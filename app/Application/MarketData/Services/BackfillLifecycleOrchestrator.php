<?php

namespace App\Application\MarketData\Services;

use App\Infrastructure\MarketData\Source\SourceAcquisitionException;
use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;
use App\Infrastructure\Persistence\MarketData\TickerMasterRepository;
use Carbon\Carbon;

class BackfillLifecycleOrchestrator
{
    private $calendar;
    private $tickers;
    private $acquisition;
    private $pipeline;
    private $evidence;
    private $replay;
    private $runs;
    private $corrections;
    private $publications;

    public function __construct(
        MarketCalendarRepository $calendar,
        TickerMasterRepository $tickers,
        ApiBackfillRangeAcquisitionService $acquisition,
        MarketDataPipelineService $pipeline,
        MarketDataEvidenceExportService $evidence,
        ReplayVerificationService $replay,
        EodRunRepository $runs,
        EodCorrectionRepository $corrections = null,
        EodPublicationRepository $publications = null
    ) {
        $this->calendar = $calendar;
        $this->tickers = $tickers;
        $this->acquisition = $acquisition;
        $this->pipeline = $pipeline;
        $this->evidence = $evidence;
        $this->replay = $replay;
        $this->runs = $runs;
        $this->corrections = $corrections;
        $this->publications = $publications;
    }

    public function execute($startDate, $endDate, $sourceMode = 'api', array $options = [])
    {
        $this->guardDateRange($startDate, $endDate);

        $sourceMode = $sourceMode ?: config('market_data.pipeline.default_source_mode', 'api');
        $requestedDates = $this->calendar->tradingDatesBetween($startDate, $endDate);
        if ($requestedDates === []) {
            throw new \RuntimeException('Lifecycle backfill requires at least one requested trading date.');
        }

        $maxDates = (int) ($options['max_dates_per_run'] ?? config('market_data.source.api_backfill.max_dates_per_run', 20));
        if ($maxDates > 0 && count($requestedDates) > $maxDates) {
            throw new \RuntimeException('CONFIG_INVALID: requested trading date count exceeds max_dates_per_run='.$maxDates.'.');
        }

        $outputDir = $this->resolveOutputDir($startDate, $endDate, $sourceMode, $options);
        $this->ensureDirectory($outputDir);
        $checkpoint = $this->readCheckpoint($outputDir);
        $warmupStart = $this->warmupStart($startDate);
        $acquisitionDates = $this->calendar->tradingDatesBetween($warmupStart, $endDate);
        $tickerCodes = $this->resolveTickerUniverse($requestedDates);
        $mode = $this->resolveErrorPolicy($options);
        $withEvidence = ! empty($options['with_evidence']) || ! empty($options['with_replay']);
        $withReplay = ! empty($options['with_replay']) && empty($options['no_replay']);
        $resume = ! empty($options['resume']);
        $onlyFailed = ! empty($options['only_failed']);
        $diagnoseSource = ! empty($options['diagnose_source']);

        if ($onlyFailed) {
            $requestedDates = $this->filterOnlyFailedDates($requestedDates, $checkpoint);
        }

        $plan = $this->sourceModeIsApi($sourceMode)
            ? $this->acquisition->plan($warmupStart, $startDate, $endDate, $acquisitionDates, $tickerCodes)
            : [
                'source_acquisition_mode' => 'per_date_file',
                'warmup_start' => $warmupStart,
                'requested_start' => $startDate,
                'requested_end' => $endDate,
                'window_count' => 0,
                'ticker_count' => count($tickerCodes),
                'trading_date_count' => count($requestedDates),
                'estimated_http_requests' => 0,
            ];

        $summary = [
            'suite' => 'market_data_backfill_lifecycle',
            'source_mode' => $sourceMode,
            'source_acquisition_mode' => $plan['source_acquisition_mode'],
            'source_acquisition_batch_id' => null,
            'requested_start' => $startDate,
            'requested_end' => $endDate,
            'warmup_start' => $warmupStart,
            'window_count' => (int) ($plan['window_count'] ?? 0),
            'estimated_http_requests' => (int) ($plan['estimated_http_requests'] ?? 0),
            'configured_concurrency' => (int) ($plan['configured_concurrency'] ?? config('market_data.source.api_backfill.concurrency', 5)),
            'ticker_count' => count($tickerCodes),
            'trading_dates' => $requestedDates,
            'trading_date_count' => count($requestedDates),
            'mode' => $mode,
            'with_evidence' => $withEvidence,
            'with_replay' => $withReplay,
            'resume' => $resume,
            'only_failed' => $onlyFailed,
            'diagnose_source' => $diagnoseSource,
            'output_dir' => $outputDir,
            'cases' => [],
            'warmup_cases' => [],
            'plan' => $plan,
        ];

        if (! empty($options['plan'])) {
            $summary['status'] = 'PLAN_ONLY';
            $this->writeSummary($outputDir, $summary);
            return $summary;
        }

        $acquired = null;
        if ($this->sourceModeIsApi($sourceMode)) {
            $acquisitionCheckpoint = $this->readAcquisitionCheckpoint($outputDir);
            $previousAcquisitionCache = $onlyFailed ? $this->readAcquisitionCache($outputDir) : null;
            if ($onlyFailed && ! $this->hasFailedAcquisitionCheckpoint($acquisitionCheckpoint)) {
                $summary['status'] = 'NOOP';
                $summary['stage'] = 'SOURCE_ACQUISITION';
                $summary['source_acquisition_state'] = 'NO_FAILED_CHECKPOINT';
                $summary['source_final_status'] = 'NO_FAILED_CHECKPOINT';
                $summary['reason_code'] = 'NO_FAILED_SOURCE_ACQUISITION_CHECKPOINT';
                $summary['failed_checkpoint_total'] = 0;
                $summary['failed_checkpoint_eligible'] = 0;
                $summary['failed_checkpoint_retried'] = 0;
                $summary['retry_success_count'] = 0;
                $summary['retry_failed_count'] = 0;
                $summary['failed_checkpoint_skipped'] = 0;
                $summary['skipped_failed_checkpoint_count'] = 0;
                $summary['skipped_checkpoint_count'] = count($acquisitionCheckpoint);
                $summary['diagnostic_path'] = $this->normalizePathForDisplay($this->writeSourceAcquisitionDiagnostics($outputDir, $this->buildSourceDiagnosticFromSummary($summary, [
                    'source_acquisition_state' => 'NO_FAILED_CHECKPOINT',
                    'reason_code' => 'NO_FAILED_SOURCE_ACQUISITION_CHECKPOINT',
                ])));
                $summary['all_passed'] = true;
                $this->writeSummary($outputDir, $summary);
                return $summary;
            }

            $acquired = ($resume && ! $onlyFailed && ! $diagnoseSource) ? $this->readAcquisitionCache($outputDir) : null;
            if (! is_array($acquired)) {
                try {
                    $acquired = $this->acquisition->acquire($warmupStart, $startDate, $endDate, $acquisitionDates, $tickerCodes, [
                        'resume' => $resume,
                        'only_failed' => $onlyFailed,
                        'source_acquisition_checkpoint' => $acquisitionCheckpoint,
                    ]);
                    if (is_array($previousAcquisitionCache)) {
                        $acquired = $this->mergeAcquiredRows($previousAcquisitionCache, $acquired);
                    }
                } catch (SourceAcquisitionException $e) {
                    return $this->blockedSourceAcquisitionSummary($summary, $outputDir, $e, $plan);
                }
                $this->writeAcquisitionCache($outputDir, $acquired);
                $this->writeAcquisitionCheckpoint($outputDir, $this->mergeAcquisitionCheckpoint($acquisitionCheckpoint, $acquired['source_acquisition_checkpoints'] ?? []));
            }

            $summary['source_acquisition_batch_id'] = $acquired['source_acquisition_batch_id'] ?? null;
            $summary['window_count'] = (int) ($acquired['window_count'] ?? $summary['window_count']);
            $summary['estimated_http_requests'] = (int) ($acquired['estimated_http_requests'] ?? $summary['estimated_http_requests']);
            $summary['source_acquisition_cache'] = $this->normalizePathForDisplay($this->acquisitionCachePath($outputDir));
            $summary['skipped_checkpoint_count'] = (int) ($acquired['skipped_checkpoint_count'] ?? 0);
            $summary['source_acquisition_state'] = $acquired['source_acquisition_state'] ?? $this->aggregateAcquisitionState($acquired['window_telemetry'] ?? []);
            $summary['source_final_status'] = $acquired['source_final_status'] ?? $summary['source_acquisition_state'];
            foreach (['failed_checkpoint_total', 'failed_checkpoint_eligible', 'failed_checkpoint_retried', 'failed_checkpoint_retry_success', 'failed_checkpoint_retry_failed', 'retry_success_count', 'retry_failed_count', 'failed_checkpoint_skipped', 'skipped_failed_checkpoint_count', 'skipped_failed_checkpoint_reasons'] as $field) {
                if (array_key_exists($field, $acquired)) {
                    $summary[$field] = $acquired[$field];
                }
            }
            $summary['failed_ticker_count'] = $this->sumTelemetryField($acquired['window_telemetry'] ?? [], 'failed_ticker_count');
            $summary['failed_window_count'] = $this->countFailedTelemetryWindows($acquired['window_telemetry'] ?? []);
            if ($resume && $onlyFailed) {
                $summary['failed_ticker_count'] = (int) ($summary['retry_failed_count'] ?? 0);
                $summary['failed_window_count'] = $this->countFailedCheckpointWindows($acquired['source_acquisition_checkpoints'] ?? []);
            }
            $summary['diagnostic_path'] = $this->normalizePathForDisplay($this->writeSourceAcquisitionDiagnostics($outputDir, $this->buildSourceDiagnosticFromAcquired($summary, $acquired)));

            if ($diagnoseSource) {
                $summary['status'] = $summary['source_acquisition_state'] === 'SUCCESS' ? 'SOURCE_DIAGNOSTIC_SUCCESS' : 'SOURCE_DIAGNOSTIC_PARTIAL';
                $summary['stage'] = 'SOURCE_ACQUISITION';
                $summary['all_passed'] = $summary['source_acquisition_state'] !== 'SYSTEMIC_FAILED';
                $this->writeSummary($outputDir, $summary);
                return $summary;
            }

            if ($resume && $onlyFailed) {
                $summary = $this->applyOnlyFailedRecoveredRows($summary, $acquired, $sourceMode, $outputDir, $withEvidence, $withReplay);
                $this->writeSummary($outputDir, $summary);
                return $summary;
            }

            $summary['warmup_cases'] = $this->importWarmupRows($acquired, $requestedDates, $sourceMode, $checkpoint, $resume);
        }

        $processed = [];
        foreach ($requestedDates as $requestedDate) {
            if ($resume && $this->checkpointCaseIsComplete($requestedDate, $checkpoint, $withReplay)) {
                $case = $checkpoint['cases'][$requestedDate];
                $case['status'] = 'SKIPPED_VERIFIED';
                $case['resume_skip'] = true;
                $summary['cases'][] = $case;
                continue;
            }

            $case = $this->processDate($requestedDate, $sourceMode, $acquired, $withEvidence, $withReplay, $outputDir);
            $summary['cases'][] = $case;
            $processed[$requestedDate] = $case;

            $checkpoint = $this->mergeCheckpoint($checkpoint, $requestedDate, $case);
            $this->writeCheckpoint($outputDir, $checkpoint);

            if ($this->caseShouldStop($case) && $mode === 'stop_on_error') {
                break;
            }
        }

        $summary = $this->finalizeSummary($summary);
        $this->writeSummary($outputDir, $summary);

        return $summary;
    }

    private function importWarmupRows(array $acquired, array $requestedDates, $sourceMode, array $checkpoint, $resume)
    {
        $requestedSet = array_fill_keys($requestedDates, true);
        $rowsByDate = $acquired['rows_by_trade_date'] ?? [];
        $dateTelemetry = $acquired['date_telemetry'] ?? [];
        $cases = [];

        foreach ($rowsByDate as $date => $rows) {
            if (isset($requestedSet[$date])) {
                continue;
            }

            if ($rows === []) {
                continue;
            }

            if ($resume && isset($checkpoint['warmup_cases'][$date]) && ($checkpoint['warmup_cases'][$date]['import_status'] ?? null) === 'SUCCESS') {
                $cases[] = $checkpoint['warmup_cases'][$date] + ['resume_skip' => true];
                continue;
            }

            try {
                $run = $this->pipeline->importDailyFromAcquiredRows($date, $sourceMode, $rows, $dateTelemetry[$date] ?? []);
                $cases[] = [
                    'requested_date' => $date,
                    'run_id' => (int) $run->run_id,
                    'warmup_only' => true,
                    'import_status' => 'SUCCESS',
                    'promote_status' => 'SKIPPED_WARMUP_ONLY',
                    'evidence_status' => 'SKIPPED_WARMUP_ONLY',
                    'fixture_status' => 'SKIPPED_WARMUP_ONLY',
                    'replay_status' => 'SKIPPED_WARMUP_ONLY',
                ];
            } catch (\Throwable $e) {
                $cases[] = [
                    'requested_date' => $date,
                    'warmup_only' => true,
                    'import_status' => 'FAILED',
                    'promote_status' => 'SKIPPED',
                    'evidence_status' => 'SKIPPED',
                    'fixture_status' => 'SKIPPED',
                    'replay_status' => 'SKIPPED',
                    'reason_code' => $this->reasonCodeFromThrowable($e, 'WARMUP_IMPORT_FAILED'),
                    'error_message' => $e->getMessage(),
                ];
            }
        }

        return $cases;
    }

    private function processDate($requestedDate, $sourceMode, $acquired, $withEvidence, $withReplay, $outputDir)
    {
        $case = [
            'requested_date' => $requestedDate,
            'import_status' => 'PENDING',
            'promote_status' => 'SKIPPED',
            'evidence_status' => $withEvidence ? 'PENDING' : 'SKIPPED',
            'fixture_status' => $withReplay ? 'PENDING' : 'SKIPPED',
            'replay_status' => $withReplay ? 'PENDING' : 'SKIPPED',
            'readable' => false,
        ];
        $run = null;
        $sourceAcquisition = [];

        try {
            if ($this->sourceModeIsApi($sourceMode)) {
                $rows = $acquired['rows_by_trade_date'][$requestedDate] ?? [];
                $sourceAcquisition = $acquired['date_telemetry'][$requestedDate] ?? [];
                $run = $this->pipeline->importDailyFromAcquiredRows($requestedDate, $sourceMode, $rows, $sourceAcquisition);
            } else {
                $run = $this->pipeline->importDaily($requestedDate, $sourceMode, null);
            }

            $case['run_id'] = (int) $run->run_id;
            $case['tickers_expected'] = isset($sourceAcquisition['expected_ticker_count']) ? (int) $sourceAcquisition['expected_ticker_count'] : null;
            $case['tickers_success'] = isset($sourceAcquisition['success_ticker_count']) ? (int) $sourceAcquisition['success_ticker_count'] : null;
            $case['tickers_failed'] = isset($sourceAcquisition['failed_ticker_count']) ? (int) $sourceAcquisition['failed_ticker_count'] : null;
            $case['source_acquisition_state'] = $sourceAcquisition['source_acquisition_state'] ?? null;
            $case['import_status'] = $this->runFailedOrHeld($run) ? (string) $run->terminal_status : 'SUCCESS';
            $case = array_merge($case, $this->mutationImpactCaseFields($run));
        } catch (\Throwable $e) {
            $run = $this->runs->findLatestForRequestedDate($requestedDate, $sourceMode);
            if ($run) {
                $case['run_id'] = (int) $run->run_id;
                $case = array_merge($case, $this->mutationImpactCaseFields($run));
            }
            $case['import_status'] = 'FAILED';
            $case['reason_code'] = $this->reasonCodeFromThrowable($e, 'IMPORT_FAILED');
            $case['error_message'] = $e->getMessage();
        }

        if ($run && ! $this->runFailedOrHeld($run) && $case['import_status'] === 'SUCCESS') {
            try {
                $run = $this->pipeline->promoteDaily($requestedDate, $sourceMode, $run->run_id, null);
                $case['run_id'] = (int) $run->run_id;
                $case['coverage_gate_state'] = $run->coverage_gate_state ?? null;
                $case['coverage_ratio'] = $run->coverage_ratio ?? null;
                $case['publishability_state'] = $run->publishability_state ?? null;
                $case['terminal_status'] = $run->terminal_status ?? null;
                $case['reason_code'] = $run->final_reason_code ?? ($case['reason_code'] ?? null);
                $case['promote_status'] = $this->isReadableRun($run) ? 'SUCCESS' : ((string) ($run->terminal_status ?? '') === 'HELD' ? 'HELD' : 'FAILED');
                $case['readable'] = $this->isReadableRun($run);
                $case = array_merge($case, $this->mutationImpactCaseFields($run));
            } catch (\Throwable $e) {
                $run = $this->runs->findLatestForRequestedDate($requestedDate, $sourceMode) ?: $run;
                $case['promote_status'] = 'FAILED';
                $case['reason_code'] = $this->reasonCodeFromThrowable($e, 'PROMOTE_FAILED');
                $case['error_message'] = $e->getMessage();
            }
        }

        $case = $this->executePublicationReprocessForCase($case, $sourceMode, $withEvidence, $withReplay, $outputDir, true);

        if ($withEvidence && $run) {
            try {
                $evidenceOutputDir = rtrim($outputDir, '/\\').'/dates/'.$requestedDate.'/run_'.$run->run_id.'/evidence';
                $this->evidence->exportRunEvidence($run->run_id, $evidenceOutputDir);
                $case['evidence_status'] = $case['readable'] ? 'EXPORTED' : 'EXPORTED_FAILURE';
                $case['evidence_output_dir'] = $this->normalizePathForDisplay($evidenceOutputDir);
            } catch (\Throwable $e) {
                $case['evidence_status'] = 'FAILED';
                $case['fixture_status'] = 'SKIPPED';
                $case['replay_status'] = 'SKIPPED';
                $case['reason_code'] = $this->reasonCodeFromThrowable($e, 'EVIDENCE_EXPORT_FAILED');
                $case['error_message'] = $e->getMessage();
            }
        } elseif ($withEvidence) {
            $case['evidence_status'] = 'SKIPPED_NO_RUN';
        }

        if ($withReplay && $run && $this->isReplayEligible($run, $case)) {
            try {
                $fixtureDir = rtrim($outputDir, '/\\').'/dates/'.$requestedDate.'/run_'.$run->run_id.'/fixture';
                $fixture = $this->replay->generateFixtureFromRun($run->run_id, $fixtureDir, 'valid_case', null);
                $case['fixture_status'] = 'GENERATED';
                $case['fixture_path'] = $this->normalizePathForDisplay($fixture['fixture_path']);

                $replay = $this->replay->verifyRunAgainstFixture($run->run_id, $fixture['fixture_path']);
                $case['replay_status'] = ($replay['replay_status'] ?? null) === 'PASS' ? 'VERIFIED' : 'FAILED';
                $case['replay_id'] = $replay['replay_id'] ?? null;
            } catch (\Throwable $e) {
                if ($case['fixture_status'] !== 'GENERATED') {
                    $case['fixture_status'] = 'FAILED';
                    $case['replay_status'] = 'SKIPPED';
                } else {
                    $case['replay_status'] = 'FAILED';
                }
                $case['reason_code'] = $this->reasonCodeFromThrowable($e, 'REPLAY_FAILED');
                $case['error_message'] = $e->getMessage();
            }
        } elseif ($withReplay) {
            if ($case['fixture_status'] === 'PENDING') {
                $case['fixture_status'] = 'SKIPPED';
            }
            if ($case['replay_status'] === 'PENDING') {
                $case['replay_status'] = 'SKIPPED';
            }
        }

        $case['status'] = $this->caseStatus($case);

        return $case;
    }

    private function applyOnlyFailedRecoveredRows(array $summary, array $acquired, $sourceMode, $outputDir, $withEvidence, $withReplay)
    {
        $summary = $this->finalizeOnlyFailedSourceRetrySummary($summary, $acquired);
        $rowsByDate = $acquired['rows_by_trade_date'] ?? [];
        $dateTelemetry = $acquired['date_telemetry'] ?? [];
        $retrySuccessCount = (int) ($summary['retry_success_count'] ?? $summary['failed_checkpoint_retry_success'] ?? 0);
        $retryFailedCount = (int) ($summary['retry_failed_count'] ?? $summary['failed_checkpoint_retry_failed'] ?? 0);

        $summary['cases'] = [];
        $summary['resume_recovered_apply_summary'] = [
            'retried_failed_checkpoint_count' => (int) ($summary['failed_checkpoint_retried'] ?? 0),
            'retry_success_count' => $retrySuccessCount,
            'recovered_row_count' => 0,
            'changed_bar_count' => 0,
            'apply_state' => $retrySuccessCount > 0 ? 'PENDING' : 'NOOP',
        ];

        if ($retrySuccessCount <= 0 || $rowsByDate === []) {
            $summary['recovered_row_apply_state'] = 'NOOP';
            $summary['recovered_row_count'] = 0;
            $summary['bar_mutation_changed_count'] = 0;
            $summary['indicator_reprocess_execution_state'] = 'NOOP';
            $summary['eligibility_reprocess_execution_state'] = 'NOOP';
            $summary['publication_reprocess_state'] = 'NOOP';
            $summary['all_passed'] = $retryFailedCount === 0;
            $summary['status'] = $retryFailedCount > 0 ? 'BLOCKED' : ($summary['status'] ?? 'NOOP');

            return $summary;
        }

        $recoveredRowCount = 0;
        $changedBarCount = 0;
        $applyFailures = 0;
        $blockedCount = 0;
        $appliedCount = 0;
        $unchangedCount = 0;

        foreach ($rowsByDate as $tradeDate => $rows) {
            $rows = array_values((array) $rows);
            if ($rows === []) {
                continue;
            }

            $case = [
                'requested_date' => (string) $tradeDate,
                'import_status' => 'PENDING',
                'promote_status' => 'SKIPPED_RECOVERED_ROW_APPLY',
                'evidence_status' => 'SKIPPED_RECOVERED_ROW_APPLY',
                'fixture_status' => 'SKIPPED_RECOVERED_ROW_APPLY',
                'replay_status' => 'SKIPPED_RECOVERED_ROW_APPLY',
                'readable' => false,
                'recovered_row_count' => count($rows),
            ];

            try {
                $telemetry = array_merge($dateTelemetry[$tradeDate] ?? [], [
                    'failed_checkpoint_retried' => (int) ($summary['failed_checkpoint_retried'] ?? 0),
                    'retry_success_count' => $retrySuccessCount,
                    'retry_failed_count' => $retryFailedCount,
                ]);
                $run = $this->pipeline->applyRecoveredRowsPartial($tradeDate, $sourceMode, $rows, $telemetry);
                $case['run_id'] = (int) $run->run_id;
                $case['import_status'] = $this->runFailedOrHeld($run) ? (string) $run->terminal_status : 'SUCCESS';
                $case = array_merge($case, $this->mutationImpactCaseFields($run));
                $case = $this->executePublicationReprocessForCase($case, $sourceMode, $withEvidence, $withReplay, $outputDir, false);

                $recoveredRowCount += count($rows);
                $changedBarCount += (int) ($case['bar_mutation_changed_count'] ?? 0);
                $state = (string) ($case['recovered_row_apply_state'] ?? $case['resume_recovered_apply_state'] ?? '');
                if ($state === 'UNCHANGED') {
                    $unchangedCount++;
                } else {
                    $appliedCount++;
                }

                if (in_array((string) ($case['indicator_reprocess_execution_state'] ?? ''), ['BLOCKED', 'FAILED'], true)
                    || in_array((string) ($case['publication_reprocess_state'] ?? ''), ['BLOCKED_REQUIRES_CORRECTION', 'PENDING_PROMOTE', 'FAILED'], true)) {
                    $blockedCount++;
                    $case['status'] = 'HELD';
                    $case['reason_code'] = $case['publication_reprocess_blocked_reason_code']
                        ?? $case['indicator_reprocess_blocked_reason_code']
                        ?? $case['indicator_reprocess_failure_reason_code']
                        ?? 'AFFECTED_PUBLICATION_REQUIRES_CORRECTION';
                } else {
                    $case['status'] = 'SUCCESS';
                }
            } catch (\Throwable $e) {
                $applyFailures++;
                $case['import_status'] = 'FAILED';
                $case['status'] = 'FAILED';
                $case['reason_code'] = $this->reasonCodeFromThrowable($e, 'RECOVERED_ROW_APPLY_FAILED');
                $case['error_message'] = $e->getMessage();
                $case['recovered_row_apply_state'] = 'FAILED';
            }

            $summary['cases'][] = $case;
        }

        $summary['resume_recovered_apply_summary'] = [
            'retried_failed_checkpoint_count' => (int) ($summary['failed_checkpoint_retried'] ?? 0),
            'retry_success_count' => $retrySuccessCount,
            'recovered_row_count' => $recoveredRowCount,
            'changed_bar_count' => $changedBarCount,
            'apply_state' => $applyFailures > 0 ? 'FAILED' : ($changedBarCount > 0 ? 'APPLIED' : 'UNCHANGED'),
        ];
        $summary['recovered_row_apply_state'] = $summary['resume_recovered_apply_summary']['apply_state'];
        $summary['recovered_row_count'] = $recoveredRowCount;
        $summary['bar_mutation_changed_count'] = $changedBarCount;
        $summary['recovered_row_apply_success_count'] = $appliedCount;
        $summary['recovered_row_apply_unchanged_count'] = $unchangedCount;
        $summary['recovered_row_apply_failed_count'] = $applyFailures;
        $summary['indicator_reprocess_execution_state'] = $this->aggregateCaseState($summary['cases'], 'indicator_reprocess_execution_state', 'NOOP');
        $summary['eligibility_reprocess_execution_state'] = $this->aggregateCaseState($summary['cases'], 'eligibility_reprocess_execution_state', 'NOOP');
        $summary['publication_reprocess_state'] = $this->aggregateCaseState($summary['cases'], 'publication_reprocess_state', 'NOOP');
        $summary['publication_reprocess_republished_trade_date_count'] = array_sum(array_map(function ($case) {
            return (int) ($case['publication_reprocess_republished_trade_date_count'] ?? 0);
        }, $summary['cases']));
        $summary['publication_reprocess_evidence_exported_count'] = array_sum(array_map(function ($case) {
            return (int) ($case['publication_reprocess_evidence_exported_count'] ?? 0);
        }, $summary['cases']));
        $summary['publication_reprocess_fixtures_generated_count'] = array_sum(array_map(function ($case) {
            return (int) ($case['publication_reprocess_fixtures_generated_count'] ?? 0);
        }, $summary['cases']));
        $summary['publication_reprocess_replay_verified_count'] = array_sum(array_map(function ($case) {
            return (int) ($case['publication_reprocess_replay_verified_count'] ?? 0);
        }, $summary['cases']));
        $summary['dates_total'] = count($summary['cases']);
        $summary['dates_success'] = count(array_filter($summary['cases'], function ($case) {
            return ($case['status'] ?? null) === 'SUCCESS';
        }));
        $summary['dates_held'] = count(array_filter($summary['cases'], function ($case) {
            return ($case['status'] ?? null) === 'HELD';
        }));
        $summary['dates_failed'] = count(array_filter($summary['cases'], function ($case) {
            return ($case['status'] ?? null) === 'FAILED';
        }));
        $summary['all_passed'] = $summary['dates_failed'] === 0 && $summary['dates_held'] === 0 && $retryFailedCount === 0;
        $summary['status'] = $summary['all_passed'] ? 'SOURCE_RETRY_APPLIED' : ($retryFailedCount > 0 || $blockedCount > 0 ? 'BLOCKED' : 'PARTIAL');

        return $summary;
    }

    private function executePublicationReprocessForCase(array $case, $sourceMode, $withEvidence, $withReplay, $outputDir, $skipRequestedDate)
    {
        $candidateDates = $this->publicationReprocessCandidateDates($case);
        if ($skipRequestedDate) {
            $requestedDate = (string) ($case['requested_date'] ?? '');
            $candidateDates = array_values(array_filter($candidateDates, function ($date) use ($requestedDate) {
                return (string) $date !== $requestedDate;
            }));
        }

        if ($candidateDates === []) {
            if ($skipRequestedDate && ($case['publication_reprocess_state'] ?? null) === 'PENDING_PROMOTE') {
                $case['publication_reprocess_state'] = 'NOOP';
                $case['publication_reprocess_republished_trade_date_count'] = (int) ($case['publication_reprocess_republished_trade_date_count'] ?? 0);
                $case['publication_reprocess_summary'] = array_merge(
                    $case['publication_reprocess_summary'] ?? [],
                    [
                        'execution_state' => 'NOOP',
                        'republished_trade_date_count' => (int) ($case['publication_reprocess_republished_trade_date_count'] ?? 0),
                        'blocked_reason_code' => 'REQUESTED_DATE_PROMOTED_BY_PRIMARY_PIPELINE',
                        'republication_mode' => 'PRIMARY_DATE_PROMOTE_HANDLED',
                    ]
                );
            }

            return $case;
        }

        $blockedDates = $this->parseCsvList($case['publication_reprocess_blocked_trade_dates'] ?? '');
        $failedDates = [];
        $republishedDates = [];
        $reprocessRuns = [];
        $evidenceExported = 0;
        $fixturesGenerated = 0;
        $replayVerified = 0;
        $blockedReason = $case['publication_reprocess_blocked_reason_code'] ?? null;
        $failureReason = null;

        foreach ($candidateDates as $tradeDate) {
            $tradeDate = (string) $tradeDate;
            if (in_array($tradeDate, $blockedDates, true)) {
                $blockedReason = $blockedReason ?: 'AFFECTED_PUBLICATION_REQUIRES_CORRECTION';
                continue;
            }

            try {
                $seedRun = $this->runs->findLatestForRequestedDate($tradeDate, $sourceMode);
                if (! $seedRun) {
                    $blockedDates[] = $tradeDate;
                    $blockedReason = $blockedReason ?: 'AFFECTED_DATE_RUN_NOT_FOUND';
                    continue;
                }

                if ($this->isReadableRun($seedRun)) {
                    $readableCorrectionPromoteMode = 'correction_current';
                    $autoCorrection = $this->executeReadablePublicationAutoCorrection(
                        $tradeDate,
                        $sourceMode,
                        $seedRun,
                        $readableCorrectionPromoteMode
                    );
                    $promotedRun = $autoCorrection['run'];
                    $autoCorrectionId = $autoCorrection['correction_id'];
                } else {
                    $promotedRun = $this->pipeline->promoteDaily($tradeDate, $sourceMode, (int) $seedRun->run_id, null, 'full_publish');
                    $autoCorrectionId = null;
                }
                $reprocessRuns[] = [
                    'trade_date' => $tradeDate,
                    'seed_run_id' => (int) ($seedRun->run_id ?? 0),
                    'run_id' => (int) ($promotedRun->run_id ?? 0),
                    'terminal_status' => $promotedRun->terminal_status ?? null,
                    'publishability_state' => $promotedRun->publishability_state ?? null,
                    'coverage_gate_state' => $promotedRun->coverage_gate_state ?? null,
                    'publication_id' => isset($promotedRun->publication_id) ? (int) $promotedRun->publication_id : null,
                    'publication_version' => isset($promotedRun->publication_version) ? (int) $promotedRun->publication_version : null,
                    'sealed_at' => $promotedRun->sealed_at ?? null,
                    'correction_id' => isset($autoCorrectionId) && $autoCorrectionId !== null ? (int) $autoCorrectionId : null,
                    'republication_mode' => isset($autoCorrectionId) && $autoCorrectionId !== null ? 'AUTOMATED_READABLE_CORRECTION' : 'AUTOMATED_NON_READABLE_DATES',
                ];

                if (! $this->isReadableRun($promotedRun)) {
                    $blockedDates[] = $tradeDate;
                    $blockedReason = $blockedReason ?: ($promotedRun->final_reason_code ?? 'PUBLICATION_REPROCESS_NOT_READABLE');
                    continue;
                }

                $republishedDates[] = $tradeDate;

                if (! empty($case['requested_date']) && (string) $case['requested_date'] === $tradeDate) {
                    $case['promote_status'] = 'SUCCESS';
                    $case['readable'] = true;
                    $case['coverage_gate_state'] = $promotedRun->coverage_gate_state ?? ($case['coverage_gate_state'] ?? null);
                    $case['coverage_ratio'] = $promotedRun->coverage_ratio ?? ($case['coverage_ratio'] ?? null);
                    $case['publishability_state'] = $promotedRun->publishability_state ?? ($case['publishability_state'] ?? null);
                    $case['terminal_status'] = $promotedRun->terminal_status ?? ($case['terminal_status'] ?? null);
                }

                if ($withEvidence) {
                    $evidenceOutputDir = rtrim($outputDir, '/\\').'/publication_reprocess/dates/'.$tradeDate.'/run_'.$promotedRun->run_id.'/evidence';
                    $this->evidence->exportRunEvidence($promotedRun->run_id, $evidenceOutputDir);
                    $evidenceExported++;
                }

                if ($withReplay) {
                    $fixtureDir = rtrim($outputDir, '/\\').'/publication_reprocess/dates/'.$tradeDate.'/run_'.$promotedRun->run_id.'/fixture';
                    $fixture = $this->replay->generateFixtureFromRun($promotedRun->run_id, $fixtureDir, 'valid_case', null);
                    $fixturesGenerated++;
                    $replay = $this->replay->verifyRunAgainstFixture($promotedRun->run_id, $fixture['fixture_path']);
                    if (($replay['replay_status'] ?? null) === 'PASS') {
                        $replayVerified++;
                    } else {
                        $failedDates[] = $tradeDate;
                        $failureReason = $failureReason ?: 'PUBLICATION_REPROCESS_REPLAY_FAILED';
                    }
                }
            } catch (\Throwable $e) {
                $failedDates[] = $tradeDate;
                $failureReason = $this->reasonCodeFromThrowable($e, 'PUBLICATION_REPROCESS_FAILED');
                $case['error_message'] = $e->getMessage();
            }
        }

        $blockedDates = array_values(array_unique($blockedDates));
        sort($blockedDates);
        $failedDates = array_values(array_unique($failedDates));
        sort($failedDates);
        $republishedDates = array_values(array_unique($republishedDates));
        sort($republishedDates);

        $state = 'NOOP';
        if ($failedDates !== []) {
            $state = 'FAILED';
        } elseif ($blockedDates !== []) {
            $state = 'BLOCKED_REQUIRES_CORRECTION';
        } elseif ($republishedDates !== []) {
            $state = 'REPUBLISHED';
        }

        $case['publication_reprocess_state'] = $state;
        $case['publication_reprocess_republished_trade_date_count'] = count($republishedDates);
        $case['publication_reprocess_republished_trade_dates'] = $republishedDates;
        $case['publication_reprocess_candidate_trade_dates'] = $candidateDates;
        $case['publication_reprocess_blocked_trade_dates'] = $blockedDates;
        $case['publication_reprocess_failed_trade_dates'] = $failedDates;
        $case['publication_reprocess_blocked_reason_code'] = $blockedReason;
        $case['publication_reprocess_failure_reason_code'] = $failureReason;
        $case['publication_reprocess_evidence_exported_count'] = $evidenceExported;
        $case['publication_reprocess_fixtures_generated_count'] = $fixturesGenerated;
        $case['publication_reprocess_replay_verified_count'] = $replayVerified;
        $case['publication_reprocess_runs'] = $reprocessRuns;
        $case['publication_reprocess_summary'] = [
            'execution_state' => $state,
            'republished_trade_date_count' => count($republishedDates),
            'republished_trade_dates' => $republishedDates,
            'candidate_trade_dates' => $candidateDates,
            'blocked_trade_dates' => $blockedDates,
            'failed_trade_dates' => $failedDates,
            'blocked_reason_code' => $blockedReason,
            'failure_reason_code' => $failureReason,
            'evidence_exported_count' => $evidenceExported,
            'fixtures_generated_count' => $fixturesGenerated,
            'replay_verified_count' => $replayVerified,
            'republication_mode' => $state === 'REPUBLISHED' ? 'AUTOMATED_IMPACT_REPUBLICATION' : ($state === 'NOOP' ? 'NOT_REQUIRED' : 'MANUAL_CORRECTION_REQUIRED'),
        ];

        if ($state === 'BLOCKED_REQUIRES_CORRECTION') {
            $case['reason_code'] = $blockedReason ?: ($case['reason_code'] ?? 'AFFECTED_PUBLICATION_REQUIRES_CORRECTION');
        } elseif ($state === 'FAILED') {
            $case['reason_code'] = $failureReason ?: ($case['reason_code'] ?? 'PUBLICATION_REPROCESS_FAILED');
        }

        $this->syncPublicationReprocessNotes($case);

        return $case;
    }


    private function executeReadablePublicationAutoCorrection($tradeDate, $sourceMode, $seedRun, $promoteMode)
    {
        if ($this->corrections === null || $this->publications === null) {
            throw new \RuntimeException('AFFECTED_PUBLICATION_AUTO_CORRECTION_UNAVAILABLE: readable affected publication requires correction repository and publication repository bindings.');
        }

        $baseline = $this->publications->findCorrectionBaselinePublicationForTradeDate($tradeDate);
        if (! $baseline) {
            throw new \RuntimeException('CORRECTION_BASELINE_LINK_MISSING: readable affected publication correction requires a current sealed readable coverage-PASS baseline publication.');
        }

        $correction = $this->corrections->createRequest(
            $tradeDate,
            'AFFECTED_PUBLICATION_REQUIRES_CORRECTION',
            'Automated correction generated by out-of-order import impact republication.',
            'system',
            (int) $baseline->publication_id,
            (int) $baseline->run_id
        );

        $correction = $this->corrections->approve((int) $correction->correction_id, 'system');

        $run = $this->pipeline->promoteDaily(
            $tradeDate,
            $sourceMode,
            (int) ($seedRun->run_id ?? $baseline->run_id),
            (int) $correction->correction_id,
            $promoteMode
        );

        return [
            'correction_id' => (int) $correction->correction_id,
            'run' => $run,
        ];
    }

    private function syncPublicationReprocessNotes(array $case): void
    {
        if (empty($case['run_id'])) {
            return;
        }

        try {
            $run = $this->runs->findByRunId((int) $case['run_id']);
            if (! $run) {
                return;
            }

            $this->runs->updateTelemetry($run, [
                'notes' => $this->appendRunNotes($run->notes ?? null, [
                    'publication_reprocess_state='.(string) ($case['publication_reprocess_state'] ?? 'NOOP'),
                    'publication_reprocess_republished_trade_date_count='.(int) ($case['publication_reprocess_republished_trade_date_count'] ?? 0),
                    ! empty($case['publication_reprocess_republished_trade_dates']) ? 'publication_reprocess_republished_trade_dates='.$this->compactList((array) $case['publication_reprocess_republished_trade_dates']) : null,
                    ! empty($case['publication_reprocess_candidate_trade_dates']) ? 'publication_reprocess_candidate_trade_dates='.$this->compactList((array) $case['publication_reprocess_candidate_trade_dates']) : null,
                    ! empty($case['publication_reprocess_blocked_trade_dates']) ? 'publication_reprocess_blocked_trade_dates='.$this->compactList((array) $case['publication_reprocess_blocked_trade_dates']) : null,
                    ! empty($case['publication_reprocess_failed_trade_dates']) ? 'publication_reprocess_failed_trade_dates='.$this->compactList((array) $case['publication_reprocess_failed_trade_dates']) : null,
                    ! empty($case['publication_reprocess_blocked_reason_code']) ? 'publication_reprocess_blocked_reason_code='.(string) $case['publication_reprocess_blocked_reason_code'] : null,
                    ! empty($case['publication_reprocess_failure_reason_code']) ? 'publication_reprocess_failure_reason_code='.(string) $case['publication_reprocess_failure_reason_code'] : null,
                ]),
            ]);
        } catch (\Throwable $e) {
            // Summary already carries the publication reprocess result; lightweight
            // repository fakes must not turn a successful reprocess into command failure.
        }
    }

    private function publicationReprocessCandidateDates(array $case)
    {
        $dates = [];
        if (! empty($case['publication_reprocess_summary']['candidate_trade_dates']) && is_array($case['publication_reprocess_summary']['candidate_trade_dates'])) {
            $dates = $case['publication_reprocess_summary']['candidate_trade_dates'];
        }

        if ($dates === []) {
            $dates = $this->parseCsvList($case['publication_reprocess_candidate_trade_dates'] ?? '');
        }

        if ($dates === [] && ! empty($case['indicator_reprocess_execution_summary']['reprocessed_trade_dates']) && is_array($case['indicator_reprocess_execution_summary']['reprocessed_trade_dates'])) {
            $dates = $case['indicator_reprocess_execution_summary']['reprocessed_trade_dates'];
        }

        if ($dates === []) {
            $dates = $this->parseCsvList($case['indicator_reprocessed_trade_dates'] ?? '');
        }

        if (($case['publication_reprocess_state'] ?? null) !== 'PENDING_PROMOTE') {
            return [];
        }

        $dates = array_values(array_unique(array_filter(array_map('strval', $dates))));
        sort($dates);

        return $dates;
    }

    private function aggregateCaseState(array $cases, $field, $default)
    {
        $states = array_values(array_filter(array_map(function ($case) use ($field) {
            return isset($case[$field]) ? (string) $case[$field] : null;
        }, $cases)));

        foreach (['FAILED', 'BLOCKED', 'BLOCKED_REQUIRES_CORRECTION', 'PENDING_PROMOTE', 'REPUBLISHED', 'EXECUTED', 'NOOP'] as $priority) {
            if (in_array($priority, $states, true)) {
                return $priority;
            }
        }

        return $default;
    }

    private function mutationImpactCaseFields($run)
    {
        $notes = $this->parseRunNotes((string) ($run->notes ?? ''));
        $fields = [];

        foreach ([
            'bar_mutation_changed_count',
            'bar_mutation_inserted_count',
            'bar_mutation_updated_count',
            'bar_mutation_unchanged_count',
            'bar_mutation_removed_count',
            'affected_ticker_count',
            'affected_trade_date_count',
            'affected_trade_dates',
            'affected_start_date',
            'affected_end_date',
            'max_indicator_dependency_trading_days',
            'indicator_reprocess_state',
            'publication_impact_state',
            'readable_publication_impacted',
            'republication_required',
            'publication_impact_reason_code',
            'indicator_reprocess_execution_state',
            'indicator_reprocessed_trade_date_count',
            'indicator_reprocessed_trade_dates',
            'indicator_reprocess_scope',
            'indicator_reprocess_blocked_reason_code',
            'indicator_reprocess_failure_reason_code',
            'eligibility_reprocess_execution_state',
            'eligibility_reprocessed_trade_date_count',
            'eligibility_reprocessed_trade_dates',
            'eligibility_reprocess_blocked_reason_code',
            'eligibility_reprocess_failure_reason_code',
            'publication_reprocess_state',
            'publication_reprocess_republished_trade_date_count',
            'publication_reprocess_republished_trade_dates',
            'publication_reprocess_candidate_trade_dates',
            'publication_reprocess_blocked_trade_dates',
            'publication_reprocess_failed_trade_dates',
            'publication_reprocess_blocked_reason_code',
            'publication_reprocess_failure_reason_code',
            'recovered_row_apply_state',
            'recovered_row_count',
            'resume_recovered_apply_state',
            'resume_recovered_row_count',
        ] as $field) {
            if (array_key_exists($field, $notes) && $notes[$field] !== '') {
                $fields[$field] = $notes[$field];
            }
        }

        if (isset($fields['bar_mutation_changed_count'])) {
            $fields['bar_mutation_summary'] = [
                'changed_bar_count' => (int) ($fields['bar_mutation_changed_count'] ?? 0),
                'inserted_bar_count' => (int) ($fields['bar_mutation_inserted_count'] ?? 0),
                'updated_bar_count' => (int) ($fields['bar_mutation_updated_count'] ?? 0),
                'unchanged_bar_count' => (int) ($fields['bar_mutation_unchanged_count'] ?? 0),
                'removed_bar_count' => (int) ($fields['bar_mutation_removed_count'] ?? 0),
            ];
            $fields['indicator_impact_summary'] = [
                'affected_ticker_count' => (int) ($fields['affected_ticker_count'] ?? 0),
                'affected_trade_date_count' => (int) ($fields['affected_trade_date_count'] ?? 0),
                'affected_trade_dates' => $this->parseCsvList($fields['affected_trade_dates'] ?? ''),
                'affected_start_date' => $fields['affected_start_date'] ?? null,
                'affected_end_date' => $fields['affected_end_date'] ?? null,
                'max_dependency_trading_days' => (int) ($fields['max_indicator_dependency_trading_days'] ?? 0),
                'indicator_reprocess_state' => $fields['indicator_reprocess_state'] ?? null,
            ];
            $fields['publication_impact_summary'] = [
                'readable_publication_impacted' => ($fields['readable_publication_impacted'] ?? 'false') === 'true',
                'republication_required' => ($fields['republication_required'] ?? 'false') === 'true',
                'publication_impact_state' => $fields['publication_impact_state'] ?? 'NOOP',
                'reason_code' => $fields['publication_impact_reason_code'] ?? null,
            ];
            $fields['indicator_reprocess_execution_summary'] = [
                'execution_state' => $fields['indicator_reprocess_execution_state'] ?? 'NOOP',
                'reprocessed_trade_date_count' => (int) ($fields['indicator_reprocessed_trade_date_count'] ?? 0),
                'reprocessed_trade_dates' => $this->parseCsvList($fields['indicator_reprocessed_trade_dates'] ?? ''),
                'reprocess_scope' => $fields['indicator_reprocess_scope'] ?? 'NONE',
                'blocked_reason_code' => $fields['indicator_reprocess_blocked_reason_code'] ?? null,
                'failure_reason_code' => $fields['indicator_reprocess_failure_reason_code'] ?? null,
            ];
            $fields['eligibility_reprocess_execution_summary'] = [
                'execution_state' => $fields['eligibility_reprocess_execution_state'] ?? 'NOOP',
                'reprocessed_trade_date_count' => (int) ($fields['eligibility_reprocessed_trade_date_count'] ?? 0),
                'reprocessed_trade_dates' => $this->parseCsvList($fields['eligibility_reprocessed_trade_dates'] ?? ''),
                'blocked_reason_code' => $fields['eligibility_reprocess_blocked_reason_code'] ?? null,
                'failure_reason_code' => $fields['eligibility_reprocess_failure_reason_code'] ?? null,
            ];
            $fields['publication_reprocess_summary'] = [
                'execution_state' => $fields['publication_reprocess_state'] ?? 'NOOP',
                'republished_trade_date_count' => (int) ($fields['publication_reprocess_republished_trade_date_count'] ?? 0),
                'republished_trade_dates' => $this->parseCsvList($fields['publication_reprocess_republished_trade_dates'] ?? ''),
                'candidate_trade_dates' => $this->parseCsvList($fields['publication_reprocess_candidate_trade_dates'] ?? ''),
                'blocked_trade_dates' => $this->parseCsvList($fields['publication_reprocess_blocked_trade_dates'] ?? ''),
                'failed_trade_dates' => $this->parseCsvList($fields['publication_reprocess_failed_trade_dates'] ?? ''),
                'blocked_reason_code' => $fields['publication_reprocess_blocked_reason_code'] ?? null,
                'failure_reason_code' => $fields['publication_reprocess_failure_reason_code'] ?? null,
            ];
        }

        return $fields;
    }

    private function isReplayEligible($run, array $case)
    {
        return $this->isReadableRun($run)
            && ($case['evidence_status'] ?? null) === 'EXPORTED';
    }

    private function isReadableRun($run)
    {
        return (string) ($run->terminal_status ?? '') === 'SUCCESS'
            && (string) ($run->publishability_state ?? '') === 'READABLE'
            && CoverageGateStateNormalizer::normalize($run->coverage_gate_state ?? null) === 'PASS'
            && ! empty($run->sealed_at);
    }

    private function runFailedOrHeld($run)
    {
        return in_array((string) ($run->terminal_status ?? ''), ['HELD', 'FAILED'], true);
    }

    private function parseRunNotes($notes)
    {
        if ($notes === '') {
            return [];
        }

        $segments = preg_split('/\s*;\s*/', $notes);
        if (! is_array($segments)) {
            return [];
        }

        $parsed = [];
        foreach ($segments as $segment) {
            $segment = trim((string) $segment);
            if ($segment === '' || strpos($segment, '=') === false) {
                continue;
            }

            [$key, $value] = array_pad(explode('=', $segment, 2), 2, null);
            $key = trim((string) $key);
            $value = trim((string) $value);

            if ($key !== '') {
                $parsed[$key] = $value;
            }
        }

        return $parsed;
    }

    private function parseCsvList($value)
    {
        if ($value === null || trim((string) $value) === '') {
            return [];
        }

        $items = array_values(array_unique(array_filter(array_map(function ($item) {
            return trim((string) $item);
        }, explode(',', (string) $value)), function ($item) {
            return $item !== '';
        })));

        sort($items);

        return $items;
    }

    private function appendRunNotes($existingNotes, array $segments)
    {
        $parts = [];
        if ($existingNotes !== null && trim((string) $existingNotes) !== '') {
            $parts[] = trim((string) $existingNotes);
        }

        foreach ($segments as $segment) {
            if ($segment !== null && trim((string) $segment) !== '') {
                $parts[] = trim((string) $segment);
            }
        }

        return implode('; ', $parts);
    }

    private function compactList(array $values)
    {
        $values = array_values(array_unique(array_filter(array_map(function ($value) {
            $value = trim((string) $value);

            return str_replace([';', '|'], '', $value);
        }, $values), function ($value) {
            return $value !== '';
        })));

        sort($values);

        return implode(',', $values);
    }

    private function caseStatus(array $case)
    {
        if (($case['publication_reprocess_state'] ?? null) === 'FAILED') {
            return 'FAILED';
        }

        if (in_array(($case['publication_reprocess_state'] ?? null), ['BLOCKED', 'BLOCKED_REQUIRES_CORRECTION', 'PENDING_PROMOTE'], true)) {
            return 'HELD';
        }

        if (($case['replay_status'] ?? null) === 'VERIFIED' || (! empty($case['readable']) && ($case['fixture_status'] ?? null) === 'SKIPPED')) {
            return 'SUCCESS';
        }

        if (in_array(($case['promote_status'] ?? null), ['HELD'], true)) {
            return 'HELD';
        }

        if (in_array(($case['import_status'] ?? null), ['FAILED', 'HELD'], true)) {
            return ($case['import_status'] ?? null) === 'HELD' ? 'HELD' : 'FAILED';
        }

        if (in_array(($case['promote_status'] ?? null), ['FAILED'], true)
            || in_array(($case['evidence_status'] ?? null), ['FAILED'], true)
            || in_array(($case['fixture_status'] ?? null), ['FAILED'], true)
            || in_array(($case['replay_status'] ?? null), ['FAILED'], true)) {
            return 'FAILED';
        }

        return ! empty($case['readable']) ? 'SUCCESS' : 'HELD';
    }

    private function caseShouldStop(array $case)
    {
        return in_array(($case['status'] ?? null), ['FAILED', 'HELD'], true);
    }

    private function finalizeSummary(array $summary)
    {
        $cases = $summary['cases'];
        $summary['dates_total'] = count($cases);
        $summary['dates_success'] = count(array_filter($cases, function ($case) {
            return ($case['status'] ?? null) === 'SUCCESS' || ($case['status'] ?? null) === 'SKIPPED_VERIFIED';
        }));
        $summary['dates_held'] = count(array_filter($cases, function ($case) {
            return ($case['status'] ?? null) === 'HELD';
        }));
        $summary['dates_failed'] = count(array_filter($cases, function ($case) {
            return ($case['status'] ?? null) === 'FAILED';
        }));
        $summary['ticker_failures'] = array_sum(array_map(function ($case) {
            return (int) ($case['tickers_failed'] ?? 0);
        }, $cases));
        $summary['evidence_exported'] = count(array_filter($cases, function ($case) {
            return in_array(($case['evidence_status'] ?? null), ['EXPORTED', 'EXPORTED_FAILURE'], true);
        }));
        $summary['fixtures_generated'] = count(array_filter($cases, function ($case) {
            return ($case['fixture_status'] ?? null) === 'GENERATED';
        }));
        $summary['replay_verified'] = count(array_filter($cases, function ($case) {
            return ($case['replay_status'] ?? null) === 'VERIFIED';
        }));
        $summary['bar_mutation_changed_count'] = array_sum(array_map(function ($case) {
            return (int) ($case['bar_mutation_changed_count'] ?? 0);
        }, $cases));
        $summary['affected_trade_date_count'] = array_sum(array_map(function ($case) {
            return (int) ($case['affected_trade_date_count'] ?? 0);
        }, $cases));
        $summary['indicator_reprocessed_trade_date_count'] = array_sum(array_map(function ($case) {
            return (int) ($case['indicator_reprocessed_trade_date_count'] ?? 0);
        }, $cases));
        $summary['eligibility_reprocessed_trade_date_count'] = array_sum(array_map(function ($case) {
            return (int) ($case['eligibility_reprocessed_trade_date_count'] ?? 0);
        }, $cases));
        $summary['publication_reprocess_state'] = $this->aggregateCaseState($cases, 'publication_reprocess_state', 'NOOP');
        $summary['publication_reprocess_republished_trade_date_count'] = array_sum(array_map(function ($case) {
            return (int) ($case['publication_reprocess_republished_trade_date_count'] ?? 0);
        }, $cases));
        $summary['publication_reprocess_evidence_exported_count'] = array_sum(array_map(function ($case) {
            return (int) ($case['publication_reprocess_evidence_exported_count'] ?? 0);
        }, $cases));
        $summary['publication_reprocess_fixtures_generated_count'] = array_sum(array_map(function ($case) {
            return (int) ($case['publication_reprocess_fixtures_generated_count'] ?? 0);
        }, $cases));
        $summary['publication_reprocess_replay_verified_count'] = array_sum(array_map(function ($case) {
            return (int) ($case['publication_reprocess_replay_verified_count'] ?? 0);
        }, $cases));
        $summary['all_passed'] = $summary['dates_failed'] === 0 && $summary['dates_held'] === 0;
        $summary['status'] = $summary['all_passed'] ? 'SUCCESS' : 'PARTIAL';

        return $summary;
    }

    private function resolveTickerUniverse(array $requestedDates)
    {
        $codes = [];
        foreach ($requestedDates as $date) {
            foreach ($this->tickers->getUniverseForTradeDate($date) as $row) {
                if (isset($row['ticker_code']) && trim((string) $row['ticker_code']) !== '') {
                    $codes[strtoupper(trim((string) $row['ticker_code']))] = true;
                }
            }
        }

        $codes = array_keys($codes);
        sort($codes);

        return $codes;
    }

    private function filterOnlyFailedDates(array $requestedDates, array $checkpoint)
    {
        if (empty($checkpoint['cases']) || ! is_array($checkpoint['cases'])) {
            return $requestedDates;
        }

        return array_values(array_filter($requestedDates, function ($date) use ($checkpoint) {
            if (! isset($checkpoint['cases'][$date])) {
                return true;
            }

            return ! in_array(($checkpoint['cases'][$date]['status'] ?? null), ['SUCCESS', 'SKIPPED_VERIFIED'], true);
        }));
    }

    private function checkpointCaseIsComplete($requestedDate, array $checkpoint, $withReplay)
    {
        if (empty($checkpoint['cases'][$requestedDate]) || ! is_array($checkpoint['cases'][$requestedDate])) {
            return false;
        }

        $case = $checkpoint['cases'][$requestedDate];
        if ($withReplay) {
            return ($case['replay_status'] ?? null) === 'VERIFIED';
        }

        return ($case['status'] ?? null) === 'SUCCESS';
    }

    private function mergeCheckpoint(array $checkpoint, $requestedDate, array $case)
    {
        if (! isset($checkpoint['cases']) || ! is_array($checkpoint['cases'])) {
            $checkpoint['cases'] = [];
        }

        $checkpoint['cases'][$requestedDate] = $case;
        $checkpoint['updated_at'] = Carbon::now(config('market_data.platform.timezone', 'Asia/Jakarta'))->toDateTimeString();

        return $checkpoint;
    }

    private function readCheckpoint($outputDir)
    {
        $path = $this->checkpointPath($outputDir);
        if (! is_file($path)) {
            return ['cases' => [], 'warmup_cases' => []];
        }

        $decoded = json_decode((string) file_get_contents($path), true);

        return is_array($decoded) ? $decoded : ['cases' => [], 'warmup_cases' => []];
    }

    private function writeCheckpoint($outputDir, array $checkpoint)
    {
        file_put_contents($this->checkpointPath($outputDir), json_encode($checkpoint, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");
    }

    private function readAcquisitionCache($outputDir)
    {
        $path = $this->acquisitionCachePath($outputDir);
        if (! is_file($path)) {
            return null;
        }

        $decoded = json_decode((string) file_get_contents($path), true);

        if (! is_array($decoded)) {
            return null;
        }

        return isset($decoded['rows_by_trade_date']) && is_array($decoded['rows_by_trade_date']) ? $decoded : null;
    }

    private function writeAcquisitionCache($outputDir, array $acquired)
    {
        file_put_contents($this->acquisitionCachePath($outputDir), json_encode($this->slimAcquisitionCache($acquired), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");
    }

    private function writeSummary($outputDir, array $summary)
    {
        file_put_contents(rtrim($outputDir, '/\\').'/market_data_backfill_lifecycle_summary.json', json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");
    }

    private function checkpointPath($outputDir)
    {
        return rtrim($outputDir, '/\\').'/lifecycle_checkpoint.json';
    }

    private function acquisitionCachePath($outputDir)
    {
        return rtrim($outputDir, '/\\').'/source_acquisition_cache.json';
    }

    private function acquisitionCheckpointPath($outputDir)
    {
        return rtrim($outputDir, '/\\').'/source_acquisition_checkpoint.json';
    }

    private function resolveOutputDir($startDate, $endDate, $sourceMode, array $options)
    {
        if (! empty($options['output_dir'])) {
            return (string) $options['output_dir'];
        }

        return storage_path('app/market_data/evidence/backfill_lifecycle/'.$sourceMode.'_'.$startDate.'_to_'.$endDate);
    }

    private function warmupStart($startDate)
    {
        return Carbon::parse($startDate, config('market_data.platform.timezone', 'Asia/Jakarta'))
            ->subDays(max(0, (int) config('market_data.source.api_backfill.warmup_days', 120)))
            ->toDateString();
    }

    private function resolveErrorPolicy(array $options)
    {
        if (! empty($options['continue_on_error'])) {
            return 'continue_on_error';
        }

        if (! empty($options['collect_all_errors']) || (bool) config('market_data.source.api_backfill.collect_all_errors', false)) {
            return 'continue_on_error';
        }

        if (! empty($options['stop_on_error'])) {
            return 'stop_on_error';
        }

        return (string) config('market_data.source.api_backfill.default_error_policy', 'stop_on_error');
    }

    private function sourceModeIsApi($sourceMode)
    {
        return (string) $sourceMode === 'api';
    }


    private function blockedSourceAcquisitionSummary(array $summary, $outputDir, SourceAcquisitionException $e, array $plan)
    {
        $context = $e->context();
        $reasonCode = $this->reasonCodeFromThrowable($e, 'RUN_SOURCE_ACQUISITION_FAILED');
        $state = $context['source_acquisition_state'] ?? 'SYSTEMIC_FAILED';
        if (! in_array($state, ['SYSTEMIC_FAILED', 'FAILED', 'PARTIAL_FAILED', 'FAILED_RETRY_BLOCKED', 'PARTIAL_RETRY_SUCCESS', 'RETRY_SUCCESS', 'NO_FAILED_CHECKPOINT'], true)) {
            $state = 'SYSTEMIC_FAILED';
        }

        $summary['status'] = 'BLOCKED';
        $summary['stage'] = 'SOURCE_ACQUISITION';
        $summary['source_acquisition_state'] = $state;
        $summary['source_acquisition_batch_id'] = $context['source_acquisition_batch_id'] ?? ($summary['source_acquisition_batch_id'] ?? null);
        $summary['source_final_status'] = $context['source_final_status'] ?? $state;
        $summary['publishability_state'] = 'NOT_READABLE';
        $summary['reason_code'] = $reasonCode;
        $summary['error_message'] = $e->getMessage();
        $summary['failed_ticker_count'] = (int) ($context['failed_ticker_count'] ?? $context['missing_ticker_count'] ?? 0);
        $summary['failed_window_count'] = 1;
        $summary['http_status'] = $context['final_http_status'] ?? ($context['http_status'] ?? null);
        $summary['provider_error_sample'] = $context['provider_error_sample'] ?? ($context['response_body_sample'] ?? null);
        $summary['sanitized_url'] = $context['sanitized_url'] ?? ($context['url'] ?? null);
        $summary['failure_scope'] = $context['failure_scope'] ?? 'systemic';
        $summary['failed_ticker'] = $context['ticker_code'] ?? ($context['failed_ticker_codes'][0] ?? null);
        $summary['failed_window_start'] = $context['source_window_start'] ?? null;
        $summary['failed_window_end'] = $context['source_window_end'] ?? null;
        $summary['all_passed'] = false;
        $summary['cases'] = [];

        $checkpoint = $this->buildAcquisitionCheckpointFromFailureContext($context, $summary);
        if ($checkpoint !== []) {
            $this->writeAcquisitionCheckpoint($outputDir, $this->mergeAcquisitionCheckpoint($this->readAcquisitionCheckpoint($outputDir), $checkpoint));
        }

        $summary['diagnostic_path'] = $this->normalizePathForDisplay($this->writeSourceAcquisitionDiagnostics(
            $outputDir,
            $this->buildSourceDiagnosticFromSummary($summary, $context + ['reason_code' => $reasonCode])
        ));

        $this->writeSummary($outputDir, $summary);

        return $summary;
    }

    private function buildSourceDiagnosticFromAcquired(array $summary, array $acquired)
    {
        $telemetry = $acquired['window_telemetry'] ?? [];
        $failures = [];
        foreach ($this->failureSamplesFromCheckpoints($acquired['source_acquisition_checkpoints'] ?? []) as $failure) {
            $failures[] = $failure;
            if (count($failures) >= 25) {
                break;
            }
        }

        if ($failures === []) {
            foreach ($telemetry as $entry) {
                if (count($failures) >= 25) {
                    break;
                }
                if (! is_array($entry)) {
                    continue;
                }
                foreach ((array) ($entry['failed_ticker_codes'] ?? $entry['missing_ticker_codes'] ?? []) as $tickerCode) {
                    $failures[] = [
                        'ticker_code' => $tickerCode,
                        'window_start' => $entry['source_window_start'] ?? null,
                        'window_end' => $entry['source_window_end'] ?? null,
                        'reason_code' => $entry['final_reason_code'] ?? null,
                        'http_status' => $entry['final_http_status'] ?? ($entry['http_status'] ?? null),
                        'failure_scope' => $entry['failure_scope'] ?? 'ticker',
                        'provider_error_sample' => $this->truncateDiagnosticString($this->redactDiagnosticString($entry['provider_error_sample'] ?? ($entry['response_body_sample'] ?? null))),
                        'sanitized_url' => $this->redactDiagnosticString($entry['sanitized_url'] ?? ($entry['url'] ?? null)),
                    ];
                    if (count($failures) >= 25) {
                        break 2;
                    }
                }
            }
        }
        $reasonCode = $this->diagnosticReasonCode($summary, $failures, $acquired['source_acquisition_checkpoints'] ?? []);

        return [
            'source_mode' => $summary['source_mode'] ?? 'api',
            'source_acquisition_mode' => $summary['source_acquisition_mode'] ?? 'range_window',
            'source_acquisition_batch_id' => $acquired['source_acquisition_batch_id'] ?? null,
            'requested_start' => $summary['requested_start'] ?? null,
            'requested_end' => $summary['requested_end'] ?? null,
            'warmup_start' => $summary['warmup_start'] ?? null,
            'window_count' => (int) ($summary['window_count'] ?? 0),
            'ticker_count' => (int) ($summary['ticker_count'] ?? 0),
            'estimated_http_requests' => (int) ($summary['estimated_http_requests'] ?? 0),
            'source_acquisition_state' => $summary['source_acquisition_state'] ?? 'SUCCESS',
            'source_final_status' => $summary['source_final_status'] ?? ($summary['source_acquisition_state'] ?? 'SUCCESS'),
            'failed_ticker_count' => (int) ($summary['failed_ticker_count'] ?? 0),
            'failed_window_count' => (int) ($summary['failed_window_count'] ?? 0),
            'skipped_checkpoint_count' => (int) ($summary['skipped_checkpoint_count'] ?? 0),
            'failed_checkpoint_total' => (int) ($summary['failed_checkpoint_total'] ?? $acquired['failed_checkpoint_total'] ?? 0),
            'failed_checkpoint_eligible' => (int) ($summary['failed_checkpoint_eligible'] ?? $acquired['failed_checkpoint_eligible'] ?? 0),
            'failed_checkpoint_retried' => (int) ($summary['failed_checkpoint_retried'] ?? $acquired['failed_checkpoint_retried'] ?? 0),
            'failed_checkpoint_retry_success' => (int) ($summary['failed_checkpoint_retry_success'] ?? $acquired['failed_checkpoint_retry_success'] ?? 0),
            'failed_checkpoint_retry_failed' => (int) ($summary['failed_checkpoint_retry_failed'] ?? $acquired['failed_checkpoint_retry_failed'] ?? 0),
            'retry_success_count' => (int) ($summary['retry_success_count'] ?? $acquired['retry_success_count'] ?? 0),
            'retry_failed_count' => (int) ($summary['retry_failed_count'] ?? $acquired['retry_failed_count'] ?? 0),
            'failed_checkpoint_skipped' => (int) ($summary['failed_checkpoint_skipped'] ?? $acquired['failed_checkpoint_skipped'] ?? 0),
            'skipped_failed_checkpoint_count' => (int) ($summary['skipped_failed_checkpoint_count'] ?? $acquired['skipped_failed_checkpoint_count'] ?? 0),
            'skipped_failed_checkpoint_reasons' => $summary['skipped_failed_checkpoint_reasons'] ?? ($acquired['skipped_failed_checkpoint_reasons'] ?? []),
            'failures_sample' => $failures,
            'reason_code' => $reasonCode,
            'created_at' => Carbon::now(config('market_data.platform.timezone', 'Asia/Jakarta'))->toDateTimeString(),
        ];
    }

    private function failureSamplesFromCheckpoints(array $checkpointRows)
    {
        $failures = [];
        foreach ($checkpointRows as $row) {
            if (! is_array($row) || ($row['state'] ?? null) !== 'FAILED') {
                continue;
            }

            $failures[] = [
                'ticker_code' => $row['ticker_code'] ?? null,
                'window_start' => $row['window_start'] ?? null,
                'window_end' => $row['window_end'] ?? null,
                'reason_code' => $row['reason_code'] ?? null,
                'http_status' => $row['http_status'] ?? null,
                'failure_scope' => $row['failure_scope'] ?? 'ticker',
                'error_sample' => $this->truncateDiagnosticString($this->redactDiagnosticString($row['error_sample'] ?? null)),
                'provider_error_sample' => $this->truncateDiagnosticString($this->redactDiagnosticString($row['provider_error_sample'] ?? null)),
                'sanitized_url' => $this->redactDiagnosticString($row['sanitized_url'] ?? null),
            ];
        }

        return $failures;
    }

    private function buildSourceDiagnosticFromSummary(array $summary, array $context)
    {
        $failure = [
            'ticker_code' => $context['ticker_code'] ?? ($summary['failed_ticker'] ?? null),
            'window_start' => $context['source_window_start'] ?? ($summary['failed_window_start'] ?? null),
            'window_end' => $context['source_window_end'] ?? ($summary['failed_window_end'] ?? null),
            'reason_code' => $context['reason_code'] ?? ($context['final_reason_code'] ?? ($summary['reason_code'] ?? null)),
            'http_status' => $context['final_http_status'] ?? ($context['http_status'] ?? ($summary['http_status'] ?? null)),
            'failure_scope' => $context['failure_scope'] ?? ($summary['failure_scope'] ?? null),
            'provider_error_sample' => $this->truncateDiagnosticString($this->redactDiagnosticString($context['provider_error_sample'] ?? ($context['response_body_sample'] ?? ($summary['provider_error_sample'] ?? null)))),
            'sanitized_url' => $this->redactDiagnosticString($context['sanitized_url'] ?? ($context['url'] ?? ($summary['sanitized_url'] ?? null))),
        ];
        $failures = array_filter($failure, function ($value) { return $value !== null && $value !== ''; }) === [] ? [] : [$failure];
        $reasonCode = $this->diagnosticReasonCode($summary, $failures, []);

        return [
            'source_mode' => $summary['source_mode'] ?? 'api',
            'source_acquisition_mode' => $summary['source_acquisition_mode'] ?? 'range_window',
            'source_acquisition_batch_id' => $summary['source_acquisition_batch_id'] ?? ($context['source_acquisition_batch_id'] ?? null),
            'requested_start' => $summary['requested_start'] ?? null,
            'requested_end' => $summary['requested_end'] ?? null,
            'warmup_start' => $summary['warmup_start'] ?? null,
            'window_count' => (int) ($summary['window_count'] ?? 0),
            'ticker_count' => (int) ($summary['ticker_count'] ?? 0),
            'estimated_http_requests' => (int) ($summary['estimated_http_requests'] ?? 0),
            'source_acquisition_state' => $summary['source_acquisition_state'] ?? ($context['source_acquisition_state'] ?? 'SYSTEMIC_FAILED'),
            'source_final_status' => $summary['source_final_status'] ?? ($context['source_final_status'] ?? ($summary['source_acquisition_state'] ?? null)),
            'failed_ticker_count' => (int) ($summary['failed_ticker_count'] ?? $context['failed_ticker_count'] ?? 0),
            'failed_window_count' => (int) ($summary['failed_window_count'] ?? 0),
            'failed_checkpoint_total' => (int) ($summary['failed_checkpoint_total'] ?? $context['failed_checkpoint_total'] ?? 0),
            'failed_checkpoint_eligible' => (int) ($summary['failed_checkpoint_eligible'] ?? $context['failed_checkpoint_eligible'] ?? 0),
            'failed_checkpoint_retried' => (int) ($summary['failed_checkpoint_retried'] ?? $context['failed_checkpoint_retried'] ?? 0),
            'failed_checkpoint_retry_success' => (int) ($summary['failed_checkpoint_retry_success'] ?? $context['failed_checkpoint_retry_success'] ?? 0),
            'failed_checkpoint_retry_failed' => (int) ($summary['failed_checkpoint_retry_failed'] ?? $context['failed_checkpoint_retry_failed'] ?? 0),
            'retry_success_count' => (int) ($summary['retry_success_count'] ?? $context['retry_success_count'] ?? 0),
            'retry_failed_count' => (int) ($summary['retry_failed_count'] ?? $context['retry_failed_count'] ?? 0),
            'failed_checkpoint_skipped' => (int) ($summary['failed_checkpoint_skipped'] ?? $context['failed_checkpoint_skipped'] ?? 0),
            'skipped_failed_checkpoint_count' => (int) ($summary['skipped_failed_checkpoint_count'] ?? $context['skipped_failed_checkpoint_count'] ?? 0),
            'skipped_failed_checkpoint_reasons' => $summary['skipped_failed_checkpoint_reasons'] ?? ($context['skipped_failed_checkpoint_reasons'] ?? []),
            'failures_sample' => $failures,
            'reason_code' => $reasonCode,
            'created_at' => Carbon::now(config('market_data.platform.timezone', 'Asia/Jakarta'))->toDateTimeString(),
        ];
    }

    private function diagnosticReasonCode(array $summary, array $failures, array $checkpointRows)
    {
        foreach ([$summary['reason_code'] ?? null, $summary['final_reason_code'] ?? null] as $reasonCode) {
            $reasonCode = trim((string) $reasonCode);
            if ($reasonCode !== '') {
                return $reasonCode;
            }
        }

        $candidates = [];
        foreach ($checkpointRows as $row) {
            if (! is_array($row) || ! in_array(($row['state'] ?? null), ['FAILED', 'RETRYING'], true)) {
                continue;
            }

            $reasonCode = trim((string) ($row['reason_code'] ?? ''));
            if ($reasonCode === '') {
                continue;
            }

            $candidates[] = [
                'window_start' => (string) ($row['window_start'] ?? ''),
                'window_end' => (string) ($row['window_end'] ?? ''),
                'ticker_code' => strtoupper((string) ($row['ticker_code'] ?? '')),
                'reason_code' => $reasonCode,
            ];
        }

        if ($candidates === []) {
            foreach ($failures as $failure) {
                if (! is_array($failure)) {
                    continue;
                }

                $reasonCode = trim((string) ($failure['reason_code'] ?? ''));
                if ($reasonCode === '') {
                    continue;
                }

                $candidates[] = [
                    'window_start' => (string) ($failure['window_start'] ?? ''),
                    'window_end' => (string) ($failure['window_end'] ?? ''),
                    'ticker_code' => strtoupper((string) ($failure['ticker_code'] ?? '')),
                    'reason_code' => $reasonCode,
                ];
            }
        }

        if ($candidates === []) {
            return null;
        }

        usort($candidates, function ($left, $right) {
            foreach (['window_start', 'window_end', 'ticker_code', 'reason_code'] as $field) {
                $comparison = strcmp((string) $left[$field], (string) $right[$field]);
                if ($comparison !== 0) {
                    return $comparison;
                }
            }

            return 0;
        });

        $counts = [];
        $firstOrder = [];
        foreach ($candidates as $order => $candidate) {
            $reasonCode = $candidate['reason_code'];
            $counts[$reasonCode] = ($counts[$reasonCode] ?? 0) + 1;
            if (! array_key_exists($reasonCode, $firstOrder)) {
                $firstOrder[$reasonCode] = $order;
            }
        }

        $selectedReason = null;
        $selectedCount = -1;
        $selectedOrder = PHP_INT_MAX;
        foreach ($counts as $reasonCode => $count) {
            $order = $firstOrder[$reasonCode];
            if ($count > $selectedCount || ($count === $selectedCount && $order < $selectedOrder)) {
                $selectedReason = $reasonCode;
                $selectedCount = $count;
                $selectedOrder = $order;
            }
        }

        return $selectedReason;
    }

    private function writeSourceAcquisitionDiagnostics($outputDir, array $diagnostics)
    {
        $path = rtrim($outputDir, '/\\').'/source_acquisition_diagnostics.json';
        file_put_contents($path, json_encode($diagnostics, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");

        return $path;
    }

    private function aggregateAcquisitionState(array $telemetry)
    {
        if ($telemetry === []) {
            return 'SUCCESS';
        }

        $hasPartial = false;
        $hasRetryPartial = false;
        foreach ($telemetry as $entry) {
            $state = (string) ($entry['source_acquisition_state'] ?? $entry['source_final_status'] ?? 'SUCCESS');
            if ($state === 'SYSTEMIC_FAILED') {
                return 'SYSTEMIC_FAILED';
            }
            if ($state === 'FAILED_RETRY_BLOCKED') {
                return 'FAILED_RETRY_BLOCKED';
            }
            if ($state === 'PARTIAL_RETRY_SUCCESS') {
                $hasRetryPartial = true;
            }
            if (in_array($state, ['SYSTEMIC_FAILED', 'FAILED'], true)) {
                return 'SYSTEMIC_FAILED';
            }
            if (in_array($state, ['PARTIAL_SUCCESS', 'PARTIAL', 'PARTIAL_FAILED'], true)) {
                $hasPartial = true;
            }
        }

        if ($hasRetryPartial) {
            return 'PARTIAL_RETRY_SUCCESS';
        }

        return $hasPartial ? 'PARTIAL_SUCCESS' : 'SUCCESS';
    }

    private function sumTelemetryField(array $telemetry, $field)
    {
        return array_sum(array_map(function ($entry) use ($field) {
            return is_array($entry) ? (int) ($entry[$field] ?? 0) : 0;
        }, $telemetry));
    }

    private function countFailedTelemetryWindows(array $telemetry)
    {
        return count(array_filter($telemetry, function ($entry) {
            if (! is_array($entry)) {
                return false;
            }

            return (int) ($entry['failed_ticker_count'] ?? 0) > 0
                || in_array(($entry['source_acquisition_state'] ?? null), ['FAILED', 'SYSTEMIC_FAILED', 'PARTIAL_FAILED', 'FAILED_RETRY_BLOCKED', 'PARTIAL_RETRY_SUCCESS'], true);
        }));
    }

    private function countFailedCheckpointWindows(array $checkpointRows)
    {
        $windows = [];
        foreach ($checkpointRows as $row) {
            if (! is_array($row) || ($row['state'] ?? null) !== 'FAILED') {
                continue;
            }

            $windowStart = $row['window_start'] ?? null;
            $windowEnd = $row['window_end'] ?? null;
            if ($windowStart !== null && $windowEnd !== null) {
                $windows[(string) $windowStart.'|'.(string) $windowEnd] = true;
            }
        }

        return count($windows);
    }

    private function slimAcquisitionCache(array $acquired)
    {
        $checkpoints = $this->slimFailedCheckpoints($acquired['source_acquisition_checkpoints'] ?? []);

        return [
            'cache_format' => 'source_acquisition_resume_v2_slim',
            'cache_supports_row_resume' => false,
            'source_acquisition_batch_id' => $acquired['source_acquisition_batch_id'] ?? null,
            'source_acquisition_mode' => $acquired['source_acquisition_mode'] ?? 'range_window',
            'source_acquisition_state' => $acquired['source_acquisition_state'] ?? $this->aggregateAcquisitionState($acquired['window_telemetry'] ?? []),
            'source_final_status' => $acquired['source_final_status'] ?? null,
            'warmup_start' => $acquired['warmup_start'] ?? null,
            'requested_start' => $acquired['requested_start'] ?? null,
            'requested_end' => $acquired['requested_end'] ?? null,
            'windows' => $acquired['windows'] ?? [],
            'window_count' => (int) ($acquired['window_count'] ?? 0),
            'ticker_count' => (int) ($acquired['ticker_count'] ?? 0),
            'configured_concurrency' => (int) ($acquired['configured_concurrency'] ?? 0),
            'trading_date_count' => count((array) ($acquired['trading_dates'] ?? [])),
            'estimated_http_requests' => (int) ($acquired['estimated_http_requests'] ?? 0),
            'rows_by_trade_date_counts' => $this->rowCountsByTradeDate($acquired['rows_by_trade_date'] ?? []),
            'date_telemetry_summary' => $this->slimDateTelemetry($acquired['date_telemetry'] ?? []),
            'window_telemetry_summary' => $this->slimWindowTelemetry($acquired['window_telemetry'] ?? []),
            'failed_source_acquisition_checkpoints' => $checkpoints,
            'source_acquisition_checkpoint_summary' => [
                'total' => count((array) ($acquired['source_acquisition_checkpoints'] ?? [])),
                'failed' => count($checkpoints),
                'success' => $this->countCheckpointState($acquired['source_acquisition_checkpoints'] ?? [], 'SUCCESS'),
            ],
            'failed_checkpoint_total' => (int) ($acquired['failed_checkpoint_total'] ?? 0),
            'failed_checkpoint_eligible' => (int) ($acquired['failed_checkpoint_eligible'] ?? 0),
            'failed_checkpoint_retried' => (int) ($acquired['failed_checkpoint_retried'] ?? 0),
            'failed_checkpoint_retry_success' => (int) ($acquired['failed_checkpoint_retry_success'] ?? 0),
            'failed_checkpoint_retry_failed' => (int) ($acquired['failed_checkpoint_retry_failed'] ?? 0),
            'retry_success_count' => (int) ($acquired['retry_success_count'] ?? 0),
            'retry_failed_count' => (int) ($acquired['retry_failed_count'] ?? 0),
            'failed_checkpoint_skipped' => (int) ($acquired['failed_checkpoint_skipped'] ?? 0),
            'skipped_failed_checkpoint_count' => (int) ($acquired['skipped_failed_checkpoint_count'] ?? 0),
            'skipped_failed_checkpoint_reasons' => $acquired['skipped_failed_checkpoint_reasons'] ?? [],
            'skipped_checkpoint_count' => (int) ($acquired['skipped_checkpoint_count'] ?? 0),
            'created_at' => Carbon::now(config('market_data.platform.timezone', 'Asia/Jakarta'))->toDateTimeString(),
        ];
    }

    private function rowCountsByTradeDate(array $rowsByTradeDate)
    {
        $counts = [];
        foreach ($rowsByTradeDate as $tradeDate => $rows) {
            $counts[(string) $tradeDate] = count((array) $rows);
        }

        ksort($counts);

        return $counts;
    }

    private function slimDateTelemetry(array $dateTelemetry)
    {
        $summary = [];
        foreach ($dateTelemetry as $tradeDate => $telemetry) {
            if (! is_array($telemetry)) {
                continue;
            }

            $summary[(string) $tradeDate] = array_intersect_key($telemetry, array_flip([
                'source_acquisition_state',
                'source_final_status',
                'source_window_start',
                'source_window_end',
                'expected_ticker_count',
                'success_ticker_count',
                'failed_ticker_count',
                'returned_row_count',
                'final_reason_code',
                'coverage_impossible',
            ]));
        }

        ksort($summary);

        return $summary;
    }

    private function slimWindowTelemetry(array $windowTelemetry)
    {
        $summary = [];
        foreach ($windowTelemetry as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $summary[] = [
                'window_start' => $entry['source_window_start'] ?? null,
                'window_end' => $entry['source_window_end'] ?? null,
                'source_acquisition_state' => $entry['source_acquisition_state'] ?? null,
                'source_final_status' => $entry['source_final_status'] ?? null,
                'final_reason_code' => $entry['final_reason_code'] ?? null,
                'final_http_status' => $entry['final_http_status'] ?? null,
                'failed_ticker_count' => (int) ($entry['failed_ticker_count'] ?? 0),
                'success_ticker_count' => (int) ($entry['success_ticker_count'] ?? 0),
                'returned_row_count' => (int) ($entry['returned_row_count'] ?? 0),
                'failures_sample' => array_slice($this->failureSamplesFromCheckpoints($this->checkpointRowsFromFailureContexts($entry)), 0, 10),
            ];
        }

        return $summary;
    }

    private function checkpointRowsFromFailureContexts(array $telemetry)
    {
        $rows = [];
        foreach ((array) ($telemetry['failed_ticker_contexts'] ?? []) as $tickerCode => $context) {
            if (! is_array($context)) {
                continue;
            }

            $rows[] = [
                'state' => 'FAILED',
                'ticker_code' => $context['ticker_code'] ?? $tickerCode,
                'window_start' => $context['source_window_start'] ?? null,
                'window_end' => $context['source_window_end'] ?? null,
                'reason_code' => $context['final_reason_code'] ?? null,
                'http_status' => $context['http_status'] ?? ($context['final_http_status'] ?? null),
                'failure_scope' => $context['failure_scope'] ?? 'ticker',
                'error_sample' => $context['error_sample'] ?? null,
                'provider_error_sample' => $context['provider_error_sample'] ?? null,
                'sanitized_url' => $context['sanitized_url'] ?? ($context['url'] ?? null),
            ];
        }

        return $rows;
    }

    private function slimFailedCheckpoints(array $checkpointRows)
    {
        $slim = [];
        foreach ($checkpointRows as $key => $row) {
            if (! is_array($row) || ($row['state'] ?? null) !== 'FAILED') {
                continue;
            }

            $slim[(string) $key] = [
                'window_start' => $row['window_start'] ?? null,
                'window_end' => $row['window_end'] ?? null,
                'ticker_code' => $row['ticker_code'] ?? null,
                'state' => $row['state'] ?? null,
                'reason_code' => $row['reason_code'] ?? null,
                'http_status' => $row['http_status'] ?? null,
                'failure_scope' => $row['failure_scope'] ?? null,
                'attempt_count' => (int) ($row['attempt_count'] ?? 0),
                'rows_count' => (int) ($row['rows_count'] ?? 0),
                'sanitized_url' => $this->redactDiagnosticString($row['sanitized_url'] ?? null),
                'error_sample' => $this->truncateDiagnosticString($this->redactDiagnosticString($row['error_sample'] ?? null)),
                'provider_error_sample' => $this->truncateDiagnosticString($this->redactDiagnosticString($row['provider_error_sample'] ?? null)),
                'created_at' => $row['created_at'] ?? null,
                'updated_at' => $row['updated_at'] ?? null,
            ];
        }

        ksort($slim);

        return $slim;
    }

    private function countCheckpointState(array $checkpointRows, $state)
    {
        return count(array_filter($checkpointRows, function ($row) use ($state) {
            return is_array($row) && ($row['state'] ?? null) === $state;
        }));
    }

    private function truncateDiagnosticString($value, $maxLength = 500)
    {
        if ($value === null) {
            return null;
        }

        $value = (string) $value;
        $maxLength = max(16, (int) $maxLength);
        if (strlen($value) <= $maxLength) {
            return $value;
        }

        $suffix = '...[truncated]';

        return substr($value, 0, $maxLength - strlen($suffix)).$suffix;
    }

    private function redactDiagnosticString($value)
    {
        if ($value === null) {
            return null;
        }

        return preg_replace('/(^|[?&\s])((?:token|apikey|api_key|auth|authorization|signature|sig)=)[^&\s]+/i', '$1$2[redacted]', (string) $value);
    }

    private function finalizeOnlyFailedSourceRetrySummary(array $summary, array $acquired)
    {
        $state = (string) ($summary['source_acquisition_state'] ?? 'FAILED_RETRY_BLOCKED');
        $summary['stage'] = 'SOURCE_ACQUISITION';
        $summary['source_final_status'] = $summary['source_final_status'] ?? $state;

        if ($state === 'RETRY_SUCCESS') {
            $summary['status'] = 'SOURCE_RETRY_SUCCESS';
            $summary['all_passed'] = true;
            return $summary;
        }

        if ($state === 'NO_FAILED_CHECKPOINT') {
            $summary['status'] = 'NOOP';
            $summary['reason_code'] = 'NO_FAILED_SOURCE_ACQUISITION_CHECKPOINT';
            $summary['all_passed'] = true;
            return $summary;
        }

        $failure = $this->firstFailedAcquisitionCheckpoint($acquired['source_acquisition_checkpoints'] ?? []);
        if ($failure !== []) {
            $summary['reason_code'] = $failure['reason_code'] ?? ($summary['reason_code'] ?? 'RUN_SOURCE_ACQUISITION_FAILED');
            $summary['failure_scope'] = $failure['failure_scope'] ?? ($summary['failure_scope'] ?? 'ticker');
            $summary['failed_ticker'] = $failure['ticker_code'] ?? ($summary['failed_ticker'] ?? null);
            $summary['failed_window_start'] = $failure['window_start'] ?? ($summary['failed_window_start'] ?? null);
            $summary['failed_window_end'] = $failure['window_end'] ?? ($summary['failed_window_end'] ?? null);
            $summary['http_status'] = $failure['http_status'] ?? ($summary['http_status'] ?? null);
            $summary['provider_error_sample'] = $failure['provider_error_sample'] ?? ($summary['provider_error_sample'] ?? null);
            $summary['sanitized_url'] = $failure['sanitized_url'] ?? ($summary['sanitized_url'] ?? null);
        }

        $summary['status'] = 'BLOCKED';
        $summary['publishability_state'] = 'NOT_READABLE';
        $summary['all_passed'] = false;

        return $summary;
    }

    private function firstFailedAcquisitionCheckpoint(array $checkpointRows)
    {
        foreach ($checkpointRows as $row) {
            if (is_array($row) && ($row['state'] ?? null) === 'FAILED') {
                return $row;
            }
        }

        return [];
    }


    private function mergeAcquiredRows(array $previous, array $current)
    {
        foreach (($previous['rows_by_trade_date'] ?? []) as $date => $rows) {
            if (! isset($current['rows_by_trade_date'][$date])) {
                $current['rows_by_trade_date'][$date] = $rows;
                continue;
            }

            $current['rows_by_trade_date'][$date] = $this->deduplicateRowsByTickerDate(array_merge((array) $rows, (array) $current['rows_by_trade_date'][$date]));
        }

        foreach (($previous['date_telemetry'] ?? []) as $date => $telemetry) {
            if (! isset($current['date_telemetry'][$date])) {
                $current['date_telemetry'][$date] = $telemetry;
            }
        }

        $current['window_telemetry'] = array_merge((array) ($previous['window_telemetry'] ?? []), (array) ($current['window_telemetry'] ?? []));
        $current['source_acquisition_checkpoints'] = array_merge((array) ($previous['source_acquisition_checkpoints'] ?? []), (array) ($current['source_acquisition_checkpoints'] ?? []));

        return $current;
    }

    private function deduplicateRowsByTickerDate(array $rows)
    {
        $deduped = [];
        foreach ($rows as $row) {
            $key = (string) ($row['ticker_code'] ?? '').'|'.(string) ($row['trade_date'] ?? '');
            if ($key === '|') {
                $key = spl_object_hash((object) $row);
            }
            $deduped[$key] = $row;
        }

        return array_values($deduped);
    }

    private function readAcquisitionCheckpoint($outputDir)
    {
        $path = $this->acquisitionCheckpointPath($outputDir);
        if (! is_file($path)) {
            return [];
        }

        $decoded = json_decode((string) file_get_contents($path), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function writeAcquisitionCheckpoint($outputDir, array $checkpoint)
    {
        file_put_contents($this->acquisitionCheckpointPath($outputDir), json_encode($checkpoint, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");
    }

    private function mergeAcquisitionCheckpoint(array $existing, array $incoming)
    {
        foreach ($incoming as $key => $row) {
            $existing[$key] = $row;
        }

        return $existing;
    }

    private function hasFailedAcquisitionCheckpoint(array $checkpoint)
    {
        foreach ($checkpoint as $row) {
            if (is_array($row) && in_array(($row['state'] ?? null), ['FAILED', 'RETRYING'], true)) {
                return true;
            }
        }

        return false;
    }

    private function buildAcquisitionCheckpointFromFailureContext(array $context, array $summary)
    {
        $windowStart = $context['source_window_start'] ?? $summary['failed_window_start'] ?? null;
        $windowEnd = $context['source_window_end'] ?? $summary['failed_window_end'] ?? null;
        $tickerCode = $context['ticker_code'] ?? $summary['failed_ticker'] ?? null;
        if ($windowStart === null || $windowEnd === null || $tickerCode === null || $tickerCode === '') {
            return [];
        }

        $now = Carbon::now(config('market_data.platform.timezone', 'Asia/Jakarta'))->toDateTimeString();
        $key = $windowStart.'|'.$windowEnd.'|'.strtoupper((string) $tickerCode);

        return [$key => [
            'source_acquisition_batch_id' => $context['source_acquisition_batch_id'] ?? $summary['source_acquisition_batch_id'] ?? null,
            'source_mode' => 'api',
            'source_acquisition_mode' => 'range_window',
            'requested_start' => $summary['requested_start'] ?? null,
            'requested_end' => $summary['requested_end'] ?? null,
            'warmup_start' => $summary['warmup_start'] ?? null,
            'window_start' => $windowStart,
            'window_end' => $windowEnd,
            'ticker_code' => strtoupper((string) $tickerCode),
            'state' => 'FAILED',
            'attempt_count' => (int) ($context['attempt_count'] ?? 0),
            'reason_code' => $summary['reason_code'] ?? ($context['final_reason_code'] ?? null),
            'http_status' => $context['final_http_status'] ?? ($context['http_status'] ?? null),
            'error_sample' => $context['error_sample'] ?? ($context['provider_error_sample'] ?? ($context['response_body_sample'] ?? null)),
            'provider_error_sample' => $context['provider_error_sample'] ?? null,
            'sanitized_url' => $context['sanitized_url'] ?? ($context['url'] ?? null),
            'failure_scope' => $context['failure_scope'] ?? 'ticker',
            'rows_count' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]];
    }

    private function reasonCodeFromThrowable(\Throwable $e, $fallback)
    {
        if (preg_match('/^([A-Z0-9_]+):/', (string) $e->getMessage(), $matches)) {
            return $matches[1];
        }

        if (method_exists($e, 'reasonCode')) {
            return $e->reasonCode();
        }

        return $fallback;
    }

    private function guardDateRange($startDate, $endDate)
    {
        $timezone = config('market_data.platform.timezone', 'Asia/Jakarta');
        $start = Carbon::parse($startDate, $timezone)->startOfDay();
        $end = Carbon::parse($endDate, $timezone)->startOfDay();

        if ($end->lt($start)) {
            throw new \RuntimeException('Lifecycle backfill requires end_date >= start_date.');
        }
    }

    private function ensureDirectory($dir)
    {
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
    }

    private function normalizePathForDisplay($path)
    {
        return str_replace('\\', '/', (string) $path);
    }
}

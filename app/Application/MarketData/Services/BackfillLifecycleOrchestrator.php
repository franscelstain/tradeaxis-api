<?php

namespace App\Application\MarketData\Services;

use App\Infrastructure\MarketData\Source\SourceAcquisitionException;
use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Infrastructure\Persistence\MarketData\EventRiskSourceRepository;
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
    private $artifacts;
    private $eventRiskSources;

    public function __construct(
        MarketCalendarRepository $calendar,
        TickerMasterRepository $tickers,
        ApiBackfillRangeAcquisitionService $acquisition,
        MarketDataPipelineService $pipeline,
        MarketDataEvidenceExportService $evidence,
        ReplayVerificationService $replay,
        EodRunRepository $runs,
        EodCorrectionRepository $corrections = null,
        EodPublicationRepository $publications = null,
        EodArtifactRepository $artifacts = null,
        EventRiskSourceRepository $eventRiskSources = null
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
        $this->artifacts = $artifacts;
        $this->eventRiskSources = $eventRiskSources;
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
        $firstRequestedTradingDate = (string) $requestedDates[0];
        $warmupStart = $this->warmupStart($firstRequestedTradingDate);
        $acquisitionDates = $this->calendar->tradingDatesBetween($warmupStart, $endDate);
        $tickerCodes = $this->resolveTickerUniverse($requestedDates);
        $mode = $this->resolveErrorPolicy($options);
        $withEvidence = ! empty($options['with_evidence']) || ! empty($options['with_replay']);
        $withReplay = ! empty($options['with_replay']) && empty($options['no_replay']);
        $skipPublicationReprocess = ! empty($options['skip_publication_reprocess']);
        $resume = ! empty($options['resume']);
        $onlyFailed = ! empty($options['only_failed']);
        $diagnoseSource = ! empty($options['diagnose_source']);

        if ($onlyFailed) {
            $requestedDates = $this->filterOnlyFailedDates($requestedDates, $checkpoint);
        }

        $plan = $this->sourceModeIsApi($sourceMode)
            ? $this->acquisition->plan($warmupStart, $startDate, $endDate, $acquisitionDates, $tickerCodes)
            : [
                'source_acquisition_mode' => ! empty($options['input_file']) ? 'single_input_file_filtered_by_date' : 'per_date_file',
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
            'skip_publication_reprocess' => $skipPublicationReprocess,
            'resume' => $resume,
            'only_failed' => $onlyFailed,
            'diagnose_source' => $diagnoseSource,
            'input_file' => $this->sourceModeIsManualFile($sourceMode) ? ($options['input_file'] ?? null) : null,
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

    public function executeMissingTickers($startDate, $endDate, $sourceMode = 'api', array $options = [])
    {
        $this->guardDateRange($startDate, $endDate);

        $sourceMode = $sourceMode ?: config('market_data.pipeline.default_source_mode', 'api');
        if (! $this->sourceModeIsApi($sourceMode) && ! $this->sourceModeIsManualFile($sourceMode)) {
            throw new \RuntimeException('MISSING_TICKER_BACKFILL_SOURCE_MODE_UNSUPPORTED: missing-ticker lifecycle backfill requires source_mode=api or source_mode=manual_file.');
        }

        $requestedDates = $this->calendar->tradingDatesBetween($startDate, $endDate);
        if ($requestedDates === []) {
            throw new \RuntimeException('Missing-ticker lifecycle backfill requires at least one requested trading date.');
        }

        $maxDates = (int) ($options['max_dates_per_run'] ?? config('market_data.source.api_backfill.max_dates_per_run', 20));
        if ($maxDates > 0 && count($requestedDates) > $maxDates) {
            throw new \RuntimeException('CONFIG_INVALID: requested trading date count exceeds max_dates_per_run='.$maxDates.'.');
        }

        $tickerFilter = $this->normalizeTickerCodeFilter($options['ticker_codes'] ?? null);
        $outputDir = $this->resolveMissingTickerOutputDir($startDate, $endDate, $sourceMode, $options);
        $this->ensureDirectory($outputDir);
        $checkpoint = $this->readCheckpoint($outputDir);
        $missingPlan = $this->resolveMissingTickerPlan($requestedDates, $tickerFilter);
        $tickerCodes = $missingPlan['ticker_codes'];
        $mode = $this->resolveErrorPolicy($options);
        $withEvidence = ! empty($options['with_evidence']) || ! empty($options['with_replay']);
        $withReplay = ! empty($options['with_replay']) && empty($options['no_replay']);
        $skipPublicationReprocess = ! empty($options['skip_publication_reprocess']);
        $resume = ! empty($options['resume']);

        $plan = $tickerCodes !== [] && $this->sourceModeIsApi($sourceMode)
            ? $this->acquisition->plan($startDate, $startDate, $endDate, $requestedDates, $tickerCodes)
            : [
                'source_acquisition_mode' => 'range_window',
                'warmup_start' => $startDate,
                'requested_start' => $startDate,
                'requested_end' => $endDate,
                'window_count' => $tickerCodes !== [] && $this->sourceModeIsManualFile($sourceMode) ? 1 : 0,
                'ticker_count' => count($tickerCodes),
                'trading_date_count' => count($requestedDates),
                'estimated_http_requests' => 0,
                'configured_concurrency' => (int) config('market_data.source.api_backfill.concurrency', 5),
            ];

        if ($this->sourceModeIsManualFile($sourceMode)) {
            $plan['source_acquisition_mode'] = 'manual_file';
            $plan['configured_concurrency'] = 1;
        }

        $summary = [
            'suite' => 'market_data_missing_ticker_backfill_lifecycle',
            'source_mode' => $sourceMode,
            'source_acquisition_mode' => $plan['source_acquisition_mode'],
            'source_acquisition_batch_id' => null,
            'requested_start' => $startDate,
            'requested_end' => $endDate,
            'warmup_start' => $startDate,
            'window_count' => (int) ($plan['window_count'] ?? 0),
            'estimated_http_requests' => (int) ($plan['estimated_http_requests'] ?? 0),
            'configured_concurrency' => (int) ($plan['configured_concurrency'] ?? config('market_data.source.api_backfill.concurrency', 5)),
            'ticker_count' => count($tickerCodes),
            'target_ticker_filter_count' => count($tickerFilter),
            'missing_ticker_count' => count($tickerCodes),
            'missing_bar_count' => (int) $missingPlan['missing_bar_count'],
            'trading_dates' => $requestedDates,
            'trading_date_count' => count($requestedDates),
            'missing_trade_date_count' => (int) $missingPlan['missing_trade_date_count'],
            'mode' => $mode,
            'with_evidence' => $withEvidence,
            'with_replay' => $withReplay,
            'skip_publication_reprocess' => $skipPublicationReprocess,
            'resume' => $resume,
            'input_file' => $this->sourceModeIsManualFile($sourceMode) ? ($options['input_file'] ?? null) : null,
            'output_dir' => $outputDir,
            'cases' => [],
            'warmup_cases' => [],
            'plan' => $plan + [
                'missing_ticker_codes_by_date' => $missingPlan['missing_ticker_codes_by_date'],
                'missing_bar_count' => (int) $missingPlan['missing_bar_count'],
                'missing_trade_date_count' => (int) $missingPlan['missing_trade_date_count'],
            ],
        ];

        if (! empty($options['plan'])) {
            $summary['status'] = 'PLAN_ONLY';
            $summary['all_passed'] = true;
            $this->writeSummary($outputDir, $summary);
            return $summary;
        }

        if ($tickerCodes === []) {
            foreach ($requestedDates as $requestedDate) {
                $summary['cases'][] = $this->missingTickerNoopCase($requestedDate, 'NO_MISSING_TICKERS');
            }
            $summary = $this->finalizeMissingTickerSummary($summary);
            $summary['status'] = 'NOOP';
            $this->writeSummary($outputDir, $summary);

            return $summary;
        }

        $acquired = $this->sourceModeIsApi($sourceMode) && $resume ? $this->readAcquisitionCache($outputDir) : null;
        if (! is_array($acquired)) {
            if ($this->sourceModeIsApi($sourceMode)) {
                try {
                    $acquired = $this->acquisition->acquire($startDate, $startDate, $endDate, $requestedDates, $tickerCodes, [
                        'resume' => $resume,
                        'source_acquisition_context' => 'missing_ticker_backfill',
                    ]);
                    $acquired = $this->applyManualFileOverlayToApiAcquisition(
                        $acquired,
                        $options['input_file'] ?? null,
                        $startDate,
                        $endDate,
                        $requestedDates,
                        $tickerCodes,
                        $missingPlan
                    );
                } catch (SourceAcquisitionException $e) {
                    if (empty($options['input_file'])) {
                        return $this->blockedSourceAcquisitionSummary($summary, $outputDir, $e, $plan);
                    }

                    $acquired = $this->acquireMissingTickerManualFile(
                        $options['input_file'],
                        $startDate,
                        $endDate,
                        $requestedDates,
                        $tickerCodes,
                        $missingPlan
                    );
                    $acquired = $this->recomputeApiManualOverlayAcquisitionTelemetry(
                        $acquired,
                        $requestedDates,
                        $tickerCodes,
                        'API_EXCEPTION_MANUAL_FILE_FALLBACK',
                        $missingPlan
                    );
                }
            } else {
                $acquired = $this->acquireMissingTickerManualFile(
                    $options['input_file'] ?? null,
                    $startDate,
                    $endDate,
                    $requestedDates,
                    $tickerCodes,
                    $missingPlan
                );
            }
            $this->writeAcquisitionCache($outputDir, $acquired);
            $this->writeAcquisitionCheckpoint($outputDir, $this->mergeAcquisitionCheckpoint($this->readAcquisitionCheckpoint($outputDir), $acquired['source_acquisition_checkpoints'] ?? []));
        }

        $summary['source_acquisition_batch_id'] = $acquired['source_acquisition_batch_id'] ?? null;
        $summary['source_acquisition_mode'] = $acquired['source_acquisition_mode'] ?? $summary['source_acquisition_mode'];
        $summary['window_count'] = (int) ($acquired['window_count'] ?? $summary['window_count']);
        $summary['estimated_http_requests'] = (int) ($acquired['estimated_http_requests'] ?? $summary['estimated_http_requests']);
        $summary['source_acquisition_cache'] = $this->normalizePathForDisplay($this->acquisitionCachePath($outputDir));
        $summary['skipped_checkpoint_count'] = (int) ($acquired['skipped_checkpoint_count'] ?? 0);
        $summary['source_acquisition_state'] = $acquired['source_acquisition_state'] ?? $this->aggregateAcquisitionState($acquired['window_telemetry'] ?? []);
        $summary['source_final_status'] = $acquired['source_final_status'] ?? $summary['source_acquisition_state'];
        $summary['failed_ticker_count'] = $this->sumTelemetryField($acquired['window_telemetry'] ?? [], 'failed_ticker_count');
        $summary['failed_window_count'] = $this->countFailedTelemetryWindows($acquired['window_telemetry'] ?? []);
        $diagnostic = $this->buildSourceDiagnosticFromAcquired($summary, $acquired);
        $summary['diagnostic_path'] = $this->normalizePathForDisplay($this->writeSourceAcquisitionDiagnostics($outputDir, $diagnostic));

        if ($this->missingTickerSourceAcquisitionShouldBlock($summary, $acquired)) {
            $summary = $this->blockedMissingTickerSourceAcquisitionSummary($summary, $acquired, $missingPlan, $diagnostic);
            $this->writeSummary($outputDir, $summary);

            return $summary;
        }

        foreach ($requestedDates as $requestedDate) {
            $missingCodes = $missingPlan['missing_ticker_codes_by_date'][$requestedDate] ?? [];
            if ($missingCodes === []) {
                $case = $this->missingTickerNoopCase($requestedDate, 'NO_MISSING_TICKERS');
                $summary['cases'][] = $case;
                continue;
            }

            if ($resume && $this->checkpointCaseIsComplete($requestedDate, $checkpoint, $withReplay)) {
                $case = $checkpoint['cases'][$requestedDate];
                $case['status'] = 'SKIPPED_VERIFIED';
                $case['resume_skip'] = true;
                $summary['cases'][] = $case;
                continue;
            }

            $case = $this->processMissingTickerDate(
                $requestedDate,
                $sourceMode,
                $acquired,
                $missingCodes,
                $missingPlan['universe_by_date'][$requestedDate] ?? [],
                $withEvidence,
                $withReplay,
                $outputDir,
                $skipPublicationReprocess
            );
            $summary['cases'][] = $case;
            $checkpoint = $this->mergeCheckpoint($checkpoint, $requestedDate, $case);
            $this->writeCheckpoint($outputDir, $checkpoint);

            if ($this->caseShouldStop($case) && $mode === 'stop_on_error') {
                break;
            }
        }

        $summary = $this->finalizeMissingTickerSummary($summary);
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
            $latestRun = $this->runs->findLatestForRequestedDate($requestedDate, $sourceMode);
            if ($latestRun && $this->runFailedOrHeld($latestRun)) {
                $run = $latestRun;
                $case['run_id'] = (int) $run->run_id;
                $case = array_merge($case, $this->mutationImpactCaseFields($run));
            } else {
                $run = null;
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

        $skipRequestedDate = ($case['promote_status'] ?? null) === 'SUCCESS' && ! empty($case['readable']);
        $case = $this->executePublicationReprocessForCase($case, $sourceMode, $withEvidence, $withReplay, $outputDir, $skipRequestedDate);

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

    private function processMissingTickerDate($requestedDate, $sourceMode, array $acquired, array $missingCodes, array $universeRows, $withEvidence, $withReplay, $outputDir, $skipPublicationReprocess = false)
    {
        $case = [
            'requested_date' => $requestedDate,
            'missing_ticker_count' => count($missingCodes),
            'missing_ticker_codes' => $missingCodes,
            'import_status' => 'PENDING',
            'promote_status' => 'SKIPPED',
            'evidence_status' => $withEvidence ? 'PENDING' : 'SKIPPED',
            'fixture_status' => $withReplay ? 'PENDING' : 'SKIPPED',
            'replay_status' => $withReplay ? 'PENDING' : 'SKIPPED',
            'readable' => false,
        ];
        $run = null;

        try {
            $providerRows = $this->filterSourceRowsForTickerCodes($acquired['rows_by_trade_date'][$requestedDate] ?? [], $missingCodes);
            $sourceAcquisition = $this->missingTickerDateTelemetry(
                $requestedDate,
                $sourceMode,
                $acquired['date_telemetry'][$requestedDate] ?? [],
                $missingCodes,
                $providerRows
            );
            $candidateRows = $this->buildMissingTickerCandidateRows($requestedDate, $providerRows, $missingCodes, $universeRows);

            $case['tickers_expected'] = count($missingCodes);
            $case['tickers_success'] = (int) ($sourceAcquisition['success_ticker_count'] ?? 0);
            $case['tickers_failed'] = (int) ($sourceAcquisition['failed_ticker_count'] ?? 0);
            $case['candidate_source_row_count'] = count($candidateRows);
            $case['source_acquisition_state'] = $sourceAcquisition['source_acquisition_state'] ?? null;
            $case['missing_source_row_count'] = count($providerRows);

            $run = $this->pipeline->importDailyFromAcquiredRows($requestedDate, $sourceMode, $candidateRows, $sourceAcquisition);
            $case['run_id'] = (int) $run->run_id;
            $case['import_status'] = $this->runFailedOrHeld($run) ? (string) $run->terminal_status : 'SUCCESS';
            $case = array_merge($case, $this->mutationImpactCaseFields($run));
        } catch (\Throwable $e) {
            $latestRun = $this->runs->findLatestForRequestedDate($requestedDate, $sourceMode);
            if ($latestRun && $this->runFailedOrHeld($latestRun)) {
                $run = $latestRun;
                $case['run_id'] = (int) $run->run_id;
                $case = array_merge($case, $this->mutationImpactCaseFields($run));
            } else {
                $run = null;
            }
            $case['import_status'] = 'FAILED';
            $case['reason_code'] = $this->reasonCodeFromThrowable($e, 'MISSING_TICKER_IMPORT_FAILED');
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
                $case['reason_code'] = $this->reasonCodeFromThrowable($e, 'MISSING_TICKER_PROMOTE_FAILED');
                $case['error_message'] = $e->getMessage();
            }
        }

        $skipRequestedDate = ($case['promote_status'] ?? null) === 'SUCCESS' && ! empty($case['readable']);
        if ($skipPublicationReprocess) {
            if (! $skipRequestedDate && $this->publicationReprocessIncludesRequestedDate($case)) {
                $case = $this->keepOnlyRequestedDatePublicationReprocess($case);
                $case = $this->executePublicationReprocessForCase($case, $sourceMode, $withEvidence, $withReplay, $outputDir, false);
            } else {
                $case = $this->skipPublicationReprocessForCase($case, 'SKIPPED_BY_OPTION');
            }
        } else {
            $case = $this->executePublicationReprocessForCase($case, $sourceMode, $withEvidence, $withReplay, $outputDir, $skipRequestedDate);
        }

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
                $case['reason_code'] = $this->reasonCodeFromThrowable($e, 'MISSING_TICKER_EVIDENCE_EXPORT_FAILED');
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
                $case['reason_code'] = $this->reasonCodeFromThrowable($e, 'MISSING_TICKER_REPLAY_FAILED');
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
        if (($case['import_status'] ?? null) === 'FAILED') {
            return $this->skipPublicationReprocessForCase($case, 'PRIMARY_IMPORT_FAILED', 'PRIMARY_IMPORT_REQUIRED');
        }

        $candidateDates = $this->publicationReprocessCandidateDates($case);
        $readableCorrectionDates = $this->publicationReprocessReadableCorrectionCandidateDates($case);
        if ($skipRequestedDate) {
            $requestedDate = (string) ($case['requested_date'] ?? '');
            $candidateDates = array_values(array_filter($candidateDates, function ($date) use ($requestedDate) {
                return (string) $date !== $requestedDate;
            }));
            $readableCorrectionDates = array_values(array_filter($readableCorrectionDates, function ($date) use ($requestedDate) {
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
        $readableCorrectionSet = array_fill_keys($readableCorrectionDates, true);
        $failedDates = [];
        $republishedDates = [];
        $reprocessRuns = [];
        $republicationModes = [];
        $correctionIds = [];
        $evidenceExported = 0;
        $fixturesGenerated = 0;
        $replayVerified = 0;
        $blockedReason = $case['publication_reprocess_blocked_reason_code'] ?? null;
        $failureReason = null;

        foreach ($candidateDates as $tradeDate) {
            $tradeDate = (string) $tradeDate;
            if (in_array($tradeDate, $blockedDates, true) && ! isset($readableCorrectionSet[$tradeDate])) {
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

                if (isset($readableCorrectionSet[$tradeDate]) || $this->isReadableRun($seedRun)) {
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
                if (isset($autoCorrectionId) && $autoCorrectionId !== null) {
                    $correctionIds[] = (int) $autoCorrectionId;
                    $republicationModes[] = 'AUTOMATED_READABLE_CORRECTION';
                } else {
                    $republicationModes[] = 'AUTOMATED_NON_READABLE_DATES';
                }

                if (! $this->isReadableRun($promotedRun)) {
                    $blockedDates[] = $tradeDate;
                    $blockedReason = $blockedReason ?: ($promotedRun->final_reason_code ?? 'PUBLICATION_REPROCESS_NOT_READABLE');
                    continue;
                }

                $blockedDates = $this->removeDateFromList($blockedDates, $tradeDate);
                $failedDates = $this->removeDateFromList($failedDates, $tradeDate);
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
        $correctionIds = array_values(array_unique(array_map('intval', $correctionIds)));
        sort($correctionIds);
        if ($blockedDates === []) {
            $blockedReason = null;
        }
        if ($failedDates === []) {
            $failureReason = null;
        }

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
        $case['publication_reprocess_readable_correction_candidate_trade_dates'] = $readableCorrectionDates;
        $case['publication_reprocess_blocked_trade_dates'] = $blockedDates;
        $case['publication_reprocess_failed_trade_dates'] = $failedDates;
        $case['publication_reprocess_blocked_reason_code'] = $blockedReason;
        $case['publication_reprocess_failure_reason_code'] = $failureReason;
        $case['publication_reprocess_republication_mode'] = $this->resolvedRepublicationMode($state, $republicationModes);
        $case['publication_reprocess_correction_ids'] = $correctionIds;
        $case['publication_reprocess_correction_id'] = count($correctionIds) === 1 ? $correctionIds[0] : null;
        $case['publication_reprocess_evidence_exported_count'] = $evidenceExported;
        $case['publication_reprocess_fixtures_generated_count'] = $fixturesGenerated;
        $case['publication_reprocess_replay_verified_count'] = $replayVerified;
        $case['publication_reprocess_runs'] = $reprocessRuns;
        $case['publication_reprocess_summary'] = [
            'execution_state' => $state,
            'republished_trade_date_count' => count($republishedDates),
            'republished_trade_dates' => $republishedDates,
            'candidate_trade_dates' => $candidateDates,
            'readable_correction_candidate_trade_dates' => $readableCorrectionDates,
            'blocked_trade_dates' => $blockedDates,
            'failed_trade_dates' => $failedDates,
            'blocked_reason_code' => $blockedReason,
            'failure_reason_code' => $failureReason,
            'evidence_exported_count' => $evidenceExported,
            'fixtures_generated_count' => $fixturesGenerated,
            'replay_verified_count' => $replayVerified,
            'republication_mode' => $case['publication_reprocess_republication_mode'],
            'correction_ids' => $correctionIds,
            'correction_id' => $case['publication_reprocess_correction_id'],
        ];

        if ($state === 'BLOCKED_REQUIRES_CORRECTION') {
            $case['reason_code'] = $blockedReason ?: ($case['reason_code'] ?? 'AFFECTED_PUBLICATION_REQUIRES_CORRECTION');
        } elseif ($state === 'FAILED') {
            $case['reason_code'] = $failureReason ?: ($case['reason_code'] ?? 'PUBLICATION_REPROCESS_FAILED');
        }

        $this->syncPublicationReprocessNotes($case);

        return $case;
    }

    private function publicationReprocessIncludesRequestedDate(array $case)
    {
        $requestedDate = (string) ($case['requested_date'] ?? '');
        if ($requestedDate === '') {
            return false;
        }

        return in_array($requestedDate, $this->publicationReprocessCandidateDates($case), true)
            || in_array($requestedDate, $this->publicationReprocessReadableCorrectionCandidateDates($case), true);
    }

    private function keepOnlyRequestedDatePublicationReprocess(array $case)
    {
        $requestedDate = (string) ($case['requested_date'] ?? '');
        $candidateDates = $this->publicationReprocessCandidateDates($case);
        $readableCorrectionDates = $this->publicationReprocessReadableCorrectionCandidateDates($case);
        $blockedDates = $this->parseCsvList($case['publication_reprocess_blocked_trade_dates'] ?? '');

        $case['publication_reprocess_deferred_by_option'] = true;
        $case['publication_reprocess_deferred_trade_dates'] = $this->removeDateFromList($candidateDates, $requestedDate);
        $case['publication_reprocess_deferred_readable_correction_trade_dates'] = $this->removeDateFromList($readableCorrectionDates, $requestedDate);
        $case['publication_reprocess_candidate_trade_dates'] = $requestedDate !== '' && in_array($requestedDate, $candidateDates, true)
            ? [$requestedDate]
            : [];
        $case['publication_reprocess_readable_correction_candidate_trade_dates'] = $requestedDate !== '' && in_array($requestedDate, $readableCorrectionDates, true)
            ? [$requestedDate]
            : [];
        $case['publication_reprocess_blocked_trade_dates'] = $requestedDate !== '' && in_array($requestedDate, $blockedDates, true)
            ? [$requestedDate]
            : [];
        $case['publication_reprocess_summary'] = array_merge(
            $case['publication_reprocess_summary'] ?? [],
            [
                'candidate_trade_dates' => $case['publication_reprocess_candidate_trade_dates'],
                'readable_correction_candidate_trade_dates' => $case['publication_reprocess_readable_correction_candidate_trade_dates'],
                'blocked_trade_dates' => $case['publication_reprocess_blocked_trade_dates'],
                'deferred_by_option' => true,
                'deferred_trade_dates' => $case['publication_reprocess_deferred_trade_dates'],
            ]
        );

        return $case;
    }

    private function skipPublicationReprocessForCase(array $case, $reasonCode, $republicationMode = 'DEFERRED_BY_OPERATOR')
    {
        $case['publication_reprocess_state'] = 'NOOP';
        $case['publication_reprocess_republished_trade_date_count'] = (int) ($case['publication_reprocess_republished_trade_date_count'] ?? 0);
        $case['publication_reprocess_summary'] = array_merge(
            $case['publication_reprocess_summary'] ?? [],
            [
                'execution_state' => 'NOOP',
                'republished_trade_date_count' => (int) ($case['publication_reprocess_republished_trade_date_count'] ?? 0),
                'blocked_reason_code' => $reasonCode,
                'republication_mode' => $republicationMode,
            ]
        );

        return $case;
    }

    private function resolvedRepublicationMode($state, array $modes)
    {
        if ($state === 'NOOP') {
            return 'NOT_REQUIRED';
        }

        if ($state === 'FAILED') {
            return 'FAILED_IMPACT_REPUBLICATION';
        }

        if ($state === 'BLOCKED_REQUIRES_CORRECTION') {
            return 'MANUAL_CORRECTION_REQUIRED';
        }

        $modes = array_values(array_unique(array_filter(array_map('strval', $modes), function ($mode) {
            return $mode !== '' && $mode !== 'NOT_REQUIRED';
        })));
        sort($modes);

        if (count($modes) > 1) {
            return 'AUTOMATED_MIXED_IMPACT_REPUBLICATION';
        }

        return $modes[0] ?? 'AUTOMATED_IMPACT_REPUBLICATION';
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

    private function removeDateFromList(array $dates, $tradeDate)
    {
        return array_values(array_filter($dates, function ($date) use ($tradeDate) {
            return (string) $date !== (string) $tradeDate;
        }));
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
                    ! empty($case['publication_reprocess_readable_correction_candidate_trade_dates']) ? 'publication_reprocess_readable_correction_candidate_trade_dates='.$this->compactList((array) $case['publication_reprocess_readable_correction_candidate_trade_dates']) : null,
                    ! empty($case['publication_reprocess_blocked_trade_dates']) ? 'publication_reprocess_blocked_trade_dates='.$this->compactList((array) $case['publication_reprocess_blocked_trade_dates']) : null,
                    ! empty($case['publication_reprocess_failed_trade_dates']) ? 'publication_reprocess_failed_trade_dates='.$this->compactList((array) $case['publication_reprocess_failed_trade_dates']) : null,
                    ! empty($case['publication_reprocess_blocked_reason_code']) ? 'publication_reprocess_blocked_reason_code='.(string) $case['publication_reprocess_blocked_reason_code'] : null,
                    ! empty($case['publication_reprocess_failure_reason_code']) ? 'publication_reprocess_failure_reason_code='.(string) $case['publication_reprocess_failure_reason_code'] : null,
                    ! empty($case['publication_reprocess_republication_mode']) ? 'publication_reprocess_republication_mode='.(string) $case['publication_reprocess_republication_mode'] : null,
                    ! empty($case['publication_reprocess_correction_ids']) ? 'publication_reprocess_correction_ids='.$this->compactList((array) $case['publication_reprocess_correction_ids']) : null,
                    ! empty($case['publication_reprocess_correction_id']) ? 'publication_reprocess_correction_id='.(int) $case['publication_reprocess_correction_id'] : null,
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

    private function publicationReprocessReadableCorrectionCandidateDates(array $case)
    {
        $dates = [];
        if (! empty($case['publication_reprocess_summary']['readable_correction_candidate_trade_dates']) && is_array($case['publication_reprocess_summary']['readable_correction_candidate_trade_dates'])) {
            $dates = $case['publication_reprocess_summary']['readable_correction_candidate_trade_dates'];
        }

        if ($dates === []) {
            $dates = $this->parseCsvList($case['publication_reprocess_readable_correction_candidate_trade_dates'] ?? '');
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
            'publication_reprocess_readable_correction_candidate_trade_dates',
            'publication_reprocess_blocked_trade_dates',
            'publication_reprocess_failed_trade_dates',
            'publication_reprocess_blocked_reason_code',
            'publication_reprocess_failure_reason_code',
            'publication_reprocess_republication_mode',
            'publication_reprocess_correction_ids',
            'publication_reprocess_correction_id',
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
                'readable_correction_candidate_trade_dates' => $this->parseCsvList($fields['publication_reprocess_readable_correction_candidate_trade_dates'] ?? ''),
                'blocked_trade_dates' => $this->parseCsvList($fields['publication_reprocess_blocked_trade_dates'] ?? ''),
                'failed_trade_dates' => $this->parseCsvList($fields['publication_reprocess_failed_trade_dates'] ?? ''),
                'blocked_reason_code' => $fields['publication_reprocess_blocked_reason_code'] ?? null,
                'failure_reason_code' => $fields['publication_reprocess_failure_reason_code'] ?? null,
                'republication_mode' => $fields['publication_reprocess_republication_mode'] ?? 'NOT_REQUIRED',
                'correction_ids' => $this->parseCsvList($fields['publication_reprocess_correction_ids'] ?? ''),
                'correction_id' => isset($fields['publication_reprocess_correction_id']) ? (int) $fields['publication_reprocess_correction_id'] : null,
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
        if (is_array($value)) {
            $items = array_values(array_unique(array_filter(array_map(function ($item) {
                return trim((string) $item);
            }, $value), function ($item) {
                return $item !== '';
            })));

            sort($items);

            return $items;
        }

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

    private function sortedUniqueList(array $values)
    {
        $values = array_values(array_unique(array_filter(array_map(function ($value) {
            return trim((string) $value);
        }, $values), function ($value) {
            return $value !== '';
        })));

        sort($values);

        return $values;
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
        $summary['dates_blocked'] = count(array_filter($cases, function ($case) {
            return in_array(($case['status'] ?? null), ['BLOCKED', 'SOURCE_ACQUISITION_BLOCKED'], true);
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
        $summary['publication_reprocess_correction_ids'] = $this->sortedUniqueList(array_reduce($cases, function ($carry, $case) {
            return array_merge($carry, (array) ($case['publication_reprocess_correction_ids'] ?? []));
        }, []));
        $summary['publication_reprocess_republication_mode'] = $this->resolvedRepublicationMode(
            $summary['publication_reprocess_state'],
            array_values(array_filter(array_map(function ($case) {
                return $case['publication_reprocess_republication_mode'] ?? null;
            }, $cases)))
        );
        $summary['all_passed'] = $summary['dates_failed'] === 0 && $summary['dates_held'] === 0;
        $summary['status'] = $summary['all_passed'] ? 'SUCCESS' : 'PARTIAL';

        return $summary;
    }

    private function resolveTickerUniverse(array $requestedDates)
    {
        $codes = [];
        foreach ($requestedDates as $date) {
            foreach ($this->filterSuspendedUniverseRows($this->tickers->getUniverseForTradeDate($date), $date) as $row) {
                if (isset($row['ticker_code']) && trim((string) $row['ticker_code']) !== '') {
                    $codes[strtoupper(trim((string) $row['ticker_code']))] = true;
                }
            }
        }

        $codes = array_keys($codes);
        sort($codes);

        return $codes;
    }

    private function resolveMissingTickerPlan(array $requestedDates, array $tickerFilter)
    {
        $artifacts = $this->artifactRepository();
        $filterSet = array_fill_keys($tickerFilter, true);
        $tickerCodes = [];
        $missingByDate = [];
        $universeByDate = [];
        $missingBarCount = 0;
        $missingTradeDateCount = 0;

        foreach ($requestedDates as $date) {
            $fullUniverseRows = [];
            $universeRows = [];
            foreach ($this->filterSuspendedUniverseRows($this->tickers->getUniverseForTradeDate($date), $date) as $row) {
                $code = strtoupper(trim((string) ($row['ticker_code'] ?? '')));
                $tickerId = (int) ($row['ticker_id'] ?? 0);
                if ($code === '' || $tickerId <= 0) {
                    continue;
                }

                $universeRow = [
                    'ticker_id' => $tickerId,
                    'ticker_code' => $code,
                ];
                $fullUniverseRows[] = $universeRow;

                if ($filterSet !== [] && ! isset($filterSet[$code])) {
                    continue;
                }

                $universeRows[] = $universeRow;
            }

            $existingIds = array_fill_keys($artifacts->loadCanonicalBarTickerIdsForTradeDate($date, null), true);
            $missingCodes = [];
            foreach ($universeRows as $row) {
                if (! isset($existingIds[(int) $row['ticker_id']])) {
                    $missingCodes[] = $row['ticker_code'];
                    $tickerCodes[$row['ticker_code']] = true;
                }
            }

            $missingCodes = $this->sortedUniqueList($missingCodes);
            $missingByDate[$date] = $missingCodes;
            $universeByDate[$date] = $fullUniverseRows;
            $missingBarCount += count($missingCodes);
            if ($missingCodes !== []) {
                $missingTradeDateCount++;
            }
        }

        $tickerCodes = array_keys($tickerCodes);
        sort($tickerCodes);

        return [
            'ticker_codes' => $tickerCodes,
            'missing_ticker_codes_by_date' => $missingByDate,
            'universe_by_date' => $universeByDate,
            'missing_bar_count' => $missingBarCount,
            'missing_trade_date_count' => $missingTradeDateCount,
        ];
    }

    private function filterSuspendedUniverseRows(array $universeRows, $tradeDate)
    {
        if (! $this->eventRiskSources instanceof EventRiskSourceRepository || $universeRows === []) {
            return $universeRows;
        }

        $tickerIds = array_values(array_filter(array_map(function ($row) {
            return (int) ($row['ticker_id'] ?? 0);
        }, $universeRows)));

        if ($tickerIds === []) {
            return $universeRows;
        }

        $suspendedIds = array_fill_keys($this->eventRiskSources->suspendedTickerIdsAsOf($tickerIds, $tradeDate), true);
        if ($suspendedIds === []) {
            return $universeRows;
        }

        return array_values(array_filter($universeRows, function ($row) use ($suspendedIds) {
            $tickerId = (int) ($row['ticker_id'] ?? 0);

            return $tickerId > 0 && ! isset($suspendedIds[$tickerId]);
        }));
    }

    private function buildMissingTickerCandidateRows($requestedDate, array $providerRows, array $missingCodes, array $universeRows)
    {
        if ($providerRows === []) {
            throw new SourceAcquisitionException(
                'Missing-ticker source acquisition returned zero rows for requested missing tickers.',
                'RUN_SOURCE_NO_VALID_DATA',
                0,
                null,
                [
                    'trade_date' => $requestedDate,
                    'missing_ticker_codes' => $missingCodes,
                    'source_acquisition_context' => 'missing_ticker_backfill',
                ]
            );
        }

        $sourceName = $this->sourceNameForCandidateRows($providerRows);
        $missingSet = array_fill_keys($missingCodes, true);
        $universeCodeById = [];
        foreach ($universeRows as $row) {
            $tickerId = (int) ($row['ticker_id'] ?? 0);
            $code = strtoupper(trim((string) ($row['ticker_code'] ?? '')));
            if ($tickerId > 0 && $code !== '') {
                $universeCodeById[$tickerId] = $code;
            }
        }

        $rowsByCode = [];
        foreach ($this->artifactRepository()->loadBarsForTradeDate($requestedDate, null) as $tickerId => $row) {
            $tickerId = (int) $tickerId;
            if (! isset($universeCodeById[$tickerId])) {
                continue;
            }

            $code = $universeCodeById[$tickerId];
            if (isset($missingSet[$code])) {
                continue;
            }

            $rowsByCode[$code] = $this->currentBarToSourceRow((array) $row, $code, $sourceName);
        }

        foreach ($providerRows as $row) {
            $code = strtoupper(trim((string) ($row['ticker_code'] ?? '')));
            if ($code === '' || ! isset($missingSet[$code])) {
                continue;
            }

            $row['ticker_code'] = $code;
            $row['trade_date'] = $requestedDate;
            $row['source_name'] = $sourceName;
            $rowsByCode[$code] = $row;
        }

        ksort($rowsByCode);

        return array_values($rowsByCode);
    }

    private function currentBarToSourceRow(array $row, $tickerCode, $sourceName)
    {
        return [
            'ticker_code' => strtoupper((string) $tickerCode),
            'trade_date' => (string) $row['trade_date'],
            'open' => $row['open'],
            'high' => $row['high'],
            'low' => $row['low'],
            'close' => $row['close'],
            'volume' => $row['volume'],
            'adj_close' => $row['adj_close'],
            'source_name' => $sourceName,
            'canonical_source' => $this->canonicalSourceNameFromCurrentBar($row, $sourceName),
            'captured_at' => $row['created_at'] ?? Carbon::now(config('market_data.platform.timezone', 'Asia/Jakarta'))->toDateTimeString(),
            'source_row_ref' => 'current:'.$row['trade_date'].':'.strtoupper((string) $tickerCode),
        ];
    }

    private function canonicalSourceNameFromCurrentBar(array $row, $fallbackSourceName)
    {
        $sourceName = strtoupper(trim((string) ($row['source'] ?? '')));

        return $sourceName !== '' ? $sourceName : strtoupper((string) $fallbackSourceName);
    }

    private function sourceNameForCandidateRows(array $rows)
    {
        foreach ($rows as $row) {
            $sourceName = strtoupper(trim((string) ($row['source_name'] ?? '')));
            if ($sourceName !== '') {
                return $sourceName;
            }
        }

        return strtoupper((string) config('market_data.source.default_source_name', 'YAHOO'));
    }

    private function filterSourceRowsForTickerCodes(array $rows, array $tickerCodes)
    {
        $set = array_fill_keys($tickerCodes, true);

        return array_values(array_filter($rows, function ($row) use ($set) {
            $code = strtoupper(trim((string) ($row['ticker_code'] ?? '')));

            return $code !== '' && isset($set[$code]);
        }));
    }

    private function applyManualFileOverlayToApiAcquisition(array $acquired, $inputFile, $startDate, $endDate, array $requestedDates, array $tickerCodes, array $missingPlan)
    {
        if ($inputFile === null || trim((string) $inputFile) === '') {
            return $acquired;
        }

        $overlayCandidateCodes = $this->expectedMissingTickerCodesForDates($missingPlan, $requestedDates, $tickerCodes);
        if ($overlayCandidateCodes === []) {
            $overlayCandidateCodes = $this->failedTickerCodesFromAcquired($acquired);
        }

        if ($overlayCandidateCodes === []) {
            return $acquired;
        }

        $overlayPlan = $this->overlayMissingTickerPlanForTickerCodes($missingPlan, $overlayCandidateCodes);
        $overlayTickerCodes = $overlayPlan['ticker_codes'];
        if ($overlayTickerCodes === []) {
            return $acquired;
        }

        $overlay = $this->acquireMissingTickerManualFile(
            $inputFile,
            $startDate,
            $endDate,
            $requestedDates,
            $overlayTickerCodes,
            $overlayPlan
        );

        $overlayRowCount = array_sum(array_map('count', (array) ($overlay['rows_by_trade_date'] ?? [])));
        if ($overlayRowCount <= 0) {
            return $acquired;
        }

        foreach (($overlay['rows_by_trade_date'] ?? []) as $date => $rows) {
            if (! isset($acquired['rows_by_trade_date'][$date])) {
                $acquired['rows_by_trade_date'][$date] = [];
            }

            $acquired['rows_by_trade_date'][$date] = $this->deduplicateRowsByTickerDate(array_merge(
                (array) $acquired['rows_by_trade_date'][$date],
                (array) $rows
            ));
        }

        $acquired['manual_overlay'] = [
            'input_file' => $overlay['source_input_file'] ?? $inputFile,
            'ticker_codes' => $overlayTickerCodes,
            'row_count' => $overlayRowCount,
            'source_file_hash' => $overlay['source_file_hash'] ?? null,
            'source_file_hash_algorithm' => $overlay['source_file_hash_algorithm'] ?? null,
            'source_file_size_bytes' => $overlay['source_file_size_bytes'] ?? null,
            'source_file_row_count' => $overlay['source_file_row_count'] ?? null,
        ];

        return $this->recomputeApiManualOverlayAcquisitionTelemetry(
            $acquired,
            $requestedDates,
            $tickerCodes,
            'API_MANUAL_FILE_OVERLAY',
            $missingPlan
        );
    }

    private function overlayMissingTickerPlanForTickerCodes(array $missingPlan, array $tickerCodes)
    {
        $tickerSet = array_fill_keys($tickerCodes, true);
        $missingByDate = [];
        $includedTickers = [];
        $missingBarCount = 0;
        $missingTradeDateCount = 0;

        foreach ((array) ($missingPlan['missing_ticker_codes_by_date'] ?? []) as $date => $codes) {
            $selectedCodes = [];
            foreach ((array) $codes as $code) {
                $code = strtoupper(trim((string) $code));
                if ($code === '' || ! isset($tickerSet[$code])) {
                    continue;
                }

                $selectedCodes[] = $code;
                $includedTickers[$code] = true;
            }

            $selectedCodes = $this->sortedUniqueList($selectedCodes);
            $missingByDate[$date] = $selectedCodes;
            $missingBarCount += count($selectedCodes);
            if ($selectedCodes !== []) {
                $missingTradeDateCount++;
            }
        }

        $includedTickers = array_keys($includedTickers);
        sort($includedTickers);

        return [
            'ticker_codes' => $includedTickers,
            'missing_ticker_codes_by_date' => $missingByDate,
            'universe_by_date' => $missingPlan['universe_by_date'] ?? [],
            'missing_bar_count' => $missingBarCount,
            'missing_trade_date_count' => $missingTradeDateCount,
        ];
    }

    private function recomputeApiManualOverlayAcquisitionTelemetry(array $acquired, array $requestedDates, array $tickerCodes, $overlayMode, array $missingPlan = [])
    {
        $tickerCodes = $this->sortedUniqueList($tickerCodes);
        $windows = $this->normalizeAcquisitionWindows($acquired, $requestedDates);
        $rowsByDate = (array) ($acquired['rows_by_trade_date'] ?? []);
        $dateTelemetry = [];

        foreach ($requestedDates as $date) {
            $expectedCodes = $this->expectedMissingTickerCodesForDate($missingPlan, $date, $tickerCodes);
            $dateRows = (array) ($rowsByDate[$date] ?? []);
            $returnedCodes = $this->filterTickerCodesToExpected($this->tickerCodesFromRows($dateRows), $expectedCodes);
            $failedCodes = array_values(array_diff($expectedCodes, $returnedCodes));
            sort($failedCodes);

            $dateTelemetry[$date] = [
                'source_mode' => 'api',
                'source_acquisition_context' => 'missing_ticker_backfill',
                'source_acquisition_mode' => 'api_manual_file_overlay',
                'trade_date' => $date,
                'expected_ticker_count' => count($expectedCodes),
                'success_ticker_count' => count($returnedCodes),
                'failed_ticker_count' => count($failedCodes),
                'missing_ticker_codes' => $failedCodes,
                'returned_row_count' => count($dateRows),
                'accepted_row_count' => count($dateRows),
                'rejected_row_count' => 0,
                'invalid_row_count' => 0,
                'source_acquisition_state' => count($returnedCodes) === 0 && count($expectedCodes) > 0
                    ? 'FAILED'
                    : (count($failedCodes) === 0 ? 'SUCCESS' : 'PARTIAL_SUCCESS'),
                'source_final_status' => count($returnedCodes) === 0 && count($expectedCodes) > 0
                    ? 'FAILED'
                    : (count($failedCodes) === 0 ? 'SUCCESS' : 'PARTIAL'),
                'final_reason_code' => count($returnedCodes) === 0 && count($expectedCodes) > 0
                    ? 'RUN_SOURCE_NO_VALID_DATA'
                    : (count($failedCodes) > 0 ? 'RUN_SOURCE_PARTIAL_RESPONSE' : null),
                'manual_overlay_mode' => $overlayMode,
            ];
        }

        $windowTelemetry = [];
        $checkpoints = [];
        $totalFailed = 0;
        $totalReturnedRows = 0;
        $batchId = $acquired['source_acquisition_batch_id'] ?? ('API_MANUAL_FILE_OVERLAY_'.str_replace('-', '', (string) reset($requestedDates)).'_'.str_replace('-', '', (string) end($requestedDates)).'_001');
        $now = Carbon::now(config('market_data.platform.timezone', 'Asia/Jakarta'))->toDateTimeString();

        foreach ($windows as $window) {
            $windowStart = (string) ($window['start'] ?? $window['window_start'] ?? reset($requestedDates));
            $windowEnd = (string) ($window['end'] ?? $window['window_end'] ?? end($requestedDates));
            $windowRows = [];
            $windowDates = [];
            foreach ($requestedDates as $date) {
                if (strcmp($date, $windowStart) < 0 || strcmp($date, $windowEnd) > 0) {
                    continue;
                }

                $windowDates[] = $date;
                $windowRows = array_merge($windowRows, (array) ($rowsByDate[$date] ?? []));
            }

            if ($windowDates === []) {
                continue;
            }

            $expectedCodes = $this->expectedMissingTickerCodesForDates($missingPlan, $windowDates, $tickerCodes);
            $returnedCodes = $this->filterTickerCodesToExpected($this->tickerCodesFromRows($windowRows), $expectedCodes);
            $rowCounts = $this->rowCountsByTickerCode($windowRows);
            $failedCodes = array_values(array_diff($expectedCodes, $returnedCodes));
            sort($failedCodes);
            $totalFailed += count($failedCodes);
            $totalReturnedRows += count($windowRows);

            $state = count($returnedCodes) === 0 && count($expectedCodes) > 0
                ? 'FAILED'
                : (count($failedCodes) === 0 ? 'SUCCESS' : 'PARTIAL_SUCCESS');
            $finalStatus = $state === 'PARTIAL_SUCCESS' ? 'PARTIAL' : $state;

            $windowTelemetry[] = [
                'source_acquisition_batch_id' => $batchId,
                'source_mode' => 'api',
                'source_acquisition_mode' => 'api_manual_file_overlay',
                'source_window_start' => $windowStart,
                'source_window_end' => $windowEnd,
                'source_acquisition_state' => $state,
                'source_final_status' => $finalStatus,
                'expected_ticker_count' => count($expectedCodes),
                'success_ticker_count' => count($returnedCodes),
                'failed_ticker_count' => count($failedCodes),
                'failed_ticker_codes' => $failedCodes,
                'missing_ticker_codes' => $failedCodes,
                'returned_row_count' => count($windowRows),
                'accepted_row_count' => count($windowRows),
                'rejected_row_count' => 0,
                'invalid_row_count' => 0,
                'final_reason_code' => count($failedCodes) > 0 ? 'RUN_SOURCE_PARTIAL_RESPONSE' : null,
                'manual_overlay_mode' => $overlayMode,
            ];

            foreach ($expectedCodes as $tickerCode) {
                $stateForTicker = isset($rowCounts[$tickerCode]) ? 'SUCCESS' : 'FAILED';
                $checkpoints[$windowStart.'|'.$windowEnd.'|'.$tickerCode] = [
                    'source_acquisition_batch_id' => $batchId,
                    'source_mode' => 'api',
                    'source_acquisition_mode' => 'api_manual_file_overlay',
                    'requested_start' => $acquired['requested_start'] ?? null,
                    'requested_end' => $acquired['requested_end'] ?? null,
                    'warmup_start' => $acquired['warmup_start'] ?? null,
                    'window_start' => $windowStart,
                    'window_end' => $windowEnd,
                    'ticker_code' => $tickerCode,
                    'state' => $stateForTicker,
                    'attempt_count' => 0,
                    'reason_code' => $stateForTicker === 'FAILED' ? 'RUN_SOURCE_NO_VALID_DATA' : null,
                    'http_status' => null,
                    'error_sample' => null,
                    'provider_error_sample' => null,
                    'sanitized_url' => null,
                    'failure_scope' => $stateForTicker === 'FAILED' ? 'ticker' : null,
                    'rows_count' => (int) ($rowCounts[$tickerCode] ?? 0),
                    'manual_overlay_mode' => $overlayMode,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $acquired['source_acquisition_mode'] = 'api_manual_file_overlay';
        $acquired['configured_concurrency'] = $acquired['configured_concurrency'] ?? 1;
        $acquired['date_telemetry'] = $dateTelemetry;
        $acquired['window_telemetry'] = $windowTelemetry;
        $acquired['source_acquisition_checkpoints'] = $checkpoints;
        $acquired['source_acquisition_state'] = $totalFailed === 0 ? 'SUCCESS' : ($totalReturnedRows > 0 ? 'PARTIAL_SUCCESS' : 'FAILED');
        $acquired['source_final_status'] = $totalFailed === 0 ? 'SUCCESS' : ($totalReturnedRows > 0 ? 'PARTIAL' : 'FAILED');
        $acquired['manual_overlay_mode'] = $overlayMode;

        return $acquired;
    }

    private function normalizeAcquisitionWindows(array $acquired, array $requestedDates)
    {
        $windows = [];
        foreach ((array) ($acquired['windows'] ?? []) as $window) {
            if (! is_array($window)) {
                continue;
            }

            $start = $window['start'] ?? $window['window_start'] ?? null;
            $end = $window['end'] ?? $window['window_end'] ?? null;
            if ($start !== null && $end !== null) {
                $windows[] = [
                    'start' => (string) $start,
                    'end' => (string) $end,
                ];
            }
        }

        if ($windows !== []) {
            return $windows;
        }

        return [[
            'start' => (string) reset($requestedDates),
            'end' => (string) end($requestedDates),
        ]];
    }

    private function expectedMissingTickerCodesForDate(array $missingPlan, $date, array $fallbackTickerCodes)
    {
        if (! isset($missingPlan['missing_ticker_codes_by_date']) || ! is_array($missingPlan['missing_ticker_codes_by_date'])) {
            return $this->sortedUniqueList($fallbackTickerCodes);
        }

        return $this->sortedUniqueList((array) ($missingPlan['missing_ticker_codes_by_date'][$date] ?? []));
    }

    private function expectedMissingTickerCodesForDates(array $missingPlan, array $dates, array $fallbackTickerCodes)
    {
        if (! isset($missingPlan['missing_ticker_codes_by_date']) || ! is_array($missingPlan['missing_ticker_codes_by_date'])) {
            return $this->sortedUniqueList($fallbackTickerCodes);
        }

        $codes = [];
        foreach ($dates as $date) {
            foreach ((array) ($missingPlan['missing_ticker_codes_by_date'][$date] ?? []) as $code) {
                $code = strtoupper(trim((string) $code));
                if ($code !== '') {
                    $codes[$code] = true;
                }
            }
        }

        $codes = array_keys($codes);
        sort($codes);

        return $codes;
    }

    private function filterTickerCodesToExpected(array $tickerCodes, array $expectedTickerCodes)
    {
        if ($expectedTickerCodes === []) {
            return [];
        }

        $expected = array_fill_keys($expectedTickerCodes, true);
        $codes = [];
        foreach ($tickerCodes as $code) {
            $code = strtoupper(trim((string) $code));
            if ($code !== '' && isset($expected[$code])) {
                $codes[$code] = true;
            }
        }

        $codes = array_keys($codes);
        sort($codes);

        return $codes;
    }

    private function tickerCodesFromRows(array $rows)
    {
        $codes = [];
        foreach ($rows as $row) {
            $code = strtoupper(trim((string) ($row['ticker_code'] ?? '')));
            if ($code !== '') {
                $codes[$code] = true;
            }
        }

        $codes = array_keys($codes);
        sort($codes);

        return $codes;
    }

    private function rowCountsByTickerCode(array $rows)
    {
        $counts = [];
        foreach ($rows as $row) {
            $code = strtoupper(trim((string) ($row['ticker_code'] ?? '')));
            if ($code === '') {
                continue;
            }

            $counts[$code] = ($counts[$code] ?? 0) + 1;
        }

        return $counts;
    }

    private function acquireMissingTickerManualFile($inputFile, $startDate, $endDate, array $requestedDates, array $tickerCodes, array $missingPlan)
    {
        $path = $this->resolveManualMissingTickerInputFile($inputFile);
        $parsed = $this->parseMissingTickerManualCsv($path);
        $requestedSet = array_fill_keys($requestedDates, true);
        $tickerSet = array_fill_keys($tickerCodes, true);
        $rowsByDate = [];
        $rowByDateCode = [];

        foreach ($requestedDates as $date) {
            $rowsByDate[$date] = [];
        }

        foreach ($parsed['rows'] as $row) {
            $date = (string) $row['trade_date'];
            $code = strtoupper(trim((string) $row['ticker_code']));

            if (! isset($requestedSet[$date]) || ! isset($tickerSet[$code])) {
                continue;
            }

            $key = $date.'|'.$code;
            if (isset($rowByDateCode[$key])) {
                throw new \RuntimeException('MISSING_TICKER_MANUAL_FILE_DUPLICATE_ROW: duplicate ticker_code/trade_date in manual missing-ticker source file: '.$key.'.');
            }

            $rowByDateCode[$key] = true;
            $rowsByDate[$date][] = $row;
        }

        foreach ($rowsByDate as $date => $rows) {
            usort($rows, function ($left, $right) {
                return strcmp((string) ($left['ticker_code'] ?? ''), (string) ($right['ticker_code'] ?? ''));
            });
            $rowsByDate[$date] = $rows;
        }

        $fileTelemetry = $this->manualMissingTickerFileTelemetry($path, $parsed['row_count']);
        $dateTelemetry = [];
        $failedContexts = [];
        $totalExpected = 0;
        $totalSuccess = 0;

        foreach ($requestedDates as $date) {
            $expectedCodes = $missingPlan['missing_ticker_codes_by_date'][$date] ?? [];
            $expectedSet = array_fill_keys($expectedCodes, true);
            $returnedCodes = [];

            foreach ($rowsByDate[$date] ?? [] as $row) {
                $code = strtoupper(trim((string) ($row['ticker_code'] ?? '')));
                if ($code !== '' && isset($expectedSet[$code])) {
                    $returnedCodes[$code] = true;
                }
            }

            $failedCodes = [];
            foreach ($expectedCodes as $code) {
                if (! isset($returnedCodes[$code])) {
                    $failedCodes[] = $code;
                    $failedContexts[] = [
                        'ticker_code' => $code,
                        'trade_date' => $date,
                        'reason_code' => 'RUN_SOURCE_MANUAL_FILE_MISSING_ROW',
                    ];
                }
            }

            sort($failedCodes);
            $successTickerCount = count($returnedCodes);
            $failedTickerCount = count($failedCodes);
            $totalExpected += count($expectedCodes);
            $totalSuccess += $successTickerCount;

            $dateTelemetry[$date] = array_merge($fileTelemetry, [
                'source_mode' => 'manual_file',
                'source_name' => 'LOCAL_FILE',
                'source_acquisition_context' => 'missing_ticker_backfill',
                'source_acquisition_mode' => 'manual_file',
                'source_window_start' => $startDate,
                'source_window_end' => $endDate,
                'trade_date' => $date,
                'expected_ticker_count' => count($expectedCodes),
                'success_ticker_count' => $successTickerCount,
                'failed_ticker_count' => $failedTickerCount,
                'missing_ticker_codes' => $failedCodes,
                'returned_row_count' => count($rowsByDate[$date] ?? []),
                'accepted_row_count' => count($rowsByDate[$date] ?? []),
                'rejected_row_count' => 0,
                'invalid_row_count' => 0,
                'source_acquisition_state' => $successTickerCount === 0 && count($expectedCodes) > 0
                    ? 'FAILED'
                    : ($failedTickerCount === 0 ? 'SUCCESS' : 'PARTIAL_SUCCESS'),
                'source_final_status' => $successTickerCount === 0 && count($expectedCodes) > 0
                    ? 'FAILED'
                    : ($failedTickerCount === 0 ? 'SUCCESS' : 'PARTIAL'),
                'final_reason_code' => $successTickerCount === 0 && count($expectedCodes) > 0
                    ? 'RUN_SOURCE_MANUAL_FILE_NO_VALID_ROWS'
                    : ($failedTickerCount > 0 ? 'RUN_SOURCE_MANUAL_FILE_MISSING_ROW' : null),
            ]);
        }

        $failedTickerCodes = $this->sortedUniqueList(array_map(function ($context) {
            return $context['ticker_code'];
        }, $failedContexts));
        $state = $failedContexts === []
            ? 'SUCCESS'
            : ($totalSuccess > 0 ? 'PARTIAL_SUCCESS' : 'FAILED');
        $finalStatus = $failedContexts === []
            ? 'SUCCESS'
            : ($totalSuccess > 0 ? 'PARTIAL' : 'FAILED');
        $batchId = 'MANUAL_FILE_'.str_replace('-', '', $startDate).'_'.str_replace('-', '', $endDate).'_001';

        return [
            'source_acquisition_batch_id' => $batchId,
            'source_acquisition_mode' => 'manual_file',
            'source_acquisition_state' => $state,
            'source_final_status' => $finalStatus,
            'warmup_start' => $startDate,
            'requested_start' => $startDate,
            'requested_end' => $endDate,
            'windows' => [[
                'window_start' => $startDate,
                'window_end' => $endDate,
            ]],
            'window_count' => 1,
            'ticker_count' => count($tickerCodes),
            'configured_concurrency' => 1,
            'trading_dates' => $requestedDates,
            'estimated_http_requests' => 0,
            'rows_by_trade_date' => $rowsByDate,
            'date_telemetry' => $dateTelemetry,
            'window_telemetry' => [[
                'source_acquisition_batch_id' => $batchId,
                'source_acquisition_mode' => 'manual_file',
                'source_window_start' => $startDate,
                'source_window_end' => $endDate,
                'source_acquisition_state' => $state,
                'source_final_status' => $finalStatus,
                'expected_ticker_count' => $totalExpected,
                'success_ticker_count' => $totalSuccess,
                'failed_ticker_count' => count($failedTickerCodes),
                'failed_ticker_codes' => $failedTickerCodes,
                'failed_ticker_contexts' => $failedContexts,
                'returned_row_count' => array_sum(array_map('count', $rowsByDate)),
                'final_reason_code' => $failedContexts === [] ? null : 'RUN_SOURCE_MANUAL_FILE_MISSING_ROW',
                'failure_scope' => $failedContexts === [] ? null : 'ticker',
            ] + $fileTelemetry],
            'source_acquisition_checkpoints' => $this->manualMissingTickerFailureCheckpoints($startDate, $endDate, $failedContexts),
            'source_input_file' => $path,
            'source_file_hash' => $fileTelemetry['source_file_hash'] ?? null,
            'source_file_hash_algorithm' => $fileTelemetry['source_file_hash_algorithm'] ?? null,
            'source_file_size_bytes' => $fileTelemetry['source_file_size_bytes'] ?? null,
            'source_file_row_count' => $fileTelemetry['source_file_row_count'] ?? null,
        ];
    }

    private function parseMissingTickerManualCsv($path)
    {
        $handle = fopen($path, 'r');
        if (! $handle) {
            throw new \RuntimeException('MISSING_TICKER_MANUAL_FILE_UNREADABLE: unable to read manual missing-ticker source file.');
        }

        $headers = null;
        $rows = [];
        $lineNumber = 0;

        while (($line = fgetcsv($handle)) !== false) {
            $lineNumber++;
            if ($lineNumber === 1) {
                $headers = array_map([$this, 'normalizeManualCsvHeader'], $line);
                continue;
            }

            if ($this->manualCsvLineIsEmpty($line)) {
                continue;
            }

            $row = [];
            foreach ((array) $headers as $index => $header) {
                if ($header === '') {
                    continue;
                }
                $row[$header] = isset($line[$index]) ? trim((string) $line[$index]) : '';
            }

            $rows[] = $this->normalizeMissingTickerManualCsvRow($row, $lineNumber, $path);
        }

        fclose($handle);

        if ($headers === null) {
            throw new \RuntimeException('MISSING_TICKER_MANUAL_FILE_EMPTY: manual missing-ticker source file is empty.');
        }

        foreach (['ticker_code', 'trade_date', 'open', 'high', 'low', 'close', 'volume'] as $requiredHeader) {
            if (! in_array($requiredHeader, $headers, true)) {
                throw new \RuntimeException('MISSING_TICKER_MANUAL_FILE_INVALID_HEADER: manual missing-ticker CSV header must include '.$requiredHeader.'.');
            }
        }

        return [
            'row_count' => count($rows),
            'rows' => $rows,
        ];
    }

    private function normalizeMissingTickerManualCsvRow(array $row, $lineNumber, $path)
    {
        $tickerCode = strtoupper(trim((string) ($row['ticker_code'] ?? '')));
        $tradeDate = trim((string) ($row['trade_date'] ?? ''));

        if ($tickerCode === '') {
            throw new \RuntimeException('MISSING_TICKER_MANUAL_FILE_INVALID_ROW: line '.$lineNumber.' ticker_code is required.');
        }

        if (! $this->isIsoDateString($tradeDate)) {
            throw new \RuntimeException('MISSING_TICKER_MANUAL_FILE_INVALID_ROW: line '.$lineNumber.' trade_date must use YYYY-MM-DD.');
        }

        foreach (['open', 'high', 'low', 'close', 'volume'] as $field) {
            if (! array_key_exists($field, $row) || trim((string) $row[$field]) === '') {
                throw new \RuntimeException('MISSING_TICKER_MANUAL_FILE_INVALID_ROW: line '.$lineNumber.' '.$field.' is required.');
            }
            if (! is_numeric(str_replace(',', '', (string) $row[$field]))) {
                throw new \RuntimeException('MISSING_TICKER_MANUAL_FILE_INVALID_ROW: line '.$lineNumber.' '.$field.' must be numeric.');
            }
        }

        $canonicalSource = strtoupper(trim((string) ($row['canonical_source'] ?? ($row['source_name'] ?? 'SOURCE_BACKED_MANUAL'))));
        $canonicalSource = preg_replace('/[^A-Z0-9_]+/', '_', $canonicalSource);
        $canonicalSource = trim((string) $canonicalSource, '_');
        if ($canonicalSource === '') {
            $canonicalSource = 'SOURCE_BACKED_MANUAL';
        }

        $sourceRowRef = trim((string) ($row['source_row_ref'] ?? ''));
        if ($sourceRowRef === '') {
            $sourceRowRef = 'manual_file:'.basename(str_replace('\\', '/', (string) $path)).':'.$lineNumber;
        }

        return [
            'ticker_code' => $tickerCode,
            'trade_date' => $tradeDate,
            'open' => str_replace(',', '', (string) $row['open']),
            'high' => str_replace(',', '', (string) $row['high']),
            'low' => str_replace(',', '', (string) $row['low']),
            'close' => str_replace(',', '', (string) $row['close']),
            'volume' => str_replace(',', '', (string) $row['volume']),
            'adj_close' => trim((string) ($row['adj_close'] ?? '')) !== ''
                ? str_replace(',', '', (string) $row['adj_close'])
                : str_replace(',', '', (string) $row['close']),
            'source_name' => 'LOCAL_FILE',
            'canonical_source' => $canonicalSource,
            'captured_at' => trim((string) ($row['captured_at'] ?? '')) !== ''
                ? trim((string) $row['captured_at'])
                : Carbon::now(config('market_data.platform.timezone', 'Asia/Jakarta'))->toDateTimeString(),
            'source_row_ref' => $sourceRowRef,
        ];
    }

    private function manualMissingTickerFileTelemetry($path, $rowCount)
    {
        return [
            'source_name' => 'LOCAL_FILE',
            'source_input_file' => $path,
            'source_file_hash' => hash_file('sha256', $path),
            'source_file_hash_algorithm' => 'sha256',
            'source_file_size_bytes' => filesize($path),
            'source_file_row_count' => (int) $rowCount,
        ];
    }

    private function manualMissingTickerFailureCheckpoints($startDate, $endDate, array $failedContexts)
    {
        $checkpoints = [];
        foreach ($failedContexts as $context) {
            $tradeDate = (string) ($context['trade_date'] ?? '');
            $tickerCode = strtoupper(trim((string) ($context['ticker_code'] ?? '')));
            if ($tradeDate === '' || $tickerCode === '') {
                continue;
            }

            $key = $startDate.'|'.$endDate.'|'.$tradeDate.'|'.$tickerCode;
            $checkpoints[$key] = [
                'state' => 'FAILED',
                'window_start' => $startDate,
                'window_end' => $endDate,
                'trade_date' => $tradeDate,
                'ticker_code' => $tickerCode,
                'reason_code' => 'RUN_SOURCE_MANUAL_FILE_MISSING_ROW',
                'http_status' => null,
                'failure_scope' => 'ticker',
                'error_sample' => 'Manual missing-ticker CSV has no source-backed row for '.$tickerCode.' on '.$tradeDate.'.',
            ];
        }

        return $checkpoints;
    }

    private function resolveManualMissingTickerInputFile($inputFile)
    {
        $inputFile = trim((string) $inputFile);
        if ($inputFile === '') {
            throw new \RuntimeException('MISSING_TICKER_MANUAL_FILE_REQUIRED: --input_file is required when source_mode=manual_file.');
        }

        $candidates = [$inputFile];
        if (! $this->pathIsAbsolute($inputFile)) {
            $candidates[] = base_path($inputFile);
        }

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                $real = realpath($candidate);

                return $real !== false ? $real : $candidate;
            }
        }

        throw new \RuntimeException('MISSING_TICKER_MANUAL_FILE_NOT_FOUND: --input_file must point to an existing CSV file.');
    }

    private function normalizeManualCsvHeader($header)
    {
        $header = strtolower(trim((string) preg_replace('/^\xEF\xBB\xBF/', '', (string) $header)));
        $header = preg_replace('/[^a-z0-9]+/', '_', $header);

        return trim((string) $header, '_');
    }

    private function manualCsvLineIsEmpty(array $line)
    {
        foreach ($line as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function isIsoDateString($value)
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $value)) {
            return false;
        }

        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', (string) $value);

        return $date !== false && $date->format('Y-m-d') === (string) $value;
    }

    private function pathIsAbsolute($path)
    {
        $path = (string) $path;

        return preg_match('/^[A-Za-z]:[\/\\\\]/', $path) === 1
            || strpos($path, '\\\\') === 0
            || strpos($path, '/') === 0;
    }

    private function missingTickerDateTelemetry($requestedDate, $sourceMode, array $telemetry, array $missingCodes, array $providerRows)
    {
        $returnedCodes = [];
        foreach ($providerRows as $row) {
            $code = strtoupper(trim((string) ($row['ticker_code'] ?? '')));
            if ($code !== '') {
                $returnedCodes[$code] = true;
            }
        }

        $missingSet = array_fill_keys($missingCodes, true);
        $failedCodes = [];
        foreach ($missingSet as $code => $_) {
            if (! isset($returnedCodes[$code])) {
                $failedCodes[] = $code;
            }
        }
        sort($failedCodes);

        $successTickerCount = count($returnedCodes);
        $failedTickerCount = count($failedCodes);

        return array_merge($telemetry, [
            'source_mode' => $sourceMode,
            'source_acquisition_context' => 'missing_ticker_backfill',
            'trade_date' => $requestedDate,
            'expected_ticker_count' => count($missingCodes),
            'success_ticker_count' => $successTickerCount,
            'failed_ticker_count' => $failedTickerCount,
            'missing_ticker_codes' => $failedCodes,
            'returned_row_count' => count($providerRows),
            'accepted_row_count' => count($providerRows),
            'rejected_row_count' => 0,
            'invalid_row_count' => 0,
            'source_acquisition_state' => $successTickerCount === 0
                ? 'FAILED'
                : ($failedTickerCount === 0 ? 'SUCCESS' : 'PARTIAL_SUCCESS'),
            'source_final_status' => $successTickerCount === 0
                ? 'FAILED'
                : ($failedTickerCount === 0 ? 'SUCCESS' : 'PARTIAL'),
            'final_reason_code' => $successTickerCount === 0
                ? 'RUN_SOURCE_NO_VALID_DATA'
                : ($failedTickerCount > 0 ? 'RUN_SOURCE_PARTIAL_RESPONSE' : null),
        ]);
    }

    private function missingTickerNoopCase($requestedDate, $reasonCode)
    {
        return [
            'requested_date' => $requestedDate,
            'missing_ticker_count' => 0,
            'missing_ticker_codes' => [],
            'import_status' => 'SKIPPED',
            'promote_status' => 'SKIPPED',
            'evidence_status' => 'SKIPPED',
            'fixture_status' => 'SKIPPED',
            'replay_status' => 'SKIPPED',
            'readable' => true,
            'status' => 'SKIPPED_NO_MISSING_TICKERS',
            'reason_code' => $reasonCode,
        ];
    }

    private function finalizeMissingTickerSummary(array $summary)
    {
        $cases = $summary['cases'];
        $successStatuses = ['SUCCESS', 'SKIPPED_VERIFIED', 'SKIPPED_NO_MISSING_TICKERS'];

        $summary['dates_total'] = count($cases);
        $summary['dates_success'] = count(array_filter($cases, function ($case) use ($successStatuses) {
            return in_array(($case['status'] ?? null), $successStatuses, true);
        }));
        $summary['dates_skipped'] = count(array_filter($cases, function ($case) {
            return ($case['status'] ?? null) === 'SKIPPED_NO_MISSING_TICKERS';
        }));
        $summary['dates_held'] = count(array_filter($cases, function ($case) {
            return ($case['status'] ?? null) === 'HELD';
        }));
        $summary['dates_blocked'] = count(array_filter($cases, function ($case) {
            return in_array(($case['status'] ?? null), ['BLOCKED', 'SOURCE_ACQUISITION_BLOCKED'], true);
        }));
        $summary['dates_failed'] = count(array_filter($cases, function ($case) {
            return ($case['status'] ?? null) === 'FAILED';
        }));
        $summary['ticker_failures'] = array_sum(array_map(function ($case) {
            return (int) ($case['tickers_failed'] ?? 0);
        }, $cases));
        $summary['missing_ticker_source_rows'] = array_sum(array_map(function ($case) {
            return (int) ($case['missing_source_row_count'] ?? 0);
        }, $cases));
        $summary['candidate_source_row_count'] = array_sum(array_map(function ($case) {
            return (int) ($case['candidate_source_row_count'] ?? 0);
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
        $summary['publication_reprocess_correction_ids'] = $this->sortedUniqueList(array_reduce($cases, function ($carry, $case) {
            return array_merge($carry, (array) ($case['publication_reprocess_correction_ids'] ?? []));
        }, []));
        $summary['publication_reprocess_republication_mode'] = $this->resolvedRepublicationMode(
            $summary['publication_reprocess_state'],
            array_values(array_filter(array_map(function ($case) {
                return $case['publication_reprocess_republication_mode'] ?? null;
            }, $cases)))
        );
        $summary['all_passed'] = $summary['dates_failed'] === 0 && $summary['dates_held'] === 0 && $summary['dates_blocked'] === 0;
        $summary['status'] = (int) ($summary['missing_bar_count'] ?? 0) === 0
            ? 'NOOP'
            : ($summary['all_passed'] ? 'SUCCESS' : ($summary['dates_blocked'] > 0 && $summary['dates_success'] === 0 ? 'BLOCKED' : 'PARTIAL'));

        return $summary;
    }

    private function missingTickerSourceAcquisitionShouldBlock(array $summary, array $acquired)
    {
        if ((int) ($summary['failed_ticker_count'] ?? 0) > 0 || (int) ($summary['failed_window_count'] ?? 0) > 0) {
            return true;
        }

        $states = [
            $summary['source_acquisition_state'] ?? null,
            $summary['source_final_status'] ?? null,
            $acquired['source_acquisition_state'] ?? null,
            $acquired['source_final_status'] ?? null,
        ];

        foreach ((array) ($acquired['window_telemetry'] ?? []) as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $states[] = $entry['source_acquisition_state'] ?? null;
            $states[] = $entry['source_final_status'] ?? null;
        }

        foreach ($states as $state) {
            if (in_array((string) $state, ['FAILED', 'SYSTEMIC_FAILED', 'PARTIAL_SUCCESS', 'PARTIAL', 'PARTIAL_FAILED', 'FAILED_RETRY_BLOCKED', 'PARTIAL_RETRY_SUCCESS'], true)) {
                return true;
            }
        }

        return false;
    }

    private function blockedMissingTickerSourceAcquisitionSummary(array $summary, array $acquired, array $missingPlan, array $diagnostic)
    {
        $reasonCode = $diagnostic['reason_code'] ?? null;
        if ($reasonCode === null || $reasonCode === '') {
            $reasonCode = ((int) ($summary['failed_ticker_count'] ?? 0) > 0)
                ? 'RUN_SOURCE_PARTIAL_RESPONSE'
                : 'RUN_SOURCE_ACQUISITION_FAILED';
        }

        $failedTickerCodes = $this->failedTickerCodesFromAcquired($acquired);

        $summary['status'] = 'BLOCKED';
        $summary['stage'] = 'SOURCE_ACQUISITION';
        $summary['publishability_state'] = 'NOT_READABLE';
        $summary['reason_code'] = $reasonCode;
        $summary['failed_ticker_codes'] = $failedTickerCodes;
        $summary['mutation_guard'] = 'MISSING_TICKER_SOURCE_ACQUISITION_BLOCKED_BEFORE_IMPORT';
        $summary['all_passed'] = false;
        $summary['cases'] = [];

        foreach ($summary['trading_dates'] as $requestedDate) {
            $missingCodes = $missingPlan['missing_ticker_codes_by_date'][$requestedDate] ?? [];
            if ($missingCodes === []) {
                $summary['cases'][] = $this->missingTickerNoopCase($requestedDate, 'NO_MISSING_TICKERS');
                continue;
            }

            $providerRows = $this->filterSourceRowsForTickerCodes($acquired['rows_by_trade_date'][$requestedDate] ?? [], $missingCodes);
            $sourceAcquisition = $this->missingTickerDateTelemetry(
                $requestedDate,
                $summary['source_mode'] ?? 'api',
                $acquired['date_telemetry'][$requestedDate] ?? [],
                $missingCodes,
                $providerRows
            );
            $dateFailedCodes = $sourceAcquisition['missing_ticker_codes'] ?? [];
            if ($dateFailedCodes === [] && $failedTickerCodes !== []) {
                $missingSet = array_fill_keys($missingCodes, true);
                $dateFailedCodes = array_values(array_filter($failedTickerCodes, function ($tickerCode) use ($missingSet) {
                    return isset($missingSet[$tickerCode]);
                }));
            }

            $summary['cases'][] = [
                'requested_date' => $requestedDate,
                'missing_ticker_count' => count($missingCodes),
                'missing_ticker_codes' => $missingCodes,
                'failed_ticker_codes' => $this->sortedUniqueList($dateFailedCodes),
                'tickers_expected' => count($missingCodes),
                'tickers_success' => (int) ($sourceAcquisition['success_ticker_count'] ?? 0),
                'tickers_failed' => (int) ($sourceAcquisition['failed_ticker_count'] ?? count($dateFailedCodes)),
                'missing_source_row_count' => count($providerRows),
                'candidate_source_row_count' => 0,
                'source_acquisition_state' => $sourceAcquisition['source_acquisition_state'] ?? ($summary['source_acquisition_state'] ?? null),
                'source_final_status' => $sourceAcquisition['source_final_status'] ?? ($summary['source_final_status'] ?? null),
                'import_status' => 'SKIPPED_SOURCE_ACQUISITION_BLOCKED',
                'promote_status' => 'SKIPPED',
                'evidence_status' => ! empty($summary['with_evidence']) ? 'SKIPPED_SOURCE_ACQUISITION_BLOCKED' : 'SKIPPED',
                'fixture_status' => ! empty($summary['with_replay']) ? 'SKIPPED_SOURCE_ACQUISITION_BLOCKED' : 'SKIPPED',
                'replay_status' => ! empty($summary['with_replay']) ? 'SKIPPED_SOURCE_ACQUISITION_BLOCKED' : 'SKIPPED',
                'readable' => false,
                'status' => 'BLOCKED',
                'stage' => 'SOURCE_ACQUISITION',
                'reason_code' => $reasonCode,
            ];
        }

        $summary = $this->finalizeMissingTickerSummary($summary);
        $summary['status'] = 'BLOCKED';
        $summary['stage'] = 'SOURCE_ACQUISITION';
        $summary['publishability_state'] = 'NOT_READABLE';
        $summary['reason_code'] = $reasonCode;
        $summary['failed_ticker_codes'] = $failedTickerCodes;
        $summary['mutation_guard'] = 'MISSING_TICKER_SOURCE_ACQUISITION_BLOCKED_BEFORE_IMPORT';
        $summary['all_passed'] = false;

        return $summary;
    }

    private function failedTickerCodesFromAcquired(array $acquired)
    {
        $codes = [];

        foreach ($this->failureSamplesFromCheckpoints($acquired['source_acquisition_checkpoints'] ?? []) as $failure) {
            $code = strtoupper(trim((string) ($failure['ticker_code'] ?? '')));
            if ($code !== '') {
                $codes[] = $code;
            }
        }

        foreach ((array) ($acquired['window_telemetry'] ?? []) as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            foreach (['failed_ticker_codes', 'missing_ticker_codes'] as $field) {
                foreach ((array) ($entry[$field] ?? []) as $code) {
                    $code = strtoupper(trim((string) $code));
                    if ($code !== '') {
                        $codes[] = $code;
                    }
                }
            }

            foreach ((array) ($entry['failed_ticker_contexts'] ?? []) as $context) {
                if (! is_array($context)) {
                    continue;
                }
                $code = strtoupper(trim((string) ($context['ticker_code'] ?? '')));
                if ($code !== '') {
                    $codes[] = $code;
                }
            }
        }

        return $this->sortedUniqueList($codes);
    }

    private function normalizeTickerCodeFilter($value)
    {
        if ($value === null || $value === '' || $value === []) {
            return [];
        }

        if (is_string($value)) {
            $value = preg_split('/[,\s]+/', $value);
        }

        if (! is_array($value)) {
            return [];
        }

        return $this->sortedUniqueList(array_map(function ($code) {
            return strtoupper(trim((string) $code));
        }, $value));
    }

    private function resolveMissingTickerOutputDir($startDate, $endDate, $sourceMode, array $options)
    {
        if (! empty($options['output_dir'])) {
            return (string) $options['output_dir'];
        }

        return storage_path('app/market_data/evidence/backfill_missing_tickers/'.$sourceMode.'_'.$startDate.'_to_'.$endDate);
    }

    private function artifactRepository()
    {
        if ($this->artifacts instanceof EodArtifactRepository) {
            return $this->artifacts;
        }

        $this->artifacts = app(EodArtifactRepository::class);

        return $this->artifacts;
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

    private function warmupStart($firstRequestedTradingDate)
    {
        $requiredTradingDates = max(1, (int) config(
            'market_data.source.api_backfill.warmup_trading_days',
            config('market_data.source.api_backfill.warmup_days', 120)
        ));

        return $this->calendar->tradingDateWindowStart($firstRequestedTradingDate, $requiredTradingDates);
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

    private function sourceModeIsManualFile($sourceMode)
    {
        return (string) $sourceMode === 'manual_file';
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

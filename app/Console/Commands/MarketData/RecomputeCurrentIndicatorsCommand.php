<?php

namespace App\Console\Commands\MarketData;

use App\Application\MarketData\Services\FullRangeCurrentEvidenceReplayService;
use App\Application\MarketData\Services\MarketDataEvidenceExportService;
use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;
use Illuminate\Support\Facades\File;

class RecomputeCurrentIndicatorsCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:eod-indicators:recompute-current
        {start_date?}
        {end_date?}
        {--force_replace_reason=}
        {--output_dir=}
        {--with-evidence}
        {--with-replay}
        {--continue-on-error}
        {--max_dates= : Optional positive limit on how many trading dates are processed.}
        {--dry-run}';

    protected $description = 'Recompute publication-bound indicators from existing current readable bars without source acquisition, bar ingest, or source/master writes.';

    public function handle()
    {
        $startDate = $this->argument('start_date');
        $endDate = $this->argument('end_date');

        if (! $startDate || ! $endDate) {
            $this->renderCommandBlocked('COMMAND_MISSING_REQUIRED_INPUT', 'start_date and end_date are required.', [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);

            return 1;
        }

        if (! $this->validateDateString($startDate, 'start_date') || ! $this->validateDateString($endDate, 'end_date')) {
            return 1;
        }

        if (strcmp((string) $endDate, (string) $startDate) < 0) {
            $this->renderCommandBlocked('COMMAND_INVALID_DATE_RANGE', 'end_date must be greater than or equal to start_date.', [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);

            return 1;
        }

        $dryRun = (bool) $this->option('dry-run');
        $continueOnError = (bool) $this->option('continue-on-error');
        $withEvidence = (bool) $this->option('with-evidence');
        $withReplay = (bool) $this->option('with-replay');
        $reason = trim((string) ($this->option('force_replace_reason') ?: ''));
        $outputDir = $this->option('output_dir') ?: $this->defaultOutputDir($startDate, $endDate);

        if ($reason === '') {
            $this->renderCommandBlocked('COMMAND_DESTRUCTIVE_GUARD_REQUIRED', 'force_replace_reason is required for current indicator recompute.', [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);

            return 1;
        }

        $maxDates = $this->option('max_dates');
        if ($maxDates !== null && $maxDates !== '' && (int) $maxDates <= 0) {
            $this->renderCommandBlocked('COMMAND_MISSING_REQUIRED_INPUT', 'max_dates must be a positive integer when provided.', [
                'max_dates' => $maxDates,
            ]);

            return 1;
        }

        try {
            $dates = app(MarketCalendarRepository::class)->tradingDatesBetween($startDate, $endDate);
        } catch (\Throwable $e) {
            $this->renderCommandBlocked($this->reasonCodeFromException($e), $e->getMessage(), [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);

            return 1;
        }

        if ($dates === []) {
            $this->renderCommandBlocked('MARKET_CALENDAR_REQUIRES_REQUESTED_TRADING_DATE', 'No active trading dates found in market_calendar for requested range.', [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);

            return 1;
        }

        // Applied after the calendar check so an empty result always means the calendar had
        // nothing, never that the limit removed everything. max_dates is > 0 here, so slicing a
        // non-empty list cannot empty it.
        $resolvedTradingDateCount = count($dates);
        if ($maxDates !== null && $maxDates !== '') {
            $dates = array_slice($dates, 0, (int) $maxDates);
        }

        $this->ensureDirectory($outputDir);

        $summary = [
            'command' => 'market-data:eod-indicators:recompute-current',
            'operation_mode' => 'indicator_recompute_from_existing_current_bars',
            'source_acquisition_executed' => false,
            'bar_ingest_executed' => false,
            'source_master_write_executed' => false,
            'sector_import_executed' => false,
            'corporate_action_import_executed' => false,
            'trading_status_import_executed' => false,
            'eod_bars_write_executed' => false,
            'start_date' => (string) $startDate,
            'end_date' => (string) $endDate,
            // trading_date_count is what this run will actually process, because all_passed is
            // measured against it. The resolved count is kept alongside so the artifact still
            // shows that the requested range was larger than the processed slice.
            'trading_date_count' => count($dates),
            'resolved_trading_date_count' => $resolvedTradingDateCount,
            'max_dates' => ($maxDates === null || $maxDates === '') ? null : (int) $maxDates,
            'dry_run' => $dryRun,
            'with_evidence' => $withEvidence,
            'with_replay' => $withReplay,
            'continue_on_error' => $continueOnError,
            'force_replace_reason' => $reason,
            'output_dir' => $this->normalizePathForDisplay($outputDir),
            'cases' => [],
            'processed_count' => 0,
            'success_count' => 0,
            'failed_count' => 0,
            'skipped_count' => 0,
            'all_passed' => false,
        ];

        foreach ($dates as $tradeDate) {
            $case = $dryRun
                ? $this->planTradeDate($tradeDate)
                : $this->recomputeTradeDate($tradeDate, $reason, $withEvidence, $outputDir);

            $summary['cases'][] = $case;
            $summary = $this->refreshCounters($summary);
            $this->writeSummary($outputDir, $summary);
            $this->renderCase($case);

            if (($case['status'] ?? null) !== 'SUCCESS' && ! $continueOnError) {
                break;
            }
        }

        if (! $dryRun && $withReplay && $summary['success_count'] > 0) {
            $summary['full_range_current_evidence_replay'] = $this->runFullRangeReplay($startDate, $endDate, $outputDir, $continueOnError);
            $this->writeSummary($outputDir, $summary);
        }

        $summary = $this->refreshCounters($summary);
        $this->writeSummary($outputDir, $summary);

        $this->line('command='.$summary['command']);
        $this->line('operation_mode='.$summary['operation_mode']);
        $this->line('source_acquisition_executed=false');
        $this->line('bar_ingest_executed=false');
        $this->line('source_master_write_executed=false');
        $this->line('eod_bars_write_executed=false');
        $this->line('start_date='.$summary['start_date']);
        $this->line('end_date='.$summary['end_date']);
        $this->line('trading_date_count='.$summary['trading_date_count']);
        $this->line('resolved_trading_date_count='.$summary['resolved_trading_date_count']);
        $this->line('max_dates='.($summary['max_dates'] === null ? '' : $summary['max_dates']));
        $this->line('processed_count='.$summary['processed_count']);
        $this->line('success_count='.$summary['success_count']);
        $this->line('failed_count='.$summary['failed_count']);
        $this->line('skipped_count='.$summary['skipped_count']);
        $this->line('dry_run='.($dryRun ? 'true' : 'false'));
        $this->line('all_passed='.($summary['all_passed'] ? '1' : '0'));
        $this->line('output_dir='.$this->normalizePathForDisplay($outputDir));
        $this->line('summary_artifact='.$this->normalizePathForDisplay(rtrim($outputDir, '/\\').'/indicator_recompute_current_summary.json'));

        return $summary['all_passed'] ? 0 : 1;
    }

    private function planTradeDate($tradeDate)
    {
        $publication = app(EodPublicationRepository::class)->findCurrentPublicationForTradeDate($tradeDate);

        if (! $publication) {
            return [
                'trade_date' => $tradeDate,
                'status' => 'ERROR',
                'reason_code' => 'NO_READABLE_PUBLICATION',
                'error' => 'No current readable publication exists for trade_date.',
            ];
        }

        return [
            'trade_date' => $tradeDate,
            'status' => 'SUCCESS',
            'planned' => true,
            'current_publication_id' => (int) $publication->publication_id,
            'current_publication_version' => (int) $publication->publication_version,
            'current_run_id' => (int) $publication->run_id,
            'source_acquisition_executed' => false,
            'bar_ingest_executed' => false,
            'source_master_write_executed' => false,
            'eod_bars_write_executed' => false,
        ];
    }

    private function recomputeTradeDate($tradeDate, $reason, $withEvidence, $outputDir)
    {
        try {
            $publication = app(EodPublicationRepository::class)->findCurrentPublicationForTradeDate($tradeDate);
            if (! $publication) {
                throw new \RuntimeException('NO_READABLE_PUBLICATION: No current readable publication exists for trade_date.');
            }

            $seedRun = $this->runRepository()->findByRunId((int) $publication->run_id);
            if (! $seedRun) {
                throw new \RuntimeException('RUN_NOT_FOUND: Current publication seed run not found.');
            }

            $sourceMode = (string) ($seedRun->source ?: config('market_data.pipeline.default_source_mode'));
            $correction = app(EodCorrectionRepository::class)->createRequest(
                $tradeDate,
                'READABILITY_FIX',
                'Indicator recompute from existing current bars: '.$reason,
                'system',
                (int) $publication->publication_id,
                (int) $publication->run_id
            );
            $correction = app(EodCorrectionRepository::class)->approve($correction->correction_id, 'system');

            $run = $this->pipeline()->promoteDaily(
                $tradeDate,
                $sourceMode,
                (int) $publication->run_id,
                (int) $correction->correction_id,
                'correction_current',
                false,
                $reason
            );

            $case = [
                'trade_date' => $tradeDate,
                'status' => ((string) ($run->terminal_status ?? '') === 'SUCCESS' && (string) ($run->publishability_state ?? '') === 'READABLE') ? 'SUCCESS' : 'FAILED',
                'run_id' => (int) $run->run_id,
                'correction_id' => (int) $correction->correction_id,
                'prior_publication_id' => (int) $publication->publication_id,
                'publication_id' => $run->publication_id ? (int) $run->publication_id : null,
                'publication_version' => $run->publication_version ? (int) $run->publication_version : null,
                'terminal_status' => (string) ($run->terminal_status ?? ''),
                'publishability_state' => (string) ($run->publishability_state ?? ''),
                'coverage_gate_state' => (string) ($run->coverage_gate_state ?? ''),
                'final_reason_code' => (string) ($run->final_reason_code ?? ''),
                'source_mode' => $sourceMode,
                'source_acquisition_executed' => false,
                'bar_ingest_executed' => false,
                'source_master_write_executed' => false,
                'eod_bars_write_executed' => false,
            ];

            if ($withEvidence) {
                $evidence = $this->exportRecomputeEvidence($run, $correction, $publication, $tradeDate, $outputDir);
                $case['evidence_selector'] = $evidence['selector']['type'] ?? null;
                $case['evidence_status'] = $evidence['summary']['evidence_admission_state'] ?? null;
                $case['evidence_output_dir'] = $this->normalizePathForDisplay($evidence['output_dir'] ?? '');
            }

            return $case;
        } catch (\Throwable $e) {
            return [
                'trade_date' => $tradeDate,
                'status' => 'ERROR',
                'reason_code' => $this->reasonCodeFromException($e),
                'error' => $e->getMessage(),
                'source_acquisition_executed' => false,
                'bar_ingest_executed' => false,
                'source_master_write_executed' => false,
                'eod_bars_write_executed' => false,
            ];
        }
    }


    private function exportRecomputeEvidence($run, $correction, $priorPublication, $tradeDate, $outputDir)
    {
        $service = app(MarketDataEvidenceExportService::class);
        $baseDir = rtrim($outputDir, '/\\').'/dates/'.$tradeDate.'/run_'.$run->run_id;

        if ($this->recomputePreservedCurrentPublication($run, $priorPublication)) {
            return $service->exportCorrectionEvidence(
                (int) $correction->correction_id,
                $baseDir.'/correction-evidence'
            );
        }

        try {
            return $service->exportRunEvidence(
                (int) $run->run_id,
                $baseDir.'/run-evidence'
            );
        } catch (\Throwable $e) {
            if ($this->reasonCodeFromException($e) !== 'EVIDENCE_PUBLICATION_NOT_FOUND') {
                throw $e;
            }

            return $service->exportCorrectionEvidence(
                (int) $correction->correction_id,
                $baseDir.'/correction-evidence'
            );
        }
    }

    private function recomputePreservedCurrentPublication($run, $priorPublication): bool
    {
        if (! $priorPublication || empty($run->publication_id)) {
            return false;
        }

        if ((int) $run->publication_id !== (int) $priorPublication->publication_id) {
            return false;
        }

        if ((int) ($priorPublication->run_id ?? 0) === (int) $run->run_id) {
            return false;
        }

        return true;
    }

    private function runFullRangeReplay($startDate, $endDate, $outputDir, $continueOnError)
    {
        return app(FullRangeCurrentEvidenceReplayService::class)->execute(
            $startDate,
            $endDate,
            [
                'fixture_case' => 'valid_case',
                'output_dir' => rtrim($outputDir, '/\\').'/full_range_current_evidence_replay',
                'continue_on_error' => $continueOnError,
            ]
        );
    }

    private function renderCase(array $case): void
    {
        $parts = [
            'trade_date='.(string) $case['trade_date'],
            'status='.(string) ($case['status'] ?? 'UNKNOWN'),
        ];

        foreach (['run_id', 'correction_id', 'prior_publication_id', 'publication_id', 'publication_version', 'terminal_status', 'publishability_state', 'coverage_gate_state', 'final_reason_code', 'reason_code'] as $key) {
            if (array_key_exists($key, $case) && $case[$key] !== null && $case[$key] !== '') {
                $parts[] = $key.'='.(string) $case[$key];
            }
        }

        if (isset($case['error'])) {
            $parts[] = 'error='.(string) $case['error'];
        }

        $this->line(implode(' | ', $parts));
    }

    private function refreshCounters(array $summary): array
    {
        $summary['processed_count'] = count($summary['cases']);
        $summary['success_count'] = count(array_filter($summary['cases'], function ($case) {
            return ($case['status'] ?? null) === 'SUCCESS';
        }));
        $summary['failed_count'] = count(array_filter($summary['cases'], function ($case) {
            return in_array(($case['status'] ?? null), ['ERROR', 'FAILED'], true);
        }));
        $summary['skipped_count'] = count(array_filter($summary['cases'], function ($case) {
            return ($case['status'] ?? null) === 'SKIPPED';
        }));
        $summary['all_passed'] = $summary['processed_count'] === (int) $summary['trading_date_count']
            && $summary['failed_count'] === 0
            && $summary['success_count'] === (int) $summary['trading_date_count'];

        return $summary;
    }

    private function writeSummary($outputDir, array $summary): void
    {
        $this->ensureDirectory($outputDir);
        file_put_contents(
            rtrim($outputDir, '/\\').'/indicator_recompute_current_summary.json',
            json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    private function ensureDirectory($path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        if (! File::isDirectory($path)) {
            File::makeDirectory($path, 0775, true);
        }
    }

    private function defaultOutputDir($startDate, $endDate)
    {
        return storage_path('app/market_data/evidence/indicator_recompute_current/'.$startDate.'_to_'.$endDate.'_'.date('Ymd_His'));
    }

    private function reasonCodeFromException(\Throwable $e)
    {
        if (preg_match('/^([A-Z0-9_]+):/', (string) $e->getMessage(), $matches)) {
            return $matches[1];
        }

        return 'COMMAND_EXECUTION_FAILED';
    }
}

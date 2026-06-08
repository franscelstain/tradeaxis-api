<?php

namespace App\Console\Commands\MarketData;

use App\Application\MarketData\DTOs\MarketDataStageInput;
use App\Application\MarketData\Services\FullRangeCurrentEvidenceReplayService;
use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;

class RepublishCurrentIndicatorsCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:eod-indicators:republish-current
        {start_date : First requested trading date, YYYY-MM-DD}
        {end_date? : Last requested trading date, YYYY-MM-DD. Defaults to start_date.}
        {--source_mode=api : Run source identity to use for the republish run; no source acquisition is executed.}
        {--force_replace_reason= : Required audit reason for indicator-only current replacement.}
        {--output_dir= : Optional evidence/replay output directory.}
        {--with-evidence : After republish, run full-range current evidence/replay proof for the requested range.}
        {--with-replay : Alias for --with-evidence; retained for operator wording.}
        {--continue-on-error : Continue remaining dates when a date fails.}
        {--continue_on_error : Alias for --continue-on-error.}
        {--max_dates= : Optional positive limit for the number of trading dates to process.}
        {--dry-run : List the target trading dates and perform no mutation.}';


    protected $description = 'Republish current-readable market data by recomputing indicators/eligibility from existing current bars only; no source acquisition or bar ingest.';

    public function handle()
    {
        $startDate = (string) $this->argument('start_date');
        $endDate = (string) ($this->argument('end_date') ?: $startDate);
        $sourceMode = (string) ($this->option('source_mode') ?: 'api');
        $reason = trim((string) ($this->option('force_replace_reason') ?: ''));
        $continueOnError = (bool) $this->option('continue-on-error') || (bool) $this->option('continue_on_error');
        $dryRun = (bool) $this->option('dry-run');
        $maxDates = $this->option('max_dates');
        $outputDir = $this->option('output_dir') ?: null;

        if (! $this->validateDateString($startDate, 'start_date') || ! $this->validateDateString($endDate, 'end_date') || ! $this->validateSourceModeString($sourceMode)) {
            return 1;
        }

        if (strcmp($startDate, $endDate) > 0) {
            $this->renderCommandBlocked('COMMAND_INVALID_DATE_RANGE', 'start_date must be less than or equal to end_date.', [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);

            return 1;
        }

        if (! $dryRun && $reason === '') {
            $this->renderCommandBlocked('COMMAND_DESTRUCTIVE_GUARD_REQUIRED', '--force_replace_reason is required because this command can replace current readable publications.', [
                'operation_mode' => 'indicator_only_republish_current',
            ]);

            return 1;
        }

        if ($maxDates !== null && $maxDates !== '' && (int) $maxDates <= 0) {
            $this->renderCommandBlocked('COMMAND_MISSING_REQUIRED_INPUT', 'max_dates must be a positive integer when provided.', [
                'max_dates' => $maxDates,
            ]);

            return 1;
        }

        $dates = app(MarketCalendarRepository::class)->tradingDatesBetween($startDate, $endDate);
        if ($maxDates !== null && $maxDates !== '') {
            $dates = array_slice($dates, 0, (int) $maxDates);
        }

        if (empty($dates)) {
            $this->renderCommandBlocked('MARKET_CALENDAR_REQUIRES_REQUESTED_TRADING_DATE', 'No active trading dates found in market_calendar for requested range.', [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);

            return 1;
        }

        $this->info('command=market-data:eod-indicators:republish-current');
        $this->line('operation_mode=indicator_only_republish_current');
        $this->line('source_acquisition_executed=false');
        $this->line('bar_ingest_executed=false');
        $this->line('sector_import_executed=false');
        $this->line('corporate_action_import_executed=false');
        $this->line('trading_status_import_executed=false');
        $this->line('start_date='.$startDate);
        $this->line('end_date='.$endDate);
        $this->line('trading_date_count='.count($dates));
        $this->line('dry_run='.($dryRun ? 'true' : 'false'));

        if ($dryRun) {
            foreach ($dates as $date) {
                $this->line('trade_date='.$this->normalizeDateValue($date).' | status=DRY_RUN_ONLY');
            }

            return 0;
        }

        $cases = [];
        $successCount = 0;
        $failedCount = 0;

        foreach ($dates as $date) {
            $date = $this->normalizeDateValue($date);

            try {
                $run = $this->republishDate($date, $sourceMode, $reason);
                $passed = ((string) ($run->terminal_status ?? '') === 'SUCCESS')
                    && ((string) ($run->publishability_state ?? '') === 'READABLE')
                    && ((string) ($run->coverage_gate_state ?? '') === 'PASS')
                    && (int) ($run->is_current_publication ?? 0) === 1;

                $cases[] = [
                    'trade_date' => $date,
                    'status' => $passed ? 'SUCCESS' : 'NOT_READABLE',
                    'run_id' => $run->run_id ?? null,
                    'publication_id' => $run->publication_id ?? null,
                    'correction_id' => $run->correction_id ?? null,
                    'terminal_status' => $run->terminal_status ?? null,
                    'publishability_state' => $run->publishability_state ?? null,
                    'coverage_gate_state' => $run->coverage_gate_state ?? null,
                    'final_reason_code' => $run->final_reason_code ?? null,
                ];

                if ($passed) {
                    $successCount++;
                } else {
                    $failedCount++;
                }

                $this->line('trade_date='.$date.' | status='.($passed ? 'SUCCESS' : 'NOT_READABLE').' | run_id='.(string) ($run->run_id ?? '').' | publication_id='.(string) ($run->publication_id ?? '').' | correction_id='.(string) ($run->correction_id ?? '').' | final_reason_code='.(string) ($run->final_reason_code ?? ''));

                if (! $passed && ! $continueOnError) {
                    break;
                }
            } catch (\Throwable $e) {
                $failedCount++;
                $cases[] = [
                    'trade_date' => $date,
                    'status' => 'ERROR',
                    'reason_code' => $this->reasonCodeFromException($e),
                    'error_message' => $e->getMessage(),
                ];

                $this->error('trade_date='.$date.' | status=ERROR | reason_code='.$this->reasonCodeFromException($e).' | error='.$e->getMessage());

                if (! $continueOnError) {
                    break;
                }
            }
        }

        $this->line('processed_count='.count($cases));
        $this->line('success_count='.$successCount);
        $this->line('failed_count='.$failedCount);
        $this->line('all_passed='.($failedCount === 0 && $successCount === count($dates) ? '1' : '0'));

        if (($this->option('with-evidence') || $this->option('with-replay')) && $successCount > 0) {
            $proofOutputDir = $outputDir ?: 'storage/app/market_data/evidence/indicator_only_republish_current_'.$startDate.'_to_'.$endDate.'/full_range_current';
            $summary = app(FullRangeCurrentEvidenceReplayService::class)->execute($startDate, $endDate, [
                'fixture_case' => 'indicator_only_republish_current',
                'output_dir' => $proofOutputDir,
                'continue_on_error' => true,
            ]);

            $this->line('evidence_replay_processed_count='.$summary['processed_count']);
            $this->line('evidence_replay_success_count='.$summary['success_count']);
            $this->line('evidence_replay_failed_count='.$summary['failed_count']);
            $this->line('evidence_replay_all_passed='.(empty($summary['all_passed']) ? '0' : '1'));
            $this->line('evidence_replay_output_dir='.$this->normalizePathForDisplay((string) $summary['output_dir']));
        }

        return $failedCount === 0 && $successCount === count($dates) ? 0 : 1;
    }

    private function republishDate($date, $sourceMode, $reason)
    {
        $publications = app(EodPublicationRepository::class);
        $corrections = app(EodCorrectionRepository::class);
        $pipeline = $this->pipeline();

        $baseline = $publications->findCorrectionBaselinePublicationForTradeDate($date);
        if (! $baseline) {
            throw new \RuntimeException('NO_READABLE_PUBLICATION: indicator-only republish requires an existing current readable publication baseline.');
        }

        $correction = $corrections->createRequest(
            $date,
            'INDICATOR_ONLY_REPUBLISH_CURRENT',
            $reason,
            'system',
            $baseline->publication_id,
            $baseline->run_id
        );
        $correction = $corrections->approve($correction->correction_id, 'system');

        $run = null;
        foreach ([
            'PUBLISH_BARS' => 'completeCoverageEvaluation',
            'COMPUTE_INDICATORS' => 'completeIndicators',
            'BUILD_ELIGIBILITY' => 'completeEligibility',
            'HASH' => 'completeHash',
            'SEAL' => 'completeSeal',
            'FINALIZE' => 'completeFinalize',
        ] as $stage => $method) {
            $input = new MarketDataStageInput(
                $date,
                $sourceMode,
                $run ? $run->run_id : null,
                $stage,
                $correction->correction_id,
                true,
                $reason,
                'promote'
            );

            $run = $pipeline->{$method}($input);

            if ($run && in_array((string) ($run->terminal_status ?? ''), ['HELD', 'FAILED'], true)) {
                return $run;
            }
        }

        return $run;
    }

    private function normalizeDateValue($value): string
    {
        return substr((string) $value, 0, 10);
    }

    private function reasonCodeFromException(\Throwable $e): string
    {
        if (preg_match('/^([A-Z0-9_]+):/', (string) $e->getMessage(), $matches)) {
            return $matches[1];
        }

        return 'COMMAND_EXECUTION_FAILED';
    }
}

<?php

namespace App\Console\Commands\MarketData;

use App\Application\MarketData\Services\SectorIndexApiIngestService;

class IngestSectorIndexBarsApiCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:sector-indexes:ingest-api {start_date?} {end_date?} {--provider=yahoo_finance} {--symbol_suffix=} {--symbol_map_json=} {--dry-run} {--apply} {--continue_on_error} {--allow_partial}';

    protected $description = 'Fetch source-backed sector index OHLC bars from an API provider and optionally upsert them into benchmark bars.';

    public function handle()
    {
        $startDate = $this->argument('start_date');
        $endDate = $this->argument('end_date') ?: $startDate;

        if (! $startDate) {
            $this->renderCommandBlocked('COMMAND_MISSING_REQUIRED_INPUT', 'start_date is required.', [
                'start_date' => $startDate,
            ]);

            return 1;
        }

        if (! $this->validateDateString($startDate, 'start_date') || ! $this->validateDateString($endDate, 'end_date')) {
            return 1;
        }

        if ((bool) $this->option('dry-run') && (bool) $this->option('apply')) {
            $this->renderCommandBlocked('COMMAND_CONFLICTING_OPTIONS', '--dry-run and --apply cannot be used together.', [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);

            return 1;
        }

        try {
            $summary = app(SectorIndexApiIngestService::class)->execute($startDate, $endDate, [
                'provider' => $this->option('provider') ?: 'yahoo_finance',
                'symbol_suffix' => $this->option('symbol_suffix') !== null && $this->option('symbol_suffix') !== ''
                    ? $this->option('symbol_suffix')
                    : null,
                'symbol_map' => $this->option('symbol_map_json') ?: null,
                'apply' => (bool) $this->option('apply'),
                'continue_on_error' => (bool) $this->option('continue_on_error'),
                'allow_partial' => (bool) $this->option('allow_partial'),
            ]);
        } catch (\Throwable $e) {
            $this->renderCommandBlocked($this->reasonCodeFromException($e), $e->getMessage(), [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'provider' => $this->option('provider') ?: 'yahoo_finance',
            ]);

            return 1;
        }

        $status = ! empty($summary['all_passed'])
            ? ((bool) $this->option('apply') ? 'APPLIED' : 'DRY_RUN')
            : 'BLOCKED';

        $this->info('status='.$status);
        $this->line('reason_code='.($status === 'BLOCKED' ? 'SECTOR_INDEX_API_INGEST_INCOMPLETE' : ((bool) $this->option('apply') ? 'COMMAND_APPLY_CONFIRMED' : 'COMMAND_DRY_RUN_ONLY')));
        $this->line('provider='.$summary['provider']);
        $this->line('source_acquisition_mode='.$summary['source_acquisition_mode']);
        $this->line('operation_mode='.((bool) $this->option('apply') ? 'APPLY' : 'DRY_RUN'));
        $this->line('start_date='.$summary['start_date']);
        $this->line('end_date='.$summary['end_date']);
        $this->line('trading_date_count='.$summary['trading_date_count']);
        $this->line('requested_benchmark_count='.$summary['requested_benchmark_count']);
        $this->line('processed_count='.$summary['processed_count']);
        $this->line('success_count='.$summary['success_count']);
        $this->line('failed_count='.$summary['failed_count']);
        $this->line('fetched_row_count='.$summary['fetched_row_count']);
        $this->line('upserted_count='.$summary['upserted_count']);
        $this->line('allow_partial='.(empty($summary['allow_partial']) ? '0' : '1'));
        $this->line('provider_symbols='.implode(',', $summary['provider_symbols']));

        foreach ($summary['cases'] as $case) {
            $parts = [
                'trade_date='.$case['trade_date'],
                'status='.$case['status'],
                'reason_code='.($case['reason_code'] ?? ''),
                'fetched_row_count='.(int) ($case['fetched_row_count'] ?? 0),
                'upserted_count='.(int) ($case['upserted_count'] ?? 0),
            ];

            if (! empty($case['source_acquisition_state'])) {
                $parts[] = 'source_acquisition_state='.$case['source_acquisition_state'];
            }
            if (! empty($case['source_priority'])) {
                $parts[] = 'source_priority='.$case['source_priority'];
            }
            if (! empty($case['active_source_decision'])) {
                $parts[] = 'active_source_decision='.$case['active_source_decision'];
            }
            if (array_key_exists('retry_attempt_count', $case) && $case['retry_attempt_count'] !== null) {
                $parts[] = 'retry_attempt_count='.(int) $case['retry_attempt_count'];
            }
            if (isset($case['failure_class_summary']) && is_array($case['failure_class_summary']) && $case['failure_class_summary'] !== []) {
                $parts[] = 'failure_class_summary='.json_encode($case['failure_class_summary'], JSON_UNESCAPED_SLASHES);
            }
            if (array_key_exists('circuit_breaker_open', $case)) {
                $parts[] = 'circuit_breaker_open='.($case['circuit_breaker_open'] ? 'yes' : 'no');
            }
            if (! empty($case['source_protection_state'])) {
                $parts[] = 'source_protection_state='.$case['source_protection_state'];
            }
            if (array_key_exists('attempted_acquisition_unit_count', $case)) {
                $parts[] = 'attempted_acquisition_unit_count='.(int) $case['attempted_acquisition_unit_count'];
            }
            if (array_key_exists('unattempted_acquisition_unit_count', $case)) {
                $parts[] = 'unattempted_acquisition_unit_count='.(int) $case['unattempted_acquisition_unit_count'];
            }
            if (! empty($case['circuit_breaker_trigger_reason_code'])) {
                $parts[] = 'circuit_breaker_trigger_reason_code='.$case['circuit_breaker_trigger_reason_code'];
            }
            if (! empty($case['missing_benchmark_codes'])) {
                $parts[] = 'missing_benchmark_codes='.implode(',', (array) $case['missing_benchmark_codes']);
            }
            if (! empty($case['failed_benchmark_codes'])) {
                $parts[] = 'failed_benchmark_codes='.implode(',', (array) $case['failed_benchmark_codes']);
            }
            if (! empty($case['error_message'])) {
                $parts[] = 'error='.$case['error_message'];
            }

            $this->line('case='.implode(' | ', $parts));
        }

        $this->line('next_action='.($status === 'APPLIED'
            ? 'Run benchmark/equity indicator recompute and promote for affected trade dates.'
            : 'Review provider symbol mapping/source availability, then re-run with --apply after a successful dry-run.'));

        return ! empty($summary['all_passed']) ? 0 : 1;
    }

    private function reasonCodeFromException(\Throwable $e)
    {
        if (preg_match('/^([A-Z0-9_]+):/', (string) $e->getMessage(), $matches)) {
            return $matches[1];
        }

        return 'COMMAND_EXECUTION_FAILED';
    }
}

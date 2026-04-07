<?php

namespace App\Console\Commands\MarketData;

use App\Application\MarketData\Services\ReplayBackfillService;

class ReplayBackfillCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:replay:backfill {start_date} {end_date} {--fixture_case=valid_case} {--fixture_root=} {--output_dir=} {--continue_on_error}';

    protected $description = 'Execute replay verification across a trading-date range and write a deterministic replay backfill summary artifact.';

    public function handle()
    {
        $summary = app(ReplayBackfillService::class)->execute(
            $this->argument('start_date'),
            $this->argument('end_date'),
            $this->option('fixture_case') ?: 'valid_case',
            $this->option('fixture_root') ?: null,
            $this->option('output_dir') ?: null,
            (bool) $this->option('continue_on_error')
        );

        $this->info('suite='.($summary['suite'] ?? 'market_data_replay_backfill_minimum'));
        $this->line('start_date='.$summary['range']['start_date']);
        $this->line('end_date='.$summary['range']['end_date']);
        $this->line('fixture_case='.$summary['fixture_case']);
        $this->line('all_passed='.(empty($summary['all_passed']) ? '0' : '1'));
        $this->line('output_dir='.$this->normalizePathForDisplay($summary['output_dir']));

        foreach ($summary['cases'] as $case) {
            $parts = [
                'trade_date='.$case['trade_date'],
                'status='.($case['status'] ?? (empty($case['passed']) ? 'ERROR' : 'SUCCESS')),
                'expected='.($case['expected_outcome'] ?? 'n/a'),
                'observed='.($case['observed_outcome'] ?? 'n/a'),
                'passed='.(empty($case['passed']) ? '0' : '1'),
            ];

            if (isset($case['run_id'])) {
                $parts[] = 'run_id='.$case['run_id'];
            }

            if (isset($case['replay_id'])) {
                $parts[] = 'replay_id='.$case['replay_id'];
            }

            if (isset($case['error_message'])) {
                $parts[] = 'error='.$case['error_message'];
            }

            $this->line(implode(' | ', $parts));
        }

        return empty($summary['all_passed']) ? 1 : 0;
    }
}

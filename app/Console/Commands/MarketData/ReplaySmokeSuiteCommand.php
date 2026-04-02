<?php

namespace App\Console\Commands\MarketData;

use App\Application\MarketData\Services\ReplaySmokeSuiteService;

class ReplaySmokeSuiteCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:replay:smoke {run_id} {--fixture_root=} {--output_dir=}';

    protected $description = 'Execute the built-in replay smoke suite against one completed run and write a suite summary artifact.';

    public function handle()
    {
        $summary = app(ReplaySmokeSuiteService::class)->execute(
            (int) $this->argument('run_id'),
            $this->option('fixture_root') ?: null,
            $this->option('output_dir') ?: null
        );

        $this->info('suite='.($summary['suite'] ?? 'replay_smoke_minimum'));
        $this->line('run_id='.$summary['run_id']);
        $this->line('all_passed='.(empty($summary['all_passed']) ? '0' : '1'));
        $this->line('fixture_root='.$summary['fixture_root']);
        $this->line('output_dir='.$summary['output_dir']);

        foreach ($summary['cases'] as $case) {
            $parts = [
                'fixture_case='.$case['fixture_case'],
                'expected='.$case['expected_outcome'],
                'observed='.$case['observed_outcome'],
                'passed='.(empty($case['passed']) ? '0' : '1'),
            ];

            if (isset($case['trade_date'])) {
                $parts[] = 'trade_date='.$case['trade_date'];
            }

            if (isset($case['replay_id'])) {
                $parts[] = 'replay_id='.$case['replay_id'];
            }

            if (isset($case['evidence_output_dir'])) {
                $parts[] = 'evidence_output_dir='.$case['evidence_output_dir'];
            }

            if (isset($case['error'])) {
                $parts[] = 'error='.$case['error'];
            }

            $this->line(implode(' | ', $parts));
        }

        return empty($summary['all_passed']) ? 1 : 0;
    }
}

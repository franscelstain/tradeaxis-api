<?php

namespace App\Console\Commands\MarketData;

use App\Application\MarketData\Services\MarketDataBackfillService;

class BackfillMarketDataCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:backfill {start_date} {end_date} {--source_mode=} {--output_dir=} {--continue_on_error}';

    protected $description = 'Historical backfill/recompute per trading-date range.';

    public function handle()
    {
        $summary = app(MarketDataBackfillService::class)->execute(
            $this->argument('start_date'),
            $this->argument('end_date'),
            $this->sourceMode(),
            $this->option('output_dir') ?: null,
            (bool) $this->option('continue_on_error')
        );

        $this->info('suite='.$summary['suite']);
        $this->line('start_date='.$summary['range']['start_date']);
        $this->line('end_date='.$summary['range']['end_date']);
        $this->line('source_mode='.(string) $summary['source_mode']);
        $this->line('all_passed='.(int) $summary['all_passed']);
        $this->line('output_dir='.$summary['output_dir']);

        foreach ($summary['cases'] as $case) {
            $this->line(
                'requested_date='.$case['requested_date']
                .' | status='.$case['status']
                .(isset($case['run_id']) ? ' | run_id='.$case['run_id'] : '')
                .(isset($case['terminal_status']) ? ' | terminal_status='.$case['terminal_status'] : '')
                .(isset($case['publishability_state']) ? ' | publishability_state='.$case['publishability_state'] : '')
                .(isset($case['trade_date_effective']) && $case['trade_date_effective'] !== null ? ' | trade_date_effective='.$case['trade_date_effective'] : '')
                .(isset($case['error_message']) ? ' | error='.$case['error_message'] : '')
            );
        }

        return $summary['all_passed'] ? 0 : 1;
    }
}

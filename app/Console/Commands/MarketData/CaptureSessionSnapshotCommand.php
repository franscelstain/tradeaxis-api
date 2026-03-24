<?php

namespace App\Console\Commands\MarketData;

use App\Application\MarketData\Services\SessionSnapshotService;
use Illuminate\Console\Command;

class CaptureSessionSnapshotCommand extends Command
{
    protected $signature = 'market-data:session-snapshot {trade_date} {snapshot_slot} {--source_mode=manual_file} {--input_file=} {--output_dir=}';
    protected $description = 'Capture optional supplemental session snapshot aligned to readable effective trade date.';

    public function handle(SessionSnapshotService $service)
    {
        $summary = $service->capture(
            $this->argument('trade_date'),
            $this->argument('snapshot_slot'),
            $this->option('source_mode'),
            $this->option('input_file'),
            $this->option('output_dir')
        );

        $this->line('trade_date='.$summary['trade_date']);
        $this->line('snapshot_slot='.$summary['snapshot_slot']);
        $this->line('run_id='.$summary['run_id']);
        $this->line('scope_count='.$summary['scope_count']);
        $this->line('captured_count='.$summary['captured_count']);
        $this->line('skipped_count='.$summary['skipped_count']);
        $this->line('output_dir='.$summary['output_dir']);

        return 0;
    }
}

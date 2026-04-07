<?php

namespace App\Console\Commands\MarketData;

use App\Application\MarketData\Services\SessionSnapshotService;
class CaptureSessionSnapshotCommand extends AbstractMarketDataCommand
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
        if (isset($summary['trade_date_effective'])) {
            $this->line('trade_date_effective='.$summary['trade_date_effective']);
        }
        if (isset($summary['publication_id'])) {
            $this->line('publication_id='.$summary['publication_id']);
        }
        $this->line('run_id='.$summary['run_id']);
        $this->line('scope_count='.$summary['scope_count']);
        $this->line('captured_count='.$summary['captured_count']);
        $this->line('skipped_count='.$summary['skipped_count']);
        if (isset($summary['slot_anchor_time']) && $summary['slot_anchor_time'] !== null) {
            $this->line('slot_anchor_time='.$summary['slot_anchor_time']);
        }
        if (isset($summary['slot_tolerance_minutes'])) {
            $this->line('slot_tolerance_minutes='.$summary['slot_tolerance_minutes']);
        }
        if (isset($summary['slot_miss_count'])) {
            $this->line('slot_miss_count='.$summary['slot_miss_count']);
        }
        $this->line('output_dir='.$this->normalizePathForDisplay($summary['output_dir']));

        return 0;
    }
}

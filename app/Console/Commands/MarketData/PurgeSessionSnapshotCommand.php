<?php

namespace App\Console\Commands\MarketData;

use App\Application\MarketData\Services\SessionSnapshotService;
use Illuminate\Console\Command;

class PurgeSessionSnapshotCommand extends Command
{
    protected $signature = 'market-data:session-snapshot:purge {--before_date=} {--output_dir=}';
    protected $description = 'Purge session snapshot rows according to retention policy.';

    public function handle(SessionSnapshotService $service)
    {
        $summary = $service->purge(
            $this->option('before_date'),
            $this->option('output_dir')
        );

        $this->line('cutoff_timestamp='.$summary['cutoff_timestamp']);
        $this->line('deleted_rows='.$summary['deleted_rows']);
        $this->line('output_dir='.$summary['output_dir']);

        return 0;
    }
}

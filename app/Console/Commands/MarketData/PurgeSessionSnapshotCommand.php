<?php

namespace App\Console\Commands\MarketData;

use App\Application\MarketData\Services\SessionSnapshotService;
class PurgeSessionSnapshotCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:session-snapshot:purge {--before_date=} {--output_dir=} {--dry-run : Preview purge only; do not delete rows.} {--apply : Execute purge deletion after explicit operator confirmation.}';
    protected $description = 'Purge session snapshot rows according to retention policy with dry-run/apply guard.';

    public function handle(SessionSnapshotService $service)
    {
        if (! $this->validateDateString($this->option('before_date'), 'before_date')) {
            return 1;
        }

        $apply = (bool) $this->option('apply');
        $summary = $service->purge(
            $this->option('before_date'),
            $this->option('output_dir'),
            $apply
        );

        $this->line('operation_mode='.$summary['operation_mode']);
        $this->line('reason_code='.$summary['reason_code']);
        $this->line('cutoff_timestamp='.$summary['cutoff_timestamp']);
        $this->line('cutoff_source='.$summary['cutoff_source']);
        if (isset($summary['before_date']) && $summary['before_date'] !== null) {
            $this->line('before_date='.$summary['before_date']);
        }
        if (isset($summary['retention_days']) && $summary['retention_days'] !== null) {
            $this->line('retention_days='.$summary['retention_days']);
        }
        $this->line('candidate_rows='.$summary['candidate_rows']);
        $this->line('deleted_rows='.$summary['deleted_rows']);
        $this->line('output_dir='.$this->normalizePathForDisplay($summary['output_dir']));

        if (! $apply) {
            $this->line('next_action=Re-run with --apply after reviewing candidate_rows and cutoff context.');
        }

        return 0;
    }
}

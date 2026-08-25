<?php

namespace App\Console\Commands\MarketData;

use App\Application\MarketData\Services\PublicationProjectionReconciliationService;
use Illuminate\Console\Command;

class ReconcilePublicationProjectionCommand extends Command
{
    protected $signature = 'market-data:reconcile:publication-projection {--start_date=} {--end_date=} {--latest}';

    protected $description = 'Persist an independent current-projection versus current-publication-history reconciliation.';

    public function handle()
    {
        /** @var PublicationProjectionReconciliationService $service */
        $service = app(PublicationProjectionReconciliationService::class);
        $start = $this->option('start_date');
        $end = $this->option('end_date');
        $latest = (bool) $this->option('latest');

        if ($latest && ($start || $end)) {
            $this->error('status=BLOCKED');
            $this->line('reason_code=COMMAND_CONFLICTING_OPTIONS');
            $this->line('error=--latest cannot be combined with --start_date/--end_date.');
            return 1;
        }

        if (! $latest && (! $start || ! $end)) {
            $this->error('status=BLOCKED');
            $this->line('reason_code=COMMAND_MISSING_REQUIRED_INPUT');
            $this->line('error=Use --latest or provide both --start_date and --end_date.');
            return 1;
        }

        try {
            $results = $latest
                ? $service->reconcileLatest()
                : $service->reconcileRange((string) $start, (string) $end);
        } catch (\Throwable $e) {
            $this->error('status=FAILED');
            $this->line('reason_code=COMMAND_EXECUTION_FAILED');
            $this->line('error='.$e->getMessage());
            return 1;
        }

        if ($results === []) {
            $this->info('status=NO_RELEVANT_TRADE_DATE');
            $this->line('reconciliation_count=0');
            return 0;
        }

        $failed = 0;
        foreach ($results as $row) {
            if (($row['reconciliation_state'] ?? '') !== 'PASS') {
                $failed++;
            }
            $this->line(implode(' | ', [
                'trade_date='.$row['trade_date'],
                'state='.$row['reconciliation_state'],
                'pointer_state='.$row['pointer_state'],
                'publication_id='.(string) ($row['publication_id'] ?? ''),
                'mismatch_count='.$row['mismatch_count'],
                'reconciliation_hash='.$row['reconciliation_hash'],
            ]));
        }

        $this->line('reconciliation_count='.count($results));
        $this->line('failed_count='.$failed);
        $this->info('status='.($failed === 0 ? 'PASS' : 'FAIL'));

        return $failed === 0 ? 0 : 1;
    }
}

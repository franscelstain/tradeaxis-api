<?php

namespace App\Console\Commands\MarketData;

use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;

class RepairCurrentPublicationIntegrityCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:current-publication:repair {--trade_date=} {--apply} {--reason=} {--force_reason=}';

    protected $description = 'Detect and optionally clear invalid current publication pointer/current mirror states.';

    public function handle()
    {
        /** @var EodPublicationRepository $repo */
        $repo = app(EodPublicationRepository::class);
        if (! $this->validateDateString($this->option('trade_date'), 'trade_date')) {
            return 1;
        }

        $tradeDate = $this->option('trade_date') ?: null;
        $apply = (bool) $this->option('apply');
        $repairReason = trim((string) ($this->option('reason') ?: $this->option('force_reason') ?: ''));

        if ($apply && $repairReason === '') {
            $this->renderCommandBlocked('COMMAND_DESTRUCTIVE_GUARD_REQUIRED', '--apply requires --reason or --force_reason for audit trail.', [
                'trade_date' => $tradeDate,
                'apply' => 'true',
            ]);
            return 1;
        }

        $invalidRows = $repo->findInvalidCurrentPublicationStates($tradeDate);

        if ($invalidRows->isEmpty()) {
            $this->info('status=OK');
            $this->line('message=No invalid current publication pointer state detected.');
            if ($tradeDate !== null) {
                $this->line('trade_date='.$tradeDate);
            }

            return 0;
        }

        foreach ($invalidRows as $row) {
            // Derived by the repository, never re-derived here. The scan that selected this row
            // and the reasons shown to the operator must be the same judgement: a local copy
            // can only drift towards telling the operator less than the platform knows.
            $reasons = $repo->determineCurrentIntegrityViolationReasons($row);

            $this->warn('status=INVALID_CURRENT_PUBLICATION');
            $this->line('trade_date='.$row->pointer_trade_date);
            $this->line('publication_id='.$row->publication_id);
            $this->line('run_id='.(string) ($row->run_id ?? ''));
            $this->line('pointer_before_publication_id='.(string) ($row->publication_id ?? ''));
            $this->line('pointer_before_run_id='.(string) ($row->pointer_run_id ?? ''));
            $this->line('terminal_status='.(string) ($row->terminal_status ?? ''));
            $this->line('publishability_state='.(string) ($row->publishability_state ?? ''));
            $this->line('is_current='.(string) ($row->is_current ?? ''));
            $this->line('is_current_publication='.(string) ($row->is_current_publication ?? ''));
            $this->line('integrity_reasons='.implode(',', $reasons));

            $this->line('operation_mode='.($apply ? 'APPLIED' : 'DRY_RUN'));
            $this->line('reason_code='.($apply ? 'COMMAND_APPLY_CONFIRMED' : 'COMMAND_DRY_RUN_ONLY'));
            if ($apply) {
                $this->line('repair_reason='.$repairReason);
            }

            if ($apply) {
                $repo->clearCurrentPublicationState($row->pointer_trade_date);
                $this->info('repair_action=CLEARED_INVALID_CURRENT_STATE');
                $this->line('pointer_after_state=CLEARED');
                $this->line('pointer_after_publication_id=');
                $this->line('pointer_after_run_id=');
            } else {
                $this->line('next_action=Re-run with --apply --reason="<operator reason>" after reviewing integrity_reasons.');
            }
        }

        return $apply ? 0 : 1;
    }
}

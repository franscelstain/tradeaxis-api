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

        if ($apply && $tradeDate === null) {
            $this->renderCommandBlocked('COMMAND_REPAIR_SCOPE_REQUIRED', 'Apply requires one explicit --trade_date and a fresh linked recovery owner.');
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

            $this->line('operation_mode='.($apply ? 'RECOVERY_ACQUISITION_REQUIRED' : 'DRY_RUN'));
            if (! $apply) {
                $this->line('reason_code=COMMAND_DRY_RUN_ONLY');
            }
            if ($apply) {
                $this->line('repair_reason='.$repairReason);
            }

            if ($apply) {
                try {
                    $service = app(\App\Application\MarketData\Services\CurrentPublicationRepairService::class);
                    $preflight = $service->preflight((string) $tradeDate, (array) $row);
                    $this->line('operation_mode=PRE_APPLY_DISCLOSURE');
                    $this->line('historical_run_id='.(string) ($preflight['historical_run_id'] ?? ''));
                    $this->line('historical_source_mode='.(string) ($preflight['historical_source_mode'] ?? 'UNAVAILABLE'));
                    $this->line('historical_config_snapshot_id='.(string) ($preflight['historical_config_snapshot_id'] ?? ''));
                    $this->line('historical_config_hash='.(string) ($preflight['historical_config_hash'] ?? 'HISTORICAL_CONFIG_HASH_UNAVAILABLE'));
                    if ($preflight['historical_config_unavailable_reason'] !== null) {
                        $this->line('historical_config_unavailable_reason='.$preflight['historical_config_unavailable_reason']);
                    }
                    $this->line('recovery_source_mode='.$preflight['recovery_source_mode']);
                    $this->line('prospective_recovery_config_hash='.$preflight['prospective_recovery_config_hash']);
                    $this->line('recovery_run_id=PENDING_ALLOCATION');
                    $this->line('recovery_config_snapshot_id=PENDING_ALLOCATION');
                    $this->line('expected_row_effects=new recovery run and audit events; clear invalid pointer and current mirrors; preserve sealed history');
                    $this->line('pointer_impact=CLEAR_INVALID_CURRENT_STATE');
                    $result = $service->apply((string) $tradeDate, (array) $row, $repairReason, $preflight);
                    $this->line('operation_mode=APPLIED');
                    $this->line('reason_code=COMMAND_APPLY_CONFIRMED');
                    $this->line('repair_action=CLEARED_INVALID_CURRENT_STATE');
                    $this->line('recovery_run_id='.$result['recovery_run_id']);
                    $this->line('recovery_config_snapshot_id='.$result['recovery_config_snapshot_id']);
                    $this->line('pointer_after_state=CLEARED');
                    $this->line('status=HELD_NOT_READABLE pointer_cleared=1');
                } catch (\Throwable $error) {
                    $reasonCode = preg_match('/^([A-Z0-9_]+)(?::|$)/', $error->getMessage(), $match)
                        ? $match[1] : 'COMMAND_REPAIR_FAILED';
                    $this->error('status=BLOCKED reason_code='.$reasonCode.' acting_owner_required=1');
                    $this->line('error='.$error->getMessage());
                    return 2;
                }
            } else {
                $this->line('next_action=Re-run with --apply --reason="<operator reason>" after reviewing integrity_reasons; apply explicitly acquires a new linked recovery owner and refuses any active incumbent.');
            }
        }

        return $apply ? 0 : 1;
    }
}

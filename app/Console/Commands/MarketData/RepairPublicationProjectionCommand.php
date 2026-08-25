<?php

namespace App\Console\Commands\MarketData;

use App\Application\MarketData\Services\PublicationProjectionRepairService;

class RepairPublicationProjectionCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:repair:publication-projection {--trade_date=} {--dry-run} {--apply} {--reason=}';

    protected $description = 'Preflight or rebuild the current projection from immutable current-publication history for one exact trade date.';

    public function handle()
    {
        $tradeDate = trim((string) $this->option('trade_date'));
        $dryRun = (bool) $this->option('dry-run');
        $apply = (bool) $this->option('apply');
        $reason = trim((string) $this->option('reason'));

        if ($tradeDate === '') {
            $this->renderCommandBlocked('COMMAND_MISSING_REQUIRED_INPUT', '--trade_date is required.');
            return 1;
        }
        if (! $this->validateDateString($tradeDate, 'trade_date')) {
            return 1;
        }
        if ($dryRun && $apply) {
            $this->renderCommandBlocked(
                'COMMAND_CONFLICTING_OPTIONS',
                '--dry-run and --apply are mutually exclusive.',
                ['trade_date' => $tradeDate]
            );
            return 1;
        }
        if ($apply && $reason === '') {
            $this->renderCommandBlocked(
                'COMMAND_DESTRUCTIVE_GUARD_REQUIRED',
                '--apply requires --reason for the operator audit trail.',
                ['trade_date' => $tradeDate]
            );
            return 1;
        }

        /** @var PublicationProjectionRepairService $service */
        $service = app(PublicationProjectionRepairService::class);

        try {
            if (! $apply) {
                $result = $service->inspectTradeDate($tradeDate);
                $this->line('operation_mode=DRY_RUN');
                $this->line('reason_code=COMMAND_DRY_RUN_ONLY');
                $this->line('repairability_state='.$result['repairability_state']);
                $this->line('repair_trade_date='.$result['trade_date']);
                $this->line('repair_publication_id='.$result['publication_id']);
                $this->line('repair_run_id='.$result['run_id']);
                foreach ($result['snapshot_counts'] as $artifact => $count) {
                    $this->line('repair_'.$artifact.'_history_count='.$count);
                }
                $this->renderReconciliation('before', $result['reconciliation']);
                $this->line('next_action='.(
                    $result['repairability_state'] === 'REPAIRABLE_FROM_IMMUTABLE_HISTORY'
                        ? 'Use --apply --reason="<operator reason>" only for this exact date/publication after reviewing mismatch evidence.'
                        : 'No projection repair is required.'
                ));
                $this->info('status='.(
                    $result['repairability_state'] === 'REPAIRABLE_FROM_IMMUTABLE_HISTORY'
                        ? 'PASS_REPAIRABLE_DRIFT'
                        : 'PASS_NO_CHANGE'
                ));
                return 0;
            }

            $result = $service->repairTradeDate($tradeDate);
            $this->line('operation_mode=APPLIED');
            $this->line('reason_code=COMMAND_APPLY_CONFIRMED');
            $this->line('repair_reason='.$reason);
            $this->line('repair_state='.$result['repair_state']);
            $this->line('trade_date='.$result['trade_date']);
            $this->line('publication_id='.$result['publication_id']);
            $this->line('run_id='.$result['run_id']);
            $this->renderReconciliation('before', $result['before']);
            $this->renderReconciliation('after', $result['after']);
            $this->info('status=PASS');
            return 0;
        } catch (\Throwable $e) {
            $this->error('status=FAILED');
            $this->line('reason_code=COMMAND_EXECUTION_FAILED');
            $this->line('trade_date='.$tradeDate);
            $this->line('error='.$e->getMessage());
            return 1;
        }
    }

    private function renderReconciliation(string $prefix, array $result): void
    {
        $this->line($prefix.'_reconciliation_state='.(string) ($result['reconciliation_state'] ?? ''));
        $this->line($prefix.'_pointer_state='.(string) ($result['pointer_state'] ?? ''));
        $this->line($prefix.'_publication_id='.(string) ($result['publication_id'] ?? ''));
        $this->line($prefix.'_run_id='.(string) ($result['run_id'] ?? ''));
        foreach (['bars', 'indicators', 'eligibility'] as $artifact) {
            foreach (['projection_count', 'history_count', 'missing_history_count', 'missing_projection_count', 'value_mismatch_count'] as $metric) {
                $this->line($prefix.'_'.$artifact.'_'.$metric.'='.(string) ($result[$artifact.'_'.$metric] ?? ''));
            }
        }
        $this->line($prefix.'_orphan_projection_row_count='.(string) ($result['orphan_projection_row_count'] ?? ''));
        $this->line($prefix.'_mismatch_count='.(string) ($result['mismatch_count'] ?? ''));
        $this->line($prefix.'_mismatch_sample='.(string) ($result['mismatch_sample_json'] ?? ''));
        $this->line($prefix.'_reconciliation_hash='.(string) ($result['reconciliation_hash'] ?? ''));
    }
}

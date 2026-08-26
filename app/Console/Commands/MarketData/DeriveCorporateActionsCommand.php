<?php

namespace App\Console\Commands\MarketData;

use App\Application\MarketData\Services\CorporateActionDerivationService;

/** Compatibility command for the retired price-derived corporate-action workflow. */
class DeriveCorporateActionsCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:events:derive-corporate-actions {--check-recorded} {--dry-run} {--apply} {--max_gap_pct=}';
    protected $description = 'Detection-only compatibility command; price geometry never creates or verifies corporate actions.';

    public function handle()
    {
        if ((bool) $this->option('dry-run') && (bool) $this->option('apply')) {
            $this->renderCommandBlocked('COMMAND_CONFLICTING_OPTIONS', '--dry-run and --apply cannot be used together.', []);
            return 1;
        }

        $applyRequested = (bool) $this->option('apply');
        $service = app(CorporateActionDerivationService::class);
        try {
            $result = (bool) $this->option('check-recorded')
                ? $service->checkRecordedActions(false)
                : $service->derive(false);
        } catch (\Throwable $e) {
            $this->renderCommandBlocked('COMMAND_EXECUTION_FAILED', $e->getMessage(), []);
            return 1;
        }

        $this->info('status=DETECTION_ONLY');
        $this->line('reason_code=CORPORATE_ACTION_AUTHORITATIVE_EVIDENCE_REQUIRED');
        $this->line('apply_requested='.($applyRequested ? 'true' : 'false'));
        $this->line('mutation_performed=false');
        $this->line('capability_state='.($result['capability_state'] ?? 'DETECTION_ONLY'));
        $this->line('derived='.count($result['derived'] ?? []));
        $this->line('skipped='.count($result['skipped'] ?? []));
        $this->line('next_action=record or reconcile independent authoritative exchange/CSD evidence; do not synthesize event terms from price movement.');

        return 0;
    }
}

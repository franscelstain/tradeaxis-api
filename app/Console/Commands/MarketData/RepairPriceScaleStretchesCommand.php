<?php

namespace App\Console\Commands\MarketData;

use App\Application\MarketData\Services\PriceScaleStretchRepairService;

/** Compatibility command for the prohibited in-place price-scale repair workflow. */
class RepairPriceScaleStretchesCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:repair-price-scale-stretches {--ticker=} {--dry-run} {--apply}';
    protected $description = 'Detection-only compatibility command; canonical/history bars are never rewritten from anomaly geometry.';

    public function handle()
    {
        if ((bool) $this->option('dry-run') && (bool) $this->option('apply')) {
            $this->renderCommandBlocked('COMMAND_CONFLICTING_OPTIONS', '--dry-run and --apply cannot be used together.', []);
            return 1;
        }

        $applyRequested = (bool) $this->option('apply');
        try {
            $result = app(PriceScaleStretchRepairService::class)->repair($this->option('ticker') ?: null, false);
        } catch (\Throwable $e) {
            $this->renderCommandBlocked('COMMAND_EXECUTION_FAILED', $e->getMessage(), []);
            return 1;
        }

        $this->info('status=DETECTION_ONLY');
        $this->line('reason_code=IMMUTABLE_HISTORY_CORRECTION_REQUIRED');
        $this->line('apply_requested='.($applyRequested ? 'true' : 'false'));
        $this->line('mutation_performed=false');
        $this->line('stretches='.count($result['stretches'] ?? []));
        $this->line('skipped='.count($result['skipped'] ?? []));
        $this->line('next_action=use authoritative evidence -> immutable observation -> correction/republication workflow.');

        return 0;
    }
}

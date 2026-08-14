<?php

namespace App\Console\Commands\MarketData;

use App\Application\MarketData\Services\PriceScaleStretchRepairService;

/**
 * Owner contract: docs/market_data/registry/Price_Scale_Break_Detection_LOCKED.md
 */
class RepairPriceScaleStretchesCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:repair-price-scale-stretches {--ticker=} {--dry-run} {--apply}';

    protected $description = 'Restore canonical bars inside stretches that carry a different adjustment epoch.';

    public function handle()
    {
        $apply = (bool) $this->option('apply');

        if ((bool) $this->option('dry-run') && $apply) {
            $this->renderCommandBlocked('COMMAND_CONFLICTING_OPTIONS', '--dry-run and --apply cannot be used together.', []);

            return 1;
        }

        if (! $apply && ! (bool) $this->option('dry-run')) {
            $this->renderCommandBlocked(
                'COMMAND_DESTRUCTIVE_GUARD_REQUIRED',
                'This command rewrites canonical bars. Pass --dry-run to preview or --apply to execute.',
                []
            );

            return 1;
        }

        try {
            $result = app(PriceScaleStretchRepairService::class)->repair($this->option('ticker') ?: null, $apply);
        } catch (\Throwable $e) {
            $this->renderCommandBlocked('COMMAND_EXECUTION_FAILED', $e->getMessage(), []);

            return 1;
        }

        $this->info('status='.($apply ? 'APPLIED' : 'DRY_RUN'));
        $this->line('reason_code='.($apply ? 'COMMAND_APPLY_CONFIRMED' : 'COMMAND_DRY_RUN_ONLY'));
        $this->line('stretches='.count($result['stretches']));

        $totalBars = 0;
        $totalHistory = 0;

        foreach ($result['stretches'] as $stretch) {
            $totalBars += $stretch['bar_count'];
            $totalHistory += $stretch['history_row_count'];

            $this->line(sprintf(
                'stretch ticker=%s range=%s..%s factor=%s bars=%d history=%d',
                $stretch['ticker_code'],
                $stretch['start_date'],
                $stretch['end_date'],
                $stretch['factor'],
                $stretch['bar_count'],
                $stretch['history_row_count']
            ));

            foreach ($stretch['sample'] as $sample) {
                $this->line(sprintf(
                    '    %s close %s -> %s   volume %s -> %s',
                    $sample['trade_date'],
                    number_format($sample['close_before']),
                    number_format($sample['close_after']),
                    number_format($sample['volume_before']),
                    number_format($sample['volume_after'])
                ));
            }
        }

        $this->line('total_bars='.$totalBars);
        $this->line('total_history_rows='.$totalHistory);

        foreach ($result['skipped'] as $skipped) {
            $this->line('skipped ticker='.$skipped['ticker_code'].' reason='.$skipped['reason']);
        }

        $this->line('next_action='.($apply
            ? 'Re-run market-data:detect-price-scale-breaks to confirm the stretches are gone, then recompute indicators for the affected dates.'
            : 'Re-run with --apply after checking the before/after samples.'));

        return 0;
    }
}

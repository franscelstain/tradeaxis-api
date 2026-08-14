<?php

namespace App\Console\Commands\MarketData;

use App\Application\MarketData\Services\CorporateActionDerivationService;

/**
 * Owner contract: docs/market_data/registry/Price_Adjustment_Contract_LOCKED.md
 */
class DeriveCorporateActionsCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:events:derive-corporate-actions {--check-recorded} {--dry-run} {--apply} {--max_gap_pct= : With --check-recorded, only re-check GAP_AMBIGUOUS actions at or below this observed gap. Lets a release be taken in tranches, smallest gap first.}';

    protected $description = 'Derive adjustment factors from detected price-scale breaks, or check recorded actions against the price series.';

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
                'This command writes corporate action records. Pass --dry-run to preview or --apply to execute.',
                []
            );

            return 1;
        }

        $service = app(CorporateActionDerivationService::class);

        if ((bool) $this->option('check-recorded')) {
            return $this->renderRecordedCheck($service, $apply);
        }

        try {
            $result = $service->derive($apply);
        } catch (\Throwable $e) {
            $this->renderCommandBlocked('COMMAND_EXECUTION_FAILED', $e->getMessage(), []);

            return 1;
        }

        $this->info('status='.($apply ? 'APPLIED' : 'DRY_RUN'));
        $this->line('reason_code='.($apply ? 'COMMAND_APPLY_CONFIRMED' : 'COMMAND_DRY_RUN_ONLY'));
        $this->line('derived='.count($result['derived']));
        $this->line('skipped='.count($result['skipped']));

        foreach ($result['derived'] as $row) {
            $this->line(sprintf(
                'derive ticker=%s ex_date=%s move=%s%% price_factor=%s volume_factor=%s inferred=%s',
                $row['ticker_code'],
                $row['trade_date'],
                $row['implied_move_pct'],
                $row['price_adjustment_factor'],
                $row['volume_adjustment_factor'],
                $row['inferred_ratio'] !== null ? '1:'.rtrim(rtrim((string) $row['inferred_ratio'], '0'), '.') : 'none'
            ));
        }

        foreach ($result['skipped'] as $row) {
            $this->line('skip ticker='.$row['ticker_code'].' date='.$row['trade_date'].' reason='.$row['reason']);
        }

        $this->line('next_action='.($apply
            ? 'Recompute indicators so the adjusted windows replace the quarantined rows, then refine the action types via CSV import.'
            : 'Re-run with --apply after reviewing the derived factors.'));

        return 0;
    }

    private function renderRecordedCheck($service, $apply)
    {
        try {
            $result = $service->checkRecordedActions($apply, $this->option('max_gap_pct'));
        } catch (\Throwable $e) {
            $this->renderCommandBlocked('COMMAND_EXECUTION_FAILED', $e->getMessage(), []);

            return 1;
        }

        $this->info('status='.($apply ? 'APPLIED' : 'DRY_RUN'));
        $this->line('reason_code='.($apply ? 'COMMAND_APPLY_CONFIRMED' : 'COMMAND_DRY_RUN_ONLY'));
        $this->line('checked='.$result['checked']);

        foreach ($result['tally'] as $status => $count) {
            $this->line('  '.str_pad($status, 26).$count);
        }

        foreach ($result['samples'] as $sample) {
            $this->line(sprintf(
                'sample ticker=%s type=%s action_date=%s ex_date=%s gap=%s%% -> %s',
                $sample['ticker_code'],
                $sample['action_type'],
                $sample['action_date'],
                $sample['ex_date'],
                $sample['gap_pct'],
                $sample['status']
            ));
        }

        $this->line('next_action='.($apply
            ? 'Recompute indicators so verified-continuous events stop quarantining and unambiguous gaps are adjusted.'
            : 'Re-run with --check-recorded --apply after reviewing the tally.'));

        return 0;
    }
}

<?php

namespace App\Console\Commands\MarketData;

use App\Application\MarketData\Services\PriceScaleBreakDetectionService;

/**
 * Owner contract: docs/market_data/registry/Price_Scale_Break_Detection_LOCKED.md
 */
class DetectPriceScaleBreaksCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:detect-price-scale-breaks {--start=} {--end=} {--ticker=} {--dry-run} {--apply}';

    protected $description = 'Detect price-scale discontinuities in canonical bars and record them as evidence.';

    public function handle()
    {
        if ((bool) $this->option('dry-run') && (bool) $this->option('apply')) {
            $this->renderCommandBlocked('COMMAND_CONFLICTING_OPTIONS', '--dry-run and --apply cannot be used together.', []);

            return 1;
        }

        $start = $this->option('start');
        $end = $this->option('end');

        foreach (['start' => $start, 'end' => $end] as $label => $value) {
            if ($value !== null && $value !== '' && ! $this->isIsoDate($value)) {
                $this->renderCommandBlocked('COMMAND_INVALID_DATE_FORMAT', '--'.$label.' must use YYYY-MM-DD.', [
                    $label => $value,
                ]);

                return 1;
            }
        }

        if ($start && $end && $start > $end) {
            $this->renderCommandBlocked('COMMAND_INVALID_DATE_RANGE', 'start_date must not be after end_date.', [
                'start' => $start,
                'end' => $end,
            ]);

            return 1;
        }

        $apply = (bool) $this->option('apply');

        try {
            $result = app(PriceScaleBreakDetectionService::class)->detect(
                $start ?: null,
                $end ?: null,
                $this->option('ticker') ?: null,
                $apply
            );
        } catch (\Throwable $e) {
            $this->renderCommandBlocked('COMMAND_EXECUTION_FAILED', $e->getMessage(), []);

            return 1;
        }

        $detected = $result['detected'];

        $unexplained = array_values(array_filter($detected, function ($row) {
            return $row['match_status'] === 'UNEXPLAINED';
        }));
        $isolated = array_values(array_filter($detected, function ($row) {
            return $row['break_type'] === 'ISOLATED_ANOMALY';
        }));

        $this->info('status='.($apply ? 'APPLIED' : 'DRY_RUN'));
        $this->line('reason_code='.($apply ? 'COMMAND_APPLY_CONFIRMED' : 'COMMAND_DRY_RUN_ONLY'));
        $this->line('scanned_bars='.$result['scanned_bars']);
        $this->line('skipped_below_min_price='.$result['skipped_below_min_price']);
        $this->line('detected_breaks='.count($detected));
        $this->line('unexplained_breaks='.count($unexplained));
        $this->line('isolated_anomalies='.count($isolated));

        foreach (array_slice($detected, 0, 40) as $row) {
            $this->line(sprintf(
                'break ticker=%s date=%s prev_close=%s open=%s ratio=%s inferred=%s type=%s match=%s%s',
                $row['ticker_code'],
                $row['trade_date'],
                $row['previous_close'],
                $row['open_price'],
                number_format($row['implied_ratio'], 3),
                $row['inferred_ratio'] !== null ? '1:'.$row['inferred_ratio'] : 'none',
                $row['break_type'],
                $row['match_status'],
                $row['matched_action_type'] ? ' action='.$row['matched_action_type'] : ''
            ));
        }

        if (count($detected) > 40) {
            $this->line('... '.(count($detected) - 40).' more not shown');
        }

        $this->line('next_action='.($apply
            ? 'Recompute indicators for affected trade dates so unexplained breaks quarantine their dependency windows.'
            : 'Re-run with --apply after reviewing the detected breaks.'));

        return 0;
    }

    private function isIsoDate($value)
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $value)) {
            return false;
        }

        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', (string) $value);

        return $date !== false && $date->format('Y-m-d') === (string) $value;
    }
}

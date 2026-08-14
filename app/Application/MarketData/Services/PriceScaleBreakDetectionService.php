<?php

namespace App\Application\MarketData\Services;

use App\Infrastructure\Persistence\MarketData\PriceScaleBreakRepository;
use Illuminate\Support\Facades\DB;

/**
 * Detect discontinuities in the canonical price series.
 *
 * Owner contract: docs/market_data/registry/Price_Scale_Break_Detection_LOCKED.md
 *
 * This service reads bars and writes evidence. It never mutates a canonical bar.
 */
class PriceScaleBreakDetectionService
{
    /**
     * Split ratios seen on IDX. Used only to name a detected break, never to decide
     * whether one exists.
     */
    private const CANDIDATE_RATIOS = [2, 2.5, 3, 4, 5, 8, 10, 20, 25, 40, 50, 100];

    private $breaks;

    public function __construct(PriceScaleBreakRepository $breaks = null)
    {
        $this->breaks = $breaks ?: new PriceScaleBreakRepository();
    }

    /**
     * @return array{detected: array, scanned_bars: int, skipped_below_min_price: int}
     */
    public function detect($startDate = null, $endDate = null, $tickerCode = null, $apply = false): array
    {
        $config = $this->config();

        $tickerQuery = DB::table('tickers')->select(['ticker_id', 'ticker_code'])->orderBy('ticker_id');

        if ($tickerCode !== null && $tickerCode !== '') {
            $tickerQuery->where('ticker_code', strtoupper(trim($tickerCode)));
        }

        $tickers = $tickerQuery->get();

        $detected = [];
        $scanned = 0;
        $skippedBelowMinPrice = 0;

        // One ticker at a time. Loading every bar at once exhausts memory on a dataset of
        // this size, and the comparison never spans two tickers anyway.
        foreach ($tickers as $ticker) {
            $tickerId = (int) $ticker->ticker_id;

            // The comparison needs the bar immediately before $startDate, and classification
            // needs the bar immediately after $endDate, so the series is not date-bounded here.
            $series = DB::table('eod_bars')
                ->where('ticker_id', $tickerId)
                ->orderBy('trade_date')
                ->get(['trade_date', 'open', 'close'])
                ->map(function ($bar) use ($ticker) {
                    $bar->ticker_code = $ticker->ticker_code;

                    return $bar;
                })
                ->all();

            $count = count($series);

            for ($i = 1; $i < $count; $i++) {
                $previous = $series[$i - 1];
                $current = $series[$i];
                $scanned++;

                if ($startDate !== null && (string) $current->trade_date < (string) $startDate) {
                    continue;
                }

                if ($endDate !== null && (string) $current->trade_date > (string) $endDate) {
                    continue;
                }

                $previousClose = (float) $previous->close;
                $open = (float) $current->open;

                if ($previousClose <= 0 || $open <= 0) {
                    continue;
                }

                // At 1-2 IDR a single tick is a 100% move, so ratio alone cannot separate
                // ordinary trading from a split. Guard on price, not on a higher ratio.
                if ($previousClose < $config['min_price_idr'] || $open < $config['min_price_idr']) {
                    $skippedBelowMinPrice++;
                    continue;
                }

                $ratio = $previousClose / $open;
                $direction = $ratio >= 1 ? 'PRICE_DECREASED' : 'PRICE_INCREASED';
                $normalizedRatio = $ratio >= 1 ? $ratio : 1 / $ratio;

                if ($normalizedRatio < $config['min_ratio']) {
                    continue;
                }

                $inferred = $this->inferRatio($normalizedRatio, $config['ratio_tolerance']);
                $breakType = $this->classifyPersistence($series, $i, $normalizedRatio, $direction);
                $match = $this->matchCorporateAction($tickerId, (string) $current->trade_date, $config);

                $row = [
                    'ticker_id' => $tickerId,
                    'ticker_code' => strtoupper(trim((string) $current->ticker_code)),
                    'trade_date' => (string) $current->trade_date,
                    'previous_close' => round($previousClose, 4),
                    'open_price' => round($open, 4),
                    'implied_ratio' => round($normalizedRatio, 10),
                    'ratio_direction' => $direction,
                    'inferred_ratio' => $inferred['ratio'],
                    'inferred_ratio_error_pct' => $inferred['error_pct'],
                    'break_type' => $breakType,
                    'match_status' => $match['status'],
                    'matched_corporate_action_id' => $match['corporate_action_id'],
                    'matched_action_type' => $match['action_type'],
                    'detection_contract_version' => $config['contract_version'],
                    'detected_at' => date('Y-m-d H:i:s'),
                ];

                $detected[] = $row;

                if ($apply) {
                    $this->breaks->upsert($row);
                }
            }
        }

        return [
            'detected' => $detected,
            'scanned_bars' => $scanned,
            'skipped_below_min_price' => $skippedBelowMinPrice,
        ];
    }

    /**
     * A real split never reverts. If the next bar returns to the prior scale, the bar in
     * between carries a different adjustment epoch than its neighbours.
     */
    private function classifyPersistence(array $series, int $index, float $normalizedRatio, string $direction): string
    {
        if (! isset($series[$index + 1])) {
            return 'SCALE_SHIFT';
        }

        $current = (float) $series[$index]->close;
        $next = (float) $series[$index + 1]->open;

        if ($current <= 0 || $next <= 0) {
            return 'SCALE_SHIFT';
        }

        $reversionRatio = $direction === 'PRICE_DECREASED' ? $next / $current : $current / $next;

        // Reverting by a comparable magnitude means the scale went back to where it was.
        return $reversionRatio >= ($normalizedRatio * 0.5) ? 'ISOLATED_ANOMALY' : 'SCALE_SHIFT';
    }

    /**
     * Recorded action_date does not reliably equal the ex-date, so matching uses a window.
     * RMKE's split is recorded on 2026-07-17 against a price break on 2026-07-15.
     */
    private function matchCorporateAction(int $tickerId, string $tradeDate, array $config): array
    {
        $tradingDates = DB::table('market_calendar')
            ->where('is_trading_day', 1)
            ->whereBetween('cal_date', [
                date('Y-m-d', strtotime($tradeDate.' -'.($config['action_match_trading_days'] * 3).' days')),
                date('Y-m-d', strtotime($tradeDate.' +'.($config['action_match_trading_days'] * 3).' days')),
            ])
            ->orderBy('cal_date')
            ->pluck('cal_date')
            ->map(function ($value) {
                return (string) $value;
            })
            ->all();

        $position = array_search($tradeDate, $tradingDates, true);

        if ($position === false) {
            $windowStart = date('Y-m-d', strtotime($tradeDate.' -'.$config['action_match_trading_days'].' days'));
            $windowEnd = date('Y-m-d', strtotime($tradeDate.' +'.$config['action_match_trading_days'].' days'));
        } else {
            $lowIndex = max(0, $position - $config['action_match_trading_days']);
            $highIndex = min(count($tradingDates) - 1, $position + $config['action_match_trading_days']);
            $windowStart = $tradingDates[$lowIndex];
            $windowEnd = $tradingDates[$highIndex];
        }

        $action = DB::table(config('market_data.event_risk.corporate_actions_table', 'market_data_corporate_actions').' as ca')
            ->leftJoin(
                config('market_data.event_risk.corporate_action_types_table', 'market_data_corporate_action_types').' as t',
                't.action_type_code',
                '=',
                'ca.action_type'
            )
            ->where('ca.ticker_id', $tickerId)
            ->whereBetween('ca.action_date', [$windowStart, $windowEnd])
            ->where(function ($query) {
                // An unmapped type is fail-safe treated as scale-breaking, matching the
                // corporate action registry rule.
                $query->whereNull('t.action_type_code')
                    ->orWhere('t.price_continuity_impact', '<>', 'NONE')
                    ->orWhere('t.volume_continuity_impact', '<>', 'NONE');
            })
            ->orderBy('ca.action_date')
            ->select(['ca.corporate_action_id', 'ca.action_type'])
            ->first();

        if ($action === null) {
            return ['status' => 'UNEXPLAINED', 'corporate_action_id' => null, 'action_type' => null];
        }

        return [
            'status' => 'EXPLAINED',
            'corporate_action_id' => (int) $action->corporate_action_id,
            'action_type' => $action->action_type,
        ];
    }

    private function inferRatio(float $normalizedRatio, float $tolerance): array
    {
        $best = null;
        $bestError = null;

        foreach (self::CANDIDATE_RATIOS as $candidate) {
            $error = abs($normalizedRatio - $candidate) / $candidate;

            if ($bestError === null || $error < $bestError) {
                $bestError = $error;
                $best = $candidate;
            }
        }

        if ($bestError === null || $bestError > $tolerance) {
            return ['ratio' => null, 'error_pct' => null];
        }

        return ['ratio' => $best, 'error_pct' => round($bestError * 100, 6)];
    }

    private function config(): array
    {
        return [
            'contract_version' => (string) config('market_data.price_scale_break.contract_version', 'price_scale_break_v1'),
            'min_ratio' => (float) config('market_data.price_scale_break.min_ratio', 1.7),
            'min_price_idr' => (float) config('market_data.price_scale_break.min_price_idr', 50),
            'action_match_trading_days' => (int) config('market_data.price_scale_break.action_match_trading_days', 5),
            'ratio_tolerance' => (float) config('market_data.price_scale_break.ratio_tolerance', 0.08),
        ];
    }
}

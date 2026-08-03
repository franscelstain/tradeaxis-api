<?php

namespace App\Infrastructure\Persistence\MarketData;

use Illuminate\Support\Facades\DB;

/**
 * Owner contract: docs/market_data/registry/Price_Scale_Break_Detection_LOCKED.md
 */
class PriceScaleBreakRepository
{
    /**
     * Review statuses that still quarantine. Absence of review is never dismissal.
     */
    private const QUARANTINING_REVIEW_STATUSES = ['DETECTED', 'CONFIRMED'];

    /**
     * Whether the matched corporate action carries a factor that makes the window continuous.
     *
     * Mirrors EventRiskSourceRepository::isAdjustable. A factor of exactly 1.0 is not an
     * adjustment; it is a recorded action that leaves the scale alone, so it cannot excuse a
     * detected break.
     */
    private function hasUsableAdjustmentFactor($row): bool
    {
        if (! property_exists($row, 'price_adjustment_factor') || $row->price_adjustment_factor === null) {
            return false;
        }

        $factor = (float) $row->price_adjustment_factor;

        return $factor > 0 && abs($factor - 1.0) > 1e-9;
    }

    public function upsert(array $row): void
    {
        $now = $row['detected_at'] ?? date('Y-m-d H:i:s');

        DB::table($this->table())->updateOrInsert(
            [
                'ticker_id' => (int) $row['ticker_id'],
                'trade_date' => $row['trade_date'],
            ],
            [
                'ticker_code' => $row['ticker_code'],
                'previous_close' => $row['previous_close'],
                'open_price' => $row['open_price'],
                'implied_ratio' => $row['implied_ratio'],
                'ratio_direction' => $row['ratio_direction'],
                'inferred_ratio' => $row['inferred_ratio'],
                'inferred_ratio_error_pct' => $row['inferred_ratio_error_pct'],
                'break_type' => $row['break_type'],
                'match_status' => $row['match_status'],
                'matched_corporate_action_id' => $row['matched_corporate_action_id'],
                'matched_action_type' => $row['matched_action_type'],
                'detection_contract_version' => $row['detection_contract_version'],
                'detected_at' => $now,
                'updated_at' => $now,
            ]
        );
    }

    /**
     * Breaks that must contaminate indicator windows ending on the last of $tradingDates.
     *
     * Only UNEXPLAINED breaks are returned. An EXPLAINED break is already covered by the
     * corporate action quarantine, and returning it too would obscure which contract caused
     * the exclusion.
     *
     * @return array<int, array<int, array>> keyed by ticker_id
     */
    public function resolveContaminationForTickerIds(array $tickerIds, array $tradingDates): array
    {
        $tickerIds = array_values(array_unique(array_map('intval', $tickerIds)));
        $tickerIds = array_values(array_filter($tickerIds, function ($tickerId) {
            return $tickerId > 0;
        }));

        $tradingDates = array_values(array_map('strval', $tradingDates));

        if (empty($tickerIds) || empty($tradingDates)) {
            return [];
        }

        $lastIndex = count($tradingDates) - 1;
        $depthByDate = [];
        foreach ($tradingDates as $index => $date) {
            $depthByDate[$date] = $lastIndex - $index;
        }

        // Explained breaks are included.
        //
        // This used to filter on match_status = 'UNEXPLAINED', on the reasoning that a break a
        // corporate action explains would be quarantined by the corporate-action path instead.
        // That path anchors on the action's recorded action_date, and for IDX actions that is
        // frequently not the ex-date — MLPT's 25:1 split was detected here on 2026-07-15 and
        // recorded as an action dated 2026-07-21, so the quarantine covered four days that were
        // fine and left the four that were not, with roc20 reporting a 95% fall that never
        // happened and is_valid still set.
        //
        // Being explained says why the series jumped, not that it is safe to compute across. What
        // makes it safe is a repair or an applied adjustment factor, and both are excluded below
        // rather than assumed.
        $rows = DB::table($this->table().' as b')
            ->leftJoin('market_data_corporate_actions as a', 'a.corporate_action_id', '=', 'b.matched_corporate_action_id')
            ->select([
                'b.ticker_id',
                'b.trade_date',
                'b.break_type',
                'b.implied_ratio',
                'b.inferred_ratio',
                'b.match_status',
                'b.matched_action_type',
                'a.price_adjustment_factor',
            ])
            ->whereIn('b.ticker_id', $tickerIds)
            ->whereIn('b.review_status', self::QUARANTINING_REVIEW_STATUSES)
            ->whereBetween('b.trade_date', [$tradingDates[0], $tradingDates[$lastIndex]])
            ->orderBy('b.ticker_id')
            ->orderBy('b.trade_date')
            ->get();

        $contamination = [];

        foreach ($rows as $row) {
            $tradeDate = (string) $row->trade_date;

            if (! array_key_exists($tradeDate, $depthByDate)) {
                continue;
            }

            // A usable factor means the window is rescaled in memory before the indicators are
            // computed, so the series the calculation sees is continuous and quarantining it
            // would discard a window that is already correct.
            if ($this->hasUsableAdjustmentFactor($row)) {
                continue;
            }

            $contamination[(int) $row->ticker_id][] = [
                'break_type' => $row->break_type,
                'trade_date' => $tradeDate,
                'depth' => $depthByDate[$tradeDate],
                'implied_ratio' => $row->implied_ratio,
                'inferred_ratio' => $row->inferred_ratio,
                'match_status' => (string) $row->match_status,
                'matched_action_type' => $row->matched_action_type,
            ];
        }

        ksort($contamination);

        return $contamination;
    }

    public function summary(): array
    {
        $rows = DB::table($this->table())
            ->selectRaw('break_type, match_status, review_status, COUNT(*) AS total')
            ->groupBy('break_type', 'match_status', 'review_status')
            ->orderBy('break_type')
            ->orderBy('match_status')
            ->get();

        return $rows->map(function ($row) {
            return (array) $row;
        })->all();
    }

    private function table(): string
    {
        return config('market_data.event_risk.price_scale_breaks_table', 'market_data_price_scale_breaks');
    }
}

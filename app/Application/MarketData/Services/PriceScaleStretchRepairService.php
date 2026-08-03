<?php

namespace App\Application\MarketData\Services;

use Illuminate\Support\Facades\DB;

/**
 * Restore canonical bars that carry a different adjustment epoch than the surrounding series.
 *
 * Owner contract: docs/market_data/registry/Price_Scale_Break_Detection_LOCKED.md
 *
 * Only stretches bounded by a matched pair of opposite-direction breaks are repaired. A
 * single unmatched break is a genuine split: the series either side of it is already the
 * true as-traded history and must not be touched. What that case needs is a corporate
 * action record, not a data rewrite.
 */
class PriceScaleStretchRepairService
{
    private const BAR_TABLES = ['eod_bars', 'eod_bars_history'];

    public function repair($tickerCode = null, $apply = false): array
    {
        $query = DB::table('market_data_price_scale_breaks')
            ->where('match_status', 'UNEXPLAINED')
            ->whereNull('repaired_at')
            ->orderBy('ticker_code')
            ->orderBy('trade_date');

        if ($tickerCode !== null && $tickerCode !== '') {
            $query->where('ticker_code', strtoupper(trim($tickerCode)));
        }

        $byTicker = [];
        foreach ($query->get() as $break) {
            $byTicker[$break->ticker_code][] = $break;
        }

        $stretches = [];
        $skipped = [];

        foreach ($byTicker as $ticker => $breaks) {
            $pairs = $this->pairStretches($breaks);

            // No pair means no defective stretch. Every break is a standalone split, which
            // is correct data needing a corporate action record rather than a repair, so
            // this is not a skip worth reporting.
            if (empty($pairs)) {
                continue;
            }

            $factor = $this->consensusFactor($breaks);

            if ($factor === null) {
                $skipped[] = [
                    'ticker_code' => $ticker,
                    'reason' => 'stretch found but no consensus split ratio could be inferred',
                ];
                continue;
            }

            foreach ($pairs as $pair) {
                [$down, $up] = $pair;

                $stretches[] = [
                    'ticker_id' => (int) $down->ticker_id,
                    'ticker_code' => $ticker,
                    'price_scale_break_id' => (int) $down->price_scale_break_id,
                    'closing_break_id' => (int) $up->price_scale_break_id,
                    'start_date' => (string) $down->trade_date,
                    'end_date' => (string) $up->trade_date,
                    'factor' => $factor,
                ];
            }
        }

        $repaired = [];

        foreach ($stretches as $stretch) {
            $counts = $apply
                ? $this->applyStretch($stretch)
                : $this->previewStretch($stretch);

            $repaired[] = $stretch + $counts;
        }

        return [
            'stretches' => $repaired,
            'skipped' => $skipped,
        ];
    }

    /**
     * The per-break implied ratio is contaminated by the session's own price movement, so
     * it cannot be trusted as the repair factor. MLPT's stretches imply 21.8 to 30.0 while
     * the actual split was 1:25. The ticker-level consensus is the reliable value.
     */
    private function consensusFactor(array $breaks)
    {
        $votes = [];

        foreach ($breaks as $break) {
            if ($break->inferred_ratio === null) {
                continue;
            }

            $key = (string) (float) $break->inferred_ratio;
            $votes[$key] = ($votes[$key] ?? 0) + 1;
        }

        if (empty($votes)) {
            return null;
        }

        arsort($votes);
        $top = array_key_first($votes);

        return (float) $top;
    }

    /**
     * A decrease followed by an increase bounds a stretch sitting on the divided scale.
     * A trailing unpaired decrease is a genuine split and is intentionally left alone.
     *
     * @return array<int, array{0:object,1:object}>
     */
    private function pairStretches(array $breaks): array
    {
        $pairs = [];
        $pending = null;

        foreach ($breaks as $break) {
            if ($break->ratio_direction === 'PRICE_DECREASED') {
                $pending = $break;
                continue;
            }

            if ($pending !== null) {
                $pairs[] = [$pending, $break];
                $pending = null;
            }
        }

        return $pairs;
    }

    private function previewStretch(array $stretch): array
    {
        $counts = [];

        foreach (self::BAR_TABLES as $table) {
            $counts[$table] = DB::table($table)
                ->where('ticker_id', $stretch['ticker_id'])
                ->where('trade_date', '>=', $stretch['start_date'])
                ->where('trade_date', '<', $stretch['end_date'])
                ->count();
        }

        return [
            'bar_count' => $counts['eod_bars'],
            'history_row_count' => $counts['eod_bars_history'],
            'sample' => $this->sampleRows($stretch),
        ];
    }

    private function sampleRows(array $stretch): array
    {
        return DB::table('eod_bars')
            ->where('ticker_id', $stretch['ticker_id'])
            ->where('trade_date', '>=', $stretch['start_date'])
            ->where('trade_date', '<', $stretch['end_date'])
            ->orderBy('trade_date')
            ->limit(3)
            ->get(['trade_date', 'open', 'close', 'volume'])
            ->map(function ($row) use ($stretch) {
                return [
                    'trade_date' => (string) $row->trade_date,
                    'close_before' => (float) $row->close,
                    'close_after' => round((float) $row->close * $stretch['factor'], 4),
                    'volume_before' => (int) $row->volume,
                    'volume_after' => (int) round((float) $row->volume / $stretch['factor']),
                ];
            })
            ->all();
    }

    private function applyStretch(array $stretch): array
    {
        $counts = ['eod_bars' => 0, 'eod_bars_history' => 0];

        DB::transaction(function () use ($stretch, &$counts) {
            $factor = $stretch['factor'];

            foreach (self::BAR_TABLES as $table) {
                $counts[$table] = DB::table($table)
                    ->where('ticker_id', $stretch['ticker_id'])
                    ->where('trade_date', '>=', $stretch['start_date'])
                    ->where('trade_date', '<', $stretch['end_date'])
                    ->update([
                        'open' => DB::raw('open * '.$factor),
                        'high' => DB::raw('high * '.$factor),
                        'low' => DB::raw('low * '.$factor),
                        'close' => DB::raw('close * '.$factor),
                        'adj_close' => DB::raw('adj_close * '.$factor),
                        // Share count moves inversely to price under a split.
                        'volume' => DB::raw('ROUND(volume / '.$factor.')'),
                    ]);
            }

            // Both endpoints describe the same defect, so both carry the trail. The break
            // stops quarantining only because the underlying data is now correct.
            DB::table('market_data_price_scale_breaks')
                ->whereIn('price_scale_break_id', [$stretch['price_scale_break_id'], $stretch['closing_break_id']])
                ->update([
                    'review_status' => 'REPAIRED',
                    'repair_factor' => $factor,
                    'repair_range_end_date' => $stretch['end_date'],
                    'repaired_bar_count' => $counts['eod_bars'],
                    'repaired_history_row_count' => $counts['eod_bars_history'],
                    'repaired_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
        });

        return [
            'bar_count' => $counts['eod_bars'],
            'history_row_count' => $counts['eod_bars_history'],
            'sample' => [],
        ];
    }
}

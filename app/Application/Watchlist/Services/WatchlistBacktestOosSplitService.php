<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestOosSplitService
{
    public const SPLIT_RULE = 'FLOOR_70_PERCENT_IS_REMAINDER_OOS';

    public function split(array $tradingDates): array
    {
        $ordered = $this->normalizeDates($tradingDates);
        $total = count($ordered);
        $isCount = (int) floor($total * 0.70);
        $oosCount = $total - $isCount;

        if ($isCount < 1 || $oosCount < 1) {
            return [
                'ready' => false,
                'is_ready' => false,
                'reason_code' => 'WS_BT_OOS_WINDOW_INSUFFICIENT',
                'split_rule' => self::SPLIT_RULE,
                'total_trading_date_count' => $total,
                'split_index' => $isCount,
                'is_dates' => [],
                'oos_dates' => [],
                'diagnostics' => [[
                    'reason_code' => 'WS_BT_OOS_WINDOW_INSUFFICIENT',
                    'message' => 'Chronological 70/30 split requires at least one in-sample and one out-of-sample trading date.',
                    'fatal' => true,
                ]],
            ];
        }

        $isDates = array_slice($ordered, 0, $isCount);
        $oosDates = array_slice($ordered, $isCount);
        $overlap = array_values(array_intersect($isDates, $oosDates));
        $recombined = array_merge($isDates, $oosDates);
        $valid = $overlap === [] && $recombined === $ordered;

        return [
            'ready' => $valid,
            'is_ready' => $valid,
            'status' => $valid ? 'READY' : 'BLOCKED',
            'reason_code' => $valid ? null : 'WS_BT_OOS_WINDOW_INSUFFICIENT',
            'split_rule' => self::SPLIT_RULE,
            'split_ratio' => ['is' => 0.70, 'oos' => 0.30],
            'total_trading_date_count' => $total,
            'split_index' => $isCount,
            'is_trading_date_count' => count($isDates),
            'oos_trading_date_count' => count($oosDates),
            'is_from' => $isDates[0] ?? null,
            'is_to' => $isDates[count($isDates) - 1] ?? null,
            'oos_from' => $oosDates[0] ?? null,
            'oos_to' => $oosDates[count($oosDates) - 1] ?? null,
            'ordered_dates' => $ordered,
            'is_dates' => $isDates,
            'oos_dates' => $oosDates,
            'ordered_trading_date_hash' => $this->stableHash($ordered),
            'is_trading_date_hash' => $this->stableHash($isDates),
            'oos_trading_date_hash' => $this->stableHash($oosDates),
            'no_random_split' => true,
            'is_prefix' => array_slice($ordered, 0, $isCount) === $isDates,
            'oos_suffix' => array_slice($ordered, $isCount) === $oosDates,
            'no_overlap' => $overlap === [],
            'no_hidden_gap' => $recombined === $ordered,
            'diagnostics' => $valid ? [] : [[
                'reason_code' => 'WS_BT_OOS_WINDOW_INSUFFICIENT',
                'message' => 'Chronological split integrity validation failed.',
                'fatal' => true,
            ]],
        ];
    }

    private function normalizeDates(array $dates): array
    {
        $normalized = [];
        foreach ($dates as $date) {
            if (! is_scalar($date)) {
                continue;
            }
            $value = trim((string) $date);
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                $normalized[$value] = $value;
            }
        }
        ksort($normalized, SORT_STRING);

        return array_values($normalized);
    }

    private function stableHash(array $value): string
    {
        return sha1(json_encode($value, JSON_UNESCAPED_SLASHES));
    }
}

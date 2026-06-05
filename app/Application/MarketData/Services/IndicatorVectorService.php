<?php

namespace App\Application\MarketData\Services;

class IndicatorVectorService
{
    public function buildRow($tickerId, array $bars, $requestedDate, $publicationId, $runId, $createdAt, array $config)
    {
        usort($bars, function ($a, $b) {
            return strcmp($a['trade_date'], $b['trade_date']);
        });

        $index = null;
        foreach ($bars as $i => $bar) {
            if ($bar['trade_date'] === $requestedDate) {
                $index = $i;
                break;
            }
        }

        if ($index === null) {
            return null;
        }

        $invalidReason = $this->resolveInvalidReason($bars, $index, $config);
        $sectorCode = $this->normalizeSectorCode($config['sector_code'] ?? null);
        $values = [
            'dv20_idr' => null,
            'atr14_pct' => null,
            'vol_ratio' => null,
            'sector_code' => $sectorCode,
            'roc5' => null,
            'roc10' => null,
            'roc20' => null,
            'hh20' => null,
            'll20' => null,
            'ma20' => null,
            'ma50' => null,
            'close_to_hh20_pct' => null,
            'close_to_ll20_pct' => null,
            'range_20_pct' => null,
            'range_position_20_pct' => null,
            'close_vs_ma20_pct' => null,
            'close_vs_ma50_pct' => null,
            'ma20_slope_pct' => null,
            'rs_20_vs_ihsg' => null,
            'sector_roc20' => null,
            'rs_20_vs_sector' => null,
            'sector_rs_20_vs_ihsg' => null,
        ] + $this->eventRiskValues($config);

        if (! $invalidReason) {
            $values = $this->calculateIndicators($bars, $index, $config);
        }

        return [
            'trade_date' => $requestedDate,
            'ticker_id' => $tickerId,
            'is_valid' => $invalidReason ? 0 : 1,
            'invalid_reason_code' => $invalidReason,
            'indicator_set_version' => $config['set_version'],
            'sector_code' => $values['sector_code'],
            'dv20_idr' => $values['dv20_idr'],
            'atr14_pct' => $values['atr14_pct'],
            'vol_ratio' => $values['vol_ratio'],
            'roc5' => $values['roc5'],
            'roc10' => $values['roc10'],
            'roc20' => $values['roc20'],
            'hh20' => $values['hh20'],
            'll20' => $values['ll20'],
            'ma20' => $values['ma20'],
            'ma50' => $values['ma50'],
            'close_to_hh20_pct' => $values['close_to_hh20_pct'],
            'close_to_ll20_pct' => $values['close_to_ll20_pct'],
            'range_20_pct' => $values['range_20_pct'],
            'range_position_20_pct' => $values['range_position_20_pct'],
            'close_vs_ma20_pct' => $values['close_vs_ma20_pct'],
            'close_vs_ma50_pct' => $values['close_vs_ma50_pct'],
            'ma20_slope_pct' => $values['ma20_slope_pct'],
            'rs_20_vs_ihsg' => $values['rs_20_vs_ihsg'],
            'sector_roc20' => $values['sector_roc20'],
            'rs_20_vs_sector' => $values['rs_20_vs_sector'],
            'sector_rs_20_vs_ihsg' => $values['sector_rs_20_vs_ihsg'],
            'corporate_action_flag' => $values['corporate_action_flag'],
            'corporate_action_types' => $values['corporate_action_types'],
            'trading_status_code' => $values['trading_status_code'],
            'is_suspended' => $values['is_suspended'],
            'is_uma' => $values['is_uma'],
            'event_risk_flag' => $values['event_risk_flag'],
            'event_risk_reasons' => $values['event_risk_reasons'],
            'run_id' => $runId,
            'publication_id' => $publicationId,
            'created_at' => $createdAt,
        ];
    }

    public function resolveInvalidReason(array $bars, $index, array $config)
    {
        $requiredHistory = max(
            (int) $config['dv_window_days'],
            (int) $config['vol_ratio_lookback_days'] + 1,
            (int) $config['roc_lookback_days'] + 1,
            (int) $config['atr_window_days'] + 1,
            (int) $config['hh_window_days']
        );

        if (($index + 1) < $requiredHistory) {
            return 'IND_INSUFFICIENT_HISTORY';
        }

        for ($i = max(0, $index - $requiredHistory); $i <= $index; $i++) {
            if (! isset($bars[$i])) {
                return 'IND_MISSING_DEPENDENCY_BAR';
            }

            foreach (['open', 'high', 'low', 'close', 'volume'] as $field) {
                if (! isset($bars[$i][$field]) || $bars[$i][$field] === null) {
                    return 'IND_MISSING_DEPENDENCY_BAR';
                }
            }
        }

        return null;
    }

    public function calculateIndicators(array $bars, $index, array $config)
    {
        $lotSize = (int) $config['lot_size'];
        $dvWindow = (int) $config['dv_window_days'];
        $atrWindow = (int) $config['atr_window_days'];
        $volLookback = (int) $config['vol_ratio_lookback_days'];
        $rocLookback = (int) $config['roc_lookback_days'];
        $hhWindow = (int) $config['hh_window_days'];

        $currentBar = $bars[$index];
        $dvBars = array_slice($bars, $index - $dvWindow + 1, $dvWindow);
        $turnovers = array_map(function ($bar) use ($lotSize) {
            return ((float) $bar['close']) * ((float) $bar['volume']) * $lotSize;
        }, $dvBars);

        $trValues = [];
        for ($i = 1; $i <= $index; $i++) {
            $bar = $bars[$i];
            $prev = $bars[$i - 1];
            $trValues[$i] = max(
                (float) $bar['high'] - (float) $bar['low'],
                abs((float) $bar['high'] - (float) $prev['close']),
                abs((float) $bar['low'] - (float) $prev['close'])
            );
        }

        $atr = null;
        if (isset($trValues[$atrWindow])) {
            $seedSlice = [];
            for ($i = 1; $i <= $atrWindow; $i++) {
                $seedSlice[] = $trValues[$i];
            }
            $atr = array_sum($seedSlice) / $atrWindow;
            for ($i = $atrWindow + 1; $i <= $index; $i++) {
                $atr = (($atr * ($atrWindow - 1)) + $trValues[$i]) / $atrWindow;
            }
        }

        $priceBasisCurrent = $this->priceBasis($currentBar, $config);
        $priorVolBars = array_slice($bars, $index - $volLookback, $volLookback);
        $priorVolAverage = array_sum(array_map(function ($bar) {
            return (float) $bar['volume'];
        }, $priorVolBars)) / $volLookback;

        $rocBaseBar = $bars[$index - $rocLookback];
        $hhBars = array_slice($bars, $index - $hhWindow + 1, $hhWindow);
        $hh20 = round(max(array_map(function ($bar) {
            return (float) $bar['high'];
        }, $hhBars)), 4);
        $ll20 = round(min(array_map(function ($bar) {
            return (float) $bar['low'];
        }, $hhBars)), 4);
        $ma20 = $this->movingAverage($bars, $index, 20, $config);
        $ma50 = $this->movingAverage($bars, $index, 50, $config);
        $ma20Past = $index >= 5 ? $this->movingAverage($bars, $index - 5, 20, $config) : null;
        $roc20 = $this->priceBasis($rocBaseBar, $config) > 0 ? round(($priceBasisCurrent / $this->priceBasis($rocBaseBar, $config)) - 1, 10) : null;
        $benchmarkRoc20Pct = array_key_exists('benchmark_roc20_pct', $config) && $config['benchmark_roc20_pct'] !== null
            ? (float) $config['benchmark_roc20_pct']
            : null;
        $sectorRoc20Pct = array_key_exists('sector_roc20_pct', $config) && $config['sector_roc20_pct'] !== null
            ? (float) $config['sector_roc20_pct']
            : null;
        $equityRoc20Pct = $roc20 !== null ? $roc20 * 100 : null;

        return [
            'dv20_idr' => round(array_sum($turnovers) / $dvWindow, 2),
            'atr14_pct' => $atr !== null && $priceBasisCurrent > 0 ? round($atr / $priceBasisCurrent, 10) : null,
            'vol_ratio' => $priorVolAverage > 0 ? round(((float) $currentBar['volume']) / $priorVolAverage, 10) : null,
            'sector_code' => $this->normalizeSectorCode($config['sector_code'] ?? null),
            'roc5' => $this->roc($bars, $index, 5, $config),
            'roc10' => $this->roc($bars, $index, 10, $config),
            'roc20' => $roc20,
            'hh20' => $hh20,
            'll20' => $ll20,
            'ma20' => $ma20,
            'ma50' => $ma50,
            'close_to_hh20_pct' => $this->pctDifference($priceBasisCurrent, $hh20),
            'close_to_ll20_pct' => $this->pctDifference($priceBasisCurrent, $ll20),
            'range_20_pct' => $this->pctDifference($hh20, $ll20),
            'range_position_20_pct' => $this->rangePositionPct($priceBasisCurrent, $ll20, $hh20),
            'close_vs_ma20_pct' => $this->pctDifference($priceBasisCurrent, $ma20),
            'close_vs_ma50_pct' => $this->pctDifference($priceBasisCurrent, $ma50),
            'ma20_slope_pct' => $ma20 !== null && $ma20Past !== null ? $this->pctDifference($ma20, $ma20Past) : null,
            'rs_20_vs_ihsg' => $equityRoc20Pct !== null && $benchmarkRoc20Pct !== null ? round($equityRoc20Pct - $benchmarkRoc20Pct, 10) : null,
            'sector_roc20' => $sectorRoc20Pct,
            'rs_20_vs_sector' => $equityRoc20Pct !== null && $sectorRoc20Pct !== null ? round($equityRoc20Pct - $sectorRoc20Pct, 10) : null,
            'sector_rs_20_vs_ihsg' => $sectorRoc20Pct !== null && $benchmarkRoc20Pct !== null ? round($sectorRoc20Pct - $benchmarkRoc20Pct, 10) : null,
        ] + $this->eventRiskValues($config);
    }

    private function movingAverage(array $bars, $index, $window, array $config)
    {
        if (($index + 1) < $window) {
            return null;
        }

        $slice = array_slice($bars, $index - $window + 1, $window);
        $values = [];

        foreach ($slice as $bar) {
            $price = $this->priceBasis($bar, $config);
            if ($price <= 0) {
                return null;
            }
            $values[] = $price;
        }

        return round(array_sum($values) / $window, 4);
    }

    private function roc(array $bars, $index, $lookback, array $config)
    {
        if ($index < $lookback || ! isset($bars[$index - $lookback])) {
            return null;
        }

        $current = $this->priceBasis($bars[$index], $config);
        $base = $this->priceBasis($bars[$index - $lookback], $config);

        if ($base <= 0) {
            return null;
        }

        return round(($current / $base) - 1, 10);
    }

    private function pctDifference($current, $base)
    {
        if ($current === null || $base === null || (float) $base <= 0) {
            return null;
        }

        return round((((float) $current - (float) $base) / (float) $base) * 100, 10);
    }

    private function rangePositionPct($current, $low, $high)
    {
        if ($current === null || $low === null || $high === null) {
            return null;
        }

        $range = (float) $high - (float) $low;
        if ($range <= 0) {
            return null;
        }

        return round((((float) $current - (float) $low) / $range) * 100, 10);
    }

    private function priceBasis(array $bar, array $config)
    {
        $basis = strtolower((string) $config['price_basis_default']);

        if ($basis === 'adj_close' && isset($bar['adj_close']) && $bar['adj_close'] !== null) {
            return (float) $bar['adj_close'];
        }

        return (float) $bar['close'];
    }

    private function normalizeSectorCode($sectorCode)
    {
        $sectorCode = strtoupper(trim((string) $sectorCode));

        return $sectorCode === '' ? null : $sectorCode;
    }

    private function eventRiskValues(array $config)
    {
        $context = $config['event_risk_context'] ?? [];
        if (! is_array($context)) {
            $context = [];
        }

        return [
            'corporate_action_flag' => $this->nullableFlag($context['corporate_action_flag'] ?? null),
            'corporate_action_types' => $this->nullableContextString($context['corporate_action_types'] ?? null),
            'trading_status_code' => $this->nullableContextString($context['trading_status_code'] ?? null),
            'is_suspended' => $this->nullableFlag($context['is_suspended'] ?? null),
            'is_uma' => $this->nullableFlag($context['is_uma'] ?? null),
            'event_risk_flag' => $this->nullableFlag($context['event_risk_flag'] ?? null),
            'event_risk_reasons' => $this->nullableContextString($context['event_risk_reasons'] ?? null),
        ];
    }

    private function nullableFlag($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value === 1 ? 1 : 0;
    }

    private function nullableContextString($value)
    {
        $value = strtoupper(trim((string) $value));

        return $value === '' ? null : substr($value, 0, 255);
    }
}

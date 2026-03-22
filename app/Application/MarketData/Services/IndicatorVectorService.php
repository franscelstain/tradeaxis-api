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
        $values = [
            'dv20_idr' => null,
            'atr14_pct' => null,
            'vol_ratio' => null,
            'roc20' => null,
            'hh20' => null,
        ];

        if (! $invalidReason) {
            $values = $this->calculateIndicators($bars, $index, $config);
        }

        return [
            'trade_date' => $requestedDate,
            'ticker_id' => $tickerId,
            'is_valid' => $invalidReason ? 0 : 1,
            'invalid_reason_code' => $invalidReason,
            'indicator_set_version' => $config['set_version'],
            'dv20_idr' => $values['dv20_idr'],
            'atr14_pct' => $values['atr14_pct'],
            'vol_ratio' => $values['vol_ratio'],
            'roc20' => $values['roc20'],
            'hh20' => $values['hh20'],
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

        return [
            'dv20_idr' => round(array_sum($turnovers) / $dvWindow, 2),
            'atr14_pct' => $atr !== null && $priceBasisCurrent > 0 ? round($atr / $priceBasisCurrent, 10) : null,
            'vol_ratio' => $priorVolAverage > 0 ? round(((float) $currentBar['volume']) / $priorVolAverage, 10) : null,
            'roc20' => $this->priceBasis($rocBaseBar, $config) > 0 ? round(($priceBasisCurrent / $this->priceBasis($rocBaseBar, $config)) - 1, 10) : null,
            'hh20' => round(max(array_map(function ($bar) {
                return (float) $bar['high'];
            }, $hhBars)), 4),
        ];
    }

    private function priceBasis(array $bar, array $config)
    {
        $basis = strtolower((string) $config['price_basis_default']);

        if ($basis === 'adj_close' && isset($bar['adj_close']) && $bar['adj_close'] !== null) {
            return (float) $bar['adj_close'];
        }

        return (float) $bar['close'];
    }
}

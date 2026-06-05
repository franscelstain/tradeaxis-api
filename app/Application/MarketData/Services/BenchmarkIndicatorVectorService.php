<?php

namespace App\Application\MarketData\Services;

class BenchmarkIndicatorVectorService
{
    public function buildRow($benchmarkCode, array $bars, $requestedDate, $createdAt, array $config)
    {
        usort($bars, function ($a, $b) {
            return strcmp($a['trade_date'], $b['trade_date']);
        });

        $index = null;
        foreach ($bars as $i => $bar) {
            if ((string) $bar['trade_date'] === (string) $requestedDate) {
                $index = $i;
                break;
            }
        }

        if ($index === null) {
            return null;
        }

        $roc20 = $this->roc($bars, $index, 20);
        $ma20 = $this->movingAverage($bars, $index, 20);
        $ma50 = $this->movingAverage($bars, $index, 50);
        $ma20Past = $index >= 5 ? $this->movingAverage($bars, $index - 5, 20) : null;
        $close = $this->close($bars[$index]);
        $invalidReason = null;
        if ($this->hasInvalidCloseInput($bars, $index, 50)) {
            $invalidReason = 'IND_INVALID_BAR_INPUT';
        } elseif ($roc20 === null || $ma20 === null || $ma50 === null) {
            $invalidReason = 'IND_INSUFFICIENT_HISTORY';
        }

        return [
            'benchmark_code' => $benchmarkCode,
            'trade_date' => $requestedDate,
            'roc_20' => $roc20,
            'ma20' => $ma20,
            'ma50' => $ma50,
            'ma20_slope_pct' => $ma20 !== null && $ma20Past !== null ? $this->pctDifference($ma20, $ma20Past) : null,
            'close_to_ma20_pct' => $this->pctDifference($close, $ma20),
            'close_to_ma50_pct' => $this->pctDifference($close, $ma50),
            'is_valid' => $invalidReason ? 0 : 1,
            'invalid_reason_code' => $invalidReason,
            'indicator_set_version' => $config['set_version'],
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ];
    }

    private function roc(array $bars, $index, $lookback)
    {
        if ($index < $lookback || ! isset($bars[$index - $lookback])) {
            return null;
        }

        $current = $this->close($bars[$index]);
        $base = $this->close($bars[$index - $lookback]);

        if ($current === null || $base === null || $base <= 0) {
            return null;
        }

        return round((($current - $base) / $base) * 100, 10);
    }

    private function movingAverage(array $bars, $index, $window)
    {
        if (($index + 1) < $window) {
            return null;
        }

        $slice = array_slice($bars, $index - $window + 1, $window);
        $values = [];
        foreach ($slice as $bar) {
            $close = $this->close($bar);
            if ($close === null || $close <= 0) {
                return null;
            }
            $values[] = $close;
        }

        return round(array_sum($values) / $window, 4);
    }

    private function pctDifference($current, $base)
    {
        if ($current === null || $base === null || (float) $base <= 0) {
            return null;
        }

        return round((((float) $current - (float) $base) / (float) $base) * 100, 10);
    }

    private function close(array $bar)
    {
        if (isset($bar['adjusted_close']) && $bar['adjusted_close'] !== null) {
            return (float) $bar['adjusted_close'];
        }

        if (isset($bar['close_price']) && $bar['close_price'] !== null) {
            return (float) $bar['close_price'];
        }

        return null;
    }

    private function hasInvalidCloseInput(array $bars, $index, $window)
    {
        if (($index + 1) < $window) {
            return false;
        }

        $slice = array_slice($bars, $index - $window + 1, $window);
        foreach ($slice as $bar) {
            $close = $this->close($bar);
            if ($close === null || $close <= 0) {
                return true;
            }
        }

        return false;
    }
}

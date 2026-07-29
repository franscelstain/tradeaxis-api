<?php

namespace App\Application\Watchlist\Services;

class WeeklySwingDecisionTimeTickRiskService
{
    public const CONTRACT = 'SIGNAL_CLOSE_ATR_STOP_NORMALIZED_WITH_IDX_EQUITY_PRICE_BANDS';

    public function calculate(?float $signalClosePrice, ?float $atr14Pct, ?float $stopAtrMult): array
    {
        if ($signalClosePrice === null || $signalClosePrice <= 0
            || $atr14Pct === null || $atr14Pct <= 0 || $atr14Pct > 1
            || $stopAtrMult === null || $stopAtrMult <= 0) {
            return $this->invalid();
        }

        $theoreticalRisk = $atr14Pct * $stopAtrMult;
        if ($theoreticalRisk <= 0 || $theoreticalRisk >= 1) {
            return $this->invalid();
        }

        $theoreticalStop = $signalClosePrice * (1 - $theoreticalRisk);
        $normalizedStop = $this->normalizeStopTriggerPrice($theoreticalStop);
        if ($normalizedStop === null || $normalizedStop >= $signalClosePrice) {
            return $this->invalid();
        }

        $normalizedRisk = ($signalClosePrice - $normalizedStop) / $signalClosePrice;
        $expansion = max(0.0, $normalizedRisk - $theoreticalRisk);

        return [
            'valid' => true,
            'contract' => self::CONTRACT,
            'signal_close_price' => $signalClosePrice,
            'theoretical_stop_risk_pct' => $theoreticalRisk,
            'theoretical_stop_price' => $theoreticalStop,
            'normalized_stop_trigger_price' => $normalizedStop,
            'normalized_stop_risk_pct' => $normalizedRisk,
            'signal_tick_risk_expansion_pct' => $expansion,
        ];
    }

    private function invalid(): array
    {
        return [
            'valid' => false,
            'contract' => self::CONTRACT,
            'signal_close_price' => null,
            'theoretical_stop_risk_pct' => null,
            'theoretical_stop_price' => null,
            'normalized_stop_trigger_price' => null,
            'normalized_stop_risk_pct' => null,
            'signal_tick_risk_expansion_pct' => null,
        ];
    }

    private function normalizeStopTriggerPrice(float $price): ?float
    {
        if ($price <= 0) {
            return null;
        }
        $tick = $this->priceTick($price);
        $normalized = floor(($price + 0.000000001) / $tick) * $tick;

        return $normalized > 0 ? (float) $normalized : null;
    }

    private function priceTick(float $price): float
    {
        if ($price < 200) {
            return 1.0;
        }
        if ($price < 500) {
            return 2.0;
        }
        if ($price < 2000) {
            return 5.0;
        }
        if ($price < 5000) {
            return 10.0;
        }

        return 25.0;
    }
}

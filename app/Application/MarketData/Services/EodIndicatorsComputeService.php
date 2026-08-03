<?php

namespace App\Application\MarketData\Services;

use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\EventRiskSourceRepository;
use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;
use App\Infrastructure\Persistence\MarketData\PriceScaleBreakRepository;
use App\Infrastructure\Persistence\MarketData\SectorClassificationRepository;
use Carbon\Carbon;

class EodIndicatorsComputeService
{
    private $artifacts;
    private $publications;
    private $vectors;
    private $benchmarkIndicators;
    private $sectors;
    private $eventRisks;
    private $calendar;
    private $priceScaleBreaks;

    public function __construct(EodArtifactRepository $artifacts, EodPublicationRepository $publications, IndicatorVectorService $vectors, BenchmarkIndicatorComputeService $benchmarkIndicators = null, SectorClassificationRepository $sectors = null, EventRiskSourceRepository $eventRisks = null, MarketCalendarRepository $calendar = null, PriceScaleBreakRepository $priceScaleBreaks = null)
    {
        $this->artifacts = $artifacts;
        $this->publications = $publications;
        $this->vectors = $vectors;
        $this->benchmarkIndicators = $benchmarkIndicators;
        $this->sectors = $sectors;
        $this->eventRisks = $eventRisks;
        $this->calendar = $calendar ?: new MarketCalendarRepository();
        $this->priceScaleBreaks = $priceScaleBreaks ?: new PriceScaleBreakRepository();
    }

    public function compute($run, $requestedDate, $correctionMode = false)
    {
        $candidatePublication = $this->publications->getOrCreateCandidatePublication($run);
        $useHistory = $correctionMode
            || (int) ($candidatePublication->publication_version ?? 1) > 1
            || ! empty($candidatePublication->supersedes_publication_id)
            || ! empty($candidatePublication->previous_publication_id)
            || ! empty($candidatePublication->replaced_publication_id);

        if ($useHistory) {
            $this->artifacts->ensureBarsHistoryFromCurrentTradeDate(
                $requestedDate,
                $candidatePublication->publication_id,
                $run->run_id
            );
        }

        $benchmarkResult = [
            'benchmark_indicators_rows_written' => 0,
            'invalid_benchmark_indicator_count' => 0,
        ];
        $benchmarkRoc20 = null;

        if ($this->benchmarkIndicators !== null) {
            $benchmarkResult = $this->benchmarkIndicators->compute($requestedDate);
            $benchmarkRoc20 = $this->benchmarkIndicators->roc20('IHSG', $requestedDate);
        }

        $windowDays = max(
            (int) config('market_data.indicators.dv_window_days'),
            (int) config('market_data.indicators.vol_ratio_lookback_days') + 1,
            (int) config('market_data.indicators.roc_lookback_days') + 1,
            (int) config('market_data.indicators.hh_window_days'),
            (int) config('market_data.indicators.atr_window_days') + 1,
            55
        );

        $barLoadWindow = $windowDays + 5;

        $barsByTicker = $this->artifacts->loadBarsWindow($requestedDate, $barLoadWindow, $useHistory ? $candidatePublication->publication_id : null);
        $sectorContextsByTicker = $this->sectors !== null
            ? $this->sectors->resolveSectorContextForTickerIds(array_keys($barsByTicker), $requestedDate)
            : [];
        $eventRiskContextsByTicker = $this->eventRisks !== null
            ? $this->eventRisks->resolveEventRiskContextForTickerIds(array_keys($barsByTicker), $requestedDate)
            : [];
        $tradingDatesWindow = $this->resolveContaminationTradingDates($requestedDate, $barLoadWindow);
        $contaminationByTicker = $this->resolveCorporateActionContamination(array_keys($barsByTicker), $tradingDatesWindow);
        $priceScaleBreaksByTicker = $this->resolvePriceScaleBreakContamination(array_keys($barsByTicker), $tradingDatesWindow);
        $adjustmentFactorsByTicker = $this->resolveAdjustmentFactors(array_keys($barsByTicker), $tradingDatesWindow);
        $sectorBenchmarkRoc20s = [];

        if ($this->benchmarkIndicators !== null && ! empty($sectorContextsByTicker)) {
            $sectorIndexCodes = array_values(array_unique(array_filter(array_map(function ($context) {
                return $context['sector_index_code'] ?? null;
            }, $sectorContextsByTicker))));

            $sectorBenchmarkRoc20s = $this->benchmarkIndicators->roc20s($sectorIndexCodes, $requestedDate);
        }

        $rows = [];
        $invalidCount = 0;
        $now = Carbon::now(config('market_data.platform.timezone'))->toDateTimeString();

        foreach ($barsByTicker as $tickerId => $bars) {
            $sectorContext = $sectorContextsByTicker[(int) $tickerId] ?? null;
            $sectorIndexCode = $sectorContext['sector_index_code'] ?? null;
            $sectorRoc20 = $sectorIndexCode && array_key_exists($sectorIndexCode, $sectorBenchmarkRoc20s)
                ? $sectorBenchmarkRoc20s[$sectorIndexCode]
                : null;
            $eventRiskContext = $eventRiskContextsByTicker[(int) $tickerId] ?? null;
            $contamination = $contaminationByTicker[(int) $tickerId] ?? [];
            $priceScaleBreaks = $priceScaleBreaksByTicker[(int) $tickerId] ?? [];
            $adjustmentFactors = $adjustmentFactorsByTicker[(int) $tickerId] ?? [];

            $row = $this->vectors->buildRow(
                (int) $tickerId,
                $bars,
                $requestedDate,
                $candidatePublication->publication_id,
                $run->run_id,
                $now,
                $this->vectorConfig($benchmarkRoc20, $sectorContext, $sectorRoc20, $eventRiskContext, $contamination, $barLoadWindow, $priceScaleBreaks, $adjustmentFactors)
            );
            if (! $row) {
                continue;
            }

            if ((int) $row['is_valid'] === 0) {
                $invalidCount++;
            }

            $rows[] = $row;
        }

        $this->artifacts->replaceIndicators($requestedDate, $run->run_id, $rows, $candidatePublication->publication_id, $useHistory);

        return [
            'publication_id' => (int) $candidatePublication->publication_id,
            'publication_version' => (int) $candidatePublication->publication_version,
            'indicators_rows_written' => count($rows),
            'invalid_indicator_count' => $invalidCount,
            'storage_target' => $useHistory ? 'eod_indicators_history' : 'eod_indicators',
        ] + $benchmarkResult;
    }

    /**
     * Corporate actions that may still poison an indicator window ending on the requested date.
     *
     * The lookback matches the loaded bar window rather than the longest declared indicator
     * horizon, because ATR seeds cumulatively across every loaded bar. Quarantining ATR on the
     * shorter declared horizon would report it clean while the recursion still carries the
     * pre-action scale. See the ATR note in Indicator_Registry_Baseline_LOCKED.md.
     */
    private function resolveCorporateActionContamination(array $tickerIds, array $tradingDates)
    {
        if ($this->eventRisks === null || empty($tickerIds) || empty($tradingDates)) {
            return [];
        }

        return $this->eventRisks->resolveCorporateActionContaminationForTickerIds($tickerIds, $tradingDates);
    }

    /**
     * Detected price-scale breaks that no recorded corporate action explains.
     *
     * The corporate action feed misses splits outright, so the price series is the only
     * reliable signal for them. Without this the quarantine would silently skip every
     * unrecorded split.
     */
    private function resolvePriceScaleBreakContamination(array $tickerIds, array $tradingDates)
    {
        if ($this->priceScaleBreaks === null || empty($tickerIds) || empty($tradingDates)) {
            return [];
        }

        return $this->priceScaleBreaks->resolveContaminationForTickerIds($tickerIds, $tradingDates);
    }

    /**
     * Canonical trading-day sequence backing every contamination depth calculation.
     *
     * Not guarded: loadBarsWindow already resolved the same window start for the same
     * requested date, so a calendar failure here cannot be new. Swallowing it would only
     * create a silent path that publishes contaminated indicators as clean.
     */
    private function resolveContaminationTradingDates($requestedDate, $lookbackTradingDays)
    {
        $windowStart = $this->calendar->tradingDateWindowStart($requestedDate, $lookbackTradingDays);

        return $this->calendar->tradingDatesBetween($windowStart, $requestedDate);
    }

    /**
     * Adjustment factors effective inside the loaded window.
     *
     * Owner contract: docs/market_data/registry/Price_Adjustment_Contract_LOCKED.md
     */
    private function resolveAdjustmentFactors(array $tickerIds, array $tradingDates)
    {
        if ($this->eventRisks === null || empty($tickerIds) || empty($tradingDates)) {
            return [];
        }

        return $this->eventRisks->resolveAdjustmentFactorsForTickerIds(
            $tickerIds,
            $tradingDates[0],
            $tradingDates[count($tradingDates) - 1]
        );
    }

    private function vectorConfig($benchmarkRoc20 = null, array $sectorContext = null, $sectorRoc20 = null, array $eventRiskContext = null, array $contamination = [], $atrContaminationHorizonDays = 0, array $priceScaleBreaks = [], array $adjustmentFactors = [])
    {
        return [
            'set_version' => config('market_data.indicators.set_version'),
            'sector_code' => $sectorContext['sector_code'] ?? null,
            'sector_index_code' => $sectorContext['sector_index_code'] ?? null,
            'event_risk_context' => $eventRiskContext ?: [],
            'corporate_action_contamination' => $contamination,
            'price_scale_break_contamination' => $priceScaleBreaks,
            'price_adjustment_factors' => $adjustmentFactors,
            'atr_contamination_horizon_days' => (int) $atrContaminationHorizonDays,
            'price_basis_default' => config('market_data.platform.price_basis_default'),
            'dv_window_days' => (int) config('market_data.indicators.dv_window_days'),
            'atr_window_days' => (int) config('market_data.indicators.atr_window_days'),
            'vol_ratio_lookback_days' => (int) config('market_data.indicators.vol_ratio_lookback_days'),
            'roc_lookback_days' => (int) config('market_data.indicators.roc_lookback_days'),
            'hh_window_days' => (int) config('market_data.indicators.hh_window_days'),
            'benchmark_roc20_pct' => $benchmarkRoc20,
            'sector_roc20_pct' => $sectorRoc20,
        ];
    }
}

<?php

namespace App\Application\MarketData\Services;

use App\Domain\MarketData\MarketDataScope;
use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\EventRiskSourceRepository;
use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;
use App\Infrastructure\Persistence\MarketData\PriceScaleBreakRepository;
use App\Infrastructure\Persistence\MarketData\SectorClassificationRepository;
use App\Infrastructure\Persistence\MarketData\CorpusAdmissionRepository;
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
    private $analyticalIdentity;
    private $factorSets;
    private $corpusAdmissions;

    public function __construct(EodArtifactRepository $artifacts, EodPublicationRepository $publications, IndicatorVectorService $vectors, BenchmarkIndicatorComputeService $benchmarkIndicators = null, SectorClassificationRepository $sectors = null, EventRiskSourceRepository $eventRisks = null, MarketCalendarRepository $calendar = null, PriceScaleBreakRepository $priceScaleBreaks = null, AnalyticalProductIdentityService $analyticalIdentity = null, AdjustmentFactorSetService $factorSets = null, CorpusAdmissionRepository $corpusAdmissions = null)
    {
        $this->artifacts = $artifacts;
        $this->publications = $publications;
        $this->vectors = $vectors;
        $this->benchmarkIndicators = $benchmarkIndicators;
        $this->sectors = $sectors;
        $this->eventRisks = $eventRisks;
        $this->calendar = $calendar ?: new MarketCalendarRepository();
        $this->priceScaleBreaks = $priceScaleBreaks ?: new PriceScaleBreakRepository();
        $this->analyticalIdentity = $analyticalIdentity ?: new AnalyticalProductIdentityService();
        $this->factorSets = $factorSets ?: app(AdjustmentFactorSetService::class);
        $this->corpusAdmissions = $corpusAdmissions ?: new CorpusAdmissionRepository();
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

        $knownAt = $run->started_at ?? $run->created_at ?? null;
        $historyStartDate = $this->corpusAdmissions->historyStartDateFor($requestedDate, $knownAt)
            ?: MarketDataScope::DATASET_START;

        $benchmarkResult = [
            'benchmark_indicators_rows_written' => 0,
            'invalid_benchmark_indicator_count' => 0,
        ];
        $benchmarkRoc20 = null;

        if ($this->benchmarkIndicators !== null) {
            $benchmarkResult = $this->benchmarkIndicators->compute($requestedDate, $historyStartDate);
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

        $barsByTicker = $this->artifacts->loadBarsWindow(
            $requestedDate,
            $barLoadWindow,
            $useHistory ? $candidatePublication->publication_id : null,
            $historyStartDate
        );

        /*
         * Wilder ATR is recursive, so it needs its own series anchored at the dataset boundary
         * rather than at the start of the 60-day load window. Measured on production, seeding at
         * the window start diverged from the boundary-seeded value by 1.62% at the 90th percentile
         * and 72.9% at worst — enough to misstate the volatility input that position sizing and
         * stop placement are built on.
         */
        $sectorContextsByTicker = $this->sectors !== null
            ? $this->sectors->resolveSectorContextForTickerIds(array_keys($barsByTicker), $requestedDate, null, $knownAt)
            : [];
        // The same $knownAt the sector root receives. Passing it to one temporal root and not the
        // others is not a partial fix, it is an inconsistent as-known state: the run would resolve
        // sectors as of its start while resolving events and factors as of now.
        $eventRiskContextsByTicker = $this->eventRisks !== null
            ? $this->eventRisks->resolveEventRiskContextForTickerIds(array_keys($barsByTicker), $requestedDate, $knownAt)
            : [];
        $tradingDatesWindow = $this->resolveContaminationTradingDates($requestedDate, $barLoadWindow, $historyStartDate);
        $contaminationByTicker = $this->resolveCorporateActionContamination(array_keys($barsByTicker), $tradingDatesWindow, $knownAt);
        $priceScaleBreaksByTicker = $this->resolvePriceScaleBreakContamination(array_keys($barsByTicker), $tradingDatesWindow);
        // Build and persist the exact revision decision set before any vector is written. This
        // replaces the old resolver over mutable `market_data_corporate_actions`: only current
        // AUTHORITATIVE_VERIFIED revisions can now reach an analytical publication.
        $factorContext = $this->factorSets->ensureForPublication(
            $run,
            $candidatePublication->publication_id,
            $requestedDate,
            $barsByTicker
        );
        $adjustmentFactorsByTicker = $factorContext['factors_by_ticker'];
        // Event-risk contamination is resolved before the publication-bound factor set exists.
        // Remove only the exact verified event revision that this candidate factor set actually
        // applies; a legacy factor or a factor from another set cannot clear quarantine.
        foreach ($adjustmentFactorsByTicker as $tickerId => $factorRows) {
            $appliedRevisionIds = array_fill_keys(array_map(function (array $factor) {
                return (int) ($factor['corporate_action_revision_id'] ?? 0);
            }, $factorRows), true);
            if (! isset($contaminationByTicker[(int) $tickerId])) continue;
            $contaminationByTicker[(int) $tickerId] = array_values(array_filter(
                $contaminationByTicker[(int) $tickerId],
                function (array $event) use ($appliedRevisionIds) {
                    $revisionId = (int) ($event['corporate_action_revision_id'] ?? 0);
                    return $revisionId <= 0 || ! isset($appliedRevisionIds[$revisionId]);
                }
            ));
        }
        foreach ($factorContext['held_events_by_ticker'] as $tickerId => $heldEvents) {
            $heldEvents = array_values(array_filter(array_map(function (array $event) use ($tradingDatesWindow) {
                $depth = $this->tradingDayDepth($tradingDatesWindow, (string) ($event['action_date'] ?? ''));
                if ($depth === null) {
                    return null;
                }

                $event['depth'] = $depth;

                return $event;
            }, $heldEvents)));
            $contaminationByTicker[(int) $tickerId] = array_merge(
                $contaminationByTicker[(int) $tickerId] ?? [],
                $heldEvents
            );
        }
        $selectedPriceProductCode = $this->analyticalIdentity->selectedProductCode();
        $priceProductVersion = $this->analyticalIdentity->productVersion();
        $factorSetId = (int) $factorContext['factor_set_id'];
        $factorSetHash = (string) $factorContext['factor_set_hash'];

        // Persist before iterating so a legitimate zero-row run still records the selected basis.
        $this->publications->bindCandidateAnalyticalProduct(
            $candidatePublication->publication_id,
            $run->run_id,
            $selectedPriceProductCode,
            $priceProductVersion,
            $factorSetHash,
            $factorSetId
        );
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
            $listingId = $this->listingIdFromBars($bars);

            // Keep memory proportional to one listing's history. The former all-ticker load held
            // the complete canonical-bar corpus twice and exhausted a 512 MB worker.
            $atrSeries = method_exists($this->artifacts, 'loadAtrSeriesForTickerFromBoundary')
                ? $this->artifacts->loadAtrSeriesForTickerFromBoundary(
                    (int) $tickerId,
                    $requestedDate,
                    $historyStartDate,
                    $useHistory ? $candidatePublication->publication_id : null
                )
                : null;

            $row = $this->vectors->buildRow(
                (int) $tickerId,
                $bars,
                $requestedDate,
                $candidatePublication->publication_id,
                $run->run_id,
                $now,
                $this->vectorConfig(
                    $benchmarkRoc20,
                    $sectorContext,
                    $sectorRoc20,
                    $eventRiskContext,
                    $contamination,
                    $barLoadWindow,
                    $priceScaleBreaks,
                    $adjustmentFactors,
                    $listingId,
                    $run->config_snapshot_id ?? null,
                    $selectedPriceProductCode,
                    $priceProductVersion,
                    $factorSetHash,
                    $factorSetId
                ),
                $atrSeries
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
    private function resolveCorporateActionContamination(array $tickerIds, array $tradingDates, $knownAt = null)
    {
        if ($this->eventRisks === null || empty($tickerIds) || empty($tradingDates)) {
            return [];
        }

        return $this->eventRisks->resolveCorporateActionContaminationForTickerIds($tickerIds, $tradingDates, $knownAt);
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
    private function resolveContaminationTradingDates($requestedDate, $lookbackTradingDays, $historyStartDate = null)
    {
        $windowStart = $this->calendar->tradingDateWindowStart($requestedDate, $lookbackTradingDays);
        if ($historyStartDate !== null && $windowStart < $historyStartDate) {
            $windowStart = $historyStartDate;
        }

        return $this->calendar->tradingDatesBetween($windowStart, $requestedDate);
    }

    private function tradingDayDepth(array $tradingDates, string $actionDate): ?int
    {
        $index = array_search($actionDate, $tradingDates, true);
        if ($index === false) {
            return null;
        }

        return count($tradingDates) - 1 - (int) $index;
    }

    /**
     * Adjustment factors effective inside the loaded window.
     *
     * Owner contract: docs/market_data/registry/Price_Adjustment_Contract_LOCKED.md
     */
    private function vectorConfig($benchmarkRoc20 = null, array $sectorContext = null, $sectorRoc20 = null, array $eventRiskContext = null, array $contamination = [], $atrContaminationHorizonDays = 0, array $priceScaleBreaks = [], array $adjustmentFactors = [], $listingId = null, $configSnapshotId = null, $selectedPriceProductCode = null, $priceProductVersion = null, $factorSetHash = null, $factorSetId = null)
    {
        return [
            'set_version' => config('market_data.indicators.set_version'),
            'formula_version' => config('market_data.indicators.set_version'),
            'listing_id' => $listingId,
            'config_snapshot_id' => $configSnapshotId,
            'selected_price_product_code' => $selectedPriceProductCode,
            'price_product_version' => $priceProductVersion,
            'factor_set_id' => $factorSetId,
            'factor_set_hash' => $factorSetHash,
            'sector_code' => $sectorContext['sector_code'] ?? null,
            'sector_index_code' => $sectorContext['sector_index_code'] ?? null,
            'sector_membership_id' => $sectorContext['sector_membership_id'] ?? null,
            'event_risk_context' => $eventRiskContext ?: [],
            'corporate_action_contamination' => $contamination,
            'price_scale_break_contamination' => $priceScaleBreaks,
            'price_adjustment_factors' => $adjustmentFactors,
            'atr_contamination_horizon_days' => (int) $atrContaminationHorizonDays,
            'dv_window_days' => (int) config('market_data.indicators.dv_window_days'),
            'atr_window_days' => (int) config('market_data.indicators.atr_window_days'),
            'vol_ratio_lookback_days' => (int) config('market_data.indicators.vol_ratio_lookback_days'),
            'roc_lookback_days' => (int) config('market_data.indicators.roc_lookback_days'),
            'hh_window_days' => (int) config('market_data.indicators.hh_window_days'),
            'benchmark_roc20_pct' => $benchmarkRoc20,
            'sector_roc20_pct' => $sectorRoc20,
        ];
    }

    private function listingIdFromBars(array $bars)
    {
        foreach ($bars as $bar) {
            if (! empty($bar['listing_id'])) {
                return (int) $bar['listing_id'];
            }
        }

        return null;
    }
}

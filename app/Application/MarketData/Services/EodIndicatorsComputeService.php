<?php

namespace App\Application\MarketData\Services;

use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\SectorClassificationRepository;
use Carbon\Carbon;

class EodIndicatorsComputeService
{
    private $artifacts;
    private $publications;
    private $vectors;
    private $benchmarkIndicators;
    private $sectors;

    public function __construct(EodArtifactRepository $artifacts, EodPublicationRepository $publications, IndicatorVectorService $vectors, BenchmarkIndicatorComputeService $benchmarkIndicators = null, SectorClassificationRepository $sectors = null)
    {
        $this->artifacts = $artifacts;
        $this->publications = $publications;
        $this->vectors = $vectors;
        $this->benchmarkIndicators = $benchmarkIndicators;
        $this->sectors = $sectors;
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

        $barsByTicker = $this->artifacts->loadBarsWindow($requestedDate, $windowDays + 5, $useHistory ? $candidatePublication->publication_id : null);
        $sectorContextsByTicker = $this->sectors !== null
            ? $this->sectors->resolveSectorContextForTickerIds(array_keys($barsByTicker), $requestedDate)
            : [];
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

            $row = $this->vectors->buildRow(
                (int) $tickerId,
                $bars,
                $requestedDate,
                $candidatePublication->publication_id,
                $run->run_id,
                $now,
                $this->vectorConfig($benchmarkRoc20, $sectorContext, $sectorRoc20)
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

    private function vectorConfig($benchmarkRoc20 = null, array $sectorContext = null, $sectorRoc20 = null)
    {
        return [
            'set_version' => config('market_data.indicators.set_version'),
            'sector_code' => $sectorContext['sector_code'] ?? null,
            'sector_index_code' => $sectorContext['sector_index_code'] ?? null,
            'lot_size' => (int) config('market_data.platform.lot_size'),
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

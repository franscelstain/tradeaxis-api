<?php

namespace App\Application\MarketData\Services;

use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use Carbon\Carbon;

class EodIndicatorsComputeService
{
    private $artifacts;
    private $publications;
    private $vectors;

    public function __construct(EodArtifactRepository $artifacts, EodPublicationRepository $publications, IndicatorVectorService $vectors)
    {
        $this->artifacts = $artifacts;
        $this->publications = $publications;
        $this->vectors = $vectors;
    }

    public function compute($run, $requestedDate, $correctionMode = false)
    {
        $candidatePublication = $this->publications->getOrCreateCandidatePublication($run);
        $windowDays = max(
            (int) config('market_data.indicators.dv_window_days'),
            (int) config('market_data.indicators.vol_ratio_lookback_days') + 1,
            (int) config('market_data.indicators.roc_lookback_days') + 1,
            (int) config('market_data.indicators.hh_window_days'),
            (int) config('market_data.indicators.atr_window_days') + 1
        );

        $barsByTicker = $this->artifacts->loadBarsWindow($requestedDate, $windowDays + 5, $correctionMode ? $candidatePublication->publication_id : null);
        $rows = [];
        $invalidCount = 0;
        $now = Carbon::now(config('market_data.platform.timezone'))->toDateTimeString();

        foreach ($barsByTicker as $tickerId => $bars) {
            $row = $this->vectors->buildRow((int) $tickerId, $bars, $requestedDate, $candidatePublication->publication_id, $run->run_id, $now, $this->vectorConfig());
            if (! $row) {
                continue;
            }

            if ((int) $row['is_valid'] === 0) {
                $invalidCount++;
            }

            $rows[] = $row;
        }

        $this->artifacts->replaceIndicators($requestedDate, $run->run_id, $rows, $candidatePublication->publication_id, $correctionMode);

        return [
            'publication_id' => (int) $candidatePublication->publication_id,
            'publication_version' => (int) $candidatePublication->publication_version,
            'indicators_rows_written' => count($rows),
            'invalid_indicator_count' => $invalidCount,
            'storage_target' => $correctionMode ? 'eod_indicators_history' : 'eod_indicators',
        ];
    }

    private function vectorConfig()
    {
        return [
            'set_version' => config('market_data.indicators.set_version'),
            'lot_size' => (int) config('market_data.platform.lot_size'),
            'price_basis_default' => config('market_data.platform.price_basis_default'),
            'dv_window_days' => (int) config('market_data.indicators.dv_window_days'),
            'atr_window_days' => (int) config('market_data.indicators.atr_window_days'),
            'vol_ratio_lookback_days' => (int) config('market_data.indicators.vol_ratio_lookback_days'),
            'roc_lookback_days' => (int) config('market_data.indicators.roc_lookback_days'),
            'hh_window_days' => (int) config('market_data.indicators.hh_window_days'),
        ];
    }
}

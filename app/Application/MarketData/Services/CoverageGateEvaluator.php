<?php

namespace App\Application\MarketData\Services;

use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use App\Infrastructure\Persistence\MarketData\TickerMasterRepository;

class CoverageGateEvaluator
{
    protected TickerMasterRepository $tickerMasterRepository;
    protected EodArtifactRepository $eodArtifactRepository;

    public function __construct(
        TickerMasterRepository $tickerMasterRepository,
        EodArtifactRepository $eodArtifactRepository
    ) {
        $this->tickerMasterRepository = $tickerMasterRepository;
        $this->eodArtifactRepository = $eodArtifactRepository;
    }

    public function evaluate($tradeDate, $requestedPublicationId = null)
    {
        $universe = $this->tickerMasterRepository->getUniverseForTradeDate($tradeDate);
        $expectedUniverseCount = count($universe);

        $thresholdValue = (float) config('market_data.coverage_gate.min_ratio', config('market_data.platform.coverage_min', 0.98));
        $thresholdMode = (string) config('market_data.coverage_gate.threshold_mode', 'MIN_RATIO');
        $calibrationVersion = (string) config('market_data.coverage_gate.contract_version', 'coverage_gate_v1');
        $missingSampleLimit = (int) config('market_data.coverage_gate.missing_sample_limit', 25);

        $universeByTickerId = [];
        foreach ($universe as $row) {
            $tickerId = isset($row['ticker_id']) ? (int) $row['ticker_id'] : null;
            if ($tickerId === null) {
                continue;
            }

            $universeByTickerId[$tickerId] = [
                'ticker_id' => $tickerId,
                'ticker_code' => isset($row['ticker_code']) ? (string) $row['ticker_code'] : null,
            ];
        }

        if ($expectedUniverseCount === 0) {
            return [
                'expected_universe_count' => 0,
                'available_eod_count' => 0,
                'missing_eod_count' => 0,
                'coverage_ratio' => null,
                'coverage_gate_status' => 'NOT_EVALUABLE',
                'coverage_threshold_value' => $thresholdValue,
                'coverage_threshold_mode' => $thresholdMode,
                'coverage_calibration_version' => $calibrationVersion,
                'reason_code' => 'COVERAGE_UNIVERSE_EMPTY',
                'reason_codes' => ['COVERAGE_UNIVERSE_EMPTY'],
                'missing_ticker_ids' => [],
                'missing_ticker_codes' => [],
            ];
        }

        $availableTickerIds = $this->eodArtifactRepository->loadCanonicalBarTickerIdsForTradeDate($tradeDate, $requestedPublicationId);

        $availableUniverseTickerIds = [];
        foreach ($availableTickerIds as $tickerId) {
            $normalizedTickerId = (int) $tickerId;
            if (array_key_exists($normalizedTickerId, $universeByTickerId)) {
                $availableUniverseTickerIds[$normalizedTickerId] = true;
            }
        }

        $availableEodCount = count($availableUniverseTickerIds);
        $missingRows = [];
        foreach ($universeByTickerId as $tickerId => $row) {
            if (! array_key_exists($tickerId, $availableUniverseTickerIds)) {
                $missingRows[] = $row;
            }
        }

        $missingEodCount = count($missingRows);
        $coverageRatio = $expectedUniverseCount > 0
            ? $availableEodCount / $expectedUniverseCount
            : null;

        $coverageGateStatus = $coverageRatio !== null && $coverageRatio >= $thresholdValue
            ? 'PASS'
            : 'FAIL';

        $reasonCode = $coverageGateStatus === 'PASS'
            ? 'COVERAGE_THRESHOLD_MET'
            : 'COVERAGE_BELOW_THRESHOLD';

        $sampleRows = array_slice($missingRows, 0, max(0, $missingSampleLimit));

        return [
            'expected_universe_count' => $expectedUniverseCount,
            'available_eod_count' => $availableEodCount,
            'missing_eod_count' => $missingEodCount,
            'coverage_ratio' => $coverageRatio,
            'coverage_gate_status' => $coverageGateStatus,
            'coverage_threshold_value' => $thresholdValue,
            'coverage_threshold_mode' => $thresholdMode,
            'coverage_calibration_version' => $calibrationVersion,
            'reason_code' => $reasonCode,
            'reason_codes' => [$reasonCode],
            'missing_ticker_ids' => array_values(array_map(function ($row) {
                return (int) $row['ticker_id'];
            }, $sampleRows)),
            'missing_ticker_codes' => array_values(array_filter(array_map(function ($row) {
                return $row['ticker_code'];
            }, $sampleRows))),
        ];
    }
}

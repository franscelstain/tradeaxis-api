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

        $thresholdValue = (float) config('market_data.coverage_gate.min_ratio', config('market_data.platform.coverage_min', 0.98));
        $thresholdMode = (string) config('market_data.coverage_gate.threshold_mode', 'MIN_RATIO');
        $contractVersion = (string) config('market_data.coverage_gate.contract_version', 'coverage_gate_v1');
        $universeBasis = (string) config('market_data.coverage_gate.universe_basis', 'ACTIVE_TICKER_MASTER_FOR_TRADE_DATE');
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

        $expectedUniverseCount = count($universeByTickerId);

        if ($expectedUniverseCount === 0) {
            return [
                'expected_universe_count' => 0,
                'available_eod_count' => 0,
                'missing_eod_count' => 0,
                'coverage_ratio' => null,
                'coverage_gate_status' => 'NOT_EVALUABLE',
                'coverage_gate_state' => 'NOT_EVALUABLE',
                'coverage_threshold_value' => $thresholdValue,
                'coverage_threshold_mode' => $thresholdMode,
                'coverage_universe_basis' => $universeBasis,
                'coverage_contract_version' => $contractVersion,
                'coverage_calibration_version' => $contractVersion,
                'coverage_reason_code' => 'RUN_COVERAGE_NOT_EVALUABLE',
                'reason_code' => 'COVERAGE_UNIVERSE_EMPTY',
                'reason_codes' => ['COVERAGE_UNIVERSE_EMPTY', 'RUN_COVERAGE_NOT_EVALUABLE'],
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
        $coverageRatio = $availableEodCount / $expectedUniverseCount;

        $coverageGateStatus = $coverageRatio >= $thresholdValue
            ? 'PASS'
            : 'FAIL';

        $reasonCode = $coverageGateStatus === 'PASS'
            ? 'COVERAGE_THRESHOLD_MET'
            : 'COVERAGE_BELOW_THRESHOLD';

        $coverageReasonCode = $coverageGateStatus === 'PASS'
            ? 'COVERAGE_THRESHOLD_MET'
            : 'RUN_COVERAGE_LOW';

        $sampleRows = array_slice($missingRows, 0, max(0, $missingSampleLimit));

        return [
            'expected_universe_count' => $expectedUniverseCount,
            'available_eod_count' => $availableEodCount,
            'missing_eod_count' => $missingEodCount,
            'coverage_ratio' => $coverageRatio,
            'coverage_gate_status' => $coverageGateStatus,
            'coverage_gate_state' => $coverageGateStatus,
            'coverage_threshold_value' => $thresholdValue,
            'coverage_threshold_mode' => $thresholdMode,
            'coverage_universe_basis' => $universeBasis,
            'coverage_contract_version' => $contractVersion,
            'coverage_calibration_version' => $contractVersion,
            'coverage_reason_code' => $coverageReasonCode,
            'reason_code' => $reasonCode,
            'reason_codes' => [$reasonCode, $coverageReasonCode],
            'missing_ticker_ids' => array_values(array_map(function ($row) {
                return (int) $row['ticker_id'];
            }, $sampleRows)),
            'missing_ticker_codes' => array_values(array_filter(array_map(function ($row) {
                return $row['ticker_code'];
            }, $sampleRows))),
        ];
    }
}

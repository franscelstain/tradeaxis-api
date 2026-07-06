<?php

namespace App\Application\MarketData\Services;

use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use App\Infrastructure\Persistence\MarketData\EventRiskSourceRepository;
use App\Infrastructure\Persistence\MarketData\TickerMasterRepository;

class CoverageGateEvaluator
{
    protected TickerMasterRepository $tickerMasterRepository;
    protected EodArtifactRepository $eodArtifactRepository;
    protected ?EventRiskSourceRepository $eventRiskSourceRepository;

    public function __construct(
        TickerMasterRepository $tickerMasterRepository,
        EodArtifactRepository $eodArtifactRepository,
        EventRiskSourceRepository $eventRiskSourceRepository = null
    ) {
        $this->tickerMasterRepository = $tickerMasterRepository;
        $this->eodArtifactRepository = $eodArtifactRepository;
        $this->eventRiskSourceRepository = $eventRiskSourceRepository;
    }

    public function evaluate($tradeDate, $requestedPublicationId = null)
    {
        $coverageBasis = $requestedPublicationId !== null && $requestedPublicationId !== ''
            ? 'CandidatePublication'
            : 'CanonicalTradeDateArtifact';
        $coverageBasisPublicationId = $requestedPublicationId !== null && $requestedPublicationId !== ''
            ? (int) $requestedPublicationId
            : null;

        $thresholdValue = (float) config('market_data.coverage_gate.min_ratio', config('market_data.platform.coverage_min', 0.98));
        $thresholdMode = (string) config('market_data.coverage_gate.threshold_mode', 'MIN_RATIO');
        $contractVersion = (string) config('market_data.coverage_gate.contract_version', 'coverage_gate_v1');
        $universeBasis = (string) config('market_data.coverage_gate.universe_basis', 'ACTIVE_TICKER_MASTER_FOR_TRADE_DATE');
        $missingSampleLimit = (int) config('market_data.coverage_gate.missing_sample_limit', 25);
        $coverageGateEnabled = (bool) config('market_data.coverage_gate.enabled', true);
        $blockedOnZeroUniverse = (bool) config('market_data.coverage_gate.blocked_on_zero_universe', true);
        $requireCanonicalBarEvidence = (bool) config('market_data.coverage_gate.require_canonical_bar_evidence', true);

        if (! $coverageGateEnabled) {
            return $this->notEvaluableResult(
                $thresholdValue,
                $thresholdMode,
                $universeBasis,
                $contractVersion,
                $coverageBasis,
                $coverageBasisPublicationId,
                'COVERAGE_GATE_DISABLED',
                ['COVERAGE_GATE_DISABLED', 'RUN_COVERAGE_NOT_EVALUABLE'],
                [
                    'coverage_gate_enabled' => false,
                    'coverage_zero_universe_blocked' => $blockedOnZeroUniverse,
                    'canonical_bar_evidence_required' => $requireCanonicalBarEvidence,
                ]
            );
        }

        if (! $requireCanonicalBarEvidence) {
            return $this->notEvaluableResult(
                $thresholdValue,
                $thresholdMode,
                $universeBasis,
                $contractVersion,
                $coverageBasis,
                $coverageBasisPublicationId,
                'COVERAGE_CANONICAL_BAR_EVIDENCE_DISABLED',
                ['COVERAGE_CANONICAL_BAR_EVIDENCE_DISABLED', 'RUN_COVERAGE_NOT_EVALUABLE'],
                [
                    'coverage_gate_enabled' => true,
                    'coverage_zero_universe_blocked' => $blockedOnZeroUniverse,
                    'canonical_bar_evidence_required' => false,
                ]
            );
        }

        $rawUniverse = $this->tickerMasterRepository->getUniverseForTradeDate($tradeDate);
        $coverageUniverseCount = count($rawUniverse);
        $universe = $this->filterSuspendedUniverseRows($rawUniverse, $tradeDate);
        $coverageBarNotRequiredCount = max(0, $coverageUniverseCount - count($universe));

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
        $coverageBarRequiredCount = $expectedUniverseCount;

        if ($expectedUniverseCount === 0) {
            return $this->notEvaluableResult(
                $thresholdValue,
                $thresholdMode,
                $universeBasis,
                $contractVersion,
                $coverageBasis,
                $coverageBasisPublicationId,
                'COVERAGE_UNIVERSE_EMPTY',
                ['COVERAGE_UNIVERSE_EMPTY', 'RUN_COVERAGE_NOT_EVALUABLE'],
                [
                    'coverage_gate_enabled' => true,
                    'coverage_zero_universe_blocked' => $blockedOnZeroUniverse,
                    'canonical_bar_evidence_required' => true,
                    'coverage_universe_count' => $coverageUniverseCount,
                    'coverage_bar_not_required_count' => $coverageBarNotRequiredCount,
                    'coverage_bar_required_count' => 0,
                ]
            );
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
            'coverage_universe_count' => $coverageUniverseCount,
            'coverage_bar_not_required_count' => $coverageBarNotRequiredCount,
            'coverage_bar_required_count' => $coverageBarRequiredCount,
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
            'coverage_basis' => $coverageBasis,
            'coverage_basis_publication_id' => $coverageBasisPublicationId,
            'candidate_publication_id' => $coverageBasisPublicationId,
            'coverage_basis_artifact_scope' => $coverageBasisPublicationId !== null ? 'candidate_publication_artifact' : 'trade_date_artifact',
            'candidate_available_count' => $availableEodCount,
            'candidate_missing_count' => $missingEodCount,
            'candidate_coverage_ratio' => $coverageRatio,
            'reason_code' => $reasonCode,
            'reason_codes' => [$reasonCode, $coverageReasonCode],
            'missing_ticker_ids' => array_values(array_map(function ($row) {
                return (int) $row['ticker_id'];
            }, $sampleRows)),
            'coverage_gate_enabled' => true,
            'coverage_zero_universe_blocked' => $blockedOnZeroUniverse,
            'canonical_bar_evidence_required' => true,
            'missing_ticker_codes' => array_values(array_filter(array_map(function ($row) {
                return $row['ticker_code'];
            }, $sampleRows))),
        ];
    }

    private function notEvaluableResult(
        $thresholdValue,
        $thresholdMode,
        $universeBasis,
        $contractVersion,
        $coverageBasis,
        $coverageBasisPublicationId,
        $reasonCode,
        array $reasonCodes,
        array $policy = []
    ) {
        return $policy + [
            'expected_universe_count' => 0,
            'coverage_universe_count' => 0,
            'coverage_bar_not_required_count' => 0,
            'coverage_bar_required_count' => 0,
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
            'coverage_basis' => $coverageBasis,
            'coverage_basis_publication_id' => $coverageBasisPublicationId,
            'candidate_publication_id' => $coverageBasisPublicationId,
            'coverage_basis_artifact_scope' => $coverageBasisPublicationId !== null ? 'candidate_publication_artifact' : 'trade_date_artifact',
            'candidate_available_count' => 0,
            'candidate_missing_count' => 0,
            'candidate_coverage_ratio' => null,
            'reason_code' => $reasonCode,
            'reason_codes' => $reasonCodes,
            'missing_ticker_ids' => [],
            'missing_ticker_codes' => [],
        ];
    }

    private function filterSuspendedUniverseRows(array $universeRows, $tradeDate): array
    {
        if (! $this->eventRiskSourceRepository instanceof EventRiskSourceRepository || $universeRows === []) {
            return $universeRows;
        }

        $tickerIds = array_values(array_filter(array_map(function ($row) {
            return (int) ($row['ticker_id'] ?? 0);
        }, $universeRows)));

        if ($tickerIds === []) {
            return $universeRows;
        }

        $suspendedIds = array_fill_keys($this->eventRiskSourceRepository->suspendedTickerIdsAsOf($tickerIds, $tradeDate), true);
        if ($suspendedIds === []) {
            return $universeRows;
        }

        return array_values(array_filter($universeRows, function ($row) use ($suspendedIds) {
            $tickerId = (int) ($row['ticker_id'] ?? 0);

            return $tickerId > 0 && ! isset($suspendedIds[$tickerId]);
        }));
    }
}

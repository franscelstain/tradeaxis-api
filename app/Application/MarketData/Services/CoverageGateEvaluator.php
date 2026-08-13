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

    /**
     * `$knownAt` makes the denominator reproducible for a fixed trade date.
     *
     * Both of its inputs were cutoff-blind: the universe resolved "as of now", and the suspension
     * lookup did too. Either one moving between two runs of the same trade date moves the
     * denominator with it, which is what `F-006` measured as 950 → 949 → 950 on one execution day.
     * With a cutoff the answer is a function of (trade date, knowledge time) and a replay can
     * reproduce it.
     */
    public function evaluate($tradeDate, $requestedPublicationId = null, $knownAt = null)
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

        $rawUniverse = $this->tickerMasterRepository->getUniverseForTradeDate($tradeDate, $knownAt);
        $coverageUniverseCount = count($rawUniverse);
        $universe = $this->filterSuspendedUniverseRows($rawUniverse, $tradeDate, $knownAt);
        $coverageBarNotExpectedCount = max(0, $coverageUniverseCount - count($universe));

        /*
         * Dormancy does not leave the denominator, and the reason is not bookkeeping.
         *
         * `Coverage_Universe_Definition_LOCKED.md:35-38` states that dormancy, absence of recent
         * bars, historical zero volume, and illiquidity cannot prove `NOT_EXPECTED`, and `:45`
         * gives the danger plainly: excluding them hides provider outages and makes coverage look
         * healthier as missing data accumulates. A ticker that stops arriving because the feed
         * broke is indistinguishable from one that stopped trading, so removing the quiet ones
         * removes exactly the evidence an outage would show up in.
         *
         * `Reason_Codes_Registry.md` records the same decision from the other side:
         * `COVERAGE_DORMANT_TICKERS_EXCLUDED` is deprecated, and any runtime emission of it is a
         * migration failure.
         *
         * Only verified `NOT_EXPECTED` may be excluded (`:21`), which here means a verified
         * full-session suspension. Everything else stays in, and `UNKNOWN` stays in fail-safe.
         */
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

        /*
         * `Coverage_Universe_Definition_LOCKED.md:52` requires EXPECTED, NOT_EXPECTED and UNKNOWN
         * to be recorded, and `:57` fixes the denominator as EXPECTED + UNKNOWN. UNKNOWN was never
         * computed, so the pipeline wrote a hard zero and the state could not be told apart from
         * EXPECTED — which `:21` forbids, because UNKNOWN must stay visible.
         *
         * Counted from the same filtered set that forms the denominator, so the identity
         * EXPECTED + UNKNOWN = denominator holds by construction rather than by arithmetic luck.
         */
        $expectationUnknownCount = $this->expectationUnknownCount($universeByTickerId, $tradeDate, $knownAt);

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
                    'coverage_bar_not_expected_count' => $coverageBarNotExpectedCount,
                    'coverage_expectation_unknown_count' => $expectationUnknownCount,
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

        $reasonCodes = [$reasonCode, $coverageReasonCode];

        return [
            'expected_universe_count' => $expectedUniverseCount,
            'coverage_universe_count' => $coverageUniverseCount,
            'coverage_bar_not_expected_count' => $coverageBarNotExpectedCount,
            'coverage_expectation_unknown_count' => $expectationUnknownCount,
            'coverage_universe_hash' => $this->universeHash($rawUniverse, $universeBasis, $tradeDate),
            'coverage_excluded_sample' => $this->excludedSample($rawUniverse, $universeByTickerId, $missingSampleLimit),
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
            'reason_codes' => $reasonCodes,
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
            'coverage_bar_not_expected_count' => 0,
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

    /**
     * A canonical hash over the universe that produced this run's denominator.
     *
     * `Coverage_Universe_Definition_LOCKED.md:52` asks for the temporal universe count **and**
     * version/hash; only the count was stored. Two runs for one trade date could therefore resolve
     * different universes with nothing recording which one each used.
     *
     * Follows the convention of `AnalyticalProductIdentityService::factorSetHash`: a schema tag so
     * the shape can change without silently colliding, the basis, and an explicitly sorted identity
     * list so ordering from the query can never alter the hash.
     */
    private function universeHash(array $universeRows, $universeBasis, $tradeDate): string
    {
        $identities = array_values(array_unique(array_map(function ($row) {
            return (int) ($row['listing_id'] ?? 0).':'.(int) ($row['ticker_id'] ?? 0);
        }, $universeRows)));
        sort($identities, SORT_STRING);

        return hash('sha256', json_encode([
            'universe_hash_schema_version' => 'coverage_universe_v1',
            'universe_basis' => (string) $universeBasis,
            'trade_date' => (string) $tradeDate,
            'listings' => $identities,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /**
     * Identities of the listings that left the denominator as verified `NOT_EXPECTED`.
     *
     * Bounded by the same sample limit as the missing sample, because the point is auditability
     * rather than a full dump: a reader needs to be able to check an exclusion back against its
     * source evidence, which naming a sample allows and a bare count does not.
     */
    private function excludedSample(array $rawUniverse, array $universeByTickerId, int $limit): array
    {
        if ($limit <= 0) {
            return [];
        }

        $excluded = [];
        foreach ($rawUniverse as $row) {
            $tickerId = (int) ($row['ticker_id'] ?? 0);
            if ($tickerId > 0 && ! array_key_exists($tickerId, $universeByTickerId)) {
                $excluded[] = [
                    'ticker_id' => $tickerId,
                    'ticker_code' => isset($row['ticker_code']) ? (string) $row['ticker_code'] : null,
                ];
            }

            if (count($excluded) >= $limit) {
                break;
            }
        }

        return $excluded;
    }

    /**
     * How many denominator listings have expectation evidence the platform cannot resolve.
     *
     * If the expectation source itself is unavailable, every denominator member is UNKNOWN. A hard
     * zero would claim the platform checked the source and found no uncertainty, which is precisely
     * the false measurement this evidence field exists to prevent.
     */
    private function expectationUnknownCount(array $universeByTickerId, $tradeDate, $knownAt = null): int
    {
        if ($universeByTickerId === []) {
            return 0;
        }

        if (! $this->eventRiskSourceRepository instanceof EventRiskSourceRepository) {
            return count($universeByTickerId);
        }

        return count($this->eventRiskSourceRepository->expectationUnknownTickerIdsAsOf(
            array_keys($universeByTickerId),
            $tradeDate,
            $knownAt
        ));
    }

    private function filterSuspendedUniverseRows(array $universeRows, $tradeDate, $knownAt = null): array
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

        $suspendedIds = array_fill_keys($this->eventRiskSourceRepository->suspendedTickerIdsAsOf($tickerIds, $tradeDate, $knownAt), true);
        if ($suspendedIds === []) {
            return $universeRows;
        }

        return array_values(array_filter($universeRows, function ($row) use ($suspendedIds) {
            $tickerId = (int) ($row['ticker_id'] ?? 0);

            return $tickerId > 0 && ! isset($suspendedIds[$tickerId]);
        }));
    }
}

<?php

namespace App\Application\MarketData\Services;

use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\EventRiskSourceRepository;
use App\Infrastructure\Persistence\MarketData\TickerMasterRepository;
use Carbon\Carbon;

class EodEligibilityBuildService
{
    private $tickers;
    private $artifacts;
    private $publications;
    private $decisions;
    private $eventRiskSources;
    private $expectations;

    public function __construct(
        TickerMasterRepository $tickers,
        EodArtifactRepository $artifacts,
        EodPublicationRepository $publications,
        EligibilityDecisionService $decisions,
        EventRiskSourceRepository $eventRiskSources = null,
        ExpectedBarDecisionService $expectations = null
    ) {
        $this->tickers = $tickers;
        $this->artifacts = $artifacts;
        $this->publications = $publications;
        $this->decisions = $decisions;
        $this->eventRiskSources = $eventRiskSources;
        $this->expectations = $expectations ?: new ExpectedBarDecisionService();
    }

    public function build($run, $requestedDate, $correctionMode = false)
    {
        $candidatePublication = $this->publications->getOrCreateCandidatePublication($run);
        $useHistory = $correctionMode
            || (int) ($candidatePublication->publication_version ?? 1) > 1
            || ! empty($candidatePublication->supersedes_publication_id)
            || ! empty($candidatePublication->previous_publication_id)
            || ! empty($candidatePublication->replaced_publication_id);

        /*
         * Every temporal listing gets a row, including the suspended ones.
         *
         * Suspension used to remove the listing from the universe before the snapshot was built,
         * so an instrument that was blocked simply vanished from the record. That inverts what the
         * snapshot is for: a reader asking why an instrument is absent today gets no answer at
         * all, and absence-because-suspended becomes indistinguishable from absence-because-never-
         * listed. `EOD_Eligibility_Snapshot_Contract_LOCKED.md` requires one publication-bound row
         * per temporal listing with status persisted separately, which is the opposite of dropping
         * the row and keeping nothing.
         */
        $knownAt = $run->started_at ?? $run->created_at ?? null;
        $universe = $this->tickers->getUniverseForTradeDate($requestedDate, $knownAt);
        $tradingStatusContexts = $this->tradingStatusContexts($universe, $requestedDate, $knownAt);

        /*
         * Dormancy is recorded here rather than in the coverage denominator.
         *
         * `Coverage_Universe_Definition_LOCKED.md:45` calls dormancy, zero-volume history, and
         * liquidity "separate factual dimensions" and forbids them altering coverage, because a
         * ticker that stopped arriving from a broken feed looks exactly like one that stopped
         * trading. The fact is still worth knowing — it just belongs on the usability row as a
         * liquidity observation, where a reader can see it without it moving a gate.
         */
        $dormantTickerIds = $this->dormantTickerIdSet($universe, $requestedDate);
        $bars = $this->artifacts->loadBarsForTradeDate($requestedDate, $useHistory ? $candidatePublication->publication_id : null);
        $deliveredTickerIds = array_fill_keys($this->artifacts->loadDeliveredObservationTickerIdsForTradeDate(
            $requestedDate,
            $run->run_id,
            $candidatePublication->publication_id
        ), true);
        $indicators = $this->artifacts->loadIndicatorsForTradeDate($requestedDate, $useHistory ? $candidatePublication->publication_id : null);
        $rows = [];
        $blockedCount = 0;
        $now = Carbon::now(config('market_data.platform.timezone'))->toDateTimeString();

        foreach ($universe as $ticker) {
            $tickerId = $ticker['ticker_id'];
            $bar = isset($bars[$tickerId]) ? $bars[$tickerId] : null;
            $indicator = isset($indicators[$tickerId]) ? $indicators[$tickerId] : null;
            $statusContext = $tradingStatusContexts[$tickerId] ?? [];
            $expectation = $this->expectations->decideForListing(
                (int) ($ticker['listing_id'] ?? 0),
                $requestedDate,
                $knownAt
            );
            $isSuspended = (string) $expectation['bar_expectation_state'] === 'BAR_NOT_EXPECTED'
                && strpos((string) ($expectation['trading_status_code'] ?? ''), 'SUSPENS') !== false;
            $decision = $this->decisions->decide($bar, $indicator);
            $reasonCode = $decision['reason_code'];
            $eligible = $decision['eligible'];
            $reasons = [];

            if ($isSuspended) {
                // A suspended listing is not usable, and it says so rather than disappearing.
                // The suspension reason leads the ordered set because it explains the absence of
                // the bar rather than merely restating it.
                $eligible = 0;
                $reasons[] = 'ELIG_TRADING_SUSPENDED';
                $reasonCode = $reasonCode ?: 'ELIG_TRADING_SUSPENDED';
            }

            if ($decision['reason_code'] !== null) {
                $reasons[] = $decision['reason_code'];
            }

            if ($eligible === 0) {
                $blockedCount++;
            }

            $rows[] = [
                'trade_date' => $requestedDate,
                'ticker_id' => $tickerId,
                'eligible' => $eligible,
                'reason_code' => $reasonCode,
                'listing_id' => $ticker['listing_id'] ?? null,
                'universe_membership_state' => 'MEMBER',
                // Recorded separately so a reader never has to infer one dimension from another.
                // A single scalar reason cannot carry an ordered set, and the first reason written
                // would silently erase every later one.
                'bar_expectation_state' => (string) $expectation['bar_expectation_state'],
                'delivery_state' => isset($deliveredTickerIds[$tickerId]) ? 'DELIVERED' : 'NOT_DELIVERED',
                'canonical_quality_state' => $this->canonicalQualityState($bar, isset($deliveredTickerIds[$tickerId])),
                // An observation, never an input to the usability decision: W16 proved the
                // decision consults no liquidity preference, and this must not become one.
                'liquidity_state' => isset($dormantTickerIds[$tickerId]) ? 'DORMANT' : 'ACTIVE',
                'temporal_status_state' => (string) ($expectation['trading_status_code'] ?? 'UNKNOWN'),
                'trading_status_revision_id' => count($expectation['trading_status_revision_ids'] ?? []) === 1
                    ? (int) $expectation['trading_status_revision_ids'][0]
                    : null,
                'trading_status_source_observation_id' => count($expectation['trading_status_source_observation_ids'] ?? []) === 1
                    ? (int) $expectation['trading_status_source_observation_ids'][0]
                    : null,
                // A factual projection of the indicator flag only. EligibilityDecisionService
                // remains independent of event preference as required by the W16 owner contract.
                'event_risk_state' => $this->eventRiskState($indicator),
                /*
                 * The three dimensions the snapshot contract enumerates and the row did not carry.
                 * Every input was already in memory; the row simply did not persist them, which the
                 * contract calls a defect against itself rather than a licence to widen reason_code.
                 */
                'source_provenance_state' => $this->sourceProvenanceState($bar),
                'price_basis_state' => $this->priceBasisState($indicator),
                'contamination_state' => $this->contaminationState($indicator),
                'indicator_state' => $this->indicatorState($indicator),
                'eligibility_reasons_json' => json_encode(array_values(array_unique($reasons))),
                'run_id' => $run->run_id,
                'publication_id' => $candidatePublication->publication_id,
                'created_at' => $now,
            ];
        }

        $this->artifacts->replaceEligibility($requestedDate, $run->run_id, $rows, $candidatePublication->publication_id, $useHistory);

        $eligibilityRowsWritten = count($rows);
        $eligibleRows = $eligibilityRowsWritten - $blockedCount;

        return [
            'publication_id' => (int) $candidatePublication->publication_id,
            'publication_version' => (int) $candidatePublication->publication_version,
            'eligibility_rows_written' => $eligibilityRowsWritten,
            'blocked_rows' => $blockedCount,
            'eligible_rows' => $eligibleRows,
            'eligibility_pass_ratio' => $eligibilityRowsWritten > 0 ? round($eligibleRows / $eligibilityRowsWritten, 4) : null,
            'storage_target' => $useHistory ? 'eod_eligibility_history' : 'eod_eligibility',
        ];
    }

    private function canonicalQualityState($bar, $delivered = false): string
    {
        if ($bar === null) {
            return $delivered ? 'INVALID_OR_REJECTED' : 'UNAVAILABLE';
        }

        $qualityState = $bar['quality_state'] ?? null;

        return $qualityState === null || trim((string) $qualityState) === ''
            ? 'UNKNOWN'
            : (string) $qualityState;
    }

    /**
     * Whether a delivered observation is traceable to accepted source evidence.
     *
     * The acceptance criterion requires a consumer to inspect this without reading internal tables,
     * so the eligibility row states it rather than pointing at the bar.
     */
    private function sourceProvenanceState($bar): string
    {
        if ($bar === null) {
            return 'NO_OBSERVATION';
        }
        $observationId = isset($bar['source_observation_id']) ? (int) $bar['source_observation_id'] : 0;
        if ($observationId <= 0) {
            // A bar with no source observation is untraceable, which is a different fact from an
            // absent bar and must not be reported as the same thing.
            return 'UNTRACEABLE';
        }

        return 'SOURCE_TRACEABLE';
    }

    /**
     * The analytical price basis the row was computed on.
     *
     * Basis and contamination are two columns rather than one delimited value. Packing two facts
     * into a single string is a smaller version of the overloading this contract exists to forbid,
     * and the deterministic hash service refuses a delimiter inside a hashed field for the same
     * reason — it caught the first draft of this method.
     */
    private function priceBasisState($indicator): string
    {
        if ($indicator === null) {
            return 'UNKNOWN';
        }

        $basis = trim((string) ($indicator['price_product_code'] ?? ''));

        // An indicator row that never recorded its price product cannot be read as clean: the
        // basis is what makes the number comparable at all.
        return $basis === '' ? 'BASIS_UNRECORDED' : $basis;
    }

    /** Whether the dependency window carried a recorded contamination reason. */
    private function contaminationState($indicator): string
    {
        if ($indicator === null) {
            return 'UNKNOWN';
        }

        $reasons = trim((string) ($indicator['corporate_action_window_reasons'] ?? ''));

        // An empty reason set records what was detected, not what occurred. CLEAN here means no
        // contamination was detected, which the indicator registry is explicit is a weaker claim.
        return $reasons === '' ? 'NO_CONTAMINATION_DETECTED' : 'CONTAMINATED';
    }

    /**
     * Indicator validity together with warm-up and nullability, which the partial-data contract
     * requires to be explicit rather than inferred from the absence of a value.
     *
     * `null_reasons_json` exists because `MD-B14-A001` found the row carrying only a compatibility
     * primary reason. Reading it here is what makes "warm-up state and reasons explicit" true on
     * the row that exists to explain the instrument.
     */
    private function indicatorState($indicator): string
    {
        if ($indicator === null) {
            return 'NO_INDICATOR_ROW';
        }

        $valid = isset($indicator['is_valid']) ? (int) $indicator['is_valid'] : 0;
        $nullReasons = trim((string) ($indicator['null_reasons_json'] ?? ''));
        $hasFieldReasons = $nullReasons !== '' && $nullReasons !== 'null' && $nullReasons !== '[]' && $nullReasons !== '{}';

        if ($valid !== 1) {
            // The specific invalid reason already travels in the ordered reason set; this column
            // states the dimension, and packing the code in here would delimit a hashed field.
            return 'INVALID';
        }

        // A valid row may still carry per-field nulls. The contract wants that visible, because a
        // consumer must be able to see why a usable row is usable rather than only that nothing
        // objected to it.
        return $hasFieldReasons ? 'VALID_WITH_FIELD_NULLS' : 'VALID';
    }

    private function eventRiskState($indicator): string
    {
        if ($indicator === null
            || ! array_key_exists('event_risk_flag', $indicator)
            || $indicator['event_risk_flag'] === null) {
            return 'UNKNOWN';
        }

        if ($indicator['event_risk_flag'] === true
            || $indicator['event_risk_flag'] === 1
            || $indicator['event_risk_flag'] === '1') {
            return 'FLAGGED';
        }

        if ($indicator['event_risk_flag'] === false
            || $indicator['event_risk_flag'] === 0
            || $indicator['event_risk_flag'] === '0') {
            return 'CLEAR';
        }

        return 'UNKNOWN';
    }

    /**
     * Resolve which listings have been silent beyond the dormancy horizon, as an observation.
     *
     * A resolution failure yields an empty set rather than an exception: dormancy is descriptive
     * here, so failing to describe it must not stop a snapshot from being written.
     */
    private function dormantTickerIdSet(array $universeRows, $tradeDate): array
    {
        $lookback = (int) config('market_data.activity.dormant_absence_trading_days', 60);
        $tickerIds = array_values(array_filter(array_map(function ($row) {
            return (int) ($row['ticker_id'] ?? 0);
        }, $universeRows)));

        if ($lookback < 1 || $tickerIds === []) {
            return [];
        }

        try {
            $dormant = $this->artifacts->loadDormantTickerIds($tickerIds, $tradeDate, $lookback);
        } catch (\Throwable $e) {
            return [];
        }

        return is_array($dormant) ? array_fill_keys($dormant, true) : [];
    }

    /**
     * Resolve which listings were suspended on the requested date, as a lookup rather than a
     * filter. The distinction is the whole point: the set is used to annotate rows, never to
     * remove them.
     */
    private function tradingStatusContexts(array $universeRows, $tradeDate, $knownAt = null): array
    {
        if (! $this->eventRiskSources instanceof EventRiskSourceRepository || $universeRows === []) {
            return [];
        }

        $tickerIds = array_values(array_filter(array_map(function ($row) {
            return (int) ($row['ticker_id'] ?? 0);
        }, $universeRows)));

        if ($tickerIds === []) {
            return [];
        }

        return $this->eventRiskSources->resolveEventRiskContextForTickerIds($tickerIds, $tradeDate, $knownAt);
    }
}

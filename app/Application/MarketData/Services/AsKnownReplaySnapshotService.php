<?php

namespace App\Application\MarketData\Services;

use App\Domain\MarketData\MarketDataScope;
use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;
use App\Infrastructure\Persistence\MarketData\MarketDataConfigSnapshotRepository;
use App\Infrastructure\Persistence\MarketData\SourceObservationRepository;
use App\Infrastructure\Persistence\MarketData\TemporalIdentityRepository;
use App\Infrastructure\Persistence\MarketData\TemporalTradingStatusRepository;
use Illuminate\Support\Facades\DB;

final class AsKnownReplaySnapshotService
{
    /**
     * Marks a reason-registry identity that the configuration snapshot resolved as known at the cutoff cannot supply.
     *
     * Since `D-MD-B18-A002-018` (Q9 = A1) every NEW configuration snapshot carries the reason-registry semantic
     * identity as a derived member, and an as-known replay binds exactly that member of the snapshot it resolved --
     * never the current registry, the latest run capture, a placeholder or an inferred value. This marker is what is
     * left for the snapshots that cannot supply it: a historical snapshot issued before the member existed (never
     * back-filled, mutated or rewritten) or one whose member is malformed. Such a replay is `BLOCKED`
     * (`Replay_Verification_Contract_LOCKED.md:33`), exactly as `MD-S050-R0016` Gap B2 recorded before the member
     * existed. It is never the identity of a replay that proceeds: `ReplayResultRepository` refuses a non-BLOCKED
     * result that carries it.
     */
    public const REASON_REGISTRY_IDENTITY_UNAVAILABLE = 'REASON_REGISTRY_IDENTITY_UNAVAILABLE';

    /**
     * `MD-S050-R0016` five-domain cumulative audit: `readProjectedUniverseAsOf()` returns an empty
     * array, never throws, when the platform has no listing known as of the cutoff -- a
     * whole-system-empty state, not a per-date business outcome (unlike a non-trading day, which
     * still has exactly one governed calendar row). `hash([[], []])` is a real-looking 64-character
     * string regardless, so an empty universe was silently admissible. Listing existence does not
     * depend on today being a trading day, so this check is unconditional.
     */
    public const TEMPORAL_IDENTITY_UNAVAILABLE = 'TEMPORAL_IDENTITY_UNAVAILABLE';

    /**
     * `MD-S050-R0016` five-domain cumulative audit: `observationManifestAsKnown()`/
     * `normalizedRowsManifestAsKnown()` never throw on zero rows -- `test_zero_row_provider_outage_
     * remains_in_as_known_observation_manifest` already proves a *recorded* outage (observation_count
     * 1, a `SOURCE_TIMEOUT`-reasoned row) is legitimately represented and must not be confused with
     * this marker. This marker is for the different case: zero rows recorded at all. Gated on
     * `is_trading_day`, matching the same distinction `ExpectedBarDecisionService::decide()` already
     * draws (`EXPECTED_BAR_NON_TRADING_DAY`) -- a non-trading day legitimately has no observations to
     * record, so only a trading day with nothing recorded is treated as a genuine gap.
     */
    public const SOURCE_OBSERVATION_UNAVAILABLE = 'SOURCE_OBSERVATION_UNAVAILABLE';

    private $identity;
    private $calendar;
    private $statuses;
    private $configSnapshots;
    private $observations;

    public function __construct(
        TemporalIdentityRepository $identity = null,
        MarketCalendarRepository $calendar = null,
        TemporalTradingStatusRepository $statuses = null,
        MarketDataConfigSnapshotRepository $configSnapshots = null,
        SourceObservationRepository $observations = null
    ) {
        $this->identity = $identity ?: new TemporalIdentityRepository();
        $this->calendar = $calendar ?: new MarketCalendarRepository();
        $this->statuses = $statuses ?: new TemporalTradingStatusRepository();
        $this->configSnapshots = $configSnapshots ?: new MarketDataConfigSnapshotRepository();
        $this->observations = $observations ?: new SourceObservationRepository();
    }

    public function capture($tradeDate, $knowledgeCutoff): array
    {
        $tradeDate = MarketDataScope::fromConfig()->assertRequestedDate($tradeDate);
        $knowledgeCutoff = trim((string) $knowledgeCutoff);
        if ($knowledgeCutoff === '') {
            throw new \RuntimeException('REPLAY_KNOWLEDGE_CUTOFF_REQUIRED: AS_KNOWN replay requires knowledge_cutoff.');
        }

        $universe = array_map(function ($row) {
            $row = (array) $row;
            return $this->only($row, [
                'listing_id', 'ticker_id', 'ticker_code', 'listing_symbol_id', 'symbol_recorded_at',
                'listing_board_id', 'board_code', 'market_segment', 'board_recorded_at',
                'provider_mapping_id', 'provider', 'provider_symbol', 'mapping_revision',
                'provider_mapping_recorded_at', 'listed_date', 'delisted_date', 'listing_recorded_at',
                'instrument_id', 'instrument_uid', 'issuer_id', 'issuer_uid',
            ]);
        }, $this->identity->readProjectedUniverseAsOf($tradeDate, $knowledgeCutoff));
        usort($universe, function ($a, $b) { return ((int) ($a['listing_id'] ?? 0)) <=> ((int) ($b['listing_id'] ?? 0)); });

        $statusContexts = [];
        foreach ($universe as $row) {
            $listingId = (int) ($row['listing_id'] ?? 0);
            if ($listingId <= 0) continue;
            $statusContexts[$listingId] = $this->statuses->resolveForListing($listingId, $tradeDate, $knowledgeCutoff);
        }
        ksort($statusContexts, SORT_NUMERIC);

        $calendar = $this->calendar->sessionContext($tradeDate, $knowledgeCutoff);
        $config = $this->configSnapshots->resolveForRun($tradeDate, $knowledgeCutoff);
        $configPayload = $this->resolvedConfigPayload($config);
        $resolvedConfig = $configPayload['resolved_config'];
        $sourceManifest = $this->observations->observationManifestAsKnown($tradeDate, $knowledgeCutoff);
        $normalizedRowsManifest = $this->observations->normalizedRowsManifestAsKnown($tradeDate, $knowledgeCutoff);
        $eventsAndFactors = $this->eventFactorContext($tradeDate, $knowledgeCutoff);

        $formulaIdentity = [
            'indicator_config' => isset($resolvedConfig['indicators']) ? $resolvedConfig['indicators'] : [],
            'quality_gates' => isset($resolvedConfig['quality_gates']) ? $resolvedConfig['quality_gates'] : [],
            'coverage' => isset($resolvedConfig['coverage']) ? $resolvedConfig['coverage'] : [],
            'eligibility' => isset($resolvedConfig['eligibility']) ? $resolvedConfig['eligibility'] : [],
            'semantic_bindings' => $configPayload['semantic_bindings'],
        ];
        // D-MD-B18-A002-018: the identity of the reason registry is the derived member of the snapshot resolved as known
        // at the cutoff, and nothing else. null when that snapshot carries no valid member (see the marker's docblock).
        $reasonIdentity = $this->reasonRegistryMember($configPayload);

        $context = [
            'replay_mode' => ReplayMode::AS_KNOWN,
            'trade_date' => $tradeDate,
            'knowledge_cutoff' => $knowledgeCutoff,
            'dataset_start' => MarketDataScope::DATASET_START,
            'temporal_universe' => $universe,
            'calendar_context' => $calendar,
            'trading_status_contexts' => $statusContexts,
            'source_observation_manifest' => $sourceManifest,
            'normalized_source_rows_manifest' => $normalizedRowsManifest,
            'config_snapshot' => $this->only($config, [
                'config_snapshot_id', 'snapshot_uid', 'snapshot_schema_version', 'serialization_version',
                'config_hash', 'registry_revision', 'effective_at', 'recorded_at', 'build_id',
                'environment_profile', 'resolver_version',
            ]),
            'event_factor_context' => $eventsAndFactors,
            'formula_registry_identity' => $formulaIdentity,
            'reason_registry_identity' => $reasonIdentity,
            // `D-MD-B18-A002-008`: `read_model_version` binds the versioned replay/read-product
            // contract of the artifact being rendered -- not a value resolved from the config
            // snapshot as known at the cutoff (which every other member of this `$context` array
            // legitimately is). `governance.read_model_version` never existed as a configuration
            // key, so this always read empty. Bound to the same canonical identity
            // `MarketDataReadProductService::READ_MODEL_VERSION` the publication path already
            // uses, never re-derived, never read from a cutoff-scoped `governance` source.
            'read_model_version' => MarketDataReadProductService::READ_MODEL_VERSION,
            'serialization_version' => (string) (isset($config['serialization_version'])
                ? $config['serialization_version'] : ''),
            'executable_build_identity' => (string) config('market_data.governance.build_id', 'development-worktree'),
        ];

        $isTradingDay = ($calendar['is_trading_day'] ?? null) === true;

        return $context + [
            // `MD-S050-R0016`: an empty universe is a whole-system-empty state (see
            // `self::TEMPORAL_IDENTITY_UNAVAILABLE`), unconditional on trading-day status.
            'temporal_identity_hash' => $universe === []
                ? self::TEMPORAL_IDENTITY_UNAVAILABLE
                : $this->hash([$universe, $statusContexts]),
            'calendar_status_hash' => $this->hash([$calendar, $statusContexts]),
            // `MD-S050-R0016`: zero observations on a trading day is a genuine gap (see
            // `self::SOURCE_OBSERVATION_UNAVAILABLE`); zero on a non-trading day is the correct,
            // expected value (`EXPECTED_BAR_NON_TRADING_DAY` precedent), so it is left as a real hash.
            'source_observation_manifest_hash' => ($isTradingDay && (int) ($sourceManifest['observation_count'] ?? 0) === 0)
                ? self::SOURCE_OBSERVATION_UNAVAILABLE
                : $sourceManifest['manifest_hash'],
            'canonical_raw_input_hash' => ($isTradingDay && (int) ($normalizedRowsManifest['row_count'] ?? 0) === 0)
                ? self::SOURCE_OBSERVATION_UNAVAILABLE
                : $this->hash($normalizedRowsManifest['rows']),
            // `MD-S050-R0016`: corporate-action/factor-set revisions have no minimum-cardinality
            // requirement -- zero is the common, legitimate value for the overwhelming majority of
            // trade dates (most listings have no corporate action most days), and no analogous
            // "was one expected" signal exists for this domain the way `is_trading_day` does for
            // source observations. Left as a real hash of whatever did or did not occur; no marker.
            'event_factor_hash' => $this->hash($eventsAndFactors),
            'config_snapshot_id' => isset($config['config_snapshot_id']) ? (int) $config['config_snapshot_id'] : null,
            'config_snapshot_hash' => (string) ($config['config_hash'] ?? ''),
            'formula_registry_hash' => $this->hash($formulaIdentity),
            // The semantic identity carried by the snapshot resolved at the cutoff (the same 64-hex value
            // PUBLICATION_EXACT derives from the registry it froze), or the BLOCKED marker when that snapshot has none.
            // Never read from the current registry.
            'reason_registry_hash' => $reasonIdentity === null
                ? self::REASON_REGISTRY_IDENTITY_UNAVAILABLE
                : $reasonIdentity['semantic_identity'],
            'snapshot_hash' => $this->hash($context),
        ];
    }

    private function eventFactorContext($tradeDate, $knowledgeCutoff): array
    {
        $events = DB::table('md_corporate_action_revisions')
            ->where('recorded_at', '<=', $knowledgeCutoff)
            ->where(function ($q) use ($tradeDate) {
                $q->whereNull('ex_date')->orWhere('ex_date', '<=', $tradeDate);
            })
            ->orderBy('event_uid')->orderBy('revision_number')
            ->get();

        $sets = DB::table('md_adjustment_factor_sets')
            ->where('recorded_at', '<=', $knowledgeCutoff)
            ->orderBy('factor_set_id')
            ->get();
        $setIds = array_map(function ($row) { return (int) $row->factor_set_id; }, $sets->all());
        $factors = $setIds === [] ? collect([]) : DB::table('md_adjustment_factors')
            ->whereIn('factor_set_id', $setIds)
            ->where('effective_from', '<=', $tradeDate)
            ->orderBy('factor_set_id')->orderBy('listing_id')->orderBy('effective_from')
            ->get();

        return [
            'corporate_action_revisions' => array_map(function ($row) { return (array) $row; }, $events->all()),
            'factor_sets' => array_map(function ($row) { return (array) $row; }, $sets->all()),
            'factors' => array_map(function ($row) { return (array) $row; }, $factors->all()),
        ];
    }

    /**
     * Decode the configuration selected by the knowledge cutoff.
     *
     * Reading formula/registry identity from the live configuration here would reintroduce the
     * exact future-state leak that resolveForRun(..., $knowledgeCutoff) prevents. A malformed
     * historical snapshot is therefore a blocking input defect, never permission to fall back to
     * config() and manufacture an as-known identity.
     */
    private function resolvedConfigPayload(array $config): array
    {
        $json = isset($config['resolved_config_json']) ? (string) $config['resolved_config_json'] : '';
        $payload = $json !== '' ? json_decode($json, true) : null;

        if (! is_array($payload)
            || ! isset($payload['resolved_config'])
            || ! is_array($payload['resolved_config'])
            || ! isset($payload['semantic_bindings'])
            || ! is_array($payload['semantic_bindings'])) {
            throw new \RuntimeException(
                'REPLAY_CONFIG_SNAPSHOT_PAYLOAD_INVALID: as-known replay requires the complete '
                .'resolved configuration and semantic bindings recorded in its selected snapshot.'
            );
        }

        return $payload;
    }

    /**
     * The derived reason-registry member of the decoded snapshot, or null when it is absent or malformed.
     *
     * Read only from the payload of the snapshot the cutoff selected. A snapshot issued before the member existed has
     * none and stays that way: nothing is inferred, defaulted or looked up elsewhere.
     *
     * @return array{identity_contract:string,semantic_identity:string}|null
     */
    private function reasonRegistryMember(array $payload): ?array
    {
        $member = $payload['reason_registry'] ?? null;
        if (! is_array($member)
            || array_keys($member) !== ['identity_contract', 'semantic_identity']
            || $member['identity_contract'] !== ReplayV2IdentityProjection::REASON_SCHEMA
            || ! is_string($member['semantic_identity'])
            || preg_match('/^[0-9a-f]{64}$/', $member['semantic_identity']) !== 1) {
            return null;
        }

        return $member;
    }

    private function only(array $row, array $keys): array
    {
        $out = [];
        foreach ($keys as $key) {
            if (array_key_exists($key, $row)) $out[$key] = $row[$key];
        }
        return $out;
    }

    private function hash($value): string
    {
        $value = $this->canonicalize($value);
        $json = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
        if ($json === false) throw new \RuntimeException('REPLAY_BOUND_INPUT_SERIALIZATION_FAILED: bound replay context cannot be serialized.');
        return hash('sha256', $json);
    }

    private function canonicalize($value)
    {
        if (! is_array($value)) return $value;
        $isList = $value === [] || array_keys($value) === range(0, count($value) - 1);
        if (! $isList) ksort($value, SORT_STRING);
        foreach ($value as $key => $child) $value[$key] = $this->canonicalize($child);
        return $value;
    }
}

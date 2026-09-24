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
     * `MD-S050-R0016` Gap B2: `Reason_Codes_Registry.md` (`MD-S085`) defines a stable code
     * vocabulary but no revision, version, effective-time or recorded-time concept at all --
     * confirmed by reading its "Registry rules" in full, which cover only naming, meaning
     * stability and deprecation. There is therefore no historical reason-registry state an
     * as-known replay could legitimately bind as of its knowledge cutoff. The prior
     * `reason_registry_hash` was a hash of two hardcoded constant arrays
     * (`coverage_states`/`replay_states`) plus a config key (`governance.reason_registry_revision`)
     * that has never existed -- the exact same "non-empty is not present" shape `E-MD-B18-A002-069`
     * found in the config identity. This marker replaces that fabricated-looking hash so the gap
     * stays legible, exactly as `ReplayVerificationService::CONFIG_IDENTITY_UNRECORDED` already
     * does for a missing config identity. Value equal to name, by the same convention.
     */
    public const REASON_REGISTRY_IDENTITY_UNAVAILABLE = 'REASON_REGISTRY_IDENTITY_UNAVAILABLE';

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
        $governanceConfig = isset($resolvedConfig['governance']) && is_array($resolvedConfig['governance'])
            ? $resolvedConfig['governance'] : [];
        $reasonIdentity = [
            'coverage_states' => ['PASS', 'FAIL', 'NOT_EVALUATED'],
            'replay_states' => ['PASS', 'FAIL', 'BLOCKED'],
            'build_reason_registry_revision' => (string) (isset($governanceConfig['reason_registry_revision'])
                ? $governanceConfig['reason_registry_revision'] : ''),
        ];

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

        return $context + [
            'temporal_identity_hash' => $this->hash([$universe, $statusContexts]),
            'calendar_status_hash' => $this->hash([$calendar, $statusContexts]),
            'source_observation_manifest_hash' => $sourceManifest['manifest_hash'],
            'canonical_raw_input_hash' => $this->hash($normalizedRowsManifest['rows']),
            'event_factor_hash' => $this->hash($eventsAndFactors),
            'config_snapshot_id' => isset($config['config_snapshot_id']) ? (int) $config['config_snapshot_id'] : null,
            'config_snapshot_hash' => (string) ($config['config_hash'] ?? ''),
            'formula_registry_hash' => $this->hash($formulaIdentity),
            // `MD-S050-R0016` Gap B2: `$reasonIdentity` above is two hardcoded constant arrays plus
            // a config key that has never existed -- a hash of it is not a historical reason-
            // registry identity, however non-empty it looks. See `self::REASON_REGISTRY_IDENTITY_UNAVAILABLE`.
            'reason_registry_hash' => self::REASON_REGISTRY_IDENTITY_UNAVAILABLE,
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

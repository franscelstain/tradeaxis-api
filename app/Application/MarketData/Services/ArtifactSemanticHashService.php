<?php

namespace App\Application\MarketData\Services;

use App\Infrastructure\Persistence\MarketData\TemporalIdentityRepository;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;

/**
 * Versioned semantic identity for the three canonical Market Data artifact families.
 *
 * Local ids are accepted only as navigation keys while loading the retained source row.  They are
 * never copied into the prepared rows or their ordering key.  Every prepared row instead carries
 * the retained roots returned by the shared Security Identity Foundation.
 */
class ArtifactSemanticHashService
{
    public const PROFILE_V2 = 'market-data-semantic-hash/v2';
    public const LEGACY_PROFILE_V1 = 'market-data-row-hash/v1';

    public const BARS_COLUMNS_V2 = [
        'trade_date',
        'issuer_root',
        'instrument_root',
        'listing_root',
        'provider_namespace',
        'provider_symbol',
        'observation_manifest_hash',
        'source_timestamp',
        'acquired_at',
        'open',
        'high',
        'low',
        'close',
        'volume',
        'previous_close',
        'traded_value_idr_actual',
        'trade_count',
        'board_code',
        'session_code',
        'canonicalization_version',
        'price_product_code',
        'quality_state',
        'quality_reasons_json',
        'config_content_hash',
        'source_scale_state',
        'source_scale_assessment_set_hash',
    ];

    public const INDICATORS_COLUMNS_V2 = [
        'trade_date',
        'issuer_root',
        'instrument_root',
        'listing_root',
        'observation_manifest_hash',
        'identity_revision_set_hash',
        'status_revision_set_hash',
        'event_revision_set_hash',
        'factor_decision_set_hash',
        'is_valid',
        'invalid_reason_code',
        'indicator_set_version',
        'sector_code',
        'sector_membership_revision_hash',
        'dv20_idr',
        'atr14_pct',
        'vol_ratio',
        'roc5',
        'roc10',
        'roc20',
        'hh20',
        'll20',
        'ma20',
        'ma50',
        'close_to_hh20_pct',
        'close_to_ll20_pct',
        'range_20_pct',
        'range_position_20_pct',
        'close_vs_ma20_pct',
        'close_vs_ma50_pct',
        'ma20_slope_pct',
        'rs_20_vs_ihsg',
        'sector_roc20',
        'rs_20_vs_sector',
        'sector_rs_20_vs_ihsg',
        'corporate_action_flag',
        'corporate_action_types',
        'trading_status_code',
        'is_suspended',
        'is_uma',
        'event_risk_flag',
        'event_risk_reasons',
        'corporate_action_window_reasons',
        'formula_version',
        'config_content_hash',
        'factor_set_hash',
        'price_product_code',
        'price_product_version',
        'liquidity_formula_version',
        'adv20_traded_value_idr_actual',
        'adv20_close_volume_proxy_idr',
        'atr14',
        'atr_state_ref',
        'null_reasons_json',
    ];

    /** Consumer freshness vocabulary (Downstream_Consumer_Read_Model_Contract_LOCKED.md:55). */
    public const SECTOR_REVISION_SCHEMA = 'market-data-sector-membership-revision/v2';

    /** Sector_Classification_Contract_LOCKED.md: only these classes may establish a membership. */
    private const SECTOR_AUTHORITATIVE_CLASSES = ['EXCHANGE_AUTHORITATIVE', 'OPERATOR_ENTERED'];

    public const FRESHNESS_STATES = ['FRESH', 'STALE', 'DEGRADED', 'NOT_AVAILABLE'];

    public const ELIGIBILITY_COLUMNS_V2 = [
        'trade_date',
        'issuer_root',
        'instrument_root',
        'listing_root',
        'identity_revision_set_hash',
        'status_revision_set_hash',
        'event_revision_set_hash',
        'market_structure_revision_set_hash',
        'eligible',
        'reason_code',
        'universe_membership_state',
        'bar_expectation_state',
        'delivery_state',
        'canonical_quality_state',
        'liquidity_state',
        'temporal_status_state',
        'event_risk_state',
        'source_provenance_state',
        'price_basis_state',
        'contamination_state',
        'indicator_state',
        'freshness_state',
        'eligibility_reasons_json',
        'config_content_hash',
        'read_model_version',
        'market_structure_resolution_state',
    ];

    private $hashes;
    private $identities;

    public function __construct(
        DeterministicHashService $hashes = null,
        TemporalIdentityRepository $identities = null
    ) {
        $this->hashes = $hashes ?: new DeterministicHashService();
        $this->identities = $identities ?: new TemporalIdentityRepository();
    }

    /**
     * Hash one stored artifact using shared-foundation roots and content-addressed governance.
     */
    public function hashStoredArtifact(
        string $artifact,
        string $table,
        string $tradeDate,
        $run,
        $publication,
        array $extraWhere = []
    ): string {
        $this->assertArtifact($artifact);
        $query = DB::table($table)->where('trade_date', $tradeDate);
        foreach ($extraWhere as $field => $value) {
            $query->where($field, $value);
        }
        $rows = array_map(static function ($row) {
            return (array) $row;
        }, $query->get()->all());

        $context = $this->loadSemanticContext($run, $publication);
        $context['config_content_by_snapshot_id'] = $this->loadRowConfigContent($rows);
        if ($artifact === 'indicators') {
            $context['sector_membership_revision_by_local_key'] = $this->loadSectorMembershipRevisions(
                $rows,
                $this->knowledgeCoordinate($run)
            );
        }
        $identities = $this->loadFoundationIdentities(
            $artifact,
            $table,
            $tradeDate,
            $rows,
            $run,
            $extraWhere
        );

        return $this->hashPreparedArtifact($artifact, $rows, $identities, $context);
    }

    /**
     * Pure preparation/hash boundary used by focused equality and sensitivity proof.
     * Identity map keys are "trade_date|local-listing-navigation-id".
     */
    public function hashPreparedArtifact(
        string $artifact,
        array $rows,
        array $identities,
        array $context
    ): string {
        $this->assertArtifact($artifact);
        $this->assertContext($artifact, $context);

        $prepared = [];
        $semanticKeys = [];
        foreach ($rows as $row) {
            $row = (array) $row;
            $identityKey = $this->localIdentityKey($row);
            if (! isset($identities[$identityKey])) {
                throw new \RuntimeException('ARTIFACT_FOUNDATION_IDENTITY_MISSING: '.$artifact.':'.$identityKey);
            }
            $identity = $this->assertIdentity((array) $identities[$identityKey]);
            $this->assertRowConfigContent($row, $context);

            if ($artifact === 'bars') {
                $semantic = $this->prepareBarsRow($row, $identity, $context);
            } elseif ($artifact === 'indicators') {
                $semantic = $this->prepareIndicatorsRow($row, $identity, $context);
            } else {
                $semantic = $this->prepareEligibilityRow($row, $identity, $context);
            }

            $semanticKey = $semantic['trade_date'].'|'.$semantic['listing_root'];
            if (isset($semanticKeys[$semanticKey])) {
                throw new \RuntimeException('ARTIFACT_SEMANTIC_DUPLICATE_KEY: '.$artifact.':'.$semanticKey);
            }
            $semanticKeys[$semanticKey] = true;
            $prepared[] = $semantic;
        }

        return $this->hashes->hashDomainRows(
            self::PROFILE_V2,
            $artifact,
            $prepared,
            $this->columns($artifact)
        );
    }

    /**
     * Freshness is a consumer-visible semantic state, never a timing value. Anything outside the
     * governed vocabulary is NOT_AVAILABLE ("no consumer-safe result"), so an unknown or
     * unevaluated state can never be hashed as FRESH.
     */
    public static function normalizeFreshnessState($value): string
    {
        $state = strtoupper(trim((string) $value));

        return in_array($state, self::FRESHNESS_STATES, true) ? $state : 'NOT_AVAILABLE';
    }

    public function columns(string $artifact): array
    {
        $this->assertArtifact($artifact);
        if ($artifact === 'bars') {
            return self::BARS_COLUMNS_V2;
        }
        if ($artifact === 'indicators') {
            return self::INDICATORS_COLUMNS_V2;
        }

        return self::ELIGIBILITY_COLUMNS_V2;
    }

    private function loadSemanticContext($run, $publication): array
    {
        $publicationId = (int) $this->value($publication, 'publication_id');
        $configSnapshotId = (int) $this->value($publication, 'config_snapshot_id');
        if ($configSnapshotId <= 0) {
            $configSnapshotId = (int) $this->value($run, 'config_snapshot_id');
        }

        $config = DB::table('md_config_snapshots')->where('config_snapshot_id', $configSnapshotId)->first();
        $lineage = DB::table('md_publication_lineage_bindings')
            ->where('publication_id', $publicationId)
            ->first();
        if (!$config || !$lineage) {
            throw new \RuntimeException('ARTIFACT_SEMANTIC_CONTEXT_INCOMPLETE: config/lineage binding missing.');
        }

        $runConfigHash = strtolower(trim((string) $this->value($run, 'config_hash')));
        $configHash = strtolower(trim((string) $config->config_hash));
        if ($runConfigHash !== '' && !hash_equals($runConfigHash, $configHash)) {
            throw new \RuntimeException('ARTIFACT_CONFIG_CONTENT_MISMATCH: run and immutable snapshot differ.');
        }

        // V2 artifacts consume only the V2 nested semantic identities (D-MD-B10-A002-003). The V1
        // nested columns carry allocated keys and are never a fallback.
        if (($lineage->semantic_nested_identity_version ?? null) !== SemanticNestedIdentityService::VERSION) {
            throw new \RuntimeException('ARTIFACT_SEMANTIC_CONTEXT_INCOMPLETE: semantic_nested_identity_version');
        }
        $context = [
            'config_snapshot_id' => $configSnapshotId,
            'config_content_hash' => $configHash,
            // Rows still carry the V1 factor-set binding; it is checked, never hashed.
            'factor_set_row_binding_hash' => strtolower((string) $this->value($publication, 'factor_set_hash')),
            'read_model_version' => (string) ($lineage->read_model_version ?: $this->value($publication, 'read_model_version')),
            // The run's frozen freshness state, normalised exactly as the publication manifest does.
            'freshness_state' => self::normalizeFreshnessState($this->value($run, 'freshness_state')),
        ];
        foreach (SemanticNestedIdentityService::LINEAGE_COLUMNS as $member => $column) {
            $context[$member] = strtolower(trim((string) ($lineage->{$column} ?? '')));
        }

        return $context;
    }

    private function loadFoundationIdentities(
        string $artifact,
        string $table,
        string $tradeDate,
        array $rows,
        $run,
        array $extraWhere
    ): array {
        $coordinate = $this->knowledgeCoordinate($run);
        $barTable = $artifact === 'bars'
            ? $table
            : (substr($table, -8) === '_history' ? 'eod_bars_history' : 'eod_bars');
        $identities = [];

        foreach ($rows as $row) {
            $localKey = $this->localIdentityKey($row);
            if (isset($identities[$localKey])) {
                continue;
            }

            $bar = $artifact === 'bars' ? (object) $row : $this->matchingBar(
                $barTable,
                $tradeDate,
                $row,
                $extraWhere
            );
            $identities[$localKey] = $this->resolveBarIdentity(
                $bar,
                (int) $row['listing_id'],
                $tradeDate,
                $coordinate,
                $artifact,
                $localKey
            );
        }

        return $identities;
    }

    /**
     * Retained foundation roots for listings, resolved through the same candidate bar and provider
     * source row that artifact rows use, so nested semantic sets and artifacts agree on identity.
     * Keys are the local listing ids the caller navigated with; only the values are semantic.
     *
     * @return array<int,array{issuer_root:string,instrument_root:string,listing_root:string}>
     */
    public function resolveListingRoots(
        string $barTable,
        string $tradeDate,
        array $listingIds,
        $run,
        array $extraWhere = []
    ): array {
        $coordinate = $this->knowledgeCoordinate($run);
        $roots = [];
        foreach ($listingIds as $listingId) {
            $listingId = (int) $listingId;
            if (isset($roots[$listingId])) {
                continue;
            }
            $row = ['trade_date' => $tradeDate, 'listing_id' => $listingId];
            $localKey = $this->localIdentityKey($row);
            $identity = $this->assertIdentity($this->resolveBarIdentity(
                $this->matchingBar($barTable, $tradeDate, $row, $extraWhere),
                $listingId,
                $tradeDate,
                $coordinate,
                'nested',
                $localKey
            ));
            $roots[$listingId] = [
                'issuer_root' => (string) $identity['issuer_id'],
                'instrument_root' => (string) $identity['instrument_id'],
                'listing_root' => (string) $identity['listing_id'],
            ];
        }

        return $roots;
    }

    private function resolveBarIdentity($bar, int $listingId, string $tradeDate, string $coordinate, string $artifact, string $localKey): array
    {
        $sourceObservationId = (int) $this->value($bar, 'source_observation_id');
        if ($sourceObservationId <= 0) {
            throw new \RuntimeException('ARTIFACT_SOURCE_OBSERVATION_REQUIRED: '.$artifact.':'.$localKey);
        }

        $source = DB::table('md_source_observation_rows')
            ->where('source_observation_id', $sourceObservationId)
            ->where('listing_id', $listingId)
            ->where('trade_date', $tradeDate)
            ->orderBy('source_observation_row_id')
            ->first();
        if (!$source || trim((string) $source->provider) === '' || trim((string) $source->provider_symbol) === '') {
            throw new \RuntimeException('ARTIFACT_PROVIDER_SOURCE_REQUIRED: '.$artifact.':'.$localKey);
        }

        return $this->identities->resolveFoundationProviderContext(
            (string) $source->provider,
            (string) $source->provider_symbol,
            $coordinate,
            $coordinate
        );
    }

    private function matchingBar(
        string $barTable,
        string $tradeDate,
        array $row,
        array $extraWhere
    ) {
        $query = DB::table($barTable)
            ->where('trade_date', $tradeDate)
            ->where('listing_id', (int) $row['listing_id']);
        if (isset($extraWhere['publication_id']) && substr($barTable, -8) === '_history') {
            $query->where('publication_id', $extraWhere['publication_id']);
        }
        $matches = $query->get();
        if ($matches->count() !== 1) {
            throw new \RuntimeException('ARTIFACT_BAR_LINEAGE_AMBIGUOUS: '.$this->localIdentityKey($row));
        }

        return $matches->first();
    }

    private function prepareBarsRow(array $row, array $identity, array $context): array
    {
        $qualityState = strtoupper(trim((string) ($row['quality_state'] ?? '')));
        $qualityReasons = $row['quality_reasons_json'] ?? null;
        if ($qualityReasons === null) {
            if (!in_array($qualityState, ['VALIDATED', 'ACCEPTED', 'PASS'], true)) {
                throw new \RuntimeException('ARTIFACT_BAR_QUALITY_REASONS_REQUIRED');
            }
            $qualityReasons = [];
        }

        return $this->roots($row, $identity) + [
            'provider_namespace' => $identity['provider_namespace'],
            'provider_symbol' => $identity['provider_symbol'],
            'observation_manifest_hash' => $context['observation_manifest_hash'],
            // The set-level manifest hash cannot say which observation produced which bar, so each
            // bar binds its own consumer-visible provider and acquisition timestamps
            // (Audit_Hash_and_Reproducibility_Contract_LOCKED.md:59, Downstream_Consumer_Read_Model_Contract_LOCKED.md:27).
            'source_timestamp' => $row['source_timestamp'] ?? null,
            'acquired_at' => $row['acquired_at'] ?? null,
            'open' => $row['open'] ?? null,
            'high' => $row['high'] ?? null,
            'low' => $row['low'] ?? null,
            'close' => $row['close'] ?? null,
            'volume' => $row['volume'] ?? null,
            // Provider adj_close is intentionally absent. It remains stored source lineage.
            'previous_close' => $row['previous_close'] ?? null,
            'traded_value_idr_actual' => $row['traded_value_idr_actual'] ?? null,
            'trade_count' => $row['trade_count_actual'] ?? null,
            'board_code' => $row['board_code'] ?? null,
            'session_code' => $row['session_code'] ?? null,
            'canonicalization_version' => $row['canonicalization_version'] ?? null,
            'price_product_code' => $row['price_product_code'] ?? null,
            'quality_state' => $row['quality_state'] ?? null,
            'quality_reasons_json' => $qualityReasons,
            'config_content_hash' => $context['config_content_hash'],
            'source_scale_state' => $row['source_scale_state'] ?? null,
            'source_scale_assessment_set_hash' => $context['source_scale_assessment_set_hash'],
        ];
    }

    private function prepareIndicatorsRow(array $row, array $identity, array $context): array
    {
        $semantic = $this->roots($row, $identity) + [
            'observation_manifest_hash' => $context['observation_manifest_hash'],
            'identity_revision_set_hash' => $context['identity_revision_set_hash'],
            'status_revision_set_hash' => $context['status_revision_set_hash'],
            'event_revision_set_hash' => $context['event_revision_set_hash'],
            'factor_decision_set_hash' => $context['factor_decision_set_hash'],
        ];
        $semantic['sector_membership_revision_hash'] = $this->sectorRevisionMember($row, $context);
        $hasAtr = trim((string) ($row['atr14'] ?? '')) !== '';
        $hasAtrReference = trim((string) ($row['atr_state_ref'] ?? '')) !== '';
        if ($hasAtr && ! $hasAtrReference) {
            throw new \RuntimeException('ARTIFACT_ATR_STATE_REFERENCE_REQUIRED');
        }
        if (! $hasAtr && $hasAtrReference) {
            throw new \RuntimeException('ARTIFACT_ATR_STATE_REFERENCE_WITHOUT_VALUE');
        }
        foreach ([
            'is_valid', 'invalid_reason_code', 'indicator_set_version', 'sector_code',
            'dv20_idr', 'atr14_pct', 'vol_ratio', 'roc5', 'roc10', 'roc20', 'hh20', 'll20',
            'ma20', 'ma50', 'close_to_hh20_pct', 'close_to_ll20_pct', 'range_20_pct',
            'range_position_20_pct', 'close_vs_ma20_pct', 'close_vs_ma50_pct',
            'ma20_slope_pct', 'rs_20_vs_ihsg', 'sector_roc20', 'rs_20_vs_sector',
            'sector_rs_20_vs_ihsg', 'corporate_action_flag', 'corporate_action_types',
            'trading_status_code', 'is_suspended', 'is_uma', 'event_risk_flag',
            'event_risk_reasons', 'corporate_action_window_reasons', 'formula_version',
            'price_product_code', 'price_product_version', 'liquidity_formula_version',
            'adv20_traded_value_idr_actual', 'adv20_close_volume_proxy_idr', 'atr14',
            'atr_state_ref', 'null_reasons_json',
        ] as $field) {
            $semantic[$field] = $row[$field] ?? null;
        }
        $rowFactorHash = strtolower(trim((string) ($row['factor_set_hash'] ?? '')));
        $rowFactorBinding = (string) ($context['factor_set_row_binding_hash'] ?? $context['factor_set_hash']);
        if ($rowFactorHash !== '' && !hash_equals($rowFactorBinding, $rowFactorHash)) {
            throw new \RuntimeException('ARTIFACT_FACTOR_SET_CONTENT_MISMATCH');
        }
        $semantic['config_content_hash'] = $context['config_content_hash'];
        $semantic['factor_set_hash'] = $context['factor_set_hash'];

        return $semantic;
    }

    private function prepareEligibilityRow(array $row, array $identity, array $context): array
    {
        $semantic = $this->roots($row, $identity) + [
            'identity_revision_set_hash' => $context['identity_revision_set_hash'],
            'status_revision_set_hash' => $context['status_revision_set_hash'],
            'event_revision_set_hash' => $context['event_revision_set_hash'],
            'market_structure_revision_set_hash' => $context['market_structure_revision_set_hash'],
        ];
        foreach ([
            'eligible', 'reason_code', 'universe_membership_state', 'bar_expectation_state',
            'delivery_state', 'canonical_quality_state', 'liquidity_state',
            'temporal_status_state', 'event_risk_state', 'source_provenance_state',
            'price_basis_state', 'contamination_state', 'indicator_state',
            'eligibility_reasons_json', 'market_structure_resolution_state',
        ] as $field) {
            $semantic[$field] = $row[$field] ?? null;
        }
        $semantic['freshness_state'] = $context['freshness_state'];
        $semantic['config_content_hash'] = $context['config_content_hash'];
        $semantic['read_model_version'] = $context['read_model_version'];

        return $semantic;
    }

    /**
     * The sector-membership revision a stored indicator row was computed under. The row carries
     * only a local navigation id; the identity bound into the artifact is the revision's content
     * tuple with its knowledge time (D-MD-B10-A002-003 1B, Sector_Classification_Contract_LOCKED.md:
     * "binds the membership revision used"). UNKNOWN sector rows bind no revision.
     *
     * @return array<string,?string> local identity key => revision content hash or null
     */
    private function loadSectorMembershipRevisions(array $rows, string $knowledgeCutoff): array
    {
        $revisions = [];
        foreach ($rows as $row) {
            $row = (array) $row;
            $key = $this->localIdentityKey($row);
            $membershipId = (int) ($row['sector_membership_id'] ?? 0);
            $sectorCode = $this->sectorCode($row['sector_code'] ?? null);
            if ($membershipId <= 0) {
                if ($sectorCode !== null && $sectorCode !== 'UNKNOWN') {
                    throw new \RuntimeException('ARTIFACT_SECTOR_MEMBERSHIP_REVISION_REQUIRED: '.$key);
                }
                $revisions[$key] = null;
                continue;
            }

            $revision = $this->sectorRevisionDocument(
                $membershipId,
                (int) $row['listing_id'],
                $knowledgeCutoff,
                []
            );
            if ($sectorCode !== $revision['content']['sector_code']) {
                throw new \RuntimeException('ARTIFACT_SECTOR_MEMBERSHIP_CODE_MISMATCH: '.$key);
            }
            $revisions[$key] = $this->hashes->hashCanonicalDocument($revision);
        }

        return $revisions;
    }

    private function sectorRevisionDocument(int $membershipId, int $listingId, string $knowledgeCutoff, array $path): array
    {
        if (isset($path[$membershipId]) || count($path) > 64) {
            throw new \RuntimeException('ARTIFACT_SECTOR_MEMBERSHIP_SUPERSESSION_CYCLE');
        }
        $path[$membershipId] = true;

        $membership = DB::table(config('market_data.sectors.membership_table', 'ticker_sector_memberships'))
            ->where('membership_id', $membershipId)
            ->first();
        if (! $membership) {
            throw new \RuntimeException('ARTIFACT_SECTOR_MEMBERSHIP_REVISION_MISSING');
        }
        if ((int) $membership->listing_id !== $listingId) {
            throw new \RuntimeException('ARTIFACT_SECTOR_MEMBERSHIP_LISTING_MISMATCH');
        }
        if (! in_array((string) $membership->source_authority_class, self::SECTOR_AUTHORITATIVE_CLASSES, true)) {
            throw new \RuntimeException('ARTIFACT_SECTOR_MEMBERSHIP_NOT_AUTHORITATIVE');
        }
        $recordedAt = substr(trim((string) $membership->recorded_at), 0, 19);
        if ($recordedAt === '' || $recordedAt > $knowledgeCutoff) {
            throw new \RuntimeException('ARTIFACT_SECTOR_MEMBERSHIP_AFTER_KNOWLEDGE_CUTOFF');
        }

        $classificationSystem = strtoupper(trim((string) $membership->classification_system));
        $sectorCode = $this->sectorCode($membership->sector_code);
        $master = DB::table(config('market_data.sectors.table', 'market_data_sectors'))
            ->where('classification_system', $classificationSystem)
            ->where('sector_code', $sectorCode)
            ->first();
        $indexCode = $master && $master->sector_index_code !== null
            ? strtoupper(trim((string) $master->sector_index_code))
            : null;

        $superseded = null;
        if ($membership->supersedes_membership_id !== null && (int) $membership->supersedes_membership_id > 0) {
            $superseded = $this->hashes->hashCanonicalDocument($this->sectorRevisionDocument(
                (int) $membership->supersedes_membership_id,
                $listingId,
                $knowledgeCutoff,
                $path
            ));
        }

        // No local ids: the instrument is bound by the row's foundation roots, the supersession
        // by the content hash of the revision it replaced.
        return [
            'schema_version' => self::SECTOR_REVISION_SCHEMA,
            'content' => [
                'classification_system' => $classificationSystem,
                'sector_code' => $sectorCode,
                'sector_index_code' => $indexCode === '' ? null : $indexCode,
                'effective_from' => substr(trim((string) $membership->effective_from), 0, 10),
                'effective_to' => $membership->effective_to === null ? null : substr(trim((string) $membership->effective_to), 0, 10),
                'source_name' => trim((string) $membership->source_name),
                'source_ref' => $membership->source_ref === null ? null : trim((string) $membership->source_ref),
                'source_authority_class' => (string) $membership->source_authority_class,
                'recorded_at' => $recordedAt,
                'operator_name' => $membership->operator_name === null ? null : trim((string) $membership->operator_name),
                'reason_code' => $membership->reason_code === null ? null : trim((string) $membership->reason_code),
                'supersedes_revision_hash' => $superseded,
            ],
        ];
    }

    private function sectorRevisionMember(array $row, array $context): ?string
    {
        $key = $this->localIdentityKey($row);
        $revisions = $context['sector_membership_revision_by_local_key'];
        if (! array_key_exists($key, $revisions)) {
            throw new \RuntimeException('ARTIFACT_SECTOR_MEMBERSHIP_REVISION_MISSING: '.$key);
        }
        $revision = $revisions[$key];
        $sectorCode = $this->sectorCode($row['sector_code'] ?? null);
        if ($revision === null) {
            if ($sectorCode !== null && $sectorCode !== 'UNKNOWN') {
                throw new \RuntimeException('ARTIFACT_SECTOR_MEMBERSHIP_REVISION_REQUIRED: '.$key);
            }

            return null;
        }
        if (preg_match('/^[a-f0-9]{64}$/', (string) $revision) !== 1) {
            throw new \RuntimeException('ARTIFACT_SECTOR_MEMBERSHIP_REVISION_INVALID: '.$key);
        }
        if ($sectorCode === null || $sectorCode === 'UNKNOWN') {
            throw new \RuntimeException('ARTIFACT_SECTOR_MEMBERSHIP_CODE_MISMATCH: '.$key);
        }

        return (string) $revision;
    }

    private function sectorCode($value): ?string
    {
        $code = strtoupper(trim((string) $value));

        return $code === '' ? null : $code;
    }

    private function roots(array $row, array $identity): array
    {
        return [
            'trade_date' => (string) ($row['trade_date'] ?? ''),
            'issuer_root' => $identity['issuer_id'],
            'instrument_root' => $identity['instrument_id'],
            'listing_root' => $identity['listing_id'],
        ];
    }

    private function assertContext(string $artifact, array $context): void
    {
        $required = ['config_content_hash'];
        if ($artifact === 'bars') {
            $required = array_merge($required, ['observation_manifest_hash', 'source_scale_assessment_set_hash']);
        } elseif ($artifact === 'indicators') {
            $required = array_merge($required, [
                'observation_manifest_hash', 'identity_revision_set_hash', 'status_revision_set_hash',
                'event_revision_set_hash', 'factor_decision_set_hash', 'factor_set_hash',
            ]);
            if (! is_array($context['sector_membership_revision_by_local_key'] ?? null)) {
                throw new \RuntimeException('ARTIFACT_SEMANTIC_CONTEXT_INCOMPLETE: sector_membership_revision_by_local_key');
            }
        } else {
            $required = array_merge($required, [
                'identity_revision_set_hash', 'status_revision_set_hash', 'event_revision_set_hash',
                'market_structure_revision_set_hash',
            ]);
            if (trim((string) ($context['read_model_version'] ?? '')) === '') {
                throw new \RuntimeException('ARTIFACT_SEMANTIC_CONTEXT_INCOMPLETE: read_model_version');
            }
            if (! in_array((string) ($context['freshness_state'] ?? ''), self::FRESHNESS_STATES, true)) {
                throw new \RuntimeException('ARTIFACT_SEMANTIC_CONTEXT_INCOMPLETE: freshness_state');
            }
        }
        foreach ($required as $field) {
            if (!isset($context[$field]) || preg_match('/^[a-f0-9]{64}$/', strtolower((string) $context[$field])) !== 1) {
                throw new \RuntimeException('ARTIFACT_SEMANTIC_CONTEXT_INCOMPLETE: '.$field);
            }
        }
    }

    private function assertIdentity(array $identity): array
    {
        foreach (['issuer_id', 'instrument_id', 'listing_id', 'provider_namespace', 'provider_symbol'] as $field) {
            if (!isset($identity[$field]) || trim((string) $identity[$field]) === '') {
                throw new \RuntimeException('ARTIFACT_FOUNDATION_IDENTITY_INCOMPLETE: '.$field);
            }
        }

        return $identity;
    }

    private function assertRowConfigContent(array $row, array $context): void
    {
        $rowSnapshotId = (int) ($row['config_snapshot_id'] ?? 0);
        if ($rowSnapshotId <= 0) {
            throw new \RuntimeException('ARTIFACT_CONFIG_CONTENT_MISSING: row snapshot binding required.');
        }
        $rowConfigHash = $context['config_content_by_snapshot_id'][$rowSnapshotId] ?? null;
        if (!$rowConfigHash || !hash_equals($context['config_content_hash'], strtolower((string) $rowConfigHash))) {
            throw new \RuntimeException('ARTIFACT_CONFIG_CONTENT_MISMATCH: row and publication content differ.');
        }
    }

    private function loadRowConfigContent(array $rows): array
    {
        $ids = [];
        foreach ($rows as $row) {
            $row = (array) $row;
            $id = (int) ($row['config_snapshot_id'] ?? 0);
            if ($id > 0) {
                $ids[$id] = true;
            }
        }
        if ($ids === []) {
            return [];
        }

        return DB::table('md_config_snapshots')
            ->whereIn('config_snapshot_id', array_keys($ids))
            ->pluck('config_hash', 'config_snapshot_id')
            ->mapWithKeys(static function ($hash, $id) {
                return [(int) $id => strtolower((string) $hash)];
            })
            ->all();
    }

    private function knowledgeCoordinate($run): string
    {
        $value = null;
        if (is_object($run) && method_exists($run, 'getRawOriginal')) {
            $value = $run->getRawOriginal('knowledge_cutoff_at');
        }
        if ($value === null || trim((string) $value) === '') {
            $value = $this->value($run, 'knowledge_cutoff_at');
        }
        if ($value instanceof DateTimeInterface) {
            $value = $value->format('Y-m-d H:i:s');
        }
        $value = trim((string) $value);
        if ($value === '') {
            throw new \RuntimeException('ARTIFACT_FOUNDATION_KNOWLEDGE_CUTOFF_REQUIRED');
        }

        return $value;
    }

    private function localIdentityKey(array $row): string
    {
        $tradeDate = trim((string) ($row['trade_date'] ?? ''));
        $listingId = (int) ($row['listing_id'] ?? 0);
        if ($tradeDate === '' || $listingId <= 0) {
            throw new \RuntimeException('ARTIFACT_LOCAL_NAVIGATION_KEY_MISSING');
        }

        return $tradeDate.'|'.$listingId;
    }

    private function assertArtifact(string $artifact): void
    {
        if (!in_array($artifact, ['bars', 'indicators', 'eligibility'], true)) {
            throw new \InvalidArgumentException('Unknown semantic artifact domain: '.$artifact);
        }
    }

    private function value($source, string $field)
    {
        if (is_array($source)) {
            return $source[$field] ?? null;
        }
        if (is_object($source)) {
            return $source->{$field} ?? null;
        }

        return null;
    }
}

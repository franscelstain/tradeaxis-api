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

        return [
            'config_snapshot_id' => $configSnapshotId,
            'config_content_hash' => $configHash,
            'observation_manifest_hash' => strtolower((string) $lineage->observation_manifest_hash),
            'identity_revision_set_hash' => strtolower((string) $lineage->identity_revision_set_hash),
            'status_revision_set_hash' => strtolower((string) $lineage->status_revision_set_hash),
            'event_revision_set_hash' => strtolower((string) $lineage->event_revision_set_hash),
            'source_scale_assessment_set_hash' => strtolower((string) $lineage->source_scale_assessment_set_hash),
            'market_structure_revision_set_hash' => strtolower((string) $lineage->market_structure_revision_set_hash),
            'factor_decision_set_hash' => strtolower((string) $lineage->factor_decision_set_hash),
            'factor_set_hash' => strtolower((string) $this->value($publication, 'factor_set_hash')),
            'read_model_version' => (string) ($lineage->read_model_version ?: $this->value($publication, 'read_model_version')),
        ];
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
            $sourceObservationId = (int) $this->value($bar, 'source_observation_id');
            if ($sourceObservationId <= 0) {
                throw new \RuntimeException('ARTIFACT_SOURCE_OBSERVATION_REQUIRED: '.$artifact.':'.$localKey);
            }

            $source = DB::table('md_source_observation_rows')
                ->where('source_observation_id', $sourceObservationId)
                ->where('listing_id', (int) $row['listing_id'])
                ->where('trade_date', $tradeDate)
                ->orderBy('source_observation_row_id')
                ->first();
            if (!$source || trim((string) $source->provider) === '' || trim((string) $source->provider_symbol) === '') {
                throw new \RuntimeException('ARTIFACT_PROVIDER_SOURCE_REQUIRED: '.$artifact.':'.$localKey);
            }

            $identities[$localKey] = $this->identities->resolveFoundationProviderContext(
                (string) $source->provider,
                (string) $source->provider_symbol,
                $coordinate,
                $coordinate
            );
        }

        return $identities;
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
        if ($rowFactorHash !== '' && !hash_equals($context['factor_set_hash'], $rowFactorHash)) {
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
        $semantic['config_content_hash'] = $context['config_content_hash'];
        $semantic['read_model_version'] = $context['read_model_version'];

        return $semantic;
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
        } else {
            $required = array_merge($required, [
                'identity_revision_set_hash', 'status_revision_set_hash', 'event_revision_set_hash',
                'market_structure_revision_set_hash',
            ]);
            if (trim((string) ($context['read_model_version'] ?? '')) === '') {
                throw new \RuntimeException('ARTIFACT_SEMANTIC_CONTEXT_INCOMPLETE: read_model_version');
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

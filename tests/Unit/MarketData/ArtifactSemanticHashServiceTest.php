<?php

use App\Application\MarketData\Services\ArtifactSemanticHashService;
use App\Application\MarketData\Services\DeterministicHashService;
use App\Application\MarketData\Services\MarketDataPipelineService;

class ArtifactSemanticHashServiceTest extends TestCase
{
    private function service(): ArtifactSemanticHashService
    {
        return new ArtifactSemanticHashService(new DeterministicHashService());
    }

    private function hash(string $seed): string
    {
        return hash('sha256', $seed);
    }

    private function context(int $snapshotId = 10, string $configSeed = 'config-a'): array
    {
        $configHash = $this->hash($configSeed);

        return [
            'config_snapshot_id' => $snapshotId,
            'config_content_hash' => $configHash,
            'config_content_by_snapshot_id' => [$snapshotId => $configHash],
            'observation_manifest_hash' => $this->hash('observations'),
            'identity_revision_set_hash' => $this->hash('identity-revisions'),
            'status_revision_set_hash' => $this->hash('status-revisions'),
            'event_revision_set_hash' => $this->hash('event-revisions'),
            'source_scale_assessment_set_hash' => $this->hash('scale-assessments'),
            'market_structure_revision_set_hash' => $this->hash('market-structure'),
            'factor_decision_set_hash' => $this->hash('factor-decisions'),
            'factor_set_hash' => $this->hash('factor-set'),
            'read_model_version' => 'market_data_read_product_v1',
        ];
    }

    private function identity(string $suffix): array
    {
        return [
            'issuer_id' => 'issuer-'.$suffix,
            'instrument_id' => 'instrument-'.$suffix,
            'listing_id' => 'listing-'.$suffix,
            'provider_namespace' => 'YAHOO_FINANCE',
            'provider_symbol' => strtoupper($suffix).'.JK',
        ];
    }

    private function bar(int $tickerId, int $listingId, int $snapshotId, string $date, string $close): array
    {
        return [
            'trade_date' => $date,
            'ticker_id' => $tickerId,
            'listing_id' => $listingId,
            'source_observation_id' => 1000 + $tickerId,
            'open' => '100.0000',
            'high' => '110.0000',
            'low' => '95.0000',
            'close' => $close,
            'volume' => 1000,
            'adj_close' => '999.0000',
            'source' => 'YAHOO_FINANCE',
            'previous_close' => '99.0000',
            'traded_value_idr_actual' => '100000.00',
            'trade_count_actual' => 25,
            'board_code' => 'DEVELOPMENT',
            'session_code' => 'REGULAR',
            'canonicalization_version' => 'eod_canonical_v1',
            'price_product_code' => 'RAW',
            'quality_state' => 'VALIDATED',
            'config_snapshot_id' => $snapshotId,
            'source_scale_state' => 'RAW_CONFIRMED',
            'source_scale_assessment_id' => 7000 + $tickerId,
            'run_id' => 8000 + $tickerId,
            'publication_id' => 9000 + $tickerId,
        ];
    }

    private function indicator(int $tickerId, int $listingId, int $snapshotId, string $date, string $roc20): array
    {
        return [
            'trade_date' => $date,
            'ticker_id' => $tickerId,
            'listing_id' => $listingId,
            'is_valid' => 1,
            'invalid_reason_code' => null,
            'indicator_set_version' => 'weekly_swing_v1',
            'sector_membership_id' => 4100 + $tickerId,
            'sector_code' => 'HEALTHCARE',
            'dv20_idr' => '1000000.00',
            'atr14_pct' => '0.0100000000',
            'vol_ratio' => '1.2500000000',
            'roc5' => '0.0100000000',
            'roc10' => '0.0200000000',
            'roc20' => $roc20,
            'hh20' => '120.0000',
            'll20' => '90.0000',
            'ma20' => '105.0000',
            'ma50' => '101.0000',
            'close_to_hh20_pct' => '-0.0833333333',
            'close_to_ll20_pct' => '0.2222222222',
            'range_20_pct' => '0.3333333333',
            'range_position_20_pct' => '0.6666666667',
            'close_vs_ma20_pct' => '0.0476190476',
            'close_vs_ma50_pct' => '0.0891089109',
            'ma20_slope_pct' => '0.0100000000',
            'rs_20_vs_ihsg' => '0.0300000000',
            'sector_roc20' => '0.0150000000',
            'rs_20_vs_sector' => '0.0250000000',
            'sector_rs_20_vs_ihsg' => '0.0050000000',
            'corporate_action_flag' => 0,
            'corporate_action_types' => 'NONE',
            'trading_status_code' => 'ACTIVE',
            'is_suspended' => 0,
            'is_uma' => 0,
            'event_risk_flag' => 0,
            'event_risk_reasons' => [],
            'corporate_action_window_reasons' => [],
            'formula_version' => 'formula-v1',
            'config_snapshot_id' => $snapshotId,
            'factor_set_id' => 5100 + $tickerId,
            'factor_set_hash' => $this->hash('factor-set'),
            'price_product_code' => 'STRUCTURAL_ADJUSTED',
            'price_product_version' => 'structural_adjusted_v1',
            'liquidity_formula_version' => 'actual_value_v1',
            'adv20_traded_value_idr_actual' => '1000000.00',
            'adv20_close_volume_proxy_idr' => null,
            'atr14' => '1.2345678901',
            'atr_state_ref' => 'atr-chain:retained-state-v1',
            'null_reasons_json' => [],
            'run_id' => 8000 + $tickerId,
            'publication_id' => 9000 + $tickerId,
        ];
    }

    private function eligibility(int $tickerId, int $listingId, int $snapshotId, string $date, int $eligible): array
    {
        return [
            'trade_date' => $date,
            'ticker_id' => $tickerId,
            'listing_id' => $listingId,
            'eligible' => $eligible,
            'reason_code' => $eligible ? 'ELIGIBLE' : 'LIQUIDITY_LOW',
            'universe_membership_state' => 'IN_SCOPE',
            'bar_expectation_state' => 'EXPECTED',
            'delivery_state' => 'DELIVERED',
            'canonical_quality_state' => 'VALIDATED',
            'liquidity_state' => $eligible ? 'PASS' : 'FAIL',
            'temporal_status_state' => 'ACTIVE',
            'trading_status_revision_id' => 6100 + $tickerId,
            'trading_status_source_observation_id' => 6200 + $tickerId,
            'event_risk_state' => 'CLEAR',
            'source_provenance_state' => 'TRACEABLE',
            'price_basis_state' => 'STRUCTURAL_ADJUSTED',
            'contamination_state' => 'CLEAN',
            'indicator_state' => 'VALID',
            'eligibility_reasons_json' => $eligible ? [] : ['LIQUIDITY_LOW'],
            'config_snapshot_id' => $snapshotId,
            'market_structure_resolution_state' => 'RESOLVED',
            'price_band_revision_id' => 7100 + $tickerId,
            'minimum_price_revision_id' => 7200 + $tickerId,
            'tick_size_revision_id' => 7300 + $tickerId,
            'run_id' => 8000 + $tickerId,
            'publication_id' => 9000 + $tickerId,
        ];
    }

    public function test_bars_ignore_allocation_order_operational_ids_and_provider_adj_close(): void
    {
        $date = '2026-09-29';
        $leftRows = [$this->bar(1, 10, 100, $date, '110.0000'), $this->bar(2, 20, 100, $date, '210.0000')];
        $rightRows = [$this->bar(92, 820, 900, $date, '210.0000'), $this->bar(91, 810, 900, $date, '110.0000')];
        $rightRows[0]['adj_close'] = '1.0000';
        $rightRows[1]['adj_close'] = '2.0000';
        $leftContext = $this->context(100);
        $rightContext = $this->context(900);
        $leftIdentities = [$date.'|10' => $this->identity('a'), $date.'|20' => $this->identity('b')];
        $rightIdentities = [$date.'|810' => $this->identity('a'), $date.'|820' => $this->identity('b')];

        $left = $this->service()->hashPreparedArtifact('bars', $leftRows, $leftIdentities, $leftContext);
        $right = $this->service()->hashPreparedArtifact('bars', $rightRows, $rightIdentities, $rightContext);
        $this->assertSame($left, $right);

        $changed = $rightRows;
        $changed[1]['open'] = '100.0100';
        $this->assertNotSame($left, $this->service()->hashPreparedArtifact('bars', $changed, $rightIdentities, $rightContext));
        $changedIdentities = $rightIdentities;
        $changedIdentities[$date.'|810']['listing_id'] = 'listing-other';
        $this->assertNotSame($left, $this->service()->hashPreparedArtifact('bars', $rightRows, $changedIdentities, $rightContext));
    }

    public function test_indicators_ignore_allocation_and_bind_semantic_value_config_content_and_root(): void
    {
        $date = '2026-09-29';
        $leftRows = [$this->indicator(1, 10, 100, $date, '0.0400000000')];
        $rightRows = [$this->indicator(99, 999, 900, $date, '0.0400000000')];
        $leftContext = $this->context(100);
        $rightContext = $this->context(900);
        $leftIdentities = [$date.'|10' => $this->identity('a')];
        $rightIdentities = [$date.'|999' => $this->identity('a')];

        $baseline = $this->service()->hashPreparedArtifact('indicators', $leftRows, $leftIdentities, $leftContext);
        $this->assertSame($baseline, $this->service()->hashPreparedArtifact('indicators', $rightRows, $rightIdentities, $rightContext));

        $semantic = $rightRows;
        $semantic[0]['roc20'] = '0.0500000000';
        $this->assertNotSame($baseline, $this->service()->hashPreparedArtifact('indicators', $semantic, $rightIdentities, $rightContext));
        $differentConfig = $this->context(900, 'config-b');
        $this->assertNotSame($baseline, $this->service()->hashPreparedArtifact('indicators', $rightRows, $rightIdentities, $differentConfig));
    }

    public function test_eligibility_ignores_revision_allocations_and_binds_decision_reasons_and_config(): void
    {
        $date = '2026-09-29';
        $leftRows = [$this->eligibility(1, 10, 100, $date, 1)];
        $rightRows = [$this->eligibility(88, 880, 900, $date, 1)];
        $leftContext = $this->context(100);
        $rightContext = $this->context(900);
        $leftIdentities = [$date.'|10' => $this->identity('a')];
        $rightIdentities = [$date.'|880' => $this->identity('a')];

        $baseline = $this->service()->hashPreparedArtifact('eligibility', $leftRows, $leftIdentities, $leftContext);
        $this->assertSame($baseline, $this->service()->hashPreparedArtifact('eligibility', $rightRows, $rightIdentities, $rightContext));

        $decision = [$this->eligibility(88, 880, 900, $date, 0)];
        $this->assertNotSame($baseline, $this->service()->hashPreparedArtifact('eligibility', $decision, $rightIdentities, $rightContext));
        $differentConfig = $this->context(900, 'config-b');
        $this->assertNotSame($baseline, $this->service()->hashPreparedArtifact('eligibility', $rightRows, $rightIdentities, $differentConfig));
    }

    public function test_nonclean_bar_without_complete_quality_reasons_fails_closed(): void
    {
        $date = '2026-09-29';
        $bar = $this->bar(1, 10, 100, $date, '110.0000');
        $bar['quality_state'] = 'DEGRADED';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('ARTIFACT_BAR_QUALITY_REASONS_REQUIRED');
        $this->service()->hashPreparedArtifact(
            'bars',
            [$bar],
            [$date.'|10' => $this->identity('a')],
            $this->context(100)
        );
    }

    public function test_profile_domain_separation_preserves_legacy_entry_point(): void
    {
        $hashes = new DeterministicHashService();
        $rows = [['trade_date' => '2026-09-29', 'listing_id' => 7, 'close' => '100.0000']];
        $columns = ['trade_date', 'listing_id', 'close'];
        $legacy = $hashes->hashRows($rows, $columns);

        $this->assertSame(hash('sha256', '2026-09-29|7|100.0000'), $legacy);
        $this->assertNotSame($legacy, $hashes->hashDomainRows(ArtifactSemanticHashService::PROFILE_V2, 'bars', $rows, $columns));
        $this->assertNotSame(
            $hashes->hashDomainRows(ArtifactSemanticHashService::PROFILE_V2, 'bars', $rows, $columns),
            $hashes->hashDomainRows(ArtifactSemanticHashService::PROFILE_V2, 'indicators', $rows, $columns)
        );
    }

    public function test_pipeline_dispatches_all_three_artifacts_to_v2_when_profile_is_explicit(): void
    {
        $artifactHashes = new class extends ArtifactSemanticHashService {
            public array $calls = [];
            public function __construct() {}
            public function hashStoredArtifact(
                string $artifact,
                string $table,
                string $tradeDate,
                $run,
                $publication,
                array $extraWhere = []
            ): string {
                $this->calls[] = [$artifact, $table, $tradeDate, $extraWhere];
                return hash('sha256', $artifact);
            }
        };

        $reflection = new ReflectionClass(MarketDataPipelineService::class);
        $pipeline = $reflection->newInstanceWithoutConstructor();
        $property = $reflection->getProperty('artifactSemanticHashes');
        $property->setAccessible(true);
        $property->setValue($pipeline, $artifactHashes);
        $method = $reflection->getMethod('hashArtifactSet');
        $method->setAccessible(true);

        $result = $method->invoke(
            $pipeline,
            (object) ['artifact_hash_profile' => ArtifactSemanticHashService::PROFILE_V2],
            (object) ['publication_id' => 77],
            '2026-09-29',
            true
        );

        $this->assertSame(ArtifactSemanticHashService::PROFILE_V2, $result['artifact_hash_profile']);
        $this->assertSame(['bars', 'indicators', 'eligibility'], array_column($artifactHashes->calls, 0));
        $this->assertSame(
            ['eod_bars_history', 'eod_indicators_history', 'eod_eligibility_history'],
            array_column($artifactHashes->calls, 1)
        );
        $this->assertSame(['publication_id' => 77], $artifactHashes->calls[0][3]);
    }
}

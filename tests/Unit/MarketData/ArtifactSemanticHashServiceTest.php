<?php

use App\Application\MarketData\Services\ArtifactSemanticHashService;
use App\Domain\MarketData\FreshnessState;
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
            'freshness_state' => 'FRESH',
        ];
    }

    /** Loader-resolved sector revision identities for the local listings an indicator fixture uses. */
    private function withSectorRevisions(array $context, array $listingIds, string $date = '2026-09-29', string $seed = 'sector-revision-a'): array
    {
        foreach ($listingIds as $listingId) {
            $context['sector_membership_revision_by_local_key'][$date.'|'.$listingId] = $this->hash($seed);
        }

        return $context;
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
        $leftContext = $this->withSectorRevisions($this->context(100), [10]);
        $rightContext = $this->withSectorRevisions($this->context(900), [999]);
        $leftIdentities = [$date.'|10' => $this->identity('a')];
        $rightIdentities = [$date.'|999' => $this->identity('a')];

        $baseline = $this->service()->hashPreparedArtifact('indicators', $leftRows, $leftIdentities, $leftContext);
        $this->assertSame($baseline, $this->service()->hashPreparedArtifact('indicators', $rightRows, $rightIdentities, $rightContext));

        $semantic = $rightRows;
        $semantic[0]['roc20'] = '0.0500000000';
        $this->assertNotSame($baseline, $this->service()->hashPreparedArtifact('indicators', $semantic, $rightIdentities, $rightContext));
        $differentConfig = $this->withSectorRevisions($this->context(900, 'config-b'), [999]);
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

    // ------------------------------------------------------------------
    // F-MD-B10-A002-005 remediation: G1-G4 members and the negative proof the complete reproof
    // (E-MD-B10-A002-016) found missing. Each test below is red if its member is removed from the
    // production column list or context.
    // ------------------------------------------------------------------

    private function barsHash(array $rows, array $listingIds, array $context = null): string
    {
        $date = '2026-09-29';
        $identities = [];
        foreach ($listingIds as $index => $listingId) {
            $identities[$date.'|'.$listingId] = $this->identity(chr(97 + $index));
        }

        return $this->service()->hashPreparedArtifact('bars', $rows, $identities, $context ?? $this->context(100));
    }

    public function test_g1_each_bar_binds_its_own_source_and_acquisition_timestamps(): void
    {
        $date = '2026-09-29';
        $rows = [$this->bar(1, 10, 100, $date, '110.0000'), $this->bar(2, 20, 100, $date, '210.0000')];
        $rows[0]['source_timestamp'] = '2026-09-29 16:00:00';
        $rows[0]['acquired_at'] = '2026-09-29 17:05:00';
        $rows[1]['source_timestamp'] = '2026-09-29 16:00:00';
        $rows[1]['acquired_at'] = '2026-09-29 17:25:00';
        $baseline = $this->barsHash($rows, [10, 20]);

        // Local allocation of the same governed timestamps changes nothing (the existing
        // allocation test covers ids; this one keeps the timestamps and moves the ids).
        $moved = $rows;
        foreach ($moved as $index => $row) {
            $moved[$index]['ticker_id'] += 50;
            $moved[$index]['source_observation_id'] += 500;
        }
        $this->assertSame($baseline, $this->barsHash($moved, [10, 20]));

        $acquired = $rows;
        $acquired[0]['acquired_at'] = '2026-09-29 17:06:00';
        $this->assertNotSame($baseline, $this->barsHash($acquired, [10, 20]), 'acquired_at');

        $source = $rows;
        $source[1]['source_timestamp'] = '2026-09-29 15:59:00';
        $this->assertNotSame($baseline, $this->barsHash($source, [10, 20]), 'source_timestamp');

        // The set-level observation manifest cannot say which bar owns which timestamp. Swapping
        // the acquisition timestamps of two listings keeps every set-level hash and every value
        // and was UNCHANGED before the member was bound (E-MD-B10-A002-016, G1).
        $swapped = $rows;
        $swapped[0]['acquired_at'] = $rows[1]['acquired_at'];
        $swapped[1]['acquired_at'] = $rows[0]['acquired_at'];
        $this->assertNotSame($baseline, $this->barsHash($swapped, [10, 20]), 'swap');

        $swappedSource = $rows;
        $swappedSource[0]['source_timestamp'] = '2026-09-29 16:30:00';
        $swappedSource[1]['source_timestamp'] = '2026-09-29 16:00:00';
        $this->assertNotSame($baseline, $this->barsHash($swappedSource, [10, 20]), 'source swap');

        $missing = $rows;
        $missing[0]['source_timestamp'] = null;
        $this->assertNotSame($baseline, $this->barsHash($missing, [10, 20]), 'null is a different fact');
    }

    public function test_g1_a_non_canonical_timestamp_is_refused_rather_than_normalised_silently(): void
    {
        $date = '2026-09-29';
        $row = $this->bar(1, 10, 100, $date, '110.0000');
        $row['acquired_at'] = '29/09/2026 17:05';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('HASH_TIMESTAMP_INVALID');
        $this->barsHash([$row], [10]);
    }

    private function eligibilityHash(array $context, array $row = null): string
    {
        $date = '2026-09-29';

        return $this->service()->hashPreparedArtifact(
            'eligibility',
            [$row ?? $this->eligibility(1, 10, 100, $date, 1)],
            [$date.'|10' => $this->identity('a')],
            $context
        );
    }

    public function test_g4_eligibility_binds_the_frozen_freshness_state_and_only_eligibility_does(): void
    {
        $baseline = $this->eligibilityHash($this->context(100));
        foreach (['STALE', 'DEGRADED', 'NOT_AVAILABLE'] as $state) {
            $context = $this->context(100);
            $context['freshness_state'] = $state;
            $this->assertNotSame($baseline, $this->eligibilityHash($context), $state);
        }

        // Freshness is eligibility-owned: the other two artifacts must not follow it.
        $date = '2026-09-29';
        $stale = $this->context(100);
        $stale['freshness_state'] = 'STALE';
        $barRows = [$this->bar(1, 10, 100, $date, '110.0000')];
        $this->assertSame($this->barsHash($barRows, [10]), $this->barsHash($barRows, [10], $stale));
        $indicatorRows = [$this->indicator(1, 10, 100, $date, '0.0400000000')];
        $indicatorIdentities = [$date.'|10' => $this->identity('a')];
        $this->assertSame(
            $this->service()->hashPreparedArtifact('indicators', $indicatorRows, $indicatorIdentities, $this->withSectorRevisions($this->context(100), [10])),
            $this->service()->hashPreparedArtifact('indicators', $indicatorRows, $indicatorIdentities, $this->withSectorRevisions($stale, [10]))
        );
    }

    public function test_g4_an_unevaluated_or_unknown_freshness_can_never_be_hashed_as_fresh(): void
    {
        // Labels outside the governed vocabulary are never FRESH. DEVELOPMENT_NOT_OPERATIONAL is the label of runs
        // created before DOC-CHG-20261005-001: it stays outside the vocabulary so that a sealed publication keeps
        // the identity it was sealed with. It is a historical-compatibility pin, not the state a new
        // pre-activation run gets: new runs are labelled NOT_APPLICABLE (FreshnessState::runLabelFor).
        foreach (['NOT_EVALUATED', 'DEVELOPMENT_NOT_OPERATIONAL', '', null, 'fresh-ish'] as $raw) {
            $this->assertSame('NOT_AVAILABLE', ArtifactSemanticHashService::normalizeFreshnessState($raw), var_export($raw, true));
        }
        $this->assertSame('FRESH', ArtifactSemanticHashService::normalizeFreshnessState(' fresh '));
        $this->assertSame('NOT_APPLICABLE', ArtifactSemanticHashService::normalizeFreshnessState('NOT_APPLICABLE'), 'a governed pre-activation state must not collapse to NOT_AVAILABLE');

        $fresh = $this->context(100);
        $unavailable = $this->context(100);
        $unavailable['freshness_state'] = 'NOT_AVAILABLE';
        $this->assertNotSame($this->eligibilityHash($fresh), $this->eligibilityHash($unavailable));
    }

    public function test_a_pre_activation_freshness_is_its_own_semantic_state_in_the_eligibility_identity(): void
    {
        // MD-S005-R0056: the eligibility row binds the canonical freshness state. NOT_APPLICABLE is a state of its
        // own: it must not hash like NOT_AVAILABLE ("no consumer-safe result"), FRESH, STALE or DEGRADED.
        $hashes = [];
        // Written out, not read from the production constant: a vocabulary that lost a state must fail here.
        foreach (['FRESH', 'STALE', 'DEGRADED', 'NOT_AVAILABLE', 'NOT_APPLICABLE'] as $state) {
            $context = $this->context(100);
            $context['freshness_state'] = $state;
            $hashes[$state] = $this->eligibilityHash($context);
        }
        $this->assertCount(5, array_unique($hashes), 'every governed freshness state must give a distinct eligibility identity');
        $this->assertNotSame($hashes['NOT_APPLICABLE'], $hashes['NOT_AVAILABLE']);
        $this->assertNotSame($hashes['NOT_APPLICABLE'], $hashes['FRESH']);

        // The four existing states keep their identity: the same context hashes as before the correction.
        $this->assertSame($hashes['FRESH'], $this->eligibilityHash($this->context(100)), 'control: the default context is FRESH');
    }

    public function test_a_pre_activation_freshness_moves_the_eligibility_identity_only(): void
    {
        $date = '2026-09-29';
        $notApplicable = $this->context(100);
        $notApplicable['freshness_state'] = 'NOT_APPLICABLE';
        $barRows = [$this->bar(1, 10, 100, $date, '110.0000')];
        $this->assertSame($this->barsHash($barRows, [10]), $this->barsHash($barRows, [10], $notApplicable));
        $indicatorRows = [$this->indicator(1, 10, 100, $date, '0.0400000000')];
        $indicatorIdentities = [$date.'|10' => $this->identity('a')];
        $this->assertSame(
            $this->service()->hashPreparedArtifact('indicators', $indicatorRows, $indicatorIdentities, $this->withSectorRevisions($this->context(100), [10])),
            $this->service()->hashPreparedArtifact('indicators', $indicatorRows, $indicatorIdentities, $this->withSectorRevisions($notApplicable, [10]))
        );
        $this->assertNotSame($this->eligibilityHash($this->context(100)), $this->eligibilityHash($notApplicable));
    }

    public function test_the_context_guard_accepts_every_governed_state_and_still_refuses_an_internal_label(): void
    {
        foreach (['FRESH', 'STALE', 'DEGRADED', 'NOT_AVAILABLE', 'NOT_APPLICABLE'] as $state) {
            $context = $this->context(100);
            $context['freshness_state'] = $state;
            $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $this->eligibilityHash($context), $state);
        }
        foreach (['NOT_EVALUATED', 'DEVELOPMENT_NOT_OPERATIONAL'] as $internal) {
            $context = $this->context(100);
            $context['freshness_state'] = $internal;
            try {
                $this->eligibilityHash($context);
                $this->fail('an internal run label was accepted as a freshness state: '.$internal);
            } catch (RuntimeException $e) {
                $this->assertStringContainsString('ARTIFACT_SEMANTIC_CONTEXT_INCOMPLETE: freshness_state', $e->getMessage());
            }
        }
    }

    public function test_g4_a_context_without_a_freshness_state_fails_closed(): void
    {
        $context = $this->context(100);
        unset($context['freshness_state']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('ARTIFACT_SEMANTIC_CONTEXT_INCOMPLETE: freshness_state');
        $this->eligibilityHash($context);
    }

    public function test_g4_a_context_with_an_ungoverned_freshness_state_fails_closed(): void
    {
        // The loader normalises; a context built any other way must not smuggle a raw run value in.
        $context = $this->context(100);
        $context['freshness_state'] = 'NOT_EVALUATED';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('ARTIFACT_SEMANTIC_CONTEXT_INCOMPLETE: freshness_state');
        $this->eligibilityHash($context);
    }

    private function indicatorsHash(array $context, array $row = null): string
    {
        $date = '2026-09-29';

        return $this->service()->hashPreparedArtifact(
            'indicators',
            [$row ?? $this->indicator(1, 10, 100, $date, '0.0400000000')],
            [$date.'|10' => $this->identity('a')],
            $context
        );
    }

    public function test_g3_the_sector_membership_revision_identity_is_a_semantic_member(): void
    {
        $baseline = $this->indicatorsHash($this->withSectorRevisions($this->context(100), [10]));
        $this->assertSame(
            $baseline,
            $this->indicatorsHash($this->withSectorRevisions($this->context(100), [10])),
            'the same revision is the same identity'
        );
        $this->assertNotSame(
            $baseline,
            $this->indicatorsHash($this->withSectorRevisions($this->context(100), [10], '2026-09-29', 'sector-revision-b')),
            'a different revision of the same sector code is a different fact'
        );

        // Allocation of the row's own navigation id changes nothing; only the revision does.
        $row = $this->indicator(1, 10, 100, '2026-09-29', '0.0400000000');
        $row['sector_membership_id'] = 987654;
        $this->assertSame($baseline, $this->indicatorsHash($this->withSectorRevisions($this->context(100), [10]), $row));
    }

    public function test_g3_a_concrete_sector_without_its_revision_fails_closed_and_unknown_binds_none(): void
    {
        $date = '2026-09-29';
        $context = $this->context(100);
        $context['sector_membership_revision_by_local_key'] = [$date.'|10' => null];
        try {
            $this->indicatorsHash($context);
            $this->fail('A concrete sector without a membership revision was hashed.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('ARTIFACT_SECTOR_MEMBERSHIP_REVISION_REQUIRED', $e->getMessage());
        }

        $missingKey = $this->context(100);
        $missingKey['sector_membership_revision_by_local_key'] = [];
        try {
            $this->indicatorsHash($missingKey);
            $this->fail('A row without a resolved revision entry was hashed.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('ARTIFACT_SECTOR_MEMBERSHIP_REVISION_MISSING', $e->getMessage());
        }

        $malformed = $this->context(100);
        $malformed['sector_membership_revision_by_local_key'] = [$date.'|10' => 'not-a-hash'];
        try {
            $this->indicatorsHash($malformed);
            $this->fail('A malformed revision identity was hashed.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('ARTIFACT_SECTOR_MEMBERSHIP_REVISION_INVALID', $e->getMessage());
        }

        $unknown = $this->indicator(1, 10, 100, $date, '0.0400000000');
        $unknown['sector_code'] = 'UNKNOWN';
        $unknown['sector_membership_id'] = null;
        $none = $this->context(100);
        $none['sector_membership_revision_by_local_key'] = [$date.'|10' => null];
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $this->indicatorsHash($none, $unknown));

        // UNKNOWN must not carry a revision, or an unknown sector could be given an identity.
        $withRevision = $this->withSectorRevisions($this->context(100), [10]);
        try {
            $this->indicatorsHash($withRevision, $unknown);
            $this->fail('An UNKNOWN sector was bound to a revision.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('ARTIFACT_SECTOR_MEMBERSHIP_CODE_MISMATCH', $e->getMessage());
        }
    }

    public function test_g2_the_atr_state_reference_is_a_semantic_member(): void
    {
        $baseline = $this->indicatorsHash($this->withSectorRevisions($this->context(100), [10]));
        $row = $this->indicator(1, 10, 100, '2026-09-29', '0.0400000000');
        $row['atr_state_ref'] = 'atr-state/v1:'.$this->hash('another-chain');
        $this->assertNotSame($baseline, $this->indicatorsHash($this->withSectorRevisions($this->context(100), [10]), $row));

    }

    public function test_g2_an_atr_value_and_its_recursive_state_reference_travel_together(): void
    {
        $context = $this->withSectorRevisions($this->context(100), [10]);

        $valueWithoutReference = $this->indicator(1, 10, 100, '2026-09-29', '0.0400000000');
        $valueWithoutReference['atr_state_ref'] = null;
        $refused = null;
        try {
            $this->indicatorsHash($context, $valueWithoutReference);
        } catch (RuntimeException $e) {
            $refused = $e;
        }
        $this->assertNotNull($refused, 'an ATR value without its chain reference was hashed');
        $this->assertStringContainsString('ARTIFACT_ATR_STATE_REFERENCE_REQUIRED', $refused->getMessage());

        $referenceWithoutValue = $this->indicator(1, 10, 100, '2026-09-29', '0.0400000000');
        $referenceWithoutValue['atr14'] = null;
        $refused = null;
        try {
            $this->indicatorsHash($context, $referenceWithoutValue);
        } catch (RuntimeException $e) {
            $refused = $e;
        }
        $this->assertNotNull($refused, 'a chain reference without an ATR value was hashed');
        $this->assertStringContainsString('ARTIFACT_ATR_STATE_REFERENCE_WITHOUT_VALUE', $refused->getMessage());

        // Neither: a row whose ATR is legitimately NULL carries no reference and hashes.
        $neither = $this->indicator(1, 10, 100, '2026-09-29', '0.0400000000');
        $neither['atr14'] = null;
        $neither['atr_state_ref'] = null;
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $this->indicatorsHash($context, $neither));
    }

    // ---- Negative proof: every context member an affected predicate requires must move its hash.

    public function context_member_provider(): array
    {
        return [
            'R0026/R0031/R0062 bars observation_manifest_hash' => ['bars', 'observation_manifest_hash'],
            'R0026/R0062 indicators observation_manifest_hash' => ['indicators', 'observation_manifest_hash'],
            'R0024 indicators factor_decision_set_hash' => ['indicators', 'factor_decision_set_hash'],
            'R0050 indicators status_revision_set_hash' => ['indicators', 'status_revision_set_hash'],
            'R0050 indicators event_revision_set_hash' => ['indicators', 'event_revision_set_hash'],
            'R0059 eligibility identity_revision_set_hash' => ['eligibility', 'identity_revision_set_hash'],
            'R0059 eligibility status_revision_set_hash' => ['eligibility', 'status_revision_set_hash'],
            'R0059 eligibility event_revision_set_hash' => ['eligibility', 'event_revision_set_hash'],
            'R0059 eligibility market_structure_revision_set_hash' => ['eligibility', 'market_structure_revision_set_hash'],
            'bars source_scale_assessment_set_hash' => ['bars', 'source_scale_assessment_set_hash'],
            'indicators identity_revision_set_hash' => ['indicators', 'identity_revision_set_hash'],
        ];
    }

    private function artifactHashFor(string $artifact, array $context): string
    {
        $date = '2026-09-29';
        if ($artifact === 'bars') {
            return $this->barsHash([$this->bar(1, 10, 100, $date, '110.0000')], [10], $context);
        }
        if ($artifact === 'indicators') {
            return $this->indicatorsHash($this->withSectorRevisions($context, [10]));
        }

        return $this->eligibilityHash($context);
    }

    /**
     * @dataProvider context_member_provider
     */
    public function test_a_changed_nested_context_member_changes_the_artifact_that_must_bind_it(string $artifact, string $member): void
    {
        $baseline = $this->artifactHashFor($artifact, $this->context(100));
        $changed = $this->context(100);
        $changed[$member] = $this->hash('changed-'.$member);

        $this->assertNotSame($baseline, $this->artifactHashFor($artifact, $changed), $artifact.'.'.$member);
        $this->assertSame($baseline, $this->artifactHashFor($artifact, $this->context(100)), 'control: unchanged context reproduces');
    }

    public function test_the_read_model_version_is_an_eligibility_member(): void
    {
        $baseline = $this->eligibilityHash($this->context(100));
        $changed = $this->context(100);
        $changed['read_model_version'] = 'market_data_read_product_v2';

        $this->assertNotSame($baseline, $this->eligibilityHash($changed));
    }

    public function test_the_bar_quality_reason_set_is_a_member_and_is_canonicalised_not_ordered(): void
    {
        $date = '2026-09-29';
        $row = $this->bar(1, 10, 100, $date, '110.0000');
        $row['quality_state'] = 'DEGRADED';
        $row['quality_reasons_json'] = ['BAR_VOLUME_LOW', 'BAR_RANGE_WIDE'];
        $baseline = $this->barsHash([$row], [10]);

        $reordered = $row;
        $reordered['quality_reasons_json'] = ['BAR_RANGE_WIDE', 'BAR_VOLUME_LOW'];
        $this->assertSame($baseline, $this->barsHash([$reordered], [10]), 'reason order is not semantic');

        $changed = $row;
        $changed['quality_reasons_json'] = ['BAR_VOLUME_LOW'];
        $this->assertNotSame($baseline, $this->barsHash([$changed], [10]), 'a dropped reason is a different fact');

        $different = $row;
        $different['quality_reasons_json'] = ['BAR_VOLUME_LOW', 'BAR_GAP_UP'];
        $this->assertNotSame($baseline, $this->barsHash([$different], [10]), 'a different reason is a different fact');
    }

    public function test_row_membership_refuses_two_rows_that_resolve_to_one_semantic_key(): void
    {
        $date = '2026-09-29';
        // Two navigation ids that the foundation resolves to the same listing root.
        $rows = [$this->bar(1, 10, 100, $date, '110.0000'), $this->bar(2, 20, 100, $date, '210.0000')];
        $identities = [$date.'|10' => $this->identity('a'), $date.'|20' => $this->identity('a')];

        try {
            $this->service()->hashPreparedArtifact('bars', $rows, $identities, $this->context(100));
            $this->fail('Two rows for one semantic key were hashed.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('ARTIFACT_SEMANTIC_DUPLICATE_KEY: bars:'.$date.'|listing-a', $e->getMessage());
        }

        // Control: two distinct semantic keys hash, and their order is not semantic.
        $distinct = [$date.'|10' => $this->identity('a'), $date.'|20' => $this->identity('b')];
        $forward = $this->service()->hashPreparedArtifact('bars', $rows, $distinct, $this->context(100));
        $backward = $this->service()->hashPreparedArtifact('bars', array_reverse($rows), $distinct, $this->context(100));
        $this->assertSame($forward, $backward);
        $this->assertNotSame(
            $forward,
            $this->service()->hashPreparedArtifact('bars', [$rows[0]], [$date.'|10' => $distinct[$date.'|10']], $this->context(100)),
            'row membership is part of the identity'
        );
    }

    // ---- Value sensitivity of the clause members the remediated predicates name individually.
    // MD-S005-R0025 (versions), R0038 (quality state), R0046 (ATR value and percentage), R0047
    // (sector code), R0056 (quality, liquidity, temporal status and event-risk states) and R0024
    // (factor-set content hash) each require the member itself to move the hash.

    public function row_member_provider(): array
    {
        return [
            'R0025 bars canonicalization_version' => ['bars', 'canonicalization_version', 'eod_canonical_v2'],
            'R0038 bars quality_state' => ['bars', 'quality_state', 'ACCEPTED'],
            'R0025 indicators formula_version' => ['indicators', 'formula_version', 'formula-v2'],
            'R0025 indicators indicator_set_version' => ['indicators', 'indicator_set_version', 'weekly_swing_v2'],
            'R0025 indicators price_product_version' => ['indicators', 'price_product_version', 'structural_adjusted_v2'],
            'R0025 indicators liquidity_formula_version' => ['indicators', 'liquidity_formula_version', 'actual_value_v2'],
            'bars trade_count' => ['bars', 'trade_count_actual', '26'],
            'R0046 indicators atr14' => ['indicators', 'atr14', '1.2345678902'],
            'R0046 indicators atr14_pct' => ['indicators', 'atr14_pct', '0.0100000001'],
            'R0047 indicators sector_code' => ['indicators', 'sector_code', 'ENERGY'],
            'R0056 eligibility canonical_quality_state' => ['eligibility', 'canonical_quality_state', 'DEGRADED'],
            'R0056 eligibility liquidity_state' => ['eligibility', 'liquidity_state', 'THIN'],
            'R0056 eligibility temporal_status_state' => ['eligibility', 'temporal_status_state', 'SUSPENDED'],
            'R0056 eligibility event_risk_state' => ['eligibility', 'event_risk_state', 'ELEVATED'],
        ];
    }

    /**
     * @dataProvider row_member_provider
     */
    public function test_a_changed_row_member_changes_the_artifact_that_must_bind_it(string $artifact, string $field, string $value): void
    {
        $date = '2026-09-29';
        $context = $this->withSectorRevisions($this->context(100), [10]);
        $rows = [
            'bars' => $this->bar(1, 10, 100, $date, '110.0000'),
            'indicators' => $this->indicator(1, 10, 100, $date, '0.0400000000'),
            'eligibility' => $this->eligibility(1, 10, 100, $date, 1),
        ];
        $hash = function (array $row) use ($artifact, $context, $date): string {
            return $this->service()->hashPreparedArtifact($artifact, [$row], [$date.'|10' => $this->identity('a')], $context);
        };

        $baseline = $hash($rows[$artifact]);
        $this->assertSame($baseline, $hash($rows[$artifact]), 'control: the unchanged row reproduces');
        $changed = $rows[$artifact];
        $this->assertNotSame($value, (string) $changed[$field], 'precondition: the new value differs');
        $changed[$field] = $value;

        $this->assertNotSame($baseline, $hash($changed), $artifact.'.'.$field);
    }

    public function test_the_provider_identity_of_a_bar_is_a_member(): void
    {
        $date = '2026-09-29';
        $rows = [$this->bar(1, 10, 100, $date, '110.0000')];
        $baseline = $this->service()->hashPreparedArtifact('bars', $rows, [$date.'|10' => $this->identity('a')], $this->context(100));

        foreach (['provider_namespace' => 'OTHER_PROVIDER', 'provider_symbol' => 'OTHER.JK'] as $field => $value) {
            $identity = $this->identity('a');
            $identity[$field] = $value;
            $this->assertNotSame(
                $baseline,
                $this->service()->hashPreparedArtifact('bars', $rows, [$date.'|10' => $identity], $this->context(100)),
                $field
            );
        }
        $this->assertSame(
            $baseline,
            $this->service()->hashPreparedArtifact('bars', $rows, [$date.'|10' => $this->identity('a')], $this->context(100)),
            'control'
        );
    }

    public function test_the_factor_set_content_hash_is_an_indicator_member(): void
    {
        $date = '2026-09-29';
        $context = $this->withSectorRevisions($this->context(100), [10]);
        // The row's own factor-set binding is a V1 allocation-bearing column that is checked, never hashed.
        $context['factor_set_row_binding_hash'] = $this->hash('factor-set');
        $baseline = $this->indicatorsHash($context);

        $changed = $context;
        $changed['factor_set_hash'] = $this->hash('another-factor-set');
        $this->assertNotSame($baseline, $this->indicatorsHash($changed));
        $this->assertSame($baseline, $this->indicatorsHash($context), 'control');
    }
}

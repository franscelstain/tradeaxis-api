<?php

use App\Application\MarketData\Services\ReplayV2IdentityProjection;

/**
 * Controls of the V2-profile replay identities of the formula registry, the reason registry and the contamination decisions
 * (D-MD-B18-A002-014, Q4 = A and Q7 = A). Pure: no database, no application state.
 */
class ReplayV2IdentityProjectionTest extends TestCase
{
    private const ROOT_A = '5a000000-0000-4000-8000-000000000003';
    private const ROOT_B = '5a000000-0000-4000-8000-000000000013';

    /** @return array<string,mixed> */
    private function registry(): array
    {
        return [
            'registry_contract' => 'producer_registry_content_v1',
            'semantic_versions' => ['factor_formula_version' => 'structural_factor_product_v2', 'liquidity_formula_version' => 'liquidity_metric_v1', 'raw_product_version' => 'raw_eod_v1'],
            'indicator_set_version' => 'v1',
            'coverage_contract_version' => 'coverage_gate_v1',
            'eligibility_contract_version' => 'eod_eligibility_snapshot_v1',
            'config_registry_revision' => 'platform_config_registry_v2',
            'config_resolver_version' => 'market_data_config_resolver_v1',
            'serialization_version' => 'canonical_json_v1',
            'read_model_version' => 'market_data_read_product_v1',
            'reason_entries' => [
                ['code' => 'A_CODE', 'category' => 'RUN', 'description' => 'first', 'severity' => 'HARD', 'is_active' => true],
                ['code' => 'B_CODE', 'category' => 'DATASET', 'description' => 'second', 'severity' => 'INFO', 'is_active' => false],
            ],
            // build content that must never reach a registry identity
            'executable_build' => ['build_id' => 'sha256:'.str_repeat('a', 64), 'artifact_hash' => str_repeat('b', 64), 'php_version' => '7.4.33', 'php_sapi' => 'cli'],
            'implementation_identities' => ['app/X.php' => str_repeat('c', 64)],
            'reason_registry_hash' => str_repeat('d', 64),
        ];
    }

    // ----------------------------------------------------------------------------------------------- formula registry

    public function test_the_formula_identity_is_a_fixed_document_hash_over_its_own_members(): void
    {
        $expectedPreimage = '{"config_registry_revision":"platform_config_registry_v2","config_resolver_version":"market_data_config_resolver_v1","coverage_contract_version":"coverage_gate_v1","eligibility_contract_version":"eod_eligibility_snapshot_v1","indicator_set_version":"v1","read_model_version":"market_data_read_product_v1","registry_contract":"producer_registry_content_v1","schema_version":"market-data-formula-registry\/v2","semantic_versions":{"factor_formula_version":"structural_factor_product_v2","liquidity_formula_version":"liquidity_metric_v1","raw_product_version":"raw_eod_v1"},"serialization_version":"canonical_json_v1"}';
        $this->assertSame(hash('sha256', str_replace('\/', '/', $expectedPreimage)), ReplayV2IdentityProjection::formulaRegistryIdentity($this->registry()));
    }

    public function test_each_formula_member_moves_the_formula_identity_and_nothing_else_does(): void
    {
        $base = ReplayV2IdentityProjection::formulaRegistryIdentity($this->registry());
        foreach (['registry_contract', 'indicator_set_version', 'coverage_contract_version', 'eligibility_contract_version', 'config_registry_revision', 'config_resolver_version', 'serialization_version', 'read_model_version'] as $member) {
            $r = $this->registry();
            $r[$member] .= '_changed';
            $this->assertNotSame($base, ReplayV2IdentityProjection::formulaRegistryIdentity($r), $member.' must move the formula identity');
        }
        foreach (['factor_formula_version', 'liquidity_formula_version', 'raw_product_version'] as $version) {
            $r = $this->registry();
            $r['semantic_versions'][$version] .= '_changed';
            $this->assertNotSame($base, ReplayV2IdentityProjection::formulaRegistryIdentity($r), $version.' must move the formula identity');
        }
        $r = $this->registry();
        $r['semantic_versions']['added_version'] = 'x';
        $this->assertNotSame($base, ReplayV2IdentityProjection::formulaRegistryIdentity($r), 'an added semantic version must move the formula identity');

        // build content, implementation hashes and the reason registry are other domains
        $r = $this->registry();
        $r['executable_build'] = ['build_id' => 'sha256:'.str_repeat('f', 64), 'php_version' => '8.1.0'];
        $r['implementation_identities'] = ['app/Y.php' => str_repeat('e', 64)];
        $r['reason_entries'][0]['description'] = 'rewritten';
        $r['reason_registry_hash'] = str_repeat('0', 64);
        $this->assertSame($base, ReplayV2IdentityProjection::formulaRegistryIdentity($r), 'build and reason content must not enter the formula identity');
    }

    public function test_an_incomplete_formula_registry_has_no_identity(): void
    {
        $this->assertNull(ReplayV2IdentityProjection::formulaRegistryIdentity(null));
        foreach (['registry_contract', 'indicator_set_version', 'coverage_contract_version', 'eligibility_contract_version', 'config_registry_revision', 'config_resolver_version', 'serialization_version', 'read_model_version', 'semantic_versions'] as $member) {
            $r = $this->registry();
            unset($r[$member]);
            $this->assertNull(ReplayV2IdentityProjection::formulaRegistryIdentity($r), 'a missing '.$member.' must leave the identity unavailable');
            $r = $this->registry();
            $r[$member] = $member === 'semantic_versions' ? [] : '  ';
            $this->assertNull(ReplayV2IdentityProjection::formulaRegistryIdentity($r), 'an empty '.$member.' must leave the identity unavailable');
        }
    }

    // ----------------------------------------------------------------------------------------------- reason registry

    public function test_the_reason_identity_is_independent_of_entry_order_and_of_the_formula_and_the_build(): void
    {
        $base = ReplayV2IdentityProjection::reasonRegistryIdentity($this->registry());
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $base);
        $r = $this->registry();
        $r['reason_entries'] = array_reverse($r['reason_entries']);
        $this->assertSame($base, ReplayV2IdentityProjection::reasonRegistryIdentity($r), 'database or insertion order is not semantic');
        $r = $this->registry();
        $r['semantic_versions']['factor_formula_version'] = 'other';
        $r['read_model_version'] = 'other';
        $r['executable_build'] = ['build_id' => 'sha256:'.str_repeat('f', 64)];
        $this->assertSame($base, ReplayV2IdentityProjection::reasonRegistryIdentity($r), 'formula and build content must not enter the reason identity');
    }

    public function test_every_reason_entry_member_moves_the_reason_identity(): void
    {
        $base = ReplayV2IdentityProjection::reasonRegistryIdentity($this->registry());
        foreach (['code' => 'A_CODE_X', 'category' => 'OTHER', 'description' => 'changed text', 'severity' => 'WARN', 'is_active' => true] as $field => $value) {
            $r = $this->registry();
            $r['reason_entries'][1][$field] = $field === 'is_active' ? ! $r['reason_entries'][1]['is_active'] : $value;
            $this->assertNotSame($base, ReplayV2IdentityProjection::reasonRegistryIdentity($r), $field.' of an entry must move the reason identity');
        }
        $r = $this->registry();
        $r['reason_entries'][] = ['code' => 'C_CODE', 'category' => 'RUN', 'description' => 'third', 'severity' => 'HARD', 'is_active' => true];
        $this->assertNotSame($base, ReplayV2IdentityProjection::reasonRegistryIdentity($r), 'an added entry must move the reason identity');
        $r = $this->registry();
        array_pop($r['reason_entries']);
        $this->assertNotSame($base, ReplayV2IdentityProjection::reasonRegistryIdentity($r), 'a removed entry must move the reason identity');
    }

    public function test_an_unusable_reason_registry_has_no_identity(): void
    {
        $this->assertNull(ReplayV2IdentityProjection::reasonRegistryIdentity(null));
        $r = $this->registry();
        $r['reason_entries'] = [];
        $this->assertNull(ReplayV2IdentityProjection::reasonRegistryIdentity($r), 'an empty registry is not an identity');
        $r = $this->registry();
        $r['reason_entries'] = null;
        $this->assertNull(ReplayV2IdentityProjection::reasonRegistryIdentity($r), 'a historical capture without the registry is unavailable');
        $r = $this->registry();
        $r['reason_entries'][1]['code'] = 'A_CODE';
        $this->assertNull(ReplayV2IdentityProjection::reasonRegistryIdentity($r), 'a duplicate code is ambiguous');
        $r = $this->registry();
        unset($r['reason_entries'][0]['is_active']);
        $this->assertNull(ReplayV2IdentityProjection::reasonRegistryIdentity($r), 'a missing member leaves the identity unavailable');
        $r = $this->registry();
        $r['reason_entries'][0]['is_active'] = 1;
        $this->assertNull(ReplayV2IdentityProjection::reasonRegistryIdentity($r), 'a non-boolean flag is not the captured shape');
    }

    // ----------------------------------------------------------------------------------------------- contamination decisions

    private function contaminationEntry(array $override = []): array
    {
        return $override + ['corporate_action_revision_id' => 41, 'action_type_code' => 'SPLIT', 'verification_state' => 'AUTHORITATIVE_VERIFIED', 'ex_date' => '2026-03-20', 'action_date' => '2026-03-20',
            'anchor_state' => 'VERIFIED_EX_DATE_REVISION', 'depth' => 1, 'breaks_price_continuity' => true, 'breaks_volume_continuity' => true, 'is_unmapped_type' => false];
    }

    private function breakEntry(array $override = []): array
    {
        return $override + ['break_type' => 'SCALE_SHIFT', 'trade_date' => '2026-03-19', 'depth' => 2, 'implied_ratio' => '0.5', 'inferred_ratio' => '0.5', 'match_status' => 'UNMATCHED',
            'matched_action_type' => null, 'candidate_uid' => 'c3a1c1d0-0000-4000-8000-000000000001', 'continuity_verdict' => 'QUARANTINE'];
    }

    public function test_the_empty_contamination_state_has_a_fixed_literal_identity(): void
    {
        $literal = hash('sha256', '{"contamination":[],"price_scale_breaks":[],"schema_version":"market-data-contamination-decision-set/v2"}');
        $this->assertSame($literal, ReplayV2IdentityProjection::contaminationDecisionIdentity([], [], []));
        $this->assertSame($literal, ReplayV2IdentityProjection::contaminationDecisionIdentity([975100 => []], [975100 => []], []), 'a ticker with no decision carries none');
    }

    public function test_the_same_semantic_decisions_under_different_local_ids_have_the_same_identity(): void
    {
        $a = ReplayV2IdentityProjection::contaminationDecisionIdentity(
            [975100 => [$this->contaminationEntry(), $this->contaminationEntry(['action_type_code' => 'DIVIDEND', 'breaks_price_continuity' => false])], 975200 => [$this->contaminationEntry(['depth' => 3])]],
            [975100 => [$this->breakEntry()]],
            [975100 => self::ROOT_A, 975200 => self::ROOT_B]
        );
        // other tickers, other revision ids, other candidate uid, other entry order
        $b = ReplayV2IdentityProjection::contaminationDecisionIdentity(
            [88 => [$this->contaminationEntry(['depth' => 3, 'corporate_action_revision_id' => 7001])], 5 => [
                $this->contaminationEntry(['action_type_code' => 'DIVIDEND', 'breaks_price_continuity' => false, 'corporate_action_revision_id' => 9000]),
                $this->contaminationEntry(['corporate_action_revision_id' => 9001]),
            ]],
            [5 => [$this->breakEntry(['candidate_uid' => 'ffffffff-0000-4000-8000-000000000009'])]],
            [5 => self::ROOT_A, 88 => self::ROOT_B]
        );
        $this->assertNotNull($a);
        $this->assertSame($a, $b, 'local ids and ordering are not semantic');
    }

    public function test_every_semantic_contamination_fact_moves_the_identity(): void
    {
        $roots = [1 => self::ROOT_A];
        $base = ReplayV2IdentityProjection::contaminationDecisionIdentity([1 => [$this->contaminationEntry()]], [1 => [$this->breakEntry()]], $roots);
        foreach (['action_type_code' => 'RIGHTS', 'verification_state' => 'LEGACY_UNVERIFIED', 'ex_date' => '2026-03-21', 'action_date' => '2026-03-21', 'anchor_state' => 'LEGACY_EXPLICIT_EX_DATE',
            'depth' => 2, 'breaks_price_continuity' => false, 'breaks_volume_continuity' => false, 'is_unmapped_type' => true] as $field => $value) {
            $changed = ReplayV2IdentityProjection::contaminationDecisionIdentity([1 => [$this->contaminationEntry([$field => $value])]], [1 => [$this->breakEntry()]], $roots);
            $this->assertNotSame($base, $changed, $field.' of a contamination decision must move the identity');
        }
        foreach (['break_type' => 'GAP', 'trade_date' => '2026-03-18', 'depth' => 5, 'implied_ratio' => '0.25', 'inferred_ratio' => '0.4', 'match_status' => 'MATCHED', 'matched_action_type' => 'SPLIT', 'continuity_verdict' => 'CLEAR'] as $field => $value) {
            $changed = ReplayV2IdentityProjection::contaminationDecisionIdentity([1 => [$this->contaminationEntry()]], [1 => [$this->breakEntry([$field => $value])]], $roots);
            $this->assertNotSame($base, $changed, $field.' of a price-scale-break decision must move the identity');
        }
        $this->assertNotSame($base, ReplayV2IdentityProjection::contaminationDecisionIdentity([1 => [$this->contaminationEntry()]], [], $roots), 'a removed decision must move the identity');
        $this->assertNotSame($base, ReplayV2IdentityProjection::contaminationDecisionIdentity([1 => [$this->contaminationEntry()], 2 => [$this->contaminationEntry()]], [1 => [$this->breakEntry()]], $roots + [2 => self::ROOT_B]), 'a decision of another listing must move the identity');
        $this->assertNotSame($base, ReplayV2IdentityProjection::contaminationDecisionIdentity([1 => [$this->contaminationEntry()]], [1 => [$this->breakEntry()]], [1 => self::ROOT_B]), 'the retained root is semantic');
    }

    public function test_a_decision_that_cannot_be_projected_leaves_the_identity_unavailable(): void
    {
        $roots = [1 => self::ROOT_A];
        // unknown field: a new member of the captured shape needs a decision, it is never silently included or dropped
        $this->assertNull(ReplayV2IdentityProjection::contaminationDecisionIdentity([1 => [$this->contaminationEntry(['ticker_id' => 1])]], [], $roots));
        $this->assertNull(ReplayV2IdentityProjection::contaminationDecisionIdentity([], [1 => [$this->breakEntry(['listing_id' => 3])]], $roots));
        // no retained root for the ticker
        $this->assertNull(ReplayV2IdentityProjection::contaminationDecisionIdentity([2 => [$this->contaminationEntry()]], [], $roots));
        $this->assertNull(ReplayV2IdentityProjection::contaminationDecisionIdentity([1 => [$this->contaminationEntry()]], [], [1 => 'not-a-root']));
        // two tickers resolving to one root are ambiguous
        $this->assertNull(ReplayV2IdentityProjection::contaminationDecisionIdentity([1 => [$this->contaminationEntry()], 2 => [$this->contaminationEntry()]], [], [1 => self::ROOT_A, 2 => self::ROOT_A]));
        // nested or malformed content
        $this->assertNull(ReplayV2IdentityProjection::contaminationDecisionIdentity([1 => [$this->contaminationEntry(['depth' => [1]])]], [], $roots));
        $this->assertNull(ReplayV2IdentityProjection::contaminationDecisionIdentity([1 => 'x'], [], $roots));
        $this->assertNull(ReplayV2IdentityProjection::contaminationDecisionIdentity([1 => [[]]], [], $roots));
    }

    public function test_held_factor_events_are_projected_with_their_hold_reason(): void
    {
        $roots = [1 => self::ROOT_A];
        $held = ['action_type_code' => 'SPLIT', 'action_date' => '2026-03-20', 'breaks_price_continuity' => true, 'breaks_volume_continuity' => true, 'is_unmapped_type' => false, 'factor_hold_reason_code' => 'FACTOR_TERMS_MISSING', 'depth' => 1];
        $base = ReplayV2IdentityProjection::contaminationDecisionIdentity([1 => [$held]], [], $roots);
        $this->assertNotNull($base);
        $this->assertNotSame($base, ReplayV2IdentityProjection::contaminationDecisionIdentity([1 => [['factor_hold_reason_code' => 'FACTOR_HELD_OTHER'] + $held]], [], $roots));
    }
}

<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC72ControlledOptInRuntimeBridgeValidationService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC72ControlledOptInRuntimeBridgeValidationServiceTest extends TestCase
{
    private string $output = 'storage/app/watchlist/backtest/.tmp-c72-test-output.json';
    private array $tmpFiles = [];

    protected function tearDown(): void
    {
        foreach (array_merge([$this->output], $this->tmpFiles) as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        parent::tearDown();
    }

    public function test_c72_runtime_passes_primary_and_backup_when_locked_c71_and_opt_in_match(): void
    {
        $result = $this->runService();

        $this->assertSame('C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_PASSED_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertSame('C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_PASSED_PRIMARY_AND_BACKUP', $result['reason_code']);
        $this->assertTrue($result['controlled_opt_in_runtime_bridge_validation_executed']);
        $this->assertTrue($result['controlled_opt_in_runtime_bridge_validation_allowed']);
        $this->assertTrue($result['controlled_opt_in_runtime_bridge_validation_pass']);
        $this->assertFalse($result['production_ready']);
        $this->assertFalse($result['production_catalog_runtime_wired']);
        $this->assertFalse($result['controlled_opt_in_runtime_bridge_active']);
        $this->assertFalse($result['production_deployment_allowed']);
        $this->assertFalse($result['production_deployment_executed']);
        $this->assertFalse($result['plan_confirm_mutation_allowed']);
        $this->assertFalse($result['plan_confirm_mutated']);
        $this->assertFalse($result['plan_confirm_runtime_reads_activated_catalog']);
        $this->assertFalse($result['live_plan_confirm_rollout_allowed']);
        $this->assertFalse($result['live_plan_confirm_rollout_executed']);
        $this->assertSame('C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION', $result['next_step_recommendation']);
        $this->assertFileExists($this->output);
    }

    public function test_c72_records_required_sections(): void
    {
        $this->runService();
        $run = $this->readOutput();

        foreach ([
            'source_artifact_locks',
            'database_dictionary_read_summary',
            'c71_lock_validation_summary',
            'lineage_validation_summary',
            'candidate_scope_freeze_summary',
            'controlled_opt_in_runtime_bridge_validation_candidate_scorecard',
            'controlled_opt_in_runtime_bridge_validation_decision',
            'runtime_path_inspection_summary',
            'feature_flag_opt_in_kill_switch_runtime_bridge_validation_summary',
            'controlled_bridge_read_execution_summary',
            'plan_confirm_baseline_non_mutation_summary',
            'fallback_behavior_runtime_bridge_validation_summary',
            'bad_month_runtime_bridge_validation_review_results',
            'weak_regime_runtime_bridge_validation_review_results',
            'source_bias_shared_core_runtime_bridge_validation_summary',
            'production_mutation_safety_summary',
            'documentation_governance_summary',
            'c65_cleanup_note_summary',
            'c73_readiness_decision',
            'failure_attribution_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }
    }

    public function test_c72_validates_c71_artifact_hash_and_file_sha1(): void
    {
        $hashResult = $this->execute(['expectedC71Hash' => '0000000000000000000000000000000000000000']);
        $this->assertSame('C72_BLOCKED_C71_ARTIFACT_LOCK_MISMATCH', $hashResult['status']);
        $this->assertFalse($hashResult['c71_hash_match']);

        $shaResult = $this->execute(['expectedC71FileSha1' => '0000000000000000000000000000000000000000']);
        $this->assertSame('C72_BLOCKED_C71_FILE_SHA1_LOCK_MISMATCH', $shaResult['status']);
        $this->assertFalse($shaResult['c71_file_sha1_match']);
    }

    public function test_c72_rejects_missing_c71_artifact(): void
    {
        $result = $this->execute(['c71Artifact' => 'storage/app/watchlist/backtest/missing-c71.json']);

        $this->assertSame('C72_BLOCKED_C71_ARTIFACT_LOCK_MISMATCH', $result['status']);
        $this->assertFalse($result['controlled_opt_in_runtime_bridge_validation_executed']);
    }

    public function test_c72_rejects_c71_status_and_reason_mismatch(): void
    {
        $status = $this->mutateC71AndExecute(function (array $c71): array {
            $c71['status'] = 'BROKEN_STATUS';
            return $c71;
        }, 'c71-status-mismatch');
        $this->assertSame('C72_BLOCKED_C71_STATUS_OR_REASON_MISMATCH', $status['status']);

        $reason = $this->mutateC71AndExecute(function (array $c71): array {
            $c71['reason_code'] = 'BROKEN_REASON';
            return $c71;
        }, 'c71-reason-mismatch');
        $this->assertSame('C72_BLOCKED_C71_STATUS_OR_REASON_MISMATCH', $reason['status']);
    }

    public function test_c72_rejects_c71_shadow_dry_run_not_passed(): void
    {
        $result = $this->mutateC71AndExecute(function (array $c71): array {
            $c71['shadow_read_or_dry_run_runtime_validation_pass'] = false;
            return $c71;
        }, 'c71-shadow-false');

        $this->assertSame('C72_BLOCKED_C71_SHADOW_DRY_RUN_VALIDATION_NOT_PASSED', $result['status']);
    }

    public function test_c72_validates_nested_c72_readiness_path_not_top_level_aliases(): void
    {
        $result = $this->mutateC71AndExecute(function (array $c71): array {
            $c71['candidate_ready_for_c72_count'] = 0;
            $c71['c72_recommendation'] = 'BROKEN_TOP_LEVEL_ALIAS';
            return $c71;
        }, 'c71-top-level-alias');

        $this->assertSame('C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_PASSED_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertTrue($result['c71_lock_validation_summary']['c72_readiness_nested_path_validated']);
        $this->assertFalse($result['c71_lock_validation_summary']['top_level_alias_used_for_c71_source_validation']);
    }

    public function test_c72_rejects_nested_c72_readiness_mismatches(): void
    {
        $count = $this->mutateC71AndExecute(function (array $c71): array {
            $c71['c72_readiness_decision']['candidate_ready_for_c72_count'] = 1;
            return $c71;
        }, 'c71-nested-count');
        $this->assertSame('C72_BLOCKED_C71_C72_READINESS_COUNT_MISMATCH', $count['status']);

        $recommendation = $this->mutateC71AndExecute(function (array $c71): array {
            $c71['c72_readiness_decision']['c72_recommendation'] = 'BROKEN_C72';
            return $c71;
        }, 'c71-nested-recommendation');
        $this->assertSame('C72_BLOCKED_C71_RECOMMENDATION_MISMATCH', $recommendation['status']);
    }

    public function test_c72_rejects_c71_runtime_live_safety_mismatches(): void
    {
        $cases = [
            ['production_catalog_runtime_wired', true, 'C72_BLOCKED_C71_RUNTIME_ALREADY_WIRED'],
            ['shadow_read_runtime_active', true, 'C72_BLOCKED_C71_SHADOW_READ_ALREADY_ACTIVE'],
            ['dry_run_runtime_active', true, 'C72_BLOCKED_C71_DRY_RUN_ALREADY_ACTIVE'],
            ['production_deployment_allowed', true, 'C72_BLOCKED_C71_PRODUCTION_DEPLOYMENT_ALREADY_ALLOWED'],
            ['production_deployment_executed', true, 'C72_BLOCKED_C71_DEPLOYMENT_ALREADY_EXECUTED'],
            ['plan_confirm_mutation_allowed', true, 'C72_BLOCKED_C71_PLAN_CONFIRM_MUTATION_ALREADY_ALLOWED'],
            ['plan_confirm_mutated', true, 'C72_BLOCKED_C71_PLAN_CONFIRM_ALREADY_MUTATED'],
            ['plan_confirm_runtime_reads_activated_catalog', true, 'C72_BLOCKED_C71_PLAN_CONFIRM_ALREADY_READING_ACTIVATED_CATALOG'],
            ['live_plan_confirm_rollout_allowed', true, 'C72_BLOCKED_C71_LIVE_PLAN_CONFIRM_ROLLOUT_ALREADY_ALLOWED'],
            ['live_plan_confirm_rollout_executed', true, 'C72_BLOCKED_C71_LIVE_PLAN_CONFIRM_ROLLOUT_ALREADY_EXECUTED'],
        ];

        foreach ($cases as $case) {
            $result = $this->mutateC71AndExecute(function (array $c71) use ($case): array {
                $c71[$case[0]] = $case[1];
                return $c71;
            }, 'c71-'.$case[0]);
            $this->assertSame($case[2], $result['status'], $case[0]);
        }
    }

    public function test_c72_validates_c71_source_lineage_locks(): void
    {
        $result = $this->mutateC71AndExecute(function (array $c71): array {
            $c71['source_artifact_locks']['c70_hash_match'] = false;
            return $c71;
        }, 'c71-lineage-mismatch');

        $this->assertSame('C72_BLOCKED_LINEAGE_LOCK_MISMATCH', $result['status']);
    }

    public function test_c72_requires_explicit_controlled_opt_in(): void
    {
        $result = $this->execute(['options' => ['controlled_opt_in' => false]]);

        $this->assertSame('C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_REJECTED_OPT_IN_PROOF_MISSING', $result['status']);
        $this->assertFalse($result['controlled_opt_in_runtime_bridge_validation_allowed']);
        $this->assertFalse($result['controlled_opt_in_runtime_bridge_validation_pass']);
    }

    public function test_c72_records_database_dictionary_rule_and_candidate_scope_freeze(): void
    {
        $this->runService();
        $run = $this->readOutput();

        $this->assertTrue($run['database_dictionary_read_summary']['dictionary_rule_acknowledged']);
        $this->assertTrue($run['database_dictionary_read_summary']['dictionary_read_rule_complied']);
        $this->assertFalse($run['database_dictionary_read_summary']['latest_shortcut_used']);
        $this->assertSame('market_benchmark_indicators.roc_20', $run['database_dictionary_read_summary']['market_index_mapping']['market_index_roc20_source']);

        $scope = $run['candidate_scope_freeze_summary'];
        $this->assertSame('C71_LOCKED_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION_DECISION', $scope['candidate_scope_source']);
        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $scope['primary_candidate_code']);
        $this->assertSame(['C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION'], $scope['backup_candidate_codes']);
        $this->assertSame(['C61_A01_B01_WEAK_REGIME_QUALITY_FIRST'], $scope['comparator_only_candidate_codes']);
        $this->assertFalse($scope['new_candidate_created']);
        $this->assertFalse($scope['selection_rule_changed']);
        $this->assertFalse($scope['parameter_changed']);
        $this->assertFalse($scope['oos_result_used_for_new_ranking']);
        $this->assertFalse($scope['a01_promoted']);
    }

    public function test_c72_records_feature_flag_opt_in_kill_switch_bridge_baseline_and_fallback_proofs(): void
    {
        $this->runService();
        $run = $this->readOutput();

        $flags = $run['feature_flag_opt_in_kill_switch_runtime_bridge_validation_summary'];
        $this->assertTrue($flags['default_off_feature_flag_pass']);
        $this->assertSame('watchlist.production_catalog_runtime_bridge_enabled', $flags['feature_flag_name']);
        $this->assertSame('watchlist.production_catalog_controlled_opt_in_runtime_bridge_enabled', $flags['controlled_opt_in_feature_flag_name']);
        $this->assertTrue($flags['explicit_opt_in_required_pass']);
        $this->assertTrue($flags['kill_switch_runtime_bridge_validation_pass']);

        $bridge = $run['controlled_bridge_read_execution_summary'];
        $this->assertTrue($bridge['controlled_bridge_read_execution_proof_pass']);
        $this->assertTrue($bridge['controlled_bridge_executed_in_isolated_validation_path']);
        $this->assertFalse($bridge['controlled_bridge_a01_used_as_runtime_fallback']);

        $baseline = $run['plan_confirm_baseline_non_mutation_summary'];
        $this->assertTrue($baseline['baseline_plan_confirm_hash_unchanged']);
        $this->assertTrue($baseline['plan_confirm_output_non_mutation_pass']);
        $this->assertFalse($baseline['plan_confirm_runtime_reads_activated_catalog']);

        $fallback = $run['fallback_behavior_runtime_bridge_validation_summary'];
        $this->assertTrue($fallback['fallback_behavior_runtime_bridge_validation_pass']);
        $this->assertTrue($fallback['safe_default_if_catalog_missing_pass']);
        $this->assertTrue($fallback['fallback_never_promotes_a01']);
        $this->assertTrue($fallback['fallback_never_uses_a01_as_runtime_candidate']);
    }

    public function test_c72_rejects_feature_flag_kill_switch_bridge_baseline_fallback_and_governance_failures(): void
    {
        $cases = [
            [['feature_flag_default_off' => false], 'C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH'],
            [['controlled_opt_in_feature_flag_default_off' => false], 'C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH'],
            [['kill_switch_available' => false], 'C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH'],
            [['kill_switch_blocks_even_with_explicit_opt_in' => false], 'C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH'],
            [['controlled_bridge_read_execution_proof_pass' => false], 'C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_REJECTED_BRIDGE_READ_PROOF_MISSING'],
            [['plan_confirm_output_changed' => true], 'C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_REJECTED_PLAN_CONFIRM_OUTPUT_CHANGED'],
            [['baseline_plan_confirm_hash_changed' => true], 'C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_REJECTED_BASELINE_HASH_CHANGED'],
            [['fallback_behavior_runtime_bridge_validation_pass' => false], 'C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_REJECTED_FALLBACK_BEHAVIOR_MISSING'],
            [['a01_used_as_runtime_fallback' => true], 'C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_REJECTED_CANDIDATE_SCOPE_MISMATCH'],
            [['bad_month_risk_retained' => false], 'C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_REJECTED_BAD_MONTH_GOVERNANCE'],
            [['weak_regime_risk_retained' => false], 'C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_REJECTED_WEAK_REGIME_GOVERNANCE'],
            [['source_bias_risk_level' => 'HIGH'], 'C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_REJECTED_SOURCE_BIAS_OR_SHARED_CORE'],
            [['shared_core_risk_level' => 'HIGH'], 'C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_REJECTED_SOURCE_BIAS_OR_SHARED_CORE'],
            [['production_deployment_executed' => true], 'C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_REJECTED_PRODUCTION_MUTATION'],
        ];

        foreach ($cases as $case) {
            $result = $this->execute(['options' => array_merge(['controlled_opt_in' => true], $case[0])]);
            $this->assertSame($case[1], $result['status'], json_encode($case[0]));
            $this->assertFalse($result['controlled_opt_in_runtime_bridge_validation_pass']);
        }
    }

    public function test_c72_candidate_scorecard_locks_e02_b01_and_a01_comparator_only(): void
    {
        $this->runService();
        $run = $this->readOutput();
        $scorecard = $this->indexByCode($run['controlled_opt_in_runtime_bridge_validation_candidate_scorecard']);

        $primary = $scorecard['C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE'];
        $this->assertSame('primary_controlled_opt_in_runtime_bridge_candidate', $primary['c72_role']);
        $this->assertTrue($primary['controlled_opt_in_runtime_bridge_validation_pass']);
        $this->assertTrue($primary['candidate_ready_for_c73_controlled_parallel_run_non_mutating_plan_confirm_bridge_validation']);

        $backup = $scorecard['C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION'];
        $this->assertSame('backup_controlled_opt_in_runtime_bridge_candidate', $backup['c72_role']);
        $this->assertTrue($backup['controlled_opt_in_runtime_bridge_validation_pass']);
        $this->assertTrue($backup['candidate_ready_for_c73_controlled_parallel_run_non_mutating_plan_confirm_bridge_validation']);

        $a01 = $scorecard['C61_A01_B01_WEAK_REGIME_QUALITY_FIRST'];
        $this->assertSame('comparator_only', $a01['c72_role']);
        $this->assertFalse($a01['controlled_opt_in_runtime_bridge_validation_pass']);
        $this->assertFalse($a01['candidate_ready_for_c73_controlled_parallel_run_non_mutating_plan_confirm_bridge_validation']);
        $this->assertSame(['C72_A01_REMAINS_COMPARATOR_ONLY'], $a01['failure_reason_codes']);
    }

    public function test_c72_risk_and_source_bias_governance_are_retained(): void
    {
        $this->runService();
        $run = $this->readOutput();

        foreach ($run['bad_month_runtime_bridge_validation_review_results'] as $row) {
            $this->assertTrue($row['documented_bad_month_risk_retained']);
            $this->assertSame('MODERATE', $row['bad_month_risk_level']);
            $this->assertSame('PASS_WITH_DOCUMENTED_RISK', $row['bad_month_governance_decision']);
            $this->assertFalse($row['controlled_opt_in_runtime_bridge_validation_risk_free_claim']);
        }
        foreach ($run['weak_regime_runtime_bridge_validation_review_results'] as $row) {
            $this->assertTrue($row['weak_regime_retained']);
            $this->assertSame('market_down_or_sideways_high_vol', $row['weak_regime_name']);
            $this->assertSame('SUFFICIENT', $row['weak_regime_sample_status']);
        }

        $source = $run['source_bias_shared_core_runtime_bridge_validation_summary'];
        $this->assertTrue($source['source_bias_governance_pass']);
        $this->assertTrue($source['shared_core_governance_pass']);
        $this->assertTrue($source['a01_remains_comparator_only']);
        $this->assertFalse($source['a01_promoted']);
        $this->assertFalse($source['a01_used_as_runtime_fallback']);
    }

    public function test_c72_production_mutation_safety_and_c73_readiness_are_locked(): void
    {
        $this->runService();
        $run = $this->readOutput();
        $safety = $run['production_mutation_safety_summary'];

        $this->assertTrue($safety['controlled_opt_in_runtime_bridge_validation_created']);
        $this->assertTrue($safety['controlled_opt_in_runtime_bridge_validation_allowed']);
        $this->assertTrue($safety['controlled_opt_in_runtime_bridge_validation_pass']);
        $this->assertFalse($safety['production_catalog_runtime_wired']);
        $this->assertFalse($safety['controlled_opt_in_runtime_bridge_active']);
        $this->assertFalse($safety['production_deployment_allowed']);
        $this->assertFalse($safety['production_deployment_executed']);
        $this->assertFalse($safety['plan_confirm_mutation_allowed']);
        $this->assertFalse($safety['plan_confirm_mutated']);
        $this->assertFalse($safety['plan_confirm_runtime_reads_activated_catalog']);
        $this->assertFalse($safety['live_plan_confirm_rollout_allowed']);
        $this->assertFalse($safety['live_plan_confirm_rollout_executed']);
        $this->assertFalse($safety['latest_shortcut_used']);
        $this->assertFalse($safety['max_date_shortcut_used']);
        $this->assertFalse($safety['future_lookup_detected']);
        $this->assertFalse($safety['return_fields_used_for_selection']);

        $c73 = $run['c73_readiness_decision'];
        $this->assertSame(2, $c73['candidate_ready_for_c73_count']);
        $this->assertSame([
            'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE',
            'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION',
        ], $c73['candidate_codes']);
        $this->assertSame('C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION', $c73['c73_recommendation']);
        $this->assertFalse($c73['production_catalog_runtime_wired']);
        $this->assertFalse($c73['plan_confirm_runtime_reads_activated_catalog']);
    }

    public function test_c72_documentation_and_runtime_path_inspection_are_recorded(): void
    {
        $this->runService();
        $run = $this->readOutput();

        $runtime = $run['runtime_path_inspection_summary'];
        $this->assertTrue($runtime['runtime_path_inspection_completed']);
        $this->assertTrue($runtime['controlled_opt_in_runtime_bridge_contract_identified_or_created']);
        $this->assertTrue($runtime['explicit_opt_in_context_contract_identified_or_created']);
        $this->assertFalse($runtime['plan_confirm_runtime_change_executed']);
        $this->assertFalse($runtime['live_runtime_behavior_changed']);

        $docs = $run['documentation_governance_summary'];
        $this->assertTrue($docs['documentation_governance_review_completed']);
        $this->assertTrue($docs['documentation_governance_pass']);
        $this->assertFalse($docs['docs_overclaim_live_deployment']);
        $this->assertFalse($docs['docs_imply_plan_confirm_rollout']);
    }

    private function runService(array $override = []): array
    {
        return $this->execute($override);
    }

    private function execute(array $override = []): array
    {
        $service = new WatchlistBacktestC72ControlledOptInRuntimeBridgeValidationService();
        $options = array_merge(['overwrite' => true, 'controlled_opt_in' => true], (array) ($override['options'] ?? []));

        return $service->execute(
            (string) ($override['c71Artifact'] ?? WatchlistBacktestC72ControlledOptInRuntimeBridgeValidationService::DEFAULT_C71_ARTIFACT),
            (string) ($override['expectedC71Hash'] ?? WatchlistBacktestC72ControlledOptInRuntimeBridgeValidationService::DEFAULT_EXPECTED_C71_HASH),
            (string) ($override['expectedC71FileSha1'] ?? WatchlistBacktestC72ControlledOptInRuntimeBridgeValidationService::DEFAULT_EXPECTED_C71_FILE_SHA1),
            $this->output,
            $options
        );
    }

    private function mutateC71AndExecute(callable $mutator, string $name): array
    {
        [$path, $hash, $sha1] = $this->writeMutatedArtifact(WatchlistBacktestC72ControlledOptInRuntimeBridgeValidationService::DEFAULT_C71_ARTIFACT, $mutator, $name);
        return $this->execute([
            'c71Artifact' => $path,
            'expectedC71Hash' => $hash,
            'expectedC71FileSha1' => $sha1,
        ]);
    }

    private function writeMutatedArtifact(string $source, callable $mutator, string $name): array
    {
        $payload = json_decode((string) file_get_contents($source), true);
        $payload = $mutator(is_array($payload) ? $payload : []);
        $path = 'storage/app/watchlist/backtest/.tmp-'.$name.'.json';
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $this->tmpFiles[] = $path;
        return [$path, (string) ($payload['artifact_hash'] ?? ''), strtoupper((string) sha1_file($path))];
    }

    private function readOutput(): array
    {
        return json_decode((string) file_get_contents($this->output), true);
    }

    private function indexByCode(array $rows): array
    {
        $indexed = [];
        foreach ($rows as $row) {
            $indexed[$row['candidate_code']] = $row;
        }
        return $indexed;
    }
}

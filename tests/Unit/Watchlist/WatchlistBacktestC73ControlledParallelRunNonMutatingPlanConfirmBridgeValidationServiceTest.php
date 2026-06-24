<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC73ControlledParallelRunNonMutatingPlanConfirmBridgeValidationService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC73ControlledParallelRunNonMutatingPlanConfirmBridgeValidationServiceTest extends TestCase
{
    private string $output = 'storage/app/watchlist/backtest/.tmp-c73-test-output.json';
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

    public function test_c73_runtime_passes_primary_and_backup_when_locked_c72_and_parallel_run_opt_in_match(): void
    {
        $result = $this->runService();

        $this->assertSame('C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_PASSED_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertSame('C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_PASSED_PRIMARY_AND_BACKUP', $result['reason_code']);
        $this->assertTrue($result['controlled_parallel_run_non_mutating_plan_confirm_bridge_validation_executed']);
        $this->assertTrue($result['controlled_parallel_run_non_mutating_plan_confirm_bridge_validation_allowed']);
        $this->assertTrue($result['controlled_parallel_run_non_mutating_plan_confirm_bridge_validation_pass']);
        $this->assertFalse($result['production_ready']);
        $this->assertFalse($result['production_catalog_runtime_wired']);
        $this->assertFalse($result['controlled_opt_in_runtime_bridge_active']);
        $this->assertFalse($result['controlled_parallel_run_active']);
        $this->assertFalse($result['production_deployment_allowed']);
        $this->assertFalse($result['production_deployment_executed']);
        $this->assertFalse($result['plan_confirm_mutation_allowed']);
        $this->assertFalse($result['plan_confirm_mutated']);
        $this->assertFalse($result['plan_confirm_runtime_reads_activated_catalog']);
        $this->assertFalse($result['live_plan_confirm_rollout_allowed']);
        $this->assertFalse($result['live_plan_confirm_rollout_executed']);
        $this->assertSame('C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW', $result['next_step_recommendation']);
        $this->assertSame(2, $result['c74_readiness_decision']['candidate_ready_for_c74_count']);
        $this->assertFileExists($this->output);
    }

    public function test_c73_records_required_sections(): void
    {
        $this->runService();
        $run = $this->readOutput();

        foreach ([
            'source_artifact_locks',
            'database_dictionary_read_summary',
            'c72_lock_validation_summary',
            'lineage_validation_summary',
            'candidate_scope_freeze_summary',
            'controlled_parallel_run_candidate_scorecard',
            'controlled_parallel_run_non_mutating_plan_confirm_bridge_validation_decision',
            'runtime_path_inspection_summary',
            'feature_flag_opt_in_kill_switch_parallel_run_validation_summary',
            'controlled_parallel_run_execution_summary',
            'plan_confirm_baseline_non_mutation_summary',
            'parallel_run_delta_governance_summary',
            'fallback_behavior_parallel_run_validation_summary',
            'bad_month_parallel_run_validation_review_results',
            'weak_regime_parallel_run_validation_review_results',
            'source_bias_shared_core_parallel_run_validation_summary',
            'production_mutation_safety_summary',
            'documentation_governance_summary',
            'c65_cleanup_note_summary',
            'c74_readiness_decision',
            'failure_attribution_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }
    }

    public function test_c73_validates_c72_artifact_hash_and_file_sha1(): void
    {
        $fixture = $this->lockedC72Fixture();
        $hashResult = $this->execute(['c72Artifact' => $fixture['path'], 'expectedC72Hash' => '0000000000000000000000000000000000000000', 'expectedC72FileSha1' => $fixture['sha1']]);
        $this->assertSame('C73_BLOCKED_C72_ARTIFACT_LOCK_MISMATCH', $hashResult['status']);
        $this->assertFalse($hashResult['c72_hash_match']);

        $shaResult = $this->execute(['c72Artifact' => $fixture['path'], 'expectedC72Hash' => $fixture['hash'], 'expectedC72FileSha1' => '0000000000000000000000000000000000000000']);
        $this->assertSame('C73_BLOCKED_C72_FILE_SHA1_LOCK_MISMATCH', $shaResult['status']);
        $this->assertFalse($shaResult['c72_file_sha1_match']);
    }

    public function test_c73_rejects_missing_c72_artifact(): void
    {
        $result = $this->execute(['c72Artifact' => 'storage/app/watchlist/backtest/missing-c72.json']);

        $this->assertSame('C73_BLOCKED_C72_ARTIFACT_LOCK_MISMATCH', $result['status']);
        $this->assertFalse($result['controlled_parallel_run_non_mutating_plan_confirm_bridge_validation_executed']);
    }

    public function test_c73_rejects_c72_status_reason_and_validation_mismatches(): void
    {
        $status = $this->mutateC72AndExecute(function (array $c72): array {
            $c72['status'] = 'BROKEN_STATUS';
            return $c72;
        }, 'c72-status-mismatch');
        $this->assertSame('C73_BLOCKED_C72_STATUS_OR_REASON_MISMATCH', $status['status']);

        $reason = $this->mutateC72AndExecute(function (array $c72): array {
            $c72['reason_code'] = 'BROKEN_REASON';
            return $c72;
        }, 'c72-reason-mismatch');
        $this->assertSame('C73_BLOCKED_C72_STATUS_OR_REASON_MISMATCH', $reason['status']);

        $bridge = $this->mutateC72AndExecute(function (array $c72): array {
            $c72['controlled_opt_in_runtime_bridge_validation_pass'] = false;
            return $c72;
        }, 'c72-bridge-false');
        $this->assertSame('C73_BLOCKED_C72_CONTROLLED_OPT_IN_BRIDGE_VALIDATION_NOT_PASSED', $bridge['status']);
    }

    public function test_c73_validates_nested_c73_readiness_path_not_top_level_aliases(): void
    {
        $result = $this->mutateC72AndExecute(function (array $c72): array {
            $c72['candidate_ready_for_c73_count'] = 0;
            $c72['c73_recommendation'] = 'BROKEN_TOP_LEVEL_ALIAS';
            return $c72;
        }, 'c72-top-level-alias');

        $this->assertSame('C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_PASSED_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertTrue($result['c72_lock_validation_summary']['c73_readiness_nested_path_validated']);
        $this->assertFalse($result['c72_lock_validation_summary']['top_level_alias_used_for_c72_source_validation']);
    }

    public function test_c73_rejects_nested_c73_readiness_mismatches(): void
    {
        $count = $this->mutateC72AndExecute(function (array $c72): array {
            $c72['c73_readiness_decision']['candidate_ready_for_c73_count'] = 1;
            return $c72;
        }, 'c72-nested-count');
        $this->assertSame('C73_BLOCKED_C72_C73_READINESS_COUNT_MISMATCH', $count['status']);

        $recommendation = $this->mutateC72AndExecute(function (array $c72): array {
            $c72['c73_readiness_decision']['c73_recommendation'] = 'BROKEN_C73';
            return $c72;
        }, 'c72-nested-recommendation');
        $this->assertSame('C73_BLOCKED_C72_RECOMMENDATION_MISMATCH', $recommendation['status']);
    }

    public function test_c73_rejects_c72_runtime_live_safety_mismatches(): void
    {
        $cases = [
            ['production_catalog_runtime_wired', true, 'C73_BLOCKED_C72_RUNTIME_ALREADY_WIRED'],
            ['controlled_opt_in_runtime_bridge_active', true, 'C73_BLOCKED_C72_CONTROLLED_OPT_IN_BRIDGE_ALREADY_ACTIVE'],
            ['production_deployment_allowed', true, 'C73_BLOCKED_C72_PRODUCTION_DEPLOYMENT_ALREADY_ALLOWED'],
            ['production_deployment_executed', true, 'C73_BLOCKED_C72_DEPLOYMENT_ALREADY_EXECUTED'],
            ['plan_confirm_mutation_allowed', true, 'C73_BLOCKED_C72_PLAN_CONFIRM_MUTATION_ALREADY_ALLOWED'],
            ['plan_confirm_mutated', true, 'C73_BLOCKED_C72_PLAN_CONFIRM_ALREADY_MUTATED'],
            ['plan_confirm_runtime_reads_activated_catalog', true, 'C73_BLOCKED_C72_PLAN_CONFIRM_ALREADY_READING_ACTIVATED_CATALOG'],
            ['live_plan_confirm_rollout_allowed', true, 'C73_BLOCKED_C72_LIVE_PLAN_CONFIRM_ROLLOUT_ALREADY_ALLOWED'],
            ['live_plan_confirm_rollout_executed', true, 'C73_BLOCKED_C72_LIVE_PLAN_CONFIRM_ROLLOUT_ALREADY_EXECUTED'],
        ];

        foreach ($cases as $case) {
            $result = $this->mutateC72AndExecute(function (array $c72) use ($case): array {
                $c72[$case[0]] = $case[1];
                return $c72;
            }, 'c72-'.$case[0]);
            $this->assertSame($case[2], $result['status'], $case[0]);
        }
    }

    public function test_c73_validates_c72_source_lineage_locks_and_candidate_scope(): void
    {
        $lineage = $this->mutateC72AndExecute(function (array $c72): array {
            $c72['source_artifact_locks']['c71_source_lineage_match'] = false;
            return $c72;
        }, 'c72-lineage-mismatch');
        $this->assertSame('C73_BLOCKED_LINEAGE_LOCK_MISMATCH', $lineage['status']);

        $scope = $this->mutateC72AndExecute(function (array $c72): array {
            $c72['candidate_scope_freeze_summary']['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $c72;
        }, 'c72-scope-mismatch');
        $this->assertSame('C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_REJECTED_CANDIDATE_SCOPE_MISMATCH', $scope['status']);
    }

    public function test_c73_requires_explicit_controlled_parallel_run(): void
    {
        $result = $this->execute(['options' => ['controlled_parallel_run' => false]]);

        $this->assertSame('C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_REJECTED_OPT_IN_PROOF_MISSING', $result['status']);
        $this->assertFalse($result['controlled_parallel_run_non_mutating_plan_confirm_bridge_validation_allowed']);
        $this->assertFalse($result['controlled_parallel_run_non_mutating_plan_confirm_bridge_validation_pass']);
    }

    public function test_c73_records_dictionary_candidate_scope_parallel_run_baseline_delta_and_fallback_proofs(): void
    {
        $this->runService();
        $run = $this->readOutput();

        $this->assertTrue($run['database_dictionary_read_summary']['dictionary_rule_acknowledged']);
        $this->assertTrue($run['database_dictionary_read_summary']['dictionary_read_rule_complied']);
        $this->assertFalse($run['database_dictionary_read_summary']['latest_shortcut_used']);
        $this->assertSame('market_benchmark_indicators.roc_20', $run['database_dictionary_read_summary']['market_index_roc20_source']);

        $scope = $run['candidate_scope_freeze_summary'];
        $this->assertSame('C72_LOCKED_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_DECISION', $scope['candidate_scope_source']);
        $this->assertFalse($scope['new_candidate_created']);
        $this->assertFalse($scope['selection_rule_changed']);
        $this->assertFalse($scope['parameter_changed']);
        $this->assertFalse($scope['a01_promoted']);
        $this->assertFalse($scope['a01_used_as_runtime_fallback']);

        $parallel = $run['controlled_parallel_run_execution_summary'];
        $this->assertTrue($parallel['controlled_parallel_run_execution_proof_pass']);
        $this->assertTrue($parallel['parallel_run_comparison_written_to_c73_artifact_only']);
        $this->assertTrue($parallel['parallel_run_delta_is_advisory_only']);
        $this->assertFalse($parallel['controlled_parallel_run_a01_used_as_runtime_fallback']);

        $baseline = $run['plan_confirm_baseline_non_mutation_summary'];
        $this->assertTrue($baseline['baseline_plan_confirm_hash_unchanged']);
        $this->assertTrue($baseline['plan_confirm_output_non_mutation_pass']);
        $this->assertFalse($baseline['plan_confirm_runtime_reads_activated_catalog']);

        $delta = $run['parallel_run_delta_governance_summary'];
        $this->assertTrue($delta['parallel_run_delta_governance_pass']);
        $this->assertTrue($delta['parallel_run_delta_is_advisory_only']);
        $this->assertFalse($delta['parallel_run_delta_used_for_selection']);
        $this->assertFalse($delta['parallel_run_delta_used_for_retuning']);
        $this->assertFalse($delta['parallel_run_delta_allowed_to_auto_deploy']);

        $fallback = $run['fallback_behavior_parallel_run_validation_summary'];
        $this->assertTrue($fallback['fallback_behavior_parallel_run_validation_pass']);
        $this->assertTrue($fallback['safe_default_if_catalog_hash_mismatch_pass']);
        $this->assertTrue($fallback['fallback_never_promotes_a01']);
        $this->assertTrue($fallback['fallback_never_uses_a01_as_runtime_candidate']);
    }

    public function test_c73_rejects_feature_flag_kill_switch_parallel_run_baseline_fallback_delta_and_governance_failures(): void
    {
        $cases = [
            [['feature_flag_default_off' => false], 'C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH'],
            [['controlled_parallel_run_feature_flag_default_off' => false], 'C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH'],
            [['kill_switch_available' => false], 'C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH'],
            [['kill_switch_blocks_even_with_explicit_opt_in' => false], 'C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH'],
            [['controlled_parallel_run_execution_proof_pass' => false], 'C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_REJECTED_PARALLEL_RUN_PROOF_MISSING'],
            [['plan_confirm_output_changed' => true], 'C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_REJECTED_PLAN_CONFIRM_OUTPUT_CHANGED'],
            [['baseline_plan_confirm_hash_changed' => true], 'C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_REJECTED_BASELINE_HASH_CHANGED'],
            [['fallback_behavior_parallel_run_validation_pass' => false], 'C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_REJECTED_FALLBACK_BEHAVIOR_MISSING'],
            [['a01_used_as_runtime_fallback' => true], 'C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_REJECTED_CANDIDATE_SCOPE_MISMATCH'],
            [['parallel_run_delta_used_for_selection' => true], 'C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_REJECTED_DELTA_USED_FOR_SELECTION_OR_RETUNING'],
            [['parallel_run_delta_used_for_retuning' => true], 'C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_REJECTED_DELTA_USED_FOR_SELECTION_OR_RETUNING'],
            [['parallel_run_delta_used_for_plan_confirm_mutation' => true], 'C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_REJECTED_DELTA_USED_FOR_SELECTION_OR_RETUNING'],
            [['bad_month_risk_retained' => false], 'C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_REJECTED_BAD_MONTH_GOVERNANCE'],
            [['weak_regime_risk_retained' => false], 'C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_REJECTED_WEAK_REGIME_GOVERNANCE'],
            [['source_bias_risk_level' => 'HIGH'], 'C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_REJECTED_SOURCE_BIAS_OR_SHARED_CORE'],
            [['shared_core_risk_level' => 'HIGH'], 'C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_REJECTED_SOURCE_BIAS_OR_SHARED_CORE'],
            [['production_deployment_executed' => true], 'C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_REJECTED_PRODUCTION_MUTATION'],
            [['plan_confirm_runtime_reads_activated_catalog' => true], 'C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_REJECTED_PRODUCTION_MUTATION'],
            [['latest_shortcut_used' => true], 'C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_REJECTED_SAFETY_OR_LEAKAGE'],
        ];

        foreach ($cases as $i => $case) {
            $result = $this->execute(['options' => array_merge(['controlled_parallel_run' => true], $case[0])]);
            $this->assertSame($case[1], $result['status'], (string) $i);
        }
    }

    public function test_c73_scorecard_preserves_e02_b01_and_a01_comparator_only(): void
    {
        $this->runService();
        $scorecard = $this->readOutput()['controlled_parallel_run_candidate_scorecard'];

        $this->assertCount(3, $scorecard);
        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $scorecard[0]['candidate_code']);
        $this->assertSame('primary_controlled_parallel_run_non_mutating_plan_confirm_bridge_candidate', $scorecard[0]['c73_role']);
        $this->assertTrue($scorecard[0]['candidate_ready_for_c74_controlled_operator_reviewed_rollout_gate']);

        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $scorecard[1]['candidate_code']);
        $this->assertSame('backup_controlled_parallel_run_non_mutating_plan_confirm_bridge_candidate', $scorecard[1]['c73_role']);
        $this->assertTrue($scorecard[1]['candidate_ready_for_c74_controlled_operator_reviewed_rollout_gate']);

        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $scorecard[2]['candidate_code']);
        $this->assertSame('comparator_only', $scorecard[2]['c73_role']);
        $this->assertFalse($scorecard[2]['candidate_ready_for_c74_controlled_operator_reviewed_rollout_gate']);
        $this->assertContains('C73_A01_REMAINS_COMPARATOR_ONLY', $scorecard[2]['failure_reason_codes']);
    }

    public function test_c73_risk_and_production_safety_are_retained(): void
    {
        $this->runService();
        $run = $this->readOutput();

        $this->assertSame('2026-03', $run['bad_month_parallel_run_validation_review_results'][0]['worst_month']);
        $this->assertSame('PASS_WITH_DOCUMENTED_RISK', $run['bad_month_parallel_run_validation_review_results'][0]['bad_month_governance_decision']);
        $this->assertSame('market_down_or_sideways_high_vol', $run['weak_regime_parallel_run_validation_review_results'][0]['weak_regime']);
        $this->assertFalse($run['weak_regime_parallel_run_validation_review_results'][0]['weak_regime_sample_collapse_detected']);
        $this->assertSame('DOCUMENTED_NOT_HIGH', $run['source_bias_shared_core_parallel_run_validation_summary']['source_bias_risk_level']);
        $this->assertSame('LOW', $run['source_bias_shared_core_parallel_run_validation_summary']['shared_core_risk_level']);
        $this->assertFalse($run['production_mutation_safety_summary']['production_catalog_runtime_wired']);
        $this->assertFalse($run['production_mutation_safety_summary']['plan_confirm_mutated']);
        $this->assertFalse($run['production_mutation_safety_summary']['plan_confirm_runtime_reads_activated_catalog']);
        $this->assertFalse($run['production_mutation_safety_summary']['live_plan_confirm_rollout_executed']);
    }

    private function runService(): array
    {
        return $this->execute();
    }

    private function execute(array $overrides = []): array
    {
        $fixture = $this->lockedC72Fixture();
        $service = new WatchlistBacktestC73ControlledParallelRunNonMutatingPlanConfirmBridgeValidationService();
        return $service->execute(
            $overrides['c72Artifact'] ?? $fixture['path'],
            $overrides['expectedC72Hash'] ?? $fixture['hash'],
            $overrides['expectedC72FileSha1'] ?? $fixture['sha1'],
            $this->output,
            array_merge(['overwrite' => true, 'controlled_parallel_run' => true, 'created_at' => '2026-06-24T00:00:00+00:00'], $overrides['options'] ?? [])
        );
    }

    private function mutateC72AndExecute(callable $mutator, string $name): array
    {
        $fixture = $this->lockedC72Fixture($mutator, $name);
        return $this->execute([
            'c72Artifact' => $fixture['path'],
            'expectedC72Hash' => $fixture['hash'],
            'expectedC72FileSha1' => $fixture['sha1'],
        ]);
    }

    private function lockedC72Fixture(?callable $mutator = null, string $name = 'base'): array
    {
        $path = 'storage/app/watchlist/backtest/.tmp-c72-fixture-'.$name.'.json';
        $payload = json_decode((string) file_get_contents('storage/app/watchlist/backtest/c72-controlled-opt-in-runtime-bridge-validation.json'), true);
        $this->assertIsArray($payload);
        if ($mutator !== null) {
            $payload = $mutator($payload);
        }
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $this->tmpFiles[] = $path;
        return [
            'path' => $path,
            'hash' => (string) ($payload['artifact_hash'] ?? ''),
            'sha1' => strtoupper((string) sha1_file($path)),
        ];
    }

    private function readOutput(): array
    {
        $payload = json_decode((string) file_get_contents($this->output), true);
        $this->assertIsArray($payload);
        return $payload;
    }
}

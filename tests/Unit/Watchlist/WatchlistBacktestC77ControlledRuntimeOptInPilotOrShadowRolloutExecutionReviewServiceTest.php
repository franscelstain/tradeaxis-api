<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC77ControlledRuntimeOptInPilotOrShadowRolloutExecutionReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC77ControlledRuntimeOptInPilotOrShadowRolloutExecutionReviewServiceTest extends TestCase
{
    private string $output = 'storage/app/watchlist/backtest/.tmp-c77-test-output.json';
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

    public function test_c77_runtime_passes_primary_and_backup_when_locked_c76_and_operator_approval_match(): void
    {
        $result = $this->runService();

        $this->assertSame('C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_PASSED_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertSame($result['status'], $result['reason_code']);
        $this->assertTrue($result['controlled_runtime_opt_in_pilot_execution_review_executed']);
        $this->assertTrue($result['controlled_runtime_opt_in_pilot_execution_review_allowed']);
        $this->assertTrue($result['controlled_runtime_opt_in_pilot_execution_review_pass']);
        $this->assertTrue($result['controlled_shadow_rollout_execution_review_executed']);
        $this->assertTrue($result['controlled_shadow_rollout_execution_review_allowed']);
        $this->assertTrue($result['controlled_shadow_rollout_execution_review_pass']);
        $this->assertFalse($result['production_ready']);
        $this->assertFalse($result['production_catalog_runtime_wired']);
        $this->assertFalse($result['controlled_opt_in_runtime_bridge_active']);
        $this->assertFalse($result['controlled_parallel_run_active']);
        $this->assertFalse($result['controlled_rollout_active']);
        $this->assertFalse($result['controlled_pilot_execution_context_persisted_to_live_runtime']);
        $this->assertFalse($result['controlled_shadow_execution_context_persisted_to_live_runtime']);
        $this->assertFalse($result['production_deployment_allowed']);
        $this->assertFalse($result['production_deployment_executed']);
        $this->assertFalse($result['plan_confirm_mutation_allowed']);
        $this->assertFalse($result['plan_confirm_mutated']);
        $this->assertFalse($result['plan_confirm_runtime_reads_activated_catalog']);
        $this->assertFalse($result['live_plan_confirm_rollout_allowed']);
        $this->assertFalse($result['live_plan_confirm_rollout_executed']);
        $this->assertSame('C78_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OBSERVATION_REVIEW', $result['next_step_recommendation']);
        $this->assertSame(2, $result['next_readiness_decision']['candidate_ready_for_controlled_limited_runtime_observation_review_count']);
        $this->assertFileExists($this->output);
    }

    public function test_c77_records_required_sections(): void
    {
        $this->runService();
        $run = $this->readOutput();

        foreach ([
            'source_artifact_locks',
            'database_dictionary_read_summary',
            'c76_lock_validation_summary',
            'lineage_validation_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'controlled_pilot_shadow_execution_candidate_scorecard',
            'controlled_pilot_shadow_execution_decision',
            'controlled_pilot_execution_context_summary',
            'controlled_shadow_execution_context_summary',
            'runtime_readiness_inspection_summary',
            'feature_flag_operator_approval_kill_switch_validation_summary',
            'rollback_and_emergency_disable_review_summary',
            'c76_proof_carry_forward_validation_summary',
            'controlled_pilot_shadow_execution_governance_summary',
            'fallback_behavior_controlled_pilot_shadow_execution_validation_summary',
            'baseline_plan_confirm_non_mutation_summary',
            'bad_month_controlled_pilot_shadow_execution_review_results',
            'weak_regime_controlled_pilot_shadow_execution_review_results',
            'source_bias_shared_core_controlled_pilot_shadow_execution_validation_summary',
            'production_mutation_safety_summary',
            'documentation_governance_summary',
            'c65_cleanup_note_summary',
            'next_readiness_decision',
            'failure_attribution_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }
    }

    public function test_c77_validates_c76_artifact_hash_and_file_sha1(): void
    {
        $fixture = $this->lockedC76Fixture();
        $hashResult = $this->execute(['c76Artifact' => $fixture['path'], 'expectedC76Hash' => '0000000000000000000000000000000000000000', 'expectedC76FileSha1' => $fixture['sha1']]);
        $this->assertSame('C77_BLOCKED_C76_ARTIFACT_LOCK_MISMATCH', $hashResult['status']);
        $this->assertFalse($hashResult['c76_hash_match']);

        $shaResult = $this->execute(['c76Artifact' => $fixture['path'], 'expectedC76Hash' => $fixture['hash'], 'expectedC76FileSha1' => '0000000000000000000000000000000000000000']);
        $this->assertSame('C77_BLOCKED_C76_FILE_SHA1_LOCK_MISMATCH', $shaResult['status']);
        $this->assertFalse($shaResult['c76_file_sha1_match']);
    }

    public function test_c77_rejects_missing_c76_artifact(): void
    {
        $result = $this->execute(['c76Artifact' => 'storage/app/watchlist/backtest/missing-c76.json']);

        $this->assertSame('C77_BLOCKED_C76_ARTIFACT_LOCK_MISMATCH', $result['status']);
        $this->assertFalse($result['controlled_runtime_opt_in_pilot_execution_review_executed']);
    }

    public function test_c77_rejects_c76_status_reason_and_preparation_pass_mismatches(): void
    {
        $status = $this->mutateC76AndExecute(function (array $c76): array {
            $c76['status'] = 'BROKEN_STATUS';
            return $c76;
        }, 'c76-status-mismatch');
        $this->assertSame('C77_BLOCKED_C76_STATUS_OR_REASON_MISMATCH', $status['status']);

        $reason = $this->mutateC76AndExecute(function (array $c76): array {
            $c76['reason_code'] = 'BROKEN_REASON';
            return $c76;
        }, 'c76-reason-mismatch');
        $this->assertSame('C77_BLOCKED_C76_STATUS_OR_REASON_MISMATCH', $reason['status']);

        $pilot = $this->mutateC76AndExecute(function (array $c76): array {
            $c76['controlled_runtime_opt_in_pilot_preparation_review_pass'] = false;
            return $c76;
        }, 'c76-pilot-preparation-false');
        $this->assertSame('C77_BLOCKED_C76_CONTROLLED_PILOT_PREPARATION_NOT_PASSED', $pilot['status']);

        $shadow = $this->mutateC76AndExecute(function (array $c76): array {
            $c76['controlled_shadow_rollout_preparation_review_pass'] = false;
            return $c76;
        }, 'c76-shadow-preparation-false');
        $this->assertSame('C77_BLOCKED_C76_CONTROLLED_SHADOW_PREPARATION_NOT_PASSED', $shadow['status']);
    }

    public function test_c77_validates_nested_c77_readiness_path_not_top_level_aliases(): void
    {
        $result = $this->mutateC76AndExecute(function (array $c76): array {
            $c76['candidate_ready_for_controlled_runtime_opt_in_pilot_or_shadow_rollout_execution_review_count'] = 0;
            $c76['next_recommendation'] = 'BROKEN_TOP_LEVEL_ALIAS';
            return $c76;
        }, 'c76-top-level-alias');

        $this->assertSame('C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_PASSED_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertTrue($result['c76_lock_validation_summary']['c77_readiness_nested_path_validated']);
        $this->assertFalse($result['c76_lock_validation_summary']['top_level_alias_used_for_c76_source_validation']);
    }

    public function test_c77_rejects_c76_nested_readiness_mismatches(): void
    {
        $count = $this->mutateC76AndExecute(function (array $c76): array {
            $c76['next_readiness_decision']['candidate_ready_for_controlled_runtime_opt_in_pilot_or_shadow_rollout_execution_review_count'] = 1;
            return $c76;
        }, 'c76-c77-count');
        $this->assertSame('C77_BLOCKED_C76_C77_READINESS_COUNT_MISMATCH', $count['status']);

        $recommendation = $this->mutateC76AndExecute(function (array $c76): array {
            $c76['next_readiness_decision']['next_recommendation'] = 'BROKEN_C77';
            return $c76;
        }, 'c76-c77-recommendation');
        $this->assertSame('C77_BLOCKED_C76_RECOMMENDATION_MISMATCH', $recommendation['status']);
    }

    public function test_c77_rejects_c76_runtime_live_safety_mismatches(): void
    {
        $cases = [
            ['production_catalog_runtime_wired', true, 'C77_BLOCKED_C76_RUNTIME_ALREADY_WIRED'],
            ['controlled_opt_in_runtime_bridge_active', true, 'C77_BLOCKED_C76_CONTROLLED_OPT_IN_BRIDGE_ALREADY_ACTIVE'],
            ['controlled_parallel_run_active', true, 'C77_BLOCKED_C76_CONTROLLED_PARALLEL_RUN_ALREADY_ACTIVE'],
            ['controlled_rollout_active', true, 'C77_BLOCKED_C76_CONTROLLED_ROLLOUT_ALREADY_ACTIVE'],
            ['controlled_pilot_context_persisted_to_live_runtime', true, 'C77_BLOCKED_C76_CONTROLLED_PILOT_CONTEXT_ALREADY_PERSISTED_TO_LIVE_RUNTIME'],
            ['controlled_shadow_context_persisted_to_live_runtime', true, 'C77_BLOCKED_C76_CONTROLLED_SHADOW_CONTEXT_ALREADY_PERSISTED_TO_LIVE_RUNTIME'],
            ['production_deployment_allowed', true, 'C77_BLOCKED_C76_PRODUCTION_DEPLOYMENT_ALREADY_ALLOWED'],
            ['production_deployment_executed', true, 'C77_BLOCKED_C76_DEPLOYMENT_ALREADY_EXECUTED'],
            ['plan_confirm_mutation_allowed', true, 'C77_BLOCKED_C76_PLAN_CONFIRM_MUTATION_ALREADY_ALLOWED'],
            ['plan_confirm_mutated', true, 'C77_BLOCKED_C76_PLAN_CONFIRM_ALREADY_MUTATED'],
            ['plan_confirm_runtime_reads_activated_catalog', true, 'C77_BLOCKED_C76_PLAN_CONFIRM_ALREADY_READING_ACTIVATED_CATALOG'],
            ['live_plan_confirm_rollout_allowed', true, 'C77_BLOCKED_C76_LIVE_PLAN_CONFIRM_ROLLOUT_ALREADY_ALLOWED'],
            ['live_plan_confirm_rollout_executed', true, 'C77_BLOCKED_C76_LIVE_PLAN_CONFIRM_ROLLOUT_ALREADY_EXECUTED'],
        ];

        foreach ($cases as $case) {
            $result = $this->mutateC76AndExecute(function (array $c76) use ($case): array {
                $c76[$case[0]] = $case[1];
                return $c76;
            }, 'c76-'.$case[0]);
            $this->assertSame($case[2], $result['status'], $case[0]);
        }
    }

    public function test_c77_validates_lineage_and_candidate_scope(): void
    {
        $lineage = $this->mutateC76AndExecute(function (array $c76): array {
            $c76['source_artifact_locks']['c75_source_lineage_match'] = false;
            return $c76;
        }, 'c76-lineage-mismatch');
        $this->assertSame('C77_BLOCKED_LINEAGE_LOCK_MISMATCH', $lineage['status']);

        $scope = $this->mutateC76AndExecute(function (array $c76): array {
            $c76['candidate_scope_freeze_summary']['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $c76;
        }, 'c76-scope-mismatch');
        $this->assertSame('C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $scope['status']);
    }

    public function test_c77_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->execute(['operatorApproved' => false]);
        $this->assertSame('C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingOperator['status']);

        $missingReference = $this->execute(['approvalReference' => '']);
        $this->assertSame('C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingReference['status']);
    }

    public function test_c77_rejects_controlled_gate_failures(): void
    {
        $cases = [
            [['feature_flag_default_off' => false], 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH'],
            [['controlled_pilot_feature_flag_default_off' => false], 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH'],
            [['controlled_shadow_feature_flag_default_off' => false], 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH'],
            [['controlled_pilot_execution_context_validation_pass' => false], 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_PILOT_EXECUTION_CONTEXT_MISSING'],
            [['controlled_shadow_execution_context_validation_pass' => false], 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_SHADOW_EXECUTION_CONTEXT_MISSING'],
            [['rollback_plan_defined' => false], 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_ROLLBACK_PLAN_MISSING'],
            [['emergency_disable_path_defined' => false], 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_EMERGENCY_DISABLE_MISSING'],
            [['baseline_plan_confirm_hash_changed' => true], 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_BASELINE_HASH_CHANGED'],
            [['plan_confirm_output_changed' => true], 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_PLAN_CONFIRM_OUTPUT_CHANGED'],
            [['a01_used_as_runtime_fallback' => true], 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH'],
            [['pilot_execution_used_for_selection' => true], 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_DELTA_USED_FOR_SELECTION_OR_RETUNING'],
            [['shadow_execution_used_for_retuning' => true], 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_DELTA_USED_FOR_SELECTION_OR_RETUNING'],
            [['controlled_pilot_execution_context_persisted_to_live_runtime' => true], 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_DEFAULT_PATH_MUTATION'],
            [['production_deployment_executed' => true], 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_PRODUCTION_MUTATION'],
            [['audit_logging_validation_pass' => false], 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_AUDIT_LOGGING_MISSING'],
            [['observability_validation_pass' => false], 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_OBSERVABILITY_MISSING'],
            [['source_bias_risk_high' => true], 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_SOURCE_BIAS_OR_SHARED_CORE'],
        ];

        foreach ($cases as $case) {
            $result = $this->execute(['options' => $case[0]]);
            $this->assertSame($case[1], $result['status'], json_encode($case[0]));
        }
    }

    public function test_c77_candidate_scorecard_locks_e02_b01_and_a01_comparator_only(): void
    {
        $result = $this->runService();
        $scorecards = $result['controlled_pilot_shadow_execution_candidate_scorecard'];

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $scorecards[0]['candidate_code']);
        $this->assertSame('primary_controlled_runtime_opt_in_pilot_or_shadow_rollout_execution_review_candidate', $scorecards[0]['c77_role']);
        $this->assertTrue($scorecards[0]['candidate_ready_for_controlled_limited_runtime_observation_review']);

        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $scorecards[1]['candidate_code']);
        $this->assertSame('backup_controlled_runtime_opt_in_pilot_or_shadow_rollout_execution_review_candidate', $scorecards[1]['c77_role']);
        $this->assertTrue($scorecards[1]['candidate_ready_for_controlled_limited_runtime_observation_review']);

        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $scorecards[2]['candidate_code']);
        $this->assertSame('comparator_only', $scorecards[2]['c77_role']);
        $this->assertFalse($scorecards[2]['candidate_ready_for_controlled_limited_runtime_observation_review']);
        $this->assertSame(['C77_A01_REMAINS_COMPARATOR_ONLY'], $scorecards[2]['failure_reason_codes']);
    }

    public function test_c77_contexts_are_explicit_only_and_non_live(): void
    {
        $result = $this->runService();

        $this->assertTrue($result['controlled_pilot_execution_context_summary']['controlled_pilot_execution_context_is_explicit_only']);
        $this->assertTrue($result['controlled_shadow_execution_context_summary']['controlled_shadow_execution_context_is_explicit_only']);
        $this->assertTrue($result['controlled_pilot_execution_context_summary']['controlled_pilot_execution_context_requires_operator_approval']);
        $this->assertTrue($result['controlled_shadow_execution_context_summary']['controlled_shadow_execution_context_requires_approval_reference']);
        $this->assertTrue($result['controlled_pilot_execution_context_summary']['controlled_pilot_execution_context_requires_feature_flag_on']);
        $this->assertTrue($result['controlled_shadow_execution_context_summary']['controlled_shadow_execution_context_requires_kill_switch_off']);
        $this->assertTrue($result['controlled_pilot_execution_context_summary']['controlled_pilot_execution_context_is_not_persisted_to_config']);
        $this->assertTrue($result['controlled_shadow_execution_context_summary']['controlled_shadow_execution_context_is_not_persisted_to_db']);
        $this->assertTrue($result['controlled_pilot_execution_context_summary']['controlled_pilot_execution_context_is_not_persisted_to_live_runtime']);
        $this->assertTrue($result['controlled_shadow_execution_context_summary']['controlled_shadow_execution_context_is_not_persisted_to_live_runtime']);
        $this->assertTrue($result['controlled_pilot_execution_context_summary']['controlled_pilot_execution_context_does_not_mutate_plan_confirm']);
        $this->assertTrue($result['controlled_shadow_execution_context_summary']['controlled_shadow_execution_context_does_not_change_default_runtime']);
        $this->assertTrue($result['controlled_pilot_execution_context_summary']['controlled_pilot_execution_context_rejects_a01_as_runtime_candidate']);
        $this->assertTrue($result['controlled_shadow_execution_context_summary']['controlled_shadow_execution_context_rejects_a01_as_runtime_candidate']);
    }

    public function test_c77_governance_retains_risk_and_non_mutation(): void
    {
        $result = $this->runService();

        $this->assertTrue($result['database_dictionary_read_summary']['database_dictionary_read_rule_completed']);
        $this->assertSame('C76_LOCKED_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_DECISION', $result['candidate_scope_freeze_summary']['candidate_scope_source']);
        $this->assertTrue($result['c76_proof_carry_forward_validation_summary']['c76_negative_operator_approval_rejection_proof_retained']);
        $this->assertTrue($result['bad_month_controlled_pilot_shadow_execution_review_results'][0]['documented_bad_month_risk_retained']);
        $this->assertTrue($result['weak_regime_controlled_pilot_shadow_execution_review_results'][0]['weak_regime_retained']);
        $this->assertTrue($result['source_bias_shared_core_controlled_pilot_shadow_execution_validation_summary']['parent_diversity_sufficient']);
        $this->assertTrue($result['baseline_plan_confirm_non_mutation_summary']['baseline_plan_confirm_hash_unchanged']);
        $this->assertFalse($result['production_mutation_safety_summary']['production_deployment_executed']);
        $this->assertFalse($result['production_mutation_safety_summary']['plan_confirm_mutated']);
        $this->assertFalse($result['production_mutation_safety_summary']['pilot_execution_used_for_selection']);
        $this->assertFalse($result['production_mutation_safety_summary']['shadow_execution_used_for_retuning']);
    }

    private function runService(): array
    {
        return $this->execute();
    }

    private function execute(array $overrides = []): array
    {
        $service = new WatchlistBacktestC77ControlledRuntimeOptInPilotOrShadowRolloutExecutionReviewService();
        $fixture = $overrides['fixture'] ?? $this->lockedC76Fixture();
        $options = array_merge([
            'overwrite' => true,
            'operator_approved' => $overrides['operatorApproved'] ?? true,
            'approval_reference' => $overrides['approvalReference'] ?? 'C77_OPERATOR_APPROVED_EXECUTION_REVIEW_ONLY',
        ], $overrides['options'] ?? []);

        return $service->execute(
            $overrides['c76Artifact'] ?? $fixture['path'],
            $overrides['expectedC76Hash'] ?? $fixture['hash'],
            $overrides['expectedC76FileSha1'] ?? $fixture['sha1'],
            $this->output,
            $options
        );
    }

    private function lockedC76Fixture(): array
    {
        $path = 'storage/app/watchlist/backtest/c76-controlled-runtime-opt-in-pilot-or-shadow-rollout-preparation-review.json';
        $payload = json_decode((string) file_get_contents($path), true);
        return [
            'path' => $path,
            'hash' => (string) ($payload['artifact_hash'] ?? ''),
            'sha1' => strtoupper(sha1((string) file_get_contents($path))),
        ];
    }

    private function mutateC76AndExecute(callable $mutator, string $name): array
    {
        $fixture = $this->lockedC76Fixture();
        $c76 = json_decode((string) file_get_contents($fixture['path']), true);
        $c76 = $mutator($c76);
        $path = 'storage/app/watchlist/backtest/.tmp-'.$name.'.json';
        file_put_contents($path, json_encode($c76, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $this->tmpFiles[] = $path;

        return $this->execute([
            'fixture' => [
                'path' => $path,
                'hash' => (string) ($c76['artifact_hash'] ?? ''),
                'sha1' => strtoupper(sha1((string) file_get_contents($path))),
            ],
        ]);
    }

    private function readOutput(): array
    {
        return json_decode((string) file_get_contents($this->output), true);
    }
}

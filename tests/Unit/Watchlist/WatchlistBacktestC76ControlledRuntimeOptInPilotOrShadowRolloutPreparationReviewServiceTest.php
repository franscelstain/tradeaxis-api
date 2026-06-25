<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC76ControlledRuntimeOptInPilotOrShadowRolloutPreparationReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC76ControlledRuntimeOptInPilotOrShadowRolloutPreparationReviewServiceTest extends TestCase
{
    private string $output = 'storage/app/watchlist/backtest/.tmp-c76-test-output.json';
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

    public function test_c76_runtime_passes_primary_and_backup_when_locked_c75_and_operator_approval_match(): void
    {
        $result = $this->runService();

        $this->assertSame('C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_PASSED_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertSame($result['status'], $result['reason_code']);
        $this->assertTrue($result['controlled_runtime_opt_in_pilot_preparation_review_executed']);
        $this->assertTrue($result['controlled_runtime_opt_in_pilot_preparation_review_allowed']);
        $this->assertTrue($result['controlled_runtime_opt_in_pilot_preparation_review_pass']);
        $this->assertTrue($result['controlled_shadow_rollout_preparation_review_executed']);
        $this->assertTrue($result['controlled_shadow_rollout_preparation_review_allowed']);
        $this->assertTrue($result['controlled_shadow_rollout_preparation_review_pass']);
        $this->assertFalse($result['production_ready']);
        $this->assertFalse($result['production_catalog_runtime_wired']);
        $this->assertFalse($result['controlled_opt_in_runtime_bridge_active']);
        $this->assertFalse($result['controlled_parallel_run_active']);
        $this->assertFalse($result['controlled_rollout_active']);
        $this->assertFalse($result['controlled_pilot_context_persisted_to_live_runtime']);
        $this->assertFalse($result['controlled_shadow_context_persisted_to_live_runtime']);
        $this->assertFalse($result['production_deployment_allowed']);
        $this->assertFalse($result['production_deployment_executed']);
        $this->assertFalse($result['plan_confirm_mutation_allowed']);
        $this->assertFalse($result['plan_confirm_mutated']);
        $this->assertFalse($result['plan_confirm_runtime_reads_activated_catalog']);
        $this->assertFalse($result['live_plan_confirm_rollout_allowed']);
        $this->assertFalse($result['live_plan_confirm_rollout_executed']);
        $this->assertSame('C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW', $result['next_step_recommendation']);
        $this->assertSame(2, $result['next_readiness_decision']['candidate_ready_for_controlled_runtime_opt_in_pilot_or_shadow_rollout_execution_review_count']);
        $this->assertFileExists($this->output);
    }

    public function test_c76_records_required_sections(): void
    {
        $this->runService();
        $run = $this->readOutput();

        foreach ([
            'source_artifact_locks',
            'database_dictionary_read_summary',
            'c75_lock_validation_summary',
            'lineage_validation_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'controlled_pilot_shadow_preparation_candidate_scorecard',
            'controlled_pilot_shadow_preparation_decision',
            'controlled_pilot_preparation_context_summary',
            'controlled_shadow_preparation_context_summary',
            'runtime_readiness_inspection_summary',
            'feature_flag_operator_approval_kill_switch_validation_summary',
            'rollback_and_emergency_disable_review_summary',
            'c75_proof_carry_forward_validation_summary',
            'controlled_pilot_shadow_preparation_governance_summary',
            'fallback_behavior_controlled_pilot_shadow_validation_summary',
            'baseline_plan_confirm_non_mutation_summary',
            'bad_month_controlled_pilot_shadow_review_results',
            'weak_regime_controlled_pilot_shadow_review_results',
            'source_bias_shared_core_controlled_pilot_shadow_validation_summary',
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

    public function test_c76_validates_c75_artifact_hash_and_file_sha1(): void
    {
        $fixture = $this->lockedC75Fixture();
        $hashResult = $this->execute(['c75Artifact' => $fixture['path'], 'expectedC75Hash' => '0000000000000000000000000000000000000000', 'expectedC75FileSha1' => $fixture['sha1']]);
        $this->assertSame('C76_BLOCKED_C75_ARTIFACT_LOCK_MISMATCH', $hashResult['status']);
        $this->assertFalse($hashResult['c75_hash_match']);

        $shaResult = $this->execute(['c75Artifact' => $fixture['path'], 'expectedC75Hash' => $fixture['hash'], 'expectedC75FileSha1' => '0000000000000000000000000000000000000000']);
        $this->assertSame('C76_BLOCKED_C75_FILE_SHA1_LOCK_MISMATCH', $shaResult['status']);
        $this->assertFalse($shaResult['c75_file_sha1_match']);
    }

    public function test_c76_rejects_missing_c75_artifact(): void
    {
        $result = $this->execute(['c75Artifact' => 'storage/app/watchlist/backtest/missing-c75.json']);

        $this->assertSame('C76_BLOCKED_C75_ARTIFACT_LOCK_MISMATCH', $result['status']);
        $this->assertFalse($result['controlled_runtime_opt_in_pilot_preparation_review_executed']);
    }

    public function test_c76_rejects_c75_status_reason_and_pass_field_mismatches(): void
    {
        $status = $this->mutateC75AndExecute(function (array $c75): array {
            $c75['status'] = 'BROKEN_STATUS';
            return $c75;
        }, 'c75-status-mismatch');
        $this->assertSame('C76_BLOCKED_C75_STATUS_OR_REASON_MISMATCH', $status['status']);

        $reason = $this->mutateC75AndExecute(function (array $c75): array {
            $c75['reason_code'] = 'BROKEN_REASON';
            return $c75;
        }, 'c75-reason-mismatch');
        $this->assertSame('C76_BLOCKED_C75_STATUS_OR_REASON_MISMATCH', $reason['status']);

        $execution = $this->mutateC75AndExecute(function (array $c75): array {
            $c75['controlled_operator_approved_rollout_execution_review_pass'] = false;
            return $c75;
        }, 'c75-execution-false');
        $this->assertSame('C76_BLOCKED_C75_OPERATOR_APPROVED_EXECUTION_REVIEW_NOT_PASSED', $execution['status']);

        $wiring = $this->mutateC75AndExecute(function (array $c75): array {
            $c75['controlled_wiring_execution_review_pass'] = false;
            return $c75;
        }, 'c75-wiring-false');
        $this->assertSame('C76_BLOCKED_C75_CONTROLLED_WIRING_REVIEW_NOT_PASSED', $wiring['status']);
    }

    public function test_c76_validates_nested_c76_readiness_path_not_top_level_aliases(): void
    {
        $result = $this->mutateC75AndExecute(function (array $c75): array {
            $c75['candidate_ready_for_next_controlled_pilot_count'] = 0;
            $c75['next_recommendation'] = 'BROKEN_TOP_LEVEL_ALIAS';
            return $c75;
        }, 'c75-top-level-alias');

        $this->assertSame('C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_PASSED_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertTrue($result['c75_lock_validation_summary']['c76_readiness_nested_path_validated']);
        $this->assertFalse($result['c75_lock_validation_summary']['top_level_alias_used_for_c75_source_validation']);
    }

    public function test_c76_rejects_c75_nested_readiness_mismatches(): void
    {
        $count = $this->mutateC75AndExecute(function (array $c75): array {
            $c75['next_readiness_decision']['candidate_ready_for_next_controlled_pilot_count'] = 1;
            return $c75;
        }, 'c75-c76-count');
        $this->assertSame('C76_BLOCKED_C75_C76_READINESS_COUNT_MISMATCH', $count['status']);

        $recommendation = $this->mutateC75AndExecute(function (array $c75): array {
            $c75['next_readiness_decision']['next_recommendation'] = 'BROKEN_C76';
            return $c75;
        }, 'c75-c76-recommendation');
        $this->assertSame('C76_BLOCKED_C75_RECOMMENDATION_MISMATCH', $recommendation['status']);
    }

    public function test_c76_rejects_c75_runtime_live_safety_mismatches(): void
    {
        $cases = [
            ['production_catalog_runtime_wired', true, 'C76_BLOCKED_C75_RUNTIME_ALREADY_WIRED'],
            ['controlled_opt_in_runtime_bridge_active', true, 'C76_BLOCKED_C75_CONTROLLED_OPT_IN_BRIDGE_ALREADY_ACTIVE'],
            ['controlled_parallel_run_active', true, 'C76_BLOCKED_C75_CONTROLLED_PARALLEL_RUN_ALREADY_ACTIVE'],
            ['controlled_rollout_active', true, 'C76_BLOCKED_C75_CONTROLLED_ROLLOUT_ALREADY_ACTIVE'],
            ['controlled_wiring_context_persisted_to_live_runtime', true, 'C76_BLOCKED_C75_CONTROLLED_WIRING_CONTEXT_ALREADY_PERSISTED_TO_LIVE_RUNTIME'],
            ['production_deployment_allowed', true, 'C76_BLOCKED_C75_PRODUCTION_DEPLOYMENT_ALREADY_ALLOWED'],
            ['production_deployment_executed', true, 'C76_BLOCKED_C75_DEPLOYMENT_ALREADY_EXECUTED'],
            ['plan_confirm_mutation_allowed', true, 'C76_BLOCKED_C75_PLAN_CONFIRM_MUTATION_ALREADY_ALLOWED'],
            ['plan_confirm_mutated', true, 'C76_BLOCKED_C75_PLAN_CONFIRM_ALREADY_MUTATED'],
            ['plan_confirm_runtime_reads_activated_catalog', true, 'C76_BLOCKED_C75_PLAN_CONFIRM_ALREADY_READING_ACTIVATED_CATALOG'],
            ['live_plan_confirm_rollout_allowed', true, 'C76_BLOCKED_C75_LIVE_PLAN_CONFIRM_ROLLOUT_ALREADY_ALLOWED'],
            ['live_plan_confirm_rollout_executed', true, 'C76_BLOCKED_C75_LIVE_PLAN_CONFIRM_ROLLOUT_ALREADY_EXECUTED'],
        ];

        foreach ($cases as $case) {
            $result = $this->mutateC75AndExecute(function (array $c75) use ($case): array {
                $c75[$case[0]] = $case[1];
                return $c75;
            }, 'c75-'.$case[0]);
            $this->assertSame($case[2], $result['status'], $case[0]);
        }
    }

    public function test_c76_validates_lineage_and_candidate_scope(): void
    {
        $lineage = $this->mutateC75AndExecute(function (array $c75): array {
            $c75['source_artifact_locks']['c74_source_lineage_match'] = false;
            return $c75;
        }, 'c75-lineage-mismatch');
        $this->assertSame('C76_BLOCKED_LINEAGE_LOCK_MISMATCH', $lineage['status']);

        $scope = $this->mutateC75AndExecute(function (array $c75): array {
            $c75['candidate_scope_freeze_summary']['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $c75;
        }, 'c75-scope-mismatch');
        $this->assertSame('C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $scope['status']);
    }

    public function test_c76_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->execute(['operatorApproved' => false]);
        $this->assertSame('C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingOperator['status']);

        $missingReference = $this->execute(['approvalReference' => '']);
        $this->assertSame('C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingReference['status']);
    }

    public function test_c76_rejects_controlled_gate_failures(): void
    {
        $cases = [
            [['feature_flag_default_off' => false], 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH'],
            [['controlled_pilot_feature_flag_default_off' => false], 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH'],
            [['controlled_shadow_feature_flag_default_off' => false], 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH'],
            [['controlled_pilot_context_validation_pass' => false], 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_PILOT_PREPARATION_CONTEXT_MISSING'],
            [['controlled_shadow_context_validation_pass' => false], 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_SHADOW_ROLLOUT_PREPARATION_CONTEXT_MISSING'],
            [['rollback_plan_defined' => false], 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_ROLLBACK_PLAN_MISSING'],
            [['emergency_disable_path_defined' => false], 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_EMERGENCY_DISABLE_MISSING'],
            [['baseline_plan_confirm_hash_changed' => true], 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_BASELINE_HASH_CHANGED'],
            [['plan_confirm_output_changed' => true], 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_PLAN_CONFIRM_OUTPUT_CHANGED'],
            [['a01_used_as_runtime_fallback' => true], 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH'],
            [['pilot_preparation_used_for_selection' => true], 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_DELTA_USED_FOR_SELECTION_OR_RETUNING'],
            [['shadow_rollout_preparation_used_for_retuning' => true], 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_DELTA_USED_FOR_SELECTION_OR_RETUNING'],
            [['controlled_pilot_context_persisted_to_live_runtime' => true], 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_DEFAULT_PATH_MUTATION'],
            [['production_deployment_executed' => true], 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_PRODUCTION_MUTATION'],
        ];

        foreach ($cases as $case) {
            $result = $this->execute(['options' => $case[0]]);
            $this->assertSame($case[1], $result['status'], json_encode($case[0]));
        }
    }

    public function test_c76_candidate_scorecard_locks_e02_b01_and_a01_comparator_only(): void
    {
        $result = $this->runService();
        $scorecards = $result['controlled_pilot_shadow_preparation_candidate_scorecard'];

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $scorecards[0]['candidate_code']);
        $this->assertSame('primary_controlled_runtime_opt_in_pilot_or_shadow_rollout_preparation_candidate', $scorecards[0]['c76_role']);
        $this->assertTrue($scorecards[0]['candidate_ready_for_controlled_runtime_opt_in_pilot_or_shadow_rollout_execution_review']);

        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $scorecards[1]['candidate_code']);
        $this->assertSame('backup_controlled_runtime_opt_in_pilot_or_shadow_rollout_preparation_candidate', $scorecards[1]['c76_role']);
        $this->assertTrue($scorecards[1]['candidate_ready_for_controlled_runtime_opt_in_pilot_or_shadow_rollout_execution_review']);

        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $scorecards[2]['candidate_code']);
        $this->assertSame('comparator_only', $scorecards[2]['c76_role']);
        $this->assertFalse($scorecards[2]['candidate_ready_for_controlled_runtime_opt_in_pilot_or_shadow_rollout_execution_review']);
        $this->assertSame(['C76_A01_REMAINS_COMPARATOR_ONLY'], $scorecards[2]['failure_reason_codes']);
    }

    public function test_c76_contexts_are_explicit_only_and_non_live(): void
    {
        $result = $this->runService();

        $this->assertTrue($result['controlled_pilot_preparation_context_summary']['controlled_pilot_context_is_explicit_only']);
        $this->assertTrue($result['controlled_shadow_preparation_context_summary']['controlled_shadow_context_is_explicit_only']);
        $this->assertTrue($result['controlled_pilot_preparation_context_summary']['controlled_pilot_context_is_not_persisted_to_live_runtime']);
        $this->assertTrue($result['controlled_shadow_preparation_context_summary']['controlled_shadow_context_is_not_persisted_to_live_runtime']);
        $this->assertTrue($result['controlled_pilot_preparation_context_summary']['controlled_pilot_context_rejects_a01_as_runtime_candidate']);
        $this->assertTrue($result['controlled_shadow_preparation_context_summary']['controlled_shadow_context_rejects_a01_as_runtime_candidate']);
    }

    public function test_c76_governance_retains_risk_and_non_mutation(): void
    {
        $result = $this->runService();

        $this->assertTrue($result['bad_month_controlled_pilot_shadow_review_results'][0]['documented_bad_month_risk_retained']);
        $this->assertTrue($result['weak_regime_controlled_pilot_shadow_review_results'][0]['weak_regime_retained']);
        $this->assertTrue($result['source_bias_shared_core_controlled_pilot_shadow_validation_summary']['parent_diversity_sufficient']);
        $this->assertTrue($result['baseline_plan_confirm_non_mutation_summary']['baseline_plan_confirm_hash_unchanged']);
        $this->assertFalse($result['production_mutation_safety_summary']['production_deployment_executed']);
        $this->assertFalse($result['production_mutation_safety_summary']['plan_confirm_mutated']);
    }

    private function runService(): array
    {
        return $this->execute();
    }

    private function execute(array $overrides = []): array
    {
        $service = new WatchlistBacktestC76ControlledRuntimeOptInPilotOrShadowRolloutPreparationReviewService();
        $fixture = $overrides['fixture'] ?? $this->lockedC75Fixture();
        $options = array_merge([
            'overwrite' => true,
            'operator_approved' => $overrides['operatorApproved'] ?? true,
            'approval_reference' => $overrides['approvalReference'] ?? 'C76_OPERATOR_APPROVED_PREPARATION_REVIEW_ONLY',
        ], $overrides['options'] ?? []);

        return $service->execute(
            $overrides['c75Artifact'] ?? $fixture['path'],
            $overrides['expectedC75Hash'] ?? $fixture['hash'],
            $overrides['expectedC75FileSha1'] ?? $fixture['sha1'],
            $this->output,
            $options
        );
    }

    private function lockedC75Fixture(): array
    {
        $path = 'storage/app/watchlist/backtest/c75-controlled-operator-approved-rollout-execution-review-or-controlled-wiring-execution-review.json';
        $payload = json_decode((string) file_get_contents($path), true);
        return [
            'path' => $path,
            'hash' => (string) ($payload['artifact_hash'] ?? ''),
            'sha1' => strtoupper(sha1((string) file_get_contents($path))),
        ];
    }

    private function mutateC75AndExecute(callable $mutator, string $name): array
    {
        $fixture = $this->lockedC75Fixture();
        $c75 = json_decode((string) file_get_contents($fixture['path']), true);
        $c75 = $mutator($c75);
        $path = 'storage/app/watchlist/backtest/.tmp-'.$name.'.json';
        file_put_contents($path, json_encode($c75, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $this->tmpFiles[] = $path;

        return $this->execute([
            'fixture' => [
                'path' => $path,
                'hash' => (string) ($c75['artifact_hash'] ?? ''),
                'sha1' => strtoupper(sha1((string) file_get_contents($path))),
            ],
        ]);
    }

    private function readOutput(): array
    {
        return json_decode((string) file_get_contents($this->output), true);
    }
}

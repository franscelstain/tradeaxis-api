<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC75ControlledOperatorApprovedRolloutExecutionReviewOrControlledWiringExecutionReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC75ControlledOperatorApprovedRolloutExecutionReviewOrControlledWiringExecutionReviewServiceTest extends TestCase
{
    private string $output = 'storage/app/watchlist/backtest/.tmp-c75-test-output.json';
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

    public function test_c75_runtime_passes_primary_and_backup_when_locked_c74_and_operator_approval_match(): void
    {
        $result = $this->runService();

        $this->assertSame('C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_PASSED_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertSame($result['status'], $result['reason_code']);
        $this->assertTrue($result['controlled_operator_approved_rollout_execution_review_executed']);
        $this->assertTrue($result['controlled_operator_approved_rollout_execution_review_allowed']);
        $this->assertTrue($result['controlled_operator_approved_rollout_execution_review_pass']);
        $this->assertTrue($result['controlled_wiring_execution_review_executed']);
        $this->assertTrue($result['controlled_wiring_execution_review_allowed']);
        $this->assertTrue($result['controlled_wiring_execution_review_pass']);
        $this->assertFalse($result['production_ready']);
        $this->assertFalse($result['production_catalog_runtime_wired']);
        $this->assertFalse($result['controlled_opt_in_runtime_bridge_active']);
        $this->assertFalse($result['controlled_parallel_run_active']);
        $this->assertFalse($result['controlled_rollout_active']);
        $this->assertFalse($result['controlled_wiring_context_persisted_to_live_runtime']);
        $this->assertFalse($result['production_deployment_allowed']);
        $this->assertFalse($result['production_deployment_executed']);
        $this->assertFalse($result['plan_confirm_mutation_allowed']);
        $this->assertFalse($result['plan_confirm_mutated']);
        $this->assertFalse($result['plan_confirm_runtime_reads_activated_catalog']);
        $this->assertFalse($result['live_plan_confirm_rollout_allowed']);
        $this->assertFalse($result['live_plan_confirm_rollout_executed']);
        $this->assertSame('C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW', $result['next_step_recommendation']);
        $this->assertSame(2, $result['next_readiness_decision']['candidate_ready_for_next_controlled_pilot_count']);
        $this->assertFileExists($this->output);
    }

    public function test_c75_records_required_sections(): void
    {
        $this->runService();
        $run = $this->readOutput();

        foreach ([
            'source_artifact_locks',
            'database_dictionary_read_summary',
            'c74_lock_validation_summary',
            'lineage_validation_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'controlled_operator_approved_execution_candidate_scorecard',
            'controlled_wiring_execution_review_decision',
            'controlled_wiring_execution_context_summary',
            'runtime_readiness_inspection_summary',
            'feature_flag_operator_approval_kill_switch_validation_summary',
            'rollback_and_emergency_disable_review_summary',
            'c74_proof_carry_forward_validation_summary',
            'controlled_execution_governance_summary',
            'fallback_behavior_controlled_wiring_validation_summary',
            'baseline_plan_confirm_non_mutation_summary',
            'bad_month_controlled_wiring_review_results',
            'weak_regime_controlled_wiring_review_results',
            'source_bias_shared_core_controlled_wiring_validation_summary',
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

    public function test_c75_validates_c74_artifact_hash_and_file_sha1(): void
    {
        $fixture = $this->lockedC74Fixture();
        $hashResult = $this->execute(['c74Artifact' => $fixture['path'], 'expectedC74Hash' => '0000000000000000000000000000000000000000', 'expectedC74FileSha1' => $fixture['sha1']]);
        $this->assertSame('C75_BLOCKED_C74_ARTIFACT_LOCK_MISMATCH', $hashResult['status']);
        $this->assertFalse($hashResult['c74_hash_match']);

        $shaResult = $this->execute(['c74Artifact' => $fixture['path'], 'expectedC74Hash' => $fixture['hash'], 'expectedC74FileSha1' => '0000000000000000000000000000000000000000']);
        $this->assertSame('C75_BLOCKED_C74_FILE_SHA1_LOCK_MISMATCH', $shaResult['status']);
        $this->assertFalse($shaResult['c74_file_sha1_match']);
    }

    public function test_c75_rejects_missing_c74_artifact(): void
    {
        $result = $this->execute(['c74Artifact' => 'storage/app/watchlist/backtest/missing-c74.json']);

        $this->assertSame('C75_BLOCKED_C74_ARTIFACT_LOCK_MISMATCH', $result['status']);
        $this->assertFalse($result['controlled_operator_approved_rollout_execution_review_executed']);
    }

    public function test_c75_rejects_c74_status_reason_gate_and_readiness_mismatches(): void
    {
        $status = $this->mutateC74AndExecute(function (array $c74): array {
            $c74['status'] = 'BROKEN_STATUS';
            return $c74;
        }, 'c74-status-mismatch');
        $this->assertSame('C75_BLOCKED_C74_STATUS_OR_REASON_MISMATCH', $status['status']);

        $reason = $this->mutateC74AndExecute(function (array $c74): array {
            $c74['reason_code'] = 'BROKEN_REASON';
            return $c74;
        }, 'c74-reason-mismatch');
        $this->assertSame('C75_BLOCKED_C74_STATUS_OR_REASON_MISMATCH', $reason['status']);

        $gate = $this->mutateC74AndExecute(function (array $c74): array {
            $c74['controlled_operator_reviewed_rollout_gate_validation_pass'] = false;
            return $c74;
        }, 'c74-gate-false');
        $this->assertSame('C75_BLOCKED_C74_OPERATOR_REVIEWED_ROLLOUT_GATE_NOT_PASSED', $gate['status']);

        $count = $this->mutateC74AndExecute(function (array $c74): array {
            $c74['c75_readiness_decision']['candidate_ready_for_c75_count'] = 1;
            return $c74;
        }, 'c74-c75-count');
        $this->assertSame('C75_BLOCKED_C74_C75_READINESS_COUNT_MISMATCH', $count['status']);

        $recommendation = $this->mutateC74AndExecute(function (array $c74): array {
            $c74['c75_readiness_decision']['c75_recommendation'] = 'BROKEN_C75';
            return $c74;
        }, 'c74-c75-recommendation');
        $this->assertSame('C75_BLOCKED_C74_RECOMMENDATION_MISMATCH', $recommendation['status']);
    }

    public function test_c75_validates_nested_c75_readiness_path_not_top_level_aliases(): void
    {
        $result = $this->mutateC74AndExecute(function (array $c74): array {
            $c74['candidate_ready_for_c75_count'] = 0;
            $c74['c75_recommendation'] = 'BROKEN_TOP_LEVEL_ALIAS';
            return $c74;
        }, 'c74-top-level-alias');

        $this->assertSame('C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_PASSED_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertTrue($result['c74_lock_validation_summary']['c75_readiness_nested_path_validated']);
        $this->assertFalse($result['c74_lock_validation_summary']['top_level_alias_used_for_c74_source_validation']);
    }

    public function test_c75_rejects_c74_runtime_live_safety_mismatches(): void
    {
        $cases = [
            ['production_catalog_runtime_wired', true, 'C75_BLOCKED_C74_RUNTIME_ALREADY_WIRED'],
            ['controlled_opt_in_runtime_bridge_active', true, 'C75_BLOCKED_C74_CONTROLLED_OPT_IN_BRIDGE_ALREADY_ACTIVE'],
            ['controlled_parallel_run_active', true, 'C75_BLOCKED_C74_CONTROLLED_PARALLEL_RUN_ALREADY_ACTIVE'],
            ['controlled_rollout_active', true, 'C75_BLOCKED_C74_CONTROLLED_ROLLOUT_ALREADY_ACTIVE'],
            ['production_deployment_allowed', true, 'C75_BLOCKED_C74_PRODUCTION_DEPLOYMENT_ALREADY_ALLOWED'],
            ['production_deployment_executed', true, 'C75_BLOCKED_C74_DEPLOYMENT_ALREADY_EXECUTED'],
            ['plan_confirm_mutation_allowed', true, 'C75_BLOCKED_C74_PLAN_CONFIRM_MUTATION_ALREADY_ALLOWED'],
            ['plan_confirm_mutated', true, 'C75_BLOCKED_C74_PLAN_CONFIRM_ALREADY_MUTATED'],
            ['plan_confirm_runtime_reads_activated_catalog', true, 'C75_BLOCKED_C74_PLAN_CONFIRM_ALREADY_READING_ACTIVATED_CATALOG'],
            ['live_plan_confirm_rollout_allowed', true, 'C75_BLOCKED_C74_LIVE_PLAN_CONFIRM_ROLLOUT_ALREADY_ALLOWED'],
            ['live_plan_confirm_rollout_executed', true, 'C75_BLOCKED_C74_LIVE_PLAN_CONFIRM_ROLLOUT_ALREADY_EXECUTED'],
        ];

        foreach ($cases as $case) {
            $result = $this->mutateC74AndExecute(function (array $c74) use ($case): array {
                $c74[$case[0]] = $case[1];
                return $c74;
            }, 'c74-'.$case[0]);
            $this->assertSame($case[2], $result['status'], $case[0]);
        }
    }

    public function test_c75_validates_lineage_and_candidate_scope(): void
    {
        $lineage = $this->mutateC74AndExecute(function (array $c74): array {
            $c74['source_artifact_locks']['c73_source_lineage_match'] = false;
            return $c74;
        }, 'c74-lineage-mismatch');
        $this->assertSame('C75_BLOCKED_LINEAGE_LOCK_MISMATCH', $lineage['status']);

        $scope = $this->mutateC74AndExecute(function (array $c74): array {
            $c74['candidate_scope_freeze_summary']['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $c74;
        }, 'c74-scope-mismatch');
        $this->assertSame('C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $scope['status']);
    }

    public function test_c75_requires_operator_approval_and_non_empty_reference(): void
    {
        $missingApproval = $this->execute(['operatorApproved' => false]);
        $this->assertSame('C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingApproval['status']);
        $this->assertFalse($missingApproval['controlled_operator_approved_rollout_execution_review_pass']);

        $missingReference = $this->execute(['approvalReference' => '']);
        $this->assertSame('C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingReference['status']);
        $this->assertFalse($missingReference['controlled_wiring_execution_review_pass']);
    }

    public function test_c75_rejects_controlled_wiring_or_safety_failures(): void
    {
        $cases = [
            [['feature_flag_default_off' => false], 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH'],
            [['controlled_rollout_feature_flag_default_off' => false], 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH'],
            [['kill_switch_available' => false], 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH'],
            [['controlled_wiring_context_validation_pass' => false], 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_CONTROLLED_WIRING_PROOF_MISSING'],
            [['rollback_plan_defined' => false], 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_ROLLBACK_PLAN_MISSING'],
            [['emergency_disable_path_defined' => false], 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_EMERGENCY_DISABLE_MISSING'],
            [['baseline_plan_confirm_hash_changed' => true], 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_BASELINE_HASH_CHANGED'],
            [['plan_confirm_output_changed' => true], 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_PLAN_CONFIRM_OUTPUT_CHANGED'],
            [['a01_used_as_runtime_fallback' => true], 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH'],
            [['controlled_execution_used_for_selection' => true], 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_DELTA_USED_FOR_SELECTION_OR_RETUNING'],
            [['controlled_wiring_execution_used_for_retuning' => true], 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_DELTA_USED_FOR_SELECTION_OR_RETUNING'],
            [['controlled_wiring_context_persisted_to_live_runtime' => true], 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_CONTROLLED_WIRING_DEFAULT_PATH_MUTATION'],
            [['production_deployment_executed' => true], 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_PRODUCTION_MUTATION'],
            [['plan_confirm_runtime_reads_activated_catalog' => true], 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_PRODUCTION_MUTATION'],
            [['bad_month_risk_retained' => false], 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_BAD_MONTH_GOVERNANCE'],
            [['weak_regime_risk_retained' => false], 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_WEAK_REGIME_GOVERNANCE'],
            [['source_bias_risk_level' => 'HIGH'], 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_SOURCE_BIAS_OR_SHARED_CORE'],
            [['latest_shortcut_used' => true], 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_SAFETY_OR_LEAKAGE'],
            [['documentation_governance_pass' => false], 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_DOCUMENTATION_GOVERNANCE'],
        ];

        foreach ($cases as $index => $case) {
            $result = $this->execute(['options' => $case[0]]);
            $this->assertSame($case[1], $result['status'], 'case '.$index);
        }
    }

    public function test_c75_candidate_scorecard_preserves_e02_b01_and_a01_comparator_only(): void
    {
        $run = $this->runService();
        $scorecard = $run['controlled_operator_approved_execution_candidate_scorecard'];

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $scorecard[0]['candidate_code']);
        $this->assertSame('primary_controlled_operator_approved_rollout_execution_review_candidate', $scorecard[0]['c75_role']);
        $this->assertTrue($scorecard[0]['controlled_wiring_execution_review_pass']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $scorecard[1]['candidate_code']);
        $this->assertSame('backup_controlled_operator_approved_rollout_execution_review_candidate', $scorecard[1]['c75_role']);
        $this->assertTrue($scorecard[1]['candidate_ready_for_next_controlled_pilot_or_shadow_rollout_review']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $scorecard[2]['candidate_code']);
        $this->assertSame('comparator_only', $scorecard[2]['c75_role']);
        $this->assertFalse($scorecard[2]['controlled_wiring_execution_review_pass']);
        $this->assertSame(['C75_A01_REMAINS_COMPARATOR_ONLY'], $scorecard[2]['failure_reason_codes']);
    }

    public function test_c75_next_readiness_is_c76_only_and_live_runtime_remains_off(): void
    {
        $run = $this->runService();
        $next = $run['next_readiness_decision'];

        $this->assertSame(2, $next['candidate_ready_for_next_controlled_pilot_count']);
        $this->assertSame('C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW', $next['next_recommendation']);
        $this->assertTrue($next['controlled_operator_approved_rollout_execution_review_pass']);
        $this->assertTrue($next['controlled_wiring_execution_review_pass']);
        foreach ([
            'production_catalog_runtime_wired',
            'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active',
            'controlled_rollout_active',
            'controlled_wiring_context_persisted_to_live_runtime',
            'production_deployment_allowed',
            'production_deployment_executed',
            'plan_confirm_mutation_allowed',
            'plan_confirm_mutated',
            'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed',
            'live_plan_confirm_rollout_executed',
        ] as $field) {
            $this->assertFalse($next[$field], $field);
        }
    }

    private function runService(): array
    {
        return $this->execute();
    }

    private function execute(array $overrides = []): array
    {
        $service = new WatchlistBacktestC75ControlledOperatorApprovedRolloutExecutionReviewOrControlledWiringExecutionReviewService();
        $fixture = $overrides['fixture'] ?? $this->lockedC74Fixture();
        $options = array_merge([
            'overwrite' => true,
            'operator_approved' => $overrides['operatorApproved'] ?? true,
            'approval_reference' => $overrides['approvalReference'] ?? 'C75_OPERATOR_APPROVED_EXECUTION_REVIEW_ONLY',
        ], $overrides['options'] ?? []);

        return $service->execute(
            $overrides['c74Artifact'] ?? $fixture['path'],
            $overrides['expectedC74Hash'] ?? $fixture['hash'],
            $overrides['expectedC74FileSha1'] ?? $fixture['sha1'],
            $this->output,
            $options
        );
    }

    private function lockedC74Fixture(): array
    {
        $path = 'storage/app/watchlist/backtest/c74-controlled-operator-reviewed-rollout-gate-or-deployment-readiness-review.json';
        $payload = json_decode((string) file_get_contents($path), true);
        return [
            'path' => $path,
            'hash' => (string) ($payload['artifact_hash'] ?? ''),
            'sha1' => strtoupper(sha1((string) file_get_contents($path))),
        ];
    }

    private function mutateC74AndExecute(callable $mutator, string $name): array
    {
        $fixture = $this->lockedC74Fixture();
        $c74 = json_decode((string) file_get_contents($fixture['path']), true);
        $c74 = $mutator($c74);
        $path = 'storage/app/watchlist/backtest/.tmp-'.$name.'.json';
        file_put_contents($path, json_encode($c74, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $this->tmpFiles[] = $path;

        return $this->execute([
            'fixture' => [
                'path' => $path,
                'hash' => (string) ($c74['artifact_hash'] ?? ''),
                'sha1' => strtoupper(sha1((string) file_get_contents($path))),
            ],
        ]);
    }

    private function readOutput(): array
    {
        return json_decode((string) file_get_contents($this->output), true);
    }
}

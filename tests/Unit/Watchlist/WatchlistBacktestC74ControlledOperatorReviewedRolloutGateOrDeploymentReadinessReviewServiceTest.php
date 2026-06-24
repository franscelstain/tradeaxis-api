<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC74ControlledOperatorReviewedRolloutGateOrDeploymentReadinessReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC74ControlledOperatorReviewedRolloutGateOrDeploymentReadinessReviewServiceTest extends TestCase
{
    private string $output = 'storage/app/watchlist/backtest/.tmp-c74-test-output.json';
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

    public function test_c74_runtime_passes_primary_and_backup_when_locked_c73_and_operator_review_match(): void
    {
        $result = $this->runService();

        $this->assertSame('C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_PASSED_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertSame('C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_PASSED_PRIMARY_AND_BACKUP', $result['reason_code']);
        $this->assertTrue($result['controlled_operator_reviewed_rollout_gate_validation_executed']);
        $this->assertTrue($result['controlled_operator_reviewed_rollout_gate_validation_allowed']);
        $this->assertTrue($result['controlled_operator_reviewed_rollout_gate_validation_pass']);
        $this->assertFalse($result['production_ready']);
        $this->assertFalse($result['production_catalog_runtime_wired']);
        $this->assertFalse($result['controlled_opt_in_runtime_bridge_active']);
        $this->assertFalse($result['controlled_parallel_run_active']);
        $this->assertFalse($result['controlled_rollout_active']);
        $this->assertFalse($result['production_deployment_allowed']);
        $this->assertFalse($result['production_deployment_executed']);
        $this->assertFalse($result['plan_confirm_mutation_allowed']);
        $this->assertFalse($result['plan_confirm_mutated']);
        $this->assertFalse($result['plan_confirm_runtime_reads_activated_catalog']);
        $this->assertFalse($result['live_plan_confirm_rollout_allowed']);
        $this->assertFalse($result['live_plan_confirm_rollout_executed']);
        $this->assertSame('C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW', $result['next_step_recommendation']);
        $this->assertSame(2, $result['c75_readiness_decision']['candidate_ready_for_c75_count']);
        $this->assertFileExists($this->output);
    }

    public function test_c74_records_required_sections(): void
    {
        $this->runService();
        $run = $this->readOutput();

        foreach ([
            'source_artifact_locks',
            'database_dictionary_read_summary',
            'c73_lock_validation_summary',
            'lineage_validation_summary',
            'candidate_scope_freeze_summary',
            'controlled_operator_reviewed_rollout_gate_candidate_scorecard',
            'controlled_operator_reviewed_rollout_gate_validation_decision',
            'runtime_readiness_inspection_summary',
            'feature_flag_operator_approval_kill_switch_validation_summary',
            'operator_review_checklist_summary',
            'rollback_and_emergency_disable_review_summary',
            'c73_proof_carry_forward_validation_summary',
            'parallel_run_delta_governance_summary',
            'fallback_behavior_rollout_gate_validation_summary',
            'bad_month_rollout_gate_review_results',
            'weak_regime_rollout_gate_review_results',
            'source_bias_shared_core_rollout_gate_validation_summary',
            'production_mutation_safety_summary',
            'documentation_governance_summary',
            'c65_cleanup_note_summary',
            'c75_readiness_decision',
            'failure_attribution_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }
    }

    public function test_c74_validates_c73_artifact_hash_and_file_sha1(): void
    {
        $fixture = $this->lockedC73Fixture();
        $hashResult = $this->execute(['c73Artifact' => $fixture['path'], 'expectedC73Hash' => '0000000000000000000000000000000000000000', 'expectedC73FileSha1' => $fixture['sha1']]);
        $this->assertSame('C74_BLOCKED_C73_ARTIFACT_LOCK_MISMATCH', $hashResult['status']);
        $this->assertFalse($hashResult['c73_hash_match']);

        $shaResult = $this->execute(['c73Artifact' => $fixture['path'], 'expectedC73Hash' => $fixture['hash'], 'expectedC73FileSha1' => '0000000000000000000000000000000000000000']);
        $this->assertSame('C74_BLOCKED_C73_FILE_SHA1_LOCK_MISMATCH', $shaResult['status']);
        $this->assertFalse($shaResult['c73_file_sha1_match']);
    }

    public function test_c74_rejects_missing_c73_artifact(): void
    {
        $result = $this->execute(['c73Artifact' => 'storage/app/watchlist/backtest/missing-c73.json']);

        $this->assertSame('C74_BLOCKED_C73_ARTIFACT_LOCK_MISMATCH', $result['status']);
        $this->assertFalse($result['controlled_operator_reviewed_rollout_gate_validation_executed']);
    }

    public function test_c74_rejects_c73_status_reason_and_validation_mismatches(): void
    {
        $status = $this->mutateC73AndExecute(function (array $c73): array {
            $c73['status'] = 'BROKEN_STATUS';
            return $c73;
        }, 'c73-status-mismatch');
        $this->assertSame('C74_BLOCKED_C73_STATUS_OR_REASON_MISMATCH', $status['status']);

        $reason = $this->mutateC73AndExecute(function (array $c73): array {
            $c73['reason_code'] = 'BROKEN_REASON';
            return $c73;
        }, 'c73-reason-mismatch');
        $this->assertSame('C74_BLOCKED_C73_STATUS_OR_REASON_MISMATCH', $reason['status']);

        $bridge = $this->mutateC73AndExecute(function (array $c73): array {
            $c73['controlled_parallel_run_non_mutating_plan_confirm_bridge_validation_pass'] = false;
            return $c73;
        }, 'c73-parallel-run-false');
        $this->assertSame('C74_BLOCKED_C73_CONTROLLED_PARALLEL_RUN_VALIDATION_NOT_PASSED', $bridge['status']);
    }

    public function test_c74_validates_nested_c74_readiness_path_not_top_level_aliases(): void
    {
        $result = $this->mutateC73AndExecute(function (array $c73): array {
            $c73['candidate_ready_for_c74_count'] = 0;
            $c73['c74_recommendation'] = 'BROKEN_TOP_LEVEL_ALIAS';
            return $c73;
        }, 'c73-top-level-alias');

        $this->assertSame('C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_PASSED_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertTrue($result['c73_lock_validation_summary']['c74_readiness_nested_path_validated']);
        $this->assertFalse($result['c73_lock_validation_summary']['top_level_alias_used_for_c73_source_validation']);
    }

    public function test_c74_rejects_nested_c74_readiness_mismatches(): void
    {
        $count = $this->mutateC73AndExecute(function (array $c73): array {
            $c73['c74_readiness_decision']['candidate_ready_for_c74_count'] = 1;
            return $c73;
        }, 'c73-nested-count');
        $this->assertSame('C74_BLOCKED_C73_C74_READINESS_COUNT_MISMATCH', $count['status']);

        $recommendation = $this->mutateC73AndExecute(function (array $c73): array {
            $c73['c74_readiness_decision']['c74_recommendation'] = 'BROKEN_C74';
            return $c73;
        }, 'c73-nested-recommendation');
        $this->assertSame('C74_BLOCKED_C73_RECOMMENDATION_MISMATCH', $recommendation['status']);
    }

    public function test_c74_rejects_c73_runtime_live_safety_mismatches(): void
    {
        $cases = [
            ['production_catalog_runtime_wired', true, 'C74_BLOCKED_C73_RUNTIME_ALREADY_WIRED'],
            ['controlled_opt_in_runtime_bridge_active', true, 'C74_BLOCKED_C73_CONTROLLED_OPT_IN_BRIDGE_ALREADY_ACTIVE'],
            ['controlled_parallel_run_active', true, 'C74_BLOCKED_C73_CONTROLLED_PARALLEL_RUN_ALREADY_ACTIVE'],
            ['production_deployment_allowed', true, 'C74_BLOCKED_C73_PRODUCTION_DEPLOYMENT_ALREADY_ALLOWED'],
            ['production_deployment_executed', true, 'C74_BLOCKED_C73_DEPLOYMENT_ALREADY_EXECUTED'],
            ['plan_confirm_mutation_allowed', true, 'C74_BLOCKED_C73_PLAN_CONFIRM_MUTATION_ALREADY_ALLOWED'],
            ['plan_confirm_mutated', true, 'C74_BLOCKED_C73_PLAN_CONFIRM_ALREADY_MUTATED'],
            ['plan_confirm_runtime_reads_activated_catalog', true, 'C74_BLOCKED_C73_PLAN_CONFIRM_ALREADY_READING_ACTIVATED_CATALOG'],
            ['live_plan_confirm_rollout_allowed', true, 'C74_BLOCKED_C73_LIVE_PLAN_CONFIRM_ROLLOUT_ALREADY_ALLOWED'],
            ['live_plan_confirm_rollout_executed', true, 'C74_BLOCKED_C73_LIVE_PLAN_CONFIRM_ROLLOUT_ALREADY_EXECUTED'],
        ];

        foreach ($cases as $case) {
            $result = $this->mutateC73AndExecute(function (array $c73) use ($case): array {
                $c73[$case[0]] = $case[1];
                return $c73;
            }, 'c73-'.$case[0]);
            $this->assertSame($case[2], $result['status'], $case[0]);
        }
    }

    public function test_c74_validates_c73_source_lineage_locks_and_candidate_scope(): void
    {
        $lineage = $this->mutateC73AndExecute(function (array $c73): array {
            $c73['source_artifact_locks']['c72_source_lineage_match'] = false;
            return $c73;
        }, 'c73-lineage-mismatch');
        $this->assertSame('C74_BLOCKED_LINEAGE_LOCK_MISMATCH', $lineage['status']);

        $scope = $this->mutateC73AndExecute(function (array $c73): array {
            $c73['candidate_scope_freeze_summary']['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $c73;
        }, 'c73-scope-mismatch');
        $this->assertSame('C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $scope['status']);
    }

    public function test_c74_requires_explicit_operator_review(): void
    {
        $result = $this->execute(['operatorReviewed' => false]);

        $this->assertSame('C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $result['status']);
        $this->assertTrue($result['controlled_operator_reviewed_rollout_gate_validation_executed']);
        $this->assertFalse($result['controlled_operator_reviewed_rollout_gate_validation_pass']);
    }

    /**
     * @dataProvider rolloutGateFailureProvider
     */
    public function test_c74_rejects_rollout_gate_failures(array $options, string $expectedStatus): void
    {
        $result = $this->execute(['options' => $options]);
        $this->assertSame($expectedStatus, $result['status']);
        $this->assertSame(0, $result['c75_readiness_decision']['candidate_ready_for_c75_count']);
        $this->assertFalse($result['controlled_operator_reviewed_rollout_gate_validation_pass']);
    }

    public function rolloutGateFailureProvider(): array
    {
        return [
            [['feature_flag_default_off' => false], 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH'],
            [['controlled_parallel_run_feature_flag_default_off' => false], 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH'],
            [['controlled_rollout_feature_flag_default_off' => false], 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH'],
            [['kill_switch_available' => false], 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH'],
            [['kill_switch_blocks_future_rollout_path' => false], 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH'],
            [['operator_review_checklist_exists' => false], 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING'],
            [['rollback_plan_defined' => false], 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_REJECTED_ROLLBACK_PLAN_MISSING'],
            [['emergency_disable_path_defined' => false], 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_REJECTED_EMERGENCY_DISABLE_MISSING'],
            [['plan_confirm_output_changed' => true], 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_REJECTED_PLAN_CONFIRM_OUTPUT_CHANGED'],
            [['baseline_plan_confirm_hash_changed' => true], 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_REJECTED_BASELINE_HASH_CHANGED'],
            [['a01_used_as_runtime_fallback' => true], 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH'],
            [['parallel_run_delta_used_for_selection' => true], 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_REJECTED_DELTA_USED_FOR_SELECTION_OR_RETUNING'],
            [['parallel_run_delta_used_for_retuning' => true], 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_REJECTED_DELTA_USED_FOR_SELECTION_OR_RETUNING'],
            [['parallel_run_delta_used_for_plan_confirm_mutation' => true], 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_REJECTED_DELTA_USED_FOR_SELECTION_OR_RETUNING'],
            [['bad_month_risk_retained' => false], 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_REJECTED_BAD_MONTH_GOVERNANCE'],
            [['weak_regime_risk_retained' => false], 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_REJECTED_WEAK_REGIME_GOVERNANCE'],
            [['source_bias_risk_level' => 'HIGH'], 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_REJECTED_SOURCE_BIAS_OR_SHARED_CORE'],
            [['shared_core_risk_level' => 'HIGH'], 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_REJECTED_SOURCE_BIAS_OR_SHARED_CORE'],
            [['production_deployment_executed' => true], 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_REJECTED_PRODUCTION_MUTATION'],
            [['plan_confirm_runtime_reads_activated_catalog' => true], 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_REJECTED_PRODUCTION_MUTATION'],
            [['latest_shortcut_used' => true], 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_REJECTED_SAFETY_OR_LEAKAGE'],
            [['documentation_governance_pass' => false], 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_REJECTED_DOCUMENTATION_GOVERNANCE'],
        ];
    }

    public function test_c74_candidate_scorecard_preserves_e02_b01_and_a01_comparator_only(): void
    {
        $result = $this->runService();
        $scorecard = $result['controlled_operator_reviewed_rollout_gate_candidate_scorecard'];

        $this->assertCount(3, $scorecard);
        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $scorecard[0]['candidate_code']);
        $this->assertSame('primary_controlled_operator_reviewed_rollout_gate_candidate', $scorecard[0]['c74_role']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $scorecard[1]['candidate_code']);
        $this->assertSame('backup_controlled_operator_reviewed_rollout_gate_candidate', $scorecard[1]['c74_role']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $scorecard[2]['candidate_code']);
        $this->assertSame('comparator_only', $scorecard[2]['c74_role']);
        $this->assertFalse($scorecard[2]['candidate_ready_for_c75_controlled_operator_approved_rollout_execution_review']);
        $this->assertSame(['C74_A01_REMAINS_COMPARATOR_ONLY'], $scorecard[2]['failure_reason_codes']);
    }

    public function test_c74_preserves_bad_month_weak_regime_source_bias_and_safety_summaries(): void
    {
        $result = $this->runService();

        $this->assertSame('2026-03', $result['bad_month_rollout_gate_review_results'][0]['worst_month']);
        $this->assertSame('2025-10', $result['bad_month_rollout_gate_review_results'][1]['worst_month']);
        $this->assertSame('market_down_or_sideways_high_vol', $result['weak_regime_rollout_gate_review_results'][0]['weak_regime']);
        $this->assertSame('PASS_WITH_DOCUMENTED_RISK', $result['weak_regime_rollout_gate_review_results'][0]['weak_regime_governance_decision']);
        $this->assertTrue($result['source_bias_shared_core_rollout_gate_validation_summary']['parent_diversity_sufficient']);
        $this->assertFalse($result['production_mutation_safety_summary']['production_catalog_runtime_wired']);
        $this->assertFalse($result['production_mutation_safety_summary']['plan_confirm_runtime_reads_activated_catalog']);
    }

    private function runService(): array
    {
        return $this->execute();
    }

    private function execute(array $overrides = []): array
    {
        $service = new WatchlistBacktestC74ControlledOperatorReviewedRolloutGateOrDeploymentReadinessReviewService();
        $fixture = $overrides['fixture'] ?? $this->lockedC73Fixture();
        $options = array_merge([
            'overwrite' => true,
            'operator_reviewed' => $overrides['operatorReviewed'] ?? true,
        ], $overrides['options'] ?? []);

        return $service->execute(
            $overrides['c73Artifact'] ?? $fixture['path'],
            $overrides['expectedC73Hash'] ?? $fixture['hash'],
            $overrides['expectedC73FileSha1'] ?? $fixture['sha1'],
            $this->output,
            $options
        );
    }

    private function lockedC73Fixture(): array
    {
        return [
            'path' => 'storage/app/watchlist/backtest/c73-controlled-parallel-run-non-mutating-plan-confirm-bridge-validation.json',
            'hash' => '34f1f84a4261da7ce1cb9d17a1bf33dfb1458281',
            'sha1' => 'BF18CAA2654D5A7DE5419DE5DAF42E0B55D73CC9',
        ];
    }

    private function mutateC73AndExecute(callable $mutator, string $name): array
    {
        $fixture = $this->lockedC73Fixture();
        $c73 = json_decode((string) file_get_contents($fixture['path']), true);
        $c73 = $mutator($c73);
        $path = 'storage/app/watchlist/backtest/.tmp-'.$name.'.json';
        file_put_contents($path, json_encode($c73, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $this->tmpFiles[] = $path;

        return $this->execute([
            'fixture' => [
                'path' => $path,
                'hash' => (string) ($c73['artifact_hash'] ?? ''),
                'sha1' => strtoupper(sha1((string) file_get_contents($path))),
            ],
        ]);
    }

    private function readOutput(): array
    {
        return json_decode((string) file_get_contents($this->output), true);
    }
}

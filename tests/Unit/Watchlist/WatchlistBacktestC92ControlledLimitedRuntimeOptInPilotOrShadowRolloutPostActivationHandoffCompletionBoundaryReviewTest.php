<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC92ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationHandoffCompletionBoundaryReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC92ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationHandoffCompletionBoundaryReviewTest extends TestCase
{
    private string $output = 'storage/app/watchlist/backtest/.tmp-c92-test-output.json';
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

    public function test_c92_passes_with_valid_c91_lock_operator_approval_and_reference(): void
    {
        $result = $this->runService();

        $this->assertSame('C92_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_COMPLETION_BOUNDARY_REVIEW_PASSED_BOUNDARY_CLEARED_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertSame($result['status'], $result['reason_code']);
        $this->assertTrue($result['post_activation_handoff_completion_boundary_review_executed']);
        $this->assertTrue($result['post_activation_handoff_completion_boundary_review_allowed']);
        $this->assertTrue($result['post_activation_handoff_completion_boundary_review_pass']);
        $this->assertTrue($result['post_activation_handoff_completion_boundary_cleared']);
        $this->assertTrue($result['boundary_cleared']);
        $this->assertTrue($result['primary_candidate_boundary_cleared']);
        $this->assertTrue($result['backup_candidate_boundary_cleared']);
        $this->assertFalse($result['comparator_candidate_boundary_cleared']);
        $this->assertTrue($result['a01_remains_comparator_only']);
        $this->assertSame('BOUNDARY_CLEARED_GO', $result['boundary_go_decision']);
        $this->assertSame('GO', $result['operator_go_decision']);
        $this->assertSame('C93_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_CLOSURE_SEAL_REVIEW', $result['next_step_recommendation']);
        $this->assertSame('C93_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_CLOSURE_SEAL_REVIEW', $result['next_readiness_decision']['next_recommendation']);
        $this->assertSame('C92_HANDOFF_COMPLETION_BOUNDARY_CLEARED_NON_LIVE_ONLY', $result['diagnostic_conclusion']);
        $this->assertFileExists($this->output);
    }

    public function test_c92_rejects_missing_operator_approval(): void
    {
        $result = $this->execute(['operatorApproved' => false]);

        $this->assertSame('C92_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_COMPLETION_BOUNDARY_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $result['status']);
        $this->assertSame($result['status'], $result['reason_code']);
    }

    public function test_c92_rejects_missing_approval_reference(): void
    {
        $result = $this->execute(['approvalReference' => '']);

        $this->assertSame('C92_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_COMPLETION_BOUNDARY_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $result['status']);
        $this->assertSame($result['status'], $result['reason_code']);
    }

    public function test_c92_rejects_missing_c91_artifact(): void
    {
        $result = $this->execute([
            'c91Artifact' => 'storage/app/watchlist/backtest/.tmp-c91-missing-for-c92.json',
            'expectedC91Hash' => '17731873369cf69b5083b2f80b15101de71851f2',
            'expectedC91FileSha1' => 'D6306D0EB132FEEA5535B99AD4A4BA9099D80DF6',
        ]);

        $this->assertSame('C92_BLOCKED_C91_ARTIFACT_LOCK_MISMATCH', $result['status']);
    }

    public function test_c92_rejects_expected_c91_artifact_hash_mismatch(): void
    {
        $fixture = $this->lockedC91Fixture();
        $result = $this->execute([
            'c91Artifact' => $fixture['path'],
            'expectedC91Hash' => '0000000000000000000000000000000000000000',
            'expectedC91FileSha1' => $fixture['sha1'],
        ]);

        $this->assertSame('C92_BLOCKED_C91_ARTIFACT_LOCK_MISMATCH', $result['status']);
        $this->assertFalse($result['c91_hash_match']);
    }

    public function test_c92_rejects_expected_c91_file_sha1_mismatch(): void
    {
        $fixture = $this->lockedC91Fixture();
        $result = $this->execute([
            'c91Artifact' => $fixture['path'],
            'expectedC91Hash' => $fixture['hash'],
            'expectedC91FileSha1' => '0000000000000000000000000000000000000000',
        ]);

        $this->assertSame('C92_BLOCKED_C91_FILE_SHA1_LOCK_MISMATCH', $result['status']);
        $this->assertFalse($result['c91_file_sha1_match']);
    }

    public function test_c92_rejects_c91_status_not_passed_handoff_finalized(): void
    {
        $result = $this->mutateC91AndExecute(function (array $c91): array {
            $c91['status'] = 'BROKEN_STATUS';
            return $c91;
        }, 'c91-status-mismatch');

        $this->assertSame('C92_BLOCKED_C91_STATUS_OR_REASON_MISMATCH', $result['status']);
    }

    public function test_c92_rejects_c91_reason_code_not_passed_handoff_finalized(): void
    {
        $result = $this->mutateC91AndExecute(function (array $c91): array {
            $c91['reason_code'] = 'BROKEN_REASON';
            return $c91;
        }, 'c91-reason-mismatch');

        $this->assertSame('C92_BLOCKED_C91_STATUS_OR_REASON_MISMATCH', $result['status']);
    }

    /**
     * @dataProvider c91SafetyFlagProvider
     */
    public function test_c92_rejects_any_live_or_mutating_safety_flag_true_in_c91(string $field): void
    {
        $result = $this->mutateC91AndExecute(function (array $c91) use ($field): array {
            $c91[$field] = true;
            return $c91;
        }, 'c91-safety-'.$field);

        $this->assertSame('C92_BLOCKED_C91_SAFETY_FLAG_ALREADY_LIVE_OR_MUTATING', $result['status'], $field);
        $this->assertSame($field, $result['c91_live_or_mutating_safety_flag_failure']);
    }

    public function c91SafetyFlagProvider(): array
    {
        return [
            ['production_ready'],
            ['production_catalog_runtime_wired'],
            ['controlled_opt_in_runtime_bridge_active'],
            ['controlled_parallel_run_active'],
            ['controlled_rollout_active'],
            ['production_deployment_allowed'],
            ['production_deployment_executed'],
            ['plan_confirm_mutation_allowed'],
            ['plan_confirm_mutated'],
            ['plan_confirm_runtime_reads_activated_catalog'],
            ['live_plan_confirm_rollout_allowed'],
            ['live_plan_confirm_rollout_executed'],
        ];
    }

    public function test_c92_keeps_a01_comparator_only_and_never_promotes_it(): void
    {
        $result = $this->runService();

        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $result['comparator_candidate_code']);
        $this->assertTrue($result['c92_readiness_decision']['a01_remains_comparator_only']);
        $this->assertFalse($result['c92_readiness_decision']['comparator_candidate_boundary_cleared']);
        $this->assertFalse($result['candidate_scope_freeze_summary']['a01_promoted']);
        $this->assertFalse($result['candidate_scope_freeze_summary']['a01_used_as_runtime_fallback']);
    }

    public function test_c92_keeps_e02_primary_and_b01_backup(): void
    {
        $result = $this->runService();

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $result['primary_candidate_code']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $result['backup_candidate_code']);
        $this->assertTrue($result['c92_readiness_decision']['primary_candidate_boundary_cleared']);
        $this->assertTrue($result['c92_readiness_decision']['backup_candidate_boundary_cleared']);
    }

    public function test_c92_writes_artifact_hash(): void
    {
        $result = $this->runService();

        $this->assertArrayHasKey('artifact_hash', $result);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $run = $this->readOutput();
        $this->assertSame($result['artifact_hash'], $run['artifact_hash']);
    }

    public function test_c92_writes_c91_source_lock_top_level_aliases(): void
    {
        $result = $this->runService();

        $this->assertSame('17731873369cf69b5083b2f80b15101de71851f2', $result['expected_c91_hash']);
        $this->assertSame('17731873369cf69b5083b2f80b15101de71851f2', $result['actual_c91_hash']);
        $this->assertTrue($result['c91_hash_match']);
        $this->assertSame('D6306D0EB132FEEA5535B99AD4A4BA9099D80DF6', $result['expected_c91_file_sha1']);
        $this->assertSame('D6306D0EB132FEEA5535B99AD4A4BA9099D80DF6', $result['actual_c91_file_sha1']);
        $this->assertTrue($result['c91_file_sha1_match']);
        $this->assertSame($result['expected_c91_hash'], $result['source_artifact_locks']['expected_c91_hash']);
    }

    public function test_c92_writes_next_recommendation_c93(): void
    {
        $result = $this->runService();

        $this->assertSame('C93_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_CLOSURE_SEAL_REVIEW', $result['next_step_recommendation']);
        $this->assertSame('C93_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_CLOSURE_SEAL_REVIEW', $result['next_readiness_decision']['next_recommendation']);
        $this->assertSame('C93_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_CLOSURE_SEAL_REVIEW', $result['planned_next_summary']['planned_next_review']);
    }

    public function test_c92_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $outputA = 'storage/app/watchlist/backtest/.tmp-c92-deterministic-a.json';
        $outputB = 'storage/app/watchlist/backtest/.tmp-c92-deterministic-b.json';
        $this->tmpFiles[] = $outputA;
        $this->tmpFiles[] = $outputB;

        $first = $this->execute(['output' => $outputA, 'options' => ['created_at' => '2026-06-27T00:00:00+00:00']]);
        $second = $this->execute(['output' => $outputB, 'options' => ['created_at' => '2026-06-27T00:00:00+00:00']]);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
    }

    public function test_c92_rejects_c91_next_recommendation_mismatch(): void
    {
        $result = $this->mutateC91AndExecute(function (array $c91): array {
            $c91['next_readiness_decision']['next_recommendation'] = 'BROKEN_NEXT';
            return $c91;
        }, 'c91-next-mismatch');

        $this->assertSame('C92_BLOCKED_C91_NEXT_RECOMMENDATION_MISMATCH', $result['status']);
    }

    public function test_c92_rejects_c91_handoff_finalized_semantic_missing(): void
    {
        $result = $this->mutateC91AndExecute(function (array $c91): array {
            $c91['post_activation_handoff_finalized'] = false;
            return $c91;
        }, 'c91-handoff-not-finalized');

        $this->assertSame('C92_BLOCKED_C91_HANDOFF_NOT_FINALIZED', $result['status']);
    }

    public function test_c92_rejects_candidate_scope_change_or_a01_promotion(): void
    {
        $result = $this->mutateC91AndExecute(function (array $c91): array {
            $c91['candidate_scope_freeze_summary']['a01_promoted'] = true;
            return $c91;
        }, 'c91-a01-promoted');

        $this->assertSame('C92_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_COMPLETION_BOUNDARY_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $result['status']);
    }

    public function test_c92_records_required_sections(): void
    {
        $this->runService();
        $run = $this->readOutput();

        foreach ([
            'source_artifact_locks',
            'database_dictionary_read_summary',
            'c91_lock_validation_summary',
            'lineage_validation_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'post_activation_handoff_completion_boundary_decision',
            'c92_readiness_decision',
            'post_activation_handoff_completion_boundary_candidate_scorecard',
            'post_activation_handoff_completion_boundary_context_summary',
            'runtime_readiness_inspection_summary',
            'feature_flag_operator_approval_kill_switch_validation_summary',
            'c91_handoff_finalization_carry_forward_validation_summary',
            'post_activation_handoff_completion_boundary_governance_summary',
            'baseline_plan_confirm_non_mutation_summary',
            'production_mutation_safety_summary',
            'documentation_governance_summary',
            'next_readiness_decision',
            'failure_attribution_summary',
            'progress_summary',
            'planned_next_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }
    }

    public function test_c92_keeps_required_safety_flags_false(): void
    {
        $result = $this->runService();

        foreach ([
            'production_ready',
            'production_catalog_runtime_wired',
            'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active',
            'controlled_rollout_active',
            'post_activation_handoff_completion_boundary_context_persisted_to_live_runtime',
            'production_deployment_allowed',
            'production_deployment_executed',
            'plan_confirm_mutation_allowed',
            'plan_confirm_mutated',
            'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed',
            'live_plan_confirm_rollout_executed',
        ] as $flag) {
            $this->assertFalse($result[$flag], $flag);
            $this->assertFalse($result['production_mutation_safety_summary'][$flag], $flag);
        }
    }

    private function runService(array $overrides = []): array
    {
        return $this->execute($overrides);
    }

    private function execute(array $overrides = []): array
    {
        $service = new WatchlistBacktestC92ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationHandoffCompletionBoundaryReviewService();
        $fixture = $this->lockedC91Fixture();
        return $service->execute(
            (string) ($overrides['c91Artifact'] ?? $fixture['path']),
            (string) ($overrides['expectedC91Hash'] ?? $fixture['hash']),
            (string) ($overrides['expectedC91FileSha1'] ?? $fixture['sha1']),
            (string) ($overrides['output'] ?? $this->output),
            array_merge([
                'overwrite' => true,
                'operator_approved' => $overrides['operatorApproved'] ?? true,
                'approval_reference' => $overrides['approvalReference'] ?? 'C92_OPERATOR_APPROVED_POST_ACTIVATION_HANDOFF_COMPLETION_BOUNDARY_REVIEW_ONLY',
            ], (array) ($overrides['options'] ?? []))
        );
    }

    private function mutateC91AndExecute(callable $mutator, string $name): array
    {
        $fixture = $this->lockedC91Fixture();
        $payload = json_decode((string) file_get_contents($fixture['path']), true);
        $payload = $mutator(is_array($payload) ? $payload : []);
        $path = 'storage/app/watchlist/backtest/.tmp-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
        return $this->execute([
            'c91Artifact' => $path,
            'expectedC91Hash' => (string) ($payload['artifact_hash'] ?? ''),
            'expectedC91FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function lockedC91Fixture(): array
    {
        $path = 'storage/app/watchlist/backtest/c91-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-finalization-review.json';
        $payload = json_decode((string) file_get_contents($path), true);
        return [
            'path' => $path,
            'hash' => (string) ($payload['artifact_hash'] ?? ''),
            'sha1' => strtoupper(sha1((string) file_get_contents($path))),
        ];
    }

    private function readOutput(): array
    {
        $decoded = json_decode((string) file_get_contents($this->output), true);
        return is_array($decoded) ? $decoded : [];
    }
}

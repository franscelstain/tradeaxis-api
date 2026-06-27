<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC93ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationHandoffClosureSealReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC93ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationHandoffClosureSealReviewTest extends TestCase
{
    private string $output = 'storage/app/watchlist/backtest/.tmp-c93-test-output.json';
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

    public function test_c93_passes_with_valid_c92_lock_operator_approval_and_reference(): void
    {
        $result = $this->runService();

        $this->assertSame('C93_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_CLOSURE_SEAL_REVIEW_PASSED_CLOSURE_SEALED_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertSame($result['status'], $result['reason_code']);
        $this->assertTrue($result['post_activation_handoff_closure_seal_review_executed']);
        $this->assertTrue($result['post_activation_handoff_closure_seal_review_allowed']);
        $this->assertTrue($result['post_activation_handoff_closure_seal_review_pass']);
        $this->assertTrue($result['post_activation_handoff_closure_sealed']);
        $this->assertTrue($result['closure_sealed']);
        $this->assertTrue($result['primary_candidate_closure_sealed']);
        $this->assertTrue($result['backup_candidate_closure_sealed']);
        $this->assertFalse($result['comparator_candidate_closure_sealed']);
        $this->assertTrue($result['a01_remains_comparator_only']);
        $this->assertFalse($result['temporary_negative_artifacts_remaining']);
        $this->assertTrue($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertSame([], $result['temporary_negative_artifact_paths']);
        $this->assertSame('C94_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_REVIEW', $result['next_step_recommendation']);
        $this->assertSame('C94_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_REVIEW', $result['next_readiness_decision']['next_recommendation']);
        $this->assertSame('C93_HANDOFF_CLOSURE_SEALED_NON_LIVE_ONLY', $result['diagnostic_conclusion']);
        $this->assertFileExists($this->output);
    }

    public function test_c93_rejects_missing_operator_approval(): void
    {
        $result = $this->execute(['operatorApproved' => false]);

        $this->assertSame('C93_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_CLOSURE_SEAL_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $result['status']);
        $this->assertSame($result['status'], $result['reason_code']);
    }

    public function test_c93_rejects_missing_approval_reference(): void
    {
        $result = $this->execute(['approvalReference' => '']);

        $this->assertSame('C93_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_CLOSURE_SEAL_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $result['status']);
        $this->assertSame($result['status'], $result['reason_code']);
    }

    public function test_c93_rejects_missing_c92_artifact(): void
    {
        $result = $this->execute([
            'c92Artifact' => 'storage/app/watchlist/backtest/.tmp-c92-missing-for-c93.json',
            'expectedC92Hash' => '21ea44188d303fb3208d1d1bff864ee86aa247e5',
            'expectedC92FileSha1' => '81B5F1502258E1419BAA7E302BCB6CBABE49A822',
        ]);

        $this->assertSame('C93_BLOCKED_C92_ARTIFACT_LOCK_MISMATCH', $result['status']);
    }

    public function test_c93_rejects_expected_c92_artifact_hash_mismatch(): void
    {
        $fixture = $this->lockedC92Fixture();
        $result = $this->execute([
            'c92Artifact' => $fixture['path'],
            'expectedC92Hash' => '0000000000000000000000000000000000000000',
            'expectedC92FileSha1' => $fixture['sha1'],
        ]);

        $this->assertSame('C93_BLOCKED_C92_ARTIFACT_LOCK_MISMATCH', $result['status']);
        $this->assertFalse($result['c92_hash_match']);
    }

    public function test_c93_rejects_expected_c92_file_sha1_mismatch(): void
    {
        $fixture = $this->lockedC92Fixture();
        $result = $this->execute([
            'c92Artifact' => $fixture['path'],
            'expectedC92Hash' => $fixture['hash'],
            'expectedC92FileSha1' => '0000000000000000000000000000000000000000',
        ]);

        $this->assertSame('C93_BLOCKED_C92_FILE_SHA1_LOCK_MISMATCH', $result['status']);
        $this->assertFalse($result['c92_file_sha1_match']);
    }

    public function test_c93_rejects_c92_status_not_passed_completion_boundary(): void
    {
        $result = $this->mutateC92AndExecute(function (array $c92): array {
            $c92['status'] = 'BROKEN_STATUS';
            return $c92;
        }, 'c92-status-mismatch');

        $this->assertSame('C93_BLOCKED_C92_STATUS_OR_REASON_MISMATCH', $result['status']);
    }

    public function test_c93_rejects_c92_reason_code_not_passed_completion_boundary(): void
    {
        $result = $this->mutateC92AndExecute(function (array $c92): array {
            $c92['reason_code'] = 'BROKEN_REASON';
            return $c92;
        }, 'c92-reason-mismatch');

        $this->assertSame('C93_BLOCKED_C92_STATUS_OR_REASON_MISMATCH', $result['status']);
    }

    public function test_c93_rejects_c92_next_recommendation_not_c93(): void
    {
        $result = $this->mutateC92AndExecute(function (array $c92): array {
            $c92['next_step_recommendation'] = 'BROKEN_NEXT';
            $c92['next_readiness_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c92['c92_readiness_decision']['next_recommendation'] = 'BROKEN_NEXT';
            return $c92;
        }, 'c92-next-mismatch');

        $this->assertSame('C93_BLOCKED_C92_NEXT_RECOMMENDATION_MISMATCH', $result['status']);
    }

    /**
     * @dataProvider c92BoundaryFlagProvider
     */
    public function test_c93_rejects_c92_boundary_cleared_state_mismatch(string $field, $value): void
    {
        $result = $this->mutateC92AndExecute(function (array $c92) use ($field, $value): array {
            $c92[$field] = $value;
            $c92['c92_readiness_decision'][$field] = $value;
            return $c92;
        }, 'c92-boundary-'.$field);

        $this->assertSame('C93_BLOCKED_C92_BOUNDARY_NOT_CLEARED', $result['status'], $field);
    }

    public function c92BoundaryFlagProvider(): array
    {
        return [
            ['boundary_cleared', false],
            ['post_activation_handoff_completion_boundary_cleared', false],
            ['primary_candidate_boundary_cleared', false],
            ['backup_candidate_boundary_cleared', false],
            ['comparator_candidate_boundary_cleared', true],
            ['a01_remains_comparator_only', false],
        ];
    }

    public function test_c93_rejects_a01_comparator_only_violation(): void
    {
        $result = $this->mutateC92AndExecute(function (array $c92): array {
            $c92['candidate_scope_freeze_summary']['a01_promoted'] = true;
            return $c92;
        }, 'c92-a01-promoted');

        $this->assertSame('C93_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_CLOSURE_SEAL_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $result['status']);
    }

    /**
     * @dataProvider c92SafetyFlagProvider
     */
    public function test_c93_rejects_any_live_or_mutating_safety_flag_true_in_c92(string $field): void
    {
        $result = $this->mutateC92AndExecute(function (array $c92) use ($field): array {
            $c92[$field] = true;
            return $c92;
        }, 'c92-safety-'.$field);

        $this->assertSame('C93_BLOCKED_C92_SAFETY_FLAG_ALREADY_LIVE_OR_MUTATING', $result['status'], $field);
        $this->assertSame($field, $result['c92_live_or_mutating_safety_flag_failure']);
    }

    public function c92SafetyFlagProvider(): array
    {
        return [
            ['production_ready'],
            ['production_catalog_runtime_wired'],
            ['controlled_opt_in_runtime_bridge_active'],
            ['controlled_parallel_run_active'],
            ['controlled_rollout_active'],
            ['post_activation_handoff_closure_seal_context_persisted_to_live_runtime'],
            ['production_deployment_allowed'],
            ['production_deployment_executed'],
            ['plan_confirm_mutation_allowed'],
            ['plan_confirm_mutated'],
            ['plan_confirm_runtime_reads_activated_catalog'],
            ['live_plan_confirm_rollout_allowed'],
            ['live_plan_confirm_rollout_executed'],
            ['pilot_runtime_active'],
            ['shadow_runtime_active'],
            ['runtime_bridge_active'],
        ];
    }

    public function test_c93_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c93-no-temporary-artifact-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}'.PHP_EOL);

        $result = $this->runService();

        $this->assertSame('C93_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_CLOSURE_SEAL_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS', $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c93_keeps_a01_comparator_only_and_never_promotes_it(): void
    {
        $result = $this->runService();

        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $result['comparator_candidate_code']);
        $this->assertTrue($result['c93_readiness_decision']['a01_remains_comparator_only']);
        $this->assertFalse($result['c93_readiness_decision']['comparator_candidate_closure_sealed']);
        $this->assertFalse($result['candidate_scope_freeze_summary']['a01_promoted']);
        $this->assertFalse($result['candidate_scope_freeze_summary']['a01_used_as_runtime_fallback']);
    }

    public function test_c93_keeps_e02_primary_and_b01_backup(): void
    {
        $result = $this->runService();

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $result['primary_candidate_code']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $result['backup_candidate_code']);
        $this->assertTrue($result['c93_readiness_decision']['primary_candidate_closure_sealed']);
        $this->assertTrue($result['c93_readiness_decision']['backup_candidate_closure_sealed']);
    }

    public function test_c93_writes_artifact_hash(): void
    {
        $result = $this->runService();

        $this->assertArrayHasKey('artifact_hash', $result);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $run = $this->readOutput();
        $this->assertSame($result['artifact_hash'], $run['artifact_hash']);
    }

    public function test_c93_writes_c92_source_lock_top_level_aliases(): void
    {
        $result = $this->runService();

        $this->assertSame('21ea44188d303fb3208d1d1bff864ee86aa247e5', $result['expected_c92_hash']);
        $this->assertSame('21ea44188d303fb3208d1d1bff864ee86aa247e5', $result['actual_c92_hash']);
        $this->assertTrue($result['c92_hash_match']);
        $this->assertSame('81B5F1502258E1419BAA7E302BCB6CBABE49A822', $result['expected_c92_file_sha1']);
        $this->assertSame('81B5F1502258E1419BAA7E302BCB6CBABE49A822', $result['actual_c92_file_sha1']);
        $this->assertTrue($result['c92_file_sha1_match']);
        $this->assertSame($result['expected_c92_hash'], $result['source_artifact_locks']['expected_c92_hash']);
    }

    public function test_c93_writes_next_recommendation_c94(): void
    {
        $result = $this->runService();

        $this->assertSame('C94_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_REVIEW', $result['next_step_recommendation']);
        $this->assertSame('C94_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_REVIEW', $result['next_readiness_decision']['next_recommendation']);
        $this->assertSame('C94_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_REVIEW', $result['planned_next_summary']['planned_next_review']);
    }

    public function test_c93_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $outputA = 'storage/app/watchlist/backtest/.tmp-c93-deterministic-a.json';
        $outputB = 'storage/app/watchlist/backtest/.tmp-c93-deterministic-b.json';
        $this->tmpFiles[] = $outputA;
        $this->tmpFiles[] = $outputB;

        $first = $this->execute(['output' => $outputA, 'options' => ['created_at' => '2026-06-27T00:00:00+00:00']]);
        $second = $this->execute(['output' => $outputB, 'options' => ['created_at' => '2026-06-27T00:00:00+00:00']]);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
    }

    public function test_c93_records_required_sections(): void
    {
        $this->runService();
        $run = $this->readOutput();

        foreach ([
            'source_artifact_locks',
            'c92_lock_validation_summary',
            'lineage_validation_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'post_activation_handoff_closure_seal_decision',
            'c93_readiness_decision',
            'next_readiness_decision',
            'post_activation_handoff_closure_seal_candidate_scorecard',
            'post_activation_handoff_closure_seal_context_summary',
            'runtime_readiness_inspection_summary',
            'feature_flag_operator_approval_kill_switch_validation_summary',
            'c92_completion_boundary_carry_forward_validation_summary',
            'baseline_plan_confirm_non_mutation_summary',
            'production_mutation_safety_summary',
            'documentation_governance_summary',
            'failure_attribution_summary',
            'progress_summary',
            'planned_next_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }
    }

    public function test_c93_keeps_required_safety_flags_false(): void
    {
        $result = $this->runService();

        foreach ($this->requiredSafetyFlags() as $flag) {
            $this->assertFalse($result[$flag], $flag);
            $this->assertFalse($result['production_mutation_safety_summary'][$flag], $flag);
            $this->assertFalse($result['c93_readiness_decision'][$flag], $flag);
        }
    }

    public function test_c93_writes_temporary_negative_artifact_cleanup_fields(): void
    {
        $result = $this->runService();

        $this->assertFalse($result['temporary_negative_artifacts_remaining']);
        $this->assertTrue($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertSame([], $result['temporary_negative_artifact_paths']);
        $this->assertFalse($result['temporary_negative_artifact_guard_summary']['temporary_negative_artifacts_remaining']);
        $this->assertTrue($result['temporary_negative_artifact_guard_summary']['temporary_negative_artifact_cleanup_confirmed']);
    }

    public function test_c93_decision_objects_include_required_closure_seal_fields(): void
    {
        $result = $this->runService();

        foreach (['c93_readiness_decision', 'next_readiness_decision', 'post_activation_handoff_closure_seal_decision'] as $section) {
            $this->assertTrue($result[$section]['review_pass'], $section);
            $this->assertTrue($result[$section]['closure_sealed'], $section);
            $this->assertTrue($result[$section]['post_activation_handoff_closure_seal_review_executed'], $section);
            $this->assertTrue($result[$section]['post_activation_handoff_closure_seal_review_allowed'], $section);
            $this->assertTrue($result[$section]['post_activation_handoff_closure_seal_review_pass'], $section);
            $this->assertTrue($result[$section]['primary_candidate_closure_sealed'], $section);
            $this->assertTrue($result[$section]['backup_candidate_closure_sealed'], $section);
            $this->assertFalse($result[$section]['comparator_candidate_closure_sealed'], $section);
            $this->assertSame('C94_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_REVIEW', $result[$section]['next_recommendation'], $section);
            $this->assertSame('C93_HANDOFF_CLOSURE_SEALED_NON_LIVE_ONLY', $result[$section]['diagnostic_conclusion'], $section);
        }
    }

    public function test_c93_does_not_mutate_c92_artifact(): void
    {
        $fixture = $this->lockedC92Fixture();
        $before = strtoupper(sha1((string) file_get_contents($fixture['path'])));

        $this->runService();

        $after = strtoupper(sha1((string) file_get_contents($fixture['path'])));
        $this->assertSame($before, $after);
        $this->assertSame('81B5F1502258E1419BAA7E302BCB6CBABE49A822', $after);
    }

    private function requiredSafetyFlags(): array
    {
        return [
            'production_ready',
            'production_catalog_runtime_wired',
            'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active',
            'controlled_rollout_active',
            'post_activation_handoff_closure_seal_context_persisted_to_live_runtime',
            'production_deployment_allowed',
            'production_deployment_executed',
            'plan_confirm_mutation_allowed',
            'plan_confirm_mutated',
            'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed',
            'live_plan_confirm_rollout_executed',
            'pilot_runtime_active',
            'shadow_runtime_active',
            'runtime_bridge_active',
        ];
    }

    private function runService(array $overrides = []): array
    {
        return $this->execute($overrides);
    }

    private function execute(array $overrides = []): array
    {
        $service = new WatchlistBacktestC93ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationHandoffClosureSealReviewService();
        $fixture = $this->lockedC92Fixture();
        return $service->execute(
            (string) ($overrides['c92Artifact'] ?? $fixture['path']),
            (string) ($overrides['expectedC92Hash'] ?? $fixture['hash']),
            (string) ($overrides['expectedC92FileSha1'] ?? $fixture['sha1']),
            (string) ($overrides['output'] ?? $this->output),
            array_merge([
                'overwrite' => true,
                'operator_approved' => $overrides['operatorApproved'] ?? true,
                'approval_reference' => $overrides['approvalReference'] ?? 'C93_OPERATOR_APPROVED_POST_ACTIVATION_HANDOFF_CLOSURE_SEAL_REVIEW_ONLY',
            ], (array) ($overrides['options'] ?? []))
        );
    }

    private function mutateC92AndExecute(callable $mutator, string $name): array
    {
        $fixture = $this->lockedC92Fixture();
        $payload = json_decode((string) file_get_contents($fixture['path']), true);
        $payload = $mutator(is_array($payload) ? $payload : []);
        $path = 'storage/app/watchlist/backtest/.tmp-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
        return $this->execute([
            'c92Artifact' => $path,
            'expectedC92Hash' => (string) ($payload['artifact_hash'] ?? ''),
            'expectedC92FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function lockedC92Fixture(): array
    {
        $path = 'storage/app/watchlist/backtest/c92-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-completion-boundary-review.json';
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

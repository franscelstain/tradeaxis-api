<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC94ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC94ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveReviewTest extends TestCase
{
    private string $output = 'storage/app/watchlist/backtest/.tmp-c94-test-output.json';
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

    public function test_c94_passes_with_valid_c93_lock_operator_approval_and_reference(): void
    {
        $result = $this->runService();

        $this->assertSame('C94_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_REVIEW_PASSED_AUDIT_ARCHIVED_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertSame($result['status'], $result['reason_code']);
        $this->assertTrue($result['post_activation_audit_archive_review_executed']);
        $this->assertTrue($result['post_activation_audit_archive_review_allowed']);
        $this->assertTrue($result['post_activation_audit_archive_review_pass']);
        $this->assertTrue($result['post_activation_audit_archived']);
        $this->assertTrue($result['audit_archived']);
        $this->assertTrue($result['primary_candidate_audit_archived']);
        $this->assertTrue($result['backup_candidate_audit_archived']);
        $this->assertFalse($result['comparator_candidate_audit_archived']);
        $this->assertTrue($result['a01_remains_comparator_only']);
        $this->assertTrue($result['archive_manifest_created']);
        $this->assertFalse($result['temporary_negative_artifacts_remaining']);
        $this->assertTrue($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertSame([], $result['temporary_negative_artifact_paths']);
        $this->assertSame('C95_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_COMPLETION_REVIEW', $result['next_step_recommendation']);
        $this->assertSame('C95_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_COMPLETION_REVIEW', $result['next_readiness_decision']['next_recommendation']);
        $this->assertSame('C94_AUDIT_ARCHIVED_NON_LIVE_ONLY', $result['diagnostic_conclusion']);
        $this->assertFileExists($this->output);
    }

    public function test_c94_rejects_missing_operator_approval(): void
    {
        $result = $this->execute(['operatorApproved' => false]);

        $this->assertSame('C94_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $result['status']);
        $this->assertSame($result['status'], $result['reason_code']);
    }

    public function test_c94_rejects_missing_approval_reference(): void
    {
        $result = $this->execute(['approvalReference' => '']);

        $this->assertSame('C94_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $result['status']);
        $this->assertSame($result['status'], $result['reason_code']);
    }

    public function test_c94_rejects_missing_c93_artifact(): void
    {
        $result = $this->execute([
            'c93Artifact' => 'storage/app/watchlist/backtest/.tmp-c93-missing-for-c94.json',
            'expectedC93Hash' => 'bd19ac672c30ea183fc46534acd6e976515c3453',
            'expectedC93FileSha1' => 'F71799E201B9C71A79094D81AFF786FCACDF9E1D',
        ]);

        $this->assertSame('C94_BLOCKED_C93_ARTIFACT_LOCK_MISMATCH', $result['status']);
    }

    public function test_c94_rejects_expected_c93_artifact_hash_mismatch(): void
    {
        $fixture = $this->lockedC93Fixture();
        $result = $this->execute([
            'c93Artifact' => $fixture['path'],
            'expectedC93Hash' => '0000000000000000000000000000000000000000',
            'expectedC93FileSha1' => $fixture['sha1'],
        ]);

        $this->assertSame('C94_BLOCKED_C93_ARTIFACT_LOCK_MISMATCH', $result['status']);
        $this->assertFalse($result['c93_hash_match']);
    }

    public function test_c94_rejects_expected_c93_file_sha1_mismatch(): void
    {
        $fixture = $this->lockedC93Fixture();
        $result = $this->execute([
            'c93Artifact' => $fixture['path'],
            'expectedC93Hash' => $fixture['hash'],
            'expectedC93FileSha1' => '0000000000000000000000000000000000000000',
        ]);

        $this->assertSame('C94_BLOCKED_C93_FILE_SHA1_LOCK_MISMATCH', $result['status']);
        $this->assertFalse($result['c93_file_sha1_match']);
    }

    public function test_c94_rejects_c93_status_not_passed_closure_sealed(): void
    {
        $result = $this->mutateC93AndExecute(function (array $c93): array {
            $c93['status'] = 'BROKEN_STATUS';
            return $c93;
        }, 'c93-status-mismatch');

        $this->assertSame('C94_BLOCKED_C93_STATUS_OR_REASON_MISMATCH', $result['status']);
    }

    public function test_c94_rejects_c93_reason_code_not_passed_closure_sealed(): void
    {
        $result = $this->mutateC93AndExecute(function (array $c93): array {
            $c93['reason_code'] = 'BROKEN_REASON';
            return $c93;
        }, 'c93-reason-mismatch');

        $this->assertSame('C94_BLOCKED_C93_STATUS_OR_REASON_MISMATCH', $result['status']);
    }

    public function test_c94_rejects_c93_next_recommendation_not_c94(): void
    {
        $result = $this->mutateC93AndExecute(function (array $c93): array {
            $c93['next_step_recommendation'] = 'BROKEN_NEXT';
            $c93['next_readiness_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c93['c93_readiness_decision']['next_recommendation'] = 'BROKEN_NEXT';
            return $c93;
        }, 'c93-next-mismatch');

        $this->assertSame('C94_BLOCKED_C93_NEXT_RECOMMENDATION_MISMATCH', $result['status']);
    }

    /**
     * @dataProvider c93ClosureFlagProvider
     */
    public function test_c94_rejects_c93_closure_sealed_state_mismatch(string $field, $value): void
    {
        $result = $this->mutateC93AndExecute(function (array $c93) use ($field, $value): array {
            $c93[$field] = $value;
            $c93['c93_readiness_decision'][$field] = $value;
            return $c93;
        }, 'c93-closure-'.$field);

        $this->assertSame('C94_BLOCKED_C93_CLOSURE_NOT_SEALED', $result['status'], $field);
    }

    public function c93ClosureFlagProvider(): array
    {
        return [
            ['closure_sealed', false],
            ['post_activation_handoff_closure_sealed', false],
            ['primary_candidate_closure_sealed', false],
            ['backup_candidate_closure_sealed', false],
            ['comparator_candidate_closure_sealed', true],
            ['a01_remains_comparator_only', false],
        ];
    }

    public function test_c94_rejects_c93_temporary_negative_cleanup_violation(): void
    {
        $result = $this->mutateC93AndExecute(function (array $c93): array {
            $c93['temporary_negative_artifacts_remaining'] = true;
            $c93['temporary_negative_artifact_cleanup_confirmed'] = false;
            $c93['temporary_negative_artifact_paths'] = ['storage/app/watchlist/backtest/c93-no-stale-test.json'];
            return $c93;
        }, 'c93-temp-cleanup-mismatch');

        $this->assertSame('C94_BLOCKED_C93_CLOSURE_NOT_SEALED', $result['status']);
    }

    public function test_c94_rejects_a01_comparator_only_violation(): void
    {
        $result = $this->mutateC93AndExecute(function (array $c93): array {
            $c93['candidate_scope_freeze_summary']['a01_promoted'] = true;
            return $c93;
        }, 'c93-a01-promoted');

        $this->assertSame('C94_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $result['status']);
    }

    /**
     * @dataProvider c93SafetyFlagProvider
     */
    public function test_c94_rejects_any_live_or_mutating_safety_flag_true_in_c93(string $field): void
    {
        $result = $this->mutateC93AndExecute(function (array $c93) use ($field): array {
            $c93[$field] = true;
            return $c93;
        }, 'c93-safety-'.$field);

        $this->assertSame('C94_BLOCKED_C93_SAFETY_FLAG_ALREADY_LIVE_OR_MUTATING', $result['status'], $field);
        $this->assertSame($field, $result['c93_live_or_mutating_safety_flag_failure']);
    }

    public function c93SafetyFlagProvider(): array
    {
        return [
            ['production_ready'],
            ['production_catalog_runtime_wired'],
            ['controlled_opt_in_runtime_bridge_active'],
            ['controlled_parallel_run_active'],
            ['controlled_rollout_active'],
            ['post_activation_audit_archive_context_persisted_to_live_runtime'],
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

    public function test_c94_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c94-no-temporary-artifact-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}'.PHP_EOL);

        $result = $this->runService();

        $this->assertSame('C94_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS', $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c94_keeps_a01_comparator_only_and_never_promotes_it(): void
    {
        $result = $this->runService();

        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $result['comparator_candidate_code']);
        $this->assertTrue($result['c94_readiness_decision']['a01_remains_comparator_only']);
        $this->assertFalse($result['c94_readiness_decision']['comparator_candidate_audit_archived']);
        $this->assertFalse($result['candidate_scope_freeze_summary']['a01_promoted']);
        $this->assertFalse($result['candidate_scope_freeze_summary']['a01_used_as_runtime_fallback']);
    }

    public function test_c94_keeps_e02_primary_and_b01_backup(): void
    {
        $result = $this->runService();

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $result['primary_candidate_code']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $result['backup_candidate_code']);
        $this->assertTrue($result['c94_readiness_decision']['primary_candidate_audit_archived']);
        $this->assertTrue($result['c94_readiness_decision']['backup_candidate_audit_archived']);
    }

    public function test_c94_writes_artifact_hash_and_c93_source_lock_top_level_aliases(): void
    {
        $result = $this->runService();

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $this->assertSame('bd19ac672c30ea183fc46534acd6e976515c3453', $result['expected_c93_hash']);
        $this->assertSame('bd19ac672c30ea183fc46534acd6e976515c3453', $result['actual_c93_hash']);
        $this->assertTrue($result['c93_hash_match']);
        $this->assertSame('F71799E201B9C71A79094D81AFF786FCACDF9E1D', $result['expected_c93_file_sha1']);
        $this->assertSame('F71799E201B9C71A79094D81AFF786FCACDF9E1D', $result['actual_c93_file_sha1']);
        $this->assertTrue($result['c93_file_sha1_match']);
    }

    public function test_c94_writes_next_recommendation_c95(): void
    {
        $result = $this->runService();

        $this->assertSame('C95_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_COMPLETION_REVIEW', $result['next_step_recommendation']);
        $this->assertSame('C95_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_COMPLETION_REVIEW', $result['next_readiness_decision']['next_recommendation']);
        $this->assertSame('C95_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_COMPLETION_REVIEW', $result['planned_next_summary']['planned_next_review']);
    }

    public function test_c94_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $outputA = 'storage/app/watchlist/backtest/.tmp-c94-deterministic-a.json';
        $outputB = 'storage/app/watchlist/backtest/.tmp-c94-deterministic-b.json';
        $this->tmpFiles[] = $outputA;
        $this->tmpFiles[] = $outputB;

        $first = $this->execute(['output' => $outputA, 'options' => ['created_at' => '2026-06-27T00:00:00+00:00']]);
        $second = $this->execute(['output' => $outputB, 'options' => ['created_at' => '2026-06-27T00:00:00+00:00']]);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
    }

    public function test_c94_records_required_sections_and_safety_flags(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();

        foreach ([
            'source_artifact_locks',
            'c93_lock_validation_summary',
            'lineage_validation_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'post_activation_audit_archive_decision',
            'c94_readiness_decision',
            'next_readiness_decision',
            'post_activation_audit_archive_candidate_scorecard',
            'post_activation_audit_archive_context_summary',
            'runtime_readiness_inspection_summary',
            'feature_flag_operator_approval_kill_switch_validation_summary',
            'c93_closure_seal_carry_forward_validation_summary',
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

        foreach ($this->requiredSafetyFlags() as $flag) {
            $this->assertFalse($result[$flag], $flag);
            $this->assertFalse($result['production_mutation_safety_summary'][$flag], $flag);
            $this->assertFalse($result['c94_readiness_decision'][$flag], $flag);
        }
    }

    public function test_c94_writes_temporary_negative_artifact_cleanup_fields_and_does_not_mutate_c93_artifact(): void
    {
        $fixture = $this->lockedC93Fixture();
        $before = strtoupper(sha1((string) file_get_contents($fixture['path'])));

        $result = $this->runService();

        $this->assertFalse($result['temporary_negative_artifacts_remaining']);
        $this->assertTrue($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertSame([], $result['temporary_negative_artifact_paths']);
        $this->assertSame($before, strtoupper(sha1((string) file_get_contents($fixture['path']))));
        $this->assertSame('F71799E201B9C71A79094D81AFF786FCACDF9E1D', $before);
    }

    private function requiredSafetyFlags(): array
    {
        return [
            'production_ready',
            'production_catalog_runtime_wired',
            'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active',
            'controlled_rollout_active',
            'post_activation_audit_archive_context_persisted_to_live_runtime',
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
        $service = new WatchlistBacktestC94ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveReviewService();
        $fixture = $this->lockedC93Fixture();
        return $service->execute(
            (string) ($overrides['c93Artifact'] ?? $fixture['path']),
            (string) ($overrides['expectedC93Hash'] ?? $fixture['hash']),
            (string) ($overrides['expectedC93FileSha1'] ?? $fixture['sha1']),
            (string) ($overrides['output'] ?? $this->output),
            array_merge([
                'overwrite' => true,
                'operator_approved' => $overrides['operatorApproved'] ?? true,
                'approval_reference' => $overrides['approvalReference'] ?? 'C94_OPERATOR_APPROVED_POST_ACTIVATION_AUDIT_ARCHIVE_REVIEW_ONLY',
            ], (array) ($overrides['options'] ?? []))
        );
    }

    private function mutateC93AndExecute(callable $mutator, string $name): array
    {
        $fixture = $this->lockedC93Fixture();
        $payload = json_decode((string) file_get_contents($fixture['path']), true);
        $payload = $mutator(is_array($payload) ? $payload : []);
        $path = 'storage/app/watchlist/backtest/.tmp-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
        return $this->execute([
            'c93Artifact' => $path,
            'expectedC93Hash' => (string) ($payload['artifact_hash'] ?? ''),
            'expectedC93FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function lockedC93Fixture(): array
    {
        $path = 'storage/app/watchlist/backtest/c93-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-closure-seal-review.json';
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

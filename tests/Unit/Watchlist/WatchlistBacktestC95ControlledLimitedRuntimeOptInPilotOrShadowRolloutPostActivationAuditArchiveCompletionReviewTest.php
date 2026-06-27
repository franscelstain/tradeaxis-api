<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC95ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveCompletionReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC95ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveCompletionReviewTest extends TestCase
{
    private string $output = 'storage/app/watchlist/backtest/.tmp-c95-test-output.json';
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

    public function test_c95_passes_with_valid_c94_lock_operator_approval_and_reference(): void
    {
        $result = $this->runService();

        $this->assertSame('C95_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_COMPLETION_REVIEW_PASSED_AUDIT_ARCHIVE_COMPLETED_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertSame($result['status'], $result['reason_code']);
        $this->assertTrue($result['post_activation_audit_archive_completion_review_executed']);
        $this->assertTrue($result['post_activation_audit_archive_completion_review_allowed']);
        $this->assertTrue($result['post_activation_audit_archive_completion_review_pass']);
        $this->assertTrue($result['post_activation_audit_archive_completed']);
        $this->assertTrue($result['audit_archive_completed']);
        $this->assertTrue($result['primary_candidate_audit_archive_completed']);
        $this->assertTrue($result['backup_candidate_audit_archive_completed']);
        $this->assertFalse($result['comparator_candidate_audit_archive_completed']);
        $this->assertTrue($result['a01_remains_comparator_only']);
        $this->assertTrue($result['archive_completion_manifest_created']);
        $this->assertFalse($result['temporary_negative_artifacts_remaining']);
        $this->assertTrue($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertSame([], $result['temporary_negative_artifact_paths']);
        $this->assertSame('C96_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_CLOSURE_SEAL_REVIEW', $result['next_step_recommendation']);
        $this->assertSame('C96_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_CLOSURE_SEAL_REVIEW', $result['next_readiness_decision']['next_recommendation']);
        $this->assertSame('C95_AUDIT_ARCHIVE_COMPLETED_NON_LIVE_ONLY', $result['diagnostic_conclusion']);
        $this->assertFileExists($this->output);
    }

    public function test_c95_rejects_missing_operator_approval(): void
    {
        $result = $this->execute(['operatorApproved' => false]);

        $this->assertSame('C95_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $result['status']);
        $this->assertSame($result['status'], $result['reason_code']);
    }

    public function test_c95_rejects_missing_approval_reference(): void
    {
        $result = $this->execute(['approvalReference' => '']);

        $this->assertSame('C95_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $result['status']);
        $this->assertSame($result['status'], $result['reason_code']);
    }

    public function test_c95_rejects_missing_c94_artifact(): void
    {
        $result = $this->execute([
            'c94Artifact' => 'storage/app/watchlist/backtest/.tmp-c94-missing-for-c95.json',
            'expectedC94Hash' => '2a17baceb2e899f93fd1d658bd6a7b020ef9b252',
            'expectedC94FileSha1' => '0D81162ED0DF53DC434B2131E34106F7203119D6',
        ]);

        $this->assertSame('C95_BLOCKED_C94_ARTIFACT_LOCK_MISMATCH', $result['status']);
    }

    public function test_c95_rejects_expected_c94_artifact_hash_mismatch(): void
    {
        $fixture = $this->lockedC94Fixture();
        $result = $this->execute([
            'c94Artifact' => $fixture['path'],
            'expectedC94Hash' => '0000000000000000000000000000000000000000',
            'expectedC94FileSha1' => $fixture['sha1'],
        ]);

        $this->assertSame('C95_BLOCKED_C94_ARTIFACT_LOCK_MISMATCH', $result['status']);
        $this->assertFalse($result['c94_hash_match']);
    }

    public function test_c95_rejects_expected_c94_file_sha1_mismatch(): void
    {
        $fixture = $this->lockedC94Fixture();
        $result = $this->execute([
            'c94Artifact' => $fixture['path'],
            'expectedC94Hash' => $fixture['hash'],
            'expectedC94FileSha1' => '0000000000000000000000000000000000000000',
        ]);

        $this->assertSame('C95_BLOCKED_C94_FILE_SHA1_LOCK_MISMATCH', $result['status']);
        $this->assertFalse($result['c94_file_sha1_match']);
    }

    public function test_c95_rejects_c94_status_not_passed_audit_archived(): void
    {
        $result = $this->mutateC94AndExecute(function (array $c94): array {
            $c94['status'] = 'BROKEN_STATUS';
            return $c94;
        }, 'c94-status-mismatch');

        $this->assertSame('C95_BLOCKED_C94_STATUS_OR_REASON_MISMATCH', $result['status']);
    }

    public function test_c95_rejects_c94_reason_code_not_passed_audit_archived(): void
    {
        $result = $this->mutateC94AndExecute(function (array $c94): array {
            $c94['reason_code'] = 'BROKEN_REASON';
            return $c94;
        }, 'c94-reason-mismatch');

        $this->assertSame('C95_BLOCKED_C94_STATUS_OR_REASON_MISMATCH', $result['status']);
    }

    public function test_c95_rejects_c94_next_recommendation_not_c95(): void
    {
        $result = $this->mutateC94AndExecute(function (array $c94): array {
            $c94['next_step_recommendation'] = 'BROKEN_NEXT';
            $c94['next_readiness_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c94['c94_readiness_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c94['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $c94;
        }, 'c94-next-mismatch');

        $this->assertSame('C95_BLOCKED_C94_NEXT_RECOMMENDATION_MISMATCH', $result['status']);
    }

    /**
     * @dataProvider c94ArchiveFlagProvider
     */
    public function test_c95_rejects_c94_audit_archive_state_mismatch(string $field, $value): void
    {
        $result = $this->mutateC94AndExecute(function (array $c94) use ($field, $value): array {
            $c94[$field] = $value;
            if ($field !== 'archive_manifest_created') {
                $c94['c94_readiness_decision'][$field] = $value;
            }
            return $c94;
        }, 'c94-archive-'.$field);

        $this->assertSame('C95_BLOCKED_C94_AUDIT_ARCHIVE_NOT_COMPLETE', $result['status'], $field);
    }

    public function c94ArchiveFlagProvider(): array
    {
        return [
            ['post_activation_audit_archive_review_pass', false],
            ['post_activation_audit_archived', false],
            ['audit_archived', false],
            ['primary_candidate_audit_archived', false],
            ['backup_candidate_audit_archived', false],
            ['comparator_candidate_audit_archived', true],
            ['a01_remains_comparator_only', false],
            ['archive_manifest_created', false],
        ];
    }

    public function test_c95_rejects_c94_temporary_negative_cleanup_violation(): void
    {
        $result = $this->mutateC94AndExecute(function (array $c94): array {
            $c94['temporary_negative_artifacts_remaining'] = true;
            $c94['temporary_negative_artifact_cleanup_confirmed'] = false;
            $c94['temporary_negative_artifact_paths'] = ['storage/app/watchlist/backtest/c94-no-stale-test.json'];
            return $c94;
        }, 'c94-temp-cleanup-mismatch');

        $this->assertSame('C95_BLOCKED_C94_AUDIT_ARCHIVE_NOT_COMPLETE', $result['status']);
    }

    public function test_c95_rejects_a01_comparator_only_violation(): void
    {
        $result = $this->mutateC94AndExecute(function (array $c94): array {
            $c94['candidate_scope_freeze_summary']['a01_promoted'] = true;
            return $c94;
        }, 'c94-a01-promoted');

        $this->assertSame('C95_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $result['status']);
    }

    /**
     * @dataProvider c94SafetyFlagProvider
     */
    public function test_c95_rejects_any_live_or_mutating_safety_flag_true_in_c94(string $field): void
    {
        $result = $this->mutateC94AndExecute(function (array $c94) use ($field): array {
            $c94[$field] = true;
            return $c94;
        }, 'c94-safety-'.$field);

        $this->assertSame('C95_BLOCKED_C94_SAFETY_FLAG_ALREADY_LIVE_OR_MUTATING', $result['status'], $field);
        $this->assertSame($field, $result['c94_live_or_mutating_safety_flag_failure']);
    }

    public function c94SafetyFlagProvider(): array
    {
        return [
            ['production_ready'],
            ['production_catalog_runtime_wired'],
            ['controlled_opt_in_runtime_bridge_active'],
            ['controlled_parallel_run_active'],
            ['controlled_rollout_active'],
            ['post_activation_audit_archive_context_persisted_to_live_runtime'],
            ['post_activation_audit_archive_completion_context_persisted_to_live_runtime'],
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

    public function test_c95_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c95-no-temporary-artifact-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}'.PHP_EOL);

        $result = $this->runService();

        $this->assertSame('C95_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS', $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c95_keeps_a01_comparator_only_and_never_promotes_it(): void
    {
        $result = $this->runService();

        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $result['comparator_candidate_code']);
        $this->assertTrue($result['c95_readiness_decision']['a01_remains_comparator_only']);
        $this->assertFalse($result['c95_readiness_decision']['comparator_candidate_audit_archive_completed']);
        $this->assertFalse($result['candidate_scope_freeze_summary']['a01_promoted']);
        $this->assertFalse($result['candidate_scope_freeze_summary']['a01_used_as_runtime_fallback']);
    }

    public function test_c95_keeps_e02_primary_and_b01_backup(): void
    {
        $result = $this->runService();

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $result['primary_candidate_code']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $result['backup_candidate_code']);
        $this->assertTrue($result['c95_readiness_decision']['primary_candidate_audit_archive_completed']);
        $this->assertTrue($result['c95_readiness_decision']['backup_candidate_audit_archive_completed']);
    }

    public function test_c95_writes_artifact_hash_and_c94_source_lock_top_level_aliases(): void
    {
        $result = $this->runService();

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $this->assertSame('2a17baceb2e899f93fd1d658bd6a7b020ef9b252', $result['expected_c94_hash']);
        $this->assertSame('2a17baceb2e899f93fd1d658bd6a7b020ef9b252', $result['actual_c94_hash']);
        $this->assertTrue($result['c94_hash_match']);
        $this->assertSame('0D81162ED0DF53DC434B2131E34106F7203119D6', $result['expected_c94_file_sha1']);
        $this->assertSame('0D81162ED0DF53DC434B2131E34106F7203119D6', $result['actual_c94_file_sha1']);
        $this->assertTrue($result['c94_file_sha1_match']);
    }

    public function test_c95_writes_next_recommendation_c96(): void
    {
        $result = $this->runService();

        $this->assertSame('C96_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_CLOSURE_SEAL_REVIEW', $result['next_step_recommendation']);
        $this->assertSame('C96_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_CLOSURE_SEAL_REVIEW', $result['next_readiness_decision']['next_recommendation']);
        $this->assertSame('C96_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_CLOSURE_SEAL_REVIEW', $result['planned_next_summary']['planned_next_review']);
    }

    public function test_c95_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $outputA = 'storage/app/watchlist/backtest/.tmp-c95-deterministic-a.json';
        $outputB = 'storage/app/watchlist/backtest/.tmp-c95-deterministic-b.json';
        $this->tmpFiles[] = $outputA;
        $this->tmpFiles[] = $outputB;

        $first = $this->execute(['output' => $outputA, 'options' => ['created_at' => '2026-06-27T00:00:00+00:00']]);
        $second = $this->execute(['output' => $outputB, 'options' => ['created_at' => '2026-06-27T00:00:00+00:00']]);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
    }

    public function test_c95_records_required_sections_and_safety_flags(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();

        foreach ([
            'source_artifact_locks',
            'c94_lock_validation_summary',
            'lineage_validation_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'post_activation_audit_archive_completion_decision',
            'c95_readiness_decision',
            'next_readiness_decision',
            'post_activation_audit_archive_completion_candidate_scorecard',
            'post_activation_audit_archive_completion_context_summary',
            'runtime_readiness_inspection_summary',
            'feature_flag_operator_approval_kill_switch_validation_summary',
            'c94_audit_archive_carry_forward_validation_summary',
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
            $this->assertFalse($result['c95_readiness_decision'][$flag], $flag);
        }
    }

    public function test_c95_writes_temporary_negative_artifact_cleanup_fields_and_does_not_mutate_c94_artifact(): void
    {
        $fixture = $this->lockedC94Fixture();
        $before = strtoupper(sha1((string) file_get_contents($fixture['path'])));

        $result = $this->runService();

        $this->assertFalse($result['temporary_negative_artifacts_remaining']);
        $this->assertTrue($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertSame([], $result['temporary_negative_artifact_paths']);
        $this->assertSame($before, strtoupper(sha1((string) file_get_contents($fixture['path']))));
        $this->assertSame('0D81162ED0DF53DC434B2131E34106F7203119D6', $before);
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
            'post_activation_audit_archive_completion_context_persisted_to_live_runtime',
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
        $service = new WatchlistBacktestC95ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveCompletionReviewService();
        $fixture = $this->lockedC94Fixture();
        return $service->execute(
            (string) ($overrides['c94Artifact'] ?? $fixture['path']),
            (string) ($overrides['expectedC94Hash'] ?? $fixture['hash']),
            (string) ($overrides['expectedC94FileSha1'] ?? $fixture['sha1']),
            (string) ($overrides['output'] ?? $this->output),
            array_merge([
                'overwrite' => true,
                'operator_approved' => $overrides['operatorApproved'] ?? true,
                'approval_reference' => $overrides['approvalReference'] ?? 'C95_OPERATOR_APPROVED_POST_ACTIVATION_AUDIT_ARCHIVE_COMPLETION_REVIEW_ONLY',
            ], (array) ($overrides['options'] ?? []))
        );
    }

    private function mutateC94AndExecute(callable $mutator, string $name): array
    {
        $fixture = $this->lockedC94Fixture();
        $payload = json_decode((string) file_get_contents($fixture['path']), true);
        $payload = $mutator(is_array($payload) ? $payload : []);
        $path = 'storage/app/watchlist/backtest/.tmp-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
        return $this->execute([
            'c94Artifact' => $path,
            'expectedC94Hash' => (string) ($payload['artifact_hash'] ?? ''),
            'expectedC94FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function lockedC94Fixture(): array
    {
        $path = 'storage/app/watchlist/backtest/c94-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-review.json';
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

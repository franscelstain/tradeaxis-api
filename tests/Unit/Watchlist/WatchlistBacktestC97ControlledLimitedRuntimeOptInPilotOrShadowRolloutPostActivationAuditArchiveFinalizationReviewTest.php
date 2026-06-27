<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC97ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveFinalizationReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC97ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveFinalizationReviewTest extends TestCase
{
    private string $output = 'storage/app/watchlist/backtest/.tmp-c97-test-output.json';
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

    public function test_c97_passes_with_valid_c96_lock_operator_approval_and_reference(): void
    {
        $result = $this->runService();

        $this->assertSame('C97_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_FINALIZATION_REVIEW_PASSED_AUDIT_ARCHIVE_FINALIZED_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertSame($result['status'], $result['reason_code']);
        $this->assertTrue($result['audit_archive_finalization_review_executed']);
        $this->assertTrue($result['audit_archive_finalization_review_allowed']);
        $this->assertTrue($result['audit_archive_finalization_review_pass']);
        $this->assertTrue($result['audit_archive_finalized']);
        $this->assertTrue($result['primary_candidate_audit_archive_finalized']);
        $this->assertTrue($result['backup_candidate_audit_archive_finalized']);
        $this->assertFalse($result['comparator_candidate_audit_archive_finalized']);
        $this->assertTrue($result['a01_remains_comparator_only']);
        $this->assertTrue($result['c96_audit_archive_closure_sealed']);
        $this->assertTrue($result['audit_archive_finalization_manifest_created']);
        $this->assertFalse($result['temporary_negative_artifacts_remaining']);
        $this->assertTrue($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertSame([], $result['temporary_negative_artifact_paths']);
        $this->assertSame('C98_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_REVIEW', $result['next_step_recommendation']);
        $this->assertSame('C98_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_REVIEW', $result['next_readiness_decision']['next_recommendation']);
        $this->assertSame('C97_AUDIT_ARCHIVE_FINALIZED_NON_LIVE_ONLY', $result['diagnostic_conclusion']);
        $this->assertFileExists($this->output);
    }

    public function test_c97_rejects_missing_operator_approval(): void
    {
        $result = $this->execute(['operatorApproved' => false]);

        $this->assertSame('C97_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $result['status']);
        $this->assertSame($result['status'], $result['reason_code']);
    }

    public function test_c97_rejects_missing_approval_reference(): void
    {
        $result = $this->execute(['approvalReference' => '']);

        $this->assertSame('C97_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $result['status']);
        $this->assertSame($result['status'], $result['reason_code']);
    }

    public function test_c97_rejects_missing_c96_artifact(): void
    {
        $result = $this->execute([
            'c96Artifact' => 'storage/app/watchlist/backtest/.tmp-c96-missing-for-c97.json',
            'expectedC96Hash' => '970152d11467ea83c80eca83081d6ae81beec38b',
            'expectedC96FileSha1' => 'CCD6B92B52745B928C48BF349BC7004E755B1EB6',
        ]);

        $this->assertSame('C97_BLOCKED_C96_ARTIFACT_LOCK_MISMATCH', $result['status']);
    }

    public function test_c97_rejects_expected_c96_artifact_hash_mismatch(): void
    {
        $fixture = $this->lockedC96Fixture();
        $result = $this->execute([
            'c96Artifact' => $fixture['path'],
            'expectedC96Hash' => '0000000000000000000000000000000000000000',
            'expectedC96FileSha1' => $fixture['sha1'],
        ]);

        $this->assertSame('C97_BLOCKED_C96_ARTIFACT_LOCK_MISMATCH', $result['status']);
        $this->assertFalse($result['c96_hash_match']);
    }

    public function test_c97_rejects_expected_c96_file_sha1_mismatch(): void
    {
        $fixture = $this->lockedC96Fixture();
        $result = $this->execute([
            'c96Artifact' => $fixture['path'],
            'expectedC96Hash' => $fixture['hash'],
            'expectedC96FileSha1' => '0000000000000000000000000000000000000000',
        ]);

        $this->assertSame('C97_BLOCKED_C96_FILE_SHA1_LOCK_MISMATCH', $result['status']);
        $this->assertFalse($result['c96_file_sha1_match']);
    }

    public function test_c97_rejects_c96_status_not_passed_audit_archive_closure_sealed(): void
    {
        $result = $this->mutateC96AndExecute(function (array $c96): array {
            $c96['status'] = 'BROKEN_STATUS';
            return $c96;
        }, 'c96-status-mismatch');

        $this->assertSame('C97_BLOCKED_C96_STATUS_OR_REASON_MISMATCH', $result['status']);
    }

    public function test_c97_rejects_c96_reason_code_not_passed_audit_archive_closure_sealed(): void
    {
        $result = $this->mutateC96AndExecute(function (array $c96): array {
            $c96['reason_code'] = 'BROKEN_REASON';
            return $c96;
        }, 'c96-reason-mismatch');

        $this->assertSame('C97_BLOCKED_C96_STATUS_OR_REASON_MISMATCH', $result['status']);
    }

    public function test_c97_rejects_c96_next_recommendation_not_c97(): void
    {
        $result = $this->mutateC96AndExecute(function (array $c96): array {
            $c96['next_step_recommendation'] = 'BROKEN_NEXT';
            $c96['next_readiness_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c96['c96_readiness_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c96['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $c96;
        }, 'c96-next-mismatch');

        $this->assertSame('C97_BLOCKED_C96_NEXT_RECOMMENDATION_MISMATCH', $result['status']);
    }

    /**
     * @dataProvider c96ClosureSealFlagProvider
     */
    public function test_c97_rejects_c96_audit_archive_closure_seal_state_mismatch(string $field, $value): void
    {
        $result = $this->mutateC96AndExecute(function (array $c96) use ($field, $value): array {
            $c96[$field] = $value;
            if ($field !== 'closure_seal_manifest_created') {
                $c96['c96_readiness_decision'][$field] = $value;
            }
            return $c96;
        }, 'c96-closure-'.$field);

        $this->assertSame('C97_BLOCKED_C96_AUDIT_ARCHIVE_CLOSURE_SEAL_NOT_COMPLETE', $result['status'], $field);
    }

    public function c96ClosureSealFlagProvider(): array
    {
        return [
            ['post_activation_audit_archive_closure_seal_review_pass', false],
            ['post_activation_audit_archive_closure_sealed', false],
            ['audit_archive_closure_sealed', false],
            ['closure_sealed', false],
            ['primary_candidate_audit_archive_closure_sealed', false],
            ['primary_candidate_archive_closure_sealed', false],
            ['backup_candidate_audit_archive_closure_sealed', false],
            ['backup_candidate_archive_closure_sealed', false],
            ['comparator_candidate_audit_archive_closure_sealed', true],
            ['comparator_candidate_archive_closure_sealed', true],
            ['a01_remains_comparator_only', false],
            ['closure_seal_manifest_created', false],
        ];
    }

    public function test_c97_rejects_c96_temporary_negative_cleanup_violation(): void
    {
        $result = $this->mutateC96AndExecute(function (array $c96): array {
            $c96['temporary_negative_artifacts_remaining'] = true;
            $c96['temporary_negative_artifact_cleanup_confirmed'] = false;
            $c96['temporary_negative_artifact_paths'] = ['storage/app/watchlist/backtest/c96-no-stale-test.json'];
            return $c96;
        }, 'c96-temp-cleanup-mismatch');

        $this->assertSame('C97_BLOCKED_C96_AUDIT_ARCHIVE_CLOSURE_SEAL_NOT_COMPLETE', $result['status']);
    }

    public function test_c97_rejects_a01_comparator_only_violation(): void
    {
        $result = $this->mutateC96AndExecute(function (array $c96): array {
            $c96['candidate_scope_freeze_summary']['a01_promoted'] = true;
            return $c96;
        }, 'c96-a01-promoted');

        $this->assertSame('C97_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_FINALIZATION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $result['status']);
    }

    /**
     * @dataProvider c96SafetyFlagProvider
     */
    public function test_c97_rejects_any_live_or_mutating_safety_flag_true_in_c96(string $field): void
    {
        $result = $this->mutateC96AndExecute(function (array $c96) use ($field): array {
            $c96[$field] = true;
            return $c96;
        }, 'c96-safety-'.$field);

        $this->assertSame('C97_BLOCKED_C96_SAFETY_FLAG_ALREADY_LIVE_OR_MUTATING', $result['status'], $field);
        $this->assertSame($field, $result['c96_live_or_mutating_safety_flag_failure']);
    }

    public function c96SafetyFlagProvider(): array
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
            ['pilot_runtime_active'],
            ['shadow_runtime_active'],
            ['runtime_bridge_active'],
            ['weekly_swing_watchlist_runtime_active'],
            ['weekly_swing_watchlist_plan_confirm_mutation_allowed'],
            ['weekly_swing_watchlist_live_output_enabled'],
        ];
    }

    public function test_c97_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c97-no-temporary-artifact-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}'.PHP_EOL);

        $result = $this->runService();

        $this->assertSame('C97_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_FINALIZATION_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS', $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c97_keeps_a01_comparator_only_and_never_promotes_it(): void
    {
        $result = $this->runService();

        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $result['comparator_candidate_code']);
        $this->assertTrue($result['c97_readiness_decision']['a01_remains_comparator_only']);
        $this->assertFalse($result['c97_readiness_decision']['comparator_candidate_audit_archive_finalized']);
        $this->assertFalse($result['candidate_scope_freeze_summary']['a01_promoted']);
        $this->assertFalse($result['candidate_scope_freeze_summary']['a01_used_as_runtime_fallback']);
    }

    public function test_c97_keeps_e02_primary_and_b01_backup(): void
    {
        $result = $this->runService();

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $result['primary_candidate_code']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $result['backup_candidate_code']);
        $this->assertTrue($result['c97_readiness_decision']['primary_candidate_audit_archive_finalized']);
        $this->assertTrue($result['c97_readiness_decision']['backup_candidate_audit_archive_finalized']);
    }

    public function test_c97_writes_artifact_hash_and_c96_source_lock_top_level_aliases(): void
    {
        $result = $this->runService();

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $this->assertSame('970152d11467ea83c80eca83081d6ae81beec38b', $result['expected_c96_hash']);
        $this->assertSame('970152d11467ea83c80eca83081d6ae81beec38b', $result['actual_c96_hash']);
        $this->assertTrue($result['c96_hash_match']);
        $this->assertSame('CCD6B92B52745B928C48BF349BC7004E755B1EB6', $result['expected_c96_file_sha1']);
        $this->assertSame('CCD6B92B52745B928C48BF349BC7004E755B1EB6', $result['actual_c96_file_sha1']);
        $this->assertTrue($result['c96_file_sha1_match']);
    }

    public function test_c97_writes_next_recommendation_c98(): void
    {
        $result = $this->runService();

        $this->assertSame('C98_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_REVIEW', $result['next_step_recommendation']);
        $this->assertSame('C98_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_REVIEW', $result['next_readiness_decision']['next_recommendation']);
        $this->assertSame('C98_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_REVIEW', $result['planned_next_summary']['planned_next_review']);
    }

    public function test_c97_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $outputA = 'storage/app/watchlist/backtest/.tmp-c97-deterministic-a.json';
        $outputB = 'storage/app/watchlist/backtest/.tmp-c97-deterministic-b.json';
        $this->tmpFiles[] = $outputA;
        $this->tmpFiles[] = $outputB;

        $first = $this->execute(['output' => $outputA, 'options' => ['created_at' => '2026-06-27T00:00:00+00:00']]);
        $second = $this->execute(['output' => $outputB, 'options' => ['created_at' => '2026-06-27T00:00:00+00:00']]);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
    }

    public function test_c97_records_required_sections_and_safety_flags(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();

        foreach ([
            'source_artifact_locks',
            'c96_lock_validation_summary',
            'lineage_validation_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'audit_archive_finalization_decision',
            'c97_readiness_decision',
            'next_readiness_decision',
            'audit_archive_finalization_candidate_scorecard',
            'audit_archive_finalization_context_summary',
            'runtime_readiness_inspection_summary',
            'feature_flag_operator_approval_kill_switch_validation_summary',
            'c96_audit_archive_closure_seal_carry_forward_validation_summary',
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
            $this->assertFalse($result['c97_readiness_decision'][$flag], $flag);
        }
    }

    public function test_c97_writes_temporary_negative_artifact_cleanup_fields_and_does_not_mutate_c96_artifact(): void
    {
        $fixture = $this->lockedC96Fixture();
        $before = strtoupper(sha1((string) file_get_contents($fixture['path'])));

        $result = $this->runService();

        $this->assertFalse($result['temporary_negative_artifacts_remaining']);
        $this->assertTrue($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertSame([], $result['temporary_negative_artifact_paths']);
        $this->assertSame($before, strtoupper(sha1((string) file_get_contents($fixture['path']))));
        $this->assertSame('CCD6B92B52745B928C48BF349BC7004E755B1EB6', $before);
    }

    public function test_c97_keeps_weekly_swing_non_live_next_phase_flags_false(): void
    {
        $result = $this->runService();

        $this->assertFalse($result['weekly_swing_watchlist_runtime_active']);
        $this->assertFalse($result['weekly_swing_watchlist_plan_confirm_mutation_allowed']);
        $this->assertFalse($result['weekly_swing_watchlist_live_output_enabled']);
        $this->assertFalse($result['runtime_readiness_inspection_summary']['weekly_swing_watchlist_runtime_active']);
        $this->assertFalse($result['runtime_readiness_inspection_summary']['weekly_swing_watchlist_live_output_enabled']);
    }

    public function test_c97_does_not_mutate_c60_through_c96_artifacts(): void
    {
        $paths = glob('storage/app/watchlist/backtest/c{60,61,62,63,64,65,66,67,68,69,70,71,72,73,74,75,76,77,78,79,80,81,82,83,84,85,86,87,88,89,90,91,92,93,94,95,96}*.json', GLOB_BRACE) ?: [];
        $before = [];
        foreach ($paths as $path) {
            $before[$path] = strtoupper(sha1((string) file_get_contents($path)));
        }

        $this->runService();

        foreach ($before as $path => $sha1) {
            $this->assertSame($sha1, strtoupper(sha1((string) file_get_contents($path))), $path);
        }
    }

    private function requiredSafetyFlags(): array
    {
        return [
            'production_ready',
            'production_catalog_runtime_wired',
            'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active',
            'controlled_rollout_active',
            'audit_archive_finalization_context_persisted_to_live_runtime',
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
            'weekly_swing_watchlist_runtime_active',
            'weekly_swing_watchlist_plan_confirm_mutation_allowed',
            'weekly_swing_watchlist_live_output_enabled',
        ];
    }

    private function runService(array $overrides = []): array
    {
        return $this->execute($overrides);
    }

    private function execute(array $overrides = []): array
    {
        $service = new WatchlistBacktestC97ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveFinalizationReviewService();
        $fixture = $this->lockedC96Fixture();
        return $service->execute(
            (string) ($overrides['c96Artifact'] ?? $fixture['path']),
            (string) ($overrides['expectedC96Hash'] ?? $fixture['hash']),
            (string) ($overrides['expectedC96FileSha1'] ?? $fixture['sha1']),
            (string) ($overrides['output'] ?? $this->output),
            array_merge([
                'overwrite' => true,
                'operator_approved' => $overrides['operatorApproved'] ?? true,
                'approval_reference' => $overrides['approvalReference'] ?? 'C97_OPERATOR_APPROVED_AUDIT_ARCHIVE_FINALIZATION_REVIEW_ONLY',
            ], (array) ($overrides['options'] ?? []))
        );
    }

    private function mutateC96AndExecute(callable $mutator, string $name): array
    {
        $fixture = $this->lockedC96Fixture();
        $payload = json_decode((string) file_get_contents($fixture['path']), true);
        $payload = $mutator(is_array($payload) ? $payload : []);
        $path = 'storage/app/watchlist/backtest/.tmp-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
        return $this->execute([
            'c96Artifact' => $path,
            'expectedC96Hash' => (string) ($payload['artifact_hash'] ?? ''),
            'expectedC96FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function lockedC96Fixture(): array
    {
        $path = 'storage/app/watchlist/backtest/c96-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-closure-seal-review.json';
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

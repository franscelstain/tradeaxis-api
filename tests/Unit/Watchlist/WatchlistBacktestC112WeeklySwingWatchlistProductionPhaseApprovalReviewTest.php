<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC112WeeklySwingWatchlistProductionPhaseApprovalReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC112WeeklySwingWatchlistProductionPhaseApprovalReviewTest extends TestCase
{
    private const PASS_STATUS = 'C112_WEEKLY_SWING_WATCHLIST_PRODUCTION_PHASE_APPROVAL_REVIEW_PASSED_PRODUCTION_PHASE_APPROVED_FOR_READINESS_REVIEW_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C112_WEEKLY_SWING_WATCHLIST_PRODUCTION_PHASE_APPROVAL_REVIEW_REJECTED_NEW_PRODUCTION_APPROVAL_MISSING';
    private const TEMP_NEGATIVE_STATUS = 'C112_WEEKLY_SWING_WATCHLIST_PRODUCTION_PHASE_APPROVAL_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const NEXT_C113 = 'C113_WEEKLY_SWING_WATCHLIST_PRODUCTION_READINESS_REVIEW';
    private const C111_HASH = '8f7c8b81eb401bfdd70f62f90779db63fc4af56d';
    private const C111_SHA1 = 'D58C10185970C9344F6EB3818A5A31C75C876842';

    private string $output;

    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c112-production-phase-approval-pass.json';
        @unlink($this->output);
    }

    protected function tearDown(): void
    {
        @unlink($this->output);
        foreach ($this->tmpFiles as $path) {
            @unlink($path);
        }
        parent::tearDown();
    }

    public function test_c112_passes_with_valid_c111_final_closure_lock_and_new_approval(): void
    {
        $result = $this->runService();

        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame($result['status'], $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_production_phase_approval_review_executed']);
        $this->assertTrue($result['weekly_swing_watchlist_production_phase_approval_review_allowed']);
        $this->assertTrue($result['weekly_swing_watchlist_production_phase_approval_review_pass']);
        $this->assertTrue($result['weekly_swing_watchlist_production_phase_opened']);
        $this->assertTrue($result['production_phase_approval_granted']);
        $this->assertTrue($result['production_readiness_review_allowed']);
        $this->assertTrue($result['primary_candidate_production_phase_approval_granted']);
        $this->assertTrue($result['backup_candidate_production_phase_approval_granted']);
        $this->assertFalse($result['comparator_candidate_production_phase_approval_granted']);
        $this->assertTrue($result['c111_handoff_audit_archive_final_closed']);
        $this->assertTrue($result['c111_audit_archive_final_closed']);
        $this->assertTrue($result['c111_final_closure_manifest_created']);
        $this->assertTrue($result['a01_remains_comparator_only']);
        $this->assertFalse($result['production_ready']);
        $this->assertFalse($result['production_catalog_runtime_wired']);
        $this->assertFalse($result['production_runtime_wiring_allowed']);
        $this->assertFalse($result['production_runtime_wiring_executed']);
        $this->assertFalse($result['plan_confirm_mutation_allowed']);
        $this->assertFalse($result['weekly_swing_watchlist_live_output_enabled']);
        $this->assertFalse($result['weekly_swing_watchlist_live_recommendation_generated']);
        $this->assertSame(self::NEXT_C113, $result['next_step_recommendation']);
        $this->assertSame('C112_NEW_PRODUCTION_PHASE_APPROVED_FOR_READINESS_REVIEW_ONLY', $result['diagnostic_conclusion']);
        $this->assertFileExists($this->output);
    }

    public function test_c112_rejects_missing_new_operator_approval(): void
    {
        $result = $this->execute(['operatorApproved' => false]);

        $this->assertSame(self::APPROVAL_MISSING_STATUS, $result['status']);
        $this->assertSame(self::APPROVAL_MISSING_STATUS, $result['reason_code']);
    }

    public function test_c112_rejects_missing_new_approval_reference(): void
    {
        $result = $this->execute(['approvalReference' => '']);

        $this->assertSame(self::APPROVAL_MISSING_STATUS, $result['status']);
        $this->assertSame(self::APPROVAL_MISSING_STATUS, $result['reason_code']);
    }

    public function test_c112_rejects_missing_or_mismatched_c111_artifact_lock(): void
    {
        $missing = $this->execute([
            'c111Artifact' => 'storage/app/watchlist/backtest/c112-source-does-not-exist.json',
            'expectedC111Hash' => 'missing',
            'expectedC111FileSha1' => 'missing',
        ]);
        $hashMismatch = $this->execute(['expectedC111Hash' => 'bad-hash']);
        $shaMismatch = $this->execute(['expectedC111FileSha1' => 'BADSHA1']);

        $this->assertSame('C112_BLOCKED_C111_ARTIFACT_LOCK_MISMATCH', $missing['status']);
        $this->assertSame('C112_BLOCKED_C111_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C112_BLOCKED_C111_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
    }

    public function test_c112_rejects_c111_status_reason_or_terminal_recommendation_mismatch(): void
    {
        $status = $this->mutateC111AndExecute(function (array $c111): array {
            $c111['status'] = 'BROKEN_STATUS';
            return $c111;
        }, 'status-broken');
        $reason = $this->mutateC111AndExecute(function (array $c111): array {
            $c111['reason_code'] = 'BROKEN_REASON';
            return $c111;
        }, 'reason-broken');
        $terminal = $this->mutateC111AndExecute(function (array $c111): array {
            $c111['next_step_recommendation'] = 'BROKEN_NEXT';
            $c111['next_readiness_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c111['c111_readiness_decision']['next_recommendation'] = 'BROKEN_NEXT';
            return $c111;
        }, 'terminal-broken');

        $this->assertSame('C112_BLOCKED_C111_STATUS_MISMATCH', $status['status']);
        $this->assertSame('C112_BLOCKED_C111_REASON_CODE_MISMATCH', $reason['status']);
        $this->assertSame('C112_BLOCKED_C111_TERMINAL_RECOMMENDATION_MISMATCH', $terminal['status']);
    }

    /**
     * @dataProvider c111FinalClosureStateMismatchProvider
     */
    public function test_c112_rejects_c111_final_closure_state_mismatch(string $field, $value): void
    {
        $result = $this->mutateC111AndExecute(function (array $c111) use ($field, $value): array {
            $c111[$field] = $value;
            return $c111;
        }, 'state-'.$field);

        $this->assertSame('C112_WEEKLY_SWING_WATCHLIST_PRODUCTION_PHASE_APPROVAL_REVIEW_REJECTED_C111_FINAL_CLOSURE_INCOMPLETE', $result['status'], $field);
    }

    public function c111FinalClosureStateMismatchProvider(): array
    {
        return [
            ['weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_final_closure_review_pass', false],
            ['weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_final_closed', false],
            ['handoff_audit_archive_final_closed', false],
            ['audit_archive_final_closed', false],
            ['final_closure_manifest_created', false],
            ['primary_candidate_handoff_audit_archive_final_closed', false],
            ['backup_candidate_handoff_audit_archive_final_closed', false],
            ['comparator_candidate_handoff_audit_archive_final_closed', true],
            ['a01_remains_comparator_only', false],
        ];
    }

    public function test_c112_rejects_candidate_scope_change_or_a01_promotion(): void
    {
        $primary = $this->mutateC111AndExecute(function (array $c111): array {
            $c111['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $c111;
        }, 'candidate-primary-broken');
        $a01 = $this->mutateC111AndExecute(function (array $c111): array {
            $c111['a01_promoted'] = true;
            return $c111;
        }, 'candidate-a01-promoted');

        $this->assertSame('C112_WEEKLY_SWING_WATCHLIST_PRODUCTION_PHASE_APPROVAL_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $primary['status']);
        $this->assertSame('C112_WEEKLY_SWING_WATCHLIST_PRODUCTION_PHASE_APPROVAL_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $a01['status']);
    }

    /**
     * @dataProvider safetyFlagProvider
     */
    public function test_c112_rejects_any_live_or_mutating_safety_flag_true_in_c111(string $field): void
    {
        $result = $this->mutateC111AndExecute(function (array $c111) use ($field): array {
            $c111[$field] = true;
            return $c111;
        }, 'safety-'.$field);

        $this->assertSame('C112_WEEKLY_SWING_WATCHLIST_PRODUCTION_PHASE_APPROVAL_REVIEW_REJECTED_LIVE_OR_PRODUCTION_MUTATION_ATTEMPT', $result['status'], $field);
        $this->assertSame($field, $result['c111_live_or_mutating_safety_flag_failure']);
    }

    public function safetyFlagProvider(): array
    {
        return array_map(static function (string $flag): array {
            return [$flag];
        }, $this->requiredSafetyFlags());
    }

    public function test_c112_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c112-no-temporary-artifact-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame(self::TEMP_NEGATIVE_STATUS, $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c112_records_source_locks_sections_manifest_and_no_live_output(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();
        $manifest = $result['weekly_swing_watchlist_production_phase_approval_manifest'];

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $this->assertSame(self::C111_HASH, $result['expected_c111_hash']);
        $this->assertSame(self::C111_HASH, $result['actual_c111_hash']);
        $this->assertTrue($result['c111_hash_match']);
        $this->assertSame(self::C111_SHA1, $result['expected_c111_file_sha1']);
        $this->assertSame(self::C111_SHA1, $result['actual_c111_file_sha1']);
        $this->assertTrue($result['c111_file_sha1_match']);
        $this->assertSame(self::NEXT_C113, $result['next_readiness_decision']['next_recommendation']);

        foreach ([
            'source_artifact_locks',
            'c111_lock_validation_summary',
            'c111_final_closure_carry_forward_summary',
            'candidate_scope_freeze_summary',
            'new_production_approval_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'c112_readiness_decision',
            'next_readiness_decision',
            'weekly_swing_watchlist_production_phase_approval_manifest',
            'c112_candidate_production_phase_approval_scorecard',
            'production_phase_approval_context_summary',
            'runtime_readiness_inspection_summary',
            'production_mutation_safety_summary',
            'failure_attribution_summary',
            'progress_summary',
            'planned_next_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }

        $this->assertTrue($manifest['production_phase_approval_artifact_only']);
        $this->assertTrue($manifest['production_readiness_review_allowed']);
        $this->assertFalse($manifest['production_runtime_wiring_allowed']);
        $this->assertFalse($manifest['production_deployment_allowed']);
        $this->assertFalse($manifest['weekly_swing_live_output_enabled']);
        $this->assertFalse($manifest['production_phase_approval_used_for_plan_confirm_mutation']);
        $this->assertFalse($result['weekly_swing_watchlist_live_output_enabled']);
        $this->assertFalse($result['weekly_swing_watchlist_live_recommendation_generated']);
    }

    public function test_c112_keeps_all_safety_flags_false(): void
    {
        $result = $this->runService();

        foreach ($this->requiredSafetyFlags() as $flag) {
            $this->assertFalse($result[$flag], $flag);
            $this->assertFalse($result['production_mutation_safety_summary'][$flag], $flag);
        }
        $this->assertFalse($result['c112_readiness_decision']['production_ready']);
        $this->assertFalse($result['c112_readiness_decision']['production_deployment_allowed']);
        $this->assertFalse($result['production_phase_approval_context_summary']['context_persisted_to_live_runtime']);
    }

    public function test_c112_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $first = $this->execute(['options' => ['created_at' => '2026-06-30T00:00:00+00:00']]);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c112-production-phase-approval-pass-second.json';
        $this->tmpFiles[] = $secondOutput;
        $second = $this->execute([
            'output' => $secondOutput,
            'options' => ['created_at' => '2026-06-30T00:00:00+00:00'],
        ]);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
    }

    private function requiredSafetyFlags(): array
    {
        return [
            'production_ready',
            'production_catalog_runtime_wired',
            'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active',
            'controlled_rollout_active',
            'weekly_swing_watchlist_production_phase_approval_context_persisted_to_live_runtime',
            'production_phase_approval_context_persisted_to_live_runtime',
            'production_deployment_allowed',
            'production_deployment_executed',
            'production_runtime_wiring_allowed',
            'production_runtime_wiring_executed',
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
            'weekly_swing_watchlist_official_output_generated',
            'weekly_swing_watchlist_official_output_published',
            'weekly_swing_watchlist_live_recommendation_generated',
        ];
    }

    private function runService(array $overrides = []): array
    {
        return $this->execute($overrides);
    }

    private function execute(array $overrides = []): array
    {
        $service = new WatchlistBacktestC112WeeklySwingWatchlistProductionPhaseApprovalReviewService();
        $fixture = $this->lockedC111Fixture();
        return $service->execute(
            (string) ($overrides['c111Artifact'] ?? $fixture['path']),
            (string) ($overrides['expectedC111Hash'] ?? $fixture['hash']),
            (string) ($overrides['expectedC111FileSha1'] ?? $fixture['sha1']),
            (string) ($overrides['output'] ?? $this->output),
            array_merge([
                'overwrite' => true,
                'operator_approved' => $overrides['operatorApproved'] ?? true,
                'approval_reference' => $overrides['approvalReference'] ?? 'C112_OPERATOR_APPROVED_NEW_PRODUCTION_PHASE_ENTRY_ONLY',
            ], (array) ($overrides['options'] ?? []))
        );
    }

    private function mutateC111AndExecute(callable $mutator, string $name): array
    {
        $fixture = $this->lockedC111Fixture();
        $payload = json_decode((string) file_get_contents($fixture['path']), true);
        $payload = $mutator(is_array($payload) ? $payload : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c112-source-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
        return $this->execute([
            'c111Artifact' => $path,
            'expectedC111Hash' => (string) ($payload['artifact_hash'] ?? ''),
            'expectedC111FileSha1' => strtoupper(sha1_file($path)),
        ]);
    }

    private function lockedC111Fixture(): array
    {
        $path = 'storage/app/watchlist/backtest/c111-weekly-swing-watchlist-non-live-rehearsal-handoff-audit-archive-final-closure-review.json';
        $payload = json_decode((string) file_get_contents($path), true);
        return [
            'path' => $path,
            'hash' => (string) ($payload['artifact_hash'] ?? ''),
            'sha1' => strtoupper(sha1_file($path)),
        ];
    }

    private function readOutput(): array
    {
        $decoded = json_decode((string) file_get_contents($this->output), true);
        return is_array($decoded) ? $decoded : [];
    }
}

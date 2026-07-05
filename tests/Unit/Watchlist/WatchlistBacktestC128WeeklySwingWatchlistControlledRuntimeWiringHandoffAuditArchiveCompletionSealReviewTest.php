<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC128WeeklySwingWatchlistControlledRuntimeWiringHandoffAuditArchiveCompletionSealReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC128WeeklySwingWatchlistControlledRuntimeWiringHandoffAuditArchiveCompletionSealReviewTest extends TestCase
{
    private const PASS_STATUS = 'C128_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_PASSED_AUDIT_ARCHIVE_COMPLETION_SEALED_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C128_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const SEAL_NOT_CONFIRMED_STATUS = 'C128_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_REJECTED_AUDIT_ARCHIVE_COMPLETION_SEAL_NOT_CONFIRMED';
    private const TEMP_NEGATIVE_STATUS = 'C128_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const NEXT_C129 = 'C129_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW';
    private const C127_HASH = 'fc9d9204da55658d1416e24bd9be20381a1bbc54';
    private const C127_SHA1 = '6AE20CACBA644E8863FEA16FD4003BE1C775DA54';

    private string $output;

    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c128-handoff-audit-archive-completion-seal-pass.json';
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

    public function test_c128_passes_with_valid_c127_audit_archive_completion_lock_operator_approval_and_reference(): void
    {
        $result = $this->runService();

        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame($result['status'], $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_review_executed']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_review_allowed']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_review_pass']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_ready']);
        $this->assertTrue($result['controlled_runtime_wiring_handoff_audit_archive_completion_ready']);
        $this->assertTrue($result['handoff_audit_archive_completion_ready']);
        $this->assertTrue($result['audit_archive_completion_ready']);
        $this->assertTrue($result['completion_manifest_created']);
        $this->assertTrue($result['handoff_audit_archive_completion_confirmed']);
        $this->assertSame('HANDOFF_AUDIT_ARCHIVE_COMPLETION_READY_GO', $result['handoff_audit_archive_completion_go_decision']);
        $this->assertTrue($result['handoff_audit_archived']);
        $this->assertTrue($result['c127_handoff_audit_archived']);
        $this->assertTrue($result['c126_handoff_audit_archived']);
        $this->assertTrue($result['c125_handoff_closure_sealed']);
        $this->assertTrue($result['c124_handoff_completion_boundary_cleared']);
        $this->assertTrue($result['c123_handoff_finalized']);
        $this->assertTrue($result['c122_handoff_ready']);
        $this->assertTrue($result['primary_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archive_completion_ready']);
        $this->assertTrue($result['backup_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archive_completion_ready']);
        $this->assertFalse($result['comparator_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archive_completion_ready']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review_executed']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review_allowed']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review_pass']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_sealed']);
        $this->assertTrue($result['controlled_runtime_wiring_handoff_audit_archive_completion_sealed']);
        $this->assertTrue($result['handoff_audit_archive_completion_sealed']);
        $this->assertTrue($result['audit_archive_completion_sealed']);
        $this->assertTrue($result['completion_seal_manifest_created']);
        $this->assertTrue($result['handoff_audit_archive_completion_seal_confirmed']);
        $this->assertSame('HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEALED_GO', $result['handoff_audit_archive_completion_seal_go_decision']);
        $this->assertTrue($result['ready_for_controlled_runtime_wiring_handoff_audit_archive_final_closure_review']);
        $this->assertTrue($result['primary_candidate_handoff_audit_archive_completion_sealed']);
        $this->assertTrue($result['backup_candidate_handoff_audit_archive_completion_sealed']);
        $this->assertFalse($result['comparator_candidate_handoff_audit_archive_completion_sealed']);
        $this->assertTrue($result['a01_remains_comparator_only']);
        $this->assertSame(self::NEXT_C129, $result['next_step_recommendation']);
        $this->assertSame('C128_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEALED_CONTROLLED_RUNTIME_WIRING_AUDIT_ONLY', $result['diagnostic_conclusion']);
        $this->assertFileExists($this->output);
    }

    public function test_c128_rejects_missing_operator_approval(): void
    {
        $result = $this->execute(['operatorApproved' => false]);

        $this->assertSame(self::APPROVAL_MISSING_STATUS, $result['status']);
        $this->assertSame(self::APPROVAL_MISSING_STATUS, $result['reason_code']);
    }

    public function test_c128_rejects_missing_approval_reference(): void
    {
        $result = $this->execute(['approvalReference' => '']);

        $this->assertSame(self::APPROVAL_MISSING_STATUS, $result['status']);
        $this->assertSame(self::APPROVAL_MISSING_STATUS, $result['reason_code']);
    }

    public function test_c128_rejects_missing_handoff_audit_archive_completion_seal_confirmation(): void
    {
        $result = $this->execute(['options' => ['handoff_audit_archive_completion_seal_confirmed' => false]]);

        $this->assertSame(self::SEAL_NOT_CONFIRMED_STATUS, $result['status']);
        $this->assertSame(self::SEAL_NOT_CONFIRMED_STATUS, $result['reason_code']);
        $this->assertFalse($result['handoff_audit_archive_completion_sealed']);
    }

    public function test_c128_rejects_missing_or_mismatched_c127_artifact_lock(): void
    {
        $missing = $this->execute([
            'c127Artifact' => 'storage/app/watchlist/backtest/c128-source-does-not-exist.json',
            'expectedC127Hash' => 'missing',
            'expectedC127FileSha1' => 'missing',
        ]);
        $hashMismatch = $this->execute(['expectedC127Hash' => 'bad-hash']);
        $shaMismatch = $this->execute(['expectedC127FileSha1' => 'BADSHA1']);

        $this->assertSame('C128_BLOCKED_C127_ARTIFACT_LOCK_MISMATCH', $missing['status']);
        $this->assertSame('C128_BLOCKED_C127_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C128_BLOCKED_C127_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
    }

    public function test_c128_rejects_c127_status_mismatch(): void
    {
        $result = $this->mutateC127AndExecute(function (array $c127): array {
            $c127['status'] = 'BROKEN_STATUS';
            return $c127;
        }, 'status-broken');

        $this->assertSame('C128_BLOCKED_C127_STATUS_MISMATCH', $result['status']);
    }

    public function test_c128_rejects_c127_reason_code_mismatch(): void
    {
        $result = $this->mutateC127AndExecute(function (array $c127): array {
            $c127['reason_code'] = 'BROKEN_REASON';
            return $c127;
        }, 'reason-broken');

        $this->assertSame('C128_BLOCKED_C127_REASON_CODE_MISMATCH', $result['status']);
    }

    public function test_c128_rejects_c127_next_recommendation_mismatch(): void
    {
        $result = $this->mutateC127AndExecute(function (array $c127): array {
            $c127['next_step_recommendation'] = 'BROKEN_NEXT';
            $c127['next_readiness_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c127['c127_handoff_audit_archive_completion_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c127['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c127['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $c127;
        }, 'next-broken');

        $this->assertSame('C128_BLOCKED_C127_NEXT_RECOMMENDATION_MISMATCH', $result['status']);
    }

    /**
     * @dataProvider c127AuditArchiveStateMismatchProvider
     */
    public function test_c128_rejects_c127_audit_archive_state_mismatch(string $field, $value): void
    {
        $result = $this->mutateC127AndExecute(function (array $c127) use ($field, $value): array {
            $c127[$field] = $value;
            return $c127;
        }, 'state-'.$field);

        $this->assertSame('C128_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_REJECTED_C127_AUDIT_ARCHIVE_COMPLETION_INCOMPLETE', $result['status'], $field);
    }

    public function c127AuditArchiveStateMismatchProvider(): array
    {
        return [
            ['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_review_pass', false],
            ['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_ready', false],
            ['controlled_runtime_wiring_handoff_audit_archive_completion_ready', false],
            ['handoff_audit_archive_completion_ready', false],
            ['audit_archive_completion_ready', false],
            ['completion_manifest_created', false],
            ['handoff_audit_archive_completion_confirmed', false],
            ['handoff_audit_archive_completion_go_decision', 'NO_GO'],
            ['ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review', false],
            ['controlled_runtime_wiring_handoff_audit_archive_completion_seal_review_allowed_next', false],
            ['primary_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archive_completion_ready', false],
            ['backup_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archive_completion_ready', false],
            ['comparator_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archive_completion_ready', true],
            ['primary_candidate_ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review', false],
            ['backup_candidate_ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review', false],
            ['comparator_candidate_ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review', true],
            ['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archived', false],
            ['controlled_runtime_wiring_handoff_audit_archived', false],
            ['handoff_audit_archived', false],
            ['audit_archived', false],
            ['archive_manifest_created', false],
            ['handoff_audit_archive_confirmed', false],
            ['handoff_audit_archive_go_decision', 'NO_GO'],
            ['weekly_swing_watchlist_controlled_runtime_wiring_handoff_closure_sealed', false],
            ['handoff_closure_sealed', false],
            ['closure_sealed', false],
            ['handoff_completion_boundary_cleared', false],
            ['handoff_finalized', false],
            ['handoff_ready', false],
            ['a01_remains_comparator_only', false],
        ];
    }

    public function test_c128_rejects_candidate_scope_change_or_a01_promotion(): void
    {
        $result = $this->mutateC127AndExecute(function (array $c127): array {
            $c127['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $c127;
        }, 'candidate-primary-broken');
        $a01 = $this->mutateC127AndExecute(function (array $c127): array {
            $c127['a01_promoted'] = true;
            return $c127;
        }, 'candidate-a01-promoted');

        $this->assertSame('C128_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $result['status']);
        $this->assertSame('C128_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $a01['status']);
    }

    /**
     * @dataProvider safetyFlagProvider
     */
    public function test_c128_rejects_any_live_or_mutating_safety_flag_true_in_c127(string $field): void
    {
        $result = $this->mutateC127AndExecute(function (array $c127) use ($field): array {
            $c127[$field] = true;
            return $c127;
        }, 'safety-'.$field);

        $this->assertSame('C128_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_REJECTED_LIVE_OR_PRODUCTION_MUTATION_ATTEMPT', $result['status'], $field);
        $this->assertSame($field, $result['c127_live_or_mutating_safety_flag_failure']);
    }

    public function safetyFlagProvider(): array
    {
        return array_map(static function (string $flag): array {
            return [$flag];
        }, $this->requiredSafetyFlags());
    }

    public function test_c128_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c128-no-temporary-artifact-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame(self::TEMP_NEGATIVE_STATUS, $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c128_records_artifact_hash_source_locks_next_and_required_sections(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $this->assertSame(self::C127_HASH, $result['expected_c127_hash']);
        $this->assertSame(self::C127_HASH, $result['actual_c127_hash']);
        $this->assertTrue($result['c127_hash_match']);
        $this->assertSame(self::C127_SHA1, $result['expected_c127_file_sha1']);
        $this->assertSame(self::C127_SHA1, $result['actual_c127_file_sha1']);
        $this->assertTrue($result['c127_file_sha1_match']);
        $this->assertTrue($result['c127_convert_from_json_pass']);
        $this->assertSame(self::NEXT_C129, $result['next_readiness_decision']['next_recommendation']);

        foreach ([
            'source_artifact_locks',
            'c127_lock_validation_summary',
            'c122_c127_handoff_lineage_completion_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'c128_readiness_decision',
            'next_readiness_decision',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_decision',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_manifest',
            'c128_candidate_audit_archive_completion_seal_scorecard',
            'handoff_audit_archive_completion_seal_context_summary',
            'runtime_readiness_inspection_summary',
            'feature_flag_operator_approval_kill_switch_validation_summary',
            'c127_handoff_audit_archive_completion_carry_forward_validation_summary',
            'handoff_audit_archive_completion_seal_governance_summary',
            'baseline_plan_confirm_non_mutation_summary',
            'production_mutation_safety_summary',
            'documentation_governance_summary',
            'planned_next_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }
    }

    public function test_c128_keeps_e02_primary_b01_backup_a01_comparator_and_no_live_output(): void
    {
        $result = $this->runService();
        $manifest = $result['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_manifest'];

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $result['primary_candidate_code']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $result['backup_candidate_code']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $result['comparator_candidate_code']);
        $this->assertFalse($result['candidate_scope_freeze_summary']['candidate_promotion_executed']);
        $this->assertSame('comparator_only_not_promoted', $manifest['comparator_candidate_role']);
        $this->assertTrue($manifest['completion_seal_artifact_only']);
        $this->assertTrue($manifest['handoff_audit_archive_completion_sealed']);
        $this->assertFalse($manifest['completion_seal_used_for_plan_confirm_mutation']);
        $this->assertFalse($manifest['completion_seal_used_for_live_rollout']);
        $this->assertFalse($manifest['weekly_swing_official_output_generated']);
        $this->assertFalse($manifest['weekly_swing_live_output_enabled']);
        $this->assertFalse($result['weekly_swing_watchlist_official_output_generated']);
        $this->assertFalse($result['weekly_swing_watchlist_live_recommendation_generated']);
    }

    public function test_c128_keeps_all_safety_flags_false(): void
    {
        $result = $this->runService();

        foreach ($this->requiredSafetyFlags() as $flag) {
            $this->assertFalse($result[$flag], $flag);
            $this->assertFalse($result['production_mutation_safety_summary'][$flag], $flag);
        }
        $this->assertFalse($result['c128_readiness_decision']['production_ready']);
        $this->assertFalse($result['c128_readiness_decision']['weekly_swing_watchlist_live_output_enabled']);
        $this->assertFalse($result['handoff_audit_archive_completion_seal_context_summary']['context_persisted_to_live_runtime']);
    }

    public function test_c128_writes_completion_seal_manifest_artifact_only_and_not_used_for_plan_confirm(): void
    {
        $result = $this->runService();
        $manifest = $result['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_manifest'];

        $this->assertTrue($manifest['manifest_created']);
        $this->assertSame('artifact_only_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review', $manifest['manifest_context']);
        $this->assertSame('C127_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW', $manifest['source_artifact']);
        $this->assertTrue($manifest['handoff_ready_carried_forward']);
        $this->assertTrue($manifest['handoff_finalized_carried_forward']);
        $this->assertTrue($manifest['handoff_completion_boundary_cleared_carried_forward']);
        $this->assertTrue($manifest['handoff_closure_sealed_carried_forward']);
        $this->assertTrue($manifest['handoff_audit_archived_carried_forward']);
        $this->assertTrue($manifest['handoff_audit_archive_completion_ready_carried_forward']);
        $this->assertFalse($manifest['completion_seal_used_for_selection']);
        $this->assertFalse($manifest['completion_seal_used_for_retuning']);
        $this->assertFalse($manifest['completion_seal_used_for_ranking']);
        $this->assertFalse($manifest['completion_seal_used_for_plan_confirm_mutation']);
    }

    public function test_c128_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $first = $this->execute(['options' => ['created_at' => '2026-06-30T00:00:00+00:00']]);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c128-handoff-audit-archive-completion-seal-pass-second.json';
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
            'production_runtime_wiring_allowed',
            'production_runtime_wiring_executed',
            'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active',
            'controlled_rollout_active',
            'weekly_swing_watchlist_rehearsal_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_controlled_runtime_wiring_execution_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_controlled_runtime_wiring_result_review_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_controlled_runtime_wiring_operator_go_no_go_context_persisted_to_live_runtime',
            'operator_go_no_go_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_controlled_runtime_wiring_go_decision_finalization_context_persisted_to_live_runtime',
            'go_decision_finalization_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_controlled_runtime_wiring_completion_boundary_context_persisted_to_live_runtime',
            'completion_boundary_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_readiness_context_persisted_to_live_runtime',
            'controlled_runtime_wiring_handoff_readiness_context_persisted_to_live_runtime',
            'handoff_readiness_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_context_persisted_to_live_runtime',
            'controlled_runtime_wiring_handoff_finalization_context_persisted_to_live_runtime',
            'handoff_finalization_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_context_persisted_to_live_runtime',
            'controlled_runtime_wiring_handoff_completion_boundary_context_persisted_to_live_runtime',
            'handoff_completion_boundary_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_closure_seal_context_persisted_to_live_runtime',
            'controlled_runtime_wiring_handoff_closure_seal_context_persisted_to_live_runtime',
            'handoff_closure_seal_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_context_persisted_to_live_runtime',
            'controlled_runtime_wiring_handoff_audit_archive_context_persisted_to_live_runtime',
            'handoff_audit_archive_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_context_persisted_to_live_runtime',
            'controlled_runtime_wiring_handoff_audit_archive_completion_context_persisted_to_live_runtime',
            'handoff_audit_archive_completion_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_context_persisted_to_live_runtime',
            'controlled_runtime_wiring_handoff_audit_archive_completion_seal_context_persisted_to_live_runtime',
            'handoff_audit_archive_completion_seal_context_persisted_to_live_runtime',
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
        $service = new WatchlistBacktestC128WeeklySwingWatchlistControlledRuntimeWiringHandoffAuditArchiveCompletionSealReviewService();
        $fixture = $this->lockedC127Fixture();
        return $service->execute(
            (string) ($overrides['c127Artifact'] ?? $fixture['path']),
            (string) ($overrides['expectedC127Hash'] ?? $fixture['hash']),
            (string) ($overrides['expectedC127FileSha1'] ?? $fixture['sha1']),
            (string) ($overrides['output'] ?? $this->output),
            array_merge([
                'overwrite' => true,
                'operator_approved' => $overrides['operatorApproved'] ?? true,
                'approval_reference' => $overrides['approvalReference'] ?? 'C128_OPERATOR_APPROVED_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_ONLY',
                'handoff_audit_archive_completion_seal_confirmed' => true,
                'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_confirmed' => true,
            ], (array) ($overrides['options'] ?? []))
        );
    }

    private function mutateC127AndExecute(callable $mutator, string $name): array
    {
        $fixture = $this->lockedC127Fixture();
        $payload = json_decode((string) file_get_contents($fixture['path']), true);
        $payload = $mutator(is_array($payload) ? $payload : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c128-source-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
        return $this->execute([
            'c127Artifact' => $path,
            'expectedC127Hash' => (string) ($payload['artifact_hash'] ?? ''),
            'expectedC127FileSha1' => strtoupper(sha1_file($path)),
        ]);
    }

    private function lockedC127Fixture(): array
    {
        $path = 'storage/app/watchlist/backtest/c127-weekly-swing-watchlist-controlled-runtime-wiring-handoff-audit-archive-completion-review.json';
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

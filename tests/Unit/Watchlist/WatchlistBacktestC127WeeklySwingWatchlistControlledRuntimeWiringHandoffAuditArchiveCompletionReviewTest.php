<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC127WeeklySwingWatchlistControlledRuntimeWiringHandoffAuditArchiveCompletionReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC127WeeklySwingWatchlistControlledRuntimeWiringHandoffAuditArchiveCompletionReviewTest extends TestCase
{
    private const PASS_STATUS = 'C127_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_PASSED_AUDIT_ARCHIVE_COMPLETION_READY_PRIMARY_AND_BACKUP';
    private const NEXT_C128 = 'C128_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW';
    private const C126_HASH = '3f990d65414dd754ac4cd7a257ade44d52c89b67';
    private const C126_SHA1 = '16B4F020A06459B46CD5ECDAAEDAC1DC2829561E';

    private string $output;

    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c127-handoff-audit-archive-completion-pass.json';
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

    public function test_c127_passes_with_valid_c126_audit_archive_lock_operator_approval_reference_and_completion_confirmation(): void
    {
        $result = $this->runService();

        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame('PR-15 / C127_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW', $result['phase_label']);
        $this->assertSame($result['status'], $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_review_executed']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_review_allowed']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_review_pass']);
        $this->assertTrue($result['handoff_audit_archive_completion_ready']);
        $this->assertTrue($result['audit_archive_completion_ready']);
        $this->assertTrue($result['completion_manifest_created']);
        $this->assertTrue($result['handoff_audit_archive_completion_confirmed']);
        $this->assertSame('HANDOFF_AUDIT_ARCHIVE_COMPLETION_READY_GO', $result['handoff_audit_archive_completion_go_decision']);
        $this->assertTrue($result['ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review']);
        $this->assertTrue($result['handoff_audit_archived']);
        $this->assertTrue($result['c126_handoff_audit_archived']);
        $this->assertTrue($result['c125_handoff_closure_sealed']);
        $this->assertTrue($result['c124_handoff_completion_boundary_cleared']);
        $this->assertTrue($result['c123_handoff_finalized']);
        $this->assertTrue($result['c122_handoff_ready']);
        $this->assertTrue($result['primary_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archive_completion_ready']);
        $this->assertTrue($result['backup_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archive_completion_ready']);
        $this->assertFalse($result['comparator_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archive_completion_ready']);
        $this->assertTrue($result['a01_remains_comparator_only']);
        $this->assertSame(self::NEXT_C128, $result['next_step_recommendation']);
        $this->assertSame('READY_FOR_C128_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW', $result['diagnostic_conclusion']);
        $this->assertFileExists($this->output);
    }

    public function test_c127_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->execute(['operatorApproved' => false]);
        $missingReference = $this->execute(['approvalReference' => '']);

        $this->assertSame('C127_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingOperator['status']);
        $this->assertSame('C127_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingReference['status']);
    }

    public function test_c127_rejects_unconfirmed_handoff_audit_archive_completion(): void
    {
        $result = $this->execute(['options' => ['handoff_audit_archive_completion_confirmed' => false]]);

        $this->assertSame('C127_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_AUDIT_ARCHIVE_COMPLETION_NOT_CONFIRMED', $result['status']);
        $this->assertFalse($result['handoff_audit_archive_completion_ready']);
    }

    public function test_c127_rejects_missing_or_mismatched_c126_artifact_lock(): void
    {
        $missing = $this->execute([
            'c126Artifact' => 'storage/app/watchlist/backtest/missing-c126-for-c127.json',
            'expectedC126Hash' => 'missing',
            'expectedC126FileSha1' => 'missing',
        ]);
        $hashMismatch = $this->execute(['expectedC126Hash' => 'bad-hash']);
        $shaMismatch = $this->execute(['expectedC126FileSha1' => 'BADSHA1']);

        $this->assertSame('C127_BLOCKED_C126_ARTIFACT_LOCK_MISMATCH', $missing['status']);
        $this->assertSame('C127_BLOCKED_C126_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C127_BLOCKED_C126_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
    }

    public function test_c127_rejects_c126_status_reason_phase_or_next_recommendation_mismatch(): void
    {
        $status = $this->mutateC126AndExecute(function (array $c126): array {
            $c126['status'] = 'BROKEN_STATUS';
            return $c126;
        }, 'c126-status-broken');
        $reason = $this->mutateC126AndExecute(function (array $c126): array {
            $c126['reason_code'] = 'BROKEN_REASON';
            return $c126;
        }, 'c126-reason-broken');
        $phase = $this->mutateC126AndExecute(function (array $c126): array {
            $c126['phase_label'] = 'BROKEN_PHASE';
            return $c126;
        }, 'c126-phase-broken');
        $next = $this->mutateC126AndExecute(function (array $c126): array {
            $c126['next_step_recommendation'] = 'BROKEN_NEXT';
            $c126['next_readiness_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c126['c126_handoff_audit_archive_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c126['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c126['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $c126;
        }, 'c126-next-broken');

        $this->assertSame('C127_BLOCKED_C126_STATUS_OR_REASON_MISMATCH', $status['status']);
        $this->assertSame('C127_BLOCKED_C126_STATUS_OR_REASON_MISMATCH', $reason['status']);
        $this->assertSame('C127_BLOCKED_C126_PHASE_LABEL_MISMATCH', $phase['status']);
        $this->assertSame('C127_BLOCKED_C126_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
    }

    public function test_c127_rejects_c126_convert_from_json_duplicate_top_level_keys(): void
    {
        $fixture = $this->lockedC126Fixture();
        $raw = (string) file_get_contents($fixture['path']);
        $path = 'storage/app/watchlist/backtest/.tmp-c127-source-c126-duplicate-key.json';
        $this->tmpFiles[] = $path;
        $duplicateRaw = preg_replace('/\{/', "{\"Run_Code\":\"DUPLICATE_CASE_KEY\",", $raw, 1);
        file_put_contents($path, $duplicateRaw);

        $result = $this->execute([
            'c126Artifact' => $path,
            'expectedC126Hash' => $fixture['hash'],
            'expectedC126FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);

        $this->assertSame('C127_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_C126_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION', $result['status']);
        $this->assertFalse($result['c126_convert_from_json_pass']);
        $this->assertContains('run_code', array_map('strtolower', $result['c126_convert_from_json_duplicate_keys']));
    }

    /**
     * @dataProvider c126AuditArchiveStateMismatchProvider
     */
    public function test_c127_rejects_c126_audit_archive_state_mismatch(string $field, $value): void
    {
        $result = $this->mutateC126AndExecute(function (array $c126) use ($field, $value): array {
            $c126[$field] = $value;
            $c126['c126_handoff_audit_archive_decision'][$field] = $value;
            return $c126;
        }, 'c126-state-'.$field);

        $this->assertSame('C127_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_C126_AUDIT_ARCHIVE_INCOMPLETE', $result['status'], $field);
    }

    public function c126AuditArchiveStateMismatchProvider(): array
    {
        return [
            ['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_review_pass', false],
            ['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archived', false],
            ['controlled_runtime_wiring_handoff_audit_archived', false],
            ['handoff_audit_archived', false],
            ['audit_archived', false],
            ['archive_manifest_created', false],
            ['handoff_audit_archive_confirmed', false],
            ['handoff_audit_archive_go_decision', 'NO_GO'],
            ['ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_review', false],
            ['weekly_swing_watchlist_ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_review', false],
            ['controlled_runtime_wiring_handoff_audit_archive_manifest_created', false],
            ['controlled_runtime_wiring_handoff_audit_archive_completion_review_allowed_next', false],
            ['primary_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archived', false],
            ['backup_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archived', false],
            ['comparator_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archived', true],
            ['primary_candidate_ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_review', false],
            ['backup_candidate_ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_review', false],
            ['comparator_candidate_ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_review', true],
            ['handoff_closure_sealed', false],
            ['handoff_completion_boundary_cleared', false],
            ['handoff_finalized', false],
            ['handoff_ready', false],
            ['operator_go_decision', 'NO_GO'],
            ['go_decision_finalized', false],
            ['a01_remains_comparator_only', false],
        ];
    }

    public function test_c127_rejects_c126_readiness_count_mismatch(): void
    {
        $result = $this->mutateC126AndExecute(function (array $c126): array {
            $c126['next_readiness_decision']['candidate_ready_for_weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_review_count'] = 1;
            return $c126;
        }, 'c126-readiness-count-broken');

        $this->assertSame('C127_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_C126_AUDIT_ARCHIVE_INCOMPLETE', $result['status']);
    }

    public function test_c127_rejects_c126_manifest_not_artifact_only_or_used_for_live_output(): void
    {
        $notArtifactOnly = $this->mutateC126AndExecute(function (array $c126): array {
            $c126['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_manifest']['handoff_audit_archive_artifact_only'] = false;
            return $c126;
        }, 'c126-manifest-not-artifact-only');
        $usedForPlanConfirm = $this->mutateC126AndExecute(function (array $c126): array {
            $c126['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_manifest']['handoff_audit_archive_used_for_plan_confirm_mutation'] = true;
            return $c126;
        }, 'c126-manifest-plan-confirm');
        $officialOutput = $this->mutateC126AndExecute(function (array $c126): array {
            $c126['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_manifest']['official_weekly_swing_stock_recommendations'] = ['SHOULD_NOT_EXIST'];
            return $c126;
        }, 'c126-manifest-official-output');

        $this->assertSame('C127_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_C126_AUDIT_ARCHIVE_INCOMPLETE', $notArtifactOnly['status']);
        $this->assertSame('C127_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_C126_AUDIT_ARCHIVE_INCOMPLETE', $usedForPlanConfirm['status']);
        $this->assertSame('C127_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_C126_AUDIT_ARCHIVE_INCOMPLETE', $officialOutput['status']);
    }

    public function test_c127_rejects_candidate_scope_change_or_a01_promotion(): void
    {
        $result = $this->mutateC126AndExecute(function (array $c126): array {
            $c126['candidate_scope_freeze_summary']['a01_promoted'] = true;
            return $c126;
        }, 'c126-a01-promoted');

        $this->assertSame('C127_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $result['status']);
    }

    /**
     * @dataProvider safetyFlagProvider
     */
    public function test_c127_rejects_any_live_or_mutating_safety_flag_true_in_c126(string $field): void
    {
        $result = $this->mutateC126AndExecute(function (array $c126) use ($field): array {
            $c126[$field] = true;
            return $c126;
        }, 'c126-safety-'.$field);

        $this->assertSame('C127_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_LIVE_OR_PRODUCTION_MUTATION_ATTEMPT', $result['status'], $field);
        $this->assertSame($field, $result['c126_live_or_mutating_safety_flag_failure']);
    }

    public function safetyFlagProvider(): array
    {
        return array_map(static function (string $flag): array {
            return [$flag];
        }, $this->requiredSafetyFlags());
    }

    public function test_c127_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c127-no-temporary-artifact-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame('C127_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS', $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c127_records_artifact_hash_locks_next_and_required_sections(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $this->assertSame(self::C126_HASH, $result['expected_c126_hash']);
        $this->assertSame(self::C126_HASH, $result['actual_c126_hash']);
        $this->assertTrue($result['c126_hash_match']);
        $this->assertSame(strtoupper(self::C126_SHA1), $result['expected_c126_file_sha1']);
        $this->assertSame(strtoupper(self::C126_SHA1), $result['actual_c126_file_sha1']);
        $this->assertTrue($result['c126_file_sha1_match']);
        $this->assertTrue($result['c126_convert_from_json_pass']);
        $this->assertTrue($result['c126_lock_validation_summary']['c126_phase_label_match']);
        $this->assertSame(self::NEXT_C128, $result['next_readiness_decision']['next_recommendation']);
        $this->assertSame(2, $result['next_readiness_decision']['candidate_ready_for_weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review_count']);

        foreach ([
            'source_artifact_locks',
            'c126_lock_validation_summary',
            'lineage_validation_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'c127_handoff_audit_archive_completion_decision',
            'next_readiness_decision',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_manifest',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_candidate_scorecard',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_context_summary',
            'runtime_readiness_inspection_summary',
            'feature_flag_operator_approval_kill_switch_validation_summary',
            'c126_handoff_audit_archive_carry_forward_validation_summary',
            'handoff_audit_archive_completion_governance_summary',
            'baseline_plan_confirm_non_mutation_summary',
            'production_mutation_safety_summary',
            'planned_next_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }
    }

    public function test_c127_keeps_e02_primary_b01_backup_a01_comparator_and_no_live_output(): void
    {
        $result = $this->runService();
        $manifest = $result['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_manifest'];

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $result['primary_candidate_code']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $result['backup_candidate_code']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $result['comparator_candidate_code']);
        $this->assertFalse($result['candidate_scope_freeze_summary']['a01_promoted']);
        $this->assertTrue($manifest['handoff_audit_archive_completion_artifact_only']);
        $this->assertTrue($manifest['handoff_audit_archive_completion_ready']);
        $this->assertFalse($manifest['handoff_audit_archive_completion_used_for_plan_confirm_mutation']);
        $this->assertFalse($manifest['handoff_audit_archive_completion_used_for_live_rollout']);
        $this->assertSame([], $manifest['official_weekly_swing_stock_recommendations']);
        $this->assertFalse($result['weekly_swing_watchlist_official_output_generated']);
        $this->assertFalse($result['weekly_swing_watchlist_live_recommendation_generated']);
    }

    public function test_c127_keeps_all_safety_flags_false(): void
    {
        $result = $this->runService();

        foreach ($this->requiredSafetyFlags() as $flag) {
            $this->assertFalse($result[$flag], $flag);
            $this->assertFalse($result['production_mutation_safety_summary'][$flag], $flag);
            $this->assertFalse($result['c127_handoff_audit_archive_completion_decision'][$flag], $flag);
        }
    }

    public function test_c127_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $first = $this->execute(['options' => ['created_at' => '2026-06-30T00:00:00+00:00']]);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c127-handoff-audit-archive-completion-pass-second.json';
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
        $service = new WatchlistBacktestC127WeeklySwingWatchlistControlledRuntimeWiringHandoffAuditArchiveCompletionReviewService();
        $fixture = $this->lockedC126Fixture();
        return $service->execute(
            (string) ($overrides['c126Artifact'] ?? $fixture['path']),
            (string) ($overrides['expectedC126Hash'] ?? $fixture['hash']),
            (string) ($overrides['expectedC126FileSha1'] ?? $fixture['sha1']),
            (string) ($overrides['output'] ?? $this->output),
            array_merge([
                'overwrite' => true,
                'operator_approved' => $overrides['operatorApproved'] ?? true,
                'approval_reference' => $overrides['approvalReference'] ?? 'C127_OPERATOR_APPROVED_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_ONLY',
                'handoff_audit_archive_completion_confirmed' => true,
                'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_confirmed' => true,
            ], (array) ($overrides['options'] ?? []))
        );
    }

    private function mutateC126AndExecute(callable $mutator, string $name): array
    {
        $fixture = $this->lockedC126Fixture();
        $payload = json_decode((string) file_get_contents($fixture['path']), true);
        $payload = $mutator(is_array($payload) ? $payload : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c127-source-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
        return $this->execute([
            'c126Artifact' => $path,
            'expectedC126Hash' => (string) ($payload['artifact_hash'] ?? ''),
            'expectedC126FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function lockedC126Fixture(): array
    {
        $path = 'storage/app/watchlist/backtest/c126-weekly-swing-watchlist-controlled-runtime-wiring-handoff-audit-archive-review.json';
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

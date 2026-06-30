<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC108WeeklySwingWatchlistNonLiveRehearsalHandoffAuditArchiveReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC108WeeklySwingWatchlistNonLiveRehearsalHandoffAuditArchiveReviewTest extends TestCase
{
    private const PASS_STATUS = 'C108_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_REVIEW_PASSED_AUDIT_ARCHIVED_PRIMARY_AND_BACKUP';
    private const NEXT_C109 = 'C109_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW';
    private const C107_HASH = 'dd9edfc84044eeaa78f83b3fe4980e86ad9be62f';
    private const C107_SHA1 = '002EAEC0989CA23C7CE713345AEA7CAE8C6622E8';

    private string $output;

    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c108-handoff-audit-archive-pass.json';
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

    public function test_c108_passes_with_valid_c107_closure_seal_lock_operator_approval_and_reference(): void
    {
        $result = $this->runService();

        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame($result['status'], $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_review_executed']);
        $this->assertTrue($result['weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_review_allowed']);
        $this->assertTrue($result['weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_review_pass']);
        $this->assertTrue($result['weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archived']);
        $this->assertTrue($result['handoff_audit_archived']);
        $this->assertTrue($result['audit_archived']);
        $this->assertTrue($result['archive_manifest_created']);
        $this->assertTrue($result['handoff_closure_sealed']);
        $this->assertTrue($result['c107_handoff_closure_sealed']);
        $this->assertTrue($result['c106_handoff_completion_boundary_cleared']);
        $this->assertTrue($result['c105_handoff_finalized']);
        $this->assertTrue($result['c104_handoff_ready']);
        $this->assertTrue($result['primary_candidate_weekly_swing_non_live_rehearsal_handoff_audit_archived']);
        $this->assertTrue($result['backup_candidate_weekly_swing_non_live_rehearsal_handoff_audit_archived']);
        $this->assertFalse($result['comparator_candidate_weekly_swing_non_live_rehearsal_handoff_audit_archived']);
        $this->assertTrue($result['a01_remains_comparator_only']);
        $this->assertSame(self::NEXT_C109, $result['next_step_recommendation']);
        $this->assertSame('READY_FOR_C109_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW', $result['diagnostic_conclusion']);
        $this->assertFileExists($this->output);
    }

    public function test_c108_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->execute(['operatorApproved' => false]);
        $missingReference = $this->execute(['approvalReference' => '']);

        $this->assertSame('C108_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingOperator['status']);
        $this->assertSame('C108_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingReference['status']);
    }

    public function test_c108_rejects_unconfirmed_handoff_audit_archive(): void
    {
        $result = $this->execute(['options' => ['handoff_audit_archive_confirmed' => false]]);

        $this->assertSame('C108_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_REVIEW_REJECTED_AUDIT_ARCHIVE_NOT_CONFIRMED', $result['status']);
        $this->assertFalse($result['handoff_audit_archived']);
    }

    public function test_c108_rejects_missing_or_mismatched_c107_artifact_lock(): void
    {
        $missing = $this->execute([
            'c107Artifact' => 'storage/app/watchlist/backtest/missing-c107-for-c108.json',
            'expectedC107Hash' => 'missing',
            'expectedC107FileSha1' => 'missing',
        ]);
        $hashMismatch = $this->execute(['expectedC107Hash' => 'bad-hash']);
        $shaMismatch = $this->execute(['expectedC107FileSha1' => 'BADSHA1']);

        $this->assertSame('C108_BLOCKED_C107_ARTIFACT_LOCK_MISMATCH', $missing['status']);
        $this->assertSame('C108_BLOCKED_C107_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C108_BLOCKED_C107_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
    }

    public function test_c108_rejects_c107_status_reason_or_next_recommendation_mismatch(): void
    {
        $status = $this->mutateC107AndExecute(function (array $c107): array {
            $c107['status'] = 'BROKEN_STATUS';
            return $c107;
        }, 'c107-status-broken');
        $reason = $this->mutateC107AndExecute(function (array $c107): array {
            $c107['reason_code'] = 'BROKEN_REASON';
            return $c107;
        }, 'c107-reason-broken');
        $next = $this->mutateC107AndExecute(function (array $c107): array {
            $c107['next_step_recommendation'] = 'BROKEN_NEXT';
            $c107['next_readiness_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c107['c107_handoff_closure_seal_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c107['weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c107['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $c107;
        }, 'c107-next-broken');

        $this->assertSame('C108_BLOCKED_C107_STATUS_OR_REASON_MISMATCH', $status['status']);
        $this->assertSame('C108_BLOCKED_C107_STATUS_OR_REASON_MISMATCH', $reason['status']);
        $this->assertSame('C108_BLOCKED_C107_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
    }

    /**
     * @dataProvider c107ClosureSealedStateMismatchProvider
     */
    public function test_c108_rejects_c107_closure_sealed_state_mismatch(string $field, $value): void
    {
        $result = $this->mutateC107AndExecute(function (array $c107) use ($field, $value): array {
            $c107[$field] = $value;
            $c107['c107_handoff_closure_seal_decision'][$field] = $value;
            return $c107;
        }, 'c107-state-'.$field);

        $this->assertSame('C108_BLOCKED_C107_CLOSURE_NOT_SEALED', $result['status'], $field);
    }

    public function c107ClosureSealedStateMismatchProvider(): array
    {
        return [
            ['weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_review_pass', false],
            ['weekly_swing_watchlist_non_live_rehearsal_handoff_closure_sealed', false],
            ['handoff_closure_sealed', false],
            ['closure_sealed', false],
            ['handoff_completion_boundary_cleared', false],
            ['boundary_go_decision', 'NO_GO'],
            ['operator_go_decision', 'NO_GO'],
            ['go_decision_finalized', false],
            ['primary_candidate_weekly_swing_non_live_rehearsal_handoff_closure_sealed', false],
            ['backup_candidate_weekly_swing_non_live_rehearsal_handoff_closure_sealed', false],
            ['comparator_candidate_weekly_swing_non_live_rehearsal_handoff_closure_sealed', true],
            ['a01_remains_comparator_only', false],
        ];
    }

    public function test_c108_rejects_c107_c108_readiness_count_mismatch(): void
    {
        $result = $this->mutateC107AndExecute(function (array $c107): array {
            $c107['next_readiness_decision']['candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_review_count'] = 1;
            return $c107;
        }, 'c107-readiness-count-broken');

        $this->assertSame('C108_BLOCKED_C107_CLOSURE_NOT_SEALED', $result['status']);
    }

    public function test_c108_rejects_c107_manifest_not_artifact_only_or_used_for_live_output(): void
    {
        $notArtifactOnly = $this->mutateC107AndExecute(function (array $c107): array {
            $c107['weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_manifest']['handoff_closure_seal_artifact_only'] = false;
            return $c107;
        }, 'c107-manifest-not-artifact-only');
        $usedForPlanConfirm = $this->mutateC107AndExecute(function (array $c107): array {
            $c107['weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_manifest']['handoff_closure_seal_used_for_plan_confirm_mutation'] = true;
            return $c107;
        }, 'c107-manifest-plan-confirm');
        $officialOutput = $this->mutateC107AndExecute(function (array $c107): array {
            $c107['weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_manifest']['official_weekly_swing_stock_recommendations'] = ['SHOULD_NOT_EXIST'];
            return $c107;
        }, 'c107-manifest-official-output');

        $this->assertSame('C108_BLOCKED_C107_CLOSURE_NOT_SEALED', $notArtifactOnly['status']);
        $this->assertSame('C108_BLOCKED_C107_CLOSURE_NOT_SEALED', $usedForPlanConfirm['status']);
        $this->assertSame('C108_BLOCKED_C107_CLOSURE_NOT_SEALED', $officialOutput['status']);
    }

    public function test_c108_rejects_candidate_scope_change_or_a01_promotion(): void
    {
        $result = $this->mutateC107AndExecute(function (array $c107): array {
            $c107['candidate_scope_freeze_summary']['a01_promoted'] = true;
            return $c107;
        }, 'c107-a01-promoted');

        $this->assertSame('C108_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $result['status']);
    }

    /**
     * @dataProvider safetyFlagProvider
     */
    public function test_c108_rejects_any_live_or_mutating_safety_flag_true_in_c107(string $field): void
    {
        $result = $this->mutateC107AndExecute(function (array $c107) use ($field): array {
            $c107[$field] = true;
            return $c107;
        }, 'c107-safety-'.$field);

        $this->assertSame('C108_BLOCKED_C107_SAFETY_FLAG_ALREADY_LIVE_OR_MUTATING', $result['status'], $field);
        $this->assertSame($field, $result['c107_live_or_mutating_safety_flag_failure']);
    }

    public function safetyFlagProvider(): array
    {
        return array_map(static function (string $flag): array {
            return [$flag];
        }, $this->requiredSafetyFlags());
    }

    public function test_c108_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c108-no-temporary-artifact-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame('C108_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS', $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c108_records_artifact_hash_locks_next_and_required_sections(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $this->assertSame(self::C107_HASH, $result['expected_c107_hash']);
        $this->assertSame(self::C107_HASH, $result['actual_c107_hash']);
        $this->assertTrue($result['c107_hash_match']);
        $this->assertSame(strtoupper(self::C107_SHA1), $result['expected_c107_file_sha1']);
        $this->assertSame(strtoupper(self::C107_SHA1), $result['actual_c107_file_sha1']);
        $this->assertTrue($result['c107_file_sha1_match']);
        $this->assertSame(self::NEXT_C109, $result['next_readiness_decision']['next_recommendation']);
        $this->assertSame(2, $result['next_readiness_decision']['candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_review_count']);

        foreach ([
            'source_artifact_locks',
            'c107_lock_validation_summary',
            'lineage_validation_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'c108_handoff_audit_archive_decision',
            'next_readiness_decision',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_manifest',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_candidate_scorecard',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_context_summary',
            'runtime_readiness_inspection_summary',
            'feature_flag_operator_approval_kill_switch_validation_summary',
            'c107_handoff_closure_seal_carry_forward_validation_summary',
            'handoff_audit_archive_governance_summary',
            'production_mutation_safety_summary',
            'planned_next_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }
    }

    public function test_c108_keeps_e02_primary_b01_backup_a01_comparator_and_no_live_output(): void
    {
        $result = $this->runService();
        $manifest = $result['weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_manifest'];

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $result['primary_candidate_code']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $result['backup_candidate_code']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $result['comparator_candidate_code']);
        $this->assertFalse($result['candidate_scope_freeze_summary']['a01_promoted']);
        $this->assertTrue($manifest['handoff_audit_archive_artifact_only']);
        $this->assertTrue($manifest['handoff_audit_archived']);
        $this->assertFalse($manifest['handoff_audit_archive_used_for_plan_confirm_mutation']);
        $this->assertFalse($manifest['handoff_audit_archive_used_for_live_rollout']);
        $this->assertSame([], $manifest['official_weekly_swing_stock_recommendations']);
        $this->assertFalse($result['weekly_swing_watchlist_official_output_generated']);
        $this->assertFalse($result['weekly_swing_watchlist_live_recommendation_generated']);
    }

    public function test_c108_keeps_all_safety_flags_false(): void
    {
        $result = $this->runService();

        foreach ($this->requiredSafetyFlags() as $flag) {
            $this->assertFalse($result[$flag], $flag);
            $this->assertFalse($result['production_mutation_safety_summary'][$flag], $flag);
            $this->assertFalse($result['c108_handoff_audit_archive_decision'][$flag], $flag);
        }
    }

    public function test_c108_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $first = $this->execute(['options' => ['created_at' => '2026-06-30T00:00:00+00:00']]);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c108-handoff-audit-archive-pass-second.json';
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
            'weekly_swing_watchlist_rehearsal_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_non_live_rehearsal_execution_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_non_live_rehearsal_result_review_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_context_persisted_to_live_runtime',
            'operator_go_no_go_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_context_persisted_to_live_runtime',
            'go_decision_finalization_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_non_live_rehearsal_completion_boundary_context_persisted_to_live_runtime',
            'completion_boundary_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_context_persisted_to_live_runtime',
            'handoff_readiness_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_finalization_context_persisted_to_live_runtime',
            'handoff_finalization_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_completion_boundary_context_persisted_to_live_runtime',
            'handoff_completion_boundary_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_context_persisted_to_live_runtime',
            'handoff_closure_seal_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_context_persisted_to_live_runtime',
            'handoff_audit_archive_context_persisted_to_live_runtime',
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
        $service = new WatchlistBacktestC108WeeklySwingWatchlistNonLiveRehearsalHandoffAuditArchiveReviewService();
        $fixture = $this->lockedC107Fixture();
        return $service->execute(
            (string) ($overrides['c107Artifact'] ?? $fixture['path']),
            (string) ($overrides['expectedC107Hash'] ?? $fixture['hash']),
            (string) ($overrides['expectedC107FileSha1'] ?? $fixture['sha1']),
            (string) ($overrides['output'] ?? $this->output),
            array_merge([
                'overwrite' => true,
                'operator_approved' => $overrides['operatorApproved'] ?? true,
                'approval_reference' => $overrides['approvalReference'] ?? 'C108_OPERATOR_APPROVED_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_REVIEW_ONLY',
            ], (array) ($overrides['options'] ?? []))
        );
    }

    private function mutateC107AndExecute(callable $mutator, string $name): array
    {
        $fixture = $this->lockedC107Fixture();
        $payload = json_decode((string) file_get_contents($fixture['path']), true);
        $payload = $mutator(is_array($payload) ? $payload : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c108-source-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
        return $this->execute([
            'c107Artifact' => $path,
            'expectedC107Hash' => (string) ($payload['artifact_hash'] ?? ''),
            'expectedC107FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function lockedC107Fixture(): array
    {
        $path = 'storage/app/watchlist/backtest/c107-weekly-swing-watchlist-non-live-rehearsal-handoff-closure-seal-review.json';
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

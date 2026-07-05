<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC126WeeklySwingWatchlistControlledRuntimeWiringHandoffAuditArchiveReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC126WeeklySwingWatchlistControlledRuntimeWiringHandoffAuditArchiveReviewTest extends TestCase
{
    private const PASS_STATUS = 'C126_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_REVIEW_PASSED_AUDIT_ARCHIVED_PRIMARY_AND_BACKUP';
    private const NEXT_C127 = 'C127_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW';
    private const C125_HASH = '38850d8848a0df52b7b804625c21f285f841c2f1';
    private const C125_SHA1 = '359325C7B236F178E4C37BAFCAC21D3E42C37447';

    private string $output;

    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c126-handoff-audit-archive-pass.json';
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

    public function test_c126_passes_with_valid_c125_closure_seal_lock_operator_approval_and_reference(): void
    {
        $result = $this->runService();

        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame('PR-14 / C126_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_REVIEW', $result['phase_label']);
        $this->assertSame($result['status'], $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_review_executed']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_review_allowed']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_review_pass']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archived']);
        $this->assertTrue($result['controlled_runtime_wiring_handoff_audit_archived']);
        $this->assertTrue($result['handoff_audit_archived']);
        $this->assertTrue($result['audit_archived']);
        $this->assertTrue($result['archive_manifest_created']);
        $this->assertTrue($result['handoff_audit_archive_confirmed']);
        $this->assertSame('HANDOFF_AUDIT_ARCHIVED_GO', $result['handoff_audit_archive_go_decision']);
        $this->assertTrue($result['ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_review']);
        $this->assertTrue($result['handoff_closure_sealed']);
        $this->assertTrue($result['c125_handoff_closure_sealed']);
        $this->assertTrue($result['c125_handoff_closure_seal_confirmed']);
        $this->assertTrue($result['c124_handoff_completion_boundary_cleared']);
        $this->assertTrue($result['c123_handoff_finalized']);
        $this->assertTrue($result['c122_handoff_ready']);
        $this->assertTrue($result['primary_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archived']);
        $this->assertTrue($result['backup_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archived']);
        $this->assertFalse($result['comparator_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archived']);
        $this->assertTrue($result['a01_remains_comparator_only']);
        $this->assertSame(self::NEXT_C127, $result['next_step_recommendation']);
        $this->assertSame('READY_FOR_C127_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW', $result['diagnostic_conclusion']);
        $this->assertFileExists($this->output);
    }

    public function test_c126_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->execute(['operatorApproved' => false]);
        $missingReference = $this->execute(['approvalReference' => '']);

        $this->assertSame('C126_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingOperator['status']);
        $this->assertSame('C126_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingReference['status']);
    }

    public function test_c126_rejects_unconfirmed_handoff_audit_archive(): void
    {
        $result = $this->execute(['options' => ['handoff_audit_archive_confirmed' => false]]);

        $this->assertSame('C126_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_REVIEW_REJECTED_AUDIT_ARCHIVE_NOT_CONFIRMED', $result['status']);
        $this->assertFalse($result['handoff_audit_archived']);
    }

    public function test_c126_rejects_missing_or_mismatched_c125_artifact_lock(): void
    {
        $missing = $this->execute([
            'c125Artifact' => 'storage/app/watchlist/backtest/missing-c125-for-c126.json',
            'expectedC125Hash' => 'missing',
            'expectedC125FileSha1' => 'missing',
        ]);
        $hashMismatch = $this->execute(['expectedC125Hash' => 'bad-hash']);
        $shaMismatch = $this->execute(['expectedC125FileSha1' => 'BADSHA1']);

        $this->assertSame('C126_BLOCKED_C125_ARTIFACT_LOCK_MISMATCH', $missing['status']);
        $this->assertSame('C126_BLOCKED_C125_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C126_BLOCKED_C125_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
    }

    public function test_c126_rejects_c125_status_reason_or_next_recommendation_mismatch(): void
    {
        $status = $this->mutateC125AndExecute(function (array $c125): array {
            $c125['status'] = 'BROKEN_STATUS';
            return $c125;
        }, 'c125-status-broken');
        $reason = $this->mutateC125AndExecute(function (array $c125): array {
            $c125['reason_code'] = 'BROKEN_REASON';
            return $c125;
        }, 'c125-reason-broken');
        $next = $this->mutateC125AndExecute(function (array $c125): array {
            $c125['next_step_recommendation'] = 'BROKEN_NEXT';
            $c125['next_readiness_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c125['c125_handoff_closure_seal_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c125['weekly_swing_watchlist_controlled_runtime_wiring_handoff_closure_seal_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c125['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $c125;
        }, 'c125-next-broken');

        $this->assertSame('C126_BLOCKED_C125_STATUS_OR_REASON_MISMATCH', $status['status']);
        $this->assertSame('C126_BLOCKED_C125_STATUS_OR_REASON_MISMATCH', $reason['status']);
        $this->assertSame('C126_BLOCKED_C125_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
    }

    public function test_c126_rejects_c125_phase_label_mismatch(): void
    {
        $result = $this->mutateC125AndExecute(function (array $c125): array {
            $c125['phase_label'] = 'BROKEN_PHASE_LABEL';
            return $c125;
        }, 'c125-phase-broken');

        $this->assertSame('C126_BLOCKED_C125_PHASE_LABEL_MISMATCH', $result['status']);
    }

    public function test_c126_rejects_c125_convert_from_json_duplicate_top_level_keys(): void
    {
        $fixture = $this->lockedC125Fixture();
        $raw = (string) file_get_contents($fixture['path']);
        $path = 'storage/app/watchlist/backtest/.tmp-c126-source-c125-duplicate-key.json';
        $this->tmpFiles[] = $path;
        $duplicateRaw = preg_replace('/\{/', "{\"Run_Code\":\"DUPLICATE_CASE_KEY\",", $raw, 1);
        file_put_contents($path, $duplicateRaw);

        $result = $this->execute([
            'c125Artifact' => $path,
            'expectedC125Hash' => $fixture['hash'],
            'expectedC125FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);

        $this->assertSame('C126_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_REVIEW_REJECTED_C125_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION', $result['status']);
        $this->assertFalse($result['c125_convert_from_json_pass']);
        $this->assertContains('run_code', array_map('strtolower', $result['c125_convert_from_json_duplicate_keys']));
    }

    /**
     * @dataProvider c125ClosureSealedStateMismatchProvider
     */
    public function test_c126_rejects_c125_closure_sealed_state_mismatch(string $field, $value): void
    {
        $result = $this->mutateC125AndExecute(function (array $c125) use ($field, $value): array {
            $c125[$field] = $value;
            $c125['c125_handoff_closure_seal_decision'][$field] = $value;
            return $c125;
        }, 'c125-state-'.$field);

        $this->assertSame('C126_BLOCKED_C125_CLOSURE_NOT_SEALED', $result['status'], $field);
    }

    public function c125ClosureSealedStateMismatchProvider(): array
    {
        return [
            ['weekly_swing_watchlist_controlled_runtime_wiring_handoff_closure_seal_review_pass', false],
            ['weekly_swing_watchlist_controlled_runtime_wiring_handoff_closure_sealed', false],
            ['controlled_runtime_wiring_handoff_closure_sealed', false],
            ['handoff_closure_sealed', false],
            ['closure_sealed', false],
            ['handoff_closure_seal_confirmed', false],
            ['handoff_closure_seal_go_decision', 'NO_GO'],
            ['ready_for_controlled_runtime_wiring_handoff_audit_archive_review', false],
            ['weekly_swing_watchlist_ready_for_controlled_runtime_wiring_handoff_audit_archive_review', false],
            ['controlled_runtime_wiring_handoff_closure_seal_manifest_created', false],
            ['controlled_runtime_wiring_handoff_audit_archive_review_allowed_next', false],
            ['handoff_completion_boundary_cleared', false],
            ['handoff_completion_boundary_confirmed', false],
            ['handoff_completion_boundary_go_decision', 'NO_GO'],
            ['boundary_go_decision', 'NO_GO'],
            ['operator_go_decision', 'NO_GO'],
            ['go_decision_finalized', false],
            ['primary_candidate_weekly_swing_controlled_runtime_wiring_handoff_closure_sealed', false],
            ['backup_candidate_weekly_swing_controlled_runtime_wiring_handoff_closure_sealed', false],
            ['comparator_candidate_weekly_swing_controlled_runtime_wiring_handoff_closure_sealed', true],
            ['primary_candidate_ready_for_controlled_runtime_wiring_handoff_audit_archive_review', false],
            ['backup_candidate_ready_for_controlled_runtime_wiring_handoff_audit_archive_review', false],
            ['comparator_candidate_ready_for_controlled_runtime_wiring_handoff_audit_archive_review', true],
            ['a01_remains_comparator_only', false],
        ];
    }

    public function test_c126_rejects_c125_c126_readiness_count_mismatch(): void
    {
        $result = $this->mutateC125AndExecute(function (array $c125): array {
            $c125['next_readiness_decision']['candidate_ready_for_weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_review_count'] = 1;
            return $c125;
        }, 'c125-readiness-count-broken');

        $this->assertSame('C126_BLOCKED_C125_CLOSURE_NOT_SEALED', $result['status']);
    }

    public function test_c126_rejects_c125_manifest_not_artifact_only_or_used_for_live_output(): void
    {
        $notArtifactOnly = $this->mutateC125AndExecute(function (array $c125): array {
            $c125['weekly_swing_watchlist_controlled_runtime_wiring_handoff_closure_seal_manifest']['handoff_closure_seal_artifact_only'] = false;
            return $c125;
        }, 'c125-manifest-not-artifact-only');
        $usedForPlanConfirm = $this->mutateC125AndExecute(function (array $c125): array {
            $c125['weekly_swing_watchlist_controlled_runtime_wiring_handoff_closure_seal_manifest']['handoff_closure_seal_used_for_plan_confirm_mutation'] = true;
            return $c125;
        }, 'c125-manifest-plan-confirm');
        $officialOutput = $this->mutateC125AndExecute(function (array $c125): array {
            $c125['weekly_swing_watchlist_controlled_runtime_wiring_handoff_closure_seal_manifest']['official_weekly_swing_stock_recommendations'] = ['SHOULD_NOT_EXIST'];
            return $c125;
        }, 'c125-manifest-official-output');

        $this->assertSame('C126_BLOCKED_C125_CLOSURE_NOT_SEALED', $notArtifactOnly['status']);
        $this->assertSame('C126_BLOCKED_C125_CLOSURE_NOT_SEALED', $usedForPlanConfirm['status']);
        $this->assertSame('C126_BLOCKED_C125_CLOSURE_NOT_SEALED', $officialOutput['status']);
    }

    public function test_c126_rejects_candidate_scope_change_or_a01_promotion(): void
    {
        $result = $this->mutateC125AndExecute(function (array $c125): array {
            $c125['candidate_scope_freeze_summary']['a01_promoted'] = true;
            return $c125;
        }, 'c125-a01-promoted');

        $this->assertSame('C126_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $result['status']);
    }

    /**
     * @dataProvider safetyFlagProvider
     */
    public function test_c126_rejects_any_live_or_mutating_safety_flag_true_in_c125(string $field): void
    {
        $result = $this->mutateC125AndExecute(function (array $c125) use ($field): array {
            $c125[$field] = true;
            return $c125;
        }, 'c125-safety-'.$field);

        $this->assertSame('C126_BLOCKED_C125_SAFETY_FLAG_ALREADY_LIVE_OR_MUTATING', $result['status'], $field);
        $this->assertSame($field, $result['c125_live_or_mutating_safety_flag_failure']);
    }

    public function safetyFlagProvider(): array
    {
        return array_map(static function (string $flag): array {
            return [$flag];
        }, $this->requiredSafetyFlags());
    }

    public function test_c126_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c126-no-temporary-artifact-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame('C126_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS', $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c126_records_artifact_hash_locks_next_and_required_sections(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $this->assertSame(self::C125_HASH, $result['expected_c125_hash']);
        $this->assertSame(self::C125_HASH, $result['actual_c125_hash']);
        $this->assertTrue($result['c125_hash_match']);
        $this->assertSame(strtoupper(self::C125_SHA1), $result['expected_c125_file_sha1']);
        $this->assertSame(strtoupper(self::C125_SHA1), $result['actual_c125_file_sha1']);
        $this->assertTrue($result['c125_file_sha1_match']);
        $this->assertTrue($result['c125_convert_from_json_pass']);
        $this->assertTrue($result['c125_lock_validation_summary']['c125_phase_label_match']);
        $this->assertSame(self::NEXT_C127, $result['next_readiness_decision']['next_recommendation']);
        $this->assertSame(2, $result['next_readiness_decision']['candidate_ready_for_weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_review_count']);

        foreach ([
            'source_artifact_locks',
            'c125_lock_validation_summary',
            'lineage_validation_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'c126_handoff_audit_archive_decision',
            'next_readiness_decision',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_manifest',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_candidate_scorecard',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_context_summary',
            'runtime_readiness_inspection_summary',
            'feature_flag_operator_approval_kill_switch_validation_summary',
            'c125_handoff_closure_seal_carry_forward_validation_summary',
            'handoff_audit_archive_governance_summary',
            'production_mutation_safety_summary',
            'planned_next_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }
    }

    public function test_c126_keeps_e02_primary_b01_backup_a01_comparator_and_no_live_output(): void
    {
        $result = $this->runService();
        $manifest = $result['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_manifest'];

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

    public function test_c126_keeps_all_safety_flags_false(): void
    {
        $result = $this->runService();

        foreach ($this->requiredSafetyFlags() as $flag) {
            $this->assertFalse($result[$flag], $flag);
            $this->assertFalse($result['production_mutation_safety_summary'][$flag], $flag);
            $this->assertFalse($result['c126_handoff_audit_archive_decision'][$flag], $flag);
        }
    }

    public function test_c126_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $first = $this->execute(['options' => ['created_at' => '2026-06-30T00:00:00+00:00']]);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c126-handoff-audit-archive-pass-second.json';
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
        $service = new WatchlistBacktestC126WeeklySwingWatchlistControlledRuntimeWiringHandoffAuditArchiveReviewService();
        $fixture = $this->lockedC125Fixture();
        return $service->execute(
            (string) ($overrides['c125Artifact'] ?? $fixture['path']),
            (string) ($overrides['expectedC125Hash'] ?? $fixture['hash']),
            (string) ($overrides['expectedC125FileSha1'] ?? $fixture['sha1']),
            (string) ($overrides['output'] ?? $this->output),
            array_merge([
                'overwrite' => true,
                'operator_approved' => $overrides['operatorApproved'] ?? true,
                'approval_reference' => $overrides['approvalReference'] ?? 'C126_OPERATOR_APPROVED_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_REVIEW_ONLY',
                'handoff_audit_archive_confirmed' => true,
                'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_confirmed' => true,
            ], (array) ($overrides['options'] ?? []))
        );
    }

    private function mutateC125AndExecute(callable $mutator, string $name): array
    {
        $fixture = $this->lockedC125Fixture();
        $payload = json_decode((string) file_get_contents($fixture['path']), true);
        $payload = $mutator(is_array($payload) ? $payload : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c126-source-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
        return $this->execute([
            'c125Artifact' => $path,
            'expectedC125Hash' => (string) ($payload['artifact_hash'] ?? ''),
            'expectedC125FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function lockedC125Fixture(): array
    {
        $path = 'storage/app/watchlist/backtest/c125-weekly-swing-watchlist-controlled-runtime-wiring-handoff-closure-seal-review.json';
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

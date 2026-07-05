<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC125WeeklySwingWatchlistControlledRuntimeWiringHandoffClosureSealReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC125WeeklySwingWatchlistControlledRuntimeWiringHandoffClosureSealReviewTest extends TestCase
{
    private const PASS_STATUS = 'C125_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_CLOSURE_SEAL_REVIEW_PASSED_CLOSURE_SEALED_PRIMARY_AND_BACKUP';
    private const NEXT_C126 = 'C126_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_REVIEW';
    private const C124_HASH = '7c1079c3a5242cee7fbaa3a3a4afad1c100f50d1';
    private const C124_SHA1 = '8E8A5E878BA6B51E7FA99B754383171F13497ABD';

    private string $output;

    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c125-handoff-closure-seal-pass.json';
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

    public function test_c125_passes_with_valid_c124_boundary_cleared_lock_operator_approval_and_reference(): void
    {
        $result = $this->runService();

        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame('PR-13 / C125_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_CLOSURE_SEAL_REVIEW', $result['phase_label']);
        $this->assertSame($result['status'], $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_runtime_wiring_handoff_closure_seal_review_executed']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_runtime_wiring_handoff_closure_seal_review_allowed']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_runtime_wiring_handoff_closure_seal_review_pass']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_runtime_wiring_handoff_closure_sealed']);
        $this->assertTrue($result['controlled_runtime_wiring_handoff_closure_sealed']);
        $this->assertTrue($result['handoff_closure_sealed']);
        $this->assertTrue($result['closure_sealed']);
        $this->assertTrue($result['handoff_closure_seal_confirmed']);
        $this->assertSame('HANDOFF_CLOSURE_SEALED_GO', $result['handoff_closure_seal_go_decision']);
        $this->assertTrue($result['handoff_completion_boundary_cleared']);
        $this->assertTrue($result['handoff_completion_boundary_confirmed']);
        $this->assertSame('HANDOFF_COMPLETION_BOUNDARY_CLEARED_GO', $result['handoff_completion_boundary_go_decision']);
        $this->assertTrue($result['c124_handoff_completion_boundary_cleared']);
        $this->assertTrue($result['ready_for_controlled_runtime_wiring_handoff_audit_archive_review']);
        $this->assertTrue($result['controlled_runtime_wiring_handoff_closure_seal_manifest_created']);
        $this->assertTrue($result['controlled_runtime_wiring_handoff_audit_archive_review_allowed_next']);
        $this->assertTrue($result['primary_candidate_weekly_swing_controlled_runtime_wiring_handoff_closure_sealed']);
        $this->assertTrue($result['backup_candidate_weekly_swing_controlled_runtime_wiring_handoff_closure_sealed']);
        $this->assertFalse($result['comparator_candidate_weekly_swing_controlled_runtime_wiring_handoff_closure_sealed']);
        $this->assertTrue($result['a01_remains_comparator_only']);
        $this->assertSame(self::NEXT_C126, $result['next_step_recommendation']);
        $this->assertSame('READY_FOR_C126_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_REVIEW', $result['diagnostic_conclusion']);
        $this->assertFileExists($this->output);
    }

    public function test_c125_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->execute(['operatorApproved' => false]);
        $missingReference = $this->execute(['approvalReference' => '']);

        $this->assertSame('C125_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_CLOSURE_SEAL_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingOperator['status']);
        $this->assertSame('C125_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_CLOSURE_SEAL_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingReference['status']);
    }

    public function test_c125_rejects_unconfirmed_handoff_closure_seal(): void
    {
        $result = $this->execute(['options' => ['handoff_closure_seal_confirmed' => false]]);

        $this->assertSame('C125_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_CLOSURE_SEAL_REVIEW_REJECTED_CLOSURE_SEAL_NOT_CONFIRMED', $result['status']);
        $this->assertFalse($result['handoff_closure_sealed']);
    }

    public function test_c125_rejects_missing_or_mismatched_c124_artifact_lock(): void
    {
        $missing = $this->execute([
            'c124Artifact' => 'storage/app/watchlist/backtest/missing-c124-for-c125.json',
            'expectedC124Hash' => 'missing',
            'expectedC124FileSha1' => 'missing',
        ]);
        $hashMismatch = $this->execute(['expectedC124Hash' => 'bad-hash']);
        $shaMismatch = $this->execute(['expectedC124FileSha1' => 'BADSHA1']);

        $this->assertSame('C125_BLOCKED_C124_ARTIFACT_LOCK_MISMATCH', $missing['status']);
        $this->assertSame('C125_BLOCKED_C124_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C125_BLOCKED_C124_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
    }

    public function test_c125_rejects_c124_status_reason_or_next_recommendation_mismatch(): void
    {
        $status = $this->mutateC124AndExecute(function (array $c124): array {
            $c124['status'] = 'BROKEN_STATUS';
            return $c124;
        }, 'c124-status-broken');
        $reason = $this->mutateC124AndExecute(function (array $c124): array {
            $c124['reason_code'] = 'BROKEN_REASON';
            return $c124;
        }, 'c124-reason-broken');
        $next = $this->mutateC124AndExecute(function (array $c124): array {
            $c124['next_step_recommendation'] = 'BROKEN_NEXT';
            $c124['next_readiness_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c124['c124_handoff_completion_boundary_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c124['weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c124['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $c124;
        }, 'c124-next-broken');

        $this->assertSame('C125_BLOCKED_C124_STATUS_OR_REASON_MISMATCH', $status['status']);
        $this->assertSame('C125_BLOCKED_C124_STATUS_OR_REASON_MISMATCH', $reason['status']);
        $this->assertSame('C125_BLOCKED_C124_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
    }

    public function test_c125_rejects_c124_phase_label_mismatch(): void
    {
        $result = $this->mutateC124AndExecute(function (array $c124): array {
            $c124['phase_label'] = 'BROKEN_PHASE_LABEL';
            return $c124;
        }, 'c124-phase-broken');

        $this->assertSame('C125_BLOCKED_C124_PHASE_LABEL_MISMATCH', $result['status']);
    }

    public function test_c125_rejects_c124_convert_from_json_duplicate_top_level_keys(): void
    {
        $fixture = $this->lockedC124Fixture();
        $raw = (string) file_get_contents($fixture['path']);
        $path = 'storage/app/watchlist/backtest/.tmp-c125-source-c124-duplicate-key.json';
        $this->tmpFiles[] = $path;
        $duplicateRaw = preg_replace('/\{/', "{\"Run_Code\":\"DUPLICATE_CASE_KEY\",", $raw, 1);
        file_put_contents($path, $duplicateRaw);

        $result = $this->execute([
            'c124Artifact' => $path,
            'expectedC124Hash' => $fixture['hash'],
            'expectedC124FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);

        $this->assertSame('C125_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_CLOSURE_SEAL_REVIEW_REJECTED_C124_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION', $result['status']);
        $this->assertFalse($result['c124_convert_from_json_pass']);
        $this->assertContains('run_code', array_map('strtolower', $result['c124_convert_from_json_duplicate_keys']));
    }

    /**
     * @dataProvider c124BoundaryClearedStateMismatchProvider
     */
    public function test_c125_rejects_c124_boundary_cleared_state_mismatch(string $field, $value): void
    {
        $result = $this->mutateC124AndExecute(function (array $c124) use ($field, $value): array {
            $c124[$field] = $value;
            $c124['c124_handoff_completion_boundary_decision'][$field] = $value;
            return $c124;
        }, 'c124-state-'.$field);

        $this->assertSame('C125_BLOCKED_C124_HANDOFF_COMPLETION_BOUNDARY_NOT_CLEARED', $result['status'], $field);
    }

    public function c124BoundaryClearedStateMismatchProvider(): array
    {
        return [
            ['weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_review_pass', false],
            ['weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_cleared', false],
            ['handoff_completion_boundary_cleared', false],
            ['weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_confirmed', false],
            ['handoff_completion_boundary_confirmed', false],
            ['handoff_completion_boundary_go_decision', 'NO_GO'],
            ['controlled_runtime_wiring_handoff_finalized', false],
            ['handoff_finalized', false],
            ['controlled_runtime_wiring_handoff_ready', false],
            ['handoff_ready', false],
            ['ready_for_controlled_runtime_wiring_handoff_closure_seal_review', false],
            ['weekly_swing_watchlist_ready_for_controlled_runtime_wiring_handoff_closure_seal_review', false],
            ['controlled_runtime_wiring_handoff_completion_boundary_manifest_created', false],
            ['controlled_runtime_wiring_handoff_closure_seal_review_allowed_next', false],
            ['completion_boundary_cleared', false],
            ['boundary_go_decision', 'NO_GO'],
            ['operator_go_decision', 'NO_GO'],
            ['go_decision_finalized', false],
            ['primary_candidate_weekly_swing_controlled_runtime_wiring_handoff_completion_boundary_cleared', false],
            ['backup_candidate_weekly_swing_controlled_runtime_wiring_handoff_completion_boundary_cleared', false],
            ['comparator_candidate_weekly_swing_controlled_runtime_wiring_handoff_completion_boundary_cleared', true],
            ['primary_candidate_ready_for_controlled_runtime_wiring_handoff_closure_seal_review', false],
            ['backup_candidate_ready_for_controlled_runtime_wiring_handoff_closure_seal_review', false],
            ['comparator_candidate_ready_for_controlled_runtime_wiring_handoff_closure_seal_review', true],
            ['a01_remains_comparator_only', false],
        ];
    }

    public function test_c125_rejects_c124_c125_readiness_count_mismatch(): void
    {
        $result = $this->mutateC124AndExecute(function (array $c124): array {
            $c124['next_readiness_decision']['candidate_ready_for_weekly_swing_watchlist_controlled_runtime_wiring_handoff_closure_seal_review_count'] = 1;
            return $c124;
        }, 'c124-readiness-count-broken');

        $this->assertSame('C125_BLOCKED_C124_HANDOFF_COMPLETION_BOUNDARY_NOT_CLEARED', $result['status']);
    }

    public function test_c125_rejects_c124_manifest_not_artifact_only_or_used_for_live_output(): void
    {
        $notArtifactOnly = $this->mutateC124AndExecute(function (array $c124): array {
            $c124['weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_manifest']['handoff_completion_boundary_artifact_only'] = false;
            return $c124;
        }, 'c124-manifest-not-artifact-only');
        $usedForPlanConfirm = $this->mutateC124AndExecute(function (array $c124): array {
            $c124['weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_manifest']['handoff_completion_boundary_used_for_plan_confirm_mutation'] = true;
            return $c124;
        }, 'c124-manifest-plan-confirm');
        $officialOutput = $this->mutateC124AndExecute(function (array $c124): array {
            $c124['weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_manifest']['official_weekly_swing_stock_recommendations'] = ['SHOULD_NOT_EXIST'];
            return $c124;
        }, 'c124-manifest-official-output');

        $this->assertSame('C125_BLOCKED_C124_HANDOFF_COMPLETION_BOUNDARY_NOT_CLEARED', $notArtifactOnly['status']);
        $this->assertSame('C125_BLOCKED_C124_HANDOFF_COMPLETION_BOUNDARY_NOT_CLEARED', $usedForPlanConfirm['status']);
        $this->assertSame('C125_BLOCKED_C124_HANDOFF_COMPLETION_BOUNDARY_NOT_CLEARED', $officialOutput['status']);
    }

    public function test_c125_rejects_candidate_scope_change_or_a01_promotion(): void
    {
        $result = $this->mutateC124AndExecute(function (array $c124): array {
            $c124['candidate_scope_freeze_summary']['a01_promoted'] = true;
            return $c124;
        }, 'c124-a01-promoted');

        $this->assertSame('C125_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_CLOSURE_SEAL_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $result['status']);
    }

    /**
     * @dataProvider safetyFlagProvider
     */
    public function test_c125_rejects_any_live_or_mutating_safety_flag_true_in_c124(string $field): void
    {
        $result = $this->mutateC124AndExecute(function (array $c124) use ($field): array {
            $c124[$field] = true;
            return $c124;
        }, 'c124-safety-'.$field);

        $this->assertSame('C125_BLOCKED_C124_SAFETY_FLAG_ALREADY_LIVE_OR_MUTATING', $result['status'], $field);
        $this->assertSame($field, $result['c124_live_or_mutating_safety_flag_failure']);
    }

    public function safetyFlagProvider(): array
    {
        return array_map(static function (string $flag): array {
            return [$flag];
        }, $this->requiredSafetyFlags());
    }

    public function test_c125_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c125-no-temporary-artifact-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame('C125_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_CLOSURE_SEAL_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS', $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c125_records_artifact_hash_locks_next_and_required_sections(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $this->assertSame(self::C124_HASH, $result['expected_c124_hash']);
        $this->assertSame(self::C124_HASH, $result['actual_c124_hash']);
        $this->assertTrue($result['c124_hash_match']);
        $this->assertSame(self::C124_SHA1, $result['expected_c124_file_sha1']);
        $this->assertSame(self::C124_SHA1, $result['actual_c124_file_sha1']);
        $this->assertTrue($result['c124_file_sha1_match']);
        $this->assertTrue($result['c124_convert_from_json_pass']);
        $this->assertTrue($result['c124_lock_validation_summary']['c124_phase_label_match']);
        $this->assertSame(self::NEXT_C126, $result['next_readiness_decision']['next_recommendation']);
        $this->assertSame(2, $result['next_readiness_decision']['candidate_ready_for_weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_review_count']);

        foreach ([
            'source_artifact_locks',
            'c124_lock_validation_summary',
            'lineage_validation_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'c125_handoff_closure_seal_decision',
            'next_readiness_decision',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_closure_seal_manifest',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_closure_seal_candidate_scorecard',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_closure_seal_context_summary',
            'c124_handoff_completion_boundary_carry_forward_validation_summary',
            'handoff_closure_seal_governance_summary',
            'production_mutation_safety_summary',
            'planned_next_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }
    }

    public function test_c125_keeps_e02_primary_b01_backup_a01_comparator_and_no_live_output(): void
    {
        $result = $this->runService();
        $manifest = $result['weekly_swing_watchlist_controlled_runtime_wiring_handoff_closure_seal_manifest'];

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $result['primary_candidate_code']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $result['backup_candidate_code']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $result['comparator_candidate_code']);
        $this->assertFalse($result['candidate_scope_freeze_summary']['a01_promoted']);
        $this->assertTrue($manifest['handoff_closure_seal_artifact_only']);
        $this->assertTrue($manifest['handoff_closure_sealed']);
        $this->assertFalse($manifest['handoff_closure_seal_used_for_plan_confirm_mutation']);
        $this->assertFalse($manifest['handoff_closure_seal_used_for_live_rollout']);
        $this->assertSame([], $manifest['official_weekly_swing_stock_recommendations']);
        $this->assertFalse($result['weekly_swing_watchlist_official_output_generated']);
        $this->assertFalse($result['weekly_swing_watchlist_live_recommendation_generated']);
    }

    public function test_c125_keeps_all_safety_flags_false(): void
    {
        $result = $this->runService();

        foreach ($this->requiredSafetyFlags() as $flag) {
            $this->assertFalse($result[$flag], $flag);
            $this->assertFalse($result['production_mutation_safety_summary'][$flag], $flag);
            $this->assertFalse($result['c125_handoff_closure_seal_decision'][$flag], $flag);
        }
    }

    public function test_c125_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $first = $this->execute(['options' => ['created_at' => '2026-06-30T00:00:00+00:00']]);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c125-handoff-closure-seal-pass-second.json';
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
        $service = new WatchlistBacktestC125WeeklySwingWatchlistControlledRuntimeWiringHandoffClosureSealReviewService();
        $fixture = $this->lockedC124Fixture();
        return $service->execute(
            (string) ($overrides['c124Artifact'] ?? $fixture['path']),
            (string) ($overrides['expectedC124Hash'] ?? $fixture['hash']),
            (string) ($overrides['expectedC124FileSha1'] ?? $fixture['sha1']),
            (string) ($overrides['output'] ?? $this->output),
            array_merge([
                'overwrite' => true,
                'operator_approved' => $overrides['operatorApproved'] ?? true,
                'approval_reference' => $overrides['approvalReference'] ?? 'C125_OPERATOR_APPROVED_CONTROLLED_RUNTIME_WIRING_HANDOFF_CLOSURE_SEAL_REVIEW_ONLY',
                'handoff_closure_seal_confirmed' => true,
                'weekly_swing_watchlist_controlled_runtime_wiring_handoff_closure_seal_confirmed' => true,
            ], (array) ($overrides['options'] ?? []))
        );
    }

    private function mutateC124AndExecute(callable $mutator, string $name): array
    {
        $fixture = $this->lockedC124Fixture();
        $payload = json_decode((string) file_get_contents($fixture['path']), true);
        $payload = $mutator(is_array($payload) ? $payload : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c125-source-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
        return $this->execute([
            'c124Artifact' => $path,
            'expectedC124Hash' => (string) ($payload['artifact_hash'] ?? ''),
            'expectedC124FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function lockedC124Fixture(): array
    {
        $path = 'storage/app/watchlist/backtest/c124-weekly-swing-watchlist-controlled-runtime-wiring-handoff-completion-boundary-review.json';
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

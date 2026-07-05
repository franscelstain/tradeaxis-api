<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC129WeeklySwingWatchlistControlledRuntimeWiringHandoffAuditArchiveFinalClosureReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC129WeeklySwingWatchlistControlledRuntimeWiringHandoffAuditArchiveFinalClosureReviewTest extends TestCase
{
    private const PASS_STATUS = 'C129_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_PASSED_AUDIT_ARCHIVE_FINAL_CLOSED_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C129_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const FINAL_CLOSURE_NOT_CONFIRMED_STATUS = 'C129_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_REJECTED_AUDIT_ARCHIVE_FINAL_CLOSURE_NOT_CONFIRMED';
    private const TEMP_NEGATIVE_STATUS = 'C129_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const NO_NEXT = 'NO_NEXT_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_REVIEW_REQUIRED';
    private const C128_HASH = '6ef4c4f7868f71fa3855c3db3a2e1372af201f68';
    private const C128_SHA1 = '33C094BFA0FF23952E68EB0E45A7C9AE092F9A82';

    private string $output;

    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c129-handoff-audit-archive-final-closure-pass.json';
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

    public function test_c129_passes_with_valid_c128_completion_seal_lock_operator_approval_reference_and_final_closure_confirmation(): void
    {
        $result = $this->runService();

        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame('PR-17 / C129_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW', $result['phase_label']);
        $this->assertSame($result['status'], $result['reason_code']);
        $this->assertTrue($result['handoff_audit_archive_completion_ready']);
        $this->assertTrue($result['handoff_audit_archive_completion_sealed']);
        $this->assertTrue($result['audit_archive_completion_sealed']);
        $this->assertTrue($result['completion_seal_manifest_created']);
        $this->assertTrue($result['handoff_audit_archive_completion_seal_confirmed']);
        $this->assertSame('HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEALED_GO', $result['handoff_audit_archive_completion_seal_go_decision']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_final_closure_review_executed']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_final_closure_review_allowed']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_final_closure_review_pass']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_final_closed']);
        $this->assertTrue($result['controlled_runtime_wiring_handoff_audit_archive_final_closed']);
        $this->assertTrue($result['handoff_audit_archive_final_closed']);
        $this->assertTrue($result['audit_archive_final_closed']);
        $this->assertTrue($result['final_closure_manifest_created']);
        $this->assertTrue($result['handoff_audit_archive_final_closure_confirmed']);
        $this->assertSame('HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSED_GO', $result['handoff_audit_archive_final_closure_go_decision']);
        $this->assertTrue($result['primary_candidate_handoff_audit_archive_final_closed']);
        $this->assertTrue($result['backup_candidate_handoff_audit_archive_final_closed']);
        $this->assertFalse($result['comparator_candidate_handoff_audit_archive_final_closed']);
        $this->assertTrue($result['a01_remains_comparator_only']);
        $this->assertSame(self::NO_NEXT, $result['next_step_recommendation']);
        $this->assertSame('C129_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSED_CONTROLLED_RUNTIME_WIRING_AUDIT_ONLY', $result['diagnostic_conclusion']);
        $this->assertFileExists($this->output);
    }

    public function test_c129_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->execute(['operatorApproved' => false]);
        $missingReference = $this->execute(['approvalReference' => '']);

        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingOperator['status']);
        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingReference['status']);
    }

    public function test_c129_rejects_missing_final_closure_confirmation(): void
    {
        $result = $this->execute(['options' => ['handoff_audit_archive_final_closure_confirmed' => false]]);

        $this->assertSame(self::FINAL_CLOSURE_NOT_CONFIRMED_STATUS, $result['status']);
        $this->assertFalse($result['handoff_audit_archive_final_closed']);
    }

    public function test_c129_rejects_missing_or_mismatched_c128_artifact_lock(): void
    {
        $missing = $this->execute([
            'c128Artifact' => 'storage/app/watchlist/backtest/missing-c128-for-c129.json',
            'expectedC128Hash' => 'missing',
            'expectedC128FileSha1' => 'missing',
        ]);
        $hashMismatch = $this->execute(['expectedC128Hash' => 'bad-hash']);
        $shaMismatch = $this->execute(['expectedC128FileSha1' => 'BADSHA1']);

        $this->assertSame('C129_BLOCKED_C128_ARTIFACT_LOCK_MISMATCH', $missing['status']);
        $this->assertSame('C129_BLOCKED_C128_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C129_BLOCKED_C128_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
    }

    public function test_c129_rejects_c128_status_reason_phase_or_next_recommendation_mismatch(): void
    {
        $status = $this->mutateC128AndExecute(function (array $c128): array {
            $c128['status'] = 'BROKEN_STATUS';
            return $c128;
        }, 'status-broken');
        $reason = $this->mutateC128AndExecute(function (array $c128): array {
            $c128['reason_code'] = 'BROKEN_REASON';
            return $c128;
        }, 'reason-broken');
        $phase = $this->mutateC128AndExecute(function (array $c128): array {
            $c128['phase_label'] = 'BROKEN_PHASE';
            return $c128;
        }, 'phase-broken');
        $next = $this->mutateC128AndExecute(function (array $c128): array {
            $c128['next_step_recommendation'] = 'BROKEN_NEXT';
            $c128['next_readiness_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c128['c128_readiness_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c128['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c128['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $c128;
        }, 'next-broken');

        $this->assertSame('C129_BLOCKED_C128_STATUS_OR_REASON_MISMATCH', $status['status']);
        $this->assertSame('C129_BLOCKED_C128_STATUS_OR_REASON_MISMATCH', $reason['status']);
        $this->assertSame('C129_BLOCKED_C128_PHASE_LABEL_MISMATCH', $phase['status']);
        $this->assertSame('C129_BLOCKED_C128_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
    }

    public function test_c129_rejects_c128_convert_from_json_duplicate_top_level_keys(): void
    {
        $fixture = $this->lockedC128Fixture();
        $raw = (string) file_get_contents($fixture['path']);
        $path = 'storage/app/watchlist/backtest/.tmp-c129-source-c128-duplicate-key.json';
        $this->tmpFiles[] = $path;
        $duplicateRaw = preg_replace('/\{/', "{\"Run_Code\":\"DUPLICATE_CASE_KEY\",", $raw, 1);
        file_put_contents($path, $duplicateRaw);

        $result = $this->execute([
            'c128Artifact' => $path,
            'expectedC128Hash' => $fixture['hash'],
            'expectedC128FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);

        $this->assertSame('C129_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_REJECTED_C128_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION', $result['status']);
        $this->assertFalse($result['c128_convert_from_json_pass']);
        $this->assertContains('run_code', array_map('strtolower', $result['c128_convert_from_json_duplicate_keys']));
    }

    /**
     * @dataProvider c128CompletionSealStateMismatchProvider
     */
    public function test_c129_rejects_c128_completion_seal_state_mismatch(string $field, $value): void
    {
        $result = $this->mutateC128AndExecute(function (array $c128) use ($field, $value): array {
            $c128[$field] = $value;
            return $c128;
        }, 'state-'.$field);

        $this->assertSame('C129_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_REJECTED_C128_AUDIT_ARCHIVE_COMPLETION_SEAL_INCOMPLETE', $result['status'], $field);
    }

    public function c128CompletionSealStateMismatchProvider(): array
    {
        return [
            ['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_review_pass', false],
            ['handoff_audit_archive_completion_ready', false],
            ['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review_pass', false],
            ['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_sealed', false],
            ['controlled_runtime_wiring_handoff_audit_archive_completion_sealed', false],
            ['handoff_audit_archive_completion_sealed', false],
            ['audit_archive_completion_sealed', false],
            ['completion_seal_manifest_created', false],
            ['handoff_audit_archive_completion_seal_confirmed', false],
            ['handoff_audit_archive_completion_seal_go_decision', 'NO_GO'],
            ['ready_for_controlled_runtime_wiring_handoff_audit_archive_final_closure_review', false],
            ['controlled_runtime_wiring_handoff_audit_archive_final_closure_review_allowed_next', false],
            ['primary_candidate_handoff_audit_archive_completion_sealed', false],
            ['backup_candidate_handoff_audit_archive_completion_sealed', false],
            ['comparator_candidate_handoff_audit_archive_completion_sealed', true],
            ['handoff_audit_archived', false],
            ['handoff_closure_sealed', false],
            ['handoff_completion_boundary_cleared', false],
            ['handoff_finalized', false],
            ['handoff_ready', false],
            ['a01_remains_comparator_only', false],
        ];
    }

    public function test_c129_rejects_c128_manifest_not_artifact_only_or_used_for_live_output(): void
    {
        $notArtifactOnly = $this->mutateC128AndExecute(function (array $c128): array {
            $c128['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_manifest']['completion_seal_artifact_only'] = false;
            return $c128;
        }, 'manifest-not-artifact-only');
        $usedForPlanConfirm = $this->mutateC128AndExecute(function (array $c128): array {
            $c128['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_manifest']['completion_seal_used_for_plan_confirm_mutation'] = true;
            return $c128;
        }, 'manifest-plan-confirm');

        $this->assertSame('C129_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_REJECTED_C128_AUDIT_ARCHIVE_COMPLETION_SEAL_INCOMPLETE', $notArtifactOnly['status']);
        $this->assertSame('C129_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_REJECTED_C128_AUDIT_ARCHIVE_COMPLETION_SEAL_INCOMPLETE', $usedForPlanConfirm['status']);
    }

    public function test_c129_rejects_candidate_scope_change_or_a01_promotion(): void
    {
        $result = $this->mutateC128AndExecute(function (array $c128): array {
            $c128['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $c128;
        }, 'candidate-primary-broken');
        $a01 = $this->mutateC128AndExecute(function (array $c128): array {
            $c128['a01_promoted'] = true;
            return $c128;
        }, 'candidate-a01-promoted');

        $this->assertSame('C129_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $result['status']);
        $this->assertSame('C129_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $a01['status']);
    }

    /**
     * @dataProvider safetyFlagProvider
     */
    public function test_c129_rejects_any_live_or_mutating_safety_flag_true_in_c128(string $field): void
    {
        $result = $this->mutateC128AndExecute(function (array $c128) use ($field): array {
            $c128[$field] = true;
            return $c128;
        }, 'safety-'.$field);

        $this->assertSame('C129_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_REJECTED_LIVE_OR_PRODUCTION_MUTATION_ATTEMPT', $result['status'], $field);
        $this->assertSame($field, $result['c128_live_or_mutating_safety_flag_failure']);
    }

    public function safetyFlagProvider(): array
    {
        return array_map(static function (string $flag): array {
            return [$flag];
        }, $this->requiredSafetyFlags());
    }

    public function test_c129_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c129-no-temporary-artifact-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame(self::TEMP_NEGATIVE_STATUS, $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c129_records_artifact_hash_source_locks_no_next_and_required_sections(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $this->assertSame(self::C128_HASH, $result['expected_c128_hash']);
        $this->assertSame(self::C128_HASH, $result['actual_c128_hash']);
        $this->assertTrue($result['c128_hash_match']);
        $this->assertSame(self::C128_SHA1, $result['expected_c128_file_sha1']);
        $this->assertSame(self::C128_SHA1, $result['actual_c128_file_sha1']);
        $this->assertTrue($result['c128_file_sha1_match']);
        $this->assertTrue($result['c128_convert_from_json_pass']);
        $this->assertSame(self::NO_NEXT, $result['next_readiness_decision']['next_recommendation']);

        foreach ([
            'source_artifact_locks',
            'c128_lock_validation_summary',
            'c122_c128_handoff_lineage_final_closure_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'c129_readiness_decision',
            'next_readiness_decision',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_final_closure_decision',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_final_closure_manifest',
            'c129_candidate_audit_archive_final_closure_scorecard',
            'handoff_audit_archive_final_closure_context_summary',
            'runtime_readiness_inspection_summary',
            'feature_flag_operator_approval_kill_switch_validation_summary',
            'c128_handoff_audit_archive_completion_seal_carry_forward_validation_summary',
            'handoff_audit_archive_final_closure_governance_summary',
            'baseline_plan_confirm_non_mutation_summary',
            'production_mutation_safety_summary',
            'documentation_governance_summary',
            'planned_next_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }
    }

    public function test_c129_keeps_e02_primary_b01_backup_a01_comparator_and_no_live_output(): void
    {
        $result = $this->runService();
        $manifest = $result['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_final_closure_manifest'];

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $result['primary_candidate_code']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $result['backup_candidate_code']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $result['comparator_candidate_code']);
        $this->assertFalse($result['candidate_scope_freeze_summary']['candidate_promotion_executed']);
        $this->assertSame('comparator_only_not_promoted', $manifest['comparator_candidate_role']);
        $this->assertTrue($manifest['final_closure_artifact_only']);
        $this->assertTrue($manifest['handoff_audit_archive_completion_sealed_carried_forward']);
        $this->assertTrue($manifest['handoff_audit_archive_final_closed']);
        $this->assertFalse($manifest['final_closure_used_for_plan_confirm_mutation']);
        $this->assertFalse($manifest['final_closure_used_for_live_rollout']);
        $this->assertSame([], $manifest['official_weekly_swing_stock_recommendations']);
        $this->assertFalse($result['weekly_swing_watchlist_official_output_generated']);
        $this->assertFalse($result['weekly_swing_watchlist_live_recommendation_generated']);
    }

    public function test_c129_keeps_all_safety_flags_false(): void
    {
        $result = $this->runService();

        foreach ($this->requiredSafetyFlags() as $flag) {
            $this->assertFalse($result[$flag], $flag);
            $this->assertFalse($result['production_mutation_safety_summary'][$flag], $flag);
        }
        $this->assertFalse($result['c129_readiness_decision']['production_ready']);
        $this->assertFalse($result['handoff_audit_archive_final_closure_context_summary']['context_persisted_to_live_runtime']);
        $this->assertFalse($result['handoff_audit_archive_final_closure_context_summary']['handoff_audit_archive_final_closure_context_persisted_to_live_runtime']);
    }

    public function test_c129_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $first = $this->execute(['options' => ['created_at' => '2026-06-30T00:00:00+00:00']]);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c129-handoff-audit-archive-final-closure-pass-second.json';
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
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_final_closure_context_persisted_to_live_runtime',
            'controlled_runtime_wiring_handoff_audit_archive_final_closure_context_persisted_to_live_runtime',
            'handoff_audit_archive_final_closure_context_persisted_to_live_runtime',
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
        $service = new WatchlistBacktestC129WeeklySwingWatchlistControlledRuntimeWiringHandoffAuditArchiveFinalClosureReviewService();
        $fixture = $this->lockedC128Fixture();
        return $service->execute(
            (string) ($overrides['c128Artifact'] ?? $fixture['path']),
            (string) ($overrides['expectedC128Hash'] ?? $fixture['hash']),
            (string) ($overrides['expectedC128FileSha1'] ?? $fixture['sha1']),
            (string) ($overrides['output'] ?? $this->output),
            array_merge([
                'overwrite' => true,
                'operator_approved' => $overrides['operatorApproved'] ?? true,
                'approval_reference' => $overrides['approvalReference'] ?? 'C129_OPERATOR_APPROVED_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_ONLY',
                'handoff_audit_archive_final_closure_confirmed' => true,
                'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_final_closure_confirmed' => true,
            ], (array) ($overrides['options'] ?? []))
        );
    }

    private function mutateC128AndExecute(callable $mutator, string $name): array
    {
        $fixture = $this->lockedC128Fixture();
        $payload = json_decode((string) file_get_contents($fixture['path']), true);
        $payload = $mutator(is_array($payload) ? $payload : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c129-source-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
        return $this->execute([
            'c128Artifact' => $path,
            'expectedC128Hash' => (string) ($payload['artifact_hash'] ?? ''),
            'expectedC128FileSha1' => strtoupper(sha1_file($path)),
        ]);
    }

    private function lockedC128Fixture(): array
    {
        $path = 'storage/app/watchlist/backtest/c128-weekly-swing-watchlist-controlled-runtime-wiring-handoff-audit-archive-completion-seal-review.json';
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

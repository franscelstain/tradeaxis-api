<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC123WeeklySwingWatchlistControlledRuntimeWiringHandoffFinalizationReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC123WeeklySwingWatchlistControlledRuntimeWiringHandoffFinalizationReviewTest extends TestCase
{
    private const PASS_STATUS = 'C123_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_FINALIZATION_REVIEW_PASSED_HANDOFF_FINALIZED_PRIMARY_AND_BACKUP';
    private const NEXT_C124 = 'C124_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_COMPLETION_BOUNDARY_REVIEW';
    private const C122_HASH = '0edfa166bfa8f195db6dfd09f318b6e0515cc5c7';
    private const C122_SHA1 = 'FF830FE04623A636F86E514120575BD57A98EEB4';

    private string $output;

    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c123-handoff-finalization-pass.json';
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

    public function test_c123_passes_with_valid_c122_handoff_ready_lock_operator_approval_and_reference(): void
    {
        $result = $this->runService();

        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame($result['status'], $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_review_executed']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_review_allowed']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_review_pass']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalized']);
        $this->assertTrue($result['controlled_runtime_wiring_handoff_finalized']);
        $this->assertTrue($result['handoff_finalized']);
        $this->assertTrue($result['handoff_finalization_confirmed']);
        $this->assertSame('HANDOFF_FINALIZED_GO', $result['handoff_finalization_go_decision']);
        $this->assertTrue($result['handoff_ready']);
        $this->assertTrue($result['c122_handoff_ready']);
        $this->assertTrue($result['ready_for_controlled_runtime_wiring_handoff_completion_boundary_review']);
        $this->assertTrue($result['controlled_runtime_wiring_handoff_completion_boundary_review_allowed_next']);
        $this->assertTrue($result['primary_candidate_weekly_swing_controlled_runtime_wiring_handoff_finalized']);
        $this->assertTrue($result['backup_candidate_weekly_swing_controlled_runtime_wiring_handoff_finalized']);
        $this->assertFalse($result['comparator_candidate_weekly_swing_controlled_runtime_wiring_handoff_finalized']);
        $this->assertTrue($result['a01_remains_comparator_only']);
        $this->assertSame(self::NEXT_C124, $result['next_step_recommendation']);
        $this->assertSame('READY_FOR_C124_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_COMPLETION_BOUNDARY_REVIEW', $result['diagnostic_conclusion']);
        $this->assertFileExists($this->output);
    }

    public function test_c123_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->execute(['operatorApproved' => false]);
        $missingReference = $this->execute(['approvalReference' => '']);

        $this->assertSame('C123_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingOperator['status']);
        $this->assertSame('C123_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingReference['status']);
    }

    public function test_c123_rejects_unconfirmed_handoff_finalization(): void
    {
        $result = $this->execute(['options' => ['handoff_finalization_confirmed' => false]]);

        $this->assertSame('C123_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_FINALIZATION_REVIEW_REJECTED_HANDOFF_FINALIZATION_NOT_CONFIRMED', $result['status']);
        $this->assertFalse($result['handoff_finalized']);
    }

    public function test_c123_rejects_missing_or_mismatched_c122_artifact_lock(): void
    {
        $missing = $this->execute([
            'c122Artifact' => 'storage/app/watchlist/backtest/missing-c122-for-c123.json',
            'expectedC122Hash' => 'missing',
            'expectedC122FileSha1' => 'missing',
        ]);
        $hashMismatch = $this->execute([
            'expectedC122Hash' => 'bad-hash',
        ]);
        $shaMismatch = $this->execute([
            'expectedC122FileSha1' => 'BADSHA1',
        ]);

        $this->assertSame('C123_BLOCKED_C122_ARTIFACT_LOCK_MISMATCH', $missing['status']);
        $this->assertSame('C123_BLOCKED_C122_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C123_BLOCKED_C122_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
    }

    public function test_c123_rejects_c122_status_reason_or_next_recommendation_mismatch(): void
    {
        $status = $this->mutateC122AndExecute(function (array $c122): array {
            $c122['status'] = 'BROKEN_STATUS';
            return $c122;
        }, 'c122-status-broken');
        $reason = $this->mutateC122AndExecute(function (array $c122): array {
            $c122['reason_code'] = 'BROKEN_REASON';
            return $c122;
        }, 'c122-reason-broken');
        $next = $this->mutateC122AndExecute(function (array $c122): array {
            $c122['next_step_recommendation'] = 'BROKEN_NEXT';
            $c122['next_readiness_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c122['c122_handoff_readiness_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c122['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $c122;
        }, 'c122-next-broken');

        $this->assertSame('C123_BLOCKED_C122_STATUS_OR_REASON_MISMATCH', $status['status']);
        $this->assertSame('C123_BLOCKED_C122_STATUS_OR_REASON_MISMATCH', $reason['status']);
        $this->assertSame('C123_BLOCKED_C122_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
    }

    /**
     * @dataProvider c122HandoffReadyStateMismatchProvider
     */
    public function test_c123_rejects_c122_handoff_ready_state_mismatch(string $field, $value): void
    {
        $result = $this->mutateC122AndExecute(function (array $c122) use ($field, $value): array {
            $c122[$field] = $value;
            $c122['c122_handoff_readiness_decision'][$field] = $value;
            $c122['next_readiness_decision'][$field] = $value;
            return $c122;
        }, 'c122-state-'.$field);

        $this->assertSame('C123_BLOCKED_C122_HANDOFF_NOT_READY', $result['status'], $field);
    }

    public function c122HandoffReadyStateMismatchProvider(): array
    {
        return [
            ['weekly_swing_watchlist_controlled_runtime_wiring_handoff_readiness_review_pass', false],
            ['controlled_runtime_wiring_handoff_readiness_review_pass', false],
            ['weekly_swing_watchlist_controlled_runtime_wiring_handoff_ready', false],
            ['controlled_runtime_wiring_handoff_ready', false],
            ['handoff_ready', false],
            ['completion_boundary_cleared', false],
            ['completion_boundary_confirmed', false],
            ['handoff_readiness_confirmed', false],
            ['handoff_readiness_go_decision', 'NO_GO'],
            ['ready_for_controlled_runtime_wiring_handoff_finalization_review', false],
            ['weekly_swing_watchlist_ready_for_controlled_runtime_wiring_handoff_finalization_review', false],
            ['controlled_runtime_wiring_handoff_readiness_manifest_created', false],
            ['controlled_runtime_wiring_handoff_finalization_review_allowed_next', false],
            ['primary_candidate_ready_for_controlled_runtime_wiring_handoff_finalization_review', false],
            ['backup_candidate_ready_for_controlled_runtime_wiring_handoff_finalization_review', false],
            ['comparator_candidate_ready_for_controlled_runtime_wiring_handoff_finalization_review', true],
        ];
    }

    public function test_c123_rejects_c122_nested_next_handoff_finalization_mismatch(): void
    {
        $result = $this->mutateC122AndExecute(function (array $c122): array {
            $c122['next_handoff_finalization_decision']['next_recommendation'] = 'BROKEN_NEXT';
            return $c122;
        }, 'c122-nested-next-broken');

        $this->assertSame('C123_BLOCKED_C122_NEXT_RECOMMENDATION_MISMATCH', $result['status']);
    }

    public function test_c123_rejects_c122_manifest_not_artifact_only_or_used_for_live_output(): void
    {
        $notArtifactOnly = $this->mutateC122AndExecute(function (array $c122): array {
            $c122['weekly_swing_watchlist_controlled_runtime_wiring_handoff_readiness_manifest']['handoff_readiness_artifact_only'] = false;
            return $c122;
        }, 'c122-manifest-not-artifact-only');
        $usedForPlanConfirm = $this->mutateC122AndExecute(function (array $c122): array {
            $c122['weekly_swing_watchlist_controlled_runtime_wiring_handoff_readiness_manifest']['handoff_readiness_used_for_plan_confirm_mutation'] = true;
            return $c122;
        }, 'c122-manifest-plan-confirm');
        $officialOutput = $this->mutateC122AndExecute(function (array $c122): array {
            $c122['weekly_swing_watchlist_controlled_runtime_wiring_handoff_readiness_manifest']['official_weekly_swing_stock_recommendations'] = ['SHOULD_NOT_EXIST'];
            return $c122;
        }, 'c122-manifest-official-output');

        $this->assertSame('C123_BLOCKED_C122_HANDOFF_NOT_READY', $notArtifactOnly['status']);
        $this->assertSame('C123_BLOCKED_C122_HANDOFF_NOT_READY', $usedForPlanConfirm['status']);
        $this->assertSame('C123_BLOCKED_C122_HANDOFF_NOT_READY', $officialOutput['status']);
    }

    /**
     * @dataProvider safetyFlagProvider
     */
    public function test_c123_rejects_any_live_or_mutating_safety_flag_true_in_c122(string $field): void
    {
        $result = $this->mutateC122AndExecute(function (array $c122) use ($field): array {
            $c122[$field] = true;
            return $c122;
        }, 'c122-safety-'.$field);

        $this->assertSame('C123_BLOCKED_C122_SAFETY_FLAG_ALREADY_LIVE_OR_MUTATING', $result['status'], $field);
        $this->assertSame($field, $result['c122_live_or_mutating_safety_flag_failure']);
    }

    public function safetyFlagProvider(): array
    {
        return array_map(static function (string $flag): array {
            return [$flag];
        }, $this->requiredSafetyFlags());
    }

    public function test_c123_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c123-no-temporary-artifact-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame('C123_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_FINALIZATION_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS', $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c123_records_artifact_hash_locks_next_and_required_sections(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $this->assertSame(self::C122_HASH, $result['expected_c122_hash']);
        $this->assertSame(self::C122_HASH, $result['actual_c122_hash']);
        $this->assertTrue($result['c122_hash_match']);
        $this->assertSame(self::C122_SHA1, $result['expected_c122_file_sha1']);
        $this->assertSame(self::C122_SHA1, $result['actual_c122_file_sha1']);
        $this->assertTrue($result['c122_file_sha1_match']);
        $this->assertSame(self::NEXT_C124, $result['next_readiness_decision']['next_recommendation']);
        $this->assertSame(2, $result['next_readiness_decision']['candidate_ready_for_weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_review_count']);

        foreach ([
            'source_artifact_locks',
            'c122_lock_validation_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'c123_handoff_finalization_decision',
            'next_readiness_decision',
            'next_handoff_completion_boundary_decision',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_manifest',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_checklist',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_candidate_scorecard',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_context_summary',
            'c122_handoff_readiness_carry_forward_validation_summary',
            'handoff_finalization_governance_summary',
            'production_mutation_safety_summary',
            'planned_next_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }
    }

    public function test_c123_keeps_e02_primary_b01_backup_a01_comparator_and_no_live_output(): void
    {
        $result = $this->runService();
        $manifest = $result['weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_manifest'];

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $result['primary_candidate_code']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $result['backup_candidate_code']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $result['comparator_candidate_code']);
        $this->assertFalse($result['candidate_scope_freeze_summary']['a01_promoted']);
        $this->assertTrue($manifest['handoff_finalization_artifact_only']);
        $this->assertTrue($manifest['handoff_finalized']);
        $this->assertFalse($manifest['handoff_finalization_used_for_plan_confirm_mutation']);
        $this->assertFalse($manifest['handoff_finalization_used_for_live_rollout']);
        $this->assertSame([], $manifest['official_weekly_swing_stock_recommendations']);
        $this->assertFalse($result['weekly_swing_watchlist_official_output_generated']);
        $this->assertFalse($result['weekly_swing_watchlist_live_recommendation_generated']);
    }

    public function test_c123_keeps_all_safety_flags_false(): void
    {
        $result = $this->runService();

        foreach ($this->requiredSafetyFlags() as $flag) {
            $this->assertFalse($result[$flag], $flag);
            $this->assertFalse($result['production_mutation_safety_summary'][$flag], $flag);
            $this->assertFalse($result['c123_handoff_finalization_decision'][$flag], $flag);
        }
    }

    public function test_c123_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $first = $this->execute(['options' => ['created_at' => '2026-06-30T00:00:00+00:00']]);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c123-handoff-finalization-pass-second.json';
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
        $service = new WatchlistBacktestC123WeeklySwingWatchlistControlledRuntimeWiringHandoffFinalizationReviewService();
        $fixture = $this->lockedC122Fixture();
        return $service->execute(
            (string) ($overrides['c122Artifact'] ?? $fixture['path']),
            (string) ($overrides['expectedC122Hash'] ?? $fixture['hash']),
            (string) ($overrides['expectedC122FileSha1'] ?? $fixture['sha1']),
            (string) ($overrides['output'] ?? $this->output),
            array_merge([
                'overwrite' => true,
                'operator_approved' => $overrides['operatorApproved'] ?? true,
                'handoff_finalization_confirmed' => $overrides['handoffFinalizationConfirmed'] ?? true,
                'approval_reference' => $overrides['approvalReference'] ?? 'C123_OPERATOR_APPROVED_CONTROLLED_RUNTIME_WIRING_HANDOFF_FINALIZATION_REVIEW_ONLY',
            ], (array) ($overrides['options'] ?? []))
        );
    }

    private function mutateC122AndExecute(callable $mutator, string $name): array
    {
        $fixture = $this->lockedC122Fixture();
        $payload = json_decode((string) file_get_contents($fixture['path']), true);
        $payload = $mutator(is_array($payload) ? $payload : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c123-source-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
        return $this->execute([
            'c122Artifact' => $path,
            'expectedC122Hash' => (string) ($payload['artifact_hash'] ?? ''),
            'expectedC122FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function lockedC122Fixture(): array
    {
        $path = 'storage/app/watchlist/backtest/c122-weekly-swing-watchlist-controlled-runtime-wiring-handoff-readiness-review.json';
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

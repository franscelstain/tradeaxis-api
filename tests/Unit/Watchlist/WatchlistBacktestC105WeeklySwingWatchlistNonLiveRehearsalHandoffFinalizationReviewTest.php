<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC105WeeklySwingWatchlistNonLiveRehearsalHandoffFinalizationReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC105WeeklySwingWatchlistNonLiveRehearsalHandoffFinalizationReviewTest extends TestCase
{
    private const PASS_STATUS = 'C105_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_FINALIZATION_REVIEW_PASSED_HANDOFF_FINALIZED_PRIMARY_AND_BACKUP';
    private const NEXT_C106 = 'C106_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_COMPLETION_BOUNDARY_REVIEW';
    private const C104_HASH = '9949422cda0ff224c7b441cdd0dd02bfb6c694a4';
    private const C104_SHA1 = '08F7A41BDB04E4B40562C855230FDC170E8A2335';

    private string $output;

    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c105-handoff-finalization-pass.json';
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

    public function test_c105_passes_with_valid_c104_handoff_ready_lock_operator_approval_and_reference(): void
    {
        $result = $this->runService();

        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame($result['status'], $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_non_live_rehearsal_handoff_finalization_review_executed']);
        $this->assertTrue($result['weekly_swing_watchlist_non_live_rehearsal_handoff_finalization_review_allowed']);
        $this->assertTrue($result['weekly_swing_watchlist_non_live_rehearsal_handoff_finalization_review_pass']);
        $this->assertTrue($result['weekly_swing_watchlist_non_live_rehearsal_handoff_finalized']);
        $this->assertTrue($result['handoff_finalized']);
        $this->assertTrue($result['handoff_ready']);
        $this->assertTrue($result['c104_handoff_ready']);
        $this->assertTrue($result['primary_candidate_weekly_swing_non_live_rehearsal_handoff_finalized']);
        $this->assertTrue($result['backup_candidate_weekly_swing_non_live_rehearsal_handoff_finalized']);
        $this->assertFalse($result['comparator_candidate_weekly_swing_non_live_rehearsal_handoff_finalized']);
        $this->assertTrue($result['a01_remains_comparator_only']);
        $this->assertSame(self::NEXT_C106, $result['next_step_recommendation']);
        $this->assertSame('READY_FOR_C106_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_COMPLETION_BOUNDARY_REVIEW', $result['diagnostic_conclusion']);
        $this->assertFileExists($this->output);
    }

    public function test_c105_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->execute(['operatorApproved' => false]);
        $missingReference = $this->execute(['approvalReference' => '']);

        $this->assertSame('C105_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingOperator['status']);
        $this->assertSame('C105_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingReference['status']);
    }

    public function test_c105_rejects_unconfirmed_handoff_finalization(): void
    {
        $result = $this->execute(['options' => ['handoff_finalization_confirmed' => false]]);

        $this->assertSame('C105_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_FINALIZATION_REVIEW_REJECTED_HANDOFF_FINALIZATION_NOT_CONFIRMED', $result['status']);
        $this->assertFalse($result['handoff_finalized']);
    }

    public function test_c105_rejects_missing_or_mismatched_c104_artifact_lock(): void
    {
        $missing = $this->execute([
            'c104Artifact' => 'storage/app/watchlist/backtest/missing-c104-for-c105.json',
            'expectedC104Hash' => 'missing',
            'expectedC104FileSha1' => 'missing',
        ]);
        $hashMismatch = $this->execute([
            'expectedC104Hash' => 'bad-hash',
        ]);
        $shaMismatch = $this->execute([
            'expectedC104FileSha1' => 'BADSHA1',
        ]);

        $this->assertSame('C105_BLOCKED_C104_ARTIFACT_LOCK_MISMATCH', $missing['status']);
        $this->assertSame('C105_BLOCKED_C104_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C105_BLOCKED_C104_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
    }

    public function test_c105_rejects_c104_status_reason_or_next_recommendation_mismatch(): void
    {
        $status = $this->mutateC104AndExecute(function (array $c104): array {
            $c104['status'] = 'BROKEN_STATUS';
            return $c104;
        }, 'c104-status-broken');
        $reason = $this->mutateC104AndExecute(function (array $c104): array {
            $c104['reason_code'] = 'BROKEN_REASON';
            return $c104;
        }, 'c104-reason-broken');
        $next = $this->mutateC104AndExecute(function (array $c104): array {
            $c104['next_step_recommendation'] = 'BROKEN_NEXT';
            $c104['next_readiness_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c104['c104_handoff_readiness_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c104['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $c104;
        }, 'c104-next-broken');

        $this->assertSame('C105_BLOCKED_C104_STATUS_OR_REASON_MISMATCH', $status['status']);
        $this->assertSame('C105_BLOCKED_C104_STATUS_OR_REASON_MISMATCH', $reason['status']);
        $this->assertSame('C105_BLOCKED_C104_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
    }

    /**
     * @dataProvider c104HandoffReadyStateMismatchProvider
     */
    public function test_c105_rejects_c104_handoff_ready_state_mismatch(string $field, $value): void
    {
        $result = $this->mutateC104AndExecute(function (array $c104) use ($field, $value): array {
            $c104[$field] = $value;
            $c104['c104_handoff_readiness_decision'][$field] = $value;
            $c104['next_readiness_decision'][$field] = $value;
            return $c104;
        }, 'c104-state-'.$field);

        $this->assertSame('C105_BLOCKED_C104_HANDOFF_NOT_READY', $result['status'], $field);
    }

    public function c104HandoffReadyStateMismatchProvider(): array
    {
        return [
            ['weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_review_pass', false],
            ['weekly_swing_watchlist_non_live_rehearsal_handoff_ready', false],
            ['handoff_ready', false],
            ['completion_boundary_cleared', false],
            ['boundary_go_decision', 'NO_GO'],
            ['operator_go_decision', 'NO_GO'],
            ['go_decision_finalized', false],
            ['primary_candidate_weekly_swing_non_live_rehearsal_handoff_ready', false],
            ['backup_candidate_weekly_swing_non_live_rehearsal_handoff_ready', false],
            ['comparator_candidate_weekly_swing_non_live_rehearsal_handoff_ready', true],
        ];
    }

    public function test_c105_rejects_c104_c105_readiness_count_mismatch(): void
    {
        $result = $this->mutateC104AndExecute(function (array $c104): array {
            $c104['next_readiness_decision']['candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_handoff_finalization_review_count'] = 1;
            return $c104;
        }, 'c104-readiness-count-broken');

        $this->assertSame('C105_BLOCKED_C104_HANDOFF_NOT_READY', $result['status']);
    }

    public function test_c105_rejects_c104_manifest_not_artifact_only_or_used_for_live_output(): void
    {
        $notArtifactOnly = $this->mutateC104AndExecute(function (array $c104): array {
            $c104['weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_manifest']['handoff_readiness_artifact_only'] = false;
            return $c104;
        }, 'c104-manifest-not-artifact-only');
        $usedForPlanConfirm = $this->mutateC104AndExecute(function (array $c104): array {
            $c104['weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_manifest']['handoff_readiness_used_for_plan_confirm_mutation'] = true;
            return $c104;
        }, 'c104-manifest-plan-confirm');
        $officialOutput = $this->mutateC104AndExecute(function (array $c104): array {
            $c104['weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_manifest']['official_weekly_swing_stock_recommendations'] = ['SHOULD_NOT_EXIST'];
            return $c104;
        }, 'c104-manifest-official-output');

        $this->assertSame('C105_BLOCKED_C104_HANDOFF_NOT_READY', $notArtifactOnly['status']);
        $this->assertSame('C105_BLOCKED_C104_HANDOFF_NOT_READY', $usedForPlanConfirm['status']);
        $this->assertSame('C105_BLOCKED_C104_HANDOFF_NOT_READY', $officialOutput['status']);
    }

    /**
     * @dataProvider safetyFlagProvider
     */
    public function test_c105_rejects_any_live_or_mutating_safety_flag_true_in_c104(string $field): void
    {
        $result = $this->mutateC104AndExecute(function (array $c104) use ($field): array {
            $c104[$field] = true;
            return $c104;
        }, 'c104-safety-'.$field);

        $this->assertSame('C105_BLOCKED_C104_SAFETY_FLAG_ALREADY_LIVE_OR_MUTATING', $result['status'], $field);
        $this->assertSame($field, $result['c104_live_or_mutating_safety_flag_failure']);
    }

    public function safetyFlagProvider(): array
    {
        return array_map(static function (string $flag): array {
            return [$flag];
        }, $this->requiredSafetyFlags());
    }

    public function test_c105_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c105-no-temporary-artifact-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame('C105_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_FINALIZATION_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS', $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c105_records_artifact_hash_locks_next_and_required_sections(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $this->assertSame(self::C104_HASH, $result['expected_c104_hash']);
        $this->assertSame(self::C104_HASH, $result['actual_c104_hash']);
        $this->assertTrue($result['c104_hash_match']);
        $this->assertSame(self::C104_SHA1, $result['expected_c104_file_sha1']);
        $this->assertSame(self::C104_SHA1, $result['actual_c104_file_sha1']);
        $this->assertTrue($result['c104_file_sha1_match']);
        $this->assertSame(self::NEXT_C106, $result['next_readiness_decision']['next_recommendation']);
        $this->assertSame(2, $result['next_readiness_decision']['candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_handoff_completion_boundary_review_count']);

        foreach ([
            'source_artifact_locks',
            'c104_lock_validation_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'c105_handoff_finalization_decision',
            'next_readiness_decision',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_finalization_manifest',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_finalization_candidate_scorecard',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_finalization_context_summary',
            'c104_handoff_readiness_carry_forward_validation_summary',
            'handoff_finalization_governance_summary',
            'production_mutation_safety_summary',
            'planned_next_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }
    }

    public function test_c105_keeps_e02_primary_b01_backup_a01_comparator_and_no_live_output(): void
    {
        $result = $this->runService();
        $manifest = $result['weekly_swing_watchlist_non_live_rehearsal_handoff_finalization_manifest'];

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

    public function test_c105_keeps_all_safety_flags_false(): void
    {
        $result = $this->runService();

        foreach ($this->requiredSafetyFlags() as $flag) {
            $this->assertFalse($result[$flag], $flag);
            $this->assertFalse($result['production_mutation_safety_summary'][$flag], $flag);
            $this->assertFalse($result['c105_handoff_finalization_decision'][$flag], $flag);
        }
    }

    public function test_c105_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $first = $this->execute(['options' => ['created_at' => '2026-06-30T00:00:00+00:00']]);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c105-handoff-finalization-pass-second.json';
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
        $service = new WatchlistBacktestC105WeeklySwingWatchlistNonLiveRehearsalHandoffFinalizationReviewService();
        $fixture = $this->lockedC104Fixture();
        return $service->execute(
            (string) ($overrides['c104Artifact'] ?? $fixture['path']),
            (string) ($overrides['expectedC104Hash'] ?? $fixture['hash']),
            (string) ($overrides['expectedC104FileSha1'] ?? $fixture['sha1']),
            (string) ($overrides['output'] ?? $this->output),
            array_merge([
                'overwrite' => true,
                'operator_approved' => $overrides['operatorApproved'] ?? true,
                'approval_reference' => $overrides['approvalReference'] ?? 'C105_OPERATOR_APPROVED_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_FINALIZATION_REVIEW_ONLY',
            ], (array) ($overrides['options'] ?? []))
        );
    }

    private function mutateC104AndExecute(callable $mutator, string $name): array
    {
        $fixture = $this->lockedC104Fixture();
        $payload = json_decode((string) file_get_contents($fixture['path']), true);
        $payload = $mutator(is_array($payload) ? $payload : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c105-source-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
        return $this->execute([
            'c104Artifact' => $path,
            'expectedC104Hash' => (string) ($payload['artifact_hash'] ?? ''),
            'expectedC104FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function lockedC104Fixture(): array
    {
        $path = 'storage/app/watchlist/backtest/c104-weekly-swing-watchlist-non-live-rehearsal-handoff-readiness-review.json';
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

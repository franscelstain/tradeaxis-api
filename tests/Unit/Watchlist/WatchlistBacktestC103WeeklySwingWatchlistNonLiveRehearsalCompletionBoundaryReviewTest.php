<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC103WeeklySwingWatchlistNonLiveRehearsalCompletionBoundaryReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC103WeeklySwingWatchlistNonLiveRehearsalCompletionBoundaryReviewTest extends TestCase
{
    private const PASS_STATUS = 'C103_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_COMPLETION_BOUNDARY_REVIEW_PASSED_BOUNDARY_CLEARED_PRIMARY_AND_BACKUP';
    private const NEXT_C104 = 'C104_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_READINESS_REVIEW';
    private const C102_HASH = 'e9e246048d14dcedda262a35fce9d52b64b052c0';
    private const C102_SHA1 = 'DD731AFB11D2EA513EEF6795BF03D2F404670FB6';

    private string $output;

    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c103-completion-boundary-pass.json';
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

    public function test_c103_passes_with_valid_c102_finalized_go_lock_operator_approval_and_reference(): void
    {
        $result = $this->runService();

        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame($result['status'], $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_non_live_rehearsal_completion_boundary_review_executed']);
        $this->assertTrue($result['weekly_swing_watchlist_non_live_rehearsal_completion_boundary_review_allowed']);
        $this->assertTrue($result['weekly_swing_watchlist_non_live_rehearsal_completion_boundary_review_pass']);
        $this->assertTrue($result['weekly_swing_watchlist_non_live_rehearsal_completion_boundary_cleared']);
        $this->assertTrue($result['completion_boundary_cleared']);
        $this->assertSame('BOUNDARY_CLEARED_GO', $result['boundary_go_decision']);
        $this->assertSame('GO', $result['operator_go_decision']);
        $this->assertTrue($result['go_decision_finalized']);
        $this->assertTrue($result['c102_go_decision_finalized']);
        $this->assertTrue($result['primary_candidate_weekly_swing_non_live_rehearsal_completion_boundary_cleared']);
        $this->assertTrue($result['backup_candidate_weekly_swing_non_live_rehearsal_completion_boundary_cleared']);
        $this->assertFalse($result['comparator_candidate_weekly_swing_non_live_rehearsal_completion_boundary_cleared']);
        $this->assertTrue($result['a01_remains_comparator_only']);
        $this->assertSame(self::NEXT_C104, $result['next_step_recommendation']);
        $this->assertSame('READY_FOR_C104_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_READINESS_REVIEW', $result['diagnostic_conclusion']);
        $this->assertFileExists($this->output);
    }

    public function test_c103_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->execute(['operatorApproved' => false]);
        $missingReference = $this->execute(['approvalReference' => '']);

        $this->assertSame('C103_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_COMPLETION_BOUNDARY_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingOperator['status']);
        $this->assertSame('C103_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_COMPLETION_BOUNDARY_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingReference['status']);
    }

    public function test_c103_rejects_unconfirmed_completion_boundary(): void
    {
        $result = $this->execute(['options' => ['completion_boundary_confirmed' => false]]);

        $this->assertSame('C103_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_COMPLETION_BOUNDARY_REVIEW_REJECTED_COMPLETION_BOUNDARY_NOT_CONFIRMED', $result['status']);
        $this->assertFalse($result['completion_boundary_cleared']);
    }

    public function test_c103_rejects_missing_c102_artifact(): void
    {
        $result = $this->execute([
            'c102Artifact' => 'storage/app/watchlist/backtest/missing-c102-for-c103.json',
            'expectedC102Hash' => 'missing',
            'expectedC102FileSha1' => 'missing',
        ]);

        $this->assertSame('C103_BLOCKED_C102_ARTIFACT_LOCK_MISMATCH', $result['status']);
    }

    public function test_c103_rejects_expected_c102_artifact_hash_mismatch(): void
    {
        $fixture = $this->lockedC102Fixture();
        $result = $this->execute([
            'c102Artifact' => $fixture['path'],
            'expectedC102Hash' => 'bad-hash',
            'expectedC102FileSha1' => $fixture['sha1'],
        ]);

        $this->assertSame('C103_BLOCKED_C102_ARTIFACT_LOCK_MISMATCH', $result['status']);
        $this->assertFalse($result['c102_hash_match']);
    }

    public function test_c103_rejects_expected_c102_file_sha1_mismatch(): void
    {
        $fixture = $this->lockedC102Fixture();
        $result = $this->execute([
            'c102Artifact' => $fixture['path'],
            'expectedC102Hash' => $fixture['hash'],
            'expectedC102FileSha1' => 'BADSHA1',
        ]);

        $this->assertSame('C103_BLOCKED_C102_FILE_SHA1_LOCK_MISMATCH', $result['status']);
        $this->assertFalse($result['c102_file_sha1_match']);
    }

    public function test_c103_rejects_c102_status_or_reason_mismatch(): void
    {
        $status = $this->mutateC102AndExecute(function (array $c102): array {
            $c102['status'] = 'BROKEN_STATUS';
            return $c102;
        }, 'c102-status-broken');

        $reason = $this->mutateC102AndExecute(function (array $c102): array {
            $c102['reason_code'] = 'BROKEN_REASON';
            return $c102;
        }, 'c102-reason-broken');

        $this->assertSame('C103_BLOCKED_C102_STATUS_OR_REASON_MISMATCH', $status['status']);
        $this->assertSame('C103_BLOCKED_C102_STATUS_OR_REASON_MISMATCH', $reason['status']);
    }

    public function test_c103_rejects_c102_next_recommendation_not_c103(): void
    {
        $result = $this->mutateC102AndExecute(function (array $c102): array {
            $c102['next_step_recommendation'] = 'BROKEN_NEXT';
            $c102['next_readiness_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c102['c102_go_decision_finalization_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c102['weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c102['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $c102;
        }, 'c102-next-broken');

        $this->assertSame('C103_BLOCKED_C102_NEXT_RECOMMENDATION_MISMATCH', $result['status']);
    }

    /**
     * @dataProvider c102FinalizedGoStateMismatchProvider
     */
    public function test_c103_rejects_c102_finalized_go_state_mismatch(string $field, $value): void
    {
        $result = $this->mutateC102AndExecute(function (array $c102) use ($field, $value): array {
            $c102[$field] = $value;
            $c102['c102_go_decision_finalization_decision'][$field] = $value;
            $c102['weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_decision'][$field] = $value;
            return $c102;
        }, 'c102-state-'.$field);

        $this->assertSame('C103_BLOCKED_C102_GO_DECISION_FINALIZATION_STATE_NOT_COMPLETE', $result['status'], $field);
    }

    public function c102FinalizedGoStateMismatchProvider(): array
    {
        return [
            ['weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_review_pass', false],
            ['operator_go_decision_confirmed', false],
            ['operator_go_decision', 'NO_GO'],
            ['go_decision_finalized', false],
            ['go_decision_finalization_confirmed', false],
            ['primary_candidate_weekly_swing_non_live_rehearsal_go_decision_finalized', false],
            ['backup_candidate_weekly_swing_non_live_rehearsal_go_decision_finalized', false],
            ['comparator_candidate_weekly_swing_non_live_rehearsal_go_decision_finalized', true],
        ];
    }

    public function test_c103_rejects_c102_c103_readiness_count_mismatch(): void
    {
        $result = $this->mutateC102AndExecute(function (array $c102): array {
            $c102['next_readiness_decision']['candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_completion_boundary_review_count'] = 1;
            return $c102;
        }, 'c102-readiness-count-broken');

        $this->assertSame('C103_BLOCKED_C102_GO_DECISION_FINALIZATION_STATE_NOT_COMPLETE', $result['status']);
    }

    public function test_c103_rejects_c102_finalization_manifest_not_artifact_only_or_used_for_live_output(): void
    {
        $notArtifactOnly = $this->mutateC102AndExecute(function (array $c102): array {
            $c102['weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_manifest']['go_decision_finalization_artifact_only'] = false;
            return $c102;
        }, 'c102-manifest-not-artifact-only');

        $usedForPlanConfirm = $this->mutateC102AndExecute(function (array $c102): array {
            $c102['weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_manifest']['go_decision_finalization_used_for_plan_confirm_mutation'] = true;
            return $c102;
        }, 'c102-manifest-plan-confirm');

        $officialOutput = $this->mutateC102AndExecute(function (array $c102): array {
            $c102['weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_manifest']['official_weekly_swing_stock_recommendations'] = ['SHOULD_NOT_EXIST'];
            return $c102;
        }, 'c102-manifest-official-output');

        $this->assertSame('C103_BLOCKED_C102_GO_DECISION_FINALIZATION_STATE_NOT_COMPLETE', $notArtifactOnly['status']);
        $this->assertSame('C103_BLOCKED_C102_GO_DECISION_FINALIZATION_STATE_NOT_COMPLETE', $usedForPlanConfirm['status']);
        $this->assertSame('C103_BLOCKED_C102_GO_DECISION_FINALIZATION_STATE_NOT_COMPLETE', $officialOutput['status']);
    }

    public function test_c103_rejects_a01_comparator_only_violation(): void
    {
        $result = $this->mutateC102AndExecute(function (array $c102): array {
            $c102['a01_remains_comparator_only'] = false;
            $c102['c102_go_decision_finalization_decision']['a01_remains_comparator_only'] = false;
            return $c102;
        }, 'c102-a01-promoted');

        $this->assertSame('C103_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_COMPLETION_BOUNDARY_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $result['status']);
    }

    /**
     * @dataProvider safetyFlagProvider
     */
    public function test_c103_rejects_any_live_or_mutating_safety_flag_true_in_c102(string $field): void
    {
        $result = $this->mutateC102AndExecute(function (array $c102) use ($field): array {
            $c102[$field] = true;
            return $c102;
        }, 'c102-safety-'.$field);

        $this->assertSame('C103_BLOCKED_C102_SAFETY_FLAG_ALREADY_LIVE_OR_MUTATING', $result['status'], $field);
        $this->assertSame($field, $result['c102_live_or_mutating_safety_flag_failure']);
    }

    public function safetyFlagProvider(): array
    {
        return array_map(static function (string $flag): array {
            return [$flag];
        }, $this->requiredSafetyFlags());
    }

    public function test_c103_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c103-no-temporary-artifact-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame('C103_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_COMPLETION_BOUNDARY_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS', $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c103_keeps_e02_primary_b01_backup_and_a01_comparator_only(): void
    {
        $result = $this->runService();

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $result['primary_candidate_code']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $result['backup_candidate_code']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $result['comparator_candidate_code']);
        $this->assertTrue($result['c103_completion_boundary_decision']['primary_candidate_weekly_swing_non_live_rehearsal_completion_boundary_cleared']);
        $this->assertTrue($result['c103_completion_boundary_decision']['backup_candidate_weekly_swing_non_live_rehearsal_completion_boundary_cleared']);
        $this->assertFalse($result['c103_completion_boundary_decision']['comparator_candidate_weekly_swing_non_live_rehearsal_completion_boundary_cleared']);
        $this->assertFalse($result['candidate_scope_freeze_summary']['a01_promoted']);
    }

    public function test_c103_writes_artifact_hash_and_c102_source_lock_top_level_aliases(): void
    {
        $result = $this->runService();

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $this->assertSame(self::C102_HASH, $result['expected_c102_hash']);
        $this->assertSame(self::C102_HASH, $result['actual_c102_hash']);
        $this->assertTrue($result['c102_hash_match']);
        $this->assertSame(self::C102_SHA1, $result['expected_c102_file_sha1']);
        $this->assertSame(self::C102_SHA1, $result['actual_c102_file_sha1']);
        $this->assertTrue($result['c102_file_sha1_match']);
    }

    public function test_c103_writes_next_recommendation_c104_handoff_readiness(): void
    {
        $result = $this->runService();

        $this->assertSame(self::NEXT_C104, $result['next_step_recommendation']);
        $this->assertSame(self::NEXT_C104, $result['next_readiness_decision']['next_recommendation']);
        $this->assertSame(self::NEXT_C104, $result['planned_next_summary']['planned_next_review']);
        $this->assertSame(2, $result['next_readiness_decision']['candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_review_count']);
    }

    public function test_c103_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $first = $this->execute(['options' => ['created_at' => '2026-06-30T00:00:00+00:00']]);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c103-completion-boundary-pass-second.json';
        $this->tmpFiles[] = $secondOutput;
        $second = $this->execute([
            'output' => $secondOutput,
            'options' => ['created_at' => '2026-06-30T00:00:00+00:00'],
        ]);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
    }

    public function test_c103_records_required_sections_and_safety_flags(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();

        foreach ([
            'source_artifact_locks',
            'c102_lock_validation_summary',
            'lineage_validation_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'c103_completion_boundary_decision',
            'next_readiness_decision',
            'weekly_swing_watchlist_non_live_rehearsal_completion_boundary_decision',
            'weekly_swing_watchlist_non_live_rehearsal_completion_boundary_manifest',
            'weekly_swing_watchlist_non_live_rehearsal_completion_boundary_candidate_scorecard',
            'weekly_swing_watchlist_non_live_rehearsal_completion_boundary_context_summary',
            'runtime_readiness_inspection_summary',
            'feature_flag_operator_approval_kill_switch_validation_summary',
            'c102_go_decision_finalization_carry_forward_validation_summary',
            'completion_boundary_governance_summary',
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
            $this->assertFalse($result['c103_completion_boundary_decision'][$flag], $flag);
        }
    }

    public function test_c103_does_not_create_weekly_swing_live_output_or_official_recommendation(): void
    {
        $result = $this->runService();

        $this->assertFalse($result['weekly_swing_watchlist_official_output_generated']);
        $this->assertFalse($result['weekly_swing_watchlist_official_output_published']);
        $this->assertFalse($result['weekly_swing_watchlist_live_recommendation_generated']);
        $this->assertSame([], $result['weekly_swing_watchlist_non_live_rehearsal_completion_boundary_manifest']['official_weekly_swing_stock_recommendations']);
    }

    public function test_c103_writes_artifact_only_completion_boundary_manifest_not_used_for_plan_confirm_mutation(): void
    {
        $result = $this->runService();
        $manifest = $result['weekly_swing_watchlist_non_live_rehearsal_completion_boundary_manifest'];

        $this->assertTrue($manifest['manifest_created']);
        $this->assertSame('artifact_only_non_live_rehearsal_completion_boundary_review', $manifest['manifest_context']);
        $this->assertSame('non_live_artifact_only_rehearsal_completion_boundary', $manifest['execution_mode']);
        $this->assertTrue($manifest['completion_boundary_artifact_only']);
        $this->assertSame('BOUNDARY_CLEARED_GO', $manifest['boundary_go_decision']);
        $this->assertTrue($manifest['completion_boundary_cleared']);
        $this->assertFalse($manifest['completion_boundary_used_for_selection']);
        $this->assertFalse($manifest['completion_boundary_used_for_retuning']);
        $this->assertFalse($manifest['completion_boundary_used_for_ranking']);
        $this->assertFalse($manifest['completion_boundary_used_for_plan_confirm_mutation']);
        $this->assertFalse($manifest['completion_boundary_used_for_live_rollout']);
        $this->assertFalse($manifest['plan_confirm_mutation_allowed']);
    }

    public function test_c103_candidate_scorecard_locks_primary_backup_boundary_cleared_and_a01_comparator_only(): void
    {
        $result = $this->runService();
        $scorecards = $result['weekly_swing_watchlist_non_live_rehearsal_completion_boundary_candidate_scorecard'];

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $scorecards[0]['candidate_code']);
        $this->assertSame('primary_non_live_rehearsal_completion_boundary_cleared_candidate', $scorecards[0]['c103_role']);
        $this->assertTrue($scorecards[0]['primary_candidate_weekly_swing_non_live_rehearsal_completion_boundary_cleared']);
        $this->assertTrue($scorecards[0]['candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_review']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $scorecards[1]['candidate_code']);
        $this->assertSame('backup_non_live_rehearsal_completion_boundary_cleared_candidate', $scorecards[1]['c103_role']);
        $this->assertTrue($scorecards[1]['backup_candidate_weekly_swing_non_live_rehearsal_completion_boundary_cleared']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $scorecards[2]['candidate_code']);
        $this->assertSame('comparator_only_candidate', $scorecards[2]['c103_role']);
        $this->assertFalse($scorecards[2]['comparator_candidate_weekly_swing_non_live_rehearsal_completion_boundary_cleared']);
        $this->assertFalse($scorecards[2]['candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_review']);
    }

    public function test_c103_does_not_mutate_c60_through_c102_artifacts(): void
    {
        $before = [];
        foreach (glob('storage/app/watchlist/backtest/c*.json') as $path) {
            if (preg_match('/storage\/app\/watchlist\/backtest\/c(6[0-9]|7[0-9]|8[0-9]|9[0-9]|10[0-2])-/', str_replace('\\', '/', $path)) === 1) {
                $before[$path] = strtoupper(sha1((string) file_get_contents($path)));
            }
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
            'weekly_swing_watchlist_rehearsal_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_non_live_rehearsal_execution_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_non_live_rehearsal_result_review_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_context_persisted_to_live_runtime',
            'operator_go_no_go_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_context_persisted_to_live_runtime',
            'go_decision_finalization_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_non_live_rehearsal_completion_boundary_context_persisted_to_live_runtime',
            'completion_boundary_context_persisted_to_live_runtime',
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
        $service = new WatchlistBacktestC103WeeklySwingWatchlistNonLiveRehearsalCompletionBoundaryReviewService();
        $fixture = $this->lockedC102Fixture();
        return $service->execute(
            (string) ($overrides['c102Artifact'] ?? $fixture['path']),
            (string) ($overrides['expectedC102Hash'] ?? $fixture['hash']),
            (string) ($overrides['expectedC102FileSha1'] ?? $fixture['sha1']),
            (string) ($overrides['output'] ?? $this->output),
            array_merge([
                'overwrite' => true,
                'operator_approved' => $overrides['operatorApproved'] ?? true,
                'approval_reference' => $overrides['approvalReference'] ?? 'C103_OPERATOR_APPROVED_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_COMPLETION_BOUNDARY_REVIEW_ONLY',
            ], (array) ($overrides['options'] ?? []))
        );
    }

    private function mutateC102AndExecute(callable $mutator, string $name): array
    {
        $fixture = $this->lockedC102Fixture();
        $payload = json_decode((string) file_get_contents($fixture['path']), true);
        $payload = $mutator(is_array($payload) ? $payload : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c103-source-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
        return $this->execute([
            'c102Artifact' => $path,
            'expectedC102Hash' => (string) ($payload['artifact_hash'] ?? ''),
            'expectedC102FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function lockedC102Fixture(): array
    {
        $path = 'storage/app/watchlist/backtest/c102-weekly-swing-watchlist-non-live-rehearsal-go-decision-finalization-review.json';
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

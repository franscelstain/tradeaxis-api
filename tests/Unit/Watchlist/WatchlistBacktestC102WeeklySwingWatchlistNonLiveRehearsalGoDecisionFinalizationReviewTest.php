<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC102WeeklySwingWatchlistNonLiveRehearsalGoDecisionFinalizationReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC102WeeklySwingWatchlistNonLiveRehearsalGoDecisionFinalizationReviewTest extends TestCase
{
    private const PASS_STATUS = 'C102_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_GO_DECISION_FINALIZATION_REVIEW_PASSED_FINALIZED_GO_PRIMARY_AND_BACKUP';
    private const NEXT_C103 = 'C103_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_COMPLETION_BOUNDARY_REVIEW';
    private const C101_HASH = 'f8a339760d94d230e184dc6f6b3016731ba72379';
    private const C101_SHA1 = 'B12CF95D02172659B51B215E567D0B31C6F891F7';

    private string $output;

    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c102-go-finalization-pass.json';
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

    public function test_c102_passes_with_valid_c101_operator_go_lock_operator_approval_and_reference(): void
    {
        $result = $this->runService();

        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame($result['status'], $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_review_executed']);
        $this->assertTrue($result['weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_review_allowed']);
        $this->assertTrue($result['weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_review_pass']);
        $this->assertSame('GO', $result['operator_go_decision']);
        $this->assertTrue($result['operator_go_decision_confirmed']);
        $this->assertTrue($result['go_decision_finalized']);
        $this->assertTrue($result['go_decision_finalization_confirmed']);
        $this->assertTrue($result['primary_candidate_weekly_swing_non_live_rehearsal_go_decision_finalized']);
        $this->assertTrue($result['backup_candidate_weekly_swing_non_live_rehearsal_go_decision_finalized']);
        $this->assertFalse($result['comparator_candidate_weekly_swing_non_live_rehearsal_go_decision_finalized']);
        $this->assertTrue($result['a01_remains_comparator_only']);
        $this->assertTrue($result['c101_operator_go_no_go_passed']);
        $this->assertSame(self::NEXT_C103, $result['next_step_recommendation']);
        $this->assertSame('READY_FOR_C103_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_COMPLETION_BOUNDARY_REVIEW', $result['diagnostic_conclusion']);
        $this->assertFileExists($this->output);
    }

    public function test_c102_rejects_missing_operator_approval(): void
    {
        $result = $this->execute(['operatorApproved' => false]);

        $this->assertSame('C102_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_GO_DECISION_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $result['status']);
        $this->assertSame($result['status'], $result['reason_code']);
    }

    public function test_c102_rejects_missing_approval_reference(): void
    {
        $result = $this->execute(['approvalReference' => '']);

        $this->assertSame('C102_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_GO_DECISION_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $result['status']);
        $this->assertSame($result['status'], $result['reason_code']);
    }

    public function test_c102_rejects_unconfirmed_go_decision_finalization(): void
    {
        $result = $this->execute(['options' => ['go_decision_finalization_confirmed' => false]]);

        $this->assertSame('C102_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_GO_DECISION_FINALIZATION_REVIEW_REJECTED_GO_DECISION_FINALIZATION_NOT_CONFIRMED', $result['status']);
        $this->assertSame('NO_GO', $result['operator_go_decision']);
        $this->assertFalse($result['go_decision_finalized']);
    }

    public function test_c102_rejects_missing_c101_artifact(): void
    {
        $result = $this->execute([
            'c101Artifact' => 'storage/app/watchlist/backtest/missing-c101-for-c102.json',
            'expectedC101Hash' => 'missing',
            'expectedC101FileSha1' => 'missing',
        ]);

        $this->assertSame('C102_BLOCKED_C101_ARTIFACT_LOCK_MISMATCH', $result['status']);
    }

    public function test_c102_rejects_expected_c101_artifact_hash_mismatch(): void
    {
        $fixture = $this->lockedC101Fixture();
        $result = $this->execute([
            'c101Artifact' => $fixture['path'],
            'expectedC101Hash' => 'bad-hash',
            'expectedC101FileSha1' => $fixture['sha1'],
        ]);

        $this->assertSame('C102_BLOCKED_C101_ARTIFACT_LOCK_MISMATCH', $result['status']);
        $this->assertFalse($result['c101_hash_match']);
    }

    public function test_c102_rejects_expected_c101_file_sha1_mismatch(): void
    {
        $fixture = $this->lockedC101Fixture();
        $result = $this->execute([
            'c101Artifact' => $fixture['path'],
            'expectedC101Hash' => $fixture['hash'],
            'expectedC101FileSha1' => 'BADSHA1',
        ]);

        $this->assertSame('C102_BLOCKED_C101_FILE_SHA1_LOCK_MISMATCH', $result['status']);
        $this->assertFalse($result['c101_file_sha1_match']);
    }

    public function test_c102_rejects_c101_status_or_reason_mismatch(): void
    {
        $statusResult = $this->mutateC101AndExecute(function (array $c101): array {
            $c101['status'] = 'BROKEN_STATUS';
            return $c101;
        }, 'c101-status-broken');

        $reasonResult = $this->mutateC101AndExecute(function (array $c101): array {
            $c101['reason_code'] = 'BROKEN_REASON';
            return $c101;
        }, 'c101-reason-broken');

        $this->assertSame('C102_BLOCKED_C101_STATUS_OR_REASON_MISMATCH', $statusResult['status']);
        $this->assertSame('C102_BLOCKED_C101_STATUS_OR_REASON_MISMATCH', $reasonResult['status']);
    }

    public function test_c102_rejects_c101_next_recommendation_not_c102(): void
    {
        $result = $this->mutateC101AndExecute(function (array $c101): array {
            $c101['next_step_recommendation'] = 'BROKEN_NEXT';
            $c101['next_readiness_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c101['c101_operator_go_no_go_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c101['weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c101['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $c101;
        }, 'c101-next-broken');

        $this->assertSame('C102_BLOCKED_C101_NEXT_RECOMMENDATION_MISMATCH', $result['status']);
    }

    /**
     * @dataProvider c101OperatorGoNoGoStateMismatchProvider
     */
    public function test_c102_rejects_c101_operator_go_no_go_state_mismatch(string $field, $value): void
    {
        $result = $this->mutateC101AndExecute(function (array $c101) use ($field, $value): array {
            $c101[$field] = $value;
            $c101['c101_operator_go_no_go_decision'][$field] = $value;
            $c101['weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_decision'][$field] = $value;
            return $c101;
        }, 'c101-state-'.$field);

        $this->assertSame('C102_BLOCKED_C101_OPERATOR_GO_NO_GO_STATE_NOT_COMPLETE', $result['status'], $field);
    }

    public function c101OperatorGoNoGoStateMismatchProvider(): array
    {
        return [
            ['weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_review_pass', false],
            ['operator_go_decision_confirmed', false],
            ['operator_go_decision', 'NO_GO'],
            ['primary_candidate_weekly_swing_non_live_rehearsal_operator_go', false],
            ['backup_candidate_weekly_swing_non_live_rehearsal_operator_go', false],
            ['comparator_candidate_weekly_swing_non_live_rehearsal_operator_go', true],
        ];
    }

    public function test_c102_rejects_c101_c102_readiness_count_mismatch(): void
    {
        $result = $this->mutateC101AndExecute(function (array $c101): array {
            $c101['next_readiness_decision']['candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_review_count'] = 1;
            return $c101;
        }, 'c101-readiness-count-broken');

        $this->assertSame('C102_BLOCKED_C101_OPERATOR_GO_NO_GO_STATE_NOT_COMPLETE', $result['status']);
    }

    public function test_c102_rejects_c101_operator_manifest_not_artifact_only_or_used_for_live_output(): void
    {
        $notArtifactOnly = $this->mutateC101AndExecute(function (array $c101): array {
            $c101['weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_manifest']['operator_go_no_go_artifact_only'] = false;
            return $c101;
        }, 'c101-manifest-not-artifact-only');

        $usedForPlanConfirm = $this->mutateC101AndExecute(function (array $c101): array {
            $c101['weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_manifest']['operator_go_used_for_plan_confirm_mutation'] = true;
            return $c101;
        }, 'c101-manifest-plan-confirm');

        $officialOutput = $this->mutateC101AndExecute(function (array $c101): array {
            $c101['weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_manifest']['official_weekly_swing_stock_recommendations'] = ['SHOULD_NOT_EXIST'];
            return $c101;
        }, 'c101-manifest-official-output');

        $this->assertSame('C102_BLOCKED_C101_OPERATOR_GO_NO_GO_STATE_NOT_COMPLETE', $notArtifactOnly['status']);
        $this->assertSame('C102_BLOCKED_C101_OPERATOR_GO_NO_GO_STATE_NOT_COMPLETE', $usedForPlanConfirm['status']);
        $this->assertSame('C102_BLOCKED_C101_OPERATOR_GO_NO_GO_STATE_NOT_COMPLETE', $officialOutput['status']);
    }

    public function test_c102_rejects_a01_comparator_only_violation(): void
    {
        $result = $this->mutateC101AndExecute(function (array $c101): array {
            $c101['a01_remains_comparator_only'] = false;
            $c101['c101_operator_go_no_go_decision']['a01_remains_comparator_only'] = false;
            $c101['weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_decision']['a01_remains_comparator_only'] = false;
            return $c101;
        }, 'c101-a01-promoted');

        $this->assertSame('C102_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_GO_DECISION_FINALIZATION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $result['status']);
    }

    /**
     * @dataProvider safetyFlagProvider
     */
    public function test_c102_rejects_any_live_or_mutating_safety_flag_true_in_c101(string $field): void
    {
        $result = $this->mutateC101AndExecute(function (array $c101) use ($field): array {
            $c101[$field] = true;
            return $c101;
        }, 'c101-safety-'.$field);

        $this->assertSame('C102_BLOCKED_C101_SAFETY_FLAG_ALREADY_LIVE_OR_MUTATING', $result['status'], $field);
        $this->assertSame($field, $result['c101_live_or_mutating_safety_flag_failure']);
    }

    public function safetyFlagProvider(): array
    {
        return array_map(static function (string $flag): array {
            return [$flag];
        }, $this->requiredSafetyFlags());
    }

    public function test_c102_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c102-no-temporary-artifact-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame('C102_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_GO_DECISION_FINALIZATION_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS', $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c102_keeps_e02_primary_b01_backup_and_a01_comparator_only(): void
    {
        $result = $this->runService();

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $result['primary_candidate_code']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $result['backup_candidate_code']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $result['comparator_candidate_code']);
        $this->assertTrue($result['c102_go_decision_finalization_decision']['primary_candidate_weekly_swing_non_live_rehearsal_go_decision_finalized']);
        $this->assertTrue($result['c102_go_decision_finalization_decision']['backup_candidate_weekly_swing_non_live_rehearsal_go_decision_finalized']);
        $this->assertFalse($result['c102_go_decision_finalization_decision']['comparator_candidate_weekly_swing_non_live_rehearsal_go_decision_finalized']);
        $this->assertFalse($result['candidate_scope_freeze_summary']['a01_promoted']);
    }

    public function test_c102_writes_artifact_hash_and_c101_source_lock_top_level_aliases(): void
    {
        $result = $this->runService();

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $this->assertSame(self::C101_HASH, $result['expected_c101_hash']);
        $this->assertSame(self::C101_HASH, $result['actual_c101_hash']);
        $this->assertTrue($result['c101_hash_match']);
        $this->assertSame(self::C101_SHA1, $result['expected_c101_file_sha1']);
        $this->assertSame(self::C101_SHA1, $result['actual_c101_file_sha1']);
        $this->assertTrue($result['c101_file_sha1_match']);
    }

    public function test_c102_writes_next_recommendation_c103_completion_boundary(): void
    {
        $result = $this->runService();

        $this->assertSame(self::NEXT_C103, $result['next_step_recommendation']);
        $this->assertSame(self::NEXT_C103, $result['next_readiness_decision']['next_recommendation']);
        $this->assertSame(self::NEXT_C103, $result['planned_next_summary']['planned_next_review']);
        $this->assertSame(2, $result['next_readiness_decision']['candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_completion_boundary_review_count']);
    }

    public function test_c102_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $first = $this->execute(['options' => ['created_at' => '2026-06-29T00:00:00+00:00']]);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c102-go-finalization-pass-second.json';
        $this->tmpFiles[] = $secondOutput;
        $second = $this->execute([
            'output' => $secondOutput,
            'options' => ['created_at' => '2026-06-29T00:00:00+00:00'],
        ]);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
    }

    public function test_c102_records_required_sections_and_safety_flags(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();

        foreach ([
            'source_artifact_locks',
            'c101_lock_validation_summary',
            'lineage_validation_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'c102_go_decision_finalization_decision',
            'next_readiness_decision',
            'weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_decision',
            'weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_manifest',
            'weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_candidate_scorecard',
            'weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_context_summary',
            'runtime_readiness_inspection_summary',
            'feature_flag_operator_approval_kill_switch_validation_summary',
            'c101_operator_go_no_go_carry_forward_validation_summary',
            'go_decision_finalization_governance_summary',
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
            $this->assertFalse($result['c102_go_decision_finalization_decision'][$flag], $flag);
        }
    }

    public function test_c102_writes_temporary_negative_artifact_cleanup_fields_and_does_not_mutate_c101_artifact(): void
    {
        $fixture = $this->lockedC101Fixture();
        $before = strtoupper(sha1((string) file_get_contents($fixture['path'])));

        $result = $this->runService();

        $this->assertFalse($result['temporary_negative_artifacts_remaining']);
        $this->assertTrue($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertSame([], $result['temporary_negative_artifact_paths']);
        $this->assertSame($before, strtoupper(sha1((string) file_get_contents($fixture['path']))));
        $this->assertSame(self::C101_SHA1, $before);
    }

    public function test_c102_does_not_create_weekly_swing_live_output_or_official_recommendation(): void
    {
        $result = $this->runService();

        $this->assertFalse($result['weekly_swing_watchlist_official_output_generated']);
        $this->assertFalse($result['weekly_swing_watchlist_official_output_published']);
        $this->assertFalse($result['weekly_swing_watchlist_live_recommendation_generated']);
        $this->assertSame([], $result['weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_manifest']['official_weekly_swing_stock_recommendations']);
    }

    public function test_c102_writes_artifact_only_go_decision_finalization_manifest_not_used_for_plan_confirm_mutation(): void
    {
        $result = $this->runService();
        $manifest = $result['weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_manifest'];

        $this->assertTrue($manifest['manifest_created']);
        $this->assertSame('artifact_only_non_live_rehearsal_go_decision_finalization_review', $manifest['manifest_context']);
        $this->assertSame('non_live_artifact_only_rehearsal_go_decision_finalization', $manifest['execution_mode']);
        $this->assertTrue($manifest['go_decision_finalization_artifact_only']);
        $this->assertSame('GO', $manifest['operator_go_decision']);
        $this->assertTrue($manifest['go_decision_finalized']);
        $this->assertFalse($manifest['go_decision_finalization_used_for_selection']);
        $this->assertFalse($manifest['go_decision_finalization_used_for_retuning']);
        $this->assertFalse($manifest['go_decision_finalization_used_for_ranking']);
        $this->assertFalse($manifest['go_decision_finalization_used_for_plan_confirm_mutation']);
        $this->assertFalse($manifest['go_decision_finalization_used_for_live_rollout']);
        $this->assertFalse($manifest['plan_confirm_mutation_allowed']);
    }

    public function test_c102_candidate_scorecard_locks_primary_backup_finalized_go_and_a01_comparator_only(): void
    {
        $result = $this->runService();
        $scorecards = $result['weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_candidate_scorecard'];

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $scorecards[0]['candidate_code']);
        $this->assertSame('primary_non_live_rehearsal_finalized_go_candidate', $scorecards[0]['c102_role']);
        $this->assertTrue($scorecards[0]['primary_candidate_weekly_swing_non_live_rehearsal_go_decision_finalized']);
        $this->assertTrue($scorecards[0]['candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_completion_boundary_review']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $scorecards[1]['candidate_code']);
        $this->assertSame('backup_non_live_rehearsal_finalized_go_candidate', $scorecards[1]['c102_role']);
        $this->assertTrue($scorecards[1]['backup_candidate_weekly_swing_non_live_rehearsal_go_decision_finalized']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $scorecards[2]['candidate_code']);
        $this->assertSame('comparator_only_candidate', $scorecards[2]['c102_role']);
        $this->assertFalse($scorecards[2]['comparator_candidate_weekly_swing_non_live_rehearsal_go_decision_finalized']);
        $this->assertFalse($scorecards[2]['candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_completion_boundary_review']);
    }

    public function test_c102_does_not_mutate_c60_through_c101_artifacts(): void
    {
        $before = [];
        foreach (glob('storage/app/watchlist/backtest/c*.json') as $path) {
            if (preg_match('/storage\/app\/watchlist\/backtest\/c(6[0-9]|7[0-9]|8[0-9]|9[0-9]|10[0-1])-/', str_replace('\\', '/', $path)) === 1) {
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
        $service = new WatchlistBacktestC102WeeklySwingWatchlistNonLiveRehearsalGoDecisionFinalizationReviewService();
        $fixture = $this->lockedC101Fixture();
        return $service->execute(
            (string) ($overrides['c101Artifact'] ?? $fixture['path']),
            (string) ($overrides['expectedC101Hash'] ?? $fixture['hash']),
            (string) ($overrides['expectedC101FileSha1'] ?? $fixture['sha1']),
            (string) ($overrides['output'] ?? $this->output),
            array_merge([
                'overwrite' => true,
                'operator_approved' => $overrides['operatorApproved'] ?? true,
                'approval_reference' => $overrides['approvalReference'] ?? 'C102_OPERATOR_APPROVED_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_GO_DECISION_FINALIZATION_REVIEW_ONLY',
            ], (array) ($overrides['options'] ?? []))
        );
    }

    private function mutateC101AndExecute(callable $mutator, string $name): array
    {
        $fixture = $this->lockedC101Fixture();
        $payload = json_decode((string) file_get_contents($fixture['path']), true);
        $payload = $mutator(is_array($payload) ? $payload : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c102-source-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
        return $this->execute([
            'c101Artifact' => $path,
            'expectedC101Hash' => (string) ($payload['artifact_hash'] ?? ''),
            'expectedC101FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function lockedC101Fixture(): array
    {
        $path = 'storage/app/watchlist/backtest/c101-weekly-swing-watchlist-non-live-rehearsal-operator-go-no-go-review.json';
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

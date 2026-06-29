<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC101WeeklySwingWatchlistNonLiveRehearsalOperatorGoNoGoReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC101WeeklySwingWatchlistNonLiveRehearsalOperatorGoNoGoReviewTest extends TestCase
{
    private string $output;

    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c101-operator-go-no-go-test.json';
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

    public function test_c101_passes_with_valid_c100_lock_operator_approval_and_reference(): void
    {
        $result = $this->runService();

        $this->assertSame('C101_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertSame($result['status'], $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_review_executed']);
        $this->assertTrue($result['weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_review_allowed']);
        $this->assertTrue($result['weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_review_pass']);
        $this->assertSame('GO', $result['operator_go_decision']);
        $this->assertTrue($result['operator_go_decision_confirmed']);
        $this->assertTrue($result['primary_candidate_weekly_swing_non_live_rehearsal_operator_go']);
        $this->assertTrue($result['backup_candidate_weekly_swing_non_live_rehearsal_operator_go']);
        $this->assertFalse($result['comparator_candidate_weekly_swing_non_live_rehearsal_operator_go']);
        $this->assertTrue($result['a01_remains_comparator_only']);
        $this->assertTrue($result['c100_non_live_rehearsal_result_reviewed']);
        $this->assertSame('C102_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_GO_DECISION_FINALIZATION_REVIEW', $result['next_step_recommendation']);
        $this->assertSame('READY_FOR_C102_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_GO_DECISION_FINALIZATION_REVIEW', $result['diagnostic_conclusion']);
        $this->assertFileExists($this->output);
    }

    public function test_c101_rejects_missing_operator_approval(): void
    {
        $result = $this->execute(['operatorApproved' => false]);

        $this->assertSame('C101_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $result['status']);
        $this->assertSame($result['status'], $result['reason_code']);
    }

    public function test_c101_rejects_missing_approval_reference(): void
    {
        $result = $this->execute(['approvalReference' => '']);

        $this->assertSame('C101_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $result['status']);
        $this->assertSame($result['status'], $result['reason_code']);
    }

    public function test_c101_rejects_unconfirmed_go_decision(): void
    {
        $result = $this->execute(['options' => ['operator_go_decision_confirmed' => false]]);

        $this->assertSame('C101_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_OPERATOR_GO_NO_GO_REVIEW_REJECTED_GO_DECISION_NOT_CONFIRMED', $result['status']);
        $this->assertSame('NO_GO', $result['operator_go_decision']);
    }

    public function test_c101_rejects_missing_c100_artifact(): void
    {
        $result = $this->execute([
            'c100Artifact' => 'storage/app/watchlist/backtest/missing-c100-for-c101.json',
            'expectedC100Hash' => 'missing',
            'expectedC100FileSha1' => 'missing',
        ]);

        $this->assertSame('C101_BLOCKED_C100_ARTIFACT_LOCK_MISMATCH', $result['status']);
    }

    public function test_c101_rejects_expected_c100_artifact_hash_mismatch(): void
    {
        $fixture = $this->lockedC100Fixture();
        $result = $this->execute([
            'c100Artifact' => $fixture['path'],
            'expectedC100Hash' => 'bad-hash',
            'expectedC100FileSha1' => $fixture['sha1'],
        ]);

        $this->assertSame('C101_BLOCKED_C100_ARTIFACT_LOCK_MISMATCH', $result['status']);
        $this->assertFalse($result['c100_hash_match']);
    }

    public function test_c101_rejects_expected_c100_file_sha1_mismatch(): void
    {
        $fixture = $this->lockedC100Fixture();
        $result = $this->execute([
            'c100Artifact' => $fixture['path'],
            'expectedC100Hash' => $fixture['hash'],
            'expectedC100FileSha1' => 'BADSHA1',
        ]);

        $this->assertSame('C101_BLOCKED_C100_FILE_SHA1_LOCK_MISMATCH', $result['status']);
        $this->assertFalse($result['c100_file_sha1_match']);
    }

    public function test_c101_rejects_c100_status_not_passed_non_live_rehearsal_result_reviewed(): void
    {
        $result = $this->mutateC100AndExecute(function (array $c100): array {
            $c100['status'] = 'BROKEN_STATUS';
            return $c100;
        }, 'c100-status-broken');

        $this->assertSame('C101_BLOCKED_C100_STATUS_OR_REASON_MISMATCH', $result['status']);
    }

    public function test_c101_rejects_c100_reason_code_not_passed_non_live_rehearsal_result_reviewed(): void
    {
        $result = $this->mutateC100AndExecute(function (array $c100): array {
            $c100['reason_code'] = 'BROKEN_REASON';
            return $c100;
        }, 'c100-reason-broken');

        $this->assertSame('C101_BLOCKED_C100_STATUS_OR_REASON_MISMATCH', $result['status']);
    }

    public function test_c101_rejects_c100_next_recommendation_not_c101(): void
    {
        $result = $this->mutateC100AndExecute(function (array $c100): array {
            $c100['next_step_recommendation'] = 'BROKEN_NEXT';
            $c100['next_readiness_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c100['c100_result_review_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c100['weekly_swing_watchlist_non_live_rehearsal_result_review_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c100['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $c100;
        }, 'c100-next-broken');

        $this->assertSame('C101_BLOCKED_C100_NEXT_RECOMMENDATION_MISMATCH', $result['status']);
    }

    /**
     * @dataProvider c100ResultReviewStateMismatchProvider
     */
    public function test_c101_rejects_c100_non_live_rehearsal_result_review_state_mismatch(string $field, $value): void
    {
        $result = $this->mutateC100AndExecute(function (array $c100) use ($field, $value): array {
            $c100[$field] = $value;
            $c100['c100_result_review_decision'][$field] = $value;
            return $c100;
        }, 'c100-result-'.$field);

        $this->assertSame('C101_BLOCKED_C100_NON_LIVE_REHEARSAL_RESULT_REVIEW_STATE_NOT_COMPLETE', $result['status'], $field);
    }

    public function c100ResultReviewStateMismatchProvider(): array
    {
        return [
            ['weekly_swing_watchlist_non_live_rehearsal_result_review_pass', false],
            ['weekly_swing_watchlist_non_live_rehearsal_result_reviewed', false],
            ['weekly_swing_watchlist_non_live_rehearsal_result_review_manifest_created', false],
            ['primary_candidate_weekly_swing_non_live_rehearsal_result_reviewed', false],
            ['backup_candidate_weekly_swing_non_live_rehearsal_result_reviewed', false],
            ['comparator_candidate_weekly_swing_non_live_rehearsal_result_reviewed', true],
        ];
    }

    public function test_c101_rejects_c100_c101_readiness_count_mismatch(): void
    {
        $result = $this->mutateC100AndExecute(function (array $c100): array {
            $c100['next_readiness_decision']['candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_review_count'] = 1;
            return $c100;
        }, 'c100-readiness-count-broken');

        $this->assertSame('C101_BLOCKED_C100_NON_LIVE_REHEARSAL_RESULT_REVIEW_STATE_NOT_COMPLETE', $result['status']);
    }

    public function test_c101_rejects_c100_result_review_manifest_not_artifact_only(): void
    {
        $result = $this->mutateC100AndExecute(function (array $c100): array {
            $c100['weekly_swing_watchlist_non_live_rehearsal_result_review_manifest']['rehearsal_result_review_artifact_only'] = false;
            return $c100;
        }, 'c100-manifest-not-artifact-only');

        $this->assertSame('C101_BLOCKED_C100_NON_LIVE_REHEARSAL_RESULT_REVIEW_STATE_NOT_COMPLETE', $result['status']);
    }

    public function test_c101_rejects_c100_result_review_manifest_used_for_plan_confirm_mutation(): void
    {
        $result = $this->mutateC100AndExecute(function (array $c100): array {
            $c100['weekly_swing_watchlist_non_live_rehearsal_result_review_manifest']['rehearsal_result_review_used_for_plan_confirm_mutation'] = true;
            return $c100;
        }, 'c100-manifest-plan-confirm');

        $this->assertSame('C101_BLOCKED_C100_NON_LIVE_REHEARSAL_RESULT_REVIEW_STATE_NOT_COMPLETE', $result['status']);
    }

    public function test_c101_rejects_c100_result_review_manifest_with_official_weekly_swing_output(): void
    {
        $result = $this->mutateC100AndExecute(function (array $c100): array {
            $c100['weekly_swing_watchlist_non_live_rehearsal_result_review_manifest']['official_weekly_swing_stock_recommendations'] = ['SHOULD_NOT_EXIST'];
            return $c100;
        }, 'c100-manifest-official-output');

        $this->assertSame('C101_BLOCKED_C100_NON_LIVE_REHEARSAL_RESULT_REVIEW_STATE_NOT_COMPLETE', $result['status']);
    }

    public function test_c101_rejects_a01_comparator_only_violation(): void
    {
        $result = $this->mutateC100AndExecute(function (array $c100): array {
            $c100['a01_remains_comparator_only'] = false;
            $c100['c100_result_review_decision']['a01_remains_comparator_only'] = false;
            return $c100;
        }, 'c100-a01-promoted');

        $this->assertSame('C101_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_OPERATOR_GO_NO_GO_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $result['status']);
    }

    /**
     * @dataProvider safetyFlagProvider
     */
    public function test_c101_rejects_any_live_or_mutating_safety_flag_true_in_c100(string $field): void
    {
        $result = $this->mutateC100AndExecute(function (array $c100) use ($field): array {
            $c100[$field] = true;
            return $c100;
        }, 'c100-safety-'.$field);

        $this->assertSame('C101_BLOCKED_C100_SAFETY_FLAG_ALREADY_LIVE_OR_MUTATING', $result['status'], $field);
        $this->assertSame($field, $result['c100_live_or_mutating_safety_flag_failure']);
    }

    public function safetyFlagProvider(): array
    {
        return array_map(static function (string $flag): array {
            return [$flag];
        }, $this->requiredSafetyFlags());
    }

    public function test_c101_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c101-no-temporary-artifact-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame('C101_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_OPERATOR_GO_NO_GO_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS', $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c101_keeps_a01_comparator_only_and_never_promotes_it(): void
    {
        $result = $this->runService();

        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $result['comparator_candidate_code']);
        $this->assertTrue($result['c101_operator_go_no_go_decision']['a01_remains_comparator_only']);
        $this->assertFalse($result['c101_operator_go_no_go_decision']['comparator_candidate_weekly_swing_non_live_rehearsal_operator_go']);
        $this->assertFalse($result['candidate_scope_freeze_summary']['a01_promoted']);
        $this->assertFalse($result['candidate_scope_freeze_summary']['a01_used_as_runtime_fallback']);
    }

    public function test_c101_keeps_e02_primary_and_b01_backup(): void
    {
        $result = $this->runService();

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $result['primary_candidate_code']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $result['backup_candidate_code']);
        $this->assertTrue($result['c101_operator_go_no_go_decision']['primary_candidate_weekly_swing_non_live_rehearsal_operator_go']);
        $this->assertTrue($result['c101_operator_go_no_go_decision']['backup_candidate_weekly_swing_non_live_rehearsal_operator_go']);
    }

    public function test_c101_writes_artifact_hash_and_c100_source_lock_top_level_aliases(): void
    {
        $result = $this->runService();

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $this->assertSame('3b4467db23914686eea465ecf11601e7dfd3a9e6', $result['expected_c100_hash']);
        $this->assertSame('3b4467db23914686eea465ecf11601e7dfd3a9e6', $result['actual_c100_hash']);
        $this->assertTrue($result['c100_hash_match']);
        $this->assertSame('E66CD7902FBE0454BFC30CED7695020E925B597E', $result['expected_c100_file_sha1']);
        $this->assertSame('E66CD7902FBE0454BFC30CED7695020E925B597E', $result['actual_c100_file_sha1']);
        $this->assertTrue($result['c100_file_sha1_match']);
    }

    public function test_c101_writes_next_recommendation_c102(): void
    {
        $result = $this->runService();

        $this->assertSame('C102_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_GO_DECISION_FINALIZATION_REVIEW', $result['next_step_recommendation']);
        $this->assertSame('C102_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_GO_DECISION_FINALIZATION_REVIEW', $result['next_readiness_decision']['next_recommendation']);
        $this->assertSame('C102_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_GO_DECISION_FINALIZATION_REVIEW', $result['planned_next_summary']['planned_next_review']);
    }

    public function test_c101_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $first = $this->execute(['options' => ['created_at' => '2026-06-28T00:00:00+00:00']]);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c101-operator-go-no-go-test-second.json';
        $this->tmpFiles[] = $secondOutput;
        $second = $this->execute([
            'output' => $secondOutput,
            'options' => ['created_at' => '2026-06-28T00:00:00+00:00'],
        ]);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
    }

    public function test_c101_records_required_sections_and_safety_flags(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();

        foreach ([
            'source_artifact_locks',
            'c100_lock_validation_summary',
            'lineage_validation_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'c101_operator_go_no_go_decision',
            'next_readiness_decision',
            'weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_decision',
            'weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_manifest',
            'weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_candidate_scorecard',
            'weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_context_summary',
            'runtime_readiness_inspection_summary',
            'feature_flag_operator_approval_kill_switch_validation_summary',
            'c100_non_live_rehearsal_result_review_carry_forward_validation_summary',
            'operator_go_no_go_governance_summary',
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
            $this->assertFalse($result['c101_operator_go_no_go_decision'][$flag], $flag);
        }
    }

    public function test_c101_writes_temporary_negative_artifact_cleanup_fields_and_does_not_mutate_c100_artifact(): void
    {
        $fixture = $this->lockedC100Fixture();
        $before = strtoupper(sha1((string) file_get_contents($fixture['path'])));

        $result = $this->runService();

        $this->assertFalse($result['temporary_negative_artifacts_remaining']);
        $this->assertTrue($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertSame([], $result['temporary_negative_artifact_paths']);
        $this->assertSame($before, strtoupper(sha1((string) file_get_contents($fixture['path']))));
        $this->assertSame('E66CD7902FBE0454BFC30CED7695020E925B597E', $before);
    }

    public function test_c101_writes_weekly_swing_non_live_rehearsal_operator_go_flags_correctly(): void
    {
        $result = $this->runService();

        $this->assertTrue($result['weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_review_executed']);
        $this->assertTrue($result['weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_review_allowed']);
        $this->assertTrue($result['weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_review_pass']);
        $this->assertSame('GO', $result['operator_go_decision']);
        $this->assertTrue($result['operator_go_decision_confirmed']);
        $this->assertFalse($result['weekly_swing_watchlist_runtime_active']);
        $this->assertFalse($result['weekly_swing_watchlist_plan_confirm_mutation_allowed']);
        $this->assertFalse($result['weekly_swing_watchlist_live_output_enabled']);
    }

    public function test_c101_does_not_create_weekly_swing_live_output_or_official_recommendation(): void
    {
        $result = $this->runService();

        $this->assertFalse($result['weekly_swing_watchlist_official_output_generated']);
        $this->assertFalse($result['weekly_swing_watchlist_official_output_published']);
        $this->assertFalse($result['weekly_swing_watchlist_live_recommendation_generated']);
        $this->assertSame([], $result['weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_manifest']['official_weekly_swing_stock_recommendations']);
    }

    public function test_c101_writes_artifact_only_operator_go_no_go_manifest_not_used_for_plan_confirm_mutation(): void
    {
        $result = $this->runService();
        $manifest = $result['weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_manifest'];

        $this->assertTrue($manifest['manifest_created']);
        $this->assertSame('artifact_only_non_live_rehearsal_operator_go_no_go_review', $manifest['manifest_context']);
        $this->assertSame('non_live_artifact_only_rehearsal_operator_go_no_go', $manifest['execution_mode']);
        $this->assertTrue($manifest['operator_go_no_go_artifact_only']);
        $this->assertSame('GO', $manifest['operator_go_decision']);
        $this->assertFalse($manifest['operator_go_used_for_selection']);
        $this->assertFalse($manifest['operator_go_used_for_retuning']);
        $this->assertFalse($manifest['operator_go_used_for_ranking']);
        $this->assertFalse($manifest['operator_go_used_for_plan_confirm_mutation']);
        $this->assertFalse($manifest['operator_go_used_for_live_rollout']);
        $this->assertFalse($manifest['plan_confirm_mutation_allowed']);
    }

    public function test_c101_candidate_scorecard_locks_primary_backup_go_and_a01_comparator_only(): void
    {
        $result = $this->runService();
        $scorecards = $result['weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_candidate_scorecard'];

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $scorecards[0]['candidate_code']);
        $this->assertSame('primary_non_live_rehearsal_operator_go_candidate', $scorecards[0]['c101_role']);
        $this->assertTrue($scorecards[0]['primary_candidate_weekly_swing_non_live_rehearsal_operator_go']);
        $this->assertTrue($scorecards[0]['candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_review']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $scorecards[1]['candidate_code']);
        $this->assertSame('backup_non_live_rehearsal_operator_go_candidate', $scorecards[1]['c101_role']);
        $this->assertTrue($scorecards[1]['backup_candidate_weekly_swing_non_live_rehearsal_operator_go']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $scorecards[2]['candidate_code']);
        $this->assertSame('comparator_only_candidate', $scorecards[2]['c101_role']);
        $this->assertFalse($scorecards[2]['comparator_candidate_weekly_swing_non_live_rehearsal_operator_go']);
        $this->assertFalse($scorecards[2]['candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_review']);
    }

    public function test_c101_does_not_mutate_c60_through_c100_artifacts(): void
    {
        $before = [];
        foreach (glob('storage/app/watchlist/backtest/c*.json') as $path) {
            if (preg_match('/storage\/app\/watchlist\/backtest\/c(6[0-9]|7[0-9]|8[0-9]|9[0-9]|100)-/', str_replace('\\', '/', $path)) === 1) {
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
        $service = new WatchlistBacktestC101WeeklySwingWatchlistNonLiveRehearsalOperatorGoNoGoReviewService();
        $fixture = $this->lockedC100Fixture();
        return $service->execute(
            (string) ($overrides['c100Artifact'] ?? $fixture['path']),
            (string) ($overrides['expectedC100Hash'] ?? $fixture['hash']),
            (string) ($overrides['expectedC100FileSha1'] ?? $fixture['sha1']),
            (string) ($overrides['output'] ?? $this->output),
            array_merge([
                'overwrite' => true,
                'operator_approved' => $overrides['operatorApproved'] ?? true,
                'approval_reference' => $overrides['approvalReference'] ?? 'C101_OPERATOR_APPROVED_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_OPERATOR_GO_NO_GO_REVIEW_ONLY',
            ], (array) ($overrides['options'] ?? []))
        );
    }

    private function mutateC100AndExecute(callable $mutator, string $name): array
    {
        $fixture = $this->lockedC100Fixture();
        $payload = json_decode((string) file_get_contents($fixture['path']), true);
        $payload = $mutator(is_array($payload) ? $payload : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c101-source-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
        return $this->execute([
            'c100Artifact' => $path,
            'expectedC100Hash' => (string) ($payload['artifact_hash'] ?? ''),
            'expectedC100FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function lockedC100Fixture(): array
    {
        $path = 'storage/app/watchlist/backtest/c100-weekly-swing-watchlist-non-live-rehearsal-result-review.json';
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

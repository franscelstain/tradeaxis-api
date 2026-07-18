<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC164WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationCompletionResultReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC164WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationCompletionResultReviewTest extends TestCase
{
    private const C164_EXECUTION_ARTIFACT = 'storage/app/watchlist/backtest/c164-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-completion-execution.json';
    private const C164_EXECUTION_HASH = '78066e88b917b317ba6af5777b0ddc98b04bc29a';
    private const C164_EXECUTION_SHA1 = 'EEBF3B6A4D12203FB1860CFC1E60DF72C057E815';
    private const CONTROLLED_COMPLETION = 'storage/app/watchlist/output/c161-weekly-swing-watchlist-controlled-plan-confirm-completion.json';
    private const CONTROLLED_COMPLETION_HASH = 'e9862d9e7738d0558f107d978f329f97f14b3520';
    private const CONTROLLED_COMPLETION_SHA1 = 'AB9FC9F714339B78D68132222AC8C398BE7EE1B3';
    private const PASS_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_PASSED_READY_FOR_OPERATOR_GO_NO_GO_REVIEW_PRIMARY_AND_BACKUP';
    private const NEXT_OPERATOR = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW';

    private string $output;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c164-plan-confirm-completion-post-handoff-activation-completion-result-review.json';
        $this->cleanupC164TemporaryArtifacts();
        @unlink($this->output);
    }

    protected function tearDown(): void
    {
        @unlink($this->output);
        foreach ($this->tmpFiles as $path) {
            @unlink($path);
        }
        $this->cleanupC164TemporaryArtifacts();
        parent::tearDown();
    }

    public function test_c164_completion_result_review_passes_and_keeps_same_topic_number_for_operator_review(): void
    {
        $result = $this->runService();

        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW', $result['run_code']);
        $this->assertSame('PR-83 / C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW', $result['phase_label']);
        $this->assertSame('C164_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION', $result['topic_code']);
        $this->assertSame('PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW', $result['topic_stage']);
        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame(self::PASS_STATUS, $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_result_review_pass']);
        $this->assertTrue($result['production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_result_review_pass']);
        $this->assertTrue($result['post_handoff_activation_completion_result_reviewed']);
        $this->assertTrue($result['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_result_review_manifest_created']);
        $this->assertTrue($result['ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_review']);
        $this->assertSame(self::NEXT_OPERATOR, $result['next_step_recommendation']);
        $this->assertSame(self::NEXT_OPERATOR, $result['planned_next_summary']['planned_next_review']);
        $this->assertSame(self::NEXT_OPERATOR, $result['next_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_decision']['next_recommendation']);
        $this->assertTrue($result['next_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_decision']['same_topic_c164_continues']);
        $this->assertTrue($result['next_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_decision']['topic_number_must_not_advance_until_c164_finalization']);
        $this->assertFileExists($this->output);
    }

    public function test_c164_completion_result_review_records_dual_locks_sections_and_controlled_function_scope(): void
    {
        $result = $this->runService();
        $run = $this->readJson($this->output);

        foreach ([
            'source_artifact_locks',
            'c164_execution_lock_validation_summary',
            'controlled_completion_lock_validation_summary',
            'c164_execution_carry_forward_summary',
            'controlled_completion_result_review_summary',
            'controlled_completion_integrity_summary',
            'watchlist_function_scope_summary',
            'publication_plan_confirm_safety_summary',
            'candidate_completion_result_scorecard',
            'operator_approval_validation_summary',
            'result_review_confirmation_summary',
            'temporary_negative_artifact_guard_summary',
            'c164_completion_result_review_decision',
            'next_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_decision',
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_result_review_manifest',
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_result_review_checklist',
            'documentation_hygiene_guard_summary',
            'progress_summary',
            'planned_next_summary',
            'diagnostics',
            'failure_attribution_summary',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }

        $this->assertSame(self::C164_EXECUTION_HASH, $result['expected_c164_execution_hash']);
        $this->assertSame(self::C164_EXECUTION_HASH, $result['actual_c164_execution_hash']);
        $this->assertTrue($result['c164_execution_hash_match']);
        $this->assertSame(self::C164_EXECUTION_SHA1, $result['actual_c164_execution_file_sha1']);
        $this->assertTrue($result['c164_execution_file_sha1_match']);
        $this->assertSame(self::CONTROLLED_COMPLETION_HASH, $result['expected_controlled_completion_hash']);
        $this->assertSame(self::CONTROLLED_COMPLETION_HASH, $result['actual_controlled_completion_hash']);
        $this->assertTrue($result['controlled_completion_hash_match']);
        $this->assertSame(self::CONTROLLED_COMPLETION_SHA1, $result['actual_controlled_completion_file_sha1']);
        $this->assertTrue($result['controlled_completion_file_sha1_match']);
        $this->assertTrue($result['controlled_completion_integrity_valid']);
        $this->assertSame('CONTROLLED_WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATION', $result['watchlist_function_used']);
    }

    public function test_c164_completion_result_review_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->runService(['operatorApproved' => false]);
        $missingReference = $this->runService(['approvalReference' => '']);

        $expected = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
        $this->assertSame($expected, $missingOperator['status']);
        $this->assertSame($expected, $missingReference['status']);
    }

    public function test_c164_completion_result_review_rejects_missing_required_confirmations(): void
    {
        $resultReview = $this->runService(['resultReviewConfirmed' => false]);
        $executionResult = $this->runService(['completionExecutionResultConfirmed' => false]);
        $controlledResult = $this->runService(['controlledCompletionResultConfirmed' => false]);
        $controlledOnly = $this->runService(['controlledCompletionOnlyConfirmed' => false]);
        $unchanged = $this->runService(['planConfirmUnchangedConfirmed' => false]);
        $noRollout = $this->runService(['noLivePlanConfirmRolloutConfirmed' => false]);
        $freeLock = $this->runService(['freePublicationLockedConfirmed' => false]);

        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_REJECTED_RESULT_REVIEW_CONFIRMATION_MISSING', $resultReview['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_REJECTED_COMPLETION_EXECUTION_RESULT_CONFIRMATION_MISSING', $executionResult['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_REJECTED_CONTROLLED_COMPLETION_RESULT_CONFIRMATION_MISSING', $controlledResult['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_REJECTED_CONTROLLED_COMPLETION_ONLY_CONFIRMATION_MISSING', $controlledOnly['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING', $unchanged['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_REJECTED_NO_LIVE_PLAN_CONFIRM_ROLLOUT_CONFIRMATION_MISSING', $noRollout['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_REJECTED_FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING', $freeLock['status']);
    }

    public function test_c164_completion_result_review_rejects_execution_lock_status_phase_next_or_convert_mismatch(): void
    {
        $missing = $this->runService([
            'c164ExecutionArtifact' => 'storage/app/watchlist/backtest/.tmp-c164-execution-missing.json',
            'expectedC164ExecutionHash' => 'missing',
            'expectedC164ExecutionFileSha1' => 'missing',
        ]);
        $hashMismatch = $this->runService(['expectedC164ExecutionHash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedC164ExecutionFileSha1' => 'BADSHA1']);
        $status = $this->mutateExecutionAndExecute(function (array $execution): array {
            $execution['status'] = 'BROKEN_STATUS';
            return $execution;
        }, 'status');
        $phase = $this->mutateExecutionAndExecute(function (array $execution): array {
            $execution['phase_label'] = 'BROKEN_PHASE';
            return $execution;
        }, 'phase');
        $next = $this->mutateExecutionAndExecute(function (array $execution): array {
            $execution['next_step_recommendation'] = 'BROKEN_NEXT';
            $execution['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            $execution['next_plan_confirm_completion_post_handoff_activation_completion_result_review_decision']['next_recommendation'] = 'BROKEN_NEXT';
            return $execution;
        }, 'next');
        $duplicate = $this->duplicateTopLevelKeyFixture(self::C164_EXECUTION_ARTIFACT, 'execution-duplicate', 'Status');
        $duplicateResult = $this->runService([
            'c164ExecutionArtifact' => $duplicate,
            'expectedC164ExecutionHash' => self::C164_EXECUTION_HASH,
            'expectedC164ExecutionFileSha1' => strtoupper(sha1((string) file_get_contents($duplicate))),
        ]);

        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_REJECTED_C164_COMPLETION_EXECUTION_ARTIFACT_LOCK_MISMATCH', $missing['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_REJECTED_C164_COMPLETION_EXECUTION_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_REJECTED_C164_COMPLETION_EXECUTION_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_REJECTED_C164_COMPLETION_EXECUTION_STATUS_MISMATCH', $status['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_REJECTED_C164_COMPLETION_EXECUTION_PHASE_LABEL_MISMATCH', $phase['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_REJECTED_C164_COMPLETION_EXECUTION_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_REJECTED_C164_COMPLETION_EXECUTION_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION', $duplicateResult['status']);
    }

    public function test_c164_completion_result_review_rejects_controlled_completion_lock_and_convert_mismatch(): void
    {
        $hashMismatch = $this->runService(['expectedControlledCompletionHash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedControlledCompletionFileSha1' => 'BADSHA1']);
        $duplicate = $this->duplicateTopLevelKeyFixture(self::CONTROLLED_COMPLETION, 'controlled-duplicate', 'Controlled_Completion_Hash');
        $duplicateResult = $this->runService([
            'controlledCompletion' => $duplicate,
            'expectedControlledCompletionFileSha1' => strtoupper(sha1((string) file_get_contents($duplicate))),
        ]);

        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_REJECTED_CONTROLLED_COMPLETION_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_REJECTED_CONTROLLED_COMPLETION_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_REJECTED_CONTROLLED_COMPLETION_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION', $duplicateResult['status']);
    }

    /**
     * @dataProvider incompleteExecutionProvider
     */
    public function test_c164_completion_result_review_rejects_incomplete_execution_evidence(string $field, $value): void
    {
        $result = $this->mutateExecutionAndExecute(function (array $execution) use ($field, $value): array {
            $this->setValueAt($execution, explode('.', $field), $value);
            return $execution;
        }, 'execution-incomplete-'.str_replace('.', '-', $field));

        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_REJECTED_C164_COMPLETION_EXECUTION_STATE_INVALID', $result['status'], $field);
    }

    public function incompleteExecutionProvider(): array
    {
        return [
            ['weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_pass', false],
            ['ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_result_review', false],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_execution_manifest_created', false],
            ['primary_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_result_review', false],
            ['backup_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_result_review', false],
            ['c164_completion_execution_only', false],
            ['topic_stage', 'PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW'],
            ['c164_completion_execution_decision.review_valid', false],
            ['next_plan_confirm_completion_post_handoff_activation_completion_result_review_decision.review_valid', false],
            ['next_plan_confirm_completion_post_handoff_activation_completion_result_review_decision.same_topic_c164_continues', false],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_execution_manifest.completion_execution_artifact_only', false],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_execution_manifest.ready_for_plan_confirm_completion_post_handoff_activation_completion_result_review', false],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_execution_checklist.completion_result_review_required_next', false],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_execution_checklist.completion_execution_only', false],
        ];
    }

    public function test_c164_completion_result_review_rejects_integrity_publication_candidate_or_function_scope_change(): void
    {
        $state = $this->mutateControlledCompletionAndExecute(function (array $completion): array {
            $completion['plan_confirm_completion_state'] = 'not_executed';
            return $completion;
        }, 'state');
        $published = $this->mutateControlledCompletionAndExecute(function (array $completion): array {
            $completion['free_publication_allowed'] = true;
            return $completion;
        }, 'published');
        $candidate = $this->mutateControlledCompletionAndExecute(function (array $completion): array {
            $completion['output_rows'][0]['candidate_code'] = 'BROKEN_PRIMARY';
            return $completion;
        }, 'candidate');
        $function = $this->mutateExecutionAndExecute(function (array $execution): array {
            $execution['watchlist_function_used'] = 'UNLOCKED_WATCHLIST_FUNCTION';
            return $execution;
        }, 'function');

        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_REJECTED_CONTROLLED_COMPLETION_INTEGRITY_MISMATCH', $state['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED', $published['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $candidate['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_REJECTED_WATCHLIST_FUNCTION_SCOPE_MISMATCH', $function['status']);
    }

    public function test_c164_completion_result_review_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c164-completion-result-review-negative-remains-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS', $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c164_completion_result_review_records_manifest_checklist_scorecard_safety_and_deterministic_hash(): void
    {
        $first = $this->runService(['createdAt' => '2026-07-18T00:00:00+00:00']);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c164-plan-confirm-completion-post-handoff-activation-completion-result-review-second.json';
        $this->tmpFiles[] = $secondOutput;
        $second = $this->runService(['output' => $secondOutput, 'createdAt' => '2026-07-18T00:00:00+00:00']);
        $manifest = $first['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_result_review_manifest'];
        $checklist = $first['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_result_review_checklist'];
        $scorecard = $first['candidate_completion_result_scorecard'];

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
        $this->assertTrue($manifest['manifest_created']);
        $this->assertTrue($manifest['result_review_artifact_only']);
        $this->assertTrue($manifest['ready_for_operator_go_no_go_review']);
        $this->assertFalse($manifest['result_review_used_for_free_publication']);
        $this->assertFalse($manifest['result_review_used_for_plan_confirm_mutation']);
        $this->assertFalse($manifest['result_review_used_for_live_plan_confirm_rollout']);
        $this->assertSame([], $manifest['official_weekly_swing_stock_recommendations']);
        $this->assertTrue($checklist['result_review_only']);
        $this->assertTrue($checklist['operator_go_no_go_review_required_next']);
        $this->assertTrue($scorecard[0]['plan_confirm_completion_post_handoff_activation_completion_result_reviewed']);
        $this->assertTrue($scorecard[1]['plan_confirm_completion_post_handoff_activation_completion_result_reviewed']);
        $this->assertFalse($scorecard[2]['plan_confirm_completion_post_handoff_activation_completion_result_reviewed']);
        $this->assertFalse($first['publication_plan_confirm_safety_summary']['weekly_swing_watchlist_official_output_published']);
        $this->assertFalse($first['publication_plan_confirm_safety_summary']['plan_confirm_mutated']);
        $this->assertFalse($first['publication_plan_confirm_safety_summary']['live_plan_confirm_rollout_executed']);
    }

    public function test_c164_completion_result_review_does_not_mutate_source_artifacts_or_config_defaults(): void
    {
        $beforeExecution = strtoupper(sha1((string) file_get_contents(self::C164_EXECUTION_ARTIFACT)));
        $beforeCompletion = strtoupper(sha1((string) file_get_contents(self::CONTROLLED_COMPLETION)));
        $beforeConfig = (string) file_get_contents('config/watchlist.php');

        $this->runService();

        $this->assertSame($beforeExecution, strtoupper(sha1((string) file_get_contents(self::C164_EXECUTION_ARTIFACT))));
        $this->assertSame($beforeCompletion, strtoupper(sha1((string) file_get_contents(self::CONTROLLED_COMPLETION))));
        $this->assertSame($beforeConfig, (string) file_get_contents('config/watchlist.php'));
    }

    private function runService(array $options = []): array
    {
        $service = new WatchlistBacktestC164WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationCompletionResultReviewService();

        return $service->execute(
            (string) ($options['c164ExecutionArtifact'] ?? self::C164_EXECUTION_ARTIFACT),
            (string) ($options['expectedC164ExecutionHash'] ?? self::C164_EXECUTION_HASH),
            (string) ($options['expectedC164ExecutionFileSha1'] ?? self::C164_EXECUTION_SHA1),
            (string) ($options['controlledCompletion'] ?? self::CONTROLLED_COMPLETION),
            (string) ($options['expectedControlledCompletionHash'] ?? self::CONTROLLED_COMPLETION_HASH),
            (string) ($options['expectedControlledCompletionFileSha1'] ?? self::CONTROLLED_COMPLETION_SHA1),
            (string) ($options['output'] ?? $this->output),
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'result_review_confirmed' => (bool) ($options['resultReviewConfirmed'] ?? true),
                'completion_execution_result_confirmed' => (bool) ($options['completionExecutionResultConfirmed'] ?? true),
                'controlled_completion_result_confirmed' => (bool) ($options['controlledCompletionResultConfirmed'] ?? true),
                'controlled_completion_only_confirmed' => (bool) ($options['controlledCompletionOnlyConfirmed'] ?? true),
                'plan_confirm_unchanged_confirmed' => (bool) ($options['planConfirmUnchangedConfirmed'] ?? true),
                'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['noLivePlanConfirmRolloutConfirmed'] ?? true),
                'free_publication_locked_confirmed' => (bool) ($options['freePublicationLockedConfirmed'] ?? true),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C164_OPERATOR_APPROVED_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_CONTROLLED_EVIDENCE_ONLY'),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-18T00:00:00+00:00'),
            ]
        );
    }

    private function mutateExecutionAndExecute(callable $mutator, string $name): array
    {
        $execution = json_decode((string) file_get_contents(self::C164_EXECUTION_ARTIFACT), true);
        $execution = $mutator(is_array($execution) ? $execution : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c164-result-review-exec-'.substr(sha1($name), 0, 12).'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($execution, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'c164ExecutionArtifact' => $path,
            'expectedC164ExecutionHash' => (string) ($execution['artifact_hash'] ?? ''),
            'expectedC164ExecutionFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function mutateControlledCompletionAndExecute(callable $mutator, string $name): array
    {
        $completion = json_decode((string) file_get_contents(self::CONTROLLED_COMPLETION), true);
        $completion = $mutator(is_array($completion) ? $completion : []);
        $path = 'storage/app/watchlist/output/.tmp-c164-plan-confirm-completion-post-handoff-activation-completion-result-review-controlled-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($completion, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'controlledCompletion' => $path,
            'expectedControlledCompletionHash' => (string) ($completion['controlled_completion_hash'] ?? ''),
            'expectedControlledCompletionFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function duplicateTopLevelKeyFixture(string $source, string $name, string $duplicateKey): string
    {
        $raw = (string) file_get_contents($source);
        $path = 'storage/app/watchlist/backtest/.tmp-c164-plan-confirm-completion-post-handoff-activation-completion-result-review-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, preg_replace('/\{/', '{"'.$duplicateKey.'":"DUPLICATE_CASE_KEY",', $raw, 1));

        return $path;
    }

    private function setValueAt(array &$source, array $path, $value): void
    {
        $current = &$source;
        foreach ($path as $index => $segment) {
            if ($index === count($path) - 1) {
                $current[$segment] = $value;
                return;
            }
            if (! isset($current[$segment]) || ! is_array($current[$segment])) {
                $current[$segment] = [];
            }
            $current = &$current[$segment];
        }
    }

    private function readJson(string $path): array
    {
        $decoded = json_decode((string) file_get_contents($path), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function cleanupC164TemporaryArtifacts(): void
    {
        foreach ([
            'storage/app/watchlist/backtest/c164-*completion-result-review*-test.json',
            'storage/app/watchlist/backtest/c164-*completion-result*-test.json',
            'storage/app/watchlist/backtest/c164-*result-review*-negative-*.json',
            'storage/app/watchlist/backtest/c164-*negative-*-test.json',
            'storage/app/watchlist/backtest/c164-*missing-*-test.json',
            'storage/app/watchlist/backtest/c164-*mismatch-*-test.json',
            'storage/app/watchlist/backtest/c164-*invalid-*-test.json',
            'storage/app/watchlist/backtest/.tmp-c164*.json',
            'storage/app/watchlist/output/.tmp-c164*.json',
        ] as $pattern) {
            foreach ((array) glob($pattern) as $path) {
                if (is_file($path)) {
                    @unlink($path);
                }
            }
        }
    }
}

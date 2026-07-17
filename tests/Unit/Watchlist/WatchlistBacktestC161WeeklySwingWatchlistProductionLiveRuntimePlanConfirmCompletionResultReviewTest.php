<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC161WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionResultReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC161WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionResultReviewTest extends TestCase
{
    private const C161_EXECUTION_ARTIFACT = 'storage/app/watchlist/backtest/c161-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-execution.json';
    private const C161_EXECUTION_HASH = '6df2b8f868fef76a0320aa18e0706bcf8dd5cc4f';
    private const C161_EXECUTION_SHA1 = 'BB9845B704FAD0B7C280182B206F6301BA34562C';
    private const CONTROLLED_COMPLETION = 'storage/app/watchlist/output/c161-weekly-swing-watchlist-controlled-plan-confirm-completion.json';
    private const CONTROLLED_COMPLETION_HASH = 'e9862d9e7738d0558f107d978f329f97f14b3520';
    private const CONTROLLED_COMPLETION_SHA1 = 'AB9FC9F714339B78D68132222AC8C398BE7EE1B3';
    private const PASS_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_RESULT_REVIEW_PASSED_READY_FOR_PLAN_CONFIRM_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_PRIMARY_AND_BACKUP';
    private const NEXT_OPERATOR = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_OPERATOR_GO_NO_GO_REVIEW';

    private string $output;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c161-plan-confirm-completion-result-review.json';
        $this->cleanupC161TemporaryArtifacts();
        @unlink($this->output);
    }

    protected function tearDown(): void
    {
        @unlink($this->output);
        foreach ($this->tmpFiles as $path) {
            @unlink($path);
        }
        $this->cleanupC161TemporaryArtifacts();
        parent::tearDown();
    }

    public function test_c161_completion_result_review_passes_and_keeps_same_topic_number_for_operator_review(): void
    {
        $result = $this->runService();

        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_RESULT_REVIEW', $result['run_code']);
        $this->assertSame('PR-62 / C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_RESULT_REVIEW', $result['phase_label']);
        $this->assertSame('C161_PLAN_CONFIRM_COMPLETION', $result['topic_code']);
        $this->assertSame('PLAN_CONFIRM_COMPLETION_RESULT_REVIEW', $result['topic_stage']);
        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame(self::PASS_STATUS, $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_result_review_pass']);
        $this->assertTrue($result['production_live_runtime_plan_confirm_completion_result_review_pass']);
        $this->assertTrue($result['weekly_swing_watchlist_plan_confirm_completion_result_reviewed']);
        $this->assertTrue($result['weekly_swing_watchlist_plan_confirm_completion_result_review_manifest_created']);
        $this->assertTrue($result['ready_for_weekly_swing_watchlist_plan_confirm_completion_operator_go_no_go_review']);
        $this->assertTrue($result['production_live_runtime_plan_confirm_completion_operator_go_no_go_review_allowed_next']);
        $this->assertTrue($result['weekly_swing_watchlist_plan_confirm_completion_operator_go_no_go_review_allowed_next']);
        $this->assertSame(self::NEXT_OPERATOR, $result['next_step_recommendation']);
        $this->assertSame(self::NEXT_OPERATOR, $result['planned_next_summary']['planned_next_review']);
        $this->assertFileExists($this->output);
    }

    public function test_c161_completion_result_review_records_dual_locks_and_sections(): void
    {
        $result = $this->runService();
        $run = $this->readJson($this->output);

        foreach ([
            'source_artifact_locks',
            'c161_execution_lock_validation_summary',
            'controlled_completion_lock_validation_summary',
            'c161_execution_carry_forward_summary',
            'controlled_completion_result_review_summary',
            'controlled_completion_integrity_summary',
            'publication_plan_confirm_safety_summary',
            'candidate_completion_result_scorecard',
            'operator_approval_validation_summary',
            'result_review_confirmation_summary',
            'temporary_negative_artifact_guard_summary',
            'documentation_hygiene_guard_summary',
            'progress_summary',
            'planned_next_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }

        $this->assertSame(self::C161_EXECUTION_HASH, $result['expected_c161_execution_hash']);
        $this->assertSame(self::C161_EXECUTION_HASH, $result['actual_c161_execution_hash']);
        $this->assertTrue($result['c161_execution_hash_match']);
        $this->assertSame(self::C161_EXECUTION_SHA1, $result['actual_c161_execution_file_sha1']);
        $this->assertTrue($result['c161_execution_file_sha1_match']);
        $this->assertSame(self::CONTROLLED_COMPLETION_HASH, $result['expected_controlled_completion_hash']);
        $this->assertSame(self::CONTROLLED_COMPLETION_HASH, $result['actual_controlled_completion_hash']);
        $this->assertTrue($result['controlled_completion_hash_match']);
        $this->assertSame(self::CONTROLLED_COMPLETION_SHA1, $result['actual_controlled_completion_file_sha1']);
        $this->assertTrue($result['controlled_completion_file_sha1_match']);
        $this->assertSame(2, $result['controlled_completion_record_count']);
    }

    public function test_c161_completion_result_review_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->runService(['operatorApproved' => false]);
        $missingReference = $this->runService(['approvalReference' => '']);

        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_RESULT_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingOperator['status']);
        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_RESULT_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingReference['status']);
    }

    public function test_c161_completion_result_review_rejects_missing_required_confirmations(): void
    {
        $resultReview = $this->runService(['resultReviewConfirmed' => false]);
        $controlledResult = $this->runService(['controlledCompletionResultConfirmed' => false]);
        $controlledOnly = $this->runService(['controlledCompletionOnlyConfirmed' => false]);
        $unchanged = $this->runService(['planConfirmUnchangedConfirmed' => false]);
        $noRollout = $this->runService(['noLivePlanConfirmRolloutConfirmed' => false]);
        $freeLock = $this->runService(['freePublicationLockedConfirmed' => false]);

        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_RESULT_REVIEW_REJECTED_RESULT_REVIEW_CONFIRMATION_MISSING', $resultReview['status']);
        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_RESULT_REVIEW_REJECTED_CONTROLLED_COMPLETION_RESULT_CONFIRMATION_MISSING', $controlledResult['status']);
        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_RESULT_REVIEW_REJECTED_CONTROLLED_COMPLETION_ONLY_CONFIRMATION_MISSING', $controlledOnly['status']);
        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_RESULT_REVIEW_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING', $unchanged['status']);
        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_RESULT_REVIEW_REJECTED_NO_LIVE_PLAN_CONFIRM_ROLLOUT_CONFIRMATION_MISSING', $noRollout['status']);
        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_RESULT_REVIEW_REJECTED_FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING', $freeLock['status']);
    }

    public function test_c161_completion_result_review_rejects_execution_lock_status_phase_or_next_mismatch(): void
    {
        $hashMismatch = $this->runService(['expectedC161ExecutionHash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedC161ExecutionFileSha1' => 'BADSHA1']);
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
            $execution['next_plan_confirm_completion_result_review_decision']['next_recommendation'] = 'BROKEN_NEXT';
            return $execution;
        }, 'next');

        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_RESULT_REVIEW_REJECTED_C161_EXECUTION_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_RESULT_REVIEW_REJECTED_C161_EXECUTION_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_RESULT_REVIEW_REJECTED_C161_EXECUTION_STATUS_MISMATCH', $status['status']);
        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_RESULT_REVIEW_REJECTED_C161_EXECUTION_PHASE_LABEL_MISMATCH', $phase['status']);
        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_RESULT_REVIEW_REJECTED_C161_EXECUTION_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
    }

    public function test_c161_completion_result_review_rejects_controlled_completion_lock_and_convert_mismatch(): void
    {
        $hashMismatch = $this->runService(['expectedControlledCompletionHash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedControlledCompletionFileSha1' => 'BADSHA1']);
        $duplicate = $this->duplicateTopLevelKeyFixture(self::CONTROLLED_COMPLETION, 'controlled-duplicate', 'Controlled_Completion_Hash');

        $duplicateResult = $this->runService([
            'controlledCompletion' => $duplicate,
            'expectedControlledCompletionFileSha1' => strtoupper(sha1((string) file_get_contents($duplicate))),
        ]);

        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_RESULT_REVIEW_REJECTED_CONTROLLED_COMPLETION_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_RESULT_REVIEW_REJECTED_CONTROLLED_COMPLETION_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_RESULT_REVIEW_REJECTED_CONTROLLED_COMPLETION_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION', $duplicateResult['status']);
    }

    /**
     * @dataProvider incompleteExecutionProvider
     */
    public function test_c161_completion_result_review_rejects_incomplete_execution_evidence(string $field, $value): void
    {
        $result = $this->mutateExecutionAndExecute(function (array $execution) use ($field, $value): array {
            $this->setValueAt($execution, explode('.', $field), $value);
            return $execution;
        }, 'execution-incomplete-'.str_replace('.', '-', $field));

        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_RESULT_REVIEW_REJECTED_C161_EXECUTION_INCOMPLETE', $result['status'], $field);
    }

    public function incompleteExecutionProvider(): array
    {
        return [
            ['weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_execution_pass', false],
            ['ready_for_weekly_swing_watchlist_plan_confirm_completion_result_review', false],
            ['weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed', false],
            ['weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created', false],
            ['primary_candidate_completion_controlled_executed', false],
            ['backup_candidate_completion_controlled_executed', false],
            ['c161_controlled_completion_only', false],
            ['topic_stage', 'PLAN_CONFIRM_COMPLETION_RESULT_REVIEW'],
            ['controlled_completion_checklist.result_review_required_next', false],
            ['next_plan_confirm_completion_result_review_decision.review_valid', false],
            ['next_plan_confirm_completion_result_review_decision.same_topic_c161_continues', false],
        ];
    }

    public function test_c161_completion_result_review_rejects_integrity_publication_plan_mutation_or_candidate_scope_change(): void
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

        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_RESULT_REVIEW_REJECTED_CONTROLLED_COMPLETION_INTEGRITY_MISMATCH', $state['status']);
        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_RESULT_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED', $published['status']);
        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_RESULT_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $candidate['status']);
    }

    public function test_c161_completion_result_review_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c161-completion-result-review-negative-remains-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_RESULT_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS', $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c161_completion_result_review_records_scorecard_safety_and_deterministic_hash(): void
    {
        $first = $this->runService(['createdAt' => '2026-07-16T00:00:00+00:00']);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c161-plan-confirm-completion-result-review-second.json';
        $this->tmpFiles[] = $secondOutput;
        $second = $this->runService(['output' => $secondOutput, 'createdAt' => '2026-07-16T00:00:00+00:00']);
        $scorecard = $first['candidate_completion_result_scorecard'];

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
        $this->assertTrue($scorecard[0]['plan_confirm_completion_result_reviewed']);
        $this->assertTrue($scorecard[1]['plan_confirm_completion_result_reviewed']);
        $this->assertFalse($scorecard[2]['plan_confirm_completion_result_reviewed']);
        $this->assertFalse($first['publication_plan_confirm_safety_summary']['weekly_swing_watchlist_official_output_published']);
        $this->assertFalse($first['publication_plan_confirm_safety_summary']['plan_confirm_mutated']);
        $this->assertFalse($first['publication_plan_confirm_safety_summary']['live_plan_confirm_rollout_executed']);
    }

    public function test_c161_completion_result_review_does_not_mutate_source_artifacts_or_config_defaults(): void
    {
        $beforeExecution = strtoupper(sha1((string) file_get_contents(self::C161_EXECUTION_ARTIFACT)));
        $beforeCompletion = strtoupper(sha1((string) file_get_contents(self::CONTROLLED_COMPLETION)));
        $beforeConfig = (string) file_get_contents('config/watchlist.php');

        $this->runService();

        $this->assertSame($beforeExecution, strtoupper(sha1((string) file_get_contents(self::C161_EXECUTION_ARTIFACT))));
        $this->assertSame($beforeCompletion, strtoupper(sha1((string) file_get_contents(self::CONTROLLED_COMPLETION))));
        $this->assertSame($beforeConfig, (string) file_get_contents('config/watchlist.php'));
    }

    private function runService(array $options = []): array
    {
        $service = new WatchlistBacktestC161WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionResultReviewService();

        return $service->execute(
            (string) ($options['c161ExecutionArtifact'] ?? self::C161_EXECUTION_ARTIFACT),
            (string) ($options['expectedC161ExecutionHash'] ?? self::C161_EXECUTION_HASH),
            (string) ($options['expectedC161ExecutionFileSha1'] ?? self::C161_EXECUTION_SHA1),
            (string) ($options['controlledCompletion'] ?? self::CONTROLLED_COMPLETION),
            (string) ($options['expectedControlledCompletionHash'] ?? self::CONTROLLED_COMPLETION_HASH),
            (string) ($options['expectedControlledCompletionFileSha1'] ?? self::CONTROLLED_COMPLETION_SHA1),
            (string) ($options['output'] ?? $this->output),
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'result_review_confirmed' => (bool) ($options['resultReviewConfirmed'] ?? true),
                'controlled_completion_result_confirmed' => (bool) ($options['controlledCompletionResultConfirmed'] ?? true),
                'controlled_completion_only_confirmed' => (bool) ($options['controlledCompletionOnlyConfirmed'] ?? true),
                'plan_confirm_unchanged_confirmed' => (bool) ($options['planConfirmUnchangedConfirmed'] ?? true),
                'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['noLivePlanConfirmRolloutConfirmed'] ?? true),
                'free_publication_locked_confirmed' => (bool) ($options['freePublicationLockedConfirmed'] ?? true),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C161_OPERATOR_APPROVED_PLAN_CONFIRM_COMPLETION_RESULT_REVIEW_CONTROLLED_EVIDENCE_ONLY'),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-16T00:00:00+00:00'),
            ]
        );
    }

    private function mutateExecutionAndExecute(callable $mutator, string $name): array
    {
        $execution = json_decode((string) file_get_contents(self::C161_EXECUTION_ARTIFACT), true);
        $execution = $mutator(is_array($execution) ? $execution : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c161-plan-confirm-completion-result-review-execution-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($execution, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'c161ExecutionArtifact' => $path,
            'expectedC161ExecutionHash' => (string) ($execution['artifact_hash'] ?? ''),
            'expectedC161ExecutionFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function mutateControlledCompletionAndExecute(callable $mutator, string $name): array
    {
        $completion = json_decode((string) file_get_contents(self::CONTROLLED_COMPLETION), true);
        $completion = $mutator(is_array($completion) ? $completion : []);
        $path = 'storage/app/watchlist/output/.tmp-c161-plan-confirm-completion-result-review-controlled-'.$name.'.json';
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
        $path = 'storage/app/watchlist/backtest/.tmp-c161-plan-confirm-completion-result-review-'.$name.'.json';
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

    private function cleanupC161TemporaryArtifacts(): void
    {
        foreach ((array) glob('storage/app/watchlist/backtest/c161-*test.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/.tmp-c161*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/output/.tmp-c161*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}

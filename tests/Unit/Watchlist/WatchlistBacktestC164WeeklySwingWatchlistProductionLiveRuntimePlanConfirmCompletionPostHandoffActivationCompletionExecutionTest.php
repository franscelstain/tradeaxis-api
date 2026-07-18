<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC164WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationCompletionExecutionService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC164WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationCompletionExecutionTest extends TestCase
{
    private const C164_BOUNDARY_ARTIFACT = 'storage/app/watchlist/backtest/c164-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-completion-boundary-review.json';
    private const C164_BOUNDARY_HASH = '997bb3cc6f5565da92438a2afaca441bb50977b4';
    private const C164_BOUNDARY_SHA1 = '2EBE74B5E40E53C60456A4110DF41A29B1D3E1A6';
    private const PASS_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_PASSED_COMPLETION_EXECUTED_READY_FOR_RESULT_REVIEW_PRIMARY_AND_BACKUP';
    private const NEXT_RESULT_REVIEW = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW';

    private string $output;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c164-plan-confirm-completion-post-handoff-activation-completion-execution.json';
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

    public function test_c164_completion_execution_passes_from_locked_boundary_and_keeps_next_inside_c164_result_review(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();

        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION', $result['run_code']);
        $this->assertSame('PR-82 / C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION', $result['phase_label']);
        $this->assertSame('C164_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION', $result['topic_code']);
        $this->assertSame('PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION', $result['topic_stage']);
        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame(self::PASS_STATUS, $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_pass']);
        $this->assertTrue($result['production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_pass']);
        $this->assertTrue($result['post_handoff_activation_completion_execution_completed']);
        $this->assertTrue($result['ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_result_review']);
        $this->assertSame(self::NEXT_RESULT_REVIEW, $result['next_step_recommendation']);
        $this->assertSame(self::NEXT_RESULT_REVIEW, $result['next_plan_confirm_completion_post_handoff_activation_completion_result_review_decision']['next_recommendation']);
        $this->assertTrue($result['next_plan_confirm_completion_post_handoff_activation_completion_result_review_decision']['same_topic_c164_continues']);
        $this->assertTrue($result['next_plan_confirm_completion_post_handoff_activation_completion_result_review_decision']['topic_number_must_not_advance_until_c164_finalization']);
        $this->assertSame($result['artifact_hash'], $run['artifact_hash']);
        $this->assertFileExists($this->output);
    }

    public function test_c164_completion_execution_locks_boundary_and_controlled_completion_artifacts(): void
    {
        $result = $this->runService();

        $this->assertSame(self::C164_BOUNDARY_HASH, $result['expected_c164_completion_boundary_hash']);
        $this->assertSame(self::C164_BOUNDARY_HASH, $result['actual_c164_completion_boundary_hash']);
        $this->assertTrue($result['c164_completion_boundary_hash_match']);
        $this->assertSame(self::C164_BOUNDARY_SHA1, $result['actual_c164_completion_boundary_file_sha1']);
        $this->assertTrue($result['c164_completion_boundary_file_sha1_match']);
        $this->assertTrue($result['c164_completion_boundary_lock_valid']);
        $this->assertTrue($result['c164_completion_boundary_review_valid']);
        $this->assertTrue($result['c164_completion_boundary_convert_from_json_pass']);
        $this->assertTrue($result['controlled_completion_lock_valid']);
        $this->assertSame('e9862d9e7738d0558f107d978f329f97f14b3520', $result['controlled_completion_hash']);
        $this->assertSame('AB9FC9F714339B78D68132222AC8C398BE7EE1B3', $result['controlled_completion_file_sha1']);
        $this->assertSame(2, $result['controlled_completion_record_count']);
    }

    public function test_c164_completion_execution_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->runService(['operatorApproved' => false]);
        $missingReference = $this->runService(['approvalReference' => '']);

        $expected = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_REJECTED_OPERATOR_APPROVAL_MISSING';
        $this->assertSame($expected, $missingOperator['status']);
        $this->assertSame($expected, $missingReference['status']);
    }

    public function test_c164_completion_execution_rejects_missing_required_confirmations(): void
    {
        $execution = $this->runService(['completionExecutionConfirmed' => false]);
        $boundaryCleared = $this->runService(['c164BoundaryClearedConfirmed' => false]);
        $completionBoundary = $this->runService(['postHandoffActivationCompletionBoundaryConfirmed' => false]);
        $controlledOnly = $this->runService(['controlledCompletionOnlyConfirmed' => false]);
        $unchanged = $this->runService(['planConfirmUnchangedConfirmed' => false]);
        $noRollout = $this->runService(['noLivePlanConfirmRolloutConfirmed' => false]);
        $freeLock = $this->runService(['freePublicationLockedConfirmed' => false]);

        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_REJECTED_COMPLETION_EXECUTION_CONFIRMATION_MISSING', $execution['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_REJECTED_C164_BOUNDARY_CLEARED_CONFIRMATION_MISSING', $boundaryCleared['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_REJECTED_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_CONFIRMATION_MISSING', $completionBoundary['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_REJECTED_CONTROLLED_COMPLETION_ONLY_CONFIRMATION_MISSING', $controlledOnly['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING', $unchanged['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_REJECTED_NO_LIVE_PLAN_CONFIRM_ROLLOUT_CONFIRMATION_MISSING', $noRollout['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_REJECTED_FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING', $freeLock['status']);
    }

    public function test_c164_completion_execution_rejects_boundary_lock_status_phase_or_next_mismatch(): void
    {
        $missing = $this->runService([
            'c164BoundaryArtifact' => 'storage/app/watchlist/backtest/.tmp-c164-source-missing.json',
            'expectedC164BoundaryHash' => 'missing',
            'expectedC164BoundaryFileSha1' => 'missing',
        ]);
        $hashMismatch = $this->runService(['expectedC164BoundaryHash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedC164BoundaryFileSha1' => 'BADSHA1']);
        $status = $this->mutateBoundaryAndExecute(function (array $boundary): array {
            $boundary['status'] = 'BROKEN_STATUS';
            return $boundary;
        }, 'status-broken');
        $phase = $this->mutateBoundaryAndExecute(function (array $boundary): array {
            $boundary['phase_label'] = 'BROKEN_PHASE';
            return $boundary;
        }, 'phase-broken');
        $next = $this->mutateBoundaryAndExecute(function (array $boundary): array {
            $boundary['next_step_recommendation'] = 'BROKEN_NEXT';
            $boundary['next_plan_confirm_completion_post_handoff_activation_completion_execution_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $boundary['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $boundary;
        }, 'next-broken');

        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_REJECTED_C164_COMPLETION_BOUNDARY_ARTIFACT_LOCK_MISMATCH', $missing['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_REJECTED_C164_COMPLETION_BOUNDARY_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_REJECTED_C164_COMPLETION_BOUNDARY_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_REJECTED_C164_COMPLETION_BOUNDARY_STATUS_MISMATCH', $status['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_REJECTED_C164_COMPLETION_BOUNDARY_PHASE_LABEL_MISMATCH', $phase['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_REJECTED_C164_COMPLETION_BOUNDARY_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
    }

    public function test_c164_completion_execution_rejects_boundary_convert_from_json_duplicate_top_level_keys(): void
    {
        $raw = (string) file_get_contents(self::C164_BOUNDARY_ARTIFACT);
        $path = 'storage/app/watchlist/backtest/.tmp-c164-completion-execution-boundary-duplicate-key.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, preg_replace('/\{/', "{\"Status\":\"DUPLICATE_CASE_KEY\",", $raw, 1));

        $result = $this->runService([
            'c164BoundaryArtifact' => $path,
            'expectedC164BoundaryHash' => self::C164_BOUNDARY_HASH,
            'expectedC164BoundaryFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);

        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_REJECTED_C164_COMPLETION_BOUNDARY_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION', $result['status']);
        $this->assertFalse($result['c164_completion_boundary_convert_from_json_pass']);
    }

    /**
     * @dataProvider boundaryStateMismatchProvider
     */
    public function test_c164_completion_execution_rejects_boundary_state_mismatch(string $field, $value): void
    {
        $result = $this->mutateBoundaryAndExecute(function (array $boundary) use ($field, $value): array {
            $this->setValueAt($boundary, explode('.', $field), $value);
            return $boundary;
        }, 'state-'.str_replace('.', '-', $field));

        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_REJECTED_C164_COMPLETION_BOUNDARY_STATE_INVALID', $result['status'], $field);
    }

    public function boundaryStateMismatchProvider(): array
    {
        return [
            ['weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_boundary_review_pass', false],
            ['post_handoff_activation_completion_boundary_cleared', false],
            ['ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_execution', false],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_boundary_manifest_created', false],
            ['primary_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_execution', false],
            ['backup_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_execution', false],
            ['a01_remains_comparator_only', false],
            ['c164_completion_boundary_review_only', false],
            ['topic_stage', 'PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION'],
            ['c164_completion_boundary_decision.review_pass', false],
            ['next_plan_confirm_completion_post_handoff_activation_completion_execution_decision.review_valid', false],
            ['next_plan_confirm_completion_post_handoff_activation_completion_execution_decision.same_topic_c164_continues', false],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_boundary_manifest.completion_boundary_artifact_only', false],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_boundary_manifest.ready_for_post_handoff_activation_completion_execution', false],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_boundary_checklist.artifact_only', false],
        ];
    }

    public function test_c164_completion_execution_rejects_publication_plan_mutation_candidate_or_function_scope_change(): void
    {
        $published = $this->mutateBoundaryAndExecute(function (array $boundary): array {
            $boundary['weekly_swing_watchlist_official_output_published'] = true;
            return $boundary;
        }, 'published');
        $planConfirm = $this->mutateBoundaryAndExecute(function (array $boundary): array {
            $boundary['plan_confirm_mutated'] = true;
            return $boundary;
        }, 'plan-confirm');
        $candidate = $this->mutateBoundaryAndExecute(function (array $boundary): array {
            $boundary['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $boundary;
        }, 'candidate');
        $function = $this->mutateBoundaryAndExecute(function (array $boundary): array {
            $boundary['watchlist_function_used'] = 'UNLOCKED_WATCHLIST_FUNCTION';
            return $boundary;
        }, 'function-scope');
        $controlled = $this->mutateBoundaryAndExecute(function (array $boundary): array {
            $boundary['controlled_completion_hash'] = 'BROKEN_CONTROLLED_COMPLETION_HASH';
            return $boundary;
        }, 'controlled-lock');

        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED', $published['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED', $planConfirm['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_REJECTED_CANDIDATE_SCOPE_MISMATCH', $candidate['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_REJECTED_WATCHLIST_FUNCTION_SCOPE_MISMATCH', $function['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_REJECTED_CONTROLLED_COMPLETION_LOCK_MISMATCH', $controlled['status']);
    }

    public function test_c164_completion_execution_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c164-completion-execution-negative-remains-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS', $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c164_records_manifest_checklist_scorecard_and_safety_flags(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();
        $manifest = $result['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_execution_manifest'];
        $checklist = $result['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_execution_checklist'];
        $scorecard = $result['c164_candidate_post_handoff_activation_completion_execution_scorecard'];

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        foreach ([
            'source_artifact_locks',
            'c164_completion_boundary_lock_validation_summary',
            'c164_completion_boundary_carry_forward_summary',
            'controlled_completion_lock_validation_summary',
            'plan_confirm_completion_post_handoff_activation_completion_execution_guard_summary',
            'candidate_scope_freeze_summary',
            'watchlist_function_scope_summary',
            'operator_approval_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'c164_completion_execution_decision',
            'next_plan_confirm_completion_post_handoff_activation_completion_result_review_decision',
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_execution_manifest',
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_execution_checklist',
            'c164_candidate_post_handoff_activation_completion_execution_scorecard',
            'publication_plan_confirm_safety_summary',
            'documentation_hygiene_guard_summary',
            'progress_summary',
            'planned_next_summary',
            'diagnostics',
            'failure_attribution_summary',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }

        $this->assertTrue($manifest['manifest_created']);
        $this->assertTrue($manifest['completion_execution_artifact_only']);
        $this->assertTrue($manifest['ready_for_plan_confirm_completion_post_handoff_activation_completion_result_review']);
        $this->assertFalse($manifest['completion_execution_used_for_free_publication']);
        $this->assertFalse($manifest['completion_execution_used_for_plan_confirm_mutation']);
        $this->assertFalse($manifest['completion_execution_used_for_live_plan_confirm_rollout']);
        $this->assertSame([], $manifest['official_weekly_swing_stock_recommendations']);
        $this->assertTrue($checklist['completion_execution_only']);
        $this->assertTrue($checklist['completion_result_review_required_next']);
        $this->assertTrue($scorecard[0]['completion_execution_completed']);
        $this->assertTrue($scorecard[1]['completion_execution_completed']);
        $this->assertFalse($scorecard[2]['completion_execution_completed']);

        foreach ([
            'weekly_swing_watchlist_official_output_published',
            'weekly_swing_watchlist_publication_allowed',
            'weekly_swing_watchlist_unrestricted_publication_allowed',
            'plan_confirm_mutation_allowed',
            'plan_confirm_mutated',
            'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed',
            'live_plan_confirm_rollout_executed',
        ] as $flag) {
            $this->assertFalse($result[$flag], $flag);
        }
    }

    public function test_c164_completion_execution_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $first = $this->runService(['createdAt' => '2026-07-18T00:00:00+00:00']);
        $second = $this->runService(['createdAt' => '2026-07-18T00:00:00+00:00']);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
    }

    private function runService(array $options = []): array
    {
        $service = new WatchlistBacktestC164WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationCompletionExecutionService();

        return $service->execute(
            (string) ($options['c164BoundaryArtifact'] ?? self::C164_BOUNDARY_ARTIFACT),
            (string) ($options['expectedC164BoundaryHash'] ?? self::C164_BOUNDARY_HASH),
            (string) ($options['expectedC164BoundaryFileSha1'] ?? self::C164_BOUNDARY_SHA1),
            (string) ($options['output'] ?? $this->output),
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'completion_execution_confirmed' => (bool) ($options['completionExecutionConfirmed'] ?? true),
                'c164_boundary_cleared_confirmed' => (bool) ($options['c164BoundaryClearedConfirmed'] ?? true),
                'post_handoff_activation_completion_boundary_confirmed' => (bool) ($options['postHandoffActivationCompletionBoundaryConfirmed'] ?? true),
                'controlled_completion_only_confirmed' => (bool) ($options['controlledCompletionOnlyConfirmed'] ?? true),
                'plan_confirm_unchanged_confirmed' => (bool) ($options['planConfirmUnchangedConfirmed'] ?? true),
                'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['noLivePlanConfirmRolloutConfirmed'] ?? true),
                'free_publication_locked_confirmed' => (bool) ($options['freePublicationLockedConfirmed'] ?? true),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C164_OPERATOR_APPROVED_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_ONLY'),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-18T00:00:00+00:00'),
            ]
        );
    }

    private function mutateBoundaryAndExecute(callable $mutator, string $name): array
    {
        $boundary = json_decode((string) file_get_contents(self::C164_BOUNDARY_ARTIFACT), true);
        $boundary = $mutator(is_array($boundary) ? $boundary : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c164-source-boundary-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($boundary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'c164BoundaryArtifact' => $path,
            'expectedC164BoundaryHash' => (string) ($boundary['artifact_hash'] ?? ''),
            'expectedC164BoundaryFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
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

    private function readOutput(): array
    {
        return json_decode((string) file_get_contents($this->output), true);
    }

    private function cleanupC164TemporaryArtifacts(): void
    {
        foreach ([
            'storage/app/watchlist/backtest/c164-*completion-execution*-test.json',
            'storage/app/watchlist/backtest/c164-*completion*-execution*-test.json',
            'storage/app/watchlist/backtest/c164-*negative-*-test.json',
            'storage/app/watchlist/backtest/c164-*missing-*-test.json',
            'storage/app/watchlist/backtest/c164-*mismatch-*-test.json',
            'storage/app/watchlist/backtest/c164-*invalid-*-test.json',
            'storage/app/watchlist/backtest/.tmp-c164-*negative-*-test.json',
        ] as $pattern) {
            foreach ((array) glob($pattern) as $path) {
                if (is_file($path)) {
                    @unlink($path);
                }
            }
        }
    }
}

<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC164WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationCompletionOperatorGoNoGoReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC164WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationCompletionOperatorGoNoGoReviewTest extends TestCase
{
    private const C164_RESULT_REVIEW_ARTIFACT = 'storage/app/watchlist/backtest/c164-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-completion-result-review.json';
    private const C164_RESULT_REVIEW_HASH = '2cf044eb2b860bf165897585d52f5d51783066e3';
    private const C164_RESULT_REVIEW_SHA1 = 'B6909750A1EDD977067460ABD8D992175B9EBE42';
    private const GO_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP_READY_FOR_GO_DECISION_FINALIZATION_REVIEW';
    private const NO_GO_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_COMPLETED_NO_GO_POST_HANDOFF_ACTIVATION_COMPLETION_PROGRESSION_STOPPED';
    private const HOLD_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_COMPLETED_HOLD_POST_HANDOFF_ACTIVATION_COMPLETION_PROGRESSION_DEFERRED';
    private const NEXT_FINALIZATION = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW';

    private string $output;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c164-post-handoff-activation-completion-operator-go-no-go-review.json';
        $this->cleanupTemporaryArtifacts();
        @unlink($this->output);
    }

    protected function tearDown(): void
    {
        @unlink($this->output);
        foreach ($this->tmpFiles as $path) {
            @unlink($path);
        }
        $this->cleanupTemporaryArtifacts();
        parent::tearDown();
    }

    public function test_c164_operator_go_passes_and_keeps_same_topic_finalization_next(): void
    {
        $result = $this->runService();

        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW', $result['run_code']);
        $this->assertSame('PR-84 / C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW', $result['phase_label']);
        $this->assertSame('C164_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION', $result['topic_code']);
        $this->assertSame('PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW', $result['topic_stage']);
        $this->assertSame(self::GO_STATUS, $result['status']);
        $this->assertSame(self::GO_STATUS, $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_review_pass']);
        $this->assertTrue($result['production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_review_pass']);
        $this->assertTrue($result['operator_decision_recorded']);
        $this->assertSame('GO', $result['operator_decision']);
        $this->assertTrue($result['operator_go_decision']);
        $this->assertTrue($result['operator_decision_confirmed']);
        $this->assertTrue($result['ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_review']);
        $this->assertTrue($result['production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_review_allowed_next']);
        $this->assertTrue($result['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_review_allowed_next']);
        $this->assertTrue($result['c164_result_review_lock_valid']);
        $this->assertTrue($result['c164_plan_confirm_completion_post_handoff_activation_completion_result_review_valid']);
        $this->assertSame(self::NEXT_FINALIZATION, $result['next_step_recommendation']);
        $this->assertFileExists($this->output);
    }

    public function test_c164_operator_records_no_go_and_hold_without_opening_finalization(): void
    {
        $noGo = $this->runService([
            'operatorDecision' => 'NO_GO',
            'decisionReason' => 'Operator stops C164 post-handoff activation completion progression after result review.',
        ]);
        $hold = $this->runService([
            'operatorDecision' => 'HOLD',
            'decisionReason' => 'Operator defers C164 post-handoff activation completion finalization until a scheduled review window.',
        ]);

        $this->assertSame(self::NO_GO_STATUS, $noGo['status']);
        $this->assertSame('NO_GO', $noGo['operator_decision']);
        $this->assertTrue($noGo['operator_no_go_decision']);
        $this->assertFalse($noGo['ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_review']);
        $this->assertTrue($noGo['post_handoff_activation_completion_stopped_no_go']);
        $this->assertSame('C164_NO_GO_CLOSE_POST_HANDOFF_ACTIVATION_COMPLETION_WITH_PLAN_CONFIRM_UNCHANGED', $noGo['next_step_recommendation']);

        $this->assertSame(self::HOLD_STATUS, $hold['status']);
        $this->assertSame('HOLD', $hold['operator_decision']);
        $this->assertTrue($hold['operator_hold_decision']);
        $this->assertFalse($hold['ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_review']);
        $this->assertTrue($hold['post_handoff_activation_completion_deferred_hold']);
        $this->assertSame('C164_HOLD_KEEP_POST_HANDOFF_ACTIVATION_COMPLETION_LOCKED_UNTIL_OPERATOR_WINDOW', $hold['next_step_recommendation']);
    }

    public function test_c164_operator_rejects_missing_approval_invalid_unconfirmed_or_unexplained_decision(): void
    {
        $missingOperator = $this->runService(['operatorApproved' => false]);
        $missingReference = $this->runService(['approvalReference' => '']);
        $invalid = $this->runService(['operatorDecision' => 'MAYBE']);
        $unconfirmed = $this->runService(['operatorDecisionConfirmed' => false]);
        $missingReason = $this->runService(['decisionReason' => '']);

        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingOperator['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingReference['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_DECISION_INVALID', $invalid['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_DECISION_NOT_CONFIRMED', $unconfirmed['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_DECISION_REASON_MISSING', $missingReason['status']);
    }

    public function test_c164_operator_rejects_result_review_lock_status_phase_or_next_mismatch(): void
    {
        $missing = $this->runService([
            'c164ResultReviewArtifact' => 'storage/app/watchlist/backtest/.tmp-c164-operator-source-missing.json',
            'expectedC164ResultReviewHash' => 'missing',
            'expectedC164ResultReviewFileSha1' => 'missing',
        ]);
        $hashMismatch = $this->runService(['expectedC164ResultReviewHash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedC164ResultReviewFileSha1' => 'BADSHA1']);
        $status = $this->mutateResultReviewAndExecute(function (array $review): array {
            $review['status'] = 'BROKEN_STATUS';
            return $review;
        }, 'status');
        $phase = $this->mutateResultReviewAndExecute(function (array $review): array {
            $review['phase_label'] = 'BROKEN_PHASE';
            return $review;
        }, 'phase');
        $next = $this->mutateResultReviewAndExecute(function (array $review): array {
            $review['next_step_recommendation'] = 'BROKEN_NEXT';
            $review['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            $review['next_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_decision']['next_recommendation'] = 'BROKEN_NEXT';
            return $review;
        }, 'next');

        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C164_RESULT_REVIEW_ARTIFACT_LOCK_MISMATCH', $missing['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C164_RESULT_REVIEW_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C164_RESULT_REVIEW_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C164_RESULT_REVIEW_STATUS_MISMATCH', $status['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C164_RESULT_REVIEW_PHASE_LABEL_MISMATCH', $phase['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C164_RESULT_REVIEW_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
    }

    public function test_c164_operator_rejects_convert_from_json_duplicate_top_level_keys(): void
    {
        $raw = (string) file_get_contents(self::C164_RESULT_REVIEW_ARTIFACT);
        $path = 'storage/app/watchlist/backtest/.tmp-c164-operator-source-duplicate-key.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, preg_replace('/\{/', "{\"Run_Code\":\"DUPLICATE_CASE_KEY\",", $raw, 1));

        $result = $this->runService([
            'c164ResultReviewArtifact' => $path,
            'expectedC164ResultReviewFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);

        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C164_RESULT_REVIEW_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION', $result['status']);
        $this->assertFalse($result['c164_result_review_convert_from_json_pass']);
    }

    /**
     * @dataProvider c164ResultReviewIncompleteProvider
     */
    public function test_c164_operator_rejects_incomplete_result_review_evidence(string $field, $value): void
    {
        $result = $this->mutateResultReviewAndExecute(function (array $review) use ($field, $value): array {
            $this->setValueAt($review, explode('.', $field), $value);
            return $review;
        }, 'incomplete-'.str_replace('.', '-', $field));

        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C164_RESULT_REVIEW_INCOMPLETE', $result['status'], $field);
    }

    public function c164ResultReviewIncompleteProvider(): array
    {
        return [
            ['weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_result_review_pass', false],
            ['ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_review', false],
            ['controlled_completion_lock_valid', false],
            ['controlled_completion_integrity_valid', false],
            ['result_review_confirmed', false],
            ['primary_candidate_completion_result_reviewed', false],
            ['backup_candidate_completion_result_reviewed', false],
            ['comparator_candidate_completion_result_reviewed', true],
            ['c164_completion_result_review_only', false],
            ['c164_not_publication', false],
            ['c164_completion_result_review_decision.review_valid', false],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_result_review_manifest.result_review_artifact_only', false],
        ];
    }

    public function test_c164_operator_rejects_publication_mutation_candidate_or_watchlist_function_scope_change(): void
    {
        $published = $this->mutateResultReviewAndExecute(function (array $review): array {
            $review['weekly_swing_watchlist_official_output_published'] = true;
            return $review;
        }, 'published');
        $planConfirm = $this->mutateResultReviewAndExecute(function (array $review): array {
            $review['plan_confirm_mutated'] = true;
            return $review;
        }, 'plan-confirm');
        $candidate = $this->mutateResultReviewAndExecute(function (array $review): array {
            $review['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $review;
        }, 'candidate-primary');
        $watchlistFunction = $this->mutateResultReviewAndExecute(function (array $review): array {
            $review['watchlist_function_used'] = 'BROKEN_FUNCTION';
            return $review;
        }, 'watchlist-function');

        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED', $published['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED', $planConfirm['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $candidate['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_WATCHLIST_FUNCTION_SCOPE_MISMATCH', $watchlistFunction['status']);
    }

    public function test_c164_operator_records_sections_manifest_scorecard_and_safety_flags_false(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();
        $manifest = $result['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_manifest'];
        $scorecard = $result['c164_candidate_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_scorecard'];

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $this->assertSame(self::C164_RESULT_REVIEW_HASH, $result['expected_c164_result_review_hash']);
        $this->assertSame(self::C164_RESULT_REVIEW_HASH, $result['actual_c164_result_review_hash']);
        $this->assertTrue($result['c164_result_review_hash_match']);
        $this->assertSame(self::C164_RESULT_REVIEW_SHA1, $result['actual_c164_result_review_file_sha1']);
        $this->assertTrue($result['c164_result_review_file_sha1_match']);
        $this->assertSame('CONTROLLED_WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATION', $result['watchlist_function_used']);
        $this->assertSame(self::NEXT_FINALIZATION, $result['next_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_decision']['next_recommendation']);

        foreach ([
            'source_artifact_locks',
            'c164_result_review_lock_validation_summary',
            'c164_plan_confirm_completion_post_handoff_activation_completion_result_review_carry_forward_summary',
            'watchlist_function_scope_summary',
            'publication_plan_confirm_safety_summary',
            'candidate_scope_freeze_summary',
            'operator_decision_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'c164_post_handoff_activation_completion_operator_go_no_go_decision',
            'next_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_decision',
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_manifest',
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_checklist',
            'c164_candidate_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_scorecard',
            'documentation_hygiene_guard_summary',
            'progress_summary',
            'planned_next_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }

        $this->assertTrue($manifest['operator_go_no_go_artifact_only']);
        $this->assertTrue($manifest['operator_go_no_go_review_pass']);
        $this->assertTrue($manifest['ready_for_go_decision_finalization_review']);
        $this->assertFalse($manifest['official_output_published']);
        $this->assertFalse($manifest['operator_go_no_go_used_for_publication']);
        $this->assertFalse($manifest['operator_go_no_go_used_for_plan_confirm_mutation']);
        $this->assertFalse($manifest['operator_go_no_go_used_for_live_plan_confirm_rollout']);
        $this->assertSame([], $manifest['official_weekly_swing_stock_recommendations']);
        $this->assertTrue($scorecard[0]['ready_for_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_review']);
        $this->assertTrue($scorecard[1]['ready_for_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_review']);
        $this->assertFalse($scorecard[2]['ready_for_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_review']);

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

    public function test_c164_operator_output_is_deterministic_and_does_not_mutate_source_or_config(): void
    {
        $beforeResultReview = strtoupper(sha1((string) file_get_contents(self::C164_RESULT_REVIEW_ARTIFACT)));
        $beforeConfig = (string) file_get_contents('config/watchlist.php');
        $first = $this->runService(['createdAt' => '2026-07-18T00:00:00+00:00']);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c164-post-handoff-activation-completion-operator-go-no-go-review-second.json';
        $this->tmpFiles[] = $secondOutput;
        $second = $this->runService([
            'output' => $secondOutput,
            'createdAt' => '2026-07-18T00:00:00+00:00',
        ]);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
        $this->assertSame($beforeResultReview, strtoupper(sha1((string) file_get_contents(self::C164_RESULT_REVIEW_ARTIFACT))));
        $this->assertSame($beforeConfig, (string) file_get_contents('config/watchlist.php'));
    }

    public function test_c164_operator_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c164-operator-go-no-go-negative-remains-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS', $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    private function runService(array $options = []): array
    {
        $service = new WatchlistBacktestC164WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationCompletionOperatorGoNoGoReviewService();

        return $service->execute(
            (string) ($options['c164ResultReviewArtifact'] ?? self::C164_RESULT_REVIEW_ARTIFACT),
            (string) ($options['expectedC164ResultReviewHash'] ?? self::C164_RESULT_REVIEW_HASH),
            (string) ($options['expectedC164ResultReviewFileSha1'] ?? self::C164_RESULT_REVIEW_SHA1),
            (string) ($options['output'] ?? $this->output),
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'operator_decision_confirmed' => (bool) ($options['operatorDecisionConfirmed'] ?? true),
                'operator_decision' => (string) ($options['operatorDecision'] ?? 'GO'),
                'decision_reason' => (string) ($options['decisionReason'] ?? 'Operator approves C164 post-handoff activation completion result for same-topic go decision finalization.'),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C164_OPERATOR_APPROVED_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_GO'),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-18T00:00:00+00:00'),
            ]
        );
    }

    private function mutateResultReviewAndExecute(callable $mutator, string $name): array
    {
        $review = json_decode((string) file_get_contents(self::C164_RESULT_REVIEW_ARTIFACT), true);
        $review = $mutator(is_array($review) ? $review : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c164-operator-result-review-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($review, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'c164ResultReviewArtifact' => $path,
            'expectedC164ResultReviewHash' => (string) ($review['artifact_hash'] ?? ''),
            'expectedC164ResultReviewFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
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
        $decoded = json_decode((string) file_get_contents($this->output), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function cleanupTemporaryArtifacts(): void
    {
        foreach ((array) glob('storage/app/watchlist/backtest/c164-*operator-go-no-go*-test.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/c164-*completion-operator*-test.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/.tmp-c164-operator*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/.tmp-c164-post-handoff-activation-completion-operator-go-no-go-review*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}

<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC164WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationCompletionBoundaryReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC164WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationCompletionBoundaryReviewTest extends TestCase
{
    private const C163_FINALIZATION_ARTIFACT = 'storage/app/watchlist/backtest/c163-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-go-decision-finalization-review.json';
    private const C163_FINALIZATION_HASH = 'e7a4e300eea57aa5f28a87e5cceb297fd92c195a';
    private const C163_FINALIZATION_SHA1 = '450DC99CAC858CBE08D4E2FB32BC4D9D2F1845B9';
    private const PASS_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_PASSED_BOUNDARY_CLEARED_READY_FOR_COMPLETION_EXECUTION_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const BOUNDARY_CONFIRMATION_MISSING_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_REJECTED_COMPLETION_BOUNDARY_CONFIRMATION_MISSING';
    private const C163_TOPIC_COMPLETE_MISSING_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_REJECTED_C163_TOPIC_COMPLETE_CONFIRMATION_MISSING';
    private const POST_HANDOFF_ACTIVATION_CLOSED_MISSING_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_REJECTED_POST_HANDOFF_ACTIVATION_CLOSED_CONFIRMATION_MISSING';
    private const PLAN_CONFIRM_UNCHANGED_MISSING_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING';
    private const NO_LIVE_ROLLOUT_MISSING_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_REJECTED_NO_LIVE_PLAN_CONFIRM_ROLLOUT_CONFIRMATION_MISSING';
    private const FREE_PUBLICATION_LOCK_MISSING_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_REJECTED_FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING';
    private const LOCK_MISMATCH_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_REJECTED_C163_GO_DECISION_FINALIZATION_ARTIFACT_LOCK_MISMATCH';
    private const SHA1_MISMATCH_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_REJECTED_C163_GO_DECISION_FINALIZATION_FILE_SHA1_LOCK_MISMATCH';
    private const STATE_INVALID_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_REJECTED_C163_GO_DECISION_FINALIZATION_STATE_INVALID';
    private const NEXT_C164_EXECUTION = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION';

    private string $output;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c164-plan-confirm-completion-post-handoff-activation-completion-boundary-review.json';
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

    public function test_c164_completion_boundary_passes_after_locked_c163_finalization_and_keeps_next_inside_c164_execution(): void
    {
        $result = $this->runService();

        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW', $result['run_code']);
        $this->assertSame('PR-81 / C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW', $result['phase_label']);
        $this->assertSame('C164_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION', $result['topic_code']);
        $this->assertSame('PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW', $result['topic_stage']);
        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame(self::PASS_STATUS, $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_boundary_review_pass']);
        $this->assertTrue($result['production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_boundary_review_pass']);
        $this->assertTrue($result['post_handoff_activation_completion_boundary_cleared']);
        $this->assertTrue($result['completion_boundary_cleared']);
        $this->assertTrue($result['completion_boundary_confirmed']);
        $this->assertTrue($result['c163_topic_complete_confirmed']);
        $this->assertTrue($result['post_handoff_activation_closed_confirmed']);
        $this->assertSame('BOUNDARY_CLEARED_GO', $result['boundary_go_decision']);
        $this->assertTrue($result['ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_execution']);
        $this->assertSame(self::NEXT_C164_EXECUTION, $result['next_step_recommendation']);
        $this->assertSame(self::NEXT_C164_EXECUTION, $result['next_plan_confirm_completion_post_handoff_activation_completion_execution_decision']['next_recommendation']);
        $this->assertTrue($result['next_plan_confirm_completion_post_handoff_activation_completion_execution_decision']['same_topic_c164_continues']);
        $this->assertTrue($result['next_plan_confirm_completion_post_handoff_activation_completion_execution_decision']['topic_number_must_not_advance_until_c164_finalization']);
        $this->assertStringStartsWith('C164_', $result['next_step_recommendation']);
        $this->assertFileExists($this->output);
    }

    public function test_c164_completion_boundary_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->runService(['operatorApproved' => false]);
        $missingReference = $this->runService(['approvalReference' => '']);

        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingOperator['status']);
        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingReference['status']);
    }

    public function test_c164_completion_boundary_rejects_missing_required_confirmations(): void
    {
        $boundary = $this->runService(['completionBoundaryConfirmed' => false]);
        $topic = $this->runService(['c163TopicCompleteConfirmed' => false]);
        $closed = $this->runService(['postHandoffActivationClosedConfirmed' => false]);
        $unchanged = $this->runService(['planConfirmUnchangedConfirmed' => false]);
        $noRollout = $this->runService(['noLivePlanConfirmRolloutConfirmed' => false]);
        $freeLock = $this->runService(['freePublicationLockedConfirmed' => false]);

        $this->assertSame(self::BOUNDARY_CONFIRMATION_MISSING_STATUS, $boundary['status']);
        $this->assertSame(self::C163_TOPIC_COMPLETE_MISSING_STATUS, $topic['status']);
        $this->assertSame(self::POST_HANDOFF_ACTIVATION_CLOSED_MISSING_STATUS, $closed['status']);
        $this->assertSame(self::PLAN_CONFIRM_UNCHANGED_MISSING_STATUS, $unchanged['status']);
        $this->assertSame(self::NO_LIVE_ROLLOUT_MISSING_STATUS, $noRollout['status']);
        $this->assertSame(self::FREE_PUBLICATION_LOCK_MISSING_STATUS, $freeLock['status']);
    }

    public function test_c164_completion_boundary_rejects_missing_or_mismatched_c163_finalization_lock(): void
    {
        $missing = $this->runService([
            'c163FinalizationArtifact' => 'storage/app/watchlist/backtest/.tmp-c164-source-missing.json',
            'expectedC163FinalizationHash' => 'missing',
            'expectedC163FinalizationFileSha1' => 'missing',
        ]);
        $hashMismatch = $this->runService(['expectedC163FinalizationHash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedC163FinalizationFileSha1' => 'BADSHA1']);

        $this->assertSame(self::LOCK_MISMATCH_STATUS, $missing['status']);
        $this->assertSame(self::LOCK_MISMATCH_STATUS, $hashMismatch['status']);
        $this->assertSame(self::SHA1_MISMATCH_STATUS, $shaMismatch['status']);
    }

    public function test_c164_completion_boundary_rejects_c163_status_phase_or_next_recommendation_mismatch(): void
    {
        $status = $this->mutateC163FinalizationAndExecute(function (array $c163): array {
            $c163['status'] = 'BROKEN_STATUS';
            return $c163;
        }, 'status-broken');
        $phase = $this->mutateC163FinalizationAndExecute(function (array $c163): array {
            $c163['phase_label'] = 'BROKEN_PHASE';
            return $c163;
        }, 'phase-broken');
        $next = $this->mutateC163FinalizationAndExecute(function (array $c163): array {
            $c163['next_step_recommendation'] = 'BROKEN_NEXT';
            $c163['next_plan_confirm_completion_post_handoff_activation_completion_boundary_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c163['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $c163;
        }, 'next-broken');

        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_REJECTED_C163_GO_DECISION_FINALIZATION_STATUS_MISMATCH', $status['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_REJECTED_C163_GO_DECISION_FINALIZATION_PHASE_LABEL_MISMATCH', $phase['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_REJECTED_C163_GO_DECISION_FINALIZATION_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
    }

    public function test_c164_completion_boundary_rejects_c163_convert_from_json_duplicate_top_level_keys(): void
    {
        $raw = (string) file_get_contents(self::C163_FINALIZATION_ARTIFACT);
        $path = 'storage/app/watchlist/backtest/.tmp-c164-source-c163-finalization-duplicate-key.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, preg_replace('/\{/', "{\"Status\":\"DUPLICATE_CASE_KEY\",", $raw, 1));

        $result = $this->runService([
            'c163FinalizationArtifact' => $path,
            'expectedC163FinalizationHash' => self::C163_FINALIZATION_HASH,
            'expectedC163FinalizationFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);

        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_REJECTED_C163_GO_DECISION_FINALIZATION_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION', $result['status']);
        $this->assertFalse($result['c163_go_decision_finalization_convert_from_json_pass']);
    }

    /**
     * @dataProvider c163FinalizationStateMismatchProvider
     */
    public function test_c164_completion_boundary_rejects_c163_finalization_state_mismatch(string $field, $value): void
    {
        $result = $this->mutateC163FinalizationAndExecute(function (array $c163) use ($field, $value): array {
            $this->setValueAt($c163, explode('.', $field), $value);
            return $c163;
        }, 'state-'.str_replace('.', '-', $field));

        $this->assertSame(self::STATE_INVALID_STATUS, $result['status'], $field);
    }

    public function c163FinalizationStateMismatchProvider(): array
    {
        return [
            ['weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_go_decision_finalization_review_pass', false],
            ['operator_decision', 'NO_GO'],
            ['operator_go_decision', false],
            ['go_decision_finalized', false],
            ['post_handoff_activation_closed', false],
            ['c163_topic_complete_after_finalization', false],
            ['ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_boundary_review', false],
            ['watchlist_function_primary_candidate_observed', false],
            ['watchlist_function_comparator_candidate_observed', true],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_go_decision_finalization_manifest_created', false],
            ['c163_go_decision_finalization_decision.review_valid', false],
            ['next_plan_confirm_completion_post_handoff_activation_completion_boundary_decision.review_valid', false],
            ['next_plan_confirm_completion_post_handoff_activation_completion_boundary_decision.same_topic_c163_complete', false],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_go_decision_finalization_manifest.ready_for_post_handoff_activation_completion_boundary_review', false],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_go_decision_finalization_manifest.go_decision_finalization_artifact_only', false],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_go_decision_finalization_manifest.go_decision_finalization_used_for_free_publication', true],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_go_decision_finalization_checklist.artifact_only', false],
        ];
    }

    public function test_c164_completion_boundary_rejects_publication_or_plan_confirm_mutation_from_c163_finalization(): void
    {
        $published = $this->mutateC163FinalizationAndExecute(function (array $c163): array {
            $c163['weekly_swing_watchlist_official_output_published'] = true;
            return $c163;
        }, 'published');
        $publicationAllowed = $this->mutateC163FinalizationAndExecute(function (array $c163): array {
            $c163['weekly_swing_watchlist_publication_allowed'] = true;
            return $c163;
        }, 'publication-allowed');
        $planConfirm = $this->mutateC163FinalizationAndExecute(function (array $c163): array {
            $c163['plan_confirm_mutated'] = true;
            return $c163;
        }, 'plan-confirm');
        $liveRollout = $this->mutateC163FinalizationAndExecute(function (array $c163): array {
            $c163['live_plan_confirm_rollout_executed'] = true;
            return $c163;
        }, 'live-rollout');

        $expected = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED';
        $this->assertSame($expected, $published['status']);
        $this->assertSame($expected, $publicationAllowed['status']);
        $this->assertSame($expected, $planConfirm['status']);
        $this->assertSame($expected, $liveRollout['status']);
    }

    public function test_c164_completion_boundary_rejects_candidate_scope_or_watchlist_function_scope_change(): void
    {
        $candidate = $this->mutateC163FinalizationAndExecute(function (array $c163): array {
            $c163['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $c163;
        }, 'candidate-primary');
        $a01 = $this->mutateC163FinalizationAndExecute(function (array $c163): array {
            $c163['a01_promoted'] = true;
            return $c163;
        }, 'candidate-a01');
        $function = $this->mutateC163FinalizationAndExecute(function (array $c163): array {
            $c163['watchlist_function_used'] = 'UNLOCKED_WATCHLIST_FUNCTION';
            return $c163;
        }, 'function-scope');

        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $candidate['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $a01['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_REJECTED_C163_GO_DECISION_FINALIZATION_STATE_INVALID', $function['status']);
    }

    public function test_c164_completion_boundary_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c164-completion-boundary-negative-remains-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS', $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c164_records_source_locks_manifest_checklist_and_no_publication_or_rollout(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();
        $manifest = $result['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_boundary_manifest'];
        $checklist = $result['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_boundary_checklist'];

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $this->assertSame(self::C163_FINALIZATION_HASH, $result['expected_c163_go_decision_finalization_hash']);
        $this->assertSame(self::C163_FINALIZATION_HASH, $result['actual_c163_go_decision_finalization_hash']);
        $this->assertTrue($result['c163_go_decision_finalization_hash_match']);
        $this->assertSame(self::C163_FINALIZATION_SHA1, $result['expected_c163_go_decision_finalization_file_sha1']);
        $this->assertSame(self::C163_FINALIZATION_SHA1, $result['actual_c163_go_decision_finalization_file_sha1']);
        $this->assertTrue($result['c163_go_decision_finalization_file_sha1_match']);
        $this->assertTrue($result['c163_go_decision_finalization_convert_from_json_pass']);
        $this->assertSame('CONTROLLED_WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATION', $result['watchlist_function_used']);
        $this->assertTrue($manifest['completion_boundary_artifact_only']);
        $this->assertTrue($manifest['ready_for_post_handoff_activation_completion_execution']);
        $this->assertFalse($manifest['completion_boundary_used_for_free_publication']);
        $this->assertFalse($manifest['completion_boundary_used_for_plan_confirm_mutation']);
        $this->assertFalse($manifest['completion_boundary_used_for_live_plan_confirm_rollout']);
        $this->assertSame([], $manifest['official_weekly_swing_stock_recommendations']);
        $this->assertTrue($checklist['artifact_only']);
        $this->assertTrue($checklist['ready_for_post_handoff_activation_completion_execution']);
        $this->assertFalse($checklist['weekly_swing_stock_recommendation_free_published_in_c164_boundary']);

        foreach ([
            'source_artifact_locks',
            'c163_go_decision_finalization_lock_validation_summary',
            'c163_go_decision_finalization_carry_forward_summary',
            'plan_confirm_completion_post_handoff_activation_completion_boundary_guard_summary',
            'candidate_scope_freeze_summary',
            'watchlist_function_scope_summary',
            'operator_approval_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'c164_completion_boundary_decision',
            'next_plan_confirm_completion_post_handoff_activation_completion_execution_decision',
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_boundary_manifest',
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_boundary_checklist',
            'c164_candidate_post_handoff_activation_completion_boundary_scorecard',
            'publication_plan_confirm_safety_summary',
            'documentation_hygiene_guard_summary',
            'progress_summary',
            'planned_next_summary',
            'diagnostics',
            'failure_attribution_summary',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }

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

    public function test_c164_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $first = $this->runService(['createdAt' => '2026-07-18T00:00:00+00:00']);
        $second = $this->runService(['createdAt' => '2026-07-18T00:00:00+00:00']);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
    }

    private function runService(array $options = []): array
    {
        $service = new WatchlistBacktestC164WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationCompletionBoundaryReviewService();

        return $service->execute(
            (string) ($options['c163FinalizationArtifact'] ?? self::C163_FINALIZATION_ARTIFACT),
            (string) ($options['expectedC163FinalizationHash'] ?? self::C163_FINALIZATION_HASH),
            (string) ($options['expectedC163FinalizationFileSha1'] ?? self::C163_FINALIZATION_SHA1),
            (string) ($options['output'] ?? $this->output),
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'completion_boundary_confirmed' => (bool) ($options['completionBoundaryConfirmed'] ?? true),
                'c163_topic_complete_confirmed' => (bool) ($options['c163TopicCompleteConfirmed'] ?? true),
                'post_handoff_activation_closed_confirmed' => (bool) ($options['postHandoffActivationClosedConfirmed'] ?? true),
                'plan_confirm_unchanged_confirmed' => (bool) ($options['planConfirmUnchangedConfirmed'] ?? true),
                'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['noLivePlanConfirmRolloutConfirmed'] ?? true),
                'free_publication_locked_confirmed' => (bool) ($options['freePublicationLockedConfirmed'] ?? true),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C164_OPERATOR_APPROVED_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_ONLY'),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-18T00:00:00+00:00'),
            ]
        );
    }

    private function mutateC163FinalizationAndExecute(callable $mutator, string $name): array
    {
        $c163 = json_decode((string) file_get_contents(self::C163_FINALIZATION_ARTIFACT), true);
        $c163 = $mutator(is_array($c163) ? $c163 : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c164-source-c163-finalization-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($c163, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'c163FinalizationArtifact' => $path,
            'expectedC163FinalizationHash' => (string) ($c163['artifact_hash'] ?? ''),
            'expectedC163FinalizationFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
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
            'storage/app/watchlist/backtest/c164-*completion-boundary*-test.json',
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

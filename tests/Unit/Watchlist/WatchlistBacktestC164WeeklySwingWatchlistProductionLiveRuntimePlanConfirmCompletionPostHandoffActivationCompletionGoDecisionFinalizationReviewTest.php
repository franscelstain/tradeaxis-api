<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC164WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationCompletionGoDecisionFinalizationReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC164WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationCompletionGoDecisionFinalizationReviewTest extends TestCase
{
    private const C164_OPERATOR_ARTIFACT = 'storage/app/watchlist/backtest/c164-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-completion-operator-go-no-go-review.json';
    private const C164_OPERATOR_HASH = 'df6957364fb3090d64ce767990fdab3964e2573d';
    private const C164_OPERATOR_SHA1 = '3F6C5BCD92864B89CDF2A974FD0C9F9367EDCD2C';
    private const PASS_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW_PASSED_GO_FINALIZED_POST_HANDOFF_ACTIVATION_COMPLETION_CLOSED_READY_FOR_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const GO_FINALIZATION_MISSING_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_GO_DECISION_FINALIZATION_CONFIRMATION_MISSING';
    private const COMPLETION_FINALIZATION_MISSING_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_POST_HANDOFF_ACTIVATION_COMPLETION_FINALIZATION_CONFIRMATION_MISSING';
    private const PLAN_CONFIRM_UNCHANGED_MISSING_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING';
    private const NO_LIVE_ROLLOUT_MISSING_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_NO_LIVE_PLAN_CONFIRM_ROLLOUT_CONFIRMATION_MISSING';
    private const FREE_PUBLICATION_LOCK_MISSING_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING';
    private const TEMP_NEGATIVE_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const LOCK_MISMATCH_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C164_OPERATOR_GO_NO_GO_ARTIFACT_LOCK_MISMATCH';
    private const SHA1_MISMATCH_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C164_OPERATOR_GO_NO_GO_FILE_SHA1_LOCK_MISMATCH';
    private const NEXT_BOUNDARY = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW';

    private string $output;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c164-plan-confirm-completion-post-handoff-activation-completion-go-decision-finalization-review.json';
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

    public function test_c164_go_decision_finalization_passes_closes_completion_topic_and_advances_to_next_boundary(): void
    {
        $result = $this->runService();

        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW', $result['run_code']);
        $this->assertSame('PR-85 / C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW', $result['phase_label']);
        $this->assertSame('C164_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION', $result['topic_code']);
        $this->assertSame('PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW', $result['topic_stage']);
        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame(self::PASS_STATUS, $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_review_pass']);
        $this->assertTrue($result['production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_review_pass']);
        $this->assertSame('GO', $result['operator_decision']);
        $this->assertTrue($result['operator_go_decision']);
        $this->assertTrue($result['operator_go_decision_confirmed']);
        $this->assertTrue($result['go_decision_finalized']);
        $this->assertTrue($result['go_decision_finalization_confirmed']);
        $this->assertTrue($result['post_handoff_activation_completion_finalization_confirmed']);
        $this->assertTrue($result['post_handoff_activation_completion_closed']);
        $this->assertTrue($result['c164_topic_complete_after_finalization']);
        $this->assertTrue($result['ready_for_weekly_swing_watchlist_plan_confirm_controlled_rollout_boundary_review']);
        $this->assertSame(self::NEXT_BOUNDARY, $result['next_step_recommendation']);
        $this->assertTrue($result['next_plan_confirm_controlled_rollout_boundary_decision']['topic_number_advances_after_c164_finalization']);
        $this->assertTrue($result['next_plan_confirm_controlled_rollout_boundary_decision']['same_topic_c164_complete']);
        $this->assertFileExists($this->output);
    }

    public function test_c164_finalization_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->runService(['operatorApproved' => false]);
        $missingReference = $this->runService(['approvalReference' => '']);

        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingOperator['status']);
        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingReference['status']);
    }

    public function test_c164_finalization_rejects_missing_required_confirmations(): void
    {
        $go = $this->runService(['goDecisionFinalizationConfirmed' => false]);
        $completion = $this->runService(['postHandoffActivationCompletionFinalizationConfirmed' => false]);
        $planUnchanged = $this->runService(['planConfirmUnchangedConfirmed' => false]);
        $noRollout = $this->runService(['noLivePlanConfirmRolloutConfirmed' => false]);
        $freeLock = $this->runService(['freePublicationLockedConfirmed' => false]);

        $this->assertSame(self::GO_FINALIZATION_MISSING_STATUS, $go['status']);
        $this->assertSame(self::COMPLETION_FINALIZATION_MISSING_STATUS, $completion['status']);
        $this->assertSame(self::PLAN_CONFIRM_UNCHANGED_MISSING_STATUS, $planUnchanged['status']);
        $this->assertSame(self::NO_LIVE_ROLLOUT_MISSING_STATUS, $noRollout['status']);
        $this->assertSame(self::FREE_PUBLICATION_LOCK_MISSING_STATUS, $freeLock['status']);
    }

    public function test_c164_finalization_rejects_missing_or_mismatched_operator_artifact_lock(): void
    {
        $missing = $this->runService([
            'c164OperatorArtifact' => 'storage/app/watchlist/backtest/.tmp-c164-finalization-source-missing.json',
            'expectedC164OperatorHash' => 'missing',
            'expectedC164OperatorFileSha1' => 'missing',
        ]);
        $hashMismatch = $this->runService(['expectedC164OperatorHash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedC164OperatorFileSha1' => 'BADSHA1']);

        $this->assertSame(self::LOCK_MISMATCH_STATUS, $missing['status']);
        $this->assertSame(self::LOCK_MISMATCH_STATUS, $hashMismatch['status']);
        $this->assertSame(self::SHA1_MISMATCH_STATUS, $shaMismatch['status']);
    }

    public function test_c164_finalization_rejects_operator_status_phase_or_next_recommendation_mismatch(): void
    {
        $status = $this->mutateOperatorAndExecute(function (array $operator): array {
            $operator['status'] = 'BROKEN_STATUS';
            return $operator;
        }, 'status-broken');
        $phase = $this->mutateOperatorAndExecute(function (array $operator): array {
            $operator['phase_label'] = 'BROKEN_PHASE';
            return $operator;
        }, 'phase-broken');
        $next = $this->mutateOperatorAndExecute(function (array $operator): array {
            $operator['next_step_recommendation'] = 'BROKEN_NEXT';
            $operator['next_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $operator['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $operator;
        }, 'next-broken');

        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C164_OPERATOR_GO_NO_GO_STATUS_MISMATCH', $status['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C164_OPERATOR_GO_NO_GO_PHASE_LABEL_MISMATCH', $phase['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C164_OPERATOR_GO_NO_GO_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
    }

    public function test_c164_finalization_rejects_operator_convert_from_json_duplicate_top_level_keys(): void
    {
        $raw = (string) file_get_contents(self::C164_OPERATOR_ARTIFACT);
        $path = 'storage/app/watchlist/backtest/.tmp-c164-finalization-source-operator-duplicate-key.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, preg_replace('/\{/', "{\"Run_Code\":\"DUPLICATE_CASE_KEY\",", $raw, 1));

        $result = $this->runService([
            'c164OperatorArtifact' => $path,
            'expectedC164OperatorFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);

        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C164_OPERATOR_GO_NO_GO_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION', $result['status']);
        $this->assertFalse($result['c164_operator_go_no_go_convert_from_json_pass']);
    }

    /**
     * @dataProvider c164OperatorGoMismatchProvider
     */
    public function test_c164_finalization_rejects_operator_go_state_mismatch(string $field, $value): void
    {
        $result = $this->mutateOperatorAndExecute(function (array $operator) use ($field, $value): array {
            $this->setValueAt($operator, explode('.', $field), $value);
            return $operator;
        }, 'operator-go-'.str_replace('.', '-', $field));

        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C164_OPERATOR_GO_INVALID', $result['status'], $field);
    }

    public function c164OperatorGoMismatchProvider(): array
    {
        return [
            ['weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_review_pass', false],
            ['operator_decision', 'NO_GO'],
            ['operator_go_decision', false],
            ['operator_decision_confirmed', false],
            ['operator_decision_reason', ''],
            ['ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_review', false],
            ['production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_review_allowed_next', false],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_manifest_created', false],
            ['post_handoff_activation_completion_result_reviewed', false],
            ['post_handoff_activation_completion_execution_completed', false],
            ['controlled_completion_lock_valid', false],
            ['weekly_swing_watchlist_plan_confirm_completion_controlled_only', false],
            ['c164_post_handoff_activation_completion_operator_go_no_go_decision.operator_decision', 'HOLD'],
            ['next_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_decision.go_decision_finalization_required_next', false],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_manifest.operator_decision', 'NO_GO'],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_manifest.operator_go_no_go_used_for_publication', true],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_manifest.operator_go_no_go_used_for_live_plan_confirm_rollout', true],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_checklist.artifact_only', false],
        ];
    }

    public function test_c164_finalization_rejects_publication_or_plan_confirm_mutation_candidate_or_watchlist_function_change(): void
    {
        $published = $this->mutateOperatorAndExecute(function (array $operator): array {
            $operator['weekly_swing_watchlist_official_output_published'] = true;
            return $operator;
        }, 'published');
        $planConfirm = $this->mutateOperatorAndExecute(function (array $operator): array {
            $operator['plan_confirm_mutated'] = true;
            return $operator;
        }, 'plan-confirm');
        $candidate = $this->mutateOperatorAndExecute(function (array $operator): array {
            $operator['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $operator;
        }, 'candidate-primary');
        $function = $this->mutateOperatorAndExecute(function (array $operator): array {
            $operator['watchlist_function_used'] = 'BROKEN_FUNCTION';
            return $operator;
        }, 'watchlist-function');

        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED', $published['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED', $planConfirm['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $candidate['status']);
        $this->assertSame('C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_WATCHLIST_FUNCTION_SCOPE_MISMATCH', $function['status']);
    }

    public function test_c164_finalization_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c164-post-handoff-activation-completion-go-decision-finalization-negative-remains-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame(self::TEMP_NEGATIVE_STATUS, $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c164_finalization_records_source_locks_manifest_checklist_and_no_publication_or_rollout(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();
        $manifest = $result['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_manifest'];
        $checklist = $result['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_checklist'];

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $this->assertSame(self::C164_OPERATOR_HASH, $result['expected_c164_operator_go_no_go_hash']);
        $this->assertSame(self::C164_OPERATOR_HASH, $result['actual_c164_operator_go_no_go_hash']);
        $this->assertTrue($result['c164_operator_go_no_go_hash_match']);
        $this->assertSame(self::C164_OPERATOR_SHA1, $result['expected_c164_operator_go_no_go_file_sha1']);
        $this->assertSame(self::C164_OPERATOR_SHA1, $result['actual_c164_operator_go_no_go_file_sha1']);
        $this->assertTrue($result['c164_operator_go_no_go_file_sha1_match']);
        $this->assertTrue($result['c164_operator_go_no_go_convert_from_json_pass']);
        $this->assertSame(self::NEXT_BOUNDARY, $result['next_plan_confirm_controlled_rollout_boundary_decision']['next_recommendation']);

        foreach ([
            'source_artifact_locks',
            'c164_operator_go_no_go_lock_validation_summary',
            'c164_operator_go_no_go_carry_forward_summary',
            'post_handoff_activation_completion_finalization_guard_summary',
            'watchlist_function_scope_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'c164_go_decision_finalization_decision',
            'next_plan_confirm_controlled_rollout_boundary_decision',
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_manifest',
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_checklist',
            'c164_candidate_post_handoff_activation_completion_go_decision_finalization_scorecard',
            'publication_plan_confirm_safety_summary',
            'documentation_hygiene_guard_summary',
            'progress_summary',
            'planned_next_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }

        $this->assertTrue($manifest['go_decision_finalization_artifact_only']);
        $this->assertTrue($manifest['operator_go_decision']);
        $this->assertTrue($manifest['go_decision_finalized']);
        $this->assertTrue($manifest['post_handoff_activation_completion_closed']);
        $this->assertTrue($manifest['ready_for_plan_confirm_controlled_rollout_boundary_review']);
        $this->assertFalse($manifest['official_output_published']);
        $this->assertFalse($manifest['free_publication_allowed']);
        $this->assertFalse($manifest['go_decision_finalization_used_for_plan_confirm_mutation']);
        $this->assertFalse($manifest['go_decision_finalization_used_for_free_publication']);
        $this->assertFalse($manifest['go_decision_finalization_used_for_live_plan_confirm_rollout']);
        $this->assertSame([], $manifest['official_weekly_swing_stock_recommendations']);
        $this->assertTrue($checklist['go_decision_finalization_reviewed']);
        $this->assertTrue($checklist['artifact_only']);
        $this->assertFalse($checklist['weekly_swing_stock_recommendation_free_published_in_c164_finalization']);
    }

    public function test_c164_finalization_keeps_e02_primary_b01_backup_a01_comparator_and_safety_flags_false(): void
    {
        $result = $this->runService();
        $scorecard = $result['c164_candidate_post_handoff_activation_completion_go_decision_finalization_scorecard'];

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $result['primary_candidate_code']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $result['backup_candidate_code']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $result['comparator_candidate_code']);
        $this->assertTrue($result['primary_candidate_ready_for_plan_confirm_controlled_rollout_boundary_review']);
        $this->assertTrue($result['backup_candidate_ready_for_plan_confirm_controlled_rollout_boundary_review']);
        $this->assertFalse($result['comparator_candidate_ready_for_plan_confirm_controlled_rollout_boundary_review']);
        $this->assertTrue($result['a01_remains_comparator_only']);
        $this->assertTrue($scorecard[0]['primary_candidate_ready_for_plan_confirm_controlled_rollout_boundary_review']);
        $this->assertTrue($scorecard[1]['backup_candidate_ready_for_plan_confirm_controlled_rollout_boundary_review']);
        $this->assertFalse($scorecard[2]['ready_for_plan_confirm_controlled_rollout_boundary_review']);

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
            $this->assertFalse($result['publication_plan_confirm_safety_summary'][$flag], $flag);
        }
    }

    public function test_c164_finalization_output_is_deterministic_and_does_not_mutate_source_or_config(): void
    {
        $beforeOperator = strtoupper(sha1((string) file_get_contents(self::C164_OPERATOR_ARTIFACT)));
        $beforeConfig = (string) file_get_contents('config/watchlist.php');
        $first = $this->runService(['createdAt' => '2026-07-18T00:00:00+00:00']);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c164-plan-confirm-completion-post-handoff-activation-completion-go-decision-finalization-review-second.json';
        $this->tmpFiles[] = $secondOutput;
        $second = $this->runService([
            'output' => $secondOutput,
            'createdAt' => '2026-07-18T00:00:00+00:00',
        ]);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
        $this->assertSame($beforeOperator, strtoupper(sha1((string) file_get_contents(self::C164_OPERATOR_ARTIFACT))));
        $this->assertSame($beforeConfig, (string) file_get_contents('config/watchlist.php'));
    }

    private function runService(array $options = []): array
    {
        $service = new WatchlistBacktestC164WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationCompletionGoDecisionFinalizationReviewService();

        return $service->execute(
            (string) ($options['c164OperatorArtifact'] ?? self::C164_OPERATOR_ARTIFACT),
            (string) ($options['expectedC164OperatorHash'] ?? self::C164_OPERATOR_HASH),
            (string) ($options['expectedC164OperatorFileSha1'] ?? self::C164_OPERATOR_SHA1),
            (string) ($options['output'] ?? $this->output),
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'go_decision_finalization_confirmed' => (bool) ($options['goDecisionFinalizationConfirmed'] ?? true),
                'post_handoff_activation_completion_finalization_confirmed' => (bool) ($options['postHandoffActivationCompletionFinalizationConfirmed'] ?? true),
                'plan_confirm_unchanged_confirmed' => (bool) ($options['planConfirmUnchangedConfirmed'] ?? true),
                'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['noLivePlanConfirmRolloutConfirmed'] ?? true),
                'free_publication_locked_confirmed' => (bool) ($options['freePublicationLockedConfirmed'] ?? true),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C164_OPERATOR_APPROVED_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW_ONLY'),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-18T00:00:00+00:00'),
            ]
        );
    }

    private function mutateOperatorAndExecute(callable $mutator, string $name): array
    {
        $operator = json_decode((string) file_get_contents(self::C164_OPERATOR_ARTIFACT), true);
        $operator = $mutator(is_array($operator) ? $operator : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c164-finalization-source-operator-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($operator, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'c164OperatorArtifact' => $path,
            'expectedC164OperatorHash' => (string) ($operator['artifact_hash'] ?? ''),
            'expectedC164OperatorFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
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

    private function cleanupC164TemporaryArtifacts(): void
    {
        foreach ((array) glob('storage/app/watchlist/backtest/c164-*completion-finalization*-test.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/c164-*completion-go-decision*-test.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/c164-*go-decision-finalization*-test.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/.tmp-c164-finalization*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/.tmp-c164-plan-confirm-completion-post-handoff-activation-completion-go-decision-finalization-review*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}

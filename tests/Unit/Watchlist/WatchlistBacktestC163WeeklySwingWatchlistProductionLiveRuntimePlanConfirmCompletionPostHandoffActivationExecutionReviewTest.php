<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationExecutionReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationExecutionReviewTest extends TestCase
{
    private const C163_APPROVAL_ARTIFACT = 'storage/app/watchlist/backtest/c163-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-approval-review.json';
    private const C163_APPROVAL_HASH = '9bcccdf3949205a5ab1a003d3767566cc4a5c004';
    private const C163_APPROVAL_SHA1 = 'A21BFA483E2B5BDDA74A40ACF2B7A51549A9B0CE';
    private const PASS_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_EXECUTION_REVIEW_PASSED_READY_FOR_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW_PRIMARY_AND_BACKUP';
    private const NEXT_C163 = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW';
    private const WATCHLIST_FUNCTION = 'CONTROLLED_WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATION';

    private string $output;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c163-plan-confirm-completion-post-handoff-activation-execution-review.json';
        $this->cleanupC163TemporaryArtifacts();
        @unlink($this->output);
    }

    protected function tearDown(): void
    {
        @unlink($this->output);
        foreach ($this->tmpFiles as $path) {
            @unlink($path);
        }
        $this->cleanupC163TemporaryArtifacts();
        parent::tearDown();
    }

    public function test_c163_post_handoff_activation_execution_passes_after_locked_approval_and_uses_controlled_watchlist_function(): void
    {
        $result = $this->runService();

        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_EXECUTION_REVIEW', $result['run_code']);
        $this->assertSame('PR-76 / C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_EXECUTION_REVIEW', $result['phase_label']);
        $this->assertSame('C163_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_EXECUTION', $result['topic_code']);
        $this->assertSame('PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_EXECUTION_REVIEW', $result['topic_stage']);
        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame(self::PASS_STATUS, $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_execution_review_pass']);
        $this->assertTrue($result['post_handoff_activation_execution_confirmed']);
        $this->assertTrue($result['post_handoff_activation_executed']);
        $this->assertTrue($result['controlled_post_handoff_activation_execution_executed']);
        $this->assertTrue($result['controlled_completion_lock_valid']);
        $this->assertSame(2, $result['controlled_completion_record_count']);
        $this->assertSame(self::WATCHLIST_FUNCTION, $result['watchlist_function_used']);
        $this->assertTrue($result['watchlist_function_primary_candidate_enabled']);
        $this->assertTrue($result['watchlist_function_backup_candidate_enabled']);
        $this->assertFalse($result['watchlist_function_comparator_candidate_enabled']);
        $this->assertTrue($result['ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_review']);
        $this->assertSame(self::NEXT_C163, $result['next_step_recommendation']);
        $this->assertSame(self::NEXT_C163, $result['next_plan_confirm_completion_post_handoff_activation_decision']['next_recommendation']);
        $this->assertFileExists($this->output);
    }

    public function test_c163_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->runService(['operatorApproved' => false]);
        $missingReference = $this->runService(['approvalReference' => '']);

        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_EXECUTION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingOperator['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_EXECUTION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingReference['status']);
    }

    public function test_c163_rejects_missing_required_confirmations(): void
    {
        $execution = $this->runService(['postHandoffActivationExecutionConfirmed' => false]);
        $approvalComplete = $this->runService(['c163PostHandoffActivationApprovalCompleteConfirmed' => false]);
        $approval = $this->runService(['postHandoffActivationApprovalConfirmed' => false]);
        $planUnchanged = $this->runService(['planConfirmUnchangedConfirmed' => false]);
        $noRollout = $this->runService(['noLivePlanConfirmRolloutConfirmed' => false]);
        $freeLock = $this->runService(['freePublicationLockedConfirmed' => false]);

        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_EXECUTION_REVIEW_REJECTED_POST_HANDOFF_ACTIVATION_EXECUTION_CONFIRMATION_MISSING', $execution['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_EXECUTION_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_APPROVAL_COMPLETE_CONFIRMATION_MISSING', $approvalComplete['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_EXECUTION_REVIEW_REJECTED_POST_HANDOFF_ACTIVATION_APPROVAL_CONFIRMATION_MISSING', $approval['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_EXECUTION_REVIEW_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING', $planUnchanged['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_EXECUTION_REVIEW_REJECTED_NO_LIVE_PLAN_CONFIRM_ROLLOUT_CONFIRMATION_MISSING', $noRollout['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_EXECUTION_REVIEW_REJECTED_FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING', $freeLock['status']);
    }

    public function test_c163_rejects_missing_or_mismatched_approval_lock(): void
    {
        $missing = $this->runService([
            'c163ApprovalArtifact' => 'storage/app/watchlist/backtest/.tmp-c163-activation-approval-source-missing.json',
            'expectedC163ApprovalHash' => 'missing',
            'expectedC163ApprovalFileSha1' => 'missing',
        ]);
        $hashMismatch = $this->runService(['expectedC163ApprovalHash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedC163ApprovalFileSha1' => 'BADSHA1']);

        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_EXECUTION_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_APPROVAL_ARTIFACT_LOCK_MISMATCH', $missing['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_EXECUTION_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_APPROVAL_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_EXECUTION_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_APPROVAL_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
    }

    public function test_c163_rejects_approval_status_phase_or_next_recommendation_mismatch(): void
    {
        $status = $this->mutateApprovalAndExecute(function (array $approval): array {
            $approval['status'] = 'BROKEN_STATUS';
            return $approval;
        }, 'status-broken');
        $phase = $this->mutateApprovalAndExecute(function (array $approval): array {
            $approval['phase_label'] = 'BROKEN_PHASE';
            return $approval;
        }, 'phase-broken');
        $next = $this->mutateApprovalAndExecute(function (array $approval): array {
            $approval['next_step_recommendation'] = 'BROKEN_NEXT';
            $approval['next_plan_confirm_completion_post_handoff_activation_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $approval['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $approval;
        }, 'next-broken');

        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_EXECUTION_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_APPROVAL_STATUS_MISMATCH', $status['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_EXECUTION_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_APPROVAL_PHASE_LABEL_MISMATCH', $phase['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_EXECUTION_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_APPROVAL_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
    }

    public function test_c163_rejects_approval_convert_from_json_duplicate_top_level_keys(): void
    {
        $raw = (string) file_get_contents(self::C163_APPROVAL_ARTIFACT);
        $path = 'storage/app/watchlist/backtest/.tmp-c163-activation-execution-source-approval-duplicate-key.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, preg_replace('/\{/', "{\"Run_Code\":\"DUPLICATE_CASE_KEY\",", $raw, 1));

        $result = $this->runService([
            'c163ApprovalArtifact' => $path,
            'expectedC163ApprovalHash' => self::C163_APPROVAL_HASH,
            'expectedC163ApprovalFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);

        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_EXECUTION_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_APPROVAL_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION', $result['status']);
        $this->assertFalse($result['c163_post_handoff_activation_approval_convert_from_json_pass']);
    }

    /**
     * @dataProvider approvalStateMismatchProvider
     */
    public function test_c163_rejects_approval_state_mismatch(string $field, $value): void
    {
        $result = $this->mutateApprovalAndExecute(function (array $approval) use ($field, $value): array {
            $this->setValueAt($approval, explode('.', $field), $value);
            return $approval;
        }, 'state-'.str_replace('.', '-', $field));

        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_EXECUTION_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_APPROVAL_STATE_INVALID', $result['status'], $field);
    }

    public function approvalStateMismatchProvider(): array
    {
        return [
            ['weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_approval_review_pass', false],
            ['post_handoff_activation_approval_confirmed', false],
            ['post_handoff_activation_approval_granted', false],
            ['ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_execution_review', false],
            ['production_live_runtime_plan_confirm_completion_post_handoff_activation_execution_review_allowed_next', false],
            ['c163_not_plan_confirm_mutation', false],
            ['controlled_completion_record_count', 0],
            ['controlled_completion_hash', ''],
            ['primary_candidate_ready_for_plan_confirm_completion_post_handoff_activation_execution_review', false],
            ['backup_candidate_ready_for_plan_confirm_completion_post_handoff_activation_execution_review', false],
            ['comparator_candidate_ready_for_plan_confirm_completion_post_handoff_activation_execution_review', true],
            ['c163_post_handoff_activation_approval_decision.review_valid', false],
            ['next_plan_confirm_completion_post_handoff_activation_decision.c163_post_handoff_activation_approval_complete', false],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_approval_manifest.ready_for_plan_confirm_completion_post_handoff_activation_execution_review', false],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_approval_manifest.post_handoff_activation_approval_artifact_only', false],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_approval_checklist.artifact_only', false],
        ];
    }

    public function test_c163_rejects_publication_or_plan_confirm_mutation_from_approval(): void
    {
        $published = $this->mutateApprovalAndExecute(function (array $approval): array {
            $approval['weekly_swing_watchlist_official_output_published'] = true;
            return $approval;
        }, 'published');
        $planConfirm = $this->mutateApprovalAndExecute(function (array $approval): array {
            $approval['plan_confirm_mutated'] = true;
            return $approval;
        }, 'plan-confirm-mutated');
        $liveRollout = $this->mutateApprovalAndExecute(function (array $approval): array {
            $approval['live_plan_confirm_rollout_executed'] = true;
            return $approval;
        }, 'live-rollout');
        $nestedFreePublication = $this->mutateApprovalAndExecute(function (array $approval): array {
            $approval['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_approval_manifest']['activation_approval_used_for_free_publication'] = true;
            return $approval;
        }, 'nested-free-publication');

        $expected = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_EXECUTION_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED';
        $this->assertSame($expected, $published['status']);
        $this->assertSame($expected, $planConfirm['status']);
        $this->assertSame($expected, $liveRollout['status']);
        $this->assertSame($expected, $nestedFreePublication['status']);
    }

    public function test_c163_rejects_controlled_completion_lock_mismatch(): void
    {
        $missing = $this->mutateApprovalAndExecute(function (array $approval): array {
            $approval['controlled_completion_path'] = 'storage/app/watchlist/output/.tmp-c163-missing-controlled-completion.json';
            return $approval;
        }, 'controlled-missing');
        $hash = $this->mutateApprovalAndExecute(function (array $approval): array {
            $approval['controlled_completion_hash'] = 'bad-hash';
            return $approval;
        }, 'controlled-hash');
        $sha = $this->mutateApprovalAndExecute(function (array $approval): array {
            $approval['controlled_completion_file_sha1'] = 'BADSHA1';
            return $approval;
        }, 'controlled-sha');

        $expected = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_EXECUTION_REVIEW_REJECTED_CONTROLLED_COMPLETION_LOCK_MISMATCH';
        $this->assertSame($expected, $missing['status']);
        $this->assertSame($expected, $hash['status']);
        $this->assertSame($expected, $sha['status']);
    }

    public function test_c163_rejects_candidate_scope_change_or_a01_promotion(): void
    {
        $candidate = $this->mutateApprovalAndExecute(function (array $approval): array {
            $approval['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $approval;
        }, 'candidate-primary');
        $a01 = $this->mutateApprovalAndExecute(function (array $approval): array {
            $approval['a01_promoted'] = true;
            return $approval;
        }, 'candidate-a01');

        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_EXECUTION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $candidate['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_EXECUTION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $a01['status']);
    }

    public function test_c163_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c163-post-handoff-activation-execution-negative-remains-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_EXECUTION_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS', $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c163_records_source_locks_function_manifest_checklist_no_publication_and_next_observation(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();
        $manifest = $result['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_execution_manifest'];
        $checklist = $result['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_execution_checklist'];

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $this->assertSame(self::C163_APPROVAL_HASH, $result['expected_c163_post_handoff_activation_approval_hash']);
        $this->assertSame(self::C163_APPROVAL_HASH, $result['actual_c163_post_handoff_activation_approval_hash']);
        $this->assertTrue($result['c163_post_handoff_activation_approval_hash_match']);
        $this->assertSame(self::C163_APPROVAL_SHA1, $result['expected_c163_post_handoff_activation_approval_file_sha1']);
        $this->assertSame(self::C163_APPROVAL_SHA1, $result['actual_c163_post_handoff_activation_approval_file_sha1']);
        $this->assertTrue($result['c163_post_handoff_activation_approval_file_sha1_match']);
        $this->assertSame(self::WATCHLIST_FUNCTION, $manifest['watchlist_function_used']);
        $this->assertTrue($manifest['controlled_completion_lock_valid']);
        $this->assertTrue($manifest['ready_for_plan_confirm_completion_post_handoff_activation_observation_review']);
        $this->assertFalse($manifest['activation_execution_used_for_free_publication']);
        $this->assertFalse($manifest['activation_execution_used_for_plan_confirm_mutation']);
        $this->assertFalse($manifest['activation_execution_used_for_live_plan_confirm_rollout']);
        $this->assertSame([], $manifest['official_weekly_swing_stock_recommendations']);
        $this->assertTrue($checklist['controlled_execution_only']);
        $this->assertTrue($checklist['post_handoff_activation_observation_review_required_next']);

        foreach ([
            'source_artifact_locks',
            'c163_post_handoff_activation_approval_lock_validation_summary',
            'c163_post_handoff_activation_approval_carry_forward_summary',
            'controlled_completion_lock_validation_summary',
            'watchlist_function_activation_summary',
            'plan_confirm_completion_post_handoff_activation_execution_guard_summary',
            'candidate_scope_freeze_summary',
            'c163_post_handoff_activation_execution_decision',
            'next_plan_confirm_completion_post_handoff_activation_decision',
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_execution_manifest',
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_execution_checklist',
            'c163_candidate_plan_confirm_completion_post_handoff_activation_execution_scorecard',
            'publication_plan_confirm_safety_summary',
            'documentation_hygiene_guard_summary',
            'progress_summary',
            'planned_next_summary',
            'diagnostics',
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

    public function test_c163_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $first = $this->runService(['createdAt' => '2026-07-18T00:00:00+00:00']);
        $second = $this->runService(['createdAt' => '2026-07-18T00:00:00+00:00']);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
    }

    private function runService(array $options = []): array
    {
        $service = new WatchlistBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationExecutionReviewService();

        return $service->execute(
            (string) ($options['c163ApprovalArtifact'] ?? self::C163_APPROVAL_ARTIFACT),
            (string) ($options['expectedC163ApprovalHash'] ?? self::C163_APPROVAL_HASH),
            (string) ($options['expectedC163ApprovalFileSha1'] ?? self::C163_APPROVAL_SHA1),
            (string) ($options['output'] ?? $this->output),
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'post_handoff_activation_execution_confirmed' => (bool) ($options['postHandoffActivationExecutionConfirmed'] ?? true),
                'c163_post_handoff_activation_approval_complete_confirmed' => (bool) ($options['c163PostHandoffActivationApprovalCompleteConfirmed'] ?? true),
                'post_handoff_activation_approval_confirmed' => (bool) ($options['postHandoffActivationApprovalConfirmed'] ?? true),
                'plan_confirm_unchanged_confirmed' => (bool) ($options['planConfirmUnchangedConfirmed'] ?? true),
                'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['noLivePlanConfirmRolloutConfirmed'] ?? true),
                'free_publication_locked_confirmed' => (bool) ($options['freePublicationLockedConfirmed'] ?? true),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C163_OPERATOR_APPROVED_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_EXECUTION_REVIEW'),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-18T00:00:00+00:00'),
            ]
        );
    }

    private function mutateApprovalAndExecute(callable $mutator, string $name): array
    {
        $approval = json_decode((string) file_get_contents(self::C163_APPROVAL_ARTIFACT), true);
        $approval = $mutator(is_array($approval) ? $approval : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c163-post-handoff-activation-execution-source-approval-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($approval, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'c163ApprovalArtifact' => $path,
            'expectedC163ApprovalHash' => (string) ($approval['artifact_hash'] ?? ''),
            'expectedC163ApprovalFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
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

    private function cleanupC163TemporaryArtifacts(): void
    {
        foreach ((array) glob('storage/app/watchlist/backtest/c163-*post-handoff-activation-execution*-test.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/.tmp-c163-post-handoff-activation-execution*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/.tmp-runtime-c163-post-handoff-activation-execution-negative-*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}

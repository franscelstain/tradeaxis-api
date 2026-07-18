<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationObservationReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationObservationReviewTest extends TestCase
{
    private const C163_EXECUTION_ARTIFACT = 'storage/app/watchlist/backtest/c163-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-execution-review.json';
    private const C163_EXECUTION_HASH = 'e3e1656317754920f8c1248ea515ef9bce1a89aa';
    private const C163_EXECUTION_SHA1 = '40A12B54B58D509982B7739E39905003852D225D';
    private const PASS_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW_PASSED_READY_FOR_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_PRIMARY_AND_BACKUP';
    private const NEXT_C163 = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW';
    private const WATCHLIST_FUNCTION = 'CONTROLLED_WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATION';

    private string $output;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c163-plan-confirm-completion-post-handoff-activation-observation-review.json';
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

    public function test_c163_post_handoff_activation_observation_passes_after_locked_execution_and_observes_controlled_watchlist_function(): void
    {
        $result = $this->runService();

        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW', $result['run_code']);
        $this->assertSame('PR-77 / C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW', $result['phase_label']);
        $this->assertSame('C163_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION', $result['topic_code']);
        $this->assertSame('PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW', $result['topic_stage']);
        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame(self::PASS_STATUS, $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_observation_review_pass']);
        $this->assertTrue($result['post_handoff_activation_observation_confirmed']);
        $this->assertTrue($result['post_handoff_activation_observed']);
        $this->assertTrue($result['controlled_watchlist_function_observed']);
        $this->assertTrue($result['c163_post_handoff_activation_execution_lock_valid']);
        $this->assertTrue($result['c163_post_handoff_activation_execution_complete']);
        $this->assertSame(2, $result['controlled_completion_record_count']);
        $this->assertSame(self::WATCHLIST_FUNCTION, $result['watchlist_function_used']);
        $this->assertTrue($result['watchlist_function_primary_candidate_observed']);
        $this->assertTrue($result['watchlist_function_backup_candidate_observed']);
        $this->assertFalse($result['watchlist_function_comparator_candidate_observed']);
        $this->assertTrue($result['runtime_bridge_active']);
        $this->assertTrue($result['weekly_swing_watchlist_runtime_active']);
        $this->assertTrue($result['weekly_swing_watchlist_live_output_enabled']);
        $this->assertTrue($result['weekly_swing_watchlist_live_recommendation_generation_allowed']);
        $this->assertTrue($result['ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_result_review']);
        $this->assertSame(self::NEXT_C163, $result['next_step_recommendation']);
        $this->assertSame(self::NEXT_C163, $result['next_plan_confirm_completion_post_handoff_activation_decision']['next_recommendation']);
        $this->assertFileExists($this->output);
    }

    public function test_c163_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->runService(['operatorApproved' => false]);
        $missingReference = $this->runService(['approvalReference' => '']);

        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingOperator['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingReference['status']);
    }

    public function test_c163_rejects_missing_required_confirmations(): void
    {
        $observation = $this->runService(['postHandoffActivationObservationConfirmed' => false]);
        $executionComplete = $this->runService(['c163PostHandoffActivationExecutionCompleteConfirmed' => false]);
        $execution = $this->runService(['postHandoffActivationExecutionConfirmed' => false]);
        $planUnchanged = $this->runService(['planConfirmUnchangedConfirmed' => false]);
        $noRollout = $this->runService(['noLivePlanConfirmRolloutConfirmed' => false]);
        $freeLock = $this->runService(['freePublicationLockedConfirmed' => false]);

        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW_REJECTED_POST_HANDOFF_ACTIVATION_OBSERVATION_CONFIRMATION_MISSING', $observation['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_EXECUTION_COMPLETE_CONFIRMATION_MISSING', $executionComplete['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW_REJECTED_POST_HANDOFF_ACTIVATION_EXECUTION_CONFIRMATION_MISSING', $execution['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING', $planUnchanged['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW_REJECTED_NO_LIVE_PLAN_CONFIRM_ROLLOUT_CONFIRMATION_MISSING', $noRollout['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW_REJECTED_FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING', $freeLock['status']);
    }

    public function test_c163_rejects_missing_or_mismatched_execution_lock(): void
    {
        $missing = $this->runService([
            'c163ExecutionArtifact' => 'storage/app/watchlist/backtest/.tmp-c163-activation-execution-source-missing.json',
            'expectedC163ExecutionHash' => 'missing',
            'expectedC163ExecutionFileSha1' => 'missing',
        ]);
        $hashMismatch = $this->runService(['expectedC163ExecutionHash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedC163ExecutionFileSha1' => 'BADSHA1']);

        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_EXECUTION_ARTIFACT_LOCK_MISMATCH', $missing['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_EXECUTION_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_EXECUTION_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
    }

    public function test_c163_rejects_execution_status_phase_or_next_recommendation_mismatch(): void
    {
        $status = $this->mutateExecutionAndExecute(function (array $execution): array {
            $execution['status'] = 'BROKEN_STATUS';
            return $execution;
        }, 'status-broken');
        $phase = $this->mutateExecutionAndExecute(function (array $execution): array {
            $execution['phase_label'] = 'BROKEN_PHASE';
            return $execution;
        }, 'phase-broken');
        $next = $this->mutateExecutionAndExecute(function (array $execution): array {
            $execution['next_step_recommendation'] = 'BROKEN_NEXT';
            $execution['next_plan_confirm_completion_post_handoff_activation_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $execution['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $execution;
        }, 'next-broken');

        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_EXECUTION_STATUS_MISMATCH', $status['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_EXECUTION_PHASE_LABEL_MISMATCH', $phase['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_EXECUTION_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
    }

    public function test_c163_rejects_execution_convert_from_json_duplicate_top_level_keys(): void
    {
        $raw = (string) file_get_contents(self::C163_EXECUTION_ARTIFACT);
        $path = 'storage/app/watchlist/backtest/.tmp-c163-activation-observation-source-execution-duplicate-key.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, preg_replace('/\{/', "{\"Run_Code\":\"DUPLICATE_CASE_KEY\",", $raw, 1));

        $result = $this->runService([
            'c163ExecutionArtifact' => $path,
            'expectedC163ExecutionHash' => self::C163_EXECUTION_HASH,
            'expectedC163ExecutionFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);

        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_EXECUTION_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION', $result['status']);
        $this->assertFalse($result['c163_post_handoff_activation_execution_convert_from_json_pass']);
    }

    /**
     * @dataProvider executionStateMismatchProvider
     */
    public function test_c163_rejects_execution_state_mismatch(string $field, $value): void
    {
        $result = $this->mutateExecutionAndExecute(function (array $execution) use ($field, $value): array {
            $this->setValueAt($execution, explode('.', $field), $value);
            return $execution;
        }, 'state-'.str_replace('.', '-', $field));

        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_EXECUTION_STATE_INVALID', $result['status'], $field);
    }

    public function executionStateMismatchProvider(): array
    {
        return [
            ['weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_execution_review_pass', false],
            ['post_handoff_activation_execution_confirmed', false],
            ['post_handoff_activation_executed', false],
            ['controlled_post_handoff_activation_execution_executed', false],
            ['ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_review', false],
            ['production_live_runtime_plan_confirm_completion_post_handoff_activation_observation_review_allowed_next', false],
            ['c163_not_plan_confirm_mutation', false],
            ['controlled_completion_record_count', 0],
            ['controlled_completion_hash', ''],
            ['watchlist_function_primary_candidate_enabled', false],
            ['watchlist_function_backup_candidate_enabled', false],
            ['watchlist_function_comparator_candidate_enabled', true],
            ['primary_candidate_ready_for_plan_confirm_completion_post_handoff_activation_observation_review', false],
            ['backup_candidate_ready_for_plan_confirm_completion_post_handoff_activation_observation_review', false],
            ['comparator_candidate_ready_for_plan_confirm_completion_post_handoff_activation_observation_review', true],
            ['c163_post_handoff_activation_execution_decision.review_valid', false],
            ['next_plan_confirm_completion_post_handoff_activation_decision.c163_post_handoff_activation_execution_complete', false],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_execution_manifest.ready_for_plan_confirm_completion_post_handoff_activation_observation_review', false],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_execution_manifest.post_handoff_activation_execution_controlled_only', false],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_execution_checklist.controlled_execution_only', false],
        ];
    }

    public function test_c163_rejects_publication_or_plan_confirm_mutation_from_execution(): void
    {
        $published = $this->mutateExecutionAndExecute(function (array $execution): array {
            $execution['weekly_swing_watchlist_official_output_published'] = true;
            return $execution;
        }, 'published');
        $planConfirm = $this->mutateExecutionAndExecute(function (array $execution): array {
            $execution['plan_confirm_mutated'] = true;
            return $execution;
        }, 'plan-confirm-mutated');
        $liveRollout = $this->mutateExecutionAndExecute(function (array $execution): array {
            $execution['live_plan_confirm_rollout_executed'] = true;
            return $execution;
        }, 'live-rollout');
        $nestedFreePublication = $this->mutateExecutionAndExecute(function (array $execution): array {
            $execution['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_execution_manifest']['activation_execution_used_for_free_publication'] = true;
            return $execution;
        }, 'nested-free-publication');

        $expected = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED';
        $this->assertSame($expected, $published['status']);
        $this->assertSame($expected, $planConfirm['status']);
        $this->assertSame($expected, $liveRollout['status']);
        $this->assertSame($expected, $nestedFreePublication['status']);
    }

    public function test_c163_rejects_watchlist_function_observation_mismatch(): void
    {
        $function = $this->mutateExecutionAndExecute(function (array $execution): array {
            $execution['watchlist_function_used'] = 'BROKEN_FUNCTION';
            return $execution;
        }, 'function-broken');
        $mode = $this->mutateExecutionAndExecute(function (array $execution): array {
            $execution['watchlist_function_runtime_mode'] = 'BROKEN_MODE';
            return $execution;
        }, 'mode-broken');

        $expected = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW_REJECTED_WATCHLIST_FUNCTION_OBSERVATION_MISMATCH';
        $this->assertSame($expected, $function['status']);
        $this->assertSame($expected, $mode['status']);
    }

    public function test_c163_rejects_candidate_scope_change_or_a01_promotion(): void
    {
        $candidate = $this->mutateExecutionAndExecute(function (array $execution): array {
            $execution['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $execution;
        }, 'candidate-primary');
        $a01 = $this->mutateExecutionAndExecute(function (array $execution): array {
            $execution['a01_promoted'] = true;
            return $execution;
        }, 'candidate-a01');

        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $candidate['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $a01['status']);
    }

    public function test_c163_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c163-post-handoff-activation-observation-negative-remains-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS', $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c163_records_source_locks_observation_manifest_checklist_no_publication_and_next_result_review(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();
        $manifest = $result['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_manifest'];
        $checklist = $result['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_checklist'];

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $this->assertSame(self::C163_EXECUTION_HASH, $result['expected_c163_post_handoff_activation_execution_hash']);
        $this->assertSame(self::C163_EXECUTION_HASH, $result['actual_c163_post_handoff_activation_execution_hash']);
        $this->assertTrue($result['c163_post_handoff_activation_execution_hash_match']);
        $this->assertSame(self::C163_EXECUTION_SHA1, $result['expected_c163_post_handoff_activation_execution_file_sha1']);
        $this->assertSame(self::C163_EXECUTION_SHA1, $result['actual_c163_post_handoff_activation_execution_file_sha1']);
        $this->assertTrue($result['c163_post_handoff_activation_execution_file_sha1_match']);
        $this->assertSame(self::WATCHLIST_FUNCTION, $manifest['watchlist_function_used']);
        $this->assertTrue($manifest['ready_for_plan_confirm_completion_post_handoff_activation_observation_result_review']);
        $this->assertFalse($manifest['activation_observation_used_for_free_publication']);
        $this->assertFalse($manifest['activation_observation_used_for_plan_confirm_mutation']);
        $this->assertFalse($manifest['activation_observation_used_for_live_plan_confirm_rollout']);
        $this->assertSame([], $manifest['official_weekly_swing_stock_recommendations']);
        $this->assertTrue($checklist['controlled_observation_only']);
        $this->assertTrue($checklist['post_handoff_activation_observation_result_review_required_next']);

        foreach ([
            'source_artifact_locks',
            'c163_post_handoff_activation_execution_lock_validation_summary',
            'c163_post_handoff_activation_execution_carry_forward_summary',
            'watchlist_function_observation_summary',
            'plan_confirm_completion_post_handoff_activation_observation_guard_summary',
            'candidate_scope_freeze_summary',
            'c163_post_handoff_activation_observation_decision',
            'next_plan_confirm_completion_post_handoff_activation_decision',
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_manifest',
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_checklist',
            'c163_candidate_plan_confirm_completion_post_handoff_activation_observation_scorecard',
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
        $service = new WatchlistBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationObservationReviewService();

        return $service->execute(
            (string) ($options['c163ExecutionArtifact'] ?? self::C163_EXECUTION_ARTIFACT),
            (string) ($options['expectedC163ExecutionHash'] ?? self::C163_EXECUTION_HASH),
            (string) ($options['expectedC163ExecutionFileSha1'] ?? self::C163_EXECUTION_SHA1),
            (string) ($options['output'] ?? $this->output),
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'post_handoff_activation_observation_confirmed' => (bool) ($options['postHandoffActivationObservationConfirmed'] ?? true),
                'c163_post_handoff_activation_execution_complete_confirmed' => (bool) ($options['c163PostHandoffActivationExecutionCompleteConfirmed'] ?? true),
                'post_handoff_activation_execution_confirmed' => (bool) ($options['postHandoffActivationExecutionConfirmed'] ?? true),
                'plan_confirm_unchanged_confirmed' => (bool) ($options['planConfirmUnchangedConfirmed'] ?? true),
                'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['noLivePlanConfirmRolloutConfirmed'] ?? true),
                'free_publication_locked_confirmed' => (bool) ($options['freePublicationLockedConfirmed'] ?? true),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C163_OPERATOR_APPROVED_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW'),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-18T00:00:00+00:00'),
            ]
        );
    }

    private function mutateExecutionAndExecute(callable $mutator, string $name): array
    {
        $execution = json_decode((string) file_get_contents(self::C163_EXECUTION_ARTIFACT), true);
        $execution = $mutator(is_array($execution) ? $execution : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c163-post-handoff-activation-observation-source-execution-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($execution, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'c163ExecutionArtifact' => $path,
            'expectedC163ExecutionHash' => (string) ($execution['artifact_hash'] ?? ''),
            'expectedC163ExecutionFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
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
        foreach ((array) glob('storage/app/watchlist/backtest/c163-*post-handoff-activation-observation*-test.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/.tmp-c163-post-handoff-activation-observation*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/.tmp-runtime-c163-post-handoff-activation-observation-negative-*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}

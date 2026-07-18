<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationObservationResultReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationObservationResultReviewTest extends TestCase
{
    private const C163_OBSERVATION_ARTIFACT = 'storage/app/watchlist/backtest/c163-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-observation-review.json';
    private const C163_OBSERVATION_HASH = '2c150f14fca84692db091b8b5137ed1e68855ffa';
    private const C163_OBSERVATION_SHA1 = '94ACF854DAF2DF1669B89D487F13496D0019F576';
    private const PASS_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_PASSED_READY_FOR_POST_HANDOFF_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_PRIMARY_AND_BACKUP';
    private const NEXT_C163 = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW';
    private const WATCHLIST_FUNCTION = 'CONTROLLED_WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATION';

    private string $output;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c163-plan-confirm-completion-post-handoff-activation-observation-result-review.json';
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

    public function test_c163_post_handoff_activation_observation_result_passes_and_keeps_same_topic_operator_next(): void
    {
        $result = $this->runService();

        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW', $result['run_code']);
        $this->assertSame('PR-78 / C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW', $result['phase_label']);
        $this->assertSame('C163_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION', $result['topic_code']);
        $this->assertSame('PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW', $result['topic_stage']);
        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame(self::PASS_STATUS, $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_observation_result_review_pass']);
        $this->assertTrue($result['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_result_reviewed']);
        $this->assertTrue($result['post_handoff_activation_observation_result_confirmed']);
        $this->assertTrue($result['post_handoff_activation_observation_result_stable']);
        $this->assertTrue($result['controlled_watchlist_function_observation_result_reviewed']);
        $this->assertTrue($result['ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_operator_go_no_go_review']);
        $this->assertSame(self::NEXT_C163, $result['next_step_recommendation']);
        $this->assertSame(self::NEXT_C163, $result['next_plan_confirm_completion_post_handoff_activation_decision']['next_recommendation']);
        $this->assertFileExists($this->output);
    }

    public function test_c163_result_review_records_locks_sections_candidate_scope_and_safety_guards(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();
        $manifest = $result['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_result_manifest'];
        $checklist = $result['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_result_checklist'];

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $this->assertSame(self::C163_OBSERVATION_HASH, $result['expected_c163_post_handoff_activation_observation_hash']);
        $this->assertSame(self::C163_OBSERVATION_HASH, $result['actual_c163_post_handoff_activation_observation_hash']);
        $this->assertTrue($result['c163_post_handoff_activation_observation_hash_match']);
        $this->assertSame(self::C163_OBSERVATION_SHA1, $result['expected_c163_post_handoff_activation_observation_file_sha1']);
        $this->assertSame(self::C163_OBSERVATION_SHA1, $result['actual_c163_post_handoff_activation_observation_file_sha1']);
        $this->assertTrue($result['c163_post_handoff_activation_observation_file_sha1_match']);
        $this->assertTrue($result['c163_post_handoff_activation_observation_lock_valid']);
        $this->assertTrue($result['c163_post_handoff_activation_observation_complete']);
        $this->assertSame(2, $result['controlled_completion_record_count']);
        $this->assertSame(self::WATCHLIST_FUNCTION, $result['watchlist_function_used']);
        $this->assertTrue($result['watchlist_function_primary_candidate_observed']);
        $this->assertTrue($result['watchlist_function_backup_candidate_observed']);
        $this->assertFalse($result['watchlist_function_comparator_candidate_observed']);
        $this->assertTrue($result['primary_candidate_observation_result_reviewed']);
        $this->assertTrue($result['backup_candidate_observation_result_reviewed']);
        $this->assertFalse($result['comparator_candidate_observation_result_reviewed']);
        $this->assertTrue($result['a01_remains_comparator_only']);
        $this->assertSame(self::NEXT_C163, $manifest['ready_for_plan_confirm_completion_post_handoff_activation_operator_go_no_go_review'] ? $result['next_step_recommendation'] : '');
        $this->assertFalse($manifest['activation_observation_result_used_for_free_publication']);
        $this->assertFalse($manifest['activation_observation_result_used_for_plan_confirm_mutation']);
        $this->assertFalse($manifest['activation_observation_result_used_for_live_plan_confirm_rollout']);
        $this->assertSame([], $manifest['official_weekly_swing_stock_recommendations']);
        $this->assertTrue($checklist['controlled_observation_result_only']);
        $this->assertTrue($checklist['post_handoff_activation_operator_go_no_go_review_required_next']);

        foreach ([
            'operator_approval_validation_summary',
            'result_review_confirmation_summary',
            'temporary_negative_artifact_guard_summary',
            'c163_post_handoff_activation_observation_lock_validation_summary',
            'c163_post_handoff_activation_observation_carry_forward_summary',
            'watchlist_function_observation_result_summary',
            'plan_confirm_completion_post_handoff_activation_observation_result_guard_summary',
            'candidate_observation_result_scorecard',
            'c163_post_handoff_activation_observation_result_decision',
            'next_plan_confirm_completion_post_handoff_activation_decision',
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_result_manifest',
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_result_checklist',
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

    public function test_c163_result_review_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->runService(['operatorApproved' => false]);
        $missingReference = $this->runService(['approvalReference' => '']);

        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingOperator['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingReference['status']);
    }

    public function test_c163_result_review_rejects_missing_required_confirmations(): void
    {
        $resultReview = $this->runService(['resultReviewConfirmed' => false]);
        $observationResult = $this->runService(['postHandoffActivationObservationResultConfirmed' => false]);
        $observationComplete = $this->runService(['c163PostHandoffActivationObservationCompleteConfirmed' => false]);
        $observation = $this->runService(['postHandoffActivationObservationConfirmed' => false]);
        $planUnchanged = $this->runService(['planConfirmUnchangedConfirmed' => false]);
        $noRollout = $this->runService(['noLivePlanConfirmRolloutConfirmed' => false]);
        $freeLock = $this->runService(['freePublicationLockedConfirmed' => false]);

        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_RESULT_REVIEW_CONFIRMATION_MISSING', $resultReview['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_CONFIRMATION_MISSING', $observationResult['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_OBSERVATION_COMPLETE_CONFIRMATION_MISSING', $observationComplete['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_POST_HANDOFF_ACTIVATION_OBSERVATION_CONFIRMATION_MISSING', $observation['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING', $planUnchanged['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_NO_LIVE_PLAN_CONFIRM_ROLLOUT_CONFIRMATION_MISSING', $noRollout['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING', $freeLock['status']);
    }

    public function test_c163_result_review_rejects_missing_or_mismatched_observation_lock(): void
    {
        $missing = $this->runService([
            'c163ObservationArtifact' => 'storage/app/watchlist/backtest/.tmp-c163-observation-source-missing.json',
            'expectedC163ObservationHash' => 'missing',
            'expectedC163ObservationFileSha1' => 'missing',
        ]);
        $hashMismatch = $this->runService(['expectedC163ObservationHash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedC163ObservationFileSha1' => 'BADSHA1']);

        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_OBSERVATION_ARTIFACT_LOCK_MISMATCH', $missing['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_OBSERVATION_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_OBSERVATION_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
    }

    public function test_c163_result_review_rejects_observation_status_phase_or_next_mismatch(): void
    {
        $status = $this->mutateObservationAndExecute(function (array $observation): array {
            $observation['status'] = 'BROKEN_STATUS';
            return $observation;
        }, 'status-broken');
        $phase = $this->mutateObservationAndExecute(function (array $observation): array {
            $observation['phase_label'] = 'BROKEN_PHASE';
            return $observation;
        }, 'phase-broken');
        $next = $this->mutateObservationAndExecute(function (array $observation): array {
            $observation['next_step_recommendation'] = 'BROKEN_NEXT';
            $observation['next_plan_confirm_completion_post_handoff_activation_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $observation['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $observation;
        }, 'next-broken');

        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_OBSERVATION_STATUS_MISMATCH', $status['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_OBSERVATION_PHASE_LABEL_MISMATCH', $phase['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_OBSERVATION_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
    }

    public function test_c163_result_review_rejects_observation_convert_from_json_duplicate_top_level_keys(): void
    {
        $raw = (string) file_get_contents(self::C163_OBSERVATION_ARTIFACT);
        $path = 'storage/app/watchlist/backtest/.tmp-c163-post-handoff-activation-observation-result-source-duplicate-key.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, preg_replace('/\{/', "{\"Run_Code\":\"DUPLICATE_CASE_KEY\",", $raw, 1));

        $result = $this->runService([
            'c163ObservationArtifact' => $path,
            'expectedC163ObservationHash' => self::C163_OBSERVATION_HASH,
            'expectedC163ObservationFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);

        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_OBSERVATION_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION', $result['status']);
        $this->assertFalse($result['c163_post_handoff_activation_observation_convert_from_json_pass']);
    }

    /**
     * @dataProvider observationIncompleteProvider
     */
    public function test_c163_result_review_rejects_incomplete_observation_evidence(string $field, $value): void
    {
        $result = $this->mutateObservationAndExecute(function (array $observation) use ($field, $value): array {
            $this->setValueAt($observation, explode('.', $field), $value);
            return $observation;
        }, 'incomplete-'.str_replace('.', '-', $field));

        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_OBSERVATION_INCOMPLETE', $result['status'], $field);
    }

    public function observationIncompleteProvider(): array
    {
        return [
            ['weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_observation_review_pass', false],
            ['post_handoff_activation_observed', false],
            ['controlled_watchlist_function_observed', false],
            ['ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_result_review', false],
            ['controlled_completion_record_count', 0],
            ['controlled_completion_hash', ''],
            ['watchlist_function_primary_candidate_observed', false],
            ['watchlist_function_backup_candidate_observed', false],
            ['watchlist_function_comparator_candidate_observed', true],
            ['topic_stage', 'PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW'],
            ['next_plan_confirm_completion_post_handoff_activation_decision.c163_post_handoff_activation_observation_complete', false],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_manifest.ready_for_plan_confirm_completion_post_handoff_activation_observation_result_review', false],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_checklist.controlled_observation_only', false],
        ];
    }

    public function test_c163_result_review_rejects_publication_or_plan_confirm_mutation_from_observation(): void
    {
        $published = $this->mutateObservationAndExecute(function (array $observation): array {
            $observation['weekly_swing_watchlist_official_output_published'] = true;
            return $observation;
        }, 'published');
        $planConfirm = $this->mutateObservationAndExecute(function (array $observation): array {
            $observation['plan_confirm_mutated'] = true;
            return $observation;
        }, 'plan-confirm-mutated');
        $liveRollout = $this->mutateObservationAndExecute(function (array $observation): array {
            $observation['live_plan_confirm_rollout_executed'] = true;
            return $observation;
        }, 'live-rollout');

        $expected = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED';
        $this->assertSame($expected, $published['status']);
        $this->assertSame($expected, $planConfirm['status']);
        $this->assertSame($expected, $liveRollout['status']);
    }

    public function test_c163_result_review_rejects_watchlist_function_or_candidate_scope_mismatch(): void
    {
        $function = $this->mutateObservationAndExecute(function (array $observation): array {
            $observation['watchlist_function_used'] = 'BROKEN_FUNCTION';
            return $observation;
        }, 'function-broken');
        $mode = $this->mutateObservationAndExecute(function (array $observation): array {
            $observation['watchlist_function_runtime_mode'] = 'BROKEN_MODE';
            return $observation;
        }, 'mode-broken');
        $candidate = $this->mutateObservationAndExecute(function (array $observation): array {
            $observation['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $observation;
        }, 'candidate-primary');
        $a01 = $this->mutateObservationAndExecute(function (array $observation): array {
            $observation['a01_promoted'] = true;
            return $observation;
        }, 'candidate-a01');

        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_WATCHLIST_FUNCTION_OBSERVATION_RESULT_MISMATCH', $function['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_WATCHLIST_FUNCTION_OBSERVATION_RESULT_MISMATCH', $mode['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $candidate['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $a01['status']);
    }

    public function test_c163_result_review_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c163-post-handoff-activation-observation-result-review-negative-remains-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS', $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c163_result_review_output_is_deterministic_and_does_not_mutate_source(): void
    {
        $beforeObservation = strtoupper(sha1((string) file_get_contents(self::C163_OBSERVATION_ARTIFACT)));
        $first = $this->runService(['createdAt' => '2026-07-18T00:00:00+00:00']);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c163-plan-confirm-completion-post-handoff-activation-observation-result-review-second.json';
        $this->tmpFiles[] = $secondOutput;
        $second = $this->runService([
            'output' => $secondOutput,
            'createdAt' => '2026-07-18T00:00:00+00:00',
        ]);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
        $this->assertSame($beforeObservation, strtoupper(sha1((string) file_get_contents(self::C163_OBSERVATION_ARTIFACT))));
    }

    private function runService(array $options = []): array
    {
        $service = new WatchlistBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationObservationResultReviewService();

        return $service->execute(
            (string) ($options['c163ObservationArtifact'] ?? self::C163_OBSERVATION_ARTIFACT),
            (string) ($options['expectedC163ObservationHash'] ?? self::C163_OBSERVATION_HASH),
            (string) ($options['expectedC163ObservationFileSha1'] ?? self::C163_OBSERVATION_SHA1),
            (string) ($options['output'] ?? $this->output),
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'result_review_confirmed' => (bool) ($options['resultReviewConfirmed'] ?? true),
                'post_handoff_activation_observation_result_confirmed' => (bool) ($options['postHandoffActivationObservationResultConfirmed'] ?? true),
                'c163_post_handoff_activation_observation_complete_confirmed' => (bool) ($options['c163PostHandoffActivationObservationCompleteConfirmed'] ?? true),
                'post_handoff_activation_observation_confirmed' => (bool) ($options['postHandoffActivationObservationConfirmed'] ?? true),
                'plan_confirm_unchanged_confirmed' => (bool) ($options['planConfirmUnchangedConfirmed'] ?? true),
                'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['noLivePlanConfirmRolloutConfirmed'] ?? true),
                'free_publication_locked_confirmed' => (bool) ($options['freePublicationLockedConfirmed'] ?? true),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C163_OPERATOR_APPROVED_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW'),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-18T00:00:00+00:00'),
            ]
        );
    }

    private function mutateObservationAndExecute(callable $mutator, string $name): array
    {
        $observation = json_decode((string) file_get_contents(self::C163_OBSERVATION_ARTIFACT), true);
        $observation = $mutator(is_array($observation) ? $observation : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c163-post-handoff-activation-observation-result-source-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($observation, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'c163ObservationArtifact' => $path,
            'expectedC163ObservationHash' => (string) ($observation['artifact_hash'] ?? ''),
            'expectedC163ObservationFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
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
        foreach ((array) glob('storage/app/watchlist/backtest/c163-*post-handoff-activation-observation-result-review*-test.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/.tmp-c163-plan-confirm-completion-post-handoff-activation-observation-result-review*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/.tmp-c163-post-handoff-activation-observation-result*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/.tmp-runtime-c163-post-handoff-activation-observation-result-negative-*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}

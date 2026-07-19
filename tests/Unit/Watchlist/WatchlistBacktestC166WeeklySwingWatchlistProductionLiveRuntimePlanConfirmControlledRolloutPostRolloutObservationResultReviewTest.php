<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC166WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutPostRolloutObservationResultReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC166WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutPostRolloutObservationResultReviewTest extends TestCase
{
    private const OBSERVATION = 'storage/app/watchlist/backtest/c166-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-post-rollout-observation-review.json';
    private const OBSERVATION_HASH = '9ffec96e1a08e927c5ad14445d6e6d038528a7f2';
    private const OBSERVATION_SHA1 = 'D9AF66D1488F3BA14134820647E8C1A288C75525';
    private const PASS_STATUS = 'C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_RESULT_REVIEW_PASSED_READY_FOR_POST_ROLLOUT_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW_PRIMARY_AND_BACKUP';
    private const NEXT_REVIEW = 'C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW';

    private string $output;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c166-post-rollout-observation-result-review.json';
        $this->cleanupTemporaryArtifacts();
    }

    protected function tearDown(): void
    {
        $this->cleanupTemporaryArtifacts();
        parent::tearDown();
    }

    public function test_c166_result_review_validates_observation_and_keeps_same_topic(): void
    {
        $result = $this->runService();

        $this->assertSame('C166_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION', $result['topic_code']);
        $this->assertSame('PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_RESULT_REVIEW', $result['topic_stage']);
        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertTrue($result['post_rollout_observation_result_reviewed']);
        $this->assertTrue($result['post_rollout_observation_result_valid']);
        $this->assertTrue($result['control_plane_observation_result_stable']);
        $this->assertTrue($result['ready_for_weekly_swing_watchlist_plan_confirm_controlled_rollout_post_rollout_observation_operator_go_no_go_review']);
        $this->assertTrue($result['operator_go_no_go_review_required_next']);
        $this->assertFalse($result['c166_topic_complete']);
        $this->assertTrue($result['c166_topic_number_retained_for_operator_go_no_go_review']);
        $this->assertSame(self::NEXT_REVIEW, $result['next_step_recommendation']);
        $this->assertTrue($result['next_post_rollout_observation_operator_go_no_go_decision']['same_topic_c166_continues']);
        $this->assertFileExists($this->output);
    }

    public function test_c166_result_review_locks_exact_observation_source(): void
    {
        $result = $this->runService();
        $lock = $result['source_artifact_locks'][0];

        $this->assertTrue($result['c166_observation_lock_valid']);
        $this->assertTrue($result['c166_observation_result_valid']);
        $this->assertTrue($result['all_required_source_locks_valid']);
        $this->assertSame(self::OBSERVATION_HASH, $result['actual_c166_observation_hash']);
        $this->assertSame(self::OBSERVATION_SHA1, $result['actual_c166_observation_file_sha1']);
        $this->assertSame(self::OBSERVATION, $lock['path']);
        $this->assertTrue($lock['hash_match']);
        $this->assertTrue($lock['file_sha1_match']);
        $this->assertTrue($lock['convert_from_json_pass']);
    }

    public function test_c166_result_review_does_not_infer_unavailable_market_metrics(): void
    {
        $result = $this->runService();
        $contract = $result['observation_metric_result_review_contract'];

        $this->assertSame('LOCKED_CONTROL_PLANE_RUNTIME_STATE_SNAPSHOT', $result['observation_basis']);
        $this->assertFalse($result['market_outcome_metrics_available']);
        $this->assertFalse($result['price_performance_evaluated']);
        $this->assertFalse($result['recommendation_quality_evaluated']);
        $this->assertFalse($result['market_metrics_inferred_by_result_review']);
        $this->assertFalse($contract['market_outcome_metrics_available']);
        $this->assertFalse($contract['price_performance_evaluated']);
        $this->assertFalse($contract['recommendation_quality_evaluated']);
        $this->assertFalse($contract['market_metrics_inferred_by_result_review']);
        $this->assertTrue($contract['operator_confirmed_market_metrics_not_inferred']);
        $this->assertTrue($contract['operator_review_must_not_infer_unavailable_market_metrics']);
    }

    public function test_c166_result_review_is_read_only_and_preserves_safety_guards(): void
    {
        $result = $this->runService();

        $this->assertTrue($result['controlled_rollout_active']);
        $this->assertTrue($result['plan_confirm_mutated']);
        $this->assertTrue($result['plan_confirm_runtime_reads_activated_catalog']);
        $this->assertTrue($result['live_plan_confirm_rollout_executed']);
        $this->assertFalse($result['new_rollout_executed']);
        $this->assertFalse($result['new_plan_confirm_mutation_executed']);
        $this->assertFalse($result['new_catalog_read_executed']);
        $this->assertFalse($result['watchlist_function_invoked_by_observation_review']);
        $this->assertFalse($result['watchlist_function_invoked_by_result_review']);
        $this->assertFalse($result['production_config_mutated']);
        $this->assertFalse($result['unrestricted_rollout_allowed']);
        $this->assertFalse($result['weekly_swing_watchlist_official_output_published']);
        $this->assertFalse($result['weekly_swing_watchlist_publication_allowed']);
        $this->assertFalse($result['weekly_swing_watchlist_unrestricted_publication_allowed']);
        $this->assertTrue($result['kill_switch_confirmed']);
        $this->assertTrue($result['rollback_confirmed']);
    }

    public function test_c166_result_review_preserves_candidate_and_function_scope(): void
    {
        $result = $this->runService();
        $scorecard = $result['c166_candidate_post_rollout_observation_result_scorecard'];

        $this->assertSame('CONTROLLED_WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATION', $result['watchlist_function_used']);
        $this->assertSame('PLAN_CONFIRM_CONTROLLED_ROLLOUT_ACTIVE', $result['watchlist_function_runtime_mode']);
        $this->assertTrue($result['watchlist_function_primary_candidate_observed']);
        $this->assertTrue($result['watchlist_function_backup_candidate_observed']);
        $this->assertFalse($result['watchlist_function_comparator_candidate_observed']);
        $this->assertTrue($scorecard[0]['ready_for_operator_go_no_go_review']);
        $this->assertTrue($scorecard[1]['ready_for_operator_go_no_go_review']);
        $this->assertFalse($scorecard[2]['ready_for_operator_go_no_go_review']);
        $this->assertTrue($result['a01_remains_comparator_only']);
        $this->assertSame(2, $result['rollout_state_record_count']);
    }

    public function test_c166_result_review_rejects_missing_operator_approval_or_reference(): void
    {
        $withoutApproval = $this->runService(['operatorApproved' => false]);
        $withoutReference = $this->runService(['approvalReference' => '']);
        $expected = 'C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_RESULT_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';

        $this->assertSame($expected, $withoutApproval['status']);
        $this->assertSame($expected, $withoutReference['status']);
    }

    /**
     * @dataProvider confirmationProvider
     */
    public function test_c166_result_review_rejects_missing_confirmation(string $option, string $suffix): void
    {
        $result = $this->runService([$option => false]);

        $this->assertSame(
            'C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_RESULT_REVIEW_REJECTED_'.$suffix,
            $result['status']
        );
    }

    public function confirmationProvider(): array
    {
        return [
            ['resultReviewConfirmed', 'RESULT_REVIEW_CONFIRMATION_MISSING'],
            ['postRolloutObservationResultConfirmed', 'POST_ROLLOUT_OBSERVATION_RESULT_CONFIRMATION_MISSING'],
            ['observationArtifactLockedConfirmed', 'OBSERVATION_ARTIFACT_LOCK_CONFIRMATION_MISSING'],
            ['controlPlaneSnapshotConfirmed', 'CONTROL_PLANE_SNAPSHOT_CONFIRMATION_MISSING'],
            ['candidateScopeConfirmed', 'CANDIDATE_SCOPE_CONFIRMATION_MISSING'],
            ['killSwitchConfirmed', 'KILL_SWITCH_CONFIRMATION_MISSING'],
            ['rollbackConfirmed', 'ROLLBACK_CONFIRMATION_MISSING'],
            ['productionConfigUnchangedConfirmed', 'PRODUCTION_CONFIG_UNCHANGED_CONFIRMATION_MISSING'],
            ['freePublicationLockedConfirmed', 'FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING'],
            ['marketMetricsNotInferredConfirmed', 'MARKET_METRICS_NOT_INFERRED_CONFIRMATION_MISSING'],
        ];
    }

    /**
     * @dataProvider sourceLockProvider
     */
    public function test_c166_result_review_rejects_source_lock_mismatch(string $option, string $value, string $suffix): void
    {
        $result = $this->runService([$option => $value]);

        $this->assertStringEndsWith('_REJECTED_'.$suffix, $result['status']);
    }

    public function sourceLockProvider(): array
    {
        return [
            ['expectedObservationHash', 'bad-hash', 'C166_POST_ROLLOUT_OBSERVATION_ARTIFACT_LOCK_MISMATCH'],
            ['expectedObservationSha1', 'BADSHA1', 'C166_POST_ROLLOUT_OBSERVATION_FILE_SHA1_LOCK_MISMATCH'],
        ];
    }

    public function test_c166_result_review_rejects_missing_or_duplicate_source(): void
    {
        $missing = $this->runService([
            'observation' => 'storage/app/watchlist/backtest/.tmp-c166-post-rollout-observation-source-missing.json',
            'expectedObservationHash' => 'missing',
            'expectedObservationSha1' => 'missing',
        ]);

        $raw = (string) file_get_contents(self::OBSERVATION);
        $path = 'storage/app/watchlist/backtest/.tmp-c166-post-rollout-observation-result-source-duplicate.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, preg_replace('/\{/', "{\"Run_Code\":\"DUPLICATE\",", $raw, 1));
        $duplicate = $this->runService([
            'observation' => $path,
            'expectedObservationSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);

        $this->assertStringEndsWith('_REJECTED_C166_POST_ROLLOUT_OBSERVATION_ARTIFACT_LOCK_MISMATCH', $missing['status']);
        $this->assertStringEndsWith('_REJECTED_C166_POST_ROLLOUT_OBSERVATION_JSON_COMPATIBILITY_VIOLATION', $duplicate['status']);
        $this->assertFalse($duplicate['c166_observation_convert_from_json_pass']);
    }

    public function test_c166_result_review_rejects_status_phase_or_next_mismatch(): void
    {
        $status = $this->mutateObservationAndReview(function (array $observation): array {
            $observation['status'] = 'BROKEN_STATUS';

            return $observation;
        }, 'status');
        $phase = $this->mutateObservationAndReview(function (array $observation): array {
            $observation['phase_label'] = 'BROKEN_PHASE';

            return $observation;
        }, 'phase');
        $next = $this->mutateObservationAndReview(function (array $observation): array {
            $observation['next_step_recommendation'] = 'BROKEN_NEXT';
            $observation['next_post_rollout_observation_result_review_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $observation['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';

            return $observation;
        }, 'next');

        $this->assertStringEndsWith('_REJECTED_C166_POST_ROLLOUT_OBSERVATION_STATUS_MISMATCH', $status['status']);
        $this->assertStringEndsWith('_REJECTED_C166_POST_ROLLOUT_OBSERVATION_PHASE_LABEL_MISMATCH', $phase['status']);
        $this->assertStringEndsWith('_REJECTED_C166_POST_ROLLOUT_OBSERVATION_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
    }

    /**
     * @dataProvider safetyViolationProvider
     */
    public function test_c166_result_review_rejects_safety_violation(string $field, $value): void
    {
        $result = $this->mutateFieldAndReview($field, $value, 'safety');

        $this->assertStringEndsWith('_REJECTED_PUBLICATION_ROLLOUT_OR_CONFIG_SAFETY_VIOLATION', $result['status'], $field);
    }

    public function safetyViolationProvider(): array
    {
        return [
            ['new_rollout_executed', true],
            ['production_config_mutated', true],
            ['weekly_swing_watchlist_official_output_published', true],
            ['free_publication_allowed', true],
            ['kill_switch_confirmed', false],
            ['publication_and_rollout_safety_summary.rollback_confirmed', false],
        ];
    }

    /**
     * @dataProvider metricViolationProvider
     */
    public function test_c166_result_review_rejects_unavailable_metric_inference(string $field, $value): void
    {
        $result = $this->mutateFieldAndReview($field, $value, 'metric');

        $this->assertStringEndsWith('_REJECTED_UNAVAILABLE_MARKET_METRIC_INFERENCE', $result['status'], $field);
    }

    public function metricViolationProvider(): array
    {
        return [
            ['market_outcome_metrics_available', true],
            ['price_performance_evaluated', true],
            ['observation_scope_and_metric_contract.recommendation_quality_evaluated', true],
            ['observation_scope_and_metric_contract.result_review_must_not_infer_unavailable_market_metrics', false],
        ];
    }

    /**
     * @dataProvider functionViolationProvider
     */
    public function test_c166_result_review_rejects_function_scope_mismatch(string $field, $value): void
    {
        $result = $this->mutateFieldAndReview($field, $value, 'function');

        $this->assertStringEndsWith('_REJECTED_WATCHLIST_FUNCTION_OBSERVATION_RESULT_MISMATCH', $result['status'], $field);
    }

    public function functionViolationProvider(): array
    {
        return [
            ['watchlist_function_used', 'BROKEN_FUNCTION'],
            ['watchlist_function_runtime_mode', 'BROKEN_MODE'],
            ['watchlist_function_invoked_by_observation_review', true],
            ['watchlist_function_observation_summary.watchlist_function_scope_valid', false],
        ];
    }

    /**
     * @dataProvider candidateViolationProvider
     */
    public function test_c166_result_review_rejects_candidate_scope_mismatch(string $field, $value): void
    {
        $result = $this->mutateFieldAndReview($field, $value, 'candidate');

        $this->assertStringEndsWith('_REJECTED_CANDIDATE_SCOPE_MISMATCH', $result['status'], $field);
    }

    public function candidateViolationProvider(): array
    {
        return [
            ['primary_candidate_code', 'BROKEN_PRIMARY'],
            ['a01_remains_comparator_only', false],
            ['candidate_scope_observation_summary.candidate_rerank_executed', true],
            ['c166_candidate_post_rollout_observation_scorecard.2.control_plane_observed', true],
        ];
    }

    /**
     * @dataProvider incompleteObservationProvider
     */
    public function test_c166_result_review_rejects_incomplete_observation(string $field, $value): void
    {
        $result = $this->mutateFieldAndReview($field, $value, 'incomplete');

        $this->assertStringEndsWith('_REJECTED_C166_POST_ROLLOUT_OBSERVATION_INCOMPLETE', $result['status'], $field);
    }

    public function incompleteObservationProvider(): array
    {
        return [
            ['run_code', 'BROKEN_RUN'],
            ['post_rollout_observation_started', false],
            ['controlled_rollout_observation_stable', false],
            ['rollout_state_record_count', 0],
            ['source_lock_validation_summary.all_required_source_locks_valid', false],
            ['weekly_swing_watchlist_plan_confirm_controlled_rollout_post_rollout_observation_manifest.manifest_created', false],
        ];
    }

    public function test_c166_result_review_contains_required_audit_sections(): void
    {
        $result = $this->runService();
        $run = json_decode((string) file_get_contents($this->output), true);

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        foreach ([
            'source_artifact_locks', 'source_lock_validation_summary', 'c166_post_rollout_observation_carry_forward_summary',
            'observation_metric_result_review_contract', 'watchlist_function_observation_result_summary',
            'candidate_scope_observation_result_summary', 'publication_and_rollout_result_review_safety_summary',
            'operator_result_review_confirmation_summary', 'temporary_negative_artifact_guard_summary',
            'next_post_rollout_observation_operator_go_no_go_decision',
            'weekly_swing_watchlist_plan_confirm_controlled_rollout_post_rollout_observation_result_manifest',
            'weekly_swing_watchlist_plan_confirm_controlled_rollout_post_rollout_observation_result_checklist',
            'c166_candidate_post_rollout_observation_result_scorecard', 'progress_summary', 'planned_next_summary',
            'diagnostics', 'failure_attribution_summary',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }

        $this->assertSame([], $run['weekly_swing_watchlist_plan_confirm_controlled_rollout_post_rollout_observation_result_manifest']['official_weekly_swing_stock_recommendations']);
    }

    public function test_c166_result_review_is_deterministic_and_does_not_mutate_source_or_config(): void
    {
        $sourceSha = strtoupper(sha1((string) file_get_contents(self::OBSERVATION)));
        $config = (string) file_get_contents('config/watchlist.php');
        $first = $this->runService(['createdAt' => '2026-07-19T00:00:00+00:00']);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c166-post-rollout-observation-result-review-second.json';
        $this->tmpFiles[] = $secondOutput;
        $second = $this->runService(['output' => $secondOutput, 'createdAt' => '2026-07-19T00:00:00+00:00']);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
        $this->assertSame($sourceSha, strtoupper(sha1((string) file_get_contents(self::OBSERVATION))));
        $this->assertSame($config, (string) file_get_contents('config/watchlist.php'));
    }

    public function test_c166_result_review_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c166-post-rollout-observation-result-review-negative-remains-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');
        $result = $this->runService();

        $this->assertStringEndsWith('_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS', $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
    }

    private function runService(array $options = []): array
    {
        $service = new WatchlistBacktestC166WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutPostRolloutObservationResultReviewService();

        return $service->execute(
            (string) ($options['observation'] ?? self::OBSERVATION),
            (string) ($options['expectedObservationHash'] ?? self::OBSERVATION_HASH),
            (string) ($options['expectedObservationSha1'] ?? self::OBSERVATION_SHA1),
            (string) ($options['output'] ?? $this->output),
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'result_review_confirmed' => (bool) ($options['resultReviewConfirmed'] ?? true),
                'post_rollout_observation_result_confirmed' => (bool) ($options['postRolloutObservationResultConfirmed'] ?? true),
                'observation_artifact_locked_confirmed' => (bool) ($options['observationArtifactLockedConfirmed'] ?? true),
                'control_plane_snapshot_confirmed' => (bool) ($options['controlPlaneSnapshotConfirmed'] ?? true),
                'candidate_scope_confirmed' => (bool) ($options['candidateScopeConfirmed'] ?? true),
                'kill_switch_confirmed' => (bool) ($options['killSwitchConfirmed'] ?? true),
                'rollback_confirmed' => (bool) ($options['rollbackConfirmed'] ?? true),
                'production_config_unchanged_confirmed' => (bool) ($options['productionConfigUnchangedConfirmed'] ?? true),
                'free_publication_locked_confirmed' => (bool) ($options['freePublicationLockedConfirmed'] ?? true),
                'market_metrics_not_inferred_confirmed' => (bool) ($options['marketMetricsNotInferredConfirmed'] ?? true),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C166_OPERATOR_APPROVED_POST_ROLLOUT_OBSERVATION_RESULT_REVIEW'),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-19T00:00:00+00:00'),
            ]
        );
    }

    private function mutateFieldAndReview(string $field, $value, string $name): array
    {
        return $this->mutateObservationAndReview(function (array $observation) use ($field, $value): array {
            $this->setValueAt($observation, explode('.', $field), $value);

            return $observation;
        }, $name.'-'.str_replace('.', '-', $field));
    }

    private function mutateObservationAndReview(callable $mutator, string $name): array
    {
        $payload = json_decode((string) file_get_contents(self::OBSERVATION), true);
        $payload = $mutator(is_array($payload) ? $payload : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c166-post-rollout-observation-result-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'observation' => $path,
            'expectedObservationHash' => (string) ($payload['artifact_hash'] ?? ''),
            'expectedObservationSha1' => strtoupper(sha1((string) file_get_contents($path))),
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

    private function cleanupTemporaryArtifacts(): void
    {
        $patterns = [
            'storage/app/watchlist/backtest/.tmp-c166-post-rollout-observation-result*.json',
            'storage/app/watchlist/backtest/c166-*post-rollout-observation-result-review*-test.json',
        ];
        $paths = $this->tmpFiles;
        foreach ($patterns as $pattern) {
            $paths = array_merge($paths, (array) glob($pattern));
        }
        foreach (array_unique($paths) as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
        $this->tmpFiles = [];
    }
}

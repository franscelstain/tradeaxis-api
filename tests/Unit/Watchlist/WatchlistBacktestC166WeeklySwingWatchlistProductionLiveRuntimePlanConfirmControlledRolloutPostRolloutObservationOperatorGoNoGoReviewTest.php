<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC166WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutPostRolloutObservationOperatorGoNoGoReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC166WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutPostRolloutObservationOperatorGoNoGoReviewTest extends TestCase
{
    private const RESULT = 'storage/app/watchlist/backtest/c166-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-post-rollout-observation-result-review.json';
    private const RESULT_HASH = '1dbd61b08afb2d45918cc66a16c782983cfd6666';
    private const RESULT_SHA1 = '2555E1C7612C066FBF60342D0235AE399CB23253';
    private const GO_STATUS = 'C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP_READY_FOR_GO_DECISION_FINALIZATION_REVIEW';
    private const NEXT_FINALIZATION = 'C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW';

    private string $output;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c166-post-rollout-observation-operator-go-no-go-review.json';
        $this->cleanupTemporaryArtifacts();
    }

    protected function tearDown(): void
    {
        $this->cleanupTemporaryArtifacts();
        parent::tearDown();
    }

    public function test_c166_operator_go_records_decision_and_keeps_same_topic_for_finalization(): void
    {
        $result = $this->runService();

        $this->assertSame('C166_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION', $result['topic_code']);
        $this->assertSame('PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW', $result['topic_stage']);
        $this->assertSame(self::GO_STATUS, $result['status']);
        $this->assertTrue($result['operator_go_no_go_review_completed']);
        $this->assertTrue($result['operator_decision_recorded']);
        $this->assertSame('GO', $result['operator_decision']);
        $this->assertTrue($result['operator_go_decision']);
        $this->assertFalse($result['operator_no_go_decision']);
        $this->assertFalse($result['operator_hold_decision']);
        $this->assertTrue($result['ready_for_weekly_swing_watchlist_plan_confirm_controlled_rollout_post_rollout_observation_go_decision_finalization_review']);
        $this->assertFalse($result['go_decision_finalized']);
        $this->assertFalse($result['c166_topic_complete']);
        $this->assertSame(self::NEXT_FINALIZATION, $result['next_step_recommendation']);
        $this->assertTrue($result['next_post_rollout_observation_go_decision_finalization_decision']['same_topic_c166_continues']);
        $this->assertFileExists($this->output);
    }

    public function test_c166_operator_no_go_completes_and_stops_progression(): void
    {
        $result = $this->runService(['operatorDecision' => 'NO_GO']);

        $this->assertStringContainsString('_COMPLETED_NO_GO_', $result['status']);
        $this->assertTrue($result['operator_go_no_go_review_completed']);
        $this->assertTrue($result['operator_no_go_decision']);
        $this->assertTrue($result['controlled_rollout_post_rollout_observation_stopped_no_go']);
        $this->assertFalse($result['operator_go_decision']);
        $this->assertFalse($result['ready_for_weekly_swing_watchlist_plan_confirm_controlled_rollout_post_rollout_observation_go_decision_finalization_review']);
        $this->assertSame('C166_POST_ROLLOUT_OBSERVATION_PROGRESSION_STOPPED_NO_GO', $result['next_step_recommendation']);
    }

    public function test_c166_operator_hold_completes_and_defers_progression(): void
    {
        $result = $this->runService(['operatorDecision' => 'hold']);

        $this->assertStringContainsString('_COMPLETED_HOLD_', $result['status']);
        $this->assertTrue($result['operator_go_no_go_review_completed']);
        $this->assertTrue($result['operator_hold_decision']);
        $this->assertTrue($result['controlled_rollout_post_rollout_observation_deferred_hold']);
        $this->assertFalse($result['operator_go_decision']);
        $this->assertSame('C166_POST_ROLLOUT_OBSERVATION_PROGRESSION_DEFERRED_HOLD', $result['next_step_recommendation']);
    }

    public function test_c166_operator_locks_exact_result_review_source(): void
    {
        $result = $this->runService();
        $lock = $result['source_artifact_locks'][0];

        $this->assertTrue($result['c166_result_review_lock_valid']);
        $this->assertTrue($result['c166_post_rollout_observation_result_review_valid']);
        $this->assertSame(self::RESULT_HASH, $result['actual_c166_result_review_hash']);
        $this->assertSame(self::RESULT_SHA1, $result['actual_c166_result_review_file_sha1']);
        $this->assertSame(self::RESULT, $lock['path']);
        $this->assertTrue($lock['hash_match']);
        $this->assertTrue($lock['file_sha1_match']);
        $this->assertTrue($lock['convert_from_json_pass']);
    }

    public function test_c166_operator_rejects_missing_approval_or_reference(): void
    {
        $withoutApproval = $this->runService(['operatorApproved' => false]);
        $withoutReference = $this->runService(['approvalReference' => '']);
        $expected = 'C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';

        $this->assertSame($expected, $withoutApproval['status']);
        $this->assertSame($expected, $withoutReference['status']);
    }

    public function test_c166_operator_rejects_invalid_unconfirmed_or_reasonless_decision(): void
    {
        $invalid = $this->runService(['operatorDecision' => 'MAYBE']);
        $unconfirmed = $this->runService(['operatorDecisionConfirmed' => false]);
        $reasonless = $this->runService(['decisionReason' => '']);

        $this->assertStringEndsWith('_REJECTED_OPERATOR_DECISION_INVALID', $invalid['status']);
        $this->assertStringEndsWith('_REJECTED_OPERATOR_DECISION_NOT_CONFIRMED', $unconfirmed['status']);
        $this->assertStringEndsWith('_REJECTED_OPERATOR_DECISION_REASON_MISSING', $reasonless['status']);
    }

    /**
     * @dataProvider confirmationProvider
     */
    public function test_c166_operator_rejects_missing_confirmation(string $option, string $suffix): void
    {
        $result = $this->runService([$option => false]);

        $this->assertSame(
            'C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_'.$suffix,
            $result['status']
        );
    }

    public function confirmationProvider(): array
    {
        return [
            ['resultReviewLockedConfirmed', 'RESULT_REVIEW_LOCK_CONFIRMATION_MISSING'],
            ['postRolloutObservationResultConfirmed', 'POST_ROLLOUT_OBSERVATION_RESULT_CONFIRMATION_MISSING'],
            ['controlPlaneResultConfirmed', 'CONTROL_PLANE_RESULT_CONFIRMATION_MISSING'],
            ['marketMetricsNotInferredConfirmed', 'MARKET_METRICS_NOT_INFERRED_CONFIRMATION_MISSING'],
            ['candidateScopeConfirmed', 'CANDIDATE_SCOPE_CONFIRMATION_MISSING'],
            ['killSwitchConfirmed', 'KILL_SWITCH_CONFIRMATION_MISSING'],
            ['rollbackConfirmed', 'ROLLBACK_CONFIRMATION_MISSING'],
            ['productionConfigUnchangedConfirmed', 'PRODUCTION_CONFIG_UNCHANGED_CONFIRMATION_MISSING'],
            ['freePublicationLockedConfirmed', 'FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING'],
        ];
    }

    public function test_c166_operator_rejects_result_hash_sha_or_missing_source(): void
    {
        $badHash = $this->runService(['expectedResultHash' => 'bad-hash']);
        $badSha = $this->runService(['expectedResultSha1' => 'BADSHA1']);
        $missing = $this->runService([
            'result' => 'storage/app/watchlist/backtest/.tmp-c166-missing-operator-result.json',
            'expectedResultHash' => 'missing',
            'expectedResultSha1' => 'missing',
        ]);

        $this->assertStringEndsWith('_REJECTED_C166_RESULT_REVIEW_ARTIFACT_LOCK_MISMATCH', $badHash['status']);
        $this->assertStringEndsWith('_REJECTED_C166_RESULT_REVIEW_FILE_SHA1_LOCK_MISMATCH', $badSha['status']);
        $this->assertStringEndsWith('_REJECTED_C166_RESULT_REVIEW_ARTIFACT_LOCK_MISMATCH', $missing['status']);
    }

    public function test_c166_operator_rejects_duplicate_top_level_key(): void
    {
        $raw = (string) file_get_contents(self::RESULT);
        $path = 'storage/app/watchlist/backtest/.tmp-c166-operator-result-duplicate.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, preg_replace('/\{/', "{\"Run_Code\":\"DUPLICATE\",", $raw, 1));

        $result = $this->runService([
            'result' => $path,
            'expectedResultSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);

        $this->assertStringEndsWith('_REJECTED_C166_RESULT_REVIEW_JSON_COMPATIBILITY_VIOLATION', $result['status']);
        $this->assertFalse($result['c166_result_review_convert_from_json_pass']);
    }

    /**
     * @dataProvider invalidResultProvider
     */
    public function test_c166_operator_rejects_incomplete_or_unsafe_result_review(string $field, $value): void
    {
        $result = $this->mutateResultAndReview(function (array $payload) use ($field, $value): array {
            $this->setValueAt($payload, explode('.', $field), $value);

            return $payload;
        }, str_replace('.', '-', $field));

        $this->assertStringEndsWith('_REJECTED_C166_RESULT_REVIEW_INCOMPLETE', $result['status'], $field);
    }

    public function invalidResultProvider(): array
    {
        return [
            ['status', 'BROKEN'],
            ['post_rollout_observation_result_valid', false],
            ['control_plane_observation_result_stable', false],
            ['operator_go_no_go_review_required_next', false],
            ['market_metrics_inferred_by_result_review', true],
            ['new_rollout_executed', true],
            ['kill_switch_confirmed', false],
            ['watchlist_function_used', 'BROKEN'],
            ['primary_candidate_code', 'BROKEN'],
            ['observation_metric_result_review_contract.market_outcome_metrics_available', true],
            ['publication_and_rollout_result_review_safety_summary.free_publication_allowed', true],
            ['next_post_rollout_observation_operator_go_no_go_decision.same_topic_c166_continues', false],
            ['c166_candidate_post_rollout_observation_result_scorecard.2.ready_for_operator_go_no_go_review', true],
        ];
    }

    public function test_c166_operator_go_is_read_only_and_does_not_infer_market_metrics(): void
    {
        $result = $this->runService();
        $contract = $result['observation_metric_operator_decision_contract'];

        $this->assertTrue($result['controlled_rollout_active']);
        $this->assertTrue($result['plan_confirm_mutated']);
        $this->assertTrue($result['live_plan_confirm_rollout_executed']);
        $this->assertFalse($result['new_rollout_executed']);
        $this->assertFalse($result['new_plan_confirm_mutation_executed']);
        $this->assertFalse($result['new_catalog_read_executed']);
        $this->assertFalse($result['watchlist_function_invoked_by_operator_review']);
        $this->assertFalse($result['production_config_mutated']);
        $this->assertFalse($result['weekly_swing_watchlist_official_output_published']);
        $this->assertFalse($result['weekly_swing_watchlist_publication_allowed']);
        $this->assertFalse($result['weekly_swing_watchlist_unrestricted_publication_allowed']);
        $this->assertFalse($result['market_outcome_metrics_available']);
        $this->assertFalse($result['price_performance_evaluated']);
        $this->assertFalse($result['recommendation_quality_evaluated']);
        $this->assertFalse($result['market_metrics_inferred_by_operator_review']);
        $this->assertFalse($contract['market_metrics_inferred_by_operator_review']);
        $this->assertTrue($contract['operator_decision_must_not_claim_unavailable_market_performance']);
    }

    public function test_c166_operator_preserves_candidate_and_function_scope(): void
    {
        $result = $this->runService();
        $scorecard = $result['c166_candidate_post_rollout_observation_operator_go_no_go_scorecard'];

        $this->assertSame('CONTROLLED_WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATION', $result['watchlist_function_used']);
        $this->assertSame('PLAN_CONFIRM_CONTROLLED_ROLLOUT_ACTIVE', $result['watchlist_function_runtime_mode']);
        $this->assertTrue($result['watchlist_function_primary_candidate_observed']);
        $this->assertTrue($result['watchlist_function_backup_candidate_observed']);
        $this->assertFalse($result['watchlist_function_comparator_candidate_observed']);
        $this->assertTrue($scorecard[0]['ready_for_go_decision_finalization']);
        $this->assertTrue($scorecard[1]['ready_for_go_decision_finalization']);
        $this->assertFalse($scorecard[2]['ready_for_go_decision_finalization']);
        $this->assertTrue($result['a01_remains_comparator_only']);
        $this->assertSame(2, $result['rollout_state_record_count']);
    }

    public function test_c166_operator_contains_required_audit_sections(): void
    {
        $result = $this->runService();
        $run = json_decode((string) file_get_contents($this->output), true);

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        foreach ([
            'source_artifact_locks', 'c166_result_review_lock_validation_summary',
            'c166_post_rollout_observation_result_review_carry_forward_summary',
            'observation_metric_operator_decision_contract', 'watchlist_function_operator_review_summary',
            'candidate_scope_freeze_summary', 'publication_and_rollout_safety_summary',
            'operator_decision_validation_summary', 'temporary_negative_artifact_guard_summary',
            'c166_post_rollout_observation_operator_go_no_go_decision',
            'next_post_rollout_observation_go_decision_finalization_decision',
            'weekly_swing_watchlist_plan_confirm_controlled_rollout_post_rollout_observation_operator_go_no_go_manifest',
            'weekly_swing_watchlist_plan_confirm_controlled_rollout_post_rollout_observation_operator_go_no_go_checklist',
            'c166_candidate_post_rollout_observation_operator_go_no_go_scorecard',
            'progress_summary', 'planned_next_summary', 'diagnostics', 'failure_attribution_summary',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }

        $this->assertSame([], $run['weekly_swing_watchlist_plan_confirm_controlled_rollout_post_rollout_observation_operator_go_no_go_manifest']['official_weekly_swing_stock_recommendations']);
    }

    public function test_c166_operator_is_deterministic_and_does_not_mutate_source_or_config(): void
    {
        $sourceSha = strtoupper(sha1((string) file_get_contents(self::RESULT)));
        $config = (string) file_get_contents('config/watchlist.php');
        $first = $this->runService(['createdAt' => '2026-07-19T00:00:00+00:00']);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c166-post-rollout-observation-operator-go-no-go-review-second.json';
        $this->tmpFiles[] = $secondOutput;
        $second = $this->runService(['output' => $secondOutput, 'createdAt' => '2026-07-19T00:00:00+00:00']);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
        $this->assertSame($sourceSha, strtoupper(sha1((string) file_get_contents(self::RESULT))));
        $this->assertSame($config, (string) file_get_contents('config/watchlist.php'));
    }

    public function test_c166_operator_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c166-post-rollout-observation-operator-go-no-go-negative-remains-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');
        $result = $this->runService();

        $this->assertStringEndsWith('_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS', $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
    }

    private function runService(array $options = []): array
    {
        $service = new WatchlistBacktestC166WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutPostRolloutObservationOperatorGoNoGoReviewService();

        return $service->execute(
            (string) ($options['result'] ?? self::RESULT),
            (string) ($options['expectedResultHash'] ?? self::RESULT_HASH),
            (string) ($options['expectedResultSha1'] ?? self::RESULT_SHA1),
            (string) ($options['output'] ?? $this->output),
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'operator_decision_confirmed' => (bool) ($options['operatorDecisionConfirmed'] ?? true),
                'operator_decision' => (string) ($options['operatorDecision'] ?? 'GO'),
                'decision_reason' => (string) ($options['decisionReason'] ?? 'C166 control-plane observation result is valid for same-topic GO decision finalization without market-metric inference.'),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C166_OPERATOR_APPROVED_POST_ROLLOUT_OBSERVATION_GO'),
                'result_review_locked_confirmed' => (bool) ($options['resultReviewLockedConfirmed'] ?? true),
                'post_rollout_observation_result_confirmed' => (bool) ($options['postRolloutObservationResultConfirmed'] ?? true),
                'control_plane_result_confirmed' => (bool) ($options['controlPlaneResultConfirmed'] ?? true),
                'market_metrics_not_inferred_confirmed' => (bool) ($options['marketMetricsNotInferredConfirmed'] ?? true),
                'candidate_scope_confirmed' => (bool) ($options['candidateScopeConfirmed'] ?? true),
                'kill_switch_confirmed' => (bool) ($options['killSwitchConfirmed'] ?? true),
                'rollback_confirmed' => (bool) ($options['rollbackConfirmed'] ?? true),
                'production_config_unchanged_confirmed' => (bool) ($options['productionConfigUnchangedConfirmed'] ?? true),
                'free_publication_locked_confirmed' => (bool) ($options['freePublicationLockedConfirmed'] ?? true),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-19T00:00:00+00:00'),
            ]
        );
    }

    private function mutateResultAndReview(callable $mutator, string $name): array
    {
        $payload = json_decode((string) file_get_contents(self::RESULT), true);
        $payload = $mutator(is_array($payload) ? $payload : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c166-operator-result-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'result' => $path,
            'expectedResultHash' => (string) ($payload['artifact_hash'] ?? ''),
            'expectedResultSha1' => strtoupper(sha1((string) file_get_contents($path))),
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
            'storage/app/watchlist/backtest/.tmp-c166*operator*.json',
            'storage/app/watchlist/backtest/c166-*post-rollout-observation-operator-go-no-go*-test.json',
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

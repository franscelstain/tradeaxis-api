<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC166WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutPostRolloutObservationGoDecisionFinalizationReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC166WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutPostRolloutObservationGoDecisionFinalizationReviewTest extends TestCase
{
    private const OPERATOR = 'storage/app/watchlist/backtest/c166-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-post-rollout-observation-operator-go-no-go-review.json';
    private const OPERATOR_HASH = '20b00b9c2c53e33eee4f1501e8fddc7c8c379dda';
    private const OPERATOR_SHA1 = '3158EDB0120527909C12A557C36C2EC28C91B209';
    private const PASS_STATUS = 'C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW_PASSED_GO_FINALIZED_POST_ROLLOUT_OBSERVATION_CLOSED_READY_FOR_CONTROLLED_ROLLOUT_COMPLETION_BOUNDARY_REVIEW_PRIMARY_AND_BACKUP';
    private const NEXT_BOUNDARY = 'C167_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_COMPLETION_BOUNDARY_REVIEW';

    private string $output;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c166-post-rollout-observation-go-decision-finalization-review.json';
        $this->cleanupTemporaryArtifacts();
    }

    protected function tearDown(): void
    {
        $this->cleanupTemporaryArtifacts();
        parent::tearDown();
    }

    public function test_c166_finalization_closes_topic_and_opens_distinct_c167_boundary(): void
    {
        $result = $this->runService();

        $this->assertSame('C166_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION', $result['topic_code']);
        $this->assertSame('PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW', $result['topic_stage']);
        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertTrue($result['go_decision_finalized']);
        $this->assertTrue($result['post_rollout_observation_go_finalized']);
        $this->assertTrue($result['post_rollout_observation_topic_closed']);
        $this->assertTrue($result['c166_topic_complete']);
        $this->assertTrue($result['c166_topic_complete_after_finalization']);
        $this->assertTrue($result['c167_controlled_rollout_completion_boundary_required_next']);
        $this->assertTrue($result['ready_for_weekly_swing_watchlist_plan_confirm_controlled_rollout_completion_boundary_review']);
        $this->assertSame(self::NEXT_BOUNDARY, $result['next_step_recommendation']);
        $this->assertSame('C167_PLAN_CONFIRM_CONTROLLED_ROLLOUT_COMPLETION', $result['next_controlled_rollout_completion_boundary_decision']['next_topic_code']);
        $this->assertTrue($result['next_controlled_rollout_completion_boundary_decision']['c167_may_start']);
        $this->assertFileExists($this->output);
    }

    public function test_c166_finalization_locks_exact_operator_go_source(): void
    {
        $result = $this->runService();
        $lock = $result['source_artifact_locks'][0];

        $this->assertTrue($result['c166_operator_artifact_lock_valid']);
        $this->assertTrue($result['c166_operator_go_valid']);
        $this->assertSame(self::OPERATOR_HASH, $result['actual_c166_operator_hash']);
        $this->assertSame(self::OPERATOR_SHA1, $result['actual_c166_operator_file_sha1']);
        $this->assertSame(self::OPERATOR, $lock['path']);
        $this->assertTrue($lock['hash_match']);
        $this->assertTrue($lock['file_sha1_match']);
        $this->assertTrue($lock['convert_from_json_pass']);
    }

    public function test_c166_finalization_carries_go_without_new_runtime_or_market_claim(): void
    {
        $result = $this->runService();
        $contract = $result['observation_metric_finalization_contract'];

        $this->assertSame('GO', $result['operator_decision']);
        $this->assertTrue($result['operator_go_decision']);
        $this->assertTrue($result['post_rollout_observation_result_valid']);
        $this->assertTrue($result['control_plane_observation_result_stable']);
        $this->assertTrue($result['controlled_rollout_active']);
        $this->assertTrue($result['plan_confirm_mutated']);
        $this->assertTrue($result['live_plan_confirm_rollout_executed']);
        $this->assertFalse($result['new_rollout_executed']);
        $this->assertFalse($result['new_plan_confirm_mutation_executed']);
        $this->assertFalse($result['new_catalog_read_executed']);
        $this->assertFalse($result['watchlist_function_invoked_by_finalization']);
        $this->assertFalse($result['production_config_mutated']);
        $this->assertFalse($result['weekly_swing_watchlist_official_output_published']);
        $this->assertFalse($result['weekly_swing_watchlist_publication_allowed']);
        $this->assertFalse($result['weekly_swing_watchlist_unrestricted_publication_allowed']);
        $this->assertFalse($result['market_outcome_metrics_available']);
        $this->assertFalse($result['price_performance_evaluated']);
        $this->assertFalse($result['recommendation_quality_evaluated']);
        $this->assertFalse($result['market_metrics_inferred_by_finalization']);
        $this->assertTrue($contract['finalization_does_not_claim_unavailable_market_performance']);
    }

    public function test_c166_finalization_rejects_missing_operator_approval_or_reference(): void
    {
        $withoutApproval = $this->runService(['operatorApproved' => false]);
        $withoutReference = $this->runService(['approvalReference' => '']);
        $expected = 'C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';

        $this->assertSame($expected, $withoutApproval['status']);
        $this->assertSame($expected, $withoutReference['status']);
    }

    /**
     * @dataProvider confirmationProvider
     */
    public function test_c166_finalization_rejects_missing_confirmation(string $option, string $suffix): void
    {
        $result = $this->runService([$option => false]);

        $this->assertSame(
            'C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_'.$suffix,
            $result['status']
        );
    }

    public function confirmationProvider(): array
    {
        return [
            ['goDecisionFinalizationConfirmed', 'GO_DECISION_FINALIZATION_CONFIRMATION_MISSING'],
            ['postRolloutObservationTopicClosureConfirmed', 'POST_ROLLOUT_OBSERVATION_TOPIC_CLOSURE_CONFIRMATION_MISSING'],
            ['operatorGoLockedConfirmed', 'OPERATOR_GO_LOCK_CONFIRMATION_MISSING'],
            ['postRolloutObservationResultConfirmed', 'POST_ROLLOUT_OBSERVATION_RESULT_CONFIRMATION_MISSING'],
            ['controlPlaneResultConfirmed', 'CONTROL_PLANE_RESULT_CONFIRMATION_MISSING'],
            ['marketMetricsNotInferredConfirmed', 'MARKET_METRICS_NOT_INFERRED_CONFIRMATION_MISSING'],
            ['candidateScopeConfirmed', 'CANDIDATE_SCOPE_CONFIRMATION_MISSING'],
            ['killSwitchConfirmed', 'KILL_SWITCH_CONFIRMATION_MISSING'],
            ['rollbackConfirmed', 'ROLLBACK_CONFIRMATION_MISSING'],
            ['productionConfigUnchangedConfirmed', 'PRODUCTION_CONFIG_UNCHANGED_CONFIRMATION_MISSING'],
            ['freePublicationLockedConfirmed', 'FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING'],
            ['controlledRolloutCompletionBoundaryRequiredConfirmed', 'CONTROLLED_ROLLOUT_COMPLETION_BOUNDARY_REQUIREMENT_CONFIRMATION_MISSING'],
        ];
    }

    public function test_c166_finalization_rejects_operator_hash_sha_or_missing_source(): void
    {
        $badHash = $this->runService(['expectedOperatorHash' => 'bad-hash']);
        $badSha = $this->runService(['expectedOperatorSha1' => 'BADSHA1']);
        $missing = $this->runService([
            'operator' => 'storage/app/watchlist/backtest/.tmp-c166-missing-finalization-operator.json',
            'expectedOperatorHash' => 'missing',
            'expectedOperatorSha1' => 'missing',
        ]);

        $this->assertStringEndsWith('_REJECTED_C166_OPERATOR_ARTIFACT_LOCK_MISMATCH', $badHash['status']);
        $this->assertStringEndsWith('_REJECTED_C166_OPERATOR_FILE_SHA1_LOCK_MISMATCH', $badSha['status']);
        $this->assertStringEndsWith('_REJECTED_C166_OPERATOR_ARTIFACT_LOCK_MISMATCH', $missing['status']);
    }

    public function test_c166_finalization_rejects_duplicate_top_level_key(): void
    {
        $raw = (string) file_get_contents(self::OPERATOR);
        $path = 'storage/app/watchlist/backtest/.tmp-c166-finalization-operator-duplicate.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, preg_replace('/\{/', "{\"Run_Code\":\"DUPLICATE\",", $raw, 1));

        $result = $this->runService([
            'operator' => $path,
            'expectedOperatorSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);

        $this->assertStringEndsWith('_REJECTED_C166_OPERATOR_JSON_COMPATIBILITY_VIOLATION', $result['status']);
        $this->assertFalse($result['c166_operator_convert_from_json_pass']);
    }

    /**
     * @dataProvider invalidOperatorProvider
     */
    public function test_c166_finalization_rejects_invalid_operator_go_state(string $field, $value): void
    {
        $result = $this->mutateOperatorAndFinalize(function (array $payload) use ($field, $value): array {
            $this->setValueAt($payload, explode('.', $field), $value);

            return $payload;
        }, str_replace('.', '-', $field));

        $this->assertStringEndsWith('_REJECTED_C166_OPERATOR_GO_INVALID', $result['status'], $field);
    }

    public function invalidOperatorProvider(): array
    {
        return [
            ['status', 'BROKEN'],
            ['operator_decision', 'NO_GO'],
            ['operator_go_decision', false],
            ['operator_no_go_decision', true],
            ['go_decision_finalized', true],
            ['c166_topic_complete', true],
            ['post_rollout_observation_result_valid', false],
            ['control_plane_observation_result_stable', false],
            ['market_metrics_inferred_by_operator_review', true],
            ['new_rollout_executed', true],
            ['production_config_mutated', true],
            ['weekly_swing_watchlist_official_output_published', true],
            ['kill_switch_confirmed', false],
            ['watchlist_function_used', 'BROKEN'],
            ['primary_candidate_code', 'BROKEN'],
            ['observation_metric_operator_decision_contract.market_outcome_metrics_available', true],
            ['publication_and_rollout_safety_summary.free_publication_allowed', true],
            ['planned_next_summary.same_topic_c166_continues', false],
            ['next_post_rollout_observation_go_decision_finalization_decision.go_decision_finalization_required_next', false],
            ['c166_candidate_post_rollout_observation_operator_go_no_go_scorecard.2.ready_for_go_decision_finalization', true],
        ];
    }

    public function test_c166_finalization_preserves_candidate_and_function_scope_for_c167(): void
    {
        $result = $this->runService();
        $scorecard = $result['c166_candidate_post_rollout_observation_go_decision_finalization_scorecard'];

        $this->assertSame('CONTROLLED_WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATION', $result['watchlist_function_used']);
        $this->assertSame('PLAN_CONFIRM_CONTROLLED_ROLLOUT_ACTIVE', $result['watchlist_function_runtime_mode']);
        $this->assertTrue($result['primary_candidate_ready_for_plan_confirm_controlled_rollout_completion_boundary_review']);
        $this->assertTrue($result['backup_candidate_ready_for_plan_confirm_controlled_rollout_completion_boundary_review']);
        $this->assertFalse($result['comparator_candidate_ready_for_plan_confirm_controlled_rollout_completion_boundary_review']);
        $this->assertTrue($scorecard[0]['ready_for_controlled_rollout_completion_boundary']);
        $this->assertTrue($scorecard[1]['ready_for_controlled_rollout_completion_boundary']);
        $this->assertFalse($scorecard[2]['ready_for_controlled_rollout_completion_boundary']);
        $this->assertTrue($result['a01_remains_comparator_only']);
        $this->assertSame(2, $result['rollout_state_record_count']);
    }

    public function test_c166_finalization_contains_required_audit_sections(): void
    {
        $result = $this->runService();
        $run = json_decode((string) file_get_contents($this->output), true);

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        foreach ([
            'source_artifact_locks', 'c166_operator_lock_validation_summary', 'c166_operator_go_carry_forward_summary',
            'post_rollout_observation_finalization_guard_summary', 'observation_metric_finalization_contract',
            'watchlist_function_finalization_summary', 'candidate_scope_freeze_summary',
            'publication_and_rollout_safety_summary', 'operator_finalization_confirmation_summary',
            'temporary_negative_artifact_guard_summary', 'c166_go_decision_finalization_decision',
            'next_controlled_rollout_completion_boundary_decision',
            'weekly_swing_watchlist_plan_confirm_controlled_rollout_post_rollout_observation_go_decision_finalization_manifest',
            'weekly_swing_watchlist_plan_confirm_controlled_rollout_post_rollout_observation_go_decision_finalization_checklist',
            'c166_candidate_post_rollout_observation_go_decision_finalization_scorecard',
            'progress_summary', 'planned_next_summary', 'diagnostics', 'failure_attribution_summary',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }

        $this->assertSame([], $run['weekly_swing_watchlist_plan_confirm_controlled_rollout_post_rollout_observation_go_decision_finalization_manifest']['official_weekly_swing_stock_recommendations']);
    }

    public function test_c166_finalization_is_deterministic_and_does_not_mutate_source_or_config(): void
    {
        $sourceSha = strtoupper(sha1((string) file_get_contents(self::OPERATOR)));
        $config = (string) file_get_contents('config/watchlist.php');
        $first = $this->runService(['createdAt' => '2026-07-19T00:00:00+00:00']);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c166-post-rollout-observation-go-decision-finalization-review-second.json';
        $this->tmpFiles[] = $secondOutput;
        $second = $this->runService(['output' => $secondOutput, 'createdAt' => '2026-07-19T00:00:00+00:00']);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
        $this->assertSame($sourceSha, strtoupper(sha1((string) file_get_contents(self::OPERATOR))));
        $this->assertSame($config, (string) file_get_contents('config/watchlist.php'));
    }

    public function test_c166_finalization_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c166-post-rollout-observation-go-decision-finalization-negative-remains-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');
        $result = $this->runService();

        $this->assertStringEndsWith('_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS', $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
    }

    private function runService(array $options = []): array
    {
        $service = new WatchlistBacktestC166WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutPostRolloutObservationGoDecisionFinalizationReviewService();

        return $service->execute(
            (string) ($options['operator'] ?? self::OPERATOR),
            (string) ($options['expectedOperatorHash'] ?? self::OPERATOR_HASH),
            (string) ($options['expectedOperatorSha1'] ?? self::OPERATOR_SHA1),
            (string) ($options['output'] ?? $this->output),
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'go_decision_finalization_confirmed' => (bool) ($options['goDecisionFinalizationConfirmed'] ?? true),
                'post_rollout_observation_topic_closure_confirmed' => (bool) ($options['postRolloutObservationTopicClosureConfirmed'] ?? true),
                'operator_go_locked_confirmed' => (bool) ($options['operatorGoLockedConfirmed'] ?? true),
                'post_rollout_observation_result_confirmed' => (bool) ($options['postRolloutObservationResultConfirmed'] ?? true),
                'control_plane_result_confirmed' => (bool) ($options['controlPlaneResultConfirmed'] ?? true),
                'market_metrics_not_inferred_confirmed' => (bool) ($options['marketMetricsNotInferredConfirmed'] ?? true),
                'candidate_scope_confirmed' => (bool) ($options['candidateScopeConfirmed'] ?? true),
                'kill_switch_confirmed' => (bool) ($options['killSwitchConfirmed'] ?? true),
                'rollback_confirmed' => (bool) ($options['rollbackConfirmed'] ?? true),
                'production_config_unchanged_confirmed' => (bool) ($options['productionConfigUnchangedConfirmed'] ?? true),
                'free_publication_locked_confirmed' => (bool) ($options['freePublicationLockedConfirmed'] ?? true),
                'controlled_rollout_completion_boundary_required_confirmed' => (bool) ($options['controlledRolloutCompletionBoundaryRequiredConfirmed'] ?? true),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C166_OPERATOR_APPROVED_POST_ROLLOUT_OBSERVATION_GO_FINALIZATION'),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-19T00:00:00+00:00'),
            ]
        );
    }

    private function mutateOperatorAndFinalize(callable $mutator, string $name): array
    {
        $payload = json_decode((string) file_get_contents(self::OPERATOR), true);
        $payload = $mutator(is_array($payload) ? $payload : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c166-finalization-operator-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'operator' => $path,
            'expectedOperatorHash' => (string) ($payload['artifact_hash'] ?? ''),
            'expectedOperatorSha1' => strtoupper(sha1((string) file_get_contents($path))),
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
            'storage/app/watchlist/backtest/.tmp-c166*finalization*.json',
            'storage/app/watchlist/backtest/c166-*post-rollout-observation-go-decision-finalization*-test.json',
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

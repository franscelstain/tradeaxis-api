<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC165WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutGoDecisionFinalizationReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC165WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutGoDecisionFinalizationReviewTest extends TestCase
{
    private const OPERATOR = 'storage/app/watchlist/backtest/c165-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-operator-go-no-go-review.json';
    private const OPERATOR_HASH = '48cd9784bb9df5ceef8b47ca970996398d104f54';
    private const OPERATOR_SHA1 = '5457B6DDA328EF4FD1B0157E5857968D01965381';
    private const PASS_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_GO_DECISION_FINALIZATION_REVIEW_PASSED_GO_FINALIZED_CONTROLLED_ROLLOUT_CLOSED_READY_FOR_POST_ROLLOUT_OBSERVATION_REVIEW_PRIMARY_AND_BACKUP';
    private const NEXT_OBSERVATION = 'C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_REVIEW';

    private string $output;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c165-plan-confirm-controlled-rollout-go-decision-finalization-review.json';
        $this->cleanupTemporaryArtifacts();
    }

    protected function tearDown(): void
    {
        $this->cleanupTemporaryArtifacts();
        parent::tearDown();
    }

    public function test_c165_finalization_closes_topic_and_opens_distinct_c166_observation(): void
    {
        $result = $this->runService();

        $this->assertSame('C165_PLAN_CONFIRM_CONTROLLED_ROLLOUT', $result['topic_code']);
        $this->assertSame('PLAN_CONFIRM_CONTROLLED_ROLLOUT_GO_DECISION_FINALIZATION_REVIEW', $result['topic_stage']);
        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertTrue($result['go_decision_finalized']);
        $this->assertTrue($result['controlled_rollout_go_finalized']);
        $this->assertTrue($result['controlled_rollout_topic_closed']);
        $this->assertTrue($result['c165_topic_complete']);
        $this->assertTrue($result['c165_topic_complete_after_finalization']);
        $this->assertTrue($result['c166_post_rollout_observation_required_next']);
        $this->assertTrue($result['ready_for_weekly_swing_watchlist_plan_confirm_controlled_rollout_post_rollout_observation_review']);
        $this->assertSame(self::NEXT_OBSERVATION, $result['next_step_recommendation']);
        $this->assertSame('C166_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION', $result['next_post_rollout_observation_decision']['next_topic_code']);
        $this->assertTrue($result['next_post_rollout_observation_decision']['c166_may_start']);
        $this->assertFileExists($this->output);
    }

    public function test_c165_finalization_carries_go_and_rollout_result_without_new_runtime_action(): void
    {
        $result = $this->runService();

        $this->assertSame('GO', $result['operator_decision']);
        $this->assertTrue($result['operator_go_decision']);
        $this->assertTrue($result['controlled_rollout_result_valid']);
        $this->assertTrue($result['rollout_state_result_valid']);
        $this->assertTrue($result['execution_rollout_state_integrity_valid']);
        $this->assertTrue($result['controlled_rollout_active']);
        $this->assertTrue($result['plan_confirm_mutated']);
        $this->assertTrue($result['plan_confirm_runtime_reads_activated_catalog']);
        $this->assertTrue($result['live_plan_confirm_rollout_executed']);
        $this->assertFalse($result['new_rollout_executed']);
        $this->assertFalse($result['new_plan_confirm_mutation_executed']);
        $this->assertFalse($result['new_catalog_read_executed']);
        $this->assertFalse($result['watchlist_function_invoked_by_finalization']);
        $this->assertFalse($result['production_config_mutated']);
        $this->assertFalse($result['weekly_swing_watchlist_official_output_published']);
        $this->assertFalse($result['weekly_swing_watchlist_publication_allowed']);
        $this->assertFalse($result['weekly_swing_watchlist_unrestricted_publication_allowed']);
    }

    public function test_c165_finalization_rejects_missing_operator_approval_or_reference(): void
    {
        $withoutApproval = $this->runService(['operatorApproved' => false]);
        $withoutReference = $this->runService(['approvalReference' => '']);
        $expected = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_GO_DECISION_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';

        $this->assertSame($expected, $withoutApproval['status']);
        $this->assertSame($expected, $withoutReference['status']);
    }

    /**
     * @dataProvider confirmationProvider
     */
    public function test_c165_finalization_rejects_missing_confirmation(string $option, string $suffix): void
    {
        $result = $this->runService([$option => false]);

        $this->assertSame('C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_GO_DECISION_FINALIZATION_REVIEW_REJECTED_'.$suffix, $result['status']);
    }

    public function confirmationProvider(): array
    {
        return [
            ['goDecisionFinalizationConfirmed', 'GO_DECISION_FINALIZATION_CONFIRMATION_MISSING'],
            ['controlledRolloutTopicClosureConfirmed', 'CONTROLLED_ROLLOUT_TOPIC_CLOSURE_CONFIRMATION_MISSING'],
            ['operatorGoLockedConfirmed', 'OPERATOR_GO_LOCK_CONFIRMATION_MISSING'],
            ['controlledRolloutResultConfirmed', 'CONTROLLED_ROLLOUT_RESULT_CONFIRMATION_MISSING'],
            ['killSwitchConfirmed', 'KILL_SWITCH_CONFIRMATION_MISSING'],
            ['rollbackConfirmed', 'ROLLBACK_CONFIRMATION_MISSING'],
            ['productionConfigUnchangedConfirmed', 'PRODUCTION_CONFIG_UNCHANGED_CONFIRMATION_MISSING'],
            ['freePublicationLockedConfirmed', 'FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING'],
            ['postRolloutObservationRequiredConfirmed', 'POST_ROLLOUT_OBSERVATION_REQUIREMENT_CONFIRMATION_MISSING'],
        ];
    }

    public function test_c165_finalization_rejects_operator_hash_sha_or_missing_source(): void
    {
        $badHash = $this->runService(['expectedOperatorHash' => 'bad-hash']);
        $badSha = $this->runService(['expectedOperatorSha1' => 'BADSHA1']);
        $missing = $this->runService([
            'operator' => 'storage/app/watchlist/backtest/.tmp-c165-missing-finalization-operator.json',
            'expectedOperatorHash' => 'missing',
            'expectedOperatorSha1' => 'missing',
        ]);

        $this->assertStringEndsWith('_REJECTED_C165_OPERATOR_ARTIFACT_LOCK_MISMATCH', $badHash['status']);
        $this->assertStringEndsWith('_REJECTED_C165_OPERATOR_FILE_SHA1_LOCK_MISMATCH', $badSha['status']);
        $this->assertStringEndsWith('_REJECTED_C165_OPERATOR_ARTIFACT_LOCK_MISMATCH', $missing['status']);
    }

    public function test_c165_finalization_rejects_duplicate_top_level_key(): void
    {
        $raw = (string) file_get_contents(self::OPERATOR);
        $path = 'storage/app/watchlist/backtest/.tmp-c165-finalization-operator-duplicate.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, preg_replace('/\{/', "{\"Run_Code\":\"DUPLICATE\",", $raw, 1));

        $result = $this->runService([
            'operator' => $path,
            'expectedOperatorSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);

        $this->assertStringEndsWith('_REJECTED_C165_OPERATOR_JSON_COMPATIBILITY_VIOLATION', $result['status']);
    }

    /**
     * @dataProvider invalidOperatorProvider
     */
    public function test_c165_finalization_rejects_invalid_operator_go_state(string $field, $value): void
    {
        $result = $this->mutateOperatorAndFinalize(function (array $payload) use ($field, $value): array {
            $this->setValueAt($payload, explode('.', $field), $value);
            return $payload;
        }, str_replace('.', '-', $field));

        $this->assertStringEndsWith('_REJECTED_C165_OPERATOR_GO_INVALID', $result['status'], $field);
    }

    public function invalidOperatorProvider(): array
    {
        return [
            ['status', 'BROKEN'],
            ['operator_decision', 'NO_GO'],
            ['operator_go_decision', false],
            ['operator_no_go_decision', true],
            ['go_decision_finalized', true],
            ['controlled_rollout_result_valid', false],
            ['controlled_rollout_active', false],
            ['production_config_mutated', true],
            ['weekly_swing_watchlist_official_output_published', true],
            ['kill_switch_confirmed', false],
            ['rollback_confirmed', false],
            ['watchlist_function_used', 'BROKEN'],
            ['primary_candidate_code', 'BROKEN'],
            ['planned_next_summary.same_topic_c165_continues', false],
            ['next_plan_confirm_controlled_rollout_go_decision_finalization_decision.go_decision_finalization_required_next', false],
        ];
    }

    public function test_c165_finalization_preserves_candidate_scope_for_c166_observation(): void
    {
        $result = $this->runService();
        $scorecard = $result['c165_candidate_plan_confirm_controlled_rollout_go_decision_finalization_scorecard'];

        $this->assertSame('CONTROLLED_WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATION', $result['watchlist_function_used']);
        $this->assertSame('PLAN_CONFIRM_CONTROLLED_ROLLOUT_ACTIVE', $result['watchlist_function_runtime_mode']);
        $this->assertTrue($result['primary_candidate_ready_for_plan_confirm_controlled_rollout_post_rollout_observation_review']);
        $this->assertTrue($result['backup_candidate_ready_for_plan_confirm_controlled_rollout_post_rollout_observation_review']);
        $this->assertFalse($result['comparator_candidate_ready_for_plan_confirm_controlled_rollout_post_rollout_observation_review']);
        $this->assertTrue($scorecard[0]['ready_for_post_rollout_observation']);
        $this->assertTrue($scorecard[1]['ready_for_post_rollout_observation']);
        $this->assertFalse($scorecard[2]['ready_for_post_rollout_observation']);
        $this->assertTrue($result['a01_remains_comparator_only']);
    }

    public function test_c165_finalization_contains_required_audit_sections(): void
    {
        $result = $this->runService();
        $run = json_decode((string) file_get_contents($this->output), true);

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        foreach ([
            'source_artifact_locks',
            'c165_operator_lock_validation_summary',
            'c165_operator_go_carry_forward_summary',
            'controlled_rollout_finalization_guard_summary',
            'watchlist_function_finalization_summary',
            'candidate_scope_freeze_summary',
            'publication_and_rollout_safety_summary',
            'operator_finalization_confirmation_summary',
            'temporary_negative_artifact_guard_summary',
            'c165_go_decision_finalization_decision',
            'next_post_rollout_observation_decision',
            'weekly_swing_watchlist_plan_confirm_controlled_rollout_go_decision_finalization_manifest',
            'weekly_swing_watchlist_plan_confirm_controlled_rollout_go_decision_finalization_checklist',
            'c165_candidate_plan_confirm_controlled_rollout_go_decision_finalization_scorecard',
            'progress_summary',
            'planned_next_summary',
            'diagnostics',
            'failure_attribution_summary',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }
    }

    public function test_c165_finalization_is_deterministic_and_does_not_mutate_source_or_config(): void
    {
        $sourceHash = strtoupper(sha1((string) file_get_contents(self::OPERATOR)));
        $config = (string) file_get_contents('config/watchlist.php');
        $first = $this->runService(['createdAt' => '2026-07-18T00:00:00+00:00']);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c165-plan-confirm-controlled-rollout-go-decision-finalization-review-second.json';
        $this->tmpFiles[] = $secondOutput;
        $second = $this->runService(['output' => $secondOutput, 'createdAt' => '2026-07-18T00:00:00+00:00']);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
        $this->assertSame($sourceHash, strtoupper(sha1((string) file_get_contents(self::OPERATOR))));
        $this->assertSame($config, (string) file_get_contents('config/watchlist.php'));
    }

    public function test_c165_finalization_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c165-controlled-rollout-go-decision-finalization-negative-remains-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertStringEndsWith('_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS', $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
    }

    private function runService(array $options = []): array
    {
        $service = new WatchlistBacktestC165WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutGoDecisionFinalizationReviewService();

        return $service->execute(
            (string) ($options['operator'] ?? self::OPERATOR),
            (string) ($options['expectedOperatorHash'] ?? self::OPERATOR_HASH),
            (string) ($options['expectedOperatorSha1'] ?? self::OPERATOR_SHA1),
            (string) ($options['output'] ?? $this->output),
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'go_decision_finalization_confirmed' => (bool) ($options['goDecisionFinalizationConfirmed'] ?? true),
                'controlled_rollout_topic_closure_confirmed' => (bool) ($options['controlledRolloutTopicClosureConfirmed'] ?? true),
                'operator_go_locked_confirmed' => (bool) ($options['operatorGoLockedConfirmed'] ?? true),
                'controlled_rollout_result_confirmed' => (bool) ($options['controlledRolloutResultConfirmed'] ?? true),
                'kill_switch_confirmed' => (bool) ($options['killSwitchConfirmed'] ?? true),
                'rollback_confirmed' => (bool) ($options['rollbackConfirmed'] ?? true),
                'production_config_unchanged_confirmed' => (bool) ($options['productionConfigUnchangedConfirmed'] ?? true),
                'free_publication_locked_confirmed' => (bool) ($options['freePublicationLockedConfirmed'] ?? true),
                'post_rollout_observation_required_confirmed' => (bool) ($options['postRolloutObservationRequiredConfirmed'] ?? true),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C165_OPERATOR_APPROVED_CONTROLLED_ROLLOUT_GO_FINALIZATION'),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-18T00:00:00+00:00'),
            ]
        );
    }

    private function mutateOperatorAndFinalize(callable $mutator, string $name): array
    {
        $payload = json_decode((string) file_get_contents(self::OPERATOR), true);
        $payload = $mutator(is_array($payload) ? $payload : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c165-finalization-operator-'.$name.'.json';
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
        foreach (array_merge($this->tmpFiles, (array) glob('storage/app/watchlist/backtest/.tmp-c165*finalization*.json'), (array) glob('storage/app/watchlist/backtest/c165-*controlled-rollout-go-decision-finalization*-test.json')) as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
        $this->tmpFiles = [];
    }
}

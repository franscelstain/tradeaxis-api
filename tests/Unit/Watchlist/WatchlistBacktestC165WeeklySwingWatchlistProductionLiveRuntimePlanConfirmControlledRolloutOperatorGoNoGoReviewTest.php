<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC165WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutOperatorGoNoGoReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC165WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutOperatorGoNoGoReviewTest extends TestCase
{
    private const RESULT = 'storage/app/watchlist/backtest/c165-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-result-review.json';
    private const RESULT_HASH = 'a30b5b0eeab344e0d0283cb4164fd2a27b234802';
    private const RESULT_SHA1 = '664A639A2C8338F407BB0B34B9648733A0F6C94E';
    private const GO_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP_READY_FOR_GO_DECISION_FINALIZATION_REVIEW';
    private const NEXT_FINALIZATION = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_GO_DECISION_FINALIZATION_REVIEW';

    private string $output;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c165-plan-confirm-controlled-rollout-operator-go-no-go-review.json';
        $this->cleanupTemporaryArtifacts();
    }

    protected function tearDown(): void
    {
        $this->cleanupTemporaryArtifacts();
        parent::tearDown();
    }

    public function test_c165_operator_go_decision_passes_and_keeps_same_topic_for_finalization(): void
    {
        $result = $this->runService();

        $this->assertSame('C165_PLAN_CONFIRM_CONTROLLED_ROLLOUT', $result['topic_code']);
        $this->assertSame('PLAN_CONFIRM_CONTROLLED_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW', $result['topic_stage']);
        $this->assertSame(self::GO_STATUS, $result['status']);
        $this->assertTrue($result['operator_go_no_go_review_completed']);
        $this->assertTrue($result['operator_decision_recorded']);
        $this->assertSame('GO', $result['operator_decision']);
        $this->assertTrue($result['operator_go_decision']);
        $this->assertFalse($result['operator_no_go_decision']);
        $this->assertFalse($result['operator_hold_decision']);
        $this->assertTrue($result['ready_for_weekly_swing_watchlist_plan_confirm_controlled_rollout_go_decision_finalization_review']);
        $this->assertFalse($result['go_decision_finalized']);
        $this->assertFalse($result['c165_topic_complete']);
        $this->assertTrue($result['operator_kill_switch_confirmed']);
        $this->assertTrue($result['operator_rollback_confirmed']);
        $this->assertSame(self::NEXT_FINALIZATION, $result['next_step_recommendation']);
        $this->assertTrue($result['next_plan_confirm_controlled_rollout_go_decision_finalization_decision']['same_topic_c165_continues']);
        $this->assertFileExists($this->output);
    }

    public function test_c165_operator_no_go_is_completed_and_stops_progression(): void
    {
        $result = $this->runService(['operatorDecision' => 'NO_GO']);

        $this->assertStringContainsString('_COMPLETED_NO_GO_', $result['status']);
        $this->assertTrue($result['operator_go_no_go_review_completed']);
        $this->assertTrue($result['operator_no_go_decision']);
        $this->assertTrue($result['controlled_rollout_stopped_no_go']);
        $this->assertFalse($result['operator_go_decision']);
        $this->assertFalse($result['ready_for_weekly_swing_watchlist_plan_confirm_controlled_rollout_go_decision_finalization_review']);
        $this->assertSame('C165_CONTROLLED_ROLLOUT_PROGRESSION_STOPPED_NO_GO', $result['next_step_recommendation']);
    }

    public function test_c165_operator_hold_is_completed_and_defers_progression(): void
    {
        $result = $this->runService(['operatorDecision' => 'hold']);

        $this->assertStringContainsString('_COMPLETED_HOLD_', $result['status']);
        $this->assertTrue($result['operator_go_no_go_review_completed']);
        $this->assertTrue($result['operator_hold_decision']);
        $this->assertTrue($result['controlled_rollout_deferred_hold']);
        $this->assertFalse($result['operator_go_decision']);
        $this->assertSame('C165_CONTROLLED_ROLLOUT_PROGRESSION_DEFERRED_HOLD', $result['next_step_recommendation']);
    }

    public function test_c165_operator_rejects_missing_approval_or_reference(): void
    {
        $withoutApproval = $this->runService(['operatorApproved' => false]);
        $withoutReference = $this->runService(['approvalReference' => '']);
        $expected = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';

        $this->assertSame($expected, $withoutApproval['status']);
        $this->assertSame($expected, $withoutReference['status']);
    }

    public function test_c165_operator_rejects_invalid_unconfirmed_or_reasonless_decision(): void
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
    public function test_c165_operator_rejects_missing_confirmation(string $option, string $suffix): void
    {
        $result = $this->runService([$option => false]);

        $this->assertSame('C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_REJECTED_'.$suffix, $result['status']);
    }

    public function confirmationProvider(): array
    {
        return [
            ['resultReviewLockedConfirmed', 'RESULT_REVIEW_LOCK_CONFIRMATION_MISSING'],
            ['controlledRolloutResultConfirmed', 'CONTROLLED_ROLLOUT_RESULT_CONFIRMATION_MISSING'],
            ['controlledRolloutOnlyConfirmed', 'CONTROLLED_ROLLOUT_ONLY_CONFIRMATION_MISSING'],
            ['candidateScopeConfirmed', 'CANDIDATE_SCOPE_CONFIRMATION_MISSING'],
            ['killSwitchConfirmed', 'KILL_SWITCH_CONFIRMATION_MISSING'],
            ['rollbackConfirmed', 'ROLLBACK_CONFIRMATION_MISSING'],
            ['productionConfigUnchangedConfirmed', 'PRODUCTION_CONFIG_UNCHANGED_CONFIRMATION_MISSING'],
            ['freePublicationLockedConfirmed', 'FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING'],
        ];
    }

    public function test_c165_operator_rejects_result_hash_sha_or_missing_source(): void
    {
        $badHash = $this->runService(['expectedResultHash' => 'bad-hash']);
        $badSha = $this->runService(['expectedResultSha1' => 'BADSHA1']);
        $missing = $this->runService([
            'result' => 'storage/app/watchlist/backtest/.tmp-c165-missing-operator-result.json',
            'expectedResultHash' => 'missing',
            'expectedResultSha1' => 'missing',
        ]);

        $this->assertStringEndsWith('_REJECTED_C165_RESULT_REVIEW_ARTIFACT_LOCK_MISMATCH', $badHash['status']);
        $this->assertStringEndsWith('_REJECTED_C165_RESULT_REVIEW_FILE_SHA1_LOCK_MISMATCH', $badSha['status']);
        $this->assertStringEndsWith('_REJECTED_C165_RESULT_REVIEW_ARTIFACT_LOCK_MISMATCH', $missing['status']);
    }

    public function test_c165_operator_rejects_duplicate_top_level_key(): void
    {
        $raw = (string) file_get_contents(self::RESULT);
        $path = 'storage/app/watchlist/backtest/.tmp-c165-operator-result-duplicate.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, preg_replace('/\{/', "{\"Run_Code\":\"DUPLICATE\",", $raw, 1));

        $result = $this->runService([
            'result' => $path,
            'expectedResultSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);

        $this->assertStringEndsWith('_REJECTED_C165_RESULT_REVIEW_JSON_COMPATIBILITY_VIOLATION', $result['status']);
    }

    /**
     * @dataProvider invalidResultProvider
     */
    public function test_c165_operator_rejects_invalid_result_review_state(string $field, $value): void
    {
        $result = $this->mutateResultAndReview(function (array $payload) use ($field, $value): array {
            $this->setValueAt($payload, explode('.', $field), $value);
            return $payload;
        }, str_replace('.', '-', $field));

        $this->assertStringEndsWith('_REJECTED_C165_RESULT_REVIEW_INCOMPLETE', $result['status'], $field);
    }

    public function invalidResultProvider(): array
    {
        return [
            ['status', 'BROKEN'],
            ['controlled_rollout_result_valid', false],
            ['execution_rollout_state_integrity_valid', false],
            ['controlled_rollout_only', false],
            ['plan_confirm_mutated', false],
            ['production_config_mutated', true],
            ['weekly_swing_watchlist_official_output_published', true],
            ['kill_switch_confirmed', false],
            ['rollback_confirmed', false],
            ['watchlist_function_used', 'BROKEN'],
            ['primary_candidate_code', 'BROKEN'],
            ['comparator_candidate_ready_for_plan_confirm_controlled_rollout_operator_go_no_go_review', true],
            ['planned_next_summary.same_topic_c165_continues', false],
        ];
    }

    public function test_c165_operator_go_preserves_observed_rollout_but_executes_no_new_action(): void
    {
        $result = $this->runService();

        $this->assertTrue($result['controlled_rollout_executed']);
        $this->assertTrue($result['plan_confirm_mutated']);
        $this->assertTrue($result['plan_confirm_runtime_reads_activated_catalog']);
        $this->assertTrue($result['live_plan_confirm_rollout_executed']);
        $this->assertFalse($result['new_rollout_executed']);
        $this->assertFalse($result['new_plan_confirm_mutation_executed']);
        $this->assertFalse($result['new_catalog_read_executed']);
        $this->assertFalse($result['watchlist_function_invoked_by_operator_review']);
        $this->assertFalse($result['production_config_mutated']);
        $this->assertFalse($result['unrestricted_rollout_allowed']);
        $this->assertFalse($result['weekly_swing_watchlist_official_output_published']);
        $this->assertFalse($result['weekly_swing_watchlist_publication_allowed']);
        $this->assertFalse($result['weekly_swing_watchlist_unrestricted_publication_allowed']);
    }

    public function test_c165_operator_preserves_candidate_and_watchlist_function_scope(): void
    {
        $result = $this->runService();
        $scorecard = $result['c165_candidate_plan_confirm_controlled_rollout_operator_go_no_go_scorecard'];

        $this->assertSame('CONTROLLED_WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATION', $result['watchlist_function_used']);
        $this->assertSame('PLAN_CONFIRM_CONTROLLED_ROLLOUT_ACTIVE', $result['watchlist_function_runtime_mode']);
        $this->assertTrue($result['watchlist_function_primary_candidate_observed']);
        $this->assertTrue($result['watchlist_function_backup_candidate_observed']);
        $this->assertFalse($result['watchlist_function_comparator_candidate_observed']);
        $this->assertTrue($scorecard[0]['ready_for_go_decision_finalization']);
        $this->assertTrue($scorecard[1]['ready_for_go_decision_finalization']);
        $this->assertFalse($scorecard[2]['ready_for_go_decision_finalization']);
        $this->assertTrue($result['a01_remains_comparator_only']);
    }

    public function test_c165_operator_contains_required_audit_sections(): void
    {
        $result = $this->runService();
        $run = json_decode((string) file_get_contents($this->output), true);

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        foreach ([
            'source_artifact_locks',
            'c165_result_review_lock_validation_summary',
            'c165_controlled_rollout_result_review_carry_forward_summary',
            'watchlist_function_operator_review_summary',
            'candidate_scope_freeze_summary',
            'publication_and_rollout_safety_summary',
            'operator_decision_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'c165_controlled_rollout_operator_go_no_go_decision',
            'next_plan_confirm_controlled_rollout_go_decision_finalization_decision',
            'weekly_swing_watchlist_plan_confirm_controlled_rollout_operator_go_no_go_manifest',
            'weekly_swing_watchlist_plan_confirm_controlled_rollout_operator_go_no_go_checklist',
            'c165_candidate_plan_confirm_controlled_rollout_operator_go_no_go_scorecard',
            'progress_summary',
            'planned_next_summary',
            'diagnostics',
            'failure_attribution_summary',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }
    }

    public function test_c165_operator_is_deterministic_and_does_not_mutate_source_or_config(): void
    {
        $sourceHash = strtoupper(sha1((string) file_get_contents(self::RESULT)));
        $config = (string) file_get_contents('config/watchlist.php');
        $first = $this->runService(['createdAt' => '2026-07-18T00:00:00+00:00']);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c165-plan-confirm-controlled-rollout-operator-go-no-go-review-second.json';
        $this->tmpFiles[] = $secondOutput;
        $second = $this->runService(['output' => $secondOutput, 'createdAt' => '2026-07-18T00:00:00+00:00']);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
        $this->assertSame($sourceHash, strtoupper(sha1((string) file_get_contents(self::RESULT))));
        $this->assertSame($config, (string) file_get_contents('config/watchlist.php'));
    }

    public function test_c165_operator_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c165-controlled-rollout-operator-go-no-go-negative-remains-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertStringEndsWith('_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS', $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
    }

    private function runService(array $options = []): array
    {
        $service = new WatchlistBacktestC165WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutOperatorGoNoGoReviewService();

        return $service->execute(
            (string) ($options['result'] ?? self::RESULT),
            (string) ($options['expectedResultHash'] ?? self::RESULT_HASH),
            (string) ($options['expectedResultSha1'] ?? self::RESULT_SHA1),
            (string) ($options['output'] ?? $this->output),
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'operator_decision_confirmed' => (bool) ($options['operatorDecisionConfirmed'] ?? true),
                'operator_decision' => (string) ($options['operatorDecision'] ?? 'GO'),
                'decision_reason' => (string) ($options['decisionReason'] ?? 'C165 controlled rollout result is stable for same-topic GO decision finalization.'),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C165_OPERATOR_APPROVED_CONTROLLED_ROLLOUT_GO'),
                'result_review_locked_confirmed' => (bool) ($options['resultReviewLockedConfirmed'] ?? true),
                'controlled_rollout_result_confirmed' => (bool) ($options['controlledRolloutResultConfirmed'] ?? true),
                'controlled_rollout_only_confirmed' => (bool) ($options['controlledRolloutOnlyConfirmed'] ?? true),
                'candidate_scope_confirmed' => (bool) ($options['candidateScopeConfirmed'] ?? true),
                'kill_switch_confirmed' => (bool) ($options['killSwitchConfirmed'] ?? true),
                'rollback_confirmed' => (bool) ($options['rollbackConfirmed'] ?? true),
                'production_config_unchanged_confirmed' => (bool) ($options['productionConfigUnchangedConfirmed'] ?? true),
                'free_publication_locked_confirmed' => (bool) ($options['freePublicationLockedConfirmed'] ?? true),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-18T00:00:00+00:00'),
            ]
        );
    }

    private function mutateResultAndReview(callable $mutator, string $name): array
    {
        $payload = json_decode((string) file_get_contents(self::RESULT), true);
        $payload = $mutator(is_array($payload) ? $payload : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c165-operator-result-'.$name.'.json';
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
        foreach (array_merge($this->tmpFiles, (array) glob('storage/app/watchlist/backtest/.tmp-c165*operator*.json'), (array) glob('storage/app/watchlist/backtest/c165-*controlled-rollout-operator-go-no-go*-test.json')) as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
        $this->tmpFiles = [];
    }
}

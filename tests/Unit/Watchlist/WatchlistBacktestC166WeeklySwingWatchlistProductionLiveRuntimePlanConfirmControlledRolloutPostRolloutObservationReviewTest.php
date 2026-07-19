<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC166WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutPostRolloutObservationReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC166WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutPostRolloutObservationReviewTest extends TestCase
{
    private const FINALIZATION = 'storage/app/watchlist/backtest/c165-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-go-decision-finalization-review.json';
    private const FINALIZATION_HASH = '618a09a64ba295aee023edc8131452782e184a9f';
    private const FINALIZATION_SHA1 = '8EBDA0F4267597ED04F7AB798A1B1A227ACE4B9A';
    private const STATE = 'storage/app/watchlist/runtime/c165-weekly-swing-watchlist-plan-confirm-controlled-rollout-state.json';
    private const STATE_HASH = '3a8350955f6a1396f5225af3fddcfa31fa622904';
    private const STATE_SHA1 = '4B58D3A17B56136CF02BE1635FB2F16F12831722';
    private const PASS_STATUS = 'C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_REVIEW_PASSED_CONTROLLED_ROLLOUT_OBSERVED_READY_FOR_POST_ROLLOUT_OBSERVATION_RESULT_REVIEW_PRIMARY_AND_BACKUP';
    private const NEXT_REVIEW = 'C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_RESULT_REVIEW';

    private string $output;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c166-post-rollout-observation-review.json';
        $this->cleanupTemporaryArtifacts();
    }

    protected function tearDown(): void
    {
        $this->cleanupTemporaryArtifacts();
        parent::tearDown();
    }

    public function test_c166_observation_captures_control_plane_snapshot_and_keeps_same_topic(): void
    {
        $result = $this->runService();

        $this->assertSame('C166_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION', $result['topic_code']);
        $this->assertSame('PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_REVIEW', $result['topic_stage']);
        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertTrue($result['post_rollout_observation_started']);
        $this->assertTrue($result['post_rollout_control_plane_snapshot_captured']);
        $this->assertTrue($result['controlled_rollout_observed']);
        $this->assertTrue($result['controlled_rollout_observation_stable']);
        $this->assertTrue($result['ready_for_weekly_swing_watchlist_plan_confirm_controlled_rollout_post_rollout_observation_result_review']);
        $this->assertFalse($result['c166_topic_complete']);
        $this->assertTrue($result['c166_topic_number_retained_for_observation_result_review']);
        $this->assertSame(self::NEXT_REVIEW, $result['next_step_recommendation']);
        $this->assertTrue($result['next_post_rollout_observation_result_review_decision']['same_topic_c166_continues']);
        $this->assertFileExists($this->output);
    }

    public function test_c166_observation_does_not_claim_unavailable_market_metrics(): void
    {
        $result = $this->runService();
        $contract = $result['observation_scope_and_metric_contract'];

        $this->assertSame('LOCKED_CONTROL_PLANE_RUNTIME_STATE_SNAPSHOT', $result['observation_basis']);
        $this->assertFalse($result['market_outcome_metrics_available']);
        $this->assertFalse($result['price_performance_evaluated']);
        $this->assertFalse($result['recommendation_quality_evaluated']);
        $this->assertFalse($contract['market_outcome_metrics_available']);
        $this->assertFalse($contract['price_performance_evaluated']);
        $this->assertFalse($contract['recommendation_quality_evaluated']);
        $this->assertTrue($contract['result_review_must_not_infer_unavailable_market_metrics']);
    }

    public function test_c166_observation_preserves_active_rollout_without_new_runtime_action(): void
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
        $this->assertFalse($result['production_config_mutated']);
        $this->assertFalse($result['unrestricted_rollout_allowed']);
        $this->assertFalse($result['weekly_swing_watchlist_official_output_published']);
        $this->assertFalse($result['weekly_swing_watchlist_publication_allowed']);
        $this->assertFalse($result['weekly_swing_watchlist_unrestricted_publication_allowed']);
    }

    public function test_c166_observation_rejects_missing_operator_approval_or_reference(): void
    {
        $withoutApproval = $this->runService(['operatorApproved' => false]);
        $withoutReference = $this->runService(['approvalReference' => '']);
        $expected = 'C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';

        $this->assertSame($expected, $withoutApproval['status']);
        $this->assertSame($expected, $withoutReference['status']);
    }

    /**
     * @dataProvider confirmationProvider
     */
    public function test_c166_observation_rejects_missing_confirmation(string $option, string $suffix): void
    {
        $result = $this->runService([$option => false]);

        $this->assertSame('C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_REVIEW_REJECTED_'.$suffix, $result['status']);
    }

    public function confirmationProvider(): array
    {
        return [
            ['postRolloutObservationConfirmed', 'POST_ROLLOUT_OBSERVATION_CONFIRMATION_MISSING'],
            ['controlledRolloutStateObservationConfirmed', 'CONTROLLED_ROLLOUT_STATE_OBSERVATION_CONFIRMATION_MISSING'],
            ['observationWindowConfirmed', 'OBSERVATION_WINDOW_CONFIRMATION_MISSING'],
            ['candidateScopeConfirmed', 'CANDIDATE_SCOPE_CONFIRMATION_MISSING'],
            ['killSwitchConfirmed', 'KILL_SWITCH_CONFIRMATION_MISSING'],
            ['rollbackConfirmed', 'ROLLBACK_CONFIRMATION_MISSING'],
            ['productionConfigUnchangedConfirmed', 'PRODUCTION_CONFIG_UNCHANGED_CONFIRMATION_MISSING'],
            ['freePublicationLockedConfirmed', 'FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING'],
        ];
    }

    /**
     * @dataProvider sourceLockProvider
     */
    public function test_c166_observation_rejects_source_lock_mismatch(string $option, string $value, string $suffix): void
    {
        $result = $this->runService([$option => $value]);

        $this->assertStringEndsWith('_REJECTED_'.$suffix, $result['status']);
    }

    public function sourceLockProvider(): array
    {
        return [
            ['expectedFinalizationHash', 'bad-hash', 'C165_FINALIZATION_ARTIFACT_LOCK_MISMATCH'],
            ['expectedFinalizationSha1', 'BADSHA1', 'C165_FINALIZATION_FILE_SHA1_LOCK_MISMATCH'],
            ['expectedStateHash', 'bad-hash', 'ROLLOUT_STATE_ARTIFACT_LOCK_MISMATCH'],
            ['expectedStateSha1', 'BADSHA1', 'ROLLOUT_STATE_FILE_SHA1_LOCK_MISMATCH'],
        ];
    }

    public function test_c166_observation_rejects_missing_source(): void
    {
        $result = $this->runService([
            'finalization' => 'storage/app/watchlist/backtest/.tmp-c166-missing-finalization.json',
            'expectedFinalizationHash' => 'missing',
            'expectedFinalizationSha1' => 'missing',
        ]);

        $this->assertStringEndsWith('_REJECTED_C165_FINALIZATION_ARTIFACT_LOCK_MISMATCH', $result['status']);
    }

    public function test_c166_observation_rejects_duplicate_top_level_key(): void
    {
        $raw = (string) file_get_contents(self::FINALIZATION);
        $path = 'storage/app/watchlist/backtest/.tmp-c166-finalization-duplicate.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, preg_replace('/\{/', "{\"Run_Code\":\"DUPLICATE\",", $raw, 1));

        $result = $this->runService([
            'finalization' => $path,
            'expectedFinalizationSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);

        $this->assertStringEndsWith('_REJECTED_C165_FINALIZATION_JSON_COMPATIBILITY_VIOLATION', $result['status']);
    }

    /**
     * @dataProvider invalidFinalizationProvider
     */
    public function test_c166_observation_rejects_invalid_finalization(string $field, $value): void
    {
        $result = $this->mutateSourceAndObserve('finalization', function (array $payload) use ($field, $value): array {
            $this->setValueAt($payload, explode('.', $field), $value);
            return $payload;
        }, 'final-'.str_replace('.', '-', $field));

        $this->assertStringEndsWith('_REJECTED_C165_FINALIZATION_INCOMPLETE', $result['status']);
    }

    public function invalidFinalizationProvider(): array
    {
        return [
            ['status', 'BROKEN'],
            ['go_decision_finalized', false],
            ['c165_topic_complete', false],
            ['controlled_rollout_active', false],
            ['production_config_mutated', true],
            ['weekly_swing_watchlist_official_output_published', true],
            ['watchlist_function_used', 'BROKEN'],
            ['primary_candidate_code', 'BROKEN'],
            ['next_post_rollout_observation_decision.c166_may_start', false],
        ];
    }

    /**
     * @dataProvider invalidStateProvider
     */
    public function test_c166_observation_rejects_invalid_rollout_state(string $field, $value): void
    {
        $result = $this->mutateSourceAndObserve('state', function (array $payload) use ($field, $value): array {
            $this->setValueAt($payload, explode('.', $field), $value);
            return $payload;
        }, 'state-'.str_replace('.', '-', $field));

        $this->assertStringEndsWith('_REJECTED_ROLLOUT_STATE_OBSERVATION_INVALID', $result['status']);
    }

    public function invalidStateProvider(): array
    {
        return [
            ['controlled_rollout_active', false],
            ['controlled_rollout_only', false],
            ['plan_confirm_mutated', false],
            ['production_config_mutated', true],
            ['free_publication_allowed', true],
            ['kill_switch_confirmed', false],
            ['rollback_confirmed', false],
            ['rollout_rows.0.candidate_code', 'BROKEN'],
            ['comparator_candidate.controlled_rollout_executed', true],
        ];
    }

    public function test_c166_observation_rejects_cross_source_record_count_mismatch(): void
    {
        $result = $this->mutateSourceAndObserve('finalization', function (array $payload): array {
            $payload['rollout_state_record_count'] = 99;
            return $payload;
        }, 'cross-count');

        $this->assertStringEndsWith('_REJECTED_FINALIZATION_ROLLOUT_STATE_SCOPE_MISMATCH', $result['status']);
    }

    public function test_c166_observation_preserves_candidate_and_function_scope(): void
    {
        $result = $this->runService();
        $scorecard = $result['c166_candidate_post_rollout_observation_scorecard'];

        $this->assertSame('CONTROLLED_WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATION', $result['watchlist_function_used']);
        $this->assertSame('PLAN_CONFIRM_CONTROLLED_ROLLOUT_ACTIVE', $result['watchlist_function_runtime_mode']);
        $this->assertTrue($scorecard[0]['ready_for_observation_result_review']);
        $this->assertTrue($scorecard[1]['ready_for_observation_result_review']);
        $this->assertFalse($scorecard[2]['ready_for_observation_result_review']);
        $this->assertTrue($result['a01_remains_comparator_only']);
        $this->assertSame(2, $result['rollout_state_record_count']);
    }

    public function test_c166_observation_contains_required_audit_sections(): void
    {
        $result = $this->runService();
        $run = json_decode((string) file_get_contents($this->output), true);

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        foreach ([
            'source_artifact_locks', 'source_lock_validation_summary', 'c165_finalization_carry_forward_summary',
            'rollout_state_observation_snapshot', 'observation_scope_and_metric_contract', 'watchlist_function_observation_summary',
            'candidate_scope_observation_summary', 'publication_and_rollout_safety_summary', 'operator_observation_confirmation_summary',
            'temporary_negative_artifact_guard_summary', 'next_post_rollout_observation_result_review_decision',
            'weekly_swing_watchlist_plan_confirm_controlled_rollout_post_rollout_observation_manifest',
            'weekly_swing_watchlist_plan_confirm_controlled_rollout_post_rollout_observation_checklist',
            'c166_candidate_post_rollout_observation_scorecard', 'progress_summary', 'planned_next_summary', 'diagnostics', 'failure_attribution_summary',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }
    }

    public function test_c166_observation_is_deterministic_and_does_not_mutate_sources_or_config(): void
    {
        $sourceHashes = $this->sourceHashes();
        $config = (string) file_get_contents('config/watchlist.php');
        $first = $this->runService(['createdAt' => '2026-07-18T00:00:00+00:00']);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c166-post-rollout-observation-review-second.json';
        $this->tmpFiles[] = $secondOutput;
        $second = $this->runService(['output' => $secondOutput, 'createdAt' => '2026-07-18T00:00:00+00:00']);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
        $this->assertSame($sourceHashes, $this->sourceHashes());
        $this->assertSame($config, (string) file_get_contents('config/watchlist.php'));
    }

    public function test_c166_observation_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c166-post-rollout-observation-review-negative-remains-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');
        $result = $this->runService();

        $this->assertStringEndsWith('_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS', $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
    }

    private function runService(array $options = []): array
    {
        $service = new WatchlistBacktestC166WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutPostRolloutObservationReviewService();

        return $service->execute(
            (string) ($options['finalization'] ?? self::FINALIZATION),
            (string) ($options['expectedFinalizationHash'] ?? self::FINALIZATION_HASH),
            (string) ($options['expectedFinalizationSha1'] ?? self::FINALIZATION_SHA1),
            (string) ($options['state'] ?? self::STATE),
            (string) ($options['expectedStateHash'] ?? self::STATE_HASH),
            (string) ($options['expectedStateSha1'] ?? self::STATE_SHA1),
            (string) ($options['output'] ?? $this->output),
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'post_rollout_observation_confirmed' => (bool) ($options['postRolloutObservationConfirmed'] ?? true),
                'controlled_rollout_state_observation_confirmed' => (bool) ($options['controlledRolloutStateObservationConfirmed'] ?? true),
                'observation_window_confirmed' => (bool) ($options['observationWindowConfirmed'] ?? true),
                'candidate_scope_confirmed' => (bool) ($options['candidateScopeConfirmed'] ?? true),
                'kill_switch_confirmed' => (bool) ($options['killSwitchConfirmed'] ?? true),
                'rollback_confirmed' => (bool) ($options['rollbackConfirmed'] ?? true),
                'production_config_unchanged_confirmed' => (bool) ($options['productionConfigUnchangedConfirmed'] ?? true),
                'free_publication_locked_confirmed' => (bool) ($options['freePublicationLockedConfirmed'] ?? true),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C166_OPERATOR_APPROVED_POST_ROLLOUT_OBSERVATION'),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-18T00:00:00+00:00'),
            ]
        );
    }

    private function mutateSourceAndObserve(string $source, callable $mutator, string $name): array
    {
        $map = [
            'finalization' => [self::FINALIZATION, 'artifact_hash', 'expectedFinalizationHash', 'expectedFinalizationSha1'],
            'state' => [self::STATE, 'rollout_state_hash', 'expectedStateHash', 'expectedStateSha1'],
        ];
        [$original, $hashField, $hashOption, $shaOption] = $map[$source];
        $payload = json_decode((string) file_get_contents($original), true);
        $payload = $mutator(is_array($payload) ? $payload : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c166-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([$source => $path, $hashOption => (string) ($payload[$hashField] ?? ''), $shaOption => strtoupper(sha1((string) file_get_contents($path)))]);
    }

    private function setValueAt(array &$source, array $path, $value): void
    {
        $current = &$source;
        foreach ($path as $index => $segment) {
            if ($index === count($path) - 1) { $current[$segment] = $value; return; }
            if (! isset($current[$segment]) || ! is_array($current[$segment])) { $current[$segment] = []; }
            $current = &$current[$segment];
        }
    }

    private function sourceHashes(): array
    {
        return [strtoupper(sha1((string) file_get_contents(self::FINALIZATION))), strtoupper(sha1((string) file_get_contents(self::STATE)))];
    }

    private function cleanupTemporaryArtifacts(): void
    {
        foreach (array_merge($this->tmpFiles, (array) glob('storage/app/watchlist/backtest/.tmp-c166*.json'), (array) glob('storage/app/watchlist/backtest/c166-*post-rollout-observation-review*-test.json')) as $path) {
            if (is_file($path)) { unlink($path); }
        }
        $this->tmpFiles = [];
    }
}

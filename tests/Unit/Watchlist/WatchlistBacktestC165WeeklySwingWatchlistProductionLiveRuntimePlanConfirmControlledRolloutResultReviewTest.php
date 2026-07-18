<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC165WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutResultReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC165WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutResultReviewTest extends TestCase
{
    private const EXECUTION = 'storage/app/watchlist/backtest/c165-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-execution.json';
    private const EXECUTION_HASH = '73dc9758d1baad52e7a8e56f6e0058e99b9f71f7';
    private const EXECUTION_SHA1 = '10B76E055119D1A9049F2D9EBA858E1B71A552BE';
    private const STATE = 'storage/app/watchlist/runtime/c165-weekly-swing-watchlist-plan-confirm-controlled-rollout-state.json';
    private const STATE_HASH = '3a8350955f6a1396f5225af3fddcfa31fa622904';
    private const STATE_SHA1 = '4B58D3A17B56136CF02BE1635FB2F16F12831722';
    private const PASS_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_RESULT_REVIEW_PASSED_READY_FOR_OPERATOR_GO_NO_GO_REVIEW_PRIMARY_AND_BACKUP';
    private const NEXT_REVIEW = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW';

    private string $output;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c165-plan-confirm-controlled-rollout-result-review.json';
        $this->cleanupTemporaryArtifacts();
    }

    protected function tearDown(): void
    {
        $this->cleanupTemporaryArtifacts();
        parent::tearDown();
    }

    public function test_c165_result_review_passes_and_keeps_same_topic_for_operator_go_no_go(): void
    {
        $result = $this->runService();

        $this->assertSame('C165_PLAN_CONFIRM_CONTROLLED_ROLLOUT', $result['topic_code']);
        $this->assertSame('PLAN_CONFIRM_CONTROLLED_ROLLOUT_RESULT_REVIEW', $result['topic_stage']);
        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertTrue($result['weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_result_review_pass']);
        $this->assertTrue($result['controlled_rollout_result_reviewed']);
        $this->assertTrue($result['controlled_rollout_result_valid']);
        $this->assertTrue($result['rollout_state_result_valid']);
        $this->assertTrue($result['execution_rollout_state_integrity_valid']);
        $this->assertTrue($result['c165_execution_lock_valid']);
        $this->assertTrue($result['rollout_state_lock_valid']);
        $this->assertTrue($result['all_required_source_locks_valid']);
        $this->assertTrue($result['ready_for_weekly_swing_watchlist_plan_confirm_controlled_rollout_operator_go_no_go_review']);
        $this->assertTrue($result['operator_go_no_go_review_required_next']);
        $this->assertTrue($result['c165_topic_number_retained_for_operator_go_no_go_review']);
        $this->assertSame(self::NEXT_REVIEW, $result['next_step_recommendation']);
        $this->assertTrue($result['next_plan_confirm_controlled_rollout_operator_go_no_go_decision']['same_topic_c165_continues']);
        $this->assertFalse($result['c165_topic_complete']);
        $this->assertFileExists($this->output);
    }

    public function test_c165_result_review_observes_execution_without_new_mutation_or_publication(): void
    {
        $result = $this->runService();

        $this->assertTrue($result['controlled_rollout_executed']);
        $this->assertTrue($result['plan_confirm_mutated']);
        $this->assertTrue($result['plan_confirm_runtime_reads_activated_catalog']);
        $this->assertTrue($result['live_plan_confirm_rollout_executed']);
        $this->assertFalse($result['new_rollout_executed']);
        $this->assertFalse($result['new_plan_confirm_mutation_executed']);
        $this->assertFalse($result['new_catalog_read_executed']);
        $this->assertFalse($result['production_config_mutated']);
        $this->assertFalse($result['unrestricted_rollout_allowed']);
        $this->assertFalse($result['weekly_swing_watchlist_official_output_published']);
        $this->assertFalse($result['weekly_swing_watchlist_publication_allowed']);
        $this->assertFalse($result['weekly_swing_watchlist_unrestricted_publication_allowed']);
    }

    public function test_c165_result_review_rejects_missing_operator_approval_or_reference(): void
    {
        $withoutApproval = $this->runService(['operatorApproved' => false]);
        $withoutReference = $this->runService(['approvalReference' => '']);
        $expected = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_RESULT_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';

        $this->assertSame($expected, $withoutApproval['status']);
        $this->assertSame($expected, $withoutReference['status']);
    }

    /**
     * @dataProvider confirmationProvider
     */
    public function test_c165_result_review_rejects_missing_confirmation(string $option, string $suffix): void
    {
        $result = $this->runService([$option => false]);

        $this->assertSame('C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_RESULT_REVIEW_REJECTED_'.$suffix, $result['status']);
    }

    public function confirmationProvider(): array
    {
        return [
            ['resultReviewConfirmed', 'RESULT_REVIEW_CONFIRMATION_MISSING'],
            ['executionResultConfirmed', 'CONTROLLED_ROLLOUT_EXECUTION_RESULT_CONFIRMATION_MISSING'],
            ['rolloutStateLockedConfirmed', 'ROLLOUT_STATE_LOCK_CONFIRMATION_MISSING'],
            ['controlledRolloutOnlyConfirmed', 'CONTROLLED_ROLLOUT_ONLY_CONFIRMATION_MISSING'],
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
    public function test_c165_result_review_rejects_source_hash_or_sha_lock_mismatch(string $option, string $value, string $suffix): void
    {
        $result = $this->runService([$option => $value]);

        $this->assertSame('C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_RESULT_REVIEW_REJECTED_'.$suffix, $result['status']);
    }

    public function sourceLockProvider(): array
    {
        return [
            ['expectedExecutionHash', 'bad-hash', 'C165_EXECUTION_ARTIFACT_LOCK_MISMATCH'],
            ['expectedExecutionSha1', 'BADSHA1', 'C165_EXECUTION_FILE_SHA1_LOCK_MISMATCH'],
            ['expectedStateHash', 'bad-hash', 'ROLLOUT_STATE_ARTIFACT_LOCK_MISMATCH'],
            ['expectedStateSha1', 'BADSHA1', 'ROLLOUT_STATE_FILE_SHA1_LOCK_MISMATCH'],
        ];
    }

    public function test_c165_result_review_rejects_missing_source(): void
    {
        $result = $this->runService([
            'execution' => 'storage/app/watchlist/backtest/.tmp-c165-missing-result-review-execution.json',
            'expectedExecutionHash' => 'missing',
            'expectedExecutionSha1' => 'missing',
        ]);

        $this->assertSame('C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_RESULT_REVIEW_REJECTED_C165_EXECUTION_ARTIFACT_LOCK_MISMATCH', $result['status']);
    }

    public function test_c165_result_review_rejects_duplicate_top_level_key(): void
    {
        $raw = (string) file_get_contents(self::EXECUTION);
        $path = 'storage/app/watchlist/backtest/.tmp-c165-result-review-execution-duplicate.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, preg_replace('/\{/', "{\"Run_Code\":\"DUPLICATE\",", $raw, 1));

        $result = $this->runService([
            'execution' => $path,
            'expectedExecutionSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);

        $this->assertSame('C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_RESULT_REVIEW_REJECTED_C165_EXECUTION_JSON_COMPATIBILITY_VIOLATION', $result['status']);
    }

    /**
     * @dataProvider invalidExecutionProvider
     */
    public function test_c165_result_review_rejects_invalid_execution_result(string $field, $value): void
    {
        $result = $this->mutateSourceAndReview('execution', function (array $payload) use ($field, $value): array {
            $this->setValueAt($payload, explode('.', $field), $value);
            return $payload;
        }, 'execution-'.str_replace('.', '-', $field));

        $this->assertSame('C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_RESULT_REVIEW_REJECTED_C165_EXECUTION_RESULT_INVALID', $result['status']);
    }

    public function invalidExecutionProvider(): array
    {
        return [
            ['status', 'BROKEN'],
            ['controlled_rollout_executed', false],
            ['plan_confirm_mutated', false],
            ['production_config_mutated', true],
            ['weekly_swing_watchlist_official_output_published', true],
            ['watchlist_function_used', 'BROKEN'],
            ['primary_candidate_code', 'BROKEN'],
        ];
    }

    /**
     * @dataProvider invalidStateProvider
     */
    public function test_c165_result_review_rejects_invalid_rollout_state(string $field, $value): void
    {
        $result = $this->mutateSourceAndReview('state', function (array $payload) use ($field, $value): array {
            $this->setValueAt($payload, explode('.', $field), $value);
            return $payload;
        }, 'state-'.str_replace('.', '-', $field));

        $this->assertSame('C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_RESULT_REVIEW_REJECTED_ROLLOUT_STATE_RESULT_INVALID', $result['status']);
    }

    public function invalidStateProvider(): array
    {
        return [
            ['controlled_rollout_only', false],
            ['live_plan_confirm_rollout_executed', false],
            ['unrestricted_rollout_allowed', true],
            ['production_config_mutated', true],
            ['free_publication_allowed', true],
            ['rollout_rows.0.candidate_code', 'BROKEN'],
            ['comparator_candidate.controlled_rollout_executed', true],
            ['kill_switch_confirmed', false],
            ['rollback_confirmed', false],
        ];
    }

    public function test_c165_result_review_rejects_cross_artifact_hash_mismatch(): void
    {
        $result = $this->mutateSourceAndReview('execution', function (array $payload): array {
            $payload['rollout_state_hash'] = 'different-state-hash';
            return $payload;
        }, 'cross-integrity');

        $this->assertSame('C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_RESULT_REVIEW_REJECTED_EXECUTION_ROLLOUT_STATE_INTEGRITY_MISMATCH', $result['status']);
    }

    public function test_c165_result_review_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c165-controlled-rollout-result-review-negative-remains-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame('C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_RESULT_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS', $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
    }

    public function test_c165_result_review_preserves_candidate_and_function_scope(): void
    {
        $result = $this->runService();
        $scorecard = $result['c165_candidate_plan_confirm_controlled_rollout_result_review_scorecard'];

        $this->assertSame('CONTROLLED_WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATION', $result['watchlist_function_used']);
        $this->assertSame('PLAN_CONFIRM_CONTROLLED_ROLLOUT_ACTIVE', $result['watchlist_function_runtime_mode']);
        $this->assertTrue($result['watchlist_function_invoked_during_execution']);
        $this->assertFalse($result['watchlist_function_invoked_by_result_review']);
        $this->assertTrue($result['watchlist_function_primary_candidate_observed']);
        $this->assertTrue($result['watchlist_function_backup_candidate_observed']);
        $this->assertFalse($result['watchlist_function_comparator_candidate_observed']);
        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $scorecard[0]['candidate_code']);
        $this->assertTrue($scorecard[0]['result_reviewed']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $scorecard[1]['candidate_code']);
        $this->assertTrue($scorecard[1]['result_reviewed']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $scorecard[2]['candidate_code']);
        $this->assertFalse($scorecard[2]['result_reviewed']);
        $this->assertTrue($result['a01_remains_comparator_only']);
        $this->assertSame(2, $result['rollout_state_record_count']);
    }

    public function test_c165_result_review_contains_required_audit_sections(): void
    {
        $result = $this->runService();
        $run = json_decode((string) file_get_contents($this->output), true);

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        foreach ([
            'source_artifact_locks',
            'source_lock_validation_summary',
            'c165_execution_result_summary',
            'rollout_state_result_summary',
            'execution_rollout_state_integrity_summary',
            'watchlist_function_result_review_summary',
            'candidate_scope_result_review_summary',
            'publication_and_rollout_safety_summary',
            'operator_confirmation_summary',
            'temporary_negative_artifact_guard_summary',
            'next_plan_confirm_controlled_rollout_operator_go_no_go_decision',
            'weekly_swing_watchlist_plan_confirm_controlled_rollout_result_review_manifest',
            'weekly_swing_watchlist_plan_confirm_controlled_rollout_result_review_checklist',
            'c165_candidate_plan_confirm_controlled_rollout_result_review_scorecard',
            'progress_summary',
            'planned_next_summary',
            'diagnostics',
            'failure_attribution_summary',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }
    }

    public function test_c165_result_review_is_deterministic_and_does_not_mutate_sources_or_config(): void
    {
        $sourceHashes = $this->sourceFileHashes();
        $config = (string) file_get_contents('config/watchlist.php');
        $first = $this->runService(['createdAt' => '2026-07-18T00:00:00+00:00']);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c165-plan-confirm-controlled-rollout-result-review-second.json';
        $this->tmpFiles[] = $secondOutput;
        $second = $this->runService(['output' => $secondOutput, 'createdAt' => '2026-07-18T00:00:00+00:00']);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
        $this->assertSame($sourceHashes, $this->sourceFileHashes());
        $this->assertSame($config, (string) file_get_contents('config/watchlist.php'));
    }

    private function runService(array $options = []): array
    {
        $service = new WatchlistBacktestC165WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutResultReviewService();

        return $service->execute(
            (string) ($options['execution'] ?? self::EXECUTION),
            (string) ($options['expectedExecutionHash'] ?? self::EXECUTION_HASH),
            (string) ($options['expectedExecutionSha1'] ?? self::EXECUTION_SHA1),
            (string) ($options['state'] ?? self::STATE),
            (string) ($options['expectedStateHash'] ?? self::STATE_HASH),
            (string) ($options['expectedStateSha1'] ?? self::STATE_SHA1),
            (string) ($options['output'] ?? $this->output),
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'result_review_confirmed' => (bool) ($options['resultReviewConfirmed'] ?? true),
                'controlled_rollout_execution_result_confirmed' => (bool) ($options['executionResultConfirmed'] ?? true),
                'rollout_state_locked_confirmed' => (bool) ($options['rolloutStateLockedConfirmed'] ?? true),
                'controlled_rollout_only_confirmed' => (bool) ($options['controlledRolloutOnlyConfirmed'] ?? true),
                'candidate_scope_confirmed' => (bool) ($options['candidateScopeConfirmed'] ?? true),
                'kill_switch_confirmed' => (bool) ($options['killSwitchConfirmed'] ?? true),
                'rollback_confirmed' => (bool) ($options['rollbackConfirmed'] ?? true),
                'production_config_unchanged_confirmed' => (bool) ($options['productionConfigUnchangedConfirmed'] ?? true),
                'free_publication_locked_confirmed' => (bool) ($options['freePublicationLockedConfirmed'] ?? true),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C165_OPERATOR_APPROVED_CONTROLLED_ROLLOUT_RESULT_REVIEW'),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-18T00:00:00+00:00'),
            ]
        );
    }

    private function mutateSourceAndReview(string $source, callable $mutator, string $name): array
    {
        $map = [
            'execution' => [self::EXECUTION, 'artifact_hash', 'expectedExecutionHash', 'expectedExecutionSha1'],
            'state' => [self::STATE, 'rollout_state_hash', 'expectedStateHash', 'expectedStateSha1'],
        ];
        [$original, $hashField, $hashOption, $shaOption] = $map[$source];
        $payload = json_decode((string) file_get_contents($original), true);
        $payload = $mutator(is_array($payload) ? $payload : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c165-result-review-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            $source => $path,
            $hashOption => (string) ($payload[$hashField] ?? ''),
            $shaOption => strtoupper(sha1((string) file_get_contents($path))),
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

    private function sourceFileHashes(): array
    {
        return [
            strtoupper(sha1((string) file_get_contents(self::EXECUTION))),
            strtoupper(sha1((string) file_get_contents(self::STATE))),
        ];
    }

    private function cleanupTemporaryArtifacts(): void
    {
        foreach (array_merge($this->tmpFiles, (array) glob('storage/app/watchlist/backtest/.tmp-c165*result-review*.json'), (array) glob('storage/app/watchlist/backtest/c165-*controlled-rollout-result-review*-test.json')) as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
        $this->tmpFiles = [];
    }
}

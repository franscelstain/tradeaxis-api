<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC167WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutCompletionBoundaryReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC167WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutCompletionBoundaryReviewTest extends TestCase
{
    private const SOURCE = 'storage/app/watchlist/backtest/c166-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-post-rollout-observation-go-decision-finalization-review.json';
    private const SOURCE_HASH = '299eb7f2978b8755351d28bb299249f0cb0d818f';
    private const SOURCE_SHA1 = '3E2CF7C226756EFD9F3AADBDDCAE3BD133D174BA';
    private const PASS_STATUS = 'C167_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_COMPLETION_BOUNDARY_REVIEW_PASSED_READY_FOR_CONTROLLED_ROLLOUT_COMPLETION_EXECUTION_PRIMARY_AND_BACKUP';
    private const NEXT_EXECUTION = 'C167_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_COMPLETION_EXECUTION';

    private string $output;
    private array $temporaryPaths = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c167-controlled-rollout-completion-boundary-review.json';
        $this->cleanup();
    }

    protected function tearDown(): void
    {
        $this->cleanup();
        parent::tearDown();
    }

    public function test_boundary_locks_c166_and_opens_same_topic_completion_execution(): void
    {
        $result = $this->runService();

        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame('C167_PLAN_CONFIRM_CONTROLLED_ROLLOUT_COMPLETION', $result['topic_code']);
        $this->assertSame('PLAN_CONFIRM_CONTROLLED_ROLLOUT_COMPLETION_BOUNDARY_REVIEW', $result['topic_stage']);
        $this->assertSame(self::NEXT_EXECUTION, $result['next_step_recommendation']);
        $this->assertTrue($result['controlled_rollout_completion_boundary_open']);
        $this->assertTrue($result['c167_topic_open']);
        $this->assertFalse($result['c167_topic_complete']);
        $this->assertTrue($result['c166_finalization_lock_valid']);
        $this->assertTrue($result['c166_finalization_state_valid']);
        $this->assertTrue($result['controlled_rollout_executed']);
        $this->assertTrue($result['plan_confirm_mutated']);
        $this->assertTrue($result['plan_confirm_runtime_reads_activated_catalog']);
        $this->assertFalse($result['new_rollout_executed']);
        $this->assertFalse($result['new_plan_confirm_mutation_executed']);
        $this->assertFalse($result['new_catalog_read_executed']);
        $this->assertFalse($result['watchlist_function_invoked_by_boundary']);
        $this->assertFalse($result['weekly_swing_watchlist_official_output_published']);
        $this->assertFalse($result['weekly_swing_watchlist_unrestricted_publication_allowed']);
        $this->assertSame([], $result['weekly_swing_watchlist_plan_confirm_controlled_rollout_completion_boundary_manifest']['official_weekly_swing_stock_recommendations']);
        $this->assertTrue($result['next_plan_confirm_controlled_rollout_completion_execution_decision']['same_topic_c167_continues']);
    }

    public function test_boundary_rejects_wrong_source_hash(): void
    {
        $result = $this->runService([], self::SOURCE, str_repeat('0', 40));

        $this->assertSame(
            'C167_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_COMPLETION_BOUNDARY_REVIEW_REJECTED_C166_FINALIZATION_ARTIFACT_LOCK_MISMATCH',
            $result['status']
        );
        $this->assertFalse($result['c166_finalization_hash_match']);
        $this->assertFalse($result['controlled_rollout_completion_boundary_open']);
    }

    public function test_boundary_rejects_missing_operator_approval(): void
    {
        $result = $this->runService(['operator_approved' => false]);

        $this->assertStringEndsWith('REJECTED_OPERATOR_APPROVAL_MISSING', $result['status']);
        $this->assertFalse($result['controlled_rollout_completion_boundary_open']);
    }

    public function test_boundary_rejects_incomplete_rollout_evidence_confirmation(): void
    {
        $result = $this->runService(['controlled_rollout_evidence_chain_complete_confirmed' => false]);

        $this->assertStringEndsWith('REJECTED_CONTROLLED_ROLLOUT_EVIDENCE_CHAIN_CONFIRMATION_MISSING', $result['status']);
        $this->assertFalse($result['controlled_rollout_completion_boundary_open']);
    }

    public function test_boundary_rejects_a_new_rollout_hidden_in_c166_evidence(): void
    {
        $source = json_decode((string) file_get_contents(self::SOURCE), true, 512, JSON_THROW_ON_ERROR);
        $source['new_rollout_executed'] = true;
        $source['artifact_hash'] = $this->stableHash($source);
        $fixture = $this->temporaryFixture('new-rollout', $source);

        $result = $this->runService([], $fixture['path'], $source['artifact_hash'], $fixture['sha1']);

        $this->assertStringEndsWith('REJECTED_C166_FINALIZATION_STATE_INVALID', $result['status']);
        $this->assertFalse($result['controlled_rollout_completion_boundary_open']);
    }

    public function test_boundary_hash_is_deterministic_for_identical_input(): void
    {
        $first = $this->runService(['created_at' => '2026-07-19T00:00:00+00:00']);
        $second = $this->runService(['created_at' => '2026-07-19T00:00:00+00:00']);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
        $this->assertSame($first['artifact_hash'], $this->stableHash($first));
    }

    private function runService(
        array $overrides = [],
        string $source = self::SOURCE,
        string $hash = self::SOURCE_HASH,
        string $sha1 = self::SOURCE_SHA1
    ): array {
        $options = array_merge([
            'overwrite' => true,
            'created_at' => '2026-07-19T00:00:00+00:00',
            'operator_approved' => true,
            'approval_reference' => 'C167-TEST-APPROVAL',
            'controlled_rollout_completion_boundary_confirmed' => true,
            'c166_finalization_locked_confirmed' => true,
            'controlled_rollout_evidence_chain_complete_confirmed' => true,
            'completion_execution_required_confirmed' => true,
            'market_metrics_not_inferred_confirmed' => true,
            'candidate_scope_confirmed' => true,
            'kill_switch_confirmed' => true,
            'rollback_confirmed' => true,
            'production_config_unchanged_confirmed' => true,
            'free_publication_locked_confirmed' => true,
        ], $overrides);

        return (new WatchlistBacktestC167WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutCompletionBoundaryReviewService())
            ->execute($source, $hash, $sha1, $this->output, $options);
    }

    private function temporaryFixture(string $suffix, array $payload): array
    {
        $path = 'storage/app/watchlist/backtest/.tmp-c167-'.$suffix.'.json';
        $raw = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL;
        file_put_contents($path, $raw);
        $this->temporaryPaths[] = $path;

        return ['path' => $path, 'sha1' => strtoupper(sha1($raw))];
    }

    private function stableHash(array $artifact): string
    {
        unset($artifact['artifact_hash'], $artifact['artifact_path']);
        $this->sortRecursive($artifact);

        return sha1(json_encode($artifact, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    private function sortRecursive(array &$value): void
    {
        foreach ($value as &$item) {
            if (is_array($item)) {
                $this->sortRecursive($item);
            }
        }
        unset($item);
        if (! array_is_list($value)) {
            ksort($value);
        }
    }

    private function cleanup(): void
    {
        foreach (array_merge([$this->output], $this->temporaryPaths) as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
        $this->temporaryPaths = [];
    }
}

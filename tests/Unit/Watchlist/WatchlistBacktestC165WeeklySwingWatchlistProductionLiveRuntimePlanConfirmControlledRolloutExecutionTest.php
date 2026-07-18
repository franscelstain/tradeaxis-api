<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC165WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutExecutionService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC165WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutExecutionTest extends TestCase
{
    private const BOUNDARY = 'storage/app/watchlist/backtest/c165-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-boundary-review.json';
    private const BOUNDARY_HASH = '11eca01c5c5cc071c9d61dcf04b2004923f4772f';
    private const BOUNDARY_SHA1 = '4391205D3732CC475FB37E518678EAB607F5CAB0';
    private const CATALOG = 'storage/app/watchlist/backtest/c68-production-catalog-activation-execution-review.json';
    private const CATALOG_HASH = '54145854758e22115e4b65a297e4c157d94c638d';
    private const CATALOG_SHA1 = '209E3225F37015DA348EC2DA9A0D6A3FFCC6E4F7';
    private const COMPLETION = 'storage/app/watchlist/output/c161-weekly-swing-watchlist-controlled-plan-confirm-completion.json';
    private const COMPLETION_HASH = 'e9862d9e7738d0558f107d978f329f97f14b3520';
    private const COMPLETION_SHA1 = 'AB9FC9F714339B78D68132222AC8C398BE7EE1B3';
    private const RUNTIME = 'storage/app/watchlist/runtime/weekly-swing-watchlist-production-live-runtime-activation-state.json';
    private const RUNTIME_HASH = '00cb935a8252efe340d5f6ec6ea6966d9645cff7';
    private const RUNTIME_SHA1 = '17E41FFC5C6EE00CCCB4DF555A22EF192F2FCCF4';
    private const PASS_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION_PASSED_CONTROLLED_ROLLOUT_EXECUTED_READY_FOR_RESULT_REVIEW_PRIMARY_AND_BACKUP';
    private const NEXT_REVIEW = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_RESULT_REVIEW';

    private string $output;
    private string $rolloutStateOutput;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c165-plan-confirm-controlled-rollout-execution.json';
        $this->rolloutStateOutput = 'storage/app/watchlist/runtime/.tmp-c165-plan-confirm-controlled-rollout-state.json';
        $this->cleanupTemporaryArtifacts();
    }

    protected function tearDown(): void
    {
        $this->cleanupTemporaryArtifacts();
        parent::tearDown();
    }

    public function test_c165_execution_runs_controlled_rollout_and_keeps_same_topic_for_result_review(): void
    {
        $result = $this->runService();

        $this->assertSame('C165_PLAN_CONFIRM_CONTROLLED_ROLLOUT', $result['topic_code']);
        $this->assertSame('PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION', $result['topic_stage']);
        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertTrue($result['weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_execution_pass']);
        $this->assertTrue($result['controlled_rollout_executed']);
        $this->assertTrue($result['controlled_rollout_active']);
        $this->assertTrue($result['plan_confirm_mutated']);
        $this->assertTrue($result['plan_confirm_runtime_reads_activated_catalog']);
        $this->assertTrue($result['live_plan_confirm_rollout_executed']);
        $this->assertFalse($result['production_config_mutated']);
        $this->assertSame(self::NEXT_REVIEW, $result['next_step_recommendation']);
        $this->assertTrue($result['next_plan_confirm_controlled_rollout_result_review_decision']['same_topic_c165_continues']);
        $this->assertFileExists($this->output);
        $this->assertFileExists($this->rolloutStateOutput);
    }

    public function test_c165_execution_rejects_missing_operator_approval_or_reference_without_rollout_state(): void
    {
        $withoutApproval = $this->runService(['operatorApproved' => false]);
        $withoutReference = $this->runService(['approvalReference' => '']);
        $expected = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION_REJECTED_OPERATOR_APPROVAL_MISSING';

        $this->assertSame($expected, $withoutApproval['status']);
        $this->assertSame($expected, $withoutReference['status']);
        $this->assertFileDoesNotExist($this->rolloutStateOutput);
    }

    /**
     * @dataProvider confirmationProvider
     */
    public function test_c165_execution_rejects_missing_confirmation(string $option, string $suffix): void
    {
        $result = $this->runService([$option => false]);

        $this->assertSame('C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION_REJECTED_'.$suffix, $result['status']);
        $this->assertFileDoesNotExist($this->rolloutStateOutput);
    }

    public function confirmationProvider(): array
    {
        return [
            ['controlledRolloutExecutionConfirmed', 'CONTROLLED_ROLLOUT_EXECUTION_CONFIRMATION_MISSING'],
            ['c165BoundaryLockedConfirmed', 'C165_BOUNDARY_LOCK_CONFIRMATION_MISSING'],
            ['activatedCatalogReadConfirmed', 'ACTIVATED_CATALOG_READ_CONFIRMATION_MISSING'],
            ['planConfirmControlledMutationConfirmed', 'PLAN_CONFIRM_CONTROLLED_MUTATION_CONFIRMATION_MISSING'],
            ['controlledRolloutOnlyConfirmed', 'CONTROLLED_ROLLOUT_ONLY_CONFIRMATION_MISSING'],
            ['killSwitchConfirmed', 'KILL_SWITCH_CONFIRMATION_MISSING'],
            ['rollbackConfirmed', 'ROLLBACK_CONFIRMATION_MISSING'],
            ['freePublicationLockedConfirmed', 'FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING'],
        ];
    }

    /**
     * @dataProvider sourceLockProvider
     */
    public function test_c165_execution_rejects_source_hash_or_sha_lock_mismatch(string $option, string $value, string $suffix): void
    {
        $result = $this->runService([$option => $value]);

        $this->assertSame('C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION_REJECTED_'.$suffix, $result['status']);
        $this->assertFileDoesNotExist($this->rolloutStateOutput);
    }

    public function sourceLockProvider(): array
    {
        return [
            ['expectedBoundaryHash', 'bad-hash', 'C165_BOUNDARY_ARTIFACT_LOCK_MISMATCH'],
            ['expectedBoundarySha1', 'BADSHA1', 'C165_BOUNDARY_FILE_SHA1_LOCK_MISMATCH'],
            ['expectedCatalogHash', 'bad-hash', 'ACTIVATED_CATALOG_ARTIFACT_LOCK_MISMATCH'],
            ['expectedCatalogSha1', 'BADSHA1', 'ACTIVATED_CATALOG_FILE_SHA1_LOCK_MISMATCH'],
            ['expectedCompletionHash', 'bad-hash', 'CONTROLLED_COMPLETION_ARTIFACT_LOCK_MISMATCH'],
            ['expectedCompletionSha1', 'BADSHA1', 'CONTROLLED_COMPLETION_FILE_SHA1_LOCK_MISMATCH'],
            ['expectedRuntimeHash', 'bad-hash', 'RUNTIME_ACTIVATION_STATE_LOCK_MISMATCH'],
            ['expectedRuntimeSha1', 'BADSHA1', 'RUNTIME_ACTIVATION_STATE_FILE_SHA1_LOCK_MISMATCH'],
        ];
    }

    public function test_c165_execution_rejects_missing_source_artifact(): void
    {
        $result = $this->runService([
            'boundary' => 'storage/app/watchlist/backtest/.tmp-c165-missing-boundary.json',
            'expectedBoundaryHash' => 'missing',
            'expectedBoundarySha1' => 'missing',
        ]);

        $this->assertSame('C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION_REJECTED_C165_BOUNDARY_ARTIFACT_LOCK_MISMATCH', $result['status']);
    }

    public function test_c165_execution_rejects_boundary_json_duplicate_key(): void
    {
        $raw = (string) file_get_contents(self::BOUNDARY);
        $path = 'storage/app/watchlist/backtest/.tmp-c165-execution-boundary-duplicate-key.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, preg_replace('/\{/', "{\"Run_Code\":\"DUPLICATE\",", $raw, 1));

        $result = $this->runService([
            'boundary' => $path,
            'expectedBoundarySha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);

        $this->assertSame('C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION_REJECTED_C165_BOUNDARY_JSON_COMPATIBILITY_VIOLATION', $result['status']);
    }

    /**
     * @dataProvider sourceStateProvider
     */
    public function test_c165_execution_rejects_invalid_source_state(string $source, string $field, $value, string $suffix): void
    {
        $result = $this->mutateSourceAndExecute($source, function (array $payload) use ($field, $value): array {
            $this->setValueAt($payload, explode('.', $field), $value);
            return $payload;
        }, 'state-'.str_replace('.', '-', $field));

        $this->assertSame('C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION_REJECTED_'.$suffix, $result['status'], $source.'.'.$field);
    }

    public function sourceStateProvider(): array
    {
        return [
            ['boundary', 'controlled_rollout_boundary_open', false, 'C165_BOUNDARY_STATE_INVALID'],
            ['boundary', 'next_plan_confirm_controlled_rollout_execution_decision.same_topic_c165_continues', false, 'C165_BOUNDARY_STATE_INVALID'],
            ['catalog', 'production_catalog_activated', false, 'ACTIVATED_CATALOG_STATE_INVALID'],
            ['catalog', 'production_catalog_activation_execution_decision.primary_candidate_code', 'BROKEN', 'ACTIVATED_CATALOG_STATE_INVALID'],
            ['completion', 'plan_confirm_completion_state', 'BROKEN', 'CONTROLLED_COMPLETION_STATE_INVALID'],
            ['completion', 'output_rows.0.candidate_code', 'BROKEN', 'CONTROLLED_COMPLETION_STATE_INVALID'],
            ['runtime', 'runtime_bridge_active', false, 'RUNTIME_ACTIVATION_STATE_INVALID'],
            ['runtime', 'primary_candidate_code', 'BROKEN', 'RUNTIME_ACTIVATION_STATE_INVALID'],
        ];
    }

    public function test_c165_execution_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c165-controlled-rollout-execution-negative-remains-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame('C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS', $result['status']);
        $this->assertTrue($result['temporary_negative_artifact_guard_summary']['temporary_negative_artifacts_remaining']);
        $this->assertFileDoesNotExist($this->rolloutStateOutput);
    }

    public function test_c165_execution_rollout_state_is_controlled_reversible_and_primary_backup_only(): void
    {
        $result = $this->runService();
        $state = json_decode((string) file_get_contents($this->rolloutStateOutput), true);

        $this->assertSame('weekly_swing_watchlist_plan_confirm_controlled_rollout_state', $state['rollout_state_type']);
        $this->assertSame($result['rollout_state_hash'], $state['rollout_state_hash']);
        $this->assertSame('PRIMARY_AND_BACKUP_ONLY', $state['controlled_rollout_scope']);
        $this->assertTrue($state['controlled_rollout_only']);
        $this->assertTrue($state['plan_confirm_mutated']);
        $this->assertTrue($state['plan_confirm_runtime_reads_activated_catalog']);
        $this->assertTrue($state['live_plan_confirm_rollout_executed']);
        $this->assertTrue($state['kill_switch_confirmed']);
        $this->assertTrue($state['rollback_confirmed']);
        $this->assertFalse($state['production_config_mutated']);
        $this->assertFalse($state['free_publication_allowed']);
        $this->assertFalse($state['unrestricted_publication_allowed']);
        $this->assertCount(2, $state['rollout_rows']);
        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $state['rollout_rows'][0]['candidate_code']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $state['rollout_rows'][1]['candidate_code']);
        $this->assertFalse($state['comparator_candidate']['controlled_rollout_executed']);
        $this->assertTrue($state['comparator_candidate']['a01_remains_comparator_only']);
    }

    public function test_c165_execution_records_required_sections_function_scope_and_publication_locks(): void
    {
        $result = $this->runService();
        $run = json_decode((string) file_get_contents($this->output), true);

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['rollout_state_hash']);
        $this->assertSame('CONTROLLED_WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATION', $result['watchlist_function_used']);
        $this->assertSame('PLAN_CONFIRM_CONTROLLED_ROLLOUT_ACTIVE', $result['watchlist_function_runtime_mode']);
        $this->assertTrue($result['watchlist_function_invoked']);
        $this->assertTrue($result['watchlist_function_primary_candidate_observed']);
        $this->assertTrue($result['watchlist_function_backup_candidate_observed']);
        $this->assertFalse($result['watchlist_function_comparator_candidate_observed']);
        $this->assertFalse($result['unrestricted_rollout_allowed']);
        $this->assertFalse($result['weekly_swing_watchlist_official_output_published']);
        $this->assertFalse($result['weekly_swing_watchlist_publication_allowed']);
        $this->assertFalse($result['weekly_swing_watchlist_unrestricted_publication_allowed']);

        foreach ([
            'source_artifact_locks',
            'source_lock_validation_summary',
            'c165_boundary_carry_forward_summary',
            'activated_catalog_read_summary',
            'controlled_completion_payload_summary',
            'runtime_activation_state_summary',
            'plan_confirm_controlled_rollout_execution_decision',
            'watchlist_function_execution_summary',
            'candidate_scope_freeze_summary',
            'operator_control_summary',
            'rollout_state_artifact_summary',
            'publication_and_rollout_safety_summary',
            'temporary_negative_artifact_guard_summary',
            'next_plan_confirm_controlled_rollout_result_review_decision',
            'weekly_swing_watchlist_plan_confirm_controlled_rollout_execution_manifest',
            'weekly_swing_watchlist_plan_confirm_controlled_rollout_execution_checklist',
            'c165_candidate_plan_confirm_controlled_rollout_execution_scorecard',
            'progress_summary',
            'planned_next_summary',
            'diagnostics',
            'failure_attribution_summary',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }
    }

    public function test_c165_execution_is_deterministic_and_does_not_mutate_sources_or_config(): void
    {
        $sourceHashes = $this->sourceFileHashes();
        $config = (string) file_get_contents('config/watchlist.php');
        $first = $this->runService(['createdAt' => '2026-07-18T00:00:00+00:00']);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c165-plan-confirm-controlled-rollout-execution-second.json';
        $this->tmpFiles[] = $secondOutput;
        $second = $this->runService([
            'output' => $secondOutput,
            'createdAt' => '2026-07-18T00:00:00+00:00',
        ]);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
        $this->assertSame($first['rollout_state_hash'], $second['rollout_state_hash']);
        $this->assertSame($sourceHashes, $this->sourceFileHashes());
        $this->assertSame($config, (string) file_get_contents('config/watchlist.php'));
    }

    private function runService(array $options = []): array
    {
        $service = new WatchlistBacktestC165WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutExecutionService();

        return $service->execute(
            (string) ($options['boundary'] ?? self::BOUNDARY),
            (string) ($options['expectedBoundaryHash'] ?? self::BOUNDARY_HASH),
            (string) ($options['expectedBoundarySha1'] ?? self::BOUNDARY_SHA1),
            (string) ($options['catalog'] ?? self::CATALOG),
            (string) ($options['expectedCatalogHash'] ?? self::CATALOG_HASH),
            (string) ($options['expectedCatalogSha1'] ?? self::CATALOG_SHA1),
            (string) ($options['completion'] ?? self::COMPLETION),
            (string) ($options['expectedCompletionHash'] ?? self::COMPLETION_HASH),
            (string) ($options['expectedCompletionSha1'] ?? self::COMPLETION_SHA1),
            (string) ($options['runtime'] ?? self::RUNTIME),
            (string) ($options['expectedRuntimeHash'] ?? self::RUNTIME_HASH),
            (string) ($options['expectedRuntimeSha1'] ?? self::RUNTIME_SHA1),
            (string) ($options['output'] ?? $this->output),
            (string) ($options['rolloutStateOutput'] ?? $this->rolloutStateOutput),
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'controlled_rollout_execution_confirmed' => (bool) ($options['controlledRolloutExecutionConfirmed'] ?? true),
                'c165_boundary_locked_confirmed' => (bool) ($options['c165BoundaryLockedConfirmed'] ?? true),
                'activated_catalog_read_confirmed' => (bool) ($options['activatedCatalogReadConfirmed'] ?? true),
                'plan_confirm_controlled_mutation_confirmed' => (bool) ($options['planConfirmControlledMutationConfirmed'] ?? true),
                'controlled_rollout_only_confirmed' => (bool) ($options['controlledRolloutOnlyConfirmed'] ?? true),
                'kill_switch_confirmed' => (bool) ($options['killSwitchConfirmed'] ?? true),
                'rollback_confirmed' => (bool) ($options['rollbackConfirmed'] ?? true),
                'free_publication_locked_confirmed' => (bool) ($options['freePublicationLockedConfirmed'] ?? true),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C165_OPERATOR_APPROVED_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION'),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-18T00:00:00+00:00'),
            ]
        );
    }

    private function mutateSourceAndExecute(string $source, callable $mutator, string $name): array
    {
        $map = [
            'boundary' => [self::BOUNDARY, 'artifact_hash', 'expectedBoundaryHash', 'expectedBoundarySha1'],
            'catalog' => [self::CATALOG, 'artifact_hash', 'expectedCatalogHash', 'expectedCatalogSha1'],
            'completion' => [self::COMPLETION, 'controlled_completion_hash', 'expectedCompletionHash', 'expectedCompletionSha1'],
            'runtime' => [self::RUNTIME, 'runtime_state_hash', 'expectedRuntimeHash', 'expectedRuntimeSha1'],
        ];
        [$original, $hashField, $hashOption, $shaOption] = $map[$source];
        $payload = json_decode((string) file_get_contents($original), true);
        $payload = $mutator(is_array($payload) ? $payload : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c165-execution-'.$source.'-'.$name.'.json';
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
            strtoupper(sha1((string) file_get_contents(self::BOUNDARY))),
            strtoupper(sha1((string) file_get_contents(self::CATALOG))),
            strtoupper(sha1((string) file_get_contents(self::COMPLETION))),
            strtoupper(sha1((string) file_get_contents(self::RUNTIME))),
        ];
    }

    private function cleanupTemporaryArtifacts(): void
    {
        foreach (array_merge($this->tmpFiles, (array) glob('storage/app/watchlist/backtest/.tmp-c165*.json'), (array) glob('storage/app/watchlist/runtime/.tmp-c165*.json'), (array) glob('storage/app/watchlist/backtest/c165-*controlled-rollout-execution*-test.json')) as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
        $this->tmpFiles = [];
    }
}

<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC151WeeklySwingWatchlistProductionLiveRuntimeActivationPostExecutionObservationReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC151WeeklySwingWatchlistProductionLiveRuntimeActivationPostExecutionObservationReviewTest extends TestCase
{
    private const C150_ARTIFACT = 'storage/app/watchlist/backtest/c150-weekly-swing-watchlist-production-live-runtime-activation-final-execution.json';
    private const C150_HASH = '0b3b5e57011d8d98fcd38c004fb8d94fb33ca9ad';
    private const C150_SHA1 = 'E25A4E0DF40F9E01E6B3270F2AE2C5FF1CF0A500';
    private const RUNTIME_STATE = 'storage/app/watchlist/runtime/weekly-swing-watchlist-production-live-runtime-activation-state.json';
    private const RUNTIME_STATE_HASH = '00cb935a8252efe340d5f6ec6ea6966d9645cff7';
    private const RUNTIME_STATE_SHA1 = '17E41FFC5C6EE00CCCB4DF555A22EF192F2FCCF4';
    private const PASS_STATUS = 'C151_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_REVIEW_PASSED_RUNTIME_ACTIVE_READY_FOR_OBSERVATION_RESULT_REVIEW_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C151_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const TEMP_NEGATIVE_STATUS = 'C151_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const NEXT_C152 = 'C152_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_RESULT_REVIEW';

    private string $output;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c151-production-live-runtime-activation-post-execution-observation-review.json';
        $this->cleanupC151TemporaryArtifacts();
        @unlink($this->output);
    }

    protected function tearDown(): void
    {
        @unlink($this->output);
        foreach ($this->tmpFiles as $path) {
            @unlink($path);
        }
        $this->cleanupC151TemporaryArtifacts();
        parent::tearDown();
    }

    public function test_c151_observes_active_runtime_bridge_live_output_and_defers_official_output_generation(): void
    {
        $result = $this->runService();

        $this->assertSame('C151_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_REVIEW', $result['run_code']);
        $this->assertSame('PR-39 / C151_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_REVIEW', $result['phase_label']);
        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame(self::PASS_STATUS, $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_production_live_runtime_activation_post_execution_observation_review_pass']);
        $this->assertTrue($result['production_live_runtime_activation_post_execution_observation_review_pass']);
        $this->assertTrue($result['ready_for_production_live_runtime_activation_post_execution_observation_result_review']);
        $this->assertTrue($result['production_live_runtime_activation_executed']);
        $this->assertTrue($result['runtime_bridge_active']);
        $this->assertTrue($result['weekly_swing_watchlist_runtime_active']);
        $this->assertTrue($result['weekly_swing_watchlist_live_output_enabled']);
        $this->assertTrue($result['weekly_swing_watchlist_live_recommendation_generation_allowed']);
        $this->assertFalse($result['weekly_swing_watchlist_official_output_generated']);
        $this->assertFalse($result['weekly_swing_watchlist_official_output_published']);
        $this->assertFalse($result['weekly_swing_watchlist_live_recommendation_generated']);
        $this->assertFalse($result['plan_confirm_mutated']);
        $this->assertFalse($result['plan_confirm_runtime_reads_activated_catalog']);
        $this->assertSame(self::NEXT_C152, $result['next_step_recommendation']);
        $this->assertFileExists($this->output);
    }

    public function test_c151_records_c150_and_runtime_state_locks(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();

        foreach ([
            'source_artifact_locks',
            'c150_lock_validation_summary',
            'runtime_state_lock_validation_summary',
            'post_execution_observation_summary',
            'runtime_state_observation_summary',
            'candidate_runtime_observation_scorecard',
            'output_generation_guard_summary',
            'plan_confirm_observation_summary',
            'temporary_negative_artifact_guard_summary',
            'documentation_hygiene_guard_summary',
            'progress_summary',
            'planned_next_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }

        $this->assertSame(self::C150_HASH, $result['expected_c150_hash']);
        $this->assertSame(self::C150_HASH, $result['actual_c150_hash']);
        $this->assertTrue($result['c150_hash_match']);
        $this->assertSame(self::C150_SHA1, $result['expected_c150_file_sha1']);
        $this->assertSame(self::C150_SHA1, $result['actual_c150_file_sha1']);
        $this->assertTrue($result['c150_file_sha1_match']);
        $this->assertSame(self::RUNTIME_STATE_HASH, $result['expected_runtime_state_hash']);
        $this->assertSame(self::RUNTIME_STATE_HASH, $result['actual_runtime_state_hash']);
        $this->assertTrue($result['runtime_state_hash_match']);
        $this->assertSame(self::RUNTIME_STATE_SHA1, $result['expected_runtime_state_file_sha1']);
        $this->assertSame(self::RUNTIME_STATE_SHA1, $result['actual_runtime_state_file_sha1']);
        $this->assertTrue($result['runtime_state_file_sha1_match']);
        $this->assertTrue($result['c150_lock_valid']);
        $this->assertTrue($result['runtime_state_lock_valid']);
        $this->assertTrue($result['runtime_state_observation_valid']);
    }

    public function test_c151_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->runService(['operatorApproved' => false]);
        $missingReference = $this->runService(['approvalReference' => '']);

        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingOperator['status']);
        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingReference['status']);
    }

    public function test_c151_rejects_missing_or_mismatched_c150_lock(): void
    {
        $missing = $this->runService([
            'c150Artifact' => 'storage/app/watchlist/backtest/missing-c150-for-c151.json',
            'expectedC150Hash' => 'missing',
            'expectedC150FileSha1' => 'missing',
        ]);
        $hashMismatch = $this->runService(['expectedC150Hash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedC150FileSha1' => 'BADSHA1']);

        $this->assertSame('C151_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_REVIEW_REJECTED_C150_ARTIFACT_LOCK_MISMATCH', $missing['status']);
        $this->assertSame('C151_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_REVIEW_REJECTED_C150_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C151_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_REVIEW_REJECTED_C150_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
    }

    public function test_c151_rejects_missing_or_mismatched_runtime_state_lock(): void
    {
        $missing = $this->runService([
            'runtimeState' => 'storage/app/watchlist/runtime/missing-c150-runtime-state-for-c151.json',
            'expectedRuntimeStateHash' => 'missing',
            'expectedRuntimeStateFileSha1' => 'missing',
        ]);
        $hashMismatch = $this->runService(['expectedRuntimeStateHash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedRuntimeStateFileSha1' => 'BADSHA1']);

        $this->assertSame('C151_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_REVIEW_REJECTED_RUNTIME_STATE_LOCK_MISMATCH', $missing['status']);
        $this->assertSame('C151_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_REVIEW_REJECTED_RUNTIME_STATE_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C151_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_REVIEW_REJECTED_RUNTIME_STATE_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
    }

    public function test_c151_rejects_c150_status_phase_or_next_recommendation_mismatch(): void
    {
        $status = $this->mutateC150AndExecute(function (array $c150): array {
            $c150['status'] = 'BROKEN_STATUS';
            return $c150;
        }, 'status-broken');
        $phase = $this->mutateC150AndExecute(function (array $c150): array {
            $c150['phase_label'] = 'BROKEN_PHASE';
            return $c150;
        }, 'phase-broken');
        $next = $this->mutateC150AndExecute(function (array $c150): array {
            $c150['next_step_recommendation'] = 'BROKEN_NEXT';
            $c150['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $c150;
        }, 'next-broken');

        $this->assertSame('C151_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_REVIEW_REJECTED_C150_STATUS_MISMATCH', $status['status']);
        $this->assertSame('C151_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_REVIEW_REJECTED_C150_PHASE_LABEL_MISMATCH', $phase['status']);
        $this->assertSame('C151_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_REVIEW_REJECTED_C150_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
    }

    /**
     * @dataProvider c150FinalExecutionMismatchProvider
     */
    public function test_c151_rejects_incomplete_c150_final_execution(string $field, $value): void
    {
        $result = $this->mutateC150AndExecute(function (array $c150) use ($field, $value): array {
            $c150[$field] = $value;
            return $c150;
        }, 'final-execution-'.$field);

        $this->assertSame('C151_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_REVIEW_REJECTED_C150_FINAL_EXECUTION_INCOMPLETE', $result['status'], $field);
    }

    public function c150FinalExecutionMismatchProvider(): array
    {
        return [
            ['weekly_swing_watchlist_production_live_runtime_activation_final_execution_pass', false],
            ['production_live_runtime_activation_executed', false],
            ['runtime_bridge_active', false],
            ['weekly_swing_watchlist_live_output_enabled', false],
            ['weekly_swing_watchlist_official_output_generated', true],
            ['weekly_swing_watchlist_official_output_published', true],
            ['weekly_swing_watchlist_live_recommendation_generated', true],
            ['plan_confirm_mutated', true],
            ['plan_confirm_runtime_reads_activated_catalog', true],
        ];
    }

    /**
     * @dataProvider runtimeStateObservationMismatchProvider
     */
    public function test_c151_rejects_runtime_state_observation_mismatch(string $field, $value): void
    {
        $result = $this->mutateRuntimeStateAndExecute(function (array $state) use ($field, $value): array {
            $state[$field] = $value;
            return $state;
        }, 'observation-'.$field);

        $this->assertSame('C151_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_REVIEW_REJECTED_RUNTIME_STATE_OBSERVATION_MISMATCH', $result['status'], $field);
    }

    public function runtimeStateObservationMismatchProvider(): array
    {
        return [
            ['production_live_runtime_activation_executed', false],
            ['runtime_bridge_active', false],
            ['weekly_swing_watchlist_live_output_enabled', false],
            ['weekly_swing_watchlist_official_output_generated', true],
            ['weekly_swing_watchlist_official_output_published', true],
            ['weekly_swing_watchlist_live_recommendation_generated', true],
            ['plan_confirm_mutated', true],
            ['plan_confirm_runtime_reads_activated_catalog', true],
        ];
    }

    public function test_c151_rejects_runtime_state_link_mismatch(): void
    {
        $phase = $this->mutateRuntimeStateAndExecute(function (array $state): array {
            $state['source_phase_label'] = 'BROKEN_PHASE';
            return $state;
        }, 'link-phase-broken');
        $activation = $this->mutateRuntimeStateAndExecute(function (array $state): array {
            $state['activation_reference'] = 'BROKEN_ACTIVATION_REFERENCE';
            return $state;
        }, 'link-activation-broken');

        $this->assertSame('C151_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_REVIEW_REJECTED_RUNTIME_STATE_LINK_MISMATCH', $phase['status']);
        $this->assertSame('C151_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_REVIEW_REJECTED_RUNTIME_STATE_LINK_MISMATCH', $activation['status']);
    }

    public function test_c151_rejects_candidate_scope_change_or_a01_promotion(): void
    {
        $candidate = $this->mutateC150AndExecute(function (array $c150): array {
            $c150['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $c150;
        }, 'candidate-primary');
        $a01 = $this->mutateC150AndExecute(function (array $c150): array {
            $c150['a01_remains_comparator_only'] = false;
            return $c150;
        }, 'candidate-a01');

        $this->assertSame('C151_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $candidate['status']);
        $this->assertSame('C151_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_REVIEW_REJECTED_C150_FINAL_EXECUTION_INCOMPLETE', $a01['status']);
    }

    public function test_c151_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c151-no-temporary-artifact-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame(self::TEMP_NEGATIVE_STATUS, $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c151_does_not_mutate_c150_artifact_runtime_state_or_config_defaults(): void
    {
        $beforeC150 = strtoupper(sha1((string) file_get_contents(self::C150_ARTIFACT)));
        $beforeRuntimeState = strtoupper(sha1((string) file_get_contents(self::RUNTIME_STATE)));
        $beforeConfig = (string) file_get_contents('config/watchlist.php');

        $this->runService();

        $this->assertSame($beforeC150, strtoupper(sha1((string) file_get_contents(self::C150_ARTIFACT))));
        $this->assertSame($beforeRuntimeState, strtoupper(sha1((string) file_get_contents(self::RUNTIME_STATE))));
        $this->assertSame($beforeConfig, (string) file_get_contents('config/watchlist.php'));
    }

    public function test_c151_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $first = $this->runService(['createdAt' => '2026-07-15T00:00:00+00:00']);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c151-production-live-runtime-activation-post-execution-observation-review-second.json';
        $this->tmpFiles[] = $secondOutput;
        $second = $this->runService([
            'output' => $secondOutput,
            'createdAt' => '2026-07-15T00:00:00+00:00',
        ]);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
    }

    private function runService(array $options = []): array
    {
        $service = new WatchlistBacktestC151WeeklySwingWatchlistProductionLiveRuntimeActivationPostExecutionObservationReviewService();

        return $service->execute(
            (string) ($options['c150Artifact'] ?? self::C150_ARTIFACT),
            (string) ($options['expectedC150Hash'] ?? self::C150_HASH),
            (string) ($options['expectedC150FileSha1'] ?? self::C150_SHA1),
            (string) ($options['runtimeState'] ?? self::RUNTIME_STATE),
            (string) ($options['expectedRuntimeStateHash'] ?? self::RUNTIME_STATE_HASH),
            (string) ($options['expectedRuntimeStateFileSha1'] ?? self::RUNTIME_STATE_SHA1),
            (string) ($options['output'] ?? $this->output),
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C151_OPERATOR_APPROVED_POST_EXECUTION_OBSERVATION_REVIEW'),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-15T00:00:00+00:00'),
            ]
        );
    }

    private function mutateC150AndExecute(callable $mutator, string $name): array
    {
        $c150 = json_decode((string) file_get_contents(self::C150_ARTIFACT), true);
        $c150 = $mutator(is_array($c150) ? $c150 : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c151-source-c150-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($c150, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'c150Artifact' => $path,
            'expectedC150Hash' => (string) ($c150['artifact_hash'] ?? ''),
            'expectedC150FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function mutateRuntimeStateAndExecute(callable $mutator, string $name): array
    {
        $state = json_decode((string) file_get_contents(self::RUNTIME_STATE), true);
        $state = $mutator(is_array($state) ? $state : []);
        $path = 'storage/app/watchlist/runtime/.tmp-c151-runtime-state-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'runtimeState' => $path,
            'expectedRuntimeStateHash' => (string) ($state['runtime_state_hash'] ?? ''),
            'expectedRuntimeStateFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function readOutput(): array
    {
        $decoded = json_decode((string) file_get_contents($this->output), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function cleanupC151TemporaryArtifacts(): void
    {
        foreach ((array) glob('storage/app/watchlist/backtest/c151-*test.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/.tmp-c151*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/runtime/.tmp-c151*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}

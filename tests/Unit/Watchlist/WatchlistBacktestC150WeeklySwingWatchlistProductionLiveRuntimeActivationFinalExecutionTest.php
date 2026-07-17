<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC150WeeklySwingWatchlistProductionLiveRuntimeActivationFinalExecutionService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC150WeeklySwingWatchlistProductionLiveRuntimeActivationFinalExecutionTest extends TestCase
{
    private const C149_ARTIFACT = 'storage/app/watchlist/backtest/c149-weekly-swing-watchlist-production-live-runtime-activation-operator-go-no-go-review.json';
    private const C149_HASH = '311898597454a6a1984f4ed84473ad52ba6859fb';
    private const C149_SHA1 = '3B14776D36FBC922782B332BDC55CE90B50188E5';
    private const PASS_STATUS = 'C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION_PASSED_LIVE_RUNTIME_BRIDGE_ACTIVE_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const ENABLEMENT_MISSING_STATUS = 'C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION_REJECTED_EXPLICIT_RUNTIME_ENABLEMENT_MISSING';
    private const ROLLBACK_OR_KILL_SWITCH_MISSING_STATUS = 'C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION_REJECTED_ROLLBACK_OR_KILL_SWITCH_CONFIRMATION_MISSING';
    private const TEMP_NEGATIVE_STATUS = 'C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const NEXT_C151 = 'C151_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_REVIEW';

    private string $output;
    private string $runtimeState;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c150-production-live-runtime-activation-final-execution.json';
        $this->runtimeState = 'storage/app/watchlist/runtime/.tmp-c150-production-live-runtime-activation-state.json';
        $this->cleanupC150TemporaryArtifacts();
        @unlink($this->output);
        @unlink($this->runtimeState);
    }

    protected function tearDown(): void
    {
        @unlink($this->output);
        @unlink($this->runtimeState);
        foreach ($this->tmpFiles as $path) {
            @unlink($path);
        }
        $this->cleanupC150TemporaryArtifacts();
        parent::tearDown();
    }

    public function test_c150_executes_final_activation_with_explicit_runtime_bridge_live_output_rollback_and_kill_switch_confirmation(): void
    {
        $result = $this->runService();

        $this->assertSame('C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION', $result['run_code']);
        $this->assertSame('PR-38 / C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION', $result['phase_label']);
        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame(self::PASS_STATUS, $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_production_live_runtime_activation_final_execution_pass']);
        $this->assertTrue($result['production_live_runtime_activation_final_execution_pass']);
        $this->assertTrue($result['production_live_runtime_activation_executed']);
        $this->assertTrue($result['production_ready']);
        $this->assertTrue($result['production_catalog_runtime_wired']);
        $this->assertTrue($result['production_runtime_wiring_executed']);
        $this->assertTrue($result['runtime_bridge_active']);
        $this->assertTrue($result['weekly_swing_watchlist_runtime_active']);
        $this->assertTrue($result['weekly_swing_watchlist_live_output_enabled']);
        $this->assertTrue($result['weekly_swing_watchlist_live_recommendation_generation_allowed']);
        $this->assertFalse($result['weekly_swing_watchlist_official_output_generated']);
        $this->assertFalse($result['weekly_swing_watchlist_official_output_published']);
        $this->assertFalse($result['weekly_swing_watchlist_live_recommendation_generated']);
        $this->assertFalse($result['plan_confirm_mutated']);
        $this->assertFalse($result['plan_confirm_runtime_reads_activated_catalog']);
        $this->assertTrue($result['c149_operator_go_no_go_valid']);
        $this->assertSame(self::NEXT_C151, $result['next_step_recommendation']);
        $this->assertFileExists($this->output);
        $this->assertFileExists($this->runtimeState);
    }

    public function test_c150_writes_runtime_state_with_active_bridge_and_live_output(): void
    {
        $result = $this->runService();
        $state = $this->readRuntimeState();

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['runtime_state_hash']);
        $this->assertSame($result['runtime_state_hash'], $state['runtime_state_hash']);
        $this->assertSame(self::C149_HASH, $state['source_c149_artifact_hash']);
        $this->assertTrue($state['production_live_runtime_activation_executed']);
        $this->assertTrue($state['runtime_bridge_active']);
        $this->assertTrue($state['weekly_swing_watchlist_live_output_enabled']);
        $this->assertFalse($state['weekly_swing_watchlist_official_output_generated']);
        $this->assertFalse($state['weekly_swing_watchlist_official_output_published']);
        $this->assertFalse($state['plan_confirm_mutated']);
        $this->assertFalse($state['plan_confirm_runtime_reads_activated_catalog']);
    }

    public function test_c150_rejects_missing_operator_approval_or_activation_reference(): void
    {
        $missingOperator = $this->runService(['operatorApproved' => false]);
        $missingReference = $this->runService(['activationReference' => '']);

        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingOperator['status']);
        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingReference['status']);
        $this->assertFileDoesNotExist($this->runtimeState);
    }

    public function test_c150_rejects_missing_explicit_runtime_or_live_output_enablement(): void
    {
        $missingBridge = $this->runService(['enableRuntimeBridge' => false]);
        $missingLiveOutput = $this->runService(['enableLiveOutput' => false]);

        $this->assertSame(self::ENABLEMENT_MISSING_STATUS, $missingBridge['status']);
        $this->assertSame(self::ENABLEMENT_MISSING_STATUS, $missingLiveOutput['status']);
        $this->assertFileDoesNotExist($this->runtimeState);
    }

    public function test_c150_rejects_missing_rollback_or_kill_switch_confirmation(): void
    {
        $missingRollback = $this->runService(['confirmRollback' => false]);
        $missingKillSwitch = $this->runService(['confirmKillSwitch' => false]);

        $this->assertSame(self::ROLLBACK_OR_KILL_SWITCH_MISSING_STATUS, $missingRollback['status']);
        $this->assertSame(self::ROLLBACK_OR_KILL_SWITCH_MISSING_STATUS, $missingKillSwitch['status']);
        $this->assertFileDoesNotExist($this->runtimeState);
    }

    public function test_c150_rejects_missing_or_mismatched_c149_artifact_lock(): void
    {
        $missing = $this->runService([
            'c149Artifact' => 'storage/app/watchlist/backtest/missing-c149-for-c150.json',
            'expectedC149Hash' => 'missing',
            'expectedC149FileSha1' => 'missing',
        ]);
        $hashMismatch = $this->runService(['expectedC149Hash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedC149FileSha1' => 'BADSHA1']);

        $this->assertSame('C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION_REJECTED_C149_ARTIFACT_LOCK_MISMATCH', $missing['status']);
        $this->assertSame('C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION_REJECTED_C149_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION_REJECTED_C149_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
    }

    public function test_c150_rejects_c149_status_phase_or_next_recommendation_mismatch(): void
    {
        $status = $this->mutateC149AndExecute(function (array $c149): array {
            $c149['status'] = 'BROKEN_STATUS';
            return $c149;
        }, 'status-broken');
        $phase = $this->mutateC149AndExecute(function (array $c149): array {
            $c149['phase_label'] = 'BROKEN_PHASE';
            return $c149;
        }, 'phase-broken');
        $next = $this->mutateC149AndExecute(function (array $c149): array {
            $c149['next_step_recommendation'] = 'BROKEN_NEXT';
            $c149['next_concrete_activation_step_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c149['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $c149;
        }, 'next-broken');

        $this->assertSame('C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION_REJECTED_C149_STATUS_MISMATCH', $status['status']);
        $this->assertSame('C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION_REJECTED_C149_PHASE_LABEL_MISMATCH', $phase['status']);
        $this->assertSame('C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION_REJECTED_C149_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
    }

    /**
     * @dataProvider c149ReadinessMismatchProvider
     */
    public function test_c150_rejects_c149_final_execution_readiness_mismatch(string $field, $value): void
    {
        $result = $this->mutateC149AndExecute(function (array $c149) use ($field, $value): array {
            $c149[$field] = $value;
            return $c149;
        }, 'readiness-'.$field);

        $this->assertSame('C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION_REJECTED_C149_FINAL_EXECUTION_READINESS_INCOMPLETE', $result['status'], $field);
    }

    public function c149ReadinessMismatchProvider(): array
    {
        return [
            ['operator_decision', 'HOLD'],
            ['operator_go_decision', 'HOLD'],
            ['weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_review_pass', false],
            ['ready_for_production_live_runtime_activation_final_execution', false],
            ['production_live_runtime_activation_final_execution_allowed_next', false],
            ['c148_activation_observation_result_review_valid', false],
            ['activation_authorized', false],
            ['primary_candidate_activation_authorized', false],
            ['backup_candidate_activation_authorized', false],
            ['operator_no_go_decision', true],
            ['operator_hold_decision', true],
            ['production_live_runtime_activation_executed', true],
            ['runtime_bridge_active', true],
            ['weekly_swing_watchlist_live_output_enabled', true],
        ];
    }

    public function test_c150_rejects_candidate_scope_change_or_a01_promotion(): void
    {
        $candidate = $this->mutateC149AndExecute(function (array $c149): array {
            $c149['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $c149;
        }, 'candidate-primary');
        $a01 = $this->mutateC149AndExecute(function (array $c149): array {
            $c149['a01_promoted'] = true;
            return $c149;
        }, 'candidate-a01');

        $this->assertSame('C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION_REJECTED_CANDIDATE_SCOPE_MISMATCH', $candidate['status']);
        $this->assertSame('C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION_REJECTED_CANDIDATE_SCOPE_MISMATCH', $a01['status']);
    }

    public function test_c150_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c150-no-temporary-artifact-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame(self::TEMP_NEGATIVE_STATUS, $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
        $this->assertFileDoesNotExist($this->runtimeState);
    }

    public function test_c150_records_manifest_sections_and_keeps_plan_confirm_unchanged(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();

        foreach ([
            'source_artifact_locks',
            'c149_lock_validation_summary',
            'c149_operator_go_no_go_carry_forward_summary',
            'explicit_enablement_summary',
            'runtime_activation_execution_manifest',
            'weekly_swing_watchlist_runtime_state_summary',
            'candidate_runtime_activation_scorecard',
            'plan_confirm_boundary_summary',
            'runtime_config_boundary_summary',
            'temporary_negative_artifact_guard_summary',
            'production_activation_safety_summary',
            'documentation_hygiene_guard_summary',
            'progress_summary',
            'planned_next_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }

        $this->assertSame(self::C149_HASH, $result['expected_c149_hash']);
        $this->assertSame(self::C149_HASH, $result['actual_c149_hash']);
        $this->assertTrue($result['c149_hash_match']);
        $this->assertSame(self::C149_SHA1, $result['expected_c149_file_sha1']);
        $this->assertSame(self::C149_SHA1, $result['actual_c149_file_sha1']);
        $this->assertTrue($result['c149_file_sha1_match']);
        $this->assertTrue($result['c149_convert_from_json_pass']);
        $this->assertTrue($result['runtime_activation_execution_manifest']['runtime_bridge_active']);
        $this->assertTrue($result['runtime_activation_execution_manifest']['weekly_swing_watchlist_live_output_enabled']);
        $this->assertFalse($result['runtime_activation_execution_manifest']['weekly_swing_watchlist_official_output_generated']);
        $this->assertFalse($result['runtime_activation_execution_manifest']['plan_confirm_mutated']);
        $this->assertFalse($result['plan_confirm_boundary_summary']['plan_confirm_runtime_reads_activated_catalog']);
    }

    public function test_c150_keeps_e02_primary_b01_backup_standby_and_a01_comparator(): void
    {
        $result = $this->runService();
        $scorecard = $result['candidate_runtime_activation_scorecard'];

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $result['primary_candidate_code']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $result['backup_candidate_code']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $result['comparator_candidate_code']);
        $this->assertTrue($result['primary_candidate_live_runtime_active']);
        $this->assertTrue($result['backup_candidate_live_runtime_standby_active']);
        $this->assertFalse($result['comparator_candidate_live_runtime_active']);
        $this->assertTrue($result['a01_remains_comparator_only']);
        $this->assertTrue($scorecard[0]['live_runtime_active']);
        $this->assertTrue($scorecard[1]['live_runtime_standby_active']);
        $this->assertFalse($scorecard[2]['live_runtime_active']);
    }

    public function test_c150_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $first = $this->runService(['createdAt' => '2026-07-15T00:00:00+00:00']);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c150-production-live-runtime-activation-final-execution-second.json';
        $this->tmpFiles[] = $secondOutput;
        $second = $this->runService([
            'output' => $secondOutput,
            'createdAt' => '2026-07-15T00:00:00+00:00',
        ]);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
    }

    public function test_c150_does_not_mutate_c149_artifact_or_config_defaults(): void
    {
        $beforeC149 = strtoupper(sha1((string) file_get_contents(self::C149_ARTIFACT)));
        $beforeConfig = (string) file_get_contents('config/watchlist.php');

        $this->runService();

        $this->assertSame($beforeC149, strtoupper(sha1((string) file_get_contents(self::C149_ARTIFACT))));
        $this->assertSame($beforeConfig, (string) file_get_contents('config/watchlist.php'));
    }

    private function runService(array $options = []): array
    {
        $service = new WatchlistBacktestC150WeeklySwingWatchlistProductionLiveRuntimeActivationFinalExecutionService();

        return $service->execute(
            (string) ($options['c149Artifact'] ?? self::C149_ARTIFACT),
            (string) ($options['expectedC149Hash'] ?? self::C149_HASH),
            (string) ($options['expectedC149FileSha1'] ?? self::C149_SHA1),
            (string) ($options['output'] ?? $this->output),
            (string) ($options['runtimeState'] ?? $this->runtimeState),
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'activation_reference' => (string) ($options['activationReference'] ?? 'C150_OPERATOR_APPROVED_FINAL_RUNTIME_ACTIVATION_EXECUTION'),
                'enable_runtime_bridge' => (bool) ($options['enableRuntimeBridge'] ?? true),
                'enable_live_output' => (bool) ($options['enableLiveOutput'] ?? true),
                'confirm_rollback' => (bool) ($options['confirmRollback'] ?? true),
                'confirm_kill_switch' => (bool) ($options['confirmKillSwitch'] ?? true),
                'overwrite' => true,
                'overwrite_runtime_state' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-15T00:00:00+00:00'),
            ]
        );
    }

    private function mutateC149AndExecute(callable $mutator, string $name): array
    {
        $c149 = json_decode((string) file_get_contents(self::C149_ARTIFACT), true);
        $c149 = $mutator(is_array($c149) ? $c149 : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c150-source-c149-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($c149, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'c149Artifact' => $path,
            'expectedC149Hash' => (string) ($c149['artifact_hash'] ?? ''),
            'expectedC149FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function readOutput(): array
    {
        $decoded = json_decode((string) file_get_contents($this->output), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function readRuntimeState(): array
    {
        $decoded = json_decode((string) file_get_contents($this->runtimeState), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function cleanupC150TemporaryArtifacts(): void
    {
        foreach ((array) glob('storage/app/watchlist/backtest/c150-*test.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/.tmp-c150*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/runtime/.tmp-c150*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}

<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC154WeeklySwingWatchlistProductionLiveRuntimeControlledOutputGenerationExecutionService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC154WeeklySwingWatchlistProductionLiveRuntimeControlledOutputGenerationExecutionTest extends TestCase
{
    private const C153_ARTIFACT = 'storage/app/watchlist/backtest/c153-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-boundary-review.json';
    private const C153_HASH = '51bdfbcbb34ce49a185122f0df932451fd914a78';
    private const C153_SHA1 = '9B8A640C6C7C9DD1947AB4C69706C76F44793B43';
    private const PASS_STATUS = 'C154_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_EXECUTION_PASSED_CONTROLLED_OUTPUT_GENERATED_NOT_PUBLISHED_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C154_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_EXECUTION_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const CONFIRMATION_MISSING_STATUS = 'C154_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_EXECUTION_REJECTED_CONTROLLED_OUTPUT_CONFIRMATION_MISSING';
    private const TEMP_NEGATIVE_STATUS = 'C154_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_EXECUTION_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const NEXT_C155 = 'C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW';

    private string $output;
    private string $controlledOutput;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c154-controlled-output-generation-execution.json';
        $this->controlledOutput = 'storage/app/watchlist/output/.tmp-c154-weekly-swing-watchlist-controlled-output.json';
        $this->cleanupC154TemporaryArtifacts();
        @unlink($this->output);
        @unlink($this->controlledOutput);
    }

    protected function tearDown(): void
    {
        @unlink($this->output);
        @unlink($this->controlledOutput);
        foreach ($this->tmpFiles as $path) {
            @unlink($path);
        }
        $this->cleanupC154TemporaryArtifacts();
        parent::tearDown();
    }

    public function test_c154_generates_controlled_output_and_keeps_publication_and_plan_confirm_locked(): void
    {
        $result = $this->runService();
        $controlled = $this->readControlledOutput();

        $this->assertSame('C154_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_EXECUTION', $result['run_code']);
        $this->assertSame('PR-42 / C154_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_EXECUTION', $result['phase_label']);
        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame(self::PASS_STATUS, $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_production_live_runtime_controlled_output_generation_execution_pass']);
        $this->assertTrue($result['production_live_runtime_controlled_output_generation_execution_pass']);
        $this->assertTrue($result['ready_for_weekly_swing_watchlist_controlled_output_generation_result_review']);
        $this->assertTrue($result['production_live_runtime_controlled_output_generation_result_review_allowed_next']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_output_generation_executed']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_output_generated']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_output_artifact_created']);
        $this->assertTrue($result['weekly_swing_watchlist_official_output_generated']);
        $this->assertTrue($result['weekly_swing_watchlist_live_recommendation_generated']);
        $this->assertFalse($result['weekly_swing_watchlist_official_output_published']);
        $this->assertFalse($result['weekly_swing_watchlist_publication_allowed']);
        $this->assertFalse($result['weekly_swing_watchlist_unrestricted_publication_allowed']);
        $this->assertFalse($result['plan_confirm_mutation_allowed']);
        $this->assertFalse($result['plan_confirm_mutated']);
        $this->assertFalse($result['plan_confirm_runtime_reads_activated_catalog']);
        $this->assertFalse($result['live_plan_confirm_rollout_allowed']);
        $this->assertFalse($result['live_plan_confirm_rollout_executed']);
        $this->assertSame(self::NEXT_C155, $result['next_step_recommendation']);
        $this->assertFileExists($this->output);
        $this->assertFileExists($this->controlledOutput);

        $this->assertSame('weekly_swing_watchlist_controlled_output_generation', $controlled['controlled_output_type']);
        $this->assertSame('controlled', $controlled['generation_mode']);
        $this->assertSame('not_published', $controlled['publication_state']);
        $this->assertSame($this->controlledOutput, $controlled['controlled_output_path']);
        $this->assertSame($result['controlled_output_hash'], $controlled['controlled_output_hash']);
        $this->assertCount(2, $controlled['output_rows']);
        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $controlled['output_rows'][0]['candidate_code']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $controlled['output_rows'][1]['candidate_code']);
        $this->assertFalse($controlled['weekly_swing_watchlist_official_output_published']);
        $this->assertFalse($controlled['weekly_swing_watchlist_publication_allowed']);
        $this->assertFalse($controlled['weekly_swing_watchlist_unrestricted_publication_allowed']);
        $this->assertFalse($controlled['plan_confirm_mutated']);
    }

    public function test_c154_records_c153_locks_and_controlled_output_manifest_sections(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();

        foreach ([
            'source_artifact_locks',
            'c153_lock_validation_summary',
            'c153_boundary_carry_forward_summary',
            'controlled_output_generation_summary',
            'controlled_output_publication_guard_summary',
            'controlled_output_generation_manifest',
            'candidate_controlled_output_generation_scorecard',
            'plan_confirm_guard_summary',
            'operator_approval_validation_summary',
            'execution_confirmation_summary',
            'temporary_negative_artifact_guard_summary',
            'documentation_hygiene_guard_summary',
            'progress_summary',
            'planned_next_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }

        $this->assertSame(self::C153_HASH, $result['expected_c153_hash']);
        $this->assertSame(self::C153_HASH, $result['actual_c153_hash']);
        $this->assertTrue($result['c153_hash_match']);
        $this->assertSame(self::C153_SHA1, $result['expected_c153_file_sha1']);
        $this->assertSame(self::C153_SHA1, $result['actual_c153_file_sha1']);
        $this->assertTrue($result['c153_file_sha1_match']);
        $this->assertTrue($result['c153_lock_valid']);
        $this->assertTrue($result['c153_controlled_output_generation_boundary_valid']);
        $this->assertTrue($run['controlled_output_generation_summary']['controlled_output_generation_executed']);
        $this->assertSame(2, $run['controlled_output_generation_manifest']['controlled_output_record_count']);
        $this->assertSame(self::NEXT_C155, $run['planned_next_summary']['planned_next_review']);
    }

    public function test_c154_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->runService(['operatorApproved' => false]);
        $missingReference = $this->runService(['approvalReference' => '']);

        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingOperator['status']);
        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingReference['status']);
    }

    public function test_c154_rejects_missing_execution_confirmations(): void
    {
        $missingControlledOutput = $this->runService(['controlledOutputConfirmed' => false]);
        $missingNoPublication = $this->runService(['noPublicationConfirmed' => false]);
        $missingPlanConfirm = $this->runService(['planConfirmUnchangedConfirmed' => false]);

        $this->assertSame(self::CONFIRMATION_MISSING_STATUS, $missingControlledOutput['status']);
        $this->assertSame(self::CONFIRMATION_MISSING_STATUS, $missingNoPublication['status']);
        $this->assertSame(self::CONFIRMATION_MISSING_STATUS, $missingPlanConfirm['status']);
    }

    public function test_c154_rejects_missing_or_mismatched_c153_lock(): void
    {
        $missing = $this->runService([
            'c153Artifact' => 'storage/app/watchlist/backtest/.tmp-c154-source-c153-missing.json',
            'expectedC153Hash' => 'missing',
            'expectedC153FileSha1' => 'missing',
        ]);
        $hashMismatch = $this->runService(['expectedC153Hash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedC153FileSha1' => 'BADSHA1']);

        $this->assertSame('C154_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_EXECUTION_REJECTED_C153_ARTIFACT_LOCK_MISMATCH', $missing['status']);
        $this->assertSame('C154_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_EXECUTION_REJECTED_C153_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C154_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_EXECUTION_REJECTED_C153_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
    }

    public function test_c154_rejects_c153_status_phase_or_next_recommendation_mismatch(): void
    {
        $status = $this->mutateC153AndExecute(function (array $c153): array {
            $c153['status'] = 'BROKEN_STATUS';
            return $c153;
        }, 'status-broken');
        $phase = $this->mutateC153AndExecute(function (array $c153): array {
            $c153['phase_label'] = 'BROKEN_PHASE';
            return $c153;
        }, 'phase-broken');
        $next = $this->mutateC153AndExecute(function (array $c153): array {
            $c153['next_step_recommendation'] = 'BROKEN_NEXT';
            $c153['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $c153;
        }, 'next-broken');

        $this->assertSame('C154_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_EXECUTION_REJECTED_C153_STATUS_MISMATCH', $status['status']);
        $this->assertSame('C154_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_EXECUTION_REJECTED_C153_PHASE_LABEL_MISMATCH', $phase['status']);
        $this->assertSame('C154_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_EXECUTION_REJECTED_C153_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
    }

    /**
     * @dataProvider c153BoundaryMismatchProvider
     */
    public function test_c154_rejects_incomplete_c153_boundary_evidence(string $field, $value): void
    {
        $result = $this->mutateC153AndExecute(function (array $c153) use ($field, $value): array {
            $c153[$field] = $value;
            return $c153;
        }, 'boundary-'.$field);

        $this->assertSame('C154_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_EXECUTION_REJECTED_C153_BOUNDARY_REVIEW_INCOMPLETE', $result['status'], $field);
    }

    public function c153BoundaryMismatchProvider(): array
    {
        return [
            ['weekly_swing_watchlist_production_live_runtime_controlled_output_generation_boundary_review_pass', false],
            ['ready_for_weekly_swing_watchlist_controlled_output_generation_execution', false],
            ['weekly_swing_watchlist_controlled_output_generation_allowed_next', false],
            ['runtime_bridge_active', false],
            ['weekly_swing_watchlist_live_output_enabled', false],
            ['c152_lock_valid', false],
            ['c151_post_execution_observation_review_valid', false],
            ['runtime_state_observation_valid', false],
            ['c153_not_output_generation', false],
            ['c153_not_publication', false],
            ['operator_approved', false],
            ['temporary_negative_artifact_cleanup_confirmed', false],
        ];
    }

    /**
     * @dataProvider outputGuardMismatchProvider
     */
    public function test_c154_rejects_output_generation_publication_or_plan_confirm_mutation_already_occurring(string $field): void
    {
        $result = $this->mutateC153AndExecute(function (array $c153) use ($field): array {
            $c153[$field] = true;
            return $c153;
        }, 'output-guard-'.$field);

        $this->assertSame('C154_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_EXECUTION_REJECTED_OUTPUT_GENERATION_OR_PUBLICATION_ALREADY_OCCURRED', $result['status'], $field);
    }

    public function outputGuardMismatchProvider(): array
    {
        return [
            ['weekly_swing_watchlist_controlled_output_generation_executed'],
            ['weekly_swing_watchlist_official_output_generated'],
            ['weekly_swing_watchlist_official_output_published'],
            ['weekly_swing_watchlist_live_recommendation_generated'],
            ['weekly_swing_watchlist_publication_allowed'],
            ['weekly_swing_watchlist_unrestricted_publication_allowed'],
            ['plan_confirm_mutation_allowed'],
            ['plan_confirm_mutated'],
            ['plan_confirm_runtime_reads_activated_catalog'],
            ['live_plan_confirm_rollout_allowed'],
            ['live_plan_confirm_rollout_executed'],
        ];
    }

    public function test_c154_rejects_candidate_scope_change_or_a01_promotion(): void
    {
        $candidate = $this->mutateC153AndExecute(function (array $c153): array {
            $c153['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $c153;
        }, 'candidate-primary');
        $a01 = $this->mutateC153AndExecute(function (array $c153): array {
            $c153['a01_promoted'] = true;
            return $c153;
        }, 'candidate-a01');

        $this->assertSame('C154_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_EXECUTION_REJECTED_CANDIDATE_SCOPE_MISMATCH', $candidate['status']);
        $this->assertSame('C154_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_EXECUTION_REJECTED_CANDIDATE_SCOPE_MISMATCH', $a01['status']);
    }

    public function test_c154_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c154-no-temporary-artifact-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame(self::TEMP_NEGATIVE_STATUS, $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
        $this->assertFileDoesNotExist($this->controlledOutput);
    }

    public function test_c154_does_not_mutate_c153_artifact_or_config_defaults(): void
    {
        $beforeC153 = strtoupper(sha1((string) file_get_contents(self::C153_ARTIFACT)));
        $beforeConfig = (string) file_get_contents('config/watchlist.php');

        $this->runService();

        $this->assertSame($beforeC153, strtoupper(sha1((string) file_get_contents(self::C153_ARTIFACT))));
        $this->assertSame($beforeConfig, (string) file_get_contents('config/watchlist.php'));
    }

    public function test_c154_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $first = $this->runService(['createdAt' => '2026-07-15T00:00:00+00:00']);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c154-controlled-output-generation-execution-second.json';
        $this->tmpFiles[] = $secondOutput;
        $second = $this->runService([
            'output' => $secondOutput,
            'createdAt' => '2026-07-15T00:00:00+00:00',
        ]);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
        $this->assertSame($first['controlled_output_hash'], $second['controlled_output_hash']);
    }

    private function runService(array $options = []): array
    {
        $service = new WatchlistBacktestC154WeeklySwingWatchlistProductionLiveRuntimeControlledOutputGenerationExecutionService();

        return $service->execute(
            (string) ($options['c153Artifact'] ?? self::C153_ARTIFACT),
            (string) ($options['expectedC153Hash'] ?? self::C153_HASH),
            (string) ($options['expectedC153FileSha1'] ?? self::C153_SHA1),
            (string) ($options['output'] ?? $this->output),
            (string) ($options['controlledOutput'] ?? $this->controlledOutput),
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C154_OPERATOR_APPROVED_CONTROLLED_OUTPUT_GENERATION_EXECUTION'),
                'controlled_output_confirmed' => (bool) ($options['controlledOutputConfirmed'] ?? true),
                'no_publication_confirmed' => (bool) ($options['noPublicationConfirmed'] ?? true),
                'plan_confirm_unchanged_confirmed' => (bool) ($options['planConfirmUnchangedConfirmed'] ?? true),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-15T00:00:00+00:00'),
            ]
        );
    }

    private function mutateC153AndExecute(callable $mutator, string $name): array
    {
        $c153 = json_decode((string) file_get_contents(self::C153_ARTIFACT), true);
        $c153 = $mutator(is_array($c153) ? $c153 : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c154-source-c153-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($c153, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'c153Artifact' => $path,
            'expectedC153Hash' => (string) ($c153['artifact_hash'] ?? ''),
            'expectedC153FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function readOutput(): array
    {
        $decoded = json_decode((string) file_get_contents($this->output), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function readControlledOutput(): array
    {
        $decoded = json_decode((string) file_get_contents($this->controlledOutput), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function cleanupC154TemporaryArtifacts(): void
    {
        foreach ((array) glob('storage/app/watchlist/backtest/c154-*test.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/.tmp-c154*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/output/.tmp-c154*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}

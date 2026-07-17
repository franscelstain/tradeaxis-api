<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC153WeeklySwingWatchlistProductionLiveRuntimeControlledOutputGenerationBoundaryReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC153WeeklySwingWatchlistProductionLiveRuntimeControlledOutputGenerationBoundaryReviewTest extends TestCase
{
    private const C152_ARTIFACT = 'storage/app/watchlist/backtest/c152-weekly-swing-watchlist-production-live-runtime-activation-post-execution-observation-result-review.json';
    private const C152_HASH = '85545acd1ea21a0efae6439ccb037b5c4ed34273';
    private const C152_SHA1 = 'FB866FEC13B1BE9D00E9D9CA50D494EC835EED14';
    private const PASS_STATUS = 'C153_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_BOUNDARY_REVIEW_PASSED_READY_FOR_CONTROLLED_OUTPUT_GENERATION_EXECUTION_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C153_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_BOUNDARY_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const TEMP_NEGATIVE_STATUS = 'C153_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_BOUNDARY_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const NEXT_C154 = 'C154_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_EXECUTION';

    private string $output;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c153-controlled-output-generation-boundary-review.json';
        $this->cleanupC153TemporaryArtifacts();
        @unlink($this->output);
    }

    protected function tearDown(): void
    {
        @unlink($this->output);
        foreach ($this->tmpFiles as $path) {
            @unlink($path);
        }
        $this->cleanupC153TemporaryArtifacts();
        parent::tearDown();
    }

    public function test_c153_records_boundary_review_and_defers_actual_output_generation_to_c154(): void
    {
        $result = $this->runService();

        $this->assertSame('C153_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_BOUNDARY_REVIEW', $result['run_code']);
        $this->assertSame('PR-41 / C153_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_BOUNDARY_REVIEW', $result['phase_label']);
        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame(self::PASS_STATUS, $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_production_live_runtime_controlled_output_generation_boundary_review_pass']);
        $this->assertTrue($result['production_live_runtime_controlled_output_generation_boundary_review_pass']);
        $this->assertTrue($result['ready_for_weekly_swing_watchlist_controlled_output_generation_execution']);
        $this->assertTrue($result['production_live_runtime_controlled_output_generation_execution_allowed_next']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_output_generation_allowed_next']);
        $this->assertFalse($result['weekly_swing_watchlist_controlled_output_generation_executed']);
        $this->assertFalse($result['weekly_swing_watchlist_official_output_generated']);
        $this->assertFalse($result['weekly_swing_watchlist_official_output_published']);
        $this->assertFalse($result['weekly_swing_watchlist_publication_allowed']);
        $this->assertFalse($result['weekly_swing_watchlist_unrestricted_publication_allowed']);
        $this->assertFalse($result['plan_confirm_mutated']);
        $this->assertTrue($result['runtime_bridge_active']);
        $this->assertTrue($result['weekly_swing_watchlist_live_output_enabled']);
        $this->assertSame(self::NEXT_C154, $result['next_step_recommendation']);
        $this->assertFileExists($this->output);
    }

    public function test_c153_records_c152_lock_sections_and_boundary_manifest(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();

        foreach ([
            'source_artifact_locks',
            'c152_lock_validation_summary',
            'c152_boundary_carry_forward_summary',
            'controlled_output_generation_execution_decision',
            'controlled_output_generation_execution_manifest',
            'controlled_output_generation_boundary_checklist',
            'candidate_controlled_output_generation_boundary_scorecard',
            'output_generation_publication_guard_summary',
            'plan_confirm_guard_summary',
            'operator_approval_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'documentation_hygiene_guard_summary',
            'progress_summary',
            'planned_next_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }

        $this->assertSame(self::C152_HASH, $result['expected_c152_hash']);
        $this->assertSame(self::C152_HASH, $result['actual_c152_hash']);
        $this->assertTrue($result['c152_hash_match']);
        $this->assertSame(self::C152_SHA1, $result['expected_c152_file_sha1']);
        $this->assertSame(self::C152_SHA1, $result['actual_c152_file_sha1']);
        $this->assertTrue($result['c152_file_sha1_match']);
        $this->assertTrue($result['c152_lock_valid']);
        $this->assertTrue($result['c152_controlled_output_generation_boundary_ready']);
        $this->assertTrue($run['controlled_output_generation_execution_decision']['controlled_output_generation_execution_allowed_next']);
        $this->assertFalse($run['controlled_output_generation_execution_decision']['controlled_output_generation_executed_in_c153']);
        $this->assertSame(self::NEXT_C154, $run['planned_next_summary']['planned_next_review']);
    }

    public function test_c153_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->runService(['operatorApproved' => false]);
        $missingReference = $this->runService(['approvalReference' => '']);

        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingOperator['status']);
        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingReference['status']);
    }

    public function test_c153_rejects_missing_or_mismatched_c152_lock(): void
    {
        $missing = $this->runService([
            'c152Artifact' => 'storage/app/watchlist/backtest/missing-c152-for-c153.json',
            'expectedC152Hash' => 'missing',
            'expectedC152FileSha1' => 'missing',
        ]);
        $hashMismatch = $this->runService(['expectedC152Hash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedC152FileSha1' => 'BADSHA1']);

        $this->assertSame('C153_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_BOUNDARY_REVIEW_REJECTED_C152_ARTIFACT_LOCK_MISMATCH', $missing['status']);
        $this->assertSame('C153_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_BOUNDARY_REVIEW_REJECTED_C152_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C153_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_BOUNDARY_REVIEW_REJECTED_C152_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
    }

    public function test_c153_rejects_c152_status_phase_or_next_recommendation_mismatch(): void
    {
        $status = $this->mutateC152AndExecute(function (array $c152): array {
            $c152['status'] = 'BROKEN_STATUS';
            return $c152;
        }, 'status-broken');
        $phase = $this->mutateC152AndExecute(function (array $c152): array {
            $c152['phase_label'] = 'BROKEN_PHASE';
            return $c152;
        }, 'phase-broken');
        $next = $this->mutateC152AndExecute(function (array $c152): array {
            $c152['next_step_recommendation'] = 'BROKEN_NEXT';
            $c152['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $c152;
        }, 'next-broken');

        $this->assertSame('C153_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_BOUNDARY_REVIEW_REJECTED_C152_STATUS_MISMATCH', $status['status']);
        $this->assertSame('C153_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_BOUNDARY_REVIEW_REJECTED_C152_PHASE_LABEL_MISMATCH', $phase['status']);
        $this->assertSame('C153_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_BOUNDARY_REVIEW_REJECTED_C152_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
    }

    /**
     * @dataProvider c152BoundaryMismatchProvider
     */
    public function test_c153_rejects_incomplete_c152_boundary_evidence(string $field, $value): void
    {
        $result = $this->mutateC152AndExecute(function (array $c152) use ($field, $value): array {
            $c152[$field] = $value;
            return $c152;
        }, 'boundary-'.$field);

        $this->assertSame('C153_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_BOUNDARY_REVIEW_REJECTED_C152_BOUNDARY_REVIEW_INCOMPLETE', $result['status'], $field);
    }

    public function c152BoundaryMismatchProvider(): array
    {
        return [
            ['weekly_swing_watchlist_production_live_runtime_activation_post_execution_observation_result_review_pass', false],
            ['ready_for_weekly_swing_watchlist_controlled_output_generation_boundary_review', false],
            ['weekly_swing_watchlist_controlled_output_generation_allowed_next', false],
            ['runtime_bridge_active', false],
            ['weekly_swing_watchlist_live_output_enabled', false],
            ['c151_lock_valid', false],
            ['runtime_state_observation_valid', false],
            ['c152_not_output_generation', false],
            ['c152_not_publication', false],
        ];
    }

    /**
     * @dataProvider outputGuardMismatchProvider
     */
    public function test_c153_rejects_output_generation_publication_or_plan_confirm_mutation_already_occurring(string $field): void
    {
        $result = $this->mutateC152AndExecute(function (array $c152) use ($field): array {
            $c152[$field] = true;
            return $c152;
        }, 'output-guard-'.$field);

        $this->assertSame('C153_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_BOUNDARY_REVIEW_REJECTED_OUTPUT_GENERATION_OR_PUBLICATION_ALREADY_OCCURRED', $result['status'], $field);
    }

    public function outputGuardMismatchProvider(): array
    {
        return [
            ['weekly_swing_watchlist_official_output_generated'],
            ['weekly_swing_watchlist_official_output_published'],
            ['weekly_swing_watchlist_live_recommendation_generated'],
            ['weekly_swing_watchlist_publication_allowed'],
            ['weekly_swing_watchlist_unrestricted_publication_allowed'],
            ['plan_confirm_mutated'],
            ['plan_confirm_runtime_reads_activated_catalog'],
        ];
    }

    public function test_c153_rejects_candidate_scope_change_or_a01_promotion(): void
    {
        $candidate = $this->mutateC152AndExecute(function (array $c152): array {
            $c152['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $c152;
        }, 'candidate-primary');
        $a01 = $this->mutateC152AndExecute(function (array $c152): array {
            $c152['a01_promoted'] = true;
            return $c152;
        }, 'candidate-a01');

        $this->assertSame('C153_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_BOUNDARY_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $candidate['status']);
        $this->assertSame('C153_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_BOUNDARY_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $a01['status']);
    }

    public function test_c153_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c153-no-temporary-artifact-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame(self::TEMP_NEGATIVE_STATUS, $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c153_does_not_mutate_c152_artifact_or_config_defaults(): void
    {
        $beforeC152 = strtoupper(sha1((string) file_get_contents(self::C152_ARTIFACT)));
        $beforeConfig = (string) file_get_contents('config/watchlist.php');

        $this->runService();

        $this->assertSame($beforeC152, strtoupper(sha1((string) file_get_contents(self::C152_ARTIFACT))));
        $this->assertSame($beforeConfig, (string) file_get_contents('config/watchlist.php'));
    }

    public function test_c153_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $first = $this->runService(['createdAt' => '2026-07-15T00:00:00+00:00']);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c153-controlled-output-generation-boundary-review-second.json';
        $this->tmpFiles[] = $secondOutput;
        $second = $this->runService([
            'output' => $secondOutput,
            'createdAt' => '2026-07-15T00:00:00+00:00',
        ]);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
    }

    private function runService(array $options = []): array
    {
        $service = new WatchlistBacktestC153WeeklySwingWatchlistProductionLiveRuntimeControlledOutputGenerationBoundaryReviewService();

        return $service->execute(
            (string) ($options['c152Artifact'] ?? self::C152_ARTIFACT),
            (string) ($options['expectedC152Hash'] ?? self::C152_HASH),
            (string) ($options['expectedC152FileSha1'] ?? self::C152_SHA1),
            (string) ($options['output'] ?? $this->output),
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C153_OPERATOR_APPROVED_CONTROLLED_OUTPUT_GENERATION_BOUNDARY_REVIEW'),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-15T00:00:00+00:00'),
            ]
        );
    }

    private function mutateC152AndExecute(callable $mutator, string $name): array
    {
        $c152 = json_decode((string) file_get_contents(self::C152_ARTIFACT), true);
        $c152 = $mutator(is_array($c152) ? $c152 : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c153-source-c152-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($c152, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'c152Artifact' => $path,
            'expectedC152Hash' => (string) ($c152['artifact_hash'] ?? ''),
            'expectedC152FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function readOutput(): array
    {
        $decoded = json_decode((string) file_get_contents($this->output), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function cleanupC153TemporaryArtifacts(): void
    {
        foreach ((array) glob('storage/app/watchlist/backtest/c153-*test.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/.tmp-c153*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}

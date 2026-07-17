<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC152WeeklySwingWatchlistProductionLiveRuntimeActivationPostExecutionObservationResultReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC152WeeklySwingWatchlistProductionLiveRuntimeActivationPostExecutionObservationResultReviewTest extends TestCase
{
    private const C151_ARTIFACT = 'storage/app/watchlist/backtest/c151-weekly-swing-watchlist-production-live-runtime-activation-post-execution-observation-review.json';
    private const C151_HASH = '55f06c57436ead483bea22626552b7e500d53120';
    private const C151_SHA1 = '198B10144A6ADC5447478E36347CD8DAD6136E16';
    private const PASS_STATUS = 'C152_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_RESULT_REVIEW_PASSED_RUNTIME_STABLE_READY_FOR_CONTROLLED_OUTPUT_GENERATION_BOUNDARY_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C152_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_RESULT_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const TEMP_NEGATIVE_STATUS = 'C152_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_RESULT_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const NEXT_C153 = 'C153_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_BOUNDARY_REVIEW';

    private string $output;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c152-production-live-runtime-activation-post-execution-observation-result-review.json';
        $this->cleanupC152TemporaryArtifacts();
        @unlink($this->output);
    }

    protected function tearDown(): void
    {
        @unlink($this->output);
        foreach ($this->tmpFiles as $path) {
            @unlink($path);
        }
        $this->cleanupC152TemporaryArtifacts();
        parent::tearDown();
    }

    public function test_c152_reviews_c151_observation_result_and_allows_only_controlled_output_generation_boundary_next(): void
    {
        $result = $this->runService();

        $this->assertSame('C152_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_RESULT_REVIEW', $result['run_code']);
        $this->assertSame('PR-40 / C152_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_RESULT_REVIEW', $result['phase_label']);
        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame(self::PASS_STATUS, $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_production_live_runtime_activation_post_execution_observation_result_review_pass']);
        $this->assertTrue($result['production_live_runtime_activation_post_execution_observation_result_review_pass']);
        $this->assertTrue($result['ready_for_weekly_swing_watchlist_controlled_output_generation_boundary_review']);
        $this->assertTrue($result['production_live_runtime_controlled_output_generation_boundary_review_allowed_next']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_output_generation_allowed_next']);
        $this->assertTrue($result['runtime_bridge_active']);
        $this->assertTrue($result['weekly_swing_watchlist_runtime_active']);
        $this->assertTrue($result['weekly_swing_watchlist_live_output_enabled']);
        $this->assertTrue($result['weekly_swing_watchlist_live_recommendation_generation_allowed']);
        $this->assertFalse($result['weekly_swing_watchlist_official_output_generated']);
        $this->assertFalse($result['weekly_swing_watchlist_official_output_published']);
        $this->assertFalse($result['weekly_swing_watchlist_live_recommendation_generated']);
        $this->assertFalse($result['weekly_swing_watchlist_publication_allowed']);
        $this->assertFalse($result['weekly_swing_watchlist_unrestricted_publication_allowed']);
        $this->assertFalse($result['plan_confirm_mutated']);
        $this->assertSame(self::NEXT_C153, $result['next_step_recommendation']);
        $this->assertFileExists($this->output);
    }

    public function test_c152_records_c151_lock_and_result_review_sections(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();

        foreach ([
            'source_artifact_locks',
            'c151_lock_validation_summary',
            'c151_post_execution_observation_carry_forward_summary',
            'runtime_stability_result_summary',
            'controlled_output_generation_boundary_decision',
            'weekly_swing_watchlist_controlled_output_generation_boundary_manifest',
            'weekly_swing_watchlist_controlled_output_generation_boundary_checklist',
            'candidate_runtime_stability_scorecard',
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

        $this->assertSame(self::C151_HASH, $result['expected_c151_hash']);
        $this->assertSame(self::C151_HASH, $result['actual_c151_hash']);
        $this->assertTrue($result['c151_hash_match']);
        $this->assertSame(self::C151_SHA1, $result['expected_c151_file_sha1']);
        $this->assertSame(self::C151_SHA1, $result['actual_c151_file_sha1']);
        $this->assertTrue($result['c151_file_sha1_match']);
        $this->assertTrue($result['c151_lock_valid']);
        $this->assertTrue($result['c151_post_execution_observation_review_valid']);
        $this->assertTrue($run['runtime_stability_result_summary']['runtime_stable_enough_for_controlled_output_generation_boundary']);
        $this->assertFalse($run['controlled_output_generation_boundary_decision']['controlled_output_generation_allowed_now']);
        $this->assertFalse($run['controlled_output_generation_boundary_decision']['official_output_publication_allowed']);
        $this->assertSame(self::NEXT_C153, $run['planned_next_summary']['planned_next_review']);
    }

    public function test_c152_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->runService(['operatorApproved' => false]);
        $missingReference = $this->runService(['approvalReference' => '']);

        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingOperator['status']);
        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingReference['status']);
    }

    public function test_c152_rejects_missing_or_mismatched_c151_lock(): void
    {
        $missing = $this->runService([
            'c151Artifact' => 'storage/app/watchlist/backtest/missing-c151-for-c152.json',
            'expectedC151Hash' => 'missing',
            'expectedC151FileSha1' => 'missing',
        ]);
        $hashMismatch = $this->runService(['expectedC151Hash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedC151FileSha1' => 'BADSHA1']);

        $this->assertSame('C152_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_RESULT_REVIEW_REJECTED_C151_ARTIFACT_LOCK_MISMATCH', $missing['status']);
        $this->assertSame('C152_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_RESULT_REVIEW_REJECTED_C151_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C152_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_RESULT_REVIEW_REJECTED_C151_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
    }

    public function test_c152_rejects_c151_status_phase_or_next_recommendation_mismatch(): void
    {
        $status = $this->mutateC151AndExecute(function (array $c151): array {
            $c151['status'] = 'BROKEN_STATUS';
            return $c151;
        }, 'status-broken');
        $phase = $this->mutateC151AndExecute(function (array $c151): array {
            $c151['phase_label'] = 'BROKEN_PHASE';
            return $c151;
        }, 'phase-broken');
        $next = $this->mutateC151AndExecute(function (array $c151): array {
            $c151['next_step_recommendation'] = 'BROKEN_NEXT';
            $c151['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $c151;
        }, 'next-broken');

        $this->assertSame('C152_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_RESULT_REVIEW_REJECTED_C151_STATUS_MISMATCH', $status['status']);
        $this->assertSame('C152_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_RESULT_REVIEW_REJECTED_C151_PHASE_LABEL_MISMATCH', $phase['status']);
        $this->assertSame('C152_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_RESULT_REVIEW_REJECTED_C151_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
    }

    /**
     * @dataProvider c151ObservationMismatchProvider
     */
    public function test_c152_rejects_incomplete_c151_observation_result(string $field, $value): void
    {
        $result = $this->mutateC151AndExecute(function (array $c151) use ($field, $value): array {
            $c151[$field] = $value;
            return $c151;
        }, 'observation-'.$field);

        $this->assertSame('C152_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_RESULT_REVIEW_REJECTED_C151_POST_EXECUTION_OBSERVATION_REVIEW_INCOMPLETE', $result['status'], $field);
    }

    public function c151ObservationMismatchProvider(): array
    {
        return [
            ['weekly_swing_watchlist_production_live_runtime_activation_post_execution_observation_review_pass', false],
            ['production_live_runtime_activation_post_execution_observation_review_pass', false],
            ['ready_for_production_live_runtime_activation_post_execution_observation_result_review', false],
            ['production_live_runtime_activation_post_execution_observation_result_review_allowed_next', false],
            ['runtime_bridge_active', false],
            ['weekly_swing_watchlist_live_output_enabled', false],
            ['runtime_state_observation_valid', false],
            ['c150_final_execution_valid', false],
            ['primary_candidate_live_runtime_active', false],
            ['backup_candidate_live_runtime_standby_active', false],
        ];
    }

    /**
     * @dataProvider outputGuardMismatchProvider
     */
    public function test_c152_rejects_output_generation_publication_or_plan_confirm_mutation_already_occurring(string $field): void
    {
        $result = $this->mutateC151AndExecute(function (array $c151) use ($field): array {
            $c151[$field] = true;
            return $c151;
        }, 'output-guard-'.$field);

        $this->assertSame('C152_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_RESULT_REVIEW_REJECTED_OUTPUT_GENERATION_OR_PUBLICATION_ALREADY_OCCURRED', $result['status'], $field);
    }

    public function outputGuardMismatchProvider(): array
    {
        return [
            ['weekly_swing_watchlist_official_output_generated'],
            ['weekly_swing_watchlist_official_output_published'],
            ['weekly_swing_watchlist_live_recommendation_generated'],
            ['plan_confirm_mutated'],
            ['plan_confirm_runtime_reads_activated_catalog'],
        ];
    }

    public function test_c152_rejects_candidate_scope_change_or_a01_promotion(): void
    {
        $candidate = $this->mutateC151AndExecute(function (array $c151): array {
            $c151['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $c151;
        }, 'candidate-primary');
        $a01 = $this->mutateC151AndExecute(function (array $c151): array {
            $c151['a01_promoted'] = true;
            return $c151;
        }, 'candidate-a01');

        $this->assertSame('C152_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_RESULT_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $candidate['status']);
        $this->assertSame('C152_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_RESULT_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $a01['status']);
    }

    public function test_c152_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c152-no-temporary-artifact-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame(self::TEMP_NEGATIVE_STATUS, $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c152_does_not_mutate_c151_artifact_or_config_defaults(): void
    {
        $beforeC151 = strtoupper(sha1((string) file_get_contents(self::C151_ARTIFACT)));
        $beforeConfig = (string) file_get_contents('config/watchlist.php');

        $this->runService();

        $this->assertSame($beforeC151, strtoupper(sha1((string) file_get_contents(self::C151_ARTIFACT))));
        $this->assertSame($beforeConfig, (string) file_get_contents('config/watchlist.php'));
    }

    public function test_c152_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $first = $this->runService(['createdAt' => '2026-07-15T00:00:00+00:00']);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c152-production-live-runtime-activation-post-execution-observation-result-review-second.json';
        $this->tmpFiles[] = $secondOutput;
        $second = $this->runService([
            'output' => $secondOutput,
            'createdAt' => '2026-07-15T00:00:00+00:00',
        ]);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
    }

    private function runService(array $options = []): array
    {
        $service = new WatchlistBacktestC152WeeklySwingWatchlistProductionLiveRuntimeActivationPostExecutionObservationResultReviewService();

        return $service->execute(
            (string) ($options['c151Artifact'] ?? self::C151_ARTIFACT),
            (string) ($options['expectedC151Hash'] ?? self::C151_HASH),
            (string) ($options['expectedC151FileSha1'] ?? self::C151_SHA1),
            (string) ($options['output'] ?? $this->output),
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C152_OPERATOR_APPROVED_POST_EXECUTION_OBSERVATION_RESULT_REVIEW'),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-15T00:00:00+00:00'),
            ]
        );
    }

    private function mutateC151AndExecute(callable $mutator, string $name): array
    {
        $c151 = json_decode((string) file_get_contents(self::C151_ARTIFACT), true);
        $c151 = $mutator(is_array($c151) ? $c151 : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c152-source-c151-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($c151, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'c151Artifact' => $path,
            'expectedC151Hash' => (string) ($c151['artifact_hash'] ?? ''),
            'expectedC151FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function readOutput(): array
    {
        $decoded = json_decode((string) file_get_contents($this->output), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function cleanupC152TemporaryArtifacts(): void
    {
        foreach ((array) glob('storage/app/watchlist/backtest/c152-*test.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/.tmp-c152*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}

<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC155WeeklySwingWatchlistProductionLiveRuntimeControlledOutputGenerationResultReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC155WeeklySwingWatchlistProductionLiveRuntimeControlledOutputGenerationResultReviewTest extends TestCase
{
    private const C154_ARTIFACT = 'storage/app/watchlist/backtest/c154-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-execution.json';
    private const C154_HASH = 'cd321cbbbbc1fa3902da5928a61741e80c8bd437';
    private const C154_SHA1 = '82C8C90E04A7B7C5208BC37E40CAC8B02673CACB';
    private const CONTROLLED_OUTPUT = 'storage/app/watchlist/output/c154-weekly-swing-watchlist-controlled-output.json';
    private const CONTROLLED_OUTPUT_HASH = 'a1ca6b200993e4c70c7dccfa62ace43ffb2f7c4e';
    private const CONTROLLED_OUTPUT_SHA1 = 'AFCA465B7567AFA37034388B257F5F5808B17E5F';
    private const PASS_STATUS = 'C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_PASSED_READY_FOR_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const CONFIRMATION_MISSING_STATUS = 'C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_REJECTED_RESULT_REVIEW_CONFIRMATION_MISSING';
    private const TEMP_NEGATIVE_STATUS = 'C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const NEXT_C156 = 'C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW';

    private string $output;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c155-controlled-output-generation-result-review.json';
        $this->cleanupC155TemporaryArtifacts();
        @unlink($this->output);
    }

    protected function tearDown(): void
    {
        @unlink($this->output);
        foreach ($this->tmpFiles as $path) {
            @unlink($path);
        }
        $this->cleanupC155TemporaryArtifacts();
        parent::tearDown();
    }

    public function test_c155_reviews_controlled_output_result_and_keeps_publication_and_plan_confirm_locked(): void
    {
        $result = $this->runService();

        $this->assertSame('C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW', $result['run_code']);
        $this->assertSame('PR-43 / C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW', $result['phase_label']);
        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame(self::PASS_STATUS, $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_production_live_runtime_controlled_output_generation_result_review_pass']);
        $this->assertTrue($result['production_live_runtime_controlled_output_generation_result_review_pass']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_output_generation_result_reviewed']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_output_generation_result_review_manifest_created']);
        $this->assertTrue($result['ready_for_weekly_swing_watchlist_controlled_output_generation_operator_go_no_go_review']);
        $this->assertTrue($result['production_live_runtime_controlled_output_generation_operator_go_no_go_review_allowed_next']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_output_generation_executed']);
        $this->assertTrue($result['weekly_swing_watchlist_official_output_generated']);
        $this->assertTrue($result['weekly_swing_watchlist_live_recommendation_generated']);
        $this->assertFalse($result['weekly_swing_watchlist_official_output_published']);
        $this->assertFalse($result['weekly_swing_watchlist_publication_allowed']);
        $this->assertFalse($result['weekly_swing_watchlist_unrestricted_publication_allowed']);
        $this->assertFalse($result['plan_confirm_mutation_allowed']);
        $this->assertFalse($result['plan_confirm_mutated']);
        $this->assertFalse($result['plan_confirm_runtime_reads_activated_catalog']);
        $this->assertTrue($result['c154_lock_valid']);
        $this->assertTrue($result['controlled_output_lock_valid']);
        $this->assertTrue($result['controlled_output_integrity_valid']);
        $this->assertSame(self::NEXT_C156, $result['next_step_recommendation']);
        $this->assertFileExists($this->output);
    }

    public function test_c155_records_dual_locks_and_result_review_sections(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();

        foreach ([
            'source_artifact_locks',
            'c154_lock_validation_summary',
            'controlled_output_lock_validation_summary',
            'c154_execution_carry_forward_summary',
            'controlled_output_result_review_summary',
            'controlled_output_integrity_summary',
            'controlled_output_publication_guard_summary',
            'candidate_controlled_output_result_scorecard',
            'operator_approval_validation_summary',
            'result_review_confirmation_summary',
            'temporary_negative_artifact_guard_summary',
            'documentation_hygiene_guard_summary',
            'progress_summary',
            'planned_next_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }

        $this->assertSame(self::C154_HASH, $result['expected_c154_hash']);
        $this->assertSame(self::C154_HASH, $result['actual_c154_hash']);
        $this->assertTrue($result['c154_hash_match']);
        $this->assertSame(self::C154_SHA1, $result['expected_c154_file_sha1']);
        $this->assertSame(self::C154_SHA1, $result['actual_c154_file_sha1']);
        $this->assertTrue($result['c154_file_sha1_match']);
        $this->assertSame(self::CONTROLLED_OUTPUT_HASH, $result['expected_controlled_output_hash']);
        $this->assertSame(self::CONTROLLED_OUTPUT_HASH, $result['actual_controlled_output_hash']);
        $this->assertTrue($result['controlled_output_hash_match']);
        $this->assertSame(self::CONTROLLED_OUTPUT_SHA1, $result['expected_controlled_output_file_sha1']);
        $this->assertSame(self::CONTROLLED_OUTPUT_SHA1, $result['actual_controlled_output_file_sha1']);
        $this->assertTrue($result['controlled_output_file_sha1_match']);
        $this->assertSame(2, $result['controlled_output_record_count']);
        $this->assertSame(self::NEXT_C156, $run['planned_next_summary']['planned_next_review']);
    }

    public function test_c155_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->runService(['operatorApproved' => false]);
        $missingReference = $this->runService(['approvalReference' => '']);

        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingOperator['status']);
        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingReference['status']);
    }

    public function test_c155_rejects_missing_result_review_confirmations(): void
    {
        $missingResultReview = $this->runService(['resultReviewConfirmed' => false]);
        $missingNoPublication = $this->runService(['noPublicationConfirmed' => false]);
        $missingPlanConfirm = $this->runService(['planConfirmUnchangedConfirmed' => false]);

        $this->assertSame(self::CONFIRMATION_MISSING_STATUS, $missingResultReview['status']);
        $this->assertSame(self::CONFIRMATION_MISSING_STATUS, $missingNoPublication['status']);
        $this->assertSame(self::CONFIRMATION_MISSING_STATUS, $missingPlanConfirm['status']);
    }

    public function test_c155_rejects_missing_or_mismatched_c154_artifact_lock(): void
    {
        $missing = $this->runService([
            'c154Artifact' => 'storage/app/watchlist/backtest/.tmp-c155-source-c154-missing.json',
            'expectedC154Hash' => 'missing',
            'expectedC154FileSha1' => 'missing',
        ]);
        $hashMismatch = $this->runService(['expectedC154Hash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedC154FileSha1' => 'BADSHA1']);

        $this->assertSame('C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_REJECTED_C154_ARTIFACT_LOCK_MISMATCH', $missing['status']);
        $this->assertSame('C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_REJECTED_C154_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_REJECTED_C154_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
    }

    public function test_c155_rejects_missing_or_mismatched_controlled_output_lock(): void
    {
        $missing = $this->runService([
            'controlledOutput' => 'storage/app/watchlist/output/.tmp-c155-controlled-output-missing.json',
            'expectedControlledOutputHash' => 'missing',
            'expectedControlledOutputFileSha1' => 'missing',
        ]);
        $hashMismatch = $this->runService(['expectedControlledOutputHash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedControlledOutputFileSha1' => 'BADSHA1']);

        $this->assertSame('C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_REJECTED_CONTROLLED_OUTPUT_ARTIFACT_LOCK_MISMATCH', $missing['status']);
        $this->assertSame('C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_REJECTED_CONTROLLED_OUTPUT_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_REJECTED_CONTROLLED_OUTPUT_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
    }

    public function test_c155_rejects_c154_status_phase_or_next_recommendation_mismatch(): void
    {
        $status = $this->mutateC154AndExecute(function (array $c154): array {
            $c154['status'] = 'BROKEN_STATUS';
            return $c154;
        }, 'status-broken');
        $phase = $this->mutateC154AndExecute(function (array $c154): array {
            $c154['phase_label'] = 'BROKEN_PHASE';
            return $c154;
        }, 'phase-broken');
        $next = $this->mutateC154AndExecute(function (array $c154): array {
            $c154['next_step_recommendation'] = 'BROKEN_NEXT';
            $c154['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $c154;
        }, 'next-broken');

        $this->assertSame('C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_REJECTED_C154_STATUS_MISMATCH', $status['status']);
        $this->assertSame('C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_REJECTED_C154_PHASE_LABEL_MISMATCH', $phase['status']);
        $this->assertSame('C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_REJECTED_C154_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
    }

    public function test_c155_rejects_convert_from_json_duplicate_top_level_keys(): void
    {
        $c154Path = $this->duplicateTopLevelKeyFixture(self::C154_ARTIFACT, 'c154-duplicate', 'Run_Code');
        $c154 = json_decode((string) file_get_contents($c154Path), true);
        $controlledPath = $this->duplicateTopLevelKeyFixture(self::CONTROLLED_OUTPUT, 'controlled-duplicate', 'Controlled_Output_Hash');

        $c154Result = $this->runService([
            'c154Artifact' => $c154Path,
            'expectedC154Hash' => (string) ($c154['artifact_hash'] ?? self::C154_HASH),
            'expectedC154FileSha1' => strtoupper(sha1((string) file_get_contents($c154Path))),
        ]);
        $controlledResult = $this->runService([
            'controlledOutput' => $controlledPath,
            'expectedControlledOutputFileSha1' => strtoupper(sha1((string) file_get_contents($controlledPath))),
        ]);

        $this->assertSame('C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_REJECTED_C154_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION', $c154Result['status']);
        $this->assertFalse($c154Result['c154_convert_from_json_pass']);
        $this->assertSame('C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_REJECTED_CONTROLLED_OUTPUT_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION', $controlledResult['status']);
        $this->assertFalse($controlledResult['controlled_output_convert_from_json_pass']);
    }

    /**
     * @dataProvider c154ExecutionMismatchProvider
     */
    public function test_c155_rejects_incomplete_c154_execution_evidence(string $field, $value): void
    {
        $result = $this->mutateC154AndExecute(function (array $c154) use ($field, $value): array {
            $c154[$field] = $value;
            return $c154;
        }, 'execution-'.$field);

        $this->assertSame('C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_REJECTED_C154_CONTROLLED_OUTPUT_GENERATION_EXECUTION_INCOMPLETE', $result['status'], $field);
    }

    public function c154ExecutionMismatchProvider(): array
    {
        return [
            ['weekly_swing_watchlist_production_live_runtime_controlled_output_generation_execution_pass', false],
            ['ready_for_weekly_swing_watchlist_controlled_output_generation_result_review', false],
            ['weekly_swing_watchlist_controlled_output_generation_executed', false],
            ['weekly_swing_watchlist_controlled_output_artifact_created', false],
            ['weekly_swing_watchlist_official_output_generated', false],
            ['c153_lock_valid', false],
            ['c154_controlled_output_generation_execution_only', false],
            ['controlled_output_confirmed', false],
            ['temporary_negative_artifact_cleanup_confirmed', false],
        ];
    }

    public function test_c155_rejects_publication_or_plan_confirm_mutation_from_c154_or_controlled_output(): void
    {
        $c154Published = $this->mutateC154AndExecute(function (array $c154): array {
            $c154['weekly_swing_watchlist_official_output_published'] = true;
            return $c154;
        }, 'c154-published');
        $controlledPublished = $this->mutateControlledOutputAndExecute(function (array $controlled): array {
            $controlled['weekly_swing_watchlist_official_output_published'] = true;
            return $controlled;
        }, 'controlled-published');
        $controlledPlanConfirm = $this->mutateControlledOutputAndExecute(function (array $controlled): array {
            $controlled['plan_confirm_mutated'] = true;
            return $controlled;
        }, 'controlled-plan-confirm');

        $this->assertSame('C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_REJECTED_C154_CONTROLLED_OUTPUT_GENERATION_EXECUTION_INCOMPLETE', $c154Published['status']);
        $this->assertSame('C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED', $controlledPublished['status']);
        $this->assertSame('C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED', $controlledPlanConfirm['status']);
    }

    public function test_c155_rejects_candidate_scope_or_controlled_output_integrity_mismatch(): void
    {
        $candidate = $this->mutateControlledOutputAndExecute(function (array $controlled): array {
            $controlled['output_rows'][0]['candidate_code'] = 'BROKEN_PRIMARY';
            return $controlled;
        }, 'candidate-primary');
        $a01 = $this->mutateC154AndExecute(function (array $c154): array {
            $c154['a01_promoted'] = true;
            return $c154;
        }, 'candidate-a01');
        $integrity = $this->mutateC154AndExecute(function (array $c154): array {
            $c154['controlled_output_hash'] = 'bad-controlled-output-hash';
            return $c154;
        }, 'integrity-hash');

        $this->assertSame('C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $candidate['status']);
        $this->assertSame('C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $a01['status']);
        $this->assertSame('C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_REJECTED_CONTROLLED_OUTPUT_INTEGRITY_MISMATCH', $integrity['status']);
    }

    public function test_c155_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c155-no-temporary-artifact-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame(self::TEMP_NEGATIVE_STATUS, $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c155_does_not_mutate_c154_controlled_output_or_config_defaults(): void
    {
        $beforeC154 = strtoupper(sha1((string) file_get_contents(self::C154_ARTIFACT)));
        $beforeControlledOutput = strtoupper(sha1((string) file_get_contents(self::CONTROLLED_OUTPUT)));
        $beforeConfig = (string) file_get_contents('config/watchlist.php');

        $this->runService();

        $this->assertSame($beforeC154, strtoupper(sha1((string) file_get_contents(self::C154_ARTIFACT))));
        $this->assertSame($beforeControlledOutput, strtoupper(sha1((string) file_get_contents(self::CONTROLLED_OUTPUT))));
        $this->assertSame($beforeConfig, (string) file_get_contents('config/watchlist.php'));
    }

    public function test_c155_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $first = $this->runService(['createdAt' => '2026-07-15T00:00:00+00:00']);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c155-controlled-output-generation-result-review-second.json';
        $this->tmpFiles[] = $secondOutput;
        $second = $this->runService([
            'output' => $secondOutput,
            'createdAt' => '2026-07-15T00:00:00+00:00',
        ]);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
    }

    private function runService(array $options = []): array
    {
        $service = new WatchlistBacktestC155WeeklySwingWatchlistProductionLiveRuntimeControlledOutputGenerationResultReviewService();

        return $service->execute(
            (string) ($options['c154Artifact'] ?? self::C154_ARTIFACT),
            (string) ($options['expectedC154Hash'] ?? self::C154_HASH),
            (string) ($options['expectedC154FileSha1'] ?? self::C154_SHA1),
            (string) ($options['controlledOutput'] ?? self::CONTROLLED_OUTPUT),
            (string) ($options['expectedControlledOutputHash'] ?? self::CONTROLLED_OUTPUT_HASH),
            (string) ($options['expectedControlledOutputFileSha1'] ?? self::CONTROLLED_OUTPUT_SHA1),
            (string) ($options['output'] ?? $this->output),
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C155_OPERATOR_APPROVED_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW'),
                'result_review_confirmed' => (bool) ($options['resultReviewConfirmed'] ?? true),
                'no_publication_confirmed' => (bool) ($options['noPublicationConfirmed'] ?? true),
                'plan_confirm_unchanged_confirmed' => (bool) ($options['planConfirmUnchangedConfirmed'] ?? true),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-15T00:00:00+00:00'),
            ]
        );
    }

    private function mutateC154AndExecute(callable $mutator, string $name): array
    {
        $c154 = json_decode((string) file_get_contents(self::C154_ARTIFACT), true);
        $c154 = $mutator(is_array($c154) ? $c154 : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c155-source-c154-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($c154, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'c154Artifact' => $path,
            'expectedC154Hash' => (string) ($c154['artifact_hash'] ?? ''),
            'expectedC154FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function mutateControlledOutputAndExecute(callable $mutator, string $name): array
    {
        $controlled = json_decode((string) file_get_contents(self::CONTROLLED_OUTPUT), true);
        $controlled = $mutator(is_array($controlled) ? $controlled : []);
        $path = 'storage/app/watchlist/output/.tmp-c155-controlled-output-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($controlled, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'controlledOutput' => $path,
            'expectedControlledOutputHash' => (string) ($controlled['controlled_output_hash'] ?? ''),
            'expectedControlledOutputFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function duplicateTopLevelKeyFixture(string $source, string $name, string $duplicateKey): string
    {
        $raw = (string) file_get_contents($source);
        $path = strpos($source, '/output/') !== false
            ? 'storage/app/watchlist/output/.tmp-c155-'.$name.'.json'
            : 'storage/app/watchlist/backtest/.tmp-c155-'.$name.'.json';
        $this->tmpFiles[] = $path;
        $duplicateRaw = preg_replace('/\{/', '{"'.$duplicateKey.'":"DUPLICATE_CASE_KEY",', $raw, 1);
        file_put_contents($path, $duplicateRaw);

        return $path;
    }

    private function readOutput(): array
    {
        $decoded = json_decode((string) file_get_contents($this->output), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function cleanupC155TemporaryArtifacts(): void
    {
        foreach ((array) glob('storage/app/watchlist/backtest/c155-*test.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/.tmp-c155*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/output/.tmp-c155*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}

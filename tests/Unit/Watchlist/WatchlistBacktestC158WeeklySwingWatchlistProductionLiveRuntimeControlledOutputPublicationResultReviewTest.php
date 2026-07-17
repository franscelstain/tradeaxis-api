<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC158WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationResultReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC158WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationResultReviewTest extends TestCase
{
    private const C158_EXECUTION_ARTIFACT = 'storage/app/watchlist/backtest/c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-execution.json';
    private const C158_EXECUTION_HASH = 'fec3b624eb3e912b1302165b1def8fe0a4669a87';
    private const C158_EXECUTION_SHA1 = '242830E193C2D54A4C7A233A68D04F90412AEE7D';
    private const CONTROLLED_PUBLICATION = 'storage/app/watchlist/output/c158-weekly-swing-watchlist-controlled-publication.json';
    private const CONTROLLED_PUBLICATION_HASH = 'df064c7290ff4c3bfd0c7a8412d39299049c01d5';
    private const CONTROLLED_PUBLICATION_SHA1 = 'D87AB8CD1564BE8B266B8A68011470272D49EE60';
    private const PASS_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_PASSED_READY_FOR_OPERATOR_GO_NO_GO_REVIEW_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const CONFIRMATION_MISSING_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_REJECTED_RESULT_REVIEW_CONFIRMATION_MISSING';
    private const TEMP_NEGATIVE_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const NEXT_OPERATOR = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW';

    private string $output;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c158-controlled-output-publication-result-review.json';
        $this->cleanupC158TemporaryArtifacts();
        @unlink($this->output);
    }

    protected function tearDown(): void
    {
        @unlink($this->output);
        foreach ($this->tmpFiles as $path) {
            @unlink($path);
        }
        $this->cleanupC158TemporaryArtifacts();
        parent::tearDown();
    }

    public function test_c158_result_review_passes_and_keeps_same_topic_number_for_operator_review(): void
    {
        $result = $this->runService();

        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW', $result['run_code']);
        $this->assertSame('PR-48 / C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW', $result['phase_label']);
        $this->assertSame('C158_CONTROLLED_OUTPUT_PUBLICATION', $result['topic_code']);
        $this->assertSame('RESULT_REVIEW', $result['topic_stage']);
        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame(self::PASS_STATUS, $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_production_live_runtime_controlled_output_publication_result_review_pass']);
        $this->assertTrue($result['production_live_runtime_controlled_output_publication_result_review_pass']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_output_publication_result_reviewed']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_output_publication_result_review_manifest_created']);
        $this->assertTrue($result['ready_for_weekly_swing_watchlist_controlled_output_publication_operator_go_no_go_review']);
        $this->assertTrue($result['production_live_runtime_controlled_output_publication_operator_go_no_go_review_allowed_next']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_output_publication_executed']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_output_published']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_publication_artifact_created']);
        $this->assertFalse($result['weekly_swing_watchlist_official_output_published']);
        $this->assertFalse($result['weekly_swing_watchlist_publication_allowed']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_publication_allowed']);
        $this->assertFalse($result['weekly_swing_watchlist_unrestricted_publication_allowed']);
        $this->assertFalse($result['plan_confirm_mutated']);
        $this->assertSame(self::NEXT_OPERATOR, $result['next_step_recommendation']);
        $this->assertFileExists($this->output);
    }

    public function test_c158_result_review_records_dual_locks_and_sections(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();

        foreach ([
            'source_artifact_locks',
            'c158_execution_lock_validation_summary',
            'controlled_publication_lock_validation_summary',
            'c158_execution_carry_forward_summary',
            'controlled_publication_result_review_summary',
            'controlled_publication_integrity_summary',
            'publication_plan_confirm_safety_summary',
            'candidate_controlled_publication_result_scorecard',
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

        $this->assertSame(self::C158_EXECUTION_HASH, $result['expected_c158_execution_hash']);
        $this->assertSame(self::C158_EXECUTION_HASH, $result['actual_c158_execution_hash']);
        $this->assertTrue($result['c158_execution_hash_match']);
        $this->assertSame(self::C158_EXECUTION_SHA1, $result['actual_c158_execution_file_sha1']);
        $this->assertTrue($result['c158_execution_file_sha1_match']);
        $this->assertSame(self::CONTROLLED_PUBLICATION_HASH, $result['expected_controlled_publication_hash']);
        $this->assertSame(self::CONTROLLED_PUBLICATION_HASH, $result['actual_controlled_publication_hash']);
        $this->assertTrue($result['controlled_publication_hash_match']);
        $this->assertSame(self::CONTROLLED_PUBLICATION_SHA1, $result['actual_controlled_publication_file_sha1']);
        $this->assertTrue($result['controlled_publication_file_sha1_match']);
        $this->assertSame(2, $result['controlled_publication_record_count']);
        $this->assertSame(self::NEXT_OPERATOR, $run['planned_next_summary']['planned_next_review']);
    }

    public function test_c158_result_review_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->runService(['operatorApproved' => false]);
        $missingReference = $this->runService(['approvalReference' => '']);

        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingOperator['status']);
        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingReference['status']);
    }

    public function test_c158_result_review_rejects_missing_required_confirmations(): void
    {
        $resultReview = $this->runService(['resultReviewConfirmed' => false]);
        $publicationResult = $this->runService(['controlledPublicationResultConfirmed' => false]);
        $controlledOnly = $this->runService(['controlledPublicationOnlyConfirmed' => false]);
        $planConfirm = $this->runService(['planConfirmUnchangedConfirmed' => false]);

        $this->assertSame(self::CONFIRMATION_MISSING_STATUS, $resultReview['status']);
        $this->assertSame(self::CONFIRMATION_MISSING_STATUS, $publicationResult['status']);
        $this->assertSame(self::CONFIRMATION_MISSING_STATUS, $controlledOnly['status']);
        $this->assertSame(self::CONFIRMATION_MISSING_STATUS, $planConfirm['status']);
    }

    public function test_c158_result_review_rejects_execution_artifact_lock_status_phase_or_next_mismatch(): void
    {
        $hashMismatch = $this->runService(['expectedC158ExecutionHash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedC158ExecutionFileSha1' => 'BADSHA1']);
        $status = $this->mutateExecutionAndExecute(function (array $execution): array {
            $execution['status'] = 'BROKEN_STATUS';
            return $execution;
        }, 'status');
        $phase = $this->mutateExecutionAndExecute(function (array $execution): array {
            $execution['phase_label'] = 'BROKEN_PHASE';
            return $execution;
        }, 'phase');
        $next = $this->mutateExecutionAndExecute(function (array $execution): array {
            $execution['next_step_recommendation'] = 'BROKEN_NEXT';
            $execution['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $execution;
        }, 'next');

        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_REJECTED_C158_EXECUTION_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_REJECTED_C158_EXECUTION_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_REJECTED_C158_EXECUTION_STATUS_MISMATCH', $status['status']);
        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_REJECTED_C158_EXECUTION_PHASE_LABEL_MISMATCH', $phase['status']);
        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_REJECTED_C158_EXECUTION_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
    }

    public function test_c158_result_review_rejects_controlled_publication_lock_mismatch(): void
    {
        $hashMismatch = $this->runService(['expectedControlledPublicationHash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedControlledPublicationFileSha1' => 'BADSHA1']);

        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_REJECTED_CONTROLLED_PUBLICATION_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_REJECTED_CONTROLLED_PUBLICATION_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
    }

    public function test_c158_result_review_rejects_convert_from_json_duplicate_top_level_keys(): void
    {
        $executionPath = $this->duplicateTopLevelKeyFixture(self::C158_EXECUTION_ARTIFACT, 'execution-duplicate', 'Run_Code');
        $publicationPath = $this->duplicateTopLevelKeyFixture(self::CONTROLLED_PUBLICATION, 'publication-duplicate', 'Controlled_Publication_Hash');

        $executionResult = $this->runService([
            'c158ExecutionArtifact' => $executionPath,
            'expectedC158ExecutionFileSha1' => strtoupper(sha1((string) file_get_contents($executionPath))),
        ]);
        $publicationResult = $this->runService([
            'controlledPublication' => $publicationPath,
            'expectedControlledPublicationFileSha1' => strtoupper(sha1((string) file_get_contents($publicationPath))),
        ]);

        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_REJECTED_C158_EXECUTION_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION', $executionResult['status']);
        $this->assertFalse($executionResult['c158_execution_convert_from_json_pass']);
        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_REJECTED_CONTROLLED_PUBLICATION_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION', $publicationResult['status']);
        $this->assertFalse($publicationResult['controlled_publication_convert_from_json_pass']);
    }

    /**
     * @dataProvider c158ExecutionIncompleteProvider
     */
    public function test_c158_result_review_rejects_incomplete_execution_evidence(string $field, $value): void
    {
        $result = $this->mutateExecutionAndExecute(function (array $execution) use ($field, $value): array {
            $this->setValueAt($execution, explode('.', $field), $value);
            return $execution;
        }, 'execution-incomplete-'.str_replace('.', '-', $field));

        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_REJECTED_C158_EXECUTION_INCOMPLETE', $result['status'], $field);
    }

    public function c158ExecutionIncompleteProvider(): array
    {
        return [
            ['weekly_swing_watchlist_production_live_runtime_controlled_output_publication_execution_pass', false],
            ['ready_for_weekly_swing_watchlist_controlled_output_publication_result_review', false],
            ['weekly_swing_watchlist_controlled_output_publication_executed', false],
            ['weekly_swing_watchlist_controlled_output_published', false],
            ['weekly_swing_watchlist_controlled_publication_artifact_created', false],
            ['controlled_output_lock_valid', false],
            ['primary_candidate_controlled_published', false],
            ['backup_candidate_controlled_published', false],
            ['c158_controlled_publication_only', false],
            ['topic_stage', 'RESULT_REVIEW'],
        ];
    }

    public function test_c158_result_review_rejects_controlled_publication_integrity_mismatch(): void
    {
        $state = $this->mutateControlledPublicationAndExecute(function (array $publication): array {
            $publication['publication_state'] = 'not_published';
            return $publication;
        }, 'publication-state');
        $hash = $this->mutateControlledPublicationAndExecute(function (array $publication): array {
            $publication['controlled_publication_hash'] = 'broken-hash';
            return $publication;
        }, 'publication-hash');
        $record = $this->mutateControlledPublicationAndExecute(function (array $publication): array {
            array_pop($publication['output_rows']);
            return $publication;
        }, 'publication-record');

        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_REJECTED_FREE_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED', $state['status']);
        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_REJECTED_CONTROLLED_PUBLICATION_INTEGRITY_MISMATCH', $hash['status']);
        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $record['status']);
    }

    public function test_c158_result_review_rejects_free_publication_plan_mutation_or_candidate_scope_change(): void
    {
        $published = $this->mutateControlledPublicationAndExecute(function (array $publication): array {
            $publication['weekly_swing_watchlist_official_output_published'] = true;
            return $publication;
        }, 'published');
        $planConfirm = $this->mutateControlledPublicationAndExecute(function (array $publication): array {
            $publication['plan_confirm_mutated'] = true;
            return $publication;
        }, 'plan-confirm');
        $candidate = $this->mutateControlledPublicationAndExecute(function (array $publication): array {
            $publication['output_rows'][0]['candidate_code'] = 'BROKEN_PRIMARY';
            return $publication;
        }, 'candidate');

        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_REJECTED_FREE_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED', $published['status']);
        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_REJECTED_FREE_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED', $planConfirm['status']);
        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $candidate['status']);
    }

    public function test_c158_result_review_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c158-result-review-negative-remains-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame(self::TEMP_NEGATIVE_STATUS, $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c158_result_review_keeps_e02_primary_b01_backup_a01_comparator_and_free_publication_flags_false(): void
    {
        $result = $this->runService();
        $scorecard = $result['candidate_controlled_publication_result_scorecard'];

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $result['primary_candidate_code']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $result['backup_candidate_code']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $result['comparator_candidate_code']);
        $this->assertTrue($result['primary_candidate_controlled_publication_result_reviewed']);
        $this->assertTrue($result['backup_candidate_controlled_publication_result_reviewed']);
        $this->assertFalse($result['comparator_candidate_controlled_publication_result_reviewed']);
        $this->assertTrue($result['a01_remains_comparator_only']);
        $this->assertTrue($scorecard[0]['controlled_publication_result_reviewed']);
        $this->assertTrue($scorecard[1]['controlled_publication_result_reviewed']);
        $this->assertFalse($scorecard[2]['controlled_publication_result_reviewed']);

        foreach ([
            'weekly_swing_watchlist_official_output_published',
            'weekly_swing_watchlist_publication_allowed',
            'weekly_swing_watchlist_unrestricted_publication_allowed',
            'plan_confirm_mutation_allowed',
            'plan_confirm_mutated',
            'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed',
            'live_plan_confirm_rollout_executed',
        ] as $flag) {
            $this->assertFalse($result[$flag], $flag);
        }
    }

    public function test_c158_result_review_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $first = $this->runService(['createdAt' => '2026-07-15T00:00:00+00:00']);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c158-controlled-output-publication-result-review-second.json';
        $this->tmpFiles[] = $secondOutput;
        $second = $this->runService([
            'output' => $secondOutput,
            'createdAt' => '2026-07-15T00:00:00+00:00',
        ]);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
    }

    public function test_c158_result_review_does_not_mutate_source_artifacts_or_config_defaults(): void
    {
        $beforeExecution = strtoupper(sha1((string) file_get_contents(self::C158_EXECUTION_ARTIFACT)));
        $beforePublication = strtoupper(sha1((string) file_get_contents(self::CONTROLLED_PUBLICATION)));
        $beforeConfig = (string) file_get_contents('config/watchlist.php');

        $this->runService();

        $this->assertSame($beforeExecution, strtoupper(sha1((string) file_get_contents(self::C158_EXECUTION_ARTIFACT))));
        $this->assertSame($beforePublication, strtoupper(sha1((string) file_get_contents(self::CONTROLLED_PUBLICATION))));
        $this->assertSame($beforeConfig, (string) file_get_contents('config/watchlist.php'));
    }

    private function runService(array $options = []): array
    {
        $service = new WatchlistBacktestC158WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationResultReviewService();

        return $service->execute(
            (string) ($options['c158ExecutionArtifact'] ?? self::C158_EXECUTION_ARTIFACT),
            (string) ($options['expectedC158ExecutionHash'] ?? self::C158_EXECUTION_HASH),
            (string) ($options['expectedC158ExecutionFileSha1'] ?? self::C158_EXECUTION_SHA1),
            (string) ($options['controlledPublication'] ?? self::CONTROLLED_PUBLICATION),
            (string) ($options['expectedControlledPublicationHash'] ?? self::CONTROLLED_PUBLICATION_HASH),
            (string) ($options['expectedControlledPublicationFileSha1'] ?? self::CONTROLLED_PUBLICATION_SHA1),
            (string) ($options['output'] ?? $this->output),
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C158_OPERATOR_APPROVED_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_ONLY'),
                'result_review_confirmed' => (bool) ($options['resultReviewConfirmed'] ?? true),
                'controlled_publication_result_confirmed' => (bool) ($options['controlledPublicationResultConfirmed'] ?? true),
                'controlled_publication_only_confirmed' => (bool) ($options['controlledPublicationOnlyConfirmed'] ?? true),
                'plan_confirm_unchanged_confirmed' => (bool) ($options['planConfirmUnchangedConfirmed'] ?? true),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-15T00:00:00+00:00'),
            ]
        );
    }

    private function mutateExecutionAndExecute(callable $mutator, string $name): array
    {
        $execution = json_decode((string) file_get_contents(self::C158_EXECUTION_ARTIFACT), true);
        $execution = $mutator(is_array($execution) ? $execution : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c158-result-review-execution-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($execution, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'c158ExecutionArtifact' => $path,
            'expectedC158ExecutionHash' => (string) ($execution['artifact_hash'] ?? ''),
            'expectedC158ExecutionFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function mutateControlledPublicationAndExecute(callable $mutator, string $name): array
    {
        $publication = json_decode((string) file_get_contents(self::CONTROLLED_PUBLICATION), true);
        $publication = $mutator(is_array($publication) ? $publication : []);
        $path = 'storage/app/watchlist/output/.tmp-c158-result-review-publication-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($publication, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'controlledPublication' => $path,
            'expectedControlledPublicationHash' => (string) ($publication['controlled_publication_hash'] ?? ''),
            'expectedControlledPublicationFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function duplicateTopLevelKeyFixture(string $source, string $name, string $key): string
    {
        $raw = (string) file_get_contents($source);
        $path = 'storage/app/watchlist/backtest/.tmp-c158-result-review-'.$name.'.json';
        if (strpos($source, '/output/') !== false) {
            $path = 'storage/app/watchlist/output/.tmp-c158-result-review-'.$name.'.json';
        }
        $this->tmpFiles[] = $path;
        $duplicateRaw = preg_replace('/\{/', '{"'.$key.'":"DUPLICATE_CASE_KEY",', $raw, 1);
        file_put_contents($path, $duplicateRaw);

        return $path;
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

    private function readOutput(): array
    {
        $decoded = json_decode((string) file_get_contents($this->output), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function cleanupC158TemporaryArtifacts(): void
    {
        foreach ((array) glob('storage/app/watchlist/backtest/c158-*test.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/.tmp-c158*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/output/.tmp-c158*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}

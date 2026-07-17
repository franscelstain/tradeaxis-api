<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC158WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationExecutionService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC158WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationExecutionTest extends TestCase
{
    private const C158_BOUNDARY_ARTIFACT = 'storage/app/watchlist/backtest/c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-boundary-review.json';
    private const C158_BOUNDARY_HASH = 'f17826dd8eb388491be7ef94d18600647dbccc85';
    private const C158_BOUNDARY_SHA1 = 'B61A0522835494811E3306ABDFE37639D5ED56C8';
    private const CONTROLLED_OUTPUT = 'storage/app/watchlist/output/c154-weekly-swing-watchlist-controlled-output.json';
    private const CONTROLLED_OUTPUT_HASH = 'a1ca6b200993e4c70c7dccfa62ace43ffb2f7c4e';
    private const CONTROLLED_OUTPUT_SHA1 = 'AFCA465B7567AFA37034388B257F5F5808B17E5F';
    private const PASS_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_PASSED_CONTROLLED_PUBLICATION_EXECUTED_READY_FOR_RESULT_REVIEW_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const CONFIRMATION_MISSING_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_REJECTED_CONTROLLED_PUBLICATION_EXECUTION_CONFIRMATION_MISSING';
    private const TEMP_NEGATIVE_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const NEXT_RESULT_REVIEW = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW';

    private string $output;
    private string $publication;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c158-controlled-output-publication-execution.json';
        $this->publication = 'storage/app/watchlist/output/.tmp-c158-controlled-publication.json';
        $this->cleanupC158TemporaryArtifacts();
        @unlink($this->output);
        @unlink($this->publication);
    }

    protected function tearDown(): void
    {
        @unlink($this->output);
        @unlink($this->publication);
        foreach ($this->tmpFiles as $path) {
            @unlink($path);
        }
        $this->cleanupC158TemporaryArtifacts();
        parent::tearDown();
    }

    public function test_c158_execution_passes_and_creates_controlled_publication_without_free_publish(): void
    {
        $result = $this->runService();
        $run = $this->readJson($this->output);
        $publication = $this->readJson($this->publication);

        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION', $result['run_code']);
        $this->assertSame('PR-47 / C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION', $result['phase_label']);
        $this->assertSame('C158_CONTROLLED_OUTPUT_PUBLICATION', $result['topic_code']);
        $this->assertSame('EXECUTION', $result['topic_stage']);
        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame(self::PASS_STATUS, $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_production_live_runtime_controlled_output_publication_execution_pass']);
        $this->assertTrue($result['production_live_runtime_controlled_output_publication_execution_pass']);
        $this->assertTrue($result['ready_for_weekly_swing_watchlist_controlled_output_publication_result_review']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_output_publication_executed']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_output_published']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_publication_artifact_created']);
        $this->assertFalse($result['weekly_swing_watchlist_official_output_published']);
        $this->assertFalse($result['weekly_swing_watchlist_publication_allowed']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_publication_allowed']);
        $this->assertFalse($result['weekly_swing_watchlist_unrestricted_publication_allowed']);
        $this->assertFalse($result['plan_confirm_mutated']);
        $this->assertSame(self::NEXT_RESULT_REVIEW, $result['next_step_recommendation']);
        $this->assertSame($result['artifact_hash'], $run['artifact_hash']);
        $this->assertSame($result['controlled_publication_hash'], $publication['controlled_publication_hash']);
        $this->assertSame('controlled', $publication['publication_mode']);
        $this->assertSame('controlled_published', $publication['publication_state']);
        $this->assertSame('not_unrestricted', $publication['public_release_state']);
        $this->assertCount(2, $publication['output_rows']);
    }

    public function test_c158_execution_locks_c158_boundary_and_controlled_output_artifacts(): void
    {
        $result = $this->runService();

        $this->assertSame(self::C158_BOUNDARY_HASH, $result['expected_c158_boundary_hash']);
        $this->assertSame(self::C158_BOUNDARY_HASH, $result['actual_c158_boundary_hash']);
        $this->assertTrue($result['c158_boundary_hash_match']);
        $this->assertSame(self::C158_BOUNDARY_SHA1, $result['actual_c158_boundary_file_sha1']);
        $this->assertTrue($result['c158_boundary_file_sha1_match']);
        $this->assertSame(self::CONTROLLED_OUTPUT_HASH, $result['expected_controlled_output_hash']);
        $this->assertSame(self::CONTROLLED_OUTPUT_HASH, $result['actual_controlled_output_hash']);
        $this->assertTrue($result['controlled_output_hash_match']);
        $this->assertSame(self::CONTROLLED_OUTPUT_SHA1, $result['actual_controlled_output_file_sha1']);
        $this->assertTrue($result['controlled_output_file_sha1_match']);
    }

    public function test_c158_execution_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->runService(['operatorApproved' => false]);
        $missingReference = $this->runService(['approvalReference' => '']);

        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingOperator['status']);
        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingReference['status']);
    }

    public function test_c158_execution_rejects_missing_required_confirmations(): void
    {
        $execution = $this->runService(['controlledPublicationExecutionConfirmed' => false]);
        $controlledOnly = $this->runService(['controlledPublicationOnlyConfirmed' => false]);
        $planConfirm = $this->runService(['planConfirmUnchangedConfirmed' => false]);

        $this->assertSame(self::CONFIRMATION_MISSING_STATUS, $execution['status']);
        $this->assertSame(self::CONFIRMATION_MISSING_STATUS, $controlledOnly['status']);
        $this->assertSame(self::CONFIRMATION_MISSING_STATUS, $planConfirm['status']);
    }

    public function test_c158_execution_rejects_c158_boundary_lock_status_phase_or_next_mismatch(): void
    {
        $hashMismatch = $this->runService(['expectedC158BoundaryHash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedC158BoundaryFileSha1' => 'BADSHA1']);
        $status = $this->mutateBoundaryAndExecute(function (array $boundary): array {
            $boundary['status'] = 'BROKEN_STATUS';
            return $boundary;
        }, 'status');
        $phase = $this->mutateBoundaryAndExecute(function (array $boundary): array {
            $boundary['phase_label'] = 'BROKEN_PHASE';
            return $boundary;
        }, 'phase');
        $next = $this->mutateBoundaryAndExecute(function (array $boundary): array {
            $boundary['next_step_recommendation'] = 'BROKEN_NEXT';
            $boundary['controlled_output_publication_boundary_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $boundary['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $boundary;
        }, 'next');

        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_REJECTED_C158_BOUNDARY_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_REJECTED_C158_BOUNDARY_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_REJECTED_C158_BOUNDARY_STATUS_MISMATCH', $status['status']);
        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_REJECTED_C158_BOUNDARY_PHASE_LABEL_MISMATCH', $phase['status']);
        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_REJECTED_C158_BOUNDARY_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
    }

    public function test_c158_execution_rejects_convert_from_json_duplicate_top_level_keys(): void
    {
        $raw = (string) file_get_contents(self::C158_BOUNDARY_ARTIFACT);
        $path = 'storage/app/watchlist/backtest/.tmp-c158-execution-boundary-duplicate-key.json';
        $this->tmpFiles[] = $path;
        $duplicateRaw = preg_replace('/\{/', "{\"Run_Code\":\"DUPLICATE_CASE_KEY\",", $raw, 1);
        file_put_contents($path, $duplicateRaw);

        $result = $this->runService([
            'c158BoundaryArtifact' => $path,
            'expectedC158BoundaryFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);

        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_REJECTED_C158_BOUNDARY_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION', $result['status']);
        $this->assertFalse($result['c158_boundary_convert_from_json_pass']);
    }

    /**
     * @dataProvider c158BoundaryIncompleteProvider
     */
    public function test_c158_execution_rejects_incomplete_boundary_evidence(string $field, $value): void
    {
        $result = $this->mutateBoundaryAndExecute(function (array $boundary) use ($field, $value): array {
            $this->setValueAt($boundary, explode('.', $field), $value);
            return $boundary;
        }, 'boundary-incomplete-'.str_replace('.', '-', $field));

        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_REJECTED_C158_BOUNDARY_REVIEW_INCOMPLETE', $result['status'], $field);
    }

    public function c158BoundaryIncompleteProvider(): array
    {
        return [
            ['weekly_swing_watchlist_production_live_runtime_controlled_output_publication_boundary_review_pass', false],
            ['ready_for_weekly_swing_watchlist_controlled_output_publication_execution', false],
            ['weekly_swing_watchlist_controlled_publication_allowed_next', false],
            ['controlled_output_lock_valid', false],
            ['primary_candidate_ready_for_controlled_output_publication_execution', false],
            ['backup_candidate_ready_for_controlled_output_publication_execution', false],
            ['a01_remains_comparator_only', false],
            ['c158_boundary_review_only', false],
            ['c158_not_publication', false],
            ['topic_stage', 'EXECUTION'],
        ];
    }

    public function test_c158_execution_rejects_incomplete_or_published_controlled_output(): void
    {
        $published = $this->mutateControlledOutputAndExecute(function (array $output): array {
            $output['weekly_swing_watchlist_official_output_published'] = true;
            return $output;
        }, 'published');
        $publicationAllowed = $this->mutateControlledOutputAndExecute(function (array $output): array {
            $output['weekly_swing_watchlist_publication_allowed'] = true;
            return $output;
        }, 'publication-allowed');
        $candidate = $this->mutateControlledOutputAndExecute(function (array $output): array {
            $output['output_rows'][0]['candidate_code'] = 'BROKEN_PRIMARY';
            return $output;
        }, 'candidate');

        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_REJECTED_CONTROLLED_OUTPUT_INCOMPLETE', $published['status']);
        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_REJECTED_CONTROLLED_OUTPUT_INCOMPLETE', $publicationAllowed['status']);
        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_REJECTED_CONTROLLED_OUTPUT_INCOMPLETE', $candidate['status']);
    }

    public function test_c158_execution_rejects_controlled_output_lock_mismatch(): void
    {
        $hashMismatch = $this->runService(['expectedControlledOutputHash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedControlledOutputFileSha1' => 'BADSHA1']);

        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_REJECTED_CONTROLLED_OUTPUT_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_REJECTED_CONTROLLED_OUTPUT_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
    }

    public function test_c158_execution_rejects_free_publication_plan_mutation_or_candidate_scope_change_from_boundary(): void
    {
        $published = $this->mutateBoundaryAndExecute(function (array $boundary): array {
            $boundary['weekly_swing_watchlist_official_output_published'] = true;
            return $boundary;
        }, 'boundary-published');
        $planConfirm = $this->mutateBoundaryAndExecute(function (array $boundary): array {
            $boundary['plan_confirm_mutated'] = true;
            return $boundary;
        }, 'boundary-plan-confirm');
        $candidate = $this->mutateBoundaryAndExecute(function (array $boundary): array {
            $boundary['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $boundary;
        }, 'boundary-candidate');

        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_REJECTED_FREE_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED', $published['status']);
        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_REJECTED_FREE_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED', $planConfirm['status']);
        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_REJECTED_CANDIDATE_SCOPE_MISMATCH', $candidate['status']);
    }

    public function test_c158_execution_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c158-execution-negative-remains-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame(self::TEMP_NEGATIVE_STATUS, $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c158_execution_records_sections_manifest_and_candidate_scorecard(): void
    {
        $result = $this->runService();
        $run = $this->readJson($this->output);
        $manifest = $result['controlled_publication_manifest'];
        $checklist = $result['controlled_publication_checklist'];
        $scorecard = $result['c158_candidate_controlled_publication_execution_scorecard'];

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['controlled_publication_hash']);
        foreach ([
            'source_artifact_locks',
            'c158_boundary_lock_validation_summary',
            'controlled_output_lock_validation_summary',
            'c158_boundary_carry_forward_summary',
            'controlled_output_carry_forward_summary',
            'controlled_publication_execution_summary',
            'controlled_publication_manifest',
            'controlled_publication_checklist',
            'publication_plan_confirm_safety_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'c158_candidate_controlled_publication_execution_scorecard',
            'documentation_hygiene_guard_summary',
            'progress_summary',
            'planned_next_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }

        $this->assertTrue($manifest['manifest_created']);
        $this->assertSame('controlled', $manifest['publication_mode']);
        $this->assertSame('controlled_published', $manifest['publication_state']);
        $this->assertFalse($manifest['free_publication_allowed']);
        $this->assertTrue($manifest['controlled_output_publication_result_review_required_next']);
        $this->assertTrue($checklist['controlled_publication_execution_completed']);
        $this->assertTrue($checklist['same_topic_number_for_next_stage']);
        $this->assertTrue($scorecard[0]['controlled_published']);
        $this->assertTrue($scorecard[1]['controlled_published']);
        $this->assertFalse($scorecard[2]['controlled_published']);
    }

    public function test_c158_execution_keeps_e02_primary_b01_backup_a01_comparator_and_free_publication_flags_false(): void
    {
        $result = $this->runService();
        $publication = $this->readJson($this->publication);

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $result['primary_candidate_code']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $result['backup_candidate_code']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $result['comparator_candidate_code']);
        $this->assertTrue($result['primary_candidate_controlled_published']);
        $this->assertTrue($result['backup_candidate_controlled_published']);
        $this->assertFalse($result['comparator_candidate_controlled_published']);
        $this->assertTrue($result['a01_remains_comparator_only']);
        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $publication['output_rows'][0]['candidate_code']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $publication['output_rows'][1]['candidate_code']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $publication['comparator_candidate']['candidate_code']);

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
            $this->assertFalse($publication[$flag] ?? false, $flag);
        }
    }

    public function test_c158_execution_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $first = $this->runService(['createdAt' => '2026-07-15T00:00:00+00:00']);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c158-controlled-output-publication-execution-second.json';
        $this->tmpFiles[] = $secondOutput;
        $second = $this->runService([
            'output' => $secondOutput,
            'createdAt' => '2026-07-15T00:00:00+00:00',
        ]);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
        $this->assertSame($first['controlled_publication_hash'], $second['controlled_publication_hash']);
    }

    public function test_c158_execution_does_not_mutate_source_artifacts_or_config_defaults(): void
    {
        $beforeBoundary = strtoupper(sha1((string) file_get_contents(self::C158_BOUNDARY_ARTIFACT)));
        $beforeOutput = strtoupper(sha1((string) file_get_contents(self::CONTROLLED_OUTPUT)));
        $beforeConfig = (string) file_get_contents('config/watchlist.php');

        $this->runService();

        $this->assertSame($beforeBoundary, strtoupper(sha1((string) file_get_contents(self::C158_BOUNDARY_ARTIFACT))));
        $this->assertSame($beforeOutput, strtoupper(sha1((string) file_get_contents(self::CONTROLLED_OUTPUT))));
        $this->assertSame($beforeConfig, (string) file_get_contents('config/watchlist.php'));
    }

    private function runService(array $options = []): array
    {
        $service = new WatchlistBacktestC158WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationExecutionService();

        return $service->execute(
            (string) ($options['c158BoundaryArtifact'] ?? self::C158_BOUNDARY_ARTIFACT),
            (string) ($options['expectedC158BoundaryHash'] ?? self::C158_BOUNDARY_HASH),
            (string) ($options['expectedC158BoundaryFileSha1'] ?? self::C158_BOUNDARY_SHA1),
            (string) ($options['controlledOutput'] ?? self::CONTROLLED_OUTPUT),
            (string) ($options['expectedControlledOutputHash'] ?? self::CONTROLLED_OUTPUT_HASH),
            (string) ($options['expectedControlledOutputFileSha1'] ?? self::CONTROLLED_OUTPUT_SHA1),
            (string) ($options['output'] ?? $this->output),
            (string) ($options['controlledPublication'] ?? $this->publication),
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'controlled_publication_execution_confirmed' => (bool) ($options['controlledPublicationExecutionConfirmed'] ?? true),
                'controlled_publication_only_confirmed' => (bool) ($options['controlledPublicationOnlyConfirmed'] ?? true),
                'plan_confirm_unchanged_confirmed' => (bool) ($options['planConfirmUnchangedConfirmed'] ?? true),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C158_OPERATOR_APPROVED_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_CONTROLLED_ONLY'),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-15T00:00:00+00:00'),
            ]
        );
    }

    private function mutateBoundaryAndExecute(callable $mutator, string $name): array
    {
        $boundary = json_decode((string) file_get_contents(self::C158_BOUNDARY_ARTIFACT), true);
        $boundary = $mutator(is_array($boundary) ? $boundary : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c158-execution-boundary-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($boundary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'c158BoundaryArtifact' => $path,
            'expectedC158BoundaryHash' => (string) ($boundary['artifact_hash'] ?? ''),
            'expectedC158BoundaryFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function mutateControlledOutputAndExecute(callable $mutator, string $name): array
    {
        $controlledOutput = json_decode((string) file_get_contents(self::CONTROLLED_OUTPUT), true);
        $controlledOutput = $mutator(is_array($controlledOutput) ? $controlledOutput : []);
        $path = 'storage/app/watchlist/output/.tmp-c158-controlled-output-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($controlledOutput, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'controlledOutput' => $path,
            'expectedControlledOutputHash' => (string) ($controlledOutput['controlled_output_hash'] ?? ''),
            'expectedControlledOutputFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
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

    private function readJson(string $path): array
    {
        $decoded = json_decode((string) file_get_contents($path), true);

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

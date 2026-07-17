<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC160WeeklySwingWatchlistProductionLiveRuntimePlanConfirmExecutionService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC160WeeklySwingWatchlistProductionLiveRuntimePlanConfirmExecutionTest extends TestCase
{
    private const C160_BOUNDARY_ARTIFACT = 'storage/app/watchlist/backtest/c160-weekly-swing-watchlist-production-live-runtime-plan-confirm-boundary-review.json';
    private const C160_BOUNDARY_HASH = 'b9ca7ca795c2d3a75ad2910263d5a7b3c249bab9';
    private const C160_BOUNDARY_SHA1 = 'D5C708775E5E6DEC644ACD54DEBBEDD370329004';
    private const CONTROLLED_PUBLICATION = 'storage/app/watchlist/output/c158-weekly-swing-watchlist-controlled-publication.json';
    private const CONTROLLED_PUBLICATION_HASH = 'df064c7290ff4c3bfd0c7a8412d39299049c01d5';
    private const CONTROLLED_PUBLICATION_SHA1 = 'D87AB8CD1564BE8B266B8A68011470272D49EE60';
    private const PASS_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION_PASSED_CONTROLLED_PLAN_CONFIRM_EXECUTED_READY_FOR_RESULT_REVIEW_PRIMARY_AND_BACKUP';
    private const NEXT_RESULT_REVIEW = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW';

    private string $output;
    private string $controlledPlanConfirm;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c160-plan-confirm-execution.json';
        $this->controlledPlanConfirm = 'storage/app/watchlist/output/.tmp-c160-controlled-plan-confirm.json';
        $this->cleanupC160TemporaryArtifacts();
        @unlink($this->output);
        @unlink($this->controlledPlanConfirm);
    }

    protected function tearDown(): void
    {
        @unlink($this->output);
        @unlink($this->controlledPlanConfirm);
        foreach ($this->tmpFiles as $path) {
            @unlink($path);
        }
        $this->cleanupC160TemporaryArtifacts();
        parent::tearDown();
    }

    public function test_c160_execution_passes_and_creates_controlled_plan_confirm_without_mutation_or_live_rollout(): void
    {
        $result = $this->runService();
        $run = $this->readJson($this->output);
        $planConfirm = $this->readJson($this->controlledPlanConfirm);

        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION', $result['run_code']);
        $this->assertSame('PR-56 / C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION', $result['phase_label']);
        $this->assertSame('C160_PLAN_CONFIRM', $result['topic_code']);
        $this->assertSame('EXECUTION', $result['topic_stage']);
        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame(self::PASS_STATUS, $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_production_live_runtime_plan_confirm_execution_pass']);
        $this->assertTrue($result['production_live_runtime_plan_confirm_execution_pass']);
        $this->assertTrue($result['ready_for_weekly_swing_watchlist_plan_confirm_result_review']);
        $this->assertTrue($result['weekly_swing_watchlist_plan_confirm_controlled_execution_executed']);
        $this->assertTrue($result['weekly_swing_watchlist_plan_confirm_controlled_artifact_created']);
        $this->assertTrue($result['weekly_swing_watchlist_plan_confirm_controlled_only']);
        $this->assertFalse($result['plan_confirm_mutated']);
        $this->assertFalse($result['plan_confirm_runtime_reads_activated_catalog']);
        $this->assertFalse($result['live_plan_confirm_rollout_executed']);
        $this->assertFalse($result['weekly_swing_watchlist_official_output_published']);
        $this->assertFalse($result['weekly_swing_watchlist_publication_allowed']);
        $this->assertSame(self::NEXT_RESULT_REVIEW, $result['next_step_recommendation']);
        $this->assertSame($result['artifact_hash'], $run['artifact_hash']);
        $this->assertSame($result['controlled_plan_confirm_hash'], $planConfirm['controlled_plan_confirm_hash']);
        $this->assertSame('controlled', $planConfirm['plan_confirm_mode']);
        $this->assertSame('controlled_executed', $planConfirm['plan_confirm_state']);
        $this->assertSame('unchanged', $planConfirm['baseline_plan_confirm_state']);
        $this->assertCount(2, $planConfirm['output_rows']);
    }

    public function test_c160_execution_locks_c160_boundary_and_controlled_publication_artifacts(): void
    {
        $result = $this->runService();

        $this->assertSame(self::C160_BOUNDARY_HASH, $result['expected_c160_boundary_hash']);
        $this->assertSame(self::C160_BOUNDARY_HASH, $result['actual_c160_boundary_hash']);
        $this->assertTrue($result['c160_boundary_hash_match']);
        $this->assertSame(self::C160_BOUNDARY_SHA1, $result['actual_c160_boundary_file_sha1']);
        $this->assertTrue($result['c160_boundary_file_sha1_match']);
        $this->assertSame(self::CONTROLLED_PUBLICATION_HASH, $result['expected_controlled_publication_hash']);
        $this->assertSame(self::CONTROLLED_PUBLICATION_HASH, $result['actual_controlled_publication_hash']);
        $this->assertTrue($result['controlled_publication_hash_match']);
        $this->assertSame(self::CONTROLLED_PUBLICATION_SHA1, $result['actual_controlled_publication_file_sha1']);
        $this->assertTrue($result['controlled_publication_file_sha1_match']);
        $this->assertTrue($result['c160_boundary_lock_valid']);
        $this->assertTrue($result['controlled_publication_lock_valid']);
        $this->assertTrue($result['controlled_publication_integrity_valid']);
    }

    public function test_c160_execution_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->runService(['operatorApproved' => false]);
        $missingReference = $this->runService(['approvalReference' => '']);

        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION_REJECTED_OPERATOR_APPROVAL_MISSING', $missingOperator['status']);
        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION_REJECTED_OPERATOR_APPROVAL_MISSING', $missingReference['status']);
    }

    public function test_c160_execution_rejects_missing_required_confirmations(): void
    {
        $execution = $this->runService(['planConfirmExecutionConfirmed' => false]);
        $controlledOnly = $this->runService(['controlledPlanConfirmOnlyConfirmed' => false]);
        $unchanged = $this->runService(['planConfirmUnchangedConfirmed' => false]);
        $noRollout = $this->runService(['noLivePlanConfirmRolloutConfirmed' => false]);

        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION_REJECTED_PLAN_CONFIRM_EXECUTION_CONFIRMATION_MISSING', $execution['status']);
        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION_REJECTED_CONTROLLED_PLAN_CONFIRM_ONLY_CONFIRMATION_MISSING', $controlledOnly['status']);
        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING', $unchanged['status']);
        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION_REJECTED_NO_LIVE_PLAN_CONFIRM_ROLLOUT_CONFIRMATION_MISSING', $noRollout['status']);
    }

    public function test_c160_execution_rejects_c160_boundary_lock_status_phase_or_next_mismatch(): void
    {
        $hashMismatch = $this->runService(['expectedC160BoundaryHash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedC160BoundaryFileSha1' => 'BADSHA1']);
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
            $boundary['plan_confirm_boundary_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $boundary['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $boundary;
        }, 'next');

        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION_REJECTED_C160_BOUNDARY_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION_REJECTED_C160_BOUNDARY_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION_REJECTED_C160_BOUNDARY_STATUS_MISMATCH', $status['status']);
        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION_REJECTED_C160_BOUNDARY_PHASE_LABEL_MISMATCH', $phase['status']);
        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION_REJECTED_C160_BOUNDARY_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
    }

    public function test_c160_execution_rejects_c160_boundary_convert_from_json_duplicate_top_level_keys(): void
    {
        $raw = (string) file_get_contents(self::C160_BOUNDARY_ARTIFACT);
        $path = 'storage/app/watchlist/backtest/.tmp-c160-plan-confirm-execution-boundary-duplicate-key.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, preg_replace('/\{/', "{\"Run_Code\":\"DUPLICATE_CASE_KEY\",", $raw, 1));

        $result = $this->runService([
            'c160BoundaryArtifact' => $path,
            'expectedC160BoundaryFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);

        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION_REJECTED_C160_BOUNDARY_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION', $result['status']);
        $this->assertFalse($result['c160_boundary_convert_from_json_pass']);
    }

    /**
     * @dataProvider incompleteBoundaryProvider
     */
    public function test_c160_execution_rejects_incomplete_boundary_evidence(string $field, $value): void
    {
        $result = $this->mutateBoundaryAndExecute(function (array $boundary) use ($field, $value): array {
            $this->setValueAt($boundary, explode('.', $field), $value);
            return $boundary;
        }, 'boundary-incomplete-'.str_replace('.', '-', $field));

        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION_REJECTED_C160_BOUNDARY_REVIEW_INCOMPLETE', $result['status'], $field);
    }

    public function incompleteBoundaryProvider(): array
    {
        return [
            ['weekly_swing_watchlist_production_live_runtime_plan_confirm_boundary_review_pass', false],
            ['ready_for_weekly_swing_watchlist_plan_confirm_execution', false],
            ['plan_confirm_execution_allowed_next', false],
            ['primary_candidate_ready_for_plan_confirm_execution', false],
            ['backup_candidate_ready_for_plan_confirm_execution', false],
            ['a01_remains_comparator_only', false],
            ['c160_boundary_review_only', false],
            ['c160_topic_number_retained_for_execution', false],
            ['topic_stage', 'EXECUTION'],
        ];
    }

    public function test_c160_execution_rejects_controlled_publication_lock_mismatch_or_incomplete_publication(): void
    {
        $hashMismatch = $this->runService(['expectedControlledPublicationHash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedControlledPublicationFileSha1' => 'BADSHA1']);
        $published = $this->mutateControlledPublicationAndExecute(function (array $publication): array {
            $publication['weekly_swing_watchlist_official_output_published'] = true;
            return $publication;
        }, 'published');
        $candidate = $this->mutateControlledPublicationAndExecute(function (array $publication): array {
            $publication['output_rows'][0]['candidate_code'] = 'BROKEN_PRIMARY';
            return $publication;
        }, 'candidate');

        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION_REJECTED_CONTROLLED_PUBLICATION_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION_REJECTED_CONTROLLED_PUBLICATION_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION_REJECTED_CONTROLLED_PUBLICATION_INCOMPLETE', $published['status']);
        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION_REJECTED_CONTROLLED_PUBLICATION_INCOMPLETE', $candidate['status']);
    }

    public function test_c160_execution_rejects_publication_plan_mutation_or_candidate_scope_change_from_boundary(): void
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

        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED', $published['status']);
        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED', $planConfirm['status']);
        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION_REJECTED_CANDIDATE_SCOPE_MISMATCH', $candidate['status']);
    }

    public function test_c160_execution_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c160-plan-confirm-execution-negative-remains-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS', $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c160_execution_records_sections_manifest_checklist_and_scorecard(): void
    {
        $result = $this->runService();
        $run = $this->readJson($this->output);
        $manifest = $result['controlled_plan_confirm_manifest'];
        $checklist = $result['controlled_plan_confirm_checklist'];
        $scorecard = $result['c160_candidate_plan_confirm_execution_scorecard'];

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['controlled_plan_confirm_hash']);
        foreach ([
            'source_artifact_locks',
            'c160_boundary_lock_validation_summary',
            'controlled_publication_lock_validation_summary',
            'c160_boundary_carry_forward_summary',
            'controlled_publication_carry_forward_summary',
            'controlled_plan_confirm_execution_summary',
            'controlled_plan_confirm_manifest',
            'controlled_plan_confirm_checklist',
            'publication_plan_confirm_safety_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'c160_candidate_plan_confirm_execution_scorecard',
            'documentation_hygiene_guard_summary',
            'progress_summary',
            'planned_next_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }

        $this->assertTrue($manifest['manifest_created']);
        $this->assertSame('controlled', $manifest['plan_confirm_mode']);
        $this->assertSame('controlled_executed', $manifest['plan_confirm_state']);
        $this->assertSame('unchanged', $manifest['baseline_plan_confirm_state']);
        $this->assertFalse($manifest['free_publication_allowed']);
        $this->assertTrue($manifest['plan_confirm_result_review_required_next']);
        $this->assertTrue($checklist['controlled_plan_confirm_execution_completed']);
        $this->assertTrue($checklist['same_topic_number_for_next_stage']);
        $this->assertTrue($scorecard[0]['plan_confirm_controlled_executed']);
        $this->assertTrue($scorecard[1]['plan_confirm_controlled_executed']);
        $this->assertFalse($scorecard[2]['plan_confirm_controlled_executed']);
    }

    public function test_c160_execution_keeps_e02_primary_b01_backup_a01_comparator(): void
    {
        $result = $this->runService();
        $planConfirm = $this->readJson($this->controlledPlanConfirm);

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $result['primary_candidate_code']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $result['backup_candidate_code']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $result['comparator_candidate_code']);
        $this->assertTrue($result['primary_candidate_plan_confirm_controlled_executed']);
        $this->assertTrue($result['backup_candidate_plan_confirm_controlled_executed']);
        $this->assertFalse($result['comparator_candidate_plan_confirm_controlled_executed']);
        $this->assertTrue($result['a01_remains_comparator_only']);
        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $planConfirm['output_rows'][0]['candidate_code']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $planConfirm['output_rows'][1]['candidate_code']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $planConfirm['comparator_candidate']['candidate_code']);
    }

    public function test_c160_execution_output_is_deterministic_enough_for_hash_validation(): void
    {
        $first = $this->runService(['createdAt' => '2026-07-16T00:00:00+00:00']);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c160-plan-confirm-execution-second.json';
        $this->tmpFiles[] = $secondOutput;
        $second = $this->runService([
            'output' => $secondOutput,
            'createdAt' => '2026-07-16T00:00:00+00:00',
        ]);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
        $this->assertSame($first['controlled_plan_confirm_hash'], $second['controlled_plan_confirm_hash']);
    }

    public function test_c160_execution_does_not_mutate_source_artifacts_or_config_defaults(): void
    {
        $beforeBoundary = strtoupper(sha1((string) file_get_contents(self::C160_BOUNDARY_ARTIFACT)));
        $beforePublication = strtoupper(sha1((string) file_get_contents(self::CONTROLLED_PUBLICATION)));
        $beforeConfig = (string) file_get_contents('config/watchlist.php');

        $this->runService();

        $this->assertSame($beforeBoundary, strtoupper(sha1((string) file_get_contents(self::C160_BOUNDARY_ARTIFACT))));
        $this->assertSame($beforePublication, strtoupper(sha1((string) file_get_contents(self::CONTROLLED_PUBLICATION))));
        $this->assertSame($beforeConfig, (string) file_get_contents('config/watchlist.php'));
    }

    private function runService(array $options = []): array
    {
        $service = new WatchlistBacktestC160WeeklySwingWatchlistProductionLiveRuntimePlanConfirmExecutionService();

        return $service->execute(
            (string) ($options['c160BoundaryArtifact'] ?? self::C160_BOUNDARY_ARTIFACT),
            (string) ($options['expectedC160BoundaryHash'] ?? self::C160_BOUNDARY_HASH),
            (string) ($options['expectedC160BoundaryFileSha1'] ?? self::C160_BOUNDARY_SHA1),
            (string) ($options['controlledPublication'] ?? self::CONTROLLED_PUBLICATION),
            (string) ($options['expectedControlledPublicationHash'] ?? self::CONTROLLED_PUBLICATION_HASH),
            (string) ($options['expectedControlledPublicationFileSha1'] ?? self::CONTROLLED_PUBLICATION_SHA1),
            (string) ($options['output'] ?? $this->output),
            (string) ($options['controlledPlanConfirm'] ?? $this->controlledPlanConfirm),
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'plan_confirm_execution_confirmed' => (bool) ($options['planConfirmExecutionConfirmed'] ?? true),
                'controlled_plan_confirm_only_confirmed' => (bool) ($options['controlledPlanConfirmOnlyConfirmed'] ?? true),
                'plan_confirm_unchanged_confirmed' => (bool) ($options['planConfirmUnchangedConfirmed'] ?? true),
                'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['noLivePlanConfirmRolloutConfirmed'] ?? true),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C160_OPERATOR_APPROVED_CONTROLLED_PLAN_CONFIRM_EXECUTION_ONLY'),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-16T00:00:00+00:00'),
            ]
        );
    }

    private function mutateBoundaryAndExecute(callable $mutator, string $name): array
    {
        $boundary = json_decode((string) file_get_contents(self::C160_BOUNDARY_ARTIFACT), true);
        $boundary = $mutator(is_array($boundary) ? $boundary : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c160-plan-confirm-execution-boundary-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($boundary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'c160BoundaryArtifact' => $path,
            'expectedC160BoundaryHash' => (string) ($boundary['artifact_hash'] ?? ''),
            'expectedC160BoundaryFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function mutateControlledPublicationAndExecute(callable $mutator, string $name): array
    {
        $publication = json_decode((string) file_get_contents(self::CONTROLLED_PUBLICATION), true);
        $publication = $mutator(is_array($publication) ? $publication : []);
        $path = 'storage/app/watchlist/output/.tmp-c160-controlled-publication-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($publication, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'controlledPublication' => $path,
            'expectedControlledPublicationHash' => (string) ($publication['controlled_publication_hash'] ?? ''),
            'expectedControlledPublicationFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
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

    private function cleanupC160TemporaryArtifacts(): void
    {
        foreach ((array) glob('storage/app/watchlist/backtest/c160-*test.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/.tmp-c160*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/output/.tmp-c160*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}

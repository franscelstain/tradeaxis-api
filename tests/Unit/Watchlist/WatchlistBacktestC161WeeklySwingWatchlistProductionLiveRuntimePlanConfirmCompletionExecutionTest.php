<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC161WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionExecutionService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC161WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionExecutionTest extends TestCase
{
    private const C161_BOUNDARY_ARTIFACT = 'storage/app/watchlist/backtest/c161-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-boundary-review.json';
    private const C161_BOUNDARY_HASH = 'fe92324430bbad2f9caa74538976a9225a4a2807';
    private const C161_BOUNDARY_SHA1 = '8BEEA9838E6C22646331A151A38404A7FE2E4CC5';
    private const PASS_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_EXECUTION_PASSED_CONTROLLED_COMPLETION_EXECUTED_READY_FOR_RESULT_REVIEW_PRIMARY_AND_BACKUP';
    private const NEXT_RESULT_REVIEW = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_RESULT_REVIEW';

    private string $output;
    private string $controlledCompletion;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c161-plan-confirm-completion-execution.json';
        $this->controlledCompletion = 'storage/app/watchlist/output/.tmp-c161-controlled-plan-confirm-completion.json';
        $this->cleanupC161TemporaryArtifacts();
        @unlink($this->output);
        @unlink($this->controlledCompletion);
    }

    protected function tearDown(): void
    {
        @unlink($this->output);
        @unlink($this->controlledCompletion);
        foreach ($this->tmpFiles as $path) {
            @unlink($path);
        }
        $this->cleanupC161TemporaryArtifacts();
        parent::tearDown();
    }

    public function test_c161_completion_execution_passes_and_creates_controlled_completion_without_mutation_or_live_rollout(): void
    {
        $result = $this->runService();
        $run = $this->readJson($this->output);
        $completion = $this->readJson($this->controlledCompletion);

        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_EXECUTION', $result['run_code']);
        $this->assertSame('PR-61 / C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_EXECUTION', $result['phase_label']);
        $this->assertSame('C161_PLAN_CONFIRM_COMPLETION', $result['topic_code']);
        $this->assertSame('PLAN_CONFIRM_COMPLETION_EXECUTION', $result['topic_stage']);
        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame(self::PASS_STATUS, $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_execution_pass']);
        $this->assertTrue($result['production_live_runtime_plan_confirm_completion_execution_pass']);
        $this->assertTrue($result['ready_for_weekly_swing_watchlist_plan_confirm_completion_result_review']);
        $this->assertTrue($result['weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed']);
        $this->assertTrue($result['weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created']);
        $this->assertTrue($result['weekly_swing_watchlist_plan_confirm_completion_controlled_only']);
        $this->assertFalse($result['plan_confirm_mutated']);
        $this->assertFalse($result['plan_confirm_runtime_reads_activated_catalog']);
        $this->assertFalse($result['live_plan_confirm_rollout_executed']);
        $this->assertFalse($result['weekly_swing_watchlist_official_output_published']);
        $this->assertFalse($result['weekly_swing_watchlist_publication_allowed']);
        $this->assertSame(self::NEXT_RESULT_REVIEW, $result['next_step_recommendation']);
        $this->assertSame(self::NEXT_RESULT_REVIEW, $result['next_plan_confirm_completion_result_review_decision']['next_recommendation']);
        $this->assertTrue($result['next_plan_confirm_completion_result_review_decision']['same_topic_c161_continues']);
        $this->assertSame($result['artifact_hash'], $run['artifact_hash']);
        $this->assertSame($result['controlled_completion_hash'], $completion['controlled_completion_hash']);
        $this->assertSame('controlled', $completion['plan_confirm_completion_mode']);
        $this->assertSame('controlled_completion_executed', $completion['plan_confirm_completion_state']);
        $this->assertSame('closed_and_unchanged', $completion['baseline_plan_confirm_state']);
        $this->assertCount(2, $completion['output_rows']);
    }

    public function test_c161_completion_execution_locks_c161_boundary_artifact(): void
    {
        $result = $this->runService();

        $this->assertSame(self::C161_BOUNDARY_HASH, $result['expected_c161_boundary_hash']);
        $this->assertSame(self::C161_BOUNDARY_HASH, $result['actual_c161_boundary_hash']);
        $this->assertTrue($result['c161_boundary_hash_match']);
        $this->assertSame(self::C161_BOUNDARY_SHA1, $result['actual_c161_boundary_file_sha1']);
        $this->assertTrue($result['c161_boundary_file_sha1_match']);
        $this->assertTrue($result['c161_boundary_lock_valid']);
        $this->assertTrue($result['c161_completion_boundary_valid']);
        $this->assertTrue($result['c161_boundary_convert_from_json_pass']);
    }

    public function test_c161_completion_execution_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->runService(['operatorApproved' => false]);
        $missingReference = $this->runService(['approvalReference' => '']);

        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_EXECUTION_REJECTED_OPERATOR_APPROVAL_MISSING', $missingOperator['status']);
        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_EXECUTION_REJECTED_OPERATOR_APPROVAL_MISSING', $missingReference['status']);
    }

    public function test_c161_completion_execution_rejects_missing_required_confirmations(): void
    {
        $execution = $this->runService(['completionExecutionConfirmed' => false]);
        $controlledOnly = $this->runService(['controlledCompletionOnlyConfirmed' => false]);
        $unchanged = $this->runService(['planConfirmUnchangedConfirmed' => false]);
        $noRollout = $this->runService(['noLivePlanConfirmRolloutConfirmed' => false]);
        $freeLock = $this->runService(['freePublicationLockedConfirmed' => false]);

        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_EXECUTION_REJECTED_COMPLETION_EXECUTION_CONFIRMATION_MISSING', $execution['status']);
        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_EXECUTION_REJECTED_CONTROLLED_COMPLETION_ONLY_CONFIRMATION_MISSING', $controlledOnly['status']);
        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_EXECUTION_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING', $unchanged['status']);
        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_EXECUTION_REJECTED_NO_LIVE_PLAN_CONFIRM_ROLLOUT_CONFIRMATION_MISSING', $noRollout['status']);
        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_EXECUTION_REJECTED_FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING', $freeLock['status']);
    }

    public function test_c161_completion_execution_rejects_c161_boundary_lock_status_phase_or_next_mismatch(): void
    {
        $missing = $this->runService([
            'c161BoundaryArtifact' => 'storage/app/watchlist/backtest/.tmp-c161-completion-execution-source-missing.json',
            'expectedC161BoundaryHash' => 'missing',
            'expectedC161BoundaryFileSha1' => 'missing',
        ]);
        $hashMismatch = $this->runService(['expectedC161BoundaryHash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedC161BoundaryFileSha1' => 'BADSHA1']);
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
            $boundary['next_plan_confirm_completion_execution_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $boundary['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $boundary;
        }, 'next');

        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_EXECUTION_REJECTED_C161_BOUNDARY_ARTIFACT_LOCK_MISMATCH', $missing['status']);
        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_EXECUTION_REJECTED_C161_BOUNDARY_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_EXECUTION_REJECTED_C161_BOUNDARY_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_EXECUTION_REJECTED_C161_BOUNDARY_STATUS_MISMATCH', $status['status']);
        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_EXECUTION_REJECTED_C161_BOUNDARY_PHASE_LABEL_MISMATCH', $phase['status']);
        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_EXECUTION_REJECTED_C161_BOUNDARY_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
    }

    public function test_c161_completion_execution_rejects_c161_boundary_convert_from_json_duplicate_top_level_keys(): void
    {
        $raw = (string) file_get_contents(self::C161_BOUNDARY_ARTIFACT);
        $path = 'storage/app/watchlist/backtest/.tmp-c161-completion-execution-boundary-duplicate-key.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, preg_replace('/\{/', "{\"Run_Code\":\"DUPLICATE_CASE_KEY\",", $raw, 1));

        $result = $this->runService([
            'c161BoundaryArtifact' => $path,
            'expectedC161BoundaryFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);

        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_EXECUTION_REJECTED_C161_BOUNDARY_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION', $result['status']);
        $this->assertFalse($result['c161_boundary_convert_from_json_pass']);
    }

    /**
     * @dataProvider incompleteBoundaryProvider
     */
    public function test_c161_completion_execution_rejects_incomplete_boundary_evidence(string $field, $value): void
    {
        $result = $this->mutateBoundaryAndExecute(function (array $boundary) use ($field, $value): array {
            $this->setValueAt($boundary, explode('.', $field), $value);
            return $boundary;
        }, 'boundary-incomplete-'.str_replace('.', '-', $field));

        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_EXECUTION_REJECTED_C161_COMPLETION_BOUNDARY_INCOMPLETE', $result['status'], $field);
    }

    public function incompleteBoundaryProvider(): array
    {
        return [
            ['weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_boundary_review_pass', false],
            ['completion_boundary_cleared', false],
            ['ready_for_weekly_swing_watchlist_plan_confirm_completion_execution', false],
            ['production_live_runtime_plan_confirm_completion_execution_allowed_next', false],
            ['weekly_swing_watchlist_plan_confirm_completion_boundary_manifest_created', false],
            ['primary_candidate_ready_for_plan_confirm_completion_execution', false],
            ['backup_candidate_ready_for_plan_confirm_completion_execution', false],
            ['a01_remains_comparator_only', false],
            ['c161_plan_confirm_completion_boundary_review_only', false],
            ['c161_controlled_plan_confirm_completion_only', false],
            ['topic_stage', 'PLAN_CONFIRM_COMPLETION_EXECUTION'],
            ['c161_completion_boundary_decision.review_pass', false],
            ['weekly_swing_watchlist_plan_confirm_completion_boundary_manifest.boundary_artifact_only', false],
            ['weekly_swing_watchlist_plan_confirm_completion_boundary_manifest.ready_for_plan_confirm_completion_execution', false],
            ['weekly_swing_watchlist_plan_confirm_completion_boundary_checklist.completion_boundary_reviewed', false],
            ['weekly_swing_watchlist_plan_confirm_completion_boundary_checklist.artifact_only', false],
            ['next_plan_confirm_completion_execution_decision.review_valid', false],
            ['next_plan_confirm_completion_execution_decision.same_topic_c161_continues', false],
        ];
    }

    public function test_c161_completion_execution_rejects_publication_plan_mutation_or_candidate_scope_change_from_boundary(): void
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
        $a01 = $this->mutateBoundaryAndExecute(function (array $boundary): array {
            $boundary['a01_promoted'] = true;
            return $boundary;
        }, 'boundary-a01');

        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_EXECUTION_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED', $published['status']);
        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_EXECUTION_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED', $planConfirm['status']);
        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_EXECUTION_REJECTED_CANDIDATE_SCOPE_MISMATCH', $candidate['status']);
        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_EXECUTION_REJECTED_CANDIDATE_SCOPE_MISMATCH', $a01['status']);
    }

    public function test_c161_completion_execution_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c161-completion-execution-negative-remains-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_EXECUTION_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS', $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c161_completion_execution_records_sections_manifest_checklist_and_scorecard(): void
    {
        $result = $this->runService();
        $run = $this->readJson($this->output);
        $manifest = $result['controlled_completion_manifest'];
        $checklist = $result['controlled_completion_checklist'];
        $scorecard = $result['c161_candidate_completion_execution_scorecard'];

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['controlled_completion_hash']);
        foreach ([
            'source_artifact_locks',
            'c161_boundary_lock_validation_summary',
            'c161_boundary_carry_forward_summary',
            'controlled_completion_execution_summary',
            'controlled_completion_manifest',
            'controlled_completion_checklist',
            'publication_plan_confirm_safety_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'c161_candidate_completion_execution_scorecard',
            'next_plan_confirm_completion_result_review_decision',
            'documentation_hygiene_guard_summary',
            'progress_summary',
            'planned_next_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }

        $this->assertTrue($manifest['manifest_created']);
        $this->assertSame('controlled_completion_executed', $manifest['plan_confirm_completion_state']);
        $this->assertTrue($manifest['completion_result_review_required_next']);
        $this->assertTrue($checklist['controlled_completion_execution_completed']);
        $this->assertTrue($checklist['same_topic_number_for_next_stage']);
        $this->assertTrue($scorecard[0]['completion_controlled_executed']);
        $this->assertTrue($scorecard[1]['completion_controlled_executed']);
        $this->assertFalse($scorecard[2]['completion_controlled_executed']);
    }

    public function test_c161_completion_execution_keeps_e02_primary_b01_backup_a01_comparator_and_safety_flags_false(): void
    {
        $result = $this->runService();

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $result['primary_candidate_code']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $result['backup_candidate_code']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $result['comparator_candidate_code']);
        $this->assertTrue($result['primary_candidate_completion_controlled_executed']);
        $this->assertTrue($result['backup_candidate_completion_controlled_executed']);
        $this->assertFalse($result['comparator_candidate_completion_controlled_executed']);
        $this->assertTrue($result['a01_remains_comparator_only']);

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
            $this->assertFalse($result['publication_plan_confirm_safety_summary'][$flag], $flag);
        }
    }

    public function test_c161_completion_execution_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $first = $this->runService(['createdAt' => '2026-07-16T00:00:00+00:00']);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c161-plan-confirm-completion-execution-second.json';
        $this->tmpFiles[] = $secondOutput;
        $second = $this->runService([
            'output' => $secondOutput,
            'createdAt' => '2026-07-16T00:00:00+00:00',
        ]);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
        $this->assertSame($first['controlled_completion_hash'], $second['controlled_completion_hash']);
    }

    public function test_c161_completion_execution_does_not_mutate_boundary_artifact_or_config_defaults(): void
    {
        $beforeBoundary = strtoupper(sha1((string) file_get_contents(self::C161_BOUNDARY_ARTIFACT)));
        $beforeConfig = (string) file_get_contents('config/watchlist.php');

        $this->runService();

        $this->assertSame($beforeBoundary, strtoupper(sha1((string) file_get_contents(self::C161_BOUNDARY_ARTIFACT))));
        $this->assertSame($beforeConfig, (string) file_get_contents('config/watchlist.php'));
    }

    private function runService(array $options = []): array
    {
        $service = new WatchlistBacktestC161WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionExecutionService();

        return $service->execute(
            (string) ($options['c161BoundaryArtifact'] ?? self::C161_BOUNDARY_ARTIFACT),
            (string) ($options['expectedC161BoundaryHash'] ?? self::C161_BOUNDARY_HASH),
            (string) ($options['expectedC161BoundaryFileSha1'] ?? self::C161_BOUNDARY_SHA1),
            (string) ($options['output'] ?? $this->output),
            (string) ($options['controlledCompletion'] ?? $this->controlledCompletion),
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'completion_execution_confirmed' => (bool) ($options['completionExecutionConfirmed'] ?? true),
                'controlled_completion_only_confirmed' => (bool) ($options['controlledCompletionOnlyConfirmed'] ?? true),
                'plan_confirm_unchanged_confirmed' => (bool) ($options['planConfirmUnchangedConfirmed'] ?? true),
                'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['noLivePlanConfirmRolloutConfirmed'] ?? true),
                'free_publication_locked_confirmed' => (bool) ($options['freePublicationLockedConfirmed'] ?? true),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C161_OPERATOR_APPROVED_PLAN_CONFIRM_COMPLETION_EXECUTION_ONLY'),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-16T00:00:00+00:00'),
            ]
        );
    }

    private function mutateBoundaryAndExecute(callable $mutator, string $name): array
    {
        $boundary = json_decode((string) file_get_contents(self::C161_BOUNDARY_ARTIFACT), true);
        $boundary = $mutator(is_array($boundary) ? $boundary : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c161-completion-execution-source-boundary-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($boundary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'c161BoundaryArtifact' => $path,
            'expectedC161BoundaryHash' => (string) ($boundary['artifact_hash'] ?? ''),
            'expectedC161BoundaryFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
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

    private function cleanupC161TemporaryArtifacts(): void
    {
        foreach ((array) glob('storage/app/watchlist/backtest/c161-*completion-execution*-test.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/c161-*completion*-execution*-test.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/.tmp-c161-plan-confirm-completion-execution*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/.tmp-c161-completion-execution-*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/output/.tmp-c161-controlled-plan-confirm-completion*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}

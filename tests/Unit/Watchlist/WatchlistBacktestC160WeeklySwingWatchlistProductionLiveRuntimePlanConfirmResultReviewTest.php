<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC160WeeklySwingWatchlistProductionLiveRuntimePlanConfirmResultReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC160WeeklySwingWatchlistProductionLiveRuntimePlanConfirmResultReviewTest extends TestCase
{
    private const C160_EXECUTION_ARTIFACT = 'storage/app/watchlist/backtest/c160-weekly-swing-watchlist-production-live-runtime-plan-confirm-execution.json';
    private const C160_EXECUTION_HASH = '8937d98bf09e440ab527b812051779a2eda8a89c';
    private const C160_EXECUTION_SHA1 = 'B7388BB99473BB12725AEE345E97C774E9D2618A';
    private const CONTROLLED_PLAN_CONFIRM = 'storage/app/watchlist/output/c160-weekly-swing-watchlist-controlled-plan-confirm.json';
    private const CONTROLLED_PLAN_CONFIRM_HASH = '10164115c468c66c1d8cced1e29985698c66f056';
    private const CONTROLLED_PLAN_CONFIRM_SHA1 = 'A696DDD288CAAD469CA02B61D155EB4EE3A8F71B';
    private const PASS_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW_PASSED_READY_FOR_PLAN_CONFIRM_OPERATOR_GO_NO_GO_REVIEW_PRIMARY_AND_BACKUP';
    private const NEXT_OPERATOR = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_OPERATOR_GO_NO_GO_REVIEW';

    private string $output;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c160-plan-confirm-result-review.json';
        $this->cleanupC160TemporaryArtifacts();
        @unlink($this->output);
    }

    protected function tearDown(): void
    {
        @unlink($this->output);
        foreach ($this->tmpFiles as $path) {
            @unlink($path);
        }
        $this->cleanupC160TemporaryArtifacts();
        parent::tearDown();
    }

    public function test_c160_result_review_passes_and_keeps_same_topic_number_for_operator_review(): void
    {
        $result = $this->runService();

        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW', $result['run_code']);
        $this->assertSame('PR-57 / C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW', $result['phase_label']);
        $this->assertSame('C160_PLAN_CONFIRM', $result['topic_code']);
        $this->assertSame('RESULT_REVIEW', $result['topic_stage']);
        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame(self::PASS_STATUS, $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_production_live_runtime_plan_confirm_result_review_pass']);
        $this->assertTrue($result['production_live_runtime_plan_confirm_result_review_pass']);
        $this->assertTrue($result['weekly_swing_watchlist_plan_confirm_result_reviewed']);
        $this->assertTrue($result['weekly_swing_watchlist_plan_confirm_result_review_manifest_created']);
        $this->assertTrue($result['ready_for_weekly_swing_watchlist_plan_confirm_operator_go_no_go_review']);
        $this->assertTrue($result['production_live_runtime_plan_confirm_operator_go_no_go_review_allowed_next']);
        $this->assertTrue($result['weekly_swing_watchlist_plan_confirm_operator_go_no_go_review_allowed_next']);
        $this->assertTrue($result['weekly_swing_watchlist_plan_confirm_controlled_execution_executed']);
        $this->assertTrue($result['weekly_swing_watchlist_plan_confirm_controlled_artifact_created']);
        $this->assertFalse($result['weekly_swing_watchlist_official_output_published']);
        $this->assertFalse($result['weekly_swing_watchlist_publication_allowed']);
        $this->assertFalse($result['plan_confirm_mutated']);
        $this->assertFalse($result['plan_confirm_runtime_reads_activated_catalog']);
        $this->assertFalse($result['live_plan_confirm_rollout_executed']);
        $this->assertSame(self::NEXT_OPERATOR, $result['next_step_recommendation']);
        $this->assertFileExists($this->output);
    }

    public function test_c160_result_review_records_dual_locks_and_sections(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();

        foreach ([
            'source_artifact_locks',
            'c160_execution_lock_validation_summary',
            'controlled_plan_confirm_lock_validation_summary',
            'c160_execution_carry_forward_summary',
            'controlled_plan_confirm_result_review_summary',
            'controlled_plan_confirm_integrity_summary',
            'publication_plan_confirm_safety_summary',
            'candidate_plan_confirm_result_scorecard',
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

        $this->assertSame(self::C160_EXECUTION_HASH, $result['expected_c160_execution_hash']);
        $this->assertSame(self::C160_EXECUTION_HASH, $result['actual_c160_execution_hash']);
        $this->assertTrue($result['c160_execution_hash_match']);
        $this->assertSame(self::C160_EXECUTION_SHA1, $result['actual_c160_execution_file_sha1']);
        $this->assertTrue($result['c160_execution_file_sha1_match']);
        $this->assertSame(self::CONTROLLED_PLAN_CONFIRM_HASH, $result['expected_controlled_plan_confirm_hash']);
        $this->assertSame(self::CONTROLLED_PLAN_CONFIRM_HASH, $result['actual_controlled_plan_confirm_hash']);
        $this->assertTrue($result['controlled_plan_confirm_hash_match']);
        $this->assertSame(self::CONTROLLED_PLAN_CONFIRM_SHA1, $result['actual_controlled_plan_confirm_file_sha1']);
        $this->assertTrue($result['controlled_plan_confirm_file_sha1_match']);
        $this->assertSame(2, $result['controlled_plan_confirm_record_count']);
        $this->assertSame(self::NEXT_OPERATOR, $run['planned_next_summary']['planned_next_review']);
    }

    public function test_c160_result_review_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->runService(['operatorApproved' => false]);
        $missingReference = $this->runService(['approvalReference' => '']);

        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingOperator['status']);
        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingReference['status']);
    }

    public function test_c160_result_review_rejects_missing_required_confirmations(): void
    {
        $resultReview = $this->runService(['resultReviewConfirmed' => false]);
        $controlledResult = $this->runService(['controlledPlanConfirmResultConfirmed' => false]);
        $controlledOnly = $this->runService(['controlledPlanConfirmOnlyConfirmed' => false]);
        $unchanged = $this->runService(['planConfirmUnchangedConfirmed' => false]);
        $noRollout = $this->runService(['noLivePlanConfirmRolloutConfirmed' => false]);

        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW_REJECTED_RESULT_REVIEW_CONFIRMATION_MISSING', $resultReview['status']);
        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW_REJECTED_CONTROLLED_PLAN_CONFIRM_RESULT_CONFIRMATION_MISSING', $controlledResult['status']);
        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW_REJECTED_CONTROLLED_PLAN_CONFIRM_ONLY_CONFIRMATION_MISSING', $controlledOnly['status']);
        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING', $unchanged['status']);
        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW_REJECTED_NO_LIVE_PLAN_CONFIRM_ROLLOUT_CONFIRMATION_MISSING', $noRollout['status']);
    }

    public function test_c160_result_review_rejects_execution_artifact_lock_status_phase_or_next_mismatch(): void
    {
        $hashMismatch = $this->runService(['expectedC160ExecutionHash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedC160ExecutionFileSha1' => 'BADSHA1']);
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

        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW_REJECTED_C160_EXECUTION_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW_REJECTED_C160_EXECUTION_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW_REJECTED_C160_EXECUTION_STATUS_MISMATCH', $status['status']);
        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW_REJECTED_C160_EXECUTION_PHASE_LABEL_MISMATCH', $phase['status']);
        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW_REJECTED_C160_EXECUTION_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
    }

    public function test_c160_result_review_rejects_controlled_plan_confirm_lock_mismatch(): void
    {
        $hashMismatch = $this->runService(['expectedControlledPlanConfirmHash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedControlledPlanConfirmFileSha1' => 'BADSHA1']);

        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW_REJECTED_CONTROLLED_PLAN_CONFIRM_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW_REJECTED_CONTROLLED_PLAN_CONFIRM_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
    }

    public function test_c160_result_review_rejects_convert_from_json_duplicate_top_level_keys(): void
    {
        $executionPath = $this->duplicateTopLevelKeyFixture(self::C160_EXECUTION_ARTIFACT, 'execution-duplicate', 'Run_Code');
        $planConfirmPath = $this->duplicateTopLevelKeyFixture(self::CONTROLLED_PLAN_CONFIRM, 'plan-confirm-duplicate', 'Controlled_Plan_Confirm_Hash');

        $executionResult = $this->runService([
            'c160ExecutionArtifact' => $executionPath,
            'expectedC160ExecutionFileSha1' => strtoupper(sha1((string) file_get_contents($executionPath))),
        ]);
        $planConfirmResult = $this->runService([
            'controlledPlanConfirm' => $planConfirmPath,
            'expectedControlledPlanConfirmFileSha1' => strtoupper(sha1((string) file_get_contents($planConfirmPath))),
        ]);

        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW_REJECTED_C160_EXECUTION_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION', $executionResult['status']);
        $this->assertFalse($executionResult['c160_execution_convert_from_json_pass']);
        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW_REJECTED_CONTROLLED_PLAN_CONFIRM_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION', $planConfirmResult['status']);
        $this->assertFalse($planConfirmResult['controlled_plan_confirm_convert_from_json_pass']);
    }

    /**
     * @dataProvider executionIncompleteProvider
     */
    public function test_c160_result_review_rejects_incomplete_execution_evidence(string $field, $value): void
    {
        $result = $this->mutateExecutionAndExecute(function (array $execution) use ($field, $value): array {
            $this->setValueAt($execution, explode('.', $field), $value);
            return $execution;
        }, 'execution-incomplete-'.str_replace('.', '-', $field));

        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW_REJECTED_C160_EXECUTION_INCOMPLETE', $result['status'], $field);
    }

    public function executionIncompleteProvider(): array
    {
        return [
            ['weekly_swing_watchlist_production_live_runtime_plan_confirm_execution_pass', false],
            ['ready_for_weekly_swing_watchlist_plan_confirm_result_review', false],
            ['weekly_swing_watchlist_plan_confirm_controlled_execution_executed', false],
            ['weekly_swing_watchlist_plan_confirm_controlled_artifact_created', false],
            ['controlled_publication_lock_valid', false],
            ['primary_candidate_plan_confirm_controlled_executed', false],
            ['backup_candidate_plan_confirm_controlled_executed', false],
            ['c160_controlled_plan_confirm_only', false],
            ['topic_stage', 'RESULT_REVIEW'],
        ];
    }

    public function test_c160_result_review_rejects_controlled_plan_confirm_integrity_mismatch(): void
    {
        $state = $this->mutateControlledPlanConfirmAndExecute(function (array $planConfirm): array {
            $planConfirm['plan_confirm_state'] = 'not_executed';
            return $planConfirm;
        }, 'state');
        $baseline = $this->mutateControlledPlanConfirmAndExecute(function (array $planConfirm): array {
            $planConfirm['baseline_plan_confirm_state'] = 'changed';
            return $planConfirm;
        }, 'baseline');

        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW_REJECTED_CONTROLLED_PLAN_CONFIRM_INTEGRITY_MISMATCH', $state['status']);
        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW_REJECTED_CONTROLLED_PLAN_CONFIRM_INTEGRITY_MISMATCH', $baseline['status']);
    }

    public function test_c160_result_review_rejects_publication_plan_mutation_or_candidate_scope_change(): void
    {
        $published = $this->mutateControlledPlanConfirmAndExecute(function (array $planConfirm): array {
            $planConfirm['weekly_swing_watchlist_official_output_published'] = true;
            return $planConfirm;
        }, 'published');
        $planMutation = $this->mutateControlledPlanConfirmAndExecute(function (array $planConfirm): array {
            $planConfirm['plan_confirm_mutated'] = true;
            return $planConfirm;
        }, 'plan-mutation');
        $candidate = $this->mutateControlledPlanConfirmAndExecute(function (array $planConfirm): array {
            $planConfirm['output_rows'][0]['candidate_code'] = 'BROKEN_PRIMARY';
            return $planConfirm;
        }, 'candidate');

        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED', $published['status']);
        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED', $planMutation['status']);
        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $candidate['status']);
    }

    public function test_c160_result_review_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c160-plan-confirm-result-review-negative-remains-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS', $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c160_result_review_records_scorecard_and_safety_sections(): void
    {
        $result = $this->runService();
        $scorecard = $result['candidate_plan_confirm_result_scorecard'];
        $safety = $result['publication_plan_confirm_safety_summary'];

        $this->assertTrue($scorecard[0]['plan_confirm_result_reviewed']);
        $this->assertTrue($scorecard[1]['plan_confirm_result_reviewed']);
        $this->assertFalse($scorecard[2]['plan_confirm_result_reviewed']);
        $this->assertFalse($safety['weekly_swing_watchlist_official_output_published']);
        $this->assertFalse($safety['plan_confirm_mutated']);
        $this->assertFalse($safety['plan_confirm_runtime_reads_activated_catalog']);
        $this->assertFalse($safety['live_plan_confirm_rollout_executed']);
    }

    public function test_c160_result_review_output_is_deterministic_enough_for_hash_validation(): void
    {
        $first = $this->runService(['createdAt' => '2026-07-16T00:00:00+00:00']);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c160-plan-confirm-result-review-second.json';
        $this->tmpFiles[] = $secondOutput;
        $second = $this->runService([
            'output' => $secondOutput,
            'createdAt' => '2026-07-16T00:00:00+00:00',
        ]);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
    }

    public function test_c160_result_review_does_not_mutate_source_artifacts_or_config_defaults(): void
    {
        $beforeExecution = strtoupper(sha1((string) file_get_contents(self::C160_EXECUTION_ARTIFACT)));
        $beforePlanConfirm = strtoupper(sha1((string) file_get_contents(self::CONTROLLED_PLAN_CONFIRM)));
        $beforeConfig = (string) file_get_contents('config/watchlist.php');

        $this->runService();

        $this->assertSame($beforeExecution, strtoupper(sha1((string) file_get_contents(self::C160_EXECUTION_ARTIFACT))));
        $this->assertSame($beforePlanConfirm, strtoupper(sha1((string) file_get_contents(self::CONTROLLED_PLAN_CONFIRM))));
        $this->assertSame($beforeConfig, (string) file_get_contents('config/watchlist.php'));
    }

    private function runService(array $options = []): array
    {
        $service = new WatchlistBacktestC160WeeklySwingWatchlistProductionLiveRuntimePlanConfirmResultReviewService();

        return $service->execute(
            (string) ($options['c160ExecutionArtifact'] ?? self::C160_EXECUTION_ARTIFACT),
            (string) ($options['expectedC160ExecutionHash'] ?? self::C160_EXECUTION_HASH),
            (string) ($options['expectedC160ExecutionFileSha1'] ?? self::C160_EXECUTION_SHA1),
            (string) ($options['controlledPlanConfirm'] ?? self::CONTROLLED_PLAN_CONFIRM),
            (string) ($options['expectedControlledPlanConfirmHash'] ?? self::CONTROLLED_PLAN_CONFIRM_HASH),
            (string) ($options['expectedControlledPlanConfirmFileSha1'] ?? self::CONTROLLED_PLAN_CONFIRM_SHA1),
            (string) ($options['output'] ?? $this->output),
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'result_review_confirmed' => (bool) ($options['resultReviewConfirmed'] ?? true),
                'controlled_plan_confirm_result_confirmed' => (bool) ($options['controlledPlanConfirmResultConfirmed'] ?? true),
                'controlled_plan_confirm_only_confirmed' => (bool) ($options['controlledPlanConfirmOnlyConfirmed'] ?? true),
                'plan_confirm_unchanged_confirmed' => (bool) ($options['planConfirmUnchangedConfirmed'] ?? true),
                'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['noLivePlanConfirmRolloutConfirmed'] ?? true),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C160_OPERATOR_APPROVED_PLAN_CONFIRM_RESULT_REVIEW_CONTROLLED_EVIDENCE_ONLY'),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-16T00:00:00+00:00'),
            ]
        );
    }

    private function mutateExecutionAndExecute(callable $mutator, string $name): array
    {
        $execution = json_decode((string) file_get_contents(self::C160_EXECUTION_ARTIFACT), true);
        $execution = $mutator(is_array($execution) ? $execution : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c160-plan-confirm-result-review-execution-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($execution, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'c160ExecutionArtifact' => $path,
            'expectedC160ExecutionHash' => (string) ($execution['artifact_hash'] ?? ''),
            'expectedC160ExecutionFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function mutateControlledPlanConfirmAndExecute(callable $mutator, string $name): array
    {
        $planConfirm = json_decode((string) file_get_contents(self::CONTROLLED_PLAN_CONFIRM), true);
        $planConfirm = $mutator(is_array($planConfirm) ? $planConfirm : []);
        $path = 'storage/app/watchlist/output/.tmp-c160-plan-confirm-result-review-controlled-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($planConfirm, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'controlledPlanConfirm' => $path,
            'expectedControlledPlanConfirmHash' => (string) ($planConfirm['controlled_plan_confirm_hash'] ?? ''),
            'expectedControlledPlanConfirmFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function duplicateTopLevelKeyFixture(string $source, string $name, string $duplicateKey): string
    {
        $raw = (string) file_get_contents($source);
        $path = 'storage/app/watchlist/backtest/.tmp-c160-plan-confirm-result-review-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, preg_replace('/\{/', '{"'.$duplicateKey.'":"DUPLICATE_CASE_KEY",', $raw, 1));

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

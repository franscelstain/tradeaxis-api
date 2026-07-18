<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC165WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutBoundaryReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC165WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutBoundaryReviewTest extends TestCase
{
    private const C164_ARTIFACT = 'storage/app/watchlist/backtest/c164-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-completion-go-decision-finalization-review.json';
    private const C164_HASH = '63c7512cb6d395bc6268dae385a10ae703e4aa3d';
    private const C164_SHA1 = '9CA9F2F36F15F17C15301E9F119C303088EDD163';
    private const PASS_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW_PASSED_READY_FOR_CONTROLLED_ROLLOUT_EXECUTION_PRIMARY_AND_BACKUP';
    private const NEXT_EXECUTION = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION';

    private string $output;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c165-plan-confirm-controlled-rollout-boundary-review.json';
        $this->cleanupTemporaryArtifacts();
    }

    protected function tearDown(): void
    {
        $this->cleanupTemporaryArtifacts();
        parent::tearDown();
    }

    public function test_c165_boundary_passes_and_opens_same_topic_controlled_rollout_execution(): void
    {
        $result = $this->runService();

        $this->assertSame('C165_PLAN_CONFIRM_CONTROLLED_ROLLOUT', $result['topic_code']);
        $this->assertSame('PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW', $result['topic_stage']);
        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame(self::PASS_STATUS, $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_boundary_review_pass']);
        $this->assertTrue($result['controlled_rollout_boundary_open']);
        $this->assertTrue($result['c164_finalization_lock_valid']);
        $this->assertTrue($result['c164_finalization_state_valid']);
        $this->assertTrue($result['ready_for_weekly_swing_watchlist_plan_confirm_controlled_rollout_execution']);
        $this->assertTrue($result['controlled_plan_confirm_rollout_execution_allowed_next']);
        $this->assertSame(self::NEXT_EXECUTION, $result['next_step_recommendation']);
        $this->assertTrue($result['next_plan_confirm_controlled_rollout_execution_decision']['same_topic_c165_continues']);
        $this->assertFileExists($this->output);
    }

    public function test_c165_boundary_rejects_missing_operator_approval_or_reference(): void
    {
        $withoutApproval = $this->runService(['operatorApproved' => false]);
        $withoutReference = $this->runService(['approvalReference' => '']);

        $expected = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
        $this->assertSame($expected, $withoutApproval['status']);
        $this->assertSame($expected, $withoutReference['status']);
    }

    /**
     * @dataProvider confirmationProvider
     */
    public function test_c165_boundary_rejects_missing_required_confirmation(string $option, string $expectedStatus): void
    {
        $result = $this->runService([$option => false]);

        $this->assertSame($expectedStatus, $result['status']);
    }

    public function confirmationProvider(): array
    {
        $prefix = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW_REJECTED_';

        return [
            ['controlledRolloutBoundaryConfirmed', $prefix.'CONTROLLED_ROLLOUT_BOUNDARY_CONFIRMATION_MISSING'],
            ['c164FinalizationLockedConfirmed', $prefix.'C164_FINALIZATION_LOCK_CONFIRMATION_MISSING'],
            ['controlledRolloutOnlyConfirmed', $prefix.'CONTROLLED_ROLLOUT_ONLY_CONFIRMATION_MISSING'],
            ['planConfirmUnchangedConfirmed', $prefix.'PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING'],
            ['noRolloutExecutedConfirmed', $prefix.'NO_ROLLOUT_EXECUTED_CONFIRMATION_MISSING'],
            ['freePublicationLockedConfirmed', $prefix.'FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING'],
        ];
    }

    public function test_c165_boundary_rejects_missing_or_mismatched_c164_lock(): void
    {
        $missing = $this->runService([
            'c164Artifact' => 'storage/app/watchlist/backtest/.tmp-c165-c164-source-missing.json',
            'expectedC164Hash' => 'missing',
            'expectedC164Sha1' => 'missing',
        ]);
        $hashMismatch = $this->runService(['expectedC164Hash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedC164Sha1' => 'BADSHA1']);

        $this->assertSame('C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW_REJECTED_C164_FINALIZATION_ARTIFACT_LOCK_MISMATCH', $missing['status']);
        $this->assertSame('C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW_REJECTED_C164_FINALIZATION_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW_REJECTED_C164_FINALIZATION_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
    }

    public function test_c165_boundary_rejects_c164_status_phase_or_next_recommendation_mismatch(): void
    {
        $status = $this->mutateC164AndExecute(function (array $c164): array {
            $c164['status'] = 'BROKEN_STATUS';
            return $c164;
        }, 'status');
        $phase = $this->mutateC164AndExecute(function (array $c164): array {
            $c164['phase_label'] = 'BROKEN_PHASE';
            return $c164;
        }, 'phase');
        $next = $this->mutateC164AndExecute(function (array $c164): array {
            $c164['next_step_recommendation'] = 'BROKEN_NEXT';
            $c164['next_plan_confirm_controlled_rollout_boundary_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c164['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $c164;
        }, 'next');

        $prefix = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW_REJECTED_';
        $this->assertSame($prefix.'C164_FINALIZATION_STATUS_MISMATCH', $status['status']);
        $this->assertSame($prefix.'C164_FINALIZATION_PHASE_LABEL_MISMATCH', $phase['status']);
        $this->assertSame($prefix.'C164_FINALIZATION_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
    }

    public function test_c165_boundary_rejects_c164_duplicate_case_insensitive_top_level_key(): void
    {
        $raw = (string) file_get_contents(self::C164_ARTIFACT);
        $path = 'storage/app/watchlist/backtest/.tmp-c165-c164-source-duplicate-key.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, preg_replace('/\{/', "{\"Run_Code\":\"DUPLICATE_CASE_KEY\",", $raw, 1));

        $result = $this->runService([
            'c164Artifact' => $path,
            'expectedC164Sha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);

        $this->assertSame('C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW_REJECTED_C164_FINALIZATION_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION', $result['status']);
        $this->assertFalse($result['c164_finalization_convert_from_json_pass']);
        $this->assertContains('run_code', array_map('strtolower', $result['c164_finalization_convert_from_json_duplicate_keys']));
    }

    /**
     * @dataProvider c164StateProvider
     */
    public function test_c165_boundary_rejects_incomplete_c164_state(string $field, $value): void
    {
        $result = $this->mutateC164AndExecute(function (array $c164) use ($field, $value): array {
            $this->setValueAt($c164, explode('.', $field), $value);
            return $c164;
        }, 'state-'.str_replace('.', '-', $field));

        $this->assertSame('C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW_REJECTED_C164_FINALIZATION_STATE_INVALID', $result['status'], $field);
    }

    public function c164StateProvider(): array
    {
        return [
            ['c164_topic_complete_after_finalization', false],
            ['post_handoff_activation_completion_closed', false],
            ['go_decision_finalized', false],
            ['controlled_completion_record_count', 0],
            ['ready_for_weekly_swing_watchlist_plan_confirm_controlled_rollout_boundary_review', false],
            ['primary_candidate_ready_for_plan_confirm_controlled_rollout_boundary_review', false],
            ['c164_go_decision_finalization_decision.review_valid', false],
            ['next_plan_confirm_controlled_rollout_boundary_decision.same_topic_c164_complete', false],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_manifest.go_decision_finalization_artifact_only', false],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_checklist.artifact_only', false],
        ];
    }

    /**
     * @dataProvider unsafeStateProvider
     */
    public function test_c165_boundary_rejects_publication_plan_mutation_catalog_read_or_rollout(string $field): void
    {
        $result = $this->mutateC164AndExecute(function (array $c164) use ($field): array {
            $this->setValueAt($c164, explode('.', $field), true);
            return $c164;
        }, 'unsafe-'.str_replace('.', '-', $field));

        $this->assertSame('C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW_REJECTED_PUBLICATION_PLAN_CONFIRM_OR_ROLLOUT_ALREADY_OCCURRED', $result['status'], $field);
    }

    public function unsafeStateProvider(): array
    {
        return [
            ['weekly_swing_watchlist_official_output_published'],
            ['weekly_swing_watchlist_publication_allowed'],
            ['plan_confirm_mutated'],
            ['plan_confirm_runtime_reads_activated_catalog'],
            ['live_plan_confirm_rollout_allowed'],
            ['live_plan_confirm_rollout_executed'],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_manifest.go_decision_finalization_used_for_free_publication'],
        ];
    }

    public function test_c165_boundary_rejects_candidate_or_watchlist_function_scope_change(): void
    {
        $candidate = $this->mutateC164AndExecute(function (array $c164): array {
            $c164['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $c164;
        }, 'candidate');
        $function = $this->mutateC164AndExecute(function (array $c164): array {
            $c164['watchlist_function_used'] = 'BROKEN_FUNCTION';
            return $c164;
        }, 'function');

        $prefix = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW_REJECTED_';
        $this->assertSame($prefix.'CANDIDATE_SCOPE_MISMATCH', $candidate['status']);
        $this->assertSame($prefix.'WATCHLIST_FUNCTION_SCOPE_MISMATCH', $function['status']);
    }

    public function test_c165_boundary_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c165-controlled-rollout-boundary-negative-remains-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame('C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS', $result['status']);
        $this->assertTrue($result['temporary_negative_artifact_guard_summary']['temporary_negative_artifacts_remaining']);
        $this->assertContains($path, $result['temporary_negative_artifact_guard_summary']['temporary_negative_artifact_paths']);
    }

    public function test_c165_boundary_records_manifest_checklist_candidate_scope_function_and_safety(): void
    {
        $result = $this->runService();
        $run = json_decode((string) file_get_contents($this->output), true);
        $manifest = $result['weekly_swing_watchlist_plan_confirm_controlled_rollout_boundary_manifest'];
        $checklist = $result['weekly_swing_watchlist_plan_confirm_controlled_rollout_boundary_checklist'];

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $this->assertSame(self::C164_HASH, $result['actual_c164_finalization_hash']);
        $this->assertSame(self::C164_SHA1, $result['actual_c164_finalization_file_sha1']);
        $this->assertTrue($result['c164_finalization_hash_match']);
        $this->assertTrue($result['c164_finalization_file_sha1_match']);
        $this->assertTrue($manifest['controlled_rollout_boundary_artifact_only']);
        $this->assertTrue($manifest['ready_for_controlled_rollout_execution']);
        $this->assertFalse($manifest['boundary_used_for_rollout_execution']);
        $this->assertFalse($manifest['boundary_used_for_plan_confirm_mutation']);
        $this->assertSame([], $manifest['official_weekly_swing_stock_recommendations']);
        $this->assertTrue($checklist['artifact_only']);
        $this->assertTrue($checklist['controlled_rollout_execution_required_next']);
        $this->assertSame('CONTROLLED_WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATION', $result['watchlist_function_used']);
        $this->assertSame('PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY', $result['watchlist_function_runtime_mode']);
        $this->assertTrue($result['primary_candidate_ready_for_plan_confirm_controlled_rollout_execution']);
        $this->assertTrue($result['backup_candidate_ready_for_plan_confirm_controlled_rollout_execution']);
        $this->assertFalse($result['comparator_candidate_ready_for_plan_confirm_controlled_rollout_execution']);

        foreach ([
            'source_artifact_locks',
            'c164_finalization_lock_validation_summary',
            'c164_finalization_carry_forward_summary',
            'plan_confirm_controlled_rollout_boundary_guard_summary',
            'watchlist_function_scope_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'c165_plan_confirm_controlled_rollout_boundary_decision',
            'next_plan_confirm_controlled_rollout_execution_decision',
            'weekly_swing_watchlist_plan_confirm_controlled_rollout_boundary_manifest',
            'weekly_swing_watchlist_plan_confirm_controlled_rollout_boundary_checklist',
            'c165_candidate_plan_confirm_controlled_rollout_boundary_scorecard',
            'publication_plan_confirm_rollout_safety_summary',
            'documentation_hygiene_guard_summary',
            'progress_summary',
            'planned_next_summary',
            'diagnostics',
            'failure_attribution_summary',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }

        foreach ([
            'weekly_swing_watchlist_official_output_published',
            'weekly_swing_watchlist_publication_allowed',
            'weekly_swing_watchlist_unrestricted_publication_allowed',
            'plan_confirm_mutation_allowed',
            'plan_confirm_mutated',
            'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed',
            'live_plan_confirm_rollout_executed',
        ] as $field) {
            $this->assertFalse($result[$field], $field);
            $this->assertFalse($result['publication_plan_confirm_rollout_safety_summary'][$field], $field);
        }
    }

    public function test_c165_boundary_is_deterministic_and_does_not_mutate_source_or_config(): void
    {
        $beforeSource = strtoupper(sha1((string) file_get_contents(self::C164_ARTIFACT)));
        $beforeConfig = (string) file_get_contents('config/watchlist.php');
        $first = $this->runService(['createdAt' => '2026-07-18T00:00:00+00:00']);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c165-plan-confirm-controlled-rollout-boundary-review-second.json';
        $this->tmpFiles[] = $secondOutput;
        $second = $this->runService(['output' => $secondOutput, 'createdAt' => '2026-07-18T00:00:00+00:00']);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
        $this->assertSame($beforeSource, strtoupper(sha1((string) file_get_contents(self::C164_ARTIFACT))));
        $this->assertSame($beforeConfig, (string) file_get_contents('config/watchlist.php'));
    }

    private function runService(array $options = []): array
    {
        $service = new WatchlistBacktestC165WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutBoundaryReviewService();

        return $service->execute(
            (string) ($options['c164Artifact'] ?? self::C164_ARTIFACT),
            (string) ($options['expectedC164Hash'] ?? self::C164_HASH),
            (string) ($options['expectedC164Sha1'] ?? self::C164_SHA1),
            (string) ($options['output'] ?? $this->output),
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'controlled_rollout_boundary_confirmed' => (bool) ($options['controlledRolloutBoundaryConfirmed'] ?? true),
                'c164_finalization_locked_confirmed' => (bool) ($options['c164FinalizationLockedConfirmed'] ?? true),
                'controlled_rollout_only_confirmed' => (bool) ($options['controlledRolloutOnlyConfirmed'] ?? true),
                'plan_confirm_unchanged_confirmed' => (bool) ($options['planConfirmUnchangedConfirmed'] ?? true),
                'no_rollout_executed_confirmed' => (bool) ($options['noRolloutExecutedConfirmed'] ?? true),
                'free_publication_locked_confirmed' => (bool) ($options['freePublicationLockedConfirmed'] ?? true),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C165_OPERATOR_APPROVED_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW'),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-18T00:00:00+00:00'),
            ]
        );
    }

    private function mutateC164AndExecute(callable $mutator, string $name): array
    {
        $c164 = json_decode((string) file_get_contents(self::C164_ARTIFACT), true);
        $c164 = $mutator(is_array($c164) ? $c164 : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c165-c164-source-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($c164, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'c164Artifact' => $path,
            'expectedC164Hash' => (string) ($c164['artifact_hash'] ?? ''),
            'expectedC164Sha1' => strtoupper(sha1((string) file_get_contents($path))),
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

    private function cleanupTemporaryArtifacts(): void
    {
        foreach (array_merge($this->tmpFiles, (array) glob('storage/app/watchlist/backtest/.tmp-c165*.json'), (array) glob('storage/app/watchlist/backtest/c165-*controlled-rollout-boundary*-test.json')) as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
        $this->tmpFiles = [];
    }
}

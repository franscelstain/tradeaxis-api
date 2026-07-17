<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC160WeeklySwingWatchlistProductionLiveRuntimePlanConfirmBoundaryReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC160WeeklySwingWatchlistProductionLiveRuntimePlanConfirmBoundaryReviewTest extends TestCase
{
    private const C159_FINALIZATION_ARTIFACT = 'storage/app/watchlist/backtest/c159-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-post-publication-observation-go-decision-finalization-review.json';
    private const C159_FINALIZATION_HASH = '1c497836fc6932909c06e62e324f806b07676ab1';
    private const C159_FINALIZATION_SHA1 = '97D00F48AA0D68853BAA46C36DCC571CFF3CB01F';
    private const PASS_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_BOUNDARY_REVIEW_PASSED_READY_FOR_PLAN_CONFIRM_EXECUTION_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_BOUNDARY_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const PLAN_CONFIRM_BOUNDARY_CONFIRMATION_MISSING_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_BOUNDARY_REVIEW_REJECTED_PLAN_CONFIRM_BOUNDARY_CONFIRMATION_MISSING';
    private const CONTROLLED_PLAN_CONFIRM_ONLY_CONFIRMATION_MISSING_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_BOUNDARY_REVIEW_REJECTED_CONTROLLED_PLAN_CONFIRM_ONLY_CONFIRMATION_MISSING';
    private const PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_BOUNDARY_REVIEW_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING';
    private const TEMP_NEGATIVE_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_BOUNDARY_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const NEXT_C160_EXECUTION = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION';

    private string $output;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c160-plan-confirm-boundary-review.json';
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

    public function test_c160_records_boundary_review_and_defers_actual_plan_confirm_execution(): void
    {
        $result = $this->runService();

        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_BOUNDARY_REVIEW', $result['run_code']);
        $this->assertSame('PR-55 / C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_BOUNDARY_REVIEW', $result['phase_label']);
        $this->assertSame('C160_PLAN_CONFIRM', $result['topic_code']);
        $this->assertSame('PLAN_CONFIRM_BOUNDARY_REVIEW', $result['topic_stage']);
        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame(self::PASS_STATUS, $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_production_live_runtime_plan_confirm_boundary_review_pass']);
        $this->assertTrue($result['production_live_runtime_plan_confirm_boundary_review_pass']);
        $this->assertTrue($result['ready_for_weekly_swing_watchlist_plan_confirm_execution']);
        $this->assertTrue($result['production_live_runtime_plan_confirm_execution_allowed_next']);
        $this->assertTrue($result['weekly_swing_watchlist_plan_confirm_execution_allowed_next']);
        $this->assertTrue($result['plan_confirm_execution_allowed_next']);
        $this->assertFalse($result['plan_confirm_mutation_allowed']);
        $this->assertFalse($result['plan_confirm_mutated']);
        $this->assertFalse($result['plan_confirm_runtime_reads_activated_catalog']);
        $this->assertFalse($result['live_plan_confirm_rollout_allowed']);
        $this->assertFalse($result['live_plan_confirm_rollout_executed']);
        $this->assertFalse($result['weekly_swing_watchlist_official_output_published']);
        $this->assertTrue($result['c159_finalization_lock_valid']);
        $this->assertTrue($result['c159_go_decision_finalization_valid']);
        $this->assertTrue($result['c160_topic_number_retained_for_execution']);
        $this->assertSame(self::NEXT_C160_EXECUTION, $result['next_step_recommendation']);
        $this->assertFileExists($this->output);
    }

    public function test_c160_records_c159_lock_sections_and_boundary_manifest(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();
        $manifest = $result['plan_confirm_boundary_manifest'];
        $checklist = $result['plan_confirm_boundary_checklist'];

        foreach ([
            'source_artifact_locks',
            'c159_finalization_lock_validation_summary',
            'c159_go_decision_finalization_carry_forward_summary',
            'plan_confirm_boundary_decision',
            'plan_confirm_boundary_manifest',
            'plan_confirm_boundary_checklist',
            'publication_plan_confirm_safety_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'c160_candidate_plan_confirm_boundary_scorecard',
            'plan_confirm_boundary_context_summary',
            'documentation_hygiene_guard_summary',
            'progress_summary',
            'planned_next_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }

        $this->assertSame(self::C159_FINALIZATION_HASH, $result['expected_c159_finalization_hash']);
        $this->assertSame(self::C159_FINALIZATION_HASH, $result['actual_c159_finalization_hash']);
        $this->assertTrue($result['c159_finalization_hash_match']);
        $this->assertSame(self::C159_FINALIZATION_SHA1, $result['expected_c159_finalization_file_sha1']);
        $this->assertSame(self::C159_FINALIZATION_SHA1, $result['actual_c159_finalization_file_sha1']);
        $this->assertTrue($result['c159_finalization_file_sha1_match']);
        $this->assertTrue($result['c159_finalization_convert_from_json_pass']);
        $this->assertSame(self::NEXT_C160_EXECUTION, $run['planned_next_summary']['planned_next_review']);
        $this->assertTrue($manifest['boundary_review_only']);
        $this->assertTrue($manifest['ready_for_plan_confirm_execution']);
        $this->assertFalse($manifest['plan_confirm_executed_in_c160_boundary']);
        $this->assertFalse($manifest['plan_confirm_mutated_in_c160_boundary']);
        $this->assertFalse($manifest['live_plan_confirm_rollout_executed']);
        $this->assertTrue($checklist['artifact_only']);
        $this->assertTrue($checklist['plan_confirm_mutation_forbidden_in_boundary']);
    }

    public function test_c160_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->runService(['operatorApproved' => false]);
        $missingReference = $this->runService(['approvalReference' => '']);

        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingOperator['status']);
        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingReference['status']);
    }

    public function test_c160_rejects_missing_required_boundary_confirmations(): void
    {
        $boundary = $this->runService(['planConfirmBoundaryConfirmed' => false]);
        $controlledOnly = $this->runService(['controlledPlanConfirmOnlyConfirmed' => false]);
        $unchanged = $this->runService(['planConfirmUnchangedConfirmed' => false]);

        $this->assertSame(self::PLAN_CONFIRM_BOUNDARY_CONFIRMATION_MISSING_STATUS, $boundary['status']);
        $this->assertSame(self::CONTROLLED_PLAN_CONFIRM_ONLY_CONFIRMATION_MISSING_STATUS, $controlledOnly['status']);
        $this->assertSame(self::PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING_STATUS, $unchanged['status']);
    }

    public function test_c160_rejects_missing_or_mismatched_c159_finalization_artifact_lock(): void
    {
        $missing = $this->runService([
            'c159FinalizationArtifact' => 'storage/app/watchlist/backtest/.tmp-c160-source-c159-missing.json',
            'expectedC159FinalizationHash' => 'missing',
            'expectedC159FinalizationFileSha1' => 'missing',
        ]);
        $hashMismatch = $this->runService(['expectedC159FinalizationHash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedC159FinalizationFileSha1' => 'BADSHA1']);

        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_BOUNDARY_REVIEW_REJECTED_C159_FINALIZATION_ARTIFACT_LOCK_MISMATCH', $missing['status']);
        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_BOUNDARY_REVIEW_REJECTED_C159_FINALIZATION_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_BOUNDARY_REVIEW_REJECTED_C159_FINALIZATION_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
    }

    public function test_c160_rejects_c159_status_phase_or_next_recommendation_mismatch(): void
    {
        $status = $this->mutateC159AndExecute(function (array $c159): array {
            $c159['status'] = 'BROKEN_STATUS';
            return $c159;
        }, 'status-broken');
        $phase = $this->mutateC159AndExecute(function (array $c159): array {
            $c159['phase_label'] = 'BROKEN_PHASE';
            return $c159;
        }, 'phase-broken');
        $next = $this->mutateC159AndExecute(function (array $c159): array {
            $c159['next_step_recommendation'] = 'BROKEN_NEXT';
            $c159['next_plan_confirm_boundary_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c159['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $c159;
        }, 'next-broken');

        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_BOUNDARY_REVIEW_REJECTED_C159_FINALIZATION_STATUS_MISMATCH', $status['status']);
        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_BOUNDARY_REVIEW_REJECTED_C159_FINALIZATION_PHASE_LABEL_MISMATCH', $phase['status']);
        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_BOUNDARY_REVIEW_REJECTED_C159_FINALIZATION_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
    }

    public function test_c160_rejects_c159_convert_from_json_duplicate_top_level_keys(): void
    {
        $raw = (string) file_get_contents(self::C159_FINALIZATION_ARTIFACT);
        $path = 'storage/app/watchlist/backtest/.tmp-c160-source-c159-duplicate-key.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, preg_replace('/\{/', "{\"Run_Code\":\"DUPLICATE_CASE_KEY\",", $raw, 1));

        $result = $this->runService([
            'c159FinalizationArtifact' => $path,
            'expectedC159FinalizationHash' => self::C159_FINALIZATION_HASH,
            'expectedC159FinalizationFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);

        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_BOUNDARY_REVIEW_REJECTED_C159_FINALIZATION_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION', $result['status']);
        $this->assertFalse($result['c159_finalization_convert_from_json_pass']);
    }

    /**
     * @dataProvider c159FinalizationMismatchProvider
     */
    public function test_c160_rejects_incomplete_c159_finalization_evidence(string $field, $value): void
    {
        $result = $this->mutateC159AndExecute(function (array $c159) use ($field, $value): array {
            $this->setValueAt($c159, explode('.', $field), $value);
            return $c159;
        }, 'finalization-'.str_replace('.', '-', $field));

        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_BOUNDARY_REVIEW_REJECTED_C159_FINALIZATION_INCOMPLETE', $result['status'], $field);
    }

    public function c159FinalizationMismatchProvider(): array
    {
        return [
            ['weekly_swing_watchlist_production_live_runtime_controlled_output_publication_post_publication_observation_go_decision_finalization_review_pass', false],
            ['operator_go_decision', 'NO_GO'],
            ['go_decision_finalized', false],
            ['post_publication_observation_closed', false],
            ['ready_for_weekly_swing_watchlist_plan_confirm_boundary_review', false],
            ['production_live_runtime_plan_confirm_boundary_review_allowed_next', false],
            ['controlled_output_publication_post_publication_observation_go_decision_finalization_manifest_created', false],
            ['c159_operator_go_no_go_lock_valid', false],
            ['controlled_publication_lock_valid', false],
            ['primary_candidate_ready_for_plan_confirm_boundary_review', false],
            ['backup_candidate_ready_for_plan_confirm_boundary_review', false],
            ['comparator_candidate_ready_for_plan_confirm_boundary_review', true],
            ['c159_go_decision_finalization_decision.review_valid', false],
            ['c159_go_decision_finalization_decision.operator_decision', 'NO_GO'],
            ['next_plan_confirm_boundary_decision.same_topic_c159_complete', false],
            ['weekly_swing_watchlist_controlled_output_publication_post_publication_observation_go_decision_finalization_manifest.go_decision_finalization_artifact_only', false],
            ['weekly_swing_watchlist_controlled_output_publication_post_publication_observation_go_decision_finalization_checklist.artifact_only', false],
        ];
    }

    /**
     * @dataProvider publicationPlanMutationProvider
     */
    public function test_c160_rejects_publication_plan_confirm_mutation_or_live_rollout_from_c159(string $field): void
    {
        $result = $this->mutateC159AndExecute(function (array $c159) use ($field): array {
            $c159[$field] = true;
            return $c159;
        }, 'guard-'.$field);

        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_BOUNDARY_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED', $result['status'], $field);
    }

    public function publicationPlanMutationProvider(): array
    {
        return [
            ['weekly_swing_watchlist_official_output_published'],
            ['weekly_swing_watchlist_publication_allowed'],
            ['weekly_swing_watchlist_unrestricted_publication_allowed'],
            ['plan_confirm_mutation_allowed'],
            ['plan_confirm_mutated'],
            ['plan_confirm_runtime_reads_activated_catalog'],
            ['live_plan_confirm_rollout_allowed'],
            ['live_plan_confirm_rollout_executed'],
        ];
    }

    public function test_c160_rejects_candidate_scope_change_or_a01_promotion(): void
    {
        $candidate = $this->mutateC159AndExecute(function (array $c159): array {
            $c159['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $c159;
        }, 'candidate-primary');
        $a01 = $this->mutateC159AndExecute(function (array $c159): array {
            $c159['a01_promoted'] = true;
            return $c159;
        }, 'candidate-a01');

        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_BOUNDARY_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $candidate['status']);
        $this->assertSame('C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_BOUNDARY_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $a01['status']);
    }

    public function test_c160_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c160-plan-confirm-boundary-negative-remains-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame(self::TEMP_NEGATIVE_STATUS, $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c160_keeps_e02_primary_b01_backup_a01_comparator_and_no_plan_confirm_mutation(): void
    {
        $result = $this->runService();
        $scorecard = $result['c160_candidate_plan_confirm_boundary_scorecard'];

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $result['primary_candidate_code']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $result['backup_candidate_code']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $result['comparator_candidate_code']);
        $this->assertTrue($result['primary_candidate_ready_for_plan_confirm_execution']);
        $this->assertTrue($result['backup_candidate_ready_for_plan_confirm_execution']);
        $this->assertFalse($result['comparator_candidate_ready_for_plan_confirm_execution']);
        $this->assertTrue($result['a01_remains_comparator_only']);
        $this->assertTrue($scorecard[0]['ready_for_plan_confirm_execution']);
        $this->assertTrue($scorecard[1]['ready_for_plan_confirm_execution']);
        $this->assertFalse($scorecard[2]['ready_for_plan_confirm_execution']);

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

    public function test_c160_boundary_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $first = $this->runService(['createdAt' => '2026-07-16T00:00:00+00:00']);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c160-plan-confirm-boundary-review-second.json';
        $this->tmpFiles[] = $secondOutput;
        $second = $this->runService([
            'output' => $secondOutput,
            'createdAt' => '2026-07-16T00:00:00+00:00',
        ]);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
    }

    public function test_c160_boundary_does_not_mutate_c159_finalization_or_config_defaults(): void
    {
        $beforeC159 = strtoupper(sha1((string) file_get_contents(self::C159_FINALIZATION_ARTIFACT)));
        $beforeConfig = (string) file_get_contents('config/watchlist.php');

        $this->runService();

        $this->assertSame($beforeC159, strtoupper(sha1((string) file_get_contents(self::C159_FINALIZATION_ARTIFACT))));
        $this->assertSame($beforeConfig, (string) file_get_contents('config/watchlist.php'));
    }

    private function runService(array $options = []): array
    {
        $service = new WatchlistBacktestC160WeeklySwingWatchlistProductionLiveRuntimePlanConfirmBoundaryReviewService();

        return $service->execute(
            (string) ($options['c159FinalizationArtifact'] ?? self::C159_FINALIZATION_ARTIFACT),
            (string) ($options['expectedC159FinalizationHash'] ?? self::C159_FINALIZATION_HASH),
            (string) ($options['expectedC159FinalizationFileSha1'] ?? self::C159_FINALIZATION_SHA1),
            (string) ($options['output'] ?? $this->output),
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'plan_confirm_boundary_confirmed' => (bool) ($options['planConfirmBoundaryConfirmed'] ?? true),
                'controlled_plan_confirm_only_confirmed' => (bool) ($options['controlledPlanConfirmOnlyConfirmed'] ?? true),
                'plan_confirm_unchanged_confirmed' => (bool) ($options['planConfirmUnchangedConfirmed'] ?? true),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C160_OPERATOR_APPROVED_PLAN_CONFIRM_BOUNDARY_REVIEW_ONLY'),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-16T00:00:00+00:00'),
            ]
        );
    }

    private function mutateC159AndExecute(callable $mutator, string $name): array
    {
        $c159 = json_decode((string) file_get_contents(self::C159_FINALIZATION_ARTIFACT), true);
        $c159 = $mutator(is_array($c159) ? $c159 : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c160-source-c159-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($c159, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'c159FinalizationArtifact' => $path,
            'expectedC159FinalizationHash' => (string) ($c159['artifact_hash'] ?? ''),
            'expectedC159FinalizationFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
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
    }
}

<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC149WeeklySwingWatchlistProductionLiveRuntimeActivationOperatorGoNoGoReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC149WeeklySwingWatchlistProductionLiveRuntimeActivationOperatorGoNoGoReviewTest extends TestCase
{
    private const C148_ARTIFACT = 'storage/app/watchlist/backtest/c148-weekly-swing-watchlist-production-live-runtime-activation-observation-result-review.json';
    private const C148_HASH = 'd5420447a0b5994791e51f65318dcc46c75ec156';
    private const C148_SHA1 = '9EF227B2B7944B2406D15235DC6C84264466B81F';
    private const GO_STATUS = 'C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP_READY_FOR_C150_FINAL_ACTIVATION_EXECUTION';
    private const NO_GO_STATUS = 'C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_COMPLETED_NO_GO_PRODUCTION_LIVE_RUNTIME_ACTIVATION_STOPPED';
    private const HOLD_STATUS = 'C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_COMPLETED_HOLD_PRODUCTION_LIVE_RUNTIME_ACTIVATION_DEFERRED';
    private const APPROVAL_MISSING_STATUS = 'C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const DECISION_INVALID_STATUS = 'C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_DECISION_INVALID';
    private const DECISION_NOT_CONFIRMED_STATUS = 'C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_DECISION_NOT_CONFIRMED';
    private const DECISION_REASON_MISSING_STATUS = 'C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_DECISION_REASON_MISSING';
    private const TEMP_NEGATIVE_STATUS = 'C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const NEXT_C150 = 'C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION';

    private string $output;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c149-production-live-runtime-activation-operator-go-no-go.json';
        $this->cleanupC149TemporaryArtifacts();
        @unlink($this->output);
    }

    protected function tearDown(): void
    {
        @unlink($this->output);
        foreach ($this->tmpFiles as $path) {
            @unlink($path);
        }
        $this->cleanupC149TemporaryArtifacts();
        parent::tearDown();
    }

    public function test_c149_go_passes_with_valid_c148_lock_operator_approval_confirmed_decision_and_reason(): void
    {
        $result = $this->runService();

        $this->assertSame('C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW', $result['run_code']);
        $this->assertSame('PR-37 / C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW', $result['phase_label']);
        $this->assertSame(self::GO_STATUS, $result['status']);
        $this->assertSame(self::GO_STATUS, $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_review_pass']);
        $this->assertTrue($result['production_live_runtime_activation_operator_go_no_go_review_pass']);
        $this->assertTrue($result['operator_decision_recorded']);
        $this->assertSame('GO', $result['operator_decision']);
        $this->assertSame('GO', $result['operator_go_decision']);
        $this->assertTrue($result['operator_decision_confirmed']);
        $this->assertTrue($result['ready_for_production_live_runtime_activation_final_execution']);
        $this->assertTrue($result['production_live_runtime_activation_final_execution_allowed_next']);
        $this->assertFalse($result['production_live_runtime_activation_executed']);
        $this->assertTrue($result['c148_activation_observation_result_review_valid']);
        $this->assertTrue($result['c147_activation_observation_review_valid']);
        $this->assertTrue($result['activation_authorized']);
        $this->assertTrue($result['primary_candidate_activation_authorized']);
        $this->assertTrue($result['backup_candidate_activation_authorized']);
        $this->assertFalse($result['comparator_candidate_activation_authorized']);
        $this->assertTrue($result['c149_operator_go_no_go_review_only']);
        $this->assertTrue($result['c149_not_live_runtime_state_change']);
        $this->assertSame(self::NEXT_C150, $result['next_step_recommendation']);
        $this->assertFileExists($this->output);
    }

    public function test_c149_records_no_go_as_completed_decision_without_opening_final_execution(): void
    {
        $result = $this->runService([
            'operatorDecision' => 'NO_GO',
            'decisionReason' => 'Operator rejects live activation window after reviewing C148 evidence.',
        ]);

        $this->assertSame(self::NO_GO_STATUS, $result['status']);
        $this->assertTrue($result['operator_decision_recorded']);
        $this->assertSame('NO_GO', $result['operator_decision']);
        $this->assertTrue($result['operator_no_go_decision']);
        $this->assertFalse($result['operator_hold_decision']);
        $this->assertFalse($result['ready_for_production_live_runtime_activation_final_execution']);
        $this->assertFalse($result['production_live_runtime_activation_final_execution_allowed_next']);
        $this->assertTrue($result['production_live_runtime_activation_stopped_no_go']);
        $this->assertSame('C149_NO_GO_CLOSE_PRODUCTION_LIVE_RUNTIME_ACTIVATION', $result['next_step_recommendation']);
        $this->assertFalse($result['runtime_bridge_active']);
    }

    public function test_c149_records_hold_as_completed_decision_without_opening_final_execution(): void
    {
        $result = $this->runService([
            'operatorDecision' => 'HOLD',
            'decisionReason' => 'Operator defers live activation until a scheduled monitoring window is available.',
        ]);

        $this->assertSame(self::HOLD_STATUS, $result['status']);
        $this->assertSame('HOLD', $result['operator_decision']);
        $this->assertFalse($result['operator_no_go_decision']);
        $this->assertTrue($result['operator_hold_decision']);
        $this->assertFalse($result['ready_for_production_live_runtime_activation_final_execution']);
        $this->assertFalse($result['production_live_runtime_activation_final_execution_allowed_next']);
        $this->assertTrue($result['production_live_runtime_activation_deferred_hold']);
        $this->assertSame('C149_HOLD_KEEP_C148_LOCKED_UNTIL_OPERATOR_WINDOW', $result['next_step_recommendation']);
        $this->assertFalse($result['weekly_swing_watchlist_live_output_enabled']);
    }

    public function test_c149_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->runService(['operatorApproved' => false]);
        $missingReference = $this->runService(['approvalReference' => '']);

        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingOperator['status']);
        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingReference['status']);
    }

    public function test_c149_rejects_invalid_unconfirmed_or_unexplained_operator_decision(): void
    {
        $invalid = $this->runService(['operatorDecision' => 'MAYBE']);
        $unconfirmed = $this->runService(['operatorDecisionConfirmed' => false]);
        $missingReason = $this->runService(['decisionReason' => '']);

        $this->assertSame(self::DECISION_INVALID_STATUS, $invalid['status']);
        $this->assertSame(self::DECISION_NOT_CONFIRMED_STATUS, $unconfirmed['status']);
        $this->assertSame(self::DECISION_REASON_MISSING_STATUS, $missingReason['status']);
    }

    public function test_c149_rejects_missing_or_mismatched_c148_artifact_lock(): void
    {
        $missing = $this->runService([
            'c148Artifact' => 'storage/app/watchlist/backtest/missing-c148-for-c149.json',
            'expectedC148Hash' => 'missing',
            'expectedC148FileSha1' => 'missing',
        ]);
        $hashMismatch = $this->runService(['expectedC148Hash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedC148FileSha1' => 'BADSHA1']);

        $this->assertSame('C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C148_ARTIFACT_LOCK_MISMATCH', $missing['status']);
        $this->assertSame('C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C148_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C148_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
    }

    public function test_c149_rejects_c148_status_phase_or_next_recommendation_mismatch(): void
    {
        $status = $this->mutateC148AndExecute(function (array $c148): array {
            $c148['status'] = 'BROKEN_STATUS';
            return $c148;
        }, 'status-broken');
        $phase = $this->mutateC148AndExecute(function (array $c148): array {
            $c148['phase_label'] = 'BROKEN_PHASE';
            return $c148;
        }, 'phase-broken');
        $next = $this->mutateC148AndExecute(function (array $c148): array {
            $c148['next_step_recommendation'] = 'BROKEN_NEXT';
            $c148['next_operator_go_no_go_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c148['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $c148;
        }, 'next-broken');

        $this->assertSame('C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C148_STATUS_MISMATCH', $status['status']);
        $this->assertSame('C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C148_PHASE_LABEL_MISMATCH', $phase['status']);
        $this->assertSame('C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C148_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
    }

    /**
     * @dataProvider c148ObservationResultMismatchProvider
     */
    public function test_c149_rejects_c148_activation_observation_result_state_mismatch(string $field, $value): void
    {
        $result = $this->mutateC148AndExecute(function (array $c148) use ($field, $value): array {
            $c148[$field] = $value;
            return $c148;
        }, 'observation-result-'.$field);

        $this->assertSame('C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C148_OBSERVATION_RESULT_REVIEW_INCOMPLETE', $result['status'], $field);
    }

    public function c148ObservationResultMismatchProvider(): array
    {
        return [
            ['weekly_swing_watchlist_production_live_runtime_activation_observation_result_review_pass', false],
            ['production_live_runtime_activation_observation_result_review_pass', false],
            ['ready_for_production_live_runtime_activation_operator_go_no_go_review', false],
            ['production_live_runtime_activation_operator_go_no_go_review_allowed_next', false],
            ['c147_activation_observation_review_valid', false],
            ['c146_activation_execution_review_valid', false],
            ['c145_activation_authorization_valid', false],
            ['c144_pre_activation_boundary_valid', false],
            ['c143_go_decision_finalization_valid', false],
            ['c142_activation_operator_go_no_go_valid', false],
            ['activation_authorized', false],
            ['primary_candidate_activation_authorized', false],
            ['backup_candidate_activation_authorized', false],
            ['comparator_candidate_activation_authorized', true],
            ['c148_observation_result_review_only', false],
            ['c148_not_live_runtime_state_change', false],
            ['primary_candidate_ready_for_production_live_runtime_activation_operator_go_no_go_review', false],
            ['backup_candidate_ready_for_production_live_runtime_activation_operator_go_no_go_review', false],
            ['comparator_candidate_ready_for_production_live_runtime_activation_operator_go_no_go_review', true],
            ['production_live_runtime_activation_executed', true],
            ['weekly_swing_watchlist_live_output_enabled', true],
        ];
    }

    public function test_c149_rejects_candidate_scope_change_or_a01_promotion(): void
    {
        $candidate = $this->mutateC148AndExecute(function (array $c148): array {
            $c148['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $c148;
        }, 'candidate-primary');
        $a01 = $this->mutateC148AndExecute(function (array $c148): array {
            $c148['a01_promoted'] = true;
            return $c148;
        }, 'candidate-a01');

        $this->assertSame('C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $candidate['status']);
        $this->assertSame('C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $a01['status']);
    }

    public function test_c149_rejects_live_or_mutating_safety_flag_true_in_c148(): void
    {
        $field = 'weekly_swing_watchlist_production_live_runtime_activation_observation_result_review_context_persisted_to_live_runtime';
        $result = $this->mutateC148AndExecute(function (array $c148) use ($field): array {
            $c148[$field] = true;
            return $c148;
        }, 'safety-observation-result-review-context');

        $this->assertSame('C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_LIVE_OR_PRODUCTION_MUTATION_ATTEMPT', $result['status']);
        $this->assertSame($field, $result['c148_live_or_mutating_safety_flag_failure']);
    }

    public function test_c149_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c149-no-temporary-artifact-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame(self::TEMP_NEGATIVE_STATUS, $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c149_records_source_locks_manifest_checklist_and_no_live_output(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();
        $manifest = $result['weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_manifest'];
        $checklist = $result['weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_checklist'];

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $this->assertSame(self::C148_HASH, $result['expected_c148_hash']);
        $this->assertSame(self::C148_HASH, $result['actual_c148_hash']);
        $this->assertTrue($result['c148_hash_match']);
        $this->assertSame(self::C148_SHA1, $result['expected_c148_file_sha1']);
        $this->assertSame(self::C148_SHA1, $result['actual_c148_file_sha1']);
        $this->assertTrue($result['c148_file_sha1_match']);
        $this->assertTrue($result['c148_convert_from_json_pass']);
        $this->assertSame(self::NEXT_C150, $result['next_concrete_activation_step_decision']['next_recommendation']);

        foreach ([
            'source_artifact_locks',
            'c148_lock_validation_summary',
            'c148_activation_observation_result_review_carry_forward_summary',
            'candidate_scope_freeze_summary',
            'operator_decision_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'feature_flag_default_off_summary',
            'c149_operator_go_no_go_decision',
            'next_concrete_activation_step_decision',
            'weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_manifest',
            'weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_checklist',
            'c149_candidate_activation_operator_go_no_go_scorecard',
            'production_live_runtime_activation_operator_go_no_go_context_summary',
            'runtime_config_review_summary',
            'production_mutation_safety_summary',
            'documentation_hygiene_guard_summary',
            'progress_summary',
            'planned_next_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }

        $this->assertTrue($manifest['operator_go_no_go_artifact_only']);
        $this->assertSame('GO', $manifest['operator_decision']);
        $this->assertTrue($manifest['production_live_runtime_activation_operator_go_no_go_review_pass']);
        $this->assertTrue($manifest['ready_for_production_live_runtime_activation_final_execution']);
        $this->assertTrue($manifest['production_live_runtime_activation_final_execution_allowed_next']);
        $this->assertFalse($manifest['production_live_runtime_activation_executed']);
        $this->assertFalse($manifest['runtime_bridge_active']);
        $this->assertFalse($manifest['weekly_swing_live_output_enabled']);
        $this->assertFalse($manifest['operator_go_no_go_used_for_plan_confirm_mutation']);
        $this->assertTrue($checklist['operator_go_no_go_reviewed']);
        $this->assertTrue($checklist['artifact_only']);
        $this->assertFalse($checklist['weekly_swing_stock_recommendation_generated']);
        $this->assertFalse($result['weekly_swing_watchlist_live_output_enabled']);
        $this->assertFalse($result['weekly_swing_watchlist_live_recommendation_generated']);
    }

    public function test_c149_keeps_e02_primary_b01_backup_a01_comparator_and_all_safety_flags_false(): void
    {
        $result = $this->runService();
        $scorecard = $result['c149_candidate_activation_operator_go_no_go_scorecard'];

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $result['primary_candidate_code']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $result['backup_candidate_code']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $result['comparator_candidate_code']);
        $this->assertTrue($result['primary_candidate_ready_for_production_live_runtime_activation_final_execution']);
        $this->assertTrue($result['backup_candidate_ready_for_production_live_runtime_activation_final_execution']);
        $this->assertFalse($result['comparator_candidate_ready_for_production_live_runtime_activation_final_execution']);
        $this->assertTrue($result['a01_remains_comparator_only']);
        $this->assertTrue($scorecard[0]['primary_candidate_ready_for_production_live_runtime_activation_final_execution']);
        $this->assertTrue($scorecard[1]['backup_candidate_ready_for_production_live_runtime_activation_final_execution']);
        $this->assertFalse($scorecard[2]['ready_for_production_live_runtime_activation_final_execution']);

        foreach ($this->requiredSafetyFlags() as $flag) {
            $this->assertFalse($result[$flag], $flag);
            $this->assertFalse($result['production_mutation_safety_summary'][$flag], $flag);
        }
    }

    public function test_c149_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $first = $this->runService(['createdAt' => '2026-07-15T00:00:00+00:00']);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c149-production-live-runtime-activation-operator-go-no-go-second.json';
        $this->tmpFiles[] = $secondOutput;
        $second = $this->runService([
            'output' => $secondOutput,
            'createdAt' => '2026-07-15T00:00:00+00:00',
        ]);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
    }

    public function test_c149_does_not_mutate_c148_artifact(): void
    {
        $before = strtoupper(sha1((string) file_get_contents(self::C148_ARTIFACT)));

        $this->runService();

        $this->assertSame($before, strtoupper(sha1((string) file_get_contents(self::C148_ARTIFACT))));
    }

    private function runService(array $options = []): array
    {
        $service = new WatchlistBacktestC149WeeklySwingWatchlistProductionLiveRuntimeActivationOperatorGoNoGoReviewService();

        return $service->execute(
            (string) ($options['c148Artifact'] ?? self::C148_ARTIFACT),
            (string) ($options['expectedC148Hash'] ?? self::C148_HASH),
            (string) ($options['expectedC148FileSha1'] ?? self::C148_SHA1),
            (string) ($options['output'] ?? $this->output),
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'operator_decision_confirmed' => (bool) ($options['operatorDecisionConfirmed'] ?? true),
                'operator_decision' => (string) ($options['operatorDecision'] ?? 'GO'),
                'decision_reason' => (string) ($options['decisionReason'] ?? 'Operator records GO from locked C148 observation result review into concrete C150 final activation execution target.'),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C149_OPERATOR_DECISION_PRODUCTION_LIVE_RUNTIME_ACTIVATION_GO_NO_GO_REVIEW_ONLY'),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-15T00:00:00+00:00'),
            ]
        );
    }

    private function mutateC148AndExecute(callable $mutator, string $name): array
    {
        $c148 = json_decode((string) file_get_contents(self::C148_ARTIFACT), true);
        $c148 = $mutator(is_array($c148) ? $c148 : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c149-source-c148-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($c148, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'c148Artifact' => $path,
            'expectedC148Hash' => (string) ($c148['artifact_hash'] ?? ''),
            'expectedC148FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function readOutput(): array
    {
        $decoded = json_decode((string) file_get_contents($this->output), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function cleanupC149TemporaryArtifacts(): void
    {
        foreach ((array) glob('storage/app/watchlist/backtest/c149-*test.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/.tmp-c149*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    private function requiredSafetyFlags(): array
    {
        return [
            'production_ready',
            'production_catalog_runtime_wired',
            'production_runtime_wiring_allowed',
            'production_runtime_wiring_executed',
            'production_deployment_allowed',
            'production_deployment_executed',
            'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active',
            'controlled_rollout_active',
            'pilot_runtime_active',
            'shadow_runtime_active',
            'runtime_bridge_active',
            'plan_confirm_mutation_allowed',
            'plan_confirm_mutated',
            'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed',
            'live_plan_confirm_rollout_executed',
            'weekly_swing_watchlist_runtime_active',
            'weekly_swing_watchlist_plan_confirm_mutation_allowed',
            'weekly_swing_watchlist_live_output_enabled',
            'weekly_swing_watchlist_official_output_generated',
            'weekly_swing_watchlist_official_output_published',
            'weekly_swing_watchlist_live_recommendation_generated',
            'weekly_swing_watchlist_production_live_runtime_activation_readiness_context_persisted_to_live_runtime',
            'production_live_runtime_activation_readiness_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_production_live_runtime_activation_approval_context_persisted_to_live_runtime',
            'production_live_runtime_activation_approval_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_production_live_runtime_activation_execution_context_persisted_to_live_runtime',
            'production_live_runtime_activation_execution_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_production_live_runtime_activation_execution_review_context_persisted_to_live_runtime',
            'production_live_runtime_activation_execution_review_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_production_live_runtime_activation_observation_context_persisted_to_live_runtime',
            'production_live_runtime_activation_observation_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_production_live_runtime_activation_observation_review_context_persisted_to_live_runtime',
            'production_live_runtime_activation_observation_review_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_production_live_runtime_activation_observation_result_context_persisted_to_live_runtime',
            'production_live_runtime_activation_observation_result_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_production_live_runtime_activation_observation_result_review_context_persisted_to_live_runtime',
            'production_live_runtime_activation_observation_result_review_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_context_persisted_to_live_runtime',
            'production_live_runtime_activation_operator_go_no_go_context_persisted_to_live_runtime',
            'operator_go_no_go_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_production_live_runtime_activation_final_execution_context_persisted_to_live_runtime',
            'production_live_runtime_activation_final_execution_context_persisted_to_live_runtime',
        ];
    }
}

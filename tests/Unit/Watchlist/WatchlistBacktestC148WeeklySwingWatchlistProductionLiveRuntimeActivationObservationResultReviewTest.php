<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC148WeeklySwingWatchlistProductionLiveRuntimeActivationObservationResultReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC148WeeklySwingWatchlistProductionLiveRuntimeActivationObservationResultReviewTest extends TestCase
{
    private const C147_ARTIFACT = 'storage/app/watchlist/backtest/c147-weekly-swing-watchlist-production-live-runtime-activation-observation-review.json';
    private const C147_HASH = '42bbc885078b0557d49b38a7377444969ad171c2';
    private const C147_SHA1 = 'A1CFE8CC09856A552156AC9365EDF55F9D41A5BD';
    private const PASS_STATUS = 'C148_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_PASSED_READY_FOR_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C148_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const TEMP_NEGATIVE_STATUS = 'C148_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const NEXT_C149 = 'C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW';

    private string $output;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c148-production-live-runtime-activation-observation-result-pass.json';
        $this->cleanupC148TemporaryArtifacts();
        @unlink($this->output);
    }

    protected function tearDown(): void
    {
        @unlink($this->output);
        foreach ($this->tmpFiles as $path) {
            @unlink($path);
        }
        $this->cleanupC148TemporaryArtifacts();
        parent::tearDown();
    }

    public function test_c148_passes_with_valid_c147_activation_observation_review_lock_and_operator_approval(): void
    {
        $result = $this->runService();

        $this->assertSame('C148_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW', $result['run_code']);
        $this->assertSame('PR-36 / C148_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW', $result['phase_label']);
        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame(self::PASS_STATUS, $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_production_live_runtime_activation_observation_result_review_pass']);
        $this->assertTrue($result['production_live_runtime_activation_observation_result_review_pass']);
        $this->assertTrue($result['ready_for_production_live_runtime_activation_operator_go_no_go_review']);
        $this->assertTrue($result['production_live_runtime_activation_operator_go_no_go_review_allowed_next']);
        $this->assertTrue($result['production_live_runtime_activation_observation_result_review_manifest_created']);
        $this->assertFalse($result['production_live_runtime_activation_executed']);
        $this->assertTrue($result['c147_activation_observation_review_valid']);
        $this->assertTrue($result['c146_lock_valid']);
        $this->assertTrue($result['c146_activation_execution_review_valid']);
        $this->assertTrue($result['c146_convert_from_json_pass']);
        $this->assertTrue($result['c145_lock_valid']);
        $this->assertTrue($result['c145_activation_authorization_valid']);
        $this->assertTrue($result['c145_convert_from_json_pass']);
        $this->assertTrue($result['c144_lock_valid']);
        $this->assertTrue($result['c144_pre_activation_boundary_valid']);
        $this->assertTrue($result['c144_convert_from_json_pass']);
        $this->assertTrue($result['c143_lock_valid']);
        $this->assertTrue($result['c143_go_decision_finalization_valid']);
        $this->assertTrue($result['c143_convert_from_json_pass']);
        $this->assertTrue($result['c142_lock_valid']);
        $this->assertTrue($result['c142_activation_operator_go_no_go_valid']);
        $this->assertTrue($result['c142_convert_from_json_pass']);
        $this->assertTrue($result['c141_activation_observation_result_review_valid']);
        $this->assertTrue($result['c140_activation_observation_review_valid']);
        $this->assertTrue($result['c139_activation_execution_review_valid']);
        $this->assertTrue($result['c138_activation_authorization_valid']);
        $this->assertTrue($result['activation_authorized']);
        $this->assertTrue($result['primary_candidate_activation_authorized']);
        $this->assertTrue($result['backup_candidate_activation_authorized']);
        $this->assertFalse($result['comparator_candidate_activation_authorized']);
        $this->assertTrue($result['c148_observation_result_review_only']);
        $this->assertTrue($result['c148_not_live_runtime_state_change']);
        $this->assertSame(self::NEXT_C149, $result['next_step_recommendation']);
        $this->assertFileExists($this->output);
    }

    public function test_c148_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->runService(['operatorApproved' => false]);
        $missingReference = $this->runService(['approvalReference' => '']);

        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingOperator['status']);
        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingReference['status']);
    }

    public function test_c148_rejects_missing_or_mismatched_c147_artifact_lock(): void
    {
        $missing = $this->runService([
            'c147Artifact' => 'storage/app/watchlist/backtest/missing-c147-for-c148.json',
            'expectedC147Hash' => 'missing',
            'expectedC147FileSha1' => 'missing',
        ]);
        $hashMismatch = $this->runService(['expectedC147Hash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedC147FileSha1' => 'BADSHA1']);

        $this->assertSame('C148_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_C147_ARTIFACT_LOCK_MISMATCH', $missing['status']);
        $this->assertSame('C148_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_C147_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C148_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_C147_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
    }

    public function test_c148_rejects_c147_status_phase_or_next_recommendation_mismatch(): void
    {
        $status = $this->mutateC147AndExecute(function (array $c147): array {
            $c147['status'] = 'BROKEN_STATUS';
            return $c147;
        }, 'status-broken');
        $phase = $this->mutateC147AndExecute(function (array $c147): array {
            $c147['phase_label'] = 'BROKEN_PHASE';
            return $c147;
        }, 'phase-broken');
        $next = $this->mutateC147AndExecute(function (array $c147): array {
            $c147['next_step_recommendation'] = 'BROKEN_NEXT';
            $c147['next_observation_result_decision']['next_recommendation'] = 'BROKEN_NEXT';
            return $c147;
        }, 'next-broken');

        $this->assertSame('C148_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_C147_STATUS_MISMATCH', $status['status']);
        $this->assertSame('C148_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_C147_PHASE_LABEL_MISMATCH', $phase['status']);
        $this->assertSame('C148_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_C147_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
    }

    public function test_c148_rejects_c147_convert_from_json_duplicate_top_level_keys(): void
    {
        $raw = (string) file_get_contents(self::C147_ARTIFACT);
        $path = 'storage/app/watchlist/backtest/.tmp-c148-source-c147-duplicate-key.json';
        $this->tmpFiles[] = $path;
        $duplicateRaw = preg_replace('/\{/', "{\"Run_Code\":\"DUPLICATE_CASE_KEY\",", $raw, 1);
        file_put_contents($path, $duplicateRaw);

        $result = $this->runService([
            'c147Artifact' => $path,
            'expectedC147Hash' => self::C147_HASH,
            'expectedC147FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);

        $this->assertSame('C148_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_C147_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION', $result['status']);
        $this->assertFalse($result['c147_convert_from_json_pass']);
    }

    /**
     * @dataProvider c147ObservationReviewMismatchProvider
     */
    public function test_c148_rejects_c147_activation_observation_review_state_mismatch(string $field, $value): void
    {
        $result = $this->mutateC147AndExecute(function (array $c147) use ($field, $value): array {
            $c147[$field] = $value;
            return $c147;
        }, 'observation-'.$field);

        $this->assertSame('C148_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_C147_ACTIVATION_OBSERVATION_REVIEW_INCOMPLETE', $result['status'], $field);
    }

    public function c147ObservationReviewMismatchProvider(): array
    {
        return [
            ['weekly_swing_watchlist_production_live_runtime_activation_observation_review_pass', false],
            ['production_live_runtime_activation_observation_review_pass', false],
            ['ready_for_production_live_runtime_activation_observation_result_review', false],
            ['production_live_runtime_activation_observation_result_review_allowed_next', false],
            ['production_live_runtime_activation_observation_review_manifest_created', false],
            ['c146_lock_valid', false],
            ['c146_activation_execution_review_valid', false],
            ['c146_convert_from_json_pass', false],
            ['c145_lock_valid', false],
            ['c145_activation_authorization_valid', false],
            ['c145_convert_from_json_pass', false],
            ['c144_lock_valid', false],
            ['c144_pre_activation_boundary_valid', false],
            ['c144_convert_from_json_pass', false],
            ['c143_lock_valid', false],
            ['c143_go_decision_finalization_valid', false],
            ['c143_convert_from_json_pass', false],
            ['c142_lock_valid', false],
            ['c142_activation_operator_go_no_go_valid', false],
            ['c142_convert_from_json_pass', false],
            ['c141_activation_observation_result_review_valid', false],
            ['c140_activation_observation_review_valid', false],
            ['c139_activation_execution_review_valid', false],
            ['c138_activation_authorization_valid', false],
            ['c137_pre_activation_boundary_valid', false],
            ['c136_go_decision_finalization_valid', false],
            ['c135_activation_operator_go_no_go_valid', false],
            ['c134_activation_observation_result_review_valid', false],
            ['c133_activation_observation_review_valid', false],
            ['c132_activation_execution_review_valid', false],
            ['c131_activation_approval_valid', false],
            ['c130_activation_readiness_valid', false],
            ['c129_final_closure_valid', false],
            ['activation_authorized', false],
            ['primary_candidate_activation_authorized', false],
            ['backup_candidate_activation_authorized', false],
            ['comparator_candidate_activation_authorized', true],
            ['c138_activation_authorization_review_only', false],
            ['c138_not_activation_execution', false],
            ['c138_not_live_runtime_state_change', false],
            ['c139_execution_review_only', false],
            ['c139_not_live_runtime_state_change', false],
            ['c140_observation_review_only', false],
            ['c140_not_live_runtime_state_change', false],
            ['c141_observation_result_review_only', false],
            ['c141_not_live_runtime_state_change', false],
            ['c142_operator_go_no_go_review_only', false],
            ['c142_not_live_runtime_state_change', false],
            ['c143_go_decision_finalization_review_only', false],
            ['c144_pre_activation_boundary_review_only', false],
            ['c144_not_activation_authorization', false],
            ['c145_activation_authorization_review_only', false],
            ['c145_not_activation_execution', false],
            ['c145_not_live_runtime_state_change', false],
            ['c146_execution_review_only', false],
            ['c146_not_live_runtime_state_change', false],
            ['c147_observation_review_only', false],
            ['c147_not_live_runtime_state_change', false],
            ['primary_candidate_ready_for_production_live_runtime_activation_observation_result_review', false],
            ['backup_candidate_ready_for_production_live_runtime_activation_observation_result_review', false],
            ['comparator_candidate_ready_for_production_live_runtime_activation_observation_result_review', true],
            ['production_live_runtime_activation_executed', true],
            ['weekly_swing_watchlist_live_output_enabled', true],
        ];
    }

    public function test_c148_rejects_candidate_scope_change_or_a01_promotion(): void
    {
        $candidate = $this->mutateC147AndExecute(function (array $c147): array {
            $c147['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $c147;
        }, 'candidate-primary');
        $a01 = $this->mutateC147AndExecute(function (array $c147): array {
            $c147['a01_promoted'] = true;
            return $c147;
        }, 'candidate-a01');

        $this->assertSame('C148_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $candidate['status']);
        $this->assertSame('C148_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $a01['status']);
    }

    public function test_c148_rejects_live_or_mutating_safety_flag_true_in_c147(): void
    {
        $field = 'weekly_swing_watchlist_production_live_runtime_activation_observation_result_context_persisted_to_live_runtime';
        $result = $this->mutateC147AndExecute(function (array $c147) use ($field): array {
            $c147[$field] = true;
            return $c147;
        }, 'safety-observation-result-context');

        $this->assertSame('C148_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_LIVE_OR_PRODUCTION_MUTATION_ATTEMPT', $result['status']);
        $this->assertSame($field, $result['c147_live_or_mutating_safety_flag_failure']);
    }

    public function test_c148_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c148-no-temporary-artifact-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame(self::TEMP_NEGATIVE_STATUS, $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c148_records_source_locks_manifest_checklist_and_no_live_output(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();
        $manifest = $result['weekly_swing_watchlist_production_live_runtime_activation_observation_result_review_manifest'];
        $checklist = $result['weekly_swing_watchlist_production_live_runtime_activation_observation_result_review_checklist'];
        $carry = $result['c147_activation_observation_review_carry_forward_summary'];

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $this->assertSame(self::C147_HASH, $result['expected_c147_hash']);
        $this->assertSame(self::C147_HASH, $result['actual_c147_hash']);
        $this->assertTrue($result['c147_hash_match']);
        $this->assertSame(self::C147_SHA1, $result['expected_c147_file_sha1']);
        $this->assertSame(self::C147_SHA1, $result['actual_c147_file_sha1']);
        $this->assertTrue($result['c147_file_sha1_match']);
        $this->assertTrue($result['c147_convert_from_json_pass']);
        $this->assertSame(self::NEXT_C149, $result['next_operator_go_no_go_decision']['next_recommendation']);
        $this->assertTrue($carry['c146_activation_execution_review_valid']);
        $this->assertTrue($carry['c145_activation_authorization_valid']);
        $this->assertTrue($carry['c144_pre_activation_boundary_valid']);
        $this->assertTrue($carry['c143_go_decision_finalization_valid']);
        $this->assertTrue($carry['c142_activation_operator_go_no_go_valid']);
        $this->assertTrue($carry['c139_activation_execution_review_valid']);
        $this->assertTrue($carry['c138_activation_authorization_valid']);

        foreach ([
            'source_artifact_locks',
            'c147_lock_validation_summary',
            'c147_activation_observation_review_carry_forward_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'feature_flag_default_off_summary',
            'c148_observation_result_decision',
            'next_operator_go_no_go_decision',
            'weekly_swing_watchlist_production_live_runtime_activation_observation_result_review_manifest',
            'weekly_swing_watchlist_production_live_runtime_activation_observation_result_review_checklist',
            'c148_candidate_activation_observation_result_scorecard',
            'production_live_runtime_activation_observation_result_review_context_summary',
            'runtime_config_review_summary',
            'production_mutation_safety_summary',
            'documentation_hygiene_guard_summary',
            'progress_summary',
            'planned_next_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }

        $this->assertTrue($manifest['activation_observation_result_review_artifact_only']);
        $this->assertTrue($manifest['production_live_runtime_activation_observation_result_review_pass']);
        $this->assertTrue($manifest['ready_for_production_live_runtime_activation_operator_go_no_go_review']);
        $this->assertTrue($manifest['production_live_runtime_activation_operator_go_no_go_review_required_next']);
        $this->assertFalse($manifest['production_live_runtime_activation_executed']);
        $this->assertFalse($manifest['runtime_bridge_active']);
        $this->assertFalse($manifest['weekly_swing_live_output_enabled']);
        $this->assertFalse($manifest['activation_observation_result_review_used_for_plan_confirm_mutation']);
        $this->assertTrue($checklist['live_runtime_activation_operator_go_no_go_review_required_next']);
        $this->assertTrue($checklist['artifact_only']);
        $this->assertFalse($checklist['weekly_swing_stock_recommendation_generated']);
    }

    public function test_c148_keeps_e02_primary_b01_backup_a01_comparator_and_all_safety_flags_false(): void
    {
        $result = $this->runService();

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $result['primary_candidate_code']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $result['backup_candidate_code']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $result['comparator_candidate_code']);
        $this->assertTrue($result['primary_candidate_ready_for_production_live_runtime_activation_operator_go_no_go_review']);
        $this->assertTrue($result['backup_candidate_ready_for_production_live_runtime_activation_operator_go_no_go_review']);
        $this->assertFalse($result['comparator_candidate_ready_for_production_live_runtime_activation_operator_go_no_go_review']);
        $this->assertTrue($result['a01_remains_comparator_only']);

        foreach ($this->requiredSafetyFlags() as $flag) {
            $this->assertFalse($result[$flag], $flag);
            $this->assertFalse($result['production_mutation_safety_summary'][$flag], $flag);
        }
    }

    public function test_c148_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $first = $this->runService(['createdAt' => '2026-07-14T00:00:00+00:00']);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c148-production-live-runtime-activation-observation-result-pass-second.json';
        $this->tmpFiles[] = $secondOutput;
        $second = $this->runService([
            'output' => $secondOutput,
            'createdAt' => '2026-07-14T00:00:00+00:00',
        ]);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
    }

    public function test_c148_does_not_mutate_c147_artifact(): void
    {
        $before = strtoupper(sha1((string) file_get_contents(self::C147_ARTIFACT)));

        $this->runService();

        $this->assertSame($before, strtoupper(sha1((string) file_get_contents(self::C147_ARTIFACT))));
    }

    private function runService(array $options = []): array
    {
        $service = new WatchlistBacktestC148WeeklySwingWatchlistProductionLiveRuntimeActivationObservationResultReviewService();

        return $service->execute(
            (string) ($options['c147Artifact'] ?? self::C147_ARTIFACT),
            (string) ($options['expectedC147Hash'] ?? self::C147_HASH),
            (string) ($options['expectedC147FileSha1'] ?? self::C147_SHA1),
            (string) ($options['output'] ?? $this->output),
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C148_OPERATOR_APPROVED_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_ONLY'),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-14T00:00:00+00:00'),
            ]
        );
    }

    private function mutateC147AndExecute(callable $mutator, string $name): array
    {
        $c147 = json_decode((string) file_get_contents(self::C147_ARTIFACT), true);
        $c147 = $mutator(is_array($c147) ? $c147 : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c148-source-c147-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($c147, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'c147Artifact' => $path,
            'expectedC147Hash' => (string) ($c147['artifact_hash'] ?? ''),
            'expectedC147FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function readOutput(): array
    {
        $decoded = json_decode((string) file_get_contents($this->output), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function cleanupC148TemporaryArtifacts(): void
    {
        foreach ((array) glob('storage/app/watchlist/backtest/c148-*test.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/.tmp-c148*.json') as $file) {
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
        ];
    }
}

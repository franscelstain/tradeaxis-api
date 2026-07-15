<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC141WeeklySwingWatchlistProductionLiveRuntimeActivationObservationResultReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC141WeeklySwingWatchlistProductionLiveRuntimeActivationObservationResultReviewTest extends TestCase
{
    private const C140_ARTIFACT = 'storage/app/watchlist/backtest/c140-weekly-swing-watchlist-production-live-runtime-activation-observation-review.json';
    private const C140_HASH = 'e1a428c007dbe40d438e34a15c74d57a58cf5449';
    private const C140_SHA1 = '91EA2C44BB6E8742F55203589BFCFB7E1088DD6B';
    private const PASS_STATUS = 'C141_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_PASSED_READY_FOR_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C141_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const TEMP_NEGATIVE_STATUS = 'C141_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const NEXT_C142 = 'C142_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW';

    private string $output;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c141-production-live-runtime-activation-observation-result-pass.json';
        $this->cleanupC141TemporaryArtifacts();
        @unlink($this->output);
    }

    protected function tearDown(): void
    {
        @unlink($this->output);
        foreach ($this->tmpFiles as $path) {
            @unlink($path);
        }
        $this->cleanupC141TemporaryArtifacts();
        parent::tearDown();
    }

    public function test_c141_passes_with_valid_c140_activation_observation_review_lock_and_operator_approval(): void
    {
        $result = $this->runService();

        $this->assertSame('C141_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW', $result['run_code']);
        $this->assertSame('PR-29 / C141_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW', $result['phase_label']);
        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame(self::PASS_STATUS, $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_production_live_runtime_activation_observation_result_review_pass']);
        $this->assertTrue($result['production_live_runtime_activation_observation_result_review_pass']);
        $this->assertTrue($result['ready_for_production_live_runtime_activation_operator_go_no_go_review']);
        $this->assertTrue($result['production_live_runtime_activation_operator_go_no_go_review_allowed_next']);
        $this->assertTrue($result['production_live_runtime_activation_observation_result_review_manifest_created']);
        $this->assertFalse($result['production_live_runtime_activation_executed']);
        $this->assertTrue($result['c140_activation_observation_review_valid']);
        $this->assertTrue($result['c139_activation_execution_review_valid']);
        $this->assertTrue($result['c138_activation_authorization_valid']);
        $this->assertTrue($result['activation_authorized']);
        $this->assertTrue($result['primary_candidate_activation_authorized']);
        $this->assertTrue($result['backup_candidate_activation_authorized']);
        $this->assertFalse($result['comparator_candidate_activation_authorized']);
        $this->assertTrue($result['c141_observation_result_review_only']);
        $this->assertTrue($result['c141_not_live_runtime_state_change']);
        $this->assertSame(self::NEXT_C142, $result['next_step_recommendation']);
        $this->assertFileExists($this->output);
    }

    public function test_c141_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->runService(['operatorApproved' => false]);
        $missingReference = $this->runService(['approvalReference' => '']);

        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingOperator['status']);
        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingReference['status']);
    }

    public function test_c141_rejects_missing_or_mismatched_c140_artifact_lock(): void
    {
        $missing = $this->runService([
            'c140Artifact' => 'storage/app/watchlist/backtest/missing-c140-for-c141.json',
            'expectedC140Hash' => 'missing',
            'expectedC140FileSha1' => 'missing',
        ]);
        $hashMismatch = $this->runService(['expectedC140Hash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedC140FileSha1' => 'BADSHA1']);

        $this->assertSame('C141_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_C140_ARTIFACT_LOCK_MISMATCH', $missing['status']);
        $this->assertSame('C141_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_C140_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C141_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_C140_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
    }

    public function test_c141_rejects_c140_status_phase_or_next_recommendation_mismatch(): void
    {
        $status = $this->mutateC140AndExecute(function (array $c140): array {
            $c140['status'] = 'BROKEN_STATUS';
            return $c140;
        }, 'status-broken');
        $phase = $this->mutateC140AndExecute(function (array $c140): array {
            $c140['phase_label'] = 'BROKEN_PHASE';
            return $c140;
        }, 'phase-broken');
        $next = $this->mutateC140AndExecute(function (array $c140): array {
            $c140['next_step_recommendation'] = 'BROKEN_NEXT';
            $c140['next_observation_result_decision']['next_recommendation'] = 'BROKEN_NEXT';
            return $c140;
        }, 'next-broken');

        $this->assertSame('C141_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_C140_STATUS_MISMATCH', $status['status']);
        $this->assertSame('C141_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_C140_PHASE_LABEL_MISMATCH', $phase['status']);
        $this->assertSame('C141_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_C140_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
    }

    public function test_c141_rejects_c140_convert_from_json_duplicate_top_level_keys(): void
    {
        $raw = (string) file_get_contents(self::C140_ARTIFACT);
        $path = 'storage/app/watchlist/backtest/.tmp-c141-source-c140-duplicate-key.json';
        $this->tmpFiles[] = $path;
        $duplicateRaw = preg_replace('/\{/', "{\"Run_Code\":\"DUPLICATE_CASE_KEY\",", $raw, 1);
        file_put_contents($path, $duplicateRaw);

        $result = $this->runService([
            'c140Artifact' => $path,
            'expectedC140Hash' => self::C140_HASH,
            'expectedC140FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);

        $this->assertSame('C141_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_C140_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION', $result['status']);
        $this->assertFalse($result['c140_convert_from_json_pass']);
    }

    /**
     * @dataProvider c140ObservationReviewMismatchProvider
     */
    public function test_c141_rejects_c140_activation_observation_review_state_mismatch(string $field, $value): void
    {
        $result = $this->mutateC140AndExecute(function (array $c140) use ($field, $value): array {
            $c140[$field] = $value;
            return $c140;
        }, 'observation-'.$field);

        $this->assertSame('C141_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_C140_ACTIVATION_OBSERVATION_REVIEW_INCOMPLETE', $result['status'], $field);
    }

    public function c140ObservationReviewMismatchProvider(): array
    {
        return [
            ['weekly_swing_watchlist_production_live_runtime_activation_observation_review_pass', false],
            ['production_live_runtime_activation_observation_review_pass', false],
            ['ready_for_production_live_runtime_activation_observation_result_review', false],
            ['production_live_runtime_activation_observation_result_review_allowed_next', false],
            ['production_live_runtime_activation_observation_review_manifest_created', false],
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
            ['primary_candidate_ready_for_production_live_runtime_activation_observation_result_review', false],
            ['backup_candidate_ready_for_production_live_runtime_activation_observation_result_review', false],
            ['comparator_candidate_ready_for_production_live_runtime_activation_observation_result_review', true],
            ['production_live_runtime_activation_executed', true],
            ['weekly_swing_watchlist_live_output_enabled', true],
        ];
    }

    public function test_c141_rejects_candidate_scope_change_or_a01_promotion(): void
    {
        $candidate = $this->mutateC140AndExecute(function (array $c140): array {
            $c140['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $c140;
        }, 'candidate-primary');
        $a01 = $this->mutateC140AndExecute(function (array $c140): array {
            $c140['a01_promoted'] = true;
            return $c140;
        }, 'candidate-a01');

        $this->assertSame('C141_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $candidate['status']);
        $this->assertSame('C141_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $a01['status']);
    }

    public function test_c141_rejects_live_or_mutating_safety_flag_true_in_c140(): void
    {
        $field = 'weekly_swing_watchlist_production_live_runtime_activation_observation_result_context_persisted_to_live_runtime';
        $result = $this->mutateC140AndExecute(function (array $c140) use ($field): array {
            $c140[$field] = true;
            return $c140;
        }, 'safety-observation-result-context');

        $this->assertSame('C141_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_LIVE_OR_PRODUCTION_MUTATION_ATTEMPT', $result['status']);
        $this->assertSame($field, $result['c140_live_or_mutating_safety_flag_failure']);
    }

    public function test_c141_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c141-no-temporary-artifact-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame(self::TEMP_NEGATIVE_STATUS, $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c141_records_source_locks_manifest_checklist_and_no_live_output(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();
        $manifest = $result['weekly_swing_watchlist_production_live_runtime_activation_observation_result_review_manifest'];
        $checklist = $result['weekly_swing_watchlist_production_live_runtime_activation_observation_result_review_checklist'];

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $this->assertSame(self::C140_HASH, $result['expected_c140_hash']);
        $this->assertSame(self::C140_HASH, $result['actual_c140_hash']);
        $this->assertTrue($result['c140_hash_match']);
        $this->assertSame(self::C140_SHA1, $result['expected_c140_file_sha1']);
        $this->assertSame(self::C140_SHA1, $result['actual_c140_file_sha1']);
        $this->assertTrue($result['c140_file_sha1_match']);
        $this->assertTrue($result['c140_convert_from_json_pass']);
        $this->assertSame(self::NEXT_C142, $result['next_operator_go_no_go_decision']['next_recommendation']);

        foreach ([
            'source_artifact_locks',
            'c140_lock_validation_summary',
            'c140_activation_observation_review_carry_forward_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'feature_flag_default_off_summary',
            'c141_observation_result_decision',
            'next_operator_go_no_go_decision',
            'weekly_swing_watchlist_production_live_runtime_activation_observation_result_review_manifest',
            'weekly_swing_watchlist_production_live_runtime_activation_observation_result_review_checklist',
            'c141_candidate_activation_observation_result_scorecard',
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

    public function test_c141_keeps_e02_primary_b01_backup_a01_comparator_and_all_safety_flags_false(): void
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

    public function test_c141_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $first = $this->runService(['createdAt' => '2026-07-14T00:00:00+00:00']);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c141-production-live-runtime-activation-observation-result-pass-second.json';
        $this->tmpFiles[] = $secondOutput;
        $second = $this->runService([
            'output' => $secondOutput,
            'createdAt' => '2026-07-14T00:00:00+00:00',
        ]);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
    }

    public function test_c141_does_not_mutate_c140_artifact(): void
    {
        $before = strtoupper(sha1((string) file_get_contents(self::C140_ARTIFACT)));

        $this->runService();

        $this->assertSame($before, strtoupper(sha1((string) file_get_contents(self::C140_ARTIFACT))));
    }

    private function runService(array $options = []): array
    {
        $service = new WatchlistBacktestC141WeeklySwingWatchlistProductionLiveRuntimeActivationObservationResultReviewService();

        return $service->execute(
            (string) ($options['c140Artifact'] ?? self::C140_ARTIFACT),
            (string) ($options['expectedC140Hash'] ?? self::C140_HASH),
            (string) ($options['expectedC140FileSha1'] ?? self::C140_SHA1),
            (string) ($options['output'] ?? $this->output),
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C141_OPERATOR_APPROVED_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_ONLY'),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-14T00:00:00+00:00'),
            ]
        );
    }

    private function mutateC140AndExecute(callable $mutator, string $name): array
    {
        $c140 = json_decode((string) file_get_contents(self::C140_ARTIFACT), true);
        $c140 = $mutator(is_array($c140) ? $c140 : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c141-source-c140-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($c140, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'c140Artifact' => $path,
            'expectedC140Hash' => (string) ($c140['artifact_hash'] ?? ''),
            'expectedC140FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function readOutput(): array
    {
        $decoded = json_decode((string) file_get_contents($this->output), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function cleanupC141TemporaryArtifacts(): void
    {
        foreach ((array) glob('storage/app/watchlist/backtest/c141-*test.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/.tmp-c141*.json') as $file) {
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

<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC135WeeklySwingWatchlistProductionLiveRuntimeActivationOperatorGoNoGoReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC135WeeklySwingWatchlistProductionLiveRuntimeActivationOperatorGoNoGoReviewTest extends TestCase
{
    private const C134_ARTIFACT = 'storage/app/watchlist/backtest/c134-weekly-swing-watchlist-production-live-runtime-activation-observation-result-review.json';
    private const C134_HASH = 'ada066cc599d749e050b5efd61073ccad1e64b74';
    private const C134_SHA1 = 'AE7C013A1B5CC0DFC5968C4FC99B2E1DDFF88F3E';
    private const PASS_STATUS = 'C135_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C135_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const GO_NOT_CONFIRMED_STATUS = 'C135_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_GO_DECISION_NOT_CONFIRMED';
    private const TEMP_NEGATIVE_STATUS = 'C135_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const NEXT_C136 = 'C136_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW';

    private string $output;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c135-production-live-runtime-activation-operator-go-no-go-pass.json';
        $this->cleanupC135TemporaryArtifacts();
        @unlink($this->output);
    }

    protected function tearDown(): void
    {
        @unlink($this->output);
        foreach ($this->tmpFiles as $path) {
            @unlink($path);
        }
        $this->cleanupC135TemporaryArtifacts();
        parent::tearDown();
    }

    public function test_c135_passes_with_valid_c134_lock_operator_approval_and_go_confirmation(): void
    {
        $result = $this->runService();

        $this->assertSame('C135_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW', $result['run_code']);
        $this->assertSame('PR-23 / C135_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW', $result['phase_label']);
        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame(self::PASS_STATUS, $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_review_pass']);
        $this->assertTrue($result['production_live_runtime_activation_operator_go_no_go_review_pass']);
        $this->assertSame('GO', $result['operator_go_decision']);
        $this->assertTrue($result['operator_go_decision_confirmed']);
        $this->assertTrue($result['ready_for_production_live_runtime_activation_go_decision_finalization_review']);
        $this->assertTrue($result['production_live_runtime_activation_go_decision_finalization_review_allowed_next']);
        $this->assertFalse($result['production_live_runtime_activation_executed']);
        $this->assertTrue($result['c134_activation_observation_result_review_valid']);
        $this->assertTrue($result['c135_operator_go_no_go_review_only']);
        $this->assertTrue($result['c135_not_live_runtime_state_change']);
        $this->assertSame(self::NEXT_C136, $result['next_step_recommendation']);
        $this->assertFileExists($this->output);
    }

    public function test_c135_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->runService(['operatorApproved' => false]);
        $missingReference = $this->runService(['approvalReference' => '']);

        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingOperator['status']);
        $this->assertSame('NO_GO', $missingOperator['operator_go_decision']);
        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingReference['status']);
    }

    public function test_c135_rejects_unconfirmed_operator_go_decision(): void
    {
        $result = $this->runService(['operatorGoDecisionConfirmed' => false]);

        $this->assertSame(self::GO_NOT_CONFIRMED_STATUS, $result['status']);
        $this->assertSame('NO_GO', $result['operator_go_decision']);
        $this->assertFalse($result['operator_go_decision_confirmed']);
    }

    public function test_c135_rejects_missing_or_mismatched_c134_artifact_lock(): void
    {
        $missing = $this->runService([
            'c134Artifact' => 'storage/app/watchlist/backtest/missing-c134-for-c135.json',
            'expectedC134Hash' => 'missing',
            'expectedC134FileSha1' => 'missing',
        ]);
        $hashMismatch = $this->runService(['expectedC134Hash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedC134FileSha1' => 'BADSHA1']);

        $this->assertSame('C135_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C134_ARTIFACT_LOCK_MISMATCH', $missing['status']);
        $this->assertSame('C135_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C134_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C135_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C134_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
    }

    public function test_c135_rejects_c134_status_phase_or_next_recommendation_mismatch(): void
    {
        $status = $this->mutateC134AndExecute(function (array $c134): array {
            $c134['status'] = 'BROKEN_STATUS';
            return $c134;
        }, 'status-broken');
        $phase = $this->mutateC134AndExecute(function (array $c134): array {
            $c134['phase_label'] = 'BROKEN_PHASE';
            return $c134;
        }, 'phase-broken');
        $next = $this->mutateC134AndExecute(function (array $c134): array {
            $c134['next_step_recommendation'] = 'BROKEN_NEXT';
            $c134['next_operator_go_no_go_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c134['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $c134;
        }, 'next-broken');

        $this->assertSame('C135_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C134_STATUS_MISMATCH', $status['status']);
        $this->assertSame('C135_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C134_PHASE_LABEL_MISMATCH', $phase['status']);
        $this->assertSame('C135_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C134_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
    }

    public function test_c135_rejects_c134_convert_from_json_duplicate_top_level_keys(): void
    {
        $raw = (string) file_get_contents(self::C134_ARTIFACT);
        $path = 'storage/app/watchlist/backtest/.tmp-c135-source-c134-duplicate-key.json';
        $this->tmpFiles[] = $path;
        $duplicateRaw = preg_replace('/\{/', "{\"Run_Code\":\"DUPLICATE_CASE_KEY\",", $raw, 1);
        file_put_contents($path, $duplicateRaw);

        $result = $this->runService([
            'c134Artifact' => $path,
            'expectedC134Hash' => self::C134_HASH,
            'expectedC134FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);

        $this->assertSame('C135_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C134_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION', $result['status']);
        $this->assertFalse($result['c134_convert_from_json_pass']);
    }

    /**
     * @dataProvider c134ObservationResultReviewMismatchProvider
     */
    public function test_c135_rejects_c134_activation_observation_result_review_state_mismatch(string $field, $value): void
    {
        $result = $this->mutateC134AndExecute(function (array $c134) use ($field, $value): array {
            $c134[$field] = $value;
            return $c134;
        }, 'observation-result-'.$field);

        $this->assertSame('C135_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C134_OBSERVATION_RESULT_REVIEW_INCOMPLETE', $result['status'], $field);
    }

    public function c134ObservationResultReviewMismatchProvider(): array
    {
        return [
            ['weekly_swing_watchlist_production_live_runtime_activation_observation_result_review_pass', false],
            ['production_live_runtime_activation_observation_result_review_pass', false],
            ['ready_for_production_live_runtime_activation_operator_go_no_go_review', false],
            ['production_live_runtime_activation_operator_go_no_go_review_allowed_next', false],
            ['production_live_runtime_activation_observation_result_review_manifest_created', false],
            ['c133_activation_observation_review_valid', false],
            ['c132_activation_execution_review_valid', false],
            ['c131_activation_approval_valid', false],
            ['c130_activation_readiness_valid', false],
            ['c129_final_closure_valid', false],
            ['c134_observation_result_review_only', false],
            ['c134_not_live_runtime_state_change', false],
            ['primary_candidate_ready_for_production_live_runtime_activation_operator_go_no_go_review', false],
            ['backup_candidate_ready_for_production_live_runtime_activation_operator_go_no_go_review', false],
            ['comparator_candidate_ready_for_production_live_runtime_activation_operator_go_no_go_review', true],
            ['production_live_runtime_activation_executed', true],
            ['weekly_swing_watchlist_live_output_enabled', true],
        ];
    }

    public function test_c135_rejects_candidate_scope_change_or_a01_promotion(): void
    {
        $candidate = $this->mutateC134AndExecute(function (array $c134): array {
            $c134['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $c134;
        }, 'candidate-primary');
        $a01 = $this->mutateC134AndExecute(function (array $c134): array {
            $c134['a01_promoted'] = true;
            return $c134;
        }, 'candidate-a01');

        $this->assertSame('C135_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $candidate['status']);
        $this->assertSame('C135_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $a01['status']);
    }

    public function test_c135_rejects_live_or_mutating_safety_flag_true_in_c134(): void
    {
        $field = 'weekly_swing_watchlist_production_live_runtime_activation_observation_result_review_context_persisted_to_live_runtime';
        $result = $this->mutateC134AndExecute(function (array $c134) use ($field): array {
            $c134[$field] = true;
            return $c134;
        }, 'safety-observation-result-review-context');

        $this->assertSame('C135_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_LIVE_OR_PRODUCTION_MUTATION_ATTEMPT', $result['status']);
        $this->assertSame($field, $result['c134_live_or_mutating_safety_flag_failure']);
    }

    public function test_c135_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c135-no-temporary-artifact-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame(self::TEMP_NEGATIVE_STATUS, $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c135_records_source_locks_manifest_checklist_and_no_live_output(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();
        $manifest = $result['weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_manifest'];
        $checklist = $result['weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_checklist'];

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $this->assertSame(self::C134_HASH, $result['expected_c134_hash']);
        $this->assertSame(self::C134_HASH, $result['actual_c134_hash']);
        $this->assertTrue($result['c134_hash_match']);
        $this->assertSame(self::C134_SHA1, $result['expected_c134_file_sha1']);
        $this->assertSame(self::C134_SHA1, $result['actual_c134_file_sha1']);
        $this->assertTrue($result['c134_file_sha1_match']);
        $this->assertTrue($result['c134_convert_from_json_pass']);
        $this->assertSame(self::NEXT_C136, $result['next_go_decision_finalization_decision']['next_recommendation']);

        foreach ([
            'source_artifact_locks',
            'c134_lock_validation_summary',
            'c134_activation_observation_result_review_carry_forward_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'feature_flag_default_off_summary',
            'c135_operator_go_no_go_decision',
            'next_go_decision_finalization_decision',
            'weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_manifest',
            'weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_checklist',
            'c135_candidate_activation_operator_go_no_go_scorecard',
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
        $this->assertSame('GO', $manifest['operator_go_decision']);
        $this->assertTrue($manifest['production_live_runtime_activation_operator_go_no_go_review_pass']);
        $this->assertTrue($manifest['ready_for_production_live_runtime_activation_go_decision_finalization_review']);
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

    public function test_c135_keeps_e02_primary_b01_backup_a01_comparator_and_all_safety_flags_false(): void
    {
        $result = $this->runService();
        $scorecard = $result['c135_candidate_activation_operator_go_no_go_scorecard'];

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $result['primary_candidate_code']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $result['backup_candidate_code']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $result['comparator_candidate_code']);
        $this->assertTrue($result['primary_candidate_ready_for_production_live_runtime_activation_go_decision_finalization_review']);
        $this->assertTrue($result['backup_candidate_ready_for_production_live_runtime_activation_go_decision_finalization_review']);
        $this->assertFalse($result['comparator_candidate_ready_for_production_live_runtime_activation_go_decision_finalization_review']);
        $this->assertTrue($result['a01_remains_comparator_only']);
        $this->assertTrue($scorecard[0]['primary_candidate_ready_for_production_live_runtime_activation_go_decision_finalization_review']);
        $this->assertTrue($scorecard[1]['backup_candidate_ready_for_production_live_runtime_activation_go_decision_finalization_review']);
        $this->assertFalse($scorecard[2]['ready_for_production_live_runtime_activation_go_decision_finalization_review']);

        foreach ($this->requiredSafetyFlags() as $flag) {
            $this->assertFalse($result[$flag], $flag);
            $this->assertFalse($result['production_mutation_safety_summary'][$flag], $flag);
        }
    }

    public function test_c135_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $first = $this->runService(['createdAt' => '2026-07-14T00:00:00+00:00']);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c135-production-live-runtime-activation-operator-go-no-go-pass-second.json';
        $this->tmpFiles[] = $secondOutput;
        $second = $this->runService([
            'output' => $secondOutput,
            'createdAt' => '2026-07-14T00:00:00+00:00',
        ]);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
    }

    public function test_c135_does_not_mutate_c134_artifact(): void
    {
        $before = strtoupper(sha1((string) file_get_contents(self::C134_ARTIFACT)));

        $this->runService();

        $this->assertSame($before, strtoupper(sha1((string) file_get_contents(self::C134_ARTIFACT))));
    }

    private function runService(array $options = []): array
    {
        $service = new WatchlistBacktestC135WeeklySwingWatchlistProductionLiveRuntimeActivationOperatorGoNoGoReviewService();

        return $service->execute(
            (string) ($options['c134Artifact'] ?? self::C134_ARTIFACT),
            (string) ($options['expectedC134Hash'] ?? self::C134_HASH),
            (string) ($options['expectedC134FileSha1'] ?? self::C134_SHA1),
            (string) ($options['output'] ?? $this->output),
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'operator_go_decision_confirmed' => (bool) ($options['operatorGoDecisionConfirmed'] ?? true),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C135_OPERATOR_APPROVED_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_ONLY'),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-14T00:00:00+00:00'),
            ]
        );
    }

    private function mutateC134AndExecute(callable $mutator, string $name): array
    {
        $c134 = json_decode((string) file_get_contents(self::C134_ARTIFACT), true);
        $c134 = $mutator(is_array($c134) ? $c134 : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c135-source-c134-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($c134, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'c134Artifact' => $path,
            'expectedC134Hash' => (string) ($c134['artifact_hash'] ?? ''),
            'expectedC134FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function readOutput(): array
    {
        $decoded = json_decode((string) file_get_contents($this->output), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function cleanupC135TemporaryArtifacts(): void
    {
        foreach ((array) glob('storage/app/watchlist/backtest/c135-*test.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/.tmp-c135*.json') as $file) {
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
        ];
    }
}

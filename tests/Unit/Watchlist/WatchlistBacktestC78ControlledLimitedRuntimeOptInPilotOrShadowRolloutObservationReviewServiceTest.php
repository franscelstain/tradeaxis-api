<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC78ControlledLimitedRuntimeOptInPilotOrShadowRolloutObservationReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC78ControlledLimitedRuntimeOptInPilotOrShadowRolloutObservationReviewServiceTest extends TestCase
{
    private string $output = 'storage/app/watchlist/backtest/.tmp-c78-test-output.json';
    private array $tmpFiles = [];

    protected function tearDown(): void
    {
        foreach (array_merge([$this->output], $this->tmpFiles) as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        parent::tearDown();
    }

    public function test_c78_observation_passes_primary_and_backup_when_locked_c77_and_operator_approval_match(): void
    {
        $result = $this->runService();

        $this->assertSame('C78_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OBSERVATION_REVIEW_PASSED_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertSame($result['status'], $result['reason_code']);
        $this->assertTrue($result['controlled_limited_runtime_opt_in_pilot_observation_review_executed']);
        $this->assertTrue($result['controlled_limited_runtime_opt_in_pilot_observation_review_allowed']);
        $this->assertTrue($result['controlled_limited_runtime_opt_in_pilot_observation_review_pass']);
        $this->assertTrue($result['controlled_limited_shadow_rollout_observation_review_executed']);
        $this->assertTrue($result['controlled_limited_shadow_rollout_observation_review_allowed']);
        $this->assertTrue($result['controlled_limited_shadow_rollout_observation_review_pass']);
        $this->assertFalse($result['production_ready']);
        $this->assertFalse($result['production_catalog_runtime_wired']);
        $this->assertFalse($result['controlled_opt_in_runtime_bridge_active']);
        $this->assertFalse($result['controlled_parallel_run_active']);
        $this->assertFalse($result['controlled_rollout_active']);
        $this->assertFalse($result['controlled_limited_pilot_observation_context_persisted_to_live_runtime']);
        $this->assertFalse($result['controlled_limited_shadow_observation_context_persisted_to_live_runtime']);
        $this->assertFalse($result['production_deployment_allowed']);
        $this->assertFalse($result['production_deployment_executed']);
        $this->assertFalse($result['plan_confirm_mutation_allowed']);
        $this->assertFalse($result['plan_confirm_mutated']);
        $this->assertFalse($result['plan_confirm_runtime_reads_activated_catalog']);
        $this->assertFalse($result['live_plan_confirm_rollout_allowed']);
        $this->assertFalse($result['live_plan_confirm_rollout_executed']);
        $this->assertSame('C79_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OBSERVATION_RESULT_REVIEW', $result['next_step_recommendation']);
        $this->assertSame(2, $result['next_readiness_decision']['candidate_ready_for_controlled_limited_runtime_observation_result_review_count']);
        $this->assertTrue($result['c77_lock_validation_summary']['c77_c78_readiness_count_match']);
        $this->assertTrue($result['lineage_validation_summary']['c77_to_c76_lock_match']);
        $this->assertFileExists($this->output);
    }

    public function test_c78_records_required_sections(): void
    {
        $this->runService();
        $run = $this->readOutput();

        foreach ([
            'source_artifact_locks',
            'database_dictionary_read_summary',
            'c77_lock_validation_summary',
            'lineage_validation_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'controlled_pilot_shadow_execution_candidate_scorecard',
            'controlled_pilot_shadow_execution_decision',
            'controlled_limited_pilot_shadow_observation_candidate_scorecard',
            'controlled_limited_pilot_shadow_observation_decision',
            'controlled_limited_pilot_observation_context_summary',
            'controlled_limited_shadow_observation_context_summary',
            'runtime_readiness_inspection_summary',
            'feature_flag_operator_approval_kill_switch_validation_summary',
            'rollback_and_emergency_disable_review_summary',
            'c77_proof_carry_forward_validation_summary',
            'production_mutation_safety_summary',
            'documentation_governance_summary',
            'next_readiness_decision',
            'failure_attribution_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }
    }

    public function test_c78_validates_c77_artifact_hash_and_file_sha1(): void
    {
        $fixture = $this->lockedC77Fixture();
        $hashResult = $this->execute(['c77Artifact' => $fixture['path'], 'expectedC77Hash' => '0000000000000000000000000000000000000000', 'expectedC77FileSha1' => $fixture['sha1']]);
        $this->assertSame('C78_BLOCKED_C77_ARTIFACT_LOCK_MISMATCH', $hashResult['status']);
        $this->assertFalse($hashResult['c77_hash_match']);

        $shaResult = $this->execute(['c77Artifact' => $fixture['path'], 'expectedC77Hash' => $fixture['hash'], 'expectedC77FileSha1' => '0000000000000000000000000000000000000000']);
        $this->assertSame('C78_BLOCKED_C77_FILE_SHA1_LOCK_MISMATCH', $shaResult['status']);
        $this->assertFalse($shaResult['c77_file_sha1_match']);
    }

    public function test_c78_rejects_c77_status_execution_and_readiness_mismatches(): void
    {
        $status = $this->mutateC77AndExecute(function (array $c77): array {
            $c77['status'] = 'BROKEN_STATUS';
            return $c77;
        }, 'c77-status-mismatch');
        $this->assertSame('C78_BLOCKED_C77_STATUS_OR_REASON_MISMATCH', $status['status']);

        $pilot = $this->mutateC77AndExecute(function (array $c77): array {
            $c77['controlled_runtime_opt_in_pilot_execution_review_pass'] = false;
            return $c77;
        }, 'c77-pilot-execution-false');
        $this->assertSame('C78_BLOCKED_C77_CONTROLLED_PILOT_EXECUTION_NOT_PASSED', $pilot['status']);

        $shadow = $this->mutateC77AndExecute(function (array $c77): array {
            $c77['controlled_shadow_rollout_execution_review_pass'] = false;
            return $c77;
        }, 'c77-shadow-execution-false');
        $this->assertSame('C78_BLOCKED_C77_CONTROLLED_SHADOW_EXECUTION_NOT_PASSED', $shadow['status']);

        $count = $this->mutateC77AndExecute(function (array $c77): array {
            $c77['next_readiness_decision']['candidate_ready_for_controlled_limited_runtime_observation_review_count'] = 1;
            return $c77;
        }, 'c77-c78-count');
        $this->assertSame('C78_BLOCKED_C77_C78_READINESS_COUNT_MISMATCH', $count['status']);
    }

    public function test_c78_validates_nested_c78_readiness_path_not_top_level_aliases(): void
    {
        $result = $this->mutateC77AndExecute(function (array $c77): array {
            $c77['candidate_ready_for_controlled_limited_runtime_observation_review_count'] = 0;
            $c77['next_recommendation'] = 'BROKEN_TOP_LEVEL_ALIAS';
            return $c77;
        }, 'c77-top-level-alias');

        $this->assertSame('C78_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OBSERVATION_REVIEW_PASSED_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertTrue($result['c77_lock_validation_summary']['c77_readiness_nested_path_validated']);
        $this->assertFalse($result['c77_lock_validation_summary']['top_level_alias_used_for_c77_source_validation']);
    }

    public function test_c78_rejects_c77_safety_lineage_and_candidate_scope_mismatches(): void
    {
        $safety = $this->mutateC77AndExecute(function (array $c77): array {
            $c77['controlled_pilot_execution_context_persisted_to_live_runtime'] = true;
            return $c77;
        }, 'c77-pilot-live-context');
        $this->assertSame('C78_BLOCKED_C77_CONTROLLED_PILOT_CONTEXT_ALREADY_PERSISTED_TO_LIVE_RUNTIME', $safety['status']);

        $lineage = $this->mutateC77AndExecute(function (array $c77): array {
            $c77['source_artifact_locks']['c76_source_lineage_match'] = false;
            return $c77;
        }, 'c77-lineage-mismatch');
        $this->assertSame('C78_BLOCKED_LINEAGE_LOCK_MISMATCH', $lineage['status']);

        $scope = $this->mutateC77AndExecute(function (array $c77): array {
            $c77['candidate_scope_freeze_summary']['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $c77;
        }, 'c77-scope-mismatch');
        $this->assertSame('C78_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OBSERVATION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $scope['status']);
    }

    public function test_c78_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->execute(['operatorApproved' => false]);
        $this->assertSame('C78_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OBSERVATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingOperator['status']);

        $missingReference = $this->execute(['approvalReference' => '']);
        $this->assertSame('C78_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OBSERVATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingReference['status']);
    }

    public function test_c78_candidate_scorecard_locks_e02_b01_and_a01_comparator_only(): void
    {
        $result = $this->runService();
        $scorecards = $result['controlled_pilot_shadow_execution_candidate_scorecard'];

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $scorecards[0]['candidate_code']);
        $this->assertTrue($scorecards[0]['candidate_ready_for_controlled_limited_runtime_observation_review']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $scorecards[1]['candidate_code']);
        $this->assertTrue($scorecards[1]['candidate_ready_for_controlled_limited_runtime_observation_review']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $scorecards[2]['candidate_code']);
        $this->assertSame('comparator_only', $scorecards[2]['c77_role']);
        $this->assertFalse($scorecards[2]['candidate_ready_for_controlled_limited_runtime_observation_review']);
    }

    private function runService(array $options = []): array
    {
        return $this->execute(['options' => $options]);
    }

    private function execute(array $overrides = []): array
    {
        $service = new WatchlistBacktestC78ControlledLimitedRuntimeOptInPilotOrShadowRolloutObservationReviewService();
        $fixture = $this->lockedC77Fixture();
        return $service->execute(
            (string) ($overrides['c77Artifact'] ?? $fixture['path']),
            (string) ($overrides['expectedC77Hash'] ?? $fixture['hash']),
            (string) ($overrides['expectedC77FileSha1'] ?? $fixture['sha1']),
            (string) ($overrides['output'] ?? $this->output),
            array_merge([
                'overwrite' => true,
                'operator_approved' => $overrides['operatorApproved'] ?? true,
                'approval_reference' => $overrides['approvalReference'] ?? 'C78_OPERATOR_APPROVED_OBSERVATION_REVIEW_ONLY',
            ], (array) ($overrides['options'] ?? []))
        );
    }

    private function mutateC77AndExecute(callable $mutator, string $name): array
    {
        $fixture = $this->lockedC77Fixture();
        $payload = json_decode((string) file_get_contents($fixture['path']), true);
        $payload = $mutator(is_array($payload) ? $payload : []);
        $path = 'storage/app/watchlist/backtest/.tmp-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
        return $this->execute([
            'c77Artifact' => $path,
            'expectedC77Hash' => (string) ($payload['artifact_hash'] ?? ''),
            'expectedC77FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function lockedC77Fixture(): array
    {
        $path = 'storage/app/watchlist/backtest/c77-controlled-runtime-opt-in-pilot-or-shadow-rollout-execution-review.json';
        $payload = json_decode((string) file_get_contents($path), true);
        return [
            'path' => $path,
            'hash' => (string) ($payload['artifact_hash'] ?? ''),
            'sha1' => strtoupper(sha1((string) file_get_contents($path))),
        ];
    }

    private function readOutput(): array
    {
        $decoded = json_decode((string) file_get_contents($this->output), true);
        return is_array($decoded) ? $decoded : [];
    }
}

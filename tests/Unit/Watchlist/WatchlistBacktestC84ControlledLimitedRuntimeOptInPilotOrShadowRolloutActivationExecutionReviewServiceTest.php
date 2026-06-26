<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC84ControlledLimitedRuntimeOptInPilotOrShadowRolloutActivationExecutionReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC84ControlledLimitedRuntimeOptInPilotOrShadowRolloutActivationExecutionReviewServiceTest extends TestCase
{
    private string $output = 'storage/app/watchlist/backtest/.tmp-c84-test-output.json';
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

    public function test_c84_executes_controlled_activation_record_for_primary_and_backup_when_locked_c83_and_operator_approval_match(): void
    {
        $result = $this->runService();

        $this->assertSame('C84_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_EXECUTION_REVIEW_PASSED_EXECUTED_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertSame($result['status'], $result['reason_code']);
        $this->assertTrue($result['activation_execution_review_executed']);
        $this->assertTrue($result['activation_execution_review_allowed']);
        $this->assertTrue($result['activation_execution_review_pass']);
        $this->assertTrue($result['activation_authorized']);
        $this->assertTrue($result['activation_executed']);
        $this->assertTrue($result['controlled_activation_record_created']);
        $this->assertTrue($result['primary_candidate_activation_executed']);
        $this->assertTrue($result['backup_candidate_activation_executed']);
        $this->assertFalse($result['production_ready']);
        $this->assertFalse($result['production_catalog_runtime_wired']);
        $this->assertFalse($result['controlled_opt_in_runtime_bridge_active']);
        $this->assertFalse($result['controlled_parallel_run_active']);
        $this->assertFalse($result['controlled_rollout_active']);
        $this->assertFalse($result['activation_execution_context_persisted_to_live_runtime']);
        $this->assertFalse($result['production_deployment_allowed']);
        $this->assertFalse($result['production_deployment_executed']);
        $this->assertFalse($result['plan_confirm_mutation_allowed']);
        $this->assertFalse($result['plan_confirm_mutated']);
        $this->assertFalse($result['plan_confirm_runtime_reads_activated_catalog']);
        $this->assertFalse($result['live_plan_confirm_rollout_allowed']);
        $this->assertFalse($result['live_plan_confirm_rollout_executed']);
        $this->assertSame('C85_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_REVIEW', $result['next_step_recommendation']);
        $this->assertSame(2, $result['next_readiness_decision']['candidate_ready_for_post_activation_observation_review_count']);
        $this->assertTrue($result['c83_lock_validation_summary']['c83_c84_readiness_count_match']);
        $this->assertTrue($result['lineage_validation_summary']['c83_to_c82_lock_match']);
        $this->assertTrue($result['progress_summary']['target_reached']);
        $this->assertSame('C85_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_REVIEW', $result['planned_next_summary']['planned_next_review']);
        $this->assertFileExists($this->output);
    }

    public function test_c84_records_required_sections(): void
    {
        $this->runService();
        $run = $this->readOutput();

        foreach ([
            'source_artifact_locks',
            'database_dictionary_read_summary',
            'c83_lock_validation_summary',
            'lineage_validation_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'activation_execution_decision',
            'activation_execution_candidate_scorecard',
            'activation_execution_context_summary',
            'runtime_readiness_inspection_summary',
            'feature_flag_operator_approval_kill_switch_validation_summary',
            'rollback_and_emergency_disable_review_summary',
            'c83_authorization_carry_forward_validation_summary',
            'activation_execution_governance_summary',
            'production_mutation_safety_summary',
            'documentation_governance_summary',
            'next_readiness_decision',
            'failure_attribution_summary',
            'progress_summary',
            'planned_next_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }
    }

    public function test_c84_validates_c83_artifact_hash_and_file_sha1(): void
    {
        $fixture = $this->lockedC83Fixture();
        $hashResult = $this->execute(['c83Artifact' => $fixture['path'], 'expectedC83Hash' => '0000000000000000000000000000000000000000', 'expectedC83FileSha1' => $fixture['sha1']]);
        $this->assertSame('C84_BLOCKED_C83_ARTIFACT_LOCK_MISMATCH', $hashResult['status']);
        $this->assertFalse($hashResult['c83_hash_match']);

        $shaResult = $this->execute(['c83Artifact' => $fixture['path'], 'expectedC83Hash' => $fixture['hash'], 'expectedC83FileSha1' => '0000000000000000000000000000000000000000']);
        $this->assertSame('C84_BLOCKED_C83_FILE_SHA1_LOCK_MISMATCH', $shaResult['status']);
        $this->assertFalse($shaResult['c83_file_sha1_match']);
    }

    public function test_c84_rejects_c83_status_authorization_execution_and_readiness_mismatches(): void
    {
        $status = $this->mutateC83AndExecute(function (array $c83): array {
            $c83['status'] = 'BROKEN_STATUS';
            return $c83;
        }, 'c83-status-mismatch');
        $this->assertSame('C84_BLOCKED_C83_STATUS_OR_REASON_MISMATCH', $status['status']);

        $authorization = $this->mutateC83AndExecute(function (array $c83): array {
            $c83['activation_authorized'] = false;
            return $c83;
        }, 'c83-authorization-false');
        $this->assertSame('C84_BLOCKED_C83_ACTIVATION_NOT_AUTHORIZED', $authorization['status']);

        $alreadyExecuted = $this->mutateC83AndExecute(function (array $c83): array {
            $c83['activation_executed'] = true;
            return $c83;
        }, 'c83-already-executed');
        $this->assertSame('C84_BLOCKED_C83_ACTIVATION_ALREADY_EXECUTED', $alreadyExecuted['status']);

        $count = $this->mutateC83AndExecute(function (array $c83): array {
            $c83['next_readiness_decision']['candidate_ready_for_activation_execution_review_count'] = 1;
            return $c83;
        }, 'c83-c84-count');
        $this->assertSame('C84_BLOCKED_C83_C84_READINESS_COUNT_MISMATCH', $count['status']);
    }

    public function test_c84_validates_nested_c84_readiness_path_not_top_level_aliases(): void
    {
        $result = $this->mutateC83AndExecute(function (array $c83): array {
            $c83['candidate_ready_for_activation_execution_review_count'] = 0;
            $c83['next_recommendation'] = 'BROKEN_TOP_LEVEL_ALIAS';
            return $c83;
        }, 'c83-top-level-alias');

        $this->assertSame('C84_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_EXECUTION_REVIEW_PASSED_EXECUTED_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertTrue($result['c83_lock_validation_summary']['c83_readiness_nested_path_validated']);
        $this->assertFalse($result['c83_lock_validation_summary']['top_level_alias_used_for_c83_source_validation']);
    }

    public function test_c84_rejects_c83_safety_lineage_candidate_scope_and_execution_confirmation_mismatches(): void
    {
        $safety = $this->mutateC83AndExecute(function (array $c83): array {
            $c83['production_catalog_runtime_wired'] = true;
            return $c83;
        }, 'c83-runtime-wired');
        $this->assertSame('C84_BLOCKED_C83_RUNTIME_ALREADY_WIRED', $safety['status']);

        $lineage = $this->mutateC83AndExecute(function (array $c83): array {
            $c83['source_artifact_locks']['c82_source_lineage_match'] = false;
            return $c83;
        }, 'c83-lineage-mismatch');
        $this->assertSame('C84_BLOCKED_LINEAGE_LOCK_MISMATCH', $lineage['status']);

        $scope = $this->mutateC83AndExecute(function (array $c83): array {
            $c83['candidate_scope_freeze_summary']['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $c83;
        }, 'c83-scope-mismatch');
        $this->assertSame('C84_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_EXECUTION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $scope['status']);

        $notConfirmed = $this->execute(['options' => ['activation_execution_confirmed' => false]]);
        $this->assertSame('C84_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_EXECUTION_REVIEW_REJECTED_EXECUTION_NOT_CONFIRMED', $notConfirmed['status']);
    }

    public function test_c84_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->execute(['operatorApproved' => false]);
        $this->assertSame('C84_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_EXECUTION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingOperator['status']);

        $missingReference = $this->execute(['approvalReference' => '']);
        $this->assertSame('C84_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_EXECUTION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingReference['status']);
    }

    public function test_c84_candidate_scorecard_locks_e02_b01_and_a01_comparator_only(): void
    {
        $result = $this->runService();
        $scorecards = $result['activation_execution_candidate_scorecard'];

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $scorecards[0]['candidate_code']);
        $this->assertSame('primary_activation_execution_candidate', $scorecards[0]['c84_role']);
        $this->assertTrue($scorecards[0]['candidate_ready_for_post_activation_observation_review']);
        $this->assertTrue($scorecards[0]['activation_executed']);
        $this->assertTrue($scorecards[0]['controlled_activation_record_created']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $scorecards[1]['candidate_code']);
        $this->assertSame('backup_activation_execution_candidate', $scorecards[1]['c84_role']);
        $this->assertTrue($scorecards[1]['candidate_ready_for_post_activation_observation_review']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $scorecards[2]['candidate_code']);
        $this->assertSame('comparator_only', $scorecards[2]['c84_role']);
        $this->assertFalse($scorecards[2]['candidate_ready_for_post_activation_observation_review']);
        $this->assertFalse($scorecards[2]['activation_executed']);
        $this->assertFalse($scorecards[2]['controlled_activation_record_created']);
    }

    private function runService(array $options = []): array
    {
        return $this->execute(['options' => $options]);
    }

    private function execute(array $overrides = []): array
    {
        $service = new WatchlistBacktestC84ControlledLimitedRuntimeOptInPilotOrShadowRolloutActivationExecutionReviewService();
        $fixture = $this->lockedC83Fixture();
        return $service->execute(
            (string) ($overrides['c83Artifact'] ?? $fixture['path']),
            (string) ($overrides['expectedC83Hash'] ?? $fixture['hash']),
            (string) ($overrides['expectedC83FileSha1'] ?? $fixture['sha1']),
            (string) ($overrides['output'] ?? $this->output),
            array_merge([
                'overwrite' => true,
                'operator_approved' => $overrides['operatorApproved'] ?? true,
                'approval_reference' => $overrides['approvalReference'] ?? 'C84_OPERATOR_APPROVED_ACTIVATION_EXECUTION_REVIEW_ONLY',
            ], (array) ($overrides['options'] ?? []))
        );
    }

    private function mutateC83AndExecute(callable $mutator, string $name): array
    {
        $fixture = $this->lockedC83Fixture();
        $payload = json_decode((string) file_get_contents($fixture['path']), true);
        $payload = $mutator(is_array($payload) ? $payload : []);
        $path = 'storage/app/watchlist/backtest/.tmp-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
        return $this->execute([
            'c83Artifact' => $path,
            'expectedC83Hash' => (string) ($payload['artifact_hash'] ?? ''),
            'expectedC83FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function lockedC83Fixture(): array
    {
        $path = 'storage/app/watchlist/backtest/c83-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-activation-authorization-review.json';
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

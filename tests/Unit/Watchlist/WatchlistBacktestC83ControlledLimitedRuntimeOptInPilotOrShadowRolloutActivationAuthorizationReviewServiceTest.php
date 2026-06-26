<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC83ControlledLimitedRuntimeOptInPilotOrShadowRolloutActivationAuthorizationReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC83ControlledLimitedRuntimeOptInPilotOrShadowRolloutActivationAuthorizationReviewServiceTest extends TestCase
{
    private string $output = 'storage/app/watchlist/backtest/.tmp-c83-test-output.json';
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

    public function test_c83_authorizes_activation_for_primary_and_backup_when_locked_c82_and_operator_approval_match(): void
    {
        $result = $this->runService();

        $this->assertSame('C83_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_AUTHORIZATION_REVIEW_PASSED_AUTHORIZED_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertSame($result['status'], $result['reason_code']);
        $this->assertTrue($result['activation_authorization_review_executed']);
        $this->assertTrue($result['activation_authorization_review_allowed']);
        $this->assertTrue($result['activation_authorization_review_pass']);
        $this->assertTrue($result['activation_authorized']);
        $this->assertTrue($result['primary_candidate_activation_authorized']);
        $this->assertTrue($result['backup_candidate_activation_authorized']);
        $this->assertFalse($result['activation_executed']);
        $this->assertFalse($result['production_ready']);
        $this->assertFalse($result['production_catalog_runtime_wired']);
        $this->assertFalse($result['controlled_opt_in_runtime_bridge_active']);
        $this->assertFalse($result['controlled_parallel_run_active']);
        $this->assertFalse($result['controlled_rollout_active']);
        $this->assertFalse($result['activation_authorization_context_persisted_to_live_runtime']);
        $this->assertFalse($result['production_deployment_allowed']);
        $this->assertFalse($result['production_deployment_executed']);
        $this->assertFalse($result['plan_confirm_mutation_allowed']);
        $this->assertFalse($result['plan_confirm_mutated']);
        $this->assertFalse($result['plan_confirm_runtime_reads_activated_catalog']);
        $this->assertFalse($result['live_plan_confirm_rollout_allowed']);
        $this->assertFalse($result['live_plan_confirm_rollout_executed']);
        $this->assertSame('C84_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_EXECUTION_REVIEW', $result['next_step_recommendation']);
        $this->assertSame(2, $result['next_readiness_decision']['candidate_ready_for_activation_execution_review_count']);
        $this->assertTrue($result['c82_lock_validation_summary']['c82_c83_readiness_count_match']);
        $this->assertTrue($result['lineage_validation_summary']['c82_to_c81_lock_match']);
        $this->assertTrue($result['progress_summary']['target_reached']);
        $this->assertSame('C84_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_EXECUTION_REVIEW', $result['planned_next_summary']['planned_next_review']);
        $this->assertFileExists($this->output);
    }

    public function test_c83_records_required_sections(): void
    {
        $this->runService();
        $run = $this->readOutput();

        foreach ([
            'source_artifact_locks',
            'database_dictionary_read_summary',
            'c82_lock_validation_summary',
            'lineage_validation_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'activation_authorization_decision',
            'activation_authorization_candidate_scorecard',
            'activation_authorization_context_summary',
            'runtime_readiness_inspection_summary',
            'feature_flag_operator_approval_kill_switch_validation_summary',
            'rollback_and_emergency_disable_review_summary',
            'c82_boundary_carry_forward_validation_summary',
            'activation_authorization_governance_summary',
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

    public function test_c83_validates_c82_artifact_hash_and_file_sha1(): void
    {
        $fixture = $this->lockedC82Fixture();
        $hashResult = $this->execute(['c82Artifact' => $fixture['path'], 'expectedC82Hash' => '0000000000000000000000000000000000000000', 'expectedC82FileSha1' => $fixture['sha1']]);
        $this->assertSame('C83_BLOCKED_C82_ARTIFACT_LOCK_MISMATCH', $hashResult['status']);
        $this->assertFalse($hashResult['c82_hash_match']);

        $shaResult = $this->execute(['c82Artifact' => $fixture['path'], 'expectedC82Hash' => $fixture['hash'], 'expectedC82FileSha1' => '0000000000000000000000000000000000000000']);
        $this->assertSame('C83_BLOCKED_C82_FILE_SHA1_LOCK_MISMATCH', $shaResult['status']);
        $this->assertFalse($shaResult['c82_file_sha1_match']);
    }

    public function test_c83_rejects_c82_status_boundary_and_readiness_mismatches(): void
    {
        $status = $this->mutateC82AndExecute(function (array $c82): array {
            $c82['status'] = 'BROKEN_STATUS';
            return $c82;
        }, 'c82-status-mismatch');
        $this->assertSame('C83_BLOCKED_C82_STATUS_OR_REASON_MISMATCH', $status['status']);

        $boundary = $this->mutateC82AndExecute(function (array $c82): array {
            $c82['pre_activation_boundary_cleared'] = false;
            return $c82;
        }, 'c82-boundary-false');
        $this->assertSame('C83_BLOCKED_C82_PRE_ACTIVATION_BOUNDARY_NOT_CLEARED', $boundary['status']);

        $primary = $this->mutateC82AndExecute(function (array $c82): array {
            $c82['primary_candidate_boundary_cleared'] = false;
            return $c82;
        }, 'c82-primary-boundary-false');
        $this->assertSame('C83_BLOCKED_C82_PRIMARY_BOUNDARY_NOT_CLEARED', $primary['status']);

        $count = $this->mutateC82AndExecute(function (array $c82): array {
            $c82['next_readiness_decision']['candidate_ready_for_activation_authorization_review_count'] = 1;
            return $c82;
        }, 'c82-c83-count');
        $this->assertSame('C83_BLOCKED_C82_C83_READINESS_COUNT_MISMATCH', $count['status']);
    }

    public function test_c83_validates_nested_c83_readiness_path_not_top_level_aliases(): void
    {
        $result = $this->mutateC82AndExecute(function (array $c82): array {
            $c82['candidate_ready_for_activation_authorization_review_count'] = 0;
            $c82['next_recommendation'] = 'BROKEN_TOP_LEVEL_ALIAS';
            return $c82;
        }, 'c82-top-level-alias');

        $this->assertSame('C83_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_AUTHORIZATION_REVIEW_PASSED_AUTHORIZED_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertTrue($result['c82_lock_validation_summary']['c82_readiness_nested_path_validated']);
        $this->assertFalse($result['c82_lock_validation_summary']['top_level_alias_used_for_c82_source_validation']);
    }

    public function test_c83_rejects_c82_safety_lineage_candidate_scope_and_authorization_confirmation_mismatches(): void
    {
        $safety = $this->mutateC82AndExecute(function (array $c82): array {
            $c82['activation_authorized'] = true;
            return $c82;
        }, 'c82-already-authorized');
        $this->assertSame('C83_BLOCKED_C82_ACTIVATION_ALREADY_AUTHORIZED', $safety['status']);

        $lineage = $this->mutateC82AndExecute(function (array $c82): array {
            $c82['source_artifact_locks']['c81_source_lineage_match'] = false;
            return $c82;
        }, 'c82-lineage-mismatch');
        $this->assertSame('C83_BLOCKED_LINEAGE_LOCK_MISMATCH', $lineage['status']);

        $scope = $this->mutateC82AndExecute(function (array $c82): array {
            $c82['candidate_scope_freeze_summary']['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $c82;
        }, 'c82-scope-mismatch');
        $this->assertSame('C83_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_AUTHORIZATION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $scope['status']);

        $notConfirmed = $this->execute(['options' => ['activation_authorization_confirmed' => false]]);
        $this->assertSame('C83_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_AUTHORIZATION_REVIEW_REJECTED_AUTHORIZATION_NOT_CONFIRMED', $notConfirmed['status']);
    }

    public function test_c83_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->execute(['operatorApproved' => false]);
        $this->assertSame('C83_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_AUTHORIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingOperator['status']);

        $missingReference = $this->execute(['approvalReference' => '']);
        $this->assertSame('C83_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_AUTHORIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingReference['status']);
    }

    public function test_c83_candidate_scorecard_locks_e02_b01_and_a01_comparator_only(): void
    {
        $result = $this->runService();
        $scorecards = $result['activation_authorization_candidate_scorecard'];

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $scorecards[0]['candidate_code']);
        $this->assertTrue($scorecards[0]['candidate_ready_for_activation_execution_review']);
        $this->assertTrue($scorecards[0]['activation_authorized']);
        $this->assertFalse($scorecards[0]['activation_executed']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $scorecards[1]['candidate_code']);
        $this->assertTrue($scorecards[1]['candidate_ready_for_activation_execution_review']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $scorecards[2]['candidate_code']);
        $this->assertSame('comparator_only', $scorecards[2]['c83_role']);
        $this->assertFalse($scorecards[2]['candidate_ready_for_activation_execution_review']);
        $this->assertFalse($scorecards[2]['activation_authorized']);
    }

    private function runService(array $options = []): array
    {
        return $this->execute(['options' => $options]);
    }

    private function execute(array $overrides = []): array
    {
        $service = new WatchlistBacktestC83ControlledLimitedRuntimeOptInPilotOrShadowRolloutActivationAuthorizationReviewService();
        $fixture = $this->lockedC82Fixture();
        return $service->execute(
            (string) ($overrides['c82Artifact'] ?? $fixture['path']),
            (string) ($overrides['expectedC82Hash'] ?? $fixture['hash']),
            (string) ($overrides['expectedC82FileSha1'] ?? $fixture['sha1']),
            (string) ($overrides['output'] ?? $this->output),
            array_merge([
                'overwrite' => true,
                'operator_approved' => $overrides['operatorApproved'] ?? true,
                'approval_reference' => $overrides['approvalReference'] ?? 'C83_OPERATOR_APPROVED_ACTIVATION_AUTHORIZATION_REVIEW_ONLY',
            ], (array) ($overrides['options'] ?? []))
        );
    }

    private function mutateC82AndExecute(callable $mutator, string $name): array
    {
        $fixture = $this->lockedC82Fixture();
        $payload = json_decode((string) file_get_contents($fixture['path']), true);
        $payload = $mutator(is_array($payload) ? $payload : []);
        $path = 'storage/app/watchlist/backtest/.tmp-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
        return $this->execute([
            'c82Artifact' => $path,
            'expectedC82Hash' => (string) ($payload['artifact_hash'] ?? ''),
            'expectedC82FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function lockedC82Fixture(): array
    {
        $path = 'storage/app/watchlist/backtest/c82-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-pre-activation-boundary-review.json';
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

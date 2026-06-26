<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC82ControlledLimitedRuntimeOptInPilotOrShadowRolloutPreActivationBoundaryReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC82ControlledLimitedRuntimeOptInPilotOrShadowRolloutPreActivationBoundaryReviewServiceTest extends TestCase
{
    private string $output = 'storage/app/watchlist/backtest/.tmp-c82-test-output.json';
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

    public function test_c82_clears_pre_activation_boundary_for_primary_and_backup_when_locked_c81_and_operator_approval_match(): void
    {
        $result = $this->runService();

        $this->assertSame('C82_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PRE_ACTIVATION_BOUNDARY_REVIEW_PASSED_BOUNDARY_CLEARED_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertSame($result['status'], $result['reason_code']);
        $this->assertTrue($result['pre_activation_boundary_review_executed']);
        $this->assertTrue($result['pre_activation_boundary_review_allowed']);
        $this->assertTrue($result['pre_activation_boundary_review_pass']);
        $this->assertTrue($result['pre_activation_boundary_cleared']);
        $this->assertTrue($result['primary_candidate_boundary_cleared']);
        $this->assertTrue($result['backup_candidate_boundary_cleared']);
        $this->assertFalse($result['production_ready']);
        $this->assertFalse($result['activation_authorized']);
        $this->assertFalse($result['production_catalog_runtime_wired']);
        $this->assertFalse($result['controlled_opt_in_runtime_bridge_active']);
        $this->assertFalse($result['controlled_parallel_run_active']);
        $this->assertFalse($result['controlled_rollout_active']);
        $this->assertFalse($result['pre_activation_boundary_context_persisted_to_live_runtime']);
        $this->assertFalse($result['production_deployment_allowed']);
        $this->assertFalse($result['production_deployment_executed']);
        $this->assertFalse($result['plan_confirm_mutation_allowed']);
        $this->assertFalse($result['plan_confirm_mutated']);
        $this->assertFalse($result['plan_confirm_runtime_reads_activated_catalog']);
        $this->assertFalse($result['live_plan_confirm_rollout_allowed']);
        $this->assertFalse($result['live_plan_confirm_rollout_executed']);
        $this->assertSame('C83_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_AUTHORIZATION_REVIEW', $result['next_step_recommendation']);
        $this->assertSame(2, $result['next_readiness_decision']['candidate_ready_for_activation_authorization_review_count']);
        $this->assertTrue($result['c81_lock_validation_summary']['c81_c82_readiness_count_match']);
        $this->assertTrue($result['lineage_validation_summary']['c81_to_c80_lock_match']);
        $this->assertTrue($result['progress_summary']['target_reached']);
        $this->assertSame('C83_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_AUTHORIZATION_REVIEW', $result['planned_next_summary']['planned_next_review']);
        $this->assertFileExists($this->output);
    }

    public function test_c82_records_required_sections(): void
    {
        $this->runService();
        $run = $this->readOutput();

        foreach ([
            'source_artifact_locks',
            'database_dictionary_read_summary',
            'c81_lock_validation_summary',
            'lineage_validation_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'pre_activation_boundary_decision',
            'pre_activation_boundary_candidate_scorecard',
            'pre_activation_boundary_context_summary',
            'runtime_readiness_inspection_summary',
            'feature_flag_operator_approval_kill_switch_validation_summary',
            'rollback_and_emergency_disable_review_summary',
            'c81_go_decision_carry_forward_validation_summary',
            'pre_activation_boundary_governance_summary',
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

    public function test_c82_validates_c81_artifact_hash_and_file_sha1(): void
    {
        $fixture = $this->lockedC81Fixture();
        $hashResult = $this->execute(['c81Artifact' => $fixture['path'], 'expectedC81Hash' => '0000000000000000000000000000000000000000', 'expectedC81FileSha1' => $fixture['sha1']]);
        $this->assertSame('C82_BLOCKED_C81_ARTIFACT_LOCK_MISMATCH', $hashResult['status']);
        $this->assertFalse($hashResult['c81_hash_match']);

        $shaResult = $this->execute(['c81Artifact' => $fixture['path'], 'expectedC81Hash' => $fixture['hash'], 'expectedC81FileSha1' => '0000000000000000000000000000000000000000']);
        $this->assertSame('C82_BLOCKED_C81_FILE_SHA1_LOCK_MISMATCH', $shaResult['status']);
        $this->assertFalse($shaResult['c81_file_sha1_match']);
    }

    public function test_c82_rejects_c81_status_finalization_and_readiness_mismatches(): void
    {
        $status = $this->mutateC81AndExecute(function (array $c81): array {
            $c81['status'] = 'BROKEN_STATUS';
            return $c81;
        }, 'c81-status-mismatch');
        $this->assertSame('C82_BLOCKED_C81_STATUS_OR_REASON_MISMATCH', $status['status']);

        $finalization = $this->mutateC81AndExecute(function (array $c81): array {
            $c81['go_decision_finalization_review_pass'] = false;
            return $c81;
        }, 'c81-finalization-false');
        $this->assertSame('C82_BLOCKED_C81_GO_DECISION_FINALIZATION_NOT_PASSED', $finalization['status']);

        $decision = $this->mutateC81AndExecute(function (array $c81): array {
            $c81['finalized_go_decision'] = 'NOT_FINALIZED';
            return $c81;
        }, 'c81-not-finalized');
        $this->assertSame('C82_BLOCKED_C81_FINALIZED_GO_DECISION_MISMATCH', $decision['status']);

        $count = $this->mutateC81AndExecute(function (array $c81): array {
            $c81['next_readiness_decision']['candidate_ready_for_pre_activation_boundary_review_count'] = 1;
            return $c81;
        }, 'c81-c82-count');
        $this->assertSame('C82_BLOCKED_C81_C82_READINESS_COUNT_MISMATCH', $count['status']);
    }

    public function test_c82_validates_nested_c82_readiness_path_not_top_level_aliases(): void
    {
        $result = $this->mutateC81AndExecute(function (array $c81): array {
            $c81['candidate_ready_for_pre_activation_boundary_review_count'] = 0;
            $c81['next_recommendation'] = 'BROKEN_TOP_LEVEL_ALIAS';
            return $c81;
        }, 'c81-top-level-alias');

        $this->assertSame('C82_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PRE_ACTIVATION_BOUNDARY_REVIEW_PASSED_BOUNDARY_CLEARED_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertTrue($result['c81_lock_validation_summary']['c81_readiness_nested_path_validated']);
        $this->assertFalse($result['c81_lock_validation_summary']['top_level_alias_used_for_c81_source_validation']);
    }

    public function test_c82_rejects_c81_safety_lineage_candidate_scope_and_boundary_confirmation_mismatches(): void
    {
        $safety = $this->mutateC81AndExecute(function (array $c81): array {
            $c81['go_decision_finalization_context_persisted_to_live_runtime'] = true;
            return $c81;
        }, 'c81-finalization-live-context');
        $this->assertSame('C82_BLOCKED_C81_GO_DECISION_FINALIZATION_CONTEXT_ALREADY_PERSISTED_TO_LIVE_RUNTIME', $safety['status']);

        $lineage = $this->mutateC81AndExecute(function (array $c81): array {
            $c81['source_artifact_locks']['c80_source_lineage_match'] = false;
            return $c81;
        }, 'c81-lineage-mismatch');
        $this->assertSame('C82_BLOCKED_LINEAGE_LOCK_MISMATCH', $lineage['status']);

        $scope = $this->mutateC81AndExecute(function (array $c81): array {
            $c81['candidate_scope_freeze_summary']['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $c81;
        }, 'c81-scope-mismatch');
        $this->assertSame('C82_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PRE_ACTIVATION_BOUNDARY_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $scope['status']);

        $notConfirmed = $this->execute(['options' => ['pre_activation_boundary_confirmed' => false]]);
        $this->assertSame('C82_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PRE_ACTIVATION_BOUNDARY_REVIEW_REJECTED_BOUNDARY_NOT_CONFIRMED', $notConfirmed['status']);
    }

    public function test_c82_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->execute(['operatorApproved' => false]);
        $this->assertSame('C82_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PRE_ACTIVATION_BOUNDARY_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingOperator['status']);

        $missingReference = $this->execute(['approvalReference' => '']);
        $this->assertSame('C82_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PRE_ACTIVATION_BOUNDARY_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingReference['status']);
    }

    public function test_c82_candidate_scorecard_locks_e02_b01_and_a01_comparator_only(): void
    {
        $result = $this->runService();
        $scorecards = $result['pre_activation_boundary_candidate_scorecard'];

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $scorecards[0]['candidate_code']);
        $this->assertTrue($scorecards[0]['candidate_ready_for_activation_authorization_review']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $scorecards[1]['candidate_code']);
        $this->assertTrue($scorecards[1]['candidate_ready_for_activation_authorization_review']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $scorecards[2]['candidate_code']);
        $this->assertSame('comparator_only', $scorecards[2]['c82_role']);
        $this->assertFalse($scorecards[2]['candidate_ready_for_activation_authorization_review']);
    }

    private function runService(array $options = []): array
    {
        return $this->execute(['options' => $options]);
    }

    private function execute(array $overrides = []): array
    {
        $service = new WatchlistBacktestC82ControlledLimitedRuntimeOptInPilotOrShadowRolloutPreActivationBoundaryReviewService();
        $fixture = $this->lockedC81Fixture();
        return $service->execute(
            (string) ($overrides['c81Artifact'] ?? $fixture['path']),
            (string) ($overrides['expectedC81Hash'] ?? $fixture['hash']),
            (string) ($overrides['expectedC81FileSha1'] ?? $fixture['sha1']),
            (string) ($overrides['output'] ?? $this->output),
            array_merge([
                'overwrite' => true,
                'operator_approved' => $overrides['operatorApproved'] ?? true,
                'approval_reference' => $overrides['approvalReference'] ?? 'C82_OPERATOR_APPROVED_PRE_ACTIVATION_BOUNDARY_REVIEW_ONLY',
            ], (array) ($overrides['options'] ?? []))
        );
    }

    private function mutateC81AndExecute(callable $mutator, string $name): array
    {
        $fixture = $this->lockedC81Fixture();
        $payload = json_decode((string) file_get_contents($fixture['path']), true);
        $payload = $mutator(is_array($payload) ? $payload : []);
        $path = 'storage/app/watchlist/backtest/.tmp-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
        return $this->execute([
            'c81Artifact' => $path,
            'expectedC81Hash' => (string) ($payload['artifact_hash'] ?? ''),
            'expectedC81FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function lockedC81Fixture(): array
    {
        $path = 'storage/app/watchlist/backtest/c81-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-go-decision-finalization-review.json';
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

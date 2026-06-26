<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC81ControlledLimitedRuntimeOptInPilotOrShadowRolloutGoDecisionFinalizationReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC81ControlledLimitedRuntimeOptInPilotOrShadowRolloutGoDecisionFinalizationReviewServiceTest extends TestCase
{
    private string $output = 'storage/app/watchlist/backtest/.tmp-c81-test-output.json';
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

    public function test_c81_finalizes_go_for_primary_and_backup_when_locked_c80_and_operator_approval_match(): void
    {
        $result = $this->runService();

        $this->assertSame('C81_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_GO_DECISION_FINALIZATION_REVIEW_PASSED_FINALIZED_GO_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertSame($result['status'], $result['reason_code']);
        $this->assertTrue($result['go_decision_finalization_review_executed']);
        $this->assertTrue($result['go_decision_finalization_review_allowed']);
        $this->assertTrue($result['go_decision_finalization_review_pass']);
        $this->assertTrue($result['go_decision_finalized']);
        $this->assertSame('FINALIZED_GO', $result['finalized_go_decision']);
        $this->assertSame('GO', $result['operator_go_decision']);
        $this->assertTrue($result['primary_candidate_go_finalized']);
        $this->assertTrue($result['backup_candidate_go_finalized']);
        $this->assertFalse($result['production_ready']);
        $this->assertFalse($result['production_catalog_runtime_wired']);
        $this->assertFalse($result['controlled_opt_in_runtime_bridge_active']);
        $this->assertFalse($result['controlled_parallel_run_active']);
        $this->assertFalse($result['controlled_rollout_active']);
        $this->assertFalse($result['go_decision_finalization_context_persisted_to_live_runtime']);
        $this->assertFalse($result['production_deployment_allowed']);
        $this->assertFalse($result['production_deployment_executed']);
        $this->assertFalse($result['plan_confirm_mutation_allowed']);
        $this->assertFalse($result['plan_confirm_mutated']);
        $this->assertFalse($result['plan_confirm_runtime_reads_activated_catalog']);
        $this->assertFalse($result['live_plan_confirm_rollout_allowed']);
        $this->assertFalse($result['live_plan_confirm_rollout_executed']);
        $this->assertSame('C82_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PRE_ACTIVATION_BOUNDARY_REVIEW', $result['next_step_recommendation']);
        $this->assertSame(2, $result['next_readiness_decision']['candidate_ready_for_pre_activation_boundary_review_count']);
        $this->assertTrue($result['c80_lock_validation_summary']['c80_c81_readiness_count_match']);
        $this->assertTrue($result['lineage_validation_summary']['c80_to_c79_lock_match']);
        $this->assertTrue($result['progress_summary']['target_reached']);
        $this->assertSame('C82_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PRE_ACTIVATION_BOUNDARY_REVIEW', $result['planned_next_summary']['planned_next_review']);
        $this->assertFileExists($this->output);
    }

    public function test_c81_records_required_sections(): void
    {
        $this->runService();
        $run = $this->readOutput();

        foreach ([
            'source_artifact_locks',
            'database_dictionary_read_summary',
            'c80_lock_validation_summary',
            'lineage_validation_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'go_decision_finalization_decision',
            'go_decision_finalization_candidate_scorecard',
            'go_decision_finalization_context_summary',
            'runtime_readiness_inspection_summary',
            'feature_flag_operator_approval_kill_switch_validation_summary',
            'rollback_and_emergency_disable_review_summary',
            'c80_go_no_go_carry_forward_validation_summary',
            'go_decision_finalization_governance_summary',
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

    public function test_c81_validates_c80_artifact_hash_and_file_sha1(): void
    {
        $fixture = $this->lockedC80Fixture();
        $hashResult = $this->execute(['c80Artifact' => $fixture['path'], 'expectedC80Hash' => '0000000000000000000000000000000000000000', 'expectedC80FileSha1' => $fixture['sha1']]);
        $this->assertSame('C81_BLOCKED_C80_ARTIFACT_LOCK_MISMATCH', $hashResult['status']);
        $this->assertFalse($hashResult['c80_hash_match']);

        $shaResult = $this->execute(['c80Artifact' => $fixture['path'], 'expectedC80Hash' => $fixture['hash'], 'expectedC80FileSha1' => '0000000000000000000000000000000000000000']);
        $this->assertSame('C81_BLOCKED_C80_FILE_SHA1_LOCK_MISMATCH', $shaResult['status']);
        $this->assertFalse($shaResult['c80_file_sha1_match']);
    }

    public function test_c81_rejects_c80_status_go_and_readiness_mismatches(): void
    {
        $status = $this->mutateC80AndExecute(function (array $c80): array {
            $c80['status'] = 'BROKEN_STATUS';
            return $c80;
        }, 'c80-status-mismatch');
        $this->assertSame('C81_BLOCKED_C80_STATUS_OR_REASON_MISMATCH', $status['status']);

        $goNoGo = $this->mutateC80AndExecute(function (array $c80): array {
            $c80['operator_go_no_go_review_pass'] = false;
            return $c80;
        }, 'c80-go-no-go-false');
        $this->assertSame('C81_BLOCKED_C80_OPERATOR_GO_NO_GO_REVIEW_NOT_PASSED', $goNoGo['status']);

        $decision = $this->mutateC80AndExecute(function (array $c80): array {
            $c80['operator_go_decision'] = 'NO_GO';
            return $c80;
        }, 'c80-decision-no-go');
        $this->assertSame('C81_BLOCKED_C80_OPERATOR_GO_DECISION_NOT_GO', $decision['status']);

        $count = $this->mutateC80AndExecute(function (array $c80): array {
            $c80['next_readiness_decision']['candidate_ready_for_go_decision_finalization_review_count'] = 1;
            return $c80;
        }, 'c80-c81-count');
        $this->assertSame('C81_BLOCKED_C80_C81_READINESS_COUNT_MISMATCH', $count['status']);
    }

    public function test_c81_validates_nested_c81_readiness_path_not_top_level_aliases(): void
    {
        $result = $this->mutateC80AndExecute(function (array $c80): array {
            $c80['candidate_ready_for_go_decision_finalization_review_count'] = 0;
            $c80['next_recommendation'] = 'BROKEN_TOP_LEVEL_ALIAS';
            return $c80;
        }, 'c80-top-level-alias');

        $this->assertSame('C81_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_GO_DECISION_FINALIZATION_REVIEW_PASSED_FINALIZED_GO_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertTrue($result['c80_lock_validation_summary']['c80_readiness_nested_path_validated']);
        $this->assertFalse($result['c80_lock_validation_summary']['top_level_alias_used_for_c80_source_validation']);
    }

    public function test_c81_rejects_c80_safety_lineage_candidate_scope_and_finalization_confirmation_mismatches(): void
    {
        $safety = $this->mutateC80AndExecute(function (array $c80): array {
            $c80['operator_go_no_go_context_persisted_to_live_runtime'] = true;
            return $c80;
        }, 'c80-go-live-context');
        $this->assertSame('C81_BLOCKED_C80_OPERATOR_GO_NO_GO_CONTEXT_ALREADY_PERSISTED_TO_LIVE_RUNTIME', $safety['status']);

        $lineage = $this->mutateC80AndExecute(function (array $c80): array {
            $c80['source_artifact_locks']['c79_source_lineage_match'] = false;
            return $c80;
        }, 'c80-lineage-mismatch');
        $this->assertSame('C81_BLOCKED_LINEAGE_LOCK_MISMATCH', $lineage['status']);

        $scope = $this->mutateC80AndExecute(function (array $c80): array {
            $c80['candidate_scope_freeze_summary']['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $c80;
        }, 'c80-scope-mismatch');
        $this->assertSame('C81_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_GO_DECISION_FINALIZATION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $scope['status']);

        $notFinalized = $this->execute(['options' => ['go_decision_finalized_confirmed' => false]]);
        $this->assertSame('C81_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_GO_DECISION_FINALIZATION_REVIEW_REJECTED_GO_DECISION_FINALIZATION_NOT_CONFIRMED', $notFinalized['status']);
    }

    public function test_c81_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->execute(['operatorApproved' => false]);
        $this->assertSame('C81_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_GO_DECISION_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingOperator['status']);

        $missingReference = $this->execute(['approvalReference' => '']);
        $this->assertSame('C81_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_GO_DECISION_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingReference['status']);
    }

    public function test_c81_candidate_scorecard_locks_e02_b01_and_a01_comparator_only(): void
    {
        $result = $this->runService();
        $scorecards = $result['go_decision_finalization_candidate_scorecard'];

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $scorecards[0]['candidate_code']);
        $this->assertTrue($scorecards[0]['candidate_ready_for_pre_activation_boundary_review']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $scorecards[1]['candidate_code']);
        $this->assertTrue($scorecards[1]['candidate_ready_for_pre_activation_boundary_review']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $scorecards[2]['candidate_code']);
        $this->assertSame('comparator_only', $scorecards[2]['c81_role']);
        $this->assertFalse($scorecards[2]['candidate_ready_for_pre_activation_boundary_review']);
    }

    private function runService(array $options = []): array
    {
        return $this->execute(['options' => $options]);
    }

    private function execute(array $overrides = []): array
    {
        $service = new WatchlistBacktestC81ControlledLimitedRuntimeOptInPilotOrShadowRolloutGoDecisionFinalizationReviewService();
        $fixture = $this->lockedC80Fixture();
        return $service->execute(
            (string) ($overrides['c80Artifact'] ?? $fixture['path']),
            (string) ($overrides['expectedC80Hash'] ?? $fixture['hash']),
            (string) ($overrides['expectedC80FileSha1'] ?? $fixture['sha1']),
            (string) ($overrides['output'] ?? $this->output),
            array_merge([
                'overwrite' => true,
                'operator_approved' => $overrides['operatorApproved'] ?? true,
                'approval_reference' => $overrides['approvalReference'] ?? 'C81_OPERATOR_APPROVED_GO_DECISION_FINALIZATION_REVIEW_ONLY',
            ], (array) ($overrides['options'] ?? []))
        );
    }

    private function mutateC80AndExecute(callable $mutator, string $name): array
    {
        $fixture = $this->lockedC80Fixture();
        $payload = json_decode((string) file_get_contents($fixture['path']), true);
        $payload = $mutator(is_array($payload) ? $payload : []);
        $path = 'storage/app/watchlist/backtest/.tmp-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
        return $this->execute([
            'c80Artifact' => $path,
            'expectedC80Hash' => (string) ($payload['artifact_hash'] ?? ''),
            'expectedC80FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function lockedC80Fixture(): array
    {
        $path = 'storage/app/watchlist/backtest/c80-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-operator-go-no-go-review.json';
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

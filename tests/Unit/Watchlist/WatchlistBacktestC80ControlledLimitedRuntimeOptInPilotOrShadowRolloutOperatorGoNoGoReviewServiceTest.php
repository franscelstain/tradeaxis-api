<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC80ControlledLimitedRuntimeOptInPilotOrShadowRolloutOperatorGoNoGoReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC80ControlledLimitedRuntimeOptInPilotOrShadowRolloutOperatorGoNoGoReviewServiceTest extends TestCase
{
    private string $output = 'storage/app/watchlist/backtest/.tmp-c80-test-output.json';
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

    public function test_c80_operator_go_passes_primary_and_backup_when_locked_c79_and_operator_approval_match(): void
    {
        $result = $this->runService();

        $this->assertSame('C80_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertSame($result['status'], $result['reason_code']);
        $this->assertTrue($result['operator_go_no_go_review_executed']);
        $this->assertTrue($result['operator_go_no_go_review_allowed']);
        $this->assertTrue($result['operator_go_no_go_review_pass']);
        $this->assertSame('GO', $result['operator_go_decision']);
        $this->assertTrue($result['primary_candidate_operator_go']);
        $this->assertTrue($result['backup_candidate_operator_go']);
        $this->assertFalse($result['production_ready']);
        $this->assertFalse($result['production_catalog_runtime_wired']);
        $this->assertFalse($result['controlled_opt_in_runtime_bridge_active']);
        $this->assertFalse($result['controlled_parallel_run_active']);
        $this->assertFalse($result['controlled_rollout_active']);
        $this->assertFalse($result['operator_go_no_go_context_persisted_to_live_runtime']);
        $this->assertFalse($result['production_deployment_allowed']);
        $this->assertFalse($result['production_deployment_executed']);
        $this->assertFalse($result['plan_confirm_mutation_allowed']);
        $this->assertFalse($result['plan_confirm_mutated']);
        $this->assertFalse($result['plan_confirm_runtime_reads_activated_catalog']);
        $this->assertFalse($result['live_plan_confirm_rollout_allowed']);
        $this->assertFalse($result['live_plan_confirm_rollout_executed']);
        $this->assertSame('C81_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_GO_DECISION_FINALIZATION_REVIEW', $result['next_step_recommendation']);
        $this->assertSame(2, $result['next_readiness_decision']['candidate_ready_for_go_decision_finalization_review_count']);
        $this->assertTrue($result['c79_lock_validation_summary']['c79_c80_readiness_count_match']);
        $this->assertTrue($result['lineage_validation_summary']['c79_to_c78_lock_match']);
        $this->assertTrue($result['progress_summary']['target_reached']);
        $this->assertSame('C81_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_GO_DECISION_FINALIZATION_REVIEW', $result['planned_next_summary']['planned_next_review']);
        $this->assertFileExists($this->output);
    }

    public function test_c80_records_required_sections(): void
    {
        $this->runService();
        $run = $this->readOutput();

        foreach ([
            'source_artifact_locks',
            'database_dictionary_read_summary',
            'c79_lock_validation_summary',
            'lineage_validation_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'operator_go_no_go_decision',
            'operator_go_no_go_candidate_scorecard',
            'operator_go_no_go_context_summary',
            'runtime_readiness_inspection_summary',
            'feature_flag_operator_approval_kill_switch_validation_summary',
            'rollback_and_emergency_disable_review_summary',
            'c79_proof_carry_forward_validation_summary',
            'go_no_go_governance_summary',
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

    public function test_c80_validates_c79_artifact_hash_and_file_sha1(): void
    {
        $fixture = $this->lockedC79Fixture();
        $hashResult = $this->execute(['c79Artifact' => $fixture['path'], 'expectedC79Hash' => '0000000000000000000000000000000000000000', 'expectedC79FileSha1' => $fixture['sha1']]);
        $this->assertSame('C80_BLOCKED_C79_ARTIFACT_LOCK_MISMATCH', $hashResult['status']);
        $this->assertFalse($hashResult['c79_hash_match']);

        $shaResult = $this->execute(['c79Artifact' => $fixture['path'], 'expectedC79Hash' => $fixture['hash'], 'expectedC79FileSha1' => '0000000000000000000000000000000000000000']);
        $this->assertSame('C80_BLOCKED_C79_FILE_SHA1_LOCK_MISMATCH', $shaResult['status']);
        $this->assertFalse($shaResult['c79_file_sha1_match']);
    }

    public function test_c80_rejects_c79_status_result_and_readiness_mismatches(): void
    {
        $status = $this->mutateC79AndExecute(function (array $c79): array {
            $c79['status'] = 'BROKEN_STATUS';
            return $c79;
        }, 'c79-status-mismatch');
        $this->assertSame('C80_BLOCKED_C79_STATUS_OR_REASON_MISMATCH', $status['status']);

        $pilot = $this->mutateC79AndExecute(function (array $c79): array {
            $c79['controlled_limited_runtime_opt_in_pilot_observation_result_review_pass'] = false;
            return $c79;
        }, 'c79-pilot-result-false');
        $this->assertSame('C80_BLOCKED_C79_CONTROLLED_PILOT_OBSERVATION_RESULT_NOT_PASSED', $pilot['status']);

        $shadow = $this->mutateC79AndExecute(function (array $c79): array {
            $c79['controlled_limited_shadow_rollout_observation_result_review_pass'] = false;
            return $c79;
        }, 'c79-shadow-result-false');
        $this->assertSame('C80_BLOCKED_C79_CONTROLLED_SHADOW_OBSERVATION_RESULT_NOT_PASSED', $shadow['status']);

        $count = $this->mutateC79AndExecute(function (array $c79): array {
            $c79['next_readiness_decision']['candidate_ready_for_controlled_limited_operator_go_no_go_review_count'] = 1;
            return $c79;
        }, 'c79-c80-count');
        $this->assertSame('C80_BLOCKED_C79_C80_READINESS_COUNT_MISMATCH', $count['status']);
    }

    public function test_c80_validates_nested_c80_readiness_path_not_top_level_aliases(): void
    {
        $result = $this->mutateC79AndExecute(function (array $c79): array {
            $c79['candidate_ready_for_controlled_limited_operator_go_no_go_review_count'] = 0;
            $c79['next_recommendation'] = 'BROKEN_TOP_LEVEL_ALIAS';
            return $c79;
        }, 'c79-top-level-alias');

        $this->assertSame('C80_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertTrue($result['c79_lock_validation_summary']['c79_readiness_nested_path_validated']);
        $this->assertFalse($result['c79_lock_validation_summary']['top_level_alias_used_for_c79_source_validation']);
    }

    public function test_c80_rejects_c79_safety_lineage_candidate_scope_and_go_confirmation_mismatches(): void
    {
        $safety = $this->mutateC79AndExecute(function (array $c79): array {
            $c79['controlled_limited_pilot_observation_result_context_persisted_to_live_runtime'] = true;
            return $c79;
        }, 'c79-pilot-live-context');
        $this->assertSame('C80_BLOCKED_C79_CONTROLLED_PILOT_OBSERVATION_RESULT_CONTEXT_ALREADY_PERSISTED_TO_LIVE_RUNTIME', $safety['status']);

        $lineage = $this->mutateC79AndExecute(function (array $c79): array {
            $c79['source_artifact_locks']['c78_source_lineage_match'] = false;
            return $c79;
        }, 'c79-lineage-mismatch');
        $this->assertSame('C80_BLOCKED_LINEAGE_LOCK_MISMATCH', $lineage['status']);

        $scope = $this->mutateC79AndExecute(function (array $c79): array {
            $c79['candidate_scope_freeze_summary']['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $c79;
        }, 'c79-scope-mismatch');
        $this->assertSame('C80_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $scope['status']);

        $noGo = $this->execute(['options' => ['operator_go_decision_confirmed' => false]]);
        $this->assertSame('C80_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_REJECTED_GO_DECISION_NOT_CONFIRMED', $noGo['status']);
    }

    public function test_c80_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->execute(['operatorApproved' => false]);
        $this->assertSame('C80_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingOperator['status']);

        $missingReference = $this->execute(['approvalReference' => '']);
        $this->assertSame('C80_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingReference['status']);
    }

    public function test_c80_candidate_scorecard_locks_e02_b01_and_a01_comparator_only(): void
    {
        $result = $this->runService();
        $scorecards = $result['operator_go_no_go_candidate_scorecard'];

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $scorecards[0]['candidate_code']);
        $this->assertTrue($scorecards[0]['candidate_ready_for_go_decision_finalization_review']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $scorecards[1]['candidate_code']);
        $this->assertTrue($scorecards[1]['candidate_ready_for_go_decision_finalization_review']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $scorecards[2]['candidate_code']);
        $this->assertSame('comparator_only', $scorecards[2]['c80_role']);
        $this->assertFalse($scorecards[2]['candidate_ready_for_go_decision_finalization_review']);
    }

    private function runService(array $options = []): array
    {
        return $this->execute(['options' => $options]);
    }

    private function execute(array $overrides = []): array
    {
        $service = new WatchlistBacktestC80ControlledLimitedRuntimeOptInPilotOrShadowRolloutOperatorGoNoGoReviewService();
        $fixture = $this->lockedC79Fixture();
        return $service->execute(
            (string) ($overrides['c79Artifact'] ?? $fixture['path']),
            (string) ($overrides['expectedC79Hash'] ?? $fixture['hash']),
            (string) ($overrides['expectedC79FileSha1'] ?? $fixture['sha1']),
            (string) ($overrides['output'] ?? $this->output),
            array_merge([
                'overwrite' => true,
                'operator_approved' => $overrides['operatorApproved'] ?? true,
                'approval_reference' => $overrides['approvalReference'] ?? 'C80_OPERATOR_APPROVED_GO_NO_GO_REVIEW_ONLY',
            ], (array) ($overrides['options'] ?? []))
        );
    }

    private function mutateC79AndExecute(callable $mutator, string $name): array
    {
        $fixture = $this->lockedC79Fixture();
        $payload = json_decode((string) file_get_contents($fixture['path']), true);
        $payload = $mutator(is_array($payload) ? $payload : []);
        $path = 'storage/app/watchlist/backtest/.tmp-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
        return $this->execute([
            'c79Artifact' => $path,
            'expectedC79Hash' => (string) ($payload['artifact_hash'] ?? ''),
            'expectedC79FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function lockedC79Fixture(): array
    {
        $path = 'storage/app/watchlist/backtest/c79-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-observation-result-review.json';
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

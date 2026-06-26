<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC88ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationGoDecisionFinalizationReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC88ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationGoDecisionFinalizationReviewServiceTest extends TestCase
{
    private string $output = 'storage/app/watchlist/backtest/.tmp-c88-test-output.json';
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

    public function test_c88_finalizes_post_activation_go_for_primary_and_backup_when_locked_c87_and_operator_approval_match(): void
    {
        $result = $this->runService();

        $this->assertSame('C88_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_PASSED_FINALIZED_GO_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertSame($result['status'], $result['reason_code']);
        $this->assertTrue($result['post_activation_go_decision_finalization_review_executed']);
        $this->assertTrue($result['post_activation_go_decision_finalization_review_allowed']);
        $this->assertTrue($result['post_activation_go_decision_finalization_review_pass']);
        $this->assertTrue($result['post_activation_go_decision_finalized']);
        $this->assertSame('FINALIZED_GO', $result['finalized_post_activation_go_decision']);
        $this->assertSame('GO', $result['operator_go_decision']);
        $this->assertTrue($result['primary_candidate_post_activation_go_finalized']);
        $this->assertTrue($result['backup_candidate_post_activation_go_finalized']);
        $this->assertFalse($result['production_ready']);
        $this->assertFalse($result['production_catalog_runtime_wired']);
        $this->assertFalse($result['controlled_opt_in_runtime_bridge_active']);
        $this->assertFalse($result['controlled_parallel_run_active']);
        $this->assertFalse($result['controlled_rollout_active']);
        $this->assertFalse($result['post_activation_go_decision_finalization_context_persisted_to_live_runtime']);
        $this->assertFalse($result['production_deployment_allowed']);
        $this->assertFalse($result['production_deployment_executed']);
        $this->assertFalse($result['plan_confirm_mutation_allowed']);
        $this->assertFalse($result['plan_confirm_mutated']);
        $this->assertFalse($result['plan_confirm_runtime_reads_activated_catalog']);
        $this->assertFalse($result['live_plan_confirm_rollout_allowed']);
        $this->assertFalse($result['live_plan_confirm_rollout_executed']);
        $this->assertSame('C89_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_COMPLETION_BOUNDARY_REVIEW', $result['next_step_recommendation']);
        $this->assertSame(2, $result['next_readiness_decision']['candidate_ready_for_post_activation_completion_boundary_review_count']);
        $this->assertTrue($result['c87_lock_validation_summary']['c87_c88_readiness_count_match']);
        $this->assertTrue($result['lineage_validation_summary']['c87_to_c86_lock_match']);
        $this->assertTrue($result['progress_summary']['target_reached']);
        $this->assertSame('C89_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_COMPLETION_BOUNDARY_REVIEW', $result['planned_next_summary']['planned_next_review']);
        $this->assertFileExists($this->output);
    }

    public function test_c88_records_required_sections(): void
    {
        $this->runService();
        $run = $this->readOutput();

        foreach ([
            'source_artifact_locks',
            'database_dictionary_read_summary',
            'c87_lock_validation_summary',
            'lineage_validation_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'post_activation_go_decision_finalization_decision',
            'post_activation_go_decision_finalization_candidate_scorecard',
            'post_activation_go_decision_finalization_context_summary',
            'runtime_readiness_inspection_summary',
            'feature_flag_operator_approval_kill_switch_validation_summary',
            'rollback_and_emergency_disable_review_summary',
            'c87_post_activation_operator_go_carry_forward_validation_summary',
            'post_activation_go_decision_finalization_governance_summary',
            'baseline_plan_confirm_non_mutation_summary',
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

    public function test_c88_validates_c87_artifact_hash_and_file_sha1(): void
    {
        $fixture = $this->lockedC87Fixture();
        $hashResult = $this->execute(['c87Artifact' => $fixture['path'], 'expectedC87Hash' => '0000000000000000000000000000000000000000', 'expectedC87FileSha1' => $fixture['sha1']]);
        $this->assertSame('C88_BLOCKED_C87_ARTIFACT_LOCK_MISMATCH', $hashResult['status']);
        $this->assertFalse($hashResult['c87_hash_match']);

        $shaResult = $this->execute(['c87Artifact' => $fixture['path'], 'expectedC87Hash' => $fixture['hash'], 'expectedC87FileSha1' => '0000000000000000000000000000000000000000']);
        $this->assertSame('C88_BLOCKED_C87_FILE_SHA1_LOCK_MISMATCH', $shaResult['status']);
        $this->assertFalse($shaResult['c87_file_sha1_match']);
    }

    public function test_c88_rejects_c87_status_go_decision_and_readiness_mismatches(): void
    {
        $status = $this->mutateC87AndExecute(function (array $c87): array {
            $c87['status'] = 'BROKEN_STATUS';
            return $c87;
        }, 'c87-status-mismatch');
        $this->assertSame('C88_BLOCKED_C87_STATUS_OR_REASON_MISMATCH', $status['status']);

        $notGo = $this->mutateC87AndExecute(function (array $c87): array {
            $c87['operator_go_decision'] = 'NO_GO';
            return $c87;
        }, 'c87-not-go');
        $this->assertSame('C88_BLOCKED_C87_POST_ACTIVATION_OPERATOR_GO_NOT_PASSED', $notGo['status']);

        $primaryMissing = $this->mutateC87AndExecute(function (array $c87): array {
            $c87['primary_candidate_post_activation_operator_go'] = false;
            return $c87;
        }, 'c87-primary-go-missing');
        $this->assertSame('C88_BLOCKED_C87_PRIMARY_GO_NOT_CONFIRMED', $primaryMissing['status']);

        $count = $this->mutateC87AndExecute(function (array $c87): array {
            $c87['next_readiness_decision']['candidate_ready_for_post_activation_go_decision_finalization_review_count'] = 1;
            return $c87;
        }, 'c87-c88-count');
        $this->assertSame('C88_BLOCKED_C87_C88_READINESS_COUNT_MISMATCH', $count['status']);
    }

    public function test_c88_validates_nested_c88_readiness_path_not_top_level_aliases(): void
    {
        $result = $this->mutateC87AndExecute(function (array $c87): array {
            $c87['candidate_ready_for_post_activation_go_decision_finalization_review_count'] = 0;
            $c87['next_recommendation'] = 'BROKEN_TOP_LEVEL_ALIAS';
            return $c87;
        }, 'c87-top-level-alias');

        $this->assertSame('C88_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_PASSED_FINALIZED_GO_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertTrue($result['c87_lock_validation_summary']['c87_readiness_nested_path_validated']);
        $this->assertFalse($result['c87_lock_validation_summary']['top_level_alias_used_for_c87_source_validation']);
    }

    public function test_c88_rejects_c87_safety_lineage_candidate_scope_and_finalization_confirmation_mismatches(): void
    {
        $safety = $this->mutateC87AndExecute(function (array $c87): array {
            $c87['production_catalog_runtime_wired'] = true;
            return $c87;
        }, 'c87-runtime-wired');
        $this->assertSame('C88_BLOCKED_C87_RUNTIME_ALREADY_WIRED', $safety['status']);

        $lineage = $this->mutateC87AndExecute(function (array $c87): array {
            $c87['source_artifact_locks']['c86_source_lineage_match'] = false;
            return $c87;
        }, 'c87-lineage-mismatch');
        $this->assertSame('C88_BLOCKED_LINEAGE_LOCK_MISMATCH', $lineage['status']);

        $scope = $this->mutateC87AndExecute(function (array $c87): array {
            $c87['post_activation_operator_go_no_go_decision']['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $c87;
        }, 'c87-scope-mismatch');
        $this->assertSame('C88_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $scope['status']);

        $notConfirmed = $this->execute(['options' => ['post_activation_go_decision_finalized_confirmed' => false]]);
        $this->assertSame('C88_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_GO_DECISION_FINALIZATION_NOT_CONFIRMED', $notConfirmed['status']);
    }

    public function test_c88_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->execute(['operatorApproved' => false]);
        $this->assertSame('C88_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingOperator['status']);

        $missingReference = $this->execute(['approvalReference' => '']);
        $this->assertSame('C88_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingReference['status']);
    }

    public function test_c88_candidate_scorecard_locks_e02_b01_and_a01_comparator_only(): void
    {
        $result = $this->runService();
        $scorecards = $result['post_activation_go_decision_finalization_candidate_scorecard'];

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $scorecards[0]['candidate_code']);
        $this->assertSame('primary_finalized_post_activation_go_candidate', $scorecards[0]['c88_role']);
        $this->assertTrue($scorecards[0]['candidate_ready_for_post_activation_completion_boundary_review']);
        $this->assertTrue($scorecards[0]['post_activation_go_finalized']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $scorecards[1]['candidate_code']);
        $this->assertSame('backup_finalized_post_activation_go_candidate', $scorecards[1]['c88_role']);
        $this->assertTrue($scorecards[1]['candidate_ready_for_post_activation_completion_boundary_review']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $scorecards[2]['candidate_code']);
        $this->assertSame('comparator_only', $scorecards[2]['c88_role']);
        $this->assertFalse($scorecards[2]['candidate_ready_for_post_activation_completion_boundary_review']);
        $this->assertFalse($scorecards[2]['post_activation_go_finalized']);
    }

    private function runService(array $options = []): array
    {
        return $this->execute(['options' => $options]);
    }

    private function execute(array $overrides = []): array
    {
        $service = new WatchlistBacktestC88ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationGoDecisionFinalizationReviewService();
        $fixture = $this->lockedC87Fixture();
        return $service->execute(
            (string) ($overrides['c87Artifact'] ?? $fixture['path']),
            (string) ($overrides['expectedC87Hash'] ?? $fixture['hash']),
            (string) ($overrides['expectedC87FileSha1'] ?? $fixture['sha1']),
            (string) ($overrides['output'] ?? $this->output),
            array_merge([
                'overwrite' => true,
                'operator_approved' => $overrides['operatorApproved'] ?? true,
                'approval_reference' => $overrides['approvalReference'] ?? 'C88_OPERATOR_APPROVED_POST_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_ONLY',
            ], (array) ($overrides['options'] ?? []))
        );
    }

    private function mutateC87AndExecute(callable $mutator, string $name): array
    {
        $fixture = $this->lockedC87Fixture();
        $payload = json_decode((string) file_get_contents($fixture['path']), true);
        $payload = $mutator(is_array($payload) ? $payload : []);
        $path = 'storage/app/watchlist/backtest/.tmp-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
        return $this->execute([
            'c87Artifact' => $path,
            'expectedC87Hash' => (string) ($payload['artifact_hash'] ?? ''),
            'expectedC87FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function lockedC87Fixture(): array
    {
        $path = 'storage/app/watchlist/backtest/c87-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-operator-go-no-go-review.json';
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

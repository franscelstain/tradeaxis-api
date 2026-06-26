<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC89ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationCompletionBoundaryReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC89ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationCompletionBoundaryReviewServiceTest extends TestCase
{
    private string $output = 'storage/app/watchlist/backtest/.tmp-c89-test-output.json';
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

    public function test_c89_clears_post_activation_completion_boundary_for_primary_and_backup_when_locked_c88_and_operator_approval_match(): void
    {
        $result = $this->runService();

        $this->assertSame('C89_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_PASSED_BOUNDARY_CLEARED_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertSame($result['status'], $result['reason_code']);
        $this->assertTrue($result['post_activation_completion_boundary_review_executed']);
        $this->assertTrue($result['post_activation_completion_boundary_review_allowed']);
        $this->assertTrue($result['post_activation_completion_boundary_review_pass']);
        $this->assertTrue($result['post_activation_completion_boundary_cleared']);
        $this->assertSame('FINALIZED_GO', $result['finalized_post_activation_go_decision']);
        $this->assertSame('GO', $result['operator_go_decision']);
        $this->assertTrue($result['primary_candidate_post_activation_completion_boundary_cleared']);
        $this->assertTrue($result['backup_candidate_post_activation_completion_boundary_cleared']);
        $this->assertFalse($result['production_ready']);
        $this->assertFalse($result['production_catalog_runtime_wired']);
        $this->assertFalse($result['controlled_opt_in_runtime_bridge_active']);
        $this->assertFalse($result['controlled_parallel_run_active']);
        $this->assertFalse($result['controlled_rollout_active']);
        $this->assertFalse($result['post_activation_completion_boundary_context_persisted_to_live_runtime']);
        $this->assertFalse($result['production_deployment_allowed']);
        $this->assertFalse($result['production_deployment_executed']);
        $this->assertFalse($result['plan_confirm_mutation_allowed']);
        $this->assertFalse($result['plan_confirm_mutated']);
        $this->assertFalse($result['plan_confirm_runtime_reads_activated_catalog']);
        $this->assertFalse($result['live_plan_confirm_rollout_allowed']);
        $this->assertFalse($result['live_plan_confirm_rollout_executed']);
        $this->assertSame('C90_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_READINESS_REVIEW', $result['next_step_recommendation']);
        $this->assertSame(2, $result['next_readiness_decision']['candidate_ready_for_post_activation_handoff_readiness_review_count']);
        $this->assertTrue($result['c88_lock_validation_summary']['c88_c89_readiness_count_match']);
        $this->assertTrue($result['lineage_validation_summary']['c88_to_c87_lock_match']);
        $this->assertTrue($result['progress_summary']['target_reached']);
        $this->assertSame('C90_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_READINESS_REVIEW', $result['planned_next_summary']['planned_next_review']);
        $this->assertFileExists($this->output);
    }

    public function test_c89_records_required_sections(): void
    {
        $this->runService();
        $run = $this->readOutput();

        foreach ([
            'source_artifact_locks',
            'database_dictionary_read_summary',
            'c88_lock_validation_summary',
            'lineage_validation_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'post_activation_completion_boundary_decision',
            'post_activation_completion_boundary_candidate_scorecard',
            'post_activation_completion_boundary_context_summary',
            'runtime_readiness_inspection_summary',
            'feature_flag_operator_approval_kill_switch_validation_summary',
            'rollback_and_emergency_disable_review_summary',
            'c88_post_activation_go_finalization_carry_forward_validation_summary',
            'post_activation_completion_boundary_governance_summary',
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

    public function test_c89_validates_c88_artifact_hash_and_file_sha1(): void
    {
        $fixture = $this->lockedC88Fixture();
        $hashResult = $this->execute(['c88Artifact' => $fixture['path'], 'expectedC88Hash' => '0000000000000000000000000000000000000000', 'expectedC88FileSha1' => $fixture['sha1']]);
        $this->assertSame('C89_BLOCKED_C88_ARTIFACT_LOCK_MISMATCH', $hashResult['status']);
        $this->assertFalse($hashResult['c88_hash_match']);

        $shaResult = $this->execute(['c88Artifact' => $fixture['path'], 'expectedC88Hash' => $fixture['hash'], 'expectedC88FileSha1' => '0000000000000000000000000000000000000000']);
        $this->assertSame('C89_BLOCKED_C88_FILE_SHA1_LOCK_MISMATCH', $shaResult['status']);
        $this->assertFalse($shaResult['c88_file_sha1_match']);
    }

    public function test_c89_rejects_c88_status_finalization_and_readiness_mismatches(): void
    {
        $status = $this->mutateC88AndExecute(function (array $c88): array {
            $c88['status'] = 'BROKEN_STATUS';
            return $c88;
        }, 'c88-status-mismatch');
        $this->assertSame('C89_BLOCKED_C88_STATUS_OR_REASON_MISMATCH', $status['status']);

        $notPassed = $this->mutateC88AndExecute(function (array $c88): array {
            $c88['post_activation_go_decision_finalization_review_pass'] = false;
            return $c88;
        }, 'c88-finalization-not-passed');
        $this->assertSame('C89_BLOCKED_C88_POST_ACTIVATION_GO_FINALIZATION_NOT_PASSED', $notPassed['status']);

        $notFinalized = $this->mutateC88AndExecute(function (array $c88): array {
            $c88['finalized_post_activation_go_decision'] = 'NOT_FINALIZED';
            return $c88;
        }, 'c88-finalized-go-mismatch');
        $this->assertSame('C89_BLOCKED_C88_FINALIZED_GO_DECISION_MISMATCH', $notFinalized['status']);

        $count = $this->mutateC88AndExecute(function (array $c88): array {
            $c88['next_readiness_decision']['candidate_ready_for_post_activation_completion_boundary_review_count'] = 1;
            return $c88;
        }, 'c88-c89-count');
        $this->assertSame('C89_BLOCKED_C88_C89_READINESS_COUNT_MISMATCH', $count['status']);
    }

    public function test_c89_validates_nested_c89_readiness_path_not_top_level_aliases(): void
    {
        $result = $this->mutateC88AndExecute(function (array $c88): array {
            $c88['candidate_ready_for_post_activation_completion_boundary_review_count'] = 0;
            $c88['next_recommendation'] = 'BROKEN_TOP_LEVEL_ALIAS';
            return $c88;
        }, 'c88-top-level-alias');

        $this->assertSame('C89_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_PASSED_BOUNDARY_CLEARED_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertTrue($result['c88_lock_validation_summary']['c88_readiness_nested_path_validated']);
        $this->assertFalse($result['c88_lock_validation_summary']['top_level_alias_used_for_c88_source_validation']);
    }

    public function test_c89_rejects_c88_safety_lineage_candidate_scope_and_boundary_confirmation_mismatches(): void
    {
        $safety = $this->mutateC88AndExecute(function (array $c88): array {
            $c88['post_activation_go_decision_finalization_context_persisted_to_live_runtime'] = true;
            return $c88;
        }, 'c88-live-context-persisted');
        $this->assertSame('C89_BLOCKED_C88_GO_FINALIZATION_CONTEXT_ALREADY_PERSISTED_TO_LIVE_RUNTIME', $safety['status']);

        $lineage = $this->mutateC88AndExecute(function (array $c88): array {
            $c88['source_artifact_locks']['c87_source_lineage_match'] = false;
            return $c88;
        }, 'c88-lineage-mismatch');
        $this->assertSame('C89_BLOCKED_LINEAGE_LOCK_MISMATCH', $lineage['status']);

        $scope = $this->mutateC88AndExecute(function (array $c88): array {
            $c88['post_activation_go_decision_finalization_decision']['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $c88;
        }, 'c88-scope-mismatch');
        $this->assertSame('C89_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $scope['status']);

        $notConfirmed = $this->execute(['options' => ['post_activation_completion_boundary_confirmed' => false]]);
        $this->assertSame('C89_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_REJECTED_BOUNDARY_NOT_CONFIRMED', $notConfirmed['status']);
    }

    public function test_c89_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->execute(['operatorApproved' => false]);
        $this->assertSame('C89_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingOperator['status']);

        $missingReference = $this->execute(['approvalReference' => '']);
        $this->assertSame('C89_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingReference['status']);
    }

    public function test_c89_candidate_scorecard_locks_e02_b01_and_a01_comparator_only(): void
    {
        $result = $this->runService();
        $scorecards = $result['post_activation_completion_boundary_candidate_scorecard'];

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $scorecards[0]['candidate_code']);
        $this->assertSame('primary_post_activation_completion_boundary_candidate', $scorecards[0]['c89_role']);
        $this->assertTrue($scorecards[0]['candidate_ready_for_post_activation_handoff_readiness_review']);
        $this->assertTrue($scorecards[0]['post_activation_completion_boundary_cleared']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $scorecards[1]['candidate_code']);
        $this->assertSame('backup_post_activation_completion_boundary_candidate', $scorecards[1]['c89_role']);
        $this->assertTrue($scorecards[1]['candidate_ready_for_post_activation_handoff_readiness_review']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $scorecards[2]['candidate_code']);
        $this->assertSame('comparator_only', $scorecards[2]['c89_role']);
        $this->assertFalse($scorecards[2]['candidate_ready_for_post_activation_handoff_readiness_review']);
        $this->assertFalse($scorecards[2]['post_activation_completion_boundary_cleared']);
    }

    private function runService(array $options = []): array
    {
        return $this->execute(['options' => $options]);
    }

    private function execute(array $overrides = []): array
    {
        $service = new WatchlistBacktestC89ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationCompletionBoundaryReviewService();
        $fixture = $this->lockedC88Fixture();
        return $service->execute(
            (string) ($overrides['c88Artifact'] ?? $fixture['path']),
            (string) ($overrides['expectedC88Hash'] ?? $fixture['hash']),
            (string) ($overrides['expectedC88FileSha1'] ?? $fixture['sha1']),
            (string) ($overrides['output'] ?? $this->output),
            array_merge([
                'overwrite' => true,
                'operator_approved' => $overrides['operatorApproved'] ?? true,
                'approval_reference' => $overrides['approvalReference'] ?? 'C89_OPERATOR_APPROVED_POST_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_ONLY',
            ], (array) ($overrides['options'] ?? []))
        );
    }

    private function mutateC88AndExecute(callable $mutator, string $name): array
    {
        $fixture = $this->lockedC88Fixture();
        $payload = json_decode((string) file_get_contents($fixture['path']), true);
        $payload = $mutator(is_array($payload) ? $payload : []);
        $path = 'storage/app/watchlist/backtest/.tmp-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
        return $this->execute([
            'c88Artifact' => $path,
            'expectedC88Hash' => (string) ($payload['artifact_hash'] ?? ''),
            'expectedC88FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function lockedC88Fixture(): array
    {
        $path = 'storage/app/watchlist/backtest/c88-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-go-decision-finalization-review.json';
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

<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC91ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationHandoffFinalizationReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC91ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationHandoffFinalizationReviewServiceTest extends TestCase
{
    private string $output = 'storage/app/watchlist/backtest/.tmp-c91-test-output.json';
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

    public function test_c91_marks_post_activation_handoff_finalized_for_primary_and_backup_when_locked_c90_and_operator_approval_match(): void
    {
        $result = $this->runService();

        $this->assertSame('C91_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_FINALIZATION_REVIEW_PASSED_HANDOFF_FINALIZED_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertSame($result['status'], $result['reason_code']);
        $this->assertTrue($result['post_activation_handoff_finalization_review_executed']);
        $this->assertTrue($result['post_activation_handoff_finalization_review_allowed']);
        $this->assertTrue($result['post_activation_handoff_finalization_review_pass']);
        $this->assertTrue($result['post_activation_handoff_finalized']);
        $this->assertTrue($result['post_activation_handoff_ready']);
        $this->assertSame('FINALIZED_GO', $result['finalized_post_activation_go_decision']);
        $this->assertSame('GO', $result['operator_go_decision']);
        $this->assertTrue($result['primary_candidate_post_activation_handoff_finalized']);
        $this->assertTrue($result['backup_candidate_post_activation_handoff_finalized']);
        $this->assertFalse($result['production_ready']);
        $this->assertFalse($result['production_catalog_runtime_wired']);
        $this->assertFalse($result['controlled_opt_in_runtime_bridge_active']);
        $this->assertFalse($result['controlled_parallel_run_active']);
        $this->assertFalse($result['controlled_rollout_active']);
        $this->assertFalse($result['post_activation_handoff_finalization_context_persisted_to_live_runtime']);
        $this->assertFalse($result['production_deployment_allowed']);
        $this->assertFalse($result['production_deployment_executed']);
        $this->assertFalse($result['plan_confirm_mutation_allowed']);
        $this->assertFalse($result['plan_confirm_mutated']);
        $this->assertFalse($result['plan_confirm_runtime_reads_activated_catalog']);
        $this->assertFalse($result['live_plan_confirm_rollout_allowed']);
        $this->assertFalse($result['live_plan_confirm_rollout_executed']);
        $this->assertSame('C92_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_COMPLETION_BOUNDARY_REVIEW', $result['next_step_recommendation']);
        $this->assertSame(2, $result['next_readiness_decision']['candidate_ready_for_post_activation_handoff_completion_boundary_review_count']);
        $this->assertTrue($result['c90_lock_validation_summary']['c90_c91_readiness_count_match']);
        $this->assertTrue($result['lineage_validation_summary']['c89_to_c88_lock_match']);
        $this->assertTrue($result['progress_summary']['target_reached']);
        $this->assertSame('C92_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_COMPLETION_BOUNDARY_REVIEW', $result['planned_next_summary']['planned_next_review']);
        $this->assertFileExists($this->output);
    }

    public function test_c91_records_required_sections(): void
    {
        $this->runService();
        $run = $this->readOutput();

        foreach ([
            'source_artifact_locks',
            'database_dictionary_read_summary',
            'c90_lock_validation_summary',
            'lineage_validation_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'post_activation_handoff_finalization_decision',
            'post_activation_handoff_finalization_candidate_scorecard',
            'post_activation_handoff_finalization_context_summary',
            'runtime_readiness_inspection_summary',
            'feature_flag_operator_approval_kill_switch_validation_summary',
            'rollback_and_emergency_disable_review_summary',
            'c90_handoff_readiness_carry_forward_validation_summary',
            'post_activation_handoff_finalization_governance_summary',
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

    public function test_c91_validates_c90_artifact_hash_and_file_sha1(): void
    {
        $fixture = $this->lockedC90Fixture();
        $hashResult = $this->execute(['c90Artifact' => $fixture['path'], 'expectedC90Hash' => '0000000000000000000000000000000000000000', 'expectedC90FileSha1' => $fixture['sha1']]);
        $this->assertSame('C91_BLOCKED_C90_ARTIFACT_LOCK_MISMATCH', $hashResult['status']);
        $this->assertFalse($hashResult['c90_hash_match']);

        $shaResult = $this->execute(['c90Artifact' => $fixture['path'], 'expectedC90Hash' => $fixture['hash'], 'expectedC90FileSha1' => '0000000000000000000000000000000000000000']);
        $this->assertSame('C91_BLOCKED_C90_FILE_SHA1_LOCK_MISMATCH', $shaResult['status']);
        $this->assertFalse($shaResult['c90_file_sha1_match']);
    }

    public function test_c91_rejects_c90_status_boundary_and_readiness_mismatches(): void
    {
        $status = $this->mutateC90AndExecute(function (array $c90): array {
            $c90['status'] = 'BROKEN_STATUS';
            return $c90;
        }, 'c90-status-mismatch');
        $this->assertSame('C91_BLOCKED_C90_STATUS_OR_REASON_MISMATCH', $status['status']);

        $notReady = $this->mutateC90AndExecute(function (array $c90): array {
            $c90['post_activation_handoff_ready'] = false;
            return $c90;
        }, 'c90-handoff-not-ready');
        $this->assertSame('C91_BLOCKED_C90_HANDOFF_NOT_READY', $notReady['status']);

        $primaryMissing = $this->mutateC90AndExecute(function (array $c90): array {
            $c90['primary_candidate_post_activation_handoff_ready'] = false;
            return $c90;
        }, 'c90-primary-handoff-missing');
        $this->assertSame('C91_BLOCKED_C90_PRIMARY_HANDOFF_NOT_READY', $primaryMissing['status']);

        $count = $this->mutateC90AndExecute(function (array $c90): array {
            $c90['next_readiness_decision']['candidate_ready_for_post_activation_handoff_finalization_review_count'] = 1;
            return $c90;
        }, 'c90-c91-count');
        $this->assertSame('C91_BLOCKED_C90_C91_READINESS_COUNT_MISMATCH', $count['status']);
    }

    public function test_c91_validates_nested_c91_readiness_path_not_top_level_aliases(): void
    {
        $result = $this->mutateC90AndExecute(function (array $c90): array {
            $c90['candidate_ready_for_post_activation_handoff_finalization_review_count'] = 0;
            $c90['next_recommendation'] = 'BROKEN_TOP_LEVEL_ALIAS';
            return $c90;
        }, 'c90-top-level-alias');

        $this->assertSame('C91_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_FINALIZATION_REVIEW_PASSED_HANDOFF_FINALIZED_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertTrue($result['c90_lock_validation_summary']['c90_readiness_nested_path_validated']);
        $this->assertFalse($result['c90_lock_validation_summary']['top_level_alias_used_for_c90_source_validation']);
    }

    public function test_c91_rejects_c90_safety_lineage_candidate_scope_and_handoff_confirmation_mismatches(): void
    {
        $safety = $this->mutateC90AndExecute(function (array $c90): array {
            $c90['post_activation_handoff_readiness_context_persisted_to_live_runtime'] = true;
            return $c90;
        }, 'c90-live-context-persisted');
        $this->assertSame('C91_BLOCKED_C90_HANDOFF_READINESS_CONTEXT_ALREADY_PERSISTED_TO_LIVE_RUNTIME', $safety['status']);

        $lineage = $this->mutateC90AndExecute(function (array $c90): array {
            $c90['source_artifact_locks']['c89_source_lineage_match'] = false;
            return $c90;
        }, 'c90-lineage-mismatch');
        $this->assertSame('C91_BLOCKED_LINEAGE_LOCK_MISMATCH', $lineage['status']);

        $scope = $this->mutateC90AndExecute(function (array $c90): array {
            $c90['post_activation_handoff_readiness_decision']['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $c90;
        }, 'c90-scope-mismatch');
        $this->assertSame('C91_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_FINALIZATION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $scope['status']);

        $notConfirmed = $this->execute(['options' => ['post_activation_handoff_finalization_confirmed' => false]]);
        $this->assertSame('C91_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_FINALIZATION_REVIEW_REJECTED_HANDOFF_NOT_CONFIRMED', $notConfirmed['status']);
    }

    public function test_c91_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->execute(['operatorApproved' => false]);
        $this->assertSame('C91_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingOperator['status']);

        $missingReference = $this->execute(['approvalReference' => '']);
        $this->assertSame('C91_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingReference['status']);
    }

    public function test_c91_candidate_scorecard_locks_e02_b01_and_a01_comparator_only(): void
    {
        $result = $this->runService();
        $scorecards = $result['post_activation_handoff_finalization_candidate_scorecard'];

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $scorecards[0]['candidate_code']);
        $this->assertSame('primary_post_activation_handoff_finalized_candidate', $scorecards[0]['c91_role']);
        $this->assertTrue($scorecards[0]['candidate_ready_for_post_activation_handoff_completion_boundary_review']);
        $this->assertTrue($scorecards[0]['post_activation_handoff_finalized']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $scorecards[1]['candidate_code']);
        $this->assertSame('backup_post_activation_handoff_finalized_candidate', $scorecards[1]['c91_role']);
        $this->assertTrue($scorecards[1]['candidate_ready_for_post_activation_handoff_completion_boundary_review']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $scorecards[2]['candidate_code']);
        $this->assertSame('comparator_only', $scorecards[2]['c91_role']);
        $this->assertFalse($scorecards[2]['candidate_ready_for_post_activation_handoff_completion_boundary_review']);
        $this->assertFalse($scorecards[2]['post_activation_handoff_finalized']);
    }

    private function runService(array $options = []): array
    {
        return $this->execute(['options' => $options]);
    }

    private function execute(array $overrides = []): array
    {
        $service = new WatchlistBacktestC91ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationHandoffFinalizationReviewService();
        $fixture = $this->lockedC90Fixture();
        return $service->execute(
            (string) ($overrides['c90Artifact'] ?? $fixture['path']),
            (string) ($overrides['expectedC90Hash'] ?? $fixture['hash']),
            (string) ($overrides['expectedC90FileSha1'] ?? $fixture['sha1']),
            (string) ($overrides['output'] ?? $this->output),
            array_merge([
                'overwrite' => true,
                'operator_approved' => $overrides['operatorApproved'] ?? true,
                'approval_reference' => $overrides['approvalReference'] ?? 'C91_OPERATOR_APPROVED_POST_ACTIVATION_HANDOFF_FINALIZATION_REVIEW_ONLY',
            ], (array) ($overrides['options'] ?? []))
        );
    }

    private function mutateC90AndExecute(callable $mutator, string $name): array
    {
        $fixture = $this->lockedC90Fixture();
        $payload = json_decode((string) file_get_contents($fixture['path']), true);
        $payload = $mutator(is_array($payload) ? $payload : []);
        $path = 'storage/app/watchlist/backtest/.tmp-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
        return $this->execute([
            'c90Artifact' => $path,
            'expectedC90Hash' => (string) ($payload['artifact_hash'] ?? ''),
            'expectedC90FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function lockedC90Fixture(): array
    {
        $path = 'storage/app/watchlist/backtest/c90-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-readiness-review.json';
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

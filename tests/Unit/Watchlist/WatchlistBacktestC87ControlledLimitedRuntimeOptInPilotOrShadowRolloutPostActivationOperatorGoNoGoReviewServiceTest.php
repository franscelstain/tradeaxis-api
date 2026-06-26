<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC87ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationOperatorGoNoGoReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC87ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationOperatorGoNoGoReviewServiceTest extends TestCase
{
    private string $output = 'storage/app/watchlist/backtest/.tmp-c87-test-output.json';
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

    public function test_c87_records_post_activation_operator_go_for_primary_and_backup_when_locked_c86_and_operator_approval_match(): void
    {
        $result = $this->runService();

        $this->assertSame('C87_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertSame($result['status'], $result['reason_code']);
        $this->assertTrue($result['post_activation_operator_go_no_go_review_executed']);
        $this->assertTrue($result['post_activation_operator_go_no_go_review_allowed']);
        $this->assertTrue($result['post_activation_operator_go_no_go_review_pass']);
        $this->assertSame('GO', $result['operator_go_decision']);
        $this->assertTrue($result['activation_authorized']);
        $this->assertTrue($result['activation_executed']);
        $this->assertTrue($result['controlled_activation_record_observed']);
        $this->assertTrue($result['post_activation_observation_result_reviewed']);
        $this->assertTrue($result['primary_candidate_post_activation_operator_go']);
        $this->assertTrue($result['backup_candidate_post_activation_operator_go']);
        $this->assertFalse($result['production_ready']);
        $this->assertFalse($result['production_catalog_runtime_wired']);
        $this->assertFalse($result['controlled_opt_in_runtime_bridge_active']);
        $this->assertFalse($result['controlled_parallel_run_active']);
        $this->assertFalse($result['controlled_rollout_active']);
        $this->assertFalse($result['post_activation_operator_go_no_go_context_persisted_to_live_runtime']);
        $this->assertFalse($result['production_deployment_allowed']);
        $this->assertFalse($result['production_deployment_executed']);
        $this->assertFalse($result['plan_confirm_mutation_allowed']);
        $this->assertFalse($result['plan_confirm_mutated']);
        $this->assertFalse($result['plan_confirm_runtime_reads_activated_catalog']);
        $this->assertFalse($result['live_plan_confirm_rollout_allowed']);
        $this->assertFalse($result['live_plan_confirm_rollout_executed']);
        $this->assertSame('C88_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW', $result['next_step_recommendation']);
        $this->assertSame(2, $result['next_readiness_decision']['candidate_ready_for_post_activation_go_decision_finalization_review_count']);
        $this->assertTrue($result['c86_lock_validation_summary']['c86_c87_readiness_count_match']);
        $this->assertTrue($result['lineage_validation_summary']['c86_to_c85_lock_match']);
        $this->assertTrue($result['progress_summary']['target_reached']);
        $this->assertSame('C88_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW', $result['planned_next_summary']['planned_next_review']);
        $this->assertFileExists($this->output);
    }

    public function test_c87_records_required_sections(): void
    {
        $this->runService();
        $run = $this->readOutput();

        foreach ([
            'source_artifact_locks',
            'database_dictionary_read_summary',
            'c86_lock_validation_summary',
            'lineage_validation_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'post_activation_operator_go_no_go_decision',
            'post_activation_operator_go_no_go_candidate_scorecard',
            'post_activation_operator_go_no_go_context_summary',
            'runtime_readiness_inspection_summary',
            'feature_flag_operator_approval_kill_switch_validation_summary',
            'rollback_and_emergency_disable_review_summary',
            'c86_post_activation_observation_result_carry_forward_validation_summary',
            'post_activation_operator_go_no_go_governance_summary',
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

    public function test_c87_validates_c86_artifact_hash_and_file_sha1(): void
    {
        $fixture = $this->lockedC86Fixture();
        $hashResult = $this->execute(['c86Artifact' => $fixture['path'], 'expectedC86Hash' => '0000000000000000000000000000000000000000', 'expectedC86FileSha1' => $fixture['sha1']]);
        $this->assertSame('C87_BLOCKED_C86_ARTIFACT_LOCK_MISMATCH', $hashResult['status']);
        $this->assertFalse($hashResult['c86_hash_match']);

        $shaResult = $this->execute(['c86Artifact' => $fixture['path'], 'expectedC86Hash' => $fixture['hash'], 'expectedC86FileSha1' => '0000000000000000000000000000000000000000']);
        $this->assertSame('C87_BLOCKED_C86_FILE_SHA1_LOCK_MISMATCH', $shaResult['status']);
        $this->assertFalse($shaResult['c86_file_sha1_match']);
    }

    public function test_c87_rejects_c86_status_result_and_readiness_mismatches(): void
    {
        $status = $this->mutateC86AndExecute(function (array $c86): array {
            $c86['status'] = 'BROKEN_STATUS';
            return $c86;
        }, 'c86-status-mismatch');
        $this->assertSame('C87_BLOCKED_C86_STATUS_OR_REASON_MISMATCH', $status['status']);

        $notReviewed = $this->mutateC86AndExecute(function (array $c86): array {
            $c86['post_activation_observation_result_review_pass'] = false;
            return $c86;
        }, 'c86-result-false');
        $this->assertSame('C87_BLOCKED_C86_POST_ACTIVATION_OBSERVATION_RESULT_NOT_PASSED', $notReviewed['status']);

        $notExecuted = $this->mutateC86AndExecute(function (array $c86): array {
            $c86['activation_executed'] = false;
            return $c86;
        }, 'c86-not-executed');
        $this->assertSame('C87_BLOCKED_C86_ACTIVATION_RESULT_NOT_CONFIRMED', $notExecuted['status']);

        $count = $this->mutateC86AndExecute(function (array $c86): array {
            $c86['next_readiness_decision']['candidate_ready_for_post_activation_operator_go_no_go_review_count'] = 1;
            return $c86;
        }, 'c86-c87-count');
        $this->assertSame('C87_BLOCKED_C86_C87_READINESS_COUNT_MISMATCH', $count['status']);
    }

    public function test_c87_validates_nested_c87_readiness_path_not_top_level_aliases(): void
    {
        $result = $this->mutateC86AndExecute(function (array $c86): array {
            $c86['candidate_ready_for_post_activation_operator_go_no_go_review_count'] = 0;
            $c86['next_recommendation'] = 'BROKEN_TOP_LEVEL_ALIAS';
            return $c86;
        }, 'c86-top-level-alias');

        $this->assertSame('C87_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertTrue($result['c86_lock_validation_summary']['c86_readiness_nested_path_validated']);
        $this->assertFalse($result['c86_lock_validation_summary']['top_level_alias_used_for_c86_source_validation']);
    }

    public function test_c87_rejects_c86_safety_lineage_candidate_scope_and_go_confirmation_mismatches(): void
    {
        $safety = $this->mutateC86AndExecute(function (array $c86): array {
            $c86['production_catalog_runtime_wired'] = true;
            return $c86;
        }, 'c86-runtime-wired');
        $this->assertSame('C87_BLOCKED_C86_RUNTIME_ALREADY_WIRED', $safety['status']);

        $lineage = $this->mutateC86AndExecute(function (array $c86): array {
            $c86['source_artifact_locks']['c85_source_lineage_match'] = false;
            return $c86;
        }, 'c86-lineage-mismatch');
        $this->assertSame('C87_BLOCKED_LINEAGE_LOCK_MISMATCH', $lineage['status']);

        $scope = $this->mutateC86AndExecute(function (array $c86): array {
            $c86['post_activation_observation_result_decision']['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $c86;
        }, 'c86-scope-mismatch');
        $this->assertSame('C87_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $scope['status']);

        $notConfirmed = $this->execute(['options' => ['post_activation_operator_go_decision_confirmed' => false]]);
        $this->assertSame('C87_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_GO_DECISION_NOT_CONFIRMED', $notConfirmed['status']);
    }

    public function test_c87_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->execute(['operatorApproved' => false]);
        $this->assertSame('C87_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingOperator['status']);

        $missingReference = $this->execute(['approvalReference' => '']);
        $this->assertSame('C87_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingReference['status']);
    }

    public function test_c87_candidate_scorecard_locks_e02_b01_and_a01_comparator_only(): void
    {
        $result = $this->runService();
        $scorecards = $result['post_activation_operator_go_no_go_candidate_scorecard'];

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $scorecards[0]['candidate_code']);
        $this->assertSame('primary_post_activation_operator_go_candidate', $scorecards[0]['c87_role']);
        $this->assertTrue($scorecards[0]['candidate_ready_for_post_activation_go_decision_finalization_review']);
        $this->assertTrue($scorecards[0]['post_activation_operator_go']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $scorecards[1]['candidate_code']);
        $this->assertSame('backup_post_activation_operator_go_candidate', $scorecards[1]['c87_role']);
        $this->assertTrue($scorecards[1]['candidate_ready_for_post_activation_go_decision_finalization_review']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $scorecards[2]['candidate_code']);
        $this->assertSame('comparator_only', $scorecards[2]['c87_role']);
        $this->assertFalse($scorecards[2]['candidate_ready_for_post_activation_go_decision_finalization_review']);
        $this->assertFalse($scorecards[2]['post_activation_operator_go']);
    }

    private function runService(array $options = []): array
    {
        return $this->execute(['options' => $options]);
    }

    private function execute(array $overrides = []): array
    {
        $service = new WatchlistBacktestC87ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationOperatorGoNoGoReviewService();
        $fixture = $this->lockedC86Fixture();
        return $service->execute(
            (string) ($overrides['c86Artifact'] ?? $fixture['path']),
            (string) ($overrides['expectedC86Hash'] ?? $fixture['hash']),
            (string) ($overrides['expectedC86FileSha1'] ?? $fixture['sha1']),
            (string) ($overrides['output'] ?? $this->output),
            array_merge([
                'overwrite' => true,
                'operator_approved' => $overrides['operatorApproved'] ?? true,
                'approval_reference' => $overrides['approvalReference'] ?? 'C87_OPERATOR_APPROVED_POST_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_ONLY',
            ], (array) ($overrides['options'] ?? []))
        );
    }

    private function mutateC86AndExecute(callable $mutator, string $name): array
    {
        $fixture = $this->lockedC86Fixture();
        $payload = json_decode((string) file_get_contents($fixture['path']), true);
        $payload = $mutator(is_array($payload) ? $payload : []);
        $path = 'storage/app/watchlist/backtest/.tmp-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
        return $this->execute([
            'c86Artifact' => $path,
            'expectedC86Hash' => (string) ($payload['artifact_hash'] ?? ''),
            'expectedC86FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function lockedC86Fixture(): array
    {
        $path = 'storage/app/watchlist/backtest/c86-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-observation-result-review.json';
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

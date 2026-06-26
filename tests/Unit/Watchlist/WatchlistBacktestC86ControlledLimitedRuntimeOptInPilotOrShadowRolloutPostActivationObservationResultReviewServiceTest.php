<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC86ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationObservationResultReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC86ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationObservationResultReviewServiceTest extends TestCase
{
    private string $output = 'storage/app/watchlist/backtest/.tmp-c86-test-output.json';
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

    public function test_c86_reviews_post_activation_observation_result_for_primary_and_backup_when_locked_c85_and_operator_approval_match(): void
    {
        $result = $this->runService();

        $this->assertSame('C86_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_RESULT_REVIEW_PASSED_RESULT_REVIEWED_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertSame($result['status'], $result['reason_code']);
        $this->assertTrue($result['post_activation_observation_result_review_executed']);
        $this->assertTrue($result['post_activation_observation_result_review_allowed']);
        $this->assertTrue($result['post_activation_observation_result_review_pass']);
        $this->assertTrue($result['activation_authorized']);
        $this->assertTrue($result['activation_executed']);
        $this->assertTrue($result['controlled_activation_record_observed']);
        $this->assertTrue($result['post_activation_observation_result_reviewed']);
        $this->assertTrue($result['primary_candidate_post_activation_result_reviewed']);
        $this->assertTrue($result['backup_candidate_post_activation_result_reviewed']);
        $this->assertFalse($result['production_ready']);
        $this->assertFalse($result['production_catalog_runtime_wired']);
        $this->assertFalse($result['controlled_opt_in_runtime_bridge_active']);
        $this->assertFalse($result['controlled_parallel_run_active']);
        $this->assertFalse($result['controlled_rollout_active']);
        $this->assertFalse($result['post_activation_observation_result_context_persisted_to_live_runtime']);
        $this->assertFalse($result['production_deployment_allowed']);
        $this->assertFalse($result['production_deployment_executed']);
        $this->assertFalse($result['plan_confirm_mutation_allowed']);
        $this->assertFalse($result['plan_confirm_mutated']);
        $this->assertFalse($result['plan_confirm_runtime_reads_activated_catalog']);
        $this->assertFalse($result['live_plan_confirm_rollout_allowed']);
        $this->assertFalse($result['live_plan_confirm_rollout_executed']);
        $this->assertSame('C87_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW', $result['next_step_recommendation']);
        $this->assertSame(2, $result['next_readiness_decision']['candidate_ready_for_post_activation_operator_go_no_go_review_count']);
        $this->assertTrue($result['c85_lock_validation_summary']['c85_c86_readiness_count_match']);
        $this->assertTrue($result['lineage_validation_summary']['c85_to_c84_lock_match']);
        $this->assertTrue($result['progress_summary']['target_reached']);
        $this->assertSame('C87_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW', $result['planned_next_summary']['planned_next_review']);
        $this->assertFileExists($this->output);
    }

    public function test_c86_records_required_sections(): void
    {
        $this->runService();
        $run = $this->readOutput();

        foreach ([
            'source_artifact_locks',
            'database_dictionary_read_summary',
            'c85_lock_validation_summary',
            'lineage_validation_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'post_activation_observation_result_decision',
            'post_activation_observation_result_candidate_scorecard',
            'post_activation_observation_result_context_summary',
            'runtime_readiness_inspection_summary',
            'feature_flag_operator_approval_kill_switch_validation_summary',
            'rollback_and_emergency_disable_review_summary',
            'c85_post_activation_observation_carry_forward_validation_summary',
            'post_activation_observation_result_governance_summary',
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

    public function test_c86_validates_c85_artifact_hash_and_file_sha1(): void
    {
        $fixture = $this->lockedC85Fixture();
        $hashResult = $this->execute(['c85Artifact' => $fixture['path'], 'expectedC85Hash' => '0000000000000000000000000000000000000000', 'expectedC85FileSha1' => $fixture['sha1']]);
        $this->assertSame('C86_BLOCKED_C85_ARTIFACT_LOCK_MISMATCH', $hashResult['status']);
        $this->assertFalse($hashResult['c85_hash_match']);

        $shaResult = $this->execute(['c85Artifact' => $fixture['path'], 'expectedC85Hash' => $fixture['hash'], 'expectedC85FileSha1' => '0000000000000000000000000000000000000000']);
        $this->assertSame('C86_BLOCKED_C85_FILE_SHA1_LOCK_MISMATCH', $shaResult['status']);
        $this->assertFalse($shaResult['c85_file_sha1_match']);
    }

    public function test_c86_rejects_c85_status_observation_and_readiness_mismatches(): void
    {
        $status = $this->mutateC85AndExecute(function (array $c85): array {
            $c85['status'] = 'BROKEN_STATUS';
            return $c85;
        }, 'c85-status-mismatch');
        $this->assertSame('C86_BLOCKED_C85_STATUS_OR_REASON_MISMATCH', $status['status']);

        $notObserved = $this->mutateC85AndExecute(function (array $c85): array {
            $c85['post_activation_observation_review_pass'] = false;
            return $c85;
        }, 'c85-observation-false');
        $this->assertSame('C86_BLOCKED_C85_POST_ACTIVATION_OBSERVATION_NOT_PASSED', $notObserved['status']);

        $notExecuted = $this->mutateC85AndExecute(function (array $c85): array {
            $c85['activation_executed'] = false;
            return $c85;
        }, 'c85-not-executed');
        $this->assertSame('C86_BLOCKED_C85_ACTIVATION_NOT_EXECUTED', $notExecuted['status']);

        $count = $this->mutateC85AndExecute(function (array $c85): array {
            $c85['next_readiness_decision']['candidate_ready_for_post_activation_observation_result_review_count'] = 1;
            return $c85;
        }, 'c85-c86-count');
        $this->assertSame('C86_BLOCKED_C85_C86_READINESS_COUNT_MISMATCH', $count['status']);
    }

    public function test_c86_validates_nested_c86_readiness_path_not_top_level_aliases(): void
    {
        $result = $this->mutateC85AndExecute(function (array $c85): array {
            $c85['candidate_ready_for_post_activation_observation_result_review_count'] = 0;
            $c85['next_recommendation'] = 'BROKEN_TOP_LEVEL_ALIAS';
            return $c85;
        }, 'c85-top-level-alias');

        $this->assertSame('C86_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_RESULT_REVIEW_PASSED_RESULT_REVIEWED_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertTrue($result['c85_lock_validation_summary']['c85_readiness_nested_path_validated']);
        $this->assertFalse($result['c85_lock_validation_summary']['top_level_alias_used_for_c85_source_validation']);
    }

    public function test_c86_rejects_c85_safety_lineage_candidate_scope_and_result_confirmation_mismatches(): void
    {
        $safety = $this->mutateC85AndExecute(function (array $c85): array {
            $c85['production_catalog_runtime_wired'] = true;
            return $c85;
        }, 'c85-runtime-wired');
        $this->assertSame('C86_BLOCKED_C85_RUNTIME_ALREADY_WIRED', $safety['status']);

        $lineage = $this->mutateC85AndExecute(function (array $c85): array {
            $c85['source_artifact_locks']['c84_source_lineage_match'] = false;
            return $c85;
        }, 'c85-lineage-mismatch');
        $this->assertSame('C86_BLOCKED_LINEAGE_LOCK_MISMATCH', $lineage['status']);

        $scope = $this->mutateC85AndExecute(function (array $c85): array {
            $c85['post_activation_observation_decision']['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $c85;
        }, 'c85-scope-mismatch');
        $this->assertSame('C86_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $scope['status']);

        $notConfirmed = $this->execute(['options' => ['post_activation_observation_result_confirmed' => false]]);
        $this->assertSame('C86_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_OBSERVATION_NOT_CONFIRMED', $notConfirmed['status']);
    }

    public function test_c86_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->execute(['operatorApproved' => false]);
        $this->assertSame('C86_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingOperator['status']);

        $missingReference = $this->execute(['approvalReference' => '']);
        $this->assertSame('C86_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingReference['status']);
    }

    public function test_c86_candidate_scorecard_locks_e02_b01_and_a01_comparator_only(): void
    {
        $result = $this->runService();
        $scorecards = $result['post_activation_observation_result_candidate_scorecard'];

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $scorecards[0]['candidate_code']);
        $this->assertSame('primary_post_activation_observation_result_candidate', $scorecards[0]['c86_role']);
        $this->assertTrue($scorecards[0]['candidate_ready_for_post_activation_operator_go_no_go_review']);
        $this->assertTrue($scorecards[0]['post_activation_observation_result_reviewed']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $scorecards[1]['candidate_code']);
        $this->assertSame('backup_post_activation_observation_result_candidate', $scorecards[1]['c86_role']);
        $this->assertTrue($scorecards[1]['candidate_ready_for_post_activation_operator_go_no_go_review']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $scorecards[2]['candidate_code']);
        $this->assertSame('comparator_only', $scorecards[2]['c86_role']);
        $this->assertFalse($scorecards[2]['candidate_ready_for_post_activation_operator_go_no_go_review']);
        $this->assertFalse($scorecards[2]['post_activation_observation_result_reviewed']);
    }

    private function runService(array $options = []): array
    {
        return $this->execute(['options' => $options]);
    }

    private function execute(array $overrides = []): array
    {
        $service = new WatchlistBacktestC86ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationObservationResultReviewService();
        $fixture = $this->lockedC85Fixture();
        return $service->execute(
            (string) ($overrides['c85Artifact'] ?? $fixture['path']),
            (string) ($overrides['expectedC85Hash'] ?? $fixture['hash']),
            (string) ($overrides['expectedC85FileSha1'] ?? $fixture['sha1']),
            (string) ($overrides['output'] ?? $this->output),
            array_merge([
                'overwrite' => true,
                'operator_approved' => $overrides['operatorApproved'] ?? true,
                'approval_reference' => $overrides['approvalReference'] ?? 'C86_OPERATOR_APPROVED_POST_ACTIVATION_OBSERVATION_RESULT_REVIEW_ONLY',
            ], (array) ($overrides['options'] ?? []))
        );
    }

    private function mutateC85AndExecute(callable $mutator, string $name): array
    {
        $fixture = $this->lockedC85Fixture();
        $payload = json_decode((string) file_get_contents($fixture['path']), true);
        $payload = $mutator(is_array($payload) ? $payload : []);
        $path = 'storage/app/watchlist/backtest/.tmp-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
        return $this->execute([
            'c85Artifact' => $path,
            'expectedC85Hash' => (string) ($payload['artifact_hash'] ?? ''),
            'expectedC85FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function lockedC85Fixture(): array
    {
        $path = 'storage/app/watchlist/backtest/c85-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-observation-review.json';
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

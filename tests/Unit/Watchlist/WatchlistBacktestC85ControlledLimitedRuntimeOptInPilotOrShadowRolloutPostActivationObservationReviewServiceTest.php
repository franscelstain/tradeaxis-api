<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC85ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationObservationReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC85ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationObservationReviewServiceTest extends TestCase
{
    private string $output = 'storage/app/watchlist/backtest/.tmp-c85-test-output.json';
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

    public function test_c85_observes_post_activation_record_for_primary_and_backup_when_locked_c84_and_operator_approval_match(): void
    {
        $result = $this->runService();

        $this->assertSame('C85_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_REVIEW_PASSED_OBSERVED_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertSame($result['status'], $result['reason_code']);
        $this->assertTrue($result['post_activation_observation_review_executed']);
        $this->assertTrue($result['post_activation_observation_review_allowed']);
        $this->assertTrue($result['post_activation_observation_review_pass']);
        $this->assertTrue($result['activation_authorized']);
        $this->assertTrue($result['activation_executed']);
        $this->assertTrue($result['controlled_activation_record_observed']);
        $this->assertTrue($result['primary_candidate_post_activation_observed']);
        $this->assertTrue($result['backup_candidate_post_activation_observed']);
        $this->assertFalse($result['production_ready']);
        $this->assertFalse($result['production_catalog_runtime_wired']);
        $this->assertFalse($result['controlled_opt_in_runtime_bridge_active']);
        $this->assertFalse($result['controlled_parallel_run_active']);
        $this->assertFalse($result['controlled_rollout_active']);
        $this->assertFalse($result['post_activation_observation_context_persisted_to_live_runtime']);
        $this->assertFalse($result['production_deployment_allowed']);
        $this->assertFalse($result['production_deployment_executed']);
        $this->assertFalse($result['plan_confirm_mutation_allowed']);
        $this->assertFalse($result['plan_confirm_mutated']);
        $this->assertFalse($result['plan_confirm_runtime_reads_activated_catalog']);
        $this->assertFalse($result['live_plan_confirm_rollout_allowed']);
        $this->assertFalse($result['live_plan_confirm_rollout_executed']);
        $this->assertSame('C86_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_RESULT_REVIEW', $result['next_step_recommendation']);
        $this->assertSame(2, $result['next_readiness_decision']['candidate_ready_for_post_activation_observation_result_review_count']);
        $this->assertTrue($result['c84_lock_validation_summary']['c84_c85_readiness_count_match']);
        $this->assertTrue($result['lineage_validation_summary']['c84_to_c83_lock_match']);
        $this->assertTrue($result['progress_summary']['target_reached']);
        $this->assertSame('C86_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_RESULT_REVIEW', $result['planned_next_summary']['planned_next_review']);
        $this->assertFileExists($this->output);
    }

    public function test_c85_records_required_sections(): void
    {
        $this->runService();
        $run = $this->readOutput();

        foreach ([
            'source_artifact_locks',
            'database_dictionary_read_summary',
            'c84_lock_validation_summary',
            'lineage_validation_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'post_activation_observation_decision',
            'post_activation_observation_candidate_scorecard',
            'post_activation_observation_context_summary',
            'runtime_readiness_inspection_summary',
            'feature_flag_operator_approval_kill_switch_validation_summary',
            'rollback_and_emergency_disable_review_summary',
            'c84_activation_execution_carry_forward_validation_summary',
            'post_activation_observation_governance_summary',
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

    public function test_c85_validates_c84_artifact_hash_and_file_sha1(): void
    {
        $fixture = $this->lockedC84Fixture();
        $hashResult = $this->execute(['c84Artifact' => $fixture['path'], 'expectedC84Hash' => '0000000000000000000000000000000000000000', 'expectedC84FileSha1' => $fixture['sha1']]);
        $this->assertSame('C85_BLOCKED_C84_ARTIFACT_LOCK_MISMATCH', $hashResult['status']);
        $this->assertFalse($hashResult['c84_hash_match']);

        $shaResult = $this->execute(['c84Artifact' => $fixture['path'], 'expectedC84Hash' => $fixture['hash'], 'expectedC84FileSha1' => '0000000000000000000000000000000000000000']);
        $this->assertSame('C85_BLOCKED_C84_FILE_SHA1_LOCK_MISMATCH', $shaResult['status']);
        $this->assertFalse($shaResult['c84_file_sha1_match']);
    }

    public function test_c85_rejects_c84_status_execution_record_and_readiness_mismatches(): void
    {
        $status = $this->mutateC84AndExecute(function (array $c84): array {
            $c84['status'] = 'BROKEN_STATUS';
            return $c84;
        }, 'c84-status-mismatch');
        $this->assertSame('C85_BLOCKED_C84_STATUS_OR_REASON_MISMATCH', $status['status']);

        $notExecuted = $this->mutateC84AndExecute(function (array $c84): array {
            $c84['activation_executed'] = false;
            return $c84;
        }, 'c84-not-executed');
        $this->assertSame('C85_BLOCKED_C84_ACTIVATION_NOT_EXECUTED', $notExecuted['status']);

        $missingRecord = $this->mutateC84AndExecute(function (array $c84): array {
            $c84['controlled_activation_record_created'] = false;
            return $c84;
        }, 'c84-record-missing');
        $this->assertSame('C85_BLOCKED_C84_ACTIVATION_EXECUTION_NOT_PASSED', $missingRecord['status']);

        $count = $this->mutateC84AndExecute(function (array $c84): array {
            $c84['next_readiness_decision']['candidate_ready_for_post_activation_observation_review_count'] = 1;
            return $c84;
        }, 'c84-c85-count');
        $this->assertSame('C85_BLOCKED_C84_C85_READINESS_COUNT_MISMATCH', $count['status']);
    }

    public function test_c85_validates_nested_c85_readiness_path_not_top_level_aliases(): void
    {
        $result = $this->mutateC84AndExecute(function (array $c84): array {
            $c84['candidate_ready_for_post_activation_observation_review_count'] = 0;
            $c84['next_recommendation'] = 'BROKEN_TOP_LEVEL_ALIAS';
            return $c84;
        }, 'c84-top-level-alias');

        $this->assertSame('C85_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_REVIEW_PASSED_OBSERVED_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertTrue($result['c84_lock_validation_summary']['c84_readiness_nested_path_validated']);
        $this->assertFalse($result['c84_lock_validation_summary']['top_level_alias_used_for_c84_source_validation']);
    }

    public function test_c85_rejects_c84_safety_lineage_candidate_scope_and_observation_confirmation_mismatches(): void
    {
        $safety = $this->mutateC84AndExecute(function (array $c84): array {
            $c84['production_catalog_runtime_wired'] = true;
            return $c84;
        }, 'c84-runtime-wired');
        $this->assertSame('C85_BLOCKED_C84_RUNTIME_ALREADY_WIRED', $safety['status']);

        $lineage = $this->mutateC84AndExecute(function (array $c84): array {
            $c84['source_artifact_locks']['c83_source_lineage_match'] = false;
            return $c84;
        }, 'c84-lineage-mismatch');
        $this->assertSame('C85_BLOCKED_LINEAGE_LOCK_MISMATCH', $lineage['status']);

        $scope = $this->mutateC84AndExecute(function (array $c84): array {
            $c84['activation_execution_decision']['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $c84;
        }, 'c84-scope-mismatch');
        $this->assertSame('C85_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $scope['status']);

        $notConfirmed = $this->execute(['options' => ['post_activation_observation_confirmed' => false]]);
        $this->assertSame('C85_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_REVIEW_REJECTED_OBSERVATION_NOT_CONFIRMED', $notConfirmed['status']);
    }

    public function test_c85_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->execute(['operatorApproved' => false]);
        $this->assertSame('C85_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingOperator['status']);

        $missingReference = $this->execute(['approvalReference' => '']);
        $this->assertSame('C85_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingReference['status']);
    }

    public function test_c85_candidate_scorecard_locks_e02_b01_and_a01_comparator_only(): void
    {
        $result = $this->runService();
        $scorecards = $result['post_activation_observation_candidate_scorecard'];

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $scorecards[0]['candidate_code']);
        $this->assertSame('primary_post_activation_observation_candidate', $scorecards[0]['c85_role']);
        $this->assertTrue($scorecards[0]['candidate_ready_for_post_activation_observation_result_review']);
        $this->assertTrue($scorecards[0]['activation_executed']);
        $this->assertTrue($scorecards[0]['controlled_activation_record_observed']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $scorecards[1]['candidate_code']);
        $this->assertSame('backup_post_activation_observation_candidate', $scorecards[1]['c85_role']);
        $this->assertTrue($scorecards[1]['candidate_ready_for_post_activation_observation_result_review']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $scorecards[2]['candidate_code']);
        $this->assertSame('comparator_only', $scorecards[2]['c85_role']);
        $this->assertFalse($scorecards[2]['candidate_ready_for_post_activation_observation_result_review']);
        $this->assertFalse($scorecards[2]['activation_executed']);
        $this->assertFalse($scorecards[2]['controlled_activation_record_observed']);
    }

    private function runService(array $options = []): array
    {
        return $this->execute(['options' => $options]);
    }

    private function execute(array $overrides = []): array
    {
        $service = new WatchlistBacktestC85ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationObservationReviewService();
        $fixture = $this->lockedC84Fixture();
        return $service->execute(
            (string) ($overrides['c84Artifact'] ?? $fixture['path']),
            (string) ($overrides['expectedC84Hash'] ?? $fixture['hash']),
            (string) ($overrides['expectedC84FileSha1'] ?? $fixture['sha1']),
            (string) ($overrides['output'] ?? $this->output),
            array_merge([
                'overwrite' => true,
                'operator_approved' => $overrides['operatorApproved'] ?? true,
                'approval_reference' => $overrides['approvalReference'] ?? 'C85_OPERATOR_APPROVED_POST_ACTIVATION_OBSERVATION_REVIEW_ONLY',
            ], (array) ($overrides['options'] ?? []))
        );
    }

    private function mutateC84AndExecute(callable $mutator, string $name): array
    {
        $fixture = $this->lockedC84Fixture();
        $payload = json_decode((string) file_get_contents($fixture['path']), true);
        $payload = $mutator(is_array($payload) ? $payload : []);
        $path = 'storage/app/watchlist/backtest/.tmp-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
        return $this->execute([
            'c84Artifact' => $path,
            'expectedC84Hash' => (string) ($payload['artifact_hash'] ?? ''),
            'expectedC84FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function lockedC84Fixture(): array
    {
        $path = 'storage/app/watchlist/backtest/c84-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-activation-execution-review.json';
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

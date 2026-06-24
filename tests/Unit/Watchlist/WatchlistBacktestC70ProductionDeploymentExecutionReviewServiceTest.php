<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC70ProductionDeploymentExecutionReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC70ProductionDeploymentExecutionReviewServiceTest extends TestCase
{
    private string $output = 'storage/app/watchlist/backtest/.tmp-c70-test-output.json';
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

    public function test_c70_runtime_passes_primary_and_backup_when_locked_inputs_match(): void
    {
        $result = $this->runService();

        $this->assertSame('C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_PASSED_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertSame('C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_PASSED_PRIMARY_AND_BACKUP', $result['reason_code']);
        $this->assertTrue($result['production_deployment_execution_review_executed']);
        $this->assertTrue($result['production_deployment_execution_review_pass']);
        $this->assertFalse($result['production_ready']);
        $this->assertTrue($result['controlled_production_deployment_execution_review_allowed']);
        $this->assertTrue($result['controlled_production_deployment_execution_review_pass']);
        $this->assertFalse($result['production_catalog_runtime_wired']);
        $this->assertFalse($result['production_deployment_allowed']);
        $this->assertFalse($result['production_deployment_executed']);
        $this->assertFalse($result['plan_confirm_mutation_allowed']);
        $this->assertFalse($result['plan_confirm_mutated']);
        $this->assertFalse($result['plan_confirm_runtime_reads_activated_catalog']);
        $this->assertFalse($result['live_plan_confirm_rollout_allowed']);
        $this->assertFalse($result['live_plan_confirm_rollout_executed']);
        $this->assertSame('C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION', $result['next_step_recommendation']);
        $this->assertFileExists($this->output);
    }

    public function test_c70_records_required_sections(): void
    {
        $this->runService();
        $run = $this->readOutput();

        foreach ([
            'source_artifact_locks',
            'database_dictionary_read_summary',
            'c69_lock_validation_summary',
            'c68_lineage_validation_summary',
            'c67_lineage_validation_summary',
            'c66_lineage_validation_summary',
            'c65_lineage_validation_summary',
            'c64_lineage_validation_summary',
            'c63_lineage_validation_summary',
            'c62_lineage_validation_summary',
            'c61_lineage_validation_summary',
            'c60_lineage_validation_summary',
            'candidate_scope_freeze_summary',
            'production_deployment_execution_candidate_scorecard',
            'controlled_deployment_execution_decision',
            'runtime_path_inspection_summary',
            'execution_contract_review_summary',
            'feature_flag_kill_switch_execution_summary',
            'rollback_execution_verification_summary',
            'smoke_test_execution_summary',
            'shadow_read_or_dry_run_execution_summary',
            'plan_confirm_non_mutation_summary',
            'bad_month_execution_review_results',
            'weak_regime_execution_review_results',
            'source_bias_shared_core_execution_review_summary',
            'production_mutation_safety_summary',
            'documentation_governance_summary',
            'c65_cleanup_note_summary',
            'c71_readiness_decision',
            'failure_attribution_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }
    }

    public function test_c70_validates_c69_artifact_hash_and_file_sha1(): void
    {
        $hashResult = $this->execute(['expectedC69Hash' => '0000000000000000000000000000000000000000']);
        $this->assertSame('C70_BLOCKED_C69_ARTIFACT_LOCK_MISMATCH', $hashResult['status']);
        $this->assertFalse($hashResult['c69_hash_match']);

        $shaResult = $this->execute(['expectedC69FileSha1' => '0000000000000000000000000000000000000000']);
        $this->assertSame('C70_BLOCKED_C69_FILE_SHA1_LOCK_MISMATCH', $shaResult['status']);
        $this->assertFalse($shaResult['c69_file_sha1_match']);
    }

    public function test_c70_rejects_missing_c69_artifact(): void
    {
        $result = $this->execute(['c69Artifact' => 'storage/app/watchlist/backtest/missing-c69.json']);

        $this->assertSame('C70_BLOCKED_C69_ARTIFACT_LOCK_MISMATCH', $result['status']);
        $this->assertFalse($result['production_deployment_execution_review_executed']);
    }

    public function test_c70_rejects_c69_status_and_reason_mismatches(): void
    {
        $status = $this->mutateC69AndExecute(function (array $c69): array {
            $c69['status'] = 'BROKEN_STATUS';
            return $c69;
        }, 'c69-status-mismatch');
        $this->assertSame('C70_BLOCKED_C69_STATUS_OR_REASON_MISMATCH', $status['status']);

        $reason = $this->mutateC69AndExecute(function (array $c69): array {
            $c69['reason_code'] = 'BROKEN_REASON';
            return $c69;
        }, 'c69-reason-mismatch');
        $this->assertSame('C70_BLOCKED_C69_STATUS_OR_REASON_MISMATCH', $reason['status']);
    }

    public function test_c70_rejects_c69_required_gate_mismatches(): void
    {
        $cases = [
            ['production_deployment_prep_or_bridge_review_pass', false, 'C70_BLOCKED_C69_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_NOT_PASSED'],
            ['production_deployment_prep_allowed', false, 'C70_BLOCKED_C69_DEPLOYMENT_PREP_NOT_ALLOWED'],
            ['production_deployment_execution_review_allowed', false, 'C70_BLOCKED_C69_DEPLOYMENT_EXECUTION_REVIEW_NOT_ALLOWED'],
            ['plan_confirm_wiring_prep_allowed', false, 'C70_BLOCKED_C69_PLAN_CONFIRM_WIRING_PREP_NOT_ALLOWED'],
            ['production_catalog_runtime_wired', true, 'C70_BLOCKED_C69_RUNTIME_ALREADY_WIRED'],
            ['production_deployment_allowed', true, 'C70_BLOCKED_C69_PRODUCTION_DEPLOYMENT_ALREADY_ALLOWED'],
            ['production_deployment_executed', true, 'C70_BLOCKED_C69_DEPLOYMENT_ALREADY_EXECUTED'],
            ['plan_confirm_mutation_allowed', true, 'C70_BLOCKED_C69_PLAN_CONFIRM_MUTATION_ALREADY_ALLOWED'],
            ['plan_confirm_mutated', true, 'C70_BLOCKED_C69_PLAN_CONFIRM_ALREADY_MUTATED'],
            ['plan_confirm_runtime_reads_activated_catalog', true, 'C70_BLOCKED_C69_PLAN_CONFIRM_ALREADY_READING_ACTIVATED_CATALOG'],
        ];

        foreach ($cases as $case) {
            $result = $this->mutateC69AndExecute(function (array $c69) use ($case): array {
                $c69[$case[0]] = $case[1];
                return $c69;
            }, 'c69-'.$case[0]);
            $this->assertSame($case[2], $result['status'], $case[0]);
        }
    }

    public function test_c70_validates_nested_c70_readiness_path_not_top_level_aliases(): void
    {
        $result = $this->mutateC69AndExecute(function (array $c69): array {
            $c69['candidate_ready_for_c70_count'] = 0;
            $c69['c70_recommendation'] = 'BROKEN_TOP_LEVEL_ALIAS';
            return $c69;
        }, 'c69-top-level-alias');

        $this->assertSame('C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_PASSED_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertTrue($result['c69_lock_validation_summary']['c70_readiness_nested_path_validated']);
        $this->assertFalse($result['c69_lock_validation_summary']['top_level_alias_used_for_c69_source_validation']);
    }

    public function test_c70_rejects_nested_c70_readiness_mismatches(): void
    {
        $count = $this->mutateC69AndExecute(function (array $c69): array {
            $c69['c70_readiness_decision']['candidate_ready_for_c70_count'] = 1;
            return $c69;
        }, 'c69-nested-count');
        $this->assertSame('C70_BLOCKED_C69_C70_READINESS_COUNT_MISMATCH', $count['status']);

        $recommendation = $this->mutateC69AndExecute(function (array $c69): array {
            $c69['c70_readiness_decision']['c70_recommendation'] = 'BROKEN_C70';
            return $c69;
        }, 'c69-nested-recommendation');
        $this->assertSame('C70_BLOCKED_C69_RECOMMENDATION_MISMATCH', $recommendation['status']);
    }

    public function test_c70_validates_all_lineage_artifacts(): void
    {
        foreach ([
            'expectedC68Hash',
            'expectedC67Hash',
            'expectedC66Hash',
            'expectedC65Hash',
            'expectedC64Hash',
            'expectedC63Hash',
            'expectedC62Hash',
            'expectedC61Hash',
            'expectedC60Hash',
        ] as $key) {
            $result = $this->execute([$key => '0000000000000000000000000000000000000000']);
            $this->assertSame('C70_BLOCKED_LINEAGE_LOCK_MISMATCH', $result['status'], $key);
        }
    }

    public function test_c70_records_database_dictionary_rule(): void
    {
        $this->runService();
        $run = $this->readOutput();

        $this->assertTrue($run['database_dictionary_read_summary']['dictionary_rule_acknowledged']);
        $this->assertTrue($run['database_dictionary_read_summary']['dictionary_read_rule_complied']);
        $this->assertFalse($run['database_dictionary_read_summary']['dictionary_missing_coverage_detected']);
        $this->assertSame('market_benchmark_indicators.roc_20', $run['database_dictionary_read_summary']['market_index_mapping']['market_index_roc20_source']);
        $this->assertSame('market_benchmark_indicators.ma20_slope_pct', $run['database_dictionary_read_summary']['market_index_mapping']['market_index_ma20_slope_pct_source']);
        $this->assertSame('IHSG', $run['database_dictionary_read_summary']['market_index_mapping']['benchmark_code']);
    }

    public function test_c70_candidate_scope_freeze_comes_from_c69_locked_decision(): void
    {
        $this->runService();
        $run = $this->readOutput();
        $scope = $run['candidate_scope_freeze_summary'];

        $this->assertTrue($scope['candidate_scope_freeze_completed']);
        $this->assertSame('C69_LOCKED_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_DECISION', $scope['candidate_scope_source']);
        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $scope['primary_candidate_code']);
        $this->assertSame(['C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION'], $scope['backup_candidate_codes']);
        $this->assertSame(['C61_A01_B01_WEAK_REGIME_QUALITY_FIRST'], $scope['comparator_only_candidate_codes']);
        $this->assertFalse($scope['candidate_scope_changed_after_c69']);
        $this->assertFalse($scope['new_candidate_created']);
        $this->assertFalse($scope['selection_rule_changed']);
        $this->assertFalse($scope['parameter_changed']);
        $this->assertFalse($scope['oos_result_used_for_new_ranking']);
        $this->assertFalse($scope['a01_promoted']);
    }

    public function test_c70_rejects_candidate_scope_mismatch_or_a01_promotion(): void
    {
        $primary = $this->mutateC69AndExecute(function (array $c69): array {
            foreach ($c69['production_deployment_bridge_candidate_scorecard'] as &$row) {
                if ($row['candidate_code'] === 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE') {
                    $row['c69_role'] = 'BROKEN_PRIMARY';
                }
            }
            unset($row);
            return $c69;
        }, 'c69-primary-scope');
        $this->assertSame('C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $primary['status']);

        $a01 = $this->execute(['options' => ['force_a01_promoted' => true]]);
        $this->assertSame('C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $a01['status']);
    }

    public function test_c70_summaries_and_scorecards_are_generated(): void
    {
        $this->runService();
        $run = $this->readOutput();

        $this->assertTrue($run['runtime_path_inspection_summary']['runtime_path_inspection_completed']);
        $this->assertTrue($run['execution_contract_review_summary']['execution_contract_pass']);
        $this->assertTrue($run['feature_flag_kill_switch_execution_summary']['default_off_feature_flag_pass']);
        $this->assertTrue($run['feature_flag_kill_switch_execution_summary']['kill_switch_execution_pass']);
        $this->assertTrue($run['rollback_execution_verification_summary']['rollback_execution_proof_pass']);
        $this->assertTrue($run['smoke_test_execution_summary']['smoke_test_execution_proof_pass']);
        $this->assertTrue($run['shadow_read_or_dry_run_execution_summary']['shadow_read_or_dry_run_execution_proof_pass']);
        $this->assertTrue($run['plan_confirm_non_mutation_summary']['plan_confirm_non_mutation_pass']);
        $this->assertTrue($run['source_bias_shared_core_execution_review_summary']['source_bias_governance_pass']);
        $this->assertTrue($run['source_bias_shared_core_execution_review_summary']['shared_core_governance_pass']);
        $this->assertTrue($run['production_mutation_safety_summary']['production_mutation_safety_pass']);
        $this->assertTrue($run['documentation_governance_summary']['documentation_governance_pass']);

        $rows = $this->indexByCode($run['production_deployment_execution_candidate_scorecard']);
        $this->assertArrayHasKey('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $rows);
        $this->assertArrayHasKey('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $rows);
        $this->assertArrayHasKey('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $rows);
        $this->assertTrue($rows['C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE']['production_deployment_execution_review_pass']);
        $this->assertTrue($rows['C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION']['production_deployment_execution_review_pass']);
        $this->assertFalse($rows['C61_A01_B01_WEAK_REGIME_QUALITY_FIRST']['production_deployment_execution_review_pass']);
        $this->assertSame(['C70_A01_REMAINS_COMPARATOR_ONLY'], $rows['C61_A01_B01_WEAK_REGIME_QUALITY_FIRST']['failure_reason_codes']);
    }

    public function test_c70_rejects_controlled_execution_gate_failures(): void
    {
        $cases = [
            ['force_execution_contract_missing', 'C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_REJECTED_EXECUTION_CONTRACT_MISSING'],
            ['force_feature_flag_missing', 'C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH_MISSING'],
            ['force_feature_flag_default_on', 'C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH_MISSING'],
            ['force_kill_switch_missing', 'C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH_MISSING'],
            ['force_rollback_missing', 'C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_REJECTED_ROLLBACK_EXECUTION_PROOF_MISSING'],
            ['force_smoke_test_missing', 'C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_REJECTED_SMOKE_TEST_EXECUTION_PROOF_MISSING'],
            ['force_shadow_read_missing', 'C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_REJECTED_SHADOW_READ_EXECUTION_PROOF_MISSING'],
            ['force_plan_confirm_output_changed', 'C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_REJECTED_PLAN_CONFIRM_ALREADY_MUTATED'],
            ['force_bad_month_missing', 'C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_REJECTED_BAD_MONTH_GOVERNANCE'],
            ['force_weak_regime_missing', 'C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_REJECTED_WEAK_REGIME_GOVERNANCE'],
            ['force_source_bias_high', 'C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_REJECTED_SOURCE_BIAS_OR_SHARED_CORE'],
            ['force_shared_core_high', 'C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_REJECTED_SOURCE_BIAS_OR_SHARED_CORE'],
            ['force_production_mutation', 'C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_REJECTED_PRODUCTION_MUTATION'],
        ];

        foreach ($cases as $case) {
            $result = $this->execute(['options' => [$case[0] => true]]);
            $this->assertSame($case[1], $result['status'], $case[0]);
            $this->assertFalse($result['production_deployment_allowed'], $case[0]);
            $this->assertFalse($result['plan_confirm_runtime_reads_activated_catalog'], $case[0]);
        }
    }

    public function test_c70_bad_month_and_weak_regime_risks_are_retained(): void
    {
        $this->runService();
        $run = $this->readOutput();

        foreach ($run['bad_month_execution_review_results'] as $row) {
            $this->assertTrue($row['documented_bad_month_risk_retained']);
            $this->assertSame('MODERATE', $row['bad_month_risk_level']);
            $this->assertSame('PASS_WITH_DOCUMENTED_RISK', $row['bad_month_governance_decision']);
            $this->assertFalse($row['production_deployment_execution_risk_free_claim']);
        }
        foreach ($run['weak_regime_execution_review_results'] as $row) {
            $this->assertTrue($row['weak_regime_retained']);
            $this->assertSame('market_down_or_sideways_high_vol', $row['weak_regime']);
            $this->assertSame('SUFFICIENT', $row['weak_regime_sample_status']);
            $this->assertFalse($row['weak_regime_sample_collapse_detected']);
        }
    }

    public function test_c70_c71_readiness_is_shadow_read_only_when_passed(): void
    {
        $this->runService();
        $run = $this->readOutput();

        $this->assertSame(2, $run['c71_readiness_decision']['candidate_ready_for_c71_count']);
        $this->assertSame([
            'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE',
            'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION',
        ], $run['c71_readiness_decision']['candidate_codes']);
        $this->assertSame('C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION', $run['c71_readiness_decision']['c71_recommendation']);
        $this->assertFalse($run['c71_readiness_decision']['production_deployment_allowed']);
        $this->assertFalse($run['c71_readiness_decision']['plan_confirm_runtime_reads_activated_catalog']);
    }

    public function test_c70_always_keeps_live_deployment_and_plan_confirm_flags_false(): void
    {
        $this->runService();
        $run = $this->readOutput();

        foreach ([
            'production_catalog_runtime_wired',
            'production_deployment_allowed',
            'production_deployment_executed',
            'plan_confirm_mutation_allowed',
            'plan_confirm_mutated',
            'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed',
            'live_plan_confirm_rollout_executed',
        ] as $field) {
            $this->assertFalse($run[$field], $field);
            $this->assertFalse($run['controlled_deployment_execution_decision'][$field], $field);
            $this->assertFalse($run['c71_readiness_decision'][$field], $field);
        }
    }

    private function runService(): array
    {
        return $this->execute();
    }

    private function execute(array $override = []): array
    {
        $service = new WatchlistBacktestC70ProductionDeploymentExecutionReviewService();
        $locks = $this->actualLocks();
        $options = array_merge(['overwrite' => true], (array) ($override['options'] ?? []));

        return $service->execute(
            (string) ($override['c69Artifact'] ?? WatchlistBacktestC70ProductionDeploymentExecutionReviewService::DEFAULT_C69_ARTIFACT),
            (string) ($override['expectedC69Hash'] ?? $locks['c69'][0]),
            (string) ($override['expectedC69FileSha1'] ?? $locks['c69'][1]),
            (string) ($override['c68Artifact'] ?? WatchlistBacktestC70ProductionDeploymentExecutionReviewService::DEFAULT_C68_ARTIFACT),
            (string) ($override['expectedC68Hash'] ?? $locks['c68'][0]),
            (string) ($override['expectedC68FileSha1'] ?? $locks['c68'][1]),
            (string) ($override['c67Artifact'] ?? WatchlistBacktestC70ProductionDeploymentExecutionReviewService::DEFAULT_C67_ARTIFACT),
            (string) ($override['expectedC67Hash'] ?? $locks['c67'][0]),
            (string) ($override['expectedC67FileSha1'] ?? $locks['c67'][1]),
            (string) ($override['c66Artifact'] ?? WatchlistBacktestC70ProductionDeploymentExecutionReviewService::DEFAULT_C66_ARTIFACT),
            (string) ($override['expectedC66Hash'] ?? $locks['c66'][0]),
            (string) ($override['expectedC66FileSha1'] ?? $locks['c66'][1]),
            (string) ($override['c65Artifact'] ?? WatchlistBacktestC70ProductionDeploymentExecutionReviewService::DEFAULT_C65_ARTIFACT),
            (string) ($override['expectedC65Hash'] ?? $locks['c65'][0]),
            (string) ($override['expectedC65FileSha1'] ?? $locks['c65'][1]),
            (string) ($override['c64Artifact'] ?? WatchlistBacktestC70ProductionDeploymentExecutionReviewService::DEFAULT_C64_ARTIFACT),
            (string) ($override['expectedC64Hash'] ?? $locks['c64'][0]),
            (string) ($override['expectedC64FileSha1'] ?? $locks['c64'][1]),
            (string) ($override['c63Artifact'] ?? WatchlistBacktestC70ProductionDeploymentExecutionReviewService::DEFAULT_C63_ARTIFACT),
            (string) ($override['expectedC63Hash'] ?? $locks['c63'][0]),
            (string) ($override['expectedC63FileSha1'] ?? $locks['c63'][1]),
            (string) ($override['c62Artifact'] ?? WatchlistBacktestC70ProductionDeploymentExecutionReviewService::DEFAULT_C62_ARTIFACT),
            (string) ($override['expectedC62Hash'] ?? $locks['c62'][0]),
            (string) ($override['expectedC62FileSha1'] ?? $locks['c62'][1]),
            (string) ($override['c61Artifact'] ?? WatchlistBacktestC70ProductionDeploymentExecutionReviewService::DEFAULT_C61_ARTIFACT),
            (string) ($override['expectedC61Hash'] ?? $locks['c61'][0]),
            (string) ($override['expectedC61FileSha1'] ?? $locks['c61'][1]),
            (string) ($override['c60Artifact'] ?? WatchlistBacktestC70ProductionDeploymentExecutionReviewService::DEFAULT_C60_ARTIFACT),
            (string) ($override['expectedC60Hash'] ?? $locks['c60'][0]),
            (string) ($override['expectedC60FileSha1'] ?? $locks['c60'][1]),
            $this->output,
            $options
        );
    }

    private function mutateC69AndExecute(callable $mutator, string $name): array
    {
        [$path, $hash, $sha1] = $this->writeMutatedArtifact(WatchlistBacktestC70ProductionDeploymentExecutionReviewService::DEFAULT_C69_ARTIFACT, $mutator, $name);
        return $this->execute([
            'c69Artifact' => $path,
            'expectedC69Hash' => $hash,
            'expectedC69FileSha1' => $sha1,
        ]);
    }

    private function writeMutatedArtifact(string $source, callable $mutator, string $name): array
    {
        $payload = json_decode((string) file_get_contents($source), true);
        $payload = $mutator(is_array($payload) ? $payload : []);
        $path = 'storage/app/watchlist/backtest/.tmp-'.$name.'.json';
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $this->tmpFiles[] = $path;
        return [$path, (string) ($payload['artifact_hash'] ?? ''), strtoupper((string) sha1_file($path))];
    }

    private function readOutput(): array
    {
        return json_decode((string) file_get_contents($this->output), true);
    }

    private function actualLocks(): array
    {
        $paths = [
            'c69' => WatchlistBacktestC70ProductionDeploymentExecutionReviewService::DEFAULT_C69_ARTIFACT,
            'c68' => WatchlistBacktestC70ProductionDeploymentExecutionReviewService::DEFAULT_C68_ARTIFACT,
            'c67' => WatchlistBacktestC70ProductionDeploymentExecutionReviewService::DEFAULT_C67_ARTIFACT,
            'c66' => WatchlistBacktestC70ProductionDeploymentExecutionReviewService::DEFAULT_C66_ARTIFACT,
            'c65' => WatchlistBacktestC70ProductionDeploymentExecutionReviewService::DEFAULT_C65_ARTIFACT,
            'c64' => WatchlistBacktestC70ProductionDeploymentExecutionReviewService::DEFAULT_C64_ARTIFACT,
            'c63' => WatchlistBacktestC70ProductionDeploymentExecutionReviewService::DEFAULT_C63_ARTIFACT,
            'c62' => WatchlistBacktestC70ProductionDeploymentExecutionReviewService::DEFAULT_C62_ARTIFACT,
            'c61' => WatchlistBacktestC70ProductionDeploymentExecutionReviewService::DEFAULT_C61_ARTIFACT,
            'c60' => WatchlistBacktestC70ProductionDeploymentExecutionReviewService::DEFAULT_C60_ARTIFACT,
        ];
        $locks = [];
        foreach ($paths as $key => $path) {
            $payload = json_decode((string) file_get_contents($path), true);
            $locks[$key] = [(string) ($payload['artifact_hash'] ?? ''), strtoupper((string) sha1_file($path))];
        }
        return $locks;
    }

    private function indexByCode(array $rows): array
    {
        $indexed = [];
        foreach ($rows as $row) {
            $indexed[$row['candidate_code']] = $row;
        }
        return $indexed;
    }
}

<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC69ProductionDeploymentPrepOrBridgeReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC69ProductionDeploymentPrepOrBridgeReviewServiceTest extends TestCase
{
    private string $output = 'storage/app/watchlist/backtest/.tmp-c69-test-output.json';
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

    public function test_c69_runtime_passes_primary_and_backup_from_locked_c68_evidence(): void
    {
        $result = $this->runService();

        $this->assertSame('C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_PASSED_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertSame('C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_PASSED_PRIMARY_AND_BACKUP', $result['reason_code']);
        $this->assertTrue($result['production_deployment_prep_or_bridge_review_executed']);
        $this->assertTrue($result['production_deployment_prep_or_bridge_review_pass']);
        $this->assertTrue($result['production_catalog_lock_allowed']);
        $this->assertTrue($result['production_catalog_activation_allowed']);
        $this->assertTrue($result['production_catalog_activation_execution_allowed']);
        $this->assertTrue($result['production_catalog_activation_execution_performed']);
        $this->assertTrue($result['production_catalog_activated']);
        $this->assertFalse($result['production_catalog_runtime_wired']);
        $this->assertTrue($result['production_deployment_prep_allowed']);
        $this->assertTrue($result['production_deployment_execution_review_allowed']);
        $this->assertFalse($result['production_deployment_allowed']);
        $this->assertFalse($result['production_deployment_executed']);
        $this->assertTrue($result['plan_confirm_wiring_prep_allowed']);
        $this->assertFalse($result['plan_confirm_mutation_allowed']);
        $this->assertFalse($result['plan_confirm_mutated']);
        $this->assertFalse($result['plan_confirm_runtime_reads_activated_catalog']);
        $this->assertFalse($result['production_ready']);
        $this->assertSame('C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW', $result['next_step_recommendation']);
        $this->assertFileExists($this->output);
    }

    public function test_c69_artifact_records_all_required_sections(): void
    {
        $this->runService();
        $run = $this->readOutput();

        foreach ([
            'source_artifact_locks',
            'database_dictionary_read_summary',
            'c68_lock_validation_summary',
            'c67_lineage_validation_summary',
            'c66_lineage_validation_summary',
            'c65_lineage_validation_summary',
            'c64_lineage_validation_summary',
            'c63_lineage_validation_summary',
            'c62_lineage_validation_summary',
            'c61_lineage_validation_summary',
            'c60_lineage_validation_summary',
            'candidate_scope_freeze_summary',
            'production_deployment_bridge_candidate_scorecard',
            'deployment_bridge_readiness_decision',
            'bridge_contract_review_summary',
            'plan_confirm_wiring_readiness_summary',
            'feature_flag_kill_switch_rollback_summary',
            'smoke_test_shadow_read_plan_summary',
            'bad_month_bridge_review_results',
            'weak_regime_bridge_review_results',
            'source_bias_shared_core_bridge_review_summary',
            'production_mutation_safety_summary',
            'documentation_governance_summary',
            'c65_cleanup_note_summary',
            'c70_readiness_decision',
            'failure_attribution_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }
    }

    public function test_c69_validates_c68_artifact_hash_and_file_sha1(): void
    {
        $hashResult = $this->execute(['expectedC68Hash' => '0000000000000000000000000000000000000000']);
        $this->assertSame('C69_BLOCKED_C68_ARTIFACT_LOCK_MISMATCH', $hashResult['status']);
        $this->assertFalse($hashResult['c68_hash_match']);

        $shaResult = $this->execute(['expectedC68FileSha1' => '0000000000000000000000000000000000000000']);
        $this->assertSame('C69_BLOCKED_C68_FILE_SHA1_LOCK_MISMATCH', $shaResult['status']);
        $this->assertFalse($shaResult['c68_file_sha1_match']);
    }

    public function test_c69_rejects_missing_c68_artifact(): void
    {
        $result = $this->execute(['c68Artifact' => 'storage/app/watchlist/backtest/missing-c68.json']);

        $this->assertSame('C69_BLOCKED_MISSING_C68_ARTIFACT', $result['status']);
        $this->assertFalse($result['production_deployment_prep_or_bridge_review_executed']);
    }

    public function test_c69_rejects_c68_status_reason_and_activation_mismatches(): void
    {
        $cases = [
            ['status', 'BROKEN_STATUS', 'C69_BLOCKED_C68_STATUS_OR_REASON_MISMATCH'],
            ['reason_code', 'BROKEN_REASON', 'C69_BLOCKED_C68_STATUS_OR_REASON_MISMATCH'],
            ['production_catalog_activation_execution_review_pass', false, 'C69_BLOCKED_C68_ACTIVATION_EXECUTION_REVIEW_NOT_PASSED'],
            ['production_catalog_activation_execution_performed', false, 'C69_BLOCKED_C68_ACTIVATION_EXECUTION_NOT_PERFORMED'],
            ['production_catalog_activated', false, 'C69_BLOCKED_C68_CONTROLLED_CATALOG_NOT_ACTIVATED'],
        ];

        foreach ($cases as $case) {
            [$path, $hash, $sha1] = $this->writeMutatedArtifact(WatchlistBacktestC69ProductionDeploymentPrepOrBridgeReviewService::DEFAULT_C68_ARTIFACT, function (array $c68) use ($case): array {
                $c68[$case[0]] = $case[1];
                return $c68;
            }, 'c68-'.$case[0]);

            $result = $this->execute(['c68Artifact' => $path, 'expectedC68Hash' => $hash, 'expectedC68FileSha1' => $sha1]);
            $this->assertSame($case[2], $result['status'], $case[0]);
        }
    }

    public function test_c69_rejects_c68_controlled_activation_record_mismatch(): void
    {
        [$path, $hash, $sha1] = $this->writeMutatedArtifact(WatchlistBacktestC69ProductionDeploymentPrepOrBridgeReviewService::DEFAULT_C68_ARTIFACT, function (array $c68): array {
            $c68['production_catalog_activation_record']['catalog_activation_record_runtime_consumable'] = true;
            return $c68;
        }, 'c68-controlled-record');

        $result = $this->execute(['c68Artifact' => $path, 'expectedC68Hash' => $hash, 'expectedC68FileSha1' => $sha1]);

        $this->assertSame('C69_BLOCKED_C68_CONTROLLED_CATALOG_ACTIVATION_RECORD_MISMATCH', $result['status']);
    }

    public function test_c69_validates_nested_c69_readiness_path_not_top_level_aliases(): void
    {
        [$path, $hash, $sha1] = $this->writeMutatedArtifact(WatchlistBacktestC69ProductionDeploymentPrepOrBridgeReviewService::DEFAULT_C68_ARTIFACT, function (array $c68): array {
            $c68['candidate_ready_for_c69_count'] = 0;
            $c68['c69_recommendation'] = 'BROKEN_TOP_LEVEL_ALIAS';
            return $c68;
        }, 'c68-top-level-readiness-alias');

        $result = $this->execute(['c68Artifact' => $path, 'expectedC68Hash' => $hash, 'expectedC68FileSha1' => $sha1]);
        $this->assertSame('C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_PASSED_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertTrue($result['c68_lock_validation_summary']['c69_readiness_nested_path_validated']);
        $this->assertFalse($result['c68_lock_validation_summary']['top_level_alias_used_for_c68_source_validation']);
    }

    public function test_c69_rejects_nested_c69_readiness_mismatches(): void
    {
        $count = $this->mutateC68AndExecute(function (array $c68): array {
            $c68['c69_readiness_decision']['candidate_ready_for_c69_count'] = 1;
            return $c68;
        }, 'c68-nested-count');
        $this->assertSame('C69_BLOCKED_C68_C69_READINESS_COUNT_MISMATCH', $count['status']);

        $recommendation = $this->mutateC68AndExecute(function (array $c68): array {
            $c68['c69_readiness_decision']['c69_recommendation'] = 'C69_BROKEN';
            return $c68;
        }, 'c68-nested-recommendation');
        $this->assertSame('C69_BLOCKED_C68_RECOMMENDATION_MISMATCH', $recommendation['status']);
    }

    public function test_c69_rejects_c68_runtime_or_live_flags(): void
    {
        $cases = [
            ['production_catalog_runtime_wired', true, 'C69_BLOCKED_C68_RUNTIME_ALREADY_WIRED'],
            ['production_deployment_allowed', true, 'C69_BLOCKED_C68_DEPLOYMENT_ALREADY_EXECUTED'],
            ['production_deployment_executed', true, 'C69_BLOCKED_C68_DEPLOYMENT_ALREADY_EXECUTED'],
            ['plan_confirm_mutation_allowed', true, 'C69_BLOCKED_C68_PLAN_CONFIRM_ALREADY_MUTATED'],
            ['plan_confirm_mutated', true, 'C69_BLOCKED_C68_PLAN_CONFIRM_ALREADY_MUTATED'],
            ['plan_confirm_runtime_reads_activated_catalog', true, 'C69_BLOCKED_C68_PLAN_CONFIRM_ALREADY_READING_ACTIVATED_CATALOG'],
        ];

        foreach ($cases as $case) {
            $result = $this->mutateC68AndExecute(function (array $c68) use ($case): array {
                $c68[$case[0]] = $case[1];
                return $c68;
            }, 'c68-'.$case[0]);
            $this->assertSame($case[2], $result['status'], $case[0]);
        }
    }

    public function test_c69_validates_all_lineage_artifacts(): void
    {
        foreach ([
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
            $this->assertSame('C69_BLOCKED_LINEAGE_LOCK_MISMATCH', $result['status'], $key);
        }
    }

    public function test_c69_records_database_dictionary_rule(): void
    {
        $this->runService();
        $run = $this->readOutput();

        $this->assertTrue($run['database_dictionary_read_summary']['dictionary_rule_acknowledged']);
        $this->assertTrue($run['database_dictionary_read_summary']['dictionary_read_rule_complied']);
        $this->assertFalse($run['database_dictionary_read_summary']['dictionary_missing_coverage_detected']);
        $this->assertSame('market_benchmark_indicators.roc_20', $run['database_dictionary_read_summary']['market_index_mapping']['market_index_roc20_source']);
        $this->assertSame('market_benchmark_indicators.ma20_slope_pct', $run['database_dictionary_read_summary']['market_index_mapping']['market_index_ma20_slope_pct_source']);
        $this->assertSame('IHSG', $run['database_dictionary_read_summary']['market_index_mapping']['benchmark_code']);
        $this->assertTrue($run['database_dictionary_read_summary']['asof_safe']);
    }

    public function test_c69_candidate_scope_freeze_comes_from_c68_locked_decision(): void
    {
        $this->runService();
        $run = $this->readOutput();

        $scope = $run['candidate_scope_freeze_summary'];
        $this->assertTrue($scope['candidate_scope_freeze_completed']);
        $this->assertSame('C68_LOCKED_PRODUCTION_CATALOG_ACTIVATION_EXECUTION_DECISION', $scope['candidate_scope_source']);
        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $scope['primary_candidate_code']);
        $this->assertSame(['C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION'], $scope['backup_candidate_codes']);
        $this->assertSame(['C61_A01_B01_WEAK_REGIME_QUALITY_FIRST'], $scope['comparator_only_candidate_codes']);
        $this->assertFalse($scope['candidate_scope_changed_after_c68']);
        $this->assertFalse($scope['candidate_scope_changed_after_c67']);
        $this->assertFalse($scope['candidate_scope_changed_after_c66']);
        $this->assertFalse($scope['new_candidate_created']);
        $this->assertFalse($scope['selection_rule_changed']);
        $this->assertFalse($scope['parameter_changed']);
        $this->assertFalse($scope['oos_result_used_for_new_ranking']);
        $this->assertFalse($scope['a01_promoted']);
    }

    public function test_c69_rejects_candidate_scope_mismatch_or_a01_promotion(): void
    {
        $primary = $this->mutateC68AndExecute(function (array $c68): array {
            foreach ($c68['production_catalog_activation_execution_candidate_scorecard'] as &$row) {
                if ($row['candidate_code'] === 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE') {
                    $row['c68_role'] = 'BROKEN_PRIMARY';
                }
            }
            unset($row);
            return $c68;
        }, 'c68-primary-scope');
        $this->assertSame('C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $primary['status']);

        $a01 = $this->mutateC68AndExecute(function (array $c68): array {
            foreach ($c68['production_catalog_activation_execution_candidate_scorecard'] as &$row) {
                if ($row['candidate_code'] === 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST') {
                    $row['c68_role'] = 'backup_production_catalog_activation_execution_candidate';
                    $row['candidate_active_in_production_catalog'] = true;
                }
            }
            unset($row);
            $c68['c69_readiness_decision']['candidate_codes'][] = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';
            return $c68;
        }, 'c68-a01-promoted');
        $this->assertSame('C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $a01['status']);
    }

    public function test_c69_bridge_contract_plan_confirm_feature_flag_rollback_smoke_and_shadow_summaries_pass(): void
    {
        $this->runService();
        $run = $this->readOutput();

        $this->assertTrue($run['bridge_contract_review_summary']['bridge_contract_review_completed']);
        $this->assertTrue($run['bridge_contract_review_summary']['bridge_contract_pass']);
        $this->assertTrue($run['bridge_contract_review_summary']['runtime_consumer_contract_pass']);
        $this->assertFalse($run['bridge_contract_review_summary']['bridge_contract_runtime_active']);
        $this->assertFalse($run['bridge_contract_review_summary']['plan_confirm_runtime_change_executed']);
        $this->assertFalse($run['bridge_contract_review_summary']['production_catalog_runtime_wired']);

        $this->assertTrue($run['plan_confirm_wiring_readiness_summary']['plan_confirm_wiring_readiness_pass']);
        $this->assertFalse($run['plan_confirm_wiring_readiness_summary']['plan_confirm_wiring_runtime_active']);
        $this->assertTrue($run['plan_confirm_wiring_readiness_summary']['plan_confirm_bridge_requires_c70_or_later']);
        $this->assertTrue($run['plan_confirm_wiring_readiness_summary']['plan_confirm_rollout_requires_explicit_operator_approval']);

        $this->assertTrue($run['feature_flag_kill_switch_rollback_summary']['feature_flag_or_kill_switch_pass']);
        $this->assertTrue($run['feature_flag_kill_switch_rollback_summary']['feature_flag_default_off']);
        $this->assertTrue($run['feature_flag_kill_switch_rollback_summary']['kill_switch_available']);
        $this->assertTrue($run['feature_flag_kill_switch_rollback_summary']['rollback_plan_pass']);
        $this->assertFalse($run['feature_flag_kill_switch_rollback_summary']['destructive_migration_required']);
        $this->assertFalse($run['feature_flag_kill_switch_rollback_summary']['irreversible_mutation_detected']);

        $this->assertTrue($run['smoke_test_shadow_read_plan_summary']['smoke_test_plan_pass']);
        $this->assertTrue($run['smoke_test_shadow_read_plan_summary']['smoke_test_commands_defined']);
        $this->assertTrue($run['smoke_test_shadow_read_plan_summary']['shadow_read_plan_pass']);
        $this->assertFalse($run['smoke_test_shadow_read_plan_summary']['shadow_read_runtime_active']);
        $this->assertTrue($run['smoke_test_shadow_read_plan_summary']['shadow_read_does_not_change_plan_confirm_output']);
    }

    public function test_c69_rejects_missing_bridge_feature_kill_rollback_smoke_or_shadow_plans(): void
    {
        $cases = [
            ['force_bridge_contract_missing', 'C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_REJECTED_BRIDGE_CONTRACT_MISSING'],
            ['force_feature_flag_missing', 'C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH_MISSING'],
            ['force_kill_switch_missing', 'C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH_MISSING'],
            ['force_rollback_plan_missing', 'C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_REJECTED_ROLLBACK_PLAN_MISSING'],
            ['force_smoke_test_plan_missing', 'C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_REJECTED_SMOKE_TEST_PLAN_MISSING'],
            ['force_shadow_read_plan_missing', 'C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_REJECTED_SHADOW_READ_PLAN_MISSING'],
        ];
        foreach ($cases as $case) {
            $result = $this->execute([], [$case[0] => true]);
            $this->assertSame($case[1], $result['status'], $case[0]);
            $this->assertFalse($result['production_deployment_allowed']);
            $this->assertFalse($result['plan_confirm_mutation_allowed']);
        }
    }

    public function test_c69_retains_bad_month_and_weak_regime_risk_governance(): void
    {
        $this->runService();
        $run = $this->readOutput();

        $bad = $this->indexByCode($run['bad_month_bridge_review_results']);
        $this->assertSame('2026-03', $bad['C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE']['worst_month']);
        $this->assertSame('2025-10', $bad['C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION']['worst_month']);
        foreach ($bad as $row) {
            $this->assertTrue($row['bad_month_bridge_review_completed']);
            $this->assertTrue($row['bad_month_governance_pass']);
            $this->assertTrue($row['documented_bad_month_risk_retained']);
            $this->assertFalse($row['bad_month_removed']);
            $this->assertFalse($row['bad_month_risk_hidden']);
            $this->assertSame('MODERATE', $row['bad_month_risk_level']);
            $this->assertSame('PASS_WITH_DOCUMENTED_RISK', $row['bad_month_governance_decision']);
            $this->assertFalse($row['production_deployment_prep_risk_free_claim']);
        }

        $weak = $this->indexByCode($run['weak_regime_bridge_review_results']);
        foreach ($weak as $row) {
            $this->assertTrue($row['weak_regime_bridge_review_completed']);
            $this->assertTrue($row['weak_regime_governance_pass']);
            $this->assertTrue($row['weak_regime_retained']);
            $this->assertFalse($row['weak_regime_removed']);
            $this->assertSame('SUFFICIENT', $row['weak_regime_sample_status']);
            $this->assertFalse($row['weak_regime_sample_collapse_detected']);
            $this->assertSame('MODERATE', $row['weak_regime_risk_level']);
            $this->assertSame('PASS_WITH_DOCUMENTED_RISK', $row['weak_regime_governance_decision']);
            $this->assertFalse($row['production_deployment_prep_ignores_weak_regime_risk']);
        }
    }

    public function test_c69_rejects_missing_bad_month_or_weak_regime_risk(): void
    {
        $bad = $this->mutateC68AndExecute(function (array $c68): array {
            $c68['bad_month_activation_execution_review_results'][0]['bad_month_risk_level'] = 'LOW';
            return $c68;
        }, 'c68-bad-month-low');
        $this->assertSame('C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_REJECTED_BAD_MONTH_GOVERNANCE', $bad['status']);

        $weak = $this->mutateC68AndExecute(function (array $c68): array {
            $c68['weak_regime_activation_execution_review_results'][1]['weak_regime_sample_collapse_detected'] = true;
            return $c68;
        }, 'c68-weak-regime-collapse');
        $this->assertSame('C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_REJECTED_WEAK_REGIME_GOVERNANCE', $weak['status']);
    }

    public function test_c69_source_bias_shared_core_and_fallback_governance(): void
    {
        $this->runService();
        $run = $this->readOutput();
        $summary = $run['source_bias_shared_core_bridge_review_summary'];

        $this->assertTrue($summary['source_bias_shared_core_bridge_review_completed']);
        $this->assertTrue($summary['source_bias_governance_pass']);
        $this->assertTrue($summary['shared_core_governance_pass']);
        $this->assertSame('DOCUMENTED_NOT_HIGH', $summary['source_bias_risk_level']);
        $this->assertSame('LOW', $summary['shared_core_risk_level']);
        $this->assertTrue($summary['parent_diversity_sufficient']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $summary['backup_fallback_candidate_code']);
        $this->assertTrue($summary['backup_fallback_requires_explicit_bridge_rule']);
        $this->assertTrue($summary['a01_remains_comparator_only']);
        $this->assertFalse($summary['a01_promoted']);
        $this->assertFalse($summary['a01_used_as_runtime_fallback']);
    }

    public function test_c69_rejects_source_bias_or_shared_core_high(): void
    {
        $sourceBias = $this->execute([], ['force_source_bias_high' => true]);
        $this->assertSame('C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_REJECTED_SOURCE_BIAS_OR_SHARED_CORE', $sourceBias['status']);

        $sharedCore = $this->execute([], ['force_shared_core_high' => true]);
        $this->assertSame('C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_REJECTED_SOURCE_BIAS_OR_SHARED_CORE', $sharedCore['status']);
    }

    public function test_c69_production_mutation_safety_and_c65_cleanup_note(): void
    {
        $this->runService();
        $run = $this->readOutput();
        $safety = $run['production_mutation_safety_summary'];

        $this->assertTrue($safety['production_mutation_safety_review_completed']);
        $this->assertTrue($safety['production_mutation_safety_pass']);
        $this->assertTrue($safety['production_deployment_prep_decision_created']);
        $this->assertTrue($safety['production_deployment_bridge_plan_created']);
        $this->assertTrue($safety['production_deployment_execution_review_allowed']);
        $this->assertTrue($safety['plan_confirm_wiring_prep_allowed']);
        $this->assertFalse($safety['production_catalog_runtime_wired']);
        $this->assertFalse($safety['production_deployment_allowed']);
        $this->assertFalse($safety['production_deployment_executed']);
        $this->assertFalse($safety['plan_confirm_mutation_allowed']);
        $this->assertFalse($safety['plan_confirm_mutated']);
        $this->assertFalse($safety['plan_confirm_runtime_reads_activated_catalog']);
        $this->assertFalse($safety['selection_changed_after_c68']);
        $this->assertFalse($safety['parameter_changed_after_c68']);
        $this->assertFalse($safety['new_candidate_created']);
        $this->assertFalse($safety['oos_reused_for_ranking']);
        $this->assertFalse($safety['latest_shortcut_used']);
        $this->assertFalse($safety['max_date_shortcut_used']);
        $this->assertFalse($safety['future_lookup_detected']);
        $this->assertFalse($safety['return_fields_used_for_selection']);

        $this->assertSame('C65_OOS_FAILURE_ATTRIBUTION_IS_ONLY', $run['c65_cleanup_note_summary']['legacy_repair_recommendation']);
        $this->assertTrue($run['c65_cleanup_note_summary']['legacy_repair_recommendation_non_blocking']);
        $this->assertSame('NOT_REQUIRED', $run['c65_cleanup_note_summary']['normalized_repair_recommendation']);
        $this->assertFalse($run['c65_cleanup_note_summary']['c65_failure_repair_required']);
    }

    public function test_c69_scorecard_marks_e02_and_b01_ready_and_a01_comparator_only(): void
    {
        $this->runService();
        $run = $this->readOutput();
        $rows = $this->indexByCode($run['production_deployment_bridge_candidate_scorecard']);

        $this->assertSame('primary_production_deployment_bridge_candidate', $rows['C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE']['c69_role']);
        $this->assertTrue($rows['C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE']['production_deployment_prep_or_bridge_review_pass']);
        $this->assertTrue($rows['C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE']['candidate_ready_for_deployment_execution_review']);
        $this->assertTrue($rows['C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE']['candidate_active_in_controlled_catalog']);

        $this->assertSame('backup_production_deployment_bridge_candidate', $rows['C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION']['c69_role']);
        $this->assertTrue($rows['C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION']['production_deployment_prep_or_bridge_review_pass']);
        $this->assertTrue($rows['C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION']['candidate_ready_for_deployment_execution_review']);
        $this->assertTrue($rows['C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION']['candidate_active_in_controlled_catalog']);

        $this->assertSame('comparator_only', $rows['C61_A01_B01_WEAK_REGIME_QUALITY_FIRST']['c69_role']);
        $this->assertFalse($rows['C61_A01_B01_WEAK_REGIME_QUALITY_FIRST']['production_deployment_prep_or_bridge_review_pass']);
        $this->assertFalse($rows['C61_A01_B01_WEAK_REGIME_QUALITY_FIRST']['candidate_ready_for_deployment_execution_review']);
        $this->assertFalse($rows['C61_A01_B01_WEAK_REGIME_QUALITY_FIRST']['candidate_active_in_controlled_catalog']);
        $this->assertSame(['C69_A01_REMAINS_COMPARATOR_ONLY'], $rows['C61_A01_B01_WEAK_REGIME_QUALITY_FIRST']['failure_reason_codes']);

        foreach ($rows as $row) {
            $this->assertFalse($row['production_catalog_runtime_wired']);
            $this->assertFalse($row['production_deployment_allowed']);
            $this->assertFalse($row['production_deployment_executed']);
            $this->assertFalse($row['plan_confirm_mutation_allowed']);
            $this->assertFalse($row['plan_confirm_mutated']);
            $this->assertFalse($row['plan_confirm_runtime_reads_activated_catalog']);
            $this->assertTrue($row['deployment_non_execution_pass']);
            $this->assertTrue($row['plan_confirm_non_mutation_pass']);
        }
    }

    public function test_c69_c70_readiness_decision_is_review_only(): void
    {
        $this->runService();
        $run = $this->readOutput();
        $decision = $run['c70_readiness_decision'];

        $this->assertTrue($decision['validation_completed']);
        $this->assertSame(2, $decision['candidate_ready_for_c70_count']);
        $this->assertSame([
            'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE',
            'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION',
        ], $decision['candidate_codes']);
        $this->assertSame('C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW', $decision['c70_recommendation']);
        $this->assertTrue($decision['production_deployment_prep_allowed']);
        $this->assertTrue($decision['production_deployment_execution_review_allowed']);
        $this->assertTrue($decision['plan_confirm_wiring_prep_allowed']);
        $this->assertFalse($decision['production_catalog_runtime_wired']);
        $this->assertFalse($decision['production_deployment_allowed']);
        $this->assertFalse($decision['production_deployment_executed']);
        $this->assertFalse($decision['plan_confirm_mutation_allowed']);
        $this->assertFalse($decision['plan_confirm_mutated']);
        $this->assertFalse($decision['plan_confirm_runtime_reads_activated_catalog']);
    }

    private function runService(): array
    {
        return $this->execute();
    }

    private function execute(array $override = [], array $options = []): array
    {
        $service = new WatchlistBacktestC69ProductionDeploymentPrepOrBridgeReviewService();
        return $service->execute(
            (string) ($override['c68Artifact'] ?? WatchlistBacktestC69ProductionDeploymentPrepOrBridgeReviewService::DEFAULT_C68_ARTIFACT),
            (string) ($override['expectedC68Hash'] ?? WatchlistBacktestC69ProductionDeploymentPrepOrBridgeReviewService::DEFAULT_EXPECTED_C68_HASH),
            (string) ($override['expectedC68FileSha1'] ?? WatchlistBacktestC69ProductionDeploymentPrepOrBridgeReviewService::DEFAULT_EXPECTED_C68_FILE_SHA1),
            (string) ($override['c67Artifact'] ?? WatchlistBacktestC69ProductionDeploymentPrepOrBridgeReviewService::DEFAULT_C67_ARTIFACT),
            (string) ($override['expectedC67Hash'] ?? WatchlistBacktestC69ProductionDeploymentPrepOrBridgeReviewService::DEFAULT_EXPECTED_C67_HASH),
            (string) ($override['expectedC67FileSha1'] ?? WatchlistBacktestC69ProductionDeploymentPrepOrBridgeReviewService::DEFAULT_EXPECTED_C67_FILE_SHA1),
            (string) ($override['c66Artifact'] ?? WatchlistBacktestC69ProductionDeploymentPrepOrBridgeReviewService::DEFAULT_C66_ARTIFACT),
            (string) ($override['expectedC66Hash'] ?? WatchlistBacktestC69ProductionDeploymentPrepOrBridgeReviewService::DEFAULT_EXPECTED_C66_HASH),
            (string) ($override['expectedC66FileSha1'] ?? WatchlistBacktestC69ProductionDeploymentPrepOrBridgeReviewService::DEFAULT_EXPECTED_C66_FILE_SHA1),
            (string) ($override['c65Artifact'] ?? WatchlistBacktestC69ProductionDeploymentPrepOrBridgeReviewService::DEFAULT_C65_ARTIFACT),
            (string) ($override['expectedC65Hash'] ?? WatchlistBacktestC69ProductionDeploymentPrepOrBridgeReviewService::DEFAULT_EXPECTED_C65_HASH),
            (string) ($override['expectedC65FileSha1'] ?? WatchlistBacktestC69ProductionDeploymentPrepOrBridgeReviewService::DEFAULT_EXPECTED_C65_FILE_SHA1),
            (string) ($override['c64Artifact'] ?? WatchlistBacktestC69ProductionDeploymentPrepOrBridgeReviewService::DEFAULT_C64_ARTIFACT),
            (string) ($override['expectedC64Hash'] ?? WatchlistBacktestC69ProductionDeploymentPrepOrBridgeReviewService::DEFAULT_EXPECTED_C64_HASH),
            (string) ($override['expectedC64FileSha1'] ?? WatchlistBacktestC69ProductionDeploymentPrepOrBridgeReviewService::DEFAULT_EXPECTED_C64_FILE_SHA1),
            (string) ($override['c63Artifact'] ?? WatchlistBacktestC69ProductionDeploymentPrepOrBridgeReviewService::DEFAULT_C63_ARTIFACT),
            (string) ($override['expectedC63Hash'] ?? WatchlistBacktestC69ProductionDeploymentPrepOrBridgeReviewService::DEFAULT_EXPECTED_C63_HASH),
            (string) ($override['expectedC63FileSha1'] ?? WatchlistBacktestC69ProductionDeploymentPrepOrBridgeReviewService::DEFAULT_EXPECTED_C63_FILE_SHA1),
            (string) ($override['c62Artifact'] ?? WatchlistBacktestC69ProductionDeploymentPrepOrBridgeReviewService::DEFAULT_C62_ARTIFACT),
            (string) ($override['expectedC62Hash'] ?? WatchlistBacktestC69ProductionDeploymentPrepOrBridgeReviewService::DEFAULT_EXPECTED_C62_HASH),
            (string) ($override['expectedC62FileSha1'] ?? WatchlistBacktestC69ProductionDeploymentPrepOrBridgeReviewService::DEFAULT_EXPECTED_C62_FILE_SHA1),
            (string) ($override['c61Artifact'] ?? WatchlistBacktestC69ProductionDeploymentPrepOrBridgeReviewService::DEFAULT_C61_ARTIFACT),
            (string) ($override['expectedC61Hash'] ?? WatchlistBacktestC69ProductionDeploymentPrepOrBridgeReviewService::DEFAULT_EXPECTED_C61_HASH),
            (string) ($override['expectedC61FileSha1'] ?? WatchlistBacktestC69ProductionDeploymentPrepOrBridgeReviewService::DEFAULT_EXPECTED_C61_FILE_SHA1),
            (string) ($override['c60Artifact'] ?? WatchlistBacktestC69ProductionDeploymentPrepOrBridgeReviewService::DEFAULT_C60_ARTIFACT),
            (string) ($override['expectedC60Hash'] ?? WatchlistBacktestC69ProductionDeploymentPrepOrBridgeReviewService::DEFAULT_EXPECTED_C60_HASH),
            (string) ($override['expectedC60FileSha1'] ?? WatchlistBacktestC69ProductionDeploymentPrepOrBridgeReviewService::DEFAULT_EXPECTED_C60_FILE_SHA1),
            $this->output,
            array_merge(['overwrite' => true, 'executed_at' => '2026-06-24T00:00:00+00:00'], $options)
        );
    }

    private function readOutput(): array
    {
        return json_decode((string) file_get_contents($this->output), true);
    }

    private function mutateC68AndExecute(callable $mutator, string $suffix): array
    {
        [$path, $hash, $sha1] = $this->writeMutatedArtifact(WatchlistBacktestC69ProductionDeploymentPrepOrBridgeReviewService::DEFAULT_C68_ARTIFACT, $mutator, $suffix);
        return $this->execute(['c68Artifact' => $path, 'expectedC68Hash' => $hash, 'expectedC68FileSha1' => $sha1]);
    }

    private function writeMutatedArtifact(string $source, callable $mutator, string $suffix): array
    {
        $payload = json_decode((string) file_get_contents($source), true);
        $payload = $mutator($payload);
        unset($payload['artifact_hash']);
        $payload['artifact_hash'] = sha1(json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $path = 'storage/app/watchlist/backtest/.tmp-'.$suffix.'-'.count($this->tmpFiles).'.json';
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $this->tmpFiles[] = $path;
        return [$path, $payload['artifact_hash'], strtoupper((string) sha1_file($path))];
    }

    private function indexByCode(array $rows): array
    {
        $indexed = [];
        foreach ($rows as $row) {
            $indexed[(string) $row['candidate_code']] = $row;
        }
        return $indexed;
    }
}

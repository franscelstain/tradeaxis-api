<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC68ProductionCatalogActivationExecutionReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC68ProductionCatalogActivationExecutionReviewServiceTest extends TestCase
{
    private string $output = 'storage/app/watchlist/backtest/.tmp-c68-test-output.json';
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

    public function test_c68_runtime_passes_primary_and_backup_from_locked_c67_evidence(): void
    {
        $result = $this->runService();

        $this->assertSame('C68_PRODUCTION_CATALOG_ACTIVATION_EXECUTION_REVIEW_PASSED_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertSame('C68_PRODUCTION_CATALOG_ACTIVATION_EXECUTION_REVIEW_PASSED_PRIMARY_AND_BACKUP', $result['reason_code']);
        $this->assertTrue($result['production_catalog_activation_execution_review_executed']);
        $this->assertTrue($result['production_catalog_activation_execution_review_pass']);
        $this->assertTrue($result['production_catalog_lock_allowed']);
        $this->assertTrue($result['production_catalog_activation_allowed']);
        $this->assertTrue($result['production_catalog_activation_execution_allowed']);
        $this->assertTrue($result['production_catalog_activation_execution_performed']);
        $this->assertTrue($result['production_catalog_activated']);
        $this->assertFalse($result['production_catalog_runtime_wired']);
        $this->assertFalse($result['production_deployment_allowed']);
        $this->assertFalse($result['production_deployment_executed']);
        $this->assertFalse($result['plan_confirm_mutation_allowed']);
        $this->assertFalse($result['plan_confirm_mutated']);
        $this->assertFalse($result['plan_confirm_runtime_reads_activated_catalog']);
        $this->assertFalse($result['production_ready']);
        $this->assertSame('C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW', $result['next_step_recommendation']);
        $this->assertFileExists($this->output);
    }

    public function test_c68_artifact_records_all_required_sections(): void
    {
        $this->runService();
        $run = $this->readOutput();

        foreach ([
            'source_artifact_locks',
            'database_dictionary_read_summary',
            'c67_lock_validation_summary',
            'c66_lineage_validation_summary',
            'c65_lineage_validation_summary',
            'c64_lineage_validation_summary',
            'c63_lineage_validation_summary',
            'c62_lineage_validation_summary',
            'c61_lineage_validation_summary',
            'c60_lineage_validation_summary',
            'candidate_scope_freeze_summary',
            'production_catalog_activation_execution_candidate_scorecard',
            'production_catalog_activation_record',
            'bad_month_activation_execution_review_results',
            'weak_regime_activation_execution_review_results',
            'concentration_loss_cluster_governance_summary',
            'rolling_month_dependency_governance_summary',
            'source_bias_shared_core_governance_summary',
            'production_activation_execution_mutation_safety_summary',
            'documentation_governance_summary',
            'c65_cleanup_note_summary',
            'production_catalog_activation_execution_decision',
            'c69_readiness_decision',
            'failure_attribution_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }
    }

    public function test_c68_validates_c67_artifact_hash_and_file_sha1(): void
    {
        $hashResult = $this->execute(['expectedC67Hash' => '0000000000000000000000000000000000000000']);
        $this->assertSame('C68_BLOCKED_C67_ARTIFACT_LOCK_MISMATCH', $hashResult['status']);
        $this->assertFalse($hashResult['c67_hash_match']);

        $shaResult = $this->execute(['expectedC67FileSha1' => '0000000000000000000000000000000000000000']);
        $this->assertSame('C68_BLOCKED_C67_FILE_SHA1_LOCK_MISMATCH', $shaResult['status']);
        $this->assertFalse($shaResult['c67_file_sha1_match']);
    }

    public function test_c68_rejects_missing_c67_artifact(): void
    {
        $result = $this->execute(['c67Artifact' => 'storage/app/watchlist/backtest/missing-c67.json']);

        $this->assertSame('C68_BLOCKED_MISSING_C67_ARTIFACT', $result['status']);
        $this->assertFalse($result['production_catalog_activation_execution_review_executed']);
    }

    public function test_c68_rejects_c67_status_reason_and_required_flag_mismatches(): void
    {
        $cases = [
            ['status', 'BROKEN_STATUS', 'C68_BLOCKED_C67_STATUS_OR_REASON_MISMATCH'],
            ['reason_code', 'BROKEN_REASON', 'C68_BLOCKED_C67_STATUS_OR_REASON_MISMATCH'],
            ['production_catalog_activation_review_pass', false, 'C68_BLOCKED_C67_ACTIVATION_REVIEW_NOT_PASSED'],
            ['production_catalog_activation_allowed', false, 'C68_BLOCKED_C67_PRODUCTION_CATALOG_ACTIVATION_NOT_ALLOWED'],
            ['production_catalog_activation_execution_allowed', true, 'C68_BLOCKED_C67_EXECUTION_FLAG_INVALID'],
            ['production_deployment_allowed', true, 'C68_BLOCKED_C67_DEPLOYMENT_FLAG_INVALID'],
            ['plan_confirm_mutation_allowed', true, 'C68_BLOCKED_C67_PLAN_CONFIRM_MUTATION_FLAG_INVALID'],
        ];

        foreach ($cases as $case) {
            [$path, $hash, $sha1] = $this->writeMutatedArtifact(WatchlistBacktestC68ProductionCatalogActivationExecutionReviewService::DEFAULT_C67_ARTIFACT, function (array $c67) use ($case): array {
                $c67[$case[0]] = $case[1];
                return $c67;
            }, 'c67-'.$case[0]);

            $result = $this->execute(['c67Artifact' => $path, 'expectedC67Hash' => $hash, 'expectedC67FileSha1' => $sha1]);
            $this->assertSame($case[2], $result['status'], $case[0]);
        }
    }

    public function test_c68_rejects_c67_candidate_ready_for_c68_count_mismatch(): void
    {
        [$path, $hash, $sha1] = $this->writeMutatedArtifact(WatchlistBacktestC68ProductionCatalogActivationExecutionReviewService::DEFAULT_C67_ARTIFACT, function (array $c67): array {
            $c67['c68_readiness_decision']['candidate_ready_for_c68_count'] = 1;
            return $c67;
        }, 'c67-ready-count');

        $result = $this->execute(['c67Artifact' => $path, 'expectedC67Hash' => $hash, 'expectedC67FileSha1' => $sha1]);

        $this->assertSame('C68_BLOCKED_C67_C68_READINESS_COUNT_MISMATCH', $result['status']);
    }

    public function test_c68_validates_all_lineage_artifacts(): void
    {
        foreach ([
            'expectedC66Hash',
            'expectedC65Hash',
            'expectedC64Hash',
            'expectedC63Hash',
            'expectedC62Hash',
            'expectedC61Hash',
            'expectedC60Hash',
        ] as $key) {
            $result = $this->execute([$key => '0000000000000000000000000000000000000000']);
            $this->assertSame('C68_BLOCKED_LINEAGE_LOCK_MISMATCH', $result['status'], $key);
        }
    }

    public function test_c68_records_database_dictionary_rule(): void
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

    public function test_c68_candidate_scope_freeze_comes_from_c67_locked_decision(): void
    {
        $this->runService();
        $run = $this->readOutput();

        $this->assertTrue($run['candidate_scope_freeze_summary']['candidate_scope_freeze_completed']);
        $this->assertSame('C67_LOCKED_PRODUCTION_CATALOG_ACTIVATION_REVIEW_DECISION', $run['candidate_scope_freeze_summary']['candidate_scope_source']);
        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $run['candidate_scope_freeze_summary']['primary_candidate_code']);
        $this->assertSame(['C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION'], $run['candidate_scope_freeze_summary']['backup_candidate_codes']);
        $this->assertSame(['C61_A01_B01_WEAK_REGIME_QUALITY_FIRST'], $run['candidate_scope_freeze_summary']['comparator_only_candidate_codes']);
        $this->assertFalse($run['candidate_scope_freeze_summary']['candidate_scope_changed_after_c67']);
        $this->assertFalse($run['candidate_scope_freeze_summary']['candidate_scope_changed_after_c66']);
        $this->assertFalse($run['candidate_scope_freeze_summary']['candidate_scope_changed_after_c65']);
        $this->assertFalse($run['candidate_scope_freeze_summary']['new_candidate_created']);
        $this->assertFalse($run['candidate_scope_freeze_summary']['selection_rule_changed']);
        $this->assertFalse($run['candidate_scope_freeze_summary']['parameter_changed']);
        $this->assertFalse($run['candidate_scope_freeze_summary']['oos_result_used_for_new_ranking']);
        $this->assertFalse($run['candidate_scope_freeze_summary']['a01_promoted']);
    }

    public function test_c68_rejects_candidate_scope_mismatch(): void
    {
        [$path, $hash, $sha1] = $this->writeMutatedArtifact(WatchlistBacktestC68ProductionCatalogActivationExecutionReviewService::DEFAULT_C67_ARTIFACT, function (array $c67): array {
            $c67['production_catalog_activation_review_decision']['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $c67;
        }, 'c67-scope');

        $result = $this->execute(['c67Artifact' => $path, 'expectedC67Hash' => $hash, 'expectedC67FileSha1' => $sha1]);

        $this->assertSame('C68_PRODUCTION_CATALOG_ACTIVATION_EXECUTION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $result['status']);
    }

    public function test_c68_scorecard_marks_e02_active_b01_active_and_a01_comparator_only(): void
    {
        $this->runService();
        $run = $this->readOutput();
        $rows = $this->indexByCode($run['production_catalog_activation_execution_candidate_scorecard']);

        $this->assertSame('primary_production_catalog_activation_execution_candidate', $rows['C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE']['c68_role']);
        $this->assertTrue($rows['C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE']['production_catalog_activation_execution_review_pass']);
        $this->assertTrue($rows['C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE']['candidate_active_in_production_catalog']);
        $this->assertTrue($rows['C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE']['candidate_ready_for_deployment_prep_review']);
        $this->assertSame('backup_production_catalog_activation_execution_candidate', $rows['C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION']['c68_role']);
        $this->assertTrue($rows['C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION']['production_catalog_activation_execution_review_pass']);
        $this->assertTrue($rows['C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION']['candidate_active_in_production_catalog']);
        $this->assertTrue($rows['C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION']['candidate_ready_for_deployment_prep_review']);
        $this->assertSame('comparator_only', $rows['C61_A01_B01_WEAK_REGIME_QUALITY_FIRST']['c68_role']);
        $this->assertFalse($rows['C61_A01_B01_WEAK_REGIME_QUALITY_FIRST']['production_catalog_activation_execution_review_pass']);
        $this->assertFalse($rows['C61_A01_B01_WEAK_REGIME_QUALITY_FIRST']['candidate_active_in_production_catalog']);
        $this->assertSame(['C68_A01_REMAINS_COMPARATOR_ONLY'], $rows['C61_A01_B01_WEAK_REGIME_QUALITY_FIRST']['failure_reason_codes']);
    }

    public function test_c68_retains_bad_month_and_weak_regime_governance(): void
    {
        $this->runService();
        $run = $this->readOutput();

        foreach ($run['bad_month_activation_execution_review_results'] as $row) {
            $this->assertTrue($row['bad_month_activation_execution_review_completed']);
            $this->assertTrue($row['documented_bad_month_risk_retained']);
            $this->assertFalse($row['bad_month_removed']);
            $this->assertFalse($row['bad_month_risk_hidden']);
            $this->assertSame('MODERATE', $row['bad_month_risk_level']);
            $this->assertSame('PASS_WITH_DOCUMENTED_RISK', $row['bad_month_governance_decision']);
            $this->assertSame('market_down_or_sideways_high_vol', $row['worst_month_regime']);
            $this->assertFalse($row['production_activation_risk_free_claim']);
        }

        foreach ($run['weak_regime_activation_execution_review_results'] as $row) {
            $this->assertTrue($row['weak_regime_activation_execution_review_completed']);
            $this->assertSame('market_down_or_sideways_high_vol', $row['weak_regime']);
            $this->assertTrue($row['weak_regime_retained']);
            $this->assertFalse($row['weak_regime_removed']);
            $this->assertSame('SUFFICIENT', $row['weak_regime_sample_status']);
            $this->assertFalse($row['weak_regime_sample_collapse_detected']);
            $this->assertSame('MODERATE', $row['weak_regime_risk_level']);
            $this->assertSame('PASS_WITH_DOCUMENTED_RISK', $row['weak_regime_governance_decision']);
            $this->assertFalse($row['production_activation_ignores_weak_regime_risk']);
        }
    }

    public function test_c68_retains_concentration_loss_cluster_rolling_source_bias_and_shared_core_governance(): void
    {
        $this->runService();
        $run = $this->readOutput();

        $this->assertTrue($run['concentration_loss_cluster_governance_summary']['concentration_governance_pass']);
        $this->assertTrue($run['concentration_loss_cluster_governance_summary']['loss_cluster_governance_pass']);
        $this->assertFalse($run['concentration_loss_cluster_governance_summary']['concentration_regression_detected']);
        $this->assertFalse($run['concentration_loss_cluster_governance_summary']['loss_cluster_regression_detected']);
        $this->assertFalse($run['concentration_loss_cluster_governance_summary']['month_dependency_detected']);
        $this->assertFalse($run['concentration_loss_cluster_governance_summary']['sample_collapse_detected']);
        $this->assertTrue($run['rolling_month_dependency_governance_summary']['rolling_governance_pass']);
        $this->assertFalse($run['rolling_month_dependency_governance_summary']['month_dependency_detected']);
        $this->assertFalse($run['rolling_month_dependency_governance_summary']['sample_collapse_detected']);
        $this->assertTrue($run['source_bias_shared_core_governance_summary']['source_bias_governance_pass']);
        $this->assertTrue($run['source_bias_shared_core_governance_summary']['shared_core_governance_pass']);
        $this->assertSame('DOCUMENTED_NOT_HIGH', $run['source_bias_shared_core_governance_summary']['source_bias_risk_level']);
        $this->assertSame('LOW', $run['source_bias_shared_core_governance_summary']['shared_core_risk_level']);
        $this->assertTrue($run['source_bias_shared_core_governance_summary']['parent_diversity_sufficient']);
        $this->assertFalse($run['source_bias_shared_core_governance_summary']['a01_promoted']);
    }

    public function test_c68_controlled_activation_record_is_not_runtime_consumable(): void
    {
        $this->runService();
        $run = $this->readOutput();
        $record = $run['production_catalog_activation_record'];

        $this->assertTrue($record['catalog_activation_record_created']);
        $this->assertFalse($record['catalog_activation_record_runtime_consumable']);
        $this->assertFalse($record['catalog_activation_record_wired_to_plan_confirm']);
        $this->assertSame('C68_PRODUCTION_CATALOG_ACTIVATION_V1', $record['catalog_version']);
        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $record['primary_candidate_code']);
        $this->assertSame(['C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION'], $record['backup_candidate_codes']);
        $this->assertSame(['C61_A01_B01_WEAK_REGIME_QUALITY_FIRST'], $record['comparator_only_candidate_codes']);
        $this->assertSame('PRIMARY_AND_BACKUP', $record['activation_scope']);
        $this->assertTrue($record['activation_execution_performed']);
        $this->assertTrue($record['production_catalog_activated']);
        $this->assertFalse($record['production_catalog_runtime_wired']);
        $this->assertFalse($record['production_deployment_executed']);
        $this->assertFalse($record['plan_confirm_mutated']);
        $this->assertFalse($record['plan_confirm_runtime_reads_activated_catalog']);
        $this->assertTrue($record['bad_month_risk_retained']);
        $this->assertTrue($record['weak_regime_risk_retained']);
        $this->assertTrue($record['source_bias_shared_core_risk_retained']);
        $this->assertSame('C68_PRODUCTION_CATALOG_ACTIVATION_EXECUTION_REVIEW', $record['created_by_run_code']);
    }

    public function test_c68_mutation_safety_keeps_deployment_and_plan_confirm_disabled(): void
    {
        $this->runService();
        $run = $this->readOutput();
        $safety = $run['production_activation_execution_mutation_safety_summary'];

        $this->assertTrue($safety['production_catalog_activation_execution_decision_created']);
        $this->assertTrue($safety['catalog_activation_record_created']);
        $this->assertFalse($safety['catalog_activation_record_runtime_consumable']);
        $this->assertTrue($safety['production_catalog_activation_execution_allowed']);
        $this->assertTrue($safety['production_catalog_activation_execution_performed']);
        $this->assertTrue($safety['production_catalog_activated']);
        $this->assertFalse($safety['production_catalog_runtime_wired']);
        $this->assertFalse($safety['production_deployment_executed']);
        $this->assertFalse($safety['plan_confirm_mutated']);
        $this->assertFalse($safety['plan_confirm_runtime_reads_activated_catalog']);
        $this->assertFalse($safety['production_deployment_allowed']);
        $this->assertFalse($safety['plan_confirm_mutation_allowed']);
        $this->assertFalse($safety['selection_changed_after_c67']);
        $this->assertFalse($safety['selection_changed_after_c66']);
        $this->assertFalse($safety['selection_changed_after_c65']);
        $this->assertFalse($safety['parameter_changed_after_c67']);
        $this->assertFalse($safety['parameter_changed_after_c66']);
        $this->assertFalse($safety['new_candidate_created']);
        $this->assertFalse($safety['oos_reused_for_ranking']);
        $this->assertFalse($safety['latest_shortcut_used']);
        $this->assertFalse($safety['max_date_shortcut_used']);
        $this->assertFalse($safety['future_lookup_detected']);
        $this->assertFalse($safety['return_fields_used_for_selection']);
        $this->assertTrue($safety['production_activation_execution_mutation_safety_pass']);
    }

    public function test_c68_c69_readiness_is_evidence_based_and_not_deployment(): void
    {
        $this->runService();
        $run = $this->readOutput();
        $decision = $run['c69_readiness_decision'];

        $this->assertTrue($decision['validation_completed']);
        $this->assertSame(2, $decision['candidate_ready_for_c69_count']);
        $this->assertSame([
            'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE',
            'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION',
        ], $decision['candidate_codes']);
        $this->assertSame('C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW', $decision['c69_recommendation']);
        $this->assertTrue($decision['production_catalog_activation_execution_allowed']);
        $this->assertTrue($decision['production_catalog_activation_execution_performed']);
        $this->assertTrue($decision['production_catalog_activated']);
        $this->assertFalse($decision['production_catalog_runtime_wired']);
        $this->assertFalse($decision['production_deployment_allowed']);
        $this->assertFalse($decision['production_deployment_executed']);
        $this->assertFalse($decision['plan_confirm_mutation_allowed']);
        $this->assertFalse($decision['plan_confirm_mutated']);
        $this->assertFalse($decision['plan_confirm_runtime_reads_activated_catalog']);
    }

    public function test_c68_candidate_cannot_pass_when_bad_month_risk_missing(): void
    {
        [$path, $hash, $sha1] = $this->writeMutatedArtifact(WatchlistBacktestC68ProductionCatalogActivationExecutionReviewService::DEFAULT_C64_ARTIFACT, function (array $c64): array {
            foreach ($c64['oos_proof_candidate_scorecard'] as &$row) {
                if ($row['candidate_code'] === 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE') {
                    unset($row['oos_worst_month']);
                }
            }
            unset($row);
            return $c64;
        }, 'c64-bad-month');

        $result = $this->execute(['c64Artifact' => $path, 'expectedC64Hash' => $hash, 'expectedC64FileSha1' => $sha1]);
        $rows = $this->indexByCode($result['production_catalog_activation_execution_candidate_scorecard']);

        $this->assertSame('C68_PRODUCTION_CATALOG_ACTIVATION_EXECUTION_REVIEW_PASSED_BACKUP_ONLY', $result['status']);
        $this->assertFalse($rows['C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE']['bad_month_governance_pass']);
        $this->assertContains('C68_BAD_MONTH_GOVERNANCE_FAIL', $rows['C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE']['failure_reason_codes']);
    }

    public function test_c68_candidate_cannot_pass_when_weak_regime_sample_collapses(): void
    {
        [$path, $hash, $sha1] = $this->writeMutatedArtifact(WatchlistBacktestC68ProductionCatalogActivationExecutionReviewService::DEFAULT_C64_ARTIFACT, function (array $c64): array {
            foreach ($c64['oos_proof_candidate_scorecard'] as &$row) {
                if ($row['candidate_code'] === 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION') {
                    $row['oos_weak_regime_sample_collapse_detected'] = true;
                }
            }
            unset($row);
            return $c64;
        }, 'c64-weak-regime');

        $result = $this->execute(['c64Artifact' => $path, 'expectedC64Hash' => $hash, 'expectedC64FileSha1' => $sha1]);
        $rows = $this->indexByCode($result['production_catalog_activation_execution_candidate_scorecard']);

        $this->assertSame('C68_PRODUCTION_CATALOG_ACTIVATION_EXECUTION_REVIEW_PASSED_PRIMARY_ONLY', $result['status']);
        $this->assertFalse($rows['C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION']['weak_regime_governance_pass']);
        $this->assertContains('C68_WEAK_REGIME_GOVERNANCE_FAIL', $rows['C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION']['failure_reason_codes']);
    }

    private function runService(): array
    {
        return $this->execute();
    }

    private function execute(array $override = []): array
    {
        $service = new WatchlistBacktestC68ProductionCatalogActivationExecutionReviewService();
        return $service->execute(
            (string) ($override['c67Artifact'] ?? WatchlistBacktestC68ProductionCatalogActivationExecutionReviewService::DEFAULT_C67_ARTIFACT),
            (string) ($override['expectedC67Hash'] ?? WatchlistBacktestC68ProductionCatalogActivationExecutionReviewService::DEFAULT_EXPECTED_C67_HASH),
            (string) ($override['expectedC67FileSha1'] ?? WatchlistBacktestC68ProductionCatalogActivationExecutionReviewService::DEFAULT_EXPECTED_C67_FILE_SHA1),
            (string) ($override['c66Artifact'] ?? WatchlistBacktestC68ProductionCatalogActivationExecutionReviewService::DEFAULT_C66_ARTIFACT),
            (string) ($override['expectedC66Hash'] ?? WatchlistBacktestC68ProductionCatalogActivationExecutionReviewService::DEFAULT_EXPECTED_C66_HASH),
            (string) ($override['expectedC66FileSha1'] ?? WatchlistBacktestC68ProductionCatalogActivationExecutionReviewService::DEFAULT_EXPECTED_C66_FILE_SHA1),
            (string) ($override['c65Artifact'] ?? WatchlistBacktestC68ProductionCatalogActivationExecutionReviewService::DEFAULT_C65_ARTIFACT),
            (string) ($override['expectedC65Hash'] ?? WatchlistBacktestC68ProductionCatalogActivationExecutionReviewService::DEFAULT_EXPECTED_C65_HASH),
            (string) ($override['expectedC65FileSha1'] ?? WatchlistBacktestC68ProductionCatalogActivationExecutionReviewService::DEFAULT_EXPECTED_C65_FILE_SHA1),
            (string) ($override['c64Artifact'] ?? WatchlistBacktestC68ProductionCatalogActivationExecutionReviewService::DEFAULT_C64_ARTIFACT),
            (string) ($override['expectedC64Hash'] ?? WatchlistBacktestC68ProductionCatalogActivationExecutionReviewService::DEFAULT_EXPECTED_C64_HASH),
            (string) ($override['expectedC64FileSha1'] ?? WatchlistBacktestC68ProductionCatalogActivationExecutionReviewService::DEFAULT_EXPECTED_C64_FILE_SHA1),
            (string) ($override['c63Artifact'] ?? WatchlistBacktestC68ProductionCatalogActivationExecutionReviewService::DEFAULT_C63_ARTIFACT),
            (string) ($override['expectedC63Hash'] ?? WatchlistBacktestC68ProductionCatalogActivationExecutionReviewService::DEFAULT_EXPECTED_C63_HASH),
            (string) ($override['expectedC63FileSha1'] ?? WatchlistBacktestC68ProductionCatalogActivationExecutionReviewService::DEFAULT_EXPECTED_C63_FILE_SHA1),
            (string) ($override['c62Artifact'] ?? WatchlistBacktestC68ProductionCatalogActivationExecutionReviewService::DEFAULT_C62_ARTIFACT),
            (string) ($override['expectedC62Hash'] ?? WatchlistBacktestC68ProductionCatalogActivationExecutionReviewService::DEFAULT_EXPECTED_C62_HASH),
            (string) ($override['expectedC62FileSha1'] ?? WatchlistBacktestC68ProductionCatalogActivationExecutionReviewService::DEFAULT_EXPECTED_C62_FILE_SHA1),
            (string) ($override['c61Artifact'] ?? WatchlistBacktestC68ProductionCatalogActivationExecutionReviewService::DEFAULT_C61_ARTIFACT),
            (string) ($override['expectedC61Hash'] ?? WatchlistBacktestC68ProductionCatalogActivationExecutionReviewService::DEFAULT_EXPECTED_C61_HASH),
            (string) ($override['expectedC61FileSha1'] ?? WatchlistBacktestC68ProductionCatalogActivationExecutionReviewService::DEFAULT_EXPECTED_C61_FILE_SHA1),
            (string) ($override['c60Artifact'] ?? WatchlistBacktestC68ProductionCatalogActivationExecutionReviewService::DEFAULT_C60_ARTIFACT),
            (string) ($override['expectedC60Hash'] ?? WatchlistBacktestC68ProductionCatalogActivationExecutionReviewService::DEFAULT_EXPECTED_C60_HASH),
            (string) ($override['expectedC60FileSha1'] ?? WatchlistBacktestC68ProductionCatalogActivationExecutionReviewService::DEFAULT_EXPECTED_C60_FILE_SHA1),
            $this->output,
            ['overwrite' => true, 'executed_at' => '2026-06-24T00:00:00+00:00']
        );
    }

    private function readOutput(): array
    {
        return json_decode((string) file_get_contents($this->output), true);
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

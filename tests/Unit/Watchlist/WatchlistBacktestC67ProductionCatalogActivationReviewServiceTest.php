<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC67ProductionCatalogActivationReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC67ProductionCatalogActivationReviewServiceTest extends TestCase
{
    private string $output = 'storage/app/watchlist/backtest/.tmp-c67-test-output.json';
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

    public function test_c67_runtime_passes_primary_and_backup_from_locked_c66_evidence(): void
    {
        $result = $this->runService();

        $this->assertSame('C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW_PASSED_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertSame('C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW_PASSED_PRIMARY_AND_BACKUP', $result['reason_code']);
        $this->assertTrue($result['production_catalog_activation_review_executed']);
        $this->assertTrue($result['production_catalog_activation_review_pass']);
        $this->assertTrue($result['production_catalog_lock_allowed']);
        $this->assertTrue($result['production_catalog_activation_allowed']);
        $this->assertFalse($result['production_catalog_activation_execution_allowed']);
        $this->assertFalse($result['production_deployment_allowed']);
        $this->assertFalse($result['plan_confirm_mutation_allowed']);
        $this->assertFalse($result['production_ready']);
        $this->assertSame('C68_PRODUCTION_CATALOG_ACTIVATION_EXECUTION_REVIEW', $result['next_step_recommendation']);
        $this->assertFileExists($this->output);
    }

    public function test_c67_artifact_records_all_required_sections(): void
    {
        $this->runService();
        $run = $this->readOutput();

        foreach ([
            'source_artifact_locks',
            'database_dictionary_read_summary',
            'c66_lock_validation_summary',
            'c65_lineage_validation_summary',
            'c64_lineage_validation_summary',
            'c63_lineage_validation_summary',
            'c62_lineage_validation_summary',
            'c61_lineage_validation_summary',
            'c60_lineage_validation_summary',
            'candidate_scope_freeze_summary',
            'production_catalog_activation_candidate_scorecard',
            'bad_month_activation_review_results',
            'weak_regime_activation_review_results',
            'concentration_loss_cluster_governance_summary',
            'rolling_month_dependency_governance_summary',
            'source_bias_shared_core_governance_summary',
            'production_activation_mutation_safety_summary',
            'documentation_governance_summary',
            'c65_cleanup_note_summary',
            'production_catalog_activation_review_decision',
            'c68_readiness_decision',
            'failure_attribution_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }
    }

    public function test_c67_validates_c66_artifact_hash_and_file_sha1(): void
    {
        $hashResult = $this->execute(['expectedC66Hash' => '0000000000000000000000000000000000000000']);
        $this->assertSame('C67_BLOCKED_C66_ARTIFACT_LOCK_MISMATCH', $hashResult['status']);
        $this->assertFalse($hashResult['c66_hash_match']);

        $shaResult = $this->execute(['expectedC66FileSha1' => '0000000000000000000000000000000000000000']);
        $this->assertSame('C67_BLOCKED_C66_FILE_SHA1_LOCK_MISMATCH', $shaResult['status']);
        $this->assertFalse($shaResult['c66_file_sha1_match']);
    }

    public function test_c67_rejects_missing_c66_artifact(): void
    {
        $result = $this->execute(['c66Artifact' => 'storage/app/watchlist/backtest/missing-c66.json']);

        $this->assertSame('C67_BLOCKED_MISSING_C66_ARTIFACT', $result['status']);
        $this->assertFalse($result['production_catalog_activation_review_executed']);
    }

    public function test_c67_rejects_c66_status_reason_lock_and_flag_mismatches(): void
    {
        $cases = [
            ['status', 'BROKEN_STATUS', 'C67_BLOCKED_C66_STATUS_OR_REASON_MISMATCH'],
            ['reason_code', 'BROKEN_REASON', 'C67_BLOCKED_C66_STATUS_OR_REASON_MISMATCH'],
            ['production_lock_review_pass', false, 'C67_BLOCKED_C66_PRODUCTION_LOCK_NOT_PASSED'],
            ['production_catalog_lock_allowed', false, 'C67_BLOCKED_C66_PRODUCTION_CATALOG_LOCK_NOT_ALLOWED'],
            ['production_catalog_activation_allowed', true, 'C67_BLOCKED_C66_ACTIVATION_FLAG_INVALID'],
            ['production_deployment_allowed', true, 'C67_BLOCKED_C66_DEPLOYMENT_FLAG_INVALID'],
            ['plan_confirm_mutation_allowed', true, 'C67_BLOCKED_C66_PLAN_CONFIRM_MUTATION_FLAG_INVALID'],
        ];

        foreach ($cases as $case) {
            [$path, $hash, $sha1] = $this->writeMutatedArtifact(WatchlistBacktestC67ProductionCatalogActivationReviewService::DEFAULT_C66_ARTIFACT, function (array $c66) use ($case): array {
                $c66[$case[0]] = $case[1];
                return $c66;
            }, 'c66-'.$case[0]);

            $result = $this->execute(['c66Artifact' => $path, 'expectedC66Hash' => $hash, 'expectedC66FileSha1' => $sha1]);
            $this->assertSame($case[2], $result['status'], $case[0]);
        }
    }

    public function test_c67_rejects_c66_candidate_ready_for_c67_count_mismatch(): void
    {
        [$path, $hash, $sha1] = $this->writeMutatedArtifact(WatchlistBacktestC67ProductionCatalogActivationReviewService::DEFAULT_C66_ARTIFACT, function (array $c66): array {
            $c66['c67_readiness_decision']['candidate_ready_for_c67_count'] = 1;
            return $c66;
        }, 'c66-ready-count');

        $result = $this->execute(['c66Artifact' => $path, 'expectedC66Hash' => $hash, 'expectedC66FileSha1' => $sha1]);

        $this->assertSame('C67_BLOCKED_C66_C67_READINESS_COUNT_MISMATCH', $result['status']);
    }

    public function test_c67_validates_lineage_artifacts(): void
    {
        foreach ([
            'expectedC65Hash',
            'expectedC64Hash',
            'expectedC63Hash',
            'expectedC62Hash',
            'expectedC61Hash',
            'expectedC60Hash',
        ] as $key) {
            $result = $this->execute([$key => '0000000000000000000000000000000000000000']);
            $this->assertSame('C67_BLOCKED_LINEAGE_LOCK_MISMATCH', $result['status'], $key);
        }
    }

    public function test_c67_records_database_dictionary_rule(): void
    {
        $this->runService();
        $run = $this->readOutput();

        $this->assertTrue($run['database_dictionary_read_summary']['dictionary_rule_acknowledged']);
        $this->assertTrue($run['database_dictionary_read_summary']['dictionary_read_rule_complied']);
        $this->assertFalse($run['database_dictionary_read_summary']['dictionary_missing_coverage_detected']);
        $this->assertSame('market_benchmark_indicators.roc_20', $run['database_dictionary_read_summary']['market_index_mapping']['market_index_roc20_source']);
        $this->assertSame('market_benchmark_indicators.ma20_slope_pct', $run['database_dictionary_read_summary']['market_index_mapping']['market_index_ma20_slope_pct_source']);
        $this->assertTrue($run['database_dictionary_read_summary']['asof_safe']);
    }

    public function test_c67_candidate_scope_freeze_comes_from_c66_locked_decision(): void
    {
        $this->runService();
        $run = $this->readOutput();

        $this->assertTrue($run['candidate_scope_freeze_summary']['candidate_scope_freeze_completed']);
        $this->assertSame('C66_LOCKED_PRODUCTION_CATALOG_DECISION', $run['candidate_scope_freeze_summary']['candidate_scope_source']);
        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $run['candidate_scope_freeze_summary']['primary_candidate_code']);
        $this->assertSame(['C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION'], $run['candidate_scope_freeze_summary']['backup_candidate_codes']);
        $this->assertSame(['C61_A01_B01_WEAK_REGIME_QUALITY_FIRST'], $run['candidate_scope_freeze_summary']['comparator_only_candidate_codes']);
        $this->assertFalse($run['candidate_scope_freeze_summary']['candidate_scope_changed_after_c66']);
        $this->assertFalse($run['candidate_scope_freeze_summary']['candidate_scope_changed_after_c65']);
        $this->assertFalse($run['candidate_scope_freeze_summary']['new_candidate_created']);
        $this->assertFalse($run['candidate_scope_freeze_summary']['selection_rule_changed']);
        $this->assertFalse($run['candidate_scope_freeze_summary']['parameter_changed']);
        $this->assertFalse($run['candidate_scope_freeze_summary']['oos_result_used_for_new_ranking']);
        $this->assertFalse($run['candidate_scope_freeze_summary']['a01_promoted']);
    }

    public function test_c67_rejects_candidate_scope_mismatch(): void
    {
        [$path, $hash, $sha1] = $this->writeMutatedArtifact(WatchlistBacktestC67ProductionCatalogActivationReviewService::DEFAULT_C66_ARTIFACT, function (array $c66): array {
            $c66['production_lock_decision']['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $c66;
        }, 'c66-scope');

        $result = $this->execute(['c66Artifact' => $path, 'expectedC66Hash' => $hash, 'expectedC66FileSha1' => $sha1]);

        $this->assertSame('C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $result['status']);
    }

    public function test_c67_scorecard_marks_e02_ready_b01_ready_and_a01_comparator_only(): void
    {
        $this->runService();
        $run = $this->readOutput();
        $rows = $this->indexByCode($run['production_catalog_activation_candidate_scorecard']);

        $this->assertSame('primary_production_catalog_activation_review_candidate', $rows['C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE']['c67_role']);
        $this->assertTrue($rows['C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE']['production_catalog_activation_review_pass']);
        $this->assertTrue($rows['C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE']['candidate_ready_for_production_catalog_activation']);
        $this->assertSame('backup_production_catalog_activation_review_candidate', $rows['C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION']['c67_role']);
        $this->assertTrue($rows['C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION']['production_catalog_activation_review_pass']);
        $this->assertTrue($rows['C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION']['candidate_ready_for_production_catalog_activation']);
        $this->assertSame('comparator_only', $rows['C61_A01_B01_WEAK_REGIME_QUALITY_FIRST']['c67_role']);
        $this->assertFalse($rows['C61_A01_B01_WEAK_REGIME_QUALITY_FIRST']['production_catalog_activation_review_pass']);
        $this->assertFalse($rows['C61_A01_B01_WEAK_REGIME_QUALITY_FIRST']['candidate_ready_for_production_catalog_activation']);
        $this->assertSame(['C67_A01_REMAINS_COMPARATOR_ONLY'], $rows['C61_A01_B01_WEAK_REGIME_QUALITY_FIRST']['failure_reason_codes']);
    }

    public function test_c67_retains_bad_month_and_weak_regime_governance(): void
    {
        $this->runService();
        $run = $this->readOutput();

        foreach ($run['bad_month_activation_review_results'] as $row) {
            $this->assertTrue($row['bad_month_activation_review_completed']);
            $this->assertTrue($row['documented_bad_month_risk_retained']);
            $this->assertFalse($row['bad_month_removed']);
            $this->assertFalse($row['bad_month_risk_hidden']);
            $this->assertSame('MODERATE', $row['bad_month_risk_level']);
            $this->assertSame('PASS_WITH_DOCUMENTED_RISK', $row['bad_month_governance_decision']);
            $this->assertFalse($row['production_activation_risk_free_claim']);
        }

        foreach ($run['weak_regime_activation_review_results'] as $row) {
            $this->assertTrue($row['weak_regime_activation_review_completed']);
            $this->assertTrue($row['weak_regime_retained']);
            $this->assertFalse($row['weak_regime_removed']);
            $this->assertSame('SUFFICIENT', $row['weak_regime_sample_status']);
            $this->assertFalse($row['weak_regime_sample_collapse_detected']);
            $this->assertSame('MODERATE', $row['weak_regime_risk_level']);
            $this->assertSame('PASS_WITH_DOCUMENTED_RISK', $row['weak_regime_governance_decision']);
            $this->assertFalse($row['production_activation_ignores_weak_regime_risk']);
        }
    }

    public function test_c67_candidate_cannot_pass_if_bad_month_or_weak_regime_risk_missing(): void
    {
        [$badPath, $badHash, $badSha1] = $this->writeMutatedArtifact(WatchlistBacktestC67ProductionCatalogActivationReviewService::DEFAULT_C66_ARTIFACT, function (array $c66): array {
            $c66['production_lock_candidate_scorecard'][0]['c64_oos_evidence_summary']['oos_worst_month'] = null;
            return $c66;
        }, 'bad-month-missing');
        $badResult = $this->execute(['c66Artifact' => $badPath, 'expectedC66Hash' => $badHash, 'expectedC66FileSha1' => $badSha1]);
        $badRows = $this->indexByCode($badResult['production_catalog_activation_candidate_scorecard']);
        $this->assertFalse($badRows['C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE']['production_catalog_activation_review_pass']);
        $this->assertContains('C67_BAD_MONTH_GOVERNANCE_INVALID', $badRows['C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE']['failure_reason_codes']);

        [$weakPath, $weakHash, $weakSha1] = $this->writeMutatedArtifact(WatchlistBacktestC67ProductionCatalogActivationReviewService::DEFAULT_C66_ARTIFACT, function (array $c66): array {
            $c66['production_lock_candidate_scorecard'][1]['c64_oos_evidence_summary']['oos_weak_regime_sample_status'] = null;
            return $c66;
        }, 'weak-regime-missing');
        $weakResult = $this->execute(['c66Artifact' => $weakPath, 'expectedC66Hash' => $weakHash, 'expectedC66FileSha1' => $weakSha1]);
        $weakRows = $this->indexByCode($weakResult['production_catalog_activation_candidate_scorecard']);
        $this->assertFalse($weakRows['C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION']['production_catalog_activation_review_pass']);
        $this->assertContains('C67_WEAK_REGIME_GOVERNANCE_INVALID', $weakRows['C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION']['failure_reason_codes']);
    }

    public function test_c67_rejects_concentration_loss_cluster_source_bias_shared_core_and_mutation_regressions(): void
    {
        $cases = [
            ['concentration_loss_cluster_governance_summary', 'concentration_regression_detected', true, 'C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW_REJECTED_CONCENTRATION_OR_LOSS_CLUSTER'],
            ['concentration_loss_cluster_governance_summary', 'loss_cluster_regression_detected', true, 'C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW_REJECTED_CONCENTRATION_OR_LOSS_CLUSTER'],
            ['source_bias_shared_core_governance_summary', 'source_bias_risk_level', 'HIGH', 'C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW_REJECTED_SOURCE_BIAS_OR_SHARED_CORE'],
            ['source_bias_shared_core_governance_summary', 'shared_core_risk_level', 'HIGH', 'C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW_REJECTED_SOURCE_BIAS_OR_SHARED_CORE'],
            ['production_mutation_safety_summary', 'production_catalog_activated', true, 'C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW_REJECTED_PRODUCTION_MUTATION'],
            ['production_mutation_safety_summary', 'production_deployment_executed', true, 'C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW_REJECTED_PRODUCTION_MUTATION'],
            ['production_mutation_safety_summary', 'plan_confirm_mutated', true, 'C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW_REJECTED_PRODUCTION_MUTATION'],
        ];

        foreach ($cases as $case) {
            [$path, $hash, $sha1] = $this->writeMutatedArtifact(WatchlistBacktestC67ProductionCatalogActivationReviewService::DEFAULT_C66_ARTIFACT, function (array $c66) use ($case): array {
                $c66[$case[0]][$case[1]] = $case[2];
                return $c66;
            }, 'regression-'.$case[1]);

            $result = $this->execute(['c66Artifact' => $path, 'expectedC66Hash' => $hash, 'expectedC66FileSha1' => $sha1]);
            $this->assertSame($case[3], $result['status'], $case[1]);
            $this->assertFalse($result['production_catalog_activation_review_pass']);
        }
    }

    public function test_c67_activation_review_decision_and_c68_readiness_are_artifact_only(): void
    {
        $this->runService();
        $run = $this->readOutput();

        $this->assertTrue($run['production_catalog_activation_review_decision']['validation_completed']);
        $this->assertTrue($run['production_catalog_activation_review_decision']['production_catalog_activation_review_pass']);
        $this->assertTrue($run['production_catalog_activation_review_decision']['primary_activation_review_pass']);
        $this->assertTrue($run['production_catalog_activation_review_decision']['backup_activation_review_pass']);
        $this->assertSame('PRIMARY_AND_BACKUP', $run['production_catalog_activation_review_decision']['production_catalog_activation_pass_scope']);
        $this->assertTrue($run['production_catalog_activation_review_decision']['production_catalog_activation_allowed']);
        $this->assertFalse($run['production_catalog_activation_review_decision']['production_catalog_activation_execution_allowed']);
        $this->assertFalse($run['production_catalog_activation_review_decision']['production_deployment_allowed']);
        $this->assertFalse($run['production_catalog_activation_review_decision']['plan_confirm_mutation_allowed']);
        $this->assertSame(2, $run['c68_readiness_decision']['candidate_ready_for_c68_count']);
        $this->assertSame('C68_PRODUCTION_CATALOG_ACTIVATION_EXECUTION_REVIEW', $run['c68_readiness_decision']['c68_recommendation']);
        $this->assertFalse($run['c68_readiness_decision']['production_catalog_activation_execution_allowed']);
        $this->assertFalse($run['c68_readiness_decision']['production_deployment_allowed']);
        $this->assertFalse($run['c68_readiness_decision']['plan_confirm_mutation_allowed']);
        $this->assertSame('NONE', $run['failure_attribution_summary']['dominant_blocker']);
    }

    private function runService(): array
    {
        return $this->execute();
    }

    private function execute(array $overrides = []): array
    {
        $service = new WatchlistBacktestC67ProductionCatalogActivationReviewService();
        return $service->execute(
            (string) ($overrides['c66Artifact'] ?? WatchlistBacktestC67ProductionCatalogActivationReviewService::DEFAULT_C66_ARTIFACT),
            (string) ($overrides['expectedC66Hash'] ?? WatchlistBacktestC67ProductionCatalogActivationReviewService::DEFAULT_EXPECTED_C66_HASH),
            (string) ($overrides['expectedC66FileSha1'] ?? WatchlistBacktestC67ProductionCatalogActivationReviewService::DEFAULT_EXPECTED_C66_FILE_SHA1),
            (string) ($overrides['c65Artifact'] ?? WatchlistBacktestC67ProductionCatalogActivationReviewService::DEFAULT_C65_ARTIFACT),
            (string) ($overrides['expectedC65Hash'] ?? WatchlistBacktestC67ProductionCatalogActivationReviewService::DEFAULT_EXPECTED_C65_HASH),
            (string) ($overrides['expectedC65FileSha1'] ?? WatchlistBacktestC67ProductionCatalogActivationReviewService::DEFAULT_EXPECTED_C65_FILE_SHA1),
            (string) ($overrides['c64Artifact'] ?? WatchlistBacktestC67ProductionCatalogActivationReviewService::DEFAULT_C64_ARTIFACT),
            (string) ($overrides['expectedC64Hash'] ?? WatchlistBacktestC67ProductionCatalogActivationReviewService::DEFAULT_EXPECTED_C64_HASH),
            (string) ($overrides['expectedC64FileSha1'] ?? WatchlistBacktestC67ProductionCatalogActivationReviewService::DEFAULT_EXPECTED_C64_FILE_SHA1),
            (string) ($overrides['c63Artifact'] ?? WatchlistBacktestC67ProductionCatalogActivationReviewService::DEFAULT_C63_ARTIFACT),
            (string) ($overrides['expectedC63Hash'] ?? WatchlistBacktestC67ProductionCatalogActivationReviewService::DEFAULT_EXPECTED_C63_HASH),
            (string) ($overrides['expectedC63FileSha1'] ?? WatchlistBacktestC67ProductionCatalogActivationReviewService::DEFAULT_EXPECTED_C63_FILE_SHA1),
            (string) ($overrides['c62Artifact'] ?? WatchlistBacktestC67ProductionCatalogActivationReviewService::DEFAULT_C62_ARTIFACT),
            (string) ($overrides['expectedC62Hash'] ?? WatchlistBacktestC67ProductionCatalogActivationReviewService::DEFAULT_EXPECTED_C62_HASH),
            (string) ($overrides['expectedC62FileSha1'] ?? WatchlistBacktestC67ProductionCatalogActivationReviewService::DEFAULT_EXPECTED_C62_FILE_SHA1),
            (string) ($overrides['c61Artifact'] ?? WatchlistBacktestC67ProductionCatalogActivationReviewService::DEFAULT_C61_ARTIFACT),
            (string) ($overrides['expectedC61Hash'] ?? WatchlistBacktestC67ProductionCatalogActivationReviewService::DEFAULT_EXPECTED_C61_HASH),
            (string) ($overrides['expectedC61FileSha1'] ?? WatchlistBacktestC67ProductionCatalogActivationReviewService::DEFAULT_EXPECTED_C61_FILE_SHA1),
            (string) ($overrides['c60Artifact'] ?? WatchlistBacktestC67ProductionCatalogActivationReviewService::DEFAULT_C60_ARTIFACT),
            (string) ($overrides['expectedC60Hash'] ?? WatchlistBacktestC67ProductionCatalogActivationReviewService::DEFAULT_EXPECTED_C60_HASH),
            (string) ($overrides['expectedC60FileSha1'] ?? WatchlistBacktestC67ProductionCatalogActivationReviewService::DEFAULT_EXPECTED_C60_FILE_SHA1),
            (string) ($overrides['outputPath'] ?? $this->output),
            ['overwrite' => true, 'executed_at' => '2026-06-23T00:00:00+00:00']
        );
    }

    private function readOutput(): array
    {
        $json = json_decode((string) file_get_contents($this->output), true);
        $this->assertIsArray($json);
        return $json;
    }

    private function writeMutatedArtifact(string $source, callable $mutator, string $name): array
    {
        $payload = json_decode((string) file_get_contents($source), true);
        $this->assertIsArray($payload);
        $payload = $mutator($payload);
        unset($payload['artifact_hash']);
        $payload['artifact_hash'] = sha1(json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $path = 'storage/app/watchlist/backtest/.tmp-c67-'.$name.'.json';
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $this->tmpFiles[] = $path;
        return [$path, $payload['artifact_hash'], strtoupper(sha1_file($path) ?: '')];
    }

    private function indexByCode(array $rows): array
    {
        $out = [];
        foreach ($rows as $row) {
            $out[$row['candidate_code']] = $row;
        }
        return $out;
    }
}

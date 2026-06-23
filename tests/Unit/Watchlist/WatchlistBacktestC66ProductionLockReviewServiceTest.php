<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC66ProductionLockReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC66ProductionLockReviewServiceTest extends TestCase
{
    private string $output = 'storage/app/watchlist/backtest/.tmp-c66-test-output.json';
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

    public function test_c66_runtime_passes_primary_and_backup_from_locked_c65_evidence(): void
    {
        $result = $this->runService();

        $this->assertSame('C66_PRODUCTION_LOCK_REVIEW_PASSED_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertSame('C66_PRODUCTION_LOCK_REVIEW_PASSED_PRIMARY_AND_BACKUP', $result['reason_code']);
        $this->assertTrue($result['production_lock_review_executed']);
        $this->assertTrue($result['production_lock_review_pass']);
        $this->assertTrue($result['production_catalog_lock_allowed']);
        $this->assertFalse($result['production_catalog_activation_allowed']);
        $this->assertFalse($result['production_deployment_allowed']);
        $this->assertFalse($result['plan_confirm_mutation_allowed']);
        $this->assertFalse($result['production_ready']);
        $this->assertSame('C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW', $result['next_step_recommendation']);
        $this->assertFileExists($this->output);
    }

    public function test_c66_artifact_records_all_required_sections(): void
    {
        $this->runService();
        $run = $this->readOutput();

        foreach ([
            'source_artifact_locks',
            'database_dictionary_read_summary',
            'c65_lock_validation_summary',
            'c64_lineage_validation_summary',
            'c63_lineage_validation_summary',
            'c62_lineage_validation_summary',
            'c61_lineage_validation_summary',
            'c60_lineage_validation_summary',
            'candidate_scope_freeze_summary',
            'c65_prelock_replay_summary',
            'c64_oos_proof_retained_summary',
            'production_lock_candidate_scorecard',
            'bad_month_governance_lock_review_results',
            'weak_regime_governance_lock_review_results',
            'concentration_loss_cluster_governance_summary',
            'rolling_month_dependency_governance_summary',
            'source_bias_shared_core_governance_summary',
            'production_mutation_safety_summary',
            'documentation_governance_summary',
            'c65_cleanup_note_summary',
            'production_lock_decision',
            'c67_readiness_decision',
            'failure_attribution_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }
    }

    public function test_c66_rejects_missing_c65_artifact(): void
    {
        $result = $this->execute(['c65Artifact' => 'storage/app/watchlist/backtest/missing-c65.json']);

        $this->assertSame('C66_BLOCKED_MISSING_C65_ARTIFACT', $result['status']);
        $this->assertFalse($result['production_lock_review_executed']);
    }

    public function test_c66_validates_c65_artifact_hash(): void
    {
        $result = $this->execute(['expectedC65Hash' => '0000000000000000000000000000000000000000']);

        $this->assertSame('C66_BLOCKED_C65_ARTIFACT_LOCK_MISMATCH', $result['status']);
        $this->assertFalse($result['c65_hash_match']);
    }

    public function test_c66_validates_c65_file_sha1(): void
    {
        $result = $this->execute(['expectedC65FileSha1' => '0000000000000000000000000000000000000000']);

        $this->assertSame('C66_BLOCKED_C65_FILE_SHA1_LOCK_MISMATCH', $result['status']);
        $this->assertFalse($result['c65_file_sha1_match']);
    }

    public function test_c66_rejects_c65_status_mismatch(): void
    {
        [$path, $hash, $sha1] = $this->writeMutatedArtifact(WatchlistBacktestC66ProductionLockReviewService::DEFAULT_C65_ARTIFACT, function (array $c65): array {
            $c65['status'] = 'C65_BROKEN_STATUS';
            return $c65;
        }, 'c65-status');

        $result = $this->execute(['c65Artifact' => $path, 'expectedC65Hash' => $hash, 'expectedC65FileSha1' => $sha1]);

        $this->assertSame('C66_BLOCKED_C65_STATUS_OR_REASON_MISMATCH', $result['status']);
        $this->assertSame('WS_BT_C66_C65_STATUS_INVALID', $result['reason_code']);
    }

    public function test_c66_rejects_c65_reason_code_mismatch(): void
    {
        [$path, $hash, $sha1] = $this->writeMutatedArtifact(WatchlistBacktestC66ProductionLockReviewService::DEFAULT_C65_ARTIFACT, function (array $c65): array {
            $c65['reason_code'] = 'C65_BROKEN_REASON';
            return $c65;
        }, 'c65-reason');

        $result = $this->execute(['c65Artifact' => $path, 'expectedC65Hash' => $hash, 'expectedC65FileSha1' => $sha1]);

        $this->assertSame('C66_BLOCKED_C65_STATUS_OR_REASON_MISMATCH', $result['status']);
        $this->assertSame('WS_BT_C66_C65_REASON_INVALID', $result['reason_code']);
    }

    public function test_c66_rejects_c65_production_prelock_not_passed(): void
    {
        [$path, $hash, $sha1] = $this->writeMutatedArtifact(WatchlistBacktestC66ProductionLockReviewService::DEFAULT_C65_ARTIFACT, function (array $c65): array {
            $c65['production_prelock_review_pass'] = false;
            return $c65;
        }, 'c65-prelock-fail');

        $result = $this->execute(['c65Artifact' => $path, 'expectedC65Hash' => $hash, 'expectedC65FileSha1' => $sha1]);

        $this->assertSame('C66_BLOCKED_C65_PRODUCTION_PRELOCK_NOT_PASSED', $result['status']);
    }

    public function test_c66_rejects_c65_candidate_ready_for_c66_count_mismatch(): void
    {
        [$path, $hash, $sha1] = $this->writeMutatedArtifact(WatchlistBacktestC66ProductionLockReviewService::DEFAULT_C65_ARTIFACT, function (array $c65): array {
            $c65['c66_readiness_decision']['candidate_ready_for_c66_count'] = 1;
            return $c65;
        }, 'c65-ready-count');

        $result = $this->execute(['c65Artifact' => $path, 'expectedC65Hash' => $hash, 'expectedC65FileSha1' => $sha1]);

        $this->assertSame('C66_BLOCKED_C65_C66_READINESS_COUNT_MISMATCH', $result['status']);
    }

    public function test_c66_rejects_c65_production_flags_that_are_not_false(): void
    {
        foreach ([
            ['production_ready', 'C66_BLOCKED_C65_PRODUCTION_READY_FLAG_INVALID'],
            ['production_catalog_allowed', 'C66_BLOCKED_C65_PRODUCTION_CATALOG_ALLOWED_FLAG_INVALID'],
            ['production_deployment_allowed', 'C66_BLOCKED_C65_PRODUCTION_DEPLOYMENT_ALLOWED_FLAG_INVALID'],
        ] as $case) {
            [$path, $hash, $sha1] = $this->writeMutatedArtifact(WatchlistBacktestC66ProductionLockReviewService::DEFAULT_C65_ARTIFACT, function (array $c65) use ($case): array {
                $c65[$case[0]] = true;
                return $c65;
            }, 'c65-'.$case[0]);

            $result = $this->execute(['c65Artifact' => $path, 'expectedC65Hash' => $hash, 'expectedC65FileSha1' => $sha1]);

            $this->assertSame($case[1], $result['status'], $case[0]);
        }
    }

    public function test_c66_validates_lineage_artifacts(): void
    {
        foreach ([
            'expectedC64Hash',
            'expectedC63Hash',
            'expectedC62Hash',
            'expectedC61Hash',
            'expectedC60Hash',
        ] as $key) {
            $result = $this->execute([$key => '0000000000000000000000000000000000000000']);
            $this->assertSame('C66_BLOCKED_LINEAGE_LOCK_MISMATCH', $result['status'], $key);
        }
    }

    public function test_c66_records_database_dictionary_rule(): void
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

    public function test_c66_candidate_scope_freeze_comes_from_c65_locked_decision(): void
    {
        $this->runService();
        $run = $this->readOutput();

        $this->assertTrue($run['candidate_scope_freeze_summary']['candidate_scope_freeze_completed']);
        $this->assertSame('C65_LOCKED_PRODUCTION_PRELOCK_DECISION', $run['candidate_scope_freeze_summary']['candidate_scope_source']);
        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $run['candidate_scope_freeze_summary']['primary_candidate_code']);
        $this->assertSame(['C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION'], $run['candidate_scope_freeze_summary']['backup_candidate_codes']);
        $this->assertSame(['C61_A01_B01_WEAK_REGIME_QUALITY_FIRST'], $run['candidate_scope_freeze_summary']['comparator_only_candidate_codes']);
        $this->assertFalse($run['candidate_scope_freeze_summary']['new_candidate_created']);
        $this->assertFalse($run['candidate_scope_freeze_summary']['selection_rule_changed']);
        $this->assertFalse($run['candidate_scope_freeze_summary']['parameter_changed']);
        $this->assertFalse($run['candidate_scope_freeze_summary']['oos_result_used_for_new_ranking']);
        $this->assertFalse($run['candidate_scope_freeze_summary']['a01_promoted']);
    }

    public function test_c66_rejects_candidate_scope_mismatch(): void
    {
        [$path, $hash, $sha1] = $this->writeMutatedArtifact(WatchlistBacktestC66ProductionLockReviewService::DEFAULT_C65_ARTIFACT, function (array $c65): array {
            $c65['production_prelock_decision']['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $c65;
        }, 'c65-scope');

        $result = $this->execute(['c65Artifact' => $path, 'expectedC65Hash' => $hash, 'expectedC65FileSha1' => $sha1]);

        $this->assertSame('C66_PRODUCTION_LOCK_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $result['status']);
    }

    public function test_c66_scorecard_locks_e02_and_b01_but_not_a01(): void
    {
        $this->runService();
        $run = $this->readOutput();
        $rows = $this->indexByCode($run['production_lock_candidate_scorecard']);

        $this->assertSame('primary_production_lock_candidate', $rows['C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE']['c66_role']);
        $this->assertTrue($rows['C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE']['production_lock_review_pass']);
        $this->assertTrue($rows['C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE']['candidate_locked_for_production_catalog']);
        $this->assertSame('backup_production_lock_candidate', $rows['C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION']['c66_role']);
        $this->assertTrue($rows['C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION']['production_lock_review_pass']);
        $this->assertTrue($rows['C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION']['candidate_locked_for_production_catalog']);
        $this->assertSame('comparator_only', $rows['C61_A01_B01_WEAK_REGIME_QUALITY_FIRST']['c66_role']);
        $this->assertFalse($rows['C61_A01_B01_WEAK_REGIME_QUALITY_FIRST']['production_lock_review_pass']);
        $this->assertFalse($rows['C61_A01_B01_WEAK_REGIME_QUALITY_FIRST']['candidate_locked_for_production_catalog']);
        $this->assertContains('C66_A01_REMAINS_COMPARATOR_ONLY', $rows['C61_A01_B01_WEAK_REGIME_QUALITY_FIRST']['failure_reason_codes']);
    }

    public function test_c66_bad_month_and_weak_regime_governance_are_documented(): void
    {
        $this->runService();
        $run = $this->readOutput();

        foreach ($run['bad_month_governance_lock_review_results'] as $row) {
            $this->assertTrue($row['bad_month_governance_lock_review_completed']);
            $this->assertFalse($row['bad_month_removed']);
            $this->assertFalse($row['bad_month_risk_hidden']);
            $this->assertSame('MODERATE', $row['bad_month_risk_level']);
            $this->assertSame('PASS_WITH_DOCUMENTED_RISK', $row['bad_month_governance_decision']);
            $this->assertFalse($row['production_lock_risk_free_claim']);
        }
        foreach ($run['weak_regime_governance_lock_review_results'] as $row) {
            $this->assertTrue($row['weak_regime_governance_lock_review_completed']);
            $this->assertTrue($row['weak_regime_retained']);
            $this->assertFalse($row['weak_regime_removed']);
            $this->assertSame('SUFFICIENT', $row['weak_regime_sample_status']);
            $this->assertFalse($row['weak_regime_sample_collapse_detected']);
            $this->assertSame('MODERATE', $row['weak_regime_risk_level']);
            $this->assertSame('PASS_WITH_DOCUMENTED_RISK', $row['weak_regime_governance_decision']);
            $this->assertFalse($row['production_lock_ignores_weak_regime_risk']);
        }
    }

    public function test_c66_validates_all_governance_summaries_and_safety(): void
    {
        $this->runService();
        $run = $this->readOutput();

        $this->assertTrue($run['concentration_loss_cluster_governance_summary']['concentration_governance_pass']);
        $this->assertTrue($run['concentration_loss_cluster_governance_summary']['loss_cluster_governance_pass']);
        $this->assertFalse($run['concentration_loss_cluster_governance_summary']['concentration_regression_detected']);
        $this->assertFalse($run['concentration_loss_cluster_governance_summary']['loss_cluster_regression_detected']);
        $this->assertFalse($run['concentration_loss_cluster_governance_summary']['month_dependency_detected']);
        $this->assertTrue($run['rolling_month_dependency_governance_summary']['rolling_governance_pass']);
        $this->assertFalse($run['rolling_month_dependency_governance_summary']['month_dependency_detected']);
        $this->assertTrue($run['source_bias_shared_core_governance_summary']['source_bias_governance_pass']);
        $this->assertTrue($run['source_bias_shared_core_governance_summary']['shared_core_governance_pass']);
        $this->assertSame('DOCUMENTED_NOT_HIGH', $run['source_bias_shared_core_governance_summary']['source_bias_risk_level']);
        $this->assertSame('LOW', $run['source_bias_shared_core_governance_summary']['shared_core_risk_level']);
        $this->assertTrue($run['production_mutation_safety_summary']['production_catalog_locked_decision_created']);
        $this->assertFalse($run['production_mutation_safety_summary']['production_catalog_created']);
        $this->assertFalse($run['production_mutation_safety_summary']['production_catalog_activated']);
        $this->assertFalse($run['production_mutation_safety_summary']['production_deployment_executed']);
        $this->assertFalse($run['production_mutation_safety_summary']['plan_confirm_mutated']);
        $this->assertFalse($run['production_mutation_safety_summary']['production_catalog_activation_allowed']);
        $this->assertFalse($run['production_mutation_safety_summary']['production_deployment_allowed']);
        $this->assertFalse($run['production_mutation_safety_summary']['plan_confirm_mutation_allowed']);
        $this->assertFalse($run['production_mutation_safety_summary']['latest_shortcut_used']);
        $this->assertFalse($run['production_mutation_safety_summary']['max_date_shortcut_used']);
        $this->assertFalse($run['production_mutation_safety_summary']['future_lookup_detected']);
        $this->assertFalse($run['production_mutation_safety_summary']['return_fields_used_for_selection']);
    }

    public function test_c66_c65_cleanup_note_is_non_blocking(): void
    {
        $this->runService();
        $run = $this->readOutput();

        $this->assertSame('C65_OOS_FAILURE_ATTRIBUTION_IS_ONLY', $run['c65_cleanup_note_summary']['legacy_repair_recommendation']);
        $this->assertTrue($run['c65_cleanup_note_summary']['legacy_repair_recommendation_non_blocking']);
        $this->assertSame('NOT_REQUIRED', $run['c65_cleanup_note_summary']['normalized_repair_recommendation']);
        $this->assertFalse($run['c65_cleanup_note_summary']['c65_failure_repair_required']);
    }

    public function test_c66_rejects_missing_bad_month_governance(): void
    {
        [$path, $hash, $sha1] = $this->writeMutatedArtifact(WatchlistBacktestC66ProductionLockReviewService::DEFAULT_C65_ARTIFACT, function (array $c65): array {
            $c65['production_prelock_candidate_scorecard'][0]['c64_oos_evidence_summary']['oos_bad_month_decision'] = null;
            return $c65;
        }, 'bad-month-missing');

        $result = $this->execute(['c65Artifact' => $path, 'expectedC65Hash' => $hash, 'expectedC65FileSha1' => $sha1]);

        $this->assertSame('C66_PRODUCTION_LOCK_REVIEW_REJECTED_BAD_MONTH_GOVERNANCE', $result['status']);
        $this->assertFalse($result['production_lock_review_pass']);
    }

    public function test_c66_rejects_missing_weak_regime_governance(): void
    {
        [$path, $hash, $sha1] = $this->writeMutatedArtifact(WatchlistBacktestC66ProductionLockReviewService::DEFAULT_C65_ARTIFACT, function (array $c65): array {
            $c65['production_prelock_candidate_scorecard'][1]['c64_oos_evidence_summary']['oos_weak_regime_sample_status'] = null;
            return $c65;
        }, 'weak-regime-missing');

        $result = $this->execute(['c65Artifact' => $path, 'expectedC65Hash' => $hash, 'expectedC65FileSha1' => $sha1]);

        $this->assertSame('C66_PRODUCTION_LOCK_REVIEW_REJECTED_WEAK_REGIME_GOVERNANCE', $result['status']);
        $this->assertFalse($result['production_lock_review_pass']);
    }

    public function test_c66_rejects_concentration_or_loss_cluster_regression(): void
    {
        [$path, $hash, $sha1] = $this->writeMutatedArtifact(WatchlistBacktestC66ProductionLockReviewService::DEFAULT_C65_ARTIFACT, function (array $c65): array {
            $c65['production_prelock_candidate_scorecard'][0]['oos_concentration_regression_detected'] = true;
            return $c65;
        }, 'concentration-regression');

        $result = $this->execute(['c65Artifact' => $path, 'expectedC65Hash' => $hash, 'expectedC65FileSha1' => $sha1]);

        $this->assertSame('C66_PRODUCTION_LOCK_REVIEW_REJECTED_CONCENTRATION_OR_LOSS_CLUSTER', $result['status']);
        $this->assertFalse($result['production_lock_review_pass']);
    }

    public function test_c66_rejects_source_bias_or_shared_core_high_risk(): void
    {
        [$path, $hash, $sha1] = $this->writeMutatedArtifact(WatchlistBacktestC66ProductionLockReviewService::DEFAULT_C65_ARTIFACT, function (array $c65): array {
            $c65['production_prelock_candidate_scorecard'][0]['oos_source_bias_risk_level'] = 'HIGH';
            return $c65;
        }, 'source-bias-high');

        $result = $this->execute(['c65Artifact' => $path, 'expectedC65Hash' => $hash, 'expectedC65FileSha1' => $sha1]);

        $this->assertSame('C66_PRODUCTION_LOCK_REVIEW_REJECTED_SOURCE_BIAS_OR_SHARED_CORE', $result['status']);
        $this->assertFalse($result['production_lock_review_pass']);
    }

    public function test_c66_rejects_production_mutation_safety_violation(): void
    {
        [$path, $hash, $sha1] = $this->writeMutatedArtifact(WatchlistBacktestC66ProductionLockReviewService::DEFAULT_C65_ARTIFACT, function (array $c65): array {
            $c65['production_mutation_safety_summary']['production_catalog_created'] = true;
            return $c65;
        }, 'production-catalog-created');

        $result = $this->execute(['c65Artifact' => $path, 'expectedC65Hash' => $hash, 'expectedC65FileSha1' => $sha1]);

        $this->assertSame('C66_PRODUCTION_LOCK_REVIEW_REJECTED_PRODUCTION_MUTATION', $result['status']);
        $this->assertFalse($result['production_catalog_lock_allowed']);
    }

    public function test_c66_c67_readiness_is_evidence_based_and_non_activation(): void
    {
        $this->runService();
        $run = $this->readOutput();

        $this->assertSame(2, $run['c67_readiness_decision']['candidate_ready_for_c67_count']);
        $this->assertSame('C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW', $run['c67_readiness_decision']['c67_recommendation']);
        $this->assertTrue($run['c67_readiness_decision']['production_catalog_lock_allowed']);
        $this->assertFalse($run['c67_readiness_decision']['production_catalog_activation_allowed']);
        $this->assertFalse($run['c67_readiness_decision']['production_deployment_allowed']);
        $this->assertFalse($run['c67_readiness_decision']['plan_confirm_mutation_allowed']);
        $this->assertSame('NONE', $run['failure_attribution_summary']['dominant_blocker']);
    }

    private function runService(): array
    {
        return $this->execute();
    }

    private function execute(array $overrides = []): array
    {
        $service = new WatchlistBacktestC66ProductionLockReviewService();
        return $service->execute(
            (string) ($overrides['c65Artifact'] ?? WatchlistBacktestC66ProductionLockReviewService::DEFAULT_C65_ARTIFACT),
            (string) ($overrides['expectedC65Hash'] ?? WatchlistBacktestC66ProductionLockReviewService::DEFAULT_EXPECTED_C65_HASH),
            (string) ($overrides['expectedC65FileSha1'] ?? WatchlistBacktestC66ProductionLockReviewService::DEFAULT_EXPECTED_C65_FILE_SHA1),
            (string) ($overrides['c64Artifact'] ?? WatchlistBacktestC66ProductionLockReviewService::DEFAULT_C64_ARTIFACT),
            (string) ($overrides['expectedC64Hash'] ?? WatchlistBacktestC66ProductionLockReviewService::DEFAULT_EXPECTED_C64_HASH),
            (string) ($overrides['expectedC64FileSha1'] ?? WatchlistBacktestC66ProductionLockReviewService::DEFAULT_EXPECTED_C64_FILE_SHA1),
            (string) ($overrides['c63Artifact'] ?? WatchlistBacktestC66ProductionLockReviewService::DEFAULT_C63_ARTIFACT),
            (string) ($overrides['expectedC63Hash'] ?? WatchlistBacktestC66ProductionLockReviewService::DEFAULT_EXPECTED_C63_HASH),
            (string) ($overrides['expectedC63FileSha1'] ?? WatchlistBacktestC66ProductionLockReviewService::DEFAULT_EXPECTED_C63_FILE_SHA1),
            (string) ($overrides['c62Artifact'] ?? WatchlistBacktestC66ProductionLockReviewService::DEFAULT_C62_ARTIFACT),
            (string) ($overrides['expectedC62Hash'] ?? WatchlistBacktestC66ProductionLockReviewService::DEFAULT_EXPECTED_C62_HASH),
            (string) ($overrides['expectedC62FileSha1'] ?? WatchlistBacktestC66ProductionLockReviewService::DEFAULT_EXPECTED_C62_FILE_SHA1),
            (string) ($overrides['c61Artifact'] ?? WatchlistBacktestC66ProductionLockReviewService::DEFAULT_C61_ARTIFACT),
            (string) ($overrides['expectedC61Hash'] ?? WatchlistBacktestC66ProductionLockReviewService::DEFAULT_EXPECTED_C61_HASH),
            (string) ($overrides['expectedC61FileSha1'] ?? WatchlistBacktestC66ProductionLockReviewService::DEFAULT_EXPECTED_C61_FILE_SHA1),
            (string) ($overrides['c60Artifact'] ?? WatchlistBacktestC66ProductionLockReviewService::DEFAULT_C60_ARTIFACT),
            (string) ($overrides['expectedC60Hash'] ?? WatchlistBacktestC66ProductionLockReviewService::DEFAULT_EXPECTED_C60_HASH),
            (string) ($overrides['expectedC60FileSha1'] ?? WatchlistBacktestC66ProductionLockReviewService::DEFAULT_EXPECTED_C60_FILE_SHA1),
            (string) ($overrides['outputPath'] ?? $this->output),
            ['overwrite' => true, 'executed_at' => '2026-06-22T00:00:00+00:00']
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
        $path = 'storage/app/watchlist/backtest/.tmp-c66-'.$name.'.json';
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

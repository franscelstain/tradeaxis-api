<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC65ProductionPreLockReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC65ProductionPreLockReviewServiceTest extends TestCase
{
    private string $output = 'storage/app/watchlist/backtest/.tmp-c65-test-output.json';
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

    public function test_c65_runtime_passes_primary_and_backup_from_locked_c64_evidence(): void
    {
        $result = $this->runService();

        $this->assertSame('C65_PRODUCTION_PRE_LOCK_REVIEW_PASSED_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertSame('C65_PRODUCTION_PRE_LOCK_REVIEW_PASSED_PRIMARY_AND_BACKUP', $result['reason_code']);
        $this->assertTrue($result['production_prelock_review_executed']);
        $this->assertTrue($result['production_prelock_review_pass']);
        $this->assertFalse($result['production_ready']);
        $this->assertFalse($result['production_catalog_allowed']);
        $this->assertFalse($result['production_deployment_allowed']);
        $this->assertSame('C66_PRODUCTION_LOCK_REVIEW', $result['next_step_recommendation']);
        $this->assertFileExists($this->output);
    }

    public function test_c65_artifact_records_all_required_sections(): void
    {
        $this->runService();
        $run = $this->readOutput();

        foreach ([
            'source_artifact_locks',
            'database_dictionary_read_summary',
            'c64_lock_validation_summary',
            'c63_lineage_validation_summary',
            'c62_lineage_validation_summary',
            'c61_lineage_validation_summary',
            'c60_lineage_validation_summary',
            'candidate_scope_freeze_summary',
            'c64_oos_proof_replay_summary',
            'production_prelock_candidate_scorecard',
            'bad_month_governance_review_results',
            'weak_regime_governance_review_results',
            'concentration_loss_cluster_governance_summary',
            'rolling_month_dependency_governance_summary',
            'source_bias_shared_core_governance_summary',
            'production_mutation_safety_summary',
            'documentation_governance_summary',
            'c64_cleanup_note_summary',
            'production_prelock_decision',
            'c66_readiness_decision',
            'failure_attribution_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }
    }

    public function test_c65_rejects_missing_c64_artifact(): void
    {
        $result = $this->execute(['c64Artifact' => 'storage/app/watchlist/backtest/missing-c64.json']);

        $this->assertSame('C65_BLOCKED_MISSING_C64_ARTIFACT', $result['status']);
        $this->assertFalse($result['production_prelock_review_executed']);
    }

    public function test_c65_validates_c64_artifact_hash(): void
    {
        $result = $this->execute(['expectedC64Hash' => '0000000000000000000000000000000000000000']);

        $this->assertSame('C65_BLOCKED_C64_ARTIFACT_LOCK_MISMATCH', $result['status']);
        $this->assertFalse($result['c64_hash_match']);
    }

    public function test_c65_validates_c64_file_sha1(): void
    {
        $result = $this->execute(['expectedC64FileSha1' => '0000000000000000000000000000000000000000']);

        $this->assertSame('C65_BLOCKED_C64_FILE_SHA1_LOCK_MISMATCH', $result['status']);
        $this->assertFalse($result['c64_file_sha1_match']);
    }

    public function test_c65_rejects_c64_status_mismatch(): void
    {
        [$path, $hash, $sha1] = $this->writeMutatedArtifact(WatchlistBacktestC65ProductionPreLockReviewService::DEFAULT_C64_ARTIFACT, function (array $c64): array {
            $c64['status'] = 'C64_BROKEN_STATUS';
            return $c64;
        }, 'c64-status');

        $result = $this->execute(['c64Artifact' => $path, 'expectedC64Hash' => $hash, 'expectedC64FileSha1' => $sha1]);

        $this->assertSame('C65_BLOCKED_C64_STATUS_OR_REASON_MISMATCH', $result['status']);
        $this->assertSame('WS_BT_C65_C64_STATUS_INVALID', $result['reason_code']);
    }

    public function test_c65_rejects_c64_reason_code_mismatch(): void
    {
        [$path, $hash, $sha1] = $this->writeMutatedArtifact(WatchlistBacktestC65ProductionPreLockReviewService::DEFAULT_C64_ARTIFACT, function (array $c64): array {
            $c64['reason_code'] = 'C64_BROKEN_REASON';
            return $c64;
        }, 'c64-reason');

        $result = $this->execute(['c64Artifact' => $path, 'expectedC64Hash' => $hash, 'expectedC64FileSha1' => $sha1]);

        $this->assertSame('C65_BLOCKED_C64_STATUS_OR_REASON_MISMATCH', $result['status']);
        $this->assertSame('WS_BT_C65_C64_REASON_INVALID', $result['reason_code']);
    }

    public function test_c65_rejects_c64_oos_proof_not_passed(): void
    {
        [$path, $hash, $sha1] = $this->writeMutatedArtifact(WatchlistBacktestC65ProductionPreLockReviewService::DEFAULT_C64_ARTIFACT, function (array $c64): array {
            $c64['oos_proof_pass'] = false;
            return $c64;
        }, 'c64-oos-fail');

        $result = $this->execute(['c64Artifact' => $path, 'expectedC64Hash' => $hash, 'expectedC64FileSha1' => $sha1]);

        $this->assertSame('C65_BLOCKED_C64_OOS_PROOF_NOT_PASSED', $result['status']);
    }

    public function test_c65_rejects_c64_candidate_ready_for_c65_count_mismatch(): void
    {
        [$path, $hash, $sha1] = $this->writeMutatedArtifact(WatchlistBacktestC65ProductionPreLockReviewService::DEFAULT_C64_ARTIFACT, function (array $c64): array {
            $c64['c65_readiness_decision']['candidate_ready_for_c65_count'] = 1;
            return $c64;
        }, 'c64-ready-count');

        $result = $this->execute(['c64Artifact' => $path, 'expectedC64Hash' => $hash, 'expectedC64FileSha1' => $sha1]);

        $this->assertSame('C65_BLOCKED_C64_C65_READINESS_COUNT_MISMATCH', $result['status']);
    }

    public function test_c65_rejects_c64_production_ready_true(): void
    {
        [$path, $hash, $sha1] = $this->writeMutatedArtifact(WatchlistBacktestC65ProductionPreLockReviewService::DEFAULT_C64_ARTIFACT, function (array $c64): array {
            $c64['production_ready'] = true;
            return $c64;
        }, 'c64-production-ready');

        $result = $this->execute(['c64Artifact' => $path, 'expectedC64Hash' => $hash, 'expectedC64FileSha1' => $sha1]);

        $this->assertSame('C65_BLOCKED_C64_PRODUCTION_READY_FLAG_INVALID', $result['status']);
    }

    public function test_c65_validates_candidate_scope_freeze(): void
    {
        $this->runService();
        $run = $this->readOutput();

        $this->assertTrue($run['candidate_scope_freeze_summary']['candidate_scope_freeze_completed']);
        $this->assertSame('C64_LOCKED_OOS_PROOF_DECISION', $run['candidate_scope_freeze_summary']['candidate_scope_source']);
        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $run['candidate_scope_freeze_summary']['primary_candidate_code']);
        $this->assertSame(['C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION'], $run['candidate_scope_freeze_summary']['backup_candidate_codes']);
        $this->assertSame(['C61_A01_B01_WEAK_REGIME_QUALITY_FIRST'], $run['candidate_scope_freeze_summary']['comparator_only_candidate_codes']);
        $this->assertFalse($run['candidate_scope_freeze_summary']['new_candidate_created']);
        $this->assertFalse($run['candidate_scope_freeze_summary']['selection_rule_changed']);
        $this->assertFalse($run['candidate_scope_freeze_summary']['parameter_changed']);
        $this->assertFalse($run['candidate_scope_freeze_summary']['oos_result_used_for_new_ranking']);
        $this->assertFalse($run['candidate_scope_freeze_summary']['a01_promoted']);
    }

    public function test_c65_rejects_candidate_scope_mismatch(): void
    {
        [$path, $hash, $sha1] = $this->writeMutatedArtifact(WatchlistBacktestC65ProductionPreLockReviewService::DEFAULT_C64_ARTIFACT, function (array $c64): array {
            $c64['selection_freeze_summary']['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $c64;
        }, 'c64-scope');

        $result = $this->execute(['c64Artifact' => $path, 'expectedC64Hash' => $hash, 'expectedC64FileSha1' => $sha1]);

        $this->assertSame('C65_PRODUCTION_PRE_LOCK_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $result['status']);
    }

    public function test_c65_validates_lineage_artifacts(): void
    {
        foreach ([
            'expectedC63Hash',
            'expectedC62Hash',
            'expectedC61Hash',
            'expectedC60Hash',
        ] as $key) {
            $result = $this->execute([$key => '0000000000000000000000000000000000000000']);
            $this->assertSame('C65_BLOCKED_LINEAGE_LOCK_MISMATCH', $result['status'], $key);
        }
    }

    public function test_c65_records_database_dictionary_rule(): void
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

    public function test_c65_generates_c64_oos_proof_replay_summary_without_retune(): void
    {
        $this->runService();
        $run = $this->readOutput();

        $summary = $run['c64_oos_proof_replay_summary'];
        $this->assertTrue($summary['validation_completed']);
        $this->assertTrue($summary['oos_proof_replayed_from_artifact']);
        $this->assertFalse($summary['oos_proof_recomputed_for_selection']);
        $this->assertSame('2025-05-22', $summary['oos_period_from']);
        $this->assertSame('2026-05-29', $summary['oos_period_to']);
        $this->assertSame('PRIMARY_AND_BACKUP', $summary['oos_pass_scope']);
        $this->assertTrue($summary['a01_remains_comparator_only']);
    }

    public function test_c65_scorecard_marks_e02_and_b01_ready_for_c66_and_a01_comparator_only(): void
    {
        $this->runService();
        $run = $this->readOutput();
        $rows = $this->indexByCode($run['production_prelock_candidate_scorecard']);

        $this->assertTrue($rows['C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE']['production_prelock_review_pass']);
        $this->assertTrue($rows['C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE']['candidate_ready_for_c66']);
        $this->assertSame('primary_production_prelock_candidate', $rows['C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE']['c65_role']);
        $this->assertTrue($rows['C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION']['production_prelock_review_pass']);
        $this->assertTrue($rows['C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION']['candidate_ready_for_c66']);
        $this->assertSame('backup_production_prelock_candidate', $rows['C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION']['c65_role']);
        $this->assertSame('comparator_only', $rows['C61_A01_B01_WEAK_REGIME_QUALITY_FIRST']['c65_role']);
        $this->assertFalse($rows['C61_A01_B01_WEAK_REGIME_QUALITY_FIRST']['production_prelock_review_pass']);
        $this->assertFalse($rows['C61_A01_B01_WEAK_REGIME_QUALITY_FIRST']['candidate_ready_for_c66']);
        $this->assertContains('C65_A01_REMAINS_COMPARATOR_ONLY', $rows['C61_A01_B01_WEAK_REGIME_QUALITY_FIRST']['failure_reason_codes']);
    }

    public function test_c65_bad_month_and_weak_regime_governance_are_documented(): void
    {
        $this->runService();
        $run = $this->readOutput();

        foreach ($run['bad_month_governance_review_results'] as $row) {
            $this->assertTrue($row['bad_month_governance_review_completed']);
            $this->assertFalse($row['bad_month_removed']);
            $this->assertFalse($row['bad_month_risk_hidden']);
            $this->assertSame('MODERATE', $row['bad_month_risk_level']);
            $this->assertSame('PASS_WITH_DOCUMENTED_RISK', $row['bad_month_governance_decision']);
        }
        foreach ($run['weak_regime_governance_review_results'] as $row) {
            $this->assertTrue($row['weak_regime_governance_review_completed']);
            $this->assertFalse($row['weak_regime_removed']);
            $this->assertSame('SUFFICIENT', $row['weak_regime_sample_status']);
            $this->assertFalse($row['weak_regime_sample_collapse_detected']);
            $this->assertSame('MODERATE', $row['weak_regime_risk_level']);
            $this->assertSame('PASS_WITH_DOCUMENTED_RISK', $row['weak_regime_governance_decision']);
        }
    }

    public function test_c65_validates_all_governance_summaries_and_safety(): void
    {
        $this->runService();
        $run = $this->readOutput();

        $this->assertTrue($run['concentration_loss_cluster_governance_summary']['concentration_governance_pass']);
        $this->assertTrue($run['concentration_loss_cluster_governance_summary']['loss_cluster_governance_pass']);
        $this->assertFalse($run['concentration_loss_cluster_governance_summary']['concentration_regression_detected']);
        $this->assertFalse($run['concentration_loss_cluster_governance_summary']['loss_cluster_regression_detected']);
        $this->assertTrue($run['rolling_month_dependency_governance_summary']['rolling_governance_pass']);
        $this->assertFalse($run['rolling_month_dependency_governance_summary']['month_dependency_detected']);
        $this->assertTrue($run['source_bias_shared_core_governance_summary']['source_bias_governance_pass']);
        $this->assertTrue($run['source_bias_shared_core_governance_summary']['shared_core_governance_pass']);
        $this->assertSame('DOCUMENTED_NOT_HIGH', $run['source_bias_shared_core_governance_summary']['source_bias_risk_level']);
        $this->assertSame('LOW', $run['source_bias_shared_core_governance_summary']['shared_core_risk_level']);
        $this->assertFalse($run['production_mutation_safety_summary']['production_catalog_created']);
        $this->assertFalse($run['production_mutation_safety_summary']['production_catalog_activated']);
        $this->assertFalse($run['production_mutation_safety_summary']['production_deployment_executed']);
        $this->assertFalse($run['production_mutation_safety_summary']['plan_confirm_mutated']);
        $this->assertFalse($run['production_mutation_safety_summary']['production_ready']);
        $this->assertFalse($run['production_mutation_safety_summary']['latest_shortcut_used']);
        $this->assertFalse($run['production_mutation_safety_summary']['future_lookup_detected']);
        $this->assertFalse($run['production_mutation_safety_summary']['return_fields_used_for_selection']);
    }

    public function test_c65_c64_cleanup_note_is_non_blocking_when_c64_passed_with_no_dominant_blocker(): void
    {
        $this->runService();
        $run = $this->readOutput();

        $this->assertSame('C65_OOS_FAILURE_ATTRIBUTION_IS_ONLY', $run['c64_cleanup_note_summary']['legacy_repair_recommendation']);
        $this->assertTrue($run['c64_cleanup_note_summary']['legacy_repair_recommendation_non_blocking']);
        $this->assertSame('NOT_REQUIRED', $run['c64_cleanup_note_summary']['normalized_repair_recommendation']);
        $this->assertFalse($run['c64_cleanup_note_summary']['c65_failure_repair_required']);
    }

    public function test_c65_rejects_missing_bad_month_governance(): void
    {
        [$path, $hash, $sha1] = $this->writeMutatedArtifact(WatchlistBacktestC65ProductionPreLockReviewService::DEFAULT_C64_ARTIFACT, function (array $c64): array {
            $c64['oos_proof_candidate_scorecard'][0]['oos_bad_month_decision'] = null;
            return $c64;
        }, 'bad-month-missing');

        $result = $this->execute(['c64Artifact' => $path, 'expectedC64Hash' => $hash, 'expectedC64FileSha1' => $sha1]);

        $this->assertSame('C65_PRODUCTION_PRE_LOCK_REVIEW_REJECTED_BAD_MONTH_GOVERNANCE', $result['status']);
        $this->assertFalse($result['production_prelock_review_pass']);
    }

    public function test_c65_rejects_missing_weak_regime_governance(): void
    {
        [$path, $hash, $sha1] = $this->writeMutatedArtifact(WatchlistBacktestC65ProductionPreLockReviewService::DEFAULT_C64_ARTIFACT, function (array $c64): array {
            $c64['oos_proof_candidate_scorecard'][1]['oos_weak_regime_sample_status'] = null;
            return $c64;
        }, 'weak-regime-missing');

        $result = $this->execute(['c64Artifact' => $path, 'expectedC64Hash' => $hash, 'expectedC64FileSha1' => $sha1]);

        $this->assertSame('C65_PRODUCTION_PRE_LOCK_REVIEW_REJECTED_WEAK_REGIME_GOVERNANCE', $result['status']);
        $this->assertFalse($result['production_prelock_review_pass']);
    }

    public function test_c65_rejects_concentration_or_loss_cluster_regression(): void
    {
        [$path, $hash, $sha1] = $this->writeMutatedArtifact(WatchlistBacktestC65ProductionPreLockReviewService::DEFAULT_C64_ARTIFACT, function (array $c64): array {
            $c64['oos_proof_candidate_scorecard'][0]['oos_concentration_regression_detected'] = true;
            return $c64;
        }, 'concentration-regression');

        $result = $this->execute(['c64Artifact' => $path, 'expectedC64Hash' => $hash, 'expectedC64FileSha1' => $sha1]);

        $this->assertSame('C65_PRODUCTION_PRE_LOCK_REVIEW_REJECTED_CONCENTRATION_OR_LOSS_CLUSTER', $result['status']);
        $this->assertFalse($result['production_prelock_review_pass']);
    }

    public function test_c65_rejects_source_bias_or_shared_core_high_risk(): void
    {
        [$path, $hash, $sha1] = $this->writeMutatedArtifact(WatchlistBacktestC65ProductionPreLockReviewService::DEFAULT_C64_ARTIFACT, function (array $c64): array {
            $c64['oos_proof_candidate_scorecard'][0]['oos_source_bias_risk_level'] = 'HIGH';
            return $c64;
        }, 'source-bias-high');

        $result = $this->execute(['c64Artifact' => $path, 'expectedC64Hash' => $hash, 'expectedC64FileSha1' => $sha1]);

        $this->assertSame('C65_PRODUCTION_PRE_LOCK_REVIEW_REJECTED_SOURCE_BIAS_OR_SHARED_CORE', $result['status']);
        $this->assertFalse($result['production_prelock_review_pass']);
    }

    public function test_c65_readiness_decision_is_evidence_based_and_non_production(): void
    {
        $this->runService();
        $run = $this->readOutput();

        $this->assertSame(2, $run['c66_readiness_decision']['candidate_ready_for_c66_count']);
        $this->assertSame('C66_PRODUCTION_LOCK_REVIEW', $run['c66_readiness_decision']['c66_recommendation']);
        $this->assertFalse($run['c66_readiness_decision']['production_ready']);
        $this->assertFalse($run['c66_readiness_decision']['production_catalog_allowed']);
        $this->assertFalse($run['c66_readiness_decision']['production_deployment_allowed']);
        $this->assertSame('NONE', $run['failure_attribution_summary']['dominant_blocker']);
    }

    private function runService(): array
    {
        return $this->execute();
    }

    private function execute(array $overrides = []): array
    {
        $service = new WatchlistBacktestC65ProductionPreLockReviewService();
        return $service->execute(
            (string) ($overrides['c64Artifact'] ?? WatchlistBacktestC65ProductionPreLockReviewService::DEFAULT_C64_ARTIFACT),
            (string) ($overrides['expectedC64Hash'] ?? WatchlistBacktestC65ProductionPreLockReviewService::DEFAULT_EXPECTED_C64_HASH),
            (string) ($overrides['expectedC64FileSha1'] ?? WatchlistBacktestC65ProductionPreLockReviewService::DEFAULT_EXPECTED_C64_FILE_SHA1),
            (string) ($overrides['c63Artifact'] ?? WatchlistBacktestC65ProductionPreLockReviewService::DEFAULT_C63_ARTIFACT),
            (string) ($overrides['expectedC63Hash'] ?? WatchlistBacktestC65ProductionPreLockReviewService::DEFAULT_EXPECTED_C63_HASH),
            (string) ($overrides['expectedC63FileSha1'] ?? WatchlistBacktestC65ProductionPreLockReviewService::DEFAULT_EXPECTED_C63_FILE_SHA1),
            (string) ($overrides['c62Artifact'] ?? WatchlistBacktestC65ProductionPreLockReviewService::DEFAULT_C62_ARTIFACT),
            (string) ($overrides['expectedC62Hash'] ?? WatchlistBacktestC65ProductionPreLockReviewService::DEFAULT_EXPECTED_C62_HASH),
            (string) ($overrides['expectedC62FileSha1'] ?? WatchlistBacktestC65ProductionPreLockReviewService::DEFAULT_EXPECTED_C62_FILE_SHA1),
            (string) ($overrides['c61Artifact'] ?? WatchlistBacktestC65ProductionPreLockReviewService::DEFAULT_C61_ARTIFACT),
            (string) ($overrides['expectedC61Hash'] ?? WatchlistBacktestC65ProductionPreLockReviewService::DEFAULT_EXPECTED_C61_HASH),
            (string) ($overrides['expectedC61FileSha1'] ?? WatchlistBacktestC65ProductionPreLockReviewService::DEFAULT_EXPECTED_C61_FILE_SHA1),
            (string) ($overrides['c60Artifact'] ?? WatchlistBacktestC65ProductionPreLockReviewService::DEFAULT_C60_ARTIFACT),
            (string) ($overrides['expectedC60Hash'] ?? WatchlistBacktestC65ProductionPreLockReviewService::DEFAULT_EXPECTED_C60_HASH),
            (string) ($overrides['expectedC60FileSha1'] ?? WatchlistBacktestC65ProductionPreLockReviewService::DEFAULT_EXPECTED_C60_FILE_SHA1),
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
        $path = 'storage/app/watchlist/backtest/.tmp-c65-'.$name.'.json';
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

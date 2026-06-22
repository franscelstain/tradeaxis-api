<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC64PreOosOrOosProofExecutionService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC64PreOosOrOosProofExecutionServiceTest extends TestCase
{
    private string $output = 'storage/app/watchlist/backtest/.tmp-c64-test-output.json';
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

    public function test_c64_runtime_passes_primary_and_backup_from_locked_c63_hierarchy(): void
    {
        $result = $this->runService();

        $this->assertSame('C64_OOS_PROOF_PASSED_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertSame('C64_OOS_PROOF_PASSED_PRIMARY_AND_BACKUP', $result['reason_code']);
        $this->assertTrue($result['oos_proof_executed']);
        $this->assertTrue($result['oos_proof_pass']);
        $this->assertFalse($result['production_ready']);
        $this->assertFalse($result['direct_oos_proof_recommended']);
        $this->assertFalse($result['oos_proof_unlocked']);
        $this->assertFalse($result['pre_oos_unlocked']);
        $this->assertFileExists($this->output);
    }

    public function test_c64_artifact_records_all_required_sections(): void
    {
        $this->runService();
        $run = $this->readOutput();

        foreach ([
            'source_artifact_locks',
            'database_dictionary_read_summary',
            'c63_lock_validation_summary',
            'c62_lineage_validation_summary',
            'c61_lineage_validation_summary',
            'c60_lineage_validation_summary',
            'selection_freeze_summary',
            'oos_period_summary',
            'c63_decision_replay_summary',
            'oos_proof_candidate_scorecard',
            'oos_bad_month_review_results',
            'oos_weak_regime_review_results',
            'oos_concentration_review_results',
            'oos_loss_cluster_review_results',
            'oos_rolling_review_summary',
            'oos_month_dependency_review_summary',
            'oos_shared_core_review_summary',
            'oos_source_bias_review_summary',
            'oos_safety_and_leakage_audit_summary',
            'oos_proof_decision',
            'c65_readiness_decision',
            'failure_attribution_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }
    }

    public function test_c64_rejects_missing_c63_artifact(): void
    {
        $result = $this->execute(['c63Artifact' => 'storage/app/watchlist/backtest/missing-c63.json']);

        $this->assertSame('C64_BLOCKED_MISSING_C63_ARTIFACT', $result['status']);
        $this->assertFalse($result['oos_proof_executed']);
    }

    public function test_c64_validates_c63_artifact_hash(): void
    {
        $result = $this->execute(['expectedC63Hash' => '0000000000000000000000000000000000000000']);

        $this->assertSame('C64_BLOCKED_C63_ARTIFACT_LOCK_MISMATCH', $result['status']);
        $this->assertFalse($result['c63_hash_match']);
    }

    public function test_c64_validates_c63_file_sha1(): void
    {
        $result = $this->execute(['expectedC63FileSha1' => '0000000000000000000000000000000000000000']);

        $this->assertSame('C64_BLOCKED_C63_FILE_SHA1_LOCK_MISMATCH', $result['status']);
        $this->assertFalse($result['c63_file_sha1_match']);
    }

    public function test_c64_rejects_c63_status_mismatch(): void
    {
        [$path, $hash, $sha1] = $this->writeMutatedArtifact(WatchlistBacktestC64PreOosOrOosProofExecutionService::DEFAULT_C63_ARTIFACT, function (array $c63): array {
            $c63['status'] = 'C63_BROKEN_STATUS';
            return $c63;
        }, 'c63-status');

        $result = $this->execute(['c63Artifact' => $path, 'expectedC63Hash' => $hash, 'expectedC63FileSha1' => $sha1]);

        $this->assertSame('C64_BLOCKED_C63_STATUS_OR_REASON_MISMATCH', $result['status']);
        $this->assertSame('WS_BT_C64_C63_STATUS_INVALID', $result['reason_code']);
    }

    public function test_c64_rejects_c63_reason_code_mismatch(): void
    {
        [$path, $hash, $sha1] = $this->writeMutatedArtifact(WatchlistBacktestC64PreOosOrOosProofExecutionService::DEFAULT_C63_ARTIFACT, function (array $c63): array {
            $c63['reason_code'] = 'C63_BROKEN_REASON';
            return $c63;
        }, 'c63-reason');

        $result = $this->execute(['c63Artifact' => $path, 'expectedC63Hash' => $hash, 'expectedC63FileSha1' => $sha1]);

        $this->assertSame('C64_BLOCKED_C63_STATUS_OR_REASON_MISMATCH', $result['status']);
        $this->assertSame('WS_BT_C64_C63_REASON_INVALID', $result['reason_code']);
    }

    public function test_c64_rejects_c63_candidate_ready_for_c64_count_mismatch(): void
    {
        [$path, $hash, $sha1] = $this->writeMutatedArtifact(WatchlistBacktestC64PreOosOrOosProofExecutionService::DEFAULT_C63_ARTIFACT, function (array $c63): array {
            $c63['c64_readiness_decision']['candidate_ready_for_c64_count'] = 1;
            return $c63;
        }, 'c63-ready-count');

        $result = $this->execute(['c63Artifact' => $path, 'expectedC63Hash' => $hash, 'expectedC63FileSha1' => $sha1]);

        $this->assertSame('C64_BLOCKED_C63_C64_READINESS_COUNT_MISMATCH', $result['status']);
    }

    public function test_c64_rejects_c63_safety_flag_mismatch(): void
    {
        [$path, $hash, $sha1] = $this->writeMutatedArtifact(WatchlistBacktestC64PreOosOrOosProofExecutionService::DEFAULT_C63_ARTIFACT, function (array $c63): array {
            $c63['oos_proof_unlocked'] = true;
            return $c63;
        }, 'c63-safety');

        $result = $this->execute(['c63Artifact' => $path, 'expectedC63Hash' => $hash, 'expectedC63FileSha1' => $sha1]);

        $this->assertSame('C64_BLOCKED_C63_SAFETY_FLAG_MISMATCH', $result['status']);
    }

    public function test_c64_validates_e02_primary(): void
    {
        $this->runService();
        $run = $this->readOutput();

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $run['selection_freeze_summary']['primary_candidate_code']);
    }

    public function test_c64_rejects_e02_primary_mismatch(): void
    {
        [$path, $hash, $sha1] = $this->writeMutatedArtifact(WatchlistBacktestC64PreOosOrOosProofExecutionService::DEFAULT_C63_ARTIFACT, function (array $c63): array {
            $c63['unlock_hierarchy_summary']['primary_unlock_candidate'] = 'BROKEN_PRIMARY';
            return $c63;
        }, 'c63-primary');

        $result = $this->execute(['c63Artifact' => $path, 'expectedC63Hash' => $hash, 'expectedC63FileSha1' => $sha1]);

        $this->assertSame('WS_BT_C64_C63_PRIMARY_INVALID', $result['reason_code']);
    }

    public function test_c64_validates_b01_backup(): void
    {
        $this->runService();
        $run = $this->readOutput();

        $this->assertSame(['C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION'], $run['selection_freeze_summary']['backup_candidate_codes']);
    }

    public function test_c64_rejects_b01_backup_mismatch(): void
    {
        [$path, $hash, $sha1] = $this->writeMutatedArtifact(WatchlistBacktestC64PreOosOrOosProofExecutionService::DEFAULT_C63_ARTIFACT, function (array $c63): array {
            $c63['unlock_hierarchy_summary']['backup_unlock_candidate'] = 'BROKEN_BACKUP';
            return $c63;
        }, 'c63-backup');

        $result = $this->execute(['c63Artifact' => $path, 'expectedC63Hash' => $hash, 'expectedC63FileSha1' => $sha1]);

        $this->assertSame('WS_BT_C64_C63_BACKUP_INVALID', $result['reason_code']);
    }

    public function test_c64_validates_a01_comparator_only(): void
    {
        $this->runService();
        $run = $this->readOutput();

        $this->assertSame(['C61_A01_B01_WEAK_REGIME_QUALITY_FIRST'], $run['selection_freeze_summary']['comparator_only_candidate_codes']);
    }

    public function test_c64_rejects_a01_comparator_mismatch(): void
    {
        [$path, $hash, $sha1] = $this->writeMutatedArtifact(WatchlistBacktestC64PreOosOrOosProofExecutionService::DEFAULT_C63_ARTIFACT, function (array $c63): array {
            $c63['unlock_hierarchy_summary']['comparator_only'] = [];
            return $c63;
        }, 'c63-a01');

        $result = $this->execute(['c63Artifact' => $path, 'expectedC63Hash' => $hash, 'expectedC63FileSha1' => $sha1]);

        $this->assertSame('WS_BT_C64_C63_A01_COMPARATOR_INVALID', $result['reason_code']);
    }

    public function test_c64_validates_c62_lineage_artifact(): void
    {
        $result = $this->execute(['expectedC62Hash' => '0000000000000000000000000000000000000000']);

        $this->assertSame('C64_BLOCKED_LINEAGE_LOCK_MISMATCH', $result['status']);
        $this->assertSame('WS_BT_C64_C62_LINEAGE_LOCK_MISMATCH', $result['reason_code']);
    }

    public function test_c64_validates_c61_lineage_artifact(): void
    {
        $result = $this->execute(['expectedC61Hash' => '0000000000000000000000000000000000000000']);

        $this->assertSame('C64_BLOCKED_LINEAGE_LOCK_MISMATCH', $result['status']);
        $this->assertSame('WS_BT_C64_C61_LINEAGE_LOCK_MISMATCH', $result['reason_code']);
    }

    public function test_c64_validates_c60_lineage_artifact(): void
    {
        $result = $this->execute(['expectedC60Hash' => '0000000000000000000000000000000000000000']);

        $this->assertSame('C64_BLOCKED_LINEAGE_LOCK_MISMATCH', $result['status']);
        $this->assertSame('WS_BT_C64_C60_LINEAGE_LOCK_MISMATCH', $result['reason_code']);
    }

    public function test_c64_validates_c62_file_sha1_lineage(): void
    {
        $result = $this->execute(['expectedC62FileSha1' => '0000000000000000000000000000000000000000']);

        $this->assertSame('WS_BT_C64_C62_LINEAGE_LOCK_MISMATCH', $result['reason_code']);
    }

    public function test_c64_validates_c61_file_sha1_lineage(): void
    {
        $result = $this->execute(['expectedC61FileSha1' => '0000000000000000000000000000000000000000']);

        $this->assertSame('WS_BT_C64_C61_LINEAGE_LOCK_MISMATCH', $result['reason_code']);
    }

    public function test_c64_validates_c60_file_sha1_lineage(): void
    {
        $result = $this->execute(['expectedC60FileSha1' => '0000000000000000000000000000000000000000']);

        $this->assertSame('WS_BT_C64_C60_LINEAGE_LOCK_MISMATCH', $result['reason_code']);
    }

    public function test_c64_database_dictionary_read_rule_is_recorded(): void
    {
        $this->runService();
        $summary = $this->readOutput()['database_dictionary_read_summary'];

        $this->assertTrue($summary['dictionary_rule_acknowledged']);
        $this->assertFalse($summary['dictionary_missing_coverage_detected']);
        $this->assertSame('market_benchmark_indicators.roc_20', $summary['market_index_mapping']['market_index_roc20_source']);
        $this->assertSame('market_calendar.cal_date', $summary['market_index_mapping']['calendar_date_key']);
    }

    public function test_c64_selection_freeze_summary_is_created_before_oos(): void
    {
        $this->runService();
        $freeze = $this->readOutput()['selection_freeze_summary'];

        $this->assertTrue($freeze['validation_completed']);
        $this->assertTrue($freeze['selection_freeze_completed_before_oos']);
        $this->assertFalse($freeze['oos_read_before_selection_freeze']);
    }

    public function test_c64_selection_source_is_c63_locked_hierarchy(): void
    {
        $this->runService();

        $this->assertSame('C63_LOCKED_HIERARCHY', $this->readOutput()['selection_freeze_summary']['selection_source']);
    }

    public function test_c64_does_not_create_new_candidate_after_oos(): void
    {
        $this->runService();

        $this->assertFalse($this->readOutput()['selection_freeze_summary']['new_candidate_created']);
    }

    public function test_c64_does_not_change_selection_rule_after_oos(): void
    {
        $this->runService();

        $this->assertFalse($this->readOutput()['selection_freeze_summary']['selection_rule_changed']);
    }

    public function test_c64_does_not_change_parameter_after_oos(): void
    {
        $this->runService();

        $this->assertFalse($this->readOutput()['selection_freeze_summary']['parameter_changed_after_oos']);
    }

    public function test_c64_oos_period_is_exactly_reserved_period(): void
    {
        $this->runService();
        $period = $this->readOutput()['oos_period_summary'];

        $this->assertSame('2025-05-22', $period['from']);
        $this->assertSame('2026-05-29', $period['to']);
        $this->assertTrue($period['oos_period_valid']);
    }

    public function test_c64_rejects_invalid_oos_period(): void
    {
        $result = $this->execute(['oosTo' => '2026-06-01']);

        $this->assertSame('C64_BLOCKED_OOS_PERIOD_INVALID', $result['status']);
    }

    public function test_c64_requests_no_future_rows_after_oos_period(): void
    {
        $this->runService();

        $this->assertFalse($this->readOutput()['oos_period_summary']['future_rows_after_oos_to_requested']);
    }

    public function test_c64_primary_e02_scorecard_is_generated(): void
    {
        $scorecard = $this->scorecardByCode();

        $this->assertArrayHasKey('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $scorecard);
        $this->assertSame('primary_oos_candidate', $scorecard['C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE']['c64_oos_role']);
    }

    public function test_c64_backup_b01_scorecard_is_generated(): void
    {
        $scorecard = $this->scorecardByCode();

        $this->assertArrayHasKey('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $scorecard);
        $this->assertSame('backup_oos_candidate', $scorecard['C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION']['c64_oos_role']);
    }

    public function test_c64_a01_comparator_scorecard_is_generated_but_not_eligible(): void
    {
        $scorecard = $this->scorecardByCode();
        $a01 = $scorecard['C61_A01_B01_WEAK_REGIME_QUALITY_FIRST'];

        $this->assertSame('comparator_only', $a01['c64_oos_role']);
        $this->assertFalse($a01['oos_proof_pass']);
        $this->assertFalse($a01['candidate_ready_for_c65']);
        $this->assertContains('C64_A01_REMAINS_COMPARATOR_ONLY', $a01['failure_reason_codes']);
    }

    public function test_c64_bad_month_oos_review_is_generated(): void
    {
        $this->runService();
        $rows = $this->readOutput()['oos_bad_month_review_results'];

        $this->assertNotEmpty($rows);
        $this->assertArrayHasKey('oos_bad_month_decision', $rows[0]);
    }

    public function test_c64_weak_regime_oos_review_is_generated(): void
    {
        $this->runService();
        $rows = $this->readOutput()['oos_weak_regime_review_results'];

        $this->assertNotEmpty($rows);
        $this->assertSame('market_down_or_sideways_high_vol', $rows[0]['weakest_regime']);
    }

    public function test_c64_concentration_oos_review_is_generated(): void
    {
        $this->runService();
        $rows = $this->readOutput()['oos_concentration_review_results'];

        $this->assertNotEmpty($rows);
        $this->assertArrayHasKey('oos_max_ticker_share', $rows[0]);
    }

    public function test_c64_loss_cluster_oos_review_is_generated(): void
    {
        $this->runService();
        $rows = $this->readOutput()['oos_loss_cluster_review_results'];

        $this->assertNotEmpty($rows);
        $this->assertArrayHasKey('oos_loss_cluster_share', $rows[0]);
    }

    public function test_c64_rolling_oos_review_is_generated(): void
    {
        $this->runService();

        $this->assertTrue($this->readOutput()['oos_rolling_review_summary']['validation_completed']);
    }

    public function test_c64_month_dependency_oos_review_is_generated(): void
    {
        $this->runService();

        $this->assertArrayHasKey('oos_month_dependency_validation_pass', $this->readOutput()['oos_month_dependency_review_summary']);
    }

    public function test_c64_shared_core_oos_review_is_generated(): void
    {
        $this->runService();

        $this->assertTrue($this->readOutput()['oos_shared_core_review_summary']['a01_remains_comparator_only']);
    }

    public function test_c64_source_bias_oos_review_is_generated(): void
    {
        $this->runService();

        $this->assertTrue($this->readOutput()['oos_source_bias_review_summary']['source_bias_detected']);
    }

    public function test_c64_safety_and_leakage_audit_is_generated(): void
    {
        $this->runService();
        $safety = $this->readOutput()['oos_safety_and_leakage_audit_summary'];

        $this->assertTrue($safety['selection_frozen_before_oos']);
        $this->assertFalse($safety['future_lookup_detected']);
        $this->assertTrue($safety['oos_safety_and_leakage_pass']);
    }

    public function test_c64_candidate_cannot_pass_if_bad_month_risk_high(): void
    {
        $result = $this->runService(['scenario' => 'bad_month_high']);

        $this->assertSame('C64_OOS_PROOF_REJECTED_BAD_MONTH_EXPOSURE', $result['status']);
        $this->assertFalse($result['oos_proof_pass']);
    }

    public function test_c64_candidate_cannot_pass_if_weak_regime_sample_collapse_detected(): void
    {
        $result = $this->runService(['scenario' => 'weak_regime_collapse']);

        $this->assertSame('C64_OOS_PROOF_REJECTED_WEAK_REGIME_FAILURE', $result['status']);
        $this->assertFalse($result['oos_proof_pass']);
    }

    public function test_c64_candidate_cannot_pass_if_oos_sample_insufficient(): void
    {
        $result = $this->runService(['scenario' => 'sample_insufficient']);

        $this->assertSame('C64_OOS_PROOF_REJECTED_WEAK_REGIME_FAILURE', $result['status']);
        $this->assertFalse($result['oos_proof_pass']);
    }

    public function test_c64_candidate_cannot_pass_if_concentration_regression_detected(): void
    {
        $result = $this->runService(['scenario' => 'concentration_regression']);

        $this->assertSame('C64_OOS_PROOF_REJECTED_CONCENTRATION_REGRESSION', $result['status']);
        $this->assertFalse($result['oos_proof_pass']);
    }

    public function test_c64_candidate_cannot_pass_if_loss_cluster_regression_detected(): void
    {
        $result = $this->runService(['scenario' => 'loss_cluster_regression']);

        $this->assertSame('C64_OOS_PROOF_REJECTED_LOSS_CLUSTER_REGRESSION', $result['status']);
        $this->assertFalse($result['oos_proof_pass']);
    }

    public function test_c64_candidate_cannot_pass_if_source_bias_risk_high(): void
    {
        $result = $this->runService(['scenario' => 'source_bias_high']);

        $this->assertSame('C64_OOS_PROOF_REJECTED_SOURCE_BIAS', $result['status']);
        $this->assertFalse($result['oos_proof_pass']);
    }

    public function test_c64_candidate_cannot_pass_if_shared_core_risk_high(): void
    {
        $result = $this->runService(['scenario' => 'shared_core_high']);

        $this->assertSame('C64_OOS_PROOF_REJECTED_SHARED_CORE', $result['status']);
        $this->assertFalse($result['oos_proof_pass']);
    }

    public function test_c64_a01_cannot_become_oos_winner(): void
    {
        $this->runService();
        $decision = $this->readOutput()['oos_proof_decision'];

        $this->assertContains('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $decision['comparator_only_candidate_codes']);
        $this->assertNotSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $decision['primary_oos_candidate_code']);
    }

    public function test_c64_production_ready_is_false_always(): void
    {
        $this->runService();
        $run = $this->readOutput();

        $this->assertFalse($run['production_ready']);
        $this->assertFalse($run['oos_proof_decision']['production_ready']);
        $this->assertFalse($run['c65_readiness_decision']['production_ready']);
    }

    public function test_c64_c65_recommendation_is_evidence_based_when_passed(): void
    {
        $this->runService();
        $decision = $this->readOutput()['c65_readiness_decision'];

        $this->assertSame('C65_PRODUCTION_PRE_LOCK_REVIEW', $decision['c65_recommendation']);
        $this->assertSame(2, $decision['candidate_ready_for_c65_count']);
    }

    public function test_c64_failure_attribution_is_produced_when_oos_proof_fails(): void
    {
        $this->runService(['scenario' => 'bad_month_high']);
        $failure = $this->readOutput()['failure_attribution_summary'];

        $this->assertSame('BAD_MONTH', $failure['dominant_blocker']);
        $this->assertNotEmpty($failure['failure_reason_codes']);
    }

    public function test_c64_does_not_mutate_plan_confirm_or_production_catalog(): void
    {
        $this->runService();
        $safety = $this->readOutput()['oos_safety_and_leakage_audit_summary'];

        $this->assertFalse($safety['plan_confirm_mutated']);
        $this->assertFalse($safety['production_catalog_created']);
    }

    public function test_c64_does_not_use_best_of_failed_promotion_or_oos_retuning(): void
    {
        $this->runService();
        $freeze = $this->readOutput()['selection_freeze_summary'];

        $this->assertFalse($freeze['best_of_failed_promotion_used']);
        $this->assertFalse($freeze['oos_based_tie_break_used']);
    }

    public function test_c64_scorecards_keep_only_locked_c63_hierarchy_candidates(): void
    {
        $scorecard = $this->scorecardByCode();

        $this->assertSame([
            'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE',
            'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION',
            'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST',
        ], array_keys($scorecard));
    }

    private function runService(array $options = []): array
    {
        return $this->execute(['options' => $options]);
    }

    private function execute(array $overrides = []): array
    {
        $options = array_merge(['overwrite' => true], (array) ($overrides['options'] ?? []));

        return (new WatchlistBacktestC64PreOosOrOosProofExecutionService())->execute(
            (string) ($overrides['c63Artifact'] ?? WatchlistBacktestC64PreOosOrOosProofExecutionService::DEFAULT_C63_ARTIFACT),
            (string) ($overrides['expectedC63Hash'] ?? WatchlistBacktestC64PreOosOrOosProofExecutionService::DEFAULT_EXPECTED_C63_HASH),
            (string) ($overrides['expectedC63FileSha1'] ?? WatchlistBacktestC64PreOosOrOosProofExecutionService::DEFAULT_EXPECTED_C63_FILE_SHA1),
            (string) ($overrides['c62Artifact'] ?? WatchlistBacktestC64PreOosOrOosProofExecutionService::DEFAULT_C62_ARTIFACT),
            (string) ($overrides['expectedC62Hash'] ?? WatchlistBacktestC64PreOosOrOosProofExecutionService::DEFAULT_EXPECTED_C62_HASH),
            (string) ($overrides['expectedC62FileSha1'] ?? WatchlistBacktestC64PreOosOrOosProofExecutionService::DEFAULT_EXPECTED_C62_FILE_SHA1),
            (string) ($overrides['c61Artifact'] ?? WatchlistBacktestC64PreOosOrOosProofExecutionService::DEFAULT_C61_ARTIFACT),
            (string) ($overrides['expectedC61Hash'] ?? WatchlistBacktestC64PreOosOrOosProofExecutionService::DEFAULT_EXPECTED_C61_HASH),
            (string) ($overrides['expectedC61FileSha1'] ?? WatchlistBacktestC64PreOosOrOosProofExecutionService::DEFAULT_EXPECTED_C61_FILE_SHA1),
            (string) ($overrides['c60Artifact'] ?? WatchlistBacktestC64PreOosOrOosProofExecutionService::DEFAULT_C60_ARTIFACT),
            (string) ($overrides['expectedC60Hash'] ?? WatchlistBacktestC64PreOosOrOosProofExecutionService::DEFAULT_EXPECTED_C60_HASH),
            (string) ($overrides['expectedC60FileSha1'] ?? WatchlistBacktestC64PreOosOrOosProofExecutionService::DEFAULT_EXPECTED_C60_FILE_SHA1),
            (string) ($overrides['isFrom'] ?? '2023-01-02'),
            (string) ($overrides['isTo'] ?? '2025-05-21'),
            (string) ($overrides['oosFrom'] ?? '2025-05-22'),
            (string) ($overrides['oosTo'] ?? '2026-05-29'),
            $this->output,
            $options
        );
    }

    private function readOutput(): array
    {
        return json_decode((string) file_get_contents($this->output), true);
    }

    private function scorecardByCode(): array
    {
        $this->runService();
        $indexed = [];
        foreach ($this->readOutput()['oos_proof_candidate_scorecard'] as $row) {
            $indexed[(string) $row['candidate_code']] = $row;
        }
        return $indexed;
    }

    private function writeMutatedArtifact(string $sourcePath, callable $mutator, string $suffix): array
    {
        $payload = json_decode((string) file_get_contents($sourcePath), true);
        $payload = $mutator($payload);
        unset($payload['artifact_hash']);
        $payload['artifact_hash'] = sha1(json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        $path = 'storage/app/watchlist/backtest/.tmp-'.$suffix.'.json';
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $this->tmpFiles[] = $path;

        return [$path, (string) $payload['artifact_hash'], strtoupper(sha1_file($path) ?: '')];
    }
}

<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyServiceTest extends TestCase
{
    private string $output;
    private array $tempFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/c62-test-output.json';
        if (is_file($this->output)) {
            unlink($this->output);
        }
    }

    protected function tearDown(): void
    {
        if (is_file($this->output)) {
            unlink($this->output);
        }
        foreach ($this->tempFiles as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        parent::tearDown();
    }

    public function test_c62_runtime_completes_from_locked_c61_and_c60_evidence(): void
    {
        $result = $this->runService();

        $this->assertSame('C62_PRE_LOCK_REVIEW_PASSED_WITH_MULTIPLE_CANDIDATES', $result['status']);
        $this->assertSame(1, (int) $result['c61_hash_match']);
        $this->assertSame(1, (int) $result['c61_file_sha1_match']);
        $this->assertSame(1, (int) $result['c60_hash_match']);
        $this->assertSame(1, (int) $result['c60_file_sha1_match']);
        $this->assertFalse($result['production_ready']);
        $this->assertFalse($result['direct_oos_proof_recommended']);
        $this->assertFalse($result['oos_proof_unlocked']);
        $this->assertFalse($result['pre_oos_unlocked']);
        $this->assertFileExists($this->output);
    }

    public function test_c62_artifact_records_all_required_sections(): void
    {
        $this->runService();
        $run = $this->readOutput();

        foreach ([
            'source_artifact_locks',
            'database_dictionary_read_summary',
            'c61_lock_validation_summary',
            'c60_lineage_validation_summary',
            'c61_ready_candidate_summary',
            'pre_lock_candidate_scorecard',
            'candidate_ranking_summary',
            'month_dependency_audit_results',
            'bad_month_exposure_audit_results',
            'weak_regime_survival_revalidation_results',
            'regime_robustness_revalidation_results',
            'regime_aware_concentration_revalidation_results',
            'loss_cluster_retention_revalidation_results',
            'rolling_stability_recheck_summary',
            'leave_one_month_out_recheck_summary',
            'material_selection_difference_recheck_summary',
            'anti_shared_core_recheck_summary',
            'source_bias_validation_summary',
            'safety_and_leakage_audit_summary',
            'pre_lock_decision',
            'c63_readiness_decision',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }
    }

    public function test_c62_rejects_missing_c61_artifact(): void
    {
        $result = (new WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService())->execute(
            'storage/app/watchlist/backtest/missing-c61.json',
            WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService::DEFAULT_EXPECTED_C61_HASH,
            WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService::DEFAULT_EXPECTED_C61_FILE_SHA1,
            WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService::DEFAULT_C60_ARTIFACT,
            WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService::DEFAULT_EXPECTED_C60_HASH,
            WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService::DEFAULT_EXPECTED_C60_FILE_SHA1,
            '2023-01-02',
            '2025-05-21',
            $this->output,
            ['overwrite' => true]
        );

        $this->assertSame('C62_BLOCKED_MISSING_C61_ARTIFACT', $result['status']);
    }

    public function test_c62_validates_c61_artifact_hash(): void
    {
        $result = $this->runWithExpectedC61('0000000000000000000000000000000000000000', WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService::DEFAULT_EXPECTED_C61_FILE_SHA1);

        $this->assertSame('C62_BLOCKED_C61_ARTIFACT_LOCK_MISMATCH', $result['status']);
        $this->assertSame(0, (int) $result['c61_hash_match']);
    }

    public function test_c62_validates_c61_file_sha1(): void
    {
        $result = $this->runWithExpectedC61(WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService::DEFAULT_EXPECTED_C61_HASH, '0000000000000000000000000000000000000000');

        $this->assertSame('C62_BLOCKED_C61_FILE_SHA1_LOCK_MISMATCH', $result['status']);
        $this->assertSame(0, (int) $result['c61_file_sha1_match']);
    }

    public function test_c62_validates_c60_lineage_artifact_and_sha1(): void
    {
        $result = (new WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService())->execute(
            WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService::DEFAULT_C61_ARTIFACT,
            WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService::DEFAULT_EXPECTED_C61_HASH,
            WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService::DEFAULT_EXPECTED_C61_FILE_SHA1,
            WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService::DEFAULT_C60_ARTIFACT,
            '0000000000000000000000000000000000000000',
            WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService::DEFAULT_EXPECTED_C60_FILE_SHA1,
            '2023-01-02',
            '2025-05-21',
            $this->output,
            ['overwrite' => true]
        );

        $this->assertSame('C62_BLOCKED_C60_ARTIFACT_LOCK_MISMATCH', $result['status']);
        $this->assertSame(0, (int) $result['c60_hash_match']);
    }

    public function test_c62_rejects_c61_status_mismatch(): void
    {
        [$path, $hash, $sha1] = $this->writeMutatedC61(function (array $c61): array {
            $c61['status'] = 'C61_BROKEN_STATUS';
            return $c61;
        });

        $result = (new WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService())->execute(
            $path,
            $hash,
            $sha1,
            WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService::DEFAULT_C60_ARTIFACT,
            WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService::DEFAULT_EXPECTED_C60_HASH,
            WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService::DEFAULT_EXPECTED_C60_FILE_SHA1,
            '2023-01-02',
            '2025-05-21',
            $this->output,
            ['overwrite' => true]
        );

        $this->assertSame('C62_BLOCKED_INVALID_C61_EVIDENCE', $result['status']);
        $this->assertSame('WS_BT_C62_C61_STATUS_INVALID', $result['reason_code']);
    }

    public function test_c62_rejects_c61_ready_candidate_count_mismatch(): void
    {
        [$path, $hash, $sha1] = $this->writeMutatedC61(function (array $c61): array {
            $c61['c62_readiness_decision']['candidate_ready_for_c62_count'] = 2;
            return $c61;
        });

        $result = (new WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService())->execute(
            $path,
            $hash,
            $sha1,
            WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService::DEFAULT_C60_ARTIFACT,
            WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService::DEFAULT_EXPECTED_C60_HASH,
            WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService::DEFAULT_EXPECTED_C60_FILE_SHA1,
            '2023-01-02',
            '2025-05-21',
            $this->output,
            ['overwrite' => true]
        );

        $this->assertSame('C62_BLOCKED_INVALID_C61_EVIDENCE', $result['status']);
        $this->assertSame('WS_BT_C62_C61_READY_COUNT_INVALID', $result['reason_code']);
    }

    public function test_c62_dictionary_and_safety_flags_are_enforced(): void
    {
        $this->runService();
        $run = $this->readOutput();
        $dictionary = $run['database_dictionary_read_summary'];
        $safety = $run['safety_and_leakage_audit_summary'];

        $this->assertTrue($dictionary['dictionary_read_required']);
        $this->assertFalse($dictionary['dictionary_missing_coverage_detected']);
        $this->assertTrue($dictionary['asof_safe']);
        $this->assertFalse($dictionary['future_lookup_detected']);
        $this->assertSame(0, $dictionary['oos_rows_requested']);
        $this->assertFalse($safety['return_fields_used_for_selection']);
        $this->assertFalse($safety['future_path_used_for_selection']);
        $this->assertFalse($safety['oos_return_used_for_selection']);
        $this->assertFalse($safety['production_catalog_created']);
        $this->assertFalse($safety['plan_confirm_mutated']);
        $this->assertTrue($safety['safety_and_leakage_pass']);
    }

    public function test_c62_rejects_oos_date_access(): void
    {
        $result = (new WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService())->execute(
            WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService::DEFAULT_C61_ARTIFACT,
            WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService::DEFAULT_EXPECTED_C61_HASH,
            WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService::DEFAULT_EXPECTED_C61_FILE_SHA1,
            WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService::DEFAULT_C60_ARTIFACT,
            WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService::DEFAULT_EXPECTED_C60_HASH,
            WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService::DEFAULT_EXPECTED_C60_FILE_SHA1,
            '2025-05-22',
            '2025-05-23',
            $this->output,
            ['overwrite' => true]
        );

        $this->assertSame('C62_PRE_LOCK_REVIEW_FAILED_ASOF_OR_OOS_SAFETY', $result['status']);
    }

    public function test_c62_reviews_only_three_c61_ready_candidates_and_includes_expected_identity(): void
    {
        $this->runService();
        $run = $this->readOutput();
        $codes = array_column($run['pre_lock_candidate_scorecard'], 'candidate_code');
        sort($codes);
        $expected = [
            'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST',
            'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION',
            'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE',
        ];
        sort($expected);

        $this->assertSame(3, $run['c61_ready_candidate_summary']['candidate_ready_for_c62_count']);
        $this->assertSame($expected, $codes);
    }

    public function test_c62_scorecard_contains_required_prelock_fields_and_never_unlocks_oos_or_production(): void
    {
        $this->runService();
        $run = $this->readOutput();

        foreach ($run['pre_lock_candidate_scorecard'] as $row) {
            foreach ([
                'candidate_code', 'source_c61_candidate_code', 'parent_candidate_code', 'pre_lock_review_role',
                'evaluated_picks_count', 'avg_ret_net', 'median_ret_net', 'win_rate', 'month_win_rate_min',
                'bad_month_count', 'zero_win_month_count', 'worst_month', 'weak_regime_pick_count',
                'weak_regime_avg_ret_net', 'weak_regime_median_ret_net', 'weak_regime_win_rate',
                'weak_regime_survival_pass', 'regime_robustness_validation_pass', 'rolling_validation_pass',
                'loo_validation_pass', 'bad_month_exposure_pass', 'month_dependency_pass',
                'source_bias_validation_pass', 'safety_and_leakage_pass', 'pre_lock_review_pass',
                'candidate_ready_for_c63', 'failure_reason_codes',
            ] as $field) {
                $this->assertArrayHasKey($field, $row, $field);
            }
        }

        $this->assertFalse($run['production_ready']);
        $this->assertFalse($run['direct_oos_proof_recommended']);
        $this->assertFalse($run['oos_proof_unlocked']);
        $this->assertFalse($run['pre_oos_unlocked']);
    }

    public function test_c62_month_win_rate_zero_bad_month_weak_regime_concentration_loss_rolling_and_loo_are_audited(): void
    {
        $this->runService();
        $run = $this->readOutput();

        $this->assertCount(3, $run['month_dependency_audit_results']);
        $this->assertCount(3, $run['bad_month_exposure_audit_results']);
        $this->assertCount(3, $run['weak_regime_survival_revalidation_results']);
        $this->assertCount(3, $run['regime_robustness_revalidation_results']);
        $this->assertCount(3, $run['regime_aware_concentration_revalidation_results']);
        $this->assertCount(3, $run['loss_cluster_retention_revalidation_results']);
        $this->assertTrue($run['rolling_stability_recheck_summary']['validation_completed']);
        $this->assertTrue($run['leave_one_month_out_recheck_summary']['validation_completed']);

        foreach ($run['month_dependency_audit_results'] as $row) {
            $this->assertSame(0.0, (float) $row['month_win_rate_min']);
            $this->assertSame(1, (int) $row['zero_win_month_count']);
            $this->assertTrue($row['month_dependency_pass']);
        }
    }

    public function test_c62_material_difference_shared_core_and_source_bias_are_rechecked(): void
    {
        $this->runService();
        $run = $this->readOutput();

        $this->assertTrue($run['material_selection_difference_recheck_summary']['validation_completed']);
        $this->assertTrue($run['anti_shared_core_recheck_summary']['validation_completed']);
        $this->assertTrue($run['source_bias_validation_summary']['validation_completed']);
        $this->assertTrue($run['anti_shared_core_recheck_summary']['e02_a01_same_parent_detected']);
        $this->assertTrue($run['anti_shared_core_recheck_summary']['e02_a01_not_promoted_equally']);
        $this->assertTrue($run['source_bias_validation_summary']['source_bias_validation_pass']);
    }

    public function test_c62_candidate_hierarchy_is_produced_and_c63_recommendation_is_is_only(): void
    {
        $this->runService();
        $run = $this->readOutput();
        $decision = $run['pre_lock_decision'];
        $c63 = $run['c63_readiness_decision'];

        $this->assertTrue($decision['validation_completed']);
        $this->assertSame(2, $decision['pre_lock_candidate_count']);
        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $decision['primary_pre_lock_candidate_code']);
        $this->assertSame(['C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION'], $decision['backup_pre_lock_candidate_codes']);
        $this->assertSame(['C61_A01_B01_WEAK_REGIME_QUALITY_FIRST'], $decision['rejected_candidate_codes']);
        $this->assertSame('C63_PRE_OOS_UNLOCK_REVIEW_IS_ONLY', $c63['c63_recommendation']);
        $this->assertFalse($c63['direct_oos_proof_recommended']);
        $this->assertFalse($c63['oos_proof_unlocked']);
        $this->assertFalse($c63['pre_oos_unlocked']);
        $this->assertFalse($c63['production_ready']);
    }

    public function test_c62_candidate_cannot_pass_prelock_if_month_dependency_fails(): void
    {
        [$path, $hash, $sha1] = $this->writeMutatedC61(function (array $c61): array {
            foreach ($c61['leave_one_month_out_results'] as &$row) {
                if ($row['candidate_code'] === 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE') {
                    $row['single_month_dependency_detected'] = true;
                    $row['loo_validation_pass'] = false;
                }
            }
            unset($row);
            return $c61;
        });

        $result = (new WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService())->execute(
            $path,
            $hash,
            $sha1,
            WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService::DEFAULT_C60_ARTIFACT,
            WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService::DEFAULT_EXPECTED_C60_HASH,
            WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService::DEFAULT_EXPECTED_C60_FILE_SHA1,
            '2023-01-02',
            '2025-05-21',
            $this->output,
            ['overwrite' => true]
        );

        $rows = array_column($result['pre_lock_candidate_scorecard'], null, 'candidate_code');
        $this->assertFalse($rows['C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE']['pre_lock_review_pass']);
        $this->assertContains('C62_SINGLE_MONTH_DEPENDENCY_DETECTED', $rows['C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE']['failure_reason_codes']);
    }

    private function runService(): array
    {
        return (new WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService())->execute(
            WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService::DEFAULT_C61_ARTIFACT,
            WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService::DEFAULT_EXPECTED_C61_HASH,
            WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService::DEFAULT_EXPECTED_C61_FILE_SHA1,
            WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService::DEFAULT_C60_ARTIFACT,
            WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService::DEFAULT_EXPECTED_C60_HASH,
            WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService::DEFAULT_EXPECTED_C60_FILE_SHA1,
            '2023-01-02',
            '2025-05-21',
            $this->output,
            ['overwrite' => true, 'executed_at' => '2026-06-22T00:00:00+00:00']
        );
    }

    private function runWithExpectedC61(string $expectedHash, string $expectedSha1): array
    {
        return (new WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService())->execute(
            WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService::DEFAULT_C61_ARTIFACT,
            $expectedHash,
            $expectedSha1,
            WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService::DEFAULT_C60_ARTIFACT,
            WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService::DEFAULT_EXPECTED_C60_HASH,
            WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService::DEFAULT_EXPECTED_C60_FILE_SHA1,
            '2023-01-02',
            '2025-05-21',
            $this->output,
            ['overwrite' => true]
        );
    }

    private function readOutput(): array
    {
        return json_decode((string) file_get_contents($this->output), true);
    }

    private function writeMutatedC61(callable $mutator): array
    {
        $path = 'storage/app/watchlist/backtest/c62-mutated-c61-'.count($this->tempFiles).'.json';
        $c61 = json_decode((string) file_get_contents(WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService::DEFAULT_C61_ARTIFACT), true);
        $c61 = $mutator($c61);
        unset($c61['artifact_hash']);
        $c61['artifact_hash'] = sha1(json_encode($c61, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $json = json_encode($c61, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        file_put_contents($path, $json);
        $this->tempFiles[] = $path;

        return [$path, $c61['artifact_hash'], strtoupper(sha1((string) $json))];
    }
}

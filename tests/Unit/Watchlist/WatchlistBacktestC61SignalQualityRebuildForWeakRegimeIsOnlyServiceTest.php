<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC61SignalQualityRebuildForWeakRegimeIsOnlyService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC61SignalQualityRebuildForWeakRegimeIsOnlyServiceTest extends TestCase
{
    private string $output;

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/c61-test-output.json';
        if (is_file($this->output)) {
            unlink($this->output);
        }
    }

    protected function tearDown(): void
    {
        if (is_file($this->output)) {
            unlink($this->output);
        }
        parent::tearDown();
    }

    public function test_c61_runtime_completes_from_locked_c60_evidence(): void
    {
        $result = $this->runService();

        $this->assertSame('C61_SIGNAL_QUALITY_REBUILD_FOR_WEAK_REGIME_IS_ONLY_COMPLETED', $result['status']);
        $this->assertSame(1, (int) $result['c60_hash_match']);
        $this->assertSame(1, (int) $result['c60_file_sha1_match']);
        $this->assertSame(0, (int) $result['production_ready']);
        $this->assertFileExists($this->output);
    }

    public function test_c61_artifact_records_required_sections_and_safety_flags(): void
    {
        $this->runService();
        $run = json_decode((string) file_get_contents($this->output), true);

        foreach ([
            'source_artifact_locks',
            'database_dictionary_read_summary',
            'c60_blocker_summary',
            'c60_improvement_retention_summary',
            'weak_regime_signal_quality_diagnostics',
            'candidate_generation_summary',
            'candidate_scorecard',
            'weak_regime_signal_quality_results',
            'weak_regime_market_sector_confirmation_results',
            'weak_regime_risk_quality_proxy_results',
            'weak_regime_entry_timing_quality_results',
            'regime_stress_validation_results',
            'regime_aware_concentration_results',
            'loss_cluster_validation_results',
            'concentration_dependency_validation_results',
            'leave_one_month_out_summary',
            'regime_robustness_validation_summary',
            'rolling_validation_summary',
            'sample_recovery_summary',
            'weak_regime_sample_recovery_summary',
            'material_selection_difference_summary',
            'anti_shared_core_summary',
            'source_bias_validation_summary',
            'c62_readiness_decision',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }

        $this->assertFalse($run['production_ready']);
        $this->assertFalse($run['direct_oos_proof_recommended']);
        $this->assertFalse($run['oos_proof_unlocked']);
        $this->assertFalse($run['c62_readiness_decision']['direct_oos_proof_recommended']);
        $this->assertFalse($run['c62_readiness_decision']['oos_proof_unlocked']);
        $this->assertFalse($run['c62_readiness_decision']['production_ready']);
        $this->assertSame(0, $run['database_dictionary_read_summary']['oos_rows_requested']);
        $this->assertFalse($run['database_dictionary_read_summary']['future_lookup_detected']);
        $this->assertTrue($run['database_dictionary_read_summary']['asof_safe']);
        $this->assertTrue($run['database_dictionary_read_summary']['dictionary_read_required']);
    }

    public function test_c61_rejects_missing_c60_artifact(): void
    {
        $result = (new WatchlistBacktestC61SignalQualityRebuildForWeakRegimeIsOnlyService())->execute(
            'storage/app/watchlist/backtest/missing-c60.json',
            WatchlistBacktestC61SignalQualityRebuildForWeakRegimeIsOnlyService::DEFAULT_EXPECTED_C60_HASH,
            WatchlistBacktestC61SignalQualityRebuildForWeakRegimeIsOnlyService::DEFAULT_EXPECTED_C60_FILE_SHA1,
            '2023-01-02',
            '2025-05-21',
            $this->output,
            ['overwrite' => true]
        );

        $this->assertSame('C61_BLOCKED_MISSING_C60_ARTIFACT', $result['status']);
    }

    public function test_c61_validates_c60_artifact_hash(): void
    {
        $result = (new WatchlistBacktestC61SignalQualityRebuildForWeakRegimeIsOnlyService())->execute(
            WatchlistBacktestC61SignalQualityRebuildForWeakRegimeIsOnlyService::DEFAULT_C60_ARTIFACT,
            '0000000000000000000000000000000000000000',
            WatchlistBacktestC61SignalQualityRebuildForWeakRegimeIsOnlyService::DEFAULT_EXPECTED_C60_FILE_SHA1,
            '2023-01-02',
            '2025-05-21',
            $this->output,
            ['overwrite' => true]
        );

        $this->assertSame('C61_BLOCKED_C60_ARTIFACT_LOCK_MISMATCH', $result['status']);
        $this->assertSame(0, (int) $result['c60_hash_match']);
    }

    public function test_c61_validates_c60_file_sha1(): void
    {
        $result = (new WatchlistBacktestC61SignalQualityRebuildForWeakRegimeIsOnlyService())->execute(
            WatchlistBacktestC61SignalQualityRebuildForWeakRegimeIsOnlyService::DEFAULT_C60_ARTIFACT,
            WatchlistBacktestC61SignalQualityRebuildForWeakRegimeIsOnlyService::DEFAULT_EXPECTED_C60_HASH,
            '0000000000000000000000000000000000000000',
            '2023-01-02',
            '2025-05-21',
            $this->output,
            ['overwrite' => true]
        );

        $this->assertSame('C61_BLOCKED_C60_FILE_SHA1_LOCK_MISMATCH', $result['status']);
        $this->assertSame(0, (int) $result['c60_file_sha1_match']);
    }

    public function test_c61_rejects_oos_date_access(): void
    {
        $result = (new WatchlistBacktestC61SignalQualityRebuildForWeakRegimeIsOnlyService())->execute(
            WatchlistBacktestC61SignalQualityRebuildForWeakRegimeIsOnlyService::DEFAULT_C60_ARTIFACT,
            WatchlistBacktestC61SignalQualityRebuildForWeakRegimeIsOnlyService::DEFAULT_EXPECTED_C60_HASH,
            WatchlistBacktestC61SignalQualityRebuildForWeakRegimeIsOnlyService::DEFAULT_EXPECTED_C60_FILE_SHA1,
            '2025-05-22',
            '2025-05-23',
            $this->output,
            ['overwrite' => true]
        );

        $this->assertSame('C61_BLOCKED_OOS_DATE_RANGE_REQUESTED', $result['status']);
    }

    public function test_c61_generates_required_candidate_tracks(): void
    {
        $this->runService();
        $run = json_decode((string) file_get_contents($this->output), true);
        $summary = $run['candidate_generation_summary'];

        $this->assertGreaterThanOrEqual(1, $summary['track_a_weak_regime_signal_quality_rebuild_candidate_count']);
        $this->assertGreaterThanOrEqual(1, $summary['track_b_market_sector_confirmation_candidate_count']);
        $this->assertGreaterThanOrEqual(1, $summary['track_c_risk_quality_proxy_candidate_count']);
        $this->assertGreaterThanOrEqual(1, $summary['track_d_entry_timing_quality_candidate_count']);
        $this->assertGreaterThanOrEqual(1, $summary['track_e_hybrid_c60_improvement_retention_candidate_count']);
        $this->assertTrue($summary['parent_pool_a_c60_strongest_structural_used']);
        $this->assertTrue($summary['parent_pool_b_weak_regime_sample_recovery_used']);
        $this->assertTrue($summary['parent_pool_c_regime_aware_concentration_used']);
        $this->assertTrue($summary['parent_pool_d_loo_improved_used']);
        $this->assertTrue($summary['weak_regime_not_skipped']);
        $this->assertFalse($summary['replay_comparator_promotable']);
    }

    public function test_c61_computes_metrics_for_every_candidate(): void
    {
        $this->runService();
        $run = json_decode((string) file_get_contents($this->output), true);
        $count = count($run['candidate_scorecard']);

        $this->assertGreaterThan(0, $count);
        $this->assertCount($count, $run['weak_regime_signal_quality_results']);
        $this->assertCount($count, $run['weak_regime_market_sector_confirmation_results']);
        $this->assertCount($count, $run['weak_regime_risk_quality_proxy_results']);
        $this->assertCount($count, $run['weak_regime_entry_timing_quality_results']);
        $this->assertCount($count, $run['regime_stress_validation_results']);
        $this->assertCount($count, $run['regime_aware_concentration_results']);
        $this->assertCount($count, $run['loss_cluster_validation_results']);
        $this->assertCount($count, $run['concentration_dependency_validation_results']);

        foreach ($run['candidate_scorecard'] as $candidate) {
            $this->assertArrayHasKey('weak_regime_signal_quality_pass', $candidate);
            $this->assertArrayHasKey('weak_regime_survival_pass', $candidate);
            $this->assertArrayHasKey('regime_robustness_validation_pass', $candidate);
            $this->assertArrayHasKey('candidate_ready_for_c62', $candidate);
            $this->assertFalse($candidate['return_fields_used_for_selection']);
            $this->assertFalse($candidate['future_path_used_for_selection']);
            $this->assertFalse($candidate['oos_return_used_for_selection']);
        }
    }

    public function test_c61_c62_readiness_never_unlocks_oos_or_production(): void
    {
        $this->runService();
        $run = json_decode((string) file_get_contents($this->output), true);

        $this->assertTrue($run['c62_readiness_decision']['validation_completed']);
        $this->assertFalse($run['c62_readiness_decision']['direct_oos_proof_recommended']);
        $this->assertFalse($run['c62_readiness_decision']['oos_proof_unlocked']);
        $this->assertFalse($run['c62_readiness_decision']['production_ready']);
        $this->assertStringStartsWith('C62_', $run['c62_readiness_decision']['c62_recommendation']);
    }

    private function runService(): array
    {
        return (new WatchlistBacktestC61SignalQualityRebuildForWeakRegimeIsOnlyService())->execute(
            WatchlistBacktestC61SignalQualityRebuildForWeakRegimeIsOnlyService::DEFAULT_C60_ARTIFACT,
            WatchlistBacktestC61SignalQualityRebuildForWeakRegimeIsOnlyService::DEFAULT_EXPECTED_C60_HASH,
            WatchlistBacktestC61SignalQualityRebuildForWeakRegimeIsOnlyService::DEFAULT_EXPECTED_C60_FILE_SHA1,
            '2023-01-02',
            '2025-05-21',
            $this->output,
            ['overwrite' => true, 'executed_at' => '2026-06-22T00:00:00+00:00']
        );
    }
}

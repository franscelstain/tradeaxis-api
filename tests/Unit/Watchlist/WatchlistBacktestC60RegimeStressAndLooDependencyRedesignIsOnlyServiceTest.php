<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC60RegimeStressAndLooDependencyRedesignIsOnlyService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC60RegimeStressAndLooDependencyRedesignIsOnlyServiceTest extends TestCase
{
    private string $output;

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/c60-test-output.json';
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

    public function test_c60_runtime_completes_from_locked_c59_evidence(): void
    {
        $result = (new WatchlistBacktestC60RegimeStressAndLooDependencyRedesignIsOnlyService())->execute(
            WatchlistBacktestC60RegimeStressAndLooDependencyRedesignIsOnlyService::DEFAULT_C59_ARTIFACT,
            WatchlistBacktestC60RegimeStressAndLooDependencyRedesignIsOnlyService::DEFAULT_EXPECTED_C59_HASH,
            '2023-01-02',
            '2025-05-21',
            $this->output,
            ['overwrite' => true, 'executed_at' => '2026-06-22T00:00:00+00:00']
        );

        $this->assertSame('C60_REGIME_STRESS_AND_LOO_DEPENDENCY_REDESIGN_IS_ONLY_COMPLETED', $result['status']);
        $this->assertSame(1, (int) $result['c59_hash_match']);
        $this->assertSame(0, (int) $result['production_ready']);
        $this->assertFileExists($this->output);
    }

    public function test_c60_artifact_records_required_sections_and_safety_flags(): void
    {
        (new WatchlistBacktestC60RegimeStressAndLooDependencyRedesignIsOnlyService())->execute(
            WatchlistBacktestC60RegimeStressAndLooDependencyRedesignIsOnlyService::DEFAULT_C59_ARTIFACT,
            WatchlistBacktestC60RegimeStressAndLooDependencyRedesignIsOnlyService::DEFAULT_EXPECTED_C59_HASH,
            '2023-01-02',
            '2025-05-21',
            $this->output,
            ['overwrite' => true, 'executed_at' => '2026-06-22T00:00:00+00:00']
        );
        $run = json_decode((string) file_get_contents($this->output), true);

        foreach ([
            'source_artifact_locks',
            'database_dictionary_read_summary',
            'c59_blocker_summary',
            'c59_improvement_retention_summary',
            'candidate_generation_summary',
            'candidate_scorecard',
            'weak_regime_diagnostics',
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
            'c61_readiness_decision',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }

        $this->assertArrayHasKey('production_ready', $run);
        $this->assertArrayHasKey('direct_oos_proof_recommended', $run);
        $this->assertArrayHasKey('oos_proof_unlocked', $run);

        $this->assertFalse($run['production_ready']);
        $this->assertFalse($run['direct_oos_proof_recommended']);
        $this->assertFalse($run['oos_proof_unlocked']);

        $this->assertFalse($run['c61_readiness_decision']['direct_oos_proof_recommended']);
        $this->assertFalse($run['c61_readiness_decision']['oos_proof_unlocked']);
        $this->assertFalse($run['c61_readiness_decision']['production_ready']);
        $this->assertSame(0, $run['database_dictionary_read_summary']['oos_rows_requested']);
        $this->assertFalse($run['database_dictionary_read_summary']['future_lookup_detected']);
        $this->assertTrue($run['database_dictionary_read_summary']['asof_safe']);
    }

    public function test_c60_rejects_missing_c59_artifact(): void
    {
        $result = (new WatchlistBacktestC60RegimeStressAndLooDependencyRedesignIsOnlyService())->execute(
            'storage/app/watchlist/backtest/missing-c59.json',
            WatchlistBacktestC60RegimeStressAndLooDependencyRedesignIsOnlyService::DEFAULT_EXPECTED_C59_HASH,
            '2023-01-02',
            '2025-05-21',
            $this->output,
            ['overwrite' => true]
        );

        $this->assertSame('C60_BLOCKED_MISSING_C59_ARTIFACT', $result['status']);
    }

    public function test_c60_validates_c59_artifact_hash(): void
    {
        $result = (new WatchlistBacktestC60RegimeStressAndLooDependencyRedesignIsOnlyService())->execute(
            WatchlistBacktestC60RegimeStressAndLooDependencyRedesignIsOnlyService::DEFAULT_C59_ARTIFACT,
            '0000000000000000000000000000000000000000',
            '2023-01-02',
            '2025-05-21',
            $this->output,
            ['overwrite' => true]
        );

        $this->assertSame('C60_BLOCKED_C59_ARTIFACT_LOCK_MISMATCH', $result['status']);
        $this->assertSame(0, (int) $result['c59_hash_match']);
    }

    public function test_c60_rejects_oos_date_access(): void
    {
        $result = (new WatchlistBacktestC60RegimeStressAndLooDependencyRedesignIsOnlyService())->execute(
            WatchlistBacktestC60RegimeStressAndLooDependencyRedesignIsOnlyService::DEFAULT_C59_ARTIFACT,
            WatchlistBacktestC60RegimeStressAndLooDependencyRedesignIsOnlyService::DEFAULT_EXPECTED_C59_HASH,
            '2025-05-22',
            '2025-05-23',
            $this->output,
            ['overwrite' => true]
        );

        $this->assertSame('C60_BLOCKED_OOS_DATE_RANGE_REQUESTED', $result['status']);
    }

    public function test_c60_generates_required_candidate_tracks(): void
    {
        (new WatchlistBacktestC60RegimeStressAndLooDependencyRedesignIsOnlyService())->execute(
            WatchlistBacktestC60RegimeStressAndLooDependencyRedesignIsOnlyService::DEFAULT_C59_ARTIFACT,
            WatchlistBacktestC60RegimeStressAndLooDependencyRedesignIsOnlyService::DEFAULT_EXPECTED_C59_HASH,
            '2023-01-02',
            '2025-05-21',
            $this->output,
            ['overwrite' => true]
        );
        $run = json_decode((string) file_get_contents($this->output), true);
        $summary = $run['candidate_generation_summary'];

        $this->assertGreaterThanOrEqual(1, $summary['track_a_weak_regime_survival_candidate_count']);
        $this->assertGreaterThanOrEqual(1, $summary['track_b_regime_aware_branch_bucket_candidate_count']);
        $this->assertGreaterThanOrEqual(1, $summary['track_c_loo_dependency_breaker_candidate_count']);
        $this->assertGreaterThanOrEqual(1, $summary['track_d_weak_regime_sample_recovery_candidate_count']);
        $this->assertGreaterThanOrEqual(1, $summary['track_e_hybrid_c59_improvement_retention_candidate_count']);
        $this->assertTrue($summary['weak_regime_not_skipped']);
    }

    public function test_c60_computes_metrics_for_every_candidate(): void
    {
        (new WatchlistBacktestC60RegimeStressAndLooDependencyRedesignIsOnlyService())->execute(
            WatchlistBacktestC60RegimeStressAndLooDependencyRedesignIsOnlyService::DEFAULT_C59_ARTIFACT,
            WatchlistBacktestC60RegimeStressAndLooDependencyRedesignIsOnlyService::DEFAULT_EXPECTED_C59_HASH,
            '2023-01-02',
            '2025-05-21',
            $this->output,
            ['overwrite' => true]
        );
        $run = json_decode((string) file_get_contents($this->output), true);
        $count = count($run['candidate_scorecard']);

        $this->assertGreaterThan(0, $count);
        $this->assertCount($count, $run['regime_stress_validation_results']);
        $this->assertCount($count, $run['regime_aware_concentration_results']);
        $this->assertCount($count, $run['loss_cluster_validation_results']);
        $this->assertCount($count, $run['concentration_dependency_validation_results']);
        foreach ($run['candidate_scorecard'] as $candidate) {
            $this->assertArrayHasKey('weak_regime_pick_count', $candidate);
            $this->assertArrayHasKey('loo_validation_pass', $candidate);
            $this->assertArrayHasKey('regime_robustness_validation_pass', $candidate);
            $this->assertFalse($candidate['return_fields_used_for_selection']);
            $this->assertFalse($candidate['future_path_used_for_selection']);
            $this->assertFalse($candidate['oos_return_used_for_selection']);
        }
    }
}

<?php

use App\Application\Watchlist\Services\WatchlistBacktestC52ConcentrationDependencyRedesignContinuationService;
use App\Application\Watchlist\Services\WatchlistBacktestC54RollingStabilityRedesignOrRecalibrationIsOnlyService;

class WatchlistBacktestC54RollingStabilityRedesignOrRecalibrationIsOnlyServiceTest extends TestCase
{
    public function test_it_blocks_missing_or_mismatched_C53_lock(): void
    {
        $service = new WatchlistBacktestC54RollingStabilityRedesignOrRecalibrationIsOnlyService(); $out = $this->path('missing.json');
        $result = $service->execute($this->path('missing-c53.json'), 'h', 's', $this->path('missing-c52.json'), 'h', 's', '2023-01-02', '2025-05-21', $out, ['overwrite' => true]);
        $this->assertSame('C54_BLOCKED_MISSING_C53_ARTIFACT', $result['status']); @unlink($out);

        [$c53, $c52, $out] = $this->fixture('mismatch');
        $result = $service->execute($c53, 'wrong', sha1_file($c53), $c52, $this->hashFile($c52), sha1_file($c52), '2023-01-02', '2025-05-21', $out, ['overwrite' => true]);
        $this->assertSame('C54_BLOCKED_C53_HASH_MISMATCH', $result['status']); $this->cleanup($c53, $c52, $out);
    }

    public function test_it_blocks_invalid_C53_contract_and_safety_flags(): void
    {
        $cases = [
            ['status', 'C53_PENDING', 'C54_BLOCKED_UNEXPECTED_C53_STATUS'],
            ['diagnostic_conclusion', 'C53_OTHER', 'C54_BLOCKED_UNEXPECTED_C53_CONCLUSION'],
            ['next_step_recommendation', 'C54_OTHER', 'C54_BLOCKED_C53_NEXT_STEP_UNEXPECTED'],
            ['production_ready', true, 'C54_BLOCKED_C53_SAFETY_BOUNDARY_INVALID'],
            ['c54_readiness_decision.oos_proof_unlocked', true, 'C54_BLOCKED_C53_SAFETY_BOUNDARY_INVALID'],
            ['c54_readiness_decision.primary_evidence_gap', 'QUALITY', 'C54_BLOCKED_C53_ROLLING_GAP_NOT_CONFIRMED'],
        ];
        foreach ($cases as $i => $case) {
            [$c53, $c52, $out] = $this->fixture('contract-'.$i); $payload = json_decode((string) file_get_contents($c53), true); $this->setNested($payload, $case[0], $case[1]); $payload['artifact_hash'] = $this->stableHash($payload); $this->write($c53, $payload);
            $result = (new WatchlistBacktestC54RollingStabilityRedesignOrRecalibrationIsOnlyService())->execute($c53, $payload['artifact_hash'], sha1_file($c53), $c52, $this->hashFile($c52), sha1_file($c52), '2023-01-02', '2025-05-21', $out, ['overwrite' => true]);
            $this->assertSame($case[2], $result['status'], $case[0]); $this->cleanup($c53, $c52, $out);
        }
    }

    public function test_it_blocks_C52_lock_and_reserved_OOS_period(): void
    {
        [$c53, $c52, $out] = $this->fixture('c52-lock'); $service = new WatchlistBacktestC54RollingStabilityRedesignOrRecalibrationIsOnlyService();
        $result = $service->execute($c53, $this->hashFile($c53), sha1_file($c53), $c52, 'wrong', sha1_file($c52), '2023-01-02', '2025-05-21', $out, ['overwrite' => true]);
        $this->assertSame('C54_BLOCKED_C52_HASH_MISMATCH', $result['status']);
        $result = $service->execute($c53, $this->hashFile($c53), sha1_file($c53), $c52, $this->hashFile($c52), sha1_file($c52), '2025-05-22', '2025-06-30', $out, ['overwrite' => true]);
        $this->assertSame('C54_BLOCKED_VALIDATION_PERIOD_TOUCHES_OOS_RESERVED', $result['status']); $this->cleanup($c53, $c52, $out);
    }

    public function test_completed_C54_preserves_gates_and_routes_remaining_gap_to_C55_IS_only(): void
    {
        [$c53, $c52, $out] = $this->fixture('complete'); $fake = $this->fakeC52(); $service = new WatchlistBacktestC54RollingStabilityRedesignOrRecalibrationIsOnlyService($fake);
        $source = [['trade_date' => '2023-01-02', 'trade_month' => '2023-01', 'ticker' => 'AAA', 'ticker_id' => 1, 'sector_code' => 'S1', 'profile_ret_net' => 0.01]];
        $reconstruction = ['source_rows' => $source, 'source_summary' => ['source_rows_available' => true], 'lineage_rows' => ['months' => ['2023-01'], 'g16' => $source, 'safe_g21' => $source, 'g13' => $source], 'c51_candidate_rows' => [], 'c52_candidate_rows' => ['C52_R07_G16_CAP_55_G21_BACKFILL_SECTOR_AWARE' => $source], 'not_evaluable_reasons' => []];
        $result = $service->execute($c53, $this->hashFile($c53), sha1_file($c53), $c52, $this->hashFile($c52), sha1_file($c52), '2023-01-02', '2025-05-21', $out, ['overwrite' => true, 'executed_at' => '2026-06-21T00:00:00+00:00', 'reconstruction' => $reconstruction]);
        $this->assertSame('C54_ROLLING_STABILITY_REDESIGN_OR_RECALIBRATION_IS_ONLY_COMPLETED', $result['status']); $this->assertSame(0, $result['production_ready']);
        $artifact = json_decode((string) file_get_contents($out), true);
        $this->assertSame('C54_ROLLING_STABILITY_GAP_REMAINS', $artifact['diagnostic_conclusion']); $this->assertSame('C55_ROLLING_STABILITY_REDESIGN_CONTINUATION_IS_ONLY', $artifact['next_step_recommendation']);
        $this->assertCount(12, $artifact['redesign_candidate_definitions']); $this->assertFalse($artifact['rolling_stability_redesign_summary']['gate_thresholds_relaxed']); $this->assertFalse($artifact['rolling_stability_redesign_summary']['adverse_month_exclusion_used']);
        foreach ($artifact['redesign_candidate_definitions'] as $row) { $this->assertFalse($row['return_used_for_selection']); $this->assertFalse($row['adverse_month_exclusion_used']); }
        $this->assertFalse($artifact['c55_readiness_decision']['direct_oos_proof_recommended']); $this->assertFalse($artifact['c55_readiness_decision']['oos_proof_unlocked']); $this->assertFalse($artifact['c55_readiness_decision']['production_ready']);
        $this->cleanup($c53, $c52, $out);
    }

    private function fakeC52(): WatchlistBacktestC52ConcentrationDependencyRedesignContinuationService
    {
        return new class extends WatchlistBacktestC52ConcentrationDependencyRedesignContinuationService {
            public function selectMonthlyQuota(array $rows, array $months, int $quota, string $ranking): array { return $quota > 0 ? $rows : []; }
            public function selectWithExposureCap(array $rows, int $tickerCap, int $sectorCap): array { return $rows; }
            public function evaluateCandidateRowsForC54(array $candidateRows, array $sourceRows, array $lineage, array $c51, bool $sector, array &$not): array
            {
                $replay = []; $concentration = []; $rolling = []; $loo = []; $regime = []; $material = []; $rollingRows = []; $looRows = []; $regimeRows = [];
                foreach ($candidateRows as $code => $rows) {
                    $replay[] = ['candidate_code' => $code, 'evaluated_picks_count' => count($rows), 'avg_ret_net' => 0.01, 'median_ret_net' => 0.01, 'bad_month_like_count' => 1, 'coverage_months' => 29, 'quality_pass' => true, 'stability_pass' => false, 'coverage_pass' => true, 'failure_reason_codes' => ['C52_STABILITY_FAIL']];
                    $concentration[] = ['candidate_code' => $code, 'max_ticker_share' => 0.05, 'max_sector_share' => 0.18, 'max_branch_share' => 0.55, 'max_bucket_share' => 0.55, 'concentration_validation_pass' => true];
                    $rolling[] = ['candidate_code' => $code, 'rolling_pass_rate' => 0.98, 'rolling_validation_pass' => false]; $loo[] = ['candidate_code' => $code, 'loo_validation_pass' => true]; $regime[] = ['candidate_code' => $code, 'regime_robustness_validation_pass' => true]; $material[] = ['candidate_code' => $code, 'material_selection_difference_pass' => true];
                    $rollingRows[] = ['candidate_code' => $code, 'return_used_for_selection' => false]; $looRows[] = ['candidate_code' => $code]; $regimeRows[] = ['candidate_code' => $code];
                }
                return ['candidate_replay_results' => $replay, 'concentration_dependency_validation_results' => $concentration, 'branch_dependency_validation_results' => [], 'bucket_dependency_validation_results' => [], 'sector_dependency_validation_results' => [], 'rolling_validation_results' => $rollingRows, 'rolling_validation_summary' => ['candidate_summaries' => $rolling], 'leave_one_month_out_results' => $looRows, 'leave_one_month_out_summary' => ['candidate_summaries' => $loo], 'regime_robustness_validation_results' => $regimeRows, 'regime_robustness_validation_summary' => ['candidate_summaries' => $regime], 'material_difference_validation_results' => $material];
            }
        };
    }

    private function fixture(string $suffix): array
    {
        $c53 = $this->path($suffix.'-c53.json'); $c52 = $this->path($suffix.'-c52.json'); $out = $this->path($suffix.'-out.json');
        $p53 = ['status' => 'C53_IS_EVIDENCE_EXPANSION_FOR_C52_REDESIGN_COMPLETED', 'production_ready' => false, 'diagnostic_conclusion' => 'C53_ROLLING_STABILITY_EVIDENCE_GAP_CONFIRMED', 'next_step_recommendation' => 'C54_ROLLING_STABILITY_REDESIGN_OR_RECALIBRATION_IS_ONLY', 'c54_readiness_decision' => ['primary_evidence_gap' => 'ROLLING_STABILITY', 'direct_oos_proof_recommended' => false, 'oos_proof_unlocked' => false], 'rolling_evidence_expansion_summary' => ['cohort_candidate_count' => 14, 'rolling_window_count' => 840, 'rolling_stability_failure_count' => 217, 'candidate_full_rolling_pass_count' => 0]]; $p53['artifact_hash'] = $this->stableHash($p53);
        $p52 = ['status' => 'C52_CONCENTRATION_DEPENDENCY_REDESIGN_CONTINUATION_COMPLETED', 'production_ready' => false, 'c51_hash_match' => true, 'c50_hash_match' => true, 'c49_hash_match' => true, 'sector_metadata_reconstruction_summary' => ['sector_metadata_reconstruction_pass' => true], 'source_reconstruction_bias_check' => ['source_bias_validation_pass' => true]]; $p52['artifact_hash'] = $this->stableHash($p52);
        $this->write($c53, $p53); $this->write($c52, $p52); return [$c53, $c52, $out];
    }

    private function path(string $name): string { return storage_path('framework/testing/c54-'.$name); }
    private function write(string $path, array $payload): void { if (! is_dir(dirname($path))) { mkdir(dirname($path), 0775, true); } file_put_contents($path, json_encode($payload, JSON_UNESCAPED_SLASHES)."\n"); }
    private function stableHash(array $payload): string { unset($payload['artifact_hash']); return sha1(json_encode($payload, JSON_UNESCAPED_SLASHES)); }
    private function hashFile(string $path): string { return $this->stableHash(json_decode((string) file_get_contents($path), true)); }
    private function setNested(array &$payload, string $path, $value): void { $ref =& $payload; foreach (explode('.', $path) as $part) { if (! isset($ref[$part]) || ! is_array($ref[$part])) { $ref[$part] = []; } $ref =& $ref[$part]; } $ref = $value; }
    private function cleanup(string ...$paths): void { foreach ($paths as $path) { @unlink($path); } }
}

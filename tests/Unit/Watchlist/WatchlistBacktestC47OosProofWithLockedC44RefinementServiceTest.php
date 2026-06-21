<?php

use App\Application\Watchlist\Services\WatchlistBacktestC47OosProofWithLockedC44RefinementService;

class WatchlistBacktestC47OosProofWithLockedC44RefinementServiceTest extends TestCase
{
    public function test_it_blocks_reserved_OOS_window_mismatch(): void
    {
        $output = $this->path('window-output.json');
        $result = (new WatchlistBacktestC47OosProofWithLockedC44RefinementService())->execute('missing', 'hash', 'missing', 'hash', 'missing', 'hash', '2025-05-23', '2026-05-29', $output, ['overwrite' => true]);
        $this->assertSame('C47_BLOCKED_RESERVED_OOS_WINDOW_MISMATCH', $result['status']);
        $this->assertSame(0, $result['production_ready']);
        @unlink($output);
    }

    public function test_it_blocks_missing_C46_artifact(): void
    {
        $output = $this->path('missing-output.json');
        $result = (new WatchlistBacktestC47OosProofWithLockedC44RefinementService())->execute($this->path('missing-c46.json'), 'hash', 'missing', 'hash', 'missing', 'hash', '2025-05-22', '2026-05-29', $output, ['overwrite' => true]);
        $this->assertSame('C47_BLOCKED_MISSING_C46_ARTIFACT', $result['status']);
        @unlink($output);
    }

    public function test_it_blocks_each_source_hash_mismatch(): void
    {
        [$c46, $c44, $oos, $output] = $this->writeFixtures('hashes', false);
        $cases = [
            ['wrong', $this->hashFromFile($c44), $this->hashFromFile($oos), 'C47_BLOCKED_C46_HASH_MISMATCH'],
            [$this->hashFromFile($c46), 'wrong', $this->hashFromFile($oos), 'C47_BLOCKED_C44_HASH_MISMATCH'],
            [$this->hashFromFile($c46), $this->hashFromFile($c44), 'wrong', 'C47_BLOCKED_OOS_SOURCE_HASH_MISMATCH'],
        ];
        foreach ($cases as $index => $case) {
            $caseOutput = $output.'.'.$index;
            $result = $this->execute($c46, $case[0], $c44, $case[1], $oos, $case[2], $caseOutput);
            $this->assertSame($case[3], $result['status']);
            @unlink($caseOutput);
        }
        $this->cleanup($c46, $c44, $oos, $output);
    }

    public function test_it_blocks_invalid_C46_authorization_or_C44_candidate_lock(): void
    {
        $cases = [
            ['c46', 'review_decision_summary.oos_proof_unlocked', false, 'C47_BLOCKED_C46_OOS_AUTHORIZATION_INVALID'],
            ['c46', 'review_decision_summary.oos_proof_executed', true, 'C47_BLOCKED_C46_OOS_AUTHORIZATION_INVALID'],
            ['c46', 'production_ready', true, 'C47_BLOCKED_C46_OOS_AUTHORIZATION_INVALID'],
            ['c44', 'candidate_summary.best_is_candidate_code', 'OTHER', 'C47_BLOCKED_C44_CANDIDATE_LOCK_INVALID'],
            ['c44', 'source_evidence_summary.monthly_g21_quota', 12, 'C47_BLOCKED_C44_CANDIDATE_LOCK_INVALID'],
            ['c44', 'production_ready', true, 'C47_BLOCKED_C44_CANDIDATE_LOCK_INVALID'],
        ];
        foreach ($cases as $index => $case) {
            [$c46, $c44, $oos, $output] = $this->writeFixtures('boundary-'.$index, false);
            $path = $case[0] === 'c46' ? $c46 : $c44;
            $artifact = json_decode((string) file_get_contents($path), true);
            $this->setNested($artifact, $case[1], $case[2]);
            $artifact['artifact_hash'] = $this->stableHash($artifact);
            $this->writeJson($path, $artifact);
            $result = $this->execute($c46, $this->hashFromFile($c46), $c44, $this->hashFromFile($c44), $oos, $this->hashFromFile($oos), $output);
            $this->assertSame($case[3], $result['status'], $case[1]);
            $this->cleanup($c46, $c44, $oos, $output);
        }
    }

    public function test_it_blocks_missing_exact_date_market_field_coverage(): void
    {
        [$c46, $c44, $oos, $output] = $this->writeFixtures('market-missing', false);
        $rows = $this->marketRows();
        array_pop($rows);
        $result = $this->execute($c46, $this->hashFromFile($c46), $c44, $this->hashFromFile($c44), $oos, $this->hashFromFile($oos), $output, $rows);
        $this->assertSame('C47_BLOCKED_PRE_TRADE_SOURCE_INCOMPLETE', $result['status']);
        $this->cleanup($c46, $c44, $oos, $output);
    }

    public function test_locked_candidate_passes_fixture_OOS_without_tuning_or_reselection(): void
    {
        $artifact = $this->completedArtifact('pass', false);
        $this->assertSame('C47_OOS_PROOF_PASSED_NOT_PRODUCTION_READY', $artifact['status']);
        $this->assertSame('C47_LOCKED_C44_REFINEMENT_OOS_PROOF_PASSED', $artifact['diagnostic_conclusion']);
        $this->assertSame('C48_POST_OOS_GOVERNANCE_REVIEW', $artifact['next_step_recommendation']);
        $this->assertTrue($artifact['oos_gate']['overall_pass']);
        $this->assertSame(72, $artifact['target_oos_result']['evaluated_picks_count']);
        $this->assertSame(52, $artifact['target_oos_result']['selected_g21_rows']);
        $this->assertSame(0, $artifact['oos_source_and_selection_audit']['target_missing_path_count']);
        $this->assertSame(0, $artifact['oos_source_and_selection_audit']['target_lookahead_violation_count']);
        $this->assertFalse($artifact['oos_source_and_selection_audit']['oos_result_used_for_retuning']);
        $this->assertFalse($artifact['oos_source_and_selection_audit']['oos_result_used_for_candidate_reselection']);
        $this->assertFalse($artifact['production_ready']);
    }

    public function test_failed_fixture_OOS_is_reported_without_lowering_gate_or_promoting_candidate(): void
    {
        $artifact = $this->completedArtifact('fail', true);
        $this->assertSame('C47_OOS_PROOF_FAILED', $artifact['status']);
        $this->assertSame('C47_LOCKED_C44_REFINEMENT_OOS_PROOF_FAILED', $artifact['diagnostic_conclusion']);
        $this->assertSame('C48_OOS_FAILURE_ATTRIBUTION_FOR_C44_REFINEMENT', $artifact['next_step_recommendation']);
        $this->assertFalse($artifact['oos_gate']['overall_pass']);
        $this->assertContains('month_win_rate_pass', $artifact['oos_gate']['failed_checks']);
        $this->assertEquals(0.0, $artifact['target_oos_result']['month_win_rate_min']);
        $this->assertFalse($artifact['production_ready']);
    }

    public function test_artifact_keeps_frozen_source_rule_window_and_gate_provenance(): void
    {
        $artifact = $this->completedArtifact('audit', false);
        $this->assertSame(['from' => '2025-05-22', 'to' => '2026-05-29'], $artifact['oos_window']);
        $this->assertSame(13, $artifact['locked_candidate']['monthly_g21_quota']);
        $this->assertSame('prefer non-extended IHSG ROC20 dates, then signal metadata, inside fixed quota', $artifact['locked_candidate']['selection_rule']);
        $this->assertSame('LOCKED_C29_OOS_ACCEPTANCE_GATE_REUSED_WITHOUT_RETUNING', $artifact['oos_gate']['thresholds']['threshold_source']);
        $this->assertTrue($artifact['safety_boundaries']['ONE_SHOT_LOCKED_OOS_PROOF']);
        $this->assertTrue($artifact['safety_boundaries']['OOS_PROOF_EXECUTED']);
        $this->assertFalse($artifact['safety_boundaries']['OOS_PROOF_RESULT_USED_FOR_RETUNING']);
        $this->assertFalse($artifact['production_ready']);
    }

    private function completedArtifact(string $suffix, bool $failing): array
    {
        [$c46, $c44, $oos, $output] = $this->writeFixtures($suffix, $failing);
        $this->execute($c46, $this->hashFromFile($c46), $c44, $this->hashFromFile($c44), $oos, $this->hashFromFile($oos), $output);
        $artifact = json_decode((string) file_get_contents($output), true);
        $this->cleanup($c46, $c44, $oos, $output);
        return $artifact;
    }

    private function execute(string $c46, string $c46Hash, string $c44, string $c44Hash, string $oos, string $oosHash, string $output, array $marketRows = null): array
    {
        return (new WatchlistBacktestC47OosProofWithLockedC44RefinementService())->execute(
            $c46, $c46Hash, $c44, $c44Hash, $oos, $oosHash, '2025-05-22', '2026-05-29', $output,
            ['overwrite' => true, 'executed_at' => '2026-06-21T00:00:00+00:00', 'pre_trade_source_rows' => $marketRows ?? $this->marketRows()]
        );
    }

    private function writeFixtures(string $suffix, bool $failing): array
    {
        $base = sys_get_temp_dir().'/c47-'.$suffix.'-'.uniqid('', true);
        $paths = [$base.'-c46.json', $base.'-c44.json', $base.'-oos.json', $base.'-output.json'];
        $this->writeJson($paths[0], $this->c46Artifact());
        $this->writeJson($paths[1], $this->c44Artifact());
        $this->writeJson($paths[2], $this->oosArtifact($failing));
        return $paths;
    }

    private function c46Artifact(): array
    {
        $artifact = [
            'status' => WatchlistBacktestC47OosProofWithLockedC44RefinementService::EXPECTED_C46_STATUS,
            'diagnostic_conclusion' => WatchlistBacktestC47OosProofWithLockedC44RefinementService::EXPECTED_C46_CONCLUSION,
            'next_step_recommendation' => WatchlistBacktestC47OosProofWithLockedC44RefinementService::EXPECTED_C46_NEXT_STEP,
            'production_ready' => false,
            'source_c45_summary' => ['target_candidate_code' => WatchlistBacktestC47OosProofWithLockedC44RefinementService::TARGET_CANDIDATE_CODE],
            'review_decision_summary' => [
                'all_review_checks_passed' => true, 'warning_review_result' => 'C46_WARNING_BOUNDED_AND_EXPLAINED',
                'candidate_decision' => 'C46_LOCKED_C44_REFINEMENT_APPROVED_FOR_ONE_SHOT_OOS_PROOF',
                'warning_acceptable_for_locked_oos_proof' => true, 'direct_oos_proof_recommended' => true,
                'oos_proof_unlocked' => true, 'oos_proof_executed' => false, 'requires_c47_oos_proof' => true,
                'candidate_reselected' => false,
            ],
        ];
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        return $artifact;
    }

    private function c44Artifact(): array
    {
        $artifact = [
            'status' => WatchlistBacktestC47OosProofWithLockedC44RefinementService::EXPECTED_C44_STATUS,
            'production_ready' => false,
            'source_evidence_summary' => ['monthly_g21_quota' => 13],
            'candidate_summary' => ['best_is_candidate_code' => WatchlistBacktestC47OosProofWithLockedC44RefinementService::TARGET_CANDIDATE_CODE],
            'candidate_results' => [[
                'candidate_code' => WatchlistBacktestC47OosProofWithLockedC44RefinementService::TARGET_CANDIDATE_CODE,
                'selection_rule' => 'prefer non-extended IHSG ROC20 dates, then signal metadata, inside fixed quota',
                'selection_input_fields' => ['market_index_roc20', 'trade_month', 'trade_date', 'ticker', 'param_id', 'row_code'],
                'all_required_guards_passed' => true, 'advancement_gate' => ['passed' => true], 'production_ready' => false,
            ]],
        ];
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        return $artifact;
    }

    private function oosArtifact(bool $failing): array
    {
        $rows = [];
        $months = ['2025-06', '2025-07', '2025-08', '2025-09'];
        foreach ($months as $monthIndex => $month) {
            for ($i = 1; $i <= 5; $i++) {
                $rows[] = $this->oosRow($month.'-'.sprintf('%02d', $i), 'G16', 1000 + $monthIndex * 100 + $i, $failing && $monthIndex === 0 ? -0.02 : 0.02);
            }
            for ($i = 1; $i <= 15; $i++) {
                $rows[] = $this->oosRow($month.'-'.sprintf('%02d', $i), 'G21', 2000 + $monthIndex * 100 + $i, $failing && $monthIndex === 0 ? -0.02 : 0.02);
            }
        }
        $artifact = [
            'status' => 'C29_OOS_PROOF_FAILED', 'artifact_type' => 'C29_OOS_PROOF', 'oos_proof' => true,
            'production_ready' => false, 'oos_window' => ['from' => '2025-05-22', 'to' => '2026-05-29'], 'oos_pick_rows' => $rows,
        ];
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        return $artifact;
    }

    private function oosRow(string $date, string $branch, int $id, float $ret): array
    {
        return [
            'trade_date' => $date, 'trade_month' => substr($date, 0, 7), 'ticker_id' => $id, 'ticker' => $branch.$id,
            'param_id' => 145, 'row_code' => 'ROW_'.$id, 'selected_source_code' => $branch, 'profile_ret_net' => $ret,
            'missing_path_data_flag' => false, 'lookahead_safe' => true, 'future_path_price_used_for_selection' => false,
            'profile_ret_net_used_for_selection' => false, 'production_ready' => 0,
        ];
    }

    private function marketRows(): array
    {
        $rows = [];
        foreach (['2025-06', '2025-07', '2025-08', '2025-09'] as $month) {
            for ($i = 1; $i <= 15; $i++) {
                $rows[] = ['trade_date' => $month.'-'.sprintf('%02d', $i), 'market_index_roc20' => $i <= 13 ? 0.05 : 0.20];
            }
        }
        return $rows;
    }

    private function hashFromFile(string $path): string { $artifact = json_decode((string) file_get_contents($path), true); return $this->stableHash($artifact); }
    private function path(string $name): string { return sys_get_temp_dir().'/c47-'.uniqid('', true).'-'.$name; }
    private function writeJson(string $path, array $payload): void { file_put_contents($path, json_encode($payload, JSON_UNESCAPED_SLASHES)."\n"); }
    private function stableHash(array $payload): string { unset($payload['artifact_hash']); return sha1(json_encode($payload, JSON_UNESCAPED_SLASHES)); }
    private function setNested(array &$payload, string $path, $value): void { $cursor =& $payload; $parts = explode('.', $path); foreach ($parts as $index => $part) { if ($index === count($parts) - 1) { $cursor[$part] = $value; return; } $cursor =& $cursor[$part]; } }
    private function cleanup(string ...$paths): void { foreach ($paths as $path) { @unlink($path); } }
}

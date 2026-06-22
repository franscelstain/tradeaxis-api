<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC63PreOosUnlockReviewIsOnlyService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC63PreOosUnlockReviewIsOnlyServiceTest extends TestCase
{
    private string $output;
    private array $tempFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/c63-test-output.json';
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

    public function test_c63_runtime_approves_primary_and_backup_from_locked_c62_evidence(): void
    {
        $result = $this->runService();

        $this->assertSame('C63_PRE_OOS_UNLOCK_REVIEW_APPROVED_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertSame('C63_PRE_OOS_UNLOCK_REVIEW_APPROVED_PRIMARY_AND_BACKUP', $result['reason_code']);
        $this->assertSame(1, (int) $result['c62_hash_match']);
        $this->assertSame(1, (int) $result['c62_file_sha1_match']);
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

    public function test_c63_artifact_records_all_required_sections(): void
    {
        $this->runService();
        $run = $this->readOutput();

        foreach ([
            'source_artifact_locks',
            'database_dictionary_read_summary',
            'c62_lock_validation_summary',
            'c61_lineage_validation_summary',
            'c60_lineage_validation_summary',
            'c62_decision_replay_summary',
            'unlock_candidate_scorecard',
            'unlock_hierarchy_summary',
            'bad_month_unlock_review_results',
            'weak_regime_unlock_review_results',
            'concentration_unlock_review_results',
            'loss_cluster_unlock_review_results',
            'rolling_unlock_review_summary',
            'loo_unlock_review_summary',
            'shared_core_unlock_review_summary',
            'source_bias_unlock_review_summary',
            'safety_and_leakage_unlock_audit_summary',
            'pre_oos_unlock_decision',
            'c64_readiness_decision',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }
    }

    public function test_c63_rejects_missing_c62_artifact(): void
    {
        [$hash, $sha1] = $this->currentC62Lock();
        $result = $this->execute(
            'storage/app/watchlist/backtest/missing-c62.json',
            $hash,
            $sha1
        );

        $this->assertSame('C63_BLOCKED_MISSING_C62_ARTIFACT', $result['status']);
    }

    public function test_c63_validates_c62_artifact_hash(): void
    {
        [, $sha1] = $this->currentC62Lock();
        $result = $this->execute(
            WatchlistBacktestC63PreOosUnlockReviewIsOnlyService::DEFAULT_C62_ARTIFACT,
            '0000000000000000000000000000000000000000',
            $sha1
        );

        $this->assertSame('C63_BLOCKED_C62_ARTIFACT_LOCK_MISMATCH', $result['status']);
        $this->assertSame(0, (int) $result['c62_hash_match']);
    }

    public function test_c63_validates_c62_file_sha1(): void
    {
        [$hash] = $this->currentC62Lock();
        $result = $this->execute(
            WatchlistBacktestC63PreOosUnlockReviewIsOnlyService::DEFAULT_C62_ARTIFACT,
            $hash,
            '0000000000000000000000000000000000000000'
        );

        $this->assertSame('C63_BLOCKED_C62_FILE_SHA1_LOCK_MISMATCH', $result['status']);
        $this->assertSame(0, (int) $result['c62_file_sha1_match']);
    }

    public function test_c63_rejects_c62_status_mismatch(): void
    {
        [$path, $hash, $sha1] = $this->writeMutatedC62(function (array $c62): array {
            $c62['status'] = 'C62_BROKEN_STATUS';
            return $c62;
        });

        $result = $this->execute($path, $hash, $sha1);

        $this->assertSame('C63_BLOCKED_C62_STATUS_OR_REASON_MISMATCH', $result['status']);
        $this->assertSame('WS_BT_C63_C62_STATUS_INVALID', $result['reason_code']);
    }

    public function test_c63_rejects_c62_reason_code_mismatch(): void
    {
        [$path, $hash, $sha1] = $this->writeMutatedC62(function (array $c62): array {
            $c62['reason_code'] = 'C62_BROKEN_REASON';
            return $c62;
        });

        $result = $this->execute($path, $hash, $sha1);

        $this->assertSame('C63_BLOCKED_C62_STATUS_OR_REASON_MISMATCH', $result['status']);
        $this->assertSame('WS_BT_C63_C62_REASON_INVALID', $result['reason_code']);
    }

    public function test_c63_rejects_c62_ready_count_mismatch(): void
    {
        [$path, $hash, $sha1] = $this->writeMutatedC62(function (array $c62): array {
            $c62['c63_readiness_decision']['candidate_ready_for_c63_count'] = 1;
            return $c62;
        });

        $result = $this->execute($path, $hash, $sha1);

        $this->assertSame('C63_BLOCKED_C62_STATUS_OR_REASON_MISMATCH', $result['status']);
        $this->assertSame('WS_BT_C63_C62_C63_READY_COUNT_INVALID', $result['reason_code']);
    }

    public function test_c63_rejects_c62_c63_recommendation_mismatch(): void
    {
        [$path, $hash, $sha1] = $this->writeMutatedC62(function (array $c62): array {
            $c62['c63_readiness_decision']['c63_recommendation'] = 'BROKEN_RECOMMENDATION';
            return $c62;
        });

        $result = $this->execute($path, $hash, $sha1);

        $this->assertSame('C63_BLOCKED_C62_STATUS_OR_REASON_MISMATCH', $result['status']);
        $this->assertSame('WS_BT_C63_C62_C63_RECOMMENDATION_INVALID', $result['reason_code']);
    }

    public function test_c63_validates_c62_primary_backup_and_a01_comparator_hierarchy(): void
    {
        $this->runService();
        $run = $this->readOutput();

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $run['unlock_hierarchy_summary']['primary_unlock_candidate']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $run['unlock_hierarchy_summary']['backup_unlock_candidate']);
        $this->assertContains('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $run['unlock_hierarchy_summary']['comparator_only']);
        $this->assertSame([], $run['unlock_hierarchy_summary']['rejected']);
        $this->assertContains('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $run['pre_oos_unlock_decision']['comparator_only_candidate_codes']);
        $this->assertSame([], $run['pre_oos_unlock_decision']['rejected_candidate_codes']);
        $this->assertFalse($run['unlock_hierarchy_summary']['a01_promoted_equal_to_e02']);
    }

    public function test_c63_rejects_c62_primary_mismatch(): void
    {
        [$path, $hash, $sha1] = $this->writeMutatedC62(function (array $c62): array {
            $c62['pre_lock_decision']['primary_pre_lock_candidate_code'] = 'BROKEN_PRIMARY';
            return $c62;
        });

        $result = $this->execute($path, $hash, $sha1);

        $this->assertSame('WS_BT_C63_C62_PRIMARY_INVALID', $result['reason_code']);
    }

    public function test_c63_rejects_c62_backup_mismatch(): void
    {
        [$path, $hash, $sha1] = $this->writeMutatedC62(function (array $c62): array {
            $c62['pre_lock_decision']['backup_pre_lock_candidate_codes'] = [];
            return $c62;
        });

        $result = $this->execute($path, $hash, $sha1);

        $this->assertSame('WS_BT_C63_C62_BACKUP_INVALID', $result['reason_code']);
    }

    public function test_c63_rejects_a01_comparator_mismatch(): void
    {
        [$path, $hash, $sha1] = $this->writeMutatedC62(function (array $c62): array {
            $c62['pre_lock_decision']['rejected_candidate_codes'] = [];
            return $c62;
        });

        $result = $this->execute($path, $hash, $sha1);

        $this->assertSame('WS_BT_C63_C62_A01_COMPARATOR_INVALID', $result['reason_code']);
    }

    public function test_c63_validates_c61_and_c60_lineage_locks(): void
    {
        [$hash, $sha1] = $this->currentC62Lock();
        $result = (new WatchlistBacktestC63PreOosUnlockReviewIsOnlyService())->execute(
            WatchlistBacktestC63PreOosUnlockReviewIsOnlyService::DEFAULT_C62_ARTIFACT,
            $hash,
            $sha1,
            WatchlistBacktestC63PreOosUnlockReviewIsOnlyService::DEFAULT_C61_ARTIFACT,
            '0000000000000000000000000000000000000000',
            WatchlistBacktestC63PreOosUnlockReviewIsOnlyService::DEFAULT_EXPECTED_C61_FILE_SHA1,
            WatchlistBacktestC63PreOosUnlockReviewIsOnlyService::DEFAULT_C60_ARTIFACT,
            WatchlistBacktestC63PreOosUnlockReviewIsOnlyService::DEFAULT_EXPECTED_C60_HASH,
            WatchlistBacktestC63PreOosUnlockReviewIsOnlyService::DEFAULT_EXPECTED_C60_FILE_SHA1,
            '2023-01-02',
            '2025-05-21',
            $this->output,
            ['overwrite' => true]
        );

        $this->assertSame('C63_BLOCKED_LINEAGE_LOCK_MISMATCH', $result['status']);
        $this->assertSame('WS_BT_C63_C61_LINEAGE_LOCK_MISMATCH', $result['reason_code']);
    }

    public function test_c63_rejects_oos_date_access(): void
    {
        [$hash, $sha1] = $this->currentC62Lock();
        $result = (new WatchlistBacktestC63PreOosUnlockReviewIsOnlyService())->execute(
            WatchlistBacktestC63PreOosUnlockReviewIsOnlyService::DEFAULT_C62_ARTIFACT,
            $hash,
            $sha1,
            WatchlistBacktestC63PreOosUnlockReviewIsOnlyService::DEFAULT_C61_ARTIFACT,
            WatchlistBacktestC63PreOosUnlockReviewIsOnlyService::DEFAULT_EXPECTED_C61_HASH,
            WatchlistBacktestC63PreOosUnlockReviewIsOnlyService::DEFAULT_EXPECTED_C61_FILE_SHA1,
            WatchlistBacktestC63PreOosUnlockReviewIsOnlyService::DEFAULT_C60_ARTIFACT,
            WatchlistBacktestC63PreOosUnlockReviewIsOnlyService::DEFAULT_EXPECTED_C60_HASH,
            WatchlistBacktestC63PreOosUnlockReviewIsOnlyService::DEFAULT_EXPECTED_C60_FILE_SHA1,
            '2025-05-22',
            '2025-05-23',
            $this->output,
            ['overwrite' => true]
        );

        $this->assertSame('C63_PRE_OOS_UNLOCK_REVIEW_REJECTED_ASOF_OR_OOS_SAFETY', $result['status']);
    }

    public function test_c63_dictionary_and_safety_flags_are_enforced(): void
    {
        $this->runService();
        $run = $this->readOutput();
        $dictionary = $run['database_dictionary_read_summary'];
        $safety = $run['safety_and_leakage_unlock_audit_summary'];

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
        $this->assertTrue($safety['safety_and_leakage_unlock_pass']);
    }

    public function test_c63_scorecard_contains_required_unlock_fields_and_roles(): void
    {
        $this->runService();
        $run = $this->readOutput();
        $scorecard = $run['unlock_candidate_scorecard'];

        $this->assertCount(3, $scorecard);
        $roles = array_column($scorecard, 'c63_unlock_review_role', 'candidate_code');
        $this->assertSame('primary_unlock_candidate', $roles['C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE']);
        $this->assertSame('backup_unlock_candidate', $roles['C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION']);
        $this->assertSame('comparator_only', $roles['C61_A01_B01_WEAK_REGIME_QUALITY_FIRST']);

        foreach ($scorecard as $row) {
            foreach ([
                'bad_month_risk_level',
                'bad_month_risk_acceptable_for_unlock',
                'weak_regime_unlock_ready',
                'rolling_unlock_ready',
                'loo_unlock_ready',
                'concentration_unlock_ready',
                'loss_cluster_unlock_ready',
                'shared_core_unlock_ready',
                'source_bias_unlock_ready',
                'safety_and_leakage_unlock_pass',
                'pre_oos_unlock_review_pass',
                'candidate_ready_for_c64',
            ] as $field) {
                $this->assertArrayHasKey($field, $row, $field);
            }
        }
    }

    public function test_c63_audits_bad_month_and_month_win_rate_min_zero(): void
    {
        $this->runService();
        $run = $this->readOutput();
        $badMonth = array_column($run['bad_month_unlock_review_results'], null, 'candidate_code');

        $this->assertSame('2024-08', $badMonth['C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE']['worst_month']);
        $this->assertSame('2024-11', $badMonth['C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION']['worst_month']);
        $this->assertSame(1, $badMonth['C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE']['zero_win_month_count']);
        $this->assertSame('MODERATE', $badMonth['C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE']['bad_month_risk_level']);
        $this->assertSame('APPROVE_WITH_DOCUMENTED_RISK', $badMonth['C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE']['bad_month_unlock_decision']);
    }

    public function test_c63_audits_weak_regime_unlock_readiness(): void
    {
        $this->runService();
        $run = $this->readOutput();
        $weak = array_column($run['weak_regime_unlock_review_results'], null, 'candidate_code');

        $this->assertSame('market_down_or_sideways_high_vol', $weak['C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE']['weakest_regime']);
        $this->assertTrue($weak['C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE']['weak_regime_unlock_ready']);
        $this->assertTrue($weak['C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION']['weak_regime_unlock_ready']);
        $this->assertTrue($weak['C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE']['weak_regime_improved_vs_c60']);
    }

    public function test_c63_c64_readiness_is_recommendation_only_and_flags_remain_locked_false(): void
    {
        $this->runService();
        $run = $this->readOutput();

        $this->assertTrue($run['pre_oos_unlock_decision']['pre_oos_unlock_approved']);
        $this->assertSame(2, $run['c64_readiness_decision']['candidate_ready_for_c64_count']);
        $this->assertSame('C64_PRE_OOS_OR_OOS_PROOF_EXECUTION', $run['c64_readiness_decision']['c64_recommendation']);
        $this->assertFalse($run['c64_readiness_decision']['direct_oos_proof_recommended']);
        $this->assertFalse($run['c64_readiness_decision']['oos_proof_unlocked']);
        $this->assertFalse($run['c64_readiness_decision']['pre_oos_unlocked']);
        $this->assertFalse($run['c64_readiness_decision']['production_ready']);
    }

    public function test_c63_rejects_high_bad_month_risk(): void
    {
        [$path, $hash, $sha1] = $this->writeMutatedC62(function (array $c62): array {
            foreach ($c62['pre_lock_candidate_scorecard'] as &$row) {
                if ($row['candidate_code'] === 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE') {
                    $row['worst_month_avg_ret_net'] = -0.02;
                }
            }
            unset($row);
            return $c62;
        });

        $result = $this->execute($path, $hash, $sha1);

        $this->assertSame('C63_PRE_OOS_UNLOCK_REVIEW_REJECTED_BAD_MONTH_EXPOSURE', $result['status']);
        $this->assertFalse($result['pre_oos_unlock_decision']['pre_oos_unlock_approved']);
        $this->assertSame('C64_BAD_MONTH_RISK_REPAIR_IS_ONLY', $result['c64_readiness_decision']['c64_recommendation']);
    }

    public function test_c63_rejects_weak_regime_sample_collapse(): void
    {
        [$path, $hash, $sha1] = $this->writeMutatedC62(function (array $c62): array {
            foreach ($c62['pre_lock_candidate_scorecard'] as &$row) {
                if ($row['candidate_code'] === 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE') {
                    $row['weak_regime_pick_count'] = 5;
                    $row['weak_regime_sample_recovery_pass'] = false;
                }
            }
            unset($row);
            return $c62;
        });

        $result = $this->execute($path, $hash, $sha1);

        $this->assertSame('C63_PRE_OOS_UNLOCK_REVIEW_REJECTED_WEAK_REGIME_RISK', $result['status']);
        $this->assertFalse($result['pre_oos_unlock_decision']['pre_oos_unlock_approved']);
    }

    private function runService(): array
    {
        [$hash, $sha1] = $this->currentC62Lock();
        return $this->execute(
            WatchlistBacktestC63PreOosUnlockReviewIsOnlyService::DEFAULT_C62_ARTIFACT,
            $hash,
            $sha1
        );
    }

    private function execute(string $c62Path, string $c62Hash, string $c62Sha1): array
    {
        return (new WatchlistBacktestC63PreOosUnlockReviewIsOnlyService())->execute(
            $c62Path,
            $c62Hash,
            $c62Sha1,
            WatchlistBacktestC63PreOosUnlockReviewIsOnlyService::DEFAULT_C61_ARTIFACT,
            WatchlistBacktestC63PreOosUnlockReviewIsOnlyService::DEFAULT_EXPECTED_C61_HASH,
            WatchlistBacktestC63PreOosUnlockReviewIsOnlyService::DEFAULT_EXPECTED_C61_FILE_SHA1,
            WatchlistBacktestC63PreOosUnlockReviewIsOnlyService::DEFAULT_C60_ARTIFACT,
            WatchlistBacktestC63PreOosUnlockReviewIsOnlyService::DEFAULT_EXPECTED_C60_HASH,
            WatchlistBacktestC63PreOosUnlockReviewIsOnlyService::DEFAULT_EXPECTED_C60_FILE_SHA1,
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

    private function currentC62Lock(): array
    {
        $path = WatchlistBacktestC63PreOosUnlockReviewIsOnlyService::DEFAULT_C62_ARTIFACT;
        $raw = (string) file_get_contents($path);
        $payload = json_decode($raw, true);
        return [(string) $payload['artifact_hash'], strtoupper(sha1($raw))];
    }

    private function writeMutatedC62(callable $mutator): array
    {
        $source = WatchlistBacktestC63PreOosUnlockReviewIsOnlyService::DEFAULT_C62_ARTIFACT;
        $payload = json_decode((string) file_get_contents($source), true);
        $payload = $mutator($payload);
        $path = 'storage/app/watchlist/backtest/c63-mutated-c62-'.count($this->tempFiles).'.json';
        $raw = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        file_put_contents($path, $raw);
        $this->tempFiles[] = $path;
        return [$path, (string) $payload['artifact_hash'], strtoupper(sha1($raw))];
    }
}

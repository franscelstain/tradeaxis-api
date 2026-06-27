<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC98WeeklySwingWatchlistNonLiveRehearsalReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC98WeeklySwingWatchlistNonLiveRehearsalReviewTest extends TestCase
{
    private string $output = 'storage/app/watchlist/backtest/.tmp-c98-test-output.json';
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

    public function test_c98_passes_with_valid_c97_lock_operator_approval_and_reference(): void
    {
        $result = $this->runService();

        $this->assertSame('C98_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_REVIEW_PASSED_NON_LIVE_REHEARSAL_READY_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertSame($result['status'], $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_non_live_rehearsal_review_executed']);
        $this->assertTrue($result['weekly_swing_watchlist_non_live_rehearsal_review_allowed']);
        $this->assertTrue($result['weekly_swing_watchlist_non_live_rehearsal_review_pass']);
        $this->assertTrue($result['weekly_swing_watchlist_non_live_rehearsal_ready']);
        $this->assertTrue($result['weekly_swing_watchlist_non_live_rehearsal_manifest_created']);
        $this->assertTrue($result['primary_candidate_weekly_swing_non_live_rehearsal_ready']);
        $this->assertTrue($result['backup_candidate_weekly_swing_non_live_rehearsal_ready']);
        $this->assertFalse($result['comparator_candidate_weekly_swing_non_live_rehearsal_ready']);
        $this->assertTrue($result['a01_remains_comparator_only']);
        $this->assertTrue($result['c97_audit_archive_finalized']);
        $this->assertFalse($result['temporary_negative_artifacts_remaining']);
        $this->assertTrue($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertSame([], $result['temporary_negative_artifact_paths']);
        $this->assertSame('C99_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_EXECUTION_REVIEW', $result['next_step_recommendation']);
        $this->assertSame('C99_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_EXECUTION_REVIEW', $result['next_readiness_decision']['next_recommendation']);
        $this->assertSame('C98_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_READY_NON_LIVE_ONLY', $result['diagnostic_conclusion']);
        $this->assertFileExists($this->output);
    }

    public function test_c98_rejects_missing_operator_approval(): void
    {
        $result = $this->execute(['operatorApproved' => false]);

        $this->assertSame('C98_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $result['status']);
        $this->assertSame($result['status'], $result['reason_code']);
    }

    public function test_c98_rejects_missing_approval_reference(): void
    {
        $result = $this->execute(['approvalReference' => '']);

        $this->assertSame('C98_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $result['status']);
        $this->assertSame($result['status'], $result['reason_code']);
    }

    public function test_c98_rejects_missing_c97_artifact(): void
    {
        $result = $this->execute([
            'c97Artifact' => 'storage/app/watchlist/backtest/.tmp-c97-missing-for-c98.json',
            'expectedC97Hash' => '5898b6eaa0b537006ba249339c21b5038c8cb6fc',
            'expectedC97FileSha1' => '620FF85234701FD72FC40BB661F068308751C2E4',
        ]);

        $this->assertSame('C98_BLOCKED_C97_ARTIFACT_LOCK_MISMATCH', $result['status']);
    }

    public function test_c98_rejects_expected_c97_artifact_hash_mismatch(): void
    {
        $fixture = $this->lockedC97Fixture();
        $result = $this->execute([
            'c97Artifact' => $fixture['path'],
            'expectedC97Hash' => '0000000000000000000000000000000000000000',
            'expectedC97FileSha1' => $fixture['sha1'],
        ]);

        $this->assertSame('C98_BLOCKED_C97_ARTIFACT_LOCK_MISMATCH', $result['status']);
        $this->assertFalse($result['c97_hash_match']);
    }

    public function test_c98_rejects_expected_c97_file_sha1_mismatch(): void
    {
        $fixture = $this->lockedC97Fixture();
        $result = $this->execute([
            'c97Artifact' => $fixture['path'],
            'expectedC97Hash' => $fixture['hash'],
            'expectedC97FileSha1' => '0000000000000000000000000000000000000000',
        ]);

        $this->assertSame('C98_BLOCKED_C97_FILE_SHA1_LOCK_MISMATCH', $result['status']);
        $this->assertFalse($result['c97_file_sha1_match']);
    }

    public function test_c98_rejects_c97_status_not_passed_audit_archive_finalized(): void
    {
        $result = $this->mutateC97AndExecute(function (array $c97): array {
            $c97['status'] = 'BROKEN_STATUS';
            return $c97;
        }, 'c97-status-broken');

        $this->assertSame('C98_BLOCKED_C97_STATUS_OR_REASON_MISMATCH', $result['status']);
    }

    public function test_c98_rejects_c97_reason_code_not_passed_audit_archive_finalized(): void
    {
        $result = $this->mutateC97AndExecute(function (array $c97): array {
            $c97['reason_code'] = 'BROKEN_REASON';
            return $c97;
        }, 'c97-reason-broken');

        $this->assertSame('C98_BLOCKED_C97_STATUS_OR_REASON_MISMATCH', $result['status']);
    }

    public function test_c98_rejects_c97_next_recommendation_not_c98(): void
    {
        $result = $this->mutateC97AndExecute(function (array $c97): array {
            $c97['next_step_recommendation'] = 'BROKEN_NEXT';
            $c97['next_readiness_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c97['c97_readiness_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c97['audit_archive_finalization_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c97['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $c97;
        }, 'c97-next-broken');

        $this->assertSame('C98_BLOCKED_C97_NEXT_RECOMMENDATION_MISMATCH', $result['status']);
    }

    /**
     * @dataProvider c97AuditArchiveFinalizationFlagProvider
     */
    public function test_c98_rejects_c97_audit_archive_finalization_state_mismatch(string $field, $value): void
    {
        $result = $this->mutateC97AndExecute(function (array $c97) use ($field, $value): array {
            $c97[$field] = $value;
            $c97['c97_readiness_decision'][$field] = $value;
            return $c97;
        }, 'c97-finalization-'.$field);

        $this->assertSame('C98_BLOCKED_C97_AUDIT_ARCHIVE_FINALIZATION_NOT_COMPLETE', $result['status'], $field);
    }

    public function c97AuditArchiveFinalizationFlagProvider(): array
    {
        return [
            ['audit_archive_finalized', false],
            ['audit_archive_finalization_review_pass', false],
            ['primary_candidate_audit_archive_finalized', false],
            ['backup_candidate_audit_archive_finalized', false],
            ['comparator_candidate_audit_archive_finalized', true],
        ];
    }

    public function test_c98_rejects_a01_comparator_only_violation(): void
    {
        $result = $this->mutateC97AndExecute(function (array $c97): array {
            $c97['a01_remains_comparator_only'] = false;
            $c97['c97_readiness_decision']['a01_remains_comparator_only'] = false;
            return $c97;
        }, 'c97-a01-promoted');

        $this->assertSame('C98_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $result['status']);
    }

    /**
     * @dataProvider c97SafetyFlagProvider
     */
    public function test_c98_rejects_any_live_or_mutating_safety_flag_true_in_c97(string $field): void
    {
        $result = $this->mutateC97AndExecute(function (array $c97) use ($field): array {
            $c97[$field] = true;
            return $c97;
        }, 'c97-safety-'.$field);

        $this->assertSame('C98_BLOCKED_C97_SAFETY_FLAG_ALREADY_LIVE_OR_MUTATING', $result['status'], $field);
        $this->assertSame($field, $result['c97_live_or_mutating_safety_flag_failure']);
    }

    public function c97SafetyFlagProvider(): array
    {
        return [
            ['production_ready'],
            ['production_catalog_runtime_wired'],
            ['controlled_opt_in_runtime_bridge_active'],
            ['controlled_parallel_run_active'],
            ['controlled_rollout_active'],
            ['production_deployment_allowed'],
            ['production_deployment_executed'],
            ['plan_confirm_mutation_allowed'],
            ['plan_confirm_mutated'],
            ['plan_confirm_runtime_reads_activated_catalog'],
            ['live_plan_confirm_rollout_allowed'],
            ['live_plan_confirm_rollout_executed'],
            ['pilot_runtime_active'],
            ['shadow_runtime_active'],
            ['runtime_bridge_active'],
            ['weekly_swing_watchlist_runtime_active'],
            ['weekly_swing_watchlist_plan_confirm_mutation_allowed'],
            ['weekly_swing_watchlist_live_output_enabled'],
            ['weekly_swing_watchlist_official_output_generated'],
            ['weekly_swing_watchlist_official_output_published'],
            ['weekly_swing_watchlist_live_recommendation_generated'],
            ['audit_archive_finalization_context_persisted_to_live_runtime'],
        ];
    }

    public function test_c98_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c98-no-temporary-artifact-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}'.PHP_EOL);

        $result = $this->runService();

        $this->assertSame('C98_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS', $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c98_keeps_a01_comparator_only_and_never_promotes_it(): void
    {
        $result = $this->runService();

        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $result['comparator_candidate_code']);
        $this->assertTrue($result['c98_readiness_decision']['a01_remains_comparator_only']);
        $this->assertFalse($result['c98_readiness_decision']['comparator_candidate_weekly_swing_non_live_rehearsal_ready']);
        $this->assertFalse($result['candidate_scope_freeze_summary']['a01_promoted']);
        $this->assertFalse($result['candidate_scope_freeze_summary']['a01_used_as_runtime_fallback']);
    }

    public function test_c98_keeps_e02_primary_and_b01_backup(): void
    {
        $result = $this->runService();

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $result['primary_candidate_code']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $result['backup_candidate_code']);
        $this->assertTrue($result['c98_readiness_decision']['primary_candidate_weekly_swing_non_live_rehearsal_ready']);
        $this->assertTrue($result['c98_readiness_decision']['backup_candidate_weekly_swing_non_live_rehearsal_ready']);
    }

    public function test_c98_writes_artifact_hash_and_c97_source_lock_top_level_aliases(): void
    {
        $result = $this->runService();

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $this->assertSame('5898b6eaa0b537006ba249339c21b5038c8cb6fc', $result['expected_c97_hash']);
        $this->assertSame('5898b6eaa0b537006ba249339c21b5038c8cb6fc', $result['actual_c97_hash']);
        $this->assertTrue($result['c97_hash_match']);
        $this->assertSame('620FF85234701FD72FC40BB661F068308751C2E4', $result['expected_c97_file_sha1']);
        $this->assertSame('620FF85234701FD72FC40BB661F068308751C2E4', $result['actual_c97_file_sha1']);
        $this->assertTrue($result['c97_file_sha1_match']);
    }

    public function test_c98_writes_next_recommendation_c99(): void
    {
        $result = $this->runService();

        $this->assertSame('C99_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_EXECUTION_REVIEW', $result['next_step_recommendation']);
        $this->assertSame('C99_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_EXECUTION_REVIEW', $result['next_readiness_decision']['next_recommendation']);
        $this->assertSame('C99_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_EXECUTION_REVIEW', $result['planned_next_summary']['planned_next_review']);
    }

    public function test_c98_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $outputA = 'storage/app/watchlist/backtest/.tmp-c98-deterministic-a.json';
        $outputB = 'storage/app/watchlist/backtest/.tmp-c98-deterministic-b.json';
        $this->tmpFiles[] = $outputA;
        $this->tmpFiles[] = $outputB;

        $first = $this->execute(['output' => $outputA, 'options' => ['created_at' => '2026-06-28T00:00:00+00:00']]);
        $second = $this->execute(['output' => $outputB, 'options' => ['created_at' => '2026-06-28T00:00:00+00:00']]);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
    }

    public function test_c98_records_required_sections_and_safety_flags(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();

        foreach ([
            'source_artifact_locks',
            'c97_lock_validation_summary',
            'lineage_validation_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'c98_readiness_decision',
            'next_readiness_decision',
            'weekly_swing_watchlist_non_live_rehearsal_decision',
            'weekly_swing_watchlist_non_live_rehearsal_manifest',
            'weekly_swing_watchlist_non_live_rehearsal_candidate_scorecard',
            'weekly_swing_watchlist_non_live_rehearsal_context_summary',
            'runtime_readiness_inspection_summary',
            'feature_flag_operator_approval_kill_switch_validation_summary',
            'c97_audit_archive_finalization_carry_forward_validation_summary',
            'baseline_plan_confirm_non_mutation_summary',
            'production_mutation_safety_summary',
            'documentation_governance_summary',
            'failure_attribution_summary',
            'progress_summary',
            'planned_next_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }

        foreach ($this->requiredSafetyFlags() as $flag) {
            $this->assertFalse($result[$flag], $flag);
            $this->assertFalse($result['production_mutation_safety_summary'][$flag], $flag);
            $this->assertFalse($result['c98_readiness_decision'][$flag], $flag);
        }
    }

    public function test_c98_writes_temporary_negative_artifact_cleanup_fields_and_does_not_mutate_c97_artifact(): void
    {
        $fixture = $this->lockedC97Fixture();
        $before = strtoupper(sha1((string) file_get_contents($fixture['path'])));

        $result = $this->runService();

        $this->assertFalse($result['temporary_negative_artifacts_remaining']);
        $this->assertTrue($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertSame([], $result['temporary_negative_artifact_paths']);
        $this->assertSame($before, strtoupper(sha1((string) file_get_contents($fixture['path']))));
        $this->assertSame('620FF85234701FD72FC40BB661F068308751C2E4', $before);
    }

    public function test_c98_writes_weekly_swing_non_live_rehearsal_flags_correctly(): void
    {
        $result = $this->runService();

        $this->assertTrue($result['weekly_swing_watchlist_non_live_rehearsal_review_executed']);
        $this->assertTrue($result['weekly_swing_watchlist_non_live_rehearsal_review_allowed']);
        $this->assertTrue($result['weekly_swing_watchlist_non_live_rehearsal_review_pass']);
        $this->assertTrue($result['weekly_swing_watchlist_non_live_rehearsal_ready']);
        $this->assertTrue($result['weekly_swing_watchlist_non_live_rehearsal_manifest_created']);
        $this->assertFalse($result['weekly_swing_watchlist_runtime_active']);
        $this->assertFalse($result['weekly_swing_watchlist_plan_confirm_mutation_allowed']);
        $this->assertFalse($result['weekly_swing_watchlist_live_output_enabled']);
    }

    public function test_c98_does_not_create_weekly_swing_live_output_or_official_recommendation(): void
    {
        $result = $this->runService();

        $this->assertFalse($result['weekly_swing_watchlist_official_output_generated']);
        $this->assertFalse($result['weekly_swing_watchlist_official_output_published']);
        $this->assertFalse($result['weekly_swing_watchlist_live_recommendation_generated']);
        $this->assertSame([], $result['weekly_swing_watchlist_non_live_rehearsal_manifest']['official_weekly_swing_stock_recommendations']);
    }

    public function test_c98_writes_artifact_only_manifest_not_used_for_plan_confirm_mutation(): void
    {
        $result = $this->runService();
        $manifest = $result['weekly_swing_watchlist_non_live_rehearsal_manifest'];

        $this->assertTrue($manifest['manifest_created']);
        $this->assertSame('artifact_only_non_live_rehearsal_review', $manifest['manifest_context']);
        $this->assertTrue($manifest['rehearsal_artifact_only']);
        $this->assertFalse($manifest['rehearsal_used_for_selection']);
        $this->assertFalse($manifest['rehearsal_used_for_retuning']);
        $this->assertFalse($manifest['rehearsal_used_for_ranking']);
        $this->assertFalse($manifest['rehearsal_used_for_plan_confirm_mutation']);
        $this->assertFalse($manifest['rehearsal_used_for_live_rollout']);
        $this->assertFalse($manifest['plan_confirm_mutation_allowed']);
    }

    public function test_c98_does_not_mutate_c60_through_c97_artifacts(): void
    {
        $paths = glob('storage/app/watchlist/backtest/c{60,61,62,63,64,65,66,67,68,69,70,71,72,73,74,75,76,77,78,79,80,81,82,83,84,85,86,87,88,89,90,91,92,93,94,95,96,97}*.json', GLOB_BRACE) ?: [];
        $before = [];
        foreach ($paths as $path) {
            $before[$path] = strtoupper(sha1((string) file_get_contents($path)));
        }

        $this->runService();

        foreach ($before as $path => $sha1) {
            $this->assertSame($sha1, strtoupper(sha1((string) file_get_contents($path))), $path);
        }
    }

    private function requiredSafetyFlags(): array
    {
        return [
            'production_ready',
            'production_catalog_runtime_wired',
            'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active',
            'controlled_rollout_active',
            'weekly_swing_watchlist_rehearsal_context_persisted_to_live_runtime',
            'production_deployment_allowed',
            'production_deployment_executed',
            'plan_confirm_mutation_allowed',
            'plan_confirm_mutated',
            'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed',
            'live_plan_confirm_rollout_executed',
            'pilot_runtime_active',
            'shadow_runtime_active',
            'runtime_bridge_active',
            'weekly_swing_watchlist_runtime_active',
            'weekly_swing_watchlist_plan_confirm_mutation_allowed',
            'weekly_swing_watchlist_live_output_enabled',
            'weekly_swing_watchlist_official_output_generated',
            'weekly_swing_watchlist_official_output_published',
            'weekly_swing_watchlist_live_recommendation_generated',
        ];
    }

    private function runService(array $overrides = []): array
    {
        return $this->execute($overrides);
    }

    private function execute(array $overrides = []): array
    {
        $service = new WatchlistBacktestC98WeeklySwingWatchlistNonLiveRehearsalReviewService();
        $fixture = $this->lockedC97Fixture();
        return $service->execute(
            (string) ($overrides['c97Artifact'] ?? $fixture['path']),
            (string) ($overrides['expectedC97Hash'] ?? $fixture['hash']),
            (string) ($overrides['expectedC97FileSha1'] ?? $fixture['sha1']),
            (string) ($overrides['output'] ?? $this->output),
            array_merge([
                'overwrite' => true,
                'operator_approved' => $overrides['operatorApproved'] ?? true,
                'approval_reference' => $overrides['approvalReference'] ?? 'C98_OPERATOR_APPROVED_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_REVIEW_ONLY',
            ], (array) ($overrides['options'] ?? []))
        );
    }

    private function mutateC97AndExecute(callable $mutator, string $name): array
    {
        $fixture = $this->lockedC97Fixture();
        $payload = json_decode((string) file_get_contents($fixture['path']), true);
        $payload = $mutator(is_array($payload) ? $payload : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c98-source-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
        return $this->execute([
            'c97Artifact' => $path,
            'expectedC97Hash' => (string) ($payload['artifact_hash'] ?? ''),
            'expectedC97FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function lockedC97Fixture(): array
    {
        $path = 'storage/app/watchlist/backtest/c97-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-finalization-review.json';
        $payload = json_decode((string) file_get_contents($path), true);
        return [
            'path' => $path,
            'hash' => (string) ($payload['artifact_hash'] ?? ''),
            'sha1' => strtoupper(sha1((string) file_get_contents($path))),
        ];
    }

    private function readOutput(): array
    {
        $decoded = json_decode((string) file_get_contents($this->output), true);
        return is_array($decoded) ? $decoded : [];
    }
}

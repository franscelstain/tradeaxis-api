<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC99WeeklySwingWatchlistNonLiveRehearsalExecutionReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC99WeeklySwingWatchlistNonLiveRehearsalExecutionReviewTest extends TestCase
{
    private string $output = 'storage/app/watchlist/backtest/.tmp-c99-test-output.json';
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

    public function test_c99_passes_with_valid_c98_lock_operator_approval_and_reference(): void
    {
        $result = $this->runService();

        $this->assertSame('C99_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_EXECUTION_REVIEW_PASSED_NON_LIVE_REHEARSAL_EXECUTED_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertSame($result['status'], $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_non_live_rehearsal_execution_review_executed']);
        $this->assertTrue($result['weekly_swing_watchlist_non_live_rehearsal_execution_review_allowed']);
        $this->assertTrue($result['weekly_swing_watchlist_non_live_rehearsal_execution_review_pass']);
        $this->assertTrue($result['weekly_swing_watchlist_non_live_rehearsal_executed']);
        $this->assertTrue($result['weekly_swing_watchlist_non_live_rehearsal_execution_manifest_created']);
        $this->assertTrue($result['primary_candidate_weekly_swing_non_live_rehearsal_executed']);
        $this->assertTrue($result['backup_candidate_weekly_swing_non_live_rehearsal_executed']);
        $this->assertFalse($result['comparator_candidate_weekly_swing_non_live_rehearsal_executed']);
        $this->assertTrue($result['a01_remains_comparator_only']);
        $this->assertTrue($result['c98_non_live_rehearsal_ready']);
        $this->assertSame('C100_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_RESULT_REVIEW', $result['next_step_recommendation']);
        $this->assertSame('C99_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_EXECUTED_NON_LIVE_ONLY', $result['diagnostic_conclusion']);
        $this->assertFileExists($this->output);
    }

    public function test_c99_rejects_missing_operator_approval(): void
    {
        $result = $this->execute(['operatorApproved' => false]);

        $this->assertSame('C99_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_EXECUTION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $result['status']);
        $this->assertSame($result['status'], $result['reason_code']);
    }

    public function test_c99_rejects_missing_approval_reference(): void
    {
        $result = $this->execute(['approvalReference' => '']);

        $this->assertSame('C99_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_EXECUTION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $result['status']);
        $this->assertSame($result['status'], $result['reason_code']);
    }

    public function test_c99_rejects_missing_c98_artifact(): void
    {
        $result = $this->execute([
            'c98Artifact' => 'storage/app/watchlist/backtest/.tmp-c98-missing-for-c99.json',
            'expectedC98Hash' => '269eb05141a2acf28925fdef51df9263955b0143',
            'expectedC98FileSha1' => '762BAFFCFCB104E10C9D8C6F6CCBD4E990766702',
        ]);

        $this->assertSame('C99_BLOCKED_C98_ARTIFACT_LOCK_MISMATCH', $result['status']);
    }

    public function test_c99_rejects_expected_c98_artifact_hash_mismatch(): void
    {
        $fixture = $this->lockedC98Fixture();
        $result = $this->execute([
            'c98Artifact' => $fixture['path'],
            'expectedC98Hash' => '0000000000000000000000000000000000000000',
            'expectedC98FileSha1' => $fixture['sha1'],
        ]);

        $this->assertSame('C99_BLOCKED_C98_ARTIFACT_LOCK_MISMATCH', $result['status']);
        $this->assertFalse($result['c98_hash_match']);
    }

    public function test_c99_rejects_expected_c98_file_sha1_mismatch(): void
    {
        $fixture = $this->lockedC98Fixture();
        $result = $this->execute([
            'c98Artifact' => $fixture['path'],
            'expectedC98Hash' => $fixture['hash'],
            'expectedC98FileSha1' => '0000000000000000000000000000000000000000',
        ]);

        $this->assertSame('C99_BLOCKED_C98_FILE_SHA1_LOCK_MISMATCH', $result['status']);
        $this->assertFalse($result['c98_file_sha1_match']);
    }

    public function test_c99_rejects_c98_status_not_passed_non_live_rehearsal_ready(): void
    {
        $result = $this->mutateC98AndExecute(function (array $c98): array {
            $c98['status'] = 'BROKEN_STATUS';
            return $c98;
        }, 'c98-status-broken');

        $this->assertSame('C99_BLOCKED_C98_STATUS_OR_REASON_MISMATCH', $result['status']);
    }

    public function test_c99_rejects_c98_reason_code_not_passed_non_live_rehearsal_ready(): void
    {
        $result = $this->mutateC98AndExecute(function (array $c98): array {
            $c98['reason_code'] = 'BROKEN_REASON';
            return $c98;
        }, 'c98-reason-broken');

        $this->assertSame('C99_BLOCKED_C98_STATUS_OR_REASON_MISMATCH', $result['status']);
    }

    public function test_c99_rejects_c98_next_recommendation_not_c99(): void
    {
        $result = $this->mutateC98AndExecute(function (array $c98): array {
            $c98['next_step_recommendation'] = 'BROKEN_NEXT';
            $c98['next_readiness_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c98['c98_readiness_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c98['weekly_swing_watchlist_non_live_rehearsal_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c98['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $c98;
        }, 'c98-next-broken');

        $this->assertSame('C99_BLOCKED_C98_NEXT_RECOMMENDATION_MISMATCH', $result['status']);
    }

    /**
     * @dataProvider c98NonLiveRehearsalReadyFlagProvider
     */
    public function test_c99_rejects_c98_non_live_rehearsal_ready_state_mismatch(string $field, $value): void
    {
        $result = $this->mutateC98AndExecute(function (array $c98) use ($field, $value): array {
            $c98[$field] = $value;
            $c98['c98_readiness_decision'][$field] = $value;
            return $c98;
        }, 'c98-ready-'.$field);

        $this->assertSame('C99_BLOCKED_C98_NON_LIVE_REHEARSAL_READY_STATE_NOT_COMPLETE', $result['status'], $field);
    }

    public function c98NonLiveRehearsalReadyFlagProvider(): array
    {
        return [
            ['weekly_swing_watchlist_non_live_rehearsal_review_pass', false],
            ['weekly_swing_watchlist_non_live_rehearsal_ready', false],
            ['weekly_swing_watchlist_non_live_rehearsal_manifest_created', false],
            ['primary_candidate_weekly_swing_non_live_rehearsal_ready', false],
            ['backup_candidate_weekly_swing_non_live_rehearsal_ready', false],
            ['comparator_candidate_weekly_swing_non_live_rehearsal_ready', true],
        ];
    }

    public function test_c99_rejects_c98_manifest_not_artifact_only(): void
    {
        $result = $this->mutateC98AndExecute(function (array $c98): array {
            $c98['weekly_swing_watchlist_non_live_rehearsal_manifest']['rehearsal_artifact_only'] = false;
            return $c98;
        }, 'c98-manifest-not-artifact-only');

        $this->assertSame('C99_BLOCKED_C98_NON_LIVE_REHEARSAL_READY_STATE_NOT_COMPLETE', $result['status']);
    }

    public function test_c99_rejects_c98_manifest_used_for_plan_confirm_mutation(): void
    {
        $result = $this->mutateC98AndExecute(function (array $c98): array {
            $c98['weekly_swing_watchlist_non_live_rehearsal_manifest']['rehearsal_used_for_plan_confirm_mutation'] = true;
            return $c98;
        }, 'c98-manifest-plan-confirm');

        $this->assertSame('C99_BLOCKED_C98_NON_LIVE_REHEARSAL_READY_STATE_NOT_COMPLETE', $result['status']);
    }

    public function test_c99_rejects_a01_comparator_only_violation(): void
    {
        $result = $this->mutateC98AndExecute(function (array $c98): array {
            $c98['a01_remains_comparator_only'] = false;
            $c98['c98_readiness_decision']['a01_remains_comparator_only'] = false;
            return $c98;
        }, 'c98-a01-promoted');

        $this->assertSame('C99_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_EXECUTION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $result['status']);
    }

    /**
     * @dataProvider c98SafetyFlagProvider
     */
    public function test_c99_rejects_any_live_or_mutating_safety_flag_true_in_c98(string $field): void
    {
        $result = $this->mutateC98AndExecute(function (array $c98) use ($field): array {
            $c98[$field] = true;
            return $c98;
        }, 'c98-safety-'.$field);

        $this->assertSame('C99_BLOCKED_C98_SAFETY_FLAG_ALREADY_LIVE_OR_MUTATING', $result['status'], $field);
        $this->assertSame($field, $result['c98_live_or_mutating_safety_flag_failure']);
    }

    public function c98SafetyFlagProvider(): array
    {
        return [
            ['production_ready'],
            ['production_catalog_runtime_wired'],
            ['controlled_opt_in_runtime_bridge_active'],
            ['controlled_parallel_run_active'],
            ['controlled_rollout_active'],
            ['weekly_swing_watchlist_rehearsal_context_persisted_to_live_runtime'],
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
        ];
    }

    public function test_c99_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c99-no-temporary-artifact-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}'.PHP_EOL);

        $result = $this->runService();

        $this->assertSame('C99_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_EXECUTION_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS', $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c99_keeps_a01_comparator_only_and_never_promotes_it(): void
    {
        $result = $this->runService();

        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $result['comparator_candidate_code']);
        $this->assertTrue($result['c99_execution_decision']['a01_remains_comparator_only']);
        $this->assertFalse($result['c99_execution_decision']['comparator_candidate_weekly_swing_non_live_rehearsal_executed']);
        $this->assertFalse($result['candidate_scope_freeze_summary']['a01_promoted']);
        $this->assertFalse($result['candidate_scope_freeze_summary']['a01_used_as_runtime_fallback']);
    }

    public function test_c99_keeps_e02_primary_and_b01_backup(): void
    {
        $result = $this->runService();

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $result['primary_candidate_code']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $result['backup_candidate_code']);
        $this->assertTrue($result['c99_execution_decision']['primary_candidate_weekly_swing_non_live_rehearsal_executed']);
        $this->assertTrue($result['c99_execution_decision']['backup_candidate_weekly_swing_non_live_rehearsal_executed']);
    }

    public function test_c99_writes_artifact_hash_and_c98_source_lock_top_level_aliases(): void
    {
        $result = $this->runService();

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $this->assertSame('269eb05141a2acf28925fdef51df9263955b0143', $result['expected_c98_hash']);
        $this->assertSame('269eb05141a2acf28925fdef51df9263955b0143', $result['actual_c98_hash']);
        $this->assertTrue($result['c98_hash_match']);
        $this->assertSame('762BAFFCFCB104E10C9D8C6F6CCBD4E990766702', $result['expected_c98_file_sha1']);
        $this->assertSame('762BAFFCFCB104E10C9D8C6F6CCBD4E990766702', $result['actual_c98_file_sha1']);
        $this->assertTrue($result['c98_file_sha1_match']);
    }

    public function test_c99_writes_next_recommendation_c100(): void
    {
        $result = $this->runService();

        $this->assertSame('C100_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_RESULT_REVIEW', $result['next_step_recommendation']);
        $this->assertSame('C100_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_RESULT_REVIEW', $result['next_readiness_decision']['next_recommendation']);
        $this->assertSame('C100_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_RESULT_REVIEW', $result['planned_next_summary']['planned_next_review']);
    }

    public function test_c99_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $outputA = 'storage/app/watchlist/backtest/.tmp-c99-deterministic-a.json';
        $outputB = 'storage/app/watchlist/backtest/.tmp-c99-deterministic-b.json';
        $this->tmpFiles[] = $outputA;
        $this->tmpFiles[] = $outputB;

        $first = $this->execute(['output' => $outputA, 'options' => ['created_at' => '2026-06-28T00:00:00+00:00']]);
        $second = $this->execute(['output' => $outputB, 'options' => ['created_at' => '2026-06-28T00:00:00+00:00']]);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
    }

    public function test_c99_records_required_sections_and_safety_flags(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();

        foreach ([
            'source_artifact_locks',
            'c98_lock_validation_summary',
            'lineage_validation_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'c99_execution_decision',
            'next_readiness_decision',
            'weekly_swing_watchlist_non_live_rehearsal_execution_decision',
            'weekly_swing_watchlist_non_live_rehearsal_execution_manifest',
            'weekly_swing_watchlist_non_live_rehearsal_execution_candidate_scorecard',
            'weekly_swing_watchlist_non_live_rehearsal_execution_context_summary',
            'runtime_readiness_inspection_summary',
            'feature_flag_operator_approval_kill_switch_validation_summary',
            'c98_non_live_rehearsal_ready_carry_forward_validation_summary',
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
            $this->assertFalse($result['c99_execution_decision'][$flag], $flag);
        }
    }

    public function test_c99_writes_temporary_negative_artifact_cleanup_fields_and_does_not_mutate_c98_artifact(): void
    {
        $fixture = $this->lockedC98Fixture();
        $before = strtoupper(sha1((string) file_get_contents($fixture['path'])));

        $result = $this->runService();

        $this->assertFalse($result['temporary_negative_artifacts_remaining']);
        $this->assertTrue($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertSame([], $result['temporary_negative_artifact_paths']);
        $this->assertSame($before, strtoupper(sha1((string) file_get_contents($fixture['path']))));
        $this->assertSame('762BAFFCFCB104E10C9D8C6F6CCBD4E990766702', $before);
    }

    public function test_c99_writes_weekly_swing_non_live_rehearsal_execution_flags_correctly(): void
    {
        $result = $this->runService();

        $this->assertTrue($result['weekly_swing_watchlist_non_live_rehearsal_execution_review_executed']);
        $this->assertTrue($result['weekly_swing_watchlist_non_live_rehearsal_execution_review_allowed']);
        $this->assertTrue($result['weekly_swing_watchlist_non_live_rehearsal_execution_review_pass']);
        $this->assertTrue($result['weekly_swing_watchlist_non_live_rehearsal_executed']);
        $this->assertTrue($result['weekly_swing_watchlist_non_live_rehearsal_execution_manifest_created']);
        $this->assertFalse($result['weekly_swing_watchlist_runtime_active']);
        $this->assertFalse($result['weekly_swing_watchlist_plan_confirm_mutation_allowed']);
        $this->assertFalse($result['weekly_swing_watchlist_live_output_enabled']);
    }

    public function test_c99_does_not_create_weekly_swing_live_output_or_official_recommendation(): void
    {
        $result = $this->runService();

        $this->assertFalse($result['weekly_swing_watchlist_official_output_generated']);
        $this->assertFalse($result['weekly_swing_watchlist_official_output_published']);
        $this->assertFalse($result['weekly_swing_watchlist_live_recommendation_generated']);
        $this->assertSame([], $result['weekly_swing_watchlist_non_live_rehearsal_execution_manifest']['official_weekly_swing_stock_recommendations']);
    }

    public function test_c99_writes_artifact_only_execution_manifest_not_used_for_plan_confirm_mutation(): void
    {
        $result = $this->runService();
        $manifest = $result['weekly_swing_watchlist_non_live_rehearsal_execution_manifest'];

        $this->assertTrue($manifest['manifest_created']);
        $this->assertSame('artifact_only_non_live_rehearsal_execution_review', $manifest['manifest_context']);
        $this->assertSame('non_live_artifact_only_rehearsal_execution', $manifest['execution_mode']);
        $this->assertTrue($manifest['rehearsal_execution_artifact_only']);
        $this->assertFalse($manifest['rehearsal_execution_used_for_selection']);
        $this->assertFalse($manifest['rehearsal_execution_used_for_retuning']);
        $this->assertFalse($manifest['rehearsal_execution_used_for_ranking']);
        $this->assertFalse($manifest['rehearsal_execution_used_for_plan_confirm_mutation']);
        $this->assertFalse($manifest['rehearsal_execution_used_for_live_rollout']);
        $this->assertFalse($manifest['plan_confirm_mutation_allowed']);
    }

    public function test_c99_does_not_mutate_c60_through_c98_artifacts(): void
    {
        $paths = glob('storage/app/watchlist/backtest/c{60,61,62,63,64,65,66,67,68,69,70,71,72,73,74,75,76,77,78,79,80,81,82,83,84,85,86,87,88,89,90,91,92,93,94,95,96,97,98}*.json', GLOB_BRACE) ?: [];
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
            'weekly_swing_watchlist_non_live_rehearsal_execution_context_persisted_to_live_runtime',
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
        $service = new WatchlistBacktestC99WeeklySwingWatchlistNonLiveRehearsalExecutionReviewService();
        $fixture = $this->lockedC98Fixture();
        return $service->execute(
            (string) ($overrides['c98Artifact'] ?? $fixture['path']),
            (string) ($overrides['expectedC98Hash'] ?? $fixture['hash']),
            (string) ($overrides['expectedC98FileSha1'] ?? $fixture['sha1']),
            (string) ($overrides['output'] ?? $this->output),
            array_merge([
                'overwrite' => true,
                'operator_approved' => $overrides['operatorApproved'] ?? true,
                'approval_reference' => $overrides['approvalReference'] ?? 'C99_OPERATOR_APPROVED_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_EXECUTION_REVIEW_ONLY',
            ], (array) ($overrides['options'] ?? []))
        );
    }

    private function mutateC98AndExecute(callable $mutator, string $name): array
    {
        $fixture = $this->lockedC98Fixture();
        $payload = json_decode((string) file_get_contents($fixture['path']), true);
        $payload = $mutator(is_array($payload) ? $payload : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c99-source-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
        return $this->execute([
            'c98Artifact' => $path,
            'expectedC98Hash' => (string) ($payload['artifact_hash'] ?? ''),
            'expectedC98FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function lockedC98Fixture(): array
    {
        $path = 'storage/app/watchlist/backtest/c98-weekly-swing-watchlist-non-live-rehearsal-review.json';
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

<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC104WeeklySwingWatchlistNonLiveRehearsalHandoffReadinessReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC104WeeklySwingWatchlistNonLiveRehearsalHandoffReadinessReviewTest extends TestCase
{
    private const PASS_STATUS = 'C104_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_READINESS_REVIEW_PASSED_HANDOFF_READY_PRIMARY_AND_BACKUP';
    private const NEXT_C105 = 'C105_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_FINALIZATION_REVIEW';
    private const C103_HASH = '60954783fd524694581bd1b4cdb47a71bdcd7bcb';
    private const C103_SHA1 = 'F61E6BAF148D974CEE483D45164E0D5F6BD51376';

    private string $output;

    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c104-handoff-readiness-pass.json';
        @unlink($this->output);
    }

    protected function tearDown(): void
    {
        @unlink($this->output);
        foreach ($this->tmpFiles as $path) {
            @unlink($path);
        }
        parent::tearDown();
    }

    public function test_c104_passes_with_valid_c103_completion_boundary_lock_operator_approval_and_reference(): void
    {
        $result = $this->runService();

        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame($result['status'], $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_review_executed']);
        $this->assertTrue($result['weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_review_allowed']);
        $this->assertTrue($result['weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_review_pass']);
        $this->assertTrue($result['weekly_swing_watchlist_non_live_rehearsal_handoff_ready']);
        $this->assertTrue($result['handoff_ready']);
        $this->assertTrue($result['completion_boundary_cleared']);
        $this->assertSame('BOUNDARY_CLEARED_GO', $result['boundary_go_decision']);
        $this->assertSame('GO', $result['operator_go_decision']);
        $this->assertTrue($result['c103_completion_boundary_cleared']);
        $this->assertTrue($result['primary_candidate_weekly_swing_non_live_rehearsal_handoff_ready']);
        $this->assertTrue($result['backup_candidate_weekly_swing_non_live_rehearsal_handoff_ready']);
        $this->assertFalse($result['comparator_candidate_weekly_swing_non_live_rehearsal_handoff_ready']);
        $this->assertTrue($result['a01_remains_comparator_only']);
        $this->assertSame(self::NEXT_C105, $result['next_step_recommendation']);
        $this->assertSame('READY_FOR_C105_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_FINALIZATION_REVIEW', $result['diagnostic_conclusion']);
        $this->assertFileExists($this->output);
    }

    public function test_c104_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->execute(['operatorApproved' => false]);
        $missingReference = $this->execute(['approvalReference' => '']);

        $this->assertSame('C104_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_READINESS_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingOperator['status']);
        $this->assertSame('C104_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_READINESS_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingReference['status']);
    }

    public function test_c104_rejects_unconfirmed_handoff_readiness(): void
    {
        $result = $this->execute(['options' => ['handoff_readiness_confirmed' => false]]);

        $this->assertSame('C104_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_READINESS_REVIEW_REJECTED_HANDOFF_READINESS_NOT_CONFIRMED', $result['status']);
        $this->assertFalse($result['handoff_ready']);
    }

    public function test_c104_rejects_missing_c103_artifact(): void
    {
        $result = $this->execute([
            'c103Artifact' => 'storage/app/watchlist/backtest/missing-c103-for-c104.json',
            'expectedC103Hash' => 'missing',
            'expectedC103FileSha1' => 'missing',
        ]);

        $this->assertSame('C104_BLOCKED_C103_ARTIFACT_LOCK_MISMATCH', $result['status']);
    }

    public function test_c104_rejects_expected_c103_artifact_hash_mismatch(): void
    {
        $fixture = $this->lockedC103Fixture();
        $result = $this->execute([
            'c103Artifact' => $fixture['path'],
            'expectedC103Hash' => 'bad-hash',
            'expectedC103FileSha1' => $fixture['sha1'],
        ]);

        $this->assertSame('C104_BLOCKED_C103_ARTIFACT_LOCK_MISMATCH', $result['status']);
        $this->assertFalse($result['c103_hash_match']);
    }

    public function test_c104_rejects_expected_c103_file_sha1_mismatch(): void
    {
        $fixture = $this->lockedC103Fixture();
        $result = $this->execute([
            'c103Artifact' => $fixture['path'],
            'expectedC103Hash' => $fixture['hash'],
            'expectedC103FileSha1' => 'BADSHA1',
        ]);

        $this->assertSame('C104_BLOCKED_C103_FILE_SHA1_LOCK_MISMATCH', $result['status']);
        $this->assertFalse($result['c103_file_sha1_match']);
    }

    public function test_c104_rejects_c103_status_or_reason_mismatch(): void
    {
        $status = $this->mutateC103AndExecute(function (array $c103): array {
            $c103['status'] = 'BROKEN_STATUS';
            return $c103;
        }, 'c103-status-broken');

        $reason = $this->mutateC103AndExecute(function (array $c103): array {
            $c103['reason_code'] = 'BROKEN_REASON';
            return $c103;
        }, 'c103-reason-broken');

        $this->assertSame('C104_BLOCKED_C103_STATUS_OR_REASON_MISMATCH', $status['status']);
        $this->assertSame('C104_BLOCKED_C103_STATUS_OR_REASON_MISMATCH', $reason['status']);
    }

    public function test_c104_rejects_c103_next_recommendation_not_c104(): void
    {
        $result = $this->mutateC103AndExecute(function (array $c103): array {
            $c103['next_step_recommendation'] = 'BROKEN_NEXT';
            $c103['next_readiness_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c103['c103_completion_boundary_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c103['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $c103;
        }, 'c103-next-broken');

        $this->assertSame('C104_BLOCKED_C103_NEXT_RECOMMENDATION_MISMATCH', $result['status']);
    }

    /**
     * @dataProvider c103CompletionBoundaryStateMismatchProvider
     */
    public function test_c104_rejects_c103_completion_boundary_state_mismatch(string $field, $value): void
    {
        $result = $this->mutateC103AndExecute(function (array $c103) use ($field, $value): array {
            $c103[$field] = $value;
            $c103['c103_completion_boundary_decision'][$field] = $value;
            $c103['next_readiness_decision'][$field] = $value;
            return $c103;
        }, 'c103-state-'.$field);

        $this->assertSame('C104_BLOCKED_C103_COMPLETION_BOUNDARY_NOT_CLEARED', $result['status'], $field);
    }

    public function c103CompletionBoundaryStateMismatchProvider(): array
    {
        return [
            ['weekly_swing_watchlist_non_live_rehearsal_completion_boundary_review_pass', false],
            ['weekly_swing_watchlist_non_live_rehearsal_completion_boundary_cleared', false],
            ['completion_boundary_cleared', false],
            ['boundary_go_decision', 'NO_GO'],
            ['operator_go_decision', 'NO_GO'],
            ['go_decision_finalized', false],
            ['primary_candidate_weekly_swing_non_live_rehearsal_completion_boundary_cleared', false],
            ['backup_candidate_weekly_swing_non_live_rehearsal_completion_boundary_cleared', false],
            ['comparator_candidate_weekly_swing_non_live_rehearsal_completion_boundary_cleared', true],
        ];
    }

    public function test_c104_rejects_c103_c104_readiness_count_mismatch(): void
    {
        $result = $this->mutateC103AndExecute(function (array $c103): array {
            $c103['next_readiness_decision']['candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_review_count'] = 1;
            return $c103;
        }, 'c103-readiness-count-broken');

        $this->assertSame('C104_BLOCKED_C103_COMPLETION_BOUNDARY_NOT_CLEARED', $result['status']);
    }

    public function test_c104_rejects_c103_completion_boundary_manifest_not_artifact_only_or_used_for_live_output(): void
    {
        $notArtifactOnly = $this->mutateC103AndExecute(function (array $c103): array {
            $c103['weekly_swing_watchlist_non_live_rehearsal_completion_boundary_manifest']['completion_boundary_artifact_only'] = false;
            return $c103;
        }, 'c103-manifest-not-artifact-only');

        $usedForPlanConfirm = $this->mutateC103AndExecute(function (array $c103): array {
            $c103['weekly_swing_watchlist_non_live_rehearsal_completion_boundary_manifest']['completion_boundary_used_for_plan_confirm_mutation'] = true;
            return $c103;
        }, 'c103-manifest-plan-confirm');

        $officialOutput = $this->mutateC103AndExecute(function (array $c103): array {
            $c103['weekly_swing_watchlist_non_live_rehearsal_completion_boundary_manifest']['official_weekly_swing_stock_recommendations'] = ['SHOULD_NOT_EXIST'];
            return $c103;
        }, 'c103-manifest-official-output');

        $this->assertSame('C104_BLOCKED_C103_COMPLETION_BOUNDARY_NOT_CLEARED', $notArtifactOnly['status']);
        $this->assertSame('C104_BLOCKED_C103_COMPLETION_BOUNDARY_NOT_CLEARED', $usedForPlanConfirm['status']);
        $this->assertSame('C104_BLOCKED_C103_COMPLETION_BOUNDARY_NOT_CLEARED', $officialOutput['status']);
    }

    public function test_c104_rejects_a01_comparator_only_violation(): void
    {
        $result = $this->mutateC103AndExecute(function (array $c103): array {
            $c103['a01_remains_comparator_only'] = false;
            $c103['candidate_scope_freeze_summary']['a01_promoted'] = true;
            return $c103;
        }, 'c103-a01-promoted');

        $this->assertSame('C104_BLOCKED_C103_COMPLETION_BOUNDARY_NOT_CLEARED', $result['status']);
    }

    /**
     * @dataProvider safetyFlagProvider
     */
    public function test_c104_rejects_any_live_or_mutating_safety_flag_true_in_c103(string $field): void
    {
        $result = $this->mutateC103AndExecute(function (array $c103) use ($field): array {
            $c103[$field] = true;
            return $c103;
        }, 'c103-safety-'.$field);

        $this->assertSame('C104_BLOCKED_C103_SAFETY_FLAG_ALREADY_LIVE_OR_MUTATING', $result['status'], $field);
        $this->assertSame($field, $result['c103_live_or_mutating_safety_flag_failure']);
    }

    public function safetyFlagProvider(): array
    {
        return array_map(static function (string $flag): array {
            return [$flag];
        }, $this->requiredSafetyFlags());
    }

    public function test_c104_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c104-no-temporary-artifact-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame('C104_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_READINESS_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS', $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c104_keeps_e02_primary_b01_backup_and_a01_comparator_only(): void
    {
        $result = $this->runService();

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $result['primary_candidate_code']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $result['backup_candidate_code']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $result['comparator_candidate_code']);
        $this->assertTrue($result['c104_handoff_readiness_decision']['primary_candidate_weekly_swing_non_live_rehearsal_handoff_ready']);
        $this->assertTrue($result['c104_handoff_readiness_decision']['backup_candidate_weekly_swing_non_live_rehearsal_handoff_ready']);
        $this->assertFalse($result['c104_handoff_readiness_decision']['comparator_candidate_weekly_swing_non_live_rehearsal_handoff_ready']);
        $this->assertFalse($result['candidate_scope_freeze_summary']['a01_promoted']);
    }

    public function test_c104_writes_artifact_hash_and_c103_source_lock_top_level_aliases(): void
    {
        $result = $this->runService();

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $this->assertSame(self::C103_HASH, $result['expected_c103_hash']);
        $this->assertSame(self::C103_HASH, $result['actual_c103_hash']);
        $this->assertTrue($result['c103_hash_match']);
        $this->assertSame(self::C103_SHA1, $result['expected_c103_file_sha1']);
        $this->assertSame(self::C103_SHA1, $result['actual_c103_file_sha1']);
        $this->assertTrue($result['c103_file_sha1_match']);
    }

    public function test_c104_writes_next_recommendation_c105_handoff_finalization(): void
    {
        $result = $this->runService();

        $this->assertSame(self::NEXT_C105, $result['next_step_recommendation']);
        $this->assertSame(self::NEXT_C105, $result['next_readiness_decision']['next_recommendation']);
        $this->assertSame(self::NEXT_C105, $result['planned_next_summary']['planned_next_review']);
        $this->assertSame(2, $result['next_readiness_decision']['candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_handoff_finalization_review_count']);
    }

    public function test_c104_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $first = $this->execute(['options' => ['created_at' => '2026-06-30T00:00:00+00:00']]);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c104-handoff-readiness-pass-second.json';
        $this->tmpFiles[] = $secondOutput;
        $second = $this->execute([
            'output' => $secondOutput,
            'options' => ['created_at' => '2026-06-30T00:00:00+00:00'],
        ]);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
    }

    public function test_c104_records_required_sections_and_safety_flags(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();

        foreach ([
            'source_artifact_locks',
            'c103_lock_validation_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'c104_handoff_readiness_decision',
            'next_readiness_decision',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_decision',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_manifest',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_candidate_scorecard',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_context_summary',
            'runtime_readiness_inspection_summary',
            'feature_flag_operator_approval_kill_switch_validation_summary',
            'c103_completion_boundary_carry_forward_validation_summary',
            'handoff_readiness_governance_summary',
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
            $this->assertFalse($result['c104_handoff_readiness_decision'][$flag], $flag);
        }
    }

    public function test_c104_does_not_create_weekly_swing_live_output_or_official_recommendation(): void
    {
        $result = $this->runService();

        $this->assertFalse($result['weekly_swing_watchlist_official_output_generated']);
        $this->assertFalse($result['weekly_swing_watchlist_official_output_published']);
        $this->assertFalse($result['weekly_swing_watchlist_live_recommendation_generated']);
        $this->assertSame([], $result['weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_manifest']['official_weekly_swing_stock_recommendations']);
    }

    public function test_c104_writes_artifact_only_handoff_readiness_manifest_not_used_for_plan_confirm_mutation(): void
    {
        $result = $this->runService();
        $manifest = $result['weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_manifest'];

        $this->assertTrue($manifest['manifest_created']);
        $this->assertSame('artifact_only_non_live_rehearsal_handoff_readiness_review', $manifest['manifest_context']);
        $this->assertSame('non_live_artifact_only_rehearsal_handoff_readiness', $manifest['execution_mode']);
        $this->assertTrue($manifest['handoff_readiness_artifact_only']);
        $this->assertTrue($manifest['handoff_ready']);
        $this->assertFalse($manifest['handoff_readiness_used_for_selection']);
        $this->assertFalse($manifest['handoff_readiness_used_for_retuning']);
        $this->assertFalse($manifest['handoff_readiness_used_for_ranking']);
        $this->assertFalse($manifest['handoff_readiness_used_for_plan_confirm_mutation']);
        $this->assertFalse($manifest['handoff_readiness_used_for_live_rollout']);
        $this->assertFalse($manifest['plan_confirm_mutation_allowed']);
    }

    public function test_c104_does_not_mutate_c60_through_c103_artifacts(): void
    {
        $before = [];
        foreach (glob('storage/app/watchlist/backtest/c*.json') as $path) {
            if (preg_match('/storage\/app\/watchlist\/backtest\/c(6[0-9]|7[0-9]|8[0-9]|9[0-9]|10[0-3])-/', str_replace('\\', '/', $path)) === 1) {
                $before[$path] = strtoupper(sha1((string) file_get_contents($path)));
            }
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
            'weekly_swing_watchlist_non_live_rehearsal_result_review_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_context_persisted_to_live_runtime',
            'operator_go_no_go_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_context_persisted_to_live_runtime',
            'go_decision_finalization_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_non_live_rehearsal_completion_boundary_context_persisted_to_live_runtime',
            'completion_boundary_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_context_persisted_to_live_runtime',
            'handoff_readiness_context_persisted_to_live_runtime',
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
        $service = new WatchlistBacktestC104WeeklySwingWatchlistNonLiveRehearsalHandoffReadinessReviewService();
        $fixture = $this->lockedC103Fixture();
        return $service->execute(
            (string) ($overrides['c103Artifact'] ?? $fixture['path']),
            (string) ($overrides['expectedC103Hash'] ?? $fixture['hash']),
            (string) ($overrides['expectedC103FileSha1'] ?? $fixture['sha1']),
            (string) ($overrides['output'] ?? $this->output),
            array_merge([
                'overwrite' => true,
                'operator_approved' => $overrides['operatorApproved'] ?? true,
                'approval_reference' => $overrides['approvalReference'] ?? 'C104_OPERATOR_APPROVED_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_READINESS_REVIEW_ONLY',
            ], (array) ($overrides['options'] ?? []))
        );
    }

    private function mutateC103AndExecute(callable $mutator, string $name): array
    {
        $fixture = $this->lockedC103Fixture();
        $payload = json_decode((string) file_get_contents($fixture['path']), true);
        $payload = $mutator(is_array($payload) ? $payload : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c104-source-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
        return $this->execute([
            'c103Artifact' => $path,
            'expectedC103Hash' => (string) ($payload['artifact_hash'] ?? ''),
            'expectedC103FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function lockedC103Fixture(): array
    {
        $path = 'storage/app/watchlist/backtest/c103-weekly-swing-watchlist-non-live-rehearsal-completion-boundary-review.json';
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

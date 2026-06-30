<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC113WeeklySwingWatchlistProductionReadinessReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC113WeeklySwingWatchlistProductionReadinessReviewTest extends TestCase
{
    private const C112_ARTIFACT = 'storage/app/watchlist/backtest/c112-weekly-swing-watchlist-production-phase-approval-review.json';
    private const C112_HASH = '5c6b4bb2cd7751e4b8b838e31f0a6aecdad67e04';
    private const C112_SHA1 = '9DAE4191A2243A660963BF5D9709B6E79F7E1998';
    private const OUTPUT = 'storage/app/watchlist/backtest/c113-weekly-swing-watchlist-production-readiness-review-test-output.json';
    private const PASS_STATUS = 'C113_WEEKLY_SWING_WATCHLIST_PRODUCTION_READINESS_REVIEW_PASSED_READY_FOR_CONTROLLED_RUNTIME_WIRING_READINESS_REVIEW_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C113_WEEKLY_SWING_WATCHLIST_PRODUCTION_READINESS_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const TEMP_NEGATIVE_STATUS = 'C113_WEEKLY_SWING_WATCHLIST_PRODUCTION_READINESS_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const NEXT_C114 = 'C114_WEEKLY_SWING_WATCHLIST_PRODUCTION_RUNTIME_WIRING_READINESS_REVIEW';

    private $output;
    private $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = self::OUTPUT;
        $this->tmpFiles = [$this->output];
        $this->cleanupC113TemporaryArtifacts();
    }

    protected function tearDown(): void
    {
        foreach ($this->tmpFiles as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        $this->cleanupC113TemporaryArtifacts();
        parent::tearDown();
    }

    public function test_c113_passes_with_valid_c112_artifact_lock_and_operator_approval(): void
    {
        $result = $this->runService();

        $this->assertSame('C113_WEEKLY_SWING_WATCHLIST_PRODUCTION_READINESS_REVIEW', $result['run_code']);
        $this->assertSame('PR-01 / C113_WEEKLY_SWING_WATCHLIST_PRODUCTION_READINESS_REVIEW', $result['phase_label']);
        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame(self::PASS_STATUS, $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_production_readiness_review_pass']);
        $this->assertTrue($result['ready_for_controlled_runtime_wiring_readiness_review']);
        $this->assertTrue($result['production_readiness_manifest_created']);
        $this->assertSame(self::NEXT_C114, $result['next_step_recommendation']);
        $this->assertFileExists($this->output);
    }

    public function test_c113_rejects_missing_operator_approval(): void
    {
        $result = $this->runService(['operatorApproved' => false]);

        $this->assertSame(self::APPROVAL_MISSING_STATUS, $result['status']);
        $this->assertSame(self::APPROVAL_MISSING_STATUS, $result['reason_code']);
    }

    public function test_c113_rejects_missing_approval_reference(): void
    {
        $result = $this->runService(['approvalReference' => '']);

        $this->assertSame(self::APPROVAL_MISSING_STATUS, $result['status']);
        $this->assertSame(self::APPROVAL_MISSING_STATUS, $result['reason_code']);
    }

    public function test_c113_rejects_missing_or_mismatched_c112_artifact_lock(): void
    {
        $missing = $this->runService([
            'c112Artifact' => 'storage/app/watchlist/backtest/c113-source-does-not-exist.json',
            'expectedC112Hash' => 'missing',
            'expectedC112FileSha1' => 'missing',
        ]);
        $hashMismatch = $this->runService(['expectedC112Hash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedC112FileSha1' => 'BADSHA1']);

        $this->assertSame('C113_WEEKLY_SWING_WATCHLIST_PRODUCTION_READINESS_REVIEW_REJECTED_C112_ARTIFACT_LOCK_MISMATCH', $missing['status']);
        $this->assertSame('C113_WEEKLY_SWING_WATCHLIST_PRODUCTION_READINESS_REVIEW_REJECTED_C112_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C113_WEEKLY_SWING_WATCHLIST_PRODUCTION_READINESS_REVIEW_REJECTED_C112_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
    }

    public function test_c113_rejects_c112_status_reason_or_next_recommendation_mismatch(): void
    {
        $status = $this->mutateC112AndExecute(function (array $c112): array {
            $c112['status'] = 'BROKEN_STATUS';
            return $c112;
        }, 'status-broken');
        $reason = $this->mutateC112AndExecute(function (array $c112): array {
            $c112['reason_code'] = 'BROKEN_REASON';
            return $c112;
        }, 'reason-broken');
        $next = $this->mutateC112AndExecute(function (array $c112): array {
            $c112['next_step_recommendation'] = 'BROKEN_NEXT';
            $c112['next_readiness_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c112['c112_readiness_decision']['next_recommendation'] = 'BROKEN_NEXT';
            return $c112;
        }, 'next-broken');

        $this->assertSame('C113_WEEKLY_SWING_WATCHLIST_PRODUCTION_READINESS_REVIEW_REJECTED_C112_STATUS_MISMATCH', $status['status']);
        $this->assertSame('C113_WEEKLY_SWING_WATCHLIST_PRODUCTION_READINESS_REVIEW_REJECTED_C112_REASON_CODE_MISMATCH', $reason['status']);
        $this->assertSame('C113_WEEKLY_SWING_WATCHLIST_PRODUCTION_READINESS_REVIEW_REJECTED_C112_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
    }

    /**
     * @dataProvider c112ProductionPhaseApprovalMismatchProvider
     */
    public function test_c113_rejects_c112_production_phase_approval_state_mismatch(string $field, $value): void
    {
        $result = $this->mutateC112AndExecute(function (array $c112) use ($field, $value): array {
            $c112[$field] = $value;
            return $c112;
        }, 'approval-'.$field);

        $this->assertSame('C113_WEEKLY_SWING_WATCHLIST_PRODUCTION_READINESS_REVIEW_REJECTED_C112_PRODUCTION_PHASE_APPROVAL_INVALID', $result['status'], $field);
    }

    public function c112ProductionPhaseApprovalMismatchProvider(): array
    {
        return [
            ['weekly_swing_watchlist_production_phase_approval_review_pass', false],
            ['production_phase_approval_review_pass', false],
            ['production_phase_approved_for_readiness_review', false],
            ['production_readiness_review_allowed', false],
            ['primary_candidate_production_phase_approved_for_readiness_review', false],
            ['primary_candidate_production_phase_approval_granted', false],
            ['backup_candidate_production_phase_approved_for_readiness_review', false],
            ['backup_candidate_production_phase_approval_granted', false],
            ['comparator_candidate_production_phase_approved_for_readiness_review', true],
            ['comparator_candidate_production_phase_approval_granted', true],
        ];
    }

    /**
     * @dataProvider c111C112BoundaryMismatchProvider
     */
    public function test_c113_rejects_c111_c112_boundary_violation(string $field, $value): void
    {
        $result = $this->mutateC112AndExecute(function (array $c112) use ($field, $value): array {
            $c112[$field] = $value;
            return $c112;
        }, 'boundary-'.$field);

        $this->assertSame('C113_WEEKLY_SWING_WATCHLIST_PRODUCTION_READINESS_REVIEW_REJECTED_C111_C112_BOUNDARY_INVALID', $result['status'], $field);
    }

    public function c111C112BoundaryMismatchProvider(): array
    {
        return [
            ['c111_final_closure_valid', false],
            ['c111_non_live_audit_archive_terminal', false],
            ['c112_not_audit_archive_continuation', false],
            ['c112_does_not_reopen_c111_final_closure', false],
            ['c112_is_audit_archive_continuation', true],
            ['c112_reopens_c111_final_closure', true],
            ['c112_extends_non_live_audit_archive_review', true],
            ['c111_handoff_audit_archive_final_closed', false],
            ['c111_audit_archive_final_closed', false],
            ['c111_final_closure_manifest_created', false],
        ];
    }

    /**
     * @dataProvider candidateScopeMismatchProvider
     */
    public function test_c113_rejects_candidate_scope_change_or_a01_promotion(string $field, $value): void
    {
        $result = $this->mutateC112AndExecute(function (array $c112) use ($field, $value): array {
            $c112[$field] = $value;
            return $c112;
        }, 'candidate-'.$field);

        $this->assertSame('C113_WEEKLY_SWING_WATCHLIST_PRODUCTION_READINESS_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $result['status'], $field);
    }

    public function candidateScopeMismatchProvider(): array
    {
        return [
            ['primary_candidate_code', 'BROKEN_PRIMARY'],
            ['backup_candidate_code', 'BROKEN_BACKUP'],
            ['comparator_candidate_code', 'BROKEN_COMPARATOR'],
            ['a01_remains_comparator_only', false],
            ['a01_promoted', true],
            ['candidate_promotion_executed', true],
            ['candidate_rerank_executed', true],
            ['strategy_retune_executed', true],
            ['scoring_mutation_executed', true],
            ['catalog_selection_changed', true],
            ['runtime_selection_changed', true],
            ['weekly_swing_live_recommendation_selection_executed', true],
        ];
    }

    /**
     * @dataProvider safetyFlagProvider
     */
    public function test_c113_rejects_any_live_or_mutating_safety_flag_true_in_c112(string $field): void
    {
        $result = $this->mutateC112AndExecute(function (array $c112) use ($field): array {
            $c112[$field] = true;
            return $c112;
        }, 'safety-'.$field);

        $this->assertSame('C113_WEEKLY_SWING_WATCHLIST_PRODUCTION_READINESS_REVIEW_REJECTED_LIVE_OR_PRODUCTION_MUTATION_ATTEMPT', $result['status'], $field);
        $this->assertSame($field, $result['c112_live_or_mutating_safety_flag_failure']);
    }

    public function safetyFlagProvider(): array
    {
        return array_map(static function (string $flag): array {
            return [$flag];
        }, $this->requiredSafetyFlags());
    }

    public function test_c113_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c113-no-temporary-artifact-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame(self::TEMP_NEGATIVE_STATUS, $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c113_records_source_locks_decisions_manifest_checklist_and_no_live_output(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();
        $manifest = $result['weekly_swing_watchlist_production_readiness_review_manifest'];
        $checklist = $result['weekly_swing_watchlist_production_readiness_checklist'];

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $this->assertSame(self::C112_HASH, $result['expected_c112_hash']);
        $this->assertSame(self::C112_HASH, $result['actual_c112_hash']);
        $this->assertTrue($result['c112_hash_match']);
        $this->assertSame(self::C112_SHA1, $result['expected_c112_file_sha1']);
        $this->assertSame(self::C112_SHA1, $result['actual_c112_file_sha1']);
        $this->assertTrue($result['c112_file_sha1_match']);
        $this->assertSame(self::NEXT_C114, $result['next_readiness_decision']['next_recommendation']);

        foreach ([
            'source_artifact_locks',
            'c112_lock_validation_summary',
            'c111_c112_boundary_carry_forward_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'c113_readiness_decision',
            'next_readiness_decision',
            'weekly_swing_watchlist_production_readiness_decision',
            'weekly_swing_watchlist_production_readiness_review_manifest',
            'weekly_swing_watchlist_production_readiness_checklist',
            'c113_candidate_production_readiness_scorecard',
            'production_readiness_context_summary',
            'runtime_config_review_summary',
            'production_mutation_safety_summary',
            'documentation_hygiene_guard_summary',
            'failure_attribution_summary',
            'progress_summary',
            'planned_next_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }

        $this->assertTrue($manifest['production_readiness_review_artifact_only']);
        $this->assertTrue($manifest['ready_for_controlled_runtime_wiring_readiness_review']);
        $this->assertFalse($manifest['production_runtime_wiring_allowed']);
        $this->assertFalse($manifest['production_deployment_allowed']);
        $this->assertFalse($manifest['weekly_swing_live_output_enabled']);
        $this->assertFalse($manifest['production_readiness_review_used_for_plan_confirm_mutation']);
        $this->assertTrue($checklist['data_dependency_reviewed']);
        $this->assertTrue($checklist['runtime_config_reviewed']);
        $this->assertTrue($checklist['artifact_only']);
        $this->assertFalse($checklist['weekly_swing_stock_recommendation_generated']);
        $this->assertFalse($result['weekly_swing_watchlist_live_output_enabled']);
        $this->assertFalse($result['weekly_swing_watchlist_live_recommendation_generated']);
    }

    public function test_c113_keeps_all_safety_flags_false(): void
    {
        $result = $this->runService();

        foreach ($this->requiredSafetyFlags() as $flag) {
            $this->assertFalse($result[$flag], $flag);
            $this->assertFalse($result['production_mutation_safety_summary'][$flag], $flag);
        }
        $this->assertFalse($result['c113_readiness_decision']['production_ready']);
        $this->assertFalse($result['c113_readiness_decision']['production_deployment_allowed']);
        $this->assertFalse($result['production_readiness_context_summary']['context_persisted_to_live_runtime']);
    }

    public function test_c113_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $first = $this->runService(['createdAt' => '2026-06-30T00:00:00+00:00']);
        $firstHash = $first['artifact_hash'];
        if (is_file($this->output)) {
            unlink($this->output);
        }
        $second = $this->runService(['createdAt' => '2026-06-30T00:00:00+00:00']);

        $this->assertSame($firstHash, $second['artifact_hash']);
    }

    public function test_c113_does_not_mutate_c111_or_c112_artifacts(): void
    {
        $before = $this->artifactSha1s([
            'storage/app/watchlist/backtest/c111-weekly-swing-watchlist-non-live-rehearsal-handoff-audit-archive-final-closure-review.json',
            self::C112_ARTIFACT,
        ]);

        $this->runService();

        $this->assertSame($before, $this->artifactSha1s(array_keys($before)));
    }

    public function test_c113_keeps_e02_primary_b01_backup_and_a01_comparator_only(): void
    {
        $result = $this->runService();
        $scorecard = $result['c113_candidate_production_readiness_scorecard'];

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $result['primary_candidate_code']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $result['backup_candidate_code']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $result['comparator_candidate_code']);
        $this->assertTrue($result['a01_remains_comparator_only']);
        $this->assertTrue($scorecard[0]['primary_candidate_ready_for_controlled_runtime_wiring_readiness_review']);
        $this->assertTrue($scorecard[1]['backup_candidate_ready_for_controlled_runtime_wiring_readiness_review']);
        $this->assertFalse($scorecard[2]['ready_for_controlled_runtime_wiring_readiness_review']);
        $this->assertTrue($scorecard[2]['a01_remains_comparator_only']);
    }

    public function test_c113_documentation_hygiene_guard_preserves_scoped_c111_sha_keys(): void
    {
        $result = $this->runService();
        $guard = $result['documentation_hygiene_guard_summary'];

        $this->assertTrue($guard['scoped_keys_are_not_duplicate_by_name_only']);
        $this->assertTrue($guard['c112_expected_c111_file_sha1_scoped_key_preserved']);
        $this->assertTrue($guard['expected_c111_file_sha1_scoped_key_preserved']);
        $this->assertTrue($guard['c111_c112_artifacts_not_modified']);
    }

    private function runService(array $options = []): array
    {
        $service = new WatchlistBacktestC113WeeklySwingWatchlistProductionReadinessReviewService();
        return $service->execute(
            (string) ($options['c112Artifact'] ?? self::C112_ARTIFACT),
            (string) ($options['expectedC112Hash'] ?? self::C112_HASH),
            (string) ($options['expectedC112FileSha1'] ?? self::C112_SHA1),
            $this->output,
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C113_OPERATOR_APPROVED_PRODUCTION_READINESS_REVIEW_ONLY'),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-06-30T00:00:00+00:00'),
            ]
        );
    }

    private function mutateC112AndExecute(callable $mutator, string $name): array
    {
        $c112 = json_decode((string) file_get_contents(self::C112_ARTIFACT), true);
        $c112 = $mutator($c112);
        $path = 'storage/app/watchlist/backtest/c113-mutated-c112-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($c112, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
        $sha1 = strtoupper(sha1((string) file_get_contents($path)));

        return $this->runService([
            'c112Artifact' => $path,
            'expectedC112Hash' => self::C112_HASH,
            'expectedC112FileSha1' => $sha1,
        ]);
    }

    private function readOutput(): array
    {
        return json_decode((string) file_get_contents($this->output), true);
    }

    private function artifactSha1s(array $paths): array
    {
        $hashes = [];
        foreach ($paths as $path) {
            $hashes[$path] = strtoupper(sha1((string) file_get_contents($path)));
        }
        return $hashes;
    }

    private function cleanupC113TemporaryArtifacts(): void
    {
        foreach ((array) glob('storage/app/watchlist/backtest/c113-*test.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/c113-mutated-c112-*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    private function requiredSafetyFlags(): array
    {
        return [
            'production_ready',
            'production_catalog_runtime_wired',
            'production_runtime_wiring_allowed',
            'production_runtime_wiring_executed',
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
            'weekly_swing_watchlist_non_live_rehearsal_handoff_finalization_context_persisted_to_live_runtime',
            'handoff_finalization_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_completion_boundary_context_persisted_to_live_runtime',
            'handoff_completion_boundary_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_context_persisted_to_live_runtime',
            'handoff_closure_seal_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_context_persisted_to_live_runtime',
            'handoff_audit_archive_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_context_persisted_to_live_runtime',
            'handoff_audit_archive_completion_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_seal_context_persisted_to_live_runtime',
            'handoff_audit_archive_completion_seal_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_final_closure_context_persisted_to_live_runtime',
            'handoff_audit_archive_final_closure_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_production_phase_approval_context_persisted_to_live_runtime',
            'production_phase_approval_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_production_readiness_context_persisted_to_live_runtime',
            'production_readiness_context_persisted_to_live_runtime',
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
}

<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC130WeeklySwingWatchlistProductionLiveRuntimeActivationReadinessReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC130WeeklySwingWatchlistProductionLiveRuntimeActivationReadinessReviewTest extends TestCase
{
    private const C129_ARTIFACT = 'storage/app/watchlist/backtest/c129-weekly-swing-watchlist-controlled-runtime-wiring-handoff-audit-archive-final-closure-review.json';
    private const C129_HASH = '39b7a16acf266f9b8853d275ff8dff3ef582f716';
    private const C129_SHA1 = 'BA9AE12F4111AED9DC973BF1EA1BAE9181844E9E';
    private const PASS_STATUS = 'C130_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_READINESS_REVIEW_PASSED_READY_FOR_ACTIVATION_APPROVAL_REVIEW_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C130_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_READINESS_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const READINESS_NOT_CONFIRMED_STATUS = 'C130_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_READINESS_REVIEW_REJECTED_ACTIVATION_READINESS_NOT_CONFIRMED';
    private const TEMP_NEGATIVE_STATUS = 'C130_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_READINESS_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const NEXT_C131 = 'C131_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_APPROVAL_REVIEW';

    private string $output;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c130-production-live-runtime-activation-readiness-pass.json';
        $this->cleanupC130TemporaryArtifacts();
        @unlink($this->output);
    }

    protected function tearDown(): void
    {
        @unlink($this->output);
        foreach ($this->tmpFiles as $path) {
            @unlink($path);
        }
        $this->cleanupC130TemporaryArtifacts();
        parent::tearDown();
    }

    public function test_c130_passes_with_valid_c129_final_closure_lock_operator_approval_and_readiness_confirmation(): void
    {
        $result = $this->runService();

        $this->assertSame('C130_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_READINESS_REVIEW', $result['run_code']);
        $this->assertSame('PR-18 / C130_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_READINESS_REVIEW', $result['phase_label']);
        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame(self::PASS_STATUS, $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_production_live_runtime_activation_readiness_review_pass']);
        $this->assertTrue($result['ready_for_production_live_runtime_activation_approval_review']);
        $this->assertTrue($result['production_live_runtime_activation_readiness_manifest_created']);
        $this->assertTrue($result['c129_final_closure_valid']);
        $this->assertTrue($result['c129_audit_archive_terminal']);
        $this->assertTrue($result['c130_is_new_production_live_activation_phase']);
        $this->assertTrue($result['c130_not_handoff_audit_archive_continuation']);
        $this->assertSame(self::NEXT_C131, $result['next_step_recommendation']);
        $this->assertFileExists($this->output);
    }

    public function test_c130_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->runService(['operatorApproved' => false]);
        $missingReference = $this->runService(['approvalReference' => '']);

        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingOperator['status']);
        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingReference['status']);
    }

    public function test_c130_rejects_missing_activation_readiness_confirmation(): void
    {
        $result = $this->runService(['readinessConfirmed' => false]);

        $this->assertSame(self::READINESS_NOT_CONFIRMED_STATUS, $result['status']);
        $this->assertFalse($result['ready_for_production_live_runtime_activation_approval_review']);
    }

    public function test_c130_rejects_missing_or_mismatched_c129_artifact_lock(): void
    {
        $missing = $this->runService([
            'c129Artifact' => 'storage/app/watchlist/backtest/missing-c129-for-c130.json',
            'expectedC129Hash' => 'missing',
            'expectedC129FileSha1' => 'missing',
        ]);
        $hashMismatch = $this->runService(['expectedC129Hash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedC129FileSha1' => 'BADSHA1']);

        $this->assertSame('C130_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_READINESS_REVIEW_REJECTED_C129_ARTIFACT_LOCK_MISMATCH', $missing['status']);
        $this->assertSame('C130_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_READINESS_REVIEW_REJECTED_C129_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C130_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_READINESS_REVIEW_REJECTED_C129_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
    }

    public function test_c130_rejects_c129_status_phase_or_terminal_recommendation_mismatch(): void
    {
        $status = $this->mutateC129AndExecute(function (array $c129): array {
            $c129['status'] = 'BROKEN_STATUS';
            return $c129;
        }, 'status-broken');
        $phase = $this->mutateC129AndExecute(function (array $c129): array {
            $c129['phase_label'] = 'BROKEN_PHASE';
            return $c129;
        }, 'phase-broken');
        $next = $this->mutateC129AndExecute(function (array $c129): array {
            $c129['next_step_recommendation'] = 'BROKEN_NEXT';
            $c129['next_readiness_decision']['next_recommendation'] = 'BROKEN_NEXT';
            return $c129;
        }, 'next-broken');

        $this->assertSame('C130_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_READINESS_REVIEW_REJECTED_C129_STATUS_MISMATCH', $status['status']);
        $this->assertSame('C130_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_READINESS_REVIEW_REJECTED_C129_PHASE_LABEL_MISMATCH', $phase['status']);
        $this->assertSame('C130_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_READINESS_REVIEW_REJECTED_C129_TERMINAL_RECOMMENDATION_MISMATCH', $next['status']);
    }

    public function test_c130_rejects_c129_convert_from_json_duplicate_top_level_keys(): void
    {
        $raw = (string) file_get_contents(self::C129_ARTIFACT);
        $path = 'storage/app/watchlist/backtest/.tmp-c130-source-c129-duplicate-key.json';
        $this->tmpFiles[] = $path;
        $duplicateRaw = preg_replace('/\{/', "{\"Run_Code\":\"DUPLICATE_CASE_KEY\",", $raw, 1);
        file_put_contents($path, $duplicateRaw);

        $result = $this->runService([
            'c129Artifact' => $path,
            'expectedC129Hash' => self::C129_HASH,
            'expectedC129FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);

        $this->assertSame('C130_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_READINESS_REVIEW_REJECTED_C129_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION', $result['status']);
        $this->assertFalse($result['c129_convert_from_json_pass']);
    }

    /**
     * @dataProvider c129FinalClosureMismatchProvider
     */
    public function test_c130_rejects_c129_final_closure_state_mismatch(string $field, $value): void
    {
        $result = $this->mutateC129AndExecute(function (array $c129) use ($field, $value): array {
            $c129[$field] = $value;
            return $c129;
        }, 'closure-'.$field);

        $this->assertSame('C130_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_READINESS_REVIEW_REJECTED_C129_FINAL_CLOSURE_INCOMPLETE', $result['status'], $field);
    }

    public function c129FinalClosureMismatchProvider(): array
    {
        return [
            ['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_final_closure_review_pass', false],
            ['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_final_closed', false],
            ['handoff_audit_archive_final_closed', false],
            ['audit_archive_final_closed', false],
            ['final_closure_manifest_created', false],
            ['handoff_audit_archive_final_closure_confirmed', false],
            ['handoff_audit_archive_final_closure_go_decision', 'NO_GO'],
            ['primary_candidate_handoff_audit_archive_final_closed', false],
            ['backup_candidate_handoff_audit_archive_final_closed', false],
            ['comparator_candidate_handoff_audit_archive_final_closed', true],
            ['production_ready', true],
            ['weekly_swing_watchlist_live_output_enabled', true],
        ];
    }

    public function test_c130_rejects_candidate_scope_change_or_a01_promotion(): void
    {
        $candidate = $this->mutateC129AndExecute(function (array $c129): array {
            $c129['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $c129;
        }, 'candidate-primary');
        $a01 = $this->mutateC129AndExecute(function (array $c129): array {
            $c129['a01_promoted'] = true;
            return $c129;
        }, 'candidate-a01');

        $this->assertSame('C130_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_READINESS_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $candidate['status']);
        $this->assertSame('C130_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_READINESS_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $a01['status']);
    }

    public function test_c130_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c130-no-temporary-artifact-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame(self::TEMP_NEGATIVE_STATUS, $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c130_records_source_locks_manifest_checklist_and_no_live_output(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();
        $manifest = $result['weekly_swing_watchlist_production_live_runtime_activation_readiness_manifest'];
        $checklist = $result['weekly_swing_watchlist_production_live_runtime_activation_readiness_checklist'];

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $this->assertSame(self::C129_HASH, $result['expected_c129_hash']);
        $this->assertSame(self::C129_HASH, $result['actual_c129_hash']);
        $this->assertTrue($result['c129_hash_match']);
        $this->assertSame(self::C129_SHA1, $result['expected_c129_file_sha1']);
        $this->assertSame(self::C129_SHA1, $result['actual_c129_file_sha1']);
        $this->assertTrue($result['c129_file_sha1_match']);
        $this->assertTrue($result['c129_convert_from_json_pass']);
        $this->assertSame(self::NEXT_C131, $result['next_readiness_decision']['next_recommendation']);

        foreach ([
            'source_artifact_locks',
            'c129_lock_validation_summary',
            'c129_final_closure_carry_forward_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'activation_readiness_confirmation_summary',
            'temporary_negative_artifact_guard_summary',
            'feature_flag_default_off_summary',
            'c130_readiness_decision',
            'next_readiness_decision',
            'weekly_swing_watchlist_production_live_runtime_activation_readiness_manifest',
            'weekly_swing_watchlist_production_live_runtime_activation_readiness_checklist',
            'c130_candidate_activation_readiness_scorecard',
            'production_live_runtime_activation_readiness_context_summary',
            'production_mutation_safety_summary',
            'documentation_hygiene_guard_summary',
            'progress_summary',
            'planned_next_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }

        $this->assertTrue($manifest['activation_readiness_artifact_only']);
        $this->assertTrue($manifest['ready_for_production_live_runtime_activation_approval_review']);
        $this->assertTrue($manifest['production_live_runtime_activation_approval_review_required_next']);
        $this->assertFalse($manifest['runtime_bridge_active']);
        $this->assertFalse($manifest['weekly_swing_live_output_enabled']);
        $this->assertFalse($manifest['activation_readiness_used_for_plan_confirm_mutation']);
        $this->assertTrue($checklist['live_runtime_activation_approval_required_next']);
        $this->assertTrue($checklist['artifact_only']);
        $this->assertFalse($checklist['weekly_swing_stock_recommendation_generated']);
    }

    public function test_c130_keeps_e02_primary_b01_backup_a01_comparator_and_all_safety_flags_false(): void
    {
        $result = $this->runService();

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $result['primary_candidate_code']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $result['backup_candidate_code']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $result['comparator_candidate_code']);
        $this->assertTrue($result['primary_candidate_ready_for_production_live_runtime_activation_approval_review']);
        $this->assertTrue($result['backup_candidate_ready_for_production_live_runtime_activation_approval_review']);
        $this->assertFalse($result['comparator_candidate_ready_for_production_live_runtime_activation_approval_review']);
        $this->assertTrue($result['a01_remains_comparator_only']);

        foreach ($this->requiredSafetyFlags() as $flag) {
            $this->assertFalse($result[$flag], $flag);
            $this->assertFalse($result['production_mutation_safety_summary'][$flag], $flag);
        }
    }

    public function test_c130_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $first = $this->runService(['createdAt' => '2026-06-30T00:00:00+00:00']);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c130-production-live-runtime-activation-readiness-pass-second.json';
        $this->tmpFiles[] = $secondOutput;
        $second = $this->runService([
            'output' => $secondOutput,
            'createdAt' => '2026-06-30T00:00:00+00:00',
        ]);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
    }

    public function test_c130_does_not_mutate_c129_artifact(): void
    {
        $before = strtoupper(sha1((string) file_get_contents(self::C129_ARTIFACT)));

        $this->runService();

        $this->assertSame($before, strtoupper(sha1((string) file_get_contents(self::C129_ARTIFACT))));
    }

    private function runService(array $options = []): array
    {
        $service = new WatchlistBacktestC130WeeklySwingWatchlistProductionLiveRuntimeActivationReadinessReviewService();

        return $service->execute(
            (string) ($options['c129Artifact'] ?? self::C129_ARTIFACT),
            (string) ($options['expectedC129Hash'] ?? self::C129_HASH),
            (string) ($options['expectedC129FileSha1'] ?? self::C129_SHA1),
            (string) ($options['output'] ?? $this->output),
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C130_OPERATOR_APPROVED_PRODUCTION_LIVE_RUNTIME_ACTIVATION_READINESS_REVIEW_ONLY'),
                'production_live_runtime_activation_readiness_confirmed' => (bool) ($options['readinessConfirmed'] ?? true),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-06-30T00:00:00+00:00'),
            ]
        );
    }

    private function mutateC129AndExecute(callable $mutator, string $name): array
    {
        $c129 = json_decode((string) file_get_contents(self::C129_ARTIFACT), true);
        $c129 = $mutator(is_array($c129) ? $c129 : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c130-source-c129-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($c129, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'c129Artifact' => $path,
            'expectedC129Hash' => (string) ($c129['artifact_hash'] ?? ''),
            'expectedC129FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function readOutput(): array
    {
        $decoded = json_decode((string) file_get_contents($this->output), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function cleanupC130TemporaryArtifacts(): void
    {
        foreach ((array) glob('storage/app/watchlist/backtest/c130-*test.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/.tmp-c130*.json') as $file) {
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
            'production_deployment_allowed',
            'production_deployment_executed',
            'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active',
            'controlled_rollout_active',
            'pilot_runtime_active',
            'shadow_runtime_active',
            'runtime_bridge_active',
            'plan_confirm_mutation_allowed',
            'plan_confirm_mutated',
            'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed',
            'live_plan_confirm_rollout_executed',
            'weekly_swing_watchlist_runtime_active',
            'weekly_swing_watchlist_plan_confirm_mutation_allowed',
            'weekly_swing_watchlist_live_output_enabled',
            'weekly_swing_watchlist_official_output_generated',
            'weekly_swing_watchlist_official_output_published',
            'weekly_swing_watchlist_live_recommendation_generated',
            'weekly_swing_watchlist_production_live_runtime_activation_readiness_context_persisted_to_live_runtime',
            'production_live_runtime_activation_readiness_context_persisted_to_live_runtime',
        ];
    }
}

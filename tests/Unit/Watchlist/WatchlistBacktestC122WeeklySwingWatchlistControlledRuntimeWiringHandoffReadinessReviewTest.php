<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC122WeeklySwingWatchlistControlledRuntimeWiringHandoffReadinessReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC122WeeklySwingWatchlistControlledRuntimeWiringHandoffReadinessReviewTest extends TestCase
{
    private const C121_ARTIFACT = 'storage/app/watchlist/backtest/c121-weekly-swing-watchlist-controlled-runtime-wiring-completion-boundary-review.json';
    private const C121_HASH = '54c19fc3235d62f07b3d57b3faac96f09afeb616';
    private const C121_SHA1 = 'AF4AF4C557F57D1435AC226311E8F49E509C4BA8';
    private const OUTPUT = 'storage/app/watchlist/backtest/c122-weekly-swing-watchlist-controlled-runtime-wiring-handoff-readiness-review-test-output.json';
    private const PASS_STATUS = 'C122_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_READINESS_REVIEW_PASSED_HANDOFF_READY_PRIMARY_AND_BACKUP';
    private const NEXT_C123 = 'C123_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_FINALIZATION_REVIEW';

    private $output;
    private $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = self::OUTPUT;
        $this->tmpFiles = [$this->output];
        $this->cleanupC122TemporaryArtifacts();
    }

    protected function tearDown(): void
    {
        foreach ($this->tmpFiles as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        $this->cleanupC122TemporaryArtifacts();
        parent::tearDown();
    }

    public function test_c122_passes_with_valid_c121_lock_operator_approval_and_handoff_readiness_confirmation(): void
    {
        $result = $this->runService();

        $this->assertSame('C122_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_READINESS_REVIEW', $result['run_code']);
        $this->assertSame('PR-10 / C122_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_READINESS_REVIEW', $result['phase_label']);
        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame(self::PASS_STATUS, $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_runtime_wiring_handoff_readiness_review_pass']);
        $this->assertTrue($result['handoff_ready']);
        $this->assertTrue($result['handoff_readiness_confirmed']);
        $this->assertSame('HANDOFF_READY_GO', $result['handoff_readiness_go_decision']);
        $this->assertTrue($result['ready_for_controlled_runtime_wiring_handoff_finalization_review']);
        $this->assertSame(self::NEXT_C123, $result['next_step_recommendation']);
        $this->assertFileExists($this->output);
    }

    public function test_c122_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->runService(['operatorApproved' => false]);
        $missingReference = $this->runService(['approvalReference' => '']);

        $this->assertSame('C122_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_READINESS_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingOperator['status']);
        $this->assertSame('C122_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_READINESS_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingReference['status']);
    }

    public function test_c122_rejects_unconfirmed_handoff_readiness(): void
    {
        $result = $this->runService(['handoffReadinessConfirmed' => false]);

        $this->assertSame('C122_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_READINESS_REVIEW_REJECTED_HANDOFF_READINESS_NOT_CONFIRMED', $result['status']);
        $this->assertFalse($result['handoff_ready']);
        $this->assertFalse($result['handoff_readiness_confirmed']);
    }

    public function test_c122_rejects_missing_or_mismatched_c121_artifact_lock(): void
    {
        $missing = $this->runService([
            'c121Artifact' => 'storage/app/watchlist/backtest/c122-source-does-not-exist.json',
            'expectedC121Hash' => 'missing',
            'expectedC121FileSha1' => 'missing',
        ]);
        $hashMismatch = $this->runService(['expectedC121Hash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedC121FileSha1' => 'BADSHA1']);

        $this->assertSame('C122_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_READINESS_REVIEW_REJECTED_C121_ARTIFACT_LOCK_MISMATCH', $missing['status']);
        $this->assertSame('C122_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_READINESS_REVIEW_REJECTED_C121_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C122_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_READINESS_REVIEW_REJECTED_C121_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
    }

    public function test_c122_rejects_c121_status_reason_next_or_phase_mismatch(): void
    {
        $status = $this->mutateC121AndExecute(function (array $c121): array {
            $c121['status'] = 'BROKEN_STATUS';
            return $c121;
        }, 'status-broken');
        $reason = $this->mutateC121AndExecute(function (array $c121): array {
            $c121['reason_code'] = 'BROKEN_REASON';
            return $c121;
        }, 'reason-broken');
        $next = $this->mutateC121AndExecute(function (array $c121): array {
            $c121['next_step_recommendation'] = 'BROKEN_NEXT';
            $c121['next_handoff_readiness_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c121['c121_completion_boundary_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c121['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $c121;
        }, 'next-broken');
        $phase = $this->mutateC121AndExecute(function (array $c121): array {
            $c121['phase_label'] = 'BROKEN_PHASE';
            return $c121;
        }, 'phase-broken');

        $this->assertSame('C122_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_READINESS_REVIEW_REJECTED_C121_STATUS_MISMATCH', $status['status']);
        $this->assertSame('C122_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_READINESS_REVIEW_REJECTED_C121_REASON_CODE_MISMATCH', $reason['status']);
        $this->assertSame('C122_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_READINESS_REVIEW_REJECTED_C121_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
        $this->assertSame('C122_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_READINESS_REVIEW_REJECTED_C121_PHASE_LABEL_MISMATCH', $phase['status']);
    }

    /**
     * @dataProvider c121CompletionBoundaryMismatchProvider
     */
    public function test_c122_rejects_c121_completion_boundary_state_mismatch(string $field, $value): void
    {
        $result = $this->mutateC121AndExecute(function (array $c121) use ($field, $value): array {
            $this->setPath($c121, explode('.', $field), $value);
            return $c121;
        }, 'completion-boundary-'.str_replace('.', '-', $field));

        $this->assertSame('C122_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_READINESS_REVIEW_REJECTED_C121_COMPLETION_BOUNDARY_INVALID', $result['status'], $field);
    }

    public function c121CompletionBoundaryMismatchProvider(): array
    {
        return [
            ['weekly_swing_watchlist_controlled_runtime_wiring_completion_boundary_review_pass', false],
            ['controlled_runtime_wiring_completion_boundary_review_pass', false],
            ['completion_boundary_cleared', false],
            ['completion_boundary_confirmed', false],
            ['boundary_go_decision', 'NO_GO'],
            ['weekly_swing_watchlist_ready_for_controlled_runtime_wiring_handoff_readiness_review', false],
            ['ready_for_controlled_runtime_wiring_handoff_readiness_review', false],
            ['controlled_runtime_wiring_completion_boundary_manifest_created', false],
            ['controlled_runtime_wiring_handoff_readiness_review_allowed_next', false],
            ['c121_completion_boundary_decision.review_pass', false],
            ['c121_completion_boundary_decision.completion_boundary_cleared', false],
            ['c121_completion_boundary_decision.completion_boundary_confirmed', false],
            ['c121_completion_boundary_decision.boundary_go_decision', 'NO_GO'],
            ['weekly_swing_watchlist_controlled_runtime_wiring_completion_boundary_manifest.manifest_created', false],
            ['weekly_swing_watchlist_controlled_runtime_wiring_completion_boundary_manifest.boundary_go_decision', 'NO_GO'],
            ['weekly_swing_watchlist_controlled_runtime_wiring_completion_boundary_manifest.completion_boundary_artifact_only', false],
            ['weekly_swing_watchlist_controlled_runtime_wiring_completion_boundary_manifest.ready_for_controlled_runtime_wiring_handoff_readiness_review', false],
            ['weekly_swing_watchlist_controlled_runtime_wiring_completion_boundary_checklist.completion_boundary_reviewed', false],
            ['c120_hash_match', false],
            ['c120_file_sha1_match', false],
            ['c120_convert_from_json_pass', false],
            ['c120_go_decision_finalization_valid', false],
            ['c119_hash_match', false],
            ['c119_file_sha1_match', false],
            ['c119_convert_from_json_pass', false],
            ['c119_operator_go_no_go_valid', false],
        ];
    }

    public function test_c122_rejects_c121_convert_from_json_duplicate_case_insensitive_top_level_key(): void
    {
        $raw = (string) file_get_contents(self::C121_ARTIFACT);
        $raw = preg_replace('/^\\{\\s*/', "{\n  \"Status\": \"DUPLICATE_CASE_INSENSITIVE_STATUS\",\n", $raw, 1);
        $path = 'storage/app/watchlist/backtest/c122-mutated-c121-duplicate-key.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, $raw);
        $sha1 = strtoupper(sha1((string) file_get_contents($path)));

        $result = $this->runService([
            'c121Artifact' => $path,
            'expectedC121Hash' => self::C121_HASH,
            'expectedC121FileSha1' => $sha1,
        ]);

        $this->assertSame('C122_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_READINESS_REVIEW_REJECTED_C121_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION', $result['status']);
        $this->assertFalse($result['c121_convert_from_json_pass']);
        $this->assertContains('status', array_map('strtolower', $result['c121_convert_from_json_duplicate_keys']));
    }

    /**
     * @dataProvider boundaryMismatchProvider
     */
    public function test_c122_rejects_boundary_violation(string $field, $value): void
    {
        $result = $this->mutateC121AndExecute(function (array $c121) use ($field, $value): array {
            $this->setPath($c121, explode('.', $field), $value);
            return $c121;
        }, 'boundary-'.str_replace('.', '-', $field));

        $this->assertSame('C122_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_READINESS_REVIEW_REJECTED_C111_C112_C113_C114_C115_C116_C117_C118_C119_C120_C121_BOUNDARY_INVALID', $result['status'], $field);
    }

    public function boundaryMismatchProvider(): array
    {
        return [
            ['c111_final_closure_valid', false],
            ['c111_non_live_audit_archive_terminal', false],
            ['c112_not_audit_archive_continuation', false],
            ['c112_does_not_reopen_c111_final_closure', false],
            ['c113_production_readiness_valid', false],
            ['c120_go_decision_finalization_review_only', false],
            ['c111_c112_c113_c114_c115_c116_c117_c118_c119_c120_boundary_evidence_labels.C121_COMPLETION_BOUNDARY_REVIEW_ONLY', false],
            ['c111_c112_c113_c114_c115_c116_c117_c118_c119_c120_boundary_evidence_labels.C121_NOT_PRODUCTION_DEPLOYMENT', false],
            ['c111_c112_c113_c114_c115_c116_c117_c118_c119_c120_boundary_evidence_labels.C121_NOT_PLAN_CONFIRM_MUTATION', false],
            ['c111_c112_c113_c114_c115_c116_c117_c118_c119_c120_boundary_evidence_labels.C121_NOT_WEEKLY_SWING_LIVE_OUTPUT', false],
        ];
    }

    /**
     * @dataProvider candidateScopeMismatchProvider
     */
    public function test_c122_rejects_candidate_scope_change_or_a01_promotion(string $field, $value): void
    {
        $result = $this->mutateC121AndExecute(function (array $c121) use ($field, $value): array {
            $c121[$field] = $value;
            return $c121;
        }, 'candidate-'.$field);

        $this->assertSame('C122_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_READINESS_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $result['status'], $field);
    }

    public function candidateScopeMismatchProvider(): array
    {
        return [
            ['primary_candidate_code', 'BROKEN_PRIMARY'],
            ['backup_candidate_code', 'BROKEN_BACKUP'],
            ['comparator_candidate_code', 'BROKEN_COMPARATOR'],
            ['primary_candidate_ready_for_controlled_runtime_wiring_handoff_readiness_review', false],
            ['backup_candidate_ready_for_controlled_runtime_wiring_handoff_readiness_review', false],
            ['comparator_candidate_ready_for_controlled_runtime_wiring_handoff_readiness_review', true],
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
    public function test_c122_rejects_any_live_or_mutating_safety_flag_true_in_c121(string $field): void
    {
        $result = $this->mutateC121AndExecute(function (array $c121) use ($field): array {
            $c121[$field] = true;
            return $c121;
        }, 'safety-'.$field);

        $this->assertSame('C122_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_READINESS_REVIEW_REJECTED_LIVE_OR_PRODUCTION_MUTATION_ATTEMPT', $result['status'], $field);
        $this->assertSame($field, $result['c121_live_or_mutating_safety_flag_failure']);
    }

    public function safetyFlagProvider(): array
    {
        return array_map(static function (string $flag): array {
            return [$flag];
        }, $this->requiredSafetyFlags());
    }

    public function test_c122_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c122-no-temporary-artifact-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame('C122_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_READINESS_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS', $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c122_records_source_locks_decisions_manifest_checklist_and_no_live_output(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();
        $manifest = $result['weekly_swing_watchlist_controlled_runtime_wiring_handoff_readiness_manifest'];
        $checklist = $result['weekly_swing_watchlist_controlled_runtime_wiring_handoff_readiness_checklist'];

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $this->assertSame(self::C121_HASH, $result['expected_c121_hash']);
        $this->assertSame(self::C121_HASH, $result['actual_c121_hash']);
        $this->assertTrue($result['c121_hash_match']);
        $this->assertSame(self::C121_SHA1, $result['expected_c121_file_sha1']);
        $this->assertSame(self::C121_SHA1, $result['actual_c121_file_sha1']);
        $this->assertTrue($result['c121_file_sha1_match']);
        $this->assertTrue($result['c121_convert_from_json_pass']);
        $this->assertTrue($result['c121_lock_valid']);
        $this->assertTrue($result['c121_completion_boundary_valid']);
        $this->assertSame(self::NEXT_C123, $result['next_handoff_finalization_decision']['next_recommendation']);

        foreach ([
            'source_artifact_locks',
            'c121_lock_validation_summary',
            'c111_c112_c113_c114_c115_c116_c117_c118_c119_c120_c121_boundary_carry_forward_summary',
            'candidate_scope_freeze_summary',
            'c121_final_completion_boundary_evidence_summary',
            'operator_approval_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'c122_handoff_readiness_decision',
            'next_handoff_finalization_decision',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_readiness_manifest',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_readiness_checklist',
            'c122_candidate_handoff_readiness_scorecard',
            'controlled_runtime_wiring_handoff_readiness_context_summary',
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

        $this->assertTrue($manifest['handoff_readiness_artifact_only']);
        $this->assertTrue($manifest['handoff_ready']);
        $this->assertSame('HANDOFF_READY_GO', $manifest['handoff_readiness_go_decision']);
        $this->assertTrue($manifest['ready_for_controlled_runtime_wiring_handoff_finalization_review']);
        $this->assertFalse($manifest['runtime_wiring_execution_performed_against_live_runtime']);
        $this->assertFalse($manifest['runtime_wiring_enabled']);
        $this->assertFalse($manifest['runtime_bridge_enabled']);
        $this->assertFalse($manifest['production_runtime_wiring_allowed']);
        $this->assertFalse($manifest['weekly_swing_live_output_enabled']);
        $this->assertFalse($manifest['handoff_readiness_used_for_plan_confirm_mutation']);
        $this->assertTrue($checklist['handoff_readiness_reviewed']);
        $this->assertTrue($checklist['c121_source_lock_reviewed']);
        $this->assertTrue($checklist['artifact_only']);
        $this->assertTrue($checklist['production_runtime_wiring_not_enabled']);
        $this->assertFalse($checklist['weekly_swing_stock_recommendation_generated']);
    }

    public function test_c122_keeps_all_safety_flags_false(): void
    {
        $result = $this->runService();

        foreach ($this->requiredSafetyFlags() as $flag) {
            $this->assertFalse($result[$flag], $flag);
            $this->assertFalse($result['production_mutation_safety_summary'][$flag], $flag);
        }
        $this->assertFalse($result['c122_handoff_readiness_decision']['production_ready']);
        $this->assertFalse($result['c122_handoff_readiness_decision']['production_runtime_wiring_executed']);
        $this->assertFalse($result['controlled_runtime_wiring_handoff_readiness_context_summary']['context_persisted_to_live_runtime']);
    }

    public function test_c122_does_not_mutate_c111_through_c121_artifacts(): void
    {
        $before = $this->artifactSha1s([
            'storage/app/watchlist/backtest/c111-weekly-swing-watchlist-non-live-rehearsal-handoff-audit-archive-final-closure-review.json',
            'storage/app/watchlist/backtest/c112-weekly-swing-watchlist-production-phase-approval-review.json',
            'storage/app/watchlist/backtest/c113-weekly-swing-watchlist-production-readiness-review.json',
            'storage/app/watchlist/backtest/c120-weekly-swing-watchlist-controlled-runtime-wiring-go-decision-finalization-review.json',
            self::C121_ARTIFACT,
        ]);

        $this->runService();

        $this->assertSame($before, $this->artifactSha1s(array_keys($before)));
    }

    public function test_c122_keeps_e02_primary_b01_backup_and_a01_comparator_only(): void
    {
        $result = $this->runService();
        $scorecard = $result['c122_candidate_handoff_readiness_scorecard'];

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $result['primary_candidate_code']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $result['backup_candidate_code']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $result['comparator_candidate_code']);
        $this->assertTrue($result['a01_remains_comparator_only']);
        $this->assertTrue($scorecard[0]['primary_candidate_ready_for_controlled_runtime_wiring_handoff_finalization_review']);
        $this->assertTrue($scorecard[1]['backup_candidate_ready_for_controlled_runtime_wiring_handoff_finalization_review']);
        $this->assertFalse($scorecard[2]['ready_for_controlled_runtime_wiring_handoff_finalization_review']);
        $this->assertTrue($scorecard[2]['a01_remains_comparator_only']);
    }

    public function test_c122_rejects_prohibited_live_or_mutating_options(): void
    {
        foreach ([
            'execute_production_runtime_wiring',
            'activate_production_runtime_wiring',
            'activate_production_catalog_runtime_bridge',
            'enable_controlled_rollout',
            'persist_controlled_runtime_wiring_handoff_readiness_context_to_live_runtime',
            'persist_controlled_runtime_wiring_completion_boundary_context_to_live_runtime',
            'mutate_plan_confirm',
            'generate_official_weekly_swing_stock_recommendation',
            'generate_live_weekly_swing_watchlist_output',
            'publish_weekly_swing_output',
        ] as $option) {
            $result = $this->runService([$option => true]);
            $this->assertSame('C122_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_READINESS_REVIEW_REJECTED_LIVE_OR_PRODUCTION_MUTATION_ATTEMPT', $result['status'], $option);
        }
    }

    private function runService(array $options = []): array
    {
        $service = new WatchlistBacktestC122WeeklySwingWatchlistControlledRuntimeWiringHandoffReadinessReviewService();

        return $service->execute(
            (string) ($options['c121Artifact'] ?? self::C121_ARTIFACT),
            (string) ($options['expectedC121Hash'] ?? self::C121_HASH),
            (string) ($options['expectedC121FileSha1'] ?? self::C121_SHA1),
            $this->output,
            array_merge($options, [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'handoff_readiness_confirmed' => (bool) ($options['handoffReadinessConfirmed'] ?? true),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C122_OPERATOR_APPROVED_CONTROLLED_RUNTIME_WIRING_HANDOFF_READINESS_REVIEW_ONLY'),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-04T00:00:00+00:00'),
            ])
        );
    }

    private function mutateC121AndExecute(callable $mutator, string $name): array
    {
        $c121 = json_decode((string) file_get_contents(self::C121_ARTIFACT), true);
        $c121 = $mutator($c121);
        $path = 'storage/app/watchlist/backtest/c122-mutated-c121-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($c121, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
        $sha1 = strtoupper(sha1((string) file_get_contents($path)));

        return $this->runService([
            'c121Artifact' => $path,
            'expectedC121Hash' => self::C121_HASH,
            'expectedC121FileSha1' => $sha1,
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

    private function setPath(array &$target, array $path, $value): void
    {
        $cursor =& $target;
        foreach ($path as $index => $segment) {
            if ($index === count($path) - 1) {
                $cursor[$segment] = $value;
                return;
            }
            if (! isset($cursor[$segment]) || ! is_array($cursor[$segment])) {
                $cursor[$segment] = [];
            }
            $cursor =& $cursor[$segment];
        }
    }

    private function cleanupC122TemporaryArtifacts(): void
    {
        foreach ((array) glob('storage/app/watchlist/backtest/c122-*test.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/c122-mutated-c121-*.json') as $file) {
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
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_readiness_context_persisted_to_live_runtime',
            'controlled_runtime_wiring_handoff_readiness_context_persisted_to_live_runtime',
            'handoff_readiness_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_controlled_runtime_wiring_completion_boundary_context_persisted_to_live_runtime',
            'controlled_runtime_wiring_completion_boundary_context_persisted_to_live_runtime',
            'completion_boundary_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_controlled_runtime_wiring_go_decision_finalization_context_persisted_to_live_runtime',
            'controlled_runtime_wiring_go_decision_finalization_context_persisted_to_live_runtime',
            'go_decision_finalization_context_persisted_to_live_runtime',
            'operator_go_no_go_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_controlled_runtime_wiring_operator_go_no_go_context_persisted_to_live_runtime',
            'controlled_runtime_wiring_operator_go_no_go_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_controlled_runtime_wiring_observation_result_review_context_persisted_to_live_runtime',
            'controlled_runtime_wiring_observation_result_review_context_persisted_to_live_runtime',
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

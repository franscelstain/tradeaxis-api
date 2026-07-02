<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC119WeeklySwingWatchlistControlledRuntimeWiringOperatorGoNoGoReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC119WeeklySwingWatchlistControlledRuntimeWiringOperatorGoNoGoReviewTest extends TestCase
{
    private const C118_ARTIFACT = 'storage/app/watchlist/backtest/c118-weekly-swing-watchlist-controlled-runtime-wiring-observation-result-review.json';
    private const C118_HASH = 'fff0b2461783386f897971a55621e265f4f1498f';
    private const C118_SHA1 = '1D81849D13F815900D56FE450BF69991904EA760';
    private const OUTPUT = 'storage/app/watchlist/backtest/c119-weekly-swing-watchlist-controlled-runtime-wiring-operator-go-no-go-review-test-output.json';
    private const PASS_STATUS = 'C119_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C119_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const GO_NOT_CONFIRMED_STATUS = 'C119_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW_REJECTED_GO_DECISION_NOT_CONFIRMED';
    private const TEMP_NEGATIVE_STATUS = 'C119_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const NEXT_C120 = 'C120_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_GO_DECISION_FINALIZATION_REVIEW';

    private $output;
    private $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = self::OUTPUT;
        $this->tmpFiles = [$this->output];
        $this->cleanupC119TemporaryArtifacts();
    }

    protected function tearDown(): void
    {
        foreach ($this->tmpFiles as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        $this->cleanupC119TemporaryArtifacts();
        parent::tearDown();
    }

    public function test_c119_passes_with_valid_c118_artifact_lock_operator_approval_and_go_confirmation(): void
    {
        $result = $this->runService();

        $this->assertSame('C119_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW', $result['run_code']);
        $this->assertSame('PR-07 / C119_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW', $result['phase_label']);
        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame(self::PASS_STATUS, $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_runtime_wiring_operator_go_no_go_review_pass']);
        $this->assertSame('GO', $result['operator_go_decision']);
        $this->assertTrue($result['operator_go_decision_confirmed']);
        $this->assertTrue($result['ready_for_controlled_runtime_wiring_go_decision_finalization_review']);
        $this->assertSame(self::NEXT_C120, $result['next_step_recommendation']);
        $this->assertFileExists($this->output);
    }

    public function test_c119_rejects_missing_operator_approval(): void
    {
        $result = $this->runService(['operatorApproved' => false]);

        $this->assertSame(self::APPROVAL_MISSING_STATUS, $result['status']);
        $this->assertSame('NO_GO', $result['operator_go_decision']);
    }

    public function test_c119_rejects_missing_approval_reference(): void
    {
        $result = $this->runService(['approvalReference' => '']);

        $this->assertSame(self::APPROVAL_MISSING_STATUS, $result['status']);
    }

    public function test_c119_rejects_unconfirmed_go_decision(): void
    {
        $result = $this->runService(['operatorGoDecisionConfirmed' => false]);

        $this->assertSame(self::GO_NOT_CONFIRMED_STATUS, $result['status']);
        $this->assertSame('NO_GO', $result['operator_go_decision']);
        $this->assertFalse($result['operator_go_decision_confirmed']);
    }

    public function test_c119_rejects_missing_or_mismatched_c118_artifact_lock(): void
    {
        $missing = $this->runService([
            'c118Artifact' => 'storage/app/watchlist/backtest/c119-source-does-not-exist.json',
            'expectedC118Hash' => 'missing',
            'expectedC118FileSha1' => 'missing',
        ]);
        $hashMismatch = $this->runService(['expectedC118Hash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedC118FileSha1' => 'BADSHA1']);

        $this->assertSame('C119_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C118_ARTIFACT_LOCK_MISMATCH', $missing['status']);
        $this->assertSame('C119_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C118_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C119_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C118_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
    }

    public function test_c119_rejects_c118_status_reason_next_or_phase_mismatch(): void
    {
        $status = $this->mutateC118AndExecute(function (array $c118): array {
            $c118['status'] = 'BROKEN_STATUS';
            return $c118;
        }, 'status-broken');
        $reason = $this->mutateC118AndExecute(function (array $c118): array {
            $c118['reason_code'] = 'BROKEN_REASON';
            return $c118;
        }, 'reason-broken');
        $next = $this->mutateC118AndExecute(function (array $c118): array {
            $c118['next_step_recommendation'] = 'BROKEN_NEXT';
            $c118['next_operator_go_no_go_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c118['c118_observation_result_review_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c118['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $c118;
        }, 'next-broken');
        $phase = $this->mutateC118AndExecute(function (array $c118): array {
            $c118['phase_label'] = 'BROKEN_PHASE';
            return $c118;
        }, 'phase-broken');

        $this->assertSame('C119_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C118_STATUS_MISMATCH', $status['status']);
        $this->assertSame('C119_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C118_REASON_CODE_MISMATCH', $reason['status']);
        $this->assertSame('C119_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C118_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
        $this->assertSame('C119_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C118_PHASE_LABEL_MISMATCH', $phase['status']);
    }

    /**
     * @dataProvider c118ObservationResultReviewMismatchProvider
     */
    public function test_c119_rejects_c118_observation_result_review_state_mismatch(string $field, $value): void
    {
        $result = $this->mutateC118AndExecute(function (array $c118) use ($field, $value): array {
            $this->setPath($c118, explode('.', $field), $value);
            return $c118;
        }, 'observation-result-review-'.str_replace('.', '-', $field));

        $this->assertSame('C119_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C118_OBSERVATION_RESULT_REVIEW_INVALID', $result['status'], $field);
    }

    public function c118ObservationResultReviewMismatchProvider(): array
    {
        return [
            ['weekly_swing_watchlist_controlled_runtime_wiring_observation_result_review_pass', false],
            ['weekly_swing_watchlist_ready_for_controlled_runtime_wiring_operator_go_no_go_review', false],
            ['controlled_runtime_wiring_observation_result_review_pass', false],
            ['ready_for_controlled_runtime_wiring_operator_go_no_go_review', false],
            ['controlled_runtime_wiring_observation_result_review_manifest_created', false],
            ['controlled_runtime_wiring_operator_go_no_go_review_allowed_next', false],
            ['c118_observation_result_review_decision.review_pass', false],
            ['weekly_swing_watchlist_controlled_runtime_wiring_observation_result_review_manifest.ready_for_controlled_runtime_wiring_operator_go_no_go_review', false],
            ['weekly_swing_watchlist_controlled_runtime_wiring_observation_result_review_checklist.observation_result_reviewed', false],
            ['c117_hash_match', false],
            ['c117_file_sha1_match', false],
            ['c117_convert_from_json_pass', false],
            ['c117_observation_review_valid', false],
            ['c115_hash_match', false],
            ['c115_file_sha1_match', false],
            ['c115_convert_from_json_pass', false],
            ['c115_execution_approval_valid', false],
            ['c114_hash_match', false],
            ['c114_file_sha1_match', false],
            ['c114_convert_from_json_pass', false],
            ['c114_runtime_wiring_readiness_valid', false],
        ];
    }

    public function test_c119_rejects_c118_convert_from_json_duplicate_case_insensitive_top_level_key(): void
    {
        $raw = (string) file_get_contents(self::C118_ARTIFACT);
        $raw = preg_replace('/^\\{\\s*/', "{\n  \"Status\": \"DUPLICATE_CASE_INSENSITIVE_STATUS\",\n", $raw, 1);
        $path = 'storage/app/watchlist/backtest/c119-mutated-c118-duplicate-key.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, $raw);
        $sha1 = strtoupper(sha1((string) file_get_contents($path)));

        $result = $this->runService([
            'c118Artifact' => $path,
            'expectedC118Hash' => self::C118_HASH,
            'expectedC118FileSha1' => $sha1,
        ]);

        $this->assertSame('C119_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C118_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION', $result['status']);
        $this->assertFalse($result['c118_convert_from_json_pass']);
        $this->assertContains('status', array_map('strtolower', $result['c118_convert_from_json_duplicate_keys']));
    }

    /**
     * @dataProvider boundaryMismatchProvider
     */
    public function test_c119_rejects_boundary_violation(string $field, $value): void
    {
        $result = $this->mutateC118AndExecute(function (array $c118) use ($field, $value): array {
            $this->setPath($c118, explode('.', $field), $value);
            return $c118;
        }, 'boundary-'.str_replace('.', '-', $field));

        $this->assertSame('C119_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C111_C112_C113_C114_C115_C116_C117_C118_BOUNDARY_INVALID', $result['status'], $field);
    }

    public function boundaryMismatchProvider(): array
    {
        return [
            ['c111_final_closure_valid', false],
            ['c111_non_live_audit_archive_terminal', false],
            ['c112_not_audit_archive_continuation', false],
            ['c112_does_not_reopen_c111_final_closure', false],
            ['c113_production_readiness_valid', false],
            ['c114_runtime_wiring_readiness_review_only', false],
            ['c114_not_runtime_wiring_execution', false],
            ['c115_execution_approval_review_only', false],
            ['c115_not_runtime_wiring_execution', false],
            ['c116_execution_review_only', false],
            ['c117_observation_review_only', false],
            ['c111_c112_c113_c114_c115_c116_c117_boundary_evidence_labels.C118_OBSERVATION_RESULT_REVIEW_ONLY', false],
            ['c111_c112_c113_c114_c115_c116_c117_boundary_evidence_labels.C118_NOT_PRODUCTION_DEPLOYMENT', false],
            ['c111_c112_c113_c114_c115_c116_c117_boundary_evidence_labels.C118_NOT_PLAN_CONFIRM_MUTATION', false],
            ['c111_c112_c113_c114_c115_c116_c117_boundary_evidence_labels.C118_NOT_WEEKLY_SWING_LIVE_OUTPUT', false],
        ];
    }

    /**
     * @dataProvider candidateScopeMismatchProvider
     */
    public function test_c119_rejects_candidate_scope_change_or_a01_promotion(string $field, $value): void
    {
        $result = $this->mutateC118AndExecute(function (array $c118) use ($field, $value): array {
            $c118[$field] = $value;
            return $c118;
        }, 'candidate-'.$field);

        $this->assertSame('C119_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $result['status'], $field);
    }

    public function candidateScopeMismatchProvider(): array
    {
        return [
            ['primary_candidate_code', 'BROKEN_PRIMARY'],
            ['backup_candidate_code', 'BROKEN_BACKUP'],
            ['comparator_candidate_code', 'BROKEN_COMPARATOR'],
            ['primary_candidate_ready_for_controlled_runtime_wiring_operator_go_no_go_review', false],
            ['backup_candidate_ready_for_controlled_runtime_wiring_operator_go_no_go_review', false],
            ['comparator_candidate_ready_for_controlled_runtime_wiring_operator_go_no_go_review', true],
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
    public function test_c119_rejects_any_live_or_mutating_safety_flag_true_in_c118(string $field): void
    {
        $result = $this->mutateC118AndExecute(function (array $c118) use ($field): array {
            $c118[$field] = true;
            return $c118;
        }, 'safety-'.$field);

        $this->assertSame('C119_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW_REJECTED_LIVE_OR_PRODUCTION_MUTATION_ATTEMPT', $result['status'], $field);
        $this->assertSame($field, $result['c118_live_or_mutating_safety_flag_failure']);
    }

    public function safetyFlagProvider(): array
    {
        return array_map(static function (string $flag): array {
            return [$flag];
        }, $this->requiredSafetyFlags());
    }

    public function test_c119_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c119-no-temporary-artifact-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame(self::TEMP_NEGATIVE_STATUS, $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c119_records_source_locks_decisions_manifest_checklist_and_no_live_output(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();
        $manifest = $result['weekly_swing_watchlist_controlled_runtime_wiring_operator_go_no_go_manifest'];
        $checklist = $result['weekly_swing_watchlist_controlled_runtime_wiring_operator_go_no_go_checklist'];

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $this->assertSame(self::C118_HASH, $result['expected_c118_hash']);
        $this->assertSame(self::C118_HASH, $result['actual_c118_hash']);
        $this->assertTrue($result['c118_hash_match']);
        $this->assertSame(self::C118_SHA1, $result['expected_c118_file_sha1']);
        $this->assertSame(self::C118_SHA1, $result['actual_c118_file_sha1']);
        $this->assertTrue($result['c118_file_sha1_match']);
        $this->assertTrue($result['c118_convert_from_json_pass']);
        $this->assertTrue($result['c118_observation_result_review_valid']);
        $this->assertSame(self::NEXT_C120, $result['next_go_decision_finalization_decision']['next_recommendation']);

        foreach ([
            'source_artifact_locks',
            'c118_lock_validation_summary',
            'c111_c112_c113_c114_c115_c116_c117_c118_boundary_carry_forward_summary',
            'candidate_scope_freeze_summary',
            'c118_final_operator_evidence_summary',
            'operator_approval_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'c119_operator_go_no_go_decision',
            'next_go_decision_finalization_decision',
            'weekly_swing_watchlist_controlled_runtime_wiring_operator_go_no_go_manifest',
            'weekly_swing_watchlist_controlled_runtime_wiring_operator_go_no_go_checklist',
            'c119_candidate_operator_go_no_go_scorecard',
            'controlled_runtime_wiring_operator_go_no_go_context_summary',
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

        $this->assertTrue($manifest['operator_go_no_go_artifact_only']);
        $this->assertSame('GO', $manifest['operator_go_decision']);
        $this->assertTrue($manifest['ready_for_controlled_runtime_wiring_go_decision_finalization_review']);
        $this->assertFalse($manifest['runtime_wiring_execution_performed_against_live_runtime']);
        $this->assertFalse($manifest['runtime_wiring_enabled']);
        $this->assertFalse($manifest['production_runtime_wiring_allowed']);
        $this->assertFalse($manifest['production_deployment_allowed']);
        $this->assertFalse($manifest['weekly_swing_live_output_enabled']);
        $this->assertFalse($manifest['operator_go_no_go_used_for_plan_confirm_mutation']);
        $this->assertTrue($checklist['operator_go_no_go_reviewed']);
        $this->assertTrue($checklist['c118_source_lock_reviewed']);
        $this->assertTrue($checklist['artifact_only']);
        $this->assertTrue($checklist['production_runtime_wiring_not_enabled']);
        $this->assertFalse($checklist['weekly_swing_stock_recommendation_generated']);
        $this->assertFalse($result['weekly_swing_watchlist_live_output_enabled']);
        $this->assertFalse($result['weekly_swing_watchlist_live_recommendation_generated']);
    }

    public function test_c119_keeps_all_safety_flags_false(): void
    {
        $result = $this->runService();

        foreach ($this->requiredSafetyFlags() as $flag) {
            $this->assertFalse($result[$flag], $flag);
            $this->assertFalse($result['production_mutation_safety_summary'][$flag], $flag);
        }
        $this->assertFalse($result['c119_operator_go_no_go_decision']['production_ready']);
        $this->assertFalse($result['c119_operator_go_no_go_decision']['production_runtime_wiring_executed']);
        $this->assertFalse($result['controlled_runtime_wiring_operator_go_no_go_context_summary']['context_persisted_to_live_runtime']);
    }

    public function test_c119_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $first = $this->runService(['createdAt' => '2026-07-02T00:00:00+00:00']);
        $firstHash = $first['artifact_hash'];
        if (is_file($this->output)) {
            unlink($this->output);
        }
        $second = $this->runService(['createdAt' => '2026-07-02T00:00:00+00:00']);

        $this->assertSame($firstHash, $second['artifact_hash']);
    }

    public function test_c119_does_not_mutate_c111_c112_c113_c114_c115_c116_c117_or_c118_artifacts(): void
    {
        $before = $this->artifactSha1s([
            'storage/app/watchlist/backtest/c111-weekly-swing-watchlist-non-live-rehearsal-handoff-audit-archive-final-closure-review.json',
            'storage/app/watchlist/backtest/c112-weekly-swing-watchlist-production-phase-approval-review.json',
            'storage/app/watchlist/backtest/c113-weekly-swing-watchlist-production-readiness-review.json',
            'storage/app/watchlist/backtest/c114-weekly-swing-watchlist-production-runtime-wiring-readiness-review.json',
            'storage/app/watchlist/backtest/c115-weekly-swing-watchlist-controlled-runtime-wiring-execution-approval-review.json',
            'storage/app/watchlist/backtest/c116-weekly-swing-watchlist-controlled-runtime-wiring-execution-review.json',
            'storage/app/watchlist/backtest/c117-weekly-swing-watchlist-controlled-runtime-wiring-observation-review.json',
            self::C118_ARTIFACT,
        ]);

        $this->runService();

        $this->assertSame($before, $this->artifactSha1s(array_keys($before)));
    }

    public function test_c119_keeps_e02_primary_b01_backup_and_a01_comparator_only(): void
    {
        $result = $this->runService();
        $scorecard = $result['c119_candidate_operator_go_no_go_scorecard'];

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $result['primary_candidate_code']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $result['backup_candidate_code']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $result['comparator_candidate_code']);
        $this->assertTrue($result['a01_remains_comparator_only']);
        $this->assertTrue($scorecard[0]['primary_candidate_ready_for_controlled_runtime_wiring_go_decision_finalization_review']);
        $this->assertTrue($scorecard[1]['backup_candidate_ready_for_controlled_runtime_wiring_go_decision_finalization_review']);
        $this->assertFalse($scorecard[2]['ready_for_controlled_runtime_wiring_go_decision_finalization_review']);
        $this->assertTrue($scorecard[2]['a01_remains_comparator_only']);
    }

    public function test_c119_rejects_prohibited_live_or_mutating_options(): void
    {
        foreach ([
            'execute_production_runtime_wiring',
            'activate_production_catalog_runtime_bridge',
            'enable_controlled_rollout',
            'persist_controlled_runtime_wiring_operator_go_no_go_context_to_live_runtime',
            'mutate_plan_confirm',
            'generate_official_weekly_swing_stock_recommendation',
            'generate_live_weekly_swing_watchlist_output',
            'publish_weekly_swing_output',
        ] as $option) {
            $result = $this->runService([$option => true]);
            $this->assertSame('C119_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW_REJECTED_LIVE_OR_PRODUCTION_MUTATION_ATTEMPT', $result['status'], $option);
        }
    }

    private function runService(array $options = []): array
    {
        $service = new WatchlistBacktestC119WeeklySwingWatchlistControlledRuntimeWiringOperatorGoNoGoReviewService();

        return $service->execute(
            (string) ($options['c118Artifact'] ?? self::C118_ARTIFACT),
            (string) ($options['expectedC118Hash'] ?? self::C118_HASH),
            (string) ($options['expectedC118FileSha1'] ?? self::C118_SHA1),
            $this->output,
            array_merge($options, [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'operator_go_decision_confirmed' => (bool) ($options['operatorGoDecisionConfirmed'] ?? true),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C119_OPERATOR_APPROVED_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW_ONLY'),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-02T00:00:00+00:00'),
            ])
        );
    }

    private function mutateC118AndExecute(callable $mutator, string $name): array
    {
        $c118 = json_decode((string) file_get_contents(self::C118_ARTIFACT), true);
        $c118 = $mutator($c118);
        $path = 'storage/app/watchlist/backtest/c119-mutated-c118-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($c118, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
        $sha1 = strtoupper(sha1((string) file_get_contents($path)));

        return $this->runService([
            'c118Artifact' => $path,
            'expectedC118Hash' => self::C118_HASH,
            'expectedC118FileSha1' => $sha1,
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

    private function cleanupC119TemporaryArtifacts(): void
    {
        foreach ((array) glob('storage/app/watchlist/backtest/c119-*test.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/c119-mutated-c118-*.json') as $file) {
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
            'operator_go_no_go_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_controlled_runtime_wiring_operator_go_no_go_context_persisted_to_live_runtime',
            'controlled_runtime_wiring_operator_go_no_go_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_controlled_runtime_wiring_observation_result_review_context_persisted_to_live_runtime',
            'controlled_runtime_wiring_observation_result_review_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_controlled_runtime_wiring_observation_review_context_persisted_to_live_runtime',
            'controlled_runtime_wiring_observation_review_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_controlled_runtime_wiring_execution_review_context_persisted_to_live_runtime',
            'controlled_runtime_wiring_execution_review_context_persisted_to_live_runtime',
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

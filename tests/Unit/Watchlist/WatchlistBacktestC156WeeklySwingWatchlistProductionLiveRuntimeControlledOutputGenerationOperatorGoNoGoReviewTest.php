<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC156WeeklySwingWatchlistProductionLiveRuntimeControlledOutputGenerationOperatorGoNoGoReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC156WeeklySwingWatchlistProductionLiveRuntimeControlledOutputGenerationOperatorGoNoGoReviewTest extends TestCase
{
    private const C155_ARTIFACT = 'storage/app/watchlist/backtest/c155-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-result-review.json';
    private const C155_HASH = '6fa40eafa588299db84b465202ea060a310d0d12';
    private const C155_SHA1 = '637A4D7EAE383CDCD8804040384367439847B16D';
    private const GO_STATUS = 'C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP_READY_FOR_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW';
    private const NO_GO_STATUS = 'C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_COMPLETED_NO_GO_CONTROLLED_OUTPUT_GENERATION_STOPPED';
    private const HOLD_STATUS = 'C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_COMPLETED_HOLD_CONTROLLED_OUTPUT_GENERATION_DEFERRED';
    private const APPROVAL_MISSING_STATUS = 'C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const DECISION_INVALID_STATUS = 'C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_DECISION_INVALID';
    private const DECISION_NOT_CONFIRMED_STATUS = 'C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_DECISION_NOT_CONFIRMED';
    private const DECISION_REASON_MISSING_STATUS = 'C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_DECISION_REASON_MISSING';
    private const TEMP_NEGATIVE_STATUS = 'C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const NEXT_C157 = 'C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW';

    private string $output;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c156-controlled-output-generation-operator-go-no-go.json';
        $this->cleanupC156TemporaryArtifacts();
        @unlink($this->output);
    }

    protected function tearDown(): void
    {
        @unlink($this->output);
        foreach ($this->tmpFiles as $path) {
            @unlink($path);
        }
        $this->cleanupC156TemporaryArtifacts();
        parent::tearDown();
    }

    public function test_c156_go_passes_with_valid_c155_lock_operator_approval_confirmed_decision_and_reason(): void
    {
        $result = $this->runService();

        $this->assertSame('C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW', $result['run_code']);
        $this->assertSame('PR-44 / C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW', $result['phase_label']);
        $this->assertSame(self::GO_STATUS, $result['status']);
        $this->assertSame(self::GO_STATUS, $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_production_live_runtime_controlled_output_generation_operator_go_no_go_review_pass']);
        $this->assertTrue($result['production_live_runtime_controlled_output_generation_operator_go_no_go_review_pass']);
        $this->assertTrue($result['operator_decision_recorded']);
        $this->assertSame('GO', $result['operator_decision']);
        $this->assertSame('GO', $result['operator_go_decision']);
        $this->assertTrue($result['operator_decision_confirmed']);
        $this->assertTrue($result['ready_for_weekly_swing_watchlist_controlled_output_generation_go_decision_finalization_review']);
        $this->assertTrue($result['production_live_runtime_controlled_output_generation_go_decision_finalization_review_allowed_next']);
        $this->assertFalse($result['weekly_swing_watchlist_official_output_published']);
        $this->assertFalse($result['weekly_swing_watchlist_publication_allowed']);
        $this->assertFalse($result['weekly_swing_watchlist_unrestricted_publication_allowed']);
        $this->assertFalse($result['plan_confirm_mutated']);
        $this->assertTrue($result['c155_lock_valid']);
        $this->assertTrue($result['c155_controlled_output_generation_result_review_valid']);
        $this->assertSame(self::NEXT_C157, $result['next_step_recommendation']);
        $this->assertFileExists($this->output);
    }

    public function test_c156_records_no_go_as_completed_decision_without_opening_go_decision_finalization(): void
    {
        $result = $this->runService([
            'operatorDecision' => 'NO_GO',
            'decisionReason' => 'Operator rejects progression after reviewing C155 controlled output result.',
        ]);

        $this->assertSame(self::NO_GO_STATUS, $result['status']);
        $this->assertTrue($result['operator_decision_recorded']);
        $this->assertSame('NO_GO', $result['operator_decision']);
        $this->assertTrue($result['operator_no_go_decision']);
        $this->assertFalse($result['operator_hold_decision']);
        $this->assertFalse($result['ready_for_weekly_swing_watchlist_controlled_output_generation_go_decision_finalization_review']);
        $this->assertFalse($result['production_live_runtime_controlled_output_generation_go_decision_finalization_review_allowed_next']);
        $this->assertTrue($result['controlled_output_generation_stopped_no_go']);
        $this->assertSame('C156_NO_GO_CLOSE_CONTROLLED_OUTPUT_GENERATION', $result['next_step_recommendation']);
        $this->assertFalse($result['weekly_swing_watchlist_publication_allowed']);
    }

    public function test_c156_records_hold_as_completed_decision_without_opening_go_decision_finalization(): void
    {
        $result = $this->runService([
            'operatorDecision' => 'HOLD',
            'decisionReason' => 'Operator defers progression until a scheduled review window is available.',
        ]);

        $this->assertSame(self::HOLD_STATUS, $result['status']);
        $this->assertSame('HOLD', $result['operator_decision']);
        $this->assertFalse($result['operator_no_go_decision']);
        $this->assertTrue($result['operator_hold_decision']);
        $this->assertFalse($result['ready_for_weekly_swing_watchlist_controlled_output_generation_go_decision_finalization_review']);
        $this->assertFalse($result['production_live_runtime_controlled_output_generation_go_decision_finalization_review_allowed_next']);
        $this->assertTrue($result['controlled_output_generation_deferred_hold']);
        $this->assertSame('C156_HOLD_KEEP_C155_LOCKED_UNTIL_OPERATOR_WINDOW', $result['next_step_recommendation']);
        $this->assertFalse($result['plan_confirm_mutated']);
    }

    public function test_c156_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->runService(['operatorApproved' => false]);
        $missingReference = $this->runService(['approvalReference' => '']);

        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingOperator['status']);
        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingReference['status']);
    }

    public function test_c156_rejects_invalid_unconfirmed_or_unexplained_operator_decision(): void
    {
        $invalid = $this->runService(['operatorDecision' => 'MAYBE']);
        $unconfirmed = $this->runService(['operatorDecisionConfirmed' => false]);
        $missingReason = $this->runService(['decisionReason' => '']);

        $this->assertSame(self::DECISION_INVALID_STATUS, $invalid['status']);
        $this->assertSame(self::DECISION_NOT_CONFIRMED_STATUS, $unconfirmed['status']);
        $this->assertSame(self::DECISION_REASON_MISSING_STATUS, $missingReason['status']);
    }

    public function test_c156_rejects_missing_or_mismatched_c155_artifact_lock(): void
    {
        $missing = $this->runService([
            'c155Artifact' => 'storage/app/watchlist/backtest/.tmp-c156-source-c155-missing.json',
            'expectedC155Hash' => 'missing',
            'expectedC155FileSha1' => 'missing',
        ]);
        $hashMismatch = $this->runService(['expectedC155Hash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedC155FileSha1' => 'BADSHA1']);

        $this->assertSame('C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C155_ARTIFACT_LOCK_MISMATCH', $missing['status']);
        $this->assertSame('C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C155_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C155_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
    }

    public function test_c156_rejects_c155_status_phase_or_next_recommendation_mismatch(): void
    {
        $status = $this->mutateC155AndExecute(function (array $c155): array {
            $c155['status'] = 'BROKEN_STATUS';
            return $c155;
        }, 'status-broken');
        $phase = $this->mutateC155AndExecute(function (array $c155): array {
            $c155['phase_label'] = 'BROKEN_PHASE';
            return $c155;
        }, 'phase-broken');
        $next = $this->mutateC155AndExecute(function (array $c155): array {
            $c155['next_step_recommendation'] = 'BROKEN_NEXT';
            $c155['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $c155;
        }, 'next-broken');

        $this->assertSame('C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C155_STATUS_MISMATCH', $status['status']);
        $this->assertSame('C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C155_PHASE_LABEL_MISMATCH', $phase['status']);
        $this->assertSame('C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C155_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
    }

    public function test_c156_rejects_c155_convert_from_json_duplicate_top_level_keys(): void
    {
        $raw = (string) file_get_contents(self::C155_ARTIFACT);
        $path = 'storage/app/watchlist/backtest/.tmp-c156-source-c155-duplicate-key.json';
        $this->tmpFiles[] = $path;
        $duplicateRaw = preg_replace('/\{/', "{\"Run_Code\":\"DUPLICATE_CASE_KEY\",", $raw, 1);
        file_put_contents($path, $duplicateRaw);

        $result = $this->runService([
            'c155Artifact' => $path,
            'expectedC155Hash' => self::C155_HASH,
            'expectedC155FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);

        $this->assertSame('C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C155_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION', $result['status']);
        $this->assertFalse($result['c155_convert_from_json_pass']);
    }

    /**
     * @dataProvider c155ResultReviewMismatchProvider
     */
    public function test_c156_rejects_incomplete_c155_result_review_evidence(string $field, $value): void
    {
        $result = $this->mutateC155AndExecute(function (array $c155) use ($field, $value): array {
            $c155[$field] = $value;
            return $c155;
        }, 'result-'.$field);

        $this->assertSame('C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C155_RESULT_REVIEW_INCOMPLETE', $result['status'], $field);
    }

    public function c155ResultReviewMismatchProvider(): array
    {
        return [
            ['weekly_swing_watchlist_production_live_runtime_controlled_output_generation_result_review_pass', false],
            ['ready_for_weekly_swing_watchlist_controlled_output_generation_operator_go_no_go_review', false],
            ['controlled_output_lock_valid', false],
            ['controlled_output_integrity_valid', false],
            ['primary_candidate_controlled_output_result_reviewed', false],
            ['backup_candidate_controlled_output_result_reviewed', false],
            ['comparator_candidate_controlled_output_result_reviewed', true],
            ['c155_controlled_output_generation_result_review_only', false],
            ['c155_not_publication', false],
            ['result_review_confirmed', false],
            ['temporary_negative_artifact_cleanup_confirmed', false],
        ];
    }

    public function test_c156_rejects_publication_or_plan_confirm_mutation_from_c155(): void
    {
        $published = $this->mutateC155AndExecute(function (array $c155): array {
            $c155['weekly_swing_watchlist_official_output_published'] = true;
            return $c155;
        }, 'published');
        $publicationAllowed = $this->mutateC155AndExecute(function (array $c155): array {
            $c155['weekly_swing_watchlist_publication_allowed'] = true;
            return $c155;
        }, 'publication-allowed');
        $planConfirm = $this->mutateC155AndExecute(function (array $c155): array {
            $c155['plan_confirm_mutated'] = true;
            return $c155;
        }, 'plan-confirm');

        $this->assertSame('C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C155_RESULT_REVIEW_INCOMPLETE', $published['status']);
        $this->assertSame('C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C155_RESULT_REVIEW_INCOMPLETE', $publicationAllowed['status']);
        $this->assertSame('C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C155_RESULT_REVIEW_INCOMPLETE', $planConfirm['status']);
    }

    public function test_c156_rejects_candidate_scope_change_or_a01_promotion(): void
    {
        $candidate = $this->mutateC155AndExecute(function (array $c155): array {
            $c155['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $c155;
        }, 'candidate-primary');
        $a01 = $this->mutateC155AndExecute(function (array $c155): array {
            $c155['a01_promoted'] = true;
            return $c155;
        }, 'candidate-a01');

        $this->assertSame('C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $candidate['status']);
        $this->assertSame('C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $a01['status']);
    }

    public function test_c156_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c156-no-temporary-artifact-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame(self::TEMP_NEGATIVE_STATUS, $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c156_records_source_locks_manifest_checklist_and_no_publication(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();
        $manifest = $result['weekly_swing_watchlist_controlled_output_generation_operator_go_no_go_manifest'];
        $checklist = $result['weekly_swing_watchlist_controlled_output_generation_operator_go_no_go_checklist'];

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $this->assertSame(self::C155_HASH, $result['expected_c155_hash']);
        $this->assertSame(self::C155_HASH, $result['actual_c155_hash']);
        $this->assertTrue($result['c155_hash_match']);
        $this->assertSame(self::C155_SHA1, $result['expected_c155_file_sha1']);
        $this->assertSame(self::C155_SHA1, $result['actual_c155_file_sha1']);
        $this->assertTrue($result['c155_file_sha1_match']);
        $this->assertTrue($result['c155_convert_from_json_pass']);
        $this->assertSame(self::NEXT_C157, $result['next_concrete_controlled_output_step_decision']['next_recommendation']);

        foreach ([
            'source_artifact_locks',
            'c155_lock_validation_summary',
            'c155_controlled_output_generation_result_review_carry_forward_summary',
            'controlled_output_publication_guard_summary',
            'candidate_scope_freeze_summary',
            'operator_decision_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'c156_operator_go_no_go_decision',
            'next_concrete_controlled_output_step_decision',
            'weekly_swing_watchlist_controlled_output_generation_operator_go_no_go_manifest',
            'weekly_swing_watchlist_controlled_output_generation_operator_go_no_go_checklist',
            'c156_candidate_controlled_output_operator_go_no_go_scorecard',
            'controlled_output_generation_operator_go_no_go_context_summary',
            'publication_plan_confirm_safety_summary',
            'documentation_hygiene_guard_summary',
            'progress_summary',
            'planned_next_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }

        $this->assertTrue($manifest['operator_go_no_go_artifact_only']);
        $this->assertSame('GO', $manifest['operator_decision']);
        $this->assertTrue($manifest['operator_go_no_go_review_pass']);
        $this->assertTrue($manifest['ready_for_go_decision_finalization_review']);
        $this->assertFalse($manifest['official_output_published']);
        $this->assertFalse($manifest['publication_allowed']);
        $this->assertFalse($manifest['operator_go_no_go_used_for_plan_confirm_mutation']);
        $this->assertFalse($manifest['operator_go_no_go_used_for_publication']);
        $this->assertTrue($checklist['operator_go_no_go_reviewed']);
        $this->assertTrue($checklist['artifact_only']);
        $this->assertFalse($checklist['weekly_swing_stock_recommendation_published_in_c156']);
    }

    public function test_c156_keeps_e02_primary_b01_backup_a01_comparator_and_publication_flags_false(): void
    {
        $result = $this->runService();
        $scorecard = $result['c156_candidate_controlled_output_operator_go_no_go_scorecard'];

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $result['primary_candidate_code']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $result['backup_candidate_code']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $result['comparator_candidate_code']);
        $this->assertTrue($result['primary_candidate_ready_for_controlled_output_generation_go_decision_finalization_review']);
        $this->assertTrue($result['backup_candidate_ready_for_controlled_output_generation_go_decision_finalization_review']);
        $this->assertFalse($result['comparator_candidate_ready_for_controlled_output_generation_go_decision_finalization_review']);
        $this->assertTrue($result['a01_remains_comparator_only']);
        $this->assertTrue($scorecard[0]['primary_candidate_ready_for_controlled_output_generation_go_decision_finalization_review']);
        $this->assertTrue($scorecard[1]['backup_candidate_ready_for_controlled_output_generation_go_decision_finalization_review']);
        $this->assertFalse($scorecard[2]['ready_for_go_decision_finalization_review']);

        foreach ([
            'weekly_swing_watchlist_official_output_published',
            'weekly_swing_watchlist_publication_allowed',
            'weekly_swing_watchlist_unrestricted_publication_allowed',
            'plan_confirm_mutation_allowed',
            'plan_confirm_mutated',
            'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed',
            'live_plan_confirm_rollout_executed',
        ] as $flag) {
            $this->assertFalse($result[$flag], $flag);
            $this->assertFalse($result['publication_plan_confirm_safety_summary'][$flag], $flag);
        }
    }

    public function test_c156_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $first = $this->runService(['createdAt' => '2026-07-15T00:00:00+00:00']);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c156-controlled-output-generation-operator-go-no-go-second.json';
        $this->tmpFiles[] = $secondOutput;
        $second = $this->runService([
            'output' => $secondOutput,
            'createdAt' => '2026-07-15T00:00:00+00:00',
        ]);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
    }

    public function test_c156_does_not_mutate_c155_artifact_or_config_defaults(): void
    {
        $beforeC155 = strtoupper(sha1((string) file_get_contents(self::C155_ARTIFACT)));
        $beforeConfig = (string) file_get_contents('config/watchlist.php');

        $this->runService();

        $this->assertSame($beforeC155, strtoupper(sha1((string) file_get_contents(self::C155_ARTIFACT))));
        $this->assertSame($beforeConfig, (string) file_get_contents('config/watchlist.php'));
    }

    private function runService(array $options = []): array
    {
        $service = new WatchlistBacktestC156WeeklySwingWatchlistProductionLiveRuntimeControlledOutputGenerationOperatorGoNoGoReviewService();

        return $service->execute(
            (string) ($options['c155Artifact'] ?? self::C155_ARTIFACT),
            (string) ($options['expectedC155Hash'] ?? self::C155_HASH),
            (string) ($options['expectedC155FileSha1'] ?? self::C155_SHA1),
            (string) ($options['output'] ?? $this->output),
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'operator_decision_confirmed' => (bool) ($options['operatorDecisionConfirmed'] ?? true),
                'operator_decision' => (string) ($options['operatorDecision'] ?? 'GO'),
                'decision_reason' => (string) ($options['decisionReason'] ?? 'Operator records GO from locked C155 controlled output result review into C157 go decision finalization review target.'),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C156_OPERATOR_DECISION_CONTROLLED_OUTPUT_GENERATION_GO_NO_GO_REVIEW_ONLY'),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-15T00:00:00+00:00'),
            ]
        );
    }

    private function mutateC155AndExecute(callable $mutator, string $name): array
    {
        $c155 = json_decode((string) file_get_contents(self::C155_ARTIFACT), true);
        $c155 = $mutator(is_array($c155) ? $c155 : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c156-source-c155-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($c155, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'c155Artifact' => $path,
            'expectedC155Hash' => (string) ($c155['artifact_hash'] ?? ''),
            'expectedC155FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function readOutput(): array
    {
        $decoded = json_decode((string) file_get_contents($this->output), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function cleanupC156TemporaryArtifacts(): void
    {
        foreach ((array) glob('storage/app/watchlist/backtest/c156-*test.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/.tmp-c156*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}

<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC158WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationOperatorGoNoGoReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC158WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationOperatorGoNoGoReviewTest extends TestCase
{
    private const C158_RESULT_REVIEW_ARTIFACT = 'storage/app/watchlist/backtest/c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-result-review.json';
    private const C158_RESULT_REVIEW_HASH = '2912bf54b34ee23b4413a179072d3e670f92e719';
    private const C158_RESULT_REVIEW_SHA1 = 'C601A8598D83D61FB84F0AAB3DED9AD8E36AD59B';
    private const GO_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP_READY_FOR_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW';
    private const NO_GO_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_COMPLETED_NO_GO_CONTROLLED_OUTPUT_PUBLICATION_STOPPED';
    private const HOLD_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_COMPLETED_HOLD_CONTROLLED_OUTPUT_PUBLICATION_DEFERRED';
    private const APPROVAL_MISSING_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const DECISION_INVALID_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_DECISION_INVALID';
    private const DECISION_NOT_CONFIRMED_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_DECISION_NOT_CONFIRMED';
    private const DECISION_REASON_MISSING_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_DECISION_REASON_MISSING';
    private const TEMP_NEGATIVE_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const NEXT_FINALIZATION = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW';

    private string $output;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c158-controlled-output-publication-operator-go-no-go-review.json';
        $this->cleanupC158TemporaryArtifacts();
        @unlink($this->output);
    }

    protected function tearDown(): void
    {
        @unlink($this->output);
        foreach ($this->tmpFiles as $path) {
            @unlink($path);
        }
        $this->cleanupC158TemporaryArtifacts();
        parent::tearDown();
    }

    public function test_c158_operator_go_passes_and_keeps_same_topic_number_for_finalization(): void
    {
        $result = $this->runService();

        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW', $result['run_code']);
        $this->assertSame('PR-49 / C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW', $result['phase_label']);
        $this->assertSame('C158_CONTROLLED_OUTPUT_PUBLICATION', $result['topic_code']);
        $this->assertSame('OPERATOR_GO_NO_GO_REVIEW', $result['topic_stage']);
        $this->assertSame(self::GO_STATUS, $result['status']);
        $this->assertSame(self::GO_STATUS, $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_production_live_runtime_controlled_output_publication_operator_go_no_go_review_pass']);
        $this->assertTrue($result['production_live_runtime_controlled_output_publication_operator_go_no_go_review_pass']);
        $this->assertTrue($result['operator_decision_recorded']);
        $this->assertSame('GO', $result['operator_decision']);
        $this->assertSame('GO', $result['operator_go_decision']);
        $this->assertTrue($result['operator_decision_confirmed']);
        $this->assertTrue($result['ready_for_weekly_swing_watchlist_controlled_output_publication_go_decision_finalization_review']);
        $this->assertTrue($result['production_live_runtime_controlled_output_publication_go_decision_finalization_review_allowed_next']);
        $this->assertTrue($result['c158_result_review_lock_valid']);
        $this->assertTrue($result['c158_controlled_output_publication_result_review_valid']);
        $this->assertSame(self::NEXT_FINALIZATION, $result['next_step_recommendation']);
        $this->assertFileExists($this->output);
    }

    public function test_c158_operator_records_no_go_without_opening_go_finalization(): void
    {
        $result = $this->runService([
            'operatorDecision' => 'NO_GO',
            'decisionReason' => 'Operator stops controlled publication progression after reviewing C158 result evidence.',
        ]);

        $this->assertSame(self::NO_GO_STATUS, $result['status']);
        $this->assertSame('NO_GO', $result['operator_decision']);
        $this->assertTrue($result['operator_no_go_decision']);
        $this->assertFalse($result['operator_hold_decision']);
        $this->assertFalse($result['ready_for_weekly_swing_watchlist_controlled_output_publication_go_decision_finalization_review']);
        $this->assertFalse($result['production_live_runtime_controlled_output_publication_go_decision_finalization_review_allowed_next']);
        $this->assertTrue($result['controlled_output_publication_stopped_no_go']);
        $this->assertSame('C158_NO_GO_CLOSE_CONTROLLED_OUTPUT_PUBLICATION', $result['next_step_recommendation']);
        $this->assertFalse($result['weekly_swing_watchlist_publication_allowed']);
    }

    public function test_c158_operator_records_hold_without_opening_go_finalization(): void
    {
        $result = $this->runService([
            'operatorDecision' => 'HOLD',
            'decisionReason' => 'Operator defers controlled publication finalization until a scheduled review window.',
        ]);

        $this->assertSame(self::HOLD_STATUS, $result['status']);
        $this->assertSame('HOLD', $result['operator_decision']);
        $this->assertFalse($result['operator_no_go_decision']);
        $this->assertTrue($result['operator_hold_decision']);
        $this->assertFalse($result['ready_for_weekly_swing_watchlist_controlled_output_publication_go_decision_finalization_review']);
        $this->assertTrue($result['controlled_output_publication_deferred_hold']);
        $this->assertSame('C158_HOLD_KEEP_CONTROLLED_PUBLICATION_LOCKED_UNTIL_OPERATOR_WINDOW', $result['next_step_recommendation']);
        $this->assertFalse($result['plan_confirm_mutated']);
    }

    public function test_c158_operator_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->runService(['operatorApproved' => false]);
        $missingReference = $this->runService(['approvalReference' => '']);

        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingOperator['status']);
        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingReference['status']);
    }

    public function test_c158_operator_rejects_invalid_unconfirmed_or_unexplained_decision(): void
    {
        $invalid = $this->runService(['operatorDecision' => 'MAYBE']);
        $unconfirmed = $this->runService(['operatorDecisionConfirmed' => false]);
        $missingReason = $this->runService(['decisionReason' => '']);

        $this->assertSame(self::DECISION_INVALID_STATUS, $invalid['status']);
        $this->assertSame(self::DECISION_NOT_CONFIRMED_STATUS, $unconfirmed['status']);
        $this->assertSame(self::DECISION_REASON_MISSING_STATUS, $missingReason['status']);
    }

    public function test_c158_operator_rejects_result_review_lock_mismatch(): void
    {
        $missing = $this->runService([
            'c158ResultReviewArtifact' => 'storage/app/watchlist/backtest/.tmp-c158-operator-source-missing.json',
            'expectedC158ResultReviewHash' => 'missing',
            'expectedC158ResultReviewFileSha1' => 'missing',
        ]);
        $hashMismatch = $this->runService(['expectedC158ResultReviewHash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedC158ResultReviewFileSha1' => 'BADSHA1']);

        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C158_RESULT_REVIEW_ARTIFACT_LOCK_MISMATCH', $missing['status']);
        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C158_RESULT_REVIEW_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C158_RESULT_REVIEW_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
    }

    public function test_c158_operator_rejects_result_review_status_phase_or_next_mismatch(): void
    {
        $status = $this->mutateResultReviewAndExecute(function (array $review): array {
            $review['status'] = 'BROKEN_STATUS';
            return $review;
        }, 'status');
        $phase = $this->mutateResultReviewAndExecute(function (array $review): array {
            $review['phase_label'] = 'BROKEN_PHASE';
            return $review;
        }, 'phase');
        $next = $this->mutateResultReviewAndExecute(function (array $review): array {
            $review['next_step_recommendation'] = 'BROKEN_NEXT';
            $review['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $review;
        }, 'next');

        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C158_RESULT_REVIEW_STATUS_MISMATCH', $status['status']);
        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C158_RESULT_REVIEW_PHASE_LABEL_MISMATCH', $phase['status']);
        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C158_RESULT_REVIEW_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
    }

    public function test_c158_operator_rejects_convert_from_json_duplicate_top_level_keys(): void
    {
        $raw = (string) file_get_contents(self::C158_RESULT_REVIEW_ARTIFACT);
        $path = 'storage/app/watchlist/backtest/.tmp-c158-operator-source-duplicate-key.json';
        $this->tmpFiles[] = $path;
        $duplicateRaw = preg_replace('/\{/', "{\"Run_Code\":\"DUPLICATE_CASE_KEY\",", $raw, 1);
        file_put_contents($path, $duplicateRaw);

        $result = $this->runService([
            'c158ResultReviewArtifact' => $path,
            'expectedC158ResultReviewFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);

        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C158_RESULT_REVIEW_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION', $result['status']);
        $this->assertFalse($result['c158_result_review_convert_from_json_pass']);
    }

    /**
     * @dataProvider c158ResultReviewIncompleteProvider
     */
    public function test_c158_operator_rejects_incomplete_result_review_evidence(string $field, $value): void
    {
        $result = $this->mutateResultReviewAndExecute(function (array $review) use ($field, $value): array {
            $this->setValueAt($review, explode('.', $field), $value);
            return $review;
        }, 'incomplete-'.str_replace('.', '-', $field));

        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C158_RESULT_REVIEW_INCOMPLETE', $result['status'], $field);
    }

    public function c158ResultReviewIncompleteProvider(): array
    {
        return [
            ['weekly_swing_watchlist_production_live_runtime_controlled_output_publication_result_review_pass', false],
            ['ready_for_weekly_swing_watchlist_controlled_output_publication_operator_go_no_go_review', false],
            ['controlled_publication_lock_valid', false],
            ['controlled_publication_integrity_valid', false],
            ['primary_candidate_controlled_publication_result_reviewed', false],
            ['backup_candidate_controlled_publication_result_reviewed', false],
            ['comparator_candidate_controlled_publication_result_reviewed', true],
            ['c158_controlled_output_publication_result_review_only', false],
            ['c158_not_free_publication', false],
            ['result_review_confirmed', false],
            ['temporary_negative_artifact_cleanup_confirmed', false],
        ];
    }

    public function test_c158_operator_rejects_publication_or_plan_confirm_mutation_from_result_review(): void
    {
        $published = $this->mutateResultReviewAndExecute(function (array $review): array {
            $review['weekly_swing_watchlist_official_output_published'] = true;
            return $review;
        }, 'published');
        $publicationAllowed = $this->mutateResultReviewAndExecute(function (array $review): array {
            $review['weekly_swing_watchlist_publication_allowed'] = true;
            return $review;
        }, 'publication-allowed');
        $planConfirm = $this->mutateResultReviewAndExecute(function (array $review): array {
            $review['plan_confirm_mutated'] = true;
            return $review;
        }, 'plan-confirm');

        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_FREE_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED', $published['status']);
        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_FREE_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED', $publicationAllowed['status']);
        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_FREE_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED', $planConfirm['status']);
    }

    public function test_c158_operator_rejects_candidate_scope_change_or_a01_promotion(): void
    {
        $candidate = $this->mutateResultReviewAndExecute(function (array $review): array {
            $review['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $review;
        }, 'candidate-primary');
        $a01 = $this->mutateResultReviewAndExecute(function (array $review): array {
            $review['a01_promoted'] = true;
            return $review;
        }, 'candidate-a01');

        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $candidate['status']);
        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $a01['status']);
    }

    public function test_c158_operator_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c158-operator-negative-remains-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame(self::TEMP_NEGATIVE_STATUS, $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c158_operator_records_source_locks_manifest_checklist_and_no_free_publication(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();
        $manifest = $result['weekly_swing_watchlist_controlled_output_publication_operator_go_no_go_manifest'];
        $checklist = $result['weekly_swing_watchlist_controlled_output_publication_operator_go_no_go_checklist'];

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $this->assertSame(self::C158_RESULT_REVIEW_HASH, $result['expected_c158_result_review_hash']);
        $this->assertSame(self::C158_RESULT_REVIEW_HASH, $result['actual_c158_result_review_hash']);
        $this->assertTrue($result['c158_result_review_hash_match']);
        $this->assertSame(self::C158_RESULT_REVIEW_SHA1, $result['actual_c158_result_review_file_sha1']);
        $this->assertTrue($result['c158_result_review_file_sha1_match']);
        $this->assertTrue($result['c158_result_review_convert_from_json_pass']);
        $this->assertSame(self::NEXT_FINALIZATION, $result['next_concrete_controlled_output_publication_step_decision']['next_recommendation']);

        foreach ([
            'source_artifact_locks',
            'c158_result_review_lock_validation_summary',
            'c158_controlled_output_publication_result_review_carry_forward_summary',
            'controlled_publication_publication_guard_summary',
            'candidate_scope_freeze_summary',
            'operator_decision_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'c158_operator_go_no_go_decision',
            'next_concrete_controlled_output_publication_step_decision',
            'weekly_swing_watchlist_controlled_output_publication_operator_go_no_go_manifest',
            'weekly_swing_watchlist_controlled_output_publication_operator_go_no_go_checklist',
            'c158_candidate_controlled_publication_operator_go_no_go_scorecard',
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
        $this->assertFalse($manifest['operator_go_no_go_used_for_publication']);
        $this->assertFalse($manifest['operator_go_no_go_used_for_plan_confirm_mutation']);
        $this->assertTrue($checklist['operator_go_no_go_reviewed']);
        $this->assertTrue($checklist['artifact_only']);
        $this->assertFalse($checklist['weekly_swing_stock_recommendation_free_published_in_c158_operator_review']);
    }

    public function test_c158_operator_keeps_e02_primary_b01_backup_a01_comparator_and_publication_flags_false(): void
    {
        $result = $this->runService();
        $scorecard = $result['c158_candidate_controlled_publication_operator_go_no_go_scorecard'];

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $result['primary_candidate_code']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $result['backup_candidate_code']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $result['comparator_candidate_code']);
        $this->assertTrue($result['primary_candidate_ready_for_controlled_output_publication_go_decision_finalization_review']);
        $this->assertTrue($result['backup_candidate_ready_for_controlled_output_publication_go_decision_finalization_review']);
        $this->assertFalse($result['comparator_candidate_ready_for_controlled_output_publication_go_decision_finalization_review']);
        $this->assertTrue($result['a01_remains_comparator_only']);
        $this->assertTrue($scorecard[0]['ready_for_go_decision_finalization_review']);
        $this->assertTrue($scorecard[1]['ready_for_go_decision_finalization_review']);
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
        }
    }

    public function test_c158_operator_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $first = $this->runService(['createdAt' => '2026-07-15T00:00:00+00:00']);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c158-controlled-output-publication-operator-go-no-go-review-second.json';
        $this->tmpFiles[] = $secondOutput;
        $second = $this->runService([
            'output' => $secondOutput,
            'createdAt' => '2026-07-15T00:00:00+00:00',
        ]);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
    }

    public function test_c158_operator_does_not_mutate_source_artifact_or_config_defaults(): void
    {
        $beforeResultReview = strtoupper(sha1((string) file_get_contents(self::C158_RESULT_REVIEW_ARTIFACT)));
        $beforeConfig = (string) file_get_contents('config/watchlist.php');

        $this->runService();

        $this->assertSame($beforeResultReview, strtoupper(sha1((string) file_get_contents(self::C158_RESULT_REVIEW_ARTIFACT))));
        $this->assertSame($beforeConfig, (string) file_get_contents('config/watchlist.php'));
    }

    private function runService(array $options = []): array
    {
        $service = new WatchlistBacktestC158WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationOperatorGoNoGoReviewService();

        return $service->execute(
            (string) ($options['c158ResultReviewArtifact'] ?? self::C158_RESULT_REVIEW_ARTIFACT),
            (string) ($options['expectedC158ResultReviewHash'] ?? self::C158_RESULT_REVIEW_HASH),
            (string) ($options['expectedC158ResultReviewFileSha1'] ?? self::C158_RESULT_REVIEW_SHA1),
            (string) ($options['output'] ?? $this->output),
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'operator_decision_confirmed' => (bool) ($options['operatorDecisionConfirmed'] ?? true),
                'operator_decision' => (string) ($options['operatorDecision'] ?? 'GO'),
                'decision_reason' => (string) ($options['decisionReason'] ?? 'Operator approves C158 controlled output publication result for same-topic go decision finalization.'),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C158_OPERATOR_APPROVED_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_GO'),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-15T00:00:00+00:00'),
            ]
        );
    }

    private function mutateResultReviewAndExecute(callable $mutator, string $name): array
    {
        $review = json_decode((string) file_get_contents(self::C158_RESULT_REVIEW_ARTIFACT), true);
        $review = $mutator(is_array($review) ? $review : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c158-operator-result-review-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($review, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'c158ResultReviewArtifact' => $path,
            'expectedC158ResultReviewHash' => (string) ($review['artifact_hash'] ?? ''),
            'expectedC158ResultReviewFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function setValueAt(array &$source, array $path, $value): void
    {
        $current = &$source;
        foreach ($path as $index => $segment) {
            if ($index === count($path) - 1) {
                $current[$segment] = $value;
                return;
            }
            if (! isset($current[$segment]) || ! is_array($current[$segment])) {
                $current[$segment] = [];
            }
            $current = &$current[$segment];
        }
    }

    private function readOutput(): array
    {
        $decoded = json_decode((string) file_get_contents($this->output), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function cleanupC158TemporaryArtifacts(): void
    {
        foreach ((array) glob('storage/app/watchlist/backtest/c158-*test.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/.tmp-c158*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/output/.tmp-c158*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}

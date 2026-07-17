<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC159WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationPostPublicationObservationOperatorGoNoGoReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC159WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationPostPublicationObservationOperatorGoNoGoReviewTest extends TestCase
{
    private const C159_RESULT_REVIEW_ARTIFACT = 'storage/app/watchlist/backtest/c159-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-post-publication-observation-result-review.json';
    private const C159_RESULT_REVIEW_HASH = 'bdd708cbe69713e100daa869388eca188eecc2c2';
    private const C159_RESULT_REVIEW_SHA1 = '26546D7BBD9525582D61A90A383823F508CF3E54';
    private const GO_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP_READY_FOR_POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW';
    private const NO_GO_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW_COMPLETED_NO_GO_POST_PUBLICATION_OBSERVATION_PROGRESSION_STOPPED';
    private const HOLD_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW_COMPLETED_HOLD_POST_PUBLICATION_OBSERVATION_PROGRESSION_DEFERRED';
    private const NEXT_FINALIZATION = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW';

    private string $output;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c159-post-publication-observation-operator-go-no-go-review.json';
        $this->cleanupTemporaryArtifacts();
        @unlink($this->output);
    }

    protected function tearDown(): void
    {
        @unlink($this->output);
        foreach ($this->tmpFiles as $path) {
            @unlink($path);
        }
        $this->cleanupTemporaryArtifacts();
        parent::tearDown();
    }

    public function test_c159_operator_go_passes_and_keeps_same_topic_finalization_next(): void
    {
        $result = $this->runService();

        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW', $result['run_code']);
        $this->assertSame('PR-53 / C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW', $result['phase_label']);
        $this->assertSame('C159_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION', $result['topic_code']);
        $this->assertSame('POST_PUBLICATION_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW', $result['topic_stage']);
        $this->assertSame(self::GO_STATUS, $result['status']);
        $this->assertSame(self::GO_STATUS, $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_production_live_runtime_controlled_output_publication_post_publication_observation_operator_go_no_go_review_pass']);
        $this->assertTrue($result['production_live_runtime_controlled_output_publication_post_publication_observation_operator_go_no_go_review_pass']);
        $this->assertTrue($result['operator_decision_recorded']);
        $this->assertSame('GO', $result['operator_decision']);
        $this->assertSame('GO', $result['operator_go_decision']);
        $this->assertTrue($result['operator_decision_confirmed']);
        $this->assertTrue($result['ready_for_weekly_swing_watchlist_controlled_output_publication_post_publication_observation_go_decision_finalization_review']);
        $this->assertTrue($result['production_live_runtime_controlled_output_publication_post_publication_observation_go_decision_finalization_review_allowed_next']);
        $this->assertTrue($result['c159_result_review_lock_valid']);
        $this->assertTrue($result['c159_post_publication_observation_result_review_valid']);
        $this->assertSame(self::NEXT_FINALIZATION, $result['next_step_recommendation']);
        $this->assertFileExists($this->output);
    }

    public function test_c159_operator_records_no_go_without_opening_finalization(): void
    {
        $result = $this->runService([
            'operatorDecision' => 'NO_GO',
            'decisionReason' => 'Operator stops C159 observation progression after result review.',
        ]);

        $this->assertSame(self::NO_GO_STATUS, $result['status']);
        $this->assertSame('NO_GO', $result['operator_decision']);
        $this->assertTrue($result['operator_no_go_decision']);
        $this->assertFalse($result['operator_hold_decision']);
        $this->assertFalse($result['ready_for_weekly_swing_watchlist_controlled_output_publication_post_publication_observation_go_decision_finalization_review']);
        $this->assertFalse($result['production_live_runtime_controlled_output_publication_post_publication_observation_go_decision_finalization_review_allowed_next']);
        $this->assertTrue($result['post_publication_observation_stopped_no_go']);
        $this->assertSame('C159_NO_GO_CLOSE_POST_PUBLICATION_OBSERVATION', $result['next_step_recommendation']);
        $this->assertFalse($result['weekly_swing_watchlist_publication_allowed']);
    }

    public function test_c159_operator_records_hold_without_opening_finalization(): void
    {
        $result = $this->runService([
            'operatorDecision' => 'HOLD',
            'decisionReason' => 'Operator defers C159 observation finalization until a scheduled review window.',
        ]);

        $this->assertSame(self::HOLD_STATUS, $result['status']);
        $this->assertSame('HOLD', $result['operator_decision']);
        $this->assertFalse($result['operator_no_go_decision']);
        $this->assertTrue($result['operator_hold_decision']);
        $this->assertFalse($result['ready_for_weekly_swing_watchlist_controlled_output_publication_post_publication_observation_go_decision_finalization_review']);
        $this->assertTrue($result['post_publication_observation_deferred_hold']);
        $this->assertSame('C159_HOLD_KEEP_POST_PUBLICATION_OBSERVATION_LOCKED_UNTIL_OPERATOR_WINDOW', $result['next_step_recommendation']);
        $this->assertFalse($result['plan_confirm_mutated']);
    }

    public function test_c159_operator_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->runService(['operatorApproved' => false]);
        $missingReference = $this->runService(['approvalReference' => '']);

        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingOperator['status']);
        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingReference['status']);
    }

    public function test_c159_operator_rejects_invalid_unconfirmed_or_unexplained_decision(): void
    {
        $invalid = $this->runService(['operatorDecision' => 'MAYBE']);
        $unconfirmed = $this->runService(['operatorDecisionConfirmed' => false]);
        $missingReason = $this->runService(['decisionReason' => '']);

        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_DECISION_INVALID', $invalid['status']);
        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_DECISION_NOT_CONFIRMED', $unconfirmed['status']);
        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_DECISION_REASON_MISSING', $missingReason['status']);
    }

    public function test_c159_operator_rejects_result_review_lock_mismatch(): void
    {
        $missing = $this->runService([
            'c159ResultReviewArtifact' => 'storage/app/watchlist/backtest/.tmp-c159-operator-source-missing.json',
            'expectedC159ResultReviewHash' => 'missing',
            'expectedC159ResultReviewFileSha1' => 'missing',
        ]);
        $hashMismatch = $this->runService(['expectedC159ResultReviewHash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedC159ResultReviewFileSha1' => 'BADSHA1']);

        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C159_RESULT_REVIEW_ARTIFACT_LOCK_MISMATCH', $missing['status']);
        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C159_RESULT_REVIEW_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C159_RESULT_REVIEW_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
    }

    public function test_c159_operator_rejects_result_review_status_phase_or_next_mismatch(): void
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

        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C159_RESULT_REVIEW_STATUS_MISMATCH', $status['status']);
        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C159_RESULT_REVIEW_PHASE_LABEL_MISMATCH', $phase['status']);
        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C159_RESULT_REVIEW_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
    }

    public function test_c159_operator_rejects_convert_from_json_duplicate_top_level_keys(): void
    {
        $raw = (string) file_get_contents(self::C159_RESULT_REVIEW_ARTIFACT);
        $path = 'storage/app/watchlist/backtest/.tmp-c159-operator-source-duplicate-key.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, preg_replace('/\{/', "{\"Run_Code\":\"DUPLICATE_CASE_KEY\",", $raw, 1));

        $result = $this->runService([
            'c159ResultReviewArtifact' => $path,
            'expectedC159ResultReviewFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);

        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C159_RESULT_REVIEW_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION', $result['status']);
        $this->assertFalse($result['c159_result_review_convert_from_json_pass']);
    }

    /**
     * @dataProvider c159ResultReviewIncompleteProvider
     */
    public function test_c159_operator_rejects_incomplete_result_review_evidence(string $field, $value): void
    {
        $result = $this->mutateResultReviewAndExecute(function (array $review) use ($field, $value): array {
            $this->setValueAt($review, explode('.', $field), $value);
            return $review;
        }, 'incomplete-'.str_replace('.', '-', $field));

        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C159_RESULT_REVIEW_INCOMPLETE', $result['status'], $field);
    }

    public function c159ResultReviewIncompleteProvider(): array
    {
        return [
            ['weekly_swing_watchlist_production_live_runtime_controlled_output_publication_post_publication_observation_result_review_pass', false],
            ['ready_for_weekly_swing_watchlist_controlled_output_publication_post_publication_observation_operator_go_no_go_review', false],
            ['controlled_publication_lock_valid', false],
            ['controlled_publication_integrity_valid', false],
            ['primary_candidate_observation_result_reviewed', false],
            ['backup_candidate_observation_result_reviewed', false],
            ['comparator_candidate_observation_result_reviewed', true],
            ['c159_post_publication_observation_result_review_only', false],
            ['c159_not_free_publication', false],
            ['post_publication_observation_result_review_confirmed', false],
            ['temporary_negative_artifact_cleanup_confirmed', false],
        ];
    }

    public function test_c159_operator_rejects_publication_or_plan_confirm_mutation_from_result_review(): void
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

        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_FREE_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED', $published['status']);
        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_FREE_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED', $publicationAllowed['status']);
        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_FREE_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED', $planConfirm['status']);
    }

    public function test_c159_operator_rejects_candidate_scope_change_or_a01_promotion(): void
    {
        $candidate = $this->mutateResultReviewAndExecute(function (array $review): array {
            $review['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $review;
        }, 'candidate-primary');
        $a01 = $this->mutateResultReviewAndExecute(function (array $review): array {
            $review['a01_promoted'] = true;
            return $review;
        }, 'candidate-a01');

        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $candidate['status']);
        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $a01['status']);
    }

    public function test_c159_operator_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c159-operator-go-no-go-negative-remains-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS', $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c159_operator_records_sections_manifest_checklist_and_no_free_publication(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();
        $manifest = $result['weekly_swing_watchlist_controlled_output_publication_post_publication_observation_operator_go_no_go_manifest'];
        $checklist = $result['weekly_swing_watchlist_controlled_output_publication_post_publication_observation_operator_go_no_go_checklist'];

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $this->assertSame(self::C159_RESULT_REVIEW_HASH, $result['expected_c159_result_review_hash']);
        $this->assertSame(self::C159_RESULT_REVIEW_HASH, $result['actual_c159_result_review_hash']);
        $this->assertTrue($result['c159_result_review_hash_match']);
        $this->assertSame(self::C159_RESULT_REVIEW_SHA1, $result['actual_c159_result_review_file_sha1']);
        $this->assertTrue($result['c159_result_review_file_sha1_match']);
        $this->assertTrue($result['c159_result_review_convert_from_json_pass']);
        $this->assertSame(self::NEXT_FINALIZATION, $result['next_concrete_post_publication_observation_step_decision']['next_recommendation']);

        foreach ([
            'source_artifact_locks',
            'c159_result_review_lock_validation_summary',
            'c159_post_publication_observation_result_review_carry_forward_summary',
            'controlled_publication_publication_guard_summary',
            'candidate_scope_freeze_summary',
            'operator_decision_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'c159_operator_go_no_go_decision',
            'next_concrete_post_publication_observation_step_decision',
            'weekly_swing_watchlist_controlled_output_publication_post_publication_observation_operator_go_no_go_manifest',
            'weekly_swing_watchlist_controlled_output_publication_post_publication_observation_operator_go_no_go_checklist',
            'c159_candidate_post_publication_observation_operator_go_no_go_scorecard',
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
        $this->assertFalse($checklist['weekly_swing_stock_recommendation_free_published_in_c159_operator_review']);
    }

    public function test_c159_operator_keeps_e02_primary_b01_backup_a01_comparator_and_publication_flags_false(): void
    {
        $result = $this->runService();
        $scorecard = $result['c159_candidate_post_publication_observation_operator_go_no_go_scorecard'];

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $result['primary_candidate_code']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $result['backup_candidate_code']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $result['comparator_candidate_code']);
        $this->assertTrue($result['primary_candidate_ready_for_post_publication_observation_go_decision_finalization_review']);
        $this->assertTrue($result['backup_candidate_ready_for_post_publication_observation_go_decision_finalization_review']);
        $this->assertFalse($result['comparator_candidate_ready_for_post_publication_observation_go_decision_finalization_review']);
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

    public function test_c159_operator_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $first = $this->runService(['createdAt' => '2026-07-15T00:00:00+00:00']);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c159-post-publication-observation-operator-go-no-go-review-second.json';
        $this->tmpFiles[] = $secondOutput;
        $second = $this->runService([
            'output' => $secondOutput,
            'createdAt' => '2026-07-15T00:00:00+00:00',
        ]);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
    }

    public function test_c159_operator_does_not_mutate_source_artifact_or_config_defaults(): void
    {
        $beforeResultReview = strtoupper(sha1((string) file_get_contents(self::C159_RESULT_REVIEW_ARTIFACT)));
        $beforeConfig = (string) file_get_contents('config/watchlist.php');

        $this->runService();

        $this->assertSame($beforeResultReview, strtoupper(sha1((string) file_get_contents(self::C159_RESULT_REVIEW_ARTIFACT))));
        $this->assertSame($beforeConfig, (string) file_get_contents('config/watchlist.php'));
    }

    private function runService(array $options = []): array
    {
        $service = new WatchlistBacktestC159WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationPostPublicationObservationOperatorGoNoGoReviewService();

        return $service->execute(
            (string) ($options['c159ResultReviewArtifact'] ?? self::C159_RESULT_REVIEW_ARTIFACT),
            (string) ($options['expectedC159ResultReviewHash'] ?? self::C159_RESULT_REVIEW_HASH),
            (string) ($options['expectedC159ResultReviewFileSha1'] ?? self::C159_RESULT_REVIEW_SHA1),
            (string) ($options['output'] ?? $this->output),
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'operator_decision_confirmed' => (bool) ($options['operatorDecisionConfirmed'] ?? true),
                'operator_decision' => (string) ($options['operatorDecision'] ?? 'GO'),
                'decision_reason' => (string) ($options['decisionReason'] ?? 'Operator approves C159 post-publication observation result for same-topic go decision finalization.'),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C159_OPERATOR_APPROVED_POST_PUBLICATION_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW_GO'),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-15T00:00:00+00:00'),
            ]
        );
    }

    private function mutateResultReviewAndExecute(callable $mutator, string $name): array
    {
        $review = json_decode((string) file_get_contents(self::C159_RESULT_REVIEW_ARTIFACT), true);
        $review = $mutator(is_array($review) ? $review : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c159-operator-result-review-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($review, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'c159ResultReviewArtifact' => $path,
            'expectedC159ResultReviewHash' => (string) ($review['artifact_hash'] ?? ''),
            'expectedC159ResultReviewFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
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

    private function cleanupTemporaryArtifacts(): void
    {
        foreach ((array) glob('storage/app/watchlist/backtest/c159-*operator-go-no-go*-test.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/.tmp-c159-operator*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/.tmp-c159-post-publication-observation-operator-go-no-go-review*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}

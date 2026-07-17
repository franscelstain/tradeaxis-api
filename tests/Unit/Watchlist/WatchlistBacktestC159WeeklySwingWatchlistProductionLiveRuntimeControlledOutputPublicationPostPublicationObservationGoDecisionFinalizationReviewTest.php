<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC159WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationPostPublicationObservationGoDecisionFinalizationReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC159WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationPostPublicationObservationGoDecisionFinalizationReviewTest extends TestCase
{
    private const C159_OPERATOR_ARTIFACT = 'storage/app/watchlist/backtest/c159-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-post-publication-observation-operator-go-no-go-review.json';
    private const C159_OPERATOR_HASH = 'e6c1daae25cfd45950c9c7849b1277cc2099e557';
    private const C159_OPERATOR_SHA1 = 'DEA4167C95413F45DA8E7F6F16816BD178987F78';
    private const PASS_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW_PASSED_GO_FINALIZED_POST_PUBLICATION_OBSERVATION_CLOSED_READY_FOR_PLAN_CONFIRM_BOUNDARY_REVIEW_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const GO_FINALIZATION_MISSING_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_GO_DECISION_FINALIZATION_CONFIRMATION_MISSING';
    private const OBSERVATION_FINALIZATION_MISSING_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_POST_PUBLICATION_OBSERVATION_FINALIZATION_CONFIRMATION_MISSING';
    private const FREE_PUBLICATION_LOCK_MISSING_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING';
    private const PLAN_CONFIRM_MISSING_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING';
    private const TEMP_NEGATIVE_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const LOCK_MISMATCH_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C159_OPERATOR_GO_NO_GO_ARTIFACT_LOCK_MISMATCH';
    private const SHA1_MISMATCH_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C159_OPERATOR_GO_NO_GO_FILE_SHA1_LOCK_MISMATCH';
    private const NEXT_C160 = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_BOUNDARY_REVIEW';

    private string $output;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c159-post-publication-observation-go-decision-finalization-review.json';
        $this->cleanupC159TemporaryArtifacts();
        @unlink($this->output);
    }

    protected function tearDown(): void
    {
        @unlink($this->output);
        foreach ($this->tmpFiles as $path) {
            @unlink($path);
        }
        $this->cleanupC159TemporaryArtifacts();
        parent::tearDown();
    }

    public function test_c159_go_decision_finalization_passes_closes_topic_and_advances_to_c160_plan_confirm_boundary(): void
    {
        $result = $this->runService();

        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW', $result['run_code']);
        $this->assertSame('PR-54 / C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW', $result['phase_label']);
        $this->assertSame('C159_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION', $result['topic_code']);
        $this->assertSame('POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW', $result['topic_stage']);
        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame(self::PASS_STATUS, $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_production_live_runtime_controlled_output_publication_post_publication_observation_go_decision_finalization_review_pass']);
        $this->assertTrue($result['production_live_runtime_controlled_output_publication_post_publication_observation_go_decision_finalization_review_pass']);
        $this->assertSame('GO', $result['operator_decision']);
        $this->assertSame('GO', $result['operator_go_decision']);
        $this->assertTrue($result['operator_go_decision_confirmed']);
        $this->assertTrue($result['go_decision_finalized']);
        $this->assertTrue($result['go_decision_finalization_confirmed']);
        $this->assertTrue($result['post_publication_observation_finalization_confirmed']);
        $this->assertTrue($result['post_publication_observation_closed']);
        $this->assertTrue($result['free_publication_locked_confirmed']);
        $this->assertTrue($result['plan_confirm_unchanged_confirmed']);
        $this->assertTrue($result['ready_for_weekly_swing_watchlist_plan_confirm_boundary_review']);
        $this->assertTrue($result['production_live_runtime_plan_confirm_boundary_review_allowed_next']);
        $this->assertSame(self::NEXT_C160, $result['next_step_recommendation']);
        $this->assertTrue($result['next_plan_confirm_boundary_decision']['topic_number_advances_after_c159_finalization']);
        $this->assertTrue($result['next_plan_confirm_boundary_decision']['same_topic_c159_complete']);
        $this->assertFileExists($this->output);
    }

    public function test_c159_finalization_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->runService(['operatorApproved' => false]);
        $missingReference = $this->runService(['approvalReference' => '']);

        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingOperator['status']);
        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingReference['status']);
    }

    public function test_c159_finalization_rejects_missing_required_confirmations(): void
    {
        $go = $this->runService(['goDecisionFinalizationConfirmed' => false]);
        $observation = $this->runService(['postPublicationObservationFinalizationConfirmed' => false]);
        $freeLock = $this->runService(['freePublicationLockedConfirmed' => false]);
        $plan = $this->runService(['planConfirmUnchangedConfirmed' => false]);

        $this->assertSame(self::GO_FINALIZATION_MISSING_STATUS, $go['status']);
        $this->assertSame(self::OBSERVATION_FINALIZATION_MISSING_STATUS, $observation['status']);
        $this->assertSame(self::FREE_PUBLICATION_LOCK_MISSING_STATUS, $freeLock['status']);
        $this->assertSame(self::PLAN_CONFIRM_MISSING_STATUS, $plan['status']);
    }

    public function test_c159_finalization_rejects_missing_or_mismatched_operator_artifact_lock(): void
    {
        $missing = $this->runService([
            'c159OperatorArtifact' => 'storage/app/watchlist/backtest/.tmp-c159-finalization-source-missing.json',
            'expectedC159OperatorHash' => 'missing',
            'expectedC159OperatorFileSha1' => 'missing',
        ]);
        $hashMismatch = $this->runService(['expectedC159OperatorHash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedC159OperatorFileSha1' => 'BADSHA1']);

        $this->assertSame(self::LOCK_MISMATCH_STATUS, $missing['status']);
        $this->assertSame(self::LOCK_MISMATCH_STATUS, $hashMismatch['status']);
        $this->assertSame(self::SHA1_MISMATCH_STATUS, $shaMismatch['status']);
    }

    public function test_c159_finalization_rejects_operator_status_phase_or_next_recommendation_mismatch(): void
    {
        $status = $this->mutateOperatorAndExecute(function (array $operator): array {
            $operator['status'] = 'BROKEN_STATUS';
            return $operator;
        }, 'status-broken');
        $phase = $this->mutateOperatorAndExecute(function (array $operator): array {
            $operator['phase_label'] = 'BROKEN_PHASE';
            return $operator;
        }, 'phase-broken');
        $next = $this->mutateOperatorAndExecute(function (array $operator): array {
            $operator['next_step_recommendation'] = 'BROKEN_NEXT';
            $operator['next_concrete_post_publication_observation_step_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $operator['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $operator;
        }, 'next-broken');

        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C159_OPERATOR_GO_NO_GO_STATUS_MISMATCH', $status['status']);
        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C159_OPERATOR_GO_NO_GO_PHASE_LABEL_MISMATCH', $phase['status']);
        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C159_OPERATOR_GO_NO_GO_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
    }

    public function test_c159_finalization_rejects_operator_convert_from_json_duplicate_top_level_keys(): void
    {
        $raw = (string) file_get_contents(self::C159_OPERATOR_ARTIFACT);
        $path = 'storage/app/watchlist/backtest/.tmp-c159-finalization-source-operator-duplicate-key.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, preg_replace('/\{/', "{\"Run_Code\":\"DUPLICATE_CASE_KEY\",", $raw, 1));

        $result = $this->runService([
            'c159OperatorArtifact' => $path,
            'expectedC159OperatorHash' => self::C159_OPERATOR_HASH,
            'expectedC159OperatorFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);

        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C159_OPERATOR_GO_NO_GO_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION', $result['status']);
        $this->assertFalse($result['c159_operator_go_no_go_convert_from_json_pass']);
    }

    /**
     * @dataProvider c159OperatorGoMismatchProvider
     */
    public function test_c159_finalization_rejects_operator_go_state_mismatch(string $field, $value): void
    {
        $result = $this->mutateOperatorAndExecute(function (array $operator) use ($field, $value): array {
            $this->setValueAt($operator, explode('.', $field), $value);
            return $operator;
        }, 'operator-go-'.str_replace('.', '-', $field));

        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C159_OPERATOR_GO_INVALID', $result['status'], $field);
    }

    public function c159OperatorGoMismatchProvider(): array
    {
        return [
            ['weekly_swing_watchlist_production_live_runtime_controlled_output_publication_post_publication_observation_operator_go_no_go_review_pass', false],
            ['operator_decision', 'NO_GO'],
            ['operator_go_decision', 'NO_GO'],
            ['operator_decision_confirmed', false],
            ['operator_decision_reason', ''],
            ['ready_for_weekly_swing_watchlist_controlled_output_publication_post_publication_observation_go_decision_finalization_review', false],
            ['production_live_runtime_controlled_output_publication_post_publication_observation_go_decision_finalization_review_allowed_next', false],
            ['controlled_output_publication_post_publication_observation_operator_go_no_go_manifest_created', false],
            ['weekly_swing_watchlist_controlled_output_publication_post_publication_observation_result_reviewed', false],
            ['weekly_swing_watchlist_controlled_output_publication_observed', false],
            ['weekly_swing_watchlist_controlled_output_publication_observation_stable', false],
            ['controlled_publication_lock_valid', false],
            ['controlled_publication_integrity_valid', false],
            ['primary_candidate_ready_for_post_publication_observation_go_decision_finalization_review', false],
            ['backup_candidate_ready_for_post_publication_observation_go_decision_finalization_review', false],
            ['comparator_candidate_ready_for_post_publication_observation_go_decision_finalization_review', true],
            ['c159_operator_go_no_go_decision.operator_decision', 'HOLD'],
            ['c159_operator_go_no_go_decision.ready_for_go_decision_finalization_review', false],
            ['weekly_swing_watchlist_controlled_output_publication_post_publication_observation_operator_go_no_go_manifest.operator_decision', 'NO_GO'],
            ['weekly_swing_watchlist_controlled_output_publication_post_publication_observation_operator_go_no_go_manifest.operator_go_no_go_used_for_publication', true],
            ['weekly_swing_watchlist_controlled_output_publication_post_publication_observation_operator_go_no_go_checklist.artifact_only', false],
        ];
    }

    public function test_c159_finalization_rejects_free_publication_or_plan_confirm_mutation_from_operator_artifact(): void
    {
        $published = $this->mutateOperatorAndExecute(function (array $operator): array {
            $operator['weekly_swing_watchlist_official_output_published'] = true;
            return $operator;
        }, 'published');
        $publicationAllowed = $this->mutateOperatorAndExecute(function (array $operator): array {
            $operator['weekly_swing_watchlist_publication_allowed'] = true;
            return $operator;
        }, 'publication-allowed');
        $planConfirm = $this->mutateOperatorAndExecute(function (array $operator): array {
            $operator['plan_confirm_mutated'] = true;
            return $operator;
        }, 'plan-confirm');

        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_FREE_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED', $published['status']);
        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_FREE_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED', $publicationAllowed['status']);
        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_FREE_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED', $planConfirm['status']);
    }

    public function test_c159_finalization_rejects_candidate_scope_change_or_a01_promotion(): void
    {
        $candidate = $this->mutateOperatorAndExecute(function (array $operator): array {
            $operator['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $operator;
        }, 'candidate-primary');
        $a01 = $this->mutateOperatorAndExecute(function (array $operator): array {
            $operator['a01_promoted'] = true;
            return $operator;
        }, 'candidate-a01');

        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $candidate['status']);
        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $a01['status']);
    }

    public function test_c159_finalization_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c159-finalization-negative-remains-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame(self::TEMP_NEGATIVE_STATUS, $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c159_finalization_records_source_locks_manifest_checklist_and_no_free_publication(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();
        $manifest = $result['weekly_swing_watchlist_controlled_output_publication_post_publication_observation_go_decision_finalization_manifest'];
        $checklist = $result['weekly_swing_watchlist_controlled_output_publication_post_publication_observation_go_decision_finalization_checklist'];

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $this->assertSame(self::C159_OPERATOR_HASH, $result['expected_c159_operator_go_no_go_hash']);
        $this->assertSame(self::C159_OPERATOR_HASH, $result['actual_c159_operator_go_no_go_hash']);
        $this->assertTrue($result['c159_operator_go_no_go_hash_match']);
        $this->assertSame(self::C159_OPERATOR_SHA1, $result['expected_c159_operator_go_no_go_file_sha1']);
        $this->assertSame(self::C159_OPERATOR_SHA1, $result['actual_c159_operator_go_no_go_file_sha1']);
        $this->assertTrue($result['c159_operator_go_no_go_file_sha1_match']);
        $this->assertTrue($result['c159_operator_go_no_go_convert_from_json_pass']);
        $this->assertSame(self::NEXT_C160, $result['next_plan_confirm_boundary_decision']['next_recommendation']);

        foreach ([
            'source_artifact_locks',
            'c159_operator_go_no_go_lock_validation_summary',
            'c159_operator_go_no_go_carry_forward_summary',
            'post_publication_observation_finalization_guard_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'c159_go_decision_finalization_decision',
            'next_plan_confirm_boundary_decision',
            'weekly_swing_watchlist_controlled_output_publication_post_publication_observation_go_decision_finalization_manifest',
            'weekly_swing_watchlist_controlled_output_publication_post_publication_observation_go_decision_finalization_checklist',
            'c159_candidate_post_publication_observation_go_decision_finalization_scorecard',
            'publication_plan_confirm_safety_summary',
            'documentation_hygiene_guard_summary',
            'progress_summary',
            'planned_next_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }

        $this->assertTrue($manifest['go_decision_finalization_artifact_only']);
        $this->assertSame('GO', $manifest['operator_go_decision']);
        $this->assertTrue($manifest['go_decision_finalized']);
        $this->assertTrue($manifest['post_publication_observation_closed']);
        $this->assertTrue($manifest['ready_for_plan_confirm_boundary_review']);
        $this->assertFalse($manifest['official_output_published']);
        $this->assertFalse($manifest['free_publication_allowed']);
        $this->assertFalse($manifest['go_decision_finalization_used_for_plan_confirm_mutation']);
        $this->assertFalse($manifest['go_decision_finalization_used_for_free_publication']);
        $this->assertTrue($checklist['go_decision_finalization_reviewed']);
        $this->assertTrue($checklist['artifact_only']);
        $this->assertFalse($checklist['weekly_swing_stock_recommendation_free_published_in_c159_finalization']);
    }

    public function test_c159_finalization_keeps_e02_primary_b01_backup_a01_comparator_and_publication_flags_false(): void
    {
        $result = $this->runService();
        $scorecard = $result['c159_candidate_post_publication_observation_go_decision_finalization_scorecard'];

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $result['primary_candidate_code']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $result['backup_candidate_code']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $result['comparator_candidate_code']);
        $this->assertTrue($result['primary_candidate_ready_for_plan_confirm_boundary_review']);
        $this->assertTrue($result['backup_candidate_ready_for_plan_confirm_boundary_review']);
        $this->assertFalse($result['comparator_candidate_ready_for_plan_confirm_boundary_review']);
        $this->assertTrue($result['a01_remains_comparator_only']);
        $this->assertTrue($scorecard[0]['primary_candidate_ready_for_plan_confirm_boundary_review']);
        $this->assertTrue($scorecard[1]['backup_candidate_ready_for_plan_confirm_boundary_review']);
        $this->assertFalse($scorecard[2]['ready_for_plan_confirm_boundary_review']);

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

    public function test_c159_finalization_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $first = $this->runService(['createdAt' => '2026-07-16T00:00:00+00:00']);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c159-post-publication-observation-go-decision-finalization-review-second.json';
        $this->tmpFiles[] = $secondOutput;
        $second = $this->runService([
            'output' => $secondOutput,
            'createdAt' => '2026-07-16T00:00:00+00:00',
        ]);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
    }

    public function test_c159_finalization_does_not_mutate_operator_artifact_or_config_defaults(): void
    {
        $beforeOperator = strtoupper(sha1((string) file_get_contents(self::C159_OPERATOR_ARTIFACT)));
        $beforeConfig = (string) file_get_contents('config/watchlist.php');

        $this->runService();

        $this->assertSame($beforeOperator, strtoupper(sha1((string) file_get_contents(self::C159_OPERATOR_ARTIFACT))));
        $this->assertSame($beforeConfig, (string) file_get_contents('config/watchlist.php'));
    }

    private function runService(array $options = []): array
    {
        $service = new WatchlistBacktestC159WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationPostPublicationObservationGoDecisionFinalizationReviewService();

        return $service->execute(
            (string) ($options['c159OperatorArtifact'] ?? self::C159_OPERATOR_ARTIFACT),
            (string) ($options['expectedC159OperatorHash'] ?? self::C159_OPERATOR_HASH),
            (string) ($options['expectedC159OperatorFileSha1'] ?? self::C159_OPERATOR_SHA1),
            (string) ($options['output'] ?? $this->output),
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'go_decision_finalization_confirmed' => (bool) ($options['goDecisionFinalizationConfirmed'] ?? true),
                'post_publication_observation_finalization_confirmed' => (bool) ($options['postPublicationObservationFinalizationConfirmed'] ?? true),
                'free_publication_locked_confirmed' => (bool) ($options['freePublicationLockedConfirmed'] ?? true),
                'plan_confirm_unchanged_confirmed' => (bool) ($options['planConfirmUnchangedConfirmed'] ?? true),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C159_OPERATOR_APPROVED_POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW_ONLY'),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-16T00:00:00+00:00'),
            ]
        );
    }

    private function mutateOperatorAndExecute(callable $mutator, string $name): array
    {
        $operator = json_decode((string) file_get_contents(self::C159_OPERATOR_ARTIFACT), true);
        $operator = $mutator(is_array($operator) ? $operator : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c159-finalization-source-operator-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($operator, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'c159OperatorArtifact' => $path,
            'expectedC159OperatorHash' => (string) ($operator['artifact_hash'] ?? ''),
            'expectedC159OperatorFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
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

    private function cleanupC159TemporaryArtifacts(): void
    {
        foreach ((array) glob('storage/app/watchlist/backtest/c159-*finalization*-test.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/.tmp-c159-finalization*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/.tmp-c159-post-publication-observation-go-decision-finalization-review*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}

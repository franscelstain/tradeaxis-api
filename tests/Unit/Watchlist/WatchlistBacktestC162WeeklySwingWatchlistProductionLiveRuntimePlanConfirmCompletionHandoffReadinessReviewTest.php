<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC162WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionHandoffReadinessReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC162WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionHandoffReadinessReviewTest extends TestCase
{
    private const C161_FINALIZATION_ARTIFACT = 'storage/app/watchlist/backtest/c161-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-go-decision-finalization-review.json';
    private const C161_FINALIZATION_HASH = '9409df354fc360554d502b4787878c770e806d45';
    private const C161_FINALIZATION_SHA1 = '06441C61A6A4B1F4BFE4C8398CD0BB4ED1C552EF';
    private const PASS_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_PASSED_HANDOFF_READY_PRIMARY_AND_BACKUP_READY_FOR_HANDOFF_FINALIZATION_REVIEW';
    private const NEXT_C162 = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_REVIEW';
    private const APPROVAL_MISSING_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const HANDOFF_READINESS_MISSING_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_REJECTED_HANDOFF_READINESS_CONFIRMATION_MISSING';
    private const C161_TOPIC_COMPLETE_MISSING_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_REJECTED_C161_TOPIC_COMPLETE_CONFIRMATION_MISSING';
    private const PLAN_CONFIRM_COMPLETION_CLOSED_MISSING_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_REJECTED_PLAN_CONFIRM_COMPLETION_CLOSED_CONFIRMATION_MISSING';
    private const PLAN_CONFIRM_UNCHANGED_MISSING_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING';
    private const NO_LIVE_ROLLOUT_MISSING_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_REJECTED_NO_LIVE_PLAN_CONFIRM_ROLLOUT_CONFIRMATION_MISSING';
    private const FREE_PUBLICATION_LOCK_MISSING_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_REJECTED_FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING';
    private const LOCK_MISMATCH_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_REJECTED_C161_FINALIZATION_ARTIFACT_LOCK_MISMATCH';
    private const SHA1_MISMATCH_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_REJECTED_C161_FINALIZATION_FILE_SHA1_LOCK_MISMATCH';
    private const C161_STATE_INVALID_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_REJECTED_C161_FINALIZATION_STATE_INVALID';

    private string $output;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c162-plan-confirm-completion-handoff-readiness-review.json';
        $this->cleanupC162TemporaryArtifacts();
        @unlink($this->output);
    }

    protected function tearDown(): void
    {
        @unlink($this->output);
        foreach ($this->tmpFiles as $path) {
            @unlink($path);
        }
        $this->cleanupC162TemporaryArtifacts();
        parent::tearDown();
    }

    public function test_c162_handoff_readiness_passes_and_advances_to_c162_handoff_finalization(): void
    {
        $result = $this->runService();

        $this->assertSame('C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW', $result['run_code']);
        $this->assertSame('PR-65 / C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW', $result['phase_label']);
        $this->assertSame('C162_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS', $result['topic_code']);
        $this->assertSame('PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW', $result['topic_stage']);
        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame(self::PASS_STATUS, $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_readiness_review_pass']);
        $this->assertTrue($result['production_live_runtime_plan_confirm_completion_handoff_readiness_review_pass']);
        $this->assertTrue($result['handoff_ready']);
        $this->assertTrue($result['handoff_readiness_confirmed']);
        $this->assertSame('HANDOFF_READY_GO', $result['handoff_readiness_go_decision']);
        $this->assertTrue($result['c161_topic_complete_confirmed']);
        $this->assertTrue($result['plan_confirm_completion_closed_confirmed']);
        $this->assertTrue($result['ready_for_weekly_swing_watchlist_plan_confirm_completion_handoff_finalization_review']);
        $this->assertSame(self::NEXT_C162, $result['next_step_recommendation']);
        $this->assertTrue($result['next_plan_confirm_completion_handoff_finalization_decision']['topic_stage_advances_within_c162_handoff_after_readiness']);
        $this->assertTrue($result['next_plan_confirm_completion_handoff_finalization_decision']['c162_handoff_readiness_complete']);
        $this->assertFileExists($this->output);
    }

    public function test_c162_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->runService(['operatorApproved' => false]);
        $missingReference = $this->runService(['approvalReference' => '']);

        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingOperator['status']);
        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingReference['status']);
    }

    public function test_c162_rejects_missing_required_confirmations(): void
    {
        $handoff = $this->runService(['handoffReadinessConfirmed' => false]);
        $topicComplete = $this->runService(['c161TopicCompleteConfirmed' => false]);
        $completionClosed = $this->runService(['planConfirmCompletionClosedConfirmed' => false]);
        $planUnchanged = $this->runService(['planConfirmUnchangedConfirmed' => false]);
        $noRollout = $this->runService(['noLivePlanConfirmRolloutConfirmed' => false]);
        $freeLock = $this->runService(['freePublicationLockedConfirmed' => false]);

        $this->assertSame(self::HANDOFF_READINESS_MISSING_STATUS, $handoff['status']);
        $this->assertSame(self::C161_TOPIC_COMPLETE_MISSING_STATUS, $topicComplete['status']);
        $this->assertSame(self::PLAN_CONFIRM_COMPLETION_CLOSED_MISSING_STATUS, $completionClosed['status']);
        $this->assertSame(self::PLAN_CONFIRM_UNCHANGED_MISSING_STATUS, $planUnchanged['status']);
        $this->assertSame(self::NO_LIVE_ROLLOUT_MISSING_STATUS, $noRollout['status']);
        $this->assertSame(self::FREE_PUBLICATION_LOCK_MISSING_STATUS, $freeLock['status']);
    }

    public function test_c162_rejects_missing_or_mismatched_c161_finalization_lock(): void
    {
        $missing = $this->runService([
            'c161FinalizationArtifact' => 'storage/app/watchlist/backtest/.tmp-c162-source-missing.json',
            'expectedC161FinalizationHash' => 'missing',
            'expectedC161FinalizationFileSha1' => 'missing',
        ]);
        $hashMismatch = $this->runService(['expectedC161FinalizationHash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedC161FinalizationFileSha1' => 'BADSHA1']);

        $this->assertSame(self::LOCK_MISMATCH_STATUS, $missing['status']);
        $this->assertSame(self::LOCK_MISMATCH_STATUS, $hashMismatch['status']);
        $this->assertSame(self::SHA1_MISMATCH_STATUS, $shaMismatch['status']);
    }

    public function test_c162_rejects_c161_finalization_status_phase_or_next_recommendation_mismatch(): void
    {
        $status = $this->mutateFinalizationAndExecute(function (array $finalization): array {
            $finalization['status'] = 'BROKEN_STATUS';
            return $finalization;
        }, 'status-broken');
        $phase = $this->mutateFinalizationAndExecute(function (array $finalization): array {
            $finalization['phase_label'] = 'BROKEN_PHASE';
            return $finalization;
        }, 'phase-broken');
        $next = $this->mutateFinalizationAndExecute(function (array $finalization): array {
            $finalization['next_step_recommendation'] = 'BROKEN_NEXT';
            $finalization['next_plan_confirm_completion_handoff_readiness_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $finalization['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $finalization;
        }, 'next-broken');

        $this->assertSame('C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_REJECTED_C161_FINALIZATION_STATUS_MISMATCH', $status['status']);
        $this->assertSame('C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_REJECTED_C161_FINALIZATION_PHASE_LABEL_MISMATCH', $phase['status']);
        $this->assertSame('C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_REJECTED_C161_FINALIZATION_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
    }

    public function test_c162_rejects_c161_finalization_convert_from_json_duplicate_top_level_keys(): void
    {
        $raw = (string) file_get_contents(self::C161_FINALIZATION_ARTIFACT);
        $path = 'storage/app/watchlist/backtest/.tmp-c162-handoff-source-finalization-duplicate-key.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, preg_replace('/\{/', "{\"Run_Code\":\"DUPLICATE_CASE_KEY\",", $raw, 1));

        $result = $this->runService([
            'c161FinalizationArtifact' => $path,
            'expectedC161FinalizationHash' => self::C161_FINALIZATION_HASH,
            'expectedC161FinalizationFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);

        $this->assertSame('C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_REJECTED_C161_FINALIZATION_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION', $result['status']);
        $this->assertFalse($result['c161_finalization_convert_from_json_pass']);
    }

    /**
     * @dataProvider c161FinalizationStateMismatchProvider
     */
    public function test_c162_rejects_c161_finalization_state_mismatch(string $field, $value): void
    {
        $result = $this->mutateFinalizationAndExecute(function (array $finalization) use ($field, $value): array {
            $this->setValueAt($finalization, explode('.', $field), $value);
            return $finalization;
        }, 'state-'.str_replace('.', '-', $field));

        $this->assertSame(self::C161_STATE_INVALID_STATUS, $result['status'], $field);
    }

    public function c161FinalizationStateMismatchProvider(): array
    {
        return [
            ['weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_go_decision_finalization_review_pass', false],
            ['operator_go_decision', false],
            ['go_decision_finalized', false],
            ['plan_confirm_completion_finalization_confirmed', false],
            ['plan_confirm_completion_closed', false],
            ['ready_for_weekly_swing_watchlist_plan_confirm_completion_handoff_readiness_review', false],
            ['production_live_runtime_plan_confirm_completion_handoff_readiness_review_allowed_next', false],
            ['c161_topic_complete_after_finalization', false],
            ['controlled_completion_lock_valid', false],
            ['controlled_completion_integrity_valid', false],
            ['controlled_completion_record_count', 0],
            ['controlled_completion_hash', ''],
            ['primary_candidate_ready_for_plan_confirm_completion_handoff_readiness_review', false],
            ['backup_candidate_ready_for_plan_confirm_completion_handoff_readiness_review', false],
            ['comparator_candidate_ready_for_plan_confirm_completion_handoff_readiness_review', true],
            ['next_plan_confirm_completion_handoff_readiness_decision.same_topic_c161_complete', false],
            ['weekly_swing_watchlist_plan_confirm_completion_go_decision_finalization_manifest.ready_for_plan_confirm_completion_handoff_readiness_review', false],
            ['weekly_swing_watchlist_plan_confirm_completion_go_decision_finalization_manifest.go_decision_finalization_used_for_free_publication', true],
            ['weekly_swing_watchlist_plan_confirm_completion_go_decision_finalization_checklist.artifact_only', false],
        ];
    }

    public function test_c162_rejects_publication_or_plan_confirm_mutation_from_c161_finalization(): void
    {
        $published = $this->mutateFinalizationAndExecute(function (array $finalization): array {
            $finalization['weekly_swing_watchlist_official_output_published'] = true;
            return $finalization;
        }, 'published');
        $publicationAllowed = $this->mutateFinalizationAndExecute(function (array $finalization): array {
            $finalization['weekly_swing_watchlist_publication_allowed'] = true;
            return $finalization;
        }, 'publication-allowed');
        $planConfirm = $this->mutateFinalizationAndExecute(function (array $finalization): array {
            $finalization['plan_confirm_mutated'] = true;
            return $finalization;
        }, 'plan-confirm-mutated');
        $liveRollout = $this->mutateFinalizationAndExecute(function (array $finalization): array {
            $finalization['live_plan_confirm_rollout_executed'] = true;
            return $finalization;
        }, 'live-rollout');

        $expected = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED';
        $this->assertSame($expected, $published['status']);
        $this->assertSame($expected, $publicationAllowed['status']);
        $this->assertSame($expected, $planConfirm['status']);
        $this->assertSame($expected, $liveRollout['status']);
    }

    public function test_c162_rejects_candidate_scope_change_or_a01_promotion(): void
    {
        $candidate = $this->mutateFinalizationAndExecute(function (array $finalization): array {
            $finalization['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $finalization;
        }, 'candidate-primary');
        $a01 = $this->mutateFinalizationAndExecute(function (array $finalization): array {
            $finalization['a01_promoted'] = true;
            return $finalization;
        }, 'candidate-a01');

        $this->assertSame('C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $candidate['status']);
        $this->assertSame('C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $a01['status']);
    }

    public function test_c162_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c162-handoff-readiness-negative-remains-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame('C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS', $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c162_records_source_locks_manifest_checklist_and_no_publication_or_rollout(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();
        $manifest = $result['weekly_swing_watchlist_plan_confirm_completion_handoff_readiness_manifest'];
        $checklist = $result['weekly_swing_watchlist_plan_confirm_completion_handoff_readiness_checklist'];

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $this->assertSame(self::C161_FINALIZATION_HASH, $result['expected_c161_finalization_hash']);
        $this->assertSame(self::C161_FINALIZATION_HASH, $result['actual_c161_finalization_hash']);
        $this->assertTrue($result['c161_finalization_hash_match']);
        $this->assertSame(self::C161_FINALIZATION_SHA1, $result['expected_c161_finalization_file_sha1']);
        $this->assertSame(self::C161_FINALIZATION_SHA1, $result['actual_c161_finalization_file_sha1']);
        $this->assertTrue($result['c161_finalization_file_sha1_match']);
        $this->assertTrue($result['c161_finalization_convert_from_json_pass']);
        $this->assertSame(self::NEXT_C162, $result['next_plan_confirm_completion_handoff_finalization_decision']['next_recommendation']);

        foreach ([
            'source_artifact_locks',
            'c161_finalization_lock_validation_summary',
            'c161_plan_confirm_completion_finalization_carry_forward_summary',
            'plan_confirm_completion_handoff_readiness_guard_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'c162_handoff_readiness_decision',
            'next_plan_confirm_completion_handoff_finalization_decision',
            'weekly_swing_watchlist_plan_confirm_completion_handoff_readiness_manifest',
            'weekly_swing_watchlist_plan_confirm_completion_handoff_readiness_checklist',
            'c162_candidate_plan_confirm_completion_handoff_readiness_scorecard',
            'publication_plan_confirm_safety_summary',
            'documentation_hygiene_guard_summary',
            'progress_summary',
            'planned_next_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }

        $this->assertTrue($manifest['handoff_readiness_artifact_only']);
        $this->assertTrue($manifest['handoff_ready']);
        $this->assertSame('HANDOFF_READY_GO', $manifest['handoff_readiness_go_decision']);
        $this->assertTrue($manifest['ready_for_plan_confirm_completion_handoff_finalization_review']);
        $this->assertFalse($manifest['official_output_published']);
        $this->assertFalse($manifest['free_publication_allowed']);
        $this->assertFalse($manifest['handoff_readiness_used_for_free_publication']);
        $this->assertFalse($manifest['handoff_readiness_used_for_plan_confirm_mutation']);
        $this->assertFalse($manifest['handoff_readiness_used_for_live_plan_confirm_rollout']);
        $this->assertTrue($checklist['handoff_readiness_reviewed']);
        $this->assertTrue($checklist['artifact_only']);
        $this->assertFalse($checklist['weekly_swing_stock_recommendation_free_published_in_c162_handoff_readiness']);
    }

    public function test_c162_keeps_e02_primary_b01_backup_a01_comparator_and_safety_flags_false(): void
    {
        $result = $this->runService();
        $scorecard = $result['c162_candidate_plan_confirm_completion_handoff_readiness_scorecard'];

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $result['primary_candidate_code']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $result['backup_candidate_code']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $result['comparator_candidate_code']);
        $this->assertTrue($result['primary_candidate_ready_for_plan_confirm_completion_handoff_finalization_review']);
        $this->assertTrue($result['backup_candidate_ready_for_plan_confirm_completion_handoff_finalization_review']);
        $this->assertFalse($result['comparator_candidate_ready_for_plan_confirm_completion_handoff_finalization_review']);
        $this->assertTrue($result['a01_remains_comparator_only']);
        $this->assertTrue($scorecard[0]['primary_candidate_ready_for_plan_confirm_completion_handoff_finalization_review']);
        $this->assertTrue($scorecard[1]['backup_candidate_ready_for_plan_confirm_completion_handoff_finalization_review']);
        $this->assertFalse($scorecard[2]['ready_for_plan_confirm_completion_handoff_finalization_review']);

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

    public function test_c162_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $first = $this->runService(['createdAt' => '2026-07-17T00:00:00+00:00']);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c162-plan-confirm-completion-handoff-readiness-review-second.json';
        $this->tmpFiles[] = $secondOutput;
        $second = $this->runService([
            'output' => $secondOutput,
            'createdAt' => '2026-07-17T00:00:00+00:00',
        ]);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
    }

    public function test_c162_does_not_mutate_c161_finalization_artifact_or_config_defaults(): void
    {
        $beforeFinalization = strtoupper(sha1((string) file_get_contents(self::C161_FINALIZATION_ARTIFACT)));
        $beforeConfig = (string) file_get_contents('config/watchlist.php');

        $this->runService();

        $this->assertSame($beforeFinalization, strtoupper(sha1((string) file_get_contents(self::C161_FINALIZATION_ARTIFACT))));
        $this->assertSame($beforeConfig, (string) file_get_contents('config/watchlist.php'));
    }

    private function runService(array $options = []): array
    {
        $service = new WatchlistBacktestC162WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionHandoffReadinessReviewService();

        return $service->execute(
            (string) ($options['c161FinalizationArtifact'] ?? self::C161_FINALIZATION_ARTIFACT),
            (string) ($options['expectedC161FinalizationHash'] ?? self::C161_FINALIZATION_HASH),
            (string) ($options['expectedC161FinalizationFileSha1'] ?? self::C161_FINALIZATION_SHA1),
            (string) ($options['output'] ?? $this->output),
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'handoff_readiness_confirmed' => (bool) ($options['handoffReadinessConfirmed'] ?? true),
                'c161_topic_complete_confirmed' => (bool) ($options['c161TopicCompleteConfirmed'] ?? true),
                'plan_confirm_completion_closed_confirmed' => (bool) ($options['planConfirmCompletionClosedConfirmed'] ?? true),
                'plan_confirm_unchanged_confirmed' => (bool) ($options['planConfirmUnchangedConfirmed'] ?? true),
                'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['noLivePlanConfirmRolloutConfirmed'] ?? true),
                'free_publication_locked_confirmed' => (bool) ($options['freePublicationLockedConfirmed'] ?? true),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C162_OPERATOR_APPROVED_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_ONLY'),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-17T00:00:00+00:00'),
            ]
        );
    }

    private function mutateFinalizationAndExecute(callable $mutator, string $name): array
    {
        $finalization = json_decode((string) file_get_contents(self::C161_FINALIZATION_ARTIFACT), true);
        $finalization = $mutator(is_array($finalization) ? $finalization : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c162-handoff-readiness-source-finalization-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($finalization, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'c161FinalizationArtifact' => $path,
            'expectedC161FinalizationHash' => (string) ($finalization['artifact_hash'] ?? ''),
            'expectedC161FinalizationFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
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

    private function cleanupC162TemporaryArtifacts(): void
    {
        foreach ((array) glob('storage/app/watchlist/backtest/c162-*handoff-readiness*-test.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/.tmp-c162-handoff-readiness*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/.tmp-c162-plan-confirm-completion-handoff-readiness-review*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}

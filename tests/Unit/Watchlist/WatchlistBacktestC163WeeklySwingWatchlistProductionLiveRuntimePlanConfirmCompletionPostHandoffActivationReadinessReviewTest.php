<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationReadinessReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationReadinessReviewTest extends TestCase
{
    private const C163_POST_HANDOFF_BOUNDARY_ARTIFACT = 'storage/app/watchlist/backtest/c163-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-boundary-review.json';
    private const C163_POST_HANDOFF_BOUNDARY_HASH = 'e0cb142d4a075acefb89e5a6f0a367e090ec190d';
    private const C163_POST_HANDOFF_BOUNDARY_SHA1 = '986469AFAC7F1349A77F4FD1712AB2272CC6E37A';
    private const PASS_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_READINESS_REVIEW_PASSED_READY_FOR_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW_PRIMARY_AND_BACKUP';
    private const NEXT_C163 = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW';
    private const APPROVAL_MISSING_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_READINESS_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const READINESS_MISSING_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_READINESS_REVIEW_REJECTED_POST_HANDOFF_ACTIVATION_READINESS_CONFIRMATION_MISSING';
    private const BOUNDARY_COMPLETE_MISSING_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_READINESS_REVIEW_REJECTED_C163_POST_HANDOFF_BOUNDARY_COMPLETE_CONFIRMATION_MISSING';
    private const BOUNDARY_CONFIRMATION_MISSING_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_READINESS_REVIEW_REJECTED_POST_HANDOFF_BOUNDARY_CONFIRMATION_MISSING';
    private const PLAN_CONFIRM_UNCHANGED_MISSING_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_READINESS_REVIEW_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING';
    private const NO_LIVE_ROLLOUT_MISSING_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_READINESS_REVIEW_REJECTED_NO_LIVE_PLAN_CONFIRM_ROLLOUT_CONFIRMATION_MISSING';
    private const FREE_PUBLICATION_LOCK_MISSING_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_READINESS_REVIEW_REJECTED_FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING';
    private const LOCK_MISMATCH_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_READINESS_REVIEW_REJECTED_C163_POST_HANDOFF_BOUNDARY_ARTIFACT_LOCK_MISMATCH';
    private const SHA1_MISMATCH_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_READINESS_REVIEW_REJECTED_C163_POST_HANDOFF_BOUNDARY_FILE_SHA1_LOCK_MISMATCH';
    private const BOUNDARY_STATE_INVALID_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_READINESS_REVIEW_REJECTED_C163_POST_HANDOFF_BOUNDARY_STATE_INVALID';

    private string $output;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c163-plan-confirm-completion-post-handoff-activation-readiness-review.json';
        $this->cleanupC163TemporaryArtifacts();
        @unlink($this->output);
    }

    protected function tearDown(): void
    {
        @unlink($this->output);
        foreach ($this->tmpFiles as $path) {
            @unlink($path);
        }
        $this->cleanupC163TemporaryArtifacts();
        parent::tearDown();
    }

    public function test_c163_post_handoff_activation_readiness_passes_after_locked_boundary(): void
    {
        $result = $this->runService();

        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_READINESS_REVIEW', $result['run_code']);
        $this->assertSame('PR-74 / C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_READINESS_REVIEW', $result['phase_label']);
        $this->assertSame('C163_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_READINESS', $result['topic_code']);
        $this->assertSame('PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_READINESS_REVIEW', $result['topic_stage']);
        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame(self::PASS_STATUS, $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_readiness_review_pass']);
        $this->assertTrue($result['post_handoff_activation_readiness_confirmed']);
        $this->assertTrue($result['c163_post_handoff_boundary_complete_confirmed']);
        $this->assertTrue($result['c163_post_handoff_boundary_complete']);
        $this->assertTrue($result['ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_approval_review']);
        $this->assertSame(self::NEXT_C163, $result['next_step_recommendation']);
        $this->assertSame(self::NEXT_C163, $result['next_plan_confirm_completion_post_handoff_activation_decision']['next_recommendation']);
        $this->assertFileExists($this->output);
    }

    public function test_c163_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->runService(['operatorApproved' => false]);
        $missingReference = $this->runService(['approvalReference' => '']);

        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingOperator['status']);
        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingReference['status']);
    }

    public function test_c163_rejects_missing_required_confirmations(): void
    {
        $readiness = $this->runService(['postHandoffActivationReadinessConfirmed' => false]);
        $boundaryComplete = $this->runService(['c163PostHandoffBoundaryCompleteConfirmed' => false]);
        $boundary = $this->runService(['postHandoffBoundaryConfirmed' => false]);
        $planUnchanged = $this->runService(['planConfirmUnchangedConfirmed' => false]);
        $noRollout = $this->runService(['noLivePlanConfirmRolloutConfirmed' => false]);
        $freeLock = $this->runService(['freePublicationLockedConfirmed' => false]);

        $this->assertSame(self::READINESS_MISSING_STATUS, $readiness['status']);
        $this->assertSame(self::BOUNDARY_COMPLETE_MISSING_STATUS, $boundaryComplete['status']);
        $this->assertSame(self::BOUNDARY_CONFIRMATION_MISSING_STATUS, $boundary['status']);
        $this->assertSame(self::PLAN_CONFIRM_UNCHANGED_MISSING_STATUS, $planUnchanged['status']);
        $this->assertSame(self::NO_LIVE_ROLLOUT_MISSING_STATUS, $noRollout['status']);
        $this->assertSame(self::FREE_PUBLICATION_LOCK_MISSING_STATUS, $freeLock['status']);
    }

    public function test_c163_rejects_missing_or_mismatched_c163_boundary_lock(): void
    {
        $missing = $this->runService([
            'c163PostHandoffBoundaryArtifact' => 'storage/app/watchlist/backtest/.tmp-c163-post-handoff-boundary-source-missing.json',
            'expectedC163PostHandoffBoundaryHash' => 'missing',
            'expectedC163PostHandoffBoundaryFileSha1' => 'missing',
        ]);
        $hashMismatch = $this->runService(['expectedC163PostHandoffBoundaryHash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedC163PostHandoffBoundaryFileSha1' => 'BADSHA1']);

        $this->assertSame(self::LOCK_MISMATCH_STATUS, $missing['status']);
        $this->assertSame(self::LOCK_MISMATCH_STATUS, $hashMismatch['status']);
        $this->assertSame(self::SHA1_MISMATCH_STATUS, $shaMismatch['status']);
    }

    public function test_c163_rejects_boundary_status_phase_or_next_recommendation_mismatch(): void
    {
        $status = $this->mutateBoundaryAndExecute(function (array $boundary): array {
            $boundary['status'] = 'BROKEN_STATUS';
            return $boundary;
        }, 'status-broken');
        $phase = $this->mutateBoundaryAndExecute(function (array $boundary): array {
            $boundary['phase_label'] = 'BROKEN_PHASE';
            return $boundary;
        }, 'phase-broken');
        $next = $this->mutateBoundaryAndExecute(function (array $boundary): array {
            $boundary['next_step_recommendation'] = 'BROKEN_NEXT';
            $boundary['next_plan_confirm_completion_post_handoff_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $boundary['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $boundary;
        }, 'next-broken');

        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_READINESS_REVIEW_REJECTED_C163_POST_HANDOFF_BOUNDARY_STATUS_MISMATCH', $status['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_READINESS_REVIEW_REJECTED_C163_POST_HANDOFF_BOUNDARY_PHASE_LABEL_MISMATCH', $phase['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_READINESS_REVIEW_REJECTED_C163_POST_HANDOFF_BOUNDARY_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
    }

    public function test_c163_rejects_boundary_convert_from_json_duplicate_top_level_keys(): void
    {
        $raw = (string) file_get_contents(self::C163_POST_HANDOFF_BOUNDARY_ARTIFACT);
        $path = 'storage/app/watchlist/backtest/.tmp-c163-activation-readiness-source-boundary-duplicate-key.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, preg_replace('/\{/', "{\"Run_Code\":\"DUPLICATE_CASE_KEY\",", $raw, 1));

        $result = $this->runService([
            'c163PostHandoffBoundaryArtifact' => $path,
            'expectedC163PostHandoffBoundaryHash' => self::C163_POST_HANDOFF_BOUNDARY_HASH,
            'expectedC163PostHandoffBoundaryFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);

        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_READINESS_REVIEW_REJECTED_C163_POST_HANDOFF_BOUNDARY_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION', $result['status']);
        $this->assertFalse($result['c163_post_handoff_boundary_convert_from_json_pass']);
    }

    /**
     * @dataProvider c163BoundaryStateMismatchProvider
     */
    public function test_c163_rejects_boundary_state_mismatch(string $field, $value): void
    {
        $result = $this->mutateBoundaryAndExecute(function (array $boundary) use ($field, $value): array {
            $this->setValueAt($boundary, explode('.', $field), $value);
            return $boundary;
        }, 'state-'.str_replace('.', '-', $field));

        $this->assertSame(self::BOUNDARY_STATE_INVALID_STATUS, $result['status'], $field);
    }

    public function c163BoundaryStateMismatchProvider(): array
    {
        return [
            ['weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_boundary_review_pass', false],
            ['post_handoff_boundary_confirmed', false],
            ['ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_readiness_review', false],
            ['production_live_runtime_plan_confirm_completion_post_handoff_activation_readiness_review_allowed_next', false],
            ['c163_not_plan_confirm_mutation', false],
            ['controlled_completion_record_count', 0],
            ['controlled_completion_hash', ''],
            ['primary_candidate_ready_for_plan_confirm_completion_post_handoff_activation_readiness_review', false],
            ['backup_candidate_ready_for_plan_confirm_completion_post_handoff_activation_readiness_review', false],
            ['comparator_candidate_ready_for_plan_confirm_completion_post_handoff_activation_readiness_review', true],
            ['c163_post_handoff_boundary_decision.review_valid', false],
            ['next_plan_confirm_completion_post_handoff_decision.c163_post_handoff_boundary_complete', false],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_boundary_manifest.ready_for_plan_confirm_completion_post_handoff_activation_readiness_review', false],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_boundary_manifest.post_handoff_boundary_artifact_only', false],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_boundary_checklist.artifact_only', false],
        ];
    }

    public function test_c163_rejects_publication_or_plan_confirm_mutation_from_boundary(): void
    {
        $published = $this->mutateBoundaryAndExecute(function (array $boundary): array {
            $boundary['weekly_swing_watchlist_official_output_published'] = true;
            return $boundary;
        }, 'published');
        $publicationAllowed = $this->mutateBoundaryAndExecute(function (array $boundary): array {
            $boundary['weekly_swing_watchlist_publication_allowed'] = true;
            return $boundary;
        }, 'publication-allowed');
        $planConfirm = $this->mutateBoundaryAndExecute(function (array $boundary): array {
            $boundary['plan_confirm_mutated'] = true;
            return $boundary;
        }, 'plan-confirm-mutated');
        $liveRollout = $this->mutateBoundaryAndExecute(function (array $boundary): array {
            $boundary['live_plan_confirm_rollout_executed'] = true;
            return $boundary;
        }, 'live-rollout');
        $nestedFreePublication = $this->mutateBoundaryAndExecute(function (array $boundary): array {
            $boundary['weekly_swing_watchlist_plan_confirm_completion_post_handoff_boundary_manifest']['post_handoff_boundary_used_for_free_publication'] = true;
            return $boundary;
        }, 'nested-free-publication');

        $expected = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_READINESS_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED';
        $this->assertSame($expected, $published['status']);
        $this->assertSame($expected, $publicationAllowed['status']);
        $this->assertSame($expected, $planConfirm['status']);
        $this->assertSame($expected, $liveRollout['status']);
        $this->assertSame($expected, $nestedFreePublication['status']);
    }

    public function test_c163_rejects_candidate_scope_change_or_a01_promotion(): void
    {
        $candidate = $this->mutateBoundaryAndExecute(function (array $boundary): array {
            $boundary['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $boundary;
        }, 'candidate-primary');
        $a01 = $this->mutateBoundaryAndExecute(function (array $boundary): array {
            $boundary['a01_promoted'] = true;
            return $boundary;
        }, 'candidate-a01');

        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_READINESS_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $candidate['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_READINESS_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $a01['status']);
    }

    public function test_c163_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c163-post-handoff-activation-readiness-negative-remains-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_READINESS_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS', $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c163_records_source_locks_manifest_checklist_no_publication_and_next_approval(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();
        $manifest = $result['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_readiness_manifest'];
        $checklist = $result['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_readiness_checklist'];

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $this->assertSame(self::C163_POST_HANDOFF_BOUNDARY_HASH, $result['expected_c163_post_handoff_boundary_hash']);
        $this->assertSame(self::C163_POST_HANDOFF_BOUNDARY_HASH, $result['actual_c163_post_handoff_boundary_hash']);
        $this->assertTrue($result['c163_post_handoff_boundary_hash_match']);
        $this->assertSame(self::C163_POST_HANDOFF_BOUNDARY_SHA1, $result['expected_c163_post_handoff_boundary_file_sha1']);
        $this->assertSame(self::C163_POST_HANDOFF_BOUNDARY_SHA1, $result['actual_c163_post_handoff_boundary_file_sha1']);
        $this->assertTrue($result['c163_post_handoff_boundary_file_sha1_match']);
        $this->assertTrue($result['c163_post_handoff_boundary_convert_from_json_pass']);
        $this->assertTrue($manifest['post_handoff_activation_readiness_artifact_only']);
        $this->assertTrue($manifest['ready_for_plan_confirm_completion_post_handoff_activation_approval_review']);
        $this->assertFalse($manifest['activation_readiness_used_for_free_publication']);
        $this->assertFalse($manifest['activation_readiness_used_for_plan_confirm_mutation']);
        $this->assertFalse($manifest['activation_readiness_used_for_live_plan_confirm_rollout']);
        $this->assertSame([], $manifest['official_weekly_swing_stock_recommendations']);
        $this->assertTrue($checklist['artifact_only']);
        $this->assertTrue($checklist['post_handoff_activation_approval_review_required_next']);
        $this->assertFalse($checklist['weekly_swing_stock_recommendation_free_published_in_c163_activation_readiness']);

        foreach ([
            'source_artifact_locks',
            'c163_post_handoff_boundary_lock_validation_summary',
            'c163_post_handoff_boundary_carry_forward_summary',
            'plan_confirm_completion_post_handoff_activation_readiness_guard_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'c163_post_handoff_activation_readiness_decision',
            'next_plan_confirm_completion_post_handoff_activation_decision',
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_readiness_manifest',
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_readiness_checklist',
            'c163_candidate_plan_confirm_completion_post_handoff_activation_readiness_scorecard',
            'publication_plan_confirm_safety_summary',
            'documentation_hygiene_guard_summary',
            'progress_summary',
            'planned_next_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }

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

    public function test_c163_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $first = $this->runService(['createdAt' => '2026-07-18T00:00:00+00:00']);
        $second = $this->runService(['createdAt' => '2026-07-18T00:00:00+00:00']);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
    }

    private function runService(array $options = []): array
    {
        $service = new WatchlistBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationReadinessReviewService();

        return $service->execute(
            (string) ($options['c163PostHandoffBoundaryArtifact'] ?? self::C163_POST_HANDOFF_BOUNDARY_ARTIFACT),
            (string) ($options['expectedC163PostHandoffBoundaryHash'] ?? self::C163_POST_HANDOFF_BOUNDARY_HASH),
            (string) ($options['expectedC163PostHandoffBoundaryFileSha1'] ?? self::C163_POST_HANDOFF_BOUNDARY_SHA1),
            (string) ($options['output'] ?? $this->output),
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'post_handoff_activation_readiness_confirmed' => (bool) ($options['postHandoffActivationReadinessConfirmed'] ?? true),
                'c163_post_handoff_boundary_complete_confirmed' => (bool) ($options['c163PostHandoffBoundaryCompleteConfirmed'] ?? true),
                'post_handoff_boundary_confirmed' => (bool) ($options['postHandoffBoundaryConfirmed'] ?? true),
                'plan_confirm_unchanged_confirmed' => (bool) ($options['planConfirmUnchangedConfirmed'] ?? true),
                'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['noLivePlanConfirmRolloutConfirmed'] ?? true),
                'free_publication_locked_confirmed' => (bool) ($options['freePublicationLockedConfirmed'] ?? true),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C163_OPERATOR_APPROVED_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_READINESS_REVIEW'),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-18T00:00:00+00:00'),
            ]
        );
    }

    private function mutateBoundaryAndExecute(callable $mutator, string $name): array
    {
        $boundary = json_decode((string) file_get_contents(self::C163_POST_HANDOFF_BOUNDARY_ARTIFACT), true);
        $boundary = $mutator(is_array($boundary) ? $boundary : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c163-post-handoff-activation-readiness-source-boundary-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($boundary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'c163PostHandoffBoundaryArtifact' => $path,
            'expectedC163PostHandoffBoundaryHash' => (string) ($boundary['artifact_hash'] ?? ''),
            'expectedC163PostHandoffBoundaryFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
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

    private function cleanupC163TemporaryArtifacts(): void
    {
        foreach ((array) glob('storage/app/watchlist/backtest/c163-*post-handoff-activation-readiness*-test.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/.tmp-c163-post-handoff-activation-readiness*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/.tmp-runtime-c163-post-handoff-activation-readiness-negative-*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}

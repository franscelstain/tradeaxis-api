<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationApprovalReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationApprovalReviewTest extends TestCase
{
    private const C163_READINESS_ARTIFACT = 'storage/app/watchlist/backtest/c163-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-readiness-review.json';
    private const C163_READINESS_HASH = '2ade4f45972d1675eb2be1c222bc688d0c454b3b';
    private const C163_READINESS_SHA1 = '17BA06C16DC071B38643D8F502C2D22808725A72';
    private const PASS_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW_PASSED_READY_FOR_POST_HANDOFF_ACTIVATION_EXECUTION_REVIEW_PRIMARY_AND_BACKUP';
    private const NEXT_C163 = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_EXECUTION_REVIEW';

    private string $output;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c163-plan-confirm-completion-post-handoff-activation-approval-review.json';
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

    public function test_c163_post_handoff_activation_approval_passes_after_locked_readiness(): void
    {
        $result = $this->runService();

        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW', $result['run_code']);
        $this->assertSame('PR-75 / C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW', $result['phase_label']);
        $this->assertSame('C163_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL', $result['topic_code']);
        $this->assertSame('PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW', $result['topic_stage']);
        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame(self::PASS_STATUS, $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_approval_review_pass']);
        $this->assertTrue($result['post_handoff_activation_approval_confirmed']);
        $this->assertTrue($result['post_handoff_activation_approval_granted']);
        $this->assertTrue($result['c163_post_handoff_activation_readiness_complete']);
        $this->assertTrue($result['ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_execution_review']);
        $this->assertSame(self::NEXT_C163, $result['next_step_recommendation']);
        $this->assertSame(self::NEXT_C163, $result['next_plan_confirm_completion_post_handoff_activation_decision']['next_recommendation']);
        $this->assertFileExists($this->output);
    }

    public function test_c163_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->runService(['operatorApproved' => false]);
        $missingReference = $this->runService(['approvalReference' => '']);

        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingOperator['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingReference['status']);
    }

    public function test_c163_rejects_missing_required_confirmations(): void
    {
        $approval = $this->runService(['postHandoffActivationApprovalConfirmed' => false]);
        $readinessComplete = $this->runService(['c163PostHandoffActivationReadinessCompleteConfirmed' => false]);
        $readiness = $this->runService(['postHandoffActivationReadinessConfirmed' => false]);
        $planUnchanged = $this->runService(['planConfirmUnchangedConfirmed' => false]);
        $noRollout = $this->runService(['noLivePlanConfirmRolloutConfirmed' => false]);
        $freeLock = $this->runService(['freePublicationLockedConfirmed' => false]);

        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW_REJECTED_POST_HANDOFF_ACTIVATION_APPROVAL_CONFIRMATION_MISSING', $approval['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_READINESS_COMPLETE_CONFIRMATION_MISSING', $readinessComplete['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW_REJECTED_POST_HANDOFF_ACTIVATION_READINESS_CONFIRMATION_MISSING', $readiness['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING', $planUnchanged['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW_REJECTED_NO_LIVE_PLAN_CONFIRM_ROLLOUT_CONFIRMATION_MISSING', $noRollout['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW_REJECTED_FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING', $freeLock['status']);
    }

    public function test_c163_rejects_missing_or_mismatched_readiness_lock(): void
    {
        $missing = $this->runService([
            'c163ReadinessArtifact' => 'storage/app/watchlist/backtest/.tmp-c163-activation-readiness-source-missing.json',
            'expectedC163ReadinessHash' => 'missing',
            'expectedC163ReadinessFileSha1' => 'missing',
        ]);
        $hashMismatch = $this->runService(['expectedC163ReadinessHash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedC163ReadinessFileSha1' => 'BADSHA1']);

        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_READINESS_ARTIFACT_LOCK_MISMATCH', $missing['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_READINESS_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_READINESS_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
    }

    public function test_c163_rejects_readiness_status_phase_or_next_recommendation_mismatch(): void
    {
        $status = $this->mutateReadinessAndExecute(function (array $readiness): array {
            $readiness['status'] = 'BROKEN_STATUS';
            return $readiness;
        }, 'status-broken');
        $phase = $this->mutateReadinessAndExecute(function (array $readiness): array {
            $readiness['phase_label'] = 'BROKEN_PHASE';
            return $readiness;
        }, 'phase-broken');
        $next = $this->mutateReadinessAndExecute(function (array $readiness): array {
            $readiness['next_step_recommendation'] = 'BROKEN_NEXT';
            $readiness['next_plan_confirm_completion_post_handoff_activation_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $readiness['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $readiness;
        }, 'next-broken');

        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_READINESS_STATUS_MISMATCH', $status['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_READINESS_PHASE_LABEL_MISMATCH', $phase['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_READINESS_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
    }

    public function test_c163_rejects_readiness_convert_from_json_duplicate_top_level_keys(): void
    {
        $raw = (string) file_get_contents(self::C163_READINESS_ARTIFACT);
        $path = 'storage/app/watchlist/backtest/.tmp-c163-activation-approval-source-readiness-duplicate-key.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, preg_replace('/\{/', "{\"Run_Code\":\"DUPLICATE_CASE_KEY\",", $raw, 1));

        $result = $this->runService([
            'c163ReadinessArtifact' => $path,
            'expectedC163ReadinessHash' => self::C163_READINESS_HASH,
            'expectedC163ReadinessFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);

        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_READINESS_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION', $result['status']);
        $this->assertFalse($result['c163_post_handoff_activation_readiness_convert_from_json_pass']);
    }

    /**
     * @dataProvider readinessStateMismatchProvider
     */
    public function test_c163_rejects_readiness_state_mismatch(string $field, $value): void
    {
        $result = $this->mutateReadinessAndExecute(function (array $readiness) use ($field, $value): array {
            $this->setValueAt($readiness, explode('.', $field), $value);
            return $readiness;
        }, 'state-'.str_replace('.', '-', $field));

        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_READINESS_STATE_INVALID', $result['status'], $field);
    }

    public function readinessStateMismatchProvider(): array
    {
        return [
            ['weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_readiness_review_pass', false],
            ['post_handoff_activation_readiness_confirmed', false],
            ['ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_approval_review', false],
            ['production_live_runtime_plan_confirm_completion_post_handoff_activation_approval_review_allowed_next', false],
            ['c163_not_plan_confirm_mutation', false],
            ['controlled_completion_record_count', 0],
            ['controlled_completion_hash', ''],
            ['primary_candidate_ready_for_plan_confirm_completion_post_handoff_activation_approval_review', false],
            ['backup_candidate_ready_for_plan_confirm_completion_post_handoff_activation_approval_review', false],
            ['comparator_candidate_ready_for_plan_confirm_completion_post_handoff_activation_approval_review', true],
            ['c163_post_handoff_activation_readiness_decision.review_valid', false],
            ['next_plan_confirm_completion_post_handoff_activation_decision.c163_post_handoff_activation_readiness_complete', false],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_readiness_manifest.ready_for_plan_confirm_completion_post_handoff_activation_approval_review', false],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_readiness_manifest.post_handoff_activation_readiness_artifact_only', false],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_readiness_checklist.artifact_only', false],
        ];
    }

    public function test_c163_rejects_publication_or_plan_confirm_mutation_from_readiness(): void
    {
        $published = $this->mutateReadinessAndExecute(function (array $readiness): array {
            $readiness['weekly_swing_watchlist_official_output_published'] = true;
            return $readiness;
        }, 'published');
        $planConfirm = $this->mutateReadinessAndExecute(function (array $readiness): array {
            $readiness['plan_confirm_mutated'] = true;
            return $readiness;
        }, 'plan-confirm-mutated');
        $liveRollout = $this->mutateReadinessAndExecute(function (array $readiness): array {
            $readiness['live_plan_confirm_rollout_executed'] = true;
            return $readiness;
        }, 'live-rollout');
        $nestedFreePublication = $this->mutateReadinessAndExecute(function (array $readiness): array {
            $readiness['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_readiness_manifest']['activation_readiness_used_for_free_publication'] = true;
            return $readiness;
        }, 'nested-free-publication');

        $expected = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED';
        $this->assertSame($expected, $published['status']);
        $this->assertSame($expected, $planConfirm['status']);
        $this->assertSame($expected, $liveRollout['status']);
        $this->assertSame($expected, $nestedFreePublication['status']);
    }

    public function test_c163_rejects_candidate_scope_change_or_a01_promotion(): void
    {
        $candidate = $this->mutateReadinessAndExecute(function (array $readiness): array {
            $readiness['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $readiness;
        }, 'candidate-primary');
        $a01 = $this->mutateReadinessAndExecute(function (array $readiness): array {
            $readiness['a01_promoted'] = true;
            return $readiness;
        }, 'candidate-a01');

        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $candidate['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $a01['status']);
    }

    public function test_c163_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c163-post-handoff-activation-approval-negative-remains-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS', $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c163_records_source_locks_manifest_checklist_no_publication_and_next_execution(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();
        $manifest = $result['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_approval_manifest'];
        $checklist = $result['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_approval_checklist'];

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $this->assertSame(self::C163_READINESS_HASH, $result['expected_c163_post_handoff_activation_readiness_hash']);
        $this->assertSame(self::C163_READINESS_HASH, $result['actual_c163_post_handoff_activation_readiness_hash']);
        $this->assertTrue($result['c163_post_handoff_activation_readiness_hash_match']);
        $this->assertSame(self::C163_READINESS_SHA1, $result['expected_c163_post_handoff_activation_readiness_file_sha1']);
        $this->assertSame(self::C163_READINESS_SHA1, $result['actual_c163_post_handoff_activation_readiness_file_sha1']);
        $this->assertTrue($result['c163_post_handoff_activation_readiness_file_sha1_match']);
        $this->assertTrue($manifest['post_handoff_activation_approval_artifact_only']);
        $this->assertTrue($manifest['ready_for_plan_confirm_completion_post_handoff_activation_execution_review']);
        $this->assertFalse($manifest['activation_approval_used_for_free_publication']);
        $this->assertFalse($manifest['activation_approval_used_for_plan_confirm_mutation']);
        $this->assertFalse($manifest['activation_approval_used_for_live_plan_confirm_rollout']);
        $this->assertSame([], $manifest['official_weekly_swing_stock_recommendations']);
        $this->assertTrue($checklist['artifact_only']);
        $this->assertTrue($checklist['post_handoff_activation_execution_review_required_next']);

        foreach ([
            'source_artifact_locks',
            'c163_post_handoff_activation_readiness_lock_validation_summary',
            'c163_post_handoff_activation_readiness_carry_forward_summary',
            'plan_confirm_completion_post_handoff_activation_approval_guard_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'c163_post_handoff_activation_approval_decision',
            'next_plan_confirm_completion_post_handoff_activation_decision',
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_approval_manifest',
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_approval_checklist',
            'c163_candidate_plan_confirm_completion_post_handoff_activation_approval_scorecard',
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
        $service = new WatchlistBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationApprovalReviewService();

        return $service->execute(
            (string) ($options['c163ReadinessArtifact'] ?? self::C163_READINESS_ARTIFACT),
            (string) ($options['expectedC163ReadinessHash'] ?? self::C163_READINESS_HASH),
            (string) ($options['expectedC163ReadinessFileSha1'] ?? self::C163_READINESS_SHA1),
            (string) ($options['output'] ?? $this->output),
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'post_handoff_activation_approval_confirmed' => (bool) ($options['postHandoffActivationApprovalConfirmed'] ?? true),
                'c163_post_handoff_activation_readiness_complete_confirmed' => (bool) ($options['c163PostHandoffActivationReadinessCompleteConfirmed'] ?? true),
                'post_handoff_activation_readiness_confirmed' => (bool) ($options['postHandoffActivationReadinessConfirmed'] ?? true),
                'plan_confirm_unchanged_confirmed' => (bool) ($options['planConfirmUnchangedConfirmed'] ?? true),
                'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['noLivePlanConfirmRolloutConfirmed'] ?? true),
                'free_publication_locked_confirmed' => (bool) ($options['freePublicationLockedConfirmed'] ?? true),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C163_OPERATOR_APPROVED_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW'),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-18T00:00:00+00:00'),
            ]
        );
    }

    private function mutateReadinessAndExecute(callable $mutator, string $name): array
    {
        $readiness = json_decode((string) file_get_contents(self::C163_READINESS_ARTIFACT), true);
        $readiness = $mutator(is_array($readiness) ? $readiness : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c163-post-handoff-activation-approval-source-readiness-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($readiness, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'c163ReadinessArtifact' => $path,
            'expectedC163ReadinessHash' => (string) ($readiness['artifact_hash'] ?? ''),
            'expectedC163ReadinessFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
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
        foreach ((array) glob('storage/app/watchlist/backtest/c163-*post-handoff-activation-approval*-test.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/.tmp-c163-post-handoff-activation-approval*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/.tmp-runtime-c163-post-handoff-activation-approval-negative-*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}

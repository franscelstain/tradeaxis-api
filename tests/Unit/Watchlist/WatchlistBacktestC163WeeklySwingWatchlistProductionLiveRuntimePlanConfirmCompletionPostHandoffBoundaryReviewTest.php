<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffBoundaryReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffBoundaryReviewTest extends TestCase
{
    private const C162_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_ARTIFACT = 'storage/app/watchlist/backtest/c162-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-handoff-audit-archive-final-closure-review.json';
    private const C162_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_HASH = '4de6d670e5e6d6990dd618e0e818e57a7f79716e';
    private const C162_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_SHA1 = '97E9057EE0E7A71BC7F74B019F16FE1D251A3157';
    private const PASS_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW_PASSED_C162_HANDOFF_CLOSED_READY_FOR_POST_HANDOFF_ACTIVATION_READINESS_REVIEW';
    private const NEXT_C163 = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_READINESS_REVIEW';
    private const APPROVAL_MISSING_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const POST_HANDOFF_BOUNDARY_MISSING_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW_REJECTED_POST_HANDOFF_BOUNDARY_CONFIRMATION_MISSING';
    private const C162_CHAIN_CLOSED_MISSING_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW_REJECTED_C162_HANDOFF_AUDIT_ARCHIVE_CHAIN_CLOSED_CONFIRMATION_MISSING';
    private const C162_TERMINAL_MISSING_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW_REJECTED_C162_TERMINAL_NO_NEXT_CONFIRMATION_MISSING';
    private const PLAN_CONFIRM_UNCHANGED_MISSING_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING';
    private const NO_LIVE_ROLLOUT_MISSING_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW_REJECTED_NO_LIVE_PLAN_CONFIRM_ROLLOUT_CONFIRMATION_MISSING';
    private const FREE_PUBLICATION_LOCK_MISSING_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW_REJECTED_FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING';
    private const LOCK_MISMATCH_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW_REJECTED_C162_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_ARTIFACT_LOCK_MISMATCH';
    private const SHA1_MISMATCH_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW_REJECTED_C162_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_FILE_SHA1_LOCK_MISMATCH';
    private const C162_STATE_INVALID_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW_REJECTED_C162_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_STATE_INVALID';

    private string $output;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c163-plan-confirm-completion-post-handoff-boundary-review.json';
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

    public function test_c163_post_handoff_boundary_passes_after_c162_terminal_no_next_final_closure(): void
    {
        $result = $this->runService();

        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW', $result['run_code']);
        $this->assertSame('PR-73 / C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW', $result['phase_label']);
        $this->assertSame('C163_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY', $result['topic_code']);
        $this->assertSame('PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW', $result['topic_stage']);
        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame(self::PASS_STATUS, $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_boundary_review_pass']);
        $this->assertTrue($result['production_live_runtime_plan_confirm_completion_post_handoff_boundary_review_pass']);
        $this->assertTrue($result['post_handoff_boundary_confirmed']);
        $this->assertTrue($result['c162_handoff_audit_archive_chain_closed_confirmed']);
        $this->assertTrue($result['c162_terminal_no_next_confirmed']);
        $this->assertTrue($result['c162_handoff_audit_archive_final_closure_complete']);
        $this->assertTrue($result['no_next_weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_review_required']);
        $this->assertTrue($result['ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_readiness_review']);
        $this->assertSame(self::NEXT_C163, $result['next_step_recommendation']);
        $this->assertSame(self::NEXT_C163, $result['next_plan_confirm_completion_post_handoff_decision']['next_recommendation']);
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
        $postHandoff = $this->runService(['postHandoffBoundaryConfirmed' => false]);
        $chainClosed = $this->runService(['c162HandoffAuditArchiveChainClosedConfirmed' => false]);
        $terminal = $this->runService(['c162TerminalNoNextConfirmed' => false]);
        $planUnchanged = $this->runService(['planConfirmUnchangedConfirmed' => false]);
        $noRollout = $this->runService(['noLivePlanConfirmRolloutConfirmed' => false]);
        $freeLock = $this->runService(['freePublicationLockedConfirmed' => false]);

        $this->assertSame(self::POST_HANDOFF_BOUNDARY_MISSING_STATUS, $postHandoff['status']);
        $this->assertSame(self::C162_CHAIN_CLOSED_MISSING_STATUS, $chainClosed['status']);
        $this->assertSame(self::C162_TERMINAL_MISSING_STATUS, $terminal['status']);
        $this->assertSame(self::PLAN_CONFIRM_UNCHANGED_MISSING_STATUS, $planUnchanged['status']);
        $this->assertSame(self::NO_LIVE_ROLLOUT_MISSING_STATUS, $noRollout['status']);
        $this->assertSame(self::FREE_PUBLICATION_LOCK_MISSING_STATUS, $freeLock['status']);
    }

    public function test_c163_rejects_missing_or_mismatched_c162_final_closure_lock(): void
    {
        $missing = $this->runService([
            'c162HandoffAuditArchiveFinalClosureArtifact' => 'storage/app/watchlist/backtest/.tmp-c162-final-closure-source-missing.json',
            'expectedC162HandoffAuditArchiveFinalClosureHash' => 'missing',
            'expectedC162HandoffAuditArchiveFinalClosureFileSha1' => 'missing',
        ]);
        $hashMismatch = $this->runService(['expectedC162HandoffAuditArchiveFinalClosureHash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedC162HandoffAuditArchiveFinalClosureFileSha1' => 'BADSHA1']);

        $this->assertSame(self::LOCK_MISMATCH_STATUS, $missing['status']);
        $this->assertSame(self::LOCK_MISMATCH_STATUS, $hashMismatch['status']);
        $this->assertSame(self::SHA1_MISMATCH_STATUS, $shaMismatch['status']);
    }

    public function test_c163_rejects_c162_status_phase_or_terminal_recommendation_mismatch(): void
    {
        $status = $this->mutateC162FinalClosureAndExecute(function (array $c162): array {
            $c162['status'] = 'BROKEN_STATUS';
            return $c162;
        }, 'status-broken');
        $phase = $this->mutateC162FinalClosureAndExecute(function (array $c162): array {
            $c162['phase_label'] = 'BROKEN_PHASE';
            return $c162;
        }, 'phase-broken');
        $next = $this->mutateC162FinalClosureAndExecute(function (array $c162): array {
            $c162['next_step_recommendation'] = 'BROKEN_NEXT';
            $c162['next_plan_confirm_completion_handoff_audit_archive_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c162['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $c162;
        }, 'next-broken');

        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW_REJECTED_C162_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_STATUS_MISMATCH', $status['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW_REJECTED_C162_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_PHASE_LABEL_MISMATCH', $phase['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW_REJECTED_C162_TERMINAL_RECOMMENDATION_MISMATCH', $next['status']);
    }

    public function test_c163_rejects_c162_final_closure_convert_from_json_duplicate_top_level_keys(): void
    {
        $raw = (string) file_get_contents(self::C162_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_ARTIFACT);
        $path = 'storage/app/watchlist/backtest/.tmp-c163-source-c162-final-closure-duplicate-key.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, preg_replace('/\{/', "{\"Run_Code\":\"DUPLICATE_CASE_KEY\",", $raw, 1));

        $result = $this->runService([
            'c162HandoffAuditArchiveFinalClosureArtifact' => $path,
            'expectedC162HandoffAuditArchiveFinalClosureHash' => self::C162_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_HASH,
            'expectedC162HandoffAuditArchiveFinalClosureFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);

        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW_REJECTED_C162_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION', $result['status']);
        $this->assertFalse($result['c162_handoff_audit_archive_final_closure_convert_from_json_pass']);
    }

    /**
     * @dataProvider c162FinalClosureStateMismatchProvider
     */
    public function test_c163_rejects_c162_final_closure_state_mismatch(string $field, $value): void
    {
        $result = $this->mutateC162FinalClosureAndExecute(function (array $c162) use ($field, $value): array {
            $this->setValueAt($c162, explode('.', $field), $value);
            return $c162;
        }, 'state-'.str_replace('.', '-', $field));

        $this->assertSame(self::C162_STATE_INVALID_STATUS, $result['status'], $field);
    }

    public function c162FinalClosureStateMismatchProvider(): array
    {
        return [
            ['weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_audit_archive_final_closure_review_pass', false],
            ['handoff_audit_archive_final_closed', false],
            ['handoff_audit_archive_final_closure_go_decision', 'NO_GO'],
            ['c162_handoff_audit_archive_final_closure_complete', false],
            ['no_next_weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_review_required', false],
            ['controlled_completion_record_count', 0],
            ['controlled_completion_hash', ''],
            ['primary_candidate_handoff_audit_archive_final_closed', false],
            ['backup_candidate_handoff_audit_archive_final_closed', false],
            ['comparator_candidate_handoff_audit_archive_final_closed', true],
            ['c162_handoff_audit_archive_final_closure_decision.review_valid', false],
            ['next_plan_confirm_completion_handoff_audit_archive_decision.c162_handoff_audit_archive_final_closure_complete', false],
            ['weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_final_closure_manifest.handoff_audit_archive_chain_closed', false],
            ['weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_final_closure_manifest.final_closure_artifact_only', false],
            ['weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_final_closure_checklist.artifact_only', false],
        ];
    }

    public function test_c163_rejects_publication_or_plan_confirm_mutation_from_c162_final_closure(): void
    {
        $published = $this->mutateC162FinalClosureAndExecute(function (array $c162): array {
            $c162['weekly_swing_watchlist_official_output_published'] = true;
            return $c162;
        }, 'published');
        $publicationAllowed = $this->mutateC162FinalClosureAndExecute(function (array $c162): array {
            $c162['weekly_swing_watchlist_publication_allowed'] = true;
            return $c162;
        }, 'publication-allowed');
        $planConfirm = $this->mutateC162FinalClosureAndExecute(function (array $c162): array {
            $c162['plan_confirm_mutated'] = true;
            return $c162;
        }, 'plan-confirm-mutated');
        $liveRollout = $this->mutateC162FinalClosureAndExecute(function (array $c162): array {
            $c162['live_plan_confirm_rollout_executed'] = true;
            return $c162;
        }, 'live-rollout');
        $nestedFreePublication = $this->mutateC162FinalClosureAndExecute(function (array $c162): array {
            $c162['weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_final_closure_manifest']['final_closure_used_for_free_publication'] = true;
            return $c162;
        }, 'nested-free-publication');

        $expected = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED';
        $this->assertSame($expected, $published['status']);
        $this->assertSame($expected, $publicationAllowed['status']);
        $this->assertSame($expected, $planConfirm['status']);
        $this->assertSame($expected, $liveRollout['status']);
        $this->assertSame($expected, $nestedFreePublication['status']);
    }

    public function test_c163_rejects_candidate_scope_change_or_a01_promotion(): void
    {
        $candidate = $this->mutateC162FinalClosureAndExecute(function (array $c162): array {
            $c162['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $c162;
        }, 'candidate-primary');
        $a01 = $this->mutateC162FinalClosureAndExecute(function (array $c162): array {
            $c162['a01_promoted'] = true;
            return $c162;
        }, 'candidate-a01');

        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $candidate['status']);
        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $a01['status']);
    }

    public function test_c163_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c163-post-handoff-boundary-negative-remains-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame('C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS', $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c163_records_source_locks_manifest_checklist_no_publication_and_next_boundary(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();
        $manifest = $result['weekly_swing_watchlist_plan_confirm_completion_post_handoff_boundary_manifest'];
        $checklist = $result['weekly_swing_watchlist_plan_confirm_completion_post_handoff_boundary_checklist'];

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $this->assertSame(self::C162_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_HASH, $result['expected_c162_handoff_audit_archive_final_closure_hash']);
        $this->assertSame(self::C162_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_HASH, $result['actual_c162_handoff_audit_archive_final_closure_hash']);
        $this->assertTrue($result['c162_handoff_audit_archive_final_closure_hash_match']);
        $this->assertSame(self::C162_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_SHA1, $result['expected_c162_handoff_audit_archive_final_closure_file_sha1']);
        $this->assertSame(self::C162_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_SHA1, $result['actual_c162_handoff_audit_archive_final_closure_file_sha1']);
        $this->assertTrue($result['c162_handoff_audit_archive_final_closure_file_sha1_match']);
        $this->assertTrue($result['c162_handoff_audit_archive_final_closure_convert_from_json_pass']);
        $this->assertTrue($manifest['post_handoff_boundary_artifact_only']);
        $this->assertTrue($manifest['ready_for_plan_confirm_completion_post_handoff_activation_readiness_review']);
        $this->assertFalse($manifest['post_handoff_boundary_used_for_free_publication']);
        $this->assertFalse($manifest['post_handoff_boundary_used_for_plan_confirm_mutation']);
        $this->assertFalse($manifest['post_handoff_boundary_used_for_live_plan_confirm_rollout']);
        $this->assertSame([], $manifest['official_weekly_swing_stock_recommendations']);
        $this->assertTrue($checklist['artifact_only']);
        $this->assertTrue($checklist['post_handoff_activation_readiness_review_required_next']);
        $this->assertFalse($checklist['weekly_swing_stock_recommendation_free_published_in_c163_post_handoff_boundary']);

        foreach ([
            'source_artifact_locks',
            'c162_handoff_audit_archive_final_closure_lock_validation_summary',
            'c162_handoff_audit_archive_final_closure_carry_forward_summary',
            'plan_confirm_completion_post_handoff_boundary_guard_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'c163_post_handoff_boundary_decision',
            'next_plan_confirm_completion_post_handoff_decision',
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_boundary_manifest',
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_boundary_checklist',
            'c163_candidate_plan_confirm_completion_post_handoff_boundary_scorecard',
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
        $service = new WatchlistBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffBoundaryReviewService();

        return $service->execute(
            (string) ($options['c162HandoffAuditArchiveFinalClosureArtifact'] ?? self::C162_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_ARTIFACT),
            (string) ($options['expectedC162HandoffAuditArchiveFinalClosureHash'] ?? self::C162_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_HASH),
            (string) ($options['expectedC162HandoffAuditArchiveFinalClosureFileSha1'] ?? self::C162_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_SHA1),
            (string) ($options['output'] ?? $this->output),
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'post_handoff_boundary_confirmed' => (bool) ($options['postHandoffBoundaryConfirmed'] ?? true),
                'c162_handoff_audit_archive_chain_closed_confirmed' => (bool) ($options['c162HandoffAuditArchiveChainClosedConfirmed'] ?? true),
                'c162_terminal_no_next_confirmed' => (bool) ($options['c162TerminalNoNextConfirmed'] ?? true),
                'plan_confirm_unchanged_confirmed' => (bool) ($options['planConfirmUnchangedConfirmed'] ?? true),
                'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['noLivePlanConfirmRolloutConfirmed'] ?? true),
                'free_publication_locked_confirmed' => (bool) ($options['freePublicationLockedConfirmed'] ?? true),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C163_OPERATOR_APPROVED_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW'),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-18T00:00:00+00:00'),
            ]
        );
    }

    private function mutateC162FinalClosureAndExecute(callable $mutator, string $name): array
    {
        $c162 = json_decode((string) file_get_contents(self::C162_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_ARTIFACT), true);
        $c162 = $mutator(is_array($c162) ? $c162 : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c163-post-handoff-boundary-source-c162-final-closure-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($c162, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'c162HandoffAuditArchiveFinalClosureArtifact' => $path,
            'expectedC162HandoffAuditArchiveFinalClosureHash' => (string) ($c162['artifact_hash'] ?? ''),
            'expectedC162HandoffAuditArchiveFinalClosureFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
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
        foreach ((array) glob('storage/app/watchlist/backtest/c163-*post-handoff-boundary*-test.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/.tmp-c163-post-handoff-boundary*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/.tmp-runtime-c163-post-handoff-boundary-negative-*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}

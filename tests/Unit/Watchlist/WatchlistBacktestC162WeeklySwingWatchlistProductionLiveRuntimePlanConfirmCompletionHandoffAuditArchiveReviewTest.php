<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC162WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionHandoffAuditArchiveReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC162WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionHandoffAuditArchiveReviewTest extends TestCase
{
    private const C162_HANDOFF_CLOSURE_SEAL_ARTIFACT = 'storage/app/watchlist/backtest/c162-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-handoff-closure-seal-review.json';
    private const C162_HANDOFF_CLOSURE_SEAL_HASH = '4af51e55bf265dc7a6e60dcedf7ebb9b63efeba3';
    private const C162_HANDOFF_CLOSURE_SEAL_SHA1 = '7A75F138EF5DC73B3A58379DCF7173EC4EAABEC7';
    private const PASS_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_REVIEW_PASSED_AUDIT_ARCHIVED_PRIMARY_AND_BACKUP_READY_FOR_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW';
    private const NEXT_C162 = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW';
    private const APPROVAL_MISSING_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const AUDIT_ARCHIVE_MISSING_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_REVIEW_REJECTED_HANDOFF_AUDIT_ARCHIVE_CONFIRMATION_MISSING';
    private const C162_CLOSURE_SEAL_COMPLETE_MISSING_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_REVIEW_REJECTED_C162_HANDOFF_CLOSURE_SEAL_COMPLETE_CONFIRMATION_MISSING';
    private const CLOSURE_SEALED_MISSING_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_REVIEW_REJECTED_HANDOFF_CLOSURE_SEALED_CONFIRMATION_MISSING';
    private const PLAN_CONFIRM_UNCHANGED_MISSING_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_REVIEW_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING';
    private const NO_LIVE_ROLLOUT_MISSING_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_REVIEW_REJECTED_NO_LIVE_PLAN_CONFIRM_ROLLOUT_CONFIRMATION_MISSING';
    private const FREE_PUBLICATION_LOCK_MISSING_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_REVIEW_REJECTED_FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING';
    private const LOCK_MISMATCH_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_REVIEW_REJECTED_C162_HANDOFF_CLOSURE_SEAL_ARTIFACT_LOCK_MISMATCH';
    private const SHA1_MISMATCH_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_REVIEW_REJECTED_C162_HANDOFF_CLOSURE_SEAL_FILE_SHA1_LOCK_MISMATCH';
    private const C162_STATE_INVALID_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_REVIEW_REJECTED_C162_HANDOFF_CLOSURE_SEAL_STATE_INVALID';

    private string $output;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c162-plan-confirm-completion-handoff-audit-archive-review.json';
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

    public function test_c162_handoff_audit_archive_passes_and_advances_to_c162_audit_archive_completion(): void
    {
        $result = $this->runService();

        $this->assertSame('C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_REVIEW', $result['run_code']);
        $this->assertSame('PR-69 / C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_REVIEW', $result['phase_label']);
        $this->assertSame('C162_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE', $result['topic_code']);
        $this->assertSame('PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_REVIEW', $result['topic_stage']);
        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame(self::PASS_STATUS, $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_audit_archive_review_pass']);
        $this->assertTrue($result['production_live_runtime_plan_confirm_completion_handoff_audit_archive_review_pass']);
        $this->assertTrue($result['handoff_ready']);
        $this->assertTrue($result['handoff_finalized']);
        $this->assertTrue($result['handoff_completion_boundary_cleared']);
        $this->assertTrue($result['handoff_closure_sealed']);
        $this->assertTrue($result['handoff_audit_archived']);
        $this->assertTrue($result['handoff_audit_archive_confirmed']);
        $this->assertSame('HANDOFF_AUDIT_ARCHIVED_GO', $result['handoff_audit_archive_go_decision']);
        $this->assertTrue($result['c162_handoff_closure_seal_complete_confirmed']);
        $this->assertTrue($result['handoff_closure_sealed_confirmed']);
        $this->assertTrue($result['ready_for_weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_completion_review']);
        $this->assertSame(self::NEXT_C162, $result['next_step_recommendation']);
        $this->assertTrue($result['next_plan_confirm_completion_handoff_audit_archive_completion_decision']['topic_stage_advances_within_c162_handoff_after_audit_archive']);
        $this->assertTrue($result['next_plan_confirm_completion_handoff_audit_archive_completion_decision']['c162_handoff_audit_archive_complete']);
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
        $auditArchive = $this->runService(['handoffAuditArchiveConfirmed' => false]);
        $closureSealComplete = $this->runService(['c162HandoffClosureSealCompleteConfirmed' => false]);
        $closureSealed = $this->runService(['handoffClosureSealedConfirmed' => false]);
        $planUnchanged = $this->runService(['planConfirmUnchangedConfirmed' => false]);
        $noRollout = $this->runService(['noLivePlanConfirmRolloutConfirmed' => false]);
        $freeLock = $this->runService(['freePublicationLockedConfirmed' => false]);

        $this->assertSame(self::AUDIT_ARCHIVE_MISSING_STATUS, $auditArchive['status']);
        $this->assertSame(self::C162_CLOSURE_SEAL_COMPLETE_MISSING_STATUS, $closureSealComplete['status']);
        $this->assertSame(self::CLOSURE_SEALED_MISSING_STATUS, $closureSealed['status']);
        $this->assertSame(self::PLAN_CONFIRM_UNCHANGED_MISSING_STATUS, $planUnchanged['status']);
        $this->assertSame(self::NO_LIVE_ROLLOUT_MISSING_STATUS, $noRollout['status']);
        $this->assertSame(self::FREE_PUBLICATION_LOCK_MISSING_STATUS, $freeLock['status']);
    }

    public function test_c162_rejects_missing_or_mismatched_c162_handoff_closure_seal_lock(): void
    {
        $missing = $this->runService([
            'c162HandoffClosureSealArtifact' => 'storage/app/watchlist/backtest/.tmp-c162-source-missing.json',
            'expectedC162HandoffClosureSealHash' => 'missing',
            'expectedC162HandoffClosureSealFileSha1' => 'missing',
        ]);
        $hashMismatch = $this->runService(['expectedC162HandoffClosureSealHash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedC162HandoffClosureSealFileSha1' => 'BADSHA1']);

        $this->assertSame(self::LOCK_MISMATCH_STATUS, $missing['status']);
        $this->assertSame(self::LOCK_MISMATCH_STATUS, $hashMismatch['status']);
        $this->assertSame(self::SHA1_MISMATCH_STATUS, $shaMismatch['status']);
    }

    public function test_c162_rejects_c162_handoff_closure_seal_status_phase_or_next_recommendation_mismatch(): void
    {
        $status = $this->mutateClosureSealAndExecute(function (array $closureSeal): array {
            $closureSeal['status'] = 'BROKEN_STATUS';
            return $closureSeal;
        }, 'status-broken');
        $phase = $this->mutateClosureSealAndExecute(function (array $closureSeal): array {
            $closureSeal['phase_label'] = 'BROKEN_PHASE';
            return $closureSeal;
        }, 'phase-broken');
        $next = $this->mutateClosureSealAndExecute(function (array $closureSeal): array {
            $closureSeal['next_step_recommendation'] = 'BROKEN_NEXT';
            $closureSeal['next_plan_confirm_completion_handoff_audit_archive_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $closureSeal['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $closureSeal;
        }, 'next-broken');

        $this->assertSame('C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_REVIEW_REJECTED_C162_HANDOFF_CLOSURE_SEAL_STATUS_MISMATCH', $status['status']);
        $this->assertSame('C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_REVIEW_REJECTED_C162_HANDOFF_CLOSURE_SEAL_PHASE_LABEL_MISMATCH', $phase['status']);
        $this->assertSame('C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_REVIEW_REJECTED_C162_HANDOFF_CLOSURE_SEAL_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
    }

    public function test_c162_rejects_c162_handoff_closure_seal_convert_from_json_duplicate_top_level_keys(): void
    {
        $raw = (string) file_get_contents(self::C162_HANDOFF_CLOSURE_SEAL_ARTIFACT);
        $path = 'storage/app/watchlist/backtest/.tmp-c162-handoff-source-closure-seal-duplicate-key.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, preg_replace('/\{/', "{\"Run_Code\":\"DUPLICATE_CASE_KEY\",", $raw, 1));

        $result = $this->runService([
            'c162HandoffClosureSealArtifact' => $path,
            'expectedC162HandoffClosureSealHash' => self::C162_HANDOFF_CLOSURE_SEAL_HASH,
            'expectedC162HandoffClosureSealFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);

        $this->assertSame('C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_REVIEW_REJECTED_C162_HANDOFF_CLOSURE_SEAL_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION', $result['status']);
        $this->assertFalse($result['c162_handoff_closure_seal_convert_from_json_pass']);
    }

    /**
     * @dataProvider c162HandoffClosureSealStateMismatchProvider
     */
    public function test_c162_rejects_c162_handoff_closure_seal_state_mismatch(string $field, $value): void
    {
        $result = $this->mutateClosureSealAndExecute(function (array $closureSeal) use ($field, $value): array {
            $this->setValueAt($closureSeal, explode('.', $field), $value);
            return $closureSeal;
        }, 'state-'.str_replace('.', '-', $field));

        $this->assertSame(self::C162_STATE_INVALID_STATUS, $result['status'], $field);
    }

    public function c162HandoffClosureSealStateMismatchProvider(): array
    {
        return [
            ['weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_closure_seal_review_pass', false],
            ['handoff_closure_sealed', false],
            ['handoff_closure_seal_go_decision', 'NO_GO'],
            ['ready_for_weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_review', false],
            ['production_live_runtime_plan_confirm_completion_handoff_audit_archive_review_allowed_next', false],
            ['c162_plan_confirm_completion_handoff_closure_seal_review_only', false],
            ['controlled_completion_record_count', 0],
            ['controlled_completion_hash', ''],
            ['primary_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_review', false],
            ['backup_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_review', false],
            ['comparator_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_review', true],
            ['next_plan_confirm_completion_handoff_audit_archive_decision.c162_handoff_closure_seal_complete', false],
            ['weekly_swing_watchlist_plan_confirm_completion_handoff_closure_seal_manifest.ready_for_plan_confirm_completion_handoff_audit_archive_review', false],
            ['weekly_swing_watchlist_plan_confirm_completion_handoff_closure_seal_manifest.handoff_closure_seal_used_for_free_publication', true],
            ['weekly_swing_watchlist_plan_confirm_completion_handoff_closure_seal_checklist.artifact_only', false],
        ];
    }

    public function test_c162_rejects_publication_or_plan_confirm_mutation_from_c162_handoff_closure_seal(): void
    {
        $published = $this->mutateClosureSealAndExecute(function (array $closureSeal): array {
            $closureSeal['weekly_swing_watchlist_official_output_published'] = true;
            return $closureSeal;
        }, 'published');
        $publicationAllowed = $this->mutateClosureSealAndExecute(function (array $closureSeal): array {
            $closureSeal['weekly_swing_watchlist_publication_allowed'] = true;
            return $closureSeal;
        }, 'publication-allowed');
        $planConfirm = $this->mutateClosureSealAndExecute(function (array $closureSeal): array {
            $closureSeal['plan_confirm_mutated'] = true;
            return $closureSeal;
        }, 'plan-confirm-mutated');
        $liveRollout = $this->mutateClosureSealAndExecute(function (array $closureSeal): array {
            $closureSeal['live_plan_confirm_rollout_executed'] = true;
            return $closureSeal;
        }, 'live-rollout');

        $expected = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED';
        $this->assertSame($expected, $published['status']);
        $this->assertSame($expected, $publicationAllowed['status']);
        $this->assertSame($expected, $planConfirm['status']);
        $this->assertSame($expected, $liveRollout['status']);
    }

    public function test_c162_rejects_candidate_scope_change_or_a01_promotion(): void
    {
        $candidate = $this->mutateClosureSealAndExecute(function (array $closureSeal): array {
            $closureSeal['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $closureSeal;
        }, 'candidate-primary');
        $a01 = $this->mutateClosureSealAndExecute(function (array $closureSeal): array {
            $closureSeal['a01_promoted'] = true;
            return $closureSeal;
        }, 'candidate-a01');

        $this->assertSame('C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $candidate['status']);
        $this->assertSame('C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $a01['status']);
    }

    public function test_c162_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c162-handoff-audit-archive-negative-remains-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame('C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS', $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c162_records_source_locks_manifest_checklist_and_no_publication_or_rollout(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();
        $manifest = $result['weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_manifest'];
        $checklist = $result['weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_checklist'];

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $this->assertSame(self::C162_HANDOFF_CLOSURE_SEAL_HASH, $result['expected_c162_handoff_closure_seal_hash']);
        $this->assertSame(self::C162_HANDOFF_CLOSURE_SEAL_HASH, $result['actual_c162_handoff_closure_seal_hash']);
        $this->assertTrue($result['c162_handoff_closure_seal_hash_match']);
        $this->assertSame(self::C162_HANDOFF_CLOSURE_SEAL_SHA1, $result['expected_c162_handoff_closure_seal_file_sha1']);
        $this->assertSame(self::C162_HANDOFF_CLOSURE_SEAL_SHA1, $result['actual_c162_handoff_closure_seal_file_sha1']);
        $this->assertTrue($result['c162_handoff_closure_seal_file_sha1_match']);
        $this->assertTrue($result['c162_handoff_closure_seal_convert_from_json_pass']);
        $this->assertSame(self::NEXT_C162, $result['next_plan_confirm_completion_handoff_audit_archive_completion_decision']['next_recommendation']);
        $this->assertTrue($manifest['handoff_audit_archive_artifact_only']);
        $this->assertFalse($manifest['handoff_audit_archive_used_for_free_publication']);
        $this->assertFalse($manifest['handoff_audit_archive_used_for_plan_confirm_mutation']);
        $this->assertFalse($manifest['handoff_audit_archive_used_for_live_plan_confirm_rollout']);
        $this->assertTrue($checklist['artifact_only']);
        $this->assertFalse($checklist['weekly_swing_stock_recommendation_free_published_in_c162_handoff_audit_archive']);

        foreach ([
            'source_artifact_locks',
            'c162_handoff_closure_seal_lock_validation_summary',
            'c162_plan_confirm_completion_handoff_closure_seal_carry_forward_summary',
            'plan_confirm_completion_handoff_audit_archive_guard_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'c162_handoff_audit_archive_decision',
            'next_plan_confirm_completion_handoff_audit_archive_completion_decision',
            'weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_manifest',
            'weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_checklist',
            'c162_candidate_plan_confirm_completion_handoff_audit_archive_scorecard',
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

    private function runService(array $options = []): array
    {
        $service = new WatchlistBacktestC162WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionHandoffAuditArchiveReviewService();

        return $service->execute(
            (string) ($options['c162HandoffClosureSealArtifact'] ?? self::C162_HANDOFF_CLOSURE_SEAL_ARTIFACT),
            (string) ($options['expectedC162HandoffClosureSealHash'] ?? self::C162_HANDOFF_CLOSURE_SEAL_HASH),
            (string) ($options['expectedC162HandoffClosureSealFileSha1'] ?? self::C162_HANDOFF_CLOSURE_SEAL_SHA1),
            (string) ($options['output'] ?? $this->output),
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'handoff_audit_archive_confirmed' => (bool) ($options['handoffAuditArchiveConfirmed'] ?? true),
                'c162_handoff_closure_seal_complete_confirmed' => (bool) ($options['c162HandoffClosureSealCompleteConfirmed'] ?? true),
                'handoff_closure_sealed_confirmed' => (bool) ($options['handoffClosureSealedConfirmed'] ?? true),
                'plan_confirm_unchanged_confirmed' => (bool) ($options['planConfirmUnchangedConfirmed'] ?? true),
                'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['noLivePlanConfirmRolloutConfirmed'] ?? true),
                'free_publication_locked_confirmed' => (bool) ($options['freePublicationLockedConfirmed'] ?? true),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C162_OPERATOR_APPROVED_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_REVIEW_ONLY'),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-17T00:00:00+00:00'),
            ]
        );
    }

    private function mutateClosureSealAndExecute(callable $mutator, string $name): array
    {
        $closureSeal = json_decode((string) file_get_contents(self::C162_HANDOFF_CLOSURE_SEAL_ARTIFACT), true);
        $closureSeal = $mutator(is_array($closureSeal) ? $closureSeal : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c162-handoff-audit-archive-source-closure-seal-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($closureSeal, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'c162HandoffClosureSealArtifact' => $path,
            'expectedC162HandoffClosureSealHash' => (string) ($closureSeal['artifact_hash'] ?? ''),
            'expectedC162HandoffClosureSealFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
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
        foreach ((array) glob('storage/app/watchlist/backtest/c162-*handoff-audit-archive*-test.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/.tmp-c162-handoff-audit-archive*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/.tmp-c162-plan-confirm-completion-handoff-audit-archive-review*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}

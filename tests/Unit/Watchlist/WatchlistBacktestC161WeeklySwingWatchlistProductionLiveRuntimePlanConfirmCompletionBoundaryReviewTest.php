<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC161WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionBoundaryReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC161WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionBoundaryReviewTest extends TestCase
{
    private const C160_FINALIZATION_ARTIFACT = 'storage/app/watchlist/backtest/c160-weekly-swing-watchlist-production-live-runtime-plan-confirm-go-decision-finalization-review.json';
    private const C160_FINALIZATION_HASH = 'f6d2ca065099a5f07d7e6f53a3263b7b75293b2c';
    private const C160_FINALIZATION_SHA1 = 'B7F94670FC798F62B129AF76D87C1EAE9813B241';
    private const PASS_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW_PASSED_BOUNDARY_CLEARED_READY_FOR_COMPLETION_EXECUTION_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const BOUNDARY_CONFIRMATION_MISSING_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW_REJECTED_COMPLETION_BOUNDARY_CONFIRMATION_MISSING';
    private const C160_TOPIC_COMPLETE_MISSING_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW_REJECTED_C160_TOPIC_COMPLETE_CONFIRMATION_MISSING';
    private const PLAN_CONFIRM_CLOSED_MISSING_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW_REJECTED_PLAN_CONFIRM_CLOSED_CONFIRMATION_MISSING';
    private const PLAN_CONFIRM_UNCHANGED_MISSING_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING';
    private const NO_LIVE_ROLLOUT_MISSING_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW_REJECTED_NO_LIVE_PLAN_CONFIRM_ROLLOUT_CONFIRMATION_MISSING';
    private const FREE_PUBLICATION_LOCK_MISSING_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW_REJECTED_FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING';
    private const TEMP_NEGATIVE_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const LOCK_MISMATCH_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW_REJECTED_C160_FINALIZATION_ARTIFACT_LOCK_MISMATCH';
    private const SHA1_MISMATCH_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW_REJECTED_C160_FINALIZATION_FILE_SHA1_LOCK_MISMATCH';
    private const NEXT_C161_EXECUTION = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_EXECUTION';

    private string $output;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c161-plan-confirm-completion-boundary-review.json';
        $this->cleanupC161TemporaryArtifacts();
        @unlink($this->output);
    }

    protected function tearDown(): void
    {
        @unlink($this->output);
        foreach ($this->tmpFiles as $path) {
            @unlink($path);
        }
        $this->cleanupC161TemporaryArtifacts();
        parent::tearDown();
    }

    public function test_c161_completion_boundary_passes_and_keeps_next_inside_c161_execution(): void
    {
        $result = $this->runService();

        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW', $result['run_code']);
        $this->assertSame('PR-60 / C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW', $result['phase_label']);
        $this->assertSame('C161_PLAN_CONFIRM_COMPLETION', $result['topic_code']);
        $this->assertSame('PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW', $result['topic_stage']);
        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame(self::PASS_STATUS, $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_boundary_review_pass']);
        $this->assertTrue($result['production_live_runtime_plan_confirm_completion_boundary_review_pass']);
        $this->assertTrue($result['completion_boundary_cleared']);
        $this->assertTrue($result['completion_boundary_confirmed']);
        $this->assertTrue($result['c160_topic_complete_confirmed']);
        $this->assertTrue($result['plan_confirm_closed_confirmed']);
        $this->assertSame('BOUNDARY_CLEARED_GO', $result['boundary_go_decision']);
        $this->assertTrue($result['ready_for_weekly_swing_watchlist_plan_confirm_completion_execution']);
        $this->assertTrue($result['production_live_runtime_plan_confirm_completion_execution_allowed_next']);
        $this->assertSame(self::NEXT_C161_EXECUTION, $result['next_step_recommendation']);
        $this->assertSame(self::NEXT_C161_EXECUTION, $result['next_plan_confirm_completion_execution_decision']['next_recommendation']);
        $this->assertTrue($result['next_plan_confirm_completion_execution_decision']['same_topic_c161_continues']);
        $this->assertStringStartsWith('C161_', $result['next_step_recommendation']);
        $this->assertFileExists($this->output);
    }

    public function test_c161_completion_boundary_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->runService(['operatorApproved' => false]);
        $missingReference = $this->runService(['approvalReference' => '']);

        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingOperator['status']);
        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingReference['status']);
    }

    public function test_c161_completion_boundary_rejects_missing_required_confirmations(): void
    {
        $boundary = $this->runService(['completionBoundaryConfirmed' => false]);
        $topicComplete = $this->runService(['c160TopicCompleteConfirmed' => false]);
        $closed = $this->runService(['planConfirmClosedConfirmed' => false]);
        $unchanged = $this->runService(['planConfirmUnchangedConfirmed' => false]);
        $noRollout = $this->runService(['noLivePlanConfirmRolloutConfirmed' => false]);
        $freeLock = $this->runService(['freePublicationLockedConfirmed' => false]);

        $this->assertSame(self::BOUNDARY_CONFIRMATION_MISSING_STATUS, $boundary['status']);
        $this->assertSame(self::C160_TOPIC_COMPLETE_MISSING_STATUS, $topicComplete['status']);
        $this->assertSame(self::PLAN_CONFIRM_CLOSED_MISSING_STATUS, $closed['status']);
        $this->assertSame(self::PLAN_CONFIRM_UNCHANGED_MISSING_STATUS, $unchanged['status']);
        $this->assertSame(self::NO_LIVE_ROLLOUT_MISSING_STATUS, $noRollout['status']);
        $this->assertSame(self::FREE_PUBLICATION_LOCK_MISSING_STATUS, $freeLock['status']);
    }

    public function test_c161_completion_boundary_rejects_missing_or_mismatched_c160_finalization_artifact_lock(): void
    {
        $missing = $this->runService([
            'c160FinalizationArtifact' => 'storage/app/watchlist/backtest/.tmp-c161-source-missing.json',
            'expectedC160FinalizationHash' => 'missing',
            'expectedC160FinalizationFileSha1' => 'missing',
        ]);
        $hashMismatch = $this->runService(['expectedC160FinalizationHash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedC160FinalizationFileSha1' => 'BADSHA1']);

        $this->assertSame(self::LOCK_MISMATCH_STATUS, $missing['status']);
        $this->assertSame(self::LOCK_MISMATCH_STATUS, $hashMismatch['status']);
        $this->assertSame(self::SHA1_MISMATCH_STATUS, $shaMismatch['status']);
    }

    public function test_c161_completion_boundary_rejects_c160_status_phase_or_next_recommendation_mismatch(): void
    {
        $status = $this->mutateC160FinalizationAndExecute(function (array $c160): array {
            $c160['status'] = 'BROKEN_STATUS';
            return $c160;
        }, 'status-broken');
        $phase = $this->mutateC160FinalizationAndExecute(function (array $c160): array {
            $c160['phase_label'] = 'BROKEN_PHASE';
            return $c160;
        }, 'phase-broken');
        $next = $this->mutateC160FinalizationAndExecute(function (array $c160): array {
            $c160['next_step_recommendation'] = 'BROKEN_NEXT';
            $c160['next_plan_confirm_completion_boundary_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c160['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $c160;
        }, 'next-broken');

        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW_REJECTED_C160_FINALIZATION_STATUS_MISMATCH', $status['status']);
        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW_REJECTED_C160_FINALIZATION_PHASE_LABEL_MISMATCH', $phase['status']);
        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW_REJECTED_C160_FINALIZATION_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
    }

    public function test_c161_completion_boundary_rejects_c160_convert_from_json_duplicate_top_level_keys(): void
    {
        $raw = (string) file_get_contents(self::C160_FINALIZATION_ARTIFACT);
        $path = 'storage/app/watchlist/backtest/.tmp-c161-source-finalization-duplicate-key.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, preg_replace('/\{/', "{\"Status\":\"DUPLICATE_CASE_KEY\",", $raw, 1));

        $result = $this->runService([
            'c160FinalizationArtifact' => $path,
            'expectedC160FinalizationHash' => self::C160_FINALIZATION_HASH,
            'expectedC160FinalizationFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);

        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW_REJECTED_C160_FINALIZATION_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION', $result['status']);
        $this->assertFalse($result['c160_finalization_convert_from_json_pass']);
    }

    /**
     * @dataProvider c160FinalizationStateMismatchProvider
     */
    public function test_c161_completion_boundary_rejects_c160_finalization_state_mismatch(string $field, $value): void
    {
        $result = $this->mutateC160FinalizationAndExecute(function (array $c160) use ($field, $value): array {
            $this->setValueAt($c160, explode('.', $field), $value);
            return $c160;
        }, 'state-'.str_replace('.', '-', $field));

        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW_REJECTED_C160_FINALIZATION_STATE_INVALID', $result['status'], $field);
    }

    public function c160FinalizationStateMismatchProvider(): array
    {
        return [
            ['weekly_swing_watchlist_production_live_runtime_plan_confirm_go_decision_finalization_review_pass', false],
            ['operator_decision', 'NO_GO'],
            ['operator_go_decision', false],
            ['operator_go_decision_confirmed', false],
            ['go_decision_finalized', false],
            ['plan_confirm_closed', false],
            ['c160_topic_complete_after_finalization', false],
            ['ready_for_weekly_swing_watchlist_plan_confirm_completion_boundary_review', false],
            ['weekly_swing_watchlist_plan_confirm_go_decision_finalization_manifest_created', false],
            ['weekly_swing_watchlist_plan_confirm_controlled_only', false],
            ['c160_plan_confirm_go_decision_finalization_review_only', false],
            ['c160_go_decision_finalization_decision.review_valid', false],
            ['c160_go_decision_finalization_decision.go_decision_finalized', false],
            ['weekly_swing_watchlist_plan_confirm_go_decision_finalization_manifest.manifest_created', false],
            ['weekly_swing_watchlist_plan_confirm_go_decision_finalization_manifest.go_decision_finalization_artifact_only', false],
            ['weekly_swing_watchlist_plan_confirm_go_decision_finalization_manifest.ready_for_plan_confirm_completion_boundary_review', false],
            ['weekly_swing_watchlist_plan_confirm_go_decision_finalization_checklist.go_decision_finalization_reviewed', false],
            ['weekly_swing_watchlist_plan_confirm_go_decision_finalization_checklist.artifact_only', false],
            ['next_plan_confirm_completion_boundary_decision.review_valid', false],
            ['next_plan_confirm_completion_boundary_decision.same_topic_c160_complete', false],
        ];
    }

    public function test_c161_completion_boundary_rejects_publication_or_plan_confirm_mutation_from_c160_finalization(): void
    {
        $published = $this->mutateC160FinalizationAndExecute(function (array $c160): array {
            $c160['weekly_swing_watchlist_official_output_published'] = true;
            return $c160;
        }, 'published');
        $publicationAllowed = $this->mutateC160FinalizationAndExecute(function (array $c160): array {
            $c160['weekly_swing_watchlist_publication_allowed'] = true;
            return $c160;
        }, 'publication-allowed');
        $planConfirm = $this->mutateC160FinalizationAndExecute(function (array $c160): array {
            $c160['plan_confirm_mutated'] = true;
            return $c160;
        }, 'plan-confirm');
        $liveRollout = $this->mutateC160FinalizationAndExecute(function (array $c160): array {
            $c160['live_plan_confirm_rollout_executed'] = true;
            return $c160;
        }, 'live-rollout');

        $expected = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED';
        $this->assertSame($expected, $published['status']);
        $this->assertSame($expected, $publicationAllowed['status']);
        $this->assertSame($expected, $planConfirm['status']);
        $this->assertSame($expected, $liveRollout['status']);
    }

    public function test_c161_completion_boundary_rejects_candidate_scope_change_or_a01_promotion(): void
    {
        $candidate = $this->mutateC160FinalizationAndExecute(function (array $c160): array {
            $c160['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $c160;
        }, 'candidate-primary');
        $a01 = $this->mutateC160FinalizationAndExecute(function (array $c160): array {
            $c160['a01_promoted'] = true;
            return $c160;
        }, 'candidate-a01');

        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $candidate['status']);
        $this->assertSame('C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $a01['status']);
    }

    public function test_c161_completion_boundary_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c161-completion-boundary-negative-remains-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame(self::TEMP_NEGATIVE_STATUS, $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c161_completion_boundary_records_source_locks_manifest_checklist_and_no_publication_or_rollout(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();
        $manifest = $result['weekly_swing_watchlist_plan_confirm_completion_boundary_manifest'];
        $checklist = $result['weekly_swing_watchlist_plan_confirm_completion_boundary_checklist'];

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $this->assertSame(self::C160_FINALIZATION_HASH, $result['expected_c160_finalization_hash']);
        $this->assertSame(self::C160_FINALIZATION_HASH, $result['actual_c160_finalization_hash']);
        $this->assertTrue($result['c160_finalization_hash_match']);
        $this->assertSame(self::C160_FINALIZATION_SHA1, $result['expected_c160_finalization_file_sha1']);
        $this->assertSame(self::C160_FINALIZATION_SHA1, $result['actual_c160_finalization_file_sha1']);
        $this->assertTrue($result['c160_finalization_file_sha1_match']);
        $this->assertTrue($result['c160_finalization_convert_from_json_pass']);

        foreach ([
            'source_artifact_locks',
            'c160_finalization_lock_validation_summary',
            'c160_finalization_carry_forward_summary',
            'plan_confirm_completion_boundary_guard_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'c161_completion_boundary_decision',
            'next_plan_confirm_completion_execution_decision',
            'weekly_swing_watchlist_plan_confirm_completion_boundary_manifest',
            'weekly_swing_watchlist_plan_confirm_completion_boundary_checklist',
            'c161_candidate_plan_confirm_completion_boundary_scorecard',
            'publication_plan_confirm_safety_summary',
            'documentation_hygiene_guard_summary',
            'progress_summary',
            'planned_next_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }

        $this->assertTrue($manifest['boundary_artifact_only']);
        $this->assertTrue($manifest['go_decision_finalized']);
        $this->assertTrue($manifest['plan_confirm_closed']);
        $this->assertTrue($manifest['completion_boundary_cleared']);
        $this->assertTrue($manifest['ready_for_plan_confirm_completion_execution']);
        $this->assertFalse($manifest['official_output_published']);
        $this->assertFalse($manifest['free_publication_allowed']);
        $this->assertFalse($manifest['completion_boundary_used_for_plan_confirm_mutation']);
        $this->assertFalse($manifest['completion_boundary_used_for_free_publication']);
        $this->assertFalse($manifest['completion_boundary_used_for_live_plan_confirm_rollout']);
        $this->assertTrue($checklist['completion_boundary_reviewed']);
        $this->assertTrue($checklist['artifact_only']);
        $this->assertFalse($checklist['weekly_swing_stock_recommendation_free_published_in_c161_boundary']);
    }

    public function test_c161_completion_boundary_keeps_e02_primary_b01_backup_a01_comparator_and_safety_flags_false(): void
    {
        $result = $this->runService();
        $scorecard = $result['c161_candidate_plan_confirm_completion_boundary_scorecard'];

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $result['primary_candidate_code']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $result['backup_candidate_code']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $result['comparator_candidate_code']);
        $this->assertTrue($result['primary_candidate_ready_for_plan_confirm_completion_execution']);
        $this->assertTrue($result['backup_candidate_ready_for_plan_confirm_completion_execution']);
        $this->assertFalse($result['comparator_candidate_ready_for_plan_confirm_completion_execution']);
        $this->assertTrue($result['a01_remains_comparator_only']);
        $this->assertTrue($scorecard[0]['primary_candidate_ready_for_plan_confirm_completion_execution']);
        $this->assertTrue($scorecard[1]['backup_candidate_ready_for_plan_confirm_completion_execution']);
        $this->assertFalse($scorecard[2]['ready_for_plan_confirm_completion_execution']);

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

    public function test_c161_completion_boundary_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $first = $this->runService(['createdAt' => '2026-07-16T00:00:00+00:00']);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c161-plan-confirm-completion-boundary-review-second.json';
        $this->tmpFiles[] = $secondOutput;
        $second = $this->runService([
            'output' => $secondOutput,
            'createdAt' => '2026-07-16T00:00:00+00:00',
        ]);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
    }

    public function test_c161_completion_boundary_does_not_mutate_c160_finalization_artifact_or_config_defaults(): void
    {
        $beforeFinalization = strtoupper(sha1((string) file_get_contents(self::C160_FINALIZATION_ARTIFACT)));
        $beforeConfig = (string) file_get_contents('config/watchlist.php');

        $this->runService();

        $this->assertSame($beforeFinalization, strtoupper(sha1((string) file_get_contents(self::C160_FINALIZATION_ARTIFACT))));
        $this->assertSame($beforeConfig, (string) file_get_contents('config/watchlist.php'));
    }

    private function runService(array $options = []): array
    {
        $service = new WatchlistBacktestC161WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionBoundaryReviewService();

        return $service->execute(
            (string) ($options['c160FinalizationArtifact'] ?? self::C160_FINALIZATION_ARTIFACT),
            (string) ($options['expectedC160FinalizationHash'] ?? self::C160_FINALIZATION_HASH),
            (string) ($options['expectedC160FinalizationFileSha1'] ?? self::C160_FINALIZATION_SHA1),
            (string) ($options['output'] ?? $this->output),
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'completion_boundary_confirmed' => (bool) ($options['completionBoundaryConfirmed'] ?? true),
                'c160_topic_complete_confirmed' => (bool) ($options['c160TopicCompleteConfirmed'] ?? true),
                'plan_confirm_closed_confirmed' => (bool) ($options['planConfirmClosedConfirmed'] ?? true),
                'plan_confirm_unchanged_confirmed' => (bool) ($options['planConfirmUnchangedConfirmed'] ?? true),
                'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['noLivePlanConfirmRolloutConfirmed'] ?? true),
                'free_publication_locked_confirmed' => (bool) ($options['freePublicationLockedConfirmed'] ?? true),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C161_OPERATOR_APPROVED_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW_ONLY'),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-16T00:00:00+00:00'),
            ]
        );
    }

    private function mutateC160FinalizationAndExecute(callable $mutator, string $name): array
    {
        $c160 = json_decode((string) file_get_contents(self::C160_FINALIZATION_ARTIFACT), true);
        $c160 = $mutator(is_array($c160) ? $c160 : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c161-source-finalization-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($c160, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'c160FinalizationArtifact' => $path,
            'expectedC160FinalizationHash' => (string) ($c160['artifact_hash'] ?? ''),
            'expectedC160FinalizationFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
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

    private function cleanupC161TemporaryArtifacts(): void
    {
        foreach ((array) glob('storage/app/watchlist/backtest/c161-*completion-boundary*-test.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/c161-*negative-*-test.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/c161-*missing-*-test.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/.tmp-c161-plan-confirm-completion-boundary-review*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/.tmp-c161-source-finalization-*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}

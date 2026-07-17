<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC157WeeklySwingWatchlistProductionLiveRuntimeControlledOutputGenerationGoDecisionFinalizationReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC157WeeklySwingWatchlistProductionLiveRuntimeControlledOutputGenerationGoDecisionFinalizationReviewTest extends TestCase
{
    private const C156_ARTIFACT = 'storage/app/watchlist/backtest/c156-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-operator-go-no-go-review.json';
    private const C156_HASH = 'f36edcf84b291dd58119caf4e003c00ced404311';
    private const C156_SHA1 = 'A7165F0FB30111B313783A1FD3DE77992BD39E99';
    private const PASS_STATUS = 'C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW_PASSED_GO_FINALIZED_READY_FOR_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const GO_FINALIZATION_NOT_CONFIRMED_STATUS = 'C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_GO_DECISION_FINALIZATION_CONFIRMATION_MISSING';
    private const NO_PUBLICATION_CONFIRMATION_MISSING_STATUS = 'C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_NO_PUBLICATION_CONFIRMATION_MISSING';
    private const PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING_STATUS = 'C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING';
    private const TEMP_NEGATIVE_STATUS = 'C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const NEXT_C158 = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW';

    private string $output;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c157-controlled-output-generation-go-decision-finalization.json';
        $this->cleanupC157TemporaryArtifacts();
        @unlink($this->output);
    }

    protected function tearDown(): void
    {
        @unlink($this->output);
        foreach ($this->tmpFiles as $path) {
            @unlink($path);
        }
        $this->cleanupC157TemporaryArtifacts();
        parent::tearDown();
    }

    public function test_c157_passes_with_valid_c156_lock_operator_approval_and_all_confirmations(): void
    {
        $result = $this->runService();

        $this->assertSame('C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW', $result['run_code']);
        $this->assertSame('PR-45 / C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW', $result['phase_label']);
        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame(self::PASS_STATUS, $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_production_live_runtime_controlled_output_generation_go_decision_finalization_review_pass']);
        $this->assertTrue($result['production_live_runtime_controlled_output_generation_go_decision_finalization_review_pass']);
        $this->assertSame('GO', $result['operator_go_decision']);
        $this->assertTrue($result['operator_go_decision_confirmed']);
        $this->assertTrue($result['go_decision_finalized']);
        $this->assertTrue($result['go_decision_finalization_confirmed']);
        $this->assertTrue($result['no_publication_confirmed']);
        $this->assertTrue($result['plan_confirm_unchanged_confirmed']);
        $this->assertTrue($result['ready_for_weekly_swing_watchlist_controlled_output_publication_boundary_review']);
        $this->assertTrue($result['production_live_runtime_controlled_output_publication_boundary_review_allowed_next']);
        $this->assertFalse($result['weekly_swing_watchlist_official_output_published']);
        $this->assertFalse($result['weekly_swing_watchlist_publication_allowed']);
        $this->assertFalse($result['weekly_swing_watchlist_unrestricted_publication_allowed']);
        $this->assertFalse($result['plan_confirm_mutated']);
        $this->assertTrue($result['c156_lock_valid']);
        $this->assertTrue($result['c156_operator_go_no_go_review_valid']);
        $this->assertTrue($result['controlled_output_lock_valid']);
        $this->assertTrue($result['controlled_output_integrity_valid']);
        $this->assertSame(self::NEXT_C158, $result['next_step_recommendation']);
        $this->assertFileExists($this->output);
    }

    public function test_c157_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->runService(['operatorApproved' => false]);
        $missingReference = $this->runService(['approvalReference' => '']);

        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingOperator['status']);
        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingReference['status']);
    }

    public function test_c157_rejects_missing_required_confirmations(): void
    {
        $go = $this->runService(['goDecisionFinalizationConfirmed' => false]);
        $noPublication = $this->runService(['noPublicationConfirmed' => false]);
        $planConfirm = $this->runService(['planConfirmUnchangedConfirmed' => false]);

        $this->assertSame(self::GO_FINALIZATION_NOT_CONFIRMED_STATUS, $go['status']);
        $this->assertSame(self::NO_PUBLICATION_CONFIRMATION_MISSING_STATUS, $noPublication['status']);
        $this->assertSame(self::PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING_STATUS, $planConfirm['status']);
    }

    public function test_c157_rejects_missing_or_mismatched_c156_artifact_lock(): void
    {
        $missing = $this->runService([
            'c156Artifact' => 'storage/app/watchlist/backtest/.tmp-c157-source-c156-missing.json',
            'expectedC156Hash' => 'missing',
            'expectedC156FileSha1' => 'missing',
        ]);
        $hashMismatch = $this->runService(['expectedC156Hash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedC156FileSha1' => 'BADSHA1']);

        $this->assertSame('C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C156_ARTIFACT_LOCK_MISMATCH', $missing['status']);
        $this->assertSame('C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C156_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C156_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
    }

    public function test_c157_rejects_c156_status_phase_or_next_recommendation_mismatch(): void
    {
        $status = $this->mutateC156AndExecute(function (array $c156): array {
            $c156['status'] = 'BROKEN_STATUS';
            return $c156;
        }, 'status-broken');
        $phase = $this->mutateC156AndExecute(function (array $c156): array {
            $c156['phase_label'] = 'BROKEN_PHASE';
            return $c156;
        }, 'phase-broken');
        $next = $this->mutateC156AndExecute(function (array $c156): array {
            $c156['next_step_recommendation'] = 'BROKEN_NEXT';
            $c156['c156_operator_go_no_go_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c156['next_concrete_controlled_output_step_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c156['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $c156;
        }, 'next-broken');

        $this->assertSame('C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C156_STATUS_MISMATCH', $status['status']);
        $this->assertSame('C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C156_PHASE_LABEL_MISMATCH', $phase['status']);
        $this->assertSame('C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C156_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
    }

    public function test_c157_rejects_c156_convert_from_json_duplicate_top_level_keys(): void
    {
        $raw = (string) file_get_contents(self::C156_ARTIFACT);
        $path = 'storage/app/watchlist/backtest/.tmp-c157-source-c156-duplicate-key.json';
        $this->tmpFiles[] = $path;
        $duplicateRaw = preg_replace('/\{/', "{\"Run_Code\":\"DUPLICATE_CASE_KEY\",", $raw, 1);
        file_put_contents($path, $duplicateRaw);

        $result = $this->runService([
            'c156Artifact' => $path,
            'expectedC156Hash' => self::C156_HASH,
            'expectedC156FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);

        $this->assertSame('C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C156_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION', $result['status']);
        $this->assertFalse($result['c156_convert_from_json_pass']);
    }

    /**
     * @dataProvider c156OperatorGoMismatchProvider
     */
    public function test_c157_rejects_c156_operator_go_state_mismatch(string $field, $value): void
    {
        $result = $this->mutateC156AndExecute(function (array $c156) use ($field, $value): array {
            $this->setValueAt($c156, explode('.', $field), $value);
            return $c156;
        }, 'operator-go-'.str_replace('.', '-', $field));

        $this->assertSame('C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C156_OPERATOR_GO_INVALID', $result['status'], $field);
    }

    public function c156OperatorGoMismatchProvider(): array
    {
        return [
            ['weekly_swing_watchlist_production_live_runtime_controlled_output_generation_operator_go_no_go_review_pass', false],
            ['operator_decision', 'NO_GO'],
            ['operator_go_decision', 'NO_GO'],
            ['operator_decision_confirmed', false],
            ['operator_decision_reason', ''],
            ['ready_for_weekly_swing_watchlist_controlled_output_generation_go_decision_finalization_review', false],
            ['production_live_runtime_controlled_output_generation_go_decision_finalization_review_allowed_next', false],
            ['controlled_output_generation_operator_go_no_go_manifest_created', false],
            ['c155_lock_valid', false],
            ['controlled_output_lock_valid', false],
            ['controlled_output_integrity_valid', false],
            ['primary_candidate_ready_for_controlled_output_generation_go_decision_finalization_review', false],
            ['backup_candidate_ready_for_controlled_output_generation_go_decision_finalization_review', false],
            ['comparator_candidate_ready_for_controlled_output_generation_go_decision_finalization_review', true],
            ['c156_operator_go_no_go_decision.review_valid', false],
            ['c156_operator_go_no_go_decision.operator_decision', 'HOLD'],
            ['weekly_swing_watchlist_controlled_output_generation_operator_go_no_go_manifest.operator_decision', 'NO_GO'],
            ['weekly_swing_watchlist_controlled_output_generation_operator_go_no_go_manifest.operator_go_no_go_used_for_publication', true],
            ['weekly_swing_watchlist_controlled_output_generation_operator_go_no_go_checklist.artifact_only', false],
        ];
    }

    public function test_c157_rejects_publication_or_plan_confirm_mutation_from_c156(): void
    {
        $published = $this->mutateC156AndExecute(function (array $c156): array {
            $c156['weekly_swing_watchlist_official_output_published'] = true;
            return $c156;
        }, 'published');
        $publicationAllowed = $this->mutateC156AndExecute(function (array $c156): array {
            $c156['weekly_swing_watchlist_publication_allowed'] = true;
            return $c156;
        }, 'publication-allowed');
        $planConfirm = $this->mutateC156AndExecute(function (array $c156): array {
            $c156['plan_confirm_mutated'] = true;
            return $c156;
        }, 'plan-confirm');

        $this->assertSame('C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED', $published['status']);
        $this->assertSame('C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED', $publicationAllowed['status']);
        $this->assertSame('C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED', $planConfirm['status']);
    }

    public function test_c157_rejects_candidate_scope_change_or_a01_promotion(): void
    {
        $candidate = $this->mutateC156AndExecute(function (array $c156): array {
            $c156['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $c156;
        }, 'candidate-primary');
        $a01 = $this->mutateC156AndExecute(function (array $c156): array {
            $c156['a01_promoted'] = true;
            return $c156;
        }, 'candidate-a01');

        $this->assertSame('C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $candidate['status']);
        $this->assertSame('C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $a01['status']);
    }

    public function test_c157_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c157-no-temporary-artifact-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame(self::TEMP_NEGATIVE_STATUS, $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c157_records_source_locks_manifest_checklist_and_no_publication(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();
        $manifest = $result['weekly_swing_watchlist_controlled_output_generation_go_decision_finalization_manifest'];
        $checklist = $result['weekly_swing_watchlist_controlled_output_generation_go_decision_finalization_checklist'];

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $this->assertSame(self::C156_HASH, $result['expected_c156_hash']);
        $this->assertSame(self::C156_HASH, $result['actual_c156_hash']);
        $this->assertTrue($result['c156_hash_match']);
        $this->assertSame(self::C156_SHA1, $result['expected_c156_file_sha1']);
        $this->assertSame(self::C156_SHA1, $result['actual_c156_file_sha1']);
        $this->assertTrue($result['c156_file_sha1_match']);
        $this->assertTrue($result['c156_convert_from_json_pass']);
        $this->assertSame(self::NEXT_C158, $result['next_controlled_output_publication_boundary_decision']['next_recommendation']);

        foreach ([
            'source_artifact_locks',
            'c156_lock_validation_summary',
            'c156_operator_go_no_go_carry_forward_summary',
            'controlled_output_publication_guard_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'c157_go_decision_finalization_decision',
            'next_controlled_output_publication_boundary_decision',
            'weekly_swing_watchlist_controlled_output_generation_go_decision_finalization_manifest',
            'weekly_swing_watchlist_controlled_output_generation_go_decision_finalization_checklist',
            'c157_candidate_controlled_output_go_decision_finalization_scorecard',
            'controlled_output_generation_go_decision_finalization_context_summary',
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
        $this->assertTrue($manifest['controlled_output_generation_go_decision_finalization_review_pass']);
        $this->assertTrue($manifest['ready_for_controlled_output_publication_boundary_review']);
        $this->assertFalse($manifest['official_output_published']);
        $this->assertFalse($manifest['publication_allowed']);
        $this->assertFalse($manifest['go_decision_finalization_used_for_plan_confirm_mutation']);
        $this->assertFalse($manifest['go_decision_finalization_used_for_publication']);
        $this->assertTrue($checklist['go_decision_finalization_reviewed']);
        $this->assertTrue($checklist['artifact_only']);
        $this->assertFalse($checklist['weekly_swing_stock_recommendation_published_in_c157']);
    }

    public function test_c157_keeps_e02_primary_b01_backup_a01_comparator_and_publication_flags_false(): void
    {
        $result = $this->runService();
        $scorecard = $result['c157_candidate_controlled_output_go_decision_finalization_scorecard'];

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $result['primary_candidate_code']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $result['backup_candidate_code']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $result['comparator_candidate_code']);
        $this->assertTrue($result['primary_candidate_ready_for_controlled_output_publication_boundary_review']);
        $this->assertTrue($result['backup_candidate_ready_for_controlled_output_publication_boundary_review']);
        $this->assertFalse($result['comparator_candidate_ready_for_controlled_output_publication_boundary_review']);
        $this->assertTrue($result['a01_remains_comparator_only']);
        $this->assertTrue($scorecard[0]['primary_candidate_ready_for_controlled_output_publication_boundary_review']);
        $this->assertTrue($scorecard[1]['backup_candidate_ready_for_controlled_output_publication_boundary_review']);
        $this->assertFalse($scorecard[2]['ready_for_controlled_output_publication_boundary_review']);

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

    public function test_c157_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $first = $this->runService(['createdAt' => '2026-07-15T00:00:00+00:00']);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c157-controlled-output-generation-go-decision-finalization-second.json';
        $this->tmpFiles[] = $secondOutput;
        $second = $this->runService([
            'output' => $secondOutput,
            'createdAt' => '2026-07-15T00:00:00+00:00',
        ]);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
    }

    public function test_c157_does_not_mutate_c156_artifact_or_config_defaults(): void
    {
        $beforeC156 = strtoupper(sha1((string) file_get_contents(self::C156_ARTIFACT)));
        $beforeConfig = (string) file_get_contents('config/watchlist.php');

        $this->runService();

        $this->assertSame($beforeC156, strtoupper(sha1((string) file_get_contents(self::C156_ARTIFACT))));
        $this->assertSame($beforeConfig, (string) file_get_contents('config/watchlist.php'));
    }

    private function runService(array $options = []): array
    {
        $service = new WatchlistBacktestC157WeeklySwingWatchlistProductionLiveRuntimeControlledOutputGenerationGoDecisionFinalizationReviewService();

        return $service->execute(
            (string) ($options['c156Artifact'] ?? self::C156_ARTIFACT),
            (string) ($options['expectedC156Hash'] ?? self::C156_HASH),
            (string) ($options['expectedC156FileSha1'] ?? self::C156_SHA1),
            (string) ($options['output'] ?? $this->output),
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'go_decision_finalization_confirmed' => (bool) ($options['goDecisionFinalizationConfirmed'] ?? true),
                'no_publication_confirmed' => (bool) ($options['noPublicationConfirmed'] ?? true),
                'plan_confirm_unchanged_confirmed' => (bool) ($options['planConfirmUnchangedConfirmed'] ?? true),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C157_OPERATOR_APPROVED_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW_ONLY'),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-15T00:00:00+00:00'),
            ]
        );
    }

    private function mutateC156AndExecute(callable $mutator, string $name): array
    {
        $c156 = json_decode((string) file_get_contents(self::C156_ARTIFACT), true);
        $c156 = $mutator(is_array($c156) ? $c156 : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c157-source-c156-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($c156, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'c156Artifact' => $path,
            'expectedC156Hash' => (string) ($c156['artifact_hash'] ?? ''),
            'expectedC156FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
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

    private function cleanupC157TemporaryArtifacts(): void
    {
        foreach ((array) glob('storage/app/watchlist/backtest/c157-*test.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/.tmp-c157*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}

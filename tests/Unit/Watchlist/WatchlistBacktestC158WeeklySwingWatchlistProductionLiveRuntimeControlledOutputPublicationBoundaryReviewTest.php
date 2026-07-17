<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC158WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationBoundaryReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC158WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationBoundaryReviewTest extends TestCase
{
    private const C157_ARTIFACT = 'storage/app/watchlist/backtest/c157-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-go-decision-finalization-review.json';
    private const C157_HASH = '36f8aadb64d1994bde030efcfec985c7fd0df411';
    private const C157_SHA1 = 'E3B40E1080F3C3CCE5E39E0A660E38937F25A68B';
    private const PASS_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_PASSED_READY_FOR_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const PUBLICATION_BOUNDARY_CONFIRMATION_MISSING_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_REJECTED_PUBLICATION_BOUNDARY_CONFIRMATION_MISSING';
    private const CONTROLLED_PUBLICATION_ONLY_CONFIRMATION_MISSING_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_REJECTED_CONTROLLED_PUBLICATION_ONLY_CONFIRMATION_MISSING';
    private const PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING';
    private const TEMP_NEGATIVE_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const NEXT_C158_EXECUTION = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION';

    private string $output;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c158-controlled-output-publication-boundary-review.json';
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

    public function test_c158_boundary_passes_and_keeps_same_topic_number_for_execution(): void
    {
        $result = $this->runService();

        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW', $result['run_code']);
        $this->assertSame('PR-46 / C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW', $result['phase_label']);
        $this->assertSame('C158_CONTROLLED_OUTPUT_PUBLICATION', $result['topic_code']);
        $this->assertSame('BOUNDARY_REVIEW', $result['topic_stage']);
        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame(self::PASS_STATUS, $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_production_live_runtime_controlled_output_publication_boundary_review_pass']);
        $this->assertTrue($result['production_live_runtime_controlled_output_publication_boundary_review_pass']);
        $this->assertTrue($result['ready_for_weekly_swing_watchlist_controlled_output_publication_execution']);
        $this->assertTrue($result['production_live_runtime_controlled_output_publication_execution_allowed_next']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_output_publication_execution_allowed_next']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_publication_allowed_next']);
        $this->assertFalse($result['weekly_swing_watchlist_official_output_published']);
        $this->assertFalse($result['weekly_swing_watchlist_publication_allowed']);
        $this->assertFalse($result['weekly_swing_watchlist_unrestricted_publication_allowed']);
        $this->assertFalse($result['plan_confirm_mutated']);
        $this->assertTrue($result['c157_lock_valid']);
        $this->assertTrue($result['c157_go_decision_finalization_valid']);
        $this->assertTrue($result['c158_topic_number_retained_for_execution']);
        $this->assertSame(self::NEXT_C158_EXECUTION, $result['next_step_recommendation']);
        $this->assertFileExists($this->output);
    }

    public function test_c158_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->runService(['operatorApproved' => false]);
        $missingReference = $this->runService(['approvalReference' => '']);

        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingOperator['status']);
        $this->assertSame(self::APPROVAL_MISSING_STATUS, $missingReference['status']);
    }

    public function test_c158_rejects_missing_required_boundary_confirmations(): void
    {
        $boundary = $this->runService(['publicationBoundaryConfirmed' => false]);
        $controlledOnly = $this->runService(['controlledPublicationOnlyConfirmed' => false]);
        $planConfirm = $this->runService(['planConfirmUnchangedConfirmed' => false]);

        $this->assertSame(self::PUBLICATION_BOUNDARY_CONFIRMATION_MISSING_STATUS, $boundary['status']);
        $this->assertSame(self::CONTROLLED_PUBLICATION_ONLY_CONFIRMATION_MISSING_STATUS, $controlledOnly['status']);
        $this->assertSame(self::PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING_STATUS, $planConfirm['status']);
    }

    public function test_c158_rejects_missing_or_mismatched_c157_artifact_lock(): void
    {
        $missing = $this->runService([
            'c157Artifact' => 'storage/app/watchlist/backtest/.tmp-c158-source-c157-missing.json',
            'expectedC157Hash' => 'missing',
            'expectedC157FileSha1' => 'missing',
        ]);
        $hashMismatch = $this->runService(['expectedC157Hash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedC157FileSha1' => 'BADSHA1']);

        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_REJECTED_C157_ARTIFACT_LOCK_MISMATCH', $missing['status']);
        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_REJECTED_C157_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_REJECTED_C157_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
    }

    public function test_c158_rejects_c157_status_phase_or_next_recommendation_mismatch(): void
    {
        $status = $this->mutateC157AndExecute(function (array $c157): array {
            $c157['status'] = 'BROKEN_STATUS';
            return $c157;
        }, 'status-broken');
        $phase = $this->mutateC157AndExecute(function (array $c157): array {
            $c157['phase_label'] = 'BROKEN_PHASE';
            return $c157;
        }, 'phase-broken');
        $next = $this->mutateC157AndExecute(function (array $c157): array {
            $c157['next_step_recommendation'] = 'BROKEN_NEXT';
            $c157['c157_go_decision_finalization_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c157['next_controlled_output_publication_boundary_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c157['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $c157;
        }, 'next-broken');

        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_REJECTED_C157_STATUS_MISMATCH', $status['status']);
        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_REJECTED_C157_PHASE_LABEL_MISMATCH', $phase['status']);
        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_REJECTED_C157_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
    }

    public function test_c158_rejects_c157_convert_from_json_duplicate_top_level_keys(): void
    {
        $raw = (string) file_get_contents(self::C157_ARTIFACT);
        $path = 'storage/app/watchlist/backtest/.tmp-c158-source-c157-duplicate-key.json';
        $this->tmpFiles[] = $path;
        $duplicateRaw = preg_replace('/\{/', "{\"Run_Code\":\"DUPLICATE_CASE_KEY\",", $raw, 1);
        file_put_contents($path, $duplicateRaw);

        $result = $this->runService([
            'c157Artifact' => $path,
            'expectedC157Hash' => self::C157_HASH,
            'expectedC157FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);

        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_REJECTED_C157_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION', $result['status']);
        $this->assertFalse($result['c157_convert_from_json_pass']);
    }

    /**
     * @dataProvider c157GoFinalizationMismatchProvider
     */
    public function test_c158_rejects_incomplete_c157_go_finalization_evidence(string $field, $value): void
    {
        $result = $this->mutateC157AndExecute(function (array $c157) use ($field, $value): array {
            $this->setValueAt($c157, explode('.', $field), $value);
            return $c157;
        }, 'go-finalization-'.str_replace('.', '-', $field));

        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_REJECTED_C157_GO_FINALIZATION_INCOMPLETE', $result['status'], $field);
    }

    public function c157GoFinalizationMismatchProvider(): array
    {
        return [
            ['weekly_swing_watchlist_production_live_runtime_controlled_output_generation_go_decision_finalization_review_pass', false],
            ['operator_go_decision', 'NO_GO'],
            ['go_decision_finalized', false],
            ['ready_for_weekly_swing_watchlist_controlled_output_publication_boundary_review', false],
            ['production_live_runtime_controlled_output_publication_boundary_review_allowed_next', false],
            ['controlled_output_generation_go_decision_finalization_manifest_created', false],
            ['controlled_output_lock_valid', false],
            ['controlled_output_integrity_valid', false],
            ['primary_candidate_ready_for_controlled_output_publication_boundary_review', false],
            ['backup_candidate_ready_for_controlled_output_publication_boundary_review', false],
            ['comparator_candidate_ready_for_controlled_output_publication_boundary_review', true],
            ['c157_go_decision_finalization_decision.review_valid', false],
            ['c157_go_decision_finalization_decision.operator_go_decision', 'NO_GO'],
            ['weekly_swing_watchlist_controlled_output_generation_go_decision_finalization_manifest.go_decision_finalization_artifact_only', false],
            ['weekly_swing_watchlist_controlled_output_generation_go_decision_finalization_checklist.artifact_only', false],
        ];
    }

    public function test_c158_rejects_publication_or_plan_confirm_mutation_from_c157(): void
    {
        $published = $this->mutateC157AndExecute(function (array $c157): array {
            $c157['weekly_swing_watchlist_official_output_published'] = true;
            return $c157;
        }, 'published');
        $publicationAllowed = $this->mutateC157AndExecute(function (array $c157): array {
            $c157['weekly_swing_watchlist_publication_allowed'] = true;
            return $c157;
        }, 'publication-allowed');
        $planConfirm = $this->mutateC157AndExecute(function (array $c157): array {
            $c157['plan_confirm_mutated'] = true;
            return $c157;
        }, 'plan-confirm');

        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED', $published['status']);
        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED', $publicationAllowed['status']);
        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED', $planConfirm['status']);
    }

    public function test_c158_rejects_candidate_scope_change_or_a01_promotion(): void
    {
        $candidate = $this->mutateC157AndExecute(function (array $c157): array {
            $c157['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $c157;
        }, 'candidate-primary');
        $a01 = $this->mutateC157AndExecute(function (array $c157): array {
            $c157['a01_promoted'] = true;
            return $c157;
        }, 'candidate-a01');

        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $candidate['status']);
        $this->assertSame('C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $a01['status']);
    }

    public function test_c158_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c158-no-temporary-artifact-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame(self::TEMP_NEGATIVE_STATUS, $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c158_records_sections_manifest_and_no_publication_execution(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();
        $manifest = $result['controlled_output_publication_boundary_manifest'];
        $checklist = $result['controlled_output_publication_boundary_checklist'];

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $result['artifact_hash']);
        $this->assertSame(self::C157_HASH, $result['expected_c157_hash']);
        $this->assertSame(self::C157_HASH, $result['actual_c157_hash']);
        $this->assertTrue($result['c157_hash_match']);
        $this->assertSame(self::C157_SHA1, $result['expected_c157_file_sha1']);
        $this->assertSame(self::C157_SHA1, $result['actual_c157_file_sha1']);
        $this->assertTrue($result['c157_file_sha1_match']);
        $this->assertTrue($result['c157_convert_from_json_pass']);

        foreach ([
            'source_artifact_locks',
            'c157_lock_validation_summary',
            'c157_go_decision_finalization_carry_forward_summary',
            'controlled_output_publication_boundary_decision',
            'controlled_output_publication_boundary_manifest',
            'controlled_output_publication_boundary_checklist',
            'publication_plan_confirm_safety_summary',
            'candidate_scope_freeze_summary',
            'operator_approval_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'c158_candidate_controlled_output_publication_boundary_scorecard',
            'controlled_output_publication_boundary_context_summary',
            'documentation_hygiene_guard_summary',
            'progress_summary',
            'planned_next_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }

        $this->assertTrue($manifest['boundary_review_only']);
        $this->assertTrue($manifest['ready_for_controlled_output_publication_execution']);
        $this->assertFalse($manifest['controlled_output_publication_executed_in_c158_boundary']);
        $this->assertFalse($manifest['official_output_published']);
        $this->assertFalse($manifest['publication_allowed_in_boundary']);
        $this->assertTrue($manifest['controlled_publication_allowed_next']);
        $this->assertTrue($checklist['same_topic_number_for_next_stage']);
        $this->assertSame(self::NEXT_C158_EXECUTION, $run['planned_next_summary']['planned_next_review']);
    }

    public function test_c158_keeps_e02_primary_b01_backup_a01_comparator_and_publication_flags_false(): void
    {
        $result = $this->runService();
        $scorecard = $result['c158_candidate_controlled_output_publication_boundary_scorecard'];

        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $result['primary_candidate_code']);
        $this->assertSame('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $result['backup_candidate_code']);
        $this->assertSame('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $result['comparator_candidate_code']);
        $this->assertTrue($result['primary_candidate_ready_for_controlled_output_publication_execution']);
        $this->assertTrue($result['backup_candidate_ready_for_controlled_output_publication_execution']);
        $this->assertFalse($result['comparator_candidate_ready_for_controlled_output_publication_execution']);
        $this->assertTrue($result['a01_remains_comparator_only']);
        $this->assertTrue($scorecard[0]['ready_for_controlled_output_publication_execution']);
        $this->assertTrue($scorecard[1]['ready_for_controlled_output_publication_execution']);
        $this->assertFalse($scorecard[2]['ready_for_controlled_output_publication_execution']);

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

    public function test_c158_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $first = $this->runService(['createdAt' => '2026-07-15T00:00:00+00:00']);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c158-controlled-output-publication-boundary-review-second.json';
        $this->tmpFiles[] = $secondOutput;
        $second = $this->runService([
            'output' => $secondOutput,
            'createdAt' => '2026-07-15T00:00:00+00:00',
        ]);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
    }

    public function test_c158_does_not_mutate_c157_artifact_or_config_defaults(): void
    {
        $beforeC157 = strtoupper(sha1((string) file_get_contents(self::C157_ARTIFACT)));
        $beforeConfig = (string) file_get_contents('config/watchlist.php');

        $this->runService();

        $this->assertSame($beforeC157, strtoupper(sha1((string) file_get_contents(self::C157_ARTIFACT))));
        $this->assertSame($beforeConfig, (string) file_get_contents('config/watchlist.php'));
    }

    private function runService(array $options = []): array
    {
        $service = new WatchlistBacktestC158WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationBoundaryReviewService();

        return $service->execute(
            (string) ($options['c157Artifact'] ?? self::C157_ARTIFACT),
            (string) ($options['expectedC157Hash'] ?? self::C157_HASH),
            (string) ($options['expectedC157FileSha1'] ?? self::C157_SHA1),
            (string) ($options['output'] ?? $this->output),
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'publication_boundary_confirmed' => (bool) ($options['publicationBoundaryConfirmed'] ?? true),
                'controlled_publication_only_confirmed' => (bool) ($options['controlledPublicationOnlyConfirmed'] ?? true),
                'plan_confirm_unchanged_confirmed' => (bool) ($options['planConfirmUnchangedConfirmed'] ?? true),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C158_OPERATOR_APPROVED_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_ONLY'),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-15T00:00:00+00:00'),
            ]
        );
    }

    private function mutateC157AndExecute(callable $mutator, string $name): array
    {
        $c157 = json_decode((string) file_get_contents(self::C157_ARTIFACT), true);
        $c157 = $mutator(is_array($c157) ? $c157 : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c158-source-c157-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($c157, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'c157Artifact' => $path,
            'expectedC157Hash' => (string) ($c157['artifact_hash'] ?? ''),
            'expectedC157FileSha1' => strtoupper(sha1((string) file_get_contents($path))),
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
    }
}

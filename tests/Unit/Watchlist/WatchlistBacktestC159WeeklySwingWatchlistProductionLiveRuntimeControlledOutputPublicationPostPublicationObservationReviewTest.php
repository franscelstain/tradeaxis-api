<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC159WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationPostPublicationObservationReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC159WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationPostPublicationObservationReviewTest extends TestCase
{
    private const C158_FINALIZATION_ARTIFACT = 'storage/app/watchlist/backtest/c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-go-decision-finalization-review.json';
    private const C158_FINALIZATION_HASH = 'd8e4bfc3f906f3bc613f9aae1e03a27a67f9241b';
    private const C158_FINALIZATION_SHA1 = 'D732BDF92A76DC25434C2DECC539CD26181C8F21';
    private const CONTROLLED_PUBLICATION_ARTIFACT = 'storage/app/watchlist/output/c158-weekly-swing-watchlist-controlled-publication.json';
    private const CONTROLLED_PUBLICATION_HASH = 'df064c7290ff4c3bfd0c7a8412d39299049c01d5';
    private const CONTROLLED_PUBLICATION_SHA1 = 'D87AB8CD1564BE8B266B8A68011470272D49EE60';
    private const PASS_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_PASSED_CONTROLLED_PUBLICATION_OBSERVED_READY_FOR_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_PRIMARY_AND_BACKUP';
    private const NEXT_RESULT_REVIEW = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW';

    private string $output;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c159-controlled-output-publication-post-publication-observation-review.json';
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

    public function test_c159_observes_controlled_publication_and_opens_result_review(): void
    {
        $result = $this->runService();

        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW', $result['run_code']);
        $this->assertSame('PR-51 / C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW', $result['phase_label']);
        $this->assertSame('C159_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION', $result['topic_code']);
        $this->assertSame('POST_PUBLICATION_OBSERVATION_REVIEW', $result['topic_stage']);
        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame(self::PASS_STATUS, $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_production_live_runtime_controlled_output_publication_post_publication_observation_review_pass']);
        $this->assertTrue($result['production_live_runtime_controlled_output_publication_post_publication_observation_review_pass']);
        $this->assertTrue($result['post_publication_observation_confirmed']);
        $this->assertTrue($result['controlled_publication_observation_confirmed']);
        $this->assertTrue($result['free_publication_locked_confirmed']);
        $this->assertTrue($result['plan_confirm_unchanged_confirmed']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_output_publication_observed']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_output_publication_observation_stable']);
        $this->assertTrue($result['ready_for_weekly_swing_watchlist_controlled_output_publication_post_publication_observation_result_review']);
        $this->assertSame(self::NEXT_RESULT_REVIEW, $result['next_step_recommendation']);
        $this->assertFileExists($this->output);
    }

    public function test_c159_records_source_locks_sections_and_no_plan_confirm_mutation(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();

        foreach ([
            'source_artifact_locks',
            'c158_finalization_lock_validation_summary',
            'controlled_publication_lock_validation_summary',
            'post_publication_observation_summary',
            'controlled_publication_observation_summary',
            'candidate_publication_observation_scorecard',
            'publication_plan_confirm_safety_summary',
            'operator_approval_validation_summary',
            'temporary_negative_artifact_guard_summary',
            'documentation_hygiene_guard_summary',
            'progress_summary',
            'planned_next_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }

        $this->assertSame(self::C158_FINALIZATION_HASH, $result['expected_c158_finalization_hash']);
        $this->assertSame(self::C158_FINALIZATION_HASH, $result['actual_c158_finalization_hash']);
        $this->assertTrue($result['c158_finalization_hash_match']);
        $this->assertSame(self::C158_FINALIZATION_SHA1, $result['expected_c158_finalization_file_sha1']);
        $this->assertSame(self::C158_FINALIZATION_SHA1, $result['actual_c158_finalization_file_sha1']);
        $this->assertTrue($result['c158_finalization_file_sha1_match']);
        $this->assertSame(self::CONTROLLED_PUBLICATION_HASH, $result['expected_controlled_publication_hash']);
        $this->assertSame(self::CONTROLLED_PUBLICATION_HASH, $result['actual_controlled_publication_hash']);
        $this->assertTrue($result['controlled_publication_hash_match']);
        $this->assertSame(self::CONTROLLED_PUBLICATION_SHA1, $result['expected_controlled_publication_file_sha1']);
        $this->assertSame(self::CONTROLLED_PUBLICATION_SHA1, $result['actual_controlled_publication_file_sha1']);
        $this->assertTrue($result['controlled_publication_file_sha1_match']);
        $this->assertTrue($result['c158_finalization_lock_valid']);
        $this->assertTrue($result['c158_go_decision_finalization_valid']);
        $this->assertTrue($result['controlled_publication_lock_valid']);
        $this->assertTrue($result['controlled_publication_integrity_valid']);
        $this->assertFalse($result['weekly_swing_watchlist_official_output_published']);
        $this->assertFalse($result['weekly_swing_watchlist_publication_allowed']);
        $this->assertFalse($result['weekly_swing_watchlist_unrestricted_publication_allowed']);
        $this->assertFalse($result['plan_confirm_mutated']);
        $this->assertFalse($result['plan_confirm_runtime_reads_activated_catalog']);
    }

    public function test_c159_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->runService(['operatorApproved' => false]);
        $missingReference = $this->runService(['approvalReference' => '']);

        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingOperator['status']);
        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingReference['status']);
    }

    public function test_c159_rejects_missing_required_confirmations(): void
    {
        $observation = $this->runService(['postPublicationObservationConfirmed' => false]);
        $controlled = $this->runService(['controlledPublicationObservationConfirmed' => false]);
        $freeLock = $this->runService(['freePublicationLockedConfirmed' => false]);
        $plan = $this->runService(['planConfirmUnchangedConfirmed' => false]);

        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_REJECTED_POST_PUBLICATION_OBSERVATION_CONFIRMATION_MISSING', $observation['status']);
        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_REJECTED_CONTROLLED_PUBLICATION_OBSERVATION_CONFIRMATION_MISSING', $controlled['status']);
        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_REJECTED_FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING', $freeLock['status']);
        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING', $plan['status']);
    }

    public function test_c159_rejects_missing_or_mismatched_c158_finalization_lock(): void
    {
        $missing = $this->runService([
            'c158FinalizationArtifact' => 'storage/app/watchlist/backtest/.tmp-c159-source-c158-finalization-missing.json',
            'expectedC158FinalizationHash' => 'missing',
            'expectedC158FinalizationFileSha1' => 'missing',
        ]);
        $hashMismatch = $this->runService(['expectedC158FinalizationHash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedC158FinalizationFileSha1' => 'BADSHA1']);

        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_REJECTED_C158_FINALIZATION_ARTIFACT_LOCK_MISMATCH', $missing['status']);
        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_REJECTED_C158_FINALIZATION_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_REJECTED_C158_FINALIZATION_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
    }

    public function test_c159_rejects_missing_or_mismatched_controlled_publication_lock(): void
    {
        $missing = $this->runService([
            'controlledPublicationArtifact' => 'storage/app/watchlist/output/.tmp-c159-controlled-publication-missing.json',
            'expectedControlledPublicationHash' => 'missing',
            'expectedControlledPublicationFileSha1' => 'missing',
        ]);
        $hashMismatch = $this->runService(['expectedControlledPublicationHash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedControlledPublicationFileSha1' => 'BADSHA1']);

        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_REJECTED_CONTROLLED_PUBLICATION_ARTIFACT_LOCK_MISMATCH', $missing['status']);
        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_REJECTED_CONTROLLED_PUBLICATION_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_REJECTED_CONTROLLED_PUBLICATION_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
    }

    public function test_c159_rejects_c158_status_phase_or_next_recommendation_mismatch(): void
    {
        $status = $this->mutateC158AndExecute(function (array $c158): array {
            $c158['status'] = 'BROKEN_STATUS';
            return $c158;
        }, 'status-broken');
        $phase = $this->mutateC158AndExecute(function (array $c158): array {
            $c158['phase_label'] = 'BROKEN_PHASE';
            return $c158;
        }, 'phase-broken');
        $next = $this->mutateC158AndExecute(function (array $c158): array {
            $c158['next_step_recommendation'] = 'BROKEN_NEXT';
            $c158['next_controlled_output_publication_post_publication_observation_decision']['next_recommendation'] = 'BROKEN_NEXT';
            $c158['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $c158;
        }, 'next-broken');

        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_REJECTED_C158_FINALIZATION_STATUS_MISMATCH', $status['status']);
        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_REJECTED_C158_FINALIZATION_PHASE_LABEL_MISMATCH', $phase['status']);
        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_REJECTED_C158_FINALIZATION_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
    }

    public function test_c159_rejects_convert_from_json_duplicate_top_level_keys(): void
    {
        $c158Raw = (string) file_get_contents(self::C158_FINALIZATION_ARTIFACT);
        $c158Path = 'storage/app/watchlist/backtest/.tmp-c159-source-c158-duplicate-key.json';
        $this->tmpFiles[] = $c158Path;
        file_put_contents($c158Path, preg_replace('/\{/', "{\"Run_Code\":\"DUPLICATE_CASE_KEY\",", $c158Raw, 1));
        $c158 = $this->runService([
            'c158FinalizationArtifact' => $c158Path,
            'expectedC158FinalizationHash' => self::C158_FINALIZATION_HASH,
            'expectedC158FinalizationFileSha1' => strtoupper(sha1((string) file_get_contents($c158Path))),
        ]);

        $publicationRaw = (string) file_get_contents(self::CONTROLLED_PUBLICATION_ARTIFACT);
        $publicationPath = 'storage/app/watchlist/backtest/.tmp-c159-source-controlled-publication-duplicate-key.json';
        $this->tmpFiles[] = $publicationPath;
        file_put_contents($publicationPath, preg_replace('/\{/', "{\"Run_Code\":\"DUPLICATE_CASE_KEY\",", $publicationRaw, 1));
        $publication = $this->runService([
            'controlledPublicationArtifact' => $publicationPath,
            'expectedControlledPublicationHash' => self::CONTROLLED_PUBLICATION_HASH,
            'expectedControlledPublicationFileSha1' => strtoupper(sha1((string) file_get_contents($publicationPath))),
        ]);

        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_REJECTED_C158_FINALIZATION_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION', $c158['status']);
        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_REJECTED_CONTROLLED_PUBLICATION_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION', $publication['status']);
    }

    /**
     * @dataProvider c158FinalizationMismatchProvider
     */
    public function test_c159_rejects_incomplete_c158_finalization(string $field, $value): void
    {
        $result = $this->mutateC158AndExecute(function (array $c158) use ($field, $value): array {
            $this->setValueAt($c158, explode('.', $field), $value);
            return $c158;
        }, 'finalization-'.str_replace('.', '-', $field));

        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_REJECTED_C158_FINALIZATION_INCOMPLETE', $result['status'], $field);
    }

    public function c158FinalizationMismatchProvider(): array
    {
        return [
            ['weekly_swing_watchlist_production_live_runtime_controlled_output_publication_go_decision_finalization_review_pass', false],
            ['go_decision_finalized', false],
            ['controlled_publication_finalization_confirmed', false],
            ['ready_for_weekly_swing_watchlist_controlled_output_publication_post_publication_observation_review', false],
            ['controlled_output_publication_go_decision_finalization_manifest_created', false],
            ['weekly_swing_watchlist_controlled_output_publication_executed', false],
            ['weekly_swing_watchlist_controlled_output_published', false],
            ['controlled_publication_integrity_valid', false],
            ['primary_candidate_ready_for_controlled_output_publication_post_publication_observation_review', false],
            ['backup_candidate_ready_for_controlled_output_publication_post_publication_observation_review', false],
            ['comparator_candidate_ready_for_controlled_output_publication_post_publication_observation_review', true],
            ['weekly_swing_watchlist_controlled_output_publication_go_decision_finalization_manifest.go_decision_finalization_used_for_free_publication', true],
            ['weekly_swing_watchlist_controlled_output_publication_go_decision_finalization_checklist.artifact_only', false],
        ];
    }

    /**
     * @dataProvider controlledPublicationIntegrityMismatchProvider
     */
    public function test_c159_rejects_controlled_publication_integrity_mismatch(string $field, $value): void
    {
        $result = $this->mutatePublicationAndExecute(function (array $publication) use ($field, $value): array {
            $this->setValueAt($publication, explode('.', $field), $value);
            return $publication;
        }, 'publication-'.str_replace('.', '-', $field));

        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_REJECTED_CONTROLLED_PUBLICATION_INTEGRITY_MISMATCH', $result['status'], $field);
    }

    public function controlledPublicationIntegrityMismatchProvider(): array
    {
        return [
            ['publication_mode', 'free'],
            ['publication_state', 'free_published'],
            ['public_release_state', 'unrestricted'],
            ['weekly_swing_watchlist_controlled_output_publication_executed', false],
            ['weekly_swing_watchlist_controlled_output_published', false],
            ['weekly_swing_watchlist_controlled_publication_allowed', false],
            ['output_rows.0.candidate_code', 'BROKEN_PRIMARY'],
            ['comparator_candidate.a01_remains_comparator_only', false],
        ];
    }

    public function test_c159_rejects_free_publication_or_plan_confirm_mutation(): void
    {
        $published = $this->mutateC158AndExecute(function (array $c158): array {
            $c158['weekly_swing_watchlist_official_output_published'] = true;
            return $c158;
        }, 'free-published');
        $plan = $this->mutateC158AndExecute(function (array $c158): array {
            $c158['plan_confirm_mutated'] = true;
            return $c158;
        }, 'plan-mutated');
        $publication = $this->mutatePublicationAndExecute(function (array $publication): array {
            $publication['weekly_swing_watchlist_publication_allowed'] = true;
            return $publication;
        }, 'publication-allowed');

        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_REJECTED_FREE_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED', $published['status']);
        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_REJECTED_FREE_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED', $plan['status']);
        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_REJECTED_FREE_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED', $publication['status']);
    }

    public function test_c159_rejects_candidate_scope_change_or_a01_promotion(): void
    {
        $candidate = $this->mutateC158AndExecute(function (array $c158): array {
            $c158['primary_candidate_code'] = 'BROKEN_PRIMARY';
            return $c158;
        }, 'candidate-primary');
        $a01 = $this->mutateC158AndExecute(function (array $c158): array {
            $c158['a01_promoted'] = true;
            return $c158;
        }, 'candidate-a01');

        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $candidate['status']);
        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', $a01['status']);
    }

    public function test_c159_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c159-no-temporary-artifact-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS', $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c159_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $first = $this->runService(['createdAt' => '2026-07-15T00:00:00+00:00']);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c159-controlled-output-publication-post-publication-observation-review-second.json';
        $this->tmpFiles[] = $secondOutput;
        $second = $this->runService([
            'output' => $secondOutput,
            'createdAt' => '2026-07-15T00:00:00+00:00',
        ]);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
    }

    public function test_c159_does_not_mutate_c158_finalization_publication_or_config_defaults(): void
    {
        $beforeC158 = strtoupper(sha1((string) file_get_contents(self::C158_FINALIZATION_ARTIFACT)));
        $beforePublication = strtoupper(sha1((string) file_get_contents(self::CONTROLLED_PUBLICATION_ARTIFACT)));
        $beforeConfig = (string) file_get_contents('config/watchlist.php');

        $this->runService();

        $this->assertSame($beforeC158, strtoupper(sha1((string) file_get_contents(self::C158_FINALIZATION_ARTIFACT))));
        $this->assertSame($beforePublication, strtoupper(sha1((string) file_get_contents(self::CONTROLLED_PUBLICATION_ARTIFACT))));
        $this->assertSame($beforeConfig, (string) file_get_contents('config/watchlist.php'));
    }

    private function runService(array $options = []): array
    {
        $service = new WatchlistBacktestC159WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationPostPublicationObservationReviewService();

        return $service->execute(
            (string) ($options['c158FinalizationArtifact'] ?? self::C158_FINALIZATION_ARTIFACT),
            (string) ($options['expectedC158FinalizationHash'] ?? self::C158_FINALIZATION_HASH),
            (string) ($options['expectedC158FinalizationFileSha1'] ?? self::C158_FINALIZATION_SHA1),
            (string) ($options['controlledPublicationArtifact'] ?? self::CONTROLLED_PUBLICATION_ARTIFACT),
            (string) ($options['expectedControlledPublicationHash'] ?? self::CONTROLLED_PUBLICATION_HASH),
            (string) ($options['expectedControlledPublicationFileSha1'] ?? self::CONTROLLED_PUBLICATION_SHA1),
            (string) ($options['output'] ?? $this->output),
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'post_publication_observation_confirmed' => (bool) ($options['postPublicationObservationConfirmed'] ?? true),
                'controlled_publication_observation_confirmed' => (bool) ($options['controlledPublicationObservationConfirmed'] ?? true),
                'free_publication_locked_confirmed' => (bool) ($options['freePublicationLockedConfirmed'] ?? true),
                'plan_confirm_unchanged_confirmed' => (bool) ($options['planConfirmUnchangedConfirmed'] ?? true),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C159_OPERATOR_APPROVED_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_ONLY'),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-15T00:00:00+00:00'),
            ]
        );
    }

    private function mutateC158AndExecute(callable $mutator, string $name): array
    {
        $c158 = json_decode((string) file_get_contents(self::C158_FINALIZATION_ARTIFACT), true);
        $c158 = $mutator(is_array($c158) ? $c158 : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c159-source-c158-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($c158, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'c158FinalizationArtifact' => $path,
            'expectedC158FinalizationHash' => (string) ($c158['artifact_hash'] ?? ''),
            'expectedC158FinalizationFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function mutatePublicationAndExecute(callable $mutator, string $name): array
    {
        $publication = json_decode((string) file_get_contents(self::CONTROLLED_PUBLICATION_ARTIFACT), true);
        $publication = $mutator(is_array($publication) ? $publication : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c159-source-controlled-publication-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($publication, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'controlledPublicationArtifact' => $path,
            'expectedControlledPublicationHash' => (string) ($publication['controlled_publication_hash'] ?? ''),
            'expectedControlledPublicationFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
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
        foreach ((array) glob('storage/app/watchlist/backtest/c159-*test.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/.tmp-c159*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}

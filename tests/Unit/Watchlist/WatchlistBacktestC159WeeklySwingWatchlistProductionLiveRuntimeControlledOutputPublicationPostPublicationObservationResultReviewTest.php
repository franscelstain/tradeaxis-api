<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC159WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationPostPublicationObservationResultReviewService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC159WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationPostPublicationObservationResultReviewTest extends TestCase
{
    private const C159_OBSERVATION_ARTIFACT = 'storage/app/watchlist/backtest/c159-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-post-publication-observation-review.json';
    private const C159_OBSERVATION_HASH = '4f4897570d35a4b572c7158c7e48e860b146aa86';
    private const C159_OBSERVATION_SHA1 = 'BD6A087B386CC4C170A30E8606533453CC20FA43';
    private const CONTROLLED_PUBLICATION_ARTIFACT = 'storage/app/watchlist/output/c158-weekly-swing-watchlist-controlled-publication.json';
    private const CONTROLLED_PUBLICATION_HASH = 'df064c7290ff4c3bfd0c7a8412d39299049c01d5';
    private const CONTROLLED_PUBLICATION_SHA1 = 'D87AB8CD1564BE8B266B8A68011470272D49EE60';
    private const PASS_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_PASSED_READY_FOR_POST_PUBLICATION_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW_PRIMARY_AND_BACKUP';
    private const NEXT_OPERATOR = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW';

    private string $output;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = 'storage/app/watchlist/backtest/.tmp-c159-post-publication-observation-result-review.json';
        $this->cleanupTemporaryArtifacts();
        @unlink($this->output);
    }

    protected function tearDown(): void
    {
        @unlink($this->output);
        foreach ($this->tmpFiles as $path) {
            @unlink($path);
        }
        $this->cleanupTemporaryArtifacts();
        parent::tearDown();
    }

    public function test_c159_result_review_passes_and_keeps_same_topic_operator_go_no_go_next(): void
    {
        $result = $this->runService();

        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW', $result['run_code']);
        $this->assertSame('PR-52 / C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW', $result['phase_label']);
        $this->assertSame('C159_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION', $result['topic_code']);
        $this->assertSame('POST_PUBLICATION_OBSERVATION_RESULT_REVIEW', $result['topic_stage']);
        $this->assertSame(self::PASS_STATUS, $result['status']);
        $this->assertSame(self::PASS_STATUS, $result['reason_code']);
        $this->assertTrue($result['weekly_swing_watchlist_production_live_runtime_controlled_output_publication_post_publication_observation_result_review_pass']);
        $this->assertTrue($result['production_live_runtime_controlled_output_publication_post_publication_observation_result_review_pass']);
        $this->assertTrue($result['weekly_swing_watchlist_controlled_output_publication_post_publication_observation_result_reviewed']);
        $this->assertTrue($result['ready_for_weekly_swing_watchlist_controlled_output_publication_post_publication_observation_operator_go_no_go_review']);
        $this->assertFalse($result['weekly_swing_watchlist_official_output_published']);
        $this->assertFalse($result['weekly_swing_watchlist_publication_allowed']);
        $this->assertFalse($result['weekly_swing_watchlist_unrestricted_publication_allowed']);
        $this->assertFalse($result['plan_confirm_mutated']);
        $this->assertSame(self::NEXT_OPERATOR, $result['next_step_recommendation']);
        $this->assertFileExists($this->output);
    }

    public function test_c159_result_review_records_locks_sections_and_candidate_scope(): void
    {
        $result = $this->runService();
        $run = $this->readOutput();

        foreach ([
            'source_artifact_locks',
            'c159_observation_lock_validation_summary',
            'controlled_publication_lock_validation_summary',
            'c159_observation_carry_forward_summary',
            'post_publication_observation_result_review_summary',
            'controlled_publication_observation_result_summary',
            'publication_plan_confirm_safety_summary',
            'candidate_observation_result_scorecard',
            'operator_approval_validation_summary',
            'result_review_confirmation_summary',
            'temporary_negative_artifact_guard_summary',
            'documentation_hygiene_guard_summary',
            'progress_summary',
            'planned_next_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }

        $this->assertSame(self::C159_OBSERVATION_HASH, $result['expected_c159_observation_hash']);
        $this->assertSame(self::C159_OBSERVATION_HASH, $result['actual_c159_observation_hash']);
        $this->assertTrue($result['c159_observation_hash_match']);
        $this->assertSame(self::C159_OBSERVATION_SHA1, $result['actual_c159_observation_file_sha1']);
        $this->assertTrue($result['c159_observation_file_sha1_match']);
        $this->assertSame(self::CONTROLLED_PUBLICATION_HASH, $result['actual_controlled_publication_hash']);
        $this->assertSame(self::CONTROLLED_PUBLICATION_SHA1, $result['actual_controlled_publication_file_sha1']);
        $this->assertTrue($result['controlled_publication_hash_match']);
        $this->assertTrue($result['controlled_publication_file_sha1_match']);
        $this->assertSame(2, $result['controlled_publication_record_count']);
        $this->assertTrue($result['primary_candidate_observation_result_reviewed']);
        $this->assertTrue($result['backup_candidate_observation_result_reviewed']);
        $this->assertFalse($result['comparator_candidate_observation_result_reviewed']);
        $this->assertTrue($result['a01_remains_comparator_only']);
        $this->assertSame(self::NEXT_OPERATOR, $run['planned_next_summary']['planned_next_review']);
    }

    public function test_c159_result_review_rejects_missing_operator_approval_or_reference(): void
    {
        $missingOperator = $this->runService(['operatorApproved' => false]);
        $missingReference = $this->runService(['approvalReference' => '']);

        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingOperator['status']);
        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', $missingReference['status']);
    }

    public function test_c159_result_review_rejects_missing_required_confirmations(): void
    {
        $resultReview = $this->runService(['resultReviewConfirmed' => false]);
        $publicationResult = $this->runService(['controlledPublicationObservationResultConfirmed' => false]);
        $freeLock = $this->runService(['freePublicationLockedConfirmed' => false]);
        $plan = $this->runService(['planConfirmUnchangedConfirmed' => false]);

        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_REJECTED_RESULT_REVIEW_CONFIRMATION_MISSING', $resultReview['status']);
        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_REJECTED_CONTROLLED_PUBLICATION_OBSERVATION_RESULT_CONFIRMATION_MISSING', $publicationResult['status']);
        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_REJECTED_FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING', $freeLock['status']);
        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING', $plan['status']);
    }

    public function test_c159_result_review_rejects_observation_lock_status_phase_or_next_mismatch(): void
    {
        $hashMismatch = $this->runService(['expectedC159ObservationHash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedC159ObservationFileSha1' => 'BADSHA1']);
        $status = $this->mutateObservationAndExecute(function (array $observation): array {
            $observation['status'] = 'BROKEN_STATUS';
            return $observation;
        }, 'status');
        $phase = $this->mutateObservationAndExecute(function (array $observation): array {
            $observation['phase_label'] = 'BROKEN_PHASE';
            return $observation;
        }, 'phase');
        $next = $this->mutateObservationAndExecute(function (array $observation): array {
            $observation['next_step_recommendation'] = 'BROKEN_NEXT';
            $observation['planned_next_summary']['planned_next_review'] = 'BROKEN_NEXT';
            return $observation;
        }, 'next');

        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_REJECTED_C159_OBSERVATION_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_REJECTED_C159_OBSERVATION_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_REJECTED_C159_OBSERVATION_STATUS_MISMATCH', $status['status']);
        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_REJECTED_C159_OBSERVATION_PHASE_LABEL_MISMATCH', $phase['status']);
        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_REJECTED_C159_OBSERVATION_NEXT_RECOMMENDATION_MISMATCH', $next['status']);
    }

    public function test_c159_result_review_rejects_controlled_publication_lock_mismatch(): void
    {
        $hashMismatch = $this->runService(['expectedControlledPublicationHash' => 'bad-hash']);
        $shaMismatch = $this->runService(['expectedControlledPublicationFileSha1' => 'BADSHA1']);

        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_REJECTED_CONTROLLED_PUBLICATION_ARTIFACT_LOCK_MISMATCH', $hashMismatch['status']);
        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_REJECTED_CONTROLLED_PUBLICATION_FILE_SHA1_LOCK_MISMATCH', $shaMismatch['status']);
    }

    public function test_c159_result_review_rejects_convert_from_json_duplicate_top_level_keys(): void
    {
        $observationPath = $this->duplicateTopLevelKeyFixture(self::C159_OBSERVATION_ARTIFACT, 'observation-duplicate', 'Run_Code');
        $publicationPath = $this->duplicateTopLevelKeyFixture(self::CONTROLLED_PUBLICATION_ARTIFACT, 'publication-duplicate', 'Controlled_Publication_Hash');

        $observation = $this->runService([
            'c159ObservationArtifact' => $observationPath,
            'expectedC159ObservationFileSha1' => strtoupper(sha1((string) file_get_contents($observationPath))),
        ]);
        $publication = $this->runService([
            'controlledPublicationArtifact' => $publicationPath,
            'expectedControlledPublicationFileSha1' => strtoupper(sha1((string) file_get_contents($publicationPath))),
        ]);

        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_REJECTED_C159_OBSERVATION_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION', $observation['status']);
        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_REJECTED_CONTROLLED_PUBLICATION_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION', $publication['status']);
    }

    /**
     * @dataProvider observationIncompleteProvider
     */
    public function test_c159_result_review_rejects_incomplete_observation_evidence(string $field, $value): void
    {
        $result = $this->mutateObservationAndExecute(function (array $observation) use ($field, $value): array {
            $this->setValueAt($observation, explode('.', $field), $value);
            return $observation;
        }, 'observation-incomplete-'.str_replace('.', '-', $field));

        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_REJECTED_C159_OBSERVATION_INCOMPLETE', $result['status'], $field);
    }

    public function observationIncompleteProvider(): array
    {
        return [
            ['weekly_swing_watchlist_production_live_runtime_controlled_output_publication_post_publication_observation_review_pass', false],
            ['weekly_swing_watchlist_controlled_output_publication_observed', false],
            ['weekly_swing_watchlist_controlled_output_publication_observation_stable', false],
            ['ready_for_weekly_swing_watchlist_controlled_output_publication_post_publication_observation_result_review', false],
            ['controlled_output_publication_post_publication_observation_manifest_created', false],
            ['controlled_publication_integrity_valid', false],
            ['primary_candidate_observed_in_controlled_publication', false],
            ['backup_candidate_observed_in_controlled_publication', false],
            ['comparator_candidate_observed_in_controlled_publication', true],
            ['a01_remains_comparator_only', false],
            ['topic_stage', 'POST_PUBLICATION_OBSERVATION_RESULT_REVIEW'],
        ];
    }

    public function test_c159_result_review_rejects_controlled_publication_integrity_or_candidate_scope_mismatch(): void
    {
        $state = $this->mutatePublicationAndExecute(function (array $publication): array {
            $publication['publication_state'] = 'not_published';
            return $publication;
        }, 'state');
        $candidate = $this->mutatePublicationAndExecute(function (array $publication): array {
            $publication['output_rows'][0]['candidate_code'] = 'BROKEN_PRIMARY';
            return $publication;
        }, 'candidate');

        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_REJECTED_CONTROLLED_PUBLICATION_INTEGRITY_MISMATCH', $state['status']);
        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_REJECTED_CONTROLLED_PUBLICATION_INTEGRITY_MISMATCH', $candidate['status']);
    }

    public function test_c159_result_review_rejects_free_publication_or_plan_confirm_mutation(): void
    {
        $published = $this->mutateObservationAndExecute(function (array $observation): array {
            $observation['weekly_swing_watchlist_official_output_published'] = true;
            return $observation;
        }, 'free-published');
        $plan = $this->mutateObservationAndExecute(function (array $observation): array {
            $observation['plan_confirm_mutated'] = true;
            return $observation;
        }, 'plan-mutated');
        $publication = $this->mutatePublicationAndExecute(function (array $publication): array {
            $publication['weekly_swing_watchlist_publication_allowed'] = true;
            return $publication;
        }, 'publication-allowed');

        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_REJECTED_FREE_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED', $published['status']);
        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_REJECTED_FREE_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED', $plan['status']);
        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_REJECTED_FREE_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED', $publication['status']);
    }

    public function test_c159_result_review_rejects_temporary_negative_artifact_remaining(): void
    {
        $path = 'storage/app/watchlist/backtest/c159-post-publication-observation-result-review-negative-remains-test.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, '{}');

        $result = $this->runService();

        $this->assertSame('C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS', $result['status']);
        $this->assertTrue($result['temporary_negative_artifacts_remaining']);
        $this->assertFalse($result['temporary_negative_artifact_cleanup_confirmed']);
        $this->assertContains($path, $result['temporary_negative_artifact_paths']);
    }

    public function test_c159_result_review_output_is_deterministic_enough_for_artifact_hash_validation(): void
    {
        $first = $this->runService(['createdAt' => '2026-07-15T00:00:00+00:00']);
        $secondOutput = 'storage/app/watchlist/backtest/.tmp-c159-post-publication-observation-result-review-second.json';
        $this->tmpFiles[] = $secondOutput;
        $second = $this->runService([
            'output' => $secondOutput,
            'createdAt' => '2026-07-15T00:00:00+00:00',
        ]);

        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
    }

    public function test_c159_result_review_does_not_mutate_source_artifacts_or_config_defaults(): void
    {
        $beforeObservation = strtoupper(sha1((string) file_get_contents(self::C159_OBSERVATION_ARTIFACT)));
        $beforePublication = strtoupper(sha1((string) file_get_contents(self::CONTROLLED_PUBLICATION_ARTIFACT)));
        $beforeConfig = (string) file_get_contents('config/watchlist.php');

        $this->runService();

        $this->assertSame($beforeObservation, strtoupper(sha1((string) file_get_contents(self::C159_OBSERVATION_ARTIFACT))));
        $this->assertSame($beforePublication, strtoupper(sha1((string) file_get_contents(self::CONTROLLED_PUBLICATION_ARTIFACT))));
        $this->assertSame($beforeConfig, (string) file_get_contents('config/watchlist.php'));
    }

    private function runService(array $options = []): array
    {
        $service = new WatchlistBacktestC159WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationPostPublicationObservationResultReviewService();

        return $service->execute(
            (string) ($options['c159ObservationArtifact'] ?? self::C159_OBSERVATION_ARTIFACT),
            (string) ($options['expectedC159ObservationHash'] ?? self::C159_OBSERVATION_HASH),
            (string) ($options['expectedC159ObservationFileSha1'] ?? self::C159_OBSERVATION_SHA1),
            (string) ($options['controlledPublicationArtifact'] ?? self::CONTROLLED_PUBLICATION_ARTIFACT),
            (string) ($options['expectedControlledPublicationHash'] ?? self::CONTROLLED_PUBLICATION_HASH),
            (string) ($options['expectedControlledPublicationFileSha1'] ?? self::CONTROLLED_PUBLICATION_SHA1),
            (string) ($options['output'] ?? $this->output),
            [
                'operator_approved' => (bool) ($options['operatorApproved'] ?? true),
                'result_review_confirmed' => (bool) ($options['resultReviewConfirmed'] ?? true),
                'controlled_publication_observation_result_confirmed' => (bool) ($options['controlledPublicationObservationResultConfirmed'] ?? true),
                'free_publication_locked_confirmed' => (bool) ($options['freePublicationLockedConfirmed'] ?? true),
                'plan_confirm_unchanged_confirmed' => (bool) ($options['planConfirmUnchangedConfirmed'] ?? true),
                'approval_reference' => (string) ($options['approvalReference'] ?? 'C159_OPERATOR_APPROVED_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_ONLY'),
                'overwrite' => true,
                'created_at' => (string) ($options['createdAt'] ?? '2026-07-15T00:00:00+00:00'),
            ]
        );
    }

    private function mutateObservationAndExecute(callable $mutator, string $name): array
    {
        $observation = json_decode((string) file_get_contents(self::C159_OBSERVATION_ARTIFACT), true);
        $observation = $mutator(is_array($observation) ? $observation : []);
        $path = 'storage/app/watchlist/backtest/.tmp-c159-result-review-observation-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($observation, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'c159ObservationArtifact' => $path,
            'expectedC159ObservationHash' => (string) ($observation['artifact_hash'] ?? ''),
            'expectedC159ObservationFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function mutatePublicationAndExecute(callable $mutator, string $name): array
    {
        $publication = json_decode((string) file_get_contents(self::CONTROLLED_PUBLICATION_ARTIFACT), true);
        $publication = $mutator(is_array($publication) ? $publication : []);
        $path = 'storage/app/watchlist/output/.tmp-c159-result-review-publication-'.$name.'.json';
        $this->tmpFiles[] = $path;
        file_put_contents($path, json_encode($publication, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $this->runService([
            'controlledPublicationArtifact' => $path,
            'expectedControlledPublicationHash' => (string) ($publication['controlled_publication_hash'] ?? ''),
            'expectedControlledPublicationFileSha1' => strtoupper(sha1((string) file_get_contents($path))),
        ]);
    }

    private function duplicateTopLevelKeyFixture(string $source, string $name, string $key): string
    {
        $raw = (string) file_get_contents($source);
        $path = 'storage/app/watchlist/backtest/.tmp-c159-result-review-'.$name.'.json';
        if (strpos($source, '/output/') !== false) {
            $path = 'storage/app/watchlist/output/.tmp-c159-result-review-'.$name.'.json';
        }
        $this->tmpFiles[] = $path;
        $duplicateRaw = preg_replace('/\{/', '{"'.$key.'":"DUPLICATE_CASE_KEY",', $raw, 1);
        file_put_contents($path, $duplicateRaw);

        return $path;
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

    private function cleanupTemporaryArtifacts(): void
    {
        foreach ((array) glob('storage/app/watchlist/backtest/c159-*post-publication-observation-result-review*-test.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/.tmp-c159-result-review*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/backtest/.tmp-c159-post-publication-observation-result-review*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        foreach ((array) glob('storage/app/watchlist/output/.tmp-c159-result-review*.json') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}

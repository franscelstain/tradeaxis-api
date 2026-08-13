<?php

/**
 * F-025 — the admissibility verdict must be storable, and must never be read as a pass.
 *
 * W18 gave ReplayVerificationService a fourth answer: NOT_ADMISSIBLE, emitted when the fixture's
 * expectation was derived from the run under verification, where agreement proves only that the run
 * equals itself. The rule was written correctly and could not run — comparison_result stayed
 * enum('MATCH','MISMATCH','EXPECTED_DEGRADE','UNEXPECTED'), so every inadmissible replay died on the
 * insert with "Warning: 1265 Data truncated" instead of being recorded as inadmissible.
 *
 * No test referenced NOT_ADMISSIBLE at all before this one, which is why a verdict that could never
 * be persisted still shipped. The two properties pinned here are the ones that failed:
 * the value must fit its column, and it must not be counted as a pass.
 */
class ReplayAdmissibilityVerdictStorabilityTest extends TestCase
{
    /**
     * Deliberately not a SQLite test. tests/Support/UsesMarketDataSqlite.php declares
     * comparison_result as a plain string, so the constraint under test does not exist there and a
     * SQLite version of this would pass whether or not the enum was ever widened.
     */
    public function test_the_admissibility_verdict_fits_the_column_that_stores_it(): void
    {
        $type = $this->comparisonResultColumnType();

        $this->assertStringContainsString(
            "'NOT_ADMISSIBLE'",
            $type,
            'md_replay_daily_metrics.comparison_result must accept the verdict the verifier emits'
        );

        foreach (['MATCH', 'MISMATCH', 'EXPECTED_DEGRADE', 'UNEXPECTED'] as $existing) {
            $this->assertStringContainsString(
                "'".$existing."'",
                $type,
                'widening the vocabulary must not drop '.$existing
            );
        }
    }

    /**
     * A refusal to judge is not a clean result. Every site that decides whether a comparison counts
     * as a pass lists the passing values explicitly; NOT_ADMISSIBLE must stay outside all of them,
     * so it falls through to BLOCKED.
     */
    public function test_the_inadmissible_verdict_is_never_counted_as_a_pass(): void
    {
        $sources = [
            'app/Infrastructure/Persistence/MarketData/ReplayResultRepository.php',
            'app/Application/MarketData/Services/ReplayVerificationService.php',
            'app/Application/MarketData/Services/MarketDataEvidenceExportService.php',
        ];

        foreach ($sources as $relativePath) {
            $source = (string) file_get_contents(__DIR__.'/../../../'.$relativePath);

            $this->assertNotSame(
                '',
                $source,
                $relativePath.' must be readable'
            );

            preg_match_all("/\[\s*'MATCH'\s*,\s*'EXPECTED_DEGRADE'\s*\]/", $source, $passLists);
            foreach ($passLists[0] as $passList) {
                $this->assertStringNotContainsString('NOT_ADMISSIBLE', $passList, $relativePath);
            }

            $this->assertStringNotContainsString(
                "'NOT_ADMISSIBLE' => 'PASS'",
                $source,
                $relativePath.' must not map the verdict to a pass'
            );
        }

        $verifier = (string) file_get_contents(
            __DIR__.'/../../../app/Application/MarketData/Services/ReplayVerificationService.php'
        );
        $this->assertStringContainsString(
            "'NOT_ADMISSIBLE'",
            $verifier,
            'the verifier must still be able to emit the verdict this test protects'
        );
    }

    /**
     * F-030: admissibility must test the fact, not the label.
     *
     * The original rule refused fixtures whose `fixture_family` was the literal string
     * `runtime_generated_valid_case`, which a self-generated expectation escapes simply by calling
     * itself something else. `fixture_source` records which run the expectation came from, and that
     * is what the rule is actually about.
     *
     * The third case matters as much as the first two: a fixture built from a *different* run is
     * admissible, so this cannot be satisfied by refusing everything.
     */
    public function test_a_relabelled_self_generated_fixture_is_still_refused(): void
    {
        $service = (new ReflectionClass(\App\Application\MarketData\Services\ReplayVerificationService::class))
            ->newInstanceWithoutConstructor();
        $method = new ReflectionMethod($service, 'replayAdmissibility');
        $method->setAccessible(true);

        $run = (object) ['run_id' => 72905, 'config_snapshot_id' => 'cfg-1'];
        $publication = (object) ['config_snapshot_id' => 'cfg-1'];

        $relabelled = $method->invoke($service, $run, $publication, ['manifest' => [
            'fixture_family' => 'curated_regression_case',
            'fixture_source' => 'generated_from_run_72905_publication_73580',
        ]]);
        $this->assertNotNull($relabelled, 'renaming the family must not make a self-generated fixture admissible');
        $this->assertStringContainsString('REPLAY_FIXTURE_SELF_GENERATED', $relabelled['reason']);

        $byFamily = $method->invoke($service, $run, $publication, ['manifest' => [
            'fixture_family' => 'runtime_generated_valid_case',
            'fixture_source' => 'generated_from_run_99999_publication_1',
        ]]);
        $this->assertNotNull($byFamily, 'the original family check must still hold');

        $independent = $method->invoke($service, $run, $publication, ['manifest' => [
            'fixture_family' => 'curated_regression_case',
            'fixture_source' => 'generated_from_run_66352_publication_67007',
        ]]);
        $this->assertNull(
            $independent,
            'an expectation authored from a different run is admissible; the rule must not refuse everything'
        );
    }

    private function comparisonResultColumnType(): string
    {
        try {
            $pdo = new PDO(
                'mysql:host=127.0.0.1;dbname=tradeaxis',
                'root',
                '',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 3]
            );
            $statement = $pdo->query(
                "SELECT COLUMN_TYPE FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = 'md_replay_daily_metrics'
                   AND COLUMN_NAME = 'comparison_result'"
            );
            $type = $statement->fetchColumn();
        } catch (\Throwable $e) {
            $this->markTestSkipped('no reachable MariaDB; enum storability cannot be proven on SQLite');
        }

        if ($type === false) {
            $this->markTestSkipped('md_replay_daily_metrics.comparison_result not present on the reachable database');
        }

        return (string) $type;
    }
}

<?php

use PHPUnit\Framework\TestCase;

class TestCoverageBehavioralStaticGuardTest extends TestCase
{
    private function projectPath(string $relativePath): string
    {
        return dirname(__DIR__, 3).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    }

    private function read(string $relativePath): string
    {
        $path = $this->projectPath($relativePath);
        $this->assertFileExists($path, $relativePath.' must exist.');

        return file_get_contents($path);
    }


    // The five named proof files are now covered by LifecycleProofIsNotMockedTest, which derives
    // the set instead: every *IntegrationTest must be DB-backed and free of test doubles, and no
    // DB-backed test anywhere may mock an application service or a persistence repository. Only
    // the source adapter may be stood in for, because a test cannot call a third-party API.

    /**
     * The pipeline integration proof must keep covering every terminal outcome.
     *
     * The twelve test METHOD NAMES that used to be pinned here are gone. Pinning a name breaks
     * when a test is renamed for clarity, catches nothing when one is deleted and replaced by a
     * weaker test of the same name, and says nothing about what is asserted.
     *
     * What survives is the substance: the state combinations and persisted tables the proof has
     * to reach. A run cannot end in a state this file does not mention.
     */
    public function test_pipeline_integration_covers_every_terminal_outcome_and_persisted_surface(): void
    {
        $pipeline = $this->read('tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php');

        foreach ([
            "'SUCCESS'",
            "'READABLE'",
            "'HELD'",
            "'FAILED'",
            "'NOT_READABLE'",
            "'RUN_PARTIAL_DATA'",
            "'RUN_LOCK_CONFLICT'",
            'eod_current_publication_pointer',
            'eod_publications',
            'eod_runs',
            'eod_run_events',
            'coverage_gate_state',
            'coverage_available_count',
            'coverage_universe_count',
            'coverage_missing_count',
            'RUN_FINALIZED',
            'STAGE_COMPLETED',
            'CORRECTION_PUBLISHED',
        ] as $needle) {
            $this->assertStringContainsString($needle, $pipeline, $needle.' must remain asserted by pipeline integration proof.');
        }
    }


}

<?php

use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * Every reason code the runtime emits must be a registered reason code.
 *
 * Several static guards carry hand-written lists of reason codes and check that each string
 * appears in the registry and the seed file. That protects the codes someone remembered to add
 * to the list. A code emitted by new runtime code and registered nowhere passes every one of
 * them, and the operator who looks it up finds nothing.
 *
 * This derives the list instead: it reads the codes out of the runtime source at the positions
 * where a reason code is unambiguously being emitted, and checks them against the seeded
 * dictionary. Nothing is listed by hand, so the rule covers codes that do not exist yet.
 */
class EmittedReasonCodeRegistrationTest extends TestCase
{
    use UsesMarketDataSqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
    }

    protected function tearDown(): void
    {
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    private function projectRoot(): string
    {
        return dirname(__DIR__, 3);
    }

    /**
     * @return string[] absolute paths to runtime PHP sources
     */
    private function runtimeSources(): array
    {
        $root = $this->projectRoot().DIRECTORY_SEPARATOR.'app';

        $files = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        sort($files);

        return $files;
    }

    /**
     * Only positions where the value is definitionally a reason code are read: the run-event and
     * run columns that store one, the parameter the command surface renders one through, and the
     * `reason_code=` line commands print.
     *
     * Bare positional string arguments are not scanned. Widening the patterns to every uppercase
     * literal would sweep up lifecycle states, seal states and threshold modes, and the noise
     * would have to be suppressed with exactly the kind of hand-maintained list this test exists
     * to avoid.
     *
     * @return array<string, string[]> code => files that emit it
     */
    private function emittedReasonCodes(): array
    {
        $patterns = [
            "/'(?:final_|coverage_|repair_|force_)?reason_code'\s*=>\s*'([A-Z][A-Z0-9_]{3,})'/",
            "/\\\$reasonCode\s*=\s*'([A-Z][A-Z0-9_]{3,})'/",
            "/renderCommandBlocked\(\s*'([A-Z][A-Z0-9_]{3,})'/",
            "/reason_code=([A-Z][A-Z0-9_]{3,})/",
            // Replay resolves a mismatch code from the field name, so its codes are returned
            // rather than assigned and none were in scope of the four patterns above. Thirty-
            // eight codes reach a replay result this way; a hand-written list in a static guard
            // covered twenty-one, and the fallback REPLAY_MISMATCH was registered nowhere.
            //
            // Scoped to REPLAY_ deliberately. A general "return 'UPPER_CASE'" pattern was tried
            // and rejected: ninety-one such returns exist in app/ and most are state values —
            // COMPLETED, PROMOTED, NOT_EVALUABLE, SCALE_SHIFT — which share the lexical form of
            // a reason code and are legitimately unregistered. Separating them would need an
            // exclusion list, which is the thing this test exists to avoid.
            "/return '(REPLAY_[A-Z0-9_]{3,})'/",
            "/\?\?\s*'(REPLAY_[A-Z0-9_]{3,})'/",
        ];

        $found = [];

        foreach ($this->runtimeSources() as $path) {
            $source = file_get_contents($path);
            $relative = str_replace($this->projectRoot().DIRECTORY_SEPARATOR, '', $path);

            foreach ($patterns as $pattern) {
                if (! preg_match_all($pattern, $source, $matches)) {
                    continue;
                }

                foreach ($matches[1] as $code) {
                    $found[$code][$relative] = true;
                }
            }
        }

        ksort($found);

        return array_map(function ($files) {
            return array_keys($files);
        }, $found);
    }

    private function seedRegisteredCodes(): array
    {
        $path = $this->projectRoot().DIRECTORY_SEPARATOR
            .str_replace('/', DIRECTORY_SEPARATOR, 'docs/market_data/registry/Reason_Codes_Seed.sql');

        $lines = array_filter(explode("\n", file_get_contents($path)), function ($line) {
            return strpos(ltrim($line), '--') !== 0;
        });

        $sql = implode("\n", $lines);

        $tail = strpos($sql, 'ON DUPLICATE KEY UPDATE');
        if ($tail !== false) {
            $sql = substr($sql, 0, $tail);
        }

        DB::unprepared(rtrim(trim(str_replace('`', '"', $sql)), ",; \t\n\r").';');

        return DB::table('eod_reason_codes')->pluck('code')->all();
    }

    public function test_the_scan_finds_a_meaningful_number_of_emitted_codes(): void
    {
        // A pattern change that silently stopped matching would make every other assertion in
        // this file vacuously true.
        $emitted = $this->emittedReasonCodes();

        $this->assertGreaterThan(25, count($emitted));
        $this->assertArrayHasKey('IMPORT_ONLY_COMPLETED_NOT_PROMOTED', $emitted);
        $this->assertArrayHasKey('COMMAND_DESTRUCTIVE_GUARD_REQUIRED', $emitted);

        // Positionally passed replay codes must be in scope, or the pattern added for them is
        // decoration. REPLAY_MISMATCH is the fallback the comparator falls back to and was the
        // one code this scan proved unregistered.
        $this->assertArrayHasKey('REPLAY_ARTIFACT_HASH_MISMATCH', $emitted);
        $this->assertArrayHasKey('REPLAY_LINEAGE_MISMATCH', $emitted);
        $this->assertArrayHasKey('REPLAY_MISMATCH', $emitted);
    }

    public function test_every_reason_code_emitted_by_the_runtime_is_registered(): void
    {
        $registered = $this->seedRegisteredCodes();

        $unregistered = [];

        foreach ($this->emittedReasonCodes() as $code => $files) {
            if (! in_array($code, $registered, true)) {
                $unregistered[] = $code.' (emitted by '.implode(', ', $files).')';
            }
        }

        $this->assertSame(
            [],
            $unregistered,
            "Reason codes emitted by the runtime but absent from eod_reason_codes.\n"
            ."An operator who reads one of these out of eod_run_events has nothing to look it up in."
        );
    }
}

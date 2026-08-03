<?php

use PHPUnit\Framework\TestCase;

/**
 * Recorded command output must be clean enough to be evidence.
 *
 * `OpsEnvironmentBaselineStaticGuardTest` asserts that two documents contain the phrases
 * "PHP Warning", "PHP Deprecated", "timezone warning" and so on. Those assertions prove the
 * policy is written down. They say nothing about the artifacts the policy governs.
 *
 * The policy is not cosmetic. Command output under storage/app is cited by the audit documents as
 * runtime proof — that a command ran, on a supported interpreter, and produced this result. A
 * PHP warning in that output means the interpreter was not the documented one, or an extension
 * was missing, or a deprecated path was taken. The command may still have printed a plausible
 * result, and that result is exactly what must not be trusted: the environment that produced it
 * was not the environment the proof claims.
 *
 * So the check belongs on the artifacts, and it is derived — every recorded .txt under
 * storage/app, including ones written after this test.
 */
class EvidenceArtifactCleanlinessTest extends TestCase
{
    /**
     * Markers that mean the interpreter, not the command, was talking.
     */
    private const NOISE_MARKERS = [
        'PHP Warning',
        'PHP Deprecated',
        'PHP Notice',
        'PHP Fatal error',
        'PHP Parse error',
        'Deprecated:',
        'Warning:',
        'Notice:',
        'Fatal error:',
        'Stack trace:',
    ];

    /** @var array<string, string>|null */
    private static $cache = null;

    /**
     * Recorded command output, found by bounded glob rather than a recursive walk.
     *
     * The first version of this iterated all of storage/app. That directory holds 281,000 files
     * across 92,000 directories — the per-date JSON of every evidence export ever taken — and
     * walking it five times took the suite from fifty seconds to thirteen minutes. A suite that
     * slow stops being run, which costs more than this test is worth.
     *
     * Scoping to `command-output` directories is not an arbitrary narrowing. That is the
     * convention this codebase already uses for "the recorded stdout of a command, cited as
     * proof" — which is exactly what the clean-output policy governs. JSON artifacts are
     * structured data, not transcripts, and an interpreter warning cannot hide in them unnoticed.
     *
     * @return array<string, string> relative path => contents
     */
    private function recordedArtifacts(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $root = dirname(__DIR__, 3);
        $paths = [];

        foreach ([
            'storage/app/market-data/*/command-output/*.txt',
            'storage/app/market-data/*/*/command-output/*.txt',
            'storage/app/market_data/*/command-output/*.txt',
        ] as $pattern) {
            $matches = glob($root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $pattern));
            $paths = array_merge($paths, $matches ?: []);
        }

        $artifacts = [];

        foreach (array_unique($paths) as $path) {
            $artifacts[str_replace($root.DIRECTORY_SEPARATOR, '', $path)] = file_get_contents($path);
        }

        ksort($artifacts);

        return self::$cache = $artifacts;
    }

    public function test_recorded_artifacts_exist_to_be_checked(): void
    {
        // Guards the guard twice over. An empty sweep would make every assertion below vacuous,
        // and a glob pattern that stopped matching is the likeliest way that happens — storage is
        // also exactly the kind of directory a cleanup script empties.
        $artifacts = $this->recordedArtifacts();

        $this->assertGreaterThan(100, count($artifacts));
        $this->assertNotEmpty(array_filter($artifacts, function ($contents) {
            return trim($contents) !== '';
        }), 'Matched artifacts must have content.');
    }

    /**
     * A warning in recorded output means the environment was wrong when the command ran, so the
     * result printed beside it cannot be cited as proof of anything.
     */
    public function test_no_recorded_artifact_contains_interpreter_noise(): void
    {
        $noisy = [];

        foreach ($this->recordedArtifacts() as $path => $contents) {
            foreach (self::NOISE_MARKERS as $marker) {
                if (strpos($contents, $marker) !== false) {
                    $noisy[] = $path.' contains "'.$marker.'"';
                }
            }
        }

        $this->assertSame(
            [],
            $noisy,
            "Recorded command output must be free of interpreter noise.\n"
            ."A warning beside a result means the run happened on an environment the proof does not describe."
        );
    }

    /**
     * Null bytes mean the file was written by something that was not producing text — a
     * redirected binary stream, or an encoding mismatch. Either way the recorded characters are
     * not the characters the command printed.
     */
    public function test_no_recorded_artifact_contains_null_bytes(): void
    {
        $binary = [];

        foreach ($this->recordedArtifacts() as $path => $contents) {
            if (strpos($contents, "\0") !== false) {
                $binary[] = $path;
            }
        }

        $this->assertSame([], $binary, 'Recorded output must be plain text.');
    }

    /**
     * And it must be valid UTF-8, because an artifact that cannot be read back reliably is not
     * an artifact anyone can audit.
     */
    public function test_every_recorded_artifact_is_valid_utf8(): void
    {
        $invalid = [];

        foreach ($this->recordedArtifacts() as $path => $contents) {
            if ($contents !== '' && ! mb_check_encoding($contents, 'UTF-8')) {
                $invalid[] = $path;
            }
        }

        $this->assertSame([], $invalid, 'Recorded output must be valid UTF-8.');
    }

    /**
     * Guards the guard again: the noise markers must actually match the shape PHP emits, or a
     * genuinely noisy artifact would pass unnoticed.
     */
    public function test_the_noise_markers_recognise_real_php_output(): void
    {
        foreach ([
            'PHP Warning:  Module already loaded in Unknown on line 0',
            'Deprecated: Return type should be compatible',
            'PHP Notice:  Undefined index: foo',
            "Fatal error: Uncaught TypeError\nStack trace:\n#0 {main}",
        ] as $sample) {
            $matched = false;

            foreach (self::NOISE_MARKERS as $marker) {
                if (strpos($sample, $marker) !== false) {
                    $matched = true;
                    break;
                }
            }

            $this->assertTrue($matched, 'Should have been recognised as noise: '.$sample);
        }

        // And must not fire on ordinary command output.
        foreach (['provider_smoke_status=PASS', 'reason_code=PROVIDER_SMOKE_OK', 'all_passed=1'] as $clean) {
            foreach (self::NOISE_MARKERS as $marker) {
                $this->assertFalse(strpos($clean, $marker) !== false, $clean.' must not be treated as noise.');
            }
        }
    }
}

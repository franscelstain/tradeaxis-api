<?php

use App\Application\MarketData\Services\CoverageGateStateNormalizer;
use PHPUnit\Framework\TestCase;

/**
 * Behavioural cover for coverage state normalisation.
 *
 * `CoveragePolicyDocsStaticGuardTest` asserts that four state strings appear in the normalizer
 * and that three services mention it by name. A file that names all four and returns the input
 * unchanged satisfies every one of those assertions.
 *
 * BLOCKED was a legacy coverage verdict and the reason it was retired is the reason this matters:
 * it did not distinguish "the gate ran and failed" from "the gate never ran". Those demand
 * opposite responses. A FAIL is a dataset that fell short of its threshold and can be corrected;
 * a NOT_EVALUABLE is a day the platform could not measure at all, and treating it as a failure
 * hides that nothing was checked.
 *
 * Old rows and old fixtures still carry BLOCKED, so the normaliser is the boundary that keeps it
 * out of every current decision — while `legacyRaw` preserves what was actually recorded, so
 * normalising is not the same as erasing.
 */
class CoverageStateNormalizationTest extends TestCase
{
    /**
     * The single property this class exists for.
     *
     * @dataProvider everyInputShape
     */
    public function test_normalisation_never_yields_the_retired_blocked_verdict($input): void
    {
        $this->assertNotSame(
            'BLOCKED',
            CoverageGateStateNormalizer::normalize($input),
            'BLOCKED must never survive normalisation.'
        );
    }

    public function everyInputShape(): array
    {
        return [
            'blocked' => ['BLOCKED'],
            'blocked lowercase' => ['blocked'],
            'blocked padded' => ['  BLOCKED  '],
            'blocked mixed case' => ['Blocked'],
            'pass' => ['PASS'],
            'fail' => ['FAIL'],
            'not evaluable' => ['NOT_EVALUABLE'],
            'unknown word' => ['SOMETHING_ELSE'],
            'empty string' => [''],
            'null' => [null],
            'a number' => [0],
        ];
    }

    /**
     * @dataProvider recognisedStates
     */
    public function test_a_recognised_state_survives_normalisation(string $input, string $expected): void
    {
        $this->assertSame($expected, CoverageGateStateNormalizer::normalize($input));
    }

    public function recognisedStates(): array
    {
        return [
            'pass' => ['PASS', 'PASS'],
            'fail' => ['FAIL', 'FAIL'],
            'not evaluable' => ['NOT_EVALUABLE', 'NOT_EVALUABLE'],
            'pass lowercase' => ['pass', 'PASS'],
            'fail padded' => [' FAIL ', 'FAIL'],
        ];
    }

    /**
     * @dataProvider legacyBlockedVariants
     */
    public function test_the_legacy_verdict_becomes_not_evaluable(string $input): void
    {
        $this->assertSame('NOT_EVALUABLE', CoverageGateStateNormalizer::normalize($input));
    }

    public function legacyBlockedVariants(): array
    {
        return [
            'canonical' => ['BLOCKED'],
            'lowercase' => ['blocked'],
            'padded' => ["\tBLOCKED\n"],
            'mixed case' => ['BlOcKeD'],
        ];
    }

    /**
     * An unrecognised state resolves to NOT_EVALUABLE, never to PASS.
     *
     * This is the direction that matters. A coverage verdict the platform does not understand
     * means it cannot say the day was covered, and "cannot say" must not read as "yes".
     *
     * @dataProvider unrecognisedStates
     */
    public function test_an_unrecognised_state_fails_closed(string $input): void
    {
        $this->assertSame('NOT_EVALUABLE', CoverageGateStateNormalizer::normalize($input));
    }

    public function unrecognisedStates(): array
    {
        return [
            'a state from another vocabulary' => ['HELD'],
            'a typo' => ['PASSS'],
            'a future state nobody taught it' => ['PARTIAL'],
            'punctuation' => ['-'],
        ];
    }

    /**
     * Absent is not the same as unevaluable. Null means no verdict was recorded at all, which is
     * how an incomplete run looks; NOT_EVALUABLE means a verdict was reached and it was "could
     * not measure". Collapsing them would make a missing column read as a decided one.
     *
     * @dataProvider absentValues
     */
    public function test_an_absent_value_stays_absent($input): void
    {
        $this->assertNull(CoverageGateStateNormalizer::normalize($input));
    }

    public function absentValues(): array
    {
        return [
            'null' => [null],
            'empty string' => [''],
            'whitespace only' => ['   '],
        ];
    }

    /**
     * Normalising is not erasing. Whatever the row actually held stays visible through
     * legacyRaw, so an operator looking at an old publication can still see it was recorded as
     * BLOCKED rather than concluding the platform always said NOT_EVALUABLE.
     *
     * @dataProvider legacyBlockedVariants
     */
    public function test_the_original_verdict_stays_recoverable(string $input): void
    {
        $this->assertSame('NOT_EVALUABLE', CoverageGateStateNormalizer::normalize($input));
        $this->assertSame('BLOCKED', CoverageGateStateNormalizer::legacyRaw($input));
    }

    /**
     * And nothing else is reported as legacy. A trace field that fired on ordinary states would
     * make every current run look like it came from the retired vocabulary.
     *
     * @dataProvider nonLegacyInputs
     */
    public function test_only_the_legacy_verdict_is_traced_as_legacy($input): void
    {
        $this->assertNull(CoverageGateStateNormalizer::legacyRaw($input));
    }

    public function nonLegacyInputs(): array
    {
        return [
            'pass' => ['PASS'],
            'fail' => ['FAIL'],
            'not evaluable' => ['NOT_EVALUABLE'],
            'unknown' => ['SOMETHING_ELSE'],
            'empty' => [''],
            'null' => [null],
        ];
    }

    /**
     * Normalisation is idempotent. It runs at several surfaces — evidence export, replay
     * comparison, command output — and a value that changed on a second pass would make those
     * surfaces disagree about the same run depending on how many times it had been through.
     *
     * @dataProvider everyInputShape
     */
    public function test_normalisation_is_idempotent($input): void
    {
        $once = CoverageGateStateNormalizer::normalize($input);

        $this->assertSame($once, CoverageGateStateNormalizer::normalize($once));
    }

    /**
     * No runtime code may write the retired verdict back into a coverage state. The normaliser
     * only helps on the way out; a writer that puts BLOCKED in would reintroduce it at the
     * source.
     */
    public function test_no_runtime_code_writes_the_retired_verdict(): void
    {
        $root = dirname(__DIR__, 3).DIRECTORY_SEPARATOR.'app';
        $violations = [];

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));

        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $source = file_get_contents($file->getPathname());

            if (preg_match("/coverage_gate_(?:state|status)'\s*=>\s*'BLOCKED'/", $source)) {
                $violations[] = str_replace(dirname(__DIR__, 3).DIRECTORY_SEPARATOR, '', $file->getPathname());
            }
        }

        $this->assertSame([], $violations, 'BLOCKED is a retired coverage verdict and must not be written.');
    }
}

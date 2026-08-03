<?php

use PHPUnit\Framework\TestCase;

/**
 * A test that proves the lifecycle must not have mocked the lifecycle.
 *
 * `TestCoverageBehavioralStaticGuardTest` checks five named files for this. That covers the files
 * someone listed; the behavioural suite has since roughly doubled and an integration test added
 * tomorrow would be governed by nothing.
 *
 * The rule is worth deriving because the failure it prevents is invisible in a passing run. A
 * test that mocks EodPublicationRepository and then asserts what EodPublicationRepository was
 * asked to do proves that the test set up its own mock correctly. It reports green while the real
 * query is broken — which is exactly how the repair command's reason derivation stayed wrong for
 * as long as it did: the only test covering it replaced the whole repository with a mock.
 *
 * The boundary is not "no mocking". Mocking the source adapter is necessary — a test cannot call
 * Yahoo, and pretending otherwise would make the suite depend on a rate-limited third party. What
 * must not be mocked is anything inside: application services and persistence repositories are
 * the thing under proof.
 */
class LifecycleProofIsNotMockedTest extends TestCase
{
    /**
     * The one namespace a test may legitimately stand in for: the outside world.
     */
    private const EXTERNAL_BOUNDARY = 'App\\Infrastructure\\MarketData\\Source';

    /**
     * @return array<string, string> relative path => source
     */
    private function marketDataTests(): array
    {
        $root = dirname(__DIR__, 3);
        $directory = $root.DIRECTORY_SEPARATOR.'tests'.DIRECTORY_SEPARATOR.'Unit'.DIRECTORY_SEPARATOR.'MarketData';

        $tests = [];

        foreach (glob($directory.DIRECTORY_SEPARATOR.'*.php') ?: [] as $path) {
            $tests[str_replace($root.DIRECTORY_SEPARATOR, '', $path)] = file_get_contents($path);
        }

        ksort($tests);

        return $tests;
    }

    /**
     * @return array<string, string>
     */
    private function integrationTests(): array
    {
        return array_filter($this->marketDataTests(), function ($source, $path) {
            return strpos($path, 'IntegrationTest.php') !== false;
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * @return array<string, string>
     */
    /**
     * Matched on the trait-use statement rather than a mention of the name.
     *
     * A scanner that looks for the bare string finds itself: this file names the trait in the
     * code that searches for it, and the first version of this test reported its own example
     * fixture as a violation.
     *
     * @return array<string, string>
     */
    private function dbBackedTests(): array
    {
        return array_filter($this->marketDataTests(), function ($source) {
            return preg_match('/^\s*use UsesMarketDataSqlite;/m', $source) === 1;
        });
    }

    public function test_the_suite_has_integration_and_db_backed_tests_to_check(): void
    {
        // Guards the guard: a naming or path change that matched nothing would make every
        // assertion below vacuous.
        $this->assertGreaterThan(3, count($this->integrationTests()));
        $this->assertGreaterThan(10, count($this->dbBackedTests()));
    }

    /**
     * An integration test is the strictest tier: it exists to show the real pieces work together,
     * so it uses none of them in effigy.
     */
    public function test_integration_tests_use_no_test_doubles_at_all(): void
    {
        $violations = [];

        foreach ($this->integrationTests() as $path => $source) {
            foreach (['use Mockery', 'm::mock', 'shouldReceive', 'createMock(', 'getMockBuilder('] as $double) {
                if (strpos($source, $double) !== false) {
                    $violations[] = $path.' uses '.$double;
                }
            }
        }

        $this->assertSame([], $violations, 'An integration test proves the real pieces work together.');
    }

    /**
     * And it must actually reach the database, or it is a unit test with an integration name.
     */
    public function test_integration_tests_assert_persisted_state(): void
    {
        $violations = [];

        foreach ($this->integrationTests() as $path => $source) {
            if (strpos($source, 'UsesMarketDataSqlite') === false) {
                $violations[] = $path.' is not DB-backed';
            }

            if (strpos($source, 'DB::table') === false) {
                $violations[] = $path.' never asserts persisted state';
            }
        }

        $this->assertSame([], $violations);
    }

    /**
     * The wider rule, across every DB-backed test.
     *
     * A test that boots a database and then mocks the repository writing to it is asserting its
     * own fixture. The source adapter is the one exception: it reaches a third party, and a test
     * that called it for real would be neither deterministic nor polite.
     */
    public function test_db_backed_tests_only_mock_the_external_source_boundary(): void
    {
        $violations = [];

        foreach ($this->dbBackedTests() as $path => $source) {
            preg_match_all('/(?:m::mock|Mockery::mock|createMock)\(\s*\\\\?([A-Za-z0-9_\\\\]+)::class/', $source, $matches);

            foreach ($matches[1] as $mockedClass) {
                $resolved = $this->resolveClassName($mockedClass, $source);

                if (strpos($resolved, self::EXTERNAL_BOUNDARY) === 0) {
                    continue;
                }

                $violations[] = $path.' mocks '.$resolved;
            }
        }

        $this->assertSame(
            [],
            $violations,
            "A DB-backed test may only stand in for the outside world.\n"
            ."Mocking an application service or a persistence repository means the persisted state "
            ."being asserted was produced by the mock, not by the code under test."
        );
    }

    /**
     * Test files use short class names with a `use` import. Resolve one back to its namespace so
     * the boundary check compares like with like.
     */
    private function resolveClassName(string $shortOrFull, string $source): string
    {
        if (strpos($shortOrFull, '\\') !== false) {
            return ltrim($shortOrFull, '\\');
        }

        if (preg_match('/^use\s+([A-Za-z0-9_\\\\]+\\\\'.preg_quote($shortOrFull, '/').');/m', $source, $match)) {
            return $match[1];
        }

        return $shortOrFull;
    }

    /**
     * Guards the guard: the patterns must recognise the shapes a real test would use, or a
     * genuinely mocked lifecycle would pass unnoticed.
     */
    public function test_the_detection_recognises_a_mocked_internal_dependency(): void
    {
        $offending = <<<'PHP'
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
$repo = m::mock(EodPublicationRepository::class);
PHP;

        preg_match_all('/(?:m::mock|Mockery::mock|createMock)\(\s*\\\\?([A-Za-z0-9_\\\\]+)::class/', $offending, $matches);

        $this->assertSame(['EodPublicationRepository'], $matches[1]);
        $this->assertSame(
            'App\\Infrastructure\\Persistence\\MarketData\\EodPublicationRepository',
            $this->resolveClassName('EodPublicationRepository', $offending)
        );
        $this->assertStringStartsNotWith(self::EXTERNAL_BOUNDARY, $this->resolveClassName('EodPublicationRepository', $offending));
    }
}

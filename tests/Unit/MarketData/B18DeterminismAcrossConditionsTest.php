<?php

use App\Application\MarketData\Services\DeterministicHashService;

/**
 * `MD-B18-A002` — `MD-S002-R0004`.
 *
 * > deterministic output across supported runtime/locale/concurrency conditions
 *
 * Three dimensions, and they are not in the same state.
 *
 * **Locale** was proven by nothing and is a real hazard on the declared runtime range. PHP's `%g`
 * conversion is locale-aware, so `sprintf('%.17g', 1.5)` returns `1,5` under a comma-decimal locale;
 * `DeterministicHashService::fixedDecimal()` used it to render float inputs, and the regex that
 * parses the result then refuses the value outright. A platform whose artifact hashes depend on the
 * operating system's locale has no determinism claim at all, and nothing in the suite would have
 * said so.
 *
 * **Concurrency** shows up here as order independence. Concurrent producers do not agree on physical
 * row order, so a batch hash that depended on the order rows arrived in would differ between two
 * runs over identical facts. `serializeRows()` sorts on a stable key derived from the values, and
 * that is the property asserted.
 *
 * **Runtime** cannot be closed from inside one process. `composer.json` declares `^7.3|^8.0`, so the
 * supported runtime is a range and a single execution cannot compare two of them. The last test
 * records that honestly and fails if the declared range ever narrows to one runtime, at which point
 * the dimension becomes provable and `MD-S002-R0004` can be revisited.
 */
class B18DeterminismAcrossConditionsTest extends TestCase
{
    /** Locales whose decimal separator is a comma, in the spellings Windows and glibc each accept. */
    private const COMMA_DECIMAL_LOCALES = [
        'de_DE.UTF-8', 'de_DE', 'German_Germany.1252', 'German',
        'fr_FR.UTF-8', 'fr_FR', 'French_France.1252',
    ];

    /** @var string|false */
    private $previousLocale;

    protected function setUp(): void
    {
        parent::setUp();
        $this->previousLocale = setlocale(LC_ALL, '0');
    }

    protected function tearDown(): void
    {
        // A leaked locale would change every later test in the process, so it is restored even when
        // the assertion above fails.
        if (is_string($this->previousLocale) && $this->previousLocale !== '') {
            setlocale(LC_ALL, $this->previousLocale);
        } else {
            setlocale(LC_ALL, 'C');
        }

        parent::tearDown();
    }

    /**
     * The float path. A close of 1.5 must render as `1.5000` whatever the operating system thinks a
     * decimal point looks like.
     */
    public function test_an_owned_decimal_field_normalizes_identically_under_a_comma_decimal_locale(): void
    {
        $service = new DeterministicHashService();

        setlocale(LC_ALL, 'C');
        $c = $service->normalizeValue(1.5, 'close');

        $this->applyCommaDecimalLocale();
        $comma = $service->normalizeValue(1.5, 'close');

        $this->assertSame('1.5000', $c, 'the C-locale rendering is not the canonical one');
        $this->assertSame($c, $comma,
            'an owned decimal field renders differently under a comma-decimal locale, so every '
                .'artifact hash containing a float depends on the operating system locale');
    }

    /**
     * The same value through the hash the platform actually stores, so the property is asserted
     * where it is used rather than only on the helper that formats it.
     */
    public function test_a_batch_hash_is_identical_under_a_comma_decimal_locale(): void
    {
        $service = new DeterministicHashService();
        $columns = ['trade_date', 'ticker_id', 'ticker_code', 'close', 'atr14'];
        $rows = [
            ['trade_date' => '2026-03-24', 'ticker_id' => 1, 'ticker_code' => 'BBCA', 'close' => 9125.5, 'atr14' => 132.25],
            ['trade_date' => '2026-03-24', 'ticker_id' => 2, 'ticker_code' => 'BMRI', 'close' => 5675.25, 'atr14' => 88.5],
        ];

        setlocale(LC_ALL, 'C');
        $c = $service->hashRows($rows, $columns);

        $this->applyCommaDecimalLocale();
        $comma = $service->hashRows($rows, $columns);

        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $c);
        $this->assertSame($c, $comma,
            'the batch hash of identical facts changed with the locale, so two machines that agree '
                .'about the data would disagree about its identity');
    }

    /**
     * The canonical document hash takes the same values through `json_encode`, which is already
     * locale-independent. Asserting it keeps the guarantee stated rather than assumed, so a later
     * change to hand-rolled serialization has something to fail against.
     */
    public function test_a_canonical_document_hash_is_identical_under_a_comma_decimal_locale(): void
    {
        $service = new DeterministicHashService();
        $document = ['coverage_ratio' => 0.9875, 'universe' => ['BBCA', 'BMRI'], 'count' => 2];

        setlocale(LC_ALL, 'C');
        $c = $service->hashCanonicalDocument($document);

        $this->applyCommaDecimalLocale();
        $comma = $service->hashCanonicalDocument($document);

        $this->assertSame($c, $comma, 'the canonical document hash depends on the locale');
    }

    /**
     * The concurrency dimension, in the form a single process can execute it. Two concurrent
     * producers do not agree on the order rows are written or read in; if the batch hash depended on
     * that order, two runs over identical facts would produce different identities and every replay
     * comparison between them would fail for a reason that has nothing to do with the data.
     */
    public function test_row_order_does_not_change_a_batch_hash(): void
    {
        $service = new DeterministicHashService();
        $columns = ['trade_date', 'ticker_id', 'ticker_code', 'close'];
        $rows = [
            ['trade_date' => '2026-03-24', 'ticker_id' => 1, 'ticker_code' => 'BBCA', 'close' => '9125.0000'],
            ['trade_date' => '2026-03-24', 'ticker_id' => 2, 'ticker_code' => 'BMRI', 'close' => '5675.0000'],
            ['trade_date' => '2026-03-24', 'ticker_id' => 3, 'ticker_code' => 'TLKM', 'close' => '3010.0000'],
        ];

        $forward = $service->hashRows($rows, $columns);
        $reversed = $service->hashRows(array_reverse($rows), $columns);
        $rotated = $service->hashRows([$rows[2], $rows[0], $rows[1]], $columns);

        $this->assertSame($forward, $reversed,
            'reversing the rows changed the batch hash, so the identity of a dataset depends on the '
                .'order a concurrent producer happened to write it in');
        $this->assertSame($forward, $rotated, 'rotating the rows changed the batch hash');

        // And the ordering must not be indifferent to content: a hash that ignored the values would
        // satisfy the two assertions above and prove nothing.
        $changed = $rows;
        $changed[0]['close'] = '9126.0000';
        $this->assertNotSame($forward, $service->hashRows($changed, $columns),
            'a one-tick change left the batch hash unchanged, so order independence was bought by '
                .'ignoring the data');
    }

    /**
     * The runtime dimension, recorded rather than claimed.
     *
     * `composer.json` declares a supported range spanning two major versions, and one process runs
     * one of them. Comparing two runtimes needs a second interpreter, which is an environment the
     * suite cannot create for itself — so this dimension of `MD-S002-R0004` is unproven, and saying
     * so in an executable place is worth more than a note.
     *
     * If the declared support ever narrows to a single runtime, this fails: at that point "across
     * supported runtimes" is a claim about one runtime, the suite already executes on it, and the
     * row can be bound instead of carried.
     */
    public function test_the_supported_runtime_range_still_spans_more_than_one_runtime(): void
    {
        $composer = json_decode((string) file_get_contents(dirname(__DIR__, 3).'/composer.json'), true);
        $constraint = (string) ($composer['require']['php'] ?? '');

        $this->assertNotSame('', $constraint, 'composer.json no longer declares a php constraint');

        $majors = [];
        foreach (preg_split('/\s*\|\|?\s*/', $constraint) as $alternative) {
            if (preg_match('/(\d+)\./', $alternative, $m) === 1) {
                $majors[(int) $m[1]] = true;
            }
        }

        $this->assertGreaterThan(1, count($majors),
            'the supported runtime range has narrowed to a single major version ('.$constraint.'); '
                .'the runtime dimension of MD-S002-R0004 is now provable on the runtime this suite '
                .'already executes on, so bind it rather than leaving it recorded as a gap');

        $this->assertArrayHasKey((int) PHP_MAJOR_VERSION, $majors,
            'this suite is executing on a runtime the platform does not declare support for');
    }

    private function applyCommaDecimalLocale(): void
    {
        $applied = @setlocale(LC_ALL, self::COMMA_DECIMAL_LOCALES);
        if ($applied === false) {
            $this->markTestSkipped('no comma-decimal locale is installed on this host, so the '
                .'locale dimension cannot be executed here');
        }

        $point = localeconv();
        if (($point['decimal_point'] ?? '.') === '.') {
            $this->markTestSkipped('the locale applied ('.$applied.') still uses a decimal point, '
                .'so it cannot demonstrate anything about locale dependence');
        }
    }
}

<?php

use App\Infrastructure\Persistence\MarketData\MarketBenchmarkRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * Behavioural cover for how the IHSG 20-day return is resolved.
 *
 * `MarketBenchmarkIndicatorExtensionStaticGuardTest` asserted that the repository contains
 * "where('trade_date', $tradeDate)" and no latest-date shortcut. The repository itself had no
 * test of any kind.
 *
 * This value is the denominator of relative strength. `rs_20_vs_ihsg` is the equity's 20-day
 * return minus the index's, and it is the field that says whether a ticker is outperforming the
 * market — the distinction a weekly-swing shortlist is built on. A wrong value here is wrong for
 * every ticker on the date at once, and it is wrong in a way that still looks like a number.
 *
 * The case that matters most is a benchmark with no usable history. IHSG's indicator row exists
 * with roc_20 NULL and invalid_reason_code IND_INSUFFICIENT_HISTORY. If that resolved to 0.0
 * instead of null, relative strength would silently become the raw equity return, and every
 * ticker would appear to be beating a market nobody measured.
 */
class BenchmarkRoc20ResolutionTest extends TestCase
{
    use UsesMarketDataSqlite;

    private const TRADE_DATE = '2026-03-20';
    private const SET_VERSION = 'v1';

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

    private function seedIndicator(array $override = []): void
    {
        DB::table('market_benchmark_indicators')->insert(array_merge([
            'benchmark_code' => 'IHSG',
            'trade_date' => self::TRADE_DATE,
            'roc_20' => '4.2500000000',
            'ma20' => null,
            'ma50' => null,
            'ma20_slope_pct' => null,
            'close_to_ma20_pct' => null,
            'close_to_ma50_pct' => null,
            'is_valid' => 1,
            'invalid_reason_code' => null,
            'indicator_set_version' => self::SET_VERSION,
            'created_at' => self::TRADE_DATE.' 17:20:00',
            'updated_at' => self::TRADE_DATE.' 17:20:00',
        ], $override));
    }

    private function roc20($tradeDate = self::TRADE_DATE, $setVersion = self::SET_VERSION, $code = 'IHSG')
    {
        return (new MarketBenchmarkRepository())->benchmarkRoc20($code, $tradeDate, $setVersion);
    }

    public function test_a_recorded_benchmark_return_is_resolved(): void
    {
        $this->seedIndicator();

        $this->assertSame(4.25, $this->roc20());
    }

    /**
     * The one that must never become zero. IND_INSUFFICIENT_HISTORY means the index could not be
     * measured, not that it did not move.
     */
    public function test_a_benchmark_without_enough_history_resolves_to_null_not_zero(): void
    {
        $this->seedIndicator([
            'roc_20' => null,
            'is_valid' => 0,
            'invalid_reason_code' => 'IND_INSUFFICIENT_HISTORY',
        ]);

        $this->assertNull($this->roc20(), 'An unmeasurable benchmark must not read as a flat market.');
    }

    /**
     * A genuine zero is a real observation and must survive as one: the index closed the 20-day
     * window exactly where it began.
     */
    public function test_a_genuine_zero_return_is_preserved(): void
    {
        $this->seedIndicator(['roc_20' => '0.0000000000']);

        $this->assertSame(0.0, $this->roc20());
    }

    /**
     * Exact date only. Relative strength on a date must use that date's market move; borrowing a
     * neighbouring session would misstate it for the whole universe at once.
     *
     * @dataProvider neighbouringDates
     */
    public function test_a_neighbouring_date_is_never_borrowed(string $seededDate, string $why): void
    {
        $this->seedIndicator(['trade_date' => $seededDate]);

        $this->assertNull($this->roc20(), 'must not borrow: '.$why);
    }

    public function neighbouringDates(): array
    {
        return [
            'the previous session' => ['2026-03-19', 'yesterday is not today'],
            'the next session' => ['2026-03-23', 'tomorrow has not happened'],
        ];
    }

    /**
     * Indicator set versions are separate vocabularies. Reading a value computed under different
     * formulas would mix two definitions of the same field.
     */
    public function test_a_different_indicator_set_version_is_not_used(): void
    {
        $this->seedIndicator(['indicator_set_version' => 'v2']);

        $this->assertNull($this->roc20());
    }

    public function test_a_missing_benchmark_resolves_to_null(): void
    {
        $this->assertNull($this->roc20());
    }

    /**
     * Callers pass the code as a literal. Normalisation means a lowercase or padded code still
     * resolves rather than silently returning null and disabling relative strength.
     *
     * @dataProvider codeVariants
     */
    public function test_the_benchmark_code_is_normalised(string $code): void
    {
        $this->seedIndicator();

        $this->assertSame(4.25, $this->roc20(self::TRADE_DATE, self::SET_VERSION, $code));
    }

    public function codeVariants(): array
    {
        return [
            'canonical' => ['IHSG'],
            'lowercase' => ['ihsg'],
            'padded' => ['  IHSG  '],
            'mixed case' => ['Ihsg'],
        ];
    }

    /**
     * The batch resolver must agree with the single one, including on exclusion: a benchmark
     * whose return is null is absent from the result rather than present as zero.
     */
    public function test_the_batch_resolver_omits_benchmarks_with_no_usable_return(): void
    {
        $this->seedIndicator();
        $this->seedIndicator([
            'benchmark_code' => 'LQ45',
            'roc_20' => null,
            'invalid_reason_code' => 'IND_INSUFFICIENT_HISTORY',
        ]);

        $resolved = (new MarketBenchmarkRepository())->benchmarkRoc20s(['IHSG', 'LQ45'], self::TRADE_DATE, self::SET_VERSION);

        $this->assertSame(['IHSG' => 4.25], $resolved);
        $this->assertArrayNotHasKey('LQ45', $resolved);
    }

    public function test_the_batch_resolver_returns_nothing_for_an_empty_request(): void
    {
        $this->seedIndicator();

        $this->assertSame([], (new MarketBenchmarkRepository())->benchmarkRoc20s([], self::TRADE_DATE, self::SET_VERSION));
    }
}

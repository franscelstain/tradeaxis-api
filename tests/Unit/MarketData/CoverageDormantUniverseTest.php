<?php

use App\Application\MarketData\Services\CoverageGateEvaluator;
use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * Owner contract: docs/market_data/book/Coverage_Universe_Definition_LOCKED.md
 */
class CoverageDormantUniverseTest extends TestCase
{
    use UsesMarketDataSqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
        $this->seedCalendar();
    }

    protected function tearDown(): void
    {
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    /** 80 consecutive weekday trading dates ending 2026-07-28. */
    private function seedCalendar(): void
    {
        $date = strtotime('2026-07-28');
        $added = 0;

        while ($added < 90) {
            $dow = (int) date('N', $date);
            if ($dow <= 5) {
                $this->seedVerifiedMarketCalendarDate(date('Y-m-d', $date));
                $added++;
            }
            $date = strtotime('-1 day', $date);
        }
    }

    private function seedTicker($tickerId, $code): void
    {
        DB::table('tickers')->insert([
            'ticker_id' => $tickerId,
            'ticker_code' => $code,
            'company_name' => $code.' Tbk',
            'is_active' => 1,
        ]);
    }

    private function seedBar($tickerId, $date, $volume = 1000): void
    {
        DB::table('eod_bars')->insert([
            'trade_date' => $date, 'ticker_id' => $tickerId,
            'open' => 100, 'high' => 101, 'low' => 99, 'close' => 100,
            'volume' => $volume, 'adj_close' => 100,
            'source' => 'YAHOO_FINANCE', 'run_id' => 1, 'publication_id' => 1,
            'created_at' => '2026-01-01 00:00:00',
        ]);
    }

    private function tradingDates(): array
    {
        return DB::table('md_market_calendar_revisions')->where('is_trading_day', 1)
            ->orderByDesc('cal_date')->pluck('cal_date')
            ->map(function ($v) { return (string) $v; })->all();
    }

    public function test_a_ticker_silent_beyond_the_horizon_is_no_longer_expected(): void
    {
        $dates = $this->tradingDates();

        $this->seedTicker(1, 'LIVE');
        $this->seedTicker(2, 'META');

        // LIVE trades today. META last traded 70 trading days ago, beyond the 60-day horizon.
        $this->seedBar(1, $dates[0]);
        $this->seedBar(2, $dates[70]);

        $dormant = (new EodArtifactRepository())->loadDormantTickerIds([1, 2], $dates[0], 60);

        $this->assertSame([2], $dormant);
    }

    public function test_a_thinly_traded_ticker_inside_the_horizon_stays_expected(): void
    {
        $dates = $this->tradingDates();

        $this->seedTicker(3, 'THIN');
        // Last traded 30 trading days ago: illiquid, but not dormant.
        $this->seedBar(3, $dates[30]);

        $this->assertSame([], (new EodArtifactRepository())->loadDormantTickerIds([3], $dates[0], 60));
    }

    /**
     * A dormant ticker stays in the denominator and counts as missing.
     *
     * This test previously asserted the opposite, and the opposite is what the contract forbids.
     * `Coverage_Universe_Definition_LOCKED.md:35-38` states dormancy cannot prove `NOT_EXPECTED`,
     * and `:45` gives the reason: a ticker that stops arriving because the feed broke is
     * indistinguishable from one that stopped trading, so excluding the quiet ones removes exactly
     * the evidence a provider outage would appear in. Coverage here is 2/3, not a clean 1.0.
     */
    public function test_a_dormant_ticker_stays_in_the_denominator(): void
    {
        $dates = $this->tradingDates();
        $today = $dates[0];

        $this->seedTicker(4, 'AAAA');
        $this->seedTicker(5, 'BBBB');
        $this->seedTicker(6, 'GONE');

        $this->seedBar(4, $today);
        $this->seedBar(5, $today);
        $this->seedBar(6, $dates[75]);

        $coverage = app(CoverageGateEvaluator::class)->evaluate($today);

        $this->assertSame(3, $coverage['expected_universe_count'], 'the quiet ticker is still expected');
        $this->assertSame(2, $coverage['available_eod_count']);
        $this->assertSame(1, $coverage['missing_eod_count'], 'its absence is missing data, not an exemption');
        $this->assertEqualsWithDelta(2 / 3, $coverage['coverage_ratio'], 0.000001);
    }

    /**
     * The deprecated exclusion reason is never emitted. The registry records any runtime emission
     * as a migration failure, so its absence is the assertion.
     */
    public function test_the_deprecated_dormancy_exclusion_reason_is_never_emitted(): void
    {
        $dates = $this->tradingDates();
        $today = $dates[0];

        $this->seedTicker(7, 'AAAA');
        $this->seedTicker(8, 'GONE');
        $this->seedBar(7, $today);
        $this->seedBar(8, $dates[80]);

        $coverage = app(CoverageGateEvaluator::class)->evaluate($today);

        $this->assertNotContains('COVERAGE_DORMANT_TICKERS_EXCLUDED', $coverage['reason_codes']);
        $this->assertSame(0, $coverage['coverage_bar_not_expected_count'], 'only verified suspension may reduce the denominator');
    }

    /**
     * Dormancy still cannot improve the ratio even when every quiet ticker is quiet. Without a
     * live ticker to balance it, coverage collapses rather than reporting a clean denominator.
     */
    public function test_a_universe_of_dormant_tickers_reports_low_coverage_not_perfect_coverage(): void
    {
        $dates = $this->tradingDates();
        $today = $dates[0];

        $this->seedTicker(9, 'GONE1');
        $this->seedTicker(13, 'GONE2');
        $this->seedBar(9, $dates[75]);
        $this->seedBar(13, $dates[80]);

        $coverage = app(CoverageGateEvaluator::class)->evaluate($today);

        $this->assertSame(2, $coverage['expected_universe_count']);
        $this->assertSame(0, $coverage['available_eod_count']);
        $this->assertEqualsWithDelta(0.0, $coverage['coverage_ratio'], 0.000001);
        $this->assertSame('FAIL', $coverage['coverage_gate_status'], 'a silent market is a failure, not a pass');
    }

    /**
     * The decisive case from the archive: a ticker that still emits bars every day but has
     * never traded. The provider carries a stale price forward with volume 0, so absence
     * alone would not catch it for months.
     */
    public function test_a_ticker_emitting_bars_with_zero_volume_is_dormant(): void
    {
        $dates = $this->tradingDates();

        $this->seedTicker(11, 'DEAD');
        // A bar on every one of the last 70 trading days, none of them traded.
        foreach (array_slice($dates, 0, 70) as $date) {
            $this->seedBar(11, $date, 0);
        }

        $this->assertSame(
            [11],
            (new EodArtifactRepository())->loadDormantTickerIds([11], $dates[0], 60),
            'a bar with no volume records a price nobody transacted at'
        );
    }

    public function test_a_single_traded_day_inside_the_window_keeps_a_ticker_active(): void
    {
        $dates = $this->tradingDates();

        $this->seedTicker(12, 'THIN');
        foreach (array_slice($dates, 0, 70) as $date) {
            $this->seedBar(12, $date, 0);
        }
        // One real trade 50 trading days ago is enough to stay in the universe.
        DB::table('eod_bars')->where('ticker_id', 12)->where('trade_date', $dates[50])->update(['volume' => 500]);

        $this->assertSame([], (new EodArtifactRepository())->loadDormantTickerIds([12], $dates[0], 60));
    }

    /** Dormancy is not delisting: a returning ticker rejoins the universe by itself. */
    public function test_a_dormant_ticker_returns_to_the_universe_once_it_trades_again(): void
    {
        $dates = $this->tradingDates();
        $today = $dates[0];

        $this->seedTicker(10, 'BACK');
        $this->seedBar(10, $dates[75]);

        $this->assertSame([10], (new EodArtifactRepository())->loadDormantTickerIds([10], $today, 60));

        $this->seedBar(10, $today);

        $this->assertSame([], (new EodArtifactRepository())->loadDormantTickerIds([10], $today, 60));
    }
}

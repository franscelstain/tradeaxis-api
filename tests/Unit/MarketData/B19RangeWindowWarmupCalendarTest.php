<?php

use App\Application\MarketData\Services\BackfillLifecycleOrchestrator;
use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;
use Tests\Support\UsesMarketDataSqlite;

/**
 * `MD-B19` — range-window warmup resolves through the market calendar.
 *
 * `Source_Data_Acquisition_Contract_LOCKED.md` (`MD-S053`) states the rule and says why it exists:
 *
 *   > This rule exists so rolling indicators and benchmark indicators do not become NULL merely
 *   > because a long holiday/weekend sequence made calendar-day warmup shorter than the required
 *   > trading-day history, while still allowing deterministic NULL outputs at the beginning of the
 *   > available dataset.
 *
 * It forbids `requested_start - N calendar days` and fixed holiday buffers as the source of truth,
 * and requires `warmup_start = tradingDateWindowStart(first_requested_trading_date, N)` capped at
 * the first available trading date. It then requires two things that pull in opposite directions:
 * fail-fast for a non-trading requested date, and **no** fail-fast merely because the dataset start
 * has fewer prior trading dates than the ideal horizon.
 *
 * A guard that only proved the fail-fast half would be satisfied by an implementation that refuses
 * the dataset-start boundary too, which is the outcome the rule exists to prevent. Both halves are
 * asserted here.
 *
 * The discriminator is a real holiday sequence: N trading days back and N calendar days back land
 * on different dates, and the test asserts the resolver picks the trading-day one.
 */
class B19RangeWindowWarmupCalendarTest extends TestCase
{
    use UsesMarketDataSqlite;

    /** A long exchange closure, so trading-day and calendar-day arithmetic cannot agree. */
    private const HOLIDAYS = [
        '2026-03-16', '2026-03-17', '2026-03-18', '2026-03-19', '2026-03-20',
        '2026-03-23', '2026-03-24', '2026-03-25',
    ];

    private const DATASET_START = '2026-03-02';

    private const REQUESTED = '2026-04-01';

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();

        $date = strtotime(self::DATASET_START);
        $end = strtotime(self::REQUESTED);
        while ($date <= $end) {
            $iso = date('Y-m-d', $date);
            $weekday = (int) date('N', $date) <= 5;
            $this->seedVerifiedMarketCalendarDate($iso, $weekday && ! in_array($iso, self::HOLIDAYS, true));
            $date = strtotime('+1 day', $date);
        }
    }

    protected function tearDown(): void
    {
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    private function calendar(): MarketCalendarRepository
    {
        return new MarketCalendarRepository();
    }

    /** The orchestrator's own resolver, driven without standing up its six other collaborators. */
    private function warmupStart(string $firstRequestedTradingDate): string
    {
        $orchestrator = (new ReflectionClass(BackfillLifecycleOrchestrator::class))
            ->newInstanceWithoutConstructor();

        $calendarProperty = new ReflectionProperty(BackfillLifecycleOrchestrator::class, 'calendar');
        $calendarProperty->setAccessible(true);
        $calendarProperty->setValue($orchestrator, $this->calendar());

        $method = new ReflectionMethod(BackfillLifecycleOrchestrator::class, 'warmupStart');
        $method->setAccessible(true);

        return (string) $method->invoke($orchestrator, $firstRequestedTradingDate);
    }

    /**
     * `MD-S053-R0222`: the boundary is the Nth prior trading date, and `MD-S053-R0219` forbids the
     * Nth prior calendar date being the source of truth. The seeded closure makes the two differ.
     */
    public function test_warmup_start_is_the_governed_trading_day_boundary_not_calendar_day_arithmetic(): void
    {
        $warmupTradingDays = 10;
        config(['market_data.source.api_backfill.warmup_trading_days' => $warmupTradingDays]);

        $resolved = $this->warmupStart(self::REQUESTED);
        $governed = $this->calendar()->tradingDateWindowStart(self::REQUESTED, $warmupTradingDays);
        $calendarDayArithmetic = date('Y-m-d', strtotime(self::REQUESTED.' -'.$warmupTradingDays.' days'));

        $this->assertSame($governed, $resolved,
            'warmup_start is not the governed trading-day window start');

        $this->assertNotSame($calendarDayArithmetic, $resolved,
            'warmup_start equals requested_start minus N calendar days, which MD-S053 forbids as the '
                .'source of truth. The seeded holiday sequence exists so these two cannot coincide.');

        $this->assertLessThan($calendarDayArithmetic, $resolved,
            'a trading-day window spanning a closure must reach further back than the same number of '
                .'calendar days, or the warmup history is short by exactly the closure');

        // The boundary must itself be a trading day, not a date that merely exists in the calendar.
        $tradingDates = $this->calendar()->tradingDatesBetween($resolved, self::REQUESTED);
        $this->assertSame($resolved, (string) $tradingDates[0],
            'the resolved warmup boundary is not itself a verified trading date');
        $this->assertCount($warmupTradingDays, $tradingDates,
            'the window does not span exactly the configured number of trading days');
    }

    /**
     * `MD-S053`: "no fail-fast solely because the dataset-start boundary has fewer prior trading
     * dates than the ideal warmup horizon".
     *
     * This is the half a fail-fast-only implementation gets wrong, and getting it wrong is what
     * makes early indicators unavailable instead of deterministically NULL.
     */
    public function test_the_dataset_start_boundary_caps_the_warmup_instead_of_failing(): void
    {
        config(['market_data.source.api_backfill.warmup_trading_days' => 500]);

        $resolved = $this->warmupStart(self::REQUESTED);
        $available = $this->calendar()->tradingDatesBetween(self::DATASET_START, self::REQUESTED);

        $this->assertSame((string) $available[0], $resolved,
            'a warmup horizon longer than the dataset must cap at the first available trading date '
                .'rather than fail');
    }

    /** `MD-S053`: fail-fast validation for non-trading requested dates. */
    public function test_a_non_trading_requested_date_fails_fast(): void
    {
        config(['market_data.source.api_backfill.warmup_trading_days' => 10]);

        // 2026-03-17 was seeded as a closure above.
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('MARKET_CALENDAR_REQUIRES_REQUESTED_TRADING_DATE');

        $this->warmupStart('2026-03-17');
    }
}

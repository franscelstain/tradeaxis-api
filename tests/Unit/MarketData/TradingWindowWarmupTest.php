<?php

use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * Behavioural cover for the indicator warm-up window.
 *
 * `ApiBackfillLifecycleStaticGuardTest` asserted that the orchestrator contains the string
 * "tradingDateWindowStart(...)" and does not contain a subDays() call on a calendar-day config
 * key. That is a useful prohibition and says nothing about whether the window it computes is
 * right.
 *
 * The distinction it guards is specific to this platform. Indicators are computed over trading
 * days: MA20 needs twenty bars, MA50 fifty, and the weekly-swing PLAN fields depend on hh20,
 * roc20 and atr14. A warm-up measured in calendar days silently comes up short, because IDX
 * closes on weekends and for multi-day national holidays — a 120-calendar-day reach back covers
 * only about 82 trading days.
 *
 * What makes it dangerous rather than merely wrong is the platform's own non-error indicator
 * rule: insufficient history produces NULL for the affected fields and does not fail the
 * publication. So a short warm-up publishes READABLE days whose indicators are hollow, and
 * nothing reports a fault.
 */
class TradingWindowWarmupTest extends TestCase
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

    /**
     * Two calendar months of 2026, weekends closed, plus a closure from 18 to 20 March standing
     * in for a national holiday block.
     */
    private function seedCalendar(): void
    {
        $holidays = ['2026-03-18', '2026-03-19', '2026-03-20'];

        $revisions = [];
        $cursor = new DateTimeImmutable('2026-02-01');
        $end = new DateTimeImmutable('2026-03-31');

        while ($cursor <= $end) {
            $date = $cursor->format('Y-m-d');
            $isWeekend = in_array($cursor->format('N'), ['6', '7'], true);
            $isHoliday = in_array($date, $holidays, true);

            $trading = ($isWeekend || $isHoliday) ? 0 : 1;
            $revisions[] = [
                'market_code' => 'IDX', 'market_segment' => 'REGULAR', 'cal_date' => $date,
                'revision_uid' => hash('sha256', 'warmup|'.$date.'|'.$trading),
                'timezone' => 'Asia/Jakarta', 'is_trading_day' => $trading, 'is_half_day' => 0,
                'session_state' => $trading ? 'COMPLETED' : 'CLOSED',
                'session_open_at' => $trading ? $date.' 09:00:00' : null,
                'session_close_at' => $trading ? $date.' 16:00:00' : null,
                'completed_at' => $trading ? $date.' 16:00:00' : null,
                'recorded_at' => $date.' 17:00:00', 'source_ref' => 'https://www.idx.co.id/calendar',
                'source_version' => 'idx-calendar-2026', 'provenance_tier' => 'VERIFIED',
                'reconciled_at' => $date, 'reconciliation_source_ref' => 'https://www.idx.co.id/calendar',
            ];

            $cursor = $cursor->modify('+1 day');
        }

        foreach (array_chunk($revisions, 25) as $chunk) {
            DB::table('md_market_calendar_revisions')->insert($chunk);
        }
    }

    private function calendar(): MarketCalendarRepository
    {
        return new MarketCalendarRepository();
    }

    /**
     * @return string[]
     */
    private function tradingDatesUpTo(string $endDate): array
    {
        return DB::table('md_market_calendar_revisions')
            ->where('cal_date', '<=', $endDate)
            ->where('is_trading_day', 1)
            ->orderBy('cal_date')
            ->pluck('cal_date')
            ->map(function ($value) {
                return substr((string) $value, 0, 10);
            })
            ->all();
    }

    /**
     * The window is counted in trading days, so it reaches back further than the same number of
     * calendar days. This is the whole point of the method.
     */
    public function test_the_window_counts_trading_days_not_calendar_days(): void
    {
        $start = $this->calendar()->tradingDateWindowStart('2026-03-31', 20);

        $calendarDaysBack = (new DateTimeImmutable('2026-03-31'))->diff(new DateTimeImmutable($start))->days;

        $this->assertGreaterThan(
            20,
            $calendarDaysBack,
            'A twenty trading day window must span more than twenty calendar days.'
        );

        // March 2026 holds only nineteen trading days up to the 31st: five weekends removed and
        // three more days lost to the holiday block. The twentieth therefore falls in February.
        // Twenty trading days span thirty-two calendar days here — a sixty percent overshoot,
        // and the reason a calendar-day warm-up comes up short without ever saying so.
        $this->assertSame('2026-02-27', $start);
        $this->assertSame(32, $calendarDaysBack);
    }

    /**
     * The end date is part of the window. Off by one here means every backfill warms up one bar
     * short, which is invisible until an indicator at the boundary is quietly null.
     */
    public function test_the_window_includes_the_requested_date_itself(): void
    {
        $tradingDates = $this->tradingDatesUpTo('2026-03-31');
        $expectedStart = $tradingDates[count($tradingDates) - 20];

        $this->assertSame($expectedStart, $this->calendar()->tradingDateWindowStart('2026-03-31', 20));

        // Asking for one trading day yields the requested date and nothing earlier.
        $this->assertSame('2026-03-31', $this->calendar()->tradingDateWindowStart('2026-03-31', 1));
    }

    /**
     * Closed days must not be consumed by the count. If the holiday block were counted, the
     * window would end three trading days short.
     */
    public function test_closed_days_are_not_counted_toward_the_window(): void
    {
        $start = $this->calendar()->tradingDateWindowStart('2026-03-31', 10);

        $this->assertSame(
            10,
            count(array_filter($this->tradingDatesUpTo('2026-03-31'), function ($date) use ($start) {
                return $date >= $start;
            })),
            'The window must contain exactly the requested number of trading days.'
        );

        foreach (['2026-03-18', '2026-03-19', '2026-03-20'] as $holiday) {
            $this->assertNotContains($holiday, $this->tradingDatesUpTo('2026-03-31'));
        }
    }

    /**
     * The returned start is itself a trading day. A start landing on a closed day would be a
     * date the pipeline can never request.
     */
    public function test_the_window_start_is_itself_a_trading_day(): void
    {
        foreach ([1, 5, 10, 20, 30] as $required) {
            $start = $this->calendar()->tradingDateWindowStart('2026-03-31', $required);

            $this->assertContains($start, $this->tradingDatesUpTo('2026-03-31'), $required.' trading day window');
        }
    }

    /**
     * A date the exchange was closed on cannot anchor a window, and saying so beats returning a
     * silently shifted one.
     *
     * @dataProvider closedDates
     */
    public function test_a_closed_date_cannot_anchor_a_window(string $closedDate, string $why): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('MARKET_CALENDAR_REQUIRES_REQUESTED_TRADING_DATE');

        $this->calendar()->tradingDateWindowStart($closedDate, 20);
    }

    public function closedDates(): array
    {
        return [
            'a Saturday' => ['2026-03-28', 'the exchange is closed at weekends'],
            'a holiday' => ['2026-03-19', 'the exchange is closed for the holiday block'],
            'a date outside the calendar' => ['2027-01-04', 'the calendar does not reach that far'],
        ];
    }

    /**
     * The default is a partial window: if the calendar cannot reach back far enough, the earliest
     * available trading day is returned rather than an error.
     *
     * That is a deliberate fail-open and worth stating plainly, because it is how a backfill can
     * warm up on less history than it asked for without anything being reported. Callers that
     * cannot tolerate it must pass false.
     */
    public function test_an_insufficient_calendar_silently_shortens_the_window_by_default(): void
    {
        $earliest = $this->tradingDatesUpTo('2026-03-31')[0];

        $this->assertSame($earliest, $this->calendar()->tradingDateWindowStart('2026-03-31', 9999));
    }

    public function test_an_insufficient_calendar_can_be_made_an_error(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('MARKET_CALENDAR_INSUFFICIENT_TRADING_WINDOW');

        $this->calendar()->tradingDateWindowStart('2026-03-31', 9999, false);
    }

    /**
     * A non-positive requirement is clamped rather than producing an empty or inverted window.
     *
     * @dataProvider nonPositiveRequirements
     */
    public function test_a_non_positive_requirement_still_yields_the_requested_date($required): void
    {
        $this->assertSame('2026-03-31', $this->calendar()->tradingDateWindowStart('2026-03-31', $required));
    }

    public function nonPositiveRequirements(): array
    {
        return [
            'zero' => [0],
            'negative' => [-5],
            'not a number' => ['none'],
        ];
    }
}

<?php

use App\Infrastructure\MarketData\Source\PublicApiEodBarsAdapter;
use Carbon\Carbon;

/**
 * Behavioural cover for the period bounds that make arbitrary-date acquisition possible.
 *
 * `ApiBackfillLifecycleStaticGuardTest` asserted that the strings "period1={period1}" and
 * "period2={period2}" appear in the config file. That confirms a template has placeholders;
 * it says nothing about whether the computed window actually contains the requested date.
 *
 * The contract at stake is the date-driven capability locked in docs/market_data/README.md:
 * the platform must accept any trade date and must not be limited by the provider's default
 * `range=10d` query window. Period bounds are how that limit is escaped, so getting them
 * wrong silently returns the wrong window rather than failing.
 */
class YahooPeriodBoundsTest extends TestCase
{
    private function invoke(string $method, array $args)
    {
        $reflection = new ReflectionMethod(PublicApiEodBarsAdapter::class, $method);
        $reflection->setAccessible(true);

        return $reflection->invokeArgs(app(PublicApiEodBarsAdapter::class), $args);
    }

    private function timezone(): string
    {
        return (string) config('market_data.platform.timezone', 'Asia/Jakarta');
    }

    public function test_single_date_bounds_bracket_the_requested_trade_date(): void
    {
        $bounds = $this->invoke('yahooPeriodBounds', ['2026-07-28']);

        $target = Carbon::parse('2026-07-28', $this->timezone())->startOfDay()->timestamp;

        $this->assertLessThan($target, (int) $bounds['period1'], 'window must open before the requested date');
        $this->assertGreaterThan($target, (int) $bounds['period2'], 'window must close after the requested date');
    }

    /**
     * The upper bound is exclusive, so the end date only survives if the bound is pushed a
     * day past it. An off-by-one here silently drops the most recent bar.
     */
    public function test_range_bounds_cover_the_whole_inclusive_range(): void
    {
        $bounds = $this->invoke('yahooRangePeriodBounds', ['2026-07-01', '2026-07-28']);

        $start = Carbon::parse('2026-07-01', $this->timezone())->startOfDay()->timestamp;
        $end = Carbon::parse('2026-07-28', $this->timezone())->startOfDay()->timestamp;

        $this->assertSame($start, (int) $bounds['period1'], 'range must start exactly at the first requested day');
        $this->assertGreaterThan($end, (int) $bounds['period2'], 'range must extend past the last requested day');
    }

    /**
     * The point of period bounds: a date years in the past must be reachable. If acquisition
     * were still governed by the provider's rolling default window, this range would be
     * unreachable and the archive could never have been built.
     */
    public function test_a_range_far_outside_the_provider_default_window_is_still_expressible(): void
    {
        $bounds = $this->invoke('yahooRangePeriodBounds', ['2023-01-02', '2023-01-31']);

        $this->assertGreaterThan(0, (int) $bounds['period1']);
        $this->assertGreaterThan((int) $bounds['period1'], (int) $bounds['period2']);

        $spanDays = ((int) $bounds['period2'] - (int) $bounds['period1']) / 86400;

        $this->assertEqualsWithDelta(30, $spanDays, 1, 'January 2023 spans 30 days inclusive of both ends');
    }

    public function test_bounds_are_resolved_in_the_configured_platform_timezone(): void
    {
        config(['market_data.platform.timezone' => 'Asia/Jakarta']);
        $jakarta = (int) $this->invoke('yahooPeriodBounds', ['2026-07-28'])['period1'];

        config(['market_data.platform.timezone' => 'UTC']);
        $utc = (int) $this->invoke('yahooPeriodBounds', ['2026-07-28'])['period1'];

        config(['market_data.platform.timezone' => 'Asia/Jakarta']);

        // Jakarta is UTC+7, so its midnight arrives seven hours earlier in epoch terms.
        $this->assertSame(7 * 3600, $utc - $jakarta);
    }

    public function test_a_single_day_range_still_produces_a_non_empty_window(): void
    {
        $bounds = $this->invoke('yahooRangePeriodBounds', ['2026-07-28', '2026-07-28']);

        $this->assertSame(
            86400,
            (int) $bounds['period2'] - (int) $bounds['period1'],
            'one requested day must still open a one-day window, not an empty one'
        );
    }
}

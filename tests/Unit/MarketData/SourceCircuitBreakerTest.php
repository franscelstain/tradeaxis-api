<?php

use App\Infrastructure\MarketData\Source\PublicApiEodBarsAdapter;

/**
 * W08 — resilience, retry/backoff/rate limit, and failure taxonomy, stage 5.
 *
 * Owner contract: docs/market_data/book/EOD_SOURCE_OPERATIONAL_RESILIENCE_CONTRACT_LOCKED.md
 *                 — section "Source access self-protection (LOCKED)"
 *
 * The breaker protects the source, not the run. Retry rules already protect the run; nothing
 * protected access to an unofficial free provider from the platform's own retry volume. A
 * universe of this size issues hundreds of requests per date before any retry, so continuing
 * through a wholesale failure multiplies load exactly when the source is refusing.
 */
class SourceCircuitBreakerTest extends TestCase
{
    private function decide(int $failures, int $universe, int $successes)
    {
        $adapter = new PublicApiEodBarsAdapter();
        $method = new ReflectionMethod($adapter, 'openCircuitBreaker');
        $method->setAccessible(true);

        return $method->invoke($adapter, $failures, $universe, $successes);
    }

    /**
     * A wholesale failure opens the breaker rather than marching through the remaining universe.
     */
    public function test_the_breaker_opens_when_failures_dominate_a_material_sample(): void
    {
        $this->assertSame('RUN_SOURCE_CIRCUIT_BREAKER_OPEN', $this->decide(90, 900, 10));
    }

    /**
     * A healthy run is never interrupted. The breaker is a protection, not a throttle on
     * ordinary partial failure, which the partial-tolerant import rule already permits.
     */
    public function test_the_breaker_stays_closed_while_most_requests_succeed(): void
    {
        $this->assertNull($this->decide(10, 900, 890));
    }

    /**
     * Exactly at the threshold the breaker stays closed. The rule is a strict crossing, so a
     * run sitting on the boundary is not stopped by rounding.
     */
    public function test_the_breaker_stays_closed_exactly_at_the_threshold(): void
    {
        $this->assertNull($this->decide(50, 900, 50));
    }

    /**
     * A single early failure is not a signal. Without a minimum sample the breaker would trip on
     * the first transient error of every run and turn a retryable blip into a stopped date.
     */
    public function test_an_early_isolated_failure_does_not_open_the_breaker(): void
    {
        $this->assertNull($this->decide(1, 900, 0));
        $this->assertNull($this->decide(3, 900, 0));
    }

    /**
     * Once the sample is material, a total failure opens immediately rather than waiting for the
     * universe to be exhausted.
     */
    public function test_a_total_failure_opens_as_soon_as_the_sample_is_material(): void
    {
        $this->assertNull($this->decide(4, 900, 0), 'below the minimum sample');
        $this->assertSame('RUN_SOURCE_CIRCUIT_BREAKER_OPEN', $this->decide(45, 900, 0));
    }

    /**
     * A small universe still gets a floor of attempts, so a two-instrument backfill is not
     * stopped by one failure.
     */
    public function test_a_small_universe_keeps_a_minimum_attempt_floor(): void
    {
        $this->assertNull($this->decide(1, 2, 0));
        $this->assertNull($this->decide(2, 2, 0));
    }
}

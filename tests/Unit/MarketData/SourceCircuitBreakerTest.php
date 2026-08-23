<?php

use App\Infrastructure\MarketData\Source\PublicApiEodBarsAdapter;
use App\Infrastructure\MarketData\Source\SourceAcquisitionException;

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
    public function test_the_breaker_opens_when_observed_failures_cross_the_configured_threshold(): void
    {
        $this->assertTrue($this->decide(451, 900, 10));
    }

    /**
     * A healthy run is never interrupted. The breaker is a protection, not a throttle on
     * ordinary partial failure, which the partial-tolerant import rule already permits.
     */
    public function test_the_breaker_stays_closed_while_most_requests_succeed(): void
    {
        $this->assertFalse($this->decide(10, 900, 890));
    }

    /**
     * Exactly at the threshold the breaker stays closed. The rule is a strict crossing, so a
     * run sitting on the boundary is not stopped by rounding.
     */
    public function test_the_breaker_stays_closed_exactly_at_the_threshold(): void
    {
        $this->assertFalse($this->decide(450, 900, 450));
    }

    /**
     * The strategy owns only the configured failure-ratio threshold. A hidden sample floor would
     * create a second unregistered threshold and allow a ratio already above the configured value
     * to continue acquiring.
     */
    public function test_the_breaker_has_no_implicit_minimum_sample_threshold(): void
    {
        $this->assertFalse($this->decide(1, 900, 0));
        $this->assertFalse($this->decide(1, 2, 0));
        $this->assertTrue($this->decide(2, 3, 0));
    }

    /**
     * An empty planned universe has no denominator and therefore nothing to trip the breaker.
     */
    public function test_the_breaker_stays_closed_before_any_acquisition_unit_is_attempted(): void
    {
        $this->assertFalse($this->decide(0, 0, 0));
    }

    public function test_invalid_breaker_threshold_fails_closed_as_configuration_error(): void
    {
        $original = config('market_data.provider.circuit_breaker_error_rate');
        config(['market_data.provider.circuit_breaker_error_rate' => 1.0]);

        try {
            $this->decide(1, 10, 0);
            $this->fail('Invalid breaker threshold must not silently disable source protection.');
        } catch (SourceAcquisitionException $e) {
            $this->assertSame('CONFIG_INVALID', $e->reasonCode());
        } finally {
            config(['market_data.provider.circuit_breaker_error_rate' => $original]);
        }
    }

}

<?php

use App\Infrastructure\MarketData\Source\BenchmarkProviderSymbolResolver;
use App\Infrastructure\MarketData\Source\EquityProviderSymbolResolver;
use PHPUnit\Framework\TestCase;

/**
 * Behavioural cover for the benchmark-versus-equity symbol boundary.
 *
 * MarketBenchmarkIndicatorExtensionStaticGuardTest asserted that the string "^JKSE.JK" does
 * not appear in the adapter or either resolver. That only rules out one already-known bad
 * literal; it says nothing about what the resolvers actually produce, and would miss the same
 * defect arising from any other index symbol.
 *
 * The invariant is that an index symbol passes through untouched while an equity symbol gains
 * the exchange suffix. IHSG resolving to "^JKSE.JK" is the failure this prevents, and it is a
 * real one: the dictionary records it as a mapping that had to be corrected.
 */
class ProviderSymbolResolverTest extends TestCase
{
    private function apiConfig(string $suffix = '.JK'): array
    {
        return ['yahoo' => ['symbol_suffix' => $suffix]];
    }

    public function test_index_symbols_pass_through_without_an_equity_suffix(): void
    {
        $resolver = new BenchmarkProviderSymbolResolver();

        foreach (['^JKSE', '^JKLQ45', '^JKII'] as $symbol) {
            $resolved = $resolver->resolve('IHSG', $symbol, 'INDEX');

            $this->assertSame($symbol, $resolved);
            $this->assertStringNotContainsString('.JK', $resolved, 'an index symbol must never gain the equity suffix');
        }
    }

    public function test_benchmark_resolver_rejects_an_empty_provider_symbol(): void
    {
        $this->expectException(\RuntimeException::class);

        (new BenchmarkProviderSymbolResolver())->resolve('IHSG', '   ', 'INDEX');
    }

    public function test_equity_symbols_gain_the_exchange_suffix(): void
    {
        $resolver = new EquityProviderSymbolResolver();

        $this->assertSame('BBCA.JK', $resolver->resolve('BBCA', $this->apiConfig()));
        $this->assertSame('BBCA.JK', $resolver->resolve('bbca', $this->apiConfig()));
        $this->assertSame('BBCA.JK', $resolver->resolve('  BBCA  ', $this->apiConfig()));
    }

    /** Applying the suffix twice would produce BBCA.JK.JK and fetch nothing. */
    public function test_an_already_suffixed_equity_symbol_is_not_suffixed_again(): void
    {
        $this->assertSame(
            'BBCA.JK',
            (new EquityProviderSymbolResolver())->resolve('BBCA.JK', $this->apiConfig())
        );
    }

    public function test_no_suffix_configured_leaves_the_equity_symbol_untouched(): void
    {
        $this->assertSame(
            'BBCA',
            (new EquityProviderSymbolResolver())->resolve('BBCA', $this->apiConfig(''))
        );
    }

    /**
     * The exact defect the old string check was written against, now expressed as behaviour:
     * routing an index through the equity resolver is what produced "^JKSE.JK".
     */
    public function test_the_two_resolvers_disagree_so_routing_matters(): void
    {
        $benchmark = (new BenchmarkProviderSymbolResolver())->resolve('IHSG', '^JKSE', 'INDEX');
        $equity = (new EquityProviderSymbolResolver())->resolve('^JKSE', $this->apiConfig());

        $this->assertSame('^JKSE', $benchmark);
        $this->assertSame('^JKSE.JK', $equity);

        $this->assertNotSame(
            $benchmark,
            $equity,
            'an index sent through the equity resolver produces the broken symbol, so the adapter must route by instrument type'
        );
    }
}

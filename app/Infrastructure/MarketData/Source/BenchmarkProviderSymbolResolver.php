<?php

namespace App\Infrastructure\MarketData\Source;

use Illuminate\Support\Str;

class BenchmarkProviderSymbolResolver
{
    public function resolve($benchmarkCode, $providerSymbol, $instrumentType)
    {
        $providerSymbol = trim((string) $providerSymbol);

        if ($providerSymbol === '') {
            throw new \RuntimeException('Benchmark provider symbol is required for '.Str::upper(trim((string) $benchmarkCode)).'.');
        }

        if (in_array(Str::upper(trim((string) $instrumentType)), ['INDEX', 'BENCHMARK'], true)) {
            return $providerSymbol;
        }

        return $providerSymbol;
    }
}

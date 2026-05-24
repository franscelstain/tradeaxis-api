<?php

namespace App\Infrastructure\MarketData\Source;

use Illuminate\Support\Str;

class EquityProviderSymbolResolver
{
    public function resolve($tickerCode, array $apiConfig)
    {
        $symbol = Str::upper(trim((string) $tickerCode));
        $suffix = (string) data_get($apiConfig, 'yahoo.symbol_suffix', '');

        if ($symbol === '' || $suffix === '') {
            return $symbol;
        }

        return substr($symbol, -strlen($suffix)) === $suffix
            ? $symbol
            : $symbol.$suffix;
    }
}

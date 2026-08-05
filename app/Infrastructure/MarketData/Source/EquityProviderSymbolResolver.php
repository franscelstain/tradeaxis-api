<?php

namespace App\Infrastructure\MarketData\Source;

use App\Infrastructure\Persistence\MarketData\TemporalIdentityRepository;
use Illuminate\Support\Str;

class EquityProviderSymbolResolver
{
    private $identities;

    public function __construct(TemporalIdentityRepository $identities = null)
    {
        $this->identities = $identities;
    }

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

    /**
     * Resolve a provider symbol from the effective-dated identity contract.
     * Suffix rendering remains available only for isolated adapter previews/tests;
     * production acquisition must set enforce_temporal_mapping=true.
     */
    public function resolveContext($tickerCode, array $apiConfig, $tradeDate, array $context = [])
    {
        if (! empty($context['enforce_temporal_mapping'])) {
            $repository = $this->identities ?: new TemporalIdentityRepository();

            return $repository->resolveProviderContext(
                $tickerCode,
                (string) ($apiConfig['provider'] ?? 'yahoo_finance'),
                $tradeDate,
                $context['known_at'] ?? null
            );
        }

        return [
            'ticker_code' => Str::upper(trim((string) $tickerCode)),
            'provider_symbol' => $this->resolve($tickerCode, $apiConfig),
            'provider_mapping_id' => null,
            'mapping_revision' => 'NON_CANONICAL_PREVIEW_ONLY',
            'listing_id' => null,
        ];
    }
}

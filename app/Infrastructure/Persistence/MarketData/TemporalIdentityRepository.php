<?php

namespace App\Infrastructure\Persistence\MarketData;

use App\Domain\MarketData\MarketDataScope;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TemporalIdentityRepository
{
    public function ensureLegacyProjection(array $tickerCodes = [])
    {
        $this->assertFoundationAvailable();
        $tickerTable = config('market_data.tickers.table', 'tickers');
        $idColumn = config('market_data.tickers.id_column', 'ticker_id');
        $codeColumn = config('market_data.tickers.code_column', 'ticker_code');
        $query = DB::table($tickerTable)->orderBy($idColumn);

        $tickerCodes = array_values(array_unique(array_filter(array_map(function ($code) {
            return Str::upper(trim((string) $code));
        }, $tickerCodes))));

        if ($tickerCodes !== []) {
            $query->whereIn(DB::raw('UPPER(TRIM('.$codeColumn.'))'), $tickerCodes);
        }

        foreach ($query->get() as $ticker) {
            $this->projectTicker($ticker);
        }
    }

    public function universeAsOf($tradeDate, $knownAt = null)
    {
        $tradeDate = MarketDataScope::fromConfig()->assertRequestedDate($tradeDate);
        $this->ensureLegacyProjection();

        return $this->readProjectedUniverseAsOf($tradeDate, $knownAt);
    }

    /**
     * Read the already-established temporal projection without creating missing identity rows.
     * Planning and admission measurement use this fail-closed surface so a read-only operation
     * cannot silently turn legacy master rows into V2 identity facts.
     */
    public function readProjectedUniverseAsOf($tradeDate, $knownAt = null)
    {
        $tradeDate = MarketDataScope::fromConfig()->assertRequestedDate($tradeDate);
        $this->assertFoundationAvailable();
        $query = $this->baseIdentityQuery($tradeDate, $knownAt);

        return $query
            ->orderBy('l.listing_id')
            ->get()
            ->map(function ($row) {
                return $this->identityRow($row);
            })
            ->all();
    }

    public function resolveProviderContext($tickerCode, $provider, $tradeDate, $knownAt = null)
    {
        $tradeDate = MarketDataScope::fromConfig()->assertRequestedDate($tradeDate);
        $tickerCode = Str::upper(trim((string) $tickerCode));
        $provider = Str::lower(trim((string) $provider));
        $this->ensureLegacyProjection([$tickerCode]);

        $query = $this->baseIdentityQuery($tradeDate, $knownAt)
            ->join('md_provider_symbol_mappings as pm', 'pm.listing_id', '=', 'l.listing_id')
            ->whereRaw('UPPER(TRIM(ls.symbol)) = ?', [$tickerCode])
            ->where('pm.provider', $provider)
            ->where('pm.effective_from', '<=', $tradeDate.' 23:59:59')
            ->where(function ($q) use ($tradeDate) {
                $q->whereNull('pm.effective_to')->orWhere('pm.effective_to', '>', $tradeDate.' 00:00:00');
            })
            ->whereNull('pm.retracted_at');

        if ($knownAt !== null) {
            $query->where('pm.recorded_at', '<=', $knownAt);
        }

        $rows = $query
            ->addSelect([
                'pm.provider_mapping_id', 'pm.provider', 'pm.provider_symbol', 'pm.mapping_revision',
                'pm.recorded_at as provider_mapping_recorded_at',
            ])
            ->orderByDesc('pm.recorded_at')
            ->get();

        if ($rows->isEmpty()) {
            throw new \RuntimeException('PROVIDER_SYMBOL_MAPPING_MISSING: '.$provider.' '.$tickerCode.' '.$tradeDate.'.');
        }

        $targets = $rows->map(function ($row) {
            return (int) $row->listing_id.'|'.(string) $row->provider_symbol;
        })->unique()->values();

        if ($targets->count() !== 1) {
            throw new \RuntimeException('PROVIDER_SYMBOL_MAPPING_AMBIGUOUS: '.$provider.' '.$tickerCode.' '.$tradeDate.'.');
        }

        return $this->identityRow($rows->first()) + [
            'provider_mapping_id' => (int) $rows->first()->provider_mapping_id,
            'provider' => (string) $rows->first()->provider,
            'provider_symbol' => (string) $rows->first()->provider_symbol,
            'mapping_revision' => (string) $rows->first()->mapping_revision,
            'provider_mapping_recorded_at' => (string) $rows->first()->provider_mapping_recorded_at,
        ];
    }

    public function resolveByTickerCodes(array $tickerCodes, $tradeDate, $knownAt = null)
    {
        $contexts = [];
        foreach ($tickerCodes as $tickerCode) {
            $normalized = Str::upper(trim((string) $tickerCode));
            if ($normalized === '') {
                continue;
            }

            try {
                $contexts[$normalized] = $this->resolveProviderContext(
                    $normalized,
                    config('market_data.source.api.provider', 'yahoo_finance'),
                    $tradeDate,
                    $knownAt
                );
            } catch (\RuntimeException $e) {
                if (strpos($e->getMessage(), 'PROVIDER_SYMBOL_MAPPING_') === 0) {
                    continue;
                }

                throw $e;
            }
        }

        return $contexts;
    }

    private function baseIdentityQuery($tradeDate, $knownAt)
    {
        $query = DB::table('md_listings as l')
            ->join('md_instruments as i', 'i.instrument_id', '=', 'l.instrument_id')
            ->join('md_issuers as iss', 'iss.issuer_id', '=', 'i.issuer_id')
            ->join('md_listing_symbols as ls', function ($join) use ($tradeDate) {
                $join->on('ls.listing_id', '=', 'l.listing_id')
                    ->where('ls.symbol_type', '=', 'EXCHANGE')
                    ->where('ls.effective_from', '<=', $tradeDate.' 23:59:59')
                    ->where(function ($q) use ($tradeDate) {
                        $q->whereNull('ls.effective_to')->orWhere('ls.effective_to', '>', $tradeDate.' 00:00:00');
                    })
                    ->whereNull('ls.retracted_at');
            })
            ->where('l.exchange_code', config('market_data.scope.market_code', 'IDX'))
            ->where('l.market_segment', config('market_data.scope.market_segment', 'REGULAR'))
            ->where('l.listed_date', '<=', $tradeDate)
            ->where(function ($q) use ($tradeDate) {
                $q->whereNull('l.delisted_date')->orWhere('l.delisted_date', '>', $tradeDate);
            })
            ->select([
                'l.listing_id', 'l.legacy_ticker_id as ticker_id', 'l.exchange_code', 'l.market_segment',
                'l.board_code', 'l.listed_date', 'l.delisted_date', 'l.recorded_at as listing_recorded_at',
                'i.instrument_id', 'i.instrument_uid', 'iss.issuer_id', 'iss.issuer_uid',
                'ls.listing_symbol_id', 'ls.symbol as ticker_code', 'ls.recorded_at as symbol_recorded_at',
            ]);

        if ($knownAt !== null) {
            $query->where('l.recorded_at', '<=', $knownAt)
                ->where('i.recorded_at', '<=', $knownAt)
                ->where('iss.recorded_at', '<=', $knownAt)
                ->where('ls.recorded_at', '<=', $knownAt);
        }

        return $query;
    }

    private function projectTicker($ticker)
    {
        $tickerId = (int) $ticker->{config('market_data.tickers.id_column', 'ticker_id')};
        $tickerCode = Str::upper(trim((string) $ticker->{config('market_data.tickers.code_column', 'ticker_code')}));
        $scope = MarketDataScope::fromConfig();
        $listedDateColumn = config('market_data.tickers.listed_date_column', 'listed_date');
        $delistedDateColumn = config('market_data.tickers.delisted_date_column', 'delisted_date');
        $listedDate = isset($ticker->{$listedDateColumn}) && $ticker->{$listedDateColumn}
            ? (string) $ticker->{$listedDateColumn}
            : $scope->datasetStart();
        $delistedDate = isset($ticker->{$delistedDateColumn}) && $ticker->{$delistedDateColumn}
            ? (string) $ticker->{$delistedDateColumn}
            : null;
        $recordedAt = isset($ticker->created_at) && $ticker->created_at
            ? (string) $ticker->created_at
            : Carbon::now($scope->timezone())->toDateTimeString();
        $sourceRef = 'legacy_tickers:ticker_id='.$tickerId.';projection='.(string) config('market_data.tickers.temporal_projection_version');

        DB::transaction(function () use ($ticker, $tickerId, $tickerCode, $listedDate, $delistedDate, $recordedAt, $sourceRef) {
            $issuerUid = hash('sha256', 'issuer|legacy-ticker|'.$tickerId);
            $issuer = DB::table('md_issuers')->where('issuer_uid', $issuerUid)->first();
            $issuerId = $issuer ? (int) $issuer->issuer_id : (int) DB::table('md_issuers')->insertGetId([
                'issuer_uid' => $issuerUid,
                'legal_name' => (string) ($ticker->company_name ?? $tickerCode),
                'source_ref' => $sourceRef,
                'recorded_at' => $recordedAt,
                'created_at' => $recordedAt,
            ]);

            $instrumentUid = hash('sha256', 'instrument|idx-equity|'.$tickerId);
            $instrument = DB::table('md_instruments')->where('instrument_uid', $instrumentUid)->first();
            $instrumentId = $instrument ? (int) $instrument->instrument_id : (int) DB::table('md_instruments')->insertGetId([
                'instrument_uid' => $instrumentUid,
                'issuer_id' => $issuerId,
                'instrument_type' => 'EQUITY',
                'currency_code' => 'IDR',
                'source_ref' => $sourceRef,
                'recorded_at' => $recordedAt,
                'created_at' => $recordedAt,
            ]);

            $listing = DB::table('md_listings')->where('legacy_ticker_id', $tickerId)->first();
            $listingId = $listing ? (int) $listing->listing_id : (int) DB::table('md_listings')->insertGetId([
                'listing_uid' => hash('sha256', 'listing|idx|regular|'.$tickerId),
                'legacy_ticker_id' => $tickerId,
                'instrument_id' => $instrumentId,
                'exchange_code' => strtoupper((string) ($ticker->exchange_code ?? 'IDX')) ?: 'IDX',
                'market_segment' => 'REGULAR',
                'board_code' => $ticker->board_code ?? null,
                'listed_date' => $listedDate,
                'delisted_date' => $delistedDate,
                'source_ref' => $sourceRef,
                'listing_state' => $delistedDate ? 'DELISTED' : 'LISTED',
                'recorded_at' => $recordedAt,
                'created_at' => $recordedAt,
            ]);

            $effectiveFrom = $listedDate.' 00:00:00';
            $effectiveTo = $delistedDate ? $delistedDate.' 00:00:00' : null;
            if (! DB::table('md_listing_symbols')->where('listing_id', $listingId)->where('symbol', $tickerCode)->where('effective_from', $effectiveFrom)->exists()) {
                DB::table('md_listing_symbols')->insert([
                    'listing_id' => $listingId,
                    'symbol' => $tickerCode,
                    'symbol_type' => 'EXCHANGE',
                    'symbol_namespace' => 'IDX',
                    'effective_from' => $effectiveFrom,
                    'effective_to' => $effectiveTo,
                    'recorded_at' => $recordedAt,
                    'retracted_at' => null,
                    'source_observation_id' => null,
                    'source_ref' => $sourceRef,
                    'change_reason' => 'LEGACY_MASTER_PROJECTION',
                ]);
            }

            $provider = strtolower((string) config('market_data.source.api.provider', 'yahoo_finance'));
            $suffix = (string) config('market_data.source.api.yahoo.symbol_suffix', '.JK');
            $providerSymbol = substr($tickerCode, -strlen($suffix)) === $suffix ? $tickerCode : $tickerCode.$suffix;
            if (! DB::table('md_provider_symbol_mappings')->where('listing_id', $listingId)->where('provider', $provider)->where('effective_from', $effectiveFrom)->exists()) {
                DB::table('md_provider_symbol_mappings')->insert([
                    'listing_id' => $listingId,
                    'provider' => $provider,
                    'provider_symbol' => $providerSymbol,
                    'effective_from' => $effectiveFrom,
                    'effective_to' => $effectiveTo,
                    'recorded_at' => $recordedAt,
                    'retracted_at' => null,
                    'source_observation_id' => null,
                    'mapping_revision' => (string) config('market_data.source.mapping_revision', 'temporal_provider_mapping_v1'),
                    'source_ref' => $sourceRef,
                    'change_reason' => 'BOOTSTRAP_PROVIDER_MAPPING',
                ]);
            }
        });
    }

    private function identityRow($row)
    {
        return [
            'issuer_id' => (int) $row->issuer_id,
            'issuer_uid' => (string) $row->issuer_uid,
            'instrument_id' => (int) $row->instrument_id,
            'instrument_uid' => (string) $row->instrument_uid,
            'listing_id' => (int) $row->listing_id,
            'ticker_id' => (int) $row->ticker_id,
            'ticker_code' => Str::upper(trim((string) $row->ticker_code)),
            'exchange_code' => (string) $row->exchange_code,
            'market_segment' => (string) $row->market_segment,
            'board_code' => $row->board_code,
            'listed_date' => (string) $row->listed_date,
            'delisted_date' => $row->delisted_date ? (string) $row->delisted_date : null,
            'listing_symbol_id' => (int) $row->listing_symbol_id,
            'identity_recorded_at' => (string) $row->listing_recorded_at,
        ];
    }

    private function assertFoundationAvailable()
    {
        foreach (['md_issuers', 'md_instruments', 'md_listings', 'md_listing_symbols', 'md_provider_symbol_mappings'] as $table) {
            if (! Schema::hasTable($table)) {
                throw new \RuntimeException('TEMPORAL_IDENTITY_FOUNDATION_MISSING: '.$table.'.');
            }
        }
    }
}

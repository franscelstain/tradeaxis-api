<?php

namespace App\Infrastructure\Persistence\MarketData;

use Illuminate\Support\Facades\DB;

/** The producer resolves from these materialized rows; capture does not re-query the answer. */
final class ProducerTemporalPopulation
{
    public const TABLES = [
        'md_issuers' => 'issuer_id', 'md_instruments' => 'instrument_id',
        'md_listings' => 'listing_id', 'md_listing_symbols' => 'listing_symbol_id',
        'md_listing_boards' => 'listing_board_id', 'md_provider_symbol_mappings' => 'provider_mapping_id',
    ];

    public function resolve(string $date, string $knownAt, ?string $ticker = null, ?string $provider = null): array
    {
        $cutoff = $knownAt;
        $component = $provider === null ? 'universe_identity' : 'provider_mapping';
        $selection = ['operation' => $provider === null ? 'temporal-identity-revisions/v1' : 'provider-mapping-revisions/v1',
            'trade_date' => $date, 'known_at' => $cutoff, 'ticker_code' => $ticker, 'provider' => $provider,
            'dataset_start' => (string) config('market_data.scope.dataset_start'),
            'exchange_code' => (string) config('market_data.scope.market_code', 'IDX'),
            'market_segment' => (string) config('market_data.scope.market_segment', 'REGULAR')];
        $captured = ProducerInputScope::inputRead($component, $selection, function () use ($date, $cutoff, $ticker, $provider, $selection) {
            $materialized = ProducerInputScope::materialize('temporal-population|'.$cutoff, function () use ($cutoff) {
                return DB::transaction(function () use ($cutoff) {
                    $tables = [];
                    foreach (self::TABLES as $table => $id) {
                        // Unknown future revisions never become historical input. Ineligible effective
                        // intervals remain in the known population and carry explicit omission reasons.
                        $tables[$table] = array_map(static function ($r) { return (array) $r; },
                            DB::table($table)->where('recorded_at', '<=', $cutoff)->orderBy($id)->get()->all());
                    }
                    $selection = ['operation' => 'temporal-revision-population/v1', 'known_at' => $cutoff];
                    ProducerInputScope::inputRead('universe_identity', $selection, static function () use ($tables) {
                        return [['population_schema' => 'md_temporal_revision_population_v1', 'tables' => $tables,
                            'population_counts' => array_map('count', $tables)]];
                    });
                    return ['tables' => $tables, 'reference' => ProducerInputScope::inputReference('universe_identity', $selection)];
                });
            });
            $tables = $materialized['tables'];
            $issuers = array_column($tables['md_issuers'], null, 'issuer_id');
            $instruments = array_column($tables['md_instruments'], null, 'instrument_id');
            $selected = []; $omitted = [];
            foreach ($tables['md_listings'] as $listing) {
                $id = (int) $listing['listing_id']; $reasons = [];
                $instrument = $instruments[$listing['instrument_id']] ?? null;
                $issuer = $instrument ? ($issuers[$instrument['issuer_id']] ?? null) : null;
                if (! $instrument) $reasons[] = 'INSTRUMENT_NOT_KNOWN';
                if (! $issuer) $reasons[] = 'ISSUER_NOT_KNOWN';
                if ($listing['exchange_code'] !== $selection['exchange_code']) $reasons[] = 'EXCHANGE_OUT_OF_SCOPE';
                if ($listing['listed_date'] > $date) $reasons[] = 'NOT_YET_LISTED';
                if ($listing['delisted_date'] !== null && $listing['delisted_date'] <= $date
                    && $listing['delisted_recorded_at'] !== null && $listing['delisted_recorded_at'] <= $cutoff) $reasons[] = 'KNOWN_DELISTING';
                $symbols = $this->intervalRows($tables['md_listing_symbols'], $id, $date, $cutoff,
                    static function ($r) { return $r['symbol_type'] === 'EXCHANGE'; });
                $boards = $this->intervalRows($tables['md_listing_boards'], $id, $date, $cutoff,
                    static function ($r) use ($selection) { return $r['market_segment'] === $selection['market_segment']; });
                if ($symbols === []) $reasons[] = 'NO_EFFECTIVE_KNOWN_EXCHANGE_SYMBOL';
                if ($boards === []) $reasons[] = 'NO_EFFECTIVE_KNOWN_BOARD';
                if ($ticker !== null) {
                    $symbols = array_values(array_filter($symbols, static function ($r) use ($ticker) { return strtoupper(trim($r['symbol'])) === $ticker; }));
                    if ($symbols === []) $reasons[] = 'REQUESTED_SYMBOL_NOT_SELECTED';
                }
                $mappings = $provider === null ? [null] : $this->intervalRows($tables['md_provider_symbol_mappings'], $id, $date, $cutoff,
                    static function ($r) use ($provider) { return $r['provider'] === $provider; });
                if ($mappings === []) $reasons[] = 'NO_EFFECTIVE_KNOWN_PROVIDER_MAPPING';
                if ($reasons !== []) { $omitted[] = ['listing_id' => $id, 'reasons' => $reasons]; continue; }
                foreach ($symbols as $symbol) foreach ($boards as $board) foreach ($mappings as $mapping) {
                    $row = ['listing_id' => $id, 'ticker_id' => $listing['legacy_ticker_id'],
                        'exchange_code' => $listing['exchange_code'], 'market_segment' => $board['market_segment'],
                        'board_code' => $board['board_code'], 'listing_board_id' => $board['listing_board_id'],
                        'board_recorded_at' => $board['recorded_at'], 'listed_date' => $listing['listed_date'],
                        'delisted_date' => $listing['delisted_date'], 'listing_recorded_at' => $listing['recorded_at'],
                        'instrument_id' => $instrument['instrument_id'], 'instrument_uid' => $instrument['instrument_uid'],
                        'issuer_id' => $issuer['issuer_id'], 'issuer_uid' => $issuer['issuer_uid'],
                        'listing_symbol_id' => $symbol['listing_symbol_id'], 'ticker_code' => $symbol['symbol'],
                        'symbol_recorded_at' => $symbol['recorded_at']];
                    if ($mapping !== null) $row += ['provider_mapping_id' => $mapping['provider_mapping_id'],
                        'provider' => $mapping['provider'], 'provider_symbol' => $mapping['provider_symbol'],
                        'mapping_revision' => $mapping['mapping_revision'], 'provider_mapping_recorded_at' => $mapping['recorded_at']];
                    $selected[] = $row;
                }
            }
            if ($provider !== null) usort($selected, static function ($a, $b) {
                return strcmp($b['provider_mapping_recorded_at'], $a['provider_mapping_recorded_at'])
                    ?: ($a['provider_mapping_id'] <=> $b['provider_mapping_id']);
            });
            return [['population_schema' => 'md_temporal_producer_population_v1', 'population_ref' => $materialized['reference'],
                'population_counts' => array_map('count', $tables), 'selected_rows' => $selected, 'omitted_listings' => $omitted,
                'selection_basis' => ['knowledge' => 'recorded_at <= known_at; future source rows are not read',
                    'effective' => 'effective_from <= T 23:59:59 AND (effective_to IS NULL OR effective_to > T 00:00:00)',
                    'retraction' => 'retracted_at IS NULL OR retracted_at > known_at',
                    'revision_relationship' => 'Source primary IDs and foreign keys plus effective/recorded/retracted intervals; no inferred supersedes edges',
                    'source_primary_keys' => self::TABLES,
                    'empty_result' => $selected === [] ? 'NO_ELIGIBLE_MEMBER_IN_KNOWN_POPULATION' : null]]];
        });
        return array_map(static function ($r) { return (object) $r; }, $captured[0]['selected_rows']);
    }

    private function intervalRows(array $population, int $listingId, string $date, string $cutoff, callable $domain): array
    {
        return array_values(array_filter($population, static function ($r) use ($listingId, $date, $cutoff, $domain) {
            return (int) $r['listing_id'] === $listingId && $domain($r)
                && $r['effective_from'] <= $date.' 23:59:59'
                && ($r['effective_to'] === null || $r['effective_to'] > $date.' 00:00:00')
                && ($r['retracted_at'] === null || $r['retracted_at'] > $cutoff);
        }));
    }
}

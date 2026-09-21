<?php

namespace App\Infrastructure\Persistence\MarketData;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** C05 captures the inputs used by resolution, including rejected and superseded revisions. */
final class ProducerTradingStatusPopulation
{
    public const TABLE_KEYS = [
        'md_trading_status_revisions' => ['status_revision_id'],
        'md_listings' => ['listing_id'], 'md_listing_boards' => ['listing_board_id'],
        'md_trading_status_source_registry' => ['source_name', 'status_type_code'],
        'market_data_trading_status_event_types' => ['event_type_code'],
        'md_source_observations' => ['source_observation_id'],
    ];

    public function resolve(int $listingId, string $tradeDate, string $knownAt): array
    {
        $selection = ['operation' => 'status-authority-revisions/v1', 'listing_id' => $listingId,
            'trade_date' => $tradeDate, 'known_at' => $knownAt];
        $captured = ProducerInputScope::inputRead('status_expectation', $selection, function () use ($listingId, $tradeDate, $knownAt) {
            // Mutable registries have no historical revision coordinate. Track this failure in
            // the capture scope too, so a consumer cannot swallow it and commit dependent output.
            if (ProducerInputScope::historical()) throw new \RuntimeException('INPUT_CAPTURE_HISTORICAL_STATUS_POPULATION_UNBOUND');
            $materialized = ProducerInputScope::materialize('status-population|'.$knownAt, function () use ($knownAt) {
                return DB::transaction(function () use ($knownAt) {
                    $tables = []; $available = [];
                    foreach (self::TABLE_KEYS as $table => $keys) {
                        $available[$table] = Schema::hasTable($table);
                        if (! $available[$table]) { $tables[$table] = []; continue; }
                        $query = DB::table($table);
                        if (in_array($table, ['md_trading_status_revisions', 'md_listings', 'md_listing_boards'], true)) $query->where('recorded_at', '<=', $knownAt);
                        if ($table === 'md_source_observations') $query->whereIn('source_observation_id', array_values(array_unique(array_filter(array_column($tables['md_trading_status_revisions'], 'source_observation_id')))));
                        foreach ($keys as $key) $query->orderBy($key);
                        $tables[$table] = array_map(static function ($r) { return (array) $r; }, $query->get()->all());
                    }
                    $population = ['population_schema' => 'md_trading_status_population_v1', 'tables' => $tables,
                        'available_tables' => $available, 'population_counts' => array_map('count', $tables),
                        'source_primary_keys' => self::TABLE_KEYS,
                        'domain_context' => ['market_code' => (string) config('market_data.scope.market_code', 'IDX'),
                            'market_segment' => (string) config('market_data.scope.market_segment', 'REGULAR')]];
                    $selection = ['operation' => 'status-revision-population/v1', 'known_at' => $knownAt];
                    ProducerInputScope::inputRead('status_expectation', $selection, static function () use ($population) { return [$population]; });
                    return ['population' => $population, 'reference' => ProducerInputScope::inputReference('status_expectation', $selection)];
                });
            });
            $evaluation = TemporalTradingStatusRepository::evaluateCaptured($materialized['population'], $listingId, $tradeDate, $knownAt);
            return [['population_schema' => 'md_trading_status_selection_v1', 'population_ref' => $materialized['reference'],
                'population_counts' => $materialized['population']['population_counts']] + $evaluation];
        });
        return $captured[0]['selection_result'];
    }
}

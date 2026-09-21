<?php

namespace App\Infrastructure\Persistence\MarketData;

/** Validate retained C05 content using captured inputs only; a slot count is never sufficient. */
final class ProducerTradingStatusCaptureValidator
{
    public function missing(array $population, array $capture, array $selection, string $prefix): array
    {
        $missing = [];
        $required = [
            'md_trading_status_revisions' => ['status_revision_id', 'listing_id', 'instrument_id', 'status_event_uid',
                'status_type_code', 'status_code', 'bar_expectation_state', 'board_code', 'authority_class', 'source_name',
                'source_payload_hash', 'source_ref', 'source_observation_id', 'observed_at', 'announced_at',
                'effective_from', 'effective_to', 'recorded_at', 'retracted_at', 'supersedes_revision_id',
                'verification_state', 'full_session_verified', 'operator_name', 'governed_reason_code', 'authoritative_source_ref'],
            'md_listings' => ['listing_id', 'instrument_id', 'exchange_code', 'listed_date', 'delisted_date', 'recorded_at'],
            'md_listing_boards' => ['listing_board_id', 'listing_id', 'market_segment', 'board_code', 'effective_from', 'effective_to', 'recorded_at', 'retracted_at'],
            'md_trading_status_source_registry' => ['source_name', 'status_type_code', 'authority_class', 'priority', 'active', 'source_ref_pattern', 'created_at', 'updated_at'],
            'market_data_trading_status_event_types' => ['event_type_code', 'carries_forward'],
            'md_source_observations' => ['source_observation_id', 'observation_uid', 'payload_hash', 'outcome_state', 'source_name', 'provider', 'acquired_at'],
        ];
        if (($population['population_schema'] ?? null) !== 'md_trading_status_population_v1'
            || ($capture['population_schema'] ?? null) !== 'md_trading_status_selection_v1') $missing[] = $prefix.'.population_schema';
        foreach (['listing_id', 'trade_date', 'known_at'] as $field) if (empty($selection[$field])) $missing[] = $prefix.'.selection_context.'.$field;
        foreach (['market_code', 'market_segment'] as $field) if (empty($population['domain_context'][$field])) $missing[] = $prefix.'.domain_context.'.$field;
        foreach ($required as $table => $fields) {
            $rows = $population['tables'][$table] ?? null;
            if (! is_array($rows)) { $missing[] = $prefix.'.'.$table.'.population'; continue; }
            if (($population['available_tables'][$table] ?? null) !== true) $missing[] = $prefix.'.'.$table.'.unavailable';
            if (($population['population_counts'][$table] ?? null) !== count($rows)
                || ($capture['population_counts'][$table] ?? null) !== count($rows)) $missing[] = $prefix.'.'.$table.'.population_count';
            if (($population['source_primary_keys'][$table] ?? null) !== ProducerTradingStatusPopulation::TABLE_KEYS[$table]) $missing[] = $prefix.'.'.$table.'.revision_identity';
            $seen = [];
            foreach ($rows as $index => $row) {
                foreach ($fields as $field) if (! array_key_exists($field, $row)) $missing[] = $prefix.'.'.$table.'.'.$index.'.'.$field;
                $key = [];
                foreach (ProducerTradingStatusPopulation::TABLE_KEYS[$table] as $field) $key[] = $row[$field] ?? null;
                $key = RunInputCaptureRepository::canonicalJson($key);
                if (isset($seen[$key])) $missing[] = $prefix.'.'.$table.'.duplicate_identity';
                $seen[$key] = true;
                if (in_array($table, ['md_trading_status_revisions', 'md_listings', 'md_listing_boards'], true)
                    && isset($row['recorded_at']) && $row['recorded_at'] > ($selection['known_at'] ?? '')) $missing[] = $prefix.'.'.$table.'.'.$index.'.future_revision';
            }
        }
        if ($missing !== []) return $missing;
        $expected = TemporalTradingStatusRepository::evaluateCaptured($population, (int) $selection['listing_id'], $selection['trade_date'], $selection['known_at']);
        foreach ($expected as $field => $value) {
            if (! array_key_exists($field, $capture) || RunInputCaptureRepository::canonicalJson($capture[$field]) !== RunInputCaptureRepository::canonicalJson($value)) $missing[] = $prefix.'.'.$field;
        }
        return $missing;
    }
}

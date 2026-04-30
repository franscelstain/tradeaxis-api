<?php

use Illuminate\Support\Facades\Schema;
use Tests\Support\UsesMarketDataSqlite;

class MarketDataSqliteSchemaSyncTest extends TestCase
{
    use UsesMarketDataSqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
    }

    public function test_sqlite_schema_contains_ticker_calendar_session_and_replay_contract_columns(): void
    {
        foreach ([
            'tickers' => [
                'ticker_id',
                'ticker_code',
                'company_name',
                'company_logo',
                'listed_date',
                'delisted_date',
                'board_code',
                'exchange_code',
                'is_active',
                'created_at',
                'updated_at',
            ],
            'market_calendar' => [
                'cal_date',
                'is_trading_day',
                'holiday_name',
                'session_open_time',
                'session_close_time',
                'breaks_json',
                'source',
                'created_at',
                'updated_at',
            ],
            'eod_reason_codes' => [
                'code',
                'category',
                'description',
                'severity',
                'is_active',
                'created_at',
                'updated_at',
            ],
            'md_session_snapshots' => [
                'snapshot_id',
                'trade_date',
                'snapshot_slot',
                'ticker_id',
                'captured_at',
                'last_price',
                'prev_close',
                'chg_pct',
                'volume',
                'day_high',
                'day_low',
                'source',
                'run_id',
                'reason_code',
                'error_note',
                'created_at',
                'updated_at',
            ],
            'md_replay_daily_metrics' => [
                'expected_config_identity',
                'expected_publication_version',
                'expected_coverage_universe_count',
                'expected_coverage_available_count',
                'expected_coverage_missing_count',
                'expected_coverage_ratio',
                'expected_coverage_min_threshold',
                'expected_coverage_gate_state',
                'expected_coverage_threshold_mode',
                'expected_coverage_universe_basis',
                'expected_coverage_contract_version',
                'expected_coverage_missing_sample_json',
                'expected_bars_batch_hash',
                'expected_indicators_batch_hash',
                'expected_eligibility_batch_hash',
                'expected_reason_code_counts_json',
            ],
        ] as $table => $columns) {
            foreach ($columns as $column) {
                $this->assertTrue(
                    Schema::hasColumn($table, $column),
                    sprintf('Missing SQLite mirror column %s.%s', $table, $column)
                );
            }
        }
    }

    public function test_sqlite_schema_does_not_contain_runtime_orphan_surrogate_keys_on_publication_bound_artifacts(): void
    {
        foreach ([
            'eod_bars' => ['bar_id'],
            'eod_indicators' => ['indicator_id'],
            'eod_eligibility' => ['eligibility_id'],
            'eod_bars_history' => ['history_id'],
            'eod_indicators_history' => ['history_id'],
            'eod_eligibility_history' => ['history_id'],
        ] as $table => $columns) {
            foreach ($columns as $column) {
                $this->assertFalse(
                    Schema::hasColumn($table, $column),
                    sprintf('SQLite mirror must not contain runtime-orphan column %s.%s', $table, $column)
                );
            }
        }
    }

    public function test_replay_metrics_does_not_contain_sqlite_only_source_file_columns(): void
    {
        foreach ([
            'source_file_hash',
            'source_file_hash_algorithm',
            'source_file_size_bytes',
            'source_file_row_count',
        ] as $column) {
            $this->assertFalse(
                Schema::hasColumn('md_replay_daily_metrics', $column),
                sprintf('SQLite-only replay metric column must not exist: md_replay_daily_metrics.%s', $column)
            );
        }
    }
}

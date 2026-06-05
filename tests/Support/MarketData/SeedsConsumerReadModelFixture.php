<?php

namespace Tests\Support\MarketData;

use Illuminate\Support\Facades\DB;

trait SeedsConsumerReadModelFixture
{
    protected function configureConsumerReadModelFixture(): void
    {
        config()->set('market_data.tickers.table', 'tickers');
        config()->set('market_data.tickers.id_column', 'ticker_id');
        config()->set('market_data.tickers.code_column', 'ticker_code');
        config()->set('market_data.indicators.set_version', 'v1');
    }

    protected function seedTicker(int $tickerId, string $tickerCode, string $tickerName = null): void
    {
        DB::table('tickers')->insert([
            'ticker_id' => $tickerId,
            'ticker_code' => strtoupper($tickerCode),
            'company_name' => $tickerName ?: strtoupper($tickerCode).' Tbk',
            'is_active' => 1,
        ]);
    }

    protected function seedReadablePublication(
        string $tradeDate,
        int $runId,
        int $publicationId,
        int $publicationVersion = 1,
        array $overrides = []
    ): void {
        $sealedAt = $overrides['sealed_at'] ?? $tradeDate.' 17:20:00';
        $terminalStatus = $overrides['terminal_status'] ?? 'SUCCESS';
        $publishabilityState = $overrides['publishability_state'] ?? 'READABLE';
        $coverageGateState = $overrides['coverage_gate_state'] ?? 'PASS';
        $isCurrent = $overrides['is_current'] ?? 1;
        $isCurrentRun = $overrides['is_current_publication'] ?? 1;
        $sealState = $overrides['seal_state'] ?? 'SEALED';
        $runPublicationId = $overrides['run_publication_id'] ?? $publicationId;
        $runPublicationVersion = $overrides['run_publication_version'] ?? $publicationVersion;

        DB::table('eod_runs')->insert([
            'run_id' => $runId,
            'trade_date_requested' => $tradeDate,
            'trade_date_effective' => $tradeDate,
            'lifecycle_state' => 'COMPLETED',
            'quality_gate_state' => 'PASS',
            'stage' => 'FINALIZE',
            'source' => 'api',
            'source_name' => 'API_FREE',
            'source_provider' => 'yahoo_finance',
            'publication_id' => $runPublicationId,
            'publication_version' => $runPublicationVersion,
            'terminal_status' => $terminalStatus,
            'publishability_state' => $publishabilityState,
            'coverage_gate_state' => $coverageGateState,
            'coverage_universe_count' => 2,
            'coverage_available_count' => 2,
            'coverage_missing_count' => 0,
            'coverage_ratio' => '1.000000',
            'coverage_min_threshold' => '0.980000',
            'coverage_threshold_mode' => 'MIN_RATIO',
            'coverage_universe_basis' => 'ACTIVE_LISTED_EQUITY_AS_OF_DATE',
            'coverage_contract_version' => 'coverage_gate_v1',
            'is_current_publication' => $isCurrentRun,
            'sealed_at' => $sealedAt,
            'started_at' => $tradeDate.' 17:00:00',
            'created_at' => $tradeDate.' 17:00:00',
            'updated_at' => $sealedAt,
        ]);

        DB::table('eod_publications')->insert([
            'publication_id' => $publicationId,
            'trade_date' => $tradeDate,
            'run_id' => $runId,
            'publication_version' => $publicationVersion,
            'is_current' => $isCurrent,
            'seal_state' => $sealState,
            'sealed_at' => $sealedAt,
            'created_at' => $sealedAt,
            'updated_at' => $sealedAt,
        ]);

        if (! ($overrides['skip_pointer'] ?? false)) {
            DB::table('eod_current_publication_pointer')->insert([
                'trade_date' => $tradeDate,
                'publication_id' => $overrides['pointer_publication_id'] ?? $publicationId,
                'run_id' => $overrides['pointer_run_id'] ?? $runId,
                'publication_version' => $overrides['pointer_publication_version'] ?? $publicationVersion,
                'sealed_at' => $overrides['pointer_sealed_at'] ?? $sealedAt,
                'updated_at' => $sealedAt,
            ]);
        }
    }

    protected function seedBar(
        string $tradeDate,
        int $tickerId,
        int $runId,
        int $publicationId,
        float $close,
        int $volume = 100000,
        ?float $adjustedClose = null
    ): void {
        DB::table('eod_bars')->insert([
            'trade_date' => $tradeDate,
            'ticker_id' => $tickerId,
            'open' => $close - 10,
            'high' => $close + 25,
            'low' => $close - 25,
            'close' => $close,
            'volume' => $volume,
            'adj_close' => $adjustedClose,
            'source' => 'api',
            'run_id' => $runId,
            'publication_id' => $publicationId,
            'created_at' => $tradeDate.' 17:20:00',
        ]);
    }

    protected function seedIndicator(
        string $tradeDate,
        int $tickerId,
        int $runId,
        int $publicationId,
        array $overrides = []
    ): void {
        DB::table('eod_indicators')->insert(array_merge([
            'trade_date' => $tradeDate,
            'ticker_id' => $tickerId,
            'is_valid' => 1,
            'invalid_reason_code' => null,
            'indicator_set_version' => 'v1',
            'sector_code' => 'G',
            'dv20_idr' => '123456789000.00',
            'atr14_pct' => '2.1000000000',
            'vol_ratio' => '1.5000000000',
            'roc5' => '0.0180000000',
            'roc10' => '0.0310000000',
            'roc20' => '5.2000000000',
            'hh20' => '9100.0000',
            'll20' => '8200.0000',
            'ma20' => '8750.0000',
            'ma50' => '8600.0000',
            'close_to_hh20_pct' => '-1.1000000000',
            'close_to_ll20_pct' => '9.7560975610',
            'range_20_pct' => '10.9756097561',
            'range_position_20_pct' => '88.8888888889',
            'close_vs_ma20_pct' => '2.8000000000',
            'close_vs_ma50_pct' => '4.6000000000',
            'ma20_slope_pct' => '1.2000000000',
            'rs_20_vs_ihsg' => '3.4000000000',
            'sector_roc20' => '2.7000000000',
            'rs_20_vs_sector' => '2.5000000000',
            'sector_rs_20_vs_ihsg' => '-0.9000000000',
            'run_id' => $runId,
            'publication_id' => $publicationId,
            'created_at' => $tradeDate.' 17:20:00',
        ], $overrides));
    }

    protected function seedEligibility(
        string $tradeDate,
        int $tickerId,
        int $runId,
        int $publicationId,
        int $eligible = 1,
        ?string $reasonCode = null
    ): void {
        DB::table('eod_eligibility')->insert([
            'trade_date' => $tradeDate,
            'ticker_id' => $tickerId,
            'eligible' => $eligible,
            'reason_code' => $reasonCode,
            'run_id' => $runId,
            'publication_id' => $publicationId,
            'created_at' => $tradeDate.' 17:20:00',
        ]);
    }

    protected function seedTradingDay(string $tradeDate): void
    {
        DB::table('market_calendar')->insert([
            'cal_date' => $tradeDate,
            'is_trading_day' => 1,
            'created_at' => $tradeDate.' 00:00:00',
        ]);
    }

    protected function seedBenchmark(string $tradeDate, array $indicatorOverrides = []): void
    {
        DB::table('market_benchmarks')->insert([
            'benchmark_code' => 'IHSG',
            'benchmark_name' => 'Jakarta Composite Index',
            'provider' => 'yahoo_finance',
            'provider_symbol' => '^JKSE',
            'instrument_type' => 'INDEX',
            'is_active' => 1,
            'created_at' => $tradeDate.' 17:20:00',
            'updated_at' => $tradeDate.' 17:20:00',
        ]);

        DB::table('market_benchmark_bars')->insert([
            'benchmark_code' => 'IHSG',
            'trade_date' => $tradeDate,
            'open_price' => '7200.0000',
            'high_price' => '7300.0000',
            'low_price' => '7100.0000',
            'close_price' => '7250.0000',
            'adjusted_close' => '7250.0000',
            'volume' => null,
            'provider' => 'yahoo_finance',
            'provider_symbol' => '^JKSE',
            'created_at' => $tradeDate.' 17:20:00',
            'updated_at' => $tradeDate.' 17:20:00',
        ]);

        DB::table('market_benchmark_indicators')->insert(array_merge([
            'benchmark_code' => 'IHSG',
            'trade_date' => $tradeDate,
            'roc_20' => null,
            'ma20' => null,
            'ma50' => null,
            'ma20_slope_pct' => null,
            'close_to_ma20_pct' => null,
            'close_to_ma50_pct' => null,
            'is_valid' => 0,
            'invalid_reason_code' => 'IND_INSUFFICIENT_HISTORY',
            'indicator_set_version' => 'v1',
            'created_at' => $tradeDate.' 17:20:00',
            'updated_at' => $tradeDate.' 17:20:00',
        ], $indicatorOverrides));
    }
}

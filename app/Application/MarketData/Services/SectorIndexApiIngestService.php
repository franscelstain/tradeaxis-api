<?php

namespace App\Application\MarketData\Services;

use App\Infrastructure\MarketData\Source\PublicApiEodBarsAdapter;
use App\Infrastructure\MarketData\Source\SourceAcquisitionException;
use App\Infrastructure\Persistence\MarketData\MarketBenchmarkRepository;
use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SectorIndexApiIngestService
{
    private $apiSourceAdapter;
    private $benchmarks;
    private $calendar;

    public function __construct(
        PublicApiEodBarsAdapter $apiSourceAdapter,
        MarketBenchmarkRepository $benchmarks,
        MarketCalendarRepository $calendar
    ) {
        $this->apiSourceAdapter = $apiSourceAdapter;
        $this->benchmarks = $benchmarks;
        $this->calendar = $calendar;
    }

    public function execute($startDate, $endDate, array $options = [])
    {
        $provider = strtolower(trim((string) ($options['provider'] ?? config('market_data.sectors.index_api.provider', 'yahoo_finance'))));
        $apply = ! empty($options['apply']);
        $continueOnError = ! empty($options['continue_on_error']);
        $allowPartial = ! empty($options['allow_partial']);
        $symbolSuffix = array_key_exists('symbol_suffix', $options) && $options['symbol_suffix'] !== null
            ? (string) $options['symbol_suffix']
            : (string) config('market_data.sectors.index_api.symbol_suffix', '.JK');
        $symbolMap = $this->providerSymbols($options['symbol_map'] ?? null);

        if ($provider !== 'yahoo_finance') {
            throw new \RuntimeException('SECTOR_INDEX_API_PROVIDER_UNSUPPORTED: sector index API currently supports yahoo_finance only.');
        }

        $previousApiProvider = config('market_data.source.api.provider');
        $previousApiSourceName = config('market_data.source.api.source_name');
        config([
            'market_data.source.api.provider' => $provider,
            'market_data.source.api.source_name' => strtoupper($provider),
        ]);

        try {
            $dates = $this->tradingDates($startDate, $endDate);
            $benchmarks = $this->sectorBenchmarks($provider, $symbolMap, $symbolSuffix);

            $summary = [
                'suite' => 'market_data_sector_index_api_ingest',
                'provider' => $provider,
                'source_acquisition_mode' => 'sector_index_api',
                'start_date' => (string) $startDate,
                'end_date' => (string) $endDate,
                'trading_date_count' => count($dates),
                'requested_benchmark_count' => count($benchmarks),
                'apply' => $apply,
                'allow_partial' => $allowPartial,
                'continue_on_error' => $continueOnError,
                'provider_symbols' => $this->providerSymbolSummary($benchmarks),
                'processed_count' => 0,
                'success_count' => 0,
                'failed_count' => 0,
                'fetched_row_count' => 0,
                'upserted_count' => 0,
                'all_passed' => false,
                'cases' => [],
            ];

            foreach ($dates as $tradeDate) {
                $case = $this->processDate($tradeDate, $benchmarks, $apply, $allowPartial);
                $summary['cases'][] = $case;
                $summary = $this->refreshCounters($summary);

                if (($case['status'] ?? null) !== 'SUCCESS' && ! $continueOnError) {
                    break;
                }
            }

            return $this->refreshCounters($summary);
        } finally {
            config([
                'market_data.source.api.provider' => $previousApiProvider,
                'market_data.source.api.source_name' => $previousApiSourceName,
            ]);
        }
    }

    private function processDate($tradeDate, array $benchmarks, $apply, $allowPartial)
    {
        try {
            $sourceRows = $this->apiSourceAdapter->fetchOrLoadBenchmarkBars($tradeDate, 'api', $benchmarks);
            $telemetry = $this->apiSourceAdapter->consumeLastAcquisitionTelemetry();
            $rows = $this->rowsForStorage($sourceRows);
            $missingCodes = $this->missingBenchmarkCodes($benchmarks, $rows);

            if (! $allowPartial && ! empty($missingCodes)) {
                return [
                    'trade_date' => $tradeDate,
                    'status' => 'BLOCKED',
                    'reason_code' => 'SECTOR_INDEX_API_PARTIAL_RESPONSE',
                    'fetched_row_count' => count($rows),
                    'upserted_count' => 0,
                    'missing_benchmark_codes' => $missingCodes,
                    'source_acquisition_state' => $telemetry['source_acquisition_state'] ?? null,
                ];
            }

            if ($apply && ! empty($rows)) {
                $this->benchmarks->replaceBars($rows);
            }

            return [
                'trade_date' => $tradeDate,
                'status' => 'SUCCESS',
                'reason_code' => $apply ? 'COMMAND_APPLY_CONFIRMED' : 'COMMAND_DRY_RUN_ONLY',
                'fetched_row_count' => count($rows),
                'upserted_count' => $apply ? count($rows) : 0,
                'benchmark_codes' => array_values(array_map(function ($row) {
                    return $row['benchmark_code'];
                }, $rows)),
                'missing_benchmark_codes' => $missingCodes,
                'source_acquisition_state' => $telemetry['source_acquisition_state'] ?? null,
                'source_final_status' => $telemetry['source_final_status'] ?? null,
                'source_final_reason_code' => $telemetry['final_reason_code'] ?? null,
            ];
        } catch (SourceAcquisitionException $e) {
            $context = $e->context();

            return [
                'trade_date' => $tradeDate,
                'status' => 'BLOCKED',
                'reason_code' => $e->reasonCode(),
                'error_message' => $e->getMessage(),
                'fetched_row_count' => 0,
                'upserted_count' => 0,
                'source_acquisition_state' => $context['source_acquisition_state'] ?? null,
                'source_final_status' => $context['source_final_status'] ?? null,
                'failed_benchmark_codes' => $context['failed_benchmark_codes'] ?? null,
                'missing_benchmark_codes' => $context['missing_benchmark_codes'] ?? null,
            ];
        } catch (\Throwable $e) {
            return [
                'trade_date' => $tradeDate,
                'status' => 'ERROR',
                'reason_code' => 'COMMAND_EXECUTION_FAILED',
                'error_message' => $e->getMessage(),
                'fetched_row_count' => 0,
                'upserted_count' => 0,
            ];
        }
    }

    private function rowsForStorage(array $sourceRows)
    {
        $now = Carbon::now(config('market_data.platform.timezone'))->toDateTimeString();

        return array_values(array_map(function ($row) use ($now) {
            return [
                'benchmark_code' => Str::upper(trim((string) $row['benchmark_code'])),
                'trade_date' => (string) $row['trade_date'],
                'open_price' => round((float) $row['open'], 4),
                'high_price' => round((float) $row['high'], 4),
                'low_price' => round((float) $row['low'], 4),
                'close_price' => round((float) $row['close'], 4),
                'adjusted_close' => isset($row['adj_close']) && $row['adj_close'] !== null ? round((float) $row['adj_close'], 4) : round((float) $row['close'], 4),
                'volume' => isset($row['volume']) ? (int) $row['volume'] : null,
                'provider' => (string) $row['provider'],
                'provider_symbol' => (string) $row['provider_symbol'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $sourceRows));
    }

    private function sectorBenchmarks($provider, array $symbolMap, $symbolSuffix)
    {
        $classificationSystem = strtoupper(trim((string) config('market_data.sectors.classification_system', 'IDX-IC')));
        $sectorTable = config('market_data.sectors.table', 'market_data_sectors');

        $sectorIndexCodes = DB::table($sectorTable)
            ->where('classification_system', $classificationSystem)
            ->where('is_active', 1)
            ->whereNotNull('sector_index_code')
            ->pluck('sector_index_code')
            ->map(function ($code) {
                return Str::upper(trim((string) $code));
            })
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($sectorIndexCodes)) {
            throw new \RuntimeException('SECTOR_INDEX_MASTER_EMPTY: active sector taxonomy has no sector_index_code rows.');
        }

        $benchmarks = DB::table('market_benchmarks')
            ->whereIn('benchmark_code', $sectorIndexCodes)
            ->where('is_active', 1)
            ->get()
            ->map(function ($row) use ($provider, $symbolMap, $symbolSuffix) {
                $benchmarkCode = Str::upper(trim((string) $row->benchmark_code));

                return [
                    'benchmark_code' => $benchmarkCode,
                    'benchmark_name' => (string) $row->benchmark_name,
                    'provider' => $provider,
                    'provider_symbol' => $symbolMap[$benchmarkCode] ?? ($benchmarkCode.$symbolSuffix),
                    'instrument_type' => 'SECTOR_INDEX',
                    'is_active' => 1,
                ];
            })
            ->sortBy('benchmark_code')
            ->values()
            ->all();

        if (empty($benchmarks)) {
            throw new \RuntimeException('SECTOR_INDEX_BENCHMARK_MASTER_EMPTY: market_benchmarks has no active sector index benchmark rows.');
        }

        return $benchmarks;
    }

    private function tradingDates($startDate, $endDate)
    {
        $dates = $this->calendar->tradingDatesBetween($startDate, $endDate);
        if (empty($dates)) {
            throw new \RuntimeException('SECTOR_INDEX_API_REQUIRES_TRADING_DATES: market_calendar has no trading dates for requested range.');
        }

        return $dates;
    }

    private function providerSymbols($optionValue)
    {
        $configured = config('market_data.sectors.index_api.provider_symbols', []);
        $symbols = is_array($configured) ? $configured : [];

        if ($optionValue !== null && trim((string) $optionValue) !== '') {
            $decoded = json_decode((string) $optionValue, true);
            if (! is_array($decoded)) {
                throw new \RuntimeException('COMMAND_INVALID_JSON: --symbol_map_json must be valid JSON object.');
            }
            $symbols = array_replace($symbols, $decoded);
        }

        $normalized = [];
        foreach ($symbols as $code => $symbol) {
            $code = Str::upper(trim((string) $code));
            $symbol = trim((string) $symbol);
            if ($code !== '' && $symbol !== '') {
                $normalized[$code] = $symbol;
            }
        }

        return $normalized;
    }

    private function missingBenchmarkCodes(array $benchmarks, array $rows)
    {
        $expected = array_values(array_unique(array_map(function ($benchmark) {
            return Str::upper(trim((string) $benchmark['benchmark_code']));
        }, $benchmarks)));
        $actual = array_values(array_unique(array_map(function ($row) {
            return Str::upper(trim((string) $row['benchmark_code']));
        }, $rows)));

        return array_values(array_diff($expected, $actual));
    }

    private function providerSymbolSummary(array $benchmarks)
    {
        return array_values(array_map(function ($benchmark) {
            return $benchmark['benchmark_code'].':'.$benchmark['provider_symbol'];
        }, $benchmarks));
    }

    private function refreshCounters(array $summary)
    {
        $cases = $summary['cases'];
        $summary['processed_count'] = count($cases);
        $summary['success_count'] = count(array_filter($cases, function ($case) {
            return ($case['status'] ?? null) === 'SUCCESS';
        }));
        $summary['failed_count'] = count(array_filter($cases, function ($case) {
            return ($case['status'] ?? null) !== 'SUCCESS';
        }));
        $summary['fetched_row_count'] = array_sum(array_map(function ($case) {
            return (int) ($case['fetched_row_count'] ?? 0);
        }, $cases));
        $summary['upserted_count'] = array_sum(array_map(function ($case) {
            return (int) ($case['upserted_count'] ?? 0);
        }, $cases));
        $summary['all_passed'] = $summary['processed_count'] === $summary['trading_date_count']
            && $summary['failed_count'] === 0;

        return $summary;
    }
}

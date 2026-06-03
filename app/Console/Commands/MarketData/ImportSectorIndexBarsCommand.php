<?php

namespace App\Console\Commands\MarketData;

use App\Infrastructure\Persistence\MarketData\MarketBenchmarkRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ImportSectorIndexBarsCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:sector-indexes:import-bars {input_file?} {--source_name=manual_sector_index_csv} {--dry-run} {--apply}';

    protected $description = 'Validate or import source-backed sector index OHLC bars from CSV.';

    public function handle()
    {
        $inputFile = $this->argument('input_file');

        if (! $inputFile) {
            $this->renderCommandBlocked('COMMAND_MISSING_REQUIRED_INPUT', 'input_file is required.', [
                'input_file' => $inputFile,
            ]);

            return 1;
        }

        if ((bool) $this->option('dry-run') && (bool) $this->option('apply')) {
            $this->renderCommandBlocked('COMMAND_CONFLICTING_OPTIONS', '--dry-run and --apply cannot be used together.', [
                'input_file' => $this->normalizeOptionalPathForDisplay($inputFile),
            ]);

            return 1;
        }

        if (! is_file($inputFile)) {
            $this->renderCommandBlocked('COMMAND_MISSING_REQUIRED_INPUT', 'input_file must point to an existing CSV file.', [
                'input_file' => $this->normalizeOptionalPathForDisplay($inputFile),
            ]);

            return 1;
        }

        try {
            $parsed = $this->parseCsv($inputFile);
            $validated = $this->validateRows($parsed['rows']);
        } catch (\Throwable $e) {
            $this->renderCommandBlocked('COMMAND_EXECUTION_FAILED', $e->getMessage(), [
                'input_file' => $this->normalizeOptionalPathForDisplay($inputFile),
            ]);

            return 1;
        }

        $apply = (bool) $this->option('apply');
        $errorCount = count($validated['errors']);

        if ($errorCount > 0) {
            $this->error('status=BLOCKED');
            $this->line('reason_code=COMMAND_EXECUTION_FAILED');
            $this->line('error=CSV validation failed.');
            $this->renderSummary($inputFile, $parsed['row_count'], count($validated['valid_rows']), 0, $errorCount, $apply, $validated['benchmark_codes']);
            foreach (array_slice($validated['errors'], 0, 25) as $error) {
                $this->line('validation_error='.$error);
            }

            return 1;
        }

        $upsertedCount = 0;
        if ($apply) {
            app(MarketBenchmarkRepository::class)->replaceBars($validated['valid_rows']);
            $upsertedCount = count($validated['valid_rows']);
        }

        $this->info('status='.($apply ? 'APPLIED' : 'DRY_RUN'));
        $this->line('reason_code='.($apply ? 'COMMAND_APPLY_CONFIRMED' : 'COMMAND_DRY_RUN_ONLY'));
        $this->renderSummary($inputFile, $parsed['row_count'], count($validated['valid_rows']), $upsertedCount, 0, $apply, $validated['benchmark_codes']);
        $this->line('next_action='.($apply ? 'Run benchmark/equity indicator recompute and promote for affected trade dates.' : 'Re-run with --apply after reviewing validation output.'));

        return 0;
    }

    private function parseCsv($inputFile)
    {
        $handle = fopen($inputFile, 'r');
        if (! $handle) {
            throw new \RuntimeException('Unable to read input CSV.');
        }

        $headers = null;
        $rows = [];
        $lineNumber = 0;

        while (($line = fgetcsv($handle)) !== false) {
            $lineNumber++;
            if ($lineNumber === 1) {
                $headers = array_map([$this, 'normalizeHeader'], $line);
                continue;
            }

            if ($this->isEmptyCsvLine($line)) {
                continue;
            }

            $row = [];
            foreach ($headers as $index => $header) {
                if ($header === '') {
                    continue;
                }
                $row[$header] = isset($line[$index]) ? trim((string) $line[$index]) : '';
            }
            $row['_line'] = $lineNumber;
            $rows[] = $row;
        }

        fclose($handle);

        if ($headers === null) {
            throw new \RuntimeException('CSV file is empty.');
        }

        foreach (['sector_index_code', 'trade_date', 'open', 'high', 'low', 'close'] as $requiredHeader) {
            if (! in_array($requiredHeader, $headers, true)) {
                throw new \RuntimeException('CSV header must include '.$requiredHeader.'.');
            }
        }

        return [
            'row_count' => count($rows),
            'rows' => $rows,
        ];
    }

    private function validateRows(array $rows)
    {
        $errors = [];
        $validRows = [];
        $sourceName = trim((string) ($this->option('source_name') ?: config('market_data.sectors.index_provider', 'manual_sector_index_csv')));
        $now = Carbon::now(config('market_data.platform.timezone'))->toDateTimeString();
        $activeSectorIndexCodes = $this->activeSectorIndexCodes();
        $benchmarkCodes = $this->activeBenchmarkCodesForSectorIndexes(array_keys($activeSectorIndexCodes));
        $seen = [];

        foreach ($rows as $row) {
            $line = (int) ($row['_line'] ?? 0);
            $sectorIndexCode = strtoupper(trim((string) ($row['sector_index_code'] ?? '')));
            $tradeDate = trim((string) ($row['trade_date'] ?? ''));

            if ($sectorIndexCode === '') {
                $errors[] = 'line '.$line.': sector_index_code is required.';
                continue;
            }

            if (! isset($activeSectorIndexCodes[$sectorIndexCode])) {
                $errors[] = 'line '.$line.': sector_index_code '.$sectorIndexCode.' is not active in sector taxonomy.';
                continue;
            }

            if (! isset($benchmarkCodes[$sectorIndexCode])) {
                $errors[] = 'line '.$line.': sector_index_code '.$sectorIndexCode.' is not seeded in market_benchmarks.';
                continue;
            }

            if (! $this->isIsoDate($tradeDate)) {
                $errors[] = 'line '.$line.': trade_date must use YYYY-MM-DD.';
                continue;
            }

            $open = $this->positiveDecimal($row['open'] ?? null);
            $high = $this->positiveDecimal($row['high'] ?? null);
            $low = $this->positiveDecimal($row['low'] ?? null);
            $close = $this->positiveDecimal($row['close'] ?? null);
            $adjustedClose = $this->positiveDecimal($row['adj_close'] ?? null, true);
            $volume = $this->nullableInteger($row['volume'] ?? null);

            if ($open === null || $high === null || $low === null || $close === null) {
                $errors[] = 'line '.$line.': open, high, low, and close must be positive decimals.';
                continue;
            }

            if ($adjustedClose === false) {
                $errors[] = 'line '.$line.': adj_close must be a positive decimal when provided.';
                continue;
            }

            if ($volume === false) {
                $errors[] = 'line '.$line.': volume must be a non-negative integer when provided.';
                continue;
            }

            if ($high < max($open, $low, $close) || $low > min($open, $high, $close)) {
                $errors[] = 'line '.$line.': OHLC range is invalid.';
                continue;
            }

            $identity = $sectorIndexCode.'|'.$tradeDate;
            if (isset($seen[$identity])) {
                $errors[] = 'line '.$line.': duplicate sector_index_code/trade_date in CSV.';
                continue;
            }
            $seen[$identity] = true;

            $validRows[] = [
                'benchmark_code' => $sectorIndexCode,
                'trade_date' => $tradeDate,
                'open_price' => $open,
                'high_price' => $high,
                'low_price' => $low,
                'close_price' => $close,
                'adjusted_close' => $adjustedClose !== null && $adjustedClose !== false ? $adjustedClose : $close,
                'volume' => $volume !== false ? $volume : null,
                'provider' => $sourceName,
                'provider_symbol' => $sectorIndexCode,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        return [
            'valid_rows' => $validRows,
            'errors' => $errors,
            'benchmark_codes' => array_values(array_unique(array_map(function ($row) {
                return $row['benchmark_code'];
            }, $validRows))),
        ];
    }

    private function renderSummary($inputFile, $rowCount, $validCount, $upsertedCount, $errorCount, $apply, array $benchmarkCodes)
    {
        sort($benchmarkCodes);

        $this->line('input_file='.$this->normalizeOptionalPathForDisplay($inputFile));
        $this->line('source_name='.(string) ($this->option('source_name') ?: config('market_data.sectors.index_provider', 'manual_sector_index_csv')));
        $this->line('operation_mode='.($apply ? 'APPLY' : 'DRY_RUN'));
        $this->line('row_count='.(int) $rowCount);
        $this->line('valid_row_count='.(int) $validCount);
        $this->line('upserted_count='.(int) $upsertedCount);
        $this->line('error_count='.(int) $errorCount);
        $this->line('benchmark_codes='.implode(',', $benchmarkCodes));
    }

    private function activeSectorIndexCodes()
    {
        $classificationSystem = strtoupper(trim((string) config('market_data.sectors.classification_system', 'IDX-IC')));

        return DB::table(config('market_data.sectors.table', 'market_data_sectors'))
            ->where('classification_system', $classificationSystem)
            ->where('is_active', 1)
            ->whereNotNull('sector_index_code')
            ->pluck('sector_index_code')
            ->map(function ($code) {
                return strtoupper(trim((string) $code));
            })
            ->filter()
            ->mapWithKeys(function ($code) {
                return [$code => true];
            })
            ->all();
    }

    private function activeBenchmarkCodesForSectorIndexes(array $sectorIndexCodes)
    {
        if (empty($sectorIndexCodes)) {
            return [];
        }

        return DB::table('market_benchmarks')
            ->whereIn('benchmark_code', $sectorIndexCodes)
            ->where('is_active', 1)
            ->pluck('benchmark_code')
            ->map(function ($code) {
                return strtoupper(trim((string) $code));
            })
            ->mapWithKeys(function ($code) {
                return [$code => true];
            })
            ->all();
    }

    private function normalizeHeader($header)
    {
        return strtolower(trim((string) preg_replace('/^\xEF\xBB\xBF/', '', (string) $header)));
    }

    private function isEmptyCsvLine(array $line)
    {
        foreach ($line as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function isIsoDate($value)
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $value)) {
            return false;
        }

        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', (string) $value);

        return $date !== false && $date->format('Y-m-d') === (string) $value;
    }

    private function positiveDecimal($value, $allowEmpty = false)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return $allowEmpty ? null : null;
        }

        if (! is_numeric($value) || (float) $value <= 0) {
            return $allowEmpty ? false : null;
        }

        return round((float) $value, 4);
    }

    private function nullableInteger($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        if (! preg_match('/^\d+$/', $value)) {
            return false;
        }

        return (int) $value;
    }
}

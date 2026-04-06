<?php

namespace App\Infrastructure\MarketData\Source;

use Carbon\Carbon;
use Illuminate\Support\Str;

class LocalFileEodBarsAdapter
{
    public function fetchOrLoadEodBars($tradeDate, $sourceMode)
    {
        if (! in_array($sourceMode, ['manual_file', 'manual_entry'], true)) {
            throw new \RuntimeException('Source mode '.$sourceMode.' belum diimplementasikan. Gunakan manual_file atau manual_entry.');
        }

        $explicitInputFile = $this->resolveExplicitInputFilePath();
        if ($explicitInputFile !== null) {
            return $this->loadExplicitInputFile($explicitInputFile, $tradeDate);
        }

        $basePath = base_path(config('market_data.source.local_directory'));
        $jsonPath = $basePath.'/'.str_replace('{date}', $tradeDate, config('market_data.source.file_template_json'));
        $csvPath = $basePath.'/'.str_replace('{date}', $tradeDate, config('market_data.source.file_template_csv'));

        if (file_exists($jsonPath)) {
            return $this->loadJson($jsonPath, $tradeDate);
        }

        if (file_exists($csvPath)) {
            return $this->loadCsv($csvPath, $tradeDate);
        }

        throw new \RuntimeException('Sumber bars lokal untuk '.$tradeDate.' tidak ditemukan pada path JSON/CSV yang dikonfigurasi.');
    }

    private function resolveExplicitInputFilePath()
    {
        $configured = trim((string) config('market_data.source.local_input_file', ''));
        if ($configured == '') {
            return null;
        }

        $candidate = $this->isAbsolutePath($configured) ? $configured : base_path($configured);

        if (! file_exists($candidate)) {
            throw new \RuntimeException('Explicit local input file not found: '.$configured);
        }

        return $candidate;
    }

    private function loadExplicitInputFile($path, $tradeDate)
    {
        $extension = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));

        if ($extension === 'json') {
            return $this->loadJson($path, $tradeDate);
        }

        if ($extension === 'csv') {
            return $this->loadCsv($path, $tradeDate);
        }

        throw new \RuntimeException('Explicit local input file must use .json or .csv extension.');
    }

    private function isAbsolutePath($path)
    {
        return Str::startsWith($path, ['/','\\'])
            || preg_match('/^[A-Za-z]:[\\\/]/', $path) === 1;
    }

    private function loadJson($path, $tradeDate)
    {
        $payload = json_decode(file_get_contents($path), true);

        if (! is_array($payload)) {
            throw new \RuntimeException('File JSON bars lokal tidak valid.');
        }

        return collect($payload)->map(function ($row, $index) use ($tradeDate) {
            return $this->normalizeRow($row, $tradeDate, 'json:'.($index + 1));
        })->all();
    }

    private function loadCsv($path, $tradeDate)
    {
        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new \RuntimeException('File CSV bars lokal tidak dapat dibuka.');
        }

        $header = fgetcsv($handle);

        if (! is_array($header)) {
            fclose($handle);
            throw new \RuntimeException('Header CSV bars lokal tidak ditemukan.');
        }

        $normalizedHeader = array_map(function ($item) {
            return Str::snake(trim($item));
        }, $header);

        $required = ['ticker_code', 'trade_date', 'open', 'high', 'low', 'close', 'volume'];
        foreach ($required as $column) {
            if (! in_array($column, $normalizedHeader, true)) {
                fclose($handle);
                throw new \RuntimeException('Header CSV bars lokal tidak lengkap. Kolom wajib hilang: '.$column);
            }
        }

        $rows = [];
        $line = 1;
        while (($data = fgetcsv($handle)) !== false) {
            $line++;
            $rows[] = $this->normalizeRow(array_combine($normalizedHeader, $data), $tradeDate, 'csv:'.$line);
        }

        fclose($handle);

        return $rows;
    }

    private function normalizeRow(array $row, $tradeDate, $fallbackRowRef)
    {
        $capturedAt = isset($row['captured_at']) && $row['captured_at']
            ? Carbon::parse($row['captured_at'])->toDateTimeString()
            : Carbon::now(config('market_data.platform.timezone'))->toDateTimeString();

        return [
            'ticker_code' => isset($row['ticker_code']) ? Str::upper(trim($row['ticker_code'])) : null,
            'trade_date' => $row['trade_date'] ?? $tradeDate,
            'open' => array_key_exists('open', $row) ? $row['open'] : null,
            'high' => array_key_exists('high', $row) ? $row['high'] : null,
            'low' => array_key_exists('low', $row) ? $row['low'] : null,
            'close' => array_key_exists('close', $row) ? $row['close'] : null,
            'volume' => array_key_exists('volume', $row) ? $row['volume'] : null,
            'adj_close' => array_key_exists('adj_close', $row) ? $row['adj_close'] : null,
            'source_name' => $row['source_name'] ?? strtoupper(config('market_data.source.default_source_name')),
            'source_row_ref' => $row['source_row_ref'] ?? $fallbackRowRef,
            'captured_at' => $capturedAt,
        ];
    }
}

<?php

namespace App\Infrastructure\MarketData\Source;

use Carbon\Carbon;
use Illuminate\Support\Str;

class LocalFileEodBarsAdapter
{
    public function fetchOrLoadEodBars($tradeDate, $sourceMode)
    {
        if (! in_array($sourceMode, ['manual_file', 'manual_entry'], true)) {
            throw $this->manualFileException(
                'Source mode '.$sourceMode.' belum diimplementasikan. Gunakan manual_file atau manual_entry.',
                'RUN_SOURCE_MODE_UNSUPPORTED',
                ['source_mode_requested' => $sourceMode]
            );
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

        throw $this->manualFileException(
            'Sumber bars lokal untuk '.$tradeDate.' tidak ditemukan pada path JSON/CSV yang dikonfigurasi.',
            'RUN_SOURCE_MANUAL_FILE_NOT_FOUND',
            [
                'trade_date' => $tradeDate,
                'json_path' => $jsonPath,
                'csv_path' => $csvPath,
            ]
        );
    }

    private function resolveExplicitInputFilePath()
    {
        $configured = trim((string) config('market_data.source.local_input_file', ''));
        if ($configured == '') {
            return null;
        }

        $candidate = $this->isAbsolutePath($configured) ? $configured : base_path($configured);

        if (! file_exists($candidate)) {
            throw $this->manualFileException(
                'Explicit local input file not found: '.$configured,
                'RUN_SOURCE_MANUAL_FILE_NOT_FOUND',
                [
                    'source_input_file' => $configured,
                    'resolved_source_input_file' => $candidate,
                ]
            );
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

        throw $this->manualFileException(
            'Explicit local input file must use .json or .csv extension.',
            'RUN_SOURCE_MANUAL_FILE_MALFORMED',
            ['source_input_file' => $path]
        );
    }


    private function manualFileException($message, $reasonCode, array $context = [])
    {
        return new SourceAcquisitionException($message, $reasonCode, 0, null, array_merge([
            'source_mode' => 'manual_file',
            'source_name' => 'LOCAL_FILE',
            'provider' => null,
            'final_reason_code' => $reasonCode,
            'source_final_status' => 'FAILED',
        ], $context));
    }

    private function isAbsolutePath($path)
    {
        return Str::startsWith($path, ['/','\\'])
            || preg_match('~^[A-Za-z]:[\\/]~', $path) === 1;
    }

    private function loadJson($path, $tradeDate)
    {
        if (! is_readable($path)) {
            throw $this->manualFileException(
                'File JSON bars lokal tidak dapat dibaca.',
                'RUN_SOURCE_MANUAL_FILE_NOT_READABLE',
                ['source_input_file' => $path]
            );
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            throw $this->manualFileException(
                'File JSON bars lokal tidak dapat dibuka.',
                'RUN_SOURCE_MANUAL_FILE_NOT_READABLE',
                ['source_input_file' => $path]
            );
        }

        $payload = json_decode($contents, true);

        if (! is_array($payload)) {
            throw $this->manualFileException(
                'File JSON bars lokal tidak valid.',
                'RUN_SOURCE_MANUAL_FILE_MALFORMED',
                ['source_input_file' => $path]
            );
        }

        return collect($payload)->map(function ($row, $index) use ($tradeDate, $path) {
            if (! is_array($row)) {
                throw $this->manualFileException(
                    'File JSON bars lokal berisi row yang bukan object/array.',
                    'RUN_SOURCE_MANUAL_FILE_MALFORMED',
                    [
                        'source_input_file' => $path,
                        'source_row_ref' => 'json:'.($index + 1),
                    ]
                );
            }

            return $this->normalizeRow($row, $tradeDate, 'json:'.($index + 1));
        })->all();
    }

    private function loadCsv($path, $tradeDate)
    {
        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw $this->manualFileException(
                'File CSV bars lokal tidak dapat dibuka.',
                'RUN_SOURCE_MANUAL_FILE_NOT_READABLE',
                ['source_input_file' => $path]
            );
        }

        $header = fgetcsv($handle);

        if (! is_array($header)) {
            fclose($handle);
            throw $this->manualFileException(
                'Header CSV bars lokal tidak ditemukan.',
                'RUN_SOURCE_MANUAL_FILE_MALFORMED',
                ['source_input_file' => $path]
            );
        }

        $normalizedHeader = array_map(function ($item) {
            return Str::snake(trim($item));
        }, $header);

        $required = ['ticker_code', 'trade_date', 'open', 'high', 'low', 'close', 'volume'];
        foreach ($required as $column) {
            if (! in_array($column, $normalizedHeader, true)) {
                fclose($handle);
                throw $this->manualFileException(
                    'Header CSV bars lokal tidak lengkap. Kolom wajib hilang: '.$column,
                    'RUN_SOURCE_MANUAL_FILE_MALFORMED',
                    [
                        'source_input_file' => $path,
                        'missing_column' => $column,
                    ]
                );
            }
        }

        $rows = [];
        $line = 1;
        while (($data = fgetcsv($handle)) !== false) {
            $line++;
            if (count($data) !== count($normalizedHeader)) {
                fclose($handle);
                throw $this->manualFileException(
                    'Row CSV bars lokal memiliki jumlah kolom yang tidak cocok dengan header.',
                    'RUN_SOURCE_MANUAL_FILE_MALFORMED',
                    [
                        'source_input_file' => $path,
                        'source_row_ref' => 'csv:'.$line,
                    ]
                );
            }

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
            'source_name' => 'LOCAL_FILE',
            'source_row_ref' => $row['source_row_ref'] ?? $fallbackRowRef,
            'captured_at' => $capturedAt,
        ];
    }
}

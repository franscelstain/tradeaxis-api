<?php

namespace App\Infrastructure\MarketData\Source;

use Carbon\Carbon;
use Illuminate\Support\Str;

class LocalFileSessionSnapshotAdapter
{
    public function loadRows($path)
    {
        if (! is_file($path)) {
            throw new \RuntimeException('Session snapshot input file not found: '.$path);
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($extension === 'json') {
            return $this->loadJson($path);
        }

        return $this->loadCsv($path);
    }

    private function loadJson($path)
    {
        $decoded = json_decode(file_get_contents($path), true);
        if (! is_array($decoded)) {
            throw new \RuntimeException('Session snapshot JSON must decode to an array of rows.');
        }

        return array_values(array_map([$this, 'normalizeRow'], $decoded));
    }

    private function loadCsv($path)
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new \RuntimeException('Unable to open session snapshot CSV: '.$path);
        }

        $header = fgetcsv($handle);
        if (! is_array($header)) {
            fclose($handle);
            throw new \RuntimeException('Session snapshot CSV header is missing.');
        }

        $header = array_map(function ($value) {
            return trim((string) $value);
        }, $header);

        $rows = [];
        while (($data = fgetcsv($handle)) !== false) {
            $assoc = [];
            foreach ($header as $index => $column) {
                $assoc[$column] = array_key_exists($index, $data) ? $data[$index] : null;
            }
            $rows[] = $this->normalizeRow($assoc);
        }
        fclose($handle);

        return $rows;
    }

    private function normalizeRow(array $row)
    {
        if (! array_key_exists('ticker_code', $row) || trim((string) $row['ticker_code']) === '') {
            throw new \RuntimeException('Session snapshot row missing ticker_code.');
        }

        $capturedAt = isset($row['captured_at']) && trim((string) $row['captured_at']) !== ''
            ? Carbon::parse($row['captured_at'])->setTimezone(config('market_data.platform.timezone'))->toDateTimeString()
            : Carbon::now(config('market_data.platform.timezone'))->toDateTimeString();

        return [
            'ticker_code' => Str::upper(trim((string) $row['ticker_code'])),
            'captured_at' => $capturedAt,
            'last_price' => $this->normalizeDecimal($row, 'last_price'),
            'prev_close' => $this->normalizeDecimal($row, 'prev_close'),
            'chg_pct' => $this->normalizeDecimal($row, 'chg_pct'),
            'volume' => $this->normalizeInteger($row, 'volume'),
            'day_high' => $this->normalizeDecimal($row, 'day_high'),
            'day_low' => $this->normalizeDecimal($row, 'day_low'),
        ];
    }

    private function normalizeDecimal(array $row, $field)
    {
        if (! array_key_exists($field, $row) || $row[$field] === '' || $row[$field] === null) {
            return null;
        }

        return (float) $row[$field];
    }

    private function normalizeInteger(array $row, $field)
    {
        if (! array_key_exists($field, $row) || $row[$field] === '' || $row[$field] === null) {
            return null;
        }

        return (int) $row[$field];
    }
}

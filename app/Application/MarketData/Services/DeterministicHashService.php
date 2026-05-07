<?php

namespace App\Application\MarketData\Services;

class DeterministicHashService
{
    public function hashRows(iterable $rows, array $columns)
    {
        return hash($this->hashAlgorithm(), $this->serializeRows($rows, $columns));
    }

    public function serializeRows(iterable $rows, array $columns)
    {
        $lines = [];

        foreach ($rows as $row) {
            $normalized = [];
            foreach ($columns as $column) {
                $value = null;
                if (is_array($row)) {
                    $value = array_key_exists($column, $row) ? $row[$column] : null;
                } elseif (is_object($row)) {
                    $value = property_exists($row, $column) || isset($row->{$column}) ? $row->{$column} : null;
                }

                $normalized[] = $column.'='.$this->encodeCanonicalValue($this->normalizeValue($value));
            }

            $lines[] = implode($this->delimiter(), $normalized);
        }

        sort($lines, SORT_STRING);

        return implode($this->lineSeparator(), $lines);
    }

    public function normalizeValue($value)
    {
        if ($value === null) {
            return $this->nullToken();
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d\TH:i:sP');
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value)) {
            return (string) $value;
        }

        if (is_float($value) || (is_string($value) && is_numeric($value))) {
            return $this->normalizeNumericValue($value);
        }

        return (string) $value;
    }

    public function hashAlgorithm()
    {
        $configured = (string) $this->config('market_data.hash.algorithm', 'SHA-256');
        $algorithm = strtolower(str_replace('-', '', $configured));

        return in_array($algorithm, hash_algos(), true) ? $algorithm : 'sha256';
    }

    public function delimiter()
    {
        return (string) $this->config('market_data.hash.delimiter', '|');
    }

    public function lineSeparator()
    {
        return (string) $this->config('market_data.hash.line_separator', "\n");
    }

    public function nullToken()
    {
        return (string) $this->config('market_data.hash.null_token', '[empty]');
    }

    private function normalizeNumericValue($value)
    {
        $formatted = sprintf('%.10F', (float) $value);
        $formatted = rtrim(rtrim($formatted, '0'), '.');

        return $formatted === '-0' || $formatted === '' ? '0' : $formatted;
    }

    private function encodeCanonicalValue($value)
    {
        $encoded = json_encode((string) $value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return $encoded === false ? '""' : $encoded;
    }

    private function config($key, $default)
    {
        if (function_exists('config')) {
            try {
                $value = config($key, $default);
                return $value === null ? $default : $value;
            } catch (\Throwable $e) {
                return $default;
            }
        }

        return $default;
    }
}

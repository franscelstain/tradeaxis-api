<?php

namespace App\Application\MarketData\Services;

class DeterministicHashService
{
    public function hashRows(iterable $rows, array $columns)
    {
        return hash('sha256', $this->serializeRows($rows, $columns));
    }

    public function serializeRows(iterable $rows, array $columns)
    {
        $parts = [];
        foreach ($rows as $row) {
            $normalized = [];
            foreach ($columns as $column) {
                $value = null;
                if (is_array($row)) {
                    $value = array_key_exists($column, $row) ? $row[$column] : null;
                } elseif (is_object($row)) {
                    $value = isset($row->{$column}) ? $row->{$column} : null;
                }
                $normalized[$column] = $this->normalizeValue($value);
            }
            $parts[] = json_encode($normalized, JSON_UNESCAPED_SLASHES);
        }

        return implode("\n", $parts);
    }

    public function normalizeValue($value)
    {
        if ($value === null) {
            return null;
        }
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }
        if (is_numeric($value)) {
            return sprintf('%.10F', (float) $value);
        }

        return (string) $value;
    }
}

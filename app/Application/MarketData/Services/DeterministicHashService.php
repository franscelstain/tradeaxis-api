<?php

namespace App\Application\MarketData\Services;

/** Canonical SHA-256 row serializer owned by the locked hash contracts. */
class DeterministicHashService
{
    public const NULL_TOKEN = '';

    public function hashRows(iterable $rows, array $columns)
    {
        return hash($this->hashAlgorithm(), $this->serializeRows($rows, $columns));
    }

    public function serializeRows(iterable $rows, array $columns)
    {
        $serialized = [];

        foreach ($rows as $row) {
            $tokens = [];
            $values = [];
            foreach ($columns as $column) {
                $value = $this->rowValue($row, $column);
                $values[$column] = $this->normalizeValue($value, $column);
                $tokens[] = $values[$column];
            }

            $serialized[] = [
                'key' => $this->stableRowKey($values, $columns),
                'line' => implode($this->delimiter(), $tokens),
            ];
        }

        usort($serialized, function (array $left, array $right) {
            $keyOrder = strcmp($left['key'], $right['key']);

            return $keyOrder !== 0 ? $keyOrder : strcmp($left['line'], $right['line']);
        });

        return implode($this->lineSeparator(), array_column($serialized, 'line'));
    }

    public function normalizeValue($value, $field = null)
    {
        $field = (string) $field;
        if ($value === null) {
            return $this->nullToken();
        }

        if ($value instanceof \DateTimeInterface) {
            $copy = clone $value;
            $copy->setTimezone(new \DateTimeZone((string) $this->config('market_data.platform.timezone', 'Asia/Jakarta')));

            return $copy->format('Y-m-d H:i:s');
        }

        if (is_array($value) || is_object($value)) {
            return $this->canonicalJson($value, $field);
        }

        if ($this->isJsonField($field) && is_string($value) && trim($value) !== '') {
            $decoded = json_decode($value, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                if (preg_match('/^[A-Z][A-Z0-9_]*(?:[,;][A-Z][A-Z0-9_]*)*$/', $value)) {
                    return $this->canonicalJson(preg_split('/[,;]/', $value), $field);
                }
                throw new \RuntimeException(
                    'HASH_CANONICAL_JSON_INVALID: '.$field.' error='.json_last_error_msg()
                    .' value='.substr($value, 0, 120)
                );
            }

            return $this->canonicalJson($decoded, $field);
        }

        if (is_bool($value) || $this->isBooleanField($field)) {
            if (in_array($value, [true, 1, '1'], true)) {
                return '1';
            }
            if (in_array($value, [false, 0, '0'], true)) {
                return '0';
            }
            throw new \RuntimeException('HASH_BOOLEAN_INVALID: '.$field);
        }

        $scale = $this->decimalScale($field);
        if ($scale !== null) {
            if (! is_numeric($value)) {
                throw new \RuntimeException('HASH_NUMBER_INVALID: '.$field);
            }

            return $this->fixedDecimal($value, $scale);
        }

        if ($this->isIntegerField($field)) {
            if (! is_numeric($value) || (float) $value != (int) $value) {
                throw new \RuntimeException('HASH_INTEGER_INVALID: '.$field);
            }

            return (string) (int) $value;
        }

        if (is_int($value) || is_float($value)) {
            throw new \RuntimeException('HASH_NUMBER_FORMAT_UNOWNED_FIELD: '.$field);
        }

        $text = (string) $value;
        if (preg_match('//u', $text) !== 1) {
            throw new \RuntimeException('HASH_TEXT_NOT_UTF8: '.$field);
        }
        if ($this->isDateField($field) && ! $this->isCanonicalDate($text)) {
            throw new \RuntimeException('HASH_DATE_INVALID: '.$field);
        }
        if ($this->isTimestampField($field) && ! $this->isCanonicalTimestamp($text)) {
            throw new \RuntimeException('HASH_TIMESTAMP_INVALID: '.$field);
        }
        if ($this->isHashField($field) && preg_match('/^[a-f0-9]{64}$/', $text) !== 1) {
            throw new \RuntimeException('HASH_CONTENT_HASH_INVALID: '.$field);
        }
        if (strpos($text, $this->delimiter()) !== false || preg_match('/[\r\n]/', $text)) {
            throw new \RuntimeException('HASH_TEXT_DELIMITER_UNESCAPED: '.$field);
        }

        return $text;
    }

    public function hashAlgorithm()
    {
        $configured = strtoupper((string) $this->config('market_data.hash.algorithm', 'SHA-256'));
        if ($configured !== 'SHA-256') {
            throw new \RuntimeException('HASH_ALGORITHM_NOT_LOCKED_SHA256: '.$configured);
        }

        return 'sha256';
    }

    public function delimiter()
    {
        $configured = (string) $this->config('market_data.hash.delimiter', '|');
        if ($configured !== '|') {
            throw new \RuntimeException('HASH_DELIMITER_NOT_LOCKED: '.json_encode($configured));
        }

        return '|';
    }

    public function lineSeparator()
    {
        $configured = (string) $this->config('market_data.hash.line_separator', "\n");
        if ($configured !== "\n") {
            throw new \RuntimeException('HASH_LINE_SEPARATOR_NOT_LOCKED: '.json_encode($configured));
        }

        return "\n";
    }

    /** Audit_Hash owns serialization semantics: NULL is the empty token. */
    public function nullToken()
    {
        $configured = (string) $this->config('market_data.hash.null_token', self::NULL_TOKEN);
        if ($configured !== self::NULL_TOKEN) {
            throw new \RuntimeException('HASH_NULL_TOKEN_NOT_LOCKED_EMPTY: '.json_encode($configured));
        }

        return self::NULL_TOKEN;
    }

    private function rowValue($row, string $column)
    {
        if (is_array($row)) {
            return array_key_exists($column, $row) ? $row[$column] : null;
        }
        if (is_object($row)) {
            return property_exists($row, $column) || isset($row->{$column}) ? $row->{$column} : null;
        }

        throw new \RuntimeException('HASH_ROW_SHAPE_INVALID');
    }

    private function stableRowKey(array $values, array $columns): string
    {
        $keyFields = [];
        foreach (['trade_date', 'listing_id', 'ticker_id'] as $field) {
            if (in_array($field, $columns, true)) {
                $keyFields[] = $values[$field];
            }
        }

        if ($keyFields === []) {
            throw new \RuntimeException('HASH_STABLE_ARTIFACT_KEY_MISSING');
        }

        return implode('|', $keyFields);
    }

    private function decimalScale(string $field)
    {
        if (in_array($field, ['open', 'high', 'low', 'close', 'previous_close', 'hh20', 'll20', 'ma20', 'ma50'], true)) {
            return 4;
        }
        if (in_array($field, ['traded_value_idr_actual', 'adv20_traded_value_idr_actual', 'adv20_close_volume_proxy_idr', 'dv20_idr'], true)) {
            return 2;
        }
        if ($field === 'atr14') {
            return 10;
        }
        if ($field === 'coverage_ratio') {
            return 4;
        }
        if (in_array($field, ['price_factor', 'volume_factor'], true) || preg_match('/_factor$/', $field)) {
            return 12;
        }
        if (in_array($field, ['atr14_pct', 'vol_ratio', 'roc5', 'roc10', 'roc20', 'sector_roc20',
            'close_to_hh20_pct', 'close_to_ll20_pct', 'range_20_pct', 'range_position_20_pct',
            'close_vs_ma20_pct', 'close_vs_ma50_pct', 'ma20_slope_pct', 'rs_20_vs_ihsg',
            'rs_20_vs_sector', 'sector_rs_20_vs_ihsg'], true)) {
            return 10;
        }

        return null;
    }

    private function isIntegerField(string $field): bool
    {
        return $field === 'volume'
            || preg_match('/(^|_)(id|count|days|minutes|seconds|bytes|qps)$/', $field) === 1;
    }

    private function isBooleanField(string $field): bool
    {
        return $field === 'eligible' || strpos($field, 'is_') === 0 || substr($field, -5) === '_flag';
    }

    private function isJsonField(string $field): bool
    {
        return substr($field, -5) === '_json' || substr($field, -8) === '_reasons';
    }

    private function fixedDecimal($value, int $scale): string
    {
        $raw = is_float($value) ? sprintf('%.17g', $value) : trim((string) $value);
        if (! preg_match('/^(?<sign>[+-]?)(?<integer>\d+)(?:\.(?<fraction>\d*))?(?:[eE](?<exponent>[+-]?\d+))?$/', $raw, $match)) {
            throw new \RuntimeException('HASH_NUMBER_INVALID');
        }

        $sign = $match['sign'] === '-' ? '-' : '';
        $integer = $match['integer'];
        $fraction = isset($match['fraction']) ? $match['fraction'] : '';
        $exponent = isset($match['exponent']) && $match['exponent'] !== '' ? (int) $match['exponent'] : 0;
        $digits = $integer.$fraction;
        $decimalPosition = strlen($integer) + $exponent;
        if (abs($exponent) > 256 || strlen($digits) > 512) {
            throw new \RuntimeException('HASH_NUMBER_OUT_OF_RANGE');
        }
        if ($decimalPosition <= 0) {
            $integer = '0';
            $fraction = str_repeat('0', -$decimalPosition).$digits;
        } elseif ($decimalPosition >= strlen($digits)) {
            $integer = $digits.str_repeat('0', $decimalPosition - strlen($digits));
            $fraction = '';
        } else {
            $integer = substr($digits, 0, $decimalPosition);
            $fraction = substr($digits, $decimalPosition);
        }

        $integer = ltrim($integer, '0');
        $integer = $integer === '' ? '0' : $integer;
        $roundingSource = str_pad($fraction, $scale + 1, '0');
        $retained = $scale === 0 ? '' : substr($roundingSource, 0, $scale);
        $combined = $integer.$retained;
        if ((int) $roundingSource[$scale] >= 5) {
            $combined = $this->incrementDecimalDigits($combined);
        }
        if ($scale > 0) {
            $combined = str_pad($combined, $scale + 1, '0', STR_PAD_LEFT);
            $integer = substr($combined, 0, -$scale);
            $retained = substr($combined, -$scale);
        } else {
            $integer = $combined;
        }

        $isZero = trim($integer, '0') === '' && ($scale === 0 || trim($retained, '0') === '');

        return ($sign !== '' && ! $isZero ? '-' : '').$integer.($scale > 0 ? '.'.$retained : '');
    }

    private function incrementDecimalDigits(string $digits): string
    {
        $digits = $digits === '' ? '0' : $digits;
        for ($index = strlen($digits) - 1; $index >= 0; $index--) {
            if ($digits[$index] !== '9') {
                $digits[$index] = (string) ((int) $digits[$index] + 1);

                return $digits;
            }
            $digits[$index] = '0';
        }

        return '1'.$digits;
    }

    private function isDateField(string $field): bool
    {
        return $field === 'trade_date' || substr($field, -5) === '_date';
    }

    private function isTimestampField(string $field): bool
    {
        return substr($field, -3) === '_at' || substr($field, -10) === '_timestamp';
    }

    private function isHashField(string $field): bool
    {
        return substr($field, -5) === '_hash';
    }

    private function isCanonicalDate(string $value): bool
    {
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        return $date !== false && $date->format('Y-m-d') === $value;
    }

    private function isCanonicalTimestamp(string $value): bool
    {
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $value);

        return $date !== false && $date->format('Y-m-d H:i:s') === $value;
    }

    private function canonicalJson($value, string $field): string
    {
        $canonical = $this->canonicalizeCollection($value, $field);
        $json = json_encode($canonical, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            throw new \RuntimeException('HASH_CANONICAL_JSON_SERIALIZATION_FAILED: '.$field);
        }

        return $json;
    }

    private function canonicalizeCollection($value, string $field)
    {
        if (is_object($value)) {
            $value = (array) $value;
        }
        if (! is_array($value)) {
            return $this->normalizeValue($value, $field);
        }

        $isList = $value === [] || array_keys($value) === range(0, count($value) - 1);
        $canonical = [];
        foreach ($value as $key => $child) {
            // List members inherit set membership, not the outer *_json parser. Associative
            // members retain their owned field name so numeric formats remain explicit.
            $canonical[$key] = $this->canonicalizeCollection($child, $isList ? '' : (string) $key);
        }
        if ($isList) {
            usort($canonical, function ($left, $right) {
                return strcmp(json_encode($left), json_encode($right));
            });
        } else {
            ksort($canonical, SORT_STRING);
        }

        return $canonical;
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

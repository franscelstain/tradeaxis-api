<?php

namespace App\Application\Watchlist\Services;

class WatchlistTradingStatusSnapshotNormalizer
{
    private const PRIMARY_PRIORITY = [
        'SUSPENDED',
        'SUSPENSION_OBSERVED',
        'UNSUSPENDED',
        'SPECIAL_MONITORING_START',
        'SPECIAL_MONITORING_END',
        'UMA',
    ];

    public static function normalize($value): ?string
    {
        $codes = self::canonicalCodes($value);
        if ($codes === []) {
            return null;
        }

        $codeSet = array_fill_keys($codes, true);
        foreach (self::PRIMARY_PRIORITY as $code) {
            if (isset($codeSet[$code])) {
                return $code;
            }
        }

        sort($codes, SORT_STRING);

        return $codes[0] ?? null;
    }

    private static function canonicalCodes($value): array
    {
        if ($value === null || ! is_scalar($value)) {
            return [];
        }

        $codes = [];
        foreach (preg_split('/[,;|]+/', (string) $value) as $rawCode) {
            $code = self::canonicalCode($rawCode);
            if ($code !== null) {
                $codes[$code] = true;
            }
        }

        return array_keys($codes);
    }

    private static function canonicalCode($value): ?string
    {
        $code = self::normalizeCode($value);
        if ($code === '') {
            return null;
        }

        if (in_array($code, self::PRIMARY_PRIORITY, true)) {
            return $code;
        }

        if ($code === 'UMA') {
            return 'UMA';
        }

        if (in_array($code, ['SPECIAL_MONITORING_END', 'SPECIAL_MONITORING_EXIT', 'SPECIAL_MONITORING_REMOVED', 'REMOVED_FROM_SPECIAL_MONITORING'], true)) {
            return 'SPECIAL_MONITORING_END';
        }

        if (in_array($code, ['SPECIAL_MONITORING', 'SPECIAL_MONITORING_START', 'WATCHLIST', 'SPECIAL_NOTATION', 'NOTASI_KHUSUS'], true)) {
            return 'SPECIAL_MONITORING_START';
        }

        if (strpos($code, 'UNSUSPEND') !== false
            || strpos($code, 'RESUME') !== false
            || in_array($code, ['ACTIVE', 'NORMAL', 'OPEN', 'REGULAR'], true)) {
            return 'UNSUSPENDED';
        }

        if (in_array($code, ['LONG_SUSPENSION', 'LONG_SUSPENSION_GT_6M', 'SUSPENSION_GT_6M', 'SUSPENDED_GT_6M', 'SUSPENSI_LEBIH_DARI_6_BULAN'], true)) {
            return 'SUSPENSION_OBSERVED';
        }

        if (strpos($code, 'SUSPEND') !== false || strpos($code, 'HALT') !== false) {
            return 'SUSPENDED';
        }

        return $code;
    }

    private static function normalizeCode($value): string
    {
        $code = strtoupper(trim((string) $value));
        $code = preg_replace('/[^A-Z0-9]+/', '_', $code);

        return trim((string) $code, '_');
    }
}

<?php

namespace App\Application\MarketData\Services;

final class CoverageGateStateNormalizer
{
    public const PASS = 'PASS';
    public const FAIL = 'FAIL';
    public const NOT_EVALUABLE = 'NOT_EVALUABLE';
    public const LEGACY_BLOCKED = 'BLOCKED';

    public static function normalize($state)
    {
        $normalized = strtoupper(trim((string) $state));

        if ($normalized === '') {
            return null;
        }

        if ($normalized === self::LEGACY_BLOCKED) {
            return self::NOT_EVALUABLE;
        }

        if (in_array($normalized, [self::PASS, self::FAIL, self::NOT_EVALUABLE], true)) {
            return $normalized;
        }

        return self::NOT_EVALUABLE;
    }

    public static function legacyRaw($state)
    {
        $raw = strtoupper(trim((string) $state));

        return $raw === self::LEGACY_BLOCKED ? self::LEGACY_BLOCKED : null;
    }
}

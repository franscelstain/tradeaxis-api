<?php

namespace App\Application\MarketData\Services;

final class ReplayMode
{
    public const PUBLICATION_EXACT = 'PUBLICATION_EXACT';
    public const AS_KNOWN = 'AS_KNOWN';

    public static function normalize($value): string
    {
        $mode = strtoupper(trim((string) $value));

        if ($mode === '') {
            throw new \RuntimeException(
                'REPLAY_MODE_REQUIRED: replay mode must be explicitly PUBLICATION_EXACT or AS_KNOWN.'
            );
        }

        if (! in_array($mode, [self::PUBLICATION_EXACT, self::AS_KNOWN], true)) {
            throw new \RuntimeException(
                'REPLAY_MODE_UNSUPPORTED: replay mode must be PUBLICATION_EXACT or AS_KNOWN.'
            );
        }

        return $mode;
    }

    public static function all(): array
    {
        return [self::PUBLICATION_EXACT, self::AS_KNOWN];
    }
}

<?php

namespace App\Domain\SecurityIdentity;

/** Technical registry encoding, not the Market Data artifact hash contract. */
final class RegistryDocument
{
    public static function json(array $value): string
    {
        return json_encode(self::sort($value), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    private static function sort(array $value): array
    {
        if ($value !== [] && array_keys($value) !== range(0, count($value) - 1)) {
            ksort($value, SORT_STRING);
        }
        foreach ($value as &$child) {
            if (is_array($child)) {
                $child = self::sort($child);
            }
        }
        return $value;
    }
}

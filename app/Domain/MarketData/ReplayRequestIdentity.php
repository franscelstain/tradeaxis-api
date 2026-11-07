<?php

namespace App\Domain\MarketData;

/** A target selector is a positive integer, never a credential or a lossy numeric cast. */
final class ReplayRequestIdentity
{
    public static function positiveInteger($value): ?int
    {
        if (!is_int($value) && !is_string($value)) return null;
        $parsed=filter_var($value,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
        return $parsed===false?null:$parsed;
    }
}

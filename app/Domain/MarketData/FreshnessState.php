<?php

namespace App\Domain\MarketData;

use DateTimeInterface;
use RuntimeException;

/**
 * Freshness vocabulary and the applicability rule of operational freshness.
 *
 * Authority: `Downstream_Data_Readiness_Guarantee_LOCKED.md` ("Freshness states", "Readability and operational
 * freshness are independent"), `Consumer_Readability_Decision_Table_LOCKED.md` row 1 and
 * `Downstream_Consumer_Read_Model_Contract_LOCKED.md`, as corrected by `DOC-CHG-20261005-001`
 * (owner decision `D-MD-B18-A002-015`).
 *
 * A `READABLE` publication whose requested trade date precedes the effective operational activation marker (or
 * for which no marker is effective) is `NOT_APPLICABLE`: operational freshness is not in force, no assessment is
 * made or claimed. It is not `FRESH`, `STALE` or `DEGRADED`, and it is not `NOT_AVAILABLE`, which keeps its
 * governed meaning "no consumer-safe result".
 *
 * Applicability is decided from the requested trade date and the marker alone. Nothing here reads a clock, so a
 * publication keeps one applicability wherever and whenever it is read, replayed or re-created.
 */
final class FreshnessState
{
    public const FRESH = 'FRESH';
    public const STALE = 'STALE';
    public const DEGRADED = 'DEGRADED';
    public const NOT_AVAILABLE = 'NOT_AVAILABLE';
    public const NOT_APPLICABLE = 'NOT_APPLICABLE';

    /** The freshness states a manifest, a semantic hash and a read model may carry. */
    public const VOCABULARY = [
        self::FRESH,
        self::STALE,
        self::DEGRADED,
        self::NOT_AVAILABLE,
        self::NOT_APPLICABLE,
    ];

    /**
     * The run label for "operational freshness is in force and has not been evaluated". It is an internal label, not a
     * member of the vocabulary: no freshness evaluator exists yet (F-MD-B18-A002-033, activated world), and a label
     * outside the vocabulary is canonicalised to NOT_AVAILABLE, never to FRESH.
     */
    public const PENDING_EVALUATION_LABEL = 'NOT_EVALUATED';

    /** @param mixed $value */
    public static function isState($value): bool
    {
        return is_string($value) && in_array($value, self::VOCABULARY, true);
    }

    /**
     * Whether operational freshness is in force for a requested trade date: only when an explicit governed marker is
     * effective on or before that date. It is never backdated.
     *
     * @param  mixed  $activationMarker  `Y-m-d` (a longer date-time string or a DateTimeInterface is cut to its date) or null/'' for none
     * @param  mixed  $requestedTradeDate  `Y-m-d`
     */
    public static function isInForce($activationMarker, $requestedTradeDate): bool
    {
        $marker = self::dateOrNull($activationMarker, 'MARKET_DATA_OPERATIONAL_START_DATE_INVALID');
        if ($marker === null) {
            return false;
        }
        $date = self::dateOrNull($requestedTradeDate, 'MARKET_DATA_REQUESTED_DATE_INVALID');
        if ($date === null) {
            throw new RuntimeException('MARKET_DATA_REQUESTED_DATE_INVALID: a requested trade date is required to decide whether operational freshness is in force.');
        }

        return $date >= $marker;
    }

    /**
     * The freshness label a run is created with for its requested trade date.
     *
     * @param  mixed  $activationMarker
     * @param  mixed  $requestedTradeDate
     */
    public static function runLabelFor($activationMarker, $requestedTradeDate): string
    {
        return self::isInForce($activationMarker, $requestedTradeDate)
            ? self::PENDING_EVALUATION_LABEL
            : self::NOT_APPLICABLE;
    }

    /** @param mixed $value */
    private static function dateOrNull($value, string $errorCode): ?string
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d');
        }
        if ($value === null || trim((string) $value) === '') {
            return null;
        }
        $text = trim((string) $value);
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})(?:[ T]\d{2}:\d{2}(?::\d{2}(?:\.\d+)?)?(?:Z|[+-]\d{2}:?\d{2})?)?$/', $text, $m) !== 1
            || ! checkdate((int) $m[2], (int) $m[3], (int) $m[1])) {
            throw new RuntimeException($errorCode.': '.$text);
        }

        return $m[1].'-'.$m[2].'-'.$m[3];
    }
}

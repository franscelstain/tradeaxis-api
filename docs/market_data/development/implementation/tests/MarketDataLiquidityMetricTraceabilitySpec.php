<?php

/**
 * The MD-B13 traceability partition.
 *
 * B13 is the first stage in this epoch to carry conditional rows through its own gate, so the
 * partition has three parts rather than two. `conditionalPending()` is not a rounding error to be
 * folded into either of the others: those rows block closure until their condition is evidenced,
 * and a spec that hid them would let the stage close on a denominator that never accounted for
 * them.
 */
final class MarketDataLiquidityMetricTraceabilitySpec
{
    public const STAGE = 'MD-B13';

    public const ATTEMPT = 'MD-B13-A001';

    public const EXPECTED_DENOMINATOR = 33;

    /**
     * Fourteen, not fifteen. `MD-S086-R0025` was reassigned to MD-B17 at this attempt: the retirement it
     * requires runs through a versioned read-model change that MD-B13 does not own, matching how the
     * identical clause for the `eligible` alias is already assigned.
     */
    public const EXPECTED_CONDITIONAL_PENDING = 14;

    public const EXPECTED_REFERENCE = 13;

    public static function matrixPath(string $root): string
    {
        return $root.'/docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';
    }

    public static function rows(string $root): array
    {
        $handle = fopen(self::matrixPath($root), 'r');
        if (! $handle) {
            throw new RuntimeException('Cannot open traceability matrix.');
        }
        $headers = fgetcsv($handle);
        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
        $rows = [];
        while (($values = fgetcsv($handle)) !== false) {
            if (count($values) === count($headers)) {
                $rows[] = array_combine($headers, $values);
            }
        }
        fclose($handle);

        return $rows;
    }

    public static function stageRows(string $root): array
    {
        return array_values(array_filter(self::rows($root), static function ($row) {
            return ($row['active'] ?? '') === 'YES' && ($row['primary_stage'] ?? '') === self::STAGE;
        }));
    }

    public static function mandatory(string $root): array
    {
        return array_values(array_filter(self::stageRows($root), static function ($row) {
            return ($row['coverage_requirement'] ?? '') === 'REQUIRED'
                && ($row['applicability'] ?? '') === 'MANDATORY';
        }));
    }

    public static function conditionalPending(string $root): array
    {
        return array_values(array_filter(self::stageRows($root), static function ($row) {
            return ($row['coverage_requirement'] ?? '') === 'REQUIRED'
                && ($row['applicability'] ?? '') === 'CONDITIONAL_PENDING';
        }));
    }

    public static function conditionalResolved(string $root): array
    {
        return array_values(array_filter(self::stageRows($root), static function ($row) {
            return ($row['coverage_requirement'] ?? '') === 'REQUIRED'
                && in_array($row['applicability'] ?? '', ['CONDITIONAL_APPLICABLE', 'CONDITIONAL_NOT_APPLICABLE'], true);
        }));
    }

    public static function reference(string $root): array
    {
        return array_values(array_filter(self::stageRows($root), static function ($row) {
            return ($row['coverage_requirement'] ?? '') === 'REFERENCE_ONLY';
        }));
    }

    public static function transitional(string $root): array
    {
        return array_values(array_filter(self::stageRows($root), static function ($row) {
            return ($row['applicability'] ?? '') === 'MANDATORY_OR_CONDITIONAL';
        }));
    }
}

<?php

/**
 * `MD-B19` traceability surface.
 *
 * Reads the required MD-B19 population straight from the matrix. The denominator is not written
 * here as a literal: `MarketDataOperationsProofSpec` declares what it expects and the gate compares
 * the two, so a matrix that quietly loses or gains a row is a gate failure rather than a silent
 * change of what the stage owes.
 */
final class MarketDataOperationsTraceabilitySpec
{
    public const STAGE = 'MD-B19';

    /** @return array<int,array<string,string>> active required rows owned by this stage */
    public static function required(string $root): array
    {
        return self::read($root, function (array $row) {
            return $row['coverage_requirement'] === 'REQUIRED'
                && in_array($row['applicability'], ['MANDATORY', 'CONDITIONAL_APPLICABLE'], true);
        });
    }

    /** @return array<int,array<string,string>> every active row owned by this stage */
    public static function stageRows(string $root): array
    {
        return self::read($root, function (array $row) {
            return true;
        });
    }

    /**
     * @param  callable(array<string,string>):bool  $keep
     * @return array<int,array<string,string>>
     */
    private static function read(string $root, callable $keep): array
    {
        $path = $root.'/docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';
        $handle = fopen($path, 'rb');
        if (! $handle) {
            throw new RuntimeException('TRACEABILITY_MATRIX_UNREADABLE');
        }
        $header = fgetcsv($handle);
        if (isset($header[0])) {
            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        }
        $rows = [];
        while (($values = fgetcsv($handle)) !== false) {
            if (count($values) !== count($header)) {
                continue;
            }
            $row = array_combine($header, $values);
            if ((isset($row['active']) ? $row['active'] : '') !== 'YES'
                || (isset($row['primary_stage']) ? $row['primary_stage'] : '') !== self::STAGE) {
                continue;
            }
            if (! $keep($row)) {
                continue;
            }
            $rows[] = $row;
        }
        fclose($handle);
        usort($rows, function ($a, $b) {
            return strcmp($a['rule_id'], $b['rule_id']);
        });

        return $rows;
    }
}

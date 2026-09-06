<?php

final class MarketDataReplayVerificationTraceabilitySpec
{
    public const STAGE = 'MD-B18';
    public const EXPECTED_DENOMINATOR = 121;

    public static function required(string $root): array
    {
        $path = $root.'/docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';
        $h = fopen($path, 'rb');
        if (! $h) { throw new RuntimeException('TRACEABILITY_MATRIX_UNREADABLE'); }
        $header = fgetcsv($h); if (isset($header[0])) { $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]); } $rows = [];
        while (($values = fgetcsv($h)) !== false) {
            if (count($values) !== count($header)) { continue; }
            $row = array_combine($header, $values);
            if (($row['active'] ?? '') !== 'YES' || ($row['primary_stage'] ?? '') !== self::STAGE) { continue; }
            if (! in_array($row['applicability'] ?? '', ['MANDATORY','CONDITIONAL_APPLICABLE'], true)) { continue; }
            $rows[] = $row;
        }
        fclose($h);
        usort($rows, fn($a,$b) => strcmp($a['rule_id'], $b['rule_id']));
        return $rows;
    }
}

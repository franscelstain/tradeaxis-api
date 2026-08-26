<?php
final class MarketDataAnalyticalPriceProductTraceabilitySpec
{
    public const STAGE='MD-B12';
    public const ATTEMPT='MD-B12-A001';
    public const EXPECTED_DENOMINATOR=45;
    public const EXPECTED_REFERENCE=78;
    public static function matrixPath(string $root): string { return $root.'/docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv'; }
    public static function rows(string $root): array {
        $h=fopen(self::matrixPath($root),'r'); if(!$h) throw new RuntimeException('Cannot open traceability matrix.');
        $headers=fgetcsv($h); $headers[0]=preg_replace('/^\xEF\xBB\xBF/','',$headers[0]); $rows=[];
        while(($r=fgetcsv($h))!==false){ if(count($r)===count($headers)) $rows[]=array_combine($headers,$r); }
        fclose($h); return $rows;
    }
    public static function stageRows(string $root): array { return array_values(array_filter(self::rows($root),static function($r){return ($r['active']??'')==='YES'&&($r['primary_stage']??'')===self::STAGE;})); }
    public static function mandatory(string $root): array { return array_values(array_filter(self::stageRows($root),static function($r){return ($r['coverage_requirement']??'')==='REQUIRED'&&($r['applicability']??'')==='MANDATORY';})); }
    public static function reference(string $root): array { return array_values(array_filter(self::stageRows($root),static function($r){return ($r['coverage_requirement']??'')==='REFERENCE_ONLY';})); }
}

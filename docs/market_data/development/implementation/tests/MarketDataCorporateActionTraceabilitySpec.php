<?php
final class MarketDataCorporateActionTraceabilitySpec
{
    public const STAGE='MD-B11'; public const ATTEMPT='MD-B11-A001'; public const EXPECTED_DENOMINATOR=138; public const EXPECTED_REFERENCE=273;
    public static function matrixPath(string $root): string { return $root.'/docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv'; }
    public static function rows(string $root): array { $h=fopen(self::matrixPath($root),'r'); if(!$h) throw new RuntimeException('Cannot open traceability matrix.'); $headers=fgetcsv($h); $headers[0]=preg_replace('/^\\xEF\\xBB\\xBF/','',$headers[0]); $rows=[]; while(($r=fgetcsv($h))!==false){ if(count($r)===count($headers)) $rows[]=array_combine($headers,$r); } fclose($h); return $rows; }
    public static function mandatory(string $root): array { return array_values(array_filter(self::rows($root),static function($r){return ($r['active']??'')==='YES'&&($r['primary_stage']??'')===self::STAGE&&($r['coverage_requirement']??'')==='REQUIRED'&&($r['applicability']??'')==='MANDATORY';})); }
}

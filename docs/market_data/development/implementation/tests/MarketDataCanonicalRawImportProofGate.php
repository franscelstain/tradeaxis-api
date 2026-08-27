<?php
require_once __DIR__.'/MarketDataCanonicalRawImportProofSpec.php';
final class MarketDataCanonicalRawImportProofGate
{
    public static function validate(string $root, bool $bound=false): array
    {
        $den=MarketDataCanonicalRawImportTraceabilitySpec::denominator($root); $map=MarketDataCanonicalRawImportProofSpec::map($root); $families=MarketDataCanonicalRawImportProofSpec::families(); $errors=[];
        if(count($den)!==MarketDataCanonicalRawImportTraceabilitySpec::EXPECTED_DENOMINATOR)$errors[]='mandatory denominator must be '.MarketDataCanonicalRawImportTraceabilitySpec::EXPECTED_DENOMINATOR;
        if(count($map)!==MarketDataCanonicalRawImportTraceabilitySpec::EXPECTED_DENOMINATOR)$errors[]='proof map must contain '.MarketDataCanonicalRawImportTraceabilitySpec::EXPECTED_DENOMINATOR.' rows';
        foreach($den as $r){
            $expected=$bound?'SATISFIED':'NOT_ASSESSED'; if($r['coverage_status']!==$expected)$errors[]=$r['rule_id'].' status='.$r['coverage_status'].' expected='.$expected;
            if(!isset($map[$r['rule_id']],$families[$map[$r['rule_id']]])){$errors[]='missing proof family '.$r['rule_id'];continue;}
            [$file,$method]=$families[$map[$r['rule_id']]]; $path=$root.'/'.$file; if(!is_file($path)){$errors[]='missing test '.$file;continue;}
            $src=file_get_contents($path); if(strpos($src,'function '.$method.'(')===false)$errors[]='missing proof method '.$method;
            if($bound && trim($r['current_evidence_ids'])==='')$errors[]='bound row lacks evidence '.$r['rule_id'];
        }
        $registry=file_get_contents($root.'/docs/market_data/authority/strategy/registry/Reason_Codes_Registry.md');
        $seed=file_get_contents($root.'/docs/market_data/development/implementation/db/registry/Reason_Codes_Seed.sql');
        $ingest=file_get_contents($root.'/app/Application/MarketData/Services/EodBarsIngestService.php');
        $adapter=file_get_contents($root.'/app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php');
        foreach([$registry,$seed,$ingest,$adapter] as $i=>$src){if(strpos($src,'BAR_ZERO_VOLUME_PRICE_MOVEMENT')===false)$errors[]='zero-volume reason missing surface '.$i;}
        if (strpos($registry, '| `BAR_ZERO_VOLUME_PRICE_MOVEMENT` | BAR | HARD |') === false) $errors[]='canonical registry metadata for zero-volume reason is not BAR/HARD';
        if (strpos($seed, "('BAR_ZERO_VOLUME_PRICE_MOVEMENT', 'BAR',") === false || strpos($seed, "'HARD', 1)") === false) $errors[]='reason seed metadata for zero-volume reason is not active BAR/HARD';
        if(strpos($ingest,"(int) \$row['volume'] === 0")===false || strpos($adapter,"(int) \$row['volume'] === 0")===false)$errors[]='zero-volume validator guard missing';
        if(strpos($ingest,"'BAR_INVALID_OHLC_ORDER'")===false || strpos($ingest,"'BAR_NEGATIVE_VOLUME'")===false)$errors[]='existing bar reason precedence surface missing';
        $importTest=file_get_contents($root.'/tests/Unit/MarketData/CanonicalRawImportBoundaryTest.php');
        foreach(['test_flat_positive_zero_volume_row_remains_canonical','test_zero_volume_with_price_movement_is_rejected_with_dedicated_reason','test_zero_volume_rule_does_not_steal_invalid_ohlc_reason_precedence','test_import_creates_a_candidate_without_sealing_or_switching_the_pointer'] as $m){if(strpos($importTest,'function '.$m.'(')===false)$errors[]='missing B09 test '.$m;}
        return ['status'=>$errors===[]?'PASS':'FAIL','denominator'=>count($den),'proof_map'=>count($map),'bound'=>$bound,'errors'=>$errors];
    }
}
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $root = dirname(__DIR__, 5);
    $denominator = MarketDataCanonicalRawImportTraceabilitySpec::denominator($root);
    $allBound = $denominator !== [];
    foreach ($denominator as $row) {
        if (($row['coverage_status'] ?? '') !== 'SATISFIED') {
            $allBound = false;
            break;
        }
    }
    $bound = in_array('--bound', $argv, true) || $allBound;
    $r = MarketDataCanonicalRawImportProofGate::validate($root, $bound);
    echo json_encode($r, JSON_PRETTY_PRINT).PHP_EOL;
    exit($r['status'] === 'PASS' ? 0 : 1);
}

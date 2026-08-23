<?php
require_once __DIR__.'/MarketDataCanonicalRawImportProofGate.php';
function cpy($src,$dst){if(is_dir($src)){@mkdir($dst,0777,true);foreach(scandir($src) as $x){if($x==='.'||$x==='..')continue;cpy($src.'/'.$x,$dst.'/'.$x);}}else{@mkdir(dirname($dst),0777,true);copy($src,$dst);}}
$root=dirname(__DIR__,5);$needed=['docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv','docs/market_data/authority/strategy/registry/Reason_Codes_Registry.md','docs/market_data/development/implementation/db/registry/Reason_Codes_Seed.sql','app/Application/MarketData/Services/EodBarsIngestService.php','app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php','tests/Unit/MarketData/CanonicalRawImportBoundaryTest.php','tests/Unit/MarketData/EodBarsIngestServiceTest.php','tests/Unit/MarketData/ConfigIdentityBindingTest.php','tests/Unit/MarketData/SourceObservationImmutabilityTest.php','tests/Unit/MarketData/MarketDataOrdersOneToFourArchitectureTest.php'];
$denominator=MarketDataCanonicalRawImportTraceabilitySpec::denominator($root);$bound=$denominator!==[];foreach($denominator as $row){if(($row['coverage_status'] ?? '')!=='SATISFIED'){$bound=false;break;}}$checks=[];$checks['control']=MarketDataCanonicalRawImportProofGate::validate($root,$bound)['status']==='PASS';
$mutations=[
 'missing_registry_code'=>['docs/market_data/authority/strategy/registry/Reason_Codes_Registry.md','BAR_ZERO_VOLUME_PRICE_MOVEMENT','BAR_ZERO_VOLUME_MOVEMENT_REMOVED'],
 'missing_seed_code'=>['docs/market_data/development/implementation/db/registry/Reason_Codes_Seed.sql','BAR_ZERO_VOLUME_PRICE_MOVEMENT','BAR_ZERO_VOLUME_MOVEMENT_REMOVED'],
 'missing_ingest_guard'=>['app/Application/MarketData/Services/EodBarsIngestService.php','BAR_ZERO_VOLUME_PRICE_MOVEMENT','BAR_ZERO_VOLUME_MOVEMENT_REMOVED'],
 'missing_provider_guard'=>['app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php','BAR_ZERO_VOLUME_PRICE_MOVEMENT','BAR_ZERO_VOLUME_MOVEMENT_REMOVED'],
 'wrong_ingest_volume_guard'=>['app/Application/MarketData/Services/EodBarsIngestService.php','(int) $row[\'volume\'] === 0','(int) $row[\'volume\'] < 0'],
 'missing_negative_test'=>['tests/Unit/MarketData/CanonicalRawImportBoundaryTest.php','function test_zero_volume_with_price_movement_is_rejected_with_dedicated_reason(','function removed_zero_volume_test('],
 'missing_flat_zero_volume_test'=>['tests/Unit/MarketData/CanonicalRawImportBoundaryTest.php','function test_flat_positive_zero_volume_row_remains_canonical(','function removed_flat_zero_volume_test('],
 'missing_reason_precedence_test'=>['tests/Unit/MarketData/CanonicalRawImportBoundaryTest.php','function test_zero_volume_rule_does_not_steal_invalid_ohlc_reason_precedence(','function removed_reason_precedence_test('],
];
foreach($mutations as $name=>$m){$tmp=sys_get_temp_dir().'/md_b09_'.uniqid();foreach($needed as $f)cpy($root.'/'.$f,$tmp.'/'.$f);$p=$tmp.'/'.$m[0];file_put_contents($p,str_replace($m[1],$m[2],file_get_contents($p)));$checks[$name]=MarketDataCanonicalRawImportProofGate::validate($tmp,$bound)['status']==='FAIL';}
$ok=!in_array(false,$checks,true);echo json_encode(['status'=>$ok?'PASS':'FAIL','checks'=>$checks],JSON_PRETTY_PRINT).PHP_EOL;exit($ok?0:1);

<?php
require_once __DIR__.'/MarketDataReplayVerificationTraceabilitySpec.php';
$root=dirname(__DIR__,5);$rows=MarketDataReplayVerificationTraceabilitySpec::required($root);$all=[];$h=fopen($root.'/docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv','rb');$head=fgetcsv($h);while(($v=fgetcsv($h))!==false){$r=array_combine($head,$v);if(($r['active']??'')==='YES'&&($r['primary_stage']??'')==='MD-B18')$all[]=$r;}fclose($h);
$c=array_count_values(array_column($all,'applicability'));$errors=[];
foreach($all as $r)if(in_array($r['applicability'],['MANDATORY_OR_CONDITIONAL','CONDITIONAL_PENDING'],true))$errors[]='UNNORMALIZED:'.$r['rule_id'];
if(count($rows)!==121)$errors[]='DENOMINATOR_MISMATCH:'.count($rows);
if(($c['MANDATORY']??0)!==117)$errors[]='MANDATORY_MISMATCH';if(($c['CONDITIONAL_APPLICABLE']??0)!==4)$errors[]='CONDITIONAL_MISMATCH';if(($c['REFERENCE_ONLY']??0)!==32)$errors[]='REFERENCE_MISMATCH';if(($c['OPTIONAL_CAPABILITY']??0)!==2)$errors[]='OPTIONAL_MISMATCH';
$result=['gate'=>'MarketDataReplayVerificationNormalization','stage_id'=>'MD-B18','status'=>$errors?'FAIL':'PASS','counts'=>$c,'denominator'=>count($rows),'errors'=>$errors,'generated_at'=>date(DATE_ATOM)];echo json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL;exit($result['status']==='PASS'?0:1);

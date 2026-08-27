<?php
require_once __DIR__.'/MarketDataAnalyticalPriceProductProofGate.php';
$root=dirname(__DIR__,5);$validate=in_array('--validate-only',$argv,true);$apply=in_array('--apply',$argv,true);$evidence=null;foreach($argv as $a)if(strpos($a,'--evidence-id=')===0)$evidence=substr($a,14);
if($validate){$r=MarketDataAnalyticalPriceProductProofGate::validate($root,false);echo json_encode(['status'=>$r['status'],'mode'=>'VALIDATE_ONLY','denominator'=>$r['denominator'],'proof_map_count'=>$r['proof_map_count'],'proof_families_used'=>$r['proof_families_used'],'runtime_pending'=>$r['runtime_pending'],'errors'=>$r['errors']],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL;exit($r['status']==='PASS'?0:1);}
if(!$evidence||!preg_match('/^E-MD-B12-A00[12]-\d{3}$/',$evidence))throw new RuntimeException('Use --validate-only or --evidence-id=E-MD-B12-A00[1|2]-NNN [--apply].');
$matches=glob($root.'/docs/market_data/records/evidence/'.$evidence.'_*');if(count($matches)!==1)throw new RuntimeException('Exactly one governed B12 evidence file required.');
$path=MarketDataAnalyticalPriceProductTraceabilitySpec::matrixPath($root);$h=fopen($path,'r');$headers=fgetcsv($h);$bom=strpos($headers[0],"\xEF\xBB\xBF")===0;$headers[0]=preg_replace('/^\xEF\xBB\xBF/','',$headers[0]);$rows=[];while(($r=fgetcsv($h))!==false)if(count($r)===count($headers))$rows[]=array_combine($headers,$r);fclose($h);
$ids=array_flip(array_column(MarketDataAnalyticalPriceProductProofSpec::entries($root),'rule_id'));
// A predicate first bound by the A002 re-check carries the A002 pair; the 45 A001 bindings are left
// exactly as issued. Requiring the whole denominator to be pristine would have forced this attempt
// to unbind a closed stage to rebind it, which is how MD-B07-A002 lost 30 predicates.
$boundToThisAttempt=array_keys(MarketDataAnalyticalPriceProductTraceabilitySpec::REMEDIATED_RULES);
$isA002=preg_match('/^E-MD-B12-A002-/',$evidence)===1;
$n=0;$bound=0;$boundMandatory=[];
foreach($rows as &$r){
    if(!isset($ids[$r['rule_id']]))continue;
    $n++;
    $targetsThisAttempt=$isA002===in_array($r['rule_id'],$boundToThisAttempt,true);
    if(!$targetsThisAttempt){$boundMandatory[]=$r;continue;}
    if($r['coverage_status']!=='NOT_ASSESSED'||trim($r['current_evidence_ids'])!=='')throw new RuntimeException('Non-pristine predicate '.$r['rule_id']);
    $r['coverage_status']='SATISFIED';$r['current_evidence_ids']=$evidence;$bound++;$boundMandatory[]=$r;
}unset($r);
$expectedBound=$isA002?count($boundToThisAttempt):MarketDataAnalyticalPriceProductTraceabilitySpec::EXPECTED_DENOMINATOR-count($boundToThisAttempt);
if($n!==MarketDataAnalyticalPriceProductTraceabilitySpec::EXPECTED_DENOMINATOR)throw new RuntimeException('Binder denominator mismatch '.$n);
if($bound!==$expectedBound)throw new RuntimeException('Binder count mismatch: bound '.$bound.' expected '.$expectedBound);
$check=MarketDataAnalyticalPriceProductProofGate::validate($root,true,['mandatory'=>$boundMandatory]);if($check['status']!=='PASS')throw new RuntimeException('Bound validation failed: '.implode(';',$check['errors']));echo json_encode(['status'=>'PASS','denominator'=>$n,'bound_by_this_attempt'=>$bound,'evidence_id'=>$evidence,'apply'=>$apply],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL;if(!$apply)exit(0);
$h=fopen($path,'w');$oh=$headers;if($bom)$oh[0]="\xEF\xBB\xBF".$oh[0];fputcsv($h,$oh);foreach($rows as $r)fputcsv($h,array_map(static function($k)use($r){return $r[$k];},$headers));fclose($h);

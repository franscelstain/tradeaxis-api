<?php
require_once __DIR__.'/MarketDataReplayVerificationProofGate.php';
$root=dirname(__DIR__,5); $base=MarketDataReplayVerificationTraceabilitySpec::required($root); $entries=MarketDataReplayVerificationProofSpec::entries($root); $families=MarketDataReplayVerificationProofSpec::families();
$tests=[];
$run=function($name,$required,$e,$f,$expectPass)use(&$tests,$root){$r=MarketDataReplayVerificationProofGate::validate($root,false,['required'=>$required,'entries'=>$e,'families'=>$f]);$ok=($r['status']==='PASS')===$expectPass;$tests[]=['name'=>$name,'passed'=>$ok,'observed'=>$r['status'],'errors'=>$r['errors']];};
$run('baseline',$base,$entries,$families,true);
$x=$base; array_pop($x); $run('denominator_missing',$x,$entries,$families,false);
$x=$base; $x[0]['coverage_status']='SATISFIED';$x[0]['current_evidence_ids']='E-MD-B18-A001-999';$run('premature_satisfied',$x,$entries,$families,false);
$x=$entries;$x[0]['family']='not_a_family';$run('wrong_family',$base,$x,$families,false);
$x=$entries;$x[]=$x[0];$run('duplicate_entry',$base,$x,$families,false);
$x=$families;unset($x[array_key_first($x)]);$run('missing_family',$base,$entries,$x,false);
$result=['gate'=>'MarketDataReplayVerificationProofSelfTest','status'=>count(array_filter($tests,fn($t)=>!$t['passed']))===0?'PASS':'FAIL','tests'=>$tests,'generated_at'=>date(DATE_ATOM)];echo json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL;exit($result['status']==='PASS'?0:1);

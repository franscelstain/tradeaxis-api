<?php
require_once __DIR__.'/MarketDataCorporateActionProofGate.php';
$root=dirname(__DIR__,5);
$entries=MarketDataCorporateActionProofSpec::entries($root);
$families=MarketDataCorporateActionProofSpec::families();
$rows=MarketDataCorporateActionTraceabilitySpec::rows($root);
$mandatory=MarketDataCorporateActionTraceabilitySpec::mandatory($root);
$bound=$mandatory!==[];
foreach($mandatory as $r){if(($r['coverage_status']??'')!=='SATISFIED'||!preg_match('/^E-MD-B11-A\\d{3}-\\d{3}$/',trim((string)($r['current_evidence_ids']??'')))){$bound=false;break;}}
$checks=[];
$checks[$bound?'control_bound':'control_prebinding']=MarketDataCorporateActionProofGate::validate($root,$bound)['status']==='PASS';
function b11fails($root,$overrides,$bound){return MarketDataCorporateActionProofGate::validate($root,$bound,$overrides)['status']==='FAIL';}
$m=$entries;array_pop($m);$checks['missing_mapping']=b11fails($root,['entries'=>$m],$bound);
$m=$entries;$m[0]['family']='anomaly_only_detector';$checks['wrong_family']=b11fails($root,['entries'=>$m],$bound);
$f=$families;unset($f['verified_event_lifecycle']);$checks['missing_family']=b11fails($root,['families'=>$f],$bound);
$r=$rows;foreach($r as &$row){if(($row['primary_stage']??'')==='MD-B11'&&($row['coverage_requirement']??'')==='REQUIRED'&&($row['applicability']??'')==='MANDATORY'){$row['coverage_status']='SATISFIED';$row['current_evidence_ids']='';break;}}unset($row);$checks['satisfied_without_evidence']=b11fails($root,['rows'=>$r],$bound);
$failed=array_keys(array_filter($checks,static function($v){return !$v;}));$out=['status'=>$failed===[]?'PASS':'FAIL','mode'=>$bound?'BOUND_CLOSURE':'PRE_RUNTIME','total'=>count($checks),'checks'=>$checks,'failed_checks'=>$failed];echo json_encode($out,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL;exit($out['status']==='PASS'?0:1);

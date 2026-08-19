<?php
/** Register one explicit cross-attempt/cross-stage/cross-baseline relationship. PHP 7.3+. */
function opt($n,$d=null){global $argv;$p='--'.$n.'=';foreach($argv as $a)if(strpos($a,$p)===0)return substr($a,strlen($p));return $d;}
function failr($m){fwrite(STDERR,$m.PHP_EOL);exit(2);} 
function csvAssoc($path){$out=array();$fh=fopen($path,'r');$h=fgetcsv($fh);if(isset($h[0]))$h[0]=preg_replace('/^\xEF\xBB\xBF/','',$h[0]);while(($row=fgetcsv($fh))!==false){$a=array();foreach($h as $i=>$k)$a[$k]=isset($row[$i])?trim($row[$i]):'';$out[]=$a;}fclose($fh);return $out;}
$id=opt('id');$src=opt('source');$tgt=opt('target');$type=opt('type');$why=opt('justification');$decision=opt('decision','');$notes=opt('notes','');
if(!$id||!$src||!$tgt||!$type||!$why)failr('Required: --id --source --target --type --justification [--decision --notes]');
$allowed=array('INHERITED_FINDING','PRIOR_EVIDENCE','PRIOR_DECISION','PREDECESSOR_ATTEMPT','SUCCESSOR_ORIGIN','SUPERSEDES_CROSS_ATTEMPT','CROSS_BASELINE_CLOSURE_EVIDENCE');
if(!in_array($type,$allowed,true))failr('Unsupported relationship type.');
$wr=realpath(dirname(__DIR__,3));$reg=$wr.'/records/WORK_RECORD_REGISTRY.csv';$rel=$wr.'/records/WORK_RELATIONSHIP_REGISTRY.csv';
if(!is_file($reg)||!is_file($rel))failr('Registry missing.');
$records=array();foreach(csvAssoc($reg) as $r)$records[$r['record_id']]=$r;
if(!isset($records[$src]))failr('Source record not registered: '.$src);if(!isset($records[$tgt]))failr('Target record not registered: '.$tgt);
if($records[$src]['attempt_id']===$records[$tgt]['attempt_id']&&$records[$src]['stage_id']===$records[$tgt]['stage_id'])failr('Explicit cross-scope relationship is not needed for same attempt/stage.');
if($type==='CROSS_BASELINE_CLOSURE_EVIDENCE'){
    if($records[$src]['record_type']!=='STAGE_CLOSURE_MANIFEST')failr('CROSS_BASELINE_CLOSURE_EVIDENCE source must be STAGE_CLOSURE_MANIFEST.');
    if($decision===''||!isset($records[$decision])||$records[$decision]['record_type']!=='DECISION')failr('Valid reviewed DECISION required.');
    if($records[$src]['baseline_id']===$records[$tgt]['baseline_id'])failr('Cross-baseline relationship requires different baseline IDs.');
}
foreach(csvAssoc($rel) as $r){if($r['relationship_id']===$id)failr('Duplicate Relationship ID: '.$id);if($r['source_record_id']===$src&&$r['target_record_id']===$tgt&&$r['relationship_type']===$type)failr('Duplicate relationship tuple.');}
$fh=fopen($rel,'a');fputcsv($fh,array($id,$src,$tgt,$type,$why,$decision,date(DATE_ATOM),$notes));fclose($fh);
echo 'REGISTERED_RELATIONSHIP '.$id.' '.$src.' -> '.$tgt.PHP_EOL;

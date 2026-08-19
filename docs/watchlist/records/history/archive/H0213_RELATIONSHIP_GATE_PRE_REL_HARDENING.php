<?php
/** WatchlistRelationshipIntegrityGate.php — PHP 7.3+, no external deps. */
function csvAssoc($path) {
    $out=array(); if(!is_file($path)) return $out;
    $fh=fopen($path,'r'); $h=fgetcsv($fh); if(!$h){fclose($fh);return $out;}
    if(isset($h[0])) $h[0]=preg_replace('/^\xEF\xBB\xBF/','',$h[0]);
    while(($row=fgetcsv($fh))!==false){$a=array();foreach($h as $i=>$k)$a[$k]=isset($row[$i])?trim($row[$i]):'';$out[]=$a;}
    fclose($fh); return $out;
}
function splitIds($s){$s=trim($s);if($s===''||$s==='—'||$s==='N/A')return array();$a=preg_split('/[;|,]+/',$s);return array_values(array_filter(array_map('trim',$a)));}
function failx(&$errs,$code,$msg,$ctx=array()){$errs[]=array('code'=>$code,'message'=>$msg,'context'=>$ctx);}
function cleanCell($s){ return trim(str_replace('`','',$s)); }
function parseStageTable($path){
    $lines=file($path,FILE_IGNORE_NEW_LINES); $headers=null; $rows=array(); $in=false;
    foreach($lines as $line){
        if(strpos($line,'| Stage | Maps to | Lifecycle state |')===0){$headers=array_map('cleanCell',array_map('trim',explode('|',trim($line,'|'))));$in=true;continue;}
        if($in && preg_match('/^\|---/',$line)) continue;
        if($in && strpos($line,'| `WS-B')===0){$cells=array_map('cleanCell',array_map('trim',explode('|',trim($line,'|'))));$r=array();foreach($headers as $i=>$h)$r[$h]=isset($cells[$i])?$cells[$i]:'';$rows[]=$r;continue;}
        if($in && trim($line)==='') break;
    }
    return $rows;
}
$watchlistRoot=realpath(dirname(__DIR__,3)); $repoRoot=realpath(dirname($watchlistRoot,2));
$registry=$watchlistRoot.'/records/WORK_RECORD_REGISTRY.csv';
$deps=$watchlistRoot.'/development/implementation/WS_DEPENDENCY_REGISTRY.csv';
$stageReg=$watchlistRoot.'/development/implementation/WS_IMPLEMENTATION_STAGE_REGISTER.md';
$matrix=$watchlistRoot.'/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';
$errs=array(); $recordRows=csvAssoc($registry); $depRows=csvAssoc($deps); $stageRows=parseStageTable($stageReg); $records=array(); $stages=array();
foreach($stageRows as $r)$stages[$r['Stage']]=$r;
foreach($recordRows as $i=>$r){
    $id=$r['record_id']; if($id===''){failx($errs,'EMPTY_RECORD_ID','registry row has empty record_id',array('row'=>$i+2));continue;}
    if(isset($records[$id])) failx($errs,'DUP_RECORD_ID','duplicate record id',array('id'=>$id)); $records[$id]=$r;
    $stage=$r['stage_id']; $attempt=$r['attempt_id']; $work=$r['work_id'];
    if($stage!==''&&!isset($stages[$stage])) failx($errs,'UNKNOWN_STAGE','record references unknown stage',array('record'=>$id,'stage'=>$stage));
    if($attempt!==''&&$attempt!=='—'){
        if(!preg_match('/^(WS-B\d{2}[A-Z]?)-A\d{3}$/',$attempt,$m)) failx($errs,'BAD_ATTEMPT_ID','invalid attempt format',array('record'=>$id,'attempt'=>$attempt));
        elseif($stage!==''&&$m[1]!==$stage) failx($errs,'ATTEMPT_STAGE_MISMATCH','attempt prefix != stage',array('record'=>$id));
        if($work!==$attempt) failx($errs,'WORK_ID_MISMATCH','work_id must equal attempt_id',array('record'=>$id,'work'=>$work,'attempt'=>$attempt));
    }
    $fp=$repoRoot.'/'.str_replace('\\','/',$r['file_path']); if($r['file_path']!==''&&!is_file($fp)) failx($errs,'MISSING_RECORD_FILE','registry file path missing',array('record'=>$id,'path'=>$r['file_path']));
}
foreach($recordRows as $r){
    $id=$r['record_id'];
    foreach(array('related_finding_ids','related_evidence_ids','related_decision_ids') as $col) foreach(splitIds($r[$col]) as $rid){
        if(strpos($rid,'LEGACY:')===0)continue; if(!isset($records[$rid])) failx($errs,'DANGLING_RECORD_REF','current registry relationship target missing',array('record'=>$id,'column'=>$col,'target'=>$rid));
    }
    foreach(splitIds($r['supersedes']) as $rid) if(!isset($records[$rid])) failx($errs,'DANGLING_SUPERSEDES','superseded current record missing',array('record'=>$id,'target'=>$rid));
}
// supersedes cycle
foreach($records as $id=>$r){$seen=array($id=>true);$stack=splitIds($r['supersedes']);while($stack){$x=array_pop($stack);if(isset($seen[$x])){failx($errs,'SUPERSEDES_CYCLE','circular supersedes chain',array('record'=>$id,'at'=>$x));break;}$seen[$x]=true;if(isset($records[$x]))foreach(splitIds($records[$x]['supersedes']) as $y)$stack[]=$y;}}
$depIds=array();foreach($depRows as $i=>$d){
    $id=$d['dependency_id'];if($id===''){failx($errs,'EMPTY_DEP_ID','dependency row empty id',array('row'=>$i+2));continue;}
    if(isset($depIds[$id]))failx($errs,'DUP_DEP_ID','duplicate dependency id',array('id'=>$id));$depIds[$id]=$d;
    if(!preg_match('/^DEP-WS-\d{3}$/',$id))failx($errs,'BAD_DEP_ID','invalid dependency id',array('id'=>$id));
    if($d['consumer_stage']!==''&&!isset($stages[$d['consumer_stage']]))failx($errs,'DEP_UNKNOWN_STAGE','dependency consumer stage unknown',array('id'=>$id,'stage'=>$d['consumer_stage']));
    if($d['status']==='WAITING_VERIFIED_DEPENDENCY'&&trim($d['resume_trigger'])==='')failx($errs,'DEP_NO_RESUME_TRIGGER','waiting dependency missing resume trigger',array('id'=>$id));
}
foreach($recordRows as $r)foreach(splitIds($r['related_dependency_ids']) as $did)if(!isset($depIds[$did]))failx($errs,'DANGLING_DEP_REF','record dependency target missing',array('record'=>$r['record_id'],'dependency'=>$did));

// Stage-register relationships.
$terminal=array('DONE','CLOSED_UNRESOLVED_WITH_EVIDENCE','SUPERSEDED_BY_SUCCESSOR','SUPERSEDED_BY_DECOMPOSITION');
foreach($stageRows as $r){
    $stage=$r['Stage']; $state=$r['Lifecycle state']; $attempt=$r['Latest attempt / Work ID']; $baseline=$r['Baseline ID']; $impact=$r['Change impact']; $dep=$r['Dependency ID']; $closure=$r['Closure manifest'];
    if($attempt!==''&&$attempt!=='—'){
        if(!preg_match('/^'.preg_quote($stage,'/').'-A\d{3}$/',$attempt)) failx($errs,'STAGE_BAD_LATEST_ATTEMPT','stage register latest attempt invalid',array('stage'=>$stage,'attempt'=>$attempt));
        $matched=false;$baselineMatched=false;
        foreach($recordRows as $wr){if($wr['attempt_id']===$attempt){$matched=true;if($wr['record_type']==='WORK_BASELINE_LOCK'&&$wr['baseline_id']===$baseline)$baselineMatched=true;}}
        if(!$matched) failx($errs,'STAGE_ATTEMPT_NOT_REGISTERED','latest attempt has no current registry records',array('stage'=>$stage,'attempt'=>$attempt));
        if($baseline===''||$baseline==='—') failx($errs,'STAGE_ATTEMPT_NO_BASELINE','latest attempt missing baseline id',array('stage'=>$stage));
        elseif(!$baselineMatched) failx($errs,'STAGE_BASELINE_NOT_REGISTERED','latest baseline not registered/bound to attempt',array('stage'=>$stage,'baseline'=>$baseline));
    }
    if($impact!==''&&$impact!=='—'&&!isset($records[$impact])) failx($errs,'STAGE_CHANGE_IMPACT_NOT_REGISTERED','stage change-impact record missing',array('stage'=>$stage,'record'=>$impact));
    if($dep!==''&&$dep!=='—'&&!isset($depIds[$dep])) failx($errs,'STAGE_DEP_NOT_REGISTERED','stage dependency id missing',array('stage'=>$stage,'dependency'=>$dep));
    if($state==='WAITING_VERIFIED_DEPENDENCY'&&($dep===''||$dep==='—')) failx($errs,'WAITING_STAGE_NO_DEP_ID','waiting stage must reference dependency registry',array('stage'=>$stage));
    if(in_array($state,$terminal,true)){
        if($closure===''||$closure==='—') failx($errs,'TERMINAL_STAGE_NO_CLOSURE','terminal stage missing closure manifest',array('stage'=>$stage,'state'=>$state));
        elseif(!isset($records[$closure])||$records[$closure]['record_type']!=='STAGE_CLOSURE_MANIFEST') failx($errs,'BAD_CLOSURE_RECORD','closure manifest not registered with correct type',array('stage'=>$stage,'record'=>$closure));
    } elseif($closure!==''&&$closure!=='—') failx($errs,'ACTIVE_STAGE_HAS_FINAL_CLOSURE','active stage must not point final closure manifest',array('stage'=>$stage,'record'=>$closure));
}

// SATISFIED matrix rows require concrete refs; current correlation-style evidence must be registry-backed.
$rows=csvAssoc($matrix);foreach($rows as $r){if($r['coverage_status']==='SATISFIED'){
    foreach(array('implementation_ref','test_ref','evidence_ref') as $c)if(trim($r[$c])===''||trim($r[$c])==='N/A')failx($errs,'SATISFIED_MISSING_REF','SATISFIED rule missing '.$c,array('rule'=>$r['rule_id']));
    $e=trim($r['evidence_ref']); if(preg_match('/^(E|SC)-WS-B/',$e)&&!isset($records[$e]))failx($errs,'SATISFIED_EVIDENCE_NOT_REGISTERED','current evidence id not in Work Record Registry',array('rule'=>$r['rule_id'],'evidence'=>$e));
}}
$report=array('status'=>count($errs)?'FAIL':'PASS','generated_at'=>date(DATE_ATOM),'stage_rows'=>count($stageRows),'work_record_rows'=>count($recordRows),'dependency_rows'=>count($depRows),'errors'=>$errs);
$out=null;foreach($argv as $arg)if(strpos($arg,'--output=')===0)$out=substr($arg,9);if($out){if(!preg_match('~^(?:[A-Za-z]:[\\/]|/)~',$out))$out=$repoRoot.'/'.str_replace('\\','/',$out);if(!is_dir(dirname($out)))mkdir(dirname($out),0777,true);file_put_contents($out,json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL);}
echo json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL; exit(count($errs)?1:0);

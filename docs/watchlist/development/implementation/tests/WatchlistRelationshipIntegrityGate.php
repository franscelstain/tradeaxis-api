<?php
/**
 * WatchlistRelationshipIntegrityGate.php — PHP 7.3+, no external deps.
 *
 * Hard invariants for current/future WS-Bxx work:
 * 1) one Attempt ID => one Stage + one Baseline ID + one baseline-lock record;
 * 2) unique Record ID;
 * 3) referenced Stage exists;
 * 4) every current record baseline exists and is bound to the same Attempt;
 * 5) Related Finding points to FINDING;
 * 6) Related Decision points to DECISION;
 * 7) Supersedes graph is acyclic;
 * 8) closure-critical evidence is baseline-consistent, or has explicit reviewed cross-baseline authorization;
 * 9) cross-attempt/cross-stage relationships are explicit and justified in WORK_RELATIONSHIP_REGISTRY.csv.
 */
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
function isBlank($v){$v=trim($v);return $v===''||$v==='—'||$v==='N/A';}
function evidenceLikeType($t){return in_array($t,array('EVIDENCE','WORK_BASELINE_LOCK','STAGE_ATTEMPT_RECORD','CHANGE_IMPACT_DECLARATION'),true);}
function recordPrefixValid($id,$type,$attempt){
    $qa=preg_quote($attempt,'/');
    if($type==='FINDING') return preg_match('/^F-'.$qa.'-\d{3}$/',$id)===1;
    if($type==='DECISION') return preg_match('/^D-'.$qa.'-\d{3}$/',$id)===1;
    if($type==='CHANGE_IMPACT_DECLARATION') return preg_match('/^CI-'.$qa.'-\d{3}$/',$id)===1;
    if($type==='STAGE_CLOSURE_MANIFEST') return preg_match('/^SC-'.$qa.'-\d{3}$/',$id)===1;
    if(in_array($type,array('EVIDENCE','WORK_BASELINE_LOCK','STAGE_ATTEMPT_RECORD'),true)) return preg_match('/^E-'.$qa.'-\d{3}$/',$id)===1;
    return true;
}
function findRelation($relationRows,$source,$target,$allowedTypes){
    foreach($relationRows as $rel){
        if($rel['source_record_id']===$source && $rel['target_record_id']===$target && in_array($rel['relationship_type'],$allowedTypes,true)) return $rel;
    }
    return null;
}
function parseClosureManifest($path){
    $out=array('record_id'=>'','stage_id'=>'','attempt_id'=>'','work_id'=>'','baseline_id'=>'','attempt_record'=>array(),'evidence'=>array(),'findings'=>array(),'decisions'=>array());
    if(!is_file($path) || strtolower(pathinfo($path,PATHINFO_EXTENSION))!=='md') return $out;
    $lines=file($path,FILE_IGNORE_NEW_LINES);
    foreach($lines as $line){
        if(preg_match('/^- Closure Record ID:\s*`?([^`]+?)`?\s*$/',$line,$m))$out['record_id']=trim($m[1]);
        elseif(preg_match('/^- Stage ID:\s*`?([^`]+?)`?\s*$/',$line,$m))$out['stage_id']=trim($m[1]);
        elseif(preg_match('/^- Final Attempt ID:\s*`?([^`]+?)`?\s*$/',$line,$m))$out['attempt_id']=trim($m[1]);
        elseif(preg_match('/^- Work ID:\s*(?:\*\([^)]*\)\*\s*)?`?([^`]+?)`?\s*$/',$line,$m))$out['work_id']=trim($m[1]);
        elseif(preg_match('/^- Baseline ID:\s*`?([^`]+?)`?\s*$/',$line,$m))$out['baseline_id']=trim($m[1]);
        elseif(preg_match('/^- Attempt record:\s*(.*)$/',$line,$m))$out['attempt_record']=splitIds(str_replace('`','',trim($m[1])));
        elseif(preg_match('/^- Evidence:\s*(.*)$/',$line,$m))$out['evidence']=splitIds(str_replace('`','',trim($m[1])));
        elseif(preg_match('/^- Findings:\s*(.*)$/',$line,$m))$out['findings']=splitIds(str_replace('`','',trim($m[1])));
        elseif(preg_match('/^- Decisions:\s*(.*)$/',$line,$m))$out['decisions']=splitIds(str_replace('`','',trim($m[1])));
    }
    return $out;
}
function setsEqual($a,$b){sort($a);sort($b);return $a===$b;}

$watchlistRoot=realpath(dirname(__DIR__,3)); $repoRoot=realpath(dirname($watchlistRoot,2));
$registry=$watchlistRoot.'/records/WORK_RECORD_REGISTRY.csv';
$relationshipRegistry=$watchlistRoot.'/records/WORK_RELATIONSHIP_REGISTRY.csv';
$deps=$watchlistRoot.'/development/implementation/WS_DEPENDENCY_REGISTRY.csv';
$stageReg=$watchlistRoot.'/development/implementation/WS_IMPLEMENTATION_STAGE_REGISTER.md';
$matrix=$watchlistRoot.'/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';
$epochRegistry=$watchlistRoot.'/authority/governance/CURRENT_VERIFICATION_EPOCH.json';
$currentVerificationEpoch='';
$errs=array();
if(!is_file($epochRegistry)) failx($errs,'MISSING_VERIFICATION_EPOCH','CURRENT_VERIFICATION_EPOCH.json missing');
else { $ep=json_decode(file_get_contents($epochRegistry),true); if(!is_array($ep)||empty($ep['verification_epoch'])) failx($errs,'BAD_VERIFICATION_EPOCH','invalid verification epoch registry'); else $currentVerificationEpoch=$ep['verification_epoch']; }
foreach(array('WORK_RECORD_REGISTRY'=>$registry,'WORK_RELATIONSHIP_REGISTRY'=>$relationshipRegistry,'DEPENDENCY_REGISTRY'=>$deps,'STAGE_REGISTER'=>$stageReg,'TRACEABILITY_MATRIX'=>$matrix) as $name=>$requiredPath){if(!is_file($requiredPath))failx($errs,'MISSING_INTEGRITY_SOURCE','required relationship-integrity source missing',array('source'=>$name,'path'=>$requiredPath));}
$recordRows=csvAssoc($registry); $relationRows=csvAssoc($relationshipRegistry); $depRows=csvAssoc($deps); $stageRows=parseStageTable($stageReg);
$records=array(); $stages=array(); $attempts=array(); $baselineLocks=array(); $baselineToAttempt=array(); $attemptRecordCounts=array(); $closureCounts=array();
foreach($stageRows as $r)$stages[$r['Stage']]=$r;

// Relationship-registry identity first.
$relationshipIds=array();
$allowedRelTypes=array('INHERITED_FINDING','PRIOR_EVIDENCE','PRIOR_DECISION','PREDECESSOR_ATTEMPT','SUCCESSOR_ORIGIN','SUPERSEDES_CROSS_ATTEMPT','CROSS_BASELINE_CLOSURE_EVIDENCE');
$relationshipTuples=array();
foreach($relationRows as $i=>$rel){
    $rid=isset($rel['relationship_id'])?$rel['relationship_id']:'';
    if($rid===''){failx($errs,'EMPTY_RELATIONSHIP_ID','relationship row has empty id',array('row'=>$i+2));continue;}
    if(isset($relationshipIds[$rid]))failx($errs,'DUP_RELATIONSHIP_ID','duplicate relationship id',array('id'=>$rid));$relationshipIds[$rid]=$rel;
    if(!in_array($rel['relationship_type'],$allowedRelTypes,true))failx($errs,'BAD_RELATIONSHIP_TYPE','unsupported relationship type',array('id'=>$rid,'type'=>$rel['relationship_type']));
    $tuple=$rel['source_record_id'].'|'.$rel['target_record_id'].'|'.$rel['relationship_type'];if(isset($relationshipTuples[$tuple]))failx($errs,'DUP_RELATIONSHIP_TUPLE','duplicate source/target/type relationship tuple',array('id'=>$rid,'first'=>$relationshipTuples[$tuple]));else $relationshipTuples[$tuple]=$rid;
    if(trim($rel['justification'])==='')failx($errs,'RELATIONSHIP_NO_JUSTIFICATION','explicit relationship requires justification',array('id'=>$rid));
}

// Pass 1: record identity, stage/attempt/work/baseline shape, paths.
foreach($recordRows as $i=>$r){
    $id=isset($r['record_id'])?$r['record_id']:''; if($id===''){failx($errs,'EMPTY_RECORD_ID','registry row has empty record_id',array('row'=>$i+2));continue;}
    if(isset($records[$id])) failx($errs,'DUP_RECORD_ID','duplicate record id',array('id'=>$id)); $records[$id]=$r;
    $stage=$r['stage_id']; $attempt=$r['attempt_id']; $work=$r['work_id']; $baseline=$r['baseline_id']; $type=$r['record_type'];
    if($stage!==''&&!isset($stages[$stage])) failx($errs,'UNKNOWN_STAGE','record references unknown stage',array('record'=>$id,'stage'=>$stage));
    if(isBlank($attempt)){failx($errs,'CURRENT_RECORD_NO_ATTEMPT','current Work Record must carry Attempt ID',array('record'=>$id));continue;}
    if(!preg_match('/^(WS-B\d{2}[A-Z]?)-A\d{3}$/',$attempt,$m)) failx($errs,'BAD_ATTEMPT_ID','invalid attempt format',array('record'=>$id,'attempt'=>$attempt));
    else {
        if($stage!==''&&$m[1]!==$stage) failx($errs,'ATTEMPT_STAGE_MISMATCH','attempt prefix != stage',array('record'=>$id,'stage'=>$stage,'attempt'=>$attempt));
        if(!isset($attempts[$attempt]))$attempts[$attempt]=array('stage'=>$stage,'baseline'=>$baseline,'records'=>array());
        else {
            if($attempts[$attempt]['stage']!==$stage)failx($errs,'ATTEMPT_MULTIPLE_STAGES','one Attempt ID cannot represent multiple stages',array('attempt'=>$attempt,'first'=>$attempts[$attempt]['stage'],'other'=>$stage));
            if($attempts[$attempt]['baseline']!==$baseline)failx($errs,'ATTEMPT_MULTIPLE_BASELINES','one Attempt ID cannot bind multiple Baseline IDs',array('attempt'=>$attempt,'first'=>$attempts[$attempt]['baseline'],'other'=>$baseline));
        }
        $attempts[$attempt]['records'][]=$id;
    }
    if($work!==$attempt) failx($errs,'WORK_ID_MISMATCH','work_id must equal attempt_id',array('record'=>$id,'work'=>$work,'attempt'=>$attempt));
    if(isBlank($baseline)) failx($errs,'CURRENT_RECORD_NO_BASELINE','current Work Record must carry Baseline ID',array('record'=>$id,'attempt'=>$attempt));
    if(!recordPrefixValid($id,$type,$attempt)) failx($errs,'RECORD_ID_TYPE_MISMATCH','Record ID prefix/pattern does not match record_type + Attempt ID',array('record'=>$id,'type'=>$type,'attempt'=>$attempt));
    if($type==='WORK_BASELINE_LOCK'){
        if(isset($baselineLocks[$attempt]))failx($errs,'ATTEMPT_MULTIPLE_BASELINE_LOCKS','Attempt ID has more than one WORK_BASELINE_LOCK',array('attempt'=>$attempt,'records'=>array($baselineLocks[$attempt],$id)));
        $baselineLocks[$attempt]=$id;
        if(isset($baselineToAttempt[$baseline])&&$baselineToAttempt[$baseline]!==$attempt)failx($errs,'BASELINE_REUSED_ACROSS_ATTEMPTS','Baseline ID cannot bind multiple attempts',array('baseline'=>$baseline,'first_attempt'=>$baselineToAttempt[$baseline],'other_attempt'=>$attempt));
        $baselineToAttempt[$baseline]=$attempt;
    }
    if($type==='STAGE_ATTEMPT_RECORD'){$attemptRecordCounts[$attempt]=isset($attemptRecordCounts[$attempt])?$attemptRecordCounts[$attempt]+1:1;if($attemptRecordCounts[$attempt]>1)failx($errs,'MULTIPLE_FINAL_ATTEMPT_RECORDS','Attempt may have at most one canonical STAGE_ATTEMPT_RECORD',array('attempt'=>$attempt));}
    if($type==='STAGE_CLOSURE_MANIFEST'){$closureCounts[$attempt]=isset($closureCounts[$attempt])?$closureCounts[$attempt]+1:1;if($closureCounts[$attempt]>1)failx($errs,'MULTIPLE_CLOSURE_MANIFESTS_FOR_ATTEMPT','Attempt may have at most one final closure manifest',array('attempt'=>$attempt));}
    $fp=$repoRoot.'/'.str_replace('\\','/',$r['file_path']); if($r['file_path']!==''&&!is_file($fp)) failx($errs,'MISSING_RECORD_FILE','registry file path missing',array('record'=>$id,'path'=>$r['file_path']));
}

// Baseline existence/binding and baseline-file identity.
foreach($attempts as $attempt=>$a){
    if(!isset($baselineLocks[$attempt]))failx($errs,'ATTEMPT_BASELINE_LOCK_MISSING','every registered Attempt ID must have exactly one WORK_BASELINE_LOCK',array('attempt'=>$attempt,'baseline'=>$a['baseline']));
}
foreach($recordRows as $r){
    $id=$r['record_id']; $attempt=$r['attempt_id']; $baseline=$r['baseline_id'];
    if(!isBlank($attempt)){
        if(!isset($baselineLocks[$attempt]))failx($errs,'RECORD_BASELINE_NOT_REGISTERED','record baseline has no WORK_BASELINE_LOCK for its Attempt',array('record'=>$id,'attempt'=>$attempt,'baseline'=>$baseline));
        elseif(isset($records[$baselineLocks[$attempt]]) && $records[$baselineLocks[$attempt]]['baseline_id']!==$baseline)failx($errs,'RECORD_BASELINE_BINDING_MISMATCH','record baseline differs from Attempt baseline lock',array('record'=>$id,'record_baseline'=>$baseline,'attempt_baseline'=>$records[$baselineLocks[$attempt]]['baseline_id']));
    }
}
foreach($baselineLocks as $attempt=>$rid){
    $r=$records[$rid]; $fp=$repoRoot.'/'.str_replace('\\','/',$r['file_path']);
    if(is_file($fp) && strtolower(pathinfo($fp,PATHINFO_EXTENSION))==='json'){
        $j=json_decode(file_get_contents($fp),true);
        if(!is_array($j))failx($errs,'BASELINE_FILE_INVALID_JSON','baseline-lock evidence is not valid JSON',array('record'=>$rid));
        else {
            $checks=array('record_id'=>$rid,'baseline_id'=>$r['baseline_id'],'stage_id'=>$r['stage_id'],'attempt_id'=>$r['attempt_id'],'work_id'=>$r['work_id']);
            foreach($checks as $k=>$v)if(!isset($j[$k])||trim((string)$j[$k])!==$v)failx($errs,'BASELINE_FILE_IDENTITY_MISMATCH','baseline file metadata differs from registry',array('record'=>$rid,'field'=>$k,'registry'=>$v,'file'=>isset($j[$k])?$j[$k]:'<missing>'));
        }
    }
}

// Relationship registry endpoints/type-specific decision checks.
foreach($relationRows as $rel){
    $rid=$rel['relationship_id'];$src=$rel['source_record_id'];$tgt=$rel['target_record_id'];
    if(!isset($records[$src]))failx($errs,'RELATION_SOURCE_MISSING','relationship source record missing',array('relationship'=>$rid,'source'=>$src));
    if(!isset($records[$tgt]))failx($errs,'RELATION_TARGET_MISSING','relationship target record missing',array('relationship'=>$rid,'target'=>$tgt));
    if(isset($records[$src])&&isset($records[$tgt])){
        $sa=$records[$src]['attempt_id'];$ta=$records[$tgt]['attempt_id'];
        if(!preg_match('/^REL-'.preg_quote($sa,'/').'-\d{3}$/',$rid))failx($errs,'RELATIONSHIP_ID_SOURCE_ATTEMPT_MISMATCH','Relationship ID must embed source Attempt ID',array('relationship'=>$rid,'source_attempt'=>$sa));
        if($sa===$ta && in_array($rel['relationship_type'],array('INHERITED_FINDING','PRIOR_EVIDENCE','PRIOR_DECISION','PREDECESSOR_ATTEMPT','SUCCESSOR_ORIGIN','SUPERSEDES_CROSS_ATTEMPT','CROSS_BASELINE_CLOSURE_EVIDENCE'),true))failx($errs,'CROSS_SCOPE_RELATION_NOT_CROSS_SCOPE','cross-attempt/stage relationship type used for same attempt',array('relationship'=>$rid,'attempt'=>$sa));
    }
    if($rel['relationship_type']==='CROSS_BASELINE_CLOSURE_EVIDENCE'){
        if(isset($records[$src])&&$records[$src]['record_type']!=='STAGE_CLOSURE_MANIFEST')failx($errs,'CROSS_BASELINE_SOURCE_NOT_CLOSURE','cross-baseline closure relation source must be STAGE_CLOSURE_MANIFEST',array('relationship'=>$rid,'source'=>$src));
        if(isset($records[$tgt])&&!evidenceLikeType($records[$tgt]['record_type']))failx($errs,'CROSS_BASELINE_TARGET_NOT_EVIDENCE','cross-baseline closure relation target must be evidence-like',array('relationship'=>$rid,'target'=>$tgt));
        $did=trim($rel['reviewed_decision_id']);
        if($did===''||!isset($records[$did])||$records[$did]['record_type']!=='DECISION')failx($errs,'CROSS_BASELINE_RELATION_NO_REVIEWED_DECISION','cross-baseline closure evidence requires existing DECISION',array('relationship'=>$rid,'decision'=>$did));
        elseif(isset($records[$src])&&($records[$did]['attempt_id']!==$records[$src]['attempt_id']||$records[$did]['baseline_id']!==$records[$src]['baseline_id']))failx($errs,'CROSS_BASELINE_REVIEW_DECISION_CONTEXT_MISMATCH','reviewed Decision must belong to closure Attempt/Baseline',array('relationship'=>$rid,'decision'=>$did));
    }
}

// Direct related references: existence + type + no silent cross-attempt link.
$relationColumnRules=array(
    'related_finding_ids'=>array('targetTypes'=>array('FINDING'),'crossTypes'=>array('INHERITED_FINDING')),
    'related_evidence_ids'=>array('targetTypes'=>array('EVIDENCE','WORK_BASELINE_LOCK','STAGE_ATTEMPT_RECORD','CHANGE_IMPACT_DECLARATION'),'crossTypes'=>array('PRIOR_EVIDENCE')),
    'related_decision_ids'=>array('targetTypes'=>array('DECISION'),'crossTypes'=>array('PRIOR_DECISION'))
);
foreach($recordRows as $r){
    $id=$r['record_id'];
    foreach($relationColumnRules as $col=>$rule) foreach(splitIds($r[$col]) as $rid){
        if(strpos($rid,'LEGACY:')===0){
            if($r['record_type']==='STAGE_CLOSURE_MANIFEST' && $col==='related_evidence_ids')failx($errs,'CLOSURE_LEGACY_EVIDENCE_NOT_CLOSURE_CRITICAL','legacy/unbaseline evidence may be contextual but cannot be closure-critical related_evidence',array('closure'=>$id,'target'=>$rid));
            continue;
        }
        if(!isset($records[$rid])){failx($errs,'DANGLING_RECORD_REF','current registry relationship target missing',array('record'=>$id,'column'=>$col,'target'=>$rid));continue;}
        if(!in_array($records[$rid]['record_type'],$rule['targetTypes'],true)){
            $code=$col==='related_finding_ids'?'RELATED_FINDING_WRONG_TYPE':($col==='related_decision_ids'?'RELATED_DECISION_WRONG_TYPE':'RELATED_EVIDENCE_WRONG_TYPE');
            failx($errs,$code,'relationship target exists but has wrong record_type',array('record'=>$id,'column'=>$col,'target'=>$rid,'target_type'=>$records[$rid]['record_type']));
        }
        if($records[$rid]['attempt_id']!==$r['attempt_id'] || $records[$rid]['stage_id']!==$r['stage_id']){
            // Closure + cross-baseline evidence has stricter authorization below.
            if($r['record_type']==='STAGE_CLOSURE_MANIFEST' && $col==='related_evidence_ids' && $records[$rid]['baseline_id']!==$r['baseline_id']){
                $rel=findRelation($relationRows,$id,$rid,array('CROSS_BASELINE_CLOSURE_EVIDENCE'));
                if(!$rel)failx($errs,'CLOSURE_CROSS_BASELINE_UNAUTHORIZED','closure evidence from different baseline requires explicit CROSS_BASELINE_CLOSURE_EVIDENCE relation',array('closure'=>$id,'evidence'=>$rid,'closure_baseline'=>$r['baseline_id'],'evidence_baseline'=>$records[$rid]['baseline_id']));
                else {
                    if(trim($rel['justification'])==='')failx($errs,'CLOSURE_CROSS_BASELINE_NO_JUSTIFICATION','cross-baseline closure relation missing justification',array('relationship'=>$rel['relationship_id']));
                    $did=trim($rel['reviewed_decision_id']);
                    if($did===''||!isset($records[$did])||$records[$did]['record_type']!=='DECISION')failx($errs,'CLOSURE_CROSS_BASELINE_BAD_DECISION','cross-baseline closure relation missing valid reviewed decision',array('relationship'=>$rel['relationship_id'],'decision'=>$did));
                    elseif(!in_array($did,splitIds($r['related_decision_ids']),true))failx($errs,'CLOSURE_CROSS_BASELINE_DECISION_NOT_BOUND','reviewed decision must also be bound to closure related_decision_ids',array('closure'=>$id,'decision'=>$did));
                }
            } else {
                $rel=findRelation($relationRows,$id,$rid,$rule['crossTypes']);
                if(!$rel)failx($errs,'CROSS_ATTEMPT_RELATION_UNDECLARED','cross-attempt/cross-stage relationship must be explicitly registered and justified',array('record'=>$id,'column'=>$col,'target'=>$rid,'source_attempt'=>$r['attempt_id'],'target_attempt'=>$records[$rid]['attempt_id']));
                elseif(trim($rel['justification'])==='')failx($errs,'CROSS_ATTEMPT_RELATION_NO_JUSTIFICATION','cross-attempt relationship missing justification',array('relationship'=>$rel['relationship_id']));
            }
        }
    }
    foreach(splitIds($r['supersedes']) as $rid){ if(!isset($records[$rid])) failx($errs,'DANGLING_SUPERSEDES','superseded current record missing',array('record'=>$id,'target'=>$rid)); elseif($records[$rid]['attempt_id']!==$r['attempt_id']){ $rel=findRelation($relationRows,$id,$rid,array('SUPERSEDES_CROSS_ATTEMPT')); if(!$rel)failx($errs,'CROSS_ATTEMPT_SUPERSEDES_UNDECLARED','cross-attempt supersedes must be explicit in Work Relationship Registry',array('record'=>$id,'target'=>$rid)); } }
}

// Supersedes cycle.
foreach($records as $id=>$r){$seen=array($id=>true);$stack=splitIds($r['supersedes']);while($stack){$x=array_pop($stack);if(isset($seen[$x])){failx($errs,'SUPERSEDES_CYCLE','circular supersedes chain',array('record'=>$id,'at'=>$x));break;}$seen[$x]=true;if(isset($records[$x]))foreach(splitIds($records[$x]['supersedes']) as $y)$stack[]=$y;}}

// Dependency registry.
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
        if(!isset($attempts[$attempt])) failx($errs,'STAGE_ATTEMPT_NOT_REGISTERED','latest attempt has no current registry records',array('stage'=>$stage,'attempt'=>$attempt));
        if($baseline===''||$baseline==='—') failx($errs,'STAGE_ATTEMPT_NO_BASELINE','latest attempt missing baseline id',array('stage'=>$stage));
        elseif(!isset($baselineLocks[$attempt])) failx($errs,'STAGE_BASELINE_NOT_REGISTERED','latest attempt has no baseline lock',array('stage'=>$stage,'baseline'=>$baseline));
        elseif($records[$baselineLocks[$attempt]]['baseline_id']!==$baseline) failx($errs,'STAGE_BASELINE_BINDING_MISMATCH','stage register baseline differs from Attempt baseline lock',array('stage'=>$stage,'stage_baseline'=>$baseline,'attempt_baseline'=>$records[$baselineLocks[$attempt]]['baseline_id']));
    }
    if($impact!==''&&$impact!=='—'){
        if(!isset($records[$impact])) failx($errs,'STAGE_CHANGE_IMPACT_NOT_REGISTERED','stage change-impact record missing',array('stage'=>$stage,'record'=>$impact));
        elseif($records[$impact]['record_type']!=='CHANGE_IMPACT_DECLARATION') failx($errs,'STAGE_CHANGE_IMPACT_WRONG_TYPE','stage change-impact pointer must target CHANGE_IMPACT_DECLARATION',array('stage'=>$stage,'record'=>$impact));
        elseif($attempt!==''&&$attempt!=='—'&&($records[$impact]['attempt_id']!==$attempt||$records[$impact]['baseline_id']!==$baseline))failx($errs,'STAGE_CHANGE_IMPACT_CONTEXT_MISMATCH','stage change-impact must belong to latest Attempt/Baseline',array('stage'=>$stage,'record'=>$impact));
    }
    if($dep!==''&&$dep!=='—'&&!isset($depIds[$dep])) failx($errs,'STAGE_DEP_NOT_REGISTERED','stage dependency id missing',array('stage'=>$stage,'dependency'=>$dep));
    if($state==='WAITING_VERIFIED_DEPENDENCY'&&($dep===''||$dep==='—')) failx($errs,'WAITING_STAGE_NO_DEP_ID','waiting stage must reference dependency registry',array('stage'=>$stage));
    if(in_array($state,$terminal,true)){
        if($closure===''||$closure==='—') failx($errs,'TERMINAL_STAGE_NO_CLOSURE','terminal stage missing closure manifest',array('stage'=>$stage,'state'=>$state));
        elseif(!isset($records[$closure])||$records[$closure]['record_type']!=='STAGE_CLOSURE_MANIFEST') failx($errs,'BAD_CLOSURE_RECORD','closure manifest not registered with correct type',array('stage'=>$stage,'record'=>$closure));
        else {
            $cr=$records[$closure];
            if($cr['stage_id']!==$stage||$cr['attempt_id']!==$attempt||$cr['baseline_id']!==$baseline)failx($errs,'CLOSURE_STAGE_ATTEMPT_BASELINE_MISMATCH','closure must match terminal Stage Register stage/attempt/baseline',array('stage'=>$stage,'closure'=>$closure));
            if(!isset($attemptRecordCounts[$attempt])||$attemptRecordCounts[$attempt]!==1)failx($errs,'TERMINAL_ATTEMPT_RECORD_MISSING_OR_DUPLICATE','terminal stage requires exactly one canonical STAGE_ATTEMPT_RECORD for final Attempt',array('stage'=>$stage,'attempt'=>$attempt,'count'=>isset($attemptRecordCounts[$attempt])?$attemptRecordCounts[$attempt]:0));
            $fp=$repoRoot.'/'.str_replace('\\','/',$cr['file_path']);$mf=parseClosureManifest($fp);
            if($mf['record_id']!==$closure||$mf['stage_id']!==$stage||$mf['attempt_id']!==$attempt||$mf['baseline_id']!==$baseline)failx($errs,'CLOSURE_MANIFEST_IDENTITY_MISMATCH','closure file identity does not match registry/stage register',array('closure'=>$closure,'parsed'=>$mf));
            if($mf['work_id']!==''&&$mf['work_id']!==$attempt)failx($errs,'CLOSURE_MANIFEST_WORK_MISMATCH','closure file Work ID must equal final Attempt ID',array('closure'=>$closure,'work'=>$mf['work_id'],'attempt'=>$attempt));
            $regEvidence=array_values(array_filter(splitIds($cr['related_evidence_ids']),function($x){return strpos($x,'LEGACY:')!==0;}));
            $regFindings=array_values(array_filter(splitIds($cr['related_finding_ids']),function($x){return strpos($x,'LEGACY:')!==0;}));
            $regDecisions=array_values(array_filter(splitIds($cr['related_decision_ids']),function($x){return strpos($x,'LEGACY:')!==0;}));
            if(count($mf['attempt_record'])!==1||!isset($records[$mf['attempt_record'][0]])||$records[$mf['attempt_record'][0]]['record_type']!=='STAGE_ATTEMPT_RECORD'||$records[$mf['attempt_record'][0]]['attempt_id']!==$attempt)failx($errs,'CLOSURE_MANIFEST_ATTEMPT_RECORD_MISMATCH','closure file must point exactly one canonical STAGE_ATTEMPT_RECORD for final Attempt',array('closure'=>$closure,'attempt_record'=>$mf['attempt_record']));
            if(!setsEqual($mf['evidence'],$regEvidence))failx($errs,'CLOSURE_MANIFEST_EVIDENCE_REGISTRY_MISMATCH','closure file Evidence list must exactly match registry related_evidence_ids',array('closure'=>$closure,'manifest'=>$mf['evidence'],'registry'=>$regEvidence));
            if(!setsEqual($mf['findings'],$regFindings))failx($errs,'CLOSURE_MANIFEST_FINDING_REGISTRY_MISMATCH','closure file Findings list must exactly match registry related_finding_ids',array('closure'=>$closure));
            if(!setsEqual($mf['decisions'],$regDecisions))failx($errs,'CLOSURE_MANIFEST_DECISION_REGISTRY_MISMATCH','closure file Decisions list must exactly match registry related_decision_ids',array('closure'=>$closure));
        }
    } elseif($closure!==''&&$closure!=='—') failx($errs,'ACTIVE_STAGE_HAS_FINAL_CLOSURE','active stage must not point final closure manifest',array('stage'=>$stage,'record'=>$closure));
}

// SATISFIED matrix rows require concrete refs; current evidence must be registry-backed and baseline-bound.
$rows=csvAssoc($matrix);foreach($rows as $r){if(isset($r['coverage_status'])&&$r['coverage_status']==='SATISFIED'){
    foreach(array('implementation_ref','test_ref','evidence_ref') as $c)if(trim($r[$c])===''||trim($r[$c])==='N/A')failx($errs,'SATISFIED_MISSING_REF','SATISFIED rule missing '.$c,array('rule'=>$r['rule_id']));
    $e=trim($r['evidence_ref']);
    if(!preg_match('/^(E|SC)-WS-B/',$e)) failx($errs,'SATISFIED_EVIDENCE_PRE_REBASELINE_OR_UNCORRELATED','SATISFIED evidence must be current correlation-first WS-B evidence',array('rule'=>$r['rule_id'],'evidence'=>$e));
    elseif(!isset($records[$e]))failx($errs,'SATISFIED_EVIDENCE_NOT_REGISTERED','current evidence id not in Work Record Registry',array('rule'=>$r['rule_id'],'evidence'=>$e));
    elseif(isBlank($records[$e]['baseline_id'])||!isset($baselineToAttempt[$records[$e]['baseline_id']]))failx($errs,'SATISFIED_EVIDENCE_BASELINE_INVALID','SATISFIED evidence is not bound to a registered baseline',array('rule'=>$r['rule_id'],'evidence'=>$e));
}}

$report=array(
    'status'=>count($errs)?'FAIL':'PASS',
    'generated_at'=>date(DATE_ATOM),
    'stage_rows'=>count($stageRows),
    'work_record_rows'=>count($recordRows),
    'relationship_rows'=>count($relationRows),
    'dependency_rows'=>count($depRows),
    'attempt_identities'=>count($attempts),
    'baseline_locks'=>count($baselineLocks),
    'enforced_invariants'=>array(
        'attempt_identity_unique_stage_and_baseline'=>true,
        'record_id_unique'=>true,
        'stage_reference_exists'=>true,
        'baseline_reference_exists_and_bound'=>true,
        'related_finding_type_safe'=>true,
        'related_decision_type_safe'=>true,
        'supersedes_acyclic'=>true,
        'closure_cross_baseline_evidence_controlled'=>true,
        'cross_attempt_relationship_explicit'=>true
    ),
    'errors'=>$errs
);
$out=null;foreach($argv as $arg)if(strpos($arg,'--output=')===0)$out=substr($arg,9);if($out){if(!preg_match('~^(?:[A-Za-z]:[\\/]|/)~',$out))$out=$repoRoot.'/'.str_replace('\\','/',$out);if(!is_dir(dirname($out)))mkdir(dirname($out),0777,true);file_put_contents($out,json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL);}
echo json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL; exit(count($errs)?1:0);

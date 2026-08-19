<?php
/** Mutation self-test for the nine hard relationship invariants. PHP 7.3+. */
function rrmdir($dir){if(!is_dir($dir))return;foreach(scandir($dir) as $x){if($x==='.'||$x==='..')continue;$p=$dir.DIRECTORY_SEPARATOR.$x;if(is_dir($p))rrmdir($p);else unlink($p);}rmdir($dir);}
function mkdirp($p){if(!is_dir($p))mkdir($p,0777,true);}
function put($p,$s){mkdirp(dirname($p));file_put_contents($p,$s);}
function csvWrite($p,$h,$rows){mkdirp(dirname($p));$f=fopen($p,'w');fputcsv($f,$h);foreach($rows as $r)fputcsv($f,$r);fclose($f);}
function baselineJson($id,$stage,$attempt,$baseline){return json_encode(array('record_id'=>$id,'baseline_id'=>$baseline,'stage_id'=>$stage,'attempt_id'=>$attempt,'work_id'=>$attempt,'verification_epoch'=>'WS-REBASELINE-20260819-001'),JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL;}
function baseRows(){
    return array(
      array('E-WS-B04-A003-001','WORK_BASELINE_LOCK','WS-B04','WS-B04-A003','WS-B04-A003','WSBL-20260818-003','FINAL','docs/watchlist/records/evidence/runs/E-WS-B04-A003-001_BASELINE.json','','','','','','2026-08-18T10:00:00+07:00',''),
      array('F-WS-B04-A003-001','FINDING','WS-B04','WS-B04-A003','WS-B04-A003','WSBL-20260818-003','OPEN','docs/watchlist/development/findings/F-WS-B04-A003-001.md','','','','','','2026-08-18T10:01:00+07:00',''),
      array('E-WS-B04-A003-002','EVIDENCE','WS-B04','WS-B04-A003','WS-B04-A003','WSBL-20260818-003','FINAL','docs/watchlist/records/evidence/runs/E-WS-B04-A003-002.json','F-WS-B04-A003-001','','','','','2026-08-18T10:02:00+07:00',''),
      array('D-WS-B04-A003-001','DECISION','WS-B04','WS-B04-A003','WS-B04-A003','WSBL-20260818-003','ISSUED','docs/watchlist/records/decisions/D-WS-B04-A003-001.md','F-WS-B04-A003-001','E-WS-B04-A003-002','','','','2026-08-18T10:03:00+07:00',''),
      array('E-WS-B04-A003-003','STAGE_ATTEMPT_RECORD','WS-B04','WS-B04-A003','WS-B04-A003','WSBL-20260818-003','FINAL','docs/watchlist/records/evidence/runs/E-WS-B04-A003-003_ATTEMPT.md','F-WS-B04-A003-001','E-WS-B04-A003-002','D-WS-B04-A003-001','','','2026-08-18T10:04:00+07:00','')
    );
}
function headers(){return array('record_id','record_type','stage_id','attempt_id','work_id','baseline_id','record_status','file_path','related_finding_ids','related_evidence_ids','related_decision_ids','related_dependency_ids','supersedes','created_at','notes');}
function relHeaders(){return array('relationship_id','source_record_id','target_record_id','relationship_type','justification','reviewed_decision_id','created_at','notes');}
function makeFixture($dir,$gateSource,$terminal=false,$crossBaseline=false,$authorized=false){
    rrmdir($dir);mkdirp($dir.'/docs/watchlist/development/implementation/tests');copy($gateSource,$dir.'/docs/watchlist/development/implementation/tests/WatchlistRelationshipIntegrityGate.php');
    $state=$terminal?'DONE':'IN_PROGRESS';$closure=$terminal?'SC-WS-B04-A003-001':'—';
    $stage="| Stage | Maps to | Lifecycle state | Stage / evaluation verdict | Latest attempt / Work ID | Baseline ID | Change impact | Convergence | Strategy coverage | Residue state / evidence | Integrity gate | Dependency ID | Dependency / resume trigger | Open finding | Active remediation / decision | Closure manifest | Successor | Resume from | Last update |\n|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|\n| `WS-B04` | x | `$state` | — | `WS-B04-A003` | `WSBL-20260818-003` | — | `IMPROVING` | 0/1 | — | PASS | — | — | — | — | `$closure` | — | — | 2026-08-18 |\n\n";
    put($dir.'/docs/watchlist/development/implementation/WS_IMPLEMENTATION_STAGE_REGISTER.md',$stage);
    csvWrite($dir.'/docs/watchlist/development/implementation/WS_DEPENDENCY_REGISTRY.csv',array('dependency_id','consumer_stage','provider','requirement','status','evidence','owner','resume_trigger','affected_rule_ids','created_at','updated_at','notes'),array());
    csvWrite($dir.'/docs/watchlist/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv',array('rule_id','coverage_status','implementation_ref','test_ref','evidence_ref'),array());
    put($dir.'/docs/watchlist/authority/governance/CURRENT_VERIFICATION_EPOCH.json',json_encode(array('verification_epoch'=>'WS-REBASELINE-20260819-001'),JSON_PRETTY_PRINT).PHP_EOL);
    $rows=baseRows();$rels=array();
    if($crossBaseline){
        $rows[]=array('E-WS-B04-A002-001','WORK_BASELINE_LOCK','WS-B04','WS-B04-A002','WS-B04-A002','WSBL-20260818-002','FINAL','docs/watchlist/records/evidence/runs/E-WS-B04-A002-001_BASELINE.json','','','','','','2026-08-18T09:00:00+07:00','');
        $rows[]=array('E-WS-B04-A002-002','EVIDENCE','WS-B04','WS-B04-A002','WS-B04-A002','WSBL-20260818-002','FINAL','docs/watchlist/records/evidence/runs/E-WS-B04-A002-002.json','','','','','','2026-08-18T09:01:00+07:00','');
        put($dir.'/docs/watchlist/records/evidence/runs/E-WS-B04-A002-001_BASELINE.json',baselineJson('E-WS-B04-A002-001','WS-B04','WS-B04-A002','WSBL-20260818-002'));put($dir.'/docs/watchlist/records/evidence/runs/E-WS-B04-A002-002.json','{}\n');
    }
    if($terminal){
        $ev='E-WS-B04-A003-003'.($crossBaseline?',E-WS-B04-A002-002':'');
        $rows[]=array('SC-WS-B04-A003-001','STAGE_CLOSURE_MANIFEST','WS-B04','WS-B04-A003','WS-B04-A003','WSBL-20260818-003','FINAL','docs/watchlist/records/evidence/runs/SC-WS-B04-A003-001.md','F-WS-B04-A003-001',$ev,'D-WS-B04-A003-001','','','2026-08-18T10:05:00+07:00','');
        $manifest="# Weekly Swing Stage Closure Manifest\n\n## Identity\n\n- Closure Record ID: `SC-WS-B04-A003-001`\n- Stage ID: `WS-B04`\n- Final Attempt ID: `WS-B04-A003`\n- Work ID: `WS-B04-A003`\n- Baseline ID: `WSBL-20260818-003`\n\n## Supporting Records\n\n- Attempt record: `E-WS-B04-A003-003`\n- Work Record Registry entries: x\n- Evidence: `".str_replace(',', '`, `',$ev)."`\n- Findings: `F-WS-B04-A003-001`\n- Decisions: `D-WS-B04-A003-001`\n- Dependencies: N/A\n- Traceability rows: x\n";
        put($dir.'/docs/watchlist/records/evidence/runs/SC-WS-B04-A003-001.md',$manifest);
        if($crossBaseline&&$authorized)$rels[]=array('REL-WS-B04-A003-001','SC-WS-B04-A003-001','E-WS-B04-A002-002','CROSS_BASELINE_CLOSURE_EVIDENCE','Prior-attempt evidence remains necessary and is explicitly reviewed.','D-WS-B04-A003-001','2026-08-18T10:05:00+07:00','');
    }
    csvWrite($dir.'/docs/watchlist/records/WORK_RECORD_REGISTRY.csv',headers(),$rows);csvWrite($dir.'/docs/watchlist/records/WORK_RELATIONSHIP_REGISTRY.csv',relHeaders(),$rels);
    put($dir.'/docs/watchlist/records/evidence/runs/E-WS-B04-A003-001_BASELINE.json',baselineJson('E-WS-B04-A003-001','WS-B04','WS-B04-A003','WSBL-20260818-003'));
    foreach(array('F-WS-B04-A003-001.md','../records/decisions/D-WS-B04-A003-001.md') as $x){}
    put($dir.'/docs/watchlist/development/findings/F-WS-B04-A003-001.md','# finding\n');put($dir.'/docs/watchlist/records/evidence/runs/E-WS-B04-A003-002.json','{}\n');put($dir.'/docs/watchlist/records/decisions/D-WS-B04-A003-001.md','# decision\n');put($dir.'/docs/watchlist/records/evidence/runs/E-WS-B04-A003-003_ATTEMPT.md','# attempt\n');
}
function readCsvRows($p){$f=fopen($p,'r');$h=fgetcsv($f);$rows=array();while(($r=fgetcsv($f))!==false)$rows[]=$r;fclose($f);return array($h,$rows);}
function writeRows($p,$h,$rows){csvWrite($p,$h,$rows);}
function runGate($dir){$cmd='php '.escapeshellarg($dir.'/docs/watchlist/development/implementation/tests/WatchlistRelationshipIntegrityGate.php').' 2>&1';$out=array();$rc=0;exec($cmd,$out,$rc);$j=json_decode(implode("\n",$out),true);$codes=array();if(is_array($j)&&isset($j['errors']))foreach($j['errors'] as $e)$codes[]=$e['code'];return array($rc,$codes,$j);}
function expectFail($name,$dir,$code){list($rc,$codes)=runGate($dir);if($rc===0||!in_array($code,$codes,true)){echo "FAIL $name expected=$code got=".implode(',',$codes)."\n";return false;}echo "PASS $name -> $code\n";return true;}
function expectPass($name,$dir){list($rc,$codes)=runGate($dir);if($rc!==0){echo "FAIL $name got=".implode(',',$codes)."\n";return false;}echo "PASS $name\n";return true;}
$gate=realpath(__DIR__.'/WatchlistRelationshipIntegrityGate.php');$base=sys_get_temp_dir().'/ws_rel_gate_selftest_'.getmypid();$ok=true;
makeFixture($base,$gate);$ok=expectPass('base_valid',$base)&&$ok;
// 1 Attempt identity baseline conflict.
makeFixture($base,$gate);list($h,$r)=readCsvRows($base.'/docs/watchlist/records/WORK_RECORD_REGISTRY.csv');$r[2][5]='WSBL-20260818-999';writeRows($base.'/docs/watchlist/records/WORK_RECORD_REGISTRY.csv',$h,$r);$ok=expectFail('attempt_unique_stage_baseline',$base,'ATTEMPT_MULTIPLE_BASELINES')&&$ok;
// 2 Duplicate Record ID.
makeFixture($base,$gate);list($h,$r)=readCsvRows($base.'/docs/watchlist/records/WORK_RECORD_REGISTRY.csv');$r[]=$r[1];writeRows($base.'/docs/watchlist/records/WORK_RECORD_REGISTRY.csv',$h,$r);$ok=expectFail('record_id_unique',$base,'DUP_RECORD_ID')&&$ok;
// 3 Unknown Stage.
makeFixture($base,$gate);list($h,$r)=readCsvRows($base.'/docs/watchlist/records/WORK_RECORD_REGISTRY.csv');$x=$r[2];$x[0]='E-WS-B99-A001-001';$x[2]='WS-B99';$x[3]='WS-B99-A001';$x[4]='WS-B99-A001';$x[5]='WSBL-20260818-099';$x[7]='docs/watchlist/records/evidence/runs/E-WS-B99-A001-001.json';$r[]=$x;put($base.'/'.$x[7],'{}\n');writeRows($base.'/docs/watchlist/records/WORK_RECORD_REGISTRY.csv',$h,$r);$ok=expectFail('stage_exists',$base,'UNKNOWN_STAGE')&&$ok;
// 4 Baseline must exist.
makeFixture($base,$gate);list($h,$r)=readCsvRows($base.'/docs/watchlist/records/WORK_RECORD_REGISTRY.csv');$x=$r[2];$x[0]='E-WS-B04-A004-001';$x[3]='WS-B04-A004';$x[4]='WS-B04-A004';$x[5]='WSBL-20260818-004';$x[7]='docs/watchlist/records/evidence/runs/E-WS-B04-A004-001.json';$x[8]='';$r[]=$x;put($base.'/'.$x[7],'{}\n');writeRows($base.'/docs/watchlist/records/WORK_RECORD_REGISTRY.csv',$h,$r);$ok=expectFail('baseline_exists',$base,'ATTEMPT_BASELINE_LOCK_MISSING')&&$ok;
// 5 Related Finding type-safe.
makeFixture($base,$gate);list($h,$r)=readCsvRows($base.'/docs/watchlist/records/WORK_RECORD_REGISTRY.csv');$r[2][8]='D-WS-B04-A003-001';writeRows($base.'/docs/watchlist/records/WORK_RECORD_REGISTRY.csv',$h,$r);$ok=expectFail('related_finding_type',$base,'RELATED_FINDING_WRONG_TYPE')&&$ok;
// 6 Related Decision type-safe.
makeFixture($base,$gate);list($h,$r)=readCsvRows($base.'/docs/watchlist/records/WORK_RECORD_REGISTRY.csv');$r[4][10]='F-WS-B04-A003-001';writeRows($base.'/docs/watchlist/records/WORK_RECORD_REGISTRY.csv',$h,$r);$ok=expectFail('related_decision_type',$base,'RELATED_DECISION_WRONG_TYPE')&&$ok;
// 7 Supersedes cycle.
makeFixture($base,$gate);list($h,$r)=readCsvRows($base.'/docs/watchlist/records/WORK_RECORD_REGISTRY.csv');$r[1][12]='D-WS-B04-A003-001';$r[3][12]='F-WS-B04-A003-001';writeRows($base.'/docs/watchlist/records/WORK_RECORD_REGISTRY.csv',$h,$r);$ok=expectFail('supersedes_acyclic',$base,'SUPERSEDES_CYCLE')&&$ok;
// 8 Closure wrong baseline without authorization.
makeFixture($base,$gate,true,true,false);$ok=expectFail('closure_cross_baseline_control',$base,'CLOSURE_CROSS_BASELINE_UNAUTHORIZED')&&$ok;
// 9 Cross-attempt relation must be explicit.
makeFixture($base,$gate,false,true,false);list($h,$r)=readCsvRows($base.'/docs/watchlist/records/WORK_RECORD_REGISTRY.csv');$r[2][8]='F-WS-B04-A002-001';$r[]=array('F-WS-B04-A002-001','FINDING','WS-B04','WS-B04-A002','WS-B04-A002','WSBL-20260818-002','OPEN','docs/watchlist/development/findings/F-WS-B04-A002-001.md','','','','','','2026-08-18T09:02:00+07:00','');put($base.'/docs/watchlist/development/findings/F-WS-B04-A002-001.md','# prior finding\n');writeRows($base.'/docs/watchlist/records/WORK_RECORD_REGISTRY.csv',$h,$r);$ok=expectFail('cross_attempt_explicit',$base,'CROSS_ATTEMPT_RELATION_UNDECLARED')&&$ok;
// Positive: authorized cross-baseline closure passes.
makeFixture($base,$gate,true,true,true);$ok=expectPass('authorized_cross_baseline_closure',$base)&&$ok;
rrmdir($base);exit($ok?0:1);

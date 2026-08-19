<?php
/** WatchlistDocumentationIntegrityGate.php — PHP 7.3+ / no external deps. */

function argValue($name, $default = null) {
    global $argv;
    $prefix = '--' . $name . '=';
    foreach ($argv as $arg) if (strpos($arg, $prefix) === 0) return substr($arg, strlen($prefix));
    return $default;
}
function norm($p) { return str_replace('\\', '/', $p); }
function rel($p, $root) {
    $rp = realpath($p); $rr = realpath($root);
    if ($rp === false) return norm($p);
    $rp = norm($rp); $rr = rtrim(norm($rr), '/');
    return strpos($rp, $rr . '/') === 0 ? substr($rp, strlen($rr) + 1) : $rp;
}
function filesRecursive($dir) {
    $out = array();
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $f) if ($f->isFile()) $out[] = $f->getPathname();
    sort($out); return $out;
}
function check(&$checks, $name, $ok, $details = array()) {
    $checks[] = array('check' => $name, 'status' => $ok ? 'PASS' : 'FAIL', 'details' => $details);
}
function sha1Upper($p) { return strtoupper(sha1_file($p)); }

$watchlistRoot = realpath(dirname(__DIR__, 3));
$repoRoot = realpath(dirname($watchlistRoot, 2));
if (!$watchlistRoot || !$repoRoot) { fwrite(STDERR, "Cannot resolve roots\n"); exit(2); }
$checks = array(); $legacyExceptionsUsed = array();

// 1. root architecture
$req = array('authority','development','records'); $missing=array();
foreach ($req as $d) if (!is_dir($watchlistRoot . DIRECTORY_SEPARATOR . $d)) $missing[]=$d;
check($checks,'ROOT_ARCHITECTURE',count($missing)===0,array('missing'=>$missing));

// 2. active markdown links
$activeRoots = array(
    $watchlistRoot . DIRECTORY_SEPARATOR . 'authority',
    $watchlistRoot . DIRECTORY_SEPARATOR . 'development' . DIRECTORY_SEPARATOR . 'implementation'
);
$activeMd = array($watchlistRoot . DIRECTORY_SEPARATOR . 'README.md', $watchlistRoot . DIRECTORY_SEPARATOR . 'START_HERE.md');
foreach ($activeRoots as $ar) foreach (filesRecursive($ar) as $p) if (substr($p,-3)==='.md') $activeMd[]=$p;
$broken=array();
foreach ($activeMd as $p) {
    $txt=file_get_contents($p);
    if (!preg_match_all('/\[[^\]]*\]\(([^)]+)\)/', $txt, $m)) continue;
    foreach ($m[1] as $target) {
        $target=trim($target);
        if ($target==='' || $target[0]==='#' || preg_match('/^[a-z]+:\/\//i',$target) || strpos($target,'mailto:')===0 || strpos($target,'sandbox:')===0) continue;
        $target=preg_replace('/#.*/','',$target); $target=preg_replace('/\?.*/','',$target);
        $target=rawurldecode($target);
        $resolved=dirname($p) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $target);
        if (!file_exists($resolved)) $broken[]=array('file'=>rel($p,$repoRoot),'target'=>$target);
    }
}
check($checks,'ACTIVE_MARKDOWN_LINKS',count($broken)===0,array('checked'=>count($activeMd),'broken_count'=>count($broken),'samples'=>array_slice($broken,0,10)));

// 3. JSON and CSV parse/shape
$jsonErrors=array(); $csvErrors=array(); $jsonCount=0; $csvCount=0;
foreach (filesRecursive($watchlistRoot) as $p) {
    if (substr($p,-5)==='.json') {
        $jsonCount++; json_decode(file_get_contents($p),true);
        if (json_last_error()!==JSON_ERROR_NONE) $jsonErrors[]=array('file'=>rel($p,$repoRoot),'error'=>json_last_error_msg());
    } elseif (substr($p,-4)==='.csv') {
        $csvCount++; $fh=fopen($p,'r'); $header=fgetcsv($fh); $expected=is_array($header)?count($header):0; $line=1;
        if ($expected===0) $csvErrors[]=array('file'=>rel($p,$repoRoot),'error'=>'missing header');
        else while (($row=fgetcsv($fh))!==false) { $line++; if (count($row)!==$expected) { $csvErrors[]=array('file'=>rel($p,$repoRoot),'line'=>$line,'expected'=>$expected,'actual'=>count($row)); break; } }
        fclose($fh);
    }
}
check($checks,'JSON_PARSE',count($jsonErrors)===0,array('checked'=>$jsonCount,'errors'=>array_slice($jsonErrors,0,10)));
check($checks,'CSV_STRUCTURE',count($csvErrors)===0,array('checked'=>$csvCount,'errors'=>array_slice($csvErrors,0,10)));

// 4. Windows-safe path length (repo-relative)
$maxLen=0; $maxPath=''; $tooLong=array(); $limit=180;
foreach (filesRecursive($watchlistRoot) as $p) { $r=rel($p,$repoRoot); $l=strlen($r); if ($l>$maxLen){$maxLen=$l;$maxPath=$r;} if($l>$limit)$tooLong[]=$r; }
check($checks,'WINDOWS_SAFE_PATHS',count($tooLong)===0,array('limit'=>$limit,'max_length'=>$maxLen,'max_path'=>$maxPath,'too_long'=>array_slice($tooLong,0,10)));

// 5. numbered governance heading duplicates
$headingErrors=array();
foreach (glob($watchlistRoot . '/authority/governance/*.md') as $p) {
    $seen=array(); $lineNo=0;
    foreach (file($p) as $line) { $lineNo++; if (preg_match('/^##\s+(\d+)\./',$line,$m)) { $n=$m[1]; if(isset($seen[$n])) $headingErrors[]=array('file'=>rel($p,$repoRoot),'section'=>$n,'first_line'=>$seen[$n],'duplicate_line'=>$lineNo); else $seen[$n]=$lineNo; } }
}
check($checks,'GOVERNANCE_NUMBERED_HEADINGS',count($headingErrors)===0,array('duplicates'=>$headingErrors));

// 6. stale active legacy semantic paths/names
$staleTokens=array(
 'docs/watchlist/strategy/','docs/watchlist/governance/','docs/watchlist/implementation/','docs/watchlist/audit/',
 '01_WS_IMPLEMENTATION_SCOPE_AND_BOUNDARY.md','02_WS_MODULE_MAPPING.md','03_WS_RUNTIME_ARTIFACT_FLOW.md','04_WS_API_GUIDANCE.md','05A_WS_CANONICAL_FIELD_MATRIX.md','05_WS_PERSISTENCE_GUIDANCE.md','06_WS_TEST_IMPLEMENTATION_GUIDANCE.md','07_WS_DELIVERY_CHECKLIST.md','21_WS_IMPLEMENTATION_BLUEPRINT.md',
 '03_WS_DATA_MODEL_MARIADB.md','04_WS_PARAMSET_JSON_CONTRACT.md','05_WS_PARAMETER_REGISTRY_COMPLETE.md','06_WS_PARAMSET_VALIDATOR_SPEC.md','07_WS_REASON_CODES_AND_HASH.md','23_WS_RECOMMENDATION_INPUT_OUTPUT_CONTRACT.md','25_WS_RECOMMENDATION_REASON_CODES_AND_TESTS.md','13_WS_CONTRACT_TEST_CHECKLIST.md','14_WS_BT_COVERAGE_MATRIX_LOCKED.md','15_WS_UNIVERSE_EQUIVALENCE_CONTRACT_LOCKED.md'
);
$stale=array();
foreach ($activeMd as $p) {
    $txt=file_get_contents($p);
    foreach ($staleTokens as $token) if (strpos($txt,$token)!==false) $stale[]=array('file'=>rel($p,$repoRoot),'token'=>$token);
}
check($checks,'STALE_ACTIVE_LEGACY_REFERENCES',count($stale)===0,array('count'=>count($stale),'samples'=>array_slice($stale,0,20)));

// 7. duplicate change IDs with controlled legacy exception
$exceptionPath=$watchlistRoot . '/authority/governance/DOCUMENT_INTEGRITY_EXCEPTION_REGISTRY.json';
$exceptions=array();
if (is_file($exceptionPath)) { $ex=json_decode(file_get_contents($exceptionPath),true); if(is_array($ex)&&isset($ex['exceptions'])) foreach($ex['exceptions'] as $e) $exceptions[]=$e; }
$log=$watchlistRoot . '/authority/governance/DOCUMENT_CHANGE_LOG.md'; $ids=array();
foreach(file($log) as $line) if(preg_match('/^##\s+(DOC-CHG-\d{8}-\d{3})\b/',$line,$m)){ if(!isset($ids[$m[1]]))$ids[$m[1]]=0; $ids[$m[1]]++; }
$dupErrors=array();
foreach($ids as $id=>$count) if($count>1){ $ok=false; foreach($exceptions as $e) if(isset($e['check'],$e['target'])&&$e['check']==='DUPLICATE_CHANGE_ID'&&$e['target']===$id){$ok=true;$legacyExceptionsUsed[]=$e['exception_id'];break;} if(!$ok)$dupErrors[]=array('id'=>$id,'count'=>$count); }
check($checks,'DUPLICATE_CHANGE_IDS',count($dupErrors)===0,array('unregistered_duplicates'=>$dupErrors,'registered_exceptions'=>$legacyExceptionsUsed));

// 8. traceability matrix consistency
$matrix=$watchlistRoot . '/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';
$matrixErrors=array(); $rowCount=0; $ruleIds=array();
$fh=fopen($matrix,'r'); $headers=fgetcsv($fh); if (isset($headers[0])) $headers[0]=preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]); $idx=array_flip($headers);
while(($row=fgetcsv($fh))!==false){ $rowCount++; $r=array(); foreach($headers as $i=>$h)$r[$h]=isset($row[$i])?$row[$i]:''; $id=$r['rule_id']; if(isset($ruleIds[$id]))$matrixErrors[]=array('row'=>$rowCount+1,'error'=>'duplicate rule_id','id'=>$id); $ruleIds[$id]=1; $owner=$watchlistRoot . '/' . $r['strategy_owner']; if(!is_file($owner)){ $matrixErrors[]=array('row'=>$rowCount+1,'error'=>'missing strategy owner','path'=>$r['strategy_owner']); continue; } $ln=(int)$r['source_line']; $lines=file($owner,FILE_IGNORE_NEW_LINES); if($ln<1||$ln>count($lines)){ $matrixErrors[]=array('row'=>$rowCount+1,'error'=>'invalid source_line'); continue; } if(strpos($lines[$ln-1],$r['rule_text'])===false)$matrixErrors[]=array('row'=>$rowCount+1,'error'=>'source clause mismatch','id'=>$id); if(strtoupper(sha1($r['rule_text']))!==strtoupper($r['rule_fingerprint_sha1']))$matrixErrors[]=array('row'=>$rowCount+1,'error'=>'fingerprint mismatch','id'=>$id); }
fclose($fh);
check($checks,'TRACEABILITY_MATRIX',count($matrixErrors)===0,array('rows'=>$rowCount,'errors'=>array_slice($matrixErrors,0,20)));

// 9. stage register lifecycle vocabulary
$allowed=array('NOT_STARTED','IN_PROGRESS','REMEDIATION_IN_PROGRESS','WAITING_VERIFIED_DEPENDENCY','VALIDATION','NOT_REQUESTED_OPTIONAL','DONE','CLOSED_UNRESOLVED_WITH_EVIDENCE','SUPERSEDED_BY_SUCCESSOR','SUPERSEDED_BY_DECOMPOSITION');
$reg=$watchlistRoot . '/development/implementation/WS_IMPLEMENTATION_STAGE_REGISTER.md'; $stateErrors=array();
foreach(file($reg) as $line){ if(preg_match('/^\|\s*`(WS-B\d{2})`\s*\|.*?\|\s*`([A-Z0-9_]+)`\s*\|/',$line,$m)){ if(!in_array($m[2],$allowed,true))$stateErrors[]=array('stage'=>$m[1],'state'=>$m[2]); } }
check($checks,'STAGE_LIFECYCLE_VOCABULARY',count($stateErrors)===0,array('errors'=>$stateErrors));

// 10. optional baseline-aware checks
$baselineArg=argValue('baseline'); $baselineErrors=array(); $baselineChecked=false;
if($baselineArg){ $baselineChecked=true; $bp=$baselineArg; if(!preg_match('/^(?:[A-Za-z]:[\\\\\/]|\/)/',$bp))$bp=$repoRoot . DIRECTORY_SEPARATOR . str_replace('/',DIRECTORY_SEPARATOR,$bp); if(!is_file($bp))$baselineErrors[]='baseline file missing'; else { $b=json_decode(file_get_contents($bp),true); if(!is_array($b))$baselineErrors[]='baseline JSON invalid'; else { foreach(array('record_id','baseline_id','stage_id','attempt_id','baseline_mode','strategy_authority_lock','governance_authority_lock','traceability_matrix') as $k) if(!array_key_exists($k,$b))$baselineErrors[]='missing field '.$k; if(isset($b['baseline_mode'])&&!in_array($b['baseline_mode'],array('IMPLEMENTATION','AUDIT_READONLY','DOCUMENTATION_GOVERNANCE'),true))$baselineErrors[]='invalid baseline_mode'; foreach(array('strategy_authority_lock','governance_authority_lock','market_data_contract_lock') as $k){ if(isset($b[$k])&&is_array($b[$k])) foreach($b[$k] as $entry){ if(!isset($entry['path'],$entry['sha1'])){$baselineErrors[]='invalid '.$k.' entry';continue;} $fp=$repoRoot . DIRECTORY_SEPARATOR . str_replace('/',DIRECTORY_SEPARATOR,$entry['path']); if(!is_file($fp))$baselineErrors[]='locked file missing '.$entry['path']; elseif(sha1Upper($fp)!==strtoupper($entry['sha1']))$baselineErrors[]='BASELINE_DRIFT '.$entry['path']; } } if(isset($b['traceability_matrix']['path'],$b['traceability_matrix']['sha1'])){ $mp=$repoRoot . DIRECTORY_SEPARATOR . str_replace('/',DIRECTORY_SEPARATOR,$b['traceability_matrix']['path']); if(!is_file($mp))$baselineErrors[]='matrix missing'; elseif(sha1Upper($mp)!==strtoupper($b['traceability_matrix']['sha1']))$baselineErrors[]='BASELINE_DRIFT traceability matrix'; } } } }
check($checks,'BASELINE_LOCK',!$baselineChecked || count($baselineErrors)===0,array('checked'=>$baselineChecked,'errors'=>$baselineErrors));

$failed=array(); foreach($checks as $c) if($c['status']==='FAIL')$failed[]=$c['check'];
$verdict=count($failed)?'FAIL':(count($legacyExceptionsUsed)?'PASS_WITH_REGISTERED_LEGACY_EXCEPTION':'PASS');
$report=array('status'=>$verdict,'generated_at'=>date(DATE_ATOM),'watchlist_root'=>rel($watchlistRoot,$repoRoot),'checks'=>$checks,'failed_checks'=>$failed,'registered_legacy_exceptions_used'=>array_values(array_unique($legacyExceptionsUsed)));
$out=argValue('output');
if($out){ $op=$out; if(!preg_match('/^(?:[A-Za-z]:[\\\\\/]|\/)/',$op))$op=$repoRoot . DIRECTORY_SEPARATOR . str_replace('/',DIRECTORY_SEPARATOR,$op); $d=dirname($op); if(!is_dir($d))mkdir($d,0777,true); file_put_contents($op,json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL); }
echo json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL;
exit(count($failed)?1:0);

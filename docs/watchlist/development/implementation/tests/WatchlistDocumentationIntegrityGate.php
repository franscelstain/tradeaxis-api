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
    // append-oriented historical ledgers preserve old path strings by design
    if (in_array(basename($p),array('LUMEN_IMPLEMENTATION_STATUS.md','LUMEN_CONTRACT_TRACKER.md'),true)) continue;
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
    // append-oriented historical ledgers preserve old path strings by design
    if (in_array(basename($p),array('LUMEN_IMPLEMENTATION_STATUS.md','LUMEN_CONTRACT_TRACKER.md'),true)) continue;
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

// 9A. legacy source normalization integrity
$legacyErrors=array(); $sourceCount=0; $splitCount=0; $removedSplitCount=0; $deduplicatedCount=0; $retainedCount=0;
$sourceIndex=$watchlistRoot . '/records/history/LEGACY_SOURCE_INDEX.csv';
$splitIndex=$watchlistRoot . '/records/history/LEGACY_SPLIT_INDEX.csv';
$reconIndex=$watchlistRoot . '/records/history/LEGACY_SPLIT_RECONSTRUCTION_INDEX.csv';
$sectionIndex=$watchlistRoot . '/records/history/LEGACY_SECTION_ROLE_AUDIT.csv';
$sourceIds=array(); $sourcePaths=array(); $archivePaths=array(); $sourceRows=array();
if(!is_file($sourceIndex)) $legacyErrors[]='missing LEGACY_SOURCE_INDEX.csv';
else {
  $fh=fopen($sourceIndex,'r'); $h=fgetcsv($fh); if(isset($h[0]))$h[0]=preg_replace('/^\xEF\xBB\xBF/','',$h[0]);
  while(($row=fgetcsv($fh))!==false){
    $sourceCount++; $r=array(); foreach($h as $i=>$k)$r[$k]=isset($row[$i])?$row[$i]:'';
    $sid=$r['source_id']; if(isset($sourceIds[$sid]))$legacyErrors[]='duplicate legacy source_id '.$sid; $sourceIds[$sid]=1;
    if(isset($sourcePaths[$r['original_path']]))$legacyErrors[]='duplicate original_path '.$r['original_path']; $sourcePaths[$r['original_path']]=1;
    $policy=isset($r['source_storage_policy'])?$r['source_storage_policy']:'';
    if(strpos($policy,'REMOVED_AFTER_FULL_SPLIT')===0){
      $removedSplitCount++;
      if(!empty($r['archived_source_path']))$legacyErrors[]='split source still has archived_source_path '.$sid;
      if(($r['split_coverage_status']??'')!=='FULL_100_PERCENT_SEALED')$legacyErrors[]='split source not fully sealed '.$sid;
      if(empty($r['split_mapping_sha1']))$legacyErrors[]='split source missing mapping hash '.$sid;
    } elseif($policy==='DEDUPLICATED_TO_ROLE_PRIMARY'){
      $deduplicatedCount++;
      if(!empty($r['archived_source_path']))$legacyErrors[]='deduplicated source still has archived_source_path '.$sid;
      $pp=$r['final_primary_path']??'';
      if(empty($pp)){$legacyErrors[]='deduplicated source missing final_primary_path '.$sid; continue;}
      $primary=$watchlistRoot . '/' . $pp;
      if(!is_file($primary)){$legacyErrors[]='deduplicated primary missing '.$sid.' -> '.$pp; continue;}
      if(strtoupper(sha1_file($primary))!==strtoupper($r['original_sha1']))$legacyErrors[]='deduplicated primary SHA mismatch '.$sid;
    } else {
      $retainedCount++;
      if(empty($r['archived_source_path'])){$legacyErrors[]='retained source path missing '.$sid; continue;}
      if(isset($archivePaths[$r['archived_source_path']]))$legacyErrors[]='duplicate archived_source_path '.$r['archived_source_path']; $archivePaths[$r['archived_source_path']]=1;
      $ap=$watchlistRoot . '/' . $r['archived_source_path']; if(!is_file($ap)){$legacyErrors[]='missing retained legacy source '.$sid; continue;}
      if(strtoupper(sha1_file($ap))!==strtoupper($r['original_sha1']))$legacyErrors[]='retained legacy SHA mismatch '.$sid;
    }
    $sourceRows[$sid]=$r;
  } fclose($fh);
}
$splitIds=array(); $splitRowsBySource=array();
if(!is_file($splitIndex)) $legacyErrors[]='missing LEGACY_SPLIT_INDEX.csv';
else {
  $fh=fopen($splitIndex,'r'); $h=fgetcsv($fh); if(isset($h[0]))$h[0]=preg_replace('/^\xEF\xBB\xBF/','',$h[0]);
  while(($row=fgetcsv($fh))!==false){
    $splitCount++; $r=array(); foreach($h as $i=>$k)$r[$k]=isset($row[$i])?$row[$i]:'';
    $xid=$r['extract_id']; if(isset($splitIds[$xid]))$legacyErrors[]='duplicate extract_id '.$xid; $splitIds[$xid]=1;
    if(!isset($sourceRows[$r['source_id']]))$legacyErrors[]='extract source missing '.$xid.' -> '.$r['source_id'];
    if(!isset($splitRowsBySource[$r['source_id']]))$splitRowsBySource[$r['source_id']]=array(); $splitRowsBySource[$r['source_id']][]=$r;
    $ep=$watchlistRoot . '/' . $r['extract_path']; if(!is_file($ep)){$legacyErrors[]='missing legacy extract '.$xid; continue;}
    $txt=file_get_contents($ep);
    if(strpos($txt,'`'.$xid.'`')===false)$legacyErrors[]='extract metadata ID mismatch '.$xid;
    if(strpos($txt,'`'.$r['source_id'].'`')===false)$legacyErrors[]='extract source metadata mismatch '.$xid;
    $marker="\n---\n\n"; $pos=strpos($txt,$marker);
    if($pos===false){$legacyErrors[]='extract body marker missing '.$xid;}
    else { $body=substr($txt,$pos+strlen($marker)); if(strtoupper(sha1($body))!==strtoupper($r['extract_body_sha1']))$legacyErrors[]='extract body hash mismatch '.$xid; }
    if((strpos($r['extract_path'],'development/research/')===0||strpos($r['extract_path'],'development/findings/')===0||strpos($r['extract_path'],'development/implementation/')===0))$legacyErrors[]='historical extract leaked into active development '.$xid;
  } fclose($fh);
}
// Verify fully removed split sources have 100% non-overlapping registered line coverage and stable mapping hash.
foreach($sourceRows as $sid=>$s){
  if(strpos(($s['source_storage_policy']??''),'REMOVED_AFTER_FULL_SPLIT')!==0)continue;
  $total=(int)$s['lines']; $counts=array(); for($i=1;$i<=$total;$i++)$counts[$i]=0; $mapItems=array();
  $rowsForSource=($splitRowsBySource[$sid]??array());
  usort($rowsForSource,function($a,$b){
    preg_match('/L(\d+)-L(\d+)/',$a['source_ranges'],$ma); preg_match('/L(\d+)-L(\d+)/',$b['source_ranges'],$mb);
    return ((int)($ma[1]??PHP_INT_MAX)) <=> ((int)($mb[1]??PHP_INT_MAX));
  });
  foreach($rowsForSource as $r){
    if(preg_match_all('/L(\d+)-L(\d+)/',$r['source_ranges'],$mm,PREG_SET_ORDER)) foreach($mm as $m){$a=(int)$m[1];$b=(int)$m[2];if($a<1||$b<$a||$b>$total){$legacyErrors[]='invalid sealed split range '.$sid.' '.$a.'-'.$b;continue;}for($i=$a;$i<=$b;$i++)$counts[$i]++;}
    $mapItems[]=implode('|',array($r['extract_id'],$r['extract_role'],$r['source_ranges'],$r['extract_body_sha1'],$r['extract_path']));
  }
  foreach($counts as $ln=>$c){if($c!==1){$legacyErrors[]='sealed split coverage violation '.$sid.' line '.$ln.' count='.$c;break;}}
  $mapHash=strtoupper(sha1(implode("\n",$mapItems)."\n"));
  if(empty($s['split_mapping_sha1']))$legacyErrors[]='sealed split missing mapping sha '.$sid;
  elseif($mapHash!==strtoupper($s['split_mapping_sha1']))$legacyErrors[]='sealed split mapping hash mismatch '.$sid;
}
if(!is_file($reconIndex))$legacyErrors[]='missing LEGACY_SPLIT_RECONSTRUCTION_INDEX.csv';
check($checks,'LEGACY_SOURCE_NORMALIZATION',count($legacyErrors)===0,array('source_count'=>$sourceCount,'retained_source_count'=>$retainedCount,'exact_deduplicated_sources'=>$deduplicatedCount,'removed_fully_split_sources'=>$removedSplitCount,'split_extract_count'=>$splitCount,'errors'=>array_slice($legacyErrors,0,30)));

// 9B. one document, one authoritative role
$roleErrors=array(); $roleRows=0; $roleSeen=array();
$roleRegistry=$watchlistRoot . '/authority/governance/DOCUMENT_ROLE_REGISTRY.csv';
$allowedRoles=array('STRATEGY','GOVERNANCE','IMPLEMENTATION_CONTRACT','IMPLEMENTATION_GUIDE','IMPLEMENTATION_TEST','IMPLEMENTATION_TOOL','IMPLEMENTATION_DB','STATUS_LEDGER','STAGE_REGISTER','GENERATED_SUMMARY','RESEARCH','FINDING','EVIDENCE','DECISION','HISTORY','LEGACY_SOURCE','NAVIGATION','REGISTRY','TEMPLATE','EXAMPLE');
$registeredPaths=array();
if(!is_file($roleRegistry)) $roleErrors[]='missing DOCUMENT_ROLE_REGISTRY.csv';
else {
  $fh=fopen($roleRegistry,'r'); $h=fgetcsv($fh); if(isset($h[0]))$h[0]=preg_replace('/^\xEF\xBB\xBF/','',$h[0]);
  while(($row=fgetcsv($fh))!==false){
    $roleRows++; $r=array(); foreach($h as $i=>$k)$r[$k]=isset($row[$i])?$row[$i]:'';
    $dp=$r['document_path']??''; $role=$r['document_role']??'';
    if($dp===''){ $roleErrors[]='empty document_path row '.$roleRows; continue; }
    if(isset($roleSeen[$dp]))$roleErrors[]='duplicate role registry path '.$dp; $roleSeen[$dp]=1; $registeredPaths[$dp]=1;
    if(!in_array($role,$allowedRoles,true))$roleErrors[]='invalid/non-scalar role '.$dp.' -> '.$role;
    if(preg_match('/[;,|+]/',$role))$roleErrors[]='multi-role token prohibited '.$dp.' -> '.$role;
    $fp=$watchlistRoot . '/' . str_replace('/',DIRECTORY_SEPARATOR,$dp); if(!is_file($fp))$roleErrors[]='registered document missing '.$dp;
    // Physical area consistency. References do not change the primary role.
    if(strpos($dp,'authority/strategy/')===0 && basename($dp)!=='README.md' && $role!=='STRATEGY')$roleErrors[]='strategy path role mismatch '.$dp.' -> '.$role;
    if(strpos($dp,'development/research/')===0 && basename($dp)!=='README.md' && $role!=='RESEARCH')$roleErrors[]='research path role mismatch '.$dp.' -> '.$role;
    if(strpos($dp,'development/findings/')===0 && basename($dp)!=='README.md' && $role!=='FINDING')$roleErrors[]='finding path role mismatch '.$dp.' -> '.$role;
    if(strpos($dp,'records/evidence/')===0 && basename($dp)!=='README.md' && $role!=='EVIDENCE')$roleErrors[]='evidence path role mismatch '.$dp.' -> '.$role;
    if(strpos($dp,'records/decisions/')===0 && basename($dp)!=='README.md' && $role!=='DECISION')$roleErrors[]='decision path role mismatch '.$dp.' -> '.$role;
    if(strpos($dp,'records/history/original_sources/')===0 && $role!=='LEGACY_SOURCE')$roleErrors[]='legacy source role mismatch '.$dp.' -> '.$role;
  } fclose($fh);
}
// Registry must cover every physical Watchlist file exactly once, including itself.
foreach(filesRecursive($watchlistRoot) as $p){ $rp=rel($p,$watchlistRoot); if(!isset($registeredPaths[$rp]))$roleErrors[]='unregistered physical document '.$rp; }
// Retained legacy sources must be semantic-role pure. Multi-role source containers cannot use bundle exceptions.
$legacySemanticRoles=array();
if(is_file($sectionIndex)){
  $fh=fopen($sectionIndex,'r'); $h=fgetcsv($fh); if(isset($h[0]))$h[0]=preg_replace('/^\xEF\xBB\xBF/','',$h[0]);
  while(($row=fgetcsv($fh))!==false){ $r=array(); foreach($h as $i=>$k)$r[$k]=isset($row[$i])?$row[$i]:''; if(($r['role']??'')==='CONTEXT')continue; $sid=$r['source_id']; if(!isset($legacySemanticRoles[$sid]))$legacySemanticRoles[$sid]=array(); $legacySemanticRoles[$sid][$r['role']]=1; }
  fclose($fh);
}
foreach($sourceRows as $sid=>$s){ if(!empty($s['archived_source_path']) && count($legacySemanticRoles[$sid]??array())>1)$roleErrors[]='retained multi-role legacy source '.$sid.' roles='.implode(',',array_keys($legacySemanticRoles[$sid])); }
$bundleIndex=$watchlistRoot . '/records/history/LEGACY_BUNDLE_EXCEPTION_INDEX.csv';
if(is_file($bundleIndex)){
  $fh=fopen($bundleIndex,'r'); $h=fgetcsv($fh); if(isset($h[0]))$h[0]=preg_replace('/^\xEF\xBB\xBF/','',$h[0]);
  while(($row=fgetcsv($fh))!==false){ $r=array(); foreach($h as $i=>$k)$r[$k]=isset($row[$i])?$row[$i]:''; $sid=$r['source_id']??''; if(count($legacySemanticRoles[$sid]??array())>1)$roleErrors[]='multi-role bundle exception prohibited '.$sid; }
  fclose($fh);
}
check($checks,'ONE_DOCUMENT_ONE_AUTHORITATIVE_ROLE',count($roleErrors)===0,array('registry_rows'=>$roleRows,'registered_paths'=>count($registeredPaths),'errors'=>array_slice($roleErrors,0,40)));


// 9C. current verification rebaseline/status registry
$verificationErrors=array();
$verificationRegistry=$watchlistRoot . '/authority/governance/CURRENT_VERIFICATION_REGISTRY.csv';
$verificationEpochFile=$watchlistRoot . '/authority/governance/CURRENT_VERIFICATION_EPOCH.json';
$currentEpoch='';
if(!is_file($verificationEpochFile))$verificationErrors[]='missing CURRENT_VERIFICATION_EPOCH.json';
else { $ep=json_decode(file_get_contents($verificationEpochFile),true); if(!is_array($ep)||empty($ep['verification_epoch']))$verificationErrors[]='invalid CURRENT_VERIFICATION_EPOCH.json'; else $currentEpoch=$ep['verification_epoch']; }
$vrPaths=array();$vrRows=0;
if(!is_file($verificationRegistry))$verificationErrors[]='missing CURRENT_VERIFICATION_REGISTRY.csv';
else {
  $fh=fopen($verificationRegistry,'r');$h=fgetcsv($fh);if(isset($h[0]))$h[0]=preg_replace('/^\xEF\xBB\xBF/','',$h[0]);
  while(($row=fgetcsv($fh))!==false){$vrRows++;$r=array();foreach($h as $i=>$k)$r[$k]=isset($row[$i])?$row[$i]:'';$p=$r['document_path']??'';
    if($p===''){ $verificationErrors[]='empty verification registry path row '.$vrRows; continue; }
    if(isset($vrPaths[$p]))$verificationErrors[]='duplicate verification registry path '.$p;$vrPaths[$p]=1;
    if(($r['verification_epoch']??'')!==$currentEpoch)$verificationErrors[]='verification epoch mismatch '.$p;
    if(($r['current_proof_eligible']??'')==='YES' && !preg_match('~^records/evidence/(?:runs/)?(?:E|SC)-WS-B~',$p))$verificationErrors[]='non-current-work document marked proof eligible '.$p;
    if(($r['legacy_origin']??'')==='YES' && ($r['current_proof_eligible']??'')==='YES')$verificationErrors[]='legacy-origin document marked current proof eligible '.$p;
    if(($r['current_verification_status']??'')==='NOT_ASSESSED_REVALIDATION_REQUIRED' && ($r['revalidation_required']??'')!=='YES')$verificationErrors[]='unverified implementation missing revalidation flag '.$p;
  } fclose($fh);
}
foreach(filesRecursive($watchlistRoot) as $p){$rp=rel($p,$watchlistRoot);if(!isset($vrPaths[$rp]))$verificationErrors[]='physical document missing verification registry row '.$rp;}
foreach($vrPaths as $rp=>$_){if(!is_file($watchlistRoot.'/'.$rp))$verificationErrors[]='verification registry points missing document '.$rp;}
check($checks,'CURRENT_VERIFICATION_REBASELINE',count($verificationErrors)===0,array('epoch'=>$currentEpoch,'registry_rows'=>$vrRows,'errors'=>array_slice($verificationErrors,0,40)));

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

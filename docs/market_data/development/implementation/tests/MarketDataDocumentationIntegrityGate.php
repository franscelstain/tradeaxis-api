<?php
/** PHP 7.3+ documentation architecture gate. */
function norm($p){return str_replace('\\','/',$p);} function rec($d){$o=array();$it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($d,FilesystemIterator::SKIP_DOTS));foreach($it as $f)if($f->isFile())$o[]=$f->getPathname();sort($o);return $o;} function add(&$c,$n,$ok,$d=array()){$c[]=array('check'=>$n,'status'=>$ok?'PASS':'FAIL','details'=>$d);} function csvrows($p){$o=array();$f=fopen($p,'r');$h=fgetcsv($f);if(isset($h[0]))$h[0]=preg_replace('/^\xEF\xBB\xBF/','',$h[0]);while(($r=fgetcsv($f))!==false){$a=array();foreach($h as $i=>$k)$a[$k]=isset($r[$i])?trim($r[$i]):'';$o[]=$a;}fclose($f);return $o;}

function documentIdRegistryErrors($rows,$physical,$roleByPath){
  $errors=array();$ids=array();$paths=array();
  foreach($rows as $r){
    $id=isset($r['document_id'])?trim($r['document_id']):'';$path=isset($r['document_path'])?trim($r['document_path']):'';$role=isset($r['document_role'])?trim($r['document_role']):'';
    if(!preg_match('/^MD-DOC-\d{5}$/',$id))$errors[]='invalid document_id '.$id.' for '.$path;
    if(isset($ids[$id]))$errors[]='duplicate document_id '.$id;else$ids[$id]=1;
    if($path===''){$errors[]='empty document_path for '.$id;continue;}
    if(isset($paths[$path]))$errors[]='duplicate document_path '.$path;else$paths[$path]=1;
    if(!isset($roleByPath[$path]))$errors[]='ID registry path has no role registration '.$path;
    elseif($roleByPath[$path]!==$role)$errors[]='role mismatch '.$path.' id='.$role.' role_registry='.$roleByPath[$path];
  }
  $registered=array_keys($paths);sort($registered);$expected=$physical;sort($expected);
  foreach(array_diff($expected,$registered) as $p)$errors[]='missing document id '.$p;
  foreach(array_diff($registered,$expected) as $p)$errors[]='extra document id '.$p;
  return $errors;
}
function stageRegisterShapeErrors($text){
  $errors=array();$expected=array();for($i=0;$i<=22;$i++)$expected[]='MD-B'.str_pad((string)$i,2,'0',STR_PAD_LEFT);
  preg_match_all('/^\| `(MD-B\d{2})` \|/m',$text,$m);$observed=isset($m[1])?$m[1]:array();
  if($observed!==$expected)$errors[]='stage rows are not exactly ordered MD-B00..MD-B22';
  $header='| Stage | Maps to frozen scope | Lifecycle state | Verdict | Latest attempt / Work ID | Baseline ID | Strategy coverage | Residue state | Integrity gate | Dependency | Open finding | Closure manifest | Resume from |';
  if(substr_count($text,$header)!==1)$errors[]='canonical stage-register header missing or duplicated';
  $resumeCount=substr_count($text,'**Single exact next executable resume point:**');
  if($resumeCount!==1)$errors[]='single exact resume point count is '.$resumeCount;
  $allowed=array('DONE'=>1,'IN_PROGRESS'=>1,'NOT_STARTED'=>1);$rows=preg_split('/\R/',$text);
  foreach($rows as $line){if(!preg_match('/^\| `(MD-B\d{2})` \|/',$line,$mm))continue;$cols=explode('|',$line);$life=isset($cols[3])?trim($cols[3]," `\t\r\n"):'';if(!isset($allowed[$life]))$errors[]='invalid lifecycle '.$mm[1].'='.$life;}
  return $errors;
}
$md=realpath(dirname(__DIR__,3));$checks=array();$files=rec($md);
$missing=array();foreach(array('authority/strategy','authority/governance','development/implementation','development/research','development/findings','records/evidence','records/decisions','records/history') as $d)if(!is_dir($md.'/'.$d))$missing[]=$d;add($checks,'ROOT_ARCHITECTURE',!$missing,array('missing'=>$missing));
// registries complete + one role
$rr=csvrows($md.'/authority/governance/DOCUMENT_ROLE_REGISTRY.csv');$seen=array();$dup=array();foreach($rr as $r){if(isset($seen[$r['document_path']]))$dup[]=$r['document_path'];$seen[$r['document_path']]=1;} $physical=array();foreach($files as $p)$physical[]=substr(norm($p),strlen(norm($md))+1);sort($physical);$registered=array_keys($seen);sort($registered);$miss=array_values(array_diff($physical,$registered));$extra=array_values(array_diff($registered,$physical));add($checks,'ONE_DOCUMENT_ONE_ROLE',!$dup&&!$miss&&!$extra,array('physical'=>count($physical),'registry'=>count($registered),'duplicates'=>$dup,'missing'=>array_slice($miss,0,20),'extra'=>array_slice($extra,0,20)));
// complete + unique document identity registry, with role consistency
$idr=csvrows($md.'/authority/governance/DOCUMENT_ID_REGISTRY.csv');$roleByPath=array();foreach($rr as $r)$roleByPath[$r['document_path']]=$r['document_role'];$idErr=documentIdRegistryErrors($idr,$physical,$roleByPath);add($checks,'DOCUMENT_ID_REGISTRY_INTEGRITY',!$idErr,array('physical'=>count($physical),'registry'=>count($idr),'errors'=>array_slice($idErr,0,20)));
// canonical stage-register shape and single-resume invariant
$stageText=file_get_contents($md.'/development/implementation/MD_IMPLEMENTATION_STAGE_REGISTER.md');$stageErr=stageRegisterShapeErrors($stageText);add($checks,'STAGE_REGISTER_SHAPE',!$stageErr,array('stage_rows'=>preg_match_all('/^\| `MD-B\d{2}` \|/m',$stageText,$unused),'errors'=>array_slice($stageErr,0,20)));
// strategy freeze
$mf=json_decode(file_get_contents($md.'/authority/governance/MARKET_DATA_STRATEGY_FREEZE_MANIFEST.json'),true);$bad=array();foreach($mf['documents'] as $d){$p=$md.'/'.$d['current_path'];if(!is_file($p)||strtoupper(sha1_file($p))!==strtoupper($d['sha1']))$bad[]=$d['current_path'];}add($checks,'STRATEGY_FREEZE',!$bad,array('registered'=>count($mf['documents']),'mismatches'=>$bad));
// verification registry complete and legacy proof zero
$vr=csvrows($md.'/authority/governance/CURRENT_VERIFICATION_REGISTRY.csv');$vseen=array();$vdup=array();$legacyProof=array();foreach($vr as $r){if(isset($vseen[$r['document_path']]))$vdup[]=$r['document_path'];$vseen[$r['document_path']]=1;if($r['legacy_origin']==='YES'&&$r['document_role']!=='STRATEGY'&&$r['current_proof_eligible']==='YES')$legacyProof[]=$r['document_path'];}$vmiss=array_values(array_diff($physical,array_keys($vseen)));$vextra=array_values(array_diff(array_keys($vseen),$physical));add($checks,'CURRENT_VERIFICATION_REBASELINE',!$vmiss&&!$vextra&&!$vdup&&!$legacyProof,array('registry'=>count($vr),'missing'=>array_slice($vmiss,0,20),'extra'=>array_slice($vextra,0,20),'duplicates'=>array_slice($vdup,0,20),'legacy_current_proof'=>$legacyProof));
// JSON CSV parse
$je=array();$ce=array();foreach($files as $p){if(substr($p,-5)==='.json'){json_decode(file_get_contents($p),true);if(json_last_error()!==JSON_ERROR_NONE)$je[]=norm($p);}elseif(substr($p,-4)==='.csv'){$f=fopen($p,'r');$h=fgetcsv($f);$n=is_array($h)?count($h):0;$ln=1;while(($r=fgetcsv($f))!==false){$ln++;if(count($r)!==$n){$ce[]=norm($p).':'.$ln;break;}}fclose($f);}}add($checks,'JSON_PARSE',!$je,array('errors'=>$je));add($checks,'CSV_STRUCTURE',!$ce,array('errors'=>$ce));
// active markdown links (only explicit links, skip history)
$broken=array();foreach($files as $p){$rp=substr(norm($p),strlen(norm($md))+1);if(substr($p,-3)!=='.md'||strpos($rp,'records/history/')===0)continue;$txt=file_get_contents($p);if(!preg_match_all('/\[[^\]]*\]\(([^)]+)\)/',$txt,$m))continue;foreach($m[1] as $t){$t=trim($t);if($t===''||$t[0]==='#'||preg_match('/^[a-z]+:\/\//i',$t)||strpos($t,'mailto:')===0)continue;$t=preg_replace('/#.*/','',$t);$res=dirname($p).'/'.rawurldecode($t);if(!file_exists($res))$broken[]=array('file'=>$rp,'target'=>$t);}}add($checks,'ACTIVE_MARKDOWN_LINKS',!$broken,array('broken_count'=>count($broken),'samples'=>array_slice($broken,0,20)));
// traceability fingerprints
$tr=csvrows($md.'/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv');$te=array();$ids=array();foreach($tr as $r){if(isset($ids[$r['rule_id']])){$te[]='duplicate '.$r['rule_id'];continue;}$ids[$r['rule_id']]=1;$p=$md.'/'.$r['strategy_owner'];if(!is_file($p)){$te[]='missing owner '.$r['strategy_owner'];continue;}$lines=file($p,FILE_IGNORE_NEW_LINES);$ln=(int)$r['source_line'];if($ln<1||$ln>count($lines)||strpos(trim($lines[$ln-1]),$r['rule_text'])===false)$te[]='source mismatch '.$r['rule_id'];if(strtoupper(sha1($r['rule_text']))!==strtoupper($r['rule_fingerprint_sha1']))$te[]='fingerprint '.$r['rule_id'];}add($checks,'TRACEABILITY_MATRIX',!$te,array('rows'=>count($tr),'errors'=>array_slice($te,0,20)));
// legacy semantic split integrity
$lsi=$md.'/records/history/LEGACY_SOURCE_INDEX.csv';$lsp=$md.'/records/history/LEGACY_SPLIT_INDEX.csv';$lda=$md.'/records/history/LEGACY_DOCUMENT_ROLE_AUDIT.csv';$splitErr=array();$splitCount=0;$reconstructed=0;
if(is_file($lsi)&&is_file($lsp)&&is_file($lda)){
  $sources=csvrows($lsi);$splits=csvrows($lsp);$docs=csvrows($lda);$bySource=array();foreach($splits as $r){$bySource[$r['source_id']][]=$r;}
  foreach($sources as $src){if(isset($src['semantic_audit_status'])&&$src['semantic_audit_status']==='FULL_SPLIT_SEALED'){
    $splitCount++;if(trim($src['current_primary_path'])!=='')$splitErr[]='split source still has primary '.$src['legacy_source_id'];
    if(!isset($bySource[$src['legacy_source_id']])){$splitErr[]='missing extracts '.$src['legacy_source_id'];continue;}
    $parts=$bySource[$src['legacy_source_id']];usort($parts,function($a,$b){return ((int)$a['source_start_line'])<=>((int)$b['source_start_line']);});$expected=1;$bodyAll='';
    foreach($parts as $x){$a=(int)$x['source_start_line'];$b=(int)$x['source_end_line'];if($a!==$expected)$splitErr[]='range gap/overlap '.$src['legacy_source_id'].' expected '.$expected.' got '.$a;$expected=$b+1;$ep=$md.'/'.$x['extract_path'];if(!is_file($ep)){$splitErr[]='missing extract '.$x['extract_path'];continue;}$txt=file_get_contents($ep);$sm="<!-- LEGACY_EXTRACT_BODY_START -->\n";$em="\n<!-- LEGACY_EXTRACT_BODY_END -->";$sp=strpos($txt,$sm);$ee=$sp===false?false:strpos($txt,$em,$sp+strlen($sm));if($sp===false||$ee===false){$splitErr[]='body marker '.$x['extract_path'];continue;}$body=substr($txt,$sp+strlen($sm),$ee-($sp+strlen($sm)));if(strtoupper(sha1($body))!==strtoupper($x['extract_body_sha1']))$splitErr[]='body hash '.$x['extract_path'];$bodyAll.=$body;}
    if(strtoupper(sha1($bodyAll))!==strtoupper($src['original_sha1']))$splitErr[]='reconstruction hash '.$src['legacy_source_id'];else $reconstructed++;
  }}
  foreach($docs as $d){if($d['material_composite']==='YES'&&$d['physical_original_retained']!=='NO')$splitErr[]='composite retained '.$d['legacy_source_id'];}
}else{$splitErr[]='legacy semantic audit indexes missing';}
add($checks,'LEGACY_SEMANTIC_SPLIT_INTEGRITY',!$splitErr,array('split_sources'=>$splitCount,'reconstructed'=>$reconstructed,'errors'=>array_slice($splitErr,0,20)));
// Extract structure. F-MD-B00-A001-003: the seal above hashes only the region between the body
// markers, so text appended AFTER LEGACY_EXTRACT_BODY_END passed every gate. Appending an
// unsealed paragraph to a HISTORICAL_ONLY extract is precisely the rewrite DOCUMENT_RECORDING_STANDARD
// forbids. The fix is structural, not a wider hash: extending the hash would break the reconstruction
// proof, which must stay bound to the exact original source range.
$structErr=array();$structChecked=0;
if(isset($splits)&&is_array($splits)){
  $sm="<!-- LEGACY_EXTRACT_BODY_START -->";$em="<!-- LEGACY_EXTRACT_BODY_END -->";
  foreach($splits as $x){$rel=isset($x['extract_path'])?$x['extract_path']:'';if($rel==='')continue;$ep=$md.'/'.$rel;if(!is_file($ep)){$structErr[]='missing extract '.$rel;continue;}
    $structChecked++;$txt=file_get_contents($ep);
    if(substr_count($txt,$sm)!==1)$structErr[]='body-start marker count '.$rel;
    if(substr_count($txt,$em)!==1){$structErr[]='body-end marker count '.$rel;continue;}
    $after=substr($txt,strpos($txt,$em)+strlen($em));
    if($after!=="\n"&&$after!=='')$structErr[]='content after sealed body '.$rel;
    if(strpos($txt,'# Legacy Semantic Extract')!==0)$structErr[]='missing extract header '.$rel;
  }
  if($structChecked<400)$structErr[]='extract structure scan reached only '.$structChecked.' extracts';
}else{$structErr[]='legacy split index unavailable for structure check';}
add($checks,'LEGACY_EXTRACT_STRUCTURE',!$structErr,array('extracts_checked'=>$structChecked,'errors'=>array_slice($structErr,0,20)));
// path length and exact duplicates
$long=array();$max=0;$hash=array();$dups=array();foreach($files as $p){$rp=substr(norm($p),strlen(norm($md))+1);$l=strlen($rp);if($l>$max)$max=$l;if($l>180)$long[]=$rp;$h=strtoupper(sha1_file($p));if(isset($hash[$h]))$dups[]=array($hash[$h],$rp);else$hash[$h]=$rp;}add($checks,'WINDOWS_SAFE_PATHS',!$long,array('max_length'=>$max,'too_long'=>array_slice($long,0,20)));add($checks,'EXACT_DUPLICATE_FILES',!$dups,array('duplicates'=>array_slice($dups,0,20)));
// only canonical root dirs
$legacyRoots=array();foreach(scandir($md) as $x){if($x==='.'||$x==='..'||in_array($x,array('README.md','START_HERE.md','authority','development','records'),true))continue;$legacyRoots[]=$x;}add($checks,'NO_LEGACY_ROOT_AREAS',!$legacyRoots,array('unexpected'=>$legacyRoots));
$fail=false;foreach($checks as $c)if($c['status']==='FAIL')$fail=true;$out=array('gate'=>'MarketDataDocumentationIntegrityGate','status'=>$fail?'FAIL':'PASS','checks'=>$checks,'generated_at'=>date(DATE_ATOM));$outp=$md.'/records/evidence/MD_DOCUMENTATION_INTEGRITY_GATE_LATEST.json';file_put_contents($outp,json_encode($out,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL);echo json_encode($out,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL;exit($fail?1:0);

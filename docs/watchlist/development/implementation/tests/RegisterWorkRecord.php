<?php
/** RegisterWorkRecord.php — current/future WS-Bxx Work Record helper. PHP 7.3+. */
function opt($n,$d=null){global $argv;$p='--'.$n.'=';foreach($argv as $a)if(strpos($a,$p)===0)return substr($a,strlen($p));return $d;}
function failr($m){fwrite(STDERR,$m.PHP_EOL);exit(2);} 
function csvAssoc($path){$out=array();$fh=fopen($path,'r');$h=fgetcsv($fh);if(isset($h[0]))$h[0]=preg_replace('/^\xEF\xBB\xBF/','',$h[0]);while(($row=fgetcsv($fh))!==false){$a=array();foreach($h as $i=>$k)$a[$k]=isset($row[$i])?trim($row[$i]):'';$out[]=$a;}fclose($fh);return $out;}
function splitIds($s){$s=trim($s);if($s===''||$s==='—'||$s==='N/A')return array();return array_values(array_filter(array_map('trim',preg_split('/[;|,]+/',$s))));}

function appendCsvAssoc($path,$row){
    $exists=is_file($path);$fh=fopen($path,'a');
    if(!$exists)failr('Registry missing: '.$path);
    fputcsv($fh,$row);fclose($fh);
}

$stage=opt('stage');$attempt=opt('attempt');$record=opt('record');$type=opt('type');$baseline=opt('baseline');$status=opt('status','OPEN');$file=opt('file');
if(!$stage||!$attempt||!$record||!$type||!$file||!$baseline)failr('Required: --stage --attempt --record --type --file --baseline [--status]');
if(!preg_match('/^(WS-B\d{2}[A-Z]?)-A\d{3}$/',$attempt,$m)||$m[1]!==$stage)failr('Attempt must match stage.');
$allowedTypes=array('FINDING','DECISION','EVIDENCE','CHANGE_IMPACT_DECLARATION','STAGE_ATTEMPT_RECORD','STAGE_CLOSURE_MANIFEST');if(!in_array($type,$allowedTypes,true))failr('Unsupported current record type. WORK_BASELINE_LOCK must be created with CreateWorkBaselineLock.php.');
$wr=realpath(dirname(__DIR__,3));$repo=realpath(dirname($wr,2));$registry=$wr.'/records/WORK_RECORD_REGISTRY.csv';if(!is_file($registry))failr('Registry missing.');
$target=$file;if(!preg_match('~^(?:[A-Za-z]:[\\/]|/)~',$target))$target=$repo.'/'.str_replace('\\','/',$target);if(!is_file($target))failr('Record file missing: '.$file);
$rows=csvAssoc($registry);$records=array();$attemptBaseline=null;$attemptRecordCount=0;$closureCount=0;
foreach($rows as $r){$records[$r['record_id']]=$r;if($r['record_id']===$record)failr('Duplicate Record ID: '.$record);if($r['attempt_id']===$attempt&&$r['record_type']==='WORK_BASELINE_LOCK'){$attemptBaseline=$r['baseline_id'];}if($r['attempt_id']===$attempt&&$r['record_type']==='STAGE_ATTEMPT_RECORD')$attemptRecordCount++;if($r['attempt_id']===$attempt&&$r['record_type']==='STAGE_CLOSURE_MANIFEST')$closureCount++;}
if($attemptBaseline===null)failr('Attempt has no registered WORK_BASELINE_LOCK. Create baseline first.');if($attemptBaseline!==$baseline)failr('Baseline mismatch. Attempt is bound to '.$attemptBaseline);
if($type==='STAGE_ATTEMPT_RECORD'&&$attemptRecordCount>0)failr('Attempt already has canonical STAGE_ATTEMPT_RECORD.');if($type==='STAGE_CLOSURE_MANIFEST'&&$closureCount>0)failr('Attempt already has Stage Closure Manifest.');
$patterns=array('FINDING'=>'/^F-'.preg_quote($attempt,'/').'-\d{3}$/','DECISION'=>'/^D-'.preg_quote($attempt,'/').'-\d{3}$/','CHANGE_IMPACT_DECLARATION'=>'/^CI-'.preg_quote($attempt,'/').'-\d{3}$/','STAGE_CLOSURE_MANIFEST'=>'/^SC-'.preg_quote($attempt,'/').'-\d{3}$/');
if(isset($patterns[$type])&&!preg_match($patterns[$type],$record))failr('Record ID pattern does not match type/attempt.');if(in_array($type,array('EVIDENCE','STAGE_ATTEMPT_RECORD'),true)&&!preg_match('/^E-'.preg_quote($attempt,'/').'-\d{3}$/',$record))failr('Evidence/Attempt record ID must be correlation-first.');
$relatedF=opt('findings');$relatedE=opt('evidence');$relatedD=opt('decisions');$relatedDep=opt('dependencies');$sup=opt('supersedes');$notes=opt('notes');
foreach(splitIds($relatedF) as $x)if(strpos($x,'LEGACY:')!==0&&(!isset($records[$x])||$records[$x]['record_type']!=='FINDING'))failr('Related Finding must resolve to FINDING: '.$x);
foreach(splitIds($relatedD) as $x)if(strpos($x,'LEGACY:')!==0&&(!isset($records[$x])||$records[$x]['record_type']!=='DECISION'))failr('Related Decision must resolve to DECISION: '.$x);
$eTypes=array('EVIDENCE','WORK_BASELINE_LOCK','STAGE_ATTEMPT_RECORD','CHANGE_IMPACT_DECLARATION');foreach(splitIds($relatedE) as $x)if(strpos($x,'LEGACY:')!==0&&(!isset($records[$x])||!in_array($records[$x]['record_type'],$eTypes,true)))failr('Related Evidence must resolve to evidence-like record: '.$x);
$rel=str_replace('\\','/',realpath($target));$rr=rtrim(str_replace('\\','/',$repo),'/');if(strpos($rel,$rr.'/')===0)$rel=substr($rel,strlen($rr)+1);
$fh=fopen($registry,'a');fputcsv($fh,array($record,$type,$stage,$attempt,$attempt,$baseline,$status,$rel,$relatedF,$relatedE,$relatedD,$relatedDep,$sup,date(DATE_ATOM),$notes));fclose($fh);

// Register physical role + current verification disposition for the new Work Record.
$roleRegistry=$wr.'/authority/governance/DOCUMENT_ROLE_REGISTRY.csv';
$verificationRegistry=$wr.'/authority/governance/CURRENT_VERIFICATION_REGISTRY.csv';
$epochFile=$wr.'/authority/governance/CURRENT_VERIFICATION_EPOCH.json';
$ep=is_file($epochFile)?json_decode(file_get_contents($epochFile),true):null;$epoch=(is_array($ep)&&!empty($ep['verification_epoch']))?$ep['verification_epoch']:null;if(!$epoch)failr('Current verification epoch missing.');
$docRole='EVIDENCE';$roleScope='IMMUTABLE_RESULT_OR_PROOF';$mutability='IMMUTABLE_AFTER_ISSUE';$authority='YES';$vclass='CURRENT_WORK_RECORD';$vstatus='CURRENT_ATTEMPT_EVIDENCE';$proof='YES';$reval='NO';
if($type==='FINDING'){$docRole='FINDING';$roleScope='CURRENT_FINDING';$mutability='LIFECYCLE_UPDATE_ONLY';$vclass='CURRENT_WORK_RECORD';$vstatus='CURRENT_OPEN_FINDING';$proof='NO';}
elseif($type==='DECISION'){$docRole='DECISION';$roleScope='ISSUED_DECISION';$vclass='CURRENT_WORK_RECORD';$vstatus='CURRENT_ISSUED_DECISION';$proof='NO';}
elseif($type==='CHANGE_IMPACT_DECLARATION'){$proof='NO';$vstatus='CURRENT_ATTEMPT_SUPPORT_RECORD';}
$roleRows=csvAssoc($roleRegistry);foreach($roleRows as $x)if($x['document_path']===$rel)failr('Document already exists in role registry: '.$rel);
$vrRows=csvAssoc($verificationRegistry);foreach($vrRows as $x)if($x['document_path']===$rel)failr('Document already exists in verification registry: '.$rel);
$fh=fopen($roleRegistry,'a');fputcsv($fh,array($rel,$docRole,$roleScope,$mutability,$authority,'YES','WORK_RECORD_HELPER',''));fclose($fh);
$fh=fopen($verificationRegistry,'a');fputcsv($fh,array($rel,$docRole,$epoch,'NO',$vclass,$vstatus,$proof,$reval,'','Current WS-Bxx Work Record under active verification epoch.'));fclose($fh);

echo 'REGISTERED '.$record.' work='.$attempt.' baseline='.$baseline.PHP_EOL;

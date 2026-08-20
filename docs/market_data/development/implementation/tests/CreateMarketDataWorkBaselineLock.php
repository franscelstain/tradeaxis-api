<?php
/** PHP 7.3+; create immutable Work Baseline Lock JSON. */
function opt($n,$d=null){global $argv;$p='--'.$n.'=';foreach($argv as $a)if(strpos($a,$p)===0)return substr($a,strlen($p));return $d;}
function failx($m){fwrite(STDERR,$m.PHP_EOL);exit(2);} function sh($p){return strtoupper(sha1_file($p));}
$md=realpath(dirname(__DIR__,3)); if(!$md)failx('Cannot resolve market_data root.');
$stage=opt('stage');$attempt=opt('attempt');$baseline=opt('baseline');$revision=opt('revision','UNKNOWN');$working=opt('working-tree','UNKNOWN');$out=opt('output');
if(!$stage||!$attempt||!$baseline||!$out)failx('Required --stage --attempt --baseline --output');
if(!preg_match('/^MD-B\d{2}$/',$stage)||!preg_match('/^MD-B\d{2}-A\d{3}$/',$attempt))failx('Invalid Stage/Attempt ID.');
$ep=json_decode(file_get_contents($md.'/authority/governance/CURRENT_VERIFICATION_EPOCH.json'),true);$epoch=$ep['verification_epoch'];
$manifest=$md.'/authority/governance/MARKET_DATA_STRATEGY_FREEZE_MANIFEST.json';$matrix=$md.'/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';
$data=array('baseline_id'=>$baseline,'stage_id'=>$stage,'attempt_id'=>$attempt,'verification_epoch'=>$epoch,'strategy_freeze_manifest_sha1'=>sh($manifest),'traceability_matrix_sha1'=>sh($matrix),'source_revision'=>$revision,'working_tree'=>$working,'schema_config_identity'=>opt('schema-config','UNKNOWN'),'toolchain_identity'=>opt('toolchain',PHP_VERSION),'created_at'=>date(DATE_ATOM));
if(file_exists($out))failx('Output already exists.'); if(!is_dir(dirname($out)))mkdir(dirname($out),0777,true); file_put_contents($out,json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL);echo $out.PHP_EOL;

<?php
require_once __DIR__.'/MarketDataCanonicalRawImportProofSpec.php';
$root=dirname(__DIR__,5);$evidence=null;$apply=in_array('--apply',$argv,true);foreach($argv as $a){if(strpos($a,'--evidence-id=')===0)$evidence=substr($a,14);}if(!$evidence||!preg_match('/^E-MD-B09-A00[23]-\d{3}$/',$evidence))throw new RuntimeException('Use --evidence-id=E-MD-B09-A00[2|3]-NNN [--apply]');
if(count(glob($root.'/docs/market_data/records/evidence/'.$evidence.'_*'))!==1)throw new RuntimeException('Governed evidence must exist before binding');
$p=MarketDataCanonicalRawImportTraceabilitySpec::matrixPath($root);$h=fopen($p,'r');$headers=fgetcsv($h);$bom=strpos($headers[0],"\xEF\xBB\xBF")===0;$headers[0]=preg_replace('/^\xEF\xBB\xBF/','',$headers[0]);$rows=[];while(($r=fgetcsv($h))!==false){if(count($r)===count($headers))$rows[]=array_combine($headers,$r);}fclose($h);$map=MarketDataCanonicalRawImportProofSpec::map($root);$seen=0;
$S='MarketDataCanonicalRawImportTraceabilitySpec';
// A predicate first bound by the A003 re-check carries the A003 pair; the rest keep A002. Existing
// binding notes are stripped first, so a re-run replaces its own line instead of stacking a new one.
foreach($rows as &$r){
    if(!isset($map[$r['rule_id']]))continue;
    $seen++;
    $boundAttempt=isset($S::REMEDIATED_RULES[$r['rule_id']])?$S::REMEDIATION_ATTEMPT:'MD-B09-A002';
    $boundEvidence=$boundAttempt==='MD-B09-A002'?$evidence:$S::REMEDIATION_EVIDENCE;
    $parts=array_values(array_filter(array_map('trim',explode(' | ',$r['notes'])),function($part){
        return $part!==''&&strpos($part,'MD-B09-A002: proof_binding=')!==0&&strpos($part,'MD-B09-A003: proof_binding=')!==0;
    }));
    $r['coverage_status']='SATISFIED';
    $r['current_evidence_ids']=$boundEvidence;
    $parts[]=$boundAttempt.': proof_binding='.$boundEvidence.'; proof_family='.$map[$r['rule_id']].'; proof_chain=current authority -> actual canonical RAW implementation -> positive/fail-closed runtime proof -> residue -> governed evidence';
    $r['notes']=implode(' | ',$parts);
}unset($r);
if($seen!==MarketDataCanonicalRawImportTraceabilitySpec::EXPECTED_DENOMINATOR)throw new RuntimeException('binding count '.$seen);echo json_encode(['denominator'=>$seen,'evidence_id'=>$evidence,'apply'=>$apply],JSON_PRETTY_PRINT).PHP_EOL;if(!$apply)exit(0);$h=fopen($p,'w');$out=$headers;if($bom)$out[0]="\xEF\xBB\xBF".$out[0];fputcsv($h,$out);foreach($rows as $r)fputcsv($h,array_map(function ($k) use ($r) { return $r[$k]; }, $headers));fclose($h);

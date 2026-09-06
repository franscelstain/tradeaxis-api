<?php
require_once __DIR__.'/MarketDataReplayVerificationProofSpec.php';

final class MarketDataReplayVerificationProofGate
{
    public const EVIDENCE_PATTERN='/^E-MD-B18-A001-\d{3}$/';
    public static function validate(string $root, bool $bound=false, array $overrides=[]): array
    {
        $rows=$overrides['required']??MarketDataReplayVerificationTraceabilitySpec::required($root);
        $entries=$overrides['entries']??MarketDataReplayVerificationProofSpec::entries($root);
        $families=$overrides['families']??MarketDataReplayVerificationProofSpec::families();
        $errors=[]; $by=[]; $used=[];
        foreach($rows as $row){
            $by[$row['rule_id']]=$row; $ev=trim((string)($row['current_evidence_ids']??''));
            if($bound){ if(($row['coverage_status']??'')!=='SATISFIED'||preg_match(self::EVIDENCE_PATTERN,$ev)!==1)$errors[]='BOUND_STATE_INVALID:'.$row['rule_id']; }
            else { if(($row['coverage_status']??'')!=='NOT_ASSESSED'||$ev!=='')$errors[]='PREMATURE_BINDING:'.$row['rule_id']; }
            if(in_array($row['applicability']??'', ['MANDATORY_OR_CONDITIONAL','CONDITIONAL_PENDING'], true))$errors[]='TRANSITIONAL_OR_PENDING:'.$row['rule_id'];
        }
        if(count($rows)!==121)$errors[]='DENOMINATOR_MISMATCH:'.count($rows);
        if(count($entries)!==121)$errors[]='PROOF_MAP_COUNT_MISMATCH:'.count($entries);
        $seen=[];
        foreach($entries as $entry){
            $rid=$entry['rule_id']??''; $family=$entry['family']??'';
            if(isset($seen[$rid])){$errors[]='DUPLICATE_ENTRY:'.$rid;continue;} $seen[$rid]=1;
            if(!isset($by[$rid])){$errors[]='ORPHAN_ENTRY:'.$rid;continue;}
            $expected=MarketDataReplayVerificationProofSpec::familyFor($by[$rid]);
            if($family!==$expected)$errors[]='WRONG_FAMILY:'.$rid;
            if(!isset($families[$family])){$errors[]='MISSING_FAMILY:'.$family;continue;} $used[$family]=1;
            $f=$families[$family];
            if(strpos((string)($f['owner']??''),'MD-B18:')!==0)$errors[]='WRONG_OWNER:'.$family;
            foreach($f['implementation']??[] as $path) if(!is_file($root.'/'.$path))$errors[]='MISSING_IMPL:'.$path;
            foreach(['positive','negative'] as $kind){ $ref=$f[$kind]??[]; $file=$ref[0]??''; $method=$ref[1]??''; $src=is_file($root.'/'.$file)?file_get_contents($root.'/'.$file):''; if(!$src||strpos($src,'function '.$method.'(')===false)$errors[]='MISSING_'.strtoupper($kind).'_PROOF:'.$family; }
        }
        foreach($by as $rid=>$row) if(!isset($seen[$rid]))$errors[]='UNMAPPED:'.$rid;
        foreach($families as $name=>$f) if(!isset($used[$name]))$errors[]='UNUSED_FAMILY:'.$name;
        return ['gate'=>'MarketDataReplayVerificationProofGate','stage_id'=>'MD-B18','attempt_id'=>'MD-B18-A001','status'=>$errors? 'FAIL':'PASS','denominator'=>count($rows),'proof_map_count'=>count($entries),'proof_families_used'=>count($used),'bound'=>$bound,'runtime_pending'=>$bound?0:count($rows),'errors'=>array_values(array_unique($errors))];
    }
}
if(realpath($_SERVER['SCRIPT_FILENAME']??'')===__FILE__){$r=MarketDataReplayVerificationProofGate::validate(dirname(__DIR__,5),in_array('--bound',$argv,true));$r['generated_at']=date(DATE_ATOM);echo json_encode($r,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL;exit($r['status']==='PASS'?0:1);}

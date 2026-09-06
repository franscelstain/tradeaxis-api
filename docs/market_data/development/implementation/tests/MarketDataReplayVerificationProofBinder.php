<?php
require_once __DIR__.'/MarketDataReplayVerificationProofGate.php';
final class MarketDataReplayVerificationProofBinder
{
    public static function bind(string $root,string $evidenceId='E-MD-B18-A001-001'): array
    {
        $evidence=$root.'/docs/market_data/records/evidence/'.$evidenceId.'_REPLAY_RUNTIME_PROOF.json';
        if(!is_file($evidence)) throw new RuntimeException('B18_RUNTIME_EVIDENCE_MISSING:'.$evidenceId);
        $payload=json_decode(file_get_contents($evidence),true);
        if(!is_array($payload)||($payload['verdict']??'')!=='PASS'||($payload['attempt_id']??'')!=='MD-B18-A001') throw new RuntimeException('B18_RUNTIME_EVIDENCE_NOT_ADMISSIBLE');
        $pre=MarketDataReplayVerificationProofGate::validate($root,false); if($pre['status']!=='PASS') throw new RuntimeException('B18_PREBIND_GATE_FAILED');
        $path=$root.'/docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';
        $h=fopen($path,'rb');$header=fgetcsv($h);$rows=[];$bound=0;
        while(($v=fgetcsv($h))!==false){$r=array_combine($header,$v);if(($r['active']??'')==='YES'&&($r['primary_stage']??'')==='MD-B18'&&in_array($r['applicability']??'', ['MANDATORY','CONDITIONAL_APPLICABLE'],true)){$r['coverage_status']='SATISFIED';$r['current_evidence_ids']=$evidenceId;$bound++;}$rows[]=$r;}fclose($h);
        $o=fopen($path,'wb');fputcsv($o,$header);foreach($rows as $r)fputcsv($o,array_map(fn($k)=>$r[$k]??'',$header));fclose($o);
        $post=MarketDataReplayVerificationProofGate::validate($root,true);if($post['status']!=='PASS')throw new RuntimeException('B18_POSTBIND_GATE_FAILED');
        return ['status'=>'PASS','bound_count'=>$bound,'evidence_id'=>$evidenceId];
    }
}
if(realpath($_SERVER['SCRIPT_FILENAME']??'')===__FILE__){try{$r=MarketDataReplayVerificationProofBinder::bind(dirname(__DIR__,5),$argv[1]??'E-MD-B18-A001-001');echo json_encode($r,JSON_PRETTY_PRINT).PHP_EOL;exit(0);}catch(Throwable $e){fwrite(STDERR,$e->getMessage().PHP_EOL);exit(1);}}

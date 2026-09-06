<?php
require_once __DIR__.'/MarketDataReplayVerificationProofGate.php';
$result=MarketDataReplayVerificationProofGate::validate(dirname(__DIR__,5),false);
$result['gate']='MarketDataReplayVerificationProofReadinessGate';
$result['readiness_state']=$result['status']==='PASS'?'READY_FOR_LOCAL_RUNTIME_PROOF':'NOT_READY';
$result['generated_at']=date(DATE_ATOM);
echo json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL;
exit($result['status']==='PASS'?0:1);

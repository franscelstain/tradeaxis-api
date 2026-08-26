<?php
final class MarketDataAnalyticalPriceProductStaticGate
{
 public static function run(string $root): array {$errors=[];$must=[
  'app/Application/MarketData/Services/AnalyticalPriceProductService.php'=>['TOTAL_RETURN_PRODUCT_UNAVAILABLE','ANALYTICAL_FACTOR_LINEAGE_INCOMPLETE','ANALYTICAL_VOLUME_FACTOR_REQUIRED','ANALYTICAL_BAR_AFTER_AS_OF','require_persisted_identity','content_hash'],
  'app/Application/MarketData/Services/AdjustmentFactorSetService.php'=>['AUTHORITATIVE_FACTOR_PROVENANCE_INCOMPLETE','source_observation_hash','volume_factor_required','GAP_UNKNOWN_MAGNITUDE','factor_formula_version'],
  'app/Application/MarketData/Services/IndicatorVectorService.php'=>['AnalyticalPriceProductService','require_factor_lineage','price_product_code'],
  'app/Domain/MarketData/MarketDataSemanticBindings.php'=>['structural_adjusted_v2','structural_factor_product_v2','total_return_v1'],
 ];foreach($must as $path=>$needles){if(!is_file($root.'/'.$path)){$errors[]='MISSING:'.$path;continue;}$s=(string)file_get_contents($root.'/'.$path);foreach($needles as $n)if(strpos($s,$n)===false)$errors[]='INVARIANT_MISSING:'.$path.':'.$n;}
 $svc=(string)file_get_contents($root.'/app/Application/MarketData/Services/AnalyticalPriceProductService.php');if(strpos($svc,"price_basis_default' => 'adj_close")!==false)$errors[]='PROVIDER_ADJ_CLOSE_SELECTION_RISK';
 return ['status'=>$errors===[]?'PASS':'FAIL','errors'=>$errors]; }
}
if(realpath($_SERVER['SCRIPT_FILENAME']??'')===__FILE__){$r=MarketDataAnalyticalPriceProductStaticGate::run(dirname(__DIR__,5));echo json_encode($r,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL;exit($r['status']==='PASS'?0:1);}

<?php

use App\Application\MarketData\Services\AnalyticalPriceProductService;

class AnalyticalPriceProductServiceTest extends TestCase
{
    private function bar($date, $price=100, $volume=10, $adjClose=777): array
    {
        return ['trade_date'=>$date,'open'=>$price,'high'=>$price+2,'low'=>$price-2,'close'=>$price,'adj_close'=>$adjClose,'volume'=>$volume];
    }

    private function strictContext(array $factors=[]): array
    {
        return [
            'analytical_as_of_date'=>'2026-03-01', 'price_adjustment_factors'=>$factors,
            'factor_set_id'=>9, 'factor_set_hash'=>str_repeat('a',64),
            'formula_version'=>'ind_v1', 'config_snapshot_id'=>4,
            'require_persisted_identity'=>true, 'require_factor_lineage'=>true,
        ];
    }

    public function test_raw_is_immutable_pass_through_and_provider_adj_close_is_not_selected(): void
    {
        $bars=[$this->bar('2026-01-01')];
        $r=(new AnalyticalPriceProductService())->build($bars,'RAW',['analytical_as_of_date'=>'2026-03-01']);
        $this->assertSame($bars,$r['bars']);
        $this->assertSame('RAW',$r['price_product_code']);
        $this->assertSame('raw_eod_v1',$r['price_product_version']);
    }

    public function test_structural_product_compounds_price_and_explicit_volume_factors_coherently(): void
    {
        $factors=[
            ['corporate_action_revision_id'=>11,'factor_revision_ref'=>'md-corporate-action-revision:11','ex_date'=>'2026-02-01','price_factor'=>0.5,'volume_factor'=>2.0,'volume_factor_required'=>true],
            ['corporate_action_revision_id'=>12,'factor_revision_ref'=>'md-corporate-action-revision:12','ex_date'=>'2026-02-15','price_factor'=>0.2,'volume_factor'=>5.0,'volume_factor_required'=>true],
        ];
        $r=(new AnalyticalPriceProductService())->build([$this->bar('2026-01-01',100,10)],'STRUCTURAL_ADJUSTED',$this->strictContext($factors));
        $b=$r['bars'][0];
        $this->assertEqualsWithDelta(10.0,$b['close'],1e-9);
        $this->assertEqualsWithDelta(10.2,$b['high'],1e-9);
        $this->assertEqualsWithDelta(9.8,$b['low'],1e-9);
        $this->assertEqualsWithDelta(100.0,$b['volume'],1e-9);
        $this->assertSame(777,$b['adj_close']);
        $this->assertSame('structural_adjusted_v2',$r['price_product_version']);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/',$r['content_hash']);
    }

    public function test_optional_null_volume_factor_preserves_volume_but_required_null_fails_closed(): void
    {
        $base=['corporate_action_revision_id'=>11,'factor_revision_ref'=>'md-corporate-action-revision:11','ex_date'=>'2026-02-01','price_factor'=>0.5,'volume_factor'=>null];
        $r=(new AnalyticalPriceProductService())->build([$this->bar('2026-01-01',100,10)],'STRUCTURAL_ADJUSTED',$this->strictContext([$base+['volume_factor_required'=>false]]));
        $this->assertEqualsWithDelta(10.0,$r['bars'][0]['volume'],1e-9);
        $this->expectExceptionMessage('ANALYTICAL_VOLUME_FACTOR_REQUIRED');
        (new AnalyticalPriceProductService())->build([$this->bar('2026-01-01')],'STRUCTURAL_ADJUSTED',$this->strictContext([$base+['volume_factor_required'=>true]]));
    }

    public function test_future_factor_is_not_admitted_to_earlier_as_of_and_future_bar_fails_closed(): void
    {
        $future=['corporate_action_revision_id'=>99,'factor_revision_ref'=>'bad-future-ref','ex_date'=>'2026-04-01','price_factor'=>0.0,'volume_factor'=>null,'volume_factor_required'=>true];
        $r=(new AnalyticalPriceProductService())->build([$this->bar('2026-01-01')],'STRUCTURAL_ADJUSTED',$this->strictContext([$future]));
        $this->assertEqualsWithDelta(100.0,$r['bars'][0]['close'],1e-9);
        $this->expectExceptionMessage('ANALYTICAL_BAR_AFTER_AS_OF');
        (new AnalyticalPriceProductService())->build([$this->bar('2026-04-01')],'STRUCTURAL_ADJUSTED',$this->strictContext([]));
    }

    public function test_factor_lineage_and_persisted_identity_are_fail_closed_and_hash_is_deterministic(): void
    {
        $factor=['corporate_action_revision_id'=>11,'factor_revision_ref'=>'md-corporate-action-revision:11','ex_date'=>'2026-02-01','price_factor'=>0.5,'volume_factor'=>2,'volume_factor_required'=>true];
        $svc=new AnalyticalPriceProductService();
        $a=$svc->build([$this->bar('2026-01-01')],'STRUCTURAL_ADJUSTED',$this->strictContext([$factor]));
        $b=$svc->build([$this->bar('2026-01-01')],'STRUCTURAL_ADJUSTED',$this->strictContext([$factor]));
        $this->assertSame($a['content_hash'],$b['content_hash']);
        $bad=$factor; $bad['factor_revision_ref']='action:11';
        $this->expectExceptionMessage('ANALYTICAL_FACTOR_LINEAGE_INCOMPLETE');
        $svc->build([$this->bar('2026-01-01')],'STRUCTURAL_ADJUSTED',$this->strictContext([$bad]));
    }

    public function test_total_return_is_distinct_and_unavailable_without_governed_distribution_formula(): void
    {
        $this->expectExceptionMessage('TOTAL_RETURN_PRODUCT_UNAVAILABLE');
        (new AnalyticalPriceProductService())->build([$this->bar('2026-01-01')],'TOTAL_RETURN',['analytical_as_of_date'=>'2026-03-01']);
    }
}

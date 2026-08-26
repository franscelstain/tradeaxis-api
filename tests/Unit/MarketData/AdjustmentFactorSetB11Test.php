<?php

use App\Application\MarketData\Services\AdjustmentFactorSetService;
use Tests\Support\UsesMarketDataSqlite;

class AdjustmentFactorSetB11Test extends TestCase
{
    use UsesMarketDataSqlite;
    protected function setUp(): void { parent::setUp(); $this->bootMarketDataSqlite(); }
    protected function tearDown(): void { $this->tearDownMarketDataSqlite(); parent::tearDown(); }

    private function factorTerms($type,array $terms): array
    {
        $service=new AdjustmentFactorSetService();
        $method=new ReflectionMethod($service,'factorTermsForEvent'); $method->setAccessible(true);
        $event=(object)['action_type_code'=>$type,'terms_json'=>json_encode($terms)];
        return $method->invoke($service,$event);
    }

    public function test_verified_split_ratio_maps_deterministically_to_price_and_volume_factors(): void
    {
        $terms=$this->factorTerms('STOCK_SPLIT',['ratio'=>['from'=>1,'to'=>5]]);
        $this->assertTrue($terms['factor_required']); $this->assertSame(0.2,$terms['price_factor']); $this->assertSame(5.0,$terms['volume_factor']);
    }

    public function test_non_adjusting_registry_type_requires_no_factor(): void
    {
        $terms=$this->factorTerms('IPO',[]);
        $this->assertFalse($terms['factor_required']); $this->assertNull($terms['price_factor']); $this->assertNull($terms['volume_factor']);
    }

    public function test_non_split_factor_never_invents_volume_factor(): void
    {
        $terms=$this->factorTerms('BONUS_SHARE',['adjustment'=>['price_factor'=>0.8]]);
        $this->assertTrue($terms['factor_required']); $this->assertSame(0.8,$terms['price_factor']); $this->assertNull($terms['volume_factor']);
    }
}

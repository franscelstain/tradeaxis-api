<?php

use App\Application\MarketData\Services\AnalyticalProductIdentityService;

class AnalyticalProductIdentityServiceTest extends TestCase
{
    public function test_empty_factor_set_still_has_a_stable_structural_adjusted_identity(): void
    {
        $service = new AnalyticalProductIdentityService();

        $first = $service->factorSetHash([], '2026-01-01', '2026-03-20');
        $second = $service->factorSetHash([], '2026-01-01', '2026-03-20');

        $this->assertSame('STRUCTURAL_ADJUSTED', $service->selectedProductCode());
        $this->assertSame('structural_adjusted_v2', $service->productVersion());
        $this->assertSame($first, $second);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $first);
    }

    public function test_product_versions_are_explicit_and_unknown_products_fail_closed(): void
    {
        $service = new AnalyticalProductIdentityService();
        $this->assertSame('raw_eod_v1', $service->productVersion('RAW'));
        $this->assertSame('structural_adjusted_v2', $service->productVersion('STRUCTURAL_ADJUSTED'));
        $this->assertSame('total_return_v1', $service->productVersion('TOTAL_RETURN'));
        $this->expectExceptionMessage('ANALYTICAL_PRICE_PRODUCT_UNKNOWN');
        $service->productVersion('PROVIDER_ADJ_CLOSE');
    }

    public function test_factor_hash_is_order_independent_but_revision_sensitive(): void
    {
        $service = new AnalyticalProductIdentityService();
        $left = [
            2 => [[
                'listing_id' => 22, 'factor_revision_ref' => 'action:2:r1',
                'ex_date' => '2026-02-01', 'price_factor' => 0.5, 'volume_factor' => 2,
            ]],
            1 => [[
                'listing_id' => 11, 'factor_revision_ref' => 'action:1:r1',
                'ex_date' => '2026-01-15', 'price_factor' => 0.2, 'volume_factor' => 5,
            ]],
        ];
        $right = array_reverse($left, true);

        $this->assertSame(
            $service->factorSetHash($left, '2026-01-01', '2026-03-20'),
            $service->factorSetHash($right, '2026-01-01', '2026-03-20')
        );

        $right[1][0]['factor_revision_ref'] = 'action:1:r2';
        $this->assertNotSame(
            $service->factorSetHash($left, '2026-01-01', '2026-03-20'),
            $service->factorSetHash($right, '2026-01-01', '2026-03-20')
        );
    }
}

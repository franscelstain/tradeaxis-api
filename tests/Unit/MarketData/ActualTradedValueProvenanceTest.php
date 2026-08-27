<?php

use App\Application\MarketData\Services\ActualTradedValueFactService;
use App\Domain\MarketData\LiquidityMetricLabelRegistry;

/**
 * B13: the required-field set for a populated actual traded value, exercised on the populated path.
 *
 * The platform's live adapter reports no traded value, so before this stage the requirement had
 * never run against a present value. These tests exist to make the enforcement real rather than
 * latent — the null path is the easy half, and it was the only half that had coverage.
 */
class ActualTradedValueProvenanceTest extends TestCase
{
    private function service(): ActualTradedValueFactService
    {
        return new ActualTradedValueFactService();
    }

    private function completeFact(array $overrides = []): array
    {
        return $overrides + [
            'traded_value_idr_actual' => 12345678.905,
            'trade_count_actual' => 42,
            'source' => 'IDX_OFFICIAL',
            'currency_code' => 'IDR',
            'market_segment' => 'IDX_REGULAR',
            'observed_date' => '2026-08-27',
            'quality_state' => 'VALIDATED',
            'value_origin' => 'SOURCE_REPORTED',
        ];
    }

    public function test_a_complete_source_backed_fact_is_storable(): void
    {
        $normalized = $this->service()->normalize($this->completeFact());

        $this->assertSame('12345678.91', $normalized['traded_value_idr_actual']);
        $this->assertSame(42, $normalized['trade_count_actual']);
    }

    public function test_an_unavailable_actual_value_is_null_and_asserts_nothing(): void
    {
        $normalized = $this->service()->normalize([
            'traded_value_idr_actual' => null,
            'trade_count_actual' => null,
        ]);

        $this->assertNull($normalized['traded_value_idr_actual']);
        $this->assertNull($normalized['trade_count_actual']);
    }

    /**
     * @dataProvider requiredFields
     */
    public function test_a_populated_value_missing_any_required_field_fails_closed(string $field): void
    {
        $fact = $this->completeFact();
        unset($fact[$field]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/ACTUAL_TRADED_VALUE_PROVENANCE_INCOMPLETE/');
        $this->service()->normalize($fact);
    }

    public static function requiredFields(): array
    {
        $sets = [];
        foreach (ActualTradedValueFactService::REQUIRED_FIELDS as $field) {
            $sets[$field] = [$field];
        }

        return $sets;
    }

    public function test_a_value_in_another_currency_is_not_this_scopes_traded_value(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/ACTUAL_TRADED_VALUE_CURRENCY_UNSUPPORTED/');
        $this->service()->normalize($this->completeFact(['currency_code' => 'USD']));
    }

    public function test_a_value_from_another_market_segment_is_not_regular_market_traded_value(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/ACTUAL_TRADED_VALUE_SEGMENT_UNSUPPORTED/');
        $this->service()->normalize($this->completeFact(['market_segment' => 'IDX_NEGOTIATED']));
    }

    public function test_a_proxy_derived_value_cannot_enter_the_actual_field(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/ACTUAL_TRADED_VALUE_NOT_SOURCE_REPORTED/');
        $this->service()->normalize($this->completeFact(['value_origin' => 'CLOSE_VOLUME_PROXY']));
    }

    public function test_a_trade_count_is_separately_nullable_from_the_traded_value(): void
    {
        $withoutCount = $this->service()->normalize($this->completeFact(['trade_count_actual' => null]));
        $this->assertNotNull($withoutCount['traded_value_idr_actual']);
        $this->assertNull($withoutCount['trade_count_actual']);

        $countOnly = $this->service()->normalize($this->completeFact(['traded_value_idr_actual' => null]));
        $this->assertNull($countOnly['traded_value_idr_actual']);
        $this->assertSame(42, $countOnly['trade_count_actual']);
    }

    public function test_a_populated_trade_count_still_requires_its_own_provenance(): void
    {
        $fact = $this->completeFact(['traded_value_idr_actual' => null]);
        unset($fact['source']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/ACTUAL_TRADED_VALUE_PROVENANCE_INCOMPLETE/');
        $this->service()->normalize($fact);
    }

    public function test_a_negative_trade_count_is_not_a_source_backed_fact(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/ACTUAL_TRADE_COUNT_INVALID/');
        $this->service()->normalize($this->completeFact(['trade_count_actual' => -1]));
    }

    public function test_the_actual_metric_is_declared_as_actual_and_never_as_proxy(): void
    {
        $label = LiquidityMetricLabelRegistry::declaredFor('traded_value_idr_actual');

        $this->assertNotNull($label);
        $this->assertSame(LiquidityMetricLabelRegistry::KIND_ACTUAL, $label['metric_kind']);
        $this->assertFalse($label['is_compatibility_alias']);
    }
}

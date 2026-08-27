<?php

use App\Application\MarketData\Services\LiquidityMetricLabelService;
use App\Domain\MarketData\LiquidityMetricLabelRegistry;
use App\Domain\MarketData\MarketDataSemanticBindings;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * B13: the actual-versus-proxy marker is a property of the stored metric, not of the contract.
 */
class LiquidityMetricLabellingTest extends TestCase
{
    use UsesMarketDataSqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
    }

    protected function tearDown(): void
    {
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    private function service(): LiquidityMetricLabelService
    {
        return new LiquidityMetricLabelService();
    }

    public function test_declared_labels_persist_and_resolve_from_storage(): void
    {
        DB::table('md_liquidity_metric_labels')->truncate();
        $written = $this->service()->syncDeclared();

        $this->assertSame(count(LiquidityMetricLabelRegistry::declared()), $written);
        $this->assertSame(
            $written,
            DB::table('md_liquidity_metric_labels')->count(),
            'Every declared label must exist as a row a consumer can query.'
        );

        $proxy = $this->service()->resolve('adv20_close_volume_proxy_idr');
        $this->assertNotNull($proxy);
        $this->assertSame('PROXY', $proxy['metric_kind']);
        $this->assertSame('RAW', $proxy['price_basis']);
        $this->assertSame(20, $proxy['window_sessions']);
        $this->assertSame('IDR', $proxy['unit_code']);
        $this->assertSame(MarketDataSemanticBindings::LIQUIDITY_FORMULA_VERSION, $proxy['formula_version']);
    }

    public function test_actual_and_proxy_are_distinguishable_without_reading_the_column_name(): void
    {
        $this->service()->syncDeclared();

        $kinds = [];
        foreach (DB::table('md_liquidity_metric_labels')->get() as $row) {
            $kinds[$row->metric_field] = $row->metric_kind;
        }

        $this->assertSame('ACTUAL', $kinds['adv20_traded_value_idr_actual']);
        $this->assertSame('ACTUAL', $kinds['traded_value_idr_actual']);
        $this->assertSame('PROXY', $kinds['adv20_close_volume_proxy_idr']);
        $this->assertSame('PROXY', $kinds['dv20_idr']);
    }

    public function test_the_actual_metric_does_not_claim_a_price_basis_it_never_used(): void
    {
        $this->service()->syncDeclared();

        $actual = $this->service()->resolve('adv20_traded_value_idr_actual');
        $this->assertSame('NOT_APPLICABLE', $actual['price_basis']);
        $this->assertNotSame('RAW', $actual['price_basis']);
    }

    public function test_the_legacy_alias_declares_its_target_and_its_retirement_condition(): void
    {
        $this->service()->syncDeclared();

        $alias = $this->service()->resolve('dv20_idr');
        $this->assertTrue($alias['is_compatibility_alias']);
        $this->assertSame('adv20_close_volume_proxy_idr', $alias['aliases_metric_field']);
        $this->assertNotSame('', trim((string) $alias['retirement_condition']));

        $target = $this->service()->resolve('adv20_close_volume_proxy_idr');
        $this->assertFalse($target['is_compatibility_alias']);
        $this->assertNull($target['aliases_metric_field']);
    }

    public function test_an_unlabelled_populated_metric_is_not_assumed_to_be_a_proxy(): void
    {
        $this->service()->syncDeclared();
        DB::table('md_liquidity_metric_labels')->where('metric_field', 'adv20_close_volume_proxy_idr')->delete();

        $service = $this->service();
        $row = ['adv20_close_volume_proxy_idr' => 1234.0, 'adv20_traded_value_idr_actual' => null];

        $unlabelled = $service->unlabelledMetrics($row, ['adv20_close_volume_proxy_idr', 'adv20_traded_value_idr_actual']);

        $this->assertSame(['adv20_close_volume_proxy_idr'], $unlabelled);
        $this->assertNull($service->resolve('adv20_close_volume_proxy_idr'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/UNLABELLED_LIQUIDITY_METRIC/');
        $service->assertPublishable($row, ['adv20_close_volume_proxy_idr', 'adv20_traded_value_idr_actual']);
    }

    public function test_a_null_actual_value_needs_no_label_because_nothing_was_published(): void
    {
        $this->service()->syncDeclared();
        DB::table('md_liquidity_metric_labels')->where('metric_field', 'adv20_traded_value_idr_actual')->delete();

        $service = $this->service();
        $row = ['adv20_close_volume_proxy_idr' => 1234.0, 'adv20_traded_value_idr_actual' => null];

        $this->assertSame([], $service->unlabelledMetrics($row, ['adv20_close_volume_proxy_idr', 'adv20_traded_value_idr_actual']));
        $service->assertPublishable($row, ['adv20_close_volume_proxy_idr', 'adv20_traded_value_idr_actual']);
    }

    public function test_a_label_that_exists_only_in_code_is_still_unlabelled(): void
    {
        DB::table('md_liquidity_metric_labels')->truncate();

        // The declaration exists in the registry class and nowhere a query reaches. The contract
        // treats that as carrying the label nowhere a consumer can read.
        $this->assertNotNull(LiquidityMetricLabelRegistry::declaredFor('dv20_idr'));
        $this->assertNull($this->service()->resolve('dv20_idr'));
    }

    public function test_declared_versus_deployed_drift_is_reported_in_both_directions(): void
    {
        $service = $this->service();
        $service->syncDeclared();
        $this->assertSame(
            ['missing' => [], 'unexpected' => [], 'mismatched' => []],
            $service->driftAgainstDeclared()
        );

        DB::table('md_liquidity_metric_labels')->where('metric_field', 'dv20_idr')->delete();
        DB::table('md_liquidity_metric_labels')->insert([
            'metric_field' => 'invented_liquidity_metric',
            'metric_scope' => LiquidityMetricLabelRegistry::SCOPE_INDICATOR_ROW,
            'formula_version' => MarketDataSemanticBindings::LIQUIDITY_FORMULA_VERSION,
            'metric_kind' => 'PROXY',
            'price_basis' => 'RAW',
            'window_sessions' => 20,
            'unit_code' => 'IDR',
            'market_scope' => 'IDX_REGULAR',
            'quality_state_field' => null,
            'is_compatibility_alias' => 0,
            'aliases_metric_field' => null,
            'retirement_condition' => null,
            'created_at' => '2026-08-27 00:00:00',
        ]);
        DB::table('md_liquidity_metric_labels')
            ->where('metric_field', 'adv20_close_volume_proxy_idr')
            ->update(['price_basis' => 'STRUCTURAL_ADJUSTED']);

        $drift = (new LiquidityMetricLabelService())->driftAgainstDeclared();

        $this->assertContains('dv20_idr@'.MarketDataSemanticBindings::LIQUIDITY_FORMULA_VERSION, $drift['missing']);
        $this->assertContains('invented_liquidity_metric@'.MarketDataSemanticBindings::LIQUIDITY_FORMULA_VERSION, $drift['unexpected']);
        $this->assertContains(
            'adv20_close_volume_proxy_idr@'.MarketDataSemanticBindings::LIQUIDITY_FORMULA_VERSION.'.price_basis',
            $drift['mismatched']
        );
    }

    public function test_an_alias_whose_retirement_condition_was_emptied_is_reported_as_drift(): void
    {
        $service = $this->service();
        $service->syncDeclared();
        DB::table('md_liquidity_metric_labels')
            ->where('metric_field', 'dv20_idr')
            ->update(['retirement_condition' => '']);

        $drift = (new LiquidityMetricLabelService())->driftAgainstDeclared();

        $this->assertContains(
            'dv20_idr@'.MarketDataSemanticBindings::LIQUIDITY_FORMULA_VERSION.'.retirement_condition_absent',
            $drift['mismatched']
        );
    }
}

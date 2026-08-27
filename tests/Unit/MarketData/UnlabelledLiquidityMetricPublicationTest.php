<?php

use App\Application\MarketData\Services\LiquidityMetricLabelService;
use App\Domain\MarketData\LiquidityMetricLabelRegistry;
use App\Domain\MarketData\MarketDataSemanticBindings;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * B13: an unlabelled liquidity metric may not be published.
 *
 * The scan is by stored artifact rather than by in-memory row, because the prohibition is on what
 * becomes readable. A row that is correct in memory and unlabelled in storage is the case that
 * matters.
 */
class UnlabelledLiquidityMetricPublicationTest extends TestCase
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

    private function insertIndicator(array $overrides = []): void
    {
        DB::table('eod_indicators')->insert($overrides + [
            'trade_date' => '2026-08-27',
            'ticker_id' => 1,
            'is_valid' => 1,
            'indicator_set_version' => 'v1',
            'run_id' => 1,
            'publication_id' => 1,
            'formula_version' => MarketDataSemanticBindings::LIQUIDITY_FORMULA_VERSION,
            'dv20_idr' => null,
            'adv20_close_volume_proxy_idr' => null,
            'adv20_traded_value_idr_actual' => null,
            'created_at' => '2026-08-27 00:00:00',
        ]);
    }

    private function scan(): array
    {
        return (new LiquidityMetricLabelService())->unlabelledPublishedMetrics(
            'eod_indicators',
            'trade_date',
            '2026-08-27',
            LiquidityMetricLabelRegistry::SCOPE_INDICATOR_ROW,
            'formula_version'
        );
    }

    public function test_a_populated_metric_with_a_persisted_label_is_publishable(): void
    {
        (new LiquidityMetricLabelService())->syncDeclared();
        $this->insertIndicator(['adv20_close_volume_proxy_idr' => 5000.00, 'dv20_idr' => 5000.00]);

        $this->assertSame([], $this->scan());
    }

    public function test_a_populated_metric_with_no_persisted_label_blocks_publication(): void
    {
        (new LiquidityMetricLabelService())->syncDeclared();
        DB::table('md_liquidity_metric_labels')->where('metric_field', 'adv20_close_volume_proxy_idr')->delete();
        $this->insertIndicator(['adv20_close_volume_proxy_idr' => 5000.00]);

        $this->assertSame(
            ['adv20_close_volume_proxy_idr@'.MarketDataSemanticBindings::LIQUIDITY_FORMULA_VERSION],
            $this->scan()
        );
    }

    public function test_an_empty_label_table_blocks_every_populated_metric(): void
    {
        DB::table('md_liquidity_metric_labels')->truncate();
        $this->insertIndicator(['adv20_close_volume_proxy_idr' => 5000.00, 'dv20_idr' => 5000.00]);

        $unlabelled = $this->scan();

        sort($unlabelled);
        $this->assertSame([
            'adv20_close_volume_proxy_idr@'.MarketDataSemanticBindings::LIQUIDITY_FORMULA_VERSION,
            'dv20_idr@'.MarketDataSemanticBindings::LIQUIDITY_FORMULA_VERSION,
        ], $unlabelled);
    }

    public function test_a_row_with_only_null_metrics_publishes_without_any_label(): void
    {
        $this->insertIndicator();

        $this->assertSame([], $this->scan());
    }

    public function test_a_metric_on_an_unlabelled_formula_version_is_caught(): void
    {
        (new LiquidityMetricLabelService())->syncDeclared();
        $this->insertIndicator([
            'adv20_close_volume_proxy_idr' => 5000.00,
            'formula_version' => 'liquidity_metric_v_unregistered',
        ]);

        $this->assertSame(
            ['adv20_close_volume_proxy_idr@liquidity_metric_v_unregistered'],
            $this->scan()
        );
    }

    public function test_a_populated_metric_stating_no_version_cannot_resolve_a_label(): void
    {
        (new LiquidityMetricLabelService())->syncDeclared();
        $this->insertIndicator(['adv20_close_volume_proxy_idr' => 5000.00, 'formula_version' => '']);

        $this->assertSame(['adv20_close_volume_proxy_idr@<no-version>'], $this->scan());
    }
}

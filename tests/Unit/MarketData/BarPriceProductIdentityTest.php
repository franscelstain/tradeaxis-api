<?php

/**
 * F-026/F-038 — a bar must declare which price product it belongs to, and a publication carrying a
 * bar that never did must be withheld rather than be given or served without one.
 *
 * All 756,329 rows in `eod_bars` carry a NULL `price_product_code`: the writer was added to the
 * ingest path after the corpus existed, so the immutable-RAW half of the stage-11 outcome cannot be
 * verified for any historical row. That gap is not closable by backfill. Writing RAW across the
 * corpus would assert a scale each row never recorded, which is the same fabrication this audit
 * refused for the sector effective dates.
 *
 * So the properties worth pinning are the two that keep the gap from growing or going quiet: every
 * path that writes a bar carries the identity forward, and the common read gateway fails closed
 * with an explicit reason instead of defaulting or leaking the price.
 */
class BarPriceProductIdentityTest extends TestCase
{
    private function read(string $relativePath): string
    {
        return (string) file_get_contents(__DIR__.'/../../../'.$relativePath);
    }

    public function test_the_ingest_path_records_the_raw_product_on_every_bar_it_writes(): void
    {
        $source = $this->read('app/Application/MarketData/Services/EodBarsIngestService.php');

        $this->assertTrue(
            (bool) preg_match("/'price_product_code'\s*=>\s*\(string\)\s*config\(/", $source),
            'the ingest path must record the product code it wrote the bar under'
        );
        $this->assertStringContainsString(
            'raw_product_code',
            $source,
            'and it must take that identity from the declared RAW scope, not from a literal'
        );
    }

    /**
     * Restoring a publication rebuilds eod_bars from eod_bars_history. If the rebuild dropped the
     * product code, a restore would silently un-record identity on rows that had it — turning a
     * recovery into data loss that reads as success.
     */
    public function test_bar_lineage_carries_the_product_identity_through_a_restore(): void
    {
        $source = $this->read('app/Infrastructure/Persistence/MarketData/EodArtifactRepository.php');

        $this->assertTrue(
            (bool) preg_match('/private function barLineage\(.*?\{.*?price_product_code.*?\}/s', $source),
            'barLineage must carry price_product_code, otherwise a restore drops it'
        );

        foreach (["DB::table('eod_bars')->insert(", 'barLineage('] as $needle) {
            $this->assertStringContainsString($needle, $source, 'the restore path must go through barLineage');
        }
    }

    public function test_the_read_gateway_withholds_unrecorded_or_non_raw_canonical_bar_identity(): void
    {
        $gateway = $this->read('app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php');
        $priceRepository = $this->read('app/Infrastructure/Persistence/MarketData/MarketDataPriceReadRepository.php');

        $this->assertStringContainsString('canonicalBarPriceProductViolationReasons', $gateway);
        $this->assertStringContainsString('PRICE_PRODUCT_UNRECORDED', $gateway);
        $this->assertStringContainsString('CANONICAL_BAR_PRICE_PRODUCT_INVALID', $gateway);
        $this->assertFalse(
            (bool) preg_match("/price_product_code\s*\?:\s*'RAW'/", $priceRepository),
            'the read side must never default an unrecorded product to RAW'
        );
        $this->assertStringNotContainsString('price_product_reason_code', $priceRepository);
        $this->assertStringContainsString('HEX(bar.price_product_code) = HEX(?)', $priceRepository);
    }

    public function test_the_unrecorded_reason_code_is_registered(): void
    {
        $registry = $this->read('docs/market_data/registry/Reason_Codes_Registry.md');
        $seed = $this->read('docs/market_data/registry/Reason_Codes_Seed.sql');

        $this->assertStringContainsString('`PRICE_PRODUCT_UNRECORDED`', $registry);
        $this->assertStringContainsString("('PRICE_PRODUCT_UNRECORDED', 'READ_SIDE'", $seed);
        $this->assertStringContainsString('`CANONICAL_BAR_PRICE_PRODUCT_INVALID`', $registry);
        $this->assertStringContainsString("('CANONICAL_BAR_PRICE_PRODUCT_INVALID', 'READ_SIDE'", $seed);
        $this->assertStringContainsString("('PRICE_PRODUCT_UNRECORDED', 'READ_SIDE', 'A canonical bar", $seed);
        $this->assertStringContainsString("lifecycle.', 'HARD', 1)", $seed);
    }
}

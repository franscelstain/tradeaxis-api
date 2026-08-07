<?php

use App\Application\MarketData\Services\MarketDataReadProductService;
use App\Infrastructure\Persistence\MarketData\MarketDataReadProductRepository;

/**
 * W17 — versioned market-data read product, stage 17 core.
 *
 * Exit gate: "no raw/current/master/`MAX(date)`/mixed-publication read; optional snapshot cannot
 * become strategy engine and, when disabled, does not create an implied missing feature."
 *
 * Owner contracts:
 *   docs/market_data/book/Downstream_Consumer_Read_Model_Contract_LOCKED.md
 *   docs/market_data/book/CONSUMER_READ_CONTRACT_LOCKED.md
 *   docs/market_data/book/Consumer_Readability_Decision_Table_LOCKED.md
 *   docs/market_data/book/Read_Side_Enforcement_Anti_Bypass_Contract_LOCKED.md
 *
 * There are currently no consumers, which means the bypass prohibition cannot be violated and
 * cannot be observed either — a gate that passes because nothing exercises it has proven nothing.
 * These are therefore written as structural guards on the gateway and on the boundary itself, so
 * they carry weight at the moment a first consumer appears rather than only describing today.
 */
class ConsumerReadProductAntiBypassTest extends TestCase
{
    private function source(string $path): string
    {
        $full = __DIR__.'/../../../'.$path;
        $this->assertFileExists($full, $path.' is the surface this gate governs');

        return (string) file_get_contents($full);
    }

    /**
     * Every artifact join binds publication and run together. Binding the date alone would let a
     * bar from one publication meet an indicator from another inside a single row, and the row
     * would look complete while describing two different datasets.
     */
    public function test_every_artifact_join_binds_publication_and_run(): void
    {
        $source = $this->source('app/Infrastructure/Persistence/MarketData/MarketDataReadProductRepository.php');

        foreach (['bar', 'ind'] as $alias) {
            $this->assertStringContainsString(
                "->on('".$alias.".publication_id', '=', 'elig.publication_id')",
                $source,
                $alias.' must be bound to the eligibility publication'
            );
            $this->assertStringContainsString(
                "->on('".$alias.".run_id', '=', 'elig.run_id')",
                $source,
                $alias.' must be bound to the eligibility run'
            );
        }
    }

    /**
     * The read path never resolves a date by taking the newest one available. `MAX(date)` is the
     * canonical way a consumer silently reads a different session than it asked for.
     */
    public function test_the_read_path_never_resolves_a_date_by_taking_the_latest(): void
    {
        foreach ([
            'app/Application/MarketData/Services/MarketDataReadProductService.php',
            'app/Application/MarketData/Services/MarketDataReadinessService.php',
            'app/Infrastructure/Persistence/MarketData/MarketDataReadProductRepository.php',
        ] as $path) {
            $source = $this->source($path);

            foreach (['MAX(', 'max(trade_date', "orderByDesc('trade_date')"] as $forbidden) {
                $this->assertStringNotContainsString($forbidden, $source, $path.' must not reach for the latest date');
            }
        }
    }

    /**
     * Only the market-data persistence layer touches the artifact tables. This is the guard that
     * will matter later: today there are no consumers, so nothing bypasses the gateway, and the
     * prohibition would otherwise be satisfied by the absence of anyone to violate it.
     */
    public function test_no_code_outside_the_persistence_layer_reads_the_artifact_tables(): void
    {
        $root = __DIR__.'/../../../app';
        $allowed = [
            // The break detector is an internal market-data producer, not a downstream consumer.
            'PriceScaleBreakDetectionService.php',
        ];

        $offenders = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));

        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $path = str_replace('\\', '/', $file->getPathname());
            if (strpos($path, '/Infrastructure/Persistence/MarketData/') !== false) {
                continue;
            }

            if (in_array($file->getFilename(), $allowed, true)) {
                continue;
            }

            $contents = (string) file_get_contents($file->getPathname());
            foreach (["table('eod_bars", "table('eod_indicators", "table('eod_eligibility"] as $needle) {
                if (strpos($contents, $needle) !== false) {
                    $offenders[] = $file->getFilename();
                    break;
                }
            }
        }

        $this->assertSame([], array_values(array_unique($offenders)), 'artifact tables are reachable only through the gateway');
    }

    /**
     * A run that is not ready yields an explicit empty payload with a reason, not the previous
     * session's rows. An implicit fallback is worse than no answer because it is indistinguishable
     * from a correct one.
     */
    public function test_an_unready_date_returns_an_explicit_empty_payload_rather_than_older_rows(): void
    {
        $source = $this->source('app/Application/MarketData/Services/MarketDataReadProductService.php');

        $this->assertStringContainsString('emptyPayload', $source);
        $this->assertStringContainsString("'is_ready' => false", $source);
        $this->assertStringContainsString("'rows' => []", $source);
    }

    /**
     * The payload declares its own product and read-model version, so a consumer can tell which
     * contract it is holding rather than inferring it from the fields that happen to be present.
     */
    public function test_the_payload_declares_its_product_and_read_model_version(): void
    {
        $source = $this->source('app/Application/MarketData/Services/MarketDataReadProductService.php');

        $this->assertStringContainsString("'read_model_version' => 'market_data_read_product_v1'", $source);
        $this->assertStringContainsString("'product_code' =>", $source);
        $this->assertStringContainsString("'publication_id' =>", $source);
        $this->assertStringContainsString("'trade_date_effective' =>", $source);
    }

    /**
     * The gateway is the only public entry point. A second read method would become the bypass the
     * anti-bypass contract exists to prevent.
     */
    public function test_the_gateway_exposes_exactly_one_read_entry_point(): void
    {
        $reflection = new ReflectionClass(MarketDataReadProductService::class);
        $public = array_values(array_filter(array_map(function (ReflectionMethod $method) {
            return $method->isConstructor() ? null : $method->getName();
        }, $reflection->getMethods(ReflectionMethod::IS_PUBLIC))));

        $this->assertSame(['getReadProductForTradeDate'], $public);
    }

    /**
     * The row shape carries every field group the read contract requires, so a consumer never has
     * to reach past the gateway to answer an ordinary question.
     */
    public function test_the_row_shape_covers_every_required_field_group(): void
    {
        $source = $this->source('app/Infrastructure/Persistence/MarketData/MarketDataReadProductRepository.php');

        foreach ([
            'ticker_code' => 'identity',
            'close_price' => 'RAW market fact',
            'price_product_code' => 'analytical product identity',
            'atr14_pct' => 'indicator',
            'eligibility_reasons_json' => 'data-usability reasons',
            'listing_id' => 'lineage',
            'indicator_set_version' => 'formula lineage',
        ] as $field => $group) {
            $this->assertStringContainsString($field, $source, $group.' must be present in the read product');
        }
    }
}

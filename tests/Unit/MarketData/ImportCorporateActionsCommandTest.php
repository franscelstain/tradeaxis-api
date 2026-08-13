<?php

use App\Console\Commands\MarketData\ImportCorporateActionsCommand;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Support\UsesMarketDataSqlite;

class ImportCorporateActionsCommandTest extends TestCase
{
    use UsesMarketDataSqlite;

    private $csvPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();

        DB::table('tickers')->insert([
            'ticker_id' => 1,
            'ticker_code' => 'BBCA',
            'company_name' => 'Bank Central Asia',
            'is_active' => 1,
        ]);
    }

    protected function tearDown(): void
    {
        if ($this->csvPath && is_file($this->csvPath)) {
            unlink($this->csvPath);
        }

        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    public function test_dry_run_validates_corporate_action_csv_without_writing_rows(): void
    {
        $this->csvPath = $this->writeCsv("ticker_code,action_date,action_type,source_ref,notes\nBBCA,2026-05-19,dividend,idx,source proof\n");

        $tester = $this->executeCommand(['input_file' => $this->csvPath]);
        $display = $tester->getDisplay();

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertStringContainsString('status=DRY_RUN', $display);
        $this->assertStringContainsString('reason_code=COMMAND_DRY_RUN_ONLY', $display);
        $this->assertStringContainsString('valid_row_count=1', $display);
        $this->assertStringContainsString('upserted_count=0', $display);
        $this->assertSame(0, DB::table('market_data_corporate_actions')->count());
    }

    public function test_apply_imports_corporate_action_csv(): void
    {
        $this->csvPath = $this->writeCsv("ticker_code,action_date,action_type,source_ref\nBBCA,2026-05-19,stock split,idx\n");

        $tester = $this->executeCommand([
            'input_file' => $this->csvPath,
            '--apply' => true,
        ]);
        $display = $tester->getDisplay();

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertStringContainsString('status=APPLIED', $display);
        $this->assertStringContainsString('reason_code=COMMAND_APPLY_CONFIRMED', $display);
        $this->assertStringContainsString('upserted_count=1', $display);

        $row = DB::table('market_data_corporate_actions')->where('ticker_id', 1)->first();

        $this->assertSame('2026-05-19', $row->action_date);
        $this->assertSame('STOCK_SPLIT', $row->action_type);
        $this->assertSame('manual_corporate_action_csv', $row->source_name);
    }

    /**
     * An attributed factor now has a way into the platform, and it survives the round trip.
     *
     * Before this the importer mapped only ticker/date/type/ref/notes, so the quantitative columns
     * had no writer except the platform's own price-series detector — which stamps
     * DERIVED_FROM_PRICE_SERIES, the one source refused for adjustment. The platform could produce
     * exactly the factors it would not use, and an authoritative one could not be entered at all.
     */
    public function test_an_attributed_factor_is_imported_and_becomes_adjustable(): void
    {
        $this->csvPath = $this->writeCsv(
            "ticker_code,action_date,action_type,ex_date,cum_date,ratio_from,ratio_to,price_adjustment_factor,volume_adjustment_factor,adjustment_source,source_ref\n"
            ."BBCA,2026-05-19,stock split,2026-05-19,2026-05-18,1,5,0.2,5,EXCHANGE_ANNOUNCEMENT,Peng-12345/BEI.PP1/05-2026\n"
        );

        $tester = $this->executeCommand(['input_file' => $this->csvPath, '--apply' => true]);

        $this->assertSame(0, $tester->getStatusCode());

        $row = DB::table('market_data_corporate_actions')->where('ticker_id', 1)->first();
        $this->assertSame('2026-05-19', $row->ex_date);
        $this->assertEqualsWithDelta(0.2, (float) $row->price_adjustment_factor, 1e-9);
        $this->assertEqualsWithDelta(5.0, (float) $row->volume_adjustment_factor, 1e-9);
        $this->assertSame('EXCHANGE_ANNOUNCEMENT', $row->adjustment_source);

        // The round trip is only worth anything if the stored row actually adjusts.
        $factors = (new \App\Infrastructure\Persistence\MarketData\EventRiskSourceRepository())
            ->resolveAdjustmentFactorsForTickerIds([1], '2026-05-01', '2026-05-31');

        $this->assertArrayHasKey(1, $factors, 'an imported attributed factor must resolve for adjustment');
        $this->assertEqualsWithDelta(0.2, $factors[1][0]['price_factor'], 1e-9);
    }

    /**
     * Each refusal is stated on its own line rather than collapsed into one failure, and the whole
     * import is blocked instead of partially applied.
     */
    public function test_unattributed_and_platform_derived_factors_are_refused(): void
    {
        $this->csvPath = $this->writeCsv(
            "ticker_code,action_date,action_type,ex_date,price_adjustment_factor,adjustment_source\n"
            ."BBCA,2026-05-19,stock split,2026-05-19,0.2,DERIVED_FROM_PRICE_SERIES\n"
            ."BBCA,2026-05-20,stock split,2026-05-20,0.2,\n"
            ."BBCA,2026-05-21,stock split,,0.2,EXCHANGE_ANNOUNCEMENT\n"
            ."BBCA,2026-05-22,stock split,2026-05-22,0.2,SOME_VENDOR_FEED\n"
        );

        $tester = $this->executeCommand(['input_file' => $this->csvPath, '--apply' => true]);
        $display = $tester->getDisplay();

        $this->assertSame(1, $tester->getStatusCode());
        $this->assertStringContainsString('error_count=4', $display);
        $this->assertStringContainsString('is produced by the platform, not imported', $display);
        $this->assertStringContainsString('adjustment_source is required when a factor is supplied', $display);
        $this->assertStringContainsString('ex_date is required when a factor is supplied', $display);
        $this->assertStringContainsString('is not a declared value', $display);
        $this->assertSame(0, DB::table('market_data_corporate_actions')->count(), 'nothing may be written');
    }

    /**
     * The same preservation rule one layer up: a header the CSV never carried leaves the stored
     * value alone. Sending null for an absent header would erase provenance on re-import, which is
     * the defect the repository-level guard also pins.
     */
    public function test_reimporting_a_minimal_csv_preserves_the_stored_factor(): void
    {
        $this->csvPath = $this->writeCsv(
            "ticker_code,action_date,action_type,ex_date,price_adjustment_factor,volume_adjustment_factor,adjustment_source,source_ref\n"
            ."BBCA,2026-05-19,stock split,2026-05-19,0.2,5,EXCHANGE_ANNOUNCEMENT,Peng-1/BEI/05-2026\n"
        );
        $this->executeCommand(['input_file' => $this->csvPath, '--apply' => true]);
        unlink($this->csvPath);

        // The three-column minimum the importer documents as valid.
        $this->csvPath = $this->writeCsv("ticker_code,action_date,action_type\nBBCA,2026-05-19,stock split\n");
        $tester = $this->executeCommand(['input_file' => $this->csvPath, '--apply' => true]);

        $this->assertSame(0, $tester->getStatusCode());

        $row = DB::table('market_data_corporate_actions')->where('ticker_id', 1)->first();
        $this->assertEqualsWithDelta(0.2, (float) $row->price_adjustment_factor, 1e-9);
        $this->assertSame('EXCHANGE_ANNOUNCEMENT', $row->adjustment_source);
        $this->assertSame('Peng-1/BEI/05-2026', $row->source_ref);
    }

    public function test_invalid_ticker_blocks_corporate_action_apply(): void
    {
        $this->csvPath = $this->writeCsv("ticker_code,action_date,action_type\nUNKNOWN,2026-05-19,dividend\n");

        $tester = $this->executeCommand([
            'input_file' => $this->csvPath,
            '--apply' => true,
        ]);
        $display = $tester->getDisplay();

        $this->assertSame(1, $tester->getStatusCode());
        $this->assertStringContainsString('status=BLOCKED', $display);
        $this->assertStringContainsString('validation_error=line 2: ticker_code UNKNOWN does not exist in ticker master.', $display);
        $this->assertSame(0, DB::table('market_data_corporate_actions')->count());
    }

    private function executeCommand(array $input): CommandTester
    {
        $command = new ImportCorporateActionsCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);
        $tester->execute($input);

        return $tester;
    }

    private function writeCsv(string $contents): string
    {
        $path = tempnam(sys_get_temp_dir(), 'corporate-actions-');
        file_put_contents($path, $contents);

        return $path;
    }
}

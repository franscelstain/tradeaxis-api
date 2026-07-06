<?php

use App\Console\Commands\MarketData\ImportTradingStatusEventsCommand;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Support\UsesMarketDataSqlite;

class ImportTradingStatusEventsCommandTest extends TestCase
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

    public function test_dry_run_validates_canonical_trading_status_csv_without_writing_rows(): void
    {
        $this->csvPath = $this->writeCsv("ticker_code,trade_date,event_type_code,source_ref\nBBCA,2026-05-19,UMA,idx\n");

        $tester = $this->executeCommand(['input_file' => $this->csvPath]);
        $display = $tester->getDisplay();

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertStringContainsString('status=DRY_RUN', $display);
        $this->assertStringContainsString('reason_code=COMMAND_DRY_RUN_ONLY', $display);
        $this->assertStringContainsString('valid_row_count=1', $display);
        $this->assertStringContainsString('upserted_count=0', $display);
        $this->assertSame(0, DB::table('market_data_trading_status_events')->count());
    }

    public function test_apply_imports_suspended_event_without_denormalized_boolean_columns(): void
    {
        $this->csvPath = $this->writeCsv("ticker_code,trade_date,event_type_code,source_ref\nBBCA,2026-05-19,suspended,idx\n");

        $tester = $this->executeCommand([
            'input_file' => $this->csvPath,
            '--apply' => true,
        ]);
        $display = $tester->getDisplay();

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertStringContainsString('status=APPLIED', $display);
        $this->assertStringContainsString('reason_code=COMMAND_APPLY_CONFIRMED', $display);
        $this->assertStringContainsString('upserted_count=1', $display);
        $this->assertStringContainsString('event_type_codes=SUSPENDED', $display);

        $row = DB::table('market_data_trading_status_events')->where('ticker_id', 1)->first();

        $this->assertSame('2026-05-19', $row->trade_date);
        $this->assertSame('SUSPENDED', $row->event_type_code);
        $this->assertSame('manual_trading_status_csv', $row->source_name);
        $this->assertFalse(property_exists($row, 'status_code'));
        $this->assertFalse(property_exists($row, 'is_suspended'));
        $this->assertFalse(property_exists($row, 'is_uma'));
    }

    public function test_apply_imports_unsuspended_event_type(): void
    {
        $this->csvPath = $this->writeCsv("ticker_code,trade_date,event_type_code,source_ref\nBBCA,2026-05-20,unsuspended,idx\n");

        $tester = $this->executeCommand([
            'input_file' => $this->csvPath,
            '--apply' => true,
        ]);
        $display = $tester->getDisplay();

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertStringContainsString('status=APPLIED', $display);
        $this->assertStringContainsString('event_type_codes=UNSUSPENDED', $display);

        $row = DB::table('market_data_trading_status_events')->where('ticker_id', 1)->first();

        $this->assertSame('UNSUSPENDED', $row->event_type_code);
    }

    public function test_apply_imports_special_monitoring_start_as_canonical_event_type(): void
    {
        $this->csvPath = $this->writeCsv("ticker_code,trade_date,event_type_code,source_ref\nBBCA,2026-05-21,special monitoring start,idx\n");

        $tester = $this->executeCommand([
            'input_file' => $this->csvPath,
            '--apply' => true,
        ]);

        $this->assertSame(0, $tester->getStatusCode());

        $row = DB::table('market_data_trading_status_events')->where('ticker_id', 1)->first();

        $this->assertSame('SPECIAL_MONITORING_START', $row->event_type_code);
    }

    public function test_deprecated_boolean_headers_block_trading_status_apply(): void
    {
        $this->csvPath = $this->writeCsv("ticker_code,trade_date,event_type_code,is_suspended\nBBCA,2026-05-19,UMA,1\n");

        $tester = $this->executeCommand([
            'input_file' => $this->csvPath,
            '--apply' => true,
        ]);
        $display = $tester->getDisplay();

        $this->assertSame(1, $tester->getStatusCode());
        $this->assertStringContainsString('status=BLOCKED', $display);
        $this->assertStringContainsString('CSV header must not include deprecated trading-status semantic column is_suspended. Use event_type_code only.', $display);
        $this->assertSame(0, DB::table('market_data_trading_status_events')->count());
    }

    public function test_unknown_event_type_code_blocks_apply(): void
    {
        $this->csvPath = $this->writeCsv("ticker_code,trade_date,event_type_code\nBBCA,2026-05-19,ACTIVE\n");

        $tester = $this->executeCommand([
            'input_file' => $this->csvPath,
            '--apply' => true,
        ]);
        $display = $tester->getDisplay();

        $this->assertSame(1, $tester->getStatusCode());
        $this->assertStringContainsString('status=BLOCKED', $display);
        $this->assertStringContainsString('event_type_code ACTIVE is not registered in market_data_trading_status_event_types', $display);
    }

    private function executeCommand(array $input): CommandTester
    {
        $command = new ImportTradingStatusEventsCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);
        $tester->execute($input);

        return $tester;
    }

    private function writeCsv(string $contents): string
    {
        $path = tempnam(sys_get_temp_dir(), 'trading-status-');
        file_put_contents($path, $contents);

        return $path;
    }
}

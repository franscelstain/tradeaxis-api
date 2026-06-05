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

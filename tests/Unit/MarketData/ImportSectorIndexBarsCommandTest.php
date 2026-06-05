<?php

use App\Console\Commands\MarketData\ImportSectorIndexBarsCommand;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Support\UsesMarketDataSqlite;

class ImportSectorIndexBarsCommandTest extends TestCase
{
    use UsesMarketDataSqlite;

    private $csvPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
    }

    protected function tearDown(): void
    {
        if ($this->csvPath && is_file($this->csvPath)) {
            unlink($this->csvPath);
        }

        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    public function test_dry_run_validates_sector_index_bars_without_writing_rows(): void
    {
        $this->csvPath = $this->writeCsv("sector_index_code,trade_date,open,high,low,close,adj_close,volume\nIDXFINANCE,2026-05-19,1200,1230,1190,1225,1225,0\n");

        $tester = $this->executeCommand(['input_file' => $this->csvPath]);
        $display = $tester->getDisplay();

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertStringContainsString('status=DRY_RUN', $display);
        $this->assertStringContainsString('reason_code=COMMAND_DRY_RUN_ONLY', $display);
        $this->assertStringContainsString('valid_row_count=1', $display);
        $this->assertStringContainsString('upserted_count=0', $display);
        $this->assertSame(0, DB::table('market_benchmark_bars')->count());
    }

    public function test_apply_imports_sector_index_bars(): void
    {
        $this->csvPath = $this->writeCsv("sector_index_code,trade_date,open,high,low,close,adj_close,volume\nIDXFINANCE,2026-05-19,1200,1230,1190,1225,1225,0\n");

        $tester = $this->executeCommand([
            'input_file' => $this->csvPath,
            '--apply' => true,
        ]);
        $display = $tester->getDisplay();

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertStringContainsString('status=APPLIED', $display);
        $this->assertStringContainsString('reason_code=COMMAND_APPLY_CONFIRMED', $display);
        $this->assertStringContainsString('upserted_count=1', $display);

        $row = DB::table('market_benchmark_bars')->where('benchmark_code', 'IDXFINANCE')->first();

        $this->assertSame('2026-05-19', $row->trade_date);
        $this->assertSame(1225.0, (float) $row->close_price);
        $this->assertSame('manual_sector_index_csv', $row->provider);
    }

    public function test_invalid_sector_index_blocks_apply(): void
    {
        $this->csvPath = $this->writeCsv("sector_index_code,trade_date,open,high,low,close\nUNKNOWN,2026-05-19,1200,1230,1190,1225\n");

        $tester = $this->executeCommand([
            'input_file' => $this->csvPath,
            '--apply' => true,
        ]);
        $display = $tester->getDisplay();

        $this->assertSame(1, $tester->getStatusCode());
        $this->assertStringContainsString('status=BLOCKED', $display);
        $this->assertStringContainsString('validation_error=line 2: sector_index_code UNKNOWN is not active in sector taxonomy.', $display);
        $this->assertSame(0, DB::table('market_benchmark_bars')->count());
    }

    private function executeCommand(array $input): CommandTester
    {
        $command = new ImportSectorIndexBarsCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);
        $tester->execute($input);

        return $tester;
    }

    private function writeCsv(string $contents): string
    {
        $path = tempnam(sys_get_temp_dir(), 'sector-index-bars-');
        file_put_contents($path, $contents);

        return $path;
    }
}

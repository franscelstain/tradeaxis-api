<?php

use App\Console\Commands\MarketData\ImportSectorMembershipCommand;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Support\UsesMarketDataSqlite;

class ImportSectorMembershipCommandTest extends TestCase
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

    public function test_dry_run_validates_sector_membership_csv_without_writing_rows(): void
    {
        $this->csvPath = $this->writeCsv("ticker_code,sector_code,effective_from,source_ref\nBBCA,G,2021-01-25,idx-fixture\n");

        $tester = $this->executeCommand(['input_file' => $this->csvPath]);
        $display = $tester->getDisplay();

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertStringContainsString('status=DRY_RUN', $display);
        $this->assertStringContainsString('reason_code=COMMAND_DRY_RUN_ONLY', $display);
        $this->assertStringContainsString('valid_row_count=1', $display);
        $this->assertStringContainsString('upserted_count=0', $display);
        $this->assertSame(0, DB::table('ticker_sector_memberships')->count());
    }

    public function test_apply_imports_sector_membership_csv(): void
    {
        $this->csvPath = $this->writeCsv("ticker_code,sector_code,effective_from,effective_to,source_name,source_ref\nBBCA,G,2021-01-25,,idx_manual,idx-fixture\n");

        $tester = $this->executeCommand([
            'input_file' => $this->csvPath,
            '--apply' => true,
        ]);
        $display = $tester->getDisplay();

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertStringContainsString('status=APPLIED', $display);
        $this->assertStringContainsString('reason_code=COMMAND_APPLY_CONFIRMED', $display);
        $this->assertStringContainsString('upserted_count=1', $display);

        $row = DB::table('ticker_sector_memberships')->where('ticker_id', 1)->first();

        $this->assertSame('G', $row->sector_code);
        $this->assertSame('IDX-IC', $row->classification_system);
        $this->assertSame('2021-01-25', $row->effective_from);
        $this->assertSame('idx_manual', $row->source_name);
    }

    public function test_invalid_csv_blocks_apply_with_validation_error(): void
    {
        $this->csvPath = $this->writeCsv("ticker_code,sector_code,effective_from\nBBCA,UNKNOWN,2021-01-25\n");

        $tester = $this->executeCommand([
            'input_file' => $this->csvPath,
            '--apply' => true,
        ]);
        $display = $tester->getDisplay();

        $this->assertSame(1, $tester->getStatusCode());
        $this->assertStringContainsString('status=BLOCKED', $display);
        $this->assertStringContainsString('validation_error=line 2: sector_code UNKNOWN is not active for IDX-IC.', $display);
        $this->assertSame(0, DB::table('ticker_sector_memberships')->count());
    }

    private function executeCommand(array $input): CommandTester
    {
        $command = new ImportSectorMembershipCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);
        $tester->execute($input);

        return $tester;
    }

    private function writeCsv(string $contents): string
    {
        $path = tempnam(sys_get_temp_dir(), 'sector-membership-');
        file_put_contents($path, $contents);

        return $path;
    }
}

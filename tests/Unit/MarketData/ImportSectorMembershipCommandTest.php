<?php

use App\Console\Commands\MarketData\ImportSectorMembershipCommand;
use App\Infrastructure\Persistence\MarketData\SectorClassificationRepository;
use App\Infrastructure\Persistence\MarketData\TemporalIdentityRepository;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Support\UsesMarketDataSqlite;

class ImportSectorMembershipCommandTest extends TestCase
{
    use UsesMarketDataSqlite;

    private $csvPath;
    private $listingUid;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();

        DB::table('tickers')->insert([
            'ticker_id' => 1,
            'ticker_code' => 'BBCA',
            'company_name' => 'Bank Central Asia',
            'listed_date' => '2021-01-25',
            'is_active' => 1,
        ]);
        (new TemporalIdentityRepository())->ensureLegacyProjection(['BBCA']);
        $this->listingUid = (string) DB::table('md_listings')->where('legacy_ticker_id', 1)->value('listing_uid');
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
        $this->csvPath = $this->writeCsv(
            $this->headers()."\n"
            .$this->listingUid.",BBCA,G,2021-01-25,,idx,idx-fixture,EXCHANGE_AUTHORITATIVE,2021-01-20 12:00:00,,\n"
        );

        $tester = $this->executeCommand(['input_file' => $this->csvPath]);
        $display = $tester->getDisplay();

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertStringContainsString('status=DRY_RUN', $display);
        $this->assertStringContainsString('reason_code=COMMAND_DRY_RUN_ONLY', $display);
        $this->assertStringContainsString('accepted_row_count=1', $display);
        $this->assertStringContainsString('planned_revision_count=1', $display);
        $this->assertStringContainsString('appended_revision_count=0', $display);
        $this->assertSame(0, DB::table('ticker_sector_memberships')->count());
    }

    public function test_apply_imports_sector_membership_csv(): void
    {
        $this->csvPath = $this->writeCsv(
            $this->headers()."\n"
            .$this->listingUid.",BBCA,G,2021-01-25,,idx,idx-fixture,EXCHANGE_AUTHORITATIVE,2021-01-20 12:00:00,,\n"
        );

        $tester = $this->executeCommand([
            'input_file' => $this->csvPath,
            '--apply' => true,
        ]);
        $display = $tester->getDisplay();

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertStringContainsString('status=APPLIED', $display);
        $this->assertStringContainsString('reason_code=COMMAND_APPLY_CONFIRMED', $display);
        $this->assertStringContainsString('accepted_row_count=1', $display);
        $this->assertStringContainsString('appended_revision_count=1', $display);

        $row = DB::table('ticker_sector_memberships')->where('ticker_id', 1)->first();

        $this->assertSame('G', $row->sector_code);
        $this->assertSame('IDX-IC', $row->classification_system);
        $this->assertSame('2021-01-25', $row->effective_from);
        $this->assertSame('idx', $row->source_name);
        $this->assertNotNull($row->listing_id);
        $this->assertSame('EXCHANGE_AUTHORITATIVE', $row->source_authority_class);
        $this->assertNull($row->operator_name);
        $this->assertSame('2021-01-20 12:00:00', $row->recorded_at);
    }

    public function test_invalid_csv_blocks_apply_with_validation_error(): void
    {
        $this->csvPath = $this->writeCsv(
            $this->headers()."\n"
            .$this->listingUid.",BBCA,UNKNOWN,2021-01-25,,idx,idx-fixture,EXCHANGE_AUTHORITATIVE,2021-01-20 12:00:00,,\n"
        );

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

    public function test_ticker_only_csv_is_blocked_because_stable_listing_identity_is_required(): void
    {
        $this->csvPath = $this->writeCsv(
            "ticker_code,sector_code,effective_from,source_name,source_ref,source_authority_class,recorded_at\n"
            ."BBCA,G,2021-01-25,idx,idx-fixture,EXCHANGE_AUTHORITATIVE,2021-01-20 12:00:00\n"
        );

        $tester = $this->executeCommand(['input_file' => $this->csvPath]);

        $this->assertSame(1, $tester->getStatusCode());
        $this->assertStringContainsString('CSV header must include listing_uid.', $tester->getDisplay());
        $this->assertSame(0, DB::table('ticker_sector_memberships')->count());
    }

    public function test_derived_reference_cannot_establish_membership(): void
    {
        $this->csvPath = $this->writeCsv(
            $this->headers()."\n"
            .$this->listingUid.",BBCA,G,2021-01-25,,vendor,vendor-ref,DERIVED_REFERENCE,2021-01-20 12:00:00,,\n"
        );

        $tester = $this->executeCommand(['input_file' => $this->csvPath, '--apply' => true]);

        $this->assertSame(1, $tester->getStatusCode());
        $this->assertStringContainsString('DERIVED_REFERENCE cannot establish sector membership.', $tester->getDisplay());
        $this->assertSame(0, DB::table('ticker_sector_memberships')->count());
    }

    public function test_dry_run_blocks_overlap_across_known_time_without_writing_rows(): void
    {
        $repository = new SectorClassificationRepository();
        $repository->upsertMembership(
            1, 'G', '2021-01-25', null, 'idx', 'initial', 'IDX-IC',
            'EXCHANGE_AUTHORITATIVE', '2021-01-20 12:00:00'
        );
        $repository->upsertMembership(
            1, 'I', '2026-06-01', null, 'idx', 'reclass', 'IDX-IC',
            'EXCHANGE_AUTHORITATIVE', '2022-01-20 12:00:00'
        );
        $beforeCount = DB::table('ticker_sector_memberships')->count();

        $this->csvPath = $this->writeCsv(
            $this->headers()."\n"
            .$this->listingUid.",BBCA,F,2025-01-01,2026-06-30,idx,overlap,EXCHANGE_AUTHORITATIVE,2023-01-20 12:00:00,,\n"
        );

        $tester = $this->executeCommand(['input_file' => $this->csvPath]);

        $this->assertSame(1, $tester->getStatusCode());
        $this->assertStringContainsString('SECTOR_MEMBERSHIP_OVERLAP_INVALID', $tester->getDisplay());
        $this->assertSame($beforeCount, DB::table('ticker_sector_memberships')->count());
    }

    public function test_imported_reclassification_resolves_by_effective_and_as_known_time(): void
    {
        $this->csvPath = $this->writeCsv(
            $this->headers()."\n"
            .$this->listingUid.",BBCA,G,2021-01-25,,idx,initial,EXCHANGE_AUTHORITATIVE,2021-01-20 12:00:00,,\n"
            .$this->listingUid.",BBCA,I,2026-06-01,,idx,reclass,EXCHANGE_AUTHORITATIVE,2026-05-20 12:00:00,,\n"
        );

        $tester = $this->executeCommand(['input_file' => $this->csvPath, '--apply' => true]);
        $repository = new SectorClassificationRepository();

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertStringContainsString('planned_revision_count=3', $tester->getDisplay());
        $this->assertStringContainsString('appended_revision_count=3', $tester->getDisplay());
        $this->assertSame([1 => 'G'], $repository->resolveSectorCodesForTickerIds(
            [1], '2026-06-03', 'IDX-IC', '2026-05-19 23:59:59'
        ));
        $this->assertSame([1 => 'I'], $repository->resolveSectorCodesForTickerIds(
            [1], '2026-06-03', 'IDX-IC', '2026-05-20 12:00:00'
        ));

        $original = DB::table('ticker_sector_memberships')->orderBy('membership_id')->first();
        $this->assertNull($original->effective_to, 'the imported prior fact must remain immutable');
    }

    private function executeCommand(array $input): CommandTester
    {
        $input += ['--operator_name' => 'test-operator'];
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

    private function headers(): string
    {
        return 'listing_uid,ticker_code,sector_code,effective_from,effective_to,source_name,source_ref,source_authority_class,recorded_at,operator_name,reason_code';
    }
}

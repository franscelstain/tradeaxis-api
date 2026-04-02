<?php

require_once __DIR__.'/../../Support/InteractsWithMarketDataConfig.php';

use App\Application\MarketData\Services\SessionSnapshotService;
use App\Infrastructure\MarketData\Source\LocalFileSessionSnapshotAdapter;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Infrastructure\Persistence\MarketData\EligibilitySnapshotScopeRepository;
use App\Infrastructure\Persistence\MarketData\SessionSnapshotRepository;
use App\Models\EodRun;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class SessionSnapshotServiceTest extends TestCase
{
    use InteractsWithMarketDataConfig;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bindMarketDataConfig();
    }

    protected function tearDown(): void
    {
        $this->clearMarketDataConfig();
        m::close();
        \Carbon\Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_capture_writes_summary_for_partial_scope()
    {
        $inputFile = sys_get_temp_dir().'/session_snapshot_'.uniqid().'.json';
        file_put_contents($inputFile, json_encode([
            ['ticker_code' => 'BBCA', 'captured_at' => '2026-03-24 09:10:00', 'last_price' => 9100, 'prev_close' => 9000, 'chg_pct' => 1.1111, 'volume' => 100000, 'day_high' => 9150, 'day_low' => 9050],
        ]));
        $outputDir = sys_get_temp_dir().'/session_snapshot_output_'.uniqid();

        $publications = m::mock(EodPublicationRepository::class);
        $runs = m::mock(EodRunRepository::class);
        $scope = m::mock(EligibilitySnapshotScopeRepository::class);
        $snapshots = m::mock(SessionSnapshotRepository::class);
        $adapter = new LocalFileSessionSnapshotAdapter();

        $publication = (object) ['publication_id' => 77, 'trade_date' => '2026-03-20', 'run_id' => 28];
        $run = new EodRun([
            'run_id' => 28,
            'trade_date_requested' => '2026-03-20',
        ]);

        $publications->shouldReceive('findCurrentPublicationForTradeDate')->once()->with('2026-03-20')->andReturn($publication);
        $runs->shouldReceive('findByRunId')->once()->with(28)->andReturn($run);
        $scope->shouldReceive('getScopeForTradeDate')->once()->with('2026-03-20')->andReturn([
            ['ticker_id' => 1, 'ticker_code' => 'BBCA', 'eligible' => 1],
            ['ticker_id' => 2, 'ticker_code' => 'BBRI', 'eligible' => 1],
        ]);
        $snapshots->shouldReceive('replaceSlotRows')->once()->withArgs(function ($tradeDate, $slot, $rows) {
            return $tradeDate === '2026-03-20' && $slot === 'OPEN_CHECK' && count($rows) === 1 && $rows[0]['ticker_id'] === 1;
        });
        $runs->shouldReceive('appendEvent')->once();

        $service = new SessionSnapshotService($publications, $runs, $scope, $snapshots, $adapter);
        $summary = $service->capture('2026-03-20', 'OPEN_CHECK', 'manual_file', $inputFile, $outputDir);

        $this->assertSame(2, $summary['scope_count']);
        $this->assertSame(1, $summary['captured_count']);
        $this->assertSame(1, $summary['skipped_count']);
        $this->assertFileExists($outputDir.'/market_data_session_snapshot_summary.json');
    }

    public function test_purge_writes_summary()
    {
        $publications = m::mock(EodPublicationRepository::class);
        $runs = m::mock(EodRunRepository::class);
        $scope = m::mock(EligibilitySnapshotScopeRepository::class);
        $snapshots = m::mock(SessionSnapshotRepository::class);
        $adapter = m::mock(LocalFileSessionSnapshotAdapter::class);

        $snapshots->shouldReceive('purgeBefore')->once()->andReturn(12);
        $service = new SessionSnapshotService($publications, $runs, $scope, $snapshots, $adapter);
        $outputDir = sys_get_temp_dir().'/session_snapshot_purge_'.uniqid();
        $summary = $service->purge('2026-03-01', $outputDir);

        $this->assertSame('explicit_before_date', $summary['cutoff_source']);
        $this->assertSame('2026-03-01', $summary['before_date']);
        $this->assertNull($summary['retention_days']);
        $this->assertSame(12, $summary['deleted_rows']);
        $this->assertFileExists($outputDir.'/market_data_session_snapshot_purge_summary.json');
    }


    public function test_purge_uses_default_retention_policy_window()
    {
        config([
            'market_data.platform.timezone' => 'Asia/Jakarta',
            'market_data.session_snapshot.retention_days' => 30,
        ]);

        \Carbon\Carbon::setTestNow('2026-03-24 12:00:00');

        $publications = m::mock(EodPublicationRepository::class);
        $runs = m::mock(EodRunRepository::class);
        $scope = m::mock(EligibilitySnapshotScopeRepository::class);
        $snapshots = m::mock(SessionSnapshotRepository::class);
        $adapter = m::mock(LocalFileSessionSnapshotAdapter::class);

        $expectedCutoff = \Carbon\Carbon::now('Asia/Jakarta')
            ->subDays(30)
            ->toDateTimeString();

        $snapshots->shouldReceive('purgeBefore')
            ->once()
            ->with($expectedCutoff)
            ->andReturn(3);

        $service = new SessionSnapshotService($publications, $runs, $scope, $snapshots, $adapter);
        $outputDir = sys_get_temp_dir().'/session_snapshot_purge_default_'.uniqid();
        $summary = $service->purge(null, $outputDir);

        $this->assertSame('default_retention_days', $summary['cutoff_source']);
        $this->assertSame(3, $summary['deleted_rows']);
        $this->assertSame(30, $summary['retention_days']);
        $this->assertNull($summary['before_date']);
        $this->assertFileExists($outputDir.'/market_data_session_snapshot_purge_summary.json');

        \Carbon\Carbon::setTestNow();
    }

}

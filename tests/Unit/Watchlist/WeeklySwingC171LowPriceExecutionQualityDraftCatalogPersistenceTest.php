<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC171LowPriceExecutionQualityParamGridCatalog;
use App\Application\Watchlist\Services\WeeklySwingC171LowPriceExecutionQualityDraftCatalogService;
use App\Application\Watchlist\Services\WeeklySwingParamsetDraftImportService;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesWatchlistRuntimeSqlite;
use TestCase;

class WeeklySwingC171LowPriceExecutionQualityDraftCatalogPersistenceTest extends TestCase
{
    use UsesWatchlistRuntimeSqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootWatchlistRuntimeSqlite();
        $this->seedR1BaselineParamGrid();
    }

    protected function tearDown(): void
    {
        $this->tearDownWatchlistRuntimeSqlite();
        parent::tearDown();
    }

    public function testCatalogAndFiveDraftsPersistIdempotentlyWithoutActiveEvalOrPlanMutation(): void
    {
        $repository = new WatchlistBacktestParamGridRepository();
        $firstSeed = $repository->seedCatalog(WatchlistBacktestC171LowPriceExecutionQualityParamGridCatalog::rows());
        $secondSeed = $repository->seedCatalog(WatchlistBacktestC171LowPriceExecutionQualityParamGridCatalog::rows());
        $this->assertSame(5, $firstSeed['inserted_count']);
        $this->assertSame(5, $secondSeed['existing_count']);

        $source = json_decode((string) file_get_contents(base_path(
            'docs/watchlist/system/policies/weekly_swing/fixtures/paramset_valid.json'
        )), true);
        $builder = new WeeklySwingC171LowPriceExecutionQualityDraftCatalogService();
        $importer = new WeeklySwingParamsetDraftImportService();
        $rows = $repository->allForCatalog(WatchlistBacktestC171LowPriceExecutionQualityParamGridCatalog::CATALOG_CODE);
        $hashes = $builder->deriveExpectedCandidateHashes($source, $rows);
        foreach ($rows as $row) {
            $payload = $builder->buildCandidatePayload($source, $row);
            $provenance = ['stage' => WeeklySwingC171LowPriceExecutionQualityDraftCatalogService::RUN_CODE, 'row' => $row['row_code']];
            $first = $importer->execute($payload, (int) $row['param_id'], WatchlistBacktestC171LowPriceExecutionQualityParamGridCatalog::CATALOG_CODE, $provenance);
            $second = $importer->execute($payload, (int) $row['param_id'], WatchlistBacktestC171LowPriceExecutionQualityParamGridCatalog::CATALOG_CODE, $provenance);
            $this->assertSame('DRAFT_PERSISTED', $first['status']);
            $this->assertSame('INSERTED', $first['persistence']['status']);
            $this->assertSame('IDEMPOTENT', $second['persistence']['status']);
            $this->assertSame($hashes[$row['row_code']], $first['persistence']['params_hash']);
        }

        $this->assertSame(5, DB::table('watchlist_param_sets')->where('status', 'DRAFT')->count());
        $this->assertSame(0, DB::table('watchlist_param_sets')->where('status', 'ACTIVE')->count());
        $this->assertSame(0, DB::table('watchlist_bt_eval')->count());
        $this->assertSame(0, DB::table('watchlist_plan_runs')->count());
    }

    public function testDesignArtifactIdentityIsExactAndImmutable(): void
    {
        $service = new WeeklySwingC171LowPriceExecutionQualityDraftCatalogService();
        $method = new \ReflectionMethod($service, 'loadAndVerifyDesignArtifact');
        $method->setAccessible(true);
        $result = $method->invoke($service);

        $this->assertTrue($result['valid']);
        $this->assertSame('C171_LOW_PRICE_EXECUTION_QUALITY_C01_DESIGN_VALID', $result['reason_code']);
    }

    public function testBindingFailsClosedWhenTickGuardDrifts(): void
    {
        $repository = new WatchlistBacktestParamGridRepository();
        $repository->seedCatalog(WatchlistBacktestC171LowPriceExecutionQualityParamGridCatalog::rows());
        $row = $repository->allForCatalog(WatchlistBacktestC171LowPriceExecutionQualityParamGridCatalog::CATALOG_CODE)[1];
        $source = json_decode((string) file_get_contents(base_path(
            'docs/watchlist/system/policies/weekly_swing/fixtures/paramset_valid.json'
        )), true);
        $payload = (new WeeklySwingC171LowPriceExecutionQualityDraftCatalogService())->buildCandidatePayload($source, $row);
        $payload['risk']['max_signal_tick_risk_expansion_pct']['value'] = 0.02;

        $result = (new WeeklySwingParamsetDraftImportService())->execute(
            $payload,
            (int) $row['param_id'],
            WatchlistBacktestC171LowPriceExecutionQualityParamGridCatalog::CATALOG_CODE
        );

        $this->assertSame('BLOCKED', $result['status']);
        $this->assertSame('WS_PARAMSET_BT_BINDING_MISMATCH', $result['reason_code']);
        $this->assertSame(0, DB::table('watchlist_param_sets')->count());
    }
}

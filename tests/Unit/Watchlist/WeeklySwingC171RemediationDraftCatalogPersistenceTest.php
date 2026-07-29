<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC171RemediationParamGridCatalog;
use App\Application\Watchlist\Services\WeeklySwingC171RemediationDraftCatalogService;
use App\Application\Watchlist\Services\WeeklySwingParamsetDraftImportService;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesWatchlistRuntimeSqlite;
use TestCase;

class WeeklySwingC171RemediationDraftCatalogPersistenceTest extends TestCase
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

    public function testCatalogAndFiveDraftsPersistIdempotentlyWithoutActiveOrOosMutation(): void
    {
        $repository = new WatchlistBacktestParamGridRepository();
        $firstSeed = $repository->seedCatalog(WatchlistBacktestC171RemediationParamGridCatalog::rows());
        $secondSeed = $repository->seedCatalog(WatchlistBacktestC171RemediationParamGridCatalog::rows());

        $this->assertSame(5, $firstSeed['inserted_count']);
        $this->assertSame(0, $secondSeed['inserted_count']);
        $this->assertSame(5, $secondSeed['existing_count']);
        $this->assertSame(5, $secondSeed['param_grid_count']);

        $source = json_decode((string) file_get_contents(base_path(
            'docs/watchlist/system/policies/weekly_swing/fixtures/paramset_valid.json'
        )), true);
        $builder = new WeeklySwingC171RemediationDraftCatalogService();
        $importer = new WeeklySwingParamsetDraftImportService();
        $catalogRows = $repository->allForCatalog(WatchlistBacktestC171RemediationParamGridCatalog::CATALOG_CODE);
        $expectedHashes = $builder->deriveExpectedCandidateHashes($source, $catalogRows);
        $persisted = [];
        foreach ($catalogRows as $row) {
            $payload = $builder->buildCandidatePayload($source, $row);
            $provenance = [
                'stage' => WeeklySwingC171RemediationDraftCatalogService::RUN_CODE,
                'catalog_row_code' => $row['row_code'],
                'test_fixture' => true,
            ];
            $first = $importer->execute(
                $payload,
                (int) $row['param_id'],
                WatchlistBacktestC171RemediationParamGridCatalog::CATALOG_CODE,
                $provenance
            );
            $second = $importer->execute(
                $payload,
                (int) $row['param_id'],
                WatchlistBacktestC171RemediationParamGridCatalog::CATALOG_CODE,
                $provenance
            );

            $this->assertSame('DRAFT_PERSISTED', $first['status']);
            $this->assertSame('INSERTED', $first['persistence']['status']);
            $this->assertSame('IDEMPOTENT', $second['persistence']['status']);
            $this->assertSame('DRAFT', $first['paramset_status']);
            $this->assertSame($row['row_code'], $first['validation']['canonical_payload']['paramset_code']);
            $this->assertSame((float) $row['top_max_score_total'], $first['validation']['canonical_payload']['grouping']['top_max_score_total']['value']);
            $this->assertSame($expectedHashes[$row['row_code']], $first['validation']['canonical_hash']);
            $this->assertSame($expectedHashes[$row['row_code']], $first['persistence']['params_hash']);
            $persisted[] = $first['param_set_id'];
        }

        $this->assertCount(5, array_unique($persisted));
        $this->assertSame(5, DB::table('watchlist_param_sets')->where('status', 'DRAFT')->count());
        $this->assertSame(0, DB::table('watchlist_param_sets')->where('status', 'ACTIVE')->count());
        $this->assertSame(0, DB::table('watchlist_bt_oos_eval_ws')->count());
    }

    public function testReleasedDesignArtifactIdentityUsesItsOriginalCanonicalJsonContract(): void
    {
        $service = new WeeklySwingC171RemediationDraftCatalogService();
        $method = new \ReflectionMethod($service, 'loadAndVerifyDesignArtifact');
        $method->setAccessible(true);
        $result = $method->invoke($service);

        $this->assertTrue($result['valid']);
        $this->assertSame('C171_REMEDIATION_DESIGN_VALID', $result['reason_code']);
    }

    public function testBindingFailsClosedWhenAnyNewUpperBoundDrifts(): void
    {
        $repository = new WatchlistBacktestParamGridRepository();
        $repository->seedCatalog(WatchlistBacktestC171RemediationParamGridCatalog::rows());
        $row = $repository->allForCatalog(WatchlistBacktestC171RemediationParamGridCatalog::CATALOG_CODE)[0];
        $source = json_decode((string) file_get_contents(base_path(
            'docs/watchlist/system/policies/weekly_swing/fixtures/paramset_valid.json'
        )), true);
        $payload = (new WeeklySwingC171RemediationDraftCatalogService())->buildCandidatePayload($source, $row);
        $payload['volume']['max_vol_ratio']['value'] = (float) $row['max_vol_ratio'] + 1.0;

        $result = (new WeeklySwingParamsetDraftImportService())->execute(
            $payload,
            (int) $row['param_id'],
            WatchlistBacktestC171RemediationParamGridCatalog::CATALOG_CODE
        );

        $this->assertSame('BLOCKED', $result['status']);
        $this->assertSame('WS_PARAMSET_BT_BINDING_MISMATCH', $result['reason_code']);
        $this->assertSame(0, DB::table('watchlist_param_sets')->count());
    }
}

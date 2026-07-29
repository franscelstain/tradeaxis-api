<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC171FinalBoundedRemediationParamGridCatalog;
use App\Application\Watchlist\Services\WeeklySwingC171FinalBoundedRemediationDraftCatalogService;
use App\Application\Watchlist\Services\WeeklySwingParamsetDraftImportService;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesWatchlistRuntimeSqlite;
use TestCase;

class WeeklySwingC171FinalBoundedRemediationDraftCatalogPersistenceTest extends TestCase
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

    public function testExactlyThreeFinalDraftsPersistIdempotentlyWithoutEvalActiveOrPlanMutation(): void
    {
        $repository = new WatchlistBacktestParamGridRepository();
        $firstSeed = $repository->seedCatalog(WatchlistBacktestC171FinalBoundedRemediationParamGridCatalog::rows());
        $secondSeed = $repository->seedCatalog(WatchlistBacktestC171FinalBoundedRemediationParamGridCatalog::rows());
        $this->assertSame(3, $firstSeed['inserted_count']);
        $this->assertSame(3, $secondSeed['existing_count']);

        $source = json_decode((string) file_get_contents(base_path(
            'docs/watchlist/system/policies/weekly_swing/fixtures/paramset_valid.json'
        )), true);
        $builder = new WeeklySwingC171FinalBoundedRemediationDraftCatalogService();
        $importer = new WeeklySwingParamsetDraftImportService();
        $rows = $repository->allForCatalog(WatchlistBacktestC171FinalBoundedRemediationParamGridCatalog::CATALOG_CODE);
        $hashes = $builder->deriveExpectedCandidateHashes($source, $rows);
        foreach ($rows as $row) {
            $payload = $builder->buildCandidatePayload($source, $row);
            $provenance = [
                'stage' => WeeklySwingC171FinalBoundedRemediationDraftCatalogService::RUN_CODE,
                'closure_rule_if_no_pass' => 'C171_FAILED_NOT_READY_NO_FURTHER_REMEDIATION',
            ];
            $first = $importer->execute($payload, (int) $row['param_id'], WatchlistBacktestC171FinalBoundedRemediationParamGridCatalog::CATALOG_CODE, $provenance);
            $second = $importer->execute($payload, (int) $row['param_id'], WatchlistBacktestC171FinalBoundedRemediationParamGridCatalog::CATALOG_CODE, $provenance);
            $this->assertSame('DRAFT_PERSISTED', $first['status']);
            $this->assertSame('INSERTED', $first['persistence']['status']);
            $this->assertSame('IDEMPOTENT', $second['persistence']['status']);
            $this->assertSame($hashes[$row['row_code']], $first['persistence']['params_hash']);
        }

        $this->assertSame(3, DB::table('watchlist_param_sets')->where('status', 'DRAFT')->count());
        $this->assertSame(0, DB::table('watchlist_param_sets')->where('status', 'ACTIVE')->count());
        $this->assertSame(0, DB::table('watchlist_bt_eval')->count());
        $this->assertSame(0, DB::table('watchlist_plan_runs')->count());
    }

    public function testDesignArtifactAndEvidenceBundleIdentityAreExact(): void
    {
        $service = new WeeklySwingC171FinalBoundedRemediationDraftCatalogService();
        $design = new \ReflectionMethod($service, 'loadAndVerifyDesignArtifact');
        $design->setAccessible(true);
        $result = $design->invoke($service);

        $this->assertTrue($result['valid']);
        $this->assertSame('C171_FINAL_BOUNDED_REMEDIATION_DESIGN_VALID', $result['reason_code']);
    }

    public function testBindingFailsClosedWhenFinalCandidateDrifts(): void
    {
        $repository = new WatchlistBacktestParamGridRepository();
        $repository->seedCatalog(WatchlistBacktestC171FinalBoundedRemediationParamGridCatalog::rows());
        $row = $repository->allForCatalog(WatchlistBacktestC171FinalBoundedRemediationParamGridCatalog::CATALOG_CODE)[2];
        $source = json_decode((string) file_get_contents(base_path(
            'docs/watchlist/system/policies/weekly_swing/fixtures/paramset_valid.json'
        )), true);
        $payload = (new WeeklySwingC171FinalBoundedRemediationDraftCatalogService())->buildCandidatePayload($source, $row);
        $payload['risk']['stop_atr_mult']['value'] = 1.5;

        $result = (new WeeklySwingParamsetDraftImportService())->execute(
            $payload,
            (int) $row['param_id'],
            WatchlistBacktestC171FinalBoundedRemediationParamGridCatalog::CATALOG_CODE
        );

        $this->assertSame('BLOCKED', $result['status']);
        $this->assertSame('WS_PARAMSET_BT_BINDING_MISMATCH', $result['reason_code']);
        $this->assertSame(0, DB::table('watchlist_param_sets')->count());
    }
}

<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WeeklySwingParamsetDraftImportService;
use App\Application\Watchlist\Services\WeeklySwingParamsetPromotionService;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestOfficialEvidenceRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesWatchlistRuntimeSqlite;
use TestCase;

class WeeklySwingParamsetPersistenceAndPromotionTest extends TestCase
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

    public function testR1BootstrapIsPersistedAsDraftWithExactBindingAndIsIdempotent(): void
    {
        $service = new WeeklySwingParamsetDraftImportService();
        $first = $service->execute($this->payload(), 1, 'WS_BT_GRID_BOOTSTRAP_2026_06', [
            'source_path' => 'PARAMSET_WS_ACTIVE_EXAMPLE.json',
        ]);
        $second = $service->execute($this->payload(), 1, 'WS_BT_GRID_BOOTSTRAP_2026_06', [
            'source_path' => 'PARAMSET_WS_ACTIVE_EXAMPLE.json',
        ]);

        $this->assertSame('DRAFT_PERSISTED', $first['status']);
        $this->assertSame('INSERTED', $first['persistence']['status']);
        $this->assertSame('DRAFT', $first['paramset_status']);
        $this->assertSame(1, $first['binding']['bt_param_id']);
        $this->assertSame('01_BASELINE', $first['binding']['row_code']);
        $this->assertSame('IDEMPOTENT', $second['persistence']['status']);
        $this->assertSame($first['param_set_id'], $second['param_set_id']);
        $this->assertSame(1, DB::table('watchlist_param_sets')->count());
    }

    public function testDraftImportBlocksWhenPayloadDoesNotMatchBoundGridRow(): void
    {
        $payload = $this->payload();
        $payload['liquidity']['min_dv20_idr']['value'] = 2500000000;

        $result = (new WeeklySwingParamsetDraftImportService())->execute(
            $payload,
            1,
            'WS_BT_GRID_BOOTSTRAP_2026_06'
        );

        $this->assertSame('BLOCKED', $result['status']);
        $this->assertSame('WS_PARAMSET_BT_BINDING_MISMATCH', $result['reason_code']);
        $this->assertSame(0, DB::table('watchlist_param_sets')->count());
    }

    public function testDraftImportDoesNotMisreportAnExistingActivePayloadAsDraft(): void
    {
        $service = new WeeklySwingParamsetDraftImportService();
        $draft = $service->execute($this->payload(), 1, 'WS_BT_GRID_BOOTSTRAP_2026_06');
        DB::table('watchlist_param_sets')
            ->where('param_set_id', $draft['param_set_id'])
            ->update(['status' => 'ACTIVE']);

        $result = $service->execute($this->payload(), 1, 'WS_BT_GRID_BOOTSTRAP_2026_06');

        $this->assertSame('BLOCKED', $result['status']);
        $this->assertSame('WS_PARAMSET_DRAFT_IMPORT_STATUS_CONFLICT', $result['reason_code']);
        $this->assertSame('ACTIVE', $result['paramset_status']);
        $this->assertSame(1, DB::table('watchlist_param_sets')->count());
    }

    public function testPromotionBlocksWhenExactPersistedOosProofIsMissing(): void
    {
        $draft = (new WeeklySwingParamsetDraftImportService())->execute(
            $this->payload(),
            1,
            'WS_BT_GRID_BOOTSTRAP_2026_06'
        );

        $result = (new WeeklySwingParamsetPromotionService())->execute($draft['param_set_id'], 1, 999);

        $this->assertSame('BLOCKED', $result['status']);
        $this->assertSame('WS_PARAMSET_PROMOTION_OOS_PROOF_MISSING', $result['reason_code']);
        $this->assertSame('DRAFT', DB::table('watchlist_param_sets')->value('status'));
    }

    public function testPromotionRequiresPassingIsAndOosRowsThenDeprecatesPreviousActive(): void
    {
        $draft = (new WeeklySwingParamsetDraftImportService())->execute(
            $this->payload(),
            1,
            'WS_BT_GRID_BOOTSTRAP_2026_06'
        );
        $this->insertPassingProof();
        $this->insertPreviousActive();

        $result = (new WeeklySwingParamsetPromotionService())->execute($draft['param_set_id'], 1, 21);

        $this->assertSame('PROMOTED', $result['status']);
        $this->assertSame('WS_PARAMSET_PROMOTED_ACTIVE', $result['reason_code']);
        $this->assertTrue($result['is_acceptance']['pass']);
        $this->assertTrue($result['oos_acceptance']['pass']);
        $this->assertSame('ACTIVE', DB::table('watchlist_param_sets')->where('param_set_id', $draft['param_set_id'])->value('status'));
        $this->assertSame('DEPRECATED', DB::table('watchlist_param_sets')->where('param_set_id', '<>', $draft['param_set_id'])->value('status'));
    }

    public function testPromotionBlocksWhenExactIsSupportEvidenceIsMissing(): void
    {
        $draft = (new WeeklySwingParamsetDraftImportService())->execute(
            $this->payload(),
            1,
            'WS_BT_GRID_BOOTSTRAP_2026_06'
        );
        $this->insertPassingProof();
        DB::table('watchlist_bt_cutoffs_ws')->delete();

        $result = (new WeeklySwingParamsetPromotionService())->execute($draft['param_set_id'], 1, 21);

        $this->assertSame('BLOCKED', $result['status']);
        $this->assertSame('WS_PARAMSET_PROMOTION_OFFICIAL_EVIDENCE_HASH_MISMATCH', $result['reason_code']);
        $this->assertSame('DRAFT', DB::table('watchlist_param_sets')->value('status'));
    }

    public function testPromotionBlocksWhenCatalogBindingMetadataDriftsAfterDraftImport(): void
    {
        $draft = (new WeeklySwingParamsetDraftImportService())->execute(
            $this->payload(),
            1,
            'WS_BT_GRID_BOOTSTRAP_2026_06'
        );
        $this->insertPassingProof();
        DB::table('watchlist_bt_param_grid')
            ->where('param_id', 1)
            ->update(['row_hash' => sha1('drifted-row')]);

        $result = (new WeeklySwingParamsetPromotionService())->execute($draft['param_set_id'], 1, 21);

        $this->assertSame('BLOCKED', $result['status']);
        $this->assertSame('WS_PARAMSET_PROMOTION_BINDING_DRIFT', $result['reason_code']);
        $this->assertSame('DRAFT', DB::table('watchlist_param_sets')->value('status'));
    }

    public function testPromotionBlocksWhenDynamicIsCoverageFloorFails(): void
    {
        $draft = (new WeeklySwingParamsetDraftImportService())->execute(
            $this->payload(),
            1,
            'WS_BT_GRID_BOOTSTRAP_2026_06'
        );
        $this->insertPassingProof();
        DB::table('watchlist_bt_eval')->where('eval_id', 11)->update(['days_covered' => 69]);

        $result = (new WeeklySwingParamsetPromotionService())->execute($draft['param_set_id'], 1, 21);

        $this->assertSame('BLOCKED', $result['status']);
        $this->assertSame('WS_PARAMSET_PROMOTION_IS_GATE_FAILED', $result['reason_code']);
        $this->assertFalse($result['is_acceptance']['gates']['minimum_days_covered']);
        $this->assertSame(70, $result['is_acceptance']['coverage']['effective_min_days_covered']);
        $this->assertSame('DRAFT', DB::table('watchlist_param_sets')->value('status'));
    }

    private function payload(): array
    {
        return json_decode((string) file_get_contents(base_path(
            'docs/watchlist/system/policies/weekly_swing/db/PARAMSET_WS_ACTIVE_EXAMPLE.json'
        )), true);
    }

    private function insertPassingProof(): void
    {
        $draft = DB::table('watchlist_param_sets')->where('policy_code', 'WS')->where('status', 'DRAFT')->first();
        $repository = new WatchlistBacktestOfficialEvidenceRepository();
        $evaluatedTrades = [];
        $runtimeTrades = [];
        for ($id = 1; $id <= 150; $id++) {
            $evaluatedTrades[] = [
                'trade_date' => '2025-01-01',
                'ticker_id' => $id,
                'ticker' => 'T'.$id,
                'bucket_code' => 'TOP_PICKS',
                'metrics_ready' => true,
                'ret_net' => 0.01,
                'entry_publication_id' => 100,
                'entry_publication_version' => 1,
                'entry_run_id' => 90,
            ];
            $runtimeTrades[] = [
                'trade_date' => '2025-01-01',
                'ticker_id' => $id,
                'score_total' => 0.9,
                'source_reference' => ['publication_id' => 100, 'publication_version' => 1, 'run_id' => 90],
            ];
        }
        $evidence = $repository->buildManifest('WS', 1, [
            'trades' => $runtimeTrades,
            'official_evidence' => [
                'universe' => [[
                    'asof_eod_date' => '2025-01-01',
                    'ticker_id' => 1,
                    'ticker_code' => 'T1',
                    'required_ok' => true,
                    'guard_ok' => true,
                    'eligible_ok' => true,
                    'source_publication_id' => 100,
                    'source_publication_version' => 1,
                    'source_run_id' => 90,
                ]],
                'cutoffs' => [[
                    'asof_eod_date' => '2025-01-01',
                    'top_cutoff_score' => 0.8,
                    'secondary_cutoff_score' => 0.65,
                    'source_publication_id' => 100,
                    'source_publication_version' => 1,
                    'source_run_id' => 90,
                ]],
            ],
        ], $evaluatedTrades);
        $manifest = $evidence['manifest'];

        DB::table('watchlist_bt_eval')->insert([
            'eval_id' => 11,
            'policy_code' => 'WS',
            'param_id' => 1,
            'eval_model' => (string) $draft->eval_model,
            'eval_model_hash' => (string) $draft->eval_model_hash,
            'implementation_version' => (string) $draft->implementation_version,
            'implementation_hash' => (string) $draft->implementation_hash,
            'paramset_hash' => (string) $draft->params_hash,
            'from_date' => '2025-01-01',
            'to_date' => '2025-04-10',
            'days_covered' => 80,
            'picks_count' => 150,
            'picks_hash' => $manifest['picks_hash'],
            'universe_count' => $manifest['universe_count'],
            'universe_hash' => $manifest['universe_hash'],
            'cutoff_count' => $manifest['cutoff_count'],
            'cutoffs_hash' => $manifest['cutoffs_hash'],
            'evidence_manifest_hash' => $manifest['evidence_manifest_hash'],
            'market_data_lineage_hash' => $manifest['market_data_lineage_hash'],
            'avg_ret_net_top' => 0.02,
            'median_ret_net_top' => 0.01,
            'p25_ret_net_top' => -0.02,
            'month_win_rate_min' => 0.50,
            'month_avg_ret_net_min' => -0.005,
        ]);
        $repository->persist(11, $evidence);
        DB::table('watchlist_bt_oos_eval_ws')->insert([
            'oos_id' => 21,
            'policy_code' => 'WS',
            'policy_version' => 'WS_EOD_RUNTIME',
            'eval_model' => (string) $draft->eval_model,
            'paramset_hash' => (string) $draft->params_hash,
            'eval_model_hash' => (string) $draft->eval_model_hash,
            'implementation_version' => (string) $draft->implementation_version,
            'implementation_hash' => (string) $draft->implementation_hash,
            'is_evidence_manifest_hash' => $manifest['evidence_manifest_hash'],
            'param_id_best_is' => 1,
            'is_eval_id' => 11,
            'from_date_is' => '2025-01-01',
            'to_date_is' => '2025-04-10',
            'from_date_oos' => '2025-04-11',
            'to_date_oos' => '2025-05-10',
            'days_covered_oos' => 20,
            'picks_count_oos' => 50,
            'avg_ret_net_top_oos' => 0.01,
            'median_ret_net_top_oos' => 0.005,
            'p25_ret_net_top_oos' => -0.02,
            'month_win_rate_min_oos' => 0.50,
        ]);
    }

    private function insertPreviousActive(): void
    {
        $payload = $this->payload();
        $payload['paramset_code'] = 'WS_PREVIOUS_ACTIVE';
        $now = date('Y-m-d H:i:s');
        DB::table('watchlist_param_sets')->insert([
            'policy_code' => 'WS',
            'policy_version' => 'WS_EOD_RUNTIME',
            'schema_version' => 'PARAMSET_JSON',
            'hash_contract' => json_encode($payload['hash_contract']),
            'provenance_json' => '{}',
            'status' => 'ACTIVE',
            'params_json' => json_encode($payload),
            'params_hash' => sha1(json_encode($payload)),
            'eval_model' => 'ENTRY=NEXT_OPEN;EXIT=STOP_TP_OR_TIME;HOLD=5;FEE=IDR_FIXED;SLIP=0;GAP=OPEN;PX=IDX_BANDS',
            'eval_model_hash' => sha1('ENTRY=NEXT_OPEN;EXIT=STOP_TP_OR_TIME;HOLD=5;FEE=IDR_FIXED;SLIP=0;GAP=OPEN;PX=IDX_BANDS'),
            'implementation_version' => 'WS_CANONICAL_IS_C171_V1',
            'implementation_hash' => sha1('WS_CANONICAL_IS_C171_V1|PLAN_RECOMMENDATION_CONFIRM_REPLAY|PUBLISHED_EOD|NO_FUTURE_ROUTING'),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}

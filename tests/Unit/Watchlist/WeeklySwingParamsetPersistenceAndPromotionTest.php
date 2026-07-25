<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WeeklySwingParamsetDraftImportService;
use App\Application\Watchlist\Services\WeeklySwingParamsetPromotionService;
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
        $this->assertSame('WS_PARAMSET_PROMOTION_OFFICIAL_SUPPORT_EVIDENCE_MISSING', $result['reason_code']);
        $this->assertFalse($result['official_support_evidence']['universe_and_cutoffs_present']);
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
        DB::table('watchlist_bt_eval')->insert([
            'eval_id' => 11,
            'policy_code' => 'WS',
            'param_id' => 1,
            'eval_model' => 'ENTRY=NEXT_OPEN;EXIT=STOP_TP_OR_TIME;HOLD=5;FEE=IDR_FIXED;SLIP=0;GAP=OPEN;PX=IDX_BANDS',
            'paramset_hash' => sha1('c169-test-paramset'),
            'from_date' => '2025-01-01',
            'to_date' => '2025-04-10',
            'days_covered' => 80,
            'picks_count' => 150,
            'avg_ret_net_top' => 0.02,
            'median_ret_net_top' => 0.01,
            'p25_ret_net_top' => -0.02,
            'month_win_rate_min' => 0.50,
            'month_avg_ret_net_min' => -0.005,
        ]);
        DB::table('watchlist_bt_oos_eval_ws')->insert([
            'oos_id' => 21,
            'policy_code' => 'WS',
            'policy_version' => 'WS_EOD_RUNTIME',
            'eval_model' => 'ENTRY=NEXT_OPEN;EXIT=STOP_TP_OR_TIME;HOLD=5;FEE=IDR_FIXED;SLIP=0;GAP=OPEN;PX=IDX_BANDS',
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

        $picks = [];
        for ($id = 1; $id <= 150; $id++) {
            $picks[] = [
                'eval_id' => 11,
                'policy_code' => 'WS',
                'param_id' => 1,
                'asof_eod_date' => '2025-01-01',
                'ticker_id' => $id,
                'ret_net' => 0.01,
            ];
        }
        DB::table('watchlist_bt_picks_ws')->insert($picks);
        DB::table('watchlist_bt_universe_ws')->insert([
            'eval_id' => 11,
            'asof_eod_date' => '2025-01-01',
            'ticker_id' => 1,
            'eligible_ok' => 1,
        ]);
        DB::table('watchlist_bt_cutoffs_ws')->insert([
            'eval_id' => 11,
            'policy_code' => 'WS',
            'param_id' => 1,
            'asof_eod_date' => '2025-01-01',
            'top_cutoff_score' => 0.8,
            'secondary_cutoff_score' => 0.65,
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
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}

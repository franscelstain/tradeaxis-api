<?php

use App\Application\MarketData\Services\DeterministicHashService;
use App\Application\MarketData\Services\PublicationProjectionReconciliationService;
use App\Application\MarketData\Services\PublicationProjectionRepairService;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

class PublicationProjectionReconciliationServiceTest extends TestCase
{
    use UsesMarketDataSqlite;

    private $publication;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
        $this->publication = (object) [
            'publication_id' => 10,
            'run_id' => 25,
            'publication_version' => 1,
            'trade_date' => '2026-03-20',
        ];
    }

    protected function tearDown(): void
    {
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    private function repository($publication = 'default', $rawPointer = 'default'): EodPublicationRepository
    {
        $resolved = $publication === 'default' ? $this->publication : $publication;
        $raw = $rawPointer === 'default' ? (object) ['pointer_publication_id' => 10] : $rawPointer;

        return new class($resolved, $raw) extends EodPublicationRepository {
            private $resolved;
            private $raw;

            public function __construct($resolved, $raw)
            {
                parent::__construct(new DeterministicHashService());
                $this->resolved = $resolved;
                $this->raw = $raw;
            }

            public function resolveCurrentReadablePublicationForTradeDate($tradeDate)
            {
                return $this->resolved;
            }

            public function findRawCurrentPublicationStateForTradeDate($tradeDate)
            {
                return $this->raw;
            }
        };
    }

    private function service($publication = 'default', $rawPointer = 'default'): PublicationProjectionReconciliationService
    {
        return new PublicationProjectionReconciliationService(
            $this->repository($publication, $rawPointer),
            new DeterministicHashService()
        );
    }

    private function repairService($publication = 'default', $rawPointer = 'default'): PublicationProjectionRepairService
    {
        $repository = $this->repository($publication, $rawPointer);
        $reconciliation = new PublicationProjectionReconciliationService($repository, new DeterministicHashService());

        return new PublicationProjectionRepairService(
            $repository,
            new EodArtifactRepository(),
            $reconciliation
        );
    }

    private function seedRepairRun(): void
    {
        DB::table('eod_runs')->insert([
            'run_id' => 25,
            'trade_date_requested' => '2026-03-20',
            'source' => 'api',
            'bars_rows_written' => 1,
            'indicators_rows_written' => 1,
            'eligibility_rows_written' => 1,
            'created_at' => '2026-03-20 17:00:00',
        ]);
    }

    private function seedExactArtifacts(): void
    {
        DB::table('eod_bars')->insert($this->barRow('eod_bars'));
        DB::table('eod_bars_history')->insert($this->barRow('eod_bars_history'));
        DB::table('eod_indicators')->insert($this->indicatorRow('eod_indicators'));
        DB::table('eod_indicators_history')->insert($this->indicatorRow('eod_indicators_history'));
        DB::table('eod_eligibility')->insert($this->eligibilityRow('eod_eligibility'));
        DB::table('eod_eligibility_history')->insert($this->eligibilityRow('eod_eligibility_history'));
    }

    private function barRow(string $table): array
    {
        return [
            'publication_id' => 10, 'trade_date' => '2026-03-20', 'ticker_id' => 1,
            'open' => 100, 'high' => 110, 'low' => 99, 'close' => 108, 'volume' => 1000,
            'adj_close' => 108, 'source' => 'YAHOO_FINANCE', 'run_id' => 25,
            'listing_id' => 1001, 'previous_close' => 100, 'canonicalization_version' => 'canonical_v1',
            'price_product_code' => 'RAW', 'quality_state' => 'VALID', 'config_snapshot_id' => 7,
            'source_observation_id' => 501, 'created_at' => '2026-03-20 17:20:00',
        ];
    }

    private function indicatorRow(string $table): array
    {
        return [
            'publication_id' => 10, 'trade_date' => '2026-03-20', 'ticker_id' => 1,
            'is_valid' => 1, 'indicator_set_version' => 'v1', 'sector_code' => 'A11',
            'roc20' => 0.12, 'run_id' => 25, 'listing_id' => 1001,
            'formula_version' => 'formula_v1', 'config_snapshot_id' => 7,
            'factor_set_id' => 8, 'factor_set_hash' => hash('sha256', 'factor'),
            'price_product_code' => 'STRUCTURAL_ADJUSTED', 'price_product_version' => 'pp_v1',
            'created_at' => '2026-03-20 17:20:00',
        ];
    }

    private function eligibilityRow(string $table): array
    {
        return [
            'publication_id' => 10, 'trade_date' => '2026-03-20', 'ticker_id' => 1,
            'eligible' => 1, 'reason_code' => null, 'run_id' => 25, 'listing_id' => 1001,
            'universe_membership_state' => 'IN_SCOPE', 'bar_expectation_state' => 'EXPECTED',
            'delivery_state' => 'AVAILABLE', 'canonical_quality_state' => 'VALID',
            'liquidity_state' => 'PASS', 'temporal_status_state' => 'ACTIVE',
            'trading_status_revision_id' => 701, 'trading_status_source_observation_id' => 702,
            'event_risk_state' => 'CLEAR', 'eligibility_reasons_json' => '[]', 'config_snapshot_id' => 7,
            'created_at' => '2026-03-20 17:20:00',
        ];
    }

    public function test_exact_projection_and_current_publication_history_reconcile_to_pass_and_persist_counts(): void
    {
        $this->seedExactArtifacts();

        $result = $this->service()->reconcileTradeDate('2026-03-20');

        $this->assertSame('PASS', $result['reconciliation_state']);
        $this->assertSame('RESOLVED', $result['pointer_state']);
        $this->assertSame(0, $result['mismatch_count']);
        $this->assertSame(1, $result['bars_projection_count']);
        $this->assertSame(1, $result['bars_history_count']);
        $this->assertSame(1, DB::table('md_publication_projection_reconciliations')->count());
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $result['reconciliation_hash']);
    }

    public function test_missing_history_is_detected_without_repairing_projection_or_history(): void
    {
        $this->seedExactArtifacts();
        DB::table('eod_bars_history')->delete();
        $beforeProjection = DB::table('eod_bars')->count();

        $result = $this->service()->reconcileTradeDate('2026-03-20');

        $this->assertSame('FAIL', $result['reconciliation_state']);
        $this->assertSame(1, $result['bars_missing_history_count']);
        $this->assertSame($beforeProjection, DB::table('eod_bars')->count());
        $this->assertSame(0, DB::table('eod_bars_history')->count());
    }

    public function test_missing_projection_is_detected_without_deleting_history(): void
    {
        $this->seedExactArtifacts();
        DB::table('eod_eligibility')->delete();

        $result = $this->service()->reconcileTradeDate('2026-03-20');

        $this->assertSame('FAIL', $result['reconciliation_state']);
        $this->assertSame(1, $result['eligibility_missing_projection_count']);
        $this->assertSame(1, DB::table('eod_eligibility_history')->count());
    }

    public function test_value_mismatch_and_wrong_publication_binding_are_detected(): void
    {
        $this->seedExactArtifacts();
        DB::table('eod_bars')->where('ticker_id', 1)->update(['close' => 109]);
        DB::table('eod_indicators')->where('ticker_id', 1)->update(['run_id' => 99]);

        $result = $this->service()->reconcileTradeDate('2026-03-20');
        $sample = json_decode($result['mismatch_sample_json'], true);

        $this->assertSame('FAIL', $result['reconciliation_state']);
        $this->assertSame(1, $result['bars_value_mismatch_count']);
        $this->assertSame(1, $result['indicators_value_mismatch_count']);
        $this->assertNotEmpty($sample);
    }

    public function test_projection_rows_without_a_resolvable_current_publication_are_persisted_as_orphans(): void
    {
        DB::table('eod_bars')->insert($this->barRow('eod_bars'));

        $result = $this->service(null, null)->reconcileTradeDate('2026-03-20');

        $this->assertSame('FAIL', $result['reconciliation_state']);
        $this->assertSame('MISSING', $result['pointer_state']);
        $this->assertSame(1, $result['orphan_projection_row_count']);
        $this->assertSame(1, DB::table('eod_bars')->count());
    }

    public function test_reconciliation_hash_is_stable_for_equivalent_content_even_when_execution_identity_changes(): void
    {
        $this->seedExactArtifacts();
        $first = $this->service()->reconcileTradeDate('2026-03-20');
        $second = $this->service()->reconcileTradeDate('2026-03-20');

        $this->assertSame($first['reconciliation_hash'], $second['reconciliation_hash']);
        $this->assertNotSame($first['reconciliation_uid'], $second['reconciliation_uid']);
        $this->assertSame(2, DB::table('md_publication_projection_reconciliations')->count());
    }

    public function test_controlled_repair_rebuilds_only_projection_from_immutable_history_and_reconciles_to_pass(): void
    {
        $this->seedExactArtifacts();
        $this->seedRepairRun();
        DB::table('eod_bars')->where('ticker_id', 1)->update(['close' => 999]);
        DB::table('eod_indicators')->where('ticker_id', 1)->update(['run_id' => 999]);
        DB::table('eod_eligibility')->where('ticker_id', 1)->update([
            'trading_status_revision_id' => null,
            'trading_status_source_observation_id' => null,
        ]);
        $historyCloseBefore = DB::table('eod_bars_history')->where('ticker_id', 1)->value('close');

        $result = $this->repairService()->repairTradeDate('2026-03-20');

        $this->assertSame('REBUILT_AND_VERIFIED', $result['repair_state']);
        $this->assertSame('FAIL', $result['before']['reconciliation_state']);
        $this->assertSame('PASS', $result['after']['reconciliation_state']);
        $this->assertSame(0, $result['after']['mismatch_count']);
        $this->assertEquals($historyCloseBefore, DB::table('eod_bars')->where('ticker_id', 1)->value('close'));
        $this->assertEquals($historyCloseBefore, DB::table('eod_bars_history')->where('ticker_id', 1)->value('close'));
        $this->assertSame(25, (int) DB::table('eod_indicators')->where('ticker_id', 1)->value('run_id'));
        $this->assertSame(701, (int) DB::table('eod_eligibility')->where('ticker_id', 1)->value('trading_status_revision_id'));
        $this->assertSame(702, (int) DB::table('eod_eligibility')->where('ticker_id', 1)->value('trading_status_source_observation_id'));
    }

    public function test_controlled_repair_refuses_unresolved_current_publication_without_mutating_projection(): void
    {
        $this->seedExactArtifacts();
        $this->seedRepairRun();
        $before = DB::table('eod_bars')->where('ticker_id', 1)->value('close');

        try {
            $this->repairService(null, null)->repairTradeDate('2026-03-20');
            $this->fail('Expected unresolved current publication to block projection repair.');
        } catch (RuntimeException $e) {
            $this->assertSame('PROJECTION_REPAIR_CURRENT_PUBLICATION_UNRESOLVED', $e->getMessage());
        }

        $this->assertEquals($before, DB::table('eod_bars')->where('ticker_id', 1)->value('close'));
    }

    public function test_controlled_repair_dry_run_marks_projection_drift_repairable_only_after_history_validation(): void
    {
        $this->seedExactArtifacts();
        $this->seedRepairRun();
        DB::table('eod_bars')->where('ticker_id', 1)->update(['close' => 999]);

        $result = $this->repairService()->inspectTradeDate('2026-03-20');

        $this->assertSame('REPAIRABLE_FROM_IMMUTABLE_HISTORY', $result['repairability_state']);
        $this->assertSame(10, $result['publication_id']);
        $this->assertSame(25, $result['run_id']);
        $this->assertSame(1, $result['snapshot_counts']['bars']);
        $this->assertSame('FAIL', $result['reconciliation']['reconciliation_state']);
        $this->assertGreaterThan(0, $result['reconciliation']['mismatch_count']);
        $this->assertSame(999, (int) DB::table('eod_bars')->where('ticker_id', 1)->value('close'));
    }

    public function test_controlled_repair_refuses_immutable_history_bound_to_a_different_run(): void
    {
        $this->seedExactArtifacts();
        $this->seedRepairRun();
        DB::table('eod_bars_history')->where('ticker_id', 1)->update(['run_id' => 999]);
        $before = DB::table('eod_bars')->where('ticker_id', 1)->value('close');

        try {
            $this->repairService()->inspectTradeDate('2026-03-20');
            $this->fail('Expected invalid immutable-history run binding to block repair preflight.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('PROJECTION_REPAIR_HISTORY_IDENTITY_INVALID', $e->getMessage());
        }

        $this->assertEquals($before, DB::table('eod_bars')->where('ticker_id', 1)->value('close'));
    }

}

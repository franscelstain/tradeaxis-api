<?php

use App\Infrastructure\Persistence\MarketData\EodEvidenceRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/** Source-backed rows for Run_Artifacts_Format_LOCKED.md section 5. */
class B19InvalidBarsExportRowProvenanceTest extends TestCase
{
    use UsesMarketDataSqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
        foreach ([
            ['2026-03-24', 91, 4, 7004, 9004, 'ref_mapped', 'BAR_INVALID_OHLC_ORDER'],
            ['2026-03-24', 91, null, null, 9005, 'ref_unknown', 'BAR_TICKER_MAPPING_MISSING'],
            ['2026-03-24', 92, 8, 7008, 9008, 'ref_other_run', 'BAR_NON_POSITIVE_PRICE'],
            ['2026-03-25', 91, 9, 7009, 9009, 'ref_other_date', 'BAR_NEGATIVE_VOLUME'],
        ] as $row) {
            DB::table('eod_invalid_bars')->insert([
                'trade_date' => $row[0], 'run_id' => $row[1], 'ticker_id' => $row[2],
                'listing_id' => $row[3], 'source_observation_id' => $row[4],
                'source' => 'YAHOO_FINANCE', 'source_row_ref' => $row[5],
                'invalid_reason_code' => $row[6], 'created_at' => '2026-03-25 17:00:00',
            ]);
        }
        DB::table('eod_bars')->insert([
            'trade_date' => '2026-03-24', 'ticker_id' => 3, 'listing_id' => 7003,
            'source_observation_id' => 9003, 'run_id' => 91, 'publication_id' => 990,
            'open' => 100, 'high' => 110, 'low' => 99, 'close' => 108, 'volume' => 1000,
            'source' => 'YAHOO_FINANCE', 'created_at' => '2026-03-25 17:00:00',
        ]);
    }

    protected function tearDown(): void
    {
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    public function test_the_export_rows_equal_their_own_persisted_rejections_with_null_identity_preserved(): void
    {
        $beforeInvalid = DB::table('eod_invalid_bars')->orderBy('invalid_bar_id')->get()->toArray();
        $beforeCanonical = DB::table('eod_bars')->get()->toArray();
        $rows = (new EodEvidenceRepository())->exportInvalidBarsRows('2026-03-24', 91);
        $this->assertSame([
            ['trade_date' => '2026-03-24', 'listing_id' => null, 'ticker_id' => null, 'source_observation_id' => '9005', 'source' => 'YAHOO_FINANCE', 'source_row_ref' => 'ref_unknown', 'invalid_reason_code' => 'BAR_TICKER_MAPPING_MISSING'],
            ['trade_date' => '2026-03-24', 'listing_id' => '7004', 'ticker_id' => '4', 'source_observation_id' => '9004', 'source' => 'YAHOO_FINANCE', 'source_row_ref' => 'ref_mapped', 'invalid_reason_code' => 'BAR_INVALID_OHLC_ORDER'],
        ], $rows);
        $this->assertEquals($beforeInvalid, DB::table('eod_invalid_bars')->orderBy('invalid_bar_id')->get()->toArray(), 'audit export cannot rewrite rejections');
        $this->assertEquals($beforeCanonical, DB::table('eod_bars')->get()->toArray(), 'audit export cannot rewrite canonical bars');
        $this->assertSame([null, '4'], array_column($rows, 'ticker_id'), 'the canonical ticker is absent from the rejection export');
    }

    public function test_a_bounded_export_reports_the_full_count_of_its_run_and_date(): void
    {
        config()->set('market_data.evidence.invalid_bars_export_sample_limit', 1);
        try {
            $repository = new EodEvidenceRepository();
            $rows = $repository->exportInvalidBarsRows('2026-03-24', 91);
            $this->assertCount(1, $rows);
            $this->assertSame('ref_unknown', $rows[0]['source_row_ref']);
            $this->assertSame(2, $repository->countInvalidBarsRows('2026-03-24', 91));
        } finally {
            config()->set('market_data.evidence.invalid_bars_export_sample_limit', 1000);
        }
    }
}

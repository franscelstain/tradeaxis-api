<?php

use App\Application\MarketData\Services\MarketDataEvidenceExportService;
use Illuminate\Support\Facades\DB;
use Tests\Support\R0025SyntheticV2World;
use Tests\Support\UsesMarketDataMariaDb;

/** The real writer and repository on a real sealed run, with two separately seeded rejected rows. */
class B19InvalidBarsRealRunExportTest extends TestCase
{
    use UsesMarketDataMariaDb;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataMariaDb();
    }

    protected function tearDown(): void
    {
        config()->set('market_data.evidence.invalid_bars_export_sample_limit', 1000);
        $this->tearDownMarketDataMariaDb();
        parent::tearDown();
    }

    public function test_the_real_file_and_summary_match_rejected_rows_and_disclose_sampling(): void
    {
        $world = R0025SyntheticV2World::build();
        $runId = (int) $world['run_id'];
        $run = DB::table('eod_runs')->where('run_id', $runId)->first();
        $date = (string) $run->trade_date_requested;
        $bar = DB::table('eod_bars')->where('trade_date', $date)->first();
        $this->assertNotNull($bar, 'precondition: the real run has a canonical bar');
        DB::table('eod_invalid_bars')->where('run_id', $runId)->where('trade_date', $date)->delete();
        foreach ([
            [null, null, 9005, 'ref_unknown', 'BAR_TICKER_MAPPING_MISSING'],
            [9876, 7004, (int) $bar->source_observation_id, 'ref_ohlc', 'BAR_INVALID_OHLC_ORDER'],
        ] as $row) {
            DB::table('eod_invalid_bars')->insert([
                'trade_date' => $date, 'run_id' => $runId, 'ticker_id' => $row[0], 'listing_id' => $row[1],
                'source_observation_id' => $row[2], 'source' => 'YAHOO_FINANCE', 'source_row_ref' => $row[3],
                'invalid_reason_code' => $row[4], 'created_at' => $date.' 17:30:00',
            ]);
        }
        $beforeInvalid = DB::table('eod_invalid_bars')->where('run_id', $runId)->where('trade_date', $date)->orderBy('invalid_bar_id')->get()->toArray();
        $beforeBar = DB::table('eod_bars')->where('trade_date', $date)->count();
        config()->set('market_data.evidence.invalid_bars_export_sample_limit', 1);

        $dir = sys_get_temp_dir().'/md_b19_invalid_real_'.uniqid('', true);
        app(MarketDataEvidenceExportService::class)->exportRunEvidence($runId, $dir);
        $summary = json_decode((string) file_get_contents($dir.'/run_summary.json'), true);
        $this->assertSame(['sampled' => true, 'exported_row_count' => 1, 'total_row_count' => 2, 'sample_limit' => 1], $summary['invalid_bars_export']);
        $h = fopen($dir.'/invalid_bars_export.csv', 'r');
        $this->assertSame(['trade_date', 'listing_id', 'ticker_id', 'source_observation_id', 'source', 'source_row_ref', 'invalid_reason_code'], fgetcsv($h));
        $this->assertSame([$date, '', '', '9005', 'YAHOO_FINANCE', 'ref_unknown', 'BAR_TICKER_MAPPING_MISSING'], fgetcsv($h));
        $this->assertFalse(fgetcsv($h), 'the second rejected row is excluded by the declared bound');
        fclose($h);
        $this->assertEquals($beforeInvalid, DB::table('eod_invalid_bars')->where('run_id', $runId)->where('trade_date', $date)->orderBy('invalid_bar_id')->get()->toArray());
        $this->assertSame($beforeBar, DB::table('eod_bars')->where('trade_date', $date)->count());

        config()->set('market_data.evidence.invalid_bars_export_sample_limit', 3);
        $fullDir = sys_get_temp_dir().'/md_b19_invalid_full_'.uniqid('', true);
        app(MarketDataEvidenceExportService::class)->exportRunEvidence($runId, $fullDir);
        $fullSummary = json_decode((string) file_get_contents($fullDir.'/run_summary.json'), true);
        $this->assertSame(['sampled' => false, 'exported_row_count' => 2, 'total_row_count' => 2, 'sample_limit' => 3], $fullSummary['invalid_bars_export']);
        $full = array_map('str_getcsv', file($fullDir.'/invalid_bars_export.csv', FILE_IGNORE_NEW_LINES));
        $this->assertCount(3, $full);
        $this->assertSame([$date, '7004', '9876', (string) $bar->source_observation_id, 'YAHOO_FINANCE', 'ref_ohlc', 'BAR_INVALID_OHLC_ORDER'], $full[2]);
    }
}

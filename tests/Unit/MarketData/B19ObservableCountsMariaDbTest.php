<?php

use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Models\EodRun;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Support\R0025SyntheticV2World;
use Tests\Support\UsesMarketDataMariaDb;

class B19ObservableCountsMariaDbTest extends TestCase
{
    use UsesMarketDataMariaDb;
    use \Tests\Support\RetainsB19ObservedEvidence;
    protected function setUp(): void { parent::setUp(); $this->bootMarketDataMariaDb(); }
    protected function tearDown(): void { Carbon::setTestNow(); $this->tearDownMarketDataMariaDb(); parent::tearDown(); }

    public function test_real_count_producers_status_and_export_preserve_null_warning_and_explicit_unavailable_counts(): void
    {
        $manualContext = new \App\Application\MarketData\Services\ManualSourceInputContext();
        app()->instance(\App\Application\MarketData\Services\ManualSourceInputContext::class, $manualContext);
        $world = R0025SyntheticV2World::build(); $run = EodRun::findOrFail($world['run_id']);
        // Independently supplied input: one valid OHLC bar, one listing, one
        // session (insufficient ATR lookback). The blocked eligibility row is
        // observable; warning population remains undefined under D003.
        $this->assertSame(1, DB::table('eod_bars_history')->where('publication_id', $world['publication_id'])->count());
        $this->assertSame(1, DB::table('eod_eligibility_history')->where('publication_id', $world['publication_id'])->where('eligible', 0)->count());
        $this->assertSame(0, (int) $run->invalid_bar_count); $this->assertSame(1, (int) $run->hard_reject_count); $this->assertNull($run->warning_count);
        $beforeRow = (array) DB::table('eod_runs')->where('run_id', $run->run_id)->first();
        $warnBefore = DB::table('eod_run_events')->where('run_id', $run->run_id)->where('severity', 'WARN')->count();
        for ($i = 0; $i < 2; $i++) (new EodRunRepository())->recordObservation($run, 'detector', 'FINALIZE', 'DIAGNOSTIC_WARNING', 'WARN',
            'Explicit diagnostic warning, not an authorized warning-count population.', null, ['control_ordinal' => $i]);
        $this->assertSame($warnBefore + 2, DB::table('eod_run_events')->where('run_id', $run->run_id)->where('severity', 'WARN')->count());
        $status = new \App\Console\Commands\MarketData\OperationalStatusCommand(); $status->setLaravel($this->app);
        $statusTester = new \Symfony\Component\Console\Tester\CommandTester($status);
        $code = $statusTester->execute(['--requested_date' => '2026-03-23', '--run_id' => (string) $run->run_id]);
        $this->assertSame(0, $code, $statusTester->getDisplay());
        $observed = json_decode(trim($statusTester->getDisplay()), true);
        $this->assertSame(0, $observed['counts']['invalid_bar_count']); $this->assertSame(1, $observed['counts']['hard_reject_count']);
        $this->assertArrayHasKey('warning_count', $observed['counts']); $this->assertNull($observed['counts']['warning_count'], 'WARN events do not define the D003 warning population.');
        $this->assertStringContainsString('NULL_PRESERVED_UNDER_D-MD-B19-A001-003', $observed['warning_count_provenance']);
        $base = getenv('B19_E049_STATE_DIR') ?: sys_get_temp_dir().'/e049-counts-'.bin2hex(random_bytes(8));
        if (! is_dir($base)) mkdir($base, 0775, true);
        $export = new \App\Console\Commands\MarketData\ExportEvidenceCommand(); $export->setLaravel($this->app);
        $exportTester = new \Symfony\Component\Console\Tester\CommandTester($export);
        try {
            $exportCode = $exportTester->execute(['--run_id' => (string) $run->run_id, '--output_dir' => $base.'/count_evidence']);
            $this->assertSame(0, $exportCode, $exportTester->getDisplay());
            $pack = json_decode(file_get_contents($base.'/count_evidence/evidence_pack.json'), true);
            $this->assertSame(0, $pack['run_summary']['invalid_bar_count']); $this->assertSame(1, $pack['run_summary']['hard_reject_count']);
            $this->assertArrayHasKey('warning_count', $pack['run_summary']); $this->assertNull($pack['run_summary']['warning_count'], 'Evidence export must preserve an unavailable warning population.');
            $this->assertSame($beforeRow, (array) DB::table('eod_runs')->where('run_id', $run->run_id)->first());
            DB::beginTransaction();
            try {
                DB::table('eod_runs')->where('run_id', $run->run_id)->update(['invalid_bar_count' => null, 'hard_reject_count' => null]);
                $this->assertSame(2, $statusTester->execute(['--requested_date' => '2026-03-23', '--run_id' => (string) $run->run_id]));
                $unavailable = json_decode(trim($statusTester->getDisplay()), true);
                $this->assertNull($unavailable['counts']['invalid_bar_count'], 'Unknown invalid count must not be normalized to zero.'); $this->assertNull($unavailable['counts']['hard_reject_count']);
                $this->assertFalse($unavailable['operationally_healthy']);
                $this->assertContains('UNAVAILABLE:invalid_bar_count', $unavailable['observability_defects']);
                $this->assertContains('UNAVAILABLE:hard_reject_count', $unavailable['observability_defects']);
                $this->retainObservedWorld('observable_counts_and_unavailable_control', [$run->run_id], ['normal_status' => $observed,
                    'export_command_exit' => $exportCode, 'export_command_output' => $exportTester->getDisplay(),
                    'unavailable_status' => $unavailable, 'persisted_run_summary_export' => $pack['run_summary']]);
            } finally { DB::rollBack(); }
            DB::beginTransaction();
            $oldProfile = config('market_data_runtime.artifact_hash_profile');
            try {
                // One valid and one structurally impossible OHLC row. Both
                // expected listings remain in the two-listing universe.
                DB::table('tickers')->insert(['ticker_id' => 975101, 'ticker_code' => 'E049BAD', 'company_name' => 'Independent rejected-count control',
                    'is_active' => 1, 'listed_date' => '2020-01-01', 'created_at' => '2020-01-01 00:00:00', 'updated_at' => '2020-01-01 00:00:00']);
                (new \App\Infrastructure\Persistence\MarketData\TemporalIdentityRepository())->ensureLegacyProjection();
                DB::table('md_market_calendar_revisions')->insert([
                    'revision_uid' => hash('sha256', 'e049-counts-independent-2026-03-24'), 'market_code' => 'IDX', 'market_segment' => 'REGULAR',
                    'cal_date' => '2026-03-24', 'timezone' => 'Asia/Jakarta', 'is_trading_day' => 1, 'is_half_day' => 0, 'session_state' => 'COMPLETED',
                    'session_open_at' => '2026-03-24 09:00:00', 'session_close_at' => '2026-03-24 16:00:00', 'completed_at' => '2026-03-24 16:00:00',
                    'recorded_at' => '2026-03-24 17:00:00', 'source_ref' => 'independent-e049-counts-session', 'source_version' => 'e049-test-only-v1',
                    'provenance_tier' => 'VERIFIED', 'reconciled_at' => '2026-03-24 17:00:00', 'reconciliation_source_ref' => 'independent-e049-counts-session',
                ]);
                file_put_contents($base.'/invalid_count.csv', "ticker_code,trade_date,open,high,low,close,volume,canonical_source,source_row_ref\nSYNV2,2026-03-24,100,110,95,105,1000,YAHOO_FINANCE,e049-valid\nE049BAD,2026-03-24,100,90,95,105,1000,YAHOO_FINANCE,e049-impossible-high-low\n");
                $manualContext->set($base.'/invalid_count.csv');
                config(['market_data_runtime.artifact_hash_profile' => 'market-data-row-hash/v1']);
                $rejected = $world['pipeline']->completeIngest(new \App\Application\MarketData\DTOs\MarketDataStageInput('2026-03-24', 'manual_file', null, 'INGEST_BARS', null, false, null, 'full_publish'));
                foreach (['COMPUTE_INDICATORS' => 'completeIndicators', 'BUILD_ELIGIBILITY' => 'completeEligibility', 'FINALIZE' => 'completeFinalize'] as $stage => $method) {
                    $rejected = $world['pipeline']->{$method}(new \App\Application\MarketData\DTOs\MarketDataStageInput('2026-03-24', 'manual_file', $rejected->run_id, $stage));
                }
                $this->assertSame(1, (int) $rejected->invalid_bar_count, 'Exactly the impossible OHLC row is rejected by the actual producer.');
                $this->assertSame(2, (int) $rejected->hard_reject_count, 'Both the missing bar and insufficient-history listing are blocked.');
                $this->assertNull($rejected->warning_count);
                $this->assertSame('NOT_READABLE', $rejected->publishability_state);
                $this->assertFalse(DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-24')->exists());
                $this->assertSame(2, $statusTester->execute(['--requested_date' => '2026-03-24', '--run_id' => (string) $rejected->run_id]));
                $rejectedStatus = json_decode(trim($statusTester->getDisplay()), true);
                $this->assertSame(1, $rejectedStatus['counts']['invalid_bar_count'], 'The real rejected row must remain observable in status.');
                $this->assertSame(2, $rejectedStatus['counts']['hard_reject_count']);
                app(\App\Application\MarketData\Services\MarketDataEvidenceExportService::class)->exportRunEvidence($rejected->run_id, $base.'/invalid_count_evidence');
                $rejectedPack = json_decode(file_get_contents($base.'/invalid_count_evidence/evidence_pack.json'), true);
                $this->assertSame(1, $rejectedPack['run_summary']['invalid_bar_count']);
                $this->assertSame(2, $rejectedPack['run_summary']['hard_reject_count']);
                $this->assertNull($rejectedPack['run_summary']['warning_count']);
                $this->retainObservedWorld('nonzero_invalid_count_producer_and_export', [$run->run_id, (int) $rejected->run_id],
                    ['status' => $rejectedStatus, 'exported_run_summary' => $rejectedPack['run_summary'], 'input_sha256' => hash_file('sha256', $base.'/invalid_count.csv')]);
            } finally { DB::rollBack(); $manualContext->set(null); config(['market_data_runtime.artifact_hash_profile' => $oldProfile]); }
        } finally {
            if (! getenv('B19_E049_STATE_DIR')) {
                foreach (['count_evidence', 'invalid_count_evidence'] as $name) {
                    foreach (glob($base.'/'.$name.'/*') as $file) if (is_file($file)) unlink($file);
                    if (is_dir($base.'/'.$name)) rmdir($base.'/'.$name);
                }
                if (is_file($base.'/invalid_count.csv')) unlink($base.'/invalid_count.csv');
                rmdir($base);
            }
        }
        $this->assertSame($beforeRow, (array) DB::table('eod_runs')->where('run_id', $run->run_id)->first());
    }
}

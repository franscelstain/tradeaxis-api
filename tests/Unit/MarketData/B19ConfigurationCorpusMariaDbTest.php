<?php

use App\Application\MarketData\Services\MarketDataConfigurationCorpusService;
use App\Console\Commands\MarketData\ConfigurationCorpusCommand;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Support\R0025SyntheticV2World;
use Tests\Support\UsesMarketDataMariaDb;

class B19ConfigurationCorpusMariaDbTest extends TestCase
{
    use UsesMarketDataMariaDb;
    protected function setUp(): void { parent::setUp(); $this->bootMarketDataMariaDb(); }
    protected function tearDown(): void { Carbon::setTestNow(); $this->tearDownMarketDataMariaDb(); parent::tearDown(); }

    public function test_real_sealed_corpus_counts_missing_bindings_in_current_and_historical_rows_without_resealing_or_deletion(): void
    {
        $world = R0025SyntheticV2World::build(['profile' => 'market-data-row-hash/v1']);
        $service = new MarketDataConfigurationCorpusService();
        $before = $service->report();
        $originalRun = (array) DB::table('eod_runs')->where('run_id', $world['run_id'])->first();
        $originalPublication = (array) DB::table('eod_publications')->where('publication_id', $world['publication_id'])->first();
        // These controlled rows represent imported historical states, not new authorized
        // sealing of an unbound candidate. Existing sealed records are never rewritten.
        $nextVersion = (int) $originalPublication['publication_version'];
        foreach ([['config_snapshot_id', null, 'SEALED'], ['config_hash', null, 'SEALED'],
            ['config_snapshot_id', 0, 'SEALED'], ['config_hash', '  ', 'SEALED'],
            ['config_hash', '', 'SEALED'], ['config_snapshot_id', null, 'UNSEALED'],
            ['config_hash', $originalRun['config_hash'], 'SEALED']] as [$field, $value, $seal]) {
            $run = $originalRun; unset($run['run_id']); $run[$field] = $value;
            $newRun = DB::table('eod_runs')->insertGetId($run);
            $publication = $originalPublication; unset($publication['publication_id']);
            $publication['run_id'] = $newRun; $publication['is_current'] = 0;
            $publication['publication_version'] = ++$nextVersion;
            $publication['seal_state'] = $seal;
            DB::table('eod_publications')->insert($publication);
        }
        $rowsBeforeReport = DB::table('eod_publications')->orderBy('publication_id')->get()->toJson();
        $after = $service->report();
        $this->assertSame($before['config_unbound_publication_count'] + 5, $after['config_unbound_publication_count']);
        $this->assertSame($before['sealed_publication_count'] + 6, $after['sealed_publication_count']);
        $command = new ConfigurationCorpusCommand(); $command->setLaravel($this->app);
        $tester = new \Symfony\Component\Console\Tester\CommandTester($command);
        $this->assertSame(2, $tester->execute([]));
        $output = json_decode(trim($tester->getDisplay()), true);
        $this->assertSame($after['config_unbound_publication_count'], $output['config_unbound_publication_count']);
        $this->assertSame('ALL_SEALED_PUBLICATIONS_INCLUDING_SUPERSEDED_HISTORY', $output['population']);
        $this->assertStringContainsString('not conformance or replay PASS', $output['claim_limit']);
        $this->assertSame($rowsBeforeReport, DB::table('eod_publications')->orderBy('publication_id')->get()->toJson());
        $this->assertSame($originalRun, (array) DB::table('eod_runs')->where('run_id', $world['run_id'])->first());
        $this->assertSame($originalPublication, (array) DB::table('eod_publications')->where('publication_id', $world['publication_id'])->first());
    }

    public function test_zero_is_reported_explicitly_on_an_empty_actual_mariadb_temporary_population(): void
    {
        // MariaDB temporary-table shadowing isolates the zero-population query without
        // deleting, truncating, resealing or retiring any canonical persistent record.
        DB::statement('CREATE TEMPORARY TABLE eod_publications AS SELECT original_publication.* FROM eod_publications AS original_publication WHERE 1 = 0');
        try {
            $command = new ConfigurationCorpusCommand(); $command->setLaravel($this->app);
            $tester = new \Symfony\Component\Console\Tester\CommandTester($command);
            $this->assertSame(0, $tester->execute([]));
            $output = json_decode(trim($tester->getDisplay()), true);
            $this->assertArrayHasKey('config_unbound_publication_count', $output);
            $this->assertSame(0, $output['config_unbound_publication_count']);
            $this->assertSame(0, $output['sealed_publication_count']);
        } finally { DB::statement('DROP TEMPORARY TABLE eod_publications'); }
    }
}

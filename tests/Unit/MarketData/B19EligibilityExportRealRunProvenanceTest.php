<?php

use App\Application\MarketData\Services\MarketDataEvidenceExportService;
use Illuminate\Support\Facades\DB;
use Tests\Support\R0025SyntheticV2World;
use Tests\Support\UsesMarketDataMariaDb;

/**
 * `MD-B19` — `eligibility_export.csv` of a REAL run, read against the database (`MD-S075-R0133..R0138`, `R0143..R0145`).
 *
 * `B19EligibilityExportRowProvenanceTest` proves the projection on a seeded history of superseded, current, unsealed
 * and neighbouring publications. This guard proves the other half on what the real pipeline wrote: the FILE the real
 * exporter writes agrees, header and row by row, with an INDEPENDENT read of `eod_eligibility`, `eod_publications`,
 * `eod_runs` and `eod_reason_codes`, and is byte-identical after a superseded neighbour publication with different rows
 * for the same trade date is added to the history.
 *
 * Building the world takes minutes, so the checks share one export.
 */
class B19EligibilityExportRealRunProvenanceTest extends TestCase
{
    use UsesMarketDataMariaDb;

    private const HEADER = ['trade_date', 'listing_id', 'ticker_id', 'publication_id', 'data_usable', 'reason_codes', 'eligible', 'reason_code'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataMariaDb();
    }

    protected function tearDown(): void
    {
        $this->tearDownMarketDataMariaDb();
        parent::tearDown();
    }

    /** @return array{0:array<int,string>,1:array<int,array<string,string>>,2:string} header, rows (by header), raw bytes */
    private function readCsv(string $dir): array
    {
        $path = $dir.'/eligibility_export.csv';
        $this->assertFileExists($path);
        $raw = (string) file_get_contents($path);
        $h = fopen($path, 'r');
        $header = fgetcsv($h);
        $rows = [];
        while (($line = fgetcsv($h)) !== false) {
            $this->assertCount(count($header), $line);
            $rows[] = array_combine($header, $line);
        }
        fclose($h);

        return [$header, $rows, $raw];
    }

    public function test_the_file_of_a_real_run_equals_an_independent_read_of_the_tables(): void
    {
        $w = R0025SyntheticV2World::build();
        $service = app(MarketDataEvidenceExportService::class);
        $dir = sys_get_temp_dir().'/md_b19_elig_real_'.uniqid('', true);
        $service->exportRunEvidence($w['run_id'], $dir);
        [$header, $rows, $raw] = $this->readCsv($dir);

        $pub = (array) DB::table('eod_publications')->where('run_id', $w['run_id'])->first();
        $run = (array) DB::table('eod_runs')->where('run_id', $w['run_id'])->first();
        $manifest = json_decode((string) file_get_contents($dir.'/publication_manifest.json'), true);
        $table = DB::table('eod_eligibility')->where('publication_id', $pub['publication_id'])->orderBy('ticker_id')->get()->map(fn ($r) => (array) $r)->all();
        $this->assertNotSame([], $table, 'precondition: the real pipeline wrote eligibility rows');

        // R0133..R0138: the header, then every row against the table
        $this->assertSame(self::HEADER, $header);
        $this->assertCount(count($table), $rows);
        $this->assertSame((int) $run['eligibility_rows_written'], count($rows), 'the file holds exactly the rows the run recorded');
        foreach ($table as $i => $t) {
            $r = $rows[$i];
            $this->assertSame((string) $pub['trade_date'], $r['trade_date'], 'R0133');
            $this->assertSame((string) $t['trade_date'], $r['trade_date']);
            $this->assertNotNull($t['listing_id'], 'precondition: the real row has a stable listing id');
            $this->assertSame((string) $t['listing_id'], $r['listing_id'], 'R0134');
            $this->assertNotSame($r['listing_id'], $r['ticker_id'], 'the listing is not the ticker');
            $this->assertSame((string) $t['ticker_id'], $r['ticker_id'], 'R0135');
            $this->assertSame((string) $pub['publication_id'], $r['publication_id'], 'R0136');
            $this->assertSame(((int) $t['eligible'] === 1) ? '1' : '0', $r['data_usable'], 'R0137');
            $set = json_decode((string) $t['eligibility_reasons_json'], true);
            $this->assertIsArray($set, 'precondition: the real row has a recorded reason set');
            $this->assertSame(json_encode($set, JSON_UNESCAPED_SLASHES), $r['reason_codes'], 'R0138');
            $this->assertSame((string) $t['eligible'], $r['eligible']);
            $this->assertSame((string) ($t['reason_code'] ?? ''), $r['reason_code']);
        }

        // R0143: one coherent publication context
        $this->assertSame([(string) $pub['publication_id']], array_values(array_unique(array_column($rows, 'publication_id'))));
        $this->assertSame((int) $manifest['publication_id'], (int) $pub['publication_id'], 'the manifest of the same export names the same publication');
        $this->assertSame((int) $run['publication_id'], (int) $pub['publication_id']);
        $this->assertSame((string) ($run['trade_date_effective'] ?: $run['trade_date_requested']), $rows[0]['trade_date'], 'the resolved trade date D');

        // R0144: a blocked row carries a non-empty set whose members, and its legacy reason, are registered
        $registered = DB::table('eod_reason_codes')->pluck('code')->all();
        $blocked = array_values(array_filter($rows, fn ($r) => $r['data_usable'] === '0'));
        $this->assertNotSame([], $blocked, 'precondition: the real world has a blocked row');
        foreach ($blocked as $r) {
            $members = json_decode($r['reason_codes'], true);
            $this->assertNotEmpty($members, 'a blocked row has a non-empty reason set');
            foreach ($members as $code) {
                $this->assertContains($code, $registered, $code.' is a registered reason code');
            }
            $this->assertContains($r['reason_code'], $registered);
        }

        // R0145: a superseded neighbour publication of the same date with other rows must not enter the file
        $clone = $pub;
        $clone['publication_id'] = (int) $pub['publication_id'] + 1;
        $clone['publication_version'] = (int) $pub['publication_version'] + 1;
        $clone['is_current'] = 0;
        $clone['seal_state'] = 'UNSEALED';   // a sealed publication's history is immutable: the rows go in while it is a candidate, then it is sealed
        $clone['sealed_at'] = null;
        foreach (['publication_manifest_hash', 'publication_seal_fingerprint', 'seal_fingerprint'] as $unique) {
            if (array_key_exists($unique, $clone)) {
                $clone[$unique] = null;
            }
        }
        $cloneRun = (array) $run;
        $cloneRun['run_id'] = (int) $run['run_id'] + 1000000;
        $cloneRun['publication_id'] = $clone['publication_id'];
        $cloneRun['publication_version'] = $clone['publication_version'];
        $cloneRun['is_current_publication'] = 0;
        DB::table('eod_runs')->insert($cloneRun);
        $clone['run_id'] = $cloneRun['run_id'];
        DB::table('eod_publications')->insert($clone);
        $history = (array) DB::table('eod_eligibility_history')->where('publication_id', $pub['publication_id'])->first();
        $this->assertNotSame([], $history, 'precondition: the real publication has a history row');
        $decoy = $history;
        $decoy['publication_id'] = $clone['publication_id'];
        $decoy['run_id'] = $clone['run_id'];
        $decoy['listing_id'] = (int) $history['listing_id'] + 100000;
        $decoy['eligible'] = 1 - (int) $history['eligible'];
        DB::table('eod_eligibility_history')->insert($decoy);
        DB::table('eod_publications')->where('publication_id', $clone['publication_id'])->update(['seal_state' => 'SEALED', 'sealed_at' => $pub['sealed_at']]);
        $dir2 = sys_get_temp_dir().'/md_b19_elig_real2_'.uniqid('', true);
        $service->exportRunEvidence($w['run_id'], $dir2);
        [, , $raw2] = $this->readCsv($dir2);
        $this->assertSame($raw, $raw2, 'the file is byte-identical with a superseded neighbour in the history');
        $this->assertStringNotContainsString((string) $decoy['listing_id'], $raw2);
    }
}

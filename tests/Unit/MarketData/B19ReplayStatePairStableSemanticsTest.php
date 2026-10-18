<?php

use App\Application\MarketData\Services\MarketDataEvidenceExportService;
use App\Application\MarketData\Services\ReplayVerificationService;
use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use App\Infrastructure\Persistence\MarketData\EodEvidenceRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\ReplayResultRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

require_once __DIR__.'/B19ReplayResultSemanticPipelineTest.php';

/** Only the sixteen state-pair obligations whose authority and paths survive F011 unchanged. */
class B19ReplayStatePairStableSemanticsTest extends TestCase
{
    use UsesMarketDataSqlite;

    private $helper;
    private $exports = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->helper = new B19ReplayResultSemanticPipelineTest();
        $p = new ReflectionProperty($this->helper, 'literals');
        $p->setAccessible(true);
        $p->setValue($this->helper, new B18ReplayComparisonExhaustivenessTest());
    }

    private function borrow(string $method, ...$args)
    {
        $r = new ReflectionMethod($this->helper, $method);
        $r->setAccessible(true);
        return $r->invoke($this->helper, ...$args);
    }

    protected function tearDown(): void
    {
        $p = new ReflectionProperty($this->helper, 'paths');
        $p->setAccessible(true);
        foreach (array_merge($this->exports, $p->getValue($this->helper)) as $path) {
            if (!is_dir($path)) continue;
            $resolved = realpath($path);
            $this->assertSame(0, strpos($resolved, realpath(sys_get_temp_dir()).DIRECTORY_SEPARATOR));
            $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($resolved, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($it as $f) $f->isDir() ? rmdir($f->getPathname()) : unlink($f->getPathname());
            rmdir($resolved);
        }
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    public static function members(): array
    {
        return [
            'R0264 R0281 status' => ['status', 'terminal_status', 'SUCCESS', 'HELD'],
            'R0265 R0282 date' => ['trade_date_effective', 'trade_date_effective', '2026-03-20', '2026-03-19'],
            'R0266 R0283 seal' => ['seal_state', 'seal_state', 'SEALED', 'UNSEALED'],
            'R0275 R0292 version' => ['publication_version', 'publication_version', 4, 9],
            'R0276 R0293 bars' => ['bars_batch_hash', 'bars_batch_hash', 'A1', 'counterfactual-bars'],
            'R0277 R0294 indicators' => ['indicators_batch_hash', 'indicators_batch_hash', 'B1', 'counterfactual-indicators'],
            'R0278 R0295 eligibility' => ['eligibility_batch_hash', 'eligibility_batch_hash', 'C1', 'counterfactual-eligibility'],
            'R0279 R0296 reasons' => ['reason_code_counts', 'expected_reason_code_counts', [['reason_code'=>'ELIG_NOT_ENOUGH_HISTORY','reason_count'=>3]], [['reason_code'=>'ELIG_NOT_ENOUGH_HISTORY','reason_count'=>4]]],
        ];
    }

    private function pipeline(array $overrides = [], bool $supersedeAfterPersistence = false): array
    {
        // The accepted independent literal fixture is authored before the actual world exists.
        $fixture = $this->borrow('fixture', 'v1', $overrides);
        $hashPath = $fixture.'/expected/expected_hashes.json';
        $hashes = json_decode(file_get_contents($hashPath), true);
        foreach (['bars_batch_hash', 'indicators_batch_hash', 'eligibility_batch_hash'] as $name) {
            if (isset($overrides[$name])) $hashes[$name] = $overrides[$name];
        }
        file_put_contents($hashPath, json_encode($hashes));
        $manifestPath = $fixture.'/manifest.json';
        $manifest = json_decode(file_get_contents($manifestPath), true);
        foreach ($manifest['files'] as $name) $manifest['files_sha256'][$name] = hash_file('sha256', $fixture.'/'.$name);
        file_put_contents($manifestPath, json_encode($manifest));
        $before = hash_file('sha256', $hashPath);
        $this->borrow('world');
        $metric = (new ReplayVerificationService(new EodEvidenceRepository(), new EodPublicationRepository(), new ReplayResultRepository()))->verifyRunAgainstFixture(91, $fixture, 6192, 44);
        $row = DB::table('md_replay_daily_metrics')->where('replay_id',6192)->where('trade_date','2026-03-20')->first();
        $sourceRun = (array) DB::table('eod_runs')->where('run_id',91)->first();
        $sourcePublication = (array) DB::table('eod_publications')->where('publication_id',44)->first();
        if ($supersedeAfterPersistence) {
            // The selected old metric stays authoritative after the live pointer and run change.
            DB::table('eod_current_publication_pointer')->where('publication_id',44)->delete();
            DB::table('eod_publications')->where('publication_id',44)->update(['is_current'=>0]);
            DB::table('eod_runs')->where('run_id',91)->update(['bars_batch_hash'=>'later-live-bars','indicators_batch_hash'=>'later-live-indicators','eligibility_batch_hash'=>'later-live-eligibility']);
        }
        $dir = sys_get_temp_dir().'/b19_stable_pair_'.uniqid();
        $this->exports[] = $dir;
        (new MarketDataEvidenceExportService(new EodEvidenceRepository(),new EodPublicationRepository(),new EodCorrectionRepository()))->exportReplayEvidence(6192,'2026-03-20',$dir);
        $json = [];
        foreach (['replay_result','replay_expected_state','replay_actual_state'] as $name) $json[$name] = json_decode(file_get_contents($dir.'/'.$name.'.json'),true);
        $this->assertSame($before,hash_file('sha256',$hashPath));
        if (getenv('B19_STATE_PAIR_ARTIFACT_DIR')) {
            $capture = getenv('B19_STATE_PAIR_ARTIFACT_DIR').'/'.preg_replace('/[^a-zA-Z0-9_-]/','_',$this->getName());
            if (!is_dir($capture)) mkdir($capture,0775,true);
            foreach (glob($dir.'/*.json') as $file) copy($file,$capture.'/'.basename($file));
            foreach (glob($fixture.'/expected/*.json') as $file) copy($file,$capture.'/fixture_'.basename($file));
            copy($manifestPath,$capture.'/fixture_manifest.json');
            file_put_contents($capture.'/persisted_metric.json',json_encode($row,JSON_PRETTY_PRINT));
            file_put_contents($capture.'/source_rows.json',json_encode(['run'=>$sourceRun,'publication'=>$sourcePublication],JSON_PRETTY_PRINT));
        }
        return [$metric,$row,$json,$sourceRun,$sourcePublication];
    }

    /** @dataProvider members */
    public function test_each_state_member_preserves_its_independent_source(string $field, string $input, $normal, $counterfactual): void
    {
        [$metric,$row,$json,$run,$publication] = $this->pipeline();
        $this->assertSame('BLOCKED',$metric['replay_status'],'D006 requires a verified publication envelope; legacy probe keeps scalar evidence but cannot claim identity PASS');
        foreach (['replay_expected_state','replay_actual_state'] as $artifact) $this->assertSame($normal,$json[$artifact][$field],$artifact.'.'.$field);
        if ($field === 'reason_code_counts') {
            $this->assertSame(3,DB::table('eod_eligibility')->where('publication_id',44)->where('reason_code','ELIG_NOT_ENOUGH_HISTORY')->count());
        } else {
            $column = $field === 'status' ? 'terminal_status' : $field;
            $source = in_array($field,['seal_state','publication_version'],true) ? $publication[$field] : $run[$column];
            $this->assertSame($normal,$field === 'publication_version' ? (int) $source : $source,'independently authored actual source row');
            $this->assertSame($normal,$field === 'publication_version' ? (int) $row->{$field} : $row->{$field},'actual persisted metric');
            $this->assertSame($normal,$field === 'publication_version' ? (int) $row->{'expected_'.$field} : $row->{'expected_'.$field},'independent expected metric');
        }
    }

    /** @dataProvider members */
    public function test_counterfactual_expected_members_cannot_replace_actual(string $field, string $input, $normal, $counterfactual): void
    {
        [$metric,$row,$json] = $this->pipeline([$input=>$counterfactual]);
        $this->assertSame('BLOCKED',$metric['replay_status']);
        $this->assertSame('NOT_ADMISSIBLE',$metric['comparison_result']);
        $this->assertSame($counterfactual,$json['replay_expected_state'][$field],'independent expected side');
        $this->assertSame($normal,$json['replay_actual_state'][$field],'actual side must not inherit the fixture expectation');
        if ($field !== 'reason_code_counts') {
            $this->assertSame($normal,$json['replay_result'][$field]);
            $this->assertSame($counterfactual,$json['replay_result']['expected_'.$field]);
        }
        $this->assertContains($field === 'status' ? 'terminal_status' : $field,array_column($metric['mismatches'],'field'));
    }

    public function test_export_keeps_the_persisted_state_after_publication_supersession(): void
    {
        [,$row,$json] = $this->pipeline([],true);
        foreach (['replay_expected_state','replay_actual_state'] as $artifact) {
            $this->assertSame(4,$json[$artifact]['publication_version']);
            $this->assertSame('A1',$json[$artifact]['bars_batch_hash']);
            $this->assertSame('B1',$json[$artifact]['indicators_batch_hash']);
            $this->assertSame('C1',$json[$artifact]['eligibility_batch_hash']);
        }
        $this->assertSame('A1',$row->bars_batch_hash);
        $this->assertSame('later-live-bars',DB::table('eod_runs')->where('run_id',91)->value('bars_batch_hash'));
    }
}

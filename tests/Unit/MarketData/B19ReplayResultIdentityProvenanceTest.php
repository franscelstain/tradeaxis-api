<?php

use App\Application\MarketData\Services\MarketDataEvidenceExportService;
use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use App\Infrastructure\Persistence\MarketData\EodEvidenceRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/** Small B19 pilot: independent persisted identities, not expectations copied from the export. */
class B19ReplayResultIdentityProvenanceTest extends TestCase
{
    use UsesMarketDataSqlite;

    private $outputDirs = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
        foreach ([
            [4101, '2026-04-06', '2026-04-02', 'COMPLETED'],
            [4101, '2026-04-07', '2026-04-03', 'HELD'],
            [4102, '2026-04-06', '2026-04-01', 'BLOCKED'],
        ] as $row) {
            DB::table('md_replay_daily_metrics')->insert([
                'replay_id' => $row[0], 'trade_date' => $row[1],
                'trade_date_effective' => $row[2], 'status' => $row[3],
                'source' => 'fixture', 'comparison_result' => 'MATCH',
                'seal_state' => 'SEALED', 'created_at' => '2026-04-08 09:00:00',
            ]);
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->outputDirs as $dir) {
            foreach (glob($dir.'/*') ?: [] as $file) {
                unlink($file);
            }
            rmdir($dir);
        }
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    private function exportRow(int $replayId, string $tradeDate): array
    {
        $dir = sys_get_temp_dir().'/b19_replay_identity_'.uniqid('', true);
        $this->outputDirs[] = $dir;
        $service = new MarketDataEvidenceExportService(
            new EodEvidenceRepository(), new EodPublicationRepository(), new EodCorrectionRepository()
        );
        $service->exportReplayEvidence($replayId, $tradeDate, $dir);

        return json_decode(file_get_contents($dir.'/replay_result.json'), true);
    }

    public function test_replay_result_identity_fields_follow_the_exact_persisted_pair(): void
    {
        $before = DB::table('md_replay_daily_metrics')->orderBy('replay_id')->orderBy('trade_date')->get()->toArray();
        $cases = [
            [4101, '2026-04-06', '2026-04-02', 'COMPLETED'],
            [4101, '2026-04-07', '2026-04-03', 'HELD'],
            [4102, '2026-04-06', '2026-04-01', 'BLOCKED'],
        ];
        foreach ($cases as $case) {
            $actual = $this->exportRow($case[0], $case[1]);
            foreach (['replay_id' => $case[0], 'trade_date' => $case[1],
                'trade_date_effective' => $case[2], 'status' => $case[3]] as $field => $expected) {
                $this->assertArrayHasKey($field, $actual);
                $this->assertSame($expected, $actual[$field], $field.' must come from its own row');
            }
        }
        $this->assertEquals($before, DB::table('md_replay_daily_metrics')->orderBy('replay_id')->orderBy('trade_date')->get()->toArray());
    }

    public function test_replay_result_identity_never_falls_back_to_another_pair(): void
    {
        $service = new MarketDataEvidenceExportService(
            new EodEvidenceRepository(), new EodPublicationRepository(), new EodCorrectionRepository()
        );
        foreach ([[4101, null], [4102, '2026-04-07'], [4103, '2026-04-06']] as $missing) {
            try {
                $service->exportReplayEvidence($missing[0], $missing[1]);
                $this->fail('missing replay/date pair was exported');
            } catch (\RuntimeException $e) {
                $this->assertContains($e->getMessage(), [
                    'Replay evidence export requires explicit trade_date; latest-row resolution is not allowed on consumer read path.',
                    'Replay result not found for evidence export.',
                ]);
            }
        }
    }
}

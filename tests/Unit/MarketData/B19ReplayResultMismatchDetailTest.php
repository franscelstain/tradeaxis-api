<?php

use App\Application\MarketData\Services\MarketDataEvidenceExportService;
use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use App\Infrastructure\Persistence\MarketData\EodEvidenceRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/** A summary is a caption on persisted mismatch detail, never a replacement for it. */
class B19ReplayResultMismatchDetailTest extends TestCase
{
    use UsesMarketDataSqlite;

    private $dir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
        DB::table('md_replay_daily_metrics')->insert([
            'replay_id' => 5301, 'trade_date' => '2026-04-09', 'source' => 'fixture',
            'status' => 'COMPLETED', 'comparison_result' => 'MISMATCH', 'replay_status' => 'FAIL',
            'seal_state' => 'SEALED', 'mismatch_summary' => 'two independent fields diverged',
            'mismatch_count' => 2,
            'mismatch_reason_codes_json' => json_encode(['REPLAY_MISMATCH']),
            'mismatches_json' => json_encode([
                ['field' => 'bars_batch_hash', 'expected' => 'bars-old', 'actual' => 'bars-new', 'reason_code' => 'REPLAY_MISMATCH'],
                ['field' => 'eligibility_batch_hash', 'expected' => 'elig-old', 'actual' => 'elig-new', 'reason_code' => 'REPLAY_MISMATCH'],
            ]),
            'created_at' => '2026-04-09 17:00:00',
        ]);
    }

    protected function tearDown(): void
    {
        if ($this->dir !== null) {
            foreach (glob($this->dir.'/*') ?: [] as $file) {
                unlink($file);
            }
            rmdir($this->dir);
        }
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    private function export(): array
    {
        $this->dir = sys_get_temp_dir().'/b19_replay_details_'.uniqid('', true);
        $service = new MarketDataEvidenceExportService(
            new EodEvidenceRepository(), new EodPublicationRepository(), new EodCorrectionRepository()
        );
        $service->exportReplayEvidence(5301, '2026-04-09', $this->dir);

        return json_decode(file_get_contents($this->dir.'/replay_result.json'), true);
    }

    public function test_summary_and_both_persisted_mismatches_survive_the_real_export(): void
    {
        $stored = DB::table('md_replay_daily_metrics')->where('replay_id', 5301)->where('trade_date', '2026-04-09')->first();
        $expectedDetails = json_decode($stored->mismatches_json, true);
        $result = $this->export();
        $this->assertSame('two independent fields diverged', $result['mismatch_summary']);
        $this->assertSame(2, $result['mismatch_count']);
        $this->assertSame($expectedDetails, $result['mismatches']);
        $this->assertSame(['bars_batch_hash', 'eligibility_batch_hash'], array_column($result['mismatches'], 'field'));
        $this->assertNotSame($result['mismatch_summary'], json_encode($result['mismatches']));
        $pack = json_decode(file_get_contents($this->dir.'/replay_evidence_pack.json'), true);
        $this->assertSame($expectedDetails, $pack['replay_result']['mismatches']);
        $this->assertSame($stored->mismatches_json, DB::table('md_replay_daily_metrics')->where('replay_id', 5301)->value('mismatches_json'));
    }

    public function test_detail_has_two_distinct_fields_not_one_repeated_summary(): void
    {
        $result = $this->export();
        $this->assertCount(2, $result['mismatches']);
        $this->assertNotSame($result['mismatches'][0]['field'], $result['mismatches'][1]['field']);
        $this->assertSame('bars-old', $result['mismatches'][0]['expected']);
        $this->assertSame('elig-new', $result['mismatches'][1]['actual']);
    }
}

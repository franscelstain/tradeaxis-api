<?php

use App\Application\MarketData\Services\MarketDataEvidenceExportService;
use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use App\Infrastructure\Persistence\MarketData\EodEvidenceRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/** B19 section 8: each asserted value originates in a distinct persisted replay metric row. */
class B19ReplayResultPersistedFieldProvenanceTest extends TestCase
{
    use UsesMarketDataSqlite;

    private $outputDirs = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
        foreach (self::rows() as $row) {
            DB::table('md_replay_daily_metrics')->insert($row);
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

    private static function rows(): array
    {
        $common = [
            'source' => 'fixture', 'created_at' => '2026-04-09 09:00:00',
        ];

        return [
            array_merge($common, [
                'replay_id' => 5201, 'trade_date' => '2026-04-07', 'trade_date_effective' => '2026-04-05',
                'status' => 'COMPLETED', 'comparison_result' => 'MISMATCH', 'replay_status' => 'FAIL',
                'comparison_note' => 'eligibility diverged on fixture A', 'artifact_changed_scope' => 'eligibility_only',
                'replay_mode' => 'PUBLICATION_EXACT', 'publication_version' => 7,
                'bars_batch_hash' => 'bars-A', 'indicators_batch_hash' => 'indicators-A',
                'eligibility_batch_hash' => 'eligibility-A', 'seal_state' => 'SEALED',
                'expected_status' => 'SUCCESS', 'expected_trade_date_effective' => '2026-04-04',
                'expected_seal_state' => 'UNSEALED', 'mismatch_summary' => 'summary A',
            ]),
            array_merge($common, [
                'replay_id' => 5201, 'trade_date' => '2026-04-08', 'trade_date_effective' => '2026-04-06',
                'status' => 'HELD', 'comparison_result' => 'EXPECTED_DEGRADE', 'replay_status' => 'PASS',
                'comparison_note' => 'coverage degraded on fixture B', 'artifact_changed_scope' => 'bars_only',
                'replay_mode' => 'AS_KNOWN', 'publication_version' => 8,
                'bars_batch_hash' => 'bars-B', 'indicators_batch_hash' => 'indicators-B',
                'eligibility_batch_hash' => 'eligibility-B', 'seal_state' => 'UNSEALED',
                'expected_status' => 'HELD', 'expected_trade_date_effective' => '2026-04-03',
                'expected_seal_state' => 'SEALED', 'mismatch_summary' => null,
            ]),
            array_merge($common, [
                'replay_id' => 5202, 'trade_date' => '2026-04-07', 'trade_date_effective' => '2026-04-01',
                // The production comparison_result enum calls prerequisite refusal NOT_ADMISSIBLE.
                'status' => 'BLOCKED', 'comparison_result' => 'NOT_ADMISSIBLE', 'replay_status' => 'BLOCKED',
                'comparison_note' => null, 'artifact_changed_scope' => null,
                'replay_mode' => 'PUBLICATION_EXACT', 'publication_version' => null,
                'bars_batch_hash' => null, 'indicators_batch_hash' => null,
                'eligibility_batch_hash' => null, 'seal_state' => 'UNSEALED',
                'expected_status' => null, 'expected_trade_date_effective' => null,
                'expected_seal_state' => null, 'mismatch_summary' => null,
            ]),
        ];
    }

    private function export(int $id, string $date): array
    {
        $dir = sys_get_temp_dir().'/b19_replay_fields_'.uniqid('', true);
        $this->outputDirs[] = $dir;
        $service = new MarketDataEvidenceExportService(
            new EodEvidenceRepository(), new EodPublicationRepository(), new EodCorrectionRepository()
        );
        $service->exportReplayEvidence($id, $date, $dir);

        return json_decode(file_get_contents($dir.'/replay_result.json'), true);
    }

    public function test_replay_result_fields_equal_their_own_independently_seeded_metric_values(): void
    {
        $before = DB::table('md_replay_daily_metrics')->orderBy('replay_id')->orderBy('trade_date')->get()->toArray();
        $fields = [
            'status', 'comparison_result', 'replay_status', 'comparison_note', 'artifact_changed_scope', 'replay_mode',
            'publication_version', 'bars_batch_hash', 'indicators_batch_hash', 'eligibility_batch_hash',
            'seal_state', 'expected_status', 'expected_trade_date_effective', 'expected_seal_state',
            'mismatch_summary',
        ];
        foreach (self::rows() as $source) {
            $json = $this->export($source['replay_id'], $source['trade_date']);
            foreach ($fields as $field) {
                $this->assertArrayHasKey($field, $json);
                $this->assertSame($source[$field], $json[$field], $field.' must use the selected stored row');
            }
        }
        $this->assertEquals($before, DB::table('md_replay_daily_metrics')->orderBy('replay_id')->orderBy('trade_date')->get()->toArray());
    }

    public function test_expected_fields_do_not_inherit_actual_values_or_a_neighbour(): void
    {
        $a = $this->export(5201, '2026-04-07');
        $b = $this->export(5201, '2026-04-08');
        $decoy = $this->export(5202, '2026-04-07');
        foreach (['expected_status' => 'status', 'expected_trade_date_effective' => 'trade_date_effective',
            'expected_seal_state' => 'seal_state'] as $expected => $actual) {
            $this->assertNotSame($a[$expected], $a[$actual], $expected.' must not come from actual state');
        }
        foreach (['status', 'comparison_result', 'replay_status', 'comparison_note', 'artifact_changed_scope', 'replay_mode',
            'publication_version', 'bars_batch_hash', 'indicators_batch_hash', 'eligibility_batch_hash',
            'seal_state', 'expected_status', 'expected_trade_date_effective', 'expected_seal_state',
            'mismatch_summary'] as $field) {
            $this->assertNotSame($a[$field], $b[$field], $field.' must not inherit an adjacent date');
            if (in_array($field, ['comparison_note', 'artifact_changed_scope', 'publication_version',
                'bars_batch_hash', 'indicators_batch_hash', 'eligibility_batch_hash', 'expected_status',
                'expected_trade_date_effective', 'expected_seal_state', 'mismatch_summary'], true)) {
                $this->assertNull($decoy[$field], $field.' missing source must stay null');
            }
        }
    }
}

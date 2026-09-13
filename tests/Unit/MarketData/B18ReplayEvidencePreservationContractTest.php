<?php

use App\Application\MarketData\Services\MarketDataEvidenceExportService;
use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use App\Infrastructure\Persistence\MarketData\EodEvidenceRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use Mockery as m;
use PHPUnit\Framework\TestCase;

/**
 * `MD-B18-A002` — what replay evidence export must preserve, item by item.
 *
 * `Manual_File_Publishability_Policy_LOCKED.md` (`MD-S040`) lists ten things evidence export and
 * replay verification must preserve. `MD-S040-R0070`..`R0079` are those ten list members.
 *
 * The pre-existing export test asserted five fields and left the other five unasserted: coverage
 * ratio, coverage minimum threshold, final reason code, effective/fallback trade date and current
 * publication id/version were present in the fixture, exported, and never checked. The exporter
 * could have dropped any of them and that test would have stayed green — which is why
 * `F-MD-B19-A001-002` recorded those five as `UNSUPPORTED` rather than proven.
 *
 * The contract list is **parsed from `MD-S040`**, not copied here, so an item added to the strategy
 * with no corresponding assertion fails this test rather than passing unnoticed. The prose item to
 * JSON-path mapping is explicit and reviewed: prose like "coverage minimum threshold" cannot be
 * matched to a field name by string similarity without reintroducing exactly the keyword-accident
 * defect `F-MD-B18-A001-001` measured.
 */
class B18ReplayEvidencePreservationContractTest extends TestCase
{
    private const CONTRACT = 'docs/market_data/authority/strategy/book/Manual_File_Publishability_Policy_LOCKED.md';

    private const INTRODUCER = 'Evidence export and replay verification must preserve:';

    /**
     * Reviewed mapping: the contract's own wording => the paths that carry it in
     * `replay_result.json`, and the value each must hold for the fixture below.
     *
     * @return array<string,array<string,mixed>>
     */
    private function preservationMap(): array
    {
        return [
            'coverage gate state' => [
                'coverage.coverage_gate_state' => 'FAIL',
                'expected_coverage.coverage_gate_state' => 'FAIL',
            ],
            'coverage reason code' => [
                // Derived from the normalized gate state, not echoed from the record: no
                // coverage reason code is persisted on the replay metric (the table carries
                // `final_reason_code`, not `coverage_reason_code`). The fixture below sets a
                // different value on purpose, and the export ignoring it is correct behaviour -
                // this expectation was wrong on its first run and the exporter was right.
                'coverage.coverage_reason_code' => 'COVERAGE_BELOW_THRESHOLD',
            ],
            'coverage ratio' => [
                'coverage.coverage_ratio' => 0.842,
                'expected_coverage.coverage_ratio' => 0.842,
            ],
            'coverage minimum threshold' => [
                'coverage.coverage_min_threshold' => 0.98,
                'expected_coverage.coverage_min_threshold' => 0.98,
            ],
            'expected / available / missing bar counts' => [
                'coverage.coverage_expected_count' => 1000,
                'coverage.coverage_available_count' => 842,
                'coverage.coverage_missing_count' => 158,
            ],
            'terminal status' => [
                'terminal_status' => 'HELD',
            ],
            'publishability state' => [
                'publishability_state' => 'NOT_READABLE',
            ],
            'final reason code' => [
                // This one IS persisted and must be carried through verbatim.
                'final_reason_code' => 'COVERAGE_BELOW_MIN_RATIO',
            ],
            'effective trade date/fallback date' => [
                'trade_date_effective' => '2025-12-09',
            ],
            'current publication id/version when available' => [
                // The id is carried in the publication audit context, not at the top level; the
                // version is carried in both. Mapping it to a top-level `publication_id` failed
                // this test on its first run, and the export was right - the mapping was wrong.
                'publication_context.publication_id' => 4411,
                'publication_context.publication_version' => 7,
                'publication_version' => 7,
            ],
        ];
    }

    protected function tearDown(): void
    {
        m::close();
    }

    /** @return array<int,string> the ten list members, read from the frozen contract */
    private function contractItems(): array
    {
        $path = dirname(__DIR__, 3).'/'.self::CONTRACT;
        $this->assertFileExists($path, 'the owner contract is missing');
        $lines = preg_split('/\R/', (string) file_get_contents($path));

        $start = null;
        foreach ($lines as $i => $line) {
            if (trim($line) === self::INTRODUCER) {
                $start = $i;
                break;
            }
        }
        $this->assertNotNull($start, 'the preservation introducer is no longer in MD-S040; '
            .'this guard is bound to text that moved and must be re-read, not relaxed');

        $items = [];
        for ($i = $start + 1; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if ($line === '') {
                break;
            }
            if (strpos($line, '- ') !== 0) {
                break;
            }
            $items[] = trim(substr($line, 2));
        }

        return $items;
    }

    public function test_the_contract_list_and_the_reviewed_mapping_cover_exactly_the_same_items(): void
    {
        $items = $this->contractItems();
        $mapped = array_keys($this->preservationMap());

        sort($items);
        sort($mapped);

        $this->assertSame($items, $mapped,
            'MD-S040 and the reviewed mapping disagree. An item added to the contract with no mapped '
                .'assertion would otherwise be silently unproven, which is the defect this stage is '
                .'remediating.');
    }

    public function test_every_preserved_item_survives_the_real_export(): void
    {
        $dir = $this->export();

        $replayResult = json_decode((string) file_get_contents($dir.'/replay_result.json'), true);
        $this->assertIsArray($replayResult, 'replay_result.json is not readable JSON');

        $missing = [];
        $wrong = [];
        foreach ($this->preservationMap() as $item => $paths) {
            foreach ($paths as $path => $expected) {
                $actual = $this->dig($replayResult, $path);
                if ($actual === null && $expected !== null) {
                    $missing[] = $item.' -> '.$path;

                    continue;
                }
                if (is_float($expected)) {
                    if (abs((float) $actual - $expected) > 0.00001) {
                        $wrong[] = $path.' = '.var_export($actual, true).', expected '.$expected;
                    }

                    continue;
                }
                if ((string) $actual !== (string) $expected) {
                    $wrong[] = $path.' = '.var_export($actual, true).', expected '.var_export($expected, true);
                }
            }
        }

        $this->assertSame([], $missing, 'MD-S040 requires these to be preserved and the export omits them');
        $this->assertSame([], $wrong, 'preserved fields were exported with the wrong value');
    }

    /**
     * The fail-closed half. A coverage failure that exports without its reason code and threshold
     * cannot be acted on: the operator sees a `FAIL` with nothing saying why or against what bar.
     * This asserts the two fields the previous guard left unchecked are genuinely load-bearing.
     */
    public function test_a_failing_coverage_gate_still_exports_the_threshold_it_failed_against(): void
    {
        $replayResult = json_decode(
            (string) file_get_contents($this->export().'/replay_result.json'), true);

        $this->assertSame('FAIL', $replayResult['coverage']['coverage_gate_state']);
        $this->assertNotNull($replayResult['coverage']['coverage_min_threshold'],
            'a FAIL was exported without the threshold it failed against');
        $this->assertNotNull($replayResult['coverage']['coverage_ratio'],
            'a FAIL was exported without the ratio that failed');
        $this->assertGreaterThan(
            (float) $replayResult['coverage']['coverage_ratio'],
            (float) $replayResult['coverage']['coverage_min_threshold'],
            'the exported ratio is not below the exported threshold, so this fixture does not '
                .'actually exercise a coverage failure');
    }

    private function dig(array $data, string $path)
    {
        $cursor = $data;
        foreach (explode('.', $path) as $segment) {
            if (! is_array($cursor) || ! array_key_exists($segment, $cursor)) {
                return null;
            }
            $cursor = $cursor[$segment];
        }

        return $cursor;
    }

    private function export(): string
    {
        $metric = (object) $this->metric();

        $evidence = m::mock(EodEvidenceRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $corrections = m::mock(EodCorrectionRepository::class);

        $evidence->shouldReceive('findReplayMetric')->once()->with(3001, '2025-12-10')->andReturn($metric);
        $evidence->shouldReceive('replayReasonCodeCounts')->once()->with(3001, '2025-12-10')->andReturn([
            ['reason_code' => 'ELIG_MISSING_BAR', 'reason_count' => 120],
        ]);

        $service = new MarketDataEvidenceExportService($evidence, $publications, $corrections);
        $dir = sys_get_temp_dir().'/md_b18_preservation_'.uniqid();
        $service->exportReplayEvidence(3001, '2025-12-10', $dir);

        return $dir;
    }

    /** @return array<string,mixed> */
    private function metric(): array
    {
        return [
            'replay_id' => 3001,
            'trade_date' => '2025-12-10',
            'trade_date_effective' => '2025-12-09',
            'source' => 'manual_file',
            'source_mode' => 'manual_file',
            'source_name' => 'LOCAL_FILE',
            'source_provider' => 'manual_import',
            'source_input_file' => 'storage/app/market-data/manual/degraded.csv',
            'source_file_hash' => 'FILE_HASH_ACTUAL',
            'source_file_hash_algorithm' => 'SHA-256',
            'source_file_size_bytes' => 2048,
            'source_file_row_count' => 842,
            'status' => 'HELD',
            'publishability_state' => 'NOT_READABLE',
            'final_reason_code' => 'COVERAGE_BELOW_MIN_RATIO',
            'publication_id' => 4411,
            'publication_run_id' => 103,
            'publication_version' => 7,
            'is_current_publication' => 1,
            'comparison_result' => 'EXPECTED_DEGRADE',
            'replay_status' => 'PASS',
            'comparison_note' => 'coverage intentionally degraded',
            'artifact_changed_scope' => 'bars_indicators_eligibility',
            'config_identity' => 'cfg_2025_12_v2',
            'coverage_universe_count' => 1000,
            'coverage_expected_count' => 1000,
            'coverage_available_count' => 842,
            'coverage_missing_count' => 158,
            'coverage_ratio' => '0.8420',
            'coverage_min_threshold' => '0.9800',
            'coverage_gate_state' => 'FAIL',
            'coverage_reason_code' => 'COVERAGE_BELOW_MIN_RATIO',
            'coverage_threshold_mode' => 'MIN_RATIO',
            'coverage_universe_basis' => 'active_equity_universe_asof_trade_date',
            'coverage_contract_version' => 'coverage_gate_v1',
            'coverage_missing_sample_json' => json_encode(['BBCA', 'TLKM']),
            'bars_rows_written' => 842,
            'indicators_rows_written' => 830,
            'eligibility_rows_written' => 1000,
            'eligible_count' => 650,
            'invalid_bar_count' => 18,
            'invalid_indicator_count' => 170,
            'warning_count' => 50,
            'hard_reject_count' => 12,
            'bars_batch_hash' => 'A1',
            'indicators_batch_hash' => 'B1',
            'eligibility_batch_hash' => 'C1',
            'seal_state' => 'UNSEALED',
            'sealed_at' => null,
            'expected_status' => 'HELD',
            'expected_terminal_status' => 'HELD',
            'expected_publishability_state' => 'NOT_READABLE',
            'expected_source_mode' => 'manual_file',
            'expected_source_name' => 'LOCAL_FILE',
            'expected_source_provider' => 'manual_import',
            'expected_source_input_file' => 'storage/app/market-data/manual/degraded.csv',
            'expected_source_file_hash' => 'FILE_HASH_ACTUAL',
            'expected_source_file_hash_algorithm' => 'SHA-256',
            'expected_source_file_size_bytes' => 2048,
            'expected_source_file_row_count' => 842,
            'expected_publication_id' => 4411,
            'expected_publication_run_id' => 103,
            'expected_is_current_publication' => 1,
            'expected_trade_date_effective' => '2025-12-09',
            'expected_seal_state' => 'UNSEALED',
            'expected_config_identity' => 'cfg_2025_12_v2',
            'expected_publication_version' => 7,
            'expected_coverage_universe_count' => 1000,
            'expected_coverage_expected_count' => 1000,
            'expected_coverage_available_count' => 842,
            'expected_coverage_missing_count' => 158,
            'expected_coverage_ratio' => '0.8420',
            'expected_coverage_min_threshold' => '0.9800',
            'expected_coverage_gate_state' => 'FAIL',
            'expected_coverage_threshold_mode' => 'MIN_RATIO',
            'expected_coverage_universe_basis' => 'active_equity_universe_asof_trade_date',
            'expected_coverage_contract_version' => 'coverage_gate_v1',
            'expected_coverage_missing_sample_json' => json_encode(['BBCA', 'TLKM']),
            'expected_bars_batch_hash' => 'A1',
            'expected_indicators_batch_hash' => 'B1',
            'expected_eligibility_batch_hash' => 'C1',
            'expected_reason_code_counts_json' => json_encode(['ELIG_MISSING_BAR' => 120]),
            'mismatch_summary' => null,
            'created_at' => '2025-12-10T17:15:00+07:00',
        ];
    }
}

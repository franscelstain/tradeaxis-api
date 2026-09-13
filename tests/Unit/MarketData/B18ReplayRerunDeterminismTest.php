<?php

use App\Application\MarketData\Services\MarketDataEvidenceExportService;
use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use App\Infrastructure\Persistence\MarketData\EodEvidenceRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use Mockery as m;
use PHPUnit\Framework\TestCase;

/**
 * `MD-B18-A002` — an unchanged rerun is byte-identical and creates no correction.
 *
 * Three predicates say the same thing from three contracts, and none had an executing guard:
 *
 *   - `MD-S019-R0073` — the consequent of the bound-input conditional: "then replay must reproduce
 *     identical outputs and identical hashes";
 *   - `MD-S003-R0004` — "prove an unchanged rerun is byte-identical and does not create a fake
 *     correction";
 *   - `MD-S005-R0095` — "Fixtures must prove: exact publication replay reproduces hashes".
 *
 * `F-MD-B19-A001-002` recorded all three as `UNSUPPORTED`. The antecedents of `MD-S019` are proven
 * by `B18ReplayBoundInputIdentityContractTest` — that the identities are bound. This is the other
 * half: that binding them identically produces the same bytes.
 *
 * Byte-identity is the assertion, not field equality. A serializer that reorders keys, formats a
 * float differently, or stamps a wall-clock time produces equal fields and different bytes, and it
 * is the bytes that get hashed into publication identity.
 */
class B18ReplayRerunDeterminismTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    /**
     * `MD-S019-R0073`, `MD-S003-R0004`, `MD-S005-R0095` — identical bound inputs, identical bytes.
     */
    public function test_an_unchanged_rerun_produces_byte_identical_artifacts(): void
    {
        $first = $this->export($this->metric());
        $second = $this->export($this->metric());

        $firstFiles = $this->filesIn($first);
        $secondFiles = $this->filesIn($second);

        $this->assertSame(array_keys($firstFiles), array_keys($secondFiles),
            'the two runs did not even produce the same set of artifacts');
        $this->assertGreaterThanOrEqual(4, count($firstFiles),
            'too few artifacts were produced for this to be a meaningful determinism check');

        $differing = [];
        foreach ($firstFiles as $name => $hash) {
            if ($secondFiles[$name] !== $hash) {
                $differing[] = $name;
            }
        }

        $this->assertSame([], $differing,
            'an unchanged rerun produced different bytes for these artifacts, so replay output is '
                .'not reproducible and any hash taken over it is not a stable identity');
    }

    /**
     * The other direction. Without this, the assertion above is satisfied by an exporter that emits
     * the same constant bytes regardless of input — which would be perfectly reproducible and
     * completely useless.
     */
    public function test_a_changed_bound_input_changes_the_bytes(): void
    {
        $baseline = $this->filesIn($this->export($this->metric()));
        $changed = $this->filesIn($this->export($this->metric([
            'config_snapshot_hash' => 'CFGHASH_DIFFERENT',
            'bars_batch_hash' => 'BARS_DIFFERENT',
        ])));

        $this->assertSame(array_keys($baseline), array_keys($changed));

        // Naming the artifact matters. Asserting only that *something* moved is satisfied by any
        // other artifact that happens to carry the value, and a probe that replaced the changed
        // field with a constant in `replay_result.json` left that looser assertion green.
        $this->assertNotSame($baseline['replay_result.json'], $changed['replay_result.json'],
            'changing the config snapshot hash and the bars batch hash did not change '
                .'replay_result.json, so the artifact that carries the bound-input identity is not '
                .'a function of it and the determinism assertion above proves nothing');

        $moved = 0;
        foreach ($baseline as $name => $hash) {
            if ($changed[$name] !== $hash) {
                $moved++;
            }
        }
        $this->assertGreaterThan(0, $moved);
    }

    /**
     * `MD-S003-R0004`, second half — an unchanged rerun "does not create a fake correction".
     *
     * The publication and correction repositories are passed as mocks with no expectations, so any
     * call to either fails the test. A replay that wrote a publication or opened a correction would
     * be indistinguishable, downstream, from a real correction of the trade date.
     */
    public function test_a_rerun_writes_no_publication_and_opens_no_correction(): void
    {
        $evidence = m::mock(EodEvidenceRepository::class);
        $evidence->shouldReceive('findReplayMetric')->once()->andReturn((object) $this->metric());
        $evidence->shouldReceive('replayReasonCodeCounts')->once()->andReturn([]);

        // No shouldReceive: Mockery fails on any call to these.
        $publications = m::mock(EodPublicationRepository::class);
        $corrections = m::mock(EodCorrectionRepository::class);

        $service = new MarketDataEvidenceExportService($evidence, $publications, $corrections);
        $dir = sys_get_temp_dir().'/md_b18_rerun_nocorrection_'.uniqid();
        $service->exportReplayEvidence(5501, '2026-02-11', $dir);

        $this->assertFileExists($dir.'/replay_result.json',
            'the export did not run, so proving it wrote nothing proves nothing');
        $this->addToAssertionCount(1);
    }


    /**
     * `MD-S019-R0009` -- Invariant 1 holds across reruns and replay.
     *
     * Invariant 1 is a conjunction: identical semantic content and bindings imply `bars_batch_hash`,
     * `indicators_batch_hash` **and** `eligibility_batch_hash` are each identical. The rerun guard
     * above proves the "identical" direction for the whole artifact set at once. This proves the
     * three are individually load-bearing, which is what makes the conjunction a claim rather than a
     * list: an exporter that carried only the bars hash would satisfy a whole-artifact comparison
     * while losing two thirds of the invariant.
     *
     * @dataProvider batchHashes
     */
    public function test_each_batch_hash_is_individually_load_bearing_across_a_rerun(string $field): void
    {
        $baseline = $this->filesIn($this->export($this->metric()));
        $changed = $this->filesIn($this->export($this->metric([$field => 'MOVED_'.$field])));

        $this->assertSame(array_keys($baseline), array_keys($changed));
        $this->assertNotSame($baseline['replay_result.json'], $changed['replay_result.json'],
            'changing '.$field.' did not change replay_result.json, so that hash is not carried '
                .'into the replay artifact and Invariant 1 is unenforced for it');
    }

    /** @return array<string,array{0:string}> the three hashes Invariant 1 names */
    public function batchHashes(): array
    {
        return [
            'bars' => ['bars_batch_hash'],
            'indicators' => ['indicators_batch_hash'],
            'eligibility' => ['eligibility_batch_hash'],
        ];
    }

    /**
     * The table must name the three hashes the invariant names, read from the contract rather than
     * transcribed, so a fourth added to Invariant 1 fails here instead of going unchecked.
     */
    public function test_the_batch_hash_table_names_exactly_what_invariant_one_names(): void
    {
        $path = dirname(__DIR__, 3).'/docs/market_data/authority/strategy/book/Determinism_Invariants_LOCKED.md';
        $this->assertFileExists($path);
        $source = (string) file_get_contents($path);

        $start = strpos($source, 'Invariant 1');
        $end = strpos($source, 'This must hold across reruns and replay.', (int) $start);
        $this->assertNotFalse($end, 'Invariant 1 no longer carries the reruns-and-replay clause');

        preg_match_all('/`([a-z_]+_batch_hash)` must be identical/', substr($source, (int) $start, $end - (int) $start), $matches);

        $named = array_values(array_unique($matches[1]));
        $mapped = array_column($this->batchHashes(), 0);
        sort($named);
        sort($mapped);

        $this->assertSame($named, $mapped,
            'Invariant 1 and the reviewed batch-hash table disagree about which hashes must be '
                .'identical across reruns and replay');
    }
    /** @return array<string,string> artifact filename => sha256 of its bytes */
    private function filesIn(string $dir): array
    {
        $out = [];
        foreach (scandir($dir) ?: [] as $name) {
            if ($name === '.' || $name === '..') {
                continue;
            }
            $path = $dir.'/'.$name;
            if (is_file($path)) {
                $out[$name] = hash_file('sha256', $path);
            }
        }
        ksort($out);

        return $out;
    }

    private function export(array $metric): string
    {
        $evidence = m::mock(EodEvidenceRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $corrections = m::mock(EodCorrectionRepository::class);
        $evidence->shouldReceive('findReplayMetric')->once()->andReturn((object) $metric);
        $evidence->shouldReceive('replayReasonCodeCounts')->once()->andReturn([]);

        $service = new MarketDataEvidenceExportService($evidence, $publications, $corrections);
        $dir = sys_get_temp_dir().'/md_b18_rerun_'.uniqid();
        $service->exportReplayEvidence(5501, '2026-02-11', $dir);

        return $dir;
    }

    /** @return array<string,mixed> */
    private function metric(array $override = []): array
    {
        return array_merge([
            'replay_id' => 5501,
            'replay_mode' => 'EXACT_PUBLICATION',
            'trade_date' => '2026-02-11',
            'trade_date_effective' => '2026-02-11',
            'knowledge_cutoff_at' => '2026-02-11 18:00:00',
            'fixture_id' => 'fixture_rerun_determinism',
            'fixture_version' => '1',
            'fixture_schema_version' => '1',
            'fixture_manifest_hash' => 'FIXTURE_MANIFEST_HASH',
            'replay_suite' => 'exact_publication',
            'replay_case' => 'unchanged_rerun',
            'source' => 'api',
            'source_mode' => 'api',
            'source_name' => 'YAHOO_FINANCE',
            'source_provider' => 'yahoo',
            'status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'final_reason_code' => null,
            'publication_id' => 9901,
            'publication_run_id' => 771,
            'publication_version' => 3,
            'is_current_publication' => 1,
            'comparison_result' => 'MATCH',
            'replay_status' => 'PASS',
            'comparison_note' => null,
            'artifact_changed_scope' => null,
            'config_identity' => 'cfg_2026_02_v1',
            'source_observation_manifest_hash' => 'OBS_HASH',
            'canonical_raw_input_hash' => 'RAW_HASH',
            'temporal_identity_hash' => 'TEMPORAL_HASH',
            'calendar_status_hash' => 'CALENDAR_HASH',
            'event_factor_hash' => 'EVENT_HASH',
            'config_snapshot_id' => 'CFG_ID',
            'config_snapshot_hash' => 'CFG_HASH',
            'formula_registry_hash' => 'FORMULA_HASH',
            'reason_registry_hash' => 'REASON_HASH',
            'read_model_version' => 'READ_MODEL_V1',
            'serialization_version' => 'SER_V1',
            'executable_build_identity' => 'BUILD_1',
            'bound_input_context_json' => json_encode(['scenario' => 'rerun']),
            'coverage_universe_count' => 500,
            'coverage_expected_count' => 500,
            'coverage_available_count' => 500,
            'coverage_missing_count' => 0,
            'coverage_ratio' => '1.0000',
            'coverage_min_threshold' => '0.9800',
            'coverage_gate_state' => 'PASS',
            'coverage_threshold_mode' => 'MIN_RATIO',
            'coverage_universe_basis' => 'active_equity_universe_asof_trade_date',
            'coverage_contract_version' => 'coverage_gate_v1',
            'coverage_missing_sample_json' => json_encode([]),
            'bars_rows_written' => 500,
            'indicators_rows_written' => 500,
            'eligibility_rows_written' => 500,
            'eligible_count' => 450,
            'invalid_bar_count' => 0,
            'invalid_indicator_count' => 0,
            'warning_count' => 0,
            'hard_reject_count' => 0,
            'bars_batch_hash' => 'BARS_HASH',
            'indicators_batch_hash' => 'IND_HASH',
            'eligibility_batch_hash' => 'ELIG_HASH',
            'seal_state' => 'SEALED',
            'sealed_at' => '2026-02-11 17:30:00',
            'expected_status' => 'SUCCESS',
            'expected_terminal_status' => 'SUCCESS',
            'expected_publishability_state' => 'READABLE',
            'expected_source_mode' => 'api',
            'expected_source_name' => 'YAHOO_FINANCE',
            'expected_source_provider' => 'yahoo',
            'expected_publication_id' => 9901,
            'expected_publication_run_id' => 771,
            'expected_is_current_publication' => 1,
            'expected_trade_date_effective' => '2026-02-11',
            'expected_seal_state' => 'SEALED',
            'expected_config_identity' => 'cfg_2026_02_v1',
            'expected_publication_version' => 3,
            'expected_coverage_universe_count' => 500,
            'expected_coverage_expected_count' => 500,
            'expected_coverage_available_count' => 500,
            'expected_coverage_missing_count' => 0,
            'expected_coverage_ratio' => '1.0000',
            'expected_coverage_min_threshold' => '0.9800',
            'expected_coverage_gate_state' => 'PASS',
            'expected_coverage_threshold_mode' => 'MIN_RATIO',
            'expected_coverage_universe_basis' => 'active_equity_universe_asof_trade_date',
            'expected_coverage_contract_version' => 'coverage_gate_v1',
            'expected_coverage_missing_sample_json' => json_encode([]),
            'expected_bars_batch_hash' => 'BARS_HASH',
            'expected_indicators_batch_hash' => 'IND_HASH',
            'expected_eligibility_batch_hash' => 'ELIG_HASH',
            'expected_reason_code_counts_json' => json_encode([]),
            'mismatch_summary' => null,
            'created_at' => '2026-02-11T18:15:00+07:00',
        ], $override);
    }
}

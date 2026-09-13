<?php

use App\Application\MarketData\Services\MarketDataEvidenceExportService;
use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use App\Infrastructure\Persistence\MarketData\EodEvidenceRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use Mockery as m;
use PHPUnit\Framework\TestCase;

/**
 * `MD-B18-A002` -- `MD-S050-R0032`.
 *
 * > Evidence preserves fixture/manifest hashes, actual and expected contexts, mismatch paths,
 * > reason distributions, publication/pointer context, executable command/build identity,
 * > timestamps, and admission state without requiring a mutable database as the primary
 * > explanation.
 *
 * `B18ReplayEvidencePreservationContractTest` exports real evidence and asserts a different list --
 * the `MD-S040` one, from `Manual_File_Publishability_Policy_LOCKED.md`. It also exports a replay
 * whose `comparison_result` is `EXPECTED_DEGRADE` and whose `mismatch_summary` is null, so the two
 * items `MD-S050-R0032` names that the other list does not -- mismatch paths and reason
 * distributions -- are empty in every export that guard produces. Asserting they survive would
 * have asserted that null survives.
 *
 * This class exports a replay that actually **failed**, so the mismatch block has content, and then
 * asserts the eight items the `MD-S050` sentence names are each present and carry something. The
 * final clause is the one that decides the shape of the assertions: the evidence must explain the
 * outcome *without a mutable database as the primary explanation*, so it is not enough for a field
 * to exist -- a mismatch entry has to name what diverged and to what, and a reason distribution has
 * to carry counts, from the exported files alone.
 */
class B18ReplayEvidenceSelfExplanationTest extends TestCase
{
    private const REPLAY_ID = 3201;

    private const TRADE_DATE = '2025-12-10';

    private const CONTRACT = 'docs/market_data/authority/strategy/book/Replay_Verification_Contract_LOCKED.md';

    /** @var string|null */
    private $exportDir;

    protected function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    /**
     * The contract's own items, each mapped to the exported path that carries it.
     *
     * @return array<string,string>
     */
    private function preservationMap(): array
    {
        return [
            'fixture/manifest hashes' => 'replay_result.json:fixture_manifest_hash',
            'actual and expected contexts' => 'replay_result.json:actual_context',
            'mismatch paths' => 'replay_result.json:mismatches',
            'reason distributions' => 'replay_reason_code_counts.json:',
            'publication/pointer context' => 'replay_result.json:pointer_context',
            'executable command/build identity' => 'replay_result.json:bound_inputs.executable_build_identity',
            'timestamps' => 'replay_result.json:created_at',
            'admission state' => 'replay_result.json:admission_state',
        ];
    }

    /**
     * `MD-S050-R0032` -- the map and the contract sentence must name the same items.
     *
     * Parsed rather than transcribed, so an item added to the sentence with no exported path behind
     * it fails here instead of leaving the claim quietly short.
     */
    public function test_the_preservation_map_names_exactly_what_the_contract_names(): void
    {
        $path = dirname(__DIR__, 3).'/'.self::CONTRACT;
        $this->assertFileExists($path);

        $this->assertSame(1, preg_match(
            '/^Evidence preserves (.+?) without requiring a mutable database as the primary explanation\.$/m',
            (string) file_get_contents($path),
            $match
        ), 'the MD-S050 evidence-preservation sentence moved; re-read it rather than weakening this map');

        $items = preg_split('/,\s*and\s+|,\s*/', trim($match[1]));
        $items = array_values(array_filter(array_map('trim', $items)));
        $mapped = array_keys($this->preservationMap());
        sort($items);
        sort($mapped);

        $this->assertSame($items, $mapped,
            'MD-S050 and the reviewed preservation map disagree about what replay evidence must '
                .'preserve');
    }

    /**
     * `MD-S050-R0032` -- every named item survives a real export of a failing replay.
     */
    public function test_every_preserved_item_survives_the_export_of_a_failed_replay(): void
    {
        $dir = $this->export();
        $absent = [];

        foreach ($this->preservationMap() as $item => $target) {
            [$file, $path] = explode(':', $target, 2);
            $payload = $this->readJson($dir, $file);

            $value = $path === '' ? $payload : $this->dig($payload, $path);
            if ($value === null || $value === [] || $value === '') {
                $absent[] = $item.' -> '.$target;
            }
        }

        $this->assertSame([], $absent,
            'these items MD-S050 requires evidence to preserve are absent or empty in the export');
    }

    /**
     * The mismatch paths have to say what diverged. A `mismatches` array that exists but whose
     * entries do not name a field is a list of the fact that something went wrong, which is exactly
     * the state the contract's "without a mutable database as the primary explanation" clause
     * exists to prevent: the reader would have to go and query the run to learn anything.
     */
    public function test_each_exported_mismatch_names_the_field_and_both_values(): void
    {
        $result = $this->readJson($this->export(), 'replay_result.json');

        $this->assertNotEmpty($result['mismatches'], 'a FAIL export with no mismatch paths explains nothing');

        foreach ($result['mismatches'] as $index => $mismatch) {
            $this->assertArrayHasKey('field', $mismatch, 'mismatch '.$index.' does not name what diverged');
            $this->assertNotSame('', (string) $mismatch['field']);
            $this->assertArrayHasKey('expected', $mismatch, 'mismatch '.$index.' does not carry the expectation');
            $this->assertArrayHasKey('actual', $mismatch, 'mismatch '.$index.' does not carry what was found');
            $this->assertArrayHasKey('reason_code', $mismatch, 'mismatch '.$index.' carries no reason code');
        }

        // The count and the paths must agree, or one of them is decoration.
        $this->assertSame(count($result['mismatches']), $result['mismatch_count'],
            'the exported mismatch count and the exported mismatch paths disagree');
    }

    /**
     * The reason distribution has to carry counts. A list of reason codes without their counts is
     * not a distribution, and the difference matters: one missing bar and nine hundred missing bars
     * are the same list and very different runs.
     */
    public function test_the_exported_reason_distribution_carries_counts(): void
    {
        $dir = $this->export();
        $counts = $this->readJson($dir, 'replay_reason_code_counts.json');

        $this->assertNotEmpty($counts);
        foreach ($counts as $row) {
            $this->assertArrayHasKey('reason_code', $row);
            $this->assertArrayHasKey('reason_count', $row);
            $this->assertGreaterThan(0, (int) $row['reason_count'],
                'a reason code recorded with no count is a label, not a distribution');
        }

        $result = $this->readJson($dir, 'replay_result.json');
        $this->assertNotEmpty($result['mismatch_reason_codes'],
            'the failing replay must also carry its mismatch reason codes in the result itself');
    }

    /**
     * The control on the whole class. A replay that matched exports the same eight items, and its
     * mismatch block is legitimately empty -- so the assertions above are about a failing replay
     * having an explanation, not about the exporter refusing to write a passing one.
     */
    public function test_a_matching_replay_exports_the_same_structure_with_no_mismatches(): void
    {
        $dir = $this->export([
            'comparison_result' => 'MATCH',
            'replay_status' => 'PASS',
            'mismatch_summary' => null,
            'mismatch_count' => 0,
            'mismatch_reason_codes_json' => json_encode([]),
            'mismatches_json' => json_encode([]),
        ]);

        $result = $this->readJson($dir, 'replay_result.json');

        $this->assertSame([], $result['mismatches']);
        $this->assertSame(0, $result['mismatch_count']);
        // Everything that is not about divergence is still there, so a PASS export is not a
        // thinner artifact than a FAIL one.
        $this->assertNotEmpty($result['fixture_manifest_hash']);
        $this->assertNotEmpty($result['actual_context']);
        $this->assertNotEmpty($result['admission_state']);
        $this->assertNotEmpty($result['created_at']);
    }


    // ---- MD-S003-R0023: the per-run recording obligation -----------------------------------------

    /**
     * `MD-S003` "Per-run evidence", mapped item by item to where the export records it.
     *
     * The knowledge cutoff is the one item that cannot be asserted on a publication replay: a
     * `PUBLICATION_EXACT` result records `knowledge_cutoff_at` as null by design, because it is
     * pinned to an immutable publication rather than to a moment of knowledge. It is asserted on an
     * `AS_KNOWN` export instead, which is where the contract's own two-mode split puts it.
     *
     * @return array<string,string> contract item => exported path
     */
    private function recordingMap(): array
    {
        return [
            'replay mode' => 'replay_result.json:replay_mode',
            'fixture/manifest hash' => 'replay_result.json:fixture_manifest_hash',
            'requested/effective dates' => 'replay_result.json:trade_date_effective',
            'knowledge cutoff' => 'AS_KNOWN:knowledge_cutoff_at',
            'all frozen revision/snapshot IDs' => 'replay_result.json:bound_inputs',
            'expected/actual readiness and reason sets' => 'replay_evidence_pack.json:expected_state',
            'field-level mismatch paths' => 'replay_result.json:mismatches',
            'artifact/manifest/seal hashes' => 'replay_result.json:bars_batch_hash',
            'executable build identity' => 'replay_result.json:bound_inputs.executable_build_identity',
            '`PASS`/`FAIL`/`BLOCKED`' => 'replay_result.json:replay_status',
        ];
    }

    /**
     * `MD-S003-R0023` -- the map and the contract sentence must name the same items.
     */
    public function test_the_recording_map_names_exactly_what_the_contract_names(): void
    {
        $path = dirname(__DIR__, 3).'/docs/market_data/authority/strategy/backtest/Historical_Replay_and_Data_Quality_Backtest.md';
        $this->assertFileExists($path);

        $this->assertSame(1, preg_match(
            '/^Record (.+?)\.$/m',
            (string) file_get_contents($path),
            $match
        ), 'the MD-S003 per-run evidence line moved; re-read it rather than weakening this map');

        $items = preg_split('/,\s*and\s+|,\s*/', trim($match[1]));
        $items = array_values(array_filter(array_map('trim', $items)));
        $mapped = array_keys($this->recordingMap());
        sort($items);
        sort($mapped);

        $this->assertSame($items, $mapped,
            'MD-S003 and the reviewed recording map disagree about what per-run evidence must record');
    }

    /**
     * `MD-S003-R0023` -- every named item is actually recorded.
     */
    public function test_every_item_the_contract_requires_recording_is_present(): void
    {
        $dir = $this->export();
        $absent = [];

        foreach ($this->recordingMap() as $item => $target) {
            [$file, $path] = explode(':', $target, 2);

            if ($file === 'AS_KNOWN') {
                // Recorded by the as-known mode, which is the only one that has a cutoff.
                $value = $this->dig($this->readJson($this->exportAsKnown(), 'replay_result.json'), $path);
            } else {
                $value = $this->dig($this->readJson($dir, $file), $path);
            }

            if ($value === null || $value === [] || $value === '') {
                $absent[] = $item.' -> '.$target;
            }
        }

        $this->assertSame([], $absent,
            'these items MD-S003 requires per-run evidence to record are absent from the export');
    }

    /**
     * The frozen revision and snapshot IDs are recorded individually, not as a single blob. "All"
     * is the load-bearing word: a bound-input block carrying one identity would satisfy a check
     * that the block exists.
     */
    public function test_every_frozen_identity_is_recorded_individually(): void
    {
        $bound = $this->dig($this->readJson($this->export(), 'replay_result.json'), 'bound_inputs');

        foreach ([
            'source_observation_manifest_hash', 'canonical_raw_input_hash', 'temporal_identity_hash',
            'calendar_status_hash', 'event_factor_hash', 'config_snapshot_id', 'config_snapshot_hash',
            'formula_registry_hash', 'reason_registry_hash', 'read_model_version',
            'serialization_version', 'executable_build_identity',
        ] as $identity) {
            $this->assertArrayHasKey($identity, $bound, $identity.' is not recorded in the evidence');
            $this->assertNotSame('', (string) $bound[$identity], $identity.' is recorded empty');
        }
    }

    /** An AS_KNOWN export, for the one recorded item a publication replay legitimately has no value for. */
    private function exportAsKnown(): string
    {
        return $this->export([
            'replay_mode' => 'AS_KNOWN',
            'knowledge_cutoff_at' => '2025-12-09 18:00:00',
            'publication_id' => null,
            'source' => 'as_known_replay',
        ]);
    }

    // ---- MD-S004-R0004: what every row/export binds ----------------------------------------------

    /**
     * `Point_In_Time_Backtest_Input_Contract_LOCKED.md`, "Required input identity".
     *
     * > Every row/export binds listing identity, requested/effective trade date, knowledge cutoff,
     * > as-known replay ID/publication-like artifact ID, read-model version, full config hash,
     * > factor/formula versions, and lineage.
     *
     * The knowledge cutoff is again taken from an `AS_KNOWN` export, for the reason recorded on the
     * recording map: a publication replay is pinned to an immutable publication rather than to a
     * moment of knowledge and records it null by design.
     *
     * @return array<string,string> contract item => exported path
     */
    private function requiredBindingMap(): array
    {
        return [
            'listing identity' => 'replay_result.json:bound_inputs.temporal_identity_hash',
            'requested/effective trade date' => 'replay_result.json:trade_date_effective',
            'knowledge cutoff' => 'AS_KNOWN:knowledge_cutoff_at',
            'as-known replay ID/publication-like artifact ID' => 'replay_result.json:replay_id',
            'read-model version' => 'replay_result.json:bound_inputs.read_model_version',
            'full config hash' => 'replay_result.json:bound_inputs.config_snapshot_hash',
            'factor/formula versions' => 'replay_result.json:bound_inputs.formula_registry_hash',
            'lineage' => 'replay_result.json:publication_context.publication_artifact_lineage',
        ];
    }

    /**
     * `MD-S004-R0004` -- the map and the contract must name the same bindings.
     */
    public function test_the_required_binding_map_names_exactly_what_the_contract_names(): void
    {
        $path = dirname(__DIR__, 3).'/docs/market_data/authority/strategy/backtest/Point_In_Time_Backtest_Input_Contract_LOCKED.md';
        $this->assertFileExists($path);

        $this->assertSame(1, preg_match(
            '/Every row\/export binds (.+?)\.\s/s',
            (string) file_get_contents($path),
            $match
        ), 'the MD-S004 required-input-identity sentence moved; re-read it rather than weakening this map');

        $items = preg_split('/,\s*and\s+|,\s*/', trim($match[1]));
        $items = array_values(array_filter(array_map('trim', $items)));
        $mapped = array_keys($this->requiredBindingMap());
        sort($items);
        sort($mapped);

        $this->assertSame($items, $mapped,
            'MD-S004 and the reviewed binding map disagree about what every row and export must bind');
    }

    /**
     * `MD-S004-R0004` -- every named binding is actually bound in a real export.
     */
    public function test_every_required_binding_is_present_in_the_export(): void
    {
        $dir = $this->export();
        $absent = [];

        foreach ($this->requiredBindingMap() as $item => $target) {
            [$file, $path] = explode(':', $target, 2);
            $payload = $file === 'AS_KNOWN'
                ? $this->readJson($this->exportAsKnown(), 'replay_result.json')
                : $this->readJson($dir, $file);

            $value = $this->dig($payload, $path);
            if ($value === null || $value === [] || $value === '') {
                $absent[] = $item.' -> '.$target;
            }
        }

        $this->assertSame([], $absent,
            'these bindings MD-S004 requires of every row and export are absent');
    }

    /**
     * `MD-S004-R0004`, final clause -- "Availability timestamp is distinct from market trade date."
     *
     * The two answer different questions: when the platform could see the data, and which session
     * the data describes. Collapsing them is how a backtest silently gains foresight, so the export
     * must carry both and they must not be the same field.
     */
    public function test_the_availability_timestamp_is_a_separate_field_from_the_trade_date(): void
    {
        $result = $this->readJson($this->export(), 'replay_result.json');

        $this->assertArrayHasKey('trade_date', $result);
        $this->assertArrayHasKey('created_at', $result);
        $this->assertNotSame($result['trade_date'], $result['created_at'],
            'the market trade date and the availability timestamp are the same value, so one of '
                .'them is standing in for the other');

        // And the fixture identity carries its own creation moment, distinct from both.
        $this->assertArrayHasKey('fixture_created_at', $result);
        $this->assertNotSame($result['trade_date'], $result['fixture_created_at']);
    }

    // ---- MD-S050-R0041: a claim citing replay evidence names the mode that produced it -----------

    /**
     * `MD-S050-R0041`: "A conformance or activation claim that cites replay evidence names which
     * mode produced it. Citing an unmoded or publication-only corpus in support of a point-in-time
     * property is a governance violation."
     *
     * The exported evidence pack is where a claim citing replay evidence is actually materialised,
     * so it is where the rule is enforceable. Every pack declares the mode among the sections its
     * admission requires, and the mode is carried in the result itself.
     */
    public function test_every_exported_pack_requires_and_carries_the_mode_that_produced_it(): void
    {
        foreach ([$this->export(), $this->exportAsKnown()] as $dir) {
            $admission = $this->readJson($dir, 'evidence_admission.json');
            $result = $this->readJson($dir, 'replay_result.json');

            $this->assertContains('replay_mode', $admission['required_sections'],
                'an evidence pack that does not require its mode can be cited without naming one');
            $this->assertNotSame('', (string) $result['replay_mode']);
            $this->assertSame('ADMITTED_COMPLETE', $admission['evidence_admission_state']);
        }
    }

    /**
     * `MD-S050-R0041` -- an unmoded result cannot be cited.
     *
     * `MD-S050-R0035` made the mode mandatory on write, so such a row can no longer be created.
     * This is the read side of the same rule, and it is the side that matters for a corpus that
     * predates the write constraint: exporting a result with no mode yields
     * `ADMITTED_INCOMPLETE`, and the missing section names the unclassified state rather than
     * quietly defaulting the pack to publication replay.
     */
    public function test_an_unmoded_result_is_not_admitted_as_citable_evidence(): void
    {
        $admission = $this->readJson($this->export(['replay_mode' => null]), 'evidence_admission.json');

        $this->assertSame('ADMITTED_INCOMPLETE', $admission['evidence_admission_state'],
            'an unmoded result was admitted as complete evidence, so it can be cited as either mode');
        $this->assertSame('EVIDENCE_ADMISSION_INCOMPLETE', $admission['evidence_admission_reason_code']);
        $this->assertContains('replay_mode', $admission['missing_sections']);
        $this->assertContains('replay_mode_invalid_or_historical_unclassified', $admission['missing_sections'],
            'the pack must say the result is unclassified rather than leave the reader to assume '
                .'a default mode');
    }

    /**
     * `MD-S050-R0041` -- a publication-only corpus cannot support a point-in-time property.
     *
     * The two modes are not held to the same requirements. An as-known pack must additionally
     * account for the knowledge cutoff and the revision identities resolved under it; a publication
     * pack is never asked for them. So a publication pack has not been examined for the property a
     * point-in-time claim rests on, and citing it for one is citing evidence that was never
     * assessed against the question.
     */
    public function test_a_publication_pack_is_not_held_to_the_as_known_requirements(): void
    {
        $publication = $this->readJson($this->export(), 'evidence_admission.json');
        $asKnown = $this->readJson($this->exportAsKnown(), 'evidence_admission.json');

        $pointInTimeSections = [
            'knowledge_cutoff_at', 'source_observation_manifest_hash', 'canonical_raw_input_hash',
            'temporal_identity_hash', 'calendar_status_hash', 'event_factor_hash',
            'config_snapshot_hash', 'formula_registry_hash', 'reason_registry_hash',
        ];

        foreach ($pointInTimeSections as $section) {
            $this->assertContains($section, $asKnown['required_sections'],
                'an as-known pack must account for '.$section);
            $this->assertNotContains($section, $publication['required_sections'],
                'a publication pack is required to account for '.$section.', which would make the '
                    .'two corpora interchangeable and a publication-only citation admissible for a '
                    .'point-in-time property');
        }

        // And the publication pack is held to its own, different set, so this is a difference in
        // what each mode is examined for rather than one mode simply being checked less.
        foreach (['publication_context', 'pointer_context'] as $section) {
            $this->assertContains($section, $publication['required_sections']);
            $this->assertNotContains($section, $asKnown['required_sections']);
        }
    }
    // ---- export harness -------------------------------------------------------------------------

    /** @param array<string,mixed> $override */
    private function export(array $override = []): string
    {
        $metric = (object) array_merge($this->failedMetric(), $override);

        $evidence = m::mock(EodEvidenceRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $corrections = m::mock(EodCorrectionRepository::class);

        $evidence->shouldReceive('findReplayMetric')->andReturn($metric);
        $evidence->shouldReceive('replayReasonCodeCounts')->andReturn([
            ['reason_code' => 'ELIG_MISSING_BAR', 'reason_count' => 120],
            ['reason_code' => 'IND_INSUFFICIENT_HISTORY', 'reason_count' => 17],
        ]);

        $dir = sys_get_temp_dir().'/md_b18_self_explanation_'.uniqid();
        (new MarketDataEvidenceExportService($evidence, $publications, $corrections))
            ->exportReplayEvidence(self::REPLAY_ID, self::TRADE_DATE, $dir);

        return $dir;
    }

    /** @return array<string,mixed> */
    private function readJson(string $dir, string $file): array
    {
        $path = $dir.'/'.$file;
        $this->assertFileExists($path, $file.' was not written by the export');

        return json_decode((string) file_get_contents($path), true);
    }

    /** @param array<string,mixed> $data */
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

    /**
     * A replay that ran and diverged: three mismatches across different assertion classes, each
     * naming its field, both values and a reason code.
     *
     * @return array<string,mixed>
     */
    private function failedMetric(): array
    {
        return [
            'replay_id' => self::REPLAY_ID,
            'replay_mode' => 'PUBLICATION_EXACT',
            'knowledge_cutoff_at' => null,
            'admission_state' => 'ADMISSIBLE',
            'trade_date' => self::TRADE_DATE,
            'trade_date_effective' => self::TRADE_DATE,
            'source' => 'manual_file',
            'source_mode' => 'manual_file',
            'source_name' => 'LOCAL_FILE',
            'source_provider' => 'manual_import',
            'status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'final_reason_code' => 'COVERAGE_THRESHOLD_MET',
            'publication_id' => 4411,
            'publication_run_id' => 103,
            'publication_version' => 7,
            'is_current_publication' => 1,
            'comparison_result' => 'MISMATCH',
            'replay_status' => 'FAIL',
            'comparison_note' => 'deterministic replay fixture expectation',
            'artifact_changed_scope' => 'bars',
            'config_identity' => 'cfg_2025_12_v2',

            // The frozen inputs, so the bound-input block is populated.
            'fixture_manifest_hash' => str_repeat('a', 64),
            'source_observation_manifest_hash' => str_repeat('b', 64),
            'canonical_raw_input_hash' => str_repeat('c', 64),
            'temporal_identity_hash' => str_repeat('d', 64),
            'calendar_status_hash' => str_repeat('e', 64),
            'event_factor_hash' => str_repeat('f', 64),
            'config_snapshot_id' => 7001,
            'config_snapshot_hash' => str_repeat('0', 64),
            'formula_registry_hash' => str_repeat('1', 64),
            'reason_registry_hash' => str_repeat('2', 64),
            'read_model_version' => 'market_data_read_model_v1',
            'serialization_version' => 'canonical_json_v1',
            'executable_build_identity' => 'build-2025-12-10-abc123',
            'bound_input_context_json' => json_encode(['mode' => 'PUBLICATION_EXACT', 'publication_id' => 4411]),

            'replay_suite' => 'fixture_replay_divergent',
            'replay_case' => 'fixture_replay_divergent',
            'fixture_id' => 'fixture_replay_divergent',
            'fixture_version' => 'v2',
            'fixture_schema_version' => 'replay_fixture_v2',
            'fixture_source' => 'unit_test',
            'fixture_created_at' => '2025-12-01T00:00:00+07:00',

            'coverage_universe_count' => 1000,
            'coverage_expected_count' => 1000,
            'coverage_available_count' => 1000,
            'coverage_missing_count' => 0,
            'coverage_ratio' => '1.0000',
            'coverage_min_threshold' => '0.9800',
            'coverage_gate_state' => 'PASS',
            'coverage_reason_code' => 'COVERAGE_THRESHOLD_MET',
            'coverage_threshold_mode' => 'MIN_RATIO',
            'coverage_universe_basis' => 'active_equity_universe_asof_trade_date',
            'coverage_contract_version' => 'coverage_gate_v1',
            'coverage_missing_sample_json' => json_encode([]),

            'bars_rows_written' => 1000,
            'indicators_rows_written' => 1000,
            'eligibility_rows_written' => 1000,
            'eligible_count' => 650,
            'invalid_bar_count' => 0,
            'invalid_indicator_count' => 0,
            'warning_count' => 0,
            'hard_reject_count' => 0,
            'bars_batch_hash' => 'A2',
            'indicators_batch_hash' => 'B1',
            'eligibility_batch_hash' => 'C1',
            'seal_state' => 'SEALED',
            'sealed_at' => '2025-12-10 17:30:00',

            'expected_status' => 'SUCCESS',
            'expected_terminal_status' => 'SUCCESS',
            'expected_publishability_state' => 'READABLE',
            'expected_publication_id' => 4411,
            'expected_publication_run_id' => 103,
            'expected_publication_version' => 7,
            'expected_is_current_publication' => 1,
            'expected_trade_date_effective' => self::TRADE_DATE,
            'expected_seal_state' => 'SEALED',
            'expected_config_identity' => 'cfg_2025_12_v2',
            'expected_bars_batch_hash' => 'A1',
            'expected_indicators_batch_hash' => 'B1',
            'expected_eligibility_batch_hash' => 'C1',
            'expected_reason_code_counts_json' => json_encode(['ELIG_MISSING_BAR' => 120]),

            // What the comparison actually found. This is the block the other evidence guard never
            // exercises, because every replay it exports matched.
            'mismatch_summary' => 'bars_batch_hash, bound_input_temporal_identity_hash, seal_state',
            'mismatch_count' => 3,
            'mismatch_reason_codes_json' => json_encode([
                'REPLAY_ARTIFACT_HASH_MISMATCH',
                'REPLAY_NON_DETERMINISTIC_OUTPUT',
                'REPLAY_SEAL_STATE_MISMATCH',
            ]),
            'mismatches_json' => json_encode([
                ['field' => 'bars_batch_hash', 'expected' => 'A1', 'actual' => 'A2', 'reason_code' => 'REPLAY_ARTIFACT_HASH_MISMATCH'],
                ['field' => 'bound_input_temporal_identity_hash', 'expected' => str_repeat('9', 64), 'actual' => str_repeat('d', 64), 'reason_code' => 'REPLAY_NON_DETERMINISTIC_OUTPUT'],
                ['field' => 'seal_state', 'expected' => 'UNSEALED', 'actual' => 'SEALED', 'reason_code' => 'REPLAY_SEAL_STATE_MISMATCH'],
            ]),
            'expected_context_json' => json_encode(['expected_final_state' => ['terminal_status' => 'SUCCESS']]),
            'actual_context_json' => json_encode(['actual_final_state' => ['terminal_status' => 'SUCCESS']]),
            'ignored_volatile_fields_json' => json_encode([]),
            'deterministic_fields_checked_json' => json_encode(['lineage', 'seal_state', 'bars_batch_hash']),

            'created_at' => '2025-12-10T17:45:00+07:00',
        ];
    }
}

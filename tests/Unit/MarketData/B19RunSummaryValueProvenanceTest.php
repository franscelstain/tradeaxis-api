<?php

use App\Application\MarketData\Services\MarketDataEvidenceExportService;
use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use App\Infrastructure\Persistence\MarketData\EodEvidenceRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use Mockery as m;
use PHPUnit\Framework\TestCase;

/**
 * `MD-B19` — what the `run_summary.json` fields CONTAIN, not only that they exist.
 *
 * `B19RunSummaryArtifactContractTest` proves every minimum field of `Run_Artifacts_Format_LOCKED.md`
 * section 1 is a key of the exported file. It cannot prove more, and in one respect it proves less
 * than it appears to: its publication-facing fields are keys holding `null`, because its publication
 * lookup returns nothing. A key holding the wrong run's value, a neighbour's value, a stale value or an
 * invented default passes it unchanged.
 *
 * This guard gives every column of the run record a different value, so a field read from the wrong
 * column, swapped with another, or defaulted cannot coincide with the right answer; gives the
 * publication manifest different values again, and a decoy manifest for every other publication id; and
 * puts decoy values on the run record under the names of the manifest-derived fields, so a summary that
 * read them from the run is caught.
 *
 * The expected value of each field is stated here, per rule id, from the contract: a field that mirrors
 * persisted run state carries that column's value under the persisted name (`MD-S075-R0074`); a
 * publication-facing field is read from the manifest of the run's own publication and is null when no
 * publication resolved (`R0075`, `R0072`, `R0073`); `source_context` is companion evidence that recovers
 * minimum fields from persisted telemetry and invents none (`R0076`).
 */
class B19RunSummaryValueProvenanceTest extends TestCase
{
    private const OWN_PUBLICATION = 1201;

    protected function tearDown(): void
    {
        m::close();
    }

    // ------------------------------------------------------------------ fixtures

    /** @var array<string,string> summary field => rule id, in contract order */
    private const RULE = [
        'run_id' => 'R0025', 'trade_date_requested' => 'R0026', 'trade_date_effective' => 'R0027', 'lifecycle_state' => 'R0028',
        'terminal_status' => 'R0029', 'quality_gate_state' => 'R0030', 'publishability_state' => 'R0031', 'stage' => 'R0032', 'source' => 'R0033',
        'coverage_ratio' => 'R0041', 'bars_rows_written' => 'R0042', 'indicators_rows_written' => 'R0043', 'eligibility_rows_written' => 'R0044',
        'invalid_bar_count' => 'R0045', 'invalid_indicator_count' => 'R0046', 'warning_count' => 'R0047', 'hard_reject_count' => 'R0048',
        'bars_batch_hash' => 'R0049', 'indicators_batch_hash' => 'R0050', 'eligibility_batch_hash' => 'R0051', 'observation_manifest_hash' => 'R0052',
        'publication_manifest_hash' => 'R0053', 'sealed_at' => 'R0054', 'config_version' => 'R0055', 'config_snapshot_id' => 'R0056',
        'config_snapshot_hash' => 'R0057', 'temporal_revision_set_hash' => 'R0058', 'factor_set_id' => 'R0059', 'factor_set_hash' => 'R0060',
        'price_product_code' => 'R0061', 'canonicalization_version' => 'R0062', 'formula_version' => 'R0063', 'read_model_version' => 'R0064',
        'freshness_state' => 'R0065', 'publication_version' => 'R0066', 'is_current_publication' => 'R0067', 'supersedes_run_id' => 'R0068',
        'started_at' => 'R0069', 'finished_at' => 'R0070',
    ];

    /** The run record: every column a different value; casts as the persisted column would deliver them. */
    private function runRecord(array $override = []): object
    {
        return (object) array_merge([
            'run_id' => 81240, 'run_uuid' => 'uuid-81240',
            'trade_date_requested' => '2026-04-21', 'trade_date_effective' => '2026-04-20',
            'lifecycle_state' => 'COMPLETED', 'terminal_status' => 'SUCCESS', 'quality_gate_state' => 'PASS', 'publishability_state' => 'READABLE',
            'stage' => 'FINALIZE', 'source' => 'api', 'request_mode' => 'daily',
            'coverage_ratio' => '0.842000', 'coverage_gate_state' => 'PASS',
            'bars_rows_written' => 8421, 'indicators_rows_written' => 8302, 'eligibility_rows_written' => 10003,
            'invalid_bar_count' => 181, 'invalid_indicator_count' => 1702, 'warning_count' => 503, 'hard_reject_count' => 124,
            'bars_batch_hash' => str_repeat('1', 64), 'indicators_batch_hash' => str_repeat('2', 64), 'eligibility_batch_hash' => str_repeat('3', 64),
            'observation_manifest_hash' => str_repeat('4', 64), 'sealed_at' => '2026-04-21 18:00:00',
            'config_version' => 'cfg_run_4', 'config_hash' => str_repeat('e', 64), 'config_snapshot_id' => 90017,
            'factor_set_hash' => str_repeat('6', 64), 'price_product_code' => 'PRODUCT_RUN', 'freshness_state' => 'FRESH_RUN',
            'publication_id' => self::OWN_PUBLICATION, 'publication_version' => 4, 'is_current_publication' => 1, 'supersedes_run_id' => 7001,
            'started_at' => '2026-04-21 17:01:00', 'finished_at' => '2026-04-21 17:09:30', 'notes' => '',
            // decoys: names the summary takes from the manifest, which a run does not hold
            'publication_manifest_hash' => 'DECOY_RUN', 'config_snapshot_hash' => 'DECOY_RUN', 'temporal_revision_set_hash' => 'DECOY_RUN',
            'factor_set_id' => 666, 'canonicalization_version' => 'DECOY_RUN', 'formula_version' => 'DECOY_RUN', 'read_model_version' => 'DECOY_RUN',
        ], $override);
    }

    private function manifest(): array
    {
        return [
            'publication_id' => self::OWN_PUBLICATION, 'run_id' => 81240, 'publication_version' => 4, 'is_current' => true,
            'publication_manifest_hash' => str_repeat('7', 64), 'config_snapshot_hash' => str_repeat('8', 64),
            'temporal_revision_set_hash' => str_repeat('9', 64), 'factor_set_id' => 70055,
            'canonicalization_version' => 'canon_manifest', 'formula_version' => 'formula_manifest', 'read_model_version' => 'read_manifest',
            'seal_state' => 'SEALED',
        ];
    }

    private function decoyManifest(): array
    {
        return array_merge($this->manifest(), [
            'publication_id' => 999, 'publication_manifest_hash' => 'DECOY_SIBLING', 'config_snapshot_hash' => 'DECOY_SIBLING',
            'temporal_revision_set_hash' => 'DECOY_SIBLING', 'factor_set_id' => 555, 'canonicalization_version' => 'DECOY_SIBLING',
            'formula_version' => 'DECOY_SIBLING', 'read_model_version' => 'DECOY_SIBLING',
        ]);
    }

    /**
     * Exports through the real service. `$publicationResolves` false models a run whose publication did
     * not resolve; the manifest is then never requested (asserted).
     *
     * @return array<string,mixed> the exported run_summary.json
     */
    private function exportSummary(object $run, bool $publicationResolves = true): array
    {
        $evidence = m::mock(EodEvidenceRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $corrections = m::mock(EodCorrectionRepository::class);
        $evidence->shouldReceive('findRunById')->andReturn($run);
        $evidence->shouldReceive('summarizeRunEvents')->andReturn([]);
        $evidence->shouldReceive('dominantReasonCodesForEvidencePublication')->andReturn([]);
        $evidence->shouldReceive('exportEligibilityRowsForEvidencePublication')->andReturn([]);
        $evidence->shouldReceive('exportInvalidBarsRows')->andReturn([]);
        $evidence->shouldReceive('resolvePublicationForEvidenceAudit')->andReturn(
            $publicationResolves ? (object) ['publication_id' => self::OWN_PUBLICATION, 'is_current' => 1, 'run_id' => $run->run_id ?? 0] : null
        );
        if ($publicationResolves) {
            $publications->shouldReceive('buildManifestByPublicationId')->andReturnUsing(function ($id) {
                return (int) $id === self::OWN_PUBLICATION ? $this->manifest() : $this->decoyManifest();
            });
        } else {
            $publications->shouldNotReceive('buildManifestByPublicationId');
        }
        $corrections->shouldReceive('findByRunId')->andReturn(null);
        $evidence->shouldIgnoreMissing();
        $publications->shouldIgnoreMissing();
        $corrections->shouldIgnoreMissing();

        $service = new MarketDataEvidenceExportService($evidence, $publications, $corrections);
        $dir = sys_get_temp_dir().'/md_b19_rs_value_'.uniqid('', true);
        $service->exportRunEvidence((int) ($run->run_id ?? 0), $dir);
        $summary = json_decode((string) file_get_contents($dir.'/run_summary.json'), true);
        $this->assertIsArray($summary);

        return $summary;
    }

    // ------------------------------------------------------------------ run-state mirrors

    /** @return array<string,array{0:string,1:mixed}> summary field => [persisted column, expected value] */
    public function mirroredFields(): array
    {
        $cases = [];
        foreach (['run_id' => 81240, 'trade_date_requested' => '2026-04-21', 'trade_date_effective' => '2026-04-20', 'lifecycle_state' => 'COMPLETED',
            'terminal_status' => 'SUCCESS', 'quality_gate_state' => 'PASS', 'publishability_state' => 'READABLE', 'stage' => 'FINALIZE', 'source' => 'api',
            'coverage_ratio' => 0.842, 'bars_rows_written' => 8421, 'indicators_rows_written' => 8302, 'eligibility_rows_written' => 10003,
            'invalid_bar_count' => 181, 'invalid_indicator_count' => 1702, 'warning_count' => 503, 'hard_reject_count' => 124,
            'bars_batch_hash' => str_repeat('1', 64), 'indicators_batch_hash' => str_repeat('2', 64), 'eligibility_batch_hash' => str_repeat('3', 64),
            'observation_manifest_hash' => str_repeat('4', 64), 'sealed_at' => '2026-04-21 18:00:00', 'config_version' => 'cfg_run_4',
            'config_snapshot_id' => 90017, 'factor_set_hash' => str_repeat('6', 64), 'price_product_code' => 'PRODUCT_RUN', 'freshness_state' => 'FRESH_RUN',
            'supersedes_run_id' => 7001, 'started_at' => '2026-04-21 17:01:00', 'finished_at' => '2026-04-21 17:09:30'] as $field => $expected) {
            $cases[$field.' ('.self::RULE[$field].')'] = [$field, $expected];
        }

        return $cases;
    }

    /**
     * `MD-S075-R0025..R0033`, `R0041..R0052`, `R0054..R0056`, `R0060`, `R0061`, `R0065`, `R0068..R0070`,
     * `R0074`: each field that mirrors persisted run state carries the value of its own `eod_runs`
     * column, under the persisted name. Every column holds a different value, so a swapped, defaulted or
     * sibling-sourced field cannot coincide with the right answer.
     *
     * @dataProvider mirroredFields
     */
    public function test_a_mirrored_field_carries_the_value_of_its_own_persisted_column(string $field, $expected): void
    {
        $summary = $this->exportSummary($this->runRecord());

        $this->assertArrayHasKey($field, $summary, self::RULE[$field].': '.$field.' is absent');
        $this->assertSame($expected, $summary[$field], self::RULE[$field].': '.$field.' does not carry the value of its own persisted column');
    }

    /**
     * A persisted NULL stays NULL: no mirrored count is defaulted to zero, no timestamp is filled in.
     */
    public function test_a_persisted_null_is_exported_as_null_not_as_a_default(): void
    {
        $summary = $this->exportSummary($this->runRecord([
            'coverage_ratio' => null, 'bars_rows_written' => null, 'indicators_rows_written' => null, 'eligibility_rows_written' => null,
            'invalid_bar_count' => null, 'invalid_indicator_count' => null, 'warning_count' => null, 'hard_reject_count' => null,
            'bars_batch_hash' => null, 'indicators_batch_hash' => null, 'eligibility_batch_hash' => null, 'observation_manifest_hash' => null,
            'sealed_at' => null, 'config_snapshot_id' => null, 'factor_set_hash' => null, 'price_product_code' => null, 'freshness_state' => null,
            'supersedes_run_id' => null, 'started_at' => null, 'finished_at' => null, 'trade_date_effective' => null,
        ]), false);

        foreach (['coverage_ratio', 'bars_rows_written', 'indicators_rows_written', 'eligibility_rows_written', 'invalid_bar_count', 'invalid_indicator_count',
            'warning_count', 'hard_reject_count', 'bars_batch_hash', 'indicators_batch_hash', 'eligibility_batch_hash', 'observation_manifest_hash', 'sealed_at',
            'config_snapshot_id', 'factor_set_hash', 'price_product_code', 'freshness_state', 'supersedes_run_id', 'started_at', 'finished_at', 'trade_date_effective'] as $field) {
            $this->assertArrayHasKey($field, $summary, self::RULE[$field].': '.$field.' disappeared when null');
            $this->assertNull($summary[$field], self::RULE[$field].': a persisted NULL in '.$field.' was exported as '.json_encode($summary[$field]));
        }
    }

    // ------------------------------------------------------------------ publication-facing fields

    /** @return array<string,array{0:string,1:mixed}> */
    public function manifestDerivedFields(): array
    {
        $cases = [];
        foreach (['publication_manifest_hash' => str_repeat('7', 64), 'config_snapshot_hash' => str_repeat('8', 64), 'temporal_revision_set_hash' => str_repeat('9', 64),
            'factor_set_id' => 70055, 'canonicalization_version' => 'canon_manifest', 'formula_version' => 'formula_manifest', 'read_model_version' => 'read_manifest',
            'publication_version' => 4] as $field => $expected) {
            $cases[$field.' ('.self::RULE[$field].')'] = [$field, $expected];
        }

        return $cases;
    }

    /**
     * `MD-S075-R0053`, `R0057..R0059`, `R0062..R0064`, `R0066`, `R0075`: a publication-facing field is read
     * from the manifest of THE RUN'S OWN publication. The manifest lookup answers a decoy for every other
     * publication id, and the run record carries decoys under the same names, so a field read from the run
     * or from a sibling publication is caught.
     *
     * @dataProvider manifestDerivedFields
     */
    public function test_a_publication_facing_field_comes_from_the_manifest_of_the_runs_own_publication(string $field, $expected): void
    {
        $summary = $this->exportSummary($this->runRecord());

        $this->assertSame($expected, $summary[$field], self::RULE[$field].': '.$field.' was not read from the run\'s own publication manifest');
    }

    /**
     * `MD-S075-R0053`, `R0057..R0059`, `R0062..R0064`, `R0066`, `R0072`, `R0073`: with no resolved
     * publication nothing publication-facing is stood in. The manifest is never requested, the fields are
     * null, and the decoys on the run record are not copied.
     *
     * @dataProvider heldAndFailedRuns
     */
    public function test_a_run_without_a_resolved_publication_exports_no_publication_facing_value(array $override): void
    {
        $summary = $this->exportSummary($this->runRecord($override + ['publication_id' => null, 'publication_version' => null, 'is_current_publication' => 0, 'sealed_at' => null,
            'bars_batch_hash' => null, 'indicators_batch_hash' => null, 'eligibility_batch_hash' => null]), false);

        foreach (['publication_manifest_hash', 'config_snapshot_hash', 'temporal_revision_set_hash', 'factor_set_id', 'canonicalization_version',
            'formula_version', 'read_model_version', 'publication_version', 'sealed_at', 'bars_batch_hash', 'indicators_batch_hash', 'eligibility_batch_hash'] as $field) {
            $this->assertNull($summary[$field], self::RULE[$field].': '.$field.' carries '.json_encode($summary[$field]).' for a run that published nothing');
        }
        $this->assertFalse($summary['is_current_publication'], 'R0067: a run that published nothing is marked as the current publication');
    }

    /** @return array<string,array{0:array<string,mixed>}> */
    public function heldAndFailedRuns(): array
    {
        return [
            'held, quality gate failed' => [['terminal_status' => 'HELD', 'publishability_state' => 'NOT_READABLE', 'quality_gate_state' => 'FAIL', 'coverage_gate_state' => 'FAIL']],
            'failed at ingest' => [['terminal_status' => 'FAILED', 'publishability_state' => 'NOT_READABLE', 'quality_gate_state' => 'FAIL', 'stage' => 'INGEST', 'lifecycle_state' => 'FAILED']],
            'success but not readable' => [['terminal_status' => 'SUCCESS', 'publishability_state' => 'NOT_READABLE', 'quality_gate_state' => 'PASS']],
        ];
    }

    /**
     * `MD-S075-R0073` (and `R0071`): a held or failed requested date is never summarised as readable, and
     * the outcome fields are the persisted ones, not an interpretation of them.
     *
     * @dataProvider heldAndFailedRuns
     */
    public function test_a_held_or_failed_run_is_never_summarised_as_readable(array $override): void
    {
        $summary = $this->exportSummary($this->runRecord($override + ['publication_id' => null, 'publication_version' => null, 'is_current_publication' => 0, 'sealed_at' => null]), false);

        $this->assertSame($override['terminal_status'], $summary['terminal_status']);
        $this->assertSame($override['publishability_state'], $summary['publishability_state']);
        $this->assertSame($override['quality_gate_state'], $summary['quality_gate_state']);
        $this->assertNotSame('READABLE', $summary['publishability_state']);
        $this->assertFalse($summary['promoted'], 'a non-readable run was summarised as promoted');
        $this->assertFalse($summary['pointer_switched'], 'a non-readable run was summarised as having switched the current pointer');
        $this->assertNull($summary['current_publication_id'], 'a non-readable run named a current publication');
        $this->assertFalse($summary['is_current_publication']);
        $this->assertNull($summary['sealed_at']);
        $this->assertStringContainsString('not readable', strtolower((string) $summary['final_outcome_note']), 'the outcome note does not say the run is not readable');
    }

    /**
     * `MD-S075-R0072`: a readable success carries seal and publication evidence compatible with
     * readability — seal time, the three batch hashes, the publication manifest hash, a publication
     * version, and the current-publication marking.
     */
    public function test_a_readable_success_carries_the_seal_and_publication_evidence_it_implies(): void
    {
        $summary = $this->exportSummary($this->runRecord());

        $this->assertSame('SUCCESS', $summary['terminal_status']);
        $this->assertSame('READABLE', $summary['publishability_state']);
        foreach (['sealed_at', 'bars_batch_hash', 'indicators_batch_hash', 'eligibility_batch_hash', 'publication_manifest_hash', 'publication_version', 'observation_manifest_hash'] as $field) {
            $this->assertNotNull($summary[$field], 'R0072: a readable success has no '.$field);
        }
        $this->assertTrue($summary['is_current_publication']);
        $this->assertTrue($summary['promoted']);
        $this->assertSame(self::OWN_PUBLICATION, $summary['current_publication_id']);
    }

    /**
     * `MD-S075-R0072`, the honest side: when a run claims readability but its seal evidence is absent, the
     * summary does not manufacture the missing evidence — it exports what is persisted (null) rather than a
     * stand-in, so the incompatibility stays visible.
     */
    public function test_missing_seal_evidence_on_a_readable_claim_is_exported_as_missing(): void
    {
        $summary = $this->exportSummary($this->runRecord(['sealed_at' => null, 'bars_batch_hash' => null, 'indicators_batch_hash' => null, 'eligibility_batch_hash' => null]), false);

        foreach (['sealed_at', 'bars_batch_hash', 'indicators_batch_hash', 'eligibility_batch_hash', 'publication_manifest_hash'] as $field) {
            $this->assertNull($summary[$field], 'R0072: missing seal evidence was replaced by '.json_encode($summary[$field]).' in '.$field);
        }
    }

    // ------------------------------------------------------------------ source_context

    /** The six minimum fields of the companion block, by summary key => persisted column. */
    private const SOURCE_FIELDS = [
        'source_name' => ['source_name', 'R0035'], 'source_input_file' => ['source_input_file', 'R0036'], 'attempt_count' => ['source_attempt_count', 'R0037'],
        'success_after_retry' => ['source_success_after_retry', 'R0038'], 'final_http_status' => ['source_final_http_status', 'R0039'],
        'final_reason_code' => ['source_final_reason_code', 'R0040'],
    ];

    /**
     * `MD-S075-R0034`, `R0035..R0040`: the companion block carries the six minimum fields from the
     * persisted source columns, each from its own column.
     */
    public function test_source_context_carries_its_minimum_fields_from_the_persisted_source_columns(): void
    {
        $summary = $this->exportSummary($this->runRecord([
            'source_name' => 'SRC_NAME_COL', 'source_input_file' => 'storage/input/eod_20260421.csv', 'source_attempt_count' => 4,
            'source_success_after_retry' => 1, 'source_final_http_status' => 503, 'source_final_reason_code' => 'RUN_SOURCE_TIMEOUT',
        ]));

        $this->assertIsArray($summary['source_context'], 'R0034: source_context is not a block');
        $c = $summary['source_context'];
        $this->assertSame('SRC_NAME_COL', $c['source_name'], 'R0035');
        $this->assertStringEndsWith('eod_20260421.csv', (string) $c['source_input_file'], 'R0036');
        $this->assertSame(4, $c['attempt_count'], 'R0037');
        $this->assertSame('yes', $c['success_after_retry'], 'R0038');
        $this->assertSame(503, $c['final_http_status'], 'R0039');
        $this->assertSame('RUN_SOURCE_TIMEOUT', $c['final_reason_code'], 'R0040');

        $no = $this->exportSummary($this->runRecord(['source_success_after_retry' => 0, 'source_name' => 'X', 'source_attempt_count' => 1]));
        $this->assertSame('no', $no['source_context']['success_after_retry'], 'R0038: a false success_after_retry is not "no"');
    }

    /**
     * `MD-S075-R0076`: the block may recover minimum fields from persisted attempt telemetry when the
     * dedicated columns are thin. Recovered values are exactly what the persisted notes say.
     */
    public function test_source_context_recovers_thin_minimum_fields_from_persisted_notes_only(): void
    {
        $notes = 'source_name=NOTES_SRC; source_attempt_count=5; source_success_after_retry=yes; source_final_http_status=429; source_final_reason_code=RUN_SOURCE_RATE_LIMIT; source_input_file=notes_input.csv; source_retry_attempt_count=2';
        $summary = $this->exportSummary($this->runRecord(['notes' => $notes]));
        $c = $summary['source_context'];

        $this->assertSame('NOTES_SRC', $c['source_name']);
        $this->assertSame(5, $c['attempt_count']);
        $this->assertSame('yes', $c['success_after_retry']);
        $this->assertSame(429, $c['final_http_status']);
        $this->assertSame('RUN_SOURCE_RATE_LIMIT', $c['final_reason_code']);
        $this->assertStringEndsWith('notes_input.csv', (string) $c['source_input_file']);
        $this->assertSame(2, $c['retry_attempt_count']);
    }

    /**
     * `MD-S075-R0076`, `R0035..R0040` (condition: source telemetry exists): with no persisted source
     * telemetry the block invents nothing — the six minimum fields are null, and so is every count that
     * could only have come from telemetry. In particular a run that never recorded its retries does not
     * report zero retries.
     */
    public function test_source_context_invents_no_source_fact_the_run_never_recorded(): void
    {
        $summary = $this->exportSummary($this->runRecord(['source' => 'api', 'notes' => '']));
        $c = $summary['source_context'];

        foreach (self::SOURCE_FIELDS as $key => [$column, $rule]) {
            $this->assertNull($c[$key], $rule.': '.$key.' was invented as '.json_encode($c[$key]).' for a run with no persisted source telemetry');
        }
        $this->assertNull($c['retry_attempt_count'], 'R0076: a run that recorded no retry telemetry was reported as having made '.json_encode($c['retry_attempt_count']).' retries');
        foreach (['timeout_seconds', 'retry_max', 'failure_class_summary_count'] as $unrecorded) {
            if (array_key_exists($unrecorded, $c)) {
                $this->assertNull($c[$unrecorded], 'R0076: '.$unrecorded.' was invented');
            }
        }
        $this->assertStringNotContainsString('retry_attempt_count=', (string) ($c['source_summary'] ?? ''), 'R0076: the source summary reports an unrecorded retry count');
    }

    /**
     * The persisted source facts are the run's own: another run's telemetry in the notes of a sibling is
     * not read (the notes come from the run record under export and nothing else).
     */
    public function test_source_context_reads_only_the_exported_runs_record(): void
    {
        $a = $this->exportSummary($this->runRecord(['run_id' => 1, 'source_name' => 'RUN_A', 'source_attempt_count' => 1]));
        $b = $this->exportSummary($this->runRecord(['run_id' => 2, 'source_name' => 'RUN_B', 'source_attempt_count' => 9]));

        $this->assertSame(['RUN_A', 1], [$a['source_context']['source_name'], $a['source_context']['attempt_count']]);
        $this->assertSame(['RUN_B', 9], [$b['source_context']['source_name'], $b['source_context']['attempt_count']]);
    }
}

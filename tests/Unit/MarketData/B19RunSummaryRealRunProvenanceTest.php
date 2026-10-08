<?php

use App\Application\MarketData\Services\MarketDataEvidenceExportService;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\R0025SyntheticV2World;
use Tests\Support\UsesMarketDataMariaDb;

/**
 * `MD-B19` — `run_summary.json` of a REAL sealed run, read against the database.
 *
 * `B19RunSummaryValueProvenanceTest` proves the exporter's mapping with a run record whose every column has
 * a different value. This guard proves the other half: that on a run produced by the real pipeline —
 * real `eod_runs` row, real publication, real manifest — the summary agrees with an INDEPENDENT read of
 * those tables. The expectations here are read from `eod_runs`, `eod_publications` and `md_config_snapshots`
 * directly, not from the exporter or the manifest builder it calls.
 *
 * It does not mock a repository. The world is the one the R0025 candidate fixture is built on: it runs the
 * real pipeline once and seals a publication.
 */
class B19RunSummaryRealRunProvenanceTest extends TestCase
{
    use UsesMarketDataMariaDb;

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

    /** @return array{0:array<string,mixed>,1:array<string,mixed>,2:array<string,mixed>} summary, run row, publication row */
    private function exportRealRun(): array
    {
        $w = R0025SyntheticV2World::build();
        $dir = sys_get_temp_dir().'/md_b19_rs_real_'.uniqid('', true);
        app(MarketDataEvidenceExportService::class)->exportRunEvidence($w['run_id'], $dir);
        $summary = json_decode((string) file_get_contents($dir.'/run_summary.json'), true);
        $row = (array) DB::table('eod_runs')->where('run_id', $w['run_id'])->first();
        $publication = (array) DB::table('eod_publications')->where('run_id', $w['run_id'])->first();
        $this->assertNotSame([], $row, 'the world produced no run');
        $this->assertNotSame([], $publication, 'the world produced no publication');

        return [$summary, $row, $publication];
    }

    /** Numeric strings from the database and numbers in the JSON are the same value; booleans are 0/1. */
    private function same($expected, $actual): bool
    {
        if ($expected === null || $actual === null) {
            return $expected === $actual;
        }
        if (is_bool($actual)) {
            $actual = $actual ? 1 : 0;
        }
        if (is_numeric($expected) && is_numeric($actual)) {
            return (float) $expected === (float) $actual;
        }

        return (string) $expected === (string) $actual;
    }

    /**
     * `MD-S075-R0074`: every summary field that mirrors persisted run state uses the persisted name and
     * carries the persisted value. Checked for every key of the summary that is a column of `eod_runs` —
     * not only the contract's minimum fields — so a mirrored field that was renamed or replaced by a
     * derived value under the same name is caught. `final_reason_code` is included: on the real run the
     * persisted column is NULL and the summary says NULL; the reason the exporter resolves for operators
     * is `effective_final_reason_code` (`D-MD-B19-A001-003`, `F-MD-B19-A001-006` Option A).
     */
    public function test_every_summary_key_named_like_an_eod_runs_column_carries_that_columns_value(): void
    {
        [$summary, $row] = $this->exportRealRun();
        $columns = Schema::getColumnListing('eod_runs');
        $shared = array_values(array_intersect($columns, array_keys($summary)));

        $this->assertGreaterThan(30, count($shared), 'the summary shares too few names with eod_runs for this check to mean anything');
        foreach (['run_id', 'trade_date_requested', 'trade_date_effective', 'lifecycle_state', 'terminal_status', 'quality_gate_state', 'publishability_state', 'stage', 'source',
            'coverage_ratio', 'bars_rows_written', 'indicators_rows_written', 'eligibility_rows_written', 'invalid_bar_count', 'invalid_indicator_count', 'warning_count',
            'hard_reject_count', 'bars_batch_hash', 'indicators_batch_hash', 'eligibility_batch_hash', 'observation_manifest_hash', 'sealed_at', 'config_version',
            'config_snapshot_id', 'factor_set_hash', 'price_product_code', 'freshness_state', 'publication_version', 'is_current_publication', 'supersedes_run_id',
            'started_at', 'finished_at'] as $contractField) {
            $this->assertContains($contractField, $shared, 'R0074: the contract field '.$contractField.' is not exported under its persisted name');
        }

        $disagree = [];
        foreach ($shared as $column) {
            if (is_array($summary[$column]) || ! $this->same($row[$column], $summary[$column])) {
                $disagree[$column] = ['persisted' => $row[$column], 'summary' => $summary[$column]];
            }
        }
        $this->assertSame([], $disagree, 'summary fields that disagree with the persisted eod_runs row: '.json_encode($disagree));
    }

    /**
     * `MD-S075-R0074`, `R0075`, `R0047` on a real run: the persisted reason is mirrored (NULL here), the effective
     * reason is a different field that names the persisted column it was taken from, every manifest-derived field is
     * listed in the marker, and the never-written `warning_count` is exported as the NULL it is.
     */
    public function test_a_real_run_mirrors_the_persisted_reason_and_marks_the_derived_ones(): void
    {
        [$summary, $row] = $this->exportRealRun();

        $this->assertNull($row['final_reason_code'], 'the world no longer leaves eod_runs.final_reason_code NULL; the real-run case below is stale');
        $this->assertNull($summary['final_reason_code'], 'R0074: a derived reason is exported under the persisted name');
        $this->assertSame($row['coverage_reason_code'], $summary['effective_final_reason_code'], 'R0075: the effective reason is not the coverage reason the run recorded');
        $this->assertNotNull($summary['effective_final_reason_code']);
        $this->assertSame('coverage.coverage_reason_code', $summary['effective_final_reason_code_derived_from']);

        $this->assertNull($row['warning_count'], 'R0047: the pipeline now writes warning_count; the Option B limitation record is stale');
        $this->assertNull($summary['warning_count'], 'R0047: a persisted NULL warning_count is exported as '.json_encode($summary['warning_count']));

        foreach (['publication_manifest_hash', 'config_snapshot_hash', 'temporal_revision_set_hash', 'factor_set_id', 'canonicalization_version', 'formula_version',
            'read_model_version', 'effective_final_reason_code', 'effective_final_reason_message'] as $derived) {
            $this->assertArrayHasKey($derived, $summary['derived_companion_fields'], 'R0075: '.$derived.' is derived and not marked');
            $this->assertArrayHasKey($derived, $summary, 'R0075: the marker names '.$derived.', which the summary does not carry');
        }
        $this->assertSame([], array_values(array_intersect(array_keys($summary['derived_companion_fields']), ['final_reason_code', 'warning_count', 'run_id', 'terminal_status', 'sealed_at'])),
            'R0074: a persisted mirror is marked as derived');
    }

    /**
     * `MD-S075-R0053`, `R0057`, `R0059`, `R0062..R0064`, `R0066`, `R0075`: the publication-facing fields
     * of a real run agree with the publication row and the config snapshot read directly from the tables,
     * and the two that no column holds (temporal revision set, canonicalization) are present and equal what
     * the repository resolves for THIS run's publication.
     */
    public function test_publication_facing_fields_agree_with_the_publication_and_snapshot_rows(): void
    {
        [$summary, $row, $publication] = $this->exportRealRun();
        $snapshot = (array) DB::table('md_config_snapshots')->where('config_snapshot_id', $row['config_snapshot_id'])->first();
        $this->assertNotSame([], $snapshot, 'the run names no config snapshot');

        $this->assertSame($publication['publication_manifest_hash'], $summary['publication_manifest_hash'], 'R0053');
        $this->assertSame($snapshot['config_hash'], $summary['config_snapshot_hash'], 'R0057: not the hash of the run\'s own config snapshot');
        $this->assertTrue($this->same($publication['factor_set_id'], $summary['factor_set_id']), 'R0059');
        $this->assertSame($publication['read_model_version'], $summary['read_model_version'], 'R0064');
        $this->assertTrue($this->same($publication['publication_version'], $summary['publication_version']), 'R0066');
        $this->assertSame($publication['observation_manifest_hash'], $summary['observation_manifest_hash'], 'R0052: the run and its publication disagree about the observation manifest');

        $manifest = (array) (new EodPublicationRepository())->buildManifestByPublicationId((int) $publication['publication_id']);
        foreach (['temporal_revision_set_hash' => 'R0058', 'canonicalization_version' => 'R0062', 'formula_version' => 'R0063'] as $field => $rule) {
            $this->assertNotNull($summary[$field], $rule.': '.$field.' is null for a sealed readable run');
            $this->assertNotSame('', $summary[$field], $rule);
            $this->assertSame($manifest[$field], $summary[$field], $rule.': '.$field.' is not the value resolved for this run\'s publication');
        }
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', (string) $summary['temporal_revision_set_hash'], 'R0058: not a SHA-256 value');
    }

    /**
     * `MD-S075-R0072`, `R0071`: a real readable success carries the seal and publication evidence that
     * readability implies, and every one of those values agrees with the persisted rows.
     */
    public function test_a_real_readable_success_carries_compatible_seal_and_publication_evidence(): void
    {
        [$summary, $row, $publication] = $this->exportRealRun();

        $this->assertSame('SUCCESS', $summary['terminal_status']);
        $this->assertSame('READABLE', $summary['publishability_state']);
        foreach (['sealed_at', 'bars_batch_hash', 'indicators_batch_hash', 'eligibility_batch_hash', 'publication_manifest_hash', 'observation_manifest_hash', 'publication_version'] as $field) {
            $this->assertNotNull($summary[$field], 'R0072: a readable success has no '.$field);
        }
        $this->assertTrue($summary['is_current_publication']);
        $this->assertSame('SEALED', $publication['seal_state']);
        $this->assertSame(1, (int) $publication['is_current']);
        foreach (['bars_batch_hash', 'indicators_batch_hash', 'eligibility_batch_hash', 'sealed_at'] as $field) {
            $this->assertTrue($this->same($publication[$field], $summary[$field]), 'R0072: the summary and the sealed publication disagree about '.$field);
        }
    }

    /**
     * `MD-S075-R0076`, `R0035..R0040`: the source facts of a real run agree with the persisted source
     * columns, and the block invents no field the run did not record.
     */
    public function test_source_context_of_a_real_run_agrees_with_the_persisted_source_columns(): void
    {
        [$summary, $row] = $this->exportRealRun();
        $c = $summary['source_context'];

        $this->assertSame($row['source_name'], $c['source_name'], 'R0035');
        $this->assertSame($row['source_input_file'], $c['source_input_file'], 'R0036');
        $this->assertTrue($this->same($row['source_attempt_count'], $c['attempt_count']), 'R0037');
        $this->assertSame($row['source_success_after_retry'] === null ? null : ((int) $row['source_success_after_retry'] === 1 ? 'yes' : 'no'), $c['success_after_retry'], 'R0038');
        $this->assertTrue($this->same($row['source_final_http_status'], $c['final_http_status']), 'R0039');
        $this->assertSame($row['source_final_reason_code'], $c['final_reason_code'], 'R0040');
        $this->assertSame($row['source'], $summary['source']);
    }
}

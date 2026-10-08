<?php

use App\Application\MarketData\Services\MarketDataEvidenceExportService;
use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use App\Infrastructure\Persistence\MarketData\EodEvidenceRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use Mockery as m;
use PHPUnit\Framework\TestCase;

/**
 * `MD-B19` — owner decisions `D-MD-B19-A001-003` for `run_summary.json`:
 *
 *  - `F-MD-B19-A001-006` Option A (`MD-S075-R0074`, `R0075`): `final_reason_code` mirrors the persisted
 *    `eod_runs.final_reason_code` and nothing else; the effective reason is a separately named derived field
 *    with its provenance; every derived field is listed in `derived_companion_fields`; the consumers that
 *    need the effective reason read the effective field.
 *  - `F-MD-B19-A001-005` Option B (`MD-S075-R0047`): `warning_count` mirrors the persisted value, a persisted
 *    NULL stays NULL, and no warning population is invented. The limitation is recorded; a tripwire fails
 *    the day something starts writing the counter, because that is then a new semantic to decide.
 *
 * The run records here give every reason column a different value, so a field read from the wrong column or
 * from the fallback chain cannot coincide with the right answer.
 */
class B19RunSummaryDerivedFieldMarkingTest extends TestCase
{
    private const OWN_PUBLICATION = 1201;

    protected function tearDown(): void
    {
        m::close();
    }

    private function runRecord(array $override = []): object
    {
        return (object) array_merge([
            'run_id' => 81240, 'run_uuid' => 'uuid-81240',
            'trade_date_requested' => '2026-04-21', 'trade_date_effective' => '2026-04-20',
            'lifecycle_state' => 'COMPLETED', 'terminal_status' => 'HELD', 'quality_gate_state' => 'FAIL', 'publishability_state' => 'NOT_READABLE',
            'stage' => 'FINALIZE', 'source' => 'api', 'request_mode' => 'daily', 'coverage_gate_state' => 'FAIL', 'coverage_ratio' => '0.842000',
            'bars_rows_written' => 8421, 'indicators_rows_written' => 8302, 'eligibility_rows_written' => 10003,
            'invalid_bar_count' => 181, 'invalid_indicator_count' => 1702, 'warning_count' => 503, 'hard_reject_count' => 124,
            'publication_id' => null, 'publication_version' => null, 'is_current_publication' => 0, 'sealed_at' => null,
            'started_at' => '2026-04-21 17:01:00', 'finished_at' => '2026-04-21 17:09:30', 'notes' => '',
            'final_reason_code' => null, 'source_final_reason_code' => null, 'coverage_reason_code' => null,
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

    /**
     * Exports through the real service and returns every written JSON file plus the result array.
     *
     * @return array{result:array<string,mixed>,run_summary:array<string,mixed>,lineage:array<string,mixed>,completeness:array<string,mixed>}
     */
    private function export(object $run, bool $publicationResolves = false): array
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
            $publicationResolves ? (object) ['publication_id' => self::OWN_PUBLICATION, 'is_current' => 1, 'run_id' => $run->run_id] : null
        );
        $publications->shouldReceive('buildManifestByPublicationId')->andReturn($this->manifest());
        $corrections->shouldReceive('findByRunId')->andReturn(null);
        $evidence->shouldIgnoreMissing();
        $publications->shouldIgnoreMissing();
        $corrections->shouldIgnoreMissing();

        $service = new MarketDataEvidenceExportService($evidence, $publications, $corrections);
        $dir = sys_get_temp_dir().'/md_b19_rs_marking_'.uniqid('', true);
        $result = $service->exportRunEvidence((int) $run->run_id, $dir);
        $read = function (string $file) use ($dir) {
            $decoded = json_decode((string) file_get_contents($dir.'/'.$file), true);
            $this->assertIsArray($decoded, $file.' is not a JSON object');

            return $decoded;
        };

        return ['result' => $result, 'run_summary' => $read('run_summary.json'), 'lineage' => $read('lineage.json'), 'completeness' => $read('evidence_completeness.json')];
    }

    // ------------------------------------------------------------------ F-006 Option A: strict mirror

    /** @return array<string,array{0:array<string,mixed>,1:?string}> run overrides => expected final_reason_code */
    public function persistedFinalReasonCases(): array
    {
        return [
            'persisted value with a different source and coverage reason' => [['final_reason_code' => 'RUN_LOCK_CONFLICT', 'source_final_reason_code' => 'RUN_SOURCE_TIMEOUT', 'coverage_reason_code' => 'RUN_COVERAGE_LOW'], 'RUN_LOCK_CONFLICT'],
            'persisted NULL, source reason recorded' => [['source_final_reason_code' => 'RUN_SOURCE_TIMEOUT', 'coverage_reason_code' => 'RUN_COVERAGE_LOW'], null],
            'persisted NULL, only a coverage reason recorded' => [['coverage_reason_code' => 'COVERAGE_THRESHOLD_MET', 'coverage_gate_state' => 'PASS'], null],
            'persisted NULL, nothing recorded' => [[], null],
        ];
    }

    /**
     * `MD-S075-R0074`: `final_reason_code` is a field that mirrors persisted run state, so it carries the
     * value of `eod_runs.final_reason_code` under that name — and a persisted NULL stays NULL. It never
     * carries the source reason or the coverage reason the run recorded under other columns.
     *
     * @dataProvider persistedFinalReasonCases
     */
    public function test_final_reason_code_carries_exactly_the_persisted_column(array $override, ?string $expected): void
    {
        $summary = $this->export($this->runRecord($override))['run_summary'];

        $this->assertArrayHasKey('final_reason_code', $summary, 'R0074: final_reason_code disappeared when the persisted column is NULL');
        $this->assertSame($expected, $summary['final_reason_code'], 'R0074: final_reason_code is not the persisted eod_runs.final_reason_code');
    }

    /**
     * `MD-S075-R0074`: the message beside the persisted code describes the persisted code and nothing else —
     * with a persisted NULL there is no message, not the message of a fallback reason.
     */
    public function test_the_final_reason_message_describes_the_persisted_code_only(): void
    {
        $withValue = $this->export($this->runRecord(['final_reason_code' => 'RUN_LOCK_CONFLICT', 'source_final_reason_code' => 'RUN_SOURCE_TIMEOUT', 'coverage_reason_code' => 'RUN_COVERAGE_NOT_EVALUABLE']))['run_summary'];
        $this->assertSame('Run finalization hit a lock conflict and was held safely.', $withValue['final_reason_message']);

        $withNull = $this->export($this->runRecord(['source_final_reason_code' => 'RUN_SOURCE_TIMEOUT', 'coverage_reason_code' => 'COVERAGE_BELOW_THRESHOLD']))['run_summary'];
        $this->assertNull($withNull['final_reason_message'], 'R0074: a persisted NULL final_reason_code carries the message of a fallback reason');
    }

    // ------------------------------------------------------------------ F-006 Option A: the effective reason

    /** @return array<string,array{0:array<string,mixed>,1:?string,2:?string,3:?string}> overrides, effective code, derived_from, effective message */
    public function effectiveReasonCases(): array
    {
        return [
            'the persisted column wins' => [['final_reason_code' => 'RUN_LOCK_CONFLICT', 'source_final_reason_code' => 'RUN_SOURCE_TIMEOUT', 'coverage_reason_code' => 'RUN_COVERAGE_LOW'],
                'RUN_LOCK_CONFLICT', 'eod_runs.final_reason_code', 'Run finalization hit a lock conflict and was held safely.'],
            'the source reason when the persisted column is NULL' => [['source_final_reason_code' => 'RUN_SOURCE_TIMEOUT', 'coverage_reason_code' => 'RUN_COVERAGE_LOW'],
                'RUN_SOURCE_TIMEOUT', 'source_context.final_reason_code', 'Source acquisition timed out.'],
            'the coverage reason when nothing else is recorded' => [['coverage_reason_code' => 'COVERAGE_BELOW_THRESHOLD'],
                'COVERAGE_BELOW_THRESHOLD', 'coverage.coverage_reason_code', 'Coverage gate failed because available data is below the configured threshold.'],
            'nothing recorded anywhere' => [[], null, null, null],
        ];
    }

    /**
     * `MD-S075-R0075`: the reason the exporter resolves for operators is exposed as a separately named
     * derived field, and its provenance names the exact column or block it was taken from.
     *
     * @dataProvider effectiveReasonCases
     */
    public function test_the_effective_final_reason_is_a_separate_field_that_names_where_it_came_from(array $override, ?string $code, ?string $derivedFrom, ?string $message): void
    {
        $summary = $this->export($this->runRecord($override))['run_summary'];

        foreach (['effective_final_reason_code', 'effective_final_reason_code_derived_from', 'effective_final_reason_message'] as $key) {
            $this->assertArrayHasKey($key, $summary, 'R0075: '.$key.' is absent');
        }
        $this->assertSame($code, $summary['effective_final_reason_code'], 'R0075: the effective final reason is wrong');
        $this->assertSame($derivedFrom, $summary['effective_final_reason_code_derived_from'], 'R0075: the effective final reason does not name where it came from');
        $this->assertSame($message, $summary['effective_final_reason_message'], 'R0075: the effective final reason message does not describe the effective reason');
    }

    // ------------------------------------------------------------------ F-006 Option A: the derived-companion marker

    /** The fields the summary takes from the publication manifest (or derives from the run state and the current marking). */
    private const MANIFEST_DERIVED = [
        'publication_manifest_hash', 'config_snapshot_hash', 'temporal_revision_set_hash', 'factor_set_id', 'canonicalization_version', 'formula_version', 'read_model_version',
        'bound_input_context_schema_version', 'bound_input_context_status', 'bound_input_context_available', 'bound_input_context_hash',
        'publication_version', 'is_current_publication', 'promoted', 'pointer_switched', 'current_publication_id',
    ];

    /**
     * `MD-S075-R0075`: derived publication-facing fields appear only when clearly marked as derived companion
     * evidence. The marker lists every manifest-derived field and every effective reason field; each entry
     * names its derivation and where it is derived from; and every listed name is a real key of the summary.
     */
    public function test_every_derived_field_is_listed_in_the_derived_companion_marker(): void
    {
        $summary = $this->export($this->runRecord(), false)['run_summary'];

        $this->assertIsArray($summary['derived_companion_fields'] ?? null, 'R0075: the summary has no derived_companion_fields marker');
        $marker = $summary['derived_companion_fields'];
        foreach (array_merge(self::MANIFEST_DERIVED, ['effective_final_reason_code', 'effective_final_reason_message', 'effective_final_reason_code_derived_from', 'final_outcome_note', 'source_context']) as $field) {
            $this->assertArrayHasKey($field, $marker, 'R0075: the derived field '.$field.' is not marked');
        }
        foreach ($marker as $field => $entry) {
            $this->assertArrayHasKey($field, $summary, 'R0075: the marker lists '.$field.', which is not a key of the summary');
            $this->assertIsString($entry['derivation'] ?? null, 'R0075: '.$field.' has no derivation');
            $this->assertNotSame('', $entry['derivation']);
            $this->assertIsArray($entry['derived_from'] ?? null, 'R0075: '.$field.' does not say what it is derived from');
            $this->assertNotSame([], $entry['derived_from']);
        }
    }

    /**
     * `MD-S075-R0074`: the marker never lists a field that mirrors persisted run state as derived — a persisted
     * mirror and a derived value are different things, and a marker that blurs them hides which is which.
     */
    public function test_the_marker_does_not_list_a_pure_persisted_mirror(): void
    {
        $marker = $this->export($this->runRecord(), false)['run_summary']['derived_companion_fields'] ?? [];

        $this->assertNotSame([], $marker, 'R0075: there is no marker, so "no mirror is marked" would hold vacuously');
        foreach (['run_id', 'trade_date_requested', 'trade_date_effective', 'lifecycle_state', 'terminal_status', 'quality_gate_state', 'publishability_state', 'stage', 'source',
            'coverage_ratio', 'bars_rows_written', 'indicators_rows_written', 'eligibility_rows_written', 'invalid_bar_count', 'invalid_indicator_count', 'warning_count',
            'hard_reject_count', 'bars_batch_hash', 'indicators_batch_hash', 'eligibility_batch_hash', 'observation_manifest_hash', 'sealed_at', 'config_version', 'config_snapshot_id',
            'factor_set_hash', 'price_product_code', 'freshness_state', 'supersedes_run_id', 'started_at', 'finished_at', 'final_reason_code'] as $mirror) {
            $this->assertArrayNotHasKey($mirror, $marker, 'R0074: the persisted mirror '.$mirror.' is marked as derived');
        }
    }

    /**
     * `MD-S075-R0075`: a marker that lists a field still has to be true. A manifest-derived field marked as
     * derived from the manifest must change when the manifest changes and not when the run record changes.
     */
    public function test_a_marked_manifest_field_follows_the_manifest_and_not_the_run_record(): void
    {
        $run = $this->runRecord(['terminal_status' => 'SUCCESS', 'quality_gate_state' => 'PASS', 'publishability_state' => 'READABLE', 'coverage_gate_state' => 'PASS', 'publication_id' => self::OWN_PUBLICATION,
            'publication_version' => 9, 'is_current_publication' => 0, 'publication_manifest_hash' => 'DECOY_RUN', 'config_snapshot_hash' => 'DECOY_RUN']);
        $summary = $this->export($run, true)['run_summary'];

        $this->assertSame(str_repeat('7', 64), $summary['publication_manifest_hash']);
        $this->assertSame(str_repeat('8', 64), $summary['config_snapshot_hash']);
        $this->assertSame(['publication_manifest.publication_manifest_hash'], $summary['derived_companion_fields']['publication_manifest_hash']['derived_from'],
            'R0075: publication_manifest_hash is marked as derived from something other than the publication manifest');
        $this->assertSame(['publication_manifest.config_snapshot_hash'], $summary['derived_companion_fields']['config_snapshot_hash']['derived_from']);
    }

    // ------------------------------------------------------------------ F-006 Option A: consumers

    /**
     * Consumers audited under `D-MD-B19-A001-003`: the export result shown to the operator, the lineage's
     * finalize decision and the completeness check keep their intended behaviour by reading the EFFECTIVE
     * reason, and each states which value it carries. The persisted value is never relabelled.
     */
    public function test_the_export_result_reports_the_persisted_and_the_effective_reason_under_their_own_names(): void
    {
        $out = $this->export($this->runRecord(['source_final_reason_code' => 'RUN_SOURCE_TIMEOUT']));

        $summary = $out['result']['summary'];
        $this->assertArrayHasKey('final_reason_code', $summary);
        $this->assertNull($summary['final_reason_code'], 'the operator summary relabels a derived reason as the persisted final_reason_code');
        $this->assertArrayHasKey('effective_final_reason_code', $summary, 'the operator summary lost the effective reason');
        $this->assertSame('RUN_SOURCE_TIMEOUT', $summary['effective_final_reason_code']);
        $this->assertSame('source_context.final_reason_code', $summary['effective_final_reason_code_derived_from'] ?? null);
    }

    public function test_the_lineage_finalize_decision_separates_the_persisted_and_the_effective_reason(): void
    {
        $decision = $this->export($this->runRecord(['coverage_reason_code' => 'COVERAGE_BELOW_THRESHOLD']))['lineage']['run_to_finalize_decision'];

        $this->assertNull($decision['final_reason_code'], 'the lineage relabels a derived reason as the persisted final_reason_code');
        $this->assertSame('COVERAGE_BELOW_THRESHOLD', $decision['effective_final_reason_code'] ?? null);
        $this->assertSame('coverage.coverage_reason_code', $decision['effective_final_reason_code_derived_from'] ?? null);

        $persisted = $this->export($this->runRecord(['final_reason_code' => 'RUN_LOCK_CONFLICT', 'coverage_reason_code' => 'COVERAGE_BELOW_THRESHOLD']))['lineage']['run_to_finalize_decision'];
        $this->assertSame('RUN_LOCK_CONFLICT', $persisted['final_reason_code']);
        $this->assertSame('RUN_LOCK_CONFLICT', $persisted['effective_final_reason_code'] ?? null);
    }

    /**
     * The completeness check asks whether the run has a resolvable reason. A run whose only recorded reason
     * is a coverage reason still has one (the intended behaviour before the change), and a run that recorded
     * none is still reported as missing it — the strict mirror must not make the check fail for the first
     * and must not make it pass for the second.
     */
    public function test_the_completeness_check_still_finds_a_reason_the_run_recorded_only_as_a_coverage_reason(): void
    {
        $withCoverageReason = $this->export($this->runRecord(['coverage_reason_code' => 'COVERAGE_BELOW_THRESHOLD']))['completeness'];
        $this->assertNotContains('reason_code_context', $withCoverageReason['missing_sections'], 'a run with a recorded coverage reason is reported as having no reason');

        $withSourceReason = $this->export($this->runRecord(['source_final_reason_code' => 'RUN_SOURCE_TIMEOUT']))['completeness'];
        $this->assertNotContains('reason_code_context', $withSourceReason['missing_sections'], 'a run with a recorded source reason is reported as having no reason');

        $withNothing = $this->export($this->runRecord())['completeness'];
        $this->assertContains('reason_code_context', $withNothing['missing_sections'], 'a run that recorded no reason at all is reported as having one');
    }

    /**
     * The outcome note of a run that is not readable names the reason the run is not readable. It keeps naming the
     * effective reason — the coverage reason the run recorded — not the reason the gate state would suggest and
     * not the persisted column, which is NULL here.
     */
    public function test_the_outcome_note_of_a_run_that_is_not_readable_names_the_effective_reason(): void
    {
        $summary = $this->export($this->runRecord(['coverage_gate_state' => 'FAIL', 'coverage_reason_code' => 'RUN_COVERAGE_LOW']))['run_summary'];

        $this->assertNull($summary['final_reason_code']);
        $this->assertStringEndsWith('reason_code=RUN_COVERAGE_LOW', (string) $summary['final_outcome_note'], 'the outcome note does not name the effective reason');
    }

    // ------------------------------------------------------------------ F-005 Option B: warning_count

    /** @return array<string,array{0:mixed,1:?int}> */
    public function warningCounts(): array
    {
        return ['a persisted count' => [503, 503], 'a persisted zero' => [0, 0], 'a persisted string count' => ['12', 12], 'a persisted NULL' => [null, null]];
    }

    /**
     * `MD-S075-R0047` (Option B): `warning_count` is the persisted `eod_runs.warning_count` under its
     * persisted name. A persisted NULL is exported as NULL — never as 0, never as a count of something the
     * contract does not define as a warning — and a persisted number is exported as that number.
     *
     * @dataProvider warningCounts
     */
    public function test_warning_count_is_the_persisted_value_and_a_null_is_not_turned_into_a_count($persisted, ?int $expected): void
    {
        $summary = $this->export($this->runRecord(['warning_count' => $persisted, 'invalid_bar_count' => 181, 'hard_reject_count' => 124]))['run_summary'];

        $this->assertArrayHasKey('warning_count', $summary, 'R0047: warning_count is not exported');
        $this->assertSame($expected, $summary['warning_count'], 'R0047: warning_count is not the persisted value');
        $this->assertArrayNotHasKey('warning_count', $summary['derived_companion_fields'] ?? [], 'R0047: a persisted mirror is marked as derived');
    }

    /**
     * The accepted limitation of Option B, as a tripwire. Nothing in `app/` writes `eod_runs.warning_count`:
     * the repository initialises it to NULL when it creates a run and no code ever sets it. If a file other
     * than the ones listed below starts to mention `warning_count`, somebody has begun to count warnings and
     * the limitation record (`F-MD-B19-A001-005`, `D-MD-B19-A001-003`) is stale — this test then fails so
     * that the owner defines the population and the contract is revisited, instead of a counter appearing
     * under a name whose meaning nobody decided.
     */
    public function test_the_known_limitation_nothing_writes_warning_count_still_holds(): void
    {
        $root = dirname(__DIR__, 3).'/app';
        $files = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if ($file->getExtension() === 'php' && strpos((string) file_get_contents($file->getPathname()), 'warning_count') !== false) {
                $files[] = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
            }
        }
        sort($files);

        $this->assertSame([
            'Application/MarketData/Services/MarketDataEvidenceExportService.php', // mirrors eod_runs.warning_count (run summary) and the replay metric row
            'Application/MarketData/Services/ReplayVerificationService.php',       // reads it for the replay comparison; never writes eod_runs
            'Domain/MarketData/MarketDataScope.php',                               // DateTime::getLastErrors() key -- unrelated
            'Infrastructure/Persistence/MarketData/EodRunRepository.php',          // initialises to null on run creation (two creation paths)
            'Infrastructure/Persistence/MarketData/ReplayResultRepository.php',    // replay metric row, not eod_runs
            'Models/EodRun.php',                                                   // cast only
        ], $files, 'F-MD-B19-A001-005 accepted limitation: a new file mentions warning_count -- the warning population must be decided before it is written');

        $repo = (string) file_get_contents($root.'/Infrastructure/Persistence/MarketData/EodRunRepository.php');
        $this->assertSame(2, substr_count($repo, "'warning_count' => null,"), 'the run repository no longer initialises warning_count to NULL');
    }
}

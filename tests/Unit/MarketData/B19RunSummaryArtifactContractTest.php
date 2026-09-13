<?php

use App\Application\MarketData\Services\MarketDataEvidenceExportService;
use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use App\Infrastructure\Persistence\MarketData\EodEvidenceRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use Mockery as m;
use PHPUnit\Framework\TestCase;

/**
 * `MD-B19` — `run_summary.json` carries the minimum field set its contract names.
 *
 * `Run_Artifacts_Format_LOCKED.md` (`MD-S075`) states the minimum shape as a JSON block under
 * "A conforming summary should contain at minimum:", and its corrected-strategy binding rule
 * requires every artifact to expose the V2 semantic bindings applicable to its scope — the
 * immutable observation-manifest hash, the full config snapshot identity, the temporal revision
 * set, the factor set, and the price product.
 *
 * The expected field list is parsed from that contract rather than copied into this file. A copied
 * list ages silently: `TermOwnershipAndPriceProductTest` carried 20 of 33 registered terms for
 * months and nobody noticed. Parsing binds the guard to the authority it claims to enforce.
 *
 * This runs the real exporter and reads the file it writes. A guard that inspected the service's
 * source for field names would pass on a service that mentions them in a comment.
 */
class B19RunSummaryArtifactContractTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    private function root(): string
    {
        return dirname(__DIR__, 3);
    }

    /**
     * The minimum `run_summary.json` shape, read from `MD-S075`.
     *
     * @return array{top:array<int,string>,source_context:array<int,string>}
     */
    private function contractMinimumFields(): array
    {
        $path = $this->root().'/docs/market_data/authority/strategy/ops/Run_Artifacts_Format_LOCKED.md';
        $this->assertFileExists($path, 'the owner contract must exist for this guard to mean anything');
        $lines = explode("\n", (string) file_get_contents($path));

        $start = null;
        foreach ($lines as $i => $line) {
            if (trim($line) === 'A conforming summary should contain at minimum:') {
                $start = $i;
                break;
            }
        }
        $this->assertNotNull($start, 'the run_summary minimum-field block is no longer in the contract');

        $top = [];
        $nested = [];
        $depth = 0;
        for ($i = $start; $i < count($lines); $i++) {
            $t = trim($lines[$i]);
            if ($t === '' && $depth === 0) {
                continue;
            }
            if (preg_match('/^"([A-Za-z0-9_]+)"\s*:/', $t, $m) === 1) {
                if ($depth === 1) {
                    $top[] = $m[1];
                } elseif ($depth === 2) {
                    $nested[] = $m[1];
                }
            }
            $depth += substr_count($t, '{') + substr_count($t, '[')
                - substr_count($t, '}') - substr_count($t, ']');
            if ($depth === 0 && $top !== []) {
                break;
            }
        }

        // A block that parsed to nothing would let this guard report a clean artifact.
        $this->assertGreaterThan(30, count($top), 'the parsed minimum-field set is implausibly small');
        $this->assertNotEmpty($nested, 'the source_context companion block is no longer parsed');

        return ['top' => $top, 'source_context' => $nested];
    }

    /** A run carrying every column the persisted schema actually holds for these bindings. */
    private function runRecord()
    {
        return (object) [
            'run_id' => 8124,
            'run_uuid' => 'run-uuid-8124',
            'trade_date_requested' => '2026-04-21',
            'trade_date_effective' => '2026-04-21',
            'lifecycle_state' => 'COMPLETED',
            'terminal_status' => 'SUCCESS',
            'quality_gate_state' => 'PASS',
            'publishability_state' => 'READABLE',
            'stage' => 'FINALIZE',
            'source' => 'manual_file',
            'request_mode' => 'daily',
            'coverage_ratio' => 1.0,
            'coverage_min_threshold' => 0.98,
            'coverage_gate_state' => 'PASS',
            'bars_rows_written' => 842,
            'indicators_rows_written' => 830,
            'eligibility_rows_written' => 1000,
            'invalid_bar_count' => 18,
            'invalid_indicator_count' => 170,
            'warning_count' => 50,
            'hard_reject_count' => 12,
            'bars_batch_hash' => str_repeat('1', 64),
            'indicators_batch_hash' => str_repeat('2', 64),
            'eligibility_batch_hash' => str_repeat('3', 64),
            'sealed_at' => '2026-04-21 18:00:00',
            'config_version' => 'cfg_2026_04',
            'config_hash' => str_repeat('4', 64),
            // -- persisted on eod_runs and named by the contract
            'observation_manifest_hash' => str_repeat('5', 64),
            'config_snapshot_id' => 9001,
            'factor_set_hash' => str_repeat('6', 64),
            'price_product_code' => 'STRUCTURAL_ADJUSTED',
            'freshness_state' => 'ON_TARGET',
            'publication_id' => 1201,
            'publication_version' => 2,
            'is_current_publication' => true,
            'supersedes_run_id' => null,
            'started_at' => '2026-04-21 17:01:00',
            'finished_at' => '2026-04-21 17:09:30',
            'notes' => '',
        ];
    }

    /**
     * @return array{0:MarketDataEvidenceExportService,1:string}
     */
    private function exporter()
    {
        $evidence = m::mock(EodEvidenceRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $corrections = m::mock(EodCorrectionRepository::class);

        $evidence->shouldReceive('findRunById')->andReturn($this->runRecord());
        $evidence->shouldReceive('summarizeRunEvents')->andReturn([]);
        $evidence->shouldReceive('dominantReasonCodesForEvidencePublication')->andReturn([]);
        $evidence->shouldReceive('exportEligibilityRowsForEvidencePublication')->andReturn([]);
        $evidence->shouldReceive('exportInvalidBarsRows')->andReturn([]);
        $publications->shouldReceive('buildManifestByPublicationId')->andReturn([
            'publication_id' => 1201,
            'publication_manifest_hash' => str_repeat('7', 64),
            'config_snapshot_hash' => str_repeat('8', 64),
            'temporal_revision_set_hash' => str_repeat('9', 64),
            'factor_set_id' => 7001,
            'canonicalization_version' => 'canonical_v2',
            'formula_version' => 'weekly_swing_eod_v2',
            'read_model_version' => 'weekly_swing_read_v2',
            'is_current' => true,
            'publication_version' => 2,
        ]);
        $evidence->shouldReceive('resolvePublicationForEvidenceAudit')->andReturn(null);
        $corrections->shouldReceive('findByRunId')->andReturn(null);
        // Any further collaborator the export path reaches is stubbed to a neutral value; this
        // guard is about the shape of the artifact, not about what the repositories return.
        $evidence->shouldIgnoreMissing();
        $publications->shouldIgnoreMissing();
        $corrections->shouldIgnoreMissing();

        $service = new MarketDataEvidenceExportService($evidence, $publications, $corrections);
        $dir = sys_get_temp_dir().'/md_b19_run_summary_'.uniqid();

        return [$service, $dir];
    }

    /**
     * Every minimum field the contract names is present in the artifact the exporter writes.
     *
     * Twelve were absent when this guard first ran. Five were persisted on `eod_runs` and merely
     * unemitted. The other seven were assembled by `buildManifestByPublicationId()` and simply not
     * carried across — including `temporal_revision_set_hash`, which the repository composes from
     * the identity, calendar and status revision sets through the deterministic canonical-document
     * hash. An earlier reading of this gap recorded those seven as unpersisted; that was wrong, and
     * it was wrong because a schema-column search and a literal grep of the service were treated as
     * a survey of where a value can come from.
     */
    public function test_the_exported_run_summary_carries_every_minimum_field_the_contract_names(): void
    {
        $contract = $this->contractMinimumFields();
        [$service, $dir] = $this->exporter();

        $service->exportRunEvidence(8124, $dir);

        $file = $dir.'/run_summary.json';
        $this->assertFileExists($file, 'the exporter did not write run_summary.json');
        $summary = json_decode((string) file_get_contents($file), true);
        $this->assertIsArray($summary, 'run_summary.json is not readable JSON');

        $missing = [];
        foreach ($contract['top'] as $field) {
            if (! array_key_exists($field, $summary)) {
                $missing[] = $field;
            }
        }
        sort($missing);

        $this->assertSame(
            [],
            $missing,
            'run_summary.json omits minimum fields the contract names: '.implode(', ', $missing)
        );

        // The five remediated in this attempt must stay emitted.
        foreach (['observation_manifest_hash', 'config_snapshot_id', 'factor_set_hash',
            'price_product_code', 'freshness_state'] as $remediated) {
            $this->assertArrayHasKey(
                $remediated,
                $summary,
                $remediated.' was emitted by this attempt and has been dropped again'
            );
        }
    }

    /**
     * The check above must be able to see an omission. Without this, a parse that returned an empty
     * field list would report a conforming artifact and nobody would know.
     */
    public function test_the_missing_field_check_detects_an_omission(): void
    {
        $contract = $this->contractMinimumFields();
        $this->assertNotEmpty($contract['top']);

        $summary = array_fill_keys($contract['top'], null);
        unset($summary[$contract['top'][0]]);

        $missing = [];
        foreach ($contract['top'] as $field) {
            if (! array_key_exists($field, $summary)) {
                $missing[] = $field;
            }
        }

        $this->assertSame([$contract['top'][0]], $missing,
            'the omission check cannot see a field that was removed');
    }
    /**
     * A held requested date must not be summarised as readable.
     *
     * `MD-S075`'s locked rules state it twice over: the summary must reflect the actual persisted
     * run outcome rather than operator interpretation, and if the requested date is held or failed
     * the summary must not imply it is readable. This is the fail-closed side of the family — the
     * field list above proves the artifact is complete, and this proves a complete artifact still
     * cannot tell a story the run did not have.
     */
    public function test_a_held_run_is_not_summarised_as_readable(): void
    {
        $run = $this->runRecord();
        $run->terminal_status = 'HELD';
        $run->publishability_state = 'NOT_READABLE';
        $run->quality_gate_state = 'FAIL';
        $run->sealed_at = null;
        $run->is_current_publication = false;

        $evidence = m::mock(EodEvidenceRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $corrections = m::mock(EodCorrectionRepository::class);
        $evidence->shouldReceive('findRunById')->andReturn($run);
        $evidence->shouldReceive('summarizeRunEvents')->andReturn([]);
        $evidence->shouldReceive('dominantReasonCodesForEvidencePublication')->andReturn([]);
        $evidence->shouldReceive('exportEligibilityRowsForEvidencePublication')->andReturn([]);
        $evidence->shouldReceive('exportInvalidBarsRows')->andReturn([]);
        $evidence->shouldReceive('resolvePublicationForEvidenceAudit')->andReturn(null);
        $publications->shouldReceive('buildManifestByPublicationId')->andReturn(null);
        $corrections->shouldReceive('findByRunId')->andReturn(null);
        $evidence->shouldIgnoreMissing();
        $publications->shouldIgnoreMissing();
        $corrections->shouldIgnoreMissing();

        $service = new MarketDataEvidenceExportService($evidence, $publications, $corrections);
        $dir = sys_get_temp_dir().'/md_b19_held_'.uniqid();
        $service->exportRunEvidence(8124, $dir);

        $summary = json_decode((string) file_get_contents($dir.'/run_summary.json'), true);
        $this->assertIsArray($summary);

        $this->assertSame('HELD', $summary['terminal_status']);
        $this->assertNotSame('READABLE', $summary['publishability_state'],
            'a held run was summarised as readable');
        $this->assertFalse((bool) $summary['promoted'],
            'a held run was summarised as promoted');
        $this->assertFalse((bool) $summary['pointer_switched'],
            'a held run was summarised as having switched the current pointer');
        $this->assertNull($summary['current_publication_id'],
            'a held run named a current publication');
        $this->assertNull($summary['sealed_at'],
            'a held run carried a seal timestamp');
    }
}

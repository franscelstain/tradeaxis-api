<?php

use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Infrastructure\Persistence\MarketData\ProducerInputScope;
use App\Infrastructure\Persistence\MarketData\ProducerSourceObservationPopulation;
use App\Infrastructure\Persistence\MarketData\RunInputCaptureRepository;
use App\Infrastructure\Persistence\MarketData\SourceObservationRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

class B18ProducerSourceObservationPopulationTest extends TestCase
{
    use UsesMarketDataSqlite;
    private $source;
    protected function setUp(): void { parent::setUp(); $this->bootMarketDataSqlite(); \Carbon\Carbon::setTestNow('2026-03-25 10:30:00'); $this->source = new SourceObservationRepository(); }
    protected function tearDown(): void { \Carbon\Carbon::setTestNow(); $this->tearDownMarketDataSqlite(); parent::tearDown(); }
    private function runContext() { return (new EodRunRepository())->getOrCreateOwningRun('2026-03-24', 'api', 'INGEST_BARS', null, 'c06-population'); }
    private function envelope(): array { return ['acquisition_batch_id' => 501, 'attempt_uid' => 'c06-population', 'requested_trade_date' => '2026-03-24', 'source_mode' => 'api', 'source_name' => 'fixture', 'provider' => 'fixture', 'provider_symbol' => 'ALPHA', 'sanitized_request_identity' => 'fixture:ALPHA', 'adapter_version' => 'v1', 'payload' => '{"rows":[]}', 'acquired_at' => '2026-03-24 17:00:00']; }
    private function row(array $overrides = []): array { return array_merge(['listing_id' => 9001, 'provider_mapping_id' => 7001, 'mapping_revision' => 'fixture-v1', 'ticker_code' => 'ALPHA', 'trade_date' => '2026-03-24', 'open' => 100, 'high' => 110, 'low' => 90, 'close' => 105, 'volume' => 1000, 'source_row_ref' => 'row:1'], $overrides); }
    private function accepted(array $overrides = [], bool $bind = true): array
    {
        $row = $this->row($overrides); $out = $this->source->recordAcceptedRows($this->source->capture($this->envelope()), [$row]);
        if ($bind) $this->source->bindResolvedIdentity($out['source_observation_id'], $row['source_row_ref'], ['listing_id' => 9001, 'provider_mapping_id' => 7001, 'mapping_revision' => 'fixture-v1', 'trade_date' => '2026-03-24']);
        return $out;
    }
    private function scoped($run, callable $read, string $operation = 'c06-population/v1')
    {
        $repo = new class extends RunInputCaptureRepository { public function captureRegistryVersions($run): array { return []; } };
        return ProducerInputScope::during($run, 'INGEST_BARS', $operation, $read, $repo);
    }
    private function payloads($run, $kind = null): array
    {
        $repo = new RunInputCaptureRepository(); $out = [];
        foreach ($repo->forRun((int) $run->run_id) as $row) {
            $p = $repo->verify($row);
            if ($p['selection_context']['operation'] === 'source-observation-outcomes/v1' && ($kind === null || $p['selection_context']['read_kind'] === $kind)) $out[] = $p;
        }
        $this->assertNotEmpty($out); return $out;
    }
    public function test_row_population_preserves_rejected_members_and_comparison_ancestry(): void
    {
        $prior = $this->accepted(); $run = $this->runContext();
        $outcome = $this->scoped($run, function () { return $this->source->recordAcceptedRows($this->source->capture($this->envelope()), [$this->row(['close' => 106])], [$this->row(['source_row_ref' => 'bad:2', 'invalid_reason_code' => 'BAR_INVALID_SOURCE_ROW'])]); });
        $p = $this->payloads($run, 'outcomeRows')[0]; $tables = $p['rows'][0]['population']['tables'];
        $this->assertCount(4, $tables['md_source_observations']); $this->assertCount(2, $tables['md_source_observation_rows']);
        $this->assertCount(1, $tables['md_source_observation_rejected_rows']); $this->assertCount(1, $tables['md_source_observation_revision_comparisons']);
        $this->assertSame((int) $prior['source_observation_id'], (int) $tables['md_source_observation_revision_comparisons'][0]['prior_source_observation_id']);
        $this->assertSame('OPEN_DIVERGENCE', $tables['md_source_observation_revision_comparisons'][0]['comparison_state']);
        $this->assertSame('BAR_INVALID_SOURCE_ROW', $p['rows'][0]['evaluation']['omitted_rows'][0]['basis']);
        foreach ($tables as $table => $rows) foreach ($rows as $row) {
            $key = ProducerSourceObservationPopulation::TABLE_KEYS[$table];
            $stored = (array) DB::table($table)->where($key, $row[$key])->first();
            $this->assertSame(RunInputCaptureRepository::canonicalJson($stored), RunInputCaptureRepository::canonicalJson($row));
        }
    }
    public function test_exists_accepted_uses_captured_full_rows_and_explicit_omissions(): void
    {
        $outcome = $this->source->recordAcceptedRows($this->source->capture($this->envelope()), [$this->row(), $this->row(['source_row_ref' => 'row:2'])]);
        $this->source->bindResolvedIdentity($outcome['source_observation_id'], 'row:1', ['listing_id' => 9001, 'provider_mapping_id' => 7001, 'mapping_revision' => 'fixture-v1', 'trade_date' => '2026-03-24']);
        $run = $this->runContext();
        foreach (['row:1', 'row:2', 'absent', null] as $ref) {
            $expected = $this->source->existsAccepted($outcome['source_observation_id'], $ref);
            $actual = $this->scoped($run, function () use ($outcome, $ref) { return $this->source->existsAccepted($outcome['source_observation_id'], $ref); }, 'read-'.($ref ?? 'all'));
            $this->assertSame($expected, $actual);
        }
        foreach ($this->payloads($run) as $p) {
            $this->assertCount(2, $p['rows'][0]['population']['tables']['md_source_observation_rows']);
            ProducerSourceObservationPopulation::assertValid($p['rows'][0], $p['selection_context']);
        }
    }
    public function test_explicit_manifest_is_identical_and_content_bound_with_prior_outcomes(): void
    {
        $old = $this->accepted(); $new = $this->accepted(['close' => 106]); $run = $this->runContext();
        $ids = [(int) $new['source_observation_id']]; $expected = $this->source->manifestHashForObservationIds($ids);
        $actual = $this->scoped($run, function () use ($ids) { return $this->source->manifestHashForObservationIds($ids); });
        $this->assertSame($expected, $actual); $p = $this->payloads($run)[0];
        $this->assertCount(4, $p['rows'][0]['population']['tables']['md_source_observations']);
        $this->assertSame($ids, $p['rows'][0]['evaluation']['selected_ids']);
    }
    public function test_one_changed_rejected_member_conflicts_without_overwriting_capture(): void
    {
        $capture = $this->source->capture($this->envelope()); $out = $this->source->recordRejectedRows($capture, [$this->row(['invalid_reason_code' => 'BAR_INVALID_SOURCE_ROW'])], 'SOURCE_SCHEMA_INVALID');
        $run = $this->runContext(); $read = function () use ($out) { return $this->source->manifestHashForObservationIds([$out['source_observation_id']]); };
        $this->scoped($run, $read); $before = $this->payloads($run);
        DB::table('md_source_observation_rejected_rows')->update(['reason_code' => 'DAMAGED']);
        try { $this->scoped($run, $read); $this->fail('Conflict required'); }
        catch (\RuntimeException $e) { $this->assertStringContainsString('INPUT_CAPTURE_CONFLICT', $e->getMessage()); }
        $this->assertSame($before, $this->payloads($run));
    }
    public function test_validator_rejects_single_comparison_value_and_selected_result_damage(): void
    {
        $this->accepted(); $out = $this->accepted(['close' => 106]); $run = $this->runContext();
        $this->scoped($run, function () use ($out) { return $this->source->manifestHashForObservationIds([$out['source_observation_id']]); });
        $p = $this->payloads($run)[0];
        foreach (['comparison','selection'] as $mutation) {
            $bad = $p['rows'][0];
            if ($mutation === 'comparison') $bad['population']['tables']['md_source_observation_revision_comparisons'][0]['prior_values_json'] = '{}';
            else $bad['evaluation']['result'] = 'forged';
            try { ProducerSourceObservationPopulation::assertValid($bad, $p['selection_context']); $this->fail('Damaged content accepted'); }
            catch (\RuntimeException $e) { $this->assertStringContainsString('INPUT_CAPTURE_OBSERVATION_', $e->getMessage()); }
        }
    }

    public function test_as_known_manifest_and_recovered_rows_match_original_queries_with_explicit_omissions(): void
    {
        $this->accepted(); $unbound = $this->accepted(['source_row_ref' => 'unbound'], false);
        $future = $this->source->capture(array_merge($this->envelope(), ['acquired_at' => '2027-01-01 00:00:00']));
        $futureRows = $this->accepted(['source_row_ref' => 'future']);
        DB::table('md_source_observations')->whereIn('source_observation_id', [$futureRows['source_observation_id'], $futureRows['parent_observation_id']])->update(['acquired_at' => '2027-01-01 00:00:00']);
        $run = $this->runContext(); $cutoff = (string) $run->knowledge_cutoff_at;
        foreach (['normalizedRowsAsKnown', 'observationManifestAsKnown', 'acquisitionRowsAsKnown', 'normalizedRowsManifestAsKnown'] as $method) {
            $expected = $this->source->$method('2026-03-24', $cutoff);
            $actual = $this->scoped($run, function () use ($method, $cutoff) { return $this->source->$method('2026-03-24', $cutoff); }, $method);
            $this->assertSame($expected, $actual, $method);
        }
        $p = $this->payloads($run, 'normalizedAsKnown')[0];
        $this->assertContains('NO_IDENTITY_BINDING_KNOWN_AT_CUTOFF', array_column($p['rows'][0]['evaluation']['omitted_rows'], 'basis'));
        $p = $this->payloads($run, 'manifestAsKnown')[0];
        $this->assertContains('OBSERVATION_AFTER_KNOWLEDGE_CUTOFF', array_column($p['rows'][0]['evaluation']['omitted_rows'], 'basis'));
    }

    public function test_prior_population_contains_all_candidates_and_selected_highest_row_id(): void
    {
        $this->accepted(); $prior = $this->accepted(['close' => 106]); $run = $this->runContext();
        $this->scoped($run, function () { $this->accepted(['close' => 107], false); });
        $p = $this->payloads($run, 'priorRow')[0];
        $this->assertCount(2, $p['selection_context']['candidate_row_ids']);
        $this->assertSame((int) $prior['source_observation_id'], (int) $p['rows'][0]['evaluation']['result']['source_observation_id']);
        $this->assertCount(1, $p['rows'][0]['evaluation']['omitted_rows']);
        $this->assertSame('LOWER_PERSISTED_ROW_ID_THAN_SELECTED_PRIOR', $p['rows'][0]['evaluation']['omitted_rows'][0]['basis']);
    }

    public function test_missing_root_is_explicit_negative_read_and_dangling_ancestor_fails_closed(): void
    {
        $run = $this->runContext();
        $this->assertFalse($this->scoped($run, function () { return $this->source->existsAccepted(99999, 'absent'); }));
        $p = $this->payloads($run)[0]; $this->assertSame([99999], $p['rows'][0]['population']['missing_root_ids']);
        $out = $this->accepted(); DB::table('md_source_observations')->where('source_observation_id', $out['parent_observation_id'])->delete();
        $this->expectExceptionMessage('INPUT_CAPTURE_OBSERVATION_ANCESTRY_MISSING');
        $this->scoped($run, function () use ($out) { return $this->source->existsAccepted($out['source_observation_id']); }, 'dangling');
    }

    public function test_ingest_partition_cannot_omit_one_input_or_its_omission_reason(): void
    {
        $out = $this->accepted(); $run = $this->runContext();
        $row = $this->row() + ['source_observation_id' => (int) $out['source_observation_id']];
        foreach (['membership','reason'] as $mutation) {
            try {
                $this->scoped($run, static function () use ($row, $mutation, $out) {
                    ProducerSourceObservationPopulation::consume('ingestSelection', ['input_rows' => [$row], 'selected_rows' => [], 'omitted_rows' => $mutation === 'membership' ? [] : [$row]], [(int) $out['source_observation_id']]);
                }, $mutation);
                $this->fail('Incomplete selection must not pass');
            } catch (\RuntimeException $e) { $this->assertStringContainsString('INPUT_CAPTURE_OBSERVATION_', $e->getMessage()); }
        }
    }

    public function test_completion_requires_ingress_content_and_the_acceptance_read_even_if_capsules_exist(): void
    {
        $out = $this->accepted(); $run = $this->runContext(); $repo = new RunInputCaptureRepository();
        $row = $this->row() + ['source_observation_id' => (int) $out['source_observation_id']];
        $this->scoped($run, function () use ($row, $out) {
            $ids = [(int) $out['source_observation_id']];
            ProducerSourceObservationPopulation::consume('incomingRows', ['input_rows' => [$row]], $ids);
            ProducerSourceObservationPopulation::consume('ingestSelection', ['input_rows' => [$row], 'selected_rows' => [$row], 'omitted_rows' => []], $ids);
            $this->source->existsAccepted($out['source_observation_id'], 'row:1');
            $this->source->manifestHashForObservationIds($ids);
        }, 'ingestAcquiredRows/v1');
        $repo->captureForProducer($run, 'INGEST_BARS', 'source_observations', 'ingest-source-rows/v1', [$row], ['input_route' => 'ingestAcquiredRows']);
        $validator = new \App\Infrastructure\Persistence\MarketData\ProducerSourceObservationCompleteness();
        $all = $repo->forRun((int) $run->run_id); $this->assertSame([], $validator->missing($all, $repo));
        $this->assertContains('source_observations.no_producer_ingress_population', $validator->missing([], $repo));
        foreach (['existsAccepted' => 'acceptance_read.', 'manifestIds' => 'manifest_provenance.', 'incomingRows' => 'ingress_provenance.', 'ingestSelection' => 'ingest_selection.'] as $kind => $gap) {
            $damaged = array_values(array_filter($all, static function ($r) use ($repo, $kind) { return ($repo->verify($r)['selection_context']['read_kind'] ?? '') !== $kind; }));
            $missing = $validator->missing($damaged, $repo);
            $this->assertNotEmpty(array_filter($missing, static function ($s) use ($gap) { return strpos($s, 'source_observations.'.$gap) === 0; }), $kind.' must remain mandatory independently of scope hashes');
        }
    }

    public function test_manifest_for_explicit_run_retains_rejected_and_failure_populations(): void
    {
        $run = $this->runContext(); $envelope = $this->envelope() + ['run_id' => (int) $run->run_id];
        $this->source->recordRejectedRows($this->source->capture($envelope), [$this->row(['invalid_reason_code' => 'BAR_INVALID_SOURCE_ROW'])], 'SOURCE_SCHEMA_INVALID');
        $this->source->recordTransportFailure($envelope, 'SOURCE_TIMEOUT');
        $expected = $this->source->manifestHashForRun($run->run_id);
        $actual = $this->scoped($run, function () use ($run) { return $this->source->manifestHashForRun($run->run_id); });
        $this->assertSame($expected, $actual); $p = $this->payloads($run, 'manifestRun')[0];
        $this->assertCount(4, $p['rows'][0]['population']['tables']['md_source_observations']);
        $this->assertCount(1, $p['rows'][0]['population']['tables']['md_source_observation_rejected_rows']);
        $this->assertContains('FAILED', array_column($p['rows'][0]['population']['tables']['md_source_observations'], 'outcome_state'));
    }

    public function test_completeness_detects_one_immutable_member_changing_between_reads(): void
    {
        $out = $this->accepted(); $run = $this->runContext();
        $this->scoped($run, function () use ($out) {
            $this->source->existsAccepted($out['source_observation_id'], 'row:1');
            DB::table('md_source_observations')->where('source_observation_id', $out['source_observation_id'])->update(['reason_code' => 'CORRUPTED_BETWEEN_READS']);
            $this->source->manifestHashForObservationIds([$out['source_observation_id']]);
        });
        $repo = new RunInputCaptureRepository();
        $missing = (new \App\Infrastructure\Persistence\MarketData\ProducerSourceObservationCompleteness())->missing($repo->forRun((int) $run->run_id), $repo);
        $this->assertContains('source_observations.immutable_member_changed.md_source_observations|'.$out['source_observation_id'], $missing);
    }
}

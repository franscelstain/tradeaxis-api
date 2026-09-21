<?php

use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Infrastructure\Persistence\MarketData\ProducerInputScope;
use App\Infrastructure\Persistence\MarketData\ProducerInputCompletionManifest;
use App\Infrastructure\Persistence\MarketData\ProducerTradingStatusPopulation;
use App\Infrastructure\Persistence\MarketData\RunInputCaptureRepository;
use App\Infrastructure\Persistence\MarketData\TemporalIdentityRepository;
use App\Infrastructure\Persistence\MarketData\TemporalTradingStatusRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

class B18ProducerTradingStatusPopulationTest extends TestCase
{
    use UsesMarketDataSqlite;
    private $observationId;
    private $revisionSequence = 0;

    protected function setUp(): void
    {
        parent::setUp(); $this->bootMarketDataSqlite(); \Carbon\Carbon::setTestNow('2026-03-25 10:30:00');
        DB::table('tickers')->insert(['ticker_id' => 1, 'ticker_code' => 'ALPHA', 'company_name' => 'Alpha',
            'is_active' => 1, 'board_code' => 'RG', 'listed_date' => '2020-01-01', 'created_at' => '2020-01-01 00:00:00']);
        (new TemporalIdentityRepository())->ensureLegacyProjection();
        $this->observationId = DB::table('md_source_observations')->insertGetId([
            'observation_uid' => hash('sha256', 'C05-source'), 'attempt_uid' => 'C05-source', 'requested_trade_date' => '2026-03-24',
            'source_mode' => 'authority_document', 'source_name' => 'IDX', 'provider' => 'IDX',
            'sanitized_request_identity' => 'https://www.idx.co.id/notice', 'response_status' => 200,
            'content_type' => 'application/json', 'acquired_at' => '2026-03-20 17:00:00', 'adapter_version' => 'C05-fixture-v1',
            'payload_hash' => str_repeat('a', 64), 'outcome_state' => 'ACCEPTED', 'created_at' => '2026-03-20 17:00:00',
        ]);
    }

    protected function tearDown(): void
    {
        \Carbon\Carbon::setTestNow(); $this->tearDownMarketDataSqlite(); parent::tearDown();
    }

    private function revision(array $override = []): int
    {
        return (int) DB::table('md_trading_status_revisions')->insertGetId(array_merge([
            'listing_id' => 1, 'instrument_id' => 1, 'status_event_uid' => hash('sha256', 'C05-'.++$this->revisionSequence),
            'status_type_code' => 'SUSPENDED', 'status_code' => 'SUSPENSION', 'bar_expectation_state' => 'BAR_NOT_EXPECTED',
            'board_code' => 'RG', 'authority_class' => 'EXCHANGE_AUTHORITATIVE', 'source_name' => 'IDX_OFFICIAL',
            'source_payload_hash' => str_repeat('a', 64), 'source_ref' => 'https://www.idx.co.id/notice',
            'source_observation_id' => $this->observationId, 'observed_at' => '2026-03-20 00:00:00', 'announced_at' => '2026-03-20 00:00:00',
            'effective_from' => '2026-03-20 00:00:00', 'effective_to' => null, 'recorded_at' => '2026-03-20 17:00:00',
            'retracted_at' => null, 'supersedes_revision_id' => null, 'verification_state' => 'VERIFIED', 'full_session_verified' => 1,
        ], $override));
    }

    private function runContext() { return (new EodRunRepository())->getOrCreateOwningRun('2026-03-24', 'api', 'INGEST_BARS', null, 'c05'); }

    private function resolve($run, int $listingId = 1, string $stage = 'ELIGIBILITY'): array
    {
        return ProducerInputScope::during($run, $stage, 'status-fixture/v1', static function () use ($listingId) {
            return (new TemporalTradingStatusRepository())->resolveForListing($listingId, '2026-03-24');
        });
    }

    private function payloads($run): array
    {
        $repository = new RunInputCaptureRepository(); $population = null; $selection = null;
        foreach ($repository->forRun((int) $run->run_id) as $row) {
            $p = $repository->verify($row);
            if ($p['selection_context']['operation'] === 'status-revision-population/v1') $population = [$row, $p];
            if ($p['selection_context']['operation'] === 'status-authority-revisions/v1') $selection = [$row, $p];
        }
        $this->assertNotNull($population); $this->assertNotNull($selection);
        $this->assertSame($population[0]['payload_hash'], $selection[1]['rows'][0]['population_ref']['payload_hash']);
        return [$population, $selection];
    }

    public function test_full_population_result_authority_and_every_omission_are_retained(): void
    {
        $old = $this->revision();
        $selected = $this->revision(['supersedes_revision_id' => $old, 'recorded_at' => '2026-03-21 17:00:00', 'status_code' => 'UNSUSPENDED', 'bar_expectation_state' => 'BAR_EXPECTED']);
        $retracted = $this->revision(['retracted_at' => '2026-03-22 00:00:00']);
        $unverified = $this->revision(['verification_state' => 'PENDING']);
        $ineffective = $this->revision(['effective_from' => '2026-04-01 00:00:00']);
        $invalid = $this->revision(['source_name' => 'UNREGISTERED']);
        $lower = $this->revision(['authority_class' => 'OPERATOR_ENTERED', 'source_name' => 'GOVERNED_OPERATOR_ENTRY',
            'operator_name' => 'Test Operator', 'governed_reason_code' => 'AUTHORITATIVE_NOTICE', 'authoritative_source_ref' => 'https://www.idx.co.id/notice']);
        $future = $this->revision(['recorded_at' => '2026-04-01 00:00:00', 'supersedes_revision_id' => $selected]);
        $run = $this->runContext();
        $expected = (new TemporalTradingStatusRepository())->resolveForListing(1, '2026-03-24', (string) $run->knowledge_cutoff_at);
        $actual = $this->resolve($run); $this->assertSame($expected, $actual);
        $this->assertSame([$selected], $actual['status_revision_ids']); $this->assertSame('BAR_EXPECTED', $actual['bar_expectation_state']);
        [$population, $selection] = $this->payloads($run); $p = $population[1]['rows'][0]; $s = $selection[1]['rows'][0];
        $this->assertCount(7, $p['tables']['md_trading_status_revisions']);
        $this->assertNotContains($future, array_map('intval', array_column($p['tables']['md_trading_status_revisions'], 'status_revision_id')));
        foreach (ProducerTradingStatusPopulation::TABLE_KEYS as $table => $keys) {
            $query = DB::table($table);
            if (in_array($table, ['md_trading_status_revisions', 'md_listings', 'md_listing_boards'], true)) $query->where('recorded_at', '<=', $run->knowledge_cutoff_at);
            foreach ($keys as $key) $query->orderBy($key);
            $expectedRows = array_map(static function ($r) { return (array) $r; }, $query->get()->all());
            $this->assertGreaterThan(0, count($expectedRows), $table.' fixture population');
            $this->assertSame(RunInputCaptureRepository::canonicalJson($expectedRows), RunInputCaptureRepository::canonicalJson($p['tables'][$table]), $table);
        }
        $reasons = array_column($s['omitted_revisions'], 'reasons', 'status_revision_id');
        foreach ([$old => 'SUPERSEDED_AT_KNOWLEDGE_CUTOFF', $retracted => 'RETRACTED_AT_KNOWLEDGE_CUTOFF',
            $unverified => 'NOT_VERIFIED', $ineffective => 'OUTSIDE_EFFECTIVE_INTERVAL',
            $invalid => 'TRADING_STATUS_SOURCE_NOT_REGISTERED', $lower => 'LOWER_AUTHORITY_PRIORITY'] as $id => $reason) $this->assertContains($reason, $reasons[$id]);
        $this->assertSame(RunInputCaptureRepository::canonicalJson($actual), RunInputCaptureRepository::canonicalJson($s['selection_result']));
        $evaluations = array_column($s['authority_evaluations'], null, 'status_revision_id');
        $this->assertTrue($evaluations[$selected]['valid']); $this->assertSame(10, $evaluations[$selected]['priority']);
        $this->assertSame([], $this->statusGaps($run));
    }

    private function statusGaps($run): array
    {
        $manifest = (new ProducerInputCompletionManifest())->inspect($run, new RunInputCaptureRepository());
        $this->assertSame('BLOCKED', $manifest['status'], 'Other C1 domains remain incomplete.');
        return array_values(array_filter($manifest['missing_paths'], static function ($p) { return strpos($p, 'status_expectation.') === 0 || strpos($p, 'completion.producer_scope.') === 0; }));
    }

    public function test_conflict_is_retained_as_unknown_with_reasons_for_both_revisions(): void
    {
        $a = $this->revision(); $b = $this->revision(['status_code' => 'NORMAL', 'bar_expectation_state' => 'BAR_EXPECTED']);
        $run = $this->runContext(); $result = $this->resolve($run);
        $this->assertSame('TRADING_STATUS_CONFLICT', $result['reason_code']);
        $this->assertSame('BAR_EXPECTATION_UNKNOWN', $result['bar_expectation_state']);
        [, $selection] = $this->payloads($run); $s = $selection[1]['rows'][0];
        $this->assertSame([], $s['selected_revision_ids']);
        $this->assertSame([$a, $b], array_column($s['omitted_revisions'], 'status_revision_id'));
        foreach ($s['omitted_revisions'] as $omission) $this->assertSame(['TRADING_STATUS_CONFLICT'], $omission['reasons']);
        $this->assertSame([], $this->statusGaps($run));
    }

    public function test_empty_population_has_explicit_basis_and_remains_unknown(): void
    {
        $run = $this->runContext(); $result = $this->resolve($run);
        $this->assertSame('TRADING_STATUS_NO_EVIDENCE', $result['reason_code']);
        [, $selection] = $this->payloads($run);
        $this->assertSame('NO_RECORDED_STATUS_REVISIONS_AT_KNOWLEDGE_CUTOFF', $selection[1]['rows'][0]['empty_basis']);
        $this->assertSame([], $this->statusGaps($run));
    }

    public function test_one_lost_status_selection_is_detected_using_real_savepoint(): void
    {
        $this->revision(); $run = $this->runContext();
        $this->expectExceptionMessage('INPUT_CAPTURE_PRODUCER_COMPLETION_MISMATCH');
        ProducerInputScope::during($run, 'ELIGIBILITY', 'lost-status/v1', function () use ($run) {
            $resolver = new TemporalTradingStatusRepository(); $resolver->resolveForListing(1, '2026-03-24');
            DB::statement('SAVEPOINT lost_status'); $resolver->resolveForListing(2, '2026-03-24');
            $query = DB::table('md_run_input_captures')->where('run_id', $run->run_id)->where('component_key', 'status_expectation');
            $this->assertSame(3, $query->count());
            DB::statement('ROLLBACK TO SAVEPOINT lost_status'); DB::statement('RELEASE SAVEPOINT lost_status');
            $this->assertSame(2, $query->count());
        });
    }

    public static function changedInputs(): array { return [['revision'], ['registry'], ['observation'], ['dictionary']]; }

    /** @dataProvider changedInputs */
    public function test_retry_changed_one_revision_conflicts_and_preserves_bytes(string $input): void
    {
        $id = $this->revision(); $run = $this->runContext(); $first = $this->resolve($run);
        $before = DB::table('md_run_input_captures')->where('run_id', $run->run_id)->get()->toJson();
        $this->assertSame($first, $this->resolve($run));
        if ($input === 'revision') DB::table('md_trading_status_revisions')->where('status_revision_id', $id)->update(['source_ref' => 'https://www.idx.co.id/changed']);
        elseif ($input === 'registry') DB::table('md_trading_status_source_registry')->where('source_name', 'IDX_OFFICIAL')->update(['priority' => 99]);
        elseif ($input === 'observation') DB::table('md_source_observations')->where('source_observation_id', $this->observationId)->update(['payload_hash' => str_repeat('b', 64)]);
        else DB::table('market_data_trading_status_event_types')->where('event_type_code', 'SUSPENDED')->update(['carries_forward' => 0]);
        try { $this->resolve($run); $this->fail('Changed revision must conflict'); }
        catch (RuntimeException $e) { $this->assertStringContainsString('INPUT_CAPTURE_CONFLICT', $e->getMessage()); }
        $this->assertSame($before, DB::table('md_run_input_captures')->where('run_id', $run->run_id)->get()->toJson());
    }

    public function test_historical_missing_population_cannot_use_current_source_registry(): void
    {
        $this->revision(); $run = $this->runContext(); $run->request_mode = 'replay_verify';
        $this->expectExceptionMessage('INPUT_CAPTURE_HISTORICAL_STATUS_POPULATION_UNBOUND'); $this->resolve($run);
    }

    public function test_multiple_reads_consume_one_immutable_population_without_live_reconstruction(): void
    {
        $id = $this->revision(); $run = $this->runContext();
        ProducerInputScope::during($run, 'ELIGIBILITY', 'shared-status/v1', function () use ($id) {
            $resolver = new TemporalTradingStatusRepository(); $first = $resolver->resolveForListing(1, '2026-03-24');
            DB::table('md_trading_status_revisions')->where('status_revision_id', $id)->update(['bar_expectation_state' => 'BAR_EXPECTED']);
            $this->assertSame($first, $resolver->resolveForListing(1, '2026-03-24'));
            $this->assertSame('TRADING_STATUS_STABLE_MAPPING_MISSING', $resolver->resolveForListing(2, '2026-03-24')['reason_code']);
        });
        $repository = new RunInputCaptureRepository(); $populations = []; $references = [];
        foreach ($repository->forRun((int) $run->run_id) as $row) {
            $p = $repository->verify($row);
            if ($p['selection_context']['operation'] === 'status-revision-population/v1') $populations[] = $row;
            if ($p['selection_context']['operation'] === 'status-authority-revisions/v1') $references[] = $p['rows'][0]['population_ref'];
        }
        $this->assertCount(1, $populations); $this->assertCount(2, $references);
        foreach ($references as $ref) $this->assertSame($populations[0]['payload_hash'], $ref['payload_hash']);
        $this->assertSame([], $this->statusGaps($run), 'Verification must use the captured source even after live input changes.');
    }

    public function test_historical_failure_cannot_be_swallowed_by_a_consumer(): void
    {
        $this->revision(); $run = $this->runContext(); $run->request_mode = 'replay_verify';
        $this->expectExceptionMessage('INPUT_CAPTURE_HISTORICAL_STATUS_POPULATION_UNBOUND');
        ProducerInputScope::during($run, 'ELIGIBILITY', 'caught-historical-status/v1', static function () {
            try { (new TemporalTradingStatusRepository())->resolveForListing(1, '2026-03-24'); }
            catch (RuntimeException $error) { /* Simulate a consumer classifying unavailable input. */ }
            return 'attempted dependent output';
        });
    }

    public static function damagedMembers(): array
    {
        return [['effective_from'], ['recorded_at'], ['retracted_at'], ['supersedes_revision_id'], ['authority_class'], ['source_ref'],
            ['selection_result'], ['omitted_revisions'], ['authority_evaluations'], ['population_reference'], ['population_component']];
    }

    /** @dataProvider damagedMembers */
    public function test_validator_names_one_damaged_status_member(string $fault): void
    {
        $old = $this->revision(); $this->revision(['supersedes_revision_id' => $old]);
        $run = $this->runContext(); $this->resolve($run); $this->assertSame([], $this->statusGaps($run));
        [$population, $selection] = $this->payloads($run);
        $p = $population[1]['rows'][0]; $s = $selection[1]['rows'][0];
        $context = $selection[1]['selection_context']; $context['fixture_fault'] = $fault;
        if ($fault === 'selection_result') $s['selection_result']['bar_expectation_state'] = 'BAR_EXPECTED';
        elseif ($fault === 'omitted_revisions') $s['omitted_revisions'] = [];
        elseif ($fault === 'authority_evaluations') $s['authority_evaluations'][0]['priority'] = 999;
        elseif (! in_array($fault, ['population_reference', 'population_component'], true)) unset($p['tables']['md_trading_status_revisions'][0][$fault]);
        $repository = new RunInputCaptureRepository();
        $source = $repository->capture((int) $run->run_id, 'PROBE', $fault === 'population_component' ? 'universe_identity' : 'status_expectation',
            ['operation' => 'status-revision-population/v1', 'known_at' => (string) $run->knowledge_cutoff_at,
                'producer_operation' => $context['producer_operation'], 'fixture_fault' => $fault], [$p]);
        $s['population_ref'] = ['run_id' => (int) $run->run_id, 'stage_code' => 'PROBE', 'component_key' => 'status_expectation',
            'slot_hash' => $source['slot_hash'], 'payload_hash' => $fault === 'population_reference' ? str_repeat('0', 64) : $source['payload_hash']];
        $bad = $repository->capture((int) $run->run_id, 'PROBE', 'status_expectation', $context, [$s]);
        $result = (new ProducerInputCompletionManifest())->inspect($run, $repository);
        $path = in_array($fault, ['selection_result', 'omitted_revisions', 'authority_evaluations', 'population_reference'], true)
            ? $fault : 'md_trading_status_revisions.0.'.$fault;
        if ($fault === 'population_component') $path = 'population_reference';
        $this->assertContains('status_expectation.'.$bad['slot_hash'].'.'.$path, $result['missing_paths']);
    }
}

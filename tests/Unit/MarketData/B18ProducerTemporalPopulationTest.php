<?php

use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Infrastructure\Persistence\MarketData\ProducerInputScope;
use App\Infrastructure\Persistence\MarketData\ProducerTemporalPopulation;
use App\Infrastructure\Persistence\MarketData\RunInputCaptureRepository;
use App\Infrastructure\Persistence\MarketData\TemporalIdentityRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

class B18ProducerTemporalPopulationTest extends TestCase
{
    use UsesMarketDataSqlite;

    protected function setUp(): void
    {
        parent::setUp(); $this->bootMarketDataSqlite();
        \Carbon\Carbon::setTestNow('2026-03-25 10:30:00');
        foreach ([1 => 'ALPHA', 2 => 'BETA', 3 => 'GAMMA'] as $id => $code) DB::table('tickers')->insert([
            'ticker_id' => $id, 'ticker_code' => $code, 'company_name' => $code.' Ltd', 'is_active' => 1,
            'listed_date' => '2020-01-01', 'created_at' => '2020-01-01 00:00:00',
        ]);
        (new TemporalIdentityRepository())->ensureLegacyProjection();
        DB::table('md_listings')->where('legacy_ticker_id', 2)->update([
            'delisted_date' => '2026-02-01', 'delisted_recorded_at' => '2026-02-02 00:00:00',
        ]);
        DB::table('md_listings')->where('legacy_ticker_id', 3)->update(['listed_date' => '2026-04-01']);
    }

    protected function tearDown(): void
    {
        \Carbon\Carbon::setTestNow(); $this->tearDownMarketDataSqlite(); parent::tearDown();
    }

    private function runContext()
    {
        return (new EodRunRepository())->getOrCreateOwningRun('2026-03-24', 'api', 'INGEST_BARS', null, 'c1-temporal');
    }

    private function payload($run, string $component): array
    {
        $repository = new RunInputCaptureRepository();
        $operation = $component === 'universe_identity' ? 'temporal-identity-revisions/v1' : 'provider-mapping-revisions/v1';
        $rows = $repository->forRun((int) $run->run_id);
        foreach ($rows as $row) {
            $payload = $repository->verify($row);
            if ($payload['selection_context']['operation'] !== $operation) continue;
            $ref = $payload['rows'][0]['population_ref'];
            foreach ($rows as $source) if ($source['slot_hash'] === $ref['slot_hash'] && $source['stage_code'] === $ref['stage_code']) {
                $this->assertSame($source['payload_hash'], $ref['payload_hash']);
                $this->assertSame((int) $run->run_id, $ref['run_id']);
                $payload['rows'][0]['tables'] = $repository->verify($source)['rows'][0]['tables'];
                return $payload;
            }
            $this->fail('Full population reference is missing');
        }
        $this->fail('Expected persisted '.$component.' capture');
    }

    public function test_full_known_population_coordinates_omissions_and_consumed_result_are_preserved(): void
    {
        $run = $this->runContext(); $identity = new TemporalIdentityRepository();
        $expected = $identity->readProjectedUniverseAsOf('2026-03-24', (string) $run->knowledge_cutoff_at);
        $this->assertCount(1, $expected);
        $actual = ProducerInputScope::during($run, 'ELIGIBILITY', 'temporal-fixture/v1', function () use ($identity) {
            return $identity->readProjectedUniverseAsOf('2026-03-24');
        });
        $this->assertSame($expected, $actual);
        $capture = $this->payload($run, 'universe_identity'); $payload = $capture['rows'][0];
        $this->assertSame((string) $run->knowledge_cutoff_at, $capture['selection_context']['known_at']);
        $this->assertSame((string) config('market_data.scope.dataset_start'), $capture['selection_context']['dataset_start']);
        $this->assertCount(6, $payload['tables']);
        foreach (ProducerTemporalPopulation::TABLES as $table => $key) {
            $expectedRows = array_map(static function ($r) { return (array) $r; }, DB::table($table)->orderBy($key)->get()->all());
            $this->assertGreaterThan(0, count($expectedRows));
            $this->assertSame(RunInputCaptureRepository::canonicalJson($expectedRows), RunInputCaptureRepository::canonicalJson($payload['tables'][$table]), $table);
            $this->assertSame(count($expectedRows), $payload['population_counts'][$table]);
            $this->assertSame($key, $payload['selection_basis']['source_primary_keys'][$table]);
        }
        $omitted = array_column($payload['omitted_listings'], 'reasons', 'listing_id');
        $this->assertContains('KNOWN_DELISTING', $omitted[2]);
        $this->assertContains('NOT_YET_LISTED', $omitted[3]);
        $this->assertCount(1, $payload['selected_rows']);
    }

    public function test_provider_retains_every_known_revision_and_selects_from_the_captured_population(): void
    {
        $first = (array) DB::table('md_provider_symbol_mappings')->where('listing_id', 1)->first();
        $next = $first; unset($next['provider_mapping_id']);
        $next['recorded_at'] = '2026-02-01 00:00:00'; $next['mapping_revision'] = 'explicit-revision-2';
        $nextId = DB::table('md_provider_symbol_mappings')->insertGetId($next);
        $future = $next; $future['recorded_at'] = '2026-04-01 00:00:00'; $future['provider_symbol'] = 'FUTURE.JK';
        $futureId = DB::table('md_provider_symbol_mappings')->insertGetId($future);
        $run = $this->runContext(); $identity = new TemporalIdentityRepository();
        $expected = $identity->resolveProviderContext('ALPHA', $first['provider'], '2026-03-24', (string) $run->knowledge_cutoff_at);
        $actual = ProducerInputScope::during($run, 'INGEST_BARS', 'provider-fixture/v1', function () use ($identity, $first) {
            return $identity->resolveProviderContext('ALPHA', $first['provider'], '2026-03-24');
        });
        $this->assertSame($expected, $actual); $this->assertSame((int) $nextId, $actual['provider_mapping_id']);
        $payload = $this->payload($run, 'provider_mapping')['rows'][0];
        $ids = array_map('intval', array_column($payload['tables']['md_provider_symbol_mappings'], 'provider_mapping_id'));
        $this->assertContains((int) $first['provider_mapping_id'], $ids); $this->assertContains((int) $nextId, $ids);
        $this->assertNotContains((int) $futureId, $ids); $this->assertCount(2, $payload['selected_rows']);
        $captured = array_column($payload['tables']['md_provider_symbol_mappings'], null, 'provider_mapping_id')[$nextId];
        foreach ($next as $key => $value) $this->assertSame($value, $captured[$key], $key);
    }

    public function test_historical_scope_cannot_bootstrap_a_missing_listing_from_current_tickers(): void
    {
        $run = $this->runContext(); $run->request_mode = 'replay_verify';
        DB::table('tickers')->insert(['ticker_id' => 4, 'ticker_code' => 'UNBOUND', 'company_name' => 'UNBOUND', 'is_active' => 1,
            'listed_date' => '2020-01-01', 'created_at' => '2020-01-01 00:00:00']);
        $this->assertSame(0, DB::table('md_listings')->where('legacy_ticker_id', 4)->count());
        $result = ProducerInputScope::during($run, 'INGEST_BARS', 'historical-provider/v1', function () {
            return (new TemporalIdentityRepository())->resolveByTickerCodes(['UNBOUND'], '2026-03-24');
        });
        $this->assertSame([], $result);
        $this->assertSame(0, DB::table('md_listings')->where('legacy_ticker_id', 4)->count());
        $payload = $this->payload($run, 'provider_mapping')['rows'][0];
        $this->assertSame([], $payload['selected_rows']);
        $this->assertSame('NO_ELIGIBLE_MEMBER_IN_KNOWN_POPULATION', $payload['selection_basis']['empty_result']);
    }

    public function test_one_lost_temporal_capture_prevents_completion_with_real_storage(): void
    {
        $run = $this->runContext();
        $this->expectExceptionMessage('INPUT_CAPTURE_PRODUCER_COMPLETION_MISMATCH');
        ProducerInputScope::during($run, 'ELIGIBILITY', 'lost-temporal/v1', function () use ($run) {
            (new TemporalIdentityRepository())->resolveProviderContext('ALPHA', 'yahoo_finance', '2026-03-24');
            DB::statement('SAVEPOINT temporal_loss');
            (new TemporalIdentityRepository())->readProjectedUniverseAsOf('2026-03-24');
            $q = DB::table('md_run_input_captures')->where('run_id', $run->run_id)->where('component_key', 'universe_identity');
            $this->assertSame(2, $q->count(), 'One population capsule and one exact selection are persisted.');
            DB::statement('ROLLBACK TO SAVEPOINT temporal_loss'); DB::statement('RELEASE SAVEPOINT temporal_loss');
            $this->assertSame(1, $q->count(), 'Only the selection capture is lost; its population capsule remains.');
        });
    }

    public function test_one_changed_revision_in_retry_conflicts_without_overwriting_the_capture(): void
    {
        $run = $this->runContext(); $read = static function () { return (new TemporalIdentityRepository())->readProjectedUniverseAsOf('2026-03-24'); };
        ProducerInputScope::during($run, 'ELIGIBILITY', 'retry-temporal/v1', $read);
        $before = DB::table('md_run_input_captures')->where('run_id', $run->run_id)->get()->toJson();
        DB::table('md_listing_symbols')->where('listing_id', 1)->update(['source_ref' => 'one changed provenance']);
        try { ProducerInputScope::during($run, 'ELIGIBILITY', 'retry-temporal/v1', $read); $this->fail('Changed source must conflict.'); }
        catch (RuntimeException $e) { $this->assertStringContainsString('INPUT_CAPTURE_CONFLICT', $e->getMessage()); }
        $this->assertSame($before, DB::table('md_run_input_captures')->where('run_id', $run->run_id)->get()->toJson());
    }

    public static function invalidTemporalMembers(): array
    {
        return [
            ['effective_from', 'md_listing_symbols.0.effective_from'],
            ['recorded_at', 'md_listing_symbols.0.recorded_at'],
            ['retracted_at', 'md_listing_symbols.0.retracted_at'],
            ['listing_symbol_id', 'md_listing_symbols.0.listing_symbol_id'],
            ['source_ref', 'md_listing_symbols.0.source_ref'],
            ['selected_value', 'selection_result.ticker_code'],
            ['omission_reason', 'omission_basis'],
            ['lost_source_revision', 'unexpected_selected_revision'],
            ['lost_selected_revision', 'population_membership'],
        ];
    }

    /** @dataProvider invalidTemporalMembers */
    public function test_one_invalid_member_is_named_by_the_persisted_capture_validator(string $fault, string $missingPath): void
    {
        $run = $this->runContext();
        ProducerInputScope::during($run, 'ELIGIBILITY', 'validator-control/v1', static function () {
            return (new TemporalIdentityRepository())->readProjectedUniverseAsOf('2026-03-24');
        });
        $capture = $this->payload($run, 'universe_identity'); $payload = $capture['rows'][0];
        $repository = new RunInputCaptureRepository(); $validator = new \App\Infrastructure\Persistence\MarketData\ProducerInputCompletionManifest();
        $control = $validator->inspect($run, $repository);
        $this->assertSame([], array_values(array_filter($control['missing_paths'], static function ($p) {
            return strpos($p, 'universe_identity.') === 0;
        })), 'Valid full temporal population must have no temporal-content gap, even while other C1 domains are blocked.');
        if ($fault === 'selected_value') $payload['selected_rows'][0]['ticker_code'] = 'WRONG';
        elseif ($fault === 'omission_reason') $payload['omitted_listings'][0]['reasons'] = ['FABRICATED_OMISSION'];
        elseif ($fault === 'lost_source_revision') {
            array_shift($payload['tables']['md_listing_symbols']); $payload['population_counts']['md_listing_symbols']--;
        } elseif ($fault === 'lost_selected_revision') $payload['selected_rows'] = [];
        else unset($payload['tables']['md_listing_symbols'][0][$fault]);
        $selection = $capture['selection_context']; $selection['fixture_variant'] = $fault;
        $source = $repository->capture((int) $run->run_id, 'PROBE', 'universe_identity', [
            'operation' => 'temporal-revision-population/v1', 'known_at' => (string) $run->knowledge_cutoff_at,
            'producer_operation' => $selection['producer_operation'], 'fixture_variant' => $fault,
        ], [['population_schema' => 'md_temporal_revision_population_v1', 'tables' => $payload['tables'], 'population_counts' => $payload['population_counts']]]);
        unset($payload['tables']);
        $payload['population_ref'] = ['run_id' => (int) $run->run_id, 'stage_code' => 'PROBE', 'component_key' => 'universe_identity',
            'slot_hash' => $source['slot_hash'], 'payload_hash' => $source['payload_hash']];
        $bad = $repository->capture((int) $run->run_id, 'PROBE', 'universe_identity', $selection, [$payload]);
        $result = $validator->inspect($run, $repository);
        $this->assertSame('BLOCKED', $result['status']);
        $this->assertContains('universe_identity.'.$bad['slot_hash'].'.'.$missingPath, $result['missing_paths']);
    }

    public function test_multiple_mapping_selections_share_one_verified_population_capsule(): void
    {
        $run = $this->runContext();
        ProducerInputScope::during($run, 'INGEST_BARS', 'shared-population/v1', static function () {
            return (new TemporalIdentityRepository())->resolveByTickerCodes(['ALPHA', 'BETA', 'GAMMA'], '2026-03-24');
        });
        $repository = new RunInputCaptureRepository(); $populations = []; $references = [];
        foreach ($repository->forRun((int) $run->run_id) as $row) {
            $p = $repository->verify($row);
            if ($p['selection_context']['operation'] === 'temporal-revision-population/v1') $populations[] = $row;
            if ($p['selection_context']['operation'] === 'provider-mapping-revisions/v1') {
                $this->assertArrayNotHasKey('tables', $p['rows'][0]);
                $references[] = $p['rows'][0]['population_ref'];
            }
        }
        $this->assertCount(1, $populations); $this->assertCount(3, $references);
        foreach ($references as $ref) {
            $this->assertSame($populations[0]['slot_hash'], $ref['slot_hash']);
            $this->assertSame($populations[0]['payload_hash'], $ref['payload_hash']);
        }
    }

    public function test_one_wrong_population_reference_hash_is_blocked_without_current_lookup(): void
    {
        $run = $this->runContext();
        ProducerInputScope::during($run, 'ELIGIBILITY', 'reference-fixture/v1', static function () {
            return (new TemporalIdentityRepository())->readProjectedUniverseAsOf('2026-03-24');
        });
        $captured = $this->payload($run, 'universe_identity'); $payload = $captured['rows'][0]; unset($payload['tables']);
        $payload['population_ref']['payload_hash'] = str_repeat('0', 64);
        $selection = $captured['selection_context']; $selection['fixture_variant'] = 'wrong-reference-hash';
        $repository = new RunInputCaptureRepository();
        $bad = $repository->capture((int) $run->run_id, 'ELIGIBILITY', 'universe_identity', $selection, [$payload]);
        $result = (new \App\Infrastructure\Persistence\MarketData\ProducerInputCompletionManifest())->inspect($run, $repository);
        $this->assertContains('universe_identity.'.$bad['slot_hash'].'.population_reference', $result['missing_paths']);
        $this->assertSame('BLOCKED', $result['status']);
    }
}

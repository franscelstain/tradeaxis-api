<?php

use App\Application\MarketData\Services\CoverageGateEvaluator;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Models\EodRun;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * `F-006` — the coverage denominator must be a function of (trade date, knowledge time).
 *
 * Owner contract: docs/market_data/book/Coverage_Universe_Definition_LOCKED.md
 *
 * The finding recorded 950 → 949 → 950 across three runs of one trade date on one execution day,
 * and re-measurement on 2026-08-12 found 202 date/day pairs behaving the same way. The cause was not
 * a wrong universe rule: it was that the universe answered "as of whenever this query ran", so a
 * listing recorded between two runs silently joined the second one's denominator.
 *
 * A cutoff that excluded everything would also produce identical answers, so each assertion here is
 * paired with its counterproof: the same corpus read without a cutoff must move, and a later cutoff
 * must admit the later listing. Stability alone would prove nothing.
 */
class CoverageDenominatorKnowledgeCutoffTest extends TestCase
{
    use UsesMarketDataSqlite;

    private const TRADE_DATE = '2026-03-24';

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
    }

    protected function tearDown(): void
    {
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    /**
     * One listing, recorded into the platform's knowledge at a stated moment.
     *
     * `recorded_at` is set on all four identity tables because the cutoff is applied to all four —
     * seeding only the listing would let the join drop the row for an unrelated reason and the test
     * would pass without the cutoff doing any work.
     */
    private function seedListing(int $n, string $recordedAt): void
    {
        $issuerId = DB::table('md_issuers')->insertGetId([
            'issuer_uid' => 'ISSUER-'.$n,
            'legal_name' => 'Issuer '.$n,
            'recorded_at' => $recordedAt,
            'created_at' => $recordedAt,
        ]);

        $instrumentId = DB::table('md_instruments')->insertGetId([
            'instrument_uid' => 'INSTRUMENT-'.$n,
            'issuer_id' => $issuerId,
            'instrument_type' => 'EQUITY',
            'currency_code' => 'IDR',
            'recorded_at' => $recordedAt,
            'created_at' => $recordedAt,
        ]);

        $listingId = (int) DB::table('md_listings')->insertGetId([
            'listing_uid' => 'LISTING-'.$n,
            'legacy_ticker_id' => 900 + $n,
            'instrument_id' => $instrumentId,
            'exchange_code' => 'IDX',
            'market_segment' => 'REGULAR',
            'board_code' => 'MAIN',
            'listed_date' => '2023-01-02',
            'delisted_date' => null,
            'listing_state' => 'LISTED',
            'recorded_at' => $recordedAt,
            'created_at' => $recordedAt,
        ]);

        DB::table('md_listing_symbols')->insert([
            'listing_id' => $listingId,
            'symbol' => 'AAA'.$n,
            'symbol_type' => 'EXCHANGE',
            'effective_from' => '2023-01-02 00:00:00',
            'effective_to' => null,
            'recorded_at' => $recordedAt,
        ]);
    }

    private function evaluateAt(?string $knownAt): array
    {
        return app(CoverageGateEvaluator::class)->evaluate(self::TRADE_DATE, null, $knownAt);
    }

    /**
     * The whole finding in one test: a listing recorded after the cutoff must not enter the
     * denominator, and the same read must keep answering the same way once it exists.
     */
    public function test_denominator_at_a_fixed_cutoff_is_unmoved_by_a_later_recorded_listing(): void
    {
        foreach ([1, 2, 3] as $n) {
            $this->seedListing($n, '2026-03-01 00:00:00');
        }

        $cutoff = '2026-03-10 00:00:00';
        $before = $this->evaluateAt($cutoff);
        $this->assertSame(3, (int) $before['expected_universe_count'], 'precondition: three listings are known at the cutoff');

        $this->seedListing(4, '2026-03-20 00:00:00');

        $after = $this->evaluateAt($cutoff);
        $this->assertSame(3, (int) $after['expected_universe_count'], 'a listing recorded after the cutoff joined the denominator');
        $this->assertSame(
            $before['coverage_universe_hash'],
            $after['coverage_universe_hash'],
            'the universe hash moved although the knowledge coordinate did not'
        );
    }

    /**
     * Counterproof for the test above. Without a cutoff the same corpus must move, otherwise the
     * stability just demonstrated would be a property of the fixture rather than of the cutoff.
     */
    public function test_without_a_cutoff_the_same_corpus_moves(): void
    {
        foreach ([1, 2, 3] as $n) {
            $this->seedListing($n, '2026-03-01 00:00:00');
        }

        $before = $this->evaluateAt(null);
        $this->assertSame(3, (int) $before['expected_universe_count']);

        $this->seedListing(4, '2026-03-20 00:00:00');

        $after = $this->evaluateAt(null);
        $this->assertSame(4, (int) $after['expected_universe_count'], 'the uncut read should see the new listing');
        $this->assertNotSame(
            $before['coverage_universe_hash'],
            $after['coverage_universe_hash'],
            'the universe changed and the hash did not notice'
        );
    }

    /**
     * Second counterproof: the cutoff must be a coordinate, not a blanket exclusion. Moving it past
     * the later listing has to admit that listing and change the hash.
     */
    public function test_a_later_cutoff_admits_the_later_listing(): void
    {
        foreach ([1, 2, 3] as $n) {
            $this->seedListing($n, '2026-03-01 00:00:00');
        }
        $this->seedListing(4, '2026-03-20 00:00:00');

        $early = $this->evaluateAt('2026-03-10 00:00:00');
        $late = $this->evaluateAt('2026-03-22 00:00:00');

        $this->assertSame(3, (int) $early['expected_universe_count']);
        $this->assertSame(4, (int) $late['expected_universe_count']);
        $this->assertNotSame($early['coverage_universe_hash'], $late['coverage_universe_hash']);
    }

    /**
     * Identity is only one input to the denominator. A suspension recorded after the cutoff must
     * not remove a listing either, otherwise a stable universe hash could hide a moving expected
     * denominator.
     */
    public function test_denominator_at_a_fixed_cutoff_is_unmoved_by_a_later_recorded_suspension(): void
    {
        foreach ([1, 2, 3] as $n) {
            $this->seedListing($n, '2026-03-01 00:00:00');
        }

        $cutoff = '2026-03-10 00:00:00';
        $before = $this->evaluateAt($cutoff);

        $sourceObservationId = DB::table('md_source_observations')->insertGetId([
            'observation_uid' => hash('sha256', 'stage-1-cutoff-status-fixture'),
            'attempt_uid' => hash('sha256', 'stage-1-cutoff-status-attempt'),
            'requested_trade_date' => self::TRADE_DATE,
            'source_mode' => 'authority_document',
            'source_name' => 'IDX',
            'provider' => 'IDX',
            'sanitized_request_identity' => 'fixture://stage-1-cutoff-status',
            'acquired_at' => '2026-03-20 00:00:00',
            'adapter_version' => 'test-v2-status',
            'outcome_state' => 'ACCEPTED',
            'validation_state' => 'PASSED',
            'created_at' => '2026-03-20 00:00:00',
        ]);
        DB::table('md_trading_status_revisions')->insert([
            'listing_id' => (int) DB::table('md_listings')->where('legacy_ticker_id', 901)->value('listing_id'),
            'status_code' => 'SUSPENSION_OBSERVED',
            'bar_expectation_state' => 'BAR_NOT_EXPECTED',
            'authority_class' => 'EXCHANGE_AUTHORITATIVE',
            'full_session_verified' => 1,
            'effective_from' => self::TRADE_DATE.' 00:00:00',
            'recorded_at' => '2026-03-20 00:00:00',
            'source_observation_id' => $sourceObservationId,
            'verification_state' => 'VERIFIED',
        ]);

        $sameCutoff = $this->evaluateAt($cutoff);
        $laterCutoff = $this->evaluateAt('2026-03-22 00:00:00');

        $this->assertSame(3, (int) $before['expected_universe_count']);
        $this->assertSame(3, (int) $sameCutoff['expected_universe_count']);
        $this->assertSame($before['coverage_universe_hash'], $sameCutoff['coverage_universe_hash']);
        $this->assertSame(0, (int) $sameCutoff['coverage_bar_not_expected_count']);
        $this->assertSame(2, (int) $laterCutoff['expected_universe_count']);
        $this->assertSame(1, (int) $laterCutoff['coverage_bar_not_expected_count']);
    }

    /**
     * The coordinate is stamped once. A run that already carries one must reuse it rather than
     * re-reading the clock, otherwise every stage of the same run would read a different world.
     */
    public function test_an_existing_run_coordinate_is_reused_not_restamped(): void
    {
        $runId = DB::table('eod_runs')->insertGetId([
            'trade_date_requested' => self::TRADE_DATE,
            'lifecycle_state' => 'RUNNING',
            'stage' => 'COVERAGE_EVALUATION',
            'source' => 'api',
            'knowledge_cutoff_at' => '2026-03-10 00:00:00',
            'created_at' => '2026-03-24 18:00:00',
            'updated_at' => '2026-03-24 18:00:00',
        ]);

        $run = EodRun::query()->findOrFail($runId);
        $repository = new EodRunRepository();

        $this->assertSame('2026-03-10 00:00:00', $repository->resolveKnowledgeCutoff($run));
        $this->assertSame('2026-03-10 00:00:00', $repository->resolveKnowledgeCutoff($run), 'the coordinate was restamped on a second read');
    }

    public function test_new_owning_run_is_stamped_at_creation(): void
    {
        $run = (new EodRunRepository())->getOrCreateOwningRun(
            self::TRADE_DATE,
            'api',
            'INGEST_BARS',
            null,
            'import_only'
        );

        $this->assertNotEmpty($run->knowledge_cutoff_at);
        $this->assertSame(
            (string) $run->knowledge_cutoff_at,
            (string) DB::table('eod_runs')->where('run_id', $run->run_id)->value('knowledge_cutoff_at')
        );
    }

    public function test_a_fresh_active_legacy_run_is_rejected_instead_of_reused_or_stamped(): void
    {
        $now = Carbon\Carbon::now(config('market_data.platform.timezone'))->toDateTimeString();
        $runId = DB::table('eod_runs')->insertGetId([
            'trade_date_requested' => self::TRADE_DATE,
            'lifecycle_state' => 'RUNNING',
            'stage' => 'INGEST_BARS',
            'source' => 'api',
            'request_mode' => 'import_only',
            'knowledge_cutoff_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $runCount = (int) DB::table('eod_runs')->count();

        try {
            (new EodRunRepository())->getOrCreateOwningRun(
                self::TRADE_DATE,
                'api',
                'INGEST_BARS',
                null,
                'import_only'
            );
            $this->fail('A fresh active legacy run was reused without a knowledge cutoff.');
        } catch (LogicException $e) {
            $this->assertStringContainsString('RUN_KNOWLEDGE_CUTOFF_MISSING', $e->getMessage());
        }

        $this->assertSame($runCount, (int) DB::table('eod_runs')->count());
        $this->assertSame('RUNNING', DB::table('eod_runs')->where('run_id', $runId)->value('lifecycle_state'));
        $this->assertNull(DB::table('eod_runs')->where('run_id', $runId)->value('knowledge_cutoff_at'));
        $this->assertNull(DB::table('eod_runs')->where('run_id', $runId)->value('config_snapshot_id'));
        $this->assertNull(DB::table('eod_runs')->where('run_id', $runId)->value('operational_start_date'));
    }

    public function test_the_execution_guard_reason_code_is_registered_and_seeded(): void
    {
        $registry = file_get_contents(base_path('docs/market_data/registry/Reason_Codes_Registry.md'));
        $seed = file_get_contents(base_path('docs/market_data/registry/Reason_Codes_Seed.sql'));

        $this->assertStringContainsString('`RUN_KNOWLEDGE_CUTOFF_MISSING` | RUN | HARD', $registry);
        $this->assertStringContainsString("('RUN_KNOWLEDGE_CUTOFF_MISSING', 'RUN'", $seed);
    }

    public function test_new_promote_run_gets_its_own_creation_coordinate(): void
    {
        $repository = new EodRunRepository();
        $seed = $repository->getOrCreateOwningRun(self::TRADE_DATE, 'api', 'INGEST_BARS', null, 'import_only');
        $promote = $repository->createPromoteRunFromSeed($seed, 'PUBLISH_BARS');

        $this->assertNotEmpty($promote->knowledge_cutoff_at);
        $this->assertSame(
            (string) $promote->knowledge_cutoff_at,
            (string) DB::table('eod_runs')->where('run_id', $promote->run_id)->value('knowledge_cutoff_at')
        );
    }

    public function test_promote_overrides_cannot_replace_the_creation_coordinate(): void
    {
        $repository = new EodRunRepository();
        $seed = $repository->getOrCreateOwningRun(self::TRADE_DATE, 'api', 'INGEST_BARS', null, 'import_only');
        $runCount = (int) DB::table('eod_runs')->count();

        try {
            $repository->createPromoteRunFromSeed($seed, 'PUBLISH_BARS', [
                'knowledge_cutoff_at' => '2020-01-01 00:00:00',
            ]);
            $this->fail('A caller replaced the promote run creation coordinate.');
        } catch (LogicException $e) {
            $this->assertStringContainsString('RUN_KNOWLEDGE_CUTOFF_IMMUTABLE', $e->getMessage());
        }

        $this->assertSame($runCount, (int) DB::table('eod_runs')->count());
    }

    public function test_an_existing_coordinate_cannot_be_changed_after_creation(): void
    {
        $repository = new EodRunRepository();
        $run = $repository->getOrCreateOwningRun(self::TRADE_DATE, 'api', 'INGEST_BARS', null, 'import_only');
        $original = (string) DB::table('eod_runs')->where('run_id', $run->run_id)->value('knowledge_cutoff_at');

        try {
            $run->knowledge_cutoff_at = '2030-01-01 00:00:00';
            $run->save();
            $this->fail('An existing run coordinate was mutable.');
        } catch (LogicException $e) {
            $this->assertStringContainsString('RUN_KNOWLEDGE_CUTOFF_IMMUTABLE', $e->getMessage());
        }

        $this->assertSame(
            $original,
            (string) DB::table('eod_runs')->where('run_id', $run->run_id)->value('knowledge_cutoff_at')
        );
    }

    public function test_a_legacy_null_coordinate_cannot_be_lazily_stamped(): void
    {
        $runId = DB::table('eod_runs')->insertGetId([
            'trade_date_requested' => self::TRADE_DATE,
            'lifecycle_state' => 'COMPLETED',
            'stage' => 'FINALIZE',
            'source' => 'api',
            'knowledge_cutoff_at' => null,
            'created_at' => '2026-03-24 18:00:00',
            'updated_at' => '2026-03-24 18:00:00',
        ]);
        $run = EodRun::query()->findOrFail($runId);

        try {
            $run->knowledge_cutoff_at = '2026-08-12 12:00:00';
            $run->save();
            $this->fail('A legacy run was lazily stamped after its execution.');
        } catch (LogicException $e) {
            $this->assertStringContainsString('RUN_KNOWLEDGE_CUTOFF_IMMUTABLE', $e->getMessage());
        }

        $this->assertNull(DB::table('eod_runs')->where('run_id', $runId)->value('knowledge_cutoff_at'));
    }

    /**
     * A legacy run never honoured a cutoff. Reading it later must not manufacture one and rewrite
     * the historical claim from "unbounded" to "as known at the audit date".
     */
    public function test_a_legacy_run_without_a_coordinate_remains_historically_unbounded_on_read(): void
    {
        $runId = DB::table('eod_runs')->insertGetId([
            'trade_date_requested' => self::TRADE_DATE,
            'lifecycle_state' => 'COMPLETED',
            'terminal_status' => 'SUCCESS',
            'stage' => 'FINALIZE',
            'source' => 'api',
            'knowledge_cutoff_at' => null,
            'created_at' => '2026-03-24 18:00:00',
            'updated_at' => '2026-03-24 18:00:00',
        ]);

        $run = EodRun::query()->findOrFail($runId);
        $resolved = (new EodRunRepository())->resolveKnowledgeCutoff($run);

        $this->assertNull($resolved);
        $this->assertNull(
            DB::table('eod_runs')->where('run_id', $runId)->value('knowledge_cutoff_at'),
            'reading a legacy run invented and persisted a cutoff it never honoured'
        );
    }
}

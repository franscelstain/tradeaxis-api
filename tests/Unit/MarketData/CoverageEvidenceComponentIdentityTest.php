<?php

use App\Application\MarketData\Services\CoverageGateEvaluator;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * `MD-B15-A001` guard for the evidence validity boundary of
 * `EOD_COVERAGE_GATE_CONTRACT_LOCKED.md`.
 *
 * The contract requires every coverage evidence record to bind the identity/universe resolver
 * version and the calendar and trading-status revision identities used to resolve expectation, and
 * states the general rule behind it: evidence binds the version of every component its correctness
 * depends on, not only the version of the contract that produced it.
 *
 * The evaluator bound `coverage_contract_version`, `coverage_calibration_version` and
 * `universe_hash_schema_version` — three names for what the contract said, and nothing at all about
 * the components that produced the numbers. A stored coverage result was therefore
 * indistinguishable from one produced by a resolver since found defective, which is the exact
 * condition the contract describes.
 */
class CoverageEvidenceComponentIdentityTest extends TestCase
{
    use UsesMarketDataSqlite;

    private const TRADE_DATE = '2026-07-28';

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();

        $date = strtotime(self::TRADE_DATE);
        $added = 0;
        while ($added < 60) {
            if ((int) date('N', $date) <= 5) {
                $this->seedVerifiedMarketCalendarDate(date('Y-m-d', $date));
                $added++;
            }
            $date = strtotime('-1 day', $date);
        }
    }

    protected function tearDown(): void
    {
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    private function seedTicker(int $tickerId, string $code): void
    {
        DB::table('tickers')->insert([
            'ticker_id' => $tickerId,
            'ticker_code' => $code,
            'company_name' => $code.' Tbk',
            'is_active' => 1,
        ]);
    }

    private function seedBar(int $tickerId, string $date): void
    {
        DB::table('eod_bars')->insert([
            'trade_date' => $date, 'ticker_id' => $tickerId,
            'open' => 100, 'high' => 101, 'low' => 99, 'close' => 100,
            'volume' => 1000, 'adj_close' => 100,
            'source' => 'YAHOO_FINANCE', 'run_id' => 1, 'publication_id' => 1,
            'created_at' => $date.' 18:00:00',
        ]);
    }

    /** @return array<string,mixed> */
    private function evaluate(): array
    {
        return app(CoverageGateEvaluator::class)->evaluate(self::TRADE_DATE);
    }

    /**
     * MD-S024-R0045, R0046 and R0052: the record binds the components its correctness depends on.
     */
    public function test_a_coverage_result_binds_the_resolver_and_revision_identities(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedTicker(2, 'BBRI');
        $this->seedBar(1, self::TRADE_DATE);
        $this->seedBar(2, self::TRADE_DATE);

        $coverage = $this->evaluate();

        foreach ([
            'coverage_universe_resolver_version',
            'coverage_calendar_revision_id',
            'coverage_calendar_revision_uid',
            'coverage_trading_status_revision_ids',
        ] as $field) {
            $this->assertArrayHasKey($field, $coverage, $field.' is not bound to the coverage record');
        }

        $this->assertSame(
            CoverageGateEvaluator::UNIVERSE_RESOLVER_VERSION,
            $coverage['coverage_universe_resolver_version'],
            'the record must name the resolver implementation, not only the contract'
        );
        $this->assertNotSame(
            $coverage['coverage_contract_version'],
            $coverage['coverage_universe_resolver_version'],
            'the resolver version must be a separate identity from the contract version, or it '
                .'records nothing the contract version did not already record'
        );
        $this->assertIsArray($coverage['coverage_trading_status_revision_ids']);
    }

    /**
     * The calendar revision is the one the platform actually resolved for the date, not a constant.
     * Without this the field could be present and still say nothing.
     */
    public function test_the_bound_calendar_revision_is_the_one_resolved_for_the_date(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedBar(1, self::TRADE_DATE);

        $coverage = $this->evaluate();

        $row = DB::table('md_market_calendar_revisions')
            ->where('cal_date', self::TRADE_DATE)
            ->orderByDesc('calendar_revision_id')
            ->first();

        $this->assertNotNull($row, 'the seeded calendar revision must exist for this to mean anything');
        $this->assertSame(
            (int) $row->calendar_revision_id,
            $coverage['coverage_calendar_revision_id'],
            'the coverage record binds a different calendar revision than the one resolved for the date'
        );
        $this->assertSame((string) $row->revision_uid, $coverage['coverage_calendar_revision_uid']);
    }

    /**
     * A `NOT_EVALUABLE` record is still a coverage evidence record. The reason a date could not be
     * evaluated is as version-dependent as any ratio would have been.
     */
    public function test_a_not_evaluable_result_binds_the_same_identities(): void
    {
        // No tickers seeded: the universe is empty and the gate cannot evaluate.
        $coverage = $this->evaluate();

        $this->assertSame('NOT_EVALUABLE', $coverage['coverage_gate_state']);
        $this->assertNull($coverage['coverage_ratio'], 'an empty denominator must not be coerced to a ratio');

        $this->assertSame(
            CoverageGateEvaluator::UNIVERSE_RESOLVER_VERSION,
            $coverage['coverage_universe_resolver_version'],
            'a non-evaluable record dropped the resolver identity'
        );
        $this->assertArrayHasKey('coverage_calendar_revision_id', $coverage);
        $this->assertArrayHasKey('coverage_trading_status_revision_ids', $coverage);
    }

    /**
     * The identity is never invented. An unresolvable calendar leaves the field null and visible
     * rather than defaulted, because a record claiming a revision it did not use would be worse
     * than one admitting it has none.
     */
    public function test_an_unresolvable_calendar_leaves_the_identity_null_rather_than_defaulted(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedBar(1, self::TRADE_DATE);

        DB::table('md_market_calendar_revisions')->where('cal_date', self::TRADE_DATE)->delete();

        $coverage = $this->evaluate();

        $this->assertArrayHasKey('coverage_calendar_revision_id', $coverage);
        $this->assertNull(
            $coverage['coverage_calendar_revision_id'],
            'a coverage record must not report a calendar revision it could not resolve'
        );
        $this->assertSame(
            CoverageGateEvaluator::UNIVERSE_RESOLVER_VERSION,
            $coverage['coverage_universe_resolver_version'],
            'the resolver identity is known regardless of the calendar and must survive'
        );
    }
    /** A listing with the full identity chain the expectation resolver reads. */
    private function seedGovernedListing(int $n, int $tickerId): int
    {
        $recordedAt = '2026-03-01 00:00:00';

        $issuerId = DB::table('md_issuers')->insertGetId([
            'issuer_uid' => 'ISSUER-'.$n, 'legal_name' => 'Issuer '.$n,
            'recorded_at' => $recordedAt, 'created_at' => $recordedAt,
        ]);
        $instrumentId = DB::table('md_instruments')->insertGetId([
            'instrument_uid' => 'INSTRUMENT-'.$n, 'issuer_id' => $issuerId,
            'instrument_type' => 'EQUITY', 'currency_code' => 'IDR',
            'recorded_at' => $recordedAt, 'created_at' => $recordedAt,
        ]);
        $listingId = (int) DB::table('md_listings')->insertGetId([
            'listing_uid' => 'LISTING-'.$n, 'legacy_ticker_id' => $tickerId,
            'instrument_id' => $instrumentId, 'exchange_code' => 'IDX',
            'market_segment' => 'REGULAR', 'board_code' => 'MAIN',
            'listed_date' => '2023-01-02', 'delisted_date' => null,
            'listing_state' => 'LISTED', 'recorded_at' => $recordedAt, 'created_at' => $recordedAt,
        ]);
        DB::table('md_listing_boards')->insert([
            'listing_id' => $listingId, 'market_segment' => 'REGULAR', 'board_code' => 'MAIN',
            'effective_from' => '2023-01-02 00:00:00', 'effective_to' => null,
            'recorded_at' => $recordedAt, 'change_reason' => 'LEGACY_MASTER_PROJECTION',
        ]);
        DB::table('md_listing_symbols')->insert([
            'listing_id' => $listingId, 'symbol' => 'AAA'.$n, 'symbol_type' => 'EXCHANGE',
            'effective_from' => '2023-01-02 00:00:00', 'effective_to' => null,
            'recorded_at' => $recordedAt,
        ]);

        return $listingId;
    }

    /** A verified exchange-authoritative full-session suspension: the only lawful NOT_EXPECTED. */
    private function seedVerifiedSuspension(int $listingId, int $instrumentId, string $salt): int
    {
        $hash = str_repeat(substr(md5($salt), 0, 1), 64);
        $observationId = DB::table('md_source_observations')->insertGetId([
            'observation_uid' => hash('sha256', 'b15-status-'.$salt),
            'attempt_uid' => hash('sha256', 'b15-attempt-'.$salt),
            'requested_trade_date' => self::TRADE_DATE,
            'source_mode' => 'authority_document', 'source_name' => 'IDX', 'provider' => 'IDX',
            'sanitized_request_identity' => 'fixture://b15-status-'.$salt,
            'acquired_at' => '2026-07-20 00:00:00', 'adapter_version' => 'test-status-v1',
            'payload_hash' => $hash, 'outcome_state' => 'ACCEPTED',
            'validation_state' => 'PASSED', 'created_at' => '2026-07-20 00:00:00',
        ]);

        return (int) DB::table('md_trading_status_revisions')->insertGetId([
            'listing_id' => $listingId, 'instrument_id' => $instrumentId,
            'status_event_uid' => hash('sha256', 'b15-status-event-'.$salt),
            'status_type_code' => 'SUSPENSION_OBSERVED', 'status_code' => 'SUSPENSION_OBSERVED',
            'bar_expectation_state' => 'BAR_NOT_EXPECTED', 'board_code' => 'MAIN',
            'authority_class' => 'EXCHANGE_AUTHORITATIVE', 'source_name' => 'IDX_OFFICIAL',
            'source_payload_hash' => $hash, 'full_session_verified' => 1,
            'effective_from' => self::TRADE_DATE.' 00:00:00',
            'recorded_at' => '2026-07-20 00:00:00', 'source_observation_id' => $observationId,
            'source_ref' => 'https://www.idx.co.id/status/'.self::TRADE_DATE,
            'verification_state' => 'VERIFIED', 'observed_at' => self::TRADE_DATE.' 00:00:00',
            'announced_at' => self::TRADE_DATE.' 00:00:00',
        ]);
    }

    /**
     * The bound status revisions are the ones that actually informed the decision, not an empty
     * array that satisfies a type check.
     *
     * A probe that gutted the revision lookup left the earlier assertions green, because
     * `assertIsArray` is true of `[]`. That is a clause of the contract nothing was holding.
     */
    public function test_the_bound_status_revisions_are_the_ones_that_resolved_the_expectation(): void
    {
        $listingId = $this->seedGovernedListing(1, 1);
        $instrumentId = (int) DB::table('md_listings')->where('listing_id', $listingId)->value('instrument_id');
        $this->seedTicker(1, 'AAA1');

        $revisionId = $this->seedVerifiedSuspension($listingId, $instrumentId, 'aaa1');

        $coverage = $this->evaluate();

        $this->assertNotSame(
            [],
            $coverage['coverage_trading_status_revision_ids'],
            'a suspension resolved the expectation, so its revision must appear on the coverage record'
        );
        $this->assertContains(
            $revisionId,
            $coverage['coverage_trading_status_revision_ids'],
            'the coverage record binds different status revisions than the ones that resolved expectation'
        );
    }
}

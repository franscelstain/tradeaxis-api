<?php

use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * Coverage must be measured against the publication being built, not the one already live.
 *
 * `CoverageGateCandidateScopeHardeningStaticGuardTest` asserted that
 * loadCandidateScopedBarTickerIdsForTradeDate is named in the repository and that a few
 * live-current fallbacks are absent from the file. Those prohibitions are worth keeping. What
 * nothing checked is what the method returns — and every existing coverage evaluator test mocks
 * EodArtifactRepository, so the real scoping query had no coverage at all.
 *
 * The failure it prevents is specific and silent. During a promote or a correction a candidate
 * publication is assembled alongside the current one. If the coverage count reads bars belonging
 * to the live publication instead of the candidate, a candidate missing half the universe still
 * reports full coverage, passes the gate, and is promoted over a complete dataset. Every check
 * downstream agrees, because they are all reading the same wrong number.
 */
class CandidateCoverageScopeTest extends TestCase
{
    use UsesMarketDataSqlite;

    private const TRADE_DATE = '2026-03-20';
    private const LIVE_PUBLICATION = 10;
    private const CANDIDATE_PUBLICATION = 11;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
        $this->seedPublications();
    }

    protected function tearDown(): void
    {
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    private function seedBar(string $table, int $publicationId, int $tickerId, string $tradeDate = self::TRADE_DATE): void
    {
        $row = [
            'trade_date' => $tradeDate,
            'ticker_id' => $tickerId,
            'open' => 100,
            'high' => 110,
            'low' => 90,
            'close' => 105,
            'volume' => 1000,
            'adj_close' => 105,
            'source' => 'api',
            'run_id' => 25,
            'publication_id' => $publicationId,
            'created_at' => $tradeDate.' 17:20:00',
        ];

        if ($table === 'eod_bars_history') {
            unset($row['publication_id']);
            $row = ['publication_id' => $publicationId] + $row;
        }

        DB::table($table)->insert($row);
    }

    /**
     * The live publication carries the full universe of three tickers. The candidate carries two:
     * ticker 3 failed to arrive in the run that is being promoted.
     */
    private function seedPublications(): void
    {
        foreach ([1, 2, 3] as $tickerId) {
            $this->seedBar('eod_bars', self::LIVE_PUBLICATION, $tickerId);
            $this->seedBar('eod_bars_history', self::LIVE_PUBLICATION, $tickerId);
        }

        foreach ([1, 2] as $tickerId) {
            $this->seedBar('eod_bars_history', self::CANDIDATE_PUBLICATION, $tickerId);
        }
    }

    private function repository(): EodArtifactRepository
    {
        return new EodArtifactRepository();
    }

    /**
     * The one that matters: an incomplete candidate must not borrow the live publication's
     * coverage.
     */
    public function test_a_candidate_is_counted_by_its_own_bars_not_the_live_publication(): void
    {
        $this->assertSame(
            [1, 2],
            $this->repository()->loadCanonicalBarTickerIdsForTradeDate(self::TRADE_DATE, self::CANDIDATE_PUBLICATION),
            'The candidate is missing ticker 3 and must be counted as missing it.'
        );
    }

    public function test_the_live_publication_is_counted_by_its_own_bars(): void
    {
        $this->assertSame(
            [1, 2, 3],
            $this->repository()->loadCanonicalBarTickerIdsForTradeDate(self::TRADE_DATE, self::LIVE_PUBLICATION)
        );
    }

    /**
     * A publication that has written nothing yet counts as nothing. Inheriting the trade date's
     * other bars would let an empty candidate report the coverage of a dataset it never produced.
     */
    public function test_a_publication_with_no_bars_counts_as_empty(): void
    {
        $this->assertSame(
            [],
            $this->repository()->loadCanonicalBarTickerIdsForTradeDate(self::TRADE_DATE, 99)
        );
    }

    /**
     * With no publication named, the question is a different one — what does the canonical live
     * artifact hold for this date — and it is answered from eod_bars.
     */
    public function test_an_unscoped_request_reads_the_canonical_live_artifact(): void
    {
        $this->assertSame(
            [1, 2, 3],
            $this->repository()->loadCanonicalBarTickerIdsForTradeDate(self::TRADE_DATE, null)
        );
    }

    /**
     * A candidate's bars can sit in history, in the live table, or in both while a promote is in
     * flight. All of them count, and a ticker present in both counts once — double counting would
     * inflate coverage above the universe and trip the invariant guard instead of the gate.
     */
    public function test_bars_are_unioned_across_history_and_current_without_double_counting(): void
    {
        $this->seedBar('eod_bars_history', self::CANDIDATE_PUBLICATION, 4);
        $this->seedBar('eod_bars', self::CANDIDATE_PUBLICATION, 4);
        $this->seedBar('eod_bars', self::CANDIDATE_PUBLICATION, 5);

        $this->assertSame(
            [1, 2, 4, 5],
            $this->repository()->loadCanonicalBarTickerIdsForTradeDate(self::TRADE_DATE, self::CANDIDATE_PUBLICATION)
        );
    }

    /**
     * Scoping is by publication and date together. A candidate that wrote bars for a neighbouring
     * date must not have them counted toward this one.
     */
    public function test_bars_from_another_trade_date_are_not_counted(): void
    {
        $this->seedBar('eod_bars_history', self::CANDIDATE_PUBLICATION, 7, '2026-03-19');

        $this->assertSame(
            [1, 2],
            $this->repository()->loadCanonicalBarTickerIdsForTradeDate(self::TRADE_DATE, self::CANDIDATE_PUBLICATION)
        );
    }

    /**
     * Ticker ids are returned as sorted integers. The coverage gate compares this list against
     * the universe by id, so a string "10" that does not match an integer 10 would be reported as
     * a missing ticker.
     */
    public function test_ticker_ids_are_returned_as_sorted_integers(): void
    {
        $this->seedBar('eod_bars_history', self::CANDIDATE_PUBLICATION, 30);
        $this->seedBar('eod_bars_history', self::CANDIDATE_PUBLICATION, 4);

        $ids = $this->repository()->loadCanonicalBarTickerIdsForTradeDate(self::TRADE_DATE, self::CANDIDATE_PUBLICATION);

        $this->assertSame([1, 2, 4, 30], $ids);

        foreach ($ids as $id) {
            $this->assertIsInt($id);
        }
    }

    /**
     * A publication id arriving as a string from a query result must scope the same way. The
     * pipeline reads it off a database row, so this is the normal case rather than an edge one.
     */
    public function test_a_publication_id_given_as_a_string_scopes_identically(): void
    {
        $this->assertSame(
            $this->repository()->loadCanonicalBarTickerIdsForTradeDate(self::TRADE_DATE, self::CANDIDATE_PUBLICATION),
            $this->repository()->loadCanonicalBarTickerIdsForTradeDate(self::TRADE_DATE, (string) self::CANDIDATE_PUBLICATION)
        );
    }
}

<?php

use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * Behavioural cover for how a bar replacement classifies what changed.
 *
 * `OutOfOrderImportImpactStaticGuardTest` asserted that `$mutationSummary = ...` appears before
 * `->delete();` inside replaceBars, and that four count field names exist in the repository. The
 * ordering check is a real property expressed as a string offset comparison; the field names say
 * nothing about whether the counts are right.
 *
 * The classification is what drives reprocessing. `changed_bar_count` decides whether the impact
 * resolver walks forward and recomputes dependent publications, and the fifty trading day
 * dependency horizon exists because a single corrected bar moves MA20, MA50, HH20 and ROC20 for
 * every session that includes it. A bar wrongly classified unchanged leaves roughly ten weeks of
 * indicators stale on a publication that still reports READABLE.
 *
 * The delete ordering matters for one classification in particular. If the rows were deleted
 * before the comparison, the previous state would be empty: everything incoming would look
 * inserted, and a ticker that vanished from the feed would never be reported removed at all.
 */
class BarMutationSummaryClassificationTest extends TestCase
{
    use UsesMarketDataSqlite;

    private const TRADE_DATE = '2026-05-01';

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
        Carbon::setTestNow('2026-05-27 09:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    private function bar(int $tickerId, array $override = []): array
    {
        return array_merge([
            'trade_date' => self::TRADE_DATE,
            'ticker_id' => $tickerId,
            'open' => 100,
            'high' => 110,
            'low' => 90,
            'close' => 105,
            'volume' => 1000,
            'adj_close' => 105,
            'source' => 'API_FREE',
            'run_id' => 20,
            'publication_id' => 77,
            'created_at' => Carbon::now()->toDateTimeString(),
        ], $override);
    }

    private function seedExisting(array $rows): void
    {
        DB::table('eod_bars')->insert($rows);
    }

    private function replaceWith(array $rows): array
    {
        return (new EodArtifactRepository())->replaceBars(self::TRADE_DATE, 77, 20, $rows, []);
    }

    public function test_a_ticker_absent_before_is_classified_inserted(): void
    {
        $this->seedExisting([$this->bar(1)]);

        $summary = $this->replaceWith([$this->bar(1), $this->bar(2)]);

        $this->assertSame(1, $summary['inserted_bar_count']);
        $this->assertSame(0, $summary['updated_bar_count']);
        $this->assertSame(1, $summary['unchanged_bar_count']);
        $this->assertSame(0, $summary['removed_bar_count']);
        $this->assertSame([2], $summary['changed_ticker_ids']);
    }

    public function test_a_ticker_whose_values_moved_is_classified_updated(): void
    {
        $this->seedExisting([$this->bar(1)]);

        $summary = $this->replaceWith([$this->bar(1, ['close' => 999])]);

        $this->assertSame(0, $summary['inserted_bar_count']);
        $this->assertSame(1, $summary['updated_bar_count']);
        $this->assertSame(0, $summary['unchanged_bar_count']);
        $this->assertSame([1], $summary['changed_ticker_ids']);
    }

    /**
     * The classification that only survives because the comparison happens before the delete.
     * A ticker dropped from the feed must be reported as removed, not silently disappear.
     */
    public function test_a_ticker_dropped_from_the_incoming_set_is_classified_removed(): void
    {
        $this->seedExisting([$this->bar(1), $this->bar(2), $this->bar(3)]);

        $summary = $this->replaceWith([$this->bar(1), $this->bar(2)]);

        $this->assertSame(1, $summary['removed_bar_count']);
        $this->assertSame(0, $summary['inserted_bar_count']);
        $this->assertSame(2, $summary['unchanged_bar_count']);
        $this->assertSame([3], $summary['changed_ticker_ids']);

        // And the row really is gone, so the count is describing the outcome rather than an
        // intention.
        $this->assertSame(0, DB::table('eod_bars')->where('ticker_id', 3)->count());
    }

    /**
     * Every changed classification must reach changed_bar_count, and unchanged must not.
     * changed_bar_count is the signal the impact resolver acts on.
     */
    public function test_changed_count_covers_inserted_updated_and_removed_but_not_unchanged(): void
    {
        $this->seedExisting([$this->bar(1), $this->bar(2), $this->bar(3)]);

        $summary = $this->replaceWith([
            $this->bar(1),                      // unchanged
            $this->bar(2, ['close' => 250]),    // updated
            $this->bar(4),                      // inserted
                                                // ticker 3 removed
        ]);

        $this->assertSame(1, $summary['inserted_bar_count']);
        $this->assertSame(1, $summary['updated_bar_count']);
        $this->assertSame(1, $summary['unchanged_bar_count']);
        $this->assertSame(1, $summary['removed_bar_count']);

        $this->assertSame(3, $summary['changed_bar_count']);
        $this->assertSame([2, 3, 4], $summary['changed_ticker_ids']);
    }

    /**
     * Bars are stored as DECIMAL and read back as strings. Comparing raw values would make every
     * republish of identical data report the whole universe as updated, which would trigger a
     * fifty day reprocess on every run and make the impact signal meaningless.
     *
     * @dataProvider equivalentNumericShapes
     */
    public function test_numeric_shape_variation_is_not_a_change(array $override, string $why): void
    {
        $this->seedExisting([$this->bar(1)]);

        $summary = $this->replaceWith([$this->bar(1, $override)]);

        $this->assertSame(0, $summary['changed_bar_count'], 'must not be a change: '.$why);
        $this->assertSame(1, $summary['unchanged_bar_count']);
        $this->assertSame([], $summary['changed_trade_dates']);
    }

    public function equivalentNumericShapes(): array
    {
        return [
            'decimal string round trip' => [['close' => '105.0000'], 'the same price read back from DECIMAL(20,4)'],
            'float and integer' => [['volume' => 1000.0], 'volume as a float rather than an int'],
            'trailing zeros' => [['open' => '100.00000000'], 'extra trailing zeros'],
            'string integer' => [['high' => '110'], 'a numeric string'],
            'lowercase source' => [['source' => 'api_free'], 'source case is normalised'],
        ];
    }

    /**
     * A genuinely different price must not be normalised away. The tolerance above exists for
     * representation, not for value.
     */
    public function test_a_price_difference_below_one_unit_is_still_a_change(): void
    {
        $this->seedExisting([$this->bar(1, ['close' => 105])]);

        $summary = $this->replaceWith([$this->bar(1, ['close' => 105.0001])]);

        $this->assertSame(1, $summary['updated_bar_count']);
        $this->assertSame(1, $summary['changed_bar_count']);
    }

    /**
     * When nothing moved, no trade date is reported as changed. The reprocess executor keys off
     * this list, so a spurious entry would recompute a date that did not need it — and an absent
     * one leaves a date that did.
     */
    public function test_changed_trade_dates_is_empty_when_nothing_moved_and_named_when_something_did(): void
    {
        $this->seedExisting([$this->bar(1)]);

        $this->assertSame([], $this->replaceWith([$this->bar(1)])['changed_trade_dates']);
        $this->assertSame([self::TRADE_DATE], $this->replaceWith([$this->bar(1, ['low' => 1])])['changed_trade_dates']);
    }
}

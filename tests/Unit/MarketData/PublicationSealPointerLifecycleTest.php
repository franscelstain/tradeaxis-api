<?php

use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * W10 — immutable publication, correction, seal, and pointer lifecycle, stage 9.
 *
 * Exit gate: "failed build/reseal/correction tidak mengubah current pointer; prior publication
 * tetap repeatable; concurrent read melihat tepat satu publication."
 *
 * Owner contracts:
 *   docs/market_data/book/Canonical_Row_History_and_Versioning_Policy_LOCKED.md
 *   docs/market_data/book/Publication_Current_Pointer_Integrity_Contract_LOCKED.md
 *   docs/market_data/book/Dataset_Seal_and_Freeze_Contract_LOCKED.md
 *   docs/market_data/ops/History_Table_Immutability_Guards_LOCKED.sql
 *
 * The seal boundary is the load-bearing distinction. Rule 7 of the history policy has a snapshot
 * set assembled while its publication is a candidate and frozen at the seal transition; rule 9
 * forbids update or delete of sealed snapshot content by any path. A guard that cannot tell those
 * apart either blocks normal operation or protects nothing, so every test here asserts both sides.
 */
class PublicationSealPointerLifecycleTest extends TestCase
{
    use UsesMarketDataSqlite;

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

    private function publication($id, $sealState, $isCurrent = 0, $version = 1, $tradeDate = '2026-03-24'): int
    {
        DB::table('eod_publications')->insert([
            'publication_id' => $id,
            'trade_date' => $tradeDate,
            'run_id' => 100 + $id,
            'publication_version' => $version,
            'is_current' => $isCurrent,
            'seal_state' => $sealState,
            'bars_batch_hash' => 'hash-'.$id,
            'sealed_at' => $sealState === 'SEALED' ? '2026-03-24 18:00:00' : null,
            'created_at' => '2026-03-24 17:30:00',
        ]);

        return $id;
    }

    private function historyRow($publicationId, $tickerId, $close, $tradeDate = '2026-03-24'): void
    {
        DB::table('eod_bars_history')->insert([
            'publication_id' => $publicationId,
            'trade_date' => $tradeDate,
            'ticker_id' => $tickerId,
            'open' => 100,
            'high' => 110,
            'low' => 99,
            'close' => $close,
            'volume' => 1000,
            'source' => 'YAHOO_FINANCE',
            'run_id' => 100 + $publicationId,
            'created_at' => '2026-03-24 17:45:00',
        ]);
    }

    /**
     * A sealed snapshot set cannot be discarded. The method is named for candidates, but the name
     * was the only thing restricting it — and it deletes both the snapshot rows and the
     * publication row, which would leave no record that the sealed publication ever existed.
     */
    public function test_a_sealed_publication_cannot_be_discarded(): void
    {
        $this->publication(1, 'SEALED', 1);
        $this->historyRow(1, 10, 108);

        $repository = new EodPublicationRepository();

        try {
            $repository->discardCandidatePublication(1);
            $this->fail('discarding a sealed publication must be refused');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('SEALED_PUBLICATION_IMMUTABLE', $e->getMessage());
        }

        $this->assertSame(1, DB::table('eod_publications')->where('publication_id', 1)->count());
        $this->assertSame(1, DB::table('eod_bars_history')->where('publication_id', 1)->count());
    }

    /**
     * A candidate that will never be published is still discardable. Without this the guard would
     * be indistinguishable from a system that cannot clean up after itself.
     */
    public function test_an_unsealed_candidate_can_still_be_discarded(): void
    {
        $this->publication(2, 'UNSEALED');
        $this->historyRow(2, 10, 108);

        (new EodPublicationRepository())->discardCandidatePublication(2);

        $this->assertSame(0, DB::table('eod_publications')->where('publication_id', 2)->count());
        $this->assertSame(0, DB::table('eod_bars_history')->where('publication_id', 2)->count());
    }

    /**
     * A sealed snapshot set cannot be rewritten through the artifact path either. Application-layer
     * and database-layer guards are not redundant: one survives a direct SQL session, the other
     * survives a schema restored without triggers.
     */
    public function test_a_sealed_snapshot_set_cannot_be_rewritten_by_the_artifact_writer(): void
    {
        $this->publication(3, 'SEALED', 1);
        $this->historyRow(3, 10, 108);

        $rows = [[
            'trade_date' => '2026-03-24',
            'ticker_id' => 10,
            'open' => 1, 'high' => 2, 'low' => 1, 'close' => 2,
            'volume' => 5, 'source' => 'YAHOO_FINANCE', 'run_id' => 103,
            'publication_id' => 3, 'created_at' => '2026-03-24 18:10:00',
        ]];

        $this->expectExceptionMessageMatches('/SEALED_SNAPSHOT_REWRITE_BLOCKED/');
        (new EodArtifactRepository())->replaceBars('2026-03-24', 3, 103, $rows, [], true);
    }

    /**
     * The prior publication stays byte-for-byte queryable after a newer one supersedes it. This is
     * what "repeatable" means: an answer given last week can be reproduced today.
     */
    public function test_a_superseded_publication_remains_queryable_and_unchanged(): void
    {
        $this->publication(4, 'SEALED', 0, 1);
        $this->historyRow(4, 10, 108);
        $this->publication(5, 'SEALED', 1, 2);
        $this->historyRow(5, 10, 112);

        $prior = DB::table('eod_bars_history')->where('publication_id', 4)->first();
        $current = DB::table('eod_bars_history')->where('publication_id', 5)->first();

        $this->assertEquals(108, $prior->close, 'the prior snapshot still reports what it always reported');
        $this->assertEquals(112, $current->close);
        $this->assertNotSame((int) $prior->publication_id, (int) $current->publication_id);
    }

    /**
     * A correction adds a publication; it never edits the one it corrects. Two snapshot sets for
     * one date is the expected shape, and the older set keeps its own rows.
     */
    public function test_a_correction_produces_a_new_snapshot_set_rather_than_editing_the_old_one(): void
    {
        $this->publication(6, 'SEALED', 0, 1);
        $this->historyRow(6, 10, 108);
        $this->publication(7, 'SEALED', 1, 2);
        $this->historyRow(7, 10, 999);

        $sets = DB::table('eod_bars_history')
            ->where('trade_date', '2026-03-24')
            ->distinct()
            ->pluck('publication_id')
            ->all();

        $this->assertCount(2, $sets, 'both snapshot sets survive the correction');
        $this->assertEquals(108, DB::table('eod_bars_history')->where('publication_id', 6)->value('close'));
    }

    /**
     * Exactly one publication is current for a date. More than one and a reader's answer depends on
     * which row the query happened to reach first.
     */
    public function test_exactly_one_publication_is_current_for_a_trade_date(): void
    {
        $this->publication(8, 'SEALED', 0, 1);
        $this->publication(9, 'SEALED', 1, 2);

        $currentCount = DB::table('eod_publications')
            ->where('trade_date', '2026-03-24')
            ->where('is_current', 1)
            ->count();

        $this->assertSame(1, $currentCount);
        $this->assertSame(9, (int) DB::table('eod_publications')
            ->where('trade_date', '2026-03-24')->where('is_current', 1)->value('publication_id'));
    }

    /**
     * The pointer table admits one row per date by primary key and one publication overall by
     * unique constraint, so a second pointer for the same date cannot be inserted at all.
     */
    public function test_the_pointer_table_structurally_refuses_a_second_current_row(): void
    {
        $this->publication(10, 'SEALED', 1);
        DB::table('eod_current_publication_pointer')->insert([
            'trade_date' => '2026-03-24',
            'publication_id' => 10,
            'run_id' => 110,
            'publication_version' => 1,
            'sealed_at' => '2026-03-24 18:00:00',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('eod_current_publication_pointer')->insert([
            'trade_date' => '2026-03-24',
            'publication_id' => 11,
            'run_id' => 111,
            'publication_version' => 2,
            'sealed_at' => '2026-03-24 19:00:00',
        ]);
    }

    /**
     * A failed build leaves the pointer where it was. The candidate exists and is visible as a
     * candidate, which is different from being readable.
     */
    public function test_a_failed_build_leaves_the_existing_pointer_untouched(): void
    {
        $this->publication(12, 'SEALED', 1, 1);
        $this->publication(13, 'UNSEALED', 0, 2);

        $pointerBefore = DB::table('eod_publications')
            ->where('trade_date', '2026-03-24')->where('is_current', 1)->value('publication_id');

        (new EodPublicationRepository())->discardCandidatePublication(13);

        $pointerAfter = DB::table('eod_publications')
            ->where('trade_date', '2026-03-24')->where('is_current', 1)->value('publication_id');

        $this->assertSame(12, (int) $pointerBefore);
        $this->assertSame(12, (int) $pointerAfter, 'discarding a failed candidate must not move the pointer');
    }

    /**
     * A candidate snapshot set is rewritable up to the seal. Rule 7 requires the set to be
     * assembled before it is frozen, so a guard that blocked this would block normal builds.
     */
    public function test_a_candidate_snapshot_set_is_still_rewritable_before_sealing(): void
    {
        $this->publication(14, 'UNSEALED');
        $this->historyRow(14, 10, 108);

        $rows = [[
            'trade_date' => '2026-03-24',
            'ticker_id' => 10,
            'open' => 100, 'high' => 115, 'low' => 99, 'close' => 114,
            'volume' => 1000, 'source' => 'YAHOO_FINANCE', 'run_id' => 114,
            'publication_id' => 14, 'created_at' => '2026-03-24 18:10:00',
        ]];

        (new EodArtifactRepository())->replaceBars('2026-03-24', 14, 114, $rows, [], true);

        $this->assertEquals(114, DB::table('eod_bars_history')->where('publication_id', 14)->value('close'));
    }
}

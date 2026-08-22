<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Board and market segment become temporal facts, the way symbols and provider mappings already are.
 *
 * `Tickers_and_Identity_Dependency_Contract_LOCKED.md` requires point-in-time resolution to return
 * the market segment and board valid on `T`, and requires board or market-segment movement to be
 * effective-dated without rewriting the prior listing context.
 * `Symbol_Lifecycle_and_Mapping_Contract.md` requires Regular-Market observations to retain the
 * listing/board context valid on their trade date.
 *
 * `md_listings.board_code` and `md_listings.market_segment` are single columns. There was no way to
 * record a move except to overwrite one of them, which changes the answer for every historical date
 * at once, and the universe query filtered on the current segment — so a listing that was Regular on
 * `T` and moved afterwards silently left `T`'s universe. That is the defect the `is_active` boundary
 * describes, in two columns it does not name.
 *
 * The change is additive. Nothing is dropped and nothing is rewritten: the existing columns stay as
 * the cached current-state projection the contract permits, and the new table becomes the only thing
 * historical resolution reads. Both new intervals are opened from what the listing already records,
 * so this migration states no fact the master did not already assert.
 */
class AddTemporalListingBoardIntervals extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('md_listings')) {
            return;
        }

        if (! Schema::hasTable('md_listing_boards')) {
            Schema::create('md_listing_boards', function (Blueprint $table) {
                $table->bigIncrements('listing_board_id');
                $table->unsignedBigInteger('listing_id');
                $table->string('market_segment', 32);
                $table->string('board_code', 16)->nullable();
                $table->dateTime('effective_from');
                $table->dateTime('effective_to')->nullable();
                $table->dateTime('recorded_at');
                $table->dateTime('retracted_at')->nullable();
                $table->unsignedBigInteger('source_observation_id')->nullable();
                $table->string('source_ref', 255)->nullable();
                $table->string('change_reason', 64)->nullable();
                $table->unique(['listing_id', 'effective_from', 'recorded_at'], 'uq_md_listing_board_revision');
                $table->index(['listing_id', 'effective_from', 'effective_to'], 'idx_md_listing_board_effective');
                $table->index(['market_segment', 'effective_from', 'effective_to'], 'idx_md_listing_board_segment');
            });
        }

        $this->openIntervalsForExistingListings();
    }

    /**
     * Every listing that already exists gets its opening interval from its own recorded dates.
     *
     * A listing left without an interval would become unresolvable the moment the temporal record
     * became required, and the fix for that must not be a fallback to the current column — that is
     * the behavior being removed. So the interval is derived, from `listed_date` to `delisted_date`,
     * carrying the segment and board the row already holds. No value is invented: a listing whose
     * segment is null is skipped and stays unresolved rather than being assigned a default.
     */
    private function openIntervalsForExistingListings()
    {
        $existing = DB::table('md_listing_boards')->pluck('listing_id')->all();
        $skip = array_flip(array_map('intval', $existing));

        foreach (DB::table('md_listings')->orderBy('listing_id')->get() as $listing) {
            if (isset($skip[(int) $listing->listing_id])) {
                continue;
            }
            $segment = trim((string) ($listing->market_segment ?? ''));
            if ($segment === '') {
                continue;
            }

            DB::table('md_listing_boards')->insert([
                'listing_id' => (int) $listing->listing_id,
                'market_segment' => $segment,
                'board_code' => $listing->board_code ?? null,
                'effective_from' => (string) $listing->listed_date.' 00:00:00',
                'effective_to' => $listing->delisted_date ? (string) $listing->delisted_date.' 00:00:00' : null,
                'recorded_at' => (string) ($listing->recorded_at ?? ((string) $listing->listed_date.' 00:00:00')),
                'retracted_at' => null,
                'source_observation_id' => null,
                'source_ref' => (string) ($listing->source_ref ?? 'md_listings:listing_id='.(int) $listing->listing_id),
                'change_reason' => 'LEGACY_MASTER_PROJECTION',
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('md_listing_boards');
    }
}

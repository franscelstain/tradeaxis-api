<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `MD-B18-A002` / `F-MD-B18-A002-002` — give a delisting its own knowledge time.
 *
 * `MD-S050` classifies the eight anti-survivorship fixtures as **as-known** fixtures. The first of
 * them -- "a listing active at historical T but inactive today" -- could not be one: `delisted_date`
 * is a mutable column on a single `md_listings` row and there is no listing revision, retraction or
 * supersession anywhere, so the delisting carried no `recorded_at` of its own. A cutoff could not
 * distinguish "delisted" from "delisted, but not yet known at T", which is precisely the
 * distinction survivorship bias lives in: a universe rebuilt today loses companies whose delisting
 * the platform had not yet learned at the moment being replayed.
 *
 * The fix is one nullable column rather than a revision series. `md_listing_symbols` models symbol
 * changes as revisions because a symbol genuinely changes over and over; a listing is delisted once,
 * and the only knowledge-bearing fact on the row is *when that was learned*. Turning `md_listings`
 * into a revision series would also fracture listing identity -- `listing_id` is the primary key and
 * `listing_uid` is unique, and identity surviving a rename is the thing the other anti-survivorship
 * fixtures depend on.
 *
 * Semantics: `delisted_recorded_at` is when the delisting entered the record. It is `NULL` for a
 * listing that is not delisted. A read bounded by `knownAt` treats the delisting as invisible when
 * `delisted_recorded_at` is `NULL` or later than the cutoff, so the listing stays in the historical
 * universe exactly as it did at that moment.
 *
 * Backfill: existing delisted rows are given `delisted_recorded_at = recorded_at`. That is the
 * honest default -- the row's own recording moment is the only knowledge time the corpus has for
 * them -- and it is deliberately not `NULL`, because `NULL` would mean "never learned" and make
 * every historical delisting invisible to every cutoff.
 */
return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('md_listings')) {
            return;
        }

        if (! Schema::hasColumn('md_listings', 'delisted_recorded_at')) {
            Schema::table('md_listings', function (Blueprint $table) {
                $table->dateTime('delisted_recorded_at')->nullable()->after('delisted_date');
                $table->index(['delisted_date', 'delisted_recorded_at'], 'idx_md_listing_delisted_known');
            });

            \Illuminate\Support\Facades\DB::table('md_listings')
                ->whereNotNull('delisted_date')
                ->whereNull('delisted_recorded_at')
                ->update(['delisted_recorded_at' => \Illuminate\Support\Facades\DB::raw('recorded_at')]);
        }
    }

    public function down()
    {
        if (! Schema::hasTable('md_listings') || ! Schema::hasColumn('md_listings', 'delisted_recorded_at')) {
            return;
        }

        Schema::table('md_listings', function (Blueprint $table) {
            try {
                $table->dropIndex('idx_md_listing_delisted_known');
            } catch (\Throwable $e) {
                // index may not exist on a partially applied schema
            }
            $table->dropColumn('delisted_recorded_at');
        });
    }
};

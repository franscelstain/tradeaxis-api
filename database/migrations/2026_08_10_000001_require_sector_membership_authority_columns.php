<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Successor to 2026_08_08_000001, which added the sector membership authority columns but left them
 * nullable on purpose. Its own docblock states the condition: "Source authority cannot be inferred
 * safely, so it deliberately remains NULL and resolves UNKNOWN until an authoritative revision is
 * imported." That revision was imported on 2026-08-10 from checksum-verified IDX announcements, so
 * the placeholder nullability has outlived its reason and is now removed.
 *
 * Two things change beyond documentation:
 *
 * `uq_sector_membership_listing_effective_known` spans `listing_id` and `recorded_at`. MySQL does
 * not treat NULLs as equal in a unique index, so for as long as either column admitted NULL the
 * constraint could be bypassed entirely by omitting them — a uniqueness rule that was written and
 * then never enforced for exactly the rows least able to justify themselves. NOT NULL makes the
 * index mean what it says.
 *
 * `source_authority_class` is what separates an IDX announcement from a scraped reference under
 * Sector_Classification_Contract_LOCKED.md, and SectorClassificationRepository::AUTHORITATIVE_CLASSES
 * reads it to decide which rows may resolve a sector at all. A NULL there is not an unknown class;
 * it is a row that silently declines to be classified while still occupying the table.
 *
 * The NULL guard below is not a precaution against a hypothetical. This deployment runs with
 * sql_mode = NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION — STRICT_TRANS_TABLES is absent.
 * Rehearsed on tradeaxis_testing on 2026-08-10, MODIFY COLUMN ... NOT NULL over a row still holding
 * NULLs raised no error and rewrote the values instead:
 *
 *   listing_id   NULL -> 0
 *   recorded_at  NULL -> 0000-00-00 00:00:00
 *
 * Both survive as ordinary-looking data. The second is the worse of the two: an as-known coordinate
 * of zero makes the row resolve as though it had been known since the beginning of time, so every
 * point-in-time query would silently prefer it. That is the same defect as the fabricated 2021-01-25
 * effective dates corrected earlier this cycle — a visible gap replaced by an invented value. The
 * guard therefore refuses the ALTER outright; backfilling is a data decision that belongs in an
 * explicit, attributable statement, not in the side effect of a schema change.
 */
class RequireSectorMembershipAuthorityColumns extends Migration
{
    /**
     * Definitions are spelled out in full because MODIFY COLUMN restates the whole column; omitting
     * a type or length here would quietly rewrite it.
     */
    private $columns = [
        'listing_id' => ['bigint(20) unsigned', 'unsignedBigInteger'],
        'source_name' => ['varchar(64)', 'string'],
        'source_authority_class' => ['varchar(32)', 'string'],
        'recorded_at' => ['datetime', 'dateTime'],
    ];

    public function up()
    {
        if (! $this->applicable()) {
            return;
        }

        $this->assertNoNullsRemain();

        foreach ($this->columns as $column => $definition) {
            DB::statement(sprintf(
                'ALTER TABLE `ticker_sector_memberships` MODIFY COLUMN `%s` %s NOT NULL',
                $column,
                $definition[0]
            ));
        }
    }

    public function down()
    {
        if (! $this->applicable()) {
            return;
        }

        foreach ($this->columns as $column => $definition) {
            DB::statement(sprintf(
                'ALTER TABLE `ticker_sector_memberships` MODIFY COLUMN `%s` %s NULL',
                $column,
                $definition[0]
            ));
        }
    }

    /**
     * SQLite cannot alter column nullability, and the test corpus does not use these migrations to
     * build its schema — tests/Support/UsesMarketDataSqlite.php declares the mirror directly, and
     * carries the same NOT NULL shape so the constraint is exercised rather than only deployed.
     */
    private function applicable()
    {
        return Schema::hasTable('ticker_sector_memberships')
            && DB::connection()->getDriverName() === 'mysql';
    }

    private function assertNoNullsRemain()
    {
        $offenders = [];

        foreach (array_keys($this->columns) as $column) {
            $count = (int) DB::table('ticker_sector_memberships')->whereNull($column)->count();
            if ($count > 0) {
                $offenders[] = $column.'='.$count;
            }
        }

        if ($offenders) {
            throw new \RuntimeException(
                'SECTOR_MEMBERSHIP_AUTHORITY_BACKFILL_INCOMPLETE: refusing to add NOT NULL while rows '
                .'still hold NULL ('.implode(', ', $offenders).'). Backfill these rows through the '
                .'governed membership import so each value is attributable, then re-run this migration. '
                .'Letting the ALTER coerce them would replace a visible gap with a fabricated value.'
            );
        }
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Close W05/W12 persistence gaps without pretending that legacy analytical rows were recomputed.
 *
 * New columns remain nullable for the historical corpus. New publications are fail-closed by the
 * application until every analytical binding is populated; historical rows must go through the
 * governed recompute/correction lifecycle rather than receiving a metadata-only relabel.
 */
class HardenSectorMembershipAndAnalyticalProductIdentity extends Migration
{
    public function up()
    {
        $this->hardenSectorMemberships();
        $this->addAnalyticalIdentity('eod_runs');
        $this->addAnalyticalIdentity('eod_publications');
        $this->addAnalyticalIdentity('eod_indicators', true);
        $this->addAnalyticalIdentity('eod_indicators_history', true);
    }

    public function down()
    {
        foreach (['eod_indicators_history', 'eod_indicators', 'eod_publications', 'eod_runs'] as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            $columns = array_values(array_filter([
                $tableName === 'eod_runs' ? 'price_product_code' : null,
                'price_product_version',
                'factor_set_hash',
                in_array($tableName, ['eod_indicators', 'eod_indicators_history'], true) ? 'sector_membership_id' : null,
            ], function ($column) use ($tableName) {
                return $column !== null && Schema::hasColumn($tableName, $column);
            }));

            if ($columns) {
                Schema::table($tableName, function (Blueprint $table) use ($columns) {
                    $table->dropColumn($columns);
                });
            }
        }

        if (Schema::hasTable('ticker_sector_memberships')) {
            Schema::table('ticker_sector_memberships', function (Blueprint $table) {
                $table->dropUnique('uq_sector_membership_listing_effective_known');
                $table->dropIndex('idx_sector_membership_listing_effective');
                $table->dropIndex('idx_sector_membership_known_authority');
                $table->dropIndex('idx_sector_membership_supersedes');
                $table->dropColumn([
                    'listing_id', 'source_authority_class', 'recorded_at',
                    'supersedes_membership_id', 'operator_name', 'reason_code',
                ]);
                $table->unique(
                    ['ticker_id', 'classification_system', 'effective_from'],
                    'uq_ticker_sector_membership_effective_from'
                );
            });
        }
    }

    private function hardenSectorMemberships()
    {
        if (! Schema::hasTable('ticker_sector_memberships')) {
            return;
        }

        Schema::table('ticker_sector_memberships', function (Blueprint $table) {
            if (! Schema::hasColumn('ticker_sector_memberships', 'listing_id')) {
                $table->unsignedBigInteger('listing_id')->nullable()->after('ticker_id');
            }
            if (! Schema::hasColumn('ticker_sector_memberships', 'source_authority_class')) {
                $table->string('source_authority_class', 32)->nullable()->after('source_ref');
            }
            if (! Schema::hasColumn('ticker_sector_memberships', 'recorded_at')) {
                $table->dateTime('recorded_at')->nullable()->after('source_authority_class');
            }
            if (! Schema::hasColumn('ticker_sector_memberships', 'supersedes_membership_id')) {
                $table->unsignedBigInteger('supersedes_membership_id')->nullable()->after('recorded_at');
            }
            if (! Schema::hasColumn('ticker_sector_memberships', 'operator_name')) {
                $table->string('operator_name', 128)->nullable()->after('supersedes_membership_id');
            }
            if (! Schema::hasColumn('ticker_sector_memberships', 'reason_code')) {
                $table->string('reason_code', 64)->nullable()->after('operator_name');
            }
        });

        // Effective/known timestamps can be reconstructed from the legacy audit timestamp. Source
        // authority cannot be inferred safely, so it deliberately remains NULL and resolves
        // UNKNOWN until an authoritative revision is imported.
        DB::table('ticker_sector_memberships')->whereNull('recorded_at')->update([
            'recorded_at' => DB::raw('COALESCE(created_at, updated_at)'),
        ]);

        if (Schema::hasTable('md_listings') && Schema::hasColumn('md_listings', 'legacy_ticker_id')) {
            DB::statement(
                'UPDATE ticker_sector_memberships AS membership '
                .'INNER JOIN md_listings AS listing ON listing.legacy_ticker_id = membership.ticker_id '
                .'SET membership.listing_id = listing.listing_id '
                .'WHERE membership.listing_id IS NULL'
            );
        }

        if ($this->indexExists('ticker_sector_memberships', 'uq_ticker_sector_membership_effective_from')) {
            Schema::table('ticker_sector_memberships', function (Blueprint $table) {
                $table->dropUnique('uq_ticker_sector_membership_effective_from');
            });
        }

        // Idempotency is required by the migration policy in
        // `DB_Schema_And_Migration_Sync_Contract_LOCKED.md`: "include idempotency/preflight for
        // partially drifted developer databases". These four already exist in the clean-install
        // base, so adding them unconditionally made `migrate` on an empty database die on a
        // duplicate key. The migration's own indexExists() helper is used rather than a new one.
        Schema::table('ticker_sector_memberships', function (Blueprint $table) {
            if (! $this->indexExists('ticker_sector_memberships', 'uq_sector_membership_listing_effective_known')) {
                $table->unique(
                    ['listing_id', 'classification_system', 'effective_from', 'recorded_at'],
                    'uq_sector_membership_listing_effective_known'
                );
            }
            if (! $this->indexExists('ticker_sector_memberships', 'idx_sector_membership_listing_effective')) {
                $table->index(
                    ['listing_id', 'classification_system', 'effective_from', 'effective_to'],
                    'idx_sector_membership_listing_effective'
                );
            }
            if (! $this->indexExists('ticker_sector_memberships', 'idx_sector_membership_known_authority')) {
                $table->index(
                    ['recorded_at', 'source_authority_class'],
                    'idx_sector_membership_known_authority'
                );
            }
            if (! $this->indexExists('ticker_sector_memberships', 'idx_sector_membership_supersedes')) {
                $table->index('supersedes_membership_id', 'idx_sector_membership_supersedes');
            }
        });
    }

    private function addAnalyticalIdentity($tableName, $includeSectorMembership = false)
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName, $includeSectorMembership) {
            if ($tableName === 'eod_runs' && ! Schema::hasColumn($tableName, 'price_product_code')) {
                $table->string('price_product_code', 32)->nullable();
            }
            if (! Schema::hasColumn($tableName, 'price_product_version')) {
                $table->string('price_product_version', 64)->nullable();
            }
            if (! Schema::hasColumn($tableName, 'factor_set_hash')) {
                $table->char('factor_set_hash', 64)->nullable();
            }
            if ($includeSectorMembership && ! Schema::hasColumn($tableName, 'sector_membership_id')) {
                $table->unsignedBigInteger('sector_membership_id')->nullable();
            }
        });
    }

    private function indexExists($table, $indexName)
    {
        try {
            $rows = DB::select(
                'SELECT COUNT(*) AS aggregate FROM information_schema.STATISTICS '
                .'WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?',
                [$table, $indexName]
            );

            return isset($rows[0]) && (int) $rows[0]->aggregate > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }
}

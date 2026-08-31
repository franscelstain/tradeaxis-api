<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * B16 additive first-class fact dimensions for the eligibility snapshot.
 *
 * `EOD_Eligibility_Snapshot_Contract_LOCKED.md` enumerates the dimensions every row must persist
 * separately, and states the consequence of not doing so without hedging:
 *
 *   > Absence of the first-class facts is a **defect against this contract**, never a licence to
 *   > overload `reason_code`.
 *
 *   > Until the required fields exist, the snapshot is **not conformant** and any claim of
 *   > explainability made on its behalf must say so explicitly.
 *
 * Seven dimensions were persisted. Three were not:
 *
 *   - source and provenance state, so a reader could not tell whether a delivered observation was
 *     traceable to accepted source evidence without opening the bar table — which the acceptance
 *     criterion rules out, "without ... reading internal tables";
 *   - analytical price-basis and contamination state, so a contaminated window and a clean one on
 *     an unexpected price basis were indistinguishable on the eligibility row. They are two columns
 *     rather than one delimited value: packing two facts into a single string is a smaller version
 *     of the overloading this contract exists to forbid, and the deterministic hash service refuses
 *     a delimiter inside a hashed field for the same reason;
 *   - indicator validity and warm-up/nullability state, so "affected indicators NULL, warm-up state
 *     and reasons explicit" was not satisfied on the row that exists to explain the instrument.
 *
 * All three were already in memory when the row was built. The columns are additive and nullable:
 * rows published before this migration keep a null dimension rather than a back-stamped one, which
 * is the honest record of a snapshot produced before the field existed.
 */
class AddEligibilityFirstClassFactDimensions extends Migration
{
    /** @var array<int,string> */
    private $tables = ['eod_eligibility', 'eod_eligibility_history'];

    public function up()
    {
        foreach ($this->tables as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (! Schema::hasColumn($tableName, 'source_provenance_state')) {
                    $table->string('source_provenance_state', 32)->nullable();
                }
                if (! Schema::hasColumn($tableName, 'price_basis_state')) {
                    $table->string('price_basis_state', 32)->nullable();
                }
                if (! Schema::hasColumn($tableName, 'contamination_state')) {
                    $table->string('contamination_state', 32)->nullable();
                }
                if (! Schema::hasColumn($tableName, 'indicator_state')) {
                    $table->string('indicator_state', 32)->nullable();
                }
            });
        }
    }

    public function down()
    {
        foreach ($this->tables as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                foreach (['source_provenance_state', 'price_basis_state', 'contamination_state', 'indicator_state'] as $column) {
                    if (Schema::hasColumn($tableName, $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
}

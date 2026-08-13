<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * F-025: let the replay admissibility verdict be stored.
 *
 * W18 gave ReplayVerificationService a fourth answer. Beyond MATCH, MISMATCH and the expected/
 * unexpected degrade pair, a comparison can now be NOT_ADMISSIBLE — the fixture's expectation was
 * derived from the very run under verification, so agreement proves only that the run equals
 * itself. That rule was written correctly and then could not run: comparison_result stayed
 * enum('MATCH','MISMATCH','EXPECTED_DEGRADE','UNEXPECTED') from 2026_05_19_000002, so every
 * inadmissible replay died on the insert with "Warning: 1265 Data truncated for column
 * 'comparison_result'" instead of being recorded as inadmissible.
 *
 * The application side already handles the value: ReplayResultRepository::replayStatusForComparison()
 * maps anything outside the four known results to replay_status BLOCKED, which is the correct
 * reading. Only the storage vocabulary was missing.
 *
 * Widening an enum is additive — existing rows keep their values and no row is rewritten. The
 * reverse is not, which is why down() refuses rather than letting MariaDB silently truncate stored
 * verdicts under this deployment's non-strict sql_mode.
 */
class AllowNotAdmissibleReplayComparisonResult extends Migration
{
    private $withNotAdmissible = "ENUM('MATCH','MISMATCH','EXPECTED_DEGRADE','UNEXPECTED','NOT_ADMISSIBLE')";

    private $withoutNotAdmissible = "ENUM('MATCH','MISMATCH','EXPECTED_DEGRADE','UNEXPECTED')";

    public function up()
    {
        if (! $this->applicable()) {
            return;
        }

        DB::statement(
            'ALTER TABLE `md_replay_daily_metrics` MODIFY COLUMN `comparison_result` '
            .$this->withNotAdmissible.' NOT NULL'
        );
    }

    public function down()
    {
        if (! $this->applicable()) {
            return;
        }

        $stored = (int) DB::table('md_replay_daily_metrics')
            ->where('comparison_result', 'NOT_ADMISSIBLE')
            ->count();

        if ($stored > 0) {
            throw new \RuntimeException(
                'REPLAY_COMPARISON_RESULT_NARROWING_WOULD_DESTROY_VERDICTS: '.$stored.' row(s) hold '
                .'NOT_ADMISSIBLE. Narrowing the enum would blank them, turning a recorded refusal to '
                .'judge into an empty cell that reads like a clean result. Delete or reclassify those '
                .'rows deliberately first.'
            );
        }

        DB::statement(
            'ALTER TABLE `md_replay_daily_metrics` MODIFY COLUMN `comparison_result` '
            .$this->withoutNotAdmissible.' NOT NULL'
        );
    }

    /**
     * SQLite has no enum; tests/Support/UsesMarketDataSqlite.php declares the mirror column as a
     * plain string, so the constraint this migration fixes never existed there. That is also why
     * the proof for this finding lives in a MariaDB-backed test rather than the SQLite suite.
     */
    private function applicable()
    {
        return Schema::hasTable('md_replay_daily_metrics')
            && DB::connection()->getDriverName() === 'mysql';
    }
}

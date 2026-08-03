<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Reserved migration sequence; intentionally performs no schema change.
 *
 * The original draft added fields for rewriting eod_bars and eod_bars_history after a
 * detected scale break. That behavior is prohibited: a detector may quarantine evidence,
 * while a correction must create verified event/factor revisions and a new publication.
 */
class AddRepairTrailToPriceScaleBreaks extends Migration
{
    public function up()
    {
        // Intentionally empty. Never restore an in-place price-repair surface here.
    }

    public function down()
    {
        // Intentionally empty.
    }
}

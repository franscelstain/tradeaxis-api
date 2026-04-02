<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EodRun extends Model
{
    protected $table = 'eod_runs';

    protected $primaryKey = 'run_id';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'coverage_universe_count' => 'integer',
        'coverage_available_count' => 'integer',
        'coverage_missing_count' => 'integer',
        'coverage_ratio' => 'float',
        'coverage_min_threshold' => 'float',
        'coverage_missing_sample_json' => 'array',
        'bars_rows_written' => 'integer',
        'indicators_rows_written' => 'integer',
        'eligibility_rows_written' => 'integer',
        'invalid_bar_count' => 'integer',
        'invalid_indicator_count' => 'integer',
        'hard_reject_count' => 'integer',
        'warning_count' => 'integer',
    ];
}

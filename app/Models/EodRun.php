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
        'source_timeout_seconds' => 'integer',
        'source_retry_max' => 'integer',
        'source_attempt_count' => 'integer',
        'source_success_after_retry' => 'boolean',
        'source_retry_exhausted' => 'boolean',
        'source_final_http_status' => 'integer',
        'publication_id' => 'integer',
        'correction_id' => 'integer',
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EodRun extends Model
{
    protected $table = 'eod_runs';

    protected $primaryKey = 'run_id';

    public $timestamps = false;

    protected $guarded = [];
}

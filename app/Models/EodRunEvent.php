<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EodRunEvent extends Model
{
    protected $table = 'eod_run_events';

    protected $primaryKey = 'event_id';

    public $timestamps = false;

    protected $guarded = [];
}

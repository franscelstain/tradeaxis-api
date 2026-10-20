<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EodRunEvent extends Model
{
    protected $table = 'eod_run_events';

    protected $primaryKey = 'event_id';

    public $timestamps = false;

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();
        static::updating(static function () { throw new \RuntimeException('RUN_EVENT_APPEND_ONLY'); });
        static::deleting(static function () { throw new \RuntimeException('RUN_EVENT_APPEND_ONLY'); });
    }
}

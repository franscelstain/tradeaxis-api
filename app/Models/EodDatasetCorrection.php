<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EodDatasetCorrection extends Model
{
    protected $table = 'eod_dataset_corrections';

    protected $primaryKey = 'correction_id';

    public $timestamps = false;

    protected $guarded = [];
}

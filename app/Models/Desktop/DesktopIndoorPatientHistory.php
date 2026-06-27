<?php

namespace App\Models\Desktop;

use Illuminate\Database\Eloquent\Model;

class DesktopIndoorPatientHistory extends Model
{
    protected $connection = 'desktop';

    protected $table = 'IndoorPatientHistory';

    protected $primaryKey = 'Ipd_Id';

    public $timestamps = false;

    public $incrementing = true;

    protected $guarded = [];
}

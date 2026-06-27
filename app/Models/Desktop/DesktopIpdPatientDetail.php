<?php

namespace App\Models\Desktop;

use Illuminate\Database\Eloquent\Model;

class DesktopIpdPatientDetail extends Model
{
    protected $connection = 'desktop';

    protected $table = 'IPD_patient_Detail';

    protected $primaryKey = 'Slip_ID';

    public $timestamps = false;

    public $incrementing = true;

    protected $guarded = [];
}

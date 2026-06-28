<?php

namespace App\Models\Desktop;

use Illuminate\Database\Eloquent\Model;

class DesktopTestParticular extends Model
{
    protected $connection = 'desktop';

    protected $table = 'test_particulars';

    public $timestamps = false;

    protected $guarded = [];
}

<?php

namespace App\Models\Desktop;

use Illuminate\Database\Eloquent\Model;

class DesktopTest extends Model
{
    protected $connection = 'desktop';

    protected $table = 'test';

    public $timestamps = false;

    protected $guarded = [];
}

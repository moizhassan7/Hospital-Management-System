<?php

namespace App\Models\Desktop;

use Illuminate\Database\Eloquent\Model;

class DesktopRegTest extends Model
{
    protected $connection = 'desktop';

    protected $table = 'reg_test';

    public $timestamps = false;

    protected $guarded = [];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LabSequence extends Model
{
    protected $table = 'lab_sequences';

    protected $fillable = [
        'date',
        'center_code',
        'last_number',
    ];

    protected $casts = [
        'date' => 'date',
    ];
}

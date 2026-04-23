<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmergencyService extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'fee',
    ];

    protected $casts = [
        'fee' => 'float',
    ];
}
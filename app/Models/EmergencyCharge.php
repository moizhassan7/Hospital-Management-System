<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmergencyCharge extends Model
{
    use HasFactory;

    protected $fillable = [
        'charge_id',
        'first_hour_charges',
        'other_hours_charges',
    ];

    protected $casts = [
        'first_hour_charges' => 'float',
        'other_hours_charges' => 'float',
    ];
}
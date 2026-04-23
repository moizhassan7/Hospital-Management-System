<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'floor_id',
        'name',
        'is_ward',
        'number_of_beds',
        'per_day_rent',
    ];

    protected $casts = [
        'is_ward' => 'boolean',
        'per_day_rent' => 'float', 
        'number_of_beds' => 'integer',
    ];

    /**
     * Get the floor that owns the room.
     */
    public function floor()
    {
        return $this->belongsTo(Floor::class);
    }
}
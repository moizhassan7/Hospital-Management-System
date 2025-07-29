<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function consumableItems()
    {
        return $this->hasMany(ConsumableItem::class);
    }
}

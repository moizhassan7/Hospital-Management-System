<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicineGroup extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function medicines()
    {
        return $this->belongsToMany(Medicine::class, 'medicine_group_items')
                    ->withPivot('dosage_frequency', 'duration_days');
    }
}

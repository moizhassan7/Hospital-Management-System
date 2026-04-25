<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function groups()
    {
        return $this->belongsToMany(MedicineGroup::class, 'medicine_group_items')
                    ->withPivot('dosage_frequency', 'duration_days');
    }

    public function dosages()
    {
        return $this->belongsToMany(Dosage::class, 'medicine_dosages');
    }
}

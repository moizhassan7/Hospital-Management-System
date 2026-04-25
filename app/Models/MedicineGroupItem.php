<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicineGroupItem extends Model
{
    use HasFactory;

    protected $fillable = ['medicine_group_id', 'medicine_id', 'dosage_frequency', 'duration_days'];
}

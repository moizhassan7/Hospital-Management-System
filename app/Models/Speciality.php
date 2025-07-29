<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Speciality extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'department_id',
    ];

    /**
     * Get the department that owns the speciality.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
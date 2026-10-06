<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Procedure extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'department_id',
        'speciality_id',
        'room_location',
        'name',
        'performed_for',
        'general_normal_fee',
        'general_emergency_fee',
        'welfare_normal_fee',
        'welfare_emergency_fee',
        'general_normal_doctor_percentage',
        'general_emergency_doctor_percentage',
        'welfare_normal_doctor_percentage',
        'welfare_emergency_doctor_percentage',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function speciality()
    {
        return $this->belongsTo(Speciality::class);
    }
}
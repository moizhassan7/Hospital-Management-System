<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'department_id',
        'speciality_id',
        'room_location',
        'employee_group',
        'working_days',
        'address',
        'mobile_number',
        'office_phone',
        'reception_phone',
        'accounts_of',
        'fee', 
        'picture',
        'is_active',
        // Add the new fields here
        'general_normal_fee',
        'general_emergency_fee',
        'welfare_normal_fee',
        'welfare_emergency_fee',
        'general_normal_percentage',
        'general_emergency_percentage',
        'welfare_normal_percentage',
        'welfare_emergency_percentage',
    ];

    protected $casts = [
        'working_days' => 'array',
        'is_active' => 'boolean',
        'fee' => 'float',
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
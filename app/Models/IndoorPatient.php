<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IndoorPatient extends Model
{
    use HasFactory;

  protected $fillable = [
    'patient_id',
    'mr_no',
    'file_no',
    'slip_no',
    'registration_date',
    'admission_type',
    'ward_id',
    'room_id',
    'bed_no',
    'admission_fee',
    'advance_fee',
    'total_amount',
    'consultant_id',
    'is_operation',
    'operation_date',
    'is_discharged',       
    'discharge_date',      
    'discharge_time',      
    'discharge_status',    
];


    /**
     * Get the patient record associated with the indoor patient.
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    /**
     * Get the ward record associated with the indoor patient.
     */
    public function ward()
    {
        return $this->belongsTo(Room::class, 'ward_id');
    }

    /**
     * Get the room record associated with the indoor patient.
     */
    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    /**
     * Get the consultant (doctor) associated with the indoor patient.
     */
    public function consultant()
    {
        return $this->belongsTo(Doctor::class, 'consultant_id');
    }
    public function scopeActive($query)
{
    return $query->where('is_discharged', false);
}

}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientDischarge extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'patient_discharges';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'indoor_patient_id',
        'patient_id',
        'discharge_date',
        'discharge_time',
        'discharge_status',
        'discharge_summary',
        'medication_at_discharge',
        'follow_up_instructions',
        'certifying_doctor_id',
        'payment_clearance_status',
        'cause',
        'is_anesthesia',
        'anesthesia_type',
        'diagnoses',
        'consultants',
        'total_bill',
        'admission_fee',
        'advance_fee',
        'discount',
        'amount_receivable',
        'amount_paid',
        'current_remaining',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_anesthesia' => 'boolean',
        'diagnoses' => 'array',
        'consultants' => 'array',
    ];

    /**
     * Get the patient record associated with the discharge.
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the indoor patient record associated with the discharge.
     */
    public function indoorPatient()
    {
        return $this->belongsTo(IndoorPatient::class);
    }

    /**
     * Get the certifying doctor record for the discharge.
     */
    public function certifyingDoctor()
    {
        return $this->belongsTo(Doctor::class, 'certifying_doctor_id');
    }
}
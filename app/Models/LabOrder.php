<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabOrder extends Model
{
    protected $fillable = [
        'desktop_booking_id',
        'lab_registration_no',
        'mr_no',
        'patient_name',
        'gender',
        'contact_no',
        'age',
        'file_no',
        'refer_by_doctor_name',
        'self_referred',
        'booking_date',
        'priority',
        'sub_total',
        'discount',
        'grand_total',
        'paid_amount',
        'due_amount',
        'lab_share_total',
        'hospital_share_total',
        'status',
        'source_hash',
        'source_updated_at',
        'laboratory_patient_id',
        'synced_at',
    ];

    protected $casts = [
        'self_referred' => 'boolean',
        'booking_date' => 'date',
        'source_updated_at' => 'datetime',
        'synced_at' => 'datetime',
    ];

    public function orderTests(): HasMany
    {
        return $this->hasMany(LabOrderTest::class);
    }

    public function laboratoryPatient(): BelongsTo
    {
        return $this->belongsTo(LaboratoryPatient::class);
    }
}

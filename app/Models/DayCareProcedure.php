<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DayCareProcedure extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'mr_no',
        'procedure_details',
        'hours_of_stay',
        'consultants',
        'total_bill',
        'amount_paid',
        'amount_receivable',
    ];

    protected $casts = [
        'consultants' => 'array',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}

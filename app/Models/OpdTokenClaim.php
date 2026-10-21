<?php

namespace App\Models;

use App\Support\OpdStates;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpdTokenClaim extends Model
{
    protected $fillable = [
        'opd_appointment_id',
        'doctor_code',
        'appointment_date',
        'session_key',
        'token_number',
        'status',
        'lock_key',
        'previous_token_number',
        'changed_by',
        'reason',
        'reserved_at',
        'confirmed_at',
        'used_at',
        'released_at',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'token_number' => 'integer',
        'previous_token_number' => 'integer',
        'reserved_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'used_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(OpdAppointment::class, 'opd_appointment_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function isGuaranteed(): bool
    {
        return in_array($this->status, OpdStates::GUARANTEED_TOKEN, true) && filled($this->lock_key);
    }

    public function scopeActive($query)
    {
        return $query->where('status', '!=', OpdStates::RELEASED);
    }

    public function scopeGuaranteed($query)
    {
        return $query->whereIn('status', OpdStates::GUARANTEED_TOKEN)->whereNotNull('lock_key');
    }
}

<?php

namespace App\Models;

use App\Support\OpdStates;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OpdAppointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_number',
        'appointment_date',
        'appointment_time',
        'session_key',
        'mr_number',
        'patient_name',
        'age',
        'gender',
        'doctor_code',
        'doctor_name',
        'doctor_fee',
        'referred_by',
        'total_amount',
        'amount_paid',
        'payment_status',
        'payment_method',
        'payment_collected_at',
        'payment_collected_by',
        'payment_idempotency_key',
        'token_number',
        'preferred_token_number',
        'token_status',
        'token_reserved_at',
        'token_confirmed_at',
        'hospital_share',
        'status',
        'booking_date',
        'received_by',
        'checked_in_at',
        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',
        'no_show_at',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'booking_date' => 'date',
        'doctor_fee' => 'float',
        'total_amount' => 'float',
        'amount_paid' => 'float',
        'hospital_share' => 'float',
        'token_number' => 'integer',
        'preferred_token_number' => 'integer',
        'token_reserved_at' => 'datetime',
        'token_confirmed_at' => 'datetime',
        'payment_collected_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'no_show_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (OpdAppointment $row) {
            $row->session_key = $row->session_key ?: OpdStates::SESSION_NONE;
            if ($row->payment_status !== OpdStates::REFUNDED) {
                $row->payment_status = $row->derivePaymentStatus();
            }
        });
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function paymentCollectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payment_collected_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function tokenClaims(): HasMany
    {
        return $this->hasMany(OpdTokenClaim::class)->orderByDesc('id');
    }

    public function activeTokenClaim(): HasOne
    {
        return $this->hasOne(OpdTokenClaim::class)->ofMany(
            ['id' => 'max'],
            fn ($query) => $query->where('status', '!=', OpdStates::RELEASED)
        );
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_code', 'code');
    }

    public function derivePaymentStatus(): string
    {
        $paid = $this->collectedAmount();
        $total = (float) $this->total_amount;

        if ($this->payment_status === OpdStates::REFUNDED) {
            return OpdStates::REFUNDED;
        }
        if ($paid <= 0) {
            return OpdStates::UNPAID;
        }
        if ($total > 0 && $paid + 0.009 < $total) {
            return OpdStates::PARTIALLY_PAID;
        }

        return OpdStates::PAID;
    }

    /**
     * Amount the ledger should post. Legacy completed walk-ins stored the
     * fee on total_amount and had no amount_paid column.
     */
    public function collectedAmount(): float
    {
        $paid = (float) $this->amount_paid;
        if ($paid > 0) {
            return $paid;
        }

        if (in_array($this->status, [OpdStates::COMPLETED, null], true)) {
            return (float) $this->total_amount;
        }

        return 0.0;
    }

    public function outstandingFee(): float
    {
        return max(0, round((float) $this->total_amount - $this->collectedAmount(), 2));
    }

    public function isPaid(): bool
    {
        return $this->derivePaymentStatus() === OpdStates::PAID;
    }

    public function isUnpaid(): bool
    {
        return $this->derivePaymentStatus() === OpdStates::UNPAID;
    }

    public function hasGuaranteedToken(): bool
    {
        return in_array($this->token_status, OpdStates::GUARANTEED_TOKEN, true);
    }

    public function isOpen(): bool
    {
        return in_array($this->status, OpdStates::OPEN_APPOINTMENT, true);
    }

    public function isCancelled(): bool
    {
        return $this->status === OpdStates::CANCELLED;
    }

    public function displayToken(): ?int
    {
        return $this->token_number ?: $this->preferred_token_number;
    }

    public function sessionLabel(): string
    {
        return $this->session_key ? (string) $this->session_key : '—';
    }
}

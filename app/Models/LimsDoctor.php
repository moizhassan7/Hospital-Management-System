<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class LimsDoctor extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'lims_doctors';

    protected $fillable = [
        'organization_id',
        'code',
        'name',
        'phone',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(LimsBooking::class, 'doctor_id');
    }

    public function commissionRules(): HasMany
    {
        return $this->hasMany(LimsCommissionRule::class, 'doctor_id');
    }

    public function commissionSnapshots(): HasMany
    {
        return $this->hasMany(LimsCommissionSnapshot::class, 'doctor_id');
    }

    public function ledger(): HasOne
    {
        return $this->hasOne(LimsDoctorLedger::class, 'doctor_id');
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(LimsLedgerEntry::class, 'doctor_id');
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(LimsDoctorPayout::class, 'doctor_id');
    }
}

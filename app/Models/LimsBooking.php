<?php

namespace App\Models;

use App\Models\Scopes\BelongsToCollectionCenterScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class LimsBooking extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_BOOKED = 'booked';
    public const STATUS_PARTIALLY_COLLECTED = 'partially_collected';
    public const STATUS_COLLECTED = 'collected';
    public const STATUS_DISPATCHED = 'dispatched';
    public const STATUS_IN_TRANSIT = 'in_transit';
    public const STATUS_RECEIVED = 'received';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_PARTIALLY_REPORTED = 'partially_reported';
    public const STATUS_REPORTED = 'reported';
    public const STATUS_CANCELLED = 'cancelled';

    protected $table = 'lims_bookings';

    protected $fillable = [
        'organization_id',
        'collection_center_id',
        'patient_id',
        'doctor_id',
        'refer_by_doctor_name',
        'self_referred',
        'lab_number',
        'lab_number_year_month',
        'lab_number_seq',
        'priority',
        'status',
        'booked_at',
        'booked_by',
        'cancelled_at',
        'cancelled_by',
        'cancel_reason',
        'notes',
        'sync_id',
        'laboratory_patient_id',
    ];

    protected function casts(): array
    {
        return [
            'self_referred' => 'boolean',
            'lab_number_seq' => 'integer',
            'booked_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new BelongsToCollectionCenterScope);

        static::creating(function (self $model) {
            if (empty($model->sync_id)) {
                $model->sync_id = (string) Str::uuid();
            }
        });
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function collectionCenter(): BelongsTo
    {
        return $this->belongsTo(CollectionCenter::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(LimsPatient::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(LimsDoctor::class, 'doctor_id');
    }

    public function bookedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'booked_by');
    }

    public function cancelledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function laboratoryPatient(): BelongsTo
    {
        return $this->belongsTo(LaboratoryPatient::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(LimsBookingItem::class, 'booking_id');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(LimsInvoice::class, 'booking_id');
    }

    public function samples(): HasMany
    {
        return $this->hasMany(LimsSample::class, 'booking_id');
    }

    public function commissionSnapshots(): HasMany
    {
        return $this->hasMany(LimsCommissionSnapshot::class, 'booking_id');
    }
}

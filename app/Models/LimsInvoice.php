<?php

namespace App\Models;

use App\Models\Scopes\BelongsToCollectionCenterScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LimsInvoice extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUS_OPEN = 'open';
    public const STATUS_PAID = 'paid';
    public const STATUS_PARTIAL = 'partial';
    public const STATUS_VOID = 'void';
    public const STATUS_REFUNDED = 'refunded';

    protected $table = 'lims_invoices';

    protected $fillable = [
        'organization_id',
        'collection_center_id',
        'booking_id',
        'invoice_no',
        'status',
        'sub_total',
        'discount_total',
        'grand_total',
        'paid_total',
        'due_total',
        'invoiced_at',
        'voided_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'sub_total' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'paid_total' => 'decimal:2',
            'due_total' => 'decimal:2',
            'invoiced_at' => 'datetime',
            'voided_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new BelongsToCollectionCenterScope);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function collectionCenter(): BelongsTo
    {
        return $this->belongsTo(CollectionCenter::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(LimsBooking::class, 'booking_id');
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(LimsPayment::class, 'invoice_id');
    }

    public static function statusFromAmounts(float $grandTotal, float $paidTotal): string
    {
        if ($paidTotal <= 0) {
            return self::STATUS_OPEN;
        }

        if ($paidTotal + 0.00001 >= $grandTotal) {
            return self::STATUS_PAID;
        }

        return self::STATUS_PARTIAL;
    }
}

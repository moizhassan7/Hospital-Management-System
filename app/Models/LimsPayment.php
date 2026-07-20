<?php

namespace App\Models;

use App\Models\Scopes\BelongsToCollectionCenterScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LimsPayment extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const METHOD_CASH = 'cash';
    public const METHOD_CARD = 'card';
    public const METHOD_BANK = 'bank';
    public const METHOD_ONLINE = 'online';
    public const METHOD_ADJUSTMENT = 'adjustment';

    public $timestamps = false;

    protected $table = 'lims_payments';

    protected $fillable = [
        'organization_id',
        'collection_center_id',
        'invoice_id',
        'method',
        'amount',
        'paid_at',
        'received_by',
        'idempotency_key',
        'notes',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'created_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new BelongsToCollectionCenterScope);

        static::creating(function (self $model) {
            if ($model->created_at === null) {
                $model->created_at = now();
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

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(LimsInvoice::class, 'invoice_id');
    }

    public function receivedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}

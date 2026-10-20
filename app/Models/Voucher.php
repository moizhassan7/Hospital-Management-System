<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_no',
        'voucher_date',
        'voucher_type',
        'category_id',
        'amount',
        'actual_amount',
        'payment_method',
        'payment_account_id',
        'party_type',
        'party_id',
        'party_name',
        'reference_no',
        'remarks',
        'status',
        'metadata',
        'source_type',
        'source_id',
        'created_by',
        'posted_by',
        'posted_at',
        'voided_by',
        'voided_at',
        'void_reason',
    ];

    protected $casts = [
        'voucher_date' => 'date',
        'amount' => 'decimal:2',
        'actual_amount' => 'decimal:2',
        'metadata' => 'array',
        'posted_at' => 'datetime',
        'voided_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(VoucherCategory::class, 'category_id');
    }

    public function paymentAccount()
    {
        return $this->belongsTo(FinancialAccount::class, 'payment_account_id');
    }

    public function entries()
    {
        return $this->hasMany(LedgerEntry::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function postedBy()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function voidedBy()
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function getEffectiveAmountAttribute(): float
    {
        return (float) ($this->actual_amount ?? $this->amount);
    }

    public function isCashHandover(): bool
    {
        return $this->category?->code === 'cash-handover';
    }

    public function getAccountingTypeCodeAttribute(): string
    {
        if ($this->isCashHandover()) {
            return 'CV';
        }

        if ($this->voucher_type === 'credit') {
            return $this->payment_method === 'cash' ? 'CR' : 'BR';
        }

        return $this->payment_method === 'cash' ? 'CP' : 'BP';
    }

    public function getAccountingTypeAttribute(): string
    {
        return match ($this->accounting_type_code) {
            'CR' => 'Cash Receipt',
            'BR' => 'Bank Receipt',
            'CP' => 'Cash Payment',
            'BP' => 'Bank Payment',
            default => 'Contra Voucher',
        };
    }
}

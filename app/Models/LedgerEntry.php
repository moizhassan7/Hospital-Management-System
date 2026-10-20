<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LedgerEntry extends Model
{
    protected $fillable = [
        'voucher_id', 'source_type', 'source_id', 'source_line', 'revision', 'document_type',
        'transaction_date', 'fiscal_year', 'reference_no', 'account_id', 'entry_type', 'amount',
        'party_type', 'party_id', 'party_name', 'custodian_user_id', 'narration', 'is_reversal',
        'reversal_of_id', 'reversed_at', 'reversed_by', 'created_by', 'posted_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
        'is_reversal' => 'boolean',
        'reversed_at' => 'datetime',
        'posted_at' => 'datetime',
    ];

    public function account()
    {
        return $this->belongsTo(FinancialAccount::class, 'account_id');
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function reversalOf()
    {
        return $this->belongsTo(self::class, 'reversal_of_id');
    }

    public function custodian()
    {
        return $this->belongsTo(User::class, 'custodian_user_id');
    }
}

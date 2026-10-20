<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'name', 'type', 'parent_id', 'system_key', 'normal_balance',
        'is_postable', 'is_system', 'is_active',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'is_active' => 'boolean',
        'is_postable' => 'boolean',
    ];

    public function categories()
    {
        return $this->hasMany(VoucherCategory::class, 'account_id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('code');
    }

    public function ledgerEntries()
    {
        return $this->hasMany(LedgerEntry::class, 'account_id');
    }
}

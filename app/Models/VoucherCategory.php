<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoucherCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'default_type',
        'account_id',
        'description',
        'is_system',
        'is_active',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function account()
    {
        return $this->belongsTo(FinancialAccount::class, 'account_id');
    }

    public function vouchers()
    {
        return $this->hasMany(Voucher::class, 'category_id');
    }
}

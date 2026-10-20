<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountMapping extends Model
{
    protected $fillable = ['mapping_key', 'name', 'account_id', 'description', 'is_system'];

    protected $casts = ['is_system' => 'boolean'];

    public function account()
    {
        return $this->belongsTo(FinancialAccount::class, 'account_id');
    }
}

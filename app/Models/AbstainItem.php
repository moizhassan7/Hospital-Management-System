<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbstainItem extends Model
{
    use HasFactory;

    protected $fillable = ['abstain_id', 'item', 'duration'];

    public function abstain()
    {
        return $this->belongsTo(Abstain::class);
    }
}

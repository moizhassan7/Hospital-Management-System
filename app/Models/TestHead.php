<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestHead extends Model
{
    use HasFactory;

    protected $fillable = ['name'];
    
    public function tests()
    {
        return $this->hasMany(Test::class);
    }
}

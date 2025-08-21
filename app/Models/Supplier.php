<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'company_name',
        'contact_number',
        'address',
        'reference',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
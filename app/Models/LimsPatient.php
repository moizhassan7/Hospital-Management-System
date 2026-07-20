<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Global LIMS patient registry (MR numbers).
 *
 * Table is lims_patients to avoid colliding with the legacy HMS patients
 * migration (indoor/outdoor registration).
 */
class LimsPatient extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'lims_patients';

    protected $fillable = [
        'organization_id',
        'mr_no',
        'full_name',
        'gender',
        'date_of_birth',
        'age_years',
        'contact_no',
        'cnic',
        'address',
        'created_at_cc_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'age_years' => 'integer',
            'deleted_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function createdAtCollectionCenter(): BelongsTo
    {
        return $this->belongsTo(CollectionCenter::class, 'created_at_cc_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

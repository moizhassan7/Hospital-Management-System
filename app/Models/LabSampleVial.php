<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabSampleVial extends Model
{
    use HasFactory;

    public const STATUS_NOT_COLLECTED = 'not_collected';
    public const STATUS_COLLECTED = 'collected';
    public const STATUS_IN_LAB = 'in_lab';

    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_EXPIRED = 'expired';

    public static function statusOptions(): array
    {
        return [
            self::STATUS_NOT_COLLECTED => 'Not Collected',
            self::STATUS_COLLECTED => 'Collected',
            self::STATUS_IN_LAB => 'Received in Lab',
            self::STATUS_PROCESSING => 'Processing',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_EXPIRED => 'Expired',
        ];
    }

    public static function statusLabel(string $status): string
    {
        return self::statusOptions()[$status] ?? ucfirst(str_replace('_', ' ', $status));
    }

    public static function statusBadgeClass(string $status): string
    {
        return match ($status) {
            self::STATUS_COLLECTED => 'bg-green-100 text-green-800',
            self::STATUS_IN_LAB, self::STATUS_PROCESSING => 'bg-blue-100 text-blue-800',
            self::STATUS_COMPLETED => 'bg-teal-100 text-teal-800',
            self::STATUS_REJECTED, self::STATUS_EXPIRED => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public static function statusReBadgeClass(string $status): string
    {
        return match ($status) {
            self::STATUS_COLLECTED => 'hms-re-status--collected',
            self::STATUS_IN_LAB => 'hms-re-status--in_lab',
            self::STATUS_PROCESSING => 'hms-re-status--processing',
            self::STATUS_COMPLETED => 'hms-re-status--completed',
            self::STATUS_REJECTED => 'hms-re-status--rejected',
            self::STATUS_EXPIRED => 'hms-re-status--expired',
            default => 'hms-re-status--not_collected',
        };
    }

    protected $fillable = [
        'laboratory_patient_id',
        'barcode',
        'vial_type',
        'vial_number',
        'test_ids',
        'collected_at',
        'received_in_lab_at',
        'reported_at',
        'expires_at',
        'status',
    ];

    protected $casts = [
        'test_ids' => 'array',
        'collected_at' => 'datetime',
        'received_in_lab_at' => 'datetime',
        'reported_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function laboratoryPatient()
    {
        return $this->belongsTo(LaboratoryPatient::class);
    }

    public function limsSample()
    {
        return $this->hasOne(LimsSample::class, 'lab_sample_vial_id');
    }

    public function tests()
    {
        return Test::whereIn('id', $this->test_ids ?? [])->get();
    }

    public function markReceivedInLab(): void
    {
        $this->status = self::STATUS_IN_LAB;
        if (!$this->received_in_lab_at) {
            $this->received_in_lab_at = now();
        }
        if (!$this->collected_at) {
            $this->collected_at = now();
        }
        $this->save();
    }

    public function markReported(): void
    {
        if (!$this->reported_at) {
            $this->reported_at = now();
        }
        $this->save();
    }
}

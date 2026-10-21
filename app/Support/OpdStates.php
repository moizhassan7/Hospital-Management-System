<?php

namespace App\Support;

/**
 * Distinct appointment, payment and token states for OPD.
 *
 * Appointment `status` keeps the historical lowercase values (`booked`,
 * `completed`) so existing reports and the outdoor register keep working.
 * Payment status is stored on the appointment but is always derived from
 * `amount_paid` versus `total_amount` (the ledger posts `amount_paid`).
 * Token status lives on `opd_token_claims`; guaranteed claims (reserved,
 * confirmed, used) hold `lock_key` and participate in uniqueness.
 */
final class OpdStates
{
    public const BOOKED = 'booked';
    public const CHECKED_IN = 'checked_in';
    public const IN_PROGRESS = 'in_progress';
    public const COMPLETED = 'completed';
    public const CANCELLED = 'cancelled';
    public const NO_SHOW = 'no_show';

    public const UNPAID = 'unpaid';
    public const PARTIALLY_PAID = 'partially_paid';
    public const PAID = 'paid';
    public const REFUNDED = 'refunded';

    public const PROVISIONAL = 'provisional';
    public const RESERVED = 'reserved';
    public const CONFIRMED = 'confirmed';
    public const USED = 'used';
    public const RELEASED = 'released';

    public const SESSION_NONE = '';

    /** Token statuses that occupy a queue slot. */
    public const GUARANTEED_TOKEN = [
        self::RESERVED,
        self::CONFIRMED,
        self::USED,
    ];

    public const OPEN_APPOINTMENT = [
        self::BOOKED,
        self::CHECKED_IN,
        self::IN_PROGRESS,
    ];

    public const CLOSED_APPOINTMENT = [
        self::COMPLETED,
        self::CANCELLED,
        self::NO_SHOW,
    ];

    public static function appointmentLabel(string $status): string
    {
        return match ($status) {
            self::BOOKED => 'Booked',
            self::CHECKED_IN => 'Checked in',
            self::IN_PROGRESS => 'In progress',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
            self::NO_SHOW => 'No-show',
            default => $status !== '' ? ucfirst(str_replace('_', ' ', $status)) : '—',
        };
    }

    public static function paymentLabel(string $status): string
    {
        return match ($status) {
            self::UNPAID => 'Unpaid',
            self::PARTIALLY_PAID => 'Partially paid',
            self::PAID => 'Paid',
            self::REFUNDED => 'Refunded',
            default => $status !== '' ? ucfirst(str_replace('_', ' ', $status)) : 'Unpaid',
        };
    }

    public static function tokenLabel(?string $status): string
    {
        return match ($status) {
            self::PROVISIONAL => 'Provisional',
            self::RESERVED => 'Reserved',
            self::CONFIRMED => 'Confirmed',
            self::USED => 'Used',
            self::RELEASED => 'Released',
            default => 'None',
        };
    }

    public static function appointmentTone(string $status): string
    {
        return match ($status) {
            self::COMPLETED, self::CHECKED_IN => 'success',
            self::BOOKED, self::IN_PROGRESS => 'info',
            self::NO_SHOW => 'warning',
            self::CANCELLED => 'danger',
            default => 'neutral',
        };
    }

    public static function paymentTone(string $status): string
    {
        return match ($status) {
            self::PAID => 'success',
            self::PARTIALLY_PAID => 'warning',
            self::REFUNDED => 'danger',
            default => 'warning',
        };
    }

    public static function tokenTone(?string $status): string
    {
        return match ($status) {
            self::RESERVED, self::CONFIRMED, self::USED => 'success',
            self::PROVISIONAL => 'warning',
            self::RELEASED => 'neutral',
            default => 'neutral',
        };
    }
}

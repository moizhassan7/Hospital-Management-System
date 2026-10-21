<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\OpdAppointment;
use App\Models\OpdTokenClaim;
use App\Support\OpdStates;
use Carbon\Carbon;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Token queue is per doctor + appointment date. The hospital has no morning/
 * evening sessions today, so session_key is stored as an empty string and
 * still participates in the uniqueness lock so a future session cannot bypass
 * the constraint by being null.
 *
 * Only reserved / confirmed / used claims set lock_key. Provisional numbers
 * may overlap. Transitions reserved → confirmed → used keep the same lock.
 */
class OpdTokenService
{
    public const MAX_TOKEN = 9999;

    public function normalizeSession(?string $session): string
    {
        return $session === null || $session === '' ? OpdStates::SESSION_NONE : trim($session);
    }

    public function normalizeDate(mixed $date): string
    {
        return Carbon::parse($date)->toDateString();
    }

    public function lockKey(string $doctorCode, mixed $date, int $token, ?string $session = null): string
    {
        return $doctorCode.'|'.$this->normalizeDate($date).'|'.$this->normalizeSession($session).'|'.$token;
    }

    public function suggest(string $doctorCode, mixed $date, ?string $session = null, ?int $ignoreAppointmentId = null): int
    {
        $taken = $this->guaranteedTokens($doctorCode, $date, $session, $ignoreAppointmentId);

        for ($token = 1; $token <= self::MAX_TOKEN; $token++) {
            if (! isset($taken[$token])) {
                return $token;
            }
        }

        throw ValidationException::withMessages([
            'token_number' => 'No available token numbers remain for this doctor on '.$this->formatDate($date).'.',
        ]);
    }

    /**
     * @return array<int, true>
     */
    public function guaranteedTokens(string $doctorCode, mixed $date, ?string $session = null, ?int $ignoreAppointmentId = null): array
    {
        $query = OpdTokenClaim::query()
            ->guaranteed()
            ->where('doctor_code', $doctorCode)
            ->whereDate('appointment_date', $this->normalizeDate($date))
            ->where('session_key', $this->normalizeSession($session));

        if ($ignoreAppointmentId) {
            $query->where('opd_appointment_id', '!=', $ignoreAppointmentId);
        }

        return $query->pluck('token_number')->mapWithKeys(fn ($n) => [(int) $n => true])->all();
    }

    public function isAvailable(string $doctorCode, mixed $date, int $token, ?string $session = null, ?int $ignoreAppointmentId = null): bool
    {
        if ($token < 1 || $token > self::MAX_TOKEN) {
            return false;
        }

        return ! isset($this->guaranteedTokens($doctorCode, $date, $session, $ignoreAppointmentId)[$token]);
    }

    public function holder(string $doctorCode, mixed $date, int $token, ?string $session = null, ?int $ignoreAppointmentId = null): ?OpdTokenClaim
    {
        $query = OpdTokenClaim::query()
            ->guaranteed()
            ->with('appointment')
            ->where('doctor_code', $doctorCode)
            ->whereDate('appointment_date', $this->normalizeDate($date))
            ->where('session_key', $this->normalizeSession($session))
            ->where('token_number', $token);

        if ($ignoreAppointmentId) {
            $query->where('opd_appointment_id', '!=', $ignoreAppointmentId);
        }

        return $query->first();
    }

    public function conflictMessage(string $doctorCode, mixed $date, int $token, ?string $session = null, ?int $ignoreAppointmentId = null): string
    {
        $doctor = Doctor::where('code', $doctorCode)->first();
        $name = $doctor?->name ?: $doctorCode;
        $when = $this->formatDate($date);
        $sessionLabel = $this->normalizeSession($session);
        $sessionBit = $sessionLabel !== OpdStates::SESSION_NONE ? ' ('.$sessionLabel.')' : '';
        $holder = $this->holder($doctorCode, $date, $token, $session, $ignoreAppointmentId);
        $patient = $holder?->appointment?->patient_name;

        $suffix = $patient ? ' It is held by '.$patient.'.' : '';

        return "Token #{$token} is already reserved for {$name} on {$when}{$sessionBit}. Please choose another available token.{$suffix}";
    }

    public function assertAvailable(string $doctorCode, mixed $date, int $token, ?string $session = null, ?int $ignoreAppointmentId = null): void
    {
        $this->assertValidNumber($token);

        if (! $this->isAvailable($doctorCode, $date, $token, $session, $ignoreAppointmentId)) {
            throw ValidationException::withMessages([
                'token_number' => $this->conflictMessage($doctorCode, $date, $token, $session, $ignoreAppointmentId),
            ])->status(409);
        }
    }

    public function assertValidNumber(mixed $token): int
    {
        if (! is_numeric($token) || (int) $token != $token || (int) $token < 1 || (int) $token > self::MAX_TOKEN) {
            throw ValidationException::withMessages([
                'token_number' => 'Token number must be a positive integer between 1 and '.self::MAX_TOKEN.'.',
            ]);
        }

        return (int) $token;
    }

    /**
     * Lock guaranteed claims in this queue so concurrent reservations serialize.
     */
    public function lockQueue(string $doctorCode, mixed $date, ?string $session = null): void
    {
        OpdTokenClaim::query()
            ->where('doctor_code', $doctorCode)
            ->whereDate('appointment_date', $this->normalizeDate($date))
            ->where('session_key', $this->normalizeSession($session))
            ->whereNotNull('lock_key')
            ->lockForUpdate()
            ->get();
    }

    public function assignProvisional(OpdAppointment $appointment, int $token, ?int $userId = null): OpdTokenClaim
    {
        $token = $this->assertValidNumber($token);

        return DB::transaction(function () use ($appointment, $token, $userId) {
            $appointment = OpdAppointment::whereKey($appointment->id)->lockForUpdate()->firstOrFail();
            $this->releaseActive($appointment, 'Replaced by a new provisional token', $userId);

            $claim = OpdTokenClaim::create([
                'opd_appointment_id' => $appointment->id,
                'doctor_code' => $appointment->doctor_code,
                'appointment_date' => $this->normalizeDate($appointment->appointment_date),
                'session_key' => $this->normalizeSession($appointment->session_key),
                'token_number' => $token,
                'status' => OpdStates::PROVISIONAL,
                'lock_key' => null,
                'changed_by' => $userId,
                'reason' => 'Provisional token stored without a guaranteed reservation',
            ]);

            $appointment->forceFill([
                'token_number' => $token,
                'preferred_token_number' => $appointment->preferred_token_number ?: $token,
                'token_status' => OpdStates::PROVISIONAL,
            ])->save();

            return $claim;
        });
    }

    public function reserve(OpdAppointment $appointment, int $token, ?int $userId = null, ?string $reason = null): OpdTokenClaim
    {
        $token = $this->assertValidNumber($token);

        return DB::transaction(function () use ($appointment, $token, $userId, $reason) {
            $appointment = OpdAppointment::whereKey($appointment->id)->lockForUpdate()->firstOrFail();
            $session = $this->normalizeSession($appointment->session_key);
            $date = $this->normalizeDate($appointment->appointment_date);

            $this->lockQueue($appointment->doctor_code, $date, $session);
            $this->assertAvailable($appointment->doctor_code, $date, $token, $session, $appointment->id);

            $active = $appointment->tokenClaims()->active()->first();
            $previous = $active?->token_number;

            if ($active && in_array($active->status, OpdStates::GUARANTEED_TOKEN, true) && (int) $active->token_number === $token) {
                return $active;
            }

            if ($active && $active->status === OpdStates::PROVISIONAL && (int) $active->token_number === $token) {
                $this->applyGuarantee($active, OpdStates::RESERVED, $userId, $reason ?: 'Fee collected — token reserved');
            } else {
                $this->releaseActive($appointment, $reason ?: 'Released so a guaranteed token can be reserved', $userId);
                try {
                    $active = OpdTokenClaim::create([
                        'opd_appointment_id' => $appointment->id,
                        'doctor_code' => $appointment->doctor_code,
                        'appointment_date' => $date,
                        'session_key' => $session,
                        'token_number' => $token,
                        'status' => OpdStates::RESERVED,
                        'lock_key' => $this->lockKey($appointment->doctor_code, $date, $token, $session),
                        'previous_token_number' => $previous && $previous !== $token ? $previous : null,
                        'changed_by' => $userId,
                        'reason' => $reason ?: 'Fee collected — token reserved',
                        'reserved_at' => now(),
                    ]);
                } catch (UniqueConstraintViolationException $e) {
                    throw ValidationException::withMessages([
                        'token_number' => $this->conflictMessage($appointment->doctor_code, $date, $token, $session, $appointment->id),
                    ])->status(409);
                }
            }

            $appointment->forceFill([
                'token_number' => $token,
                'preferred_token_number' => $appointment->preferred_token_number ?: $token,
                'token_status' => OpdStates::RESERVED,
                'token_reserved_at' => $appointment->token_reserved_at ?: now(),
            ])->save();

            return $active->fresh();
        });
    }

    public function confirm(OpdAppointment $appointment, ?int $userId = null): OpdTokenClaim
    {
        return DB::transaction(function () use ($appointment, $userId) {
            $appointment = OpdAppointment::whereKey($appointment->id)->lockForUpdate()->firstOrFail();
            $claim = $appointment->tokenClaims()->active()->first();

            if (! $claim || ! in_array($claim->status, OpdStates::GUARANTEED_TOKEN, true)) {
                throw ValidationException::withMessages([
                    'token_number' => 'This appointment has no reserved token to confirm. Collect the fee first.',
                ]);
            }

            if ($claim->status === OpdStates::RESERVED) {
                $this->applyGuarantee($claim, OpdStates::CONFIRMED, $userId, 'Patient checked in');
            }

            $appointment->forceFill([
                'token_status' => $claim->fresh()->status,
                'token_confirmed_at' => $appointment->token_confirmed_at ?: now(),
            ])->save();

            return $claim->fresh();
        });
    }

    public function markUsed(OpdAppointment $appointment, ?int $userId = null): OpdTokenClaim
    {
        return DB::transaction(function () use ($appointment, $userId) {
            $appointment = OpdAppointment::whereKey($appointment->id)->lockForUpdate()->firstOrFail();
            $claim = $appointment->tokenClaims()->active()->first();

            if (! $claim || ! in_array($claim->status, OpdStates::GUARANTEED_TOKEN, true)) {
                throw ValidationException::withMessages([
                    'token_number' => 'This appointment has no reserved token to mark used.',
                ]);
            }

            if ($claim->status !== OpdStates::USED) {
                $this->applyGuarantee($claim, OpdStates::USED, $userId, 'Consultation completed');
            }

            $appointment->forceFill(['token_status' => OpdStates::USED])->save();

            return $claim->fresh();
        });
    }

    public function release(OpdAppointment $appointment, ?string $reason = null, ?int $userId = null): void
    {
        DB::transaction(function () use ($appointment, $reason, $userId) {
            $appointment = OpdAppointment::whereKey($appointment->id)->lockForUpdate()->firstOrFail();
            $this->releaseActive($appointment, $reason ?: 'Token released', $userId);
            $appointment->forceFill(['token_status' => OpdStates::RELEASED])->save();
        });
    }

    public function reassign(OpdAppointment $appointment, int $token, string $reason, ?int $userId = null): OpdTokenClaim
    {
        $token = $this->assertValidNumber($token);

        if (! $appointment->isPaid()) {
            return $this->assignProvisional($appointment, $token, $userId);
        }

        return $this->reserve($appointment, $token, $userId, $reason !== '' ? $reason : 'Manual token reassignment');
    }

    public function inspect(OpdAppointment $appointment): array
    {
        $preferred = (int) ($appointment->preferred_token_number ?: $appointment->token_number ?: 0);
        $session = $this->normalizeSession($appointment->session_key);
        $available = $preferred > 0
            ? $this->isAvailable($appointment->doctor_code, $appointment->appointment_date, $preferred, $session, $appointment->id)
            : false;
        $suggestion = $this->suggest($appointment->doctor_code, $appointment->appointment_date, $session, $appointment->id);

        return [
            'preferred' => $preferred ?: null,
            'current' => $appointment->displayToken(),
            'status' => $appointment->token_status,
            'available' => $available,
            'suggestion' => $suggestion,
            'conflict' => $preferred && ! $available
                ? $this->conflictMessage($appointment->doctor_code, $appointment->appointment_date, $preferred, $session, $appointment->id)
                : null,
        ];
    }

    private function applyGuarantee(OpdTokenClaim $claim, string $status, ?int $userId, string $reason): void
    {
        $lockKey = $this->lockKey($claim->doctor_code, $claim->appointment_date, (int) $claim->token_number, $claim->session_key);

        try {
            $claim->update([
                'status' => $status,
                'lock_key' => $lockKey,
                'changed_by' => $userId,
                'reason' => $reason,
                'reserved_at' => $claim->reserved_at ?: now(),
                'confirmed_at' => $status === OpdStates::CONFIRMED || $status === OpdStates::USED
                    ? ($claim->confirmed_at ?: now())
                    : $claim->confirmed_at,
                'used_at' => $status === OpdStates::USED ? ($claim->used_at ?: now()) : $claim->used_at,
            ]);
        } catch (UniqueConstraintViolationException $e) {
            throw ValidationException::withMessages([
                'token_number' => $this->conflictMessage($claim->doctor_code, $claim->appointment_date, (int) $claim->token_number, $claim->session_key, $claim->opd_appointment_id),
            ])->status(409);
        }
    }

    private function releaseActive(OpdAppointment $appointment, string $reason, ?int $userId): void
    {
        $appointment->tokenClaims()->active()->get()->each(function (OpdTokenClaim $claim) use ($reason, $userId) {
            $claim->update([
                'status' => OpdStates::RELEASED,
                'lock_key' => null,
                'released_at' => now(),
                'changed_by' => $userId,
                'reason' => $reason,
            ]);
        });
    }

    private function formatDate(mixed $date): string
    {
        return Carbon::parse($date)->format('d M Y');
    }
}

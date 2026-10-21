<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\OpdAppointment;
use App\Models\Patient;
use App\Support\OpdStates;
use Carbon\Carbon;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OpdAppointmentService
{
    public function __construct(private OpdTokenService $tokens)
    {
    }

    public function consultationFee(Doctor $doctor, ?Patient $patient = null): float
    {
        $welfare = (bool) ($patient?->is_welfare);
        $fee = $welfare ? $doctor->welfare_normal_fee : $doctor->general_normal_fee;
        if (! $fee) {
            $fee = $doctor->fee;
        }

        return round((float) $fee, 2);
    }

    /**
     * @param  array<string, mixed>  $input
     */
    public function book(array $input, bool $collectPayment, ?int $userId = null): OpdAppointment
    {
        $patient = $this->requirePatient($input['mr_number']);
        $doctor = $this->requireDoctor($input['doctor_code']);
        $date = $this->tokens->normalizeDate($input['appointment_date']);
        $session = $this->tokens->normalizeSession($input['session_key'] ?? null);
        $token = $this->tokens->assertValidNumber($input['token_number'] ?? 0);
        $fee = $this->consultationFee($doctor, $patient);
        $amountPaid = $collectPayment ? $this->qualifyingAmount($input, $fee) : 0.0;

        if ($collectPayment) {
            $this->tokens->assertAvailable($doctor->code, $date, $token, $session);
        }

        return retry(3, function () use ($input, $collectPayment, $userId, $patient, $doctor, $date, $session, $token, $fee, $amountPaid) {
            try {
                return DB::transaction(function () use ($input, $collectPayment, $userId, $patient, $doctor, $date, $session, $token, $fee, $amountPaid) {
                    $appointment = OpdAppointment::create([
                        'appointment_number' => $this->nextAppointmentNumber($date),
                        'appointment_date' => $date,
                        'appointment_time' => $input['appointment_time'],
                        'session_key' => $session,
                        'mr_number' => $patient->mr_number,
                        'patient_name' => $patient->name,
                        'age' => (string) $patient->age,
                        'gender' => $patient->gender,
                        'doctor_code' => $doctor->code,
                        'doctor_name' => $doctor->name,
                        'doctor_fee' => $fee,
                        'referred_by' => $input['referred_by'] ?? null,
                        'total_amount' => $fee,
                        'hospital_share' => $input['hospital_share'] ?? round($fee * 0.40, 2),
                        'amount_paid' => $amountPaid,
                        'payment_status' => $amountPaid > 0 ? OpdStates::PAID : OpdStates::UNPAID,
                        'payment_method' => $collectPayment ? ($input['payment_method'] ?? 'cash') : null,
                        'payment_collected_at' => $collectPayment ? now() : null,
                        'payment_collected_by' => $collectPayment ? $userId : null,
                        'payment_idempotency_key' => $collectPayment ? ($input['idempotency_key'] ?? null) : null,
                        'preferred_token_number' => $token,
                        'token_number' => $token,
                        'status' => OpdStates::BOOKED,
                        'booking_date' => now()->toDateString(),
                        'received_by' => $userId,
                    ]);

                    if ($collectPayment) {
                        $this->tokens->reserve($appointment, $token, $userId, 'Paid at booking — token reserved');
                    } else {
                        $this->tokens->assignProvisional($appointment, $token, $userId);
                    }

                    return $appointment->fresh(['activeTokenClaim', 'tokenClaims']);
                });
            } catch (UniqueConstraintViolationException $e) {
                if ($this->isAppointmentNumberRace($e)) {
                    throw $e;
                }
                throw ValidationException::withMessages([
                    'token_number' => $this->tokens->conflictMessage($doctor->code, $date, $token, $session),
                ])->status(409);
            }
        }, 50, fn ($e) => $e instanceof UniqueConstraintViolationException && $this->isAppointmentNumberRace($e));
    }

    /**
     * Collect the outstanding consultation fee and convert a provisional token
     * into a guaranteed reservation. Already-paid appointments are a no-op so
     * retries cannot post a second collection.
     */
    public function collectFee(OpdAppointment $appointment, array $input, ?int $userId = null): OpdAppointment
    {
        return DB::transaction(function () use ($appointment, $input, $userId) {
            $appointment = OpdAppointment::whereKey($appointment->id)->lockForUpdate()->firstOrFail();

            if ($appointment->isCancelled()) {
                throw ValidationException::withMessages(['status' => 'A cancelled appointment cannot collect a fee.']);
            }

            $idempotency = $input['idempotency_key'] ?? null;
            if ($idempotency && $appointment->payment_idempotency_key === $idempotency && $appointment->isPaid()) {
                return $appointment->fresh(['activeTokenClaim']);
            }

            if ($appointment->isPaid()) {
                return $appointment->fresh(['activeTokenClaim']);
            }

            $token = $this->tokens->assertValidNumber($input['token_number'] ?? $appointment->displayToken() ?? 0);
            $amount = $this->qualifyingAmount($input, (float) $appointment->total_amount);
            $session = $this->tokens->normalizeSession($appointment->session_key);

            $this->tokens->lockQueue($appointment->doctor_code, $appointment->appointment_date, $session);
            $this->tokens->assertAvailable($appointment->doctor_code, $appointment->appointment_date, $token, $session, $appointment->id);

            $keepOriginal = (bool) ($input['keep_original_token'] ?? false);
            $original = (int) ($appointment->preferred_token_number ?: 0);
            if ($keepOriginal && $original && $original !== $token) {
                throw ValidationException::withMessages([
                    'token_number' => 'Keep original token was selected but token #'.$original.' was not submitted.',
                ]);
            }
            if ($keepOriginal && $original && ! $this->tokens->isAvailable($appointment->doctor_code, $appointment->appointment_date, $original, $session, $appointment->id)) {
                throw ValidationException::withMessages([
                    'token_number' => $this->tokens->conflictMessage($appointment->doctor_code, $appointment->appointment_date, $original, $session, $appointment->id),
                ])->status(409);
            }

            $appointment->forceFill([
                'amount_paid' => round($appointment->collectedAmount() + $amount, 2),
                'payment_method' => $input['payment_method'] ?? $appointment->payment_method ?? 'cash',
                'payment_collected_at' => now(),
                'payment_collected_by' => $userId,
                'payment_idempotency_key' => $idempotency ?: $appointment->payment_idempotency_key ?: 'collect-'.$appointment->id.'-'.now()->timestamp,
                'received_by' => $appointment->received_by ?: $userId,
            ])->save();

            $this->tokens->reserve($appointment, $token, $userId, 'Fee collected — token reserved');

            return $appointment->fresh(['activeTokenClaim', 'tokenClaims']);
        });
    }

    public function checkIn(OpdAppointment $appointment, ?int $userId = null): OpdAppointment
    {
        return DB::transaction(function () use ($appointment, $userId) {
            $appointment = OpdAppointment::whereKey($appointment->id)->lockForUpdate()->firstOrFail();

            if ($appointment->isCancelled() || $appointment->status === OpdStates::NO_SHOW) {
                throw ValidationException::withMessages(['status' => 'This appointment cannot be checked in.']);
            }
            if ($appointment->status === OpdStates::COMPLETED) {
                return $appointment;
            }
            if (! $appointment->isPaid()) {
                throw ValidationException::withMessages([
                    'payment_status' => 'Collect the consultation fee before checking this patient in.',
                ]);
            }
            if (! $appointment->hasGuaranteedToken()) {
                throw ValidationException::withMessages([
                    'token_number' => 'This paid appointment has no reserved token. Reassign a token first.',
                ]);
            }

            $this->tokens->confirm($appointment, $userId);

            $appointment->refresh();
            $appointment->forceFill([
                'status' => OpdStates::CHECKED_IN,
                'checked_in_at' => $appointment->checked_in_at ?: now(),
                'received_by' => $appointment->received_by ?: $userId,
            ])->save();

            return $appointment->fresh(['activeTokenClaim']);
        });
    }

    public function complete(OpdAppointment $appointment, ?int $userId = null): OpdAppointment
    {
        return DB::transaction(function () use ($appointment, $userId) {
            $appointment = OpdAppointment::whereKey($appointment->id)->lockForUpdate()->firstOrFail();

            if ($appointment->isCancelled()) {
                throw ValidationException::withMessages(['status' => 'A cancelled appointment cannot be completed.']);
            }
            if (! $appointment->isPaid()) {
                throw ValidationException::withMessages([
                    'payment_status' => 'Collect the consultation fee before completing this visit.',
                ]);
            }
            if ($appointment->status !== OpdStates::CHECKED_IN) {
                $this->checkIn($appointment, $userId);
                $appointment = $appointment->fresh();
            }

            $this->tokens->markUsed($appointment, $userId);

            $appointment->refresh();
            $appointment->forceFill([
                'status' => OpdStates::COMPLETED,
                'received_by' => $appointment->received_by ?: $userId,
            ])->save();

            return $appointment->fresh(['activeTokenClaim']);
        });
    }

    /**
     * Same-day OPD desk: new walk-in or arrival of a booked appointment.
     *
     * @param  array<string, mixed>  $input
     */
    public function registerVisit(array $input, ?int $userId = null): OpdAppointment
    {
        $date = $this->tokens->normalizeDate($input['appointment_date'] ?? now());
        $prebooked = OpdAppointment::query()
            ->where('mr_number', $input['mr_number'])
            ->whereDate('appointment_date', $date)
            ->whereIn('status', OpdStates::OPEN_APPOINTMENT)
            ->orderBy('id')
            ->first();

        if ($prebooked) {
            return DB::transaction(function () use ($prebooked, $input, $userId) {
                $appointment = OpdAppointment::whereKey($prebooked->id)->lockForUpdate()->firstOrFail();

                if ($appointment->isPaid()) {
                    $this->complete($appointment, $userId);

                    return $appointment->fresh(['activeTokenClaim']);
                }

                $token = $this->tokens->assertValidNumber($input['token_number'] ?? $appointment->displayToken() ?? 0);
                $this->collectFee($appointment, [
                    'token_number' => $token,
                    'amount_paid' => $input['total_amount'] ?? $appointment->total_amount,
                    'payment_method' => $input['payment_method'] ?? 'cash',
                    'keep_original_token' => (bool) ($input['keep_original_token'] ?? false),
                    'idempotency_key' => $input['idempotency_key'] ?? null,
                ], $userId);

                return $this->complete($appointment->fresh(), $userId);
            });
        }

        $patient = $this->requirePatient($input['mr_number']);
        $doctor = $this->requireDoctor($input['doctor_code']);
        $session = $this->tokens->normalizeSession($input['session_key'] ?? null);
        $token = $this->tokens->assertValidNumber($input['token_number'] ?? 0);
        $fee = isset($input['total_amount']) ? (float) $input['total_amount'] : $this->consultationFee($doctor, $patient);

        return retry(3, function () use ($input, $userId, $patient, $doctor, $date, $session, $token, $fee) {
            return DB::transaction(function () use ($input, $userId, $patient, $doctor, $date, $session, $token, $fee) {
                $this->tokens->lockQueue($doctor->code, $date, $session);
                $this->tokens->assertAvailable($doctor->code, $date, $token, $session);

                $appointment = OpdAppointment::create([
                    'appointment_number' => $input['appointment_number'] ?? $this->nextAppointmentNumber($date),
                    'appointment_date' => $date,
                    'appointment_time' => $input['appointment_time'] ?? now()->format('H:i:s'),
                    'session_key' => $session,
                    'mr_number' => $patient->mr_number,
                    'patient_name' => $input['patient_name'] ?? $patient->name,
                    'age' => (string) ($input['age'] ?? $patient->age),
                    'gender' => $input['gender'] ?? $patient->gender,
                    'doctor_code' => $doctor->code,
                    'doctor_name' => $input['doctor_name'] ?? $doctor->name,
                    'doctor_fee' => $input['doctor_fee'] ?? $fee,
                    'referred_by' => $input['referred_by'] ?? null,
                    'total_amount' => $fee,
                    'hospital_share' => $input['hospital_share'] ?? round($fee * 0.40, 2),
                    'amount_paid' => $fee,
                    'payment_status' => OpdStates::PAID,
                    'payment_method' => $input['payment_method'] ?? 'cash',
                    'payment_collected_at' => now(),
                    'payment_collected_by' => $userId,
                    'preferred_token_number' => $token,
                    'token_number' => $token,
                    'status' => OpdStates::COMPLETED,
                    'booking_date' => $date,
                    'received_by' => $userId,
                    'checked_in_at' => now(),
                ]);

                $this->tokens->reserve($appointment, $token, $userId, 'Walk-in OPD — token reserved');
                $this->tokens->confirm($appointment, $userId);
                $this->tokens->markUsed($appointment, $userId);

                return $appointment->fresh(['activeTokenClaim']);
            });
        }, 50, fn ($e) => $e instanceof UniqueConstraintViolationException && $this->isAppointmentNumberRace($e));
    }

    public function cancel(OpdAppointment $appointment, ?string $reason = null, ?int $userId = null): OpdAppointment
    {
        return DB::transaction(function () use ($appointment, $reason, $userId) {
            $appointment = OpdAppointment::whereKey($appointment->id)->lockForUpdate()->firstOrFail();

            if ($appointment->status === OpdStates::COMPLETED) {
                throw ValidationException::withMessages(['status' => 'A completed visit cannot be cancelled.']);
            }
            if ($appointment->isCancelled()) {
                return $appointment;
            }

            $this->tokens->release($appointment, $reason ?: 'Appointment cancelled', $userId);

            $appointment->refresh();
            $appointment->forceFill([
                'status' => OpdStates::CANCELLED,
                'cancelled_at' => now(),
                'cancelled_by' => $userId,
                'cancellation_reason' => $reason,
            ])->save();

            return $appointment->fresh(['tokenClaims']);
        });
    }

    public function markNoShow(OpdAppointment $appointment, ?int $userId = null): OpdAppointment
    {
        return DB::transaction(function () use ($appointment, $userId) {
            $appointment = OpdAppointment::whereKey($appointment->id)->lockForUpdate()->firstOrFail();

            if (! $appointment->isOpen()) {
                throw ValidationException::withMessages(['status' => 'Only an open appointment can be marked as a no-show.']);
            }

            $appointment->forceFill([
                'status' => OpdStates::NO_SHOW,
                'no_show_at' => now(),
                'received_by' => $appointment->received_by ?: $userId,
            ])->save();

            return $appointment->fresh(['activeTokenClaim']);
        });
    }

    /**
     * @param  array<string, mixed>  $input
     */
    public function reschedule(OpdAppointment $appointment, array $input, ?int $userId = null): OpdAppointment
    {
        return DB::transaction(function () use ($appointment, $input, $userId) {
            $appointment = OpdAppointment::whereKey($appointment->id)->lockForUpdate()->firstOrFail();

            if (! $appointment->isOpen()) {
                throw ValidationException::withMessages(['status' => 'Only an open appointment can be rescheduled.']);
            }

            $doctor = $this->requireDoctor($input['doctor_code'] ?? $appointment->doctor_code);
            $date = $this->tokens->normalizeDate($input['appointment_date'] ?? $appointment->appointment_date);
            $session = $this->tokens->normalizeSession($input['session_key'] ?? $appointment->session_key);
            $time = $input['appointment_time'] ?? $appointment->appointment_time;
            $requested = $input['token_number'] ?? $appointment->displayToken();
            $token = $this->tokens->assertValidNumber($requested);

            $queueChanged = $doctor->code !== $appointment->doctor_code
                || $date !== $this->tokens->normalizeDate($appointment->appointment_date)
                || $session !== $this->tokens->normalizeSession($appointment->session_key);

            $wasPaid = $appointment->isPaid();
            $previousToken = $appointment->displayToken();

            if ($queueChanged || (int) $previousToken !== $token) {
                $this->tokens->release($appointment, 'Released for reschedule', $userId);
                $appointment->refresh();
            }

            $fee = $this->consultationFee($doctor, Patient::where('mr_number', $appointment->mr_number)->first());

            $appointment->forceFill([
                'appointment_date' => $date,
                'appointment_time' => $time,
                'session_key' => $session,
                'doctor_code' => $doctor->code,
                'doctor_name' => $doctor->name,
                'doctor_fee' => $fee,
                'total_amount' => $wasPaid ? $appointment->total_amount : $fee,
                'hospital_share' => $wasPaid ? $appointment->hospital_share : round($fee * 0.40, 2),
            ])->save();

            if ($wasPaid) {
                $this->tokens->reserve($appointment->fresh(), $token, $userId, 'Rescheduled — token reserved in the new queue');
            } else {
                $this->tokens->assignProvisional($appointment->fresh(), $token, $userId);
            }

            return $appointment->fresh(['activeTokenClaim', 'tokenClaims']);
        });
    }

    public function reassignToken(OpdAppointment $appointment, int $token, string $reason, ?int $userId = null): OpdAppointment
    {
        if (trim($reason) === '' && $appointment->isPaid()) {
            throw ValidationException::withMessages([
                'reason' => 'A reason is required when reassigning a reserved token.',
            ]);
        }

        $this->tokens->reassign($appointment, $token, $reason, $userId);

        return $appointment->fresh(['activeTokenClaim', 'tokenClaims']);
    }

    public function nextAppointmentNumber(string $date): string
    {
        $last = OpdAppointment::whereDate('appointment_date', $date)
            ->lockForUpdate()
            ->orderByDesc('id')
            ->first();

        $dailySerial = 1;
        if ($last) {
            $parts = explode('-', (string) $last->appointment_number);
            $dailySerial = isset($parts[2]) ? ((int) $parts[2] + 1) : 1;
        }

        return 'AP-'.Carbon::parse($date)->format('Ymd').'-'.$dailySerial;
    }

    private function isAppointmentNumberRace(UniqueConstraintViolationException $e): bool
    {
        return str_contains($e->getMessage(), 'appointment_number');
    }

    private function requirePatient(string $mrNumber): Patient
    {
        $patient = Patient::where('mr_number', $mrNumber)->first();
        if (! $patient) {
            throw ValidationException::withMessages(['mr_number' => 'No patient found with this MR number.']);
        }

        return $patient;
    }

    private function requireDoctor(string $code): Doctor
    {
        $doctor = Doctor::where('code', $code)->first();
        if (! $doctor) {
            throw ValidationException::withMessages(['doctor_code' => 'The selected doctor could not be found.']);
        }
        if ($doctor->is_active === false) {
            throw ValidationException::withMessages(['doctor_code' => 'This doctor profile is inactive.']);
        }

        return $doctor;
    }

    /**
     * @param  array<string, mixed>  $input
     */
    private function qualifyingAmount(array $input, float $fee): float
    {
        $amount = round((float) ($input['amount_paid'] ?? $input['total_amount'] ?? $fee), 2);
        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount_paid' => 'Enter the amount collected before confirming a reserved token.',
            ]);
        }
        if ($amount + 0.009 < $fee) {
            throw ValidationException::withMessages([
                'amount_paid' => 'A guaranteed token is issued only when the consultation fee of '.number_format($fee, 2).' is collected in full.',
            ]);
        }

        return $amount;
    }
}

<?php

use App\Models\Doctor;
use App\Models\LedgerEntry;
use App\Models\OpdAppointment;
use App\Models\OpdTokenClaim;
use App\Models\Patient;
use App\Models\User;
use App\Services\OpdAppointmentService;
use App\Services\OpdTokenService;
use App\Support\OpdStates;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function deskUser(): User
{
    return User::create([
        'name' => 'Reception',
        'username' => 'reception-opd',
        'email' => 'reception-opd@example.test',
        'password' => bcrypt('password'),
        'branch' => 'Main',
    ]);
}

function clinicDoctor(string $code = 'DOC-AH'): Doctor
{
    return Doctor::create([
        'code' => $code,
        'name' => 'Dr. Ahmed',
        'type' => 'Consultant',
        'is_active' => true,
        'general_normal_fee' => 1500,
        'welfare_normal_fee' => 1000,
        'fee' => 1500,
        'general_normal_percentage' => 25,
    ]);
}

function clinicPatient(string $mr, string $name): Patient
{
    return Patient::create([
        'mr_number' => $mr,
        'registration_date' => now()->toDateString(),
        'name' => $name,
        'marital_status' => 'Single',
        'date_of_birth' => '1995-01-15',
        'age' => 31,
        'gender' => 'Male',
        'is_welfare' => false,
    ]);
}

function tomorrow(): string
{
    return now()->addDay()->toDateString();
}

function bookPayload(Patient $patient, Doctor $doctor, array $overrides = []): array
{
    return array_merge([
        'mr_number' => $patient->mr_number,
        'patient_name' => $patient->name,
        'age' => $patient->age,
        'gender' => $patient->gender,
        'doctor_code' => $doctor->code,
        'appointment_date' => tomorrow(),
        'appointment_time' => '10:00',
        'token_number' => 12,
        'referred_by' => null,
    ], $overrides);
}

it('saves an unpaid booking as booked + unpaid + provisional', function () {
    $user = deskUser();
    $doctor = clinicDoctor();
    $patient = clinicPatient('01001', 'Ali Raza');

    $appointment = app(OpdAppointmentService::class)->book(bookPayload($patient, $doctor, ['token_number' => 12]), false, $user->id);

    expect($appointment->status)->toBe(OpdStates::BOOKED)
        ->and($appointment->payment_status)->toBe(OpdStates::UNPAID)
        ->and($appointment->token_status)->toBe(OpdStates::PROVISIONAL)
        ->and($appointment->token_number)->toBe(12)
        ->and($appointment->preferred_token_number)->toBe(12)
        ->and($appointment->collectedAmount())->toBe(0.0)
        ->and($appointment->activeTokenClaim->lock_key)->toBeNull();

    expect(LedgerEntry::where('source_type', 'opd_appointment')->where('source_id', $appointment->id)->count())->toBe(0);
});

it('saves a paid booking as booked + paid + reserved', function () {
    $user = deskUser();
    $doctor = clinicDoctor();
    $patient = clinicPatient('01002', 'Fatima Noor');

    $appointment = app(OpdAppointmentService::class)->book(bookPayload($patient, $doctor, [
        'token_number' => 13,
        'amount_paid' => 1500,
        'payment_method' => 'cash',
    ]), true, $user->id);

    expect($appointment->status)->toBe(OpdStates::BOOKED)
        ->and($appointment->payment_status)->toBe(OpdStates::PAID)
        ->and($appointment->token_status)->toBe(OpdStates::RESERVED)
        ->and($appointment->token_number)->toBe(13)
        ->and($appointment->hasGuaranteedToken())->toBeTrue()
        ->and($appointment->activeTokenClaim->lock_key)->toBe($doctor->code.'|'.tomorrow().'||13');

    $entries = LedgerEntry::where('source_type', 'opd_appointment')->where('source_id', $appointment->id)->whereNull('reversed_at')->get();
    expect($entries)->toHaveCount(2)
        ->and((float) $entries->where('entry_type', 'debit')->sum('amount'))->toBe(1500.0);
});

it('rejects a second paid reservation of the same token', function () {
    $user = deskUser();
    $doctor = clinicDoctor();
    $first = clinicPatient('01003', 'Fatima Noor');
    $second = clinicPatient('01004', 'Sana Malik');
    $service = app(OpdAppointmentService::class);

    $service->book(bookPayload($first, $doctor, ['token_number' => 13, 'amount_paid' => 1500]), true, $user->id);

    expect(fn () => $service->book(bookPayload($second, $doctor, ['token_number' => 13, 'amount_paid' => 1500]), true, $user->id))
        ->toThrow(ValidationException::class);
});

it('lets a paid booking take a provisional number without cancelling the unpaid appointment', function () {
    $user = deskUser();
    $doctor = clinicDoctor();
    $ali = clinicPatient('01005', 'Ali Raza');
    $fatima = clinicPatient('01006', 'Fatima Noor');
    $service = app(OpdAppointmentService::class);

    $unpaid = $service->book(bookPayload($ali, $doctor, ['token_number' => 12]), false, $user->id);
    $paid = $service->book(bookPayload($fatima, $doctor, ['token_number' => 12, 'amount_paid' => 1500]), true, $user->id);

    $unpaid->refresh();
    expect($paid->token_status)->toBe(OpdStates::RESERVED)
        ->and($paid->token_number)->toBe(12)
        ->and($unpaid->status)->toBe(OpdStates::BOOKED)
        ->and($unpaid->token_status)->toBe(OpdStates::PROVISIONAL)
        ->and($unpaid->token_number)->toBe(12);

    expect(fn () => $service->collectFee($unpaid, [
        'token_number' => 12,
        'amount_paid' => 1500,
        'keep_original_token' => true,
    ], $user->id))->toThrow(ValidationException::class);

    $unpaid->refresh();
    expect($unpaid->payment_status)->toBe(OpdStates::UNPAID)
        ->and($unpaid->status)->toBe(OpdStates::BOOKED);
});

it('keeps an available original token when the unpaid patient pays', function () {
    $user = deskUser();
    $doctor = clinicDoctor();
    $patient = clinicPatient('01007', 'Ali Raza');
    $service = app(OpdAppointmentService::class);

    $appointment = $service->book(bookPayload($patient, $doctor, ['token_number' => 12]), false, $user->id);
    $inspect = app(OpdTokenService::class)->inspect($appointment);
    expect($inspect['available'])->toBeTrue();

    $paid = $service->collectFee($appointment, [
        'token_number' => 12,
        'amount_paid' => 1500,
        'keep_original_token' => true,
        'payment_method' => 'cash',
    ], $user->id);

    expect($paid->token_number)->toBe(12)
        ->and($paid->token_status)->toBe(OpdStates::RESERVED)
        ->and($paid->payment_status)->toBe(OpdStates::PAID);
});

it('blocks an unavailable original token and suggests another', function () {
    $user = deskUser();
    $doctor = clinicDoctor();
    $ali = clinicPatient('01008', 'Ali Raza');
    $fatima = clinicPatient('01009', 'Fatima Noor');
    $service = app(OpdAppointmentService::class);
    $tokens = app(OpdTokenService::class);

    $unpaid = $service->book(bookPayload($ali, $doctor, ['token_number' => 12]), false, $user->id);
    $service->book(bookPayload($fatima, $doctor, ['token_number' => 12, 'amount_paid' => 1500]), true, $user->id);

    $inspect = $tokens->inspect($unpaid->fresh());
    expect($inspect['available'])->toBeFalse()
        ->and($inspect['suggestion'])->toBe(1)
        ->and($inspect['conflict'])->toContain('Token #12 is already reserved');

    $paid = $service->collectFee($unpaid, [
        'token_number' => $inspect['suggestion'],
        'amount_paid' => 1500,
        'keep_original_token' => false,
    ], $user->id);

    expect($paid->token_number)->toBe(1)
        ->and($paid->token_status)->toBe(OpdStates::RESERVED);
});

it('accepts a manually entered available token', function () {
    $user = deskUser();
    $doctor = clinicDoctor();
    $patient = clinicPatient('01010', 'Omar');

    $appointment = app(OpdAppointmentService::class)->book(bookPayload($patient, $doctor, [
        'token_number' => 7,
        'amount_paid' => 1500,
    ]), true, $user->id);

    expect($appointment->token_number)->toBe(7)
        ->and($appointment->token_status)->toBe(OpdStates::RESERVED);
});

it('rejects a manually entered reserved number', function () {
    $user = deskUser();
    $doctor = clinicDoctor();
    $first = clinicPatient('01011', 'Omar');
    $second = clinicPatient('01012', 'Hina');
    $service = app(OpdAppointmentService::class);

    $service->book(bookPayload($first, $doctor, ['token_number' => 7, 'amount_paid' => 1500]), true, $user->id);

    expect(fn () => $service->book(bookPayload($second, $doctor, ['token_number' => 7, 'amount_paid' => 1500]), true, $user->id))
        ->toThrow(ValidationException::class);
});

it('protects guaranteed tokens with a database unique lock key', function () {
    $user = deskUser();
    $doctor = clinicDoctor();
    $patient = clinicPatient('01013', 'Lock Patient');
    $service = app(OpdAppointmentService::class);
    $appointment = $service->book(bookPayload($patient, $doctor, ['token_number' => 4, 'amount_paid' => 1500]), true, $user->id);
    $lock = $appointment->activeTokenClaim->lock_key;

    expect(fn () => OpdTokenClaim::create([
        'opd_appointment_id' => $appointment->id,
        'doctor_code' => $doctor->code,
        'appointment_date' => tomorrow(),
        'session_key' => '',
        'token_number' => 4,
        'status' => OpdStates::RESERVED,
        'lock_key' => $lock,
    ]))->toThrow(UniqueConstraintViolationException::class);
});

it('rolls back fee collection when the chosen token is no longer free', function () {
    $user = deskUser();
    $doctor = clinicDoctor();
    $ali = clinicPatient('01014', 'Ali Raza');
    $fatima = clinicPatient('01015', 'Fatima Noor');
    $service = app(OpdAppointmentService::class);

    $unpaid = $service->book(bookPayload($ali, $doctor, ['token_number' => 9]), false, $user->id);
    $service->book(bookPayload($fatima, $doctor, ['token_number' => 9, 'amount_paid' => 1500]), true, $user->id);

    try {
        $service->collectFee($unpaid, ['token_number' => 9, 'amount_paid' => 1500], $user->id);
        expect(false)->toBeTrue();
    } catch (ValidationException $e) {
        expect($e->errors())->toHaveKey('token_number');
    }

    $unpaid->refresh();
    expect($unpaid->amount_paid)->toBe(0.0)
        ->and($unpaid->payment_status)->toBe(OpdStates::UNPAID)
        ->and(LedgerEntry::where('source_type', 'opd_appointment')->where('source_id', $unpaid->id)->count())->toBe(0);
});

it('checks a paid patient in without requesting another fee', function () {
    $user = deskUser();
    $doctor = clinicDoctor();
    $patient = clinicPatient('01016', 'Fatima Noor');
    $service = app(OpdAppointmentService::class);

    $appointment = $service->book(bookPayload($patient, $doctor, [
        'token_number' => 13,
        'amount_paid' => 1500,
        'appointment_date' => now()->toDateString(),
    ]), true, $user->id);

    $checkedIn = $service->checkIn($appointment, $user->id);

    expect($checkedIn->status)->toBe(OpdStates::CHECKED_IN)
        ->and($checkedIn->token_number)->toBe(13)
        ->and($checkedIn->token_status)->toBe(OpdStates::CONFIRMED)
        ->and($checkedIn->payment_status)->toBe(OpdStates::PAID)
        ->and($checkedIn->collectedAmount())->toBe(1500.0);

    $again = $service->collectFee($checkedIn, ['token_number' => 13, 'amount_paid' => 1500], $user->id);
    expect($again->collectedAmount())->toBe(1500.0);
    expect(LedgerEntry::where('source_type', 'opd_appointment')->where('source_id', $appointment->id)->whereNull('reversed_at')->count())->toBe(2);
});

it('moves a paid token into the new queue on reschedule', function () {
    $user = deskUser();
    $doctor = clinicDoctor();
    $other = clinicDoctor('DOC-BN');
    $other->update(['name' => 'Dr. Bilal']);
    $patient = clinicPatient('01017', 'Sana Malik');
    $service = app(OpdAppointmentService::class);

    $appointment = $service->book(bookPayload($patient, $doctor, [
        'token_number' => 5,
        'amount_paid' => 1500,
    ]), true, $user->id);

    $oldLock = $appointment->activeTokenClaim->lock_key;
    $moved = $service->reschedule($appointment, [
        'doctor_code' => $other->code,
        'appointment_date' => now()->addDays(2)->toDateString(),
        'appointment_time' => '11:00',
        'token_number' => 5,
    ], $user->id);

    expect($moved->doctor_code)->toBe($other->code)
        ->and($moved->token_number)->toBe(5)
        ->and($moved->token_status)->toBe(OpdStates::RESERVED)
        ->and($moved->activeTokenClaim->lock_key)->not->toBe($oldLock)
        ->and(OpdTokenClaim::where('lock_key', $oldLock)->exists())->toBeFalse();
});

it('cancels without deleting payment history and releases the token', function () {
    $user = deskUser();
    $doctor = clinicDoctor();
    $patient = clinicPatient('01018', 'Omar');
    $service = app(OpdAppointmentService::class);

    $appointment = $service->book(bookPayload($patient, $doctor, [
        'token_number' => 8,
        'amount_paid' => 1500,
    ]), true, $user->id);

    $cancelled = $service->cancel($appointment, 'Patient requested', $user->id);

    expect($cancelled->status)->toBe(OpdStates::CANCELLED)
        ->and($cancelled->payment_status)->toBe(OpdStates::PAID)
        ->and($cancelled->amount_paid)->toBe(1500.0)
        ->and($cancelled->token_status)->toBe(OpdStates::RELEASED)
        ->and($cancelled->activeTokenClaim)->toBeNull();

    expect(LedgerEntry::where('source_type', 'opd_appointment')->where('source_id', $appointment->id)->whereNull('reversed_at')->count())->toBe(2);
});

it('interprets legacy completed walk-ins as paid without losing the row', function () {
    $user = deskUser();
    clinicDoctor();

    $appointment = OpdAppointment::create([
        'appointment_number' => 'AP-'.now()->format('Ymd').'-88',
        'appointment_date' => now()->toDateString(),
        'appointment_time' => '09:00:00',
        'mr_number' => '01019',
        'patient_name' => 'Legacy Patient',
        'age' => '40',
        'gender' => 'Female',
        'doctor_code' => 'DOC-AH',
        'doctor_name' => 'Dr. Ahmed',
        'doctor_fee' => 1200,
        'total_amount' => 1200,
        'hospital_share' => 600,
        'token_number' => 3,
        'status' => 'completed',
        'received_by' => $user->id,
    ]);

    expect($appointment->fresh()->payment_status)->toBe(OpdStates::PAID)
        ->and($appointment->collectedAmount())->toBe(1200.0)
        ->and(OpdAppointment::count())->toBe(1);
});

it('posts a walk-in OPD receipt once and does not duplicate on retry', function () {
    $user = deskUser();
    $doctor = clinicDoctor();
    $patient = clinicPatient('01020', 'Walk In');
    $service = app(OpdAppointmentService::class);

    $visit = $service->registerVisit([
        'mr_number' => $patient->mr_number,
        'patient_name' => $patient->name,
        'age' => $patient->age,
        'gender' => $patient->gender,
        'doctor_code' => $doctor->code,
        'doctor_name' => $doctor->name,
        'doctor_fee' => 1500,
        'total_amount' => 1500,
        'appointment_date' => now()->toDateString(),
        'appointment_time' => now()->format('H:i:s'),
        'token_number' => 1,
        'payment_method' => 'cash',
    ], $user->id);

    expect($visit->status)->toBe(OpdStates::COMPLETED)
        ->and($visit->payment_status)->toBe(OpdStates::PAID)
        ->and($visit->token_status)->toBe(OpdStates::USED);

    $entries = LedgerEntry::where('source_type', 'opd_appointment')->where('source_id', $visit->id)->whereNull('reversed_at')->get();
    expect($entries)->toHaveCount(2);

    $again = $service->collectFee($visit, ['token_number' => 1, 'amount_paid' => 1500], $user->id);
    expect($again->collectedAmount())->toBe(1500.0);
    expect(LedgerEntry::where('source_type', 'opd_appointment')->where('source_id', $visit->id)->whereNull('reversed_at')->count())->toBe(2);
});

it('suggests the lowest free token and skips guaranteed numbers only', function () {
    $user = deskUser();
    $doctor = clinicDoctor();
    $service = app(OpdAppointmentService::class);
    $tokens = app(OpdTokenService::class);

    foreach ([1, 2, 3, 5] as $i => $token) {
        $patient = clinicPatient('0200'.$i, 'Holder '.$token);
        $service->book(bookPayload($patient, $doctor, ['token_number' => $token, 'amount_paid' => 1500]), true, $user->id);
    }

    $provisionalPatient = clinicPatient('02009', 'Provisional');
    $service->book(bookPayload($provisionalPatient, $doctor, ['token_number' => 4]), false, $user->id);

    expect($tokens->suggest($doctor->code, tomorrow()))->toBe(4);
});

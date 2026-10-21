<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\OpdAppointment;
use App\Services\OpdAppointmentService;
use App\Services\OpdTokenService;
use App\Support\OpdStates;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OpdAppointmentController extends Controller
{
    public function __construct(
        private OpdAppointmentService $appointments,
        private OpdTokenService $tokens,
    ) {
    }

    public function index(Request $request): View
    {
        $doctors = Doctor::query()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('employee_group')
                    ->orWhereNotIn('employee_group', ['Procedure', 'Service']);
            })
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        if (! $request->exists('date') && ! $request->filled('q')) {
            $request->merge(['date' => now()->toDateString()]);
        }

        $query = OpdAppointment::query()->orderBy('appointment_date')->orderBy('token_number')->orderBy('id');

        if ($request->filled('date')) {
            $query->whereDate('appointment_date', $request->input('date'));
        }
        if ($request->filled('doctor_code')) {
            $query->where('doctor_code', $request->input('doctor_code'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->input('payment_status'));
        }
        if ($request->filled('token_status')) {
            $query->where('token_status', $request->input('token_status'));
        }
        if ($request->filled('q')) {
            $term = '%'.trim((string) $request->input('q')).'%';
            $query->where(function ($inner) use ($term) {
                $inner->where('patient_name', 'like', $term)
                    ->orWhere('mr_number', 'like', $term)
                    ->orWhere('appointment_number', 'like', $term);
            });
        }

        $appointments = $query->paginate(25)->withQueryString();

        return view('patients.appointments.index', compact('appointments', 'doctors'));
    }

    public function show(OpdAppointment $appointment): View
    {
        $appointment->load(['tokenClaims.changedBy', 'receivedBy', 'paymentCollectedBy', 'cancelledBy', 'doctor.department']);
        $inspect = $this->tokens->inspect($appointment);
        $doctors = Doctor::query()->where('is_active', true)->orderBy('name')->get(['id', 'code', 'name']);

        return view('patients.appointments.show', compact('appointment', 'inspect', 'doctors'));
    }

    public function suggestToken(Request $request): JsonResponse
    {
        $data = $request->validate([
            'doctor_code' => 'required|string',
            'appointment_date' => 'required|date',
            'session_key' => 'nullable|string|max:32',
            'appointment_id' => 'nullable|integer',
        ]);

        $suggestion = $this->tokens->suggest(
            $data['doctor_code'],
            $data['appointment_date'],
            $data['session_key'] ?? null,
            isset($data['appointment_id']) ? (int) $data['appointment_id'] : null,
        );

        $taken = array_keys($this->tokens->guaranteedTokens(
            $data['doctor_code'],
            $data['appointment_date'],
            $data['session_key'] ?? null,
            isset($data['appointment_id']) ? (int) $data['appointment_id'] : null,
        ));
        sort($taken);

        return response()->json([
            'token_number' => $suggestion,
            'reserved_tokens' => $taken,
        ]);
    }

    public function validateToken(Request $request): JsonResponse
    {
        $data = $request->validate([
            'doctor_code' => 'required|string',
            'appointment_date' => 'required|date',
            'token_number' => 'required',
            'session_key' => 'nullable|string|max:32',
            'appointment_id' => 'nullable|integer',
        ]);

        $token = $this->tokens->assertValidNumber($data['token_number']);
        $ignore = isset($data['appointment_id']) ? (int) $data['appointment_id'] : null;
        $available = $this->tokens->isAvailable(
            $data['doctor_code'],
            $data['appointment_date'],
            $token,
            $data['session_key'] ?? null,
            $ignore,
        );

        return response()->json([
            'token_number' => $token,
            'available' => $available,
            'message' => $available
                ? 'Token available.'
                : $this->tokens->conflictMessage($data['doctor_code'], $data['appointment_date'], $token, $data['session_key'] ?? null, $ignore),
        ]);
    }

    public function collectFee(Request $request, OpdAppointment $appointment): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'token_number' => 'required|integer|min:1',
            'amount_paid' => 'required|numeric|min:0.01',
            'payment_method' => 'nullable|in:cash,bank,cheque',
            'keep_original_token' => 'sometimes|boolean',
            'idempotency_key' => 'nullable|string|max:80',
        ]);

        $appointment = $this->appointments->collectFee($appointment, $data, $request->user()?->id);

        return $this->respond($request, $appointment, 'Fee collected. Token #'.$appointment->token_number.' is reserved.');
    }

    public function checkIn(Request $request, OpdAppointment $appointment): RedirectResponse|JsonResponse
    {
        $appointment = $this->appointments->checkIn($appointment, $request->user()?->id);

        return $this->respond($request, $appointment, 'Patient checked in on token #'.$appointment->token_number.'.');
    }

    public function complete(Request $request, OpdAppointment $appointment): RedirectResponse|JsonResponse
    {
        $appointment = $this->appointments->complete($appointment, $request->user()?->id);

        return $this->respond($request, $appointment, 'Visit marked completed.');
    }

    public function cancel(Request $request, OpdAppointment $appointment): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'cancellation_reason' => 'nullable|string|max:255',
        ]);

        $appointment = $this->appointments->cancel($appointment, $data['cancellation_reason'] ?? null, $request->user()?->id);

        return $this->respond($request, $appointment, 'Appointment cancelled. Payment history was kept; the token was released.');
    }

    public function noShow(Request $request, OpdAppointment $appointment): RedirectResponse|JsonResponse
    {
        $appointment = $this->appointments->markNoShow($appointment, $request->user()?->id);

        return $this->respond($request, $appointment, 'Appointment marked as no-show. The reserved token was not released.');
    }

    public function reschedule(Request $request, OpdAppointment $appointment): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'doctor_code' => 'required|string',
            'token_number' => 'required|integer|min:1',
            'session_key' => 'nullable|string|max:32',
        ]);

        $appointment = $this->appointments->reschedule($appointment, $data, $request->user()?->id);

        return $this->respond($request, $appointment, 'Appointment moved. Token #'.$appointment->token_number.' applies to the new queue.');
    }

    public function reassignToken(Request $request, OpdAppointment $appointment): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'token_number' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:255',
        ]);

        $appointment = $this->appointments->reassignToken(
            $appointment,
            (int) $data['token_number'],
            (string) ($data['reason'] ?? ''),
            $request->user()?->id,
        );

        return $this->respond($request, $appointment, 'Token reassigned to #'.$appointment->token_number.'.');
    }

    private function respond(Request $request, OpdAppointment $appointment, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'appointment' => $this->payload($appointment),
            ]);
        }

        return redirect()
            ->route('patients.appointments.show', $appointment)
            ->with('success', $message);
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(OpdAppointment $appointment): array
    {
        $inspect = $this->tokens->inspect($appointment);

        return [
            'id' => $appointment->id,
            'appointment_number' => $appointment->appointment_number,
            'appointment_date' => optional($appointment->appointment_date)?->toDateString(),
            'appointment_time' => $appointment->appointment_time,
            'mr_number' => $appointment->mr_number,
            'patient_name' => $appointment->patient_name,
            'doctor_code' => $appointment->doctor_code,
            'doctor_name' => $appointment->doctor_name,
            'token_number' => $appointment->displayToken(),
            'preferred_token_number' => $appointment->preferred_token_number,
            'status' => $appointment->status,
            'status_label' => OpdStates::appointmentLabel((string) $appointment->status),
            'payment_status' => $appointment->payment_status,
            'payment_status_label' => OpdStates::paymentLabel((string) $appointment->payment_status),
            'token_status' => $appointment->token_status,
            'token_status_label' => OpdStates::tokenLabel($appointment->token_status),
            'total_amount' => (float) $appointment->total_amount,
            'amount_paid' => $appointment->collectedAmount(),
            'outstanding' => $appointment->outstandingFee(),
            'has_guaranteed_token' => $appointment->hasGuaranteedToken(),
            'token' => $inspect,
            'receipt_url' => $appointment->isPaid()
                ? route('patients.outdoor_receipt', $appointment->appointment_number)
                : null,
        ];
    }
}

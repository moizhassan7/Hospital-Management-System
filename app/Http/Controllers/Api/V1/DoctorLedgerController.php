<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\LimsDoctor;
use App\Models\LimsDoctorLedger;
use App\Models\LimsDoctorPayout;
use App\Models\LimsLedgerEntry;
use App\Models\LimsPayment;
use App\Services\Lims\DoctorPayoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DoctorLedgerController extends Controller
{
    public function __construct(
        private readonly DoctorPayoutService $payouts,
    ) {}

    public function ledger(LimsDoctor $doctor): JsonResponse
    {
        $doctor = LimsDoctor::query()->findOrFail($doctor->id);
        $this->authorize('viewLedger', $doctor);

        $ledger = LimsDoctorLedger::query()->where('doctor_id', $doctor->id)->first();
        $entries = LimsLedgerEntry::query()
            ->where('doctor_id', $doctor->id)
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        return response()->json([
            'data' => [
                'doctor' => $doctor,
                'balance' => $ledger?->balance ?? '0.00',
                'entries' => $entries,
            ],
        ]);
    }

    public function payouts(LimsDoctor $doctor): JsonResponse
    {
        $doctor = LimsDoctor::query()->findOrFail($doctor->id);
        $this->authorize('viewPayouts', $doctor);

        $rows = LimsDoctorPayout::query()
            ->where('doctor_id', $doctor->id)
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->limit(100)
            ->get();

        return response()->json(['data' => $rows]);
    }

    public function storePayout(Request $request, LimsDoctor $doctor): JsonResponse
    {
        $doctor = LimsDoctor::query()->findOrFail($doctor->id);
        $this->authorize('payout', $doctor);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0'],
            'method' => ['nullable', Rule::in([
                LimsPayment::METHOD_CASH,
                LimsPayment::METHOD_CARD,
                LimsPayment::METHOD_BANK,
                LimsPayment::METHOD_ONLINE,
                LimsPayment::METHOD_ADJUSTMENT,
            ])],
            'notes' => ['nullable', 'string'],
            'paid_at' => ['nullable', 'date'],
            'idempotency_key' => ['nullable', 'string', 'max:191'],
        ]);

        $payout = $this->payouts->payout(
            $doctor,
            (float) $data['amount'],
            $request->user(),
            $data['method'] ?? LimsPayment::METHOD_CASH,
            $data['notes'] ?? null,
            $data['idempotency_key'] ?? $request->header('Idempotency-Key'),
            $data['paid_at'] ?? null,
        );

        $ledger = LimsDoctorLedger::query()->where('doctor_id', $doctor->id)->first();

        return response()->json([
            'data' => [
                'payout' => $payout,
                'balance' => $ledger?->balance ?? '0.00',
            ],
        ], 201);
    }
}

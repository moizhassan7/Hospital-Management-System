<?php

namespace App\Http\Controllers\Lims;

use App\Http\Controllers\Controller;
use App\Models\LimsDoctor;
use App\Models\LimsDoctorLedger;
use App\Models\LimsLedgerEntry;
use App\Models\LimsPayment;
use App\Models\Organization;
use App\Services\Lims\DoctorPayoutService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class LimsDoctorController extends Controller
{
    public function __construct(
        private readonly DoctorPayoutService $payouts,
    ) {}

    public function index()
    {
        $this->authorize('viewAny', LimsDoctor::class);

        $doctors = LimsDoctor::query()
            ->with('ledger')
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        return view('lims.doctors.index', compact('doctors'));
    }

    public function create()
    {
        $this->authorize('create', LimsDoctor::class);

        return view('lims.doctors.form', [
            'doctor' => new LimsDoctor(['is_active' => true]),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', LimsDoctor::class);

        $data = $this->validated($request);
        LimsDoctor::query()->create($data);

        return redirect()
            ->route('pathology.lims_doctors.index')
            ->with('success', 'Referring doctor created.');
    }

    public function edit(LimsDoctor $limsDoctor)
    {
        $this->authorize('update', $limsDoctor);

        return view('lims.doctors.form', [
            'doctor' => $limsDoctor,
        ]);
    }

    public function update(Request $request, LimsDoctor $limsDoctor)
    {
        $this->authorize('update', $limsDoctor);

        $data = $this->validated($request, $limsDoctor);
        $limsDoctor->fill($data)->save();

        return redirect()
            ->route('pathology.lims_doctors.index')
            ->with('success', 'Referring doctor updated.');
    }

    public function ledger(LimsDoctor $limsDoctor)
    {
        $this->authorize('viewLedger', $limsDoctor);

        $ledger = LimsDoctorLedger::query()->where('doctor_id', $limsDoctor->id)->first();
        $entries = LimsLedgerEntry::query()
            ->where('doctor_id', $limsDoctor->id)
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        return view('lims.doctors.ledger', [
            'doctor' => $limsDoctor,
            'ledger' => $ledger,
            'entries' => $entries,
            'canPayout' => request()->user()?->can('payout', $limsDoctor) ?? false,
            'paymentMethods' => [
                LimsPayment::METHOD_CASH,
                LimsPayment::METHOD_CARD,
                LimsPayment::METHOD_BANK,
                LimsPayment::METHOD_ONLINE,
                LimsPayment::METHOD_ADJUSTMENT,
            ],
        ]);
    }

    public function storePayout(Request $request, LimsDoctor $limsDoctor)
    {
        $this->authorize('payout', $limsDoctor);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0'],
            'method' => ['nullable', Rule::in([
                LimsPayment::METHOD_CASH,
                LimsPayment::METHOD_CARD,
                LimsPayment::METHOD_BANK,
                LimsPayment::METHOD_ONLINE,
                LimsPayment::METHOD_ADJUSTMENT,
            ])],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $this->payouts->payout(
                $limsDoctor,
                (float) $data['amount'],
                $request->user(),
                $data['method'] ?? LimsPayment::METHOD_CASH,
                $data['notes'] ?? null,
            );
        } catch (InvalidArgumentException $e) {
            return redirect()
                ->route('pathology.lims_doctors.ledger', $limsDoctor)
                ->withInput()
                ->withErrors(['amount' => $e->getMessage()]);
        }

        return redirect()
            ->route('pathology.lims_doctors.ledger', $limsDoctor)
            ->with('success', 'Payout recorded.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?LimsDoctor $existing = null): array
    {
        $orgId = $existing?->organization_id
            ?? $request->user()?->organization_id
            ?? Organization::query()->where('code', 'MMC')->value('id')
            ?? Organization::query()->orderBy('id')->value('id');

        $data = $request->validate([
            'code' => [
                'nullable',
                'string',
                'max:32',
                Rule::unique('lims_doctors', 'code')
                    ->where(fn ($q) => $q->where('organization_id', $orgId)->whereNull('deleted_at'))
                    ->ignore($existing?->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['organization_id'] = (int) $orgId;
        $data['is_active'] = $request->boolean('is_active');
        $data['code'] = isset($data['code']) && trim((string) $data['code']) !== ''
            ? strtoupper(trim($data['code']))
            : null;

        return $data;
    }
}

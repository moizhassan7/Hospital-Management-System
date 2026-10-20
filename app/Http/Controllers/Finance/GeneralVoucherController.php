<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherCategory;
use App\Models\LedgerEntry;
use App\Services\CashCollectionService;
use App\Services\DoctorShareService;
use App\Services\GeneralVoucherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class GeneralVoucherController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $selfOnly = $this->authorizeView($user);
        $query = Voucher::with(['category', 'createdBy', 'postedBy', 'entries.account'])
            ->orderByDesc('voucher_date')->orderByDesc('id');

        if ($selfOnly) {
            $query->whereHas('category', fn ($q) => $q->where('code', 'cash-handover'))
                ->where('party_type', 'user')->where('party_id', $user->id);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('voucher_date', '>=', $request->input('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('voucher_date', '<=', $request->input('to_date'));
        }
        if ($request->filled('voucher_type')) {
            $query->where('voucher_type', $request->input('voucher_type'));
        }
        if ($request->filled('category')) {
            $query->whereHas('category', fn ($q) => $q->where('code', $request->input('category')));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('search')) {
            $term = '%'.$request->input('search').'%';
            $query->where(function ($q) use ($term) {
                $q->where('voucher_no', 'like', $term)
                    ->orWhere('party_name', 'like', $term)
                    ->orWhere('reference_no', 'like', $term);
            });
        }

        $filteredVoucherIds = (clone $query)->reorder()->select('vouchers.id');
        $credit = (float) LedgerEntry::whereIn('voucher_id', clone $filteredVoucherIds)
            ->where('entry_type', 'credit')->sum('amount');
        $debit = (float) LedgerEntry::whereIn('voucher_id', clone $filteredVoucherIds)
            ->where('entry_type', 'debit')->sum('amount');

        return view('finance.vouchers.index', [
            'vouchers' => $query->paginate(25)->withQueryString(),
            'categories' => VoucherCategory::where('is_active', true)
                ->when($selfOnly, fn ($q) => $q->where('code', 'cash-handover'))
                ->orderBy('default_type')->orderBy('name')->get(),
            'creditTotal' => $credit,
            'debitTotal' => $debit,
            'selfOnly' => $selfOnly,
            'canCreate' => $this->canCreateGeneral($user) || $user->hasPermission('Create Cash Handover'),
            'canReceive' => $user->isSuperAdmin() || $user->hasPermission('Receive Cash Handover'),
        ]);
    }

    public function create(Request $request, CashCollectionService $cashService)
    {
        $user = Auth::user();
        $canGeneral = $this->canCreateGeneral($user);
        $canHandover = $user && $user->hasPermission('Create Cash Handover');
        if (! $canGeneral && ! $canHandover) {
            abort(403, 'Unauthorized action.');
        }

        $categories = VoucherCategory::with('account')->where('is_active', true)
            ->when(! $canGeneral, fn ($q) => $q->where('code', 'cash-handover'))
            ->when($canGeneral && ! $canHandover, fn ($q) => $q->where('code', '!=', 'cash-handover'))
            ->orderBy('default_type')->orderBy('name')->get();
        $selectedCode = $request->input('category', old('category'));
        if (! $selectedCode && $categories->count() === 1) {
            $selectedCode = $categories->first()->code;
        }

        return view('finance.vouchers.create', [
            'categories' => $categories,
            'selectedCode' => $selectedCode,
            'patients' => Patient::orderBy('name')->limit(500)->get(['id', 'mr_number', 'name']),
            'doctors' => app(DoctorShareService::class)->payableDoctors(),
            'suppliers' => Supplier::where('is_active', true)->orderBy('name')->get(['id', 'name', 'company_name']),
            'staff' => User::orderBy('name')->get(['id', 'name', 'username']),
            'cashSnapshot' => $canHandover ? $cashService->snapshot($user->id) : null,
        ]);
    }

    public function store(
        Request $request,
        GeneralVoucherService $voucherService,
        DoctorShareService $shareService,
        CashCollectionService $cashService
    ) {
        $user = Auth::user();
        if (! $user) {
            abort(403, 'Unauthorized action.');
        }
        $validated = $request->validate([
            'category' => ['required', Rule::exists('voucher_categories', 'code')->where('is_active', true)],
            'voucher_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', Rule::in(['cash', 'bank', 'cheque'])],
            'patient_id' => ['nullable', 'integer', 'exists:patients,id'],
            'doctor_id' => ['nullable', 'integer', 'exists:doctors,id'],
            'generic_patient_id' => ['nullable', 'integer', 'exists:patients,id'],
            'generic_doctor_id' => ['nullable', 'integer', 'exists:doctors,id'],
            'supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
            'staff_id' => ['nullable', 'integer', 'exists:users,id'],
            'party_type' => ['nullable', Rule::in(['patient', 'doctor', 'supplier', 'user', 'owner', 'other'])],
            'party_name' => ['nullable', 'string', 'max:255'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'pay_period' => ['nullable', 'date_format:Y-m'],
            'remarks' => ['nullable', 'string', 'max:4000'],
        ]);

        $category = VoucherCategory::with('account')->where('code', $validated['category'])->firstOrFail();
        $isHandover = $category->code === 'cash-handover';
        if ($isHandover) {
            if (! $user->hasPermission('Create Cash Handover')) {
                abort(403, 'Unauthorized action.');
            }
        } elseif (! $this->canCreateGeneral($user)) {
            abort(403, 'Unauthorized action.');
        }

        $amount = round((float) $validated['amount'], 2);
        $partyType = $validated['party_type'] ?? null;
        $partyId = null;
        $partyName = trim((string) ($validated['party_name'] ?? '')) ?: null;
        $metadata = [];
        $status = 'posted';
        $paymentAccount = $voucherService->paymentAccount($validated['payment_method']);

        if ($category->code === 'patient-refund') {
            $request->validate(['patient_id' => ['required', 'exists:patients,id']]);
            $patient = Patient::findOrFail($validated['patient_id']);
            [$partyType, $partyId, $partyName] = ['patient', $patient->id, $patient->name];
            $metadata['mr_number'] = $patient->mr_number;
        } elseif ($category->code === 'doctor-payment') {
            $request->validate(['doctor_id' => ['required', 'exists:doctors,id']]);
            $doctor = Doctor::findOrFail($validated['doctor_id']);
            $summary = $shareService->balanceSummary($doctor->id);
            if ($amount > $summary['pending'] + 0.001) {
                return back()->withErrors(['amount' => 'Amount cannot exceed doctor pending balance of '.number_format($summary['pending'], 2).'.'])->withInput();
            }
            [$partyType, $partyId, $partyName] = ['doctor', $doctor->id, $doctor->name];
            $metadata = [
                'total_share' => $summary['total_share'],
                'previously_paid' => $summary['total_paid'],
                'balance' => round($summary['pending'] - $amount, 2),
            ];
        } elseif ($isHandover) {
            $snapshot = $cashService->snapshot($user->id);
            if ($snapshot['cash_in_hand'] <= 0 || $amount > $snapshot['cash_in_hand'] + 0.001) {
                return back()->withErrors(['amount' => 'Amount cannot exceed cash in hand of '.number_format($snapshot['cash_in_hand'], 2).'.'])->withInput();
            }
            [$partyType, $partyId, $partyName] = ['user', $user->id, $user->name];
            $status = 'pending';
            $paymentAccount = app(\App\Services\LedgerPostingService::class)->mappedAccount('staff_cash');
            $metadata = [
                'total_collected' => $snapshot['total_collected'],
                'previously_handed_over' => $snapshot['previously_handed_over'],
                'breakdown' => $snapshot['modules'],
            ];
        } else {
            [$partyType, $partyId, $partyName] = $this->resolveParty($validated, $partyType, $partyName);
        }

        if (in_array($category->code, ['salary-payment', 'owner-withdrawal'], true) && ! $partyName) {
            return back()->withErrors(['party_name' => 'Payee name is required for this category.'])->withInput();
        }
        if ($category->code === 'salary-payment' && ! empty($validated['pay_period'])) {
            $metadata['pay_period'] = $validated['pay_period'];
        }

        $voucher = $voucherService->create([
            'voucher_date' => $validated['voucher_date'],
            'voucher_type' => $category->default_type,
            'category_id' => $category->id,
            'amount' => $amount,
            'payment_method' => $validated['payment_method'],
            'payment_account_id' => $paymentAccount->id,
            'party_type' => $partyType,
            'party_id' => $partyId,
            'party_name' => $partyName,
            'reference_no' => $validated['reference_no'] ?? null,
            'remarks' => $validated['remarks'] ?? null,
            'status' => $status,
            'metadata' => $metadata ?: null,
        ], $user);

        return redirect()->route('finance.vouchers.show', $voucher)->with('success',
            $status === 'pending' ? 'Contra Voucher created and sent for cash receipt confirmation.' : $voucher->accounting_type.' posted successfully.'
        );
    }

    public function show(Voucher $voucher)
    {
        $user = Auth::user();
        $selfOnly = $this->authorizeView($user);
        $voucher->load(['category.account', 'paymentAccount', 'entries.account', 'createdBy', 'postedBy', 'voidedBy']);
        if ($selfOnly && (! $voucher->isCashHandover() || (int) $voucher->party_id !== (int) $user->id)) {
            abort(403, 'Unauthorized action.');
        }

        return view('finance.vouchers.show', [
            'voucher' => $voucher,
            'canReceive' => $user->isSuperAdmin() || $user->hasPermission('Receive Cash Handover'),
            'canVoid' => $user->isSuperAdmin() || $user->hasPermission('Void Vouchers'),
        ]);
    }

    public function receive(Request $request, Voucher $voucher, GeneralVoucherService $service)
    {
        $user = Auth::user();
        if (! $user || (! $user->isSuperAdmin() && ! $user->hasPermission('Receive Cash Handover'))) {
            abort(403, 'Unauthorized action.');
        }
        $validated = $request->validate([
            'received_amount' => ['required', 'numeric', 'min:0.01'],
            'receiver_remarks' => ['nullable', 'string', 'max:4000'],
        ]);
        $service->receiveCashHandover($voucher, (float) $validated['received_amount'], $user, $validated['receiver_remarks'] ?? null);

        return redirect()->route('finance.vouchers.show', $voucher)->with('success', 'Contra Voucher received and posted.');
    }

    public function reject(Request $request, Voucher $voucher, GeneralVoucherService $service)
    {
        $user = Auth::user();
        if (! $user || (! $user->isSuperAdmin() && ! $user->hasPermission('Receive Cash Handover'))) {
            abort(403, 'Unauthorized action.');
        }
        $validated = $request->validate(['receiver_remarks' => ['nullable', 'string', 'max:4000']]);
        $service->rejectCashHandover($voucher, $user, $validated['receiver_remarks'] ?? null);

        return redirect()->route('finance.vouchers.index')->with('success', 'Contra Voucher rejected.');
    }

    public function void(Request $request, Voucher $voucher, GeneralVoucherService $service)
    {
        $user = Auth::user();
        if (! $user || (! $user->isSuperAdmin() && ! $user->hasPermission('Void Vouchers'))) {
            abort(403, 'Unauthorized action.');
        }
        $validated = $request->validate(['void_reason' => ['required', 'string', 'min:5', 'max:1000']]);
        $service->void($voucher, $user, $validated['void_reason']);

        return redirect()->route('finance.vouchers.show', $voucher)->with('success', 'Voucher reversed and voided.');
    }

    public function doctorBalance(Doctor $doctor, DoctorShareService $shareService)
    {
        if (! $this->canCreateGeneral(Auth::user())) {
            abort(403, 'Unauthorized action.');
        }

        return response()->json($shareService->balanceSummary($doctor->id));
    }

    private function resolveParty(array $validated, ?string $partyType, ?string $partyName): array
    {
        $map = [
            'supplier' => [Supplier::class, 'supplier_id'],
            'patient' => [Patient::class, 'generic_patient_id'],
            'doctor' => [Doctor::class, 'generic_doctor_id'],
            'user' => [User::class, 'staff_id'],
        ];
        if (isset($map[$partyType]) && ! empty($validated[$map[$partyType][1]])) {
            $party = $map[$partyType][0]::findOrFail($validated[$map[$partyType][1]]);

            return [$partyType, $party->id, $party->name];
        }

        return [$partyType ?: ($partyName ? 'other' : null), null, $partyName];
    }

    private function authorizeView(?User $user): bool
    {
        if (! $user) {
            abort(403, 'Unauthorized action.');
        }
        $hospitalAccess = $user->isSuperAdmin() || $user->hasPermission('View Finance')
            || $user->hasPermission('Create Vouchers') || $user->hasPermission('Manage Doctor Payments')
            || $user->hasPermission('Receive Cash Handover');
        $ownAccess = $user->hasPermission('Create Cash Handover');
        if (! $hospitalAccess && ! $ownAccess) {
            abort(403, 'Unauthorized action.');
        }

        return $ownAccess && ! $hospitalAccess;
    }

    private function canCreateGeneral(?User $user): bool
    {
        return $user && ($user->isSuperAdmin() || $user->hasPermission('Create Vouchers')
            || $user->hasPermission('Manage Doctor Payments'));
    }
}

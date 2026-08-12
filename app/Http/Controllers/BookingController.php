<?php

namespace App\Http\Controllers;

use App\Models\CollectionCenter;
use App\Models\LabSampleVial;
use App\Models\LaboratoryPatient;
use App\Models\LimsDoctor;
use App\Models\Organization;
use App\Models\TestPackage;
use App\Models\Test;
use App\Services\Lims\LimsBookingSync;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    public function create()
    {
        $tests = Test::where('category', 'Pathology')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'test_id', 'name', 'price']);
            
        $testPackages = TestPackage::with(['tests:id,test_id,name,price'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $doctors = $this->referringDoctorsForBooking();

        $nextMrNo = LaboratoryPatient::generateMrNo();

        [$collectionCenters, $lockedCollectionCenter, $defaultCollectionCenterId] = $this->bookingCenterContext();

        return view('laboratory.bookings.create', compact(
            'tests',
            'testPackages',
            'doctors',
            'nextMrNo',
            'collectionCenters',
            'lockedCollectionCenter',
            'defaultCollectionCenterId',
        ));
    }

    public function searchPatients(Request $request)
    {
        $query = $request->query('query');

        if (! $query || strlen($query) < 2) {
            return response()->json([]);
        }

        $patients = LaboratoryPatient::where(function ($q) use ($query) {
            $q->where('mr_no', 'like', "%{$query}%")
              ->orWhere('contact_no', 'like', "%{$query}%");
        })
        ->latest()
        ->get(['mr_no', 'patient_name', 'gender', 'age', 'contact_no'])
        ->unique('mr_no')
        ->take(10)
        ->values();

        return response()->json($patients);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $effectiveId = method_exists($user, 'getEffectiveCollectionCenterId')
            ? $user->getEffectiveCollectionCenterId()
            : null;
        
        $isMainLab = $user && method_exists($user, 'isMainLabScope') && $user->isMainLabScope();
        $lockedCcId = ($user && !$user->isSuperAdmin() && !$isMainLab && $effectiveId)
            ? (int) $effectiveId
            : null;

        $orgId = $this->bookingOrganizationId();

        $request->validate([
            'collection_center_id' => [
                $lockedCcId ? 'nullable' : 'required',
                'integer',
                Rule::exists('collection_centers', 'id')->where(fn ($q) => $q->where('is_active', true)),
            ],
            'patient_name' => 'required|string|max:255',
            'gender' => 'required|string|in:Male,Female,Other,Child,Infant,New born',
            'age' => 'required|integer|min:0|max:150',
            'contact_no' => 'nullable|string|max:50',
            'mr_no' => 'nullable|string|max:50',
            'self_referred' => 'nullable|boolean',
            'doctor_id' => [
                'nullable',
                'integer',
                Rule::exists('lims_doctors', 'id')->where(function ($q) use ($orgId) {
                    $q->where('is_active', true)->whereNull('deleted_at');
                    if ($orgId) {
                        $q->where('organization_id', $orgId);
                    }
                }),
            ],
            'refer_by_doctor_name' => 'nullable|required_without:self_referred|string|max:255',
            'tests' => 'required|array|min:1',
            'tests.*.id' => 'required|exists:tests,id',
            'tests.*.list_price' => 'nullable|numeric|min:0',
            'tests.*.discount_type' => 'nullable|string|in:flat,percentage',
            'tests.*.discount_value' => 'nullable|numeric|min:0',
            'tests.*.price' => 'required|numeric|min:0',
            'sub_total' => 'required|numeric|min:0',
            'discount' => 'required|numeric|min:0',
            'discount_type' => 'nullable|string|in:flat,percentage',
            'discount_value' => 'nullable|numeric|min:0',
            'grand_total' => 'required|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'due_amount' => 'required|numeric|min:0',
        ]);

        $collectionCenterId = $lockedCcId
            ?? (int) $request->input('collection_center_id');

        $isSelfReferred = $request->boolean('self_referred');

        $selectedDoctor = null;
        $referByDoctorName = null;
        $preferredDoctorId = null;

        if (! $isSelfReferred) {
            if ($request->filled('doctor_id')) {
                $selectedDoctor = LimsDoctor::query()
                    ->where('id', (int) $request->input('doctor_id'))
                    ->where('is_active', true)
                    ->when($orgId, fn ($q) => $q->where('organization_id', $orgId))
                    ->first();
            }

            // Prefer linked lims_doctors row when chosen from the list; otherwise keep free-typed name.
            $referByDoctorName = $selectedDoctor
                ? trim((string) $selectedDoctor->name)
                : trim((string) $request->refer_by_doctor_name);
            $preferredDoctorId = $selectedDoctor?->id;
        }

        // Match test models to fetch names
        $testIds = collect($request->tests)->pluck('id')->all();
        $testModels = Test::whereIn('id', $testIds)->get()->keyBy('id');

        $selectedTests = [];
        foreach ($request->tests as $testInput) {
            $testId = $testInput['id'];
            $price = (float) $testInput['price'];
            $listPrice = isset($testInput['list_price']) ? (float) $testInput['list_price'] : $price;
            $discountType = $testInput['discount_type'] ?? 'flat';
            $discountValue = isset($testInput['discount_value']) ? (float) $testInput['discount_value'] : 0;
            $testModel = $testModels->get($testId);

            if (! $testModel) {
                continue;
            }

            $selectedTests[] = [
                'id' => $testModel->id,
                'name' => $testModel->name,
                'list_price' => $listPrice,
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                'price' => $price,
                'carry_out' => true,
                'status' => 'Pending',
                'sample_status' => LabSampleVial::STATUS_NOT_COLLECTED,
                'desktop_test_id' => null,
                'desktop_reg_test_id' => null,
            ];
        }

        if (empty($selectedTests)) {
            return back()->withInput()->withErrors(['tests' => 'Please select at least one valid pathology test.']);
        }

        $labRegNo = LaboratoryPatient::generateLabRegistrationNo($collectionCenterId);

        $patient = LaboratoryPatient::create([
            'mr_no' => $request->mr_no ? trim($request->mr_no) : LaboratoryPatient::generateMrNo(),
            'lab_registration_no' => $labRegNo,
            'patient_name' => trim($request->patient_name),
            'gender' => $request->gender,
            'contact_no' => $request->contact_no ? trim($request->contact_no) : null,
            'age' => (int) $request->age,
            'self_referred' => $isSelfReferred,
            'refer_by_doctor_name' => $referByDoctorName,
            'selected_tests' => $selectedTests,
            'sub_total' => (float) $request->sub_total,
            'discount' => (float) $request->discount,
            'discount_type' => $request->input('discount_type', 'flat'),
            'discount_value' => (float) $request->input('discount_value', $request->discount),
            'grand_total' => (float) $request->grand_total,
            'paid_amount' => (float) $request->paid_amount,
            'due_amount' => (float) $request->due_amount,
            'lab_share_total' => 0,
            'hospital_share_total' => 0,
            'previous_due' => 0,
            'status' => 'Pending',
        ]);

        // Phase 1 dual-write: normalized LIMS booking + items + invoice/payment.
        // Quiet so a LIMS failure never blocks the existing Sample Portal flow.
        app(LimsBookingSync::class)->syncQuietly(
            $patient,
            $request->user(),
            $collectionCenterId,
            $preferredDoctorId,
        );

        $success = "Booking created successfully! Registration number: {$labRegNo}. Please collect samples and print the receipt.";

        return redirect()
            ->route('pathology.sample_portal', ['lab_reg_no' => $labRegNo])
            ->with('success', $success)
            ->with('print_receipt_id', $patient->id);
    }

    public function printReceipt($id)
    {
        $patient = LaboratoryPatient::findOrFail($id);

        return view('laboratory.bookings.receipt', compact('patient'));
    }

    public function a4Receipt($id)
    {
        $patient = LaboratoryPatient::findOrFail($id);

        $qrCodeDataUri = null;
        if (!empty($patient->lab_registration_no)) {
            $reportViewUrl = route('patient.report.view', ['regNo' => $patient->lab_registration_no]);
            $qrCodeDataUri = app(\App\Services\PathologyReportService::class)->getQrCodeDataUri($reportViewUrl);
        }

        return view('laboratory.bookings.a4_receipt', compact('patient', 'qrCodeDataUri'));
    }

    public function cancel($id)
    {
        $patient = LaboratoryPatient::findOrFail($id);
        
        $hasCollectedSamples = \App\Models\LabSampleVial::where('laboratory_patient_id', $id)
            ->where('status', '!=', \App\Models\LabSampleVial::STATUS_NOT_COLLECTED)
            ->exists();

        if ($hasCollectedSamples) {
            return back()->with('error', 'Cannot cancel booking: Samples have already been collected or processed.');
        }
        
        $patient->update([
            'status' => 'Cancelled',
            'is_returned' => true,
        ]);
        
        // Also update lims_booking if exists
        \App\Models\LimsBooking::where('laboratory_patient_id', $id)->update(['status' => 'cancelled']);

        return back()->with('success', 'Booking has been cancelled and marked as returned.');
    }

    /**
     * @return array{0: \Illuminate\Support\Collection<int, CollectionCenter>, 1: CollectionCenter|null, 2: int|null}
     */
    private function bookingCenterContext(): array
    {
        $user = auth()->user();
        $locked = null;
        $defaultId = null;

        $effectiveId = method_exists($user, 'getEffectiveCollectionCenterId')
            ? $user->getEffectiveCollectionCenterId()
            : null;

        $isMainLab = $user && method_exists($user, 'isMainLabScope') && $user->isMainLabScope();

        if ($user && !$user->isSuperAdmin() && !$isMainLab && $effectiveId) {
            $locked = CollectionCenter::query()->find($effectiveId);
            $defaultId = $locked?->id;

            return [collect($locked ? [$locked] : []), $locked, $defaultId];
        }

        $centers = CollectionCenter::query()
            ->where('is_active', true)
            ->orderByRaw("CASE WHEN kind = 'main_lab' THEN 0 ELSE 1 END")
            ->orderBy('code')
            ->get();

        $main = $centers->firstWhere('kind', CollectionCenter::KIND_MAIN_LAB);
        $defaultId = old('collection_center_id', $main?->id ?? $centers->first()?->id);

        return [$centers, null, $defaultId ? (int) $defaultId : null];
    }

    /**
     * Active referring doctors for the booking org (shared across all CCs in that org).
     *
     * @return \Illuminate\Support\Collection<int, LimsDoctor>
     */
    private function referringDoctorsForBooking()
    {
        $orgId = $this->bookingOrganizationId();

        return LimsDoctor::query()
            ->where('is_active', true)
            ->when($orgId, fn ($q) => $q->where('organization_id', $orgId))
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'phone']);
    }

    private function bookingOrganizationId(): ?int
    {
        $user = auth()->user();

        $orgId = $user?->organization_id
            ?? Organization::query()->where('code', 'MMC')->value('id')
            ?? Organization::query()->orderBy('id')->value('id');

        return $orgId ? (int) $orgId : null;
    }

    public function collectDue(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $patient = LaboratoryPatient::findOrFail($id);
        $amountToCollect = (float) $request->amount;

        if ($amountToCollect > $patient->due_amount) {
            $amountToCollect = (float) $patient->due_amount;
        }

        $patient->paid_amount += $amountToCollect;
        $patient->due_amount = max(0, $patient->grand_total - $patient->paid_amount);
        $patient->save();

        // Dual-write sync to LIMS
        app(LimsBookingSync::class)->syncQuietly($patient, $request->user());

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Due amount collected successfully!',
                'paid_amount' => $patient->paid_amount,
                'due_amount' => $patient->due_amount,
            ]);
        }

        return back()->with('success', 'Due amount collected successfully!');
    }
}


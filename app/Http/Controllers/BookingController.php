<?php

namespace App\Http\Controllers;

use App\Models\CollectionCenter;
use App\Models\LabReportDoctor;
use App\Models\LabSampleVial;
use App\Models\LaboratoryPatient;
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

        $doctors = LabReportDoctor::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name']);

        $nextMrNo = LaboratoryPatient::generateMrNo();

        [$collectionCenters, $lockedCollectionCenter, $defaultCollectionCenterId] = $this->bookingCenterContext();

        return view('laboratory.bookings.create', compact(
            'tests',
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
        ->get(['mr_no', 'patient_name', 'gender', 'age', 'contact_no', 'file_no'])
        ->unique('mr_no')
        ->take(10)
        ->values();

        return response()->json($patients);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $lockedCcId = ($user && $user->isCollectionCenterScope() && $user->collection_center_id)
            ? (int) $user->collection_center_id
            : null;

        $request->validate([
            'collection_center_id' => [
                $lockedCcId ? 'nullable' : 'required',
                'integer',
                Rule::exists('collection_centers', 'id')->where(fn ($q) => $q->where('is_active', true)),
            ],
            'patient_name' => 'required|string|max:255',
            'gender' => 'required|string|in:Male,Female,Other',
            'age' => 'required|integer|min:0|max:150',
            'contact_no' => 'nullable|string|max:50',
            'file_no' => 'nullable|string|max:50',
            'mr_no' => 'nullable|string|max:50',
            'priority' => 'required|string|in:Routine,Urgent,STAT',
            'self_referred' => 'nullable|boolean',
            'refer_by_doctor_name' => 'nullable|required_without:self_referred|string|max:255',
            'tests' => 'required|array|min:1',
            'tests.*.id' => 'required|exists:tests,id',
            'tests.*.price' => 'required|numeric|min:0',
            'sub_total' => 'required|numeric|min:0',
            'discount' => 'required|numeric|min:0',
            'grand_total' => 'required|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'due_amount' => 'required|numeric|min:0',
        ]);

        $collectionCenterId = $lockedCcId
            ?? (int) $request->input('collection_center_id');

        $isSelfReferred = $request->boolean('self_referred');

        // Match test models to fetch names
        $testIds = collect($request->tests)->pluck('id')->all();
        $testModels = Test::whereIn('id', $testIds)->get()->keyBy('id');

        $selectedTests = [];
        foreach ($request->tests as $testInput) {
            $testId = $testInput['id'];
            $price = (float) $testInput['price'];
            $testModel = $testModels->get($testId);

            if (! $testModel) {
                continue;
            }

            $selectedTests[] = [
                'id' => $testModel->id,
                'name' => $testModel->name,
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

        $labRegNo = LaboratoryPatient::generateLabRegistrationNo();

        $patient = LaboratoryPatient::create([
            'mr_no' => $request->mr_no ? trim($request->mr_no) : LaboratoryPatient::generateMrNo(),
            'lab_registration_no' => $labRegNo,
            'patient_name' => trim($request->patient_name),
            'gender' => $request->gender,
            'contact_no' => $request->contact_no ? trim($request->contact_no) : null,
            'age' => (int) $request->age,
            'file_no' => $request->file_no ? trim($request->file_no) : null,
            'priority' => $request->priority,
            'self_referred' => $isSelfReferred,
            'refer_by_doctor_name' => $isSelfReferred ? null : trim($request->refer_by_doctor_name),
            'selected_tests' => $selectedTests,
            'sub_total' => (float) $request->sub_total,
            'discount' => (float) $request->discount,
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
        app(LimsBookingSync::class)->syncQuietly($patient, $request->user(), $collectionCenterId);

        return redirect()
            ->route('pathology.sample_portal', ['lab_reg_no' => $labRegNo])
            ->with('success', "Booking created successfully! Registration number: {$labRegNo}. Next: collect samples → add to a Sample Batch → dispatch.")
            ->with('print_receipt_id', $patient->id);
    }

    public function printReceipt($id)
    {
        $patient = LaboratoryPatient::findOrFail($id);

        return view('laboratory.bookings.receipt', compact('patient'));
    }

    /**
     * @return array{0: \Illuminate\Support\Collection<int, CollectionCenter>, 1: CollectionCenter|null, 2: int|null}
     */
    private function bookingCenterContext(): array
    {
        $user = auth()->user();
        $locked = null;
        $defaultId = null;

        if ($user && $user->isCollectionCenterScope() && $user->collection_center_id) {
            $locked = CollectionCenter::query()->find($user->collection_center_id);
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
}

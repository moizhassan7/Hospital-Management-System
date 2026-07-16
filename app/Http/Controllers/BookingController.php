<?php

namespace App\Http\Controllers;

use App\Models\LabReportDoctor;
use App\Models\LabSampleVial;
use App\Models\LaboratoryPatient;
use App\Models\Test;
use Illuminate\Http\Request;

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

        return view('laboratory.bookings.create', compact('tests', 'doctors'));
    }

    public function searchPatients(Request $request)
    {
        $query = $request->query('query');

        if (!$query || strlen($query) < 2) {
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
        $request->validate([
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

        $isSelfReferred = $request->boolean('self_referred');

        // Match test models to fetch names
        $testIds = collect($request->tests)->pluck('id')->all();
        $testModels = Test::whereIn('id', $testIds)->get()->keyBy('id');

        $selectedTests = [];
        foreach ($request->tests as $testInput) {
            $testId = $testInput['id'];
            $price = (float) $testInput['price'];
            $testModel = $testModels->get($testId);

            if (!$testModel) {
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
            'mr_no' => $request->mr_no ? trim($request->mr_no) : null,
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

        return redirect()
            ->route('pathology.sample_portal', ['lab_reg_no' => $labRegNo])
            ->with('success', "Booking created successfully! Registration number: {$labRegNo}")
            ->with('print_receipt_id', $patient->id);
    }

    public function printReceipt($id)
    {
        $patient = LaboratoryPatient::findOrFail($id);
        return view('laboratory.bookings.receipt', compact('patient'));
    }
}

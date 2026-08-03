<?php

namespace App\Http\Controllers;

use App\Models\TestPackage;
use App\Models\Test;
use Illuminate\Http\Request;

class TestPackageController extends Controller
{
    public function index()
    {
        $packages = TestPackage::withCount('tests')->latest()->get();
        return view('laboratory.test_packages.index', compact('packages'));
    }

    public function create()
    {
        $tests = Test::where('category', 'Pathology')->where('is_active', true)->orderBy('name')->get();
        return view('laboratory.test_packages.form', compact('tests'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'test_ids' => 'required|array|min:1',
            'test_ids.*' => 'exists:tests,id'
        ]);

        $package = TestPackage::create($request->only(['name', 'description', 'price', 'is_active']));
        $package->tests()->sync($request->test_ids);

        return redirect()->route('pathology.test_packages.index')->with('success', 'Test Package created successfully.');
    }

    public function edit(TestPackage $testPackage)
    {
        $tests = Test::where('category', 'Pathology')->where('is_active', true)->orderBy('name')->get();
        $testPackage->load('tests');
        return view('laboratory.test_packages.form', compact('testPackage', 'tests'));
    }

    public function update(Request $request, TestPackage $testPackage)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'test_ids' => 'required|array|min:1',
            'test_ids.*' => 'exists:tests,id'
        ]);

        $testPackage->update($request->only(['name', 'description', 'price', 'is_active']));
        $testPackage->tests()->sync($request->test_ids);

        return redirect()->route('pathology.test_packages.index')->with('success', 'Test Package updated successfully.');
    }

    public function destroy(TestPackage $testPackage)
    {
        $testPackage->delete();
        return redirect()->route('pathology.test_packages.index')->with('success', 'Test Package deleted successfully.');
    }
}

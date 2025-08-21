<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Display the form to add a new supplier and a list of existing suppliers.
     * Also handles displaying a supplier for editing.
     */
    public function add(Supplier $supplier = null)
    {
        $suppliers = Supplier::all();

        return view('store.supplier', compact('suppliers', 'supplier'));
    }

    /**
     * Store a newly created supplier in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'contact_number' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        Supplier::create([
            'name' => $request->input('name'),
            'company_name' => $request->input('company_name'),
            'contact_number' => $request->input('contact_number'),
            'address' => $request->input('address'),
            'reference' => $request->input('reference'),
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('store.supplier')->with('success', 'Supplier added successfully!');
    }

    /**
     * Update the specified supplier in storage.
     */
    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'contact_number' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $supplier->update([
            'name' => $request->input('name'),
            'company_name' => $request->input('company_name'),
            'contact_number' => $request->input('contact_number'),
            'address' => $request->input('address'),
            'reference' => $request->input('reference'),
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('store.supplier')->with('success', 'Supplier updated successfully!');
    }

    /**
     * Remove the specified supplier from storage.
     */
    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()->route('store.supplier')->with('success', 'Supplier deleted successfully!');
    }
}
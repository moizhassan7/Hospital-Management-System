<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date', now()->format('Y-m-d'));
        
        $expenses = Expense::whereDate('expense_date', $date)->orderByDesc('id')->get();
        $totalExpense = $expenses->sum('amount');
        
        $totalIncome = \App\Models\LaboratoryPatient::whereDate('created_at', $date)
            ->where('status', '!=', 'Cancelled')
            ->sum('paid_amount');
            
        $cashInHand = $totalIncome - $totalExpense;
        
        return view('laboratory.expenses.index', compact('expenses', 'date', 'totalExpense', 'totalIncome', 'cashInHand'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'expense_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string|max:255',
        ]);

        Expense::create($request->only(['expense_date', 'amount', 'description']));

        return back()->with('success', 'Expense added successfully.');
    }

    public function destroy($id)
    {
        $expense = Expense::findOrFail($id);
        $expense->delete();

        return back()->with('success', 'Expense deleted successfully.');
    }
}

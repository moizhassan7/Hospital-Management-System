@extends('layouts.app', ['title' => 'Daily Expenses'])

@section('content')
<div class="hms-page">
    <div class="hms-page-header">
        <h1 class="hms-page-title">Daily Expenses</h1>
    </div>

    @include('partials.flash-alerts')

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="hms-panel p-5 border-t-4 border-blue-500 flex flex-col justify-center items-center text-center">
            <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-1">Total Income</h3>
            <p class="text-3xl font-bold text-gray-800">{{ number_format($totalIncome, 2) }} <span class="text-sm text-gray-500">PKR</span></p>
        </div>
        <div class="hms-panel p-5 border-t-4 border-red-500 flex flex-col justify-center items-center text-center">
            <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-1">Total Expense</h3>
            <p class="text-3xl font-bold text-red-600">{{ number_format($totalExpense, 2) }} <span class="text-sm text-red-400">PKR</span></p>
        </div>
        <div class="hms-panel p-5 border-t-4 border-green-500 flex flex-col justify-center items-center text-center">
            <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-1">Cash In Hand</h3>
            <p class="text-3xl font-bold text-green-600">{{ number_format($cashInHand, 2) }} <span class="text-sm text-green-400">PKR</span></p>
        </div>
    </div>

    <div class="hms-panel mb-6">
        <div class="hms-panel-header">
            <h3 class="hms-panel-title">Add New Expense</h3>
        </div>
        <div class="hms-panel-body">
            <form action="{{ route('pathology.expenses.store') }}" method="POST" class="hms-form">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="hms-form-group">
                        <label class="hms-label">Date</label>
                        <input type="date" name="expense_date" class="hms-input" value="{{ $date }}" required>
                    </div>
                    <div class="hms-form-group md:col-span-2">
                        <label class="hms-label">Description</label>
                        <input type="text" name="description" class="hms-input" placeholder="e.g. Stationary, Refreshments" required maxlength="255">
                    </div>
                    <div class="hms-form-group">
                        <label class="hms-label">Amount (PKR)</label>
                        <input type="number" name="amount" class="hms-input" step="1" min="0" required placeholder="0">
                    </div>
                </div>
                <div class="mt-4 flex justify-end">
                    <button type="submit" class="hms-btn hms-btn-purple">Add Expense</button>
                </div>
            </form>
        </div>
    </div>

    <div class="hms-panel">
        <div class="hms-panel-header flex justify-between items-center">
            <h3 class="hms-panel-title">Expenses for {{ \Carbon\Carbon::parse($date)->format('d-M-Y') }}</h3>
            
            <form action="{{ route('pathology.expenses.index') }}" method="GET" class="flex gap-2">
                <input type="date" name="date" class="hms-input" value="{{ $date }}" required>
                <button type="submit" class="hms-btn hms-btn-outline">Filter</button>
            </form>
        </div>
        
        <div class="hms-panel-body">
            @if($expenses->isEmpty())
                <div class="hms-empty">
                    <p class="hms-empty-title">No expenses found</p>
                    <p class="hms-empty-desc">There are no expenses recorded for this date.</p>
                </div>
            @else
                <div class="hms-table-wrap">
                    <table class="hms-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Description</th>
                                <th>Amount (PKR)</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($expenses as $index => $expense)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $expense->description }}</td>
                                    <td class="font-bold">{{ number_format($expense->amount, 2) }}</td>
                                    <td>
                                        <form action="{{ route('pathology.expenses.destroy', $expense->id) }}" method="POST" onsubmit="return confirm('Delete this expense?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 p-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            <tr class="bg-gray-50 border-t-2 border-gray-200">
                                <td colspan="2" class="text-right font-bold text-gray-700">Total Expense:</td>
                                <td colspan="2" class="font-bold text-red-600 text-lg">{{ number_format($totalExpense, 2) }} PKR</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\FinancialAccount;
use App\Models\User;
use App\Models\VoucherCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class VoucherCategoryController extends Controller
{
    public function index()
    {
        $this->authorizeManage(Auth::user());

        return view('finance.categories.index', [
            'categories' => VoucherCategory::with('account')->orderBy('default_type')->orderBy('name')->get(),
            'accounts' => FinancialAccount::where('is_active', true)->where('is_postable', true)->orderBy('code')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeManage(Auth::user());
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:80', 'unique:voucher_categories,code'],
            'default_type' => ['required', Rule::in(['credit', 'debit'])],
            'account_id' => ['required', Rule::exists('financial_accounts', 'id')->where('is_postable', true)],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);
        $code = Str::slug($validated['code'] ?: $validated['name']);
        if (! $code || VoucherCategory::where('code', $code)->exists()) {
            return back()->withErrors(['code' => 'Choose a unique category code.'])->withInput();
        }
        VoucherCategory::create(array_merge($validated, [
            'code' => $code,
            'is_system' => false,
            'is_active' => true,
        ]));

        return back()->with('success', 'Voucher category added.');
    }

    public function update(Request $request, VoucherCategory $category)
    {
        $this->authorizeManage(Auth::user());
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:80', Rule::unique('voucher_categories', 'code')->ignore($category->id)],
            'default_type' => ['required', Rule::in(['credit', 'debit'])],
            'account_id' => ['required', Rule::exists('financial_accounts', 'id')->where('is_postable', true)],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        if ($category->is_system) {
            $validated['code'] = $category->code;
            $validated['default_type'] = $category->default_type;
            $validated['account_id'] = $category->account_id;
            $validated['is_active'] = true;
        } else {
            $validated['code'] = Str::slug($validated['code']);
            $validated['is_active'] = $request->boolean('is_active');
        }
        $category->update($validated);

        return back()->with('success', 'Voucher category updated.');
    }

    private function authorizeManage(?User $user): void
    {
        if (! $user || (! $user->isSuperAdmin() && ! $user->hasPermission('Manage Voucher Categories'))) {
            abort(403, 'Unauthorized action.');
        }
    }
}

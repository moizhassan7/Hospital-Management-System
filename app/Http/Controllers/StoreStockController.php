<?php

namespace App\Http\Controllers;

use App\Models\PurchaseBill;
use App\Models\StockIssue;
use App\Models\StockReturn;
use App\Models\Supplier;
use App\Services\StoreStock;

/**
 * Purchase, issue and return screens. Saving happens through the JSON
 * endpoints in App\Http\Controllers\Api.
 */
class StoreStockController extends Controller
{
    private const RECENT = 8;

    public function purchase()
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('company_name')->get(['id', 'name', 'company_name']);
        $recent = PurchaseBill::withCount('items')->latest()->limit(self::RECENT)->get();

        return view('store.purchase_stock', compact('suppliers', 'recent'));
    }

    public function issue()
    {
        $recent = StockIssue::withCount('items')->latest()->limit(self::RECENT)->get();

        return view('store.issue_stock', compact('recent'));
    }

    public function returns()
    {
        $recent = StockReturn::latest()->limit(self::RECENT)->get();
        $assetsOut = StoreStock::assetCustody()->count();

        return view('store.return_stock', compact('recent', 'assetsOut'));
    }
}

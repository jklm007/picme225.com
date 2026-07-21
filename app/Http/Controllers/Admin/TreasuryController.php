<?php

namespace App\Http\Controllers\Admin;

use App\Models\Provider;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Setting;

class TreasuryController extends Controller
{
    /**
     * Display the treasury monitoring dashboard.
     */
    public function index()
    {
        // Total virtual ECO liabilities (what we owe to drivers)
        $totalVirtualEco = Provider::sum('eco_wallet_balance');
        
        // Physical CFA reserve (manual input from admin from Bank/Mobile Money)
        $physicalCfaReserve = Setting::get('physical_cfa_reserve', 0);
        
        // Liquidity ratio
        $liquidityRatio = ($totalVirtualEco > 0) ? ($physicalCfaReserve / $totalVirtualEco) * 100 : 100;

        return view('admin.treasury.index', compact('totalVirtualEco', 'physicalCfaReserve', 'liquidityRatio'));
    }

    /**
     * Update the physical CFA reserve amount.
     */
    public function updateReserve(Request $request)
    {
        $this->validate($request, [
            'physical_cfa_reserve' => 'required|numeric|min:0',
        ]);

        Setting::set('physical_cfa_reserve', $request->physical_cfa_reserve);
        Setting::save();

        return back()->with('flash_success', 'Physical CFA reserve updated successfully.');
    }
}

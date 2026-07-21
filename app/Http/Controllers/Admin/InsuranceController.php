<?php

namespace App\Http\Controllers\Admin;

use App\Models\InsuranceClaim;
use App\Services\InsuranceManagerService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;

class InsuranceController extends Controller
{
    protected $insuranceService;

    public function __construct(InsuranceManagerService $insuranceService)
    {
        $this->insuranceService = $insuranceService;
    }

    /**
     * Display the insurance mutual dashboard.
     */
    public function dashboard()
    {
        $totalPool = $this->insuranceService->getTotalPool();
        $claims = InsuranceClaim::with('provider')->orderBy('created_at', 'desc')->paginate(10);
        $pendingClaims = InsuranceClaim::where('status', 'PENDING')->count();
        $totalDisbursed = InsuranceClaim::where('status', 'APPROVED')->sum('amount_approved');

        return view('admin.insurance.dashboard', compact('totalPool', 'claims', 'pendingClaims', 'totalDisbursed'));
    }

    /**
     * Approve a claim and disburse funds to the provider's wallet.
     */
    public function approve(Request $request, $id)
    {
        $this->validate($request, [
            'amount_approved' => 'required|numeric|min:0',
        ]);

        try {
            $claim = InsuranceClaim::findOrFail($id);
            if ($claim->status !== 'PENDING') {
                return back()->with('flash_error', 'This claim has already been processed.');
            }

            $this->insuranceService->approveClaim($claim, $request->amount_approved, $request->admin_comment);

            return back()->with('flash_success', 'Claim approved and funds disbursed to driver wallet.');
        } catch (Exception $e) {
            return back()->with('flash_error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Reject a claim.
     */
    public function reject(Request $request, $id)
    {
        try {
            $claim = InsuranceClaim::findOrFail($id);
            $claim->update([
                'status' => 'REJECTED',
                'admin_comment' => $request->admin_comment
            ]);

            return back()->with('flash_success', 'Claim has been rejected.');
        } catch (Exception $e) {
            return back()->with('flash_error', 'Error: ' . $e->getMessage());
        }
    }
}

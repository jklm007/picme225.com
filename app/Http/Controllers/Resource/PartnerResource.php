<?php

namespace App\Http\Controllers\Resource;

use App\Models\Partner;
use App\Models\User;
use App\Models\PdpStop;
use App\Models\InterurbanCompany;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;
use DB;

class PartnerResource extends Controller
{
    public function index(Request $request)
    {
        $query = Partner::with(['user', 'pdpStop', 'company']);

        if ($request->has('type') && $request->type != '') {
            $query->where('type', $request->type);
        }

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $partners = $query->orderBy('created_at', 'desc')->get();

        return view('admin.partner.index', compact('partners'));
    }

    public function create()
    {
        $users = User::where('user_type', '!=', 'PARTNER')->orderBy('first_name')->get();
        $stations = PdpStop::orderBy('nom_arret')->get();
        $companies = InterurbanCompany::orderBy('name')->get();

        return view('admin.partner.create', compact('users', 'stations', 'companies'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'type' => 'required|in:FLEET_OWNER,SYNDICATE,STATION_AGENT,RECRUITER,AMBASSADOR,SPONSOR',
            'status' => 'required|in:PENDING,ACTIVE,APPROVED,SUSPENDED',
            'tier' => 'required|in:STANDARD,CERTIFIED,PREMIUM,PERMANENT',
            'user_id' => 'nullable|exists:users,id',
            'new_user_first_name' => 'nullable|required_without:user_id|max:255',
            'new_user_last_name' => 'nullable|required_without:user_id|max:255',
            'new_user_email' => 'nullable|required_without:user_id|email|unique:users,email|max:255',
            'new_user_mobile' => 'nullable|required_without:user_id|unique:users,mobile|max:15',
            'new_user_password' => 'nullable|required_without:user_id|min:6',
            'company_name' => 'nullable|max:255',
            'pdp_stop_id' => 'nullable|exists:pdp_stops,id',
            'interurban_company_id' => 'nullable|exists:interurban_companies,id',
            'commission_rules' => 'nullable|json'
        ]);

        try {
            DB::beginTransaction();

            $userId = $request->user_id;

            if (!$userId) {
                $user = User::create([
                    'first_name' => $request->new_user_first_name,
                    'last_name' => $request->new_user_last_name,
                    'email' => $request->new_user_email,
                    'mobile' => $request->new_user_mobile,
                    'password' => bcrypt($request->new_user_password),
                    'user_type' => 'PARTNER'
                ]);
                $userId = $user->id;
            } else {
                $user = User::findOrFail($userId);
                // Optional: Force user_type to PARTNER or DUAL
                // $user->user_type = 'PARTNER'; $user->save();
            }

            $partnerCode = 'PRT-' . strtoupper(Str::random(6));
            while (Partner::where('partner_code', $partnerCode)->exists()) {
                $partnerCode = 'PRT-' . strtoupper(Str::random(6));
            }

            $partnerData = $request->only(['type', 'status', 'tier', 'company_name', 'pdp_stop_id', 'interurban_company_id']);
            $partnerData['user_id'] = $userId;
            $partnerData['partner_code'] = $partnerCode;
            
            if ($request->has('commission_rules') && !empty($request->commission_rules)) {
                $partnerData['commission_rules'] = $request->commission_rules;
            }

            if ($request->hasFile('logo')) {
                $partnerData['logo'] = $request->logo->store('partner');
            }

            Partner::create($partnerData);

            DB::commit();

            return redirect()->route('admin.partner.index')->with('flash_success', 'Partenaire créé avec succès');
        } catch (Exception $e) {
            DB::rollback();
            return back()->with('flash_error', 'Erreur lors de la création du partenaire: ' . $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        try {
            $partner = Partner::findOrFail($id);
            $stations = PdpStop::orderBy('nom_arret')->get();
            $companies = InterurbanCompany::orderBy('name')->get();

            return view('admin.partner.edit', compact('partner', 'stations', 'companies'));
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'Partenaire non trouvé');
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'type' => 'required|in:FLEET_OWNER,SYNDICATE,STATION_AGENT,RECRUITER,AMBASSADOR,SPONSOR',
            'status' => 'required|in:PENDING,ACTIVE,APPROVED,SUSPENDED',
            'tier' => 'required|in:STANDARD,CERTIFIED,PREMIUM,PERMANENT',
            'company_name' => 'nullable|max:255',
            'pdp_stop_id' => 'nullable|exists:pdp_stops,id',
            'interurban_company_id' => 'nullable|exists:interurban_companies,id',
            'commission_rules' => 'nullable|json'
        ]);

        try {
            $partner = Partner::findOrFail($id);

            $partnerData = $request->only(['type', 'status', 'tier', 'company_name', 'pdp_stop_id', 'interurban_company_id']);
            
            if ($request->has('commission_rules') && !empty($request->commission_rules)) {
                $partnerData['commission_rules'] = $request->commission_rules;
            } else {
                $partnerData['commission_rules'] = null;
            }

            if ($request->hasFile('logo')) {
                if ($partner->logo) {
                    \Storage::delete($partner->logo);
                }
                $partnerData['logo'] = $request->logo->store('partner');
            }

            $partner->update($partnerData);

            return redirect()->route('admin.partner.index')->with('flash_success', 'Partenaire mis à jour avec succès');
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'Partenaire non trouvé');
        } catch (Exception $e) {
            return back()->with('flash_error', 'Erreur lors de la mise à jour: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $partner = Partner::findOrFail($id);
            if ($partner->logo) {
                \Storage::delete($partner->logo);
            }
            $partner->delete();
            return back()->with('flash_success', 'Partenaire supprimé avec succès');
        } catch (Exception $e) {
            return back()->with('flash_error', 'Erreur lors de la suppression');
        }
    }
}

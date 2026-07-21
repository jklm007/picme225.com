<?php

namespace App\Http\Controllers\Resource;

use App\Models\StationAgent;
use App\Models\User;
use App\Models\PdpStop;
use App\Models\InterurbanCompany;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Auth;

class StationAgentResource extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $agents = StationAgent::with(['user', 'station', 'company'])->orderBy('created_at', 'desc')->get();
        return view('admin.agent.index', compact('agents'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $users = User::orderBy('first_name')->get();
        $stations = PdpStop::orderBy('name')->get();
        $companies = InterurbanCompany::orderBy('name')->get();
        return view('admin.agent.create', compact('users', 'stations', 'companies'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'user_id' => 'required|exists:users,id|unique:station_agents,user_id',
            'pdp_stop_id' => 'required|exists:pdp_stops,id',
            'interurban_company_id' => 'required|exists:interurban_companies,id',
            'agent_code' => 'required|unique:station_agents,agent_code',
        ]);

        try {
            StationAgent::create($request->all());
            return back()->with('flash_success', 'Agent de gare créé avec succès');
        } catch (Exception $e) {
            return back()->with('flash_error', 'Erreur lors de la création de l\'agent');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $agent = StationAgent::findOrFail($id);
            $users = User::orderBy('first_name')->get();
            $stations = PdpStop::orderBy('name')->get();
            $companies = InterurbanCompany::orderBy('name')->get();
            return view('admin.agent.edit', compact('agent', 'users', 'stations', 'companies'));
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'Agent non trouvé');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'pdp_stop_id' => 'required|exists:pdp_stops,id',
            'interurban_company_id' => 'required|exists:interurban_companies,id',
            'agent_code' => 'required|unique:station_agents,agent_code,' . $id,
        ]);

        try {
            $agent = StationAgent::findOrFail($id);
            $agent->update($request->all());
            return redirect()->route('admin.agent.index')->with('flash_success', 'Agent mis à jour avec succès');
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'Agent non trouvé');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            StationAgent::findOrFail($id)->delete();
            return back()->with('flash_success', 'Agent supprimé avec succès');
        } catch (Exception $e) {
            return back()->with('flash_error', 'Erreur lors de la suppression');
        }
    }
}

<?php

namespace App\Http\Controllers\Resource;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PdpRoute;
use App\Models\PdpStop;
use App\Models\PdpRouteSegment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PdpRouteResource extends Controller
{
    /**
     * Display the list of routes
     */
    public function index()
    {
        $routes = PdpRoute::with(['stops', 'segments', 'company'])->orderBy('created_at', 'desc')->get();
        return view('admin.pdp.routes.index', compact('routes'));
    }

    /**
     * Show the form for creating a new route.
     */
    public function create()
    {
        $companies = \App\Models\InterurbanCompany::all();
        return view('admin.pdp.routes.create', compact('companies'));
    }

    /**
     * Store a newly created route in storage.
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'interurban_company_id' => 'required|exists:interurban_companies,id',
            'description' => 'nullable',
            'type' => 'required|in:COMMUNAL,INTERURBAN,INTERREGIONAL',
            'base_price_per_segment' => 'required|numeric|min:0',
        ]);

        try {
            PdpRoute::create($request->all());
            return redirect()->route('admin.pdp-route.index')->with('flash_success', 'Itinéraire créé avec succès');
        } catch (Exception $e) {
            return back()->with('flash_error', $e->getMessage());
        }
    }

    /**
     * Show the form for editing the route.
     */
    public function edit($id)
    {
        try {
            $route = PdpRoute::findOrFail($id);
            $companies = \App\Models\InterurbanCompany::all();
            return view('admin.pdp.routes.edit', compact('route', 'companies'));
        } catch (Exception $e) {
            return back()->with('flash_error', 'Itinéraire introuvable');
        }
    }

    /**
     * Update the route.
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'interurban_company_id' => 'required|exists:interurban_companies,id',
            'description' => 'nullable',
            'type' => 'required|in:COMMUNAL,INTERURBAN,INTERREGIONAL',
            'base_price_per_segment' => 'required|numeric|min:0',
        ]);

        try {
            $route = PdpRoute::findOrFail($id);
            $route->update($request->all());
            return redirect()->route('admin.pdp-route.index')->with('flash_success', 'Itinéraire mis à jour');
        } catch (Exception $e) {
            return back()->with('flash_error', $e->getMessage());
        }
    }

    /**
     * Import routes from JSON file
     */
    public function import(Request $request)
    {
        // ... (Keep existing import logic but ensure it can take a company_id)
        // For simplicity, I'll keep it as is for now or update if needed.
        // Actually, let's update it to allow selecting a company for the whole import.
        $validator = Validator::make($request->all(), [
            'json_file' => 'required|file|mimes:json,txt|max:10240',
            'interurban_company_id' => 'required|exists:interurban_companies,id'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', 'Fichier JSON invalide ou compagnie manquante');
        }

        try {
            $file = $request->file('json_file');
            $json = file_get_contents($file->getRealPath());
            $routesData = json_decode($json, true);
            $companyId = $request->interurban_company_id;

            if (!is_array($routesData)) {
                return redirect()->back()->with('error', 'Le fichier JSON doit contenir un tableau d\'itinéraires');
            }

            DB::beginTransaction();

            $imported = 0;
            $updated = 0;
            $updateExisting = $request->has('update_existing');

            foreach ($routesData as $rData) {
                if (!isset($rData['name']) || !isset($rData['stops']) || !isset($rData['segments'])) {
                    continue;
                }

                $existingRoute = null;
                if ($updateExisting) {
                    $existingRoute = PdpRoute::where('name', $rData['name'])->where('interurban_company_id', $companyId)->first();
                }

                if ($existingRoute) {
                    $existingRoute->update([
                        'description' => $rData['description'] ?? null,
                        'type' => $rData['type'] ?? 'INTERURBAN',
                        'status' => $rData['status'] ?? 'PROPOSED',
                    ]);
                    $existingRoute->stops()->delete();
                    $existingRoute->segments()->delete();
                    $route = $existingRoute;
                    $updated++;
                } else {
                    $route = PdpRoute::create([
                        'name' => $rData['name'],
                        'interurban_company_id' => $companyId,
                        'description' => $rData['description'] ?? null,
                        'type' => $rData['type'] ?? 'INTERURBAN',
                        'status' => $rData['status'] ?? 'PROPOSED',
                        'is_active' => true,
                        'base_price_per_segment' => $rData['base_price'] ?? 500 // Fallback
                    ]);
                    $imported++;
                }

                $stopMap = [];
                foreach ($rData['stops'] as $sData) {
                    $stop = PdpStop::create([
                        'pdp_route_id' => $route->id,
                        'interurban_company_id' => $companyId,
                        'name' => $sData['name'],
                        'address' => $sData['address'] ?? '',
                        'latitude' => $sData['latitude'],
                        'longitude' => $sData['longitude'],
                        'order' => $sData['order'],
                        'type' => $sData['type'] ?? 'arret'
                    ]);
                    $stopMap[$sData['order']] = $stop->id;
                }

                foreach ($rData['segments'] as $segData) {
                    if (isset($stopMap[$segData['from_stop_order']]) && isset($stopMap[$segData['to_stop_order']])) {
                        PdpRouteSegment::create([
                            'pdp_route_id' => $route->id,
                            'from_stop_id' => $stopMap[$segData['from_stop_order']],
                            'to_stop_id' => $stopMap[$segData['to_stop_order']],
                            'order' => $segData['from_stop_order'],
                            'price' => $segData['price'],
                            'distance_km' => $segData['distance_km'],
                            'is_active' => true
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->back()->with('success', "Import réussi ! {$imported} nouveaux itinéraires créés.");
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Erreur lors de l\'import : ' . $e->getMessage());
        }
    }

    /**
     * Delete a route
     */
    public function destroy($id)
    {
        try {
            $route = PdpRoute::findOrFail($id);
            $route->stops()->delete();
            $route->segments()->delete();
            $route->delete();
            return redirect()->back()->with('success', 'Itinéraire supprimé avec succès');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }
}

<?php

namespace App\Http\Controllers\Resource;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PdpStop;
use App\Models\PdpRoute;
use Exception;

class PdpStopResource extends Controller
{
    public function __construct()
    {
        $this->middleware('demo', ['only' => ['store', 'update', 'destroy']]);
    }

    public function index(Request $request)
    {
        $query = PdpStop::with('routes');

        if ($request->route_id) {
            $query->whereHas('routes', function($q) use ($request) {
                $q->where('pdp_routes.id', $request->route_id);
            });
        }

        $stops = $query->orderBy('name', 'asc')->get();

        if ($request->ajax()) {
            return $stops;
        } else {
            $routes = PdpRoute::all();
            return view('admin.pdp-stop.index', compact('stops', 'routes'));
        }
    }

    public function create(Request $request)
    {
        $routes = PdpRoute::where('status', 'APPROVED')->orWhere('status', 'VOTING')->get();
        $routeId = $request->route_id;
        return view('admin.pdp-stop.create', compact('routes', 'routeId'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'pdp_route_id' => 'required|exists:pdp_routes,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'order' => 'required|integer|min:1',
            'commune' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'max_waiting_time' => 'nullable|integer|min:0',
            'type' => 'required|in:gare,arret',
            'vehicle_category' => 'required|in:car,minibus,both',
        ]);

        try {
            $stopData = $request->except(['pdp_route_id', 'order']);
            $stopData['is_active'] = $request->has('is_active') ? 1 : 0;
            $stopData['is_recommended'] = $request->has('is_recommended') ? 1 : 0;

            $stop = PdpStop::create($stopData);

            // Attacher l'arrêt à l'itinéraire sélectionné avec son ordre
            if ($request->has('pdp_route_id')) {
                $stop->routes()->attach($request->pdp_route_id, ['order' => $request->order ?? 1]);
            }

            return redirect()->route('admin.pdp-stop.index', ['route_id' => $request->pdp_route_id])
                ->with('flash_success', 'Arrêt créé avec succès et affecté à la ligne.');
        } catch (Exception $e) {
            return back()->with('flash_error', 'Erreur lors de la création de l\'arrêt: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        try {
            $stop = PdpStop::with('routes')->findOrFail($id);
            return view('admin.pdp-stop.show', compact('stop'));
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'Arrêt non trouvé');
        }
    }

    public function edit($id)
    {
        try {
            $stop = PdpStop::findOrFail($id);
            $routes = PdpRoute::where('status', 'APPROVED')->orWhere('status', 'VOTING')->get();
            return view('admin.pdp-stop.edit', compact('stop', 'routes'));
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'Arrêt non trouvé');
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'pdp_route_id' => 'required|exists:pdp_routes,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'order' => 'required|integer|min:1',
            'commune' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'max_waiting_time' => 'nullable|integer|min:0',
            'type' => 'required|in:gare,arret',
            'vehicle_category' => 'required|in:car,minibus,both',
        ]);

        try {
            $stop = PdpStop::findOrFail($id);
            $stopData = $request->except(['pdp_route_id', 'order']);
            $stopData['is_active'] = $request->has('is_active') ? 1 : 0;
            $stopData['is_recommended'] = $request->has('is_recommended') ? 1 : 0;

            $stop->update($stopData);

            // Mettre à jour l'itinéraire et l'ordre
            if ($request->has('pdp_route_id')) {
                // Synchroniser avec un seul itinéraire (remplace l'existant s'il y en a)
                $stop->routes()->sync([
                    $request->pdp_route_id => ['order' => $request->order ?? 1]
                ]);
            }

            return redirect()->route('admin.pdp-stop.index', ['route_id' => $request->pdp_route_id])
                ->with('flash_success', 'Arrêt mis à jour avec succès et affecté à la ligne.');
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'Arrêt non trouvé');
        } catch (Exception $e) {
            return back()->with('flash_error', 'Erreur lors de la mise à jour: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $stop = PdpStop::findOrFail($id);
            $route = $stop->routes()->first();
            $routeId = $route ? $route->id : null;
            $stop->delete();
            return redirect()->route('admin.pdp-stop.index', ['route_id' => $routeId])
                ->with('flash_success', 'Arrêt supprimé avec succès');
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'Arrêt non trouvé');
        } catch (Exception $e) {
            return back()->with('flash_error', 'Erreur lors de la suppression');
        }
    }
}

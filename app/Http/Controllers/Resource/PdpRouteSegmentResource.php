<?php

namespace App\Http\Controllers\Resource;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PdpRouteSegment;
use App\Models\PdpRoute;
use App\Models\PdpStop;
use App\Models\ServiceType;
use Exception;

class PdpRouteSegmentResource extends Controller
{
    public function __construct()
    {
        $this->middleware('demo', ['only' => ['store', 'update', 'destroy']]);
    }

    public function index(Request $request)
    {
        $query = PdpRouteSegment::with(['route', 'fromStop', 'toStop', 'serviceType']);

        if ($request->route_id) {
            $query->where('pdp_route_id', $request->route_id);
        }

        $segments = $query->orderBy('order')->get();

        if ($request->ajax()) {
            return $segments;
        } else {
            $routes = PdpRoute::all();
            $serviceTypes = ServiceType::where('sharing_type', 'PDP')->orWhereNull('sharing_type')->get();
            return view('admin.pdp-route-segment.index', compact('segments', 'routes', 'serviceTypes'));
        }
    }

    public function create(Request $request)
    {
        $routes = PdpRoute::where('status', 'APPROVED')->orWhere('status', 'VOTING')->get();
        $routeId = $request->route_id;
        $serviceTypes = ServiceType::where('sharing_type', 'PDP')->orWhereNull('sharing_type')->get();

        // Charger les stops via la relation pivot pdp_route_stops
        $stops = [];
        if ($routeId) {
            $route = PdpRoute::find($routeId);
            if ($route) {
                $stops = $route->stops()->orderBy('pdp_route_stops.order')->get();
            }
        }

        return view('admin.pdp-route-segment.create', compact('routes', 'routeId', 'serviceTypes', 'stops'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'pdp_route_id' => 'required|exists:pdp_routes,id',
            'service_type_id' => 'nullable|exists:service_types,id',
            'allowed_service_types' => 'nullable|array',
            'allowed_service_types.*' => 'exists:service_types,id',
            'from_stop_id' => 'required|exists:pdp_stops,id',
            'to_stop_id' => 'required|exists:pdp_stops,id|different:from_stop_id',
            'order' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'distance_km' => 'nullable|numeric|min:0',
            'commune' => 'nullable|string|max:255',
        ]);

        try {
            // Vérifier que les stops appartiennent à la même route via la table pivot
            $fromStop = PdpStop::findOrFail($request->from_stop_id);
            $toStop   = PdpStop::findOrFail($request->to_stop_id);

            $route = PdpRoute::findOrFail($request->pdp_route_id);
            $routeStopIds = $route->stops()->pluck('pdp_stops.id')->toArray();

            if (!in_array($fromStop->id, $routeStopIds) || !in_array($toStop->id, $routeStopIds)) {
                return back()->with('flash_error', 'Les arrêts doivent appartenir à la même route')->withInput();
            }

            $segmentData = $request->all();
            $segmentData['is_active'] = $request->has('is_active') ? 1 : 0;
            $segmentData['allowed_service_types'] = $request->input('allowed_service_types', null);

            $segment = PdpRouteSegment::create($segmentData);

            return redirect()->route('admin.pdp-route-segment.index', ['route_id' => $request->pdp_route_id])
                ->with('flash_success', 'Segment créé avec succès');
        } catch (Exception $e) {
            return back()->with('flash_error', 'Erreur lors de la création du segment: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        try {
            $segment = PdpRouteSegment::with(['route', 'fromStop', 'toStop', 'serviceType'])->findOrFail($id);
            return view('admin.pdp-route-segment.show', compact('segment'));
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'Segment non trouvé');
        }
    }

    public function edit($id)
    {
        try {
            $segment  = PdpRouteSegment::findOrFail($id);
            $routes   = PdpRoute::where('status', 'APPROVED')->orWhere('status', 'VOTING')->get();
            $serviceTypes = ServiceType::where('sharing_type', 'PDP')->orWhereNull('sharing_type')->get();

            // Charger les stops de la route via la table pivot pdp_route_stops
            $route = PdpRoute::find($segment->pdp_route_id);
            $stops = $route ? $route->stops()->orderBy('pdp_route_stops.order')->get() : collect([]);

            return view('admin.pdp-route-segment.edit', compact('segment', 'routes', 'serviceTypes', 'stops'));
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'Segment non trouvé');
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'pdp_route_id' => 'required|exists:pdp_routes,id',
            'service_type_id' => 'nullable|exists:service_types,id',
            'allowed_service_types' => 'nullable|array',
            'allowed_service_types.*' => 'exists:service_types,id',
            'from_stop_id' => 'required|exists:pdp_stops,id',
            'to_stop_id' => 'required|exists:pdp_stops,id|different:from_stop_id',
            'order' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'distance_km' => 'nullable|numeric|min:0',
            'commune' => 'nullable|string|max:255',
        ]);

        try {
            $segment = PdpRouteSegment::findOrFail($id);

            // Vérifier que les stops appartiennent à la même route via la table pivot
            $fromStop = PdpStop::findOrFail($request->from_stop_id);
            $toStop   = PdpStop::findOrFail($request->to_stop_id);

            $route = PdpRoute::findOrFail($request->pdp_route_id);
            $routeStopIds = $route->stops()->pluck('pdp_stops.id')->toArray();

            if (!in_array($fromStop->id, $routeStopIds) || !in_array($toStop->id, $routeStopIds)) {
                return back()->with('flash_error', 'Les arrêts doivent appartenir à la même route')->withInput();
            }

            $segmentData = $request->all();
            $segmentData['is_active'] = $request->has('is_active') ? 1 : 0;
            $segmentData['allowed_service_types'] = $request->input('allowed_service_types', null);

            $segment->update($segmentData);

            return redirect()->route('admin.pdp-route-segment.index', ['route_id' => $request->pdp_route_id])
                ->with('flash_success', 'Segment mis à jour avec succès');
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'Segment non trouvé');
        } catch (Exception $e) {
            return back()->with('flash_error', 'Erreur lors de la mise à jour: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $segment = PdpRouteSegment::findOrFail($id);
            $routeId = $segment->pdp_route_id;
            $segment->delete();
            return redirect()->route('admin.pdp-route-segment.index', ['route_id' => $routeId])
                ->with('flash_success', 'Segment supprimé avec succès');
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'Segment non trouvé');
        } catch (Exception $e) {
            return back()->with('flash_error', 'Erreur lors de la suppression');
        }
    }
}


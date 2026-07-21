<?php

namespace App\Http\Controllers\Resource;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Setting;
use Exception;
use App\Helpers\Helper;

use App\ServiceType;
use App\KmHour;
use App\ServiceTypeRental;
use App\Service;

class ServiceResource extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('demo', ['only' => ['store', 'update', 'destroy']]);
    }

    private function getAvailableImages()
    {
        return collect(\Illuminate\Support\Facades\Storage::disk('s3')->files('service'))
            ->filter(function($file) {
                return in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['webp', 'png', 'jpg', 'jpeg']);
            })
            ->values();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $services = ServiceType::all();
        if ($request->ajax()) {
            return $services;
        } else {
            return view('admin.service.index', compact('services'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $kmhours = KmHour::all();
        $services = Service::all();
        $companies = \Illuminate\Support\Facades\DB::table('interurban_companies')->where('is_active', 1)->get();
        $images = $this->getAvailableImages();
        return view('admin.service.create', compact('kmhours', 'services', 'companies', 'images'));
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
            'name' => 'required|max:255',
            'provider_name' => 'required|max:255',
            'capacity' => 'required|numeric',
            'fixed' => 'required|numeric',
            'price' => 'required|numeric',
            'minute' => 'required|numeric',
            'distance' => 'required|numeric',
            'calculator' => 'required|in:MIN,HOUR,DISTANCE,DISTANCEMIN,DISTANCEHOUR',
            'image' => 'nullable|mimes:ico,png,jpg,jpeg,webp',
            'day' => 'nullable|numeric',
        ]);

        try {
            $serviceTypeData = $request->all();
            
            // ── Vrais checkboxes HTML ─────────────────────────────────────────
            $realCheckboxes = [
                'requires_feeder_ride', 'can_act_as_feeder', 'allow_without_driver',
                'ambulance', 'is_taxable', 'requires_pro_subscription', 'is_shared'
            ];
            foreach ($realCheckboxes as $cb) {
                $serviceTypeData[$cb] = $request->has($cb) ? 1 : 0;
            }

            // ── Drapeaux géo-zone (hidden inputs 0/1 gérés par JS) ───────────
            $geoFlags = ['is_communal', 'is_intercommunal', 'is_interregional', 'is_intercity'];
            foreach ($geoFlags as $flag) {
                $serviceTypeData[$flag] = (int) $request->input($flag, 0);
            }

            // ── Communes autorisées ─────────────────────────────────────────────────
            $serviceTypeData['communes'] = $request->input('communes', []);

            // Antigravity: Auto-calcul zone_coverage depuis les drapeaux géo
            // Règles : TOUTE_ZONE > INTERCOMMUNAL > COMMUNAL
            $serviceTypeData['zone_coverage'] = $this->inferZoneCoverage(
                (int)($serviceTypeData['is_communal']    ?? 0),
                (int)($serviceTypeData['is_intercommunal'] ?? 0),
                (int)($serviceTypeData['is_interregional'] ?? 0)
            );

            if (!$request->has('hour')) {
                $serviceTypeData['hour'] = 0;
            }
            if (empty($serviceTypeData['interurban_company_id'])) {
                $serviceTypeData['interurban_company_id'] = null;
            }

            if ($request->hasFile('image')) {
                $serviceTypeData['image'] = Helper::upload_picture($request->image);
            } elseif ($request->filled('image_select')) {
                $serviceTypeData['image'] = $request->image_select;
            }

            // Create the ServiceType
            $serviceType = ServiceType::create($serviceTypeData);

            // Determine and associate with the correct service and pivot table data
            $this->associateServices($serviceType, $request);

            // Create entries in ServiceTypeRental pivot table
            if ($request->rental_amount > 0 && $request->has('ren_price') && is_array($request->ren_price)) {
                for ($i = 0; $i < count($request->ren_price); $i++) {
                    if (!empty($request->ren_price[$i])) {
                        ServiceTypeRental::create([
                            'service_type_id' => $serviceType->id,
                            'km_hour_id' => $request->km_hour_id[$i] ?? null,
                            'ren_price' => $request->ren_price[$i],
                        ]);
                    }
                }
            }

            return back()->with('flash_success', 'Service Type Saved Successfully');
        } catch (Exception $e) {
            \Log::error('ServiceResource store error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('flash_error', 'Service Type Not Found');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ServiceType  $serviceType
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            return ServiceType::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'Service Type Not Found');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ServiceType  $serviceType
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $kmhours_service = ServiceTypeRental::where('service_type_id', $id)->with('package')->get();
            $kmhours = KmHour::all();
            $services = Service::all();
            $service = ServiceType::findOrFail($id);
            $companies = \Illuminate\Support\Facades\DB::table('interurban_companies')->where('is_active', 1)->get();
            $images = $this->getAvailableImages();
            return view('admin.service.edit', compact('service', 'kmhours', 'kmhours_service', 'services', 'companies', 'images'));
        } catch (Exception $e) {
            return back()->with('flash_error', 'Erreur lors de l\'accès au service : ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ServiceType  $serviceType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'provider_name' => 'required|max:255',
            'fixed' => 'required|numeric',
            'price' => 'required|numeric',
            'image' => 'nullable|mimes:ico,png,jpg,jpeg,webp',
            'day' => 'nullable|numeric',
            'is_communal' => 'nullable|in:1,0',
            'is_intercommunal' => 'nullable|in:1,0',
            'is_interregional' => 'nullable|in:1,0',
            'max_distance' => 'nullable|numeric',
            'allow_without_driver' => 'nullable|in:0,1',
            'interurban_company_id' => 'nullable|integer',
        ]);

        try {
            $service = ServiceType::findOrFail($id);

            if ($request->hasFile('image')) {
                if ($service->image) {
                    Helper::delete_picture($service->image);
                }
                $service->image = Helper::upload_picture($request->image);
            } elseif ($request->filled('image_select')) {
                $service->image = $request->image_select;
            }

            $updateData = $request->except(['image', '_method', '_token', 'main_services', 'ren_price', 'km_hour_id', 'communes']);

            // ── Vrais checkboxes HTML (absents du request si non cochés) ──────
            $realCheckboxes = [
                'requires_feeder_ride', 'can_act_as_feeder', 'allow_without_driver',
                'ambulance', 'is_taxable', 'requires_pro_subscription', 'is_shared'
            ];
            foreach ($realCheckboxes as $cb) {
                $updateData[$cb] = $request->has($cb) ? 1 : 0;
            }

            // ── Drapeaux géo-zone (hidden inputs avec valeur 0 ou 1 gérée par JS) ──
            // Ne PAS utiliser ->has() ici : ils sont toujours présents dans le request.
            $geoFlags = ['is_communal', 'is_intercommunal', 'is_interregional', 'is_intercity'];
            foreach ($geoFlags as $flag) {
                $updateData[$flag] = (int) $request->input($flag, 0);
            }

            // ── Communes autorisées (checkboxes multiples → JSON array) ────────
            $updateData['communes'] = $request->has('communes') ? $request->input('communes', []) : [];

            // Antigravity: Auto-recalcul zone_coverage depuis les drapeaux géo mis à jour
            $updateData['zone_coverage'] = $this->inferZoneCoverage(
                (int)($updateData['is_communal']    ?? 0),
                (int)($updateData['is_intercommunal'] ?? 0),
                (int)($updateData['is_interregional'] ?? 0)
            );

            // Antigravity: Invalider le cache ZoneFilter pour ce service
            \Illuminate\Support\Facades\Cache::flush();

            if (!$request->has('hour')) {
                $updateData['hour'] = 0;
            }
            if (empty($updateData['interurban_company_id'])) {
                $updateData['interurban_company_id'] = null;
            }

            if (!$request->has('allowed_variants')) {
                $updateData['allowed_variants'] = [];
            }
            
            $service->update($updateData);

            $service_rental = ServiceTypeRental::where('service_type_id', $service->id)->get();
            if (count($service_rental) != 0) {
                ServiceTypeRental::where('service_type_id', $service->id)->delete();
            }
            // Create entries in ServiceTypeRental pivot table
            if ($request->rental_amount > 0 && $request->has('ren_price') && is_array($request->ren_price)) {
                for ($i = 0; $i < count($request->ren_price); $i++) {
                    if (!empty($request->ren_price[$i])) {
                        ServiceTypeRental::create([
                            'service_type_id' => $service->id,
                            'km_hour_id' => $request->km_hour_id[$i] ?? null,
                            'ren_price' => $request->ren_price[$i],
                        ]);
                    }
                }
            }
            //  Determine and associate with the correct service
            \DB::table('service_service_type')->where('service_type_id', $service->id)->delete();
            $this->associateServices($service, $request);

            return redirect()->route('admin.service.index')->with('flash_success', 'Service Type Updated Successfully');
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'Service Type Not Found');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ServiceType  $serviceType
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            ServiceType::find($id)->delete();
            ServiceTypeRental::where('service_type_id', $id)->delete();
            \DB::table('service_service_type')->where('service_type_id', $id)->delete();
            return back()->with('message', 'Service Type deleted successfully');
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'Service Type Not Found');
        } catch (Exception $e) {
            return back()->with('flash_error', 'Service Type Not Found');
        }
    }

    /**
     * Private function to associate services (Rental, Outstation, Ambulance, Standard) with the ServiceType and populate pivot data.
     *
     * @param  \App\ServiceType  $serviceType
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    private function associateServices(ServiceType $serviceType, Request $request)
    {
        $servicesToAssociate = [];
        
        $pivotData = [
            'name' => $request->name,
            'fixed' => $request->fixed,
            'price' => $request->price,
            'minute' => $request->minute,
            'hour' => $request->hour ?: 0,
            'distance' => $request->distance,
            'day' => $request->day,
            'calculator' => $request->calculator,
            'description' => $request->description,
            'ambulance' => $request->has('ambulance') ? 1 : 0,
            'rental_amount' => $request->rental_amount,
            'outstation_price' => $request->outstation_price,
            'status' => 1,
        ];

        if ($request->has('main_services') && is_array($request->main_services)) {
            foreach ($request->main_services as $serviceId) {
                $servicesToAssociate[$serviceId] = $pivotData;
            }
        } else {
            if ($request->rental_amount > 0) {
                $rentalService = Service::where('name', 'Rental')->first();
                if ($rentalService) {
                    $servicesToAssociate[$rentalService->id] = $pivotData;
                }
            }
            if ($request->outstation_price > 0) {
                $outstationService = Service::where('name', 'Outstation')->first();
                if ($outstationService) {
                    $servicesToAssociate[$outstationService->id] = $pivotData;
                }
            }
            if ($request->has('ambulance')) {
                $ambulanceService = Service::where('name', 'Ambulance')->first();
                if ($ambulanceService) {
                    $servicesToAssociate[$ambulanceService->id] = $pivotData;
                }
            }

            if (empty($servicesToAssociate)) {
                $standardService = Service::where('name', 'Standard')->first();
                if ($standardService) {
                    $servicesToAssociate[$standardService->id] = $pivotData;
                }
            }
        }

        // Sync services with pivot data
        $serviceType->services()->sync($servicesToAssociate);
    }

    // =========================================================================
    // Antigravity — Helpers privés
    // =========================================================================

    /**
     * Calcule la zone_coverage à partir des drapeaux géographiques.
     * Miroir de ZoneFilterService::inferZoneCoverage() mais sans dépendance
     * au service complet (contexte admin léger).
     *
     * Règles :
     *  - TOUTE_ZONE    : intercommunal=1 ET interrégional=1
     *  - INTERCOMMUNAL : intercommunal=1 ET interrégional=0
     *  - COMMUNAL      : communal=1 OU aucun flag
     */
    private function inferZoneCoverage(int $communal, int $intercommunal, int $interregional): string
    {
        if ($intercommunal && $interregional) {
            return 'TOUTE_ZONE';
        }
        if ($intercommunal) {
            return 'INTERCOMMUNAL';
        }
        return 'COMMUNAL';
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServiceType;
use App\Models\Service;
use DB;
use Log;

class VehicleTypesApiController extends Controller
{
    /**
     * Récupère tous les types de véhicules disponibles
     * Si service_id est fourni, filtre par service principal
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllVehicleTypes(Request $request)
    {
        try {
            $serviceId = $request->input('service_id');

            $query = ServiceType::query();

            // Si un service_id est fourni, filtrer par la relation many-to-many
            if ($serviceId) {
                $query->whereHas('services', function ($q) use ($serviceId) {
                    $q->where('services.id', $serviceId);
                });
            }

            // Récupérer les véhicules avec leurs services associés et les prix pivot
            $vehicleTypes = $query->with([
                'services' => function ($query) {
                    $query->select('services.id', 'services.name');
                }
            ])->get();

            // Formatter la réponse pour l'application mobile
            $formattedVehicles = $vehicleTypes->map(function ($vehicle) use ($serviceId) {
                // Si un service spécifique est demandé, récupérer les prix de ce service
                $pivotData = null;
                if ($serviceId) {
                    $service = $vehicle->services->where('id', $serviceId)->first();
                    if ($service) {
                        $pivotData = [
                            'fixed' => $service->pivot->fixed,
                            'price' => $service->pivot->price,
                            'minute' => $service->pivot->minute,
                            'hour' => $service->pivot->hour,
                            'distance' => $service->pivot->distance,
                            'calculator' => $service->pivot->calculator,
                        ];
                    }
                }

                return [
                    'id' => $vehicle->id,
                    'name' => $vehicle->name,
                    'provider_name' => $vehicle->provider_name,
                    'image' => $vehicle->image,
                    'capacity' => $vehicle->capacity,
                    'description' => $vehicle->description,
                    // Prix par défaut du véhicule
                    'fixed' => $pivotData['fixed'] ?? $vehicle->fixed,
                    'price' => $pivotData['price'] ?? $vehicle->price,
                    'minute' => $pivotData['minute'] ?? $vehicle->minute,
                    'hour' => $pivotData['hour'] ?? $vehicle->hour,
                    'distance' => $pivotData['distance'] ?? $vehicle->distance,
                    'calculator' => $pivotData['calculator'] ?? $vehicle->calculator,
                    // Services principaux associés
                    'main_services' => $vehicle->services->map(function ($service) {
                        return [
                            'id' => $service->id,
                            'name' => $service->name,
                        ];
                    }),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedVehicles,
                'count' => $formattedVehicles->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('Error in getAllVehicleTypes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération des types de véhicules',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère tous les services principaux avec leurs véhicules
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getServicesWithVehicles()
    {
        try {
            $services = Service::with([
                'serviceTypes' => function ($query) {
                    $query->select(
                        'service_types.id',
                        'service_types.name',
                        'service_types.image',
                        'service_types.fixed',
                        'service_types.capacity'
                    );
                }
            ])->get();

            $formattedServices = $services->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'vehicle_count' => $service->serviceTypes->count(),
                    'vehicles' => $service->serviceTypes->map(function ($vehicle) use ($service) {
                        return [
                            'id' => $vehicle->id,
                            'name' => $vehicle->name,
                            'image' => $vehicle->image,
                            'base_price' => $vehicle->pivot->fixed ?? $vehicle->fixed,
                            'capacity' => $vehicle->capacity,
                        ];
                    }),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedServices,
            ]);

        } catch (\Exception $e) {
            Log::error('Error in getServicesWithVehicles: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération des services',
            ], 500);
        }
    }
}

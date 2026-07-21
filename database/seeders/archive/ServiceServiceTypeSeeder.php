<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;
use App\Models\ServiceType;
use Illuminate\Support\Facades\DB;

class ServiceServiceTypeTableSeeder extends Seeder
{
    public function run()
    {
        // Supprimer les données existantes pour repartir de zéro
        DB::table('service_service_type')->truncate();

        // Récupérer tous les services
        $services = Service::all();

        // Récupérer tous les types de services
        $serviceTypes = ServiceType::all();

        // Iterer sur tous les services
        foreach ($services as $service) {
            // Iterer sur tous les types de services
            foreach ($serviceTypes as $serviceType) {
                // Définir les valeurs pour la table pivot en fonction du service et du type de service
                $pivotData = [
                    'fixed' => $serviceType->fixed, // Copier 'fixed' de service_types par défaut
                    'price' => $serviceType->price, // Copier 'price' de service_types par défaut
                    'minute' => $serviceType->minute ?? null, // Copier 'minute' si disponible, sinon null
                    'hour' => $serviceType->hour ?? null,     // Copier 'hour' si disponible, sinon null
                    'distance' => $serviceType->distance ?? null, // Copier 'distance' si disponible, sinon null
                    'day' => null, // Par défaut null, vous pouvez définir une logique spécifique
                    'calculator' => $serviceType->calculator ?? null, // Copier 'calculator' si disponible, sinon null
                    'description' => "Service {$service->name} pour type de véhicule {$serviceType->name}", // Description générique
                    'status' => 1, // Actif par défaut
                    'ambulance' => $serviceType->ambulance ?? 0, // Copier 'ambulance' de service_types, sinon 0
                    'rental_amount' => $serviceType->rental_amount ?? null, // Copier 'rental_amount', sinon null
                    'outstation_price' => $serviceType->outstation_price ?? null, // Copier 'outstation_price', sinon null
                ];

                // Ajouter des logiques spécifiques par service si nécessaire
                if ($service->name == 'Rental') {
                    $pivotData['rental_amount'] = $serviceType->rental_amount > 0 ? $serviceType->rental_amount : 50.00; // Exemple : montant de location par défaut si non défini dans service_type
                    $pivotData['description'] = "Service de location {$serviceType->name}";
                } elseif ($service->name == 'Outstation') {
                    $pivotData['outstation_price'] = $serviceType->outstation_price > 0 ? $serviceType->outstation_price : 0.50; // Exemple : prix hors ville par km par défaut
                    $pivotData['calculator'] = 'DISTANCE'; // Forcer le calculateur à DISTANCE pour Outstation
                    $pivotData['description'] = "Service hors ville {$serviceType->name}";
                } elseif ($service->name == 'Ambulance') {
                    $pivotData['ambulance'] = 1; // Forcer ambulance à 1 pour le service Ambulance
                    $pivotData['calculator'] = 'DISTANCEMIN'; // Calculateur DISTANCEMIN pour Ambulance (exemple)
                    $pivotData['description'] = "Service ambulance pour {$serviceType->name}";
                    $pivotData['status'] = 1; // S'assurer que le service ambulance est actif par défaut
                }


                // Associer le service au type de service avec les données pivot
                $service->serviceTypes()->attach($serviceType->id, $pivotData);
            }
        }
    }
}

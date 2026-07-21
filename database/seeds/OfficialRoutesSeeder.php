<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use App\Models\InterurbanCompany;
use App\Models\PdpRoute;
use App\Models\PdpStop; // Assuming you have a model for stops
use App\Models\PdpRouteSegment; // Assuming you have a model for segments

class OfficialRoutesSeeder extends Seeder
{
    /**
     * Coordonnées approximatives des principales villes (Lat, Lng)
     */
    protected $citiesCoordinates = [
        'Abidjan' => ['lat' => 5.3600, 'lng' => -4.0083],
        'Bouaké' => ['lat' => 7.6900, 'lng' => -5.0300],
        'Yamoussoukro' => ['lat' => 6.8276, 'lng' => -5.2893],
        'San-Pédro' => ['lat' => 4.7485, 'lng' => -6.6363],
        'Man' => ['lat' => 7.4125, 'lng' => -7.5539],
        'Korhogo' => ['lat' => 9.4580, 'lng' => -5.6297],
        'Daloa' => ['lat' => 6.8770, 'lng' => -6.4503],
        'Gagnoa' => ['lat' => 6.1319, 'lng' => -5.9506],
        'Issia' => ['lat' => 6.4928, 'lng' => -6.5856],
        'Soubré' => ['lat' => 5.7858, 'lng' => -6.5972],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $jsonPath = database_path('seeds/official_routes_data.json');

        if (!File::exists($jsonPath)) {
            $this->command->error("Fichier JSON introuvable : $jsonPath");
            return;
        }

        $jsonData = json_decode(File::get($jsonPath), true);
        
        if (!$jsonData || !isset($jsonData['companies'])) {
             $this->command->error("Format JSON invalide.");
             return;
        }

        foreach ($jsonData['companies'] as $companyData) {
            // Tentative de trouver la compagnie par nom (recherche floue ou par id mappé)
            // On suppose que les compagnies ont étés créées par InterurbanCompaniesSeeder avec des noms courts (UTB, STIF, etc.)
            // On utilise l'ID du json (ex: 'utb') pour matcher le nom stocké en base si possible, ou on cherche dans le nom long
            
            $companyName = strtoupper($companyData['id']); // ex: UTB
            // Mapping spécifique si nécessaire
            if ($companyData['id'] == 'art_luxury') $companyName = 'ART Luxury';
            
            $company = InterurbanCompany::where('name', 'LIKE', "%$companyName%")->first();

            if (!$company) {
                $this->command->warn("Compagnie non trouvée en base pour : " . $companyData['name'] . ". Création...");
                $company = InterurbanCompany::create([
                    'name' => $companyData['name'],
                    'logo' => $companyData['logo'] ?? null,
                    'is_active' => true
                ]);
            }

            $this->command->info("Traitement des routes pour : " . $company->name);

            foreach ($companyData['routes'] as $routeData) {
                $this->createRoute($company, $routeData);
            }
        }
    }

    protected function createRoute($company, $routeData)
    {
        $sourceName = $routeData['source'];
        $destName = $routeData['destination'];
        
        // Coordonnées
        $sourceCoords = $this->citiesCoordinates[$sourceName] ?? null;
        $destCoords = $this->citiesCoordinates[$destName] ?? null;

        if (!$sourceCoords || !$destCoords) {
            $this->command->warn("Coordonnées manquantes pour $sourceName ou $destName. Route ignorée.");
            return;
        }

        // Créer ou récupérer les arrêts (Stops)
        // On crée des arrêts de type 'gare' pour ces compagnies
        $sourceStop = $this->findOrCreateStop($sourceName, $sourceCoords, $company->id);
        $destStop = $this->findOrCreateStop($destName, $destCoords, $company->id);

        // Créer la Route PDP
        $routeName = "$sourceName - $destName";
        
        $pdpRoute = PdpRoute::firstOrCreate(
            [
                'name' => $routeName,
                'interurban_company_id' => $company->id,
            ],
            [
                'type' => 'interurban', // Supposons un type
                'status' => 'APPROVED', // Routes officielles
                'is_active' => true,
                'description' => "Ligne officielle " . $company->name,
                'base_price_per_segment' => $routeData['price'], 
                // Note: Si le modèle de prix est par route entière, on met le prix ici. 
                // Si c'est dynamique, il faudra adapter. 
                // Ici on simplifie: 1 segment = route entière.
                'created_by_user_id' => 1, // Admin par défaut
            ]
        );
        
        // Mettre à jour le prix si existe déjà
        $pdpRoute->update(['base_price_per_segment' => $routeData['price']]);

        // Créer le segment unique pour cette route directe
        // D'abord vérifier si le segment existe
        // Note: PdpRouteSegment table logic required. Assuming simplified relation.
        
        // Attacher les arrêts via pivot ou table segments ? 
        // PdpRoute a hasMany segments.
        // PdpStop a order ? No, RouteStops pivot usually. 
        // Checking PdpRoute.php: hasMany stops (PdpStop has pdp_route_id).
        // WARNING: PdpStop has pdp_route_id imply one stop belongs to one route? 
        // If correct, we must create Specific Stops for THIS route.
        // Let's check PdpStop.php again. Yes: belongsTo Route. 
        // So we cannot reuse stops across routes implies we create new stops for each route? 
        // Or is it a Hub system? "is_outstation_hub".
        // Use PdpStop with pdp_route_id.
        
        // Création des arrêts spécifiques à cette route (Source et Destination)
        // Order 0 et Order 1
        
        $this->createRouteStop($pdpRoute, $sourceName, $sourceCoords, 0, 'start');
        $this->createRouteStop($pdpRoute, $destName, $destCoords, 1, 'end');

        $this->command->info("Route créée/mise à jour : $routeName (" . $routeData['price'] . " FCFA)");
    }

    protected function findOrCreateStop($name, $coords, $companyId)
    {
        // Cette méthode servait si les arrêts étaient globaux. 
        // Avec PdpStop lié à route_id, on crée directement dans createRouteStop.
        return null; 
    }

    protected function createRouteStop($route, $name, $coords, $order, $typeMarker)
    {
        // On cherche si un arrêt existe déjà pour cette route à cet ordre (update)
        PdpStop::updateOrCreate(
            [
                'pdp_route_id' => $route->id,
                'order' => $order,
            ],
            [
                'name' => "Gare " . $route->company->name . " - " . $name,
                'address' => $name, // Ville
                'commune' => $name,
                'latitude' => $coords['lat'],
                'longitude' => $coords['lng'],
                'interurban_company_id' => $route->interurban_company_id,
                'type' => 'gare',
                'is_active' => true,
                'status' => 'APPROVED'
            ]
        );
    }
}

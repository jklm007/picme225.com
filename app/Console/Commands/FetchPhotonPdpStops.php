<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Commune;
use App\Models\PdpStop;
use App\Services\PhotonGeocodingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class FetchPhotonPdpStops extends Command
{
    protected $signature = 'pdp:fetch-photon';
    protected $description = 'Peuple la table pdp_stops en interrogeant Photon (OSRM) pour chaque commune';

    public function handle(PhotonGeocodingService $photonService)
    {
        $this->info('Début du peuplement automatique des arrêts PDP depuis Photon...');

        $communes = Commune::all();
        $motsCles = ['carrefour', 'pharmacie', 'marché', 'mairie', 'hôpital', 'gare', 'école'];

        $countTotal = 0;

        foreach ($communes as $commune) {
            $this->info("Traitement de la commune : {$commune->commune}");

            foreach ($motsCles as $mot) {
                $query = "{$mot} {$commune->commune}";
                $this->line(" - Recherche : {$query}");

                $results = $photonService->searchAndValidate($query, $commune->id);

                foreach ($results as $res) {
                    // On ne garde que les points avec un bon score (> 40) pour éviter trop de déchets
                    if ($res['score'] >= 40) {
                        
                        // Déterminer le type
                        $type = 'carrefour';
                        if (strpos(strtolower($res['nom']), 'pharmacie') !== false) $type = 'commerce';
                        if (strpos(strtolower($res['nom']), 'marché') !== false) $type = 'lieu_public';
                        if (strpos(strtolower($res['nom']), 'hôpital') !== false) $type = 'lieu_public';
                        if (strpos(strtolower($res['nom']), 'mairie') !== false) $type = 'communal';
                        if (strpos(strtolower($res['nom']), 'gare') !== false) $type = 'gare';

                        // Vérifier qu'un arrêt n'existe pas déjà tout près (doublon)
                        $existe = PdpStop::withinRadius($res['latitude'], $res['longitude'], 0.05)->exists(); // 50m

                        if (!$existe) {
                            $stop = new PdpStop();
                            $stop->nom_arret = substr($res['nom'], 0, 250);
                            $stop->type_arret = $type;
                            $stop->commune_id = $commune->id;
                            $stop->adresse = substr($res['adresse_formatee'], 0, 250);
                            $stop->description = "Import Auto Photon ($mot)";
                            $stop->source_coordonnees = 'photon';
                            $stop->photon_place_id = $res['photon_raw_data']['properties']['osm_id'] ?? null;
                            $stop->photon_raw_data = $res['photon_raw_data'];
                            $stop->statut_validation = $res['statut'];
                            $stop->confidence_score = $res['score'];
                            // Use the custom setter that populates the geography column
                            $stop->setLocationAttribute($res['latitude'], $res['longitude']);
                            
                            $stop->save();
                            $countTotal++;
                        }
                    }
                }
                
                // Petit délai pour ne pas spammer Photon
                usleep(500000); // 0.5s
            }
        }

        $this->info("Terminé ! {$countTotal} arrêts PDP ont été créés.");
    }
}

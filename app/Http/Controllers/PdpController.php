<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Commune;
use App\Models\PdpStop;
use App\Services\PhotonGeocodingService;

class PdpController extends Controller
{
    protected $photonService;

    public function __construct(PhotonGeocodingService $photonService)
    {
        $this->photonService = $photonService;
    }

    /**
     * GET /communes
     */
    public function getCommunes()
    {
        // On ne retourne pas le polygone car très lourd, juste le centre
        $communes = Commune::select('id', 'ville', 'commune', 'latitude_centre', 'longitude_centre', 'statut')
                           ->where('statut', 'actif')
                           ->get();
        return response()->json(['status' => true, 'data' => $communes]);
    }

    /**
     * GET /communes/{id}/arrets
     */
    public function getCommuneArrets($id)
    {
        $stops = PdpStop::where('commune_id', $id)
                        ->where('statut_validation', '!=', 'rejete')
                        ->get(['id', 'nom_arret', 'type_arret', 'adresse', 'latitude', 'longitude']);
                        
        return response()->json(['status' => true, 'data' => $stops]);
    }

    /**
     * GET /pdp/nearby?lat=&lng=&radius=
     */
    public function getNearbyPdp(Request $request)
    {
        $request->validate([
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'radius' => 'nullable|numeric' // En km, défaut 2
        ]);

        $lat = $request->lat;
        $lng = $request->lng;
        $radius = $request->radius ?? 2;

        if ($lat && $lng) {
            $stops = PdpStop::withinRadius($lat, $lng, $radius)
                            ->where('statut_validation', '!=', 'rejete')
                            ->get(['id', 'nom_arret as name', 'type_arret as type', 'adresse as address', 'latitude', 'longitude']);
        } else {
            // Rétrocompatibilité : renvoyer tous les arrêts si pas de coordonnées
            $stops = PdpStop::where('statut_validation', '!=', 'rejete')
                            ->get(['id', 'nom_arret as name', 'type_arret as type', 'adresse as address', 'latitude', 'longitude']);
        }

        return response()->json(['status' => true, 'data' => $stops]);
    }

    /**
     * GET /pdp/search?q=&commune_id=
     * Recherche hybride (DB locale + Photon en assistance)
     */
    public function searchPdp(Request $request)
    {
        $q = $request->q;
        $communeId = $request->commune_id;

        if (!$q) {
            return response()->json(['status' => false, 'message' => 'Query manquante']);
        }

        // 1. Recherche dans la base locale (très rapide)
        $localQuery = PdpStop::where('nom_arret', 'like', "%{$q}%")
                             ->orWhere('adresse', 'like', "%{$q}%");
                             
        if ($communeId) {
            $localQuery->where('commune_id', $communeId);
        }
        
        $localResults = $localQuery->limit(10)->get();

        // 2. Si on veut aussi appeler Photon pour enrichir les résultats ou valider
        $photonResults = $this->photonService->searchAndValidate($q, $communeId);

        return response()->json([
            'status' => true,
            'data' => [
                'local_pdp' => $localResults,
                'photon_suggestions' => $photonResults
            ]
        ]);
    }

    /**
     * POST /pdp/create
     */
    public function createPdp(Request $request)
    {
        $request->validate([
            'nom_arret' => 'required|string',
            'type_arret' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'commune_id' => 'nullable|exists:communes,id'
        ]);

        $stop = new PdpStop($request->except(['latitude', 'longitude']));
        $stop->setLocationAttribute($request->latitude, $request->longitude);
        
        // Si c'est un import Photon, on force la validation selon le score
        if ($request->source_coordonnees === 'photon') {
            $score = $request->confidence_score ?? 0;
            $stop->statut_validation = $score >= 90 ? 'automatique' : ($score >= 60 ? 'en_attente' : 'manuel');
        } else {
            // Un admin crée manuellement
            $stop->statut_validation = 'automatique';
            $stop->confidence_score = 100;
        }

        $stop->save();

        return response()->json(['status' => true, 'message' => 'Arrêt PDP créé avec succès', 'data' => $stop]);
    }

    /**
     * PUT /pdp/{id}/correct
     */
    public function correctPdp(Request $request, $id)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric'
        ]);

        $stop = PdpStop::findOrFail($id);
        
        // Mettre à jour les coordonnées et regénérer le POINT PostGIS
        $stop->setLocationAttribute($request->latitude, $request->longitude);
        
        // Reset le statut si corrigé manuellement
        $stop->source_coordonnees = 'admin';
        $stop->statut_validation = 'automatique';
        $stop->confidence_score = 100;
        $stop->save();

        return response()->json(['status' => true, 'message' => 'Coordonnées corrigées avec succès', 'data' => $stop]);
    }
}

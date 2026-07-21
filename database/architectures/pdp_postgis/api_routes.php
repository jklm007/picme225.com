<?php

use Illuminate\Support\Facades\Route;
use App\Architectures\PdpPostgis\Controllers\PdpController;

/*
|--------------------------------------------------------------------------
| Routes API PDP (PostGIS & Photon)
|--------------------------------------------------------------------------
| Ces routes doivent être incluses dans routes/api.php
| Si vous utilisez l'authentification Passport/Sanctum, vous pouvez les
| envelopper dans un middleware : Route::middleware('auth:api')->group(...)
|
*/

Route::prefix('pdp')->group(function () {
    // Liste des communes disponibles (centre géographique)
    Route::get('/communes', [PdpController::class, 'getCommunes']);

    // Récupère tous les arrêts PDP pour une commune spécifique
    Route::get('/communes/{id}/arrets', [PdpController::class, 'getCommuneArrets']);

    // Recherche d'un arrêt via son nom (Fusion BD Locale + Photon OSRM)
    // Query params attendus : ?q=Carrefour Angré&commune_id=1
    Route::get('/search', [PdpController::class, 'searchPdp']);

    // Récupère les arrêts proches d'une position GPS (utilise PostGIS ST_DWithin)
    // Query params attendus : ?lat=5.34&lng=-4.02&radius=2
    Route::get('/nearby', [PdpController::class, 'getNearbyPdp']);

    // --- Administration / Vérification (Devrait être protégé par un middleware Admin) ---

    // Création d'un nouvel arrêt PDP manuellement ou après validation Photon
    Route::post('/create', [PdpController::class, 'createPdp']);

    // Correction des coordonnées d'un arrêt PDP existant (Forcer les coordonnées exactes)
    Route::put('/{id}/correct', [PdpController::class, 'correctPdp']);
});

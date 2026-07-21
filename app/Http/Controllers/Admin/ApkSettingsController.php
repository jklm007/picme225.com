<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Setting;
use Storage;

class ApkSettingsController extends Controller
{
    /**
     * Affiche le formulaire d'upload des APKs
     */
    public function index()
    {
        return view('admin.settings.apks');
    }

    /**
     * Traite l'upload des APKs et met à jour les paramètres
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_apk' => 'nullable|file|mimes:apk,zip|max:102400', // max 100MB
            'driver_apk' => 'nullable|file|mimes:apk,zip|max:102400',
        ]);

        if ($request->hasFile('user_apk')) {
            $userFile = $request->file('user_apk');
            // Sauvegarde dans storage/app/public
            $userFile->storeAs('public', 'user_apk_custom.apk');
            Setting::set('user_apk_path', 'user_apk_custom.apk');
        }

        if ($request->hasFile('driver_apk')) {
            $driverFile = $request->file('driver_apk');
            $driverFile->storeAs('public', 'driver_apk_custom.apk');
            Setting::set('driver_apk_path', 'driver_apk_custom.apk');
        }

        Setting::save();

        return back()->with('flash_success', 'Les APKs ont été mis à jour avec succès.');
    }
    
    /**
     * Réinitialise pour utiliser les fichiers par défaut (ceux générés par LANCER_PICKME)
     */
    public function resetDefault(Request $request)
    {
        $type = $request->input('type');
        
        if ($type === 'user') {
            Setting::set('user_apk_path', 'user_apk_default.apk');
        } elseif ($type === 'driver') {
            Setting::set('driver_apk_path', 'driver_apk_default.apk');
        }

        Setting::save();

        return back()->with('flash_success', 'Réinitialisation vers l\'APK par défaut effectuée.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Setting;

class SocialSettingsController extends Controller
{
    /**
     * Affiche le formulaire de configuration des réseaux sociaux
     */
    public function index()
    {
        return view('admin.settings.social');
    }

    /**
     * Traite l'enregistrement des tokens
     */
    public function store(Request $request)
    {
        $request->validate([
            'facebook_page_id' => 'nullable|string|max:255',
            'facebook_access_token' => 'nullable|string',
            'tiktok_client_key' => 'nullable|string|max:255',
            'tiktok_client_secret' => 'nullable|string|max:255',
            'tiktok_access_token' => 'nullable|string',
        ]);

        if ($request->has('facebook_page_id')) {
            Setting::set('facebook_page_id', $request->input('facebook_page_id'));
        }
        if ($request->has('facebook_access_token')) {
            Setting::set('facebook_access_token', $request->input('facebook_access_token'));
        }
        if ($request->has('tiktok_client_key')) {
            Setting::set('tiktok_client_key', $request->input('tiktok_client_key'));
        }
        if ($request->has('tiktok_client_secret')) {
            Setting::set('tiktok_client_secret', $request->input('tiktok_client_secret'));
        }
        if ($request->has('tiktok_access_token')) {
            Setting::set('tiktok_access_token', $request->input('tiktok_access_token'));
        }

        Setting::save();

        return back()->with('flash_success', 'Les identifiants API (Facebook et TikTok) ont été mis à jour avec succès.');
    }
}

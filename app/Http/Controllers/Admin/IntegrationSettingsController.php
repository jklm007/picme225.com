<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Setting;

class IntegrationSettingsController extends Controller
{
    public function index()
    {
        return view('admin.settings.integrations');
    }

    public function store(Request $request)
    {
        // R2 Settings
        if ($request->has('r2_access_key')) {
            Setting::set('r2_access_key', $request->input('r2_access_key'));
            Setting::set('r2_secret_key', $request->input('r2_secret_key'));
            Setting::set('r2_endpoint', $request->input('r2_endpoint'));
            Setting::set('r2_bucket', $request->input('r2_bucket'));
        }

        // WhatsApp Settings
        if ($request->has('evolution_api_url')) {
            Setting::set('evolution_api_url', $request->input('evolution_api_url'));
            Setting::set('evolution_api_key', $request->input('evolution_api_key'));
            Setting::set('evolution_instance', $request->input('evolution_instance'));
        }

        // Social Settings
        if ($request->has('facebook_page_id')) {
            Setting::set('facebook_page_id', $request->input('facebook_page_id'));
            Setting::set('facebook_access_token', $request->input('facebook_access_token'));
            Setting::set('tiktok_client_key', $request->input('tiktok_client_key'));
            Setting::set('tiktok_client_secret', $request->input('tiktok_client_secret'));
            Setting::set('tiktok_access_token', $request->input('tiktok_access_token'));
        }

        // Google Ads Settings
        if ($request->has('google_ads_client_id')) {
            Setting::set('google_ads_client_id', $request->input('google_ads_client_id'));
            Setting::set('google_ads_client_secret', $request->input('google_ads_client_secret'));
            Setting::set('google_ads_developer_token', $request->input('google_ads_developer_token'));
            Setting::set('google_ads_customer_id', $request->input('google_ads_customer_id'));
        }

        Setting::save();

        return redirect()->back()->with('success', 'Paramètres d\'intégration enregistrés avec succès. Les pods utiliseront ces nouvelles valeurs.');
    }
}

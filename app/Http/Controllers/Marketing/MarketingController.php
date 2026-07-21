<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * MarketingController — Isolated landing pages for Google Ads & SEO.
 * 
 * ⚠️ This controller is 100% independent from the core app.
 * It does NOT use any existing models, services, or business logic.
 */
class MarketingController extends Controller
{
    /**
     * WhatsApp number for all CTAs (international format, no +)
     */
    private $whatsapp = '2250759747444';

    /**
     * Airport Transfer Landing Page
     */
    public function airport(Request $request)
    {
        $data = $this->getPageData($request, [
            'title' => 'Transfert Aéroport Abidjan — Service Premium 24/7 | PicMe',
            'description' => 'Réservez votre transfert aéroport à Abidjan. Chauffeurs professionnels, véhicules climatisés, prix fixe. Disponible 24h/24, 7j/7.',
            'keywords' => 'transfert aéroport abidjan, taxi aéroport abidjan, navette aéroport abidjan, transport aéroport félix houphouët-boigny',
            'h1' => 'Transfert Aéroport Abidjan',
            'subtitle' => 'Service premium 24/7 — Prix fixe, sans surprise',
            'service_type' => 'airport',
            'whatsapp_message' => 'Bonjour PicMe! Je souhaite réserver un transfert aéroport à Abidjan.',
            'price_from' => '10 000',
            'features' => [
                ['icon' => 'fa-star', 'title' => 'Service Premium', 'desc' => 'Véhicules luxueux récents avec chauffeurs professionnels et service client bilingue'],
                ['icon' => 'fa-plane', 'title' => 'Suivi des vols', 'desc' => 'On surveille votre vol, zéro stress en cas de retard'],
                ['icon' => 'fa-clock-o', 'title' => 'Disponible 24/7', 'desc' => 'Même à 3h du matin, on est là'],
                ['icon' => 'fa-money', 'title' => 'Prix fixe garanti', 'desc' => 'Pas de compteur, pas de surprise'],
            ],
        ]);

        return view('marketing.airport', $data);
    }



    /**
     * Generate sitemap.xml
     */
    public function sitemap()
    {
        $urls = [
            ['loc' => url('/'),               'priority' => '1.0', 'changefreq' => 'daily'],
            ['loc' => url('/marketplace'),     'priority' => '0.9', 'changefreq' => 'daily'],
            ['loc' => url('/airport'),         'priority' => '0.9', 'changefreq' => 'daily'],
            ['loc' => url('/location'),        'priority' => '0.8', 'changefreq' => 'daily'],
            ['loc' => url('/ride'),            'priority' => '0.8', 'changefreq' => 'weekly'],
            ['loc' => url('/drive'),           'priority' => '0.8', 'changefreq' => 'weekly'],
            ['loc' => url('/blog'),            'priority' => '0.7', 'changefreq' => 'weekly'],
            ['loc' => url('/blog/prix-taxi-aeroport-abidjan'), 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => url('/blog/louer-voiture-chauffeur-abidjan-guide'), 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => url('/help'),            'priority' => '0.4', 'changefreq' => 'monthly'],
            ['loc' => url('/privacy'),         'priority' => '0.3', 'changefreq' => 'monthly'],
        ];

        return response()->view('marketing.sitemap', compact('urls'))
            ->header('Content-Type', 'application/xml');
    }

    /**
     * Build common page data with UTM tracking
     */
    private function getPageData(Request $request, array $page): array
    {
        $page['whatsapp'] = $this->whatsapp;
        $page['whatsapp_link'] = 'https://wa.me/' . $this->whatsapp . '?text=' . urlencode($page['whatsapp_message']);
        $page['phone_display'] = '+225 07 59 74 74 44';
        $page['phone_link'] = 'tel:+2250759747444';
        $page['city'] = $request->get('city', 'Abidjan');

        // UTM tracking
        $page['utm_source'] = $request->get('utm_source', 'direct');
        $page['utm_medium'] = $request->get('utm_medium', '');
        $page['utm_campaign'] = $request->get('utm_campaign', '');

        return $page;
    }
}

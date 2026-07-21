<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WhatsAppQrController extends Controller
{
    private string $evolutionApiUrl = 'http://evolution-api-service:8080';
    private string $evolutionApiKey = 'picme225-evolution-secret-key';
    private string $instanceName    = 'picme_whatsapp';

    /**
     * Return the QR code page (HTML) — for browser access.
     * The page itself auto-refreshes via AJAX every 15 seconds.
     */
    public function page()
    {
        return view('whatsapp.qr_page');
    }

    /**
     * Return the current QR code image as JSON { base64, status }.
     * Called every ~15 s by the frontend via AJAX.
     */
    public function qr()
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders(['apikey' => $this->evolutionApiKey])
                ->get("{$this->evolutionApiUrl}/instance/connect/{$this->instanceName}");

            $data = $response->json();

            // Instance already open (already connected)
            if (isset($data['instance']['state']) && $data['instance']['state'] === 'open') {
                return response()->json(['status' => 'connected']);
            }

            // Normal QR code response
            if (!empty($data['base64'])) {
                return response()->json([
                    'status' => 'qr',
                    'base64' => $data['base64'],
                ]);
            }

            // Pairing code / other state — try to fetch instance status
            $statusResponse = Http::timeout(10)
                ->withHeaders(['apikey' => $this->evolutionApiKey])
                ->get("{$this->evolutionApiUrl}/instance/fetchInstances");

            $instances = $statusResponse->json();
            $instance  = collect($instances)->firstWhere('name', $this->instanceName);

            if ($instance && ($instance['connectionStatus'] ?? '') === 'open') {
                return response()->json(['status' => 'connected']);
            }

            return response()->json([
                'status'  => 'error',
                'message' => 'QR code non disponible pour le moment. Réessayez dans quelques secondes.',
                'raw'     => $data,
            ], 503);

        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Impossible de joindre Evolution API : ' . $e->getMessage(),
            ], 503);
        }
    }
}

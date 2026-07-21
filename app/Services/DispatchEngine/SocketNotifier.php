<?php

namespace App\Services\DispatchEngine;

use Illuminate\Support\Facades\Log;

/**
 * SocketNotifier - Pont Laravel → Serveur Node.js Temps Réel
 *
 * Rôle : Quand le MatchingService sélectionne les meilleurs chauffeurs,
 * ce service contacte le serveur Node.js pour envoyer la notification
 * directement via WebSocket (sans passer par Firebase).
 *
 * Si le chauffeur est hors ligne (pas connecté au socket),
 * Node retourne son ID dans 'need_firebase_push' → Firebase prend le relai.
 *
 * URL du serveur Node : configurable via .env (SOCKET_SERVER_URL)
 */
class SocketNotifier
{
    /** @var string URL du serveur Node.js temps réel */
    protected $nodeUrl;

    /** @var int Timeout de la requête HTTP vers Node (ms) */
    const HTTP_TIMEOUT = 2; // 2 secondes max

    public function __construct()
    {
        // Configurable via .env : SOCKET_SERVER_URL=http://localhost:3000
        $this->nodeUrl = env('SOCKET_SERVER_URL', 'http://localhost:3000');
    }

    /**
     * Dispatch d'une course vers les chauffeurs sélectionnés via WebSocket.
     *
     * @param  array $providerIds  IDs des chauffeurs à notifier
     * @param  array $tripData     Données de la course (id, pickup, etc.)
     * @return array               ['socket_notified' => [...], 'firebase_needed' => [...]]
     */
    public function dispatchTrip(array $providerIds, array $tripData): array
    {
        $result = [
            'socket_notified' => [],
            'firebase_needed' => $providerIds, // Par défaut tout Firebase (fallback)
        ];

        try {
            $payload = json_encode([
                'provider_ids' => $providerIds,
                'request'      => $tripData,
            ]);

            // Appel HTTP POST vers Node.js /dispatch (timeout strict 2s)
            $ch = curl_init($this->nodeUrl . '/dispatch');
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $payload,
                CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => self::HTTP_TIMEOUT,
                CURLOPT_CONNECTTIMEOUT => 1,
            ]);

            $response   = curl_exec($ch);
            $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpStatus === 200 && $response) {
                $data = json_decode($response, true);
                if ($data) {
                    $result['socket_notified'] = $data['notified_via_socket'] ?? [];
                    $result['firebase_needed'] = $data['need_firebase_push']  ?? $providerIds;

                    Log::info('[SocketNotifier] Dispatch WebSocket réussi.', [
                        'socket' => count($result['socket_notified']),
                        'push'   => count($result['firebase_needed']),
                    ]);
                }
            } else {
                Log::warning('[SocketNotifier] Serveur Node injoignable (status=' . $httpStatus . '). Fallback Firebase complet.');
            }
        } catch (\Exception $e) {
            Log::error('[SocketNotifier] Erreur: ' . $e->getMessage() . '. Fallback Firebase.');
        }

        return $result;
    }

    /**
     * Vérifie si le serveur Node est opérationnel.
     *
     * @return bool
     */
    public function isNodeServerUp(): bool
    {
        try {
            $ch = curl_init($this->nodeUrl . '/health');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 1,
                CURLOPT_CONNECTTIMEOUT => 1,
            ]);
            $response = curl_exec($ch);
            $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            return $status === 200;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Récupère les chauffeurs actuellement connectés au serveur Node.
     * Utile pour l'admin ou le dashboard.
     *
     * @return array
     */
    public function getOnlineDrivers(): array
    {
        try {
            $ch = curl_init($this->nodeUrl . '/drivers');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 2,
            ]);
            $response = curl_exec($ch);
            curl_close($ch);
            $data = json_decode($response, true);
            return $data['drivers'] ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AiFailoverService
{
    private array $models;
    private int $cooldown;
    private string $apiKey;
    private string $endpoint;
    private int $timeout;

    public function __construct()
    {
        $this->models = config('services.groq.models', ['llama-3.3-70b-versatile', 'llama-3.1-8b-instant']);
        $this->cooldown = config('services.groq.cooldown', 300);
        $this->apiKey = config('services.groq.api_key', '');
        $this->endpoint = config('services.groq.endpoint', 'https://api.groq.com/openai/v1/chat/completions');
        $this->timeout = config('services.groq.timeout', 60);
    }

    /**
     * Exécute une requête API avec failover automatique.
     *
     * @param array $payload  Le payload à envoyer (sans la clé 'model')
     * @param string $jobContext  Nom du job ou contexte (pour les logs)
     * @return array Résultat JSON de l'API
     * @throws \Exception Si tous les modèles échouent
     */
    public function executeWithFailover(array $payload, string $jobContext = 'General'): array
    {
        if (empty($this->apiKey)) {
            throw new \Exception("Clé API Groq manquante.");
        }

        $testedModels = [];
        $lastError = null;

        // On parcourt la liste de modèles par ordre de priorité
        foreach ($this->models as $model) {
            $model = trim($model);
            if (empty($model)) continue;

            $cacheKey = "groq_cooldown_{$model}";

            // Si le modèle est en quarantaine temporaire, on le saute
            if (Cache::has($cacheKey)) {
                $testedModels[] = "{$model} ⚠️ (En quarantaine)";
                continue;
            }

            $payload['model'] = $model;
            $startTime = microtime(true);

            try {
                $response = Http::withToken($this->apiKey)
                    ->timeout($this->timeout)
                    ->post($this->endpoint, $payload);

                $duration = round((microtime(true) - $startTime) * 1000);

                if ($response->successful()) {
                    Log::info("[AI Failover] Succès avec le modèle {$model} en {$duration}ms", ['context' => $jobContext]);
                    return $response->json();
                }

                // Erreurs HTTP diverses (4xx, 5xx, rate limit, quota exceeded...)
                $errorMessage = "HTTP " . $response->status() . " - " . $response->body();
                throw new \Exception($errorMessage);

            } catch (\Exception $e) {
                $duration = round((microtime(true) - $startTime) * 1000);
                $lastError = $e->getMessage();
                $testedModels[] = "{$model} ❌ (Erreur en {$duration}ms)";

                Log::warning("[AI Failover] Échec du modèle {$model}", [
                    'context' => $jobContext,
                    'error' => $lastError,
                    'duration' => $duration
                ]);

                // Mise en quarantaine (cooldown) du modèle défaillant
                Cache::put($cacheKey, true, $this->cooldown);
            }
        }

        // Si on arrive ici, c'est que TOUS les modèles ont échoué ou étaient en cooldown.
        $lastError = $lastError ?? 'Tous les modèles sont actuellement en quarantaine (cooldown).';
        
        $this->sendAdminAlert($lastError, $jobContext, $testedModels);

        throw new \Exception("Tous les modèles IA sont indisponibles. Dernière erreur: {$lastError}");
    }

    /**
     * Envoie une alerte WhatsApp à l'administrateur.
     */
    private function sendAdminAlert(?string $lastError, string $jobContext, array $testedModels)
    {
        $alertCacheKey = "groq_admin_alert_cooldown";
        // Limiter l'alerte à 1 fois toutes les 15 minutes (900 secondes) pour éviter le spam
        if (Cache::has($alertCacheKey)) {
            return; // Déjà alerté récemment
        }
        Cache::put($alertCacheKey, true, 900);

        $adminPhone = env('PICME_ADMIN_WHATSAPP');
        if (empty($adminPhone)) {
            Log::warning("[AI Failover] Impossible d'envoyer l'alerte: Numéro PICME_ADMIN_WHATSAPP non configuré.");
            return;
        }

        $evoApiUrl = env('EVOLUTION_API_URL', 'http://evolution-api-service:8080');
        $evoApiKey = env('EVOLUTION_API_KEY', 'picme225-evolution-secret-key');
        $instanceName = env('EVOLUTION_INSTANCE', 'picme_whatsapp');

        $modelsList = !empty($testedModels) ? implode("\n- ", $testedModels) : 'Aucun modèle disponible.';
        $date = now()->format('Y-m-d H:i:s \U\T\C');

        $messageText = "🚨 *[PicMe225 - CRITIQUE]*\n\n";
        $messageText .= "Tous les modèles IA configurés sont indisponibles.\n";
        $messageText .= "Les nouvelles annonces WhatsApp ne peuvent plus être traitées.\n\n";
        $messageText .= "*Dernière erreur :*\n_{$lastError}_\n\n";
        $messageText .= "*Modèles testés :*\n- {$modelsList}\n\n";
        $messageText .= "*Date :*\n{$date}\n\n";
        $messageText .= "*Job :*\n{$jobContext}\n\n";
        $messageText .= "*Actions recommandées :*\n";
        $messageText .= "• Vérifier la disponibilité des modèles configurés.\n";
        $messageText .= "• Contrôler la configuration du payload envoyé.\n";
        $messageText .= "• Vérifier les clés API.\n";
        $messageText .= "• Vider le cache Laravel (`php artisan cache:clear`).";

        // Suppression des caractères non numériques du numéro admin
        $rawPhone = preg_replace('/[^0-9]/', '', $adminPhone);
        $sendToJid = $rawPhone . '@s.whatsapp.net';

        try {
            Http::withHeaders(['apikey' => $evoApiKey])
                ->timeout(10)
                ->post("{$evoApiUrl}/message/sendText/{$instanceName}", [
                    'number'  => $sendToJid,
                    'text'    => $messageText,
                ]);
            Log::info("[AI Failover] Alerte administrateur envoyée avec succès au numéro {$adminPhone}.");
        } catch (\Exception $e) {
            Log::error("[AI Failover] Échec de l'envoi de l'alerte WhatsApp à l'admin: " . $e->getMessage());
        }
    }
}

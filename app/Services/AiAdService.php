<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Service pour la génération de contenu publicitaire avec IA
 */
class AiAdService
{
    private $apiKey;
    private $apiUrl;

    public function __construct()
    {
        // Configuration depuis .env
        $this->apiKey = config('ai.openai_api_key');
        $this->apiUrl = config('ai.openai_api_url', 'https://api.openai.com/v1');
    }

    /**
     * Générer du contenu publicitaire avec IA
     */
    public function generateAdContent($campaignType, $businessType, $targetAudience, $budget = null)
    {
        try {
            $prompt = $this->buildPrompt($campaignType, $businessType, $targetAudience, $budget);
            
            // Appel à l'API OpenAI (ou autre service IA)
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '/chat/completions', [
                'model' => 'gpt-4',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Tu es un expert en marketing digital et création publicitaire. Génère du contenu publicitaire efficace et engageant.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.7,
                'max_tokens' => 1000,
            ]);

            if ($response->successful()) {
                $content = $response->json();
                return $this->parseAiResponse($content);
            }

            throw new Exception('Erreur lors de la génération IA: ' . $response->body());
        } catch (Exception $e) {
            Log::error('Error generating AI ad content: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Optimiser le contenu pour une plateforme spécifique
     */
    public function optimizeForPlatform($content, $platform)
    {
        $optimizations = [
            'GOOGLE_ADS' => [
                'max_headline_length' => 30,
                'max_description_length' => 90,
                'required_fields' => ['headline', 'description', 'call_to_action'],
            ],
            'FACEBOOK_ADS' => [
                'max_headline_length' => 40,
                'max_description_length' => 125,
                'image_ratio' => '1:1',
                'required_fields' => ['headline', 'description', 'image_url'],
            ],
            'TIKTOK_ADS' => [
                'max_headline_length' => 80,
                'video_required' => true,
                'max_video_duration' => 60,
                'required_fields' => ['headline', 'video_url'],
            ],
        ];

        $rules = $optimizations[$platform] ?? [];

        // Appliquer les optimisations
        $optimized = $content;
        
        if (isset($rules['max_headline_length']) && isset($optimized['headline'])) {
            $optimized['headline'] = substr($optimized['headline'], 0, $rules['max_headline_length']);
        }

        if (isset($rules['max_description_length']) && isset($optimized['description'])) {
            $optimized['description'] = substr($optimized['description'], 0, $rules['max_description_length']);
        }

        return $optimized;
    }

    /**
     * Suggérer des mots-clés pertinents
     */
    public function suggestKeywords($businessType, $campaignType, $targetAudience)
    {
        try {
            $prompt = "Génère 20 mots-clés pertinents pour une campagne publicitaire de type {$campaignType} pour une entreprise de type {$businessType} ciblant: " . json_encode($targetAudience);
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '/chat/completions', [
                'model' => 'gpt-4',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.7,
            ]);

            if ($response->successful()) {
                $content = $response->json();
                $keywords = $this->extractKeywords($content);
                return $keywords;
            }

            return [];
        } catch (Exception $e) {
            Log::error('Error suggesting keywords: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Construire le prompt pour l'IA
     */
    private function buildPrompt($campaignType, $businessType, $targetAudience, $budget)
    {
        $audienceInfo = '';
        if ($targetAudience) {
            $audienceInfo = "Cible: " . json_encode($targetAudience);
        }

        return "Crée une publicité efficace pour:
        - Type de campagne: {$campaignType}
        - Type d'entreprise: {$businessType}
        {$audienceInfo}
        - Budget: " . ($budget ? number_format($budget, 0, ',', ' ') . " FCFA" : "Non spécifié") . "
        
        Génère:
        1. Un titre accrocheur (headline)
        2. Une description persuasive
        3. Un appel à l'action (CTA)
        4. Des suggestions de mots-clés
        5. Des recommandations pour les images/vidéos
        
        Format de réponse en JSON.";
    }

    /**
     * Parser la réponse de l'IA
     */
    private function parseAiResponse($response)
    {
        $content = $response['choices'][0]['message']['content'] ?? '';
        
        // Essayer de parser le JSON
        $json = json_decode($content, true);
        if ($json) {
            return $json;
        }

        // Si ce n'est pas du JSON, extraire les informations
        return [
            'headline' => $this->extractField($content, 'headline', 'titre'),
            'description' => $this->extractField($content, 'description'),
            'call_to_action' => $this->extractField($content, 'call_to_action', 'CTA'),
            'keywords' => $this->extractKeywords($content),
            'raw_content' => $content,
        ];
    }

    /**
     * Extraire un champ du contenu
     */
    private function extractField($content, $field, $alternative = null)
    {
        $patterns = [
            "/{$field}[:\\s]+(.+?)(?=\\n|$)/i",
            "/{$alternative}[:\\s]+(.+?)(?=\\n|$)/i",
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                return trim($matches[1]);
            }
        }

        return null;
    }

    /**
     * Extraire les mots-clés
     */
    private function extractKeywords($content)
    {
        if (is_array($content)) {
            $content = $content['choices'][0]['message']['content'] ?? '';
        }

        // Chercher une liste de mots-clés
        if (preg_match('/mots-clés?[:\\s]+(.+?)(?=\\n|$)/i', $content, $matches)) {
            $keywords = explode(',', $matches[1]);
            return array_map('trim', $keywords);
        }

        return [];
    }
}


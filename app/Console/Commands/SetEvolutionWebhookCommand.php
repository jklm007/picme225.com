<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SetEvolutionWebhookCommand extends Command
{
    protected $signature = 'evolution:set-webhook';
    protected $description = 'Set the webhook for the Evolution API instance';

    public function handle()
    {
        $evolutionApiUrl = config('services.evolution.url', 'http://evolution-api-service:8080');
        $evolutionApiKey = config('services.evolution.key', 'picme225-evolution-secret-key');
        $instanceName = config('services.evolution.instance', 'picme_whatsapp');
        
        $webhookUrl = 'http://laravel-service/api/user/whatsapp/webhook';

        $this->info("Setting webhook for instance {$instanceName} to {$webhookUrl}");

        try {
            $response = Http::withHeaders(['apikey' => $evolutionApiKey])
                ->post("{$evolutionApiUrl}/webhook/set/{$instanceName}", [
                    'webhook' => [
                        'enabled' => true,
                        'url' => $webhookUrl,
                        'byEvents' => false, // Set to true if configuring specific events
                        'base64' => false, // Set to true if you want media base64
                        'events' => [
                            'APPLICATION_STARTUP',
                            'QRCODE_UPDATED',
                            'MESSAGES_UPSERT',
                            'MESSAGES_UPDATE',
                            'MESSAGES_DELETE',
                            'SEND_MESSAGE',
                            'CONNECTION_UPDATE',
                        ]
                    ]
                ]);

            if ($response->successful()) {
                $this->info('Webhook set successfully: ' . $response->body());
                Log::info('Evolution API Webhook set: ' . $response->body());
            } else {
                $this->error('Failed to set webhook: ' . $response->body());
                Log::error('Evolution API Webhook error: ' . $response->body());
            }
        } catch (\Exception $e) {
            $this->error('Exception: ' . $e->getMessage());
        }
    }
}

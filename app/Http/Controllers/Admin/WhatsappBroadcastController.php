<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\WhatsappGroup;
use App\Models\User;
use App\Models\Provider;

class WhatsappBroadcastController extends Controller
{
    public function index()
    {
        return view('admin.whatsapp_broadcast.create');
    }

    public function generateWithAi(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string',
        ]);

        $apiKey = env('GROQ_API_KEY');
        $model = env('GROQ_MODEL', 'llama-3.1-8b-instant');
        $prompt = $request->input('prompt');
        $systemPrompt = "You are a professional marketing and sales assistant for PicMe225. Your job is to draft highly converting, professional, yet engaging WhatsApp broadcast messages. Use emojis appropriately. The tone should be Marketing/Sale focused. Reply in French.";

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.7,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $generatedMessage = $data['choices'][0]['message']['content'] ?? '';
                return response()->json(['success' => true, 'message' => trim($generatedMessage)]);
            } else {
                return response()->json(['success' => false, 'error' => $response->body()]);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function sendBroadcast(Request $request)
    {
        $request->validate([
            'target' => 'required|string', // 'GROUPS', 'USERS', 'PROVIDERS', 'ALL', 'ALL_WITH_GROUPS'
            'message' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $target = $request->input('target');
        $message = $request->input('message');
        
        $base64Image = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->getRealPath();
            $imageData = file_get_contents($path);
            $mime = $request->file('image')->getMimeType();
            $base64Image = base64_encode($imageData);
        }

        $evoApiUrl = config('services.evolution.url', 'http://evolution-api-service:8080');
        $evoApiKey = config('services.evolution.key');
        $instanceName = config('services.evolution.instance', 'picme_whatsapp');

        $recipients = [];

        if ($target === 'GROUPS' || $target === 'ALL_WITH_GROUPS') {
            $groups = WhatsappGroup::all();
            foreach ($groups as $group) {
                if ($group->group_id) {
                    $recipients[] = $group->group_id;
                }
            }
        }
        if ($target === 'USERS' || $target === 'ALL' || $target === 'ALL_WITH_GROUPS') {
            $users = User::whereNotNull('mobile')->get();
            foreach ($users as $user) {
                $number = preg_replace('/[^0-9]/', '', $user->mobile);
                if($number) $recipients[] = $number;
            }
        }
        if ($target === 'PROVIDERS' || $target === 'ALL' || $target === 'ALL_WITH_GROUPS') {
            $providers = Provider::whereNotNull('mobile')->get();
            foreach ($providers as $provider) {
                $number = preg_replace('/[^0-9]/', '', $provider->mobile);
                if($number) $recipients[] = $number;
            }
        }

        $recipients = array_unique($recipients);
        $successCount = 0;

        foreach ($recipients as $number) {
            $payload = [
                "number" => $number,
                "options" => [
                    "delay" => 1200,
                    "presence" => "composing"
                ]
            ];

            if ($base64Image) {
                $payload["mediatype"] = "image";
                $payload["mimetype"] = $mime ?? "image/jpeg";
                $payload["caption"] = $message;
                $payload["media"] = $base64Image;
                $endpoint = "/message/sendMedia/{$instanceName}";
            } else {
                $payload["textMessage"] = ["text" => $message];
                $endpoint = "/message/sendText/{$instanceName}";
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "{$evoApiUrl}{$endpoint}");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "apikey: {$evoApiKey}",
                "Content-Type: application/json"
            ]);

            $res = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode >= 200 && $httpCode < 300) {
                $successCount++;
            }
        }

        return back()->with('flash_success', "Broadcast envoyé à {$successCount} contacts/groupes avec succès !");
    }
}

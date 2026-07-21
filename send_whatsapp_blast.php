<?php
// send_whatsapp_blast.php
// This script will send a broadcast message to all WhatsApp groups in the Evolution API instance.

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$evoApiUrl = config('services.evolution.url', 'http://evolution-api-service:8080');
$evoApiKey = config('services.evolution.key');
$instanceName = config('services.evolution.instance', 'picme_whatsapp');

// The message
$message = "ðŸš€ *Excellente nouvelle pour notre communautÃ© d'affaires !* ðŸ‡¨ðŸ‡®\n\n"
         . "Afin de vous offrir une meilleure visibilitÃ©, nous vous informons que *toutes vos annonces* seront dÃ©sormais disponibles sur notre nouveau serveur : *picme225.site* (actuellement en mode BÃªta). ðŸŽ‰\n\n"
         . "Cette nouvelle plateforme a Ã©tÃ© conÃ§ue pour centraliser vos offres et faciliter vos Ã©changes professionnels en un seul endroit.\n\n"
         . "*N'attendez plus, rejoignez la plateforme dÃ¨s maintenant :*\n"
         . "ðŸ“² *TÃ©lÃ©chargez l'application ici :* https://picme225.site/download\n"
         . "âœï¸ *CrÃ©ez votre compte (Inscription) :* https://picme225.site/register\n"
         . "ðŸŒ *Visitez le site BÃªta :* www.picme225.site\n\n"
         . "Merci de votre confiance et de continuer Ã  dÃ©velopper vos affaires avec *PicMe225* ! ðŸŒŸ";

// Load image and convert to base64
$imagePath = __DIR__ . '/picme225_promo.png';
if (!file_exists($imagePath)) {
    die("Error: Image not found at $imagePath\n");
}
$imageData = file_get_contents($imagePath);
$base64Image = base64_encode($imageData);

// Fetch groups from DB
$groups = \App\Models\WhatsappGroup::all();
echo "Found " . $groups->count() . " groups in DB.\n";

$successCount = 0;
foreach ($groups as $group) {
    $groupId = $group->group_id;
    echo "Sending to group: " . ($group->name ?? $groupId) . "...\n";

    $payload = [
        "number" => $groupId,
        "options" => [
            "delay" => 1200,
            "presence" => "composing"
        ],
        "mediatype" => "image", "mimetype" => "image/png", "media" => $base64Image, "caption" => $message, "mediaMessage" => [
            "mediatype" => "image", "mimetype" => "image/png",
            "caption" => $message,
            "media" => $base64Image
        ]
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "{$evoApiUrl}/message/sendMedia/{$instanceName}");
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
        echo " -> Success\n";
        $successCount++;
    } else {
        echo " -> Failed: $res\n";
    }

    // Sleep to avoid rate limiting
    sleep(3);
}

echo "\nFinished! Successfully sent to $successCount groups.\n";
?>



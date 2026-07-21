<?php
$apiKey = "picme225-evolution-secret-key";
$instanceName = "picme_whatsapp";
$url = "http://evolution-api-service:8080/instance/connect/" . $instanceName;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "apikey: " . $apiKey
]);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
$base64Image = "";

if (isset($data['base64'])) {
    $base64Image = $data['base64']; // Already includes 'data:image/png;base64,' prefix
} elseif (isset($data['instance']['state']) && $data['instance']['state'] === 'open') {
    echo "Instance is already connected.\n";
    exit;
} else {
    echo "Failed to get QR code. Response: " . $response . "\n";
    exit;
}

$html = '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>PicMe225 — Connexion WhatsApp</title>
    <style>
        body { font-family: sans-serif; text-align: center; padding: 50px; background-color: #f3f4f6; }
        .card { background: white; padding: 30px; border-radius: 12px; display: inline-block; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        img { width: 300px; height: 300px; margin-top: 20px; border: 1px solid #ddd; border-radius: 8px; }
        h1 { color: #10b981; }
        .refresh { margin-top: 20px; padding: 10px 20px; background-color: #10b981; color: white; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Associer PicMe225 à WhatsApp</h1>
        <p>Scannez ce QR Code avec l\'application WhatsApp de votre téléphone dédié à PicMe225.</p>
        <img src="' . $base64Image . '" alt="QR Code WhatsApp">
        <p style="margin-top: 20px; color: #6b7280; font-size: 0.9em;">Rechargez cette page si le QR Code expire.</p>
        <a href="whatsapp_qr.html" class="refresh">Rafraîchir</a>
    </div>
</body>
</html>';

file_put_contents('/app/public/whatsapp_qr.html', $html);
echo "QR code page generated successfully at /app/public/whatsapp_qr.html\n";
?>

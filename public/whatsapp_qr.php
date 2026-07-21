<?php
$url = 'http://evolution-api-service:8080/instance/connect/picme_whatsapp';
$apiKey = 'picme225-evolution-secret-key';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'apikey: ' . $apiKey
]);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

$base64 = null;
if (isset($data['base64'])) {
    $base64 = $data['base64'];
} elseif (isset($data['qrcode']['base64'])) {
    $base64 = $data['qrcode']['base64'];
}

$state = null;
if (isset($data['instance']['state'])) {
    $state = $data['instance']['state'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>WhatsApp QR Code</title>
    <meta charset="utf-8">
    <?php if (!$state || $state !== 'open'): ?>
    <meta http-equiv="refresh" content="15">
    <?php endif; ?>
</head>
<body style="text-align:center; padding: 50px; font-family: sans-serif;">
    <h1>Scanner le QR Code WhatsApp</h1>
    <?php if ($base64): ?>
        <img src="<?php echo htmlspecialchars($base64); ?>" alt="QR Code" style="width: 300px; height: 300px; border: 1px solid #ccc; padding: 10px;">
        <p>Veuillez rafraîchir la page si le code expire.</p>
    <?php elseif ($state === 'open'): ?>
        <p style="color: green; font-size: 24px;">L'instance est déjà connectée (open).</p>
    <?php else: ?>
        <p style="color: red;">Erreur ou état inattendu:</p>
        <pre style="text-align:left; background: #eee; padding: 20px; display: inline-block;"><?php echo htmlspecialchars(print_r($data, true)); ?></pre>
    <?php endif; ?>
</body>
</html>

<?php
$time = date('Y-m-d H:i:s');
$ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
file_put_contents(__DIR__ . '/ping_log.txt', "[$time] Connexion russie depuis : $ip\n", FILE_APPEND);
echo "<h1>TEST RESEAU REUSSI !</h1>";
echo "<p>Votre tlphone communique parfaitement avec le serveur.</p>";
?>

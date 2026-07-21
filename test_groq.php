<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=================================================\n";
echo "    DIAGNOSTIC COMPLET PICME CHAT SYSTEM\n";
echo "=================================================\n\n";

// ─── TEST 1 : IA Groq / Support Chat ──────────────────────────────────────────
echo "[ TEST 1 ] Support Chat IA (Chauffeur)\n";
$ai = new \App\Services\PicmeAiService();
echo "  AI Enabled : " . ($ai->isEnabled() ? '✅ YES' : '❌ NO') . "\n";
echo "  AI Mode    : " . $ai->getMode() . "\n";

$reply = $ai->generateSupportReply("Problème d'identification", "Chauffeur Test");
if (str_contains($reply, 'entraînement')) {
    echo "  Reply Test : ❌ TOUJOURS EN MODE SIMULATION\n";
    echo "  Contenu    : " . $reply . "\n";
} else {
    echo "  Reply Test : ✅ IA RÉELLE ACTIVE\n";
    echo "  Contenu    : " . substr($reply, 0, 100) . "...\n";
}

echo "\n";

// ─── TEST 2 : WebSockets (Port 6001) ─────────────────────────────────────────
echo "[ TEST 2 ] WebSockets Natifs (Port 6001)\n";
$wsActive = false;
try {
    $sock = @fsockopen("127.0.0.1", 6001, $errno, $errstr, 2);
    if ($sock) {
        $wsActive = true;
        fclose($sock);
    }
} catch (Exception $e) {}
echo "  Port 6001  : " . ($wsActive ? '✅ OUVERT (WebSockets actifs)' : '❌ FERMÉ (WebSockets inactifs)') . "\n";

// Test broadcast config
echo "  Broadcast  : " . config('broadcasting.default', 'non défini') . "\n";
echo "  Pusher Host: " . config('broadcasting.connections.pusher.options.host', 'non défini') . "\n";
echo "  Pusher Port: " . config('broadcasting.connections.pusher.options.port', 'non défini') . "\n";

echo "\n";

// ─── TEST 3 : Marketplace Chat (SecureMessage / Queue) ───────────────────────
echo "[ TEST 3 ] Marketplace Chat (SQL + Queue)\n";
try {
    $msgCount = \App\Models\SecureMessage::count();
    echo "  Messages SQL  : ✅ Table accessible ($msgCount messages)\n";
} catch (Exception $e) {
    echo "  Messages SQL  : ❌ ERREUR: " . $e->getMessage() . "\n";
}

// Check queue
try {
    $queueSize = \Illuminate\Support\Facades\Redis::llen('queues:default');
    echo "  Queue Redis   : ✅ $queueSize jobs en attente\n";
} catch (Exception $e) {
    echo "  Queue Redis   : ⚠️  " . $e->getMessage() . "\n";
}

// Check Jobs table (if using database driver)
echo "  Queue Driver  : " . config('queue.default', 'non défini') . "\n";

echo "\n";

// ─── TEST 4 : Marketplace Jobs / ModerateChatMessageJob ──────────────────────
echo "[ TEST 4 ] ModerateChatMessageJob\n";
$jobExists = class_exists(\App\Jobs\ModerateChatMessageJob::class);
echo "  Job Class  : " . ($jobExists ? '✅ Existe' : '❌ Introuvable') . "\n";

echo "\n";

// ─── TEST 5 : NewSupportMessage Event ────────────────────────────────────────
echo "[ TEST 5 ] Events WebSockets\n";
$eventSupport = class_exists(\App\Events\NewSupportMessage::class);
$eventMarket  = class_exists(\App\Events\NewSecureMessage::class);
echo "  NewSupportMessage : " . ($eventSupport ? '✅ Existe' : '❌ Introuvable') . "\n";
echo "  NewSecureMessage  : " . ($eventMarket  ? '✅ Existe' : '❌ Introuvable') . "\n";

echo "\n=================================================\n";
echo "    FIN DU DIAGNOSTIC\n";
echo "=================================================\n";

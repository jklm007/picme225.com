<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\ProviderDevice;
use App\Jobs\SendFirebasePushJob;

if ($argc < 4) {
    echo "Usage: php send_test_push.php [user|provider] [id] [chat|call]\n";
    echo "Examples:\n";
    echo "  php send_test_push.php user 1 chat\n";
    echo "  php send_test_push.php user 1 call\n";
    echo "  php send_test_push.php provider 1 chat\n";
    echo "  php send_test_push.php provider 1 call\n";
    exit(1);
}

$targetType = $argv[1]; // 'user' or 'provider'
$targetId = intval($argv[2]);
$testType = $argv[3]; // 'chat' or 'call'

$token = '';
$name = '';

if ($targetType === 'user') {
    $user = User::find($targetId);
    if (!$user) {
        echo "ERROR: User with ID {$targetId} not found.\n";
        exit(1);
    }
    $token = $user->device_token;
    $name = $user->first_name . " " . $user->last_name . " (User)";
} elseif ($targetType === 'provider') {
    $pd = ProviderDevice::where('provider_id', $targetId)->with('provider')->first();
    if (!$pd) {
        echo "ERROR: Provider Device for Provider ID {$targetId} not found.\n";
        exit(1);
    }
    $token = $pd->token;
    $pName = $pd->provider ? ($pd->provider->first_name . " " . $pd->provider->last_name) : "Unknown";
    $name = $pName . " (Provider)";
} else {
    echo "ERROR: Target type must be 'user' or 'provider'.\n";
    exit(1);
}

if (empty($token)) {
    echo "ERROR: Target {$name} does not have a device token.\n";
    exit(1);
}

echo "Target: {$name}\n";
echo "Device Token: " . substr($token, 0, 40) . "...\n";
echo "Test Type: {$testType}\n";

if ($testType === 'chat') {
    $data = [
        'type' => 'NEW_CHAT_MESSAGE',
        'message' => 'Message de test: Cloche et pastille rouge vérifiées ! 🔔',
        'recipient_id' => (string) $targetId,
        'title' => 'Nouveau message PicMe',
        'sound' => 'default'
    ];
    $title = "Nouveau message PicMe";
    $body = "Message de test: Cloche et pastille rouge vérifiées ! 🔔";
} elseif ($testType === 'call') {
    $data = [
        'type' => 'WEBRTC_CALL',
        'call_type' => 'video',
        'roomId' => 'test_room_' . time(),
        'callerId' => '999',
        'callerName' => 'Testeur WebRTC',
        'title' => 'Appel entrant de Testeur WebRTC',
        'message' => 'Appel vidéo en cours...',
        'sound' => 'default'
    ];
    $title = "Appel entrant";
    $body = "Appel vidéo de Testeur WebRTC";
} else {
    echo "ERROR: Invalid test type. Choose 'chat' or 'call'.\n";
    exit(1);
}

echo "Dispatching SendFirebasePushJob...\n";
try {
    SendFirebasePushJob::dispatch(
        $token,
        $data,
        $title,
        $body
    );
    echo "SUCCESS: Notification sent successfully.\n";
} catch (\Exception $e) {
    echo "ERROR: Failed to send notification: " . $e->getMessage() . "\n";
}

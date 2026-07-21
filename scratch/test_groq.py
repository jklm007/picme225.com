import paramiko
import sys
import io

sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel-worker -o jsonpath='{.items[0].metadata.name}'")
worker_pod = stdout.read().decode().strip().strip("'")

# Fetch recent logs around HTTP 400
# The log line is "[Groq Fallback] Modèle meta-llama/llama-4-scout-17b-16e-instruct a retourné HTTP 400"
# Wait, I logged the body in $lastError! Let me check if there's any log with the actual body.
# Actually, the python script earlier showed that I did NOT log the body, wait, in ProcessWhatsappBatchJob:
# Log::warning("[Groq Fallback] Modèle {$model} a retourné HTTP " . $response->status());
# $lastError = $response->body();
# But I didn't log the body! The body is in $lastError but it's only printed at the END if ALL models fail!
# But the last error printed was the decommissioned one!
# So we lost the body of the 400 error!

# Let's write a quick test script to call Groq directly with the Scout model to see the error!
test_script = """<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\\Contracts\\Console\\Kernel::class);
$kernel->bootstrap();

$apiKey = config('services.groq.api_key');
$endpoint = config('services.groq.endpoint', 'https://api.groq.com/openai/v1/chat/completions');

// Small 1x1 pixel base64 image
$b64 = "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wgALCAABAAEBAREA/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxA=";

$contentArray = [
    ['type' => 'text', 'text' => 'test'],
    ['type' => 'image_url', 'image_url' => ['url' => $b64]]
];

$response = \Illuminate\Support\Facades\Http::withToken($apiKey)
    ->post($endpoint, [
        'model' => 'meta-llama/llama-4-scout-17b-16e-instruct',
        'messages' => [['role' => 'user', 'content' => $contentArray]],
        'temperature' => 0.1,
    ]);

echo "Status: " . $response->status() . "\\n";
echo "Body: " . $response->body() . "\\n";

// Also test a text-only model with images to see if it 400s
$response2 = \Illuminate\Support\Facades\Http::withToken($apiKey)
    ->post($endpoint, [
        'model' => 'llama-3.3-70b-versatile',
        'messages' => [['role' => 'user', 'content' => $contentArray]],
        'temperature' => 0.1,
    ]);
echo "Text Model Status: " . $response2->status() . "\\n";
echo "Text Model Body: " . $response2->body() . "\\n";
"""

sftp = client.open_sftp()
with sftp.file("/tmp/test_groq.php", "w") as f:
    f.write(test_script)
sftp.close()

client.exec_command(f"kubectl cp /tmp/test_groq.php {worker_pod}:/tmp/test_groq.php")
stdin, stdout, stderr = client.exec_command(f"kubectl exec {worker_pod} -- php /tmp/test_groq.php")
print(stdout.read().decode('utf-8'))
client.close()

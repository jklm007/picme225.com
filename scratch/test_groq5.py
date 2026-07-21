import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

test_script = """<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\\Contracts\\Console\\Kernel::class);
$kernel->bootstrap();

$apiKey = config('services.groq.api_key');
$endpoint = config('services.groq.endpoint', 'https://api.groq.com/openai/v1/chat/completions');

// Download a real 200x200 image
$imgData = file_get_contents('https://via.placeholder.com/200.jpg');
$b64 = 'data:image/jpeg;base64,' . base64_encode($imgData);

$contentArray = [
    ['type' => 'text', 'text' => 'What is this image?'],
    ['type' => 'image_url', 'image_url' => ['url' => $b64]]
];

$response = \Illuminate\Support\Facades\Http::withToken($apiKey)
    ->post($endpoint, [
        'model' => 'meta-llama/llama-4-scout-17b-16e-instruct',
        'messages' => [['role' => 'user', 'content' => $contentArray]],
        'temperature' => 0.1,
    ]);

echo "Status Scout B64: " . $response->status() . "\\n";
echo "Body Scout B64: " . $response->body() . "\\n\\n";

// Test passing URL directly instead of base64
$contentArrayUrl = [
    ['type' => 'text', 'text' => 'What is this image?'],
    ['type' => 'image_url', 'image_url' => ['url' => 'https://via.placeholder.com/200.jpg']]
];
$response2 = \Illuminate\Support\Facades\Http::withToken($apiKey)
    ->post($endpoint, [
        'model' => 'meta-llama/llama-4-scout-17b-16e-instruct',
        'messages' => [['role' => 'user', 'content' => $contentArrayUrl]],
        'temperature' => 0.1,
    ]);
echo "Status Scout URL: " . $response2->status() . "\\n";
echo "Body Scout URL: " . $response2->body() . "\\n\\n";

// Test qwen vision model
$response3 = \Illuminate\Support\Facades\Http::withToken($apiKey)
    ->post($endpoint, [
        'model' => 'qwen/qwen3.6-27b',
        'messages' => [['role' => 'user', 'content' => $contentArray]],
        'temperature' => 0.1,
    ]);
echo "Status Qwen B64: " . $response3->status() . "\\n";
echo "Body Qwen B64: " . $response3->body() . "\\n";
"""

sftp = client.open_sftp()
with sftp.file("/tmp/test_groq.php", "w") as f:
    f.write(test_script)
sftp.close()

stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel-worker -o jsonpath='{.items[0].metadata.name}'")
worker_pod = stdout.read().decode().strip().strip("'")

client.exec_command(f"kubectl cp /tmp/test_groq.php {worker_pod}:/tmp/test_groq.php")
stdin, stdout, stderr = client.exec_command(f"kubectl exec {worker_pod} -- php /tmp/test_groq.php")
print(stdout.read().decode('utf-8'))
client.close()

import paramiko

client=paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

pod = "laravel-deployment-b5fb86954-cgldw"
php_code = """<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo view('user.dashboard', [
        'user' => null,
        'recentTrips' => collect(),
        'totalTrips' => 0,
        'upcomingTrips' => collect(),
        'categories' => \App\Models\Service::with('serviceTypes')->get(),
        'package' => \App\Models\KmHour::all()
    ])->render();
    echo "\n\nSUCCESS\n";
} catch (\Throwable $e) {
    echo "EXCEPTION: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . "\n";
}
"""

with open('scratch/test_render2.php', 'w') as f:
    f.write(php_code)

sftp = client.open_sftp()
sftp.put('scratch/test_render2.php', '/tmp/test_render2.php')
sftp.close()

client.exec_command(f'kubectl cp /tmp/test_render2.php default/{pod}:/app/public/test_render2.php')
_, out, _ = client.exec_command(f'kubectl exec {pod} -- php public/test_render2.php')
print(out.read().decode('utf-8', errors='replace'))
client.exec_command(f'kubectl exec {pod} -- rm public/test_render2.php')


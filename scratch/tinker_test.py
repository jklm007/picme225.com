import paramiko

client=paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

pod = "laravel-deployment-b5fb86954-cgldw"
php_code = """
try {
    echo view('user.dashboard', [
        'user' => null,
        'recentTrips' => collect(),
        'totalTrips' => 0,
        'upcomingTrips' => collect(),
        'categories' => \App\Models\Service::with('serviceTypes')->get(),
        'package' => \App\Models\KmHour::all()
    ])->render();
} catch (\Throwable $e) {
    echo 'EXCEPTION: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine();
}
"""

cmd = f'kubectl exec -i {pod} -- php artisan tinker'
stdin, stdout, stderr = client.exec_command(cmd)
stdin.write(php_code + "\nexit\n")
stdin.flush()
print(stdout.read().decode('utf-8', errors='replace'))
print(stderr.read().decode('utf-8', errors='replace'))


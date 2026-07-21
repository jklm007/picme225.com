import paramiko, time

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

pod = "laravel-deployment-56f54497f-r8pmg"

diag_php = '''<?php
require '/app/vendor/autoload.php';
$app = require '/app/bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

// 1. Fetch all ad campaigns with their contents
$campaigns = \\App\\Models\\AdCampaign::with('contents')->get();
echo "=== AD CAMPAIGNS ===\\n";
foreach ($campaigns as $c) {
    echo "Campaign #{$c->id}: {$c->name}\\n";
    foreach ($c->contents as $content) {
        echo "  Content #{$content->id}: type={$content->content_type}\\n";
        echo "  image_url=" . ($content->image_url ?? 'NULL') . "\\n";
        echo "  video_url=" . ($content->video_url ?? 'NULL') . "\\n";
    }
    if ($c->contents->isEmpty()) echo "  (no contents)\\n";
}

// 2. Check disk config
echo "\\n=== DISK CONFIG ===\\n";
echo "FILESYSTEM_DISK=" . env('FILESYSTEM_DISK', 'local') . "\\n";
echo "AWS_URL=" . env('AWS_URL', 'N/A') . "\\n";
echo "AWS_BUCKET=" . env('AWS_BUCKET', 'N/A') . "\\n";

// 3. Test S3 upload + URL generation
echo "\\n=== S3 TEST ===\\n";
try {
    $disk = \'s3\';
    $testFile = \'ads/test_\' . time() . \'.txt\';
    \\Illuminate\\Support\\Facades\\Storage::disk($disk)->put($testFile, \'test\', \'public\');
    $url = \\Illuminate\\Support\\Facades\\Storage::disk($disk)->url($testFile);
    echo "Generated URL: " . $url . "\\n";
    // Clean up
    \\Illuminate\\Support\\Facades\\Storage::disk($disk)->delete($testFile);
    echo "S3 OK\\n";
} catch (Exception $e) {
    echo "S3 ERROR: " . $e->getMessage() . "\\n";
}
'''

with open('scratch/diag_ads.php', 'w', encoding='utf-8') as f:
    f.write(diag_php)

sftp = client.open_sftp()
sftp.put('scratch/diag_ads.php', '/tmp/diag_ads.php')
sftp.close()

client.exec_command("kubectl cp /tmp/diag_ads.php " + pod + ":/tmp/diag_ads.php")
time.sleep(1)
stdin, stdout, stderr = client.exec_command("kubectl exec " + pod + " -- php /tmp/diag_ads.php 2>&1")
output = stdout.read().decode('utf-8', errors='replace')
print(output)

client.close()

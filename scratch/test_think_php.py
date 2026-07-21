import paramiko, time

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

pod = "laravel-deployment-56f54497f-r8pmg"

test_php = r"""<?php
$text = '<think>The user wants me to extract data...</think>{"annonces":[{"is_commercial":true,"title":"Test"}]}';
echo "ORIGINAL LEN: " . strlen($text) . "\n";
$cleaned = preg_replace('/<think>.*?<\/think>/is', '', $text);
echo "CLEANED: " . $cleaned . "\n";
echo "CLEANED LEN: " . strlen($cleaned) . "\n";
$json = json_decode(trim($cleaned), true);
echo "JSON OK: " . ($json !== null ? "YES" : "NO - " . json_last_error_msg()) . "\n";
"""

with open('scratch/test_think.php', 'w') as f:
    f.write(test_php)

sftp = client.open_sftp()
sftp.put('scratch/test_think.php', '/tmp/test_think.php')
sftp.close()

stdin, stdout, stderr = client.exec_command(f"kubectl cp /tmp/test_think.php {pod}:/tmp/test_think.php")
time.sleep(1)
stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- php /tmp/test_think.php 2>&1")
print(stdout.read().decode())

# Now also check the worker pod has the same file
worker_pod = "laravel-worker-566897c544-bjbzn"
stdin, stdout, stderr = client.exec_command(f"kubectl exec {worker_pod} -- grep -c 'think' /app/app/Jobs/ProcessWhatsappBatchJob.php 2>&1")
print("Think matches on worker:", stdout.read().decode())

client.close()

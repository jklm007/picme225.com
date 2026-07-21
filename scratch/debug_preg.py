import paramiko, time

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

pod = "laravel-deployment-56f54497f-r8pmg"
worker_pod = "laravel-worker-566897c544-bjbzn"

# Show exact line 212 to confirm preg_replace pattern
stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- sed -n '210,215p' /app/app/Jobs/ProcessWhatsappBatchJob.php")
output = stdout.read().decode()
print("Lines 210-215 on laravel pod:")
print(repr(output))  # repr to see exact bytes

stdin, stdout, stderr = client.exec_command(f"kubectl exec {worker_pod} -- sed -n '210,215p' /app/app/Jobs/ProcessWhatsappBatchJob.php")
output2 = stdout.read().decode()
print("\nLines 210-215 on worker pod:")
print(repr(output2))

# The log shows the WORKER is processing the job but the think tag removal might not be working
# Let's manually test the regex pattern
stdin, stdout, stderr = client.exec_command(f"""kubectl exec {pod} -- php -r "
\\$text = '<think>thinking...</think>{{\"annonces\":[]}}';
\\$cleaned = preg_replace('/<think>.*?<\\/think>/is', '', \\$text);
echo 'CLEANED: ' . \\$cleaned . \"\\n\";
echo 'MATCH: ' . (preg_match('/{/', \\$cleaned) ? 'YES JSON FOUND' : 'NO JSON') . \"\\n\";
" 2>&1""")
print("\nRegex test:")
print(stdout.read().decode())

client.close()

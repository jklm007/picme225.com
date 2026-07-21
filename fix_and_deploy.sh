#!/bin/bash
# Fix category labels via Laravel Artisan
POD=$(kubectl get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers | head -n 1)
if [ -z "$POD" ]; then
  POD=$(sudo k3s kubectl get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers | head -n 1)
  KCL="sudo k3s kubectl"
else
  KCL="kubectl"
fi

echo ">>> Pod: $POD"

$KCL cp /tmp/fix_labels.sql default/${POD}:/tmp/fix_labels.sql -c laravel

# Run SQL via php -r using PDO
$KCL exec ${POD} -c laravel -- php -r "
\$dsn = getenv('DB_CONNECTION').'host='.getenv('DB_HOST').';dbname='.getenv('DB_DATABASE').';port='.(getenv('DB_PORT') ?: '5432');
\$pdo = new PDO('pgsql:host='.getenv('DB_HOST').';dbname='.getenv('DB_DATABASE').';port='.(getenv('DB_PORT') ?: '5432'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
\$sql = file_get_contents('/tmp/fix_labels.sql');
\$stmts = explode(';', \$sql);
\$count = 0;
foreach(\$stmts as \$stmt) {
  \$stmt = trim(\$stmt);
  if(empty(\$stmt) || strpos(\$stmt,'--') === 0) continue;
  try {
    \$r = \$pdo->exec(\$stmt);
    if(\$r !== false) \$count++;
  } catch(Exception \$e) { echo 'SKIP: '.\$e->getMessage().PHP_EOL; }
}
echo 'Done. '.\$count.' statements executed.'.PHP_EOL;
"
echo ">>> SQL executed!"

# Also deploy updated WhatsApp controller
$KCL cp /tmp/p3/WhatsAppWebhookController.php default/${POD}:/app/app/Http/Controllers/Api/WhatsAppWebhookController.php -c laravel

# Clear views/cache
$KCL exec ${POD} -c laravel -- php /app/artisan optimize:clear || true

echo ">>> DONE!"

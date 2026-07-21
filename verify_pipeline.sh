#!/bin/bash
echo "=== Worker logs (last 40 lines) ==="
kubectl logs laravel-worker-59bb9bcf79-4bdwg --tail=40 2>&1

echo ""
echo "=== DB: Latest whatsapp_messages ==="
kubectl exec laravel-deployment-7779b9fb85-jfnrs -- php /app/artisan db:show --counts 2>&1 | head -5 || true
kubectl exec laravel-deployment-7779b9fb85-jfnrs -- php /app/artisan db:query "SELECT id, phone, content, status, error_log FROM whatsapp_messages ORDER BY id DESC LIMIT 5" 2>&1 || \
  kubectl exec laravel-deployment-7779b9fb85-jfnrs -- php -r "
    require '/app/vendor/autoload.php';
    \$app = require_once '/app/bootstrap/app.php';
    \$kernel = \$app->make(Illuminate\Contracts\Console\Kernel::class);
    \$kernel->bootstrap();
    \$msgs = DB::select('SELECT id, phone, content, status, error_log FROM whatsapp_messages ORDER BY id DESC LIMIT 5');
    foreach(\$msgs as \$m) { echo json_encode(\$m, JSON_UNESCAPED_UNICODE) . PHP_EOL; }
  "

echo ""
echo "=== DB: Latest marketplace_listings (from WhatsApp) ==="
kubectl exec laravel-deployment-7779b9fb85-jfnrs -- php -r "
  require '/app/vendor/autoload.php';
  \$app = require_once '/app/bootstrap/app.php';
  \$kernel = \$app->make(Illuminate\Contracts\Console\Kernel::class);
  \$kernel->bootstrap();
  \$listings = DB::select(\"SELECT id, title, category, type, status, ai_confidence_score, source, created_at FROM marketplace_listings WHERE source='whatsapp' ORDER BY id DESC LIMIT 5\");
  foreach(\$listings as \$l) { echo json_encode(\$l, JSON_UNESCAPED_UNICODE) . PHP_EOL; }
"

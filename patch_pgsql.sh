#!/bin/bash
POD=$(sudo k3s kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}')
echo "=== Pod: $POD ==="

echo "--- Full pgsql config section ---"
sudo k3s kubectl exec "$POD" -- bash -c "grep -A 20 \"'pgsql' =>\" /app/config/database.php"

echo ""
echo "--- Patching ALL pgsql env() calls to hardcoded values ---"
sudo k3s kubectl exec "$POD" -- bash -c "
python3 -c \"
import re

with open('/app/config/database.php', 'r') as f:
    content = f.read()

# Find the pgsql section and replace env() calls with hardcoded values
replacements = {
    \\\"env('DB_PGSQL_HOST', '127.0.0.1')\\\": \\\"'postgres-service'\\\",
    \\\"env('DB_HOST', '127.0.0.1')\\\": \\\"'postgres-service'\\\",
    \\\"env('DB_PGSQL_PORT', '5432')\\\": \\\"'5432'\\\",
    \\\"env('DB_PORT', '5432')\\\": \\\"'5432'\\\",
    \\\"env('DB_PGSQL_DATABASE', 'forge')\\\": \\\"'picme_db'\\\",
    \\\"env('DB_DATABASE', 'forge')\\\": \\\"'picme_db'\\\",
    \\\"env('DB_PGSQL_USERNAME', 'forge')\\\": \\\"'picme_user'\\\",
    \\\"env('DB_USERNAME', 'forge')\\\": \\\"'picme_user'\\\",
    \\\"env('DB_PGSQL_PASSWORD', '')\\\": \\\"'secret_password'\\\",
    \\\"env('DB_PASSWORD', '')\\\": \\\"'secret_password'\\\",
    \\\"env('DB_PGSQL_SCHEMA', 'public')\\\": \\\"'public'\\\",
}

for old, new in replacements.items():
    content = content.replace(old, new)

with open('/app/config/database.php', 'w') as f:
    f.write(content)

print('Done!')
\"
"

echo "--- Verifying pgsql section ---"
sudo k3s kubectl exec "$POD" -- bash -c "grep -A 12 \"'pgsql' =>\" /app/config/database.php"

echo "--- Running migrations ---"
sudo k3s kubectl exec "$POD" -- php /app/artisan migrate --force

echo "=== DONE ==="

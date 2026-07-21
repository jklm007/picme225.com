#!/bin/bash
POD=$(sudo k3s kubectl get pods -l app=laravel --field-selector=status.phase=Running -o jsonpath='{.items[0].metadata.name}')
echo "Updating .env in pod $POD..."

sudo k3s kubectl exec $POD -- bash -c "
sed -i '/^GROQ_/d' /app/.env
echo 'GROQ_API_KEY=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' >> /app/.env
echo 'GROQ_MODEL=llama-3.1-8b-instant' >> /app/.env
echo 'GROQ_TIMEOUT=5' >> /app/.env
php /app/artisan config:clear
php /app/artisan queue:restart
echo 'Env updated successfully.'
"

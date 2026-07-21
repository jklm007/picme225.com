#!/bin/bash
set -e
echo "=== DEPLOIEMENT SUR TOUTES LES PRODS (PICME, JKLM, EPDD) ==="

# Auto-detect kubectl
KCL="kubectl"
command -v kubectl > /dev/null 2>&1 || KCL="sudo k3s kubectl"

function deploy_to_pod {
  POD=$1
  CONTAINER=$2
  
  if [ -z "$POD" ]; then
    return
  fi
  
  echo ""
  echo ">>> Deploiement sur: $POD (Container: $CONTAINER)"
  
  # Create missing directories in pod
  $KCL exec ${POD} -c $CONTAINER -- mkdir -p /app/app/Http/Controllers/Api
  $KCL exec ${POD} -c $CONTAINER -- mkdir -p /app/app/Http/Controllers/Admin
  $KCL exec ${POD} -c $CONTAINER -- mkdir -p /app/app/Jobs
  $KCL exec ${POD} -c $CONTAINER -- mkdir -p /app/resources/views/marketplace
  $KCL exec ${POD} -c $CONTAINER -- mkdir -p /app/resources/views/admin/whatsapp
  $KCL exec ${POD} -c $CONTAINER -- mkdir -p /app/database/migrations
  
  # Copy files
  $KCL cp /tmp/p3/WhatsAppWebhookController.php   default/${POD}:/app/app/Http/Controllers/Api/WhatsAppWebhookController.php -c $CONTAINER
  $KCL cp /tmp/p3/WhatsappListingController.php    default/${POD}:/app/app/Http/Controllers/Admin/WhatsappListingController.php -c $CONTAINER
  $KCL cp /tmp/p3/HomeController.php               default/${POD}:/app/app/Http/Controllers/HomeController.php -c $CONTAINER
  $KCL cp /tmp/p3/ProcessWhatsappBatchJob.php      default/${POD}:/app/app/Jobs/ProcessWhatsappBatchJob.php -c $CONTAINER
  $KCL cp /tmp/p3/admin.php                        default/${POD}:/app/routes/admin.php -c $CONTAINER
  
  $KCL cp /tmp/p3/marketplace_index.blade.php      default/${POD}:/app/resources/views/marketplace/index.blade.php -c $CONTAINER
  $KCL cp /tmp/p3/marketplace_detail.blade.php     default/${POD}:/app/resources/views/marketplace/detail.blade.php -c $CONTAINER
  $KCL cp /tmp/p3/whatsapp_admin_index.blade.php   default/${POD}:/app/resources/views/admin/whatsapp/index.blade.php -c $CONTAINER
  
  $KCL cp /tmp/p3/2026_07_03_112022_add_is_blacklisted_to_whatsapp_users_table.php default/${POD}:/app/database/migrations/2026_07_03_112022_add_is_blacklisted_to_whatsapp_users_table.php -c $CONTAINER
  
  # Clear cache
  $KCL exec ${POD} -c $CONTAINER -- php /app/artisan optimize:clear || true
  $KCL exec ${POD} -c $CONTAINER -- php /app/artisan view:clear || true
  echo ">>> $POD OK"
}

# 1. PICME
PICME_POD=$($KCL get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers | head -n 1)
deploy_to_pod "$PICME_POD" "laravel"
if [ ! -z "$PICME_POD" ]; then
    echo ">>> Migration sur Picme ($PICME_POD)..."
    $KCL exec ${PICME_POD} -c laravel -- php /app/artisan migrate --force || echo "Migration Picme Echouee, on continue..."
fi

# 2. JKLM
JKLM_POD=$($KCL get pods -l app=jklm-web --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers | head -n 1)
deploy_to_pod "$JKLM_POD" "jklm-web"
if [ ! -z "$JKLM_POD" ]; then
    echo ">>> Migration sur JKLM ($JKLM_POD)..."
    $KCL exec ${JKLM_POD} -c jklm-web -- php /app/artisan migrate --force || echo "Migration JKLM Echouee (DB introuvable?), on continue..."
fi

# 3. EPDD
EPDD_POD=$($KCL get pods -l app=epdd --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers | head -n 1)
deploy_to_pod "$EPDD_POD" "epdd"
if [ ! -z "$EPDD_POD" ]; then
    echo ">>> Migration sur EPDD ($EPDD_POD)..."
    $KCL exec ${EPDD_POD} -c epdd -- php /app/artisan migrate --force || echo "Migration EPDD Echouee (DB introuvable?), on continue..."
fi

echo ""
echo "=========================================="
echo " TOUTES LES PRODS ONT ETE MISES A JOUR !"
echo "=========================================="

#!/bin/bash
ZONE="europe-west9-a"
NODE="k3s-master-gcp"

echo "=== Uploading index.blade.php to Master Node ==="
gcloud compute scp resources/views/marketplace/index.blade.php ${NODE}:/tmp/index.blade.php --zone=$ZONE

echo "=== Distributing to all Laravel pods ==="
gcloud compute ssh $NODE --zone=$ZONE --command "
    PODS=\$(sudo k3s kubectl get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=':metadata.name' --no-headers)
    for POD in \$PODS; do
        echo \"Patching \$POD...\"
        sudo k3s kubectl cp /tmp/index.blade.php default/\$POD:/app/resources/views/marketplace/index.blade.php -c laravel
    done
    echo \"Done!\"
"

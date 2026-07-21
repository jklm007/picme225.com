#!/bin/bash
echo "Dumping PostgreSQL database..."
/usr/local/bin/k3s kubectl exec postgres-deployment-54dfff559f-p5dmf -- bash -c "PGPASSWORD=secret_password pg_dump -U picme_user picme_db > /tmp/picme_db.sql"
/usr/local/bin/k3s kubectl cp postgres-deployment-54dfff559f-p5dmf:/tmp/picme_db.sql /home/ubuntu/picme_db.sql

echo "Archiving application files..."
/usr/local/bin/k3s kubectl exec laravel-deployment-787568f8f4-hxdsz -c laravel -- bash -c "tar -czf /tmp/picme225_app_backup.tar.gz -C / app/"
/usr/local/bin/k3s kubectl cp laravel-deployment-787568f8f4-hxdsz:/tmp/picme225_app_backup.tar.gz /home/ubuntu/picme225_app_backup.tar.gz -c laravel

echo "Done!"


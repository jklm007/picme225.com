PODS=$(sudo k3s kubectl get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers)
for POD in $PODS; do
  echo "Clearing view cache for $POD..."
  sudo k3s kubectl exec $POD -c laravel -- php artisan view:clear
done

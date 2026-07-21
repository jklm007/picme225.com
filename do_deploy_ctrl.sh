PODS=$(sudo k3s kubectl get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=":metadata.name" --no-headers)
for POD in $PODS; do
  echo "Patching controllers in $POD..."
  sudo k3s kubectl cp /tmp/HomeController.php default/$POD:/app/app/Http/Controllers/HomeController.php -c laravel
  sudo k3s kubectl cp /tmp/MarketplaceListingController.php default/$POD:/app/app/Http/Controllers/MarketplaceListingController.php -c laravel
done

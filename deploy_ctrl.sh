POD=$(sudo k3s kubectl get pods -l app=laravel --field-selector=status.phase=Running -o custom-columns=':metadata.name' --no-headers | head -n 1)
sudo k3s kubectl cp /tmp/HomeController.php $POD:/var/www/html/app/Http/Controllers/HomeController.php -c laravel
sudo k3s kubectl cp /tmp/MarketplaceListingController.php $POD:/var/www/html/app/Http/Controllers/MarketplaceListingController.php -c laravel

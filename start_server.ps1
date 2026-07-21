# start_server.ps1
# Script pour lancer simultanément Laravel (API) et Node (Sockets)

Write-Host "Démarrage du backend Tranxit..." -ForegroundColor Cyan

# 1. Lancer le serveur Node (Sockets) dans une fenêtre séparée
Write-Host "Lancement du serveur Sockets (port 3000)..." -ForegroundColor Green
Start-Process powershell -ArgumentList "-NoExit", "-Command", "node server.js" -WindowStyle Normal

# 2. Lancer le serveur Laravel (API) dans cette fenêtre
Write-Host "Lancement de l'API Laravel (port 8000)..." -ForegroundColor Green
php artisan serve

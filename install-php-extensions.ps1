# Script d'installation des extensions PHP manquantes
# Executer en tant qu'administrateur

Write-Host "Installation des extensions PHP manquantes pour Picme225.com" -ForegroundColor Cyan
Write-Host "================================================================" -ForegroundColor Cyan
Write-Host ""

$phpIni = "C:\DevTools\php\php.ini"

# Verifier que le fichier existe
if (-not (Test-Path $phpIni)) {
    Write-Host "[ERREUR] Fichier php.ini non trouve: $phpIni" -ForegroundColor Red
    Write-Host ""
    Write-Host "Veuillez verifier le chemin de votre installation PHP." -ForegroundColor Yellow
    Write-Host "Utilisez 'php --ini' pour trouver le bon chemin." -ForegroundColor Yellow
    exit 1
}

Write-Host "[OK] Fichier php.ini trouve: $phpIni" -ForegroundColor Green
Write-Host ""

# Creer une sauvegarde
$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$backup = "$phpIni.backup.$timestamp"
try {
    Copy-Item $phpIni $backup -ErrorAction Stop
    Write-Host "[OK] Sauvegarde creee: $backup" -ForegroundColor Green
    Write-Host ""
} catch {
    Write-Host "[ERREUR] Erreur lors de la creation de la sauvegarde: $_" -ForegroundColor Red
    exit 1
}

# Lire le contenu
$content = Get-Content $phpIni

# Extensions a activer
$extensions = @('pdo_mysql', 'gd', 'fileinfo')

$modified = $false
$activated = @()
$alreadyActive = @()

Write-Host "Analyse des extensions..." -ForegroundColor Cyan
Write-Host ""

foreach ($extName in $extensions) {
    $pattern = ";extension=$extName"
    $replacement = "extension=$extName"
    
    if ($content -match [regex]::Escape($pattern)) {
        Write-Host "[ACTIVATION] $extName" -ForegroundColor Yellow
        $content = $content -replace [regex]::Escape($pattern), $replacement
        $modified = $true
        $activated += $extName
    } elseif ($content -match [regex]::Escape($replacement)) {
        Write-Host "[DEJA ACTIF] $extName" -ForegroundColor Green
        $alreadyActive += $extName
    } else {
        Write-Host "[AJOUT] $extName (non trouve dans php.ini)" -ForegroundColor Yellow
        $content += "`nextension=$extName"
        $modified = $true
        $activated += $extName
    }
}

Write-Host ""

if ($modified) {
    # Sauvegarder les modifications
    try {
        $content | Set-Content $phpIni -ErrorAction Stop
        Write-Host "================================================================" -ForegroundColor Green
        Write-Host "[OK] Fichier php.ini modifie avec succes!" -ForegroundColor Green
        Write-Host "================================================================" -ForegroundColor Green
        Write-Host ""
    } catch {
        Write-Host "[ERREUR] Erreur lors de la sauvegarde: $_" -ForegroundColor Red
        Write-Host "Restauration de la sauvegarde..." -ForegroundColor Yellow
        Copy-Item $backup $phpIni
        exit 1
    }
} else {
    Write-Host "================================================================" -ForegroundColor Cyan
    Write-Host "[INFO] Aucune modification necessaire" -ForegroundColor Cyan
    Write-Host "================================================================" -ForegroundColor Cyan
    Write-Host ""
}

# Resume
Write-Host "RESUME DE L'INSTALLATION" -ForegroundColor Cyan
Write-Host "================================================================" -ForegroundColor Cyan
Write-Host ""

if ($activated.Count -gt 0) {
    Write-Host "Extensions activees ($($activated.Count)):" -ForegroundColor Green
    foreach ($ext in $activated) {
        Write-Host "   - $ext" -ForegroundColor Green
    }
    Write-Host ""
}

if ($alreadyActive.Count -gt 0) {
    Write-Host "Extensions deja actives ($($alreadyActive.Count)):" -ForegroundColor Cyan
    foreach ($ext in $alreadyActive) {
        Write-Host "   - $ext" -ForegroundColor Cyan
    }
    Write-Host ""
}

# Verifier les fichiers DLL
Write-Host "Verification des fichiers DLL..." -ForegroundColor Cyan
Write-Host ""

$phpExtDir = "C:\DevTools\php\ext"
$missingDlls = @()

foreach ($extName in $extensions) {
    $dllPath = Join-Path $phpExtDir "php_$extName.dll"
    
    if (Test-Path $dllPath) {
        Write-Host "[OK] DLL trouvee: php_$extName.dll" -ForegroundColor Green
    } else {
        Write-Host "[MANQUANT] DLL manquante: php_$extName.dll" -ForegroundColor Red
        $missingDlls += $extName
    }
}
Write-Host ""

if ($missingDlls.Count -gt 0) {
    Write-Host "[ATTENTION] Certaines DLL sont manquantes!" -ForegroundColor Red
    Write-Host "Les extensions suivantes ne fonctionneront pas:" -ForegroundColor Yellow
    foreach ($dll in $missingDlls) {
        Write-Host "   - $dll" -ForegroundColor Yellow
    }
    Write-Host ""
    Write-Host "Solution: Telecharger PHP 8.1.32 complet depuis:" -ForegroundColor Yellow
    Write-Host "https://windows.php.net/download/" -ForegroundColor Cyan
    Write-Host ""
}

# Verifier les extensions chargees
Write-Host "Verification des extensions chargees..." -ForegroundColor Cyan
Write-Host ""

$phpPath = "C:\DevTools\php\php.exe"
if (Test-Path $phpPath) {
    $loadedExtensions = & $phpPath -m 2>&1
    
    foreach ($extName in $extensions) {
        if ($loadedExtensions -match $extName) {
            Write-Host "[OK] $extName : CHARGEE" -ForegroundColor Green
        } else {
            Write-Host "[ERREUR] $extName : NON CHARGEE" -ForegroundColor Red
        }
    }
    Write-Host ""
} else {
    Write-Host "[ATTENTION] PHP non trouve a: $phpPath" -ForegroundColor Yellow
    Write-Host ""
}

# Instructions finales
Write-Host "================================================================" -ForegroundColor Cyan
Write-Host "PROCHAINES ETAPES" -ForegroundColor Cyan
Write-Host "================================================================" -ForegroundColor Cyan
Write-Host ""

if ($modified) {
    Write-Host "1. REDEMARREZ votre serveur web:" -ForegroundColor Yellow
    Write-Host "   - XAMPP: Arreter et redemarrer Apache" -ForegroundColor Gray
    Write-Host "   - Laragon: Stop All puis Start All" -ForegroundColor Gray
    Write-Host "   - Serveur PHP: Ctrl+C puis 'php artisan serve'" -ForegroundColor Gray
    Write-Host ""
    
    Write-Host "2. Verifiez l'installation:" -ForegroundColor Yellow
    Write-Host "   php -m | findstr pdo_mysql gd fileinfo" -ForegroundColor Gray
    Write-Host ""
    
    Write-Host "3. Lancez le script de verification:" -ForegroundColor Yellow
    Write-Host "   php scripts\verify-features.php" -ForegroundColor Gray
    Write-Host ""
}

Write-Host "================================================================" -ForegroundColor Green
Write-Host "[OK] Installation terminee!" -ForegroundColor Green
Write-Host "================================================================" -ForegroundColor Green
Write-Host ""

Write-Host "Pour plus d'informations, consultez:" -ForegroundColor Cyan
Write-Host "   docs\INSTALLATION_PHP_EXTENSIONS.md" -ForegroundColor Gray
Write-Host ""

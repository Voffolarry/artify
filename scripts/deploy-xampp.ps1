# Artify - Deploy frontend + backend to XAMPP (htdocs/artify)
# Usage: powershell -ExecutionPolicy Bypass -File scripts/deploy-xampp.ps1

$ErrorActionPreference = 'Stop'
$root = Split-Path $PSScriptRoot -Parent

$htdocs = 'C:\xampp\htdocs\artify'
if ($env:ARTIFY_HTDOCS) { $htdocs = $env:ARTIFY_HTDOCS }

Write-Host "Source : $root"
Write-Host "Cible  : $htdocs"
Write-Host ""

New-Item -ItemType Directory -Force -Path $htdocs | Out-Null

Copy-Item -Path "$root\frontend\*" -Destination $htdocs -Recurse -Force
Copy-Item -Path "$root\backend\api" -Destination "$htdocs\api" -Recurse -Force
Copy-Item -Path "$root\backend\.htaccess" -Destination $htdocs -Force
Copy-Item -Path "$root\backend\composer.json" -Destination $htdocs -Force
if (Test-Path "$root\backend\.env") {
    Copy-Item -Path "$root\backend\.env" -Destination $htdocs -Force
    Write-Host ".env deploye vers $htdocs"
} else {
    Write-Host "AVERTISSEMENT : backend\.env absent - copiez .env.example vers .env"
}
if (Test-Path "$root\backend\vendor") {
    Copy-Item -Path "$root\backend\vendor" -Destination "$htdocs\vendor" -Recurse -Force
}

Set-Location $htdocs
if (-not (Test-Path "$htdocs\vendor\autoload.php")) {
    Write-Host "Installation Composer..."
    composer install --no-interaction
}

$seed = Join-Path $htdocs 'api\config\seed.php'
Write-Host ""
Write-Host "OK - Artify deploye dans $htdocs"
Write-Host "1. Demarrez Apache dans XAMPP"
Write-Host "2. Ouvrez : http://localhost/artify/login.html"
Write-Host "3. Optionnel : php $seed"

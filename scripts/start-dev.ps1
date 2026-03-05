# Lance Symfony + Stripe listen dans deux fenetres separees
# Usage: .\scripts\start-dev.ps1

$projectRoot = Split-Path -Parent $PSScriptRoot

# Verifier si Stripe CLI est installe
try {
    $null = Get-Command stripe -ErrorAction Stop
} catch {
    Write-Host "ERREUR: Stripe CLI n'est pas installe." -ForegroundColor Red
    Write-Host "Installez-le avec: winget install -e --id Stripe.StripeCli" -ForegroundColor Yellow
    Write-Host "Ou: https://stripe.com/docs/stripe-cli#install" -ForegroundColor Yellow
    exit 1
}

Write-Host "=== Demarrage Maternia (dev local) ===" -ForegroundColor Cyan

# Fenetre 1: Serveur Symfony
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd '$projectRoot'; symfony server:start"
Write-Host "[OK] Fenetre Symfony ouverte" -ForegroundColor Green

# Fenetre 2: Stripe listen (avec mise a jour auto .env)
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd '$projectRoot'; .\scripts\stripe-listen.ps1"
Write-Host "[OK] Fenetre Stripe listen ouverte" -ForegroundColor Green

Write-Host "`nLes deux terminaux sont ouverts. Fermez-les pour arreter." -ForegroundColor Gray

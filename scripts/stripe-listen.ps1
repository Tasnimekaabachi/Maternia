# Script pour lancer Stripe listen et mettre à jour .env automatiquement
# Usage: .\scripts\stripe-listen.ps1

$projectRoot = Split-Path -Parent $PSScriptRoot
$envFile = Join-Path $projectRoot ".env"

$envUpdated = $false

function Update-EnvSecret {
    param([string]$secret)
    $lines = Get-Content $envFile
    $updated = $lines | ForEach-Object {
        if ($_ -match '^STRIPE_WEBHOOK_SECRET=') {
            "STRIPE_WEBHOOK_SECRET=$secret"
        } else {
            $_
        }
    }
    $updated | Set-Content $envFile
    Push-Location $projectRoot
    php bin/console cache:clear -q 2>$null
    Pop-Location
    Write-Host "`n[OK] .env mis a jour + cache vide" -ForegroundColor Green
}

Write-Host "Lancement de Stripe listen..." -ForegroundColor Cyan
Write-Host "Appuyez sur Ctrl+C pour arreter.`n" -ForegroundColor Gray

& stripe listen --forward-to localhost:8000/api/payment/webhook 2>&1 | ForEach-Object {
    $line = $_
    if (-not $script:envUpdated -and $line -match '(whsec_[a-zA-Z0-9]+)') {
        Update-EnvSecret -secret $Matches[1]
        $script:envUpdated = $true
    }
    Write-Host $line
}

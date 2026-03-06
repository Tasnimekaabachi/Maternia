# Demarre le service Python d'analyse des pleurs (port 8001).
# Lancer ce script depuis la RACINE du projet Maternia.
# Garder ce terminal ouvert. Dans un autre terminal, lancer Symfony (symfony serve).

$ErrorActionPreference = "Stop"
$projectRoot = $PSScriptRoot + "\.."
$cryServiceDir = Join-Path $projectRoot "ml\cry_service"
$venvActivate = Join-Path $cryServiceDir ".venv\Scripts\Activate.ps1"

# Trouver un Python qui fonctionne (pas le raccourci Microsoft Store)
$pythonCmd = $null
foreach ($cmd in @("python", "python3", "py")) {
    if (-not (Get-Command $cmd -ErrorAction SilentlyContinue)) { continue }
    try {
        $out = & $cmd --version 2>&1 | Out-String
        if ($out -match "Python\s+3\.\d+" -and $out -notmatch "Microsoft Store|installez|est introuvable") {
            $pythonCmd = $cmd
            break
        }
    } catch {
        continue
    }
}
if (-not $pythonCmd) {
    Write-Host "Erreur: Python 3.10+ n'est pas installe ou pas dans le PATH." -ForegroundColor Red
    Write-Host "  - Telechargez Python sur https://www.python.org/downloads/" -ForegroundColor Yellow
    Write-Host "  - Lors de l'installation, cochez 'Add Python to PATH'." -ForegroundColor Yellow
    Write-Host "  - Redemarrez le terminal puis relancez ce script." -ForegroundColor Yellow
    exit 1
}

if (-not (Test-Path $cryServiceDir)) {
    Write-Host "Erreur: dossier introuvable: $cryServiceDir" -ForegroundColor Red
    exit 1
}

if (-not (Test-Path $venvActivate)) {
    Write-Host "Environnement virtuel absent. Creation..." -ForegroundColor Yellow
    Push-Location $cryServiceDir
    try {
        & $pythonCmd -m venv .venv
        if (-not (Test-Path ".\.venv\Scripts\Activate.ps1")) {
            Write-Host "Erreur: la creation du venv a echoue (Python invalide ou droits)." -ForegroundColor Red
            Write-Host "  Installez Python depuis https://www.python.org/downloads/ (cochez 'Add to PATH')." -ForegroundColor Yellow
            Pop-Location
            exit 1
        }
        & .\.venv\Scripts\Activate.ps1
        & $pythonCmd -m pip install -U pip -q
        pip install -r requirements.txt -q
    } finally {
        Pop-Location
    }
}

Write-Host "Demarrage du service ML (analyse des pleurs) sur http://127.0.0.1:8001" -ForegroundColor Green
Write-Host "Arreter avec Ctrl+C. Lancer Symfony dans un autre terminal (symfony serve)." -ForegroundColor Gray
Write-Host ""

Set-Location $cryServiceDir
& .\.venv\Scripts\Activate.ps1
uvicorn main:app --host 127.0.0.1 --port 8001 --reload

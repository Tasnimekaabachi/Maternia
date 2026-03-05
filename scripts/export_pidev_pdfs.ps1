param(
    [string]$ProjectRoot = (Resolve-Path (Join-Path $PSScriptRoot "..")).Path,
    [string]$OutDir = (Join-Path (Join-Path $ProjectRoot "docs") "exports")
)

$ErrorActionPreference = "Stop"

function Get-BrowserExe {
    $candidates = @(
        "C:\Program Files\Microsoft\Edge\Application\msedge.exe",
        "C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe",
        "C:\Program Files\Google\Chrome\Application\chrome.exe",
        "C:\Program Files (x86)\Google\Chrome\Application\chrome.exe"
    )

    foreach ($p in $candidates) {
        if (Test-Path $p) { return $p }
    }

    throw "Impossible de trouver Edge/Chrome. Installe Microsoft Edge ou Google Chrome, ou adapte les chemins dans scripts/export_pidev_pdfs.ps1."
}

function To-FileUrl([string]$path) {
    $full = (Resolve-Path $path).Path
    $full = $full -replace "\\", "/"
    return "file:///$full"
}

$browser = Get-BrowserExe
New-Item -ItemType Directory -Force -Path $OutDir | Out-Null

$items = @(
    @{ name = "Sprint1_Marketplace_Backlog"; html = (Join-Path $ProjectRoot "docs\\Sprint1_Marketplace_Backlog_CaptureStyle.html") },
    @{ name = "Sequence_Marketplace_Stripe_Graphique"; html = (Join-Path $ProjectRoot "docs\\Sequence_Marketplace_Stripe_Graphique.html") },
    @{ name = "Burndown_Kanban_Marketplace"; html = (Join-Path $ProjectRoot "docs\\Burndown_Kanban_Marketplace.html") }
)

foreach ($it in $items) {
    $htmlPath = $it.html
    if (-not (Test-Path $htmlPath)) {
        throw "Fichier introuvable: $htmlPath"
    }

    $pdfPath = Join-Path $OutDir ($it.name + ".pdf")
    $url = To-FileUrl $htmlPath

    Write-Host "Export PDF -> $pdfPath" -ForegroundColor Cyan

    $args = @(
        "--headless",
        "--disable-gpu",
        "--no-first-run",
        "--no-default-browser-check",
        "--disable-extensions",
        "--print-to-pdf-no-header",
        "--print-to-pdf=$pdfPath",
        $url
    )

    $p = Start-Process -FilePath $browser -ArgumentList $args -NoNewWindow -PassThru -Wait
    if ($p.ExitCode -ne 0) {
        throw "Échec export PDF ($($it.name)) : code $($p.ExitCode)"
    }
    if (-not (Test-Path $pdfPath)) {
        throw "PDF non généré: $pdfPath"
    }
}

Write-Host "OK. PDFs générés dans $OutDir" -ForegroundColor Green


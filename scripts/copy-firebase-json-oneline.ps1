# Copia el JSON de Firebase (service account) en una sola linea al portapapeles.
$ErrorActionPreference = 'Stop'

$root = Split-Path -Parent $PSScriptRoot
$jsonPath = Join-Path $root 'storage\app\firebase\isi-plaza-bf7f0-firebase-adminsdk-fbsvc-cc44137eea.json'

if (-not (Test-Path $jsonPath)) {
    Write-Host "No se encontro: $jsonPath" -ForegroundColor Red
    exit 1
}

$json = Get-Content $jsonPath -Raw -Encoding UTF8 | ConvertFrom-Json
$oneLine = $json | ConvertTo-Json -Compress -Depth 10
Set-Clipboard -Value $oneLine
Write-Host "Listo: JSON en una linea copiado al portapapeles." -ForegroundColor Green
Write-Host "Caracteres: $($oneLine.Length)"
Write-Host "Pegalo en Render como FIREBASE_SERVICE_ACCOUNT_JSON (Secret)."

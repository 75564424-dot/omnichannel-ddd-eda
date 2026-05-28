# Post-deploy client simulation smoke (Plan_SimulacionClientes.md)
# Usage: .\scripts\ops\simulate-client-smoke.ps1 -ClientSlug retailco -Events 10

param(
    [string]$ClientSlug = "retailco",
    [int]$Events = 10,
    [string]$AppUrl = "http://127.0.0.1:8080"
)

$ErrorActionPreference = "Stop"
$AppUrl = $AppUrl.TrimEnd("/")

Write-Host "==> Health checks"
Invoke-WebRequest -Uri "$AppUrl/up" -UseBasicParsing | Out-Null
$ready = Invoke-WebRequest -Uri "$AppUrl/health/ready" -UseBasicParsing
if ($ready.Content -notmatch '"ready"') { throw "Readiness check failed" }

Write-Host "==> Validate catalog"
php artisan platform:validate-catalog

Write-Host "==> Simulate client $ClientSlug ($Events events)"
php artisan platform:simulate-client $ClientSlug --events=$Events

Write-Host "==> API smoke"
$env:APP_URL = $AppUrl
bash scripts/ci/smoke-test.sh

Write-Host "Client simulation smoke passed for $ClientSlug."

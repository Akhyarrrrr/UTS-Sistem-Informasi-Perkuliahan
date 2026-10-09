param([string]$MySqlBin = 'C:\Program Files\MySQL\MySQL Server 8.0\bin')
$ErrorActionPreference='Stop'
foreach($command in @('php','composer','node','npm')) { if(!(Get-Command $command -ErrorAction SilentlyContinue)){throw "$command belum tersedia di PATH."} }
& (Join-Path $PSScriptRoot 'start-mysql.ps1') -MySqlBin $MySqlBin
Push-Location (Join-Path $PSScriptRoot 'Aplikasi')
try {
    & composer install --no-interaction --prefer-dist
    if($LASTEXITCODE -ne 0){throw 'Instalasi Composer gagal.'}
    & php scripts/configure-local.php
    if($LASTEXITCODE -ne 0){throw 'Konfigurasi database gagal.'}
    & php artisan migrate --seed --force
    if($LASTEXITCODE -ne 0){throw 'Migration atau seeder gagal.'}
    & npm ci
    if($LASTEXITCODE -ne 0){throw 'Instalasi npm gagal.'}
    & npm run build
    if($LASTEXITCODE -ne 0){throw 'Build aset gagal.'}
    & php artisan optimize:clear
} finally { Pop-Location }
Write-Output 'Instalasi selesai. Jalankan start.ps1. Akun demo: Catatan_Pribadi/Layanan_Lokal/akses-demo.txt, di luar repositori.'

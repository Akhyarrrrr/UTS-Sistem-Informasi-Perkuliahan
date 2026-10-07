param([string]$MySqlBin = 'C:\Program Files\MySQL\MySQL Server 8.0\bin', [switch]$Replay, [ValidatePattern('^uts_perkuliahan_replay(?:_[0-9]{8})?$')][string]$ReplayDatabase = 'uts_perkuliahan_replay')
$ErrorActionPreference='Stop'
foreach($command in @('php','composer','node','npm')) { if(!(Get-Command $command -ErrorAction SilentlyContinue)){throw "$command belum tersedia di PATH."} }
if(!$Replay){ & (Join-Path $PSScriptRoot 'start-mysql.ps1') -MySqlBin $MySqlBin }
elseif(!(Get-NetTCPConnection -LocalPort 3319 -State Listen -ErrorAction SilentlyContinue)){throw 'Replay memerlukan MySQL khusus UTS yang sudah berjalan.'}
Push-Location (Join-Path $PSScriptRoot 'Aplikasi')
try {
    & composer install --no-interaction --prefer-dist
    if($LASTEXITCODE -ne 0){throw 'Instalasi Composer gagal.'}
    if($Replay){ & php scripts/configure-local.php "--database=$ReplayDatabase" }
    else{ & php scripts/configure-local.php }
    if($LASTEXITCODE -ne 0){throw 'Konfigurasi database gagal.'}
    & php artisan migrate --seed --force
    if($LASTEXITCODE -ne 0){throw 'Migration atau seeder gagal.'}
    & npm ci
    if($LASTEXITCODE -ne 0){throw 'Instalasi npm gagal.'}
    & npm run build
    if($LASTEXITCODE -ne 0){throw 'Build aset gagal.'}
    & php artisan optimize:clear
} finally { Pop-Location }
if($Replay){Write-Output 'Instalasi replay selesai. Jalankan php artisan serve --host=127.0.0.1 --port=8089 dari Aplikasi. Akun demo: Runtime/akses-demo.txt.'}
else{Write-Output 'Instalasi selesai. Jalankan start.ps1. Akun demo: Runtime/akses-demo.txt.'}

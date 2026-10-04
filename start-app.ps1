$ErrorActionPreference = 'Stop'
$appDirectory = Join-Path $PSScriptRoot 'Aplikasi'
$runtimeDirectory = Join-Path $PSScriptRoot 'Runtime'
New-Item -ItemType Directory -Path $runtimeDirectory -Force | Out-Null
$listener = Get-NetTCPConnection -LocalPort 8088 -State Listen -ErrorAction SilentlyContinue | Select-Object -First 1
if ($listener) {
    $owner = Get-CimInstance Win32_Process -Filter "ProcessId=$($listener.OwningProcess)"
    if (!$owner.CommandLine -or !$owner.CommandLine.Contains($appDirectory)) { throw 'Port 8088 dipakai proses lain. Periksa konflik port sebelum menjalankan UTS.' }
    Write-Output 'Aplikasi UTS sudah berjalan: http://localhost:8088.'
    exit 0
}
$phpPath = (Get-Command php -ErrorAction Stop).Source
$serverProcess = Start-Process -FilePath $phpPath -ArgumentList @('artisan','serve','--host=127.0.0.1','--port=8088','--no-reload') -WorkingDirectory $appDirectory -WindowStyle Hidden -PassThru -RedirectStandardOutput (Join-Path $runtimeDirectory 'laravel-out.log') -RedirectStandardError (Join-Path $runtimeDirectory 'laravel-error.log')
$serverProcess.Id | Set-Content (Join-Path $runtimeDirectory 'laravel.pid')
for ($attempt = 0; $attempt -lt 30; $attempt++) {
    if (Get-NetTCPConnection -LocalPort 8088 -State Listen -ErrorAction SilentlyContinue) {
        Write-Output 'Aplikasi tersedia: http://localhost:8088'
        exit 0
    }
    Start-Sleep -Milliseconds 500
}
throw 'Server Laravel belum siap. Periksa Runtime/laravel-error.log.'

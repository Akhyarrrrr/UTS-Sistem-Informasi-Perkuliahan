$ErrorActionPreference='Stop'
$runtimeDirectory=Join-Path (Split-Path $PSScriptRoot -Parent) 'Catatan_Pribadi/Layanan_Lokal'
$appDirectory=Join-Path $PSScriptRoot 'Aplikasi'
$listeners=Get-NetTCPConnection -LocalPort 8088 -State Listen -ErrorAction SilentlyContinue
foreach($listener in $listeners){
    $serverInfo=Get-CimInstance Win32_Process -Filter "ProcessId=$($listener.OwningProcess)"
    if($serverInfo.Name -ne 'php.exe' -or $serverInfo.CommandLine -notlike "*$appDirectory*"){throw 'Pemilik port 8088 bukan aplikasi ini. Penghentian dibatalkan.'}
    Stop-Process -Id $listener.OwningProcess
}
$pidPath=Join-Path $runtimeDirectory 'laravel.pid'
if(Test-Path -LiteralPath $pidPath){
    $serverPid=[int](Get-Content -LiteralPath $pidPath)
    $parentInfo=Get-CimInstance Win32_Process -Filter "ProcessId=$serverPid"
    if($parentInfo -and $parentInfo.Name -eq 'php.exe' -and $parentInfo.CommandLine -like '*artisan*serve*') {Stop-Process -Id $serverPid}
}
$mysqlPidPath=Join-Path $runtimeDirectory 'mysql.pid'
$mysqlProcess=if(Test-Path -LiteralPath $mysqlPidPath){Get-Process -Id ([int](Get-Content -LiteralPath $mysqlPidPath)) -ErrorAction SilentlyContinue}
& php (Join-Path $appDirectory 'scripts/stop-mysql.php')
if($LASTEXITCODE -ne 0){throw 'MySQL gagal dihentikan. Periksa layanan sebelum memindahkan data.'}
if($mysqlProcess -and !$mysqlProcess.WaitForExit(30000)){throw 'MySQL belum berhenti sepenuhnya. Data belum boleh dipindahkan.'}
Write-Output 'Aplikasi dan MySQL UTS dihentikan. Data tetap tersimpan.'

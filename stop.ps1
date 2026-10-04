$ErrorActionPreference='Stop'
$runtimeDirectory=Join-Path $PSScriptRoot 'Runtime'
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
& php (Join-Path $appDirectory 'scripts/stop-mysql.php')
Write-Output 'Aplikasi dan MySQL UTS dihentikan. Data tetap tersimpan.'

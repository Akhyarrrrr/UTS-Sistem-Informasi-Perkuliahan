$ErrorActionPreference='Stop'
foreach($command in @('php','composer','node','npm','mysql','xelatex')){
    $found=Get-Command $command -ErrorAction SilentlyContinue
    Write-Output "$command : $(if($found){$found.Source}else{'tidak tersedia di PATH'})"
}
foreach($portNumber in @(3319,8088)) {
    $listening=Get-NetTCPConnection -LocalPort $portNumber -State Listen -ErrorAction SilentlyContinue
    Write-Output "Port $portNumber : $(if($listening){'aktif'}else{'tidak aktif'})"
}
Push-Location (Join-Path $PSScriptRoot 'Aplikasi')
try { & php artisan about --only=environment,drivers; & php artisan migrate:status } finally {Pop-Location}

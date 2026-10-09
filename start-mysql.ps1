param([string]$MySqlBin = 'C:\Program Files\MySQL\MySQL Server 8.0\bin')
$ErrorActionPreference = 'Stop'
$runtime = Join-Path (Split-Path $PSScriptRoot -Parent) 'Catatan_Pribadi/Layanan_Lokal'
$data = Join-Path $runtime 'mysql-data'
$mysql = $MySqlBin
New-Item -ItemType Directory -Force -Path $runtime | Out-Null
$listener = Get-NetTCPConnection -LocalPort 3319 -State Listen -ErrorAction SilentlyContinue | Select-Object -First 1
if ($listener) {
    $owner = Get-CimInstance Win32_Process -Filter "ProcessId=$($listener.OwningProcess)"
    if (!$owner.CommandLine -or !$owner.CommandLine.Contains($data)) { throw 'Port 3319 dipakai proses lain. Instans UTS tidak dijalankan; periksa konflik port.' }
    Write-Output 'MySQL UTS sudah berjalan pada 127.0.0.1:3319.'
    exit 0
}
if (!(Test-Path -LiteralPath (Join-Path $data 'mysql'))) {
    & (Join-Path $mysql 'mysqld.exe') --no-defaults --initialize-insecure "--datadir=$data" --console
    if ($LASTEXITCODE -ne 0) { throw 'Inisialisasi MySQL gagal.' }
}
if (!(Get-NetTCPConnection -LocalPort 3319 -State Listen -ErrorAction SilentlyContinue)) {
    $mysqlArguments = @('--no-defaults', '--port=3319', '--bind-address=127.0.0.1', '--mysqlx=OFF', '--skip-log-bin', "--datadir=`"$data`"", "--pid-file=`"$(Join-Path $runtime 'mysql.pid')`"", "--log-error=`"$(Join-Path $runtime 'mysql-error.log')`"")
    Start-Process -FilePath (Join-Path $mysql 'mysqld.exe') -ArgumentList $mysqlArguments -WindowStyle Hidden
}
for ($attempt=0; $attempt -lt 40; $attempt++) {
    if (Get-NetTCPConnection -LocalPort 3319 -State Listen -ErrorAction SilentlyContinue) { break }
    Start-Sleep -Milliseconds 500
}
if (!(Get-NetTCPConnection -LocalPort 3319 -State Listen -ErrorAction SilentlyContinue)) { throw "MySQL belum siap. Periksa $runtime/mysql-error.log." }
Write-Output 'MySQL UTS siap pada 127.0.0.1:3319.'

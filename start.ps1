param([string]$MySqlBin = 'C:\Program Files\MySQL\MySQL Server 8.0\bin')
$ErrorActionPreference='Stop'
& (Join-Path $PSScriptRoot 'start-mysql.ps1') -MySqlBin $MySqlBin
& (Join-Path $PSScriptRoot 'start-app.ps1')

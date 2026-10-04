$ErrorActionPreference='Stop'
$systemInfo=Get-CimInstance Win32_ComputerSystem
$processorInfo=Get-CimInstance Win32_Processor
$osInfo=Get-CimInstance Win32_OperatingSystem
$volumeInfo=Get-Volume -DriveLetter D
$specs=[ordered]@{manufacturer=$systemInfo.Manufacturer;model=$systemInfo.Model;cpu=$processorInfo.Name;cores=$processorInfo.NumberOfCores;threads=$processorInfo.NumberOfLogicalProcessors;ram_gb=[math]::Round($systemInfo.TotalPhysicalMemory/1GB,2);os=$osInfo.Caption;os_version=$osInfo.Version;architecture=$osInfo.OSArchitecture;workspace_drive='D:';capacity_gb=[math]::Round($volumeInfo.Size/1GB,2);free_gb=[math]::Round($volumeInfo.SizeRemaining/1GB,2);php=(& php -r 'echo PHP_VERSION;');node=(& node --version);composer=(& cmd /c 'composer --version --no-ansi 2>NUL');captured=(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')}
New-Item -ItemType Directory -Force -Path (Join-Path $PSScriptRoot 'Bukti')|Out-Null
$specs|ConvertTo-Json|Set-Content -Encoding utf8 (Join-Path $PSScriptRoot 'Bukti/spesifikasi.json')
$specs|ConvertTo-Json

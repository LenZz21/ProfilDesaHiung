Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

$ScriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$ProjectRoot = Split-Path -Parent $ScriptDir
$StateFile = Join-Path $ProjectRoot "storage\framework\mobile-tunnel.json"
$HotFile = Join-Path $ProjectRoot "public\hot"
$HotBackupFile = Join-Path $ProjectRoot "storage\framework\mobile-vite-hot.backup"

if (-not (Test-Path $StateFile)) {
    Write-Host "Tidak ada tunnel aktif."
    exit 0
}

$state = Get-Content $StateFile -Raw | ConvertFrom-Json
$hotBackedUp = $false
if ($state.PSObject.Properties.Name -contains "hotBackedUp") {
    $hotBackedUp = [bool]$state.hotBackedUp
}

foreach ($processId in @($state.tunnelPid, $state.serverPid)) {
    if ($null -eq $processId) {
        continue
    }

    $process = Get-Process -Id $processId -ErrorAction SilentlyContinue
    if ($null -ne $process) {
        Stop-Process -Id $processId -Force -ErrorAction SilentlyContinue
    }
}

if ($hotBackedUp -and (Test-Path $HotBackupFile) -and (-not (Test-Path $HotFile))) {
    Move-Item -Path $HotBackupFile -Destination $HotFile -Force
}

Remove-Item $StateFile -Force -ErrorAction SilentlyContinue
Write-Host "Mobile tunnel dihentikan."

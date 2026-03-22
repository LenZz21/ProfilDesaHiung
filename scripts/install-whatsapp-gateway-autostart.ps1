Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

$ScriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$RunnerScript = Join-Path $ScriptDir "run-whatsapp-gateway-daemon.ps1"
$TaskName = "ProfilDesa WhatsApp Gateway 24H"
$TaskDescription = "Menjalankan dan menjaga WhatsApp Gateway tetap aktif dengan PM2 saat user login Windows."
$StartupDir = Join-Path $env:APPDATA "Microsoft\Windows\Start Menu\Programs\Startup"
$StartupLauncher = Join-Path $StartupDir "profil-desa-whatsapp-gateway-autostart.cmd"

if (-not (Test-Path $RunnerScript)) {
    throw "Runner script tidak ditemukan: $RunnerScript"
}

$currentUser = if ($env:USERDOMAIN) { "$($env:USERDOMAIN)\$($env:USERNAME)" } else { $env:USERNAME }
$actionArgs = "-NoProfile -ExecutionPolicy Bypass -WindowStyle Hidden -File `"$RunnerScript`""

$action = New-ScheduledTaskAction -Execute "powershell.exe" -Argument $actionArgs
$trigger = New-ScheduledTaskTrigger -AtLogOn -User $currentUser
$settings = New-ScheduledTaskSettingsSet `
    -AllowStartIfOnBatteries `
    -DontStopIfGoingOnBatteries `
    -StartWhenAvailable
$principal = New-ScheduledTaskPrincipal -UserId $currentUser -LogonType Interactive -RunLevel Limited

$autoStartMode = "Task Scheduler"
try {
    Register-ScheduledTask `
        -TaskName $TaskName `
        -Description $TaskDescription `
        -Action $action `
        -Trigger $trigger `
        -Settings $settings `
        -Principal $principal `
        -Force | Out-Null
} catch {
    New-Item -ItemType Directory -Force -Path $StartupDir | Out-Null
    $cmd = @(
        "@echo off"
        "powershell.exe -NoProfile -ExecutionPolicy Bypass -WindowStyle Hidden -File `"$RunnerScript`""
    ) -join [Environment]::NewLine
    Set-Content -Path $StartupLauncher -Value $cmd -Encoding ASCII
    $autoStartMode = "Startup Folder"
}

& powershell.exe -NoProfile -ExecutionPolicy Bypass -File $RunnerScript

Write-Host "Autostart WhatsApp Gateway aktif."
Write-Host "Mode: $autoStartMode"
Write-Host "Task: $TaskName"
Write-Host "Runner: $RunnerScript"
if ($autoStartMode -eq "Startup Folder") {
    Write-Host "Launcher: $StartupLauncher"
}

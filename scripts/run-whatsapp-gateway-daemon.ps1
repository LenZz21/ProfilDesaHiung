Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

$ScriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$ProjectRoot = Split-Path -Parent $ScriptDir
$GatewayDir = Join-Path $ProjectRoot "tools\whatsapp-gateway"
$EcosystemFile = Join-Path $GatewayDir "ecosystem.config.cjs"
$Pm2Path = Join-Path $env:APPDATA "npm\pm2.cmd"
$GatewayName = "whatsapp-gateway"

if (-not (Test-Path $Pm2Path)) {
    throw "pm2 tidak ditemukan di $Pm2Path. Install dengan: npm i -g pm2"
}

if (-not (Test-Path $EcosystemFile)) {
    throw "File ecosystem PM2 tidak ditemukan: $EcosystemFile"
}

Push-Location $GatewayDir
try {
    & $Pm2Path resurrect | Out-Null
    Start-Sleep -Seconds 2

    $rawJlist = & $Pm2Path jlist 2>$null
    $processes = @()
    if ($rawJlist) {
        try {
            $processes = $rawJlist | ConvertFrom-Json
        } catch {
            $processes = @()
        }
    }

    $target = @($processes | Where-Object { $_.name -eq $GatewayName })

    if ($target.Count -gt 1) {
        & $Pm2Path delete $GatewayName | Out-Null
        & $Pm2Path start $EcosystemFile --only $GatewayName | Out-Null
    } elseif ($target.Count -eq 0) {
        & $Pm2Path start $EcosystemFile --only $GatewayName | Out-Null
    } else {
        $status = [string]($target[0].pm2_env.status)
        if ($status -ne "online") {
            & $Pm2Path restart $GatewayName | Out-Null
        }
    }

    & $Pm2Path save | Out-Null
} finally {
    Pop-Location
}

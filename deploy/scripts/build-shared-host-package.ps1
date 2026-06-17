[CmdletBinding()]
param(
    [string] $OutputDir = "",
    [switch] $SkipInstall,
    [switch] $SkipFrontendBuild,
    [switch] $IncludeUploads
)

$ErrorActionPreference = "Stop"

$root = Resolve-Path (Join-Path $PSScriptRoot "..\..")
$backendSource = Join-Path $root "backend"
$frontendSource = Join-Path $root "frontend"
$frontendDist = Join-Path $frontendSource "dist"

if ($OutputDir -eq "") {
    $OutputDir = Join-Path $root "deploy\build\mesh-photo-shared-host"
}

$OutputDir = $ExecutionContext.SessionState.Path.GetUnresolvedProviderPathFromPSPath($OutputDir)
$zipPath = Join-Path (Split-Path $OutputDir -Parent) "mesh-photo-shared-host.zip"

function Invoke-Step {
    param(
        [string] $FilePath,
        [string[]] $Arguments,
        [string] $WorkingDirectory
    )

    Write-Host ">> $FilePath $($Arguments -join ' ')"
    Push-Location $WorkingDirectory
    try {
        & $FilePath @Arguments
        if ($LASTEXITCODE -ne 0) {
            throw "$FilePath exited with code $LASTEXITCODE"
        }
    } finally {
        Pop-Location
    }
}

function Copy-DirectoryFiltered {
    param(
        [string] $Source,
        [string] $Destination
    )

    New-Item -ItemType Directory -Path $Destination -Force | Out-Null

    foreach ($item in Get-ChildItem -LiteralPath $Source -Force) {
        if ($item.Name -in @(".env", ".phpunit.result.cache", "phpunit.xml")) {
            continue
        }

        if ($item.PSIsContainer -and $item.Name -in @("tests", "vendor")) {
            continue
        }

        $target = Join-Path $Destination $item.Name

        if ($item.PSIsContainer) {
            Copy-DirectoryFiltered -Source $item.FullName -Destination $target
            continue
        }

        Copy-Item -LiteralPath $item.FullName -Destination $target -Force
    }
}

if (-not $SkipFrontendBuild) {
    if (-not $SkipInstall) {
        Invoke-Step -FilePath "npm" -Arguments @("ci") -WorkingDirectory $frontendSource
    }

    Invoke-Step -FilePath "npm" -Arguments @("run", "build") -WorkingDirectory $frontendSource
}

if (-not (Test-Path (Join-Path $frontendDist "index.html"))) {
    throw "Frontend build missing. Run npm run build in frontend or rerun without -SkipFrontendBuild."
}

if (Test-Path $OutputDir) {
    Remove-Item -LiteralPath $OutputDir -Recurse -Force
}

New-Item -ItemType Directory -Path $OutputDir -Force | Out-Null
$packageBackend = Join-Path $OutputDir "backend"
Copy-DirectoryFiltered -Source $backendSource -Destination $packageBackend

if (-not $SkipInstall) {
    Invoke-Step -FilePath "composer" -Arguments @("install", "--no-dev", "--prefer-dist", "--optimize-autoloader", "--no-interaction") -WorkingDirectory $packageBackend
} else {
    $sourceVendor = Join-Path $backendSource "vendor"
    if (-not (Test-Path (Join-Path $sourceVendor "autoload.php"))) {
        throw "Composer dependencies missing. Run composer install in backend or rerun without -SkipInstall."
    }

    Copy-Item -LiteralPath $sourceVendor -Destination (Join-Path $packageBackend "vendor") -Recurse -Force
}

$packagePublic = Join-Path $OutputDir "backend\public"

foreach ($item in Get-ChildItem -LiteralPath $frontendDist -Force) {
    if ($item.Name -in @(".htaccess", "index.php")) {
        continue
    }

    Copy-Item -LiteralPath $item.FullName -Destination (Join-Path $packagePublic $item.Name) -Recurse -Force
}

$packageUploads = Join-Path $packagePublic "uploads"
if (-not $IncludeUploads -and (Test-Path $packageUploads)) {
    foreach ($item in Get-ChildItem -LiteralPath $packageUploads -Force) {
        if ($item.Name -in @(".htaccess", ".gitkeep")) {
            continue
        }

        Remove-Item -LiteralPath $item.FullName -Recurse -Force
    }
}

$packageLogs = Join-Path $OutputDir "backend\storage\logs"
if (Test-Path $packageLogs) {
    Get-ChildItem -LiteralPath $packageLogs -Force -File | Where-Object { $_.Extension -eq ".log" } | Remove-Item -Force
}

$requiredFiles = @(
    "backend\public\index.php",
    "backend\public\index.html",
    "backend\public\.htaccess",
    "backend\public\robots.txt",
    "backend\public\uploads\.htaccess",
    "backend\vendor\autoload.php",
    "backend\.env.production.example"
)

foreach ($relativePath in $requiredFiles) {
    $fullPath = Join-Path $OutputDir $relativePath
    if (-not (Test-Path $fullPath)) {
        throw "Package verification failed. Missing $relativePath"
    }
}

if (Test-Path $zipPath) {
    Remove-Item -LiteralPath $zipPath -Force
}

Compress-Archive -Path (Join-Path $OutputDir "*") -DestinationPath $zipPath -Force

Write-Host ""
Write-Host "Shared-host package ready:"
Write-Host "  Folder: $OutputDir"
Write-Host "  ZIP:    $zipPath"
Write-Host ""
Write-Host "Upload the ZIP contents to a non-public folder, then set the domain document root to backend/public."

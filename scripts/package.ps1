$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path -Parent $PSScriptRoot
$themePath = Join-Path $projectRoot 'themes/wp-main-theme'
$outputPath = Join-Path $projectRoot 'dist'
New-Item -ItemType Directory -Force -Path $outputPath | Out-Null
Compress-Archive -Path $themePath -DestinationPath (Join-Path $outputPath 'wp-main-theme.zip') -Force
Write-Output 'Created dist/wp-main-theme.zip'
$catalogPath = Join-Path $projectRoot 'plugins/wp-store-catalog'
Compress-Archive -Path $catalogPath -DestinationPath (Join-Path $outputPath 'wp-store-catalog.zip') -Force
Write-Output 'Created dist/wp-store-catalog.zip'

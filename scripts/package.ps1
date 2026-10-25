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
$releaseFiles = @('wp-main-theme.zip', 'wp-store-catalog.zip') | ForEach-Object {
    $releaseFile = Join-Path $outputPath $_
    [ordered]@{ file = $_; bytes = (Get-Item -LiteralPath $releaseFile).Length; sha256 = (Get-FileHash -LiteralPath $releaseFile -Algorithm SHA256).Hash.ToLowerInvariant() }
}
$manifest = [ordered]@{ commit = (git -C $projectRoot rev-parse HEAD); packages = @($releaseFiles) }
$manifest | ConvertTo-Json -Depth 5 | Set-Content -LiteralPath (Join-Path $outputPath 'release-manifest.json') -Encoding utf8
Write-Output 'Created dist/release-manifest.json with package hashes'

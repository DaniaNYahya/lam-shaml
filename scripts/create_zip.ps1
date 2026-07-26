param(
  [string]$Output = "LamShaml_WEB_FINAL.zip"
)

$root = Split-Path -Parent $PSScriptRoot
$out = Join-Path $root $Output
if (Test-Path $out) { Remove-Item -LiteralPath $out -Force }
$exclude = @('.git', 'LamShaml_WEB_FINAL', 'LamShaml_source.zip', $Output, 'database/install.lock')
$items = Get-ChildItem -LiteralPath $root -Force | Where-Object { $exclude -notcontains $_.Name }
Compress-Archive -Path $items.FullName -DestinationPath $out -Force
Write-Host "Created $out"

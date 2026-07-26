param(
  [string]$Output = "LamShaml_WEB_FINAL.zip"
)

$root = Split-Path -Parent $PSScriptRoot
$out = Join-Path $root $Output
if (Test-Path $out) { Remove-Item -LiteralPath $out -Force }
$exclude = @('.git', 'LamShaml_WEB_FINAL', 'LamShaml_source.zip', $Output, 'database/install.lock')
$items = Get-ChildItem -LiteralPath $root -Force | Where-Object { $exclude -notcontains $_.Name }
$staging = Join-Path ([System.IO.Path]::GetTempPath()) ("lam-shaml-zip-" + [guid]::NewGuid().ToString("N"))
New-Item -ItemType Directory -Path $staging | Out-Null
try {
  Copy-Item -Path $items.FullName -Destination $staging -Recurse -Force
  $lock = Join-Path $staging 'database\install.lock'
  if (Test-Path $lock) { Remove-Item -LiteralPath $lock -Force }
  $stagedItems = Get-ChildItem -LiteralPath $staging -Force
  Compress-Archive -Path $stagedItems.FullName -DestinationPath $out -Force
} finally {
  Remove-Item -LiteralPath $staging -Recurse -Force -ErrorAction SilentlyContinue
}
Write-Host "Created $out"

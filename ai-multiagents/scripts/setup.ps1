# Windows bootstrap: thin wrapper around scripts/setup.py
if (-not (Get-Command python -ErrorAction SilentlyContinue)) {
    Write-Error "Python 3 is required but was not found on PATH."
    exit 1
}
python "$PSScriptRoot\setup.py"
exit $LASTEXITCODE

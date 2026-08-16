# Add C:\tools\node22 to the machine PATH, placed before C:\Program Files\nodejs\
# so that Node 22 (the project's declared engines: >=20 <23) is the default.
# Idempotent: safe to re-run.

$node22 = 'C:\tools\node22'
$old = [Environment]::GetEnvironmentVariable('Path', 'Machine')

if ($old.Contains($node22)) {
    Write-Output 'node22 already on machine PATH - nothing to do'
    exit 0
}

if ($old.Contains('C:\Program Files\nodejs\')) {
    $new = $old.Replace('C:\Program Files\nodejs\', "$node22;C:\Program Files\nodejs\")
} else {
    $new = "$node22;$old"
}

[Environment]::SetEnvironmentVariable('Path', $new, 'Machine')
Write-Output 'machine PATH updated: node22 inserted before nodejs'

# Show the relevant entries to confirm
$check = [Environment]::GetEnvironmentVariable('Path', 'Machine')
$check -split ';' | Where-Object { $_ -match 'node' } | ForEach-Object { Write-Output "  $_" }

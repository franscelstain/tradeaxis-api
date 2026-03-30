param(
    [string]$OutputRoot = "storage/app/market_data/evidence/local_phpunit",
    [switch]$SkipSuite
)

$ErrorActionPreference = 'Stop'

function Ensure-Path([string]$Path) {
    if (-not (Test-Path -LiteralPath $Path)) {
        New-Item -ItemType Directory -Path $Path -Force | Out-Null
    }
}

function Run-And-Capture {
    param(
        [Parameter(Mandatory = $true)][string]$Label,
        [Parameter(Mandatory = $true)][string]$Command,
        [Parameter(Mandatory = $true)][string]$OutputFile
    )

    Write-Host ("[RUN] {0}" -f $Command)

    $commandOutput = & cmd /c $Command 2>&1
    $exitCode = $LASTEXITCODE

    $content = @(
        ("label={0}" -f $Label),
        ("command={0}" -f $Command),
        ("exit_code={0}" -f $exitCode),
        "--- output ---"
    )

    if ($commandOutput) {
        $content += $commandOutput
    }

    Set-Content -LiteralPath $OutputFile -Value $content -Encoding UTF8

    if ($exitCode -ne 0) {
        throw ("Command failed for {0} with exit code {1}. See {2}" -f $Label, $exitCode, $OutputFile)
    }

    return [PSCustomObject]@{
        label = $Label
        command = $Command
        exit_code = $exitCode
        output_file = $OutputFile
    }
}

$repoRoot = Resolve-Path (Join-Path $PSScriptRoot '..\\..')
Set-Location $repoRoot

$phpUnitPath = Join-Path $repoRoot 'vendor/bin/phpunit'
if (-not (Test-Path -LiteralPath $phpUnitPath)) {
    throw "vendor/bin/phpunit not found. Jalankan composer install di environment lokal yang lengkap terlebih dahulu."
}

$timestamp = Get-Date -Format 'yyyyMMdd_HHmmss'
$sessionOutputDir = Join-Path $repoRoot (Join-Path $OutputRoot ("session54_post_switch_resolution_mismatch_{0}" -f $timestamp))
Ensure-Path $sessionOutputDir

$proofRuns = @()
$proofRuns += Run-And-Capture -Label 'targeted_test' -Command 'vendor\bin\phpunit --filter post_switch_resolution_mismatch tests\Unit\MarketData\MarketDataPipelineIntegrationTest.php' -OutputFile (Join-Path $sessionOutputDir '01_targeted_test.txt')
$proofRuns += Run-And-Capture -Label 'preserves_state_cluster' -Command 'vendor\bin\phpunit --filter preserves_approval_state tests\Unit\MarketData\MarketDataPipelineIntegrationTest.php' -OutputFile (Join-Path $sessionOutputDir '02_preserves_state_cluster.txt')

if (-not $SkipSuite.IsPresent) {
    $proofRuns += Run-And-Capture -Label 'integration_suite' -Command 'vendor\bin\phpunit tests\Unit\MarketData\MarketDataPipelineIntegrationTest.php' -OutputFile (Join-Path $sessionOutputDir '03_integration_suite.txt')
}

$summaryLines = @(
    'session=session54_batch54_db_backed_post_switch_resolution_mismatch_guard_minimum',
    'purpose=sync local phpunit proof for post-switch resolution mismatch rollback path',
    ("generated_at={0}" -f (Get-Date).ToString('yyyy-MM-dd HH:mm:ss zzz')),
    ("repo_root={0}" -f $repoRoot),
    ''
)

foreach ($run in $proofRuns) {
    $summaryLines += ("label={0}" -f $run.label)
    $summaryLines += ("command={0}" -f $run.command)
    $summaryLines += ("exit_code={0}" -f $run.exit_code)
    $summaryLines += ("output_file={0}" -f $run.output_file)
    $summaryLines += ''
}

$summaryFile = Join-Path $sessionOutputDir 'proof_summary.txt'
Set-Content -LiteralPath $summaryFile -Value $summaryLines -Encoding UTF8

Write-Host ''
Write-Host ("Proof artifacts saved to: {0}" -f $sessionOutputDir)
Write-Host ("Summary: {0}" -f $summaryFile)

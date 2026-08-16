# C171 Trade Evidence Diagnostic Operator Commands

Run from:

```text
D:\Laravel\watchlist\tradeaxis-api
```

## Database rule

The diagnostic reads real official evidence and Market Data. It must use:

```text
DB_DATABASE=tradeaxis
```

PHPUnit remains isolated on:

```text
DB_DATABASE=tradeaxis_testing
```

Confirm the SQL target before manual inspection:

```sql
SELECT DATABASE() AS current_database;
```

Expected: `tradeaxis`.

## 1. Preserve and verify the official baseline

```sql
SELECT
    eval_id,
    policy_code,
    param_id,
    from_date,
    to_date,
    days_covered,
    picks_count,
    universe_count,
    cutoff_count,
    paramset_hash,
    eval_model_hash,
    implementation_version,
    implementation_hash,
    evidence_manifest_hash
FROM watchlist_bt_eval
WHERE eval_id = 188;
```

Do not update or delete this row.

## 2. Focused tests

```powershell
vendor\bin\phpunit tests\Unit\Watchlist `
  --filter "WeeklySwingC171TradeEvidenceDiagnosticTest"

vendor\bin\phpunit tests\Unit\Watchlist `
  --filter "WatchlistBacktestC171StaticGuardTest"

vendor\bin\phpunit tests\Unit\Watchlist --filter "C171"
```

Then run full Watchlist regression:

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

## 3. Execute the read-only diagnostic

```powershell
php -d memory_limit=512M artisan watchlist:backtest-c171-trade-evidence-diagnostic `
  --eval-id=188 `
  --param-set-id=1 `
  --approval-reference=C171_OPERATOR_APPROVED_READ_ONLY_TRADE_EVIDENCE_DIAGNOSTIC `
  --operator-approved `
  --output=storage/app/watchlist/backtest/c171-trade-evidence-diagnostic.json `
  --overwrite `
  --progress
```

Expected top-level shape:

```text
status=C171_TRADE_EVIDENCE_DIAGNOSTIC_COMPLETED
reason_code=<one remediation classification>
eval_id=188
param_set_id=1
draft_paramset_created=0
oos_runtime_invoked=0
paramset_promoted=0
plan_run_created=0
production_ready=0
```

A nonzero command exit with `C171_TRADE_DIAGNOSTIC_REPRODUCTION_PARITY_FAILED`
means no strategy-remediation conclusion is allowed until the mismatch is
explained.

## 4. Expected files

```powershell
Get-ChildItem storage/app/watchlist/backtest/c171-trade-evidence-diagnostic*

Get-FileHash storage/app/watchlist/backtest/c171-trade-evidence-diagnostic.json -Algorithm SHA1
Get-FileHash storage/app/watchlist/backtest/c171-trade-evidence-diagnostic-trades.csv -Algorithm SHA1
Get-FileHash storage/app/watchlist/backtest/c171-trade-evidence-diagnostic-segments.csv -Algorithm SHA1
Get-FileHash storage/app/watchlist/backtest/c171-trade-evidence-diagnostic-anomalies.csv -Algorithm SHA1
```

Temporary spool:

```text
storage/app/watchlist/backtest/.c171-trade-evidence-diagnostic-spool
```

It should have no matching run files after completion.

## 5. Read the result

```powershell
$run = Get-Content `
  storage/app/watchlist/backtest/c171-trade-evidence-diagnostic.json `
  -Raw | ConvertFrom-Json

$run.status
$run.reason_code
$run.official_pick_parity | Format-List
$run.baseline_metrics | Format-List
$run.metrics_without_flagged_anomalies | Format-List
$run.canonical_gates.gates | Format-List
$run.canonical_gates_without_flagged_anomalies.gates | Format-List
$run.anomaly_summary | Format-List
$run.segment_highlights | ConvertTo-Json -Depth 10
$run.next_recommendation
```

## 6. Review the largest negative segments

```powershell
Import-Csv storage/app/watchlist/backtest/c171-trade-evidence-diagnostic-segments.csv |
  Where-Object { [int]$_.trade_count -ge 10 } |
  Sort-Object { [double]$_.avg_ret_net } |
  Select-Object -First 40 |
  Format-Table axis,segment,trade_count,avg_ret_net,median_ret_net,p25_ret_net,win_rate,anomaly_count -AutoSize
```

Review anomalies:

```powershell
Import-Csv storage/app/watchlist/backtest/c171-trade-evidence-diagnostic-anomalies.csv |
  Sort-Object { [double]$_.ret_net } |
  Format-Table trade_date,ticker_code,entry_trade_date,exit_trade_date,entry_price,exit_price,ret_net,price_ratio,fill_rule,market_data_review_reason,source_publication_id,source_publication_version,source_run_id -AutoSize
```

## 7. Boundary verification

```sql
SELECT COUNT(*) AS oos_count FROM watchlist_bt_oos_eval_ws;
SELECT COUNT(*) AS active_paramset_count FROM watchlist_param_sets WHERE status = 'ACTIVE';
SELECT COUNT(*) AS plan_run_count FROM watchlist_plan_runs;
```

The diagnostic must not increase these counts.

## 8. Operator evidence block

Record:

```text
C171_TRADE_DIAGNOSTIC_STATUS=...
C171_TRADE_DIAGNOSTIC_REASON_CODE=...
C171_TRADE_DIAGNOSTIC_ARTIFACT_HASH=...
C171_TRADE_DIAGNOSTIC_FILE_SHA1=...
OFFICIAL_PICK_PARITY_PASS=...
OFFICIAL_PICK_COUNT=1425
REPRODUCED_PICK_COUNT=...
FLAGGED_ANOMALY_COUNT=...
PRICE_DISCONTINUITY_COUNT=...
METRICS_WITHOUT_ANOMALIES_AVG=...
METRICS_WITHOUT_ANOMALIES_MEDIAN=...
METRICS_WITHOUT_ANOMALIES_P25=...
REMEDIATION_CLASSIFICATION=...
NEXT_RECOMMENDATION=...
DRAFT_PARAMSET_CREATED=0
OOS_RUNTIME_INVOKED=0
PARAMSET_PROMOTED=0
PLAN_RUN_CREATED=0
PRODUCTION_READY=0
```

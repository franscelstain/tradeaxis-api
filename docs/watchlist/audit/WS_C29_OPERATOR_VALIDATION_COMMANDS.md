# WS C29 Operator Validation Commands

C29 validation must be run only against the locked C28 G05 candidate and the reserved OOS window.

Do not claim PASS until the commands below are actually run in the operator/CI PHP baseline.

## PHPUnit C29 Only

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC29"
```

Expected marker:

```text
PHPUNIT_C29=PASS
```

If the command cannot run, record:

```text
PHPUNIT_C29=NOT_RUN
C29_OPERATOR_VALIDATION_REQUIRED=true
```

## Full Watchlist PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

Expected marker:

```text
FULL_WATCHLIST_PHPUNIT=PASS
```

If the command cannot run, record:

```text
FULL_WATCHLIST_PHPUNIT=NOT_RUN
C29_OPERATOR_VALIDATION_REQUIRED=true
```

## C29 OOS Proof Runtime

```powershell
php artisan watchlist:backtest-c29-oos-proof `
  --c28-artifact=storage/app/watchlist/backtest/c28-rule-revision-tiebreak-diagnostic-all-param.json `
  --expected-c28-hash=64ec3e48fa3c6beb4b1175cc8f0cc277f22d20fd `
  --candidate-profile-code=C28_G05_BUCKET_TIEBREAK_R09_STABLE_G21_NO_SIGNAL_G16_DELAY `
  --from=2025-05-22 `
  --to=2026-05-29 `
  --output=storage/app/watchlist/backtest/c29-oos-proof-c28-g05.json `
  --progress
```

If replacing an existing artifact intentionally, add `--overwrite` and record that the artifact was replaced.

Expected runtime markers:

```text
status=C29_OOS_PROOF_PASSED_NOT_PRODUCTION_READY
```

or:

```text
status=C29_OOS_PROOF_FAILED
```

or:

```text
status=C29_BLOCKED_INVALID_C28_SOURCE
```

If the runtime command cannot be executed in the environment, record:

```text
C29_RUNTIME=NOT_RUN
C29_OPERATOR_VALIDATION_REQUIRED=true
```

## Read C29 Artifact Summary

```powershell
$run = Get-Content storage/app/watchlist/backtest/c29-oos-proof-c28-g05.json | ConvertFrom-Json

$run | Select-Object `
  run_code,
  status,
  production_ready,
  expected_c28_hash,
  actual_c28_hash,
  c28_hash_match,
  candidate_profile_code,
  lookahead_violation_count |
  Format-List

$run.metrics | Format-List

$run.gate | Format-List

$run.diagnostics | Select-Object reason_code,message | Format-Table -AutoSize
```

## C29 Artifact Hash

```powershell
Get-FileHash storage/app/watchlist/backtest/c29-oos-proof-c28-g05.json -Algorithm SHA1
```

Record the resulting hash in `WS_C29_OOS_PROOF.md` and `LUMEN_IMPLEMENTATION_STATUS.md` only after the artifact exists.

## No Overclaim Rule

```text
DO_NOT_CLAIM_PHPUNIT_PASS_IF_NOT_RUN=true
DO_NOT_CLAIM_FULL_WATCHLIST_PASS_IF_NOT_RUN=true
DO_NOT_CLAIM_C29_RUNTIME_PASS_IF_NOT_RUN=true
DO_NOT_PROMOTE_C29_TO_PRODUCTION=true
production_ready=0
```

## Final Operator Evidence Recorded for C29

The operator ran the C29 validation commands in the project environment and produced the following evidence:

```text
PHPUNIT_C29=PASS: OK (13 tests, 132 assertions)
FULL_WATCHLIST_PHPUNIT=PASS: OK (448 tests, 10900 assertions)
C29_RUNTIME=FAIL
status=C29_OOS_PROOF_FAILED
reason_code=C29_OOS_PROOF_FAILED
artifact_path=storage/app/watchlist/backtest/c29-oos-proof-c28-g05.json
artifact_hash=c02add8f2cc8af53bdb3f0cf9d0c7d90d63e1dd9
expected_c28_hash=64ec3e48fa3c6beb4b1175cc8f0cc277f22d20fd
actual_c28_hash=64ec3e48fa3c6beb4b1175cc8f0cc277f22d20fd
c28_hash_match=1
production_ready=0
```

C29 failed metrics:

```text
evaluated_picks_count=128
avg_ret_net=0.004431048028767
median_ret_net=0.0052763819095477
p25_ret_net=-0.0075615188321481
win_rate=0.53125
month_win_rate_min=0
month_avg_ret_net_min=-0.040489877530617
lookahead_violation_count=4
```

Bad months:

```text
2025-06 win_rate=0
2025-08 win_rate=0
2026-03 win_rate=0
```

Rows contributing to the lookahead gate failure are currently classified as missing D1-D5 raw OHLC path rows:

```text
2025-06-04 MICE param_id=151 R09 WS_BT_C29_D1_TO_D5_RAW_OHLC_PATH_MISSING
2025-06-04 MICE param_id=152 R09 WS_BT_C29_D1_TO_D5_RAW_OHLC_PATH_MISSING
2025-08-15 BBSI param_id=151 R09 WS_BT_C29_D1_TO_D5_RAW_OHLC_PATH_MISSING
2025-08-15 BBSI param_id=152 R09 WS_BT_C29_D1_TO_D5_RAW_OHLC_PATH_MISSING
```

C29 final marker:

```text
C29_FINAL_VERDICT=C29_OOS_PROOF_FAILED
NEXT_STEP=C30_OOS_FAILURE_ATTRIBUTION_AND_DATA_COMPLETENESS_DIAGNOSTIC
```


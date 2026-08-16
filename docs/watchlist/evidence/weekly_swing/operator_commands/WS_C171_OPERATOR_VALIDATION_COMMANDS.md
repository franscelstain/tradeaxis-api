# C171 Operator Validation Commands

Run from:

```text
D:\Laravel\watchlist\tradeaxis-api
```


## Database target rule

Use the databases as follows. This separation is mandatory:

```text
tradeaxis
= normal `php artisan migrate`
= C171 runtime command
= c171-diagnose.php / c171-source-diagnose.php
= SQL inspection of publications, Market Data, paramsets, evals, picks, universe, and cutoffs

tradeaxis_testing
= `php artisan migrate --env=testing --force`
= every PHPUnit command
= test fixtures only; never copy or point tests to the main `tradeaxis` database
```

Before any runtime or SQL diagnosis in phpMyAdmin/DBeaver, confirm:

```sql
SELECT DATABASE() AS current_database;
```

Expected for C171 runtime diagnosis:

```text
current_database=tradeaxis
```

Normal C171 runtime commands must not include `--env=testing`.

## 1. Preflight and apply migration

Before migration, the legacy support tables must be empty unless they already have `eval_id`. C171 intentionally refuses to guess ownership for existing unversioned rows.

```sql
SELECT COUNT(*) AS picks_rows FROM watchlist_bt_picks_ws;
SELECT COUNT(*) AS universe_rows FROM watchlist_bt_universe_ws;
SELECT COUNT(*) AS cutoff_rows FROM watchlist_bt_cutoffs_ws;
```

Expected for the current source-of-truth database before versioning:

```text
PICKS_ROWS=0
UNIVERSE_ROWS=0
CUTOFF_ROWS=0
```

Then apply:

```powershell
php artisan migrate
php artisan migrate:status
```

### Recovery when the first C171 migration attempt stopped at `FK_bt_picks_eval`

MySQL/MariaDB DDL is not fully transactional. A failed migration may already have
added several C171 columns even though the migration is still marked `Pending`.
The repaired C171 migration is intentionally resumable and reads the actual
parent-column type before creating each foreign key.

The historical owner DDL may define `watchlist_bt_eval.eval_id` as signed
`BIGINT`, while the older Laravel migration may define it as `BIGINT UNSIGNED`.
MySQL requires exact numeric signedness on both sides of a foreign key. Do not
manually force one side without inspecting the parent.

Inspect the actual schema:

```sql
SELECT
    TABLE_NAME,
    ENGINE
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN (
      'watchlist_bt_eval',
      'watchlist_bt_picks_ws',
      'watchlist_bt_universe_ws',
      'watchlist_bt_cutoffs_ws',
      'watchlist_bt_param_grid'
  )
ORDER BY TABLE_NAME;

SELECT
    TABLE_NAME,
    COLUMN_NAME,
    COLUMN_TYPE,
    IS_NULLABLE,
    COLUMN_KEY,
    EXTRA
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND (
      (TABLE_NAME = 'watchlist_bt_eval' AND COLUMN_NAME = 'eval_id')
      OR (TABLE_NAME = 'watchlist_bt_picks_ws' AND COLUMN_NAME = 'eval_id')
      OR (TABLE_NAME = 'watchlist_bt_universe_ws' AND COLUMN_NAME IN ('eval_id', 'param_id'))
      OR (TABLE_NAME = 'watchlist_bt_cutoffs_ws' AND COLUMN_NAME = 'eval_id')
      OR (TABLE_NAME = 'watchlist_bt_param_grid' AND COLUMN_NAME = 'param_id')
  )
ORDER BY TABLE_NAME, ORDINAL_POSITION;
```

After applying the repaired source, rerun normally:

```powershell
php artisan migrate
php artisan migrate:status
```

Do not run `migrate:rollback` for this failed first attempt. The original C171
`down()` is intentionally non-destructive, while MySQL may already have
auto-committed part of the `up()` DDL.

Confirm migration:

```text
2026_07_25_000001_version_watchlist_official_backtest_evidence_and_paramset_identity=Ran
```

## 2. Schema inspection

Lumen in this repository does not provide `artisan tinker`. Use phpMyAdmin,
DBeaver, SQL, or the documented standalone C171 diagnostic scripts.

Inspect these columns in the main `tradeaxis` database:

```text
watchlist_param_sets.params_hash
watchlist_param_sets.eval_model_hash
watchlist_param_sets.implementation_hash
watchlist_bt_eval.evidence_manifest_hash
watchlist_bt_picks_ws.eval_id
watchlist_bt_universe_ws.eval_id
watchlist_bt_cutoffs_ws.eval_id
watchlist_bt_oos_eval_ws.is_evidence_manifest_hash
```

## 3. Testing database preflight

PHPUnit uses `.env.testing`, which points to `tradeaxis_testing`. Create and migrate that database before running database-backed regression tests. Do not point testing to the production `tradeaxis` database.

```sql
CREATE DATABASE IF NOT EXISTS tradeaxis_testing
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
```

Then apply the test schema:

```powershell
php artisan migrate --env=testing --force
```

Confirm `.env.testing` still contains:

```text
DB_DATABASE=tradeaxis_testing
```

## 4. Focused tests

On Windows, run filters separately. A quoted expression containing `|` may still be split by the Composer `.bat` wrapper.

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "C171"
vendor\bin\phpunit tests\Unit\Watchlist --filter "WeeklySwingC171"
vendor\bin\phpunit tests\Unit\Watchlist --filter "WeeklySwingParamsetPersistenceAndPromotion"
```

Record:

```text
FOCUSED_PHPUNIT_C171=OK (... tests, ... assertions)
```

## 5. Cross-pipeline regression

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestIsCalibration"
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestPublishedPrice"
vendor\bin\phpunit tests\Unit\Watchlist --filter "WeeklySwingParamset"
```

## 6. Full Watchlist regression

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

## 7. Official IS execution only

```powershell
php artisan watchlist:backtest-c171-versioned-official-is-evidence `
  --param-set-id=1 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --approval-reference=C171_OPERATOR_APPROVED_OFFICIAL_IS_EVIDENCE_ONLY `
  --operator-approved `
  --output=storage/app/watchlist/backtest/c171-versioned-official-is-evidence.json `
  --overwrite `
  --progress
```

The command may exit `1` when canonical IS gates fail. That is not an implementation failure when the artifact is persisted and the reason is `C171_NO_EXECUTABLE_IS_CANDIDATE_CANONICAL_GATES_FAILED`.

## 8. Artifact inspection

```powershell
$run = Get-Content storage/app/watchlist/backtest/c171-versioned-official-is-evidence.json | ConvertFrom-Json

$run.run_code
$run.status
$run.reason_code
$run.params_hash
$run.eval_model_hash
$run.implementation_hash
$run.canonical_is_gates_pass
$run.official_evidence_manifest | Format-List
$run.execution_route_proof | Format-List
$run.strict_is_boundary
$run.hard_market_data_to_date
$run.max_requested_market_data_date
$run.boundary_censored_trade_date_count
$run.future_derived_route_used
$run.oos_runtime_invoked
$run.oos_rows_before
$run.oos_rows_after
$run.oos_mutated
$run.paramset_promoted
$run.active_paramset_created
$run.plan_run_created
$run.recommendation_persisted
$run.confirm_mutated
$run.production_activation_executed
$run.controlled_rollout_executed
$run.production_ready
$run.next_recommendation
$run.artifact_hash
```

Expected hard boundaries:

```text
STRICT_IS_BOUNDARY=1
HARD_MARKET_DATA_TO_DATE=2025-05-21
MAX_REQUESTED_MARKET_DATA_DATE<=2025-05-21
TRADE_CANDIDATES_FROZEN_BEFORE_PRICE_READ=1
FUTURE_PRICE_USED_FOR_EVALUATION_ONLY=1
STRATEGY_PAYLOAD_IMMUTABLE=1
FUTURE_DERIVED_ROUTE_USED=0
OOS_RUNTIME_INVOKED=0
OOS_ROWS_BEFORE=OOS_ROWS_AFTER
OOS_MUTATED=0
PARAMSET_PROMOTED=0
ACTIVE_PARAMSET_CREATED=0
PLAN_RUN_CREATED=0
RECOMMENDATION_PERSISTED=0
CONFIRM_MUTATED=0
PRODUCTION_ACTIVATION_EXECUTED=0
CONTROLLED_ROLLOUT_EXECUTED=0
PRODUCTION_READY=0
```

## 9. Database identity inspection

```sql
SELECT
    eval_id,
    policy_code,
    param_id,
    paramset_hash,
    eval_model_hash,
    implementation_version,
    implementation_hash,
    picks_count,
    picks_hash,
    universe_count,
    universe_hash,
    cutoff_count,
    cutoffs_hash,
    market_data_lineage_hash,
    evidence_manifest_hash,
    from_date,
    to_date
FROM watchlist_bt_eval
ORDER BY eval_id DESC
LIMIT 5;

SELECT eval_id, COUNT(*) FROM watchlist_bt_picks_ws GROUP BY eval_id ORDER BY eval_id DESC;
SELECT eval_id, COUNT(*) FROM watchlist_bt_universe_ws GROUP BY eval_id ORDER BY eval_id DESC;
SELECT eval_id, COUNT(*) FROM watchlist_bt_cutoffs_ws GROUP BY eval_id ORDER BY eval_id DESC;
```

The counts and 40-character hashes must match the exact `watchlist_bt_eval` row referenced by the C171 artifact. Hash columns must contain SHA1 strings, never numeric `0`. Verify that every support row has non-null `eval_id`, `ticker_code` where applicable, `source_publication_id`, `source_publication_version`, `source_run_id`, and `row_hash`. The set of universe dates must exactly equal the set of cutoff dates.

## 10. File SHA1

```powershell
Get-FileHash storage/app/watchlist/backtest/c171-versioned-official-is-evidence.json -Algorithm SHA1
```

## 11. Evidence to return

```text
MIGRATION_C171=RAN
FOCUSED_PHPUNIT_C171=OK (... tests, ... assertions)
FULL_WATCHLIST_PHPUNIT_POST_C171=OK (... tests, ... assertions)
C171_STATUS=...
C171_REASON_CODE=...
C171_ARTIFACT_HASH=...
C171_FILE_SHA1=...
DRAFT_PARAMS_HASH=...
IS_PARAMSET_HASH=...
DRAFT_IS_HASH_MATCH=1
DRAFT_IS_EVAL_MODEL_HASH_MATCH=1
DRAFT_IS_IMPLEMENTATION_HASH_MATCH=1
STRICT_IS_BOUNDARY=1
HARD_MARKET_DATA_TO_DATE=2025-05-21
FUTURE_DERIVED_ROUTE_USED=0
PICKS_COUNT=...
PICKS_HASH=...
UNIVERSE_COUNT=...
UNIVERSE_HASH=...
CUTOFF_COUNT=...
CUTOFFS_HASH=...
EVIDENCE_MANIFEST_HASH=...
OOS_RUNTIME_INVOKED=0
OOS_ROWS_UNCHANGED=1
PARAMSET_PROMOTED=0
ACTIVE_PARAMSET_COUNT=0
PLAN_RUN_COUNT=0
PRODUCTION_READY=0
```

## 12. Streaming-memory remediation validation

The pre-remediation full-window runtime exhausted both 512 MB and 2048 MB while
materializing and canonicalizing the complete universe. After applying the
streaming patch, run the normal command first; do not use unlimited memory:

```powershell
php artisan watchlist:backtest-c171-versioned-official-is-evidence `
  --param-set-id=1 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --approval-reference=C171_OPERATOR_APPROVED_OFFICIAL_IS_EVIDENCE_ONLY `
  --operator-approved `
  --output=storage/app/watchlist/backtest/c171-versioned-official-is-evidence.json `
  --overwrite `
  --progress
```

The main database remains `tradeaxis`. PHPUnit remains on `tradeaxis_testing`.
The temporary spool directory is:

```text
storage/app/watchlist/backtest/.c171-official-evidence-spool
```

After successful first persistence, matching raw and canonical JSONL files
should be removed automatically. If the command fails before persistence, the
files may remain for diagnosis. Do not import them manually into the database.

Focused validation:

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WeeklySwingC171EvidenceIdentityTest"
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestIsCalibration"
vendor\bin\phpunit tests\Unit\Watchlist --filter "C171"
```

Expected contract:

```text
OFFICIAL_EVIDENCE_STORAGE_MODE=JSONL_SPOOL
COMPACT_REPLAY_ITEMS=1
STREAMING_MANIFEST_EQUALS_IN_MEMORY_MANIFEST=1
OOS_RUNTIME_INVOKED=0
PARAMSET_PROMOTED=0
PLAN_RUN_CREATED=0
PRODUCTION_READY=0
```

## 13. Universe `vol_ratio` precision remediation

The streaming runtime may expose historical `vol_ratio` values above
`9999.999999`. Apply the widening migration to the main runtime database before
re-running C171:

```powershell
# Uses DB_DATABASE=tradeaxis
php artisan migrate
```

Keep testing-schema parity:

```powershell
# Uses DB_DATABASE=tradeaxis_testing
php artisan migrate --env=testing --force
```

Verify on `tradeaxis`:

```sql
SELECT DATABASE() AS current_database;

SELECT COLUMN_TYPE
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'watchlist_bt_universe_ws'
  AND COLUMN_NAME = 'vol_ratio';
```

Expected:

```text
current_database=tradeaxis
COLUMN_TYPE=decimal(20,6)
```

Do not clamp, cap, or replace extreme ratios. The official evidence must preserve
the six-decimal value used by the C171 row-hash contract.


## 14. Final operator result and targeted trade-evidence continuation

Final validated official evidence:

```text
FULL_WATCHLIST_PHPUNIT=OK (7085 tests, 47794 assertions)
OFFICIAL_EVAL_ID=188
PICKS_COUNT=1425
UNIVERSE_COUNT=401982
CUTOFF_COUNT=508
C171_ARTIFACT_HASH=fef05d2849746e233290224ca3c18018a44bbd81
C171_FILE_SHA1=B9A3E74466F05FB7A1504CAFF4C7B06F86DD3F62
CANONICAL_IS_GATES_PASS=0
OOS_RUNTIME_INVOKED=0
PARAMSET_PROMOTED=0
PLAN_RUN_CREATED=0
PRODUCTION_READY=0
```

The official-evidence command is complete. Continue with the separate read-only
operator procedure in:

```text
docs/watchlist/evidence/weekly_swing/operator_commands/WS_C171_TRADE_EVIDENCE_DIAGNOSTIC_OPERATOR_COMMANDS.md
```

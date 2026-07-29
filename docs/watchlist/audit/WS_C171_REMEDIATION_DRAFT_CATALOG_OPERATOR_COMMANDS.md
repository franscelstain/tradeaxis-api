# C171 Remediation DRAFT Catalog Operator Commands

## Database ownership

Use the main database for migration, catalog/DRAFT persistence, and later
official IS:

```text
DATABASE=tradeaxis
```

Use the testing database/SQLite test connection for PHPUnit:

```text
DATABASE=tradeaxis_testing
```

Do not add `--env=testing` to runtime persistence commands.

## Repair prerequisite

The operator already completed the C171-R1 migration on both main and testing
databases. This hash-identity repair adds no migration. Do not roll back or
rerun the migration manually; `php artisan migrate` may be used only to confirm
there is nothing pending.

## 1. Apply migration to `tradeaxis`

```powershell
php artisan migrate
```

Expected migration:

```text
2026_07_27_000002_add_c171_real_is_remediation_catalog_bounds
```

Verify in the main database:

```sql
SELECT DATABASE() AS current_database;

SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'watchlist_bt_param_grid'
  AND COLUMN_NAME IN ('max_dv20_idr', 'max_vol_ratio', 'top_max_score_total')
ORDER BY COLUMN_NAME;
```

Expected:

```text
max_dv20_idr       bigint unsigned  YES
max_vol_ratio      decimal(20,6)    YES
top_max_score_total decimal(10,6)   YES
```

## 2. Synchronize the testing schema

```powershell
php artisan migrate --env=testing --force
```

## 3. Focused tests

```powershell
vendor\bin\phpunit tests\Unit\Watchlist `
  --filter "WatchlistBacktestC171RemediationParamGridCatalogTest"
```

```powershell
vendor\bin\phpunit tests\Unit\Watchlist `
  --filter "WeeklySwingC171RemediationDraftCatalogPersistenceTest"
```

```powershell
vendor\bin\phpunit tests\Unit\Watchlist `
  --filter "WatchlistBacktestC171StaticGuardTest"
```

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "C171"
```

## 4. Full Watchlist regression

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

Do not persist the catalog while any focused or full Watchlist test is red.

## 5. Persist the exact catalog and five DRAFTs

This command uses `tradeaxis` and does not run IS/OOS:

```powershell
php artisan watchlist:backtest-c171-persist-remediation-draft-catalog `
  --source-eval-id=188 `
  --source-param-set-id=1 `
  --diagnostic-artifact=storage/app/watchlist/backtest/c171-trade-evidence-diagnostic.json `
  --approval-reference=C171_OPERATOR_APPROVED_IMMUTABLE_DRAFT_CATALOG_PERSISTENCE_ONLY `
  --operator-approved `
  --output-dir=storage/app/watchlist/backtest/c171-remediation-draft-catalog `
  --output=storage/app/watchlist/backtest/c171-remediation-draft-catalog.json `
  --overwrite `
  --progress
```

Expected top-level result:

```text
status=C171_IMMUTABLE_REMEDIATION_DRAFT_CATALOG_PERSISTED
reason_code=C171_FIVE_NEW_IMMUTABLE_DRAFT_PARAMSETS_PERSISTED_OFFICIAL_IS_NOT_RUN
catalog_code=WS_BT_GRID_REAL_IS_REMEDIATION_C171_R1_2026_07
catalog_version=C171-R1
catalog_hash=82b0fcbf17823fda5ab59bd2dba3d947b4f9e233
catalog_row_count=5
candidate_hash_contract=DERIVED_FROM_IMMUTABLE_SOURCE_CANONICAL_PAYLOAD_AND_CATALOG_ROW
candidate_hash_manifest_hash=<non-empty SHA1>
draft_paramset_created_count=4
draft_paramset_idempotent_count=1
official_is_runtime_invoked=0
oos_runtime_invoked=0
paramset_promoted=0
plan_run_created=0
production_ready=0
next_recommendation=C171_RUN_VERSIONED_OFFICIAL_IS_FOR_EACH_IMMUTABLE_REMEDIATION_DRAFT
```

After the recorded partial attempt, the repaired rerun should normally report
one `IDEMPOTENT` DRAFT (`param_set_id=2`) and four `INSERTED` DRAFTs. A later
exact rerun should report five idempotent rows without creating duplicates.
Do not delete or renumber the existing first DRAFT.

## 6. Database verification

```sql
SELECT
    param_id,
    catalog_code,
    catalog_version,
    row_code,
    min_dv20_idr,
    max_dv20_idr,
    min_vol_ratio,
    max_vol_ratio,
    min_atr14_pct,
    max_atr14_pct,
    top_max_score_total,
    catalog_hash,
    row_hash
FROM watchlist_bt_param_grid
WHERE policy_code = 'WS'
  AND catalog_code = 'WS_BT_GRID_REAL_IS_REMEDIATION_C171_R1_2026_07'
ORDER BY row_code;
```

Expected row count: `5` and one catalog hash:

```text
82b0fcbf17823fda5ab59bd2dba3d947b4f9e233
```

Verify DRAFTs and bindings:

```sql
SELECT
    param_set_id,
    status,
    params_hash,
    CAST(JSON_UNQUOTE(JSON_EXTRACT(provenance_json, '$.bt_binding.bt_param_id')) AS UNSIGNED) AS bt_param_id,
    JSON_UNQUOTE(JSON_EXTRACT(provenance_json, '$.bt_binding.catalog_code')) AS catalog_code,
    JSON_UNQUOTE(JSON_EXTRACT(provenance_json, '$.bt_binding.row_code')) AS row_code,
    implementation_version,
    created_at
FROM watchlist_param_sets
WHERE policy_code = 'WS'
  AND JSON_UNQUOTE(JSON_EXTRACT(provenance_json, '$.bt_binding.catalog_code'))
      = 'WS_BT_GRID_REAL_IS_REMEDIATION_C171_R1_2026_07'
ORDER BY param_set_id;
```

Expected:

```text
row_count=5
status=DRAFT for every row
five distinct params_hash values
five distinct bt_param_id values
```

The expected identities are emitted by the repaired command from the exact
verified database source payload. Read them from the summary artifact:

```powershell
$catalog = Get-Content `
  storage/app/watchlist/backtest/c171-remediation-draft-catalog.json `
  -Raw | ConvertFrom-Json

$catalog.candidate_hash_contract
$catalog.candidate_hash_manifest_hash
$catalog.candidate_hash_manifest | Format-Table row_code, params_hash
$catalog.drafts | Format-Table row_code, param_set_id, params_hash, persistence_status
```

Required contract:

```text
candidate_hash_contract=DERIVED_FROM_IMMUTABLE_SOURCE_CANONICAL_PAYLOAD_AND_CATALOG_ROW
candidate_hash_manifest_count=5
all candidate params_hash values are distinct
manifest params_hash equals persisted DRAFT params_hash for every row
```

The known first recovered identity is:

```text
C171_DRAFT_A_BROAD_MODERATE_SCORE_CAP=ffd50a2cf482558d6a5582f8479accd9b3bf62c8
```

The remaining four hashes must come from the successful repaired artifact, not
from the standalone documentation fixture.

Verify forbidden writes did not occur:

```sql
SELECT COUNT(*) AS active_count
FROM watchlist_param_sets
WHERE policy_code = 'WS' AND status = 'ACTIVE';

SELECT COUNT(*) AS oos_count
FROM watchlist_bt_oos_eval_ws;
```

The command must not increase either count and must not create PLAN rows.

## 7. Artifact hashes

```powershell
Get-ChildItem storage/app/watchlist/backtest/c171-remediation-draft-catalog* -Recurse |
  Where-Object { -not $_.PSIsContainer } |
  Get-FileHash -Algorithm SHA1 |
  Select-Object Hash, Path
```

Preserve the summary JSON and all five canonical JSON files as operator
evidence.

## Stop boundary

After successful persistence, stop. Do not promote a DRAFT and do not run OOS.
The next separate stage runs official IS for each exact `param_set_id` on the
canonical IS window. C172 remains forbidden until one immutable candidate passes
all canonical gates.

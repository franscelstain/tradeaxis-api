# Legacy Role Extract — R2 — EVIDENCE

> **Document Type:** EVIDENCE
> **Status:** HISTORICAL_EXTRACT / IMMUTABLE
> **Legacy Extract ID:** `LX-WS-0604-EVD-01`
> **Legacy Source ID:** `LS-WS-0604`
> **Legacy Work Key:** `R2`
> **Original Path:** `docs/watchlist/system/policies/weekly_swing/_refs/WS_R2_ENTRY_QUALITY_CALIBRATION_NOTE.md`
> **Original SHA1:** `74783FDEA5FEA8C3F39255D52386E24E31018678`
> **Source Sections:** L17-L31 Preserved R1 Evidence; L109-L114 Evidence Artifact Contract; L133-L199 Supported Operator Final Evidence
> **Extract Body SHA1:** `6369EFB2E8DAEE9B67CF9FD40858F90BA912FC09`
> **Current Authority:** NO

The body below is an exact copy of the identified original sections. No semantic rewriting was applied.

---

## Preserved R1 Evidence

```text
catalog_code=WS_BT_GRID_BOOTSTRAP_2026_06
catalog_version=R1
catalog_count=24
catalog_hash=9da8b0983c57bde1ce0a1fbf1c119756f8af431c
R1 runtime artifact hash=f4ec8464f08515b31d7d26636851acea930307d6
R1 valid IS rows=0
R1 failed IS rows=24
R1 OOS executed=false
```

`WatchlistBacktestParamGridCatalog::rows()` and its historical SHA1 remain unchanged. R1 persistence enrichment adds catalog metadata and explicit companion defaults without altering any historical R1 runtime row. A direct compatibility harness compared all 24 enriched R1 rows against the original ZIP factory output and passed `24/24`; a second harness compared the original and updated R1 IS-calibration service outputs and passed exact equality.

## Evidence Artifact Contract

The IS-only artifact includes catalog/date/lineage hashes, all evaluations, canonical gate results, optional valid best binding, R1 snapshot equality, OOS table before/after hashes, maximum requested market-data date, and `production_ready=false`.

A best binding is emitted only if at least one row passes every unchanged canonical gate in file 16. No valid row means `WS_BT_R2_NO_VALID_IS_CANDIDATE`, non-zero command exit, and `best_is_binding=null`.

## Supported Operator Final Evidence

The supported operator environment executed the required PHPUnit, migration, seed, and two-run IS calibration sequence.

Validation:

```text
WatchlistBacktestR2ParamGridParamsetFactoryTest: PASS / 12 tests / 106 assertions
WatchlistBacktestR2StaticGuardTest: PASS / 5 tests / 53 assertions
WatchlistBacktestOosPersistenceTest: PASS / 3 tests / 13 assertions
WatchlistBacktestR2: PASS / 26 tests / 530 assertions
WatchlistBacktestOos: PASS / 24 tests / 228 assertions
WatchlistBacktest: PASS / 117 tests / 2442 assertions
Full Watchlist: PASS / 209 tests / 3330 assertions
```

Migration/seed:

```text
migration=2026_06_10_000001_add_watchlist_backtest_catalog_identity_and_r2_entry_quality
migration_status=Yes
migration_batch=10
R2 seed run 1: inserted_count=12, updated_count=0, existing_count=0, exit_code=0
R2 seed run 2: inserted_count=0, updated_count=0, existing_count=12, exit_code=0
R1 immutable proof: r1_immutable=1 on both seed runs
```

R1/R2 coexistence:

```text
R1 catalog=WS_BT_GRID_BOOTSTRAP_2026_06 / version=R1 / count=24 / hash=9da8b0983c57bde1ce0a1fbf1c119756f8af431c
R2 catalog=WS_BT_GRID_ENTRY_QUALITY_R2_2026_06 / version=R2 / count=12 / hash=0f2eaadaa446980a3d5e48cd498df2a8157c01a5
```

Two-run R2 IS result:

```text
status=R2_GRID_FAILED_IS_QUALITY
reason_code=WS_BT_R2_NO_VALID_IS_CANDIDATE
is_window=2023-01-02..2025-05-21
is_trading_date_count=562
is_trading_date_hash=581dd450ebcbd56cb3a1c066c9fc80bbccb3a753
is_valid_param_count=0
is_failed_param_count=12
failure_codes=WS_BT_EVAL_DOWNSIDE_FAIL,WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_STABILITY_FAIL
artifact_hash=8a8521fc9a3726d90f2b77506532a1e5392def8b
```

No-OOS proof:

```text
max_requested_market_data_date=2025-05-21
strict_is_boundary_all_evaluations=1
oos_service_invoked=0
oos_repository_invoked=0
oos_table_unchanged=1
oos_executed=0
```

Final eligibility:

```text
OOS_PROOF_ELIGIBILITY=NOT_ELIGIBLE_FOR_OOS_PROOF — no valid R2 IS parameter
PROMOTION_ELIGIBILITY=NOT_ELIGIBLE — OOS proof missing
PRODUCTION_READY=false
```

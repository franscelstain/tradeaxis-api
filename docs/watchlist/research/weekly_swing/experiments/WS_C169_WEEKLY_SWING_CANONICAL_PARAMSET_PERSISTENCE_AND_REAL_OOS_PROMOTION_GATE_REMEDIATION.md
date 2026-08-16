# C169 Weekly Swing Canonical Paramset Persistence and Real OOS Promotion Gate Remediation

## Purpose

C169 replaces declaration-only production binding with executable persistence and promotion controls. The session does not create an ACTIVE configuration merely because a catalog artifact claims PASS. Promotion is allowed only from a valid DRAFT whose exact backtest-grid binding and exact official persisted IS/OOS rows pass the owner gates.

The mandatory order is:

```text
validated paramset JSON
  -> exact watchlist_bt_param_grid binding
  -> immutable DRAFT persistence
  -> exact official watchlist_bt_eval evidence
  -> exact official watchlist_bt_oos_eval_ws evidence
  -> promotion gate
  -> ACTIVE only if every gate passes
  -> canonical PLAN may run only after ACTIVE exists
```

## Parity Audit Correction

The audit performed before implementation established these runtime facts:

- the five Watchlist core paramset/PLAN tables did not yet exist;
- `watchlist_bt_param_grid` contained 156 rows and `watchlist_bt_eval` contained 186 rows;
- `watchlist_bt_oos_eval_ws` contained zero rows;
- the prior official OOS runtime artifact `storage/app/watchlist/backtest/oos-proof-run-1.json` reported 24 grid rows, zero valid IS candidates, and no OOS id;
- the latest actual IS evaluation for R1 `param_id=1` failed the canonical IS quality gates;
- C64's `oosScorecardFor()` derives OOS-looking metrics from IS/default scenario inputs and hard-coded sample values; it does not read `watchlist_bt_oos_eval_ws`.

Therefore C61/C64 E02/B01 declarations are not accepted as canonical promotion evidence. C167 remains incomplete.

## Implemented Components

### Core migration

`database/migrations/2026_07_24_000001_create_watchlist_runtime_paramset_and_plan_schema.php` creates:

- `watchlist_fail_codes`;
- `watchlist_reason_codes`;
- `watchlist_param_sets`;
- `watchlist_plan_runs`;
- `watchlist_plan_items`.

For MySQL, four triggers enforce append-only PLAN evidence:

- `trg_wpr_guard_update`;
- `trg_wpr_no_delete`;
- `trg_wpi_no_update`;
- `trg_wpi_no_delete`.

The CONFIRM tables remain outside this migration because C169 does not execute CONFIRM. No physical recommendation table was invented because the owner documents do not yet define one.

### Paramset validator

`WeeklySwingParamsetValidator` enforces:

- exact root and section keys;
- complete audit objects;
- allowed types, origins, statuses, enums, and ranges;
- canonical bootstrap evaluation floors of 120 IS trades and 40 OOS trades;
- fixed sort/hash rules;
- deterministic object-key canonicalization while preserving semantic array order;
- canonical stable hash calculation.

The support example now validates with:

```text
PARAM_SET_HASH=b7f3c207b989c55c93f8f61b1fcceea2c343a151
```

### Exact DRAFT import

`watchlist:weekly-swing-paramset-import-draft` validates the JSON, verifies every mapped strategy value against one exact `watchlist_bt_param_grid` row, and persists an idempotent DRAFT. It never promotes.

Executed database result:

```text
status=DRAFT_PERSISTED
reason_code=WS_PARAMSET_DRAFT_PERSISTED
param_set_id=1
paramset_status=DRAFT
bt_param_id=1
catalog_code=WS_BT_GRID_BOOTSTRAP_2026_06
catalog_version=R1
catalog_hash=9da8b0983c57bde1ce0a1fbf1c119756f8af431c
row_code=01_BASELINE
row_hash=d0ad2af3a061c42e879922e621ed265b17cb8847
production_ready=0
```

Re-executing the same import preserved one exact DRAFT row.
If the exact payload already exists as ACTIVE or DEPRECATED, the DRAFT import now stops with `WS_PARAMSET_DRAFT_IMPORT_STATUS_CONFLICT` instead of misreporting that row as a DRAFT.

### Real promotion gate

`watchlist:weekly-swing-paramset-promote` requires:

- one exact DRAFT id;
- the exact bound backtest grid id;
- one exact official OOS id;
- revalidation of persisted paramset JSON;
- matching immutable provenance;
- a reverified, unchanged catalog version/hash and row code/hash;
- passing official persisted IS metrics;
- an IS coverage floor resolved as `ceil(0.70 * total_trading_days_in_window)` when the configured value is the canonical zero sentinel;
- passing official persisted OOS metrics.

The command uses a database lock and performs any ACTIVE transition transactionally. It cannot use C64 artifacts or in-memory/synthesized scorecards as proof.

Executed database result:

```text
status=BLOCKED
reason_code=WS_PARAMSET_PROMOTION_OOS_PROOF_MISSING
production_ready=0
```

Final state:

```text
WATCHLIST_BT_OOS_EVAL_WS_ROW_COUNT=0
PARAMSET_DRAFT_COUNT=1
PARAMSET_ACTIVE_COUNT=0
PARAM_SET_POLICY_VERSION=WS_EOD_RUNTIME
PARAM_SET_SCHEMA_VERSION=PARAMSET_JSON
PLAN_RUN_COUNT=0
PLAN_ITEM_COUNT=0
PRODUCTION_RUNTIME_ACTIVATED=0
```

This BLOCKED result is the expected proof that the gate is real.

## Validation Log

```text
PHP_LINT_CHANGED_WATCHLIST_FILES=PASS
MIGRATION_STATUS=RAN_BATCH_14
REGISTERED_WEEKLY_SWING_COMMAND_COUNT=3
PHPUNIT_C169_FOCUSED=OK (22 tests, 106 assertions)
PHPUNIT_C168_RUNTIME=OK (10 tests, 116 assertions)
PHPUNIT_C168_C169_INTEGRATION_REGRESSION=OK (143 tests, 1990 assertions)
FULL_WATCHLIST_PHPUNIT=OK (7064 tests, 47699 assertions)
GIT_DIFF_CHECK=PASS
```

The final full suite ran for 2 minutes 58.034 seconds with 422 MB peak memory.

## C169 Boundary

```text
REAL_CORE_SCHEMA_CREATED=1
REAL_PARAMSET_VALIDATION_EXECUTED=1
REAL_DRAFT_PERSISTENCE_EXECUTED=1
REAL_BT_GRID_BINDING_EXECUTED=1
REAL_PROMOTION_GATE_EXECUTED=1
REAL_OFFICIAL_OOS_PROOF_AVAILABLE=0
REAL_ACTIVE_PARAMSET_CREATED=0
REAL_PLAN_PERSISTENCE_EXECUTED=0
REAL_RECOMMENDATION_PERSISTENCE_EXECUTED=0
REAL_CONFIRM_MUTATION_EXECUTED=0
REAL_ROLLOUT_EXECUTED=0
OFFICIAL_OUTPUT_PUBLISHED=0
WATCHLIST_PRODUCTION_READY=NO
```

## C170 Continuation Result

C170 followed the planned order but stopped before OOS. The presumed C28 G05 IS candidate was not execution-eligible: its R09/G21/G16 route is chosen from a bucket derived from the evaluated D1-D5 path. Revalidation produced 1,575 routing/lookahead violations and no immutable IS-passing candidate.

C170 also proved that the official support tables are empty and cannot be tied to an exact IS evaluation because `watchlist_bt_picks_ws`, `watchlist_bt_universe_ws`, and `watchlist_bt_cutoffs_ws` do not contain `eval_id`.

Therefore steps 3-4 above were not legally reachable. No OOS was read again, no official OOS row was inserted, and the DRAFT was not promoted.

The corrected next session is:

```text
C171_WEEKLY_SWING_VERSIONED_OFFICIAL_BACKTEST_EVIDENCE_AND_EXECUTABLE_IS_STRATEGY_REMEDIATION
```

C171 must version the three support-evidence tables by exact `eval_id`, persist real picks/universe/cutoffs from the canonical runtime, and design/recalibrate an IS-only rule whose routing inputs are available at execution time. OOS remains forbidden until one exact candidate passes all canonical IS gates and the complete official evidence manifest.

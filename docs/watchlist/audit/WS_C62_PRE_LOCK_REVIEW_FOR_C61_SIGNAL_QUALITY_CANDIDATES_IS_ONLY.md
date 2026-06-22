# WS C62 — Pre-Lock Review For C61 Signal Quality Candidates IS-Only

Status: `IMPLEMENTED_PENDING_OPERATOR_VALIDATION`

C62 is an IS-only pre-lock review session. It starts from locked C61 evidence and retains C60 lineage evidence. It does not redesign broadly, does not run OOS, does not unlock OOS proof, does not create a production catalog, and does not mutate PLAN/CONFIRM.

## Scope

```text
RUN_CODE=C62_PRE_LOCK_REVIEW_FOR_C61_SIGNAL_QUALITY_CANDIDATES_IS_ONLY
IS_PERIOD=2023-01-02..2025-05-21
OOS_RESERVED=2025-05-22..2026-05-29
SOURCE_C61_ARTIFACT=storage/app/watchlist/backtest/c61-signal-quality-rebuild-for-weak-regime-is-only.json
SOURCE_C60_ARTIFACT=storage/app/watchlist/backtest/c60-regime-stress-and-loo-dependency-redesign-is-only.json
PRODUCTION_READY=false
DIRECT_OOS_PROOF_RECOMMENDED=false
OOS_PROOF_UNLOCKED=false
PRE_OOS_UNLOCKED=false
```

## Locked Evidence

C62 validates these locks before continuing:

```text
EXPECTED_C61_HASH=40d2c4a4f9f1310f9165cdfb4abdd45ff94cb0c8
EXPECTED_C61_FILE_SHA1=DEA3C807813DE81DB6776AB2C441C945D4E98EC6
EXPECTED_C60_HASH=25a32ee9c4cb77ecc29103c86a1abf0826aea705
EXPECTED_C60_FILE_SHA1=1FA933157B61ECB4554CE6C76B0F2B314F19DB0F
```

If any lock mismatches, C62 blocks before review.

## Reviewed Candidates

C62 reviews only the three C61 candidates with `candidate_ready_for_c62=true`:

```text
PRIMARY_UNDER_REVIEW=C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE
SIBLING_BACKUP_UNDER_REVIEW=C61_A01_B01_WEAK_REGIME_QUALITY_FIRST
PARENT_DIVERSIFIER_UNDER_REVIEW=C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION
```

No new candidate is created. No replay comparator is promoted.

## Mandatory Database Dictionary Rule

C62 records the mandatory database dictionary read rule and checks these dictionary paths:

```text
docs/market_data/db/MARKET_DATA_DICTIONARY.md
docs/db/DATABASE_DICTIONARY_USAGE_RULE.md
docs/market_data/db/Database_Schema_MariaDB.sql
docs/market_data/db/Database_Schema_Contracts_MariaDB.md
docs/market_data/db/DB_FIELDS_AND_METADATA.md
docs/watchlist/system/db/WATCHLIST_DB_DICTIONARY.md
```

C62 records table/field mappings for `market_calendar`, `market_benchmark_indicators`, `eod_indicators`, `eod_bars`, and the watchlist backtest artifact read model. It keeps all lookups as-of safe and records zero OOS rows requested.

## Required Audits

C62 audits:

- `month_win_rate_min=0`
- bad-month exposure
- month dependency
- leave-one-month-out stability
- rolling stability
- weak-regime survival in `market_down_or_sideways_high_vol`
- regime robustness
- concentration and loss-cluster retention
- sample recovery and weak-regime sample recovery
- material selection difference
- anti-shared-core behavior
- source bias
- as-of and OOS safety
- candidate hierarchy

Bad months and weak regimes are not removed. Ticker/sector hard exclusions are not used.

## Runtime Artifact

C62 writes:

```text
storage/app/watchlist/backtest/c62-pre-lock-review-for-c61-signal-quality-candidates-is-only.json
```

The artifact includes lock summaries, dictionary summary, candidate scorecard, month/bad-month audit results, weak-regime survival revalidation, regime robustness revalidation, concentration and loss-cluster revalidation, rolling and LOO recheck summaries, material-difference and anti-shared-core summaries, source-bias validation, safety/leakage audit, pre-lock decision, C63 readiness decision, and diagnostics.

## Expected Decision Semantics

If one or more candidates pass all C62 IS-only gates, C62 may mark them only as ready for C63/pre-OOS-unlock review:

```text
C63_PRE_OOS_UNLOCK_REVIEW_IS_ONLY
```

This is not OOS proof and not production readiness.

If no candidate passes, C62 must report the dominant blocker and recommend an IS-only continuation such as month dependency repair, bad-month exposure review, weak-regime pre-lock repair, source-bias reduction, or shared-core reduction.

## Safety Statement

C62 does not authorize:

```text
OOS proof
pre-OOS unlock
production catalog
PLAN/CONFIRM mutation
return/future-path/OOS-return selection
bad-month removal
weak-regime removal
ticker/sector hard exclusion from failure attribution
```

# Legacy Role Extract — R2 — RESEARCH

> **Document Type:** RESEARCH
> **Status:** HISTORICAL_EXTRACT / IMMUTABLE
> **Legacy Extract ID:** `LX-WS-0604-RES-03`
> **Legacy Source ID:** `LS-WS-0604`
> **Legacy Work Key:** `R2`
> **Original Path:** `docs/watchlist/system/policies/weekly_swing/_refs/WS_R2_ENTRY_QUALITY_CALIBRATION_NOTE.md`
> **Original SHA1:** `74783FDEA5FEA8C3F39255D52386E24E31018678`
> **Source Sections:** L3-L16 Status; L32-L60 R2 Immutable Catalog Manifest; L74-L85 Fixed Execution Snapshot; L95-L108 Strict IS and No-OOS Design; L115-L132 Validation Performed in Packaging Environment; L200-L215 Required Operator Validation; L216-L245 Catalog Naming and Next-Session Rule
> **Extract Body SHA1:** `75F1C3C230F6263883D487D9B3BC796ECF8DED5F`
> **Current Authority:** NO

The body below is an exact copy of the identified original sections. No semantic rewriting was applied.

---

## Status

```text
LAST_UPDATED=2026-06-10
IMPLEMENTATION_SCOPE=DONE
SUPPORTED_OPERATOR_IS_RUNTIME_EXECUTED
LOCAL_R2_IS_CALIBRATION_EXECUTED
R2_GRID_FAILED_IS_QUALITY
OOS_NOT_READ
NOT_PRODUCTION_READY
```

This note records implementation and validation evidence for the separate R2 IS-only calibration session. It is a reference/evidence note, not the owner of behavior. Normative ownership remains in files 04–07, 12–13, 16–18, and 20.

## R2 Immutable Catalog Manifest

```text
catalog_code=WS_BT_GRID_ENTRY_QUALITY_R2_2026_06
catalog_version=R2
catalog_count=12
catalog_hash=0f2eaadaa446980a3d5e48cd498df2a8157c01a5
control_row=00_R1_BASELINE_CONTROL
search_mode=CURATED_DETERMINISTIC
random_or_bayesian=false
```

Rows:

1. `00_R1_BASELINE_CONTROL`
2. `01_BALANCED_ENTRY_FILTER`
3. `02_LIQUID_MOMENTUM`
4. `03_VOLUME_BREAKOUT_CONFIRM`
5. `04_LOW_VOLATILITY_QUALITY`
6. `05_RISK_WEIGHTED_ENTRY`
7. `06_NEAR_BREAKOUT_ONLY`
8. `07_STRONG_PARTICIPATION`
9. `08_MOMENTUM_NOT_EXTENDED`
10. `09_HIGH_LIQ_BALANCED_STRICT`
11. `10_DEFENSIVE_ENTRY`
12. `11_CONCENTRATED_ENTRY_QUALITY`

The catalog is frozen in code before operator runtime. Any change after first execution requires a new catalog/version, not an edit to R2. The runtime artifact embeds all curated row values/rationales, the 19 owner-linked axis rationales, and provenance markers showing registry + R1 IS evidence + deterministic engineering rationale with `oos_used=false`.

## Fixed Execution Snapshot

```text
ENTRY=NEXT_OPEN;EXIT=STOP_TP_OR_TIME;HOLD=5;FEE=IDR_FIXED;SLIP=0;GAP=OPEN;PX=IDX_BANDS
risk.stop_atr_mult=1.50
risk.min_rr=1.50
grouping.top_picks_target=5
grouping.secondary_target=10
```

The grouping count targets are frozen because they are not R2 `bt_target` axes. No exit-axis search, fee/slippage change, gap-fill change, price-band change, or holding-horizon change is part of R2.

## Strict IS and No-OOS Design

The command accepts only:

```text
catalog=WS_BT_GRID_ENTRY_QUALITY_R2_2026_06
from=2023-01-02
to=2025-05-21
```

The runtime receives `hard_market_data_to_date=2025-05-21`. The final five IS dates cannot start new trades, so their required exits never cross into reserved OOS. Calendar/publication/OHLCV reads remain bounded to the explicit IS window.

The R2 execution path has no dependency on `WatchlistBacktestOosProofService` or `WatchlistBacktestOosEvaluationRepository`. It snapshots `watchlist_bt_oos_eval_ws` before/after for mutation proof but does not insert/update/delete that table.

## Validation Performed in Packaging Environment

```text
PHP syntax lint: PASS / 312 PHP files
R2 pure-PHP smoke harness: PASS / 180 assertions
R1 factory compatibility: PASS / 24 of 24 rows
R1 IS-calibration service compatibility: PASS / exact output equality
R1 catalog source hash: 9da8b0983c57bde1ce0a1fbf1c119756f8af431c
R2 catalog source hash: 0f2eaadaa446980a3d5e48cd498df2a8157c01a5
R2 catalog count: 12
locked file 16 SHA1: 31299d858b68ee351ae898f4c9380d8753a65d8a
locked file 17 SHA1: 39519a391158a7b2dcf7b6e989079788d61669be
```

The smoke harness covers catalog identity/count/hash, all cross-field invariants, explicit factory projection, paramset-hash independence from database `param_id`, immutable eval idempotence/conflict behavior, no-best-of-failed, post-IS mutation invariance, and the real strict published-price path (`resolveTradingDates` only, HOLD=5 entry censoring, maximum requested date `2025-05-21`).

Official PHPUnit could not start because PHP extensions `dom`, `mbstring`, `xml`, and `xmlwriter` are unavailable. Artisan migration/seed/calibration commands fail closed before application bootstrap with `ENV_UNSUPPORTED_PHP_VERSION` because this container runs PHP `8.4.16` while the project requires PHP `>=7.3,<8.4`; no PDO database driver is installed either. Package installation was blocked by DNS resolution. No fabricated database or runtime evidence was created.

## Required Operator Validation

Operator validation is complete for R2 IS execution. Do not rerun R2 with changed catalog values. The immutable R2 catalog has already produced a failed-IS quality result.

Final status:

```text
DONE for R2 entry-quality calibration execution infrastructure
LOCAL_R2_IS_CALIBRATION_EXECUTED
R2_GRID_FAILED_IS_QUALITY
OOS_NOT_READ
NOT_PRODUCTION_READY
OOS_PROOF_ELIGIBILITY=NOT_ELIGIBLE_FOR_OOS_PROOF — no valid R2 IS parameter
PROMOTION_ELIGIBILITY=NOT_ELIGIBLE — OOS proof missing
```

## Catalog Naming and Next-Session Rule

`R1` and `R2` are retained only as immutable historical aliases and backward-compatible evidence labels. They must not become the active naming convention for future work.

Do not create `R3`, `R4`, `R5`, or later catalog identities.

Future catalog naming must be semantic:

```text
WS_BT_GRID_<FOCUS>_C##_YYYY_MM
```

Future evidence naming should distinguish run type and catalog attempt:

```text
WS_BT_IS_<FOCUS>_C##_RUN_##
WS_BT_OOS_<FOCUS>_C##_RUN_##
```

The next session must not mutate R1 or R2 and must not run OOS. It must begin with IS failure diagnostics and, only if justified, design a new semantic catalog. Recommended session title:

```text
WATCHLIST — WEEKLY SWING IS FAILURE DIAGNOSTIC AND DOWNSIDE/STABILITY C01 CATALOG DESIGN SESSION
```

Recommended catalog identity if design proceeds:

```text
WS_BT_GRID_DOWNSIDE_STABILITY_C01_2026_06
```

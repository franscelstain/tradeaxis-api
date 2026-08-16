# WS C52 — Concentration Dependency Redesign Continuation

## Purpose and boundary

C52 continues C51 as an IS-only diagnostic/redesign stage. It fixes sector metadata reconstruction first, then replays the promising C51 variants and evaluates a predeclared second pass of branch, bucket, ticker, sector, and loss-cluster controls. It does not tune on OOS, execute OOS proof, promote a candidate, create a production catalog, or mutate PLAN/RECOMMENDATION/CONFIRM behavior.

Canonical periods and execution remain locked:

```text
IS=2023-01-02..2025-05-21
OOS_RESERVED=2025-05-22..2026-05-29
ENTRY=NEXT_OPEN
EXIT=STOP_TP_OR_TIME
HOLD=5
FEE=IDR_FIXED
SLIP=0
GAP=OPEN
PX=IDX_BANDS
production_ready=false
```

## Locked source lineage

```text
input_c51_artifact=storage/app/watchlist/backtest/c51-concentration-dependency-redesign-review.json
expected_c51_hash=a786034b8e344207592e58efe262287102b0ef36
input_c50_artifact=storage/app/watchlist/backtest/c50-is-validation-anti-overfit-check.json
expected_c50_hash=1f2b919662a395444f43403e8f7f4d0b91e146aa
input_c49_artifact=storage/app/watchlist/backtest/c49-broader-strategy-redesign.json
expected_c49_hash=9266ec2b59a6ea11c21b830cd9b769635afc91a8
c51_used_as_locked_continuation_source=true
c50_used_as_locked_validation_source=true
c49_used_as_locked_candidate_source=true
```

C51 is carried forward as technically valid but strategically failed: no candidate was selected, concentration and anti-overfit validation failed, and the next step is C52 concentration/dependency continuation.

## C51 root cause and sector reconstruction

C51 selected pre-trade indicator columns without carrying `eod_indicators.sector_code`. Its sector aggregator then grouped every missing value into `UNKNOWN`, producing `unique_sector_count=0` while reporting `max_sector_share=1`. Those two values are an evaluation defect, not evidence that every pick belonged to one real sector.

C52 uses this source order:

1. `eod_indicators.sector_code` joined by exact `(trade_date, ticker_id)`;
2. `ticker_sector_memberships` effective on the same trade/signal date as a fallback;
3. `market_data_sectors` by `sector_code` for `sector_name`.

The join never uses a future `MAX(trade_date)`. Effective membership requires `effective_from <= trade_date` and `effective_to IS NULL OR effective_to >= trade_date`. Missing metadata remains not evaluable; no dummy sector is synthesized. Indicator/membership disagreements are emitted in `sector_metadata_conflict_results`.

Final runtime reconstruction result:

```text
rows_attempted=15750
rows_joined=15750
join_coverage_rate=1
sector_code_coverage_rate=1
sector_name_coverage_rate=1
unknown_sector_share=0
unique_sector_count=11
max_sector_share_after_join=0.22031746031746
conflict_count=0
sector_metadata_asof_safe=true
lookahead_guard_pass=true
sector_metadata_reconstruction_pass=true
sector_concentration_evaluable=true
```

## Source reconstruction

The candidate universe remains the C28 IS pick-diagnostic row universe used by C51, locked through C51/C50/C49 lineage. C52 enriches those rows from the exact-date pre-trade database read model. Realized return is used only after candidate membership has been deterministically locked, for IS evaluation metrics.

```text
source_mode=C28_PICK_DIAGNOSTIC_ROWS
source_is_rows=15750
source_g16_rows=1320
source_g21_rows=1770
source_g13_rows=590
source_months=27
pre_trade_source_mode=DATABASE_AS_OF_SIGNAL_DATE_JOIN_WITH_SECTOR
source_bias_validation_pass=true
oos_data_used_for_tuning=false
oos_return_used_for_selection=false
return_used_for_selection=false
future_path_used_for_selection=false
```

## Candidate definitions

C52 evaluates exactly 20 definitions:

- R00–R05 replay C51 R05/R06/R08/R09/R10/R12 after sector reconstruction.
- R06–R09 cap G16 at approximately 60% or 55%, use G21 as primary backfill, and exclude or limit G13.
- R10–R12 use predeclared branch quotas `55/30/15`, `50/35/15`, and `60/35/05`.
- R13–R14 target bucket balances `55/45` and `50/50`.
- R15 applies loss-cluster/ticker/sector exposure controls.
- R16 balances ticker, sector, and branch exposure.
- R17–R18 are sector-aware F03/F08 hybrid and stability-repair variants.
- R19 is the C44/F00 anchor comparator only and cannot be selected.

All downsampling/backfill ordering uses safe pre-trade fields and deterministic metadata order. G21 is primary backfill. G13 is limited because its C51 branch return was negative. No realized return, future path, MFE/MAE, OOS month, OOS ticker, or OOS sector participates in membership selection.

## Validation results

Sector reconstruction confirms that C51 R08, R09, R10, and R12 were concentration-pass after the invalid sector layer was fixed. R05 and R06 remain useful high-quality comparators but fail one or more tightened concentration limits. Fourteen C52 candidates pass the complete relaxed concentration layer. Examples:

```text
C52_R02 max_branch=0.4903846154 max_bucket=0.5096153846 max_sector=0.1778846154
C52_R05 max_branch=0.4888178914 max_bucket=0.5111821086 max_sector=0.1597444089
C52_R10 max_branch=0.5159574468 max_bucket=0.5159574468 max_sector=0.1808510638
C52_R16 max_branch=0.4728682171 max_bucket=0.5271317829 max_sector=0.1782945736
```

G16 remains the strongest return engine; C52 therefore caps rather than eliminates it. G21 can diversify the engine without using return-ranked backfill. G13 reduces branch share but materially depresses quality when its quota is too large. Sector caps improve concentration shape, though strict caps reduce coverage for several second-pass candidates.

Rolling, leave-one-month-out, regime, and material-difference results are emitted per candidate. The final evidence does not produce a candidate that passes the entire validation stack. The strongest rolling result among redesigned candidates is R07, but it has only 113 picks and therefore fails the predeclared 120-pick coverage floor. Several concentration-pass candidates fail rolling validation even when LOO/regime/material-difference layers are acceptable.

## Scorecard and C53 readiness

```text
sector_metadata_reconstruction_pass=true
concentration_pass_candidate_count=14
best_redesigned_candidate_code=null
selected_candidate_count=0
best_redesigned_candidate_pass=false
anti_overfit_pass=false
diagnostic_conclusion=C52_EVIDENCE_EXPANSION_REQUIRED
next_step_recommendation=C53_IS_EVIDENCE_EXPANSION_FOR_C52_REDESIGN
direct_oos_proof_recommended=false
oos_proof_unlocked=false
production_ready=false
```

The C53 decision is IS evidence expansion, not OOS proof. C52 establishes that the sector layer is valid and that branch/bucket/sector concentration can be reduced mechanically without eliminating G16, but it does not establish a complete anti-overfit pass.

## Candidate safety audit and not-evaluable policy

Every candidate receives selection-rule, sector-as-of, OOS-boundary, and production-boundary checks. Artifact JSON uses lowercase snake_case safety keys with no case-insensitive duplicates. Missing regime fields are recorded as not evaluable; sector metadata that falls below the coverage/diversity floor yields `C52_SECTOR_METADATA_NOT_EVALUABLE`, `max_sector_share=null`, and never a fabricated single-sector failure.

## Runtime result

```text
C52_IMPLEMENTATION_STATUS=IMPLEMENTED_FINAL
C52_PHPUNIT_STATUS=PASS — OK (10 tests, 665 assertions)
FULL_WATCHLIST_PHPUNIT_STATUS=PASS — OK (759 tests, 14908 assertions)
C52_ARTISAN_RUNTIME_STATUS=COMPLETED
status=C52_CONCENTRATION_DEPENDENCY_REDESIGN_CONTINUATION_COMPLETED
artifact_path=storage/app/watchlist/backtest/c52-concentration-dependency-redesign-continuation.json
artifact_hash=5dbe51c9d18b175e65cddb60336baf43d6833b72
file_sha1=DADE6518BFF3912D8A43D7C67073FB803F7CF878
diagnostic_conclusion=C52_EVIDENCE_EXPANSION_REQUIRED
next_step_recommendation=C53_IS_EVIDENCE_EXPANSION_FOR_C52_REDESIGN
production_ready=false
```

C52 did not run OOS proof, tune on OOS, create a production catalog, promote a candidate, or claim OOS recovery.

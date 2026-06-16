# WS C17 Quality-Preserving Sample Recovery Design Result

## Scope

C17 continues from the final C16 result. C16 is immutable, runtime-validated, seed-validated, diagnose-batch validated, and deterministic, but failed IS strategy-quality gates with zero valid candidates. C17 is therefore a new source-level catalog design, not a mutation or promotion of C16.

## Initial audit result

Score: `86/100`.

Verdict: `READY_FOR_C17_SOURCE_IMPLEMENTATION_WITH_OPERATOR_RUNTIME_VALIDATION_REQUIRED`.

| Area | Status | Notes |
| --- | --- | --- |
| Scope | PASS | Watchlist weekly swing only. No broker/order/execution behavior added. |
| Policy | PASS | PLAN/RECOMMENDATION/CONFIRM boundary unchanged. |
| Boundary | PASS | OOS remains forbidden until valid IS candidate exists. |
| C16 final evidence | PASS | C16 final evidence is present in audit docs and explicit user-provided baseline. |
| Immutability | PASS | C17 is new catalog/version/code and C16 hash remains `0ad1289f79d78787cdca275f0b3f3e2ba90bf8f2`. |
| OOS | PASS | No OOS path added or invoked. |
| Production readiness | PASS | `production_ready=0`. |
| Runtime readiness | SOURCE PASS / RUNTIME NOT_RUN | Runtime mode is wired into factory, CandidateUniverse, Scoring, and PlanGrouping; operator must run PHPUnit/Artisan. |
| Docs readiness | PASS | C17 design, commands, policy note, status, contract tracker, and source artifact are updated. |

## C17 identity

```text
C17_CATALOG_CODE=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06
C17_CATALOG_VERSION=C17
C17_CATALOG_COUNT=12
C17_CATALOG_HASH=d411bfbee6fb14c17d821aa92e7e0fea06925d67
C17_RUNTIME_EXTENSION_MODE=C17_QUALITY_PRESERVING_SAMPLE_RECOVERY_FROM_C16
C17_WORKING_CONCEPT=C17_QUALITY_PRESERVING_SAMPLE_RECOVERY_FROM_C16
```

## Design summary

C17 targets C16's actual blockers: low sample count and failed monthly stability. It does not lower canonical gates. It recovers sample only through segmented, runtime-consumed entry-quality expansion.

C17 uses these C16 rows as diagnostic anchors only:

| C16 param_id | C16 row_code | C17 usage |
| ---: | --- | --- |
| 140 | `07_ONE_R_TARGET_MID_DV20` | one-R target and mid-DV20 sample-recovery direction |
| 134 | `01_STRICT_CORE_NEGATIVE_ROC20` | negative ROC20 cooling direction |
| 143 | `10_NEGATIVE_ROC20_ONE_R_TIGHT` | negative ROC20 plus one-R target direction |
| 137 | `04_DV20_TO_6B_STRICT_SCORE_WINDOW` | DV20 recovery to 6B direction |
| 141 | `08_DV20_TO_7_5B_STRICT_RECOVERY` | upper liquidity recovery direction |

None of these failed C16 rows is promoted as a binding parameter.

## Runtime-consumed C17 guard

Runtime mode:

```text
C17_QUALITY_PRESERVING_SAMPLE_RECOVERY_FROM_C16
```

Runtime checks include:

```text
DV20 between catalog min and strong
volume ratio between catalog min and strong
ATR14 between catalog min and max
ROC20 between catalog roc_lo and roc_hi
ROC5 between -0.020 and 0.000
row-specific score window from score_windows_by_row_code
score chase 0.90..1.00 blocked
score component minimums
score component pass count
score component average minimum
trend metric floor pass count
```

The new mode is consumed in:

```text
WatchlistBacktestParamGridParamsetFactory
WatchlistCandidateUniverseService
WatchlistScoringService
WatchlistPlanGroupingService
```

## C17 row families

C17 has 12 deterministic rows:

```text
00_C16_140_SCORE_65_80_MID_DV20_ONE_R
01_NEG_ROC20_SCORE_65_80_DV20_2B_6B
02_NEG_ROC20_ONE_R_SCORE_68_82
03_SCORE_70_85_LOW_ATR_NEG_ROC20
04_DV20_2B_6B_CONTROLLED_PULLBACK
05_DV20_25_75_SCORE_68_82
06_VOL_150_250_LOW_ATR_NEG_ROC20
07_VOL_150_250_ONE_R_LOW_ATR
08_SCORE_70_85_DV20_2B_6B_ROC20_COOLING
09_MID_DV20_LOWER_VOLUME_GUARDED
10_C16_134_DERIVED_NEG_ROC20_SCORE_68_82
11_C16_143_DERIVED_ONE_R_SCORE_70_85
```

Score windows are row-specific and runtime-enforced:

```text
0.65..0.80
0.68..0.82
0.70..0.85
```

C17 deliberately does not open a free `0.80..0.90` bucket. Rows reaching above `0.80` are segmented by low ATR, negative ROC20, DV20, and volume bounds. Score chase `0.90..1.00` remains blocked.

## Explicit non-goals

```text
C16_MUTATION_ALLOWED=false
C16_PROMOTION_ALLOWED=false
BEST_OF_FAILED_BINDING_ALLOWED=false
TICKER_BLACKLIST_ALLOWED=false
MONTH_BLACKLIST_ALLOWED=false
SECTOR_WHITELIST_ALLOWED=false
OOS_ALLOWED=false until valid IS candidate exists
PRODUCTION_READY_ALLOWED=false
```

## Source-level validation result

Actually run in this environment:

```text
php -l selected C17 and touched PHP files: PASS (15 files)
C17_SOURCE_SMOKE=PASS
C17_PLAN_GROUPING_SMOKE=PASS
C17_CATALOG_HASH=d411bfbee6fb14c17d821aa92e7e0fea06925d67
C17_CATALOG_COUNT=12
C17_RUNTIME_MODE=C17_QUALITY_PRESERVING_SAMPLE_RECOVERY_FROM_C16
C16_CATALOG_HASH=0ad1289f79d78787cdca275f0b3f3e2ba90bf8f2
OOS_EXECUTED=0
production_ready=0
```

Not run in this environment:

```text
PHPUnit C17: NOT_RUN / BLOCKED - missing PHP extensions dom, mbstring, xml, xmlwriter
Full Watchlist PHPUnit: NOT_RUN / BLOCKED - missing PHP extensions dom, mbstring, xml, xmlwriter
Seed C17: NOT_RUN / BLOCKED - PHP 8.4.16 unsupported by Lumen command guard
Diagnose-batch C17: NOT_RUN / BLOCKED - PHP 8.4.16 unsupported by Lumen command guard
IS calibration C17 run-1/run-2: NOT_RUN / BLOCKED - PHP 8.4.16 unsupported by Lumen command guard
```

## Final source-level status

```text
C17_IMPLEMENTED_SOURCE_LEVEL=true
C17_RUNTIME_MODE_IMPLEMENTED_SOURCE_LEVEL=true
C17_CATALOG_CODE=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06
C17_CATALOG_HASH=d411bfbee6fb14c17d821aa92e7e0fea06925d67
C17_CATALOG_COUNT=12
C16_UNCHANGED=true
C01_TO_C16_IMMUTABLE=true
WATCHLIST_SCOPE_ONLY=true
PLAN_RECOMMENDATION_CONFIRM_BOUNDARY_UNCHANGED=true
OOS_NOT_RUN=true
production_ready=0
OPERATOR_VALIDATION_REQUIRED=true
```

## Final operator validation evidence - 2026-06-16

C17 is now runtime validated by operator output and closed as strategy-quality failed.

Operator validation summary:

```text
PHPUnit C17: PASS — 11 tests, 579 assertions
Full Watchlist PHPUnit: PASS — 366 tests, 8956 assertions
Seed C17: PASS — catalog_count=12, catalog_hash=d411bfbee6fb14c17d821aa92e7e0fea06925d67, inserted_count=12, updated_count=0, existing_count=0
Diagnose-batch C17: PASS — diagnostic_param_count=12, ready_count=12, blocked_count=0
IS calibration run-1: C17_GRID_FAILED_IS_QUALITY — artifact_hash=23c30d70aeefa88701de8d9a59dd9217ee340ae6
IS calibration run-2: C17_GRID_FAILED_IS_QUALITY — artifact_hash=23c30d70aeefa88701de8d9a59dd9217ee340ae6
C17_IS_CALIBRATION_DETERMINISTIC=true
OOS_NOT_RUN=true
production_ready=0
```

Final gate summary:

| Reason code | Count |
| --- | ---: |
| `WS_BT_EVAL_MIN_TRADES_FAIL` | 12 |
| `WS_BT_EVAL_ROBUST_RETURN_FAIL` | 5 |
| `WS_BT_EVAL_STABILITY_FAIL` | 12 |

Final ranked IS metrics by average return:

| Param | Row | Picks | Avg net | Median net | P25 net | Win rate | Worst month avg | Failures |
| ---: | --- | ---: | ---: | ---: | ---: | ---: | ---: | --- |
| 149 | `04_DV20_2B_6B_CONTROLLED_PULLBACK` | 35 | 0.008152 | 0.012427 | -0.012497 | 65.71% | -0.029619 | `WS_BT_EVAL_MIN_TRADES_FAIL,WS_BT_EVAL_STABILITY_FAIL` |
| 154 | `09_MID_DV20_LOWER_VOLUME_GUARDED` | 16 | 0.007882 | 0.013650 | -0.002502 | 62.50% | -0.022407 | `WS_BT_EVAL_MIN_TRADES_FAIL,WS_BT_EVAL_STABILITY_FAIL` |
| 145 | `00_C16_140_SCORE_65_80_MID_DV20_ONE_R` | 35 | 0.007509 | 0.009399 | -0.008772 | 57.14% | -0.033502 | `WS_BT_EVAL_MIN_TRADES_FAIL,WS_BT_EVAL_STABILITY_FAIL` |
| 148 | `03_SCORE_70_85_LOW_ATR_NEG_ROC20` | 28 | 0.005751 | 0.010993 | -0.000500 | 67.86% | -0.038226 | `WS_BT_EVAL_MIN_TRADES_FAIL,WS_BT_EVAL_STABILITY_FAIL` |
| 150 | `05_DV20_25_75_SCORE_68_82` | 42 | 0.004921 | 0.010993 | -0.016792 | 54.76% | -0.033550 | `WS_BT_EVAL_MIN_TRADES_FAIL,WS_BT_EVAL_STABILITY_FAIL` |
| 152 | `07_VOL_150_250_ONE_R_LOW_ATR` | 26 | 0.002450 | 0.006692 | -0.019023 | 53.85% | -0.029619 | `WS_BT_EVAL_MIN_TRADES_FAIL,WS_BT_EVAL_STABILITY_FAIL` |
| 151 | `06_VOL_150_250_LOW_ATR_NEG_ROC20` | 25 | 0.001634 | 0.006692 | -0.017164 | 52.00% | -0.029619 | `WS_BT_EVAL_MIN_TRADES_FAIL,WS_BT_EVAL_STABILITY_FAIL` |
| 147 | `02_NEG_ROC20_ONE_R_SCORE_68_82` | 17 | -0.000317 | 0.006692 | -0.020298 | 52.94% | -0.038226 | `WS_BT_EVAL_MIN_TRADES_FAIL,WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_STABILITY_FAIL` |
| 156 | `11_C16_143_DERIVED_ONE_R_SCORE_70_85` | 19 | -0.000336 | 0.006692 | -0.021458 | 52.63% | -0.038226 | `WS_BT_EVAL_MIN_TRADES_FAIL,WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_STABILITY_FAIL` |
| 155 | `10_C16_134_DERIVED_NEG_ROC20_SCORE_68_82` | 15 | -0.000492 | 0.009399 | -0.021675 | 53.33% | -0.038226 | `WS_BT_EVAL_MIN_TRADES_FAIL,WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_STABILITY_FAIL` |
| 153 | `08_SCORE_70_85_DV20_2B_6B_ROC20_COOLING` | 19 | -0.001047 | 0.006692 | -0.021675 | 52.63% | -0.038226 | `WS_BT_EVAL_MIN_TRADES_FAIL,WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_STABILITY_FAIL` |
| 146 | `01_NEG_ROC20_SCORE_65_80_DV20_2B_6B` | 17 | -0.001111 | 0.006692 | -0.020298 | 52.94% | -0.038226 | `WS_BT_EVAL_MIN_TRADES_FAIL,WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_STABILITY_FAIL` |

Final interpretation:

- C17 solved the source/runtime validation objective but did not solve strategy quality.
- All 12 rows failed `WS_BT_EVAL_MIN_TRADES_FAIL`; the strongest sample row produced `42` picks against the canonical `120` minimum.
- All 12 rows failed `WS_BT_EVAL_STABILITY_FAIL`; every row has `month_win_rate_min=0` and worst monthly average below `-0.01`.
- C17 did improve downside robustness: `WS_BT_EVAL_DOWNSIDE_FAIL=0`.
- C17 cannot unlock OOS or promotion because `is_valid_param_count=0` and `best_is_binding=null`.

Final C17 status:

```text
C17_IMPLEMENTED_SOURCE_LEVEL=true
C17_RUNTIME_VALIDATED=true
C17_SEED_PASS=true
C17_DIAGNOSE_BATCH_PASS=true
C17_IS_CALIBRATION_DETERMINISTIC=true
C17_GRID_FAILED_IS_QUALITY=true
C17_REJECTED_AS_STRATEGY_CATALOG=true
reason_code=WS_BT_C17_NO_VALID_IS_CANDIDATE
artifact_hash=23c30d70aeefa88701de8d9a59dd9217ee340ae6
OOS_NOT_RUN=true
production_ready=0
```

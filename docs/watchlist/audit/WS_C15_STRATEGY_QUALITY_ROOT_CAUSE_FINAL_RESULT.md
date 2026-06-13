# WS C15 Strategy Quality Root-Cause Final Result

Status: C15_IMPLEMENTED / C15_RUNTIME_PAYLOAD_FIX4_VALIDATED / IS_QUALITY_FAILED / C15_REJECTED_AS_STRATEGY_CATALOG / OOS_NOT_RUN / NOT_PRODUCTION_READY

## Final Decision

C15 is technically complete and runtime-ready, but it is rejected as an IS strategy-quality catalog.

```text
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C15_2026_06
catalog_version=C15
catalog_count=12
catalog_hash=cc07324262151783dc6b5583ebd91a96c0d0527d
candidate_selection_extension=C15_CONTROLLED_PULLBACK_MID_LIQUIDITY_ANTI_OVEREXTENSION
C15_RUNTIME_PAYLOAD_STATUS=PASS
C15_IS_CALIBRATION_STATUS=C15_GRID_FAILED_IS_QUALITY
reason_code=WS_BT_C15_NO_VALID_IS_CANDIDATE
is_valid_param_count=0
is_failed_param_count=12
param_id_best_is=
best_is_binding_hash=
OOS_NOT_RUN
production_ready=0
```

C15 must not be promoted manually and must not be used as OOS input. The next research step is a new C16 catalog design based on C15's failed-but-informative evidence.

## Post-Fix4 Operator Validation Evidence

Operator-provided validation after the runtime payload and static-guard compatibility fixes recorded:

```text
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC15"
OK (10 tests, 534 assertions)

vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistCandidateUniverseService"
OK (5 tests, 68 assertions)

vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistScoringService"
OK (9 tests, 107 assertions)

vendor\bin\phpunit tests\Unit\Watchlist
OK (341 tests, 7771 assertions)
```

C15 fix4 drilldown became runtime-ready:

```text
status=PASS
reason_code=WS_BT_IS_FAILURE_DRILLDOWN_BATCH_READY
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C15_2026_06
catalog_version=C15
catalog_count=12
catalog_hash=cc07324262151783dc6b5583ebd91a96c0d0527d
diagnostic_param_count=12
ready_count=12
blocked_count=0
missing_runtime_evidence_fields=<empty for all rows>
oos_service_invoked=0
oos_repository_invoked=0
oos_table_unchanged=1
oos_executed=0
production_ready=0
```

C15 IS calibration was deterministic and reached canonical gates:

```text
run1.status=C15_GRID_FAILED_IS_QUALITY
run2.status=C15_GRID_FAILED_IS_QUALITY
reason_code=WS_BT_C15_NO_VALID_IS_CANDIDATE
is_trading_date_count=562
is_trading_date_hash=581dd450ebcbd56cb3a1c066c9fc80bbccb3a753
is_valid_param_count=0
is_failed_param_count=12
is_failure_reason_codes=WS_BT_EVAL_MIN_TRADES_FAIL,WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_STABILITY_FAIL
artifact_hash=1b96a2c38c0aacced72e441bb8d0ecaff045eabf
deterministic=true
strict_is_boundary_all_evaluations=1
no_oos_market_data_read=True
no_oos_table_mutation=True
production_ready=False
```

Validation markers:

```text
all_rows_evaluated_or_reason_coded=True
all_rows_reached_canonical_gates=True
catalog_count_matches=True
catalog_hash_matches=True
best_binding_only_when_valid=True
best_of_failed_forbidden=True
r1_immutable=True
r2_immutable=True
c01_immutable=True
c02_immutable=True
c03_immutable=True
c04_immutable=True
c05_immutable=True
c06_immutable=True
c07_immutable=True
c14_immutable=True
```

## Fix History Summary

Initial C15 implementation created the immutable catalog and C15 candidate-selection extension. The first IS calibration attempt was blocked by runtime metric insufficiency:

```text
status=BLOCKED
reason_code=WS_BT_EVAL_METRICS_MISSING
is_valid_param_count=0
is_failed_param_count=12
picks_count=0 for all C15 rows
next_focus=RUNTIME_PAYLOAD_ENRICHMENT_BEFORE_NEXT_CATALOG
```

Fix3 enabled the C15 extension to consume the extended runtime metric payload class and added fraction-aware parsing for short-term momentum/trend guards. Fix4 preserved C14 static-guard compatibility while keeping C15 runtime enrichment active.

The final C15 fix4 outcome is no longer a runtime failure. It is an honest IS strategy-quality failure.

## C15 Param Outcome Summary

C15 row outcomes after fix4:

```text
param_id  row_code                                      picks  avg_ret_net_top   median_ret_net_top  decision
121       00_C14_REFERENCE_CONTROLLED_PULLBACK_MID_DV20 64     0.0020785842      -0.0004999750      failed
122       01_CORE_MID_DV20_NEUTRAL_ROC20                47     0.0068831807       0.0128957217      best_failed_anchor
123       02_CORE_MID_DV20_LOW_ATR                      54     0.0047713922       0.0066922754      failed
124       03_SAMPLE_RECOVERY_DV20_TO_7_5B               74     0.0045203488      -0.0004998750      sample_recovery_degraded_median
125       04_STRICT_DV20_2_5B_TO_5B_SCORE_CAP           33     0.0035966638       0.0047063536      failed_low_sample
126       05_WIDER_VOLUME_CONTROLLED_PULLBACK           78     0.0025885530      -0.0004999300      sample_recovery_degraded_median
127       06_NARROW_ROC20_NEGATIVE_TO_FLAT              39     0.0055415095      -0.0004998750      failed_low_sample
128       07_LIGHTLY_POSITIVE_ROC20_WITH_PULLBACK       58     0.0043961517      -0.0004998875      failed
129       08_LOW_ATR_SAMPLE_RECOVERY                    118    0.0005140667      -0.0004999100      near_sample_threshold_quality_degraded
130       09_TIGHT_RISK_MID_DV20                        38     0.0068455091       0.0125046360      best_failed_anchor
131       10_MODERATE_RR_MID_DV20                       60     0.0060217148      -0.0004998750      failed
132       11_BACKFILL_WITH_C15_GUARDS                   179   -0.0001369346      -0.0004998800      sample_recovery_failed_quality
```

All rows had `month_win_rate_min=0`, and therefore failed monthly stability.

## Best Failed Anchors

The strongest failed anchors are `param_id=122` and `param_id=130`.

`param_id=122`:

```text
avg_ret_net_top=0.006883180654542075
median_ret_net_top=0.012895721654698088
p25_ret_net_top=-0.013991665524242029
win_rate_top=0.6170212765957447
picks_count=47
month_win_rate_min=0
month_avg_ret_net_min=-0.035376058525830534
reason_codes=WS_BT_EVAL_MIN_TRADES_FAIL,WS_BT_EVAL_STABILITY_FAIL
```

`param_id=130`:

```text
avg_ret_net_top=0.006845509113568816
median_ret_net_top=0.012504636025864869
p25_ret_net_top=-0.010740060145898159
win_rate_top=0.631578947368421
picks_count=38
month_win_rate_min=0
month_avg_ret_net_min=-0.011530965016048141
reason_codes=WS_BT_EVAL_MIN_TRADES_FAIL,WS_BT_EVAL_STABILITY_FAIL
```

These rows are diagnostic anchors only. `best_of_failed_forbidden=True`, so neither row may be promoted.

## Strategy-Quality Root Cause

C15 failed because the high-quality C15 anchors were too narrow, while broad sample-recovery rows degraded quality.

Main findings:

```text
sample too small for high-quality anchors:
  param 122 picks_count=47
  param 130 picks_count=38
  min_trades=120

sample recovery degraded quality:
  param 129 picks_count=118, avg_ret_net_top=0.0005140667, median_ret_net_top=-0.0004999100
  param 132 picks_count=179, avg_ret_net_top=-0.0001369346, median_ret_net_top=-0.0004998800

monthly stability failed everywhere:
  month_win_rate_min=0 for all rows
```

Repeated bad-month clusters included:

```text
2024-10
2024-12
2024-02
2024-03
2025-02
2025-03
2023-05
2023-12
```

Repeated loss-ticker clusters included examples such as:

```text
PPGL, PTSN, DNET, BLTA, INCI, OPMS, EKAD, TOOL, MKTR, TLDN, BSIM, VICI
```

These clusters are diagnostic evidence only and are not an authorization to create hardcoded ticker exclusions.

## Useful C16 Design Signals From C15

C15 evidence supports these C16 design directions:

```text
score bucket 0.7..0.8 = useful
score bucket 0.8..0.9 = repeatedly poor / overextended
volume ratio 1.5..2 = best observed volume bucket
volume ratio 1.0..1.5 = often degraded quality
DV20 2.5B..5B remains the main useful liquidity band
ROC5 -0.02..0 remains the controlled pullback band
ROC20 -0.05..0 and 0..0.02 both remain diagnostic candidates
```

C16 must not simply loosen C15. A broad sample-recovery approach already failed in C15 rows 129 and 132.

## C15 Runtime Guardrail Preserved

C15 candidate-selection extension remains:

```text
C15_CONTROLLED_PULLBACK_MID_LIQUIDITY_ANTI_OVEREXTENSION
```

Final C15 runtime guards remain:

```text
min_dv20_idr <= dv20_idr <= dv20_strong_idr
min_vol_ratio <= vol_ratio <= strong_vol_ratio
min_atr14_pct <= atr14_pct <= max_atr14_pct
roc_lo <= roc20 <= roc_hi
-0.02 <= roc5 <= 0
score_total <= 0.899999
score_component_average >= configured floor when present
```

C15 did not change R1/R2/C01/C02/C03/C04/C05/C06/C07/C14 payloads or hashes.

## Historical C15 Axis Evidence Carried Forward

C15 was originally authorized because C14 drilldown aggregate supported:

```text
dv20 2500000000..5000000000:
  param_bucket_rows=6
  total_trades=216
  win_rate=51.39%
  weighted_avg_pct=0.2277

roc5 -0.02..0:
  param_bucket_rows=12
  total_trades=1470
  win_rate=47.48%
  weighted_avg_pct=0.0806
```

C14 drilldown aggregate rejected high-chase behavior:

```text
score_bucket 0.9..1       weighted_avg_pct=-0.5466
roc5 0.05..0.1            weighted_avg_pct=-0.6095
roc10 0.05..0.1           weighted_avg_pct=-0.6161
dv20 >20000000000         weighted_avg_pct=-0.4617
volume_ratio >3           weighted_avg_pct=-0.5106
```

C15 refined that evidence, but did not produce a valid IS candidate.

## OOS Status

```text
OOS_NOT_RUN
OOS_NOT_AUTHORIZED
NOT_ELIGIBLE_FOR_OOS_PROOF_NO_VALID_IS_PARAMETER
production_ready=0
```

OOS must not be run for C15 because there is no valid IS candidate and no best binding hash.

## Next Required Action

```text
NEXT_ACTION=C16_SAMPLE_RECOVERY_AND_STABILITY_DESIGN_FROM_C15_EVIDENCE
```

C16 should be a new immutable catalog if implemented. It must not mutate C15, select a best failed C15 row, or loosen canonical IS gates.

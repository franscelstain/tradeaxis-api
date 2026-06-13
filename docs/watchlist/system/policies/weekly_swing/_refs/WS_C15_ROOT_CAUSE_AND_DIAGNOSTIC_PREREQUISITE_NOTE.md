# WS C15 Root-Cause and Implementation Note

Status: C15_IMPLEMENTED_AS_RESEARCH_CATALOG / RUNTIME_PAYLOAD_VALIDATED / IS_QUALITY_FAILED / OOS_NOT_RUN / NOT_PRODUCTION_READY

## Purpose

This note records the policy-side guardrail and final outcome for the C15 strategy-quality work. It is not a production strategy definition.

C01 through C14 remain forensic/research history. C08 through C13 remain diagnostic or support layers. C15 is an immutable IS research catalog that has now been evaluated and rejected as an IS strategy-quality catalog.

## Final C15 Decision

```text
C15_STRATEGY_CATALOG_CREATED=true
C15_CATALOG_CODE=WS_BT_GRID_DOWNSIDE_STABILITY_C15_2026_06
C15_CATALOG_VERSION=C15
C15_CATALOG_COUNT=12
C15_CATALOG_HASH=cc07324262151783dc6b5583ebd91a96c0d0527d
C15_CATALOG_AUTHORIZED_FOR_IS_RESEARCH=true
C15_RUNTIME_PAYLOAD_STATUS=PASS
C15_IS_CALIBRATION_STATUS=C15_GRID_FAILED_IS_QUALITY
C15_REASON_CODE=WS_BT_C15_NO_VALID_IS_CANDIDATE
C15_VALID_PARAM_COUNT=0
C15_FAILED_PARAM_COUNT=12
C15_AUTHORIZED_FOR_OOS=false
OOS_NOT_RUN
production_ready=0
```

C15 is not eligible for OOS because it produced no valid IS candidate and no best binding hash.

## C15 Evidence Standard Satisfied Before Creation

C15 was authorized because C14 all-row diagnostics showed repeatable, runtime-supported buckets:

```text
dv20 2500000000..5000000000: weighted_avg_pct=0.2277, total_trades=216
roc5 -0.02..0: weighted_avg_pct=0.0806, total_trades=1470, param_bucket_rows=12
```

C15 also used negative evidence to reject overextension:

```text
score_bucket 0.9..1 underperformed
high ROC5/ROC10/ROC20 buckets underperformed
extreme DV20 and volume-ratio buckets underperformed
```

## C15 Runtime Guardrail

C15 candidate-selection extension:

```text
C15_CONTROLLED_PULLBACK_MID_LIQUIDITY_ANTI_OVEREXTENSION
```

Allowed runtime guards:

```text
min_dv20_idr <= dv20_idr <= dv20_strong_idr
min_vol_ratio <= vol_ratio <= strong_vol_ratio
min_atr14_pct <= atr14_pct <= max_atr14_pct
roc_lo <= roc20 <= roc_hi
-0.02 <= roc5 <= 0
score_total <= 0.899999
```

Final runtime validation after fix4 showed `missing_runtime_evidence_fields` empty for all C15 rows and `ready_count=12`, `blocked_count=0` in C15 drilldown.

## Final IS Quality Result

C15 IS calibration result:

```text
status=C15_GRID_FAILED_IS_QUALITY
reason_code=WS_BT_C15_NO_VALID_IS_CANDIDATE
is_valid_param_count=0
is_failed_param_count=12
failure_reason_codes=WS_BT_EVAL_MIN_TRADES_FAIL,WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_STABILITY_FAIL
artifact_hash=1b96a2c38c0aacced72e441bb8d0ecaff045eabf
deterministic=true
strict_is_boundary_all_evaluations=1
no_oos_market_data_read=True
no_oos_table_mutation=True
production_ready=False
```

C15 reached canonical gates and failed quality. This is not a runtime blocker.

## Root-Cause Outcome

C15's best failed anchors were `param_id=122` and `param_id=130`, but both failed minimum-trade and monthly-stability gates.

```text
param 122: picks_count=47, avg_ret_net_top=0.0068831807, median_ret_net_top=0.0128957217, win_rate_top=0.6170, month_win_rate_min=0
param 130: picks_count=38, avg_ret_net_top=0.0068455091, median_ret_net_top=0.0125046360, win_rate_top=0.6316, month_win_rate_min=0
```

Sample-recovery rows degraded quality:

```text
param 129: picks_count=118, avg_ret_net_top=0.0005140667, median_ret_net_top=-0.0004999100, month_win_rate_min=0
param 132: picks_count=179, avg_ret_net_top=-0.0001369346, median_ret_net_top=-0.0004998800, month_win_rate_min=0
```

Therefore C15 should be treated as a failed research catalog with useful design evidence for C16, not as a candidate for promotion.

## Blocked C15 Shortcuts

Blocked shortcuts remain:

```text
lowering IS thresholds
loosening gates
picking a best failed row
manually promoting param 122 or param 130
using sample-recovery rows 129/132 as proof
adding fake sector filters or hardcoded ticker exclusions
changing old hashes
mutating R1/R2/C01/C02/C03/C04/C05/C06/C07/C14/C15
claiming OOS or production readiness before an explicit valid-IS-plus-OOS session
```

Diagnostic loss clusters may inform C16 design but must not become hardcoded exclusions without a separate contract.

## C16 Direction From C15 Evidence

If C16 is implemented, it should be a new immutable catalog based on C15 evidence:

```text
anchor evidence: param 122 / param 130
preferred score bucket: 0.7..0.8
avoid score bucket: 0.8..0.9
preferred volume ratio bucket: 1.5..2
avoid broad low-volume recovery: 1.0..1.5 without additional guard
preserve mid-DV20 focus: 2.5B..5B
preserve controlled ROC5 pullback: -0.02..0
focus: sample recovery without degrading monthly stability
```

## OOS Boundary

```text
OOS_NOT_RUN
OOS_NOT_AUTHORIZED
NOT_ELIGIBLE_FOR_OOS_PROOF_NO_VALID_IS_PARAMETER
production_ready=0
```

OOS remains blocked unless a future catalog produces a valid IS candidate and a later explicit OOS session is requested.

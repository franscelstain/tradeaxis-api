# C171 Immutable DRAFT Paramset Candidate Design

## Session identity

```text
C171_TOPIC=C171_DESIGN_NEW_IMMUTABLE_DRAFT_PARAMSET_CANDIDATES_FROM_DIAGNOSTIC
C171_STATUS=C171_IMMUTABLE_DRAFT_CANDIDATE_DESIGN_COMPLETED
C171_SOURCE_EVAL_ID=188
C171_SOURCE_PARAM_SET_ID=1
C171_SOURCE_PARAMS_HASH=b7f3c207b989c55c93f8f61b1fcceea2c343a151
C171_DIAGNOSTIC_ARTIFACT_HASH=768b4e47d4a9e497fda29ca6541be9a8f3a63c9d
C171_DIAGNOSTIC_JSON_SHA1=1D2EDC8BA5E1DA342437E8C91465DB03858B068D
C171_TRADES_CSV_SHA1=7EE6A9C292B43DE39A6D4436C5CA80A41D463048
C171_SEGMENTS_CSV_SHA1=3BB20881DDF2C3D5D402034FDEB50034BF4A10B0
C171_ANOMALIES_CSV_SHA1=53E530A601B2B2DC47812D149D5082AC72F226B9
C171_CANDIDATE_COUNT=5
C171_DRAFT_ROWS_CREATED=0
C171_OOS_RUNTIME_INVOKED=0
C171_PRODUCTION_READY=0
```

## Owner decision

The exact official diagnostic reproduced all 1,425 picks with zero mismatch and
classified the failure as `STRATEGY_QUALITY_FAILURE_CONFIRMED`. The single
flagged price discontinuity is not the primary cause: after isolating it,
average return, median return, p25 downside, minimum monthly win rate, and
minimum monthly average remain failed. Therefore C171 may design a small
curated DRAFT set, but may not invoke OOS or edit `param_set_id=1`.

This design uses only decision-time fields. It does not use ticker, sector, or
month blacklists and does not lower any canonical gate.

## Confirmed real-IS signals

1. **Volume spikes are harmful.** `vol_ratio >= 5` produced 595 trades with
   negative average return and only about 34.79% wins.
2. **Very-high liquidity concentration is harmful.** `dv20_idr >= 50B`
   contributed the largest negative aggregate return.
3. **ATR 6%-10% has weak downside.** Its median and p25 were materially worse
   than lower-ATR bands.
4. **Score saturation is not a quality guarantee.** The upper score deciles
   remained negative while bounded, non-saturated subsets improved.
5. **The anomaly is not a strategy filter.** No ticker blacklist or silent
   trade deletion is permitted.

## Important limitation of proxy metrics

The figures below retain only baseline picks satisfying each proposed bound.
They are **not** reranked official IS results and cannot be used as PASS evidence.
A real candidate run will filter the full daily universe, select replacements,
recompute cutoffs, and may have materially different trade/date coverage.

## Curated candidate set

| Candidate | Role | DV20 range | Volume-ratio range | ATR range | TOP score cap | Proxy trades/days | Proxy avg | Proxy median | Proxy p25 |
|---|---|---:|---:|---:|---:|---:|---:|---:|---:|
| `C171_DRAFT_A_BROAD_MODERATE_SCORE_CAP` | primary_quality_candidate | 1B-50B | 1.2-5 | 2.0000%-7.5000% | 0.98 | 200/163 | 3.2373% | 3.5280% | -6.2984% |
| `C171_DRAFT_B_BROAD_SAMPLE_RECOVERY` | coverage_recovery_candidate | 1B-50B | 1.2-5 | 2.0000%-7.5000% | 1 | 292/231 | 2.3980% | 1.4375% | -7.2210% |
| `C171_DRAFT_C_MID_LIQ_LOW_ATR_SCORE_CAP` | mid_liquidity_quality_candidate | 2.5B-50B | 1.5-5 | 2.0000%-6.0000% | 0.999999 | 166/149 | 2.3548% | 1.3855% | -6.4413% |
| `C171_DRAFT_D_LOW_ATR_BALANCED` | low_atr_balanced_candidate | 1B-50B | 1.2-5 | 2.0000%-6.0000% | 0.999999 | 184/160 | 2.4343% | 1.5908% | -6.2984% |
| `C171_DRAFT_E_LOWER_VOLUME_BALANCED` | volume_spike_avoidance_candidate | 1B-50B | 1.2-3 | 2.0000%-7.5000% | 0.98 | 132/119 | 3.2520% | 3.3859% | -6.8937% |

None of the proxy subsets passes every canonical gate. In particular, p25 and
monthly floors remain failed, and proxy date coverage is below 390 because
replacement candidates were not reranked. The candidate set is therefore a
controlled implementation hypothesis, not evidence of strategy success.

## Candidate rationale

### C171_DRAFT_A_BROAD_MODERATE_SCORE_CAP

Broad enough to preserve replacement opportunity while removing the three confirmed weak regions: very-high liquidity concentration, volume spikes, and score saturation.

### C171_DRAFT_B_BROAD_SAMPLE_RECOVERY

Control candidate for whether upper liquidity/volume and ATR bounds improve quality without a score cap; intended to preserve more dates for the canonical coverage gate.

### C171_DRAFT_C_MID_LIQ_LOW_ATR_SCORE_CAP

Targets the strongest liquidity/volume/ATR intersection while avoiding exact score saturation; retains a broader upper liquidity bound than the narrow 2.5B–10B frontier.

### C171_DRAFT_D_LOW_ATR_BALANCED

Keeps the broad liquidity floor but removes the 6%–10% ATR bucket and exact score saturation; intended as the main balance between quality and replacement coverage.

### C171_DRAFT_E_LOWER_VOLUME_BALANCED

Tests the strongest direct signal from the official diagnostic: moderate participation outperformed volume spikes; keeps ATR broader to avoid combining too many strict axes.

## Required implementation before DRAFT persistence

The current canonical paramset cannot express the strongest diagnostic bounds.
The next implementation must add audited, hashed fields:

```text
liquidity.max_dv20_idr
volume.max_vol_ratio
grouping.top_max_score_total
```

The implementation must introduce immutable catalog:

```text
CATALOG_CODE=WS_BT_GRID_REAL_IS_REMEDIATION_C171_R1_2026_07
CATALOG_VERSION=C171-R1
CATALOG_ROW_COUNT=5
```

Required behavior:

- maximum DV20 and volume-ratio checks use decision-time indicators only;
- score maximum is applied during TOP selection, not after returns are known;
- baseline scoring weights and execution model remain unchanged in this first
  remediation catalog so the bounded-selection effect is isolated;
- no sector whitelist, ticker blacklist, month blacklist, or OOS information;
- each imported candidate receives a new immutable `param_set_id` and
  `params_hash`;
- official IS uses the unchanged `2023-01-02` to `2025-05-21` window;
- C172 remains blocked until one unchanged candidate passes every gate.

## Forbidden interpretations

```text
PROXY_RESULT_IS_OFFICIAL_IS_PASS=0
EDIT_PARAM_SET_1=0
DELETE_EVAL_188=0
LOWER_CANONICAL_GATES=0
CREATE_RANDOM_GRID=0
AUTO_PROMOTE_BEST_FAILED=0
RUN_OOS=0
PRODUCTION_READY=0
```

## Next stage

```text
C171_IMPLEMENT_AND_PERSIST_IMMUTABLE_REAL_IS_REMEDIATION_DRAFT_CATALOG
```

That stage must implement the three explicit bounds, seed exactly five immutable
catalog rows, create DRAFTs only after validation and exact binding, and provide
operator commands for official IS. No OOS command belongs in that stage.

## Implementation continuation

The three canonical bounds and five-row code-owned catalog are now implemented
in source. Operator migration, focused/full regression, and DRAFT persistence
remain pending. The design artifact remains immutable; implementation does not
change its candidate definitions or proxy interpretation.

```text
IMPLEMENTATION_STATUS=IMPLEMENTED_PENDING_OPERATOR_VALIDATION_AND_PERSISTENCE
CATALOG_HASH=82b0fcbf17823fda5ab59bd2dba3d947b4f9e233
DRAFT_ROWS_CREATED_BY_SOURCE_PATCH=0
NEXT=C171_OPERATOR_MIGRATE_TEST_AND_PERSIST_IMMUTABLE_REMEDIATION_DRAFT_CATALOG
```
### Candidate-design artifact hash compatibility

The immutable design artifact hash was created from recursively key-sorted compact JSON with unescaped Unicode and preserved zero fractions. Runtime verification therefore uses `JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION` after recursive key sorting. This is intentionally distinct from the official C171 evidence hash helper and preserves the already-released design identity `2a1345857e2ecf62b2d64fcaa46ed06f6015e9a6`.


# Indicator Expected Output Oracle (STRATEGY LOCKED)

## Purpose

Define compact, strategy-correct expected outputs. The mandatory >100-session independent artifact lives in fixture family `atr_wilder_long_chain_v2`; this file locks representative meanings and exact short calculations without permitting legacy fallback behavior.

## Input identity

Every case binds:

- stable listing and trade date;
- immutable RAW publication/observation manifest;
- coherent `STRUCTURAL_ADJUSTED` product and verified factor-set hash;
- formula/indicator registry version;
- full configuration snapshot hash;
- calendar/session dependency chain.

Provider `adj_close` is never an oracle input or fallback.

## Compact oracle table

| Case ID | Required input | Expected output |
|---|---|---|
| `ORACLE_ATR_SEED` | first 14 consecutive TR values sum to `15.60` | `atr14 = 1.1142857143` at the stable seed date |
| `ORACLE_ATR_RECURSIVE_NEXT` | prior ATR `1.1142857143`, current TR `1.50`, coherent close `48.0000` | `atr14 = 1.1418367347`; `atr14_pct = 0.0237882653` |
| `ORACLE_ROC20_STRUCTURAL` | coherent `C(D)=110.0000`, coherent `C(D[-20])=100.0000` under one factor set | `roc20 = 0.1000000000` |
| `ORACLE_PROXY_ACTUAL_DISTINCT` | complete actual values average `200000000.00`; RAW close-volume proxy values average `150000000.00` | actual field `200000000.00`; proxy field `150000000.00`; neither falls back to the other |
| `ORACLE_SHORT_HISTORY` | required dependency chain not yet available because of dataset/listing warm-up | dependent fields `NULL` with `IND_INSUFFICIENT_HISTORY` or the versioned field reason |
| `ORACLE_MISSING_EXPECTED_BAR` | an expected session is missing inside the dependency chain | dependent fields `NULL`/invalid with `IND_MISSING_DEPENDENCY_BAR`; no skip or reseed |
| `ORACLE_UNVERIFIED_STRUCTURAL_BREAK` | discontinuity candidate without verified event/factor terms | affected structural indicators `NULL`/contaminated; no generated factor or repaired bar |
| `ORACLE_LATE_CORRECTION` | a historical TR changes more than 14 sessions before target D | new publication recomputes every affected recursive ATR state; prior publication remains unchanged |

## Exact calculations

### Stable ATR seed

`ATR14(seed) = 15.60 / 14 = 1.114285714285...`, stored/formatted as `1.1142857143` under the locked precision.

### Next Wilder recurrence

`ATR14(next) = ((1.114285714285... × 13) + 1.50) / 14 = 1.141836734693...`

`atr14_pct(next) = 1.141836734693... / 48.0000 = 0.023788265306...`

No intermediate rounding is allowed; shown stored values are rounded only at the owned output boundary.

### ROC20

`roc20(D) = (110.0000 / 100.0000) - 1 = 0.1000000000`

Both endpoints are from the same coherent structural product/factor-set identity. If either endpoint lacks verified factor continuity, the result is `NULL`; RAW/provider fallback is forbidden.

## Mandatory long-chain artifact

`atr_wilder_long_chain_v2` must contain:

- stable seed beginning at the later of `2023-01-02` and listing start;
- at least 100 post-seed expected sessions;
- independently calculated TR, ATR, and stored expected values;
- a deliberately calculated sliding-reseed series asserted to diverge;
- a gap case and an old-correction case whose effect survives beyond fourteen sessions;
- input, expected-output, and manifest hashes.

Short examples in this file cannot close indicator correctness without that artifact and production-path execution.

## Precision

- RAW/analytical prices: 4 decimal places;
- actual and proxy IDR metrics: 2 decimal places;
- `atr14`, `atr14_pct`, ratios, ROC, and relative values: 10 decimal places;
- factor precision: owned by the price-adjustment contract, with no destructive intermediate rounding.

## Acceptance

An implementation is wrong when it:

- uses provider `adj_close`/RAW fallback;
- mixes price/volume bases;
- reseeds ATR from a sliding load window;
- skips a missing expected dependency;
- labels a proxy as actual traded value;
- repairs history or creates a verified factor from price behavior;
- updates expected values merely to match changed runtime output.

## Cross-contract alignment

- `Indicator_Test_Vectors_LOCKED.md`
- `Golden_Fixture_Catalog_LOCKED.md`
- `../indicators/EOD_Indicators_Formula_Spec.md`
- `../book/EOD_Indicators_Contract.md`
- `../registry/Price_Adjustment_Contract_LOCKED.md`

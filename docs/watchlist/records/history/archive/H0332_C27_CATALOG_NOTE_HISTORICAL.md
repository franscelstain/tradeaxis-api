# WS Catalog Candidate C27 Raw OHLC Validation Note

C27 implements the raw-OHLC-first step required by C26.

## Boundary

```text
scope=IS_ONLY_CATALOG_CANDIDATE_RAW_OHLC_VALIDATION
catalog_created=false
oos_run=false
production_ready=0
mutates_C01_to_C26=false
```

C27 is not a production catalog and not an OOS proof. It is an implementation-readiness validation artifact for the C26 candidate rule family.

## Execution Model

Canonical execution model remains:

```text
ENTRY=NEXT_OPEN
EXIT=STOP_TP_OR_TIME
HOLD=5
FEE=IDR_FIXED
SLIP=0
GAP=OPEN
PX=IDX_BANDS
```

C27 validates the candidate with raw published OHLC:

```text
G21 = raw preplanned intraday target +1.00%
      else raw no-profit-by-D2 damage-control exit at D3 open
      else raw R09 next-open-after-D1/D2/D3-close-profit rule

G13 = raw preplanned intraday target +0.50% else raw R09
G16 = raw preplanned intraday target +1.50% else raw R09
```

Raw preplanned target prices are normalized to IDX tick bands. Close-signal rules exit only after the close signal on the next open.

## C27 Result

C27 satisfies the raw OHLC validation requirement:

```text
raw_ohlc_validation_pass=true
raw_ohlc_validated_count=1575
raw_ohlc_missing_count=0
derived_mfe_mae_used_for_execution=false
lookahead_violation_count=0
```

However, C27 does not unlock OOS:

```text
g21_raw_beats_r09=true
g21_raw_catalog_candidate_ready=false
failure_reason=G21_BUCKET_STABILITY_WEAK
c28_oos_proof_recommended=false
```

The primary finding is that raw G21 improves R09 overall and materially improves downside distribution, but bucket stability is not clean enough for OOS/catalog promotion. The large `candidate_matches_or_beats_c22` bucket loses average return versus raw R09 despite improving p25.

## Next Design Work

Next work should stay IS-only until a new candidate passes raw stability gates:

```text
NEXT_STEP=C28_RULE_REVISION_OR_G13_G16_TIEBREAK_DIAGNOSTIC_IS_ONLY
```

Likely investigation directions:

```text
1. Re-evaluate whether G21 should only apply to no-signal and next-open-delay buckets.
2. Compare G13 and G16 as raw primary/defensive candidates after C27 showed stronger raw averages.
3. Keep R09 as fallback where candidate_matches_or_beats_c22 already behaves well.
4. Do not run OOS until an IS-only raw candidate passes bucket stability.
```

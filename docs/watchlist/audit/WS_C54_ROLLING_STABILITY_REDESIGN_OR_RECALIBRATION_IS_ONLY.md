# WS C54 — Rolling Stability Redesign or Recalibration (IS Only)

## Purpose and boundary

C54 consumes the locked C53 rolling-stability diagnosis and locked C52 reconstruction lineage. It forms a predeclared IS-only family using monthly G16/G21 quotas, optional minimal G13 backfill, and monthly ticker/sector caps. Realized returns, future paths, C53 adverse-month attribution, and OOS data are not candidate-formation inputs.

```text
IS=2023-01-02..2025-05-21
OOS_RESERVED=2025-05-22..2026-05-29
NO_GATE_RELAXATION=true
NO_ADVERSE_MONTH_EXCLUSION=true
NO_TICKER_OR_SECTOR_EXCLUSION=true
production_ready=false
```

## Locked inputs

```text
C53_HASH=6a1749d723e16b7efdb8aa1d7510388a9475d12c
C53_FILE_SHA1=E35FEFB78B6F1931E54169BD8AABE286CB6F08C2
C52_HASH=5dbe51c9d18b175e65cddb60336baf43d6833b72
C52_FILE_SHA1=DADE6518BFF3912D8A43D7C67073FB803F7CF878
C53_PRIMARY_GAP=ROLLING_STABILITY
```

C52 rows are reconstructed through a read-only seam using the same C28 source, exact-date pre-trade join, sector metadata rules, and C51/C50/C49 lineage. No earlier artifact is rewritten.

## Candidate contract

C54 evaluates one locked C52_R07 stability-anchor comparator and 11 redesigned candidates. The redesign dimensions are limited to:

```text
G16_MONTHLY_QUOTA=7..11
G21_MONTHLY_QUOTA=7..9
G13_MONTHLY_QUOTA=0..1
TICKER_CAP_PER_MONTH=3..5
SECTOR_CAP_PER_MONTH=4..5
RANKING=SAFE_PRE_TRADE_BALANCED_OR_METADATA
```

The inherited full-IS, concentration, rolling, LOO, regime, and material-difference gates remain unchanged.

## Runtime result

```text
source_rows=15750
redesigned_candidates=11
quality_pass=11/11
coverage_pass=11/11
full_is_stability_pass=0/11
concentration_pass=0/11
full_rolling_pass=0/11
loo_pass=5/11
regime_pass=3/11
material_difference_pass=8/11
candidate_ready_for_c55=0/11
best_rolling_pass_rate=0.9833333333333333
```

`C54_R05_G16_08_G21_07_G13_01_MINIMAL` and `C54_R07_G16_08_G21_08_G13_01_MINIMAL` each pass 59 of 60 rolling windows. This is material improvement over C53, but it is not a full pass. Neither candidate may be treated as selected, production-ready, or OOS-ready.

```text
status=C54_ROLLING_STABILITY_REDESIGN_OR_RECALIBRATION_IS_ONLY_COMPLETED
diagnostic_conclusion=C54_ROLLING_STABILITY_GAP_REMAINS
next_step_recommendation=C55_ROLLING_STABILITY_REDESIGN_CONTINUATION_IS_ONLY
direct_oos_proof_recommended=false
oos_proof_unlocked=false
production_ready=false
artifact_hash=8c71a4352a1024dbe985e0f0bb6329f5e1545150
file_sha1=75410BB1A30A32FFFF9661CAD6818C13E044F7E5
```

C54 therefore closes as a completed redesign experiment with a remaining gap. C55 must continue IS-only and must not convert observed failing windows or C53 adverse months into retrospective exclusion rules.

## Validation

```text
C54_PHPUNIT=OK (8 tests, 114 assertions)
FULL_WATCHLIST_PHPUNIT=OK (777 tests, 15152 assertions)
COMMAND_REGISTRATION=PASS
STABLE_HASH_RECALCULATION=PASS
POWERSHELL_SAFETY_KEY_UNIQUENESS=PASS
```

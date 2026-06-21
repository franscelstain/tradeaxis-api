# WS C53 — IS Evidence Expansion for C52 Redesign

## Purpose and boundary

C53 locks the completed C52 artifact and expands its IS evidence across rolling windows, leave-one-month-out, adverse-month attribution, regime availability, and structural guard preservation. C53 does not create a new candidate, select a winner, use return for cohort membership, tune on OOS, execute OOS proof, or promote a production candidate.

```text
IS=2023-01-02..2025-05-21
OOS_RESERVED=2025-05-22..2026-05-29
production_ready=false
```

## C52 lock

```text
input_c52_artifact=storage/app/watchlist/backtest/c52-concentration-dependency-redesign-continuation.json
expected_c52_hash=5dbe51c9d18b175e65cddb60336baf43d6833b72
expected_c52_file_sha1=DADE6518BFF3912D8A43D7C67073FB803F7CF878
c52_status=C52_CONCENTRATION_DEPENDENCY_REDESIGN_CONTINUATION_COMPLETED
c52_diagnostic_conclusion=C52_EVIDENCE_EXPANSION_REQUIRED
c52_next_step_recommendation=C53_IS_EVIDENCE_EXPANSION_FOR_C52_REDESIGN
```

C51/C50/C49 hashes are carried forward from C52 as locked lineage. Sector reconstruction and source-bias validation must remain passing before C53 can run.

## Structural review cohort

Cohort membership is predeclared as:

```text
candidate_role != comparator_only
sector_metadata_reconstruction_pass=true
concentration_validation_pass=true
material_selection_difference_pass=true
source_bias_validation_pass=true
```

Return, win rate, future path, and OOS fields are explicitly excluded from membership. The resulting cohort contains 14 candidates. No candidate winner or new candidate is formed.

## Evidence expansion result

Rolling evidence:

```text
cohort_candidates=14
rolling_windows=840
rolling_quality_failures=0
rolling_stability_failures=217
rolling_coverage_failures=0
full_rolling_pass_candidates=0
primary_gap=ROLLING_STABILITY
```

All 14 candidates preserve quality and coverage across the rolling expansion, but every candidate has at least one stability-failing window. This distinguishes the problem from quality collapse or insufficient pick coverage.

LOO evidence:

```text
loo_results=378
candidate_loo_pass_count=0
single_month_dependency_count=0
```

LOO does not show the hard `dependency_on_excluded_month` condition, but rank stability remains below the predeclared 70% floor for every structural candidate.

Adverse-month attribution identifies broad cohort improvement after excluding several months. The strongest common pressure months are:

```text
2025-05 mean_quality_delta=+0.0007396216604373822 candidates_improved=14/14
2024-03 mean_quality_delta=+0.0005061067537866148 candidates_improved=14/14
2024-02 mean_quality_delta=+0.0004567985091938383 candidates_improved=14/14
2024-05 mean_quality_delta=+0.0003914118431058309 candidates_improved=14/14
2023-08 mean_quality_delta=+0.00038926662751357445 candidates_improved=14/14
```

These months are diagnostic evidence only and are not exclusion rules.

Regime availability:

```text
fully_available_fields=sector_roc20,rs_20_vs_ihsg,rs_20_vs_sector,roc20,ma20_slope_pct
not_evaluable_fields=market_index_roc20,market_index_ma20_slope_pct
not_evaluable_records=28
regime_pass_candidates=13/14
```

Structural concentration, sector metadata coverage, source-bias, and no-OOS boundaries remain preserved for all 14 candidates.

## C54 readiness decision

```text
candidate_ready_for_c54_count=0
primary_evidence_gap=ROLLING_STABILITY
adverse_month_cluster_detected=true
regime_field_evidence_gap=true
diagnostic_conclusion=C53_ROLLING_STABILITY_EVIDENCE_GAP_CONFIRMED
next_step_recommendation=C54_ROLLING_STABILITY_REDESIGN_OR_RECALIBRATION_IS_ONLY
direct_oos_proof_recommended=false
oos_proof_unlocked=false
production_ready=false
```

C53 therefore closes as completed evidence expansion, not as an OOS gate. C54 must remain IS-only and address stability without retrospective month/ticker exclusion.

## Runtime validation

```text
C53_PHPUNIT_STATUS=PASS — OK (10 tests, 130 assertions)
FULL_WATCHLIST_PHPUNIT_STATUS=PASS — OK (769 tests, 15038 assertions)
C53_RUNTIME_STATUS=COMPLETED
artifact_path=storage/app/watchlist/backtest/c53-is-evidence-expansion-for-c52-redesign.json
artifact_hash=6a1749d723e16b7efdb8aa1d7510388a9475d12c
file_sha1=E35FEFB78B6F1931E54169BD8AABE286CB6F08C2
```

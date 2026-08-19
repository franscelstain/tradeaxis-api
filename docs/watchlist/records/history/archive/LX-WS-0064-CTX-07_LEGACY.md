# Legacy Role Extract — LEGACY — CONTEXT

> **Document Type:** HISTORICAL_CONTEXT
> **Authoritative Role:** `HISTORY`
> **Status:** HISTORICAL_EXTRACT / IMMUTABLE
> **Legacy Extract ID:** `LX-WS-0064-CTX-07`
> **Legacy Source ID:** `LS-WS-0064`
> **Legacy Work Key:** `LEGACY`
> **Original Path:** `docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md`
> **Original SHA1:** `EA74B18E611681C8BFDFEA7F436AE16E2222F596`
> **Source Sections:** L1-L2 (preamble/title); L3-L13 Document Purpose; L533-L568 PRIOR SESSION - C57 REGIME FIELD RECONSTRUCTION CONTINUATION; L3833-L3875 WL-CONTRACT-012 â€” PORTFOLIO AWARENESS BOUNDARY; L4725-L4741 OOS Post-Deployment Regression Contract Correction â€” 2026-06-10; L4742-L4756 OOS Grid Cross-Field Paramset Contract Correction â€” 2026-06-10; L5215-L5218 Contract Append - 2026-06-15 C16 final operator validation; L5544-L5655 C37 Contract - IS Validation And Anti-Overfit Check; L5879-L5991 C40 Contract - IS Validation And Anti-Overfit Check For C39 Guarded Candidate; L6299-L6337 C45 Contract - IS Validation and Anti-Overfit Check for C44 Refinement; L7091-L7174 C57 Contract â€” Regime Field Reconstruction Continuation IS Only; L7175-L7188 C57 fix2 contract clarification; L7189-L7218 C57 final contract validation; L7219-L7241 WATCHLIST_DB_DICTIONARY_REQUIRED_CONTRACT; L7766-L7790 C65 Contract Final Operator Validation; L7814-L7840 C66 Contract Final Operator Validation; L7872-L7877 C68 Contract Tracker; L7878-L7919 C68 Contract Final Operator Validation; L7920-L7929 C69 Production Deployment Prep / Bridge Review Contract; L7930-L7977 C69 Contract Final Operator Validation; L8060-L8067 C71 Shadow-Read / Dry-Run Runtime Validation Contract; L8068-L8121 C71 Contract Final Operator Validation; L8122-L8152 C72 Contract â€” Controlled Opt-In Runtime Bridge Validation; L10856-L10882 C111/C112 Boundary Clarification - 2026-06-30; L13545-L13580 C150 Weekly Swing Watchlist Production Live Runtime Activation Final Execution Contract; L13825-L13856 C158 Weekly Swing Watchlist Production Live Runtime Controlled Output Publication Boundary Review Contract; L15521-L15550 C165 Weekly Swing Watchlist Production Live Runtime PLAN/CONFIRM Controlled Rollout Execution Contract; L15950-L15972 C171 Final Bounded Remediation and Closure Contract; L15973-L15996 C171 Final Failed/Not-Ready Closure Contract; L15997-L16008 C171 Final Closure Summary CSV Parsing Contract
> **Extract Body SHA1:** `4369409E9D975E26584B526188FDC9480B15C706`
> **Current Authority:** NO

The body below is an exact preservation copy of the registered source sections. It is historical context only.

---

# Watchlist Lumen Contract Tracker

## Document Purpose

Dokumen ini melacak kontrak perilaku yang harus dipenuhi selama implementasi watchlist di Lumen.

Dokumen ini bukan owner business rule. Kontrak di sini harus ditelusuri ke:

- `docs/watchlist/system/policy.md`;
- `docs/watchlist/system/policies/weekly_swing/**`;
- `docs/watchlist/system/implementation/weekly_swing/**` sebagai translation guidance;
- owner upstream market-data untuk producer-facing consumer read contract.

## PRIOR SESSION - C57 REGIME FIELD RECONSTRUCTION CONTINUATION

Session:
`WATCHLIST - C57 REGIME FIELD RECONSTRUCTION CONTINUATION IS ONLY`

Current status:

`C57_SOURCE_IMPLEMENTED / C57_COMMAND_REGISTERED / C57_TESTS_PASS / FULL_WATCHLIST_PHPUNIT_PASS / C57_RUNTIME_COMPLETED / C57_REGIME_FIELD_RECONSTRUCTION_CONTINUATION_IS_ONLY_COMPLETED / C56_C55_C54_C53_C52_LOCKED_LINEAGE_PASS / MARKET_INDEX_REGIME_FIELDS_RECONSTRUCTED / REGIME_FULLY_EVALUABLE / CONCENTRATION_LOSS_CLUSTER_GAP_REMAINS / NO_OOS_TUNING / NO_OOS_PROOF / NO_PRODUCTION_CATALOG / NOT_PRODUCTION_READY / C58_LOSS_CLUSTER_CONCENTRATION_REDESIGN_CONTINUATION_IS_ONLY_REQUIRED`.

C55 contract status:

- `WL-CONTRACT-C55-001`: PASS. C55 is IS-only rolling stability redesign continuation and does not perform OOS proof, OOS tuning, production promotion, or catalog promotion.
- `WL-CONTRACT-C55-002`: PASS. C54, C53, and C52 artifact stable hashes and file SHA1 locks match the expected lineage.
- `WL-CONTRACT-C55-003`: PASS. Near-pass failed rolling windows and C53 adverse months remain diagnostic-only and were not converted into exclusion rules.
- `WL-CONTRACT-C55-004`: PASS. C55 writes candidate replay, concentration/dependency, rolling, LOO, regime robustness, material difference, source reconstruction bias, scorecard, and C56 readiness layers.
- `WL-CONTRACT-C55-005`: PASS. Operator validation executed: PHPUnit C55 `OK (9 tests, 293 assertions)`, full Watchlist PHPUnit `OK (786 tests, 15445 assertions)`, and C55 runtime completed with artifact hash `a4145d6f356e678d0dadf95be5d356198ebfed79`.
- `WL-CONTRACT-C55-006`: NOT_READY. `production_ready=false`, `candidate_ready_for_c56_count=0`, `rolling_validation_pass_candidate_count=0`, and `concentration_validation_pass_candidate_count=0`.

C55 validation status:

```text
PHPUNIT_C55=PASS
PHPUNIT_C55_RESULT=OK (9 tests, 293 assertions)
FULL_WATCHLIST_PHPUNIT=PASS
FULL_WATCHLIST_PHPUNIT_RESULT=OK (786 tests, 15445 assertions)
C55_RUNTIME=COMPLETED
C55_FINAL_STATUS=C55_ROLLING_STABILITY_REDESIGN_CONTINUATION_IS_ONLY_COMPLETED
C55_ARTIFACT_HASH=a4145d6f356e678d0dadf95be5d356198ebfed79
C55_FILE_SHA1=18875FCAD7FD7CDA6607BB09A60917E853E68D2B
DIAGNOSTIC_CONCLUSION=C55_ROLLING_STABILITY_GAP_REMAINS
NEXT_STEP=C56_ROLLING_STABILITY_REDESIGN_CONTINUATION_IS_ONLY
PRODUCTION_READY=0
DIRECT_OOS_PROOF_RECOMMENDED=false
OOS_PROOF_UNLOCKED=false
```

## WL-CONTRACT-012 â€” PORTFOLIO AWARENESS BOUNDARY

Contract ID:
`WL-CONTRACT-012`

Title:
`PORTFOLIO AWARENESS BOUNDARY`

Status:
`NOT_STARTED`

Owner docs:

- `docs/watchlist/README.md`
- `docs/watchlist/audit/WATCHLIST_SCOPE_LOCK.md`
- `docs/watchlist/system/policy.md`
- `docs/watchlist/system/policies/weekly_swing/22_WS_RECOMMENDATION_OVERVIEW.md`
- `docs/watchlist/system/policies/weekly_swing/23_WS_RECOMMENDATION_INPUT_OUTPUT_CONTRACT.md`

Implementation files:
`NOT_STARTED`

Tests:
`NOT_STARTED`

Runtime proof:
`NOT_STARTED`

Current gaps:

- No portfolio-aware integration exists.

Acceptance criteria:

- Portfolio integration does not alter market-data.
- Clear boundary exists between signal, position awareness, and execution decision.
- Watchlist remains suggestion-only and does not execute orders.

Last update:
`2026-05-28`

---

## OOS Post-Deployment Regression Contract Correction â€” 2026-06-10

Operator execution proved the 24-row canonical database seed and param-grid catalog tests pass, then exposed three parity regressions: stale static-guard cardinality `18`, missing strategy bootstrap ATR/RR defaults, and runtime metadata not rebound onto returned strategy payloads before freeze.

Contract impact:

- `WL-CONTRACT-007`: parameter traceability now uses one cardinality source (`CATALOG_COUNT=24`), exact persisted-set validation, and non-null bootstrap risk defaults.
- `WL-CONTRACT-008`: trade candidates and artifacts consistently expose ATR/RR and exact published-price runtime semantics.
- `WL-CONTRACT-009`: runtime metadata binding occurs before the frozen strategy hash and before future-price access.
- `WL-CONTRACT-010`: catalog/SQL/test cardinality parity no longer depends on duplicated literals; deterministic payload hashing includes the bound runtime metadata.
- `WL-CONTRACT-013`: persisted grid extras/missing rows fail closed with `WS_BT_PARAM_GRID_PERSISTED_SET_MISMATCH`.
- `WL-CONTRACT-014`: owner contract, implementation guidance, tests, and trackers are synchronized.
- `WL-CONTRACT-015`: remains `PARTIAL / NOT_READY` pending supported operator PHPUnit and OOS rerun.

No contract is promoted to `LOCKED`. Promotion remains `NOT_ELIGIBLE â€” corrected OOS proof missing`.

## OOS Grid Cross-Field Paramset Contract Correction â€” 2026-06-10

Operator full-window execution proved the memory-safe runtime and 24-row grid load, but aggregate IS failures included `WATCHLIST_BACKTEST_SOURCE_NOT_READY`. The cause was a row-projection defect: strict `max_atr14_pct` values were merged with a wider default ideal ATR band.

Contract impact:

- `WL-CONTRACT-007`: immutable paramset binding now includes deterministic `bt_grid_resolution` companion values and rule marker.
- `WL-CONTRACT-008`: strict canonical rows may no longer fail as source-not-ready solely due to contradictory default ATR companion values.
- `WL-CONTRACT-009`: companion-band projection is completed before replay and uses no OOS metrics or future prices.
- `WL-CONTRACT-010`: all 24 catalog rows are covered by deterministic cross-field invariants.
- `WL-CONTRACT-014`: policy, implementation guidance, checklist, tests, and trackers are synchronized.
- `WL-CONTRACT-015`: remains `PARTIAL / NOT_READY`; corrected full-window rerun and actual IS/OOS result are still required.

No metric acceptance threshold was weakened. No best-of-failed selection or promotion is allowed.

## Contract Append - 2026-06-15 C16 final operator validation

C16 is now closed as `C16_GRID_FAILED_IS_QUALITY` after operator runtime validation. Seed and diagnose-batch passed, IS calibration was deterministic, and OOS/prod readiness remain locked because no valid IS candidate exists.

## C37 Contract - IS Validation And Anti-Overfit Check

C37 contract scope:

```text
CONTRACT_CODE=C37_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK
SOURCE_ARTIFACT=storage/app/watchlist/backtest/c36-is-controlled-redesign-candidate-formation.json
EXPECTED_C36_HASH=8bc5198cf3b79fc9b58c39fc19f319826406b4b1
EXPECTED_C36_STATUS=C36_IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION_COMPLETED
EXPECTED_C36_CONCLUSION=C36_COMBINED_CANDIDATE_FORMED
OUTPUT_ARTIFACT=storage/app/watchlist/backtest/c37-is-validation-anti-overfit-check.json
```

Required C37 boundaries:

```text
IS_ONLY_VALIDATION=true
ANTI_OVERFIT_VALIDATION=true
C36_ARTIFACT_HASH_LOCK=true
C37_CANDIDATE_FROM_C36_CANDIDATE=true
NO_OOS_TUNING=true
NO_OOS_PROOF=true
NO_BEST_OF_OOS=true
NO_OOS_WINNER=true
NO_PROFILE_RESELECTION_FROM_OOS=true
NO_PRODUCTION_CATALOG=true
NO_PROMOTION=true
NO_PLAN_CONFIRM_MUTATION=true
NO_C01_TO_C36_ARTIFACT_MUTATION=true
CANDIDATE_IS_NOT_PRODUCTION=true
production_ready=false
oos_data_used_for_tuning=false
return_used_for_selection=false
future_path_used_for_selection=false
future_path_price_used_for_selection=false
profile_ret_net_used_for_selection=false
derived_mfe_mae_used_for_execution=false
```

C37 validation target contract:

```text
baseline_candidate_code=C36_BASELINE_C35_CURRENT_BRANCH_BEHAVIOR
target_candidate_code=C36_COMBINED_G21_REDESIGN_PLUS_G16_COMPARATOR
target_candidate_is_not_production=true
candidate_must_come_from_c36_candidate=true
candidate_may_advance_to_C38_OOS_only_if_anti_overfit_passes=true
```

C37 output contract result:

```text
full_is_validation=present
yearly_validation=present
rolling_window_validation=present
bad_month_like_stress_validation=present
non_bad_month_validation=present
ticker_concentration_validation=present
branch_concentration_validation=present
month_coverage_validation=present
downside_stability_validation=present
candidate_comparison_table=present
anti_overfit_summary=present
candidate_safety_audit=present
not_evaluable_reasons=present
```

C37 validation status:

```text
C37_IMPLEMENTATION_STATUS=IMPLEMENTED
PHPUNIT_C37=PASS:OK (17 tests, 343 assertions)
FULL_WATCHLIST_PHPUNIT=PASS:OK (561 tests, 12153 assertions)
ARTISAN_C37_RUNTIME=COMPLETED
C37_FINAL_STATUS=C37_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_COMPLETED
C37_ARTIFACT_HASH=5938e353296cb2188b6668093522d0b40d6cb9d2
C37_FILE_SHA1=C17254C01D2405DE8F77999DD7131AEE0663A287
```

C36 lock result:

```text
expected_c36_hash=8bc5198cf3b79fc9b58c39fc19f319826406b4b1
actual_c36_hash=8bc5198cf3b79fc9b58c39fc19f319826406b4b1
c36_hash_match=true
c36_status=C36_IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION_COMPLETED
c36_diagnostic_conclusion=C36_COMBINED_CANDIDATE_FORMED
```

C37 anti-overfit result:

```text
full_is_result=PASS
yearly_validation_result=PASS
rolling_validation_result=WARNING
bad_month_stress_result=PASS
normal_month_result=PASS
ticker_concentration_result=PASS
branch_concentration_result=WARNING
month_coverage_result=FAIL
downside_stability_result=PASS
overall_anti_overfit_result=FAIL
candidate_c37_decision=C37_CANDIDATE_REQUIRES_IS_REDESIGN_OR_EVIDENCE_EXPANSION
diagnostic_conclusion=C37_CANDIDATE_FAILED_ANTI_OVERFIT_CHECK
next_step_recommendation=C38_IS_REDESIGN_OR_EVIDENCE_EXPANSION_DIAGNOSTIC
production_ready=false
```

C37 contract decision: FAIL for anti-overfit advancement. C37 completed IS-only validation against the locked C36 candidate and did not use OOS tuning or run OOS proof. The candidate improves full/yearly/stress/downside metrics but fails month coverage with one zero-pick IS month and has a branch concentration warning. C37 does not unlock C38 OOS proof directly, does not create a production catalog, does not promote a candidate, and keeps `production_ready=false`.

---

## C40 Contract - IS Validation And Anti-Overfit Check For C39 Guarded Candidate

C40 contract scope:

```text
CONTRACT_CODE=C40_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C39_GUARDED_CANDIDATE
SOURCE_ARTIFACT=storage/app/watchlist/backtest/c39-is-controlled-redesign-with-coverage-and-branch-diversification-guards.json
EXPECTED_C39_HASH=504aaa061054ed2771ed08294d8a0570f08e18db
EXPECTED_C39_STATUS=C39_IS_CONTROLLED_REDESIGN_WITH_COVERAGE_AND_BRANCH_DIVERSIFICATION_GUARDS_COMPLETED
EXPECTED_C39_CONCLUSION=C39_GUARDED_IS_CANDIDATE_FORMED
EXPECTED_C39_NEXT_STEP=C40_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C39_GUARDED_CANDIDATE
OUTPUT_ARTIFACT=storage/app/watchlist/backtest/c40-is-validation-and-anti-overfit-check-for-c39-guarded-candidate.json
```

Required C40 boundaries:

```text
IS_ONLY_VALIDATION=true
ANTI_OVERFIT_VALIDATION=true
C39_ARTIFACT_HASH_LOCK=true
C40_CANDIDATE_FROM_C39_GUARDED_CANDIDATE=true
NO_OOS_TUNING=true
NO_OOS_PROOF=true
NO_BEST_OF_OOS=true
NO_OOS_WINNER=true
NO_PROFILE_RESELECTION_FROM_OOS=true
NO_PRODUCTION_CATALOG=true
NO_PROMOTION=true
NO_PLAN_CONFIRM_MUTATION=true
NO_C01_TO_C39_ARTIFACT_MUTATION=true
production_ready=false
oos_data_used_for_tuning=false
return_used_for_selection=false
future_path_used_for_selection=false
future_path_price_used_for_selection=false
profile_ret_net_used_for_selection=false
derived_mfe_mae_used_for_execution=false
```

C40 validation target contract:

```text
baseline_candidate_code=C39_BASELINE_C36_CURRENT_BRANCH_BEHAVIOR
target_candidate_code=C39_COVERAGE_AND_BRANCH_GUARD_G16_PLUS_METADATA_G21_MONTHLY_QUOTA
target_candidate_is_not_production=true
candidate_must_come_from_c39_best_candidate=true
candidate_may_advance_to_oos_only_if_anti_overfit_passes=true
```

C40 output contract result:

```text
full_is_validation=present
yearly_validation=present
rolling_window_validation=present
bad_month_like_stress_validation=present
non_bad_month_validation=present
ticker_concentration_validation=present
branch_concentration_validation=present
month_coverage_validation=present
downside_stability_validation=present
candidate_comparison_table=present
anti_overfit_summary=present
candidate_safety_audit=present
not_evaluable_reasons=present
```

C40 validation status:

```text
C40_IMPLEMENTATION_STATUS=IMPLEMENTED
PHPUNIT_C40=PASS:OK (16 tests, 176 assertions)
FULL_WATCHLIST_PHPUNIT=PASS:OK (609 tests, 12640 assertions)
ARTISAN_C40_RUNTIME=COMPLETED
C40_FINAL_STATUS=C40_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C39_GUARDED_CANDIDATE_COMPLETED
C40_ARTIFACT_HASH=0b40ee2464ed820d47ad0b83acbacd78b440d5bd
C40_FILE_SHA1=306E01AD1274944991F1AFE6CFEBBDB3C0E06BFC
```

C39 lock result:

```text
expected_c39_hash=504aaa061054ed2771ed08294d8a0570f08e18db
actual_c39_hash=504aaa061054ed2771ed08294d8a0570f08e18db
c39_hash_match=true
c39_status=C39_IS_CONTROLLED_REDESIGN_WITH_COVERAGE_AND_BRANCH_DIVERSIFICATION_GUARDS_COMPLETED
c39_diagnostic_conclusion=C39_GUARDED_IS_CANDIDATE_FORMED
c39_next_step=C40_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C39_GUARDED_CANDIDATE
```

C40 anti-overfit result:

```text
full_is_result=PASS
yearly_validation_result=PASS
rolling_validation_result=WARNING
bad_month_stress_result=PASS
normal_month_result=WARNING
ticker_concentration_result=PASS
branch_concentration_result=PASS
month_coverage_result=PASS
downside_stability_result=PASS
overall_anti_overfit_result=WARNING
candidate_c40_decision=C40_CANDIDATE_REQUIRES_REVIEW_BEFORE_OOS
diagnostic_conclusion=C40_CANDIDATE_WARNING_REQUIRES_REVIEW_BEFORE_OOS
next_step_recommendation=C41_IS_REVIEW_OR_EVIDENCE_EXPANSION_BEFORE_OOS
production_ready=false
```

C40 contract decision: WARNING for anti-overfit advancement. C40 completed IS-only validation against the locked C39 guarded candidate and did not use OOS tuning or run OOS proof. The candidate passes full/yearly/stress/ticker/branch/month-coverage/downside layers and has no failed layers, but rolling and non-bad-month warnings remain. C40 does not unlock direct OOS proof, does not promote a catalog, and keeps `production_ready=false`.

---

## C45 Contract - IS Validation and Anti-Overfit Check for C44 Refinement

```text
INPUT_C44_HASH_LOCK=606cd3109371b0d99419082daee18ff65f1cd99b
C44_TARGET_SELECTION_RECONSTRUCTED=true
C44_TARGET_ROW_COUNTS_MUST_MATCH=true
IS_ONLY_VALIDATION=true
FULL_IS_YEARLY_ROLLING_VALIDATION=true
BAD_AND_NON_BAD_MONTH_VALIDATION=true
TICKER_BRANCH_COVERAGE_DOWNSIDE_VALIDATION=true
RETURN_USED_FOR_SELECTION=false
FUTURE_PATH_USED_FOR_SELECTION=false
OOS_DATA_USED_FOR_TUNING=false
NO_OOS_PROOF=true
NO_OOS_UNLOCK=true
NO_PRODUCTION_CATALOG=true
NO_PROMOTION=true
HUMAN_REVIEW_REQUIRED_BEFORE_OOS=true
production_ready=false
```

Validated result:

```text
C45_STATUS=C45_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C44_REFINEMENT_COMPLETED
OVERALL_ANTI_OVERFIT_RESULT=WARNING
FULL_IS_RESULT=PASS
YEARLY_RESULT=WARNING
ROLLING_RESULT=WARNING
NON_BAD_MONTH_RESULT=WARNING
FAILED_LAYERS=0
DIAGNOSTIC_CONCLUSION=C45_C44_REFINEMENT_WARNING_REQUIRES_REVIEW_BEFORE_OOS
NEXT_STEP=C46_IS_REVIEW_OR_EVIDENCE_EXPANSION_BEFORE_OOS
DIRECT_OOS_PROOF_RECOMMENDED=false
OOS_PROOF_UNLOCKED=false
ARTIFACT_HASH=47970ba6e772bcf7fec68f306883f9f3d6cdd976
FILE_SHA1=CF7D7D78103B543814C1B84F29B33AEA3E4FAF78
```

## C57 Contract â€” Regime Field Reconstruction Continuation IS Only

- contract_code=C57_REGIME_FIELD_RECONSTRUCTION_CONTINUATION_IS_ONLY
- status=DONE_OPERATOR_VALIDATED
- production_ready=false

### Source artifact locks

- C56 artifact hash lock: `f7edab247dc824dcd33a15f00575dd04f76f4786`
- C55 artifact hash lock: `a4145d6f356e678d0dadf95be5d356198ebfed79`
- C55 file SHA1 lock: `18875FCAD7FD7CDA6607BB09A60917E853E68D2B`
- C54 artifact hash lock: `8c71a4352a1024dbe985e0f0bb6329f5e1545150`
- C54 file SHA1 lock: `75410BB1A30A32FFFF9661CAD6818C13E044F7E5`
- C53 artifact hash lock: `6a1749d723e16b7efdb8aa1d7510388a9475d12c`
- C53 file SHA1 lock: `E35FEFB78B6F1931E54169BD8AABE286CB6F08C2`
- C52 artifact hash lock: `5dbe51c9d18b175e65cddb60336baf43d6833b72`
- C52 file SHA1 lock: `DADE6518BFF3912D8A43D7C67073FB803F7CF878`

### Locked lineage rule

C57 may use only C56/C55/C54/C53/C52 as locked lineage. It must not mutate C01-C56 artifacts and must not retry or rerun prior OOS proof flows.

### Market index source discovery contract

Market index source discovery must be read-only and must record all attempted sources:

- `market_benchmark_indicators`
- `market_benchmark_bars`
- ticker-backed `eod_indicators`
- ticker-backed `eod_bars`
- `market_calendar` previous trading-day fallback
- published EOD read model if available
- artifact fallback only if as-of-safe and IS-only

### Market index reconstruction contract

- Reconstruction must be as-of-safe.
- Reconstruction must not use `MAX(trade_date)` as a latest-row selector.
- Reconstruction must not use future lookup.
- Reconstruction must not request OOS rows.
- Reconstruction must use exact signal/trade date first, then previous published trading day not after the row date.
- If indicators are missing, benchmark bars may be used to compute `market_index_roc20` and `market_index_ma20_slope_pct` from historical bars only.

### Candidate contract

- Anchor candidates must come from C56.
- Comparator-only candidates must stay comparator-only.
- Candidate is not production.
- No production candidate may be declared.
- Failed rolling windows must not become exclusion rules.
- Adverse months must not become exclusion rules.
- No ticker exclusion rule may be derived from failure attribution.
- No sector exclusion rule may be derived from failure attribution.

### OOS and production contract

- no OOS tuning
- no OOS proof
- no OOS proof rerun
- no best-of-OOS
- no OOS winner
- no candidate reselection from OOS
- no profile reselection from OOS
- no OOS return selection
- no production catalog
- no promotion
- no PLAN/CONFIRM mutation
- production_ready remains false
- return/future path not used for selection
- OOS data may not be used for selection/tuning/proof
- C57 must not recommend OOS proof

### Allowed C58 recommendations

C57 may recommend only:

- `C58_IS_VALIDATION_AND_PRE_OOS_LOCK_REVIEW_FOR_C57_RECONSTRUCTION`
- `C58_LOSS_CLUSTER_CONCENTRATION_REDESIGN_CONTINUATION_IS_ONLY`
- `C58_MARKET_INDEX_EVIDENCE_EXPANSION_OR_SOURCE_RECONSTRUCTION_IS_ONLY`
- `C58_REGIME_FIELD_RECONSTRUCTION_CONTINUATION_IS_ONLY`
- `C58_ROLLING_STABILITY_RECHECK_AFTER_REGIME_RECONSTRUCTION_IS_ONLY`
- `C58_SHARED_CORE_REVERSION_REDESIGN_REQUIRED`
- `C58_REDESIGN_OR_RECALIBRATION_REQUIRED_IS_ONLY`

## C57 fix2 contract clarification

C57 market-index reconstruction must support the concrete benchmark schema observed in the operator DB:

- benchmark identifier column: `benchmark_code`
- benchmark date column: `trade_date`
- market-index ROC20 column: `roc_20`
- market-index MA20 slope column: `ma20_slope_pct`
- benchmark bars close fallback: `adjusted_close` or `close_price`
- calendar date fallback column: `cal_date` when `trade_date` is absent

C57 must derive required dates from locked IS source rows, including C28 `pick_diagnostic_rows`, when runtime options do not inject `source_rows`. `required_date_count=0` is invalid when locked source rows are available.

## C57 final contract validation

C57 contract status after operator validation:

- `WL-CONTRACT-C57-001`: PASS. C57 remains IS-only and performed no OOS tuning, OOS proof, production rollout, catalog promotion, PLAN/CONFIRM mutation, or C01-C56 artifact mutation.
- `WL-CONTRACT-C57-002`: PASS. C56/C55/C54/C53/C52 artifact hash and file SHA1 locks matched the expected lineage.
- `WL-CONTRACT-C57-003`: PASS. Market-index source discovery selected `market_benchmark_indicators` with identifier `IHSG` using read-only as-of-safe lookup.
- `WL-CONTRACT-C57-004`: PASS. `market_index_roc20` was reconstructed `15750/15750` and `market_index_ma20_slope_pct` was reconstructed `15750/15750`.
- `WL-CONTRACT-C57-005`: PASS. Regime fields are fully evaluable: `required_field_count=9`, `evaluable_field_count=9`, `regime_fully_evaluable=true`.
- `WL-CONTRACT-C57-006`: PASS. Source bias validation remains pass with no `MAX(trade_date)`, no future lookup, no OOS rows, and no return/path/OOS-return selection.
- `WL-CONTRACT-C57-007`: NOT_READY. Concentration/loss-cluster remains failed for all primary anchors and `candidate_ready_for_c58_count=0`.
- `WL-CONTRACT-C57-008`: NOT_READY. Regime robustness is now fully evaluable but `candidate_regime_pass_count=0`.
- `WL-CONTRACT-C57-009`: PASS. C57 recommends only the IS-only next step `C58_LOSS_CLUSTER_CONCENTRATION_REDESIGN_CONTINUATION_IS_ONLY`.

C57 final validation markers:

```text
PHPUNIT_C57=PASS OK (10 tests, 185 assertions)
FULL_WATCHLIST_PHPUNIT=PASS OK (805 tests, 15967 assertions)
C57_RUNTIME=COMPLETED
C57_FINAL_STATUS=C57_REGIME_FIELD_RECONSTRUCTION_CONTINUATION_IS_ONLY_COMPLETED
C57_ARTIFACT_HASH=71230896c2121fcfedddf36dd54c9c03ad462b4d
C57_FILE_SHA1=50272917A107E304F8EEEB874DBC02A881DB0C31
DIAGNOSTIC_CONCLUSION=C57_LOSS_CLUSTER_GAP_REMAINS
NEXT_STEP=C58_LOSS_CLUSTER_CONCENTRATION_REDESIGN_CONTINUATION_IS_ONLY
PRODUCTION_READY=0
DIRECT_OOS_PROOF_RECOMMENDED=false
OOS_PROOF_UNLOCKED=false
```

## WATCHLIST_DB_DICTIONARY_REQUIRED_CONTRACT

Status: `DONE_DOCS_ONLY`

Last updated: 2026-06-22

Related implementation: `DB Dictionary and Field Usage Governance`

Contract:

- Watchlist database-connected sessions must read:
  - `docs/market_data/db/MARKET_DATA_DICTIONARY.md`
  - `docs/db/DATABASE_DICTIONARY_USAGE_RULE.md`
  - `docs/watchlist/system/db/WATCHLIST_DB_DICTIONARY.md`
- Prompt generation must include the database dictionary requirement when touching any DB-backed data.
- Implementations must identify touched tables, date keys, identifier keys, field roles, as-of safety, and selection/evaluation boundary before coding.
- Missing dictionary coverage must block or trigger a dictionary update.
- OOS rows/returns/bad months, future paths, and evaluation metrics remain forbidden as IS selection inputs.

Validation:

- Docs-only contract and prompt standards updated.

## C65 Contract Final Operator Validation

Status: `IMPLEMENTED_OPERATOR_VALIDATED`

```text
PHPUNIT_C65=PASS
PHPUNIT_C65_RESULT=OK (28 tests, 193 assertions)
FULL_WATCHLIST_PHPUNIT=PASS
FULL_WATCHLIST_PHPUNIT_RESULT=OK (1024 tests, 18664 assertions)
C65_RUNTIME=COMPLETED
C65_FINAL_STATUS=C65_PRODUCTION_PRE_LOCK_REVIEW_PASSED_PRIMARY_AND_BACKUP
C65_REASON_CODE=C65_PRODUCTION_PRE_LOCK_REVIEW_PASSED_PRIMARY_AND_BACKUP
C65_ARTIFACT_HASH=f08da5acc87ccbe0d88c39423c4321496230b01b
C65_FILE_SHA1=115201C1F44C7C420ABA3251435F21B870EF9AE6
CANDIDATE_READY_FOR_C66_COUNT=2
C66_RECOMMENDATION=C66_PRODUCTION_LOCK_REVIEW
PRODUCTION_READY=false
PRODUCTION_CATALOG_ALLOWED=false
PRODUCTION_DEPLOYMENT_ALLOWED=false
```

C65 contract conclusion: operator validation passed. C65 locks the production pre-lock review result for E02 primary and B01 backup, keeps A01 comparator-only, keeps all production mutation gates closed, and only authorizes `C66_PRODUCTION_LOCK_REVIEW` as the next review step. C65 is not production-ready by itself.

---

## C66 Contract Final Operator Validation

Status: `IMPLEMENTED_OPERATOR_VALIDATED`

```text
PHPUNIT_C66=PASS
PHPUNIT_C66_RESULT=OK (28 tests, 214 assertions)
FULL_WATCHLIST_PHPUNIT=PASS
FULL_WATCHLIST_PHPUNIT_RESULT=OK (1052 tests, 18878 assertions)
C66_RUNTIME=COMPLETED
C66_FINAL_STATUS=C66_PRODUCTION_LOCK_REVIEW_PASSED_PRIMARY_AND_BACKUP
C66_REASON_CODE=C66_PRODUCTION_LOCK_REVIEW_PASSED_PRIMARY_AND_BACKUP
C66_ARTIFACT_HASH=9ef0c2eed94f2ac9e6e8e348e93774c563f8e6d4
C66_FILE_SHA1=11936FC807140E9B0A18FD00B543B03C8AE2950C
PRODUCTION_LOCK_REVIEW_EXECUTED=true
PRODUCTION_LOCK_REVIEW_PASS=true
PRODUCTION_CATALOG_LOCK_ALLOWED=true
PRODUCTION_CATALOG_ACTIVATION_ALLOWED=false
PRODUCTION_DEPLOYMENT_ALLOWED=false
PLAN_CONFIRM_MUTATION_ALLOWED=false
CANDIDATE_READY_FOR_C67_COUNT=2
C67_RECOMMENDATION=C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW
DOMINANT_BLOCKER=NONE
```

C66 contract conclusion: operator validation passed. C66 locks E02 as primary production catalog candidate and B01 as backup production catalog candidate at artifact-decision level only. A01 remains comparator-only and cannot be promoted. C66 does not authorize production catalog activation, production deployment, or PLAN/CONFIRM mutation. The only allowed next contract is `C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW`.

## C68 Contract Tracker

C68 contract: production catalog activation execution review only. Input lock is C67 artifact hash 5e3ba8ac20c810a36a7928ad1f201c82143ac72f and file SHA1 CB98A7B5B4B5F0CCCEDEF0C7B5BDC8CB3FE940E6. Output artifact is storage/app/watchlist/backtest/c68-production-catalog-activation-execution-review.json. Controlled activation record is not runtime consumable by PLAN/CONFIRM. production_catalog_runtime_wired=false, production_deployment_allowed=false, production_deployment_executed=false, plan_confirm_mutation_allowed=false, plan_confirm_mutated=false.

---

## C68 Contract Final Operator Validation

Status: `IMPLEMENTED_OPERATOR_VALIDATED`

```text
PHPUNIT_C68=PASS
PHPUNIT_C68_RESULT=OK (22 tests, 241 assertions)
FULL_WATCHLIST_PHPUNIT=PASS
FULL_WATCHLIST_PHPUNIT_RESULT=OK (1093 tests, 19331 assertions)
C68_RUNTIME=COMPLETED
C68_FINAL_STATUS=C68_PRODUCTION_CATALOG_ACTIVATION_EXECUTION_REVIEW_PASSED_PRIMARY_AND_BACKUP
C68_REASON_CODE=C68_PRODUCTION_CATALOG_ACTIVATION_EXECUTION_REVIEW_PASSED_PRIMARY_AND_BACKUP
C68_ARTIFACT_HASH=54145854758e22115e4b65a297e4c157d94c638d
C68_FILE_SHA1=209E3225F37015DA348EC2DA9A0D6A3FFCC6E4F7
```

Contract validation result:

```text
C68_CONTRACT_ACCEPTED=true
C67_TO_C60_LINEAGE_LOCK_VALID=true
CANDIDATE_SCOPE_FREEZE_VALID=true
PRIMARY_E02_ACTIVATION_EXECUTION_PASS=true
BACKUP_B01_ACTIVATION_EXECUTION_PASS=true
A01_REMAINS_COMPARATOR_ONLY=true
A01_PROMOTED=false
CONTROLLED_ACTIVATION_RECORD_CREATED=true
CONTROLLED_ACTIVATION_RECORD_RUNTIME_CONSUMABLE=false
CONTROLLED_ACTIVATION_RECORD_WIRED_TO_PLAN_CONFIRM=false
PRODUCTION_CATALOG_RUNTIME_WIRED=false
PRODUCTION_DEPLOYMENT_ALLOWED=false
PRODUCTION_DEPLOYMENT_EXECUTED=false
PLAN_CONFIRM_MUTATION_ALLOWED=false
PLAN_CONFIRM_MUTATED=false
PLAN_CONFIRM_RUNTIME_READS_ACTIVATED_CATALOG=false
```

C68 contract conclusion: operator validation passed. C68 creates only a controlled production catalog activation execution artifact/record for E02 primary and B01 backup. It does not authorize live runtime wiring, production deployment, or PLAN/CONFIRM mutation. The only allowed next contract is `C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW`.


---

## C69 Production Deployment Prep / Bridge Review Contract

C69 contract is non-runtime bridge readiness only. E02 remains primary deployment bridge candidate, B01 remains backup deployment bridge candidate, and A01 remains comparator-only and cannot be promoted.

C69 validates the current PLAN/CONFIRM runtime path and proposes a future C70 bridge behind feature flag `watchlist.production_catalog_bridge.enabled` and kill switch `watchlist.production_catalog_bridge.kill_switch`. Default is OFF. Rollback source is current PLAN/CONFIRM behavior.

C69 pass is not production deployment and not PLAN/CONFIRM rollout.

---

## C69 Contract Final Operator Validation

Status: `IMPLEMENTED_OPERATOR_VALIDATED`

```text
PHPUNIT_C69=PASS
PHPUNIT_C69_RESULT=OK (26 tests, 318 assertions)
FULL_WATCHLIST_PHPUNIT=PASS
FULL_WATCHLIST_PHPUNIT_RESULT=OK (1119 tests, 19649 assertions)
C69_RUNTIME=COMPLETED
C69_FINAL_STATUS=C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_PASSED_PRIMARY_AND_BACKUP
C69_REASON_CODE=C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_PASSED_PRIMARY_AND_BACKUP
C69_ARTIFACT_HASH=477a279a1f35cfafb811f5984e7a329f72d3f08e
C69_FILE_SHA1=82BAF5F192AF0C4680303F7A0409D0EA446A8192
```

Contract validation result:

```text
C69_CONTRACT_ACCEPTED=true
C68_TO_C60_LINEAGE_LOCK_VALID=true
PRIMARY_E02_BRIDGE_PREP_PASS=true
BACKUP_B01_BRIDGE_PREP_PASS=true
A01_REMAINS_COMPARATOR_ONLY=true
A01_PROMOTED=false
BRIDGE_CONTRACT_REVIEW_PASS=true
PLAN_CONFIRM_WIRING_READINESS_PASS=true
FEATURE_FLAG_KILL_SWITCH_REVIEW_PASS=true
ROLLBACK_PLAN_PASS=true
SMOKE_TEST_PLAN_PASS=true
SHADOW_READ_DRY_RUN_PLAN_PASS=true
BAD_MONTH_RISK_RETAINED=true
WEAK_REGIME_RISK_RETAINED=true
SOURCE_BIAS_SHARED_CORE_RISK_RETAINED=true
PRODUCTION_DEPLOYMENT_PREP_ALLOWED=true
PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_ALLOWED=true
PLAN_CONFIRM_WIRING_PREP_ALLOWED=true
PRODUCTION_CATALOG_RUNTIME_WIRED=false
PRODUCTION_DEPLOYMENT_ALLOWED=false
PRODUCTION_DEPLOYMENT_EXECUTED=false
PLAN_CONFIRM_MUTATION_ALLOWED=false
PLAN_CONFIRM_MUTATED=false
PLAN_CONFIRM_RUNTIME_READS_ACTIVATED_CATALOG=false
```

C69 contract conclusion: operator validation passed. C69 authorizes only controlled non-runtime bridge/prep readiness for C70 review. It does not authorize live deployment, PLAN/CONFIRM mutation, or runtime catalog consumption. The only allowed next contract is `C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW`.

## C71 Shadow-Read / Dry-Run Runtime Validation Contract

C71 contract is isolated shadow-read / dry-run runtime validation only. It validates the locked controlled production catalog can be read and evaluated safely in a non-live validation path. It does not authorize live production deployment, PLAN/CONFIRM mutation, or PLAN/CONFIRM runtime catalog consumption.

C71 locks the C70 final artifact, validates C70 readiness through nested `c71_readiness_decision.*`, validates C70 â†’ C60 lineage, keeps E02 as primary, B01 as backup, and A01 as comparator-only.

C71 pass means readiness for `C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION` only.

## C71 Contract Final Operator Validation

Status: `IMPLEMENTED_OPERATOR_VALIDATED`

```text
PHPUNIT_C71=PASS
PHPUNIT_C71_RESULT=OK (22 tests, 275 assertions)
FULL_WATCHLIST_PHPUNIT=PASS
FULL_WATCHLIST_PHPUNIT_RESULT=OK (1163 tests, 20178 assertions)
C71_RUNTIME=COMPLETED
C71_FINAL_STATUS=C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION_PASSED_PRIMARY_AND_BACKUP
C71_REASON_CODE=C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION_PASSED_PRIMARY_AND_BACKUP
C71_ARTIFACT_HASH=dee0b4e6a5a17dcb7c99eccf6f54832f88aefa1f
C71_FILE_SHA1=4F2D3C8AE01F3EB0CE60D820FA78BDBD2CA2ABDB
```

Contract validation result:

```text
C71_CONTRACT_ACCEPTED=true
C70_LOCK_VALID=true
C69_TO_C60_LINEAGE_LOCK_VALID=true
PRIMARY_E02_SHADOW_READ_DRY_RUN_RUNTIME_VALIDATION_PASS=true
BACKUP_B01_SHADOW_READ_DRY_RUN_RUNTIME_VALIDATION_PASS=true
A01_REMAINS_COMPARATOR_ONLY=true
A01_PROMOTED=false
A01_USED_AS_RUNTIME_FALLBACK=false
DEFAULT_OFF_FEATURE_FLAGS_PASS=true
KILL_SWITCH_FORCE_DISABLE_PROVEN=true
SHADOW_READ_PROOF_PASS=true
DRY_RUN_PROOF_PASS=true
BASELINE_PLAN_CONFIRM_HASH_UNCHANGED_PASS=true
PLAN_CONFIRM_OUTPUT_NON_MUTATION_PASS=true
FALLBACK_BEHAVIOR_RUNTIME_VALIDATION_PASS=true
AUDIT_OBSERVABILITY_PROOF_PASS=true
BAD_MONTH_RISK_RETAINED=true
WEAK_REGIME_RISK_RETAINED=true
SOURCE_BIAS_SHARED_CORE_RISK_RETAINED=true
PRODUCTION_CATALOG_RUNTIME_WIRED=false
SHADOW_READ_RUNTIME_ACTIVE=false
DRY_RUN_RUNTIME_ACTIVE=false
PRODUCTION_DEPLOYMENT_ALLOWED=false
PRODUCTION_DEPLOYMENT_EXECUTED=false
PLAN_CONFIRM_MUTATION_ALLOWED=false
PLAN_CONFIRM_MUTATED=false
PLAN_CONFIRM_RUNTIME_READS_ACTIVATED_CATALOG=false
LIVE_PLAN_CONFIRM_ROLLOUT_ALLOWED=false
LIVE_PLAN_CONFIRM_ROLLOUT_EXECUTED=false
C72_RECOMMENDATION=C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION
```

C71 contract conclusion: operator validation passed. C71 authorizes only readiness for C72 controlled opt-in runtime bridge validation. It does not authorize live production deployment, PLAN/CONFIRM mutation, or PLAN/CONFIRM runtime catalog consumption.

## C72 Contract â€” Controlled Opt-In Runtime Bridge Validation

Status: `OPERATOR_VALIDATED_ACCEPTED`

C72 contract is controlled opt-in runtime bridge validation only. It validates that the activated production catalog can be read through an explicit opt-in, default-off, kill-switch protected, auditable, non-mutating bridge proof in an isolated validation path.

C72 locks C71 final evidence, validates nested `c72_readiness_decision.*`, validates C71 â†’ C60 lineage, keeps E02 as primary, B01 as backup, and A01 as comparator-only. C72 does not authorize live production deployment, PLAN/CONFIRM mutation, PLAN/CONFIRM output changes, or PLAN/CONFIRM runtime catalog consumption.

```text
C72_CONTROLLED_OPT_IN_REQUIRED=true
C72_FEATURE_FLAG_DEFAULT_OFF=true
C72_CONTROLLED_OPT_IN_FEATURE_FLAG_DEFAULT_OFF=true
C72_KILL_SWITCH_REQUIRED=true
C72_BASELINE_PLAN_CONFIRM_NON_MUTATION_REQUIRED=true
C72_FALLBACK_BEHAVIOR_REQUIRED=true
C72_AUDIT_OBSERVABILITY_REQUIRED=true
A01_PROMOTED=false
A01_USED_AS_RUNTIME_FALLBACK=false
BAD_MONTH_RISK_RETAINED=true
WEAK_REGIME_RISK_RETAINED=true
SOURCE_BIAS_SHARED_CORE_RISK_RETAINED=true
PRODUCTION_CATALOG_RUNTIME_WIRED=false
CONTROLLED_OPT_IN_RUNTIME_BRIDGE_ACTIVE=false
PRODUCTION_DEPLOYMENT_EXECUTED=false
PLAN_CONFIRM_MUTATED=false
PLAN_CONFIRM_RUNTIME_READS_ACTIVATED_CATALOG=false
LIVE_PLAN_CONFIRM_ROLLOUT_EXECUTED=false
```

C72 pass means readiness for `C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION` only.

## C111/C112 Boundary Clarification - 2026-06-30

This contract boundary clarification records that C111 is the terminal final-closure point for the weekly swing watchlist non-live rehearsal handoff audit archive chain. C112 is a separate post-C111 production-phase transition gate and must not be interpreted as another audit archive continuation.

```text
C111_NON_LIVE_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSED=1
C111_NON_LIVE_AUDIT_ARCHIVE_TERMINAL=1
C111_NO_NEXT_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_REVIEW_REQUIRED=1
C112_SEPARATE_POST_C111_PRODUCTION_PHASE_TRANSITION_GATE=1
C112_NOT_AUDIT_ARCHIVE_CONTINUATION=1
C112_DOES_NOT_REOPEN_C111_FINAL_CLOSURE=1
C112_DOES_NOT_EXTEND_NON_LIVE_AUDIT_ARCHIVE_REVIEW=1
C112_PRODUCTION_PHASE_APPROVAL_IS_READINESS_ENTRY_ONLY=1
C112_PRODUCTION_READY=0
C112_PRODUCTION_RUNTIME_WIRING_ALLOWED=0
C112_PRODUCTION_RUNTIME_WIRING_EXECUTED=0
C112_PRODUCTION_DEPLOYMENT_ALLOWED=0
C112_PRODUCTION_DEPLOYMENT_EXECUTED=0
C112_PLAN_CONFIRM_MUTATION_ALLOWED=0
C112_WEEKLY_SWING_LIVE_OUTPUT_ENABLED=0
C112_OFFICIAL_WEEKLY_SWING_RECOMMENDATION_GENERATED=0
NEXT_AFTER_C111_NON_LIVE_AUDIT_ARCHIVE=STOP_OR_SEPARATE_PRODUCTION_PHASE_TRANSITION_GATE_ONLY
NEXT_AFTER_C112_IF_OPERATOR_CONTINUES_PRODUCTION_READINESS_PATH=C113_WEEKLY_SWING_WATCHLIST_PRODUCTION_READINESS_REVIEW
```

C111 remains the final close of the non-live audit archive. C112 only records a new production-phase approval for readiness review and does not cancel, reopen, weaken, or continue the C111 final-closed audit archive state.

## C150 Weekly Swing Watchlist Production Live Runtime Activation Final Execution Contract

C150 contract executes the production/live runtime activation state after C149 operator GO.
C150 requires explicit operator approval, activation reference, runtime bridge enablement, live output enablement, rollback confirmation, and kill-switch confirmation.
C150 activates the runtime bridge and weekly swing live output in the runtime state.
C150 does not generate or publish the official weekly swing recommendation list.
C150 does not mutate PLAN/CONFIRM.

```text
C150_CONTRACT_STATUS=C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION_PASSED_LIVE_RUNTIME_BRIDGE_ACTIVE_PRIMARY_AND_BACKUP
C150_ARTIFACT=storage/app/watchlist/backtest/c150-weekly-swing-watchlist-production-live-runtime-activation-final-execution.json
C150_ARTIFACT_HASH=0b3b5e57011d8d98fcd38c004fb8d94fb33ca9ad
C150_FILE_SHA1=E25A4E0DF40F9E01E6B3270F2AE2C5FF1CF0A500
C150_RUNTIME_STATE=storage/app/watchlist/runtime/weekly-swing-watchlist-production-live-runtime-activation-state.json
C150_RUNTIME_STATE_HASH=00cb935a8252efe340d5f6ec6ea6966d9645cff7
C150_RUNTIME_STATE_FILE_SHA1=17E41FFC5C6EE00CCCB4DF555A22EF192F2FCCF4
C149_LOCK_VALID=1
C149_OPERATOR_GO_NO_GO_VALID=1
PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTED=1
PRODUCTION_READY=1
PRODUCTION_CATALOG_RUNTIME_WIRED=1
PRODUCTION_RUNTIME_WIRING_EXECUTED=1
RUNTIME_BRIDGE_ACTIVE=1
WEEKLY_SWING_WATCHLIST_RUNTIME_ACTIVE=1
WEEKLY_SWING_WATCHLIST_LIVE_OUTPUT_ENABLED=1
WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATION_ALLOWED=1
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_GENERATED=0
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_PUBLISHED=0
WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATED=0
PLAN_CONFIRM_MUTATED=0
PLAN_CONFIRM_RUNTIME_READS_ACTIVATED_CATALOG=0
C150_NEXT_CONTRACT=C151_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_REVIEW
```

C150 contract permits runtime activation state execution only. It does not permit PLAN/CONFIRM mutation, official recommendation generation, publication, candidate rerank, A01 promotion, scoring mutation, or C60-C149 artifact mutation.

## C158 Weekly Swing Watchlist Production Live Runtime Controlled Output Publication Boundary Review Contract

C158 boundary contract locks the C157 GO finalization artifact.
C158 boundary requires C157 artifact hash, file SHA1, phase label, status, next recommendation, and ConvertFrom-Json compatibility to pass.
C158 boundary requires operator approval plus publication-boundary, controlled-publication-only, and PLAN/CONFIRM unchanged confirmations.
C158 boundary may only recommend the same-topic C158 controlled output publication execution stage next.
C158 boundary does not publish output, does not unlock unrestricted publication, and does not mutate PLAN/CONFIRM.

```text
C158_CONTRACT_TOPIC=C158_CONTROLLED_OUTPUT_PUBLICATION
C158_CONTRACT_STAGE=BOUNDARY_REVIEW
C158_CONTRACT_STATUS=C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_PASSED_READY_FOR_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_PRIMARY_AND_BACKUP
C158_BOUNDARY_ARTIFACT=storage/app/watchlist/backtest/c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-boundary-review.json
C158_BOUNDARY_ARTIFACT_HASH=f17826dd8eb388491be7ef94d18600647dbccc85
C158_BOUNDARY_FILE_SHA1=B61A0522835494811E3306ABDFE37639D5ED56C8
FOCUSED_PHPUNIT_C158_BOUNDARY=OK (28 tests, 119 assertions)
C157_LOCK_VALID=1
C157_GO_DECISION_FINALIZATION_VALID=1
CONTROLLED_OUTPUT_LOCK_VALID=1
CONTROLLED_OUTPUT_INTEGRITY_VALID=1
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_GENERATED=1
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_PUBLISHED=0
WEEKLY_SWING_WATCHLIST_PUBLICATION_ALLOWED=0
WEEKLY_SWING_WATCHLIST_CONTROLLED_PUBLICATION_ALLOWED_NEXT=1
WEEKLY_SWING_WATCHLIST_UNRESTRICTED_PUBLICATION_ALLOWED=0
PLAN_CONFIRM_MUTATED=0
PLAN_CONFIRM_RUNTIME_READS_ACTIVATED_CATALOG=0
C158_NEXT_CONTRACT=C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION
```

C158 boundary contract permits same-topic controlled output publication execution next only. It does not permit direct free publication, unrestricted publication, PLAN/CONFIRM mutation, candidate rerank, A01 promotion, scoring mutation, catalog selection change, C60-C157 artifact mutation, or controlled output artifact mutation.

## C165 Weekly Swing Watchlist Production Live Runtime PLAN/CONFIRM Controlled Rollout Execution Contract

C165 execution requires four locked sources plus explicit operator approval, catalog-read confirmation, controlled mutation confirmation, controlled-only confirmation, kill switch, rollback, and free-publication lock.
The execution may write only the dedicated controlled rollout state. It may set controlled PLAN/CONFIRM mutation, activated-catalog read, and controlled rollout to active, but must keep production config unchanged, unrestricted rollout disabled, free publication disabled, and A01 comparator-only.

```text
C165_CONTRACT_TOPIC=C165_PLAN_CONFIRM_CONTROLLED_ROLLOUT
C165_CONTRACT_STAGE=PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION
C165_CONTRACT_STATUS=C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION_PASSED_CONTROLLED_ROLLOUT_EXECUTED_READY_FOR_RESULT_REVIEW_PRIMARY_AND_BACKUP
C165_EXECUTION_ARTIFACT_HASH=73dc9758d1baad52e7a8e56f6e0058e99b9f71f7
C165_EXECUTION_FILE_SHA1=10B76E055119D1A9049F2D9EBA858E1B71A552BE
C165_ROLLOUT_STATE_HASH=3a8350955f6a1396f5225af3fddcfa31fa622904
C165_ROLLOUT_STATE_FILE_SHA1=4B58D3A17B56136CF02BE1635FB2F16F12831722
C165_ROLLOUT_STATE_RECORD_COUNT=2
FOCUSED_PHPUNIT_C165_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION=OK (32 tests, 116 assertions)
FULL_PHPUNIT_FILTER_C165=OK (64 tests, 223 assertions)
ALL_SOURCE_LOCKS_VALID=1
CONTROLLED_ROLLOUT_EXECUTED=1
PLAN_CONFIRM_MUTATED=1
PLAN_CONFIRM_RUNTIME_READS_ACTIVATED_CATALOG=1
LIVE_PLAN_CONFIRM_ROLLOUT_EXECUTED=1
PRODUCTION_CONFIG_MUTATED=0
UNRESTRICTED_ROLLOUT_ALLOWED=0
FREE_PUBLICATION_ALLOWED=0
A01_REMAINS_COMPARATOR_ONLY=1
C165_NEXT_CONTRACT=C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_RESULT_REVIEW
```

C165 remains in progress; execution does not advance the C-number.

## C171 Final Bounded Remediation and Closure Contract

```text
C171_FINAL_BOUNDED_REMEDIATION_EXACTLY_THREE_DRAFTS_REQUIRED=1
C171_FINAL_ANCHOR_EVAL_ID_REQUIRED=204
C171_FINAL_ANCHOR_PARAM_SET_ID_REQUIRED=11
C171_FINAL_SOURCE_PIPELINE_VERSION_REQUIRED=WS_C171_C01_TICK_RISK_GUARD_ENFORCEMENT_PIPELINE_V3
C171_FINAL_SOURCE_PIPELINE_HASH_REQUIRED=9e9933b363026623b7ab5629f3281fa680a53a2e
C171_FINAL_TICK_RISK_PRIMARY_DIRECTION_REOPEN_ALLOWED=0
C171_FINAL_TICKER_BLACKLIST_ALLOWED=0
C171_FINAL_MONTH_BLACKLIST_ALLOWED=0
C171_FINAL_FUTURE_RETURN_SELECTION_ALLOWED=0
C171_FINAL_CANONICAL_GATE_WEAKENING_ALLOWED=0
C171_FINAL_OOS_READ_ALLOWED=0
C171_FINAL_PROMOTION_ALLOWED=0
C171_FINAL_PLAN_ALLOWED=0
C171_FINAL_PRODUCTION_READY=0
C171_FINAL_ADDITIONAL_CANDIDATE_CATALOG_ALLOWED=0
C171_FINAL_IF_NO_CANONICAL_IS_PASS=C171_FAILED_NOT_READY_NO_FURTHER_REMEDIATION
C171_FINAL_IF_CANONICAL_IS_PASS=C171_FINAL_REVIEW_REQUIRED_BEFORE_C172
```

## C171 Final Failed/Not-Ready Closure Contract

```text
C171_FINAL_CLOSURE_EXACT_ANCHOR_EVAL_ID_REQUIRED=204
C171_FINAL_CLOSURE_EXACT_FINAL_EVAL_IDS_REQUIRED=205,206,207
C171_FINAL_CLOSURE_EXACT_FINAL_PARAM_SET_IDS_REQUIRED=12,13,14
C171_FINAL_CLOSURE_FINAL_PASSING_CANDIDATE_COUNT_REQUIRED=0
C171_FINAL_CLOSURE_ALL_FINAL_ARTIFACT_SHA1_REQUIRED=1
C171_FINAL_CLOSURE_SUMMARY_SHA1_REQUIRED=1
C171_FINAL_CLOSURE_DATABASE_IDENTITY_VERIFICATION_REQUIRED=1
C171_FINAL_CLOSURE_DATABASE_MUTATION_ALLOWED=0
C171_FINAL_CLOSURE_OOS_TABLE_READ_ALLOWED=0
C171_FINAL_CLOSURE_OFFICIAL_IS_EXECUTION_ALLOWED=0
C171_FINAL_CLOSURE_OOS_EXECUTION_ALLOWED=0
C171_FINAL_CLOSURE_PROMOTION_ALLOWED=0
C171_FINAL_CLOSURE_PLAN_ALLOWED=0
C171_FINAL_CLOSURE_CONFIRM_MUTATION_ALLOWED=0
C171_FINAL_CLOSURE_PRODUCTION_ACTIVATION_ALLOWED=0
C171_FINAL_CLOSURE_ADDITIONAL_C171_CATALOG_ALLOWED=0
C171_FINAL_CLOSURE_DECISION_REQUIRED=C171_FAILED_NOT_READY_NO_FURTHER_REMEDIATION
C171_FINAL_CLOSURE_C172_ALLOWED=0
C171_FINAL_CLOSURE_NEW_STRATEGY_RESEARCH_MUST_USE_SEPARATE_APPROVED_SCOPE=1
```

## C171 Final Closure Summary CSV Parsing Contract

```text
C171_FINAL_CLOSURE_SUMMARY_EXACT_FILE_SHA1_REQUIRED=53356CA429CF7AA47EFC45ACFB5511F9DC92ED50
C171_FINAL_CLOSURE_SUMMARY_UTF8_BOM_ALLOWED=1
C171_FINAL_CLOSURE_SUMMARY_BOM_REMOVAL_MUST_PRECEDE_CSV_HEADER_PARSE=1
C171_FINAL_CLOSURE_SUMMARY_FIRST_HEADER_REQUIRED=param_set_id
C171_FINAL_CLOSURE_SUMMARY_REQUIRED_HEADERS=param_set_id,eval_id,params_hash,canonical_is_gates_pass,pipeline_version,pipeline_hash,artifact_hash,file_sha1
C171_FINAL_CLOSURE_SUMMARY_IDENTITY_FAIL_CLOSED_REQUIRED=1
C171_FINAL_CLOSURE_SUMMARY_REWRITE_REQUIRED=0
C171_FINAL_CLOSURE_DATABASE_MUTATION_ALLOWED=0
```

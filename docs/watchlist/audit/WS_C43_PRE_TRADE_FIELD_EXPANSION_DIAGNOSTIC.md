# WS C43 — Pre-Trade Field Expansion Diagnostic

## Purpose and source lock

C43 is an IS-only diagnostic following C42. It locks `storage/app/watchlist/backtest/c42-is-rolling-normal-month-evidence-expansion.json` with stable hash `939e85f179b3bf5d2511730fafb4271cf7c2ca11`, carries the C42 warning decision forward, and audits additional pre-trade fields available in repository/database sources. C43 does not run OOS proof, tune from OOS, form a final candidate, create a catalog, promote production behavior, or mutate PLAN/CONFIRM or C01–C42 artifacts.

The locked C42 evidence is:

```text
status=C42_IS_ROLLING_NORMAL_MONTH_EVIDENCE_EXPANSION_COMPLETED
diagnostic_conclusion=C42_NO_SAFE_REFINEMENT_FIELD_AVAILABLE
target_candidate_code=C39_COVERAGE_AND_BRANCH_GUARD_G16_PLUS_METADATA_G21_MONTHLY_QUOTA
warning_interpretation=STRUCTURAL_METADATA_QUOTA_WEAKNESS
suspected_warning_month=2024-03
rolling_warning_explanation_result=C42_ROLLING_WARNING_EXPLAINED
normal_month_warning_explanation_result=C42_NORMAL_MONTH_WARNING_EXPLAINED
c39_guard_preservation_result=PASS
direct_oos_proof_recommended=false
oos_proof_unlocked=false
production_ready=false
```

## IS and leakage boundary

```text
IS_FROM=2023-01-02
IS_TO=2025-05-21
OOS_RESERVED_FROM=2025-05-22
OOS_RESERVED_TO=2026-05-29
oos_data_used_for_tuning=false
return_used_for_selection=false
future_path_used_for_selection=false
production_ready=false
```

Signal-date EOD fields are joined only when `eod_* .trade_date = C28.trade_date` and `ticker_id` matches. C28 `entry_date` is later than this signal date. Entry/next-open/gap fields are therefore execution-time fields and excluded from selection. Return and exit-path fields are post-selection diagnostic evidence only.

## Repository and database discovery

C43 audits C28/C39/C42 artifacts plus the following repository-backed sources:

- `eod_bars`: signal-date open/high/low/close/volume;
- `eod_indicators`: liquidity, volatility, volume, momentum, trend, relative strength, sector, suspension/UMA, corporate-action and event-risk context;
- `eod_eligibility`: signal-date eligibility;
- `market_benchmark_indicators`: IHSG condition fields;
- `market_data_sectors` and `ticker_sector_memberships`: sector metadata/effective membership;
- `market_calendar`: source exists but is not joined as a C43 refinement-quality field;
- `market_data_trading_status_events`: raw note timing remains unclear, while snapshot flags in `eod_indicators` are separately auditable.

No repository source was found for breadth or explicit special-monitoring-board status.

## Field timing classes

The artifact emits all required classes:

```text
SAFE_PRE_TRADE_SELECTION_FIELD
SAFE_PRE_TRADE_JOINABLE_FIELD
DIAGNOSTIC_ONLY_EVALUATION_FIELD
UNSAFE_FUTURE_OR_RETURN_FIELD
UNSAFE_NEXT_OPEN_OR_EXECUTION_FIELD
UNSAFE_DERIVED_FROM_EXIT_PATH
SOURCE_EXISTS_BUT_NOT_JOINED
SOURCE_EXISTS_BUT_TIMING_UNCLEAR
UNAVAILABLE_FIELD
```

Profile/exit context is diagnostic-only. `ret_net`, `avg_ret_net`, `profile_ret_net`, deltas and win flags are unsafe for selection. Entry open/next open/gap are unsafe for pre-trade selection. MFE, MAE, exit result, exit price and future path are future-derived and forbidden.

## Join feasibility and warning-cluster enrichment

C43 calculates coverage/missingness for every source field and records join keys, as-of rules, source class, selection safety and reason codes. It reconstructs the locked C39 metadata quota without returns, then enriches the C42 warning cluster:

```text
cluster_code=C42_MARCH_2024_G21_WARNING_CLUSTER
trade_month=2024-03
selected_source_code=G21
```

Bucket returns and win rates are computed only after the metadata-selected rows are fixed. They explain field separation diagnostically; C43 does not choose a threshold or final rule from those returns.

## Refinement readiness and guard feasibility

Safe joined quality fields may support a C44 proposal for liquidity, volatility, relative strength, sector health, market condition or event risk. Every proposal is conditional on preserving the C39 guards:

```text
months_covered=27
zero_pick_months=0
minimum monthly selected-row floor retained
G21 not suppressed in full
branch diversification retained
```

C43 records feasibility, not proof. C44 must form and validate any actual IS candidate.

## Runtime result

The runtime completed in this workspace:

```text
status=C43_PRE_TRADE_FIELD_EXPANSION_DIAGNOSTIC_COMPLETED
artifact_path=storage/app/watchlist/backtest/c43-pre-trade-field-expansion-diagnostic.json
artifact_hash=41a91ba0447dcf6c0493e1bb27bce6df08fd3490
file_sha1=27816E62CBE7278108D0BC43C4C3E3F91BC749D7
expected_c42_hash=939e85f179b3bf5d2511730fafb4271cf7c2ca11
actual_c42_hash=939e85f179b3bf5d2511730fafb4271cf7c2ca11
c42_hash_match=true
diagnostic_conclusion=C43_SAFE_PRE_TRADE_FIELDS_FOUND_FOR_C44_REFINEMENT
next_step_recommendation=C44_IS_GUARD_REFINEMENT_CANDIDATE_FORMATION
direct_oos_proof_recommended=false
oos_proof_unlocked=false
production_ready=false
```

The artifact contains the field discovery matrix, timing/leakage audit, join feasibility matrix, warning-cluster enrichment, cluster explanation table, refinement readiness assessment, guard preservation feasibility, C43 decision summary, candidate safety audit and not-evaluable reasons.

Final validation evidence:

```text
PHPUNIT_C43=PASS — OK (13 tests, 106 assertions)
FULL_WATCHLIST_PHPUNIT=PASS — OK (652 tests, 12966 assertions)
ARTISAN_C43_RUNTIME=COMPLETED
field_discovery_rows=63
timing_audit_rows=63
join_feasibility_rows=38
warning_cluster_enrichment_rows=28
cluster_field_explanation_rows=21
```

C43 does not claim that OOS is repaired and does not authorize an OOS proof.

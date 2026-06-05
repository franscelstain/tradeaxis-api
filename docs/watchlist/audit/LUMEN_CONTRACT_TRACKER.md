# Watchlist Lumen Contract Tracker

## Document Purpose

Dokumen ini melacak kontrak perilaku yang harus dipenuhi selama implementasi watchlist di Lumen.

Dokumen ini bukan owner business rule. Kontrak di sini harus ditelusuri ke:

- `docs/watchlist/system/policy.md`;
- `docs/watchlist/system/policies/weekly_swing/**`;
- `docs/watchlist/system/implementation/weekly_swing/**` sebagai translation guidance;
- owner upstream market-data untuk producer-facing consumer read contract.

## ACTIVE SESSION

Session:
`WATCHLIST — PLAN GROUPING + TOP_PICKS / SECONDARY SELECTION EXECUTION SESSION`

Status:
`DONE for Phase 4 unit/static scope` for PLAN grouping foundation. Local PHP lint and PHPUnit validation passed in this session; readiness-critical contracts remain `PARTIAL` because no watchlist command/API runtime proof or artifact/log evidence exists yet.

Scope:
Create deterministic PLAN grouping on top of `WatchlistScoringService`, with TOP_PICKS, SECONDARY, WATCH_ONLY, diagnostics AVOID, grouping threshold/limit validation, paramset traceability, scoring metadata traceability, deterministic ranking, and ticker-id dedupe. Final recommendation, confirm, backtest, API, command, scheduler, portfolio, and execution remain out of scope.

Evidence:

- `app/Application/Watchlist/Services/WatchlistPlanGroupingService.php` created.
- `tests/Unit/Watchlist/WatchlistPlanGroupingServiceTest.php` added.
- `tests/Unit/Watchlist/WatchlistPlanGroupingStaticGuardTest.php` added.
- Bootstrap labels normalized to `WS_EOD_RUNTIME` and `WS_ACTIVE_BOOTSTRAP`; no `_V1` suffix is used for watchlist runtime/bootstrap labels because the application does not have formal app/runtime versioning yet.
- PLAN grouping reason codes added to `07_WS_REASON_CODES_AND_HASH.md`, `25_WS_RECOMMENDATION_REASON_CODES_AND_TESTS.md`, and support seed `db/REASON_CODES_SEED.sql`.
- Existing governance static guards are updated to track the active PLAN grouping session.

## Status Rules

- `NOT_STARTED`: no implementation yet.
- `FOUNDATION_STARTED`: governance/docs baseline exists, but runtime implementation is not complete.
- `IN_PROGRESS`: implementation started but not finished.
- `PARTIAL`: some acceptance criteria met but not enough for lock.
- `DONE`: scope-specific work completed, not necessarily production readiness.
- `LOCKED`: implementation, tests, runtime proof, artifact evidence, and docs sync all valid.
- `BLOCKED`: cannot progress due to missing dependency or decision.
- `SUPERSEDED`: replaced by newer contract.

No contract may move to `LOCKED` only because documentation exists.

## Contract Summary

| Contract ID | Title | Status |
|---|---|---|
| WL-CONTRACT-001 | MARKET-DATA PUBLICATION READ CONTRACT | `PARTIAL` |
| WL-CONTRACT-002 | NO RAW MARKET-DATA BYPASS | `PARTIAL` |
| WL-CONTRACT-003 | NO MAX-DATE / LATEST SHORTCUT | `PARTIAL` |
| WL-CONTRACT-004 | INDICATOR VALIDITY CONTRACT | `PARTIAL` |
| WL-CONTRACT-005 | ELIGIBILITY CONTRACT | `PARTIAL` |
| WL-CONTRACT-006 | SCORING DETERMINISM CONTRACT | `PARTIAL` |
| WL-CONTRACT-007 | PARAMSET TRACEABILITY CONTRACT | `PARTIAL` |
| WL-CONTRACT-008 | SIGNAL EXPLAINABILITY CONTRACT | `PARTIAL` |
| WL-CONTRACT-009 | BACKTEST NO-LOOKAHEAD CONTRACT | `NOT_STARTED` |
| WL-CONTRACT-010 | BACKTEST REPRODUCIBILITY CONTRACT | `NOT_STARTED` |
| WL-CONTRACT-011 | RISK GATE CONTRACT | `PARTIAL` |
| WL-CONTRACT-012 | PORTFOLIO AWARENESS BOUNDARY | `NOT_STARTED` |
| WL-CONTRACT-013 | AUDIT ARTIFACT CONTRACT | `NOT_STARTED` |
| WL-CONTRACT-014 | DOCS SYNC CONTRACT | `PARTIAL` |
| WL-CONTRACT-015 | PRODUCTION READINESS CONTRACT | `PARTIAL` |
| WL-CONTRACT-016 | PLAN GROUPING DETERMINISM CONTRACT | `PARTIAL` |
| WL-CONTRACT-017 | PLAN GROUP BOUNDARY CONTRACT | `PARTIAL` |

---

## WL-CONTRACT-001 — MARKET-DATA PUBLICATION READ CONTRACT

Contract ID:
`WL-CONTRACT-001`

Title:
`MARKET-DATA PUBLICATION READ CONTRACT`

Status:
`PARTIAL`

Owner docs:

- `docs/watchlist/system/policy.md`
- `docs/watchlist/system/README.md`
- `docs/watchlist/system/implementation/weekly_swing/01_WS_IMPLEMENTATION_SCOPE_AND_BOUNDARY.md`
- `docs/watchlist/system/implementation/weekly_swing/02_WS_MODULE_MAPPING.md`
- `docs/market_data/book/Downstream_Consumer_Read_Model_Contract_LOCKED.md`
- `docs/market_data/book/EOD_Eligibility_Snapshot_Contract_LOCKED.md`
- `docs/market_data/book/Downstream_Data_Readiness_Guarantee_LOCKED.md`
- `docs/market_data/book/Publication_Current_Pointer_Integrity_Contract_LOCKED.md`

Implementation files:

- `app/Application/Watchlist/Services/WatchlistMarketDataConsumerReadService.php`
- `app/Application/Watchlist/Services/WatchlistCandidateUniverseService.php` downstream gated universe consumer
- `app/Application/MarketData/Services/MarketDataWatchlistReadService.php` upstream consumer read gateway
- `app/Infrastructure/Persistence/MarketData/MarketDataWatchlistReadRepository.php` upstream publication-scoped row source

Tests:

- `tests/Unit/Watchlist/WatchlistMarketDataConsumerReadServiceTest.php`
- `tests/Unit/Watchlist/WatchlistMarketDataConsumerReadModelStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistCandidateUniverseServiceTest.php`
- `tests/Unit/Watchlist/WatchlistCandidateUniverseStaticGuardTest.php`
- upstream reference: `tests/Unit/MarketData/MarketDataWatchlistReadModelTest.php`

Runtime proof:
`NOT_STARTED` — no watchlist command/API exists yet.

Current gaps:

- Watchlist read model exists and consumes the upstream market-data watchlist read surface.
- Candidate universe service consumes the Phase 1 read model and preserves pointer/publication metadata in gated rows.
- Contract is not `LOCKED` because there is no watchlist command/API runtime proof and no artifact/log output yet.
- Scoring/recommendation/backtest consumers have not consumed this read model yet.

Acceptance criteria:

- Watchlist reads market-data only from current readable publication pointer.
- Consumed publication is sealed, `SUCCESS`, `READABLE`, coverage `PASS`, and mirror-valid through upstream market-data readiness.
- Failure to resolve valid publication fails safe.
- No raw/staging/latest fallback exists in watchlist application code.
- Static guard covers the no-bypass constraint.

Last update:
`2026-05-28 — WATCHLIST — CANDIDATE UNIVERSE + LIQUIDITY/RISK FILTER EXECUTION SESSION`

---

## WL-CONTRACT-002 — NO RAW MARKET-DATA BYPASS

Contract ID:
`WL-CONTRACT-002`

Title:
`NO RAW MARKET-DATA BYPASS`

Status:
`PARTIAL`

Owner docs:

- `docs/watchlist/README.md`
- `docs/watchlist/system/policy.md`
- `docs/watchlist/system/implementation/weekly_swing/01_WS_IMPLEMENTATION_SCOPE_AND_BOUNDARY.md`
- `docs/watchlist/system/implementation/weekly_swing/02_WS_MODULE_MAPPING.md`

Implementation files:

- `app/Application/Watchlist/Services/WatchlistMarketDataConsumerReadService.php`
- `app/Application/Watchlist/Services/WatchlistCandidateUniverseService.php`
- `app/Infrastructure/Persistence/MarketData/MarketDataWatchlistReadRepository.php` upstream hardened query boundary

Tests:

- `tests/Unit/Watchlist/WatchlistMarketDataConsumerReadModelStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistMarketDataConsumerReadServiceTest.php`
- `tests/Unit/Watchlist/WatchlistCandidateUniverseStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistCandidateUniverseServiceTest.php`

Runtime proof:
`NOT_STARTED` — no watchlist command/API exists yet.

Current gaps:

- Watchlist application code currently has no direct DB read. Phase 2 candidate universe consumes `WatchlistMarketDataConsumerReadService` only.
- Static guard blocks `DB::table`, raw market-data table names, staging/latest/MAX(date) shortcuts in watchlist application code, including the candidate universe service.
- Contract is not `LOCKED` until future watchlist consumers are added and guarded by runtime proof.

Acceptance criteria:

- Watchlist does not directly consume raw provider response, staging tables, unsealed bars, unsealed indicators, or unsealed eligibility rows.
- Static guard rejects raw market-data bypass patterns in watchlist code.
- Any future repository/API/command must preserve this boundary or update the guard.

Last update:
`2026-05-28 — WATCHLIST — CANDIDATE UNIVERSE + LIQUIDITY/RISK FILTER EXECUTION SESSION`

---

## WL-CONTRACT-003 — NO MAX-DATE / LATEST SHORTCUT

Contract ID:
`WL-CONTRACT-003`

Title:
`NO MAX-DATE / LATEST SHORTCUT`

Status:
`PARTIAL`

Owner docs:

- `docs/watchlist/system/policy.md`
- `docs/watchlist/system/implementation/weekly_swing/01_WS_IMPLEMENTATION_SCOPE_AND_BOUNDARY.md`
- `docs/watchlist/system/implementation/weekly_swing/03_WS_RUNTIME_ARTIFACT_FLOW.md`
- `docs/market_data/book/Publication_Current_Pointer_Integrity_Contract_LOCKED.md`

Implementation files:

- `app/Application/Watchlist/Services/WatchlistMarketDataConsumerReadService.php`
- `app/Application/Watchlist/Services/WatchlistCandidateUniverseService.php`

Tests:

- `tests/Unit/Watchlist/WatchlistMarketDataConsumerReadModelStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistCandidateUniverseStaticGuardTest.php`

Runtime proof:
`NOT_STARTED` — no watchlist command/API exists yet.

Current gaps:

- Watchlist read model delegates date/publication resolution to market-data.
- Candidate universe service keeps the already-resolved `trade_date_effective`, `publication_id`, `publication_version`, and `run_id` from Phase 1 output.
- Static guard forbids `MAX(trade_date)`, `max('trade_date')`, `latest()`, `orderByDesc('trade_date')`, and descending date fallback in watchlist application code.
- Contract is not `LOCKED` until all future watchlist read consumers are added and covered by runtime proof.

Acceptance criteria:

- Date/effective publication resolution is owned by market-data current readable publication pointer.
- Watchlist code does not infer data freshness via `MAX(trade_date)`, `latest()`, descending date limit, or fallback to newest available raw row.
- Any future backtest/recommendation/API code must use the same resolved publication/effective date contract.

Last update:
`2026-05-28 — WATCHLIST — CANDIDATE UNIVERSE + LIQUIDITY/RISK FILTER EXECUTION SESSION`

---

## WL-CONTRACT-004 — INDICATOR VALIDITY CONTRACT

Contract ID:
`WL-CONTRACT-004`

Title:
`INDICATOR VALIDITY CONTRACT`

Status:
`PARTIAL`

Owner docs:

- `docs/watchlist/system/policies/weekly_swing/03_WS_DATA_MODEL_MARIADB.md`
- `docs/watchlist/system/policies/weekly_swing/08_WS_PLAN_ALGORITHM.md`
- `docs/watchlist/system/implementation/weekly_swing/05A_WS_CANONICAL_FIELD_MATRIX.md`
- market-data indicator/readiness owner docs

Implementation files:

- `app/Application/Watchlist/Services/WatchlistMarketDataConsumerReadService.php`
- `app/Infrastructure/Persistence/MarketData/MarketDataWatchlistReadRepository.php`

Tests:

- `tests/Unit/Watchlist/WatchlistMarketDataConsumerReadServiceTest.php`
- `tests/Unit/Watchlist/WatchlistMarketDataConsumerReadModelStaticGuardTest.php`

Runtime proof:
`NOT_STARTED` — no watchlist command/API exists yet.

Current gaps:

- Required indicator fields are checked in watchlist service.
- Upstream market-data watchlist repository now filters `ind.is_valid = 1`, `invalid_reason_code IS NULL`, `indicator_set_version IS NOT NULL`, and required indicator fields non-null.
- Watchlist service still revalidates rows and excludes invalid/incomplete rows if they ever appear in the upstream payload.
- Contract is not `LOCKED` because no runtime command/API proof exists.

Acceptance criteria:

- A ticker cannot become a watchlist candidate if required indicator values are null, missing, invalid, or flagged by invalid reason code.
- Required indicator list is explicit and guarded by tests.
- Invalid candidate rows are excluded with reason-coded evidence.

Last update:
`2026-05-28 — WATCHLIST — CANDIDATE UNIVERSE + LIQUIDITY/RISK FILTER EXECUTION SESSION`

---

## WL-CONTRACT-005 — ELIGIBILITY CONTRACT

Contract ID:
`WL-CONTRACT-005`

Title:
`ELIGIBILITY CONTRACT`

Status:
`PARTIAL`

Owner docs:

- `docs/watchlist/system/policy.md`
- `docs/watchlist/system/policies/weekly_swing/03_WS_DATA_MODEL_MARIADB.md`
- `docs/watchlist/system/implementation/weekly_swing/05A_WS_CANONICAL_FIELD_MATRIX.md`
- `docs/market_data/book/EOD_Eligibility_Snapshot_Contract_LOCKED.md`

Implementation files:

- `app/Application/Watchlist/Services/WatchlistMarketDataConsumerReadService.php`
- `app/Infrastructure/Persistence/MarketData/MarketDataWatchlistReadRepository.php`

Tests:

- `tests/Unit/Watchlist/WatchlistMarketDataConsumerReadServiceTest.php`
- `tests/Unit/Watchlist/WatchlistMarketDataConsumerReadModelStaticGuardTest.php`

Runtime proof:
`NOT_STARTED` — no watchlist command/API exists yet.

Current gaps:

- Upstream market-data watchlist repository returns `elig.eligible = 1` rows only and publication/run scopes eligibility to the resolved readable publication.
- Watchlist service rechecks `eligibility_state` and excludes any non-eligible row if the upstream payload is malformed.
- Contract is not `LOCKED` because no runtime command/API proof exists.

Acceptance criteria:

- Watchlist candidate universe contains only eligible tickers from the resolved market-data publication.
- Non-eligible rows are not silently accepted.
- Eligibility reason state remains traceable for downstream scoring/recommendation work.

Last update:
`2026-05-28 — WATCHLIST — MARKET-DATA CONSUMER READ MODEL EXECUTION SESSION`

---

## WL-CONTRACT-006 — SCORING DETERMINISM CONTRACT

Contract ID:
`WL-CONTRACT-006`

Title:
`SCORING DETERMINISM CONTRACT`

Status:
`PARTIAL`

Owner docs:

- `docs/watchlist/system/policies/weekly_swing/08_WS_PLAN_ALGORITHM.md`
- `docs/watchlist/system/policies/weekly_swing/09_WS_DYNAMIC_SELECTION_DETERMINISTIC.md`
- `docs/watchlist/system/policies/weekly_swing/04_WS_PARAMSET_JSON_CONTRACT.md`
- `docs/watchlist/system/policies/weekly_swing/05_WS_PARAMETER_REGISTRY_COMPLETE.md`

Implementation files:

- `app/Application/Watchlist/Services/WatchlistScoringService.php`
- `app/Application/Watchlist/Services/WatchlistPlanGroupingService.php`
- `app/Application/Watchlist/Services/WatchlistCandidateUniverseService.php` scoring input metric pass-through
- `app/Application/Watchlist/Services/WatchlistMarketDataConsumerReadService.php` ticker id pass-through
- `app/Infrastructure/Persistence/MarketData/MarketDataWatchlistReadRepository.php` ticker id read-surface pass-through

Tests:

- `tests/Unit/Watchlist/WatchlistScoringServiceTest.php`
- `tests/Unit/Watchlist/WatchlistScoringStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistPlanGroupingServiceTest.php`
- `tests/Unit/Watchlist/WatchlistPlanGroupingStaticGuardTest.php`

Runtime proof:
`NOT_STARTED` — no watchlist command/API exists yet.

Current gaps:

- Scoring engine foundation is baseline local PASS for Phase 3 unit/static scope.
- PLAN grouping foundation consumes scoring output and preserves deterministic sort keys for Phase 4 unit/static scope.
- `score_total` is deterministic `WEIGHTED_MEAN` over momentum, breakout, volume, and risk components.
- Component scores and total score are clamped to `0..1`.
- Ranking sort keys are deterministic: `score_total_desc`, `score_breakout_desc`, `score_momentum_desc`, `dv20_idr_desc`, `atr14_pct_asc`, `ticker_id_asc`.
- PLAN grouping deduplicates duplicate `ticker_id` by deterministic best item before active group assignment.
- Contract is not `LOCKED` because there is no command/API runtime proof and no persisted artifact/log output yet.

Acceptance criteria:

- Same publication input + same paramset + same universe produces the same score and ranking.
- Tie-breaking is deterministic.
- Tests cover deterministic scoring output and deterministic PLAN grouping output.

Last update:
`2026-06-05 — WATCHLIST — PLAN GROUPING + TOP_PICKS / SECONDARY SELECTION EXECUTION SESSION`

---

## WL-CONTRACT-007 — PARAMSET TRACEABILITY CONTRACT

Contract ID:
`WL-CONTRACT-007`

Title:
`PARAMSET TRACEABILITY CONTRACT`

Status:
`PARTIAL`

Owner docs:

- `docs/watchlist/system/policies/weekly_swing/04_WS_PARAMSET_JSON_CONTRACT.md`
- `docs/watchlist/system/policies/weekly_swing/05_WS_PARAMETER_REGISTRY_COMPLETE.md`
- `docs/watchlist/system/policies/_shared/02_PARAMSET_CONTRACT_GLOBAL.md`
- `docs/watchlist/system/policies/weekly_swing/20_WS_CANONICAL_PARAMSET_PROCEDURES.md`

Implementation files:

- `app/Application/Watchlist/Services/WatchlistCandidateUniverseService.php`
- `app/Application/Watchlist/Services/WatchlistScoringService.php`
- `app/Application/Watchlist/Services/WatchlistPlanGroupingService.php`

Tests:

- `tests/Unit/Watchlist/WatchlistCandidateUniverseServiceTest.php`
- `tests/Unit/Watchlist/WatchlistCandidateUniverseStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistScoringServiceTest.php`
- `tests/Unit/Watchlist/WatchlistScoringStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistPlanGroupingServiceTest.php`
- `tests/Unit/Watchlist/WatchlistPlanGroupingStaticGuardTest.php`

Runtime proof:
`NOT_STARTED` — no watchlist command/API exists yet.

Current gaps:

- Candidate universe records canonical policy/paramset labels: `policy_code`, `policy_version`, and `paramset_code`.
- Scoring output records canonical policy/paramset labels plus `paramset_snapshot`.
- PLAN grouping output records canonical policy/paramset labels plus `paramset_snapshot.grouping`.
- Current bootstrap labels intentionally do not use `_V1` suffix because the watchlist application does not have formal app/runtime versioning yet.
- Candidate universe accepts nested `{ value: ... }` paramset shape for the gate fields it owns.
- Scoring accepts nested `{ value: ... }` weight shape for the fields it owns and rejects invalid weights.
- PLAN grouping accepts nested `{ value: ... }` grouping threshold/limit shape for the fields it owns and rejects invalid threshold/limit contracts.
- Candidate universe rejects invalid ATR percent-point units above `1.0`.
- Scoring rejects candidate ATR unit drift above `1.0`.
- Full runtime paramset loader/validator, persistence, hash, promotion, and artifact recording are still not implemented.

Acceptance criteria:

- Every scoring/recommendation/backtest execution has traceable policy/paramset identity.
- Paramset validation rejects missing, unknown, invalid, or type-drifted fields.
- Artifact output records policy/paramset identity and hash when runtime artifacts are introduced.

Last update:
`2026-06-05 — WATCHLIST — PLAN GROUPING + TOP_PICKS / SECONDARY SELECTION EXECUTION SESSION`

---

## WL-CONTRACT-008 — SIGNAL EXPLAINABILITY CONTRACT

Contract ID:
`WL-CONTRACT-008`

Title:
`SIGNAL EXPLAINABILITY CONTRACT`

Status:
`PARTIAL`

Owner docs:

- `docs/watchlist/system/policies/weekly_swing/07_WS_REASON_CODES_AND_HASH.md`
- `docs/watchlist/system/policies/weekly_swing/08_WS_PLAN_ALGORITHM.md`
- `docs/watchlist/system/policies/weekly_swing/22_WS_RECOMMENDATION_OVERVIEW.md`
- `docs/watchlist/system/policies/weekly_swing/23_WS_RECOMMENDATION_INPUT_OUTPUT_CONTRACT.md`
- `docs/watchlist/system/policies/weekly_swing/25_WS_RECOMMENDATION_REASON_CODES_AND_TESTS.md`

Implementation files:

- `app/Application/Watchlist/Services/WatchlistScoringService.php`
- `app/Application/Watchlist/Services/WatchlistPlanGroupingService.php`

Tests:

- `tests/Unit/Watchlist/WatchlistScoringServiceTest.php`
- `tests/Unit/Watchlist/WatchlistScoringStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistPlanGroupingServiceTest.php`
- `tests/Unit/Watchlist/WatchlistPlanGroupingStaticGuardTest.php`

Runtime proof:
`NOT_STARTED` — no watchlist command/API exists yet.

Current gaps:

- PLAN scoring explainability exists via `score_components`, `score_weights`, `factor_breakdown`, and `reason_codes`.
- PLAN grouping explainability exists via `group_reason_code`, augmented `reason_codes`, `group_contract`, `paramset_snapshot.grouping`, and summary counts.
- Explainability reason codes used by scoring are traceable to Weekly Swing owner docs / reason seed.
- PLAN grouping reason codes `WS_PLAN_TOP_PICK`, `WS_PLAN_SECONDARY`, `WS_PLAN_WATCH_ONLY`, `WS_PLAN_AVOID_LOW_SCORE`, and `WS_PLAN_AVOID_EXCLUDED` are traceable to Weekly Swing reason-code docs / support seed.
- Recommendation explainability is not implemented yet because recommendation engine remains out of scope.
- Contract is not `LOCKED` because there is no command/API runtime proof and no artifact/log output yet.

Acceptance criteria:

- Every signal/recommendation has explainable reason/factor output.
- Output includes enough factor breakdown to audit why a ticker is included, watched, avoided, or rejected.

Last update:
`2026-06-05 — WATCHLIST — PLAN GROUPING + TOP_PICKS / SECONDARY SELECTION EXECUTION SESSION`

---

## WL-CONTRACT-009 — BACKTEST NO-LOOKAHEAD CONTRACT

Contract ID:
`WL-CONTRACT-009`

Title:
`BACKTEST NO-LOOKAHEAD CONTRACT`

Status:
`NOT_STARTED`

Owner docs:

- `docs/watchlist/system/policies/weekly_swing/12_WS_BACKTEST_SCHEMA_AND_CALIBRATION.md`
- `docs/watchlist/system/policies/weekly_swing/14_WS_BT_COVERAGE_MATRIX_LOCKED.md`
- `docs/watchlist/system/policies/weekly_swing/17_WS_WALK_FORWARD_OOS_PROOF_LOCKED.md`
- `docs/watchlist/system/policies/weekly_swing/18_WS_BACKTEST_ARTIFACT_MANIFEST_LOCKED.md`

Implementation files:
`NOT_STARTED`

Tests:
`NOT_STARTED`

Runtime proof:
`NOT_STARTED`

Current gaps:

- No backtest engine exists.

Acceptance criteria:

- Backtest never uses future publication, future indicator, future eligibility, future price, or future outcome to make historical decisions.
- Tests include lookahead guard cases.

Last update:
`2026-05-28`

---

## WL-CONTRACT-010 — BACKTEST REPRODUCIBILITY CONTRACT

Contract ID:
`WL-CONTRACT-010`

Title:
`BACKTEST REPRODUCIBILITY CONTRACT`

Status:
`NOT_STARTED`

Owner docs:

- `docs/watchlist/system/policies/weekly_swing/12_WS_BACKTEST_SCHEMA_AND_CALIBRATION.md`
- `docs/watchlist/system/policies/weekly_swing/18_WS_BACKTEST_ARTIFACT_MANIFEST_LOCKED.md`
- `docs/watchlist/system/policies/weekly_swing/20_WS_CANONICAL_PARAMSET_PROCEDURES.md`

Implementation files:
`NOT_STARTED`

Tests:
`NOT_STARTED`

Runtime proof:
`NOT_STARTED`

Current gaps:

- No backtest artifact/replay mechanism exists.

Acceptance criteria:

- Backtest can be replayed with the same dataset identity, publication scope, paramset identity, universe, date range, and artifact manifest.
- Replayed result matches expected metrics and output contract.

Last update:
`2026-05-28`

---

## WL-CONTRACT-011 — RISK GATE CONTRACT

Contract ID:
`WL-CONTRACT-011`

Title:
`RISK GATE CONTRACT`

Status:
`PARTIAL`

Owner docs:

- `docs/watchlist/system/policies/weekly_swing/04_WS_PARAMSET_JSON_CONTRACT.md`
- `docs/watchlist/system/policies/weekly_swing/05_WS_PARAMETER_REGISTRY_COMPLETE.md`
- `docs/watchlist/system/policies/weekly_swing/08_WS_PLAN_ALGORITHM.md`
- `docs/watchlist/system/policies/weekly_swing/09_WS_DYNAMIC_SELECTION_DETERMINISTIC.md`
- `docs/watchlist/system/policies/weekly_swing/15_WS_UNIVERSE_EQUIVALENCE_CONTRACT_LOCKED.md`
- `docs/watchlist/system/policies/weekly_swing/22_WS_RECOMMENDATION_OVERVIEW.md`
- `docs/watchlist/system/policies/weekly_swing/24_WS_RECOMMENDATION_ALGORITHM.md`

Implementation files:

- `app/Application/Watchlist/Services/WatchlistCandidateUniverseService.php`
- `app/Application/Watchlist/Services/WatchlistScoringService.php`

Tests:

- `tests/Unit/Watchlist/WatchlistCandidateUniverseServiceTest.php`
- `tests/Unit/Watchlist/WatchlistCandidateUniverseStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistScoringServiceTest.php`
- `tests/Unit/Watchlist/WatchlistScoringStaticGuardTest.php`

Runtime proof:
`NOT_STARTED` — no watchlist command/API exists yet.

Current gaps:

- Runtime risk/liquidity/volume gate exists at service + unit/static test level.
- Scoring risk/volume quality components now exist at service + unit/static scope.
- PLAN grouping consumes scored risk/volume-aware output without rewriting risk/liquidity formulas.
- Guards implemented: `dv20_idr >= min_dv20_idr`, `atr14_pct >= min_atr14_pct`, `atr14_pct <= max_atr14_pct`, `vol_ratio >= min_vol_ratio`.
- Canonical rejection reason priority implemented: `WS_DATA_MISSING`, `WS_LIQ_FAIL`, `WS_ATR_LOW`, `WS_ATR_HIGH`, `WS_VOLR_FAIL`.
- Explainable row output includes `required_ok`, `guard_ok`, `eligible_plan`, `canonical_fail_reason_code`, `reason_codes`, `missing_fields`, `gate_metrics`, and `gate_thresholds`.
- Scoring output includes risk factor breakdown and rejects ATR unit drift above `1.0`.
- PLAN grouping keeps low-score candidates in diagnostics `AVOID` and prevents scoring exclusions from entering active PLAN groups.
- Contract is not `LOCKED` because no command/API runtime proof, artifact output, backtest equivalence proof, or persisted universe snapshot exists yet.

Acceptance criteria:

- Watchlist does not rank only potential return.
- Candidate selection includes risk, liquidity, volatility, and guard failure handling.
- Risk gate output is explainable.
- Production PLAN universe and future backtest universe can compare pass/fail + reason using canonical fields.

Last update:
`2026-06-05 — WATCHLIST — PLAN GROUPING + TOP_PICKS / SECONDARY SELECTION EXECUTION SESSION`

---

## WL-CONTRACT-012 — PORTFOLIO AWARENESS BOUNDARY

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

## WL-CONTRACT-013 — AUDIT ARTIFACT CONTRACT

Contract ID:
`WL-CONTRACT-013`

Title:
`AUDIT ARTIFACT CONTRACT`

Status:
`NOT_STARTED`

Owner docs:

- `docs/watchlist/system/policies/weekly_swing/07_WS_REASON_CODES_AND_HASH.md`
- `docs/watchlist/system/policies/weekly_swing/18_WS_BACKTEST_ARTIFACT_MANIFEST_LOCKED.md`
- `docs/watchlist/system/implementation/weekly_swing/03_WS_RUNTIME_ARTIFACT_FLOW.md`

Implementation files:
`NOT_STARTED`

Tests:
`NOT_STARTED`

Runtime proof:
`NOT_STARTED`

Current gaps:

- No runtime artifact/log output exists.

Acceptance criteria:

- Every important watchlist run produces traceable artifact/log.
- Artifact records publication, paramset, universe, result, reason code/factor output, and validation status.

Last update:
`2026-05-28`

---

## WL-CONTRACT-014 — DOCS SYNC CONTRACT

Contract ID:
`WL-CONTRACT-014`

Title:
`DOCS SYNC CONTRACT`

Status:
`PARTIAL`

Owner docs:

- `docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md`
- `docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md`
- `docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md`
- `docs/watchlist/audit/README.md`
- `docs/watchlist/audit/WATCHLIST_OWNER_MATRIX.md`

Implementation files:

- `tests/Unit/Watchlist/WatchlistAuditGovernanceStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistMarketDataConsumerReadModelStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistCandidateUniverseStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistScoringStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistPlanGroupingStaticGuardTest.php`
- `docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md`
- `docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md`
- `docs/watchlist/system/policies/weekly_swing/07_WS_REASON_CODES_AND_HASH.md`
- `docs/watchlist/system/policies/weekly_swing/25_WS_RECOMMENDATION_REASON_CODES_AND_TESTS.md`
- `docs/watchlist/system/policies/weekly_swing/db/REASON_CODES_SEED.sql`

Tests:

- `WatchlistAuditGovernanceStaticGuardTest` added for initial docs guard.
- `WatchlistMarketDataConsumerReadModelStaticGuardTest` added for Phase 1 docs/code synchronization guard.
- `WatchlistScoringStaticGuardTest` added for Phase 3 docs/code synchronization guard.
- `WatchlistPlanGroupingStaticGuardTest` added for Phase 4 docs/code synchronization guard.

Runtime proof:
`NOT_STARTED`

Current gaps:

- Phase 1 code/test/docs sync completed for market-data consumer read model.
- Phase 2 code/test/docs sync completed for candidate universe.
- Phase 3 code/test/docs sync completed for scoring foundation.
- Phase 4 code/test/docs sync completed for PLAN grouping foundation.
- Contract remains `PARTIAL` because future final recommendation/backtest/API/runtime artifact docs are not implemented yet.

- Docs sync foundation exists, but future code/config/schema/test/runtime changes still need ongoing enforcement.
- Runtime watchlist docs sync cannot be proven until runtime watchlist code exists.

Acceptance criteria:

- Every watchlist code/config/schema/test/behavior change updates implementation status and contract tracker.
- Active session name is aligned between status and tracker.
- Tracker contracts reflect actual code/test/runtime status without overclaim.

Last update:
`2026-06-05 — WATCHLIST — PLAN GROUPING + TOP_PICKS / SECONDARY SELECTION EXECUTION SESSION`

---

## WL-CONTRACT-015 — PRODUCTION READINESS CONTRACT

Contract ID:
`WL-CONTRACT-015`

Title:
`PRODUCTION READINESS CONTRACT`

Status:
`PARTIAL`

Owner docs:

- `docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md`
- `docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md`
- `docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md`
- `docs/watchlist/system/policy.md`
- `docs/watchlist/system/policies/weekly_swing/**`

Implementation files:

- `app/Application/Watchlist/Services/WatchlistMarketDataConsumerReadService.php`
- `app/Application/Watchlist/Services/WatchlistCandidateUniverseService.php`
- `app/Application/Watchlist/Services/WatchlistScoringService.php`
- `app/Application/Watchlist/Services/WatchlistPlanGroupingService.php`

Tests:

- watchlist read model unit/static tests
- watchlist candidate universe unit/static tests
- watchlist scoring unit/static tests
- watchlist PLAN grouping unit/static tests

Runtime proof:
`NOT_STARTED`

Current gaps:

- Read model, candidate universe, scoring foundation, and PLAN grouping foundation exist at unit/static scope.
- Runtime proof is not available.
- Artifacts are not available.
- No API endpoint exists.
- No watchlist command surface exists.
- No production watchlist schema/migration exists.
- No final recommendation/backtest/portfolio-aware integration exists.
- Core contracts are not `LOCKED` because runtime proof and artifact/log evidence are missing.

Acceptance criteria:

- Market-data consumer read model locked.
- No raw/latest/`MAX(date)` bypass.
- Required indicator and eligibility guards locked.
- Scoring deterministic and explainable.
- PLAN grouping deterministic and explainable.
- Paramset identity traceable.
- Recommendation output tested.
- Backtest no-lookahead and reproducible.
- Risk gates present.
- Artifact/log proof present.
- Full watchlist test suite passes.
- Runtime command/API proof passes.
- Docs sync complete.

Last update:
`2026-06-05 — WATCHLIST — PLAN GROUPING + TOP_PICKS / SECONDARY SELECTION EXECUTION SESSION`

---

## WL-CONTRACT-016 — PLAN GROUPING DETERMINISM CONTRACT

Contract ID:
`WL-CONTRACT-016`

Title:
`PLAN GROUPING DETERMINISM CONTRACT`

Status:
`PARTIAL`

Owner docs:

- `docs/watchlist/system/policies/weekly_swing/08_WS_PLAN_ALGORITHM.md`
- `docs/watchlist/system/policies/weekly_swing/09_WS_DYNAMIC_SELECTION_DETERMINISTIC.md`
- `docs/watchlist/system/policies/weekly_swing/04_WS_PARAMSET_JSON_CONTRACT.md`
- `docs/watchlist/system/policies/weekly_swing/05_WS_PARAMETER_REGISTRY_COMPLETE.md`

Implementation files:

- `app/Application/Watchlist/Services/WatchlistPlanGroupingService.php`
- upstream source: `app/Application/Watchlist/Services/WatchlistScoringService.php`

Tests:

- `tests/Unit/Watchlist/WatchlistPlanGroupingServiceTest.php`
- `tests/Unit/Watchlist/WatchlistPlanGroupingStaticGuardTest.php`

Runtime proof:
`NOT_STARTED` — no watchlist command/API exists yet.

Current gaps:

- PLAN grouping service exists for Phase 4 unit/static scope.
- `TOP_PICKS`, `SECONDARY`, `WATCH_ONLY`, and diagnostics `AVOID` are formed deterministically from Phase 3 scored output.
- Default bootstrap thresholds and limits are validated: top `0.70/5`, secondary `0.55/10`, watch-only `0.40/20`, avoid below `0.40`.
- Sort keys follow Phase 3 scoring contract: `score_total_desc`, `score_breakout_desc`, `score_momentum_desc`, `dv20_idr_desc`, `atr14_pct_asc`, `ticker_id_asc`.
- Duplicate `ticker_id` is resolved by deterministic best item.
- Contract is not `LOCKED` because there is no command/API runtime proof and no artifact/log output yet.

Acceptance criteria:

- Same scored input + same grouping paramset produces identical PLAN groups.
- Active PLAN groups do not depend on input array order.
- Duplicate ticker IDs do not enter more than one active PLAN group.
- Overflow from TOP_PICKS and SECONDARY follows deterministic threshold/limit rules.

Last update:
`2026-06-05 — WATCHLIST — PLAN GROUPING + TOP_PICKS / SECONDARY SELECTION EXECUTION SESSION`

---

## WL-CONTRACT-017 — PLAN GROUP BOUNDARY CONTRACT

Contract ID:
`WL-CONTRACT-017`

Title:
`PLAN GROUP BOUNDARY CONTRACT`

Status:
`PARTIAL`

Owner docs:

- `docs/watchlist/system/policies/weekly_swing/01_WS_OVERVIEW.md`
- `docs/watchlist/system/policies/weekly_swing/02_WS_CANONICAL_RUNTIME_FLOW.md`
- `docs/watchlist/system/policies/weekly_swing/08_WS_PLAN_ALGORITHM.md`
- `docs/watchlist/system/policies/weekly_swing/09_WS_DYNAMIC_SELECTION_DETERMINISTIC.md`
- `docs/watchlist/system/policies/weekly_swing/10_WS_CONFIRM_OVERLAY.md`
- `docs/watchlist/system/policies/weekly_swing/22_WS_RECOMMENDATION_OVERVIEW.md`
- `docs/watchlist/system/policies/weekly_swing/23_WS_RECOMMENDATION_INPUT_OUTPUT_CONTRACT.md`
- `docs/watchlist/system/policies/weekly_swing/24_WS_RECOMMENDATION_ALGORITHM.md`

Implementation files:

- `app/Application/Watchlist/Services/WatchlistPlanGroupingService.php`

Tests:

- `tests/Unit/Watchlist/WatchlistPlanGroupingServiceTest.php`
- `tests/Unit/Watchlist/WatchlistPlanGroupingStaticGuardTest.php`

Runtime proof:
`NOT_STARTED` — no watchlist command/API exists yet.

Current gaps:

- `TOP_PICKS` and `SECONDARY` are implemented only as PLAN groups, not final recommendation membership.
- Grouping output explicitly carries `not_final_recommendation = true`.
- Grouping does not read confirm, portfolio, execution, raw market-data, or backtest inputs.
- Scoring excluded candidates and invalid scored items do not enter active PLAN groups.
- Contract is not `LOCKED` because there is no command/API runtime proof and no artifact/log output yet.

Acceptance criteria:

- PLAN grouping consumes `WatchlistScoringService` only.
- PLAN grouping does not create recommendation labels, confirm state, order/execution actions, portfolio allocation, or backtest metrics.
- `AVOID` remains diagnostics and must not be interpreted as sell recommendation or execution instruction.
- Future recommendation layer must consume PLAN grouping output without mutating PLAN group membership.

Last update:
`2026-06-05 — WATCHLIST — PLAN GROUPING + TOP_PICKS / SECONDARY SELECTION EXECUTION SESSION`

## Next Required Contract Work

Next session must target:

`WATCHLIST — FINAL RECOMMENDATION LAYER FOUNDATION EXECUTION SESSION`

Priority contracts:

1. `WL-CONTRACT-006`
2. `WL-CONTRACT-007`
3. `WL-CONTRACT-008`
4. `WL-CONTRACT-016`
5. `WL-CONTRACT-017`
6. `WL-CONTRACT-014`
7. `WL-CONTRACT-015`

Scope boundary:

- consume `WatchlistPlanGroupingService` output;
- create final recommendation foundation only from PLAN groups;
- preserve that recommendation can exist without confirm and can be empty even when `TOP_PICKS`/`SECONDARY` exists;
- do not create confirm overlay;
- do not create portfolio/execution logic;
- do not create backtest/API/command runtime yet unless explicitly scoped in a later session.

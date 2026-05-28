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
`WATCHLIST — MARKET-DATA CONSUMER READ MODEL EXECUTION SESSION`

Status:
`DONE` for Phase 1 read model scope; `PARTIAL` for readiness-critical contracts because no watchlist command/API runtime proof exists yet.

Scope:
Create the watchlist application read model that consumes the official market-data watchlist read surface, fails closed when market-data is not readable, rejects invalid/incomplete candidate rows, and adds static guards against raw/latest/MAX(date) bypass.

Evidence:

- `app/Application/Watchlist/Services/WatchlistMarketDataConsumerReadService.php` created.
- `app/Infrastructure/Persistence/MarketData/MarketDataWatchlistReadRepository.php` hardened for publication/run scope, active ticker scope, valid indicator rows, required non-null indicator fields, and eligible rows only.
- `tests/Unit/Watchlist/WatchlistMarketDataConsumerReadServiceTest.php` added.
- `tests/Unit/Watchlist/WatchlistMarketDataConsumerReadModelStaticGuardTest.php` added.
- Existing governance static guard from Phase 0 remains valid.
- Scoring, recommendation, backtest, API, command, scheduler, and runtime artifact generation remain out of scope.

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
| WL-CONTRACT-006 | SCORING DETERMINISM CONTRACT | `NOT_STARTED` |
| WL-CONTRACT-007 | PARAMSET VERSION CONTRACT | `NOT_STARTED` |
| WL-CONTRACT-008 | SIGNAL EXPLAINABILITY CONTRACT | `NOT_STARTED` |
| WL-CONTRACT-009 | BACKTEST NO-LOOKAHEAD CONTRACT | `NOT_STARTED` |
| WL-CONTRACT-010 | BACKTEST REPRODUCIBILITY CONTRACT | `NOT_STARTED` |
| WL-CONTRACT-011 | RISK GATE CONTRACT | `NOT_STARTED` |
| WL-CONTRACT-012 | PORTFOLIO AWARENESS BOUNDARY | `NOT_STARTED` |
| WL-CONTRACT-013 | AUDIT ARTIFACT CONTRACT | `NOT_STARTED` |
| WL-CONTRACT-014 | DOCS SYNC CONTRACT | `PARTIAL` |
| WL-CONTRACT-015 | PRODUCTION READINESS CONTRACT | `NOT_STARTED` |

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
- `app/Application/MarketData/Services/MarketDataWatchlistReadService.php` upstream consumer read gateway
- `app/Infrastructure/Persistence/MarketData/MarketDataWatchlistReadRepository.php` upstream publication-scoped row source

Tests:

- `tests/Unit/Watchlist/WatchlistMarketDataConsumerReadServiceTest.php`
- `tests/Unit/Watchlist/WatchlistMarketDataConsumerReadModelStaticGuardTest.php`
- upstream reference: `tests/Unit/MarketData/MarketDataWatchlistReadModelTest.php`

Runtime proof:
`NOT_STARTED` — no watchlist command/API exists yet.

Current gaps:

- Watchlist read model exists and consumes the upstream market-data watchlist read surface.
- Contract is not `LOCKED` because there is no watchlist command/API runtime proof and no artifact/log output yet.
- Scoring/recommendation/backtest consumers have not consumed this read model yet.

Acceptance criteria:

- Watchlist reads market-data only from current readable publication pointer.
- Consumed publication is sealed, `SUCCESS`, `READABLE`, coverage `PASS`, and mirror-valid through upstream market-data readiness.
- Failure to resolve valid publication fails safe.
- No raw/staging/latest fallback exists in watchlist application code.
- Static guard covers the no-bypass constraint.

Last update:
`2026-05-28 — WATCHLIST — MARKET-DATA CONSUMER READ MODEL EXECUTION SESSION`

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
- `app/Infrastructure/Persistence/MarketData/MarketDataWatchlistReadRepository.php` upstream hardened query boundary

Tests:

- `tests/Unit/Watchlist/WatchlistMarketDataConsumerReadModelStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistMarketDataConsumerReadServiceTest.php`

Runtime proof:
`NOT_STARTED` — no watchlist command/API exists yet.

Current gaps:

- Watchlist application code currently has no direct DB read and consumes `MarketDataWatchlistReadService` only.
- Static guard blocks `DB::table`, raw market-data table names, staging/latest/MAX(date) shortcuts in watchlist application code.
- Contract is not `LOCKED` until future watchlist consumers are added and guarded.

Acceptance criteria:

- Watchlist does not directly consume raw provider response, staging tables, unsealed bars, unsealed indicators, or unsealed eligibility rows.
- Static guard rejects raw market-data bypass patterns in watchlist code.
- Any future repository/API/command must preserve this boundary or update the guard.

Last update:
`2026-05-28 — WATCHLIST — MARKET-DATA CONSUMER READ MODEL EXECUTION SESSION`

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

Tests:

- `tests/Unit/Watchlist/WatchlistMarketDataConsumerReadModelStaticGuardTest.php`

Runtime proof:
`NOT_STARTED` — no watchlist command/API exists yet.

Current gaps:

- Watchlist read model delegates date/publication resolution to market-data.
- Static guard forbids `MAX(trade_date)`, `max('trade_date')`, `latest()`, `orderByDesc('trade_date')`, and descending date fallback in watchlist application code.
- Contract is not `LOCKED` until all future watchlist read consumers are added and covered.

Acceptance criteria:

- Date/effective publication resolution is owned by market-data current readable publication pointer.
- Watchlist code does not infer data freshness via `MAX(trade_date)`, `latest()`, descending date limit, or fallback to newest available raw row.
- Any future backtest/recommendation/API code must use the same resolved publication/effective date contract.

Last update:
`2026-05-28 — WATCHLIST — MARKET-DATA CONSUMER READ MODEL EXECUTION SESSION`

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
`2026-05-28 — WATCHLIST — MARKET-DATA CONSUMER READ MODEL EXECUTION SESSION`

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
`NOT_STARTED`

Owner docs:

- `docs/watchlist/system/policies/weekly_swing/08_WS_PLAN_ALGORITHM.md`
- `docs/watchlist/system/policies/weekly_swing/09_WS_DYNAMIC_SELECTION_DETERMINISTIC.md`
- `docs/watchlist/system/policies/weekly_swing/04_WS_PARAMSET_JSON_CONTRACT.md`
- `docs/watchlist/system/policies/weekly_swing/05_WS_PARAMETER_REGISTRY_COMPLETE.md`

Implementation files:
`NOT_STARTED`

Tests:
`NOT_STARTED`

Runtime proof:
`NOT_STARTED`

Current gaps:

- No scoring engine exists.

Acceptance criteria:

- Same publication input + same paramset + same universe produces the same score and ranking.
- Tie-breaking is deterministic.
- Tests cover deterministic output.

Last update:
`2026-05-28`

---

## WL-CONTRACT-007 — PARAMSET VERSION CONTRACT

Contract ID:
`WL-CONTRACT-007`

Title:
`PARAMSET VERSION CONTRACT`

Status:
`NOT_STARTED`

Owner docs:

- `docs/watchlist/system/policies/weekly_swing/04_WS_PARAMSET_JSON_CONTRACT.md`
- `docs/watchlist/system/policies/weekly_swing/05_WS_PARAMETER_REGISTRY_COMPLETE.md`
- `docs/watchlist/system/policies/_shared/02_PARAMSET_CONTRACT_GLOBAL.md`
- `docs/watchlist/system/policies/weekly_swing/20_WS_CANONICAL_PARAMSET_PROCEDURES.md`

Implementation files:
`NOT_STARTED`

Tests:
`NOT_STARTED`

Runtime proof:
`NOT_STARTED`

Current gaps:

- No runtime paramset loader/validator exists.

Acceptance criteria:

- Every scoring/recommendation/backtest execution has traceable paramset version.
- Paramset validation rejects missing, unknown, invalid, or type-drifted fields.
- Artifact output records paramset version/hash.

Last update:
`2026-05-28`

---

## WL-CONTRACT-008 — SIGNAL EXPLAINABILITY CONTRACT

Contract ID:
`WL-CONTRACT-008`

Title:
`SIGNAL EXPLAINABILITY CONTRACT`

Status:
`NOT_STARTED`

Owner docs:

- `docs/watchlist/system/policies/weekly_swing/07_WS_REASON_CODES_AND_HASH.md`
- `docs/watchlist/system/policies/weekly_swing/22_WS_RECOMMENDATION_OVERVIEW.md`
- `docs/watchlist/system/policies/weekly_swing/23_WS_RECOMMENDATION_INPUT_OUTPUT_CONTRACT.md`
- `docs/watchlist/system/policies/weekly_swing/25_WS_RECOMMENDATION_REASON_CODES_AND_TESTS.md`

Implementation files:
`NOT_STARTED`

Tests:
`NOT_STARTED`

Runtime proof:
`NOT_STARTED`

Current gaps:

- No signal/recommendation runtime exists.

Acceptance criteria:

- Every signal/recommendation has explainable reason/factor output.
- Output includes enough factor breakdown to audit why a ticker is included, watched, avoided, or rejected.

Last update:
`2026-05-28`

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

- Backtest can be replayed with the same dataset version, publication scope, paramset version, universe, date range, and artifact manifest.
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
`NOT_STARTED`

Owner docs:

- `docs/watchlist/system/policies/weekly_swing/08_WS_PLAN_ALGORITHM.md`
- `docs/watchlist/system/policies/weekly_swing/09_WS_DYNAMIC_SELECTION_DETERMINISTIC.md`
- `docs/watchlist/system/policies/weekly_swing/22_WS_RECOMMENDATION_OVERVIEW.md`
- `docs/watchlist/system/policies/weekly_swing/24_WS_RECOMMENDATION_ALGORITHM.md`

Implementation files:
`NOT_STARTED`

Tests:
`NOT_STARTED`

Runtime proof:
`NOT_STARTED`

Current gaps:

- No runtime risk/liquidity/volatility gate exists.

Acceptance criteria:

- Watchlist does not rank only potential return.
- Candidate selection includes risk, liquidity, volatility, and guard failure handling.
- Risk gate output is explainable.

Last update:
`2026-05-28`

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
- `docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md`
- `docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md`

Tests:

- `WatchlistAuditGovernanceStaticGuardTest` added for initial docs guard.
- `WatchlistMarketDataConsumerReadModelStaticGuardTest` added for Phase 1 docs/code synchronization guard.

Runtime proof:
`NOT_STARTED`

Current gaps:

- Phase 1 code/test/docs sync completed for market-data consumer read model.
- Contract remains `PARTIAL` because future scoring/recommendation/backtest/API docs are not implemented yet.

- Docs sync foundation exists, but future code/config/schema/test/runtime changes still need ongoing enforcement.
- Runtime watchlist docs sync cannot be proven until runtime watchlist code exists.

Acceptance criteria:

- Every watchlist code/config/schema/test/behavior change updates implementation status and contract tracker.
- Active session name is aligned between status and tracker.
- Tracker contracts reflect actual code/test/runtime status without overclaim.

Last update:
`2026-05-28 — WATCHLIST — MARKET-DATA CONSUMER READ MODEL EXECUTION SESSION`

---

## WL-CONTRACT-015 — PRODUCTION READINESS CONTRACT

Contract ID:
`WL-CONTRACT-015`

Title:
`PRODUCTION READINESS CONTRACT`

Status:
`NOT_STARTED`

Owner docs:

- `docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md`
- `docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md`
- `docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md`
- `docs/watchlist/system/policy.md`
- `docs/watchlist/system/policies/weekly_swing/**`

Implementation files:
`NOT_STARTED`

Tests:
`NOT_STARTED`

Runtime proof:
`NOT_STARTED`

Current gaps:

- Main watchlist code not implemented.
- Runtime proof not available.
- Artifacts not available.
- Core contracts WL-CONTRACT-001 through WL-CONTRACT-013 are not locked.

Acceptance criteria:

- Market-data consumer read model locked.
- No raw/latest/`MAX(date)` bypass.
- Required indicator and eligibility guards locked.
- Scoring deterministic and explainable.
- Paramset version traceable.
- Recommendation output tested.
- Backtest no-lookahead and reproducible.
- Risk gates present.
- Artifact/log proof present.
- Full watchlist test suite passes.
- Runtime command/API proof passes.
- Docs sync complete.

Last update:
`2026-05-28`

## Next Required Contract Work

Next session must target:

`WATCHLIST — MARKET-DATA CONSUMER READ MODEL EXECUTION SESSION`

Priority contracts:

1. `WL-CONTRACT-001`
2. `WL-CONTRACT-002`
3. `WL-CONTRACT-003`
4. `WL-CONTRACT-004`
5. `WL-CONTRACT-005`
6. `WL-CONTRACT-014`

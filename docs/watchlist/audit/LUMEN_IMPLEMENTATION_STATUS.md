# Watchlist Lumen Implementation Status

## Document Purpose

Dokumen ini mencatat status implementasi watchlist pada codebase Lumen. Dokumen ini adalah status tracker, bukan owner behavior bisnis.

Behavioral owner tetap:

1. `docs/watchlist/system/policy.md`
2. `docs/watchlist/system/README.md`
3. `docs/watchlist/system/policies/weekly_swing/**`
4. `docs/watchlist/system/implementation/weekly_swing/**` untuk translation guidance
5. `docs/watchlist/audit/**` untuk audit guardrail dan status tracking

## ACTIVE SESSION

Session:
`WATCHLIST — BACKTEST RUNTIME ARTIFACT AND METRICS EXECUTION SESSION`

Status:
`DONE for runtime artifact and metrics foundation unit/static scope / LOCAL_PHPUNIT_PASS / NOT_PRODUCTION_READY`. Phase 7 remains `PHASE_7_BACKTEST_STRATEGY_ENGINE_FOUNDATION_DONE / NOT_PRODUCTION_READY`. Phase 6 remains `PHASE_6_CONFIRM_OVERLAY_FOUNDATION_DONE / NOT_PRODUCTION_READY`. Local validation for the current scope is PASS: `WatchlistBacktest` 25 tests / 286 assertions, full `tests\Unit\Watchlist` 116 tests / 1168 assertions, and `MarketDataWatchlistReadModelTest` 3 tests / 41 assertions. Readiness-critical production scope remains `NOT_PRODUCTION_READY` because no watchlist command/API runtime proof, production schema, production persisted artifact/log evidence, portfolio-aware integration, or walk-forward/OOS proof exists yet.

Scope:
Add a runtime-safe backtest artifact and metrics foundation on top of the existing `WatchlistBacktestStrategyService` output. This session keeps the PLAN / RECOMMENDATION / CONFIRM boundary intact, adds deterministic artifact shaping, JSON export foundation, metrics aggregation, fail-safe pricing/calendar diagnostics, and static/unit guard coverage. This session explicitly does not create portfolio allocation, position sizing final, broker instruction, order recommendation, execution automation, API endpoint, artisan command, scheduler, production migration, or production schema.

Evidence:

- `app/Application/Watchlist/Services/WatchlistBacktestStrategyService.php` remains the Phase 7 boundary and is not rewritten.
- `app/Application/Watchlist/Services/WatchlistBacktestRuntimeArtifactService.php` added.
- `app/Application/Watchlist/Services/WatchlistBacktestMetricsService.php` added.
- `tests/Unit/Watchlist/WatchlistBacktestRuntimeArtifactServiceTest.php` added.
- `tests/Unit/Watchlist/WatchlistBacktestMetricsServiceTest.php` added.
- `tests/Unit/Watchlist/WatchlistBacktestRuntimeArtifactStaticGuardTest.php` added.
- Runtime artifact output records `meta`, `source_contract`, `backtest_contract`, `paramset_snapshot`, `replay_window`, `input_manifest`, `items`, `trades`, `evaluations`, `metrics`, `summary`, `diagnostics`, `artifact_manifest`, and `validation`.
- Artifact manifest references official Weekly Swing backtest artifacts including `watchlist_bt_eval`, `watchlist_bt_picks_ws`, `watchlist_bt_universe_ws`, `watchlist_bt_cutoffs_ws`, and `watchlist_bt_oos_eval_ws`.
- Metrics foundation records total replay dates, total recommendations, evaluated trade count, win rate, average return, median return, max gain, max loss, hit target count, hit stop count, hold-expired count, empty recommendation days, rejected/no-data evaluation count, missing price/calendar diagnostics, and reason-code distribution.
- If published EOD price series or explicit trading calendar input is missing, metrics fail safe with `WATCHLIST_BACKTEST_PRICE_SERIES_UNAVAILABLE`, `WATCHLIST_BACKTEST_CALENDAR_UNAVAILABLE`, and `WATCHLIST_BACKTEST_EVALUATION_SKIPPED_NO_PUBLISHED_PRICE`; no raw/staging/unsealed/latest fallback is created.
- JSON artifact export foundation exists through `WatchlistBacktestRuntimeArtifactService::writeJsonArtifact()` only; no command/API/production schema/persistence surface is added.
- Local validation after the metrics float-output correction is fully PASS:
  - `vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktest"` — OK (25 tests, 286 assertions).
  - `vendor\bin\phpunit tests\Unit\Watchlist` — OK (116 tests, 1168 assertions).
  - `vendor\bin\phpunit tests\Unit\MarketData --filter "MarketDataWatchlistReadModelTest"` — OK (3 tests, 41 assertions).
- Watchlist Production Ready remains `NO`.

## Source of Truth ZIP

- Source ZIP: `tradeaxis-api.zip`
- Session date: `2026-06-08`
- Latest local validation date: `2026-06-09`
- Scope classification: watchlist backtest runtime artifact and metrics foundation code + tests + docs sync.

## Current Implementation Baseline

| Area | Status | Notes |
|---|---|---|
| Current status | `DONE for runtime artifact and metrics foundation unit/static scope / LOCAL_PHPUNIT_PASS / NOT_PRODUCTION_READY` | Read model, candidate universe gates, deterministic scoring foundation, deterministic PLAN grouping foundation, deterministic recommendation foundation, confirm overlay foundation, backtest strategy foundation, runtime artifact foundation, and metrics foundation exist at unit/static scope. Whole watchlist remains not production-ready. |
| Main feature code | `DONE for runtime artifact and metrics foundation unit/static scope` | Read model, candidate universe gates, scoring foundation, PLAN grouping foundation, final recommendation foundation, confirm overlay foundation, backtest strategy foundation, runtime artifact foundation, and metrics foundation exist at service + unit/static scope. Command/API runtime remains not started. |
| Runtime API | `NOT_STARTED` | No API endpoint created. |
| Artisan command surface | `NOT_STARTED` | No watchlist command created. |
| Database schema | `NOT_STARTED` | No production migration created. Existing SQL docs/fixtures are support artifacts only. |
| Backtest engine | `DONE for runtime artifact and metrics foundation unit/static scope / LOCAL_PHPUNIT_PASS` | `WatchlistBacktestStrategyService` remains intact; `WatchlistBacktestRuntimeArtifactService` and `WatchlistBacktestMetricsService` add deterministic artifact/metrics foundation. The current backtest filter and full watchlist suite pass locally. No runtime command/API, production schema, production persisted artifact evidence, or walk-forward/OOS proof exists. |
| Recommendation engine | `DONE for Phase 5 unit/static scope` | `WatchlistRecommendationService` derives recommendation only from PLAN grouping output. Confirm overlay now consumes recommendation membership as immutable snapshot and does not mutate it. |
| PLAN grouping engine | `DONE for Phase 4 unit/static scope` | `WatchlistPlanGroupingService` maps Phase 3 scored output into deterministic PLAN groups only. |
| Scoring engine | `DONE / LOCAL PASS` | `WatchlistScoringService` computes deterministic PLAN component scores and ranking over Phase 2 eligible universe rows; baseline validation at the start of this session is local PASS. |
| Market-data consumer read model | `DONE` | `WatchlistMarketDataConsumerReadService` consumes the official market-data read surface and validates candidate readiness. |
| Candidate universe / liquidity-risk gates | `DONE` | `WatchlistCandidateUniverseService` applies deterministic liquidity, ATR/risk, and volume participation guards over Phase 1 candidates. |
| Test coverage | `DONE / LOCAL_PHPUNIT_PASS` | `WatchlistBacktest` OK (25 tests, 286 assertions); full `tests\Unit\Watchlist` OK (116 tests, 1168 assertions); `MarketDataWatchlistReadModelTest` OK (3 tests, 41 assertions). |
| Artifact/log output | `DONE for runtime artifact foundation unit/static scope` | Runtime-safe artifact shaping and JSON export foundation exist in service scope. No command/API production artifact/log proof exists yet. |
| Production readiness | `NOT_READY` | Watchlist is not production-ready. |

## Existing Docs Discovered

The ZIP already contains a substantial watchlist documentation baseline:

- root docs: `docs/watchlist/README.md`, `docs/watchlist/LAYER_ACTIVATION_RULE.md`;
- root system policy: `docs/watchlist/system/policy.md`, `docs/watchlist/system/README.md`;
- audit guardrails: `docs/watchlist/audit/README.md`, `WATCHLIST_AUDIT_FOUNDATION.md`, `WATCHLIST_SCOPE_LOCK.md`, `WATCHLIST_OWNER_MATRIX.md`, `WATCHLIST_AUDIT_CHECKLIST_FINAL.md`, `WATCHLIST_AUDIT_PROMPT_STANDARD.md`, `WATCHLIST_CHANGE_IMPACT_MATRIX.md`;
- implementation audit guardrails: `docs/watchlist/audit/implementation/**`;
- Weekly Swing policy docs: `docs/watchlist/system/policies/weekly_swing/**`;
- shared policy docs: `docs/watchlist/system/policies/_shared/**`;
- implementation guidance: `docs/watchlist/system/implementation/weekly_swing/**`;
- support docs/artifacts: `_refs`, `examples`, `fixtures`, `db`, SQL files, JSON fixtures.

## Owner Hierarchy Summary

The active owner hierarchy for watchlist is:

1. `docs/watchlist/README.md` — root overview and navigation.
2. `docs/watchlist/system/policy.md` — highest behavioral/governance owner for watchlist.
3. `docs/watchlist/LAYER_ACTIVATION_RULE.md` — layer activation and audit classification rule.
4. `docs/watchlist/system/policies/weekly_swing/**` — domain policy owner for active Weekly Swing strategy.
5. `docs/watchlist/system/implementation/weekly_swing/**` — implementation translation only, not business owner.
6. `docs/watchlist/audit/**` — audit governance, status, checklist, prompt, and tracker.
7. `docs/watchlist/audit/implementation/**` — implementation audit guardrail.
8. `docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md` — actual Lumen implementation progress and evidence tracker.
9. `docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md` — contract lock/status tracker.
10. `docs/watchlist/system/policies/weekly_swing/db/**` — persistence/schema support, not owner of business rules.
11. `docs/watchlist/system/policies/weekly_swing/_refs/**`, `examples/**`, `fixtures/**` — support artifacts only.

Rules:

- Audit docs must not replace policy owner docs.
- `docs/watchlist/system/policy.md` remains the root behavioral owner.
- `LUMEN_IMPLEMENTATION_STATUS.md` records progress only.
- `LUMEN_CONTRACT_TRACKER.md` tracks contracts derived from system/policy docs and valid upstream market-data contracts.

## Market-Data Dependency

Watchlist depends on market-data as the official upstream data source.

Watchlist must consume:

- sealed publication;
- `SUCCESS` run;
- `READABLE` publication;
- coverage `PASS`;
- valid current publication pointer;
- valid publication/run mirror;
- valid indicator rows;
- valid eligibility rows.

Watchlist must not consume:

- raw provider response;
- raw staging table;
- unsealed `eod_bars`;
- unsealed `eod_indicators`;
- unsealed `eod_eligibility`;
- `MAX(trade_date)` shortcut;
- latest available row without publication pointer;
- indicator rows with required null values;
- invalid indicator rows.

Market-data production-ready does not automatically make watchlist production-ready. Watchlist must prove its own read contract, scoring contract, backtest contract, and runtime behavior.

## Created Governance Files

| File | Status | Purpose |
|---|---|---|
| `docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md` | `DONE` for initial foundation | Defines update rules, status taxonomy, evidence rule, anti-overclaim, docs sync, market-data dependency, and readiness claim rules. |
| `docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md` | `DONE` for initial foundation | Tracks current implementation status, evidence, validation, gaps, and roadmap. |
| `docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md` | `DONE` for initial foundation | Defines baseline contracts WL-CONTRACT-001 through WL-CONTRACT-015. |
| `tests/Unit/Watchlist/WatchlistAuditGovernanceStaticGuardTest.php` | `DONE` for initial foundation | Guards existence and critical wording of the three governance tracker docs. |

## Phase 1 Created / Updated Files

| File | Status | Purpose |
|---|---|---|
| `app/Application/Watchlist/Services/WatchlistMarketDataConsumerReadService.php` | `DONE` for Phase 1 | Watchlist application read model over market-data consumer surface. Fails closed when market-data is not readable and excludes invalid/incomplete candidate rows. |
| `app/Infrastructure/Persistence/MarketData/MarketDataWatchlistReadRepository.php` | `UPDATED` | Upstream consumer row source hardened for publication/run scope, active tickers, valid indicators, non-null required fields, and eligibility. |
| `tests/Unit/Watchlist/WatchlistMarketDataConsumerReadServiceTest.php` | `DONE` for Phase 1 | Covers valid candidates, fail-closed market-data readiness, and invalid/incomplete row rejection. |
| `tests/Unit/Watchlist/WatchlistMarketDataConsumerReadModelStaticGuardTest.php` | `DONE` for Phase 1 | Guards no raw/latest/MAX(date) bypass and docs sync for read model session. |

## Phase 2 Created / Updated Files

| File | Status | Purpose |
|---|---|---|
| `app/Application/Watchlist/Services/WatchlistCandidateUniverseService.php` | `DONE` for Phase 2 | Builds deterministic PLAN candidate universe from Phase 1 read-model candidates and applies WS liquidity, ATR/risk, and volume participation guards. |
| `tests/Unit/Watchlist/WatchlistCandidateUniverseServiceTest.php` | `DONE` for Phase 2 | Covers accepted/rejected candidate paths, canonical reason priority, source fail-closed behavior, nested paramset value shape, and ATR fraction-unit rejection. |
| `tests/Unit/Watchlist/WatchlistCandidateUniverseStaticGuardTest.php` | `DONE` for Phase 2 | Guards no raw/latest/MAX(date) bypass, required reason codes, default paramset baseline, and docs sync for candidate universe session. |
| `tests/Support/MarketData/SeedsConsumerReadModelFixture.php` | `UPDATED` | Corrects fixture `atr14_pct` to fractional value (`0.021`) matching market-data indicator computation and WS policy units. |

## Phase 3 Created / Updated Files

| File | Status | Purpose |
|---|---|---|
| `app/Application/Watchlist/Services/WatchlistScoringService.php` | `DONE / LOCAL PASS` | Computes deterministic PLAN scoring from Phase 2 candidate universe rows only; Phase 3 baseline validation is local PASS at the start of this session. |
| `tests/Unit/Watchlist/WatchlistScoringServiceTest.php` | `DONE / LOCAL PASS` | Covers weighted score computation, exclusion, fail-closed source readiness, range clamp, ATR unit drift, deterministic tie-break, output contracts, and no recommendation/confirm/execution fields. |
| `tests/Unit/Watchlist/WatchlistScoringStaticGuardTest.php` | `DONE / LOCAL PASS` | Guards scoring service boundary, no raw/latest/MAX(date) access, reason-code parity, deterministic sort keys, and docs sync. |
| `app/Application/Watchlist/Services/WatchlistCandidateUniverseService.php` | `UPDATED` | Preserves scoring metrics and `ticker_id` from Phase 1 output while keeping Phase 2 gate semantics unchanged. |
| `app/Application/Watchlist/Services/WatchlistMarketDataConsumerReadService.php` | `UPDATED` | Passes through `ticker_id` when upstream provides it. |
| `app/Infrastructure/Persistence/MarketData/MarketDataWatchlistReadRepository.php` | `UPDATED` | Adds `ticker_id` to the publication-scoped watchlist read rows for deterministic tie-break input only. |

## Phase 4 Created / Updated Files

| File | Status | Purpose |
|---|---|---|
| `app/Application/Watchlist/Services/WatchlistPlanGroupingService.php` | `DONE for Phase 4 unit/static scope` | Consumes Phase 3 scored output and maps valid scored candidates into deterministic PLAN groups `TOP_PICKS`, `SECONDARY`, `WATCH_ONLY`, and diagnostics `AVOID`. |
| `tests/Unit/Watchlist/WatchlistPlanGroupingServiceTest.php` | `DONE for Phase 4 unit/static scope` | Covers deterministic grouping, fail-closed source readiness, invalid scored items, top/secondary overflow, low-score AVOID, metadata traceability, contracts, tie-breaks, dedupe, forbidden output fields, and invalid grouping paramsets. |
| `tests/Unit/Watchlist/WatchlistPlanGroupingStaticGuardTest.php` | `DONE for Phase 4 unit/static scope` | Guards scoring-only consumption, no raw/latest/MAX(date) bypass, no recommendation/confirm/execution/backtest leakage, reason-code docs sync, and Lumen audit tracker sync. |
| `docs/watchlist/system/policies/weekly_swing/07_WS_REASON_CODES_AND_HASH.md` | `UPDATED` | Adds PLAN grouping reason codes as PLAN-only diagnostics/membership codes. |
| `docs/watchlist/system/policies/weekly_swing/25_WS_RECOMMENDATION_REASON_CODES_AND_TESTS.md` | `UPDATED` | Adds boundary reference that PLAN grouping reason codes are not final recommendation reason codes. |
| `docs/watchlist/system/policies/weekly_swing/03_WS_DATA_MODEL_MARIADB.md` | `UPDATED` | Clarifies that `policy_version` / `schema_version` field names are contract labels, not application versioning claims. |
| `docs/watchlist/system/policies/weekly_swing/04_WS_PARAMSET_JSON_CONTRACT.md` | `UPDATED` | Clarifies that bootstrap labels do not use `_V1` and support fixture suffixes are artifact identifiers only. |
| `docs/watchlist/system/policies/weekly_swing/db/REASON_CODES_SEED.sql` | `UPDATED` | Adds support seed rows for PLAN grouping reason-code parity. |

## Phase 5 Created / Updated Files

| File | Status | Purpose |
|---|---|---|
| `app/Application/Watchlist/Services/WatchlistRecommendationService.php` | `DONE for Phase 5 unit/static scope` | Consumes Phase 4 PLAN grouping output and builds deterministic recommendation output only from PLAN `TOP_PICKS` and `SECONDARY`. |
| `tests/Unit/Watchlist/WatchlistRecommendationServiceTest.php` | `DONE for Phase 5 unit/static scope` | Covers PLAN-only source, fail-closed source readiness, empty recommendation behavior, dynamic target cap, capital-free mode, capital-aware feasibility, deterministic tie-breaks, metadata traceability, output contracts, invalid paramsets, and confirm/execution/backtest boundary. |
| `tests/Unit/Watchlist/WatchlistRecommendationStaticGuardTest.php` | `DONE for Phase 5 unit/static scope` | Guards PLAN grouping-only consumption, no raw/latest/MAX(date) bypass, no confirm/execution/portfolio/backtest leakage, reason-code docs sync, and Lumen audit tracker sync. |
| `docs/watchlist/system/policies/weekly_swing/db/REASON_CODES_SEED.sql` | `UPDATED` | Synchronizes recommendation support seed rows with owner docs for `WS_REC_BORDERLINE`, `WS_REC_RANK_OUTSIDE_DYNAMIC_TARGET`, and `WS_REC_CAPITAL_AWARE`. |


## Phase 6 Created / Updated Files

| File | Status | Purpose |
|---|---|---|
| `app/Application/Watchlist/Services/WatchlistConfirmOverlayService.php` | `DONE for Phase 6 unit/static scope` | Consumes immutable PLAN candidate binding and recommendation membership snapshot, then adds CONFIRM overlay metadata without mutating recommendation membership, rank, score, label, or hash. |
| `tests/Unit/Watchlist/WatchlistConfirmOverlayServiceTest.php` | `DONE for Phase 6 unit/static scope` | Covers recommended and non-recommended PLAN candidate confirm, unknown/non-PLAN diagnostics, source metadata preservation, immutability, and forbidden portfolio/execution/backtest/API/command fields. |
| `tests/Unit/Watchlist/WatchlistConfirmOverlayStaticGuardTest.php` | `DONE for Phase 6 unit/static scope` | Guards PLAN/recommendation-only consumption, no raw/latest/MAX(date) bypass, no allocation/execution/backtest/runtime leakage, reason-code docs sync, and Lumen audit tracker sync. |

## Phase 7 Created / Updated Files

| File | Status | Purpose |
|---|---|---|
| `app/Application/Watchlist/Services/WatchlistBacktestStrategyService.php` | `DONE for Phase 7 unit/static scope` | Consumes PLAN grouping, recommendation, and confirm overlay outputs through explicit replay windows; preserves no-lookahead, deterministic replay, publication-aware alignment, and explainable foundation output without runtime persistence. |
| `tests/Unit/Watchlist/WatchlistBacktestStrategyServiceTest.php` | `DONE for Phase 7 unit/static scope` | Covers no-lookahead failure, deterministic replay, empty recommendation behavior, confirm-overlay diagnostic boundary, unknown/rejected confirm evidence, explainable output shape, and no portfolio/broker/order/runtime surface leakage. |
| `tests/Unit/Watchlist/WatchlistBacktestStrategyStaticGuardTest.php` | `DONE for Phase 7 unit/static scope` | Guards PLAN/recommendation/confirm-only consumption, no raw/latest/`MAX(trade_date)` bypass, no allocation/order/runtime surface, backtest reason-code traceability, artifact manifest references, and Lumen audit tracker sync. |
| `docs/watchlist/system/policies/weekly_swing/07_WS_REASON_CODES_AND_HASH.md` | `UPDATED` | Adds CONFIRM overlay foundation reason-code owner entries. |
| `docs/watchlist/system/policies/weekly_swing/10_WS_CONFIRM_OVERLAY.md` | `UPDATED` | Adds CONFIRM overlay foundation reason-code semantics and immutability boundary wording. |
| `docs/watchlist/system/policies/weekly_swing/25_WS_RECOMMENDATION_REASON_CODES_AND_TESTS.md` | `UPDATED` | Adds boundary reference that CONFIRM reason codes are not final recommendation reason codes and cannot mutate recommendation fields. |
| `docs/watchlist/system/policies/weekly_swing/db/REASON_CODES_SEED.sql` | `UPDATED` | Synchronizes support seed rows for CONFIRM overlay foundation reason-code parity. |


## Runtime Artifact and Metrics Created / Updated Files

| File | Status | Purpose |
|---|---|---|
| `app/Application/Watchlist/Services/WatchlistBacktestRuntimeArtifactService.php` | `DONE for runtime artifact and metrics foundation unit/static scope` | Builds deterministic runtime-safe backtest artifact output from Phase 7 payload; includes official artifact manifest references, input manifest, metrics, diagnostics, validation, artifact hash, and JSON export foundation without command/API/production schema. |
| `app/Application/Watchlist/Services/WatchlistBacktestMetricsService.php` | `DONE for runtime artifact and metrics foundation unit/static scope` | Builds fail-safe metrics from backtest payload and explicit published EOD price series + trading-calendar input only; emits missing price/calendar diagnostics instead of raw/latest fallback. |
| `tests/Unit/Watchlist/WatchlistBacktestRuntimeArtifactServiceTest.php` | `DONE for runtime artifact and metrics foundation unit/static scope` | Covers artifact shape, deterministic hash, fail-safe metric diagnostics, source-payload preservation, boundary flags, and JSON export foundation. |
| `tests/Unit/Watchlist/WatchlistBacktestMetricsServiceTest.php` | `DONE for runtime artifact and metrics foundation unit/static scope` | Covers missing price/calendar fail-safe behavior, time-exit evaluation using explicit published input, target/stop/hold-expired counts, return metrics, and determinism. |
| `tests/Unit/Watchlist/WatchlistBacktestRuntimeArtifactStaticGuardTest.php` | `DONE for runtime artifact and metrics foundation unit/static scope` | Guards no raw/staging/unsealed market-data bypass, no latest/`MAX(trade_date)` shortcut, no API/command/schema/execution leakage, and Lumen audit docs sync. |

Runtime artifact/metrics diagnostic codes in this session are internal backtest diagnostics, not canonical WS recommendation reason codes:

- `WATCHLIST_BACKTEST_PRICE_SERIES_UNAVAILABLE`
- `WATCHLIST_BACKTEST_CALENDAR_UNAVAILABLE`
- `WATCHLIST_BACKTEST_EVALUATION_SKIPPED_NO_PUBLISHED_PRICE`
- `WATCHLIST_BACKTEST_RUNTIME_ARTIFACT_READY_WITH_EVALUATION_SKIPPED`

## Active Gaps

| Severity | Gap | Impact |
|---|---|---|
| `RUNTIME_PROOF_MISSING` | No watchlist command/API runtime proof exists for read model, candidate universe, scoring, PLAN grouping, recommendation, confirm overlay, or backtest strategy foundation. | Core contracts remain not `LOCKED` because command/API runtime proof and production persisted artifact/log evidence are still missing. |
| `RUNTIME_PROOF_MISSING` | Confirm overlay, backtest strategy, runtime artifact, and metrics foundations exist at service + unit/static scope. | Foundations are implemented and locally validated, but no watchlist command/API runtime proof or production persisted artifact/log evidence exists yet. |
| `RUNTIME_PROOF_MISSING` | Runtime-safe artifact shaping and JSON export foundation exist, but no command-driven production persisted artifact/log evidence exists. | The artifact contract remains not `LOCKED`, and production readiness cannot be claimed. |
| `DOCS_ONLY` | Governance/read model/candidate universe/scoring/PLAN grouping/recommendation/confirm/backtest/artifact/metrics docs are synced for unit/static scope, but future API/command/persistence docs and code still require implementation sync. | Correct for current scope; not production readiness. |
| `NOT_READY` | No runtime API, command, production schema, command-driven production artifact persistence, portfolio-aware boundary implementation, or walk-forward/OOS proof exists. | Watchlist Production Ready remains `NO`. |

## First Implementation Roadmap

### Phase 0 — Governance Foundation

- Create audit governance.
- Create implementation status.
- Create contract tracker.
- Map existing docs.
- Define owner hierarchy.

Status: `DONE` for initial foundation.

### Phase 1 — Market-Data Consumer Read Model

- Read from current readable publication only.
- No raw/latest bypass.
- Validate required indicators.
- Validate eligibility.
- Add static guard tests.

Status: `DONE` for code + unit/static tests. Contracts remain `PARTIAL` until watchlist command/API runtime proof and artifact/log evidence exist.

### Phase 2 — Watchlist Candidate Universe

- Define universe rules.
- Define liquidity/risk filters.
- Define eligibility from market-data.
- Add tests.

Status: `DONE` for deterministic candidate universe + liquidity/risk/volume gate code and unit/static tests. Contracts remain `PARTIAL` until command/API runtime proof and artifact/log evidence exist.

### Phase 3 — Scoring Engine Foundation

- Define score factors.
- Define weight/paramset.
- Deterministic scoring.
- Explainable score breakdown.
- Add tests.

Status: `DONE / LOCAL PASS` for Phase 3 unit/static scope. Contracts remain not `LOCKED` until watchlist command/API runtime proof and artifact/log evidence exist.

### Phase 4 — PLAN Grouping + TOP_PICKS/SECONDARY

- Consume Phase 3 scored output.
- Produce PLAN group semantics `TOP_PICKS`, `SECONDARY`, `WATCH_ONLY`, and `AVOID`.
- Apply deterministic sort, threshold, limit, and dedupe contracts.
- Preserve source scoring metadata and paramset traceability.
- Add tests.

Status: `DONE for Phase 4 unit/static scope`. This is not final recommendation, confirm, API/command runtime, persistence runtime, or production readiness.

### Phase 5 — Final Recommendation Layer Foundation

- Consume Phase 4 PLAN grouping output.
- Produce `meta`, `items`, and `summary` recommendation output.
- Select only from PLAN `TOP_PICKS` and `SECONDARY`.
- Preserve empty recommendation behavior.
- Preserve availability without CONFIRM.
- Add tests.

Status: `DONE for Phase 5 unit/static scope`. This is not confirm, API/command runtime, persistence runtime, backtest, portfolio allocation, broker instruction, execution, or production readiness.

### Phase 6 — Confirm Overlay Foundation

- Bind CONFIRM eligibility to candidate PLAN.
- Allow recommended and non-recommended PLAN candidates to confirm.
- Preserve recommendation immutability.
- Add tests.

Status: `DONE for Phase 6 unit/static scope`. Confirm overlay implementation and static/unit coverage are covered by the local full watchlist PHPUnit proof. This is not API/command runtime, persistence runtime, backtest runtime, portfolio allocation, broker instruction, execution, or production readiness.

### Phase 7 — Backtest Strategy Engine

- Consume immutable PLAN grouping output, recommendation output, and confirm overlay output.
- Use explicit replay windows.
- Preserve no-lookahead by rejecting future-effective source outputs.
- Preserve deterministic replay ordering.
- Produce explainable foundation output with diagnostics and official artifact-manifest references.
- Add unit/static tests.

Status: `DONE for Phase 7 unit/static scope`. Service, tests, static guard, docs sync, PHP lint, and local PHPUnit proof exist. This is not API/command runtime, persistence runtime, completed pricing metric engine, artifact persistence, portfolio allocation, broker instruction, execution, or production readiness.

### Phase 8 — Portfolio-Aware Integration

- Current holding awareness.
- Position sizing guidance.
- Risk exposure.
- No execution automation unless explicitly designed.

Status: `NOT_STARTED`.

### Phase 9 — API/Command Surface

- Artisan commands.
- API endpoints.
- Output contract.
- Evidence artifacts.

Status: `NOT_STARTED`.

### Phase 10 — Production Readiness Audit

- Full PHPUnit.
- StaticGuard.
- Runtime command proof.
- Artifact proof.
- Docs sync.

Status: `NOT_STARTED`.

## Evidence Log

### 2026-05-28 — WATCHLIST — AUDIT GOVERNANCE + LUMEN TRACKER FOUNDATION

Status: `DONE` for governance foundation.

Evidence:

- New governance/tracker files created.
- Existing audit README and owner matrix synchronized with new tracker files.
- Lightweight docs static guard added.
- Operator local validation passed: `WatchlistAuditGovernanceStaticGuardTest` — 5 tests, 44 assertions.
- No scoring/recommendation/backtest/API/command implementation created.

### 2026-05-28 — WATCHLIST — MARKET-DATA CONSUMER READ MODEL EXECUTION SESSION

Status: `DONE` for Phase 1 read model scope; `PARTIAL` for readiness-critical contracts.

Evidence:

- `WatchlistMarketDataConsumerReadService` created under `app/Application/Watchlist/Services`.
- Watchlist service consumes `MarketDataWatchlistReadService` instead of reading DB/raw market-data directly.
- Service returns candidate universe metadata with `source_contract`, required indicator list, publication/run metadata, and reason-coded readiness.
- Service fails closed when market-data has no readable publication.
- Service rejects invalid, non-eligible, or incomplete rows even if such rows appear in an upstream payload.
- Upstream market-data watchlist repository now filters publication/run scoped rows, active ticker rows, eligible rows, `ind.is_valid = 1`, `invalid_reason_code IS NULL`, and required indicator fields non-null.
- Static guard blocks raw DB reads, raw market-data table names, latest shortcuts, and `MAX(trade_date)` patterns in watchlist application code.
- No scoring/recommendation/backtest/API/command logic was created.

### 2026-05-28 — WATCHLIST — CANDIDATE UNIVERSE + LIQUIDITY/RISK FILTER EXECUTION SESSION

Status: `DONE` for Phase 2 candidate universe + liquidity/risk/volume gate scope; `PARTIAL` for readiness-critical contracts.

Evidence:

- `WatchlistCandidateUniverseService` created under `app/Application/Watchlist/Services`.
- Candidate universe consumes `WatchlistMarketDataConsumerReadService` only; it does not read DB/raw market-data directly.
- Default paramset baseline follows WS active policy values: `min_dv20_idr=1000000000`, `dv20_strong_idr=5000000000`, `min_vol_ratio=1.2`, `min_atr14_pct=0.02`, `max_atr14_pct=0.12`, `atr_ideal_low=0.035`, `atr_ideal_high=0.075`.
- Guard output uses canonical WS reason codes: `WS_DATA_MISSING`, `WS_LIQ_FAIL`, `WS_ATR_LOW`, `WS_ATR_HIGH`, `WS_VOLR_FAIL`, plus informational `WS_LIQ_STRONG`, `WS_LIQ_BORDER`, `WS_RISK_IDEAL`, `WS_RISK_HIGH`, `WS_RISK_LOW`.
- Output includes production/backtest-equivalence fields: `required_ok`, `guard_ok`, `eligible_plan`, `canonical_fail_reason_code`, `missing_fields`, `gate_metrics`, and `gate_thresholds`.
- Service rejects invalid ATR paramset units above 1.0 to prevent percent-point/fraction drift.
- Static guard extends no raw/latest/MAX(date) coverage to the candidate universe service.
- No final scoring/recommendation/backtest/API/command logic was created.

### 2026-05-29 — WATCHLIST — SCORING ENGINE FOUNDATION EXECUTION SESSION

Status: `PARTIAL` until local PHPUnit confirms Phase 3 scoring unit/static guards; readiness-critical contracts remain `PARTIAL`.

Evidence:

- `WatchlistScoringService` created under `app/Application/Watchlist/Services`.
- Scoring consumes `WatchlistCandidateUniverseService` only; it does not read DB/raw market-data directly and does not consume `WatchlistMarketDataConsumerReadService` directly.
- Output includes `source_contract`, `score_contract`, `paramset_snapshot`, `score_components`, `score_weights`, `factor_breakdown`, `reason_codes`, and deterministic `ranking_keys`.
- Component scores implemented: `score_momentum`, `score_breakout`, `score_volume`, and `score_risk`, each clamped to `0..1`.
- `score_total` uses deterministic `WEIGHTED_MEAN` with bootstrap weights: momentum `0.30`, breakout `0.30`, volume `0.20`, risk `0.20`.
- Deterministic sort keys implemented: `score_total_desc`, `score_breakout_desc`, `score_momentum_desc`, `dv20_idr_desc`, `atr14_pct_asc`, `ticker_id_asc`.
- Scoring rejects candidates that are not `eligible_plan=true` and `guard_ok=true`, rejects missing scoring metrics, and rejects ATR unit drift where `atr14_pct > 1`.
- Candidate universe output now preserves scoring metrics from Phase 1 rows so scoring does not bypass Phase 2.
- `ticker_id` is passed through the read model/repository strictly for deterministic tie-break input.
- No recommendation membership, confirm overlay, portfolio allocation, order instruction, execution action, backtest metric, API, command, scheduler, or runtime artifact output was created.

### 2026-06-05 — WATCHLIST — PLAN GROUPING + TOP_PICKS / SECONDARY SELECTION EXECUTION SESSION

Status: `DONE for Phase 4 unit/static scope`; local validation in this session passed and readiness-critical contracts remain `PARTIAL`.

Evidence:

- `WatchlistPlanGroupingService` created under `app/Application/Watchlist/Services`.
- PLAN grouping consumes `WatchlistScoringService` only; it does not read DB/raw market-data directly and does not consume `WatchlistCandidateUniverseService` or `WatchlistMarketDataConsumerReadService` directly.
- Output includes `source_contract`, `group_contract`, `paramset_snapshot`, `groups`, `excluded`, and deterministic `summary`.
- Default bootstrap grouping contract uses `PLAN_GROUPING_DETERMINISTIC`, top-picks min score `0.70` max `5`, secondary min score `0.55` max `10`, watch-only min score `0.40` max `20`, and avoid low-score boundary `0.40`.
- PLAN groups implemented: `TOP_PICKS`, `SECONDARY`, `WATCH_ONLY`, and diagnostics `AVOID`.
- Deterministic sort keys preserved from Phase 3 scoring: `score_total_desc`, `score_breakout_desc`, `score_momentum_desc`, `dv20_idr_desc`, `atr14_pct_asc`, `ticker_id_asc`.
- Duplicate `ticker_id` is deduplicated by deterministic best item before active PLAN group assignment.
- Scoring excluded candidates and invalid scored items do not enter active PLAN groups; they remain diagnostics via `AVOID`.
- PLAN grouping reason codes added: `WS_PLAN_TOP_PICK`, `WS_PLAN_SECONDARY`, `WS_PLAN_WATCH_ONLY`, `WS_PLAN_AVOID_LOW_SCORE`, `WS_PLAN_AVOID_EXCLUDED`.
- No final recommendation membership, recommendation label, confirm overlay, portfolio allocation, order instruction, execution action, backtest metric, API, command, scheduler, persistence runtime, or runtime artifact output was created.

### 2026-06-05 — WATCHLIST — FINAL RECOMMENDATION LAYER FOUNDATION EXECUTION SESSION

Status: `DONE for Phase 5 unit/static scope`; local validation in this session passed and readiness-critical contracts remain `PARTIAL`.

Evidence:

- `WatchlistRecommendationService` created under `app/Application/Watchlist/Services`.
- Recommendation consumes `WatchlistPlanGroupingService` only; it does not read DB/raw market-data directly and does not consume scoring, candidate-universe, or market-data read services directly.
- Output includes `meta`, `items`, and `summary`, matching the recommendation owner docs shape.
- Recommendation source universe is limited to PLAN groups `TOP_PICKS` and `SECONDARY`; `WATCH_ONLY` and diagnostics `AVOID` do not enter recommendation evaluation.
- Default recommendation contract uses `PLAN_DERIVED_DETERMINISTIC`, dynamic count mode `THRESHOLD_AND_CAP`, min recommendation score `0.70`, borderline min score `0.55`, max recommended items `3`, and deterministic sort keys `recommendation_score_desc`, `plan_rank_asc`, `plan_group_priority_asc`, and `ticker_id_asc`.
- Empty recommendation is valid and sets `empty_recommendation_flag = true` when `recommended_count = 0`, even if prioritized PLAN groups are non-empty.
- `CAPITAL_FREE` mode works without capital input.
- Limited `CAPITAL_AWARE` mode supports deterministic affordability feasibility from explicit capital input/minimum-lot values without creating portfolio allocation, suggested lots, broker instruction, or execution logic.
- Recommendation output ignores confirm-like fields if malformed upstream payloads include them and does not emit confirm state/status.
- Recommendation reason codes are explainable: `WS_REC_SELECTED`, `WS_REC_NOT_SELECTED`, `WS_REC_BORDERLINE`, `WS_REC_EMPTY_SET`, `WS_REC_RANK_OUTSIDE_DYNAMIC_TARGET`, `WS_REC_CAPITAL_AWARE`, `WS_REC_CAPITAL_INSUFFICIENT`, and `WS_REC_MIN_LOT_NOT_AFFORDABLE`.
- No confirm overlay, portfolio allocation, order instruction, execution action, backtest metric, API, command, scheduler, persistence runtime, or runtime artifact output was created.


### 2026-06-05 — WATCHLIST — CONFIRM OVERLAY FOUNDATION EXECUTION SESSION

Status: `DONE for Phase 6 unit/static scope` after local full watchlist PHPUnit proof; readiness-critical production contracts remain `PARTIAL` / `NOT_READY`.

Evidence:

- `WatchlistConfirmOverlayService` created and bound to immutable PLAN candidate output from `WatchlistPlanGroupingService`.
- Service uses `WatchlistRecommendationService` only as immutable recommendation membership snapshot.
- Recommended PLAN candidates can be confirmed without changing recommendation membership, rank, score, label, or hash.
- Non-recommended active PLAN candidates can be confirmed without becoming recommended.
- Unknown/non-active candidate evidence is rejected into diagnostics/excluded output.
- Service output contains `source_contract`, `confirm_contract`, `immutability_contract`, `items`, `excluded`, and `summary`.
- Static guard covers no raw market-data, no latest/`MAX(trade_date)`, and no portfolio/execution/backtest/API/command leakage.


### 2026-06-08 — WATCHLIST — BACKTEST STRATEGY ENGINE FOUNDATION LOCAL VALIDATION UPDATE

Session:
`WATCHLIST — BACKTEST STRATEGY ENGINE FOUNDATION EXECUTION SESSION`

Status: `PHASE_7_BACKTEST_STRATEGY_ENGINE_FOUNDATION_DONE / NOT_PRODUCTION_READY`.

Local validation proof:

```text
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestStrategy"
OK (13 tests, 152 assertions)

vendor\bin\phpunit tests\Unit\MarketData --filter "MarketDataWatchlistReadModelTest"
OK (3 tests, 41 assertions)

vendor\bin\phpunit tests\Unit\Watchlist
OK (104 tests, 1034 assertions)
```

Notes:

- Phase 7 is DONE for unit/static scope only.
- Empty recommendation behavior is fixed and validated: no active trades/evaluations are created and `WATCHLIST_BACKTEST_EMPTY_RECOMMENDATION` is emitted for empty recommendation runs.
- Production readiness remains `NOT_READY` because runtime API/command, persisted artifacts/logs, production schema, completed pricing metric engine, portfolio-aware integration, and walk-forward/OOS proof do not exist yet.

## 2026-06-09 — WATCHLIST — BACKTEST RUNTIME ARTIFACT AND METRICS LOCAL VALIDATION UPDATE

Status: `DONE for runtime artifact and metrics foundation unit/static scope / LOCAL_PHPUNIT_PASS / NOT_PRODUCTION_READY`.

Local validation proof:

```text
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktest"
OK (25 tests, 286 assertions)

vendor\bin\phpunit tests\Unit\Watchlist
OK (116 tests, 1168 assertions)

vendor\bin\phpunit tests\Unit\MarketData --filter "MarketDataWatchlistReadModelTest"
OK (3 tests, 41 assertions)
```

Validation impact:

- The metrics float-output correction is validated; the time-exit metrics case now passes with the required float contract.
- All runtime artifact/metrics unit and static guard tests pass.
- The full watchlist suite increased from 104 to 116 tests and remains green.
- The upstream market-data watchlist read-model guard remains green.
- Phase 6 and Phase 7 baselines remain DONE for their unit/static scopes.
- No contract is promoted to `LOCKED`, and production readiness remains `NOT_READY`, because command/API runtime proof, production persisted artifact evidence, production schema, and walk-forward/OOS proof are still missing.

## Validation Log

Validation performed in this session and later local proof:

```text
php -l app/Application/Watchlist/Services/WatchlistConfirmOverlayService.php
php -l tests/Unit/Watchlist/WatchlistConfirmOverlayServiceTest.php
php -l tests/Unit/Watchlist/WatchlistConfirmOverlayStaticGuardTest.php
php -d zend.assertions=1 -d assert.exception=1 /tmp/confirm_smoke.php
vendor/bin/phpunit tests/Unit/Watchlist --filter "WatchlistConfirmOverlay"
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestStrategy"
vendor\bin\phpunit tests\Unit\MarketData --filter "MarketDataWatchlistReadModelTest"
vendor\bin\phpunit tests\Unit\Watchlist
```

Observed validation result in the original sandbox and local runtime:

```text
php -l app/Application/Watchlist/Services/WatchlistConfirmOverlayService.php: PASS
php -l tests/Unit/Watchlist/WatchlistConfirmOverlayServiceTest.php: PASS
php -l tests/Unit/Watchlist/WatchlistConfirmOverlayStaticGuardTest.php: PASS
Direct confirm overlay smoke test: PASS
vendor/bin/phpunit tests/Unit/Watchlist --filter "WatchlistConfirmOverlay": BLOCKED in original sandbox only
Reason: PHPUnit requires PHP extensions dom, json, libxml, mbstring, tokenizer, xml, xmlwriter; original sandbox PHP is missing dom, mbstring, xml, and xmlwriter. Local full watchlist PHPUnit proof has since passed.
```

Local PHPUnit proof upgrades Phase 7 to DONE for unit/static scope. No `LOCKED` or production-ready claim is made because runtime API/command proof and persisted artifact/log evidence are still missing.

## Latest Completed Local Validation

The required local validation for the current runtime artifact and metrics foundation scope is complete:

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktest"
# OK (25 tests, 286 assertions)

vendor\bin\phpunit tests\Unit\Watchlist
# OK (116 tests, 1168 assertions)

vendor\bin\phpunit tests\Unit\MarketData --filter "MarketDataWatchlistReadModelTest"
# OK (3 tests, 41 assertions)
```

Validated outcomes:

- runtime artifact and metrics tests pass;
- full watchlist regression suite passes;
- market-data watchlist consumer read-model test remains green;
- no regression is observed in Phase 1 through Phase 7 unit/static coverage;
- documentation static guards pass as part of the full watchlist suite;
- local PHPUnit is no longer a pending requirement for this session.

## Production Readiness Status

`NOT_READY`.

Reason:

- market-data consumer read model exists but has no watchlist command/API runtime proof yet;
- candidate universe, scoring foundation, PLAN grouping foundation, recommendation foundation, confirm overlay foundation, and backtest strategy foundation exist only at service + unit/static scope;
- runtime artifact and metrics foundation exists at service + unit/static scope, but no command/API production artifact/log proof exists yet;
- no runtime API;
- no command surface;
- no production watchlist schema/migration;
- backtest strategy foundation is DONE and runtime artifact/metrics foundation exists, but there is no runtime command/API proof, production persisted artifact proof, portfolio-aware integration, or walk-forward/OOS proof;
- no portfolio-aware integration;
- WL-CONTRACT-001 through WL-CONTRACT-008, WL-CONTRACT-011, WL-CONTRACT-014, WL-CONTRACT-016, WL-CONTRACT-017, WL-CONTRACT-018, and WL-CONTRACT-019 are not `LOCKED` because runtime proof/artifact evidence is missing;
- WL-CONTRACT-009, WL-CONTRACT-010, and WL-CONTRACT-013 are `DONE for runtime artifact and metrics foundation unit/static scope` but not `LOCKED`; WL-CONTRACT-012 remains `NOT_STARTED`.

## Next Required Sessions

Recommended next session:

`WATCHLIST — BACKTEST PUBLISHED PRICE SERIES RUNTIME PROOF EXECUTION SESSION`

Target:

- keep Phase 7 strategy service and runtime artifact/metrics services intact;
- connect backtest evaluation only to official published/sealed EOD price series and explicit trading-calendar input;
- add runtime proof or command only if it remains artifact generation/evaluation only;
- persist/export official artifact-manifest output with deterministic evidence hash;
- prove no-lookahead, reproducibility, publication-aware replay, metric sufficiency, and docs sync locally;
- do not add portfolio allocation, position sizing final, broker instruction, order recommendation, or execution automation.


## Runtime Artifact and Metrics Foundation Update — 2026-06-08

Status:
`DONE for runtime artifact and metrics foundation unit/static scope / NOT_PRODUCTION_READY`.

Evidence added:

- `WatchlistBacktestRuntimeArtifactService.php` builds deterministic runtime artifact shape with official manifest references, input manifest, metrics, diagnostics, validation, and artifact hash.
- `WatchlistBacktestMetricsService.php` builds metrics from backtest output and explicit published EOD price/calendar input only.
- Missing official price/calendar inputs fail safe with `WATCHLIST_BACKTEST_PRICE_SERIES_UNAVAILABLE`, `WATCHLIST_BACKTEST_CALENDAR_UNAVAILABLE`, and `WATCHLIST_BACKTEST_EVALUATION_SKIPPED_NO_PUBLISHED_PRICE`.
- Unit/static tests added for artifact service, metrics service, and static boundary guard.
- No command/API/scheduler/migration/production schema was added.
- No raw/staging/unsealed market-data reader, latest shortcut, or `MAX(trade_date)` shortcut was added.
- No portfolio allocation, position sizing final, broker instruction, order recommendation, or execution automation was added.

Validation note:

- Local PHPUnit validation is complete and PASS: `WatchlistBacktest` 25 tests / 286 assertions; full watchlist 116 tests / 1168 assertions; `MarketDataWatchlistReadModelTest` 3 tests / 41 assertions.
- The earlier metrics float-output and audit baseline-marker failures were corrected, and the complete requested validation set now passes.

Production readiness:

- Watchlist Production Ready remains `NO`.
- Contracts are not promoted to `LOCKED` because command/API runtime proof, production persisted artifact evidence, production schema, and walk-forward/OOS proof are still missing.

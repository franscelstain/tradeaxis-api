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
`WATCHLIST — BACKTEST PUBLISHED PRICE SERIES RUNTIME PROOF EXECUTION SESSION`

Status:
`DONE for published price series runtime proof scope / LOCAL_RUNTIME_PROOF_PASS / NOT_PRODUCTION_READY`.

Historical baselines remain preserved and are not downgraded:

- `PHASE_6_CONFIRM_OVERLAY_FOUNDATION_DONE / NOT_PRODUCTION_READY`;
- `PHASE_7_BACKTEST_STRATEGY_ENGINE_FOUNDATION_DONE / NOT_PRODUCTION_READY`;
- `DONE for runtime artifact and metrics foundation unit/static scope / LOCAL_PHPUNIT_PASS / NOT_PRODUCTION_READY`;
- prior local evidence remains `WatchlistBacktest` 25 tests / 286 assertions, full `tests\Unit\Watchlist` 116 tests / 1168 assertions, and `MarketDataWatchlistReadModelTest` 3 tests / 41 assertions.

Scope:
Connect the Phase 7 replay boundary to official market-data application read surfaces for explicit trading-calendar windows and exact-date current readable published EOD OHLCV, then orchestrate strategy replay, price evaluation, canonical metrics, deterministic artifact generation, and explicit JSON export through a watchlist proof command. No portfolio allocation, order, broker instruction, scheduler, API endpoint, market-data producer behavior, or production migration is added.

Current evidence:

- official application read surfaces now exist for trading calendar and exact-date published EOD series;
- watchlist runtime orchestration freezes the strategy payload before future-price reads and verifies that payload remains unchanged;
- runtime artifact now records calendar, price-series, publication lineage, runtime execution, canonical metrics, and a deterministic canonical artifact hash;
- command `watchlist:backtest-published-price-proof` requires explicit `--from`, `--to`, and `--output`;
- final operator PHPUnit passed: PublishedPrice `17 tests / 146 assertions`, MetricsService `8 / 63`, Backtest `48 / 497`, full Watchlist `139 / 1379`, PublishedEodSeries `6 / 29`, TradingCalendar `4 / 16`, and existing MarketData watchlist read model `3 / 41`;
- official operator command replay `2026-05-21` through `2026-05-29` passed twice after zero-volume, threshold-binding, and dynamic-coverage closure;
- both final runs resolved 9/9 required price dates, evaluated 13 trades, emitted 2 reason-coded non-fatal diagnostics, resolved metric thresholds, and produced equal canonical artifact hash `0eaa353d20df901c4f372c0000951408578bf302`;
- publication lineage passed for 10/10 dates through `2026-06-08` with current pointer, `SEALED`, `SUCCESS`, `READABLE`, coverage `PASS`, and current-publication identity;
- dynamic coverage proof passed with `days_covered=5`, `total_trading_days_in_window=5`, effective `min_days_covered=4`, and `minimum_coverage=true`;
- metric thresholds are resolved with `min_trades=120`; `metric_calibration_valid=0` is expected for this smoke window because only 13 trades were evaluated;
- KING correctly emitted `BT_SKIP_MISSING_OHLC_EXIT` after ignoring zero-volume dates `2026-05-25`, `2026-05-26`, and `2026-05-29`; no synthetic exit or zero return was created;
- BKDP correctly emitted `BT_SKIP_NO_TRADABLE_ENTRY` with `entry_volume=0`;
- file SHA-1 differed between runs because output path and execution metadata are non-hashed, while canonical artifact hash equality passed;
- watchlist remains `NOT_PRODUCTION_READY`; no contract is promoted to `LOCKED`, and walk-forward/OOS plus production operating proof remain outstanding.

## Source of Truth ZIP

- Source ZIP: `tradeaxis-api.zip`
- Session date: `2026-06-09`
- Latest local validation date: `2026-06-09`
- Scope classification: watchlist published-price runtime integration + official operator proof + zero-volume/metric-threshold closure + corrected dynamic coverage semantics; final operator rerun passed.

## Current Implementation Baseline

| Area | Status | Notes |
|---|---|---|
| Current status | `DONE for published price series runtime proof scope / LOCAL_RUNTIME_PROOF_PASS / NOT_PRODUCTION_READY` | Read model, candidate universe gates, deterministic scoring foundation, deterministic PLAN grouping foundation, deterministic recommendation foundation, confirm overlay foundation, backtest strategy foundation, runtime artifact foundation, and metrics foundation exist at unit/static scope. Whole watchlist remains not production-ready. |
| Main feature code | `DONE for published-price runtime proof scope` | Official calendar and published OHLC read surfaces, watchlist runtime orchestration, command, artifact export, metrics, and tests exist; final operator PHPUnit and database-backed command proof passed. |
| Runtime API | `NOT_STARTED` | No API endpoint created. |
| Artisan command surface | `IMPLEMENTED / LOCAL_RUNTIME_PROOF_PASS` | Command produced two final deterministic artifacts on the supported operator environment with equal canonical artifact hash. |
| Database schema | `NOT_STARTED` | No production migration created. Existing SQL docs/fixtures are support artifacts only. |
| Backtest engine | `DONE for published-price runtime proof scope` | Phase 7 strategy remains intact. Published-price orchestration, zero-volume execution semantics, metric threshold binding, dynamic coverage, final PHPUnit regression, and two-run command proof pass. OOS and production operating proof remain incomplete. |
| Recommendation engine | `DONE for Phase 5 unit/static scope` | `WatchlistRecommendationService` derives recommendation only from PLAN grouping output. Confirm overlay now consumes recommendation membership as immutable snapshot and does not mutate it. |
| PLAN grouping engine | `DONE for Phase 4 unit/static scope` | `WatchlistPlanGroupingService` maps Phase 3 scored output into deterministic PLAN groups only. |
| Scoring engine | `DONE / LOCAL PASS` | `WatchlistScoringService` computes deterministic PLAN component scores and ranking over Phase 2 eligible universe rows; baseline validation at the start of this session is local PASS. |
| Market-data consumer read model | `DONE for published-price range-read runtime proof scope` | Existing watchlist read model remains intact; new market-data application services expose official trading-calendar windows and exact-date current-readable published EOD OHLCV without watchlist DB access. |
| Candidate universe / liquidity-risk gates | `DONE` | `WatchlistCandidateUniverseService` applies deterministic liquidity, ATR/risk, and volume participation guards over Phase 1 candidates. |
| Test coverage | `LOCAL_PHPUNIT_PASS` | Final operator evidence: PublishedPrice 17/146, MetricsService 8/63, Backtest 48/497, full Watchlist 139/1379, PublishedEodSeries 6/29, TradingCalendar 4/16, existing Watchlist read model 3/41. |
| Artifact/log output | `DONE for deterministic JSON runtime evidence scope` | Two final official command artifacts exist with equal canonical hash `0eaa353d20df901c4f372c0000951408578bf302`; production persistence/operating evidence remains out of scope. |
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
| `EXTERNAL_DEPENDENCY` | Sandbox runs PHP `8.4.16`; project Artisan guard requires PHP `< 8.4`. | Official command/database runtime proof cannot be executed here. |
| `RUNTIME_PROOF_MISSING` | New PHPUnit filters and full regression suite have not been executed against the current patch under supported PHP. | New integration remains `PARTIAL`, although prior Phase 6/7/artifact/metrics baselines remain preserved. |
| `RUNTIME_PROOF_MISSING` | Registered command has not been run twice against an official fixture/integration database. | Command exit behavior, DB-backed exact-date publication lineage, and command-level hash reproducibility remain unproven. |
| `REVIEW_REQUIRED` | File 12 locks TP/SL/time-exit while file 16 states fixed holding as default. | Metric sufficiency cannot be claimed complete until owner precedence/governance is explicit. |
| `NOT_READY` | Walk-forward/OOS proof and production operating proof do not exist. | `WL-CONTRACT-015` remains `PARTIAL / NOT_READY`; watchlist remains `NOT_PRODUCTION_READY`. |

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

- Published-price Artisan proof command implemented with explicit `--from`, `--to`, and `--output`.
- No API endpoint and no scheduler.
- Deterministic JSON output contract exists at service level.
- Official Artisan/database evidence remains blocked in this sandbox.

Status: `PARTIAL / RUNTIME_BLOCKED`.

### Phase 10 — Production Readiness Audit

- Historical full PHPUnit baseline preserved; current patch PHPUnit still required under supported PHP.
- Grouped static validation passes.
- Controlled service/read-surface artifact proof passes.
- Official runtime command/database proof, OOS proof, and production operating proof remain missing.
- Docs sync is current for this patch.

Status: `IN_PROGRESS / NOT_READY`.

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

This section preserves the last completed local baseline before the current published-price patch. It is historical evidence and does not claim that the new tests were executed in the sandbox.

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
- local PHPUnit was no longer pending for that historical runtime-artifact foundation session; the current published-price patch still requires supported-environment PHPUnit execution.

## Production Readiness Status

`NOT_READY`.

Reason:

- Phase 6, Phase 7, runtime artifact foundation, and metrics foundation remain historically validated and are not downgraded;
- official trading-calendar and exact-date published OHLC read surfaces, orchestration, command, and deterministic service artifacts now exist;
- current-patch PHPUnit regression and official command/database runtime proof passed on the supported operator environment;
- file 12/file 16 exit-model wording and zero-volume execution semantics are synchronized;
- no walk-forward/OOS proof;
- no production operating proof;
- no portfolio/execution scope was added;
- no contract is `LOCKED` and `WL-CONTRACT-015` remains `PARTIAL / NOT_READY`.

## Next Required Sessions

Recommended next session:

`WATCHLIST — WALK-FORWARD AND OUT-OF-SAMPLE PROOF EXECUTION SESSION`

Target:

- consume the completed published-price runtime proof as the immutable replay foundation;
- implement and prove chronological train/calibration/test segmentation without future leakage;
- keep exact-date current-readable publication, calendar, paramset, and artifact lineage per fold;
- prove deterministic fold construction, OOS-only evaluation, aggregate OOS metrics, and reproducible artifacts;
- retain zero-volume non-tradable handling and no synthetic fills;
- keep portfolio allocation, final position sizing, broker instruction, execution automation, scheduler, and production API out of scope;
- keep watchlist `NOT_PRODUCTION_READY` until OOS and production operating evidence are complete.


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

## Published Price Series Runtime Integration Update — 2026-06-09

Session:
`WATCHLIST — BACKTEST PUBLISHED PRICE SERIES RUNTIME PROOF EXECUTION SESSION`

Status:
`PARTIAL — implementation and controlled service runtime proof complete / official Artisan and database runtime proof blocked / NOT_PRODUCTION_READY`.

Implemented files:

- `app/Application/MarketData/Services/MarketDataTradingCalendarReadService.php`;
- `app/Application/MarketData/Services/MarketDataPublishedEodSeriesReadService.php`;
- `app/Infrastructure/Persistence/MarketData/MarketDataPublishedEodSeriesReadRepository.php`;
- `app/Infrastructure/Persistence/MarketData/MarketCalendarRepository.php`;
- `app/Application/Watchlist/Services/WatchlistBacktestPublishedPriceRuntimeService.php`;
- `app/Application/Watchlist/Services/WatchlistBacktestStrategyService.php`;
- `app/Application/Watchlist/Services/WatchlistBacktestMetricsService.php`;
- `app/Application/Watchlist/Services/WatchlistBacktestRuntimeArtifactService.php`;
- `app/Console/Commands/Watchlist/RunBacktestPublishedPriceProofCommand.php`;
- `app/Console/Kernel.php`.

Added test files:

- `tests/Unit/MarketData/MarketDataTradingCalendarReadModelTest.php`;
- `tests/Unit/MarketData/MarketDataPublishedEodSeriesReadModelTest.php`;
- `tests/Unit/Watchlist/WatchlistBacktestPublishedPriceRuntimeServiceTest.php`;
- `tests/Unit/Watchlist/WatchlistBacktestPublishedPriceProofCommandTest.php`;
- `tests/Unit/Watchlist/WatchlistBacktestPublishedPriceRuntimeStaticGuardTest.php`.

Modified test files:

- `tests/Unit/Watchlist/WatchlistBacktestStrategyServiceTest.php`;
- `tests/Unit/Watchlist/WatchlistBacktestMetricsServiceTest.php`.

Audit docs updated:

- `docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md`;
- `docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md`.

Runtime evidence written:

- `storage/app/watchlist/backtest/published-price-service-proof-run-1.json`;
- `storage/app/watchlist/backtest/published-price-service-proof-run-2.json`;
- `storage/app/watchlist/backtest/published-price-service-proof-missing-exit.json`;
- `storage/app/watchlist/backtest/published-price-service-proof-evidence.json`;
- `storage/app/watchlist/backtest/published-price-read-surface-proof-evidence.json`.

Validation evidence:

- `php -l`: PASS for 17 changed/new PHP source and test files, 0 failures;
- grouped static validation: PASS, 0 failures;
- controlled application-service runtime proof: PASS, 25 direct assertions;
- controlled market-data application read-surface proof: PASS, 21 direct assertions;
- strategy paramset snapshot regression smoke: PASS, 4 direct assertions;
- command argument fail-safe smoke without Artisan bootstrap: PASS, 4 direct assertions;
- canonical artifact hash run 1: `bb2268bbc053d7aa85fd5a400e834c519cfd3429`;
- canonical artifact hash run 2: `bb2268bbc053d7aa85fd5a400e834c519cfd3429`;
- canonical hash equality: PASS;
- file SHA-1 differs because `generated_at`, `executed_at`, and output path are intentionally non-hashed metadata;
- metric required fields are available for the controlled proof, but calibration validity is false because the proof has only one evaluated trade and does not meet file 16 gates;
- missing publication and future-effective source fail closed; missing exit OHLC remains a reason-coded skip with `ret_net = null`.

Sandbox blockers:

- `php artisan watchlist:backtest-published-price-proof --from=2026-05-19 --to=2026-05-19 --output=storage/app/watchlist/backtest/command-proof-blocked.json --overwrite` exits `2` with `ENV_UNSUPPORTED_PHP_VERSION`; project requires PHP `>= 7.3` and `< 8.4`, sandbox is PHP `8.4.16`, and no command artifact is written;
- each requested PHPUnit command exits `1` before test discovery because required extensions `dom`, `mbstring`, `xml`, and `xmlwriter` are absent; test and assertion counts are therefore unavailable for the current patch;
- official database-backed command proof therefore remains `BLOCKED`.

Historical pre-closure owner conflict — RESOLVED by the later gap-closure update:

- file 12 locks TP/SL/time-exit up to D+5;
- file 16 states fixed holding as its default evaluation model;
- the closure update explicitly aligns file 16 to file 12 rule-based TP/SL/time-exit semantics;
- metric sufficiency remains `PARTIAL` only until the current closure-patch PHPUnit and runtime rerun are completed.

Production readiness:

- `NOT_PRODUCTION_READY`;
- `WL-CONTRACT-015` remains `PARTIAL / NOT_READY`;
- no contract is promoted to `LOCKED`.



## Published Price Runtime Proof and Gap Closure Update — 2026-06-09

Session:
`WATCHLIST — BACKTEST PUBLISHED PRICE SERIES RUNTIME PROOF EXECUTION SESSION`

Status:
`PARTIAL — LOCAL_RUNTIME_PROOF_PASS for the operator-tested pre-closure build / zero-volume tradability and canonical metric-threshold closure implemented / current coverage-fix patch rerun required / NOT_PRODUCTION_READY`.

### Operator PHPUnit evidence

```text
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestPublishedPrice"
OK (13 tests, 87 assertions)

vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktest"
OK (39 tests, 375 assertions)

vendor\bin\phpunit tests\Unit\Watchlist
OK (130 tests, 1257 assertions)

vendor\bin\phpunit tests\Unit\MarketData --filter "MarketDataPublishedEodSeries"
OK (6 tests, 29 assertions)

vendor\bin\phpunit tests\Unit\MarketData --filter "MarketDataTradingCalendar"
OK (4 tests, 16 assertions)

vendor\bin\phpunit tests\Unit\MarketData --filter "MarketDataWatchlistReadModelTest"
OK (3 tests, 41 assertions)
```

The first PublishedEodSeries attempt exposed a test-fixture error caused by inserting a historical publication row into live `eod_bars`, whose canonical key is `(trade_date, ticker_id)`. The fixture was corrected to place the non-current row in `eod_bars_history`; the rerun passed 6 tests / 29 assertions. No production reader defect was found.

### Official operator runtime evidence before closure patch

Replay window:

```text
from=2026-05-21
to=2026-05-29
replay_date_count=5
calendar_date_count=10
required_price_date_count=9
resolved_price_date_count=9
evaluated_trade_count=13
diagnostic_count=2
```

Two command runs completed with `status=PASS`. Canonical artifact hash matched:

```text
run 1 artifact_hash=03dce5cbd7176a6065dc711e0d9907a2279f9cc3
run 2 artifact_hash=03dce5cbd7176a6065dc711e0d9907a2279f9cc3
hash equality=PASS
```

File SHA-1 differed because output path and execution metadata are intentionally excluded from the canonical artifact hash. Publication evidence covered 10/10 required dates from `2026-05-21` through `2026-06-08`; each date resolved a current pointer with `SEALED`, `SUCCESS`, `READABLE`, coverage `PASS`, and current-publication identity.

The two non-fatal diagnostics were:

```text
2026-05-21 KING BT_SKIP_MISSING_OHLC_EXIT
2026-05-26 BKDP BT_SKIP_MISSING_OHLC_EXIT
```

Operator bar inspection confirmed inactive/non-trading rows: equal OHLC with `volume = 0`, followed by unavailable bars. This exposed a semantic gap: a published row is not automatically an executable backtest fill.

### Closure implemented after operator proof

- Entry and exit fills now require numeric `volume >= 1`.
- Zero-volume rows remain valid published market-data rows but are ignored for TP/SL and cannot become entry, exit, or synthetic zero-return fills.
- Added canonical diagnostics `BT_SKIP_NO_TRADABLE_ENTRY` and `BT_SKIP_NO_TRADABLE_EXIT`.
- BKDP-like D+1 zero-volume cases now fail at entry; zero-volume days inside a KING-like exit horizon are recorded in `ignored_non_tradable_exit_dates`.
- Runtime paramset snapshot now carries all required `eval` thresholds.
- Canonical bootstrap floors are `min_trades = 120`, `min_trades_oos = 40`, downside `-0.03`, monthly win-rate `0.45`, and monthly average `-0.01`.
- `min_days_covered = 0` is only a dynamic sentinel; metrics resolves it to `ceil(70% * total_trading_days_in_window)` and writes both configured and effective thresholds.
- Runtime export fails closed with `WS_BT_EVAL_METRICS_MISSING` when required thresholds remain unresolved.
- File 16 is synchronized with file 12: active execution is TP/SL with deterministic stop priority and time-exit at a maximum five-trading-day horizon; fixed holding is not the active default.

Controlled closure-patch validation confirms:

```text
php -l: PASS for all 9 closure-patch PHP source/test files
static parity/safety validation: PASS, 20 assertions
gap-closure metrics harness: PASS, 12 assertions
controlled runtime determinism harness: PASS, 10 assertions
canonical hash run 1: e2d725378e6df67ffa579017fdbb2399e8bdc322
canonical hash run 2: e2d725378e6df67ffa579017fdbb2399e8bdc322
hash equality: PASS
file SHA equality: false, expected because output path/execution metadata are non-hashed

default_thresholds_resolved=true
min_trades=120
min_days_covered effective=ceil(70% * replay days)
zero-volume entry => BT_SKIP_NO_TRADABLE_ENTRY, ret_net=null
zero-volume final exit => BT_SKIP_NO_TRADABLE_EXIT, ret_net=null
normal positive-volume trade => evaluated
```

### Dynamic coverage correction after first closure rerun

The first operator PHPUnit rerun correctly failed the sentinel test because the implementation used the same requested replay-date count as both observed `days_covered` and total-window denominator. That made `minimum_coverage` always true whenever the explicit window existed.

Corrected runtime semantics:

```text
total_trading_days_in_window = count(explicit replay trading dates)
days_covered = distinct replay dates with >= 1 metrics_ready trade
             + explicit WATCHLIST_BACKTEST_EMPTY_RECOMMENDATION_VALID dates
all candidates skipped on a date = not covered
```

Controlled proof after the correction:

```text
1 covered day / 10 requested days => effective floor 7 => minimum_coverage=false
7 covered days / 10 requested days => effective floor 7 => minimum_coverage=true
valid empty-recommendation day => counted once as covered
coverage pass still does not bypass min_trades/return/stability gates
```

The corrected service and regression tests require operator PHPUnit and two-run command rerun before the current patch can be promoted.

### Current boundary

The operator runtime evidence above proves the pre-closure implementation. Because execution semantics and hashed paramset metadata changed afterward, the current closure patch must be rerun locally. Its artifact hash is expected to differ from `03dce5...`, but two identical closure-patch runs must still match each other.

Production readiness remains `NOT_PRODUCTION_READY`. No contract is promoted to `LOCKED`; walk-forward/OOS and production operating proof remain outstanding.

## Published Price Runtime Proof Final Closure Update — 2026-06-09

Session:
`WATCHLIST — BACKTEST PUBLISHED PRICE SERIES RUNTIME PROOF EXECUTION SESSION`

Final status:
`DONE for published price series runtime proof scope / LOCAL_RUNTIME_PROOF_PASS / NOT_PRODUCTION_READY`.

### Final operator PHPUnit evidence

```text
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestPublishedPrice"
OK (17 tests, 146 assertions)

vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestMetricsServiceTest"
OK (8 tests, 63 assertions)

vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktest"
OK (48 tests, 497 assertions)

vendor\bin\phpunit tests\Unit\Watchlist
OK (139 tests, 1379 assertions)

vendor\bin\phpunit tests\Unit\MarketData --filter "MarketDataPublishedEodSeries"
OK (6 tests, 29 assertions)

vendor\bin\phpunit tests\Unit\MarketData --filter "MarketDataTradingCalendar"
OK (4 tests, 16 assertions)

vendor\bin\phpunit tests\Unit\MarketData --filter "MarketDataWatchlistReadModelTest"
OK (3 tests, 41 assertions)
```

### Final official command proof

```text
command: watchlist:backtest-published-price-proof
replay_from: 2026-05-21
replay_to: 2026-05-29
run_count: 2
status: PASS for both runs
replay_date_count: 5
calendar_date_count: 10
required_price_date_count: 9
resolved_price_date_count: 9
evaluated_trade_count: 13
diagnostic_count: 2
metric_required_fields_available: 1
metric_thresholds_resolved: 1
metric_min_trades: 120
metric_min_days_covered: 4
metric_coverage_threshold_rule: CEIL_70_PERCENT_OF_TOTAL_TRADING_DAYS
days_covered: 5
total_trading_days_in_window: 5
minimum_coverage: true
metric_calibration_valid: 0
canonical_artifact_hash_run_1: 0eaa353d20df901c4f372c0000951408578bf302
canonical_artifact_hash_run_2: 0eaa353d20df901c4f372c0000951408578bf302
canonical_hash_equality: true
production_ready: 0
```

`metric_calibration_valid=0` is expected and correct for this smoke window because 13 evaluated trades do not meet `min_trades=120`. Threshold resolution, coverage calculation, runtime orchestration, and deterministic artifact generation passed.

### Final zero-volume diagnostics

- KING (`trade_date=2026-05-21`) emitted `BT_SKIP_MISSING_OHLC_EXIT`; zero-volume dates `2026-05-25`, `2026-05-26`, and `2026-05-29` were recorded in `ignored_non_tradable_exit_dates`, no synthetic exit was created, and no zero return was fabricated.
- BKDP (`trade_date=2026-05-26`) emitted `BT_SKIP_NO_TRADABLE_ENTRY` with `entry_volume=0`; the trade was never treated as entered.

### Final scope conclusion

- Official trading-calendar runtime read: PASS.
- Exact-date current-readable published EOD OHLCV runtime read: PASS.
- Publication lineage: PASS for 10/10 required dates through `2026-06-08`.
- Immutable strategy output before future-price evaluation: PASS.
- Zero-volume non-tradable handling: PASS.
- Metric threshold binding and dynamic coverage: PASS.
- Deterministic two-run canonical hash: PASS.
- JSON runtime evidence export: PASS.
- Portfolio/execution leakage: none introduced.
- Walk-forward/OOS proof: not started.
- Production operating proof: not available.
- Overall watchlist status remains `NOT_PRODUCTION_READY`.

Earlier references in this document to a required closure/coverage rerun are historical and are superseded by this final closure update.

Next session:
`WATCHLIST — WALK-FORWARD AND OUT-OF-SAMPLE PROOF EXECUTION SESSION`.


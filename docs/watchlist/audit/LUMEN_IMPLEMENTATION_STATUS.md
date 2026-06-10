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
`WATCHLIST — WALK-FORWARD AND OUT-OF-SAMPLE PROOF EXECUTION SESSION`

Status:
`DONE for OOS execution infrastructure / EXECUTION_PRICE_CORRECTION_VALIDATED / FULL_IS_CALIBRATION_EXECUTED / R1_GRID_FAILED_IS_QUALITY / OOS_NOT_EXECUTED / NOT_PRODUCTION_READY`.

Historical baselines remain preserved and are not downgraded:

- `PHASE_6_CONFIRM_OVERLAY_FOUNDATION_DONE / NOT_PRODUCTION_READY`;
- `PHASE_7_BACKTEST_STRATEGY_ENGINE_FOUNDATION_DONE / NOT_PRODUCTION_READY`;
- `DONE for published price series runtime proof scope / LOCAL_RUNTIME_PROOF_PASS / NOT_PRODUCTION_READY`;
- final published-price operator evidence remains PublishedPrice `17/146`, MetricsService `8/63`, Backtest `48/497`, full Watchlist `139/1379`, PublishedEodSeries `6/29`, TradingCalendar `4/16`, and MarketDataWatchlistReadModel `3/41`.

Scope:
Implement the minimum canonical chronological 70/30 Weekly Swing OOS proof over explicit historical windows. The implementation reads the official trading calendar and published EOD series through existing application surfaces, calibrates every official database param-grid row only on the IS prefix, freezes the best valid IS binding, evaluates the OOS suffix without re-tuning, persists official evaluation rows idempotently, and exports deterministic evidence. It does not add portfolio allocation, order, broker instruction, scheduler, API endpoint, paramset promotion, or production-trading state.

Current evidence:

- canonical 24-row grid is deployed and idempotent: `catalog_count=24`, `param_grid_count=24`;
- supported operator PHPUnit is green after execution-price correction: ParamGrid `4/636`, MetricsService `15/113`, PublishedPrice `18/177`, OOS `24/186`, Backtest `87/1430`, and full Watchlist `179/2318`;
- full explicit historical proof executed with `from=2023-01-02` and `to=2026-05-29`; split is deterministic with IS `2023-01-02..2025-05-21` / `562` trading dates and OOS `2025-05-22..2026-05-29` / `242` trading dates;
- all 24 canonical rows completed IS evaluation without memory exhaustion, source-readiness failure, raw-reader fallback, or per-evaluation runtime/source diagnostics;
- `is_valid_param_count=0`, `is_failed_param_count=24`, `is_max_picks_count=1445`, and `is_max_days_covered=513`;
- the only IS failure classes are `WS_BT_EVAL_ROBUST_RETURN_FAIL`, `WS_BT_EVAL_DOWNSIDE_FAIL`, and `WS_BT_EVAL_STABILITY_FAIL`; no best-of-failed binding was created;
- param 9 is the only evaluated row with positive average return among the nearest rows but still fails median, downside, and both stability gates; param 24 passes downside only and fails average, median, and both stability gates;
- execution-price correction is validated: opening gaps fill at open, intraday trigger prices use deterministic IDX price-band normalization, trigger and executed prices are distinct, and non-executable fractional source OHLC fails closed;
- the corrected canonical artifact hash is `f4ec8464f08515b31d7d26636851acea930307d6`; operator evidence path is `storage/app/watchlist/backtest/oos-proof-run-1.json`;
- global diagnostic is `WS_BT_OOS_PROOF_MISSING` because no valid IS binding exists; per-evaluation diagnostics are empty;
- OOS did not execute, no `watchlist_bt_oos_eval_ws` proof was created for this run, no promotion occurred, and promotion eligibility remains `NOT_ELIGIBLE — OOS proof missing`;
- R1 is preserved as failed IS research evidence. The next justified work is a separate R2 entry-quality calibration session using IS only; acceptance gates and the OOS suffix remain unchanged and unread for R2 design.

## Source of Truth ZIP

- Source ZIP: `tradeaxis-api.zip`
- Session date: `2026-06-10`
- Latest local validation date: `2026-06-10`
- Scope classification: supported-runtime chronological 70/30 proof infrastructure and full IS calibration completed; R1 has no valid IS parameter, so OOS did not execute.

## Current Implementation Baseline

| Area | Status | Notes |
|---|---|---|
| Current status | `FULL_IS_CALIBRATION_EXECUTED / R1_GRID_FAILED_IS_QUALITY / OOS_NOT_EXECUTED / NOT_PRODUCTION_READY` | OOS infrastructure and corrected execution semantics are validated, but no canonical R1 parameter passed all IS gates. |
| Main feature code | `DONE for OOS execution infrastructure scope` | Deterministic split, official grid, IS-only calibration, immutable binding boundary, OOS no-retune service, official persistence, and artifact export exist. |
| Runtime API | `NOT_STARTED` | No API endpoint created, by scope. |
| Artisan command surface | `IMPLEMENTED / SUPPORTED-RUNTIME EXECUTED` | `watchlist:backtest-oos-proof` executed over the full explicit range and correctly stopped before OOS because no valid IS binding existed. |
| Database schema | `DEPLOYED / VERIFIED FOR CURRENT OOS SCOPE` | Backtest schema migrations and canonical 24-row grid seed executed successfully. |
| Backtest engine | `DONE for corrected IS runtime scope / OOS NOT EXECUTED` | Full IS replay completed under gap-aware, price-band-normalized execution semantics; no best-of-failed selection occurred. |
| Recommendation engine | `DONE for Phase 5 unit/static scope` | Recommendation remains derived only from PLAN; calibration/OOS does not mutate recommendation membership. |
| PLAN grouping engine | `DONE for Phase 4 scope + deterministic BT quantile support` | Official grid quantiles are deterministic and runtime-tested. |
| Scoring engine | `DONE / R1 QUALITY INSUFFICIENT` | Scoring execution is deterministic, but the R1 grid did not produce a parameter satisfying canonical IS quality gates. |
| Market-data consumer read model | `DONE for published-price runtime scope` | Full-range replay used official calendar/publication/OHLCV surfaces without raw/latest/MAX-date bypass. |
| Candidate universe / liquidity-risk gates | `DONE for current runtime scope` | All 24 rows pass cross-field paramset validation and reach IS metric evaluation. |
| Test coverage | `LOCAL_PHPUNIT_PASS` | ParamGrid `4/636`, MetricsService `15/113`, PublishedPrice `18/177`, OOS `24/186`, Backtest `87/1430`, full Watchlist `179/2318`. |
| Artifact/log output | `IS FAILURE EVIDENCE AVAILABLE / OOS ARTIFACT ABSENT` | Corrected IS artifact hash `f4ec8464f08515b31d7d26636851acea930307d6`; no OOS evidence exists because no valid IS binding exists. |
| Production readiness | `NOT_READY` | R1 quality failed, OOS proof is missing, promotion is not eligible, and production operating proof remains absent. |

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
| `EXTERNAL_DEPENDENCY` | Sandbox runs PHP `8.4.16`; project Artisan guard requires PHP `< 8.4`. | Official OOS command/database proof cannot bootstrap here. |
| `EXTERNAL_DEPENDENCY` | PHP extensions `dom`, `mbstring`, `xml`, and `xmlwriter` are missing. | PHPUnit exits before test discovery; no current-patch PHPUnit PASS can be claimed. |
| `RUNTIME_PROOF_MISSING` | Official tables and populated `watchlist_bt_param_grid` were not reachable in a supported runtime. | DB-backed IS/OOS persistence ids, official lineage, OOS metrics, and acceptance remain unproven. |
| `RUNTIME_PROOF_MISSING` | Two identical supported-environment OOS command runs do not yet exist. | Canonical artifact hash equality is proven only by controlled smoke, not official operator execution. |
| `NOT_READY` | OOS acceptance and production operating proof are missing. | No paramset promotion review eligibility and no production-ready claim. |

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

- historical Phase 6, Phase 7, runtime artifact, metrics, and published-price runtime baselines remain valid and are not downgraded;
- canonical schema, 24-row grid, execution-price semantics, PHPUnit, and full-range IS runtime are validated;
- all 24 R1 parameters failed one or more canonical IS quality gates;
- no valid `param_id_best_is` or immutable best-IS binding exists;
- OOS did not execute and no OOS acceptance artifact exists;
- no promotion was executed and promotion eligibility is `NOT_ELIGIBLE — OOS proof missing`;
- no portfolio, broker, scheduler, API, or production-execution scope was added;
- no contract is `LOCKED` and `WL-CONTRACT-015` remains `PARTIAL / NOT_READY`.

Watchlist is not production-ready.

## Next Required Sessions

Required next session:

`WATCHLIST — WEEKLY SWING R2 ENTRY-QUALITY CALIBRATION SESSION`

Target:

- preserve R1 artifact and matrix as immutable failed-IS evidence;
- create a new deterministic catalog/version rather than editing R1 rows in place;
- use IS data only; the OOS suffix must not be read or used to design R2;
- focus on entry-quality variables such as momentum threshold, breakout proximity/extension, volume confirmation, ATR selection band, liquidity threshold, scoring weights, and TOP_PICKS quantile;
- keep canonical metric sufficiency and acceptance gates unchanged;
- avoid unbounded/manual trial loops and keep the search space documented and finite;
- require all R2 rows to pass the same cross-field, execution-price, determinism, and persistence guards;
- select no best-of-failed parameter; OOS may start only if at least one R2 row passes every canonical IS gate;
- keep promotion, portfolio, broker, scheduler, API, and production-ready claims out of scope.

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

## Walk-Forward/OOS Implementation Unit-Static Update — 2026-06-09

Session:
`WATCHLIST — WALK-FORWARD AND OUT-OF-SAMPLE PROOF EXECUTION SESSION`

Session status:
`DONE for walk-forward/OOS implementation unit-static scope / LOCAL_SMOKE_PASS / OFFICIAL_RUNTIME_PROOF_BLOCKED / NOT_PRODUCTION_READY`.

### TRACE result

```text
official trading calendar
→ official current-readable published EOD series
→ WatchlistBacktestPublishedPriceRuntimeService
→ WatchlistBacktestStrategyService
→ WatchlistBacktestMetricsService
→ official watchlist_bt_param_grid
→ IS-only calibration and watchlist_bt_eval
→ deterministic 70/30 split
→ immutable best-IS binding
→ OOS one-param evaluation without re-tuning
→ watchlist_bt_oos_eval_ws
→ deterministic JSON evidence
```

No raw market-data reader, latest/`MAX(trade_date)` shortcut, hidden current-date default, PLAN/RECOMMENDATION/CONFIRM mutation, paramset status mutation, portfolio allocation, order, broker, scheduler, or API endpoint was introduced.

### Contract drift closed

- file 17 now locks `is_count=floor(0.70*N)` and assigns the remainder to OOS;
- file 16 and file 12 now end canonical ranking with `param_id ASC`;
- file 20 and `PROMOTE_PARAMSET.sql` now require the canonical minimum OOS count, default `40`, rather than `picks_count_oos > 0`;
- the OOS fixture now uses only owner acceptance gates;
- OOS DDL now records `is_eval_id` with a foreign key to `watchlist_bt_eval`.

### Implementation evidence

Created:

- `WatchlistBacktestOosSplitService`;
- `WatchlistBacktestIsCalibrationService`;
- `WatchlistBacktestOosProofService`;
- official param-grid, IS-evaluation, and OOS-evaluation repositories;
- `RunBacktestOosProofCommand` and Kernel registration;
- seven OOS/quantile PHPUnit test files.

Updated:

- published-price runtime with an internal explicit-window evaluation surface;
- strategy paramset propagation and canonical eval model;
- PLAN grouping with deterministic daily quantile cutoffs for official BT-grid fields;
- owner contracts, DDL, promotion SQL, fixture, and audit trackers.

### Validation evidence

```text
PHP lint: PASS for every changed/new PHP file
controlled OOS smoke: PASS / 35 assertions
controlled grouping quantile smoke: PASS / 6 assertions
new OOS PHPUnit source: 20 test methods / 118 assertion-expectation call sites
official Artisan attempt 1: exit 2 / unsupported PHP 8.4.16 / no artifact
official Artisan attempt 2: exit 2 / unsupported PHP 8.4.16 / no artifact
requested PHPUnit scopes: exit 1 before discovery / missing dom, mbstring, xml, xmlwriter
```

The controlled smoke proves split odd/even behavior, canonical ranking/tie-break, immutable binding, no OOS selection leakage, OOS gates, missing-metric fail-closed behavior, exact-duplicate idempotency, conflicting-duplicate rejection, no promotion mutation, and canonical hash equality across INSERTED/IDEMPOTENT persistence status. It does not replace supported-environment PHPUnit or official DB-backed command evidence.

### Runtime and promotion conclusion

```text
Official OOS runtime evidence: BLOCKED
LOCAL_OOS_PROOF_PASS: NOT CLAIMED
OOS_ACCEPTANCE_FAIL: NOT CLAIMED (OOS did not execute)
Promotion eligibility: NOT_ELIGIBLE — OOS proof missing
Production ready: false
```

No contract is promoted to `LOCKED`.


## OOS Supported-Runtime Finding and Gap-Closure Implementation Update — 2026-06-09

Session:
`WATCHLIST — WALK-FORWARD AND OUT-OF-SAMPLE PROOF EXECUTION SESSION`

Current status:
`DONE for OOS runtime gap-closure implementation unit/static scope / OPERATOR_RERUN_REQUIRED / NOT_PRODUCTION_READY`.

### Operator evidence received before this closure patch

```text
watchlist:backtest-oos-proof command registration: PASS
WatchlistBacktestOos: 19 tests / 117 assertions / PASS
WatchlistBacktestIsCalibration: 3 tests / 17 assertions / PASS
WatchlistPlanGroupingQuantileCutoff: 1 test / 6 assertions / PASS
WatchlistBacktest: 70 tests / 631 assertions / PASS
Full Watchlist: 162 tests / 1519 assertions / PASS
MarketDataPublishedEodSeries: 6 tests / 29 assertions / PASS
MarketDataTradingCalendar: 4 tests / 16 assertions / PASS
MarketDataWatchlistReadModelTest: 3 tests / 41 assertions / PASS
```

The first long-range attempt (`2023-01-02` through `2026-05-29`) exhausted the 512 MB PHP memory limit while materializing published prices. A shorter supported-runtime attempt (`2025-01-02` through `2026-05-29`) completed the chronological split and IS calibration:

```text
IS: 2025-01-02 through 2025-12-17 / 229 trading dates
OOS: 2025-12-18 through 2026-05-29 / 99 trading dates
param_grid_count: 1
is_valid_param_count: 0
picks_count: 629
IS coverage: PASS
average return: PASS
median return: FAIL
p25 downside: FAIL
monthly win-rate floor: FAIL
monthly average floor: FAIL
OOS: not started because no valid IS binding existed
```

The existing one-row baseline was correctly rejected. No best-of-failed selection and no OOS retuning occurred.

### Confirmed implementation gaps closed by this patch

- Added a deterministic, curated, 24-row canonical WS parameter catalog and idempotent database seed command/seeder/SQL.
- Added official grid columns for `stop_atr_mult` and `min_rr`; both are bound into the runtime paramset.
- Propagated `atr14_pct` and level inputs through recommendation/backtest candidates.
- Added canonical ATR/RR stop/target fallback when PLAN has no explicit levels.
- Replaced date/ticker cartesian published-price loading with exact frozen candidate date/ticker pair reads.
- Removed per-grid temporary JSON writes during IS calibration and released iteration memory.
- Corrected runtime metadata to `PUBLISHED_EOD_OHLCV_CURRENT_READABLE_EXACT_DATE` / `TARGETED_DATE_TICKER_MAP`.
- Added compact deterministic worst/best trade evidence with entry/exit and publication lineage to each IS evaluation reference.
- Versioned `watchlist_bt_eval` identity with `eval_model` and `paramset_hash`, and OOS identity with `is_eval_id`, so the earlier `eval_id=1` evidence can remain while corrected semantics are rerun; no deletion or overwrite is required.
- Added migrations and synchronized SQL for fresh and existing databases.

### Current validation boundary

The code and documentation patch has local syntax/static validation in the packaging environment. Official PHPUnit and database-backed rerun for this closure patch must be executed by the operator under the supported project PHP/runtime. Historical operator PASS evidence above is preserved but does not prove the new closure patch.

Required next operator sequence:

```text
migrate schema
seed canonical grid
run OOS/backtest/full-Watchlist/MarketData PHPUnit
run one explicit OOS proof
if best IS exists and OOS executes, run the same proof a second time
compare canonical hashes and persistence ids/status
```

Current conclusion:

```text
LOCAL_OOS_PROOF_PASS: NOT CLAIMED
OOS_ACCEPTANCE_FAIL: NOT CLAIMED for the corrected multi-grid runtime
Promotion eligibility: NOT_ELIGIBLE — corrected OOS proof missing
Production ready: false
```

Watchlist is not production-ready.

## OOS Post-Deployment Regression Root-Cause Correction — 2026-06-10

Supported operator deployment confirmed the canonical grid seed itself was healthy:

```text
catalog_count=24
inserted_count=0
updated_count=0
existing_count=24
param_grid_count=24
WatchlistBacktestParamGrid: 2 tests / 535 assertions / PASS
```

The subsequent suites exposed three source-level regressions in the uploaded source of truth:

1. `WatchlistBacktestOosStaticGuardTest` duplicated the obsolete literal `18` even though the catalog and SQL seed contain 24 rows.
2. `WatchlistBacktestStrategyService::DEFAULT_PARAMSET` had no nested risk defaults, so a standalone strategy replay emitted `stop_atr_mult=null` and `min_rr=null`.
3. `WatchlistBacktestPublishedPriceRuntimeService` passed runtime metadata into strategy replay but trusted the returned payload to echo it. Test doubles and legacy payloads could therefore omit `pricing_model` / `price_read_mode`, causing artifact drift and an undefined index.

Root corrections:

- added `WatchlistBacktestParamGridCatalog::CATALOG_COUNT=24` and made catalog/SQL static guards derive from it;
- added exact persisted-catalog validation with fail-closed code `WS_BT_PARAM_GRID_PERSISTED_SET_MISMATCH`;
- added canonical strategy defaults `risk.stop_atr_mult=1.5` and `risk.min_rr=1.5`, with nested risk resolution;
- bound published-price runtime metadata onto the returned strategy payload before the frozen strategy hash and before price reads;
- synchronized top-level/meta paramset snapshots and trade runtime metadata without fabricating missing eval thresholds;
- synchronized owner contract, implementation flow, test guidance, and audit trackers.

Packaging-environment validation:

```text
PHP lint: PASS for all changed PHP files
controlled root-cause smoke: 15 assertions / PASS
official PHPUnit: not executable in packaging environment because dom, mbstring, xml, xmlwriter are unavailable
```

Operator rerun is required. No `LOCAL_OOS_PROOF_PASS`, promotion eligibility, or production-ready claim is made.


## OOS Full-Window Operator Result and Grid Cross-Field Closure — 2026-06-10

Supported operator validation after the post-deployment correction:

```text
WatchlistBacktestParamGrid: 2 tests / 535 assertions / PASS
WatchlistBacktestOos: 24 tests / 174 assertions / PASS
WatchlistBacktestStrategy: 15 tests / 191 assertions / PASS
WatchlistBacktestPublishedPrice: 18 tests / 155 assertions / PASS
WatchlistBacktest: 79 tests / 1252 assertions / PASS
Full Watchlist: 171 tests / 2140 assertions / PASS
```

The full explicit OOS command then executed without memory exhaustion:

```text
from=2023-01-02
split IS=2023-01-02..2025-05-21 / 562 trading dates
split OOS=2025-05-22..2026-05-29 / 242 trading dates
param_grid_count=24
is_valid_param_count=0
is_failed_param_count=24
is_max_picks_count=1513
is_max_days_covered=513
reason_code=WS_BT_OOS_PROOF_MISSING
```

Static source analysis identified a technical failure mixed into the honest statistical failures: 19 strict catalog rows have `max_atr14_pct < 0.075`, while the previous row-to-paramset merge retained active `atr_ideal_high=0.075`. Candidate/scoring validation therefore rejected those rows as internally contradictory before daily replay, surfacing aggregate `WATCHLIST_BACKTEST_SOURCE_NOT_READY`.

Root correction:

- introduced `WatchlistBacktestParamGridParamsetFactory` as the single row-to-runtime-paramset boundary;
- locked deterministic companion-band projection `CLAMP_DEFAULT_IDEAL_ATR_BAND_TO_GRID_MAX_ATR`;
- records resolved values in `bt_grid_resolution`;
- guarantees `min_atr14_pct <= atr_ideal_low <= atr_ideal_high <= max_atr14_pct` for all 24 rows;
- added catalog-wide PHPUnit/static guards.

This correction removes a technical false rejection. It does not weaken metric gates and does not guarantee that any parameter will pass IS. The operator must rerun the same full command. If all 24 rows execute but still fail robust-return/downside/stability gates, that result is an honest strategy-calibration failure, not a runtime defect.

Current status:

```text
UNIT/REGRESSION BASELINE: PASS
FULL-WINDOW TECHNICAL EXECUTION: PASS
GRID CROSS-FIELD CORRECTION: IMPLEMENTED / OPERATOR RERUN REQUIRED
LOCAL_OOS_PROOF_PASS: NOT CLAIMED
PROMOTION: NOT_ELIGIBLE
NOT_PRODUCTION_READY
```

## Execution-Price Corrected Full-Range R1 IS Final Result — 2026-06-10

Session:
`WATCHLIST — WALK-FORWARD AND OUT-OF-SAMPLE PROOF EXECUTION SESSION`

Final session status:
`DONE for OOS execution infrastructure / EXECUTION_PRICE_CORRECTION_VALIDATED / FULL_IS_CALIBRATION_EXECUTED / R1_GRID_FAILED_IS_QUALITY / OOS_NOT_EXECUTED / NOT_PRODUCTION_READY`.

### Final operator validation

```text
WatchlistBacktestParamGrid: 4 tests / 636 assertions / PASS
WatchlistBacktestMetricsServiceTest: 15 tests / 113 assertions / PASS
WatchlistBacktestPublishedPrice: 18 tests / 177 assertions / PASS
WatchlistBacktestOos: 24 tests / 186 assertions / PASS
WatchlistBacktest: 87 tests / 1430 assertions / PASS
Full Watchlist: 179 tests / 2318 assertions / PASS
```

### Final supported-runtime evidence

```text
requested_from=2023-01-02
requested_to=2026-05-29
split_rule=FLOOR_70_PERCENT_IS_REMAINDER_OOS
is_from=2023-01-02
is_to=2025-05-21
is_trading_date_count=562
oos_from=2025-05-22
oos_to=2026-05-29
oos_trading_date_count=242
param_grid_count=24
is_valid_param_count=0
is_failed_param_count=24
is_max_picks_count=1445
is_max_days_covered=513
is_failure_reason_codes=WS_BT_EVAL_DOWNSIDE_FAIL,WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_STABILITY_FAIL
artifact_hash=f4ec8464f08515b31d7d26636851acea930307d6
production_ready=0
exit_code=1
```

The run completed the full IS evaluation. Exit code `1` is the correct fail-closed result for `WS_BT_OOS_PROOF_MISSING` when no parameter passes every IS gate. It is not a runtime or database defect.

### Diagnostics and quality conclusion

- no per-evaluation runtime/source diagnostics were emitted;
- no `WATCHLIST_BACKTEST_SOURCE_NOT_READY`, price-read, OHLC, tradability, or execution failure remained;
- all failures are canonical return/downside/stability gate failures;
- param 9 passes average return only and fails median, downside, and stability;
- param 24 passes downside only and fails average, median, and stability;
- R1 therefore has no eligible best-IS binding and OOS correctly remains unread/unexecuted.

### Evidence retained

- canonical runtime artifact: `storage/app/watchlist/backtest/oos-proof-run-1.json`;
- frozen copy: `storage/app/watchlist/backtest/oos-proof-execution-price-corrected-is-failed.json`;
- IS matrix: `storage/app/watchlist/backtest/oos-is-evaluation-matrix-execution-corrected.csv`;
- canonical artifact hash: `f4ec8464f08515b31d7d26636851acea930307d6`.

### Final boundary

```text
R1_EXECUTION_VALIDATED
R1_GRID_FAILED_IS_QUALITY
NO_VALID_IS_PARAM
OOS_NOT_EXECUTED
NOT_ELIGIBLE_FOR_PROMOTION
NOT_PRODUCTION_READY
```

No owner acceptance rule was weakened, no OOS data was used for selection, no best-of-failed parameter was created, and no contract is promoted to `LOCKED`.

Next session:
`WATCHLIST — WEEKLY SWING R2 ENTRY-QUALITY CALIBRATION SESSION`.

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
`WATCHLIST — PLAN GROUPING + TOP_PICKS / SECONDARY SELECTION EXECUTION SESSION`

Status:
`DONE for Phase 4 unit/static scope` for PLAN grouping foundation. Local PHP lint and PHPUnit validation passed in this session. Readiness-critical contracts remain `PARTIAL` because no watchlist command/API runtime proof or artifact/log output exists yet.

Scope:
Create deterministic PLAN grouping foundation on top of Phase 3 scoring output. This session consumes `WatchlistScoringService`, maps scored items into PLAN groups `TOP_PICKS`, `SECONDARY`, `WATCH_ONLY`, and diagnostics `AVOID`, records grouping thresholds/limits, keeps paramset/code and source scoring traceability, and explicitly does not create final recommendation, confirm overlay, backtest, API, command, scheduler, portfolio, or execution logic.

Evidence:

- `app/Application/Watchlist/Services/WatchlistPlanGroupingService.php` created.
- `tests/Unit/Watchlist/WatchlistPlanGroupingServiceTest.php` added.
- `tests/Unit/Watchlist/WatchlistPlanGroupingStaticGuardTest.php` added.
- `WatchlistPlanGroupingService` consumes `WatchlistScoringService` and does not consume candidate-universe or market-data read services directly.
- Bootstrap labels normalized to `WS_EOD_RUNTIME` and `WS_ACTIVE_BOOTSTRAP`; no `_V1` suffix is used for watchlist runtime/bootstrap labels because the application does not have formal app/runtime versioning yet.
- PLAN group reason codes `WS_PLAN_TOP_PICK`, `WS_PLAN_SECONDARY`, `WS_PLAN_WATCH_ONLY`, `WS_PLAN_AVOID_LOW_SCORE`, and `WS_PLAN_AVOID_EXCLUDED` added to reason-code docs/support seed.
- Existing no raw/latest/`MAX(trade_date)` boundary remains enforced by static guards.

## Source of Truth ZIP

- Source ZIP: `tradeaxis-api.zip`
- Session date: `2026-06-05`
- Scope classification: Phase 4 watchlist PLAN grouping foundation code + tests + docs sync.

## Current Implementation Baseline

| Area | Status | Notes |
|---|---|---|
| Current status | `PHASE_4_PLAN_GROUPING_FOUNDATION_DONE / NOT_PRODUCTION_READY` | Read model, candidate universe gates, deterministic scoring foundation, and deterministic PLAN grouping foundation exist at unit/static scope. Whole watchlist remains not production-ready. |
| Main feature code | `PARTIAL` | Read model, candidate universe gates, scoring foundation, and PLAN grouping foundation exist. Final recommendation/backtest/runtime code is still not started. |
| Runtime API | `NOT_STARTED` | No API endpoint created. |
| Artisan command surface | `NOT_STARTED` | No watchlist command created. |
| Database schema | `NOT_STARTED` | No production migration created. Existing SQL docs/fixtures are support artifacts only. |
| Backtest engine | `NOT_STARTED` | No runtime backtest engine created. |
| Recommendation engine | `NOT_STARTED` | No final recommendation engine created. PLAN grouping is not final recommendation. |
| PLAN grouping engine | `DONE for Phase 4 unit/static scope` | `WatchlistPlanGroupingService` maps Phase 3 scored output into deterministic PLAN groups only. |
| Scoring engine | `DONE / LOCAL PASS` | `WatchlistScoringService` computes deterministic PLAN component scores and ranking over Phase 2 eligible universe rows; baseline validation at the start of this session is local PASS. |
| Market-data consumer read model | `DONE` | `WatchlistMarketDataConsumerReadService` consumes the official market-data read surface and validates candidate readiness. |
| Candidate universe / liquidity-risk gates | `DONE` | `WatchlistCandidateUniverseService` applies deterministic liquidity, ATR/risk, and volume participation guards over Phase 1 candidates. |
| Test coverage | `PARTIAL` | Read model, candidate universe, scoring, and PLAN grouping unit/static tests exist. Full watchlist runtime proof is not available yet. |
| Artifact/log output | `NOT_STARTED` | No runtime artifact generator exists yet. |
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

## Active Gaps

| Severity | Gap | Impact |
|---|---|---|
| `RUNTIME_PROOF_MISSING` | No watchlist command/API runtime proof exists for read model, candidate universe, scoring, or PLAN grouping. | WL-CONTRACT-001 through WL-CONTRACT-008, WL-CONTRACT-011, WL-CONTRACT-014, WL-CONTRACT-016, and WL-CONTRACT-017 remain not `LOCKED`. |
| `HIGH_RISK` | Final recommendation layer is not implemented yet. | PLAN grouping now produces `TOP_PICKS`, `SECONDARY`, `WATCH_ONLY`, and `AVOID`, but these are not final recommendation membership. |
| `RUNTIME_PROOF_MISSING` | No watchlist artifact/log output exists. | Cannot claim audit artifact contract or production readiness. |
| `DOCS_ONLY` | Governance/read model/candidate universe/scoring/PLAN grouping docs are synced, but API/command/backtest/runtime artifact docs/code still need implementation sync. | Correct for current scope; not production readiness. |
| `NOT_READY` | No runtime API, command, production schema, final recommendation engine, backtest engine, or portfolio-aware boundary implementation exists. | Watchlist Production Ready remains `NO`. |

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

### Phase 5 — Backtest Strategy Engine

- No lookahead.
- Publication-aware replay.
- Entry/exit rules.
- Metrics.
- Artifact output.
- Add tests.

Status: `NOT_STARTED`.

### Phase 6 — Portfolio-Aware Integration

- Current holding awareness.
- Position sizing guidance.
- Risk exposure.
- No execution automation unless explicitly designed.

Status: `NOT_STARTED`.

### Phase 7 — API/Command Surface

- Artisan commands.
- API endpoints.
- Output contract.
- Evidence artifacts.

Status: `NOT_STARTED`.

### Phase 8 — Production Readiness Audit

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

## Validation Log

Validation performed in this session:

```text
php -l app\Application\Watchlist\Services\WatchlistPlanGroupingService.php
php -l tests\Unit\Watchlist\WatchlistPlanGroupingServiceTest.php
php -l tests\Unit\Watchlist\WatchlistPlanGroupingStaticGuardTest.php
php -l tests\Unit\Watchlist\WatchlistAuditGovernanceStaticGuardTest.php
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistPlanGrouping"
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistScoring"
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistCandidateUniverse"
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistMarketDataConsumerRead"
vendor\bin\phpunit tests\Unit\MarketData --filter "MarketDataWatchlistReadModelTest"
vendor\bin\phpunit tests\Unit\Watchlist
```

Observed validation result:

```text
php -l: PASS for all touched PHP files
WatchlistPlanGrouping: PASS — 19 tests, 170 assertions
WatchlistScoring: PASS — 14 tests, 149 assertions
WatchlistCandidateUniverse: PASS — 9 tests, 97 assertions
WatchlistMarketDataConsumerRead: PASS — 8 tests, 80 assertions
MarketDataWatchlistReadModelTest: PASS — 3 tests, 41 assertions
tests\Unit\Watchlist: PASS — 55 tests, 542 assertions
```

## Manual Validation Requirements

Required local checks:

```powershell
php -l app\Application\Watchlist\Services\WatchlistPlanGroupingService.php
php -l tests\Unit\Watchlist\WatchlistPlanGroupingServiceTest.php
php -l tests\Unit\Watchlist\WatchlistPlanGroupingStaticGuardTest.php
php -l tests\Unit\Watchlist\WatchlistAuditGovernanceStaticGuardTest.php
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistPlanGrouping"
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistScoring"
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistCandidateUniverse"
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistMarketDataConsumerRead"
vendor\bin\phpunit tests\Unit\MarketData --filter "MarketDataWatchlistReadModelTest"
vendor\bin\phpunit tests\Unit\Watchlist
```

Pass criteria:

- PLAN grouping service consumes `WatchlistScoringService` only;
- PLAN grouping output contains deterministic `TOP_PICKS`, `SECONDARY`, `WATCH_ONLY`, and diagnostics `AVOID`;
- duplicate `ticker_id` is deduplicated deterministically;
- scoring excluded candidates do not enter active PLAN groups;
- watchlist application code does not contain raw DB reads, raw market-data table names, latest shortcuts, or `MAX(trade_date)` patterns;
- PLAN grouping output does not contain final recommendation, confirm, portfolio, execution, order, or backtest fields;
- implementation status and contract tracker active sessions remain aligned.

## Production Readiness Status

`NOT_READY`.

Reason:

- market-data consumer read model exists but has no watchlist command/API runtime proof yet;
- candidate universe, scoring foundation, and PLAN grouping foundation exist only at service + unit/static scope;
- no runtime artifact/log output exists yet;
- no runtime API;
- no command surface;
- no production watchlist schema/migration;
- no final recommendation engine;
- no backtest engine;
- no portfolio-aware integration;
- WL-CONTRACT-001 through WL-CONTRACT-008, WL-CONTRACT-011, WL-CONTRACT-014, WL-CONTRACT-016, and WL-CONTRACT-017 are not `LOCKED` because runtime proof/artifact evidence is missing;
- WL-CONTRACT-009, WL-CONTRACT-010, WL-CONTRACT-012, and WL-CONTRACT-013 remain `NOT_STARTED`.

## Next Required Sessions

Recommended next session:

`WATCHLIST — FINAL RECOMMENDATION LAYER FOUNDATION EXECUTION SESSION`

Target:

- consume PLAN grouped output from `WatchlistPlanGroupingService`;
- derive final recommendation only from PLAN;
- preserve that recommendation can exist without confirm;
- preserve that recommendation can be empty even when `TOP_PICKS` and/or `SECONDARY` exists;
- do not create confirm overlay mutation, portfolio/capital allocation, execution, API/command runtime, or backtest unless explicitly scoped later.

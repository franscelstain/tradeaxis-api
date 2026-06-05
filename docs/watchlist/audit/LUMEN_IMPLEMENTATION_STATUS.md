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
`WATCHLIST — SCORING ENGINE FOUNDATION EXECUTION SESSION`

Status:
`PARTIAL` for Phase 3 scoring foundation until local PHPUnit confirms the new unit/static guards. Code, docs, and PHP lint scope are complete; readiness-critical contracts remain `PARTIAL` because no watchlist command/API runtime proof or artifact/log output exists yet.

Scope:
Create deterministic PLAN scoring foundation on top of the Phase 2 candidate universe. This session computes component scores for momentum, breakout, volume, and risk; records paramset/version traceability; returns explainable factor breakdown; applies deterministic tie-break sorting; and explicitly does not create recommendation, confirm overlay, backtest, API, command, scheduler, portfolio, or execution logic.

Evidence:

- `app/Application/Watchlist/Services/WatchlistScoringService.php` created.
- `tests/Unit/Watchlist/WatchlistScoringServiceTest.php` added.
- `tests/Unit/Watchlist/WatchlistScoringStaticGuardTest.php` added.
- `app/Application/Watchlist/Services/WatchlistCandidateUniverseService.php` updated to preserve scoring input metrics and `ticker_id` from Phase 1 rows inside candidate universe output.
- `app/Application/Watchlist/Services/WatchlistMarketDataConsumerReadService.php` and `app/Infrastructure/Persistence/MarketData/MarketDataWatchlistReadRepository.php` updated only to expose `ticker_id` needed by canonical deterministic tie-break input.
- Existing no raw/latest/`MAX(trade_date)` boundary remains enforced by static guards.

## Source of Truth ZIP

- Source ZIP: `tradeaxis-api.zip`
- Session date: `2026-05-29`
- Scope classification: Phase 3 watchlist scoring foundation code + tests + docs sync.

## Current Implementation Baseline

| Area | Status | Notes |
|---|---|---|
| Current status | `PHASE_3_SCORING_FOUNDATION_PARTIAL / NOT_PRODUCTION_READY` | Read model, candidate universe gates, and deterministic scoring foundation code/tests/docs exist, but local PHPUnit proof is still required. Whole watchlist remains not production-ready. |
| Main feature code | `PARTIAL` | Read model, candidate universe gates, and scoring foundation exist. Recommendation/backtest/runtime code is still not started. |
| Runtime API | `NOT_STARTED` | No API endpoint created. |
| Artisan command surface | `NOT_STARTED` | No watchlist command created. |
| Database schema | `NOT_STARTED` | No production migration created. Existing SQL docs/fixtures are support artifacts only. |
| Backtest engine | `NOT_STARTED` | No runtime backtest engine created. |
| Recommendation engine | `NOT_STARTED` | No runtime recommendation engine created. |
| Scoring engine | `PARTIAL` | `WatchlistScoringService` computes deterministic PLAN component scores and ranking over Phase 2 eligible universe rows; local PHPUnit proof is still required. |
| Market-data consumer read model | `DONE` | `WatchlistMarketDataConsumerReadService` consumes the official market-data read surface and validates candidate readiness. |
| Candidate universe / liquidity-risk gates | `DONE` | `WatchlistCandidateUniverseService` applies deterministic liquidity, ATR/risk, and volume participation guards over Phase 1 candidates. |
| Test coverage | `PARTIAL` | Read model, candidate universe, and scoring unit/static tests exist. Full watchlist runtime proof is not available yet. |
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
| `app/Application/Watchlist/Services/WatchlistScoringService.php` | `PARTIAL` | Computes deterministic PLAN scoring from Phase 2 candidate universe rows only; awaiting local PHPUnit proof. |
| `tests/Unit/Watchlist/WatchlistScoringServiceTest.php` | `PARTIAL` | Covers weighted score computation, exclusion, fail-closed source readiness, range clamp, ATR unit drift, deterministic tie-break, output contracts, and no recommendation/confirm/execution fields; awaiting local PHPUnit proof. |
| `tests/Unit/Watchlist/WatchlistScoringStaticGuardTest.php` | `PARTIAL` | Guards scoring service boundary, no raw/latest/MAX(date) access, reason-code parity, deterministic sort keys, and docs sync; awaiting local PHPUnit proof. |
| `app/Application/Watchlist/Services/WatchlistCandidateUniverseService.php` | `UPDATED` | Preserves scoring metrics and `ticker_id` from Phase 1 output while keeping Phase 2 gate semantics unchanged. |
| `app/Application/Watchlist/Services/WatchlistMarketDataConsumerReadService.php` | `UPDATED` | Passes through `ticker_id` when upstream provides it. |
| `app/Infrastructure/Persistence/MarketData/MarketDataWatchlistReadRepository.php` | `UPDATED` | Adds `ticker_id` to the publication-scoped watchlist read rows for deterministic tie-break input only. |

## Active Gaps

| Severity | Gap | Impact |
|---|---|---|
| `RUNTIME_PROOF_MISSING` | No watchlist command/API runtime proof exists for read model, candidate universe, or scoring. | WL-CONTRACT-001 through WL-CONTRACT-008 and WL-CONTRACT-011/WL-CONTRACT-014 remain not `LOCKED`. |
| `HIGH_RISK` | Recommendation/grouping layer is not implemented yet. | Scored output is PLAN scoring only; it does not produce TOP_PICKS/SECONDARY/Watch/Avoid groups or final recommendation. |
| `RUNTIME_PROOF_MISSING` | No watchlist artifact/log output exists. | Cannot claim audit artifact contract or production readiness. |
| `DOCS_ONLY` | Governance/read model/candidate universe/scoring docs are synced, but API/command/backtest/runtime artifact docs/code still need implementation sync. | Correct for current scope; not production readiness. |
| `NOT_READY` | No runtime API, command, production schema, recommendation engine, backtest engine, or portfolio-aware boundary implementation exists. | Watchlist Production Ready remains `NO`. |

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

Status: `PARTIAL` until local PHPUnit confirms scoring unit/static guards. Contracts remain not `LOCKED` until watchlist command/API runtime proof and artifact/log evidence exist.

### Phase 4 — Recommendation Engine

- Generate buy/watch/avoid labels.
- Reason code output.
- Risk notes.
- Add tests.

Status: `NOT_STARTED`.

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

## Validation Log

Validation performed in this session:

```text
php -l app/Application/Watchlist/Services/WatchlistScoringService.php
php -l app/Application/Watchlist/Services/WatchlistCandidateUniverseService.php
php -l app/Application/Watchlist/Services/WatchlistMarketDataConsumerReadService.php
php -l app/Infrastructure/Persistence/MarketData/MarketDataWatchlistReadRepository.php
php -l tests/Unit/Watchlist/WatchlistScoringServiceTest.php
php -l tests/Unit/Watchlist/WatchlistScoringStaticGuardTest.php
php runtime smoke check for WatchlistScoringService deterministic output
```

Observed validation result:

```text
php -l: PASS for all touched PHP files
runtime smoke check: PASS
phpunit: BLOCKED in sandbox because PHP extensions dom, mbstring, xml, xmlwriter are unavailable
```

Expected local follow-up on operator machine:

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistScoring"
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistCandidateUniverse"
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistMarketDataConsumerRead"
vendor\bin\phpunit tests\Unit\MarketData --filter "MarketDataWatchlistReadModelTest"
vendor\bin\phpunit tests\Unit\Watchlist
```

## Manual Validation Requirements

Required local checks:

```powershell
php -l app\Application\Watchlist\Services\WatchlistScoringService.php
php -l tests\Unit\Watchlist\WatchlistScoringServiceTest.php
php -l tests\Unit\Watchlist\WatchlistScoringStaticGuardTest.php
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistScoring"
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistCandidateUniverse"
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistMarketDataConsumerRead"
vendor\bin\phpunit tests\Unit\MarketData --filter "MarketDataWatchlistReadModelTest"
vendor\bin\phpunit tests\Unit\Watchlist
```

Pass criteria:

- scoring service consumes `WatchlistCandidateUniverseService` only;
- scoring output contains deterministic `score_total`, component scores, weights, factor breakdown, reason codes, and sort keys;
- rejected candidate does not receive valid score;
- ATR unit drift where `atr14_pct > 1` is rejected;
- watchlist application code does not contain raw DB reads, raw market-data table names, latest shortcuts, or `MAX(trade_date)` patterns;
- scoring output does not contain recommendation, confirm, portfolio, execution, order, or backtest fields;
- implementation status and contract tracker active sessions remain aligned.

## Production Readiness Status

`NOT_READY`.

Reason:

- market-data consumer read model exists but has no watchlist command/API runtime proof yet;
- candidate universe and scoring foundation exist only at service + unit/static scope;
- no runtime artifact/log output exists yet;
- no runtime API;
- no command surface;
- no production watchlist schema/migration;
- no recommendation/grouping engine;
- no backtest engine;
- no portfolio-aware integration;
- WL-CONTRACT-001 through WL-CONTRACT-008, WL-CONTRACT-011, and WL-CONTRACT-014 are not `LOCKED` because runtime proof/artifact evidence is missing;
- WL-CONTRACT-009, WL-CONTRACT-010, WL-CONTRACT-012, and WL-CONTRACT-013 remain `NOT_STARTED`.

## Next Required Sessions

Recommended next session:

`WATCHLIST — PLAN GROUPING + TOP_PICKS / SECONDARY SELECTION EXECUTION SESSION`

Target:

- consume scored output from `WatchlistScoringService`;
- apply deterministic grouping only;
- produce PLAN group semantics: `TOP_PICKS`, `SECONDARY`, `WATCH_ONLY`, `AVOID`;
- preserve boundary that PLAN groups are not final recommendation membership;
- do not create confirm overlay, recommendation final, portfolio logic, execution, API/command runtime, or backtest.

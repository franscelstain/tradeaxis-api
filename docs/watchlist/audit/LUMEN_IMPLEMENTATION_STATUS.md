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
`WATCHLIST — MARKET-DATA CONSUMER READ MODEL EXECUTION SESSION`

Status:
`DONE` for Phase 1 read model scope. Readiness-critical contracts remain `PARTIAL` because no watchlist command/API runtime proof exists yet.

Scope:
Create watchlist application read model over the official market-data watchlist read surface. This session does not create scoring, recommendation, backtest, API, command, scheduler, or UI logic.

Evidence:

- `app/Application/Watchlist/Services/WatchlistMarketDataConsumerReadService.php` created.
- `app/Infrastructure/Persistence/MarketData/MarketDataWatchlistReadRepository.php` hardened for publication/run scope, valid indicators, required non-null fields, active tickers, and eligible rows only.
- `tests/Unit/Watchlist/WatchlistMarketDataConsumerReadServiceTest.php` added.
- `tests/Unit/Watchlist/WatchlistMarketDataConsumerReadModelStaticGuardTest.php` added.
- `docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md` updated.
- `docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md` updated.

## Source of Truth ZIP

- Source ZIP: `tradeaxis-api-watchlist-governance-foundation.zip`
- Session date: `2026-05-28`
- Scope classification: Phase 1 watchlist read model code + tests + docs sync.

## Current Implementation Baseline

| Area | Status | Notes |
|---|---|---|
| Current status | `PHASE_1_READ_MODEL_DONE / NOT_PRODUCTION_READY` | Market-data consumer read model exists. Whole watchlist remains not production-ready. |
| Main feature code | `PARTIAL` | Read model exists. Scoring/recommendation/backtest runtime code is still not started. |
| Runtime API | `NOT_STARTED` | No API endpoint created. |
| Artisan command surface | `NOT_STARTED` | No watchlist command created. |
| Database schema | `NOT_STARTED` | No production migration created. Existing SQL docs/fixtures are support artifacts only. |
| Backtest engine | `NOT_STARTED` | No runtime backtest engine created. |
| Recommendation engine | `NOT_STARTED` | No runtime recommendation engine created. |
| Scoring engine | `NOT_STARTED` | No runtime scoring engine created. |
| Market-data consumer read model | `DONE` | `WatchlistMarketDataConsumerReadService` consumes the official market-data read surface and validates candidate readiness. |
| Test coverage | `PARTIAL` | Read model unit tests and static guard added. Full watchlist suite and runtime proof not available yet. |
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

## Active Gaps

| Severity | Gap | Impact |
|---|---|---|
| `RUNTIME_PROOF_MISSING` | No watchlist command/API runtime proof exists for the read model. | WL-CONTRACT-001 through WL-CONTRACT-005 remain `PARTIAL`, not `LOCKED`. |
| `BLOCKER` | Scoring/recommendation/backtest runtime code is not implemented. | No watchlist business decision runtime exists. |
| `HIGH_RISK` | Future watchlist consumers could bypass the read model if not covered by static guards. | Every future service/repository/command/API must remain inside the no raw/latest/MAX(date) guard. |
| `RUNTIME_PROOF_MISSING` | No watchlist artifact/log output exists. | Cannot claim audit artifact or production readiness. |
| `DOCS_ONLY` | Governance foundation exists, but Phase 2+ business docs/code still need implementation sync. | Correct for current scope; not feature readiness. |

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

Status: `NOT_STARTED`.

### Phase 3 — Scoring Engine

- Define score factors.
- Define weight/paramset.
- Deterministic scoring.
- Explainable score breakdown.
- Add tests.

Status: `NOT_STARTED`.

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

## Validation Log

Validation performed in this session:

```text
php -l app/Application/Watchlist/Services/WatchlistMarketDataConsumerReadService.php
php -l app/Infrastructure/Persistence/MarketData/MarketDataWatchlistReadRepository.php
php -l tests/Unit/Watchlist/WatchlistMarketDataConsumerReadServiceTest.php
php -l tests/Unit/Watchlist/WatchlistMarketDataConsumerReadModelStaticGuardTest.php
python static checks for active session alignment, required files, and no-bypass patterns
```

Observed validation result:

```text
php -l: PASS for all touched PHP files
static file checks: PASS
active session sync: PASS
phpunit: BLOCKED in sandbox because PHP extensions dom, mbstring, xml, xmlwriter are unavailable
```

Expected local follow-up on operator machine:

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistMarketDataConsumerRead"
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistAuditGovernanceStaticGuardTest|WatchlistMarketDataConsumerReadModelStaticGuardTest"
vendor\bin\phpunit tests\Unit\MarketData --filter "MarketDataWatchlistReadModelTest"
```

## Manual Validation Requirements

Required local checks:

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistMarketDataConsumerRead"
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistAuditGovernanceStaticGuardTest|WatchlistMarketDataConsumerReadModelStaticGuardTest"
vendor\bin\phpunit tests\Unit\MarketData --filter "MarketDataWatchlistReadModelTest"
```

Pass criteria:

- watchlist read service returns only valid candidates from current readable market-data publication;
- no readable market-data publication returns fail-closed payload and zero candidates;
- invalid/non-eligible/incomplete rows are rejected with reason-coded exclusions;
- watchlist application code does not contain raw DB reads, raw market-data table names, latest shortcuts, or `MAX(trade_date)` patterns;
- implementation status and contract tracker active sessions remain aligned.

## Production Readiness Status

`NOT_READY`.

Reason:

- market-data consumer read model exists but has no watchlist command/API runtime proof yet;
- no runtime artifact/log output exists yet;
- no runtime API;
- no command surface;
- no production watchlist schema/migration;
- no scoring/recommendation/backtest engine;
- WL-CONTRACT-001 through WL-CONTRACT-005 are `PARTIAL`, not `LOCKED`;
- WL-CONTRACT-006 through WL-CONTRACT-013 remain `NOT_STARTED`.

## Next Required Sessions

Recommended next session:

`WATCHLIST — CANDIDATE UNIVERSE + LIQUIDITY/RISK FILTER EXECUTION SESSION`

Target:

- define candidate universe from the Phase 1 read model output;
- add liquidity/risk/volatility gates without scoring yet;
- keep market-data read boundary intact;
- add deterministic filter reason codes;
- add unit/static guard tests;
- update `LUMEN_IMPLEMENTATION_STATUS.md` and `LUMEN_CONTRACT_TRACKER.md`.

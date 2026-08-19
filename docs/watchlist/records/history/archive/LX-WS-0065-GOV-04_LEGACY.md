# Legacy Role Extract — LEGACY — GOVERNANCE

> **Document Type:** GOVERNANCE
> **Authoritative Role:** `GOVERNANCE`
> **Status:** HISTORICAL_EXTRACT / IMMUTABLE
> **Legacy Extract ID:** `LX-WS-0065-GOV-04`
> **Legacy Source ID:** `LS-WS-0065`
> **Legacy Work Key:** `LEGACY`
> **Original Path:** `docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md`
> **Original SHA1:** `EE2593354FAC55E6E3B4579525334F9865A752A4`
> **Source Sections:** L4025-L4033 Created Governance Files; L8445-L8469 DB Dictionary and Field Usage Governance
> **Extract Body SHA1:** `692B5EFB9D531BB6A5C697BAA207FD58FA06CF7E`
> **Current Authority:** NO

The body below is an exact copy of the registered source sections for this single semantic role. Cross-role authority is intentionally excluded.

---

## Created Governance Files

| File | Status | Purpose |
|---|---|---|
| `docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md` | `DONE` for initial foundation | Defines update rules, status taxonomy, evidence rule, anti-overclaim, docs sync, market-data dependency, and readiness claim rules. |
| `docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md` | `DONE` for initial foundation | Tracks current implementation status, evidence, validation, gaps, and roadmap. |
| `docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md` | `DONE` for initial foundation | Defines baseline contracts WL-CONTRACT-001 through WL-CONTRACT-015. |
| `tests/Unit/Watchlist/WatchlistAuditGovernanceStaticGuardTest.php` | `DONE` for initial foundation | Guards existence and critical wording of the three governance tracker docs. |

## DB Dictionary and Field Usage Governance

Status: `DONE_DOCS_ONLY_DICTIONARY_CREATED`

Last updated: 2026-06-22

Related contract: `WATCHLIST_DB_DICTIONARY_REQUIRED_CONTRACT`

Implementation:

- Added `docs/watchlist/system/db/WATCHLIST_DB_DICTIONARY.md` for Watchlist-owned DB tables and Market Data consumer rules.
- Added shared requirement to read `docs/market_data/db/MARKET_DATA_DICTIONARY.md` before any Watchlist session touches database-connected data.
- Updated Watchlist audit and implementation prompt standards so future prompts must include the dictionary-reading clause.

Final behavior:

- Watchlist sessions touching PLAN, CONFIRM, backtest, diagnostics, source reconstruction, market-index/regime fields, sector metadata, or eligibility must read the database dictionary first.
- Missing table/field/role coverage is a blocker or required dictionary update.
- Selection/evaluation safety must be established before coding.

Evidence:

- Docs-only update.
- Based on C57 final evidence where market-index reconstruction required correct mapping from `market_benchmark_indicators.roc_20` and `market_benchmark_indicators.ma20_slope_pct`.

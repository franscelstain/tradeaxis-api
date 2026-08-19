# WS DB Dictionary and Field Usage Audit

Status: `DONE_DOCS_ONLY_DICTIONARY_CREATED`

Last updated: 2026-06-22

## Purpose

Create a formal database dictionary and governance rule so future Market Data, Watchlist, backtest, audit, and feature work does not infer table/column names or field roles from memory.

## Evidence / Context

C57 initially discovered `market_benchmark_indicators` but failed market-index reconstruction until the actual schema was inspected. The root mapping was:

```text
market_index_roc20 => market_benchmark_indicators.roc_20 where benchmark_code='IHSG'
market_index_ma20_slope_pct => market_benchmark_indicators.ma20_slope_pct where benchmark_code='IHSG'
market_calendar date key => cal_date
```

## Files Added

- `docs/market_data/db/MARKET_DATA_DICTIONARY.md`
- `docs/market_data/db/README.md`
- `docs/db/DATABASE_DICTIONARY_USAGE_RULE.md`
- `docs/watchlist/development/implementation/db/WATCHLIST_DB_DICTIONARY.md`
- `docs/watchlist/records/evidence/runs/WS_DB_DICTIONARY_AND_FIELD_USAGE_AUDIT.md`
- `docs/watchlist/records/evidence/runs/WS_DB_DICTIONARY_OPERATOR_COMMANDS.md`

## Files Updated

- `docs/market_data/db/Database_Schema_Contracts_MariaDB.md`
- `docs/market_data/db/DB_FIELDS_AND_METADATA.md`
- `docs/market_data/audit/AUDIT_UPDATE_GOVERNANCE.md`
- `docs/market_data/audit/LUMEN_IMPLEMENTATION_STATUS.md`
- `docs/market_data/audit/LUMEN_CONTRACT_TRACKER.md`
- `docs/watchlist/authority/governance/audit/AUDIT_UPDATE_GOVERNANCE.md`
- `docs/watchlist/authority/governance/audit/WATCHLIST_AUDIT_PROMPT_STANDARD.md`
- `docs/watchlist/authority/governance/audit/IMPL_WATCHLIST_IMPLEMENTATION_PROMPT_STANDARD.md`
- `docs/watchlist/records/evidence/LUMEN_IMPLEMENTATION_STATUS.md`
- `docs/watchlist/authority/governance/LUMEN_CONTRACT_TRACKER.md`

## Final Rule

Any database-connected task must read `docs/market_data/db/MARKET_DATA_DICTIONARY.md` and `docs/db/DATABASE_DICTIONARY_USAGE_RULE.md` before implementation. If a table/column/field role is missing, update the dictionary first or mark the task blocked.

## Validation

Docs-only change. Validation should confirm file existence and required guardrail strings. See `WS_DB_DICTIONARY_OPERATOR_COMMANDS.md`.

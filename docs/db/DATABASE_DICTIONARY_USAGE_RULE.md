# Database Dictionary Usage Rule

Status: `MANDATORY_FOR_DATABASE_CONNECTED_WORK`

Last updated: 2026-06-22

This rule applies to Market Data, Watchlist, backtest, audit, dashboards, API read models, future screeners, and any feature that reads, writes, transforms, or interprets database-backed data.

## Mandatory References

Before coding or auditing database-connected work, read:

1. `docs/market_data/db/MARKET_DATA_DICTIONARY.md`
2. Physical schema/migrations relevant to touched tables:
   - `docs/market_data/db/Database_Schema_MariaDB.sql`
   - `database/migrations/**`
   - `docs/watchlist/system/db/**` when touching Watchlist tables
3. Module owner docs for the feature behavior.

## Hard Rules

- Do not infer table or column names from memory.
- Do not assume equity and benchmark fields share the same spelling. Example: equity `eod_indicators.roc20` differs from benchmark `market_benchmark_indicators.roc_20`.
- Do not use unbounded `MAX(trade_date)` to resolve current/as-of data.
- Do not cross from IS to OOS rows for tuning, source reconstruction, candidate selection, or gate repair.
- Do not use return/path/evaluation fields as selection inputs.
- If dictionary coverage is missing, update the dictionary before claiming final implementation or evidence.

## Required Prompt Clause

Every future prompt/session that touches database-connected data must include a clause equivalent to:

```text
Before implementation, read `docs/market_data/db/MARKET_DATA_DICTIONARY.md` and the relevant schema/migration docs. Identify each table, date key, identifier key, field role, and as-of safety rule before coding. Do not infer DB field names. If the dictionary is missing a field/table, update the dictionary or mark the session blocked before implementation.
```

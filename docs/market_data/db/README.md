# Market Data DB Documentation Index

Status: `DATABASE_DICTIONARY_INDEX`

Last updated: 2026-06-22

## Mandatory first-read documents

For any database-connected Market Data, Watchlist, backtest, audit, API, dashboard, or future feature work, read these first:

1. `MARKET_DATA_DICTIONARY.md` — operational table/column dictionary, field roles, as-of rules, and consumer mappings.
2. `Database_Schema_MariaDB.sql` — physical schema contract.
3. `Database_Schema_Contracts_MariaDB.md` — semantic schema contract.
4. `DB_FIELDS_AND_METADATA.md` — coverage-gate and metadata addendum.
5. `docs/db/DATABASE_DICTIONARY_USAGE_RULE.md` — shared governance rule for all database-connected work.

## Current critical mappings

- `market_index_roc20` maps to `market_benchmark_indicators.roc_20` for `benchmark_code='IHSG'`.
- `market_index_ma20_slope_pct` maps to `market_benchmark_indicators.ma20_slope_pct` for `benchmark_code='IHSG'`.
- `market_calendar` date key is `cal_date`.
- IHSG is a benchmark/index row outside the equity ticker universe; do not search it in `eod_indicators` unless a future contract explicitly creates that representation.

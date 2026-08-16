# WS DB Dictionary Operator Validation Commands

Status: `DOCS_ONLY_VALIDATION_COMMANDS`

Last updated: 2026-06-22

## File existence

```powershell
Test-Path docs/market_data/db/MARKET_DATA_DICTIONARY.md
Test-Path docs/market_data/db/README.md
Test-Path docs/db/DATABASE_DICTIONARY_USAGE_RULE.md
Test-Path docs/watchlist/implementation/persistence/WATCHLIST_DB_DICTIONARY.md
Test-Path docs/watchlist/evidence/weekly_swing/results/WS_DB_DICTIONARY_AND_FIELD_USAGE_AUDIT.md
```

Expected: all `True`.

## Required mapping markers

```powershell
Select-String -Path docs/market_data/db/MARKET_DATA_DICTIONARY.md -Pattern "market_benchmark_indicators.roc_20"
Select-String -Path docs/market_data/db/MARKET_DATA_DICTIONARY.md -Pattern "market_benchmark_indicators.ma20_slope_pct"
Select-String -Path docs/market_data/db/MARKET_DATA_DICTIONARY.md -Pattern "benchmark_code='IHSG'"
Select-String -Path docs/market_data/db/MARKET_DATA_DICTIONARY.md -Pattern "cal_date"
```

Expected: all markers found.

## Governance markers

```powershell
Select-String -Path docs/watchlist/governance/audit/AUDIT_UPDATE_GOVERNANCE.md -Pattern "Database Dictionary Required Rule"
Select-String -Path docs/market_data/audit/AUDIT_UPDATE_GOVERNANCE.md -Pattern "Database Dictionary Required Rule"
Select-String -Path docs/watchlist/governance/audit/WATCHLIST_AUDIT_PROMPT_STANDARD.md -Pattern "MARKET_DATA_DICTIONARY"
Select-String -Path docs/watchlist/governance/audit/implementation/WATCHLIST_IMPLEMENTATION_PROMPT_STANDARD.md -Pattern "MARKET_DATA_DICTIONARY"
```

Expected: all markers found.

## Physical schema cross-check without Tinker

Use direct DB SQL/client or a temporary PDO probe if needed. Lumen does not require Tinker for this validation.

Minimum DB checks:

```sql
select count(*) from market_benchmark_indicators where benchmark_code = 'IHSG';
select count(*) from market_benchmark_bars where benchmark_code = 'IHSG';
select column_name from information_schema.columns where table_name = 'market_benchmark_indicators' and column_name in ('roc_20','ma20_slope_pct');
select column_name from information_schema.columns where table_name = 'market_calendar' and column_name = 'cal_date';
```

Expected: IHSG rows exist, `roc_20`, `ma20_slope_pct`, and `cal_date` exist.

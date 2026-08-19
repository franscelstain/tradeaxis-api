# Legacy Role Extract — LEGACY — EVIDENCE

> **Document Type:** EVIDENCE
> **Authoritative Role:** `EVIDENCE`
> **Status:** HISTORICAL_EXTRACT / IMMUTABLE
> **Legacy Extract ID:** `LX-WS-0501-EVD-01`
> **Legacy Source ID:** `LS-WS-0501`
> **Legacy Work Key:** `LEGACY`
> **Original Path:** `docs/watchlist/audit/WS_DB_DICTIONARY_OPERATOR_COMMANDS.md`
> **Original SHA1:** `B7CB10825494E4A4390AAC0596B31749EBBF19C8`
> **Source Sections:** L7-L18 File existence; L19-L29 Required mapping markers; L30-L40 Governance markers
> **Extract Body SHA1:** `FECECA696A2AEAA462F1C4701B43CADF3C4287D6`
> **Current Authority:** NO

The body below is an exact copy of the registered source sections for this single semantic role. Cross-role authority is intentionally excluded.

---

## File existence

```powershell
Test-Path docs/market_data/db/MARKET_DATA_DICTIONARY.md
Test-Path docs/market_data/db/README.md
Test-Path docs/db/DATABASE_DICTIONARY_USAGE_RULE.md
Test-Path docs/watchlist/system/db/WATCHLIST_DB_DICTIONARY.md
Test-Path docs/watchlist/audit/WS_DB_DICTIONARY_AND_FIELD_USAGE_AUDIT.md
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
Select-String -Path docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md -Pattern "Database Dictionary Required Rule"
Select-String -Path docs/market_data/audit/AUDIT_UPDATE_GOVERNANCE.md -Pattern "Database Dictionary Required Rule"
Select-String -Path docs/watchlist/audit/WATCHLIST_AUDIT_PROMPT_STANDARD.md -Pattern "MARKET_DATA_DICTIONARY"
Select-String -Path docs/watchlist/audit/implementation/WATCHLIST_IMPLEMENTATION_PROMPT_STANDARD.md -Pattern "MARKET_DATA_DICTIONARY"
```

Expected: all markers found.

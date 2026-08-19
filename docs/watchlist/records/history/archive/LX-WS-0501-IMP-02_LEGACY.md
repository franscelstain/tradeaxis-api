# Legacy Role Extract — LEGACY — IMPLEMENTATION

> **Document Type:** IMPLEMENTATION
> **Authoritative Role:** `IMPLEMENTATION`
> **Status:** HISTORICAL_EXTRACT / IMMUTABLE
> **Legacy Extract ID:** `LX-WS-0501-IMP-02`
> **Legacy Source ID:** `LS-WS-0501`
> **Legacy Work Key:** `LEGACY`
> **Original Path:** `docs/watchlist/audit/WS_DB_DICTIONARY_OPERATOR_COMMANDS.md`
> **Original SHA1:** `B7CB10825494E4A4390AAC0596B31749EBBF19C8`
> **Source Sections:** L41-L54 Physical schema cross-check without Tinker
> **Extract Body SHA1:** `7E18C60D023D425E7738D6464ED4C5F72B4DE537`
> **Current Authority:** NO

The body below is an exact copy of the registered source sections for this single semantic role. Cross-role authority is intentionally excluded.

---

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

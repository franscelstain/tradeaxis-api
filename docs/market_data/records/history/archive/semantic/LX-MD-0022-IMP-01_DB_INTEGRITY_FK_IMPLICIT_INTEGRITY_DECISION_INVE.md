# Legacy Semantic Extract — LX-MD-0022-IMP-01

- Source ID: `LS-MD-0022`
- Original path: `audit/DB_INTEGRITY_FK_IMPLICIT_INTEGRITY_DECISION_INVENTORY.md`
- Original SHA1: `BA5CB0819D76C0ADAEA2600174DA40EF3CFF16A3`
- Extract role: `IMPLEMENTATION`
- Source range: `L40-L62`
- Extract body SHA1: `89A9429FC567ECF39987A65959C10B341B3FB5B1`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Schema Constraint Matrix

| Table | Column | Current Constraint | FK Exists | Unique/Index Exists | Nullable | Runtime Meaning | Risk | Decision |
|---|---|---|---:|---:|---:|---|---|---|
| `tickers` | `ticker_id` | PK | No downstream FK from live artifact | Yes | No | Master ticker identity | Orphan ticker reference if source bypasses ticker mapper | `IMPLICIT_GUARD_ACCEPTED` for downstream current artifacts; master PK remains explicit |
| `market_calendar` | `cal_date` | PK | No artifact FK | Yes | No | Trading-date authority | Invalid non-trading date if service bypasses calendar | `IMPLICIT_GUARD_ACCEPTED` via calendar/source validation |
| `eod_bars` | `trade_date,ticker_id` | PK | No | Yes | No | Current readable bar row identity | Orphan/mismatched current row if written outside repository | `HYBRID_REQUIRED`: PK/index + implicit guard |
| `eod_bars` | `run_id,publication_id` | NOT NULL + indexes | No | Yes | No | Run/publication context for current row | FK could false-block phase-dependent promote/correction; missing guard would allow stale row | `IMPLICIT_GUARD_ACCEPTED` with repository/read-side/evidence guard |
| `eod_indicators` | `trade_date,ticker_id` | PK | No | Yes | No | Current indicator row identity | Same as bars | `HYBRID_REQUIRED` |
| `eod_indicators` | `run_id,publication_id` | NOT NULL + indexes | No | Yes | No | Run/publication context | Same as bars | `IMPLICIT_GUARD_ACCEPTED` |
| `eod_eligibility` | `trade_date,ticker_id` | PK | No | Yes | No | Current eligibility row identity | Same as bars | `HYBRID_REQUIRED` |
| `eod_eligibility` | `run_id,publication_id` | NOT NULL + indexes | No | Yes | No | Run/publication context | Same as bars | `IMPLICIT_GUARD_ACCEPTED` |
| `eod_publications` | `publication_id` | PK | Referenced by pointer/history | Yes | No | Publication identity | Publication/run mirror mismatch | `IMPLICIT_GUARD_ACCEPTED` for run mirror; referenced by stable FKs where safe |
| `eod_publications` | `trade_date,publication_version` | Unique | n/a | Yes | No | Publication version identity | Duplicate version ambiguity | `EXPLICIT_FK_REQUIRED` not applicable; unique key required and present |
| `eod_current_publication_pointer` | `trade_date` | PK | n/a | Yes | No | Single current pointer per date | Multiple current pointer rows | `EXPLICIT_FK_REQUIRED` for publication; PK/unique present |
| `eod_current_publication_pointer` | `publication_id` | FK + unique | Yes | Yes | No | Pointer target publication | Broken pointer target | `EXPLICIT_FK_REQUIRED`, already present |
| `eod_current_publication_pointer` | `run_id,publication_version` | Index + mirror guard | No | Yes | No | Pointer mirror context | Pointer target mismatch | `IMPLICIT_GUARD_ACCEPTED` |
| `eod_bars_history` | `publication_id,trade_date,ticker_id` | PK + FK publication | Yes for publication | Yes | No | Immutable publication snapshot | Historical orphan if FK removed | `EXPLICIT_FK_REQUIRED`, already present |
| `eod_indicators_history` | `publication_id,trade_date,ticker_id` | PK + FK publication | Yes for publication | Yes | No | Immutable publication snapshot | Historical orphan if FK removed | `EXPLICIT_FK_REQUIRED`, already present |
| `eod_eligibility_history` | `publication_id,trade_date,ticker_id` | PK + FK publication | Yes for publication | Yes | No | Immutable publication snapshot | Historical orphan if FK removed | `EXPLICIT_FK_REQUIRED`, already present |
| `eod_runs` | `run_id` | PK | No downstream FK selected | Yes | No | Run lifecycle identity | Run/publication/correction mismatch | `IMPLICIT_GUARD_ACCEPTED` for lifecycle mirror |
| `eod_dataset_corrections` | correction linkage columns | Nullable + indexes | No | Yes | Yes | Phase-dependent correction lineage | FK could block requested/approved states before run/publication exists | `IMPLICIT_GUARD_ACCEPTED` |


<!-- LEGACY_EXTRACT_BODY_END -->

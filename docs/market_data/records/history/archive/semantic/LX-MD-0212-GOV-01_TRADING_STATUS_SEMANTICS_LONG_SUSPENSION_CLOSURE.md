# Legacy Semantic Extract — LX-MD-0212-GOV-01

- Source ID: `LS-MD-0212`
- Original path: `patches/TRADING_STATUS_SEMANTICS_LONG_SUSPENSION_CLOSURE_2026_07_02.md`
- Original SHA1: `A26CB394EAA179AFC224499200FE3C7B5AAE0E7B`
- Extract role: `GOVERNANCE`
- Source range: `L15-L34`
- Extract body SHA1: `D72FBFEF257FB210F832298CE535577DAACCF10C`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Scope

This closure records the final Market Data trading-status semantics and the 2026-06-29 coverage recovery proof after importing IDX long-suspension evidence.

The session closed three related issues:

1. Historical `SUSPENDED` state must not remain effective after a newer official non-exclusion status.
2. `SPECIAL_MONITORING`, `SPECIAL_MONITORING_EXIT`, and `UMA` must remain event-risk context only when `coverage_exclusion_flag=0`; they must not block import or coverage.
3. IDX `Suspensi Lebih Dari 6 Bulan` / potential-delisting data is a separate long-suspension evidence source and must be imported as coverage exclusion.

## Final domain rules

- Suspension does not expire by age.
- Effective coverage exclusion is determined by the latest official semantic status as of the target trade date.
- `SUSPENDED`, `HALT`, `LONG_SUSPENSION_GT_6M`, or any row with `coverage_exclusion_flag=1` excludes the ticker from the coverage universe.
- `SPECIAL_MONITORING`, `SPECIAL_MONITORING_EXIT`, `UMA`, `WATCHLIST`, or `NOTASI_KHUSUS` with `coverage_exclusion_flag=0` clears older suspension carry-forward for coverage purposes.
- `SPECIAL_MONITORING` and `UMA` remain event-risk signals when applicable, but they are not import or coverage blockers.
- `SPECIAL_MONITORING_EXIT` clears special-monitoring event risk and must not force an unrelated UMA flag when the source row does not carry UMA information.
- IDX long-suspension / potential-delisting sources must be represented by `LONG_SUSPENSION_GT_6M`, `is_suspended=1`, and `coverage_exclusion_flag=1`.


<!-- LEGACY_EXTRACT_BODY_END -->

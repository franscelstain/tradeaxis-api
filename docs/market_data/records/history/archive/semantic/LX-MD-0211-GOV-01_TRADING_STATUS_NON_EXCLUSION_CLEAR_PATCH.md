# Legacy Semantic Extract — LX-MD-0211-GOV-01

- Source ID: `LS-MD-0211`
- Original path: `patches/TRADING_STATUS_NON_EXCLUSION_CLEAR_PATCH.md`
- Original SHA1: `D7FA2DA1E496AD9DA1448E9736FD87DDADE28E6C`
- Extract role: `GOVERNANCE`
- Source range: `L11-L23`
- Extract body SHA1: `82E25EEE991A75ECB491BE410816E42A9F9335D0`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Purpose

This patch refines the Market Data trading-status resolver so that an official latest non-exclusion status clears old suspension carry-forward for coverage purposes, without removing event-risk context.

## Domain rule

- `coverage_exclusion_flag = 1` means the ticker is excluded from coverage, e.g. `SUSPENDED`, `HALT`, `LONG_SUSPENSION_GT_6M`.
- `coverage_exclusion_flag = 0` on an official non-exclusion status clears old coverage exclusion carry-forward.
- `SPECIAL_MONITORING`, `SPECIAL_MONITORING_EXIT`, `UMA`, `WATCHLIST`, and `NOTASI_KHUSUS` are not coverage blockers when `coverage_exclusion_flag = 0`.
- `SPECIAL_MONITORING` and `UMA` may remain event-risk signals even when they are not coverage blockers.
- `SPECIAL_MONITORING_EXIT` clears special-monitoring event risk and must not be treated as suspended.
- Long-suspension sources such as IDX `Suspensi Lebih Dari 6 Bulan` must be imported as `coverage_exclusion_flag = 1`.


<!-- LEGACY_EXTRACT_BODY_END -->

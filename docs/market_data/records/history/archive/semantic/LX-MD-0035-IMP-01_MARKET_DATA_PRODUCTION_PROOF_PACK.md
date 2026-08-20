# Legacy Semantic Extract — LX-MD-0035-IMP-01

- Source ID: `LS-MD-0035`
- Original path: `audit/MARKET_DATA_PRODUCTION_PROOF_PACK.md`
- Original SHA1: `9D1E95DE0523A6EFF6B7E31DF54056B51CB33F26`
- Extract role: `IMPLEMENTATION`
- Source range: `L137-L147`
- Extract body SHA1: `F46EDA92AB63B713947F2816FB716BEA414C2C23`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## 5. Command Runtime Matrix Summary

| Scenario | Artifact / Output | Result | Status |
|---|---|---|---|
| daily import-only | `daily-2026-05-11/market_data_daily_summary.json` | `run_id=30`, accepted `913`, pointer switch `false` | PASS |
| promote success | `promote-2026-05-14/market_data_promote_summary.json` | `run_id=33`, `publication_id=27`, `coverage_gate_state=PASS`, `publishability_state=READABLE`, `seal_state=SEALED` | PASS |
| held partial promote | `held-partial-promote-2026-05-16/market_data_promote_summary.json` | `terminal_status=HELD`, `coverage_reason_code=COVERAGE_BELOW_THRESHOLD`, available `5/913` | PASS |
| failed empty source | `failed-empty-daily-2026-05-17/market_data_daily_summary.json` | `terminal_status=FAILED`, `final_reason_code=RUN_SOURCE_MANUAL_FILE_EMPTY` | PASS |
| replay smoke | `replay-smoke-run-33/replay_smoke_suite_summary.json` | `all_passed=true`, generated valid fixture included | PASS |
| replay backfill | `replay-backfill-run-33/market_data_replay_backfill_summary.json` | `all_passed=true`, replay case MATCH | PASS |


<!-- LEGACY_EXTRACT_BODY_END -->

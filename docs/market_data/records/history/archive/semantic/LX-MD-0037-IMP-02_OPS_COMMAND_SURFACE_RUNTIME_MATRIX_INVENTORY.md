# Legacy Semantic Extract — LX-MD-0037-IMP-02

- Source ID: `LS-MD-0037`
- Original path: `audit/OPS_COMMAND_SURFACE_RUNTIME_MATRIX_INVENTORY.md`
- Original SHA1: `D6E40A3FC4141C4D0798627BD21A5F34418206F8`
- Extract role: `IMPLEMENTATION`
- Source range: `L245-L253`
- Extract body SHA1: `F92FEB389BE9FF3C40EADCD04B9B1527A17AA887`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Patch Summary

- Backfill, replay verify, replay smoke, replay backfill, replay fixture generation, correction approve/run, and session snapshot capture now expose parser-optional arguments where needed so the command can render `status=BLOCKED` and a reason code instead of a raw Symfony missing-argument error.
- `ReplaySmokeSuiteCommand` catches service failures and renders `status=BLOCKED`, an actionable `reason_code`, and `replay_status=BLOCKED`.
- `ApproveCorrectionCommand` catches missing/non-executable correction records and renders `COMMAND_CORRECTION_NOT_FOUND` or `COMMAND_CORRECTION_STATUS_NOT_EXECUTABLE`.
- `market-data:eod-bars:ingest` now accepts explicit `--request_mode` for stage-by-stage publish proof while preserving import-only as the default.
- `MarketDataPipelineService::completeHash()` is public so `market-data:audit:hash` can execute the documented hash stage at runtime.
- New command/static tests cover missing required input, request-mode validation, service-failure behavior, and hash-stage command visibility.


<!-- LEGACY_EXTRACT_BODY_END -->

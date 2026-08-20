# Legacy Semantic Extract — LX-MD-0035-EVD-09

- Source ID: `LS-MD-0035`
- Original path: `audit/MARKET_DATA_PRODUCTION_PROOF_PACK.md`
- Original SHA1: `9D1E95DE0523A6EFF6B7E31DF54056B51CB33F26`
- Extract role: `EVIDENCE`
- Source range: `L954-L973`
- Extract body SHA1: `C72B1DF0881C39F4977E58B82389E64C5D2D34FB`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## 2026-06-05 - Provider Smoke Artifact Refresh and Full MarketData Suite Proof

Status: `PASS`.

This append-only reconciliation refreshes the authoritative provider-smoke proof after a stale invalid-date smoke artifact was detected by static guards.

- Previous stale artifact state: `php artisan market-data:provider:smoke --ticker=BBCA --trade_date=2025-11-30 --dry-run --retry-max=0` returned `provider_smoke_status=BLOCKED`, `reason_code=PROVIDER_EMPTY_OR_INVALID_RESPONSE`, `source_reason_code=RUN_SOURCE_RESPONSE_CHANGED`, and `http_status=200`.
- That blocked result was fail-closed and must not be counted as provider PASS.
- Refreshed command: `php artisan market-data:provider:smoke --ticker=BBCA --trade_date=2026-05-20 --dry-run --retry-max=0`.
- Refreshed artifact: `storage/app/market-data/provider-smoke-safe-mode/command-output/provider-smoke-bbca.txt`.
- Refreshed result: `provider_smoke_status=PASS`, `reason_code=PROVIDER_SMOKE_OK`, `source_reason_code=none`, `http_status=200`, `returned_row_count=1`, `attempt_count=1`, `retry_max=0`, `retry_exhausted=false`, `timeout_seconds=10`.
- Safety flags remained false: `publication_created=false`, `seal_executed=false`, `finalize_executed=false`, `pointer_switched=false`, `readable_publication_created=false`, `full_universe_fetch=false`.
- Targeted static guards:
  - `vendor\bin\phpunit tests\Unit\MarketData\ProviderSmokeSafeModeStaticGuardTest.php` -> OK (6 tests, 169 assertions).
  - `vendor\bin\phpunit tests\Unit\MarketData\ProductionValidationRuntimeProofStaticGuardTest.php` -> OK (15 tests, 491 assertions).
  - `vendor\bin\phpunit tests\Unit\MarketData\ProductionSchedulerCronStaticGuardTest.php` -> OK (5 tests, 107 assertions).
- Full MarketData suite: `vendor\bin\phpunit tests\Unit\MarketData` -> OK (635 tests, 9474 assertions), Time 00:35.061, Memory 48.00 MB.

Final current-source decision remains `OPS_RUNTIME_PARITY_PASSED`, backed by a refreshed real provider PASS artifact and full MarketData PHPUnit proof.


<!-- LEGACY_EXTRACT_BODY_END -->

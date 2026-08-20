# Legacy Semantic Extract — LX-MD-0035-CTX-02

- Source ID: `LS-MD-0035`
- Original path: `audit/MARKET_DATA_PRODUCTION_PROOF_PACK.md`
- Original SHA1: `9D1E95DE0523A6EFF6B7E31DF54056B51CB33F26`
- Extract role: `CONTEXT`
- Source range: `L565-L607`
- Extract body SHA1: `BCF730793027404348E45C033D56918D63277410`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## 2026-05-22 — YAHOO_PROVIDER_SMOKE_REQUEST_CONTEXT_HARDENING

[SESSION] YAHOO_PROVIDER_SMOKE_REQUEST_CONTEXT_HARDENING

[SESSION_STATUS] OPS_RUNTIME_PARITY_PASSED

[FINAL_DECISION]
- Core source-code readiness remains `MARKET_DATA_PRODUCTION_READY_LOCKED`.  **[SUPERSEDED 2026-08-06 — W22]** Klaim ini tidak berlaku untuk baseline yang dikoreksi; lihat `reports/AUDIT_FINAL_STATE.md`.
- Provider smoke safe mode: `PROVIDER_SMOKE_SAFE_MODE_SURFACE_ADDED`.
- Live provider smoke: `LIVE_PROVIDER_SMOKE_PASSED`.
- Ops rollout/runtime parity: `OPS_RUNTIME_PARITY_PASSED`.
- `ROOT_CAUSE_FIXED=PHP_ADAPTER_HEADER_CONTEXT_MISMATCH`.
- `FINAL_PROVIDER_SMOKE=PASSED`.

[PHASE_1_REQUEST_CONTEXT_PROOF]
- Request URL: `https://query1.finance.yahoo.com/v8/finance/chart/BBCA.JK?interval=1d&range=10d&includePrePost=false&events=div%2Csplits&corsDomain=finance.yahoo.com`.
- Minimal PHP header artifact: `storage/app/market-data/provider-smoke-request-context/command-output/php-request-minimal-header.txt` -> HTTP 200.
- Browser-like PHP header artifact: `storage/app/market-data/provider-smoke-request-context/command-output/php-request-browser-like-header.txt` -> HTTP 200.
- Root cause: `PHP_ADAPTER_HEADER_CONTEXT_MISMATCH`.

[PROVIDER_SMOKE_RUNTIME]
- Command: `php artisan market-data:provider:smoke --ticker=BBCA --trade_date=2026-05-20 --dry-run --retry-max=0`.
- Artifact: `storage/app/market-data/provider-smoke-safe-mode/command-output/provider-smoke-bbca.txt`.
- Result: `provider_smoke_status=PASS`, `reason_code=PROVIDER_SMOKE_OK`, `http_status=200`, `attempt_count=1`, `retry_max=0`, `retry_exhausted=false`, `timeout_seconds=10`.
- Safety: `publication_created=false`, `seal_executed=false`, `finalize_executed=false`, `pointer_switched=false`, `readable_publication_created=false`, `full_universe_fetch=false`.

[IMPLEMENTATION]
- `PublicApiEodBarsAdapter` now sends browser-like Yahoo headers and supports optional `{period1}` / `{period2}` endpoint placeholders.
- `ProviderSmokeCommand` now has `--retry-max=0` and emits request URL, HTTP status, response body sample, adapter/source reason codes, attempt count, retry max, retry exhaustion, and timeout.
- Provider-smoke reason registry/seed now includes request-context, parse-failure, and trade-date-not-found classifications.

[VALIDATION]
- Syntax checks passed for `ProviderSmokeCommand.php`, `PublicApiEodBarsAdapter.php`, `config/market_data.php`, `ProviderSmokeSafeModeStaticGuardTest.php`, and `ProductionValidationRuntimeProofStaticGuardTest.php`.
- `vendor/bin/phpunit tests/Unit/MarketData/ProviderSmokeSafeModeStaticGuardTest.php` -> OK (5 tests, 163 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData/ProductionValidationRuntimeProofStaticGuardTest.php --filter "runtime_parity"` -> OK (2 tests, 259 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData --filter "ProviderSmoke"` -> OK (5 tests, 163 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData` -> OK (492 tests, 7588 assertions), Time 00:17.316, Memory 40.00 MB.

[REMAINING_RISK]
- Yahoo/PublicApi can still legitimately return 429, timeout, network, parse, empty-response, or missing-date outcomes in future runs; those outcomes must remain reason-coded and must not be silently counted as provider PASS.

---


<!-- LEGACY_EXTRACT_BODY_END -->

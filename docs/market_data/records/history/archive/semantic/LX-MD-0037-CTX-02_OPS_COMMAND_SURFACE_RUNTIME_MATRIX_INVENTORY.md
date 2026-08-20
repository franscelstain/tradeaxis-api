# Legacy Semantic Extract — LX-MD-0037-CTX-02

- Source ID: `LS-MD-0037`
- Original path: `audit/OPS_COMMAND_SURFACE_RUNTIME_MATRIX_INVENTORY.md`
- Original SHA1: `D6E40A3FC4141C4D0798627BD21A5F34418206F8`
- Extract role: `CONTEXT`
- Source range: `L74-L115`
- Extract body SHA1: `8D1CC5B28E685BDFD304C675CB009E0730D00194`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Provider-Smoke Safe-Mode Overlay

`market-data:provider:smoke` is part of the current public command surface. Including `market-data:backfill:lifecycle`, `market-data:evidence-replay:full-range-current`, `market-data:sectors:import-memberships`, `market-data:sector-indexes:import-bars`, `market-data:sector-indexes:ingest-api`, `market-data:events:import-corporate-actions`, `market-data:events:import-trading-status`, and `market-data:backfill:missing-tickers` brings the current command count to 30. The provider overlay is intentionally separated from the 2026-05-20 seeded fixture matrix because live provider behavior is environment/upstream dependent; the full-range evidence/replay extension is proof-only and uses existing current publications, sector/event imports only write source, membership, or benchmark/source rows after CSV/API validation and explicit apply, and missing-ticker backfill enters the normal lifecycle only for current bar gaps.

Current proof from this reconciliation:

```text
php artisan list market-data -> 30 public market-data commands registered
php artisan market-data:provider:smoke --help -> exit 0
php artisan market-data:backfill:lifecycle --help -> exit 0
php artisan market-data:backfill:missing-tickers --help -> exit 0
php artisan market-data:evidence-replay:full-range-current --help -> exit 0
php artisan market-data:sector-indexes:ingest-api --help -> exit 0
php artisan market-data:sector-indexes:import-bars --help -> exit 0
php artisan market-data:sectors:import-memberships --help -> exit 0
php artisan market-data:events:import-corporate-actions --help -> exit 0
php artisan market-data:events:import-trading-status --help -> exit 0
php artisan market-data:provider:smoke -> exit 1, provider_smoke_status=BLOCKED, reason_code=PROVIDER_SMOKE_TICKER_REQUIRED
php artisan market-data:provider:smoke --ticker=BBCA --trade_date=2026-05-20 --dry-run --retry-max=0 -> exit 0, provider_smoke_status=PASS, reason_code=PROVIDER_SMOKE_OK, http_status=200, returned_row_count=1, retry_exhausted=false
```

Provider smoke safe-mode invariants and final proof:

- `provider_smoke_status=PASS`
- `reason_code=PROVIDER_SMOKE_OK`
- `source_reason_code=none`
- `http_status=200`
- `returned_row_count=1`
- `attempt_count=1`
- `retry_max=0`
- `retry_exhausted=false`
- `adapter_reason_code=PROVIDER_SMOKE_OK`
- `request_url=https://query1.finance.yahoo.com/v8/finance/chart/BBCA.JK?interval=1d&range=10d&includePrePost=false&events=div%2Csplits&corsDomain=finance.yahoo.com`
- `publication_created=false`
- `seal_executed=false`
- `finalize_executed=false`
- `pointer_switched=false`
- `readable_publication_created=false`
- `full_universe_fetch=false`

Decision: command surface coverage is current at 21 commands and final provider smoke runtime proof is PASS. `PROVIDER_RATE_LIMITED`, `PROVIDER_TIMEOUT`, and `PROVIDER_NETWORK_ERROR` remain BLOCKED outcomes for future runs, but the current final artifact records `provider_smoke_status=PASS`, `reason_code=PROVIDER_SMOKE_OK`, and `http_status=200`.


<!-- LEGACY_EXTRACT_BODY_END -->

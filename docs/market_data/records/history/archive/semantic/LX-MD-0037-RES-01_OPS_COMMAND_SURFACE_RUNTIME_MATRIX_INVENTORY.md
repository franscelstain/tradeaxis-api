# Legacy Semantic Extract — LX-MD-0037-RES-01

- Source ID: `LS-MD-0037`
- Original path: `audit/OPS_COMMAND_SURFACE_RUNTIME_MATRIX_INVENTORY.md`
- Original SHA1: `D6E40A3FC4141C4D0798627BD21A5F34418206F8`
- Extract role: `RESEARCH`
- Source range: `L295-L324`
- Extract body SHA1: `A1AAA6F40337FA25D6B59AA5BE39C69CA06F94F0`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## 2026-05-22 Provider Smoke PASS Reconciliation

Status: DONE.

This reconciliation updates the ops command surface runtime matrix after the final provider-smoke proof was rerun successfully.

Final provider-smoke evidence:

```text
php artisan market-data:provider:smoke --ticker=BBCA --trade_date=2026-05-20 --dry-run --retry-max=0
provider_smoke_status=PASS
reason_code=PROVIDER_SMOKE_OK
source_reason_code=none
http_status=200
returned_row_count=1
attempt_count=1
retry_max=0
retry_exhausted=false
publication_created=false
seal_executed=false
finalize_executed=false
pointer_switched=false
readable_publication_created=false
full_universe_fetch=false
```

Artifact: `storage/app/market-data/provider-smoke-safe-mode/command-output/provider-smoke-bbca.txt`.

Decision: the prior provider-smoke overlay text that described the live proof as `BLOCKED` / `PROVIDER_RATE_LIMITED` is superseded by the final `PASS` / `PROVIDER_SMOKE_OK` artifact. Future provider rate-limit, timeout, network, parse, empty-response, or missing-date outcomes remain valid BLOCKED outcomes, but they are not the current final proof state.


<!-- LEGACY_EXTRACT_BODY_END -->

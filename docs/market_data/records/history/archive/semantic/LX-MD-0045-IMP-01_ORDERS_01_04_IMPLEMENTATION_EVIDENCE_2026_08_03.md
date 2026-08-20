# Legacy Semantic Extract — LX-MD-0045-IMP-01

- Source ID: `LS-MD-0045`
- Original path: `audit/reports/ORDERS_01_04_IMPLEMENTATION_EVIDENCE_2026-08-03.md`
- Original SHA1: `9EC579890FB2FB4256FE012CF3BD1CF68D55E90E`
- Extract role: `IMPLEMENTATION`
- Source range: `L21-L64`
- Extract body SHA1: `4CDA6BD175864314B65F9A3FADF17A9FB51F85A8`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Implemented result

### Scope and time boundary

- canonical runtime scope is fail-closed to `IDX`, `REGULAR`, `EOD`, `Asia/Jakarta`;
- intentional dataset start is `2023-01-02` and requests before it are rejected;
- `OPERATIONAL_START_DATE` is independent and nullable;
- absence of activation produces `DEVELOPMENT_NOT_OPERATIONAL`, not a false freshness incident or production-readiness claim;
- `RAW`, `STRUCTURAL_ADJUSTED`, and `TOTAL_RETURN` remain explicit product identities.

### Domain boundary

- market-data application services no longer expose watchlist- or portfolio-named artifacts;
- the publication-bound surface is `market_data_read_product_v1`;
- the read product exposes both usable and unusable rows and does not filter current `is_active`, require a watchlist indicator subset, rank, or select candidates;
- price reads explicitly expose `raw_close` and nullable `provider_adjusted_close_evidence` instead of silently replacing missing adjusted close with raw close.

### Yahoo bootstrap and provider-neutral boundary

- application services depend on `ApiEodBarsSource` / `ManualEodBarsSource`, not the Yahoo adapter class;
- Yahoo-specific URL, schema, symbol suffix, and response behavior remain inside `PublicApiEodBarsAdapter` and temporal provider mapping;
- adapter capability output states that Yahoo phase data does not provide actual traded value, official board/status, authoritative corporate actions, or point-in-time identity without internal mapping;
- a missing Yahoo `adjclose` remains `null`; it is never patched with `close` and is forbidden as a canonical price basis;
- current Yahoo use remains the explicit bootstrap/free-source phase. Paid-provider procurement remains deferred and is not an implementation requirement for this phase.

### Immutable acquisition observations

- HTTP response/file bytes are captured before parsing;
- capture records include run/date/range/source/provider/provider symbol/mapping/config, sanitized request identity, response state, content type, adapter/schema version, payload hash/size/bounded redacted body, and timestamps;
- validation outcome is appended as a child observation; the capture row is never updated;
- successful canonical bars require a persisted `ACCEPTED` observation, temporal listing, config snapshot, completed Regular-Market session, source/acquisition timestamps, canonicalization version, `RAW` product identity, and quality state;
- empty, malformed, HTTP failure, transport failure, missing file, rejected response, and observation-persistence failure paths fail closed with explicit evidence/reason state;
- run/publication observation manifests bind provenance separately from canonical content hashes;
- identical canonical re-acquisition remains an unchanged correction even when observation IDs/acquisition timestamps differ.

### Required dependency foundations

- deterministic secret-redacted configuration snapshots are bound to run, bars, and publication;
- issuer/instrument/listing/exchange-symbol/provider-symbol identities are separated and effective-dated;
- historical universe no longer filters current `tickers.is_active`;
- calendar state uses immutable revisions and appends `SCHEDULED -> COMPLETED` rather than freezing a pre-close state;
- missing trading-status evidence remains `UNKNOWN`; conflicting verified revisions remain `CONFLICTING`;
- correction/copy paths preserve bar lineage fields.


<!-- LEGACY_EXTRACT_BODY_END -->

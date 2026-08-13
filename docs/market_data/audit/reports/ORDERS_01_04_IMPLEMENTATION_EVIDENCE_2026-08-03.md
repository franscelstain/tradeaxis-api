# Orders 01-04 Implementation Evidence

> **HISTORICAL AUDIT/IMPLEMENTATION EVIDENCE — NON-AUTHORITATIVE FOR CURRENT V2 STRATEGY.** This file preserves dated runtime/inventory facts and may contain legacy field names, command behavior, locks, or production claims from earlier contracts. Current strategy authority is the owner contracts + Blueprint + Conformance Matrix; current execution/conformance state is `MARKET_DATA_IMPLEMENTATION_LEDGER.md`; current audit verdict is `reports/AUDIT_FINAL_STATE.md`. Legacy statements are not current requirements unless explicitly re-admitted by those authorities.


Date: `2026-08-03`  
Scope: implementation of document-by-document strategy update orders 1-4  
Verdict: `PROVEN_IN_TEST_ENVIRONMENT`  
Operational verdict: `NOT_CLAIMED`

## Owner contracts implemented

1. `book/Terminology_and_Scope.md`
2. `book/Domain_Boundary_Invariants_LOCKED.md`
3. `book/Yahoo_Finance_Bootstrap_Source_Strategy.md`
4. `book/Source_Data_Acquisition_Contract_LOCKED.md`
5. Dependency contracts required to avoid false implementation: source mapping, temporal identity/provider mapping, calendar/session, trading status, immutable config, reason registry, and schema/test mirror.

The additional dependency foundations do not claim later analytical, coverage, factor, replay, operational, or production work orders complete. They exist because an immutable observation cannot truthfully bind provider symbol, session, listing, or config identities that do not yet exist.

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

## Fixed obligations carried by orders 1-4

| Obligation | Evidence |
|---|---|
| Config keys registered | `config/market_data.php`, `.env.example`, and `.env.testing` are synchronized by executable governance test. |
| Schema targets declared | additive migration `2026_08_03_000001_harden_market_data_orders_1_to_4.php` plus the SQLite production-shape mirror. |
| Rejected tests retired in the same implementation | price-derived corporate-action synthesis and in-place price/history repair tests were superseded by non-mutation/fail-closed proofs. |
| Capability limits explicit | provider, corporate-action detector, in-place repair compatibility surface, calendar, identity, and status paths expose or enforce their blind spots/fail-safe states. |

## Proof

Commands executed from repository root:

```text
php -l <all changed and added PHP files>
PHP lint PASS: 44 files

php vendor/phpunit/phpunit/phpunit
OK (1158 tests, 8774 assertions)
Time: 02:11.613
```

High-value semantic proofs include:

- intentional start/activation separation and canonical-scope rejection;
- deterministic/redacted config snapshots;
- inactive-now but historically listed universe membership;
- explicit temporal provider symbol mapping;
- as-known trading status and unknown fail-safe behavior;
- append-only source capture/outcome plus redaction/manifest binding;
- fail-closed observation persistence;
- missing `adj_close` remains null;
- completed-session transition appends a calendar revision;
- production-path run -> observation -> listing/config -> canonical bar -> publication lineage;
- consumer-neutral read product does not screen strategy candidates;
- no synthetic event/factor and no in-place history mutation from price geometry.

## Claims deliberately not made

- no live Yahoo availability/SLA/licensing validation was performed;
- no MariaDB production upgrade, rollback, backfill, or enforcement rehearsal was performed;
- no deployed scheduler, alert, operational freshness, or consecutive-session SLO proof was performed;
- no watchlist policy, ranking, profitability, or trading-strategy quality was implemented or used as acceptance evidence;
- no `IMPLEMENTATION_CONFORMANT`, `PRODUCTION_READY`, or `OPERATIONALLY_VALIDATED` relock is created by this implementation report;
- `MARKET_DATA_IMPLEMENTATION_LEDGER.md` is not advanced because its `MD-RUN W00...` sequential audit protocol was not invoked and later work-order conformance has not been independently audited.

The correct next activity after this implementation is an independent audit of orders 1-4 against this evidence. It must not infer completion of orders 5 onward.

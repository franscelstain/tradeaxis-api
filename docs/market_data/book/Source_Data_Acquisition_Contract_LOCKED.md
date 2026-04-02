# Source Data Acquisition Contract (API or Manual)

## Purpose
Lock acquisition behavior so outputs stay deterministic, auditable, and easy to implement whether data comes from public/free APIs or manual input.

## Source adapter (LOCKED)
Implementation must normalize every source through one adapter contract:
- `fetchOrLoadEodBars(trade_date, ticker_codes[]) -> source_bars[]`

Allowed source modes:
- public/free API pull
- local CSV/JSON import
- manual entry/import prepared in a controlled format

## Current selected default operating mode (LOCKED FOR ACTIVE CODEBASE)
For the active market-data codebase, the selected default acquisition mode is:
- `source_mode=api`
- default provider = `yahoo_finance`
- IDX ticker requests append suffix `.JK` before provider fetch
- `manual_file` remains a valid controlled fallback mode for local recovery, deterministic replay support, and operator-led ingestion when API mode is unavailable

This is a sanctioned operating-model choice for the active codebase, not an implementation drift. The downstream canonicalization contract remains source-mode agnostic after normalization.

The downstream canonicalization contract must not care which source mode produced the row once it has been normalized.

## Minimum normalized source fields (LOCKED)
Every normalized source row must provide:
- `ticker_code`
- `trade_date` (`YYYY-MM-DD`)
- `open`
- `high`
- `low`
- `close`
- `volume`
- `adj_close` (nullable)
- `source_name`
- `source_row_ref` (nullable but recommended)
- `captured_at`

## Request/load rules (LOCKED)
- bounded concurrency for API mode
- throttle + jitter between requests when API mode is used
- retry with backoff on transient API errors when API mode is used
- manual-file mode must validate schema before rows are accepted
- every acquisition stage must record source mode and source name in run telemetry
- for the active default `yahoo_finance` provider path, ingestion resolves the active ticker universe first, then fetches provider payloads per ticker symbol before normalization into canonical source rows
- for the active default `yahoo_finance` provider path, EOD requests use daily interval semantics and provider-specific symbol mapping must stay inside the source adapter, not leak into downstream canonicalization

## Failure classification (LOCKED)
- transient API error: retry, then `HELD` if unresolved
- source format change / parsing failure: `FAILED`
- manual file schema mismatch: `FAILED`
- partial source coverage: allowed only if coverage >= `COVERAGE_MIN`, else `HELD`

## Parsing + mapping (LOCKED)
Map normalized source OHLCV into internal canonical bars as defined by `EOD_Bars_Contract.md`.

## Audit recording (LOCKED)
- create/update `eod_runs` per stage
- each `eod_bars` row records `source`, `ingested_at`, and `run_id`
- source mode used for a run must be visible in logs or run notes
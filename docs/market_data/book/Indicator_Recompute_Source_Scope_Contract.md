# Indicator Recompute Source Scope Contract (LOCKED)

## Purpose
This contract removes ambiguity around the phrase "recompute/update indicators without updating sector, corporate action, trading status, or master data".

The locked meaning is:
- source/master tables are read-only
- publication-bound indicator artifacts may be regenerated from the already-existing source/master data
- no source import, provider fetch, or master upsert is implied

## Source/master read-only rule
A recompute-only indicator operation must not write to these source/master tables:

- `tickers`
- ticker-sector membership tables
- sector/sector-index master tables
- `market_benchmark_bars`
- `market_benchmark_indicators`, unless a future explicitly scoped benchmark-only recompute command is introduced
- `market_data_corporate_actions`
- `market_data_trading_status_events`
- `eod_bars`

It must not run source/import commands such as sector membership import, sector-index import/API ingest, corporate-action import, trading-status import, missing-ticker source acquisition, API OHLCV acquisition, or manual-file OHLCV ingest.

## Publication-bound recompute rule
Publication-bound rows may be regenerated into a new candidate/current publication artifact. This includes technical indicators and publication-bound source context fields such as:

- `sector_code`
- `sector_roc20`
- `rs_20_vs_sector`
- `sector_rs_20_vs_ihsg`
- `corporate_action_flag`
- `corporate_action_types`
- `trading_status_code`
- `is_suspended`
- `is_uma`
- `event_risk_flag`
- `event_risk_reasons`

Those fields must be derived only from existing current bars and existing source/master data. If source/master data already changed before the recompute operation, the new publication may reflect those existing source facts. That is not a source/master update.

## Technical-only mode boundary
If an operator wants to recompute only numeric technical indicators while freezing sector/corporate/trading-status/event context exactly as it was in the prior current publication, that must be an explicit future mode such as `--technical-only`.

Such a mode does not currently exist as an accepted production command. It must preserve publication context fields from the prior current publication rather than re-resolving them from source tables.

## Current command surface rule
`market-data:eod-indicators:recompute-current` is the implemented replacement command for recomputing publication-bound indicators from existing current readable bars without source acquisition, bar ingest, source/master writes, or `eod_bars` writes.

The invalid command `market-data:eod-indicators:republish-current` remains removed after operator runtime proved it failed the seal/hash lifecycle and did not satisfy this contract.

The approved recompute command uses correction-current lifecycle: it snapshots existing current bars into candidate history, recomputes indicators and eligibility, hashes, seals, finalizes, and switches the current pointer only when validation passes.

## Non-error indicator rule
Indicator nullability remains per field. Missing/insufficient dependencies such as MA20, MA50, ROC20, ATR14, sector benchmark history, or zero-placeholder OHLCV must produce NULL only for affected fields and must not fail the whole publication date.

## Runtime lock evidence

The approved command is runtime-proven for `2023-01-02` through `2026-06-04`:

- full MarketData PHPUnit: 640 tests / 9539 assertions PASS;
- latest docs-review `vendor\bin\phpunit`: 641 tests / 9547 assertions PASS on 2026-06-08;
- recompute runtime: 807 processed, 807 success, 0 failed/skipped, `all_passed=1`;
- source acquisition, bar ingest, source/master writes, and `eod_bars` writes remained false;
- 757 replacement publications used run evidence and 50 unchanged/preserved-current outcomes used correction evidence; all 807 evidence exports were `ADMITTED_COMPLETE`.
- final current evidence/replay: 807 processed, 807 success, 0 failures/errors/mismatches, all MATCH/PASS.

This proof locks the read/write interpretation: publication/history/evidence artifacts may be written, but source/master/OHLCV tables are not updated by recompute-current.

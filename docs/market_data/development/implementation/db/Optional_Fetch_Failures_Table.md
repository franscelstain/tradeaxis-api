# Optional Fetch Failures Table

## Purpose
Define an optional per-listing audit table for source-acquisition failures that occur during upstream data collection for trade date D.

This table is optional.
It exists to improve row-level explainability and diagnostics when some tickers fail source retrieval after retry exhaustion.

It is not a substitute for:
- run-level terminal status
- eligibility snapshot
- reason-code registry
- run events

## When to use
Use this optional table when the implementation wants to preserve per-listing/source-symbol fetch-failure evidence such as:
- source timeout after retries
- source rate-limit exhaustion
- malformed per-ticker payload
- listing/source-symbol-scoped source response failure

## Minimum semantic role
If this table is implemented:
- it records listing/source-symbol-scoped source retrieval failure evidence
- it may feed data-usability blocking decisions for the affected listing
- it must remain auditable and append-only for the relevant run context

## Recommended columns
Minimum recommended fields:
- `trade_date`
- stable `listing_id` when temporal mapping resolved; nullable only when failure occurred before symbol→listing resolution
- source symbol/provider mapping reference when `listing_id` is unresolved
- optional compatibility/display `ticker_id`
- `run_id`
- `source_observation_id` / acquisition-attempt identity where available
- `source`
- `failure_reason_code`
- `failure_note`
- `retry_count`
- `created_at`

## Reason-code rule (LOCKED)
If this table is implemented and a listing cannot be safely produced because source acquisition failed, then the publication-bound data-usability/eligibility projection for that listing/date may use:
- `ELIG_FETCH_FAILURE`

This code must exist in the official reason-code registry and seed.

If the implementation chooses not to support `ELIG_FETCH_FAILURE`, then eligibility must still map to another valid registered blocking reason based on the artifact that ended up missing or invalid.

## Non-blocking-table rule
This table itself is optional.
Its absence must not break the rest of the market-data contracts.

However, if the table is implemented, its semantics must be consistent with:
- reason-code registry
- eligibility snapshot contract
- partial-data eligibility behavior

## Run-level distinction
Per-listing fetch failures do not automatically force run-level `FAILED`.

Run-level terminal status still depends on:
- coverage thresholds
- required artifact existence
- quality gates
- hash/seal/finalization rules

Therefore:
- some per-listing fetch failures may coexist with terminal `SUCCESS`
- broader fetch failure patterns may lead to `HELD` or `FAILED`

## Anti-ambiguity rule
Do not use this optional table as a hidden alternative to eligibility.
If the table is present, blocked listing data-usability must still be reflected explicitly in the eligibility snapshot.
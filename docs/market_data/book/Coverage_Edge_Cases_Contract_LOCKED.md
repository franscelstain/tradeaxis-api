# Coverage Edge Cases Contract LOCKED

Status: LOCKED  
Owner: Market Data EOD Coverage / Finalize / Source Acquisition  
Scope: multi-source, partial dataset, delayed data, retry window, stale data, fallback behavior.

## 1. Core Rule

Coverage Gate remains absolute.

A requested-date publication may become `SUCCESS + READABLE` only when the locked coverage gate returns `PASS` and all other finalize preconditions pass.

Edge cases must never downgrade, bypass, override, or reinterpret the coverage threshold.

## 2. Multi-Source Rule

One run has one primary source identity.

The system must not mix rows from multiple `source_name` values inside a single run unless a separate locked source-combination contract exists.

Current locked behavior:

- there is no active env/config key that permits multi-source row mixing
- `MARKET_DATA_MULTI_SOURCE_MODE` and `MARKET_DATA_ALLOW_MIXED_SOURCES` are pruned as unused/stale config surfaces
- mixed source rows inside one ingest boundary are rejected
- source fallback is not row-level mixing
- any provider/source fallback must start a clean run or use a future explicitly locked source-fallback contract

## 3. Partial Dataset Rule

Partial data is not complete data.

If the expected universe is 900 and only 400 requested-date canonical valid bars are available:

- coverage is still evaluated
- coverage returns `FAIL`
- publication must remain `NOT_READABLE`
- final reason must identify the edge as `RUN_PARTIAL_DATA` when partial counts are available
- fallback may only point to a previous `READABLE` publication

Coverage-failing partial data must not be promoted as requested-date current readable publication. A bounded gap at or above the locked `0.98` threshold follows `EOD_COVERAGE_GATE_CONTRACT_LOCKED.md`: missing listings stay explicit and all independent hard gates must still pass.

## 4. Delayed Data Rule

Delayed data is a controlled HOLD condition only while the configured delay window is still open.

Config:

- `MARKET_DATA_COVERAGE_DELAY_WINDOW_MINUTES`
- default: `60`

Behavior:

- before cutoff: finalize is blocked by cutoff policy
- after cutoff but inside delay window: coverage `FAIL` may produce `HELD + NOT_READABLE + RUN_DATA_DELAYED`
- after delay window expires: coverage `FAIL` is no longer treated as delayed and must finalize as `FAILED` or `HELD` only if deterministic previous readable fallback exists

Delayed data must not be counted as available unless requested-date canonical valid bars exist.

## 5. Retry Window Rule

Retry is source acquisition behavior, not coverage bypass behavior.

Retry is allowed only before finalize and must be deterministic through:

- `MARKET_DATA_API_RETRY_MAX`
- `MARKET_DATA_API_BACKOFF_MS`
- `MARKET_DATA_API_THROTTLE_QPS`
- per-attempt telemetry
- final reason code telemetry

After retry is exhausted, the run must continue into the normal decision path:

- source unavailable with fallback: `HELD + NOT_READABLE`
- source unavailable without fallback: `HELD + NOT_READABLE`
- no automatic READABLE state is allowed from retry exhaustion

## 6. Stale Data Rule

Rows outside the requested `trade_date` are stale for the requested publication.

Stale data:

- must not count as available coverage
- must not be normalized into requested-date availability
- must produce `RUN_STALE_DATA` when detected at single-day ingest boundary
- must keep publication `NOT_READABLE`

## 7. Reason Code Contract

Required edge reason codes:

- `RUN_COVERAGE_LOW`
- `RUN_SOURCE_RATE_LIMIT`
- `RUN_SOURCE_TIMEOUT`
- `RUN_DATA_DELAYED`
- `RUN_PARTIAL_DATA`
- `RUN_STALE_DATA`

Reason-code meaning:

| code | meaning |
|---|---|
| `RUN_COVERAGE_LOW` | coverage failed without a more specific edge classification |
| `RUN_PARTIAL_DATA` | some requested-date canonical bars exist, but below locked coverage threshold |
| `RUN_DATA_DELAYED` | coverage failed while delay window is still open |
| `RUN_STALE_DATA` | source rows do not belong to requested trade date |
| `RUN_SOURCE_RATE_LIMIT` | source acquisition hit rate limit after retry policy |
| `RUN_SOURCE_TIMEOUT` | source acquisition timed out after retry policy |

## 8. Finalize Behavior

| condition | terminal_status | publishability_state | promotion |
|---|---:|---:|---:|
| coverage `PASS` + sealed + cutoff satisfied | `SUCCESS` | `READABLE` | allowed |
| coverage `FAIL` + partial data | `FAILED` or `HELD` with previous readable fallback | `NOT_READABLE` | blocked |
| coverage `FAIL` + delayed within window | `HELD` | `NOT_READABLE` | blocked |
| coverage `FAIL` + delayed expired | `FAILED` or `HELD` with previous readable fallback | `NOT_READABLE` | blocked |
| stale data detected | `FAILED`/stage failure or held by source failure path | `NOT_READABLE` | blocked |
| source timeout/rate-limit after retry | `HELD` | `NOT_READABLE` | blocked |

## 9. Fallback Rule

Fallback is deterministic and read-side safe.

Allowed fallback target:

- previous `READABLE` publication only

Forbidden fallback target:

- partial candidate
- stale candidate
- unsealed candidate
- non-readable candidate
- raw/staging data
- `MAX(date)` shortcut

## 10. Implementation Mapping

Runtime mapping:

- `CoverageGateEvaluator` calculates expected vs available requested-date canonical valid bars.
- `FinalizeDecisionService` maps coverage fail edge conditions to explicit final reason codes.
- `MarketDataPipelineService` detects delayed-window and partial-data edge context before finalize decision.
- `EodBarsIngestService` rejects stale single-day source rows via `RUN_STALE_DATA`.
- `PublicApiEodBarsAdapter` keeps retry telemetry deterministic and before finalize.
- `Reason_Codes_Seed.sql` registers edge reason codes.

## 11. Non-Negotiable Invariant

No edge case may produce `READABLE` unless coverage is `PASS`.

## 12. Dormancy, zero-volume, and provider-outage correction (LOCKED)

Dormancy and zero-volume history are factual liquidity/activity observations, not proof that a bar was not expected and not automatic failures of upstream data usability. They remain in the coverage denominator unless verified temporal calendar/listing/status evidence says `NOT_EXPECTED` for the full Regular-Market session.

A provider that stops serving an otherwise valid listing must continue increasing missing delivery; no elapsed-day threshold may hide it. A valid zero-volume canonical bar is delivered coverage and remains explicit for downstream tradability policy. A missing row is not delivered even when the instrument was historically illiquid.

Coverage edge handling must expose expectation, delivery, canonical validity, quality, liquidity, and eligibility as separate counts/states.

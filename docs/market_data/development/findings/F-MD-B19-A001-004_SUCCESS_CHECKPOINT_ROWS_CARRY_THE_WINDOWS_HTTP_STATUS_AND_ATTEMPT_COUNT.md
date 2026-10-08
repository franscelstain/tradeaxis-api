# Finding — `F-MD-B19-A001-004`

- ID: `F-MD-B19-A001-004`
- Stage / Attempt / Baseline: `MD-B19` / `MD-B19-A001` / `MD-B19-A001-BL001`
- Raised at: 2026-10-08T08:04:28+07:00
- Severity: `P3`
- Status: `RESOLVED`
- Class: `AUTHORITY_AMBIGUITY_WITH_MEASURED_CROSS_IDENTITY_VALUE`
- Blocked: the per-predicate proof basis of `MD-S053-R0210` only (now established).
- Blocks strategy change: `NO`

## Statement

`Source_Data_Acquisition_Contract_LOCKED.md` (`MD-S053`), "Failure telemetry isolation":

> - failed checkpoint `reason_code`, `http_status`, `error_sample`, `provider_error_sample`, `sanitized_url`, `failure_scope`, `attempt_count`, and `rows_count` must come from the same checkpoint identity
> - timeout/non-HTTP failure must not inherit HTTP status or provider body from a different ticker
> - successful checkpoint rows must not carry stale failure sample fields

For a **failed** row all of this holds (measured and now guarded). For a **successful** row,
`ApiBackfillRangeAcquisitionService::buildWindowCheckpoints()` sets

```
'http_status'   => $telemetry['final_http_status'] ?? $telemetry['http_status']   // window-level
'attempt_count' => $telemetry['attempt_count']                                    // window total
```

Measured with three tickers in one window (`BBCA` succeeds, `TTTT` times out and is retried twice,
`ZZ400` is rejected with HTTP 400, `api_retry_max = 2`):

| Row | state | `http_status` | `attempt_count` |
|---|---|---|---|
| `BBCA` | `SUCCESS` | **400** | **5** |
| `TTTT` | `FAILED` | null | 3 |
| `ZZ400` | `FAILED` | 400 | 1 |

The successful `BBCA` row shows the HTTP 400 of a different ticker and the request count of the whole window.
Whether the window's last status lands on the success row depends on iteration order.

## Why this is an authority question and not yet a defect

`R0210` forbids stale failure **sample** fields on a success row. The first four fields of the list above are
named by `R0208` as failure fields; `http_status` is a status, not a sample, and `attempt_count` is a count. The
section is titled *isolation*, which supports reading `R0210` strictly (no failure telemetry of another identity
may appear on a success row). The text does not say what a success row *should* carry in those two fields, so
any fix chooses a value the contract does not state.

## Options

- **A — strict (recommended).** A success row carries only facts of its own request. `http_status` is the
  status of its own successful response (2xx) and `attempt_count` its own attempts. Needs per-ticker success
  telemetry from `PublicApiEodBarsAdapter` and a change in `buildWindowCheckpoints()`: production change in two
  frozen-build files, batched under `D-MD-B19-A001-001`.
- **B — lenient.** Success rows may carry window-level aggregates; `R0210` is limited to the sample and
  scope fields (`error_sample`, `provider_error_sample`, `sanitized_url`, `failure_scope`, `reason_code`). No
  production change; the current behaviour is recorded as intended and the window-level meaning is documented.

## Effect on the proof

`MD-S053-R0210` has **no** entry in `MarketDataOperationsProofBasis` until this is decided. What is guarded today:
`reason_code`, `error_sample`, `provider_error_sample`, `sanitized_url`, `failure_scope`, `date_level_reason_code`,
`missing_trade_dates` are null/empty on a success row, in both orders of the failing neighbours, with five
single-protection mutations red (one per field family). The two contested fields are deliberately **not** asserted, so that no test
blesses either reading.

## Related

- `E-MD-B19-A001-003` (where this was measured), `F-MD-B19-A001-002`, `MD-S053-R0208`, `MD-S053-R0209`, `MD-S053-R0210`

## 2026-10-08T08:53:57+07:00 Owner decision recorded: Option A (`D-MD-B19-A001-002`)

The project owner selected **Option A**: for a checkpoint row identified by `window_start`, `window_end`, `ticker_code`, all per-execution request-result fields come from that same ticker execution; a SUCCESS row's `http_status` is the status of its own request and its `attempt_count` the number of attempts of its own request; neither inherits from another ticker or carries a window aggregate (window aggregates stay in window-scoped telemetry). The production change is governed under `F-MD-B19-A001-003` Option O1.

## 2026-10-08T10:53:24+07:00 Resolved under Option A (`E-MD-B19-A001-004`)

Root cause: `buildWindowCheckpoints()` read the success row's `http_status` and the `attempt_count` of every row without a failure context from the window telemetry entry, which aggregates all tickers of the window; the per-ticker request results were not retained. Fix: `PublicApiEodBarsAdapter::buildYahooRangeAggregateTelemetry()` now exposes `ticker_request_telemetry` (each ticker's own `attempt_count` and `final_http_status`), and `buildWindowCheckpoints()` reads those; the window aggregates stay on the window telemetry. Proven by five guards and eight single-protection mutations; `MD-S053-R0210` is the thirteenth reviewed predicate of the family. This was the **first executable-build change** under O1 (`PublicApiEodBarsAdapter.php`, 2026-10-08T08:57:11+07:00).

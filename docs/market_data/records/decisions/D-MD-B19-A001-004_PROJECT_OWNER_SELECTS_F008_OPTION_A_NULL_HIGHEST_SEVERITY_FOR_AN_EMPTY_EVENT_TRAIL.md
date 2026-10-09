# Decision — project owner selects F-MD-B19-A001-008 Option A for `run_event_summary.json`

- ID: `D-MD-B19-A001-004`
- Verification epoch: `MD-REBASELINE-20260820-001`
- Stage / Attempt / Baseline: `MD-B19` / `MD-B19-A001` / `MD-B19-A001-BL001`
- Decides: `F-MD-B19-A001-008` (Option A); the held predicates `MD-S075-R0121` and `MD-S075-R0128`
- Governed under: `F-MD-B19-A001-003` Option O1 (`D-MD-B19-A001-001`); `MD-DEP-0025`
- Decisions relied on: `D-MD-B19-A001-001`
- Evidence relied on: `E-MD-B19-A001-008`, `E-MD-B19-A001-009` (the analysis and the options)
- Change impact: `CI-MD-B19-A001-001` (amendment issued with the implementation)
- Issued: 2026-10-09T08:25:51+07:00
- Status: `APPROVED` — project owner, in the coordination instruction "CONTINUE MD-B19-A001 — RESOLVE F-008 OWNER DECISION OPTION A"
- Mutability: `IMMUTABLE_AFTER_ISSUE`
- Strategy impact: `NONE`; `MD-STRATEGY-FREEZE-20261005-001` unchanged

## Owner decision (as supplied)

`F-MD-B19-A001-008 — OPTION A`.

Approved semantics: when a run has zero `eod_run_events`, its `run_event_summary.json` field `highest_severity` must
be JSON `null`, not `"INFO"`. For non-empty event trails, `highest_severity` must remain derived from the actual
recorded event severities according to existing authority.

## Precise semantics recorded

- **Zero events:** `highest_severity` is JSON `null`. `event_count` stays `0`; `first_event_time`,
  `last_event_time`, `first_event_type` and `last_event_type` stay `null`; `stage_counts` and `reason_code_counts`
  stay `{}`.
- **One or more events:** `highest_severity` is the maximum severity of that run's events on the existing ordering
  `INFO` < `WARN` < `ERROR`; an `ERROR` is never presented as lower (`MD-S075-R0130`). Unchanged.
- **No other behaviour changes:** event isolation by run, the deterministic `event_time`, `event_id` order and the
  `{}` shape of empty maps are unchanged.

## Predicates affected

- `MD-S075-R0121` (`highest_severity`): the empty-trail value is now defined (`null`).
- `MD-S075-R0128` (derivable from the trail; nothing invented): satisfied for the severity of an empty trail, because
  no severity is reported where none was observed.
- Not affected: `R0129`, `R0130`.

## Conflict check

The locked text (`MD-S075` section 3), the Observability Minimum Contract, the Determinism Invariants and the executed
bundle example state no empty-trail value; the example `"INFO"` is for a 17-event trail. Option A therefore fills a
gap the authority leaves open and conflicts with no locked contract. No strategy file is amended.

## What this record does and does not do

- It selects Option A and defines the empty-trail value. It does not change the severity ordering, the tie order, or any
  other predicate.
- It does not create candidate-v6, does not resolve `MD-DEP-0025`, and does not edit candidate-v5, any `MD-B18`
  record, `E-MD-B19-A001-008` or `E-MD-B19-A001-009`.
- The implementation, its proof and the re-admission of `R0121` and `R0128` are recorded in the successor evidence.

# Decision — project owner selects Option A for F-MD-B19-A001-004: a checkpoint row carries only the request-result values of its own ticker execution

- ID: `D-MD-B19-A001-002`
- Verification epoch: `MD-REBASELINE-20260820-001`
- Stage / Attempt / Baseline: `MD-B19` / `MD-B19-A001` / `MD-B19-A001-BL001`
- Decides: `F-MD-B19-A001-004` (Option A); the held predicate `MD-S053-R0210`
- Governed under: `F-MD-B19-A001-003` Option O1 (`D-MD-B19-A001-001`); `MD-DEP-0025`
- Decisions relied on: `D-MD-B19-A001-001`, `D-MD-B18-A002-018` (item 13)
- Evidence relied on: `E-MD-B19-A001-003` (the measurement)
- Change impact: `CI-MD-B19-A001-001` (amendment issued with the implementation)
- Issued: 2026-10-08T08:53:57+07:00
- Status: `APPROVED` — project owner, in the 2026-10-08 instruction "CONTINUE MD-B19-A001 — COMPLETE R0210 UNDER OWNER OPTION A"
- Mutability: `IMMUTABLE_AFTER_ISSUE`
- Strategy impact: `NONE`; `MD-STRATEGY-FREEZE-20261005-001` unchanged

## Owner decision (as supplied)

F-MD-B19-A001-004 Option A.

Semantic rule:

For a checkpoint row identified by:

- window_start
- window_end
- ticker_code

all per-execution request-result fields must come from that SAME ticker / execution identity.

For a SUCCESS row:

- http_status = HTTP status of that ticker's own request;
- attempt_count = number of attempts for that ticker's own request;
- neither may inherit values from another ticker;
- neither may carry window-level aggregate values.

Window-level aggregates, if needed, belong in window-scoped telemetry/summary, not in the per-ticker checkpoint row.

Any production change required by this decision is governed under: F-MD-B19-A001-003 Option O1.

Candidate-v5 remains immutable. The first executable-build change makes candidate-v5 historical-only for the current build and activates MD-DEP-0025 successor-pending semantics. Do NOT create candidate-v6 yet.

## What this record does and does not do

- It selects Option A for `MD-S053-R0210` and nothing more. It does not redesign checkpoint telemetry, does not change
  what a *failed* row reports beyond the same rule (values of its own identity), and does not create candidate-v6.
- The production change it requires is a change to files of the frozen candidate-v5 build; it is permitted by
  `D-MD-B19-A001-001`. The exact file and change point of the first executable-build change are recorded in the
  evidence of the implementing unit.
- It does not edit candidate-v5, any `MD-B18` record, or `D-MD-B19-A001-001`. `MD-B18` stays `DONE` / `PASS`.

# Decision — project owner selects F-MD-B19-A001-005 Option B and F-MD-B19-A001-006 Option A for `run_summary.json`

- ID: `D-MD-B19-A001-003`
- Verification epoch: `MD-REBASELINE-20260820-001`
- Stage / Attempt / Baseline: `MD-B19` / `MD-B19-A001` / `MD-B19-A001-BL001`
- Decides: `F-MD-B19-A001-005` (Option B); `F-MD-B19-A001-006` (Option A); the held predicates `MD-S075-R0047`, `MD-S075-R0074`, `MD-S075-R0075`
- Governed under: `F-MD-B19-A001-003` Option O1 (`D-MD-B19-A001-001`); `MD-DEP-0025`
- Decisions relied on: `D-MD-B19-A001-001`, `D-MD-B18-A002-018` (item 13)
- Evidence relied on: `E-MD-B19-A001-005` (the measurements)
- Change impact: `CI-MD-B19-A001-001` (amendment issued with the implementation)
- Issued: 2026-10-08T13:02:10+07:00
- Status: `APPROVED` — project owner, in the 2026-10-08 instruction "MD-B19-A001 — RESOLVE RUN-SUMMARY OWNER DECISIONS"
- Mutability: `IMMUTABLE_AFTER_ISSUE`
- Strategy impact: `NONE`; `MD-STRATEGY-FREEZE-20261005-001` unchanged

## Owner decision (as supplied)

- `F-MD-B19-A001-005`: Option B.
- `F-MD-B19-A001-006`: Option A.

Tasks stated with the decision:

- R0047: preserve faithful `warning_count` mapping, including NULL; record the accepted semantic limitation. Do not invent warning population.
- R0074/R0075: strictly mirror persisted `eod_runs.final_reason_code`. Expose effective derived reason separately with explicit provenance marking.
- Ensure all manifest-derived publication-facing fields are clearly marked as derived companion evidence.
- Audit and update affected consumers, including anomaly reporting, completeness checks, and replay comparison. Preserve intended behavior without disguising derived values as persisted data.

## What the options mean, as recorded in the findings

- **`F-MD-B19-A001-005` Option B:** NULL is accepted as the contracted value of `warning_count` until a warning population is defined; the example value in the contract is illustrative; `MD-S075-R0047` is satisfied by the faithful mirror; the finding closes with the limitation recorded.
- **`F-MD-B19-A001-006` Option A:** `final_reason_code` carries the persisted value (NULL stays NULL); the effective reason moves to a separately named derived field with provenance; the manifest-derived minimum fields are listed in a derived-companion marker; consumers are pointed at the effective field. A production change in `MarketDataEvidenceExportService.php` under `D-MD-B19-A001-001`.

## What this record does and does not do

- It selects the two options and nothing else. It does not define what a warning is, does not amend the contract, and does not create candidate-v6.
- The accepted limitation of Option B (a run's `warning_count` is NULL because nothing writes it) is a recorded limitation, not a conformance claim about warning counting.
- It does not edit candidate-v5, any `MD-B18` record or an earlier decision.

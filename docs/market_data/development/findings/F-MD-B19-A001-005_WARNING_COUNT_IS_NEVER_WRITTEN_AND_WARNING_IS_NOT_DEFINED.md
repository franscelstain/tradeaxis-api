# Finding — `F-MD-B19-A001-005`

- ID: `F-MD-B19-A001-005`
- Stage / Attempt / Baseline: `MD-B19` / `MD-B19-A001` / `MD-B19-A001-BL001`
- Raised at: 2026-10-08T12:02:54+07:00
- Severity: `P3`
- Status: `OPEN — AUTHORITY QUESTION FOR THE PROJECT OWNER`
- Class: `AUTHORITY_GAP_FIELD_NEVER_POPULATED`
- Blocked: the per-predicate proof basis of `MD-S075-R0047` only.
- Blocks strategy change: `NO`

## Statement

`Run_Artifacts_Format_LOCKED.md` (`MD-S075`) section 1 lists `warning_count` among the minimum fields of
`run_summary.json` (example value `50`). The summary mirrors the persisted `eod_runs.warning_count`
faithfully — a persisted number is exported as that number and a persisted NULL as NULL (guarded).

Measured on a run produced by the real pipeline (the R0025 world), the persisted value is **NULL**, and it
cannot be anything else: `EodRunRepository` initialises `warning_count` to `null` when it creates a run
(`EodRunRepository.php:112` and `:303`) and **no code in `app/` ever writes it**. Searching the strategy
authority, `warning_count` occurs only in the artifact example; no document defines what a *warning* of a run
is, who counts it, or when. The companion counters `invalid_bar_count`, `invalid_indicator_count` and
`hard_reject_count` are written (1, 1, 1 on that run).

So the field is present in every summary and has no value in any of them.

## Why this is an authority question and not a defect to fix

An implementation would have to choose what a warning is (a row-level `WARNING` severity event? a soft
reject? a coverage near-miss?). The contract does not say. Counting something and calling it `warning_count`
would be inventing a semantic, which the proof method forbids.

## Options

- **A — define and implement.** The owner defines the warning population (and the stage that writes the
  counter). Then `warning_count` is produced by the pipeline and the guard gains a value assertion.
- **B — accept NULL as the contracted value until a population is defined.** The example value becomes
  illustrative, `MD-S075-R0047` is satisfied by the faithful mirror, and the finding closes with a recorded
  limitation.

## Effect on the proof

`MD-S075-R0047` has no entry in `MarketDataOperationsProofBasis` until this is decided. What is guarded
today: the summary exposes `warning_count` under its persisted name and carries the persisted value
(`503` in a sentinel run, `NULL` for NULL, never defaulted to `0`).

## Related

- `E-MD-B19-A001-005` (where this was measured), `MD-S075-R0047`

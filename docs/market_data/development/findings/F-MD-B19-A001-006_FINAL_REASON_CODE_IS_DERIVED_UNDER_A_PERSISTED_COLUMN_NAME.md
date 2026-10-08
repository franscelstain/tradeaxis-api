# Finding — `F-MD-B19-A001-006`

- ID: `F-MD-B19-A001-006`
- Stage / Attempt / Baseline: `MD-B19` / `MD-B19-A001` / `MD-B19-A001-BL001`
- Raised at: 2026-10-08T12:02:54+07:00
- Severity: `P2`
- Status: `RESOLVED`
- Class: `DERIVED_VALUE_UNDER_A_PERSISTED_NAME`
- Blocked: the per-predicate proof basis of `MD-S075-R0074` and `MD-S075-R0075` (now established).
- Blocks strategy change: `NO`

## Statement

`Run_Artifacts_Format_LOCKED.md` (`MD-S075`) section 1, locked rules:

> - run-summary fields that mirror persisted run state must use the persisted names from `eod_runs`
> - derived publication-facing fields may appear only when clearly marked as derived companion evidence, not as replacement names for persisted columns

`MarketDataEvidenceExportService::buildRunSummary()` exports

```
'final_reason_code' => field($run, 'final_reason_code') ?: $sourceContext['final_reason_code'] ?: $coverageReasonCode
```

under the name of a persisted column. Measured on a run produced by the real pipeline: `eod_runs.final_reason_code`
is **NULL**; the summary exports `"final_reason_code": "COVERAGE_THRESHOLD_MET"` — a coverage reason that the
run never recorded as its final reason. Every other key of the summary that shares a name with an `eod_runs`
column (42 of them) carries exactly the persisted value, checked against the database on that run.

The consequence is what the rule is written to prevent: an operator reading `final_reason_code` in the summary
reads a value that the `eod_runs` row does not hold, with nothing marking it as derived.

A second, weaker instance of the same question: the publication-facing fields the contract's own minimum set
puts at the top level of the summary (`publication_manifest_hash`, `config_snapshot_hash`,
`temporal_revision_set_hash`, `factor_set_id`, `canonicalization_version`, `formula_version`,
`read_model_version`) are derived from the publication manifest and carry no marker. They do not replace a
persisted column (`eod_runs` has no column of those names) but the rule says derived publication-facing fields
must be *clearly marked*, and the contract does not say what a marker is.

## Why this needs the owner

Several consumers read the derived value as the run's effective reason (the anomaly report, the completeness
check, replay comparison). Removing the fallback changes them; keeping it contradicts the locked rule as
written. And "clearly marked" has no defined form.

## Options

- **A — mirror strictly, expose the derivation separately (recommended).** `final_reason_code` carries the persisted
  value (NULL stays NULL); the effective reason moves to a separately named derived field (for example
  `effective_final_reason_code`, with a `derived_from` note), and the consumers are pointed at it. The
  manifest-derived minimum fields are listed in a `derived_companion_fields` marker. Production change in
  `MarketDataEvidenceExportService.php` and its consumers, batched under `D-MD-B19-A001-001`.
- **B — keep the fallback, define the marking.** The owner states that `final_reason_code` in the summary is the
  effective reason and defines the marker (a list of derived keys in the summary). No consumer changes; the locked
  rule is satisfied by marking.
- **C — amend the contract** so a derived value under a persisted name is allowed when documented. Strategy change.

## Effect on the proof

`MD-S075-R0074` and `R0075` have no entry in `MarketDataOperationsProofBasis`. What is guarded today: every summary
key that shares a name with an `eod_runs` column other than `final_reason_code` carries the persisted value on a
real run; `final_reason_code` is excluded from that check by name and the exclusion is documented in the test, so
no guard blesses either reading.

## Related

- `E-MD-B19-A001-005` (where this was measured), `MD-S075-R0074`, `MD-S075-R0075`, `F-MD-B19-A001-004`

## 2026-10-08T13:02:10+07:00 Owner decision recorded: Option A (`D-MD-B19-A001-003`)

The project owner selected **Option A**: `final_reason_code` strictly mirrors the persisted `eod_runs.final_reason_code`; the effective derived reason is exposed in a separately named field with explicit provenance; manifest-derived publication-facing fields are marked as derived companion evidence; consumers are audited and updated. The production change is governed under `F-MD-B19-A001-003` Option O1.

## 2026-10-08T14:15:01+07:00 Resolved under Option A (`E-MD-B19-A001-006`)

`buildRunSummary()` now exports `final_reason_code` as the persisted `eod_runs.final_reason_code` (NULL stays NULL) and `final_reason_message` for that code only. The reason resolved for operators is `effective_final_reason_code` (persisted, else source, else coverage reason) with `effective_final_reason_code_derived_from` naming `eod_runs.final_reason_code`, `source_context.final_reason_code` or `coverage.coverage_reason_code`, and `effective_final_reason_message`. `derived_companion_fields` lists every derived field with its derivation kind and origin: the effective reason fields, the seven manifest-derived minimum fields, the bound-input projection, the manifest-preferred `publication_version` / `is_current_publication`, the current-marking derivations (`promoted`, `pointer_switched`, `current_publication_id`, `import_status`, `promote_status`, `import_promote_boundary`), `final_outcome_note` and `source_context`. No persisted mirror is listed.

Consumers audited (`consumer_audit.md` in the raw evidence): the export result and lineage carry both the persisted and the effective reason; the completeness check and the outcome note read the effective reason; the anomaly report does not read the field; replay comparison does not read `run_summary.json` and derives its own final state from the row (unchanged). Proven by 21 guards (plus one real-run guard) and 34 single-defect mutations. Production change in `MarketDataEvidenceExportService.php`, an already-listed executable-build file.

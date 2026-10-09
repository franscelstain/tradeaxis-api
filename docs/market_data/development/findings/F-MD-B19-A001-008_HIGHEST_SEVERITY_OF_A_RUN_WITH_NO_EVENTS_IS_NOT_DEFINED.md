# Finding — `F-MD-B19-A001-008`

- ID: `F-MD-B19-A001-008`
- Stage / Attempt / Baseline: `MD-B19` / `MD-B19-A001` / `MD-B19-A001-BL001`
- Raised at: 2026-10-09T07:52:52+07:00
- Severity: `P3`
- Status: `OPEN`
- Class: `AUTHORITY_GAP_EMPTY_TRAIL_REPRESENTATION`
- Blocked: the per-predicate proof basis of `MD-S075-R0121` and `MD-S075-R0128` only (both held, see below).
- Blocks strategy change: `NO`

## Statement

`Run_Artifacts_Format_LOCKED.md` (`MD-S075`) section 3 gives `highest_severity` the example value `"INFO"` for a
trail of 17 events and says two things about it: it is a *derived* field (not a persisted column name), and the
summary "must not present `highest_severity` as lower than `ERROR`" when the trail contains an `ERROR`.

`EodEvidenceRepository::summarizeRunEvents()` initialises `$highestSeverity = 'INFO'` before it looks at any
event, so **a run with zero events reports `"highest_severity": "INFO"`**, while `first_event_time`,
`last_event_time`, `first_event_type` and `last_event_type` are `null` and both count maps are empty.

The locked authority does not say what `highest_severity` is when there is no event to take a maximum over.
Nothing in `app/` or the artifacts consumes the value of an empty trail (`dominantReasonCodesFromRunEvents()` reads
only `reason_code_counts`; the only other readers of `highest_severity` are tests).

## What authority does and does not settle

- Observed severity: for any trail with at least one event the value is the maximum severity (INFO < WARN < ERROR)
  of its events. That is guarded and is not in question.
- Empty trail: neither `MD-S075` section 3, the Observability Minimum Contract, the Determinism Invariants nor the
  executed bundle example says whether the value is `INFO`, absent, `null` or a marker. The ERROR rule cannot decide
  it (an empty trail has no ERROR).
- `MD-S075-R0128` says the summary "must not invent event history that is absent from the append-only trail". Whether
  a *default severity with no event behind it* is an invented observation (an INFO nobody logged) or merely the
  vacuous floor of the severity scale is exactly what the text leaves open.

An implementation would have to choose. Choosing is a semantic decision, which the proof method forbids.

## Affected predicates

- **`MD-S075-R0121`** (`highest_severity`): directly — its value for a run with no events.
- **`MD-S075-R0128`** (derivable from the trail, nothing invented): indirectly — only if the owner reads a default
  severity with no event as invented history. Its other content (event count, times, types, count maps for an
  empty trail) is proven.
- Not affected: `R0129` (the field is derived, not a persisted column — true under every option), `R0130` (needs an
  ERROR in the trail).

## Options (none is an approved decision; the first is the independent reviewer's recommendation)

- **A — `null` when no event severity exists.** One line in the repository; the empty-trail guard then asserts
  `null`. Reads the field as a pure derivation: nothing observed, nothing reported.
- **B — keep `INFO` as the contracted floor for an empty trail** and record it as a limitation. The example stays
  the only shape; the empty-trail guard asserts `INFO`.
- **C — another explicit marker defined by the owner** (for example an absent key or a named non-severity value),
  with the exporter and the guard following the definition.

## Effect on the proof (applied)

`R0121` and `R0128` have **no entry** in `MarketDataOperationsProofBasis` until this is decided, as
`MD-S075-R0047` had none while `F-MD-B19-A001-005` was open. The guards are not withdrawn:
`B19RunEventSummaryTrailDerivationTest` and `B19RunEventSummaryTieOrderAndEmptyTrailTest` prove the observed
maximum for every non-empty trail and assert only that an empty trail does not report a `WARN`/`ERROR` it never
observed — they do not pin `INFO`, so they stay valid under every option.

`E-MD-B19-A001-008` is unchanged and immutable; its statement that the empty-trail severity is "not asserted either
way" is the same fact this finding records.

## Related

- `E-MD-B19-A001-008` (where this was first noted), `E-MD-B19-A001-009`, `MD-S075-R0121`, `MD-S075-R0128`

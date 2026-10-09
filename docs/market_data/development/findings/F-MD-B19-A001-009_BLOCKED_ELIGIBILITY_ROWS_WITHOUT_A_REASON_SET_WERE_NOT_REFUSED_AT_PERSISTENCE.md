# Finding — `F-MD-B19-A001-009`

- ID: `F-MD-B19-A001-009`
- Stage / Attempt / Baseline: `MD-B19` / `MD-B19-A001` / `MD-B19-A001-BL001`
- Raised at: 2026-10-09T13:45:08+07:00
- Severity: `P3`
- Status: `RESOLVED`
- Class: `LOCKED_INVARIANT_NOT_ENFORCED_AT_PERSISTENCE`
- Blocked: nothing beyond the admission of `MD-S075-R0144` (and the boundary cases of `R0134`, `R0138`) in the
  `artifact_eligibility_export` family; proven in the attempt that raised it.
- Blocks strategy change: `NO`

## Statement

`Eligibility_Partial_Data_Behavior_LOCKED.md` states: "No `eligible=false` row may have an empty reason set."
`Run_Artifacts_Format_LOCKED.md` section 4 states: "blocked rows must carry registered reason codes."

The eligibility producer obeys the first rule by construction: `EligibilityDecisionService::decide()` returns a reason for
every `eligible = 0` branch and `EodEligibilityBuildService` leads the set with `ELIG_TRADING_SUSPENDED` for a suspended
listing; every code it can emit is a registered, active reason code (`B19EligibilityReasonRegistrationTest`).

But nothing in the **persistence path** enforced it. `EodArtifactRepository::replaceEligibility()` required the fact
fields to be present and non-blank (`REQUIRED_ELIGIBILITY_WRITE_FIELDS`, including `eligibility_reasons_json`), and `'[]'`
satisfies "non-blank". A blocked row with an empty set, a set that is not a list, or a blank or non-string member was
written. The sealing path does not check it either (the semantic hash covers `eligibility_reasons_json` but does not
judge it). The CSV export reports what was persisted; it can neither invent a reason nor refuse to describe the
publication, so the export could not meet `R0144` for such a row.

Measured with the real repository on SQLite: six defective blocked rows (empty set; empty set with a legacy reason; a
string; a JSON object; a blank member; a non-string member) and a mixed batch were all written. No production run produces
such a row, so this is a missing guard, not a producing defect.

## Remediation

`replaceEligibility()` now refuses, before it deletes or inserts anything, any row with `eligible = 0` whose
`eligibility_reasons_json` is not a non-empty JSON list of non-blank strings (`ELIGIBILITY_WRITE_INCOMPLETE`, the code the
neighbouring completeness guard already uses). Usable rows, and blocked rows with a real set, are written as before. The
snapshot and promote paths, which copy already-persisted rows, are deliberately not tightened: they must not reject legacy
history.

## What it does not do

- It does not check that the members are registered (the producer's literals are proven registered; a registry lookup in
  the write path would be a new dependency and is not required by the locked text).
- It does not rewrite, repair or invent anything. A defective row that already exists (legacy or bypassed) is still exported
  exactly as stored — a faithful report of defective data, not conformance — and
  `B19EligibilityExportRowProvenanceTest` says so.
- It does not enforce a listing identity: a V2 semantic artifact already refuses a row without one
  (`ARTIFACT_LOCAL_NAVIGATION_KEY_MISSING`), proven in `B19EligibilityBlockedRowAdmissionTest`.

## Proof

`B19EligibilityBlockedRowAdmissionTest` (12 tests): red before the change (7 failures: the six defective rows and the mixed
batch), green after; seven mutations red; the existing write completeness, producer and explainability suites stay green.

## Related

- `E-MD-B19-A001-012` (the proof), `E-MD-B19-A001-011` (unchanged, where the question was raised by independent review),
  `F-MD-B01-A014-001`, `MD-S075-R0144`, `MD-S075-R0138`, `MD-S075-R0134`
- `MD-DEP-0025`: `EodArtifactRepository.php` is in the candidate-v5 frozen build manifest, so it is a fifth executable-build
  file that differs from candidate-v5.

## 2026-10-09T15:10:24+07:00 Correction to this finding's own first form (`E-MD-B19-A001-013`)

The first remediation (`E-MD-B19-A001-012`) decoded the set with `json_decode(..., true)` and required sequential keys. An independent reviewer showed, and the local runtime (PHP 7.4.33) confirmed, that an associative decode turns `{"0":"ELIG_MISSING_BAR"}` and `{"0":"ELIG_MISSING_BAR","1":"ELIG_TRADING_SUSPENDED"}` into arrays indistinguishable from a list, so the guard accepted a JSON **object** while claiming to require a list. The statement above that the write refuses a set that is "not a list" was therefore true for every form tested then but not for this one. The guard now decodes without the associative flag and requires `is_array` (a list) and non-empty; objects of any kind, empty lists, lists of lists and blank or non-string members are refused before any stored row is touched. Red before the change (the two numeric-key objects), green after, seven mutations red. Registry membership is still not enforced at the write: an arbitrary unregistered string in an otherwise valid list is outside the current proof scope and is recorded by a tripwire test. Nothing was invented and no owner decision was needed.

## 2026-10-09T16:12:13+07:00 Remaining limitation and optional hardening question (`E-MD-B19-A001-014`)

An independent reviewer asked whether an unregistered reason written through the public `replaceEligibility()` could reach an admissible publication. Executed: the method accepts it and the evidence export of a sealed publication carries it unchanged, so **nothing downstream of the producer consults the registry**. The boundary of `MD-S075-R0144` is therefore the producer, and it is closed: one production caller, one builder of reason sets, two member expressions (the suspension literal and the decision's reason), every `ELIG_` literal registered, a closed set of table writers, enforced by a census of `app/` (`B19EligibilityReasonReachabilityTest`). No production path can pass an unregistered code, so no correction was needed and `R0144` stays admitted.

**Optional hardening question, not decided and not blocking:** should persistence or sealing additionally consult the reason-code registry for row reasons, and against which registry - the live `eod_reason_codes` table or the snapshot bound to the publication's config, which differ for historical or replayed publications? (A) none - keep the producer boundary and the census; (B) a write-path check against the live table; (C) a seal-time check against the bound snapshot. This needs an authority decision on the registry and on legacy-copy compatibility.

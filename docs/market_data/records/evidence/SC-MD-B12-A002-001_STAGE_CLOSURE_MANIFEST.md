# MD Stage Closure Manifest — SC-MD-B12-A002-001

- ID: `SC-MD-B12-A002-001`
- Stage / Attempt / Baseline / Epoch: `MD-B12` / `MD-B12-A002` / `MD-B12-A002-BL001` / `MD-REBASELINE-20260820-001`
- Strategy freeze: `MD-STRATEGY-FREEZE-20260823-001`
- Change Impact Declaration: `CI-MD-B12-A002-001`
- Governed evidence: `E-MD-B12-A002-001`
- Stage precondition: `SC-MD-B12-A001-001`
- Dependency: `MD-DEP-0004` — partially discharged for B12; `MD-B12-A003` owns the remainder
- Role: `EVIDENCE`, scope `STAGE_CLOSURE_MANIFEST`, mutability `IMMUTABLE_AFTER_ISSUE`
- Supersedes: nothing. `SC-MD-B12-A001-001` remains immutable.

## Closure verdict

`MD-B12` is `DONE` with verdict `PASS` under `MD-B12-A002`, at **60 mandatory / 60 SATISFIED**, zero
pending applicability, zero unbound.

`MD-B12` is **not** admitted to `DECISION_RECORDED_STAGES`. 39 non-structural reference rows remain
for `MD-B12-A003`, and the classification gate continues to count them. This attempt closes its
declared scope, not the stage's reference population.

## Scope, and why it is partial

`MD-B12` carries 54 non-structural reference rows against 5 in `MD-B09`; roughly 30 are genuine
promotions. Binding 30 predicates in one pass without verifying each guard covers its whole predicate
is the over-claiming this track exists to prevent — the lesson of `MD-S008-R0018`, where the existing
guard covered two of the three leak classes its rule names.

| In A002 | Deferred to A003 |
|---|---|
| `Forbidden behavior (LOCKED)` — 9 | `Contamination behavior` — 5 |
| `Eligibility for adjustment (LOCKED)` — 6 | `MD-S012 Version/change rule` — 5 |
| | factor formula, append-only factor, provenance — 5 |
| | 24 reference decisions to record |

## Why neither block was reachable by the old gate

Both are **uniformly** `REFERENCE_ONLY` enumerated runs. `MIXED_RUN` fires only when a run holds both
classes; a run with nothing required in it has no mixture to detect. Both surfaced through
`UNEXPLAINED_REFERENCE`, added in `MD-B00-A004`.

## The headline audit finding is closed

`MD-S083-R0032` — *the event revision is `AUTHORITATIVE_VERIFIED` or governed `MANUAL_VERIFIED`* — is
the B12-side twin of `MD-S011-R0023`, the invariant the reference-population audit demonstrated was
enforced by exactly one line at `AdjustmentFactorSetService.php:183` and protected by nothing:
widening its filter to admit `PROVIDER_REPORTED` passed the entire 1946-test suite, and because both
rules were `REFERENCE_ONLY`, no traceability row claimed the invariant either.

`AdjustmentFactorSetB11Test::test_only_authoritative_or_manual_verified_revisions_are_adjustment_active`
now seeds `PROVIDER_REPORTED`, `SYNTHETIC_CANDIDATE` and `REJECTED` revisions alongside a verified
one and requires only the verified one to reach the factor set. The exact audit mutation fails it.
The application file was restored from git and verified byte-identical to `HEAD`.

## A guard I expected to write and did not

I reported `MD-S083-R0061` (*treating factor `1` or absent factor as proof the window is clean*) as
having no test, based on a search for the phrase *clean window*. That search used the wrong strings.
`ContaminationAnchoredOnBreakDateTest::test_a_neutral_factor_does_not_excuse_a_detected_break` covers
the factor-1.0 half verbatim, and `test_an_unexplained_break_still_contaminates` covers the
absent-factor half. Contamination is resolved from the event-risk repository, never from factor
arithmetic. No new guard was needed and none was written.

## What is explicitly not a promotion candidate

`MD-S012` coherence bullets `R0023`–`R0027` read like strong candidates and are not:
`MD-S083-R0039` and `MD-S012-R0028` already own that obligation as `REQUIRED` rows, so promoting the
bullets would duplicate closure ownership contrary to §7. Recorded because the reading changed
mid-review.

## Executed proof

- B12 proof gate `--bound`: `PASS` — 60 denominator, 60 proof map, 12 families, 0 runtime pending.
- B12 traceability gate `--bound`: `PASS` — `BOUND_CLOSURE`, 60 mandatory, 63 reference, 0 pending.
- B12 static gate: `PASS`. Classification, documentation, relationship, applicability gates: `PASS`.
- B12 proof surface: **46 tests, 118 assertions, exit 0**.
- Full suite: **1952 tests, 18241 assertions, 0 failures, exit 0**.

## Review-pass guards, each proven falsifiable

- A named rule never reached aborts the pass — proven by renaming `MD-S083-R0061` to a non-existent id.
- A named rule that was not `REFERENCE_ONLY` to begin with aborts it — proven by inverting the check,
  so a typo cannot silently no-op.
- Touching any row owned by another stage aborts it — proven by injecting a mutation on
  `MD-S011-R0023`, a `MD-B11` row.

## Tooling defects corrected

The binder required the whole denominator to be pristine `NOT_ASSESSED`, which would have forced this
attempt to unbind the 45 closed A001 predicates in order to rebind them — the shape that lost 30
predicates in `MD-B07-A002`. It now binds only the rules belonging to the attempt whose evidence id
is passed. The proof gate, traceability gate and binder also hardcoded `A001` and the literals `45`
and `78`; all now read the spec constants.

## Residue and boundary verdict

- Verdict: `CONFORMANT_NO_HARMFUL_RESIDUE_FOUND_IN_THE_CORRECTED_B12_SURFACE`.
- Application source, schema, migration, configuration: **no change**. Storage: not inspected, not
  mutated. `E-MD-B12-A001-001` unedited; its 45 bindings stand.

## Findings and dependencies

- `MD-B12-A003` owns 39 non-structural reference rows.
- `MD-B11` remains at 201 and is the last closed stage after B12.
- Every stage normalization other than `MD-B07` and `MD-B08` still carries the unguarded clearing
  code that can unbind another stage's proof.

## Correlation and closure chain

`SC-MD-B12-A001-001` → `MD-B12-A002-BL001` → `CI-MD-B12-A002-001` → `E-MD-B12-A002-001` →
`SC-MD-B12-A002-001`, with `SC-MD-B00-A004-001` as the gate that surfaced both blocks.

## Exact successor state

The single next executable resume point is `MD-B12-A003`: the remaining 39 rows of this stage, before
`MD-B11` at 201.

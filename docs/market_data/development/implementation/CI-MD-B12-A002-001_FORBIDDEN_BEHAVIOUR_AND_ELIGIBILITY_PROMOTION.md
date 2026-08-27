# Change Impact Declaration — `MD-B12-A002`

- ID: `CI-MD-B12-A002-001`
- Stage / Attempt / Baseline / Epoch: `MD-B12` / `MD-B12-A002` / `MD-B12-A002-BL001` / `MD-REBASELINE-20260820-001`
- Strategy freeze: `MD-STRATEGY-FREEZE-20260823-001`
- Stage precondition: `SC-MD-B12-A001-001`
- Dependencies: `MD-DEP-0004` stage-entry semantic normalization
- Status: `EXECUTED — SCOPED`
- Strategy meaning change: `NO`

## Objective

Promote and bind the two `MD-S083` blocks that state obligations and carried no proof obligation at
all: `Forbidden behavior (LOCKED)` and `Eligibility for adjustment (LOCKED)`.

## Why the scope is deliberately partial

`MD-B12` carries 54 non-structural reference rows against 5 in `MD-B09`. Of those, roughly 30 are
genuine promotions. Binding 30 predicates in one pass without verifying that each guard covers its
whole predicate is precisely the over-claiming this track exists to prevent — the lesson of
`MD-S008-R0018` in `MD-B09-A003`, where the existing guard covered two of the three leak classes the
rule names.

The split was requested. A002 takes the 15 rows whose status is unambiguous; A003 takes contamination
behaviour, the version/change rule, the remaining formula and provenance rows, and the reference
decisions. `MD-B12` is **not** admitted to `DECISION_RECORDED_STAGES` by this attempt, and the
classification gate still counts it as pending at 39.

## Why the gate could not see these blocks

Both are **uniformly** `REFERENCE_ONLY` enumerated runs. `MIXED_RUN` fires only when a run holds both
classes, so a run with nothing required in it has no mixture to detect. They surfaced through
`UNEXPLAINED_REFERENCE`, added in `MD-B00-A004` — the same way `MD-S008-R0018` surfaced in
`MD-B09-A003`.

## Authority and traceability scope

- `MD-S083` Forbidden behavior (LOCKED): nine prohibitions, `R0054`–`R0062`.
- `MD-S083` Eligibility for adjustment (LOCKED): five conditions plus the exclusion sentence,
  `R0032`–`R0037`.
- Denominator 45 → **60**. Reference 78 → **63**.

## What is explicitly not a promotion candidate

`MD-S012` coherence bullets `R0023`–`R0027` look like strong candidates and are not. `MD-S083-R0039`
and `MD-S012-R0028` already own that obligation as `REQUIRED` rows; promoting the bullets would
duplicate closure ownership contrary to §7. Recorded here because the reading changed mid-review.

## Impact assessment

- Strategy / schema / configuration / application source: **no change**.
- Storage: not inspected, not mutated.
- Tests/gates: one new behavioural guard for adjustment eligibility. Four new proof families. The
  B12 proof gate, traceability gate and binder become attempt-aware and constant-driven instead of
  hardcoding `A001` and the literals `45` and `78`.
- Compatibility: `E-MD-B12-A001-001` is not edited; its 45 bindings stand untouched.

## Actual impact and result

- **Traceability**: 15 promoted; denominator 45 → **60**; B12 unexplained reference 54 → **39**.
- **Headline audit finding closed.** `MD-S083-R0032` is the B12-side twin of `MD-S011-R0023` — the
  invariant the audit proved was enforced by one line of code and protected by nothing. A new guard
  seeds `PROVIDER_REPORTED`, `SYNTHETIC_CANDIDATE` and `REJECTED` revisions alongside a verified one
  and requires only the verified one to reach the factor set. The exact audit mutation, which
  previously passed all 1946 tests, now fails it.
- **A guard I expected to write and did not.** I reported `MD-S083-R0061` as having no test, from a
  search for the phrase *clean window*. That was the wrong search:
  `test_a_neutral_factor_does_not_excuse_a_detected_break` covers the factor-1.0 half verbatim and
  `test_an_unexplained_break_still_contaminates` covers the absent-factor half. No new guard was
  needed and none was written.
- **Three review-pass guards, each proven falsifiable**: a named rule never reached aborts the pass;
  a named rule that was not `REFERENCE_ONLY` to begin with aborts it; touching any row owned by
  another stage aborts it.
- **Binder correctness**: the binder required the whole denominator to be pristine `NOT_ASSESSED`,
  which would have forced this attempt to unbind the 45 closed A001 predicates in order to rebind
  them — the shape that lost 30 predicates in `MD-B07-A002`. It now binds only the rules belonging to
  the attempt whose evidence id is passed.
- **Application source changed**: **NO**. **Strategy changed**: **NO**.

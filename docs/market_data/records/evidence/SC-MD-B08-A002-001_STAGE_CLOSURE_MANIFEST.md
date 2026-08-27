# MD Stage Closure Manifest — SC-MD-B08-A002-001

- ID: `SC-MD-B08-A002-001`
- Stage / Attempt / Baseline / Epoch: `MD-B08` / `MD-B08-A002` / `MD-B08-A002-BL001` / `MD-REBASELINE-20260820-001`
- Strategy freeze: `MD-STRATEGY-FREEZE-20260823-001`
- Change Impact Declaration: `CI-MD-B08-A002-001`
- Governed evidence: `E-MD-B08-A002-001`
- Stage precondition: `SC-MD-B08-A001-001`
- Dependency: `MD-DEP-0004` B08 entry obligation re-completed; remains `OPEN_NON_BLOCKING` downstream
- Role: `EVIDENCE`, scope `STAGE_CLOSURE_MANIFEST`, mutability `IMMUTABLE_AFTER_ISSUE`
- Supersedes: nothing. `SC-MD-B08-A001-001` remains immutable; this manifest records the corrected denominator.

## Closure verdict

`MD-B08` is `DONE` with verdict `PASS` under `MD-B08-A002`, at **139 mandatory / 139 SATISFIED**,
zero transitional applicability, zero unbound proof, zero B08 mixed-classification debt.

## What was corrected

`MD-S067-R0010` — *"All reason codes are retained. A primary reason is routing compatibility only."*
— is the standalone paragraph closing the `Independent dimensions` section. It carries no list
marker, so `detectRuns` never groups it with the eight dimension bullets and `MIXED_RUN` cannot
fire. It states two objectively testable obligations and was filed `REFERENCE_ONLY` with empty
notes.

Unlike `MD-B07-A001`, the B08 normalization scope was not at fault: it examines all 233 rows the
matrix assigns to the stage. The rule was examined and misclassified.

## Why this correction is heavier than MD-B07-A002

`MD-B07-A002` bound proof that already existed. Here it did not. The only test naming
`failure_reason_summary` asserted the string appears in the adapter source — a static guard that
stays green while the behaviour is gone. **Collapsing the retained reason map to its single most
frequent entry passed the entire 1946-test suite.** This is the third reason-code defect in this
package that a static guard failed to catch.

So this attempt wrote the first behavioural test the obligation has ever had.

## Executed proof

- New guard: `PublicApiEodBarsAdapterTest::test_every_distinct_failure_reason_is_retained_and_the_primary_reason_does_not_replace_them`
  — one run over three tickers, two failing for genuinely different reasons (HTTP 429 →
  `RUN_SOURCE_RATE_LIMIT`, HTTP 500 → `RUN_SOURCE_TIMEOUT`), one succeeding so the run is partial.
- Falsifiability: the exact mutation that previously passed all 1946 tests now fails with
  `the transient-server cause was dropped from the retained set`. Application file restored from git
  and verified byte-identical to `HEAD`.
- B08 failure-taxonomy proof surface: **75 tests, 335 assertions, exit 0**.

## A correction to my own first draft

The first draft of the test asserted `final_reason_code` would be one of the two retained causes. It
is not: for a partial run the adapter publishes the routing label `RUN_SOURCE_PARTIAL_RESPONSE`.
That is the contract working as written — a primary reason that is not itself one of the causes is
exactly why the contract forbids reading it as *the* reason. The assertion now states what the
contract says rather than what the draft assumed.

## Latent hazard closed

The B08 normalization carried the same unguarded clearing code that unbound 30 closed predicates
during `MD-B07-A002`. `foreign_bindings_preserved` reports **41** rows whose closed proof a re-run
would have destroyed. Both guards written for B07 are now present here: foreign-binding preservation
with its assertion, and scope completeness reporting **233 of 233**.

## Residue and boundary verdict

- Verdict: `CONFORMANT_NO_HARMFUL_RESIDUE_FOUND_IN_THE_CORRECTED_B08_SURFACE`.
- Application source changed: **NO**. Schema/migration/configuration: **NO**. Storage: not inspected,
  not mutated.
- `E-MD-B08-A001-001` unedited; its 138 bindings stand. Only the 139th carries the A002 pair.

## Findings and dependencies

- **Reported, not fixed here**: `MD-B09` 2, `MD-B11` 15, `MD-B12` 5 obligation-shaped reference rows
  still carry no recorded decision.
- **Reported, not fixed here**: the classification consistency gate still cannot see an
  obligation-bearing paragraph filed as reference outside a mixed enumerated run. Two stages have now
  been corrected by hand for defects of exactly this shape, which is the argument for fixing the gate
  before continuing by hand.
- **Reported, not fixed here**: no governance gate verifies that a closed stage still holds its proof
  bindings; only `MD-B07` and `MD-B08` normalizations now guard against unbinding another stage.

## Correlation and closure chain

`SC-MD-B08-A001-001` → `MD-B08-A002-BL001` → `CI-MD-B08-A002-001` → `E-MD-B08-A002-001` →
`SC-MD-B08-A002-001`.

## Exact successor state

`MD-B08` is closed at 139/139. The single next executable resume point is the classification-gate
hardening that would detect this defect class across all remaining stages, ahead of continuing the
per-stage re-check at `MD-B09`.

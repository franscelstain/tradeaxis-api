# Finding — production relock predicate has only substrate proof

- ID: `F-MD-B18-A002-008`
- Raised by / Owner: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001`
- Epoch: `MD-REBASELINE-20260820-001`
- Raised: 2026-09-13T23:00:16+07:00
- Severity: `P1`
- Status: `RESOLVED — OWNER_DECIDED_AND_FALSIFIABLE_GUARD_ESTABLISHED`
- Class: `PARTIAL_PREDICATE_PROOF_AND_UNCONFIRMED_OWNER`
- Evidence: `E-MD-B18-A002-006`
- Blocking dependency: `MD-DEP-0014`; `MD-DEP-0009` still blocks B19 return
- Affected predicate: `MD-S050-R0056`, still MANDATORY / NOT_ASSESSED, primary MD-B18

## Root cause and executed counterexample

The frozen Replay_Verification_Contract_LOCKED.md line 109 requires executed publication and
as-known fixtures, including all anti-survivorship cases, as a production-relock prerequisite on
the actual production path. The complete Trading backtest boundary section (lines 105-109) contains
reference paragraph R0055 and this self-contained obligation; R0050-R0054 belong to the preceding
admissibility section. No common absent-capability condition extends to R0056.

ProofBasis had placed R0056 in PROVEN even though its own basis explicitly states that nothing
there refuses a relock lacking the corpus. B18ProductionPathReplayFixturesTest lines 28-35 states
the narrower engine/repository test boundary. The mapped positive method (line 132) checks the
eight-case text map and existence of method names; the negative method (line 157) checks database
driver/version/name. Neither executes or aggregates the corpus, or admits/refuses a relock.

A current counterexample makes the distinction measurable. Injecting one unconditional
PROBE_PUBLICATION_FIXTURE_NOT_EXECUTABLE exception into the publication fixture (original entry
line 181) lands exactly once. Both mapped guards remain green: 2 tests / 8 assertions. Executing
that fixture itself produces 1 error / 0 assertions. A green corpus control before the mutation
(part of 20 tests / 100 assertions) and after byte restoration (11 tests / 43 assertions) establishes
that the injected defect is real, isolated and reversible. All runs use MariaDB where claimed,
with zero skips. This is an UNCaught proof-mapping probe, not a successful falsifiability result.

Scoped search of app/routes/config/tests and implementation gate PHP finds no application relock
admission operation; the related schema guard only prevents claiming relocked rollout states while
columns remain nullable. That is a different predicate, not proof of this prerequisite. The Stage
Register assigns operational validation and relock to MD-B22, which is NOT_STARTED. Choosing
whether B18 owns the complete prerequisite or B22 owns it is a scope/ownership decision, not a
test tweak. No production data execution is silently authorized or asserted to be required merely
because the frozen phrase says actual production path.

## Safe remediation already performed

- Move only R0056's basis from PROVEN to INCOMPLETE, preserving its old explanation for audit.
- ProofGate now requires each live predicate's own nonempty basis and both actual guard methods;
  it reports PREDICATE_WITHOUT_REVIEWED_BASIS:MD-S050-R0056, not a family-level PASS.
- Count outstanding bases from the current owned denominator, not the original 121-entry audit.
  The current result is 114 candidate bases and 1 incomplete basis across 115 NOT_ASSESSED rows.
  The other 114 have not all been re-reviewed in this continuation; their presence is not proof.
- Align the live map's exact six approved exclusions and counts to 115 under D001/D002/E005.
  R0056 remains in the map and matrix; it is not conditionally N/A, waived, split or reassigned.
- Keep conforming application/fixture files unchanged after probes. No E001 PASS, SC, production
  relock, stage entry or B19 handover is issued. F005 closure-artifact proof remains required.

## Decision options and recommendation

Recommended: assign the COMPLETE unchanged R0056 predicate to MD-B22 as primary proof owner,
with MD-B18 supporting. B22 must review the actual production-path corpus and its enforceable
relock acceptance linkage before implementing any missing gate. Preserve the obligation as
MANDATORY / NOT_ASSESSED; do not equate fixture existence or database connection with completion.
Register a non-blocking downstream dependency and explicit evidence relationships. Impact if
approved: B18 denominator 115 -> 114; B22 mandatory population 28 -> 29. No stage opens and no proof
or closure transfers. B18 still owes all 114 predicate proofs and its own closure requirements.

Alternative: retain the whole predicate under B18. Then explicitly approve/review its complete
relock acceptance scope and enforcement boundary, including what qualifies as executed actual
production-path proof; contract/review precedes any behavior change. Impact: B18 remains 115 and
cannot close on the current substrate-only tests. Neither choice permits narrowing the predicate,
changing strategy bytes or silently authorizing a production relock.

## Single exact continuation and do-not-repeat

Stop at MD-B18-A002 / MD-DEP-0014 for this decision. Do not repeat the resolved four-child
applicability question, the caught AS_KNOWN capability probe, or the demonstrated R0056 map-pair
blind spot unless their implementation/evidence changes. After a decision, issue its correlated
record, apply only the approved ownership/scope change with counted registry/matrix deltas, then
resume per-predicate review/runtime/probes and F005 closure proof in this same attempt. Return to
MD-B19-A001 only after valid B18 closure and resolution of MD-DEP-0009.

## Current validation journal

Final targeted/stage/governance/full-suite results are appended here after execution. The immutable
E006 audit records the observed applicability/proof counterexamples, not an unexecuted full suite.

2026-09-14 — stopped-state revalidation, `E-MD-B18-A002-007`. This journal was empty on resume.
Targeted 351 tests / 1522 assertions, zero skips; all 11 production-path fixtures pass on MariaDB.
Normalization PASS; proof, readiness and binder fail only on this finding's R0056 basis; closure
fails on R0056 plus the absent A002 pack (F005); self-test control red on R0056 only, so no mutation
verdict is admitted. Governance gates pass after `F-MD-B18-A002-009` was registered. The full suite
did not complete cleanly: MariaDB crashed mid-run (`F-MD-B18-A002-010`, `MD-DEP-0015`).

The claim above was re-checked, not inherited: lines 132 and 158 are as described, and app, config,
routes and database contain zero relock operations. Two matrix precedents bear on the decision and
were not weighed when the recommendation was written. `MD-S049-R0015`, `MD-S021-R0042` and
`MD-S007-R0073` are "relock requires X" predicates owned and satisfied by their subject stage;
`MD-S029-R0002` and `MD-S059-R0037` are operational/final-relock predicates owned by MD-B22 with
the subject stage supporting. R0056 names B18-subject fixtures, so precedent favours B18 owning the
executed prerequisite and B22 owning the relock act. `MD-S049-R0015` itself binds five evidence
kinds to one guard pair, so it is ownership precedent only, not a proof standard. The decision
remains the user's.

## Resolution — 2026-09-14

**Decision.** `D-MD-B18-A002-003`: the user chose Option B. MD-B18 stays primary and MD-B22 is
supporting for the final relock act. The predicate is unchanged. "Production path" means the
MariaDB engine, the production repositories and the migrated schema; it does not mean the
production deployment or production data. The matrix changed in one row (`supporting_stages` became
`MD-B22`), and the denominator stays at 115.

**The blind spot is closed.** Two new guards cover it:
- `test_the_whole_production_path_corpus_executes_and_passes_on_mariadb` derives nine members from
  MD-S050 plus the publication fixture, and runs every one in-process inside a rolled-back
  savepoint. It counts a member only if it completes with at least one assertion, and it fails
  rather than skips when the engine is unavailable.
- `test_the_corpus_harness_refuses_every_way_a_fixture_can_fail_to_count` checks the harness's
  classification.

The probe this finding recorded is P1 below, and it now turns the guard red. Every probe landed
once, with controls green either side and a byte restore:

| Probe | Mutation | Reported as |
|---|---|---|
| P1 | exception at the entry of the publication fixture (this finding's probe) | `THREW` |
| P2 | skip in the calendar case | `SKIPPED` |
| P3 | early return in the outage case | `NO_ASSERTIONS` |
| P4 | corporate-action case removed from the map | `MISSING` |
| P5 | falsified calendar expectation | `FAILED` |
| P6 | nonexistent test database | the aggregate fails while the other twelve tests skip |

`ProofBasis::PROVEN['MD-S050-R0056']` now names this pair; the substrate-only entry is kept under
`SUPERSEDED` for audit. The evidence is `E-MD-B18-A002-009`.

**What resolution does not mean.** R0056 stays `NOT_ASSESSED` in the matrix until MD-B18-A002
binds its whole denominator to stage evidence. No production relock was performed or authorized.
Adding the aggregate changed a tested source of E005, which the normalization gate caught; E008
re-executed that proof. Closure remains blocked by `F-MD-B18-A002-011` and the pending
per-predicate review.

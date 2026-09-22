# Finding — replay admissibility and result-semantics review gaps

- ID: `F-MD-B18-A002-015`
- Stage / Attempt / Baseline / Epoch: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001` / `MD-REBASELINE-20260820-001`
- Raised: 2026-09-14T14:02:47+07:00 (system clock)
- Severity: `P1` for closure. It contains executable defects and proof bases that overclaim.
- Status: `OPEN — REMEDIATION_IN_CONSOLIDATED_PACKAGE`
- Class: `EXECUTABLE_DEFECT_AND_PROOF_BASIS_MISTARGETED`
- Found by: per-predicate review of PAIRS 02, 03, 04, 06, 07 and 08 (30 predicates)
- Dependency: `MD-DEP-0017` (consolidated remediation review)

## Executable defects

1. **An unknown fixture case passes every outcome.** In `ReplayBackfillService.php:68`, the result
   is computed as `$passed = $expectedOutcome ? $observedOutcome === $expectedOutcome : true`.
   `expectedOutcomeForFixtureCase()` (`:~160`) knows four cases, while
   `market-data:replay:backfill --fixture_case` accepts any string. For an unknown case, every date
   counts as passed and `all_passed` stays true. That includes `NOT_ADMISSIBLE`
   (`ReplayVerificationService.php:93`), which persists as `BLOCKED`, and it includes `MISMATCH`.
   The rules this contradicts:
   - `MD-S002-R0009` — a release candidate treats `BLOCKED` as missing proof, never as a pass;
   - `MD-S050-R0053` — `BLOCKED` is not a weaker `PASS`;
   - `MD-S002-R0010` — nothing may compensate for a semantic mismatch.

   `ReplayBackfillServiceTest` has two methods, and neither tests an unknown case.
2. **The coverage reason code is synthesized, not preserved.**
   - No `coverage_reason_code` column exists in the base SQL or any migration.
   - `CoverageGateEvaluator` produces `COVERAGE_GATE_DISABLED`,
     `COVERAGE_CANONICAL_BAR_EVIDENCE_DISABLED`, `COVERAGE_UNIVERSE_EMPTY`,
     `RUN_COVERAGE_NOT_EVALUABLE`, `COVERAGE_THRESHOLD_MET`, `COVERAGE_BELOW_THRESHOLD` and
     `RUN_COVERAGE_LOW`.
   - The export re-derives a code from the gate state alone
     (`MarketDataEvidenceExportService::resolveCoverageReasonCodeFromState`), which yields only
     three values. Distinct causes collapse into one.
   - The PAIR 03 guard's expectation had been changed to match the exporter.

   This contradicts `MD-S040-R0071`, which says evidence export and replay verification must
   preserve the coverage reason code.
3. **Missing expected proof is reported as FAIL, not BLOCKED.** A required file missing throws an
   exception, which becomes `BLOCKED`. A missing expected-proof section is appended as a mismatch
   instead (`ReplayVerificationService.php:1096-1097`), which gives `MISMATCH`, then `FAIL`.
   `MD-S050-R0031` defines `BLOCKED` as "required fixture/runtime/input proof was unavailable",
   and `FAIL` as a comparison that executed and diverged. The result is still fail-closed, but it
   reports a divergence that never happened.

## Guard gaps and rebinds — fixable in tests, no application change

| Predicate | Gap | Remedy |
|---|---|---|
| MD-S050-R0051 | The corpus guard sees prose only. Nothing guards against code using a replay PASS to close a finding, release a quarantine, dismiss a candidate, or satisfy a continuity check. No such consumer exists today: verdict readers are the replay, backfill, evidence and command surfaces. | A static consumer guard with a positive locator, plus a probe |
| MD-S050-R0052 | There is no pattern of its own in `forbidden()`. A sentence claiming "the replay verdict establishes correctness" is not caught. | A dedicated pattern, its claim and denial pair, and an injection probe |
| MD-S036-R0012 | The basis points at the replay-*mode* guard. No test guards the `replay_verify` *request* mode (`MarketDataStageInput:30`, `EodRunRepository:194/221`, `EodBarsIngestService:110`). | A request-mode guard plus a probe |
| MD-S050-R0036 | The basis concerns command invocation, while the predicate concerns stored unmoded results. | Rebind to `B18ReplayEvidenceSelfExplanationTest::test_an_unmoded_result_is_not_admitted_as_citable_evidence`, plus a probe |
| MD-S003-R0021, MD-S005-R0096 | The positive guard only checks that the mapped guard methods *exist*. | Rebind to `B18AsKnownSnapshotIsolationTest::test_a_later_cutoff_exposes_later_revisions_without_rewriting_the_earlier_snapshot`, plus one probe per root |
| MD-S050-R0030 | The positive guard exports a PASS / EXPECTED_DEGRADE record, so FAIL semantics go unexercised. | Rebind to an executed-divergence guard that asserts `replay_status` FAIL, plus a probe |
| MD-S050-R0033 | The negative guard is a text check. | Rebind to the hash-only perturbation in `B18ReplayComparisonExhaustivenessTest::test_a_divergence_in_any_named_assertion_class_denies_pass` (equal row counts, diverged hash), plus a probe |
| MD-S040-R0077 | Only the PASS path is asserted. On mismatch, the run's final reason code survives in `actual_context` (`ReplayVerificationService.php:248`), but no guard asserts it. | Extend the guard to a mismatch case, plus a probe |

## Ownership or interpretation

`MD-S020-R0014` belongs to the data-readiness admission rule in
`Domain_Boundary_Invariants_LOCKED.md`: readiness is admitted only from market-data evidence,
including "immutable publication, lineage, reproducibility, and replay". The B18 corpus guard covers
one narrow sentence shape: a claim that replay by itself is enough to admit readiness. No B18
surface admits readiness. The runtime
readiness service belongs to MD-B17, and release acceptance to MD-B22. Which stage owns the
executable admission rule is a user decision.

## Kept `PROVEN` after review — probe evidence in E010

- MD-S050-R0045, MD-S050-R0050, MD-S002-R0016, MD-S003-R0031, MD-S004-R0011: citation
  boundaries. The corpus instrument fits them, and an injected claim document turned the guard red,
  naming each rule.
- MD-S040-R0070, R0072, R0073, R0074, R0075, R0076, R0078, R0079: each exporter field was nulled
  once, and each time the preservation guard went red.
- MD-S050-R0001: making `ReplayMode::normalize('')` default instead of refusing turned the command
  refusal guard red.
- MD-S003-R0024: disabling the run-identity check on `fixture_source` turned the relabelled-fixture
  guard red. The "independently reviewed" half is a process obligation and is not claimed as
  executable.

## Outside MD-B18, recorded but not acted on

In `BackfillLifecycleOrchestrator::caseStatus` (`:~1599`), a readable case whose
`fixture_status` is `SKIPPED` returns `SUCCESS` before `evidence_status FAILED` is checked. Lines
`~596-600` set `SKIPPED` exactly when evidence export fails. `replay_status` stays `SKIPPED`, so no
replay pass is claimed, but an evidence failure is reported as a successful date. The owner is the
backfill lifecycle (MD-B19). It is carried forward for MD-B19-A001 and is not MD-B18 proof.


## Q5 ownership correction — 2026-09-15T15:22:45.945825+00:00

D005/E018 settle the one ownership question: MD-S020-R0014 primary B22, supporting B18/B17. The entire nine-row parent was reviewed; exactly one matrix row changed, MANDATORY to MANDATORY and NOT_ASSESSED to NOT_ASSESSED. Context includes documentation, implementation and operational readiness. Prior B18 incomplete basis moved to audit-only TRANSFERRED_OWNERSHIP; no B22 proof inherited. This finding remains OPEN for the 14 remaining B18 predicate plans (5 executable defects and 9 guard/rebind gaps); B18 supporting contract/evidence remains an obligation of the approved package. Global required population is unchanged.

## E052: C2 status semantics (MD-S050-R0030/R0031) — first bounded unit — 2026-09-22T21:30:00+07:00

Bounded to exactly the two predicates naming the `BLOCKED`-vs-`FAIL` boundary itself, per explicit
scope instruction. Every other F-015 predicate (the unknown-fixture backfill defect, coverage-reason
preservation, and the guard/rebind-only items) is untouched.

**Defect confirmed by reading current source, not by trusting this finding's own prior description.**
`compareExpectedAndActual()`'s `validateExpectedProofCompleteness()` result was folded into the
ordinary mismatch list via `appendMismatch()`, so a fixture package missing a required
`expected_*` section reached `comparison_result=MISMATCH` and, through
`replayStatusForComparison()`, `replay_status=FAIL` -- identical to a comparison that genuinely
executed and diverged. A pre-existing test,
`ReplayVerificationServiceTest::test_verify_replay_fails_safe_when_expected_proof_is_incomplete`,
had already locked this defective outcome in as an assertion.

**Fix.** `replayAdmissibility()` -- the pre-existing gate that already produces
`NOT_ADMISSIBLE`/`BLOCKED` for a self-generated fixture, an unbound configuration, or an unverified
bound-input context -- gained one further, structurally identical check, checked first: a non-empty
`expected_proof_missing` list now returns its own `REPLAY_EXPECTED_PROOF_INCOMPLETE` reason naming
every missing path. This reuses the existing status mechanism rather than inventing a new one --
`Replay_Verification_Contract_LOCKED.md`'s own `BLOCKED` definition already reads "fixture/runtime/
input proof", covering the fixture-incompleteness case under the same status as the two pre-existing
runtime/input cases. `compareExpectedAndActual()` was not changed: the missing-path mismatch entries
still land in `mismatches`/`mismatch_reason_codes`, unaffected by the admissibility override, so the
exact missing field/path C2 requires stays visible in evidence even though the outer verdict is now
`BLOCKED`.

**Proof.** The pre-existing defective test was corrected in place (kept, not deleted) to assert
`NOT_ADMISSIBLE`/`BLOCKED` while its own missing-path assertion was kept and strengthened. One new
test, `test_admission_blocks_publication_exact_when_required_fixture_proof_is_missing`, proves the
`BLOCKED` side with a genuinely incomplete fixture (a required key actually absent from the JSON) and
a genuinely `VERIFIED` bound context, isolating the missing-proof condition from the fixture's other
two admissibility reasons. The `FAIL` side needed no new test:
`test_admission_reports_fail_not_blocked_when_verified_context_still_diverges` (built in an earlier
F-013 remediation round, unmodified here) already proves complete-proof-plus-divergence reports
`FAIL`/`ADMISSIBLE`, and now doubles as this predicate pair's negative counterpart. Both directions
mutation-proven: disabling the new check turned both `BLOCKED`-side proofs red while the `FAIL`
counterpart stayed green; forcing executed `MISMATCH` to also map to `BLOCKED` turned the `FAIL`
counterpart red while both `BLOCKED`-side proofs stayed green. Both mutations were byte-restored
after, sha256-verified identical before/after, with the control green again and no mutant artifact
left behind.

`MD-S050-R0030` and `MD-S050-R0031` moved `INCOMPLETE` → `PROVEN` in
`MarketDataReplayVerificationProofBasis` (confirmed via `git stash`: the without-basis count moves
from 50 to 48, exactly these two, nothing else). No traceability-matrix `coverage_status`/
`SATISFIED`/denominator change.

Targeted suites green: `ReplayVerificationServiceTest` 20/20, `ReplayComparisonDetectsDivergenceTest`
29/29 (unmodified, unaffected -- it exercises `compareExpectedAndActual()` directly and never reaches
`replayAdmissibility()`), plus eleven other replay-consumer suites (`B18ReplayComparisonExhaustivenessTest`,
`ReplayEvidenceExportServiceTest`, `ReplayBackfillServiceTest`, `ReplaySmokeSuiteServiceTest`,
`FullRangeCurrentEvidenceReplayServiceTest`, `BackfillLifecyclePublicationReprocessTest`,
`BackfillMissingTickerLifecycleTest`, `OpsCommandSurfaceTest`, `ReplayMismatchClassificationTest`,
`ReplayDeterminismStaticGuardTest`) all green, confirming no collateral impact on fixtures that
already carry complete proof. Governance self-tests 12/12. Full suite not run, not required for a
one-pair bounded unit.

**This finding remains `OPEN — REMEDIATION_IN_CONSOLIDATED_PACKAGE`.** 12 of its own 14 predicates
remain `INCOMPLETE`. `MD-DEP-0017` remains `BLOCKING`. Next bounded unit: the unknown-fixture-case
defect in `ReplayBackfillService.php:68` and `MD-S050-R0053`/`MD-S002-R0009`/`MD-S002-R0010`, which
also needs re-checking that a now-correctly-`BLOCKED` date is treated as failed for every named
fixture case, not only the previously-unhandled unknown one.

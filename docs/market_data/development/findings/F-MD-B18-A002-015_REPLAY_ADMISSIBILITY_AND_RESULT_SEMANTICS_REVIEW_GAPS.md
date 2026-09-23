# Finding — replay admissibility and result-semantics review gaps

- ID: `F-MD-B18-A002-015`
- Stage / Attempt / Baseline / Epoch: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001` / `MD-REBASELINE-20260820-001`
- Raised: 2026-09-14T14:02:47+07:00 (system clock)
- Closed: 2026-09-23T10:50:41+07:00 (E-MD-B18-A002-057 issuance)
- Severity: `P1` for closure. It contained executable defects and proof bases that overclaimed.
- Status: `RESOLVED — CONSOLIDATED_REMEDIATION_PACKAGE_COMPLETE`
- Class: `EXECUTABLE_DEFECT_AND_PROOF_BASIS_MISTARGETED`
- Found by: per-predicate review of PAIRS 02, 03, 04, 06, 07 and 08 (30 predicates)
- Dependency: `MD-DEP-0017` (consolidated remediation review) — not resolved by F-015 closure; still BLOCKING

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

## E053: backfill fixture-case semantics (MD-S050-R0053/MD-S002-R0009/MD-S002-R0010) — second bounded unit — 2026-09-22T22:30:00+07:00

Bounded to exactly the three predicates the prior audit turn named for this unit. G04, G07, G08, G09
and every other F-015 predicate are untouched.

**Defect confirmed by reading current source.** `ReplayBackfillService::expectedOutcomeForFixtureCase()`
returned `null` for any `--fixture_case` outside its four-entry map, and
`$passed = $expectedOutcome ? $observedOutcome === $expectedOutcome : true` turned that into an
automatic pass for every date in the requested range -- reached only *after* the full replay/export
work already ran for each one. `ReplayBackfillCommand.php` was confirmed to carry no independent
`--fixture_case` validation of its own, so the fix belongs in `execute()` alone.

**Fix.** A single `KNOWN_FIXTURE_CASES` constant is now the sole source of truth for both a new
pre-execution rejection (`execute()`'s first action, before `guardDateRange()`, before any
filesystem/calendar work, before the date loop) and `expectedOutcomeForFixtureCase()`'s own lookup --
the two notions of "known" can never diverge because there is only one list. An unrecognised case is
refused outright, matching C2's "tolak sebelum bekerja; tidak memilih pointer atau menebak expected
outcome" literally: no outcome is guessed, no partial work happens for any date.

**Known-case comparison, re-verified rather than assumed to need a change.** `$observedOutcome ===
$expectedOutcome` already correctly fails for all four named cases (`MATCH`, `MISMATCH`, and the
exception-path `ERROR` sentinel) when the actual outcome is the `NOT_ADMISSIBLE` value
`E-MD-B18-A002-052` introduced, since none of those three strings equals `NOT_ADMISSIBLE` -- proven
directly with a `reason_code_mismatch_case` fixture whose mocked `verifyRunAgainstFixture()` call
returns exactly that shape. No production change was needed for this half. **No current named fixture
case expects `BLOCKED`/`NOT_ADMISSIBLE`** (the map's only values are `MATCH`/`MISMATCH`/`ERROR`), so
C2's "fixture negatif memang mengharapkan ERROR/BLOCKED" row has nothing to implement here; inventing
such a case was explicitly out of scope and not done.

**Sibling consumers, confirmed not reused.** `ReplaySmokeSuiteService` iterates a hardcoded internal
case map that arbitrary input cannot reach; `FullRangeCurrentEvidenceReplayService::casePassed()` is
a positive allowlist requiring `MATCH`+`PASS`+zero mismatches+both admission states
`ADMITTED_COMPLETE`. Both re-confirmed structurally immune to this defect, matching the prior audit
turn's finding; neither touched.

**Proof.** Two new tests added to the previously two-method `ReplayBackfillServiceTest.php`:
`test_execute_rejects_an_unknown_fixture_case_before_any_work` (Mockery `shouldNotReceive` on the
calendar, publication repository, replay service and evidence exporter, proving zero work occurs, not
merely that an exception is thrown) and
`test_execute_does_not_count_a_blocked_replay_as_passed_for_a_mismatch_expecting_case` (a
real-shaped mocked `verifyRunAgainstFixture()` return matching E-052's `NOT_ADMISSIBLE`/`BLOCKED`
output, asserting `passed=false`, `all_passed=false`, and `replay_status` preserved as `BLOCKED` in
the persisted case record). Both pre-existing tests were left completely unmodified and re-confirmed
green. Both new tests mutation-proven: removing the unknown-case rejection turned that proof red
while the others stayed green; forcing `$passed=true` unconditionally turned the `BLOCKED`-not-passed
proof red while the others stayed green. Both mutations byte-restored after, sha256-verified identical
before/after, control green again, no mutant artifact left behind.

`MD-S050-R0053`, `MD-S002-R0009` and `MD-S002-R0010` moved `INCOMPLETE` → `PROVEN` in
`MarketDataReplayVerificationProofBasis` (confirmed via `git stash`: without-basis count 48 → 45,
exactly these three). No traceability-matrix `coverage_status`/`SATISFIED`/denominator change.

Targeted suites green: `ReplayBackfillServiceTest` 4/4, `OpsCommandSurfaceTest` 64/64 (confirms no
collateral impact on the CLI command surface). Governance self-tests 12/12. Full suite not run, not
required for a three-predicate bounded unit.

## E054: G04 coverage-reason preservation (MD-S040-R0071/MD-S040-R0077) — third bounded unit — 2026-09-23T00:10:00+07:00

Bounded to exactly the two predicates the prior audit turn named for this unit (G04). G07, G08, G09
and every other F-015 predicate are untouched.

**Defect confirmed by reading current source.** `CoverageGateEvaluator::evaluateCaptured()` and
`notEvaluableResult()` emit a `coverage_reason_code` field with exactly three values
(`RUN_COVERAGE_NOT_EVALUABLE`/`COVERAGE_THRESHOLD_MET`/`RUN_COVERAGE_LOW`), each in strict
one-to-one correspondence with `coverage_gate_state`'s three values -- confirmed by reading both
methods in full, which rules out a same-state/different-reason collision as constructible from this
field's real vocabulary and distinguishes it from the broader five-value `reason_code`/`reason_codes`
fields this finding's item 2 more loosely described as "more distinct codes than can be reconstructed
from the gate state alone." `Manual_File_Publishability_Policy_LOCKED.md` section 7 lists "coverage
reason code" as its own sibling item beside "coverage gate state," confirming the literal field is
this predicate's exact scope. No `coverage_reason_code` column existed in the base SQL, any
migration, or the SQLite mirror. `MarketDataEvidenceExportService::buildCoverageState()`/
`buildExpectedCoverageState()` and an independent second copy of the identical logic in
`ReplayVerificationService::buildActualReplayState()` both called
`resolveCoverageReasonCodeFromState(coverageGateState)`, which returns `COVERAGE_BELOW_THRESHOLD` for
`FAIL` -- a value the producer never emits for this field (it emits `RUN_COVERAGE_LOW`) -- confirmed
live in `B18ReplayEvidencePreservationContractTest`'s own pre-existing fixture and comment, which had
explicitly documented and asserted the wrong reconstructed value as correct behaviour. This is a
genuine correctness bug, not only a fidelity loss.

**Fix.** `coverage_reason_code VARCHAR(64) NULL` added to `eod_runs`;
`coverage_reason_code`/`expected_coverage_reason_code VARCHAR(64) NULL` added to
`md_replay_daily_metrics` (new migration, base SQL, SQLite mirror, applied to both `tradeaxis` and
`tradeaxis_testing`). `MarketDataPipelineService::completeCoverageEvaluation()`/`completeEligibility()`
now persist the evaluator's exact value verbatim. `MarketDataEvidenceExportService` and
`ReplayVerificationService` now read the persisted column instead of reconstructing;
`ReplayResultRepository` persists both the actual and expected sides -- the expected side's exact
fixture-declared value was already computed into `$comparison['expected_coverage_reason_code']`
before this unit but discarded at the repository boundary for lack of a column.
`resolveCoverageReasonCodeFromState()` is kept in both classes for their other, out-of-scope callers
(the exporter's `publication_reason_code`/`pointer_switch_reason_code` fallback chain; a
legacy-fixture default-fill in `ReplayVerificationService::buildExpectedContext()` unreachable for
any fixture meeting the existing required-proof check) -- neither is this predicate's scope.

**Historical rows.** No authority addresses retroactive reconstruction for this field. Per the
explicit instruction against inventing semantics where unresolved, a pre-migration row reads back
`NULL` on every path, never a reconstruction: one legacy-fixture test was re-pointed from asserting
the old reconstructed `RUN_COVERAGE_NOT_EVALUABLE` to asserting `NULL`.

**MD-S040-R0077** needed no production change of its own -- this finding's own table classified it as
a guard gap, not an executable defect. Its guard was extended from the PASS-only path to a genuine
MISMATCH case, proving `final_reason_code` survives a real divergence rather than only an
unchallenged echo. `MD-S040-R0071`'s fix incidentally strengthens its existing fallback chain
(`run->final_reason_code ?? run->source_final_reason_code ?? coverageReasonCode`) from an
approximated to an exact value, though the new guard exercises an explicitly-set value, not that
fallback path.

**Proof.** Three production mutations proven and byte-restored with sha256 verification: reverting
the exporter's reconstruction turned `B18ReplayEvidencePreservationContractTest`'s main preservation
test red (and, independently, two `MarketDataEvidenceExportServiceTest` cases, one on the wrong FAIL
value, one on the historical-NULL rule); reverting the replay-actual reconstruction turned
`ReplayVerificationServiceTest`'s mismatch test red on its actual-side `coverage_reason_code`
assertion; removing the writer's telemetry key (on `completeEligibility()` only, the sibling call
site left untouched) turned `MarketDataPipelineServiceTest`'s strengthened matcher red. Every other
case in each suite stayed green throughout.

**Validation.** One contaminated full-suite/pipeline-integration round was discarded after being
traced to mutation-testing edits racing a still-running background test process (confirmed via
`ProducerRegistrySnapshot`'s in-process build-drift guard correctly firing
`INPUT_CAPTURE_BUILD_EXECUTABLE_DRIFT`); both re-run start-to-finish with no concurrent edits. The
first clean full run (2420/34177/0 errors/10 failures) surfaced three genuine regressions this unit
caused -- `EvidenceExportCompletenessStaticGuardTest`'s `RUN_COVERAGE_STORAGE_EXPORT_PATHS`
declaration gap, and two `B18ReplayComparisonExhaustivenessTest` default-fixture tests whose shared
run-row helper needed the matching field -- all three fixed. Final clean run: 2420 tests, 34194
assertions, 0 errors, 7 failures, all seven the pre-existing `ProductionCorpusInvariantOracleTest`
corpus-data-absence baseline, none related to this unit.

`MD-S040-R0071` and `MD-S040-R0077` moved `INCOMPLETE` → `PROVEN` in
`MarketDataReplayVerificationProofBasis` (confirmed via `git stash`: without-basis count 45 → 43,
exactly these two, and the proof self-test's own pre-existing overall `FAIL` status is identical
before and after -- not a regression). No traceability-matrix `coverage_status`/`SATISFIED`/
denominator change.

**This finding remains `OPEN — REMEDIATION_IN_CONSOLIDATED_PACKAGE`.** 7 of its own 14 predicates
remain `INCOMPLETE`: `MD-S050-R0051`/`MD-S050-R0052` (G08, consumer/admission claim scan);
`MD-S036-R0012`/`MD-S050-R0036`/`MD-S003-R0021`/`MD-S005-R0096` (G09, rebind to already-existing
executing guards); `MD-S050-R0033` (G07, hash-only divergence perturbation rebind). `MD-DEP-0017`
remains `BLOCKING`. Next bounded unit, per canonical C2 guard-scan ordering: G07 or G09.

## E055: G07 hash-only divergence perturbation rebind (MD-S050-R0033) — 2026-09-23T03:15:00+07:00

Bounded to exactly one predicate: `MD-S050-R0033` only. All other F-015 predicates untouched.

**Requirement confirmed.** `MD-S050-R0033` from Replay_Verification_Contract_LOCKED.md:64: "Command exited successfully" or matching row counts alone is not replay proof.

**Proof-only rebind; no production/test code changes.** Existing executing guard `B18ReplayComparisonExhaustivenessTest::test_a_divergence_in_any_named_assertion_class_denies_pass` already exercises the exact requirement: three independent hash perturbations (bars_batch_hash, indicators_batch_hash, eligibility_batch_hash changed to new values while row counts bars_rows_written, indicators_rows_written, eligibility_rows_written remain equal to their baseline), each producing MISMATCH verdict instead of PASS, proving equal row counts cannot establish equivalence.

**Rebinding.** `MD-S050-R0033` was incorrectly bound to inadmissible-verdict tests in the old proof basis. Moved from `INCOMPLETE` array to `PROVEN` array in `MarketDataReplayVerificationProofBasis` with correct test references: positive = `B18ReplayComparisonExhaustivenessTest::test_the_unperturbed_fixture_passes` (control fixture matching); negative = `B18ReplayComparisonExhaustivenessTest::test_a_divergence_in_any_named_assertion_class_denies_pass` (hash divergences with equal counts producing MISMATCH).

**Validation.** Targeted test execution: `B18ReplayComparisonExhaustivenessTest::test_a_divergence_in_any_named_assertion_class_denies_pass` 14 parameterized tests, 56 assertions, OK. Proof self-test: `MD-S050-R0033` no longer reported as `PREDICATE_WITHOUT_REVIEWED_BASIS`. Proof basis count: 71 → 72 PROVEN entries; global INCOMPLETE count 43 → 42, confirmed via PHP parse of updated basis.

`MD-S050-R0033` moved `INCOMPLETE` → `PROVEN`. No traceability-matrix `coverage_status`/`SATISFIED`/denominator change.

**This finding remains `OPEN — REMEDIATION_IN_CONSOLIDATED_PACKAGE`.** 6 of its own 14 predicates
remain `INCOMPLETE`: `MD-S050-R0051`/`MD-S050-R0052` (G08); `MD-S036-R0012`/`MD-S050-R0036`/
`MD-S003-R0021`/`MD-S005-R0096` (G09). `MD-DEP-0017` remains `BLOCKING`. 

**Next bounded unit per consolidated remediation package canonical ordering:** G08 (`MD-S050-R0051` and `MD-S050-R0052` together, consumer/admission claim scan requiring new static guard + pattern guard).

## E056: G08 consumer/admission claim scan (MD-S050-R0051/MD-S050-R0052) — 2026-09-23T04:20:00+07:00

Bounded to exactly two predicates: `MD-S050-R0051` and `MD-S050-R0052`, reviewed and proven
independently rather than bulk-promoted. All other F-015 predicates untouched.

**Requirements reconstructed from current authority, not from this table's paraphrase.**
`STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv` against `Replay_Verification_Contract_LOCKED.md`
lines 99-100, section "Admissibility of a PASS (LOCKED)": `MD-S050-R0051` — "A replay `PASS` may not
close a data-quality finding, release a quarantine, dismiss a corporate-action candidate, or satisfy
a continuity check." `MD-S050-R0052` — "Where an audit claim requires correctness, the admissible
evidence is independent — verified event terms, source reconciliation, or exchange-published facts —
not a replay verdict." R0051 concerns executable consumer behaviour; R0052 concerns what a claim may
cite.

**Consumer scan performed before any guard was written.** `app/` grepped for `replay_status` and
`comparison_result`, the two verdict field names, yielding exactly 13 files: `BackfillLifecycleOrchestrator`,
`FullRangeCurrentEvidenceReplayService`, `MarketDataEvidenceExportService`, `ReplayBackfillService`,
`ReplaySmokeSuiteService`, `ReplayVerificationService`, six command classes, and `ReplayResultRepository`
— matching this finding's own earlier review ("verdict readers are the replay, backfill, evidence and
command surfaces"). All 13 read directly: zero occurrences of finding/quarantine/corporate-action-candidate/
continuity vocabulary outside comments. Every `candidate` occurrence in this surface is a publication or
correction candidate (the immutable publication state machine's own vocabulary), never a corporate-action
candidate. The only verdict consumption found is telemetry — a counter increment, a diagnostic
case-pass flag — never a downstream action on a finding, quarantine, candidate, or continuity check.
**Implementation already correct; no production code changed for either predicate.**

**R0051 remedy.** New test file `B18ReplayVerdictConsumerBoundaryTest.php`. `REVIEWED_VERDICT_CONSUMERS`
is a positive-locator, fail-closed enumeration of the 13 files, asserted equal (both directions) to
the set actually derived by scanning `app/` for the two verdict fields — a file added later that reads
one fails this closed until reviewed and added. Each reviewed file is then scanned for the rule's four
prohibited actions using bidirectional verb/noun proximity patterns (verb-then-subject or
subject-then-verb, since a real call site as often reads `$this->quarantine->release(...)` as
`releaseQuarantine(...)`), so the OHLC `close` price field and the publication/correction `candidate`
vocabulary already present throughout this exact surface cannot false-positive — proven directly
against the real files, not assumed.

**R0052 remedy.** `forbidden()` in `B18ReplayAdmissibilityBoundaryTest` carried no pattern of its own
for this predicate. One was added, deliberately phrased from the rule's own named alternative
(`admissible|independent` evidence) rather than from the paraphrase this very table already quotes
verbatim for this predicate ("the replay verdict establishes correctness") — reusing that wording
would have made the new pattern fire on this finding's own live, unexcluded document. Corpus-grepped
before writing the pattern to confirm zero collision with any existing sentence, including this one.

**Falsifiability.** Four live mutation/restore probes, each byte-restored from a pre-mutation copy and
sha256-verified identical before/after, each control re-run green: injecting a bare `replay_status`
token into an unreviewed file (`PriceScaleBreakDetectionService.php`) turned the positive-locator
drift guard red; injecting a real prohibited call (`$this->findingRepository->closeFinding(...)`)
into a reviewed consumer (`ReplayVerificationService.php`) turned the action-scan red, naming the
exact file and action; a synthetic-sample test independently proves all four R0051 patterns fire
without needing a file mutation; injecting the exact R0052 claim sentence into a live scanned file
(`CURRENT_STATE.md`) turned the corpus scan red, naming the injected file and matched span, while this
finding's own quoted example of the same underlying claim — phrased differently — stayed correctly
unflagged throughout.

`MD-S050-R0051` and `MD-S050-R0052` moved `INCOMPLETE` → `PROVEN`, reviewed and bound independently
(separate positive/negative/basis entries, not a bulk promotion). Proof basis: `PROVEN` 72 → 74,
`INCOMPLETE` 42 → 40, confirmed via PHP parse. Proof self-test no longer lists either predicate under
`PREDICATE_WITHOUT_REVIEWED_BASIS` (baseline remains its own pre-existing `FAIL`, unchanged, driven
only by the other genuinely-incomplete predicates — not a regression). No traceability-matrix
`coverage_status`/`SATISFIED`/denominator change.

Targeted suites green: `B18ReplayVerdictConsumerBoundaryTest` 5/5 (48 assertions),
`B18ReplayAdmissibilityBoundaryTest` 4/4 (10 assertions), plus sanity re-runs of unrelated consumer
suites — `ReplayVerificationServiceTest` 20/20, `OpsCommandSurfaceTest` 64/64,
`B18ReplayComparisonExhaustivenessTest` 39/39 — all green, confirming zero collateral impact from
test-tooling-only changes. Governance self-tests green:
`GovernanceGateReadOnlyExecutionTest`+`ScopeBoundaryAndOrchestrationCompletionTest` 9/9,
`FindingRecordConsistencyTest` 3/3. Full application suite not run, not required — only test files
changed, no production/schema/migration touched.

**This finding remained `OPEN — REMEDIATION_IN_CONSOLIDATED_PACKAGE`** after G08, with 4 of its own
14 predicates `INCOMPLETE`, all `G09`: `MD-S036-R0012`/`MD-S050-R0036`/`MD-S003-R0021`/`MD-S005-R0096`.

## E057: G09 final unit (MD-S036-R0012/MD-S050-R0036/MD-S003-R0021/MD-S005-R0096) — 2026-09-23T10:50:41+07:00

**An earlier, uncommitted attempt at this same unit was invalid and was discarded before commit.**
That draft moved all four predicates from `INCOMPLETE` to `PROVEN` byte-for-byte, with no new guard,
no rebind, and no probe — directly contradicting this finding's own "Guard gaps and rebinds" table,
which names a specific remedy for each: "A request-mode guard plus a probe" for `MD-S036-R0012`;
"Rebind ... plus a probe" for `MD-S050-R0036`; "Rebind ... plus one probe per root" for
`MD-S003-R0021`/`MD-S005-R0096`. The draft was found before commit, its tracked-file changes were
restored to the last valid HEAD (the G08/E056 commit), and its untracked evidence file was deleted.
This section records the unit actually done.

**`MD-S036-R0012` — "Allowed request modes: `replay_verify`".** The old basis pointed at the
`ReplayMode` (`PUBLICATION_EXACT`/`AS_KNOWN`) guard, a domain distinct from the `eod_runs.request_mode`
vocabulary this predicate governs (`MarketDataStageInput::ALLOWED_REQUEST_MODES`, enforced by
`MarketDataPipelineService::assertAllowedRequestMode()`). `RequestModeVocabularyTest` already invoked
that real method behaviourally, but its positive test's `dataProvider` derives its cases from
`ALLOWED_REQUEST_MODES` itself — confirmed live: removing `replay_verify` from the array did not fail
the guard, it silently shrank the `dataProvider` by one case (16/16 green instead of a failure naming
`replay_verify`). A new, independent test,
`test_replay_verify_specifically_is_an_accepted_request_mode`, asserts the literal string against the
real method, not a value read from the array it verifies. Re-probed under the identical mutation: the
new test turns red with `REQUEST_MODE_INVALID` naming exactly the removed mode; byte-restored,
sha256-verified identical, control green (17/17).

**`MD-S050-R0036` — "A result carrying no mode ... is unclassified ... may not be cited as either".**
The old basis concerned command invocation, while this predicate concerns a stored, already-unmoded
result — the read side of the rule, for a corpus row predating `MD-S050-R0035`'s write-time mandate.
Rebound to `B18ReplayEvidenceSelfExplanationTest::test_an_unmoded_result_is_not_admitted_as_citable_evidence`,
which forces `replay_mode=null` onto a real exported metric row and asserts the real (unmocked)
`MarketDataEvidenceExportService::exportReplayEvidence` marks the pack `ADMITTED_INCOMPLETE` naming
`replay_mode_invalid_or_historical_unclassified`, paired with
`test_every_exported_pack_requires_and_carries_the_mode_that_produced_it` as the positive control (a
correctly-moded result is `ADMITTED_COMPLETE`). Mutation-proven: removing the missing-section append
turned exactly that assertion red; byte-restored, sha256-verified identical, control green (14/14).

**`MD-S003-R0021`/`MD-S005-R0096` — later master/event/status/calendar/config/formula/factor
revisions invisible before their recorded/known times; as-known replay excludes later revisions.**
The finding's own remedy table names the defect precisely: "The positive guard only checks that the
mapped guard methods *exist*" — `test_every_later_revision_kind_is_bound_to_an_executing_guard`
performs a string search for `function <name>(` in a file, proving nothing about the method's body.
Rebound to `test_a_later_cutoff_exposes_later_revisions_without_rewriting_the_earlier_snapshot`, a
behavioural guard using real repositories and real seeded DB rows (no mocks) for all seven
contract-named roots in one real `AsKnownReplaySnapshotService::capture()` call. **Seven independent,
isolated discriminating probes**, one per root, each byte-restored and sha256-verified before the
next began: master and status and calendar and config (each root's cutoff argument forced to a future
date, leaking a later-recorded revision into the early capture — four distinct assertions, all
caught); formula (indicator config read from live `config()` instead of the frozen snapshot payload —
caught independently of the config probe, which passed under this same mutation, proving the two are
separately guarded); event and factor (each root's `recorded_at` filter removed — two distinct
assertions, all caught). The negative guard,
`test_an_incomplete_historical_config_snapshot_is_refused_instead_of_using_live_config`, uses a real
repository and a real corrupted DB row (not a mock); independently re-probed by removing the refusal
and substituting a live-config fallback, which turned it red rather than silently succeeding.
`MD-S005-R0096` was reviewed independently rather than assumed proven merely because it shares a
guard with `R0021`; both name the same underlying rule (as-known exclusion of later revisions) and
both are established by the same nine probes.

`MD-S036-R0012`/`MD-S050-R0036`/`MD-S003-R0021`/`MD-S005-R0096` moved `INCOMPLETE` → `PROVEN`,
reviewed and bound independently. Proof basis: `PROVEN` 74 → 78, `INCOMPLETE` 40 → 36, confirmed via
PHP parse. Proof self-test re-run: baseline's failure shape unchanged (driven only by predicates from
other, still-open findings — not a regression), and none of the four `G09` predicates appear in its
`PREDICATE_WITHOUT_REVIEWED_BASIS` list any more. No traceability-matrix
`coverage_status`/`SATISFIED`/denominator change. **No production application code changed** — every
mutation in this unit was a probe, restored byte-for-byte and sha256-verified before the next began;
one new independent test method was added
(`RequestModeVocabularyTest::test_replay_verify_specifically_is_an_accepted_request_mode`).

Targeted suites green: `RequestModeVocabularyTest` 17/17 (22 assertions),
`B18ReplayEvidenceSelfExplanationTest` 14/14 (143 assertions), `B18AsKnownSnapshotIsolationTest` 3/3
(27 assertions). Governance self-tests green:
`GovernanceGateReadOnlyExecutionTest`+`ScopeBoundaryAndOrchestrationCompletionTest` 9/9,
`FindingRecordConsistencyTest` 3/3. Full application suite not run, not required — production code is
byte-identical to before this unit.

**`F-MD-B18-A002-015` is now formally `RESOLVED`.** All 14 of its own predicates are `PROVEN`:
`MD-S050-R0053`/`MD-S002-R0009`/`MD-S002-R0010` (unknown-fixture-case rejection, E053),
`MD-S040-R0071`/`MD-S040-R0077` (coverage reason code preservation, E054),
`MD-S050-R0030`/`MD-S050-R0031` (`BLOCKED`-vs-`FAIL` status semantics, E052), `MD-S050-R0033` (hash
divergence rebind, E055), `MD-S050-R0051`/`MD-S050-R0052` (consumer/admission claim scan, E056), and
`MD-S036-R0012`/`MD-S050-R0036`/`MD-S003-R0021`/`MD-S005-R0096` (this unit, E057). Consolidated
remediation package canonical closure (G04/G07/G08/G09) is complete for this finding. `MD-DEP-0017`
remains `BLOCKING` — it is not resolved by F-015 closure and bundles F-013 (`RESOLVED`) through F-018
(`F-016`/`F-017`/`F-018` remain `OPEN`).

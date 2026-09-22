# Finding — rerun-determinism guards re-export instead of rerunning

- ID: `F-MD-B18-A002-014`
- Stage / Attempt / Baseline / Epoch: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001` / `MD-REBASELINE-20260820-001`
- Raised: 2026-09-14T11:23:30+07:00 (system clock)
- Severity: `P1` for closure — the proof basis overclaims; no application defect has been
  established
- Status: `RESOLVED` (E051 rebound all four predicates to guards that genuinely exercise the sense
  each requires -- `MD-S019-R0073`/`MD-S005-R0095` (replay sense) to two new
  `ReplayVerificationServiceTest` tests calling `ReplayVerificationService::verifyRunAgainstFixture()`
  twice through genuinely separate instances/mocks; `MD-S003-R0004` (rebuild sense) and
  `MD-S019-R0009` (both senses) to a pre-existing, already-green, real-SQLite-pipeline test pair in
  `MarketDataPipelineIntegrationTest` discovered by cross-referencing the predicates against the
  existing test corpus rather than assumed absent. No production semantic change: an exploratory
  probe confirmed the system already behaves correctly -- an unchanged rebuild reruns to identical
  batch hashes and does not silently promote a second, unauthorized run as current.)
- Class: `PROOF_BASIS_MISTARGETED`
- Found by: per-predicate review of PAIR 05 and PAIR 20 (`B18ReplayRerunDeterminismTest`)

## What the predicates require

| Predicate | Requirement |
|---|---|
| `MD-S003-R0004` (Historical_Replay_and_Data_Quality_Backtest.md:13) | "prove an unchanged rerun is byte-identical and does not create a fake correction", within the exact-publication scenario family |
| `MD-S005-R0095` (Audit_Hash_and_Reproducibility_Contract_LOCKED.md:153, required proof 5) | "exact publication replay reproduces hashes" |
| `MD-S019-R0073` (Determinism_Invariants_LOCKED.md:120) | if replay uses identical bound inputs, replay reproduces identical outputs and hashes |
| `MD-S019-R0009` (Invariant 1) | the three batch hashes stay identical "across reruns and replay" |

The same contract, MD-S005 L140, says what a rerun is: "An unchanged rebuild with identical stable
inputs/versions produces identical artifact hashes and must not create a fake corrected
publication."

## What the guards do

`B18ReplayRerunDeterminismTest::export()` (lines 199–212) mocks `findReplayMetric` to return one
fixed record, then calls `MarketDataEvidenceExportService::exportReplayEvidence`. Each "rerun" in
the class calls `export()` again on that same record:

- `test_an_unchanged_rerun_produces_byte_identical_artifacts` compares the bytes of the two exports.
- `test_a_changed_bound_input_changes_the_bytes` and
  `test_each_batch_hash_is_individually_load_bearing_across_a_rerun` show that
  `replay_result.json` changes when a field of the record changes.
- `test_a_rerun_writes_no_publication_and_opens_no_correction` shows that the exporter writes
  neither.

Nothing in the class runs the pipeline, a seal, a promotion or `ReplayVerificationService`.

What these guards **do** prove, and remain valuable for: exporter determinism, each batch hash
carried into the replay artifact, and an exporter that writes no publication or correction.

What they **do not** prove: that rebuilding an unchanged date yields identical batch hashes and no
fake correction, or that replaying a publication reproduces its hashes.

## Verdict

`MD-S019-R0073`, `MD-S003-R0004`, `MD-S005-R0095` and `MD-S019-R0009` move to `INCOMPLETE`.

## Remediation — guard redesign

1. **Rebuild guard (SQLite pipeline).** Promote one unchanged fixture date twice. Assert that
   `bars_batch_hash`, `indicators_batch_hash` and `eligibility_batch_hash` are identical, and that
   the second run creates no new publication or correction, or is an explicit idempotent no-op.
   Probes: an unordered row set or a wall-clock value injected into a hashed payload turns it red;
   a correction opened on the rerun turns it red.
2. **Replay-rerun guard.** Run `ReplayVerificationService` twice against the same publication and
   fixture. Assert identical comparison results, bound identities and batch hashes. `replay_id`
   and `created_at` are the declared volatile fields.
3. **Rebinding.** Rebind the four predicates to these guards. Keep the existing guards as
   supporting evidence for exporter determinism.

The list of fields that may legitimately differ between two replay reruns is a contract reading
(MD-S003 says "byte-identical"), so the user confirms it together with F013's contract. If
executing either guard exposes a real non-determinism or a fake correction, that becomes an
executable defect with its own contract step.

## Remediation — E051, 2026-09-22T20:00:00+07:00

**Authority re-read before any code/test change**, per instruction, not trusted from this finding's
own prior summary alone:

- `MD-S003-R0004` (`Historical_Replay_and_Data_Quality_Backtest.md:13`): "prove an unchanged rerun is
  byte-identical and does not create a fake correction" -- under the "Exact publication verification"
  scenario family.
- `MD-S005-R0095` (`Audit_Hash_and_Reproducibility_Contract_LOCKED.md:153`, required proof 5): "exact
  publication replay reproduces hashes."
- `MD-S019-R0073` (`Determinism_Invariants_LOCKED.md:120`, Invariant 14 consequent): "then replay must
  reproduce identical outputs and identical hashes" -- replay sense only.
- `MD-S019-R0009` (`Determinism_Invariants_LOCKED.md:18`, Invariant 1): identical semantic content
  implies `bars_batch_hash`/`indicators_batch_hash`/`eligibility_batch_hash` identical, "across
  reruns **and** replay" -- both senses.
- The same document's "Correction and rerun rules" section (line ~139) states the general rule
  precisely: "An unchanged rebuild with identical stable inputs/versions produces identical artifact
  hashes and must not create a fake corrected publication." This is the **rebuild** sense (the
  production pipeline, not the replay-verification subsystem) -- a different mechanism from `MD-S005`'s
  required-proof-5, which is explicitly the **replay** sense.

**These four predicates therefore split into two genuinely different senses, not one ambiguous
"rerun":**

| Sense | Predicates | What must actually execute twice |
|---|---|---|
| Replay | `MD-S019-R0073`, `MD-S005-R0095` | `ReplayVerificationService::verifyRunAgainstFixture()` |
| Rebuild | `MD-S003-R0004` | `MarketDataPipelineService`'s real promotion path |
| Both | `MD-S019-R0009` | Both of the above |

This reading is `ALREADY_DECIDED` by the contract text itself (two named mechanisms, two named
document sections) -- not a new interpretation invented here, and not the "invent rerun semantics
from a method name" trap the user's instruction explicitly warned against: neither guard was chosen
because a method name looked plausible; both were chosen because the exact contract sentence names
the exact subsystem.

**Current guard, re-confirmed defective by direct reading, not by trusting this finding's own prior
summary:** `B18ReplayRerunDeterminismTest::export()` calls
`MarketDataEvidenceExportService::exportReplayEvidence()` on one hand-fabricated
`md_replay_daily_metrics` object, twice. Grepping the whole file for
`ReplayVerificationService`/`verifyRunAgainstFixture`/`MarketDataPipelineService`/`runDaily` returns
zero matches. Confirmed: nothing in it reruns anything; it re-exports a static value.

**What "rebuild determinism" concretely means was proven by direct observation, not assumed.** An
exploratory probe (`MarketDataPipelineService::runDaily()` called twice, independently, for the same
unchanged trade date, with no correction request) showed: the second call gets a genuinely new
`run_id`; both runs compute the **identical** `bars_batch_hash`; the second run's candidate
publication is sealed but never becomes current (`is_current = 0`); the run is held
`terminal_status = HELD`, `publishability_state = NOT_READABLE`, `final_reason_code =
RUN_LOCK_CONFLICT` (a promotion-safety refusal inside `PublicationFinalizeOutcomeService`, not an
error). This is the "explicit idempotent no-op" this finding's own remediation text anticipated as an
acceptable alternative to "no new publication at all" -- the system already does the right thing; no
production change was needed or made. The probe itself was discarded (a throwaway diagnostic, not a
permanent test) once its purpose was served.

**A pre-existing, already-green, real-SQLite-pipeline test pair was found to already prove the rebuild
sense**, by cross-referencing `MarketDataPipelineIntegrationTest`'s existing correction-workflow tests
against these predicates rather than assuming none existed:
`test_run_daily_correction_with_unchanged_artifacts_cancels_request_and_preserves_current_publication`
reruns the real pipeline through the explicit correction workflow with byte-identical input, asserts
all three batch hashes identical to the baseline, and asserts the correction record is explicitly
`CONSUMED_CURRENT` (no publication_version switch, `published_at` stays null) -- an unchanged rebuild
is recorded as **not** a real correction, never silently promoted as one, which is the literal "does
not create a fake correction" clause. Its immediate neighbour,
`test_run_daily_correction_replaces_current_publication_and_marks_correction_published`, is the
negative counterpart already in the same file: genuinely different content produces a genuinely
different `publication_version` and an explicit `CORRECTION_PUBLISHED` outcome. **No new test was
needed for the rebuild sense; only the proof-basis rebind was outstanding.**

**The replay sense had no existing test at all** (confirmed by grep across `tests/`: nothing calls
`verifyRunAgainstFixture()` twice against the same publication/fixture). Two new targeted tests were
added to `ReplayVerificationServiceTest.php`:

1. `test_replaying_the_same_unchanged_publication_twice_persists_byte_identical_results_except_execution_identity`
   -- calls `verifyRunAgainstFixture()` twice, each time through a genuinely separate
   `ReplayVerificationService` instance and a genuinely separate set of Mockery mocks (not the same
   object reused, and the first call's return value is never fed into the second as its actual),
   configured to represent the same stable underlying run/publication/fixture. Every field of the
   persisted metric is asserted identical between the two calls except `replay_id`, which is asserted
   to **differ** (each independent execution mints its own identity via its own `nextReplayId()` mock
   expectation) -- proving two genuinely separate executions were compared, not one execution compared
   with itself.
2. `test_a_genuine_input_divergence_between_two_independent_replays_is_detected` -- the mutation/
   falsification half this claim needs: diverges one real input (`bars_batch_hash`) between the two
   independent calls and asserts the persisted result moves in exactly that field, so a
   constant-output implementation could never satisfy test 1 by accident.

**Field-exclusion basis, not invented:** the class already declares its own authoritative
`$ignoredVolatileFields` list (`exported_at`, `replay_started_at`, `replay_completed_at`,
`duration_ms`, `runtime_memory`, `temporary_output_path`, `created_at`, `updated_at`), used elsewhere
in the same class's own comparison logic. `replay_id` is the one further field excluded here, on the
same general principle `Audit_Hash_and_Reproducibility_Contract_LOCKED.md` already states for
artifact hashes (`run_id` and auto-increment navigation IDs are excluded unless they alter
semantics) -- applying an already-decided principle to a new record shape, not inventing a new one.

**No production code changed.** `ReplayVerificationService::verifyRunAgainstFixture()` and
`MarketDataPipelineService` were read in full and found already correct for both senses; this
remediation is proof-binding plus two new targeted tests, nothing else.

**Rebind, each predicate reviewed on its own requirement:**

| Predicate | Verdict | Proof basis |
|---|---|---|
| `MD-S019-R0073` | **PROVEN** | Positive: `ReplayVerificationServiceTest::test_replaying_the_same_unchanged_publication_twice_persists_byte_identical_results_except_execution_identity`. Negative: `::test_a_genuine_input_divergence_between_two_independent_replays_is_detected`. |
| `MD-S005-R0095` | **PROVEN** | Same pair as `MD-S019-R0073` -- the same required proof restated. |
| `MD-S003-R0004` | **PROVEN** | Positive: `MarketDataPipelineIntegrationTest::test_run_daily_correction_with_unchanged_artifacts_cancels_request_and_preserves_current_publication`. Negative: `::test_run_daily_correction_replaces_current_publication_and_marks_correction_published`. Both pre-existing, unmodified. |
| `MD-S019-R0009` | **PROVEN** | Same pair as `MD-S003-R0004` for the rerun half (Invariant 1's own three named hashes, individually load-bearing); the replay half is the same claim `MD-S019-R0073`'s pair proves. |

`B18ReplayRerunDeterminismTest` is **kept, unmodified** as supporting evidence for exporter
determinism (per this finding's own remediation point 3) -- it remains valid proof of a different,
narrower claim (the exporter round-trips a record byte-for-byte), just no longer the guard bound to
these four predicates.

`MarketDataReplayVerificationProofBasis`: all four predicates moved `INCOMPLETE` → `PROVEN`. No
traceability-matrix `coverage_status`/`SATISFIED`/denominator change.

**This finding is `RESOLVED`.**

# Finding — rerun-determinism guards re-export instead of rerunning

- ID: `F-MD-B18-A002-014`
- Stage / Attempt / Baseline / Epoch: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001` / `MD-REBASELINE-20260820-001`
- Raised: 2026-09-14T11:23:30+07:00 (system clock)
- Severity: `P1` for closure — the proof basis overclaims; no application defect has been
  established
- Status: `OPEN — GUARD_REDESIGN_REQUIRED`
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

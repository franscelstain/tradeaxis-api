# Finding - The governed B10 binder cannot promote the 56 affected rows under MD-B10-A002

- ID: F-MD-B10-A002-006
- Stage / Attempt / Baseline / Epoch: MD-B10 / MD-B10-A002 / MD-B10-A002-BL001 / MD-REBASELINE-20260820-001
- Raised: 2026-10-02T03:38:43+07:00
- Severity: P1 - blocks the governed promotion of the 56 affected rows and B10 closure
- Status: RESOLVED - the governed successor binder, gates and self-test exist, were proven fail-closed and atomic, and were executed once (E-MD-B10-A002-018)
- Class: GOVERNANCE_TOOLING_GAP
- Related: E-MD-B10-A002-017, F-MD-B10-A002-005, F-MD-B18-A002-023, CI-MD-B10-A002-001, MD-DEP-0020, MD-DEP-0021
- Remediation owner: MD-B10-A002 (B10-owned tooling; same attempt, baseline and impact declaration)

## Observed

`E-MD-B10-A002-017` proves all 56 affected predicates `PROVEN_CURRENT`. The promotion gate stops at its last condition because the only governed B10 binding mechanism cannot bind them.

The mechanism is `MarketDataPublicationLifecycleProofBinder` with `MarketDataPublicationLifecycleProofSpec`, `MarketDataPublicationLifecycleProofGate`, `MarketDataPublicationLifecycleTraceabilityGate` and `MarketDataPublicationLifecycleProofSelfTest`. It was built for `MD-B10-A001` and binds the whole B10 denominator once:

| Property | Value in the tool | What the A002 subset needs |
|---|---|---|
| Attempt / baseline / change impact | `MD-B10-A001` / `MD-B10-A001-BL001` / `CI-MD-B10-A001-001` | `MD-B10-A002` / `MD-B10-A002-BL001` / `CI-MD-B10-A002-001` |
| Evidence id accepted | `E-MD-B10-A001-NNN` only | `E-MD-B10-A002-017` |
| Rows it binds | all 1072, and it refuses unless every one is `NOT_ASSESSED` with no evidence ("non-pristine predicate") | exactly the 56 `NOT_ASSESSED` rows; the other 1016 stay `SATISFIED` and untouched |
| Count it enforces | `EXPECTED_DENOMINATOR = 1072` | 56 bound, 1016 unchanged, 1072 total |

Read-only runs on 2026-10-02, with nothing written (the traceability matrix is byte-identical):

- `MarketDataPublicationLifecycleProofBinder.php --validate-only` returns `FAIL`: 1016 `COVERAGE_STATUS_INVALID` and 1016 `PREMATURE_EVIDENCE_BINDING`. It reports the 1016 already satisfied rows as errors because it expects the A001 starting state.
- `MarketDataPublicationLifecycleProofBinder.php --evidence-id=E-MD-B10-A002-017` stops before it reads the evidence: "Use --validate-only or --evidence-id=E-MD-B10-A001-NNN".

The 56 rows are pristine (`NOT_ASSESSED`, empty `current_evidence_ids`), and the standard says what binding them requires (`STRATEGY_IMPLEMENTATION_TRACEABILITY_STANDARD.md:94`): current correlation-first evidence tied to a valid Attempt/Baseline/Epoch. `E-MD-B10-A002-017` is that evidence. No governed tool applies it to a subset.

## Not caused by this finding

The 56 proofs are not in doubt. Editing the 56 status rows by hand, or loosening the A001 binder's pristine and denominator checks, would bypass the governance the gate exists to enforce, so neither was done.

## Remediation conditions

Same A002 / BL001 / CI, no new attempt and no new baseline.

1. Provide a governed binder and gate for the A002 affected subset. It binds `E-MD-B10-A002-017` to exactly the 56 rules in its matrix, refuses any other row, any non-pristine row and any matrix that is not exactly 56 `PROVEN_CURRENT`.
2. The other 1016 rows must stay byte-identical, and the denominator stays 1072. The writer must emit untouched lines verbatim (the earlier B18 binder re-quoted rows it did not own).
3. Dry run first, then apply. Apply is all or nothing and leaves no partial state if the gate fails after the write.
4. The gate must check the evidence payload per row (verdict, proof standard, member and behaviour proof), not only the evidence id, and bind the same attempt, baseline and impact declaration that issued the evidence.
5. A self-test proves it fails closed (foreign row, missing row, a non-proven row, a second evidence id, a changed unrelated row) with a green control.
6. Run it once, and only then evaluate `MD-DEP-0021` and `MD-DEP-0020` against their resolution conditions.

## Orchestration

Opened by the remediation reproof under `MD-DEP-0021` and `MD-DEP-0020`; both stay BLOCKING. It creates the single next executable work for MD-B10-A002. R0025 and F-018 stay paused; `F-MD-B10-A002-003` and `F-MD-B10-A002-004` stay with MD-B18-A002.

## Resolution - 2026-10-02 - E-MD-B10-A002-018

Same A002 / BL001 / CI. Each remediation condition is met by a governed tool, not by a hand edit of the matrix:

1. `MarketDataB10SuccessorBinding` and `MarketDataB10SuccessorBinder` bind `E-MD-B10-A002-017` to exactly the 56 rules its scope record `E-MD-B10-A002-001` names. The affected set comes from that record, nothing is hard-coded to 56 or to the evidence id, and any other row, any row not in its expected prior status or any matrix that is not exactly 56 `PROVEN_CURRENT` is refused. The proof and traceability gates are successor-aware: one evidence record per owning attempt, the predecessor model unchanged when no successor is registered.
2. The 1016 unaffected rows are emitted byte for byte and the denominator stays 1072. Every non-affected line keeps its own quoting style.
3. Validate-only is the default and reported no diagnostic on the canonical matrix. `--apply` plans the post-state through the gates first, writes a hash-verified temporary file, renames it, reads it back, runs the gates again and restores the original bytes on any failure (injected failures are only possible on a non-canonical matrix with `MD_B10_BINDER_TEST=1`).
4. The evidence payload is checked per row: attempt, baseline, change impact, epoch, freeze, result counts, the rules it proves, its relationships and its raw package manifest and member hashes.
5. The self-test drives the real binder against temporary copies: 7 controls then 46 fail-closed cases before the promotion, 54 after it. 32 mutation probes of the binder and gates each go red at the intended case.
6. The binder ran once: before 1016 / 56 / 1072, after 1072 / 0 / 1072; only the 56 lines changed, and only `coverage_status`, `current_evidence_ids` and `notes`. `MD-DEP-0021` and `MD-DEP-0020` were then evaluated and are RESOLVED.

`F-MD-B10-A002-001` was not absorbed: it was resolved separately on its own criteria.

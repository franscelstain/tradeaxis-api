# Finding — governance gates overwrite immutable evidence

- ID: `F-MD-B18-A002-019`
- Stage / Attempt / Baseline / Epoch: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001` / `MD-REBASELINE-20260820-001`
- Raised: 2026-09-15T21:42:47+07:00
- Severity: `P1` — governed record immutability breach by a validation side effect
- Status: `RESOLVED — Q6_EXECUTABLE_PRESERVATION_PROVEN`
- Dependency: `MD-DEP-0018` RESOLVED by `E-MD-B18-A002-017`; discovery evidence `E-MD-B18-A002-016`

## Observed conflict and restoration

DOCUMENT_ROLE_REGISTRY.csv classifies both records/evidence/MD_DOCUMENTATION_INTEGRITY_GATE_LATEST.json
and MD_RELATIONSHIP_INTEGRITY_GATE_LATEST.json as IMMUTABLE_AFTER_ISSUE (rows 271/272).
MarketDataDocumentationIntegrityGate.php:88 unconditionally writes the first; the relationship
gate writes the second at lines 240–243. Neither exposes a read-only/output-path mode. The final
diff showed populations 1094→1097 and records/relationships 247/524→249/545 plus timestamps.
Successful gate verdicts do not authorize this overwrite (DOCUMENT_CHANGE_POLICY §3; DOCUMENT_RECORDING_STANDARD).

The overwritten bytes were retained under E016's raw manifest, and both canonical files restored
byte-for-byte from their Git HEAD blobs. The entry working tree was clean, so those blobs are the
pre-turn bytes; the restoration used byte copies, not git checkout. Both hash comparisons pass.
No IMMUTABLE record remains changed in the final diff. This mitigates the persisted damage; it does
not make the earlier writes authorized or cure the tooling defect.

## Q6 — pending user decision; recommendation and alternatives

1. **Recommended:** authorize a bounded governance-tool remediation within the current attempt:
   add explicit read-only/output-path behavior, direct new outputs to correlated runtime storage,
   keep existing immutable records unchanged, and test that a sentinel immutable file is never
   written. Inspect all callers and both sibling gates, then mutation-probe write prevention and
   run the required suite. Amend CI before code/test changes; record an explicit ownership decision.
2. **Alternative:** dependency-driven B00 tooling re-entry, with its own baseline/CI and a recorded
   return to B18. This separates proof ownership but adds orchestration work; do not switch now.
3. Reclassifying *_LATEST records requires a separate governance decision and impact review. It is
   not recommended as a means of making the existing tool pass; prior issued bytes remain historical
   facts and cannot be silently repurposed. No reclassification has been made.

This is an addendum to the Q1–Q5 package review, not a replacement or approval of those choices.
Before user decision, no further canonical invocation of the writing gates or full suite that
invokes them. Read-only documentation checks may execute in a disposable copy with outputs confined
to that copy. Such checks cannot establish full-suite/runtime acceptance.

## Current validation and completion

The completed post-E015 full suite: 2258 tests / 21941 assertions / 7 corpus-oracle failures /
0 errors / 0 skipped. These still belong to F011 and MD-DEP-0015. New F019/E016 registration is
after that run; a post-registration full suite remains pending under MD-DEP-0018 rather than
repeating an unauthorized immutable-record write. No test/guard is changed to hide a failure.

Resolve only after the chosen owner/scope is recorded, tools preserve immutable bytes, each relevant
single-write mutation is caught with green controls before/after and byte restore, all callers are
covered, and required validations run safely. Role classes/strategy/denominator stay unchanged.


## User approval — 2026-09-15T21:56:09+07:00

D-MD-B18-A002-005 approves Q6 bounded remediation in current B18, with unchanged governance
semantics. The prior pending decision is now taken; the finding remains OPEN and MD-DEP-0018
remains BLOCKING until executable byte-preservation and fail-closed proof meet its criteria.


## Executable resolution — 2026-09-15T15:22:45.945825+00:00

D005 authorized the bounded fix. E017 now establishes all five Q6 criteria: stdout-only Documentation/Relationship gates; explicit Applicability --check rejects mutation flags; whole-document-tree preservation tests cover PASS and FAIL. Nine tests/5430 assertions PASS. Four single landed mutations (file_put_contents, fwrite, copy to another immutable D005, conflicting-mode bypass) fail with green controls before/after and byte restoration. All 663 registered immutable files remain identical through every relevant canonical gate and full suite. Governance PASS (15 documentation checks; relationship validity/completeness; 14 mutation cases/4 controls; classification; applicability). Full suite safely ran: 2267 tests/27406 assertions, seven existing corpus failures, zero errors/skips. These remain F011/MD-DEP-0015; Q6 does not resolve them or supply replay predicate proof. Earlier pending/stop paragraphs above are issue chronology superseded by this executable resolution.

# Finding — `F-MD-B18-A002-006`

- ID: `F-MD-B18-A002-006`
- Raised by: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001`
- Epoch: `MD-REBASELINE-20260820-001`
- Raised at: 2026-09-13T11:00:00+07:00
- Owner stage: `MD-B18`
- Severity: `P1`
- Status: `RESOLVED — COMPLETE_PARENT_APPROVED_AND_FALSE_CONDITION_EVIDENCED`
- Class: `INCOMPLETE_PARENT_SCOPE_REVIEW`
- Former blocker: `MD-DEP-0012`, resolved by D002/E005 and counted normalization
- Current Decision / Evidence: `D-MD-B18-A002-002` / `E-MD-B18-A002-005`; execution audit `E-MD-B18-A002-006`

## Resolution — complete parent, 2026-09-13 continuation

The user approved the omitted R0038/R0039 recommendation. Immutable successor D002 preserves
D001's independent owner/deferral decisions and authorizes only the complete four-child condition.
E005 records fresh integrated AS_KNOWN execution: 20 tests / 100 assertions before and after a
single-landed unavailable-capability mutation; the probe was caught (5 errors and 1 failure), and
the application file was restored from a byte copy with matching SHA256. Actual MariaDB fixtures
execute without skips. Both databases have all 72 current repository migrations applied; their
ledger contains 83 entries including 11 names no longer in this repository. No migrations or
database recovery were performed.

Exactly four of 6501 matrix rows changed in this continuation, all R0038-R0041:

| Rule | Old applicability | New applicability | Current evidence |
|---|---|---|---|
| MD-S050-R0038 | CONDITIONAL_PENDING | CONDITIONAL_NOT_APPLICABLE | E-MD-B18-A002-005 |
| MD-S050-R0039 | CONDITIONAL_PENDING | CONDITIONAL_NOT_APPLICABLE | E-MD-B18-A002-005 |
| MD-S050-R0040 | CONDITIONAL_PENDING | CONDITIONAL_NOT_APPLICABLE | E-MD-B18-A002-005 |
| MD-S050-R0041 | CONDITIONAL_PENDING | CONDITIONAL_NOT_APPLICABLE | E-MD-B18-A002-005 |

Each has NOT_APPLICABLE coverage, complete parent/normalized predicate and D002 binding. Source
fields, owners, parent R0037 and all other rows are unchanged. Backup and exact delta are retained
in E006. The normalization gate now checks the entire 154-row population, each of the four N/A
bindings, current E005 identity and raw/source hashes. Clearing just R0038's evidence made it red
with FALSE_CONDITION_BINDING_INVALID:MD-S050-R0038; byte restoration returned PASS.

Pending applicability is 4 -> 0. All 115 retained predicates remain NOT_ASSESSED. The exact live
map now has 115 entries, not 121, based on D001/D002 and E005 rather than a desired verdict.
The separate R0056 basis review found a genuine incomplete predicate; F-MD-B18-A002-008 /
MD-DEP-0014 records that new owner/scope boundary. It does not reopen this resolved four-child
decision. The historical stopped-state journal below is preserved, not current orchestration.

## Root cause and exact population

The previous recommendation reopened only MD-S050-R0040/R0041 and projected a 117-row final
denominator. It did not apply its reasoning to R0038/R0039, although all four have the same parent
MD-S050-R0037 and all four were classified CONDITIONAL_APPLICABLE at A002 entry.

In the frozen Replay_Verification_Contract_LOCKED.md, line 75 says "Where as-known replay is not
implemented, the following hold without exception". Its complete child population is lines 77-80,
MD-S050-R0038 through R0041. The next section starts at line 82. There are four children, not two.
The four existing normalized predicates named the parent ID but omitted its external condition.
Thus a 2-row applicability recommendation was not a whole-parent normalization.

This is a recommendation/matrix defect, not a strategy ambiguity or permission to rewrite the
parent. It violates the user's whole-section rule and traceability standard sections 3, 8-10.

## Safe stopped state

- D-MD-B18-A002-001 records the actual approved scope and executes its independent owner/deferral
  decisions. Approval is not silently extended to the omitted siblings.
- All four children are CONDITIONAL_PENDING / APPLICABILITY_PENDING, with the parent condition
  included in each normalized predicate. Parent R0037 remains reference context, unchanged.
- The only other matrix mutations are approved ownership/reference corrections for S002-R0004
  and S004-R0007. Exactly six rows change; source fields and all unrelated rows remain unchanged.
- No current evidence is bound, no gate denominator is lowered, no code is changed to create work,
  and no PASS, terminal N/A, or final denominator is claimed.
- Live B18 population is 154: 115 mandatory unassessed, 4 conditional pending, 33 reference, and
  2 optional. There are 115 live proof-basis entries; their presence alone is not current proof.

## Required decision and deterministic continuation

## Stopped-state validation correction — 2026-09-13

The first governance pass correctly rejected S004-R0007 with UNEXPLAINED_REFERENCE: its decision
was written in prose, but hasRecordedReferenceDecision() requires both
applicability_normalized=REFERENCE_ONLY and proof_owner_confirmed=MD-B18. Added those two
structured markers to that one already-approved row without changing its classification or the
decision. This is an administrative binding fix, not a new owner choice or a gate relaxation.

The B18 stage proof/readiness gates remain red on the provisional population (115 live rows versus
the original 121-entry reviewed map). The stage self-test baseline is red, so its ten negative
outcomes are NOT admitted as mutation proof. The closure gate's basis subtotal also still derives
from the original map: its 113/115 figure subtracts the two now-nonowned missing entries. The live
intersection is 115 basis entries; neither number proves those predicates. Align these consumers
only after final applicability review, then obtain green controls before admitting any probe.

### Required user decision (unchanged)

### Final post-issuance validation journal

E-MD-B18-A002-004 supersedes E003's current runtime-availability status and corrects its log-event
correlation without editing E003. After E004 and all registry rows were issued, the final
`php vendor/bin/phpunit` run passed **2248 tests / 21401 assertions**, zero errors, failures or
skips, in 02:43.129. Raw output:
`storage/app/market-data/evidence/MD-B18-A002/approval-scope-20260913/full-suite-final-post-e004.txt`;
SHA256 `E2BF0DC37C03358A40E2894CC83551D89254E42F9C5591540277AE2E96AA009D`.

This is a post-record regression check, not a new predicate binding. The E004 runtime observations
remain the separately retained 2248/21399 execution. No E001 PASS evidence or SC is issued.

Measured convergence in this continuation: missing reference-decision markers 1 -> 0; stale
generated-navigation assertion failures 2 -> 0; database errors 6 -> 0 and outage skips 16 -> 0.
MD-DEP-0013 is resolved by current execution. The only remaining decision blocker is MD-DEP-0012.
Live proof state is unchanged: **115 unassessed / 0 satisfied, 4 pending applicability, 0 missing
basis entries in the live intersection**. Basis existence does not mean predicate proof.

Final governance controls: documentation 15/15; relationship validity/completeness PASS with
224 records and 443 relationships; classification PASS; built-in applicability gate PASS for
B01 only, not B18. Governance self-test: 14 caught mutations and four green controls. Stage
proof/readiness/closure remain FAIL; the B18 self-test's red baseline admits zero mutation proofs.
All matrix and registry writes were compared with pre-write byte copies and counted; matrix
delta remains exactly six rows out of 6501. Strategy bytes and issued predecessor records stay
unchanged. CURRENT_STATE is generated again after the final suite, never hand-edited.

Destination count cross-check: MD-B22 already owned 27 mandatory rows before the approved transfer.
The transferred row is an increment of one, not a total of one. Its register now reports the actual
28 mandatory plus 2 conditional N/A rows, subject to its future stage-entry revalidation. No B22
matrix row other than the approved incoming S002-R0004 is changed and B22 remains NOT_STARTED.

Executed stopped-state validation is retained by E-MD-B18-A002-003. Targeted replay/condition
checks pass 24/81; classification/applicability tests pass 23/77 after the marker correction;
governance documentation has 15 passing checks, relationship validity/completeness pass, and
the governance self-test has 14 caught mutations with four green before/after controls.
The full suite returned 2248/21249 with 6 database connection errors, 2 stale generated-navigation
failures and 16 skips. F-MD-B18-A002-007 / MD-DEP-0013 records the newly observed runtime blocker.
Navigation must be regenerated and the two failures retested; database restoration is not
silently authorized by the already-approved scope package.

Ask whether to apply the same parent condition to R0038/R0039 too. Recommendation: yes, followed
by fresh proof of the false condition for all four children. Projected final denominator is then
115, not 117; these four conditional outcomes are separately evidenced, never SATISFIED.

After the decision, continue this attempt with a successor decision record, fresh capability
proof, counted whole-parent normalization and exact proof-map updates. Re-review per-predicate
bases and guard bodies, execute controls and landed mutations, close F-MD-B18-A002-005 through
actual A002 evidence-integrity proof, and perform all closure/governance/full-suite requirements.
F-MD-B19-A001-002 and MD-DEP-0009 remain open until valid B18 closure. A failing local test does
not open a new attempt. The deferred B21/B22 obligations do not create another executable resume.

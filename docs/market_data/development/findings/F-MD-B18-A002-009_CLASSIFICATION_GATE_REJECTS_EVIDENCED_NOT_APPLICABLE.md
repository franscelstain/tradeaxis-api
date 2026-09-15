# Finding — classification gate rejects evidenced N/A

- ID: `F-MD-B18-A002-009`
- Stage / Attempt / Baseline / Epoch: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001` / `MD-REBASELINE-20260820-001`
- Raised: 2026-09-13T23:07:00+07:00
- Severity: `P2`
- Status: `RESOLVED — REPAIR_REPROBED_ON_CURRENT_CODE`
- CI: `CI-MD-B18-A002-001`, amended before this guard mutation
- Related evidence: `E-MD-B18-A002-005`; corrective validation to be issued separately

The first post-normalization targeted run returned 27 tests / 73 assertions and two failures.
ClassificationConsistencyGateTest reports four BINDING_COHERENCE errors, R0038-R0041: status is
NOT_APPLICABLE while evidence is present. MarketDataClassificationConsistencyGate used the
equivalence hasEvidence === isSatisfied, so correctly evidenced terminal N/A was forbidden.

This contradicts the existing traceability lifecycle (standard sections 4-6) and Stage Closure
Manifest Standard's requirement to prove each false condition. The matrix and E005 are not the
remediation target. The intended fix admits the precise REQUIRED / CONDITIONAL_NOT_APPLICABLE /
NOT_APPLICABLE tuple with evidence, while preserving all half-cleared-proof refusals. Evidence
sufficiency stays with the predicate/normalization gates; this is not a generic N/A proof waiver.

The gate's callers include generated navigation and classification tests. New tests must cover
one evidenced N/A instance, retained no-evidence/rationale-only N/A support, and single-field
mutations to nonterminal or incorrectly classified states. Probe the actual corrected branch with
green controls before and after byte restoration. Keep the first red output; no attempt change.

The first red output is retained at
`storage/app/market-data/evidence/MD-B18-A002/complete-parent-20260913-2246/targeted-after-records.txt`.
This finding is independent of the unresolved R0056 ownership decision (F008 / MD-DEP-0014).

## Resolution — 2026-09-14

The record was left unregistered by the 2026-09-13 interruption; it was registered on
2026-09-14 (MD-DOC-01121, MD-REL-0454..0456). The repair was re-probed on the current gate bytes
(sha256 80C96361…610D) against the clean instance. The control was
`ClassificationConsistencyGateTest`, 20 tests / 96 assertions, green before each probe and after
each restore.

| Probe | Mutation | Result |
|---|---|---|
| Reject | condition reverted to `hasEvidence !== isSatisfied` | 10 tests red, including the evidenced-N/A case |
| Overbroad | `isConditionalNa` forced true | 7 non-terminal and misclassified data sets red |

Each probe landed once and was restored hash-equal. The four N/A rows now carry
`E-MD-B18-A002-008`, and the test asserts that. The evidence is `E-MD-B18-A002-009`.

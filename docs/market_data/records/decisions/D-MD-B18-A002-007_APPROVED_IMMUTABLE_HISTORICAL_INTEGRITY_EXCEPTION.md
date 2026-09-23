# Decision — admit an issued, immutable, unparseable evidence record through an explicit, fail-closed integrity exception

- ID: `D-MD-B18-A002-007`
- Verification epoch: `MD-REBASELINE-20260820-001`
- Stage / Attempt / Baseline reviewed: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001`
- Finding: `F-MD-B18-A002-022`
- Supporting evidence: `E-MD-B18-A002-063` (correction of `E-MD-B18-A002-061`, unresolved item `E061-U1`)
- Issued: 2026-09-23T16:23:35+07:00
- Decision status: `APPROVED`
- Strategy impact: `NONE` — governance-only; strategy bytes and freeze `MD-STRATEGY-FREEZE-20260903-001` unchanged
- Governance impact: `CONTROLLED_REVISION` of `DOCUMENT_INTEGRITY_GATE_STANDARD.md`, recorded as `DOC-CHG-20260923-001`

## Question

`E-MD-B18-A002-061` is issued and `IMMUTABLE_AFTER_ISSUE`, and it does not parse as JSON. The
documentation integrity gate must verify parseable JSON and has no exception path. How does the
repository reconcile the two without editing the record or weakening the gate?

## Authority review

- `DOCUMENT_CHANGE_POLICY.md` §3: `IMMUTABLE_AFTER_ISSUE` records must not be edited after issue;
  corrections require a new correlated record. `DOCUMENT_RECORDING_STANDARD.md` §1: evidence
  correction creates new evidence. E061 therefore stays byte-identical; `E-MD-B18-A002-063` is its
  correction.
- `DOCUMENT_INTEGRITY_GATE_STANDARD.md` is `CONTROLLED_REVISION`. Changing what the gate accepts
  changes a gate requirement, so `DOCUMENT_CHANGE_POLICY.md` §3 and §5 require a finding, evidence,
  this reviewed decision, a change-log entry, and a verification-impact statement.
- `DOCUMENT_INTEGRITY_EXCEPTION_REGISTRY.json` is registered `MUTABLE_TRACEABLE` but has no defined
  semantics. This decision gives it one, and only one, purpose.

## Decision

Adopt a narrowly controlled immutable historical integrity exception. An already-issued
`IMMUTABLE_AFTER_ISSUE` evidence artifact that is structurally invalid may bypass the raw structural
check only when all of the following hold:

1. the original artifact remains byte-identical to its issued form (bound by sha256);
2. an issued, valid correction evidence record explicitly identifies that artifact and the exact
   structural defect;
3. the correction does not rewrite or replace the original substantive proof or verdict;
4. the original and the correction are both traceably registered;
5. the exception is explicitly authorised in `DOCUMENT_INTEGRITY_EXCEPTION_REGISTRY.json`;
6. the gate validates the exception registry itself, fail-closed;
7. any malformed artifact without a fully valid authorised exception remains a hard `FAIL`.

The only integrity check that may be excepted is `JSON_PARSE`, the only one the triggering case
needs. The mechanism must not become a generic "ignore `JSON_PARSE`" path: the raw failure stays
visible in the gate output, and the exception binds one path at one hash.

E061 is not declared never-issued and is not edited.

## Authorization state

Explicit owner authorization, received 2026-09-23 in this attempt: "Owner decision: Do not declare
E061 “never issued” and do not edit E061. Adopt a narrowly controlled immutable historical integrity
exception mechanism, but only through explicit governance authority and fail-closed gate
implementation." The seven conditions above are the owner's required policy semantics, recorded
without change.

## Invalidation / revalidation impact

- Strategy: none.
- Prior documentation-gate `PASS` results: unaffected. The revision only adds an admission path
  for files named in the exception registry, and that registry held no entry from its creation
  (commit `ac09939`) until this decision, so no earlier gate result could have used the path. The
  registry validation it introduces is stricter than before.
- `MD-B00` closure (`SC-MD-B00-A001-001`) states no exception was registered; that remains true of
  the tree it closed.
- Current proof: none depends on the gate accepting a malformed file other than E061. `MD-S050-R0046`
  rests on executing guards, not on E061 parsing.
- Required revalidation: the gate, its self-test with new fail-closed mutations, and the governance
  gates, recorded in `E-MD-B18-A002-064`.

## Scope limit

No other integrity check may be excepted; no artifact class other than issued
`IMMUTABLE_AFTER_ISSUE` evidence is eligible; no exception may be registered without an issued
correction record naming the artifact and defect. Adding an exception category or eligible class
requires a new controlled revision.

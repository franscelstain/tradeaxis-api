# Decision — approved Q1–Q6 with bounded B18 execution

- ID: `D-MD-B18-A002-005`
- Stage / Attempt / Baseline / Epoch: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001` / `MD-REBASELINE-20260820-001`
- Issued: 2026-09-15T21:56:09+07:00
- Status: `APPROVED — EXECUTION AND PROOF OUTSTANDING`
- Mutability: `IMMUTABLE_AFTER_ISSUE`
- Authority: explicit user message approving Q1–Q6 with scope limits; no inferred authorization
- Related findings: F-MD-B18-A002-013 through F-MD-B18-A002-019
- Dependencies: MD-DEP-0017, MD-DEP-0018; MD-DEP-0015/0016 remain unchanged

## Decisions and mandatory limits

Q1 APPROVED: bounded remediation in current B18 uses/extends existing publication lineage.
No owner-publication re-entry unless actual implementation establishes a semantic requirement that
cannot lawfully be handled by B18; then stop and record the need rather than changing ownership.

Q2 APPROVED: historical publication lacking complete required bound inputs is BLOCKED. No historical
identity reconstructed from assumptions. Reconstruction requires deterministic derivation of every
value from authoritative immutable evidence and a separate decision where governance requires it.

Q3 APPROVED: historical/backfill verification uses explicit publication/fixture identity, including
publication_id wherever required. No latest/current substitution. Any current-read verification is
a separate operation/semantic contract, not a fallback for historical verification.

Q4 APPROVED WITH SCOPE BOUNDARY: determinism covers canonical domain/semantic payload on independent
rerun. Non-semantic execution metadata may differ only when explicitly classified by the governing
contract; replay_id and created_at are examples, not authority for blanket exclusions. Every other
field exclusion requires specific authority/contract justification. In particular, the draft's
run_id/publication-ID/updated_at/generated_at list is NOT a blanket approved envelope exclusion.
S005 content-hash exclusions apply only to their stated content-hash scope. Preserve execution audit
separately and retain all availability/effective/knowledge semantics in the domain payload.

Q5 APPROVED: MD-S020-R0014 primary MD-B22, supporting MD-B18 and MD-B17. B18 supplies its evidence
and supporting contract; it does not own cross-stage readiness admission. Change the one ownership
row through traceability/change-impact rules; retain MANDATORY/NOT_ASSESSED and the complete parent.
No proof transferred automatically. Planned B18 denominator 115→114; B22 gains one required row.

Q6 APPROVED WITH STRICT LIMIT: bounded governance/output tooling fix in current B18 prevents
unintended immutable-record writes. Validate/check/gate paths must be read-only or use a legitimate
generated output location. Preserve present governance semantics and acceptance rules; no framework
redesign, no acceptance weakening, no reclassification of immutable records to make tooling pass.

Q6 acceptance: immutable files byte-identical before/after every relevant gate; Documentation,
Relationship, Classification and Applicability PASS; self-test still fails closed; no hidden write
through file_put_contents or equivalent paths; mutable generated outputs change only through an
authorized process. Fix Q6 first if needed for validation safety, then implement Q1–Q5 in dependency
order, targeted tests and falsifiability probes, safe governance gates, then full suite.

## Proof and orchestration

Approval alone resolves no finding, dependency, predicate, or stage. MD-DEP-0017 and MD-DEP-0018
remain BLOCKING with approved execution triggers until their respective proof criteria are met.
No PASS/SC/E001 is authorized by this decision itself. No access/recovery/copy from data_260914,
production relock, watchlist policy, or strategy-byte change is authorized. Prior D001–D004 remain.

Single next executable resume: implement and prove Q6 read-only governance execution under F019,
then continue approved package Q1–Q5 in the same attempt. Return to B19 only after valid B18 closure.

The reviewed proposal remains byte-preserved as review context (SHA-256 `1D7F35E6F83DA48A425DF96BA2E88959617B800F3B235A90B567B353368DFFC1`).
Its older PENDING labels and broader Q4 proposal are superseded for execution by this explicit
decision, not edited into new authority. E014/E016 remain immutable descriptions of their issue state.

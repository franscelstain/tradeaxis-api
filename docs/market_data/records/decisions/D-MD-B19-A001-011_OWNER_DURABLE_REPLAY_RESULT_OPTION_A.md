# Decision — D-MD-B19-A001-011

- ID: `D-MD-B19-A001-011`
- Stage / Attempt / Baseline: `MD-B19` / `MD-B19-A001` / `MD-B19-A001-BL001`
- Verification epoch: `MD-REBASELINE-20260820-001`
- Status: `APPROVED`
- Recorded at: 2026-10-29T02:02:34.856332+07:00
- Source: actual Project Owner answer “Owner Decision — D010-B: Option A” in the current conversation. Recorded by Codex; no signature, separate approval identity, external principal credential or independent reviewer approval is invented.

## Approved bounded contract

This refines D010-B only; D010-A/C/D, D009 admission and historical decisions remain intact. The Owner explicitly approves Option A in the concrete request identified below:

1. Durable replay results use an isolated execution identity and their own scoped/fenced persistence authority. The trusted issuer is the internal verifier within the existing F03 limitation; no external principal authentication is claimed. Scope/input/target identities and provenance supplied by a caller must be validated, not accepted as credentials.
2. Each acting execution has a fresh secret credential and fenced identity bound to mode, requested date, target/subject publication or as-known cutoff, and fixture/input identity. An integer replay ID, historical run model, released projection credential or incumbent token is not acting authority. A competitor cannot borrow the incumbent execution. Valid reacquisition creates a new actor/token and retains prior lifecycle history.
3. Identity allocation is atomic. Metric, reason counts and completion are one consistent protected result transaction. Failure rolls back the result pair and preserves a truthful lifecycle; stale/released/wrong-scope/wrong-connection writers cannot change a successor's state.
4. Completed durable cases are immutable. An identical retry is read-only/idempotent; changed inputs or results require a new replay ID. A supplied completed replay ID is a selector, not overwrite authority. Unattributable legacy cases remain readable/exportable and cannot be rewritten under a fabricated owner. The versioned-attempt alternative B is not selected.
5. Result authority grants no EOD run/publication/candidate/pointer/artifact/hash rights. AS_KNOWN projection remains rollback-only. Durable result recording occurs outside that isolation under its own authority and cannot escape or weaken the existing commit/DDL/nested/statement/reconnect controls.
6. Continue compatible governed development after this branch; discretionary independent review is not an automatic stop. Mandatory admission, closure, fixture-oracle and operational review gates are not waived.

## Authority compatibility and preserved boundaries

This is a concrete implementation/lifecycle decision under D010-B, LOCKED Scheduling and Locking, Run Ownership/Recovery and Replay Verification; it changes no LOCKED document or comparison/admissibility/oracle semantics. Runtime actor/lifecycle storage, scope checks and transaction machinery require RED/GREEN, persisted independent-connection, rollback/takeover and mutation/restoration proof before conformance claims. No deployment or migration execution against production is approved.

Accounting at recording remains669 independently accepted /750 applicable;674 repository proposals;76 without basis;E04927;formal SATISFIED0. No new predicate or admission follows from this decision. Four E049 dependency HOLDs remain; MD-S078-R0004 is INCOMPLETE. F03, F018, MD-DEP-0025, operations FAIL, inherited manual-file consumer FAIL and immutable B18/candidate-v5 history remain. This decision neither closes incidents nor promotes the four HOLDs.

## Exact source evidence

- Actual instruction: `storage/app/market-data/evidence/MD-B19-A001/E049_OWNER_D010B_OPTION_A_INSTRUCTION_20261029.md`; SHA-256 `095a2b4713a659b8019547aa363919fb476db2829da1ea7a48bac4a99221f620`.
- Approved Option A request, original bytes preserved: `storage/app/market-data/evidence/MD-B19-A001/E049_D010B_DURABLE_RESULT_AUTHORITY_DECISION_REQUEST.md`; SHA-256 `1f0d51e90a7d978d0a450360e97c5e88de07fd979f26221dee8c23b3b8695bee`. Its historical DRAFT/not-yet-received wording describes the pre-decision request; this issued decision records the later approval.
- Existing author evidence: `E-MD-B19-A001-049-R007`; existing admission `E-MD-B19-A001-049`; scope decision `D-MD-B19-A001-010`; admission decision `D-MD-B19-A001-009`; current impact declaration `CI-MD-B19-A001-001`.

# Decision — `eligible` alias meaning is discharged by canonical ownership and governed delegation

- ID: `D-MD-20260822-04`
- Verification epoch: `MD-REBASELINE-20260820-001`
- Stage / Attempt / Baseline: `MD-B01` / `MD-B01-A016` / `MD-B01-A016-BL001`
- Finding: `F-MD-B01-A003-001`
- Dependency: `MD-DEP-0005`
- Rule under review: `MD-S020-R0067`
- Issued: 2026-08-22
- Decision status: `ISSUED`
- Strategy impact: `NONE` — no strategy byte is changed

## Question

`MD-S020-R0067` states that `eligible` "is the most policy-suggestive name on the entire market-data surface: read plainly, it says *permitted to trade*. Every contract that uses it must therefore repeat that it means `data_usable`, and that repetition is the only thing preventing the misreading."

`F-MD-B01-A003-001`, after two measurement corrections, identifies exactly one frozen strategy contract that names the alias without restating the meaning: `book/CONSUMER_READ_CONTRACT_LOCKED.md`, whose response invariant reads "A stale/degraded response is not equivalent to a fresh response. `200 OK`, non-empty rows, `eligible = 1`, or job completion cannot erase the state." The document states `data_usable` nowhere.

Two resolutions were recorded as defensible: an authorised strategy revision adding the repetition to that contract, or a reviewed decision that the existing ownership arrangement is authoritative.

## Review

**1. A canonical semantic owner exists and states the repetition.**
`book/Terminology_and_Scope.md` holds `eligibility snapshot` in its Term ownership register and defines it: "A compatibility field named `eligible` has **only** this upstream data-usability meaning." The register's binding rules — `MD-S056-R0141` and `MD-S056-R0142`, both `SATISFIED` with current evidence — establish that other documents may use and summarise these terms but may not redefine, widen, or narrow them, and that any summary carries a pointer and the precedence rule.

**2. The document that names the alias explicitly delegates field semantics elsewhere.**
`CONSUMER_READ_CONTRACT_LOCKED.md` opens by stating that all downstream consumers "read only the versioned market-data read model defined by `Downstream_Consumer_Read_Model_Contract_LOCKED.md` and its readiness metadata." The delegation is in the document's own first substantive sentence, not inferred.

**3. The delegated contract carries the full repetition, including the very caveat the failing sentence makes.**
`Downstream_Consumer_Read_Model_Contract_LOCKED.md`: "A compatibility `eligible` field means `data_usable`; it is not watchlist selection, tradability approval, alpha, ranking, or portfolio policy, **and it does not by itself prove that the requested dataset publication is readable**." The last clause is the same point `CONSUMER_READ_CONTRACT` line 30 makes about freshness. The read-model contract states both halves; the readiness contract restates one of them.

**4. That delegated contract is named in the owner boundary's own alignment requirement.**
`Domain_Boundary_Invariants_LOCKED.md`, "Required cross-contract alignment", lists `Downstream_Consumer_Read_Model_Contract_LOCKED.md` among the documents the owner contract must remain aligned with. The chain is closed by the owner, not by this decision.

**5. No semantic gap exists.**
A reader following the governed documents from the failing sentence arrives at the read-model contract and then at the owner boundary, and finds the meaning stated in full at both. No path through current authority yields the tradability misreading the owner boundary warns about; the phrase is deliberately not reproduced here, because a document that states the forbidden equation is indistinguishable from one that asserts it.

**6. Duplicating the sentence would violate the arrangement it is meant to protect.**
Writing `data_usable` into `CONSUMER_READ_CONTRACT_LOCKED.md` would place a third statement of one semantic into a document that does not own it. `MD-S056-R0141` forbids a non-owner document from redefining, widening, or narrowing a registered term, and `ONE_DOCUMENT_ONE_AUTHORITATIVE_ROLE_STANDARD.md` exists to prevent exactly this. The repetition would be added for traceability convenience and would make the term's meaning maintainable in three places instead of one.

## What this decision does not rely on

It does **not** rely on `MD-S020-R0189` as the operative instrument, and the reasoning is worth stating because the dependency record framed it that way.

R0189 reads: "Where a dependent document uses older wording that conflates eligibility with readability or policy, this owner boundary takes precedence **until** that dependent contract reaches its ordered strategy-update step."

Its condition is not met here. `CONSUMER_READ_CONTRACT_LOCKED.md` does not conflate eligibility with readability — its sentence exists to *separate* them, denying that `eligible = 1` establishes freshness. There is no conflation for the owner boundary to override. Declaring R0189's interim precedence "permanent" would also strain the word "until", which anticipates an update rather than excusing one.

The basis is therefore narrower and stronger than precedence: **the meaning is stated by the canonical owner, and the document that names the alias explicitly routes field semantics to a contract that states it in full.** R0189 remains what it is — an interim override for genuine conflation — and is untouched.

## Decision

1. `MD-S020-R0067`'s obligation is **discharged for `CONSUMER_READ_CONTRACT_LOCKED.md`** through canonical semantic ownership plus that document's own explicit delegation, which is governed cross-document proof ownership and not a precedence waiver.
2. **No strategy revision is authorised or required.** No actual semantic gap was demonstrated; the review found the meaning stated by the owner and by the delegated contract.
3. `MD-S020-R0067` becomes provable at `MD-B01-A016`. It is **not** marked `SATISFIED` by this decision: the composed predicate must be established by executed proof over the actual documents, and that proof must fail closed if any link in the chain is removed.
4. `F-MD-B01-A003-001` closes as **resolved by governance decision**, not by remediation and not by reinterpretation of the rule.
5. `MD-DEP-0005` resolves.

## Scope limit

This decision governs the `eligible` alias and the specific chain reviewed above. It grants nothing to any other term, document, or unproven rule, and it does not authorise proof ownership to cross a document boundary anywhere the canonical owner, the delegation, and the alignment linkage are not all explicit and governed.

If any link is later removed — the owner's definition, the delegation sentence, the read-model contract's repetition, or the alignment listing — the basis for this decision fails and `MD-S020-R0067` must be reopened. The proof bound at `MD-B01-A016` asserts each link individually so that removal is caught rather than assumed.

## Related

- `MD-S020-R0189` unchanged and unrelied upon; `MD-S056-R0141`, `MD-S056-R0142` remain `SATISFIED`.
- Independent of `D-MD-20260822-05`, which concerns a process-timing deviation in `MD-B03`.

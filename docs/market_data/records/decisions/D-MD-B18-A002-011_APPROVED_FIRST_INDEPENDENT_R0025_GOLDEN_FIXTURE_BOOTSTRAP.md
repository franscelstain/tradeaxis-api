# Decision — first independent R0025 golden fixture bootstrap

- ID: `D-MD-B18-A002-011`
- Verification epoch: `MD-REBASELINE-20260820-001`
- Stage / Attempt / Baseline: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001`
- Finding / predicate: `F-MD-B18-A002-017` / `MD-S003-R0025`
- Supporting evidence: `E-MD-B18-A002-085` (authority fit and `MD-S003-R0024` verification impact)
- Change impact: `CI-MD-B18-A002-001`
- Issued: 2026-09-27T00:50:07+07:00
- Status: `APPROVED` — owner Option A in the 2026-09-27 first-independent-fixture instruction
- Strategy impact: `NONE`; `MD-STRATEGY-FREEZE-20260925-001` unchanged
- Governance impact: controlled revision of `RUNTIME_ARTIFACT_AND_GOVERNED_EVIDENCE_STANDARD.md` section 6A under `DOC-CHG-20260927-001`; bounded technical-spec clarifications

## Authority fit

`Historical_Replay_and_Data_Quality_Backtest.md` (`MD-S003-R0024`) requires independently reviewed semantic oracles and rejects copied current output without independent derivation. `Golden_Fixtures_Specification.md` already permits reviewed spreadsheet/reference calculations and manual lineage derivation. `Fixture_Package_Manifest_LOCKED.md` requires frozen inputs, independent expected artifacts and file hashes. The missing first-package admission, chronology and review linkage is a governance gap; this decision adds no market-data strategy meaning. `E-MD-B18-A002-085` records the verification-impact review.

## Selected rule — Option A

1. Freeze the authority-relevant input observations, master revisions and configuration; derive expected semantics independently from those frozen inputs by identified manual calculation, reviewed spreadsheet/calculation artifact or separate reference implementation. The derivation method and version, evidence and input lineage must make each expected value traceable. The target run's produced output cannot be the source of expected truth. A different `run_id` alone does not establish independence. Copying or relabelling a same-target generated fixture remains forbidden, and `REPLAY_FIXTURE_SELF_GENERATED` remains fail-closed.
2. Independently review the expected semantics and derivation. Before admission, a governed record must identify the reviewed expected semantics, derivation evidence, frozen-input identity/lineage, oracle/method ID and version, reviewer identity, owner approval, fixture version, and package/file/manifest hashes. Owner approval admits only the reviewed fingerprint once all required evidence exists. A free-form `fixture_source` or the presence of expected files alone is insufficient.
3. Freeze the approved package and manifest immutably. The package manifest binds stable frozen inputs, oracle/derivation reference and version, expected-content identity, fixture version and canonical file hashes. The governed review/approval record binds reviewer, derivation evidence, approval and approved package/manifest fingerprint. The target verification record separately binds the target run/publication IDs and result to that approved fingerprint. Volatile target identity is kept out of semantic expected-content hashes.
4. A fixture independently authored without consuming target output may verify an already executed target publication. Approval and freeze must precede the governed verification/proof claim; they need not precede target execution. Target output may be inspected for comparison only after independent expected values are established and cannot retroactively become their source. Later approval cannot rehabilitate a target-generated package.
5. Approved package files and manifest are immutable. A semantic expected-value correction creates a new fixture version with renewed independent derivation, review and approval; retain the old version and do not silently edit it.

## Verification effect and limits

This decision defines fixture admissibility only. It constructs and approves no fixture, runs no R0025 proof, and does not change `MD-S003-R0025` from `INCOMPLETE` or resolve `MD-DEP-0019`. The `MD-S003-R0024` guard proving rejection of relabelled self-generated expectations remains valid on its exact scope; it cannot be cited as proof of a future approved package. `F-017` remains OPEN on `MD-S003-R0025` and `MD-S050-R0005`; formal `SATISFIED` is unchanged. The next bounded work is to build and review the first independent R0025 golden fixture under this rule, before resuming the predicate proof campaign.

# Decision — owner selections Q4=A, Q5=B, Q6=B, Q7=A for the R0025 candidate-v2 prerequisites

- ID: `D-MD-B18-A002-014`
- Verification epoch: `MD-REBASELINE-20260820-001`
- Stage / Attempt / Baseline: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001`
- Findings / predicate: `F-MD-B18-A002-025`, `F-MD-B18-A002-027`, `F-MD-B18-A002-028`, `F-MD-B10-A002-004` / `MD-S003-R0025`
- Answers: the four owner decisions Q4 to Q7 of `E-MD-B18-A002-093`
- Change impact: `CI-MD-B18-A002-001`
- Issued: 2026-10-03T10:25:05+07:00
- Status: `APPROVED` — project owner, in the 2026-10-03 instruction "OWNER DECISION Q4/Q5/Q6/Q7 APPROVED"
- Mutability: `IMMUTABLE_AFTER_ISSUE`
- Strategy impact: `NONE`; `MD-STRATEGY-FREEZE-20260925-001` unchanged

## Decisions

**Q4 — formula and reason registry identity of a V2 replay: option A.** For V2-profile replay binding, `formula_registry_hash` represents the governed formula, indicator and related registry semantic content of its own domain; `reason_registry_hash` represents the governed reason-registry semantic content of its own domain. Executable-build content is not folded into either. A shared frozen registry capture may stay the operational source, but each V2 identity is derived from its semantic content. No local id, no allocation. V1 historical semantics (whole-payload hash) stay unchanged. The member set is the one stated for option A in `E-MD-B18-A002-093`: for the formula identity `registry_contract`, `semantic_versions`, `indicator_set_version`, `coverage_contract_version`, `eligibility_contract_version`, `config_registry_revision`, `config_resolver_version`, `serialization_version`, `read_model_version`; for the reason identity the canonical reason entries.

**Q5 — executable build identity: option B.** `executable_build_identity` is a required frozen publication-exact input, asserted as a literal frozen build identity and never as a target-bound marker. It is derived from the current governed build-identity mechanism (`ProducerRegistrySnapshot::build`, schema `php_source_build_v1`); the candidate freezes the exact build manifest and identity it used and retains derivation evidence sufficient for independent review. The standalone oracle consumes the frozen build input and does not inspect the running tree; a mismatch between the frozen expected build identity and the actual publication-bound one fails. Current build semantics are not replaced. Consequence the owner accepted by selecting B: the approval of a package is valid only for the build it froze; a later change to the build needs a new candidate version.

**Q6 — provider source timestamp: option B.** The provider chart-series timestamp is a provider/source observation fact. It is preserved in the immutable observation envelope and observation-manifest semantics when available; the platform acquisition and received time is preserved separately and is never labelled as the provider or source timestamp; the canonical bar `source_timestamp` stays NULL for the synthetic world unless higher-precedence bar authority proves otherwise; the provider timestamp is not discarded because the bar does not expose it. The frozen provider timestamp of the synthetic world is `1774231200`. This is an authorised correction of the production defect `F-MD-B18-A002-027`. Before the observation semantic producer changes, the B10 impact analysis is performed; an old incorrect hash is not preserved to keep B10 green.

**Q7 — V2 `event_factor_hash`: option A.** For V2 only the allocation-sensitive ancillary-group representation is removed and replaced by a deterministic semantic projection of the relevant contamination decisions. The identity still binds the authority-required semantic domains (event revision set, factor-set semantic identity, factor decisions, source-scale, market-structure and contamination decisions where current authority includes them) and is not reduced to contamination decisions alone. The projection contains semantic facts only: no `ticker_id`, `listing_id` or local listing key, `run_id`, `publication_id`, row id or local allocation order. V1 is unchanged. For the empty-event synthetic world a deterministic literal identity of the frozen empty state is derived.

## Conditions that stay in force

Candidate-v1 (fingerprint `05b717c63ef1f5f96a759ed6d2160b46e4eb9d1c9c946e2a03d373229abf26c0`) stays reviewed, `CHANGES REQUIRED`, never approved, never edited or reused. Candidate-v2 is a distinct package and is built to stop at independent review. All eleven replay bound inputs are asserted with no NULL expectation and no `@TARGET` marker. `MD-S003-R0025` stays `INCOMPLETE`; `F-MD-B18-A002-025` and `F-MD-B18-A002-017` stay open; `F-MD-B10-A002-004` is reassessed against its closure criteria and not resolved automatically; F-018 is not started.

## What this decision does not do

It approves no package, decides no predicate, resolves no finding by itself, does not amend `D-MD-B10-A002-005` or `D-MD-B18-A002-006` for V1, and does not reopen or close B10: the impact on the closed B10 proof is assessed from the executed code and evidence, not assumed.

# Legacy Semantic Extract — LX-MD-0030-CTX-02

- Source ID: `LS-MD-0030`
- Original path: `audit/LUMEN_CONTRACT_TRACKER.md`
- Original SHA1: `88A4CD4C9D12A578A2837DCB275B315C91D1492A`
- Extract role: `CONTEXT`
- Source range: `L3488-L3507`
- Extract body SHA1: `4FE1FD544787BE38852841797A05549FC2E914CF`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## COVERAGE_GATE_CANDIDATE_SCOPE_HARDENING — COVERAGE_GATE_ENFORCEMENT_CONTRACT

- Status: SUPERSEDED_BY_LOCKED_CANONICAL_CONTRACT / HISTORICAL_TRANSITION_ONLY.
- Current authority: canonical `COVERAGE_GATE_ENFORCEMENT_CONTRACT -> LOCKED` entry above and `COVERAGE_GATE_CANDIDATE_SCOPE_HARDENING_INVENTORY.md`; previous aggregate `FULL_MARKET_DATA_PRODUCTION_READY_CONTRACT` REVIEW_REQUIRED wording is superseded by the final provider smoke PASS and full MarketData PHPUnit PASS.  **[SUPERSEDED 2026-08-06 — W22]** Klaim ini tidak berlaku untuk baseline yang dikoreksi; lihat `reports/AUDIT_FINAL_STATE.md`.
- Related implementation: Coverage Gate Candidate Scope Hardening.
- Existing owner: `COVERAGE_GATE_ENFORCEMENT_CONTRACT`; this is not coverage gate enforcement ulang and does not replace prior coverage gate enforcement history.
- Enforcement hardening: promote, manual promote, and correction coverage evaluation must use candidate publication / candidate artifact scope as coverage basis.
- The correction candidate must be evaluated separately from baseline/current publication.
- Baseline/current publication may be used for correction lineage, comparison, fallback preservation, and unchanged detection only. It must not be used as coverage basis for candidate publishability.
- Runtime patch: `MarketDataPipelineService` resolves `coverageBasisPublicationId` before coverage evaluation and records candidate/baseline proof in run notes.
- Runtime patch: `EodArtifactRepository` resolves candidate coverage ticker ids from `eod_bars_history` and `eod_bars` using explicit `publication_id`; no current pointer/latest/MAX-date fallback is used.
- Proof surface: command output, evidence export, and replay actual context expose `coverage_basis`, `coverage_basis_publication_id`, `coverage_basis_artifact_scope`, `candidate_publication_id`, and `baseline_publication_id`.
- Static guard: `CoverageGateCandidateScopeHardeningStaticGuardTest.php`.
- Inventory: `COVERAGE_GATE_CANDIDATE_SCOPE_HARDENING_INVENTORY.md` records `DONE_LOCAL_PHPUNIT_PASS / LOCKED_LOCAL_PHPUNIT_PASS` with full `tests/Unit/MarketData` OK (397 tests, 5461 assertions).
- Historical lock condition: SATISFIED by later operator-local targeted/full PHPUnit proof and the canonical locked coverage contract. Do not read this historical transition section as a current blocker.


---



<!-- LEGACY_EXTRACT_BODY_END -->

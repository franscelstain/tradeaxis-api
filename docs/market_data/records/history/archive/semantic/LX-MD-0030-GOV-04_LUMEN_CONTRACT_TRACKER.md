# Legacy Semantic Extract — LX-MD-0030-GOV-04

- Source ID: `LS-MD-0030`
- Original path: `audit/LUMEN_CONTRACT_TRACKER.md`
- Original SHA1: `88A4CD4C9D12A578A2837DCB275B315C91D1492A`
- Extract role: `GOVERNANCE`
- Source range: `L4226-L4249`
- Extract body SHA1: `BDA3010DEAD354FD42824CA0B15CEFA07E4EB244`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## 2026-05-27 - OUT-OF-ORDER IMPORT PUBLICATION LIFECYCLE CONTRACT BOUNDARY

[CONTRACT_STATUS]
- Superseded by the final readable auto-correction contract below. The aggregate out-of-order import publication lifecycle now includes recovered apply, indicator/eligibility execution, non-readable promotion, and readable correction-current republication.

[BOUNDARY]
- Current executor performs:
  - recovered row partial upsert,
  - bar mutation summary,
  - affected-date reprocess detection,
  - indicator recompute,
  - eligibility rebuild,
  - readable publication correction-current candidate emission.
- Current executor does not perform:
  - downstream hash/seal/finalize directly; lifecycle/full-publish paths consume candidates and run guarded promote flows,
  - evidence/replay for a replacement publication unless the promotion path actually produces one.

[CONTRACT_RULE]
- `publication_reprocess_summary.execution_state=NOOP`, `PENDING_PROMOTE`, or `BLOCKED_REQUIRES_CORRECTION` must not be interpreted as republished.
- `indicator_reprocess_execution_state=EXECUTED` and `eligibility_reprocess_execution_state=EXECUTED` must not be interpreted as hash/seal/finalize execution.
- Full lock requires lifecycle/full-publish orchestration proof that candidates are promoted or corrected through existing hash/seal/finalize/republication guards.

---


<!-- LEGACY_EXTRACT_BODY_END -->

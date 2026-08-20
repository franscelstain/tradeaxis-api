# Legacy Semantic Extract — LX-MD-0024-DEC-01

- Source ID: `LS-MD-0024`
- Original path: `audit/EVIDENCE_EXPORT_RUNTIME_PROOF_INVENTORY.md`
- Original SHA1: `CE951167381AE5231B705EE619EA1FECEEC18A9E`
- Extract role: `DECISION`
- Source range: `L16-L28`
- Extract body SHA1: `EB3BF757B5BFAFF9F3BF2CD91A36E7B34413177E`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Decision

| Decision Area | Final Rule | Status |
|---|---|---|
| selector rule | exactly one of `--run_id`, `--correction_id`, or `--replay_id` is required | ENFORCED |
| replay selector | `--replay_id` requires explicit `--trade_date`; latest-row lookup is forbidden | ENFORCED |
| run evidence admission | write `evidence_admission.json` plus `evidence_completeness.json` | PATCHED |
| correction evidence admission | write `evidence_admission.json` plus `correction_evidence.json` | PATCHED |
| replay evidence admission | write `evidence_admission.json` plus replay expected/actual/result/reason-code artifacts | PATCHED |
| silent missing metadata | forbidden; admission artifact exposes missing/critical sections | ENFORCED |
| current vs historical proof | current consumer result and historical audit evidence remain separated by evidence resolution mode and pointer context | PRESERVED |
| runtime proof status | operator-local run, correction, and replay runtime exports produced admitted artifacts and post-patch PHPUnit/static/full-suite proof passed | LOCKED_FULL_SELECTOR_RUNTIME_PROOF |


<!-- LEGACY_EXTRACT_BODY_END -->

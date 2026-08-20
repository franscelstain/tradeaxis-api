# Legacy Semantic Extract — LX-MD-0201-GOV-01

- Source ID: `LS-MD-0201`
- Original path: `ops/OPS_ENVIRONMENT_BASELINE.md`
- Original SHA1: `4CD43340DAE04A7BB47B9DBDD430FACBC6FCAEF5`
- Extract role: `GOVERNANCE`
- Source range: `L121-L127`
- Extract body SHA1: `1F0E3BCD9255CD177648F39F3493B9683D170FD1`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Final rule

No future market-data session may use command output as evidence when the output contains PHP warnings, PHP deprecations, vendor/framework deprecations, missing-extension warnings, timezone warnings, debug noise, or stack traces caused by environment mismatch.

A clean unsupported-environment block is acceptable as `BLOCKED_CONTAINER_RUNTIME_ENV`; it is not a runtime PASS and must not be used to mark a market-data implementation DONE/LOCKED. Operator-local targeted proof and final full `tests/Unit/MarketData` proof have been supplied after the Config / ENV guard synchronization patch.



<!-- LEGACY_EXTRACT_BODY_END -->

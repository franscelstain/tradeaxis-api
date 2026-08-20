# Legacy Semantic Extract — LX-MD-0239-GOV-01

- Source ID: `LS-MD-0239`
- Original path: `tests/Behavioral_Test_Coverage_Inventory.md`
- Original SHA1: `742C746586E0501E4D5B9983BCDE37F7B3DFC30F`
- Extract role: `GOVERNANCE`
- Source range: `L16-L19`
- Extract body SHA1: `050E02414E207CBEF4CEA51537EA68DDD4B95C0B`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Rule

Market-data test coverage is only valid when the test proves runtime behavior and durable state, not just assertion volume, method-level implementation details, or mock-shaped return values. DB-backed integration tests are the primary proof for lifecycle-critical behavior. Unit tests and static guards are support proof only. Command-surface tests may use command/service mocks for operator output coverage, but those tests do not count as lifecycle proof unless they assert real DB/evidence/replay state through runtime-like execution.


<!-- LEGACY_EXTRACT_BODY_END -->

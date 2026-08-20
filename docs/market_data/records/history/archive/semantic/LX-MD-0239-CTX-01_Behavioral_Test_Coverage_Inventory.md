# Legacy Semantic Extract — LX-MD-0239-CTX-01

- Source ID: `LS-MD-0239`
- Original path: `tests/Behavioral_Test_Coverage_Inventory.md`
- Original SHA1: `742C746586E0501E4D5B9983BCDE37F7B3DFC30F`
- Extract role: `CONTEXT`
- Source range: `L64-L80`
- Extract body SHA1: `F536A70272C3316D5934CEB8C1EB0048E93C90E6`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Final mock policy

Allowed mocks:

- external provider API
- file system fixture adapter where isolation is required
- clock/time provider
- console IO and command surface output
- explicit orchestration shell tests that do not claim lifecycle proof

Not accepted as behavioral proof:

- mocked internal repository lifecycle
- mocked internal service return state for finalize/pointer/correction/replay/evidence proof
- command output test without DB/proof state assertion
- static guard alone


<!-- LEGACY_EXTRACT_BODY_END -->

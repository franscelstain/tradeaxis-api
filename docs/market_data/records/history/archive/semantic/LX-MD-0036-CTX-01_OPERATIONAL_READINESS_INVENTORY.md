# Legacy Semantic Extract — LX-MD-0036-CTX-01

- Source ID: `LS-MD-0036`
- Original path: `audit/OPERATIONAL_READINESS_INVENTORY.md`
- Original SHA1: `5052578CFB667B3B0F992126B669430E12EFBA12`
- Extract role: `CONTEXT`
- Source range: `L68-L73`
- Extract body SHA1: `0E6E2541F21B9E0DDB8123A357DFA064066848E2`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## 2026-05-08 Replay fixture generation update

- `market-data:replay:fixture:generate` was added after Production Validation runtime replay proved that committed `valid_case` can become stale against local run context.
- Expected command discovery after this patch is 20 market-data commands, including `market-data:replay:fixture:generate`.
- Operators must use generated runtime fixtures for local MATCH proof when committed smoke fixtures do not match the local run/publication/pointer context.
- Required proof remains: generated fixture verify returns `comparison_result=MATCH`, `mismatch_count=0`, and smoke with `--generate_runtime_valid_case` returns `all_passed=1`.

<!-- LEGACY_EXTRACT_BODY_END -->

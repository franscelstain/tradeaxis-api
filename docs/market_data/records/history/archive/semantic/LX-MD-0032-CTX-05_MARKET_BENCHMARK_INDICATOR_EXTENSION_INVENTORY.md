# Legacy Semantic Extract — LX-MD-0032-CTX-05

- Source ID: `LS-MD-0032`
- Original path: `audit/MARKET_BENCHMARK_INDICATOR_EXTENSION_INVENTORY.md`
- Original SHA1: `D0BE40DBBA4BFF89D35ED251D64531E8EF56FC39`
- Extract role: `CONTEXT`
- Source range: `L155-L163`
- Extract body SHA1: `7D276E5DF599DA95BF8DADF1368D5A8A1527A66A`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Replay Verify Status
- PASS. Runtime-generated replay fixture and verify for `run_id=3` completed with `replay_id=2`, `comparison_result=MATCH`, `replay_status=PASS`, and `mismatch_count=0`.
- Replay hash input now includes the new equity indicator columns so deterministic publication proof includes the extension.

## Config / ENV Governance
- No new ENV keys were added.
- Existing Yahoo/API config remains the source of provider endpoint, suffix, timeout, headers, and retry behavior.
- No unused benchmark ENV/config keys are introduced.


<!-- LEGACY_EXTRACT_BODY_END -->

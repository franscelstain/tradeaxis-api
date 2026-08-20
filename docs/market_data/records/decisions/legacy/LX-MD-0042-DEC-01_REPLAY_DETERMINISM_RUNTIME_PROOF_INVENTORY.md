# Legacy Semantic Extract — LX-MD-0042-DEC-01

- Source ID: `LS-MD-0042`
- Original path: `audit/REPLAY_DETERMINISM_RUNTIME_PROOF_INVENTORY.md`
- Original SHA1: `7E5FB7DE9A03E174497EC8911DE7215EE2F3EEEC`
- Extract role: `DECISION`
- Source range: `L8-L13`
- Extract body SHA1: `101C7734E81C0D65D52E3938FDE9F735FAF6884C`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Decision

Replay determinism runtime proof is complete for the current-readable fixture scope, command/evidence surfaces, and historical non-current publication scope. The runtime proof produced explicit `PASS`, `FAIL`, and `BLOCKED` outcomes. Historical replay is locked as an explicit-context audit path because the current source ZIP includes the required historical non-current runtime artifact pack with `historical_publication_allowed=true`, `replay_actual_resolution_mode=HISTORICAL_PUBLICATION_AUDIT`, and `replay_publication_scope=HISTORICAL_SEALED_PUBLICATION`.

This inventory now supports full market-data production readiness for this source ZIP because the historical non-current replay runtime artifacts are supplied and the `FULL_MARKET_DATA_PRODUCTION_READY_INVENTORY.md` lock conditions are satisfied.  **[SUPERSEDED 2026-08-06 — W22]** Klaim ini tidak berlaku untuk baseline yang dikoreksi; lihat `reports/AUDIT_FINAL_STATE.md`.


<!-- LEGACY_EXTRACT_BODY_END -->

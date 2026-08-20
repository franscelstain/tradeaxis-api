# Legacy Semantic Extract — LX-MD-0213-DEC-01

- Source ID: `LS-MD-0213`
- Original path: `patches/TRADING_STATUS_SOURCE_MODEL_SIMPLIFICATION_2026_07_02.md`
- Original SHA1: `3A438F772827F234842C4CCDEBFE8AB9A783C588`
- Extract role: `DECISION`
- Source range: `L6-L9`
- Extract body SHA1: `B0E7198E23B0ED14BC792903897520E2C97D615A`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Decision

`market_data_trading_status_events` is now a source-event table only. It stores the canonical event identity and source metadata, not duplicated semantic interpretation fields.


<!-- LEGACY_EXTRACT_BODY_END -->

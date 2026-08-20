# Legacy Semantic Extract — LX-MD-0213-IMP-01

- Source ID: `LS-MD-0213`
- Original path: `patches/TRADING_STATUS_SOURCE_MODEL_SIMPLIFICATION_2026_07_02.md`
- Original SHA1: `3A438F772827F234842C4CCDEBFE8AB9A783C588`
- Extract role: `IMPLEMENTATION`
- Source range: `L63-L70`
- Extract body SHA1: `7F280B72C9BFADE6E328B4432668494EAFC6E04B`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Impacted implementation

- Migration `2026_06_04_000001_add_event_risk_source_context` creates the canonical dictionary and simplified event table for fresh installs.
- Migration `2026_06_30_000001_refine_market_data_trading_status_semantics` no longer creates duplicated semantic columns.
- Migration `2026_07_02_000001_simplify_trading_status_event_source_model` upgrades legacy tables by moving legacy `status_code` rows to `event_type_code` and dropping duplicated semantic columns.
- `ImportTradingStatusEventsCommand` now accepts only canonical CSV format and blocks legacy semantic headers.
- `EventRiskSourceRepository` resolves coverage/risk state from `market_data_trading_status_event_types`.


<!-- LEGACY_EXTRACT_BODY_END -->

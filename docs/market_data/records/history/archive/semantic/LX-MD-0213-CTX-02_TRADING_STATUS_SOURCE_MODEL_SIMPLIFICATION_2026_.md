# Legacy Semantic Extract — LX-MD-0213-CTX-02

- Source ID: `LS-MD-0213`
- Original path: `patches/TRADING_STATUS_SOURCE_MODEL_SIMPLIFICATION_2026_07_02.md`
- Original SHA1: `3A438F772827F234842C4CCDEBFE8AB9A783C588`
- Extract role: `CONTEXT`
- Source range: `L10-L38`
- Extract body SHA1: `B3CAD5A4B7158FB0BF99B2271A902817488BC009`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Canonical event table

Required daily import columns:

```csv
ticker_code,trade_date,event_type_code,source_name,source_ref,notes
```

Illustrative sample: `docs/market_data/examples/trading_status_daily.csv`.

Allowed `event_type_code` values:

- `SUSPENDED`
- `SUSPENSION_OBSERVED`
- `UNSUSPENDED`
- `SPECIAL_MONITORING_START`
- `SPECIAL_MONITORING_END`
- `UMA`

Forbidden legacy source-event columns:

- `status_code`
- `status_effect`
- `is_suspended`
- `is_uma`
- `event_risk_scope`
- `coverage_exclusion_flag`
- `expected_bar_policy`


<!-- LEGACY_EXTRACT_BODY_END -->

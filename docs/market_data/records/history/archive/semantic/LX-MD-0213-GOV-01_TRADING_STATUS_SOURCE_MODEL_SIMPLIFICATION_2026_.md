# Legacy Semantic Extract — LX-MD-0213-GOV-01

- Source ID: `LS-MD-0213`
- Original path: `patches/TRADING_STATUS_SOURCE_MODEL_SIMPLIFICATION_2026_07_02.md`
- Original SHA1: `3A438F772827F234842C4CCDEBFE8AB9A783C588`
- Extract role: `GOVERNANCE`
- Source range: `L39-L62`
- Extract body SHA1: `1660815C89C1D0670FFE300A2F1F8D462F3E318E`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Dictionary owner

`market_data_trading_status_event_types` owns the meaning of each event type:

| event_type_code | risk_family | transition_type | expected_bar_policy | carries_forward | clears_risk_family |
|---|---|---|---|---:|---|
| `SUSPENDED` | `SUSPENSION` | `START` | `BAR_NOT_REQUIRED` | 1 |  |
| `SUSPENSION_OBSERVED` | `SUSPENSION` | `OBSERVED` | `BAR_NOT_REQUIRED` | 1 |  |
| `UNSUSPENDED` | `SUSPENSION` | `END` | `BAR_REQUIRED` | 0 | `SUSPENSION` |
| `SPECIAL_MONITORING_START` | `SPECIAL_MONITORING` | `START` | `BAR_REQUIRED_WITH_RISK` | 1 |  |
| `SPECIAL_MONITORING_END` | `SPECIAL_MONITORING` | `END` | `BAR_REQUIRED` | 0 | `SPECIAL_MONITORING` |
| `UMA` | `UMA` | `POINT_IN_TIME` | `BAR_REQUIRED_WITH_RISK` | 0 |  |

## Runtime rule

- `SUSPENDED` is a suspension-start transition; it resolves to `BAR_NOT_REQUIRED` and carries forward until `UNSUSPENDED`.
- `SUSPENSION_OBSERVED` is a source/snapshot observation that suspension remains active, including long-suspension lists; it resolves to `BAR_NOT_REQUIRED` but is not a suspension-start transition.
- `UNSUSPENDED` clears only suspension and returns the ticker to `BAR_REQUIRED` from the effective date.
- `SPECIAL_MONITORING_START` carries event-risk context and does not exclude coverage.
- `SPECIAL_MONITORING_END` clears only special-monitoring context.
- `UMA` is exact-date event-risk context and has no end pair.
- `ACTIVE` is not imported. It is a resolved state when no source-backed risk state remains active.
- Absence of source data must not fabricate `ACTIVE`, include, exclude, or no-risk rows.


<!-- LEGACY_EXTRACT_BODY_END -->

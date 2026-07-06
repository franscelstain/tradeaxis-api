# Trading Status Source Model Simplification — 2026-07-02

## Decision

`market_data_trading_status_events` is now a source-event table only. It stores the canonical event identity and source metadata, not duplicated semantic interpretation fields.

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

## Impacted implementation

- Migration `2026_06_04_000001_add_event_risk_source_context` creates the canonical dictionary and simplified event table for fresh installs.
- Migration `2026_06_30_000001_refine_market_data_trading_status_semantics` no longer creates duplicated semantic columns.
- Migration `2026_07_02_000001_simplify_trading_status_event_source_model` upgrades legacy tables by moving legacy `status_code` rows to `event_type_code` and dropping duplicated semantic columns.
- `ImportTradingStatusEventsCommand` now accepts only canonical CSV format and blocks legacy semantic headers.
- `EventRiskSourceRepository` resolves coverage/risk state from `market_data_trading_status_event_types`.

## 2026-07-04 final clarification

- `LONG_SUSPENSION_GT_6M` and equivalent long-suspension source labels map to `SUSPENSION_OBSERVED`, not `SUSPENDED`.
- `BAR_NOT_REQUIRED` means the ticker is removed from the expected EOD bar denominator before `missing_bar_count` and coverage ratio are calculated. It is not a coverage failure.
- `expected_bar_policy` replaces the ambiguous include/exclude wording for trading-status dictionary semantics.


## 2026-07-04 EOD indicator projection normalization

`eod_indicators.trading_status_code` is a publication-bound projection of source-backed trading status, not the source of truth and not a comma-joined risk list.

Final projection rules:

- no source/no active trading-status risk -> `trading_status_code=NULL`;
- exact `UNSUSPENDED` source event -> `trading_status_code=UNSUSPENDED` on that date only;
- active `SUSPENDED` -> `trading_status_code=SUSPENDED`, `is_suspended=1`;
- active `SUSPENSION_OBSERVED` -> `trading_status_code=SUSPENSION_OBSERVED`, `is_suspended=1`;
- active `SPECIAL_MONITORING_START` -> `trading_status_code=SPECIAL_MONITORING_START`;
- exact `SPECIAL_MONITORING_END` source event -> `trading_status_code=SPECIAL_MONITORING_END` on that date only;
- exact `UMA` source event -> `is_uma=1` and a canonical primary code when no higher-priority exact/active source status is selected;
- legacy `ACTIVE`, `SPECIAL_MONITORING`, `SPECIAL_MONITORING_EXIT`, and comma-composite values must not be emitted by future recompute.

When multiple source-backed risk contexts apply to one ticker/date, `trading_status_code` stores a single deterministic primary canonical code and the full multi-risk context is preserved in `event_risk_reasons`.


## 2026-07-05 final runtime closure

The source model simplification and EOD indicator projection normalization are runtime-proven and locked.

Final operator-local proof:

- `EventRiskSourceRepositoryTest.php` OK (8 tests, 43 assertions).
- `IndicatorVectorServiceTest.php` OK (9 tests, 80 assertions).
- `MarketDataWatchlistReadModelTest.php` OK (3 tests, 41 assertions).
- `MarketDataSqliteSchemaSyncTest.php` OK (5 tests, 306 assertions).
- Audit-doc guard trio OK.
- StaticGuard OK (229 tests, 5872 assertions).
- Full `tests/Unit/MarketData` OK (648 tests, 9577 assertions).
- Recompute-current runtime proof passed across 2023-01-02 through 2026-06-29 from existing current bars, with no source acquisition, no bar ingest, no source/master writes, and no `eod_bars` writes.
- Final DB validation returned zero legacy indicator projection values: no `ACTIVE`, no `SPECIAL_MONITORING`, no `SPECIAL_MONITORING_EXIT`, and no comma-composite `trading_status_code`.
- Final DB validation returned no invalid non-null `trading_status_code` values outside the canonical set.

Final current non-null code distribution from operator validation:

| trading_status_code | row_count |
|---|---:|
| `SPECIAL_MONITORING_END` | 293 |
| `SPECIAL_MONITORING_START` | 50928 |
| `SUSPENDED` | 9660 |
| `UMA` | 1116 |
| `UNSUSPENDED` | 748 |

`SUSPENSION_OBSERVED` remains an allowed canonical projection value. Its absence from the current non-null indicator distribution is valid when suspension-observed tickers have no bar/indicator rows for those dates because `BAR_NOT_REQUIRED` excludes them from expected-bar coverage rather than forcing empty indicator rows.

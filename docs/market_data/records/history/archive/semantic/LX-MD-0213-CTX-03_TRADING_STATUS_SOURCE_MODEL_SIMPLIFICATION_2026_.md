# Legacy Semantic Extract — LX-MD-0213-CTX-03

- Source ID: `LS-MD-0213`
- Original path: `patches/TRADING_STATUS_SOURCE_MODEL_SIMPLIFICATION_2026_07_02.md`
- Original SHA1: `3A438F772827F234842C4CCDEBFE8AB9A783C588`
- Extract role: `CONTEXT`
- Source range: `L71-L123`
- Extract body SHA1: `313FE75506D0248EAC8BAF385A44770DED0B277B`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
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

<!-- LEGACY_EXTRACT_BODY_END -->

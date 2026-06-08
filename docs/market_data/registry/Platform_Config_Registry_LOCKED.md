# Platform Configuration Registry (LOCKED)

Defines output-affecting configuration that must be versioned, effective-dated, and audit-linked to each run.

## Registry principles (LOCKED)
- each config key has one authoritative meaning
- output-affecting keys require effective start date and change note
- historical replay must use the config effective for the replayed trade date unless explicitly testing an alternate scenario
- undocumented runtime overrides are forbidden for production output
- each finalized run must carry enough config identity to prove which effective registry snapshot was used

## Minimum keys
### Core data production
- `COVERAGE_MIN`
- `PRICE_BASIS_DEFAULT`
- `LOT_SIZE`
- `CUT_OFF_GRACE_MINUTES`
- `PLATFORM_EOD_CUTOFF_TIME`
- `SEAL_REQUIRED_FOR_CONSUMERS` (default `true`)
- `PLATFORM_TIMEZONE`

### Indicator windows
- `DV_WINDOW_DAYS` = 20
- `ATR_WINDOW_DAYS` = 14
- `VOL_RATIO_LOOKBACK_DAYS` = 20 (prior days, excluding D)
- `ROC_LOOKBACK_DAYS` = 20
- `HH_WINDOW_DAYS` = 20
- `MA20_WINDOW_DAYS` = 20 when exposed as config; otherwise locked by indicator specification
- `MA50_WINDOW_DAYS` = 50 when exposed as config; otherwise locked by indicator specification

Indicator dependency windows are counted in market-calendar trading days. Calendar-day subtraction is not a valid source of truth for loading indicator history.

### Hash / serialization
- `HASH_ALGORITHM` = `SHA-256`
- `HASH_DELIMITER` = `|`
- `HASH_LINE_SEPARATOR` = `\n`
- `HASH_NULL_TOKEN` = empty string

### Provider/runtime
- `API_RETRY_MAX`
- `API_BACKOFF_MS`
- `API_THROTTLE_QPS`
- `CIRCUIT_BREAKER_ERROR_RATE`
- `API_BACKFILL_WINDOW_DAYS` = provider request-window span, expressed as calendar-day source acquisition windows
- `API_BACKFILL_WARMUP_DAYS` = legacy compatibility fallback only
- `API_BACKFILL_WARMUP_TRADING_DAYS` = 120 by default; source-acquisition warmup dependency expressed in market-calendar trading days

`API_BACKFILL_WARMUP_TRADING_DAYS` is output-affecting because it controls whether rolling indicators such as MA50, ROC20, ATR14, DV20, and sector benchmark dependencies have enough canonical history before requested-date promotion. If both legacy warmup-days and warmup-trading-days are present, the trading-day setting is the authoritative lifecycle warmup input.

### Session snapshot
- `INTRADAY_RETENTION_DAYS`
- `INTRADAY_SCOPE_DEFAULT`
- `SNAPSHOT_SLOT_TOLERANCE_MINUTES`

## Required registry metadata
For each key/value change, record at minimum:
- config key
- value
- effective start date
- change note / change ticket
- changed by
- whether replay output is affected

## Run-linked config identity (LOCKED)
Each finalized run must preserve config identity through at least one of the following implementation patterns:
1. `eod_runs.config_version` + `eod_runs.config_hash`, or
2. an immutable artifact referenced from `eod_runs` that stores the exact effective config snapshot used by the run.

Minimum requirement:
- the effective configuration used by a sealed run must be reproducible without guessing from current registry state
- two runs with identical canonical inputs but different effective config snapshots must be auditable as different run contexts
- config identity itself does not belong inside content hashes unless the canonical output fields actually change; it belongs to provenance/audit linkage

## Locked anti-drift rule
When a config key changes and can alter canonical output, replay expectations and fixtures must be versioned accordingly.
Silent config drift is forbidden.

## Amendment 2026-06-05 - Market-calendar warmup config
Lifecycle API backfill warmup must use `API_BACKFILL_WARMUP_TRADING_DAYS` and resolve `warmup_start` through `market_calendar` trading-day sequence ending at the first requested trading date.

Locked rules:
- do not compute lifecycle warmup by `subDays()` / calendar-day subtraction
- do not treat `API_BACKFILL_WARMUP_DAYS` as authoritative when `API_BACKFILL_WARMUP_TRADING_DAYS` is configured
- fail fast when the requested date is not an active trading day in `market_calendar`
- fail fast when the configured trading-day warmup window cannot be resolved from available calendar rows
- `.env.example` and config registry must stay synchronized whenever a `MARKET_DATA_*` key is added or removed

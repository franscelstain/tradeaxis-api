# WS Downside Stability C02 Design Note

## Purpose

This note records the C02 semantic catalog design derived from the C01 IS failure drilldown review. It is design/implementation evidence only. It is not OOS proof, not promotion evidence, and not production readiness evidence.

## Catalog identity

```text
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C02_2026_06
catalog_version=C02
catalog_count=8
catalog_hash=7287c438e15bd03d6beb4796e4d5159ecd8ed59a
```

C02 is a semantic C-campaign catalog, not an R-series continuation. It does not rename, mutate, or reinterpret R1, R2, or C01.

## Source evidence used

C02 uses only evidence already present in the current workspace:

- C01 failed IS calibration result: `is_valid_param_count=0`, no best IS binding, no OOS execution.
- C01 runtime drilldown buckets recorded in `_refs/WS_C01_IS_FAILURE_DRILLDOWN_NOTE.md`.
- C01 drilldown feature fields exported by runtime evidence: `close_to_hh20_pct`, `roc20`, `vol_ratio`, `dv20_idr`, `sector_code`, and score components.

## Derived design focus

C01 diagnostic review pointed to this next design focus:

```text
anti-chase / moderate-liquidity-volume / near-breakout / sector-aware stability review
```

C02 translates that into existing runtime-consumed axes only:

- tighter near-breakout bands instead of momentum chasing;
- moderate volume ratio, avoiding high volume spike chase;
- moderate DV20 buckets instead of very broad `>20B` dominance;
- narrower ROC around the positive diagnostic bucket;
- stronger risk/downside stability weighting where needed.

## Sector evidence handling

Sector evidence stays diagnostic-only in C02.

The existing persisted grid and paramset projection do not contain a safe sector filter axis. Therefore C02 does not add `sector_code`, `sector_filter`, or any unsupported sector allow/deny list. The row `07_STABILITY_PROXY_SECTOR_REVIEW` uses only existing liquidity, volume, ATR, ROC, breakout, scoring, and grouping axes as a proxy for stability review.

Canonical marker:

```text
sector_filter_used=false
sector_evidence_usage=DIAGNOSTIC_REVIEW_ONLY_EXISTING_AXIS_PROXY
```

## Rows

| Row code | Intent |
|---|---|
| `00_C01_NEAREST_GATE_REFERENCE` | Reference copy of C01 `01_LOW_ATR_BREADTH`, the nearest average-return gate row, for drift measurement only. |
| `01_NEAR_BREAKOUT_MODERATE_LIQUIDITY` | Near-breakout and moderate-liquidity anti-chase probe. |
| `02_MID_LIQUIDITY_VOLUME_BALANCED` | Balance 2.5B-15B DV20 evidence while avoiding volume spikes. |
| `03_STRICT_NEAR_BREAKOUT_LOW_CHASE` | Strictest anti-chase row, breakout/risk-led. |
| `04_LOW_ATR_MID_ROC_STABILITY` | Low ATR and ROC 0.02..0.05 stability probe. |
| `05_VOLUME_NOT_SPIKE_RISK_FIRST` | 1.2..1.5 volume bucket and risk-first scoring. |
| `06_BROAD_SAMPLE_NEAR_BREAKOUT` | Broader sample control with near-breakout and moderate volume retained. |
| `07_STABILITY_PROXY_SECTOR_REVIEW` | Existing-axis proxy for sector-aware stability review; no sector filter. |

## Implemented files

- `app/Application/Watchlist/Services/WatchlistBacktestC02ParamGridCatalog.php`
- `app/Console/Commands/Watchlist/SeedBacktestC02ParamGridCommand.php`
- `database/seeders/Watchlist/WatchlistBacktestC02ParamGridSeeder.php`
- repository allowlist update for C02 catalog identity;
- paramset factory support for C02;
- IS calibration artifact definition for C02 with C01 immutability proof;
- unit/static tests for C02 catalog, factory mapping, and guardrails.

## Local validation in authoring environment

Executed in the current sandbox:

```text
php -l C02 PHP files and modified Watchlist files = PASS
C02 pure PHP catalog/factory smoke = PASS / exit code 0
```

Blocked in the current sandbox:

```text
php vendor/bin/phpunit tests/Unit/Watchlist --filter "WatchlistBacktestC02" = BLOCKED / exit code 1
reason=missing PHP extensions: dom, mbstring, xml, xmlwriter

php artisan list = BLOCKED / exit code 2
reason=ENV_UNSUPPORTED_PHP_VERSION; current PHP 8.4.16, project guard requires PHP >= 7.3 and < 8.4
```

No PHPUnit, Artisan seed, migration, calibration, OOS proof, replay, diagnostic run, or database write proof is claimed for C02 until operator output is provided from a supported environment.

## Required operator validation before claiming C02 runtime PASS

1. Run C02 unit/static tests.
2. Run full Watchlist unit tests.
3. Seed C02 and confirm R1/R2/C01 immutable markers.
4. Run C02 IS calibration twice on the frozen IS window.
5. Compare artifact hashes across the two C02 runs.
6. Keep OOS unread unless C02 produces at least one valid IS binding and the separate OOS proof session is explicitly opened.

Until those outputs are supplied, C02 status remains:

```text
IMPLEMENTED_CODE_AND_STATIC_GUARDS_READY / RUNTIME_VALIDATION_BLOCKED_IN_AUTHORING_ENV / OPERATOR_VALIDATION_REQUIRED / NOT_PRODUCTION_READY
```

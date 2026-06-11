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

Executed in the authoring sandbox before operator validation:

```text
php -l C02 PHP files and modified Watchlist files = PASS
C02 pure PHP catalog/factory smoke = PASS / exit code 0
```

Blocked in the authoring sandbox:

```text
php vendor/bin/phpunit tests/Unit/Watchlist --filter "WatchlistBacktestC02" = BLOCKED / exit code 1
reason=missing PHP extensions: dom, mbstring, xml, xmlwriter

php artisan list = BLOCKED / exit code 2
reason=ENV_UNSUPPORTED_PHP_VERSION; current PHP 8.4.16, project guard requires PHP >= 7.3 and < 8.4
```

## Operator validation final result

Operator output was supplied from the supported project environment. C02 is now validated for implementation/test/seed/execution, but rejected as a strategy-quality catalog.

```text
C02 PHPUnit = PASS / OK (12 tests, 391 assertions)
Full Watchlist PHPUnit = PASS / OK (238 tests, 4182 assertions)
C02 seed = PASS / catalog_count=8 / inserted_count=8 / updated_count=0 / r1_immutable=1 / r2_immutable=1 / c01_immutable=1 / oos_executed=0 / production_ready=0
C02 IS run 1 = C02_GRID_FAILED_IS_QUALITY / is_valid_param_count=0 / is_failed_param_count=8 / artifact_hash=81da37a1c526cf71c096a4be6fc8623b013ae3a2
C02 IS run 2 = C02_GRID_FAILED_IS_QUALITY / is_valid_param_count=0 / is_failed_param_count=8 / artifact_hash=81da37a1c526cf71c096a4be6fc8623b013ae3a2
```

C02 two-run determinism is proven for the reported artifact hash:

```text
artifact_hash_run_1=81da37a1c526cf71c096a4be6fc8623b013ae3a2
artifact_hash_run_2=81da37a1c526cf71c096a4be6fc8623b013ae3a2
hash_equal=1
```

## Post-docs validation evidence

After the final C02 documentation and forensic CSV sync, operator validation was rerun for the focused C02 test and the full Watchlist suite.

```text
scope=DOCUMENTATION_AND_FORENSIC_CSV_ONLY
runtime_code_changed=false
catalog_changed=false
seed_rerun_required=false
calibration_rerun_required=false
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC02" = PASS / OK (12 tests, 391 assertions) / Time 00:01.281 / Memory 14.00 MB / exit code 0
vendor\bin\phpunit tests\Unit\Watchlist = PASS / OK (238 tests, 4182 assertions) / Time 00:04.431 / Memory 24.00 MB / exit code 0
post_docs_validation_verdict=PASS
```

This is docs/static-guard validation evidence only. It is not a new seed, not a new calibration, not OOS proof, and not production-readiness evidence.

## Final forensic result

C02 artifact manifest:

```text
artifact_version=WATCHLIST_C02_IS_CALIBRATION_V1
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C02_2026_06
catalog_version=C02
catalog_count=8
catalog_hash=7287c438e15bd03d6beb4796e4d5159ecd8ed59a
is_from=2023-01-02
is_to=2025-05-21
trading_date_count=562
valid_count=0
failed_count=8
best_is_binding_empty=true
strict_is_boundary=true
oos_executed=false
production_ready=false
catalog_hash_matches=true
r1_immutable=true
r2_immutable=true
c01_immutable=true
no_oos_market_read=true
no_oos_table_mutation=true
```

All eight C02 rows failed all three quality families:

```text
WS_BT_EVAL_DOWNSIDE_FAIL=8
WS_BT_EVAL_ROBUST_RETURN_FAIL=8
WS_BT_EVAL_STABILITY_FAIL=8
```

The representative gate detail shows C02 had enough sample but poor quality:

```text
minimum_coverage=true
minimum_trade_count=true
average_return_positive=false
median_return_non_negative=false
monthly_average_floor=false
monthly_win_rate_floor=false
p25_downside_bound=false
```

Metric range across all C02 rows:

```text
days_covered=506..508
picks_count=1360..1435
win_rate_top=39.44%..41.82%
median_ret_net_top=-2.10%..-1.72%
p25_ret_net_top=-5.59%..-4.97%
month_win_rate_min=14.03%..23.21%
period_fail_count=18..22 of 27
```

Best reference rows are still rejected:

```text
param_id=51 / row_code=06_BROAD_SAMPLE_NEAR_BREAKOUT / avg_ret_net_top=0.180984% / median_ret_net_top=-1.7164% / p25_ret_net_top=-5.4058% / month_win_rate_min=14.0351%
param_id=52 / row_code=07_STABILITY_PROXY_SECTOR_REVIEW / month_win_rate_min=23.2143% / avg_ret_net_top=-0.0694% / median_ret_net_top=-1.8353% / p25_ret_net_top=-5.5662%
```

## Final C02 verdict

```text
C02_IMPLEMENTATION_PASS
C02_OPERATOR_VALIDATION_PASS
C02_IS_EXECUTION_PASS
C02_IS_QUALITY_FAIL
C02_REJECTED_AS_STRATEGY_CATALOG
OOS_NOT_RUN
NOT_PRODUCTION_READY
C03_REQUIRED
```

C02 must remain immutable rejected evidence. Do not patch C02 to force a pass. Do not run OOS for C02. The next catalog must be C03 or a new-focus C01, derived from C02 forensic metrics.

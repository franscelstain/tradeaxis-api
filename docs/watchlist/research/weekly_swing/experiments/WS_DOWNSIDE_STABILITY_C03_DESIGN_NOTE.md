# WS Downside Stability C03 Design Note

Status: OPERATOR_VALIDATED / IS_QUALITY_FAILED / REJECTED_AS_STRATEGY_CATALOG
Runtime status: OPERATOR_VALIDATED
OOS status: NOT_RUN
Production readiness: false
Last updated: 2026-06-11

## 1. Purpose

C03 is a new Watchlist backtest parameter catalog created after C02 failed IS quality. It is not a patch to C02, does not modify any C02 catalog row or hash, and does not authorize OOS.

C03 exists to test whether a more selective weekly swing candidate filter can produce at least one valid IS candidate before any OOS work is allowed.

## 2. Catalog identity

| Field | Value |
| --- | --- |
| catalog_code | `WS_BT_GRID_DOWNSIDE_STABILITY_C03_2026_06` |
| catalog_version | `C03` |
| catalog_count | `10` |
| catalog_hash | `29e15ceab1b3f7dc31a21f339ac6ab7483e14800` |
| artifact_version | `WATCHLIST_C03_IS_CALIBRATION_V1` |
| calibration scope | `WEEKLY_SWING_DOWNSIDE_STABILITY_C03_IS_ONLY` |

## 3. Evidence base

C03 is derived from repository evidence and operator evidence available to this session:

1. `docs/watchlist/findings/weekly_swing/records/WS_C02_OPERATOR_FORENSIC_FINAL_RESULT.md`
2. `docs/watchlist/evidence/weekly_swing/artifacts/c02-forensic-summary.csv`
3. C02 operator validation command/result notes.
4. C01 diagnostic payload expansion and runtime feature-bucket evidence already recorded in the audit docs.
5. Existing weekly swing parameter registry and C01/C02 catalog implementation patterns.

No OOS evidence is used. No sector filter is added because the current runtime catalog axis/filter contract does not expose a supported sector filter.

## 4. C02 failure facts carried into C03

C02 had enough coverage and enough trade samples, but failed quality:

- all 8 C02 parameter rows failed IS quality;
- `WS_BT_EVAL_DOWNSIDE_FAIL = 8`;
- `WS_BT_EVAL_ROBUST_RETURN_FAIL = 8`;
- `WS_BT_EVAL_STABILITY_FAIL = 8`;
- `valid_count = 0`;
- `failed_count = 8`;
- median return was negative for all C02 rows;
- p25 downside was roughly -4.96% to -5.59%, worse than the -3% threshold;
- minimum monthly win rate was roughly 14.03% to 23.21%, far below the 45% threshold;
- picks_count was roughly 1360 to 1435, proving the problem was not sample shortage but weak/loose candidate selection.

C02 remains rejected as a strategy-quality catalog.

## 5. Design response

C03 does not lower IS quality gates to make weak rows pass. It changes the candidate-selection axes using only existing supported parameter dimensions.

Design changes:

1. reduce weak trades by raising `top_min_score_q` and lowering `secondary_min_score_q` only as a fallback candidate pool;
2. reduce high-volatility/downside exposure through lower `atr_pct_max` and tighter risk weighting;
3. reduce chase entries through lower `bo_max_ext_pct`;
4. keep volume participation moderate rather than rewarding broad noisy participation;
5. reduce momentum/extension overweighting where C02 produced too many weak trades;
6. keep fixed exit/grouping axes unchanged from C02 for clean comparison:
   - `stop_atr_mult = 1.50`;
   - `min_rr = 1.50`;
   - `top_picks_target = 5`;
   - `secondary_target = 10`.

## 6. Catalog rows

| Row | Purpose |
| --- | --- |
| `00_C02_BEST_AVG_REFERENCE` | Exact reference copy of C02 row `06_BROAD_SAMPLE_NEAR_BREAKOUT`; retained only as drift/reference evidence, not selected as a best-of-failed strategy. |
| `01_HIGH_SCORE_LOW_ATR_MID_ROC` | High score quantile, lower ATR, moderate ROC, lower breakout extension. |
| `02_STABILITY_PROXY_TIGHTENED` | Tightened stability proxy from lower ATR and less chase exposure. |
| `03_DOWNSIDE_P25_LOW_ATR_STRICT_Q` | Downside-focused strict score quantile with low ATR and conservative risk weights. |
| `04_ANTI_CHASE_CLOSE_BREAKOUT` | Anti-chase row with very tight breakout extension and moderate momentum. |
| `05_MODERATE_VOLUME_NO_SPIKE` | Avoids broad noisy picks by using moderate volume and score gates. |
| `06_LIQUIDITY_QUALITY_CORE` | Keeps liquidity participation but with tighter candidate quality. |
| `07_LOW_ATR_STABILITY_CORE` | Low ATR and stability-oriented weights. |
| `08_RISK_BREAKOUT_BALANCED_HIGH_Q` | Balanced breakout quality with high score quantile and lower risk weight. |
| `09_CANDIDATE_REDUCTION_EXTREME_Q` | Most selective candidate-reduction row for testing whether fewer picks can improve quality. |

## 7. Implementation touchpoints

C03 implementation adds or extends:

- `app/Application/Watchlist/Services/WatchlistBacktestC03ParamGridCatalog.php`
- `app/Application/Watchlist/Services/WatchlistBacktestParamGridParamsetFactory.php`
- `app/Application/Watchlist/Services/WatchlistBacktestIsCalibrationExecutionService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestIsCalibrationService.php`
- `app/Infrastructure/Persistence/Watchlist/WatchlistBacktestParamGridRepository.php`
- `app/Console/Commands/Watchlist/SeedBacktestC03ParamGridCommand.php`
- `database/seeders/Watchlist/WatchlistBacktestC03ParamGridSeeder.php`
- `app/Console/Kernel.php`
- C03 unit/static guard tests under `tests/Unit/Watchlist`.

## 8. Immutability requirements

C03 seed/calibration must verify that these prior catalogs are unchanged:

- R1 `WS_BT_GRID_BOOTSTRAP_2026_06` count/hash;
- R2 `WS_BT_GRID_DOWNSIDE_STABILITY_R2_2026_06` count/hash;
- C01 `WS_BT_GRID_DOWNSIDE_STABILITY_C01_2026_06` count/hash;
- C02 `WS_BT_GRID_DOWNSIDE_STABILITY_C02_2026_06` count/hash.

Any mutation to prior catalogs is a failure. C02 rejected status must remain documented.

## 9. Local authoring validation

Authoring environment validation completed:

| Validation | Status | Evidence |
| --- | --- | --- |
| PHP lint for C03 changed/added PHP files | PASS | `php -l` returned no syntax errors. |
| Pure PHP C03 catalog/factory smoke | PASS | catalog count `10`, hash `29e15ceab1b3f7dc31a21f339ac6ab7483e14800`, factory rows `10`. |
| PHPUnit C03 filter | BLOCKED | Local PHP lacks required PHPUnit extensions: `dom`, `mbstring`, `xml`, `xmlwriter`. |
| Artisan seed/calibration | BLOCKED | Local PHP is `8.4.16`; project guard requires PHP `< 8.4`. |
| C03 seed | NOT_RUN | Requires supported operator runtime. |
| C03 IS calibration | NOT_RUN | Requires supported operator runtime and seeded catalog. |
| OOS | NOT_RUN | Explicitly forbidden in this session. |

## 10. Operator validation final result

The operator executed C03 validation in the supported project runtime.

| Validation | Status | Evidence |
| --- | --- | --- |
| PHPUnit C03 filter | PASS | `OK (12 tests, 461 assertions)` |
| Full Watchlist PHPUnit | PASS | `OK (250 tests, 4643 assertions)` |
| C03 seed | PASS | `inserted_count=10`, `updated_count=0`, `existing_count=0` |
| Prior catalog immutability | PASS | `r1_immutable=1`, `r2_immutable=1`, `c01_immutable=1`, `c02_immutable=1` |
| C03 IS run 1 | QUALITY_FAILED | `C03_GRID_FAILED_IS_QUALITY`, `is_valid_param_count=0`, `is_failed_param_count=10` |
| C03 IS run 2 | QUALITY_FAILED | same status and same artifact hash as run 1 |
| Determinism | PASS | artifact hash `649e8fead0c57262307f749a4776f053f5ccd0f8` in both runs |
| OOS guards | PASS | service/repository not invoked, OOS table unchanged |
| Production readiness | false | `production_ready=0` |

## 11. Decision state

Current C03 decision: `C03_REJECTED_AS_STRATEGY_QUALITY_CATALOG`.

C03 is not eligible for OOS because:

```text
is_valid_param_count=0
param_id_best_is=
best_is_binding_hash=
```

OOS remains `NOT_RUN`, and production readiness remains false.

## 12. C04 input

C03 proves that the C02-derived stricter grid did not solve IS quality. C04 must be a new catalog identity and must change candidate-selection axis/logic, not merely tighten or loosen C03 values.

C04 design input is recorded in:

```text
docs/watchlist/research/weekly_swing/experiments/WS_DOWNSIDE_STABILITY_C04_DESIGN_INPUT_NOTE.md
```

# WS C19 Tahap 5 — Quality Recovery Tuning Diagnostic

Status: `IMPLEMENTED_SOURCE_LEVEL_DIAGNOSTIC_ONLY`

## Purpose

C19 Tahap 4 solved the evaluated-sample recovery problem but exposed a return-quality problem: evaluated samples reached the canonical target while average/median returns and monthly stability remained negative. Tahap 5 is therefore not a catalog step. It is an IS-only diagnostic for comparing quality recovery profiles that use only selector-time inputs before price evaluation.

## Non-goals

- Do not create a C19 catalog.
- Do not seed or promote a param grid.
- Do not run OOS.
- Do not set production readiness.
- Do not use price outcome to choose candidates before price evaluation.
- Do not blacklist ticker, month, or sector.

## Command

Default Tahap 5 is now a fast diagnostic. It runs only the baseline and Q05 downside-aware profile unless profile scope is explicitly provided. This avoids the earlier brute-force full run that did not finish and produced no artifact.

Fast smoke run:

```powershell
php artisan watchlist:backtest-c19-quality-recovery-diagnose `
  --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --param-ids=148 `
  --profile-codes=Q00_TAHAP_4_BASELINE,Q05_DOWNSIDE_AWARE_SCORE_120 `
  --progress `
  --output=storage/app/watchlist/backtest/c19-quality-recovery-tuning-diagnostic-smoke-148.json `
  --overwrite
```

Focused rows after smoke PASS:

```powershell
php artisan watchlist:backtest-c19-quality-recovery-diagnose `
  --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --param-ids=148,149,150,152 `
  --output=storage/app/watchlist/backtest/c19-quality-recovery-tuning-diagnostic-focused.json `
  --overwrite
```

Optional profile subset:

```powershell
php artisan watchlist:backtest-c19-quality-recovery-diagnose `
  --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --profiles=Q00_TAHAP_4_BASELINE,Q05_DOWNSIDE_AWARE_SCORE_120,Q06_MONTHLY_QUALITY_CAP_120 `
  --output=storage/app/watchlist/backtest/c19-quality-recovery-tuning-diagnostic-q05-q06.json `
  --overwrite
```

## Profiles

- `Q00_TAHAP_4_BASELINE`: Tahap 4 baseline, no additional filter.
- `Q01_STRICT_ENTRY_QUALITY`: rejects candidates that still carry C17 entry-quality floor failure and low quality score.
- `Q02_NO_SCORE_OVEREXTENSION_RECOVERY`: rejects score-overextension recovery candidates and high score-chase zone.
- `Q03_PULLBACK_ROC_DISCIPLINE`: rejects ROC5 pullback miss and ROC20 segment miss.
- `Q04_LOW_ATR_NEG_ROC20_PRIORITY`: prioritizes low ATR and non-chasing ROC20 candidates.
- `Q05_DOWNSIDE_AWARE_SCORE_120`: globally reranks quality candidates and caps around 120-125.
- `Q06_MONTHLY_QUALITY_CAP_120`: avoids forcing five weak picks per month; selects best monthly candidates first, then fills globally to target.

Full all-profile execution is intentionally explicit:

```powershell
php artisan watchlist:backtest-c19-quality-recovery-diagnose `
  --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --all-profiles `
  --progress `
  --output=storage/app/watchlist/backtest/c19-quality-recovery-tuning-diagnostic-all-profiles.json `
  --overwrite
```

All profiles use only selector-time candidate fields, C17 extension failure reasons, quality score, penalty totals, score components, and EOD metrics. They do not use price outcome for selection.

## Artifact decision rule

Tahap 5 can only produce one of two decisions:

- `CONTINUE_TO_REPEAT_IS_PROOF_WITH_BEST_PROFILE`
- `DO_NOT_CREATE_CATALOG_CONTINUE_QUALITY_REDESIGN`

Even if a quality profile improves results, catalog creation remains deferred until a separate repeat IS proof is provided.

## Quality gate

Minimum diagnostic target:

```text
sample_target = 120
avg_ret_net_top >= 0
median_ret_net_top >= 0
win_rate_top >= 45%
```

The diagnostic also records p25, period fail count, stop/target counts, price-missing counts, and deltas versus the Tahap 4 baseline.

## Current validation boundary

Source-level implementation has been added. Runtime proof is operator-validation required:

```text
PHPUNIT_C19_FILTER=OPERATOR_VALIDATION_REQUIRED
FULL_WATCHLIST_PHPUNIT=OPERATOR_VALIDATION_REQUIRED
C19_TAHAP_5_FAST_RUNTIME_DIAGNOSTIC=OPERATOR_VALIDATION_REQUIRED
C19_TAHAP_5_FULL_BRUTE_FORCE_AVOIDED=true
OOS_NOT_RUN=true
production_ready=0
C19_CATALOG_CODE=NOT_CREATED
```

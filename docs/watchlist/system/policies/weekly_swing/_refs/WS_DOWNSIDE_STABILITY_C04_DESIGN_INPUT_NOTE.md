# WS Downside Stability C04 Design Input Note

Status: DESIGN_INPUT_ONLY
Implementation status: NOT_STARTED
OOS status: NOT_RUN
Production readiness: false
Last updated: 2026-06-11

## 1. Purpose

C04 is required because C03 was implemented, seeded, validated, and calibrated, but failed IS quality deterministically.

This note is not a C04 implementation. It defines the minimum design constraints for the next same-focus catalog.

## 2. Evidence carried forward

C02 final evidence:

- C02 had enough coverage and enough trade samples;
- all 8 C02 rows failed IS quality;
- failures included downside, robust return, and stability;
- median returns were negative;
- p25 downside was worse than threshold;
- monthly stability was weak;
- C02 was rejected as strategy-quality catalog.

C03 final evidence:

- C03 catalog `WS_BT_GRID_DOWNSIDE_STABILITY_C03_2026_06` was implemented as version `C03`, count `10`, hash `29e15ceab1b3f7dc31a21f339ac6ab7483e14800`;
- PHPUnit C03 and full Watchlist PHPUnit passed in operator runtime;
- C03 seed passed with R1/R2/C01/C02 immutability preserved;
- C03 IS calibration run 1 and run 2 were deterministic with artifact hash `649e8fead0c57262307f749a4776f053f5ccd0f8`;
- C03 failed quality: `C03_GRID_FAILED_IS_QUALITY` / `WS_BT_C03_NO_VALID_IS_CANDIDATE`;
- `is_valid_param_count=0`, `is_failed_param_count=10`;
- aggregate failure family remained downside, robust return, and stability;
- OOS was not run.

## 3. Design interpretation

C03 proves that a stricter catalog based on existing C02-style axes was insufficient. C04 must not be a superficial numeric tightening of C03.

The likely design problem is earlier in candidate selection: the runtime is still admitting trades whose median return, downside profile, or month-to-month stability is structurally poor.

## 4. C04 hard constraints

C04 must:

- use a new catalog code and version, e.g. `WS_BT_GRID_DOWNSIDE_STABILITY_C04_2026_06` / `C04`;
- preserve R1/R2/C01/C02/C03 immutability;
- keep C02 and C03 rejected as strategy-quality catalogs;
- keep canonical quality gates unchanged;
- avoid best-of-failed selection;
- avoid OOS until a valid IS binding exists;
- avoid unsupported `sector_code` or `sector_filter` axes;
- use only runtime-supported fields or implement new field support with tests and docs before consuming it.

## 5. C04 design direction

C04 should investigate candidate-selection axis changes that can reduce weak/reversal-trap picks before metric evaluation.

Allowed direction only if supported by runtime data/service contracts:

- stronger trend confirmation before breakout selection;
- entry timing confirmation instead of near-breakout proximity alone;
- anti-overextension that distinguishes constructive breakout from chase entry;
- volatility regime control beyond broad ATR max/min;
- liquidity quality that avoids noisy high-volume traps;
- prior-strength confirmation that avoids weak rebound candidates;
- risk-first ranking that penalizes downside profile earlier in the candidate score.

Unsupported direction:

- sector filters without a real runtime sector axis;
- lowering IS quality gates;
- selecting best failed param as a strategy candidate;
- running OOS without a valid IS binding.

## 6. C04 validation target

C04 IS success requires at least:

```text
is_valid_param_count >= 1
param_id_best_is=<non-empty>
best_is_binding_hash=<non-empty>
strict_is_boundary_all_evaluations=1
oos_service_invoked=0
oos_repository_invoked=0
oos_table_unchanged=1
oos_executed=0
production_ready=0
```

If C04 fails IS quality, OOS must remain `NOT_RUN` and C04 must be rejected as a strategy-quality catalog.

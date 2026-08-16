# WS Downside Stability C16 Design Note

## Purpose

C16 defines a new immutable weekly swing watchlist backtest catalog for controlled pullback quality recovery after C15 failed IS quality gates.

This note is a reference note. Owner behavior remains in the weekly swing policy documents.

## C16 catalog

```text
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C16_2026_06
catalog_version=C16
catalog_count=12
catalog_hash=0ad1289f79d78787cdca275f0b3f3e2ba90bf8f2
runtime_extension_mode=C16_CONTROLLED_PULLBACK_SCORE_WINDOW_VOLUME_QUALITY_RECOVERY
production_ready=0
```

## C15 evidence boundary

C15 remains rejected as a strategy-quality catalog:

```text
C15_IS_CALIBRATION_STATUS=C15_GRID_FAILED_IS_QUALITY
reason_code=WS_BT_C15_NO_VALID_IS_CANDIDATE
is_valid_param_count=0
artifact_hash=1b96a2c38c0aacced72e441bb8d0ecaff045eabf
OOS_NOT_RUN
production_ready=0
```

C16 may use C15 diagnostics as design evidence, but must not promote any failed C15 row as binding.

## Design commitments

C16 keeps these boundaries:

```text
watchlist_only=true
recommendation_from_PLAN_only=true
recommendation_can_exist_without_confirm=true
confirm_eligibility_from_candidate_PLAN=true
non_recommended_candidate_can_confirm=true
confirm_does_not_mutate_recommendation=true
recommended_plus_confirmed_means_confirm_strengthens_only=true
OOS_NOT_RUN=true
production_ready=0
```

## Quality recovery strategy

C16 prioritizes quality-preserving sample recovery:

- primary liquidity band remains DV20 `2.5B..5B`;
- controlled recovery rows may extend DV20 modestly only when other quality guards remain tight;
- volume ratio focuses on `1.5..2.0`, with limited guarded tests above `2.0`;
- low volume `1.0..1.5` is not opened freely;
- ROC5 stays in controlled pullback `-0.020..0.000`;
- ROC20 is split into cooling and low-continuation bands;
- absolute score window `0.700000..0.799999` is enforced by runtime guard;
- score `0.8..0.9` is rejected as overextension for this C16 concept;
- no ticker/month blacklist is allowed.

## Runtime path

C16 must be consumed by:

```text
WatchlistBacktestC16ParamGridCatalog
WatchlistBacktestParamGridParamsetFactory
WatchlistCandidateUniverseService
WatchlistScoringService
WatchlistPlanGroupingService
WatchlistBacktestIsCalibrationExecutionService
WatchlistBacktestIsCalibrationService
SeedBacktestC16ParamGridCommand
```

## Final validation boundary

C16 operator validation has been completed for source/runtime/IS-only calibration:

```text
WatchlistBacktestC16: OK (12 tests, 553 assertions)
Full Watchlist: OK (355 tests, 8377 assertions)
Seed C16: PASS
Diagnose batch C16: PASS, diagnostic_param_count=12, ready_count=12, blocked_count=0
IS calibration run 1: C16_GRID_FAILED_IS_QUALITY, artifact_hash=63698d0c809a1f2124d8218273ba4d34d9c78deb
IS calibration run 2: C16_GRID_FAILED_IS_QUALITY, artifact_hash=63698d0c809a1f2124d8218273ba4d34d9c78deb
```

C16 final result:

```text
reason_code=WS_BT_C16_NO_VALID_IS_CANDIDATE
is_valid_param_count=0
is_failed_param_count=12
main_blockers=WS_BT_EVAL_MIN_TRADES_FAIL,WS_BT_EVAL_STABILITY_FAIL
OOS_NOT_RUN=true
production_ready=0
```

C16 is runtime-validated but rejected as a strategy-quality catalog. It must not be mutated, promoted, OOS-tested, or marked production-ready. Future work should create a new immutable C17 catalog for quality-preserving sample recovery.

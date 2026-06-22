# WS C61 Signal Quality Rebuild For Weak Regime IS-Only

## Session Code

`C61_SIGNAL_QUALITY_REBUILD_FOR_WEAK_REGIME_IS_ONLY`

## Final Status

`DONE_OPERATOR_VALIDATED_NOT_PRODUCTION_READY`

C61 is closed as an operator-validated IS-only success. It found three candidates ready only for C62/pre-lock IS review. It does not unlock OOS proof, pre-OOS, production, or PLAN/CONFIRM mutation.

## Scope

C61 is strictly IS-only and starts from locked C60 evidence.

IS window:

- `2023-01-02` to `2025-05-21`

Reserved OOS window:

- `2025-05-22` to `2026-05-29`

C61 did not run OOS proof, did not read OOS rows, did not create a production catalog, did not mutate PLAN/CONFIRM, and did not claim production readiness.

## Locked Input

C61 starts from:

- `storage/app/watchlist/backtest/c60-regime-stress-and-loo-dependency-redesign-is-only.json`
- expected C60 artifact hash: `25a32ee9c4cb77ecc29103c86a1abf0826aea705`
- expected C60 file SHA1: `1FA933157B61ECB4554CE6C76B0F2B314F19DB0F`

Final runtime validation:

```text
expected_c60_hash=25a32ee9c4cb77ecc29103c86a1abf0826aea705
actual_c60_hash=25a32ee9c4cb77ecc29103c86a1abf0826aea705
c60_hash_match=1
expected_c60_file_sha1=1FA933157B61ECB4554CE6C76B0F2B314F19DB0F
actual_c60_file_sha1=1FA933157B61ECB4554CE6C76B0F2B314F19DB0F
c60_file_sha1_match=1
```

Runtime must stop if either lock fails:

- `C61_BLOCKED_C60_ARTIFACT_LOCK_MISMATCH`
- `C61_BLOCKED_C60_FILE_SHA1_LOCK_MISMATCH`

## Database Dictionary Rule

Before DB-connected code is changed or audited, C61 requires these dictionary files to be present/readable:

- `docs/market_data/db/MARKET_DATA_DICTIONARY.md`
- `docs/db/DATABASE_DICTIONARY_USAGE_RULE.md`
- `docs/market_data/db/Database_Schema_MariaDB.sql`
- `docs/market_data/db/Database_Schema_Contracts_MariaDB.md`
- `docs/market_data/db/DB_FIELDS_AND_METADATA.md`
- `docs/watchlist/system/db/WATCHLIST_DB_DICTIONARY.md`

C61 records:

- `dictionary_read_required=true`
- `asof_safe=true`
- `future_lookup_detected=false`
- `oos_rows_requested=0`

Market-index mapping remains dictionary-locked:

- `market_benchmark_indicators.roc_20` maps to `market_index_roc20`
- `market_benchmark_indicators.ma20_slope_pct` maps to `market_index_ma20_slope_pct`
- `benchmark_code='IHSG'`
- `market_calendar.cal_date` is the calendar date key

## C57/C58/C59/C60 Carry Forward

C57 market-index/regime reconstruction remains solved and was not repeated in C61.

C58/C59/C60 structural improvements were retained as prerequisites:

- C60 concentration validation pass count: `10`
- C60 regime-aware concentration pass count: `10`
- C60 loss-cluster validation pass count: `10`
- C60 LOO validation pass count: `7`
- C60 rolling validation pass count: `4`
- C60 weak-regime sample recovery pass count: `9`

C60 blocker before C61:

- weak-regime survival pass count: `0`
- regime robustness pass count: `0`
- weakest regime: `market_down_or_sideways_high_vol`
- candidate ready for C61/pre-lock review: `0`

C61 focused on signal quality for `market_down_or_sideways_high_vol`, not another concentration/loss-cluster-only redesign.

## Implemented Files

- `app/Application/Watchlist/Services/WatchlistBacktestC61SignalQualityRebuildForWeakRegimeIsOnlyService.php`
- `app/Console/Commands/Watchlist/RunBacktestC61SignalQualityRebuildForWeakRegimeIsOnlyCommand.php`
- `tests/Unit/Watchlist/WatchlistBacktestC61SignalQualityRebuildForWeakRegimeIsOnlyServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestC61StaticGuardTest.php`
- `docs/watchlist/audit/WS_C61_OPERATOR_VALIDATION_COMMANDS.md`

`app/Console/Kernel.php` registers the C61 command.

## Candidate Tracks

C61 created controlled candidates from C60 parents:

- replay comparator from C60 B01, non-promotable
- Track A: weak-regime signal-quality rebuild
- Track B: market/sector confirmation for weak regime
- Track C: weak-regime risk-quality proxy
- Track D: weak-regime entry timing quality
- Track E: hybrid C60-improvement retention

Replay comparators are never promotable.

## Final Operator Validation Evidence

PHPUnit C61:

```text
PHPUnit 9.6.34
OK (15 tests, 206 assertions)
```

Full Watchlist PHPUnit:

```text
PHPUnit 9.6.34
OK (878 tests, 17872 assertions)
```

Runtime command completed:

```text
status=C61_SIGNAL_QUALITY_REBUILD_FOR_WEAK_REGIME_IS_ONLY_COMPLETED
reason_code=C61_WEAK_REGIME_SIGNAL_QUALITY_REBUILD_FOUND_C62_REVIEW_CANDIDATE
artifact_path=storage/app/watchlist/backtest/c61-signal-quality-rebuild-for-weak-regime-is-only.json
artifact_hash=40d2c4a4f9f1310f9165cdfb4abdd45ff94cb0c8
production_ready=0
c60_hash_match=1
c60_file_sha1_match=1
c60_status=C60_REGIME_STRESS_AND_LOO_DEPENDENCY_REDESIGN_IS_ONLY_COMPLETED
c60_reason_code=C60_WEAK_REGIME_RETURN_SURVIVAL_GAP_REMAINS
next_step_recommendation=C62_PRE_LOCK_REVIEW_FOR_C61_SIGNAL_QUALITY_CANDIDATES_IS_ONLY
c62_candidate_ready_for_c62_count=3
c62_direct_oos_proof_recommended=0
c62_oos_proof_unlocked=0
c62_production_ready=0
```

Final artifact SHA1:

```text
SHA1=DEA3C807813DE81DB6776AB2C441C945D4E98EC6
```

## C62/Pre-Lock Review Candidates

C61 produced three candidates ready for C62/pre-lock IS review only:

1. `C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE`
2. `C61_A01_B01_WEAK_REGIME_QUALITY_FIRST`
3. `C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION`

These are not production candidates and do not unlock OOS proof.

## Primary Candidate Ranking

### 1. Primary candidate

`C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE`

Parent:

`C60_B01_H01_PER_REGIME_BRANCH_BUCKET_QUOTA`

Track:

`Track E - Hybrid C60 improvement retention`

Evidence:

```text
evaluated_picks_count=80
avg_ret_net=0.0024192667485595848
median_ret_net=0.0060736049509387486
win_rate=0.5572650952205372
month_win_rate_min=0
max_branch_share=0.43
max_bucket_share=0.44
max_sector_share=0.145
max_ticker_share=0.075
max_month_share=0.069
loss_cluster_share=0.079
weak_regime_pick_count=28
weak_regime_avg_ret_net=0.0017212795439995802
weak_regime_median_ret_net=0.002413136314079545
weak_regime_win_rate=0.5692650952205373
weak_regime_month_coverage=14
weak_regime_signal_quality_pass=true
weak_regime_survival_pass=true
rolling_validation_pass=true
loo_validation_pass=true
regime_robustness_validation_pass=true
regime_aware_concentration_pass=true
concentration_validation_pass=true
loss_cluster_validation_pass=true
sample_recovery_pass=true
weak_regime_sample_recovery_pass=true
material_selection_difference_pass=true
anti_shared_core_pass=true
overall_is_redesign_pass=true
candidate_ready_for_c62=true
failure_reason_codes={}
```

This is the strongest C61 candidate by overall avg return, overall median return, overall win rate, weak-regime avg return, weak-regime median return, and weak-regime win rate.

### 2. Backup candidate

`C61_A01_B01_WEAK_REGIME_QUALITY_FIRST`

Parent:

`C60_B01_H01_PER_REGIME_BRANCH_BUCKET_QUOTA`

Track:

`Track A - Weak-regime signal-quality rebuild`

Evidence:

```text
evaluated_picks_count=80
avg_ret_net=0.002272600081892918
median_ret_net=0.005953604950938749
win_rate=0.5502650952205372
month_win_rate_min=0
loss_cluster_share=0.079
weak_regime_pick_count=27
weak_regime_avg_ret_net=0.00128127954399958
weak_regime_median_ret_net=0.002173136314079545
weak_regime_win_rate=0.5552650952205372
weak_regime_month_coverage=14
all_core_validation_gates=true
candidate_ready_for_c62=true
failure_reason_codes={}
```

This is the cleanest Track A comparator because it isolates weak-regime signal-quality rebuild more directly than the hybrid candidate.

### 3. Diversification comparator

`C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION`

Parent:

`C60_A02_C02_DEFENSIVE_GATE_WEAK_REGIME_SURVIVAL`

Track:

`Track B - Market/sector confirmation for weak regime`

Evidence:

```text
evaluated_picks_count=80
avg_ret_net=0.001964504958573553
median_ret_net=0.005841473569527805
win_rate=0.5354874418604652
month_win_rate_min=0
max_branch_share=0.44
max_bucket_share=0.44
max_sector_share=0.145
max_ticker_share=0.075
max_month_share=0.07
loss_cluster_share=0.079
weak_regime_pick_count=27
weak_regime_avg_ret_net=0.001216638572845102
weak_regime_median_ret_net=0.002117325316364164
weak_regime_win_rate=0.5544874418604652
weak_regime_month_coverage=14
all_core_validation_gates=true
candidate_ready_for_c62=true
failure_reason_codes={}
```

This candidate remains useful because it comes from a different C60 parent and a different lineage track.

## Weak-Regime Stress Evidence

All three ready candidates keep the weakest regime explicit as `market_down_or_sideways_high_vol` and pass weak-regime survival without deleting that regime.

```text
C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE:
  weak_regime_pick_count=28
  weak_regime_month_coverage=14
  weak_regime_branch_count=4
  weak_regime_bucket_count=4
  weak_regime_ticker_count=21
  weak_regime_avg_ret_net=0.0017212795439995802
  weak_regime_median_ret_net=0.002413136314079545
  weak_regime_win_rate=0.5692650952205373
  weak_regime_survival_pass=true
  weak_regime_improved_vs_c60=true
  weak_regime_improved_vs_c59=true
  weak_regime_improved_vs_c58=true

C61_A01_B01_WEAK_REGIME_QUALITY_FIRST:
  weak_regime_pick_count=27
  weak_regime_month_coverage=14
  weak_regime_branch_count=4
  weak_regime_bucket_count=4
  weak_regime_ticker_count=21
  weak_regime_avg_ret_net=0.00128127954399958
  weak_regime_median_ret_net=0.002173136314079545
  weak_regime_win_rate=0.5552650952205372
  weak_regime_survival_pass=true
  weak_regime_improved_vs_c60=true
  weak_regime_improved_vs_c59=true
  weak_regime_improved_vs_c58=true

C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION:
  weak_regime_pick_count=27
  weak_regime_month_coverage=14
  weak_regime_branch_count=4
  weak_regime_bucket_count=4
  weak_regime_ticker_count=22
  weak_regime_avg_ret_net=0.001216638572845102
  weak_regime_median_ret_net=0.002117325316364164
  weak_regime_win_rate=0.5544874418604652
  weak_regime_survival_pass=true
  weak_regime_improved_vs_c60=true
  weak_regime_improved_vs_c59=true
  weak_regime_improved_vs_c58=true
```

## Signal-Quality Safety Evidence

All three ready candidates passed weak-regime signal-quality checks:

```text
weak_regime_quality_floor_pass=true
weak_regime_market_confirmation_pass=true
weak_regime_sector_confirmation_pass=true
weak_regime_relative_strength_pass=true
weak_regime_volatility_risk_pass=true
weak_regime_liquidity_pass=true
weak_regime_entry_timing_quality_pass=true
weak_regime_signal_quality_pass=true
return_fields_used_for_selection=false
future_path_used_for_selection=false
oos_return_used_for_selection=false
failure_reason_codes={}
```

Quality rank coverage:

```text
C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE=1.0
C61_A01_B01_WEAK_REGIME_QUALITY_FIRST=0.8800000000000001
C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION=0.97
```

## Concentration And Loss-Cluster Retention Evidence

All three ready candidates retained C60 concentration and loss-cluster improvements.

Concentration:

```text
max_ticker_share=0.075
max_sector_share=0.145
max_bucket_share=0.44
max_branch_share=0.43..0.44
weak_regime_max_ticker_share=0.075
weak_regime_max_sector_share=0.145
weak_regime_max_bucket_share=0.44
weak_regime_max_branch_share=0.43..0.44
unique_ticker_count=76..77
unique_sector_count=7
unique_bucket_count=4
unique_branch_count=4
weak_regime_unique_ticker_count=21..22
weak_regime_unique_sector_count=6..7
weak_regime_unique_bucket_count=4
weak_regime_unique_branch_count=4
concentration_validation_pass=true
regime_aware_concentration_pass=true
improved_or_retained_vs_c60=true
failure_reason_codes={}
```

Loss cluster:

```text
loss_cluster_share=0.079
loss_cluster_count=3
loss_cluster_trade_count=7
loss_cluster_month_count=6
loss_cluster_branch_count=4
loss_cluster_bucket_count=4
loss_cluster_ticker_count=5
loss_cluster_pre_trade_guard_pass=true
loss_cluster_validation_pass=true
loss_cluster_improved_or_retained_vs_c60=true
failure_reason_codes={}
```

## Remaining C62 Audit Note

All three candidates have:

```text
month_win_rate_min=0
```

This is not a C61 acceptance blocker because the candidates passed C61's strict IS-only gate set, including rolling, LOO, regime robustness, concentration, loss-cluster, sample recovery, material selection difference, and anti-shared-core.

However, C62 must audit whether `month_win_rate_min=0` represents acceptable adverse-month noise or a hidden dependency / bad-month fragility that should block pre-lock selection.

## Diagnostic Conclusion

C61 found candidates that pass the strict IS-only C61 gate set in the operator-validated artifact, while keeping OOS locked.

Diagnostic conclusion:

`C61_WEAK_REGIME_SIGNAL_QUALITY_REBUILD_FOUND_C62_REVIEW_CANDIDATE`

Next governed step:

`C62_PRE_LOCK_REVIEW_FOR_C61_SIGNAL_QUALITY_CANDIDATES_IS_ONLY`

C62 must remain IS/pre-lock review. C61 does not unlock direct OOS proof.

## Safety Closeout

C61 keeps these rules locked:

- no OOS proof
- no OOS rows
- no future lookup
- no return/future path used for selection
- no production catalog
- no PLAN/CONFIRM mutation
- no gate relaxation
- no bad-month deletion
- no weak-regime removal
- no hard ticker/sector exclusion from failure attribution
- no replay comparator promotion
- database dictionary read rule mandatory

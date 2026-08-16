# Weekly Swing New Strategy R02 Candidate Lock

## Status

```text
SCOPE=WS_NEW_STRATEGY_R02
SOURCE_R01_ARTIFACT_HASH=a38e59f6d1422b7823a428ca4f6b724a3fa1a0e7
CATALOG_CODE=WS_BT_GRID_NEW_STRATEGY_R02_2026_07
CATALOG_COUNT=3
ONE_PRIMARY_IDEA_PER_CANDIDATE=1
CANONICAL_GATES_CHANGED=0
OOS_USED=0
```

Threshold berikut dikunci sebelum official IS R02 dijalankan.

## Candidate H1

```text
ROW_CODE=R02_H1_BREAKOUT_QUALITY_0_TO_2
HYPOTHESIS=H1_BREAKOUT_QUALITY_CONFIRMATION
RULE=SIGNAL_CLOSE_TO_HH20_0_TO_2_PCT
MIN_CLOSE_TO_HH20_PCT=0.00
MAX_CLOSE_TO_HH20_PCT=0.02
```

Hanya `close_to_hh20_pct` pada signal date yang menjadi guard tambahan.

## Candidate H2

```text
ROW_CODE=R02_H2_ROC20_PERSISTENCE_10_TO_15
HYPOTHESIS=H2_MOMENTUM_PERSISTENCE
RULE=SIGNAL_ROC20_10_TO_15_PCT
MIN_ROC20=0.10
MAX_ROC20=0.15
```

Hanya `roc20` equity pada signal date yang menjadi guard tambahan.

## Candidate H3

```text
ROW_CODE=R02_H3_IHSG_MIXED_REGIME_ONLY
HYPOTHESIS=H3_MARKET_REGIME_COMPATIBILITY
RULE=SIGNAL_IHSG_MIXED_REGIME_ONLY
BENCHMARK=IHSG
ALLOWED_REGIME=MIXED
```

`MIXED` berarti ROC20 IHSG dan MA20 slope IHSG pada signal date memiliki tanda
yang berlawanan. Benchmark harus tersedia pada exact signal date, memakai
indicator-set version canonical, dan valid. Missing context bersifat fail-closed.

## Immutable guard

Ketiga kandidat:

- memakai window IS `2023-01-02` sampai `2025-05-21`;
- mempertahankan seluruh canonical gate;
- tidak memakai return, entry gap, exit reason, ticker blacklist, atau month
  blacklist sebagai selection input;
- tidak membaca OOS;
- tidak memberi izin promotion, PLAN, CONFIRM, rollout, atau production.

Jika tidak ada kandidat yang lulus seluruh gate, hanya satu remediation yang
boleh dievaluasi setelah hasil R02 dicatat. Threshold remediation harus dikunci
sebelum rerun dan OOS tetap dilarang.

## Single allowed remediation lock

Ketiga kandidat awal selesai Official IS sebagai eval `208`, `209`, dan `210`.
Tidak ada yang lulus seluruh gate. H2 dipilih sebagai basis satu-satunya
remediation karena memiliki `321` trade, average dan median yang sudah positif,
serta kepadatan observasi bulanan yang lebih besar daripada H3. Pemilihan ini
dibuat tanpa membaca OOS.

```text
REMEDIATION_COUNT=1
MAX_REMEDIATION_COUNT=1
SOURCE_ROW=R02_H2_ROC20_PERSISTENCE_10_TO_15
SOURCE_PARAM_SET_ID=16
SOURCE_EVAL_ID=209
SOURCE_OFFICIAL_IS_ARTIFACT_HASH=d4992cb12859fe74ab287139e1023173ad6a2566
REMEDIATION_ROW=R02_M1_H2_SEQUENTIAL_TARGET_0P5_PROFIT_NEXT_OPEN
REMEDIATION_CATALOG=WS_BT_GRID_NEW_STRATEGY_R02_REMEDIATION_2026_07
REMEDIATION_CATALOG_VERSION=R02M1
REMEDIATION_CATALOG_HASH=f78cc1e0bba15cbcd407d7b69d5d54a5a56e45d5
REMEDIATION_PARAM_SET_ID=19
REMEDIATION_BT_PARAM_ID=173
REMEDIATION_PARAMS_HASH=e50a62ac2dbf1f3e9517f8e2d44f072c7d42eb1f
SELECTION_CHANGED_FROM_H2=0
CANONICAL_GATES_CHANGED=0
OOS_USED=0
```

Satu ide remediation adalah profit-capture exit yang urut dan ditetapkan
sebelum entry:

```text
ENTRY=D1_OPEN
PREPLANNED_TARGET=ENTRY_PLUS_0.50_PERCENT_NORMALIZED_IDX_TICK
PROFIT_SIGNAL=D1_D2_OR_D3_CLOSE_RETURN_GT_0
PROFIT_SIGNAL_EXIT=NEXT_TRADING_DAY_OPEN_ONLY
FALLBACK_EXIT=D5_CLOSE
CANONICAL_STOP_USED=0
RAW_TRADABLE_OHLCV_REQUIRED=1
FIXED_BEFORE_ENTRY=1
FUTURE_DERIVED_ROUTE_USED=0
```

Target dicek secara kronologis. Close Dn tidak boleh menghasilkan exit pada
close Dn; exit paling awal adalah open D(n+1). Rule tidak boleh terlebih dahulu
melihat apakah target akan tercapai pada hari yang lebih jauh untuk memilih
antara target, profit-signal exit, atau fallback. Setelah Official IS remediation
ini selesai, tidak ada remediation R02 tambahan yang diizinkan.

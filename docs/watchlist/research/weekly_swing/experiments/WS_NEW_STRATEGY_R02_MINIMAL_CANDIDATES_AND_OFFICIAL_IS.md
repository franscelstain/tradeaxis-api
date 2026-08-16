# WS New Strategy R02 Minimal Candidates and Official IS

## Scope

R02 adalah tahap terpisah setelah R01. R02 tidak membuka kembali C171 dan tidak
memberi izin OOS sebelum satu kandidat lulus seluruh canonical IS gate.

```text
RUN_CODE=WS_NEW_STRATEGY_R02_MINIMAL_ONE_IDEA_DRAFT_CATALOG
SOURCE_EVAL_ID=204
SOURCE_PARAM_SET_ID=11
SOURCE_PARAMS_HASH=c93bae2b761028d6b236f368d5b19bb4f498715a
R01_ARTIFACT_HASH=a38e59f6d1422b7823a428ca4f6b724a3fa1a0e7
CATALOG_CODE=WS_BT_GRID_NEW_STRATEGY_R02_2026_07
CATALOG_VERSION=R02
CATALOG_HASH=09ff6665630396eafa857fefa1647a8a997a52e4
CANDIDATE_COUNT=3
```

## Locked candidates

| Candidate | Hypothesis | Exact signal-date rule | Param set |
|---|---|---|---:|
| `R02_H1_BREAKOUT_QUALITY_0_TO_2` | H1 breakout quality | `close_to_hh20_pct` 0.00..0.02 | 15 |
| `R02_H2_ROC20_PERSISTENCE_10_TO_15` | H2 momentum persistence | `roc20` 0.10..0.15 | 16 |
| `R02_H3_IHSG_MIXED_REGIME_ONLY` | H3 market regime | exact-date IHSG `MIXED` only | 17 |

```text
R02_H1_PARAMS_HASH=2cae122e5eecba0d9c2313c19ab4561c779463ec
R02_H2_PARAMS_HASH=d50497b951107ae8de9f559d3fccf13e7b2182c6
R02_H3_PARAMS_HASH=876839ce2d4816acb1fcc8fd23165ef36cbc3f9e
DRAFT_PARAMSET_CREATED_COUNT=3
OFFICIAL_IS_RUNTIME_INVOKED_DURING_DRAFT_PERSISTENCE=0
OOS_RUNTIME_INVOKED=0
OOS_TABLE_READ=0
PARAMSET_PROMOTED=0
PLAN_RUN_CREATED=0
PRODUCTION_READY=0
```

Canonical candidate lock ada pada
`docs/watchlist/research/weekly_swing/experiments/WS_NEW_STRATEGY_R02_CANDIDATE_LOCK.md`.

## Implementation

- `WatchlistBacktestNewStrategyR02ParamGridCatalog` mengunci tiga row.
- `WeeklySwingParamsetValidator` menerima section `research_selection` opsional
  hanya untuk tiga kontrak R02 exact.
- `WatchlistPlanGroupingService` menerapkan guard H1/H2 pada feature equity
  signal date dan H3 melalui exact-date IHSG.
- Missing benchmark context pada H3 bersifat fail-closed.
- `WeeklySwingNewStrategyR02DraftCatalogService` memverifikasi seal C171 dan
  artifact R01 sebelum persistence.
- `WeeklySwingNewStrategyR02OfficialIsEvidenceService` menjalankan official IS
  dengan strict boundary, official evidence persistence, dan zero-OOS boundary.

## Official IS result

Ketiga runtime memakai window canonical `2023-01-02` sampai `2025-05-21`,
strict IS boundary, dan official evidence persistence.

| Candidate | Eval | Trades | Days | Avg | Median | P25 | Worst month WR | Worst month avg | Failed periods | Result |
|---|---:|---:|---:|---:|---:|---:|---:|---:|---:|---|
| H1 breakout | 208 | 429 | 488 | +2.4035% | -0.0500% | -6.2984% | 0.00% | -4.1632% | 10 | FAIL |
| H2 momentum | 209 | 321 | 489 | +1.1302% | +0.3865% | -5.0075% | 0.00% | -6.8270% | 11 | FAIL |
| H3 IHSG mixed | 210 | 166 | 508 | +2.1045% | +0.9239% | -5.9130% | 0.00% | -5.6882% | 8 | FAIL |

```text
INITIAL_CANDIDATE_COUNT=3
INITIAL_PASSING_CANDIDATE_COUNT=0
INITIAL_OOS_RUNTIME_INVOKED=0
INITIAL_OOS_ROWS_BEFORE=0
INITIAL_OOS_ROWS_AFTER=0
INITIAL_PARAMSET_PROMOTED=0
INITIAL_PRODUCTION_READY=0
```

H2 dipilih sebagai basis remediation tunggal. Alasan predeclared-nya adalah
sample `321`, average dan median yang sudah lulus, serta observasi per bulan yang
lebih padat daripada H3. Tidak ada data OOS yang dibaca.

## Single remediation

```text
REMEDIATION_COUNT=1
MAX_REMEDIATION_COUNT=1
SOURCE_PARAM_SET_ID=16
SOURCE_EVAL_ID=209
REMEDIATION_CATALOG_CODE=WS_BT_GRID_NEW_STRATEGY_R02_REMEDIATION_2026_07
REMEDIATION_CATALOG_VERSION=R02M1
REMEDIATION_CATALOG_HASH=f78cc1e0bba15cbcd407d7b69d5d54a5a56e45d5
REMEDIATION_PARAM_SET_ID=19
REMEDIATION_BT_PARAM_ID=173
REMEDIATION_PARAMS_HASH=e50a62ac2dbf1f3e9517f8e2d44f072c7d42eb1f
SELECTION_CHANGED_FROM_H2=0
CANONICAL_GATES_CHANGED=0
FUTURE_DERIVED_ROUTE_USED=0
```

Exit model remediation adalah satu fixed sequential rule: target +0,5% sejak
entry; jika belum terisi dan close D1-D3 profit, exit pada open hari bursa
berikutnya; jika tidak ada fill, exit close D5. Model ini tidak memakai
lookahead router C28 G05 dan tidak memprioritaskan target yang baru diketahui
tercapai pada masa depan terhadap exit signal yang lebih awal.

Official IS remediation:

| Eval | Trades | Days | Avg | Median | P25 | Win rate | Worst month WR | Worst month avg | Failed periods | Result |
|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|---|
| 211 | 323 | 490 | +0.6232% | +0.7250% | +0.5279% | 86.997% | 50.00% | -4.5072% | 4 | FAIL |

```text
REMEDIATION_OFFICIAL_IS_ARTIFACT_HASH=fbf336b8dc5b2a0e798eceb70075b256f711d4c3
REMEDIATION_OFFICIAL_IS_FILE_SHA1=fc6d8f646d9848086cd7ddec67ee2f7e71f8eece
MINIMUM_TRADE_COUNT=PASS
MINIMUM_COVERAGE=PASS
AVERAGE_RETURN_POSITIVE=PASS
MEDIAN_RETURN_NON_NEGATIVE=PASS
P25_DOWNSIDE_BOUND=PASS
MONTHLY_WIN_RATE_FLOOR=PASS
MONTHLY_AVERAGE_FLOOR=FAIL
CANONICAL_IS_GATES_PASS=0
OOS_RUNTIME_INVOKED=0
OOS_ROWS_BEFORE=0
OOS_ROWS_AFTER=0
PARAMSET_PROMOTED=0
PLAN_RUN_CREATED=0
PRODUCTION_READY=0
R02_CLOSED_NO_MORE_REMEDIATION=1
```

Empat bulan yang gagal average floor `-1%` adalah:

| Month | Trades | Avg | Win rate | Min | Max |
|---|---:|---:|---:|---:|---:|
| 2023-03 | 3 | -2.7528% | 66.67% | -9.4002% | +0.5709% |
| 2024-11 | 12 | -1.1725% | 75.00% | -9.5714% | +2.2222% |
| 2024-12 | 8 | -1.4264% | 75.00% | -11.8118% | +1.9903% |
| 2025-03 | 10 | -4.5072% | 50.00% | -18.7953% | +2.9796% |

Remediation berhasil memperbaiki median, P25, win rate, dan worst-month win-rate,
tetapi tail loss pada empat bulan masih membuat monthly-average gate gagal.
Gate tidak diturunkan dan tidak ada month/ticker blacklist.

## Advancement rule

```text
IF_ANY_R02_CANDIDATE_PASSES_ALL_CANONICAL_IS_GATES:
  NEXT=R03_IS_IDENTITY_REVIEW_BEFORE_OFFICIAL_OOS

IF_NO_R02_CANDIDATE_PASSES_ALL_CANONICAL_IS_GATES:
  NEXT=ONE_ALLOWED_REMEDIATION_REVIEW

MAX_REMEDIATION_ROUNDS=1
OOS_BEFORE_ALL_CANONICAL_IS_GATES_PASS=FORBIDDEN
```

Final R02 decision:

```text
INITIAL_PASSING_CANDIDATE_COUNT=0
REMEDIATION_PASSING_CANDIDATE_COUNT=0
REMEDIATION_ROUNDS_USED=1
REMEDIATION_ROUNDS_REMAINING=0
R02_STATUS=FAILED_NOT_READY_CLOSED
OFFICIAL_OOS_ALLOWED=0
PROMOTION_ALLOWED=0
PLAN_ALLOWED=0
PRODUCTION_READY=0
NEXT=NEW_SEPARATE_PREREGISTERED_STRATEGY_SCOPE_ONLY
```

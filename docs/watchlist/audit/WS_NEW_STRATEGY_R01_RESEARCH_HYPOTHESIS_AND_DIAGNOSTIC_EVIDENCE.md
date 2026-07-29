# WS New Strategy R01 Research Hypothesis and Diagnostic Evidence

## Keputusan kesesuaian

Dua dokumen perencanaan setelah C171 sesuai dengan owner docs
`docs/watchlist/system/policies/weekly_swing/**`.

| Target | Owner contract | Hasil |
|---|---|---|
| median return non-negatif | file 12 dan 16 | sesuai |
| P25 minimal `-0.03` | file 12, 16, dan 17 | sesuai |
| worst-month win rate minimal `0.45` | file 12, 16, dan 17 | sesuai |
| worst-month average minimal `-0.01` | file 12 dan 16 | sesuai |
| trade count minimal `120` | file 16 | sesuai |
| coverage minimal `390` untuk window canonical | file 12 dan 16 | sesuai |
| decision-time only | file 12 dan kontrak no-lookahead | sesuai |
| OOS hanya setelah seluruh IS gate lulus | file 17 dan 20 | sesuai |
| controlled runtime setelah IS/OOS | canonical lifecycle | sesuai |

C171 tetap ditutup. R01 bukan C171 remediation dan bukan C172.

## Scope R01

```text
RUN_CODE=WS_NEW_STRATEGY_R01_RESEARCH_HYPOTHESIS_AND_DIAGNOSTIC_EVIDENCE
SOURCE_EVAL_ID=204
SOURCE_PARAM_SET_ID=11
SOURCE_PARAM_ID=166
SOURCE_PARAMSET_HASH=c93bae2b761028d6b236f368d5b19bb4f498715a
SOURCE_EVIDENCE_MANIFEST_HASH=604bfbe9698fbb8ec3c74e3fa6e10f9335f66d1d
CANONICAL_IS_FROM=2023-01-02
CANONICAL_IS_TO=2025-05-21
MAX_HYPOTHESES=3
```

## Implementasi

R01 menambahkan command read-only:

```text
watchlist:weekly-swing-new-strategy-r01-diagnostic
```

Command:

1. memverifikasi seal penutupan C171;
2. memverifikasi identity database anchor final;
3. menghitung ulang official evidence manifest;
4. membaca 1.308 official picks anchor;
5. mengikat feature equity ke immutable signal-publication indicator history;
6. membaca IHSG pada signal date;
7. mereplay D+1 sampai D+5 melalui current readable published prices;
8. mewajibkan parity `ret_net` enam desimal dan entry publication lineage;
9. menghasilkan segment, winner/loser, monthly/yearly, dan hypothesis evidence;
10. membandingkan boundary count sebelum/sesudah.

## Hipotesis praregistrasi

```text
H1=BREAKOUT_QUALITY_CONFIRMATION
H2=MOMENTUM_PERSISTENCE
H3=MARKET_REGIME_COMPATIBILITY
```

Actual entry gap dan exit result hanya diagnostic evidence. R02 dilarang
memakainya sebagai selection router.

## Boundary

```text
C171_MORE_REMEDIATION_ALLOWED=0
DRAFT_PARAMSET_CREATED=0
OFFICIAL_IS_RUNTIME_INVOKED=0
OOS_RUNTIME_INVOKED=0
OOS_TABLE_READ=0
PARAMSET_PROMOTED=0
PLAN_RUN_CREATED=0
RECOMMENDATION_PERSISTED=0
CONFIRM_MUTATED=0
PRODUCTION_READY=0
```

## Runtime evidence

Runtime evidence diisi dari command resmi dan bukan dari inferensi source:

```text
R01_RUNTIME_STATUS=COMPLETED
R01_RUNTIME_ARTIFACT=storage/app/watchlist/backtest/ws-new-strategy-r01-diagnostic.json
R01_RUNTIME_ARTIFACT_HASH=a38e59f6d1422b7823a428ca4f6b724a3fa1a0e7
R01_RUNTIME_FILE_SHA1=BF76FB76388D6E0C81230B12B1DD4E934BBBE59A
R01_HYPOTHESIS_LOCK_FILE_SHA1=4560BF207AF841641885F863FD2219D0C7C1F6D1
R01_OFFICIAL_PICK_PARITY=PASS_1308_OF_1308
R01_OFFICIAL_PICK_MISMATCH_COUNT=0
R01_EQUITY_SIGNAL_FEATURE_COVERAGE=1.0
R01_BENCHMARK_SIGNAL_FEATURE_COVERAGE=1.0
R01_DATABASE_BOUNDARY_COUNTS_UNCHANGED=1
```

Validation evidence:

```text
focused_r01_phpunit=PASS_3_TESTS_35_ASSERTIONS
c171_regression=PASS_63_TESTS_695_ASSERTIONS
full_watchlist_phpunit=PASS_7137_TESTS_48447_ASSERTIONS
php_syntax=PASS
git_diff_check=PASS
```

Reproduced anchor metrics:

```text
picks_count=1308
observed_trade_days=504
official_days_covered=508
avg_ret_net=0.01190327599388372
median_ret_net=-0.000501
p25_ret_net=-0.063094
win_rate=0.4648318042813456
month_win_rate_min=0.27586206896551724
month_avg_ret_net_min=-0.016505948275862065
worst_month=2023-05
worst_month_avg_ret_net=-0.01650594827586207
worst_month_win_rate=0.27586206896551724
```

Canonical gate snapshot tetap sama:

```text
minimum_trade_count=PASS
minimum_coverage=PASS
average_return_positive=PASS
median_return_non_negative=FAIL
p25_downside_bound=FAIL
monthly_win_rate_floor=FAIL
monthly_average_floor=FAIL
```

## Hypothesis evidence result

Ketiga hipotesis memiliki material decision-time contrast dan dapat maju ke
minimal candidate design, tetapi bukan dengan tingkat keyakinan yang sama.

### H1 — Breakout quality

`BREAKOUT_0_TO_2%` dibanding `FAR_BELOW_LT_-2%`:

```text
median_spread=+0.0061165
avg_spread=+0.020942367149461603
win_rate_spread=+0.05752885897157117
p25_spread=-0.006665000000000004
p25_regression_warning=1
```

H1 didukung untuk target median/win-rate, tetapi P25 memburuk. R02 tidak boleh
menganggap breakout-near-HH20 sebagai downside solution tanpa official IS.

### H2 — Momentum persistence

Primary persistence grouping sendiri belum material. Dukungan H2 datang dari
kontras ROC20 `10_TO_15%` terhadap `5_TO_10%`:

```text
median_spread=+0.017237
p25_spread=+0.005630750000000004
avg_spread=+0.018153127274108394
win_rate_spread=+0.1436059098332637
```

R02 harus menguji satu rule persistence yang praregistered. Bucket result tidak
boleh berubah menjadi return-routed threshold.

### H3 — Market regime compatibility

`MIXED` dibanding `STRONG` pada definisi regime R01:

```text
median_spread=+0.016635
p25_spread=+0.0080205
avg_spread=+0.017879201556669094
win_rate_spread=+0.09649216334364008
```

H3 adalah kontras robust terkuat. Evidence tidak mengatakan bahwa regime
`STRONG` otomatis lebih baik; kandidat harus menguji kompatibilitas/non-
extension berdasarkan signal-date IHSG secara eksplisit.

Output file identity:

```text
trades_csv_sha1=AC8050D546D3EB4FA46E7D8F1B6C5D9F603B56FF
segments_csv_sha1=D010B2741A1F94D703973B8C2484DF7121D54C5D
winner_loser_csv_sha1=36223072570DF4316660BDD0B9CFD217A5C3CBF0
monthly_yearly_csv_sha1=88127CFADFDBA068FCDDFECAE06E2CBB78D180B6
```

## Next-stage guard

R02 hanya boleh membuat kandidat minimal dari hipotesis yang dinyatakan
`SUPPORTED_FOR_MINIMAL_CANDIDATE_DESIGN` oleh artifact R01. Maksimal tiga
kandidat, satu ide utama per kandidat, satu remediation, canonical gate tetap,
dan OOS tetap terkunci sampai satu kandidat lulus seluruh official canonical IS
gate.

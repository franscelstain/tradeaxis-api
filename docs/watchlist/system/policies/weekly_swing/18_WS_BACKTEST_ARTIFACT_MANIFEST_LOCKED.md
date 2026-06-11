# 18 — WS Backtest Artifact Manifest (LOCKED)

## Purpose
Menjadi allowlist resmi untuk artefak backtest, calibration, dan proof yang dianggap **exist**, **valid**, dan **boleh dirujuk** oleh dokumen Weekly Swing.

Dokumen ini mencegah drift akibat penyebutan tabel/file/artefak yang tidak resmi atau tidak lagi dipakai.

## Scope

Dokumen ini adalah allowlist artefak resmi WS; artefak di luar daftar ini dianggap non-scope sampai dinyatakan resmi.

Dokumen ini berlaku untuk:
- artefak tabel backtest resmi,
- artefak proof produksi resmi,
- dan dokumen pengendali yang sah untuk artefak tersebut.

Dokumen ini tidak memasukkan fixture test sebagai artefak produksi.
Dokumen ini juga tidak memutihkan artefak lama yang belum masuk allowlist.

## Inputs
- desain backtest WS,
- kebutuhan calibration/evidence/promote,
- dan dokumen proof lintas sistem.

## Outputs
- daftar artefak resmi yang boleh dirujuk,
- klasifikasi artefak,
- aturan referensi silang,
- aturan penanganan artefak non-scope.

## Prerequisites
- [`17_WS_WALK_FORWARD_OOS_PROOF_LOCKED.md`](17_WS_WALK_FORWARD_OOS_PROOF_LOCKED.md)
- [`19_WS_DEPRECATED_OR_NONSCOPE_ARTIFACTS_LEDGER.md`](19_WS_DEPRECATED_OR_NONSCOPE_ARTIFACTS_LEDGER.md)

## 1) Official WS Backtest Tables (LOCKED)
Tabel berikut adalah artefak backtest resmi Weekly Swing:
1. `watchlist_bt_param_grid`
2. `watchlist_bt_eval`
3. `watchlist_bt_picks_ws`
4. `watchlist_bt_universe_ws`
5. `watchlist_bt_cutoffs_ws`
6. `watchlist_bt_oos_eval_ws`

Aturan:
- nama tabel harus dipakai persis seperti di atas,
- penyebutan alias/deskripsi boleh, tapi nama artefak resminya tidak boleh berubah,
- dan artefak lain tidak boleh dianggap resmi hanya karena “mirip” fungsinya.

## 2) Official Production Proof Artifact (LOCKED)

Shape proof resmi boleh diekspor ke media berbeda, tetapi konsumen tidak boleh mengandalkan nama file ad-hoc sebagai kontrak.

Artefak proof produksi resmi yang dibutuhkan untuk equivalence adalah:
1. export PLAN universe snapshot dengan shape mengikuti:
   - [`db/PLAN_UNIVERSE_SNAPSHOT_SCHEMA.md`](db/PLAN_UNIVERSE_SNAPSHOT_SCHEMA.md)

Catatan:
- ini adalah **shape/contract artefak proof**, bukan nama tabel fisik baku,
- implementasi boleh mengekspor ke file JSON/CSV atau bentuk lain selama shape-nya identik terhadap kontrak schema export resmi.

## 3) Official Supporting Governance Docs (LOCKED)

Dokumen pendukung di daftar ini membantu governance artefak, tetapi tidak otomatis mengubah status artefak fisik menjadi resmi.

Dokumen resmi yang mengendalikan artefak di atas adalah:
- [`12_WS_BACKTEST_SCHEMA_AND_CALIBRATION.md`](12_WS_BACKTEST_SCHEMA_AND_CALIBRATION.md)
- [`14_WS_BT_COVERAGE_MATRIX_LOCKED.md`](14_WS_BT_COVERAGE_MATRIX_LOCKED.md)
- [`15_WS_UNIVERSE_EQUIVALENCE_CONTRACT_LOCKED.md`](15_WS_UNIVERSE_EQUIVALENCE_CONTRACT_LOCKED.md)
- [`16_WS_EVAL_METRICS_SUFFICIENCY_LOCKED.md`](16_WS_EVAL_METRICS_SUFFICIENCY_LOCKED.md)
- [`17_WS_WALK_FORWARD_OOS_PROOF_LOCKED.md`](17_WS_WALK_FORWARD_OOS_PROOF_LOCKED.md)
- [`_refs/WS_OOS_EVIDENCE_NOTE.md`](_refs/WS_OOS_EVIDENCE_NOTE.md) — catatan referensi bukti, bukan sumber aturan utama
- [`db/PLAN_UNIVERSE_SNAPSHOT_SCHEMA.md`](db/PLAN_UNIVERSE_SNAPSHOT_SCHEMA.md)

## 4) Explicit Non-members (LOCKED)
Artefak berikut **bukan** anggota manifest ini kecuali kelak ditambahkan secara eksplisit:
- fixtures pada folder [`fixtures/`](fixtures/README.md)
- examples pada folder [`examples/`](examples/README.md)
- ledger/history notes
- export ad-hoc yang tidak punya kontrak resmi
- tabel/folder/file yang hanya pernah muncul di diskusi lama

## 5) Reference Rule (LOCKED)

Setiap penyebutan artefak resmi di dokumen lain harus memakai nama manifest yang sama dan tidak boleh memakai sinonim yang menciptakan artefak bayangan.

Jika dokumen Weekly Swing menyebut artefak sebagai:
- wajib,
- resmi,
- dipakai promote,
- dipakai proof,
- atau dipakai calibration,

maka artefak itu wajib memenuhi salah satu dari dua kondisi berikut:
1. tercantum pada bagian `Official WS Backtest Tables`, atau
2. tercantum pada bagian `Official Production Proof Artifact`.

Jika tidak, maka dokumen tersebut melakukan **artifact reference violation**.

## 6) Relationship with Deprecation Ledger
Jika sebuah artefak pernah disebut tetapi tidak ada di manifest ini, maka default status-nya adalah:
- non-scope / deprecated

dan artefak tersebut harus dicatat pada:
- [`19_WS_DEPRECATED_OR_NONSCOPE_ARTIFACTS_LEDGER.md`](19_WS_DEPRECATED_OR_NONSCOPE_ARTIFACTS_LEDGER.md)

## 7) Acceptance Rule (LOCKED)
Sebuah patch Weekly Swing hanya dianggap bersih secara artefak jika:
- semua artefak wajib berada di manifest ini,
- tidak ada artefak “hantu” yang disebut sebagai resmi,
- dan semua artefak non-scope tercatat pada ledger.

## Next
- [`19_WS_DEPRECATED_OR_NONSCOPE_ARTIFACTS_LEDGER.md`](19_WS_DEPRECATED_OR_NONSCOPE_ARTIFACTS_LEDGER.md)

## 8) Official deployment support for the OOS runtime

The following are supporting deployment artifacts controlled by the official schema/owner documents. They do not create additional official runtime tables:

- `db/BACKTEST_SCHEMA_DDL.sql`;
- `db/BACKTEST_PARAM_GRID_SEED.sql`;
- `db/BACKTEST_OOS_RUNTIME_GAP_CLOSURE.sql`;
- Laravel migrations for the same schema changes;
- `WatchlistBacktestParamGridSeeder` and `watchlist:backtest-param-grid-seed`.

A JSON OOS proof export is evidence transport, not a new table or a replacement for `watchlist_bt_eval` / `watchlist_bt_oos_eval_ws`.

Read-only operator verification may use `db/BACKTEST_OOS_RUNTIME_VERIFY.sql`; this is a support query, not a persisted artifact.

## 9) Official R2/C01 IS Calibration Evidence Transport

The deterministic JSON produced by `watchlist:backtest-is-calibrate` is an official IS-calibration evidence transport. It is not a new database table and does not replace `watchlist_bt_param_grid` or `watchlist_bt_eval`.

Required sections are:

```text
meta
catalog_manifest
parameter_axes
r1_control_reference
is_window_manifest
market_data_lineage
all_evaluations
best_is_binding
gate_summary
diagnostic_summary
persistence_manifest
r1_immutability_proof
r2_immutability_proof
no_oos_read_proof
validation
```


The artifact must record `production_ready=false`, `oos_executed=false`, and `paramset_promoted=false`. A filename is operator-selected evidence transport, not catalog identity. The normative reference notes are `_refs/WS_R2_ENTRY_QUALITY_CALIBRATION_NOTE.md` for R2 and `_refs/WS_DOWNSIDE_STABILITY_C01_CALIBRATION_NOTE.md` for C01.

## 10) Official C01 IS Failure Drilldown Evidence Transport

The deterministic JSON produced by `watchlist:backtest-is-diagnose` is an official IS-only diagnostic evidence transport for failed C01 analysis. It is not a new database table, not an OOS proof artifact, not a promotion artifact, and not production readiness evidence.

Required sections are:

```text
catalog_code
catalog_version
catalog_hash
catalog_count
is_from
is_to
is_trading_date_hash
artifact_hash
canonical_artifact_hash
per_param_status
per_param_failure_codes
per_param_key_metrics
nearest_gate_gap
worst_gate_gap
candidate_count_summary
days_covered_summary
month_win_rate_min_summary
month_avg_ret_min_summary
downside_metric_summary
robust_return_metric_summary
stability_metric_summary
ticker_loss_cluster_summary
ticker_profit_cluster_summary
month_failure_cluster_summary
month_profit_cluster_summary
trade_date_failure_cluster_summary
setup_bucket_summary
breakout_extension_bucket_summary
momentum_roc_bucket_summary
volume_ratio_bucket_summary
liquidity_dv20_bucket_summary
atr_bucket_summary
score_bucket_summary
sector_bucket_summary
score_component_effectiveness_summary
param_axis_effectiveness_summary
runtime_consumed_parameter_summary
dead_parameter_or_silent_default_summary
runtime_field_availability_summary
data_quality_diagnostic_summary
no_oos_leakage_summary
diagnostic_reason_summary
next_focus_recommendation
```

If a runtime feature field needed by a diagnostic bucket is not exported by the evaluated trade evidence, the artifact must record `FIELD_NOT_AVAILABLE_IN_RUNTIME_EVIDENCE`, `NOT_DERIVED`, and `NOT_USED_FOR_NEXT_CATALOG_DECISION` instead of synthesizing the missing field.

Canonical artifact hash must exclude timestamp-like metadata such as `generated_at`. The artifact must record `production_ready=false`, `oos_executed=false`, and `best_is_binding=null` when C01 has no valid IS parameter. The normative reference note is `_refs/WS_C01_IS_FAILURE_DRILLDOWN_NOTE.md`.

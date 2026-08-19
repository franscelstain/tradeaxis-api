# Watchlist Audit Foundation

## Purpose

Dokumen ini mengunci fondasi audit untuk domain `watchlist`.

Audit watchlist dipakai untuk memastikan seluruh layer dokumentasi dan implementasi tetap mengarah pada satu produk:
- Weekly Swing decision-support;
- qualified final recommendation;
- ranked `TOP_PICKS`;
- current actionability melalui CONFIRM;
- keputusan beli manual tanpa portfolio/execution automation.

## Current Active Scope

- domain aktif: `watchlist`
- active policy: `weekly_swing`
- active output layers: `PLAN`, `RECOMMENDATION`, `CONFIRM`

Policy lain di luar `weekly_swing` berada di luar scope audit aktif sampai policy `weekly_swing` dianggap matang.

Audit wajib menurunkan nilai jika dokumen strategy secara normatif memasuki domain out-of-scope, bukan sekadar menyebut dependency yang sah.

## Core Scope Lock

### In Scope
- system docs watchlist untuk `weekly_swing`;
- boundary `PLAN / RECOMMENDATION / CONFIRM`;
- runtime shape, contract, acceptance, dan implementation blueprint watchlist;
- examples, fixtures, refs, dan db support yang langsung mendukung `weekly_swing`.

### Out of Scope
- portfolio state, holdings, entry nyata, sell lifecycle;
- order placement, fill, broker integration, execution orchestration;
- market-data ingestion internals, provider scheduler, retry pipeline, fetch pipeline;
- policy lain di luar `weekly_swing`.

## Market Data Boundary

Watchlist hanya mengonsumsi producer-facing Market Data read product yang sah sesuai `../../strategy/WS_MARKET_DATA_INPUT_REQUIREMENTS.md`. Watchlist tidak mengambil alih provider acquisition, fetch/retry/scheduler, corporate-action processing, publication internals, benchmark/status reconstruction, atau producer-side data usability.

Audit wajib menurunkan nilai jika Watchlist:
- mendefinisikan ulang fakta Market Data;
- membaca producer internal tables sebagai normal intake contract;
- menerima current PLAN tanpa `READABLE + FRESH + same requested/effective date`;
- memperlakukan `data_usable` sebagai Weekly Swing eligibility;
- mencampur actual traded value dengan close×volume proxy tanpa identity change;
- bergantung pada data yang tidak dinyatakan tersedia pada strategy/evaluation identity;
- memakai future information atau source yang tidak dapat direplay secara point-in-time.

## Audit Layers

### Layer A — Document Audit
Menilai owner docs, boundary, scope, dan sinkronisasi dokumen system.

### Layer B — Build Translation Audit
Menilai apakah implementation blueprint dan build guidance menerjemahkan system docs dengan benar.

### Layer C — Implementation Audit
Menilai apakah aplikasi nyata dibangun sesuai system docs.

Untuk fase aktif saat ini, fokus utama adalah Layer A dan Layer B. Layer C akan dibangun setelah `weekly_swing` system docs dianggap matang.

Support artifacts seperti `_refs`, `examples`, `fixtures`, `db/*.sql`, `db/*.md`, dan sample JSON **tidak otomatis mengaktifkan Layer C**. Artifact tersebut tetap dihitung sebagai bukti/support untuk Layer A/B sampai ada code aplikasi nyata, runtime payload nyata dari app, atau persistence runtime nyata.

## Mandatory Watchlist Rules

Audit wajib memastikan seluruh current docs/implementation konsisten terhadap rule berikut:

1. watchlist hanya memberi decision-support dan bukan portfolio/execution engine;
2. active policy hanya `weekly_swing`;
3. PLAN memakai `RECOMMENDATION_CANDIDATES`, `WATCH_ONLY`, `AVOID`;
4. istilah `TOP_PICKS` hanya dimiliki final RECOMMENDATION;
5. final recommendation berasal dari immutable PLAN;
6. seluruh dan hanya candidate yang lulus final qualification menjadi Top Picks;
7. jumlah Top Picks boleh nol dan tidak boleh dipaksa quota;
8. active hard-gate/scoring feature harus lengkap untuk recommendation candidacy; missing active feature fail-closed;
9. canonical `score_total` memakai normalized weighted-sum semantics dan `recommendation_score = score_total`;
10. capital/affordability tidak boleh mengubah recommendation membership/rank;
11. CONFIRM hanya mengevaluasi final Top Picks pada canonical D+1 entry window dan hanya mengubah current actionability;
12. stale/over-drift/invalid trade-plan tidak boleh berstatus actionable;
13. backtest/IS/OOS mengukur final Top Picks, bukan PLAN state proxy;
14. production qualification memakai realistic cost, non-zero slippage, adverse-friction stress, dan ranking-quality proof;
15. OOS tidak boleh retuning;
16. core production-use approval membutuhkan core forward-shadow proof; CONFIRM mempunyai capability-specific proof terpisah dan tidak memblokir core approval bila data/source belum tersedia.

## Expected Outputs of Audit

Setiap audit minimal harus menghasilkan:
- status per file: `PASS / PARTIAL / FAIL / N/A`;
- rekap rule inti lintas file;
- daftar drift / conflict;
- patch prioritas berikutnya.

## Final Rule

Audit watchlist tidak boleh menilai dokumen sebagai matang jika boundary scope belum terkunci, walaupun wording terlihat rapi.

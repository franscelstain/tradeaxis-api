# 24 — WS Recommendation Algorithm

## Purpose

Dokumen ini menetapkan final qualification dan ranking yang menghasilkan **Weekly Swing Top Picks**. Recommendation adalah quality gate setelah PLAN, bukan quota filler dan bukan second independent scoring engine.

## A. Source Universe

Source universe hanya terdiri dari immutable PLAN items dengan state `RECOMMENDATION_CANDIDATES`.

`WATCH_ONLY` dan `AVOID` tidak boleh dipromosikan pada run yang sama.

## B. Final Qualification Gates

Candidate hanya dapat menjadi Top Pick bila seluruh gate berikut lulus:

1. PLAN binding dan candidate state sah untuk `trade_date` yang sama;
2. seluruh active candidate-level hard gate tetap dinyatakan lulus pada immutable PLAN;
3. candidate mempunyai complete valid predeclared trade plan untuk active exit-policy identity;
4. exit-policy-specific risk requirement terpenuhi:
   - stop/target mode: valid stop, target, dan minimum risk/reward;
   - sequential signal-next-open mode: valid predeclared profit/loss thresholds, next-open routing, dan bounded fallback horizon;
5. `score_total` memenuhi **absolute final recommendation quality floor** pada frozen active strategy/paramset identity;
6. seluruh optional final dimension floor yang memang aktif pada strategy identity lulus;
7. tidak ada active disqualifying rule pada strategy identity yang sama.

Final recommendation quality floor harus ditetapkan sebelum evaluation outcome dibaca dan tidak boleh diturunkan hanya untuk memenuhi jumlah Top Picks.

## C. Recommendation Score

Canonical baseline:

`recommendation_score = PLAN score_total`

`score_total` mengikuti formula dan frozen component transforms/weights di `08_WS_PLAN_ALGORITHM.md`.

Tidak ada second-stage opaque rescore pada canonical baseline.

Perubahan menjadi score formula lain adalah strategy change dan wajib membuktikan ranking utility baru pada IS/OOS tanpa post-OOS retuning.

## D. Ranking

Seluruh qualified candidates diurutkan secara deterministic:

1. `recommendation_score` descending;
2. breakout/setup quality descending;
3. momentum quality descending;
4. liquidity descending;
5. ATR ascending;
6. stable ticker identity ascending.

Hasil urutan menjadi `recommendation_rank = 1..N`.

Rank #1 harus bermakna **candidate qualified dengan canonical predicted-quality ordering tertinggi**, bukan candidate yang dipilih karena capital, presentation position, atau retrospective return.

## E. Recommendation Count

`N = jumlah candidate yang lulus seluruh final qualification gates`.

Tidak ada fixed recommendation count, dynamic quota, forced minimum, atau strategy cap yang menghapus candidate qualified.

`N` boleh sama dengan nol.

## F. Empty Set

Jika tidak ada candidate lulus, canonical output adalah:

**NO QUALIFIED TOP PICKS**

Empty set bukan error dan tidak boleh memicu pelonggaran rule otomatis.

## G. Capital / Affordability

Capital tidak menjadi input selection atau ranking.

Setelah final Top Picks dibentuk, consumer boleh menghitung affordability atau suggested lot sebagai information-only enrichment. Informasi tersebut tidak boleh mengubah recommendation membership, score, rank, atau historical recommendation state.

## H. Explainability

Setiap evaluated candidate harus dapat menjelaskan:
- candidate-state outcome;
- final gate yang lulus/gagal;
- canonical component scores dan `score_total`;
- reason mengapa masuk/tidak masuk Top Picks;
- rank keys yang menentukan urutan ketika score sama.

Explainability tidak boleh memperkenalkan post-hoc reason yang tidak ikut active strategy identity.

## I. Determinism

Untuk authoritative PLAN snapshot dan frozen strategy identity yang sama, recommendation membership, score, dan rank harus identik pada replay.

## J. Proof Binding

Backtest, OOS, adverse-friction stress, dan shadow harus mengukur output algorithm ini secara langsung.

PLAN candidate state atau historical PLAN priority group tidak boleh dipakai sebagai proxy untuk final Top Picks proof.

## Final Rules

1. final recommendation adalah qualification problem, bukan quota-filling problem;
2. seluruh dan hanya qualified candidates menjadi Top Picks;
3. `recommendation_score` sama dengan canonical PLAN `score_total`;
4. ranking tidak dipengaruhi capital atau CONFIRM;
5. Top Picks boleh kosong;
6. quality floor tidak boleh dilonggarkan karena jumlah recommendation sedikit;
7. proof harus mengevaluasi exact final recommendation output.

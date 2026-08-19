# Weekly Swing Strategy Audit — Qualified Top Picks Findings

## Audit Objective

Audit ini menilai canonical Weekly Swing strategy terhadap tujuan produk berikut:

> menghasilkan qualified recommendation yang diurutkan sebagai Top Picks terbaik untuk membantu keputusan beli manual pada horizon Weekly Swing, menggunakan Market Data yang sah, dengan positive expected net return setelah biaya yang realistis dan downside yang terkendali.

## Material Findings

### F1 — PLAN dan RECOMMENDATION sama-sama memakai istilah TOP_PICKS

PLAN memiliki group `TOP_PICKS`, sementara produk akhir juga membutuhkan Top Picks sebagai final recommendation. Hal ini membuat consumer dan implementer tidak dapat membedakan candidate priority dengan final buy recommendation secara tegas.

**Impact:** HIGH — dapat menyebabkan PLAN priority dibaca sebagai rekomendasi final.

### F2 — recommendation_score tidak mempunyai definisi strategy yang cukup

Canonical recommendation algorithm sebelumnya hanya memerintahkan `compute recommendation_score` tanpa mengunci hubungan score tersebut dengan PLAN scoring.

**Impact:** HIGH — rank #1 tidak mempunyai dasar normatif yang dapat diuji konsisten terhadap rank berikutnya.

### F3 — capital input dapat mengubah recommendation membership

Strategy sebelumnya mengizinkan `CAPITAL_AWARE` recommendation. Affordability adalah constraint pengguna, bukan ukuran kualitas saham.

**Impact:** HIGH — saham yang lebih baik dapat hilang dari Top Picks hanya karena nominal modal, sehingga ranking tidak lagi merepresentasikan kualitas Weekly Swing.

### F4 — backtest mengukur PLAN priority bucket, bukan final recommendation

Canonical backtest sebelumnya menyatakan TOP evaluation bucket merujuk PLAN priority bucket dan selection dibentuk dari PLAN logic. Dengan demikian final recommendation yang dipakai pengguna belum menjadi objek utama proof IS/OOS.

**Impact:** CRITICAL — evidence profitabilitas dapat membuktikan layer yang berbeda dari output produk akhir.

### F5 — zero-slippage canonical proof terlalu ideal untuk production-use objective

Canonical baseline sebelumnya menggunakan slippage nol dan fixed-IDR fee. Untuk strategi yang akan dipakai pada keputusan beli nyata, proof dengan biaya terlalu ideal dapat melebihkan edge, terutama bila expected return per trade kecil.

**Impact:** HIGH — research pass belum otomatis berarti edge bertahan setelah friction nyata.

### F6 — CONFIRM dapat berlaku pada non-recommended candidate

CONFIRM sebelumnya dapat memvalidasi candidate PLAN yang tidak masuk recommendation. Untuk product scope yang fokus pada keputusan beli dari Top Picks, hal ini menambah jalur yang dapat membingungkan consumer.

**Impact:** MEDIUM — dapat membuat non-recommended candidate terlihat seperti alternatif rekomendasi.

### F7 — recommendation count algoritmik masih dapat dibaca sebagai quota selection

Strategy sebelumnya mengatur dynamic recommendation count tetapi tidak mengunci bahwa seluruh dan hanya candidate yang memenuhi quality floor harus direkomendasikan.

**Impact:** HIGH — implementasi dapat memotong atau memaksa jumlah picks tanpa dasar kualitas.

## Audit Conclusion

Perubahan strategy diperlukan. Ini bukan implementation refactor biasa karena temuan menyentuh product meaning, proof target, ranking semantics, dan real-world robustness.

### F8 — best current B01 evidence does not yet prove the revised final-Top-Picks product

B01 is the strongest current historical evidence: canonical IS and Official OOS passed and ACTIVE shadow ran without production rollout. However its proof predates this strategy revision, uses the prior recommendation/PLAN semantics and low-friction evaluation identity, and therefore cannot be treated as proof that the revised final Top Picks algorithm, capital-independent membership, ranking-quality gates, realistic friction, or revised CONFIRM actionability have passed.

**Impact:** CRITICAL — B01 remains the primary research/evidence anchor, but revised strategy requires a new implementation-aligned proof before production use.

### F9 — PRIMARY/SECONDARY PLAN tiers no longer have decision value under qualification-driven recommendation

After removing quota/fallback behavior, both PRIMARY and SECONDARY were allowed into the same final qualification path. Keeping both labels adds structure without changing recommendation membership or ranking.

**Impact:** MEDIUM — unnecessary complexity can reintroduce fallback/quota interpretations.

### F10 — missing active scoring features could still be tolerated by old technical semantics

Prior technical rules could keep a ticker eligible and assign zero to a missing scoring component. For a product that claims rank #1 is the best qualified stock, candidates must be compared using the complete feature set actually used by the active score formula.

**Impact:** HIGH — incomplete-feature candidates can distort score comparability and Top Picks ordering.

### F11 — real-use decision point requires explicit entry-window actionability

An EOD Top Pick can become stale or too far from its planned entry by the time the user buys. Recommendation quality proof alone does not prove that the actual entry offered to the user is still valid.

**Impact:** HIGH — without an explicit D+1 actionability gate, user behavior can diverge materially from the evaluated entry model.

## Traceable Evidence Sources

Audit findings are grounded in the preserved pre-revision strategy and current historical/technical records, including:

- `../../records/history/superseded/2026-08-16_pre-qualified-top-picks-strategy/` — exact pre-revision canonical strategy snapshot;
- `../../records/evidence/results/WS_BREAKOUT_INTEGRITY_B01_EXECUTION_AND_TARGET_ALIGNMENT.md` — strongest current IS/OOS/shadow evidence and current `PRODUCTION_READY=0` boundary;
- `../../records/evidence/locks/WS_OOS_EVIDENCE_NOTE.md` and historical evaluation records — prior low-friction evaluation identity;
- `../implementation/contracts/WS_BACKTEST_PERSISTENCE_AND_UNIVERSE_SCHEMA_CONTRACT.md` — prior missing-scoring-feature zero-fill semantics;
- `../implementation/contracts/23_WS_RECOMMENDATION_INPUT_OUTPUT_CONTRACT.md` and related fixtures/examples — pre-revision recommendation/capital semantics;
- `../implementation/contracts/05_WS_PARAMETER_REGISTRY_COMPLETE.md` — prior PLAN grouping targets and CONFIRM drift/freshness parameters.

These sources remain evidence/history/implementation records; they are not rewritten as if they had already conformed to the revised strategy.

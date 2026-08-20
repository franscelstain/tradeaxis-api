# Watchlist Weekly Swing — PLAN Scoring and Trade-Plan Strategy

## Purpose

PLAN membentuk Weekly Swing candidate pool yang bersih, terukur, dan mempunyai deterministic quality ordering serta predeclared trade plan. PLAN adalah feeder untuk final RECOMMENDATION, bukan final buy recommendation.

## Lifecycle Position

- **Stage:** `WS-S03` — PLAN Scoring, Ordering, and Trade-Plan Freeze.
- **Consumes:** candidate states from `WS-S02`.
- **Produces:** immutable PLAN with canonical score/order and predeclared trade plan.
- **Next:** `WS-S04` final recommendation qualification.

## Canonical PLAN Pipeline

1. bind trusted Market Data intake from `WS_MARKET_DATA_INPUT_REQUIREMENTS.md`, including requested/effective date and publication/read-model identity;
2. verify required active features dan data usability;
3. apply candidate eligibility/setup strategy;
4. assign deterministic candidate state under canonical `WS-S02` classification rules;
5. verify complete active scoring features untuk `RECOMMENDATION_CANDIDATES`;
6. compute deterministic score components hanya untuk `RECOMMENDATION_CANDIDATES`;
7. compute canonical `score_total`;
8. order recommendation candidates secara deterministik;
9. derive entry reference/band dan predeclared exit/risk plan;
10. freeze final PLAN.

## Candidate Eligibility

Candidate eligibility mengikuti `WS_CANDIDATE_ELIGIBILITY_AND_SETUP.md`.

Ticker yang gagal mandatory data, liquidity, participation, volatility, setup, regime, atau risk guard yang aktif tidak boleh menjadi `RECOMMENDATION_CANDIDATES`.

Hard guard tidak boleh dilonggarkan hanya untuk menambah jumlah kandidat.

## PLAN Scoring

Canonical PLAN quality score dibentuk dari empat quality dimensions:
- momentum quality;
- breakout/setup quality;
- participation/volume quality;
- risk quality.

Setiap component score harus:
- berada pada normalized range `[0,1]` dengan `1` = kualitas lebih baik;
- berasal hanya dari information available pada signal date;
- menggunakan deterministic versioned transform;
- mempunyai active feature yang valid; missing active component tidak boleh diganti synthetic zero untuk recommendation candidate.

Canonical combine semantics adalah normalized weighted sum:

`score_total = clamp01(w_momentum*S_momentum + w_breakout*S_breakout + w_volume*S_volume + w_risk*S_risk)`

dengan:
- seluruh weight `>= 0`;
- total weight `= 1`;
- exact component transform dan weights menjadi bagian dari frozen active strategy/paramset identity;
- weights/transform tidak boleh diubah setelah evaluation outcome dibaca.

`score_total` adalah canonical quality score yang dipakai downstream untuk final recommendation qualification dan ranking. Baseline Weekly Swing tidak membuat second opaque score yang tidak mempunyai hubungan jelas dengan PLAN quality.

## Score Interpretation Boundary

`score_total` mengukur relative/canonical setup quality dan **bukan calibrated probability of profit, target return, atau certainty score**. Monotonic ordering harus dibuktikan downstream melalui realized ranking utility; nilai score sendiri tidak boleh dipakai untuk mengklaim probabilitas kemenangan.

## Feature-Family Expansion / Challenger Rule

Canonical baseline tetap empat quality dimensions di atas. Penambahan feature family seperti relative strength vs IHSG, sector relative strength, trend structure, atau regime-specific score **tidak boleh masuk diam-diam** ke baseline.

Setiap feature-family addition harus:

1. menjadi preregistered challenger identity;
2. memakai same-date causal producer facts;
3. dibandingkan dengan canonical baseline pada dates, friction, dan evaluation semantics yang sama;
4. menunjukkan incremental edge/ranking utility yang bukan hanya isolated best return;
5. melewati multiple-testing, IS robustness, untouched OOS, stress, dan shadow proof sebagai identity baru sebelum dapat menggantikan baseline.

Feature yang gagal menambah robust evidence tidak dipertahankan hanya karena terlihat intuitif.

## Canonical PLAN Ordering

`RECOMMENDATION_CANDIDATES` diurutkan dengan prioritas:

1. `score_total` lebih tinggi;
2. breakout quality lebih tinggi;
3. momentum quality lebih tinggi;
4. canonical liquidity metric (`adv20_close_volume_proxy_idr` for current bootstrap identity) lebih tinggi;
5. ATR lebih rendah sebagai risk tie-break;
6. stable ticker identity sebagai final deterministic tie-break.

Ordering ini belum otomatis membuat candidate menjadi Top Pick.

## PLAN Candidate States

### RECOMMENDATION_CANDIDATES

Candidate yang telah melewati seluruh mandatory candidate gate, mempunyai complete active score features, dan boleh masuk final recommendation qualification.

### WATCH_ONLY

Ticker yang masih berguna untuk monitoring/diagnostic tetapi tidak memenuhi syarat untuk menjadi final recommendation pada run tersebut.

### AVOID

Ticker yang gagal mandatory Weekly Swing rule atau secara eksplisit dilarang oleh active strategy identity.

## Plan-Derived Trade Plan

Candidate yang dapat diteruskan ke recommendation harus mempunyai trade plan yang lengkap untuk active Weekly Swing evaluation identity.

Minimal:
- causal entry rule/reference;
- maximum holding horizon;
- active exit-policy binding;
- risk/downside control yang sesuai exit policy.

Jika active exit policy memakai stop/target, PLAN wajib mempunyai stop, target, dan minimum risk/reward yang sah.

Jika active exit policy memakai sequential close-signal → next-open exit, PLAN wajib mengikat seluruh profit/loss signal threshold dan fallback exit sebelum entry.

PLAN adalah planning information, bukan order instruction.

## Final Rules

1. PLAN tidak memiliki final Top Picks.
2. Hanya `RECOMMENDATION_CANDIDATES` yang dapat diteruskan ke final recommendation qualification.
3. `WATCH_ONLY` dan `AVOID` tidak dapat menjadi Top Picks pada run yang sama.
4. `score_total` adalah canonical downstream quality score dengan explicit weighted-sum semantics.
5. Candidate count tidak boleh dipaksa untuk memenuhi kebutuhan output.

## Strategy Calculation vs Market-Fact Derivation

PLAN boleh menghitung keputusan Weekly Swing, tetapi tidak boleh menjadi feature-engineering layer Market Data.

- Score component, `score_total`, ordering, entry reference/zone, stop, target, dan horizon-derived plan adalah Weekly Swing strategy outputs dan boleh dihitung dari authoritative producer facts + frozen strategy parameters.
- Intermediate calculation yang meaning-nya hanya ada di dalam frozen Weekly Swing score/trade-plan identity tetap strategy output; ia tidak boleh dipublikasikan sebagai pengganti market fact upstream.
- Reusable factual measurement seperti new momentum/liquidity/relative-strength/regime/sector/session feature tetap Market Data-owned walaupun PLAN dapat menghitungnya dari raw inputs; PLAN **MUST NOT** melakukan derivation tersebut untuk menyelamatkan active rule.
- Adopsi new market feature ke score/trade-plan membutuhkan producer-facing fact yang authoritative serta controlled strategy identity/re-proof; keberadaan raw ingredients saja tidak cukup.

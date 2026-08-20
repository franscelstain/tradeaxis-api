# Watchlist Weekly Swing — End-to-End Strategy Lifecycle

## Purpose

Dokumen ini adalah canonical orchestration owner untuk **urutan end-to-end Weekly Swing strategy**. Ia menetapkan dependency, handoff, valid stop condition, dan proof path dari trusted Market Data sampai production-use eligibility.

Stage `WS-S00` sampai `WS-S11` membentuk satu authoritative lifecycle set. Core runtime berjalan `WS-S00..WS-S04`; `WS-S05` adalah optional non-blocking CONFIRM branch; proof core berjalan `WS-S06..WS-S11` tanpa dependency pada availability CONFIRM.

## End-to-End Objective

Weekly Swing harus mengubah trusted Market Data menjadi decision-support yang dapat dipakai secara nyata melalui alur:

`trusted Market Data → eligible candidates → immutable PLAN → qualified TOP PICKS → manual buy decision support`

Optional enhancement branch:

`qualified TOP PICK → optional D+1 CONFIRM → current-actionability evidence`

Strategy baru boleh dipertimbangkan untuk real use setelah alur recommendation yang sama mempunyai proof:

`IS PASS → untouched OOS PASS → adverse-friction PASS → forward-shadow PASS → production-use review`

Tidak ada **required core/proof stage** yang boleh dilewati hanya karena hasil stage sebelumnya terlihat baik. `WS-S05` tidak termasuk required core/proof chain dan boleh tidak dijalankan ketika decision-time data tidak tersedia.

## Canonical Stage Map

| Stage | Purpose | Required input | Normative output | Exit condition |
|---|---|---|---|---|
| `WS-S00` | lock product objective, scope, success meaning, dan boundary | Weekly Swing product intent | frozen Weekly Swing scope + Top Picks meaning | tidak ada ambiguity tentang apa yang direkomendasikan dan apa yang out-of-scope |
| `WS-S01` | bind trusted same-date market facts | authoritative point-in-time Market Data | evaluation-ready market context atau fail-closed outcome | seluruh required market facts tersedia/valid untuk candidate path |
| `WS-S02` | determine absolute eligibility dan candidate state | `WS-S01` market context | `RECOMMENDATION_CANDIDATES`, `WATCH_ONLY`, `AVOID` | setiap ticker mempunyai deterministic state; tidak ada forced candidate quota |
| `WS-S03` | score/order recommendation candidates dan freeze trade plan | `RECOMMENDATION_CANDIDATES` + active strategy identity | immutable PLAN dengan `score_total`, ordering, entry reference, dan predeclared risk/exit plan | PLAN complete dan immutable untuk `trade_date` |
| `WS-S04` | apply final quality qualification dan form ranked Top Picks | immutable PLAN | final `TOP_PICKS` rank `1..N`, termasuk valid `N=0` | seluruh dan hanya qualified candidates menjadi ranked Top Picks |
| `WS-S05` | optionally determine current D+1 actionability | final Top Pick; valid current-entry snapshot when available | `NOT_REQUESTED`, `UNAVAILABLE_RETRYABLE`, `ACTIONABLE`, `NOT_ACTIONABLE`, atau `EXPIRED_UNCONFIRMED` | optional branch never blocks core runtime/proof; valid data may be retried within entry window |
| `WS-S06` | define causal historical evaluation model | frozen strategy identity + historical authoritative Market Data | reproducible executable Top-Pick trade outcomes dan calibration dataset | exact final recommendation can be replayed and evaluated net of baseline friction |
| `WS-S07` | establish IS sufficiency dan freeze one best-IS binding | `WS-S06` IS outcomes | `IS PASS + frozen winner`, `IS FAIL`, atau `INSUFFICIENT EVIDENCE` | hanya one frozen binding boleh diteruskan bila seluruh IS floor lulus |
| `WS-S08` | test frozen winner on untouched OOS | frozen best-IS binding + untouched OOS suffix | OOS verdict | sample, return, downside, stability, dan ranking-usefulness OOS gates lulus |
| `WS-S09` | test edge under worse realistic friction | exact frozen OOS recommendation set | adverse-friction verdict | positive net edge tetap bertahan pada stress profile |
| `WS-S10` | validate live-available core Top-Picks flow; optionally observe CONFIRM | exact frozen strategy + forward-only Market Data; optional D+1 data when available | core forward-shadow verdict + optional CONFIRM capability evidence | core duration/sample/return/no-leakage gates lulus; missing CONFIRM data is not a core failure |
| `WS-S11` | determine whether exact core strategy identity may enter production-use review | PASS core outcomes from `WS-S07..WS-S10` | production-use eligible-for-review atau rejected/not-ready; separate CONFIRM proof status | all required core proof stages PASS; CONFIRM availability does not block core review |

## Stage Contracts

### WS-S00 — Scope and Success Lock

**Consumes:** product intent untuk Weekly Swing IDX.

**Must establish:**
- only `weekly_swing` active policy;
- decision-support only, bukan execution/portfolio system;
- final `TOP_PICKS` = qualified recommendations, bukan PLAN group;
- quality over quantity dan valid zero-pick outcome;
- objective = positive expected net return after realistic friction dengan controlled downside, bukan guaranteed profit.

**Produces:** stable strategic boundary yang menjadi constraint seluruh stage berikutnya.

**May advance when:** objective, naming, scope, dan out-of-scope tidak ambigu.

### WS-S01 — Trusted Market Data Binding

**Owner detail:** `WS_MARKET_DATA_INPUT_REQUIREMENTS.md`.

**Consumes:** one producer-facing, publication-aware Market Data read product for requested `asof_eod_date`, or an explicit exact/as-known replay identity for historical proof.

**Must establish for a new current PLAN:**
- `readiness_state = READABLE`;
- `freshness_state = FRESH`;
- `effective_trade_date = requested_trade_date`;
- one coherent publication/read-model/config/formula identity;
- stable listing identity and upstream `data_usable` facts;
- every field used by the active Weekly Swing gate/score/trade-plan rule is valid.

Explicit prior-date `STALE/DEGRADED` fallback may be observed as stale context but cannot be relabelled into a new PLAN for the requested date. `data_usable` is an upstream prerequisite, not Weekly Swing strategy eligibility.

**Produces:** trusted intake context plus per-ticker producer facts for `WS-S02`, or an explicit market-data availability/no-output state without direct-table fallback.

**May advance when:** required market facts are available through the single producer-facing contract and Watchlist does not reconstruct producer meaning.

### WS-S02 — Eligibility and Candidate Classification

**Consumes:** trusted market context.

**Must apply before scoring:**
- liquidity/executability floor;
- participation/volume rule;
- volatility/risk rule;
- momentum/setup requirements;
- regime compatibility bila active;
- complete active feature requirement.

**Produces exactly one state per evaluated ticker:**
- `RECOMMENDATION_CANDIDATES`;
- `WATCH_ONLY`;
- `AVOID`.

Only `RECOMMENDATION_CANDIDATES` may enter scoring/final-recommendation path.

**May advance when:** state assignment deterministic dan tidak memakai relative quota untuk menyelamatkan ticker yang gagal absolute quality.

### WS-S03 — PLAN Scoring, Ordering, and Trade-Plan Freeze

**Consumes:** `RECOMMENDATION_CANDIDATES` dari `WS-S02`.

**Must produce:**
- valid normalized component scores;
- canonical `score_total`;
- deterministic candidate ordering;
- causal entry reference/band;
- one active predeclared exit/risk-policy binding;
- immutable PLAN.

PLAN **does not** create final Top Picks.

**May advance when:** PLAN untuk `trade_date` complete, deterministic, replayable, dan immutable.

### WS-S04 — Final Recommendation and Top Picks

**Consumes:** immutable PLAN only.

**Must apply:**
- final mandatory quality/risk gates;
- absolute recommendation quality floor;
- canonical score-based deterministic ordering.

**Produces:** all and only qualified candidates as `TOP_PICKS` rank `1..N`.

`N` has no quota and may be zero.

**May advance when:** membership/rank reproducible and capital-independent.

### WS-S05 — Optional D+1 CONFIRM Actionability

`WS-S05` is an **optional non-blocking branch** from final Top Picks. `WS-S06` core proof does not depend on completion of `WS-S05`.

**Consumes:** final Top Pick and a valid current-entry snapshot **when available** during canonical entry window. The D+1 snapshot is a decision-time input distinct from the EOD snapshot used to form the recommendation and cannot be used retroactively in EOD scoring/ranking.

**May produce:**
- `NOT_REQUESTED`;
- `UNAVAILABLE_RETRYABLE`;
- `ACTIONABLE`;
- `NOT_ACTIONABLE`;
- `EXPIRED_UNCONFIRMED`.

`NOT_ACTIONABLE` requires valid decision-time data and an actually evaluated failed gate. Missing, stale, incomplete, delayed, or temporarily unavailable data must produce `UNAVAILABLE_RETRYABLE`, not a negative decision.

`UNAVAILABLE_RETRYABLE` may be reevaluated when valid data arrives while the canonical entry window remains open.

CONFIRM cannot add a ticker, rescore, rerank, rewrite EOD recommendation history, or turn core Top Picks into a failed run.

**Valid outcomes:**
- no Top Picks → valid `NO QUALIFIED TOP PICKS`; CONFIRM not needed;
- no CONFIRM request/data → Top Picks remain valid EOD recommendations;
- Top Pick validly evaluates not actionable → `DO NOT ENTER NOW` for that ticker;
- expired entry window without valid evaluation → `EXPIRED_UNCONFIRMED`, no automatic carry-forward and no core failure.

### WS-S06 — Historical Evaluation Model

**Consumes:** exact frozen EOD selection/trade-plan semantics and historical point-in-time Market Data.

**Must replay:** candidate eligibility → PLAN → final Top Picks → causal executable entry/exit → realistic net return.

Only final Top Picks are canonical evaluated recommendations. Canonical historical evaluation proves EOD recommendation/ranking edge; it does not fabricate unavailable D+1 CONFIRM observations. Optional CONFIRM capability may be evaluated in `WS-S10` using actually available decision-time information, but its data availability is not required for core proof.

**Produces:** deterministic IS/OOS-ready outcomes, including valid no-recommendation dates and skipped non-executable trades.

### WS-S07 — IS Sufficiency and Winner Freeze

**Consumes:** IS outcomes from one preregistered candidate grid.

**Must evaluate:** sample sufficiency, coverage, net-return distribution, downside, period stability, and ranking usefulness.

**Produces one of:**
- `IS PASS` and exactly one frozen best-IS identity;
- `IS FAIL`;
- `INSUFFICIENT EVIDENCE`.

Only the frozen winner may enter untouched OOS.

### WS-S08 — Untouched OOS Proof

**Consumes:** exact frozen best-IS identity and untouched chronological OOS suffix.

No threshold, score transform, weight, cost assumption, recommendation rule, or exit rule may be retuned after OOS outcome is read.

**Produces:** OOS PASS/FAIL/INSUFFICIENT verdict on exact final Top Picks.

### WS-S09 — Adverse-Friction Robustness

**Consumes:** exact frozen OOS recommendations; recommendation membership and rank stay unchanged.

Only execution-return assumptions become more conservative.

**Produces:** stress PASS/FAIL.

If positive net edge disappears under realistic adverse friction, strategy is not production-use eligible.

### WS-S10 — Forward Shadow Core Flow + Optional CONFIRM Observation

**Consumes:** exact frozen strategy identity using only information actually available forward in time.

**Required core observation:**
`Market Data → PLAN → TOP PICKS → causal realized outcome`

**Optional CONFIRM observation when valid D+1 data is available:**
`TOP PICK → CONFIRM → ACTIONABLE/NOT_ACTIONABLE`

If CONFIRM data is absent, delayed, stale, or incomplete, record the applicable availability state and continue core shadow. Do not synthesize CONFIRM and do not fail core shadow because optional data is unavailable.

No automated trade is required or permitted by this strategy stage.

**Produces:**
- core forward-shadow `PASS/FAIL/INSUFFICIENT` verdict;
- separate optional CONFIRM capability evidence/status when enough valid observations exist.

### WS-S11 — Production-Use Boundary

Core production-use review may begin only when the **same material core strategy identity** has:

`IS PASS + OOS PASS + adverse-friction PASS + core forward-shadow PASS`

CONFIRM is assessed separately:
- sufficient valid optional CONFIRM shadow evidence may support `CONFIRM_PROVEN`;
- insufficient/unavailable CONFIRM data yields `CONFIRM_UNPROVEN` / `CONFIRM_EVIDENCE_INSUFFICIENT` and does **not** invalidate core Top Picks production-use review.

This stage does not authorize broker execution, portfolio management, or automatic deployment. It only establishes that the decision-support strategy has sufficient proof to be considered for real user-facing use.

## High-Trust Proof Controls Across WS-S06..WS-S11

The required proof chain additionally enforces:

- `WS-S06`: operational recommendation-availability timestamping, committed post-entry exposure resolution, benchmark/excess-return inputs, tail/path-risk metrics, capacity metrics, and date-clustered statistical inputs;
- `WS-S07`: complete trial ledger, selection-bias/multiple-testing control, DSR, PBO when computable, robust parameter-plateau review, economic-significance floor, benchmark uplift, tail-risk and Top-K acceptance;
- `WS-S08`: purged IS/OOS boundary and protected OOS suffix; once OOS outcome is read it becomes consumed and cannot be reused as untouched proof for a materially changed identity;
- `WS-S09`: exact membership/rank stress under more adverse friction plus reference-notional/capacity assumptions;
- `WS-S10`: live-available publication timing, minimum decision lead time, causal executable outcomes including exit delays, and live-equivalent ranking/capacity/tail behavior;
- `WS-S11`: production-use review plus continuing 20/60-trading-day health monitoring and deterministic suspension/revalidation boundary.

None of these controls may be relaxed after seeing a negative downstream outcome merely to preserve progression.

## Canonical Stop / Failure Routing

Required core lifecycle must fail closed where evidence is actually required:

1. invalid/untrusted required EOD Market Data → do not create recommendation from affected path;
2. zero eligible candidates → `NO QUALIFIED TOP PICKS`;
3. zero final qualified candidates → `NO QUALIFIED TOP PICKS`;
4. missing/stale/incomplete CONFIRM data → `UNAVAILABLE_RETRYABLE` (or `EXPIRED_UNCONFIRMED` after window closes), **not core failure**;
5. validly evaluated Top Pick fails CONFIRM → `NOT_ACTIONABLE`, no fallback ticker promotion;
6. insufficient required IS/OOS/core-shadow sample → `INSUFFICIENT EVIDENCE`, not PASS;
7. any required core proof gate fails → core production-use path stops;
8. insufficient optional CONFIRM proof → CONFIRM remains unproven; core proof remains unchanged;
9. material strategy change after proof → proof identity resets for the affected behavior and required stages must be rerun.

## Identity Continuity Rule

Runtime and proof are comparable only when the material identity remains bound across:
- candidate eligibility semantics;
- score transforms/weights;
- final recommendation qualification/ranking;
- entry/exit/horizon model;
- transaction-cost/slippage profile for the evaluated proof;
- CONFIRM semantics only when CONFIRM capability proof/status is being claimed;
- Market Data requested/effective date, publication/read-model/config/formula/factor identity required by the producer-facing read contract.

A material change creates a new proof identity. Historical PASS cannot be administratively inherited by behavior that was not actually tested.

## End-to-End Completion Definition

Weekly Swing **core runtime is complete** when `WS-S00..WS-S04` define an unambiguous deterministic path through final ranked Top Picks, including valid empty output.

`WS-S05` is an optional capability branch. Lack of CONFIRM data, implementation, or successful evaluation does not make the core runtime incomplete.

Weekly Swing **core proof is complete** when `WS-S06..WS-S11` establish sufficient IS, untouched OOS, friction robustness, and core forward-shadow evidence for the exact final-Top-Picks behavior.

CONFIRM is **capability-proven** only when separate forward decision-time evidence is sufficient. `CONFIRM_UNPROVEN` is compatible with core strategy proof-complete status.

Downstream realization of Weekly Swing must preserve these required-vs-optional dependencies; a different realization structure cannot make CONFIRM a prerequisite for core completion.

## Production Continuation Rule

`WS-S11` approval is a gate into monitored use, not a permanent terminal certificate. While strategy remains user-facing, health monitoring is part of the active strategy contract. Confirmed material degradation can suspend new recommendation publication without rewriting historical recommendations. Restart after suspension requires evidence appropriate to the root cause and any changed strategy identity.

## Final Invariants

1. Stage IDs `WS-S00..WS-S11` are authoritative; `WS-S05` is explicitly optional and not a required predecessor of `WS-S06`.
2. Absolute quality and required EOD data validity precede scoring/ranking.
3. Candidate classification precedes scoring; only `RECOMMENDATION_CANDIDATES` receive the canonical recommendation-path score.
4. PLAN precedes and is immutable before final RECOMMENDATION.
5. Final Top Picks come only from final qualification and may be empty.
6. Core Weekly Swing completes at final Top Picks; CONFIRM availability cannot invalidate that output.
7. CONFIRM may add actionability evidence but cannot create/reorder recommendations.
8. Missing/stale/incomplete CONFIRM data is availability uncertainty, not `NOT_ACTIONABLE` and not core failure.
9. Backtest/OOS proof measures final Top Picks, not a PLAN proxy.
10. Core real-use eligibility requires realistic friction plus core forward-shadow validation; CONFIRM proof is capability-specific.
11. Failure/insufficient required evidence never triggers automatic rule relaxation or fallback promotion.
12. Material strategy change invalidates inherited proof for affected behavior.


## EOD-Only Cross-Lifecycle Invariant

Seluruh core lifecycle `WS-S00..WS-S11` mempertahankan identity **EOD Weekly Swing decision support**.

- `WS-S01..WS-S04` membentuk final Top Picks dari authoritative point-in-time EOD facts; realtime/intraday/orderbook input bukan dependency core selection.
- `WS-S05` tetap optional capability dan boleh tidak tersedia tanpa memblokir core lifecycle.
- `WS-S06..WS-S11` membuktikan EOD recommendation dengan conservative modeled execution; proof tidak boleh mengubah product menjadi orderbook/intraday strategy demi mengejar exact fill.
- Future realtime/intraday/orderbook enhancement hanya boleh masuk melalui explicit strategy/capability revision dan separately identified proof; existing EOD proof tidak boleh dicampur dengan enhancement tersebut secara diam-diam.

## Upstream Market-Data Dependency Routing Invariant

Market-fact ownership harus tetap konsisten dari runtime sampai seluruh proof lifecycle.

- `WS-S01` adalah binding point untuk seluruh authoritative market facts yang diperlukan current/replay identity; downstream stage tidak menjadi alternate Market Data producer.
- Bila `WS-S02..WS-S11` menemukan market fact required yang tidak ada/invalid pada bound producer contract, stage tersebut **MUST** route explicit `UPSTREAM_MARKET_DATA_DEPENDENCY_GAP` kembali ke dependency `WS-S01` dan tidak boleh membuat local replacement.
- Run-level required fact/read identity yang missing memblokir pembentukan new current PLAN untuk affected date; ticker-level required fact hanya memblokir affected ticker path sesuai candidate-state semantics.
- Historical/OOS/shadow proof yang kehilangan required market fact menghasilkan missing/insufficient evidence untuk affected proof path, bukan reconstructed fact dari raw/internal/current state.
- Optional market context yang tidak tersedia hanya menonaktifkan behavior/capability yang benar-benar bergantung padanya dan tidak boleh memicu local derivation atau memperluas required core set secara diam-diam.
- Dependency baru dianggap resolved ketika producer-facing contract menyediakan fact beserta semantic/identity/point-in-time behavior yang diperlukan dan current Watchlist baseline mengikat contract tersebut; keberadaan data di internal table saja tidak cukup.

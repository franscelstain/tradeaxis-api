# Watchlist Weekly Swing — End-to-End Strategy Lifecycle

## Purpose

Dokumen ini adalah canonical orchestration owner untuk **urutan end-to-end Weekly Swing strategy**. Ia menetapkan dependency, handoff, valid stop condition, dan proof path dari trusted Market Data sampai production-use eligibility.

Stage `WS-S00` sampai `WS-S11` membentuk satu authoritative lifecycle sequence.

## End-to-End Objective

Weekly Swing harus mengubah trusted Market Data menjadi decision-support yang dapat dipakai secara nyata melalui alur:

`trusted Market Data → eligible candidates → immutable PLAN → qualified TOP PICKS → D+1 CONFIRM → manual buy decision support`

Strategy baru boleh dipertimbangkan untuk real use setelah alur recommendation yang sama mempunyai proof:

`IS PASS → untouched OOS PASS → adverse-friction PASS → forward-shadow PASS → production-use review`

Tidak ada stage yang boleh dilewati hanya karena hasil stage sebelumnya terlihat baik.

## Canonical Stage Map

| Stage | Purpose | Required input | Normative output | Exit condition |
|---|---|---|---|---|
| `WS-S00` | lock product objective, scope, success meaning, dan boundary | Weekly Swing product intent | frozen Weekly Swing scope + Top Picks meaning | tidak ada ambiguity tentang apa yang direkomendasikan dan apa yang out-of-scope |
| `WS-S01` | bind trusted same-date market facts | authoritative point-in-time Market Data | evaluation-ready market context atau fail-closed outcome | seluruh required market facts tersedia/valid untuk candidate path |
| `WS-S02` | determine absolute eligibility dan candidate state | `WS-S01` market context | `RECOMMENDATION_CANDIDATES`, `WATCH_ONLY`, `AVOID` | setiap ticker mempunyai deterministic state; tidak ada forced candidate quota |
| `WS-S03` | score/order recommendation candidates dan freeze trade plan | `RECOMMENDATION_CANDIDATES` + active strategy identity | immutable PLAN dengan `score_total`, ordering, entry reference, dan predeclared risk/exit plan | PLAN complete dan immutable untuk `trade_date` |
| `WS-S04` | apply final quality qualification dan form ranked Top Picks | immutable PLAN | final `TOP_PICKS` rank `1..N`, termasuk valid `N=0` | seluruh dan hanya qualified candidates menjadi ranked Top Picks |
| `WS-S05` | determine current D+1 actionability | final Top Pick + valid current-entry snapshot | `ACTIONABLE` atau `NOT_ACTIONABLE` | current-entry decision state tersedia tanpa mengubah EOD recommendation history |
| `WS-S06` | define causal historical evaluation model | frozen strategy identity + historical authoritative Market Data | reproducible executable Top-Pick trade outcomes dan calibration dataset | exact final recommendation can be replayed and evaluated net of baseline friction |
| `WS-S07` | establish IS sufficiency dan freeze one best-IS binding | `WS-S06` IS outcomes | `IS PASS + frozen winner`, `IS FAIL`, atau `INSUFFICIENT EVIDENCE` | hanya one frozen binding boleh diteruskan bila seluruh IS floor lulus |
| `WS-S08` | test frozen winner on untouched OOS | frozen best-IS binding + untouched OOS suffix | OOS verdict | sample, return, downside, stability, dan ranking-usefulness OOS gates lulus |
| `WS-S09` | test edge under worse realistic friction | exact frozen OOS recommendation set | adverse-friction verdict | positive net edge tetap bertahan pada stress profile |
| `WS-S10` | validate intended live information flow | exact frozen strategy + forward-only Market Data + D+1 CONFIRM | full-flow shadow verdict | minimum duration/sample + actionable-return + no-leakage gates lulus |
| `WS-S11` | determine whether exact strategy identity may enter production-use review | PASS outcomes from `WS-S07..WS-S10` | production-use eligible-for-review atau rejected/not-ready | all proof stages PASS; no automatic deployment/execution authority |

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

**Consumes:** one authoritative Market Data publication/read context untuk `asof_eod_date`.

**Must establish:**
- point-in-time correctness;
- same-date coherent facts;
- required active fields valid/usable;
- temporal listing/trading status eligible.

**Produces:** evaluation-ready market context atau fail-closed ticker/run outcome.

**May advance when:** required market facts untuk candidate path dapat dipercaya tanpa Watchlist mengarang atau menghitung ulang upstream truth.

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

### WS-S05 — D+1 CONFIRM Actionability

**Consumes:** final Top Pick and valid current-entry snapshot during canonical entry window. The D+1 confirmation snapshot is a decision-time input distinct from the EOD snapshot used to form the recommendation and cannot be used retroactively in EOD scoring/ranking.

**Must check:** freshness, current disqualifying state, allowed price drift/entry band, and trade-plan validity.

**Produces:**
- `ACTIONABLE`; or
- `NOT_ACTIONABLE`.

CONFIRM cannot add a ticker, rescore, rerank, or rewrite EOD recommendation history.

**Runtime stop conditions:**
- no Top Picks → valid `NO QUALIFIED TOP PICKS`;
- Top Pick not actionable → valid `DO NOT ENTER NOW` for that ticker;
- expired entry window → no automatic carry-forward.

### WS-S06 — Historical Evaluation Model

**Consumes:** exact frozen EOD selection/trade-plan semantics and historical point-in-time Market Data.

**Must replay:** candidate eligibility → PLAN → final Top Picks → causal executable entry/exit → realistic net return.

Only final Top Picks are canonical evaluated recommendations. Canonical historical evaluation proves EOD recommendation/ranking edge; it does not fabricate unavailable D+1 CONFIRM observations. CONFIRM full-flow actionability is proven in `WS-S10` forward shadow using actually available decision-time information.

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

### WS-S10 — Forward Shadow Full Flow

**Consumes:** exact frozen strategy identity using only information actually available forward in time.

**Must observe:**
`Market Data → PLAN → TOP PICKS → D+1 CONFIRM → ACTIONABLE/NOT_ACTIONABLE → causal realized outcome`

No automated trade is required or permitted by this strategy stage.

**Produces:** forward-shadow PASS/FAIL/INSUFFICIENT verdict.

### WS-S11 — Production-Use Boundary

Production-use review may begin only when the **same material strategy identity** has:

`IS PASS + OOS PASS + adverse-friction PASS + full-flow shadow PASS`

This stage does not authorize broker execution, portfolio management, or automatic deployment. It only establishes that the decision-support strategy has sufficient proof to be considered for real user-facing use.

## Canonical Stop / Failure Routing

The lifecycle must fail closed:

1. invalid/untrusted Market Data → do not create recommendation from affected path;
2. zero eligible candidates → `NO QUALIFIED TOP PICKS`;
3. zero final qualified candidates → `NO QUALIFIED TOP PICKS`;
4. Top Pick fails CONFIRM → `NOT_ACTIONABLE`, no fallback ticker promotion;
5. insufficient IS/OOS/shadow sample → `INSUFFICIENT EVIDENCE`, not PASS;
6. any proof gate fails → production-use path stops;
7. material strategy change after proof → proof identity resets and affected stages must be rerun.

## Identity Continuity Rule

Runtime and proof are comparable only when the material identity remains bound across:
- candidate eligibility semantics;
- score transforms/weights;
- final recommendation qualification/ranking;
- entry/exit/horizon model;
- transaction-cost/slippage profile for the evaluated proof;
- CONFIRM semantics for full-flow shadow;
- Market Data knowledge/publication identity where required.

A material change creates a new proof identity. Historical PASS cannot be administratively inherited by behavior that was not actually tested.

## End-to-End Completion Definition

Weekly Swing strategy is **runtime-complete** when `WS-S00..WS-S05` define an unambiguous deterministic path for every valid trade date, including empty/no-actionable outcomes.

Weekly Swing strategy is **proof-complete** only when `WS-S06..WS-S11` establish that the exact runtime behavior has sufficient IS, untouched OOS, friction robustness, and forward-shadow evidence.

Downstream realization of Weekly Swing must preserve this stage ordering and handoff semantics; a different realization structure cannot change the strategy lifecycle.

## Final Invariants

1. Stage IDs `WS-S00..WS-S11` are the authoritative end-to-end sequence.
2. Absolute quality and data validity precede scoring/ranking.
3. Candidate classification precedes scoring; only `RECOMMENDATION_CANDIDATES` receive the canonical recommendation-path score.
4. PLAN precedes and is immutable before final RECOMMENDATION.
5. Final Top Picks come only from final qualification and may be empty.
6. CONFIRM may restrict actionability but cannot create/reorder recommendations.
7. Backtest/OOS proof measures final Top Picks, not a PLAN proxy.
8. Real-use eligibility requires realistic friction plus forward full-flow validation.
9. Failure/insufficient evidence never triggers automatic rule relaxation or fallback promotion.
10. Material strategy change invalidates inherited proof for affected behavior.

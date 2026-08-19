# START HERE — Building Watchlist Weekly Swing

## Why This Document Exists

Dokumen ini adalah **pintu masuk pertama** untuk siapa pun yang baru membaca atau membangun Watchlist.

Jika tujuan Anda adalah memahami, mengimplementasikan, menguji, atau melanjutkan Weekly Swing, **mulai dari file ini dan ikuti urutan yang ditetapkan di bawah**. Jangan menentukan urutan sendiri dari nama file, nomor campaign lama, tracker historis, atau hasil audit terdahulu.

Current product scope hanya:

`Watchlist → Weekly Swing → qualified ranked Top Picks → manual buy decision support`

Optional enhancement:

`Top Pick → optional D+1 CONFIRM → current actionability`

Watchlist bukan broker execution engine, portfolio manager, multi-strategy platform, atau pengganti Market Data.

---

## 1. One Authoritative Path

Urutan resmi pembangunan berasal dari lifecycle strategy:

`WS-S00 → WS-S01 → WS-S02 → WS-S03 → WS-S04 → WS-S06 → WS-S07 → WS-S08 → WS-S09 → WS-S10 → WS-S11`

`WS-S05 CONFIRM` adalah **optional branch** dari `WS-S04` dan bukan prerequisite core runtime/proof.

Interpretasi sederhananya:

1. pahami apa yang ingin dibangun;
2. bind data pasar yang terpercaya;
3. tentukan ticker yang layak dievaluasi;
4. bentuk PLAN yang deterministic dan immutable;
5. pilih dan ranking semua qualified Top Picks;
6. buktikan kualitas recommendation secara historis dan forward;
7. baru review apakah exact strategy identity layak digunakan nyata;
8. CONFIRM dapat ditambahkan kapan data decision-time tersedia tanpa memblokir core system.

Lifecycle strategy authoritative: [`strategy/WS_END_TO_END_STRATEGY_LIFECYCLE.md`](strategy/WS_END_TO_END_STRATEGY_LIFECYCLE.md).

---

## 2. Before Reading Anything Else — Documentation Authority

Gunakan urutan authority berikut:

1. **Governance** — menentukan bagaimana dokumen dibaca, dicatat, diubah, dikoreksi, dan disupersede.
2. **Strategy** — menjawab *apa yang harus dilakukan Weekly Swing*.
3. **Implementation** — menjawab *bagaimana software memenuhi strategy*.
4. **Research** — eksperimen/candidate/preregistration; bukan current behavior.
5. **Evidence** — hasil aktual; bukan pembuat strategy baru.
6. **Findings** — masalah/insight yang ditemukan.
7. **Decisions** — alasan formal menerima/menolak perubahan; issued decision immutable.
8. **History** — superseded/migration/historical records; immutable.

Jangan menggunakan dokumen research, evidence, decision lama, atau history sebagai current strategy hanya karena isinya lebih detail.

Governance minimum yang perlu dipahami sebelum mengubah dokumen:

- [`governance/DOCUMENTATION_ARCHITECTURE.md`](governance/DOCUMENTATION_ARCHITECTURE.md)
- [`governance/DOCUMENT_RECORDING_STANDARD.md`](governance/DOCUMENT_RECORDING_STANDARD.md)
- [`governance/STAGE_EXECUTION_AND_REWORK_STANDARD.md`](governance/STAGE_EXECUTION_AND_REWORK_STANDARD.md)
- [`governance/IMPLEMENTATION_RESIDUE_AND_CONFORMANCE_STANDARD.md`](governance/IMPLEMENTATION_RESIDUE_AND_CONFORMANCE_STANDARD.md)
- [`governance/DOCUMENT_CHANGE_POLICY.md`](governance/DOCUMENT_CHANGE_POLICY.md)
- [`governance/WATCHLIST_DOCUMENT_AUTHORITY.md`](governance/WATCHLIST_DOCUMENT_AUTHORITY.md)

**Recording rule:** final evidence, issued decision, locked research, dan history tidak boleh ditulis ulang. Implementation boleh berubah tetapi material semantic/contract change harus traceable melalui `DOCUMENT_CHANGE_LOG.md`, tests/evidence, dan status/contract tracker.

---


**Recurring implementation rule:** setiap build/rerun yang menyentuh behavior atau proof wajib melakukan residue/conformance check. Jangan menghapus semua legacy identifier; classify berdasarkan reachability dan semantic impact. `HARMFUL_RESIDUE` memblokir implementation-stage `DONE`; controlled compatibility/history boleh tetap bila terisolasi dan terbukti.

---

# PART I — READ THE STRATEGY LIKE A BOOK

Bagian ini adalah **urutan baca wajib dari halaman pertama sampai halaman terakhir strategy**. Jangan melompat langsung ke implementation sebelum Bab 1–10 dipahami; proof chapters dapat dibaca setelah runtime behavior jelas.

## Chapter 1 — Scope, Success, and Hard Boundary

**Read:** [`strategy/WS_SCOPE_AND_SUCCESS_CRITERIA.md`](strategy/WS_SCOPE_AND_SUCCESS_CRITERIA.md)

**Question answered:** Sistem apa yang sedang dibangun, apa arti berhasil, dan apa yang tidak boleh masuk scope?

**You must understand before continuing:**
- active strategy hanya Weekly Swing;
- output adalah decision-support;
- tujuan adalah positive expected net return setelah realistic friction dengan downside terkendali, bukan guaranteed profit;
- final Top Picks boleh kosong;
- execution/portfolio lifecycle tetap out-of-scope.

**Exit:** Anda dapat menjelaskan produk dalam satu kalimat tanpa menyebut C-number atau implementation class.

## Chapter 2 — Product Objective and Layer Meaning

**Read:** [`strategy/WS_PRODUCT_OBJECTIVE_AND_LAYERS.md`](strategy/WS_PRODUCT_OBJECTIVE_AND_LAYERS.md)

**Question answered:** PLAN, RECOMMENDATION, TOP PICKS, dan CONFIRM masing-masing berarti apa?

**Exit:** Tidak ada ambiguity antara PLAN candidate dan final Top Picks.

## Chapter 3 — End-to-End Lifecycle

**Read:** [`strategy/WS_END_TO_END_STRATEGY_LIFECYCLE.md`](strategy/WS_END_TO_END_STRATEGY_LIFECYCLE.md)

**Question answered:** Stage apa yang required, optional, proof-only, dan apa handoff antar-stage?

**Exit:** Anda dapat menggambar `WS-S00..WS-S11` dan menjelaskan bahwa `WS-S05` optional.

## Chapter 4 — Market Data Intake Boundary

**Read:** [`strategy/WS_MARKET_DATA_INPUT_REQUIREMENTS.md`](strategy/WS_MARKET_DATA_INPUT_REQUIREMENTS.md)

**Question answered:** Fakta apa yang dimiliki Market Data, fakta apa yang dibutuhkan Weekly Swing, dan bagaimana Watchlist bereaksi bila field/readiness tidak valid?

**Key rule:** Market Data owns facts; Watchlist owns decision policy.

**Exit:** Anda tahu bahwa current PLAN membutuhkan producer-facing read product yang `READABLE + FRESH + same effective trade date`, dan tidak boleh membuat direct-table shortcut/recompute fakta Market Data.

## Chapter 5 — Runtime Flow

**Read:** [`strategy/WS_RUNTIME_FLOW.md`](strategy/WS_RUNTIME_FLOW.md)

**Question answered:** Bagaimana trusted input berubah menjadi PLAN, Recommendation/Top Picks, dan optional CONFIRM?

**Exit:** Anda tahu core runtime selesai di final Top Picks, bukan di CONFIRM.

## Chapter 6 — Candidate Eligibility and Setup

**Read:** [`strategy/WS_CANDIDATE_ELIGIBILITY_AND_SETUP.md`](strategy/WS_CANDIDATE_ELIGIBILITY_AND_SETUP.md)

**Question answered:** Kondisi absolut apa yang harus dipenuhi sebelum ticker boleh masuk jalur recommendation?

**Exit:** Missing active required feature tidak di-zero-fill dan tidak diselamatkan oleh relative ranking.

## Chapter 7 — Candidate Classification

**Read:** [`strategy/WS_CANDIDATE_CLASSIFICATION.md`](strategy/WS_CANDIDATE_CLASSIFICATION.md)

**Question answered:** Bagaimana setiap evaluated ticker diberi state deterministic sebelum scoring?

**Expected states:**
- `RECOMMENDATION_CANDIDATES`
- `WATCH_ONLY`
- `AVOID`

**Exit:** Hanya `RECOMMENDATION_CANDIDATES` yang boleh lanjut ke score/recommendation path.

## Chapter 8 — PLAN Scoring and Trade Plan

**Read:** [`strategy/WS_PLAN_SCORING_AND_TRADE_PLAN.md`](strategy/WS_PLAN_SCORING_AND_TRADE_PLAN.md)

**Question answered:** Bagaimana candidate dinilai, diurutkan, diberi entry/risk/exit plan, lalu PLAN dibekukan?

**Exit:** PLAN deterministic, replayable, dan immutable sebelum Recommendation dibentuk.

## Chapter 9 — Recommendation Meaning

**Read:** [`strategy/WS_TOP_PICKS_RECOMMENDATION.md`](strategy/WS_TOP_PICKS_RECOMMENDATION.md)

**Question answered:** Apa yang membuat recommendation berbeda dari candidate PLAN?

**Exit:** Anda memahami bahwa tidak ada fixed quota dan valid result dapat `NO QUALIFIED TOP PICKS`.

## Chapter 10 — Qualification and Ranking Top Picks

**Read:** [`strategy/WS_TOP_PICKS_QUALIFICATION_AND_RANKING.md`](strategy/WS_TOP_PICKS_QUALIFICATION_AND_RANKING.md)

**Question answered:** Siapa yang benar-benar menjadi Top Pick dan mengapa rank #1 berada di atas rank #2?

**Exit:** Semua dan hanya candidate yang melewati final quality gates menjadi `TOP_PICKS`, ranking deterministic, dan capital tidak mengubah membership/rank.

### CORE RUNTIME MILESTONE

Setelah Chapter 1–10 dan implementation `WS-S00..WS-S04` selesai, core Watchlist secara fungsional harus dapat menghasilkan:

`trusted Market Data → PLAN → qualified ranked TOP PICKS (0..N)`

Ini adalah **core product completion point**.

## Chapter 11 — Optional D+1 CONFIRM

**Read:** [`strategy/WS_D1_CONFIRM_ACTIONABILITY.md`](strategy/WS_D1_CONFIRM_ACTIONABILITY.md)

**Question answered:** Bila valid decision-time data tersedia, apakah Top Pick masih actionable pada entry window?

**Important:** Chapter ini optional untuk core construction.

Valid availability/actionability states harus membedakan:
- `NOT_REQUESTED`
- `UNAVAILABLE_RETRYABLE`
- `ACTIONABLE`
- `NOT_ACTIONABLE`
- `EXPIRED_UNCONFIRMED`

Missing/stale/incomplete data bukan `NOT_ACTIONABLE` dan bukan core failure.

## Chapter 12 — Historical Evaluation Model

**Read:** [`strategy/WS_HISTORICAL_EVALUATION_STRATEGY.md`](strategy/WS_HISTORICAL_EVALUATION_STRATEGY.md)

**Question answered:** Bagaimana exact final Top Picks direplay secara causal tanpa future leakage dan dinilai setelah friction?

**Exit:** Historical evaluator mengukur final recommendations, bukan PLAN proxy.

## Chapter 13 — IS Sufficiency and Winner Freeze

**Read:** [`strategy/WS_IS_SUFFICIENCY_AND_WINNER_FREEZE.md`](strategy/WS_IS_SUFFICIENCY_AND_WINNER_FREEZE.md)

**Question answered:** Kapan evidence IS cukup dan kapan tepat satu best-IS identity boleh dibekukan?

**Exit:** hanya `IS PASS + exactly one frozen winner` boleh diteruskan ke untouched OOS.

## Chapter 14 — OOS, Friction, Shadow, Production Boundary

**Read:** [`strategy/WS_OOS_STRESS_SHADOW_AND_PRODUCTION_PROOF.md`](strategy/WS_OOS_STRESS_SHADOW_AND_PRODUCTION_PROOF.md)

**Question answered:** Bagaimana winner dibuktikan pada untouched OOS, adverse friction, forward shadow, lalu dinilai untuk real-use review?

**Exit:** Anda dapat membedakan `PASS`, `FAIL`, `INSUFFICIENT EVIDENCE`, core proof, dan optional CONFIRM proof.

### STRATEGY BOOK END

Jika Chapter 1–14 telah dipahami, Anda telah membaca current Weekly Swing strategy dari awal sampai akhir. Baru setelah itu gunakan implementation layer untuk membangun software.

---


## MANDATORY STRATEGY COVERAGE MATRIX

Sebelum coding stage-by-stage, baca:

- [`governance/STRATEGY_IMPLEMENTATION_TRACEABILITY_STANDARD.md`](governance/STRATEGY_IMPLEMENTATION_TRACEABILITY_STANDARD.md);
- [`governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv`](governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv).

Matrix adalah backlog coverage rule-by-rule. Pada setiap `WS-Bxx`, programmer harus memfilter row `verification_build_stage` stage tersebut dan memastikan tidak ada mandatory rule yang hilang dari mapping/test/evidence.

**Stage `DONE` bukan pengganti matrix.** Final 100% mandatory strategy coverage hanya sah ketika seluruh active mandatory/conditional row `SATISFIED`; optional CONFIRM boleh tetap `OPTIONAL_NOT_REQUESTED`.

# PART II — BUILD THE SYSTEM STEP BY STEP

Detailed technical build sequence berada di:

[`implementation/WS_IMPLEMENTATION_BUILD_SEQUENCE.md`](implementation/WS_IMPLEMENTATION_BUILD_SEQUENCE.md)

Jangan memakai nama file implementation lama sebagai build order. Build order harus mengikuti lifecycle stage.

Ringkasan urutan:

| Build step | Lifecycle | Build target | May proceed when |
|---|---|---|---|
| `WS-B00` | `WS-S00` | lock current strategy/authority before code changes | scope and strategy identity understood |
| `WS-B01` | `WS-S01` | governed Market Data intake boundary | read contract + mapping + fail-closed behavior defined |
| `WS-B02` | support `S01..S04` | align paramset/data model/validator/reason semantics | technical contracts match revised strategy |
| `WS-B03` | `WS-S02` | eligibility + classification | deterministic candidate states tested |
| `WS-B04` | `WS-S03` | PLAN scoring/trade-plan/immutability | PLAN deterministic and immutable |
| `WS-B05` | `WS-S04` | final qualification + ranked Top Picks | valid `0..N` Top Picks tested |
| `WS-B06` | core runtime | API/persistence/composite core delivery | PLAN + Recommendation survive round-trip without semantic drift |
| `WS-B07` | optional `WS-S05` | CONFIRM capability, only if desired/data-source ready | must remain non-blocking; may be deferred indefinitely |
| `WS-B08` | `WS-S06` | historical evaluator + persistence | exact final Top Picks replayable causally |
| `WS-B09` | `WS-S07` | IS evaluator + winner freeze | sufficiency gates + exactly-one-winner behavior tested |
| `WS-B10` | `WS-S08..S09` | untouched OOS + adverse friction | frozen identity preserved, no retuning |
| `WS-B11` | `WS-S10` | forward shadow core flow | forward-only core sample/evidence sufficient |
| `WS-B12` | `WS-S11` | production-use review package | all required core proof stages have explicit verdict |

---

# PART II-B — WHEN A STAGE IS BEING REWORKED OR RESUMED

Before rerunning any `WS-Bxx` stage that has prior attempts, read:

1. [`governance/STAGE_EXECUTION_AND_REWORK_STANDARD.md`](governance/STAGE_EXECUTION_AND_REWORK_STANDARD.md)
2. [`implementation/WS_IMPLEMENTATION_STAGE_REGISTER.md`](implementation/WS_IMPLEMENTATION_STAGE_REGISTER.md)
3. latest attempt evidence linked by the register
4. open/accepted unresolved findings
5. active remediation/decision
6. documentation change log since the prior attempt

Do **not** restart from code or from historical campaign prose.

Key rule:

> Repeated failure does not justify closure. If evidence is still narrowing the problem or a reasonable remedy remains, keep the stage active and record the next testable action.

`DONE` means the declared stage objective/exit criteria were achieved. A proof/evaluation stage may be `DONE` with verdict `FAIL` when producing that valid verdict is itself the stage objective. An implementation stage with unresolved failing acceptance tests is not `DONE`.

If a real external dependency is missing, use `WAITING_VERIFIED_DEPENDENCY` with evidence + resume trigger. If the approach is truly terminally infeasible, closure requires immutable evidence + reviewed decision; never personal judgement alone.

# PART III — HOW TO HANDLE MISSING DATA WITHOUT BREAKING DEVELOPMENT

## A. Missing Live/Runtime Market Data

If runtime producer data is not yet available, software construction may continue using contract-valid fixtures/mocks for module and contract tests.

This permits:
- contract development;
- deterministic unit tests;
- persistence/API implementation;
- negative/failure-path tests.

It does **not** permit claiming:
- real runtime readiness;
- historical proof based on fabricated producer facts;
- production readiness.

Required Market Data facts must eventually come through the governed producer-facing contract.

## B. Missing CONFIRM Data

CONFIRM is different because it is optional.

Missing CONFIRM data:
- never blocks PLAN;
- never blocks Recommendation/Top Picks;
- never blocks core historical proof;
- never blocks core production-use review;
- produces availability state, not core failure.

CONFIRM can be implemented later and can evaluate a still-open entry window when valid data arrives.

## C. Missing Historical Evidence

Code may be complete while proof is incomplete.

Use the correct state:
- implementation complete, proof pending; or
- `INSUFFICIENT EVIDENCE`.

Never weaken gates or fabricate evidence just to complete the lifecycle.

---

# PART IV — WHERE TO WRITE NEW INFORMATION

When building or testing the system:

| What happened | Write/update here |
|---|---|
| strategy behavior genuinely changes due to material evidence | `strategy/` only after finding + evidence + decision |
| technical design/code mapping changes | `implementation/` |
| new experiment/hypothesis | `research/` |
| test/backtest/runtime result | `evidence/` |
| problem discovered | `findings/` |
| formal accept/reject/go/no-go/change decision | `decisions/` |
| old/superseded material | `history/` |
| authority/change rules | `governance/` |

Do not append implementation progress, hashes, test output, or campaign history to canonical strategy documents.

---

# PART V — NEW DEVELOPER CHECKPOINTS

A new programmer should be able to answer these questions in order:

1. **What am I building?** → Chapter 1–2.
2. **What is the stage order?** → Chapter 3.
3. **Where do market facts come from?** → Chapter 4.
4. **How does a ticker enter the candidate path?** → Chapter 6–7.
5. **How is it scored and given a trade plan?** → Chapter 8.
6. **When does it become an actual Top Pick?** → Chapter 9–10.
7. **Does the system require CONFIRM?** → No; Chapter 11 is optional.
8. **How is profitability/robustness proven?** → Chapter 12–14.
9. **What do I implement first?** → `WS-B00`, then follow the build sequence.
10. **Where do I record results/problems?** → Part IV above.

If any answer still requires reading a C-number/history file, stop: the current-authority path is being bypassed.

11. **If I am resuming a stage, what do I read first?** → stage register + re-entry protocol + latest attempt lineage, not random history.

---

# PART VI — DEFINITION OF COMPLETION

## Core software complete

Selain build-stage Definition of Done, seluruh mandatory matrix row yang terkait core implementation harus `SATISFIED` sesuai canonical traceability standard.

At minimum:
- governed Market Data intake exists;
- deterministic eligibility/classification exists;
- immutable PLAN exists;
- final qualified ranked Top Picks `0..N` exists;
- required core contracts/persistence/API/tests are aligned.

CONFIRM is not required.

## Core strategy proof complete

Required:
- historical model valid;
- IS sufficient + one winner frozen;
- untouched OOS PASS;
- adverse-friction PASS;
- core forward-shadow PASS;
- production-use review verdict recorded.

## CONFIRM capability complete

Only if implemented:
- non-blocking availability states work;
- valid decision-time inputs can produce actionability result;
- missing data remains retryable/non-failing;
- capability proof is recorded separately from core proof.

---

## Next File

If you are new to the project, read next:

[`strategy/WS_SCOPE_AND_SUCCESS_CRITERIA.md`](strategy/WS_SCOPE_AND_SUCCESS_CRITERIA.md)

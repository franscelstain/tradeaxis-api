# Weekly Swing — Implementation Build Sequence

## Role

Dokumen ini adalah **implementation orchestration guide**, bukan strategy owner. Ia menerjemahkan canonical lifecycle `WS-S00..WS-S11` menjadi urutan kerja pembangunan software yang dapat diikuti programmer baru.

Jika technical document lama bertentangan dengan canonical strategy, strategy menang dan technical document harus di-align sebelum dipakai sebagai implementation contract.

Start from: [`../../START_HERE.md`](../START_HERE.md).

Canonical strategy lifecycle: [`../../strategy/WS_END_TO_END_STRATEGY_LIFECYCLE.md`](../strategy/WS_END_TO_END_STRATEGY_LIFECYCLE.md).

Current alignment marker: [`STRATEGY_ALIGNMENT_REQUIRED.md`](STRATEGY_ALIGNMENT_REQUIRED.md).

---

## Build Discipline

Untuk setiap build step di bawah, gunakan urutan ini:

1. **Read recording governance** — [`../governance/DOCUMENT_RECORDING_STANDARD.md`](../governance/DOCUMENT_RECORDING_STANDARD.md).
2. **Read stage execution/re-entry governance** — [`../governance/STAGE_EXECUTION_AND_REWORK_STANDARD.md`](../governance/STAGE_EXECUTION_AND_REWORK_STANDARD.md).
3. **Read recurring residue/conformance governance** — [`../governance/IMPLEMENTATION_RESIDUE_AND_CONFORMANCE_STANDARD.md`](../governance/IMPLEMENTATION_RESIDUE_AND_CONFORMANCE_STANDARD.md).
4. **Read current stage register** — [`WS_IMPLEMENTATION_STAGE_REGISTER.md`](WS_IMPLEMENTATION_STAGE_REGISTER.md).
5. Jika stage pernah dicoba, jalankan mandatory re-entry protocol termasuk membaca known residue/evidence sebelum code change.
6. **Read strategy owner** — jangan mulai dari code/schema.
7. **Read current implementation guards/contracts** — identifikasi semantic drift lama.
8. **Define attempt objective/hypothesis dan perubahan versus attempt sebelumnya**.
9. **Implement deterministic domain/application behavior**.
10. **Add negative/fail-closed tests** sebelum happy-path dianggap cukup.
11. **Add persistence/API translation** tanpa mengubah semantics.
12. **Run functional + conformance tests** terhadap stage output/exit criteria.
13. **Run Residue & Conformance Check** pada impacted code/config/schema/API/fixture/test/runtime/proof paths; classify residue berdasarkan reachability + semantic impact.
14. Jika `HARMFUL_RESIDUE` ditemukan, buat/remediate finding dan kembali ke implementation/validation; jangan lanjut ke `DONE`.
15. **Close the attempt** dengan immutable evidence, termasuk residue scope/classification/conformance verdict, attempt outcome, convergence, learned facts, do-not-repeat, dan next action.
16. **Update stage register** + implementation status/contract tracker append-oriented.
17. **Record material documentation/contract change** di [`../governance/DOCUMENT_CHANGE_LOG.md`](../governance/DOCUMENT_CHANGE_LOG.md).
18. Tentukan stage state berikutnya berdasarkan evidence—bukan berdasarkan jumlah gagal.

Technical implementation boleh dikembangkan dengan contract fixtures bila external/runtime data belum tersedia. Namun runtime/proof status harus tetap jujur dan tidak boleh disimulasikan sebagai production evidence.

### Mandatory recurring Residue & Conformance Gate

Sebelum implementation stage dapat `DONE`, evidence wajib menunjukkan salah satu:

- `CONFORMANT_NO_HARMFUL_RESIDUE_FOUND`; atau
- `CONFORMANT_WITH_CONTROLLED_COMPATIBILITY`.

`NON_CONFORMANT_HARMFUL_RESIDUE_OPEN` dan `INCONCLUSIVE_RESIDUE_EVIDENCE` menjaga stage tetap aktif/remediation. Grep/search identifier lama hanya discovery aid; conformance harus menilai reachability dan behavior.

### Meaning of `DONE`

`DONE` berarti **declared objective + exit criteria stage tercapai**.

- Untuk implementation stage, test/contract yang masih gagal **atau harmful residue masih unresolved** berarti stage belum `DONE`.
- Untuk evaluation/proof stage, stage dapat `DONE` dengan verdict `FAIL` bila objective stage memang menjalankan evaluasi secara sah dan menghasilkan valid verdict. Verdict `FAIL` kemudian menghentikan downstream proof/production path sesuai strategy.
- Repeated failure, elapsed time, fatigue, atau personal judgement tidak pernah mengubah stage menjadi `DONE`.

### Non-PASS work that is still progressing

Gunakan active state yang sesuai:

- `REMEDIATION_IN_PROGRESS` — masih ada testable remedy;
- `WAITING_VERIFIED_DEPENDENCY` — dependency objektif belum tersedia;
- `VALIDATION` — candidate fix sudah ada dan sedang dibuktikan.

Jika failure masih menghasilkan `Convergence = IMPROVING`, stage wajib tetap aktif.

### Terminal unresolved / successor

`CLOSED_UNRESOLVED_WITH_EVIDENCE`, `SUPERSEDED_BY_SUCCESSOR`, atau `SUPERSEDED_BY_DECOMPOSITION` hanya sah mengikuti burden of proof dan reviewed decision pada stage execution standard. Jangan membuat successor hanya untuk mengganti nomor stage atau menghindari acceptance criteria.

### Re-entry shortcut is forbidden

Jika stage pernah mempunyai attempt, jangan membaca seluruh history secara acak dan jangan langsung rerun. Baca current lineage dari stage register → latest attempt evidence → open findings → active decision/remediation → change log. History hanya dibuka bila direferensikan current record.

---

# WS-B00 — Orientation and Strategy Lock

**Maps to:** `WS-S00`.

### Read first
- [`../../strategy/WS_SCOPE_AND_SUCCESS_CRITERIA.md`](../strategy/WS_SCOPE_AND_SUCCESS_CRITERIA.md)
- [`../../strategy/WS_PRODUCT_OBJECTIVE_AND_LAYERS.md`](../strategy/WS_PRODUCT_OBJECTIVE_AND_LAYERS.md)
- [`../../strategy/WS_END_TO_END_STRATEGY_LIFECYCLE.md`](../strategy/WS_END_TO_END_STRATEGY_LIFECYCLE.md)
- [`STRATEGY_ALIGNMENT_REQUIRED.md`](STRATEGY_ALIGNMENT_REQUIRED.md)

### Goal
Pastikan developer tahu current behavior sebelum menyentuh technical contract/code.

### Deliverables
- stage-to-module impact list;
- list technical docs/code paths yang belum conformant;
- no assumption bahwa historical C/R/B/P artifact adalah current contract.

### Do not proceed if
- `TOP_PICKS` masih dipahami sebagai PLAN group;
- CONFIRM masih dianggap core prerequisite;
- fixed recommendation quota masih dianggap canonical;
- direct Market Data table read masih dianggap normal intake.

### Definition of Done
Current strategy semantics dan impacted technical surfaces telah dipahami/ditandai.

---

# WS-B01 — Market Data Intake Adapter / Boundary

**Maps to:** `WS-S01`.

### Strategy owners
- [`../../strategy/WS_MARKET_DATA_INPUT_REQUIREMENTS.md`](../strategy/WS_MARKET_DATA_INPUT_REQUIREMENTS.md)
- [`../../strategy/WS_RUNTIME_FLOW.md`](../strategy/WS_RUNTIME_FLOW.md)

### Technical starting points
- [`MARKET_DATA_INTAKE_IMPLEMENTATION_CONTRACT.md`](MARKET_DATA_INTAKE_IMPLEMENTATION_CONTRACT.md)
- [`guidance/01_WS_IMPLEMENTATION_SCOPE_AND_BOUNDARY.md`](guides/WS_IMPLEMENTATION_SCOPE_AND_BOUNDARY.md)
- [`guidance/05A_WS_CANONICAL_FIELD_MATRIX.md`](guides/WS_CANONICAL_FIELD_MATRIX.md)

### Build
Implement one governed producer-facing intake boundary that carries:
- requested/effective trade date;
- publication/read-model/config/formula identity required by producer contract;
- readiness/freshness;
- stable ticker/listing identity;
- required EOD facts/indicators;
- null/reason/data-usability semantics.

### Mandatory negative paths
- unreadable publication;
- stale/prior-date data for a new PLAN;
- missing required run-level identity;
- missing required active ticker field;
- attempted direct-table/recompute fallback.

### Output
Evaluation-ready market context or explicit no-output/unavailable state.

### Definition of Done
No downstream module needs to know Market Data internal tables or reconstruct producer facts.

---

# WS-B02 — Align Core Technical Contracts and Paramset Identity

**Supports:** `WS-S01..WS-S04` and proof identity.

### Read/align
- [`contracts/03_WS_DATA_MODEL_MARIADB.md`](contracts/WS_DATA_MODEL_MARIADB.md)
- [`contracts/04_WS_PARAMSET_JSON_CONTRACT.md`](contracts/WS_PARAMSET_JSON_CONTRACT.md)
- [`contracts/05_WS_PARAMETER_REGISTRY_COMPLETE.md`](contracts/WS_PARAMETER_REGISTRY_COMPLETE.md)
- [`contracts/06_WS_PARAMSET_VALIDATOR_SPEC.md`](contracts/WS_PARAMSET_VALIDATOR_SPEC.md)
- [`contracts/07_WS_REASON_CODES_AND_HASH.md`](contracts/WS_REASON_CODES_AND_HASH.md)

### Goal
Pastikan technical representation dapat mengekspresikan current strategy tanpa legacy semantics.

### Must align
- candidate states;
- score component semantics;
- final Top Picks semantics;
- no fixed recommendation quota;
- capital independence;
- Market Data semantic aliases;
- immutable strategy/paramset identity;
- optional CONFIRM states.

### Tests
- required/unknown key validation;
- type/enum validation;
- stable hash/canonical serialization;
- cross-field semantic validation;
- old incompatible semantics rejected or explicitly migrated.

### Definition of Done
Technical contracts dapat menjadi input untuk domain implementation tanpa menerjemahkan ulang strategy secara subjektif.

---

# WS-B03 — Eligibility and Candidate Classification

**Maps to:** `WS-S02`.

### Strategy owners
- [`../../strategy/WS_CANDIDATE_ELIGIBILITY_AND_SETUP.md`](../strategy/WS_CANDIDATE_ELIGIBILITY_AND_SETUP.md)
- [`../../strategy/WS_CANDIDATE_CLASSIFICATION.md`](../strategy/WS_CANDIDATE_CLASSIFICATION.md)

### Technical references to align/use
- [`guidance/02_WS_MODULE_MAPPING.md`](guides/WS_MODULE_MAPPING.md)
- [`guidance/05A_WS_CANONICAL_FIELD_MATRIX.md`](guides/WS_CANONICAL_FIELD_MATRIX.md)
- [`reference/WS_FAILURE_BEHAVIOR_MATRIX.md`](guides/WS_FAILURE_BEHAVIOR_MATRIX.md)

### Build order
1. validate upstream required facts;
2. apply absolute liquidity/executability requirements;
3. apply participation/volume rule;
4. apply volatility/risk rule;
5. apply momentum/setup requirements;
6. apply regime compatibility only when active strategy requires it;
7. assign exactly one deterministic candidate state.

### Required outputs
`RECOMMENDATION_CANDIDATES`, `WATCH_ONLY`, or `AVOID` for every evaluated ticker.

### Mandatory tests
- null required feature;
- boundary threshold values;
- deterministic reason/state;
- no forced quota;
- ticker failing absolute quality cannot be rescued by ranking.

### Definition of Done
Only `RECOMMENDATION_CANDIDATES` can reach scoring path and every exclusion is deterministic/auditable.

---

# WS-B04 — PLAN Scoring, Ordering, Trade Plan, and Immutability

**Maps to:** `WS-S03`.

### Strategy owner
- [`../../strategy/WS_PLAN_SCORING_AND_TRADE_PLAN.md`](../strategy/WS_PLAN_SCORING_AND_TRADE_PLAN.md)

### Technical references to align/use
- [`guidance/02_WS_MODULE_MAPPING.md`](guides/WS_MODULE_MAPPING.md)
- [`guidance/03_WS_RUNTIME_ARTIFACT_FLOW.md`](guides/WS_RUNTIME_ARTIFACT_FLOW.md)
- [`guidance/05_WS_PERSISTENCE_GUIDANCE.md`](guides/WS_PERSISTENCE_GUIDANCE.md)

### Build order
1. consume only recommendation candidates;
2. calculate normalized active components;
3. calculate canonical `score_total`;
4. apply deterministic tie-break/order;
5. derive causal entry reference/band;
6. bind one active risk/exit family;
7. create immutable PLAN identity/artifact.

### Mandatory tests
- ties;
- missing component rejection;
- deterministic rerun/hash;
- entry/exit boundary;
- PLAN cannot be back-mutated after Recommendation.

### Definition of Done
Same input + same strategy identity produces same immutable PLAN.

---

# WS-B05 — Final Recommendation and Ranked Top Picks

**Maps to:** `WS-S04`.

### Strategy owners
- [`../../strategy/WS_TOP_PICKS_RECOMMENDATION.md`](../strategy/WS_TOP_PICKS_RECOMMENDATION.md)
- [`../../strategy/WS_TOP_PICKS_QUALIFICATION_AND_RANKING.md`](../strategy/WS_TOP_PICKS_QUALIFICATION_AND_RANKING.md)

### Technical references to align/use
- [`contracts/23_WS_RECOMMENDATION_INPUT_OUTPUT_CONTRACT.md`](contracts/WS_RECOMMENDATION_INPUT_OUTPUT_CONTRACT.md)
- [`guidance/03_WS_RUNTIME_ARTIFACT_FLOW.md`](guides/WS_RUNTIME_ARTIFACT_FLOW.md)
- [`guidance/04_WS_API_GUIDANCE.md`](guides/WS_API_GUIDANCE.md)
- [`guidance/05_WS_PERSISTENCE_GUIDANCE.md`](guides/WS_PERSISTENCE_GUIDANCE.md)

### Build order
1. consume frozen PLAN only;
2. apply final mandatory qualification gates;
3. apply absolute recommendation quality floor;
4. assign recommendation score from canonical PLAN score semantics;
5. deterministic sort;
6. mark all and only qualified items as Top Picks rank `1..N`;
7. support valid `N=0` without fallback promotion.

### Mandatory tests
- zero qualified candidates;
- one candidate;
- many candidates;
- deterministic ties;
- capital-free vs any presentation capital input yields identical membership/rank;
- non-qualified candidate cannot be promoted to fill a quota.

### Definition of Done
The user-facing ranked list is reproducible, quality-driven, and can legitimately be empty.

### Core runtime milestone
After `WS-B05`, core business behavior exists. Remaining core work is delivery hardening and proof. CONFIRM is not required.

---

# WS-B06 — Core Persistence, API, and Read/Composite Delivery

**Supports:** completed `WS-S01..WS-S04` runtime behavior.

### Technical references
- [`guidance/04_WS_API_GUIDANCE.md`](guides/WS_API_GUIDANCE.md)
- [`guidance/05_WS_PERSISTENCE_GUIDANCE.md`](guides/WS_PERSISTENCE_GUIDANCE.md)
- [`guidance/03_WS_RUNTIME_ARTIFACT_FLOW.md`](guides/WS_RUNTIME_ARTIFACT_FLOW.md)
- [`db/RECOMMENDATION_RUNTIME_SCHEMA.md`](db/RECOMMENDATION_RUNTIME_SCHEMA.md)

### Build
- persist PLAN separately from Recommendation;
- preserve immutable source/reference identity;
- expose PLAN and Recommendation without semantic mutation;
- composite view may aggregate but not recompute/rewrite artifacts;
- no CONFIRM artifact required for core completeness.

### Mandatory tests
- write/read round-trip;
- immutable conflict;
- missing optional CONFIRM;
- empty Top Picks response;
- API/persistence shape consistency;
- no hidden reranking at transport layer.

### Definition of Done
Core output survives persistence/API round-trip with identical strategy meaning.

---

# WS-B07 — Optional CONFIRM Capability

**Maps to:** optional `WS-S05`.

### Read first
- [`CONFIRM_OPTIONAL_NON_BLOCKING_IMPLEMENTATION_GUARD.md`](CONFIRM_OPTIONAL_NON_BLOCKING_IMPLEMENTATION_GUARD.md)
- [`../../strategy/WS_D1_CONFIRM_ACTIONABILITY.md`](../strategy/WS_D1_CONFIRM_ACTIONABILITY.md)

### Important
This step may be **deferred**. Skip directly to `WS-B08` if valid decision-time data/source is unavailable or the feature is not currently needed.

### Build only when desired
- final Top Picks only;
- separate decision-time snapshot source/binding;
- availability state separate from actionability state;
- retry while entry window remains open;
- never rescore/rerank/add recommendations.

### Mandatory tests
- `NOT_REQUESTED`;
- `UNAVAILABLE_RETRYABLE`;
- later retry to `ACTIONABLE` or `NOT_ACTIONABLE` when valid data arrives;
- `EXPIRED_UNCONFIRMED`;
- CONFIRM technical/data error does not fail existing PLAN/Recommendation.

### Definition of Done
Optional capability works without becoming a core dependency.

---

# WS-B08 — Historical Evaluator and Replay Foundation

**Maps to:** `WS-S06`.

### Strategy owner
- [`../../strategy/WS_HISTORICAL_EVALUATION_STRATEGY.md`](../strategy/WS_HISTORICAL_EVALUATION_STRATEGY.md)

### Technical references to align/use
- [`contracts/WS_BACKTEST_EVALUATION_TECHNICAL_CONTRACT.md`](contracts/WS_BACKTEST_EVALUATION_TECHNICAL_CONTRACT.md)
- [`contracts/WS_BACKTEST_PERSISTENCE_AND_UNIVERSE_SCHEMA_CONTRACT.md`](contracts/WS_BACKTEST_PERSISTENCE_AND_UNIVERSE_SCHEMA_CONTRACT.md)
- [`verification/14_WS_BT_COVERAGE_MATRIX_LOCKED.md`](tests/WS_BT_COVERAGE_MATRIX_LOCKED.md)
- [`verification/15_WS_UNIVERSE_EQUIVALENCE_CONTRACT_LOCKED.md`](tests/WS_UNIVERSE_EQUIVALENCE_CONTRACT_LOCKED.md)

### Build order
1. bind historical exact/as-known Market Data identity;
2. reconstruct temporal eligible universe without survivorship leakage;
3. replay `S02 → S03 → S04` exactly;
4. evaluate only final Top Picks;
5. simulate causal executable entry/exit;
6. apply baseline realistic friction;
7. persist recommendation/trade outcome lineage.

### Mandatory tests
- no lookahead;
- corporate-action/price basis consistency;
- temporal universe/listing;
- no-recommendation dates;
- non-executable trades;
- exact rerun reproducibility.

### Definition of Done
Historical evaluator can reproduce final Top Pick decisions and outcomes without reading future state.

---

# WS-B09 — IS Sufficiency and One-Winner Freeze

**Maps to:** `WS-S07`.

### Strategy owner
- [`../../strategy/WS_IS_SUFFICIENCY_AND_WINNER_FREEZE.md`](../strategy/WS_IS_SUFFICIENCY_AND_WINNER_FREEZE.md)

### Build
- evaluate preregistered candidate identities on IS only;
- compute required sample/return/downside/stability/ranking metrics;
- distinguish `PASS`, `FAIL`, `INSUFFICIENT EVIDENCE`;
- select/freeze exactly one best-IS identity only after all mandatory floors pass.

### Mandatory tests
- zero passing candidate;
- multiple pass candidates with deterministic winner;
- insufficient sample;
- tie handling;
- frozen identity immutable after selection.

### Definition of Done
IS evaluation menghasilkan valid terminal verdict. Jika verdict `PASS`, exactly one winner wajib frozen untuk OOS. Jika verdict `FAIL`, stage evaluation dapat `DONE` tetapi OOS path berhenti. `INSUFFICIENT EVIDENCE` tetap active bila evidence masih dapat dikumpulkan secara wajar.

---

# WS-B10 — Untouched OOS and Adverse-Friction Robustness

**Maps to:** `WS-S08` and `WS-S09`.

### Strategy owner
- [`../../strategy/WS_OOS_STRESS_SHADOW_AND_PRODUCTION_PROOF.md`](../strategy/WS_OOS_STRESS_SHADOW_AND_PRODUCTION_PROOF.md)

### Technical references to align/use
- [`contracts/WS_BACKTEST_OOS_RUNTIME_IMPLEMENTATION_CONTRACT.md`](contracts/WS_BACKTEST_OOS_RUNTIME_IMPLEMENTATION_CONTRACT.md)
- [`evidence_contracts/18_WS_BACKTEST_ARTIFACT_MANIFEST_LOCKED.md`](contracts/WS_BACKTEST_ARTIFACT_MANIFEST_LOCKED.md)

### Build/order
1. bind frozen best-IS identity;
2. lock untouched chronological OOS suffix;
3. run without retuning;
4. compute OOS quality/ranking metrics;
5. if OOS passes, rerun same recommendations under adverse friction only;
6. do not change membership/rank to improve stress result.

### Hard stop
Any post-OOS strategy retuning creates a new identity and invalidates the proof chain.

### Definition of Done
Valid untouched OOS verdict tersedia untuk exact frozen strategy. Adverse-friction verdict wajib hanya bila OOS `PASS`; jika OOS valid `FAIL`, stage dapat `DONE` dengan OOS verdict `FAIL` dan stress/proof path berhenti. Retuning tetap dilarang.

---

# WS-B11 — Core Forward Shadow

**Maps to:** `WS-S10`.

### Goal
Validate the same frozen core strategy using only information available forward in real time, without automated trade execution.

### Observe
`Market Data → PLAN → TOP PICKS → causal realized outcome`

Optional CONFIRM observation can be added when valid data exists but cannot affect core-shadow completion.

### Required evidence
- duration/sample sufficiency;
- exact strategy identity;
- no leakage/retuning;
- Top Picks output and realized outcomes;
- operational failures separated from strategy failures.

### Definition of Done
Core shadow mempunyai sample/evidence yang cukup untuk valid `PASS` atau `FAIL` verdict. `INSUFFICIENT EVIDENCE` **bukan terminal DONE** selama forward evidence masih dapat dikumpulkan secara wajar; stage tetap active/waiting dengan explicit resume trigger.

---

# WS-B12 — Production-Use Review Package

**Maps to:** `WS-S11`.

### Required inputs
- IS verdict + frozen identity;
- untouched OOS verdict;
- adverse-friction verdict;
- core forward-shadow verdict;
- exact identity continuity evidence.

### Review output
- `ELIGIBLE_FOR_PRODUCTION_USE_REVIEW`; or
- rejected/not-ready with explicit reason.

This does not authorize broker execution or portfolio management.

Optional CONFIRM status is reported separately (`PROVEN`, `UNPROVEN`, or `EVIDENCE_INSUFFICIENT`).

### Definition of Done
Anyone reviewing the package can trace the exact strategy from Market Data identity → recommendation → proof without relying on historical campaign prose.

---

# Global Stop Rules

Do not advance by weakening rules when:
- required Market Data facts are invalid;
- candidate/recommendation count is zero;
- IS/OOS/shadow sample is insufficient;
- OOS/stress fails;
- optional CONFIRM data is missing.

Correct actions are respectively:
- fail closed for affected current recommendation path;
- valid `NO QUALIFIED TOP PICKS`;
- keep proof stage active as `IN_PROGRESS`/`WAITING_VERIFIED_DEPENDENCY` while evidence can still be collected;
- record valid failed evaluation verdict and stop production-use proof path;
- keep core running and mark CONFIRM availability state.

A failed attempt must still be closed with evidence and convergence. It does not close the stage automatically.

---

# Evidence Placement

For each build step:
- current stage/resume pointer → `WS_IMPLEMENTATION_STAGE_REGISTER.md`;
- attempt result → `../../evidence/` as immutable evidence;
- implementation changes → `../` relevant implementation documents/code;
- test/backtest/runtime output → `../../evidence/`;
- discovered issue → `../../findings/`;
- formal strategy/production decision → `../../decisions/`;
- superseded technical/history material → `../../history/`.

Never append implementation progress into canonical strategy owner files.

---

# First Action for a New Programmer

If you have not yet read the strategy, return to [`../../START_HERE.md`](../START_HERE.md) and complete Part I first.

If strategy is understood, begin at `WS-B00` and do not skip to database/API/backtest simply because those files already exist.

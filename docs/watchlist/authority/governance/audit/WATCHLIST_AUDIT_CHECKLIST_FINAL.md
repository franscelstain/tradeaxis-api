# Watchlist Audit Checklist Final

Gunakan checklist ini untuk audit ketat dokumentasi Watchlist.

Status:
- `PASS`
- `PARTIAL`
- `FAIL`
- `N/A`

## A. Scope Guard

- [ ] domain tetap `watchlist`
- [ ] active policy hanya `weekly_swing`
- [ ] tidak melebar ke portfolio / execution / position management
- [ ] Market Data tetap upstream authority

## B. Product Objective

- [ ] tujuan adalah qualified recommendation untuk keputusan beli manual
- [ ] quality lebih penting daripada jumlah picks
- [ ] zero Top Picks adalah outcome valid
- [ ] target return adalah net after realistic friction, bukan guaranteed profit

## C. Market Data Intake

- [ ] runtime intake hanya melalui producer-facing consumer read contract
- [ ] new current PLAN hanya menerima `READABLE + FRESH + effective_trade_date=requested_trade_date`
- [ ] prior-date `STALE/DEGRADED` tidak dilabeli sebagai PLAN requested date
- [ ] `data_usable`/legacy `eligible` dibaca sebagai upstream usability, bukan Weekly Swing eligibility
- [ ] required active field null/invalid menutup candidate path tanpa zero/default/reconstruction
- [ ] liquidity selection basis explicit: `adv20_close_volume_proxy_idr`; `dv20_idr` hanya alias proxy
- [ ] actual traded value tidak mengganti proxy secara diam-diam
- [ ] producer indicators/status/sector/benchmark/data usability tidak direcompute Watchlist
- [ ] direct Market Data tables/current pointer/run mirror bukan runtime intake contract
- [ ] historical replay memakai exact/as-known identity tanpa current/prior-date substitution
- [ ] EOD Market Data tidak diasumsikan menyediakan D+1 CONFIRM source

## D. PLAN

- [ ] PLAN memakai `RECOMMENDATION_CANDIDATES`
- [ ] PLAN memakai `WATCH_ONLY` / `AVOID`
- [ ] PLAN tidak memakai `TOP_PICKS` sebagai final semantic
- [ ] PLAN count tidak dipaksa minimum
- [ ] active hard-gate/scoring feature missing = fail-closed dari recommendation path
- [ ] score components normalized, complete, causal, dan frozen

## E. RECOMMENDATION / TOP PICKS

- [ ] source hanya immutable `RECOMMENDATION_CANDIDATES`
- [ ] all-and-only candidate yang lulus final qualification menjadi Top Picks
- [ ] recommendation count dapat nol dan tidak quota-driven
- [ ] `recommendation_score = PLAN score_total` pada canonical baseline
- [ ] tie-break deterministic
- [ ] capital/affordability tidak mengubah membership/rank
- [ ] optional affordability hanya enrichment

## F. CONFIRM

- [ ] CONFIRM hanya final Top Picks
- [ ] CONFIRM optional/non-blocking; core PLAN/RECOMMENDATION/Top Picks tetap complete tanpa CONFIRM
- [ ] non-recommended candidate tidak dipromosikan lewat CONFIRM
- [ ] CONFIRM tidak mengubah historical recommendation score/rank
- [ ] CONFIRM hanya menentukan current actionability
- [ ] canonical entry window D+1 dijaga
- [ ] missing/stale/incomplete/delayed snapshot menghasilkan `UNAVAILABLE_RETRYABLE`, bukan `NOT_ACTIONABLE` atau core failure
- [ ] `NOT_ACTIONABLE` hanya diberikan ketika valid decision-time data tersedia dan active gate benar-benar gagal
- [ ] excessive price drift / invalid trade-plan pada valid data dapat membuat Top Pick `NOT_ACTIONABLE`
- [ ] `UNAVAILABLE_RETRYABLE` dapat diretry selama entry window; sesudah window berakhir tanpa valid evaluation menjadi `EXPIRED_UNCONFIRMED`

## G. Evaluation / Proof

- [ ] backtest/IS/OOS mengevaluasi final Top Picks, bukan PLAN proxy
- [ ] next-day executable open adalah canonical entry
- [ ] missing open di-skip, bukan hindsight fallback
- [ ] realistic all-in cost profile digunakan
- [ ] production proof memakai non-zero slippage
- [ ] adverse-friction stress proof tersedia
- [ ] ranking-quality metrics tersedia
- [ ] OOS tanpa retuning
- [ ] core forward shadow proof sebelum core production-use approval; CONFIRM capability proof terpisah dan non-blocking

## H. Documentation Layer Guard

- [ ] strategy hanya behavior/acceptance owner
- [ ] implementation tunduk pada strategy
- [ ] research/evidence/findings/decisions/history berada pada layer masing-masing
- [ ] evidence historis tidak ditulis ulang mengikuti strategy baru

## I. Recording / Lifecycle Integrity

- [ ] semua material record mempunyai role/lifecycle yang jelas
- [ ] final evidence tidak pernah ditulis ulang; correction memakai record baru
- [ ] issued decision tidak pernah ditulis ulang; perubahan memakai superseding decision
- [ ] locked research tidak diubah setelah lock; perubahan memakai identity/version baru
- [ ] finding mempertahankan original observation dan hanya menambah lifecycle/resolution
- [ ] historical ledger/session entry tidak dihapus/ditulis ulang
- [ ] material implementation-contract/documentation change mempunyai `DOCUMENT_CHANGE_LOG` entry
- [ ] strategy/governance material revision mempunyai finding/evidence/decision/supersession trace yang sesuai
- [ ] history/archive immutable dan tidak dipakai sebagai current fallback
- [ ] README/index tetap navigation-only dan tidak membuat business rule baru

## J. Stage Execution Governance

- [ ] current `WS-Bxx` stage register tersedia dan current
- [ ] stage rerun membaca latest attempt lineage sebelum code change
- [ ] every attempt mempunyai closed evidence + convergence
- [ ] repeated failure/time/fatigue tidak dipakai sebagai closure criterion
- [ ] `DONE` berarti declared stage objective/exit criteria tercapai
- [ ] evaluation verdict (`PASS/FAIL`) dipisahkan dari stage execution state
- [ ] improving convergence menjaga stage tetap aktif
- [ ] waiting dependency dibuktikan dan mempunyai resume trigger
- [ ] terminal unresolved closure mempunyai evidence + reviewed decision
- [ ] successor/decomposition materially different dan residual scope lengkap

## K. Implementation Residue / Conformance

- [ ] recurring residue check dilakukan pada impacted stage/proof path
- [ ] residue dinilai berdasarkan reachability + semantic impact, bukan identifier lama saja
- [ ] unresolved `HARMFUL_RESIDUE` = 0 sebelum implementation-stage `DONE`
- [ ] controlled compatibility residue mempunyai exact mapping + isolation + tests + evidence
- [ ] historical-only residue tetap non-executable/non-authoritative
- [ ] dead residue tidak diklaim tanpa reachability evidence
- [ ] fixtures/tests/config/reason/API lama tidak mempertahankan superseded behavior
- [ ] proof/evaluator path bebas harmful residue yang dapat mengubah exact current identity/verdict
- [ ] rerun membaca prior residue finding/evidence dan tidak rediscover from zero
- [ ] residue conformance verdict tersedia pada attempt evidence/stage register

## L. Strategy Coverage / Traceability

- [ ] Audit memeriksa canonical traceability matrix, bukan hanya stage summary.
- [ ] Seluruh active mandatory/conditional rule yang diklaim complete adalah `SATISFIED`.
- [ ] `SATISFIED` mempunyai implementation/test/evidence/residue trace.
- [ ] Tidak ada rule yang dihapus/diabaikan untuk menaikkan coverage.
- [ ] Strategy change yang material telah supersede/add rule ID secara traceable.
- [ ] 100% coverage claim dipisahkan dari OOS/production business verdict.

## M. Verdict Gate

Dokumen tidak boleh dinilai conformant jika:
- implementation translation masih memaksakan semantics lama;
- final Top Picks belum menjadi object proof;
- production proof hanya zero-slippage/idealized friction;
- ranking belum mempunyai usefulness proof;
- runtime/test evidence belum mendukung claim implementation.

## Work Baseline Lock / Attempt / Executable Integrity Requirement

Current implementation/proof work must follow:

- `docs/watchlist/authority/governance/WORK_BASELINE_LOCK_STANDARD.md`;
- `docs/watchlist/authority/governance/DOCUMENT_INTEGRITY_GATE_STANDARD.md`;
- `docs/watchlist/development/implementation/examples/WS_STAGE_ATTEMPT_RECORD_TEMPLATE.md`.

Audit/implementation must verify:

- baseline was issued before material code/contract change;
- Stage ID, Attempt ID, and Baseline ID are linked;
- source revision and locked authority fingerprints are recorded;
- pre-attempt and pre-close executable integrity gate results exist;
- attempt evidence records coverage/residue/convergence/root-cause/do-not-repeat/resume point;
- no stage `DONE` or 100% coverage claim relies on evidence detached from a valid baseline;
- a gate failure is not waived by prose; only exact registered legacy exception may be used where governance permits.

## Work relationship / closure audit

- [ ] Work ID links attempt, baseline, finding, evidence, decision, residue and closure records.
- [ ] Work Record Registry paths/relationships valid.
- [ ] Dependency Registry has objective evidence + resume trigger.
- [ ] Relationship integrity gate PASS.
- [ ] Terminal stage closure manifest matches Stage Register and evidence.


## 9/9 Relationship Integrity

- [ ] One Attempt ID maps to exactly one Stage and one Baseline ID.
- [ ] Exactly one WORK_BASELINE_LOCK exists per registered Attempt; Baseline ID is not reused by another Attempt.
- [ ] Current Record IDs are unique.
- [ ] Every referenced Stage exists.
- [ ] Every current record Baseline ID resolves to the Attempt baseline lock and baseline file identity matches registry.
- [ ] `related_finding_ids` resolve only to `FINDING` records.
- [ ] `related_decision_ids` resolve only to `DECISION` records.
- [ ] `supersedes` graph is acyclic.
- [ ] Closure-critical evidence is same-baseline, or has explicit `CROSS_BASELINE_CLOSURE_EVIDENCE` + justification + reviewed Decision.
- [ ] Cross-attempt/cross-stage references have explicit justified Work Relationship Registry rows.
- [ ] Terminal stage has exactly one canonical final Attempt Record and one matching Stage Closure Manifest.
## Document Role Purity

- [ ] Setiap physical semantic document mempunyai tepat satu authoritative role.
- [ ] Registry role mempunyai satu row unik per file.
- [ ] Tidak ada retained multi-role legacy source atau multi-role bundle exception.
- [ ] Cross-role references tidak berubah menjadi cross-role authority.
- [ ] Executable role-purity gate PASS.


# Watchlist Implementation Audit Checklist Final

Gunakan `PASS`, `PARTIAL`, `FAIL`, atau `N/A`.

## A. Foundation Alignment

| Item | Cek | Status | Catatan |
|---|---|---|---|
| A1 | Implementation tunduk pada canonical strategy |  |  |
| A2 | weekly_swing only |  |  |
| A3 | tidak bocor ke portfolio/execution/market-data internals |  |  |
| A4 | strategy revision tidak dianggap implementation completion |  |  |
| A5 | `STRATEGY_ALIGNMENT_REQUIRED.md` ditutup hanya dengan evidence |  |  |

## B. PLAN

| Item | Cek | Status | Catatan |
|---|---|---|---|
| B1 | RECOMMENDATION_CANDIDATES |  |  |
| B2 | tidak ada PRIMARY/SECONDARY fallback |  |  |
| B3 | WATCH_ONLY / AVOID |  |  |
| B4 | PLAN tidak memakai final TOP_PICKS semantic |  |  |
| B5 | no forced minimum count |  |  |
| B6 | score_total/tie-break deterministic |  |  |
| B7 | active scoring feature missing fail-closed |  |  |
| B8 | normalized weighted-sum score semantics sesuai strategy |  |  |

## C. Recommendation / Top Picks

| Item | Cek | Status | Catatan |
|---|---|---|---|
| C1 | source dari immutable eligible PLAN candidates |  |  |
| C2 | all-and-only final qualification pass menjadi Top Picks |  |  |
| C3 | zero Top Picks valid |  |  |
| C4 | no quota/cap yang membuang qualified picks |  |  |
| C5 | recommendation_score = PLAN score_total baseline |  |  |
| C6 | deterministic rank |  |  |
| C7 | capital tidak mengubah membership/rank |  |  |
| C8 | affordability hanya enrichment |  |  |

## D. CONFIRM

| Item | Cek | Status | Catatan |
|---|---|---|---|
| D1 | hanya final Top Picks eligible |  |  |
| D2 | non-recommended tidak dipromosikan |  |  |
| D3 | hanya current actionability berubah |  |  |
| D4 | recommendation membership/score/rank immutable |  |  |
| D5 | D+1 canonical entry window |  |  |
| D6 | freshness + entry drift/band + trade-plan validity gates |  |  |

## E. Backtest / Evaluation

| Item | Cek | Status | Catatan |
|---|---|---|---|
| E1 | exact PLAN + RECOMMENDATION replay |  |  |
| E2 | evaluated trade source = final Top Picks |  |  |
| E3 | next-day executable open |  |  |
| E4 | missing open = skip, no hindsight close fallback |  |  |
| E5 | realistic all-in cost profile |  |  |
| E6 | non-zero slippage production proof |  |  |
| E7 | adverse-friction stress |  |  |
| E8 | no synthetic zero return |  |  |

## F. IS/OOS / Ranking Proof

| Item | Cek | Status | Catatan |
|---|---|---|---|
| F1 | IS metrics final recommendation |  |  |
| F2 | OOS exact frozen winner |  |  |
| F3 | no OOS retuning |  |  |
| F4 | rank1 metrics |  |  |
| F5 | score-vs-return correlation |  |  |
| F6 | rank inversion check |  |  |
| F7 | stress OOS positive edge |  |  |

## G. Forward Shadow

| Item | Cek | Status | Catatan |
|---|---|---|---|
| G1 | exact frozen strategy |  |  |
| G2 | minimum 40 trading days unless longer needed for sample |  |  |
| G3 | no future leakage |  |  |
| G4 | no retuning during window |  |  |
| G5 | realized executable outcomes recorded |  |  |
| G6 | full flow TOP PICKS -> CONFIRM -> actionable/not-actionable |  |  |

## H. Layer C Evidence Applicability

- Jika ZIP belum mengaktifkan Layer C, seluruh code/runtime-only checks diberi `N/A`, bukan `PARTIAL`.
- Jika Layer C aktif tetapi evidence belum lengkap, gunakan `PARTIAL`.
- Jangan klaim PHPUnit, Artisan, migration, runtime, artifact, atau persistence PASS tanpa evidence nyata.
- Jika validasi membutuhkan operator, gunakan `OPERATOR_VALIDATION_REQUIRED` sampai output diberikan.

## I. Database Work

Untuk DB-connected implementation, baca dictionary/schema owner dan identifikasi table/date key/identifier/as-of rule sebelum coding. Missing dictionary coverage adalah blocker/update-doc requirement, bukan alasan menebak field.

## J. Documentation Recording Integrity

| Item | Cek | Status | Catatan |
|---|---|---|---|
| J1 | material implementation contract change tercatat pada DOCUMENT_CHANGE_LOG |  |  |
| J2 | final evidence tidak di-rewrite; correction memakai record baru |  |  |
| J3 | issued decision tidak di-rewrite; perubahan memakai superseding decision |  |  |
| J4 | locked research/preregistration tidak diubah setelah lock |  |  |
| J5 | historical ledger/session entry tetap utuh; correction append-only |  |  |
| J6 | finding mempertahankan original observation |  |  |
| J7 | README/index tidak membuat rule baru |  |  |
| J8 | strategy/governance revision punya trace sesuai controlled-revision policy |  |  |

## K. Stage Execution / Re-entry / Closure Integrity

| Item | Cek | Status | Catatan |
|---|---|---|---|
| K1 | current stage register dibaca/diperbarui |  |  |
| K2 | rerun membaca latest attempt evidence + open finding + active decision/remediation |  |  |
| K3 | attempt baru menjelaskan perubahan/hypothesis dibanding attempt sebelumnya |  |  |
| K4 | setiap attempt ditutup dengan immutable evidence |  |  |
| K5 | convergence (`IMPROVING/STABLE/REGRESSING/INCONCLUSIVE`) dicatat |  |  |
| K6 | repeated failure/time/fatigue tidak dipakai sebagai closure criterion |  |  |
| K7 | `DONE` hanya bila declared stage objective/exit criteria tercapai |  |  |
| K8 | valid evaluation `FAIL` dipisahkan dari stage-execution failure |  |  |
| K9 | `WAITING_VERIFIED_DEPENDENCY` mempunyai evidence + resume trigger |  |  |
| K10 | `INSUFFICIENT EVIDENCE` tidak ditutup terminal bila evidence masih dapat dikumpulkan |  |  |
| K11 | terminal unresolved closure mempunyai objective evidence + reviewed decision |  |  |
| K12 | successor/decomposition memetakan seluruh residual objective dan materially different |  |  |

## L. Recurring Residue / Conformance Gate

| Item | Cek | Status | Catatan |
|---|---|---|---|
| L1 | impacted surfaces residue-scanned dan scope evidence tertulis |  |  |
| L2 | reachability/behavior diperiksa; bukan grep/search saja |  |  |
| L3 | unresolved `HARMFUL_RESIDUE` = 0 sebelum implementation-stage `DONE` |  |  |
| L4 | compatibility residue punya exact semantic mapping |  |  |
| L5 | compatibility residue terisolasi dan punya positive/negative tests |  |  |
| L6 | historical residue tidak dijadikan current executable/authority fallback |  |  |
| L7 | dead residue hanya disebut dead dengan evidence reachability yang cukup |  |  |
| L8 | fixture/test/reason/config lama tidak mengunci behavior superseded |  |  |
| L9 | proof/evaluator path merepresentasikan exact current strategy identity |  |  |
| L10 | rerun membaca known residue/finding/evidence sebelumnya |  |  |
| L11 | attempt evidence mempunyai residue classification + conformance verdict |  |  |

## M. Strategy-to-Implementation Traceability Coverage

- [ ] Current stage matrix rows sudah dibaca sebelum implementation/rework.
- [ ] Setiap mandatory rule mempunyai implementation mapping.
- [ ] Test dan immutable evidence pointer tersedia sebelum `SATISFIED`.
- [ ] Residue verdict conformant tersedia untuk satisfied behavior.
- [ ] Tidak ada mandatory row stage berstatus `NOT_ASSESSED`, `MAPPED_UNVERIFIED`, `IMPLEMENTED_UNVERIFIED`, `FAILED_REMEDIATION_OPEN`, `WAITING_VERIFIED_DEPENDENCY`, atau `INCONCLUSIVE` saat stage dinyatakan `DONE`.
- [ ] Optional CONFIRM `OPTIONAL_NOT_REQUESTED` tidak dianggap core gap.
- [ ] Final 100% strategy coverage claim dihitung dari matrix, bukan hanya jumlah stage `DONE`.

## N. Final Verdict

- Belum layak
- Parsial
- Layak kuat
- Sangat matang

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

## Work correlation / registry / closure

- [ ] Attempt ID is canonical Work ID.
- [ ] Current records are registered in `records/WORK_RECORD_REGISTRY.csv`.
- [ ] Material change has verified Change Impact Declaration.
- [ ] Verified dependency is registered with resume trigger.
- [ ] Relationship integrity gate PASS.
- [ ] Terminal stage has immutable Stage Closure Manifest.
- [ ] `CURRENT_STATE.md` regenerated after material state change.

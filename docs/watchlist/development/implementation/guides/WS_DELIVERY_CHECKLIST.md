# 07 — WS Delivery Checklist

> **Current Conformance:** `NOT_ASSESSED_REVALIDATION_REQUIRED`  
> **Verification Epoch:** `WS-REBASELINE-20260819-001`  
> Existing content is a revalidation input. Historical PASS/READY/DONE wording does not grant current conformance.


## Purpose

Checklist ini dipakai saat menerjemahkan Weekly Swing dari system docs ke aplikasi watchlist.

Checklist ini adalah **delivery gate**, bukan checklist informatif biasa.  
Build harus ditolak bila hard-stop items gagal.

## Scope Lock

- watchlist only
- weekly_swing only
- bukan portfolio
- bukan execution
- bukan market-data internals

## Gate 1 — Scope Gate

- [ ] baseline `weekly_swing` sudah difreeze
- [ ] audit baseline watchlist digunakan sebagai guardrail
- [ ] owner docs sudah dipahami
- [ ] tidak ada perluasan domain ke portfolio
- [ ] tidak ada perluasan domain ke execution
- [ ] tidak ada perluasan domain ke market-data internals
- [ ] implementation guidance tetap tunduk pada owner docs
- [ ] support artifacts tidak diperlakukan sebagai owner rule

## Gate 2 — Contract Gate

- [ ] `PLAN` dibangun terlebih dahulu dan dipublish/freeze sebagai artifact
- [ ] `RECOMMENDATION` hanya berasal dari `PLAN`
- [ ] `RECOMMENDATION` tidak membaca `CONFIRM`
- [ ] `RECOMMENDATION` dapat tersedia tanpa `CONFIRM`
- [ ] core PLAN + RECOMMENDATION selesai tanpa CONFIRM artifact/request
- [ ] `RECOMMENDATION` dapat kosong
- [ ] `CONFIRM` hanya berlaku untuk final Top Pick + immutable binding yang sah
- [ ] non-Top-Pick tidak dapat menjadi valid CONFIRM target
- [ ] `CONFIRM` tidak memutasi recommendation membership/rank/score/label
- [ ] consumer/composite view tidak mengubah source semantics
- [ ] path/reference tidak drift dari baseline freeze

## Gate 3 — API / Persistence Gate

### Read / API
- [ ] endpoint watchlist hanya bersifat read/suggestion
- [ ] tidak ada endpoint buy/sell di domain ini
- [ ] optional confirm input/manual snapshot tervalidasi bila CONFIRM digunakan; missing market data menghasilkan non-blocking availability state
- [ ] unknown top-level field selalu ditolak pada confirm request boundary
- [ ] field canonical minimum API dipakai persis seperti di `WS_API_GUIDANCE.md`
- [ ] recommendation endpoint tidak memasukkan ticker di luar PLAN candidate set
- [ ] composite endpoint memisahkan plan/recommendation/confirm secara eksplisit

### Persistence
- [ ] artifact watchlist dipisah: PLAN / RECOMMENDATION / CONFIRM
- [ ] `RECOMMENDATION` merefer ke source `PLAN`
- [ ] `CONFIRM` merefer ke source `PLAN`
- [ ] tidak ada persistence holdings/portfolio di domain ini
- [ ] tidak ada persistence execution/order/broker di domain ini
- [ ] tidak ada back-mutation business fields setelah publish
- [ ] field canonical minimum persistence dipakai persis seperti di `WS_PERSISTENCE_GUIDANCE.md` / `WS_CANONICAL_FIELD_MATRIX.md`

## Gate 4 — Audit Evidence Gate

- [ ] daftar endpoint/fungsi yang diimplementasikan tersedia
- [ ] contoh payload valid tersedia
- [ ] contoh payload invalid tersedia
- [ ] hasil deterministic tests tersedia
- [ ] hasil contract tests tersedia
- [ ] hasil empty recommendation tests tersedia
- [ ] test non-Top-Pick CONFIRM rejection tersedia
- [ ] hasil no-mutation tests tersedia
- [ ] bukti persistence separation tersedia
- [ ] system docs dan implementation guidance sinkron

## Hard Stop Rejection Criteria

Delivery **must be rejected** bila salah satu kondisi berikut terjadi:
1. implementation memakai data di luar PLAN untuk membangun RECOMMENDATION
2. confirm mengubah ranking/group/membership/score recommendation
3. artifact watchlist bercampur dengan execution/order/broker state
4. artifact watchlist bercampur dengan portfolio/holding state
5. ticker non-Top-Pick dapat menghasilkan valid business CONFIRM
6. support artifacts override owner docs
7. contract fields drift tanpa update owner docs
8. composite view mengaburkan source semantics
9. baseline freeze digeser diam-diam saat implementasi

## Final Rule

Checklist delivery dianggap lolos hanya bila:
- scope tetap utuh
- contract tetap utuh
- API/persistence separation tetap utuh
- audit evidence minimum tersedia


## Merge Readiness Focus

- [ ] `PLAN` artifact sudah immutable sebelum `RECOMMENDATION` dibentuk
- [ ] `RECOMMENDATION` hanya membaca `PLAN` immutable
- [ ] `RECOMMENDATION` dapat tersedia tanpa `CONFIRM`
- [ ] core PLAN + RECOMMENDATION selesai tanpa CONFIRM artifact/request
- [ ] `RECOMMENDATION` dapat kosong
- [ ] `CONFIRM` hanya berlaku untuk final Top Pick + immutable binding yang sah
- [ ] ticker non-Top-Pick tidak dapat di-confirm sebagai valid business outcome
- [ ] recommendation kosong adalah valid core state dan tidak membutuhkan CONFIRM
- [ ] `CONFIRM` tidak mengubah recommendation membership/rank/score/label
- [ ] missing/stale/incomplete current data menghasilkan `UNAVAILABLE_RETRYABLE`, bukan core failure / `NOT_ACTIONABLE`
- [ ] later valid current data dapat diretry selama entry window
- [ ] tidak ada code path yang membaca `CONFIRM` untuk membentuk `RECOMMENDATION`
- [ ] tidak ada code path yang membuat CONFIRM mandatory untuk core success; eligibility CONFIRM justru dibatasi pada final Top Picks
- [ ] tidak ada leakage ke portfolio
- [ ] tidak ada leakage ke execution

## OOS runtime closure delivery

- migrate schema, including stop/RR grid columns and versioned IS-eval identity;
- seed the canonical deterministic grid and verify more than one row;
- run PHPUnit regressions;
- execute one explicit chronological window;
- inspect IS failure summaries and extreme trade evidence;
- run a second identical proof only after OOS executes;
- verify idempotent persistence and equal canonical artifact hashes;
- keep `production_ready=0` and do not promote any paramset.

## Stage Progression / Rework Gate

- [ ] current stage register sudah dibaca dan diperbarui
- [ ] jika rerun, latest attempt evidence + open finding + active remediation/decision sudah dibaca
- [ ] attempt baru materially berbeda atau mempunyai new testable hypothesis
- [ ] failed attempt ditutup dengan evidence + convergence; tidak dihapus
- [ ] `DONE` hanya bila stage objective/exit criteria terpenuhi
- [ ] repeated failure/time/fatigue tidak dipakai sebagai terminal closure reason
- [ ] improving convergence mempertahankan stage active
- [ ] waiting dependency memiliki evidence + resume trigger
- [ ] unresolved terminal closure/successor/decomposition mempunyai reviewed decision

Authority: `../../../authority/governance/STAGE_EXECUTION_AND_REWORK_STANDARD.md`.

## Residue / Conformance Delivery Gate

Sebelum delivery stage dinyatakan complete, link residue evidence current dan pastikan verdict `CONFORMANT_NO_HARMFUL_RESIDUE_FOUND` atau `CONFORMANT_WITH_CONTROLLED_COMPATIBILITY`. Unresolved harmful residue atau inconclusive residue evidence memblokir implementation-stage `DONE`.

## Work Baseline / Attempt Integrity

- Work Baseline Lock must be issued before material implementation change.
- Use `../examples/WS_STAGE_ATTEMPT_RECORD_TEMPLATE.md` for final attempt evidence.
- Run `../tests/WatchlistDocumentationIntegrityGate.php` before attempt/stage/package closure.
